<?php

namespace App\Http\Controllers;


use App\Models\Branch;
use Illuminate\Http\Request;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\ErrorResponse;
use Illuminate\Support\Str;
class BranchController extends Controller
{
    protected $branch;
    public function __construct(Branch $branch)
    {
        $this->branch=$branch;
    }

    public function getlocation(Request $request)
    {
        $validatedData = $request->validate([
            'pincode' => 'required'

        ]);

        $branch_details=$this->branch->select('br_ID')->where('br_pincode',$request['pincode'])->where('br_status','Active')->first();

        if(empty($branch_details))
        {

            $branch_details=$this->branch->select('br_ID')->where('br_ID','1')->where('br_status','Active')->first();
        }

        return new SuccessWithData($branch_details);


    }
    
    public function getBranchesByStoregroup()
    {
        try
        {
            $storegroupid = getHeaderStoreGroup();
            if($storegroupid > 0)
            {
                $branchList = Branch::select('br_ID', 'br_Name', 'br_City', 'br_District', 'br_State', 'br_Address', 'br_Email', 'br_Phone', 'br_GST', 'br_status')
                ->where([
                    ['br_storeGroup', $storegroupid],
                    ['br_status', 'Active']
                ])
                ->with('state:st_ID,st_name', 'district:dst_Id,dst_Name')
                ->get();
                return new SuccessWithData($branchList);
            }
            return new ErrorResponse('Store not available.');
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
}
