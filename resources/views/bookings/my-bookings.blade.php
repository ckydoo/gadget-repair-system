<!-- ============================================================ -->
<!-- VIEW 6: My Bookings -->
<!-- File: resources/views/bookings/my-bookings.blade.php -->
<!-- ============================================================ -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">My Bookings</h1>
        <p class="text-gray-600">Track all your service and repair bookings</p>
    </div>

    @if($bookings->isEmpty())
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <div class="bg-gray-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">No Bookings Yet</h2>
        <p class="text-gray-600 mb-6">You haven't made any bookings yet</p>
        <a href="{{ route('bookings.index') }}"
           class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
            Make Your First Booking
        </a>
    </div>
    @else
    <div class="space-y-6">
        @foreach($bookings as $booking)
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-1">
                            {{ ucfirst($booking->type) }} - {{ $booking->deviceCategory->name }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            Booked on {{ $booking->created_at->format('M d, Y') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            @if($booking->payment_status === 'paid') bg-green-100 text-green-800
                            @elseif($booking->payment_status === 'pending') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($booking->payment_status) }}
                        </span>
                    </div>
                </div>

                @if($booking->type === 'repair')
                <div class="mb-4">
                    <p class="text-gray-700">
                        <strong>Device:</strong> {{ $booking->device_brand }} {{ $booking->device_model }}
                    </p>
                    <p class="text-gray-700 text-sm mt-1">
                        <strong>Problem:</strong> {{ Str::limit($booking->problem_description, 100) }}
                    </p>
                </div>
                @else
                <div class="mb-4">
                    <p class="text-gray-700">
                        <strong>Devices:</strong> {{ $booking->device_count }} device(s)
                    </p>
                </div>
                @endif

                @if($booking->task)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-600">Task ID</p>
                            <p class="text-xl font-mono font-bold text-blue-600">{{ $booking->task->task_id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="font-semibold text-gray-800">{{ ucfirst(str_replace('_', ' ', $booking->task->status)) }}</p>
                        </div>
                        @if($booking->task->technician)
                        <div>
                            <p class="text-sm text-gray-600">Technician</p>
                            <p class="font-semibold text-gray-800">{{ $booking->task->technician->name }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <div class="flex justify-between items-center pt-4 border-t">
                    <div>
                        <p class="text-2xl font-bold text-gray-800">${{ number_format($booking->total_fee, 2) }}</p>
                    </div>
                    <div class="flex gap-3">
                        @if($booking->payment_status === 'pending')
                        <a href="{{ route('bookings.payment', $booking->id) }}"
                           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold text-sm">
                            Complete Payment
                        </a>
                        @endif
                        @if($booking->task)
                        <a href="#"
                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold text-sm">
                            Track Progress
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $bookings->links() }}
    </div>
    @endif
</div>
@endsection
