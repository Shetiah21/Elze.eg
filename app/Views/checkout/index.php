<?php
$base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '/Elze.eg/public';
?>

<div class="checkout-page-wrapper">
    <div class="container">
        
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="<?= $base ?>/">Home</a>
            <span>›</span>
            <a href="<?= $base ?>/cart">Shopping Cart</a>
            <span>›</span>
            <span>Checkout</span>
        </nav>

        <h1 class="cart-title">Secure Checkout</h1>

        <form action="<?= $base ?>/checkout" method="POST" id="checkout-form" class="cart-layout">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <!-- Left Side: Shipping Address & Payment Selection -->
            <div class="cart-items-column">
                
                <!-- 1. Shipping Address Selection -->
                <div class="summary-card" style="margin-bottom: 24px;">
                    <h3 class="summary-title" style="border-bottom: 1px solid var(--color-grey-border); padding-bottom: 12px; margin-bottom: 20px;">1. Shipping Address</h3>
                    
                    <?php if (!empty($addresses)): ?>
                        <div class="address-options" style="margin-bottom: 20px; display: flex; flex-direction: column; gap: 12px;">
                            <?php foreach ($addresses as $index => $addr): ?>
                                <label class="address-radio-label" style="display: flex; gap: 12px; padding: 16px; border: 1px solid var(--color-grey-border); border-radius: var(--border-radius-md); cursor: pointer; transition: var(--transition-smooth); background-color: var(--color-white);" id="label-addr-<?= $addr['id'] ?>">
                                    <input type="radio" name="address_option" value="saved" <?= $index === 0 ? 'checked' : '' ?> data-id="<?= $addr['id'] ?>" data-gov="<?= htmlspecialchars($addr['governorate']) ?>" onclick="selectAddressOption('saved', <?= $addr['id'] ?>, '<?= htmlspecialchars($addr['governorate']) ?>')" style="margin-top: 4px;">
                                    <div style="font-size: 14px;">
                                        <strong><?= htmlspecialchars($addr['recipient_name']) ?></strong> (<?= htmlspecialchars($addr['phone_number']) ?>)<br>
                                        <span style="color: var(--color-charcoal-light);">
                                            <?= htmlspecialchars($addr['street_address']) ?><?= !empty($addr['building_details']) ? ', ' . htmlspecialchars($addr['building_details']) : '' ?><br>
                                            <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['governorate']) ?>
                                        </span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                            
                            <label class="address-radio-label" style="display: flex; gap: 12px; padding: 16px; border: 1px solid var(--color-grey-border); border-radius: var(--border-radius-md); cursor: pointer; transition: var(--transition-smooth); background-color: var(--color-white);" id="label-addr-new">
                                    <input type="radio" name="address_option" value="new" onclick="selectAddressOption('new')" style="margin-top: 4px;">
                                    <div>
                                        <strong>Use a different shipping address</strong>
                                    </div>
                            </label>
                        </div>
                    <?php else: ?>
                        <!-- Force new address selection -->
                        <input type="hidden" name="address_option" value="new">
                    <?php endif; ?>

                    <!-- New Address Form Details -->
                    <div id="new-address-form" style="<?= !empty($addresses) ? 'display: none;' : '' ?> border-top: 1px dashed var(--color-grey-border); padding-top: 20px;">
                        <h4 style="font-family: var(--font-headers); color: var(--color-brand-blue); margin-bottom: 16px; font-size: 15px;">New Shipping Details</h4>
                        
                        <div class="form-group">
                            <label for="new_recipient_name">Recipient Name *</label>
                            <input type="text" id="new_recipient_name" name="new_recipient_name" class="form-control" placeholder="Full name of recipient">
                        </div>

                        <div class="form-group-row">
                            <div class="form-group">
                                <label for="new_phone_number">Phone Number *</label>
                                <input type="tel" id="new_phone_number" name="new_phone_number" class="form-control" placeholder="e.g. 01001234567" pattern="^01[0125][0-9]{8}$">
                            </div>

                            <div class="form-group">
                                <label for="new_governorate">Governorate *</label>
                                <select id="new_governorate" name="new_governorate" class="form-control" onchange="onNewGovChange(this.value)">
                                    <option value="" disabled selected>Select Governorate</option>
                                    <?php foreach ($governorates as $gov): ?>
                                        <option value="<?= htmlspecialchars($gov) ?>"><?= htmlspecialchars($gov) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_city">City *</label>
                            <input type="text" id="new_city" name="new_city" class="form-control" placeholder="e.g. Maadi, Heliopolis">
                        </div>

                        <div class="form-group">
                            <label for="new_street_address">Street Address *</label>
                            <input type="text" id="new_street_address" name="new_street_address" class="form-control" placeholder="e.g. 12 Street name">
                        </div>

                        <div class="form-group">
                            <label for="new_building_details">Building, Floor, Flat Details (Optional)</label>
                            <input type="text" id="new_building_details" name="new_building_details" class="form-control" placeholder="e.g. Building 12, Floor 4, Flat 6">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-checkbox-label">
                                <input type="checkbox" name="save_address" value="1">
                                <span>Save address to address book</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 2. Payment Strategy Selection -->
                <div class="summary-card">
                    <h3 class="summary-title" style="border-bottom: 1px solid var(--color-grey-border); padding-bottom: 12px; margin-bottom: 20px;">2. Payment Method</h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <label class="address-radio-label" style="display: flex; gap: 12px; padding: 16px; border: 2px solid var(--color-brand-blue); border-radius: var(--border-radius-md); cursor: pointer;" id="payment-label-cod">
                            <input type="radio" name="payment_method" value="cod" checked onclick="selectPaymentMethod('cod')" style="margin-top: 4px;">
                            <div>
                                <strong>Cash on Delivery (COD)</strong>
                                <p style="font-size: 12px; color: var(--color-charcoal-light); margin-top: 2px;">Pay with cash upon physical shipment arrival.</p>
                            </div>
                        </label>

                        <label class="address-radio-label" style="display: flex; gap: 12px; padding: 16px; border: 1px solid var(--color-grey-border); border-radius: var(--border-radius-md); cursor: pointer;" id="payment-label-instapay">
                            <input type="radio" name="payment_method" value="instapay" onclick="selectPaymentMethod('instapay')" style="margin-top: 4px;">
                            <div>
                                <strong>InstaPay Bank Transfer</strong>
                                <p style="font-size: 12px; color: var(--color-charcoal-light); margin-top: 2px;">Instant transfer via InstaPay App. Requires entering a valid transaction reference.</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Right Side: Order Summary & Placement -->
            <div class="cart-summary-column">
                
                <!-- Order Items Mini Cards -->
                <div class="summary-card" style="margin-bottom: 24px; padding: 16px;">
                    <h4 style="font-family: var(--font-headers); color: var(--color-brand-blue); font-size: 15px; border-bottom: 1px solid var(--color-grey-border); padding-bottom: 8px; margin-bottom: 12px;">Items Summary</h4>
                    <div style="max-height: 180px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                        <?php foreach ($cartItems as $item): ?>
                            <div style="display: flex; gap: 8px; font-size: 13px; justify-content: space-between; align-items: center;">
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <?php if ($item['primary_image']): ?>
                                        <img src="<?= $base . htmlspecialchars($item['primary_image']) ?>" style="width: 36px; height: 45px; object-fit: cover; border-radius: 2px;">
                                    <?php endif; ?>
                                    <div>
                                        <strong style="color: var(--color-brand-blue);"><?= htmlspecialchars($item['product_name']) ?></strong><br>
                                        <span style="font-size: 11px; color: var(--color-charcoal-light);"><?= htmlspecialchars($item['size']) ?> / <?= htmlspecialchars($item['color']) ?> x <?= $item['quantity'] ?></span>
                                    </div>
                                </div>
                                <strong><?= number_format($item['subtotal'], 0) ?> EGP</strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Price Summary -->
                <div class="summary-card">
                    <h3 class="summary-title">Summary totals</h3>
                    
                    <div class="summary-rows">
                        <div class="summary-row">
                            <span class="row-label">Items Subtotal</span>
                            <span class="row-value" id="summary-subtotal" data-val="<?= $subtotal ?>"><?= number_format($subtotal, 0) ?> EGP</span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="row-label">Shipping Rate</span>
                            <span class="row-value" id="summary-shipping" data-val="<?= $shipping_fee ?>"><?= number_format($shipping_fee, 0) ?> EGP</span>
                        </div>

                        <div class="summary-row" id="coupon-row" style="display: none; color: var(--color-success);">
                            <span class="row-label" style="color: var(--color-success);">Coupon Discount</span>
                            <span class="row-value" id="summary-discount" data-val="0">-0 EGP</span>
                        </div>

                        <div class="summary-row tax-row">
                            <span class="row-label">Estimated 14% VAT <small style="display: block; font-size: 10px; color: var(--color-charcoal-light); font-weight: normal;">(Included)</small></span>
                            <span class="row-value"><?= number_format($tax, 0) ?> EGP</span>
                        </div>
                        
                        <hr class="summary-divider">

                        <div class="summary-row total-row">
                            <span class="row-label">Total to Pay</span>
                            <span class="row-value" id="summary-total"><?= number_format($subtotal + $shipping_fee, 0) ?> EGP</span>
                        </div>
                    </div>

                    <!-- Coupon Code Input -->
                    <div style="margin-top: 24px; border-top: 1px solid var(--color-grey-border); padding-top: 16px;">
                        <label for="coupon_input" style="font-size: 12px; font-weight: 600; color: var(--color-charcoal-light); display: block; margin-bottom: 6px;">Have a Promo Code?</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="text" id="coupon_input" class="form-control" placeholder="e.g. ELZE10" style="padding: 6px 10px; font-size: 13px; text-transform: uppercase;">
                            <button type="button" class="btn" onclick="applyCoupon()" style="padding: 6px 12px; font-size: 13px; background-color: var(--color-brand-blue); color: var(--color-white); border: none;">Apply</button>
                        </div>
                        <span id="coupon-message" style="display: none; font-size: 11px; margin-top: 4px; font-weight: 500;"></span>
                        <input type="hidden" name="coupon_code" id="coupon_code_field" value="">
                    </div>

                    <!-- Notes Input -->
                    <div style="margin-top: 16px;">
                        <label for="notes" style="font-size: 12px; font-weight: 600; color: var(--color-charcoal-light); display: block; margin-bottom: 6px;">Order Notes (Optional)</label>
                        <textarea id="notes" name="notes" class="form-control" placeholder="Special delivery instructions, flat number..." style="height: 60px; font-size: 13px; resize: none;"></textarea>
                    </div>

                    <div class="summary-actions" style="margin-top: 24px;">
                        <button type="submit" class="btn btn-primary btn-block checkout-btn" style="padding: 16px;">
                            Place Order
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Governorate shipping rates configuration matrix
const SHIPPING_RATES = {
    cairoGiza: ['Cairo', 'Giza'],
    deltaCanal: ['Alexandria', 'Beheira', 'Gharbia', 'Kafr El Sheikh', 'Dakahlia', 'Damietta', 'Qalyubia', 'Sharqia', 'Monufia', 'Ismailia', 'Port Said', 'Suez'],
    upperEgypt: ['Fayoum', 'Beni Suef', 'Minya', 'Assiut', 'Sohag', 'Qena', 'Luxor', 'Aswan']
};

function getShippingFeeForGov(gov) {
    if (!gov) return 50.0;
    if (SHIPPING_RATES.cairoGiza.includes(gov)) {
        return 50.0;
    } else if (SHIPPING_RATES.deltaCanal.includes(gov)) {
        return 65.0;
    } else if (SHIPPING_RATES.upperEgypt.includes(gov)) {
        return 80.0;
    } else {
        return 100.0; // Frontier / Sinai / Red Sea / Matrouh
    }
}

// Current pricing states
let cartSubtotal = parseFloat(document.getElementById('summary-subtotal').dataset.val);
let shippingRate = parseFloat(document.getElementById('summary-shipping').dataset.val);
let discountAmt  = 0.0;

function selectAddressOption(option, id = null, gov = null) {
    const radioLabels = document.querySelectorAll('.address-radio-label');
    radioLabels.forEach(lbl => {
        lbl.style.borderColor = 'var(--color-grey-border)';
        lbl.style.backgroundColor = 'var(--color-white)';
    });

    const activeLabel = document.getElementById(option === 'saved' ? 'label-addr-' + id : 'label-addr-new');
    if (activeLabel) {
        activeLabel.style.borderColor = 'var(--color-brand-blue)';
        activeLabel.style.backgroundColor = 'rgba(10, 9, 51, 0.01)';
    }

    const form = document.getElementById('new-address-form');
    
    if (option === 'saved') {
        form.style.display = 'none';
        
        // Remove 'required' tags
        document.getElementById('new_recipient_name').required = false;
        document.getElementById('new_phone_number').required = false;
        document.getElementById('new_governorate').required = false;
        document.getElementById('new_city').required = false;
        document.getElementById('new_street_address').required = false;

        // Recalculate shipping rate
        shippingRate = getShippingFeeForGov(gov);
    } else {
        form.style.display = 'block';

        // Add 'required' tags
        document.getElementById('new_recipient_name').required = true;
        document.getElementById('new_phone_number').required = true;
        document.getElementById('new_governorate').required = true;
        document.getElementById('new_city').required = true;
        document.getElementById('new_street_address').required = true;

        // Recalculate shipping rate based on the dropdown choice
        const newGovSelect = document.getElementById('new_governorate');
        shippingRate = getShippingFeeForGov(newGovSelect.value);
    }

    updateInvoiceTotals();
}

function onNewGovChange(govValue) {
    shippingRate = getShippingFeeForGov(govValue);
    updateInvoiceTotals();
}

function selectPaymentMethod(method) {
    // Reset borders
    const labels = [
        document.getElementById('payment-label-cod'),
        document.getElementById('payment-label-instapay')
    ];
    labels.forEach(lbl => {
        if (lbl) {
            lbl.style.borderColor = 'var(--color-grey-border)';
        }
    });

    const activeLabel = document.getElementById('payment-label-' + method);
    if (activeLabel) {
        activeLabel.style.borderColor = 'var(--color-brand-blue)';
    }
}

function updateInvoiceTotals() {
    // Update labels
    document.getElementById('summary-shipping').textContent = shippingRate.toFixed(0) + ' EGP';
    document.getElementById('summary-shipping').dataset.val = shippingRate;
    
    // Grand Total calculation
    const grandTotal = Math.max(0, cartSubtotal + shippingRate - discountAmt);
    document.getElementById('summary-total').textContent = grandTotal.toFixed(0) + ' EGP';
}

function applyCoupon() {
    const input = document.getElementById('coupon_input');
    const msg = document.getElementById('coupon-message');
    const code = input.value.trim().toUpperCase();

    if (!code) {
        msg.textContent = 'Please enter a coupon code.';
        msg.style.color = 'var(--color-danger)';
        msg.style.display = 'block';
        return;
    }

    fetch('<?= $base ?>/coupon/validate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            coupon_code: code,
            subtotal: cartSubtotal
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            discountAmt = parseFloat(data.discount_amount);
            
            // Show discount row
            document.getElementById('coupon-row').style.display = 'flex';
            document.getElementById('summary-discount').textContent = '-' + data.discount_formatted;
            document.getElementById('summary-discount').dataset.val = discountAmt;
            
            // Set hidden field
            document.getElementById('coupon_code_field').value = data.coupon_code;

            // Update success messages
            msg.textContent = 'Promo code applied successfully!';
            msg.style.color = 'var(--color-success)';
            msg.style.display = 'block';
            
            updateInvoiceTotals();
        } else {
            // Error
            discountAmt = 0.0;
            document.getElementById('coupon-row').style.display = 'none';
            document.getElementById('coupon_code_field').value = '';
            
            msg.textContent = data.error || 'Invalid promo code.';
            msg.style.color = 'var(--color-danger)';
            msg.style.display = 'block';
            
            updateInvoiceTotals();
        }
    })
    .catch(err => {
        console.error('Coupon error:', err);
    });
}

// ── Prevent checkout double-submission ──
(function() {
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function() {
            const btn = checkoutForm.querySelector('.checkout-btn');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Processing Order...';
                btn.style.opacity = '0.7';
                btn.style.cursor = 'not-allowed';
            }
        });
    }
})();
</script>
