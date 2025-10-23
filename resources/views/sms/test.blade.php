@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">SMS Testing Panel</h1>

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
        @endif

        <!-- Send Test SMS Form -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Send Test SMS</h2>

            <form method="POST" action="{{ route('sms.send-test') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Phone Number</label>
                    <input type="text"
                           name="phone_number"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="+263774888464"
                           value="{{ old('phone_number', auth()->user()->phone) }}"
                           required>
                    <p class="text-sm text-gray-600 mt-1">Format: +263774888464</p>
                    @error('phone_number')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Message</label>
                    <textarea name="message"
                              rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Enter your test message here..."
                              required>{{ old('message', 'This is a test message from RepairHub SMS system.') }}</textarea>
                    <p class="text-sm text-gray-600 mt-1">Max 160 characters</p>
                    @error('message')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                    Send Test SMS
                </button>
            </form>
        </div>

        <!-- Test Collection Reminders -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Test Collection Reminders</h2>

            <div class="space-y-4">
                <p class="text-gray-600">Enter a Task ID to send test reminders:</p>

                <form method="GET" action="" class="flex gap-2" id="reminderForm">
                    <input type="number"
                           id="taskId"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Task ID"
                           required>

                    <button type="button"
                            onclick="testReminder('day3')"
                            class="bg-yellow-500 text-white px-6 py-2 rounded-lg hover:bg-yellow-600 transition font-semibold">
                        Test Day 3
                    </button>

                    <button type="button"
                            onclick="testReminder('day4')"
                            class="bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600 transition font-semibold">
                        Test Day 4
                    </button>
                </form>
            </div>
        </div>

        <!-- API Balance Check -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">API Balance</h2>

            <button onclick="checkBalance()"
                    class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition font-semibold">
                Check Balance
            </button>

            <div id="balanceResult" class="mt-4 hidden">
                <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto"></pre>
            </div>
        </div>

        <!-- View SMS Logs -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">SMS Logs</h2>
            <a href="{{ route('sms.logs') }}"
               class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition font-semibold inline-block">
                View All SMS Logs
            </a>
        </div>
    </div>
</div>

<script>
function testReminder(type) {
    const taskId = document.getElementById('taskId').value;
    if (!taskId) {
        alert('Please enter a Task ID');
        return;
    }

    const url = type === 'day3'
        ? `/sms/test-day3-reminder/${taskId}`
        : `/sms/test-day4-reminder/${taskId}`;

    window.location.href = url;
}

function checkBalance() {
    fetch('/sms/balance')
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('balanceResult');
            resultDiv.classList.remove('hidden');
            resultDiv.querySelector('pre').textContent = JSON.stringify(data, null, 2);
        })
        .catch(error => {
            alert('Error checking balance: ' + error.message);
        });
}
</script>
@endsection
