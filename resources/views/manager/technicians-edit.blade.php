@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('manager.technicians') }}"
               class="text-gray-600 hover:text-gray-900 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Edit Technician</h1>
                <p class="text-gray-600">Update technician information</p>
            </div>
        </div>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
        <p class="font-semibold mb-2">Please fix the following errors:</p>
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Form -->
    <form action="{{ route('manager.technicians.update', $technician->id) }}" method="POST" class="bg-white rounded-lg shadow-lg p-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Personal Information Section -->
            <div class="md:col-span-2">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Personal Information
                </h2>
            </div>

            <!-- Full Name -->
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="name"
                       id="name"
                       value="{{ old('name', $technician->name) }}"
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                       placeholder="John Doe">
                @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input type="email"
                       name="email"
                       id="email"
                       value="{{ old('email', $technician->email) }}"
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                       placeholder="john@example.com">
                @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                    Phone Number <span class="text-red-500">*</span>
                </label>
                <input type="tel"
                       name="phone"
                       id="phone"
                       value="{{ old('phone', $technician->phone) }}"
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror"
                       placeholder="+263771234567">
                @error('phone')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- City -->
            <div>
                <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">
                    City
                </label>
                <input type="text"
                       name="city"
                       id="city"
                       value="{{ old('city', $technician->city) }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Harare">
            </div>

            <!-- Address -->
            <div class="md:col-span-2">
                <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                    Address
                </label>
                <input type="text"
                       name="address"
                       id="address"
                       value="{{ old('address', $technician->address) }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="123 Main Street">
            </div>

            <!-- Country -->
            <div>
                <label for="country" class="block text-sm font-semibold text-gray-700 mb-2">
                    Country
                </label>
                <input type="text"
                       name="country"
                       id="country"
                       value="{{ old('country', $technician->country) }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Zimbabwe">
            </div>

            <!-- Account Security Section -->
            <div class="md:col-span-2 mt-6">
                <h2 class="text-xl font-bold text-gray-800 mb-2 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Change Password
                </h2>
                <p class="text-sm text-gray-500 mb-4">Leave blank to keep current password</p>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                    New Password
                </label>
                <input type="password"
                       name="password"
                       id="password"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror"
                       placeholder="Leave blank to keep current">
                @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                    Confirm New Password
                </label>
                <input type="password"
                       name="password_confirmation"
                       id="password_confirmation"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Confirm new password">
            </div>

            <!-- Professional Information Section -->
            <div class="md:col-span-2 mt-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Professional Information
                </h2>
            </div>

            <!-- Specializations -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Specializations <span class="text-red-500">*</span>
                </label>
                <p class="text-sm text-gray-500 mb-3">Select at least one device category this technician can work on</p>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @php
                        $currentSpecializations = old('specializations', $technician->technician->specializations ?? []);
                    @endphp
                    @foreach($deviceCategories as $category)
                    <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-blue-50 transition">
                        <input type="checkbox"
                               name="specializations[]"
                               value="{{ $category->id }}"
                               {{ in_array($category->id, $currentSpecializations) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">{{ $category->name }}</span>
                    </label>
                    @endforeach
                </div>
                @error('specializations')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Hourly Rate -->
            <div>
                <label for="hourly_rate" class="block text-sm font-semibold text-gray-700 mb-2">
                    Hourly Rate ($) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-3 text-gray-500">$</span>
                    <input type="number"
                           name="hourly_rate"
                           id="hourly_rate"
                           value="{{ old('hourly_rate', $technician->technician->hourly_rate ?? 25.00) }}"
                           step="0.01"
                           min="0"
                           required
                           class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('hourly_rate') border-red-500 @enderror"
                           placeholder="25.00">
                </div>
                @error('hourly_rate')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Max Workload -->
            <div>
                <label for="max_workload" class="block text-sm font-semibold text-gray-700 mb-2">
                    Maximum Workload <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       name="max_workload"
                       id="max_workload"
                       value="{{ old('max_workload', $technician->technician->max_workload ?? 10) }}"
                       min="1"
                       max="50"
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('max_workload') border-red-500 @enderror"
                       placeholder="10">
                <p class="text-sm text-gray-500 mt-1">Maximum number of concurrent tasks</p>
                @error('max_workload')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Availability Status -->
            <div class="md:col-span-2">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox"
                           name="is_available"
                           value="1"
                           {{ old('is_available', $technician->technician->is_available ?? true) ? 'checked' : '' }}
                           class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="ml-3">
                        <span class="text-sm font-semibold text-gray-700">Available for Tasks</span>
                        <span class="block text-sm text-gray-500">When checked, this technician will be available for new task assignments</span>
                    </span>
                </label>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('manager.technicians') }}"
               class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-semibold transition">
                Cancel
            </a>
            <button type="submit"
                    class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold shadow-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Update Technician
            </button>
        </div>
    </form>
</div>
@endsection
