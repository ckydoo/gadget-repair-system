@extends('layouts.app')

@section('title', 'Collection Receipt')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <!-- Print Button -->
        <div class="mb-6 text-right no-print">
            <button onclick="window.print()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Print Receipt
            </button>
            <a href="{{ route('frontdesk.collection') }}" class="ml-3 px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Back to Collection
            </a>
        </div>

        <!-- Receipt -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-8 border-b pb-6">
                <h1 class="text-3xl font-bold text-gray-800">COLLECTION RECEIPT</h1>
                <p class="text-gray-600 mt-2">Device Successfully Collected</p>
            </div>

            <!-- Receipt Details -->
            <div class="mb-8">
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Receipt Date</p>
                        <p class="font-semibold">{{ $task->collected_at->format('F d, Y H:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Task ID</p>
                        <p class="font-mono font-bold text-lg">{{ $task->task_id }}</p>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h3 class="font-bold text-gray-800 mb-4">Customer Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Name</p>
                            <p class="font-semibold">{{ $task->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Phone</p>
                            <p class="font-semibold">{{ $task->user->phone }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-semibold">{{ $task->user->email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Device Info -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h3 class="font-bold text-gray-800 mb-4">Device Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Device</p>
                            <p class="font-semibold">{{ $task->device_brand }} {{ $task->device_model }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Category</p>
                            <p class="font-semibold">{{ $task->deviceCategory->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Technician</p>
                            <p class="font-semibold">{{ $task->technician->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Service Type</p>
                            <p class="font-semibold">{{ ucfirst($task->type) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h3 class="font-bold text-gray-800 mb-4">Service Timeline</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Check-in Date:</span>
                            <span class="font-semibold">{{ $task->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        @if($task->started_at)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Work Started:</span>
                            <span class="font-semibold">{{ $task->started_at->format('M d, Y H:i') }}</span>
                        </div>
                        @endif
                        @if($task->completed_at)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Completed:</span>
                            <span class="font-semibold">{{ $task->completed_at->format('M d, Y H:i') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-600">Ready for Collection:</span>
                            <span class="font-semibold">{{ $task->ready_at->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Collected:</span>
                            <span class="font-semibold text-green-600">{{ $task->collected_at->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                @if($task->invoice)
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h3 class="font-bold text-gray-800 mb-4">Financial Summary</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Invoice Number:</span>
                            <span class="font-mono font-semibold">{{ $task->invoice->invoice_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Materials Cost:</span>
                            <span class="font-semibold">${{ number_format($task->invoice->materials_cost, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Labour Cost:</span>
                            <span class="font-semibold">${{ number_format($task->invoice->labour_cost, 2) }}</span>
                        </div>
                        @if($task->invoice->transport_cost > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Transport Cost:</span>
                            <span class="font-semibold">${{ number_format($task->invoice->transport_cost, 2) }}</span>
                        </div>
                        @endif
                        @if($task->storageFee && $task->storageFee->total_fee > 0)
                        <div class="flex justify-between text-yellow-700">
                            <span class="font-medium">Storage Fee ({{ $task->getDaysUncollected() }} days):</span>
                            <span class="font-semibold">${{ number_format($task->storageFee->total_fee, 2) }}</span>
                        </div>
                        @endif
                        <div class="border-t pt-2 mt-2">
                            <div class="flex justify-between text-lg">
                                <span class="font-bold text-gray-800">Total Paid:</span>
                                <span class="font-bold text-green-600">
                                    ${{ number_format($task->invoice->total + ($task->storageFee ? $task->storageFee->total_fee : 0), 2) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Payment Method:</span>
                            <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $task->invoice->payment_method ?? 'N/A')) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Paid On:</span>
                            <span class="font-semibold">{{ $task->invoice->paid_at ? $task->invoice->paid_at->format('M d, Y H:i') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Warranty Info -->
                @if($task->warranty_days > 0)
                <div class="bg-blue-50 border border-blue-300 rounded-lg p-6 mb-6">
                    <h3 class="font-bold text-blue-800 mb-2">🛡️ Warranty Information</h3>
                    <p class="text-blue-700">
                        This repair is covered by a <strong>{{ $task->warranty_days }}-day warranty</strong>.
                    </p>
                    <p class="text-sm text-blue-600 mt-2">
                        Valid until: <strong>{{ $task->warranty_expires_at->format('F d, Y') }}</strong>
                    </p>
                </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="border-t pt-6 text-center text-sm text-gray-600">
                <p class="mb-2">Thank you for choosing our repair service!</p>
                <p>For support or inquiries, please contact us or visit our website.</p>
                <p class="mt-4 font-semibold">Keep this receipt for warranty claims</p>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none;
    }
    body {
        background: white;
    }
}
</style>
@endsection
