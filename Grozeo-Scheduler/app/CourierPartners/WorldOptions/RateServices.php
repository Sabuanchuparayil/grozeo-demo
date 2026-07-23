<?php
namespace App\CourierPartners\WorldOptions;

use SoapClient;
use App\Http\Repositories\ShippingLogRepository;

class RateServices
{
    protected $woRateServiceEndpoint;
    protected $authenticationDetails;

    public function __construct($auth)
    {
        $this->woRateServiceEndpoint = config('courierpartners.worldoptions.rateService');
        $this->authenticationDetails = config('courierpartners.worldoptions.auths');
    }

    public function findRateService($data)
    {
        if(@$data['package_details']['items'])
        {
            $packageDetail = $data['package_details']['items'];
        }
        else
        {
            $packageDetail = [
                [
                    'ItemNumber'    => 52623,
                    'Weight'        => 1,
                    'Length'        => 10,
                    'Breadth'       => 10,
                    'Height'        => 10,
                    'CustomValue'   => 0,
                ]
            ];
        }
        $senderDetail = array(
            'CollectionCity'        => $data['from_details']['city'],
            'CollectionPostCode'    => $data['from_details']['pincode'],
            'CollectionCountryCode' => "GB"
        );
        $recipientsDetail = [
            "DeliveryCountryCode"   => "GB",
            "DeliveryState"         => '',
            "DeliveryCity"          => $data['to_details']['customer_city'],
            "DeliveryPostCode"      => $data['to_details']['pincode'],
            "Residential"           => 0
        ];
        $soap = new SoapClient($this->woRateServiceEndpoint, array('trace' => 1, 'features' => SOAP_USE_XSI_ARRAY_TYPE));
        $params = array(
            'request' => array(
                'AuthenticationDetail'  => $this->authenticationDetails,
                'SenderDetails'         => $senderDetail,
                'RecipientDetails'      => $recipientsDetail,
                "ShippingDetails"       => array(
                    "ShipmentType"                  => "Domestic",
                    "ServiceName"                   => "ALL",
                    "ServiceTypeName"               => "ALL",
                    "PackageType"                   => "Any_Document",
                    "PackageDetails"                => $packageDetail,
                    "IsCollectionDropoffRequired"   => 0,
                    "IsDeliveryDropoffRequired"     => 0,
                    "IsSMSRequired"                 => '',
                    "PODType"                       => 0
                )
            )
        );
        try
        {
            $response = $soap->GetAllServicesAndRates($params);
            (new ShippingLogRepository)->createLog([
                'order_id'      => @$data['order_id'] ?? "-",
                'type'          => config('courierpartners.worldoptions.local_id'),
                'request'       => json_encode($params) ?? "",
                'response'      => json_encode($response),
            ]);
            if($response->GetAllServicesAndRatesResult->NotificationtType == "SUCCESS")
            {
                return [
                    'status'    => 'success',
                    'data'      => reset($response->GetAllServicesAndRatesResult->wsRateService->wsAvailableServicesAndRates)
                ];
            }
            else
            {
                return [
                    'status'    => $response->GetAllServicesAndRatesResult->NotificationtType,
                    'data'      => $response->GetAllServicesAndRatesResult
                ];
            }

        }
        catch (SoapFault $soapFault)
        {
            return [
                'status'    => 'failed2',
                'data'      => $soap->__getLastResponse(),
                'faults'    => $soapFault
            ];
        }
    }

}