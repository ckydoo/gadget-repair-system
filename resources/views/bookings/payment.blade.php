<!-- ============================================================ -->
<!-- VIEW 4: Payment Page -->
<!-- File: resources/views/bookings/payment.blade.php -->
<!-- ============================================================ -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Payment</h1>
        <p class="text-gray-600">Complete your booking payment</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Booking Summary -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Booking Summary</h2>

                <div class="space-y-3 mb-6">
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-600">Booking Type:</span>
                        <span class="font-semibold capitalize">{{ $booking->type }}</span>
                    </div>

                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-600">Device Category:</span>
                        <span class="font-semibold">{{ $booking->deviceCategory->name }}</span>
                    </div>

                    @if($booking->type === 'service')
                        <div class="flex justify-between pb-2 border-b">
                            <span class="text-gray-600">Number of Devices:</span>
                            <span class="font-semibold">{{ $booking->device_count }}</span>
                        </div>
                        <div class="flex justify-between pb-2 border-b">
                            <span class="text-gray-600">Service Cost:</span>
                            <span class="font-semibold">${{ number_format($booking->service_cost_total, 2) }}</span>
                        </div>
                    @else
                        <div class="flex justify-between pb-2 border-b">
                            <span class="text-gray-600">Device:</span>
                            <span class="font-semibold">{{ $booking->device_brand }} {{ $booking->device_model }}</span>
                        </div>
                        <div class="flex justify-between pb-2 border-b">
                            <span class="text-gray-600">Diagnostic Fee:</span>
                            <span class="font-semibold">${{ number_format($booking->diagnostic_fee, 2) }}</span>
                        </div>

                        @if($booking->needs_transport)
                            <div class="flex justify-between pb-2 border-b">
                                <span class="text-gray-600">Transport Type:</span>
                                <span class="font-semibold capitalize">{{ $booking->transport_type }}</span>
                            </div>
                            <div class="flex justify-between pb-2 border-b">
                                <span class="text-gray-600">Distance:</span>
                                <span class="font-semibold">{{ number_format($booking->distance_km, 2) }} km</span>
                            </div>
                            <div class="flex justify-between pb-2 border-b">
                                <span class="text-gray-600">Transport Fee:</span>
                                <span class="font-semibold">${{ number_format($booking->transport_fee, 2) }}</span>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-800">Total Amount:</span>
                        <span class="text-2xl font-bold text-blue-600">${{ number_format($booking->total_fee, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Payment Method</h2>

                <form action="{{ route('bookings.payment.process', $booking->id) }}" method="POST">
                    @csrf

                    <div class="space-y-4 mb-6">
                        <!-- Payment Method Selection -->
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="card" checked
                                   class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3 flex-1">
                                <span class="font-semibold text-gray-800">💳 Credit/Debit Card</span>
                                <p class="text-sm text-gray-500">Pay securely with your card</p>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="mobile_money"
                                   class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3 flex-1">
                                <span class="font-semibold text-gray-800">📱 Mobile Money</span>
                                <p class="text-sm text-gray-500">EcoCash, OneMoney, etc.</p>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="bank_transfer"
                                   class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500">
                            <div class="ml-3 flex-1">
                                <span class="font-semibold text-gray-800">🏦 Bank Transfer</span>
                                <p class="text-sm text-gray-500">Direct bank transfer</p>
                            </div>
                        </label>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <p class="text-sm text-gray-700">
                            <strong>Note:</strong> This is a demo payment. In production, this would integrate with a real payment gateway like Stripe, PayPal, or local payment providers.
                        </p>
                    </div>

                    <button type="submit"
                            class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold text-lg">
                        Pay ${{ number_format($booking->total_fee, 2) }} Now
                    </button>
                </form>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Customer Information</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-500">Name</p>
                        <p class="font-semibold">{{ $booking->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Email</p>
                        <p class="font-semibold">{{ $booking->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Phone</p>
                        <p class="font-semibold">{{ $booking->user->phone }}</p>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t">
                    <p class="text-xs text-gray-500">
                        🔒 Your payment information is secure and encrypted
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
