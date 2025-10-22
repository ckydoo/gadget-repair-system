<!-- ============================================================ -->
<!-- VIEW 3: Technician Performance -->
<!-- File: resources/views/manager/technicians.blade.php -->
<!-- ============================================================ -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Technician Performance</h1>
        <p class="text-gray-600">Monitor technician workload and performance metrics</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($technicians as $tech)
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="bg-blue-600 text-white w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg mr-3">
                        {{ substr($tech['name'], 0, 1) }}
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">{{ $tech['name'] }}</h3>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $tech['is_available'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $tech['is_available'] ? 'Available' : 'Unavailable' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Workload Bar -->
            <div class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Workload</span>
                    <span>{{ $tech['active_tasks'] }}/{{ $tech['max_workload'] }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="h-3 rounded-full transition-all
                        @if($tech['workload_weight'] >= $tech['max_workload'] * 0.8) bg-red-500
                        @elseif($tech['workload_weight'] >= $tech['max_workload'] * 0.6) bg-yellow-500
                        @else bg-green-500
                        @endif"
                        style="width: {{ min(($tech['workload_weight'] / $tech['max_workload']) * 100, 100) }}%">
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 text-center">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-2xl font-bold text-gray-800">{{ $tech['total_completed'] }}</p>
                    <p class="text-xs text-gray-500">Total Completed</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-2xl font-bold text-blue-600">{{ $tech['completed_this_month'] }}</p>
                    <p class="text-xs text-gray-500">This Month</p>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Avg Completion Time:</span>
                    <span class="font-semibold text-gray-800">{{ $tech['avg_completion_hours'] }}h</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
