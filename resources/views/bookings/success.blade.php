
<!-- ============================================================ -->
<!-- VIEW 5: Booking Success Page -->
<!-- File: resources/views/bookings/success.blade.php -->
<!-- ============================================================ -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="bg-white rounded-lg shadow-lg p-8 text-center">
        <!-- Success Icon -->
        <div class="bg-green-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-gray-800 mb-2">Booking Confirmed!</h1>
        <p class="text-gray-600 mb-8">Your payment has been processed successfully</p>

        @if($booking->task)
        <!-- Task ID Display -->
        <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-6 mb-8">
            <p class="text-sm text-gray-600 mb-2">Your Task ID</p>
            <p class="text-4xl font-mono font-bold text-blue-600 mb-4">{{ $booking->task->task_id }}</p>
            <p class="text-sm text-gray-600">Please save this ID for tracking your device</p>
        </div>

        <!-- Assigned Technician -->
        @if($booking->task->technician)
        <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Assigned Technician</h2>
            <div class="flex items-center">
                <div class="bg-blue-600 text-white w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg mr-4">
                    {{ substr($booking->task->technician->name, 0, 1) }}
                </div>
                <div>
                    <p class="font-semibold text-gray-800">{{ $booking->task->technician->name }}</p>
                    <p class="text-sm text-gray-600">{{ $booking->task->technician->phone }}</p>
                </div>
            </div>
        </div>
        @endif
        @endif

        <!-- Next Steps -->
        <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Next Steps</h2>
            <ol class="space-y-3">
                <li class="flex items-start">
                    <span class="bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold mr-3 flex-shrink-0">1</span>
                    <span class="text-gray-700">Bring your device to our workshop along with your Task ID</span>
                </li>
                <li class="flex items-start">
                    <span class="bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold mr-3 flex-shrink-0">2</span>
                    <span class="text-gray-700">Our front desk will check in your device and give you a receipt</span>
                </li>
                <li class="flex items-start">
                    <span class="bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold mr-3 flex-shrink-0">3</span>
                    <span class="text-gray-700">Your assigned technician will start working on it</span>
                </li>
                <li class="flex items-start">
                    <span class="bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold mr-3 flex-shrink-0">4</span>
                    <span class="text-gray-700">You'll receive real-time updates on your device's progress</span>
                </li>
            </ol>
        </div>

        <!-- Workshop Information -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8 text-left">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Workshop Location</h2>
            <p class="text-gray-700 mb-2">📍 123 Main Street, Harare, Zimbabwe</p>
            <p class="text-gray-700 mb-2">📞 +263 77 123 4567</p>
            <p class="text-gray-700">🕐 Mon-Fri: 8:00 AM - 5:00 PM | Sat: 9:00 AM - 2:00 PM</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4">
            <a href="{{ route('bookings.my-bookings') }}"
               class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                View My Bookings
            </a>
            <a href="{{ route('bookings.index') }}"
               class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-semibold">
                Book Another
            </a>
        </div>

        <!-- Download Options -->
        <div class="mt-6">
            <button onclick="window.print()"
                    class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                🖨️ Print Confirmation
            </button>
        </div>
    </div>
</div>
@endsection
