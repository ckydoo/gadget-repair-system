<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Task;
use App\Events\InvoiceCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Example: Create invoice and trigger email
     *
     * This shows how to integrate the InvoiceCreated event
     * into your existing invoice creation logic
     */
    public function createInvoice(Request $request, $taskId)
    {
        $task = Task::with(['user'])->findOrFail($taskId);

        DB::beginTransaction();
        try {
            // Create the invoice
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'task_id' => $task->id,
                'user_id' => $task->user_id,
                'materials_cost' => $request->materials_cost ?? 0,
                'labour_cost' => $request->labour_cost ?? 0,
                'transport_cost' => $request->transport_cost ?? 0,
                'diagnostic_fee' => $request->diagnostic_fee ?? 0,
                'status' => 'pending',
            ]);

            // Calculate totals
            $invoice->calculateTotals()->save();

            // IMPORTANT: Dispatch the event to send email automatically
            event(new InvoiceCreated($invoice));

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Invoice created successfully! Email sent to client.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    /**
     * Alternative: Using the event helper in technician controller
     *
     * Example of how to add the event to your existing TechnicianController
     * when completing a job and generating an invoice
     */
    public function completeJobAndGenerateInvoice(Request $request, $taskId)
    {
        $task = Task::with(['user'])->findOrFail($taskId);

        $request->validate([
            'materials_cost' => 'required|numeric|min:0',
            'labour_cost' => 'required|numeric|min:0',
            'completion_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Update task status
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completion_notes' => $request->completion_notes,
            ]);

            // Generate invoice
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'task_id' => $task->id,
                'user_id' => $task->user_id,
                'materials_cost' => $request->materials_cost,
                'labour_cost' => $request->labour_cost,
                'transport_cost' => $task->transport_cost ?? 0,
                'diagnostic_fee' => $task->diagnostic_fee ?? 0,
                'status' => 'pending',
            ]);

            $invoice->calculateTotals()->save();

            // Trigger the email notification
            event(new InvoiceCreated($invoice));

            DB::commit();

            return redirect()
                ->route('technician.jobs')
                ->with('success', 'Job completed and invoice generated! Email sent to client.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to complete job: ' . $e->getMessage());
        }
    }

    /**
     * Example: Adding event to existing manager/supervisor invoice creation
     */
    public function managerCreateInvoice(Request $request, $taskId)
    {
        $task = Task::with(['user'])->findOrFail($taskId);

        $request->validate([
            'materials_cost' => 'required|numeric|min:0',
            'labour_cost' => 'required|numeric|min:0',
            'transport_cost' => 'nullable|numeric|min:0',
            'diagnostic_fee' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'task_id' => $task->id,
                'user_id' => $task->user_id,
                'materials_cost' => $request->materials_cost,
                'labour_cost' => $request->labour_cost,
                'transport_cost' => $request->transport_cost ?? 0,
                'diagnostic_fee' => $request->diagnostic_fee ?? 0,
                'status' => 'pending',
            ]);

            $invoice->calculateTotals()->save();

            // Fire the event - email will be sent automatically
            event(new InvoiceCreated($invoice));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice created and email sent to client',
                'invoice' => $invoice
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice: ' . $e->getMessage()
            ], 500);
        }
    }
}
