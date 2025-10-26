<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;

class NotificationService
{
    /**
     * Notify technician about new job assignment
     */
    public function notifyTechnicianNewJob(Task $task)
    {
        if (!$task->technician_id) {
            return;
        }

        Notification::create([
            'user_id' => $task->technician_id,
            'type' => 'job_assigned',
            'title' => 'New Job Assigned',
            'message' => "You have been assigned a new {$task->type} job for {$task->device_brand} {$task->device_model}. Task ID: {$task->task_id}",
            'data' => [
                'task_id' => $task->id,
                'task_code' => $task->task_id,
                'device' => $task->device_brand . ' ' . $task->device_model,
            ],
        ]);
    }

    /**
     * Notify management (manager and supervisor) about new job
     */
    public function notifyManagementNewJob(Task $task)
    {
        $managers = User::role(['manager', 'supervisor'])->get();

        foreach ($managers as $manager) {
            Notification::create([
                'user_id' => $manager->id,
                'type' => 'new_job_created',
                'title' => 'New Job Created',
                'message' => "New {$task->type} job created. Task ID: {$task->task_id}, Assigned to: " . ($task->technician ? $task->technician->name : 'Unassigned'),
                'data' => [
                    'task_id' => $task->id,
                    'task_code' => $task->task_id,
                    'technician_name' => $task->technician ? $task->technician->name : null,
                    'customer_name' => $task->user->name,
                ],
            ]);
        }
    }

    /**
     * Notify technician that device has been checked in
     */
    public function notifyTechnicianDeviceCheckedIn(Task $task)
    {
        if (!$task->technician_id) {
            return;
        }

        Notification::create([
            'user_id' => $task->technician_id,
            'type' => 'device_checked_in',
            'title' => 'Device Checked In',
            'message' => "Device for Task {$task->task_id} has been checked in and is ready for you to start working on.",
            'data' => [
                'task_id' => $task->id,
                'task_code' => $task->task_id,
            ],
        ]);
    }

    /**
     * Notify client that device has been checked in
     */
    public function notifyClientDeviceCheckedIn(Task $task)
    {
        Notification::create([
            'user_id' => $task->user_id,
            'type' => 'device_checked_in',
            'title' => 'Device Checked In',
            'message' => "Your {$task->device_brand} {$task->device_model} has been checked in. Our technician " . ($task->technician ? $task->technician->name : '') . " will begin working on it soon.",
            'data' => [
                'task_id' => $task->id,
                'task_code' => $task->task_id,
                'technician_name' => $task->technician ? $task->technician->name : null,
            ],
        ]);
    }

    /**
     * Notify client about job progress update
     */
    public function notifyClientJobProgress(Task $task, $stage, $notes = null)
    {
        Notification::create([
            'user_id' => $task->user_id,
            'type' => 'job_progress',
            'title' => 'Job Progress Update',
            'message' => "Your {$task->device_brand} {$task->device_model} is now at stage: {$stage}. " . ($notes ? $notes : ''),
            'data' => [
                'task_id' => $task->id,
                'task_code' => $task->task_id,
                'stage' => $stage,
            ],
        ]);
    }


    /**
     * Notify supervisor to review job complexity
     */
    public function notifySupervisorReviewComplexity(Task $task)
    {
        $supervisors = User::role('supervisor')->get();

        foreach ($supervisors as $supervisor) {
            Notification::create([
                'user_id' => $supervisor->id,
                'type' => 'review_complexity',
                'title' => 'Review Job Complexity',
                'message' => "Please review the complexity of Task {$task->task_id}. Current weight: {$task->complexity_weight}",
                'data' => [
                    'task_id' => $task->id,
                    'task_code' => $task->task_id,
                ],
            ]);
        }
    }

    /**
     * Get unread notifications for user
     */
    public function getUnreadNotifications($userId)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->recent()
            ->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead($userId)
    {
        Notification::where('user_id', $userId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }



    /**
     * Notify client that device is ready for collection
     * UPDATED: Now includes payment status information
     */
    public function notifyClientDeviceReady(Task $task)
    {
        $task->load(['invoice', 'deviceCategory']);

        $paymentStatus = $task->invoice && $task->invoice->status === 'paid'
            ? 'Invoice has been paid.'
            : '⚠️ Please bring payment ($' . number_format($task->invoice->total, 2) . ') when collecting your device.';

        Notification::create([
            'user_id' => $task->user_id,
            'type' => 'device_ready',
            'title' => '🎉 Device Ready for Collection',
            'message' => "Great news! Your {$task->device_brand} {$task->device_model} is ready for collection. Task ID: {$task->task_id}. {$paymentStatus}",
            'data' => [
                'task_id' => $task->id,
                'task_code' => $task->task_id,
                'invoice_status' => $task->invoice ? $task->invoice->status : null,
                'amount_due' => $task->invoice && $task->invoice->status !== 'paid' ? $task->invoice->total : 0,
            ],
        ]);

        // If there's an unpaid invoice, also send a reminder about payment
        if ($task->invoice && $task->invoice->status !== 'paid') {
            $this->sendPaymentReminderNotification($task);
        }
    }

    /**
     * Send payment reminder notification to client
     */
    protected function sendPaymentReminderNotification(Task $task)
    {
        Notification::create([
            'user_id' => $task->user_id,
            'type' => 'payment_reminder',
            'title' => '💳 Payment Required for Collection',
            'message' => "Your device (Task {$task->task_id}) is ready! Please bring payment of $" . number_format($task->invoice->total, 2) . " when collecting your {$task->device_brand} {$task->device_model}. Invoice #: {$task->invoice->invoice_number}",
            'data' => [
                'task_id' => $task->id,
                'task_code' => $task->task_id,
                'invoice_id' => $task->invoice->id,
                'invoice_number' => $task->invoice->invoice_number,
                'amount_due' => $task->invoice->total,
            ],
        ]);
    }

    /**
     * Notify front desk about unpaid device collection
     * NEW METHOD
     */
    public function notifyFrontDeskUnpaidCollection(Task $task)
    {
        $frontDeskUsers = User::role('frontdesk')->get();

        foreach ($frontDeskUsers as $frontDesk) {
            Notification::create([
                'user_id' => $frontDesk->id,
                'type' => 'unpaid_collection_alert',
                'title' => '⚠️ Collect Payment on Pickup',
                'message' => "Device ready for collection with UNPAID invoice. Customer: {$task->user->name}. Task: {$task->task_id}. Amount: $" . number_format($task->invoice->total, 2) . ". Invoice #: {$task->invoice->invoice_number}",
                'data' => [
                    'task_id' => $task->id,
                    'task_code' => $task->task_id,
                    'invoice_id' => $task->invoice->id,
                    'invoice_number' => $task->invoice->invoice_number,
                    'amount_due' => $task->invoice->total,
                    'customer_name' => $task->user->name,
                    'customer_phone' => $task->user->phone,
                    'device' => $task->device_brand . ' ' . $task->device_model,
                ],
            ]);
        }

        Log::info('Front desk notified about unpaid collection', [
            'task_id' => $task->id,
            'invoice_number' => $task->invoice->invoice_number,
            'amount' => $task->invoice->total,
        ]);
    }
}
