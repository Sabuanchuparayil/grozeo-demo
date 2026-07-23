<?php

namespace App\Modules\Payment;

interface InterfacePayment
{
    public function createPayment(array $data);

    public function verifyPayment(array $data);
    
}