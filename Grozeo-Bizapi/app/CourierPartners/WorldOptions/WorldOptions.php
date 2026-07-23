<?php
namespace App\CourierPartners\WorldOptions;
use DateTime;
use App\CourierPartners\WorldOptions\{
    RateServices,
    Shipments,
    Tracking
};
use App\Http\Responses\SuccessWithData;

use Illuminate\Support\Facades\DB;
use BackOffice\Models\TransferOrder;
use App\Models\{
    Order,
    Branch
};

class WorldOptions
{
    protected $authenticationDetails;
    protected $rateServices;
    protected $shipments;
    protected $tracking;
    protected $shippingConsignment;
    protected $cancelConsignment;
    protected $consignmentTracking;

    protected $packDetails;

    public function __construct()
    {
        $this->authenticationDetails = config('courierpartners.worldoptions.auths');

        $this->rateServices = new RateServices($this->authenticationDetails);
        $this->shipments = new Shipments($this->authenticationDetails);
        $this->tracking = new Tracking($this->authenticationDetails);

        $this->packDetails = DB::table('retaline_transfer_order_pack_details');
        $this->shippingConsignment = DB::table('shipping_consignment');
        $this->cancelConsignment = DB::table('cancel_consignment');
        $this->consignmentTracking = DB::table('consignment_tracking');
    }

    public function getPartnersList($data)
    {
        $partner = $this->findRateService($data);
        if($partner['status'] == 'success')
		{
			return [
				"status"	=> "success",
				"amount"	=> $partner['data']['total'],
				"partner"	=> [
					'partner_id'		=> $partner['data']['partner_id'],
					'courier_name'		=> $partner['data']['partner_name'],
					'service_name'		=> $partner['data']['service_name'],
					'delivery_charge'	=> $partner['data']['total']
                ],
				'selected'	=> json_encode($partner['data']['selected']),
				'response'	=> json_encode($partner['data']['response'])
			];
		}
		else
		{
			return [
				"status"	=> $partner['status'],
				"amount"	=> 0
			];
		}
    }
    public function findRateService($data)
    {
        $response = $this->rateServices->findRateService($data);
        if($response['status'] == 'success')
        {
            $response['data'] = [
                "partner_id"    => $response['data']->wsServiceCode,
                "partner_name"  => $response['data']->wsServiceTypeCode,
                "service_name"  => $response['data']->wsPackageTypeCode,
                "total"         => $response['data']->wsQuoteDetails->TotalNetCharge,
                "estimate_date" => $response['data']->wsDeliveryDateTime,
                'selected'      => $response['data'],
                'response'      => $response['response']
            ];
        }
        return $response;
    }

    public function generateShipment($fsto_id)
    {
        $shippingData = $this->generateShipmentRequest($fsto_id);
        if($shippingData['status'] == 'success')
        {
            $response = $this->shipments->generateShipment($shippingData['data']);

            if($response['status'] == 'success')
            {
                $pickupdate = explode("<br/>", $response['data']['pickupdate']);
                $myDateTime = DateTime::createFromFormat('d/m/Y H:i:s', ($pickupdate[0]." ".$pickupdate[1]));
                $newPickupDate = $myDateTime->format('Y-m-d H:i:s');
                $details = [
                    'order_id'              => $shippingData['data']['order_id'],
                    'shipping_type'         => 'worldoptions',
                    'shipping_id'           => $response['data']['MasterTrackingNo'],
                    'tracking_id'           => $response['data']['MasterTrackingNo'],
                    'shipment_label'        => $response['data']['Labels']['LabelURL'],
                    'shipping_partner'      => json_encode([
                        "partner_id"    => $response['data']['request']['shipment']['ShippingDetail']['ServiceType'],
                        "partner_name"  => $response['data']['request']['shipment']['ShippingDetail']['ServiceTypeCode']
                    ]),
                    'shipping_charge'       => ($response['data']['charges']) ?? 0,
                    'pickupdate'            => $newPickupDate,
                    'consignment_status'    => 1,
                    'consignment_request'   => json_encode($response['data']['request']),
                    'consignment_response'  => json_encode($response['data']['response'])
                ];
                $this->shippingConsignment->insert($details);
                
				$transferOrderUpdate = TransferOrder::where('fsto_id', $fsto_id)->update([
					'fsto_hasShipmentCreated'	=> 1
				]);
                return [
                    'status'    => 'success',
                    'data'      => [
                        'order_id'              => $shippingData['data']['order_id'],
                        'shipping_id'           => $response['data']['MasterTrackingNo'],
                        'tracking_id'           => $response['data']['MasterTrackingNo'],
                        'shipping_charge'       => $response['data']['charges'],
                        'pickupdate'            => $newPickupDate,
                    ],
                    'message'   => 'success'
                ];
            }
            return $response;
        }
        else
        {
            return $shippingData;
        }
    }

    public function cancelShipment($data)
    {
        $shippingData = $this->shippingConsignment->where('order_id', @$data['order_id'])->first();
        $outs = [
            'status'    => 'error',
            'data'      => [],
            'message'   => 'Shipping Data not found'
        ];
        if($shippingData)
        {
            $cancellation = $this->shipments->cancelShipment($shippingData->tracking_id);

            if($cancellation['status'] == 'success')
            {
                $this->shippingConsignment->where([
                    'shipping_type' => 'worldoptions',
                    'id'            => $shippingData->id
                ])->update([
                    'consignment_status'    => 4
                ]);

                $this->cancelConsignment->insert([
                    'order_id'      => $shippingData->order_id,
                    'shipping_id'   => $shippingData->shipping_id,
                    'cancel_reason' => @$data['reason']
                ]);
                $outs['status'] = 'success';
                $outs['message'] = $cancellation['data']->Message;
            }
        }
        return $outs;
    }
    public function currentTrackingStatus($tracking_number)
    {
        $shippingData = $this->shippingConsignment->where('tracking_id', $tracking_number)->first();
        $response = [
            'status'    => 'error',
            'data'      => [],
            'message'   => 'Shipping Data not found'
        ];
        if($shippingData)
        {
            $response = $this->tracking->trackShipment($tracking_number);
            if($response['status'] == 'success')
            {
                $routemap = $response['data']->TrackingStatus->ActivityDetails->{'TrackStatusDetail.ActivityDetail'};
                if(@count($routemap) > 1)
                {
                    $routemap = reset($routemap);
                }
                $myDateTime = DateTime::createFromFormat('d/m/Y H:i:s', $routemap->Dt);
                $newDateString = $myDateTime->format('d-m-Y H:i:s');
                $status = trim(preg_replace("/[\t\n\r\s]+/", " ", $routemap->Activity));
                $outs = [
                    'status'            => 'success',
                    'data'              => [
                        'shipping_id'       => $tracking_number,
                        'tracking_id'       => $tracking_number,
                        'routemap'          => [
                            'location'      => $routemap->Location,
                            'status'        => $status,
                            'status_date'   => $newDateString
                        ]
                    ],
                    'message'           => ''
                ];
                // return $outs;
                $trackingCheck = $this->consignmentTracking->where([
                    ['shipping_type', 'worldoptions'],
                    ['tracking_id', $tracking_number],
                    ['status_value', $status],
                    ['location', $routemap->Location]
                ])->first();
                if(empty($trackingCheck))
                {
                    $this->consignmentTracking->insert([
                        'shipping_type' => 'worldoptions',
                        'tracking_id'   => $tracking_number,
                        'status_value'  => $status,
                        'location'      => $routemap->Location,
                        'status_date'   => $myDateTime->format('Y-m-d H:i:s')
                    ]);
                    if($status == 'Delivered')
                    {
                        $this->shippingConsignment->where([
                            'shipping_type' => 'worldoptions',
                            'id'            => $shippingData->id
                        ])->update([
                            'consignment_status'    => 3
                        ]);
                    }
                }
                return $outs;
            }
        }
        return $response;
    }
    public function completeTrackingStatus($tracking_number)
    {
        $shippingData = $this->shippingConsignment->where('tracking_id', $tracking_number)->first();
        $response = [
            'status'    => 'error',
            'data'      => [],
            'message'   => 'Shipping Data not found'
        ];
        if($shippingData)
        {
            $response = $this->tracking->trackShipment($tracking_number);
            // return $response;
            if($response['status'] == 'success')
            {
                $trackingStatus = $response['data']->TrackingStatus;
                $routemap = reset($trackingStatus)->{'TrackStatusDetail.ActivityDetail'};
                $outs = [
                    'shipping_id'       => $tracking_number,
                    'tracking_id'       => $tracking_number,
                    'routemap'          => []
                ];
                $this->consignmentTracking->where([
                    ['shipping_type', 'worldoptions'],
                    ['tracking_id', $tracking_number]
                ])->delete();
                if(@count($routemap) > 1)
                {
                    foreach ($routemap as $rp)
                    {
                        $myDateTime = DateTime::createFromFormat('d/m/Y H:i:s', $rp->Dt);
                        $newDateString = $myDateTime->format('d-m-Y H:i:s');
                        $status = trim(preg_replace("/[\t\n\r\s]+/", " ", $rp->Activity));
                        $outs['routemap'][] = [
                            'location'      => $rp->Location,
                            'status'        => $status,
                            'status_date'   => $newDateString
                        ];
                        $this->consignmentTracking->insert([
                            'shipping_type' => 'worldoptions',
                            'tracking_id'   => $tracking_number,
                            'status_value'  => $status,
                            'location'      => $rp->Location,
                            'status_date'   => $myDateTime->format('Y-m-d H:i:s')
                        ]);
                        if($status == 'Delivered')
                        {
                            $this->shippingConsignment->where([
                                'shipping_type' => 'worldoptions',
                                'id'            => $shippingData->id
                            ])->update([
                                'consignment_status'    => 3
                            ]);
                        }
                    }
                }
                else
                {
                    $myDateTime = DateTime::createFromFormat('d/m/Y H:i:s', $routemap->Dt);
                    $newDateString = $myDateTime->format('d-m-Y H:i:s');
                    $status = trim(preg_replace("/[\t\n\r\s]+/", " ", $routemap->Activity));
                    $outs['routemap'][] = [
                        'location'      => $routemap->Location,
                        'status'        => $status,
                        'status_date'   => $newDateString
                    ];
                    $this->consignmentTracking->insert([
                        'shipping_type' => 'worldoptions',
                        'tracking_id'   => $tracking_number,
                        'status_value'  => $status,
                        'location'      => $routemap->Location,
                        'status_date'   => $myDateTime->format('Y-m-d H:i:s')
                    ]);
                    if($status == 'Delivered')
                    {
                        $this->shippingConsignment->where([
                            'shipping_type' => 'worldoptions',
                            'id'            => $shippingData->id
                        ])->update([
                            'consignment_status'    => 3
                        ]);
                    }
                }
                return $outs;
            }
        }
        return $response;
    }


    private function generateShipmentRequest($fsto_id)
    {
        $outs = [
            'status'    => 'error',
            'data'      => [],
            'message'   => 'Order not found'
        ];
        $data = [];
        $package = TransferOrder::where('fsto_id', $fsto_id)->first();
        if($package)
        {
            $outs['message'] = 'Order not packed';
            if($package->fsto_status == 10)
            {
                $productData = $package->packedtransferorderDetails;
                $outs['message'] = 'No packed products found.';
                if($productData)
                {
                    $orderData = Order::where('order_id', $package->fstr_id)->first();
                    $outs['message'] = 'Branch not available.';
                    if(@$orderData->order_branch_id > 0)
                    {
                        $branchData = Branch::where('br_ID', $orderData->order_branch_id)->first();

                        $shipmentExists = $this->shippingConsignment->where([
                            ['order_id', $orderData->order_order_id],
                            ['shipping_type', 'worldoptions'],
                        ])->whereNotIn('consignment_status', [4,5])->get();
                        $outs['message'] = 'Order already registered for shipping.';
                        if(count($shipmentExists) == 0)
                        {
                            $store_addr1 = @$branchData->br_Address;
                            $store_addr2 = [@$branchData->br_City, @$branchData->district->dst_Name, @$branchData->state->st_name];

                            $cust_addr1 = [@$orderData->deliveryAddress->order_house_no, @$orderData->deliveryAddress->order_house_name, @$orderData->deliveryAddress->order_address];
                            $cust_addr2 = [@$orderData->deliveryAddress->order_land_mark, @$orderData->deliveryAddress->order_city, @$orderData->deliveryAddress->order_state];

                            $data['order_id'] = @$orderData->order_order_id;
                            $data['from_details'] = [
                                'company_name'      => $branchData->br_Name,
                                // 'address1'          => $branchData->br_Address,
                                'address1'          => $store_addr1 ?? implode(', ', array_filter($store_addr2)),
                                'address2'          => implode(', ', array_filter($store_addr2)),
                                'city'              => $branchData->br_City,
                                'pincode'           => $branchData->br_pincode,
                                'phone'             => $branchData->br_Phone,
                                'email'             => $branchData->br_Email,
                            ];
                            $data['to_details'] = [
                                'customer_name'     => @$orderData->deliveryAddress->order_customer_name,
                                'customer_email'    => @$orderData->deliveryAddress->order_customer_email,
                                'customer_phone'    => @$orderData->deliveryAddress->order_contact_no,
                                'customer_city'     => @$orderData->deliveryAddress->order_city,
                                'address1'          => implode(', ', array_filter($cust_addr1)),
                                'address2'          => implode(', ', array_filter($cust_addr2)),
                                'pincode'           => @$orderData->deliveryAddress->order_pin
                            ];
                            $pickup_date = date('d/m/Y', strtotime($package->fsto_updateon));
                            $dateCheck = date('Y-m-d', strtotime($package->fsto_updateon));
                            $pickup_time = date('H:i', strtotime($package->fsto_updateon));
                            if(strtotime($package->fsto_updateon) < strtotime('now'))
                            {
                                $pickup_date = date("d/m/Y", time()+86400);
                                $dateCheck = date("Y-m-d", time()+86400);
                                $pickup_time = '11:00';
                            }
                            if (in_array(date('w', strtotime($dateCheck)), [0, 6]))
                            {
                                $pickup_date = (date('w', strtotime($dateCheck)) == 6) ? date("d/m/Y", strtotime('+3 days')) : date("d/m/Y", strtotime('+1 days'));
                                $pickup_time = '11:00';
                            }
                            $data['package_details'] = [
                                'items'         => [],
                                'pickup_date'   => $pickup_date,
                                'pickup_time'   => $pickup_time
                            ];

                            $packagedItems = $this->packDetails->where('rtopd_fstoId', $fsto_id)->get();
                            $x = 0;
                            foreach ($packagedItems as $pItem)
                            {
                                $x++;
                                $data['package_details']['items'][] = [
                                    'ItemNumber'    => intval($pItem->rtopd_id),
                                    'Weight'        => intval($pItem->rtopd_packetweigh),
                                    'Length'        => @intval($pItem->rtpod_length),
                                    'Breadth'       => @intval($pItem->rtpod_breadth),
                                    'Height'        => @intval($pItem->rtpod_height),
                                    'CustomValue'   => intval(str_replace('/', '', $pItem->rtopd_packaging))
                                ];
                            }
                            if($x > 0)
                            {
                                $outs['message'] = 'success';
                                $outs['status'] = 'success';
                                $outs['data'] = $data;
                            }
                        }
                    }
                }
            }
        }
        return $outs;
    }
}