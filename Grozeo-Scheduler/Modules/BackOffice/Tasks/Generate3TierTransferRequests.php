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

class Generate3TierTransferRequests
{
    protected $retailerItems;

    protected $distributorItems;

    protected $distributorItemsForRetailer;

    protected $centralstoreItems;
    
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
        $distributors = Branch::select('br_id', 'br_name', 'br_cpd', 'br_stocklevel')
        ->where('br_PyramidLevel', 3)
        ->where('br_status', 'Active')
        ->get();

        $centralstores = Branch::select('br_id', 'br_name', 'br_cpd', 'br_stocklevel')
        ->where('br_PyramidLevel', 2)
        ->where('br_status', 'Active')
        ->get();       

        $centralstoreids = $centralstores->pluck('br_id');        
        $this->centralstoreItems = $this->getCentralStoreItems($centralstoreids); //Need fix
        $centralstoretransferableitems = $this->centralstoreItems->unique('stit_id')->pluck('stit_id');
        $centralstoreitems = Item::select($this->selectFields())
                  ->wherein('stit_ID',$centralstoretransferableitems) 
                  ->get() ; //finascop_stock_itemmaster table

        $distributorids = $distributors->pluck('br_id'); 
        $this->distributorItems = $this->getDistributorItems($distributorids); //Need Fix
        $branchOrder = [];

        //loop through each Distributor		
        foreach ($distributors as $distributor) { //From finascop_branch
            $branchLevel = $distributor->br_stocklevel ?? 1;
            $branchId = $distributor->br_id;
            $branchOrderItems = [];
            foreach ($centralstoreitems as $item) { //finascop_stock_itemmaster table
                $requiredCount = $this->getRequiredCount(
                    $this->distributorItems,$branchId ,
                    $item,$branchLevel); //Stock items
                $availableCount = $this->getAvailableCentralStoreItemCount($item->stit_id, $distributor->br_cpd);
                if ($requiredCount > 0 && $availableCount > 0) {
                    $requestedCount = $availableCount > $requiredCount ? $requiredCount : $availableCount;
                    $branchOrderItems[] = [
                        'fstr_ItemId' => $item->stit_id,
                        'fstr_RequiredItemQty' => $requestedCount,
                        'fstr_ApprovedItemQty' => $requestedCount,
                        'fstrd_status' => TransferRequestStatus::TRANSFER_DETAILS_REQUESTED
                    ];
                    $this->updateAvailableCentralItemCount($item->stit_id, $distributor->br_cpd, $requestedCount); //Deduct the item count
                }
            }
            $branchOrder[] = [
                'fstr_type' => 1,
                'fstr_source' => $distributor->br_cpd,
                'fstr_destination' => $distributor->br_id,
                'fstr_status' => TransferRequestStatus::TRANSFER_REQUESTED,
                'transfer_items' => $branchOrderItems,
            ];
        }

        $retailers = Branch::select('br_id', 'br_name', 'br_cpd', 'br_stocklevel')
        ->where('br_PyramidLevel', 4)
        ->where('br_status', 'Active')
        ->get();
        
        $retailerids = $retailers->pluck('br_id'); 
        $distributorids = $distributors->pluck('br_id'); 
        $this->distributorItemsForRetailer = $this->getDistributorItems($distributorids); //Need Fix

        $distributortransferableitems = $this->distributorItemsForRetailer->unique('stit_id')->pluck('stit_id');
        $distributoritems = Item::select($this->selectFields())
                  ->wherein('stit_ID',$distributortransferableitems) 
                  ->get() ; //finascop_stock_itemmaster table
                  
      
        $this->retailerItems = $this->getRetailerBranchItems($retailerids); //Need Fix

        foreach ($retailers as $retailer) { //From finascop_branch
        $branchLevel = $retailer->br_stocklevel ?? 1;
        $branchId = $retailer->br_id;
        $branchOrderItems = [];
        foreach ($distributoritems as $item) { //finascop_stock_itemmaster table

            $requiredCount = $this->getRequiredCount(
                $this->retailerItems,$branchId,
                $item,$branchLevel); //Stock items
            $availableCount = $this->getAvailabledistributorItemCount($item->stit_id, $retailer->br_cpd);
            if ($requiredCount > 0 && $availableCount > 0) {
                $requestedCount = $availableCount > $requiredCount ? $requiredCount : $availableCount;
                $branchOrderItems[] = [
                    'fstr_ItemId' => $item->stit_id,
                    'fstr_RequiredItemQty' => $requestedCount,
                    'fstr_ApprovedItemQty' => $requestedCount,
                    'fstrd_status' => TransferRequestStatus::TRANSFER_DETAILS_REQUESTED
                ];
                $this->updateAvailabledistributorItemCount($item->stit_id, $retailer->br_cpd, $requestedCount); //Deduct the item count
            }
        
        }
        $branchOrder[] = [
            'fstr_type' => 1,
            'fstr_source' => $retailer->br_cpd,
            'fstr_destination' => $retailer->br_id,
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
    private function getRequiredCount($currentBranchItems,$branch_id, $item, $branchLevel)
    {
        $optimumCount = (int) $item->{"stitl" . $branchLevel . "_optimumqty"};
        $availableCount =0;
        foreach ($currentBranchItems as $currentBranchItem){
            if($currentBranchItem->branch_id == $branch_id && $item->stit_id == $currentBranchItem->stit_id){
                $availableCount = $currentBranchItem->item_count;
            }
        }     
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
    public function getCentralStoreItems($cpdids)
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
    public function getDistributorItems($branchid)
    {
        $whereArray = [
            InventoryStatus::GODOWN_MARKED_OUTWARD,
            InventoryStatus::IN_TRANSIT_BRANCH,
            InventoryStatus::GODOWN_AVAILABLE,
        ];
        //finascop_stock_item_inventorydetails
        $data = DB::table('finascop_stock_item_inventorydetails')
             ->selectRaw('stiid_id,stiid_itemmasterid as stit_id,cpd_branch_id as branch_id,count(*) as item_count')
            ->whereIn('stiid_status', $whereArray)
            ->whereIn('cpd_branch_id', $branchid)
            ->groupBy('branch_id')
            ->groupBy('stit_id')
            ->get();

        return $data;
    }
    /**
     * Fetch available item count grouped by branch.
     *
     * @return \Illuminate\Support\Collection|InventoryDetails[]
     */
    public function getRetailerBranchItems($branchid)
    {
        $whereArray = [
            InventoryStatus::GODOWN_MARKED_OUTWARD,
            InventoryStatus::IN_TRANSIT_BRANCH,
            InventoryStatus::INWARD_ITEM_SCANNED,
        ];

        $data =  DB::table('finascop_stock_item_inventorydetails')
             ->selectRaw('stiid_id,stiid_itemmasterid as stit_id,cpd_branch_id as branch_id,count(*) as item_count')
            ->whereIn('stiid_status', $whereArray)
            ->whereIn('cpd_branch_id', $branchid)
            ->groupBy('branch_id')
            ->groupBy('stit_id')
            ->get();

        
        return $data;
    }

    /**
     * Get the available item count for a particular branch.
     *
     * @param integer $itemId
     * @param integer $cpdId
     * @return integer
     */
    public function getAvailableCentralStoreItemCount(int $itemId, int $cpdId)
    {       
        foreach($this->centralstoreItems as $cpdItem) {
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
    protected function updateAvailableCentralItemCount(int $itemId, int $cpdId, int $count)
    {
        
        foreach ($this->centralstoreItems as &$item) {
            if ($itemId == $item->stit_id && $cpdId == $item->branch_id ) {
                $item->item_count = $item->item_count - $count;
                break; 
            }
        }
    }

        /**
     * Get the available item count for a particular branch.
     *
     * @param integer $itemId
     * @param integer $cpdId
     * @return integer
     */
    public function getAvailabledistributorItemCount(int $itemId, int $cpdId)
    {       
        foreach($this->distributorItemsForRetailer as $cpdItem) {
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
    protected function updateAvailabledistributorItemCount(int $itemId, int $cpdId, int $count)
    {
        
        foreach ($this->distributorItemsForRetailer as &$item) {
            if ($itemId == $item->stit_id && $cpdId == $item->branch_id ) {
                $item->item_count = $item->item_count - $count;
                break; 
            }
        }
    }
}
