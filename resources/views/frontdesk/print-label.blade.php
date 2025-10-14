
<!-- ============================================================ -->
<!-- VIEW 4: Print Label Page -->
<!-- File: resources/views/frontdesk/print-label.blade.php -->
<!-- ============================================================ -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Print Device Label</h1>
        <p class="text-gray-600">Print label for Task ID: <span class="font-mono font-bold text-blue-600">{{ $task->task_id }}</span></p>
    </div>

    <!-- Label Preview -->
    <div class="bg-white rounded-lg shadow p-8 mb-6">
        <div id="label-content" class="border-4 border-gray-800 p-8 max-w-md mx-auto">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold">GADGET REPAIR SYSTEM</h2>
                <p class="text-sm text-gray-600">Device Service Label</p>
            </div>

            <div class="space-y-4">
                <div class="border-b pb-2">
                    <p class="text-xs text-gray-500">TASK ID</p>
                    <p class="text-2xl font-mono font-bold">{{ $task->task_id }}</p>
                </div>

                <div class="border-b pb-2">
                    <p class="text-xs text-gray-500">CUSTOMER</p>
                    <p class="text-lg font-semibold">{{ $task->user->name }}</p>
                </div>

                <div class="border-b pb-2">
                    <p class="text-xs text-gray-500">DEVICE</p>
                    <p class="text-lg font-semibold">{{ $task->device_brand }} {{ $task->device_model }}</p>
                    <p class="text-sm text-gray-600">{{ $task->deviceCategory->name }}</p>
                </div>

                <div class="border-b pb-2">
                    <p class="text-xs text-gray-500">TECHNICIAN</p>
                    <p class="text-lg font-semibold">{{ $task->technician->name }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">CHECK-IN DATE</p>
                    <p class="text-sm">{{ $task->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <!-- Barcode placeholder -->
            <div class="mt-6 text-center">
                <svg id="barcode" class="mx-auto"></svg>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-4">
        <button onclick="window.print()"
                class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
            🖨️ Print Label
        </button>
        <a href="{{ route('frontdesk.index') }}"
           class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-semibold">
            Done
        </a>
    </div>
</div>

<!-- Include JsBarcode library for barcode generation -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    // Generate barcode
    JsBarcode("#barcode", "{{ $task->task_id }}", {
        format: "CODE128",
        width: 2,
        height: 60,
        displayValue: false
    });
</script>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #label-content, #label-content * {
            visibility: visible;
        }
        #label-content {
            position: absolute;
            left: 0;
            top: 0;
        }
    }
</style>
@endsection
