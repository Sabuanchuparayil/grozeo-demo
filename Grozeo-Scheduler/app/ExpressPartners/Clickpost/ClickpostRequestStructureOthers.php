<?php
namespace App\ExpressPartners\Clickpost;

use Carbon\Carbon;

class ClickpostRequestStructureOthers
{
    protected $partnerID, $pinCode, $countryCode;
    function __construct()
    {
        $this->partnerID = config("expresspartners.clickpost.partnerID");
        $this->countryCode = config("expresspartners.clickpost.country");
        $this->pinCode = config("expresspartners.clickpost.defaultPin") ?? "00000";
    }

    public function recommendationRequest($data)
    {
        $dropCode = (@$data["to_details"]["pincode"] > 0) ? $data["to_details"]["pincode"] : $this->pinCode;
        $dropInfo = [
            "name"          => $data["to_details"]["name"],
            "email"         => $data["to_details"]["email"],
            "phone"         => $data["to_details"]["phone"],
            "address"       => $data["to_details"]["address"],
            "landmark"      => $data["to_details"]["landmark"],
            "city"          => $data["to_details"]["city"],
            "state"         => $data["to_details"]["state"],
            "lat"           => $data["to_details"]["latitude"],
            "long"          => $data["to_details"]["longitude"],
            "postal_code"   => $dropCode,
            "country_code"  => $this->countryCode
        ];
        $pickCode = (@$data["from_details"]["pincode"] > 0) ? $data["from_details"]["pincode"] : $this->pinCode;
        $pickupTime = $data["from_details"]["pickup_date"]."T".$data["from_details"]["pickup_time"];
        $pickupTime = Carbon::parse($pickupTime)->toIso8601String();
        $pickupInfo = [
            "name"          => $data["from_details"]["name"],
            "phone"         => $data["from_details"]["phone"],
            "email"         => $data["from_details"]["email"],
            "address"       => $data["from_details"]["address"],
            "city"          => $data["from_details"]["city"],
            "district"      => $data["from_details"]["district"],
            "state"         => $data["from_details"]["state"],
            "time"          => $pickupTime,
            "lat"           => $data["from_details"]["latitude"],
            "long"          => $data["from_details"]["longitude"],
            "postal_code"   => $pickCode,
            "country_code"  => $this->countryCode
        ];
        $items = [];
        $package = reset($data["package_details"]["package"]);
        foreach ($data['package_details']['products'] as $item)
        {
            $description = $item["description"] ?? $item["sku"];
            $items[] = [
                "sku"           => $item["sku"],
                "price"         => $item["price"],
                "quantity"      => $item["qty"],
                "weight"        => $item["weight"],
                "hs_code"       => $item["hsn"],
                "description"   => $description,
            ];
        }
        $shipmentDetails = [
            "items"             => $items,
            "length"            => $package["length"],
            "breadth"           => $package["width"],
            "weight"            => $package["weight"],
            "height"            => $package["height"],
            "cod_value"         => $data['pending_amount'],
            "currency_code"     => $data["currency"],
            "order_type"        => $data['payment_mode'],
            "delivery_type"     => "FORWARD",
            "invoice_number"    => $data['invoiceNo'],
            "invoice_date"      => $data['invoiceDate'],
            "invoice_value"     => $data['invoiceAmount'],
            "reference_number"  => $data["order_id"]
        ];
        return [
            "pickup_info"       => $dropInfo,
            "drop_info"         => $pickupInfo,
            "shipment_details"  => $shipmentDetails
        ];
    }

    public function consignmentRequest($data, $partner)
    {
        $dropCode = (@$data["to_details"]["pincode"] > 0) ? $data["to_details"]["pincode"] : $this->pinCode;
        $dropInfo = [
            "name"          => $data["to_details"]["name"],
            "email"         => $data["to_details"]["email"],
            "phone"         => $data["to_details"]["phone"],
            "address"       => $data["to_details"]["address"],
            "landmark"      => $data["to_details"]["landmark"],
            "city"          => $data["to_details"]["city"],
            "state"         => $data["to_details"]["state"],
            "postal_code"   => $dropCode,
            "phone_code"    => config("app.phonecode"),
            "lat"           => $data["to_details"]["latitude"],
            "long"          => $data["to_details"]["longitude"],
            "address_type"  => "RESIDENTIAL",
            "country_code"  => $this->countryCode
        ];
        $pickCode = (@$data["from_details"]["pincode"] > 0) ? $data["from_details"]["pincode"] : $this->pinCode;
        $pickupTime = $data["from_details"]["pickup_date"]."T".$data["from_details"]["pickup_time"];
        $pickupTime = Carbon::parse($pickupTime)->toIso8601String();
        $pickupInfo = [
            "name"          => $data["from_details"]["name"],
            "phone"         => $data["from_details"]["phone"],
            "email"         => $data["from_details"]["email"],
            "address"       => $data["from_details"]["address"],
            "city"          => $data["from_details"]["city"],
            "district"      => $data["from_details"]["district"],
            "state"         => $data["from_details"]["state"],
            "postal_code"   => $pickCode,
            "phone_code"    => config("app.phonecode"),
            "lat"           => $data["from_details"]["latitude"],
            "long"          => $data["from_details"]["longitude"],
            "time"          => $pickupTime,
            "address_type"  => "OFFICE",
            "country_code"  => $this->countryCode
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
            "courier_partner"   => @$partner->courier_partner_code,
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