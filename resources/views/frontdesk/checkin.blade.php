<!-- ============================================================ -->
<!-- VIEW 2: Check-in Form for Online Bookings -->
<!-- File: resources/views/frontdesk/checkin.blade.php -->
<!-- ============================================================ -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-8">
        <a href="{{ route('frontdesk.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Back to Dashboard
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Check-in Online Booking</h1>
        <p class="text-gray-600">Enter the Task ID to check in a device</p>
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

    <!-- Search Form -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Search Booking</h2>

        <div class="flex gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Task ID</label>
                <input type="text" id="task_id_search"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="e.g., JOHPHN2410140A5B">
            </div>
            <div class="flex items-end">
                <button onclick="searchBooking()"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Search
                </button>
            </div>
        </div>
    </div>

    <!-- Booking Details (Hidden until search) -->
    <div id="booking-details" class="hidden bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Booking Details</h2>

        <div id="booking-info" class="space-y-4">
            <!-- Will be populated by JavaScript -->
        </div>

        <form action="{{ route('frontdesk.checkin.process') }}" method="POST" class="mt-6">
            @csrf
            <input type="hidden" name="task_id" id="task_id_confirm">
            <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                Confirm Check-in
            </button>
        </form>
    </div>

    <!-- Error Message -->
    <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <span id="error-text"></span>
    </div>
</div>

<script>
function searchBooking() {
    const taskId = document.getElementById('task_id_search').value;
    const bookingDetails = document.getElementById('booking-details');
    const errorMessage = document.getElementById('error-message');

    if (!taskId) {
        alert('Please enter a Task ID');
        return;
    }

    // Hide previous results
    bookingDetails.classList.add('hidden');
    errorMessage.classList.add('hidden');

    fetch('{{ route("frontdesk.checkin.search") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ task_id: taskId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayBookingDetails(data.task);
            document.getElementById('task_id_confirm').value = taskId;
            bookingDetails.classList.remove('hidden');
        } else {
            document.getElementById('error-text').textContent = data.message;
            errorMessage.classList.remove('hidden');
        }
    })
    .catch(error => {
        document.getElementById('error-text').textContent = 'An error occurred. Please try again.';
        errorMessage.classList.remove('hidden');
    });
}

function displayBookingDetails(task) {
    const bookingInfo = document.getElementById('booking-info');
    bookingInfo.innerHTML = `
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Task ID</p>
                <p class="font-semibold text-lg">${task.task_id}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Customer Name</p>
                <p class="font-semibold">${task.user.name}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Phone</p>
                <p class="font-semibold">${task.user.phone}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Device</p>
                <p class="font-semibold">${task.device_brand} ${task.device_model}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Category</p>
                <p class="font-semibold">${task.device_category.name}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Assigned Technician</p>
                <p class="font-semibold">${task.technician ? task.technician.name : 'Not assigned'}</p>
            </div>
            <div class="col-span-2">
                <p class="text-sm text-gray-500">Problem Description</p>
                <p class="font-semibold">${task.problem_description || 'N/A'}</p>
            </div>
        </div>
    `;
}
</script>
@endsection
