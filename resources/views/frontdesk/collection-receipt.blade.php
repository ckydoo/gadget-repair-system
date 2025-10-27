@extends('layouts.app')

@section('title', 'Collection Receipt')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8" id="receipt">
        <!-- Header -->
        <div class="text-center border-b pb-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800">COLLECTION RECEIPT</h1>
            <p class="text-gray-600 mt-2">Device Collection Confirmation</p>
        </div>

        <!-- Receipt Details -->
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-600 mb-2">TASK INFORMATION</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-gray-600 text-sm">Task ID:</span>
                        <span class="ml-2 font-mono font-bold">{{ $task->task_id }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600 text-sm">Device:</span>
                        <span class="ml-2">{{ $task->device_brand }} {{ $task->device_model }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600 text-sm">Category:</span>
                        <span class="ml-2">{{ $task->deviceCategory->name }}</span>
                    </div>
                    @if($task->serial_number)
                    <div>
                        <span class="text-gray-600 text-sm">Serial Number:</span>
                        <span class="ml-2 font-mono">{{ $task->serial_number }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-600 mb-2">COLLECTION DETAILS</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-gray-600 text-sm">Collected By:</span>
                        <span class="ml-2 font-semibold">{{ $task->collected_by }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600 text-sm">ID Type:</span>
                        <span class="ml-2">{{ ucwords(str_replace('_', ' ', $task->collector_id_type)) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600 text-sm">ID Number:</span>
                        <span class="ml-2 font-mono">{{ $task->collector_id_number }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600 text-sm">Collection Date:</span>
                        <span class="ml-2">{{ $task->collected_at->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-600 mb-2">CUSTOMER INFORMATION</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-gray-600 text-sm">Name:</span>
                    <span class="ml-2">{{ $task->user->name }}</span>
                </div>
                <div>
                    <span class="text-gray-600 text-sm">Phone:</span>
                    <span class="ml-2">{{ $task->user->phone }}</span>
                </div>
                <div>
                    <span class="text-gray-600 text-sm">Email:</span>
                    <span class="ml-2">{{ $task->user->email }}</span>
                </div>
            </div>
        </div>

        <!-- Invoice Details -->
        @if($task->invoice)
        <div class="mb-6 p-4 border border-gray-200 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-600 mb-3">PAYMENT DETAILS</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Invoice Number:</span>
                    <span class="font-mono">{{ $task->invoice->invoice_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Invoice Amount:</span>
                    <span class="font-semibold">${{ number_format($task->invoice->total, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment Status:</span>
                    <span class="px-2 py-1 rounded text-sm font-semibold {{ $task->invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ strtoupper($task->invoice->status) }}
                    </span>
                </div>
                @if($task->invoice->status === 'paid')
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment Method:</span>
                    <span>{{ ucwords(str_replace('_', ' ', $task->invoice->payment_method)) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment Date:</span>
                    <span>{{ $task->invoice->paid_at->format('d M Y, h:i A') }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Storage Fee (if applicable) -->
        @if($task->storageFee && $task->storageFee->total_fee > 0)
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h3 class="text-sm font-semibold text-yellow-800 mb-3">STORAGE FEE</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Days in Storage:</span>
                    <span>{{ $task->storageFee->days_stored }} days</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Daily Rate:</span>
                    <span>${{ number_format($task->storageFee->daily_rate, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold">
                    <span class="text-gray-800">Storage Fee Total:</span>
                    <span>${{ number_format($task->storageFee->total_fee, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment Status:</span>
                    <span class="px-2 py-1 rounded text-sm font-semibold {{ $task->storageFee->paid_at ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $task->storageFee->paid_at ? 'PAID' : 'UNPAID' }}
                    </span>
                </div>
            </div>
        </div>
        @endif

        <!-- Warranty Information -->
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h3 class="text-sm font-semibold text-blue-800 mb-2">📋 WARRANTY INFORMATION</h3>
            <p class="text-sm text-gray-700">
                This device is covered under warranty. The warranty period is
                <strong>{{ $task->deviceCategory->warranty_days ?? 30 }} days</strong> from the collection date.
            </p>
            <p class="text-sm text-gray-700 mt-2">
                Warranty valid until: <strong>{{ $task->collected_at->addDays($task->deviceCategory->warranty_days ?? 30)->format('d M Y') }}</strong>
            </p>
        </div>

        <!-- Processed By -->
        <div class="border-t pt-4 text-center text-sm text-gray-600">
            <p>Processed by: <span class="font-semibold">{{ auth()->user()->name }}</span></p>
            <p class="mt-1">Front Desk Staff</p>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t text-center text-sm text-gray-500">
            <p>Thank you for choosing our services!</p>
            <p class="mt-2">For support or inquiries, please contact us.</p>
        </div>

        <!-- Print Instructions -->
        <div class="mt-6 p-4 bg-gray-100 rounded-lg text-center no-print">
            <p class="text-gray-700 mb-3">Please keep this receipt for your records</p>
            <div class="flex justify-center gap-4">
                <button onclick="window.print()"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    🖨️ Print Receipt
                </button>
                <a href="{{ route('frontdesk.collection') }}"
                    class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                    ← Back to Collection
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }

    body {
        background: white;
    }

    #receipt {
        box-shadow: none;
        max-width: 100%;
    }
}
</style>
@endsection
