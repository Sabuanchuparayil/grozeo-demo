<?php

namespace BackOffice\Tasks;
ini_set('max_execution_time', '1800');
use BackOffice\Models\Item;
use Illuminate\Support\Arr;
use BackOffice\Models\Branch;
use BackOffice\Models\TransferRequest;
use Illuminate\Support\Facades\DB;
use BackOffice\Status\TransferRequestStatus;
use BackOffice\Models\BranchInventory;
use BackOffice\Status\InventoryStatus;
use BackOffice\Models\InventoryDetails;
use Illuminate\Support\Facades\Log;

class GenerateTransferRequests
{
    protected $branchItems;

    protected $cpdItems;
    
    public function __invoke()
    {
        $orders = $this->getRequestDetails();
        $this->createTransferRequests($orders);
    }

    /**
     * Create the actual order
     *
     * @param array $orders
     * @return void
     */
    private function createTransferRequests($orders)
    {
        DB::transaction(function () use ($orders) {
            
             //ORDER_COMPLETED,EXPIRED,DISPATCHED,PARTLY_RECEIVED,RECEIVED,
            foreach ($orders as $order) {                
                //$lastOrderNo = $this->getLastOrderNo($order['fstr_source']); //Get Latest Number
                $this->updatePendingOrderAsExpired($order['fstr_destination']);
                $orderItems = Arr::pull($order, 'transfer_items');
                
                if (empty($orderItems)) {
                    continue;
                }

                $transferRequest = TransferRequest::create(array_merge(
                    $order,
                    [
                        'fstr_uid' => $this->generateRequestNo($order['fstr_source']),                        
                    ]
                ));

                $transferRequest->requestItems()->createMany($orderItems);
            }
        });
    }

    /**
     * Prepare the order details
     *
     * @return array
     */
    private function getRequestDetails()
    {
        $branches = Branch::select('br_id', 'br_name', 'br_cpd', 'br_stocklevel')
            ->where('br_iscpd', 0)
            ->where('br_status', 'Active')
            ->get();

        $cpds = Branch::select('br_id')
            ->where('br_iscpd', 1)
            ->where('br_status', 'Active')
            ->get();

        $cpdids = $cpds->pluck('br_id');

        
        $this->cpdItems = $this->getCpdItems($cpdids); //Need fix

        

        $transferableitems = $this->cpdItems->unique('stit_id')->pluck('stit_id');




        $items = Item::select($this->selectFields())
                  ->wherein('stit_ID',$transferableitems) 
                  ->get() ; //finascop_stock_itemmaster table
        $this->branchItems = $this->getBranchItems(); //Need Fix
        $branchOrder = [];
        //loop through each branches
		
        foreach ($branches as $branch) { //From finascop_branch
            $branchLevel = $branch->br_stocklevel ?? 1;
            $branchId = $branch->br_id;
            $branchOrderItems = [];
            foreach ($items as $item) { //finascop_stock_itemmaster table
                $requiredCount = $this->getRequiredCount(
                    $this->branchItems[$branchId] ?? collect([]),
                    $item,$branchLevel); //Stock items
                $availableCount = $this->getAvailableItemCount($item->stit_id, $branch->br_cpd);
                if ($requiredCount > 0 && $availableCount > 0) {
                    $requestedCount = $availableCount > $requiredCount ? $requiredCount : $availableCount;
                    $branchOrderItems[] = [
                        'fstr_ItemId' => $item->stit_id,
                        'fstr_RequiredItemQty' => $requestedCount,
                        'fstr_ApprovedItemQty' => $requestedCount,
                        'fstrd_status' => TransferRequestStatus::TRANSFER_DETAILS_REQUESTED
                    ];
                    $this->updateAvailableItemCount($item->stit_id, $branch->br_cpd, $requestedCount); //Deduct the item count
                }
            }
            $branchOrder[] = [
                'fstr_type' => 1,
                'fstr_source' => $branch->br_cpd,
                'fstr_destination' => $branch->br_id,
                'fstr_status' => TransferRequestStatus::TRANSFER_REQUESTED,
                'transfer_items' => $branchOrderItems,
            ];
        }
        return $branchOrder;
    }

    /**
     * Update the pending orders as expired.
     *
     * @return void
     */
    protected function updatePendingOrderAsExpired($branchId)
    {
        $includeOrderStatus = [
            TransferRequestStatus::TRANSFER_REQUESTED
        ];
        ////DB::enableQueryLog();
        TransferRequest::whereIn('fstr_status', $includeOrderStatus)
            ->where('fstr_type',TransferRequestStatus::TRANSFER_INVOKED)
            ->where('fstr_destination',$branchId)
            ->update(['fstr_status' => TransferRequestStatus::TRANSFER_INVOKE_EXPIRED]);
        
    }

    /**
     * Get the select fields
     *
     * @return array
     */
    private function selectFields()
    {
        return [
            'stit_id',
            'stitl1_optimumqty',
            'stitl2_optimumqty',
            'stitl3_optimumqty',
            'stit11_minimumqty',
            'stit12_minimumqty',
            'stit13_minimumqty',
            'stit11_maximumqty',
            'stit12_maximumqty',
            'stit13_maximumqty',
        ];
    }

    /**
     * Calculate the required count for a branch item.
     *
     * @param \Illuminate\Support\Collection|null $currentBranchItems
     * @param \Illuminate\Database\Eloquent\Model $item
     * @param int $branchLevel
     * @return int
     */
    private function getRequiredCount($currentBranchItems, $item, $branchLevel)
    {
        $optimumCount = (int) $item->{"stitl" . $branchLevel . "_optimumqty"};
        $availableCount = $currentBranchItems->where('stit_id', $item->stit_id)->count();

        $requiredCount = ($availableCount < $optimumCount) ? $optimumCount - $availableCount : 0;

        return $requiredCount;
    }

    /**
     * Get the incremental order no for last order.
     *
     * @return int
     */
    private function getLastOrderNo($branchId)
    {
        $lastOrder = TransferRequest::select('fstr_uid')
            ->where('fstr_source',$branchId)
            ->where('fstr_createdOn',$branchId)
            ->WhereBetween('fstr_createdOn', [now()->format('y-m-d') . ' 00:00:00', now()->format('y-m-d') . ' 23:59:59'])
            ->orderBy('fstr_id', 'desc')
            ->first();

        return $lastOrder->fstr_uid ?? 0;
    }

    /**
     * Generate order no.
     *
     * @param int $cpdId
     * @param int $lastOrderNo
     * @return string
     */
    public function generateRequestNo($brid)
    {
        $branches = Branch::select('branch_shortname')
        ->where('br_id', $brid)
        ->first();
        
        $lastOrderNo = TransferRequest::selectraw('right(fstr_uid,3)*1 as fstr_uid ')
                ->where('fstr_source',$brid)
                ->orderBy('fstr_id', 'desc')
                ->first();
    $lastOrderNo =  $lastOrderNo->fstr_uid??0;
    return $branches->branch_shortname . '/TRQ/' . now()->format('ym') . '/' .
                str_pad(($lastOrderNo + 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Fetch available item count grouped by branch.
     *
     * @return \Illuminate\Support\Collection|BranchInventory[]
     */
    public function getCpdItems($cpdids)
    {
      

        $items = DB::table('finascop_stock_branch_inventory')
        ->selectRaw('stit_id, branch_id, sum(item_count) as item_count ')
        ->where('item_count', '>' , 0)
        ->wherein('branch_id', $cpdids)
        ->groupBy('branch_id', 'stit_id')
        ->get();

        /*$items =  BranchInventory::selectRaw('stit_id, branch_id, sum(item_count) as item_count ')
            ->get()
            ->where('item_count', '>' , 0)
            ->wherein('branch_id', $cpdids)
            ->groupBy(['branch_id','stit_id']);
        
           */

        return $items;

           
    }

    /**
     * Fetch available item count grouped by branch.
     *
     * @return \Illuminate\Support\Collection|InventoryDetails[]
     */
    public function getBranchItems()
    {
        $whereArray = [
            InventoryStatus::GODOWN_MARKED_OUTWARD,
            InventoryStatus::IN_TRANSIT_BRANCH,
            InventoryStatus::INWARD_ITEM_SCANNED,
        ];

        return InventoryDetails::select('stiid_id', 'stiid_itemmasterid as stit_id', 'cpd_branch_id as branch_id')
            ->whereIn('stiid_status', $whereArray)
            ->get()
            ->groupBy('branch_id');
    }

    /**
     * Get the available item count for a particular branch.
     *
     * @param integer $itemId
     * @param integer $cpdId
     * @return integer
     */
    public function getAvailableItemCount(int $itemId, int $cpdId)
    {       
        foreach($this->cpdItems as $cpdItem) {
            if ($itemId == $cpdItem->stit_id && $cpdId == $cpdItem->branch_id ) {
                return $cpdItem->item_count;
            }
        }
        return 0;
    }

    /**
     * Update the available item count of an item.
     *
     * @param integer $itemId
     * @param integer $cpdId
     * @param integer $count
     * @return void
     */
    protected function updateAvailableItemCount(int $itemId, int $cpdId, int $count)
    {
        
        foreach ($this->cpdItems as &$item) {
            if ($itemId == $item->stit_id && $cpdId == $item->branch_id ) {
                $item->item_count = $item->item_count - $count;
                break; 
            }
        }
    }
}
