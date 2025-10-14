<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\DeviceCategory;
use App\Models\Booking;
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

        $recentTasks = Task::with(['user', 'deviceCategory', 'technician'])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('frontdesk.index', compact(
            'todayCheckins',
            'onlineBookingsToday',
            'pendingCheckins',
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
                    'password' => Hash::make('password123'), // Default password
                ]);
                $customer->assignRole('client');
            }

            // Get device category
            $category = DeviceCategory::find($request->device_category_id);

            // Generate unique task ID
            $taskId = Task::generateTaskId($customer->name, $category->code);

            // Assign technician automatically
            $technician = $this->taskAssignmentService->assignTechnician($category->id);

            if (!$technician) {
                DB::rollBack();
                return back()->with('error', 'No available technician found for this category.');
            }

            // Create task directly (walk-in has no booking)
            $task = Task::create([
                'task_id' => $taskId,
                'user_id' => $customer->id,
                'device_category_id' => $category->id,
                'technician_id' => $technician->id,
                'type' => $request->type,
                'device_brand' => $request->device_brand,
                'device_model' => $request->device_model,
                'problem_description' => $request->problem_description,
                'is_walkin' => true,
                'status' => 'checked_in',
                'assigned_at' => now(),
            ]);

            // Notify technician
            $this->notificationService->notifyTechnicianNewJob($task);

            // Notify manager and supervisor
            $this->notificationService->notifyManagementNewJob($task);

            DB::commit();

            return redirect()->route('frontdesk.print-label', $task->id)
                ->with('success', 'Walk-in customer registered successfully! Task ID: ' . $taskId);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Show print label page
     */
    public function printLabel($taskId)
    {
        $task = Task::with(['user', 'deviceCategory', 'technician'])
            ->findOrFail($taskId);

        return view('frontdesk.print-label', compact('task'));
    }

    /**
     * Generate label data for printing
     */
    public function generateLabelData($taskId)
    {
        $task = Task::with(['user', 'deviceCategory', 'technician'])
            ->findOrFail($taskId);

        return response()->json([
            'task_id' => $task->task_id,
            'customer_name' => $task->user->name,
            'technician_name' => $task->technician->name,
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
