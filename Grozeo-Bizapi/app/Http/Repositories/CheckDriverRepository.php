<?php
namespace App\Http\Repositories;

use App\Models\Branch;
use Aws\DynamoDb\DynamoDbClient;

class CheckDriverRepository
{
    public function checkIfDriverAvailable($branch_id, $deliveredBy = 0, $total = 0)
    {
        try
        {
            $hasDriver = 0;
            $deliveryAddress = auth()->user()->primaryAddress;
            $hasExpPartner = config('expresspartners.default');
            $branchData = Branch::where('br_ID', $branch_id)->first();
            if(($hasExpPartner != "") && ($deliveredBy == 1))
            {
                $shipping = config("expresspartners.{$hasExpPartner}.sClass");
                $shipper = new $shipping();
                $store_addr = [@$branchData->br_Address, @$branchData->br_City, @$branchData->district->dst_Name, @$branchData->state->st_name, @$branchData->br_pincode];

                $cust_note = [@$deliveryAddress->order_customer_name, @$deliveryAddress->deli_house_no, @$deliveryAddress->deli_house_name];
                $streetAddr = @$deliveryAddress->deli_house_name ? explode(',', $deliveryAddress->deli_house_name) : "";
                $streetAddr = @$streetAddr[1] ? trim($streetAddr[1]) :  "";
                $addressLine = implode(', ', array_filter([@$deliveryAddress->deli_address, @$deliveryAddress->deli_address2]));
                $addressLine = ($addressLine != "") ? $addressLine : $streetAddr;
                $cust_addr = [@$addressLine, @$deliveryAddress->deli_city, @$deliveryAddress->deli_land_mark,  @$deliveryAddress->deli_state, @$deliveryAddress->deli_delivery_pin];

                $request = [
                    "order_id"          => 0,
                    "branch_id"         => $branchData->br_ID,
                    "branch_name"       => $branchData->br_Name,
                    "from_address"      => $store_addr,
                    "from_phone"        => $branchData->br_Phone,
                    "from_latitude"     => $branchData->br_Lat,
                    "from_longitude"    => $branchData->br_Lng,
                    "to_name"           => auth()->user()->cust_customer_name,
                    "to_address"        => $cust_addr,
                    "to_phone"          => @$deliveryAddress->deli_contact_no,
                    "to_latitude"       => @$deliveryAddress->deli_latitude,
                    "to_longitude"      => @$deliveryAddress->deli_longitude,
                    "total"             => $total
                ];
                $checkDriver = $shipper->checkIfDeliveryAgentAvailable($request);
                $hasDriver = ($checkDriver) ? 1 : 0;
            }
            else
            {
                $branchData = Branch::selectRaw('area_entries.areaBusinessAssociate, finascop_branch.br_ID, finascop_branch.br_Lat, finascop_branch.br_Lng')
                ->join('area_entries', 'finascop_branch.areaId', '=', 'area_entries.id')
                ->where('br_ID', $branch_id)->first();
                $dynamoClient = DynamoDbClient::factory(config('aws.dynamodb'));
                $isLive = "1";
                $branch_id = (string)$branch_id;

                $expressionAttributes = [
                    ':IsLive'      => ['N' => $isLive],
                    ':BranchID'    => ['N' => $branch_id]
                ];
                $filterExpression = "Is_Live = :IsLive AND DriverBranchId = :BranchID";
                if(@$branchData->areaBusinessAssociate)
                {
                    $baID = $branchData->areaBusinessAssociate;
                    $filterExpression = "Is_Live = :IsLive AND ((DriverBranchId = :BranchID) OR (createdBy = :createdBy AND sourceId = :sourceId))";
                    $expressionAttributes[":createdBy"] = ['N' => '2'];
                    $expressionAttributes[":sourceId"] = ['N' => (string)$baID];
                }
                $params = [
                    'TableName'                 => config('aws.prefix').'QugeoLiveVehicles',
                    'FilterExpression'          => $filterExpression,
                    'ExpressionAttributeValues' => $expressionAttributes
                ];
                $liveDriversBranch = $dynamoClient->scan($params);
                $hasDriver = @$liveDriversBranch->toArray()['Count'] ?? 0;
            }

            return $hasDriver;
        }
        catch (\Exception $e)
        {
            // info("CheckDriverRepository => checkIfDriverAvailable({$branch_id}) Error");info($e);
            return 0;
        }
    }
}