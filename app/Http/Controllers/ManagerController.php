<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Technician;
use App\Services\TaskAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ManagerController extends Controller
{
    protected $taskAssignmentService;

    public function __construct(TaskAssignmentService $taskAssignmentService)
    {
        $this->taskAssignmentService = $taskAssignmentService;
    }

    /**
     * Manager/Supervisor Dashboard
     */
    public function index()
    {
        $totalTechnicians = User::role('technician')->count();
$availableTechnicians = User::role('technician')->count();

        // Overview Statistics
        $stats = [
            'total_technicians' => $totalTechnicians,
            'available_technicians' => $availableTechnicians,
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'pending_amount' => Invoice::where('status', 'pending')->sum('total'),

            // Tasks
            'active_tasks' => Task::active()->count(),
            'completed_today' => Task::whereDate('completed_at', today())->count(),
            'pending_collection' => Task::where('status', 'ready_for_collection')->count(),
            'total_tasks' => Task::count(),

            // Revenue
            'revenue_today' => Invoice::where('status', 'paid')
                ->whereDate('paid_at', today())
                ->sum('total'),
            'revenue_week' => Invoice::where('status', 'paid')
                ->whereBetween('paid_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('total'),
            'revenue_month' => Invoice::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->sum('total'),
            'pending_payments' => Invoice::where('status', 'pending')->sum('total'),

            // Customers
            'new_customers_today' => User::role('client')->whereDate('created_at', today())->count(),
            'total_customers' => User::role('client')->count(),

            // Bookings
            'pending_bookings' => Booking::where('payment_status', 'pending')->count(),
            'todays_bookings' => Booking::whereDate('created_at', today())->count(),
        ];

        // Recent Activities
        $recentTasks = Task::with(['user', 'technician', 'deviceCategory'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Technician Workload
        $technicians = $this->taskAssignmentService->getAllTechniciansWorkload();

        // Revenue Chart Data (Last 7 days)
        $revenueChartData = $this->getRevenueChartData();

        return view('manager.index', compact('stats', 'recentTasks', 'technicians', 'revenueChartData'));
    }

    /**
     * All Tasks Overview
     */
    public function tasks(Request $request)
    {
        $query = Task::with(['user', 'technician', 'deviceCategory', 'invoice']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('task_id', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q2) use ($request) {
                      $q2->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate(20);

        $technicians = User::role('technician')->get();

        return view('manager.tasks', compact('tasks', 'technicians'));
    }

    /**
     * Review Task Complexity
     */
    public function reviewComplexity($taskId)
    {
        $task = Task::with(['user', 'technician', 'deviceCategory', 'progress'])->findOrFail($taskId);

        return view('manager.review-complexity', compact('task'));
    }

    /**
     * Update Task Complexity
     */
    public function updateComplexity(Request $request, $taskId)
    {
        $request->validate([
            'complexity_weight' => 'required|integer|min:1|max:5',
            'notes' => 'nullable|string',
        ]);

        $task = Task::findOrFail($taskId);

        $task->update([
            'complexity_weight' => $request->complexity_weight,
        ]);

        // Add note to progress if provided
        if ($request->notes) {
            \App\Models\JobProgress::create([
                'task_id' => $task->id,
                'technician_id' => auth()->id(),
                'stage' => 'Complexity Reviewed',
                'notes' => $request->notes,
            ]);
        }

        return redirect()->route('manager.tasks')
            ->with('success', 'Task complexity updated successfully!');
    }

    /**
     * Reassign Task
     */
    public function reassignTask(Request $request, $taskId)
    {
        $request->validate([
            'technician_id' => 'required|exists:users,id',
        ]);

        $task = Task::findOrFail($taskId);

        $this->taskAssignmentService->reassignTask($taskId, $request->technician_id);

        // Notify new technician
        $newTechnician = User::find($request->technician_id);
        \App\Models\Notification::create([
            'user_id' => $newTechnician->id,
            'type' => 'task_reassigned',
            'title' => 'Task Reassigned to You',
            'message' => "Task {$task->task_id} has been reassigned to you by management.",
            'data' => ['task_id' => $task->id],
        ]);

        return back()->with('success', 'Task reassigned successfully!');
    }


    /**
     * Revenue Reports
     */
    public function revenue(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, year

        $query = Invoice::where('status', 'paid');

        switch($period) {
            case 'day':
                $query->whereDate('paid_at', today());
                break;
            case 'week':
                $query->whereBetween('paid_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('paid_at', now()->month);
                break;
            case 'year':
                $query->whereYear('paid_at', now()->year);
                break;
        }

        $invoices = $query->with(['task.user', 'task.technician'])->get();

        $stats = [
            'total_revenue' => $invoices->sum('total'),
            'materials_cost' => $invoices->sum('materials_cost'),
            'labour_cost' => $invoices->sum('labour_cost'),
            'tax_collected' => $invoices->sum('tax'),
            'invoice_count' => $invoices->count(),
            'avg_invoice' => $invoices->avg('total'),
        ];

        return view('manager.revenue', compact('invoices', 'stats', 'period'));
    }

    /**
     * Customer Management
     */
    public function customers()
    {
        $customers = User::role('client')
            ->withCount(['bookings', 'tasks'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('manager.customers', compact('customers'));
    }

    /**
     * Get Revenue Chart Data for last 7 days
     */
    private function getRevenueChartData()
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = Invoice::where('status', 'paid')
                ->whereDate('paid_at', $date)
                ->sum('total');

            $data[] = [
                'date' => $date->format('M d'),
                'revenue' => $revenue,
            ];
        }

        return $data;
    }

    /**
     * Analytics Dashboard
     */
    public function analytics()
    {
        // Task completion rate
        $totalTasks = Task::count();
        $completedTasks = Task::whereIn('status', ['completed', 'ready_for_collection', 'collected'])->count();
        $completionRate = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

        // Average completion time
        $avgCompletionTime = Task::whereNotNull('completed_at')
            ->whereNotNull('assigned_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, assigned_at, completed_at)) as avg_hours')
            ->value('avg_hours');

        // Category breakdown
        $categoryStats = Task::select('device_category_id', DB::raw('count(*) as count'))
            ->with('deviceCategory')
            ->groupBy('device_category_id')
            ->get();

        // Monthly trend
        $monthlyTrend = Task::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        return view('manager.analytics', compact(
            'completionRate',
            'avgCompletionTime',
            'categoryStats',
            'monthlyTrend'
        ));
    }

/**
 * Display list of all technicians
 */
public function technicians()
{
    $technicians = User::role('technician')
        ->with('technician')
        ->withCount([
            'assignedTasks',
            'assignedTasks as active_tasks_count' => function($query) {
                $query->whereIn('status', ['assigned', 'checked_in', 'in_progress', 'waiting_parts']);
            },
            'assignedTasks as completed_tasks_count' => function($query) {
                $query->where('status', 'completed');
            }
        ])
        ->paginate(15);

    return view('manager.technicians-crud', compact('technicians'));
}

/**
 * Show form to create new technician
 */
public function createTechnician()
{
    $deviceCategories = \App\Models\DeviceCategory::all();
    return view('manager.technicians-create', compact('deviceCategories'));
}

/**
 * Store new technician
 */
public function storeTechnician(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
        'phone' => 'required|string|max:20',
        'address' => 'nullable|string|max:500',
        'city' => 'nullable|string|max:100',
        'country' => 'nullable|string|max:100',
        'specializations' => 'required|array|min:1',
        'specializations.*' => 'exists:device_categories,id',
        'hourly_rate' => 'required|numeric|min:0',
        'max_workload' => 'required|integer|min:1|max:50',
        'is_available' => 'boolean',
    ]);

    DB::beginTransaction();
    try {
        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'country' => $validated['country'] ?? null,
        ]);

        // Assign technician role
        $user->assignRole('technician');

        // Create technician profile
        \App\Models\Technician::create([
            'user_id' => $user->id,
            'specializations' => $validated['specializations'],
            'hourly_rate' => $validated['hourly_rate'],
            'max_workload' => $validated['max_workload'],
            'is_available' => $validated['is_available'] ?? true,
        ]);

        DB::commit();

        return redirect()->route('manager.technicians')
            ->with('success', 'Technician created successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to create technician: ' . $e->getMessage())
            ->withInput();
    }
}

/**
 * Show form to edit technician
 */
public function editTechnician($id)
{
    $technician = User::role('technician')
        ->with('technician')
        ->findOrFail($id);

    $deviceCategories = \App\Models\DeviceCategory::all();

    return view('manager.technicians-edit', compact('technician', 'deviceCategories'));
}

/**
 * Update technician
 */
public function updateTechnician(Request $request, $id)
{
    $user = User::role('technician')->findOrFail($id);

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'password' => 'nullable|string|min:8|confirmed',
        'phone' => 'required|string|max:20',
        'address' => 'nullable|string|max:500',
        'city' => 'nullable|string|max:100',
        'country' => 'nullable|string|max:100',
        'specializations' => 'required|array|min:1',
        'specializations.*' => 'exists:device_categories,id',
        'hourly_rate' => 'required|numeric|min:0',
        'max_workload' => 'required|integer|min:1|max:50',
        'is_available' => 'boolean',
    ]);

    DB::beginTransaction();
    try {
        // Update user
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'country' => $validated['country'] ?? null,
        ]);

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update technician profile
        $user->technician->update([
            'specializations' => $validated['specializations'],
            'hourly_rate' => $validated['hourly_rate'],
            'max_workload' => $validated['max_workload'],
            'is_available' => $validated['is_available'] ?? true,
        ]);

        DB::commit();

        return redirect()->route('manager.technicians')
            ->with('success', 'Technician updated successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to update technician: ' . $e->getMessage())
            ->withInput();
    }
}

/**
 * Delete technician
 */
public function deleteTechnician($id)
{
    $user = User::role('technician')->findOrFail($id);

    // Check if technician has active tasks
    $activeTasks = Task::where('technician_id', $user->id)
        ->whereIn('status', ['assigned', 'checked_in', 'in_progress', 'waiting_parts'])
        ->count();

    if ($activeTasks > 0) {
        return back()->with('error', 'Cannot delete technician with active tasks. Please reassign or complete their tasks first.');
    }

    DB::beginTransaction();
    try {
        // Delete technician profile
        $user->technician()->delete();

        // Remove role
        $user->removeRole('technician');

        // Delete user
        $user->delete();

        DB::commit();

        return redirect()->route('manager.technicians')
            ->with('success', 'Technician deleted successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to delete technician: ' . $e->getMessage());
    }
}





}
