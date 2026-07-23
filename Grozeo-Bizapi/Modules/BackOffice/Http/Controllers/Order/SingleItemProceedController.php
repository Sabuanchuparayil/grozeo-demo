<?php

namespace BackOffice\Http\Controllers\Order;

use BackOffice\Models\BoyOrder;
use BackOffice\Status\BoyOrderStatus;
use BackOffice\Status\InventoryStatus;
use App\Http\Responses\SuccessWithData;
use BackOffice\Models\InventoryDetails;
use BackOffice\Http\Requests\ItemProceedRequest;
use Illuminate\Support\Facades\Log;

class SingleItemProceedController
{
    protected $inventoryDetails;

    public function __construct(InventoryDetails $inventoryDetails)
    {
        $this->inventoryDetails = $inventoryDetails;
    }

    public function __invoke(ItemProceedRequest $request)
    {
        $isValid = false;
        $isOldStockExists = false;
        $msg = '';
        
        $isRevoked = $this->isRevokedOrder($request->boy_order_id);

        if (!$isRevoked) {
            $desiredStatus = $request->type  == 0
                ? InventoryStatus::GODOWN_AVAILABLE
                : InventoryStatus::INWARD_ITEM_SCANNED;

            $item = $this->inventoryDetails
                ->select('stiid_id', 'stiid_expirydate', 'fsbg_id')
                ->where('stiid_itemmasterid', $request->item_id)
                ->where('stiid_barcode', $request->barcode)
                ->where(function($q) {
                    $q->where('stiid_status', InventoryStatus::GODOWN_AVAILABLE)
                      ->orWhere('stiid_status', InventoryStatus::INWARD_ITEM_SCANNED);
                })               
                ->first();
            
            $isValid = !is_null($item);

            if ($isValid) {
                $oldItems = $this->getOldItems($request->item_id, $item->stiid_expirydate, $item->fsbg_id);
                $oldItemSCount = $oldItems->count();
                
                $isOldStockExists =  $oldItemSCount > 0 &&  $oldItemSCount > $request->scanned_count;

                $msg =  $isOldStockExists
                    ? "Another item is available that has an expiry date - {$oldItems->first()->stiid_expirydate}"
                    : '';
            }
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

}
