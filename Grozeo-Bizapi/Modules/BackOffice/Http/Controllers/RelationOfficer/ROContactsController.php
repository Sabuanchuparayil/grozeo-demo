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
use BackOffice\Models\RelationOfficer\ROContactType;
use BackOffice\Models\RelationOfficer\ROCrmStatus;
use BackOffice\Models\RelationOfficer\BusinessCategory;
use BackOffice\Http\Requests\RelationOfficer\ROContactRequest;
use BackOffice\Http\Requests\RelationOfficer\ROContactRequestUpdate;
use BackOffice\Http\Repositories\RelationOfficer\AreaFinderCheck;
use BackOffice\Http\Repositories\RelationOfficer\AwsBucketPresigned;
use BackOffice\Http\Repositories\RelationOfficer\ROSurveyRepository;

class ROContactsController
{
    protected $user;
    public function __construct()
    {
    }

    public function getContactTypes()
    {
        $contactTypes = ROContactType::select('id', 'name')->where([['status', 1], ['isMerchant', 1]])->get();
        return new SuccessWithData($contactTypes);
    }
    public function getRetailCategories($type = '')
    {
        $where = [
            ['status', 1],
            ['store_group_id', 0]
        ];
        if($type == 'others')
        {
            $where[] = ['business_category_ingroup', 0];
        }
        else
        {
            $where[] = ['business_category_ingroup', 1];
        }
        $retailCategories = BusinessCategory::select('business_category_id as id', 'business_category_name as name')->where($where)->get();
        if($type != 'others')
        {
            $retailCategories->push(['id'   => -1, 'name'   => 'Others']);
        }
        return new SuccessWithData($retailCategories);
    }
    public function getCRMStatus()
    {
        $crmStatus = ROCrmStatus::select('crmu_id as id', 'crmu_name as name')->where('crmu_IsActive', 1)->get();
        return new SuccessWithData($crmStatus);
    }
    public function getAllContacts()
    {
        $roUser = auth_user();
        try
        {
            $select = DB::raw("
                (CASE 
                    WHEN ((SELECT `lead`.assignedRO FROM `finascop_crm_lead` AS `lead` WHERE `lead`.`contactId` = `finascop_crm_contact`.`id` LIMIT 1) = 1) THEN 'Assigned to me' 
                    WHEN ((SELECT `lead`.assignedRO FROM `finascop_crm_lead` AS `lead` WHERE `lead`.`contactId` = `finascop_crm_contact`.`id` LIMIT 1) <> 1) THEN 'Assigned' 
                    WHEN ((SELECT `lead`.assignedRO FROM `finascop_crm_lead` AS `lead` WHERE `lead`.`contactId` = `finascop_crm_contact`.`id` LIMIT 1) = 0) THEN 'Unassigned' 
                    ELSE 'Unassigned' 
                END) as status");
            $contactDetails = ROFinascopContacts::select('finascop_crm_contact.*', $select)->where([
                ['crco_CreatedBy', $roUser->id],
                // ['crco_type', '<>', 3]
            ])->whereIn('crmu_id', [0, 1, 2])
            ->with('retailCategory:business_category_id,business_category_name')
            ->with('contactType:id,name')
            ->groupBy('finascop_crm_contact.id');
            return new SuccessWithData($contactDetails->paginate(10));
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function addNewContact(ROContactRequest $request)
    {
        $roUser = auth_user();
        try
        {
            $image = '';
            if(@$request->image != '')
            {
                $image = strtok($request->image, '?');
            }
            $contactTypes = array_unique($request->contact_type);
            $createContact = 0;
            $createLeads = 0;
            foreach($contactTypes as $ctype)
            {
                $create = ROFinascopContacts::create([
                    'crco_orgName'              => $request->store_name,
                    'crco_mode'                 => ($request->address_status == 1) ? 3 : 4,
                    'crco_type'                 => $ctype,
                    'crco_location'             => $request->google_address,
                    'crco_orgPincode'           => @$request->post_code,
                    'crco_orgCountry'           => $request->country,
                    'crco_groute'               => @$request->route,
                    'crco_glocality'            => @$request->locality,
                    'crco_gplace'               => @$request->place,
                    'glatitude'                 => $request->latitude,
                    'glongitude'                => $request->longitude,
                    'crco_orgAddress'           => $request->address_1,
                    'crco_orgAddress_2'         => $request->address_2,
                    'crco_indContactperson'     => @$request->contact_person,
                    'crco_indMobile'            => @$request->phone,
                    'crco_orgContactNo'         => @$request->contact_number,
                    'retailCategory'            => $request->retailer_category,
                    'crco_orgEmail'             => @$request->email,
                    'crco_image'                => $image,
                    'crco_remarks'              => @$request->remarks,
                    'crco_CreatedFrom'          => 3,
                    'crco_CreatedBy'            => (@$roUser->id) ? $roUser->id : 0,
                    'crco_isActive'             => 1,
                    'crme_id'                   => 0,
                    'crmu_id'                   => 1,
                    'retailCategory_isOthers'   => $request->is_others
                ]);
                if($create)
                {
                    $createContact++;
                    $convertToLead = $this->convertToLead($create->id, $roUser);
                    if($convertToLead)
                        $createLeads++;
                }
            }
            if($createContact > 0)
            {
                return new SuccessResponse($createContact.' contact(s) created. '.$createLeads.' lead(s) created.');
            }
            return new ErrorResponse('Some error occured.');
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function viewSingleContact($contactID)
    {
        $roUser = auth_user();
        try
        {
            $select = DB::raw("
                (CASE 
                    WHEN ((SELECT `lead`.assignedRO FROM `finascop_crm_lead` AS `lead` WHERE `lead`.`contactId` = `finascop_crm_contact`.`id` LIMIT 1) = ".$roUser->id.") THEN 'Assigned to me' 
                    WHEN ((SELECT `lead`.assignedRO FROM `finascop_crm_lead` AS `lead` WHERE `lead`.`contactId` = `finascop_crm_contact`.`id` LIMIT 1) <> ".$roUser->id.") THEN 'Assigned' 
                    WHEN ((SELECT `lead`.assignedRO FROM `finascop_crm_lead` AS `lead` WHERE `lead`.`contactId` = `finascop_crm_contact`.`id` LIMIT 1) = 0) THEN 'Unassigned' 
                    ELSE 'Unassigned' 
                END) as status");
            $contactDetails = ROFinascopContacts::select('finascop_crm_contact.*', $select)->where([
                ['id', $contactID],
                ['crco_CreatedBy', $roUser->id],
                // ['crco_type', '<>', 3]
            ])->whereIn('crmu_id', [0, 1, 2])
            ->with('retailCategory:business_category_id,business_category_name')
            ->with('contactType:id,name')
            ->first();
            if($contactDetails)
            {
                $contactDetails->survey = (new ROSurveyRepository)->getSurveyDetails($contactID, 'contact', $roUser->id);
                return new SuccessWithData($contactDetails);
            }
            return new ErrorResponse('Contact not found assigned to this user.');
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function updateSelectedContact(ROContactRequestUpdate $request, $contactID)
    {
        $roUser = auth_user();
        try
        {
            $image = '';
            if(@$request->image != '')
            {
                $image = strtok($request->image, '?');
            }
            $status = (@$request->status_id) ? $request->status_id : 0;
            $update = [
                'crco_orgName'              => $request->store_name,
                'crco_location'             => $request->google_address,
                'crco_orgPincode'           => $request->post_code,
                'crco_orgCountry'           => $request->country,
                'crco_groute'               => @$request->route,
                'crco_glocality'            => @$request->locality,
                'crco_gplace'               => @$request->place,
                'glatitude'                 => $request->latitude,
                'glongitude'                => $request->longitude,
                'crco_orgAddress'           => $request->address_1,
                'crco_orgAddress_2'         => $request->address_2,
                'crco_indContactperson'     => $request->contact_person,
                'retailCategory'            => $request->retailer_category,
                'crco_remarks'              => @$request->remarks,
                'crco_CreatedBy'            => (@$roUser->id) ? $roUser->id : 0,
                'crme_id'                   => 0,
                'crmu_id'                   => $status,
                'retailCategory_isOthers'   => $request->is_others,
                'crco_indMobile'            => @$request->phone,
                'crco_orgEmail'             => @$request->email,
                'crco_orgContactNo'         => @$request->contact_number,
            ];
            if($image != '')
            {
                $update['crco_image'] = $image;
            }
            $updateContact = ROFinascopContacts::where([
                ['id', $contactID],
                ['crco_CreatedBy', $roUser->id]
            ])->whereIn('crmu_id', [0, 1])->update($update);
            if($updateContact)
            {
                if($status == 2)
                {
                    $createLead = $this->convertToLead($contactID, $roUser);
                    if($createLead)
                    {
                        return new SuccessResponse('Contact updated. Converted to Lead.');
                    }
                }
                return new SuccessResponse('Contact updated');
            }
            return new ErrorResponse('Unable to update this contact.');
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function updateContactStatus(Request $request, $contactID)
    {
        $request->validate([
            'status_id'    => 'required|exists:finascop_crm_status,crmu_id'
        ]);

        $roUser = auth_user();
        try
        {
            $updateContact = ROFinascopContacts::where([
                ['id', $contactID],
                ['crco_CreatedBy', $roUser->id]
            ])->whereNotIn('crmu_id', [2, 3])->update([
                'crmu_id'   => $request->status_id
            ]);
            if($updateContact)
            {
                if($request->status_id == 2)
                {
                    $createLead = $this->convertToLead($contactID, $roUser);
                    if($createLead)
                    {
                        return new SuccessResponse('Contact status updated. Converted to Lead.');
                    }
                }
                if($request->status_id == 10)
                {
                    return new SuccessResponse('Contact deleted.');
                }
                return new SuccessResponse('Contact status updated');
            }
            return new ErrorResponse('Unable to update this contact.');
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function getFilteredContacts(Request $request)
    {
        $roUser = auth_user();
        try
        {
            $select = ['*', DB::raw("
            (CASE 
                WHEN ((SELECT `lead`.assignedRO FROM `finascop_crm_lead` AS `lead` WHERE `lead`.`contactId` = `finascop_crm_contact`.`id` LIMIT 1) = 1) THEN 'Assigned to me' 
                WHEN ((SELECT `lead`.assignedRO FROM `finascop_crm_lead` AS `lead` WHERE `lead`.`contactId` = `finascop_crm_contact`.`id` LIMIT 1) <> 1) THEN 'Assigned' 
                WHEN ((SELECT `lead`.assignedRO FROM `finascop_crm_lead` AS `lead` WHERE `lead`.`contactId` = `finascop_crm_contact`.`id` LIMIT 1) = 0) THEN 'Unassigned' 
                ELSE 'Unassigned' 
            END) as status")];
            $contactsByLocation = ROFinascopContacts::query();
            if(@$request->contact_type)
            {
                $contact_type = is_array($request->contact_type) ? $request->contact_type : [$request->contact_type];
                $contactsByLocation = $contactsByLocation->whereIn('crco_type', $contact_type);
            }
            if(@$request->retailer_category)
            {
                $contactsByLocation = $contactsByLocation->where('retailCategory', $request->retailer_category);
            }
            if((@$request->latitude > 0) && (@$request->longitude > 0))
            {
                $lat = $request->latitude;
                $lng = $request->longitude;
                $select[] = DB::raw('calcDistance('.$lat.', '.$lng.', glatitude, glongitude) AS distance');
            }
            $contactsByLocation
                ->select($select)
                ->whereIn('crmu_id', [0, 1, 2])
                ->where([['crco_CreatedBy', $roUser->id], ['crco_type', '<>', 3]])
                ->with('retailCategory:business_category_id,business_category_name')
                ->with('contactType:id,name');
            if((@$request->latitude > 0) && (@$request->longitude > 0))
            {
                $contactsByLocation->orderBy('distance');
            }
            return new SuccessWithData($contactsByLocation->paginate(10));
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function getSearchedContacts(Request $request)
    {
        $roUser = auth_user();
        try
        {
            $contactsSearch = ROFinascopContacts::select('*', DB::raw("
            (CASE 
                WHEN ((SELECT `lead`.assignedRO FROM `finascop_crm_lead` AS `lead` WHERE `lead`.`contactId` = `finascop_crm_contact`.`id` LIMIT 1) = 1) THEN 'Assigned to me' 
                WHEN ((SELECT `lead`.assignedRO FROM `finascop_crm_lead` AS `lead` WHERE `lead`.`contactId` = `finascop_crm_contact`.`id` LIMIT 1) <> 1) THEN 'Assigned' 
                WHEN ((SELECT `lead`.assignedRO FROM `finascop_crm_lead` AS `lead` WHERE `lead`.`contactId` = `finascop_crm_contact`.`id` LIMIT 1) = 0) THEN 'Unassigned' 
                ELSE 'Unassigned' 
            END) as status"));
            if(@$request->store_name)
            {
                $contactsSearch->where('crco_orgName', 'like', '%'.$request->store_name.'%');
            }
            if(@$request->person_name)
            {
                $contactsSearch->where('crco_indContactperson', 'like', '%'.$request->person_name.'%');
            }
            if(@$request->phone)
            {
                $contactsSearch->where('crco_orgContactNo', 'like', '%'.$request->phone.'%');
            }
            if(@$request->location)
            {
                $contactsSearch->where('crco_location', 'like', '%'.$request->location.'%')
                    ->orWhere('crco_glocality', 'like', '%'.$request->location.'%')
                    ->orWhere('crco_gplace', 'like', '%'.$request->location.'%')
                    ->orWhere('crco_orgAddress', 'like', '%'.$request->location.'%');
            }
            $contactsSearch
                ->whereIn('crmu_id', [0, 1, 2])
                ->where([['crco_CreatedBy', $roUser->id], ['crco_type', '<>', 3]])
                ->with('retailCategory:business_category_id,business_category_name')
                ->with('contactType:id,name');
            return new SuccessWithData($contactsSearch->paginate(10));
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function createImageUploadLink()
    {
        try
        {
            $presignedLink = (new AwsBucketPresigned)->getBucketLink();
            if($presignedLink)
            {
                return new SuccessWithData([
                    'url'       => $presignedLink,
                    'expiry'    => '20 minutes'
                ]);
            }
            else
            {
                return new ErrorResponse('Unable to create a link');
            }
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }


    private function convertToLead($contactID, $roUser)
    {
        $contactDetails = ROFinascopContacts::where([
            ['id', '=', $contactID],
            ['retailCategory_isOthers', '<>', 1]
        ])->first();
        if($contactDetails)
        {
            $areaFinder = (new AreaFinderCheck)->areaCheckLatLong($contactDetails->glatitude, $contactDetails->glongitude);
            if($areaFinder)
            {
                if($areaFinder->id == $roUser->roArea)
                {
                    $createLead = ROFinascopLeads::create([
                        'crle_orgName'              => $contactDetails->crco_orgName,
                        'crle_mode'                 => $contactDetails->crco_mode,
                        'crle_type'                 => $contactDetails->crco_type,
                        'crle_description'          => $contactDetails->crco_description,
                        'crle_location'             => $contactDetails->crco_location,
                        'crle_orgPincode'           => $contactDetails->crco_orgPincode,
                        'crle_orgCountry'           => $contactDetails->crco_orgCountry,
                        'crle_groute'               => $contactDetails->crco_groute,
                        'crle_glocality'            => $contactDetails->crco_glocality,
                        'crle_gplace'               => $contactDetails->crco_gplace,
                        'glatitude'                 => $contactDetails->glatitude,
                        'glongitude'                => $contactDetails->glongitude,
                        'crle_orgAddress'           => $contactDetails->crco_orgAddress,
                        'crle_orgAddress_2'         => $contactDetails->crco_orgAddress_2,
                        'crle_indContactperson'     => $contactDetails->crco_indContactperson,
                        'crle_indMobile'            => $contactDetails->crco_indMobile,
                        'crle_orgContactNo'         => $contactDetails->crco_orgContactNo,
                        'retailCategory'            => $contactDetails->retailCategory,
                        'crle_orgEmail'             => $contactDetails->crco_orgEmail,
                        'crle_CreatedFrom'          => $contactDetails->crco_CreatedFrom,
                        'crle_CreatedBy'            => $contactDetails->crco_CreatedBy,
                        'assignedRO'                => $roUser->id,
                        'crle_UpdatedBy'            => $contactDetails->crco_UpdatedBy,
                        'crle_isActive'             => $contactDetails->crco_isActive,
                        'crmRemarks'                => $contactDetails->crco_remarks,
                        'crmuId'                    => 2,
                        'contactId'                 => $contactDetails->id,
                        'isLeadAreaAssigned'        => 1,
                        'baId'                      => $roUser->ba->id,
                        'baName'                    => $roUser->ba->baName,
                        'areaId'                    => $areaFinder->id,
                        'areaName'                  => $roUser->areaEntries->areaName,
                        'crle_image'                => $contactDetails->crco_image,
                        'retailCategory_isOthers'   => $contactDetails->retailCategory_isOthers
                    ]);
                    if($createLead)
                    {
                        $updateContact = ROFinascopContacts::where([
                                ['id', $contactDetails->id],
                                ['crmu_id', '<', 2]
                            ])->update(['crmu_id'   => 2]);
                    }
                    return $createLead;
                }
                else
                {
                    $areaLeads = DB::table('relationship_officer as ro')
                        ->select('ro.id', DB::raw('COUNT(fcl.id) as fclCount'))
                        ->leftJoin('finascop_crm_lead as fcl', 'fcl.assignedRO', '=', 'ro.id')
                        ->where('ro.roArea', $areaFinder->id)
                        ->orderBy('fclCount', 'ASC')->first();
                    if($areaLeads->id)
                    {
                        $selectedRO = ROUser::find($areaLeads->id);
                        $createLead = ROFinascopLeads::create([
                            'crle_orgName'          => $contactDetails->crco_orgName,
                            'crle_mode'             => $contactDetails->crco_mode,
                            'crle_type'             => $contactDetails->crco_type,
                            'crle_description'      => $contactDetails->crco_description,
                            'crle_location'         => $contactDetails->crco_location,
                            'crle_orgPincode'       => $contactDetails->crco_orgPincode,
                            'crle_orgCountry'       => $contactDetails->crco_orgCountry,
                            'crle_groute'           => $contactDetails->crco_groute,
                            'crle_glocality'        => $contactDetails->crco_glocality,
                            'crle_gplace'           => $contactDetails->crco_gplace,
                            'glatitude'             => $contactDetails->glatitude,
                            'glongitude'            => $contactDetails->glongitude,
                            'crle_orgAddress'       => $contactDetails->crco_orgAddress,
                            'crle_indContactperson' => $contactDetails->crco_indContactperson,
                            'crle_indMobile'        => $contactDetails->crco_indMobile,
                            'crle_orgContactNo'     => $contactDetails->crco_orgContactNo,
                            'retailCategory'        => $contactDetails->retailCategory,
                            'crle_orgEmail'         => $contactDetails->crco_orgEmail,
                            'crle_CreatedFrom'      => $contactDetails->crco_CreatedFrom,
                            'crle_CreatedBy'        => $contactDetails->crco_CreatedBy,
                            'assignedRO'            => $selectedRO->id,
                            'crle_UpdatedBy'        => $contactDetails->crco_UpdatedBy,
                            'crle_isActive'         => $contactDetails->crco_isActive,
                            'crmRemarks'            => $contactDetails->crco_remarks,
                            'crmuId'                => 2,
                            'contactId'             => $contactDetails->id,
                            'isLeadAreaAssigned'    => 1,
                            'baId'                  => $selectedRO->ba->id,
                            'baName'                => $selectedRO->ba->baName,
                            'areaId'                => $areaFinder->id,
                            'areaName'              => $selectedRO->areaEntries->areaName,
                            'crle_image'            => $contactDetails->crco_image
                        ]);
                        if($createLead)
                        {
                            $updateContact = ROFinascopContacts::where([
                                    ['id', $contactDetails->id],
                                    ['crmu_id', '<', 2]
                                ])->update(['crmu_id'   => 2]);
                        }
                        return $createLead;
                    }
                }
            }
        }
        return false;
    }
}