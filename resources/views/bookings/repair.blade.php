
<!-- ============================================================ -->
<!-- VIEW 3: Repair Booking Form -->
<!-- File: resources/views/bookings/repair.blade.php -->
<!-- ============================================================ -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-8">
        <a href="{{ route('bookings.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Back to Booking Options
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Book a Repair</h1>
        <p class="text-gray-600">Get your faulty device fixed</p>
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

    <form action="{{ route('bookings.repair.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
        @csrf

        <!-- Device Information -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b">Device Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Device Category *</label>
                    <select name="device_category_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('device_category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Device Brand *</label>
                    <input type="text" name="device_brand" value="{{ old('device_brand') }}" required
                           placeholder="e.g., Apple, Samsung"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Device Model *</label>
                    <input type="text" name="device_model" value="{{ old('device_model') }}" required
                           placeholder="e.g., iPhone 13, Galaxy S21"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Problem Description *</label>
                <textarea name="problem_description" rows="4" required
                          placeholder="Describe the problem with your device..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">{{ old('problem_description') }}</textarea>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Upload Images (Optional)</label>
                <input type="file" name="problem_images[]" multiple accept="image/*"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <p class="text-sm text-gray-500 mt-1">You can upload multiple images (max 5MB each)</p>
            </div>
        </div>

        <!-- Transport Service -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b">Transport Service</h2>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="needs_transport" id="needs_transport" value="1"
                           {{ old('needs_transport') ? 'checked' : '' }}
                           onchange="toggleTransport()"
                           class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <span class="ml-3 text-gray-700">I need device pickup/delivery service ($0.75 per km)</span>
                </label>
            </div>

            <div id="transport-fields" class="space-y-4 {{ old('needs_transport') ? '' : 'hidden' }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Transport Type *</label>
                    <select name="transport_type" id="transport_type"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        <option value="">Select type</option>
                        <option value="pickup" {{ old('transport_type') == 'pickup' ? 'selected' : '' }}>Pickup Only</option>
                        <option value="delivery" {{ old('transport_type') == 'delivery' ? 'selected' : '' }}>Delivery Only</option>
                        <option value="both" {{ old('transport_type') == 'both' ? 'selected' : '' }}>Both Pickup & Delivery</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pickup Address *</label>
                    <input type="text" name="pickup_address" id="pickup_address" value="{{ old('pickup_address') }}"
                           placeholder="Enter your address"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    <button type="button" onclick="getCurrentLocation()"
                            class="mt-2 text-sm text-red-600 hover:text-red-800">
                        📍 Use My Current Location
                    </button>
                </div>

                <input type="hidden" name="pickup_lat" id="pickup_lat">
                <input type="hidden" name="pickup_lng" id="pickup_lng">

                <div id="distance-info" class="bg-red-50 border border-red-200 rounded-lg p-4 hidden">
                    <p class="text-sm text-gray-700">
                        Distance: <span id="distance-value" class="font-semibold">0</span> km<br>
                        Transport Fee: $<span id="transport-fee" class="font-semibold">0.00</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Cost Summary -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-gray-800 mb-2">Cost Summary</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>Diagnostic Fee:</span>
                    <span class="font-semibold">$25.00</span>
                </div>
                <div class="flex justify-between" id="transport-cost-row" style="display: none;">
                    <span>Transport Fee:</span>
                    <span class="font-semibold">$<span id="transport-cost-display">0.00</span></span>
                </div>
                <div class="border-t border-red-300 pt-2 mt-2 flex justify-between text-lg">
                    <span class="font-bold">Total to Pay Now:</span>
                    <span class="font-bold text-red-600">$<span id="total-to-pay">25.00</span></span>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">* Parts and labor costs will be calculated after diagnosis</p>
        </div>

        <!-- Submit Button -->
        <button type="submit"
                class="w-full px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
            Continue to Payment
        </button>
    </form>
</div>

<script>
function toggleTransport() {
    const checkbox = document.getElementById('needs_transport');
    const fields = document.getElementById('transport-fields');
    const transportType = document.getElementById('transport_type');

    if (checkbox.checked) {
        fields.classList.remove('hidden');
        transportType.setAttribute('required', 'required');
    } else {
        fields.classList.add('hidden');
        transportType.removeAttribute('required');
        updateTotalCost(0);
    }
}

function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('pickup_lat').value = position.coords.latitude;
            document.getElementById('pickup_lng').value = position.coords.longitude;

            // Calculate distance (workshop coordinates - replace with actual)
            const workshopLat = -17.8252;
            const workshopLng = 31.0335;

            const distance = calculateDistance(
                workshopLat, workshopLng,
                position.coords.latitude, position.coords.longitude
            );

            const transportFee = distance * 0.75;

            document.getElementById('distance-value').textContent = distance.toFixed(2);
            document.getElementById('transport-fee').textContent = transportFee.toFixed(2);
            document.getElementById('distance-info').classList.remove('hidden');

            updateTotalCost(transportFee);

            // Reverse geocode to get address (optional - requires Google Maps API)
            alert('Location captured! Distance: ' + distance.toFixed(2) + ' km');
        }, function(error) {
            alert('Unable to get your location. Please enter address manually.');
        });
    } else {
        alert('Geolocation is not supported by your browser.');
    }
}

function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Earth's radius in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function updateTotalCost(transportFee) {
    const diagnosticFee = 25.00;
    const total = diagnosticFee + transportFee;

    document.getElementById('transport-cost-display').textContent = transportFee.toFixed(2);
    document.getElementById('total-to-pay').textContent = total.toFixed(2);

    if (transportFee > 0) {
        document.getElementById('transport-cost-row').style.display = 'flex';
    } else {
        document.getElementById('transport-cost-row').style.display = 'none';
    }
}
</script>
@endsection
