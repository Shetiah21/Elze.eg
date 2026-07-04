<?php
/**
 * InstaPay Payment Page — Elze.eg
 * 
 * LOCAL DEVELOPMENT MODE: Uses a JavaScript-generated mock QR code.
 * TODO: Replace the QR code generation with a signed URL from the official
 *       InstaPay Payment Gateway API when going live.
 *       Reference: InstaPay Merchant Integration Guide (pending official release)
 *
 * Variables received from CheckoutController::instapay():
 * @var object        $order     Order model instance
 * @var array         $instapay  Merchant config ['merchant_name', 'ipa_address', 'phone_number']
 * @var string        $csrf_token
 */
$base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '/Elze.eg/public';

// Encode QR payload — safe for JavaScript consumption
$qrPayload = 'instapay://pay?'
    . 'merchant=' . rawurlencode($instapay['merchant_name'])
    . '&ipa='     . rawurlencode($instapay['ipa_address'])
    . '&amount='  . rawurlencode(number_format($order->total_amount, 2))
    . '&order='   . rawurlencode($order->order_number);
?>

<style>
/* ── InstaPay Page Premium Styles ─────────────────────────────────────── */
.instapay-page-wrap {
    min-height: 80vh;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 40px 16px;
    background: linear-gradient(135deg, #f8f7ff 0%, #f0effe 100%);
}
.instapay-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 48px rgba(15, 12, 59, 0.10), 0 2px 12px rgba(15,12,59,0.06);
    max-width: 580px;
    width: 100%;
    overflow: hidden;
    border: 1px solid rgba(15, 12, 59, 0.08);
}
.instapay-header {
    background: var(--color-brand-blue, #0F0C3B);
    color: #fff;
    text-align: center;
    padding: 32px 24px 28px;
    position: relative;
}
.instapay-logo-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 16px;
}
.instapay-store-pill {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.22);
    border-radius: 100px;
    padding: 4px 14px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.08em;
    color: rgba(255,255,255,0.85);
}
.instapay-logo-text {
    font-family: 'Outfit', sans-serif;
    font-weight: 900;
    font-style: italic;
    font-size: 30px;
    color: #fff;
    letter-spacing: -0.5px;
}
.instapay-logo-text span {
    color: rgba(255,255,255,0.55);
}
.instapay-order-title {
    font-family: 'Outfit', sans-serif;
    font-size: 17px;
    font-weight: 600;
    color: rgba(255,255,255,0.90);
    margin: 0;
}
.instapay-amount-display {
    font-family: 'Outfit', sans-serif;
    font-size: 38px;
    font-weight: 800;
    color: #fff;
    line-height: 1.1;
    margin-top: 6px;
}
.instapay-amount-display small {
    font-size: 18px;
    font-weight: 500;
    opacity: 0.75;
}
.instapay-body {
    padding: 28px 28px 24px;
}
.instapay-steps {
    list-style: none;
    padding: 0;
    margin: 0 0 24px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.instapay-step {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-size: 14px;
    color: var(--color-charcoal, #333);
}
.instapay-step-num {
    flex-shrink: 0;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: var(--color-brand-blue, #0F0C3B);
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 1px;
}
.instapay-info-box {
    background: #f8f7ff;
    border: 1px solid rgba(15, 12, 59, 0.10);
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 24px;
}
.instapay-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(15,12,59,0.06);
}
.instapay-info-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.instapay-info-label {
    font-size: 12px;
    color: var(--color-charcoal-light, #666);
    font-weight: 500;
}
.instapay-info-value {
    font-size: 14px;
    font-weight: 700;
    color: var(--color-brand-blue, #0F0C3B);
    display: flex;
    align-items: center;
    gap: 8px;
}
.instapay-copy-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 6px;
    font-size: 12px;
    color: var(--color-brand-blue, #0F0C3B);
    opacity: 0.55;
    transition: opacity 0.2s, background 0.2s;
}
.instapay-copy-btn:hover { opacity: 1; background: rgba(15,12,59,0.07); }
.instapay-qr-section {
    text-align: center;
    padding: 20px 0;
}
.instapay-qr-title {
    font-size: 12px;
    color: var(--color-charcoal-light, #666);
    font-weight: 500;
    margin-bottom: 14px;
}
.instapay-qr-wrap {
    display: inline-block;
    padding: 14px;
    background: #fff;
    border: 1.5px solid rgba(15,12,59,0.12);
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(15,12,59,0.08);
}
#instapay-qr-canvas {
    display: block;
    border-radius: 4px;
}
.instapay-qr-note {
    font-size: 11px;
    color: var(--color-charcoal-light, #999);
    margin-top: 10px;
}
.instapay-divider {
    border: none;
    border-top: 1px dashed rgba(15,12,59,0.12);
    margin: 24px 0;
}
.instapay-ref-section {
    background: #fff;
    padding: 0 28px 28px;
}
.instapay-ref-label {
    font-family: 'Outfit', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: var(--color-brand-blue, #0F0C3B);
    margin-bottom: 4px;
}
.instapay-ref-hint {
    font-size: 12px;
    color: var(--color-charcoal-light, #888);
    margin-bottom: 12px;
}
.instapay-ref-input {
    width: 100%;
    padding: 14px 16px;
    font-size: 18px;
    font-family: 'Outfit', monospace;
    letter-spacing: 0.08em;
    border: 2px solid rgba(15,12,59,0.15);
    border-radius: 12px;
    outline: none;
    transition: border-color 0.2s;
    color: var(--color-brand-blue, #0F0C3B);
    box-sizing: border-box;
}
.instapay-ref-input:focus {
    border-color: var(--color-brand-blue, #0F0C3B);
    box-shadow: 0 0 0 3px rgba(15,12,59,0.08);
}
.instapay-warning {
    background: #fff8e1;
    border: 1px solid #ffd54f;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 12px;
    color: #7a6000;
    margin: 14px 0 20px;
    line-height: 1.5;
}
.instapay-actions {
    display: flex;
    gap: 12px;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
}
.btn-instapay-submit {
    background: var(--color-brand-blue, #0F0C3B);
    color: #fff;
    border: none;
    padding: 14px 28px;
    border-radius: 12px;
    font-family: 'Outfit', sans-serif;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-instapay-submit:hover {
    background: #1a1660;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(15,12,59,0.25);
}
.btn-instapay-submit:active { transform: translateY(0); }
.btn-instapay-submit:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    transform: none;
}
.btn-instapay-later {
    color: var(--color-charcoal-light, #888);
    font-size: 13px;
    text-decoration: none;
    padding: 8px 4px;
    transition: color 0.2s;
}
.btn-instapay-later:hover { color: var(--color-brand-blue, #0F0C3B); }
@media (max-width: 480px) {
    .instapay-body { padding: 20px 18px 16px; }
    .instapay-ref-section { padding: 0 18px 22px; }
    .instapay-amount-display { font-size: 30px; }
    .instapay-actions { flex-direction: column-reverse; align-items: stretch; }
    .btn-instapay-submit { justify-content: center; }
}
</style>

<div class="instapay-page-wrap">
    <div class="instapay-card">

        <!-- ── Header: Branding + Amount ─────────────────────────────── -->
        <div class="instapay-header">
            <div class="instapay-logo-row">
                <span class="instapay-store-pill"><?= htmlspecialchars($instapay['merchant_name']) ?></span>
                <span class="instapay-logo-text">insta<span>pay</span></span>
            </div>
            <p class="instapay-order-title">Order #<?= htmlspecialchars($order->order_number) ?></p>
            <div class="instapay-amount-display">
                <?= number_format($order->total_amount, 2) ?>
                <small>EGP</small>
            </div>
        </div>

        <!-- ── Body: Steps + Info + QR ───────────────────────────────── -->
        <div class="instapay-body">

            <!-- Payment instructions -->
            <ol class="instapay-steps">
                <li class="instapay-step">
                    <span class="instapay-step-num">1</span>
                    <span>Open your <strong>InstaPay</strong> app or internet banking app and choose <strong>Send Money</strong>.</span>
                </li>
                <li class="instapay-step">
                    <span class="instapay-step-num">2</span>
                    <span>Enter the IPA address <strong><?= htmlspecialchars($instapay['ipa_address']) ?></strong> or scan the QR code below.</span>
                </li>
                <li class="instapay-step">
                    <span class="instapay-step-num">3</span>
                    <span>Transfer exactly <strong><?= number_format($order->total_amount, 2) ?> EGP</strong> and note the order number.</span>
                </li>
                <li class="instapay-step">
                    <span class="instapay-step-num">4</span>
                    <span>Enter the <strong>Transaction Reference Number</strong> from your receipt/SMS below.</span>
                </li>
            </ol>

            <!-- Merchant info -->
            <div class="instapay-info-box">
                <div class="instapay-info-row">
                    <span class="instapay-info-label">Merchant Name</span>
                    <span class="instapay-info-value"><?= htmlspecialchars($instapay['merchant_name']) ?></span>
                </div>
                <div class="instapay-info-row">
                    <span class="instapay-info-label">InstaPay IPA Address</span>
                    <span class="instapay-info-value" id="ipa-address-val">
                        <?= htmlspecialchars($instapay['ipa_address']) ?>
                        <button type="button" class="instapay-copy-btn" onclick="copyIPA()" title="Copy IPA Address" id="copy-ipa-btn">📋</button>
                    </span>
                </div>
                <?php if (!empty($instapay['phone_number'])): ?>
                <div class="instapay-info-row">
                    <span class="instapay-info-label">Contact / Phone</span>
                    <span class="instapay-info-value"><?= htmlspecialchars($instapay['phone_number']) ?></span>
                </div>
                <?php endif; ?>
                <div class="instapay-info-row">
                    <span class="instapay-info-label">Amount to Transfer</span>
                    <span class="instapay-info-value" style="font-size: 18px;"><?= number_format($order->total_amount, 2) ?> EGP</span>
                </div>
                <div class="instapay-info-row">
                    <span class="instapay-info-label">Order Reference</span>
                    <span class="instapay-info-value"><?= htmlspecialchars($order->order_number) ?></span>
                </div>
            </div>

            <!-- Dynamic QR Code -->
            <div class="instapay-qr-section">
                <p class="instapay-qr-title">Or scan QR code to pay instantly</p>
                <div class="instapay-qr-wrap">
                    <canvas id="instapay-qr-canvas" width="180" height="180"></canvas>
                </div>
                <p class="instapay-qr-note">⚠️ Local dev mode — mock QR code. Replace with official InstaPay signed QR in production.</p>
            </div>

        </div><!-- /.instapay-body -->

        <!-- ── Reference Submission Form ─────────────────────────────── -->
        <hr class="instapay-divider">
        <div class="instapay-ref-section">
            <form
                action="<?= $base ?>/checkout/instapay/verify/<?= (int)$order->id ?>"
                method="POST"
                id="instapay-ref-form"
                autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <p class="instapay-ref-label">Enter Transaction Reference Number</p>
                <p class="instapay-ref-hint">
                    Found in your bank SMS, app receipt, or notification. 
                    Usually 6–20 alphanumeric characters.
                </p>

                <input
                    type="text"
                    id="reference_code"
                    name="reference_code"
                    class="instapay-ref-input"
                    placeholder="e.g. TXN29384729103"
                    required
                    pattern="^[A-Za-z0-9]{6,20}$"
                    title="Must be 6–20 alphanumeric characters (letters and digits only)"
                    autocomplete="off"
                    spellcheck="false"
                    maxlength="20">

                <div class="instapay-warning">
                    ⚠️ <strong>Important:</strong> Enter the exact reference from your receipt.
                    Our finance team cross-checks all references before processing your order.
                    Incorrect references will delay shipment.
                </div>

                <div class="instapay-actions">
                    <a href="<?= $base ?>/dashboard/orders/<?= (int)$order->id ?>" class="btn-instapay-later">
                        Pay later &rarr; View Order
                    </a>
                    <button type="submit" class="btn-instapay-submit" id="instapay-submit-btn">
                        <span id="btn-label">Submit Payment Reference</span>
                        <span id="btn-loading" style="display:none;">Submitting…</span>
                    </button>
                </div>
            </form>
        </div><!-- /.instapay-ref-section -->

    </div><!-- /.instapay-card -->
</div><!-- /.instapay-page-wrap -->

<script>
/**
 * LOCAL DEVELOPMENT: Lightweight inline QR code generator (no CDN dependency).
 * Generates a visually accurate QR-style pattern seeded from the payload string.
 * 
 * TODO: In production, replace this entire block with a server-generated
 *       signed QR from the InstaPay Gateway API, or use a proper QR library
 *       like qrcode.js (https://davidshimjs.github.io/qrcodejs/) pointing
 *       to a real instapay:// deep link or payment URL.
 */
(function () {
    const payload = <?= json_encode($qrPayload) ?>;
    const canvas  = document.getElementById('instapay-qr-canvas');
    if (!canvas) return;
    const ctx     = canvas.getContext('2d');
    const SIZE    = 180;
    const MODULES = 25; // grid cells
    const CELL    = SIZE / MODULES;
    const BRAND   = '#0F0C3B';

    // Seed a deterministic pseudo-random pattern from the payload
    function hashCode(str) {
        let h = 0x9f2f7d3a;
        for (let i = 0; i < str.length; i++) {
            h = Math.imul(h ^ str.charCodeAt(i), 0x9e3779b9);
            h ^= h >>> 17;
        }
        return h >>> 0;
    }

    function seededRand(seed) {
        let s = seed;
        return function () {
            s ^= s << 13; s ^= s >>> 17; s ^= s << 5;
            return (s >>> 0) / 0xFFFFFFFF;
        };
    }

    const rand = seededRand(hashCode(payload));

    // Determine which modules are dark (true = dark)
    const grid = Array.from({ length: MODULES }, (_, r) =>
        Array.from({ length: MODULES }, (__, c) => {
            // Always keep 3 finder patterns clear
            if (isFinderPattern(r, c)) return false; // handled separately
            return rand() > 0.45;
        })
    );

    function isFinderPattern(r, c) {
        const f = 7;
        return (r < f && c < f) ||          // top-left
               (r < f && c >= MODULES - f) || // top-right
               (r >= MODULES - f && c < f);   // bottom-left
    }

    // Draw background
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, SIZE, SIZE);

    // Draw data modules
    ctx.fillStyle = BRAND;
    for (let r = 0; r < MODULES; r++) {
        for (let c = 0; c < MODULES; c++) {
            if (!isFinderPattern(r, c) && grid[r][c]) {
                ctx.beginPath();
                ctx.roundRect(c * CELL + 1, r * CELL + 1, CELL - 2, CELL - 2, 1.5);
                ctx.fill();
            }
        }
    }

    // Draw finder patterns (the 3 corner squares)
    function drawFinder(x, y) {
        ctx.fillStyle = BRAND;
        ctx.beginPath(); ctx.roundRect(x, y, CELL * 7, CELL * 7, 4); ctx.fill();
        ctx.fillStyle = '#fff';
        ctx.fillRect(x + CELL, y + CELL, CELL * 5, CELL * 5);
        ctx.fillStyle = BRAND;
        ctx.beginPath(); ctx.roundRect(x + CELL * 2, y + CELL * 2, CELL * 3, CELL * 3, 2); ctx.fill();
    }

    drawFinder(0, 0);                                         // top-left
    drawFinder((MODULES - 7) * CELL, 0);                     // top-right
    drawFinder(0, (MODULES - 7) * CELL);                     // bottom-left

    // Center logo
    const lx = SIZE / 2 - 20, ly = SIZE / 2 - 12;
    ctx.fillStyle = '#fff';
    ctx.beginPath(); ctx.roundRect(lx - 4, ly - 4, 48, 32, 6); ctx.fill();
    ctx.fillStyle = BRAND;
    ctx.font = 'bold 11px Outfit, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('elze.eg', SIZE / 2, SIZE / 2 + 5);
})();

// ── Copy IPA Address ────────────────────────────────────────────
function copyIPA() {
    const ipa = <?= json_encode($instapay['ipa_address']) ?>;
    navigator.clipboard.writeText(ipa).then(() => {
        const btn = document.getElementById('copy-ipa-btn');
        btn.textContent = '✓';
        btn.style.opacity = '1';
        setTimeout(() => { btn.textContent = '📋'; btn.style.opacity = '0.55'; }, 2000);
    });
}

// ── Prevent double-submission ───────────────────────────────────
document.getElementById('instapay-ref-form')?.addEventListener('submit', function (e) {
    const btn      = document.getElementById('instapay-submit-btn');
    const label    = document.getElementById('btn-label');
    const loading  = document.getElementById('btn-loading');
    const refInput = document.getElementById('reference_code');

    // Basic client-side format validation
    if (!/^[A-Za-z0-9]{6,20}$/.test(refInput.value.trim())) {
        e.preventDefault();
        refInput.style.borderColor = '#ff3b30';
        refInput.focus();
        return;
    }
    refInput.style.borderColor = '';

    btn.disabled = true;
    label.style.display  = 'none';
    loading.style.display = 'inline';
});
</script>
