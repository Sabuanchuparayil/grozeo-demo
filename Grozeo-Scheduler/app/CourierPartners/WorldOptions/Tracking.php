<?php
namespace App\CourierPartners\WorldOptions;

use SoapClient;

class Tracking
{
    public $woTrackingEndpoint;
    public $authenticationDetails;

    public function __construct($auth)
    {
        $this->woTrackingEndpoint = config('courierpartners.worldoptions.tracking');
        $this->authenticationDetails = config('courierpartners.worldoptions.auths');
    }

    public function trackShipment($tracking_number)
    {
        $soap = new SoapClient($this->woTrackingEndpoint, array('trace' => 1, 'features' => SOAP_USE_XSI_ARRAY_TYPE));
        $params = array(
            'request' => array(
                'AuthenticationDetail'  => $this->authenticationDetails,
                "TrackingNumber"        => $tracking_number
            )
        );
        try
        {
            $response = $soap->GetTrackingStatus($params);

            if($response->GetTrackingStatusResult->NotificationtType == "SUCCESS")
            {
                return [
                    'status'    => 'success',
                    'data'      => $response->GetTrackingStatusResult,
                    'message'   => ''
                ];
            }
            else
            {
                return [
                    'status'    => 'failed',
                    'data'      => [],
                    'message'   => $response->GetTrackingStatusResult
                ];
            }
        }
        catch (SoapFault $soapFault)
        {
            return [
                'status'    => 'failed',
                'data'      => [],
                'message'   => $soap->__getLastResponse()
            ];
        }
    }
}