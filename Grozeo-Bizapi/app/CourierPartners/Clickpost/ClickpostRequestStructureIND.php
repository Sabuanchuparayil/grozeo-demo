<?php

namespace App\CourierPartners\Clickpost;

use Carbon\Carbon;

class ClickpostRequestStructureIND
{
    protected $partnerID;
    function __construct()
    {
        $this->partnerID = config("courierpartners.clickpost.partnerID");
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
        // return [$data, $partner];
        $dropInfo = [
            "drop_name"         => $data["to_details"]["name"],
            "drop_email"        => $data["to_details"]["email"],
            "drop_phone"        => $data["to_details"]["phone"],
            "drop_address"      => $data["to_details"]["address"],
            "drop_landmark"     => $data["to_details"]["landmark"],
            "drop_city"         => $data["to_details"]["city"],
            "drop_state"        => $data["to_details"]["state"],
            "drop_country"      => $data["to_details"]["country"],
            "drop_pincode"      => $data["to_details"]["pincode"],
            "drop_lat"          => $data["to_details"]["latitude"],
            "drop_long"         => $data["to_details"]["longitude"],
            "drop_address_type" => "RESIDENTIAL",
        ];
        $pickupTime = $data["from_details"]["pickup_date"]."T".$data["from_details"]["pickup_time"];
        $pickupTime = Carbon::parse($pickupTime)->toIso8601String();
        $pickupInfo = [
            "pickup_name"           => $data["from_details"]["name"],
            "pickup_phone"          => $data["from_details"]["phone"],
            "pickup_email"          => $data["from_details"]["email"],
            "pickup_address"        => $data["from_details"]["address"],
            "pickup_city"           => $data["from_details"]["city"],
            "pickup_district"       => $data["from_details"]["district"],
            "pickup_state"          => $data["from_details"]["state"],
            "pickup_country"        => $data["from_details"]["country"],
            "pickup_pincode"        => $data["from_details"]["pincode"],
            "pickup_lat"            => $data["from_details"]["latitude"],
            "pickup_long"           => $data["from_details"]["longitude"],
            "tin"                   => $data["from_details"]["tin"],
            "pickup_time"           => $pickupTime,
            "pickup_address_type"   => "OFFICE",
        ];
        $items = [];
        $package = reset($data["package_details"]["package"]);
        foreach ($data['package_details']['products'] as $item)
        {
            $description = $item["description"] ?? $item["sku"];
            $items[] = [
                "sku"           => $item["sku"],
                "price"         => $item["price"],
                "weight"        => $item["weight"],
                "hs_code"       => $item["hsn"],
                "quantity"      => $item["qty"],
                "description"   => $description,
                "additional"    => [
                    "length"    => $package["length"],
                    "breadth"   => $package["width"],
                    "height"    => $package["height"],
                ],
                "gst_info"      => []
            ];
        }
        $shipmentDetails = [
            "items"             => $items,
            "length"            => $package["length"],
            "breadth"           => $package["width"],
            "weight"            => $package["weight"],
            "height"            => $package["height"],
            "order_id"          => $data["order_id"],
            "cod_value"         => $data['pending_amount'],
            "cod_currency_code" => $data["currency"],
            "order_type"        => $data['payment_mode'],
            "delivery_type"     => "FORWARD",
            "invoice_number"    => $data['invoiceNo'],
            "invoice_date"      => $data['invoiceDate'],
            "invoice_value"     => $data['invoiceAmount'],
            "courier_partner"   => @$partner->cp_id,
            "reference_number"  => $data["order_id"],
            "account_code"      => @$partner->account_code,
        ];
        $structure = [
            "drop_info"         => $dropInfo,
            "pickup_info"       => $pickupInfo,
            "shipment_details"  => $shipmentDetails,
            "additional"        => []
        ];
        return $structure;
    }
}