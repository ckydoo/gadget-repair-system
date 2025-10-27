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
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
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
                    onkeypress="if(event.key === 'Enter') searchDevice()"
                >
                <button
                    onclick="searchDevice()"
                    class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                    Search
                </button>
            </div>
        </div>

        <!-- Error Message (Hidden Initially) -->
        <div id="error-message" class="hidden mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <span id="error-text"></span>
        </div>

        <!-- Device Details (Hidden Initially) -->
        <div id="device-details" class="hidden bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Device Information</h2>

            <!-- Device Info Display -->
            <div id="device-info" class="mb-6"></div>

            <!-- Payment Section (Shown only if payment required) -->
            <div id="payment-section" class="hidden border-t pt-6 mb-6">
                <h3 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Payment Required
                </h3>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <p class="text-yellow-800 font-semibold mb-2">⚠️ This invoice has not been paid yet.</p>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Invoice Amount:</span>
                            <span id="invoice-amount" class="ml-2 font-bold text-gray-900"></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Storage Fee:</span>
                            <span id="storage-fee-amount" class="ml-2 font-bold text-gray-900"></span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-600">Total Amount Due:</span>
                            <span id="total-amount-due" class="ml-2 font-bold text-lg text-red-600"></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                        <select id="payment_method" name="payment_method" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select payment method</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount Received *</label>
                        <input type="number" id="amount_received" name="amount_received" step="0.01" min="0" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter amount received">
                    </div>
                </div>

                <div id="change-display" class="hidden mt-3 p-3 bg-green-50 border border-green-200 rounded">
                    <span class="text-green-800 font-semibold">Change to Return: $</span>
                    <span id="change-amount" class="text-green-800 font-bold text-lg">0.00</span>
                </div>
            </div>

            <!-- Collection Form -->
            <form id="collection-form" method="POST" class="border-t pt-6">
                @csrf
                <h3 class="text-lg font-bold text-gray-800 mb-4">Collection Details</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Collected By (Name) *</label>
                        <input type="text" name="collected_by" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Full name of person collecting">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Type *</label>
                        <select name="id_type" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select ID type</option>
                            <option value="national_id">National ID</option>
                            <option value="passport">Passport</option>
                            <option value="drivers_license">Driver's License</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Number *</label>
                        <input type="text" name="id_number" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter ID number">
                    </div>
                </div>

                <!-- Storage Fee Checkbox (if applicable) -->
                <div id="storage-fee-checkbox" class="hidden mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="storage_fee_paid" value="1" class="rounded border-gray-300 text-blue-600">
                        <span class="ml-2 text-sm text-gray-700">Storage fee has been paid</span>
                    </label>
                </div>

                <!-- Hidden fields for payment -->
                <input type="hidden" id="hidden_payment_method" name="payment_method">
                <input type="hidden" id="hidden_amount_received" name="amount_received">

                <div class="flex gap-4">
                    <button type="button" onclick="cancelCollection()"
                        class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                        Complete Collection
                    </button>
                </div>
            </form>
        </div>

        <!-- Ready Devices Table -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Devices Ready for Collection</h2>

            @if($readyDevices->isEmpty())
            <p class="text-gray-500 text-center py-8">No devices ready for collection</p>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Waiting</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($readyDevices as $task)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap font-mono text-sm">{{ $task->task_id }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $task->user->name }}</td>
                            <td class="px-4 py-3">{{ $task->device_brand }} {{ $task->device_model }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $task->status === 'ready_for_collection' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $task->status === 'ready_for_collection' ? 'Ready' : 'Completed' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $task->getDaysUncollected() > 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $task->getDaysUncollected() }} days
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($task->invoice)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $task->invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $task->invoice->status === 'paid' ? 'Paid' : 'UNPAID - $' . number_format($task->invoice->total, 2) }}
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

            <div class="mt-4">
                {{ $readyDevices->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
let currentTaskData = null;

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
            currentTaskData = data;
            displayDeviceDetails(data);

            // Update form action
            const form = document.getElementById('collection-form');
            form.action = `{{ url('frontdesk/collection') }}/${data.task.id}`;

            deviceDetails.classList.remove('hidden');

            // Scroll to device details
            deviceDetails.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            document.getElementById('error-text').textContent = data.message;
            errorMessage.classList.remove('hidden');
        }
    })
    .catch(error => {
        document.getElementById('error-text').textContent = 'An error occurred. Please try again.';
        errorMessage.classList.remove('hidden');
        console.error('Error:', error);
    });
}

function displayDeviceDetails(data) {
    const task = data.task;
    const requiresPayment = data.requires_payment;
    const storageFee = data.storage_fee || 0;
    const invoiceAmount = data.invoice_amount || 0;
    const totalDue = data.total_amount_due || 0;

    // Display device information
    const deviceInfoHtml = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
            <div>
                <span class="text-gray-600 text-sm">Task ID:</span>
                <span class="ml-2 font-mono font-bold">${task.task_id}</span>
            </div>
            <div>
                <span class="text-gray-600 text-sm">Customer:</span>
                <span class="ml-2 font-semibold">${task.user.name}</span>
            </div>
            <div>
                <span class="text-gray-600 text-sm">Device:</span>
                <span class="ml-2">${task.device_brand} ${task.device_model}</span>
            </div>
            <div>
                <span class="text-gray-600 text-sm">Category:</span>
                <span class="ml-2">${task.device_category.name}</span>
            </div>
            <div>
                <span class="text-gray-600 text-sm">Problem:</span>
                <span class="ml-2">${task.problem_description || 'N/A'}</span>
            </div>
            <div>
                <span class="text-gray-600 text-sm">Technician:</span>
                <span class="ml-2">${task.technician ? task.technician.name : 'N/A'}</span>
            </div>
        </div>
    `;
    document.getElementById('device-info').innerHTML = deviceInfoHtml;

    // Show/hide payment section
    const paymentSection = document.getElementById('payment-section');
    if (requiresPayment) {
        paymentSection.classList.remove('hidden');
        document.getElementById('invoice-amount').textContent = '$' + invoiceAmount.toFixed(2);
        document.getElementById('storage-fee-amount').textContent = '$' + storageFee.toFixed(2);
        document.getElementById('total-amount-due').textContent = '$' + totalDue.toFixed(2);

        // Auto-fill amount received with total due
        document.getElementById('amount_received').value = totalDue.toFixed(2);
    } else {
        paymentSection.classList.add('hidden');
    }

    // Show storage fee checkbox if there's a storage fee
    const storageFeeCheckbox = document.getElementById('storage-fee-checkbox');
    if (storageFee > 0) {
        storageFeeCheckbox.classList.remove('hidden');
    } else {
        storageFeeCheckbox.classList.add('hidden');
    }
}

// Calculate change when amount received changes
document.addEventListener('DOMContentLoaded', function() {
    const amountReceived = document.getElementById('amount_received');
    const changeDisplay = document.getElementById('change-display');
    const changeAmount = document.getElementById('change-amount');

    if (amountReceived) {
        amountReceived.addEventListener('input', function() {
            if (currentTaskData && currentTaskData.requires_payment) {
                const received = parseFloat(this.value) || 0;
                const totalDue = currentTaskData.total_amount_due || 0;
                const change = received - totalDue;

                if (change > 0) {
                    changeAmount.textContent = change.toFixed(2);
                    changeDisplay.classList.remove('hidden');
                } else {
                    changeDisplay.classList.add('hidden');
                }
            }
        });
    }
});

// Copy payment fields to hidden fields before form submission
document.getElementById('collection-form').addEventListener('submit', function(e) {
    if (currentTaskData && currentTaskData.requires_payment) {
        const paymentMethod = document.getElementById('payment_method').value;
        const amountReceived = document.getElementById('amount_received').value;

        if (!paymentMethod || !amountReceived) {
            e.preventDefault();
            alert('Please provide payment method and amount received');
            return;
        }

        const totalDue = currentTaskData.total_amount_due || 0;
        if (parseFloat(amountReceived) < totalDue) {
            e.preventDefault();
            alert(`Insufficient payment. Amount due: $${totalDue.toFixed(2)}`);
            return;
        }

        // Copy to hidden fields
        document.getElementById('hidden_payment_method').value = paymentMethod;
        document.getElementById('hidden_amount_received').value = amountReceived;
    }
});

function cancelCollection() {
    if (confirm('Are you sure you want to cancel this collection?')) {
        document.getElementById('device-details').classList.add('hidden');
        document.getElementById('task_id_search').value = '';
        currentTaskData = null;
    }
}
</script>
@endsection
