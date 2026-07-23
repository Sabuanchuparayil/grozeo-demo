<?php
namespace App\Partners\Express\Porter;

class Porter
{
    public function __construct() {}

    public function checkDeliveryPartner($params){}
    public function bookShipment($params){}
    public function cancelShipment($params){}
    public function trackShipment($params){}
    public function webhook($request){}
}