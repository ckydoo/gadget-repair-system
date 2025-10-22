<!-- ============================================================ -->
<!-- VIEW 1: Manager Dashboard Overview -->
<!-- File: resources/views/manager/index.blade.php -->
<!-- ============================================================ -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Manager Dashboard</h1>
        <p class="text-gray-600">Overview of workshop operations and analytics</p>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Active Tasks -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Active Tasks</h3>
                <svg class="w-8 h-8 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="text-4xl font-bold">{{ $stats['active_tasks'] }}</p>
            <p class="text-sm opacity-75 mt-2">{{ $stats['completed_today'] }} completed today</p>
        </div>

        <!-- Revenue Today -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Today's Revenue</h3>
                <svg class="w-8 h-8 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-4xl font-bold">${{ number_format($stats['revenue_today'], 0) }}</p>
            <p class="text-sm opacity-75 mt-2">${{ number_format($stats['revenue_month'], 0) }} this month</p>
        </div>

        <!-- Pending Collection -->
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">Pending Collection</h3>
                <svg class="w-8 h-8 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-4xl font-bold">{{ $stats['pending_collection'] }}</p>
            <p class="text-sm opacity-75 mt-2">Devices ready for pickup</p>
        </div>

        <!-- New Customers -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold opacity-90">New Customers</h3>
                <svg class="w-8 h-8 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="text-4xl font-bold">{{ $stats['new_customers_today'] }}</p>
            <p class="text-sm opacity-75 mt-2">{{ $stats['total_customers'] }} total</p>
        </div>
    </div>

    <!-- Revenue Chart & Quick Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Revenue Chart -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Revenue Trend (Last 7 Days)</h2>
            <div class="h-64">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Stats</h2>
            <div class="space-y-4">
                <div class="flex justify-between items-center pb-3 border-b">
                    <span class="text-gray-600">Total Tasks</span>
                    <span class="font-bold text-gray-800">{{ $stats['total_tasks'] }}</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b">
                    <span class="text-gray-600">Revenue This Week</span>
                    <span class="font-bold text-green-600">${{ number_format($stats['revenue_week'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b">
                    <span class="text-gray-600">Pending Payments</span>
                    <span class="font-bold text-orange-600">${{ number_format($stats['pending_payments'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b">
                    <span class="text-gray-600">Today's Bookings</span>
                    <span class="font-bold text-blue-600">{{ $stats['todays_bookings'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Pending Bookings</span>
                    <span class="font-bold text-red-600">{{ $stats['pending_bookings'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Technician Workload -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Technician Workload</h2>
            <div class="space-y-4">
                @foreach($technicians as $tech)
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $tech['name'] }}</span>
                        <span class="text-sm text-gray-600">{{ $tech['active_tasks'] }}/{{ $tech['max_workload'] }} tasks</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="h-3 rounded-full transition-all
                            @if($tech['percentage'] >= 80) bg-red-500
                            @elseif($tech['percentage'] >= 60) bg-yellow-500
                            @else bg-green-500
                            @endif"
                            style="width: {{ min($tech['percentage'], 100) }}%">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4">
                <a href="{{ route('manager.technicians') }}" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                    View Full Report →
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Tasks</h2>
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @foreach($recentTasks as $task)
                <div class="flex items-start p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800 text-sm">{{ $task->task_id }}</p>
                        <p class="text-xs text-gray-600">{{ $task->user->name }} - {{ $task->deviceCategory->name }}</p>
                        <p class="text-xs text-gray-500 mt-1">Assigned to: {{ $task->technician->name ?? 'Unassigned' }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                        @if($task->status === 'completed') bg-green-100 text-green-800
                        @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <a href="{{ route('manager.tasks') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition text-center">
            <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="font-semibold text-gray-800">Manage Tasks</p>
        </a>

        <a href="{{ route('manager.technicians') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition text-center">
            <div class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <p class="font-semibold text-gray-800">Technicians</p>
        </a>

        <a href="{{ route('manager.revenue') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition text-center">
            <div class="bg-yellow-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="font-semibold text-gray-800">Revenue</p>
        </a>

        <a href="{{ route('manager.analytics') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition text-center">
            <div class="bg-purple-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <p class="font-semibold text-gray-800">Analytics</p>
        </a>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueData = @json($revenueChartData);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: revenueData.map(d => d.date),
        datasets: [{
            label: 'Revenue ($)',
            data: revenueData.map(d => d.revenue),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value;
                    }
                }
            }
        }
    }
});
</script>
@endsection
