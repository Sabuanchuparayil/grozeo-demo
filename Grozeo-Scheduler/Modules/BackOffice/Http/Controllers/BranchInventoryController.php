<?php

namespace BackOffice\Http\Controllers;

use DB;
use App\Models\StockItemMaster;
use BackOffice\Models\BoyOrder;
use App\Exceptions\MsgException;
use Illuminate\Support\Facades\Log;
use App\Http\Responses\ErrorResponse;
use BackOffice\Models\BranchInventory;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\InventoryResponse;
use BackOffice\Models\BranchInventoryUpload;
use BackOffice\Http\Requests\BranchInventoryRequest;
use App\Models\MarginDistributionb2c;
use App\Models\SellingPriceFactor;
use App\Models\MinimumMargin;
use BackOffice\Models\Branch;
use BackOffice\Models\InventoryUploadLog;

class BranchInventoryController
{
    protected $itemMaster;

    protected $branchUpload;

    protected $branchInventory;

    public function __construct(StockItemMaster $itemMaster = null, BranchInventoryUpload $branchUpload = null, BranchInventory $branchInventory = null)
    {
        $this->itemMaster = $itemMaster ?? new StockItemMaster;
        $this->branchUpload = $branchUpload ?? new BranchInventoryUpload;
        $this->branchInventory = $branchInventory ?? new BranchInventory;
    }

    public function get()
    {
        return  new SuccessWithData($this->getItems());
    }
    public function logInventory(BranchInventoryRequest $request)
    {
        $error = $refid = null;
        $stockItem = $request["data"];
        $stockItems = array_map(function ($tag) {
            $data = array();
            $data["item_count"] = $tag["Qty"];
            $data["mrp"] = $tag["MRP"];
            $data["selling_price"] = $tag["selling_price"];
            $data["discount_selling_price"] = @$tag["discount_selling_price"] ? @$tag["discount_selling_price"] : 0;
            $data["stit_itemERPId"] = $tag["erpId"];
            $data["type"] = @$tag["type"] ? @$tag["type"] : 0;
            $data["product_id"] = @$tag["product_id"] ? @$tag["product_id"] : 0;
            $data["hsnCode"] = @$tag["hsnCode"] ? @$tag["hsnCode"] : NULL;
            $data["taxValue"] = @$tag["taxValue"] ? @$tag["taxValue"] : NULL;
            $data["cessValue"] = @$tag["cessValue"] ? @$tag["cessValue"] : NULL;
            return $data;
        }, $stockItem);

        $refid = $this->addRequestLog($stockItems, $error);
        $error = [];
        return  new InventoryResponse(
            $request["data"],
            $error,
            $refid

        );
    }
    public function saveInventory(BranchInventoryRequest $request)
    {
        $error = $refid = null;
        $stockItem = $request["data"];
        $stockItems = array_map(function ($tag) {
            $data = array();
            $data["item_count"] = $tag["Qty"];
            $data["mrp"] = $tag["MRP"];
            $data["selling_price"] = $tag["selling_price"];
            $data["discount_selling_price"] = @$tag["discount_selling_price"] ? @$tag["discount_selling_price"] : 0;
            $data["stit_itemERPId"] = $tag["erpId"];
            $data["type"] = @$tag["type"] ? @$tag["type"] : 0;
            $data["product_id"] = @$tag["product_id"] ? @$tag["product_id"] : 0;
            $data["hsnCode"] = @$tag["hsnCode"] ? @$tag["hsnCode"] : NULL;
            $data["taxValue"] = @$tag["taxValue"] ? @$tag["taxValue"] : NULL;
            $data["cessValue"] = @$tag["cessValue"] ? @$tag["cessValue"] : NULL;
            return $data;
        }, $stockItem);

        $error = $this->validateItems($stockItems);

        DB::transaction(function () use ($stockItems, $error) {
            $updatedItems = $this->saveItems($stockItems, $error);
            $refid = $this->addUpdateLog($stockItems, $error);
        });
        return  new InventoryResponse(
            $request["data"],
            $error,
            $refid

        );
    }
    public function validateItems(&$stockItems)
    {
        $error = [];
        $childitems = [];
        foreach ($stockItems as $items => $indiitems) {


            $br_ID = auth_user()->br_ID;
            $stit_id = @$indiitems["product_id"]??0;
            if ($indiitems["type"] != 1) {                
                $storecode = DB::table('finascop_stock_itemmaster_product_code_stores')
                    ->select('fsipc_stit_id')
                    ->where('fsipcs_store', $br_ID)
                    ->where('fsipcs_Code', $indiitems["stit_itemERPId"])
                    ->first();


                if (isset($storecode->fsipc_stit_id))
                    $stit_id = $storecode->fsipc_stit_id;

                if ($stit_id == 0) {

                    $branch = Branch::where('br_ID', $br_ID)
                        ->select('br_storeGroup')
                        ->first();
                    $brgroup =  $branch->br_storeGroup;

                    $storecode = DB::table('finascop_stock_itemmaster_product_codes')->select('fsipc_stit_id')
                        ->where([
                            ['fsipc_isIndividual', 0],
                            ['fsipc_isCompany', 0],
                            ['fsipc_storeGroup', $brgroup],
                            ['fsipc_code', $indiitems["stit_itemERPId"]]
                        ])
                        ->orWhere([
                            ['fsipc_isCompany', 1],
                            ['fsipc_code', $indiitems["stit_itemERPId"]]
                        ])
                        ->first();
                    $stit_id = @$storecode->fsipc_stit_id ?? 0;
                }
            }



            if ($stit_id > 0) {
                $item = $this->itemMaster->where("stit_ID", $stit_id)
                    ->first(['stit_ID', 'stit_HasChildItem', 'stit_GST', 'least_package_type_id']);
                $stockItems[$items]['itemId'] =  $item['stit_ID'];
                $stockItems[$items]['stit_GST'] =  $item['stit_GST'];
                $stockItems[$items]['least_package_type_id'] =  $item['least_package_type_id'];

                if ($item['stit_HasChildItem'] == 1) {
                    $subitems = $this->itemMaster->where("stit_ParentItemId", $item['stit_ID'])
                        ->get(['stit_ID', 'stit_itemERPId', 'stit_ConvertCalcMode', 'stit_ConvertCalcRate']);
                    foreach ($subitems as $subitem) {
                        $mrp = round($indiitems['mrp'] / $subitem['stit_ConvertCalcRate'], 2);
                        $selling_price = round($indiitems['selling_price'] / $subitem['stit_ConvertCalcRate'], 2);
                        if ($subitem['stit_ConvertCalcMode'] == 1) {
                            $mrp = round($indiitems['mrp'] * $subitem['stit_ConvertCalcRate'], 2);
                            $selling_price = round($indiitems['selling_price'] * $subitem['stit_ConvertCalcRate'], 2);
                        }
                        $childitems[] = [
                            "stit_itemERPId"    => $indiitems['stit_itemERPId'],
                            "item_count"        => $indiitems['item_count'],
                            "mrp"               => $mrp,
                            "selling_price"     => $selling_price,
                            "itemId"            => $subitem['stit_ID']
                        ];
                    }
                }
            } else {
                $error[] = $indiitems["stit_itemERPId"];
                unset($stockItems[$items]);
            }
        }
        if (!empty($childitems)) {
            $stockItems = array_merge($stockItems, $childitems);
        }

        return $error;
    }
    public function saveItems($stockItems, $errorIds)
    {
        $stocksIds = [];
        /*$this->branchInventory
            ->where('branch_id', auth_user()->br_ID)
            ->update([
                'item_count' => 0,
                'mrp' => 0,
                'selling_price' => 0
            ]);
*/
        $stocksIds = array();
        foreach ($stockItems as $items) {
            $stocksIds[] = $items['itemId'];
        }



        $stocks = $this->branchInventory
            ->where("branch_id", auth_user()->br_ID)
            ->whereIn("stit_id", $stocksIds)
            ->get(['id', 'stit_id']);

        $stocksBrIds = array();
        foreach ($stocks as $stock) {
            $stocksBrIds[] = $stock['stit_id'];
        }

        $marginDistributions = MarginDistributionb2c::where('is_default', 1)
            ->first();
        foreach ($stockItems as $items) {
            if ($items['item_count'] <= 0 || $items['mrp'] <= 0 || $items['selling_price'] <= 0) {
                $itemcount = 0;
                $itemmrp = 0;
                $itemsp = 0;
                $itemdsp = 0;
            } else {
                $itemcount = $items['item_count'];
                $itemmrp = $items['mrp'];
                $itemsp = $items['selling_price'];
                $itemdsp = $items['discount_selling_price'];
            }
            $itemLandingCost = $items['selling_price'];
            $itemMMG = round(($items['mrp'] - $itemLandingCost), 2);
            $discount_selling_price = 0;
            $calculatedSP = 0;




            $fpoddata['fcpod_spHmDel'] = round($calculatedSP, 2);
            $fpoddata['fcpod_spCouDel'] = round($calculatedSP, 2);
            $fpoddata['fcpod_spPikup'] = round($calculatedSP, 2);

            $fpod_spetHmDel = ($fpoddata['fcpod_spHmDel'] * 100) / (100 + $items['stit_GST']);
            $fpoddata['fcpod_spetHmDel'] = round($fpod_spetHmDel, 2);
            $fpod_spetCouDel = ($fpoddata['fcpod_spCouDel'] * 100) / (100 + $items['stit_GST']);
            $fpoddata['fcpod_spetCouDel'] = round($fpod_spetCouDel, 2);
            $fpod_spetPikup = ($fpoddata['fcpod_spPikup'] * 100) / (100 + $items['stit_GST']);
            $fpoddata['fcpod_spetPikup'] = round($fpod_spetPikup, 2);

            $insertData = [
                    'item_count' => $itemcount,
                    'mrp' => $itemmrp,
                    'selling_price' => $itemsp,
                    'updated_on' => date("Y-m-d H:i:s"),
                    'fpod_poLandingCostleastSKU' => $itemLandingCost,
                    'fpod_poMMGleastSKU' => $itemMMG,
                    'fpod_leastSKUmrp' => $items['mrp'],
                    'purchasing_unit' => $items['least_package_type_id'],
                    'fpod_customerRateHmDel' => $fpoddata['fcpod_spHmDel'],
                    'fpod_customerRateCouDel' => $fpoddata['fcpod_spCouDel'],
                    'fpod_customerRatePikup' => $fpoddata['fcpod_spPikup']
                ];
                if(@$items['hsnCode'] != NULL)
                    $insertData["hsnCode"] = @$items['hsnCode'];
                if(@$items['taxValue'] != NULL)
                    $insertData["taxValue"] = @$items['taxValue'];
                if(@$items['cessValue'] != NULL)
                    $insertData["cessValue"] = @$items['cessValue'];

            if (!in_array($items["itemId"], $stocksBrIds)) { 

                $insertData['stit_id'] = $items['itemId'];
                $insertData['branch_id'] = auth_user()->br_ID;
                $this->branchInventory->create($insertData);
                $stocksBrIds[] = $items["itemId"];
            } else {
                $this->branchInventory
                    ->where('stit_id', $items["itemId"])
                    ->where('branch_id', auth_user()->br_ID)
                    ->update($insertData);
            }
        }
    }
    public function addUpdateLog($stockItems, $error)
    {
        $upload = new BranchInventoryUpload;
        $upload->fbiu_branch = auth_user()->br_ID;
        $upload->fbiu_uploadedbyapi = 1;
        $upload->fbiu_status = 1;
        $upload->fbiu_uploadedapikey = auth_user()->br_ReferenceID;
        $upload->fbiu_missingerpids = implode(",", $error);
        $upload->save();
        $items = array_map(function ($tag) {
            $updateDetails = [];
            $updateDetails["stit_id"] = $tag["itemId"];
            $updateDetails["branch_id"] = auth_user()->br_ID;
            $updateDetails["item_count"] = $tag["item_count"];
            $updateDetails["mrp"] = $tag["mrp"];
            $updateDetails["selling_price"] = $tag["selling_price"];


            return $updateDetails;
        }, $stockItems);

        $upload->details()->createMany($items);
        return $upload->fbiu_id;
    }
    public function getItems()
    {
        $stock = DB::table('finascop_stock_itemmaster')
            ->select('finascop_stock_itemmaster.stit_ID as itemId', 'stit_SKU as sku', 'item_count as count', 'mrp as market_price', 'selling_price as sell_price', 'stit_itemERPId as erpId', 'stit_GST', 'least_package_type_id')
            ->whereRaw('stit_itemERPId <> ""')
            ->where("branch_id", "=", auth_user()->br_ID)
            ->leftjoin('finascop_stock_branch_inventory', 'finascop_stock_branch_inventory.stit_id', '=', 'finascop_stock_itemmaster.stit_ID')
            ->get();

        return $stock;
    }

    public function addRequestLog($stockItems, $error)
    {
        $uploadLog = InventoryUploadLog::create([
            "branchId" => auth_user()->br_ID,
            "status"    => 1,
            "uploadedapikey"   => auth_user()->br_ReferenceID,
            "request"  => json_encode($stockItems),
            "error"  => $error
        ]);
        return @$uploadLog->id;
    }
}
