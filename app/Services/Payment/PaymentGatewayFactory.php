<?php

namespace App\Services\Payment;

use Exception;

class PaymentGatewayFactory
{
    /**
     * Create and return the correct PaymentStrategy instance
     * 
     * @param string $method
     * @return PaymentStrategy
     * @throws Exception
     */
    public static function create(string $method): PaymentStrategy
    {
        $normalized = strtolower(trim($method));
        
        switch ($normalized) {
            case 'cod':
                return new CodPaymentStrategy();
            case 'instapay':
                return new InstapayPaymentStrategy();
            default:
                throw new Exception("Unsupported payment method: '{$method}'");
        }
    }
}
