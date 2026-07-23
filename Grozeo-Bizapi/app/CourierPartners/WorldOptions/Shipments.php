<?php
namespace App\CourierPartners\WorldOptions;

use SoapClient;
use App\CourierPartners\WorldOptions\RateServices;
use App\Models\CourierDelivery\{
    ShippingConsignmentLog,
    OrderCourierPartnerSelections
};


class Shipments
{
    protected $woShipmentEndpoint;
    protected $woViodShipmentEndpoint;
    protected $authenticationDetails;
    protected $rateServices;

    public function __construct($auth)
    {
        $this->woShipmentEndpoint = config('courierpartners.worldoptions.createShipment');
        $this->woViodShipmentEndpoint = config('courierpartners.worldoptions.voidShipment');
        $this->authenticationDetails = config('courierpartners.worldoptions.auths');
        $this->rateServices = new RateServices($this->authenticationDetails);
    }

    public function generateShipment($data)
    {
        $deliveryPartner = $this->checkExistingDeliveryPartner($data);
        $outs = [
            'status'    => 'failed',
            'data'      => [],
            'message'   => 'Unable to find a delivery partner'
        ];
        if($deliveryPartner['status'] == 'success')
        {
            $packageDetail = array_map(function($item) {
                return [
                    'ItemNumber'  => $item['ItemNumber'],
                    'Wt'          => $item['Weight'],
                    'Length'      => $item['Length'],
                    'Breadth'     => $item['Breadth'],
                    'Height'      => $item['Height'],
                    'CustomValue' => $item['CustomValue']
                ];
            }, $data['package_details']['items']);
            $recipientsDetail = array(
                "Name"          => $data['to_details']['customer_name'],
                "Company"       => $data['to_details']['customer_name'],
                "Address1"      => $data['to_details']['address1'],
                "Address2"      => $data['to_details']['address2'],
                "Address3"      => '',
                "City"          => $data['to_details']['customer_city'],
                "Postalcode"    => $data['to_details']['pincode'],
                "Country_Code"  => "GB",
                "State_Code"    => '',
                "PhoneDialCode" => '44',
                "Phone"         => $data['to_details']['customer_phone'],
                "Email"         => $data['to_details']['customer_email'],
                "Residential"   => 0
            );

            $orderNote = 'No note';
            $orderReference = $data['order_id'];

            $senderDetail = array(
                'Company'               => $data['from_details']['company_name'],
                'Name'                  => $data['from_details']['company_name'],
                'Address1'              => $data['from_details']['address1'],
                'Address2'              => $data['from_details']['address2'],
                'Address3'              => '',
                'City'                  => $data['from_details']['city'],
                'PostalCode'            => $data['from_details']['pincode'],
                'PhoneDialCode'         => '44',
                'Phone'                 => $data['from_details']['phone'],
                'Email'                 => $data['from_details']['email']
            );
            $soap = new SoapClient($this->woShipmentEndpoint, array('trace' => 1, 'features' => SOAP_USE_XSI_ARRAY_TYPE));
            $params = array(
                'shipment' => array(
                    'AuthenticationDetail'  => $this->authenticationDetails,
                    'SendersDetails'        => $senderDetail,
                    'RecipientsDetails'     => $recipientsDetail,
                    "ShippingDetail"        => array(
                        "ServiceType"           => $deliveryPartner['wsServiceCode'],
                        "ServiceTypeCode"       => $deliveryPartner['wsServiceTypeCode'],
                        "Insurance"             => "0",
                        "CustomerReference"     => $orderReference,
                        "Description"           => $orderNote,
                        "PackageTypeCode"       => $deliveryPartner['wsPackageTypeCode'],
                        "Currency"              => null,
                        "PackageDetails"        => array(
                            "wsShippingDetail.PackageDetail"    => $packageDetail,
                        )
                    ),              
                    "BillingDetail"         => array(
                        "ReadyDate"             => $data['package_details']['pickup_date'],
                        "ReadyTime"             => $data['package_details']['pickup_time'],
                        "CloseTime"             => "15:00",
                        "PickUpLocation"        => "Front",
                        "LocationDescription"   => "Front Door",
                        "TransportationPayor"   => "Bill_To_Sender",
                        "DutiesPayor"           => "Duties_To_Be_Paid_By_Sender",
                        "CollectionOptions"     => "I_Need_To_Book_A_Collection"
                    )
                )
            );
            try
            {
                $response = $soap->DoShipment($params);
                
                ShippingConsignmentLog::create([
                    'order_id'      => $data['order_id'],
                    'type'          => config('courierpartners.worldoptions.local_id'),
                    'request'       => json_encode($params),
                    'response'      => json_encode($response),
                ]);

                if($response->DoShipmentResult->NotificationtType == "SUCCESS")
                {
                    unset($response->DoShipmentResult->Labels->ShippingLabel->Image);
                    $outs = [
                        'status'    => 'success',
                        'data'      => [
                            'pickupdate'    => $response->DoShipmentResult->CollectionDateNumber,
                            'Labels'        => [
                                'ImageLength'       => @$response->DoShipmentResult->Labels->ShippingLabel->ImageLength,
                                'IsThermalPrint'    => @$response->DoShipmentResult->Labels->ShippingLabel->IsThermalPrint,
                                'LabelType'         => @$response->DoShipmentResult->Labels->ShippingLabel->LabelType,
                                'LabelURL'          => $response->DoShipmentResult->Labels->ShippingLabel->LabelURL
                            ],
                            'MasterTrackingNo'  => $response->DoShipmentResult->MasterTrackingNo,
                            'Message'           => @$response->DoShipmentResult->Message,
                            'NotificationtType' => @$response->DoShipmentResult->NotificationtType,
                            'Warning'           => @$response->DoShipmentResult->Warning,
                            'charges'           => $deliveryPartner['TotalNetCharge'],
                            // 'pickup'            => $deliveryPartner['wsPickupDateTime'],
                            'request'           => $params,
                            'response'          => $response->DoShipmentResult
                        ],
                        'message'   => ''
                    ];
                }
                else
                {
                    $outs = [
                        'status'    => 'failed',
                        'data'      => [],
                        'message'   => $response->DoShipmentResult
                    ];
                }
            }
            catch (SoapFault $soapFault)
            {
                $outs = [
                    'status'    => 'failed',
                    'data'      => [],
                    'message'   => $soap->__getLastResponse()
                ];
            }
        }
        return $outs;
    }

    public function cancelShipment($tracking_number)
    {
        $soap = new SoapClient($this->woViodShipmentEndpoint, array('trace' => 1, 'features' => SOAP_USE_XSI_ARRAY_TYPE));
        $outs = [
            'status'    => 'failed',
            'data'      => [],
            'message'   => 'Unable to find a delivery partner'
        ];
        $params = array(
            'request' => array(
                'AuthenticationDetail'  => $this->authenticationDetails,
                "TrackingNumber"        => $tracking_number
            )
        );
        try
        {
            $response = $soap->VoidShipment($params);

            if($response->VoidShipmentResult->NotificationtType == "SUCCESS")
            {
                $outs = [
                    'status'    => 'success',
                    'data'      => $response->VoidShipmentResult
                ];
            }
            else
            {
                $outs = [
                    'status'    => 'failed',
                    'data'      => $response->VoidShipmentResult
                ];
            }
        }
        catch (SoapFault $soapFault)
        {
            $outs = [
                'status'    => 'failed',
                'data'      => $soap->__getLastResponse()
            ];
        }
        return $outs;
    }


    private function checkExistingDeliveryPartner($data)
    {
        $checkPartnerAdded = OrderCourierPartnerSelections::where('order_id', $data['order_id'])->orderBy('id', 'DESC')->first();
		if($checkPartnerAdded)
		{
			return [
                'status'                => 'success',
                'wsServiceCode'         => $checkPartnerAdded->partner_id,
                'wsServiceTypeCode'     => $checkPartnerAdded->courier_name,
                'wsPackageTypeCode'     => $checkPartnerAdded->service_name,
                'TotalNetCharge'        => $checkPartnerAdded->total,
                'wsDeliveryDateTime'    => $checkPartnerAdded->estimate_date
            ];
		}
		else
		{
			$partner = $this->rateServices->findRateService($data);
            if($partner['status'] == 'success')
            {
                return [
                    'status'                => 'success',
                    'wsServiceCode'         => $partner['data']->wsServiceCode,
                    'wsServiceTypeCode'     => $partner['data']->wsServiceTypeCode,
                    'wsPackageTypeCode'     => $partner['data']->wsPackageTypeCode,
                    'TotalNetCharge'        => $partner['data']->wsQuoteDetails->TotalNetCharge,
                    'wsDeliveryDateTime'    => $partner['data']->wsPickupDateTime
                ];
            }
            return $partner;
		}
    }
}