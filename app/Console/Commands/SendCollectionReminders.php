<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\AfricasTalkingSmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendCollectionReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:send-collection-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS reminders for overdue device collections (Day 3 and Day 4)';

    protected $smsService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AfricasTalkingSmsService $smsService)
    {
        parent::__construct();
        $this->smsService = $smsService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting collection reminders check...');

        // Get all tasks ready for collection
        $tasks = Task::with(['user', 'storageFee'])
            ->where('status', 'ready_for_collection')
            ->whereNotNull('ready_at')
            ->get();

        $day3Count = 0;
        $day4Count = 0;
        $storageFeeCount = 0;

        foreach ($tasks as $task) {
            $daysUncollected = $task->getDaysUncollected();

            // Day 3 Reminder
            if ($task->shouldSendReminderDay3()) {
                $this->info("Sending Day 3 reminder for Task ID: {$task->task_id}");

                $result = $this->smsService->sendDay3Reminder($task);

                if ($result['success']) {
                    $task->storageFee->update(['sms_day3_sent' => true]);
                    $day3Count++;
                    $this->info("✓ Day 3 SMS sent successfully to {$task->user->name}");
                } else {
                    $this->error("✗ Failed to send Day 3 SMS: {$result['message']}");
                    Log::error('Day 3 SMS failed', [
                        'task_id' => $task->task_id,
                        'error' => $result['message'],
                    ]);
                }
            }

            // Day 4 Reminder
            if ($task->shouldSendReminderDay4()) {
                $this->info("Sending Day 4 reminder for Task ID: {$task->task_id}");

                $result = $this->smsService->sendDay4Reminder($task);

                if ($result['success']) {
                    $task->storageFee->update(['sms_day4_sent' => true]);
                    $day4Count++;
                    $this->info("✓ Day 4 SMS sent successfully to {$task->user->name}");
                } else {
                    $this->error("✗ Failed to send Day 4 SMS: {$result['message']}");
                    Log::error('Day 4 SMS failed', [
                        'task_id' => $task->task_id,
                        'error' => $result['message'],
                    ]);
                }
            }

            // Storage fee notification (Day 5+)
            if ($task->shouldApplyStorageFee() && $daysUncollected == 5) {
                $this->info("Sending storage fee notification for Task ID: {$task->task_id}");

                $result = $this->smsService->sendStorageFeeNotification($task);

                if ($result['success']) {
                    $storageFeeCount++;
                    $this->info("✓ Storage fee notification sent to {$task->user->name}");
                } else {
                    $this->error("✗ Failed to send storage fee notification: {$result['message']}");
                }
            }

            // Update storage fee calculations
            if ($task->storageFee) {
                $task->storageFee->updateDaysStored();
            }
        }

        $this->info("\n" . str_repeat('=', 50));
        $this->info("Collection Reminders Summary:");
        $this->info("Day 3 reminders sent: {$day3Count}");
        $this->info("Day 4 reminders sent: {$day4Count}");
        $this->info("Storage fee notifications sent: {$storageFeeCount}");
        $this->info("Total tasks checked: {$tasks->count()}");
        $this->info(str_repeat('=', 50));

        return Command::SUCCESS;
    }
}
