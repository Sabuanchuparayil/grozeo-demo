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
        try
        {
            $details = $this->shipper->getPartnersList($data);
            if($details['status'] == 'success')
            {
                $createSelection = OrderCourierPartnerSelections::create([
                    'courier_partner'   => config("courierpartners.{$this->partner}.local_id"),
                    'partner_id'        => $details['partner']['partner_id'],
                    'courier_name'      => $details['partner']['courier_name'],
                    'service_name'      => $details['partner']['service_name'],
                    'delivery_charge'   => $details['partner']['delivery_charge'],
                    'selected_data'     => $details['selected'],
                    'whole_response'    => $details['response']
                ]);
                $details['selection'] = @$createSelection->id;
            }
            return $details;
        }
        catch (\Exception $e)
        {
            // info("CourierPartnerSection getPartnersList ERROR => ".$e->getMessage());
            return [
                'status'    => 'failed',
                'amount'    => 0,
                'selection' => 0
            ];
        }
    }
    public function updateCourierSelection($details, $id)
    {
        OrderCourierPartnerSelections::where('id', $id)->update($details);
    }
}