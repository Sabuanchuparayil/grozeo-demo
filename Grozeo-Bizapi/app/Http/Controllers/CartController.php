<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\DeliveryInfo;
use App\Models\Customer;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Exceptions\ErrException;
use App\Modules\DetermineStates;
use App\Location\RetailerLocation;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Requests\Cart\EditCartRequest;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Repositories\Cart\CartRepository;
use App\Http\Requests\Cart\AddToBulkCartRequest;
use App\Http\Repositories\Checkout\CheckoutOrder;
use App\Http\Repositories\Payment\PaymentRepository;
use App\Http\Repositories\Checkout\CheckoutRepository;
use App\Http\Repositories\Wishlist\SavedItemRepository;
use Illuminate\Support\Facades\Log;
use App\Http\Responses\ErrorResponse;
use BackOffice\Models\Branch;

class CartController extends Controller
{
    protected $cart;
    protected $savedItem;

    private $checkout;

    private $payment;

    private $address;

    public function __construct(CartRepository $cart, SavedItemRepository $savedItem)
    {
        $this->cart = $cart;
        $this->savedItem = $savedItem;
        $this->address = new DeliveryInfo;
        $this->customer=new Customer;
        // $this->checkout = $checkout;
        // $this->payment = $payment;
    }

    public function get($order_method)
    {
        $guestToken = getGuestTokenFromHeader();
        /* if(auth_user()->cust_id=="-1"){
            return new SuccessWithData([
                'cart' => [],
                'wishlist' => [],
            ]);
        } */
        $cart = $this->cart->getCartIds($order_method, $guestToken);
        return new SuccessWithData([
            'cart'      => $cart,
            'wishlist'  => []//app(SavedItemRepository::class)->getIDs($order_method),
        //    'approval'=>$cart[1]
        ]);
    }
    public function delivery_step1(Request $request)
    {
        $user = auth_user();
        if(@$user->cust_id < 1)
        {
            return new ErrorResponse("Operation failed.");
        }
        $validatedData = $request->validate([
            'order_method' => 'required',
            'branch_id' => 'required',

        ]);
        $order_method = $request->get('order_method');
        $customer=$this->getDeliveryAddress();
        if(is_null(@$customer->deli_retailer))
        {
            return new ErrorResponse("Operation failed.");
        }
        $branch_id = $request->get('branch_id');

        $shipping_method= $this->cart->delivery_step1($order_method,$customer->deli_retailer,$branch_id);
        return new SuccessWithData($shipping_method);

    }
    public function delivery_step2(Request $request)
    {
        $user = auth_user();
        if(@$user->cust_id < 1)
        {
            return new ErrorResponse("Operation failed");
        }
        $validatedData = $request->validate([
            'order_method' => 'required',
            'branch_id' => 'required',

        ]);
        $order_method = $request->get('order_method');
        $branch_id = $request->get('branch_id');
        $prescription_method= $this->cart->delivery_step2($order_method,$branch_id); 
        return new SuccessWithData($prescription_method);   
    }
    public function delivery_step3(Request $request)
    {
        $user = auth_user();
        if(!auth_user())
        {
            return new ErrorResponse("Operation failed");
        }
        $validatedData = $request->validate([
            'order_method' => 'required',
            'delivery_branch_id' => 'required',
            'shipping_method'=>'required',

        ]);
        $order_method = $request->get('order_method');
        $branch_id = $request->get('delivery_branch_id');
        $shipping_method = $request->get('shipping_method');
        $cart_item = $this->cart->get($order_method);
        $cart_items = $cart_item[0] ?? [];
        $price = [];
        if(count($cart_items) > 0)
        {
            $cart_items=array_values($cart_items);
            $price = app(CheckoutOrder::class)->getPriceValues($cart_items, $branch_id, 0,0,$shipping_method);
        }
       
        return new SuccessWithData([[
            "item_count" => count($cart_items),
            "pricedetails" => $price,
            "nearest_retailer" => $branch_id,
            "shipping_method"=>$request->get('shipping_method'),
            "prescription_id"=>isset($request->prescription_id)?$request->prescription_id:[]
        ]]);
        //$cart_items=array_values($cart_items);
        //return $this->getPrice($cart_items, $branch_id, 0, $cart_item_count);    
    }
    public function pickup_step1(Request $request)
    {
        $user = auth_user();
        if(!auth_user())
        {
            return new ErrorResponse("Operation failed");
        }
        $validatedData = $request->validate([
            'order_method' => 'required',
            'branch_id' => 'required',
  
        ]);
        $order_method = $request->get('order_method');
        $branch_id = $request->get('branch_id');
        $cart_item = $this->cart->get($order_method);
        $cart_items = $cart_item[0] ?? [];
        $cart_item_count = count($cart_items);
        return $this->getPrice($cart_items, $branch_id, 0, $cart_item_count);    
    
    }
    
    public function cartdetails($order_method)
    {
        $guestToken = getGuestTokenFromHeader();
        $cart = $this->cart->get($order_method, $guestToken);
        $cart = @$cart[0] ? $cart[0] : $cart;
        if(count($cart) == 0)
        {
            return new SuccessWithData([
                'data'          => $cart,
                'price_details' => [],
                'approval'      => 1,
                'nearestitems'  => []
            ]);
        }
        $productIDs = array_values(array_column($cart, 'cart_product_id'));
        $nearestItems = getNearestRetailerInventories($productIDs);
        $returns = [
            'data'          => $cart,
            'price_details' => $this->calculateCartPrice($cart),
            'approval'      => 1,
            'nearestitems'  => ($nearestItems) ? $nearestItems->toArray() : []
        ];
        return new SuccessWithData($returns);
    }
    private function calculateCartPrice($cartDetails)
    {
        $storegroupid = getHeaderStoreGroup();
        $totalMrpEt = 0;
        $sellerDiscount = 0;
        $orderTotalGst = 0;
        $total = 0;
        foreach ($cartDetails as $cart)
        {
            $branch_id = $cart["cart_branch_id"];
            $product_id = $cart["cart_product_id"];

            $branchInventory = DB::select("SELECT selling_price,issponsered,fpod_poLandingCostleastSKU,discount_selling_price, taxValue, cessValue FROM finascop_stock_branch_inventory bi inner join finascop_branch b on (b.br_ID=bi.branch_id OR (b.br_type = 1 AND b.br_typeParent = bi.branch_id)) WHERE b.br_ID = {$branch_id} AND stit_id = {$product_id} limit 1");
            $branch = Branch::where('br_ID', $branch_id)->first();

            $cartSalesPrice = $cart['cart_sales_price'];
            $cartPrice = $cart['cart_price'];
            $cartMrp = $cart['fpod_leastSKUmrp'];

            $itemMaster = $cart['item']['item_master'];
            $item = array_values(array_filter($itemMaster, function($it) use($product_id){
                if($it['stit_ID'] == $product_id)
                {
                    return $it;
                }
            }));
            $branchInventory = reset($branchInventory);
            $item = reset($item);
            // $taxValue = DB::table("hsn_value")->where("id", $item['taxValueId'])->first();
            $cosNos = (@$item['cos_nos'] > 0) ? $item['cos_nos'] : 1;
            /*$hsnCess = @$taxValue->hsnCess ?? 0;
            $hsnGst = @$taxValue->hsnGst ?? 0;*/
            $hsnCess = @$branchInventory->cessValue ?? 0;
            $hsnGst = @$branchInventory->taxValue ?? 0;

            // total_mrpet
            $mrpes = $cartMrp * $cosNos;
            $mrpEt = $mrpes * $cart['cart_order_qty'] * (1 - (($hsnCess + $hsnGst) / (100 + ($hsnGst + $hsnCess))));
            $mrpEt = floor($mrpEt * 100) / 100;
            if ($mrpEt > 0 && $hsnGst > 0)
            {
                $mrpEt = $this->nearestEvenDecimal($mrpEt);
            }
            $totalMrpEt += round($mrpEt, 2);

            // seller_discount
            $sellingpriceField = ($storegroupid > 0 && $branch->br_storeGroup == $storegroupid ? 'cart_sales_price' : (@$branchInventory->issponsered != 1 ? 'cart_sales_price' : ($cart['branch_type_id'] == 3 ? 'fpod_customerRateHmDel' : 'fpod_customerRateCouDel')));

            $selling_price = $cart[$sellingpriceField] * $cosNos;

            $basket_priceet = $selling_price * $cart['cart_order_qty'] * (1 - (($hsnCess + $hsnGst) / (100 + ($hsnGst + $hsnCess))));
            $basket_priceet = floor($basket_priceet * 100) / 100;
            if ($basket_priceet > 0 && $hsnGst > 0) {
                $basket_priceet = $this->nearestEvenDecimal($basket_priceet);
            }
            $sellerDiscount += round($mrpEt - $basket_priceet, 2) * -1;

            // order_total_gst
            $productCess = $basket_priceet * $hsnCess / 100;
            $productCess = floor($productCess * 100) / 100;
            if ($productCess > 0) {
                $productCess = $this->nearestEvenDecimal($productCess);
            }
            $new_price_tax = ($selling_price * $cart['cart_order_qty']) - ($basket_priceet + $productCess);
            if ($new_price_tax > 0) {
                $new_price_tax = $this->nearestEvenDecimal($new_price_tax);
            }
            $orderTotalGst += round($new_price_tax, 2);
        }
        $priceDetails = [
            'total_mrpet'       => round($totalMrpEt, 2),
            'seller_discount'   => $sellerDiscount,
            'order_total_gst'   => $orderTotalGst,
            'cart_total'        => round($totalMrpEt, 2) + $sellerDiscount + $orderTotalGst
        ];
        return $this->applyCartStyle($priceDetails);
    }
    private function applyCartStyle($priceDetails)
    {
        $styles = config('style.checkout');
        $styles = Arr::except($styles, ["order_delivery_charge", "order_discount", "order_courier_charge","delivery_charge_et", "total_tax", "order_roundoff", "subtotal", "order_total_amount", "total", "order_subtotal", "order_total_sgst", "order_total_cgst", "order_kfc_amount"]);

        foreach ($styles as $key => $style)
        {
            $i = 1;
            if(array_key_exists($key, $priceDetails))
            {
                $styles[$key]['order'] = $i;
                $styles[$key]['value'] = config('app.def_currency_symbol')." ".(string) $priceDetails[$key];
                $i++;
            }
        }
        return array_values($styles);
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

    public function cartdetails1($order_method)
    {
        $styles = [];
        $price = new \stdClass;
        $cart = $this->cart->get($order_method);
        $cartprod_ids=array();
        if (count($cart[0]) > 0) {
            $cartItems = is_array($cart[0]) ? $cart[0] : $cart[0]->toArray();
            $cartItems =array_values($cartItems);
            $cartprod_ids = array_map(function($item)
            {
                $ret = 0;
                $itemmaster = $item['item']['item_master'];
                foreach($itemmaster as $im)
                {
                    if($im['directDelivery'] == 1)
                    {
                        $ret = $im['stit_ID'];
                    }
                }
                return $ret;
            }, $cartItems);
            $price = app(CheckoutOrder::class)->priceCalculation($cartItems,0, 0, 2, 1);

            $branch_id = $cartItems[0]['cart_branch_id'] ?? 0;
            $styles = config('style.checkout');
            $styles = Arr::except($styles, ['order_delivery_charge', 'order_discount', 'order_courier_charge','delivery_charge_et','total_tax','order_roundoff','subtotal','order_total_amount','total','order_subtotal']);

			// $state = DetermineStates::find($branch_id);
                /*
                            if($state)
                            {
                                $styles = Arr::except($styles, ["order_total_gst"]);
                                $gst = $price['order_total_gst'] / 2;
                                $price['order_total_sgst'] = $gst_value = round($gst, 2); 
                                $price['order_total_cgst'] = $gst_value;
                            }
                            else {
                */
                $styles = Arr::except($styles, ["order_total_sgst", "order_total_cgst", "order_kfc_amount"]);
                //            }
            $i = 1;
            foreach($styles as $key => $style)
            {
                if(array_key_exists($key, $price))
                {
                    // exclude delivery charge for cart view.
			        if($key == 'total'){
                        try{
				            $val = $price[$key];
				            $deli = $price["order_delivery_charge"];
				            if($deli > 0 && $val > $deli){
					            $val = $val - $deli;
					            $price[$key] = $val;
				            }
                        }
                        catch (\Exception $e){}
			        }

                    $styles[$key]['order'] = $i;
                    $styles[$key]['value'] = config('app.def_currency_symbol')." ".(string) $price[$key];
                    $i++;
                }
            }
       }
       $nearestretailer_items=getNearestRetailerInventories($cartprod_ids);

        return new SuccessWithData([
            'data' =>(count($cart[0]) > 0)? array_values($cart[0]):[],
            'price_details' => array_values($styles),
            'approval'=>$cart[1],
	        'nearestitems'=>$nearestretailer_items
        ]);
    }

    public function wishlistdetails($order_method)
    {
        return new SuccessWithData([
            'data' => app(SavedItemRepository::class)->get($order_method),
        ]);
    }



    public function cartorder(Request $request)
    {
        

       /* $checkDeliveryInfo=$this->checkDeliveryInfo();
        if(!$checkDeliveryInfo){
            return new SuccessResponse('Please wait , we are finding the stores near by you');
        }*/
        $validatedData = $request->validate([
            'order_method' => 'required',
            'branch_id' => 'required',

        ]);

        $order_method = $request->get('order_method');
        $branch_id = $request->get('branch_id');
        $cart = $this->cart->orderchecking($order_method,$branch_id);

        if (count($cart) > 0) {
            $price = app(CheckoutOrder::class)->priceCalculation1($cart[0], $branch_id);
        }

        
        return new SuccessWithData([
            'hour'=>48,
            'cart' => $cart[0],
            'price' => $price,


        ]);


    }
    
    public function s3_bucket()
    {
        $s3Details = DB::table('s3_bucket')->first();
        
        $s3Client = new \Aws\S3\S3Client([
            'region' => $s3Details->region,
            'version' => 'latest',
            'credentials' => array(
                'key' => $s3Details->access_key,
                'secret' => $s3Details->secretkey,
            )
        ]);
        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket' => $s3Details->tobucket,
            'Key' => $s3Details->filepath
        ]);

        $request = $s3Client->createPresignedRequest($cmd, '+20 minutes');

            // Get the actual presigned-url
        $presignedUrl = (string) $request->getUri();
        
        return new SuccessWithData([
            'id'=>$s3Details->id,
            'name'=>$s3Details->name,
            'bucket'=>$s3Details->bucket,
            'tobucket'=>$s3Details->tobucket,
            'filepath'=>$s3Details->filepath,
            'region'=>$s3Details->region,
            'access_key'=>$s3Details->access_key,
            'secretkey'=>$s3Details->secretkey,
            'created_at'=>$s3Details->created_at,
            'presignedUrl' => $presignedUrl

        ]);
        //return new SuccessWithData(DB::table('s3_bucket')->first());
    }

    public function clear($order_method)
    {
        $guestToken = getGuestTokenFromHeader();
        return new SuccessResponse(
            $this->cart->clearItems($order_method, $guestToken)
        );
    }
    public function delete($id)
    {
        $guestToken = getGuestTokenFromHeader();
        return new SuccessWithData([
            'product_id' => $this->cart->delete($id, $guestToken)
        ]);
    }

    public function edit(EditCartRequest $request)
    {
        $request->merge(['guest_token' => getGuestTokenFromHeader()]);
        $this->cart->edit($request);
        return new SuccessResponse('cart updated successfully');
    }
    /**
     * Undocumented function
     *
     * @param AddToCartRequest $request
     * @return void
     */
    public function store(AddToCartRequest $request)
    {
        if ($request['type'] == 1) {
            $this->savedItem->delete($request['cart_group_id'], $request['cart_product_id'], $request['order_method']);
        }
        $request->merge(['guest_token' => getGuestTokenFromHeader()]);
        return new SuccessWithData(
            ['cart_product_ids' => $this->cart->store($request->all())]
        );
    }

    public function ReplaceItem(AddToCartRequest $request){
        $this->cart->delete($request['cart_id']);
        return new SuccessWithData(
            ['cart_product_ids' => $this->cart->store($request->validated())]
        );
    }


    public function bulkstore(AddToBulkCartRequest $request)
    {

        $request->validated();

        $requests = $request->toArray();
        $data = '';
        foreach ($requests as $request) {


            if ($request['type'] == 1) {
                $this->savedItem->delete($request['cart_group_id'], $request['cart_product_id'], $request['order_method']);
            }

            $data=$this->cart->store($request);
        }
        return new SuccessWithData(
            ['cart_product_ids' => $data]
        );
    }
    public function order_success($order_id)
    {
        $price = app(CheckoutOrder::class)->order_success($order_id);
        return new SuccessResponse('Status changed successfully');
    }
    public function checkprocessed(Request $request)
    {
        $validatedData = $request->validate([
            'order_method' => 'required',
            'branch_id' => 'required',
            'selection'=>'nullable|array',
        ]);

       

        $order_method = $request->get('order_method');
        $branch_id = $request->get('branch_id');
        $selection = $request->get('selection');
        $count = count($selection);
        $cart_item = $this->cart->get($order_method);
        $cart_items = $cart_item[0] ?? [];
        $cart_item_count = count($cart_items);
        $select = '';
     /*    $prescriptionCheck=$this->cart->prescriptionCheck($cart_items);
        if($prescriptionCheck==true){
            return new SuccessResponse('Need to upload prescription');
        }*/
        if($order_method == 1)
        {
            $cart = $this->cart->orderchecking($order_method,$branch_id);
            $available = $cart[0]['all_available_product_quality'] ?? [];
            $not_available = $cart[0]['not_available_product_quality_in_48_hours'] ?? [];
            $all_available = $cart[0]['all_product_in_48_hours'] ?? [];
            if($count == 1)
            {
                $value = implode(",", $selection);
                $select = in_array($value, [1, 2, 3]) ? $value : 0;
            }
            else if(count(array_diff($selection, [1, 2])) == 0)
            {
                $select = 4;
                $cart_combine = array_merge($available, $not_available);
            }
            switch ($select)
            {
                case 1 : return $this->getPrice($available, $branch_id, $select, $cart_item_count);
                break;
                case 2 : return $this->getPrice($not_available, $branch_id, $select, $cart_item_count);
                break;
                case 3 : return $this->getPrice($all_available, $branch_id, $select, $cart_item_count);
                break;
                case 4 : return $this->cartCombine($cart_combine, $branch_id, $select, $cart_item_count);
                break;
                default : throw new ErrException("Invalid selection");
            }
        }
        else if($order_method == 2) {
            return $this->getPrice($cart_items, $branch_id, 0, $cart_item_count);
        }

    }

    
    private function getPrice(array $cart, $branch_id, int $selection, $count)
    {
        $price = [];
        if(count($cart) > 0)
        {
            $price = app(CheckoutOrder::class)->getPriceValues($cart, $branch_id, $selection);
        }
        $customer = $this->getDeliveryAddress();
 
        return new SuccessWithData([[
            "item_count" => $count,
            "pricedetails" => $price,
            "nearest_retailer" => RetailerLocation::fetchRetailer($customer),
        ]]);
    }

    private function cartCombine(array $cart_combine, $branch_id, $select, $cart_item_count)
    {
        $styles = [];
        if(count($cart_combine) > 0)
        {
            $all = 1;
            $i = 1;
            $state = DetermineStates::find($branch_id);
            $price = app(CheckoutOrder::class)->priceCalculation($cart_combine, $select, $all);
            $styles = config('style.checkout');
            /*
            if($state)
            {
                $styles = Arr::except($styles, ["order_total_gst"]);
                $gst = $price['order_total_gst'] / 2;
                $price['order_total_sgst'] = $gst_value = round($gst, 2); 
                $price['order_total_cgst'] = $gst_value;
            }
            else {
            */
                $styles = Arr::except($styles, ["order_total_sgst", "order_total_cgst", "order_kfc_amount"]);
            // }
            foreach($styles as $key => $style)
            {
                if(array_key_exists($key, $price))
                {
                    $styles[$key]['order'] = $i;
                    $styles[$key]['value'] = config('app.def_currency_symbol')." ".(string) $price[$key];
                    $i++;
                }
            }
        }
        $customer = $this->getDeliveryAddress();

        return new SuccessWithData([[
            "item_count" => $cart_item_count,
            "pricedetails" => array_values($styles),
            "nearest_retailer" => RetailerLocation::fetchRetailer($customer),
        ]]);
    }

    private function getDeliveryAddress()
    {
        return $this->address->where('deli_customer_id', auth()->user()->cust_id)
                        ->where('deli_is_primary',1)
                        ->first();
    }

    public function checkprocessed_old(Request $request)
    {
        $validatedData = $request->validate([
            'order_method' => 'required',
            'branch_id' => 'required',
            'selection'=>'nullable|array',
        ]);


        $data=array();
        $order_method = $request->get('order_method');
        $branch_id = $request->get('branch_id');
        $cart_item = $this->cart->get($order_method);
        $styles = config('style.checkout');
            if($order_method==1)
            {

                $cart = $this->cart->orderchecking($order_method,$branch_id);


                if (count($cart) > 0) {
                    $price = app(CheckoutOrder::class)->priceCalculation1($cart[0]);
                }


                if(isset($request['selection'])){
                    if(in_array(1,$request['selection']) && in_array(2,$request['selection']))
                    {


                        foreach($styles as $key => $style)
                        {

                                ($key === "total")? $styles[$key]['value'] = "₹ ".(string)(trim(str_replace("₹","",$price['all_available_product_price'][6]['value']))+trim(str_replace("₹","",$price['not_available_product_price_48_hours'][6]['value']))) : "";
                                ($key === "subtotal")? $styles[$key]['value'] = "₹ ".(string)(trim(str_replace("₹","",$price['all_available_product_price'][0]['value']))+trim(str_replace("₹","",$price['not_available_product_price_48_hours'][0]['value']))) : "";
                                ($key === "order_delivery_charge")? $styles[$key]['value'] = "₹ ".(string)(trim(str_replace("₹","",$price['all_available_product_price'][4]['value']))+trim(str_replace("₹","",$price['not_available_product_price_48_hours'][4]['value']))) : "";
                                ($key === "order_total_gst")? $styles[$key]['value'] ="₹ ".(string)(trim(str_replace("₹","",$price['all_available_product_price'][2]['value']))+trim(str_replace("₹","",$price['not_available_product_price_48_hours'][2]['value']))) : "";


                        }


                        array_push($data,array(
                            'item_count'=>count($cart_item[0]),
                            'pricedetails'=>array_values($styles),
                        ));
                    }
                    else if(in_array(1,$request['selection']))
                    {


                        foreach($styles as $key => $style)
                        {

                                ($key === "total")? $styles[$key]['value'] = "₹ ".(string)(trim(str_replace("₹","",$price['all_available_product_price'][6]['value']))) : "";
                                ($key === "subtotal")? $styles[$key]['value'] = "₹ ".(string)(trim(str_replace("₹","",$price['all_available_product_price'][0]['value']))) : "";
                                ($key === "order_delivery_charge")? $styles[$key]['value'] = "₹ ".(string)(trim(str_replace("₹","",$price['all_available_product_price'][4]['value']))) : "";
                                ($key === "order_total_gst")? $styles[$key]['value'] ="₹ ".(string)(trim(str_replace("₹","",$price['all_available_product_price'][2]['value']))) : "";


                        }
                        array_push($data,array(
                            'item_count'=>count($cart_item[0]),
                            'pricedetails'=>array_values($styles),

                        ));
                    }
                    else if(in_array(2,$request['selection']))
                    {


                        foreach($styles as $key => $style)
                        {

                                ($key === "total")? $styles[$key]['value'] = "₹ ".(string)(trim(str_replace("₹","",$price['not_available_product_price_48_hours'][6]['value']))) : "";
                                ($key === "subtotal")? $styles[$key]['value'] = "₹ ".(string)(trim(str_replace("₹","",$price['not_available_product_price_48_hours'][0]['value']))) : "";
                                ($key === "order_delivery_charge")? $styles[$key]['value'] = "₹ ".(string)(trim(str_replace("₹","",$price['not_available_product_price_48_hours'][4]['value']))) : "";
                                ($key === "order_total_gst")? $styles[$key]['value'] ="₹ ".(string)(trim(str_replace("₹","",$price['not_available_product_price_48_hours'][2]['value']))) : "";


                        }
                        array_push($data,array(
                            'item_count'=>count($cart_item[0]),
                            'pricedetails'=>array_values($styles),

                        ));
                    }
                    else if(in_array(3,$request['selection']))
                    {

                        foreach($styles as $key => $style)
                        {

                                ($key === "total")? $styles[$key]['value'] = "₹ ".(string)(trim(str_replace("₹","",$price['all_product_price_48_hours'][6]['value']))) : "";
                                ($key === "subtotal")? $styles[$key]['value'] = "₹ ".(string)(trim(str_replace("₹","",$price['all_product_price_48_hours'][0]['value']))) : "";
                                ($key === "order_delivery_charge")? $styles[$key]['value'] = "₹ ".(string)(trim(str_replace("₹","",$price['all_product_price_48_hours'][4]['value']))) : "";
                                ($key === "order_total_gst")? $styles[$key]['value'] ="₹ ".(string)(trim(str_replace("₹","",$price['all_product_price_48_hours'][2]['value']))) : "";


                        }
                        array_push($data,array(
                            'item_count'=>count($cart_item[0]),
                            'pricedetails'=>array_values($styles),

                        ));
                    }
                }
            }
            else{

                if (count($cart_item) > 0) {
                    $price = app(CheckoutOrder::class)->priceCalculation($cart_item[0]);
                }

                foreach($styles as $key => $style)
                        {

                                ($key === "total")? $styles[$key]['value'] = "₹ ".(string)$price['total'] : "";
                                ($key === "subtotal")? $styles[$key]['value'] = "₹ ".(string)$price['basket_price'] : "";
                                ($key === "order_delivery_charge")? $styles[$key]['value'] = "₹ ".(string)$price['delivery_charge'] : "";
                                ($key === "order_total_gst")? $styles[$key]['value'] ="₹ ".(string)$price['total_gst'] : "";


                        }

                array_push($data,array(
                    'item_count'=>count($cart_item[0]),
                    'pricedetails'=>array_values($styles),
                ));

            }


            return new SuccessWithData(
                $data
            );





    }

    public function checkOut()
    {   

        $cart = $this->cart->checkOut();
        $code = $cart['stock_available'] == false ? 400 : 200;
        return new SuccessWithData(
            $cart,
            $code
        );
    }
    private function checkDeliveryInfo()
    {
        $res=true;
        $customer = $this->customer->with(['deliveryInfo'=>function($q){
                $q->where('deli_is_primary',1);
            }])
            ->where('cust_id', auth()->user()->cust_id)
            //->where('cust_issetretcs', 0)
            ->first();    
        if(($customer->cust_issetretcs==0) || (!isset($customer->deliveryInfo)) || ($customer->deliveryInfo['deli_retailer']==0 && $customer->deliveryInfo['deli_branch_id']==0)){
            $res=false;
        }
        return $res;
    }

    public function moveToWishList()
    {
        return new SuccessResponse(
            $this->cart->moveToWishList()
        );
    }

    public function cartSummary($order_method)
    {
        $storegroupid = getHeaderStoreGroup();
        $cart = Cart::select("id", "cart_customer_id", "cart_group_id", "cart_product_id", "cart_branch_id", "cart_order_qty")
        ->where([
            ['storegroup_id', $storegroupid],
            ['order_method', $order_method],
        ]);
        if(auth()->check())
        {
            $cart->where('cart_customer_id', auth()->user()->cust_id);
        }
        else
        {
            $cart->whereNotNull("guest_token")->where("guest_token", "<>", "")->whereRaw("TRIM(guest_token) <> ''") ->where("guest_token", getGuestTokenFromHeader());
        }
        $cart = $cart->get();
        
        $savedItem = [];
	    if(auth()->check())
        {
            $savedItem = auth()->user()->savedItems()
            ->select("id", "customer_id", "group_id", "product_id", "branch_id", "order_method", "branch_type_id")
            ->where([
                ['order_method', $order_method],
                ['storegroupid', $storegroupid]
            ])->get();
        }
        return new SuccessWithData([
            "cart"      => $cart,
            'wishlist'  => $savedItem
        ]);
    }

    public function cartPreview($order_method)
    {
        $cart = $this->cart->get($order_method);
        $subtotal = (count(@$cart[0]) > 0) ? array_sum(array_column($cart[0], 'cart_price')) : 0;
        $sellingtotal = (count(@$cart[0]) > 0) ? array_sum(array_column($cart[0], 'cart_sales_price')) : 0;
        return new SuccessWithData([
            'cart'      => @$cart[0],
            'subtotal'  => @$subtotal,
            'discount'  => @($subtotal - $sellingtotal),
            'total'     => @$sellingtotal
        ]);
    }
}
