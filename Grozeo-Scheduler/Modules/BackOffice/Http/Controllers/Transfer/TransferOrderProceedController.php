<?php

namespace BackOffice\Http\Controllers\Transfer;

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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use BackOffice\Models\ReturnPacking;
use BackOffice\Models\TransferOrder;
use App\Http\Responses\ErrorResponse;
use BackOffice\Status\B2bOrderStatus;
use BackOffice\Status\BoyOrderStatus;
use BackOffice\Models\BranchInventory;
use BackOffice\Models\TransferRequest;
use BackOffice\Status\InventoryStatus;
use BackOffice\Models\InventoryDetails;
use BackOffice\Status\CustomerOrderStatus;
use BackOffice\Status\TransferOrderStatus;
use BackOffice\Models\TransferOrderDetails;
use Illuminate\Database\Eloquent\Collection;
use BackOffice\Http\Repositories\ReduceStock;
use BackOffice\Responses\ItemProceedResponse;
use BackOffice\Http\Services\TransferOrderToQugeo;
use BackOffice\Actions\Inventory\RecordItemHistory;
use BackOffice\Actions\Inventory\ProcessInventoryItems;
use BackOffice\Http\Requests\TransferOrderProceedRequest;

class TransferOrderProceedController
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
    
    protected $relatedorder;

    protected $orderField;

    protected $intOrderId;

    protected $blockOrderId;

    protected const CPD_ORDER = 0;

    protected const CUSTOMER_ORDER = 1;

    protected const B2B_ORDER = 2;

    protected const STOCK_RETURN = 3;

    protected $recordItemHistory;   

    protected $inventory;

    

    public function __construct(InventoryDetails $inventoryDetails, ProcessInventoryItems $processItems, RecordItemHistory $recordItemHistory, BranchInventory $inventory)
    {
        $this->inventoryDetails = $inventoryDetails;
        $this->processItems = $processItems;
        $this->recordItemHistory = $recordItemHistory;        
        $this->inventory = $inventory;
      
    }

    public function __invoke(TransferOrderProceedRequest $request, $orderId)
    {
        $isValid = false;


        $ismanual = (isset($request->ismanual)?$request->ismanual:false);
        $isincomplete = (isset($request->isincomplete)?$request->isincomplete:0);
        $this->checkOrderId($orderId,$request,$isincomplete);
        if(!$ismanual){
            if ($this->isRevokedOrder($request->boy_order_id)) {
                return response()->json([
                    'status' => 'mismatch',
                    'data' => [
                        'mismatched' => [],
                        'is_revoked' => true,
                    ],
                ]);
            }
        }
        
        $barcodes = Arr::collapse(Arr::pluck($request->items, 'barcodes'));
        // DB::enableQueryLog(); 
        $inventoryItems = $this->getInventoryItems($barcodes, $this->order->fsto_ordertype);

        $mismatched = $this->processItems->process($request->items, $inventoryItems);
        $packinglist =[];
        if (empty($mismatched)) {
            if($this->order->fsto_ordertype !=  static::STOCK_RETURN){
                $this->checkBarcodeCount($inventoryItems, $this->order->fsto_ordertype, $isincomplete);
            }
            if($this->order->fsto_ordertype != static::CUSTOMER_ORDER && $this->order->fsto_ordertype !=  static::STOCK_RETURN){ 
                $this->checkItemCount($inventoryItems, $this->order->fsto_ordertype);
            }
            DB::transaction(function () use ($barcodes, $request, $inventoryItems,$ismanual, $isincomplete, &$packinglist) {
               
               
                $this->recordItemHistory->record($inventoryItems, $this->order->fsto_ordertype, $this->intOrderId);
               
               

                if(!$ismanual)
                $this->freeUpGodownBoy(auth_user(),$request->boy_order_id);

                //Need editing start
                if($isincomplete){
                   // $this->saveItems($request->items,$this->order->fsto_isalreadypacked,$isincomplete);
                    $this->saveItemsBarcode($inventoryItems, $this->order->fsto_ordertype,$this->order->fsto_isalreadypacked,$isincomplete);
                    $this->updateItemsAsMarked($barcodes, $this->order->fsto_ordertype,$this->order->fsto_isalreadypacked);
                    $this->markOrderAsInCompleted($ismanual);       
                    $this->relatedactionsOnIncompleteOrder($this->intOrderId, $this->order->fsto_ordertype, $request->number_bags,$request);
                }else{
                    //$this->saveItems($request->items,$this->order->fsto_isalreadypacked,$isincomplete);
                    $this->saveItemsBarcode($inventoryItems, $this->order->fsto_ordertype,$this->order->fsto_isalreadypacked,$isincomplete);
                    $this->updateItemsAsMarked($barcodes, $this->order->fsto_ordertype,$this->order->fsto_isalreadypacked);
                    $this->markOrderAsCompleted($ismanual);
                    $this->relatedactionsOnComplete($this->intOrderId, $this->order->fsto_ordertype, $request->number_bags);
                    $packinglist = (new TransferOrderToQugeo)->createQugeoOrder($this->order,$this->order->fsto_ordertype, $request->number_bags,floatval($request->invoiceamt)); 
                    if($this->order->fsto_ordertype == static::CUSTOMER_ORDER){                        
                        ReduceStock::minusStock($this->blockOrderId);
                    }else {
                        if($this->order->fsto_isNotSellable !=  1){
                            $this->decreaseItemCount($inventoryItems,$this->order->fsto_ordertype);
                        }
                    }
            }     
            });
        }else{
        }

        return new ItemProceedResponse($mismatched,$packinglist);
    }
    protected function relatedactionsOnIncompleteOrder(string $orderId, int $type, int $number_bags, $request)
    {
        if($type === static::CPD_ORDER){
            $this->relatedorder = new TransferRequest;
            return;
       }else{
           if($type === static::CUSTOMER_ORDER){
            $this->relatedorder =new Order;
            $orderField = 'order_id';
            $statusField = 'status_id';
            $status= CustomerOrderStatus::INSUFFICIENT_ITEMS;            
           }elseif($type === static::B2B_ORDER){
            $this->relatedorder =new B2bOrder;
            $orderField = 'bbso_id';
            $statusField = 'status_id';
            $status= B2bOrderStatus:: INSUFFICIENT_ITEMS;
           }elseif($type === static::STOCK_RETURN){
            $this->relatedorder =new ReturnPacking;
            $orderField = 'frrp_id';
            $statusField = 'frrp_status';
            $status= 2;   
           }    
       } 
   
        if ($type == static::CUSTOMER_ORDER) {
            $this->relatedorder
                ->where($orderField, $this->order->fstr_id)
                ->update([$statusField => $status]);
                event(new OrderHistory($this->order->fstr_id, CustomerOrderStatus::INSUFFICIENT_ITEMS));
        } else {
            $this->relatedorder
                ->where($orderField, $this->order->fstr_id)
                ->update([$statusField => $status]);
        }
    }
    protected function markOrderAsInCompleted($ismanual)
    {

        $statusField = 'fsto_status';
        $status= TransferOrderStatus::INCOMPLETE_ORDER;
        $ismanualfield = 'fsto_ismanualpacking';
        $ismanualvalue = ($ismanual?1:0); 
        $this->order
        ->update([$statusField => $status,$ismanualfield =>$ismanualvalue,'fsto_isalreadypacked'=>1]);

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
    protected function checkOrderId(string $orderId,$requestitems,$isincomplete)
    {
         $this->model = new TransferOrder();        
         $this->orderField = 'fsto_uid';

        $this->order = $this->model->where($this->orderField, $orderId)->first();

        if (is_null($this->order)) {
            throw new MsgException('Invalid order id');
        }elseif(!$isincomplete && $requestitems->number_bags ==0 ){
            throw new MsgException("The number of bags must be greater than 0");
        }
//        elseif(md5($this->order->fsto_updateon) != $requestitems->key ){
//            throw new MsgException("The order has been updated, please reload and try again");
//        }
        elseif(!$isincomplete && $this->order->fsto_isalreadypacked != 1){
            $totalcount =array_sum($this->order->transferorderDetails()->pluck('fsto_ItemQty')->toArray());                    
            $totalcounthave = count(Arr::collapse(Arr::pluck($requestitems->items, 'barcodes')));
          
            if($totalcount != $totalcounthave){
                throw new MsgException("Check the order, its not completed " . $totalcount  . " != " .  $totalcounthave);
            }
        }elseif($isincomplete && $this->order->fsto_isalreadypacked != 1){     
            
            $totalcount =array_sum($this->order->transferorderDetails()->pluck('fsto_ItemQty')->toArray());                    
            $totalcounthave = count(Arr::collapse(Arr::pluck($requestitems->items, 'barcodes')));
            if($totalcount == $totalcounthave){
                throw new MsgException("The order is NOT incomplete, its has all the items added, please check again");
            }
        }
        $this->intOrderId = $this->order->fsto_id;   
        $this->blockOrderId=$this->order->fstr_id;    

        
    }

    protected function checkBarcodeCount(Collection $inventoryItems, int $type, $isincomplete)
    {

        $scannedItems = $inventoryItems->groupBy('stiid_itemmasterid');
        $orderItems = $this->order->transferorderDetails;

        foreach ($orderItems as $item) {
            if(!$isincomplete){
            if (
                !isset($scannedItems[$item->fsto_ItemId]) 
                || $scannedItems[$item->fsto_ItemId]->count() != $item->fsto_ItemQty
            ) {               
                throw new MsgException('Item count and/or items mismatch');
            }
        }else{
            if (
                isset($scannedItems[$item->fsto_ItemId]) 
                && $scannedItems[$item->fsto_ItemId]->count() > $item->fsto_ItemQty
            ) {
                throw new MsgException('Excess Items picked up');
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
      
            if($type === 0){
                $status =  InventoryStatus::GODOWN_AVAILABLE;
            }elseif($type == '3'){
                $status =  InventoryStatus::DAMAGED_AND_IN_BRANCH;
            }else{
                $status =  InventoryStatus::INWARD_ITEM_SCANNED;
            }    
       
         
        return $this->inventoryDetails
            ->select('stiid_id', 'stii_id', 'stiid_barcode', 'stiid_itemmasterid', 'fsbg_id','stiid_itemmastername')
            ->whereIn('stiid_barcode', $barcodes)
            ->where('cpd_branch_id', $this->order->fsto_source)
            ->where(function($q) {
                $q->where('stiid_status', InventoryStatus::GODOWN_AVAILABLE)
                  ->orWhere('stiid_status', InventoryStatus::INWARD_ITEM_SCANNED)
                  ->orWhere('stiid_status', InventoryStatus::DAMAGED_AND_IN_BRANCH)
                  ->orWhere('stiid_status', InventoryStatus::BANNED_AND_IN_BRANCH);
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
    protected function updateItemsAsMarked(array $barcodes, int $type, $isalreadypacked)
    {
        if($isalreadypacked ==0){

        if($type === static::CPD_ORDER){
            $status =  InventoryStatus::GODOWN_MARKED_OUTWARD;
        }elseif($type ==  static::CUSTOMER_ORDER){
            $status =  InventoryStatus::IN_DELIVERY_CART;
        }elseif($type === static::B2B_ORDER){
            $status =  InventoryStatus::IN_DELIVERY_CART;
        }else{
            $status =  InventoryStatus::DAMAGED_RETURNING_TO_CPD;
        }    
       
        $branchId = $this->order->fsto_source;
        $order_field= 'cpd_order_id';
        
        if($type === static::CPD_ORDER){       
            $order_field= 'cpd_order_id';
        }else{
            if($type === static::CUSTOMER_ORDER){
                $order_field= 'cust_order_id';
            }elseif($type === static::B2B_ORDER){
                $order_field= 'b2b_order_id';
            }elseif($type === static::STOCK_RETURN){
                $order_field= 'ret_packing_id';
            }       
        }     

        $fields = [
            'stiid_status' => $status,
            'is_branch' => 1,
            'cpd_branch_id' => $branchId,
        ];
        $fields[$order_field] = $this->order->fstr_id;
        if($type === static::STOCK_RETURN){
            DB::statement("UPDATE finascop_stock_item_inventorydetails SET is_branch=1,cpd_branch_id={$branchId},stiid_status=if(stiid_status=13,14,9),stiid_updatedon=now()," . $order_field . " = " . $this->order->fstr_id . " where stiid_barcode in (" . implode(',',$barcodes) . ")");
        }else{       
            DB::statement("UPDATE finascop_stock_item_inventorydetails SET is_branch=1,cpd_branch_id={$branchId},stiid_status={$status},stiid_updatedon=now()," . $order_field . " = " . $this->order->fstr_id . " where stiid_barcode in (" . implode(',',$barcodes) . ")");
        }
       
    }
    }

    /**
     * Mark the order as completed
     *
     * @param string $orderId
     * @param integer $type
     * @return void
     */
    protected function markOrderAsCompleted($ismanual)
    {
        $statusField = 'fsto_status';
        $status= TransferOrderStatus::COMPLETED;     
        $ismanualfield = 'fsto_ismanualpacking';
        $ismanualvalue = ($ismanual?1:0);    
        $this->order
                ->update([$statusField => $status,$ismanualfield =>$ismanualvalue]);
        
    }
    
    /**
     * On complete action
     *
     * @param string $orderId
     * @param integer $type
     * @return void
     */
    protected function relatedactionsOnComplete(string $orderId, int $type, int $number_bags)
    {
        if($type === static::CPD_ORDER){
            $this->relatedorder = new TransferRequest;
            return;
       }else{
           if($type === static::CUSTOMER_ORDER){
            $this->relatedorder =new Order;
            $orderField = 'order_id';
            $statusField = 'status_id';
            $status= CustomerOrderStatus::READY_FOR_DELIVERY;
           }elseif($type === static::B2B_ORDER){
            $this->relatedorder =new B2bOrder;
            $orderField = 'bbso_id';
            $statusField = 'status_id';
            $status= B2bOrderStatus:: READY_FOR_DELIVERY;
           }elseif($type === static::STOCK_RETURN){
            $this->relatedorder =new ReturnPacking;
            $orderField = 'frrp_id';
            $statusField = 'frrp_status';
            $status=3;   
           }            
       } 
   
        if ($type == static::CUSTOMER_ORDER) {
            $this->relatedorder
                ->where($orderField, $this->order->fstr_id)
                ->update([$statusField => $status, 'order_packedbags_count' => $number_bags]);
                event(new OrderHistory($this->order->fstr_id, CustomerOrderStatus::READY_FOR_DELIVERY));
        } elseif($type === static::B2B_ORDER){
            $this->relatedorder                
                ->where($orderField, $this->order->fstr_id)
                ->update([$statusField => $status,'bbso_packedbags_count' => $number_bags, 'bbso_InvoiceStatus' => 1]);
        }  else {
            $this->relatedorder
                ->where($orderField, $this->order->fstr_id)
                ->update([$statusField => $status]);
        }
    }

    /**
     * Free up the godown boy from the completed order.
     *
     * @param GodownBoy $boy
     * @return void
     */
    protected function freeUpGodownBoy(User $boy,$boyOrderId)
    {
        $boy->update(['has_open_orders' => 0]);
        BoyOrder::where('id', $boyOrderId)           
            ->update(['status' => BoyOrderStatus::COMPLETED, 'completed_time' => now()]);
    }
    

    protected function decreaseItemCount(Collection $inventoryItems,$type=0)
    {
        $items = $inventoryItems->groupBy(function ($item) {
            return (int) $item->stiid_itemmasterid;
        });


        $groupIds = $inventoryItems->pluck('fsbg_id')->toArray();

        $brId = $this->order->fsto_source;
        

        $stockInventoryItems = $this->inventory
            ->whereIn('stit_id', $items->keys())
            ->whereIn('fsbg_id', $groupIds)
            ->where('branch_id', $brId)
            ->get();

            
        foreach ($items as $itemId => $item) {
            $groupedItem = $item->groupBy('fsbg_id');
            foreach ($groupedItem as $groupId => $items) {
                $this->inventory
                    ->where('stit_id', $itemId)
                    ->where('branch_id', $brId)
                    ->where('fsbg_id', $groupId)
                    ->update([
                        'item_count' => $this->getStockCount($stockInventoryItems, $groupId,$itemId,$brId) - $groupedItem[$groupId]->count()
                    ]);
            }
        }
    }

    protected function checkItemCount(Collection $inventoryItems,$type=0)
    {
        $items = $inventoryItems->groupBy(function ($item) {
            return (int) $item->stiid_itemmasterid;
        });



        $groupIds = $inventoryItems->pluck('fsbg_id')->toArray();

        $stockInventoryItems = $this->inventory
            ->whereIn('stit_id', $items->keys())
            ->whereIn('fsbg_id', $groupIds)
            ->where('branch_id', $this->order->fsto_source)
            ->get();

        if($this->order->fsto_ordertype == static::CUSTOMER_ORDER){     
            $blockedItems=BlockedItems::where('branch_id', $this->order->fsto_source)
                ->whereIn("item_id", $items->keys())
                ->where('order_id', '!=', $this->order->fstr_id)
                ->get();    
        }else{
            $blockedItems=BlockedItems::where('branch_id', $this->order->fsto_source)
            ->whereIn("item_id", $items->keys())
            ->get();     
        }

            
        foreach ($items as $itemId => $item) {
            
            $groupedItem = $item->groupBy('fsbg_id');
            foreach ($groupedItem as $groupId => $items) {
                if(($this->getStockCount($stockInventoryItems, $groupId,$itemId,$this->order->fsto_source) +$this->getBlockedStockCount($blockedItems, $itemId)) < $groupedItem[$groupId]->count()){
                    throw new MsgException('The item - '.$items[0]["stiid_itemmastername"].' count is more than what is available in stock.');

                }
                
            }
        }

    }

    protected function getStockCount(Collection $stock, $groupId,$itemId,$brId)
    {
        return $stock->where('fsbg_id', $groupId)
            ->where('stit_id', $itemId)
            ->where('branch_id', $brId)
            ->sum('item_count');
    }
    protected function getBlockedStockCount(Collection $blockstock, $itemId)
    {
        return $blockstock->where('item_id', $itemId)
            ->sum('count');
    }
    
    protected function saveItems($inventoryItems,$isalreadypacked,$isincomplete)
    {
        foreach ($inventoryItems as $item) {
           if($isincomplete){
            TransferOrderDetails::where('fsto_id',$this->order->fsto_id)
            ->where('fsto_ItemId',$item['item_id'])
            ->update(['fsto_pkdQty' => $item['count']]);
           }else{
            if($isalreadypacked ==0){
                TransferOrderDetails::where('fsto_id',$this->order->fsto_id)
                ->where('fsto_ItemId',$item['item_id'])
                ->update([
                    "fsto_pkdQty" => DB::raw("`fsto_ItemQty`")
                ]);
            }
           }
        }
        
    }

    protected function saveItemsBarcode(Collection $inventoryItems, int $type, $isalreadypacked, $isincomplete)
    {
        if($isalreadypacked ==0){
        $orderItems = $this->order->transferorderDetails;
        $scannedItems = $inventoryItems->groupBy('stiid_itemmasterid');
        $scanCountField = 'fsto_pkdQty';
        $itemIdField= 'fsto_ItemId';

        foreach ($orderItems as $item) {
            if (isset($scannedItems[$item->{$itemIdField}])) {
                $item->update([$scanCountField => $scannedItems[$item->{$itemIdField}]->count()]);
                $barcodes = $scannedItems[$item->{$itemIdField}]->map(function ($barcode) use ($type, $item) {
                    $data = [
                        'stiid_id' => $barcode['stiid_id'],
                        'stiid_barcode' => $barcode['stiid_barcode'],
                        'fstod_id' =>  $item->fstod_id,
                        'fsto_id' =>  $item->fsto_id ,
                        'stiid_isbanned' => $this->isbanned($barcode['stiid_barcode'])
                    ];
                    return $data;
                });
                $item->barcodes()->createMany($barcodes->toArray());
            }
        }
    }
        
    }
    protected function isbanned($stiid_barcode){
        $barcode = DB::table('finascop_stock_item_inventorydetails')
        ->selectraw('if(stiid_status=13 or stiid_status=14 or stiid_status=15 or stiid_status=16 or stiid_status=17 or stiid_status=19,1,0)  as banned')                       
        ->where('stiid_barcode', $stiid_barcode)
        ->first() ;    
        return $barcode->banned;
    }
    
}
