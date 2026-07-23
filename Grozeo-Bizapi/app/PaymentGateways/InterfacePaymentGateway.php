<?php

namespace App\PaymentGateways;

interface InterfacePaymentGateway
{
    public function processPayment(array $data);
    public function paymentComplete(array $data,$compid);
}
