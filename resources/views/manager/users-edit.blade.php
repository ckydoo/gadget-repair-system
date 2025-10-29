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
        <h1 class="text-3xl font-bold text-gray-800">Edit User</h1>
        <p class="text-gray-600 mt-2">Update user information and permissions</p>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-8">
            <form method="POST" action="{{ route('manager.users.update', $user) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                    <input type="text" id="name" name="name" 
                           class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror" 
                           value="{{ old('name', $user->name) }}" required>
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address *</label>
                    <input type="email" id="email" name="email" 
                           class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror" 
                           value="{{ old('email', $user->email) }}" required>
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="tel" id="phone" name="phone" 
                           class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                           value="{{ old('phone', $user->phone) }}">
                </div>

                <!-- Role Selection -->
                <div class="border-t border-gray-200 pt-6">
                    <label for="role" class="block text-sm font-bold text-gray-700 mb-3">User Role *</label>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @php
                            $userRole = old('role', $user->roles->first()?->name);
                        @endphp

                        <!-- Admin -->
                        <label class="relative flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition {{ $userRole === 'admin' ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="radio" name="role" value="admin" class="mt-1" {{ $userRole === 'admin' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Administrator</p>
                                <p class="text-sm text-gray-500">Full system access and control</p>
                            </div>
                        </label>

                        <!-- Manager -->
                        <label class="relative flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition {{ $userRole === 'manager' ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="radio" name="role" value="manager" class="mt-1" {{ $userRole === 'manager' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Manager</p>
                                <p class="text-sm text-gray-500">Manage operations and staff</p>
                            </div>
                        </label>

                        <!-- Supervisor -->
                        <label class="relative flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition {{ $userRole === 'supervisor' ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="radio" name="role" value="supervisor" class="mt-1" {{ $userRole === 'supervisor' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Supervisor</p>
                                <p class="text-sm text-gray-500">Oversee work and staff</p>
                            </div>
                        </label>

                        <!-- Front Desk -->
                        <label class="relative flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition {{ $userRole === 'front_desk' ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="radio" name="role" value="front_desk" class="mt-1" {{ $userRole === 'front_desk' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Front Desk Staff</p>
                                <p class="text-sm text-gray-500">Customer service and bookings</p>
                            </div>
                        </label>

                        <!-- Technician -->
                        <label class="relative flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition {{ $userRole === 'technician' ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="radio" name="role" value="technician" class="mt-1" {{ $userRole === 'technician' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Technician</p>
                                <p class="text-sm text-gray-500">Repair and device services</p>
                            </div>
                        </label>

                        <!-- Client -->
                        <label class="relative flex items-start p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition {{ $userRole === 'client' ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="radio" name="role" value="client" class="mt-1" {{ $userRole === 'client' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <p class="font-medium text-gray-900">Client</p>
                                <p class="text-sm text-gray-500">Regular customer account</p>
                            </div>
                        </label>
                    </div>
                    @error('role')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Account Status -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Status</h3>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" 
                               class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500" 
                               {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                        <span class="ml-2 text-sm font-medium text-gray-700">Account is Active</span>
                    </label>
                    <p class="mt-2 text-xs text-gray-500">Uncheck to deactivate this user's account</p>
                </div>

                <!-- Form Actions -->
                <div class="border-t border-gray-200 pt-6 flex gap-3 justify-between">
                    <form method="POST" action="{{ route('manager.users.delete', $user) }}" class="inline" onsubmit="return confirm('Are you absolutely sure? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete User
                        </button>
                    </form>

                    <div class="flex gap-3">
                        <a href="{{ route('manager.users') }}" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-medium">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Update User
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection