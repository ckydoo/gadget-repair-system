@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Analytics Dashboard</h1>
        <p class="text-gray-600">Detailed insights and performance metrics</p>
    </div>

    <!-- Key Performance Indicators -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Completion Rate</h3>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                    <div>
                        <span class="text-4xl font-bold text-gray-800">{{ number_format($completionRate, 1) }}%</span>
                    </div>
                </div>
                <div class="overflow-hidden h-4 mb-4 text-xs flex rounded bg-gray-200">
                    <div style="width:{{ $completionRate }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"></div>
                </div>
            </div>
            <p class="text-sm text-gray-600">Tasks completed successfully</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Avg Completion Time</h3>
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-4xl font-bold text-gray-800">{{ number_format($avgCompletionTime ?? 0, 1) }}</p>
            <p class="text-sm text-gray-600 mt-2">Hours per task</p>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Most Popular</h3>
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            @if($categoryStats->isNotEmpty())
            <p class="text-2xl font-bold text-gray-800">{{ $categoryStats->first()->deviceCategory->name ?? 'N/A' }}</p>
            <p class="text-sm text-gray-600 mt-2">{{ $categoryStats->first()->count ?? 0 }} tasks</p>
            @else
            <p class="text-2xl font-bold text-gray-800">No data</p>
            @endif
        </div>
    </div>

    <!-- Category Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Tasks by Device Category</h2>
            <div class="space-y-4">
                @forelse($categoryStats as $stat)
                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">{{ $stat->deviceCategory->name ?? 'Unknown' }}</span>
                        <span class="text-sm text-gray-600">{{ $stat->count }} tasks</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ ($stat->count / $categoryStats->sum('count')) * 100 }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No category data available</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Monthly Trend (Last 6 Months)</h2>
            <div class="space-y-4">
                @forelse($monthlyTrend as $month)
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($month->month . '-01')->format('M Y') }}</span>
                    <span class="text-2xl font-bold text-blue-600">{{ $month->count }}</span>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No monthly data available</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Detailed Statistics -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Detailed Statistics</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center p-6 bg-blue-50 rounded-lg">
                <p class="text-4xl font-bold text-blue-600">{{ \App\Models\Task::count() }}</p>
                <p class="text-sm text-gray-600 mt-2">Total Tasks</p>
            </div>

            <div class="text-center p-6 bg-green-50 rounded-lg">
                <p class="text-4xl font-bold text-green-600">{{ \App\Models\Task::whereIn('status', ['completed', 'ready_for_collection', 'collected'])->count() }}</p>
                <p class="text-sm text-gray-600 mt-2">Completed Tasks</p>
            </div>

            <div class="text-center p-6 bg-orange-50 rounded-lg">
                <p class="text-4xl font-bold text-orange-600">{{ \App\Models\Task::active()->count() }}</p>
                <p class="text-sm text-gray-600 mt-2">Active Tasks</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <div class="text-center p-6 bg-purple-50 rounded-lg">
                <p class="text-4xl font-bold text-purple-600">{{ \App\Models\User::role('client')->count() }}</p>
                <p class="text-sm text-gray-600 mt-2">Total Customers</p>
            </div>

            <div class="text-center p-6 bg-indigo-50 rounded-lg">
                <p class="text-4xl font-bold text-indigo-600">{{ \App\Models\User::role('technician')->count() }}</p>
                <p class="text-sm text-gray-600 mt-2">Active Technicians</p>
            </div>

            <div class="text-center p-6 bg-pink-50 rounded-lg">
                <p class="text-4xl font-bold text-pink-600">${{ number_format(\App\Models\Invoice::where('status', 'paid')->sum('total'), 0) }}</p>
                <p class="text-sm text-gray-600 mt-2">Total Revenue</p>
            </div>
        </div>
    </div>

    <!-- Performance Insights -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <h3 class="text-lg font-bold mb-4">📈 Performance Insights</h3>
            <ul class="space-y-2 text-sm">
                <li>✓ Completion rate is {{ $completionRate >= 80 ? 'excellent' : ($completionRate >= 60 ? 'good' : 'needs improvement') }}</li>
                <li>✓ Average completion time: {{ number_format($avgCompletionTime ?? 0, 1) }} hours</li>
                <li>✓ Most popular service: {{ $categoryStats->first()->deviceCategory->name ?? 'N/A' }}</li>
                <li>✓ Active tasks currently being worked on</li>
            </ul>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <h3 class="text-lg font-bold mb-4">💡 Recommendations</h3>
            <ul class="space-y-2 text-sm">
                @if($completionRate < 70)
                <li>⚠️ Consider reviewing task assignment process</li>
                @endif
                @if(($avgCompletionTime ?? 0) > 24)
                <li>⚠️ Average completion time is high - review workflows</li>
                @endif
                <li>✓ Monitor technician workload distribution</li>
                <li>✓ Keep tracking customer satisfaction</li>
                <li>✓ Review pricing strategy regularly</li>
            </ul>
        </div>
    </div>
</div>
@endsection
