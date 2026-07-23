<?php

namespace App\Status;

use Exception;

final class InventoryStatus
{
    public const GODOWN_AVAILABLE = 1;
    
    public const GODOWN_MARKED_OUTWARD = 2;

    public const IN_TRANSIT_BRANCH = 3;

    public const INWARD_ITEM_SCANNED = 4;

    public const IN_DELIVERY_CART = 5;

    public const OUT_FOR_DELIVERY = 6;

    public const DELIVERED = 7;

    public const DAMAGED_AND_IN_BRANCH = 8;

    public const DAMAGED_RETURNING_TO_CPD = 9;

    public const BANNED_AND_IN_BRANCH = 13;

    public const BANNED_RETURNING_TO_CPD = 14;

    private function __construct()
    {
        throw new Exception('Cannot instantiate InventoryStatus');
    }

}
