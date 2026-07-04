<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TAX INVOICE #<?= htmlspecialchars($order['order_number']) ?></title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --color-brand: #0A0933;
            --color-text: #1C1C1E;
            --color-muted: #48484A;
            --color-border: #E5E5EA;
            --color-light-bg: #FAF9F6;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--color-text);
            background-color: #ffffff;
            line-height: 1.5;
            padding: 40px;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid var(--color-border);
            padding: 40px;
            border-radius: 8px;
            position: relative;
        }

        .print-btn-bar {
            max-width: 800px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            font-size: 13px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: 1px solid var(--color-border);
        }

        .btn-print {
            background-color: var(--color-brand);
            color: #ffffff;
            border-color: var(--color-brand);
        }

        .btn-print:hover {
            opacity: 0.9;
        }

        .btn-close {
            background-color: var(--color-light-bg);
            color: var(--color-text);
        }

        .btn-close:hover {
            background-color: var(--color-border);
        }

        /* Invoice Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 2px solid var(--color-brand);
            padding-bottom: 24px;
        }

        .brand-logo-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 36px;
            color: var(--color-brand);
            text-transform: lowercase;
            letter-spacing: -1.5px;
            font-style: italic;
            line-height: 1;
            margin-bottom: 8px;
        }

        .company-details {
            font-size: 12px;
            color: var(--color-muted);
            line-height: 1.4;
        }

        .invoice-title-block {
            text-align: right;
        }

        .invoice-title {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: var(--color-brand);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .invoice-meta {
            font-size: 13px;
            line-height: 1.5;
        }

        .invoice-meta strong {
            color: var(--color-brand);
        }

        /* Billing / Shipping section */
        .invoice-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--color-brand);
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--color-border);
            padding-bottom: 6px;
            margin-bottom: 12px;
        }

        .billing-info {
            font-size: 13px;
            line-height: 1.6;
        }

        .billing-info strong {
            font-size: 14px;
            color: #000000;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            font-size: 13px;
        }

        .items-table th {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--color-brand);
            border-bottom: 2px solid var(--color-border);
            padding: 10px;
            text-align: left;
        }

        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--color-border);
        }

        .items-table tr:last-child td {
            border-bottom: 2px solid var(--color-border);
        }

        /* Totals Block */
        .totals-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .payment-notes {
            width: 50%;
            font-size: 11px;
            color: var(--color-muted);
            line-height: 1.5;
        }

        .payment-notes strong {
            color: var(--color-text);
            display: block;
            margin-bottom: 4px;
        }

        .totals-table {
            width: 40%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .totals-table td {
            padding: 8px 10px;
            text-align: right;
        }

        .totals-table tr.grand-total {
            font-size: 16px;
            font-weight: 700;
            color: var(--color-brand);
            border-top: 1px solid var(--color-brand);
        }

        .invoice-footer {
            margin-top: 60px;
            border-top: 1px solid var(--color-border);
            padding-top: 20px;
            text-align: center;
            font-size: 11px;
            color: var(--color-muted);
        }

        /* Print Media Override */
        @media print {
            body {
                padding: 0;
            }
            .invoice-container {
                border: none;
                padding: 0;
            }
            .print-btn-bar {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Actions bar for viewing in browser -->
    <div class="print-btn-bar">
        <button class="btn btn-close" onclick="window.close()">Close Window</button>
        <button class="btn btn-print" onclick="window.print()">Print Invoice 🖨️</button>
    </div>

    <div class="invoice-container">
        
        <!-- Header -->
        <header class="invoice-header">
            <div>
                <div class="brand-logo-text">elze</div>
                <div class="company-details">
                    Elze.eg Apparel Co.<br>
                    15 Road 9, Maadi, Cairo, Egypt<br>
                    Reg. Tax Number: 729-103-847<br>
                    support@elze.eg | www.elze.eg
                </div>
            </div>
            
            <div class="invoice-title-block">
                <div class="invoice-title">Tax Invoice</div>
                <div class="invoice-meta">
                    Order Number: <strong><?= htmlspecialchars($order['order_number']) ?></strong><br>
                    Invoice Date: <?= date('d M Y', strtotime($order['created_at'])) ?><br>
                    Status: <?= strtoupper($order['status']) ?>
                </div>
            </div>
        </header>

        <!-- Details Grid -->
        <div class="invoice-details-grid">
            <div>
                <div class="section-title">Shipping Address (Bill To)</div>
                <div class="billing-info">
                    <?php if ($address): ?>
                        <strong><?= htmlspecialchars($address['recipient_name']) ?></strong><br>
                        Phone: <?= htmlspecialchars($address['phone_number']) ?><br>
                        Street: <?= htmlspecialchars($address['street_address']) ?><br>
                        <?php if (!empty($address['building_details'])): ?>
                            Details: <?= htmlspecialchars($address['building_details']) ?><br>
                        <?php endif; ?>
                        Location: <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['governorate']) ?><br>
                        Egypt
                    <?php else: ?>
                        <em>Shipping address info missing.</em>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="section-title">Payment Information</div>
                <div class="billing-info">
                    Customer Name: <?= htmlspecialchars($user['name']) ?><br>
                    Customer Email: <?= htmlspecialchars($user['email']) ?><br>
                    Payment Strategy: <strong><?= strtoupper($order['payment_method']) ?></strong><br>
                    Payment Status: <?= strtoupper($order['payment_status']) ?><br>
                    <?php if ($order['payment_method'] === 'instapay' && !empty($order['payment_reference'])): ?>
                        InstaPay Ref: <code><?= htmlspecialchars($order['payment_reference']) ?></code>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>Size/Color</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                        </td>
                        <td style="color: var(--color-muted);">
                            <?= htmlspecialchars($item['size']) ?> / <?= htmlspecialchars($item['color']) ?>
                        </td>
                        <td style="text-align: center;"><?= $item['quantity'] ?></td>
                        <td style="text-align: right;"><?= number_format($item['unit_price'], 2) ?> EGP</td>
                        <td style="text-align: right; font-weight: 500;"><?= number_format($item['total_price'], 2) ?> EGP</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals Block -->
        <div class="totals-wrapper">
            <div class="payment-notes">
                <strong>Egyptian VAT Compliance</strong>
                All prices shown are inclusive of Egyptian Value Added Tax (VAT) at the standard rate of 14% where applicable.<br>
                Thank you for supporting premium local Egyptian clothing brands.
            </div>

            <table class="totals-table">
                <tr>
                    <td style="color: var(--color-muted);">Subtotal</td>
                    <td style="font-weight: 500;"><?= number_format($order['subtotal'], 2) ?> EGP</td>
                </tr>
                <?php if ($order['discount_amount'] > 0): ?>
                    <tr style="color: var(--color-muted);">
                        <td>Promo Discount</td>
                        <td style="font-weight: 500; color: #2E7D32;">-<?= number_format($order['discount_amount'], 2) ?> EGP</td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td style="color: var(--color-muted);">Shipping rate</td>
                    <td style="font-weight: 500;"><?= number_format($order['shipping_fee'], 2) ?> EGP</td>
                </tr>
                <tr>
                    <td style="color: var(--color-muted); font-size: 11px;">Included 14% VAT</td>
                    <td style="font-size: 11px;"><?= number_format($order['tax_amount'], 2) ?> EGP</td>
                </tr>
                <tr class="grand-total">
                    <td>Grand Total</td>
                    <td><?= number_format($order['total_amount'], 2) ?> EGP</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <footer class="invoice-footer">
            &copy; <?= date('Y') ?> Elze.eg Apparel Co. This is an electronically generated tax invoice document. No physical signature required.
        </footer>

    </div>

</body>
</html>
