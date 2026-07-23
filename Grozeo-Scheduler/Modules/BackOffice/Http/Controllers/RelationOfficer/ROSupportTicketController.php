<?php

namespace BackOffice\Http\Controllers\RelationOfficer;

use App\Http\Responses\{
    ErrorResponse,
    SuccessResponse,
    SuccessWithData
};
use Illuminate\Support\Facades\DB;
use BackOffice\Models\RelationOfficer\ROFinascopProspects;
use App\Http\Requests\SupportTickets\SupportTicketRequest;
use App\Http\Repositories\SupportTickets\SupportTicketRepository;

class ROSupportTicketController
{
    public function __construct()
    {
        $this->stRepo = new SupportTicketRepository;
    }

    public function supportUnitList($type = 1)
    {
        try
        {
            switch ($type)
            {
                case 1:
                    $units = $this->stRepo->supportUnitList(config('support_ticket.roType'));
                    return new SuccessWithData($units);
                    break;
                case 2:
                    $units = $this->stRepo->supportUnitList(config('support_ticket.retailerType'));
                    return new SuccessWithData($units);
                    break;
                default:
                    return new ErrorResponse('Operation Failed');
                    break;
            }
        }
        catch (\Exception $e)
        {
            // info("supportUnitList Error");info($e);
            return new ErrorResponse("Operation Failed. {$e->getMessage()}"); 
        }
    }
    public function createPresignedURL($extension = "jpg")
    {
        try
        {
            $response = $this->stRepo->createPresignedURL($extension);
            return new SuccessWithData($response);
        }
        catch (\Exception $e)
        {
            // info("createPresignedURL Error");info($e);
            return new ErrorResponse("Operation Failed. {$e->getMessage()}"); 
        }
    }
    public function submitSupportTicket(SupportTicketRequest $request)
    {
        try
        {
            $fileURL = "";
            $fileName = "";
            if(@$request->file != "")
            {
                $fileURL = strtok(@$request->file, '?');
                $fileName = basename($fileURL);
            }
            $roUser = auth_user();
            $details = [
                'name'          => $roUser->roName,
                'phone'         => $roUser->roMobile,
                'email'         => $roUser->roEmailId,
                'title'         => @$request->title,
                'description'   => @$request->description,
                'unit'          => @$request->unit,
                'createdBy'     => $roUser->id,
                'createdFrom'   => 4,
                'createdFor'    => 0,
                'file'          => $fileURL,
                'fileName'      => $fileName,
                'type'          => config('support_ticket.roType'),
            ];
            if(@$request->retailer_id > 0)
            {
                $retalier = ROFinascopProspects::where('id', $request->retailer_id)->with('storegroup:store_group_id,store_group_name,contactNumber')->first();
                if($retalier)
                {
                    $details['name'] = @$retalier->storegroup->store_group_name;
                    $details['phone'] = @$retalier->storegroup->contactNumber;
                    $details['email'] = @$retalier->crpr_orgEmail;
                    $details['createdFor'] = @$retalier->storegroup->store_group_id;
                    $details['type'] = config('support_ticket.retailerType');
                }
                else
                {
                    return new ErrorResponse("Retailer not found");
                }
            }
            $ticket = $this->stRepo->submitSupportTicket($details);
            return $ticket;
        }
        catch (\Exception $e)
        {
            // info("submitSupportTicket Error");info($e);
            return new ErrorResponse("Operation Failed. {$e->getMessage()}"); 
        }
    }
    public function supportTicketListing($type = -1)
    {
        try
        {
            $roUser = auth_user();
            $condition = [
                ["ticketSupTypeId", config('support_ticket.roType')],
                ["createdFrom", 4],
                ["createdBy", $roUser->id],
                ["createdFor", 0],
            ];
            if($type > 0)
            {
                $condition = [
                    ["ticketSupTypeId", config('support_ticket.retailerType')],
                    ["createdFrom", 4],
                    ["createdFor", $type],
                ];
            }
            $tickets = $this->stRepo->supportTicketListing($condition);
            return new SuccessWithData($tickets);

        }
        catch (\Exception $e)
        {
            // info("supportTicketListing Error");info($e);
            return new ErrorResponse("Operation Failed. {$e->getMessage()}"); 
        }
    }
}
