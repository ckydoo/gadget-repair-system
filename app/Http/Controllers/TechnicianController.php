<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Invoice;
use App\Models\JobProgress;
use App\Models\MaterialUsed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;

class TechnicianController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Mark task as ready for collection - UPDATED VERSION
     * Now allows marking ready even if invoice is unpaid (with warnings)
     */
    public function markReady(Request $request, $taskId)
    {
        $task = Task::with(['invoice', 'deviceCategory', 'user'])
            ->where('technician_id', auth()->id())
            ->findOrFail($taskId);

        // Verify task is completed
        if ($task->status !== 'completed') {
            return back()->with('error', 'Task must be completed before marking as ready for collection.');
        }

        // Verify invoice exists
        if (!$task->invoice) {
            return back()->with('error', 'Invoice must be generated before marking as ready for collection.');
        }

        $isUnpaid = $task->invoice->status !== 'paid';
        $markUnpaid = $request->has('mark_unpaid') && $request->mark_unpaid == '1';

        DB::beginTransaction();
        try {
            // Update task status
            $task->update([
                'status' => 'ready_for_collection',
                'ready_at' => now(),
                'warranty_expires_at' => now()->addDays($task->warranty_days ?? 30),
            ]);

            // Create storage fee record
            $category = $task->deviceCategory;
            \App\Models\StorageFee::create([
                'task_id' => $task->id,
                'days_stored' => 0,
                'daily_rate' => $category ? $category->getStorageFeeRate() : 2.00,
                'total_fee' => 0,
            ]);

            // Add progress note
            JobProgress::create([
                'task_id' => $task->id,
                'technician_id' => auth()->id(),
                'stage' => 'Ready for Collection',
                'notes' => $isUnpaid
                    ? "Device marked as ready for collection. ⚠️ WARNING: Invoice #{$task->invoice->invoice_number} is UNPAID (Status: {$task->invoice->status}, Amount: $" . number_format($task->invoice->total, 2) . "). Payment must be collected at pickup."
                    : "Device marked as ready for collection. Invoice #{$task->invoice->invoice_number} has been paid.",
            ]);

            // Notify client that device is ready
            $this->notificationService->notifyClientDeviceReady($task);

            // If unpaid, notify front desk staff about payment collection
            if ($isUnpaid) {
                $this->notifyFrontDeskUnpaidCollection($task);

                // Log the unpaid ready marking
                Log::warning('Device marked ready with unpaid invoice', [
                    'task_id' => $task->id,
                    'task_code' => $task->task_id,
                    'invoice_id' => $task->invoice->id,
                    'invoice_number' => $task->invoice->invoice_number,
                    'invoice_status' => $task->invoice->status,
                    'invoice_total' => $task->invoice->total,
                    'technician_id' => auth()->id(),
                    'technician_name' => auth()->user()->name,
                    'marked_by_technician' => true,
                ]);
            }

            DB::commit();

            $message = $isUnpaid
                ? "Task marked as ready for collection! ⚠️ Note: Invoice is unpaid - front desk must collect payment during pickup."
                : "Task marked as ready for collection! Customer has been notified.";

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark task as ready', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to mark as ready: ' . $e->getMessage());
        }
    }

    /**
     * Notify front desk staff about unpaid device ready for collection
     */
    protected function notifyFrontDeskUnpaidCollection(Task $task)
    {
        // Get all front desk users
        $frontDeskUsers = \App\Models\User::role('frontdesk')->get();

        foreach ($frontDeskUsers as $frontDesk) {
            \App\Models\Notification::create([
                'user_id' => $frontDesk->id,
                'type' => 'unpaid_collection',
                'title' => '⚠️ Unpaid Device Ready for Collection',
                'message' => "Task {$task->task_id} ({$task->device_brand} {$task->device_model}) is ready for collection but invoice #{$task->invoice->invoice_number} is UNPAID. Amount due: $" . number_format($task->invoice->total, 2) . ". Please collect payment during pickup.",
                'data' => [
                    'task_id' => $task->id,
                    'task_code' => $task->task_id,
                    'invoice_id' => $task->invoice->id,
                    'invoice_number' => $task->invoice->invoice_number,
                    'amount_due' => $task->invoice->total,
                    'customer_name' => $task->user->name,
                    'customer_phone' => $task->user->phone,
                ],
            ]);
        }
    }
    /**
     * Show technician dashboard
     */
    public function index()
    {
        $technician = auth()->user()->technician;

        if (!$technician) {
            abort(403, 'You are not registered as a technician.');
        }

        // Get active tasks
        $activeTasks = Task::with(['user', 'deviceCategory', 'progress', 'materials'])
            ->where('technician_id', auth()->id())
            ->whereIn('status', ['assigned', 'checked_in', 'in_progress', 'waiting_parts'])
            ->orderBy('assigned_at', 'asc')
            ->get();

        // Get completed tasks (last 30 days)
        $completedTasks = Task::with(['user', 'deviceCategory'])
            ->where('technician_id', auth()->id())
            ->whereIn('status', ['completed', 'ready_for_collection', 'collected'])
            ->where('completed_at', '>=', now()->subDays(30))
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        // Statistics
        $stats = [
            'active_count' => $activeTasks->count(),
            'completed_today' => Task::where('technician_id', auth()->id())
                ->whereDate('completed_at', today())
                ->count(),
            'total_completed' => Task::where('technician_id', auth()->id())
                ->whereIn('status', ['completed', 'ready_for_collection', 'collected'])
                ->count(),
            'workload_weight' => $technician->getCurrentWorkloadWeight(),
            'max_workload' => $technician->max_workload,
        ];

        return view('technician.index', compact('activeTasks', 'completedTasks', 'stats', 'technician'));
    }

    /**
     * Show task details
     */
    public function showTask($taskId)
    {
        $task = Task::with([
            'user',
            'deviceCategory',
            'booking',
            'progress.technician',
            'materials',
            'invoice'
        ])
        ->where('technician_id', auth()->id())
        ->findOrFail($taskId);

        return view('technician.task-details', compact('task'));
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, $taskId)
    {
        $request->validate([
            'status' => 'required|in:checked_in,in_progress,waiting_parts,completed',
        ]);

        $task = Task::where('technician_id', auth()->id())->findOrFail($taskId);

        DB::beginTransaction();
        try {
            $task->update([
                'status' => $request->status,
                'started_at' => $request->status === 'in_progress' && !$task->started_at ? now() : $task->started_at,
                'completed_at' => $request->status === 'completed' ? now() : $task->completed_at,
            ]);

            // Add automatic progress update
            JobProgress::create([
                'task_id' => $task->id,
                'technician_id' => auth()->id(),
                'stage' => 'Status Updated',
                'notes' => "Status changed to: " . ucfirst(str_replace('_', ' ', $request->status)),
            ]);

            // Notify client
            $this->notificationService->notifyClientJobProgress(
                $task,
                ucfirst(str_replace('_', ' ', $request->status)),
                'Status updated by technician'
            );

            DB::commit();

            return back()->with('success', 'Task status updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Add progress update
     */
    public function addProgress(Request $request, $taskId)
    {
        $request->validate([
            'stage' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'images.*' => 'nullable|image|max:5120',
        ]);

        $task = Task::where('technician_id', auth()->id())->findOrFail($taskId);

        DB::beginTransaction();
        try {
            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('progress-images', 'public');
                    $imagePaths[] = $path;
                }
            }

            // Create progress update
            $progress = JobProgress::create([
                'task_id' => $task->id,
                'technician_id' => auth()->id(),
                'stage' => $request->stage,
                'notes' => $request->notes,
                'images' => $imagePaths,
            ]);

            // Notify client
            $this->notificationService->notifyClientJobProgress(
                $task,
                $request->stage,
                $request->notes
            );

            DB::commit();

            return back()->with('success', 'Progress update added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to add progress: ' . $e->getMessage());
        }
    }

    /**
     * Add material used
     */
    public function addMaterial(Request $request, $taskId)
    {
        $request->validate([
            'material_name' => 'required|string|max:255',
            'part_number' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $task = Task::where('technician_id', auth()->id())->findOrFail($taskId);

        try {
            MaterialUsed::create([
                'task_id' => $task->id,
                'material_name' => $request->material_name,
                'part_number' => $request->part_number,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'total_price' => $request->quantity * $request->unit_price,
            ]);

            return back()->with('success', 'Material added successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add material: ' . $e->getMessage());
        }
    }

    /**
     * Complete task and generate invoice
     */
    public function completeTask(Request $request, $taskId)
    {
        $request->validate([
            'labour_hours' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $task = Task::with(['materials', 'booking', 'technician'])->where('technician_id', auth()->id())->findOrFail($taskId);

        DB::beginTransaction();
        try {
            // Calculate costs
            $materialsCost = $task->materials->sum('total_price');
            $labourCost = $request->labour_hours * $task->technician->technician->hourly_rate;
            $transportCost = $task->booking ? $task->booking->transport_fee : 0;
            $diagnosticFee = $task->booking ? $task->booking->diagnostic_fee : 0;

            // Generate invoice
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'task_id' => $task->id,
                'user_id' => $task->user_id,
                'materials_cost' => $materialsCost,
                'labour_cost' => $labourCost,
                'transport_cost' => $transportCost,
                'diagnostic_fee' => $diagnosticFee,
                'status' => 'pending',
            ]);

            $invoice->calculateTotals();
            $invoice->save();

            // Update task
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Add completion progress
            JobProgress::create([
                'task_id' => $task->id,
                'technician_id' => auth()->id(),
                'stage' => 'Task Completed',
                'notes' => $request->notes ?? 'Repair/service completed successfully.',
            ]);

            // Notify client
            $this->notificationService->notifyClientJobProgress(
                $task,
                'Task Completed',
                'Your device has been repaired and is ready for final inspection.'
            );

            DB::commit();

            return redirect()->route('technician.index')
                ->with('success', 'Task completed and invoice generated!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to complete task: ' . $e->getMessage());
        }
    }


}
