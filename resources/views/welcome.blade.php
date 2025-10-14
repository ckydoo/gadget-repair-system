<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gadget Repair System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-4xl mx-auto p-8">
            <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                <h1 class="text-4xl font-bold text-blue-600 mb-4">
                    🔧 Gadget Repair System
                </h1>
                <p class="text-gray-600 mb-8 text-lg">
                    Professional Device Repair Management
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    @auth
                        @if(auth()->user()->hasRole('front_desk') || auth()->user()->hasRole('admin'))
                        <a href="{{ route('frontdesk.index') }}"
                           class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Go to Front Desk Dashboard
                        </a>
                        @endif

                        @if(auth()->user()->hasRole('client'))
                        <a href="{{ route('bookings.index') }}"
                           class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            Book a Service/Repair
                        </a>
                        <a href="{{ route('bookings.my-bookings') }}"
                           class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                            My Bookings
                        </a>
                        @endif

                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="w-full px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                           class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Login
                        </a>
                        <a href="{{ route('register') }}"
                           class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            Register
                        </a>
                    @endauth
                </div>

                <div class="border-t pt-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">System Status</h2>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-3xl font-bold text-green-600">✓</p>
                            <p class="text-sm text-gray-600">System Online</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-blue-600">{{ \App\Models\User::count() }}</p>
                            <p class="text-sm text-gray-600">Users</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-purple-600">{{ \App\Models\Task::count() }}</p>
                            <p class="text-sm text-gray-600">Tasks</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 text-sm text-gray-500">
                    <p>Laravel v{{ Illuminate\Foundation\Application::VERSION }}</p>
                    <p>PHP v{{ PHP_VERSION }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
