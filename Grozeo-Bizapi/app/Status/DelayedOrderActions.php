<?php
namespace App\Status;

use Exception;

final class DelayedOrderActions
{
    public const ASSIGN_RIDER_HYPERLOCAL = 1;
    public const CANCEL_ORDER_HYPERLOCAL = 2;
    public const ASSIGN_RIDER_EXPRESS = 3;
    public const CANCEL_ORDER_EXPRESS = 4;
    public const CANCEL_BOOKING_COURIER = 5;
    public const BOOK_MANUAL_COURIER = 6;
    public const SKIP_FOR_A_DAY_COURIER = 7;
    public const RETRY_BOOKING_API = 8;
    public const BOOK_MANUAL_API = 9;
    public const CANCEL_ORDER_API = 10;
    public const BOOK_MANUAL_PARCEL = 11;
    public const CANCEL_ORDER_PARCEL = 12;
    public const BOOK_MANUAL_CARGO = 13;
    public const CANCEL_ORDER_CARGO = 14;
    public const DELIVERY_COMPLETED = 15;
    public const COURIER_DELIVERY_COMPLETED = 16;
    public const PACKING_COMPLETED = 17;
    public const MERCHANT_APPROVED_CANCELLATION = 18;
    public const PACKING_STARTED = 19;
    public const DELIVERY_STARTED = 20;
    
    private function __construct()
    {
        throw new Exception('Cannot instantiate StatusCode');
    }
}
