<?php

namespace App\Status;

use Exception;

final class QugeoStatus
{
    public const ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID = 0;
    public const ORDER_DELIVERY_OUT_FOR_DELIVERY = 9;
    public const ORDER_DELIVERY_FAILED_DOOR_LOCKED_DLS_ID = 10;
    public const ORDER_DELIVERY_FAILED_REFUSED_DLS_ID = 11;
    public const ORDER_DELIVERY_FAILED_ADDRESS_NOT_FOUND_DLS_ID = 12;
    public const ORDER_DELIVERY_FAILED_DAMAGED_DLS_ID = 14;
    public const ORDER_DELIVERY_COMPLETED_DLS_ID = 15;
    public const ORDER_PICKUP_AT_ORIGIN_DLS_ID = 22;
    public const ORDER_PICKUP_POLLED_DLS_ID = 23;
    public const ORDER_PICKUP_POLL_REJECTED_DLS_ID = 24;
    public const ORDER_PICKUP_POLL_NORESP_DLS_ID = 25;
    public const ORDER_PICKUP_FLAGGED_TOBR_DLS_ID = 26;
    public const ORDER_PICKUP_FLAGGED_TODST_DLS_ID = 27;
    public const ORDER_PICKUP_PICKEDUP_TOBR_DLS_ID = 28;
    public const ORDER_PICKUP_PICKEDUP_TODST_DLS_ID = 29;
    public const ORDER_PICKUP_FAILED_AT_ORIGIN_DLS_ID = 30;
    public const ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID = 31;
    public const ORDER_DELIVERY_POLLED_DLS_ID = 32;
    public const ORDER_DELIVERY_POLL_REJECTED_DLS_ID = 33;
    public const ORDER_DELIVERY_POLL_NORESP_DLS_ID = 34;
    public const ORDER_PICKUP_FAILED_DOOR_LOCKED_DLS_ID = 35;
    public const ORDER_PICKUP_FAILED_ADDRESS_NOT_FOUND_DLS_ID = 36;
    public const ORDER_PICKUP_FAILED_PARCEL_NOT_READY_DLS_ID = 37;
    public const ORDER_DELIVERY_MARKED_DLS_ID = 38;

    private function __construct()
    {
        throw new Exception('Cannot instantiate StatusCode') ;
    }

}