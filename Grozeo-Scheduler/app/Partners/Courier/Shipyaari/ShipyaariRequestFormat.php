<?php
namespace App\Partners\Courier\Shipyaari;

use Carbon\Carbon;

class ShipyaariRequestFormat
{
    function __construct() {}

    public function searchServiceRequest($request)
    {
        $outs = [
            "pickupPincode"     => @$request['from_details']['pincode'] ? (double)$request['from_details']['pincode'] : 00000,
            "deliveryPincode"   => @$request['to_details']['pincode'] ? (double)$request['to_details']['pincode'] : 00000,
            "invoiceValue"      => @$request['total'] ? (double)$request['total'] : 100,
            "paymentMode"       => "PREPAID",
            "weight"            => (@$request['totalWeight'] > 0) ? $request['totalWeight'] : 1,
            "orderType"         => "B2C",
            "dimension"         => [
                "length"            => 10,
                "width"             => 10,
                "height"            => 10
            ]
        ];
        return $outs;
    }
    public function bookOrderRequest($request, $partner)
    {
        $pickupDetails = $this->pickupDetails($request);
        $deliveryDetails = $this->deliveryDetails($request);
        $boxInfo = $this->boxDetails($request);
        $estimatedDate = Carbon::parse($request['exp_pickup']);
        $outs = [
            "pickupDetails"             => $pickupDetails,
            "deliveryDetails"           => $deliveryDetails,
            "boxInfo"                   => $boxInfo,
            "orderType"                 => "B2C",
            "transit"                   => "FORWARD",
            "pickupDate"                => $estimatedDate->timestamp,
            "orderId"                   => $request['order_id']
        ];
        return $outs;
    }

    private function pickupDetails($request)
    {
        $storeAddr1 = implode(", ", array_filter([$request['from_details']['address1'], $request['from_details']['address2'], $request['from_details']['address3']]));
        $storeAddr2 = implode(', ', array_filter([@$request['from_details']['city'], @$request['from_details']['district'], @$request['from_details']['state']]));

        return [
            "fullAddress"   => ($storeAddr1 != "") ? $storeAddr1 : $storeAddr2,
            "pincode"       => @$request['from_details']['pincode'],
            "latitude"      => (string)@$request['from_details']['latitude'],
            "longitude"     => (string)@$request['from_details']['longitude'],
            "contact"       => [
                "name"          => @$request['from_details']['store_name'],
                "mobileNo"      => $this->phoneCodeCheck(@$request['from_details']['phone'])
            ]
        ];
    }
    private function deliveryDetails($request)
    {
        $custAddr1 = implode(", ", array_filter([$request['to_details']['address1'], $request['to_details']['address2']]));
        $custAddr2 = implode(', ', array_filter([@$request['to_details']['house_no'], @$request['to_details']['house_name'], @$request['to_details']['landmark'], @$request['to_details']['city'], @$request['to_details']['state']]));

        return [
            "fullAddress"   => ($custAddr1 != "") ? $custAddr1 : $custAddr2,
            "pincode"       => @$request['to_details']['pincode'],
            "latitude"      => (string)@$request['to_details']['latitude'],
            "longitude"     => (string)@$request['to_details']['longitude'],
            "contact"       => [
                "name"          => @$request['to_details']['name'],
                "mobileNo"      => $this->phoneCodeCheck(@$request['to_details']['phone'])

            ]
        ];
    }
    private function boxDetails($request)
    {
        $outs = [];
        $productList = [];
        foreach ($request['package_details']['products'] as $prod)
        {
            $productList[] = [
                "name"          => $prod['name'],
                "sku"           => $prod['sku'],
                "hsnCode"       => $prod['hsn'],
                "qty"           => $prod['qty'],
                "unitPrice"     => $prod['price'],
                "unitTax"       => $prod['tax'],
                "totalDiscount" => $prod['discount'],
                "totalPrice"    => $prod['total'],
                "weightUnit"    => "kg",
                "deadWeight"    => (@$prod['weight'] > 0.1) ? (double)$prod['weight'] : 0.1,
                "length"        => (@$prod['length'] > 0.5) ? $prod['length'] : 1,
                "breadth"       => (@$prod['width'] > 0.5) ? $prod['width'] : 1,
                "height"        => (@$prod['height'] > 0.5) ? $prod['height'] : 1,
                "measureUnit"   => "cm"
            ];
        }
        $splitter = ceil(count($productList) / count($request['package_details']['package']));
        $productList = (count($request['package_details']['package']) > 1) ? array_chunk($productList, $splitter) : $productList;
        $p = 0;
        $codInfo = [
            "isCod"             => false,
            "collectableAmount" => (double)$request['pending_amount'],
            "invoiceValue"      => (double)$request['total']
        ];
        $codInfo["isCod"] = ($request['payment_mode'] == 'cod') ? true : false;
        foreach ($request['package_details']['package'] as $pdetails)
        {
            $products = (count($request['package_details']['package']) > 1) ? $productList[$p] : $productList;
            $outs[] = [
                "name"          => "package_{$pdetails['id']}",
                "type"          => "parcel",
                "weightUnit"    => "Kg",
                "deadWeight"    => (@$pdetails['weight'] > 0.1) ? (double)$pdetails['weight'] : 0.1,
                "length"        => (@$pdetails['length'] >= 0.5) ? $pdetails['length'] : 10,
                "breadth"       => (@$pdetails['width'] >= 0.5) ? $pdetails['width'] : 10,
                "height"        => (@$pdetails['height'] >= 0.5) ? $pdetails['height'] : 10,
                "qty"           => 1,
                "measureUnit"   => "cm",
                "products"      => $products,
                "codInfo"       => $codInfo,
                "podInfo"       => [
                    "isPod"     => false,
                ],
                "insurance"     => false
            ];
            $p++;
        }
        return $outs;
    }
    private function phoneCodeCheck($checkPhone = NULL)
    {
        $phone = "";
        if(@$checkPhone != "")
        {
            $phoneCode = config('app.phonecode') ?? "+91";
            $number = preg_replace('/[^A-Za-z0-9+]/', '', $checkPhone);
            $phone = str_replace($phoneCode, '', $number);
        }
        return $phone;
    }
}