<?php

namespace BackOffice\Http\Controllers\RelationOfficer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use BackOffice\Models\RelationOfficer\ROUser;
use BackOffice\Models\RelationOfficer\ROFinascopContacts;
use BackOffice\Models\RelationOfficer\ROFinascopLeads;
use BackOffice\Models\RelationOfficer\ROFinascopProspects;
use App\Helpers\HttpCurlCalls;
use BackOffice\Http\Repositories\RelationOfficer\ROSurveyRepository;

class ROMerchantController
{
    public function __construct()
    {
    }
    public function getAllMerchants()
    {
        $roUser = auth_user();
        try
        {
            $merchantDetails = ROFinascopProspects::where([
                ['crpr_CreatedBy', $roUser->id],
                ['crmuId', 3],
                ['crpr_type', 1],
                ['storeGroupId', '<>', 0]
            ])
            ->with('retailCategory:business_category_id,business_category_name')
            ->with('contactType:id,name')
            ->with('storegroup:store_group_id,store_group_name,contactNumber')
            ->paginate(10);
            return new SuccessWithData($merchantDetails);
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function viewSingleMerchant($prID)
    {
        $roUser = auth_user();
        try
        {
            $merchantDetails = ROFinascopProspects::where([
                ['id', $prID],
                ['crpr_CreatedBy', $roUser->id],
                ['crmuId', 3],
                ['crpr_type', 1],
                ['storeGroupId', '<>', 0]
            ])
            ->with('retailCategory:business_category_id,business_category_name')
            ->with('contactType:id,name')
            ->with('storegroup:store_group_id,store_group_name,contactNumber')
            ->first();
            if($merchantDetails)
            {
                $merchantDetails->survey = (new ROSurveyRepository)->getSurveyDetails($prID, 'prospect', $roUser->id);
                return new SuccessWithData($merchantDetails);
            }
            return new ErrorResponse('Prospect not found assigned to this user.');
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function getSearchedMerchants(Request $request)
    {
        $roUser = auth_user();
        try
        {
            $merchantSearch = ROFinascopProspects::select('*');
            if(@$request->store_name)
            {
                $merchantSearch->where('crpr_orgName', 'like', '%'.$request->store_name.'%');
            }
            if(@$request->person_name)
            {
                $merchantSearch->where('crpr_indContactperson', 'like', '%'.$request->person_name.'%');
            }
            if(@$request->phone)
            {
                $merchantSearch->where('crpr_orgContactNo', 'like', '%'.$request->phone.'%');
            }
            if(@$request->location)
            {
                $merchantSearch->where('crpr_location', 'like', '%'.$request->location.'%')
                    ->orWhere('crpr_glocality', 'like', '%'.$request->location.'%')
                    ->orWhere('crpr_gplace', 'like', '%'.$request->location.'%')
                    ->orWhere('crpr_orgAddress', 'like', '%'.$request->location.'%');
            }
            $merchantSearch
                ->where([
                    ['crpr_CreatedBy', $roUser->id],
                    ['crmuId', 3],
                    ['crpr_type', 1],
                    ['storeGroupId', '<>', 0]
                ])
                ->with('retailCategory:business_category_id,business_category_name')
                ->with('contactType:id,name')
                ->with('storegroup:store_group_id,store_group_name,contactNumber');
            return new SuccessWithData($merchantSearch->paginate(10));
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function getFilteredMerchants(Request $request)
    {
        $roUser = auth_user();
        try
        {
            $select = ['*'];
            $merchantsByLocation = ROFinascopProspects::query();
            if(@$request->contact_type)
            {
                $contact_type = is_array($request->contact_type) ? $request->contact_type : [$request->contact_type];
                $merchantsByLocation = $merchantsByLocation->whereIn('crpr_type', $contact_type);
            }
            if(@$request->retailer_category)
            {
                $merchantsByLocation = $merchantsByLocation->where('retailCategory', $request->retailer_category);
            }
            if((@$request->latitude > 0) && (@$request->longitude > 0))
            {
                $lat = $request->latitude;
                $lng = $request->longitude;
                $select[] = DB::raw('calcDistance('.$lat.', '.$lng.', glatitude, glongitude) AS distance');
            }
            $merchantsByLocation
                ->select($select)
                ->where([
                    ['crpr_CreatedBy', $roUser->id],
                    ['crmuId', 3],
                    ['crpr_type', 1],
                    ['storeGroupId', '<>', 0]
                ])
                ->with('retailCategory:business_category_id,business_category_name')
                ->with('contactType:id,name')
                ->with('storegroup:store_group_id,store_group_name,contactNumber');
            if((@$request->latitude > 0) && (@$request->longitude > 0))
            {
                $merchantsByLocation->orderBy('distance');
            }
            return new SuccessWithData($merchantsByLocation->paginate(10));
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
}