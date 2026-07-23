<?php

namespace App\ExpressPartners\Clickpost;

use Carbon\Carbon;

class ClickpostRequestStructureIND
{
    protected $partnerID;
    function __construct()
    {
        $this->partnerID = config("expresspartners.clickpost.partnerID");
    }

    public function recommendationRequest($data)
    {
        $structure = [
            "reference_number"  => $this->partnerID,
            "item"              => count($data["package_details"]['package']),
            "pickup_pincode"    => @$data['from_details']["pincode"],
            "drop_pincode"      => @$data['to_details']["pincode"],
            "invoice_value"     => (double)$data["invoiceAmount"],
            "weight"            => array_sum(array_column($data['package_details']['package'], 'weight')),
            "length"            => array_sum(array_column($data['package_details']['package'], 'length')),
            "breadth"           => array_sum(array_column($data['package_details']['package'], 'width')),
            "height"            => array_sum(array_column($data['package_details']['package'], 'height')),
            "delivery_type"     => "FORWARD",
            "order_type"        => $data["payment_mode"]
        ];

        return $structure;
    }
    public function consignmentRequest($data, $partner)
    {
        $pickupTime = $data["from_details"]["pickup_date"]."T".$data["from_details"]["pickup_time"];
        $pickupTime = Carbon::parse($pickupTime)->toIso8601String();
        $items = [];
        $package = reset($data["package_details"]["package"]);
        foreach ($data['package_details']['products'] as $item)
        {
            $description = $item["description"] ?? $item["sku"];
            $items[] = [
                "sku"           => $item["sku"],
                "price"         => $item["price"],
                "height"        => $package["height"],
                "length"        => $package["length"],
                "weight"        => $item["weight"],
                "breadth"       => $package["width"],
                "quantity"      => $item["qty"],
                "hsn_code"      => $item['hsn'],
                "description"   => $item["description"]
            ];
        }
        return [
            "email"             => $data["to_details"]["email"],
            "items"             => $items,
            "label"             => true,
            "height"            => $package["height"],
            "length"            => $package["length"],
            "weight"            => $package["weight"],
            "breadth"           => $package["width"],
            "drop_lat"          => $data["to_details"]["latitude"],
            "cod_value"         => $data['pending_amount'],
            "drop_city"         => $data["to_details"]["city"],
            "drop_long"         => $data["to_details"]["longitude"],
            "drop_name"         => $data["to_details"]["name"],
            "drop_time"         => NULL,
            "async_flag"        => false,
            "drop_email"        => $data["to_details"]["email"],
            "drop_phone"        => $data["to_details"]["phone"],
            "drop_state"        => $data["to_details"]["state"],
            "order_date"        => $data["orderDate"],
            "order_type"        => $data["payment_mode"],
            "pickup_lat"        => $data["from_details"]["latitude"],
            "pickup_city"       => $data["to_details"]["city"],
            "pickup_long"       => $data["from_details"]["longitude"],
            "pickup_name"       => $data["from_details"]["name"],
            "pickup_district"   => $data["from_details"]["district"],
            "pickup_time"       => $pickupTime,
            "account_code"      => @$partner->account_code,
            "drop_address"      => $data["to_details"]["address"],
            "drop_country"      => $data["to_details"]["country"],
            "drop_pincode"      => (string)$data["to_details"]["pincode"],
            "invoice_date"      => $data['invoiceDate'],
            "pickup_phone"      => $data["from_details"]["phone"],
            "pickup_email"      => $data["from_details"]["email"],
            "pickup_state"      => $data["from_details"]["state"],
            "delivery_type"     => "FORWARD",
            "drop_landmark"     => $data["to_details"]["landmark"],
            "invoice_value"     => floatval($data['invoiceAmount']),
            "invoice_number"    => $data['invoiceNo'],
            "pickup_address"    => $data["from_details"]["address"],
            "pickup_country"    => $data["from_details"]["country"],
            "pickup_pincode"    => $data["from_details"]["pincode"],
            "courier_partner"   => @$partner->cp_id,
            "pickup_landmark"   => "",
            "reference_number"  => $data["order_id"],
            "drop_instructions" => NULL,
        ];
    }
}