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
use BackOffice\Http\Requests\RelationOfficer\ROUpdateLeadRequest;
use BackOffice\Http\Repositories\RelationOfficer\ROSurveyRepository;

class ROLeadController
{
    public function __construct()
    {
    }
    public function getAllLeads()
    {
        $roUser = auth_user();
        try
        {
            $leadDetails = ROFinascopLeads::where([
                ['assignedRO', $roUser->id],
                ['crmuId', 2],
                ['crle_type', 1]
            ])
            ->with('retailCategory:business_category_id,business_category_name')
            ->with('contactType:id,name')
            ->withCount('survey')
            ->paginate(10);
            return new SuccessWithData($leadDetails);
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }

    public function leadToProspect($leadID)
    {
        $roUser = auth_user();
        try
        {
            $getLead = ROFinascopLeads::where([
                ['id', $leadID],
                ['assignedRO', $roUser->id],
                ['crmuId', 2]
            ])->first();
            if($getLead)
            {
                if(($getLead->crle_orgEmail != '') || ($getLead->crle_orgEmail != NULL))
                {
                    $updateLead = ROFinascopLeads::where('id', $leadID)->update(['crmuId' => 3]);
                    if($updateLead)
                    {
                        $createProspect = $this->convertToProspect($leadID);
                        if($createProspect)
                        {
                            return new SuccessResponse('Lead Converted to Prospect.');
                        }
                    }
                    return new ErrorResponse('This lead is unable to convert to prospect.');
                }
                return new ErrorResponse('Email not available for this lead.');
            }
            return new ErrorResponse('Lead not found.');
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }

    public function viewSingleLead($leadID)
    {
        $roUser = auth_user();
        try
        {
            $leadDetails = ROFinascopLeads::where([
                ['id', $leadID],
                ['assignedRO', $roUser->id],
                ['crmuId', 2],
                ['crle_type', 1]
            ])
            ->with('retailCategory:business_category_id,business_category_name')
            ->with('contactType:id,name')
            ->withCount('survey')
            ->first();
            if($leadDetails)
            {
                return new SuccessWithData($leadDetails);
            }
            return new ErrorResponse('Lead not found assigned to this user.');
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }

    
    public function updateLead(ROUpdateLeadRequest $request, $leadID)
    {
        $roUser = auth_user();
        try
        {
            $leadDetails = ROFinascopLeads::where([
                ['id', $leadID],
                ['assignedRO', $roUser->id],
                ['crmuId', 2],
                ['crle_type', 1]
            ])->first();
            if($leadDetails)
            {
                $update = ROFinascopLeads::where('id', $leadID)->update([
                    'crle_orgName'              => $request->store_name,
                    'crle_location'             => $request->google_address,
                    'crle_orgPincode'           => $request->post_code,
                    'crle_orgCountry'           => $request->country,
                    'crle_groute'               => $request->route,
                    'crle_glocality'            => $request->locality,
                    'crle_gplace'               => $request->place,
                    'glatitude'                 => $request->latitude,
                    'glongitude'                => $request->longitude,
                    'crle_orgAddress'           => $request->address_1,
                    'crle_orgAddress_2'         => $request->address_2,
                    'crle_indContactperson'     => $request->contact_person,
                    'retailCategory'            => $request->retailer_category,
                    'crmRemarks'                => $request->remarks,
                    'retailCategory_isOthers'   => $request->is_others,
                    'crle_UpdatedBy'            => (@$roUser->id) ? $roUser->id : 0,
                    'crle_orgEmail'             => ((@$request->email != "") ? $request->email : @$leadDetails->crle_orgEmail),
                    'crle_indMobile'            => ((@$request->phone != "") ? $request->phone : @$leadDetails->crle_indMobile),
                    'crle_orgContactNo'         => ((@$request->contact_number != "") ? $request->contact_number : @$leadDetails->crle_orgContactNo)
                ]);
                if($update)
                {
                    return new SuccessResponse('Lead updated');
                }
                return new ErrorResponse("Unable to update");
            }
            return new ErrorResponse("Lead not found");
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }

    public function getSearchedLeads(Request $request)
    {
        $roUser = auth_user();
        try
        {
            $leadSearch = ROFinascopLeads::select('*');
            if(@$request->store_name)
            {
                $leadSearch->where('crle_orgName', 'like', '%'.$request->store_name.'%');
            }
            if(@$request->person_name)
            {
                $leadSearch->where('crle_indContactperson', 'like', '%'.$request->person_name.'%');
            }
            if(@$request->phone)
            {
                $leadSearch->where('crle_orgContactNo', 'like', '%'.$request->phone.'%');
            }
            if(@$request->location)
            {
                $leadSearch->where('crle_location', 'like', '%'.$request->location.'%')
                    ->orWhere('crle_glocality', 'like', '%'.$request->location.'%')
                    ->orWhere('crle_gplace', 'like', '%'.$request->location.'%')
                    ->orWhere('crle_orgAddress', 'like', '%'.$request->location.'%');
            }
            $leadSearch
                ->where([['assignedRO', $roUser->id], ['crmuId', 2], ['crle_type', 1]])
                ->with('retailCategory:business_category_id,business_category_name')
                ->with('contactType:id,name')
                ->withCount('survey');
            return new SuccessWithData($leadSearch->paginate(10));
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function getFilteredLeads(Request $request)
    {
        $roUser = auth_user();
        try
        {
            $select = ['*'];
            $LeadsByLocation = ROFinascopLeads::query();
            if(@$request->contact_type)
            {
                $contact_type = is_array($request->contact_type) ? $request->contact_type : [$request->contact_type];
                $LeadsByLocation = $LeadsByLocation->whereIn('crle_type', $contact_type);
            }
            if(@$request->retailer_category)
            {
                $LeadsByLocation = $LeadsByLocation->where('retailCategory', $request->retailer_category);
            }
            if((@$request->latitude > 0) && (@$request->longitude > 0))
            {
                $lat = $request->latitude;
                $lng = $request->longitude;
                $select[] = DB::raw('calcDistance('.$lat.', '.$lng.', glatitude, glongitude) AS distance');
            }
            $LeadsByLocation
                ->select($select)
                ->where([['assignedRO', $roUser->id], ['crmuId', 2], ['crle_type', 1]])
                ->with('retailCategory:business_category_id,business_category_name')
                ->with('contactType:id,name')
                ->withCount('survey');
            if((@$request->latitude > 0) && (@$request->longitude > 0))
            {
                $LeadsByLocation->orderBy('distance');
            }
            return new SuccessWithData($LeadsByLocation->paginate(10));
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }



    private function convertToProspect($leadID)
    {
        $leadDetails = ROFinascopLeads::where('id', '=', $leadID)->first();
        if($leadDetails)
        {
            $code = substr(strtoupper(md5(now().$leadDetails->crle_orgEmail)), 0, 4);
            $createProspect = ROFinascopProspects::create([
                'crpr_orgName'              => $leadDetails->crle_orgName,
                'crpr_mode'                 => $leadDetails->crle_mode,
                'crpr_type'                 => $leadDetails->crle_type,
                'crpr_description'          => $leadDetails->crle_description,
                'crpr_location'             => $leadDetails->crle_location,
                'crpr_orgPincode'           => $leadDetails->crle_orgPincode,
                'crpr_orgCountry'           => $leadDetails->crle_orgCountry,
                'crpr_groute'               => $leadDetails->crle_groute,
                'crpr_glocality'            => $leadDetails->crle_glocality,
                'crpr_gplace'               => $leadDetails->crle_gplace,
                'glatitude'                 => $leadDetails->glatitude,
                'glongitude'                => $leadDetails->glongitude,
                'crpr_orgAddress'           => $leadDetails->crle_orgAddress,
                'crpr_orgAddress_2'         => $leadDetails->crle_orgAddress_2,
                'crpr_indContactperson'     => $leadDetails->crle_indContactperson,
                'crpr_indMobile'            => $leadDetails->crle_indMobile,
                'crpr_orgContactNo'         => $leadDetails->crle_orgContactNo,
                'retailCategory'            => $leadDetails->retailCategory,
                'crpr_orgEmail'             => $leadDetails->crle_orgEmail,
                'crpr_CreatedFrom'          => $leadDetails->crle_CreatedFrom,
                'crpr_CreatedBy'            => $leadDetails->crle_CreatedBy,
                'crpr_UpdatedBy'            => $leadDetails->crle_UpdatedBy,
                'crpr_isActive'             => $leadDetails->crle_isActive,
                'crmRemarks'                => $leadDetails->crmRemarks,
                'leadId'                    => $leadDetails->id,
                'assignedRO'                => $leadDetails->assignedRO,
                'crmuId'                    => 3,
                'baId'                      => $leadDetails->baId,
                'baName'                    => $leadDetails->baName,
                'areaId'                    => $leadDetails->areaId,
                'areaName'                  => $leadDetails->areaName,
                'crmRemarks'                => 'Converted to Prospect',
                'invitationCode'            => $code,
                'crpr_image'                => $leadDetails->crle_image,
                'retailCategory_isOthers'   => $leadDetails->retailCategory_isOthers
            ]);
            if($createProspect)
            {
                $contactUpdate = ROFinascopContacts::where([
                    ['id', $leadDetails->contactId]
                ])->update(['crmu_id'   => 3]);
                $leadUpdate = ROFinascopLeads::where([
                    ['id', $leadDetails->id]
                ])->update(['crmuId'   => 3]);
            }
            return $createProspect;
        }
    }
}