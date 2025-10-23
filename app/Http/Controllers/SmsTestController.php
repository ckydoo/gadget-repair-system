<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\AfricasTalkingSmsService;
use Illuminate\Http\Request;

class SmsTestController extends Controller
{
    protected $smsService;

    public function __construct(AfricasTalkingSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Show SMS test form
     */
    public function index()
    {
        return view('sms.test');
    }

    /**
     * Send test SMS
     */
    public function sendTest(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'message' => 'required|string|max:160',
        ]);

        $result = $this->smsService->sendSms(
            $request->phone_number,
            $request->message,
            auth()->user(),
            'test'
        );

        if ($result['success']) {
            return back()->with('success', 'SMS sent successfully! Check logs for details.');
        } else {
            return back()->with('error', 'Failed to send SMS: ' . $result['message']);
        }
    }

    /**
     * Test Day 3 reminder for a specific task
     */
    public function testDay3Reminder($taskId)
    {
        $task = Task::with(['user', 'storageFee'])->findOrFail($taskId);

        if (!$task->storageFee) {
            return back()->with('error', 'Task must have storage fee record');
        }

        $result = $this->smsService->sendDay3Reminder($task);

        if ($result['success']) {
            $task->storageFee->update(['sms_day3_sent' => true]);
            return back()->with('success', 'Day 3 reminder sent successfully!');
        } else {
            return back()->with('error', 'Failed to send Day 3 reminder: ' . $result['message']);
        }
    }

    /**
     * Test Day 4 reminder for a specific task
     */
    public function testDay4Reminder($taskId)
    {
        $task = Task::with(['user', 'storageFee'])->findOrFail($taskId);

        if (!$task->storageFee) {
            return back()->with('error', 'Task must have storage fee record');
        }

        $result = $this->smsService->sendDay4Reminder($task);

        if ($result['success']) {
            $task->storageFee->update(['sms_day4_sent' => true]);
            return back()->with('success', 'Day 4 reminder sent successfully!');
        } else {
            return back()->with('error', 'Failed to send Day 4 reminder: ' . $result['message']);
        }
    }

    /**
     * Get SMS balance
     */
    public function getBalance()
    {
        $result = $this->smsService->getBalance();

        return response()->json($result);
    }

    /**
     * View SMS logs
     */
    public function logs()
    {
        $logs = \App\Models\SmsLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('sms.logs', compact('logs'));
    }
}
