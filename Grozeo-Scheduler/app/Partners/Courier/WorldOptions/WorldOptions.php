<?php
namespace App\Partners\Courier\WorldOptions;

class WorldOptions
{
    public function __construct() {}

    public function checkDeliveryPartner($params){}
    public function bookShipment($params){}
    public function cancelShipment($params){}
    public function trackShipment($params){}
    public function webhook($request){}
}