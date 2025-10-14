<!-- ============================================================ -->
<!-- VIEW 2: Track Progress Detail Page -->
<!-- File: resources/views/tracking/show.blade.php -->
<!-- ============================================================ -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('tracking.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Track Another Device
        </a>
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Task Progress</h1>
                <p class="text-gray-600">Task ID: <span class="font-mono font-bold text-blue-600">{{ $task->task_id }}</span></p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Last Updated</p>
                <p class="font-semibold text-gray-800" id="last-updated">{{ $task->updated_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Current Status Card -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Current Status</h2>

                <div class="flex items-center justify-between mb-6">
                    <div>
                        <div id="status-badge" class="inline-block px-4 py-2 text-sm font-semibold rounded-full
                            @if($task->status === 'completed' || $task->status === 'ready_for_collection') bg-green-100 text-green-800
                            @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                            @elseif($task->status === 'waiting_parts') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </div>
                    </div>

                    @if($task->status === 'ready_for_collection')
                    <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-2">
                        <p class="text-sm font-semibold text-green-800">✓ Ready for Pickup!</p>
                    </div>
                    @endif
                </div>

                <!-- Progress Bar -->
                <div class="mb-6">
                    <div class="flex justify-between text-xs text-gray-600 mb-2">
                        <span>Assigned</span>
                        <span>In Progress</span>
                        <span>Completed</span>
                        <span>Collected</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full transition-all duration-500"
                             style="width: {{ $task->status === 'assigned' ? '25%' : ($task->status === 'checked_in' || $task->status === 'in_progress' ? '50%' : ($task->status === 'completed' || $task->status === 'ready_for_collection' ? '75%' : '100%')) }}">
                        </div>
                    </div>
                </div>

                <!-- Status Messages -->
                @if($task->status === 'assigned')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <strong>Next Step:</strong> Please bring your device to our workshop for check-in.
                    </p>
                </div>
                @elseif($task->status === 'in_progress')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <strong>In Progress:</strong> Our technician is currently working on your device.
                    </p>
                </div>
                @elseif($task->status === 'ready_for_collection')
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-sm text-green-800">
                        <strong>Ready!</strong> Your device is ready for collection. Please visit our workshop.
                    </p>
                    @if($task->getDaysUncollected() >= 3)
                    <p class="text-sm text-orange-600 mt-2">
                        ⚠️ Your device has been ready for {{ $task->getDaysUncollected() }} days.
                        @if($task->getDaysUncollected() >= 5)
                        Storage fees of ${{ number_format($task->storageFee->daily_rate ?? 0, 2) }}/day are being applied.
                        @endif
                    </p>
                    @endif
                </div>
                @elseif($task->status === 'collected')
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <p class="text-sm text-gray-800">
                        <strong>Completed:</strong> Device collected on {{ $task->collected_at->format('M d, Y') }}
                    </p>
                </div>
                @endif
            </div>

            <!-- Progress Timeline -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Progress Timeline</h2>

                <div class="space-y-6">
                    <!-- Task Assigned -->
                    @if($task->assigned_at)
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="font-semibold text-gray-800">Task Assigned</p>
                            <p class="text-sm text-gray-600">Assigned to {{ $task->technician->name }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $task->assigned_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Progress Updates -->
                    @forelse($task->progress as $progress)
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="font-semibold text-gray-800">{{ $progress->stage }}</p>
                            @if($progress->notes)
                            <p class="text-sm text-gray-600">{{ $progress->notes }}</p>
                            @endif
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $progress->created_at->format('M d, Y H:i') }} by {{ $progress->technician->name }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <p class="text-gray-500">No progress updates yet</p>
                    </div>
                    @endforelse

                    <!-- Completed -->
                    @if($task->completed_at)
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="font-semibold text-gray-800">Repair Completed</p>
                            <p class="text-sm text-gray-600">Your device has been successfully repaired</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $task->completed_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Ready for Collection -->
                    @if($task->ready_at)
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="font-semibold text-gray-800">Ready for Collection</p>
                            <p class="text-sm text-gray-600">Device is ready to be picked up</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $task->ready_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Materials Used -->
            @if($task->materials->isNotEmpty())
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Materials Used</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Material</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Part #</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($task->materials as $material)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $material->material_name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $material->part_number ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ $material->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800 text-right">${{ number_format($material->unit_price, 2) }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-800 text-right">${{ number_format($material->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Invoice -->
            @if($task->invoice)
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Invoice</h2>
                <div class="space-y-3">
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-600">Invoice Number:</span>
                        <span class="font-semibold">{{ $task->invoice->invoice_number }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-600">Materials Cost:</span>
                        <span class="font-semibold">${{ number_format($task->invoice->materials_cost, 2) }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-600">Labour Cost:</span>
                        <span class="font-semibold">${{ number_format($task->invoice->labour_cost, 2) }}</span>
                    </div>
                    @if($task->invoice->transport_cost > 0)
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-600">Transport Cost:</span>
                        <span class="font-semibold">${{ number_format($task->invoice->transport_cost, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="font-semibold">${{ number_format($task->invoice->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-600">Tax (15%):</span>
                        <span class="font-semibold">${{ number_format($task->invoice->tax, 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-2">
                        <span class="text-gray-800 font-bold">Total:</span>
                        <span class="font-bold text-lg">${{ number_format($task->invoice->total, 2) }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Side Panel -->
        <div class="space-y-6">
            <!-- Device Details Card -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Device Details</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Device Type:</span>
                        <span class="font-semibold">{{ $task->device_type }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Brand:</span>
                        <span class="font-semibold">{{ $task->brand }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Model:</span>
                        <span class="font-semibold">{{ $task->model }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Serial Number:</span>
                        <span class="font-semibold font-mono">{{ $task->serial_number }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
