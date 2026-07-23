<?php

namespace BackOffice\Status;

use Exception;

final class OrderType
{
    public const CPD_ORDER = 0;

    public const CUSTOMER_ORDER = 1;

    public const B2B_ORDER = 2;

    public const STOCK_RETURN = 3;

    private function __construct()
    {
        throw new Exception('Cannot instantiate OrderType');
    }
}
