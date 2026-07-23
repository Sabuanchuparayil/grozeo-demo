<?php

namespace App\Http\Controllers;

use BackOffice\Models\Item;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use BackOffice\Models\Branch;
use BackOffice\Models\CpdOrder;
use Illuminate\Support\Facades\DB;
use BackOffice\Models\CpdOrderItems;
use BackOffice\Status\CpdOrderStatus;
use BackOffice\Models\BranchInventory;
use App\Http\Responses\SuccessResponse;
use BackOffice\Tasks\GenerateCpdOrders;

class TestController extends Controller
{

    protected $branchItems;
    protected $cpdItems;
    public function index()
    {
        $order = new GenerateCpdOrders;

        $order();

        // $orders = $this->getOrderforcenterStoretoRetailer();
        // $this->createOrderforcenterStoretoRetailer($orders);


        return new SuccessResponse('Cpd orders generated successfully');
    }



    public function getCpdItems()
    {
        return BranchInventory::select('stit_id', 'branch_id', 'item_count')
            ->get()
            ->groupBy('branch_id');
    }



    private function getOrderforcenterStoretoRetailer()
    {


        $distributors = Branch::select('br_id', 'br_name', 'br_cpd', 'br_stocklevel')
            ->where('br_PyramidLevel', 3)
            ->get();
        $items = Item::all($this->selectFields());
        $this->cpdItems = $this->getCpdItems();
        $this->branchItems = $this->getBranchItems();
        foreach ($distributors as $distributor) {

            $retailers = Branch::select('br_id', 'br_name', 'br_cpd', 'br_stocklevel')->where('br_PyramidLevel', 4)->where('br_cpd', $distributor->br_id)
                ->get();



            foreach ($retailers as $retailer) {

                $branchLevel = $retailer->br_stocklevel ?? 1;
                $branchId = $retailer->br_id;
                $branchOrderItems = [];
                foreach ($items as $item) {
                    $requiredCount = $this->getRequiredCount(
                        $this->branchItems[$branchId] ?? collect([]),
                        $item,
                        $branchLevel
                    );

                    $availableCount = $this->getAvailableItemCount($item->stit_id,$distributor->br_cpd);



    if ($requiredCount > 0 && $availableCount > 0) {

                    $requestedCount = $availableCount > $requiredCount ? $requiredCount : $availableCount;



                    $branchOrderItems[] = [
                        'stit_id' => $item->stit_id,
                        'bcod_count' => $requestedCount,
                    ];

                   $this->updateAvailableItemCount($item->stit_id,$distributor->br_cpd,$requestedCount);

                }

                }
                //inventory_status

                $branchOrder[] = [
                    'cpd_id' =>  $distributor->br_cpd,          //centerstore                      // to
                    'branch_id' => $distributor->br_id,   //retailer           // from
                    'order_status' => CpdOrderStatus::MANUAL_QUEUED, //order
                    'order_items' => $branchOrderItems,
                    'br_PyramidLevel' => 3
                ];

                $branchOrder1[] = [
                    'cpd_id' => $distributor->br_id,  // distributor          //to
                    'branch_id' =>$retailer->br_id,        //centerstore        //from
                    'order_status' => CpdOrderStatus::MANUAL_QUEUED, //order
                    'order_items' => $branchOrderItems,
                    'br_PyramidLevel' => 4
                ];


                // $branchOrder1[] = [
                //     'cpd_id' => $retailer->br_cpd,             // centerstore          //to
                //     'branch_id' => $distributor->br_id,        //distribor        //from
                //     'order_status' => CpdOrderStatus::MANUAL_QUEUED, //order
                //     'order_items' => $branchOrderItems,
                //     'br_PyramidLevel' => 1
                // ];
            }


        }

        return array($branchOrder, $branchOrder1);
    }


    private function createOrderforcenterStoretoRetailer($orderss)
    {
        $this->updatePendingOrderAsExpired();
        foreach ($orderss as $orders) {
            DB::transaction(function () use ($orders) {
                $lastOrderNo = $this->getLastOrderNo();





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
     * Update the pending orders as expired.
     *
     * @return void
     */
    protected function updatePendingOrderAsExpired1()
    {

        $excludeOrderStatus = [
            CpdOrderStatus::ORDER_COMPLETED,
            CpdOrderStatus::EXPIRED,
            CpdOrderStatus::DISPATCHED,
            CpdOrderStatus::PARTLY_RECEIVED,
            CpdOrderStatus::RECEIVED,
        ];






        CpdOrder::whereNotIn('order_status', $excludeOrderStatus)->where('br_PyramidLevel',2)
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
        if (($branchItem = $currentBranchItems->firstWhere('stit_id', $item->stit_id))) {
            $requiredCount = ($branchItem->item_count < $optimumCount) ?
                $optimumCount - $branchItem->item_count : 0;
        } else {
            $requiredCount = $optimumCount;
        }


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
    protected function getBranchItems()
    {
        return BranchInventory::select('stit_id', 'branch_id', 'item_count')
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

        if (!isset($this->cpdItems[$cpdId])) {
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

        $items = isset($this->cpdItems[$cpdId])?$this->cpdItems[$cpdId]:null;
if($items!=null)
{
    foreach ($items as &$item) {
        if ($item->stit_id == $itemId) {
            $item->item_count = $item->item_count - $count;
            break;
        }
    }
}

    }
}


/////////////////





