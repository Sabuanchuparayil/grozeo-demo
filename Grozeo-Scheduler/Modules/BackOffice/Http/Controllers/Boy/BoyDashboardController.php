<?php

namespace BackOffice\Http\Controllers\Boy;

use BackOffice\Models\{
    GodownBoy,
    TransferOrder
};
use BackOffice\Status\{
    TransferOrderStatus
};
use App\Http\Responses\{
    ErrorResponse,
    SuccessWithData
};
use Illuminate\Support\Facades\DB;

class BoyDashboardController
{
    public function __invoke()
    {
        try
        {
            $boyID = auth_user()->id;
            $storeGroup = getHeaderStoreGroup();
            $branchID = GodownBoy::where('id', $boyID)->value('branch_id');
            $boyOrder = TransferOrder::selectRaw($this->boySelectFields($boyID))
            ->where('fsto_source', $branchID)
            ->whereIn('fsto_ordertype', [0,1,2])
            ->where('fsto_iscancelunpacked', 0)
            ->get();

            $response = [
                "New"           => 0,
                "Polling"       => 0,
                "Cancelled"     => 0,
                "Completed"     => 0,
                "Processing"    => 0,
                "custFeedback"  => 0,
                "Rerack"        => 0,
                "Accepted"      => 0,
                "NotBoxed"      => 0,
                'recent_orders' => []
            ];

            foreach ($boyOrder as $bo)
            {
                $response["New"] += (($bo->status_type == "New") ? 1 : 0);
                $response["Polling"] += (($bo->status_type == "Polling") ? 1 : 0);
                $response["Cancelled"] += (($bo->status_type == "Cancelled") ? 1 : 0);
                $response["Completed"] += (($bo->status_type == "Completed") ? 1 : 0);
                $response["Processing"] += (($bo->status_type == "Processing") ? 1 : 0);
                $response["custFeedback"] += (($bo->status_type == "custFeedback") ? 1 : 0);
                $response["Accepted"] += (($bo->status_type == "Accepted") ? 1 : 0);
                $response["NotBoxed"] += (($bo->status_type == "NotBoxed") ? 1 : 0);
            }
            $response["Rerack"] = TransferOrder::where([
                ['fsto_source', $branchID],
                ['fsto_status', TransferOrderStatus::CANCELLED],
                ['fsto_isalreadypacked', 1],
                ['is_replenished', 0],
            ])
            ->whereIn('fsto_ordertype', [0,1,2])
            ->count();
            $recentStat = TransferOrderStatus::COMPLETED;
            $recentQuery = "SELECT {$this->recentSelectFields()} FROM finascop_stock_transfer_order fsto
                INNER JOIN retaline_customer rc ON rc.cust_id = fsto.fsto_destination
                INNER JOIN retaline_customer_order rco ON rco.order_id = fsto.fstr_id
                WHERE 
                    fsto_assigned_boy = {$boyID} AND
                    fsto_status = {$recentStat}
                ORDER BY fsto_updateon DESC
                LIMIT 10
            ";
            $response['recent_orders'] = DB::select($recentQuery);
            return new SuccessWithData($response); 
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    private function boySelectFields($boyID)
    {
        $created = TransferOrderStatus::TO_MANUALLY_ASSIGN.",".TransferOrderStatus::GODOWN_BOY_POLLED.",".TransferOrderStatus::POLL_NO_RESPONSE.",".TransferOrderStatus::POLL_REJECTED.",".TransferOrderStatus::CREATED;
        $polling = TransferOrderStatus::GODOWN_BOY_POLLED.",".TransferOrderStatus::POLL_NO_RESPONSE.",".TransferOrderStatus::POLL_REJECTED;
        $processing = TransferOrderStatus::PICKER_APPROVED;
        $custFeedback = TransferOrderStatus::INCOMPLETE_ORDER;
        $completed = TransferOrderStatus::COMPLETED;
        $cancelled = TransferOrderStatus::CANCELLED;
        $accepted = TransferOrderStatus::ASSIGNED_GODOWN_BOY;
        $notBoxed = TransferOrderStatus::PACKED_NOT_BOXED;
        return "
        (CASE
            WHEN fsto_status IN ({$created}) THEN 'New'
            WHEN fsto_status IN ({$polling}) THEN 'Polling'
            WHEN fsto_status IN ({$processing}) THEN 'Processing'
            WHEN fsto_status IN ({$accepted}) THEN 'Accepted'
            WHEN fsto_status IN ({$notBoxed}) THEN 'NotBoxed'
            WHEN fsto_status IN ({$custFeedback}) THEN 'custFeedback'
            WHEN fsto_status IN ({$completed}) AND fsto_assigned_boy = {$boyID} THEN 'Completed'
            #WHEN fsto_replenished_boy = {$boyID} AND is_replenished = 1 AND replenishedOn IS NOT NULL THEN 'Rerack'
            WHEN fsto_status IN ({$cancelled}) THEN 'Cancelled'
        END) as status_type,
        fsto_status
        ";
    }
    private function recentSelectFields()
    {
        return "
            fsto.fsto_id,
            fsto.fsto_uid,
            fsto.fstr_id,
            rco.order_order_id,
            fsto.fsto_source,
            fsto.fsto_status,
            fsto.fsto_destination,
            rc.cust_customer_name,
            (SELECT COUNT(item_id) FROM retaline_customer_order_items WHERE customer_order_id = fsto.fstr_id
            ) AS items,
            fsto_updateon as delivered_date
        ";
    }

}
