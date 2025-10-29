@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="{{ route('manager.users') }}" class="text-blue-600 hover:text-blue-900 flex items-center mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Users
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Create New User</h1>
        <p class="text-gray-600 mt-2">Add a new user to the system</p>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-8">
            <form method="POST" action="{{ route('manager.users.store') }}" class="space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                    <input type="text" id="name" name="name" 
                           class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror" 
                           value="{{ old('name') }}" required placeholder="John Doe">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address *</label>
                    <input type="email" id="email" name="email" 
                           class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror" 
                           value="{{ old('email') }}" required placeholder="john@example.com">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="tel" id="phone" name="phone" 
                           class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                           value="{{ old('phone') }}" placeholder="+1 (555) 000-0000">
                </div>

                <!-- Role Selection -->
                <div class="border-t border-gray-200 pt-6">
                    <label for="role" class="block text-sm font-bold text-gray-700 mb-3">Select Role *</label>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Admin -->
                        <label class="relative flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition {{ old('role') === 'admin' ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="radio" name="role" value="admin" class="mt-1" {{ old('role') === 'admin' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Administrator</p>
                                <p class="text-sm text-gray-500">Full system access and control</p>
                            </div>
                        </label>

                        <!-- Manager -->
                        <label class="relative flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition {{ old('role') === 'manager' ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="radio" name="role" value="manager" class="mt-1" {{ old('role') === 'manager' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Manager</p>
                                <p class="text-sm text-gray-500">Manage operations and staff</p>
                            </div>
                        </label>

                        <!-- Supervisor -->
                        <label class="relative flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition {{ old('role') === 'supervisor' ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="radio" name="role" value="supervisor" class="mt-1" {{ old('role') === 'supervisor' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Supervisor</p>
                                <p class="text-sm text-gray-500">Oversee work and staff</p>
                            </div>
                        </label>

                        <!-- Front Desk -->
                        <label class="relative flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition {{ old('role') === 'front_desk' ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="radio" name="role" value="front_desk" class="mt-1" {{ old('role') === 'front_desk' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Front Desk Staff</p>
                                <p class="text-sm text-gray-500">Customer service and bookings</p>
                            </div>
                        </label>

                        <!-- Technician -->
                        <label class="relative flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition {{ old('role') === 'technician' ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="radio" name="role" value="technician" class="mt-1" {{ old('role') === 'technician' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Technician</p>
                                <p class="text-sm text-gray-500">Repair and device services</p>
                            </div>
                        </label>

                        <!-- Client -->
                        <label class="relative flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition {{ old('role') === 'client' ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="radio" name="role" value="client" class="mt-1" {{ old('role') === 'client' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Client</p>
                                <p class="text-sm text-gray-500">Regular customer account</p>
                            </div>
                        </label>
                    </div>
                    @error('role')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Password Section -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Credentials</h3>
                    
                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
                        <input type="password" id="password" name="password" 
                               class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror" 
                               required placeholder="Minimum 8 characters">
                        <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters long</p>
                        @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password *</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" 
                               class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                               required placeholder="Re-enter password">
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="border-t border-gray-200 pt-6 flex gap-3 justify-end">
                    <a href="{{ route('manager.users') }}" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection