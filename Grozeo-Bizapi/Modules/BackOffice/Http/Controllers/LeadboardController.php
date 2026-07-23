<?php

namespace BackOffice\Http\Controllers;

use Response;
use Illuminate\Http\Request;
use App\Modules\CustomerPickupOtp;
use App\Models\{
    Branch,
    Order,
    Customer,
    OrderHistory,
    CustomerOrderStatus
};
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\OrderHistoryRepository;
use App\Http\Controllers\OrderCompleteController;
use App\Http\Responses\SuccessWithData;

use App\Sms\SmsSender;

class LeadboardController
{
    protected $leadTable;
    protected $signUpURL;

    public function __construct()
    {
        $this->leadTable = DB::table('lead_request');
        $this->signUpURL = 'https://test.grozeo.in';
    }
    
    public function getBusinessAssociateByLatLong(Request $request)
    {
        $data = [
            'status'    => 'error',
            'data'      => [],
            'message'   => 'Latitude and Longitude are required.'
        ];
        if((@$request->latitude != '') && (@$request->longitude != ''))
        {
            $selectors = DB::raw('
                ba.id as baId,
                ae.id as areaId,
                ae.areaName,
                ae.areaLocation,
                ae.areaSpan,
                ba.baName,
                ba.baAddress,
                ba.baCity,
                ba.baPincode,
                ba.baContactPerson,
                ba.baMobileNo,
                ba.baEmail,
                (6371 * acos(
                    cos( radians(ae.areaLatitude) ) 
                    * cos(radians('.$request->latitude.')) 
                    * cos(radians('.$request->longitude.') - radians(ae.areaLongitude)) 
                    + sin(radians(ae.areaLatitude) ) 
                    * sin(radians('.$request->latitude.') )
                )) as distance
            ');
            $queryData = DB::table('area_entries as ae')
                ->select($selectors)
                ->join(
                    'business_associate as ba',
                    'ba.id',
                    '=',
                    'ae.areaBusinessAssociate'
                )
                ->orderBy('distance', 'ASC')
                ->first();
            if(@$queryData)
            {
                unset($queryData->distance);
            }
            $data = [
                'status'    => 'success',
                'data'      => $queryData,
                'message'   => ''
            ];
        }
        return new SuccessWithData($data);
    }
    public function createNewLead(Request $request)
    {
        $data = [
            'status'    => 'failed',
            'data'      => [],
            'message'   => 'Unable to create lead'
        ];
        $validatedData = $request->validate([
            'leadName'      => 'required',
            'leadEmail'     => 'required|email:rfc,dns',
            'leadMobileNo'  => 'required'
        ]);
        $refNo = strtoupper(substr(md5($request->lead_email.$request->lead_phone.date('d M Y H:i:s')), 0, 10));
        $createLead = $this->leadTable->insert([
            'leadName'          => @$request->leadName,
            'leadAddress'       => @$request->leadAddress,
            'leadCity'          => @$request->leadCity,
            'leadPincode'       => @$request->leadPincode,
            'leadContactPerson' => @$request->leadContactPerson,
            'leadMobileNo'      => @$request->leadMobileNo,
            'leadEmail'         => @$request->leadEmail,
            'leadPanNo'         => @$request->leadPanNo,
            'leadSignUpURL'     => $this->signUpURL.'/'.$refNo,
            'leadReferenceId'   => $refNo
        ]);
        if($createLead)
        {
            /*$templateData = [
                'signUpURL'     => $this->signUpURL.'/'.$refNo,
                'referenceNo'   => $refNo
            ];
            SmsSender::fetchContentSendSms($templateData, $request->leadMobileNo, 6);*/
            $data = [
                'status'    => 'success',
                'data'      => [
                    'signUpURL'     => $this->signUpURL.'/'.$refNo,
                    'referenceNo'   => $refNo
                ],
                'message'   => ''
            ];
        }
        return new SuccessWithData($data);
    }
}