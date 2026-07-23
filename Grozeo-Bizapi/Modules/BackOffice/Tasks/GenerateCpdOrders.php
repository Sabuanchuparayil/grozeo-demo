<?php

namespace BackOffice\Tasks;
ini_set('max_execution_time', '1800');
use BackOffice\Models\Item;
use Illuminate\Support\Arr;
use BackOffice\Models\Branch;
use BackOffice\Models\CpdOrder;
use Illuminate\Support\Facades\DB;
use BackOffice\Status\CpdOrderStatus;
use BackOffice\Models\BranchInventory;
use BackOffice\Status\InventoryStatus;
use BackOffice\Models\InventoryDetails;
use Illuminate\Support\Facades\Log;

class GenerateCpdOrders
{
    protected $branchItems;

    protected $cpdItems;
    
    public function __invoke()
    {
        $orders = $this->getOrderDetails();
        $this->createOrder($orders);
    }

    /**
     * Create the actual order
     *
     * @param array $orders
     * @return void
     */
    private function createOrder($orders)
    {
        DB::transaction(function () use ($orders) {
            $lastOrderNo = $this->getLastOrderNo(); //Get Latest Number
            $this->updatePendingOrderAsExpired();
            
            foreach ($orders as $order) {
                $orderItems = Arr::pull($order, 'order_items');
                
                if (empty($orderItems)) {
                    continue;
                }

                $cpdOrder = CpdOrder::create(array_merge(
                    $order,
                    [
                        'order_no' => $this->generateOrderNo($order['cpd_id'], ++$lastOrderNo),
                        'order_no_last_id' => $lastOrderNo,
                    ]
                ));

                $cpdOrder->orderItems()->createMany($orderItems);
            }
        });
    }

    /**
     * Prepare the order details
     *
     * @return array
     */
    private function getOrderDetails()
    {
        $branches = Branch::select('br_id', 'br_name', 'br_cpd', 'br_stocklevel')
            ->where('br_iscpd', 0)
            ->where('br_status', 'Active')
            ->get();
        $items = Item::all($this->selectFields()); //finascop_stock_itemmaster table
        $this->cpdItems = $this->getCpdItems(); //Need fix
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
                        'stit_id' => $item->stit_id,
                        'bcod_count' => $requestedCount,
                    ];
                    $this->updateAvailableItemCount($item->stit_id, $branch->br_cpd, $requestedCount); //Deduct the item count
                }
            }
            $branchOrder[] = [
                'cpd_id' => $branch->br_cpd,
                'branch_id' => $branch->br_id,
                'order_status' => CpdOrderStatus::MANUAL_QUEUED,
                'order_items' => $branchOrderItems,
            ];
        }
        return $branchOrder;
    }

    /**
     * Update the pending orders as expired.
     *
     * @return void
     */
    protected function updatePendingOrderAsExpired()
    {
        $excludeOrderStatus = [
            CpdOrderStatus::ORDER_COMPLETED,
            CpdOrderStatus::EXPIRED,
            CpdOrderStatus::DISPATCHED,
            CpdOrderStatus::PARTLY_RECEIVED,
            CpdOrderStatus::RECEIVED,
        ];

        CpdOrder::whereNotIn('order_status', $excludeOrderStatus)
            ->update(['order_status' => CpdOrderStatus::EXPIRED]);
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
    private function getLastOrderNo()
    {
        $lastOrder = CpdOrder::select('order_no_last_id')
            ->orderBy('order_no_last_id', 'desc')
            ->first();

        return $lastOrder->order_no_last_id ?? 0;
    }

    /**
     * Generate order no.
     *
     * @param int $cpdId
     * @param int $lastOrderNo
     * @return string
     */
    public function generateOrderNo($cpdId, $lastOrderNo)
    {
        return 'PKT' . 
            str_pad($cpdId, 3, '0', STR_PAD_LEFT) .
            str_pad($lastOrderNo, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Fetch available item count grouped by branch.
     *
     * @return \Illuminate\Support\Collection|BranchInventory[]
     */
    public function getCpdItems()
    {
        return BranchInventory::select('stit_id', 'branch_id', 'item_count')
            ->get()
            ->groupBy('branch_id');
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
        if(!isset($this->cpdItems[$cpdId])) {
            return 0;
        }

        return $this->cpdItems[$cpdId]->firstWhere('stit_id', $itemId)->item_count ?? 0;
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
        $items = $this->cpdItems[$cpdId];

        foreach ($items as &$item) {
            if ($item->stit_id == $itemId) {
                $item->item_count = $item->item_count - $count;
                break; 
            }
        }
    }
}
