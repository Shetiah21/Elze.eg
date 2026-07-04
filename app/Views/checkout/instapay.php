<?php
$base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '/Elze.eg/public';
?>

<div class="checkout-page-wrapper">
    <div class="container" style="max-width: 650px;">
        
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="<?= $base ?>/">Home</a>
            <span>›</span>
            <span>InstaPay Payment</span>
        </nav>

        <div class="summary-card" style="padding: 32px; text-align: center; border: 2px solid var(--color-brand-blue);">
            <div style="color: var(--color-brand-blue); margin-bottom: 16px;">
                <!-- InstaPay Mock Logo / SVG -->
                <svg viewBox="0 0 100 45" width="100" height="45" style="margin: 0 auto; display: block;">
                    <text x="50%" y="30" text-anchor="middle" font-family="'Outfit', sans-serif" font-weight="900" font-style="italic" font-size="28" fill="var(--color-brand-blue)">instapay</text>
                </svg>
            </div>
            
            <h2 style="font-family: var(--font-headers); font-size: 22px; color: var(--color-brand-blue); margin-bottom: 8px;">Order #<?= htmlspecialchars($order['order_number']) ?></h2>
            <p style="font-size: 14px; color: var(--color-charcoal-light); margin-bottom: 24px;">Please complete your instant bank transfer using the instructions below.</p>

            <div style="background-color: var(--color-alabaster); padding: 20px; border-radius: var(--border-radius-md); border: 1px solid var(--color-grey-border); margin-bottom: 24px; text-align: left;">
                <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-grey-border); padding-bottom: 10px;">
                    <span style="font-size: 13px; color: var(--color-charcoal-light);">Amount to Transfer:</span>
                    <strong style="font-size: 18px; color: var(--color-brand-blue); font-family: var(--font-headers);"><?= number_format($order['total_amount'], 2) ?> EGP</strong>
                </div>

                <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-grey-border); padding-bottom: 10px;">
                    <span style="font-size: 13px; color: var(--color-charcoal-light);">Our InstaPay IPA Address:</span>
                    <strong style="font-size: 15px; color: var(--color-brand-blue); font-family: var(--font-headers); cursor: pointer;" onclick="navigator.clipboard.writeText('elze@instapay'); alert('IPA Address copied to clipboard!');" title="Click to copy">
                        elze@instapay 📋
                    </strong>
                </div>

                <div style="text-align: center; margin-top: 24px; margin-bottom: 16px;">
                    <span style="font-size: 13px; color: var(--color-charcoal-light); display: block; margin-bottom: 12px;">Or scan QR code to pay instantly:</span>
                    
                    <!-- Dynamic simulated Instapay payment QR Code -->
                    <div style="display: inline-block; padding: 12px; background-color: var(--color-white); border: 1px solid var(--color-grey-border); border-radius: var(--border-radius-md);">
                        <svg viewBox="0 0 100 100" width="140" height="140" style="display: block;">
                            <!-- Mock QR Code Art -->
                            <rect x="0" y="0" width="100" height="100" fill="var(--color-white)"/>
                            <!-- Position Finders -->
                            <rect x="5" y="5" width="25" height="25" fill="var(--color-brand-blue)"/>
                            <rect x="10" y="10" width="15" height="15" fill="var(--color-white)"/>
                            <rect x="13" y="13" width="9" height="9" fill="var(--color-brand-blue)"/>
                            
                            <rect x="70" y="5" width="25" height="25" fill="var(--color-brand-blue)"/>
                            <rect x="75" y="10" width="15" height="15" fill="var(--color-white)"/>
                            <rect x="78" y="13" width="9" height="9" fill="var(--color-brand-blue)"/>

                            <rect x="5" y="70" width="25" height="25" fill="var(--color-brand-blue)"/>
                            <rect x="10" y="75" width="15" height="15" fill="var(--color-white)"/>
                            <rect x="13" y="78" width="9" height="9" fill="var(--color-brand-blue)"/>
                            
                            <!-- Random Data Blocks -->
                            <rect x="40" y="10" width="5" height="15" fill="var(--color-brand-blue)"/>
                            <rect x="50" y="5" width="10" height="10" fill="var(--color-brand-blue)"/>
                            <rect x="45" y="25" width="15" height="5" fill="var(--color-brand-blue)"/>
                            
                            <rect x="10" y="40" width="15" height="5" fill="var(--color-brand-blue)"/>
                            <rect x="5" y="50" width="10" height="10" fill="var(--color-brand-blue)"/>
                            <rect x="25" y="45" width="5" height="15" fill="var(--color-brand-blue)"/>

                            <rect x="40" y="40" width="20" height="20" fill="var(--color-brand-blue)"/>
                            <rect x="45" y="45" width="10" height="10" fill="var(--color-white)"/>

                            <rect x="70" y="40" width="15" height="10" fill="var(--color-brand-blue)"/>
                            <rect x="80" y="55" width="15" height="15" fill="var(--color-brand-blue)"/>
                            <rect x="75" y="80" width="20" height="15" fill="var(--color-brand-blue)"/>
                            <rect x="40" y="70" width="15" height="15" fill="var(--color-brand-blue)"/>
                            <rect x="60" y="80" width="10" height="10" fill="var(--color-brand-blue)"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Submit transaction reference form -->
            <form action="<?= $base ?>/checkout/instapay/verify/<?= $order['id'] ?>" method="POST" style="margin-top: 32px; border-top: 1px dashed var(--color-grey-border); padding-top: 24px; text-align: left;">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="form-group">
                    <label for="reference_code" style="font-weight: 600; color: var(--color-brand-blue);">InstaPay Transaction Reference Code *</label>
                    <p style="font-size: 11px; color: var(--color-charcoal-light); margin-bottom: 8px;">Enter the 6 to 12 digit transaction reference code from your bank receipt/SMS details.</p>
                    <input type="text" id="reference_code" name="reference_code" class="form-control" placeholder="e.g. 29384729103" required pattern="^[0-9]{6,12}$" title="Must be a numeric reference between 6 and 12 digits">
                </div>

                <div style="background-color: var(--color-danger-bg); border: 1px solid rgba(255, 59, 48, 0.15); border-radius: var(--border-radius-sm); padding: 12px; font-size: 12px; color: var(--color-danger); margin-bottom: 20px; line-height: 1.5;">
                    ⚠️ <strong>Important:</strong> Do not submit fake reference numbers. Our finance team verifies reference numbers with the Central Bank database before orders are shipped.
                </div>

                <div style="display: flex; gap: 12px; justify-content: space-between; align-items: center;">
                    <a href="<?= $base ?>/dashboard/orders/<?= $order['id'] ?>" class="btn btn-secondary" style="padding: 10px 16px; font-size: 13px;">Pay Later / View Order</a>
                    <button type="submit" class="btn btn-primary" style="background-color: var(--color-brand-blue); color: var(--color-white); padding: 10px 24px; font-size: 13px; border: none;">Submit Payment Code</button>
                </div>
            </form>
        </div>

    </div>
</div>
