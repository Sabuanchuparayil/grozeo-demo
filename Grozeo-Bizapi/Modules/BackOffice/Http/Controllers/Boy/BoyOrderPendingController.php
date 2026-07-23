<?php

namespace BackOffice\Http\Controllers\Boy;

use App\Models\Order;
use App\Models\Client;
use App\Models\DeliveryInfo;
use App\Models\OrderAddress;
use BackOffice\Models\Branch;
use BackOffice\Models\B2bOrder;
use BackOffice\Models\BoyOrder;
use BackOffice\Models\GodownBoy;
use Illuminate\Support\Facades\DB;
use BackOffice\Models\TransferOrder;
use BackOffice\Models\BoyOrderRequest;
use BackOffice\Models\TransferRequest;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessWithData;
use BackOffice\Status\TransferOrderStatus;
use BackOffice\Status\BoyOrderRequestStatus;
use BackOffice\Http\Requests\BoyOrderPendingRequest;
use Illuminate\Http\Request;
use BackOffice\Models\FirebaseLog;

class BoyOrderPendingController
{
    public function __invoke(BoyOrderPendingRequest $request)
    {
        try
        {
            $summary = $this->getOrders(auth_user()->id, $request);
            return new SuccessWithData($summary);
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    public  function getOrderCount(){
        $branch = GodownBoy::select('branch_id')
        ->where('id', (auth_user()->id) )
        ->first();
        $count = TransferOrder::where('fsto_source',$branch->branch_id)      
        ->whereIn('fsto_ordertype', [0,1,2])
        ->where('fsto_iscancelunpacked',0)
        ->wherein('fsto_status',[TransferOrderStatus::TO_MANUALLY_ASSIGN,TransferOrderStatus::ASSIGNED_GODOWN_BOY,TransferOrderStatus::PACKED_NOT_BOXED])      
        ->count() ; 
         return new SuccessWithData( $count);
    }
    public  function getPolledOrder(request $request){
        $polledlog = FirebaseLog::select('rfir_payload')
            ->where('rfir_token', $request->token)
            ->where('rfir_StatusId', 1)
            ->whereRaw("TIMESTAMPDIFF(MINUTE, rfir_date, '" . date('Y-m-d H:i:s') . "') <= 3")
            ->latest()
            ->first();
        return new SuccessWithData(@$polledlog->rfir_payload);
    }
    private function getOrders($boy_id, $request)
    {
        $type = @$request->type ?? 0;

        $branch = GodownBoy::select('branch_id')
        ->where('id', $boy_id) 
        ->first();
        $boyOrder = TransferOrder::selectraw('fsto_status,fsto_isalreadypacked,fsto_id,fsto_uid,fstr_id,fsto_ordertype,DATE_FORMAT(fsto_createdOn,"%M %d, %Y, %H:%m% %p") as time,fsto_assigned_boy ')
        ->where('fsto_source',$branch->branch_id)      
        ->whereIn('fsto_ordertype', [0,1,2])
        ->where('fsto_iscancelunpacked',0);

        $fstoStatusListOrder = [TransferOrderStatus::TO_MANUALLY_ASSIGN,TransferOrderStatus::CANCELLED_AFTER_PACKING,TransferOrderStatus::PACKED_NOT_BOXED];
        $fstoStatusListInner = [TransferOrderStatus::PACKED_NOT_BOXED];
        if(@$request->filter)
        {
            $fstoStatusListOrder = [$request->filter];
            $fstoStatusListInner = [$request->filter];

        }
        $fstoStatus = $this->getStatusByType($type);
        if(!empty($fstoStatus) && $type > 0)
        {
            if($type == 7)
            {
                $boyOrder->where([
                    ['fsto_status', TransferOrderStatus::CANCELLED],
                    ['fsto_isalreadypacked', 1],
                    ['is_replenished', 0]
                ]);
            }
            else
            {
                $boyOrder->wherein('fsto_status', $fstoStatus);
            }
            if(in_array($type, [5]))
            {
                $boyOrder->where('fsto_assigned_boy',$boy_id);
            }
        }
        else
        {
            $boyOrder->where(function($sql) use ($boy_id, $fstoStatusListOrder, $fstoStatusListInner){
    			$sql->wherein('fsto_status', $fstoStatusListOrder)  
        		->orWhere(function($q) use ($boy_id, $fstoStatusListOrder, $fstoStatusListInner){
                    $q->where('fsto_assigned_boy',$boy_id);
                    $q->where('fsto_status', $fstoStatusListInner);
                });
    		});
        }
        $boyOrder->with('fstosStatus');
        $boyOrder->with('boy:id,name,lname');
        if(@$request->sort)
        {
            $boyOrder->orderBy('fsto_createdOn', $request->sort);
        }
        else
        {
            $boyOrder->orderBy('fsto_id', 'asc');
        }
        
        $boyOrderDetails =  $boyOrder->paginate(10);


        foreach($boyOrderDetails as $key => $value ){
                     
            $boyOrderDetails[$key]['order_pk_id']  = $value->fsto_id;
            $boyOrderDetails[$key]['order_id']  = $value->fsto_uid;
            $boyOrderDetails[$key]['time'] = $value->time;  
            $boyOrderDetails[$key]['type']  =$value->fsto_ordertype;
            $boyOrderDetails[$key]['branch']  =$branch->branch_id;
           
            $boyOrderDetails[$key]['laststatus'] = "";

            if($value->fsto_ordertype == '0'){
                $transReq = TransferRequest::select('fstr_destination','fstr_uid')->where('fstr_id', $value->fstr_id)->first();
                $boyOrderDetails[$key]['orgno']  = $transReq->fstr_uid;
                $branchdets = Branch::select('br_name','br_Phone')
                ->where('br_id', $transReq->fstr_destination)
                ->first();
                $boyOrderDetails[$key]['name']  = $branchdets->br_name;
                $boyOrderDetails[$key]['phone']  = $branchdets->br_Phone;
            }elseif($value->fsto_ordertype == '1'){
                $order = Order::where('order_id', $value->fstr_id)
                          ->select('order_order_id', 'order_method')
                          ->with('shipment:id,order_id,shipment_label,tracking_link')
                          ->first();
                $delinfo = OrderAddress::where('customer_order_id',$value->fstr_id)
                           ->select('order_customer_name','order_contact_no')
                           ->first();  
                $boyOrderDetails[$key]['orgno']  = $order->order_order_id; 
                $boyOrderDetails[$key]['isCourier']  = (@$order->order_method == 3) ? 1 : 0; 
                $boyOrderDetails[$key]['name']  = $delinfo->order_customer_name;
                $boyOrderDetails[$key]['phone']  = $delinfo->order_contact_no;
                $boyOrderDetails[$key]['shipment_label']  = @$order->shipment->shipment_label;
                $boyOrderDetails[$key]['tracking_link']  = @$order->shipment->tracking_link;
            }elseif($value->fsto_ordertype == '2'){
                $order = B2bOrder::where('bbso_id', $value->fstr_id)
                          ->select('b2b_Customer_ID')
                          ->first();
                $delinfo = Client::where('b2b_Customer_ID',$value->b2b_Customer_ID)
                           ->select('b2b_Customer_Name','b2b_Customer_Phone')
                           ->first();  
                $boyOrderDetails[$key]['orgno']  = $order->bbso_SONumber; 
                $boyOrderDetails[$key]['name']  = $delinfo->b2b_Customer_Name;
                $boyOrderDetails[$key]['phone']  = $delinfo->b2b_Customer_Phone; 
                $boyOrderDetails[$key]['shipment_label']  = "";
                $boyOrderDetails[$key]['tracking_link']  = "";
            }
            //$boyOrderSummary->push( $boyOrdervalue);
        }
        DB::table('retaline_godown_boy_pendingcheck')->insertGetId(
            ['boy_id' => $boy_id, 'rgbp_date' => now()->format('Y-m-d') , 'rgbp_time' => now(), 'rggp_recordsreturned' => count($boyOrderDetails)   ]
        );
        return $boyOrderDetails;
    }

    private function getStatusByType($type)
    {
        $returns = [];
        switch ($type)
        {
            case 1:
                $returns = [TransferOrderStatus::TO_MANUALLY_ASSIGN, TransferOrderStatus::GODOWN_BOY_POLLED, TransferOrderStatus::POLL_NO_RESPONSE, TransferOrderStatus::POLL_REJECTED, TransferOrderStatus::CREATED];
                break;
            case 2:
                $returns = [TransferOrderStatus::GODOWN_BOY_POLLED, TransferOrderStatus::POLL_NO_RESPONSE, TransferOrderStatus::POLL_REJECTED];
                break;
            case 3:
                $returns = [TransferOrderStatus::PICKER_APPROVED];
                break;
            case 4:
                $returns = [TransferOrderStatus::INCOMPLETE_ORDER];
                break;
            case 5:
                $returns = [TransferOrderStatus::COMPLETED];
                break;
            case 6:
                $returns = [TransferOrderStatus::CANCELLED];
                break;
            case 7:
                $returns = ['Rerack'];
                break;
            case 8:
                $returns = [TransferOrderStatus::ASSIGNED_GODOWN_BOY];
                break;
            case 9:
                $returns = [TransferOrderStatus::PACKED_NOT_BOXED];
                break;
        }
        return $returns;
    }

}
