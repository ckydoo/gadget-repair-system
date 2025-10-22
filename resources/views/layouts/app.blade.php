<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Gadget Repair System') }}</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    @stack('styles')
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-blue-600">Gadget Repair</h1>

                    <!-- Public Tracking Link -->
                    <a href="{{ route('tracking.index') }}" class="hidden md:block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md font-medium">
                        📍 Track Device
                    </a>

                    @auth
                        <div class="hidden md:flex space-x-4">
                            @role('client')
                            <a href="{{ route('bookings.index') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md font-medium">
                                Book Service
                            </a>
                            <a href="{{ route('bookings.my-bookings') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md font-medium">
                                My Bookings
                            </a>
                            @endrole

                            @role('front_desk|admin')
                            <a href="{{ route('frontdesk.index') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md font-medium">
                                Front Desk
                            </a>
                            @endrole

                            @role('technician|admin')
                            <a href="{{ route('technician.index') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md font-medium">
                                My Tasks
                            </a>
                            @endrole

                            @role('manager|supervisor|admin')
                            <a href="{{ route('manager.index') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md font-medium">
                                Dashboard
                            </a>
                            @endrole
                        </div>
                    @endauth
                </div>

                @auth
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">{{ auth()->user()->name }}</span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                        {{ auth()->user()->roles->first()->name ?? 'User' }}
                    </span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-700 hover:text-red-600 px-3 py-2 rounded-md font-medium">
                            Logout
                        </button>
                    </form>
                </div>
                @else
                <div>
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md font-medium">
                        Login
                    </a>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white shadow-lg mt-12">
        <div class="container mx-auto px-4 py-6 text-center text-gray-600">
            <p>&copy; {{ date('Y') }} Gadget Repair System. All rights reserved.</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
