<?php

namespace App\Http\Repositories\Cart;


use App\Models\Cart;
use stdClass;
use App\Product;
use App\Models\Customer;
use App\Models\BlockedItems;
use App\Models\DeliveryInfo;
use App\Models\StockItemMaster;
use App\Models\UploadPrescription;
use Illuminate\Support\Arr;
use App\Models\prescriptiomMedicineMap;
use App\Location\RetailerLocation;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\Checkout;
use App\Http\Repositories\ItemPrice;
use App\Domains\Cart\PrepareCartItem;
use App\Http\Repositories\Item\Price;
use App\Http\Repositories\Item\Stock;
use BackOffice\Models\BranchInventory;
use App\Http\Repositories\StockAvailable;
use App\Http\Repositories\Item\CheapPrice;
use Illuminate\Support\Facades\Log;
use App\Models\SavedItem;


class CartRepository
{
    protected $cart;

    protected $product;
    protected $customer;
    protected $uploaddocument;
    protected $itemMaster;
    protected $medinicemap;
    protected $deliveryinfo;
    protected $savedItem;

   public function __construct(DeliveryInfo $deliveryinfo,Cart $cart, Customer $customer, UploadPrescription $uploaddocument, StockItemMaster $itemMaster, prescriptiomMedicineMap $medinicemap, SavedItem $savedItem)
   {
        $this->cart = $cart;
        $this->customer = $customer;
        $this->uploaddocument = $uploaddocument;
        $this->itemMaster = $itemMaster;
        $this->medinicemap = $medinicemap;
        $this->deliveryinfo=$deliveryinfo;
        $this->savedItem = $savedItem;
   }

    public function delivery_step1($order_method,$branch_id,$old_branch_id){
        $shipping_method=$cartitemProductIds=array();
        $home_delivery=false;
        $storegroupid = getHeaderStoreGroup();

        $cartitems = $this->cart->where('order_method', $order_method)->has('item')
        ->with(['item' => function ($query) {
            $query->selectRaw($this->getItemFieldsRaw())
                ->has('itemMaster')
                ->with(['itemMaster' => function ($query) {
                    $query->select($this->itemMasterFields());
                }]);
        }])
        ->where('cart_customer_id', auth()->user()->cust_id)->where('storegroup_id', $storegroupid)
        ->get();
   
        if($branch_id){
          //  $cartitems=$this->cart->where('order_method', $order_method)->where('cart_customer_id', auth()->user()->cust_id)->get();
           
            foreach($cartitems as $cartitem)
            {
                $cartitemProductIds[]=$cartitem['cart_product_id'];
            }
            $branchInventory = BranchInventory::whereIn('stit_id', $cartitemProductIds)->where('branch_id',$branch_id)->where('item_count','>',0)->pluck('item_count','stit_id')->toArray();
            if(!empty($branchInventory) && count($branchInventory)==count($cartitemProductIds)){
                $home_delivery=true;
            }
            if($home_delivery==true){
                foreach($cartitems as $cartitem){
                    if (isset($branchInventory[$cartitem['cart_product_id']]) && $branchInventory[$cartitem['cart_product_id']] < $cartitem['cart_order_qty']) {
                        $home_delivery=false;
                        break;
                    }
                }
            }

        }
        if($home_delivery==true){
            $percentage=$this->getCartSaving($cartitems,$order_method,$branch_id,1);
            $shipping_method["home_delivery"]["percentage"]=$percentage["percentage"];
            $shipping_method["home_delivery"]["selling_price"]=$percentage["selling_price"];
            $shipping_method["home_delivery"]["title"]="Home Delivery";
             $shipping_method["home_delivery"]["branch_id"]=$branch_id;
              $shipping_method["home_delivery"]["description"]="Save ".$percentage["percentage"].": We found a Retailer nearby. We can deliver your order soonest possible for ".config('app.def_currency_symbol')." ".$percentage["selling_price"]." with ".$percentage["percentage"]."% savings";
        //$shipping_method[0]["value"]="home_delivery";
        }
        $percentage=$this->getCartSaving($cartitems,$order_method,$old_branch_id,2);
        $shipping_method["courier_delivery"]["percentage"]=$percentage["percentage"];
        $shipping_method["courier_delivery"]["selling_price"]=$percentage["selling_price"];
        $shipping_method["courier_delivery"]["branch_id"]=$old_branch_id;
        $shipping_method["courier_delivery"]["title"]="Courier Delivery";
        $shipping_method["courier_delivery"]["description"]="Save ".$percentage["percentage"]."%: We can arrange delivery through Courier for ".config('app.def_currency_symbol')." ".$percentage["selling_price"]."  with ".$percentage["percentage"]." % savings";
       // $shipping_method[1]["value"]="courier_delivery";
        return $shipping_method;
    }
    
    public function getCartSaving($cart,$order_method,$branch_id,$shipping_method){
        if($cart){
            $data = $cart->toArray() ? $this->addStock($cart->toArray(),$order_method,$branch_id,$shipping_method) : $cart;
            $percentage=$selling_price=$quantity=0 ;
            foreach($data as $item){
                $item_masters=$item['item']['item_master'];
                 
                //if($item_masters){
                    foreach($item_masters as $item_master){
                        if($item_master["stit_ID"]==$item["cart_product_id"]){
                            $quantity=$quantity+$item['cart_order_qty'];
                            if(!empty($item_master["percentage"])){
                                $percentage=$percentage+$item_master["percentage"]*$item['cart_order_qty'];
                                $selling_price=$selling_price+$item_master["selling_prize"]*$item['cart_order_qty'];
                               
                            }
                        }
                    }
               // }
            }
            if(!empty($quantity)){
                return ["percentage"=>round($percentage/$quantity,1),"selling_price"=>round($selling_price,2)];
            }else{
                return ["percentage"=>0,"selling_price"=>round($selling_price,2)];
            }
           
        }else{
            return ["percentage"=>0,"selling_price"=>0];
        }
       
    }
    public function delivery_step2($order_method,$branch_id){
        $storegroupid = getHeaderStoreGroup();

        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;
        $cart = $this->cart->where('order_method', $order_method)->has('item')
            ->with(['item' => function ($query)use($domain) {
                $query->selectRaw($this->getItemFieldsRaw())
                    ->has('itemMaster')
                    ->with(['itemMaster' => function ($query)use($domain) {
                        $query->select($this->itemMasterFields())
                            ->with(['mainImage' => function ($query)use($domain) {
                                $query->where('image_type', 1)
                                ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));

                            }]);
                    }]);
            }])
            ->where('cart_customer_id', auth()->user()->cust_id)
            ->where('storegroup_id', $storegroupid)
            ->get();
        $cart=$this->prescriptionMapped($cart);
        $data = $cart->toArray() ? $this->addStock($cart->toArray(),$order_method,$branch_id) : $cart;
        return $data;
       
    }

    // Get cart object from database with live stock and live price by join the master tables for stock, branch and rate contract.
    public function getcartobj($order_method, array $cartids, $availableStockOnly=0, $guestToken = ""){


        $mylat=0; $mylng=0;
        $isGuest = true;
        $usr = auth()->user();

        if($usr)
        {
            $isGuest = false;
            if(isset($usr->primaryAddress))
            {
                $mylat = $usr->primaryAddress->deli_latitude;
                $mylng = $usr->primaryAddress->deli_longitude;
            }    
        }
        else
        {
            $getGuestLatLong = getGuestLocationFromHeader();
            $mylat = @$getGuestLatLong["lat"];
            $mylng = @$getGuestLatLong["long"];
        }


        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;
        $storegroupid = getHeaderStoreGroup();

        $cart = $this->cart
            ->leftJoin('finascop_contractpo_products as cpo', function($join) { // left join rate contract table.
                  $join->on('cpo.fcpod_itemid', '=', 'retaline_cart.cart_product_id');
                  $join->on('cpo.fcpod_vendorid', '=', 'retaline_cart.cart_branch_id');
                  $join->on('retaline_cart.branch_type_id', '=', DB::raw("2"));
                })
            ->leftJoin(DB::raw('(SELECT bi.stit_id, b.br_ID AS branch_id, b.br_storeGroup, bi.mrp, (IFNULL(bi.item_count, 0) - IFNULL(blockedNum, 0)) AS item_count, b.br_directDelivery,
            b.br_courierDelivery, fpod_leastSKUmrp, fpod_customerRateHmDel, fpod_customerRateCouDel, fpod_customerRatePikup,selling_price, b.max_delivery_distance, location
 FROM finascop_stock_branch_inventory AS bi INNER JOIN finascop_branch AS b ON (b.br_ID=bi.branch_id OR (br_type = 1 AND b.br_typeParent = bi.branch_id))
LEFT JOIN (SELECT item_id, SUM(`count`) AS blockedNum FROM finascop_stock_blocked GROUP BY item_id) blocked ON blocked.item_id = bi.stit_id) as br'), function($join)
                        {
                           $join->on('br.stit_id', '=', 'retaline_cart.cart_product_id');
                           $join->on('br.branch_id', '=', 'retaline_cart.cart_branch_id');
                           $join->on('retaline_cart.branch_type_id', '!=', DB::raw("2"));
                        })
            //->leftJoin('finascop_stock_branch_inventory', function($join) { // left join branch inventory.
            //      $join->on('finascop_stock_branch_inventory.stit_id', '=', 'retaline_cart.cart_product_id');
            //      $join->on('finascop_stock_branch_inventory.branch_id', '=', 'retaline_cart.cart_branch_id');
            //      $join->on('retaline_cart.branch_type_id', '!=', DB::raw("2"));
            //    })
            ->where('order_method', $order_method)->has('item')
            ->with(['item' => function ($query)use($domain) {
                $query->selectRaw($this->getItemFieldsRaw())
                    ->has('itemMaster')
                    ->with(['itemMaster' => function ($query)use($domain) {
                        $query->select($this->itemMasterFields())
                            ->with(['mainImage' => function ($query)use($domain) {
                                $query->where('image_type', 1)
                                ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));

                            }]);
                    }]);
            }])
            ->where('storegroup_id', $storegroupid);
            if(auth()->check())
            {
                $cart = $cart->where('cart_customer_id', auth()->user()->cust_id);
            }
            else
            {
                $cart = $cart->whereNotNull('guest_token')->where('guest_token', "<>", "")->whereRaw("TRIM(guest_token) != ''") ->where('guest_token', $guestToken);
            }
if(isset($cartids) && !empty($cartids))
    $cart = $cart->whereIn('retaline_cart.id', $cartids);
if($availableStockOnly > 0)
   $cart = $cart->where(function ($cart){
    	$cart->where('retaline_cart.branch_type_id', '=', 2)->orWhere('br.item_count','>=', DB::raw('retaline_cart.cart_order_qty'));
   });

	       $sellingpriceField = ($storegroupid > 0 ? 'selling_price' : 'CASE retaline_cart.branch_type_id WHEN 2 THEN cpo.fcpod_customerRateCouDel WHEN 1 THEN br.fpod_customerRateCouDel ELSE IFNULL( br.fpod_customerRateHmDel, 0 ) END');

            $cart = $cart->select('retaline_cart.id', 'retaline_cart.cart_customer_id', 'cart_product_id', 'retaline_cart.cart_group_id', 'retaline_cart.cart_branch_id', 'retaline_cart.cart_order_qty', 'retaline_cart.storegroup_id',
                'max_delivery_distance', DB::Raw('(case when retaline_cart.branch_type_id =3 then ST_Distance_Sphere(POINT('. $mylng . ', '.$mylat.'), location) * 0.001 else 0 end) AS distance'),
                DB::Raw('IFNULL( br.mrp , cpo.fcpod_leastSKUmrp ) + 0E0  AS cart_price'), DB::Raw('IFNULL( br.mrp , cpo.fcpod_leastSKUmrp ) + 0E0  AS cart_retail_price'),
                //DB::Raw('IFNULL( br.fpod_customerRateCouDel , cpo.fcpod_customerRateCouDel ) + 0E0  AS cart_sales_price'),
                //DB::Raw('CASE retaline_cart.branch_type_id WHEN 2 THEN cpo.fcpod_customerRateCouDel WHEN 1 THEN fpod_customerRatePikup ELSE IFNULL( fpod_customerRatePikup, 0 ) END + 0E0 AS cart_sales_price'),

                // DB::Raw($sellingpriceField . ' + 0E0 AS cart_sales_price'),
                'retaline_cart.cart_sales_price',
                'retaline_cart.cart_subcategory_id', 'retaline_cart.cart_package_type_id', 'retaline_cart.cart_is_taxable', 
                'retaline_cart.cart_cgst', 'retaline_cart.cart_sgst', 'retaline_cart.cart_igst', 'retaline_cart.cart_discount', 'retaline_cart.cart_sku_id', 'retaline_cart.cart_status', 'retaline_cart.order_method', 'retaline_cart.created_at', 
                'retaline_cart.updated_at', 'retaline_cart.branch_type_id', 
DB::Raw('IFNULL( br.fpod_leastSKUmrp , cpo.fcpod_leastSKUmrp ) + 0E0  AS fpod_leastSKUmrp'),
DB::Raw('IFNULL( br.fpod_customerRateHmDel , cpo.fcpod_customerRateHmDel ) + 0E0  AS fpod_customerRateHmDel'),
DB::Raw('IFNULL( br.fpod_customerRatePikup , -1 ) + 0E0  AS fpod_customerRatePikup'),
DB::Raw('IFNULL( br.fpod_customerRateCouDel , cpo.fcpod_customerRateCouDel ) + 0E0  AS fpod_customerRateCouDel'),
                DB::Raw('(CASE retaline_cart.branch_type_id WHEN 2 THEN 1000 ELSE IFNULL( br.item_count , 0 ) END) + 0E0 AS stock_at_branch'))
                ->groupBy('retaline_cart.id')
            ->get();

		return $cart;	
    }



    /**
     * Retrieve all items in the cart.
     *
     * @return \Illuminate\Database\Eloquent\Model
    */
    public function get($order_method, $guestToken = "")
    {

        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;
        
        /*comment by Mitali 
        $medicinedata=array();
        $cartdatas =$cartitems=$this->cart->where('order_method', $order_method)->where('cart_customer_id', auth()->user()->cust_id)->get();
        $i=0;
         foreach($cartitems as $cartitem)
         {
            $checkforMedicine=$this->itemMaster->where('stit_ID',$cartitem['cart_product_id'])->where('prescription',1)->where('isMedicine',1)->get();
            $checkforMedicinenotprepention=$this->itemMaster->where('stit_ID',$cartitem['cart_product_id'])->where('prescription',0)->where('isMedicine',1)->get();
            if(count($checkforMedicinenotprepention)>0)
            {

                $medicinedata[$i][$cartitem['id']]=array("ispreciption"=>0,"mapped"=>0);

            }
            if(count($checkforMedicine)>0)
            {
                $map=$this->medinicemap->where('stit_Id',$cartitem['cart_product_id'])->where('cust_id', auth()->user()->cust_id)->where('pmm_expirydate', '>=', date('y-m-d'))->get();

                if(count($map)>0)
                {
                    $medicinedata[$i][$cartitem['id']]=array("ispreciption"=>1,"mapped"=>1);
                }
                else{
                    $medicinedata[$i][$cartitem['id']]=array("ispreciption"=>1,"mapped"=>0);
                }
            }



         }
         /*comment by Mitali */



        ////
/*        
        $cart = $this->cart->where('order_method', $order_method)->has('item')
            ->with(['item' => function ($query)use($domain) {
                $query->selectRaw($this->getItemFieldsRaw())
                    ->has('itemMaster')
                    ->with(['itemMaster' => function ($query)use($domain) {
                        $query->select($this->itemMasterFields())
                            ->with(['mainImage' => function ($query)use($domain) {
                                $query->where('image_type', 1)
                                ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));

                            }]);
                    }]);
            }])
            ->where('cart_customer_id', auth()->user()->cust_id)
            ->get();
*/
        $cart = $this->getcartobj($order_method, [], 0, $guestToken);

          //  $cart=$this->prescriptionMapped($cart);
       
      /*      foreach($cart as $cartitem)
            {
                $cartitemProductIds[]=$cartitem['cart_product_id'];
            }
            $checkforMedicines=$this->itemMaster->whereIn('stit_ID',$cartitemProductIds)->where('prescription',1)->where('isMedicine',1)->pluck('stit_Id')->toArray();    
          */
             $approval_status = 1;
            /*comment by Mitali
            foreach($cart as $key =>$value)
            {
              //  $cart[$key]['isprescription']=(in_array($value['cart_product_id'],$checkforMedicines))?1:0;
                    
                
                foreach($prescriptionMapped as $element)
                {

                    $keys=array_keys($element);
                   if (in_array($value['id'],$keys))
                   {
                        $item_element=$element[$value['id']];

                        $cart[$key]['isprescription']=$item_element['ispreciption'];
                        $cart[$key]['prescription_mapped']=$item_element['mapped'];

                    }

                }
               
        

            }
         /*comment by Mitali */    
        /*
        foreach ($cartdatas as $cartdata) {
            $data = $this->itemMaster->where('stit_ID', $cartdata['cart_product_id'])->where('prescription',1)->where('isMedicine', 1)->get();
            if (count($data) > 0) {
                $exist = $this->medinicemap->where('stit_Id', $cartdata['cart_product_id'])->where('cust_id', auth()->user()->cust_id)->where('pmm_expirydate', '>=', date('y-m-d'))->get();
                if (count($exist) == 0) {
                    $approval_status = 0;
                }
            }
        }
        */





        // $upload_ids = $this->cart->select('upload_id')
        //     ->where('order_method', $order_method)
        //     ->where('cart_customer_id', auth()->user()->cust_id)
        //     ->groupBy('upload_id')
        //     ->get();


        // foreach ($upload_ids as $upload_id) {
        //     $checking = $this->uploaddocument->where('id', $upload_id['upload_id'])->where('status', 2)->where('expiry_date', '>=', date('Y-m-d'))->get();

        //     $is_exit = $this->uploaddocument->where('id', $upload_id['upload_id'])->get();
        //     if (count($checking) != 0 && count($is_exit) > 0) {
        //         $approval_status = 1;
        //     }
        // }



        $data = $cart->toArray() ? $this->addStock($cart->toArray(),$order_method) : $cart;

        return array($data, $approval_status);
    }



    public function getCartIds($order_method, $guestToken = ""){
        $cart=array();
        $storegroupid = getHeaderStoreGroup();
        $cartdatas =$this->cart->select(['cart_product_id','cart_order_qty', 'cart_group_id','storegroup_id'])->where('order_method', $order_method)->where('storegroup_id', $storegroupid);
        if(auth()->check())
        {
            $cartdatas = $cartdatas->where('cart_customer_id', auth()->user()->cust_id);
        }
        else
        {
            $cartdatas = $cartdatas->whereNotNull("guest_token")->where("guest_token", "<>", "")->whereRaw("TRIM(guest_token) != ''") ->where("guest_token", $guestToken);
        }

        return $cartdatas->get()->toArray();
    }
    public function orderchecking($order_method, $branch_id)
    {
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;
        $storegroupid = getHeaderStoreGroup();

        $cartdatas = array();
      //  $cart = $this->cart->where('order_method', $order_method)->where('cart_branch_id', $branch_id)->has('item')
          $cart = $this->cart->where('order_method', $order_method)->where('cart_branch_id', $branch_id)->where('storegroup_id', $storegroupid)->has('item')
                  ->with(['item' => function ($query)use($domain) {
                    $query->select($this->getItemFields())
                        ->has('itemMaster')
                        ->with(['itemMaster' => function ($query)use($domain) {
                            $query->select($this->itemMasterFields())
                                ->with(['mainImage' => function ($query)use($domain) {
                                    $query->where('image_type', 1)
                                    ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));

                                }]);
                        }]);
                }])
                ->where('cart_customer_id', auth()->user()->cust_id)
                ->get();
        $cart=$this->prescriptionMapped($cart);
        /*foreach($cart as $key =>$value)
            {
                foreach($prescriptionMapped as $element)
                {

                    $keys=array_keys($element);
                   if (in_array($value['id'],$keys))
                   {
                        $item_element=$element[$value['id']];

                        $cart[$key]['isprescription']=$item_element['ispreciption'];
                        $cart[$key]['need_prescription']=($item_element['ispreciption']==1 && $item_element['mapped']==0 )?1:0;

                    }

                }

            }*/
        $cartdatas = $cart->toArray() ? $this->addStock($cart->toArray(),$order_method) : $cart;

        if ($order_method == 2) {

            return $this->splitorder($cartdatas,$branch_id);

        } else {

            //nearest retailer in center store
            //$retailer_branch=46;
/*            $customer=$this->deliveryinfo->where('deli_customer_id',auth()->user()->cust_id)->where('deli_is_primary',1)->first();
            $retailer_branch = RetailerLocation::fetchRetailer($customer);
            return $this->splitorder($cartdatas,$retailer_branch);*/
              return $this->splitorder($cartdatas,$branch_id);


            }



    }



    public function splitorder($cartdatas,$branch_id)
    {
        $final_data=array();

        foreach ($cartdatas as $key => $cartdata) {

            $data = BranchInventory::where('stit_id', $cartdata['cart_product_id'])->where('branch_id',$branch_id)->get();
            if (count($data) > 0) {

                if ($data[0]['item_count'] >= $cartdata['cart_order_qty']) {
                    $available = $cartdata['cart_order_qty'];
                    $late_deliver = 0;
                    //array_push($final_data,array('cart_id'=>$cartdata['id'],'stit_id'=>$cartdata['cart_product_id'],'purchase_quantity'=>$available,'availabe_quantity'=>$available,'notavailable_quantity'=>$late_deliver));
                    $cartdatas[$key]['availabe_quantity'] = $available;
                    $cartdatas[$key]['notavailable_quantity'] = $late_deliver;
                } else {
                    $late_deliver = $cartdata['cart_order_qty'] - $data[0]['item_count'];
                    $available = $cartdata['cart_order_qty'];
                    $cartdatas[$key]['availabe_quantity'] = $available - $late_deliver;
                    $cartdatas[$key]['notavailable_quantity'] = $late_deliver;
                    // array_push($final_data,array('cart_id'=>$cartdata['id'],'stit_id'=>$cartdata['cart_product_id'],'purchase_quantity'=>$available,'availabe_quantity'=>$available-$late_deliver,'notavailable_quantity'=>$late_deliver));
                }
            } else {
                $late_deliver = $cartdata['cart_order_qty'];
                $available = 0;

                $cartdatas[$key]['availabe_quantity'] = 0;
                $cartdatas[$key]['notavailable_quantity'] = $late_deliver;
                //array_push($final_data,array('cart_id'=>$cartdata['id'],'stit_id'=>$cartdata['cart_product_id'],'purchase_quantity'=>$cartdata['cart_order_qty'],'availabe_quantity'=>0,'notavailable_quantity'=>$late_deliver));
            }
        }

        $all_available=array();
        $notavailable=array();
        $all_are_after_hour=array();
        $cart_data=$cartdatas;
        foreach($cartdatas as $key => $data)
        {
            if($data['availabe_quantity']==0)
            {
                $quality=$data['cart_order_qty'];
                array_push($notavailable,$data);
                array_push($all_are_after_hour,$data);
            }
            else{
                if($data['notavailable_quantity']==0)
                {
                    $quality=$data['cart_order_qty'];
                    array_push($all_available,$data);
                    array_push($all_are_after_hour,$data);
                }
                else{
                    $quality=$data['cart_order_qty'];
                    array_push($all_are_after_hour,$data);
                    $cartdatas[$key]['cart_order_qty']=$quality-$data['notavailable_quantity'];
                    array_push($all_available,$cartdatas[$key]);
                    $cartdatas[$key]['cart_order_qty']=$data['notavailable_quantity'];
                    array_push($notavailable,$cartdatas[$key]);
                }


            }
        }

        $final_data=array('all_available_product_quality'=>$all_available,'not_available_product_quality_in_48_hours'=>$notavailable,'all_product_in_48_hours'=>$all_are_after_hour);
        return array($final_data,$cart_data);
    }

    /**
     * Return stock unique items fields
     *
     * @return Array
     */
    private function getItemFields()
    {
        return [
            "fsi_uid",
            "fsi_uid as item_group_id",
            "fsi_item_name as item_name",
            "fsi_brand_name as brand_name",
            "fsi_category_id as category_id",
            "fsi_categry_name as category_name",
            "fsi_variant as variant",
            "isMedicine"
        ];
    }

    private function getItemFieldsRaw()
    {

        return
            "fsi_item_id,fsi_uid,fsi_uid as item_group_id,trim(CONCAT(if(isMedicine=1,'',fsi_brand_name), ' ',fsi_item_name,' ',fsi_variant)) as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,finascop_stock_uniqueitem.isMedicine,fsi_displaylabel,fsi_def_itemmaster_id";
        

    }

    /**
     *Item master table fields.
     *
     * @return Array
     */
    private function itemMasterFields()
    {
        return [
            'stit_ID',
            'stit_fsiuid',
            'stit_quantity as quantity',
            'stit_ID as itemId',
            'stit_SKU as stit_SKU',
            'stit_ID as short_description',
            'stit_ID as long_description',
            'stit_displaylabel',
            'prescription',
            'cos_nos',
            'directDelivery',
            'courierDelivery',
            'taxValueId',
            'product_category',
            'stit_courierWt'
        ];
     }


    /**
     * Add stock value
     *
     * @param array $cart
     * @return Array
     */
    private function addStock(array $item,$order_method=1,$branch_id=0,$shipping_method=2)
    {
        $storegroupid = getHeaderStoreGroup();
        $products = $this->findProducts($item);
        $branch_id=($branch_id==0)?$products['branch']:$branch_id;

                        
        $stock = Stock::getStock($products['product'], $branch_id);

        $price = Price::findPrice($products['product'], $branch_id);
        $cheap = CheapPrice::getDefault($products['group'], $stock, $price);

        foreach ($item as $key => $itm) {
            $count = $item[$key]['item']['item_master'] ?
                count($item[$key]['item']['item_master']) :
                0;
            for ($i = 0; $i < $count; $i++) {
               /* $stitId = $item[$key]['item']['item_master'][$i]['stit_ID'];
                $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
                $mrp = array_key_exists($stitId, $price['mrp']) ? $price['mrp'][$stitId] : 0;
                $selling_price = array_key_exists($stitId, $price['selling_price']) ? $price['selling_price'][$stitId] : 0;*/

                $stitId = $item[$key]['item']['item_master'][$i]['stit_ID'];
                $cos_nos= $item[$key]['item']['item_master'][$i]['cos_nos'];
                $cos_nos = ($cos_nos > 0)?$cos_nos:1;

                $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
                $mrp = array_key_exists($stitId, $price['fpod_leastSKUmrp']) ? $price['fpod_leastSKUmrp'][$stitId] *$cos_nos : 0;
                //$order_method= \Session::get('order_method');
              
              //  $order_method= \Session::get('order_method');
            //    $order_method= 1;
/*
                if($order_method==1){ 
                    if($shipping_method==1)  {
                        $selling_price = array_key_exists($stitId, $price['fpod_customerRateHmDel']) ? $price['fpod_customerRateHmDel'][$stitId] *$cos_nos : 0;  
            
                    }else{
                        $selling_price = array_key_exists($stitId, $price['fpod_customerRateCouDel']) ? $price['fpod_customerRateCouDel'][$stitId] *$cos_nos : 0;  
                
                    }

     // $selling_price = array_key_exists($stitId, $price['fpod_customerRateHmDel']) ? $price['fpod_customerRateHmDel'][$stitId] *$cos_nos: 0;
                }else{
*/
                $branch_type_id = array_key_exists($stitId, $price['branch_type_id']) ? $price['branch_type_id'][$stitId] : 1;
            $itemstoregroup_id = array_key_exists($stitId, $price['storegroup_id']) ? $price['storegroup_id'][$stitId] : -1;
            
            $sellingpriceField = ($storegroupid > 0 && $itemstoregroup_id == $storegroupid ? 'selling_price' : ($branch_type_id == 3 ? 'fpod_customerRateHmDel' : 'fpod_customerRateCouDel'));
                   $selling_price = array_key_exists($stitId, $price[$sellingpriceField]) ? $price[$sellingpriceField][$stitId] *$cos_nos : 0;
/*
                }
*/

                 $percentage=($mrp>0)?((($mrp - $selling_price)*100) /$mrp):0 ;
               $percentage=round($percentage,2);
                $default_val = in_array($stitId, $cheap) ? 1 : 0;

                 $mrp=round($mrp,2);
                  $selling_price=round($selling_price,2);

                $item[$key]['item']['item_master'][$i]['stock_available'] = $stock_count;
                $item[$key]['item']['item_master'][$i]['selling_prize'] = $selling_price;
                $item[$key]['item']['item_master'][$i]['selling_price'] = $selling_price;
                
                $item[$key]['item']['item_master'][$i]['godown_itemId'] = $this->getStock();
                $item[$key]['item']['item_master'][$i]['mrp'] = $mrp;
                $item[$key]['item']['item_master'][$i]['percentage'] = $percentage;
                $item[$key]['item']['item_master'][$i]['default_value'] = $default_val;
            }
        }


        return $item;
    }

    private function  findProducts(array $items)
    {

        foreach ($items as $Itm) {
            $branch = $Itm['cart_branch_id'];
            $products = $Itm['item']['item_master'];
            $group_id = $Itm['item']['fsi_uid'];
            $group_product = [];
            if(is_array($products))
            {
                foreach ($products as $product) {
                    $product_id[] = $product['stit_ID'];
                    $group_product[] = $product['stit_ID'];
                }
            }
            $group[] = [
                "group" => $group_id,
                "products" => $group_product
            ];
            unset($group_product);
        }

        return [
            'product' => @$product_id,
            'branch' => @$branch,
            'group' => @$group,
        ];
    }

    public function getStock()
    {
        return rand(10, 100);
    }

    

    /**
     * Clear cart.
     *
     * @param string $id
     * @return int
     */
    public function clearItems($order_method, $guestToken = "")
    {
        $where = [];
        if(auth()->check())
        {
            $where[] = ["cart_customer_id", auth()->user()->cust_id];
        }
        else
        {
            $where[] = ["guest_token", $guestToken];
        }
        $this->cart->where($where)->delete();
        return "Clear cart Items successfully.";
    }


    /**
     * Delete a product from the cart.
     *
     * @param string $id
     * @return int
     */
    public function delete($id, $guestToken = "")
    {
        DB::transaction(function () use ($id, $guestToken) {
            $where = [
                ['id', $id]
            ];
            if(auth()->check())
            {
                $where[] = ["cart_customer_id", auth()->user()->cust_id];
            }
            else
            {
                $where[] = ["guest_token", $guestToken];
            }
            $this->cart->where($where)->delete();
            if($guestToken == "")
            {
                $this->removeBlockedItems();
            }
        });

        // $this->cart
        //     ->where('cart_customer_id', $this->customer->cust_id)
        //     ->where('cart_product_id', $id)
        //     ->delete();
        // $this->removeBlockedItems();
        // });
        return $id;
    }

    private function removeBlockedItems()
    {
        $customer_id = auth()->user()->cust_id ?? 0;
        // $customer_id = $this->customer->cust_id ?? 0;

        return BlockedItems::where('customer_id', $customer_id)
            ->delete();
    }


    public function edit($request)
    {
        $update = null;
        $cart = $request->all();
        DB::transaction(function () use ($cart, $update) {
            $where = [
                ['cart_product_id', $cart['cart_product_id']],
                ['order_method', $cart['order_method']],
            ];
            if(auth()->check())
            {
                $where[] = ["cart_customer_id", auth()->user()->cust_id];
            }
            else
            {
                $where[] = ["guest_token", @$cart['guest_token']];
            }
            if($cart['cart_order_qty'] == '0' || $cart['cart_order_qty'] == 0)
            {
                $update = $this->cart->where($where)->delete();
            }
            else
            {
                $update = $this->cart->where($where)->update([
                    'cart_order_qty' => $cart['cart_order_qty']
                ]);
            }
            if(auth()->check())
            {
                $this->removeBlockedItems();
            }
        });
        return $update;
    }

    /**
     * Add product to the cart
     *
     * @param array $data
     * @return void
     */
public function store($data)
{
    $data = Arr::except($data, ['type']);
    $storegroupid = getHeaderStoreGroup();
    $data['storegroup_id'] = $storegroupid;
    $preparedData = PrepareCartItem::prepare($data);
    if(!empty($preparedData))
    {
        unset($preparedData['cart_id'], $preparedData['unikey']);
        $this->cart->create($preparedData);
        return $this->getIds(@$data['guest_token']);
    }
    return [];
}

    /**
     * Retrieve the ids of products added to the cart
     *
     * @return \Illuminate\Support\Collection
     */
    public function getIds($guestToken = "")
    {
        if($guestToken == "")
        {
            $where = [["cart_customer_id", auth()->user()->cust_id]];
        }
        else
        {
            $where = [["guest_token", $guestToken]];
        }
        return $this->cart
            ->select('cart_product_id', 'cart_order_qty')
            ->where($where)
            ->get();
    }




    public function checkOut()
    {
        $cart = $this->get();

        $stock = $cart ? $this->checkStockAvailable(Arr::wrap($cart)) : false;

        $order = $cart ? $this->checkOrderAvailable(Arr::wrap($cart)) : false;

        $msg =  [
            "stock_available" => false,
            "message" => "Stock is not Available in " . implode(',', $stock),
            "payment_details" => new \stdClass
        ];

        $msgOrder =  [
            "stock_available" => false,
            "message" => "Stock is not Available Based on your Order Qty " . implode(',', $order),
            "payment_details" => new \stdClass
        ];


        // return $this->orderInitiate($cart);
        return $stock ? $msg : ($order ? $msgOrder : $this->orderInitiate($cart));
    }

    private function orderInitiate($cart)
    {

        $customer = Checkout::customerCheckout($cart);
        return [
            "stock_available" => True,
            "message" => "Stock is Available",
            "payment_details" => $customer
        ];
    }
    private function checkStockAvailable(array $item)
    {
        $itemNotAvailable = [];
        foreach ($item as $key => $itm) {
            $count = $item[$key]['item']['item_master'] ?
                count($item[$key]['item']['item_master']) : 0;
            for ($i = 0; $i < $count; $i++) {
                $flag = empty($item[$key]['item']['item_master'][$i]['stock_available']) ?
                    1 : 0;
                if ($flag) {
                    $itemNotAvailable[$i] = $item[$key]['item']['item_name'];
                    $flag = 0;
                }
            }
        }


        return $itemNotAvailable;
    }
    /**
     * Check user required Order qty with stock available...
     *
     * @param array $item
     * @return array
     */
    private function checkOrderAvailable(array $item)
    {

        $orderNotAvailable = [];
        foreach ($item as $key => $itm) {
            $count = $item[$key]['item']['item_master'] ?
                count($item[$key]['item']['item_master']) : 0;

            for ($i = 0; $i < $count; $i++) {
                $flag = ($item[$key]['cart_order_qty'] > $item[$key]['item']['item_master'][$i]['stock_available']) ?
                    1 : 0;
                //dd($item);
                if ($flag) {
                    $orderNotAvailable[$i] = $item[$key]['item']['item_name'];
                    $flag = 0;
                }
            }
        }
        return $orderNotAvailable;
    }
    public function prescriptionMapped($cartitems){
        $medicinedata=array();
        $i=0;
        foreach($cartitems as $cartitem)
         {
            $checkforMedicine=$this->itemMaster->where('stit_ID',$cartitem['cart_product_id'])->where('prescription',1)->where('isMedicine',1)->get();
            $checkforMedicinenotprepention=$this->itemMaster->where('stit_ID',$cartitem['cart_product_id'])->where('prescription',0)->where('isMedicine',1)->get();
            if(count($checkforMedicinenotprepention)>0)
            {


                $medicinedata[$i][$cartitem['id']]=array("ispreciption"=>0,"mapped"=>0);

            }
            if(count($checkforMedicine)>0)
            {
                $map=$this->medinicemap->where('stit_Id',$cartitem['cart_product_id'])->where('cust_id', auth()->user()->cust_id)->where('pmm_expirydate', '>=', date('y-m-d'))->get();

                if(count($map)>0)
                {
                    $medicinedata[$i][$cartitem['id']]=array("ispreciption"=>1,"mapped"=>1);
                }
                else{
                    $medicinedata[$i][$cartitem['id']]=array("ispreciption"=>1,"mapped"=>0);
                }
            }
            if(count($checkforMedicine)==0 && count($checkforMedicinenotprepention)==0)
            {
                $medicinedata[$i][$cartitem['id']]=array("ispreciption"=>0,"mapped"=>0);
            }

        }
        foreach($cartitems as $key =>$value)
            {
              //  $cart[$key]['isprescription']=(in_array($value['cart_product_id'],$checkforMedicines))?1:0;
                    
                foreach($medicinedata as $element)
                {
                    $keys=array_keys($element);
                   if (in_array($value['id'],$keys))
                   {
                        $item_element=$element[$value['id']];

                        $cartitems[$key]['isprescription']=$item_element['ispreciption'];
                        $cartitems[$key]['prescription_mapped']=$item_element['mapped'];
                        $cartitems[$key]['need_prescription_upload']=($item_element['ispreciption']==1 && $item_element['mapped']==0 )?1:0;
                        if($cartitems[$key]['need_prescription_upload']==0){
                             unset($cartitems[$key]);
                        }

                    }

                }
                /*comment by Mitali */
        

            }
        return $cartitems;

    }
    public function prescriptionCheck(){
        $uploadPrescription=false;
        $storegroupid = getHeaderStoreGroup();

        $cartdatas =$cartitems=$this->cart->where('order_method', $order_method)->where('cart_customer_id', auth()->user()->cust_id)->where('storegroup_id', $storegroupid)->get();
        $i=0;$cartitemProductIds=[];
         foreach($cartitems as $cartitem)
         {
            $cartitemProductIds[]=$cartitem['cart_product_id'];
         }
         $checkforMedicines=$this->itemMaster->whereIn('stit_ID',$cartitemProductIds)->where('prescription',1)->where('isMedicine',1)->pluck('stit_Id')->toArray();
        if(count($checkforMedicines)>0){
            $prescribedMedicineList=$this->getPriscribedMedicines();
            if(count($prescribedMedicineList)>0){
                foreach ($checkforMedicines as $checkforMedicine) {
                    if(!in_array($checkforMedicine, $prescribedMedicineList)){
                        $uploadPrescription=true;
                        break;
                    }
               
                }    
            }
            
        }


            
        //get cart items & check prescription =1 is there, if not return false
        //if prescription ==1, check user's prescription, if not return true
        //if prescriptin ==1, there is prescription , get approve prescription's medicine list
        // get all its alternet brands medicine IDDS
        // if required prescription medicine id is in that list then return true otherwise false

        return false;
    }
    private function getPriscribedMedicines(){
        $prescribedmedicines=array();
        $stit_Ids=$this->medinicemap->where('cust_id', auth()->user()->cust_id)->where('pmm_expirydate', '>=', date('y-m-d'))->pluck('stit_Id')->toArray();
        if($stit_Ids){
            $medcompos_id=     $this->itemMaster->whereIn('stit_ID',$stit_Ids)->where('prescription',1)->where('isMedicine',1)->pluck('medcompos_id')->toArray();

            $prescribedmedicines= $this->itemMaster->whereIn('medcompos_id',$medcompos_id)->where('prescription',1)->where('isMedicine',1)->pluck('stit_Id')->toArray();
        }
        return  $prescribedmedicines;
         

    }

    /// Move items from cart to wishlist.
    public function moveToWishList()
    {
        $storegroupid = getHeaderStoreGroup();
        // Get items from cart associated to the customer and store group.
        $cartitems=$this->cart->where('cart_customer_id', auth()->user()->cust_id)->where('storegroup_id', $storegroupid)->get();
        if(isset($cartitems)){
            foreach($cartitems as $cartitem)
            {
                $item = SavedItem::where('product_id', $cartitem['cart_product_id'])->where('customer_id', auth()->user()->cust_id)->first();
                if(!$item){
                    $data = [
                        'product_id'        => $cartitem['cart_product_id'],
                        'group_id'          => $cartitem['cart_group_id'],
                        'branch_id'         => $cartitem['cart_branch_id'],
                        'order_method'      => $cartitem['order_method'],
                        'branch_type_id'    => $cartitem['branch_type_id'],
                        'customer_id'       => auth()->user()->cust_id,
                        'storegroupid'      =>  $storegroupid
                    ];
                    //$this->savedItem->updateOrCreate($data);
                    $this->savedItem->create($data);
                }
                $this->cart
                    ->where('cart_customer_id', auth()->user()->cust_id)->where('id', $cartitem['id'])
                    ->delete();

            }
        }

    }

    public function migrateGuestCart($authUser = 0)
    {
        $guestToken = getGuestTokenFromHeader();
        if(($authUser > 0) && ($guestToken != ""))
        {

            $details = DB::table('retaline_cart as ci')
            ->leftJoin('retaline_cart as ci_new', function ($join) use ($authUser) {
                $join->on('ci.cart_product_id', '=', 'ci_new.cart_product_id')
                ->where('ci_new.cart_customer_id', '=', $authUser);
            })
            ->where('ci.guest_token', $guestToken)
            ->whereNull('ci_new.id') // Ensure the product_id does not exist for the new cust_id
            ->update([
                'ci.guest_token'        => "",
                'ci.cart_customer_id'   => $authUser
            ]);
            $this->cart->where('guest_token', $guestToken)->update([
                'guest_token'   => ""
            ]);
            return $details;
        }
    }
}