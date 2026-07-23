<?php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class EmailHelper
{
    public function __construct(){}
    public function sendEmail($type, $details)
    {
        $response = false;
        $header = ['Content-Type: application/json'];
        switch ($type)
        {
            case 'welcomeCustomer':
                $invitationLink = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name= 'PUB_WELCOME_EMAIL'");
                $url = $invitationLink[0]->cfg_Value;

                $response = $this->curlCall($url, json_encode($details), 'POST', $header);
                break;

            case 'orderComplete':
                $invitationLink = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name= 'PUB_ORDER_COMPLETE_EMAIL'");
                $url = $invitationLink[0]->cfg_Value;

                $response = $this->curlCall($url, json_encode($details), 'POST', $header);
                break;

            case 'deliveryComplete':
                $invitationLink = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name= 'PUB_DELIVERY_COMPLETE_EMAIL'");
                $url = $invitationLink[0]->cfg_Value;

                $response = $this->curlCall($url, json_encode($details), 'POST', $header);
                break;

            case 'packingInvoice':
                $invitationLink = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name= 'PACKING_SEND_INVOICE'");
                $url = $invitationLink[0]->cfg_Value;

                $response = $this->curlCall($url, json_encode($details), 'POST', $header);
                break;

            case 'ShipmentConfirmation':
                $invitationLink = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name= 'SHIPMENT_CONFIRMATION_EMAIL'");
                $url = $invitationLink[0]->cfg_Value;

                $response = $this->curlCall($url, json_encode($details), 'POST', $header);
                break;

            case 'EmailOTP':
                $invitationLink = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name= 'EMAIL_OTP_VERIFICATION'");
                $url = $invitationLink[0]->cfg_Value;

                $response = $this->curlCall($url, json_encode($details), 'POST', $header);
                break;
        }
        return $response;
    }

    private function curlCall($url, $data, $method, $header)
    {
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS =>$data,
                CURLOPT_HTTPHEADER => $header
            )
        );
        $response = curl_exec($curl);
        if (curl_errno($curl))
        {
            return json_decode(curl_error($curl));
        }
        curl_close($curl);
        return json_decode($response);
    }
}