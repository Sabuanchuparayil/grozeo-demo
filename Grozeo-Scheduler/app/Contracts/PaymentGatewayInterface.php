<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Get the details necessary for the payment.
     *
     * @param string $orderId
     * @param int $amount
     * @return array
     */
    public function getPaymentDetails($orderId, $amount);

    /**
     * Get the status of the payment
     *
     * @param array $paymentDetails
     * @return array
     */
    public function getPaymentStatus($paymentDetails);
}
