<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $task;
    public $client;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Invoice  $invoice
     * @param  \App\Models\Task  $task
     * @param  \App\Models\User  $client
     * @return void
     */
    public function __construct(Invoice $invoice, Task $task, User $client)
    {
        $this->invoice = $invoice;
        $this->task = $task;
        $this->client = $client;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Invoice ' . $this->invoice->invoice_number . ' - Device Repair Service',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.invoice-created',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
