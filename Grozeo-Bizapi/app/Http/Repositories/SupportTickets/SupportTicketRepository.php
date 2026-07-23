<?php
namespace App\Http\Repositories\SupportTickets;

use App\Models\{
    SupportTickets\SupportUnit,
    SupportTickets\SupportType,
    SupportTickets\SupportTicket,
    SupportTickets\SupportTicketLog,
    SupportTickets\SupportTicketNumbering
};
use App\Http\Responses\{
    ErrorResponse,
    SuccessResponse,
    SuccessWithData
};
use Illuminate\Support\Facades\DB;

class SupportTicketRepository
{
    public function __construct()
    {
    }

    public function supportUnitList($typeID)
    {
        $unitDetails = SupportUnit::select('id', 'name', 'description')
        ->where('status', 1)
        ->whereIn("id", [DB::raw("SELECT `unitId` FROM `support_type_unit` WHERE `typeId`={$typeID}")])
        ->get();
        return $unitDetails;
    }
    public function createPresignedURL($extension)
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
        $uuid = DB::select('SELECT UUID() as uuid')[0]->uuid;
        $cmd = $s3Client->getCommand('PutObject', [
            'Bucket'    => $s3Details->tobucket,
            'Key'       => "support_tickets/{$uuid}.{$extension}",
            'ACL'       => 'public-read'
        ]);
        $request = $s3Client->createPresignedRequest($cmd, '+20 minutes');

        return [
            'url'       => (string) $request->getUri(),
            'expiry'    => '20 minutes'
        ];
    }
    public function submitSupportTicket($details)
    {
        $create = SupportTicket::create([
            'ticketNumber'          => $this->createTicketID(),
            'ticketSupTypeId'       => $details['type'],
            'ticketContactNo'       => $details['phone'],
            'ticketContactName'     => $details['name'],
            'ticketContactEmail'    => $details['email'],
            'ticketTitle'           => $details['title'],
            'ticketDescription'     => $details['description'],
            'ticketStatus'          => 1,
            'ticketStage'           => 1,
            'ticketSuId'            => $details['unit'],
            'createdFrom'           => $details['createdFrom'],
            'createdBy'             => @$details['createdBy'],
            'createdFor'            => @$details['createdFor']
        ]);
        if($create)
        {
            $addLog = [
                "ticket_id"     => $create->ticketId,
                "created_by"    => $create->createdBy,
                "status"        => $create->ticketStatus,
                "remarks"       => $create->ticketDescription,
                "stage"         => $create->ticketStage,
                "support_unit"  => $details['unit'],
                "file_name"     => @$details['fileName'],
                "file_url"      => @$details['file'],
            ];
            $log = $this->createSupportTicketLog($addLog);
            return new SuccessResponse("Support Ticket Created for Ticket #{$create->ticketNumber}.");
        }
        return new ErrorResponse('Some error occured.');
    }
    public function supportTicketListing($condition)
    {
        $tickets = SupportTicket::select('ticketId', 'ticketNumber', 'ticketSupTypeId', 'ticketContactNo', 'ticketContactName', 'ticketContactEmail', 'ticketTitle', 'ticketDescription', 'ticketStage', 'ticketStatus', 'ticketSuId', 'isAssigned', 'createdFrom', 'createdBy', 'createdFor', 'createdOn')
        ->where($condition)
        ->with([
            'logs',
            'logs.supportUnit:id,name,description',
            'logs.supportStatus',
            'logs.supportStage',
            'supportUnit:id,name,description',
            'supportType:typeId,typeName',
            'supportStage',
            'supportStatus'
        ])->paginate(10);
        return $tickets;
    }



    private function createTicketID()
    {
        $ticketNumbering = SupportTicketNumbering::create();
        $ticketNo = $ticketNumbering ? $ticketNumbering->id : 1;
        return 'GRST'.date('ymd').str_pad($ticketNo, 4, '0', STR_PAD_LEFT);
    }
    private function createSupportTicketLog($details)
    {
        return SupportTicketLog::create([
            "ticketId"          => $details["ticket_id"],
            "ticketStatus"      => $details["status"],
            "ticketRemarks"     => $details["remarks"],
            "ticketSupportUnit" => $details['support_unit'],
            "ticketStage"       => $details['stage'],
            "filename"          => @$details["file_name"],
            "filepath"          => @$details["file_url"],
            "createdBy"         => $details["created_by"],
        ]);
    }
}