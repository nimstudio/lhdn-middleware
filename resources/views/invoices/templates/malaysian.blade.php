<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: {{ $settings['layout']['font_family'] ?? 'Arial' }}, sans-serif;
            font-size: {{ $settings['layout']['font_size'] ?? 11 }}px;
            line-height: {{ $settings['layout']['line_spacing'] ?? 1.3 }};
            color: #333;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: {{ $settings['layout']['margins']['top'] ?? 15 }}mm {{ $settings['layout']['margins']['right'] ?? 15 }}mm {{ $settings['layout']['margins']['bottom'] ?? 15 }}mm {{ $settings['layout']['margins']['left'] ?? 15 }}mm;
        }

        /* Header Section */
        .header-section {
            margin-bottom: 30px;
            border-bottom: 3px solid {{ $settings['colors']['primary'] ?? '#2E7D32' }};
            padding-bottom: 20px;
            width: 100%;
        }

        .company-info {
            width: 100%;
        }

        .logo-section {
            width: 120px;
            float: left;
            text-align: center;
        }

        .company-logo {
            max-width: 120px;
            max-height: 90px;
        }

        .company-details-section {
            overflow: hidden;
            text-align: center;
            padding: 0 10px;
        }

        .company-name {
            font-size: 21px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.2;
        }

        .company-reg {
            font-size: 11px;
            color: #666;
            margin-bottom: 8px;
            font-weight: normal;
            line-height: 1.2;
        }

        .company-details {
            font-size: 10px;
            line-height: 1.4;
            color: #555;
        }

        .document-title {
            text-align: center;
            margin: 25px 0 30px 0;
        }

        .title-text {
            font-size: 28px;
            font-weight: bold;
            color: {{ $settings['colors']['primary'] ?? '#2E7D32' }};
            letter-spacing: 1px;
        }

        /* Invoice Details */
        .invoice-details {
            width: 100%;
            margin-bottom: 25px;
            display: table;
            table-layout: fixed;
        }

        .bill-to {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }

        .bill-to .detail-row {
            clear: none;
        }

        .bill-to .detail-value {
            float: none;
            width: 100%;
            text-align: left;
        }

        .invoice-meta {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-left: 20px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: {{ $settings['colors']['primary'] ?? '#2E7D32' }};
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .detail-row {
            width: 100%;
            margin-bottom: 4px;
            font-size: 11px;
            clear: both;
        }

        .detail-label {
            font-size: 13px;
            font-weight: bold;
            color: #666;
            width: 30%;
            float: left;
        }

        .detail-value {
            font-size: 13px;
            color: #333;
            width: 70%;
            float: right;
            text-align: right;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10px;
        }

        .items-table th {
            background: {{ $settings['colors']['primary'] ?? '#2E7D32' }};
            color: white;
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #ddd;
        }

        .items-table td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .items-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .items-table .text-left {
            text-align: left;
        }

        .items-table .text-right {
            text-align: right;
        }

        /* Summary Section */
        .summary-section {
            width: 100%;
            margin: 20px 0;
        }

        .summary-table {
            width: 300px;
            border-collapse: collapse;
            float: right;
        }

        .summary-table td {
            padding: 6px 12px;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        .summary-table .summary-label {
            background: #f5f5f5;
            font-weight: bold;
            text-align: left;
        }

        .summary-table .summary-value {
            text-align: right;
            font-weight: bold;
        }

        .grand-total {
            font-size: 13px;
            font-weight: bold;
        }

        .grand-total .summary-label {
            background: #f5f5f5;
            color: #333;
        }

        .grand-total .summary-value {
            background: #f5f5f5;
            color: #333;
        }

        /* Amount in Words */
        .amount-words {
            margin: 20px 0;
            padding: 10px;
            background: #f8f9fa;
            border-left: 4px solid {{ $settings['colors']['primary'] ?? '#2E7D32' }};
        }

        .amount-words-label {
            font-weight: bold;
            font-size: 11px;
            color: #333;
            margin-bottom: 5px;
        }

        .amount-words-text {
            font-size: 11px;
            color: #555;
            font-style: italic;
        }

        /* Footer Section */
        .footer-section {
            margin-top: 30px;
            width: 100%;
            display: table;
            table-layout: fixed;
            clear: both;
        }

        .payment-info {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
        }

        .payment-info .section-title {
            margin-bottom: 10px;
        }

        .payment-info .detail-row {
            clear: none;
            margin-bottom: 8px;
        }

        .payment-info .detail-value {
            float: none;
            width: 100%;
            text-align: left;
            font-size: 11px;
            line-height: 1.5;
        }

        .signature-section {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: bottom;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            width: 200px;
            margin: 0 auto 10px;
            height: 60px;
        }

        .signature-label {
            font-size: 11px;
            color: #333;
            font-weight: normal;
        }

        .notes {
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 10px;
            color: #555;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        /* Status Text */
        .status-text {
            font-size: 11px;
            font-weight: normal;
            text-transform: capitalize;
        }

        @media print {
            .container {
                padding: 0;
            }
        }

        /* PDF-specific optimizations */
        .header-section {
            page-break-inside: avoid;
        }

        .invoice-details {
            page-break-inside: avoid;
        }

        .items-table {
            page-break-inside: avoid;
        }

        .summary-section {
            page-break-inside: avoid;
        }

        /* Ensure proper spacing in PDF */
        .detail-row {
            margin-bottom: 6px !important;
        }

    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="company-info">
                <div class="logo-section">
                    @if($company->hasMedia('pdf_logo'))
                        @php
                            // Get the actual file path instead of URL to avoid redirect loops
                            $logoMedia = $company->getFirstMedia('pdf_logo');
                            $logoPath = $logoMedia ? $logoMedia->getPath() : null;

                            // Convert image to base64 for PDF embedding
                            $logoBase64 = null;
                            if ($logoPath && file_exists($logoPath)) {
                                $imageData = base64_encode(file_get_contents($logoPath));
                                $mimeType = mime_content_type($logoPath);
                                $logoBase64 = "data:{$mimeType};base64,{$imageData}";
                            }
                        @endphp
                        @if($logoBase64)
                            <img src="{{ $logoBase64 }}"
                                 alt="{{ $company->name }}"
                                 class="company-logo">
                        @else
                            <div style="font-size: 11px; color: #999; text-align: center;">No Logo</div>
                        @endif
                    @else
                        <div style="font-size: 11px; color: #999; text-align: center;">No Logo</div>
                    @endif
                </div>

                <div class="company-details-section">
                    <div class="company-name">{{ strtoupper($company->name) }}</div>

                    @if($company->registration_number)
                        <div class="company-reg">({{ $company->registration_number }})</div>
                    @endif

                    <div class="company-details">
                        @if($company->address_line_1)
                            {{ $company->address_line_1 }}@if($company->address_line_2), {{ $company->address_line_2 }}@endif
                        @endif
                        @if($company->city && $company->state)
                            , {{ $company->city }}, {{ $company->state->name }} {{ $company->postcode }}
                        @endif
                        @if($company->country)
                            , {{ $company->country }}
                        @endif
                        <br>
                        @if($company->phone)
                            Tel: {{ $company->phone }}
                        @endif
                        @if($company->phone && $company->email)
                            |
                        @endif
                        @if($company->email)
                            Email: {{ $company->email }}
                        @endif
                    </div>
                </div>

            </div>
            <div style="clear: both;"></div>
        </div>

        <!-- Document Title -->
        <div class="document-title">
            <div class="title-text">Invoice</div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            @if(($settings['sections']['show_customer_details'] ?? true))
            <div class="bill-to">
                <div class="section-title">Bill To:</div>
                <div class="detail-row">
                    <div class="detail-value"><strong>{{ strtoupper($invoice->customer_name) }}</strong></div>
                </div>
                @if($invoice->customer_address)
                <div class="detail-row">
                    <div class="detail-value">{{ strtoupper($invoice->customer_address) }}</div>
                </div>
                @endif
                @if($invoice->customer_phone)
                <div class="detail-row">
                    <div class="detail-value">Tel: {{ $invoice->customer_phone }}</div>
                </div>
                @endif
                @if($invoice->customer_email)
                <div class="detail-row">
                    <div class="detail-value">Email: {{ $invoice->customer_email }}</div>
                </div>
                @endif
            </div>
            @endif

            <div class="invoice-meta">
                <div class="detail-row">
                    <div class="detail-label">No.:</div>
                    <div class="detail-value"><strong>{{ $invoice->invoice_number }}</strong></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date:</div>
                    <div class="detail-value">{{ $invoice->invoice_date->format('d/m/Y') }}</div>
                </div>
                @if($invoice->due_date)
                <div class="detail-row">
                    <div class="detail-label">Due Date:</div>
                    <div class="detail-value">{{ $invoice->due_date->format('d/m/Y') }}</div>
                </div>
                @endif
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">
                        <span class="status-text">{{ ucfirst($invoice->invoice_status) }}</span>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Page:</div>
                    <div class="detail-value">1 of 1</div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-left">Description</th>
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
                    <td class="text-left">{{ $item->description }}</td>
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

        <!-- Summary Section -->
        <div class="summary-section">
            <table class="summary-table">
                <tr>
                    <td class="summary-label">SUB TOTAL:</td>
                    <td class="summary-value">RM {{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->discount_amount > 0)
                <tr>
                    <td class="summary-label">DISCOUNT:</td>
                    <td class="summary-value">- RM {{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                @endif
                @if($invoice->tax_amount > 0)
                <tr>
                    <td class="summary-label">SST AMOUNT:</td>
                    <td class="summary-value">RM {{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td class="summary-label">ROUNDING ADJ:</td>
                    <td class="summary-value">RM 0.00</td>
                </tr>
                <tr class="grand-total">
                    <td class="summary-label">NET TOTAL:</td>
                    <td class="summary-value">RM {{ number_format($invoice->total_amount, 2) }}</td>
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

        <!-- Footer Section -->
        <div class="footer-section">
            <div class="payment-info">
                <div class="section-title">PAYMENT INSTRUCTIONS:</div>
                <div>All cheques should be crossed and made payable to {{ strtoupper($company->name) }}</div>
                <div>Bank transfer details available upon request</div>
                @if(($settings['footer']['custom_text'] ?? ''))
                <div>{{ $settings['footer']['custom_text'] }}</div>
                @endif
            </div>

            <div class="signature-section">
                <div class="signature-line"></div>
                <div class="signature-label">Authorised Signature</div>
            </div>
        </div>

        <!-- Footer -->
        @if(($settings['footer']['enabled'] ?? true))
        <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #eee; padding-top: 10px;">
            @if(($settings['footer']['show_page_numbers'] ?? true))
            <p>Page 1 of 1</p>
            @endif
        </div>
        @endif
    </div>
</body>
</html>
