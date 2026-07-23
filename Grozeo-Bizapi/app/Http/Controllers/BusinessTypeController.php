<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use Illuminate\Support\Facades\DB;

class BusinessTypeController extends Controller
{

    public function __construct()
    {
    }


    public function get()
    {
       
        return new SuccessWithData(
            ""
        );

    }
    public function GetRetailTypes($businessTypeId)
    {
        $storegroupid = getHeaderStoreGroup();

        $data = DB::select("SELECT * FROM retaline_business_category WHERE store_group_id = ".$storegroupid." AND status = 1 ORDER BY displayOrder ASC, business_category_name");
        
        return new SuccessWithData(
            $data
        );

    }

    public function getBusinesstypeByRetailCategory($retailCatId)
    {
       // $request->branch_id = auth_user()->deli_branch_id;
      
        $storegroupid = getHeaderStoreGroup();
        
       //$data = BusinessType::select('finascop_business_type.business_type_id','finascop_business_type.business_type_name')-whereRaw();
       //$data = DB::select("SELECT * FROM finascop_business_type bt WHERE EXISTS(SELECT * FROM retaline_business_category WHERE business_category_id = " . $retailCatId . " AND FIND_IN_SET(bt.business_type_id, rbc_business_type) > 0)");
        $data = DB::select("SELECT * FROM finascop_business_type bt ". ($storegroupid > 0 ? " INNER JOIN finascop_branch_group_business_type bgbt ON bgbt.business_type_id = bt.business_type_id " : "") ." WHERE " . ($storegroupid > 0 ? " bgbt.store_group_id = " . $storegroupid . " AND " : "") . " EXISTS(SELECT * FROM retaline_business_category WHERE business_category_id = " . $retailCatId . " AND FIND_IN_SET(bt.business_type_id, rbc_business_type) > 0)");
       //if($storegroupid > 0)
       //    $data->join('finascop_branch_group_business_type as bt', 'bt.business_type_id', 'finascop_business_type.business_type_id')->where('bt.store_group_id', $storegroupid);

	return new SuccessWithData(
            $data
        );



       $data = $data->get(); 
       
        return new SuccessWithData(
            $data
        );
    }


}
