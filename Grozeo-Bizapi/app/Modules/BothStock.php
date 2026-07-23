<?php

namespace App\Modules;

use App\Models\Order;
use App\Models\StockItemMaster;
use App\Models\FinanceAutopostingValues;
use Illuminate\Http\Request;
use App\Http\Repositories\PostingRepository;
use App\Modules\OrderCollect;
use App\Exceptions\MsgException;
use App\Modules\PriceCalculation;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\Item\Stock;
use BackOffice\Models\BranchInventory;
use Illuminate\Support\Facades\Log;

class BothStock
{
    private $order;

    private $stock;

    private $price;

    private $order_model;

    private $inventory;

    private $item_master;

    public function __construct()
    {
        $this->order = new OrderCollect;
        $this->stock = new Stock;
        $this->price = new PriceCalculation;
        $this->order_model = new Order;
        $this->inventory = new BranchInventory;
        $this->item_master = new StockItemMaster;
    }
    
    public function bothStockOrder(array $request, array $stock, $hasRestService = 0)
    {

        //$stock = $this->checkStock($cart, $request);
        if(count($stock['available_stock']) == 0)
        {
            throw new MsgException("Stocks are empty for All products.");
        }
         $central_store_branch = $request['branch_id'];
         $request['branch_id'] = $request['nearest_retailer_branch']; 
        $parentOrder = $this->order->createOrder($stock['available_stock'], $request, $hasRestService); 
        if($parentOrder && count($stock['empty_stock']) > 0)
        {
            $request['branch_id'] = $central_store_branch;
            $parent_id = $parentOrder->order_id ?? 0;
            $company_id = $parentOrder->order_company_id ?? 0;
            $child_stock = $stock['empty_stock'];
            $data = $this->createChildOrder($child_stock, $request, $parent_id, $company_id, $hasRestService);
            DB::transaction(function () use($data, $child_stock, $request) {
                $child_order = $this->order_model->create($data);
                if($child_order)
                {
                    // $this->saveFinanceAutoPostingValues($child_order);
                }

                $child_order_id = $child_order->order_order_id ?? 0;
                $child_order->productItem()->createMany(
                $this->prepareOrderedItems($child_stock, $child_order_id, $request['branch_id'])
                    );
            });
            
        }
        return $parentOrder;
    }

    private function checkStock(array $cart, $request)
    {
       $available_stock = [];
       $empty_stock = [];
       $products = array_column($cart, "cart_product_id");
       $stock = $this->stock->getStock($products, $request['branch_id']);
       foreach($cart as $key => $value)
       {
           $stock_val = array_key_exists($value['cart_product_id'], $stock) ? 
                                   $stock[$value['cart_product_id']] : 0;
          
          if($value['cart_order_qty'] < $stock_val) {
               $available_stock[] = $value;
           }
           else{
                $empty_stock[] = $value;
           }
       }
     return [
           "available_stock" => $available_stock,
           "empty_stock" => $empty_stock,
       ];
    }

    private function createChildOrder(array $cart, array $request, int $parent_id, $company_id, $hasRestService = 0)
    {
        $branch_id = $request['branch_id'];
        $request['child'] = 1;

        if($hasRestService > 0)
        {
            $checkAvailableDrivers = (new CheckDriverRepository)->checkIfDriverAvailable($branch_id);
            if($checkAvailableDrivers > 0)
            {
                $prices = $this->price->calculate($cart, $branch_id, $request);
            }
            else
            {
                $prices['delivery_status'] = 0;
            }
        }
        else
        {
            $prices = $this->price->calculate($cart, $branch_id, $request);
        }
        $storegroupid = getHeaderStoreGroup();

        return [
            'order_order_id' => CreateOrderId::generate(),
            'order_parent' => $parent_id,
            'order_customer_id' => auth()->user()->cust_id,
            'order_total_amount' => $prices['basket_price'],
            'order_delivery_charge' => $prices['delivery_charge'],
            'order_courier_charge' => $prices['courier_charge'],
            'order_total_gst' => $prices['total_tax'],
            'order_branch_id' => $branch_id,
            'order_company_id' => $company_id,
            'subtotal' => $selling = $prices['total_selling'],
            'order_mrp' => $mrp = $prices['total_mrp'],
            'order_saved_amount' => round($mrp - $selling, 2),
            'order_kfc_amount' => $prices['total_kfc'],
            'total' => $round_total = round($prices['total'], 0, PHP_ROUND_HALF_UP),
            'order_roundoff' => round($round_total - $prices['total'], 2),
            'order_method' => $request['order_method'],
            'order_type' => $request['selection'],
'order_portal_afterpayment_redirecturl' => (isset($request['portal_redirecturl'])?base64_decode($request['portal_redirecturl']):"test"),
'storegroup_id' => $storegroupid

        ];
    }

    private function prepareOrderedItems($cart, $order, $branch_id)
    {
        $product_id = array_column($cart, 'cart_product_id');
        $prices = $this->getPrice($product_id, $branch_id)->toArray();
        $selling = array_column($prices, 'selling_price', 'stit_id');
        $mrp = array_column($prices, 'mrp', 'stit_id');
        $medicine = $this->getItems($product_id)->toArray();
        $med = array_column($medicine, 'isMedicine', 'stit_ID');
        return array_map(function ($item) use ($order, $selling, $mrp, $med) {
            $count = $item['cart_order_qty'] ?? 1;
            $product_id = $item['cart_product_id'];
            $selling_rs = $selling[$product_id] ?? 0;
            return [
                'item_product_id' => $product_id,
                'item_group_id' => $item['cart_group_id'],
                'item_order_qty' => $count,
                'item_order_id' => $order,
                'item_price' => round($selling_rs * $count, 2),
                'item_retail_price' => $mrp[$product_id] ?? 0,
                'item_sales_price' => $selling_rs,
                'item_amount' => 0,
                'item_isMedicine' => $med[$product_id] ?? 0,
            ];
        }, $cart);
    }

    private function getPrice(array $product_id, $branch_id)
    {
        return  $this->inventory->whereIn('stit_id',$product_id)
                                ->where('branch_id',$branch_id)
                                ->select('stit_id','selling_price','mrp')
                                ->get();
    }

    private function getItems(array $product_id)
    {
        return $this->item_master->whereIn('stit_ID', $product_id)
                                ->select('stit_ID', 'isMedicine')
                                ->get();
    }

    private function saveFinanceAutoPostingValues($child_order)
    {
        /* $autoPostingInsert = [
            'order_id'                      => $child_order->order_id,
            'OrderDeliveryCharges_ODC'      => ($child_order->order_delivery_charge - $child_order->order_delivery_charge_gst),
            'RetailSalePriceinMRP'          => $child_order->order_mrp_et,
            'MRP_RRP'                       => $child_order->order_mrp,
            'TaxinMRP'                      => ($child_order->order_mrp - $child_order->order_mrp_et),
        ];
        $OrderGrandTotal = $child_order->total;
        if($child_order->order_roundoff < 0)
        {
            $autoPostingInsert['RoundDown'] = abs($child_order->order_roundoff);
        }
        if($child_order->order_roundoff > 0)
        {
            $autoPostingInsert['RoundUp'] = $child_order->order_roundoff;
        }
        if(($child_order->payment_mode == 1) || ($child_order->payment_mode == 4))
        {
            $autoPostingInsert['OrderGrandTotal_POD'] = $OrderGrandTotal;
            $autoPostingInsert['TSOPOD_PendingCollection'] = $OrderGrandTotal - $child_order->order_wallet_amount;
        }
        else
        {
            $autoPostingInsert['OrderGrandTotal'] = $OrderGrandTotal;
        }
        $autoPosting = FinanceAutopostingValues::create($autoPostingInsert); */
        
        $postReq = new Request();
        $postReq->setMethod('POST');
        $postReq->request->add([
            'order_id'              => $child_order->order_id,
            'finascopEventRefId'    => config("event_master.checkout"),
            'storegroup_id'         => ($child_order->storegroup_id ? $child_order->storegroup_id : 0)
        ]);

        (new PostingRepository)->finascopPosting($postReq);
    }
}
