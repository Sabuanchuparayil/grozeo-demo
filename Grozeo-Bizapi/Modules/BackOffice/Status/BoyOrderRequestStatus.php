<?php

namespace BackOffice\Status;

use Exception;

final class BoyOrderRequestStatus
{
    public const REQUEST_SENT = 1;

    public const ACCEPTED = 2;

    public const REJECTED = 3;

    public const TIMED_OUT = 4;

    private function __construct()
    {
        throw new Exception('Cannot instantiate BoyOrderRequestStatus');
    }
}
