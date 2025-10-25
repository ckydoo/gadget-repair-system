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
                            @elseif($task->status === 'collected') bg-gray-100 text-gray-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            <span id="status-text">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                        </div>
                    </div>
                    <button onclick="refreshStatus()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        🔄 Refresh Status
                    </button>
                </div>

                <!-- Status-specific messages -->
                @if($task->status === 'assigned')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <strong>Task Assigned:</strong> Your device has been assigned to {{ $task->technician->name }}.
                        Awaiting check-in.
                    </p>
                </div>
                @elseif($task->status === 'checked_in')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <strong>Checked In:</strong> Your device has been received and is in our workshop.
                        Technician will begin work soon.
                    </p>
                </div>
                @elseif($task->status === 'in_progress')
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800">
                        <strong>In Progress:</strong> {{ $task->technician->name }} is currently working on your device.
                    </p>
                    @if($task->progress->count() > 0)
                    <p class="text-sm text-yellow-700 mt-2">
                        Latest update: {{ $task->progress->last()->notes }}
                    </p>
                    @endif
                </div>
                @elseif($task->status === 'waiting_parts')
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <p class="text-sm text-orange-800">
                        <strong>Waiting for Parts:</strong> We're waiting for required parts to arrive.
                        You'll be notified when work resumes.
                    </p>
                </div>
                @elseif($task->status === 'completed')
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-sm text-green-800">
                        <strong>Repair Completed:</strong> Your device has been successfully repaired!
                        @if($task->invoice && $task->invoice->status === 'paid')
                        Waiting to be marked as ready for collection.
                        @else
                        Please settle the invoice to proceed with collection.
                        @endif
                    </p>
                </div>
                @elseif($task->status === 'ready_for_collection')
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <p class="text-sm text-purple-800">
                        <strong>Ready for Collection:</strong> Your device is ready to be picked up!
                        Please visit our workshop.
                    </p>

                    @php
                        $daysUncollected = $task->getDaysUncollected();
                        $storageFee = $task->storageFee;
                        $dailyRate = $storageFee ? $storageFee->daily_rate : ($task->deviceCategory ? $task->deviceCategory->getStorageFeeRate() : 0);
                    @endphp

                    @if($daysUncollected >= 5)
                        @php
                            $chargeableDays = $daysUncollected - 5;
                            $currentFee = $chargeableDays * $dailyRate;
                        @endphp
                        <div class="mt-3 p-3 bg-red-50 border border-red-300 rounded-lg">
                            <p class="text-sm text-red-800 font-semibold">⚠️ Storage Fees Apply</p>
                            <p class="text-sm text-red-700 mt-1">
                                Your device has been ready for <strong>{{ $daysUncollected }} days</strong>
                            </p>
                            <div class="mt-2 text-red-800">
                                <p class="text-lg font-bold">Current Storage Fee: ${{ number_format($currentFee, 2) }}</p>
                                <p class="text-xs mt-1">
                                    (${{ number_format($dailyRate, 2) }}/day × {{ $chargeableDays }} chargeable days)
                                </p>
                            </div>
                            <p class="text-xs text-red-600 mt-2">
                                💡 First 5 days are free. Fees started on day 6.
                            </p>
                        </div>
                    @elseif($daysUncollected >= 3)
                        <div class="mt-3 p-3 bg-orange-50 border border-orange-300 rounded-lg">
                            <p class="text-sm text-orange-800 font-semibold">⏰ Collection Reminder</p>
                            <p class="text-sm text-orange-700 mt-1">
                                Your device has been ready for <strong>{{ $daysUncollected }} days</strong>
                            </p>
                            <p class="text-sm text-orange-700 mt-2">
                                <strong>Storage fees will apply after 5 days</strong><br>
                                Rate: ${{ number_format($dailyRate, 2) }}/day
                            </p>
                            <p class="text-xs text-orange-600 mt-2">
                                💡 Please collect soon to avoid charges!
                            </p>
                        </div>
                    @else
                        <div class="mt-3 p-3 bg-green-50 border border-green-300 rounded-lg">
                            <p class="text-sm text-green-800">
                                ✅ No storage fees yet
                            </p>
                            <p class="text-xs text-green-700 mt-1">
                                Device ready for {{ $daysUncollected }} {{ $daysUncollected == 1 ? 'day' : 'days' }}.
                                Storage fees apply after 5 days (${{ number_format($dailyRate, 2) }}/day)
                            </p>
                        </div>
                    @endif
                </div>
                @elseif($task->status === 'collected')
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <p class="text-sm text-gray-800">
                        <strong>Completed:</strong> Device collected on {{ $task->collected_at->format('M d, Y') }}
                    </p>

                    @if($task->storageFee && $task->storageFee->total_fee > 0)
                    <div class="mt-3 p-3 bg-yellow-50 border border-yellow-300 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            <strong>Storage Fee Paid:</strong> ${{ number_format($task->storageFee->total_fee, 2) }}
                        </p>
                        <p class="text-xs text-yellow-700 mt-1">
                            Device was stored for {{ $task->storageFee->days_stored }} days
                        </p>
                    </div>
                    @endif
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

                    <!-- Device Checked In -->
                    @if($task->status !== 'assigned')
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="font-semibold text-gray-800">Device Checked In</p>
                            <p class="text-sm text-gray-600">Your device has been received at the workshop</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $task->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Progress Updates -->
                    @foreach($task->progress as $progress)
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="font-semibold text-gray-800">{{ $progress->stage }}</p>
                            <p class="text-sm text-gray-600">{{ $progress->notes }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $progress->created_at->format('M d, Y H:i') }}
                                @if($progress->technician)
                                - by {{ $progress->technician->name }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @endforeach

                    <!-- Task Completed -->
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
                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="font-semibold text-gray-800">Ready for Collection</p>
                            <p class="text-sm text-gray-600">Your device is ready to be picked up</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $task->ready_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Device Collected -->
                    @if($task->collected_at)
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="font-semibold text-gray-800">Device Collected</p>
                            <p class="text-sm text-gray-600">Device has been collected by customer</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $task->collected_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Materials Used (if any) -->
            @if($task->materials->count() > 0)
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Materials Used</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Material</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Part #</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($task->materials as $material)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $material->material_name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $material->part_number ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $material->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">${{ number_format($material->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Category</p>
                        <p class="font-semibold text-gray-800">{{ $task->deviceCategory->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Brand</p>
                        <p class="font-semibold text-gray-800">{{ $task->device_brand }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Model</p>
                        <p class="font-semibold text-gray-800">{{ $task->device_model }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Service Type</p>
                        <p class="font-semibold text-gray-800">{{ ucfirst($task->type) }}</p>
                    </div>
                    @if($task->problem_description)
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Problem Description</p>
                        <p class="text-sm text-gray-700 mt-1">{{ $task->problem_description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Customer Info Card -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Customer Info</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Name</p>
                        <p class="font-semibold text-gray-800">{{ $task->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Phone</p>
                        <p class="font-semibold text-gray-800">{{ $task->user->phone }}</p>
                    </div>
                    @if($task->user->email)
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Email</p>
                        <p class="text-sm text-gray-700">{{ $task->user->email }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Technician Info -->
            @if($task->technician)
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Assigned Technician</h2>
                <div class="space-y-2">
                    <p class="font-semibold text-gray-800">{{ $task->technician->name }}</p>
                    @if($task->technician->phone)
                    <p class="text-sm text-gray-600">{{ $task->technician->phone }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Invoice Info -->
            @if($task->invoice)
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Invoice</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Invoice #:</span>
                        <span class="font-mono font-semibold">{{ $task->invoice->invoice_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $task->invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ ucfirst($task->invoice->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total:</span>
                        <span class="font-bold text-lg">${{ number_format($task->invoice->total, 2) }}</span>
                    </div>
                    @if($task->invoice->status === 'paid' && $task->invoice->paid_at)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Paid on:</span>
                        <span class="text-gray-800">{{ $task->invoice->paid_at->format('M d, Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Storage Fee Info -->
            @if($task->status === 'ready_for_collection' && $task->getDaysUncollected() >= 1)
            <div class="bg-white rounded-lg shadow-lg p-6 border-2 border-orange-200">
                <h2 class="text-xl font-bold text-gray-800 mb-4">📦 Storage Information</h2>

                @php
                    $daysUncollected = $task->getDaysUncollected();
                    $storageFee = $task->storageFee;
                    $dailyRate = $storageFee ? $storageFee->daily_rate : ($task->deviceCategory ? $task->deviceCategory->getStorageFeeRate() : 0);
                    $chargeableDays = max(0, $daysUncollected - 5);
                    $currentFee = $chargeableDays * $dailyRate;
                @endphp

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Days Uncollected:</span>
                        <span class="font-bold text-lg">{{ $daysUncollected }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Daily Rate:</span>
                        <span class="font-semibold">${{ number_format($dailyRate, 2) }}/day</span>
                    </div>

                    @if($daysUncollected >= 5)
                    <div class="border-t pt-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Chargeable Days:</span>
                            <span class="font-semibold">{{ $chargeableDays }}</span>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="text-gray-800 font-semibold">Current Fee:</span>
                            <span class="font-bold text-xl text-red-600">${{ number_format($currentFee, 2) }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            ⏰ Fee increases ${{ number_format($dailyRate, 2) }} daily
                        </p>
                    </div>
                    @else
                    <div class="bg-green-50 border border-green-200 rounded p-3 mt-2">
                        <p class="text-green-800 font-semibold text-center">
                            ✅ Not Charging Yet
                        </p>
                        <p class="text-xs text-green-700 text-center mt-1">
                            {{ 5 - $daysUncollected }} {{ (5 - $daysUncollected) == 1 ? 'day' : 'days' }} remaining before charges apply
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Warranty Info -->
            @if($task->warranty_days > 0 && $task->status === 'collected')
            <div class="bg-blue-50 border border-blue-300 rounded-lg p-6">
                <h2 class="text-lg font-bold text-blue-800 mb-2">🛡️ Warranty</h2>
                <p class="text-sm text-blue-700 mb-2">
                    This repair is covered by a <strong>{{ $task->warranty_days }}-day warranty</strong>.
                </p>
                @if($task->warranty_expires_at)
                <p class="text-xs text-blue-600">
                    Valid until: <strong>{{ $task->warranty_expires_at->format('F d, Y') }}</strong>
                </p>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// Auto-refresh status every 30 seconds
let statusRefreshInterval;

function refreshStatus() {
    fetch(`{{ route('tracking.status', $task->task_id) }}`)
        .then(response => response.json())
        .then(data => {
            // Update status badge
            document.getElementById('status-text').textContent = data.status_label;
            document.getElementById('last-updated').textContent = data.last_update;

            // Update badge color based on status
            const badge = document.getElementById('status-badge');
            badge.className = 'inline-block px-4 py-2 text-sm font-semibold rounded-full';

            if (data.status === 'completed' || data.status === 'ready_for_collection') {
                badge.classList.add('bg-green-100', 'text-green-800');
            } else if (data.status === 'in_progress') {
                badge.classList.add('bg-blue-100', 'text-blue-800');
            } else if (data.status === 'waiting_parts') {
                badge.classList.add('bg-yellow-100', 'text-yellow-800');
            } else if (data.status === 'collected') {
                badge.classList.add('bg-gray-100', 'text-gray-800');
            } else {
                badge.classList.add('bg-gray-100', 'text-gray-800');
            }

            // If status changed significantly, reload page
            if (data.status !== '{{ $task->status }}') {
                location.reload();
            }
        })
        .catch(error => console.error('Error refreshing status:', error));
}

// Start auto-refresh
statusRefreshInterval = setInterval(refreshStatus, 30000);

// Clear interval when leaving page
window.addEventListener('beforeunload', () => {
    clearInterval(statusRefreshInterval);
});
</script>
@endsection
