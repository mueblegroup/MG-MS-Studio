<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $invoiceNumber }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        .page {
            padding: 32px;
        }

        .header {
            width: 100%;
            margin-bottom: 24px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-left {
            width: 60%;
            vertical-align: top;
        }

        .header-right {
            width: 40%;
            vertical-align: top;
            text-align: right;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 6px;
            color: #111827;
        }

        .muted {
            color: #6b7280;
        }

        .section {
            margin-top: 24px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e5e7eb;
            color: #111827;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .label {
            width: 180px;
            color: #6b7280;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .items-table th {
            background: #f3f4f6;
            color: #374151;
            font-size: 11px;
            text-transform: uppercase;
            text-align: left;
            padding: 10px 8px;
            border: 1px solid #e5e7eb;
        }

        .items-table td {
            padding: 10px 8px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .summary {
            width: 320px;
            margin-left: auto;
            margin-top: 18px;
            border-collapse: collapse;
        }

        .summary td {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
        }

        .summary .total-row td {
            font-weight: bold;
            background: #f9fafb;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: bold;
            background: #ecfdf5;
            color: #065f46;
        }

        .footer {
            margin-top: 36px;
            font-size: 11px;
            color: #6b7280;
            text-align: center;
        }

        .small {
            font-size: 11px;
        }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <div class="title">PAYMENT RECEIPT</div>
                    <div><strong>{{ $company['name'] }}</strong></div>
                    @if(!empty($company['email']))
                        <div class="muted">{{ $company['email'] }}</div>
                    @endif
                    @if(!empty($company['phone']))
                        <div class="muted">{{ $company['phone'] }}</div>
                    @endif
                    @if(!empty($company['address']))
                        <div class="muted">{{ $company['address'] }}</div>
                    @endif
                </td>
                <td class="header-right">
                    <div><strong>Receipt No:</strong> {{ $invoiceNumber }}</div>
                    <div><strong>Issued At:</strong> {{ $issuedAt?->format('Y-m-d H:i') }}</div>
                    <div>
                        <strong>Status:</strong>
                        <span class="badge">{{ strtoupper($payment->status ?? 'pending') }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Customer Details</div>
        <table class="info-table">
            <tr>
                <td class="label">Name</td>
                <td>{{ $payment->user->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td>{{ $payment->user->email ?? '-' }}</td>
            </tr>
            @if(!empty($payload['customer_details']['phone']) || !empty($payload['phone']))
                <tr>
                    <td class="label">Phone</td>
                    <td>{{ $payload['customer_details']['phone'] ?? $payload['phone'] }}</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="section">
        <div class="section-title">Payment Details</div>
        <table class="info-table">
            <tr>
                <td class="label">Payment ID</td>
                <td>{{ $payment->id }}</td>
            </tr>
            <tr>
                <td class="label">Reference</td>
                <td>{{ $payment->reference ?? ('PAY-' . $payment->id) }}</td>
            </tr>
            <tr>
                <td class="label">Provider</td>
                <td>{{ strtoupper($payment->provider ?? $payment->method ?? '-') }}</td>
            </tr>
            <tr>
                <td class="label">Provider Reference</td>
                <td>{{ $payment->provider_reference ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Currency</td>
                <td>{{ strtoupper($payment->currency ?? 'MYR') }}</td>
            </tr>
            <tr>
                <td class="label">Amount Paid</td>
                <td>{{ strtoupper($payment->currency ?? 'MYR') }} {{ number_format((float) $total, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Paid At</td>
                <td>{{ $payment->paid_at?->format('Y-m-d H:i') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Created At</td>
                <td>{{ $payment->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
            </tr>

            @if(!empty($payment->order_id))
                <tr>
                    <td class="label">Order ID</td>
                    <td>#{{ $payment->order_id }}</td>
                </tr>
            @endif

            @if(!empty($payload['payment_id']))
                <tr>
                    <td class="label">Gateway Payment ID</td>
                    <td>{{ $payload['payment_id'] }}</td>
                </tr>
            @endif

            @if(!empty($payload['payment_request_id']))
                <tr>
                    <td class="label">Gateway Request ID</td>
                    <td>{{ $payload['payment_request_id'] }}</td>
                </tr>
            @endif

            @if(!empty($payload['payment_intent']))
                <tr>
                    <td class="label">Stripe Payment Intent</td>
                    <td>{{ $payload['payment_intent'] }}</td>
                </tr>
            @endif

            @if(!empty($payload['id']))
                <tr>
                    <td class="label">Session / Checkout ID</td>
                    <td>{{ $payload['id'] }}</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="section">
        <div class="section-title">Order Items</div>

        <table class="items-table">
            <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>Item</th>
                <th>Description</th>
                <th style="width: 70px;" class="text-right">Qty</th>
                <th style="width: 110px;" class="text-right">Unit Price</th>
                <th style="width: 120px;" class="text-right">Line Total</th>
            </tr>
            </thead>
            <tbody>
            @forelse($payment->order?->items ?? [] as $index => $item)
                @php
                    $qty = (int) ($item->quantity ?? 1);
                    $price = (float) ($item->price ?? 0);
                    $lineTotal = $qty * $price;

                    $meta = is_array($item->meta)
                        ? $item->meta
                        : (json_decode($item->meta ?? '[]', true) ?: []);

                    $itemName =
                        $meta['title'] ??
                        $meta['name'] ??
                        $item->purchasable->title ??
                        $item->purchasable->name ??
                        class_basename($item->purchasable_type ?? 'Item');

                    $descriptionParts = [];

                    if (!empty($item->purchasable_type)) {
                        $descriptionParts[] = 'Type: ' . class_basename($item->purchasable_type);
                    }

                    if (!empty($meta['description'])) {
                        $descriptionParts[] = $meta['description'];
                    }

                    if (!empty($meta['schedule'])) {
                        $descriptionParts[] = 'Schedule: ' . $meta['schedule'];
                    }

                    if (!empty($meta['duration'])) {
                        $descriptionParts[] = 'Duration: ' . $meta['duration'];
                    }

                    if (!empty($meta['student_email'])) {
                        $descriptionParts[] = 'Student: ' . $meta['student_email'];
                    }

                    $description = implode(' | ', $descriptionParts);
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $itemName }}</strong></td>
                    <td class="small">{{ $description ?: '-' }}</td>
                    <td class="text-right">{{ $qty }}</td>
                    <td class="text-right">{{ strtoupper($payment->currency ?? 'MYR') }} {{ number_format($price, 2) }}</td>
                    <td class="text-right">{{ strtoupper($payment->currency ?? 'MYR') }} {{ number_format($lineTotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;">No order items found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <table class="summary">
            <tr>
                <td>Subtotal</td>
                <td class="text-right">{{ strtoupper($payment->currency ?? 'MYR') }} {{ number_format($subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td class="text-right">{{ strtoupper($payment->currency ?? 'MYR') }} {{ number_format($discount, 2) }}</td>
            </tr>
            <tr>
                <td>Tax</td>
                <td class="text-right">
                    {{ strtoupper($payment->currency ?? 'MYR') }}
                    {{ number_format((float) data_get($payload, 'total_details.amount_tax', 0) / 100, 2) }}
                </td>
            </tr>
            <tr class="total-row">
                <td>Total Paid</td>
                <td class="text-right">{{ strtoupper($payment->currency ?? 'MYR') }} {{ number_format($total, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        This receipt was generated electronically by {{ $company['name'] }} and is valid without signature.
    </div>
</div>
</body>
</html>