<?php

namespace App\Services\Payment;

use App\Models\Order;

class InstapayPaymentStrategy implements PaymentStrategy
{
    public function pay(Order $order, array $paymentData = []): bool
    {
        $order->payment_method = 'instapay';
        
        if (!empty($paymentData['reference_code'])) {
            // Save the transaction reference and update status
            $order->payment_reference = $paymentData['reference_code'];
            $order->payment_status = 'paid';
            $order->status = 'processing';
        } else {
            // Awaiting reference number entry
            $order->payment_status = 'pending';
            $order->status = 'pending';
        }
        
        return $order->save();
    }
}
