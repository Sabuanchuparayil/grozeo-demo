<?php

namespace BackOffice\Http\Controllers\Transfer;

use BackOffice\Models\BoyOrder;
use Illuminate\Support\Facades\Log;
use BackOffice\Models\TransferOrder;
use BackOffice\Status\BoyOrderStatus;
use BackOffice\Status\InventoryStatus;
use App\Http\Responses\SuccessWithData;
use BackOffice\Models\InventoryDetails;
use BackOffice\Http\Requests\ItemProceedRequest;
use Illuminate\Support\Facades\DB;

class SingleTransferItemProceedController
{
    protected $inventoryDetails;

    protected const CPD_ORDER = 0;

    protected const CUSTOMER_ORDER = 1;

    protected const B2B_ORDER = 2;

    protected const STOCK_RETURN = 3;

    public function __construct(InventoryDetails $inventoryDetails)
    {
        $this->inventoryDetails = $inventoryDetails;
    }

    public function __invoke(ItemProceedRequest $request)
    {
        $isValid = false;
        $isOldStockExists = false;
        $msg = '';
        
        $boyOrder = $this->getBoyOrderDetails($request->boy_order_id);

        $isOldStockExists = false;
        if ($boyOrder->status != BoyOrderStatus::REVOKED) {
            $isRevoked = false;
            //$boydetails = $this->getBoyOrderDetails($boyOrder->order_pk_id);
           /* $desiredStatus =  $transferOrder->fsto_ordertype  == 0
                ? InventoryStatus::GODOWN_AVAILABLE
                : InventoryStatus::INWARD_ITEM_SCANNED;*/
           // DB::enableQueryLog(); 
            $item = $this->inventoryDetails
                ->select('stiid_id', 'stiid_expirydate', 'fsbg_id')
                ->where('stiid_itemmasterid', $request->item_id)
                ->where('stiid_barcode', $request->barcode)
                ->where('cpd_branch_id', $boyOrder->branch_id)
                ->where(function($q) {
                    $q->where('stiid_status', InventoryStatus::GODOWN_AVAILABLE)
                      ->orWhere('stiid_status', InventoryStatus::INWARD_ITEM_SCANNED)
                      ->orWhere('stiid_status', InventoryStatus::DAMAGED_AND_IN_BRANCH)
                      ->orWhere('stiid_status', InventoryStatus::BANNED_AND_IN_BRANCH);
                })               
                ->first();
            $isValid = !is_null($item);

            if ($isValid) {
                $type = $this->getTransferOrder($boyOrder->order_pk_id);
                if($type->fsto_ordertype == static::STOCK_RETURN){
                    $isValid  = true;
                    $isRevoked = false;
                    $isOldStockExists = false;
                }else{
                    $oldItems = $this->getOldItems($request->item_id, $item->stiid_expirydate, $item->fsbg_id);
                    $oldItemSCount = $oldItems->count();
                    
                    $isOldStockExists =  $oldItemSCount > 0 &&  $oldItemSCount > $request->scanned_count;

                    $msg =  $isOldStockExists
                        ? "Another item is available that has an expiry date - {$oldItems->first()->stiid_expirydate}"
                        : '';
                    $isValid  =  $isOldStockExists ? false : true;
                }
            }else{
                $msg = "The barcode is not valid";
            }
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
    protected function getBoyOrderDetails(int $boyOrderId)
    {
        return BoyOrder::where('id', $boyOrderId)
            ->select('status','branch_id','order_pk_id')
            ->first();
    }
    protected function getTransferOrder(int $TransferOrderId)
    {
        return TransferOrder::where('fsto_id', $TransferOrderId)
            ->select('fsto_ordertype')
            ->first();
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
            ->whereIn('stiid_status', [InventoryStatus::GODOWN_AVAILABLE, InventoryStatus::INWARD_ITEM_SCANNED, InventoryStatus::DAMAGED_AND_IN_BRANCH, InventoryStatus::BANNED_AND_IN_BRANCH])
            ->orderBy('stiid_expirydate')
            ->get();
    }

}
