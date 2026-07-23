<?php
namespace App\CourierPartners;

use App\Models\CourierDelivery\OrderCourierPartnerSelections;

class CourierPartnerSection
{
    protected $partner;
    protected $shipper;
    
	function __construct()
    {
        $this->partner = config('courierpartners.default');
        $shipping = $this->partner ? config("courierpartners.{$this->partner}.sClass") : null;
        $this->shipper = $shipping ? new $shipping() : null;
    }

    public function getPartnersList($data)
    {
        $details = $this->shipper->getPartnersList($data);
        if($details['status'] == 'success')
        {
            $createSelection = OrderCourierPartnerSelections::create([
                'courier_partner'   => config("courierpartners.{$this->partner}.local_id"),
                'partner_id'        => $details['partner']['partner_id'],
                'courier_name'      => $details['partner']['courier_name'],
                'service_name'      => $details['partner']['service_name'],
                'delivery_charge'   => $details['partner']['delivery_charge']
            ]);
            $details['selection'] = @$createSelection->id;
        }
        return $details;
    }
    public function updateCourierSelection($details, $id)
    {
        OrderCourierPartnerSelections::where('id', $id)->update($details);
    }
}