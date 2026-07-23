<?php

namespace BackOffice\Status;

use Exception;

final class BoyOrderStatus
{
    public const ACCEPTED = 1;

    public const SCANNING_STARTED = 2;

    public const INCOMPLETE_ORDER = 3;

    public const COMPLETED = 4;

    public const REVOKED = 5;

    private function __construct()
    {
        throw new Exception('Cannot instantiate BoyOrderStatus');
    }
}
