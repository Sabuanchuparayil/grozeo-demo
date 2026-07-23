<?php

namespace App\Http\Controllers;

use stdClass;
use App\Models\ManuFacture;
use App\Models\MedicineAdvice;
use App\Models\MedicineMaster;
use App\Models\StockItemMaster;

use App\Models\StockUniqueItem;
use App\Models\UploadPrescription;
use App\Models\MedicineComposition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\Item\Price;
use App\Http\Repositories\Item\Stock;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\Item\CheapPrice;
use App\Http\Requests\Product\ProductCategory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\HomeScreenController;

use Illuminate\Support\Collection;
use App\Http\Requests\Product\ProductSearchRequest;
use App\Http\Repositories\Item\ItemMasterCollection;

class MedicinesDetailsContriller extends Controller
{
    protected $medicine;
    private $stockItem;
    private $itemMaster;
    private $medinicemaster;
    private $manufacture;
    private $compostion;
    private $medicineadvice;
    private $upload;
    protected $_itemMasterCollection;

    public function __construct(MedicineAdvice $medicineadvice, StockUniqueItem $stockItem, StockItemMaster $itemMaster, MedicineMaster $medinicemaster, ManuFacture $manufacture, MedicineComposition $compostion, UploadPrescription $upload, ItemMasterCollection $itemMasterCollection)
    {
        $this->stockItem = $stockItem;
        $this->itemMaster = $itemMaster;
        $this->medinicemaster = $medinicemaster;
        $this->manufacture = $manufacture;
        $this->compostion = $compostion;
        $this->medicineadvice = $medicineadvice;
        $this->upload = $upload;
        $this->_itemMasterCollection = $itemMasterCollection;
    }

    public function medicineSearch(Request $request)
    {

        $validatedData = $request->validate([
            'param' => 'required',
           
        ]);
        $param = $request->input('param');

        

        $medicine = StockItemMaster::select('stit_ID', DB::raw('CONCAT('.'stit_itemName," ",stit_quantity) as stit_SKU'), 'isMedicine')
                    ->where('stit_brand_name', 'LIKE', '%' . $param . '%') 
                    ->orWhere('stit_itemName', 'LIKE', '%' . $param . '%')
                    ->limit(10)
                    ->get();
        return new SuccessWithData($medicine);
    }
 
    public function medicineDetails(Request $request)

    {
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $validatedData = $request->validate([
            'stit_ID' => 'required         ',
            'isMedicine' => 'required',
            'branch_id' => 'required',

        ]);

        $branch_id = $request->get('branch_id');
        $std_id = $request->get('stit_ID');
        $isMedince = $request->get('isMedicine');
        $stit_id=0;
        if(isset($request->stit_ID)){
            $stit_id=$request->stit_ID;
        }
       
        if($isMedince==1)

        {

            $stit_itemId=$this->itemMaster->select('stit_fsiuid')->where('stit_ID',$std_id)->where('stit_status',1)->first();

            
            $MedinceMasterId = $this->stockItem
            //->join('finascop_stock_itemmaster as fs', 'fs.stit_fsiuid', 'finascop_stock_uniqueitem.fsi_uid')
            ->with(['itemMaster' => function ($query)use($domain) {
                $query->with(['mainImage' => function ($qry)use($domain) {
                    $qry->where('image_type', 1)
                    ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));
                }])
                    ->with(['additionalImage' => function ($qry)use($domain) {
                        $qry->where('image_type', 0)
                        ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));
                    }])

                    ->selectRaw($this->getProductFields());
              }])
                //->groupBy('fsi_uid')

               // ->where('stit_ID', $std_id)
                ->where('fsi_uid', $stit_itemId->stit_fsiuid)
                ->selectRaw($this->getItemFields(1))
                ->where('finascop_stock_uniqueitem.isMedicine', 1)->first();

              $item_master=$MedinceMasterId->toArray()['item_master'];
              if(isset($item_master[0])){
                $MedinceMasterId["item_name"] = $MedinceMasterId["item_name"] . " " . $item_master[0]["quantity"];
                $MedinceMasterId["brand_name"] =($item_master[0]["displaylabel"]!="")?$item_master[0]["displaylabel"]:$MedinceMasterId["brand_name"];
              }

            
            $MedinceCompostion = $this->medinicemaster->where('medicineMaster_id', $MedinceMasterId['fsi_item_id'])->first()->toArray();


            $compostion = $this->compostion->select('composition_name')->where('composition_id', $MedinceCompostion['medicine_composition'])->first();
            
            $manufacture = $this->manufacture->select('manufacture_name')->where('manufacture_id', $MedinceCompostion['medicine_manufacture'])->first();

            $MedinceMasterIDS = $this->medinicemaster->select('medicineMaster_id')->where('medicine_composition', $MedinceCompostion['medicine_composition'])->where('medicine_type',$MedinceCompostion['medicine_type'])->limit(5)
            ->get()->toArray();

            $MedicineMasterID = array_column($MedinceMasterIDS, 'medicineMaster_id');

            if (($key = array_search($MedinceMasterId['fsi_item_id'], $MedicineMasterID)) !== false) {
                unset($MedicineMasterID[$key]);
            }

          /*  $stockUniqueitem = $this->stockItem->whereIn('fsi_item_id',$MedicineMasterID)->where('isMedicine', $isMedince)->get()->toArray();

            $stock_item_master = $this->itemMaster->whereIn('stit_itemId', array_column($stockUniqueitem, 'fsi_item_id'))->get()->toArray();*/

            $alternatebrands = $item = $this->stockItem
            //->join('finascop_stock_itemmaster as fs', 'fs.stit_fsiuid', 'finascop_stock_uniqueitem.fsi_uid')
            ->with(['itemMaster' => function ($query)use($domain) {
                    $query->with(['mainImage' => function ($qry)use($domain) {
                        $qry->where('image_type', 1)
                        ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));
                    }])
                        ->with(['additionalImage' => function ($qry)use($domain) {
                            $qry->where('image_type', 0)
                            ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));
                        }])

                        ->selectRaw($this->getProductFields());
                }])->selectRaw($this->getItemFields(1))
                ->where('finascop_stock_uniqueitem.isMedicine', 1)
                ->whereIn('fsi_item_id',$MedicineMasterID)
                ->where('fsi_count','>',0)
              //  ->whereIn('stit_ID', array_unique(array_column($stock_item_master, 'stit_ID')))
                ->get();


            $total_count = count($alternatebrands);
           // $total_count = 0;
            $limit = 5;
            $altermedicine = array();

            $alternatebrands = $alternatebrands->take(5);
            foreach ($alternatebrands as $alternatebrand) {
                $datas = $this->checkField($alternatebrand, $branch_id);

                array_push($altermedicine, array(
                    'stit_ID' => $datas['item_master'][0]['stit_ID'],
                    'fsi_uid' => $datas['fsi_uid'],
                    'item_group_id' => $datas['item_group_id'],
                    'item_name' => $datas['item_name'],
                    'brand_name' => $datas['brand_name'],
                    'stock_available' => $datas['item_master'][0]['stock_available'],
                    'main_image' => $datas['item_master'][0]['main_image'],
                    'mrp' => $datas['item_master'][0]['mrp'],
                    'isMedicine' => "1"
                ));
            }

            $viewaltermedicine = array('total_data' => $total_count, 'limit_data' => $limit);
            //$approve_status = 0;


            $Depthinfo = array(
                'about' => $MedinceCompostion['medicine_about'],
                'use' => $MedinceCompostion['medicine_use'],
                'sideEffect' => $MedinceCompostion['medicine_sideeffects'],
                'medicine work' => $MedinceCompostion['medicine_works'],
                'MoreInfo' => $MedinceCompostion['medicine_morInfo'],
                'medicineDiseases' => $MedinceCompostion['medicine_diseases'],

            );


            $details = $MedinceMasterId->toArray();
            $genernalInfo = array('overview' => $details['item_master'][0]['short_description'], 'medicine content' => '');
            $patientConcerns = array();



            $patientConcerns = $this->medicineadvice->with('safety_advice')->with('safety_precaution')->where('medicineMaster_id', $MedinceCompostion['medicineMaster_id'])->first();




            $tab = array('depthinfo' => $Depthinfo, 'generalInfo' => $genernalInfo, 'patientConcers' => $patientConcerns);



            $data = $this->checkField($MedinceMasterId, $branch_id,$stit_id);

            // if (isset(auth()->user()->cust_id)) {

            //     $check = $this->upload->where('cust_id',auth()->user()->cust_id)->where('medinicemasterid', $data['fsi_def_itemmaster_id'])
            //         ->where('item_master_id', $data['item_master'][0]['stit_ID'])->where('status', 1)
            //         ->where('expiry_date', '>=', now()->format('Y-m-d'))->get();
            //     if (count($check)) {
            //         $approve_status = 1;
            //     }
            //     //check medicine vaild and expire date

            // }
            $prescription=$this->itemMaster->select('prescription')->where('stit_ID',$std_id)->first();

            if ($data) {
                $data['composition_name'] = $compostion->composition_name;
                $data['isPrescription'] = $prescription['prescription'];
                $data['manufacture'] = $manufacture->manufacture_name;
                $data['tabmenu'] = $tab;
                $data['alternateMedicine'] = array('MedicineList' => $altermedicine, 'viewDetails' => $viewaltermedicine);
                // $data['approvel'] = $approve_status;
            }
        }
        else{


            $item = $this->stockItem
            ->join('finascop_stock_itemmaster as fs', 'fs.stit_fsiuid', 'finascop_stock_uniqueitem.fsi_uid')
            ->with(['itemMaster' => function ($query)use($domain)  {
                $query->with(['mainImage' => function ($qry)use($domain) {
                    $qry->where('image_type', 1)
                    ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));
                }])
                    ->with(['additionalImage' => function ($qry)use($domain) {
                        $qry->where('image_type', 0)
                        ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));
                    }])
                    ->selectRaw($this->getProductFields());
            }])
            ->where('stit_ID',$std_id)
            ->selectRaw($this->getItemFields(0))
             ->first(); 
                $item_master=$item->toArray()['item_master'];
                if(isset($item_master[0])){
                  $item["item_name"] = $item["item_name"] . " " . $item_master[0]["quantity"];
                  $item["brand_name"] =($item_master[0]["displaylabel"]!="")?$item_master[0]["displaylabel"]:$item["brand_name"];
               //   $item["brand_name"] = $item_master[0]["displaylabel"];
                }
                $data= $this->checkField($item, $branch_id);


        }




        return new SuccessWithData(
            $data //$this->stockItem->with('itemMaster')->get()
        );
    }



    public function viewallalernatemedicineList(Request $request)

    {

        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $validatedData = $request->validate([
            'stit_ID' => 'required',
            'isMedicine' => 'required',
            'branch_id' => 'required',

        ]);

        $branch_id = $request->get('branch_id');
        $std_id = $request->get('stit_ID');
        $isMedince = $request->get('isMedicine');

        $stit_itemId=$this->itemMaster->select('stit_fsiuid')->where('stit_ID',$std_id)->where('stit_status',1)->first();

            
        $MedinceMasterId = $this->stockItem
        //->join('finascop_stock_itemmaster as fs', 'fs.stit_fsiuid', 'finascop_stock_uniqueitem.fsi_uid')
        ->with(['itemMaster' => function ($query)use($domain) {
            $query->with(['mainImage' => function ($qry)use($domain) {
                $qry->where('image_type', 1)
                ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));
            }])
                ->with(['additionalImage' => function ($qry)use($domain) {
                    $qry->where('image_type', 0)
                    ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));
                }])

                ->selectRaw($this->getProductFields());
          }])
            //->groupBy('fsi_uid')

           // ->where('stit_ID', $std_id)
            ->where('fsi_uid', $stit_itemId->stit_fsiuid)
            ->selectRaw($this->getItemFields(1))
            ->where('finascop_stock_uniqueitem.isMedicine', 1)->first();

          $item_master=$MedinceMasterId->toArray()['item_master'];
          if(isset($item_master[0])){
            $MedinceMasterId["item_name"] = $MedinceMasterId["item_name"] . " " . $item_master[0]["quantity"];
            $MedinceMasterId["brand_name"] =($item_master[0]["displaylabel"]!="")?$item_master[0]["displaylabel"]:$MedinceMasterId["brand_name"];

          }

        
        $MedinceCompostion = $this->medinicemaster->where('medicineMaster_id', $MedinceMasterId['fsi_item_id'])->first()->toArray();


        $compostion = $this->compostion->select('composition_name')->where('composition_id', $MedinceCompostion['medicine_composition'])->first();
        
        $manufacture = $this->manufacture->select('manufacture_name')->where('manufacture_id', $MedinceCompostion['medicine_manufacture'])->first();

        $MedinceMasterIDS = $this->medinicemaster->select('medicineMaster_id')->where('medicine_composition', $MedinceCompostion['medicine_composition'])->where('medicine_type',$MedinceCompostion['medicine_type'])->limit(5)
        ->get()->toArray();

        $MedicineMasterID = array_column($MedinceMasterIDS, 'medicineMaster_id');

        if (($key = array_search($MedinceMasterId['fsi_item_id'], $MedicineMasterID)) !== false) {
            unset($MedicineMasterID[$key]);
        }

      /*  $stockUniqueitem = $this->stockItem->whereIn('fsi_item_id',$MedicineMasterID)->where('isMedicine', $isMedince)->get()->toArray();

        $stock_item_master = $this->itemMaster->whereIn('stit_itemId', array_column($stockUniqueitem, 'fsi_item_id'))->get()->toArray();*/

        $alternatebrands = $item = $this->stockItem
        //->join('finascop_stock_itemmaster as fs', 'fs.stit_fsiuid', 'finascop_stock_uniqueitem.fsi_uid')
        ->with(['itemMaster' => function ($query)use($domain) {
                $query->with(['mainImage' => function ($qry)use($domain) {
                    $qry->where('image_type', 1)
                    ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));
                }])
                    ->with(['additionalImage' => function ($qry)use($domain) {
                        $qry->where('image_type', 0)
                        ->select('id', 'product_id',DB::raw('CONCAT("'.$domain.'preview-",image_url) as image_url'),DB::raw('CONCAT("'.$domain.'thumbnail-",image_url) as image_thumb_url'));
                    }])
                    ->where('stit_status',1)
                    ->selectRaw($this->getProductFields());
            }])->selectRaw($this->getItemFields(1))
            ->where('finascop_stock_uniqueitem.isMedicine', 1)
            ->whereIn('fsi_item_id',$MedicineMasterID)
            ->where('fsi_count','>',0)
          //  ->whereIn('stit_ID', array_unique(array_column($stock_item_master, 'stit_ID')))
            ->paginate(10);

        return $alternatebrands;

       
        $item = $alternatebrands ->toArray(); 
        //   $item["item_name"] = $item["item_name"] . " " . $item_master[0]["quantity"];
        //   $item["brand_name"] =($item_master[0]["displaylabel"]!="")?$item_master[0]["displaylabel"]:$item["brand_name"];
        //   $item["brand_name"] = $item_master[0]["displaylabel"];
         
         
         $item["data"]= $this->checkFieldSearch($item['data'], $branch_id);
         return new SuccessWithData($item);

    }


    private function getProductFields()
    {
        return "stit_ID,
                stit_fsiuid,
                stit_quantity as quantity,
                stit_SKU as item_name,
                stit_ID as itemId,
                stit_Description as short_description,
                stit_long_description as long_description,
                stit_item_volume as stock_available,
                stit_GST as selling_prize,
                stit_MRP as mrp,
                stit_displaylabel as displaylabel,
                cos_nos, -1 as branch_id, 1 as branch_type_id";
    }

    private function getItemFields($dat)
    {

        if($dat==0)//product
        {
            return
            "fsi_item_id,fsi_uid,fsi_uid as item_group_id,CONCAT_WS(' ',fsi_brand_name,fsi_item_name,fsi_variant) as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,finascop_stock_uniqueitem.isMedicine,fsi_displaylabel,fsi_def_itemmaster_id";
        }
        else{ //medinice
            return
            "fsi_item_id,fsi_uid,fsi_uid as item_group_id,fsi_item_name as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,finascop_stock_uniqueitem.isMedicine,fsi_displaylabel,fsi_def_itemmaster_id";
        }

    }
    private function getItemFieldsSearch()
    {

        return
            "fsi_item_id,fsi_uid,fsi_uid as item_group_id,stit_SKU as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,finascop_stock_uniqueitem.isMedicine,stit_displaylabel AS fsi_displaylabel,fsi_def_itemmaster_id,fs.stit_ID, courierDelivery, directDelivery, branch_id, br_storeGroup, br_directDelivery, br_courierDelivery";
        

    }


    private function checkField($item, $branch_id,$stit_id=0)
    {
        $data = $item ? $item->toArray() : [];
        if ($data)
            $data['item_master'] = $this->productFields($data['item_master'], $branch_id, $data['fsi_uid'],$stit_id);
        return $data;
    }


    private function productFields(array $item, $branch_id, $group_id,$stit_id)
    {
        $product_id = array_column($item, 'stit_ID');
        $group_product[] = [
            "group" => $group_id,
            "products" => $product_id,
        ];
        $stock = Stock::getStock($product_id, $branch_id);
        $price = Price::findPrice($product_id, $branch_id);
        $cheap = CheapPrice::getDefault($group_product, $stock, $price);
        for ($i = 0; $i < count($item); $i++) {
           /* $stitId = $item[$i]['stit_ID'];
            $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
            $mrp = array_key_exists($stitId, $price['mrp']) ? $price['mrp'][$stitId] : 0;
            $selling_price = array_key_exists($stitId, $price['selling_price']) ? $price['selling_price'][$stitId] : 0;*/

            $stitId = $item[$i]['stit_ID'];
            $cos_nos= $item[$i]['cos_nos'];

            $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
            $mrp = array_key_exists($stitId, $price['fpod_leastSKUmrp']) ? $price['fpod_leastSKUmrp'][$stitId] *$cos_nos : 0;
           // $order_method= \Session::get('order_method');
             
              //  $order_method= \Session::get('order_method');
                $order_method= 1;
/*
                if($order_method==1){
                     $selling_price = array_key_exists($stitId, $price['fpod_customerRateCouDel']) ? $price['fpod_customerRateCouDel'][$stitId] *$cos_nos: 0;
                }else{
*/
                   $selling_price = array_key_exists($stitId, $price['fpod_customerRatePikup']) ? $price['fpod_customerRatePikup'][$stitId] *$cos_nos : 0;
/*
                }
*/             
             $percentage=($mrp>0)?((($mrp - $selling_price)*100) /$mrp):0 ;
             

            $default_val = in_array($stitId, $cheap) ? 1 : 0;

            $mrp=round($mrp,2);
            $selling_price=round($selling_price,2);
            $percentage=round($percentage,2);
            
            if($stit_id>0){
                $default_val = ($stitId==$stit_id)?1:0;
            }else{
                $default_val = in_array($stitId, $cheap) ? 1 : 0;
            }

            $item[$i]['selling_prize'] = $selling_price;
            $item[$i]['selling_price'] = $selling_price;
            $item[$i]['stock_available'] = $stock_count;
            $item[$i]['mrp'] = $mrp;
            $item[$i]['selling_price'] = $selling_price;
            $item[$i]['percentage'] = $percentage;
            $item[$i]['godown_itemId'] = $this->getRand();
            $item[$i]['default_value'] = $default_val;
        }
        return $item;
    }


    private function getRand()
    {
        return rand(10, 1000);
    }
    public function newSearch(ProductSearchRequest $filter)
    {
        $items = $this->_itemMasterCollection->getProducts();
        $items = $items->where(function($q) use ($filter) {
            $q->where('stit_brand_name',  'like', "%{$filter->product_name}%")
            ->orWhere('stit_itemName', 'like', "%{$filter->product_name}%")
            ->orWhere('stit_SKU', 'like', "%{$filter->product_name}%");
        })
        ->paginate(10);
        $item = $items->toArray();

        return new SuccessWithData($item);        
    }

    private function checkFieldSearch($item, $branch_id)
    {

        return $item ? $this->addFieldsSearch($item, $branch_id) : [];
    }
    private function addFieldsSearch(array $item, $branch_id)
    {

        $products = $this->getHomeProducts($item);



        $stock = Stock::getStock($products['product'], $branch_id);
        $price = Price::findPrice($products['product'], $branch_id);

        $cheap = CheapPrice::getDefault($products['group'], $stock, $price);
/*
        $cpoproducts = DB::select('select * from vw_cpo_products');
        $cpparray = [
               "branch_id" => array_column($cpoproducts, "branch_id", "fcpod_itemid"),
               "fcpod_price" => array_column($cpoproducts, "fcpod_price", "fcpod_itemid"),
               "mrp" => array_column($cpoproducts, "mrp", "fcpod_itemid"),
          ];
*/
        $outOfStock_ids = array();
        foreach ($item as $key => $itm) {
            $count = count($item[$key]['item_master']);

            $brid = $itm['branch_id'];                
            $brDirectDelivery = $itm['br_directDelivery'];
            $itemDirectDelivery = $itm['directDelivery'];
		    $brTypeId= ($branch_id != $brid ? 1 : ($brDirectDelivery == 1 && $itemDirectDelivery == 1? 3 : 1));

            for ($i = 0; $i < $count; $i++) {
                $stitId = $item[$key]['item_master'][$i]['stit_ID'];
                $cos_nos= $item[$key]['item_master'][$i]['cos_nos'];
                
                $cos_nos = ($cos_nos>0)?$cos_nos:1;
                
                //$brid = $branch_id;
		        //$brTypeId= 1;
                //$default_br_id = getBranchIdForll();
                //if($brid != $default_br_id)
		        //    $brTypeId= 3;

                $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
                $mrp = array_key_exists($stitId, $price['fpod_leastSKUmrp']) ? $price['fpod_leastSKUmrp'][$stitId] *$cos_nos : 0;

              //  $order_method= \Session::get('order_method');
                $order_method= 1;
/*
                if($order_method==1){
                     $selling_price = array_key_exists($stitId, $price['fpod_customerRateCouDel']) ? $price['fpod_customerRateCouDel'][$stitId] *$cos_nos: 0;
                }else{
*/
                   $selling_price = array_key_exists($stitId, $price['fpod_customerRatePikup']) ? $price['fpod_customerRatePikup'][$stitId] *$cos_nos : 0;
/*
                }
*/

                if($stock_count<=0){
                    $mrp = 0; 
                    $selling_price =0;
                }
/*
                if($order_method==1 && ($stock_count <=0 || $selling_price <= 0 ) && array_key_exists($stitId, $cpparray['fcpod_price']) ){
                    $brid = $cpparray['branch_id'][$stitId];
                    $selling_price = $cpparray['fcpod_price'][$stitId];
                    $mrp = $cpparray['mrp'][$stitId];
                    $stock_count = 1000;
                    $brTypeId = 2;
                }
*/
                $percentage=($mrp>0)?((($mrp - $selling_price)*100) /$mrp):0 ;
                
                
                $default_val = ($stitId== $item[$key]["stit_ID"]) ? 1 : 0;
                $mrp=round($mrp,2);
                $selling_price=round($selling_price,2);
                $percentage=round($percentage,2);

                if($i==0){
                    if(isset($item[$key]['item_master'][0] )){
                       // $item[$key]["item_name"] =($item[$key]["isMedicine"]==0)?$item[$key]["brand_name"]." ".$item[$key]["item_name"] :$item[$key]["item_name"];
                       // $item[$key]["item_name"] = $item[$key]["item_name"] . " " . $item[$key]['item_master'][0]["quantity"];
                        $item[$key]["brand_name"] = ($item[$key]['item_master'][0]["displaylabel"]!="")?$item[$key]['item_master'][0]["displaylabel"]:$item[$key]["brand_name"];
                      //  $item[$key]["item_name"] =$item[$key]['item_master'][0]["item_name"];
                    }
                }
                $item[$key]['item_master'][$i]['default_value'] = $default_val;
                $item[$key]['item_master'][$i]['stock_available'] = $stock_count;
                $item[$key]['item_master'][$i]['mrp'] = $mrp;
                $item[$key]['item_master'][$i]['selling_prize'] = $selling_price;
                $item[$key]['item_master'][$i]['selling_price'] = $selling_price;
                $item[$key]['item_master'][$i]['godown_itemId'] = $this->getRand();
                $item[$key]['item_master'][$i]['percentage'] = $percentage;
                $item[$key]['item_master'][$i]['branch_id'] = $brid;
                $item[$key]['item_master'][$i]['branch_type_id'] = $brTypeId;
                if($order_method==1 && ($stock_count <=0 || $selling_price <= 0 ))
                    array_push($outOfStock_ids, $stitId);

            }
        }

        //$item=SetRetailerStock($outOfStock_ids, $item);

        return $item;
    }
    private function getHomeProducts(array $items)
    {
        $group_id = array();
        $group = array();
        $product_id = array();

        foreach ($items as $Itm) {          
            $products = $Itm['item_master'];
            $group_id = $Itm['fsi_uid'];
            foreach ($products as $product) {
                $product_id[] = $product['stit_ID'];
                $group_product[] = $product['stit_ID'];
            }
         
            $group[] = [
                "group" => $group_id,
                "products" => $group_product
            ];
            unset($group_product);
        }
        return [
            'product' => $product_id,
            'group' => $group,
        ];
    }

    // Search products by group id
    public function searchByGroupId(Request $filter)
    {
        $collection = null; //collect(['fsi_item_id' => $filter->group_id]);
        $items = $this->_itemMasterCollection->getProducts()
			->where('stit_itemId', $filter->group_id)
			->paginate(10);
        $item = $items ->toArray(); 	
	    foreach ($item['data'] as $key => $value){
            $item['data'][$key]['IsItemGroup'] = 0;
        }

        return new SuccessWithData($item);        
    }

    public function getAllGroupItems(){
        $group_products = DB::select('SELECT itemname_id AS groupid, item_name, itemDisplayName, iteamGroupImage FROM finascop_stock_itemmastername WHERE isItemGroup=1 AND `status`=1');
        return new SuccessWithData($group_products);
    }

}
