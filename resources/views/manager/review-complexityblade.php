@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-8">
        <a href="{{ route('manager.tasks') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Back to Tasks
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Review Task Complexity</h1>
        <p class="text-gray-600">Adjust the complexity weight for this task</p>
    </div>

    <!-- Task Information -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Task Information</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Task ID</p>
                <p class="font-semibold text-lg">{{ $task->task_id }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Current Complexity</p>
                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">
                    {{ $task->complexity_weight }}x Weight
                </span>
            </div>
            <div>
                <p class="text-sm text-gray-500">Customer</p>
                <p class="font-semibold">{{ $task->user->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Technician</p>
                <p class="font-semibold">{{ $task->technician->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Device</p>
                <p class="font-semibold">{{ $task->device_brand }} {{ $task->device_model }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Category</p>
                <p class="font-semibold">{{ $task->deviceCategory->name }}</p>
            </div>
            <div class="col-span-2">
                <p class="text-sm text-gray-500">Problem Description</p>
                <p class="text-gray-800 mt-1">{{ $task->problem_description }}</p>
            </div>
        </div>
    </div>

    <!-- Progress Updates -->
    @if($task->progress->isNotEmpty())
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Progress Updates</h2>
        <div class="space-y-3">
            @foreach($task->progress as $progress)
            <div class="border-l-4 border-blue-500 pl-4 py-2">
                <p class="font-semibold text-gray-800">{{ $progress->stage }}</p>
                @if($progress->notes)
                <p class="text-sm text-gray-600 mt-1">{{ $progress->notes }}</p>
                @endif
                <p class="text-xs text-gray-500 mt-1">{{ $progress->created_at->format('M d, Y H:i') }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Complexity Review Form -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Update Complexity Weight</h2>

        <form action="{{ route('manager.tasks.update-complexity', $task->id) }}" method="POST">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Complexity Weight *</label>
                <div class="grid grid-cols-5 gap-3">
                    @for($i = 1; $i <= 5; $i++)
                    <label class="relative flex flex-col items-center p-4 border-2 rounded-lg cursor-pointer transition
                        {{ $task->complexity_weight == $i ? 'border-purple-500 bg-purple-50' : 'border-gray-300 hover:border-purple-300' }}">
                        <input type="radio" name="complexity_weight" value="{{ $i }}"
                               {{ $task->complexity_weight == $i ? 'checked' : '' }}
                               class="sr-only" required>
                        <span class="text-3xl font-bold text-gray-800">{{ $i }}x</span>
                        <span class="text-xs text-gray-600 mt-1">
                            @if($i == 1) Simple
                            @elseif($i == 2) Standard
                            @elseif($i == 3) Complex
                            @elseif($i == 4) Very Complex
                            @else Extremely Complex
                            @endif
                        </span>
                    </label>
                    @endfor
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    This weight affects technician workload calculations. Higher weight = more impact on workload.
                </p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Review Notes (Optional)</label>
                <textarea name="notes" rows="4"
                          placeholder="Add notes about why this complexity rating was assigned..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
            </div>

            <div class="flex gap-4">
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold">
                    Update Complexity
                </button>
                <a href="{{ route('manager.tasks') }}"
                   class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Complexity Guidelines -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="font-bold text-gray-800 mb-3">📋 Complexity Guidelines</h3>
        <div class="space-y-2 text-sm text-gray-700">
            <p><strong>1x - Simple:</strong> Basic service, common repairs, <1 hour work</p>
            <p><strong>2x - Standard:</strong> Typical repairs, standard parts replacement, 1-3 hours</p>
            <p><strong>3x - Complex:</strong> Multiple issues, uncommon repairs, 3-6 hours</p>
            <p><strong>4x - Very Complex:</strong> Major repairs, multiple components, 6-12 hours</p>
            <p><strong>5x - Extremely Complex:</strong> Extensive damage, rare issues, 12+ hours</p>
        </div>
    </div>
</div>
@endsection
