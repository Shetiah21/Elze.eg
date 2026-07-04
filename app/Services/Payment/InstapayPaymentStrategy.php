<?php

namespace App\Services\Payment;

use App\Models\Order;

/**
 * InstaPay Payment Strategy (LOCAL DEVELOPMENT / MOCK MODE)
 *
 * Workflow:
 * 1. Order is created → payment_status = 'pending', no reference yet
 * 2. Customer submits transaction reference → payment_status = 'pending_verification'
 * 3. Admin reviews reference and clicks Verify → payment_status = 'paid', status = 'processing'
 *
 * TODO: To integrate with the real InstaPay API in production, replace the
 *       mock verification logic in OrderManagementService::verifyInstapayPayment()
 *       with a call to the official InstaPay Payment Gateway API endpoint.
 *       Reference: https://www.instapay.eg/api-docs (when available)
 */
class InstapayPaymentStrategy implements PaymentStrategy
{
    public function pay(Order $order, array $paymentData = []): bool
    {
        $order->payment_method = 'instapay';

        if (!empty($paymentData['reference_code'])) {
            // Customer has submitted their transaction reference number.
            // Set to 'pending_verification' — admin must verify before order is processed.
            // BUG FIX: Previously this was incorrectly set to 'paid'/'processing',
            //          bypassing the admin verification step entirely.
            $order->payment_reference = $paymentData['reference_code'];
            $order->payment_status = 'pending_verification';
            $order->status = 'pending'; // Remains pending until admin verifies
            $order->payment_date = date('Y-m-d H:i:s'); // Record submission timestamp
        } else {
            // Awaiting reference number entry — order created, customer redirected to InstaPay page
            $order->payment_status = 'pending';
            $order->status = 'pending';
        }

        return $order->save();
    }
}
