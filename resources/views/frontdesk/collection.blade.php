@extends('layouts.app')

@section('title', 'Device Collection - Front Desk')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Device Collection</h1>
            <p class="text-gray-600 mt-2">Process device checkout for collected items</p>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
        @endif

        <!-- Search Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Search Device</h2>
            <div class="flex gap-4">
                <input
                    type="text"
                    id="task_id_search"
                    placeholder="Enter Task ID (e.g., JOHSMA241025AB12)"
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                <button
                    onclick="searchDevice()"
                    class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                    Search
                </button>
            </div>
        </div>

        <!-- Device Details (Hidden Initially) -->
        <div id="device-details" class="hidden bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Device Information</h2>
            <div id="device-info" class="mb-6"></div>

            <!-- Collection Form -->
            <form id="collection-form" method="POST" class="border-t pt-6">
                @csrf
                <h3 class="text-lg font-bold text-gray-800 mb-4">Collection Details</h3>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Collected By (Name) *</label>
                        <input
                            type="text"
                            name="collected_by"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Full name of person collecting"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Type *</label>
                        <select
                            name="id_type"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select ID Type</option>
                            <option value="national_id">National ID</option>
                            <option value="passport">Passport</option>
                            <option value="drivers_license">Driver's License</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Number *</label>
                        <input
                            type="text"
                            name="id_number"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter ID number"
                        >
                    </div>
                </div>

                <!-- Storage Fee Section -->
                <div id="storage-fee-section" class="hidden bg-yellow-50 border border-yellow-300 rounded-lg p-4 mb-6">
                    <h4 class="font-bold text-gray-800 mb-2">⚠️ Storage Fee Applicable</h4>
                    <p class="text-sm text-gray-600 mb-2">Device has been stored for <span id="days-stored" class="font-bold"></span> days.</p>
                    <p class="text-lg font-bold text-gray-800 mb-3">Storage Fee: $<span id="storage-fee-amount">0.00</span></p>

                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            name="storage_fee_paid"
                            id="storage_fee_paid"
                            class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                        >
                        <span class="ml-2 text-sm font-medium text-gray-700">Storage fee has been paid</span>
                    </label>
                </div>

                <button
                    type="submit"
                    class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold text-lg">
                    Complete Collection & Checkout
                </button>
            </form>
        </div>

        <!-- Error Message (Hidden Initially) -->
        <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-8">
            <span id="error-text"></span>
        </div>

        <!-- Ready for Collection List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Devices Ready for Collection</h2>

            @if($readyTasks->isEmpty())
            <p class="text-gray-500 text-center py-8">No devices ready for collection at the moment.</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Task ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ready Since</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($readyTasks as $task)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="font-mono text-sm font-semibold">{{ $task->task_id }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium">{{ $task->user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $task->user->phone }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium">{{ $task->device_brand }} {{ $task->device_model }}</p>
                                    <p class="text-sm text-gray-500">{{ $task->deviceCategory->name }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $task->ready_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $task->getDaysUncollected() >= 5 ? 'bg-red-100 text-red-800' :
                                       ($task->getDaysUncollected() >= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                    {{ $task->getDaysUncollected() }} days
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($task->invoice)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $task->invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($task->invoice->status) }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">No invoice</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    onclick="loadDevice('{{ $task->task_id }}')"
                                    class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                                    Process
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function searchDevice() {
    const taskId = document.getElementById('task_id_search').value.trim();

    if (!taskId) {
        alert('Please enter a Task ID');
        return;
    }

    loadDevice(taskId);
}

function loadDevice(taskId) {
    const deviceDetails = document.getElementById('device-details');
    const errorMessage = document.getElementById('error-message');

    // Hide previous results
    deviceDetails.classList.add('hidden');
    errorMessage.classList.add('hidden');

    fetch(`{{ route('frontdesk.collection.search') }}`, {
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
            displayDeviceDetails(data.task, data.storage_fee, data.days_stored);

            // Update form action
            const form = document.getElementById('collection-form');
            form.action = `{{ url('frontdesk/collection') }}/${data.task.id}`;

            deviceDetails.classList.remove('hidden');

            // Scroll to device details
            deviceDetails.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            document.getElementById('error-text').textContent = data.message;
            errorMessage.classList.remove('hidden');

            // If requires payment, show invoice details
            if (data.requires_payment && data.task) {
                // You can add additional UI here to show payment options
            }
        }
    })
    .catch(error => {
        document.getElementById('error-text').textContent = 'An error occurred. Please try again.';
        errorMessage.classList.remove('hidden');
    });
}

function displayDeviceDetails(task, storageFee, daysStored) {
    const deviceInfo = document.getElementById('device-info');
    const storageFeeSection = document.getElementById('storage-fee-section');

    // Display device information
    deviceInfo.innerHTML = `
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Task ID</p>
                <p class="font-semibold text-lg font-mono">${task.task_id}</p>
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
                <p class="text-sm text-gray-500">Ready Since</p>
                <p class="font-semibold">${new Date(task.ready_at).toLocaleDateString()}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Invoice Status</p>
                <p class="font-semibold">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${task.invoice.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                        ${task.invoice.status.toUpperCase()}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Invoice Total</p>
                <p class="font-semibold">$${parseFloat(task.invoice.total).toFixed(2)}</p>
            </div>
        </div>
    `;

    // Show storage fee section if applicable
    if (storageFee > 0) {
        document.getElementById('days-stored').textContent = daysStored;
        document.getElementById('storage-fee-amount').textContent = storageFee.toFixed(2);
        storageFeeSection.classList.remove('hidden');
    } else {
        storageFeeSection.classList.add('hidden');
    }
}

// Enter key support for search
document.getElementById('task_id_search').addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        searchDevice();
    }
});
</script>
@endsection
