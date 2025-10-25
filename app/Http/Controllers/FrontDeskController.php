<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\DeviceCategory;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\StorageFee;
use App\Services\TaskAssignmentService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
     * Process device collection (checkout)
     */
    public function processCollection(Request $request, $taskId)
    {
        $request->validate([
            'collected_by' => 'required|string|max:255',
            'id_type' => 'required|string|max:50',
            'id_number' => 'required|string|max:50',
            'storage_fee_paid' => 'nullable|boolean',
        ]);

        $task = Task::with(['user', 'invoice', 'storageFee'])->findOrFail($taskId);

        // Verify task is ready for collection
        if ($task->status !== 'ready_for_collection') {
            return back()->with('error', 'This device is not ready for collection.');
        }

        // Verify invoice is paid
        if (!$task->invoice || $task->invoice->status !== 'paid') {
            return back()->with('error', 'Invoice must be paid before device can be collected.');
        }

        DB::beginTransaction();
        try {
            // Check if there are storage fees
            if ($task->storageFee && $task->storageFee->total_fee > 0) {
                if (!$request->storage_fee_paid) {
                    return back()->with('error', 'Storage fee must be paid before collection.');
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
            ]);

            // Log collection details (you may want to create a separate model for this)
            // For now, we'll add it to a notes field or create a simple log

            // Send notification to client
            $this->notificationService->notifyClientDeviceCollected($task);

            DB::commit();

            return redirect()->route('frontdesk.collection-receipt', $task->id)
                ->with('success', 'Device collected successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
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

        return view('frontdesk.collection-receipt', compact('task'));
    }

    /**
     * Process invoice payment at front desk
     */
    public function processPayment(Request $request, $invoiceId)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card,mobile_money',
            'amount_received' => 'required|numeric|min:0',
        ]);

        $invoice = Invoice::with('task')->findOrFail($invoiceId);

        if ($invoice->status === 'paid') {
            return back()->with('error', 'This invoice has already been paid.');
        }

        if ($request->amount_received < $invoice->total) {
            return back()->with('error', 'Insufficient payment amount.');
        }

        DB::beginTransaction();
        try {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $request->payment_method,
            ]);

            // Notify client
            $this->notificationService->notifyClientPaymentReceived($invoice->task);

            DB::commit();

            $change = $request->amount_received - $invoice->total;
            return back()->with('success', 'Payment processed successfully! Change: $' . number_format($change, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Payment processing failed: ' . $e->getMessage());
        }
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
}
