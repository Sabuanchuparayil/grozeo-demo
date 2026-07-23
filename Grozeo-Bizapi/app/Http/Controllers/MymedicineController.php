<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\Product\ProductRepository;
use App\Models\UploadPrescription;
use App\Models\prescriptiomMedicineMap;


class MymedicineController extends Controller
{
     protected $order;
     protected $orderItem;
     protected $products;
     protected $prescriptiomMedicine;


    public function __construct(Order $order, OrderItem $orderItem,ProductRepository $products,prescriptiomMedicineMap $prescriptiomMedicine)
    {
         $this->order = $order;
         $this->orderItem= $orderItem;
         $this->products=$products;
         $this->prescriptiomMedicine=$prescriptiomMedicine;
    }

    public function get()
    {
        $customerId = auth()->user()->cust_id;
        $expired_medicines_ids= $productIds=$item_product_ids = $item_prescription_ids=[];
       
        //expired medicines Or not prescribed medicines

        $expired_medicines_ids = $this->prescriptiomMedicine
           ->whereHas('prescription', function ($query) use($customerId) {
                $query->where('status', '3');
                $query->where('cust_id', $customerId);
                $query->where('expiry_date', '<', DB::raw('"'.date('Y-m-d').'"'));
            })
            ->select("stit_Id","pmm_id")
            ->groupBy('stit_Id')
            ->pluck('stit_Id','pmm_id');

        // Order Medicine   

        $item_product_ids = $this->orderItem->whereHas('order', function ($query) use($customerId) {
                $query->where('order_customer_id', $customerId);
            })
            ->select("item_product_id","item_id")
            ->groupBy('item_product_id');
            if(!empty($expired_medicines_ids)){
               $item_product_ids->whereNotIn('item_product_id', $expired_medicines_ids->toArray());
            }
         $item_product_ids = $item_product_ids->pluck('item_product_id','item_id');
         $item_product_ids   = $item_product_ids->toArray();

        // prescribed medicine but not expired
        $item_prescription_ids = $this->prescriptiomMedicine
           ->whereHas('prescription', function ($query) use($customerId) {
                $query->where('status', '3');
                $query->where('cust_id', $customerId);
            })
           // ->where('cust_id', $customerId)
           ->whereDate('pmm_expirydate', '>', date('Y-m-d H:i:s'))
            ->select("stit_Id","pmm_id")
            ->groupBy('stit_Id')
            ->pluck('stit_Id','pmm_id');
        $item_prescription_ids   = $item_prescription_ids->toArray();


        if(!empty($item_product_ids) && !empty($item_prescription_ids)){
            $productIds=array_merge($item_product_ids,$item_prescription_ids);    
        }elseif(!empty($item_product_ids)){
            $productIds=$item_product_ids;
        }else{
            $productIds=$item_prescription_ids;
        }
        
        $result=$this->products->getProductsIds($productIds);
        return new SuccessWithData($result);


        
    }
    

    


}
