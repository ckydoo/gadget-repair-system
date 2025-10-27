<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\StorageFee;
use Illuminate\Http\Request;
use App\Models\DeviceCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Services\NotificationService;
use App\Services\TaskAssignmentService;

class FrontDeskController extends Controller
{
    protected $taskAssignmentService;
    protected $notificationService;

    public function __construct(
        TaskAssignmentService $taskAssignmentService,
        NotificationService $notificationService
    ) {
        $this->taskAssignmentService = $taskAssignmentService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display front desk dashboard
     */
    public function index()
    {
        $todayCheckins = Task::whereDate('created_at', today())
            ->where('is_walkin', true)
            ->count();

        $onlineBookingsToday = Booking::whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->where('status', 'confirmed')
            ->count();

        $pendingCheckins = Booking::where('payment_status', 'paid')
            ->where('status', 'confirmed')
            ->whereDoesntHave('task')
            ->count();

        $readyForCollection = Task::where('status', 'ready_for_collection')
            ->count();

        $recentTasks = Task::with(['user', 'deviceCategory', 'technician'])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('frontdesk.index', compact(
            'todayCheckins',
            'onlineBookingsToday',
            'pendingCheckins',
            'readyForCollection',
            'recentTasks'
        ));
    }

    /**
     * Show check-in form for online bookings
     */
    public function showCheckinForm()
    {
        return view('frontdesk.checkin');
    }

    /**
     * Search for booking by task ID
     */
    public function searchBooking(Request $request)
    {
        $request->validate([
            'task_id' => 'required|string',
        ]);

        $task = Task::with(['user', 'deviceCategory', 'booking', 'technician'])
            ->where('task_id', $request->task_id)
            ->first();

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task ID not found. Please check and try again.',
            ], 404);
        }

        if ($task->status !== 'assigned') {
            return response()->json([
                'success' => false,
                'message' => 'This device has already been checked in.',
                'task' => $task,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'task' => $task,
        ]);
    }

    /**
     * Check in device for online booking
     */
    public function checkinOnlineBooking(Request $request)
    {
        $request->validate([
            'task_id' => 'required|string|exists:tasks,task_id',
        ]);

        DB::beginTransaction();
        try {
            $task = Task::where('task_id', $request->task_id)->first();

            if ($task->status !== 'assigned') {
                return back()->with('error', 'This device has already been checked in.');
            }

            // Update task status
            $task->update([
                'status' => 'checked_in',
            ]);

            // Send notification to technician
            $this->notificationService->notifyTechnicianDeviceCheckedIn($task);

            // Send notification to client
            $this->notificationService->notifyClientDeviceCheckedIn($task);

            DB::commit();

            return redirect()->route('frontdesk.print-label', $task->id)
                ->with('success', 'Device checked in successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Check-in failed: ' . $e->getMessage());
        }
    }

    /**
     * Show walk-in registration form
     */
    public function showWalkinForm()
    {
        $categories = DeviceCategory::active()->get();
        return view('frontdesk.walkin', compact('categories'));
    }

    /**
     * Register walk-in customer
     */
    public function registerWalkin(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email',
            'customer_address' => 'nullable|string',
            'device_category_id' => 'required|exists:device_categories,id',
            'device_brand' => 'required|string|max:255',
            'device_model' => 'required|string|max:255',
            'problem_description' => 'required|string',
            'type' => 'required|in:service,repair',
        ]);

        DB::beginTransaction();
        try {
            // Check if customer exists or create new
            $customer = User::where('email', $request->customer_email)
                ->orWhere('phone', $request->customer_phone)
                ->first();

            if (!$customer) {
                $customer = User::create([
                    'name' => $request->customer_name,
                    'email' => $request->customer_email ?? 'walkin_' . time() . '@gadgetrepair.local',
                    'phone' => $request->customer_phone,
                    'address' => $request->customer_address,
                    'password' => Hash::make('password123'),
                ]);
                $customer->assignRole('client');
            }

            // Create task
            $category = DeviceCategory::findOrFail($request->device_category_id);
            $taskId = Task::generateTaskId($customer->name, $category->code);

            $task = Task::create([
                'task_id' => $taskId,
                'user_id' => $customer->id,
                'device_category_id' => $request->device_category_id,
                'type' => $request->type,
                'device_brand' => $request->device_brand,
                'device_model' => $request->device_model,
                'problem_description' => $request->problem_description,
                'complexity_weight' => $category->complexity_weight,
                'is_walkin' => true,
                'status' => 'checked_in',
                'warranty_days' => $category->warranty_days,
            ]);

            // Auto-assign technician
            $this->taskAssignmentService->assignTask($task);

            DB::commit();

            return redirect()->route('frontdesk.print-label', $task->id)
                ->with('success', 'Walk-in customer registered successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Show collection form
     */
    public function collectionForm()
    {
        $readyTasks = Task::with(['user', 'deviceCategory', 'invoice', 'storageFee'])
            ->where('status', 'ready_for_collection')
            ->orderBy('ready_at', 'asc')
            ->get();

        return view('frontdesk.collection', compact('readyTasks'));
    }

    /**
     * Search for device ready for collection
     */
    public function searchCollection(Request $request)
    {
        $request->validate([
            'task_id' => 'required|string',
        ]);

        $task = Task::with(['user', 'deviceCategory', 'invoice', 'storageFee', 'technician'])
            ->where('task_id', $request->task_id)
            ->first();

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task ID not found. Please check and try again.',
            ], 404);
        }

        if ($task->status !== 'ready_for_collection') {
            return response()->json([
                'success' => false,
                'message' => 'This device is not ready for collection yet. Current status: ' . ucfirst(str_replace('_', ' ', $task->status)),
            ], 400);
        }

        // Check if invoice is paid
        if (!$task->invoice || $task->invoice->status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice must be paid before device can be collected.',
                'task' => $task,
                'requires_payment' => true,
            ], 400);
        }

        // Calculate storage fees if applicable
        $storageFee = 0;
        if ($task->storageFee) {
            $task->storageFee->calculateFee();
            $storageFee = $task->storageFee->total_fee;
        }

        return response()->json([
            'success' => true,
            'task' => $task,
            'storage_fee' => $storageFee,
            'days_stored' => $task->getDaysUncollected(),
        ]);
    }





    /**
     * Print device label
     */
    public function printLabel($taskId)
    {
        $task = Task::with(['user', 'deviceCategory'])->findOrFail($taskId);

        return view('frontdesk.print-label', compact('task'));
    }

    /**
     * Get label data for printing
     */
    public function getLabelData($taskId)
    {
        $task = Task::with(['user', 'deviceCategory'])->findOrFail($taskId);

        return response()->json([
            'task_id' => $task->task_id,
            'customer' => $task->user->name,
            'device' => $task->device_brand . ' ' . $task->device_model,
            'category' => $task->deviceCategory->name,
            'date' => $task->created_at->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Search tasks
     */
    public function searchTasks(Request $request)
    {
        $query = Task::with(['user', 'deviceCategory', 'technician']);

        if ($request->filled('task_id')) {
            $query->where('task_id', 'like', '%' . $request->task_id . '%');
        }

        if ($request->filled('customer_name')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer_name . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('frontdesk.search-results', compact('tasks'));
    }


    /**
     * Show collection page with ready devices
     */
    public function collectionIndex()
    {
        // Show devices that are either 'completed' or 'ready_for_collection'
        $readyDevices = Task::with(['user', 'deviceCategory', 'invoice', 'storageFee', 'technician'])
            ->whereIn('status', ['completed', 'ready_for_collection'])
            ->orderByRaw("FIELD(status, 'ready_for_collection', 'completed')")
            ->orderBy('completed_at', 'asc')
            ->paginate(20);

        return view('frontdesk.collection', compact('readyDevices'));
    }

    /**
     * Search for device by Task ID
     */
    public function searchDevice(Request $request)
    {
        $request->validate([
            'task_id' => 'required|string',
        ]);

        $task = Task::with(['user', 'deviceCategory', 'invoice', 'storageFee', 'technician'])
            ->where('task_id', $request->task_id)
            ->first();

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task ID not found. Please check and try again.',
            ], 404);
        }

        // Allow both 'completed' and 'ready_for_collection' status
        if (!in_array($task->status, ['completed', 'ready_for_collection'])) {
            return response()->json([
                'success' => false,
                'message' => 'This device is not ready for collection yet. Current status: ' . ucfirst(str_replace('_', ' ', $task->status)),
            ], 400);
        }

        // Check invoice status - allow unpaid but flag for payment
        $requiresPayment = !$task->invoice || $task->invoice->status !== 'paid';
        $invoiceAmount = $task->invoice ? $task->invoice->total : 0;

        // Calculate storage fees if applicable
        $storageFee = 0;
        if ($task->storageFee) {
            $task->storageFee->calculateFee();
            $storageFee = $task->storageFee->total_fee;
        }

        return response()->json([
            'success' => true,
            'task' => $task,
            'requires_payment' => $requiresPayment,
            'invoice_amount' => $invoiceAmount,
            'storage_fee' => $storageFee,
            'days_stored' => $task->getDaysUncollected(),
            'total_amount_due' => $requiresPayment ? ($invoiceAmount + $storageFee) : $storageFee,
        ]);
    }

    /**
     * Process invoice payment at front desk during collection
     */
    public function processPayment(Request $request, $invoiceId)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card,mobile_money',
            'amount_received' => 'required|numeric|min:0',
        ]);

        $invoice = Invoice::with('task')->findOrFail($invoiceId);

        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This invoice has already been paid.',
            ], 400);
        }

        if ($request->amount_received < $invoice->total) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient payment amount. Required: $' . number_format($invoice->total, 2),
            ], 400);
        }

        DB::beginTransaction();
        try {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $request->payment_method,
            ]);

            // Log the payment
            Log::info('Front desk payment processed', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $invoice->total,
                'payment_method' => $request->payment_method,
                'processed_by' => auth()->id(),
                'task_id' => $invoice->task->id,
            ]);

            // Notify client
            $this->notificationService->notifyClientPaymentReceived($invoice->task);

            DB::commit();

            $change = $request->amount_received - $invoice->total;

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully!',
                'change' => $change,
                'invoice' => $invoice,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process device collection (checkout) with optional payment
     */
    public function processCollection(Request $request, $taskId)
    {
        $request->validate([
            'collected_by' => 'required|string|max:255',
            'id_type' => 'required|string|max:50',
            'id_number' => 'required|string|max:50',
            'storage_fee_paid' => 'nullable|boolean',
            'payment_method' => 'nullable|in:cash,card,mobile_money',
            'amount_received' => 'nullable|numeric|min:0',
        ]);

        $task = Task::with(['user', 'invoice', 'storageFee'])->findOrFail($taskId);

        // Verify task is completed or ready for collection
        if (!in_array($task->status, ['completed', 'ready_for_collection'])) {
            return back()->with('error', 'This device is not ready for collection. Current status: ' . ucfirst(str_replace('_', ' ', $task->status)));
        }

        DB::beginTransaction();
        try {
            // Process invoice payment if not paid
            if ($task->invoice && $task->invoice->status !== 'paid') {
                if (!$request->payment_method || !$request->amount_received) {
                    return back()->with('error', 'Payment is required. Please provide payment method and amount.');
                }

                if ($request->amount_received < $task->invoice->total) {
                    return back()->with('error', 'Insufficient payment amount. Required: $' . number_format($task->invoice->total, 2));
                }

                // Process the payment
                $task->invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => $request->payment_method,
                ]);

                $change = $request->amount_received - $task->invoice->total;

                Log::info('Payment collected at device pickup', [
                    'task_id' => $task->id,
                    'task_code' => $task->task_id,
                    'invoice_id' => $task->invoice->id,
                    'amount' => $task->invoice->total,
                    'payment_method' => $request->payment_method,
                    'amount_received' => $request->amount_received,
                    'change' => $change,
                    'processed_by' => auth()->id(),
                ]);
            }

            // Handle storage fees if applicable
            if ($task->storageFee && $task->storageFee->total_fee > 0) {
                if (!$request->storage_fee_paid) {
                    return back()->with('error', 'Storage fee must be paid before collection. Amount due: $' . number_format($task->storageFee->total_fee, 2));
                }

                // Mark storage fee as paid
                $task->storageFee->update([
                    'paid_at' => now(),
                ]);
            }

            // Update task to collected
            $task->update([
                'status' => 'collected',
                'collected_at' => now(),
                'collected_by' => $request->collected_by,
                'collector_id_type' => $request->id_type,
                'collector_id_number' => $request->id_number,
            ]);

            // Send notification to client
            $this->notificationService->notifyClientDeviceCollected($task);

            // Create collection log
            Log::info('Device collected', [
                'task_id' => $task->id,
                'task_code' => $task->task_id,
                'collected_by' => $request->collected_by,
                'collector_id' => $request->id_number,
                'processed_by' => auth()->user()->name,
            ]);

            DB::commit();

            $successMessage = 'Device collected successfully!';
            if (isset($change) && $change > 0) {
                $successMessage .= ' Change to return: $' . number_format($change, 2);
            }

            return redirect()->route('frontdesk.collection-receipt', $task->id)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Collection failed', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Collection failed: ' . $e->getMessage());
        }
    }

    /**
     * Show collection receipt
     */
    public function collectionReceipt($taskId)
    {
        $task = Task::with(['user', 'deviceCategory', 'invoice', 'storageFee', 'technician'])
            ->findOrFail($taskId);

        if ($task->status !== 'collected') {
            return redirect()->route('frontdesk.collection')
                ->with('error', 'This device has not been collected yet.');
        }

        return view('frontdesk.collection-receipt', compact('task'));
    }
}

