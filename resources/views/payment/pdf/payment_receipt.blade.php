<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 28px;
            padding: 0;
            color: #1f2937;
            font-size: 13px;
        }
        .container {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 24px 26px;
        }
        .header {
            display: flex;
            align-items: center;
            gap: 14px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 16px;
            margin-bottom: 18px;
        }
        .logo {
            width: 140px;
            height: auto;
            object-fit: contain;
        }
        .brand {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }
        .subtitle {
            margin: 6px 0 0;
            color: #6b7280;
        }
        .bill-to {
            margin: 18px 0 16px;
        }
        .bill-to .label {
            font-weight: 700;
            margin-bottom: 6px;
        }
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
        }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid #f0f2f5;
        }
        .row:last-child {
            border-bottom: 0;
        }
        .label {
            color: #374151;
            font-weight: 600;
            width: 34%;
        }
        .value {
            width: 66%;
            text-align: left;
            color: #111827;
            font-weight: 600;
        }
        .status-success {
            color: #16a34a;
            text-transform: capitalize;
        }
        .footer {
            text-align: center;
            margin: 20px 0 10px;
            font-weight: 700;
            color: #6b7280;
        }
        .provider {
            text-align: center;
            color: #4b5563;
            line-height: 1.6;
            margin-top: 10px;
        }
        .provider .title {
            font-weight: 700;
        }
        .receipt-id {
            text-align: center;
            margin-top: 20px;
            color: #6b7280;
        }
    </style>
</head>
<body>
@php
    $p = $payload;
    $status = strtolower($p['status'] ?? 'unknown');
    $orderId = $p['reference_no'] ?? $p['order_id'] ?? $p['payment_id'] ?? '';
    $billTo = trim(($p['name'] ?? '') . ' ' . ($p['email'] ?? ''));
    $description = $p['description'] ?? '';
    $purpose = $p['purpose'] ?? $description;
    $datePaid = $p['date_paid'] ?? $p['paid_at'] ?? '';
    $paymentMethod = $p['payment_method'] ?? $p['method'] ?? '';
    $normalizeReceiptText = static function ($value) {
        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }
        $text = preg_replace('/\s+/', ' ', $text);
        $text = str_replace('?','PHP ', $text);
        $text = preg_replace('/PHP\s+/', 'PHP ', $text);
        return trim($text);
    };
    $descriptionValue = $normalizeReceiptText($description);
    $purposeValue = $normalizeReceiptText($purpose);
    $datePaidValue = trim((string) $datePaid);
    $paymentMethodValue = trim((string) $paymentMethod);
    $amountPaid = $p['amount'] ?? '';
    $receiptId = $p['payment_id'] ?? $p['id'] ?? $p['reference_no'] ?? '';
@endphp

<div class="container">
    <div class="header">
        <img class="logo" src="{{ public_path('images/novupay-logo.png') }}" alt="NovuPay Logo">
        <div>
            <div class="brand">Novupay</div>
            <div class="subtitle">View transaction details below</div>
        </div>
    </div>

    <div class="bill-to">
        <div class="label">Bill to:</div>
        <div>{{ $billTo }}</div>
    </div>

    <div class="card">
        <div class="row">
            <div class="label">Order ID</div>
            <div class="value">{{ $orderId }}</div>
        </div>
        <div class="row">
            <div class="label">Status</div>
            <div class="value status-success">{{ $status }}</div>
        </div>
        @if($descriptionValue !== '')
        <div class="row">
            <div class="label">Description</div>
            <div class="value">{{ $descriptionValue }}</div>
        </div>
        @endif
        @if($purposeValue !== '')
        <div class="row">
            <div class="label">Purpose</div>
            <div class="value">{{ $purposeValue }}</div>
        </div>
        @endif
        @if($datePaidValue !== '')
        <div class="row">
            <div class="label">Date Paid</div>
            <div class="value">{{ $datePaidValue }}</div>
        </div>
        @endif
        @if($paymentMethodValue !== '')
        <div class="row">
            <div class="label">Payment method</div>
            <div class="value">{{ $paymentMethodValue }}</div>
        </div>
        @endif
        @if(!empty($p['show_breakdown']))
        <div class="row">
            <div class="label">Amount</div>
            <div class="value">PHP {{ $p['amount_due'] ?? '0.00' }}</div>
        </div>
        @if(isset($p['disconnection_fee']) && (float) str_replace(',', '', $p['disconnection_fee']) > 0)
        <div class="row">
            <div class="label">Disconnection Fee</div>
            <div class="value">PHP {{ $p['disconnection_fee'] }}</div>
        </div>
        @endif
        @if(isset($p['surcharge']) && (float) str_replace(',', '', $p['surcharge']) > 0)
        <div class="row">
            <div class="label">Surcharge</div>
            <div class="value">PHP {{ $p['surcharge'] }}</div>
        </div>
        @endif
        @endif
        <div class="row">
            <div class="label">{{ !empty($p['show_breakdown']) ? 'Total paid' : 'Amount paid' }}</div>
            <div class="value">PHP {{ $amountPaid }}</div>
        </div>
    </div>

    <div class="footer">THIS SERVES AS AN OFFICIAL RECEIPT</div>

    <div class="provider">
        <div class="title">System Provider :</div>
        <div>HitPay Payment Solutions, Inc</div>
        <div>Level 21, 8 Rockwell, Hidalgo Drive Rockwell Center,</div>
        <div>Poblacion, Makati City, Fourth District, NCR, 1210</div>
        <div>TIN 600-613-625-00000</div>
    </div>

    <div class="receipt-id">ID: {{ $receiptId }}</div>
</div>

</body>
</html>
