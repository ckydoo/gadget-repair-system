<!-- Manager Dashboard Sidebar Navigation -->
<div class="bg-white shadow-lg">
    <div class="container mx-auto px-4">
        <!-- Desktop Navigation Bar -->
        <nav class="hidden md:flex items-center justify-between py-4 border-b border-gray-200">
            <!-- Logo/Home -->
            <div class="flex items-center space-x-8">
                <a href="{{ route('manager.index') }}" class="text-2xl font-bold text-blue-600 hover:text-blue-700">
                    RepairHub
                </a>

                <!-- Main Navigation Links -->
                <div class="flex space-x-1">
                    <!-- Dashboard -->
                    <a href="{{ route('manager.index') }}"
                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('manager.index') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4m0 0l4 4m-4-4v4m0 0H7"/>
                        </svg>
                        Dashboard
                    </a>

                    <!-- Tasks Dropdown -->
                    <div class="relative group">
                        <button class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 flex items-center group-hover:bg-gray-100 {{ request()->routeIs('manager.tasks*') ? 'bg-blue-100 text-blue-700' : '' }}">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Tasks
                            <svg class="w-4 h-4 ml-1 group-hover:rotate-180 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </button>
                        <!-- Dropdown Menu -->
                        <div class="hidden group-hover:block absolute left-0 mt-0 w-48 bg-white shadow-lg rounded-md py-2 z-50">
                            <a href="{{ route('manager.tasks') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                All Tasks
                            </a>
                            <a href="{{ route('manager.tasks', ['status' => 'assigned']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Active Tasks
                            </a>
                            <a href="{{ route('manager.tasks', ['status' => 'pending']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Pending
                            </a>
                            <a href="{{ route('manager.tasks', ['status' => 'completed']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Completed
                            </a>
                            <a href="{{ route('manager.tasks', ['status' => 'ready_for_collection']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                                Ready for Collection
                            </a>
                        </div>
                    </div>

                    <!-- Revenue -->
                    <a href="{{ route('manager.revenue') }}"
                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('manager.revenue') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Revenue
                    </a>

                    <!-- Technicians Dropdown -->
                    <div class="relative group">
                        <button class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 flex items-center group-hover:bg-gray-100 {{ request()->routeIs('manager.technicians*') ? 'bg-blue-100 text-blue-700' : '' }}">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            Technicians
                            <svg class="w-4 h-4 ml-1 group-hover:rotate-180 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </button>
                        <!-- Dropdown Menu -->
                        <div class="hidden group-hover:block absolute left-0 mt-0 w-48 bg-white shadow-lg rounded-md py-2 z-50">
                            <a href="{{ route('manager.technicians') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                All Technicians
                            </a>
                            <a href="{{ route('manager.technicians.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Technician
                            </a>
                        </div>
                    </div>

                    <!-- Users Dropdown -->
                    <div class="relative group">
                        <button class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 flex items-center group-hover:bg-gray-100 {{ request()->routeIs('manager.users*') ? 'bg-blue-100 text-blue-700' : '' }}">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20a6 6 0 0112 0v2H0v-2a6 6 0 0112 0z"/>
                            </svg>
                            Users
                            <svg class="w-4 h-4 ml-1 group-hover:rotate-180 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </button>
                        <!-- Dropdown Menu -->
                        <div class="hidden group-hover:block absolute left-0 mt-0 w-48 bg-white shadow-lg rounded-md py-2 z-50">
                            <a href="{{ route('manager.users') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center font-semibold border-b border-gray-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20a6 6 0 0112 0v2H0v-2a6 6 0 0112 0z"/>
                                </svg>
                                All Users
                            </a>

                            <!-- Front Desk Staff -->
                            <div class="px-4 py-2 text-xs font-bold text-gray-500 uppercase tracking-wide">
                                By Role
                            </div>
                            <a href="{{ route('manager.users', ['role' => 'front_desk']) }}" class="block px-4 py-2 pl-8 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Front Desk Staff
                            </a>

                            <!-- Managers -->
                            <a href="{{ route('manager.users', ['role' => 'manager']) }}" class="block px-4 py-2 pl-8 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Managers
                            </a>

                            <!-- Supervisors -->
                            <a href="{{ route('manager.users', ['role' => 'supervisor']) }}" class="block px-4 py-2 pl-8 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                </svg>
                                Supervisors
                            </a>

                            <div class="border-t border-gray-200 my-1"></div>

                            <a href="{{ route('manager.users.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center font-semibold text-blue-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add User
                            </a>
                        </div>
                    </div>

                    <!-- Analytics -->
                    <a href="{{ route('manager.analytics') }}"
                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('manager.analytics') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Analytics
                    </a>

                    <!-- Customers -->
                    <a href="{{ route('manager.customers') }}"
                       class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('manager.customers') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Customers
                    </a>
                </div>
            </div>

            <!-- Right Side: User Menu -->
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-700">{{ auth()->user()->name }}</span>
                <div class="relative group">
                    <button class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </button>
                    <!-- Dropdown -->
                    <div class="hidden group-hover:block absolute right-0 mt-0 w-48 bg-white shadow-lg rounded-md py-2 z-50">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Mobile Navigation Toggle -->
        <nav class="md:hidden py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <a href="{{ route('manager.index') }}" class="text-xl font-bold text-blue-600">
                    RepairHub
                </a>
                <div class="flex items-center space-x-4">
                    <span class="text-xs text-gray-700">{{ auth()->user()->name }}</span>
                    <button class="text-gray-700 hover:text-blue-600" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden mt-4 space-y-2">
                <a href="{{ route('manager.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Dashboard</a>
                <a href="{{ route('manager.tasks') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Tasks</a>
                <a href="{{ route('manager.revenue') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Revenue</a>
                <a href="{{ route('manager.technicians') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Technicians</a>
                <a href="{{ route('manager.users') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Users</a>
                <a href="{{ route('manager.analytics') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Analytics</a>
                <a href="{{ route('manager.customers') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Customers</a>
                <hr class="my-2">
                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Logout</button>
                </form>
            </div>
        </nav>
    </div>
</div>
