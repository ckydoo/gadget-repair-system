
<!-- ============================================================ -->
<!-- VIEW 2: Task Details Page -->
<!-- File: resources/views/technician/task-details.blade.php -->
<!-- ============================================================ -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="mb-8">
        <a href="{{ route('technician.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Back to Dashboard
        </a>
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Task Details</h1>
                <p class="text-gray-600">Task ID: <span class="font-mono font-bold text-blue-600">{{ $task->task_id }}</span></p>
            </div>
            <div>
                <span class="px-4 py-2 text-sm font-semibold rounded-full
                    @if($task->status === 'in_progress') bg-blue-100 text-blue-800
                    @elseif($task->status === 'waiting_parts') bg-yellow-100 text-yellow-800
                    @elseif($task->status === 'completed') bg-green-100 text-green-800
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
            <!-- Quick Actions -->
            @if(!in_array($task->status, ['completed', 'ready_for_collection', 'collected']))
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>

                <form action="{{ route('technician.task.update-status', $task->id) }}" method="POST" class="flex gap-3">
                    @csrf
                    <select name="status" required class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Change Status...</option>
                        <option value="checked_in" {{ $task->status === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                        <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="waiting_parts" {{ $task->status === 'waiting_parts' ? 'selected' : '' }}>Waiting for Parts</option>
                    </select>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                        Update Status
                    </button>
                </form>
            </div>
            @endif

            <!-- Add Progress Update -->
            @if(!in_array($task->status, ['completed', 'ready_for_collection', 'collected']))
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
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>

                        <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                            Add Progress Update
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Progress Timeline -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Progress Timeline</h2>

                @forelse($task->progress as $progress)
                <div class="flex mb-6 last:mb-0">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="font-semibold text-gray-800">{{ $progress->stage }}</p>
                        @if($progress->notes)
                        <p class="text-sm text-gray-600 mt-1">{{ $progress->notes }}</p>
                        @endif
                        <p class="text-xs text-gray-500 mt-2">
                            {{ $progress->created_at->format('M d, Y H:i') }} by {{ $progress->technician->name }}
                        </p>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No progress updates yet</p>
                @endforelse
            </div>

            <!-- Materials Used -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Materials Used</h2>

                @if(!in_array($task->status, ['completed', 'ready_for_collection', 'collected']))
                <form action="{{ route('technician.task.add-material', $task->id) }}" method="POST" class="mb-6 p-4 bg-gray-50 rounded-lg">
                    @csrf
                    <p class="font-semibold text-gray-800 mb-3">Add Material</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <input type="text" name="material_name" required placeholder="Material Name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <input type="text" name="part_number" placeholder="Part Number (Optional)"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <input type="number" name="quantity" required min="1" placeholder="Quantity"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <input type="number" name="unit_price" required min="0" step="0.01" placeholder="Unit Price"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                    <button type="submit" class="mt-3 w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                        Add Material
                    </button>
                </form>
                @endif

                @if($task->materials->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Material</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Qty</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Unit Price</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($task->materials as $material)
                            <tr>
                                <td class="px-4 py-3 text-sm">
                                    <p class="font-medium text-gray-800">{{ $material->material_name }}</p>
                                    @if($material->part_number)
                                    <p class="text-xs text-gray-500">{{ $material->part_number }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-gray-800">{{ $material->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-800">${{ number_format($material->unit_price, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-800">${{ number_format($material->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                            <tr class="bg-gray-50 font-bold">
                                <td colspan="3" class="px-4 py-3 text-sm text-right">Total Materials Cost:</td>
                                <td class="px-4 py-3 text-sm text-right">${{ number_format($task->materials->sum('total_price'), 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-gray-500 text-center py-4">No materials added yet</p>
                @endif
            </div>

            <!-- Complete Task -->
            @if($task->status === 'in_progress' && !$task->invoice)
            <div class="bg-green-50 border-2 border-green-300 rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Complete Task</h2>

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
                            Mark as Completed & Generate Invoice
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Mark Ready for Collection -->
            @if($task->status === 'completed' && $task->invoice && $task->invoice->status === 'paid')
            <div class="bg-purple-50 border-2 border-purple-300 rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Mark Ready for Collection</h2>
                <p class="text-gray-600 mb-4">Invoice has been paid. Mark this device as ready for customer pickup.</p>

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
                <h3 class="text-lg font-bold text-gray-800 mb-4">Device Information</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-500">Category</p>
                        <p class="font-semibold">{{ $task->deviceCategory->name }}</p>
                    </div>
                    @if($task->device_brand)
                    <div>
                        <p class="text-gray-500">Brand & Model</p>
                        <p class="font-semibold">{{ $task->device_brand }} {{ $task->device_model }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-gray-500">Type</p>
                        <p class="font-semibold capitalize">{{ $task->type }}</p>
                    </div>
                    @if($task->problem_description)
                    <div>
                    <p class="text-gray-500">Problem Description</p>
                    <p class="text-gray-800 mt-1">{{ $task->problem_description }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection
