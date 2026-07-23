<?php

namespace App\Status;

use Exception;

final class DriverDeliveryStatus
{
    public const WAITING_FOR_DESPATCH = 0;

    public const DESPATCHED_IN_TRANSIT = 1;

    public const DAMAGED_WHILE_TRANSIT = 2;

    public const AWAITING_SALES_TAX_CLEARANCE = 3;

    public const INCOMPLETE_RECEIPT = 4;

    public const ITEM_NOT_RECEIVED = 5;

    public const ITEM_PARTLY_RECEIVED = 6;

    public const FAILED_IN_TRANSIT = 7;

    public const RECEIVED_IN_GODOWN = 8;

    public const SEND_FOR_DOOR_DELIVERY = 9;

    public const DELIVERY_FAILED_DOOR_LOCKED = 10;

    public const DELIVERY_FAILED_REFUSED = 11;

    public const DELIVERY_FAILED_ADDRESS_NOT_FOUND = 12;

    public const INCOMPLETE_DELIVERY = 13;

    public const DELIVERY_FAILED_DAMAGED = 14;

    public const DELIVERY_COMPLETED = 15;

    public const RE_ROUTED = 16;

    public const RE_BOOKED = 17;

    public const ARBITRATED = 18;

    public const PARTIAL_DISPATCH = 19;

    public const PARTIAL_RECEIVE = 20;

    public const PARTIAL_DELIVERY = 21;

    public const PICKUP_AT_ORIGIN = 22;

    public const PICKUP_POLLED = 23;

    public const PICKUP_POLL_REJECTED = 24;

    public const PICKUP_POLL_NO_RESPONSE = 25;

    public const PICKUP_HOME_BRANCH_FLAGGED = 26;

    public const PICKUP_DIRECT_DELIVERY_FLAGGED = 27;

    public const PICKUP_HOME_BRANCH_PICKED_UP = 28;

    public const PICKUP_DIRECT_DELIVERY_PICKED_UP = 29;

    public const PICKUP_BOOKING_CANCELLED = 30;

    public const PICKED_UP_WAITING_FOR_ASSIGNMENT = 31;

    public const DELIVERY_POLLED = 32;

    public const DELIVERY_POLL_REJECTED = 33;

    public const DELIVERY_POLL_NO_RESPONSE = 34;

    public const PICKUP_FAILED_DOOR_LOCKED = 35;

    public const PICKUP_FAILED_ADDRESS_NOT_FOUND = 36;

    public const PICKUP_FAILED_ITEM_NOT_READY = 37;

    public const DELIVERY_DELIVERED_BUT_NOT_CONFIRMED = 38;

    public const DESPATCHED_VIA_CROSS_BOOKING = 39;

    public const CANCELLED = 40;


    private function __construct()
    {
        throw new Exception('Cannot instantiate StatusCode');
    }

}
