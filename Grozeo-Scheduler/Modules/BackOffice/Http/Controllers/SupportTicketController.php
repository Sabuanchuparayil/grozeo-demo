<?php
namespace BackOffice\Http\Controllers;

use BackOffice\Models\SupportTicket\{
    SupportTicket,
    SupportTicketLog,
    SupportTicketNumbering
};
use BackOffice\Http\Requests\SupportTicket\{
    SupportTicketRequest,
    SupportTicketLogRequest
};
use Illuminate\Support\Facades\DB;
use App\Http\Responses\{
    SuccessWithData,
    SuccessResponse,
    ErrorResponse,
    ErrorWithData
};

class SupportTicketController
{
    public function __construct() {}

    public function createNewSupportTicket(SupportTicketRequest $request)
    {
        try
        {
            $create = SupportTicket::create([
                'ticketNumber'          => $this->createTicketID(),
                'ticketSupTypeId'       => $request->support_type,
                'ticketContactNo'       => $request->phone,
                'ticketContactName'     => $request->name,
                'ticketContactEmail'    => $request->email,
                'ticketTitle'           => $request->title,
                'ticketDescription'     => $request->description,
                'ticketStatus'          => @$request->support_unit ? 1 : 2,
                'ticketSuId'            => $request->support_unit,
                'createdFrom'           => $request->created_from,
                'createdBy'             => @$request->created_by ? $request->created_by : 0
            ]);
            if($create)
            {
                $details = new \StdClass();
                $details->ticket_id = $create->id;
                $details->created_by = $create->createdBy;
                $details->status = $create->ticketStatus;
                $details->remarks = $request->description;
                $details->file_name = @$request->file_name;
                $details->file_url = @$request->file_url;
                $log = $this->createSupportTicketLog($details);
                return new SuccessResponse("Support Ticket Created for Ticket #{$create->ticketNumber}.");
            }
            return new ErrorResponse('Some error occured.');
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    
    public function createPresignedURL()
    {
        try
        {
            $s3Details = DB::table('s3_bucket')->first();
            $s3Client = new \Aws\S3\S3Client([
                'region'        => $s3Details->region,
                'version'       => 'latest',
                'credentials'   => array(
                    'key'           => $s3Details->access_key,
                    'secret'        => $s3Details->secretkey,
                )
            ]);
            $cmd = $s3Client->getCommand('PutObject', [
                'Bucket'    => $s3Details->tobucket,
                'Key'       => 'support_tickets/'.md5('ymdHisu'),
                'ACL'       => 'public-read'
            ]);
            $request = $s3Client->createPresignedRequest($cmd, '+20 minutes');

            return new SuccessWithData([
                'url'       => (string) $request->getUri(),
                'expiry'    => '20 minutes'
            ]);
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }

    public function addSupportTicketLog(SupportTicketLogRequest $request)
    {
        try
        {
            $ticket = SupportTicket::where('ticketId', $request->ticket_id)->first();
            // $status = $ticket->ticketStatus;
            $request->status = $ticket->ticketStatus;
            $create = $this->createSupportTicketLog($request);
            if($create)
            {
                return new SuccessResponse("Log Added for Ticket #{$ticket->ticketNumber}.");
            }
            return new ErrorResponse('Some error occured.');
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }

    private function createTicketID()
    {
        $ticketNumbering = SupportTicketNumbering::create();
        $ticketNo = $ticketNumbering ? $ticketNumbering->id : 1;
        return 'GRST'.date('ymd').str_pad($ticketNo, 4, '0', STR_PAD_LEFT);
    }
    private function createSupportTicketLog($details)
    {
        $fileURL = strtok($details->file_url, '?');
        return SupportTicketLog::create([
            'ticketId'      => $details->ticket_id,
            'ticketStatus'  => $details->status,
            'ticketRemarks' => $details->remarks,
            'filename'      => $details->file_name,
            'filepath'      => $fileURL,
            'createdBy'     => $details->created_by,
        ]);
    }
}