<?php

namespace App\Services\Payment;

use App\Models\Order;

interface PaymentStrategy
{
    /**
     * Process order payment
     * 
     * @param Order $order
     * @param array $paymentData Additional data (like transactions reference)
     * @return bool
     */
    public function pay(Order $order, array $paymentData = []): bool;
}
