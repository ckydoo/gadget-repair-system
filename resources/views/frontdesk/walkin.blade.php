<!-- ============================================================ -->
<!-- VIEW 3: Walk-in Customer Registration Form -->
<!-- File: resources/views/frontdesk/walkin.blade.php -->
<!-- ============================================================ -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-8">
        <a href="{{ route('frontdesk.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Back to Dashboard
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Register Walk-in Customer</h1>
        <p class="text-gray-600">Register a new customer without online booking</p>
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

    @if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('frontdesk.walkin.register') }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf

        <!-- Customer Information -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b">Customer Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                    <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email (Optional)</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address (Optional)</label>
                    <input type="text" name="customer_address" value="{{ old('customer_address') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>

        <!-- Device Information -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b">Device Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Service Type *</label>
                    <select name="type" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Type</option>
                        <option value="service" {{ old('type') == 'service' ? 'selected' : '' }}>Service</option>
                        <option value="repair" {{ old('type') == 'repair' ? 'selected' : '' }}>Repair</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Device Category *</label>
                    <select name="device_category_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Category</option>
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
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Device Model *</label>
                    <input type="text" name="device_model" value="{{ old('device_model') }}" required
                           placeholder="e.g., iPhone 13, Galaxy S21"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Problem Description *</label>
                    <textarea name="problem_description" rows="4" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('problem_description') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex gap-4">
            <button type="submit"
                    class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                Register & Check-in
            </button>
            <a href="{{ route('frontdesk.index') }}"
               class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-semibold">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
