<?php

namespace App\Http\Controllers;

use App\Models\ManuFacture;
use App\Models\MedicineAdvice;
use App\Models\MedicineMaster;
use App\Models\StockItemMaster;
use App\Models\StockUniqueItem;
use App\Models\CustomerReminder;
use App\Models\ProductMedicineIitemReminder;
use App\Models\UploadPrescription;
use App\Models\MedicineComposition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\Item\Price;
use App\Http\Repositories\Item\Stock;
use BackOffice\Models\BranchInventory;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\SuccessResponse;
use App\Http\Repositories\Item\CheapPrice;
use Illuminate\Support\Facades\Log;

class MymedicineReminderController extends Controller
{
    private $customreminder;
    protected $medicine;
    private $stockItem;
    private $itemMaster;
    private $medinicemaster;
    private $manufacture;
    private $compostion;
    private $medicineadvice;
    private $productMedicineIitemReminder;

    public function __construct(CustomerReminder $customreminder, MedicineAdvice $medicineadvice, StockUniqueItem $stockItem, StockItemMaster $itemMaster, MedicineMaster $medinicemaster, ManuFacture $manufacture, MedicineComposition $compostion, UploadPrescription $upload, ProductMedicineIitemReminder $productMedicineIitemReminder)
    {
        $this->customreminder = $customreminder;
        $this->stockItem = $stockItem;
        $this->itemMaster = $itemMaster;
        $this->medinicemaster = $medinicemaster;
        $this->manufacture = $manufacture;
        $this->compostion = $compostion;
        $this->medicineadvice = $medicineadvice;
        $this->upload = $upload;
        $this->productMedicineIitemReminder=$productMedicineIitemReminder;
    }



    public function additem(Request $request)
    {

        $customerId = auth()->user()->cust_id;

        $validatedData = $request->validate([
            'puporse' => 'required',
            'interval' => 'required',
            'notification_status' => 'required',
            'notification_interwell' => 'required',
            'item' => 'nullable|array',
            'item.*.stit_ID' => 'nullable',
            'item.*.quantity' => 'nullable',
            'branch_id' => 'required',
        ]);

        $id = DB::table('customer_reminder')->insertGetId([
            'customer_id' => $customerId,
            'purpose' => $request['puporse'],
            'interval' => $request['interval'],
            'notification_status' => $request['notification_status'],
            'notification_interwell' => $request['notification_interwell']
        ]);

        $items = $request['item'];


        foreach ($items as $item) {




            $ismedinice = $this->itemMaster->select('isMedicine')->where('stit_ID', $item['stit_ID'])->first();


            DB::table('product_medicine_item')->insertGetId([
                'item_id' => $id,
                'stit_ID' => $item['stit_ID'],
                'ismedicine' => $ismedinice['isMedicine'],
                'qty' => $item['quantity']

            ]);
        }

        return $this->get($request);
        //return new SuccessWithData("successfully added");
    }
    public function updateitem(Request $request)
    {

        $customerId = auth()->user()->cust_id;
        $validatedData = $request->validate([
            'id' => 'required',
            'puporse' => 'required',
            'interval' => 'required',
            'notification_status' => 'required',
            'notification_interwell' => 'required',
            'item' => 'nullable|array',
            'item.*.stit_ID' => 'nullable',
            'item.*.quantity' => 'nullable',
        ]);


        $this->customreminder->where('id', $request['id'])
            ->where('customer_id', $customerId)

            ->update([
                'purpose' => $request['puporse'],
                'interval' => $request['interval'],
                'notification_status' => $request['notification_status'],
                'notification_interwell' => $request['notification_interwell']

            ]);

        $items = $request['item'];


        foreach ($items as $item) {


            $isexit = DB::table('product_medicine_item')
                ->where('stit_ID', $item['stit_ID'])
                ->where('item_id', $request['id'])->count();

            if ($isexit == 0) {
                $ismedinice = $this->itemMaster->select('isMedicine')->where('stit_ID', $item['stit_ID'])->first();

                DB::table('product_medicine_item')->insertGetId([
                    'item_id' => $request['id'],
                    'stit_ID' => $item['stit_ID'],
                    'ismedicine' => $ismedinice['isMedicine'],
                    'qty' => $item['quantity']
                ]);
            } else {

                DB::table('product_medicine_item')->where('item_id', $request['id'])->where('stit_ID', $item['stit_ID'])->update([

                    'qty' => $item['quantity']

                ]);
            }
        }

        return $this->get($request);
        // return new SuccessWithData("successfully updated");

    }
    public function delete($id)
    {
        DB::transaction(function () use ($id) {
            $this->customreminder
                ->where('id', $id)
                ->delete();
            $this->removeProductMedicineIitemReminder($id);
        });
        return new SuccessResponse("successfully deleted");        
       
    }
    public function notificationInterwellupdate(Request $request)
    {

        $customerId = auth()->user()->cust_id;

        $validatedData = $request->validate([
            'id' => 'required',
            'notification_interwell' => 'required',

        ]);

        $this->customreminder->where('id', $request['id'])
            ->where('customer_id', $customerId)

            ->update([

                'notification_interwell' => $request['notification_interwell']

            ]);

        return new SuccessWithData("successfully updated");
    }

    public function searchitem(Request $request)
    {
        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;
        $validatedData = $request->validate([
            'branch_id' => 'required',

        ]);
        $param = $request['param'];
        if (empty($request['param'])) {
            $data = $this->itemMaster->with(['mainImage' => function ($query) use ($domain) {

                $query->where('image_type', 1)
                    ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
            }])->select('stit_ID', 'stit_itemName', 'isMedicine')->get();
        } else {
            $data = $this->itemMaster->with(['mainImage' => function ($query) use ($domain) {

                $query->where('image_type', 1)
                    ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
            }])->select('stit_ID', 'stit_itemName', 'isMedicine', 'stit_SKU')->where('stit_SKU', 'LIKE', '%' . $param . '%')->get();
        }
        foreach($data as $key=>$value)
        {
            $selling_price=app(BranchInventory::class)->where('stit_id',$value['stit_ID'])->where('branch_id',$request['branch_id'])->count()>0

            ?app(BranchInventory::class)->where('stit_id',$value['stit_ID'])->where('branch_id',$request['branch_id'])->get():[];
            $data[$key]['selling_prize']=(count($selling_price)>0)?$selling_price[0]->selling_price:0;
        }



        return new SuccessWithData($data);
    }
    public function get(Request $request)
    {
        $customerId = auth()->user()->cust_id;
        $validatedData = $request->validate([
            'branch_id' => 'required',

        ]);
        $branch_id = $request['branch_id'];

        $details = $this->customreminder->with('items')->where('customer_id', $customerId)->get()->toArray();

        foreach ($details as $key => $detail) {
            $item_details = array();
            foreach ($detail['items'] as $ke => $data) {

                $item = $this->productdata($data['stit_ID'], $data['ismedicine'], $branch_id);

                array_push($item_details, $item);
            }
            $details[$key]['item_details'] = $item_details;
            $details[$key]['item_count'] = count($detail['items']);
        }
        return new SuccessWithData($details);
    }

    public function getallitem($branch_id)
    {
        $customerId = auth()->user()->cust_id;


        $details = $this->customreminder->with('items')->where('customer_id', $customerId)->get()->toArray();

        foreach ($details as $key => $detail) {
            $item_details = array();
            foreach ($detail['items'] as $ke => $data) {

                $item = $this->productdata($data['stit_ID'], $data['ismedicine'], $branch_id);

                array_push($item_details, $item);
            }

            $details[$key]['item_details'] = $item_details;
            $details[$key]['item_count'] = count($detail['items']);
        }
        return new SuccessWithData($details);
    }


    public function productdata($stit_id, $isMedicine, $branch_id)
    {

        $data = array('stit_ID' => $stit_id, 'isMedicine' => $isMedicine, 'branch_id' => $branch_id);
        return $this->medicineDetails($data);
    }

  public  function medicineDetails($fulldata)

    {

        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $branch_id = $fulldata['branch_id'];
        $std_id = $fulldata['stit_ID'];
        $isMedince = $fulldata['isMedicine'];
        $data = $this->itemMaster->with(['mainImage' => function ($query) use ($domain) {

                $query->where('image_type', 1)
                    ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
            }])->select('stit_ID', 'stit_itemName', 'isMedicine', 'stit_SKU')->where('stit_ID',$std_id);
        if($isMedince==1){
             $data=$data->where('isMedicine', 1) ;
       }        

        $data =$data->first(); 
        

        $selling_price=app(BranchInventory::class)->where('stit_id',$data['stit_ID'])->where('branch_id',$branch_id)->count()>0

        ?app(BranchInventory::class)->where('stit_id',$data['stit_ID'])->where('branch_id',$branch_id)->get():[];
        $data['selling_prize']=(count($selling_price)>0)?$selling_price[0]->selling_price:0;



        return $data;

        /*   $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";

            $branch_id = 10;
            $std_id = $fulldata['stit_ID'];
            $isMedince = $fulldata['isMedicine'];
            $MedinceMasterId = $this->stockItem->whereHas('itemMaster', function ($q) use ($std_id) {
                $q->where('stit_ID', $std_id);
            })->with(['itemMaster' => function ($query) use ($domain) {
                $query->with(['mainImage' => function ($qry) use ($domain) {
                    $qry->where('image_type', 1)
                        ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                }])->selectRaw($this->getProductFields());
            }])->selectRaw($this->getItemFields(1));
             if($isMedince==1){
                 $MedinceMasterId=$MedinceMasterId->where('isMedicine', 1) ;
             }
             $MedinceMasterId=$MedinceMasterId->first();


            $data = $this->checkField($MedinceMasterId, $branch_id);
     

        return $data;*/
    }


    public  function medicineDetails1($fulldata)

    {

        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;

        $branch_id = 10;
        $std_id = $fulldata['stit_ID'];
        $isMedince = $fulldata['isMedicine'];
        if ($isMedince == 1) {

            $stit_itemId = $this->itemMaster->select('stit_itemId')->where('stit_ID', $std_id)->first();


            $MedinceMasterId = $this->stockItem->whereHas('itemMaster', function ($q) use ($std_id) {
                $q->where('stit_ID', $std_id);
            })->with(['itemMaster' => function ($query) use ($domain) {
                $query->with(['mainImage' => function ($qry) use ($domain) {
                    $qry->where('image_type', 1)
                        ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                }])
                    ->with(['additionalImage' => function ($qry) use ($domain) {
                        $qry->where('image_type', 0)
                            ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                    }])

                    ->selectRaw($this->getProductFields());
            }])


                ->selectRaw($this->getItemFields(1))
                ->where('isMedicine', 1)->first();

            $MedinceCompostion = $this->medinicemaster->where('medicineMaster_id', $MedinceMasterId['fsi_item_id'])->first()->toArray();


            $compostion = $this->compostion->select('composition_name')->where('composition_id', $MedinceCompostion['medicine_composition'])->first();
            $manufacture = $this->manufacture->where('manufacture_id', $MedinceCompostion['medicine_manufacture'])->first();

            $MedinceMasterIDS = $this->medinicemaster->select('medicineMaster_id')->where('medicine_composition', $MedinceCompostion['medicine_composition'])->where('medicine_type',$MedinceCompostion['medicine_type'])->get()->toArray();

            $MedicineMasterID = array_column($MedinceMasterIDS, 'medicineMaster_id');

            if (($key = array_search($MedinceMasterId['fsi_item_id'], $MedicineMasterID)) !== false) {
                unset($MedicineMasterID[$key]);
            }

            $stockUniqueitem = $this->stockItem->whereIn('fsi_item_id', $MedicineMasterID)->where('isMedicine', $isMedince)->get()->toArray();

            $stock_item_master = $this->itemMaster->whereIn('stit_itemId', array_column($stockUniqueitem, 'fsi_item_id'))->get()->toArray();


            $alternatebrands = $item = $this->stockItem->whereHas('itemMaster', function ($q) use ($stock_item_master) {
                $q->whereIn('stit_ID', array_unique(array_column($stock_item_master, 'stit_ID')));
            })
                ->with(['itemMaster' => function ($query) use ($domain) {
                    $query->with(['mainImage' => function ($qry) use ($domain) {
                        $qry->where('image_type', 1)
                            ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                    }])
                        ->with(['additionalImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 0)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }])

                        ->selectRaw($this->getProductFields());
                }])->selectRaw($this->getItemFields(1))
                ->where('isMedicine', 1)->get();


            $total_count = count($alternatebrands);
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



            $data = $this->checkField($MedinceMasterId, $branch_id);

            // if (isset(auth()->user()->cust_id)) {

            //     $check = $this->upload->where('cust_id',auth()->user()->cust_id)->where('medinicemasterid', $data['fsi_def_itemmaster_id'])
            //         ->where('item_master_id', $data['item_master'][0]['stit_ID'])->where('status', 1)
            //         ->where('expiry_date', '>=', now()->format('Y-m-d'))->get();
            //     if (count($check)) {
            //         $approve_status = 1;
            //     }
            //     //check medicine vaild and expire date

            // }
            $prescription = $this->itemMaster->select('prescription')->where('stit_ID', $std_id)->first();

            if ($data) {
                $data['composition_name'] = $compostion->composition_name;
                $data['isPrescription'] = $prescription['prescription'];
                $data['manufacture'] = $manufacture->manufacture_name;
                $data['tabmenu'] = $tab;
                $data['alternateMedicine'] = array('MedicineList' => $altermedicine, 'viewDetails' => $viewaltermedicine);
                // $data['approvel'] = $approve_status;
            }
        } else {

            $item = $this->stockItem->whereHas('itemMaster', function ($q) use ($std_id, $domain) {
                $q->where('stit_ID', $std_id);
            })
                ->with(['itemMaster' => function ($query) use ($domain) {
                    $query->with(['mainImage' => function ($qry) use ($domain) {
                        $qry->where('image_type', 1)
                            ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                    }])
                        ->with(['additionalImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 0)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }])
                        ->selectRaw($this->getProductFields());
                }])
                ->selectRaw($this->getItemFields(0))
                ->first();

            $data = $this->checkField($item, $branch_id);
        }


        return $data;
    }

    private function getItemFields($dat)
    {

        if ($dat == 0) //product
        {
            return
                "fsi_item_id,fsi_uid,fsi_uid as item_group_id,CONCAT(fsi_brand_name,' ',fsi_item_name) as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,isMedicine,fsi_displaylabel,fsi_def_itemmaster_id";
        } else { //medinice
            return
                "fsi_item_id,fsi_uid,fsi_uid as item_group_id,fsi_item_name as item_name,fsi_brand_name as brand_name,fsi_category_id as category_id,fsi_categry_name as category_name,fsi_variant as variant,isMedicine,fsi_displaylabel,fsi_def_itemmaster_id";
        }
    }

    private function getProductFields()
    {
        return "stit_ID,
                stit_fsiuid,
                stit_quantity as quantity,
                stit_ID as itemId,
                stit_Description as short_description,
                stit_long_description as long_description,
                stit_item_volume as stock_available,
                stit_GST as selling_prize,
                stit_MRP as mrp,
                cos_nos";
    }


    private function checkField($item, $branch_id)
    {
        $data = $item ? $item->toArray() : [];
        if ($data)
            $data['item_master'] = $this->productFields($data['item_master'], $branch_id, $data['fsi_uid']);
        return $data;
    }


    private function productFields(array $item, $branch_id, $group_id)
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
         /*   $stitId = $item[$i]['stit_ID'];
            $stock_count = array_key_exists($stitId, $stock) ? $stock[$stitId] : 0;
            $mrp = array_key_exists($stitId, $price['mrp']) ? $price['mrp'][$stitId] : 0;
            $selling_price = array_key_exists($stitId, $price['selling_price']) ? $price['selling_price'][$stitId] : 0;*/

             $stitId = $item[$i]['stit_ID'];
            $cos_nos= $item[$i]['cos_nos'];

            $cos_nos = ($cos_nos>0)?$cos_nos:1;
            
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

             $mrp=round($mrp,2);
             $selling_price=round($selling_price,2);
             $percentage=round($percentage,2);
            $default_val = in_array($stitId, $cheap) ? 1 : 0;
            $item[$i]['selling_prize'] = $selling_price;
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


    private function removeProductMedicineIitemReminder($id)
    {
        
        return ProductMedicineIitemReminder::where('item_id', $id)
            ->delete();
    }


}
