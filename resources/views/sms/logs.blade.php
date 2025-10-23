@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800">SMS Logs</h1>
        <a href="{{ route('sms.test') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            Back to Test Panel
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-600 mb-1">Total SMS</div>
            <div class="text-3xl font-bold text-gray-800">{{ $logs->total() }}</div>
        </div>

        <div class="bg-green-50 rounded-lg shadow p-6">
            <div class="text-sm text-green-600 mb-1">Sent</div>
            <div class="text-3xl font-bold text-green-700">
                {{ \App\Models\SmsLog::where('status', 'sent')->count() }}
            </div>
        </div>

        <div class="bg-yellow-50 rounded-lg shadow p-6">
            <div class="text-sm text-yellow-600 mb-1">Pending</div>
            <div class="text-3xl font-bold text-yellow-700">
                {{ \App\Models\SmsLog::where('status', 'pending')->count() }}
            </div>
        </div>

        <div class="bg-red-50 rounded-lg shadow p-6">
            <div class="text-sm text-red-600 mb-1">Failed</div>
            <div class="text-3xl font-bold text-red-700">
                {{ \App\Models\SmsLog::where('status', 'failed')->count() }}
            </div>
        </div>
    </div>

    <!-- SMS Logs Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date/Time
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Recipient
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Phone
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Message
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->user->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->phone_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $log->type === 'reminder_day3' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $log->type === 'reminder_day4' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $log->type === 'ready_for_collection' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $log->type === 'storage_fee_notification' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $log->type === 'test' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $log->type === 'general' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $log->type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="max-w-xs truncate" title="{{ $log->message }}">
                                {{ Str::limit($log->message, 50) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                {{ $log->status === 'sent' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $log->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $log->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($log->status) }}
                            </span>
                            @if($log->sent_at)
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $log->sent_at->format('H:i') }}
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No SMS logs found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
