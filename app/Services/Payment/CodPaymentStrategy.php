<?php

namespace App\Services\Payment;

use App\Models\Order;

class CodPaymentStrategy implements PaymentStrategy
{
    public function pay(Order $order, array $paymentData = []): bool
    {
        // COD orders are processed and marked as pending payment on delivery
        $order->payment_method = 'cod';
        $order->payment_status = 'pending';
        $order->status = 'pending';
        
        return $order->save();
    }
}
