<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Booking;
use Illuminate\Http\Request;

class TaskTrackingController extends Controller
{
    /**
     * Show task tracking page
     */
    public function show($taskId)
    {
        $task = Task::with([
            'user',
            'deviceCategory',
            'technician',
            'booking',
            'progress.technician',
            'materials',
            'invoice',
            'storageFee'
        ])
        ->where('task_id', $taskId)
        ->orWhere('id', $taskId)
        ->firstOrFail();

        // Check if user owns this task
        if (auth()->check() && $task->user_id !== auth()->id()) {
            // Allow technician, manager, supervisor, and admin to view
            if (!auth()->user()->hasAnyRole(['technician', 'manager', 'supervisor', 'admin', 'front_desk'])) {
                abort(403, 'Unauthorized access to this task.');
            }
        }

        return view('tracking.show', compact('task'));
    }

    /**
     * Track by Task ID (public search)
     */
    public function search(Request $request)
    {
        $request->validate([
            'task_id' => 'required|string',
        ]);

        $task = Task::where('task_id', $request->task_id)->first();

        if (!$task) {
            return back()->with('error', 'Task ID not found. Please check and try again.');
        }

        return redirect()->route('tracking.show', $task->task_id);
    }

    /**
     * Show tracking search page
     */
    public function index()
    {
        return view('tracking.index');
    }

    /**
     * Get task status timeline data (for AJAX)
     */
    public function getTimeline($taskId)
    {
        $task = Task::with(['progress.technician'])
            ->where('task_id', $taskId)
            ->orWhere('id', $taskId)
            ->firstOrFail();

        $timeline = [];

        // Task assigned
        if ($task->assigned_at) {
            $timeline[] = [
                'title' => 'Task Assigned',
                'description' => "Assigned to {$task->technician->name}",
                'timestamp' => $task->assigned_at,
                'icon' => 'assignment',
            ];
        }

        // Device checked in
        if ($task->status !== 'assigned') {
            $timeline[] = [
                'title' => 'Device Checked In',
                'description' => 'Your device has been received at the workshop',
                'timestamp' => $task->created_at,
                'icon' => 'checkin',
            ];
        }

        // Progress updates
        foreach ($task->progress as $progress) {
            $timeline[] = [
                'title' => $progress->stage,
                'description' => $progress->notes,
                'timestamp' => $progress->created_at,
                'icon' => 'progress',
                'technician' => $progress->technician->name,
            ];
        }

        // Task completed
        if ($task->completed_at) {
            $timeline[] = [
                'title' => 'Repair Completed',
                'description' => 'Your device has been successfully repaired',
                'timestamp' => $task->completed_at,
                'icon' => 'completed',
            ];
        }

        // Ready for collection
        if ($task->ready_at) {
            $timeline[] = [
                'title' => 'Ready for Collection',
                'description' => 'Your device is ready to be picked up',
                'timestamp' => $task->ready_at,
                'icon' => 'ready',
            ];
        }

        // Collected
        if ($task->collected_at) {
            $timeline[] = [
                'title' => 'Device Collected',
                'description' => 'Device has been collected by customer',
                'timestamp' => $task->collected_at,
                'icon' => 'collected',
            ];
        }

        return response()->json($timeline);
    }

    /**
     * Get real-time status update (for polling)
     */
    public function getStatus($taskId)
    {
        $task = Task::with(['progress', 'invoice', 'storageFee'])
            ->where('task_id', $taskId)
            ->orWhere('id', $taskId)
            ->firstOrFail();

        return response()->json([
            'status' => $task->status,
            'status_label' => ucfirst(str_replace('_', ' ', $task->status)),
            'last_update' => $task->updated_at->diffForHumans(),
            'progress_count' => $task->progress->count(),
            'has_invoice' => $task->invoice ? true : false,
            'invoice_total' => $task->invoice ? $task->invoice->total : null,
            'days_uncollected' => $task->getDaysUncollected(),
            'storage_fee' => $task->storageFee ? $task->storageFee->total_fee : 0,
        ]);
    }
}
