
<!-- ============================================================ -->
<!-- VIEW 4: Revenue Reports -->
<!-- File: resources/views/manager/revenue.blade.php -->
<!-- ============================================================ -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Revenue Reports</h1>
            <p class="text-gray-600">Financial overview and invoice management</p>
        </div>

        <!-- Period Filter -->
        <div>
            <form method="GET" action="{{ route('manager.revenue') }}" class="flex gap-2">
                <select name="period" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="day" {{ $period == 'day' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ $period == 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="year" {{ $period == 'year' ? 'selected' : '' }}>This Year</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Revenue Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <h3 class="text-sm font-semibold opacity-90 mb-2">Total Revenue</h3>
            <p class="text-4xl font-bold">${{ number_format($stats['total_revenue'], 2) }}</p>
            <p class="text-sm opacity-75 mt-2">{{ $stats['invoice_count'] }} invoices</p>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <h3 class="text-sm font-semibold opacity-90 mb-2">Materials Cost</h3>
            <p class="text-4xl font-bold">${{ number_format($stats['materials_cost'], 2) }}</p>
            <p class="text-sm opacity-75 mt-2">Parts & Components</p>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <h3 class="text-sm font-semibold opacity-90 mb-2">Labour Revenue</h3>
            <p class="text-4xl font-bold">${{ number_format($stats['labour_cost'], 2) }}</p>
            <p class="text-sm opacity-75 mt-2">Technician Work</p>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Breakdown</h2>
            <div class="space-y-3">
                <div class="flex justify-between pb-2 border-b">
                    <span class="text-gray-600">Average Invoice:</span>
                    <span class="font-bold text-gray-800">${{ number_format($stats['avg_invoice'], 2) }}</span>
                </div>
                <div class="flex justify-between pb-2 border-b">
                    <span class="text-gray-600">Tax Collected:</span>
                    <span class="font-bold text-gray-800">${{ number_format($stats['tax_collected'], 2) }}</span>
                </div>
                <div class="flex justify-between pb-2 border-b">
                    <span class="text-gray-600">Materials:</span>
                    <span class="font-bold text-gray-800">${{ number_format($stats['materials_cost'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Labour:</span>
                    <span class="font-bold text-gray-800">${{ number_format($stats['labour_cost'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Stats</h2>
            <div class="space-y-3">
                <div class="flex justify-between pb-2 border-b">
                    <span class="text-gray-600">Total Invoices:</span>
                    <span class="font-bold text-blue-600">{{ $stats['invoice_count'] }}</span>
                </div>
                <div class="flex justify-between pb-2 border-b">
                    <span class="text-gray-600">Period:</span>
                    <span class="font-bold text-gray-800">{{ ucfirst($period) }}</span>
                </div>
                <div class="flex justify-between pb-2 border-b">
                    <span class="text-gray-600">Revenue/Invoice:</span>
                    <span class="font-bold text-gray-800">${{ number_format($stats['avg_invoice'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Invoices</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Technician</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Materials</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Labour</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $invoice->task->user->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $invoice->task->technician->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                            ${{ number_format($invoice->materials_cost, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                            ${{ number_format($invoice->labour_cost, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900">
                            ${{ number_format($invoice->total, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $invoice->paid_at ? $invoice->paid_at->format('M d, Y') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No invoices for this period
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($invoices->isNotEmpty())
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-right">TOTAL:</td>
                        <td class="px-6 py-4 text-right">${{ number_format($invoices->sum('materials_cost'), 2) }}</td>
                        <td class="px-6 py-4 text-right">${{ number_format($invoices->sum('labour_cost'), 2) }}</td>
                        <td class="px-6 py-4 text-right text-green-600">${{ number_format($invoices->sum('total'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
