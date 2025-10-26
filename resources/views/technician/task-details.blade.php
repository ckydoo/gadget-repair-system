@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="mb-8">
        <a href="{{ route('technician.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Back to Dashboard
        </a>
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Task Details / Job Card</h1>
                <p class="text-gray-600">Task ID: <span class="font-mono font-bold text-blue-600">{{ $task->task_id }}</span></p>
            </div>
            <div>
                <span class="px-4 py-2 text-sm font-semibold rounded-full
                    @if($task->status === 'in_progress') bg-blue-100 text-blue-800
                    @elseif($task->status === 'waiting_parts') bg-yellow-100 text-yellow-800
                    @elseif($task->status === 'completed') bg-green-100 text-green-800
                    @elseif($task->status === 'ready_for_collection') bg-purple-100 text-purple-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                </span>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Device Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Device Information</h2>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Device Type</p>
                        <p class="font-semibold">{{ $task->deviceCategory->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Brand & Model</p>
                        <p class="font-semibold">{{ $task->device_brand }} {{ $task->device_model }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-gray-500">Problem Description</p>
                        <p class="font-semibold">{{ $task->problem_description }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Service Type</p>
                        <p class="font-semibold">{{ ucfirst($task->type) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Warranty</p>
                        <p class="font-semibold">{{ $task->warranty_days }} days</p>
                    </div>
                </div>
            </div>

            <!-- Progress Updates -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Progress Timeline</h2>

                @if($task->progress->count() > 0)
                <div class="space-y-4">
                    @foreach($task->progress->sortByDesc('created_at') as $progress)
                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-gray-800">{{ $progress->stage }}</h3>
                                @if($progress->notes)
                                <p class="text-sm text-gray-600 mt-1">{{ $progress->notes }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">
                                    By {{ $progress->technician->name }} • {{ $progress->created_at->format('M d, Y H:i') }}
                                </p>
                            </div>
                        </div>

                        @if($progress->images && count($progress->images) > 0)
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            @foreach($progress->images as $image)
                            <div class="relative group">
                                <img src="{{ asset('storage/' . $image) }}"
                                     alt="Progress image"
                                     class="w-full h-24 object-cover rounded cursor-pointer hover:opacity-75 transition"
                                     onclick="window.open('{{ asset('storage/' . $image) }}', '_blank')">
                                <a href="{{ asset('storage/' . $image) }}"
                                    download
                                    class="absolute top-2 right-2 bg-blue-600 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition"
                                    title="Download image"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-center py-4">No progress updates yet</p>
                @endif
            </div>

            <!-- Quick Status Change -->
            @if(!in_array($task->status, ['ready_for_collection', 'collected']))
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Status Update</h2>

                <form action="{{ route('technician.task.update-status', $task->id) }}" method="POST" class="flex gap-3">
                    @csrf
                    <select name="status" required class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Change Status...</option>
                        <option value="checked_in" {{ $task->status === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                        <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="waiting_parts" {{ $task->status === 'waiting_parts' ? 'selected' : '' }}>Waiting for Parts</option>
                        <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                        Update Status
                    </button>
                </form>
            </div>
            @endif

            <!-- Add Progress Update -->
            @if(!in_array($task->status, ['ready_for_collection', 'collected']))
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Add Progress Update</h2>

                <form action="{{ route('technician.task.add-progress', $task->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Stage/Milestone *</label>
                            <input type="text" name="stage" required placeholder="e.g., Diagnosis Complete, Screen Replaced"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                            <textarea name="notes" rows="3" placeholder="Add any additional details..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Upload Images (Optional)</label>
                            <input type="file" name="images[]" multiple accept="image/*"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Maximum 5 images, 5MB each</p>
                        </div>

                        <button type="submit" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                            Add Progress Update
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Add Material -->
            @if(!in_array($task->status, ['ready_for_collection', 'collected']))
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Add Material Used</h2>

                <form action="{{ route('technician.task.add-material', $task->id) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Material/Part Name *</label>
                            <input type="text" name="material_name" required placeholder="e.g., LCD Screen, Battery"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Part Number</label>
                            <input type="text" name="part_number" placeholder="Optional"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                            <input type="number" name="quantity" required min="1" value="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Unit Price ($) *</label>
                            <input type="number" name="unit_price" required min="0" step="0.01" placeholder="0.00"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="col-span-2">
                            <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                                Add Material
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @endif

            <!-- Materials Used -->
            @if($task->materials->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Materials Used</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Material</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Part #</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($task->materials as $material)
                            <tr>
                                <td class="px-4 py-3 text-sm">{{ $material->material_name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $material->part_number ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right">{{ $material->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-right">${{ number_format($material->unit_price, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold">${{ number_format($material->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                            <tr class="bg-gray-50 font-bold">
                                <td colspan="4" class="px-4 py-3 text-sm text-right">Total Materials Cost:</td>
                                <td class="px-4 py-3 text-sm text-right">${{ number_format($task->materials->sum('total_price'), 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Complete Task & Generate Invoice -->
            @if($task->status === 'in_progress' || $task->status === 'waiting_parts')
            <div class="bg-green-50 border-2 border-green-300 rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Complete Task & Generate Invoice</h2>
                <p class="text-gray-600 mb-4">Ready to complete this task? Enter your labour hours to generate the final invoice.</p>

                <form action="{{ route('technician.task.complete', $task->id) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Labour Hours *</label>
                            <input type="number" name="labour_hours" required min="0" step="0.5" placeholder="e.g., 2.5"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">Your hourly rate: ${{ number_format($task->technician->technician->hourly_rate, 2) }}/hour</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Completion Notes (Optional)</label>
                            <textarea name="notes" rows="3" placeholder="Summary of work completed..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>

                        <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold text-lg">
                            Complete Task & Generate Invoice
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Mark Ready for Collection - MODIFIED: No payment requirement -->
            @if($task->status === 'completed')
            <div class="bg-purple-50 border-2 border-purple-300 rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Mark Ready for Collection</h2>
                <p class="text-gray-600 mb-4">Mark this device as ready for customer pickup.</p>

                @if($task->invoice)
                    @if($task->invoice->status === 'paid')
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        ✓ Invoice has been paid - Ready to release
                    </div>
                    @else
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                        ⚠ Invoice is not paid yet - Payment will be collected at pickup
                    </div>
                    @endif
                @else
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                    ⚠ No invoice generated yet - Will need to be created before collection
                </div>
                @endif

                <form action="{{ route('technician.task.mark-ready', $task->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold text-lg">
                        Mark Ready for Collection
                    </button>
                </form>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Customer Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Customer Information</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-500">Name</p>
                        <p class="font-semibold">{{ $task->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Phone</p>
                        <p class="font-semibold">{{ $task->user->phone }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Email</p>
                        <p class="font-semibold">{{ $task->user->email }}</p>
                    </div>
                </div>
            </div>

            <!-- Device Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Task Timeline</h3>
                <div class="space-y-3 text-sm">
                    @if($task->assigned_at)
                    <div>
                        <p class="text-gray-500">Assigned</p>
                        <p class="font-semibold">{{ $task->assigned_at->format('M d, Y H:i') }}</p>
                    </div>
                    @endif
                    @if($task->started_at)
                    <div>
                        <p class="text-gray-500">Started</p>
                        <p class="font-semibold">{{ $task->started_at->format('M d, Y H:i') }}</p>
                    </div>
                    @endif
                    @if($task->completed_at)
                    <div>
                        <p class="text-gray-500">Completed</p>
                        <p class="font-semibold">{{ $task->completed_at->format('M d, Y H:i') }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-gray-500">Complexity Weight</p>
                        <p class="font-semibold">{{ $task->complexity_weight }}x</p>
                    </div>
                </div>
            </div>

            <!-- Invoice Info (if exists) -->
            @if($task->invoice)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Invoice</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-500">Invoice #</p>
                        <p class="font-mono font-semibold">{{ $task->invoice->invoice_number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Status</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $task->invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ ucfirst($task->invoice->status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-gray-500">Total Amount</p>
                        <p class="font-bold text-lg">${{ number_format($task->invoice->total, 2) }}</p>
                    </div>
                    <div class="pt-2 border-t">
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Materials:</span>
                            <span>${{ number_format($task->invoice->materials_cost, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Labour:</span>
                            <span>${{ number_format($task->invoice->labour_cost, 2) }}</span>
                        </div>
                        @if($task->invoice->transport_cost > 0)
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Transport:</span>
                            <span>${{ number_format($task->invoice->transport_cost, 2) }}</span>
                        </div>
                        @endif
                        @if($task->invoice->diagnostic_fee > 0)
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Diagnostic:</span>
                            <span>${{ number_format($task->invoice->diagnostic_fee, 2) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
