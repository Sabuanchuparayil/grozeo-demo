<?php
namespace BackOffice\Http\Controllers\RelationOfficer;

use App\Http\Responses\{
    ErrorResponse,
    SuccessResponse,
    SuccessWithData
};
use BackOffice\Models\{
    RelationOfficer\ROUser,
    RelationOfficer\ROFinascopLeads,
    RelationOfficer\ROFinascopContacts,
    RelationOfficer\ROFinascopProspects
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RODashboardController
{
    public function __construct()
    {
    }

    public function getCounts()
    {
        $roUser = auth_user();
        try
        {
            $contacts = ROFinascopContacts::where([
                ['crco_CreatedBy', $roUser->id],
                // ['crco_type', '<>', 3]
            ])->whereIn('crmu_id', [0, 1, 2])->count();
            $leads = ROFinascopLeads::where([
                ['assignedRO', $roUser->id],
                ['crmuId', 2],
                ['crle_type', 1]
            ])->count();
            $prospects = ROFinascopProspects::where([
                ['assignedRO', $roUser->id],
                ['crmuId', 3],
                ['storeGroupId', 0],
                ['crpr_type', 1]
            ])->count();
            $retaliers = ROFinascopProspects::where([
                ['assignedRO', $roUser->id],
                ['crmuId', 3],
                ['storeGroupId', '<>', 0],
                ['crpr_type', 1]
            ])->count();
            $merchants = DB::table('finascop_branch_group')
            ->leftJoin('finascop_crm_prospect', 'finascop_branch_group.prospect_Id', 'finascop_crm_prospect.id')
            ->where([
                ['finascop_crm_prospect.assignedRO', $roUser->id],
                ['finascop_crm_prospect.crmuId', 3],
                ['finascop_crm_prospect.storeGroupId', '<>', 0],
                ['finascop_crm_prospect.crpr_type', 1],
                ['finascop_branch_group.store_group_grosmartMerchant', 1]
            ])->count();
            return new SuccessWithData([
                'contacts'      => $contacts,
                'leads'         => $leads,
                'prospects'     => $prospects,
                'retaliers'     => $retaliers,
                'merchants'     => $merchants
            ]);
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
}