<?php

namespace BackOffice\Http\Controllers\Transfer;

use App\Http\Repositories\Finascop\PackingFinascop;

use Exception;
use App\Models\Order;
use App\Models\OrderItem;
use BackOffice\Models\Item;
use BackOffice\Models\User;
use App\Models\BlockedItems;

use App\Helpers\EmailHelper;
use App\Models\Customer;

use Illuminate\Support\Arr;

use App\Events\{
    OrderHistory,
    DelayedOrderActions as DelayedOrderEvent
};

use BackOffice\Models\B2bOrder;
use BackOffice\Models\BoyOrder;
use BackOffice\Models\CpdOrder;
use App\Exceptions\MsgException;
use BackOffice\Models\Inventory;

use App\Exceptions\ErrorException;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\FinanceAutopostingValues;
use App\Http\Repositories\PostingRepository;

use BackOffice\Models\ReturnPacking;
use BackOffice\Models\DistributionChart;
use BackOffice\Models\TransferOrder;
use App\Http\Responses\ErrorResponse;
use BackOffice\Status\B2bOrderStatus;
use BackOffice\Status\BoyOrderStatus;

use BackOffice\Models\BranchInventory;
use BackOffice\Models\TransferRequest;
use BackOffice\Status\InventoryStatus;
use BackOffice\Status\CustomerOrderStatus;
use BackOffice\Status\TransferOrderStatus;
use BackOffice\Models\TransferOrderDetails;
use Illuminate\Database\Eloquent\Collection;
use BackOffice\Http\Repositories\ReduceStock;
use BackOffice\Responses\ItemProceedResponse;
use BackOffice\Actions\Inventory\QugeoProcessor;
use BackOffice\Http\Services\TransferOrderToQugeo;
use BackOffice\Actions\Inventory\RecordItemHistory;
use BackOffice\Actions\Inventory\ProcessInventoryItems;
use BackOffice\Http\Requests\OrderProceedNoBarcodeRequest;
use BackOffice\Http\Requests\TransferOrderProceedNoBarcodeRequest;
use App\Models\Branch;
use BackOffice\Http\Repositories\CheckTolerance;
use App\Http\Responses\SuccessResponse;
use BackOffice\Http\Services\TransferOrderToInvoice;
use App\CourierPartners\WorldOptions\WorldOptions;
use App\CourierPartners\Shipyaari\Shipyaari;
use App\Http\Responses\SuccessWithData;
use App\Status\DelayedOrderActions;
use App\Http\Repositories\Order\OrderRepositoryInterface;

class TransferOrderNoBarcodeProceedController
{
    /**
     * Inventory deails model.
     *
     * @var \BackOffice\Models\Inventory
     */
    protected $inventory;

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

    protected const STOCK_RETURN = 3;
    protected const BRANCH_DISTRIBUTION = 4;

    protected $recordItemHistory;

    protected $qugeoProcessor;

    protected $branchinventory;

    public function __construct(Inventory $inventory, ProcessInventoryItems $processItems, RecordItemHistory $recordItemHistory, QugeoProcessor $qugeoProcessor, BranchInventory $branchinventory)
    {
        $this->inventory = $inventory;
        $this->processItems = $processItems;
        $this->recordItemHistory = $recordItemHistory;
        $this->qugeoProcessor = $qugeoProcessor;
        $this->inventory = $branchinventory;
    }

    public function __invoke(TransferOrderProceedNoBarcodeRequest $request, $orderId)
    {
        $isValid = false;
        $partnerType  = (@$request->ispartner == 1) ? 'Merchant' : 'Grozeo';
        $ismanual = (isset($request->ismanual)?$request->ismanual:false);
        $is_incomplete =isset($request->is_incomplete)?$request->is_incomplete:0;
        $this->checkOrderId($orderId,$request,$is_incomplete);
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
        $key = "item_id";
        $items = array_reduce($request->items, function($result, $array) use($key){
            isset($array[$key]) &&
            $result[] = $array[$key];

            return $result;
        }, array());

        $postingController = new PostingRepository();
        $this->checkItemCount($items,$request->items,$is_incomplete);
        $packinglist = [];
        DB::transaction(function () use ($request, $items,$ismanual,&$packinglist,$is_incomplete,$postingController)
        {
            if(!$ismanual)
            {
                $this->freeUpGodownBoy(auth_user(),$request->boy_order_id);
            }
            $amountValidation = true;

            if($is_incomplete)
            {
                $this->saveItems($request->items,$this->order->fsto_isalreadypacked,$is_incomplete);
                if($this->order->fsto_ordertype == static::CUSTOMER_ORDER){
                    CheckTolerance::updateIncompleteItemAmount($this->order->fstr_id,$this->order->fsto_id);
                    $amountValidation = true;
                }


                if($amountValidation == true){
                    $packinglist = $this->markOrderAsInCompleted($ismanual);       
                    $this->relatedactionsOnIncompleteOrder($this->intOrderId, $this->order->fsto_ordertype, $request->number_bags,$request);
                }

            }
            else
            {
                $this->saveItems($request->items,$this->order->fsto_isalreadypacked,$is_incomplete);
                if($this->order->fsto_ordertype == static::CUSTOMER_ORDER)
                {
                    $orderidCount = DB::select('SELECT count(rcep_id) as count FROM customer_order_extra_payment_log WHERE rcep_order_id =  '. $this->order->fstr_id);
                    $orderidWallCount = DB::select('SELECT brcw_Amount FROM retaline_customer_wallet_transaction WHERE refentry_id =  '. $this->order->fstr_id);

                    if(isset($orderidCount[0]->count) > 0 || isset($orderidWallCount[0]->brcw_Amount) > 0)
                    {
                        $amountValidation == true;
                    }
                    else
                    {
                        $amountValidation = CheckTolerance::validateOrderAmount($this->order->fstr_id,$this->order->fsto_id, $partnerType);
                    }
                }
                if($amountValidation == true)
                {
                    // $this->markOrderAsCompleted($ismanual);                 
                    $this->markOrderAsPackedNotBoxed($ismanual);                 
                    $this->relatedactionsOnComplete($this->intOrderId, $this->order->fsto_ordertype, $request->number_bags,$request);
                    $orderManupId = $this->order->fstr_id;
                    if($this->order->fsto_ordertype == static::CUSTOMER_ORDER )
                    {
                        $this->updateOrderItems($orderManupId,$this->order->fsto_id);
                        if($this->order->fsto_isalreadypacked == 1)
                            $this->orderCalculationUpdates($orderManupId);

                    }
                    $packinglist = (new TransferOrderToQugeo)->createQugeoOrder($this->order,$this->order->fsto_ordertype, $request->number_bags,floatval($request->invoiceamt));
                    if($packinglist)
                    {
                        (new TransferOrderToInvoice)->createInvoice($this->order,$this->order->fsto_ordertype, $request->number_bags,floatval($request->invoiceamt));

                        $br_details = Branch::where('br_ID', $this->order->fsto_destination)->first();
                        $sourcebr_details = Branch::where('br_ID', $this->order->fsto_source)->first();
                        
                        if($this->order->fsto_ordertype == static::CPD_ORDER)
                        {
                            if($br_details->br_type == 1)
                            {
                                $fsr_id = DB::table('finascop_stock_request')->select('fsr_id')->where('fstr_id', $this->order->fstr_id)->first();							
                                $satelliteOrder = DB::table('order_request_log')->select('order_id')->where('fsr_id', $fsr_id->fsr_id)->first();
                                $orderManupId = $satelliteOrder->order_id;
                            }
                        }


                        if($this->order->fsto_ordertype == static::CUSTOMER_ORDER || $br_details->br_type == 1)
                        {
                            if($sourcebr_details->br_type == 1)
                            {
                                $this->decreaseItemCount($items,$request->items);
                            }
                            else
                            {
                                ReduceStock::minusStock($orderManupId,$this->order->fsto_id); // $this->intOrderId
                                $postReq = new Request();
                                $postReq->setMethod('POST');
                                $postReq->request->add([
                                'order_id' => $this->order->fstr_id,
                                'finascopEventRefId'     => config("event_master.packing"),
                                'storegroup_id' => 0
                                ]);

                                $postingController->finascopPosting($postReq); 

                                event(new DelayedOrderEvent($this->order->fstr_id, 4));
                            }                            
                            if($this->order->fsto_ordertype == static::CUSTOMER_ORDER )
                            {
                                $this->updateOrderTdr($orderManupId,$this->order->fsto_id);
                            }
                        }
                        else
                        {
                            if($this->order->fsto_isNotSellable == 0)
                            {
                                $this->decreaseItemCount($items,$request->items);
                            } 
                        }
                    }
                }
                $this->saveFinanceAutoPostingValues($this->order->fstr_id);
            }
        });
        return new ItemProceedResponse([], $packinglist);
    }

    protected function getOrderRepo(): OrderRepositoryInterface
{
    if (!isset($this->orderRepository)) {
        $this->orderRepository = app(OrderRepositoryInterface::class);
    }
    return $this->orderRepository;
}
    /**
     * On complete action
     *
     * @param string $orderId
     * @param integer $type
     * @return void
     */
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
    /**
     * On complete action
     *
     * @param string $orderId
     * @param integer $type
     * @return void
     */
    protected function relatedactionsOnComplete(string $orderId, int $type, int $number_bags, $request)
    {
        if($type === static::CPD_ORDER){
            $this->relatedorder = new TransferRequest;
            return;
       }else{
           if($type === static::CUSTOMER_ORDER){
            $this->relatedorder =new Order;
            $orderField = 'order_id';
            $statusField = 'status_id';
            $status= CustomerOrderStatus::PACKED_NOT_BOXED;            
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
           } elseif($type === static::BRANCH_DISTRIBUTION){
            $this->relatedorder =new DistributionChart;
            $orderField = 'rdc_id';
            $statusField = 'rdc_status';
            $status=10;   
           }           
       } 
   
        if ($type == static::CUSTOMER_ORDER) {
            $this->relatedorder
                ->where($orderField, $this->order->fstr_id)
                ->update([$statusField => $status, 'order_packedbags_count' => $number_bags, 'order_invoiceno' => $request->invoiceno ,'order_invoicedate' => $request->invoicedate , 'order_invoiceamt' => $request->invoiceamt ]);
                event(new OrderHistory($this->order->fstr_id, CustomerOrderStatus::PACKED_NOT_BOXED));
        }elseif($type === static::B2B_ORDER){
            $this->relatedorder
                ->where($orderField, $this->order->fstr_id)
                ->update([$statusField => $status,'bbso_packedbags_count' => $number_bags, 'bbso_InvoiceStatus' => 1]);
        } else {
            $this->relatedorder
                ->where($orderField, $this->order->fstr_id)
                ->update([$statusField => $status]);
        }
    }

    /**
     * 
     * @items array
     * @itemid int
     * @return int 
     */
     protected function getItemCountPicked($items, int $itemid) {
           foreach($items as $item){
               if($item['item_id'] ==  $itemid){
                return $item['count'];
               }
           }      
           return 0;
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
    protected function checkOrderId(string $orderId,$requestitems,$is_incomplete)
    {
        
        $this->model = new TransferOrder;
        $this->orderField = 'fsto_uid';
        //DB::enableQueryLog(); 
        $this->order = $this->model->where($this->orderField, $orderId)->first();

        if (is_null($this->order)) {
            throw new MsgException('Invalid order id ' . $orderId);
        }elseif(!$is_incomplete && $requestitems->number_bags ==0 ){
            throw new MsgException("The number of bags must be greater than 0");
        }
//        elseif(md5($this->order->fsto_updateon) != $requestitems->key ){
//            throw new MsgException("The order has been updated, please reload and try again");
//        }
        elseif(!$is_incomplete && $this->order->fsto_isalreadypacked != 1){
            $totalcount =array_sum($this->order->transferorderDetails()->pluck('fsto_ItemQty')->toArray());                    
            $totalcounthave = array_sum(collect($requestitems->items)->pluck('count')->toArray());
            if($totalcount != $totalcounthave){
                throw new MsgException("Check the order, its not completed");
            }
        }elseif($is_incomplete && $this->order->fsto_isalreadypacked != 1){     
            
            $totalcount =array_sum($this->order->transferorderDetails()->pluck('fsto_ItemQty')->toArray());                    
            $totalcounthave = array_sum(collect($requestitems->items)->pluck('count')->toArray());
            if($totalcount == $totalcounthave){
                throw new MsgException("The order is NOT incomplete, its has all the items added, please check again");
            }
        }

        $this->intOrderId = $this->order->fsto_id;
        
    }
    

    /**
     * Get inventory item details for given barcodes
     *
     * @param array $barcodes
     * @param integer $type
     * @return \Illuminate\Support\Collection
     */
    protected function getInventoryItems(array $items, int $type)
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

         
        return $this->inventory
            ->select('stiid_id', 'stii_id', 'stiid_barcode', 'stiid_itemmasterid', 'fsbg_id','stiid_itemmastername')
            ->whereIn('stiid_barcode', $items)
            ->where('cpd_branch_id', $this->order->{$cpdBranchId})
            ->where('stiid_status', $status)
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
        $fields[$order_field] = $this->intOrderId;

        return $this->inventory
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
     * Mark the order as packed not boxed
     *
     * @param string $orderId
     * @param integer $type
     * @return void
     */
    protected function markOrderAsPackedNotBoxed($ismanual)
    {

        $statusField = 'fsto_status';
        $status= TransferOrderStatus::PACKED_NOT_BOXED;
        $ismanualfield = 'fsto_ismanualpacking';
        $ismanualvalue = ($ismanual?1:0); 
        $this->order
        ->update([$statusField => $status,$ismanualfield =>$ismanualvalue]);

    }
     /**
     * Mark the order as incomplete
     *
     * @param string $orderId
     * @param integer $type
     * @return void
     */
    protected function markOrderAsInCompleted($ismanual)
    {

        $statusField = 'fsto_status';
        $status= TransferOrderStatus::INCOMPLETE_ORDER;
        $ismanualfield = 'fsto_ismanualpacking';
        $ismanualvalue = ($ismanual?1:0); 
        $pickingNumberField = 'fsto_pickingNumber';
        $pickingNumber = $this->generateUniqueString(4).'/'.$this->order->fsto_id;
        $this->order
        ->update([$statusField => $status,$ismanualfield =>$ismanualvalue,'fsto_isalreadypacked'=>1,$pickingNumberField => $pickingNumber]);

        $packinglist['pickingNumber'][] = $pickingNumber;
        $packinglist['BoxDetails'] = [];
        return $packinglist;
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

    protected function decreaseItemCount($inventoryItems,$requesteditems)
    {

        $brId = $this->order->fsto_source;

$br_details = Branch::where('br_ID', $brId)->first();

        DB::enableQueryLog();     
        
            foreach ($inventoryItems as $inventoryItem) {
               $stockcount = $this->getItemCountPicked($requesteditems,$inventoryItem) ;
               DB::table('finascop_stock_branch_inventory')
                    ->where('stit_id', $inventoryItem)
                    ->where('branch_id', $brId)

                    ->update([
                        'item_count' =>DB::raw('if(item_count-' . $stockcount.'>0,item_count-' . $stockcount  . ',0)' )
                    ]);
               
               if($br_details->br_type == 0){
                   ReduceStock::ResetChildItemsStock($inventoryItem,$brId);
            }
            }
             
    }

    protected function checkItemCount($inventoryItems,$requesteditems,$is_incomplete)
    {
        
        $brID = $this->order->fsto_source;
        //DB::enableQueryLog(); 
        $stockInventoryItems = DB::table('finascop_stock_branch_inventory')
        ->selectRaw('sum(item_count) as item_count, stit_id, (SELECT stit_SKU FROM finascop_stock_itemmaster WHERE finascop_stock_itemmaster.stit_ID = finascop_stock_branch_inventory.stit_id) as name ')
        ->whereIn('stit_id',  $inventoryItems)
        ->where('branch_id', '=' , $brID)    
        ->groupby('stit_id')    
        ->get();
        if($this->order->fsto_ordertype == static::CUSTOMER_ORDER){
            $blockedItems=DB::table('finascop_stock_blocked')
                ->where('branch_id', $brID)
                ->where('order_id', '!=', $this->order->fstr_id)
                ->whereIn("item_id", $inventoryItems)
                ->get();     
        }else{
            $blockedItems=DB::table('finascop_stock_blocked')
                ->where('branch_id', $brID)
                ->whereIn("item_id", $inventoryItems)
                ->get();   
        }
        /// NO VALIDATION REQUIRED

        /*foreach ($inventoryItems as $inventoryItem) { 
                $pickedcount = $this->getItemCountPicked($requesteditems,$inventoryItem);
                $stockcount = $this->getStockCount($stockInventoryItems,$inventoryItem,$inventoryItemname);
                $blockedcount = $this->getBlockedStockCount($blockedItems, $inventoryItem);

                if($pickedcount > ($stockcount -$blockedcount) ) {
                    throw new MsgException('The item - ' . $inventoryItemname . ' count is more than what is available in stock.');
                }else{
                }               
           
        }*/

    }

    protected function getStockCount($stock, $stitid,&$inventoryItemname)
    {   
        $inventoryItemname = "";
        foreach($stock as $item){
               if($item->stit_id ==  $stitid){
                $inventoryItemname = $item->name;
                return $item->item_count;
               }
           }      
           return 0;
    }
    protected function getBlockedStockCount( $blockstock, $itemId)
    {
        foreach($blockstock as $item){
           
               if( $item->item_id ==  $itemId){             
                return $item->count;
               }
           }      
           return 0;
    }
    
 
    protected function saveItems($inventoryItems,$isalreadypacked,$is_incomplete)
    {
        foreach ($inventoryItems as $item) {
           if($is_incomplete){
            TransferOrderDetails::where('fsto_id',$this->order->fsto_id)
            ->where('fsto_ItemId',$item['item_id'])
            ->update(['fsto_pkdQty' => $item['count'],'fsto_stockValue' => $item['fsto_stockValue']]);
           }else{
            if($isalreadypacked ==0){
                TransferOrderDetails::where('fsto_id',$this->order->fsto_id)
                ->where('fsto_ItemId',$item['item_id'])
                ->update([
                    "fsto_pkdQty" => DB::raw("`fsto_ItemQty`"),'fsto_stockValue' => $item['fsto_stockValue']
                ]);
            }else{
				if($item['count'] >= 0 && $this->order->fsto_ordertype == static::CUSTOMER_ORDER){
					$removedItem = BlockedItems::where('order_id', $this->order->fstr_id)
                            ->where('markedfordelivery', 1)
							->where('item_id', $item['item_id'])
                            ->update(['count' => $item['count']]);
					
				}
			}
           }
        }
        
    }
    
    protected function updateOrderItems($orderId, $fstoId) {
        $itemDetails = DB::table('retaline_customer_order_items')
            ->select('item_sales_price', 'item_order_qty', 'item_product_id', 'item_order_qty_scanned')
            ->where('customer_order_id', '=', $orderId)
            ->get();
        foreach ($itemDetails as $itemDetail) {

            $fstoDetails = DB::table('finascop_stock_transfer_order_details')
                ->select('fsto_stockValue', 'fstro_ItemSPincTax', 'fsto_pkdQty', 'fsto_ItemId','fstro_ItemPackedSPincTax','fsto_ItemQty')
                ->where('fsto_id', '=', $fstoId)
                ->where('fsto_ItemId', '=', $itemDetail->item_product_id)
                ->first();
            $itemStatus = ($fstoDetails->fsto_pkdQty == $fstoDetails->fsto_ItemQty) ? 1 : 
              (($fstoDetails->fsto_pkdQty == 0) ? 0 : 2);
            DB::table('retaline_customer_order_items')
                    ->where('item_product_id', $itemDetail->item_product_id)
                    ->where('customer_order_id', $orderId)
                    ->update([
                        'item_order_qty_scanned' => $fstoDetails->fsto_pkdQty,'item_price_packed' => $fstoDetails->fstro_ItemPackedSPincTax,'updated_at' => now(),'order_item_status' => $itemStatus
                    ]);
        
}
}
    public function updatePackages(Request $request)
    {
        try
        {
            if(!isset($request->fstoId) || intval($request->fstoId) ==0)
            {
                return new ErrorResponse( "Invalid orderid");
            }
            $packingDetails = $request->packingDetails;
            $fstoId = $request->fstoId;
            $fstoOrderType = $request->fstoOrderType;
            foreach ($packingDetails as $packingDetail)
            {
                DB::table('retaline_transfer_order_pack_details')->insert([
                    'rtopd_fstoId'      => $fstoId,
                    'rtopd_orderType'   => $fstoOrderType,
                    'rtopd_packets'     => $packingDetail['packingNumber'], 
                    'rtopd_packaging'   => $packingDetail['rpckm_id'], 
                    'rtpod_length'      => @$packingDetail['Length'],
                    'rtpod_breadth'     => @$packingDetail['Breadth'],
                    'rtpod_height'      => @$packingDetail['Height'],
                    'rtopd_packetweigh' => $packingDetail['Weight'],
                    'rtopd__createdOn'  => date('Y-m-d H:i:s')
                ]);
            }
            $transferOrder = TransferOrder::where('fsto_id', $fstoId)->first();
            if($transferOrder)
            {
                TransferOrder::where('fsto_id', $fstoId)->update([
                    'fsto_status' => TransferOrderStatus::COMPLETED
                ]);
                Order::where('order_id', $transferOrder->fstr_id)->update([
                    'status_id' => CustomerOrderStatus::READY_FOR_DELIVERY
                ]);
                event(new OrderHistory($transferOrder->fstr_id, CustomerOrderStatus::READY_FOR_DELIVERY));
                
                event(new DelayedOrderEvent($transferOrder->fstr_id, 4, 5));
            }
            return new SuccessResponse('Packages details saved successfully');
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
//for tenent entry update
    protected function updateOrderTdr($orderId, $fstoId) {
        $orderDetails = DB::table('retaline_customer_order')
            ->select('total','order_delivery_charge','order_courier_charge', 'payment_mode','total_afterpacking','order_delivery_charge_gst',
            'order_total_igst','order_wallet_amount','order_branch_id','order_order_id')
            ->where('order_id', '=', $orderId)
            ->first();
            $tenantDeliveryCharge = round(($orderDetails->order_delivery_charge) + ($orderDetails->order_courier_charge)- ($orderDetails->order_delivery_charge_gst), 2) ;
            $tdr = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TDR'");
            $tdrCGST = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TDR_CGST'");

            $companyGST = DB::select("SELECT comp_gstno FROM finascop_company WHERE comp_id = 1");
            $orderBranchGST = DB::select("SELECT br_GST FROM finascop_branch WHERE br_ID = {$orderDetails->order_branch_id}");

            $companyGSTStr = substr($companyGST[0]->comp_gstno, 0, 2);
            $orderBranchGSTStr = substr($orderBranchGST[0]->br_GST, 0, 2);

            $isIntrastate = strcmp($companyGSTStr,$orderBranchGSTStr);

            
            if($orderDetails->payment_mode == 2 || $orderDetails->payment_mode == 6){   
                $tdrOnlinePayment = round(($orderDetails->total  - $orderDetails->order_wallet_amount)*$tdr[0]->cfg_Value/100,2);
             }
             else{
                $tdrOnlinePayment = 0.00;
             }
              $sgst = round(($tdrOnlinePayment * $tdrCGST[0]->cfg_Value/100),2);
              $igst = round($sgst * 2,2);
              $tenant = round($tenantDeliveryCharge + $tdrOnlinePayment + $igst,2);

              $deliveryIncome = $tenantDeliveryCharge;
              $deliveryIncomeGst = $orderDetails->order_delivery_charge_gst;
              $deliveryIncomesgst = round($deliveryIncomeGst/2,2);
              $deliveryIncomeigst = round($deliveryIncomesgst * 2,2);           
              

            if($isIntrastate == 0){
                $tdrIGSTVal = 0.00;
                $tdrCGSTVal = $sgst;
                $tdrSGSTVal = $sgst;
                $tdrUTGSTVal = 0.00;

                $diIGST = 0.00;
                $diSGST = $deliveryIncomesgst;
                $diCGST = $deliveryIncomesgst;
                $diUTGST = 0.00;                
            }else{                
                $tdrIGSTVal = $igst;
                $tdrCGSTVal = 0.00;
                $tdrSGSTVal = 0.00;
                $tdrUTGSTVal = 0.00;

                $diIGST = $deliveryIncomeigst;
                $diSGST = 0.00;
                $diCGST = 0.00;
                $diUTGST = 0.00;
            }
            $pgchgsIGST = $tdrIGSTVal + $diIGST;
            $pgchgsCGST = $tdrCGSTVal + $diCGST;
            $pgchgsSGST = $tdrSGSTVal + $diSGST;
            $pgchgsUTGST = $tdrUTGSTVal + $diUTGST;

            $tenant = round($deliveryIncome + $tdrOnlinePayment + $pgchgsIGST + $pgchgsCGST + $pgchgsSGST + $pgchgsUTGST, 2);
            DB::table('retaline_customer_order')
                    ->where('order_id', $orderId)
                    ->update([
                        'order_tdr' => $tdrOnlinePayment,
                        'order_tdr_cgst' => $tdrCGSTVal,'order_tdr_sgst' => $tdrSGSTVal,
                        'order_tdr_igst' => $tdrIGSTVal
            ]);
            DB::table('tenant_income_expense')->insert(
                        [
                        'orderId' => $orderId,
                        'orderOrderId' => $orderDetails->order_order_id,
                        'delivery_income' => $deliveryIncome,
                        'diIGST' =>$diIGST, 
                        'diCGST' => $diCGST, 
                        'diSGST'=> $diSGST,
                        'diUTGST'=> $diUTGST,
                        'pgIGST'=>$pgchgsIGST,
                        'pgCGST'=>$pgchgsCGST,
                        'pgSGST'=>$pgchgsSGST,
                        'pgUTGST'=>$pgchgsUTGST,
                        'tenantExpense'=>$tenant
                        ]
            );
        
}

public function generateshipment(Request $request){
    if(config('app.is_courier_enabled') == 1)
    {
        if(@$request['fstoId']){
            $reponse = '';
            if(config('courierpartners.default') == 'worldoptions')
        {
            $shipper = new WorldOptions;
            $reponse = $shipper->generateShipment($request['fstoId']);
        }else if(config('courierpartners.default') == 'shipyaari')
        {
            $shipper = new Shipyaari;
            $reponse = $shipper->createConsignment($request['fstoId']);
        }
        return  new SuccessWithData($reponse);
        //return $reponse;
        }else{
            return new ErrorResponse( "Invalid orderid"); 
        }
    }
    else
    {
        return new ErrorResponse( "Courier Shipment Generation is disabled.");
    }
    
    
}

    protected function generateUniqueString($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $randomIndex = random_int(0, strlen($characters) - 1);
            $string .= $characters[$randomIndex];
        }

        return $string;
    }

    private function saveFinanceAutoPostingValues($orderID)
    {
        $order = Order::where('order_id', $orderID)->first();

        $postReq = new Request();
        $postReq->setMethod('POST');
        $postReq->request->add([
            'order_id'              => $order->order_id,
            'finascopEventRefId'    => config("event_master.packing"),
            'storegroup_id'         => ($order->storegroup_id ? $order->storegroup_id : 0)
        ]);

        (new PostingRepository)->finascopPosting($postReq);

        event(new DelayedOrderEvent($order->order_id, 3));

        $customer = Customer::find($order->order_customer_id);
        $sendEmail = (new EmailHelper)->sendEmail('packingInvoice', [
            'email'         => $customer->cust_email,
            'order_id'      => $orderID,
            'Customersname' => $customer->cust_customer_name
        ]);
    }

    protected function orderCalculationUpdates($orderId)
    {
        $orders = Order::where('order_id', $orderId)->first();
        $items = $orders->productItem()->where('order_item_status', '>', 0)->get();        
        $total = DB::transaction(function () use ($items, $orders) {
            $orderRepo = $this->getOrderRepo();
            $updateItemOrders = $orderRepo->updateOrderItemsPacking($items, 0);
            $total = $orderRepo->updateOrderPacking($orders, $updateItemOrders);
            return $total;
        });
    }

    private function updateOrderItemsCalcuation($items, $discountAmount)
    {
        $itemsArray = json_decode($items, true);
        $total_gst = $total_kfc = $total_after_coupon = $totalCess = $totalMrpEt = $totalMrp = $totalsellingPrice = 0 ;
        $total_seller_discount = 0;
        $item_discount_total = 0;
        $sellingPrice = 0;
        $kfc = $this->getKfc();
        $totalItemMrps = array_sum(array_map(function ($itemOrder) {
            $qty = $itemOrder['item_order_qty_scanned'] > 0 ? $itemOrder['item_order_qty_scanned'] : $itemOrder['item_order_qty'];
            return $itemOrder['order_item_mrp'] * $qty;
        }, $itemsArray));
        foreach ($items as $item) {
            $itemmaster = Item::select("stit_ID", "taxValueId", "product_category", "stit_courierWt")->where("stit_ID", $item['item_product_id'])->with('productCategory')->first();
            $taxValues = DB::table("hsn_value")->where("id", $itemmaster->taxValueId)->first();
            $tax_percentage = @$taxValues->hsnGst ? $taxValues->hsnGst : 0;
            $itemcess = @$taxValues->hsnCess ? $taxValues->hsnCess : 0;
            $tax_val = $tax_percentage + $kfc;

            $qty = $item['item_order_qty_scanned'] > 0 ? $item['item_order_qty_scanned'] : $item['item_order_qty'];
            $itemMrp = $item['order_item_mrp'] * $qty;
            $itemMrpEt = $itemMrp * (1 - (($itemcess + $tax_val) / (100 + ($tax_val + $itemcess))));
            $itemMrpEt = floor($itemMrpEt * 100) / 100;
            if ($itemMrpEt > 0) {
                $itemMrpEt = $this->nearestEvenDecimal($itemMrpEt);
            }
            

            $orginal_sales_price = $item['orginal_sales_price'];
            $item_sales_price = $item['orginal_sales_price'];
            $itemDiscountValue = 0;

            $itemDiscountedSP = $item['orginal_sales_price'] * $qty;
            if ($itemDiscountedSP > 0) {
                $sellingPrice = $this->nearestEvenDecimal($itemDiscountedSP);
            }
            $itemDiscountedSPEt = $itemDiscountedSP * (1 - (($itemcess + $tax_val) / (100 + ($tax_val + $itemcess))));
            $itemDiscountedSPEt = floor($itemDiscountedSPEt * 100) / 100;
            if ($itemDiscountedSPEt > 0) {
                $itemDiscountedSPEt = $this->nearestEvenDecimal($itemDiscountedSPEt);
            }
            $seller_discount = round($itemMrpEt - $itemDiscountedSPEt, 2);
            
            $productCess = $itemDiscountedSPEt * $itemcess / 100;
            $productCess = floor($productCess * 100) / 100;
            if ($productCess > 0) {
                $productCess = $this->nearestEvenDecimal($productCess);
            }
            
            $new_price_tax = $itemDiscountedSP - ($itemDiscountedSPEt + $productCess);
            if ($new_price_tax > 0) {
                $new_price_tax = $this->nearestEvenDecimal($new_price_tax);
            }
            
            $partialtax = $new_price_tax / 2;
            $kfc_val = ($kfc > 0) ? ($new_price_tax * $kfc) / $tax_val : 0;
            $taxValue = ($kfc > 0) ? ($new_price_tax - $kfc_val) : $new_price_tax;

            $tcsVal = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TCS' limit 1");
            $partTcsVal = round($tcsVal[0]->cfg_Value / 2, 2);
            $parTtcsGst =  round($itemDiscountedSPEt * $partTcsVal / 100, 2);
            $tcsGst = $parTtcsGst * 2;

            $tcs_igst = ($item['order_item_tcs_igst'] > 0) ? $tcsGst : 0.00;
            $tcs_cgst =  ($item['order_item_tcs_cgst'] > 0) ? $parTtcsGst : 0.00;
            $tcs_sgst =  ($item['order_item_tcs_sgst'] > 0) ? $parTtcsGst : 0.00;
            $tcs_utgst = ($item['order_item_tcs_igst'] > 0) ? $parTtcsGst : 0.00;

            $itemcgst = ($item['order_item_cgst'] > 0) ? $partialtax : 0.00;
            $itemsgst = ($item['order_item_sgst'] > 0) ? $partialtax : 0.00;
            $itemutgst = ($item['order_item_ugst'] > 0) ? $partialtax : 0.00;
            $itemigst = ($item['order_item_igst'] > 0) ? $taxValue : 0.00;

            $totalsellingPrice += $sellingPrice;
            $total_seller_discount += $seller_discount;
            $total_after_coupon += $itemDiscountedSPEt;
            $total_gst += $new_price_tax;
            $total_kfc += $kfc_val;
            $totalCess += $productCess;
            $totalMrpEt += $itemMrpEt;
            $totalMrp += $itemMrp;
            $item->update([         
                'order_item_mrp_et'             => $itemMrpEt,
                'order_item_basket_price'       => $itemDiscountedSPEt,
                'order_item_basket_price_et'    => $itemDiscountedSPEt,
                'order_item_seller_discount'    => $seller_discount,
                'order_item_gst'                => ($itemcgst + $itemutgst + $itemsgst + $itemigst),
                'order_item_cgst'               => $itemcgst,
                'order_item_ugst'               => $itemutgst,
                'order_item_sgst'               => $itemsgst,
                'order_item_igst'               => $itemigst,
                'order_item_tcs_gst'            => ($tcs_igst + $tcs_cgst + $tcs_utgst + $tcs_sgst),
                'order_item_tcs_igst'           => $tcs_igst,
                'order_item_tcs_cgst'           => $tcs_cgst,
                'order_item_tcs_utgst'          => $tcs_utgst,
                'order_item_tcs_sgst'           => $tcs_sgst,
                'order_item_cess'               => $productCess,
                "orginal_sales_price"           => $orginal_sales_price,
                "order_item_seller_discount"    => $seller_discount,
                "order_item_total_mrp"          => $itemMrp,
                'item_price'                    => $itemDiscountedSP,
                'item_sales_price'              => $item_sales_price
            ]);
        }
        return compact("total_after_coupon", "total_gst", "total_kfc", "total_seller_discount", "totalCess","totalMrpEt","totalMrp","totalsellingPrice");
    }

    public function nearestEvenDecimal($decimalValue)
    {
        $numArr = explode('.', $decimalValue);
        if (@$numArr[1]) {
            $num_length = strlen((string)$numArr[1]); //to cover .01, .001
            if ($num_length == 1) {
                $numArr[1] = $numArr[1] * 10;
            }
            $remainder = $numArr[1] % 2;
            if ($remainder == 0) {
                return $decimalValue;
            } else {
                return $decimalValue + 0.01;
            }
        } else {
            return $decimalValue;
        }
    }

    private function updateOrder($orders, $updateItemOrders = array())
    {
        //$total = $total_discount + $orders->order_delivery_charge + $updateItemOrders["total_gst"] + $updateItemOrders["total_kfc"];
        $order_subtotal = $updateItemOrders["totalMrpEt"] - $updateItemOrders["total_seller_discount"] + $orders->order_delivery_charge + $updateItemOrders["total_gst"] + $updateItemOrders["total_kfc"] + $updateItemOrders["totalCess"];

        $isRoudoff = (env('ROUND_OFF', false) ? 'true' : 'false');
        $gross_total = $isRoudoff == 'true' ? round($order_subtotal, 0, PHP_ROUND_HALF_UP) ?? 0 : round($order_subtotal, 2);

        $total = $updateItemOrders["total_after_coupon"] + $orders->order_delivery_charge + $updateItemOrders["total_gst"] + $updateItemOrders["total_kfc"];

        $isRoudoff = (env('ROUND_OFF', false) ? 'true' : 'false');
        $order_roundoff = round($gross_total - $order_subtotal, 2) ?? 0;
        $order_nettotal = $total;
        $rounding_precision = $isRoudoff == 'true' ? 0 : 2;
        $round_total = round($total, $rounding_precision, PHP_ROUND_HALF_UP);
        $update = [
            "order_mrp"                 => $updateItemOrders["totalMrp"],
            "order_mrp_et"              => $updateItemOrders["totalMrpEt"],
            "total"                     => $round_total,
            "order_total_gst"           => $updateItemOrders["total_gst"],
            "order_kfc_amount"          => $updateItemOrders["total_kfc"],
            "order_total_amount"        => $updateItemOrders["total_after_coupon"],
            "subtotal"                  => $updateItemOrders["totalsellingPrice"],
            "order_saved_amount"        => $updateItemOrders["total_seller_discount"],
            "order_seller_discount"     => -round($updateItemOrders["total_seller_discount"], 2),
            "order_subtotal"            => $order_subtotal,
            "order_nettotal"            => $order_nettotal,
            "order_grosstotal"          => $gross_total,
            "order_roundoff"            => $order_roundoff
        ];

        $orders->update($update);
        return $round_total;
    }
    
    public static function getKfc()
    {
        return (int) config('kfc.kfc_percentage') ?? 0;
    }
}
