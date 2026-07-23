<?php

namespace BackOffice\Http\Controllers\Boy;

use DB;
use Illuminate\Http\Request;
use BackOffice\Models\User;
use BackOffice\Models\Branch;
use App\Exceptions\MsgException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessWithData;
use BackOffice\Http\Requests\BoyBranchInventoryRequest;
use BackOffice\Models\BranchInventory;
use App\Http\Responses\SuccessResponse;

class BoyBranchInventoryController {

    protected $branchInventory;

    public function __construct(BranchInventory $branchInventory = null) {
        $this->branchInventory = $branchInventory ?? new BranchInventory;
    }

    public function getBranchStatus(BoyBranchInventoryRequest $request) {
        $data = Branch::where('br_Id', $request->branch_id)
                ->select('br_SalesOnline', 'br_SalesOffline')
                ->first();
        return new SuccessWithData(
                $data
        );
    }

    public function updateBranchesStatus(Request $request) {
        if (!isset($request->branch_id) || intval($request->branch_id) == 0) {
            return new ErrorResponse("Invalid brid");
        }
        if (!isset($request->enable) || (intval($request->enable) != 0 && intval($request->enable) != 1)) {
            return new ErrorResponse("Specify 1/0 to enable or disable branch");
        }

        $brdata = Branch::where('br_ID', $request->branch_id)
                ->select('br_ID', 'br_Name', 'br_ReferenceID')
                ->first();

        if (!isset($brdata->br_ID) || intval($brdata->br_ID) == 0) {
            return new ErrorResponse("Invalid branch_id");
        }

        //$brdata = Branch::where('br_ID', $request->branch_id)
        //->update(['br_SalesOnline' => ($request->enable == '1' ? 1 : 0), 'br_SalesOffline' => ($request->enable == '1' ? 0 : 1)]);
        DB::table('finascop_branch')->where('br_ID', $request->branch_id)->update(array('br_SalesOnline' => ($request->enable == '1' ? 1 : 0), 'br_SalesOffline' => ($request->enable == '1' ? 0 : 1)));

        DB::table('branch_timings')->where('branch_id', $request->branch_id)->update(array('updatedOn' => date('Y-m-d H:i:s')));

        return new SuccessResponse("Saved successfully.");
    }

    public function getBranchInventory(BoyBranchInventoryRequest $request) {
        
        $boy = auth_user();
        if(@$boy->allowInventoryControl == 0)
        {
            return new ErrorResponse("Invalid permissions");
        }
        $stock = DB::table('finascop_stock_itemmaster')
                ->select('finascop_stock_itemmaster.stit_ID as itemId', 'stit_SKU as sku', 'item_count as count', 'mrp as market_price', 'selling_price as sell_price', 'stit_itemERPId as erpId', 'stit_GST', 'least_package_type_id', 'id', 'isAvailable','isOnDemand') 
                //->orderBy('isAvailable', 'asc')
                //->whereRaw('stit_itemERPId <> ""')
                ->where("branch_id", "=", $request->branch_id)
                ->where('stit_SKU', 'like', "%{$request->product_name}%")
                ->join('finascop_stock_branch_inventory', 'finascop_stock_branch_inventory.stit_id', '=', 'finascop_stock_itemmaster.stit_ID')
                ->get();
        return new SuccessWithData($stock);
    }

    public function updateBrancheItems(Request $request) {

        $boy = auth_user();
        if(@$boy->allowInventoryControl == 0)
        {
            return new ErrorResponse("Invalid permissions");
        }

        if (!isset($request->branch_id) || intval($request->branch_id) == 0) {
            return new ErrorResponse("Invalid brid");
        }
        if (!isset($request->id) || intval($request->id) == 0) {
            return new ErrorResponse("Invalid Stock");
        }
        if (!isset($request->isOnDemand) || intval($request->isOnDemand) > 0) {
            $item_count = 1000;
        }else{
            $item_count = $request->item_count;
        }
        $this->branchInventory
                ->where('stit_id', $request->itemId)
                ->where('branch_id', $request->branch_id)
                ->where('id', $request->id)
                ->update([
                    'isAvailable' => ($request->enable == '1' ? 1 : 0),
                    'item_count' => ($request->enable == '1' ? $item_count : 0),
                    'updated_on' => date("Y-m-d H:i:s")
        ]);


        return new SuccessResponse("Saved successfully.");
    }

}
