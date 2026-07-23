<?php

namespace BackOffice\Http\Controllers\Agent;

use Carbon\carbon;
use App\Models\ProductBrand;
use Illuminate\Http\Request;
use App\Models\CompanyBranch;
use App\Models\FinascopState;
use BackOffice\Models\Branch;
use App\Models\StockItemMaster;
use App\Models\FinascopDistrict;
use BackOffice\Models\BranchGroup;
use Illuminate\Support\Facades\DB;
use BackOffice\Models\BusinessType;
use Illuminate\Support\Facades\Log;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\UnauthenticatedResponse;
use BackOffice\Http\Requests\AgentStoresRequest;
use BackOffice\Http\Requests\BoyLocationRequest;
use BackOffice\Http\Requests\AgentStoresAddRequest;
use BackOffice\Http\Requests\AgentStoresUpdateRequest;
use BackOffice\Http\Requests\AgentStoresProductRequest;
use BackOffice\Http\Requests\AgentStoresGroupAddRequest;
use BackOffice\Http\Requests\AgentStoresGroupUpdateRequest;
  
class AgentMasterDataController
{
    public function __invoke(BoyLocationRequest $request)
    {
        if(auth_user()->fcm_id != $request->fcm_id){
            return new UnauthenticatedResponse('FCM ID Mismatch');
        }
        auth_user()->update([
            'is_offline' => 0,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'latlng_updated_at' => now(),
            'loggedout_by' => 0,
        ]);

        return new SuccessResponse('Location stored successfully');
    }

    public function getBrands()
    {
       // $request->branch_id = auth_user()->deli_branch_id;
       $data = ProductBrand::where('status', 1)                   
                   ->select('brand_id',
                             'brand_name',
                             'img_url as image_url',
                             'img_name',
                             'top_brand',
                             'status as brand_status'   
                       )
                    ->get();
       
        return new SuccessWithData(
            $data
        );
    }

    public function getBranchGroup()
    {
       // $request->branch_id = auth_user()->deli_branch_id;
      
       
       $data = BranchGroup::where('status', '1')
                               
                   ->select('store_group_id',
                             'store_group_name')
                    ->get(); 
       
        return new SuccessWithData(
            $data
        );
    }

    public function getBranches(AgentStoresRequest $request)
    {
       // $request->branch_id = auth_user()->deli_branch_id;
       
       $data = Branch::where('br_storeGroup', $request->storegroup)
		->with(['on_off_time' => function ($qry) {
            $qry->select('branch_id', 'id', 'br_open_time', 'br_close_time');
        }])
        ->get(); 
       
        return new SuccessWithData(
            $data
        );
    }

    public function getBranchProducts(AgentStoresProductRequest $request)
    {
       // $request->branch_id = auth_user()->deli_branch_id;
       $company =  CompanyBranch::where('br_Id',$request->store)
                    ->select('comp_id')
                   ->first();
       $branch =  Branch::where('br_Id',$request->store)
                   ->select('br_storeGroup')
                  ->first();
       $query = StockItemMaster::query();
       $query->where('stit_status', '1')                                
                   ->selectraw("stit_ID as id, stit_sku as sku, (SELECT fsipc_code FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = finascop_stock_itemmaster.stit_ID AND fsipc_isCompany = {$company['comp_id']}) as CompanyCode,(SELECT fsipc_code FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = finascop_stock_itemmaster.stit_ID AND fsipc_isCompany = 0 AND fsipc_storeGroup = {$branch['br_storeGroup']} AND fsipc_isIndividual = 0) as GroupCode,(SELECT fsipcs_Code FROM finascop_stock_itemmaster_product_code_stores WHERE fsipc_stit_id = finascop_stock_itemmaster.stit_ID  AND  fsipcs_store = {$request['store']}) as BranchCode " 
                       );
        if($request->brand>0){
            $query->where('pdt_brand', $request['brand']);    
        }
        if($request->catlevel>0){
            if($request->catlevel==1){
                $query->where('product_parentcategory', $request['category']);    
            }elseif($request->catlevel==2){
                $query->where('product_midcategory', $request['category']);  
            }elseif($request->catlevel==3){
                $query->where('product_category', $request['category']);  
            }
        }
       $branch = [
           "products" => $query->paginate($request->count),
           "count" => $query->count(),
       ];
       $items = [];
       foreach($branch['products'] as &$products){
           //"CompanyCode":null,"GroupCode":null,"BranchCode":null
           $code ='';
           if($products['BranchCode'] != ""){
            $code = $products['BranchCode'];
           }elseif($products['GroupCode'] != ""){
            $code = $products['GroupCode'];
           }elseif($products['CompanyCode'] != ""){
            $code = $products['CompanyCode'];
           }
           $products['erpid'] = $code;
           unset($products['BranchCode']);
           unset($products['GroupCode']);
           unset($products['CompanyCode']);
       }
       $home['value'] = $branch['products'];
       $home['min_count'] = $request->count;
       $home['total_count'] = $branch['count'];
       
        return new SuccessWithData(
            $home
        );
    }

    public function getStates()
    {
       // $request->branch_id = auth_user()->deli_branch_id;
      
       
       $data = FinascopState::select('st_ID','st_name')
                    ->get(); 
       
        return new SuccessWithData(
            $data
        );
    }

    public function getBusinesstype()
    {
       // $request->branch_id = auth_user()->deli_branch_id;
      
        $storegroupid = getHeaderStoreGroup();
        
       $data = BusinessType::select('finascop_business_type.business_type_id','finascop_business_type.business_type_name');
       if($storegroupid > 0)
           $data->join('finascop_branch_group_business_type as bt', 'bt.business_type_id', 'finascop_business_type.business_type_id')->where('bt.store_group_id', $storegroupid);

       $data = $data->get(); 
       
        return new SuccessWithData(
            $data
        );
    }
    public function getDistricts($st_ID)
    {
        if(!isset($st_ID) || intval($st_ID)==0){
            return new ErrorResponse("State id is not set");  
        }
       $data = FinascopDistrict::where('st_Id',$st_ID)
                    ->select('dst_Id','dst_Name')
                    ->get(); 
       
        return new SuccessWithData(
            $data
        );
    }
    public function addBranches(AgentStoresAddRequest $request){

        $brdata = Branch::where('br_IsCPD', 1)                   
       ->select('br_ID','br_Name','br_ReferenceID')
        ->first();
        $maxbrid = Branch::max('br_ID'); 
        $compdets = DB::table('finascop_branch_company')
        ->select('finascop_branch_company.comp_id as comp_id','comp_ReferenceId')
        ->join('finascop_company as fb', 'fb.comp_id', 'finascop_branch_company.comp_id')                    
        ->where('cmp_status', 'Active')
        ->first() ;

        $data = array();
        $data['apikey'] =  $compdets->comp_ReferenceId ;
        $data['tstamp'] =  now()->format('YMDHis');
        $data['br_Company'] =  $compdets->comp_id;
        $data['branch_shortname'] =  str_pad((intval($maxbrid)+1),4,"0",STR_PAD_LEFT );
        $data['br_Name'] =  $request->brname . '_' .  $data['branch_shortname'];    
        $data['br_Address'] =  $request->braddress;
        $data['br_State'] =  $request->brstate;
        $data['br_District'] =  $request->brdistrict;
        $data['br_City'] =  $request->brcity;
        $data['br_pincode'] =  $request->brpincode;
        $data['br_Incharge'] =  $request->brincharge;
        $data['br_Phone'] =  $request->brphone;
        $data['br_Email'] =  $request->bremail;
        $data['br_Fax'] =  $request->brfax;
        $data['br_stockLevel'] =  $request->brstocklevel;
        $data['br_IsCPD'] =  0;
        $data['br_cpd'] =  $brdata->br_ID;
        $data['br_defaultapibranch'] =  $request->brdefaultapibranch;
        $data['br_storeGroup'] =  $request->brstoregroup;
        $data['br_Lat'] =  $request->brlat;
        $data['br_Lng'] =  $request->brlng;       
        $data['br_byagent'] =  1;
        //$data['br_ID'] = -1;
        $data['br_businessType'] = 1;
       


        $param = array(
        'apikey' => $compdets->comp_ReferenceId,
        'branchkey' => $brdata->br_ReferenceID,
        'isnew' => 'true',
        'Content-Type' => 'application/x-www-form-urlencoded',
        'data' => json_encode($data) );

        $ch = curl_init(config('finascope.api_url').'branch');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($param));
      //  curl_setopt($ch, CURLOPT_PROXY, 'http://idccfm.axisb.com:1050'); //Use Proxy wherever needed and change IP accordingly
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $returnData = curl_exec($ch);        
        $data = json_decode($returnData,true);
        if(isset($data['success']) && $data['success']==true){
            return new SuccessWithData(
                array("id"=>$data['Data']['Branchid'],"ref"=>$data['Data']['BranchRef'])
            );
        }else{
            return new ErrorResponse( $returnData); 
           
        }
         
        

    }
    public function updateBranches(AgentStoresUpdateRequest $request){
    
        $brdata = Branch::where('br_IsCPD', 1)                   
       ->select('br_ID','br_Name','br_ReferenceID')
        ->first();
        $brid = $request->brid; 
        $shortname = Branch::where('br_id', $brid)                   
        ->select('branch_shortname')
         ->first();        
        $compdets = DB::table('finascop_branch_company')
        ->select('finascop_branch_company.comp_id as comp_id','comp_ReferenceId')
        ->join('finascop_company as fb', 'fb.comp_id', 'finascop_branch_company.comp_id')                    
        ->where('cmp_status', 'Active')
        ->first() ;

        $data = array();
        $data['apikey'] =  $compdets->comp_ReferenceId ;
        $data['tstamp'] =  now()->format('YMDHis');
        $data['br_Company'] =  $compdets->comp_id;
        $data['branch_shortname'] =  $shortname->branch_shortname;
        $data['br_Name'] =  $request->brname . '_' . $shortname->branch_shortname;        
        $data['br_Address'] =  $request->braddress;
        $data['br_State'] =  $request->brstate;
        $data['br_District'] =  $request->brdistrict;
        $data['br_City'] =  $request->brcity;
        $data['br_pincode'] =  $request->brpincode;
        $data['br_Incharge'] =  $request->brincharge;
        $data['br_Phone'] =  $request->brphone;
        $data['br_Email'] =  $request->bremail;
        $data['br_Fax'] =  $request->brfax;
        $data['br_stockLevel'] =  $request->brstocklevel;
        $data['br_IsCPD'] =  0;
        $data['br_cpd'] =  $brdata->br_ID;
        $data['br_defaultapibranch'] =  $request->brdefaultapibranch;
        $data['br_storeGroup'] =  $request->brstoregroup;
        $data['br_Lat'] =  $request->brlat;
        $data['br_Lng'] =  $request->brlng;       
        $data['br_byagent'] =  1;
        $data['br_ID'] =  $brid;
        $data['br_businessType'] = 1;
       


        $param = array(
        'apikey' => $compdets->comp_ReferenceId,
        'branchkey' => $brdata->br_ReferenceID,
        'isnew' => 'false',
        'Content-Type' => 'application/x-www-form-urlencoded',
        'data' => json_encode($data) );

        $ch = curl_init(config('finascope.api_url').'branch');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($param));
      //  curl_setopt($ch, CURLOPT_PROXY, 'http://idccfm.axisb.com:1050'); //Use Proxy wherever needed and change IP accordingly
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $returnData = curl_exec($ch);        
        $data = json_decode($returnData,true);
        if(isset($data['success']) && $data['success']==true){
                    return new SuccessResponse(
                "Saved successfully."
            );
        }else{
            return new ErrorResponse( $returnData); 
           
        }
         
        
    }
    public function updateBranchesStatus(Request $request){
        if(!isset($request->brid) || intval($request->brid) ==0){
            return new ErrorResponse( "Invalid brid");
        }
        if(!isset($request->enable) || (intval($request->enable) !=0 && intval($request->enable)!=1)){
            return new ErrorResponse( "Specify 1/0 to enable or disable branch");
        }
        
        $brdata = Branch::where('br_ID', $request->brid)                   
       ->select('br_ID','br_Name','br_ReferenceID','br_IsCPD')
        ->first();

        if(!isset($brdata->br_ID) || intval($brdata->br_ID) ==0){
            return new ErrorResponse( "Invalid brid");
        }

        $brdata = Branch::where('br_ID', $request->brid)                   
        ->update(['br_status' => ($request->enable=='1'?'Active':'InActive')]);
        
        return new SuccessResponse("Saved successfully.");  
        
        
    }
    public function setstoreasdefault(Request $request){
        if(!isset($request->brid) || intval($request->brid) ==0){
            return new ErrorResponse( "Invalid brid");
        }
        
        $brdata = Branch::where('br_ID', $request->brid)                   
       ->select('br_ID','br_Name','br_ReferenceID','br_IsCPD','br_storeGroup')
        ->first();

        if(!isset($brdata->br_ID) || intval($brdata->br_ID) ==0){
            return new ErrorResponse( "Invalid brid");
        }

        $brdata = Branch::where('br_storeGroup', $brdata->br_storeGroup)                   
        ->update(['br_isdefaultstore' => 0]);

        $brdata = Branch::where('br_ID', $request->brid)                   
        ->update(['br_isdefaultstore' => 1]);
     
        return new SuccessResponse("Saved successfully.");      
    

    }
    public function addbranchgroup(AgentStoresGroupAddRequest $request){

        $brdata = Branch::where('br_IsCPD', 1)                   
       ->select('br_ID','br_Name','br_ReferenceID')
        ->first();
        $maxbrid = Branch::max('br_ID'); 
        $compdets = DB::table('finascop_branch_company')
        ->select('finascop_branch_company.comp_id as comp_id','comp_ReferenceId')
        ->join('finascop_company as fb', 'fb.comp_id', 'finascop_branch_company.comp_id')                    
        ->where('cmp_status', 'Active')
        ->first() ;
        $data = array();
        $data['apikey'] =  $compdets->comp_ReferenceId ;
        $data['tstamp'] =  now()->format('YMDHis');
        $data['name'] = $request->name;
        $data['store_group_primary_businessType'] = $request->primarybusinesstype;
        $data['store_group_additional_businessType'] = $request->additionalbusinessType;
        $data['status'] = 1;

        $param = array(
        'apikey' => $compdets->comp_ReferenceId,
        'branchkey' => $brdata->br_ReferenceID,
        'isnew' => 'true',
        'Content-Type' => 'application/x-www-form-urlencoded',
        'data' => json_encode($data) );

        $ch = curl_init(config('finascope.api_url').'branch/branchgroup');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($param));
        //  curl_setopt($ch, CURLOPT_PROXY, 'http://idccfm.axisb.com:1050'); //Use Proxy wherever needed and change IP accordingly
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $returnData = curl_exec($ch);        
        $data = json_decode($returnData,true);
        if(isset($data['success']) && $data['success']==true){
            return new SuccessWithData(
                $data['Data']['BranchGroup']
            );
        }else{
            return new ErrorResponse( $returnData); 
            
        }
    }
    public function updatebranchgroup(AgentStoresGroupUpdateRequest $request){

        $brdata = Branch::where('br_IsCPD', 1)                   
       ->select('br_ID','br_Name','br_ReferenceID')
        ->first();
        $maxbrid = Branch::max('br_ID'); 
        $compdets = DB::table('finascop_branch_company')
        ->select('finascop_branch_company.comp_id as comp_id','comp_ReferenceId')
        ->join('finascop_company as fb', 'fb.comp_id', 'finascop_branch_company.comp_id')                    
        ->where('cmp_status', 'Active')
        ->first() ;
        $data = array();
        $data['apikey'] =  $compdets->comp_ReferenceId ;
        $data['tstamp'] =  now()->format('YMDHis');
        $data['id'] = $request->id;
        $data['name'] = $request->name;
        $data['store_group_primary_businessType'] = $request->primarybusinesstype;
        $data['store_group_additional_businessType'] = $request->additionalbusinessType;
        $data['status'] = 1;

        $param = array(
        'apikey' => $compdets->comp_ReferenceId,
        'branchkey' => $brdata->br_ReferenceID,
        'isnew' => 'true',
        'Content-Type' => 'application/x-www-form-urlencoded',
        'data' => json_encode($data) );

        $ch = curl_init(config('finascope.api_url').'branch/branchgroup');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($param));
        //  curl_setopt($ch, CURLOPT_PROXY, 'http://idccfm.axisb.com:1050'); //Use Proxy wherever needed and change IP accordingly
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $returnData = curl_exec($ch);        
        $data = json_decode($returnData,true);
        if(isset($data['success']) && $data['success']==true){
                    return new SuccessResponse(
                "Saved successfully."
            );
        }else{
            return new ErrorResponse( $returnData); 
            
        }
    }
    
}
