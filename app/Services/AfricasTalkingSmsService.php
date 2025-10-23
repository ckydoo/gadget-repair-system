<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfricasTalkingSmsService
{
    protected $apiKey;
    protected $username;
    protected $shortcode;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.africas_talking.api_key');
        $this->username = config('services.africas_talking.username');
        $this->shortcode = config('services.africas_talking.shortcode');

        // Use sandbox URL for testing, production URL for live
        $this->apiUrl = config('services.africas_talking.environment') === 'sandbox'
            ? 'https://api.sandbox.africastalking.com/version1/messaging'
            : 'https://api.africastalking.com/version1/messaging';
    }

    /**
     * Send SMS via Africa's Talking API
     *
     * @param string|array $phoneNumbers Phone number(s) in format: +263771234567
     * @param string $message SMS message content
     * @param User|null $user User model for logging
     * @param string $type Type of SMS (reminder_day3, reminder_day4, ready_for_collection, etc.)
     * @return array Response with success status and details
     */
    public function sendSms($phoneNumbers, string $message, ?User $user = null, string $type = 'general'): array
    {
        try {
            // Ensure phone numbers is an array
            if (!is_array($phoneNumbers)) {
                $phoneNumbers = [$phoneNumbers];
            }

            // Format phone numbers to ensure correct format
            $formattedNumbers = array_map(function($number) {
                return $this->formatPhoneNumber($number);
            }, $phoneNumbers);

            // Create SMS log entry
            $smsLog = null;
            if ($user) {
                $smsLog = SmsLog::create([
                    'user_id' => $user->id,
                    'phone_number' => implode(',', $formattedNumbers),
                    'message' => $message,
                    'type' => $type,
                    'status' => 'pending',
                ]);
            }

            // Prepare request data
            $data = [
                'username' => $this->username,
                'to' => implode(',', $formattedNumbers),
                'message' => $message,
            ];

            // Add shortcode if available
            if ($this->shortcode) {
                $data['from'] = $this->shortcode;
            }

            // Send request to Africa's Talking API
            $response = Http::withHeaders([
                'apiKey' => $this->apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ])->asForm()->post($this->apiUrl, $data);

            // Parse response
            $responseData = $response->json();

            // Check if request was successful
            if ($response->successful() && isset($responseData['SMSMessageData']['Recipients'])) {
                $recipients = $responseData['SMSMessageData']['Recipients'];

                // Check individual recipient status
                $allSent = true;
                foreach ($recipients as $recipient) {
                    if (isset($recipient['status']) && $recipient['status'] !== 'Success') {
                        $allSent = false;
                        break;
                    }
                }

                if ($allSent && $smsLog) {
                    $smsLog->markAsSent(json_encode($responseData));
                }

                Log::info('SMS sent successfully via Africa\'s Talking', [
                    'recipients' => $formattedNumbers,
                    'type' => $type,
                    'response' => $responseData
                ]);

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'data' => $responseData,
                    'sms_log_id' => $smsLog ? $smsLog->id : null,
                ];
            } else {
                // Request failed
                $errorMessage = $responseData['SMSMessageData']['Message'] ?? 'Unknown error occurred';

                if ($smsLog) {
                    $smsLog->markAsFailed(json_encode($responseData));
                }

                Log::error('Failed to send SMS via Africa\'s Talking', [
                    'error' => $errorMessage,
                    'response' => $responseData,
                    'recipients' => $formattedNumbers,
                ]);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'data' => $responseData,
                ];
            }

        } catch (\Exception $e) {
            Log::error('Exception while sending SMS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($smsLog) {
                $smsLog->markAsFailed($e->getMessage());
            }

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Send collection reminder on Day 3
     *
     * @param \App\Models\Task $task
     * @return array
     */
    public function sendDay3Reminder($task): array
    {
        $user = $task->user;

        $message = "Hi {$user->name}, your {$task->device_type} (Task ID: {$task->task_id}) has been ready for collection for 3 days. Please collect it soon to avoid storage fees after day 5. RepairHub";

        return $this->sendSms(
            $user->phone,
            $message,
            $user,
            'reminder_day3'
        );
    }

    /**
     * Send collection reminder on Day 4
     *
     * @param \App\Models\Task $task
     * @return array
     */
    public function sendDay4Reminder($task): array
    {
        $user = $task->user;

        $message = "URGENT: Hi {$user->name}, your {$task->device_type} (Task ID: {$task->task_id}) is still uncollected. Storage fees of \${$task->storageFee->daily_rate}/day will apply from day 5. Please collect ASAP. RepairHub";

        return $this->sendSms(
            $user->phone,
            $message,
            $user,
            'reminder_day4'
        );
    }

    /**
     * Send device ready for collection notification
     *
     * @param \App\Models\Task $task
     * @return array
     */
    public function sendDeviceReadyNotification($task): array
    {
        $user = $task->user;

        $message = "Good news {$user->name}! Your {$task->device_type} (Task ID: {$task->task_id}) is ready for collection. Please collect within 5 days to avoid storage fees. Track: " . route('tracking.show', $task->task_id) . " - RepairHub";

        return $this->sendSms(
            $user->phone,
            $message,
            $user,
            'ready_for_collection'
        );
    }

    /**
     * Send storage fee notification
     *
     * @param \App\Models\Task $task
     * @return array
     */
    public function sendStorageFeeNotification($task): array
    {
        $user = $task->user;
        $storageFee = $task->storageFee;

        $message = "Hi {$user->name}, storage fees are now being applied to your {$task->device_type} (Task ID: {$task->task_id}). Current fee: \${$storageFee->total_fee}. Daily rate: \${$storageFee->daily_rate}. Please collect immediately. RepairHub";

        return $this->sendSms(
            $user->phone,
            $message,
            $user,
            'storage_fee_notification'
        );
    }

    /**
     * Format phone number to E.164 format for Zimbabwe
     * Accepts formats: 0771234567, 771234567, +263771234567
     * Returns: +263771234567
     *
     * @param string $phoneNumber
     * @return string
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters except +
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);

        // If already in E.164 format with +, return as is
        if (strpos($cleaned, '+263') === 0) {
            return $cleaned;
        }

        // If starts with 263 (without +), add +
        if (strpos($cleaned, '263') === 0) {
            return '+' . $cleaned;
        }

        // If starts with 0, remove it and add +263
        if (strpos($cleaned, '0') === 0) {
            return '+263' . substr($cleaned, 1);
        }

        // If it's just the number without country code or leading 0
        // Assume it's Zimbabwean and add +263
        return '+263' . $cleaned;
    }

    /**
     * Get SMS balance from Africa's Talking (optional)
     *
     * @return array
     */
    public function getBalance(): array
    {
        try {
            $url = config('services.africas_talking.environment') === 'sandbox'
                ? 'https://api.sandbox.africastalking.com/version1/user'
                : 'https://api.africastalking.com/version1/user';

            $response = Http::withHeaders([
                'apiKey' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get($url, [
                'username' => $this->username,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch balance',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
