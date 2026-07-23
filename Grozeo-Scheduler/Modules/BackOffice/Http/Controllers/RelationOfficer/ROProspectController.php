<?php

namespace BackOffice\Http\Controllers\RelationOfficer;

use Carbon\Carbon;
use App\Sms\SmsSender;
use Illuminate\Http\Request;
use App\Helpers\HttpCurlCalls;
use Illuminate\Support\Facades\DB;
use BackOffice\Models\BranchGroup;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use BackOffice\Models\RelationOfficer\ROUser;
use BackOffice\Models\RelationOfficer\ROFinascopContacts;
use BackOffice\Models\RelationOfficer\ROFinascopLeads;
use BackOffice\Models\RelationOfficer\ROFinascopProspects;
use BackOffice\Http\Repositories\RelationOfficer\ROSurveyRepository;

class ROProspectController
{
    public function __construct()
    {
    }
    public function getAllProspects()
    {
        $roUser = auth_user();
        try
        {
            $prospectDetails = ROFinascopProspects::where([
                ['assignedRO', $roUser->id],
                ['crmuId', 3],
                ['crpr_type', 1],
                ['storeGroupId', 0]
            ])
            ->with('retailCategory:business_category_id,business_category_name')
            ->with('contactType:id,name')
            ->paginate(10);
            return new SuccessWithData($prospectDetails);
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function viewSingleProspect($prID)
    {
        $roUser = auth_user();
        try
        {
            $prospectDetails = ROFinascopProspects::where([
                ['id', $prID],
                ['assignedRO', $roUser->id],
                ['crmuId', 3],
                ['crpr_type', 1],
                ['storeGroupId', 0]
            ])
            ->with('retailCategory:business_category_id,business_category_name')
            ->with('contactType:id,name')
            ->first();
            if($prospectDetails)
            {
                $prospectDetails->survey = (new ROSurveyRepository)->getSurveyDetails($prID, 'prospect', $roUser->id);
                return new SuccessWithData($prospectDetails);
            }
            return new ErrorResponse('Prospect not found assigned to this user.');
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function getSearchedProspects(Request $request)
    {
        $roUser = auth_user();
        try
        {
            $prospectSearch = ROFinascopProspects::select('*');
            if(@$request->store_name)
            {
                $prospectSearch->where('crpr_orgName', 'like', '%'.$request->store_name.'%');
            }
            if(@$request->person_name)
            {
                $prospectSearch->where('crpr_indContactperson', 'like', '%'.$request->person_name.'%');
            }
            if(@$request->phone)
            {
                $prospectSearch->where('crpr_orgContactNo', 'like', '%'.$request->phone.'%');
            }
            if(@$request->location)
            {
                $prospectSearch->where('crpr_location', 'like', '%'.$request->location.'%')
                    ->orWhere('crpr_glocality', 'like', '%'.$request->location.'%')
                    ->orWhere('crpr_gplace', 'like', '%'.$request->location.'%')
                    ->orWhere('crpr_orgAddress', 'like', '%'.$request->location.'%');
            }
            $prospectSearch
                ->where([['assignedRO', $roUser->id], ['crmuId', 3], ['crpr_type', 1],['storeGroupId', 0]])
                ->with('retailCategory:business_category_id,business_category_name')
                ->with('contactType:id,name');
            return new SuccessWithData($prospectSearch->paginate(10));
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function getFilteredProspects(Request $request)
    {
        $roUser = auth_user();
        try
        {
            $select = ['*'];
            $prospectsByLocation = ROFinascopProspects::query();
            if(@$request->contact_type)
            {
                $contact_type = is_array($request->contact_type) ? $request->contact_type : [$request->contact_type];
                $prospectsByLocation = $prospectsByLocation->whereIn('crpr_type', $contact_type);
            }
            if(@$request->retailer_category)
            {
                $prospectsByLocation = $prospectsByLocation->where('retailCategory', $request->retailer_category);
            }
            if((@$request->latitude > 0) && (@$request->longitude > 0))
            {
                $lat = $request->latitude;
                $lng = $request->longitude;
                $select[] = DB::raw('calcDistance('.$lat.', '.$lng.', glatitude, glongitude) AS distance');
            }
            $prospectsByLocation
                ->select($select)
                ->where([['assignedRO', $roUser->id], ['crmuId', 3], ['crpr_type', 1],
                ['storeGroupId', 0]])
                ->with('retailCategory:business_category_id,business_category_name')
                ->with('contactType:id,name');
            if((@$request->latitude > 0) && (@$request->longitude > 0))
            {
                $prospectsByLocation->orderBy('distance');
            }
            return new SuccessWithData($prospectsByLocation->paginate(10));
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function sendProspectInvitation($prID)
    {
        $roUser = auth_user();
        try
        {
            $prospectDetails = ROFinascopProspects::select('id', 'crpr_indContactperson', 'crpr_orgEmail', 'invitationCode', 'invitationLink', 'invitationSent', 'crpr_indMobile')
            ->where([
                ['id', $prID],
                ['assignedRO', $roUser->id],
                ['crmuId', 3],
                ['storeGroupId', 0]
            ])->first();
            if($prospectDetails)
            {
                $url = $prospectDetails->invitationLink;
                if($prospectDetails->invitationSent != 1)
                {
                    $response = $this->createNewInvitationLink($prospectDetails);
                    if(@$response['status'] == true)
                    {
                        return new SuccessWithData([
                            'msg'   => $response['msg'],
                            'url'   => $response['url']
                        ]);
                    }
                    return new ErrorResponse('Unable to send invitation.');
                }
                $smsResponse = $this->sendInvitationLinkSMS($prospectDetails->crpr_indMobile, $prospectDetails->invitationCode);

                ROFinascopProspects::where('id', $prID)->update([
                    'invitationCreated' => date('Y-m-d H:i:s')
                ]);
                return new SuccessWithData([
                    'msg'   => "Email Invitation already sent. {$smsResponse}",
                    'url'   => $prospectDetails->invitationLink
                ]);
            }
            return new ErrorResponse('Prospect not found assigned to this user.');
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public function approveProspectInvitation($prID, Request $request)
    {
        $roUser = auth_user();
        try
        {
            $prospectDetails = ROFinascopProspects::select('id', 'crpr_indContactperson', 'crpr_orgEmail', 'invitationCode', 'invitationLink', 'invitationSent', 'crpr_indMobile', 'crpr_CreatedOn', 'invitationCreated')
            ->where([
                ['id', $prID],
                ['assignedRO', $roUser->id],
                ['crmuId', 3],
                ['storeGroupId', 0]
            ])->first();
            if($prospectDetails)
            {
                if($prospectDetails->invitationCode == $request->code)
                {
                    if($prospectDetails->invitationCreated != "")
                    {
                        $startDate = new Carbon($prospectDetails->invitationCreated);
                        $checkDate = Carbon::now();
                        $difference = (int)$startDate->diff($checkDate)->format('%I');
                        if($difference < 30)
                        {
                            ROFinascopProspects::where('id', $prID)->update([
                                'storeGroupId' => $request->storegroupid
                            ]);
                            BranchGroup::where('store_group_id', $request->storegroupid)->update([
                                'prospect_Id' => $prID
                            ]);
                            return new SuccessResponse("Prospect linked with store.");
                        }
                        return new ErrorResponse('Invitation link expired. Please send it again.');
                    }
                    return new ErrorResponse('Invitation link not sent to this prospect.');
                }
                return new ErrorResponse('Provided Invitation code is invalid for this prospect.');
            }
            return new ErrorResponse('Prospect not found assigned to this user.');
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }

    private function createNewInvitationLink($prospectDetails)
    {
        $data = [
            'code'      => $prospectDetails->invitationCode,
            'fullname'  => $prospectDetails->crpr_indContactperson,
            'email'     => $prospectDetails->crpr_orgEmail
        ];
        $invitationLink = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name= 'PROSPECT_INVITATION' ");
        $url = $invitationLink[0]->cfg_Value;
        $response = (new HttpCurlCalls)->curlCall($url, json_encode($data), 'POST', ['Content-Type: application/json']);

        if($response->status == 'Success')
        {
            $updateProspect = ROFinascopProspects::where([
                ['id', $prospectDetails->id],
                ['crmuId', 3]
            ])->update([
                'invitationSent'    => 1,
                'invitationLink'    => $response->url,
                'invitationCreated' => date('Y-m-d H:i:s')
            ]);

            $smsResponse = $this->sendInvitationLinkSMS($prospectDetails->crpr_indMobile, $prospectDetails->invitationCode);
            $msg = "Email Invitation Sent. {$smsResponse}";
            if($updateProspect)
            {
                return [
                    'status'    => true,
                    'msg'       => $msg,
                    'url'       => $response->url
                ];
            }
            else
            {
                return [
                    'status'    => true,
                    'msg'       => $msg.'Unable to update.',
                    'url'       => $response->url
                ];
            }
        }
        else
        return false;
    }
    private function sendInvitationLinkSMS($mobile, $code)
    {
        if($mobile != NULL)
        {
            $templatedata['code'] = $code;
            app(SmsSender::class)->fetchContentSendSms($templatedata, $mobile, 24);
            return "SMS Invitation sent. ";
        }
        return "Unable to Send SMS. Phone number not found. ";
    }
}