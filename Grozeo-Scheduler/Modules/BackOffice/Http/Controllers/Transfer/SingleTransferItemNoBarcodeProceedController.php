<?php

namespace BackOffice\Http\Controllers\Transfer;

use BackOffice\Models\BoyOrder;
use BackOffice\Status\BoyOrderStatus;
use BackOffice\Status\InventoryStatus;
use App\Http\Responses\SuccessWithData;
use BackOffice\Models\BranchInventory;
use BackOffice\Http\Requests\ItemProceedNoBarcodeRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SingleTransferItemNoBarcodeProceedController
{
    protected $inventoryDetails;

    public function __construct(BranchInventory $inventoryDetails)
    {
        $this->inventoryDetails = $inventoryDetails;
    }

    public function __invoke(ItemProceedNoBarcodeRequest $request)
    {
        $isValid = false;
        $isOldStockExists = false;
        $msg = '';        

        $boydetails = $this->getBoyOrderDetails($request->boy_order_id);
        $isOldStockExists = false;
        if ($boydetails->status != BoyOrderStatus::REVOKED) {
            $isRevoked = false;
            /*$availableitemcount = $this->getTotalItemCount($request->item_id,$boydetails->branch_id);
            $isValid = $request->scanned_count > 0 && $availableitemcount->item_count >= $request->scanned_count;
            if(!$isValid){
                $msg = "There isnt enough stock available, only " . (int)$availableitemcount->item_count . " is available ";
            }*/
            $isValid = true;
        }else{
            $isRevoked = true;
            $isValid = false;
            $msg = "The order has been revoked from you";
         }

        return  new SuccessWithData([
            'is_revoked' => $isRevoked, 
            'is_valid' => $isValid,
            'is_old_stock_exists' => $isOldStockExists,
            'msg' => $msg,
        ]);
    }

    /**
     * Check if the boy order is revoked or not.
     *
     * @param integer $boyOrderId
     * @return boolean
     */
    protected function isRevokedOrder(int $boyOrderId)
    {
        return BoyOrder::where('id', $boyOrderId)
            ->where('status', BoyOrderStatus::REVOKED)
            ->exists();
    }

    /**
     * Get old items from inventory
     *
     * @param string $itemId
     * @param string $expiryDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getOldItems($itemId, $expiryDate, $groupId)
    {
        return $this->inventoryDetails
            ->select('stiid_expirydate')
            ->where('stiid_itemmasterid', $itemId)
            ->where('stiid_expirydate', '<', $expiryDate)
            ->where('fsbg_id', '!=', $groupId)
            ->whereIn('stiid_status', [InventoryStatus::GODOWN_AVAILABLE, InventoryStatus::INWARD_ITEM_SCANNED])
            ->orderBy('stiid_expirydate')
            ->get();
    }

    /**
     * Check boy order's status , branch_id.
     *
     * @param integer $boyOrderId
     * @return boolean
     */
    protected function getBoyOrderDetails(int $boyOrderId)
    {
        return BoyOrder::where('id', $boyOrderId)
            ->select('status','branch_id')
            ->first();
    }    

    protected function getTotalItemCount($itemId, $branchid)
    {
        //DB::enableQueryLog();
        $items = DB::table('finascop_stock_branch_inventory')
        ->selectRaw('sum(item_count) as item_count ')
        ->where('stit_id', '=' , $itemId)
        ->where('branch_id', '=' , $branchid)        
        ->first();
        
         return $items;
    }

}
