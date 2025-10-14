
<!-- ============================================================ -->
<!-- VIEW 2: Service Booking Form -->
<!-- File: resources/views/bookings/service.blade.php -->
<!-- ============================================================ -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-8">
        <a href="{{ route('bookings.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Back to Booking Options
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Book a Service</h1>
        <p class="text-gray-600">Regular maintenance for your devices</p>
    </div>

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('bookings.service.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf

        <!-- Device Category -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Device Category *</label>
            <select name="device_category_id" id="device_category" required onchange="calculateCost()"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Select device category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                            data-cost="{{ $category->service_cost }}"
                            {{ old('device_category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }} - ${{ number_format($category->service_cost, 2) }} per device
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Device Count -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Number of Devices *</label>
            <input type="number" name="device_count" id="device_count" value="{{ old('device_count', 1) }}"
                   min="1" max="100" required onchange="calculateCost()"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <p class="text-sm text-gray-500 mt-1">How many devices do you want to service?</p>
        </div>

        <!-- Cost Summary -->
        <div id="cost-summary" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 hidden">
            <h3 class="font-semibold text-gray-800 mb-2">Cost Summary</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>Service cost per device:</span>
                    <span class="font-semibold">$<span id="cost-per-device">0.00</span></span>
                </div>
                <div class="flex justify-between">
                    <span>Number of devices:</span>
                    <span class="font-semibold"><span id="device-count-display">0</span></span>
                </div>
                <div class="border-t border-blue-300 pt-2 mt-2 flex justify-between text-lg">
                    <span class="font-bold">Total Cost:</span>
                    <span class="font-bold text-blue-600">$<span id="total-cost">0.00</span></span>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit"
                class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
            Continue to Payment
        </button>
    </form>
</div>

<script>
function calculateCost() {
    const categorySelect = document.getElementById('device_category');
    const deviceCountInput = document.getElementById('device_count');
    const costSummary = document.getElementById('cost-summary');

    if (!categorySelect.value || !deviceCountInput.value) {
        costSummary.classList.add('hidden');
        return;
    }

    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
    const costPerDevice = parseFloat(selectedOption.dataset.cost || 0);
    const deviceCount = parseInt(deviceCountInput.value || 0);
    const totalCost = costPerDevice * deviceCount;

    document.getElementById('cost-per-device').textContent = costPerDevice.toFixed(2);
    document.getElementById('device-count-display').textContent = deviceCount;
    document.getElementById('total-cost').textContent = totalCost.toFixed(2);

    costSummary.classList.remove('hidden');
}

// Calculate on page load if values exist
document.addEventListener('DOMContentLoaded', calculateCost);
</script>
@endsection
