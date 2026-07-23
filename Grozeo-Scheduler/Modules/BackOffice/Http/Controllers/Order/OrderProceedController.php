<?php

namespace BackOffice\Http\Controllers\Order;

use Exception;
use App\Models\Order;
use BackOffice\Models\User;
use Illuminate\Support\Arr;
use App\Events\OrderHistory;
use App\Models\BlockedItems;
use BackOffice\Models\B2bOrder;
use BackOffice\Models\BoyOrder;
use BackOffice\Models\CpdOrder;
use App\Exceptions\MsgException;
use BackOffice\Models\GodownBoy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Responses\ErrorResponse;
use BackOffice\Status\B2bOrderStatus;
use BackOffice\Status\BoyOrderStatus;
use BackOffice\Status\CpdOrderStatus;
use BackOffice\Models\BranchInventory;
use BackOffice\Status\InventoryStatus;
use App\Http\Responses\SuccessWithData;
use BackOffice\Models\InventoryDetails;
use BackOffice\Status\CustomerOrderStatus;
use Illuminate\Database\Eloquent\Collection;
use BackOffice\Http\Repositories\ReduceStock;
use BackOffice\Responses\ItemProceedResponse;
use BackOffice\Actions\Inventory\QugeoProcessor;

use BackOffice\Http\Requests\OrderProceedRequest;
use BackOffice\Actions\Inventory\RecordItemHistory;
use BackOffice\Actions\Inventory\ProcessInventoryItems;

class OrderProceedController
{
    /**
     * Inventory deails model.
     *
     * @var \BackOffice\Models\InventoryDetails
     */
    protected $inventoryDetails;

    /**
     * Item processor
     *
     * @var \BackOffice\Actions\Inventory\ProcessInventoryItems
     */
    protected $processItems;

    /**
     * Order model
     *
     * @var CpdOrder|Order
     */
    protected $model;

    /**
     * Order model
     *
     * @var CpdOrder|Order
     */
    protected $order;

    protected $orderField;

    protected $intOrderId;

    protected const CPD_ORDER = 0;

    protected const CUSTOMER_ORDER = 1;

    protected const B2B_ORDER = 2;

    protected $recordItemHistory;

    protected $qugeoProcessor;

    protected $inventory;

    public function __construct(InventoryDetails $inventoryDetails, ProcessInventoryItems $processItems, RecordItemHistory $recordItemHistory, QugeoProcessor $qugeoProcessor, BranchInventory $inventory)
    {
        $this->inventoryDetails = $inventoryDetails;
        $this->processItems = $processItems;
        $this->recordItemHistory = $recordItemHistory;
        $this->qugeoProcessor = $qugeoProcessor;
        $this->inventory = $inventory;
    }

    public function __invoke(OrderProceedRequest $request, $orderId)
    {
         
        $isValid = false;

        $this->checkOrderId($orderId, $request->type);

        if ($this->isRevokedOrder($request->boy_order_id)) {
            return response()->json([
                'status' => 'mismatch',
                'data' => [
                    'mismatched' => [],
                    'is_revoked' => true,
                ],
            ]);
        }
        
        $barcodes = Arr::collapse(Arr::pluck($request->items, 'barcodes'));
        $inventoryItems = $this->getInventoryItems($barcodes, $request->type);
        $mismatched = $this->processItems->process($request->items, $inventoryItems);
       
        if (empty($mismatched)) {
            $this->checkBarcodeCount($inventoryItems, $request->type);
            $this->checkItemCount($inventoryItems, $request->type);
            
            DB::transaction(function () use ($barcodes, $request, $inventoryItems, $orderId) {
                $this->updateItemsAsMarked($barcodes, $request->type);
                $this->saveItemsBarcode($inventoryItems, $request->type);
                $this->recordItemHistory->record($inventoryItems, $request->type, $this->intOrderId);
                $this->markOrderAsCompleted($orderId, $request->type, $request->number_bags);
                $this->freeUpGodownBoy(auth_user());

                 if($request->type == static::CUSTOMER_ORDER){
                        $this->addOrderToQugeo();
                 

                    ReduceStock::minusStock($this->intOrderId);
                }else {
                    $this->decreaseItemCount($inventoryItems,$request->type);
                } 

            });
        }else{
        }

        return new ItemProceedResponse($mismatched);
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
     * Check if an order is valid.
     *
     * @param string $orderId
     * @param integer $type
     * @throws Exception
     * @return void
     */
    protected function checkOrderId(string $orderId, int $type)
    {
        if($type === static::CPD_ORDER){
             $this->model =new CpdOrder;
             $this->orderField = 'order_no';
        }else{
            if($type === static::CUSTOMER_ORDER){
             $this->model =new Order;
             $this->orderField = 'order_order_id';
            }else{
             $this->model =new B2bOrder;
             $this->orderField = 'bbso_SONumber';
            }       
        }


       /* $this->model = $type === static::CPD_ORDER
            ? new CpdOrder
            : $type === static::CUSTOMER_ORDER? new Order : new B2bOrder;

        $this->orderField = $type === static::CPD_ORDER
            ? 'order_no'
            : $type === static::CUSTOMER_ORDER ?  'order_order_id' : 'bbso_SONumber';
            */

        $this->order = $this->model->where($this->orderField, $orderId)->first();

        if (is_null($this->order)) {
            throw new MsgException('Invalid order id');
        }

        if($type === static::CPD_ORDER){
            $this->intOrderId = $this->order->order_id;
        }else{
            if($type === static::CUSTOMER_ORDER){
                $this->intOrderId = $this->order->order_id;
            }else{
                $this->intOrderId = $this->order->bbso_id;
            }       
        }

        
    }

    protected function checkBarcodeCount(Collection $inventoryItems, int $type)
    {

        if ($type === static::CPD_ORDER) {
            return;
        }
        if ($type === static::CUSTOMER_ORDER) {
            $scannedItems = $inventoryItems->groupBy('stiid_itemmasterid');
            $orderItems = $this->order->orderItems;
            foreach ($orderItems as $item) {
                if (
                    !isset($scannedItems[$item->item_product_id]) 
                    || $scannedItems[$item->item_product_id]->count() != $item->item_order_qty
                ) {
                    throw new MsgException('Item count and/or items mismatch');
                }
            }
        }
        if ($type === static::B2B_ORDER) {

            $scannedItems = $inventoryItems->groupBy('stiid_itemmasterid');
            $orderItems = $this->order->orderItems;
            foreach ($orderItems as $item) {
               
                if (
                    !isset($scannedItems[$item->b2bso_itemid]) 
                    || $scannedItems[$item->b2bso_itemid]->count() != $item->b2bso_itemqty
                ) {
                    throw new MsgException ('Item count and/or items mismatch B2B_ORDER');
                }
            }
        }
    }

    /**
     * Get inventory item details for given barcodes
     *
     * @param array $barcodes
     * @param integer $type
     * @return \Illuminate\Support\Collection
     */
    protected function getInventoryItems(array $barcodes, int $type)
    {
        $status = $type === static::CPD_ORDER
            ? InventoryStatus::GODOWN_AVAILABLE
            : InventoryStatus::INWARD_ITEM_SCANNED;

        if($type === static::CPD_ORDER){
            $cpdBranchId = 'cpd_id';
        }else{
            if($type === static::CUSTOMER_ORDER){
                $cpdBranchId = 'order_branch_id';
            }else{
                $cpdBranchId = 'br_ID';
            }       
        }         
       /* $cpdBranchId = $type === static::CPD_ORDER
            ? 'cpd_id'
            : ($type === static::CUSTOMER_ORDER)? 'order_branch_id' : 'br_ID';
        */

         
        return $this->inventoryDetails
            ->select('stiid_id', 'stii_id', 'stiid_barcode', 'stiid_itemmasterid', 'fsbg_id','stiid_itemmastername')
            ->whereIn('stiid_barcode', $barcodes)
            ->where('cpd_branch_id', $this->order->{$cpdBranchId})
            ->where(function($q) {
                $q->where('stiid_status', InventoryStatus::GODOWN_AVAILABLE)
                  ->orWhere('stiid_status', InventoryStatus::INWARD_ITEM_SCANNED);
            })  
            ->get();
    }

    /**
     * Update items in inventory as marked
     *
     * @param array $barcodes
     * @param integer $type
     * @return \Illuminate\Support\Collection
     */
    protected function updateItemsAsMarked(array $barcodes, int $type)
    {
        $status = $type === static::CPD_ORDER
            ? InventoryStatus::GODOWN_MARKED_OUTWARD
            : InventoryStatus::IN_DELIVERY_CART;

        if($type === static::CPD_ORDER){
            $branchId = $this->order->branch_id;
            $order_field= 'cpd_order_id';

        }else{
            if($type === static::CUSTOMER_ORDER){
                $branchId = $this->order->order_branch_id;
                $order_field= 'cust_order_id';
            }else{
                $branchId = $this->order->br_ID;
                $order_field= 'b2b_order_id';
            }       
        }     

       /* $branchId = $type === static::CPD_ORDER
            ? $this->order->branch_id
            : ($type === static::CUSTOMER_ORDER) ? $this->order->order_branch_id
            : $this->order->br_ID;
        */

        $fields = [
            'stiid_status' => $status,
            'is_branch' => 1,
            'cpd_branch_id' => $branchId,
        ];


/*        $fields[static::CPD_ORDER === $type ? 'cpd_order_id' :(static::CUSTOMER_ORDER === $type)? 'cust_order_id':'b2b_order_id'] = $this->intOrderId;
*/
        $fields[$order_field] = $this->intOrderId;

        return $this->inventoryDetails
            ->whereIn('stiid_barcode', $barcodes)
            ->update($fields);
    }

    /**
     * Mark the order as completed
     *
     * @param string $orderId
     * @param integer $type
     * @return void
     */
    protected function markOrderAsCompleted(string $orderId, int $type, int $number_bags)
    {
       /* $statusField = $type === static::CPD_ORDER
            ? 'order_status'
            : 'status_id';
        */

        if($type === static::CPD_ORDER){
            $statusField = 'order_status';
            $status= CpdOrderStatus::ORDER_COMPLETED;

        }else{
            if($type === static::CUSTOMER_ORDER){
                $statusField = 'status_id';
                $status= CustomerOrderStatus::READY_FOR_DELIVERY;
            }else{
                $statusField = 'status_id';
                $status= B2bOrderStatus:: READY_FOR_DELIVERY;
            }       
        } 

     /*   $status = $type === static::CPD_ORDER
            ? CpdOrderStatus::ORDER_COMPLETED
            : $type === static::CPD_ORDER ? CustomerOrderStatus::READY_FOR_DELIVERY :B2bOrderStatus:: READY_FOR_DELIVERY;*/

        if ($type == static::CUSTOMER_ORDER) {
            $this->order
                ->update([$statusField => $status, 'order_packedbags_count' => $number_bags]);
                event(new OrderHistory($this->intOrderId, CustomerOrderStatus::READY_FOR_DELIVERY));
        } else {
            $this->order
                ->update([$statusField => $status]);
        }
    }

    /**
     * Free up the godown boy from the completed order.
     *
     * @param GodownBoy $boy
     * @return void
     */
    protected function freeUpGodownBoy(User $boy)
    {
        $boy->update(['has_open_orders' => 0]);
    }

    protected function decreaseItemCount(Collection $inventoryItems,$type=0)
    {
        $items = $inventoryItems->groupBy(function ($item) {
            return (int) $item->stiid_itemmasterid;
        });


        $groupIds = $inventoryItems->pluck('fsbg_id')->toArray();

        $cpdId = $this->order->cpd_id;
        if($type==static::B2B_ORDER){
            $cpdId = $this->order->br_ID;
        }

        $stockInventoryItems = $this->inventory
            ->whereIn('stit_id', $items->keys())
            ->whereIn('fsbg_id', $groupIds)
            ->where('branch_id', $cpdId)
            ->get();

        // $stockInventoryItems = $stockInventoryItems->groupBy(function ($stock) {
        //     return $stock->fsbg_id . $stock->stit_id;
        // });
            
        foreach ($items as $itemId => $item) {
            $groupedItem = $item->groupBy('fsbg_id');
            foreach ($groupedItem as $groupId => $items) {
                $this->inventory
                    ->where('stit_id', $itemId)
                    ->where('branch_id', $cpdId)
                    ->where('fsbg_id', $groupId)
                    ->update([
                        'item_count' => $this->getStockCount($stockInventoryItems, $groupId) - $groupedItem[$groupId]->count()
                    ]);
            }
        }

        
        
        // $inventoryItems = $this->inventory
        //     ->whereIn('stit_id', $items->keys())
        //     ->where('branch_id', $cpdId)
        //     ->get();

        // foreach ($inventoryItems as $inventoryItem) {
        //     $inventoryItem->update([
        //         'item_count' => $inventoryItem->item_count - $items[$inventoryItem->stit_id]->count()
        //     ]);
        // }
    }

    protected function checkItemCount(Collection $inventoryItems,$type=0)
    {
        $items = $inventoryItems->groupBy(function ($item) {
            return (int) $item->stiid_itemmasterid;
        });



        $groupIds = $inventoryItems->pluck('fsbg_id')->toArray();

        if($type === static::CPD_ORDER){
             $cpdId = $this->order->cpd_id;
        }else{
            if($type === static::CUSTOMER_ORDER){
                $cpdId = $this->order->order_branch_id;
            }else{
                 $cpdId = $this->order->br_ID;
            }       
        }

        $stockInventoryItems = $this->inventory
            ->whereIn('stit_id', $items->keys())
            ->whereIn('fsbg_id', $groupIds)
            ->where('branch_id', $cpdId)
            ->get();
        $blockedItems=BlockedItems::where('branch_id', $cpdId)
            ->whereIn("item_id", $items->keys())
            ->get();    

            
        foreach ($items as $itemId => $item) {
            
            $groupedItem = $item->groupBy('fsbg_id');
            foreach ($groupedItem as $groupId => $items) {
                if(($this->getStockCount($stockInventoryItems, $groupId) +$this->getBlockedStockCount($blockedItems, $itemId)) < $groupedItem[$groupId]->count()){
                    throw new MsgException('The item - '.$items[0]["stiid_itemmastername"].' count is more than what is available in stock.');

                }
                
            }
        }

    }

    protected function getStockCount(Collection $stock, $groupId)
    {
        return $stock->where('fsbg_id', $groupId)
            ->sum('item_count');
    }
    protected function getBlockedStockCount(Collection $blockstock, $itemId)
    {
        return $blockstock->where('item_id', $itemId)
            ->sum('count');
    }
    
    protected function addOrderToQugeo()
    {
        $this->qugeoProcessor->save($this->order->refresh());
    }
    
    protected function saveItemsBarcode(Collection $inventoryItems, int $type)
    {
        $orderItems = $this->order->orderItems;
        $scannedItems = $inventoryItems->groupBy('stiid_itemmasterid');
        if($type === static::CPD_ORDER){
            $scanCountField = 'bcod_scannedcount';
            $itemIdField= 'stit_ID';

        }else{
            if($type === static::CUSTOMER_ORDER){
                $scanCountField = 'item_order_qty_scanned';
                $itemIdField= 'item_product_id';
            }else{
                $scanCountField = 'b2bso_itemscannedqty';
                $itemIdField= 'b2bso_itemid';
            }       
        }
       /* $scanCountField = $type === static::CPD_ORDER
            ? 'bcod_scannedcount'
            : $type === static::CUSTOMER_ORDER ? 'item_order_qty_scanned':'b2bso_itemscannedqty';

        $itemIdField = $type === static::CPD_ORDER
            ? 'stit_ID'
            : $type === static::CUSTOMER_ORDER ?'item_product_id' : 'b2bso_itemid';
         */   

        foreach ($orderItems as $item) {
            if (isset($scannedItems[$item->{$itemIdField}])) {
                $item->update([$scanCountField => $scannedItems[$item->{$itemIdField}]->count()]);
                $barcodes = $scannedItems[$item->{$itemIdField}]->map(function ($barcode) use ($type, $item) {
                    $data = [
                        'stiid_id' => $barcode['stiid_id'],
                        'stiid_barcode' => $barcode['stiid_barcode'],
                    ];

                    if($type === static::CUSTOMER_ORDER) {
                        $data['customer_order_id'] = $item->customer_order_id;
                    }
                    if($type === static::B2B_ORDER) {
                        $data['bbso_id'] = $item->bbso_id;
                        $data['bbsd_id'] = $item->bbsd_id;
                    }

                    return $data;
                });
                $item->barcodes()->createMany($barcodes->toArray());
            }
        }
        
    }
    
}
