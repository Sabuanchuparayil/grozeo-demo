<?php

namespace BackOffice\Http\Controllers\Boy;

use BackOffice\Models\BoyOrder;
use BackOffice\Status\BoyOrderStatus;
use BackOffice\Models\BoyOrderRequest;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use BackOffice\Status\BoyOrderRequestStatus;
use BackOffice\Http\Requests\BoyOrderSummaryRequest;
use Illuminate\Support\Facades\DB;

class BoyOrderSummaryController
{
    public function __invoke(BoyOrderSummaryRequest $request)
    {
        $summary = $this->getOrders($request->orderdate,auth_user()->id,$request->showonlypickedorders);
        return new SuccessWithData($summary);
    }

    private function getOrders($orderdate,$boy_id,$OnlyCompletedOrders){
        $from = $orderdate ." 00:00:00";
        $to = $orderdate ." 23:59:59";

        $boyOrderRequests = BoyOrderRequest::selectraw('id,order_id,order_pk_id,status,DATE_FORMAT(created_at,"%H:%m%:%s") as time ')
        ->whereBetween('created_at', [$from, $to])
        ->where('boy_id', $boy_id)      
        ->orderBy('id', 'desc')
        ->get();

        $boyOrderSummary =collect([]);
        foreach($boyOrderRequests as $key => $value ){
            $boyOrdervalue =array();
            if($OnlyCompletedOrders ==1 && $value->status != BoyOrderRequestStatus::ACCEPTED){
                continue;
            }
            $toDetails = DB::select("SELECT fstr_id,fsto_ordertype FROM finascop_stock_transfer_order WHERE fsto_id = {$value->order_pk_id}");
            switch($toDetails[0]->fsto_ordertype){
                case 0:
                    $parentOrder = DB::select("SELECT fstr_uid as orderId FROM finascop_stock_transfer_request WHERE fstr_id = {$toDetails[0]->fstr_id}");
                    break;
                case 1:   
                    $parentOrder = DB::select("SELECT order_order_id as orderId FROM retaline_customer_order WHERE order_id = {$toDetails[0]->fstr_id}");                 
                    break;
                case 2:  
                    $parentOrder = "";                  
                    break;
                case 3:    
                    $parentOrder = "";                
                    break;
                


            }
            $boyOrdervalue['order_pk_id']  = $value->order_pk_id;
            $boyOrdervalue['order_id']  = $value->order_id;
            $boyOrdervalue['order_order_id']  = $parentOrder[0]->orderId;
            if($value->status == BoyOrderRequestStatus::ACCEPTED){
                $boyorder = BoyOrder::selectraw('status,DATE_FORMAT(updated_at,"%H:%m%:%s") as time')
                ->where('bgor_id',$value->id)
                ->first();
                if($OnlyCompletedOrders ==1 && $value->status != BoyOrderStatus::ACCEPTED){
                    continue;
                }
                $boyOrdervalue['time'] = $boyorder->time;
                $boyOrdervalue['status'] = BoyOrder::getStatusDescription($boyorder->status);
            }else{
                $boyOrdervalue['time'] = $value->time;
                $boyOrdervalue['status'] = BoyOrderRequest::getStatusDescription($value->status);
            }
            $boyOrderSummary->push( $boyOrdervalue);
        }
        return $boyOrderSummary;
    }

}
