<?php

namespace App\Listeners;

use App\Events\InvoiceCreated;
use App\Mail\InvoiceCreatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendInvoiceEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\InvoiceCreated  $event
     * @return void
     */
    public function handle(InvoiceCreated $event)
    {
        $invoice = $event->invoice;

        // Load relationships if not already loaded
        if (!$invoice->relationLoaded('task')) {
            $invoice->load('task');
        }
        if (!$invoice->relationLoaded('user')) {
            $invoice->load('user');
        }

        $task = $invoice->task;
        $client = $invoice->user;

        // Check if client has email
        if (!$client || !$client->email) {
            Log::warning('Invoice created but client has no email', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);
            return;
        }

        try {
            Mail::to($client->email)->send(new InvoiceCreatedMail($invoice, $task, $client));

            Log::info('Invoice email sent successfully', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_email' => $client->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \App\Events\InvoiceCreated  $event
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(InvoiceCreated $event, $exception)
    {
        Log::error('Invoice email listener failed', [
            'invoice_id' => $event->invoice->id,
            'error' => $exception->getMessage()
        ]);
    }
}
