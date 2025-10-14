<!-- ============================================================ -->
<!-- VIEW 1: Track Progress Search Page -->
<!-- File: resources/views/tracking/index.blade.php -->
<!-- ============================================================ -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="mb-8 text-center">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Track Your Device</h1>
        <p class="text-gray-600">Enter your Task ID to see real-time progress updates</p>
    </div>

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg p-8">
        <form action="{{ route('tracking.search') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Task ID</label>
                <input type="text" name="task_id" required
                       placeholder="e.g., JOHPHN2410140A5B"
                       class="w-full px-4 py-4 text-lg border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-sm text-gray-500 mt-2">Your Task ID was provided when you booked your service</p>
            </div>

            <button type="submit"
                    class="w-full px-6 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold text-lg">
                Track My Device
            </button>
        </form>
    </div>

    <div class="mt-8 text-center">
        <p class="text-sm text-gray-600">
            Don't have a Task ID?
            <a href="{{ route('bookings.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                Book a service →
            </a>
        </p>
    </div>
</div>
@endsection
