<?php

namespace App\Schedulers\RelationOfficer;

use App\Models\ProcessLock;
use Illuminate\Support\Facades\DB;
use App\Models\RelationOfficer\ROUser;
use App\Models\RelationOfficer\ROFinascopLeads;
use App\Models\RelationOfficer\ROFinascopContacts;
use App\Http\Repositories\RelationOfficer\AreaFinderCheck;

class ContactToLead
{
    public function __invoke()
    {
        try
        {
            $contactDetails = ROFinascopContacts::where([
                ['crco_type', 1],
                ['crco_isActive', 1],
                ['crmu_id', 1],
                ['retailCategory_isOthers', '<>', 1]
            ])->get();
            if($contactDetails)
            {
                foreach ($contactDetails as $contact)
                {
                    if($contact->glatitude && $contact->glongitude)
                    {
                        $areaFinder = (new AreaFinderCheck)->areaCheckLatLong($contact->glatitude, $contact->glongitude);
                        if($areaFinder)
                        {
                            $areaLeads = DB::table('relationship_officer as ro')
                                ->select('ro.id', DB::raw('COUNT(fcl.id) as fclCount'))
                                ->leftJoin('finascop_crm_lead as fcl', 'fcl.assignedRO', '=', 'ro.id')
                                ->where('ro.roArea', $areaFinder->id)
                                ->orderBy('fclCount', 'ASC')
                                ->first();
                            if($areaLeads->id)
                            {
                                $selectedRO = ROUser::find($areaLeads->id);
                                $checkLead = ROFinascopLeads::where('contactId', $contact->id)->exists();
                                if(!$checkLead)
                                {
                                    $createLead = ROFinascopLeads::create([
                                        'crle_orgName'          => $contact->crco_orgName,
                                        'crle_mode'             => $contact->crco_mode,
                                        'crle_type'             => $contact->crco_type,
                                        'crle_description'      => $contact->crco_description,
                                        'crle_location'         => $contact->crco_location,
                                        'crle_orgPincode'       => $contact->crco_orgPincode,
                                        'crle_orgCountry'       => $contact->crco_orgCountry,
                                        'crle_groute'           => $contact->crco_groute,
                                        'crle_glocality'        => $contact->crco_glocality,
                                        'crle_gplace'           => $contact->crco_gplace,
                                        'glatitude'             => $contact->glatitude,
                                        'glongitude'            => $contact->glongitude,
                                        'crle_orgAddress'       => $contact->crco_orgAddress,
                                        'crle_indContactperson' => $contact->crco_indContactperson,
                                        'crle_indMobile'        => $contact->crco_indMobile,
                                        'crle_orgContactNo'     => $contact->crco_orgContactNo,
                                        'retailCategory'        => $contact->retailCategory,
                                        'crle_orgEmail'         => $contact->crco_orgEmail,
                                        'crle_CreatedFrom'      => $contact->crco_CreatedFrom,
                                        'crle_CreatedBy'        => $contact->crco_CreatedBy,
                                        'assignedRO'            => $selectedRO->id,
                                        'crle_UpdatedBy'        => $contact->crco_UpdatedBy,
                                        'crle_isActive'         => $contact->crco_isActive,
                                        'crmuId'                => 2,
                                        'contactId'             => $contact->id,
                                        'isLeadAreaAssigned'    => 1,
                                        'baId'                  => $selectedRO->ba->id,
                                        'baName'                => $selectedRO->ba->baName,
                                        'areaId'                => $areaFinder->id,
                                        'areaName'              => $selectedRO->areaEntries->areaName,
                                        'crle_image'            => $contact->crco_image
                                    ]);
                                    if($createLead)
                                    {
                                        $updateContact = ROFinascopContacts::where([
                                                ['id', $contact->id],
                                                ['crmu_id', '<', 2]
                                            ])->update(['crmu_id'   => 2]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            ProcessLock::updateColData("BizAPI_ContactToLead", 0);
        }
        catch (\Exception $e)
        {
            info("ContacttoLead ERROR => ".$e->getMessage());
            ProcessLock::updateColData("BizAPI_ContactToLead", 0);
        }
    }
}