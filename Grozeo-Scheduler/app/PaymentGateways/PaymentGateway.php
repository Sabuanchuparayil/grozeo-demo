<?php

namespace App\PaymentGateways;

interface PaymentGateway
{
    public function processPayment(array $data);
}