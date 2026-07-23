<?php

namespace App\Modules;

use App\Models\Order;
use App\Models\OrderNumbering;
use App\Models\OrderGrouping;

class CreateOrderId {

    private $order;

    public function __construct() {
        $this->order = new Order;
    }

    /**
     * Static function to generate a unique order Id.
     */
    public static function generate() {
        return (new static)->generateId();
    }

    public static function generateGrId() {
        return (new static)->generateGroupId();
    }

    /**
     * Generate a unique refferal code.
     *
     * @return string
     */
    public function generateGroupId() {
        return $this->getDate() .
                $this->addPaddingZerosGrp(
                        $this->getGrLastNumber()
        );
    }
    public function generateId() {
        return $this->getDate() .
                $this->addPaddingZeros(
                        $this->getLastNumber()
        );
    }

    /**
     * Get the last inserted number from db.
     *
     * @return int
     */
    public function getLastNumber() {
        /* $latest = $this->order->latest('order_id')->whereDate('created_at', today())->first();
        return $latest ?
                (int) substr($latest->order_order_id, 6, 9) + 1 :
                1; */
        $numbering = OrderNumbering::create();
        return $numbering ? $numbering->id : 1;
    }
    public function getGrLastNumber() {
       /*  $latest = $this->order->latest('order_id')->whereDate('created_at', today())->first();
        return $latest ?
                (int) substr($latest->order_group_id, 6, 9) + 1 :
                1; */
        $grouping = OrderGrouping::create();
        return $grouping ? $grouping->id : 1;
    }

    /**
     * Add padding zeros.
     *
     * @param string $refCode
     * @return string
     */
    public function addPaddingZeros($refCode) {
        return str_pad($refCode, 4, '0', STR_PAD_LEFT);
    }
    public function addPaddingZerosGrp($refCode) {
        return str_pad($refCode, 5, '0', STR_PAD_LEFT);
    }

    public function getDate() {
        return now()->format('ymd');
    }

}
