<?php
namespace App\Partners;

interface DeliveryPartnerInterface
{
    public function checkDeliveryPartner($params);
    public function bookShipment($params);
    public function cancelShipment($params);
    public function trackShipment($params);
    public function webhook($request);
}
