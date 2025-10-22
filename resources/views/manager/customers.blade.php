@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Customer Management</h1>
        <p class="text-gray-600">View and manage customer information</p>
    </div>

    <!-- Customer Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Total Customers</p>
            <p class="text-3xl font-bold text-blue-600">{{ $customers->total() }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">New This Month</p>
            <p class="text-3xl font-bold text-green-600">{{ \App\Models\User::role('client')->whereMonth('created_at', now()->month)->count() }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">With Active Tasks</p>
            <p class="text-3xl font-bold text-orange-600">{{ \App\Models\User::role('client')->whereHas('tasks', function($q) { $q->active(); })->count() }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Repeat Customers</p>
            <p class="text-3xl font-bold text-purple-600">{{ \App\Models\User::role('client')->has('bookings', '>', 1)->count() }}</p>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Bookings</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tasks</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="bg-blue-600 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold mr-3">
                                    {{ substr($customer->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $customer->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $customer->phone }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $customer->city ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500">{{ $customer->country ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $customer->bookings_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                {{ $customer->tasks_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $customer->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button onclick="viewCustomerDetails({{ $customer->id }})"
                                    class="text-blue-600 hover:text-blue-900 font-semibold">
                                View Details
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No customers found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $customers->links() }}
        </div>
    </div>
</div>

<script>
function viewCustomerDetails(customerId) {
    // Redirect to customer bookings or tasks
    window.location.href = `/manager/customers/${customerId}`;
}
</script>
@endsection
