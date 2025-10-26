<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .invoice-box {
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .invoice-box h2 {
            margin: 0 0 15px 0;
            color: #4A90E2;
            font-size: 20px;
            border-bottom: 2px solid #4A90E2;
            padding-bottom: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e8e8e8;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .line-items {
            margin-top: 20px;
        }
        .line-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .line-item:first-child {
            border-top: 2px solid #4A90E2;
            padding-top: 15px;
        }
        .line-item-label {
            font-weight: 500;
            color: #555;
        }
        .line-item-value {
            font-weight: 500;
            color: #333;
        }
        .subtotal-item {
            background: #f5f5f5;
            padding: 12px;
            margin: 5px 0;
            border-radius: 4px;
        }
        .total-item {
            background: #4A90E2;
            color: white;
            padding: 15px;
            margin-top: 10px;
            border-radius: 4px;
            font-size: 18px;
            font-weight: 700;
        }
        .total-item .line-item-label,
        .total-item .line-item-value {
            color: white;
        }
        .device-info {
            background: #E8F4F8;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #4A90E2;
        }
        .device-info p {
            margin: 5px 0;
        }
        .footer {
            background: #f9f9f9;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
            border-top: 1px solid #e0e0e0;
        }
        .footer p {
            margin: 5px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #4A90E2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: 600;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending {
            background: #FFF3CD;
            color: #856404;
        }
        .status-paid {
            background: #D4EDDA;
            color: #155724;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            .content {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 Invoice Generated</h1>
        </div>

        <div class="content">
            <div class="greeting">
                <p>Dear <strong>{{ $client->name }}</strong>,</p>
            </div>

            <p>Your invoice for the repair of your device has been generated and is ready for payment.</p>

            <div class="device-info">
                <p><strong>📱 Device:</strong> {{ $task->device_brand }} {{ $task->device_model }}</p>
                <p><strong>🔧 Service Type:</strong> {{ ucfirst($task->service_type) }}</p>
                <p><strong>📋 Issue:</strong> {{ $task->problem_description }}</p>
            </div>

            <div class="invoice-box">
                <h2>Invoice Details</h2>

                <div class="info-row">
                    <span class="info-label">Invoice Number:</span>
                    <span class="info-value"><strong>{{ $invoice->invoice_number }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Task ID:</span>
                    <span class="info-value">{{ $task->task_id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date:</span>
                    <span class="info-value">{{ $invoice->created_at->format('d M Y, h:i A') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <span class="status-badge status-{{ $invoice->status }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </span>
                </div>

                <div class="line-items">
                    @if($invoice->materials_cost > 0)
                    <div class="line-item">
                        <span class="line-item-label">🔩 Materials Cost</span>
                        <span class="line-item-value">${{ number_format($invoice->materials_cost, 2) }}</span>
                    </div>
                    @endif

                    @if($invoice->labour_cost > 0)
                    <div class="line-item">
                        <span class="line-item-label">👨‍🔧 Labour Cost</span>
                        <span class="line-item-value">${{ number_format($invoice->labour_cost, 2) }}</span>
                    </div>
                    @endif

                    @if($invoice->transport_cost > 0)
                    <div class="line-item">
                        <span class="line-item-label">🚗 Transport Cost</span>
                        <span class="line-item-value">${{ number_format($invoice->transport_cost, 2) }}</span>
                    </div>
                    @endif

                    @if($invoice->diagnostic_fee > 0)
                    <div class="line-item">
                        <span class="line-item-label">🔍 Diagnostic Fee</span>
                        <span class="line-item-value">${{ number_format($invoice->diagnostic_fee, 2) }}</span>
                    </div>
                    @endif

                    <div class="line-item subtotal-item">
                        <span class="line-item-label">Subtotal</span>
                        <span class="line-item-value">${{ number_format($invoice->subtotal, 2) }}</span>
                    </div>

                    <div class="line-item subtotal-item">
                        <span class="line-item-label">Tax (15%)</span>
                        <span class="line-item-value">${{ number_format($invoice->tax, 2) }}</span>
                    </div>

                    <div class="line-item total-item">
                        <span class="line-item-label">💰 TOTAL AMOUNT</span>
                        <span class="line-item-value">${{ number_format($invoice->total, 2) }}</span>
                    </div>
                </div>
            </div>

            @if($invoice->status === 'pending')
            <div style="text-align: center;">
                <p style="margin: 20px 0; font-weight: 600; color: #4A90E2;">
                    Please proceed with the payment to collect your device.
                </p>
            </div>
            @endif

            <p style="margin-top: 30px;">If you have any questions about this invoice, please don't hesitate to contact us.</p>

            <p style="margin-top: 20px; color: #666;">
                <strong>Thank you for choosing our repair service! 🙏</strong>
            </p>
        </div>

        <div class="footer">
            <p><strong>Device Repair Service</strong></p>
            <p>This is an automated email. Please do not reply directly to this message.</p>
            <p style="margin-top: 10px; font-size: 12px; color: #999;">
                © {{ date('Y') }} Device Repair Service. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
