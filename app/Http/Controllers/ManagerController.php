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

public function users(Request $request)
{
   $query = User::with('roles');

   // Filter by role (admin, manager, supervisor, front_desk, technician, client)
   if ($request->filled('role')) {
       $query->whereHas('roles', function($q) use ($request) {
           $q->where('name', $request->role);
       });
   }

   // Filter by status (active, inactive)
   if ($request->filled('status')) {
       if ($request->status === 'active') {
           $query->where('is_active', true);
       } elseif ($request->status === 'inactive') {
           $query->where('is_active', false);
       }
   }

   // Search by name or email
   if ($request->filled('search')) {
       $searchTerm = '%' . $request->search . '%';
       $query->where(function($q) use ($searchTerm) {
           $q->where('name', 'like', $searchTerm)
             ->orWhere('email', 'like', $searchTerm);
       });
   }

   // Get paginated results
   $users = $query->orderBy('created_at', 'desc')->paginate(15);

   // Get counts by role for statistics
   $adminCount = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->count();
   $managerCount = User::whereHas('roles', fn($q) => $q->where('name', 'manager'))->count();
   $supervisorCount = User::whereHas('roles', fn($q) => $q->where('name', 'supervisor'))->count();
   $frontDeskCount = User::whereHas('roles', fn($q) => $q->where('name', 'front_desk'))->count();

   return view('manager.users', compact(
       'users',
       'adminCount',
       'managerCount', 
       'supervisorCount',
       'frontDeskCount'
   ));
}

/**
* Show the form for creating a new user
* 
* @return \Illuminate\View\View
*/
public function createUser()
{
   $roles = [
       'admin' => 'Administrator - Full system access',
       'manager' => 'Manager - Manage operations and staff',
       'supervisor' => 'Supervisor - Oversee work and staff',
       'front_desk' => 'Front Desk Staff - Customer service',
       'technician' => 'Technician - Device repairs and services',
       'client' => 'Client - Regular customer account',
   ];

   return view('manager.users-create', compact('roles'));
}

/**
* Store a newly created user in database
* 
* @param Request $request
* @return \Illuminate\Http\RedirectResponse
*/
public function storeUser(Request $request)
{
   // Validate input
   $validated = $request->validate([
       'name' => [
           'required',
           'string',
           'max:255',
       ],
       'email' => [
           'required',
           'email',
           'unique:users,email',
       ],
       'phone' => [
           'nullable',
           'string',
           'max:20',
       ],
       'password' => [
           'required',
           'string',
           'min:8',
           'confirmed',
       ],
       'role' => [
           'required',
           'string',
           'in:admin,manager,supervisor,front_desk,technician,client',
       ],
   ]);

   try {
       DB::beginTransaction();

       // Create the user
       $user = User::create([
           'name' => $validated['name'],
           'email' => $validated['email'],
           'password' => Hash::make($validated['password']),
           'phone' => $validated['phone'] ?? null,
           'is_active' => true,
       ]);

       // Assign role using Laravel Spatie Roles & Permissions
       $user->assignRole($validated['role']);

       DB::commit();

       // Log the activity (optional - requires activity log package)
       // activity()
       //     ->causedBy(auth()->user())
       //     ->performedOn($user)
       //     ->log('User created: ' . $user->name);

       return redirect()
           ->route('manager.users')
           ->with('success', "User '{$user->name}' has been created successfully!");

   } catch (\Exception $e) {
       DB::rollBack();
       
       return redirect()
           ->back()
           ->with('error', 'Failed to create user. Please try again.')
           ->withInput();
   }
}

/**
* Show the form for editing an existing user
* 
* @param User $user
* @return \Illuminate\View\View
*/
public function editUser(User $user)
{
   // Prevent editing admin if not super admin
   if ($user->hasRole('admin') && !auth()->user()->hasRole('admin')) {
       return redirect()
           ->route('manager.users')
           ->with('error', 'You do not have permission to edit administrators.');
   }

   $roles = [
       'admin' => 'Administrator',
       'manager' => 'Manager',
       'supervisor' => 'Supervisor',
       'front_desk' => 'Front Desk Staff',
       'technician' => 'Technician',
       'client' => 'Client',
   ];

   return view('manager.users-edit', compact('user', 'roles'));
}

/**
* Update the specified user in database
* 
* @param Request $request
* @param User $user
* @return \Illuminate\Http\RedirectResponse
*/
public function updateUser(Request $request, User $user)
{
   // Validate input
   $validated = $request->validate([
       'name' => [
           'required',
           'string',
           'max:255',
       ],
       'email' => [
           'required',
           'email',
           'unique:users,email,' . $user->id,
       ],
       'phone' => [
           'nullable',
           'string',
           'max:20',
       ],
       'role' => [
           'required',
           'string',
           'in:admin,manager,supervisor,front_desk,technician,client',
       ],
       'is_active' => [
           'boolean',
       ],
   ]);

   try {
       DB::beginTransaction();

       // Store old role for logging
       $oldRole = $user->roles->first()?->name;

       // Update user information
       $user->update([
           'name' => $validated['name'],
           'email' => $validated['email'],
           'phone' => $validated['phone'] ?? null,
           'is_active' => $request->boolean('is_active'),
       ]);

       // Update role if changed
       if ($oldRole !== $validated['role']) {
           $user->syncRoles([$validated['role']]);
       }

       DB::commit();

       // Log the activity (optional)
       // if ($oldRole !== $validated['role']) {
       //     activity()
       //         ->causedBy(auth()->user())
       //         ->performedOn($user)
       //         ->log("Role changed from {$oldRole} to {$validated['role']}");
       // }

       return redirect()
           ->route('manager.users')
           ->with('success', "User '{$user->name}' has been updated successfully!");

   } catch (\Exception $e) {
       DB::rollBack();

       return redirect()
           ->back()
           ->with('error', 'Failed to update user. Please try again.')
           ->withInput();
   }
}

/**
* Delete the specified user from database
* 
* @param User $user
* @return \Illuminate\Http\RedirectResponse
*/
public function deleteUser(User $user)
{
   // Prevent self-deletion
   if ($user->id === auth()->id()) {
       return redirect()
           ->route('manager.users')
           ->with('error', 'You cannot delete your own account!');
   }

   // Prevent deletion of last admin
   if ($user->hasRole('admin')) {
       $adminCount = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->count();
       if ($adminCount <= 1) {
           return redirect()
               ->route('manager.users')
               ->with('error', 'Cannot delete the last administrator account.');
       }
   }

   try {
       DB::beginTransaction();

       $userName = $user->name;

       // Delete user
       $user->delete();

       DB::commit();

       // Log the activity (optional)
       // activity()
       //     ->causedBy(auth()->user())
       //     ->log("User deleted: {$userName}");

       return redirect()
           ->route('manager.users')
           ->with('success', "User '{$userName}' has been deleted successfully!");

   } catch (\Exception $e) {
       DB::rollBack();

       return redirect()
           ->route('manager.users')
           ->with('error', 'Failed to delete user. Please try again.');
   }
}

/**
* Export users list (optional - for CSV export)
* 
* @param Request $request
* @return \Symfony\Component\HttpFoundation\StreamedResponse
*/
public function exportUsers(Request $request)
{
   $query = User::with('roles');

   if ($request->filled('role')) {
       $query->whereHas('roles', function($q) use ($request) {
           $q->where('name', $request->role);
       });
   }

   $users = $query->get();

   $csvFileName = 'users_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

   $headers = [
       'Content-Encoding' => 'UTF-8',
       'Content-type' => 'text/csv; charset=UTF-8',
       'Content-Disposition' => "attachment; filename=$csvFileName",
       'Pragma' => 'no-cache',
       'Expires' => '0',
   ];

   $callback = function() use ($users) {
       $file = fopen('php://output', 'w');
       
       // BOM for UTF-8
       fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
       
       // Header row
       fputcsv($file, ['Name', 'Email', 'Phone', 'Role', 'Status', 'Created At']);

       // Data rows
       foreach ($users as $user) {
           fputcsv($file, [
               $user->name,
               $user->email,
               $user->phone ?? 'N/A',
               $user->roles->first()?->name ?? 'N/A',
               $user->is_active ? 'Active' : 'Inactive',
               $user->created_at->format('Y-m-d H:i:s'),
           ]);
       }

       fclose($file);
   };

   return response()->stream($callback, 200, $headers);
}
}