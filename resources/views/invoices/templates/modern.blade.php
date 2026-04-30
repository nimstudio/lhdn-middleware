<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: {{ $settings['layout']['font_family'] ?? 'Arial' }}, sans-serif;
            font-size: {{ $settings['layout']['font_size'] ?? 12 }}px;
            line-height: {{ $settings['layout']['line_spacing'] ?? 1.2 }};
            color: #333;
            background: #fff;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: {{ $settings['layout']['margins']['top'] ?? 20 }}mm {{ $settings['layout']['margins']['right'] ?? 15 }}mm {{ $settings['layout']['margins']['bottom'] ?? 20 }}mm {{ $settings['layout']['margins']['left'] ?? 15 }}mm;
        }

        .header {
            border-bottom: 3px solid {{ $settings['colors']['primary'] ?? '#3B82F6' }};
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .company-info {
            flex: 1;
        }

        .company-logo {
            max-width: 150px;
            max-height: 80px;
            margin-bottom: 10px;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: {{ $settings['colors']['primary'] ?? '#3B82F6' }};
            margin-bottom: 10px;
        }

        .invoice-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 20px;
        }

        .invoice-details, .customer-details {
            flex: 1;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: {{ $settings['colors']['primary'] ?? '#3B82F6' }};
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid {{ $settings['colors']['primary'] ?? '#3B82F6' }};
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .detail-label {
            font-weight: bold;
            color: #666;
        }

        .detail-value {
            color: #333;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .items-table th {
            background: {{ $settings['colors']['primary'] ?? '#3B82F6' }};
            color: white;
            padding: 15px 10px;
            text-align: left;
            font-weight: bold;
        }

        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
        }

        .items-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .items-table tr:hover {
            background: #f0f8ff;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-section {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }

        .totals-table {
            width: 300px;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 15px;
            border-bottom: 1px solid #eee;
        }

        .totals-table .total-label {
            font-weight: bold;
            color: #666;
        }

        .totals-table .total-value {
            font-weight: bold;
            text-align: right;
        }

        .grand-total {
            background: {{ $settings['colors']['primary'] ?? '#3B82F6' }};
            color: white;
            font-size: 18px;
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            text-align: center;
            color: #666;
            font-size: 12px;
        }

        .notes {
            margin-top: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid {{ $settings['colors']['primary'] ?? '#3B82F6' }};
            border-radius: 4px;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: {{ $settings['colors']['primary'] ?? '#3B82F6' }};
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-draft { background: #f3f4f6; color: #374151; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        @media print {
            .container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                @if($company->hasMedia('pdf_logo'))
                    <img src="{{ $company->getFirstMediaUrl('pdf_logo', 'pdf') }}" alt="{{ $company->name }}" class="company-logo">
                @endif
                <h1 class="invoice-title">{{ $company->name }}</h1>
                <div class="company-details">
                    @if($company->registration_number)
                        <div class="detail-row">
                            <span class="detail-label">Registration:</span>
                            <span class="detail-value">{{ $company->registration_number }}</span>
                        </div>
                    @endif
                    @if($company->tin_number)
                        <div class="detail-row">
                            <span class="detail-label">TIN:</span>
                            <span class="detail-value">{{ $company->tin_number }}</span>
                        </div>
                    @endif
                    @if($company->email)
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value">{{ $company->email }}</span>
                        </div>
                    @endif
                    @if($company->phone)
                        <div class="detail-row">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value">{{ $company->phone }}</span>
                        </div>
                    @endif
                    @if($company->address_line_1)
                        <div class="detail-row">
                            <span class="detail-label">Address:</span>
                            <span class="detail-value">{{ $company->address_line_1 }}@if($company->address_line_2), {{ $company->address_line_2 }}@endif</span>
                        </div>
                    @endif
                    @if($company->city && $company->state)
                        <div class="detail-row">
                            <span class="detail-label">Location:</span>
                            <span class="detail-value">{{ $company->city }}, {{ $company->state->name }} {{ $company->postcode }}</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="invoice-info">
                <h2 class="invoice-title">INVOICE</h2>
                <div class="invoice-details">
                    <div class="detail-row">
                        <span class="detail-label">Invoice #:</span>
                        <span class="detail-value">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value">{{ $invoice->invoice_date->format('M d, Y') }}</span>
                    </div>
                    @if($invoice->due_date)
                    <div class="detail-row">
                        <span class="detail-label">Due Date:</span>
                        <span class="detail-value">{{ $invoice->due_date->format('M d, Y') }}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">
                            <span class="status-badge status-{{ $invoice->invoice_status }}">{{ ucfirst($invoice->invoice_status) }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Meta -->
        <div class="invoice-meta">
            @if(($settings['sections']['show_customer_details'] ?? true))
            <div class="customer-details">
                <h3 class="section-title">Bill To</h3>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">{{ $invoice->customer_name }}</span>
                </div>
                @if($invoice->customer_email)
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $invoice->customer_email }}</span>
                </div>
                @endif
                @if($invoice->customer_phone)
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">{{ $invoice->customer_phone }}</span>
                </div>
                @endif
                @if($invoice->customer_address)
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value">{{ $invoice->customer_address }}</span>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Unit Price</th>
                    @if(($settings['sections']['show_tax_breakdown'] ?? true))
                    <th class="text-center">Tax %</th>
                    @endif
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                    <td class="text-right">RM {{ number_format($item->unit_price, 2) }}</td>
                    @if(($settings['sections']['show_tax_breakdown'] ?? true))
                    <td class="text-center">{{ number_format($item->tax_rate, 1) }}%</td>
                    @endif
                    <td class="text-right">RM {{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="total-label">Subtotal:</td>
                    <td class="total-value">RM {{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                <tr>
                    <td class="total-label">Tax:</td>
                    <td class="total-value">RM {{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @endif
                @if($invoice->discount_amount > 0)
                <tr>
                    <td class="total-label">Discount:</td>
                    <td class="total-value">- RM {{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td class="total-label">Total:</td>
                    <td class="total-value">RM {{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        @if($invoice->notes && ($settings['sections']['show_notes'] ?? true))
        <div class="notes">
            <div class="notes-title">Notes:</div>
            <div>{{ $invoice->notes }}</div>
        </div>
        @endif

        <!-- Footer -->
        @if(($settings['footer']['enabled'] ?? true))
        <div class="footer">
            @if(($settings['footer']['custom_text'] ?? ''))
            <p>{{ $settings['footer']['custom_text'] }}</p>
            @endif
            @if(($settings['footer']['show_terms'] ?? true))
            <p>Payment terms: {{ ($settings['sections']['show_payment_terms'] ?? true) ? 'Net 30 days' : 'As agreed' }}</p>
            @endif
            @if(($settings['footer']['show_page_numbers'] ?? true))
            <p>Page 1 of 1</p>
            @endif
        </div>
        @endif
    </div>
</body>
</html>
