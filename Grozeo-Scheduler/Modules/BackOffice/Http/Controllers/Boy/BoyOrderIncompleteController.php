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
use App\Http\Responses\SuccessWithData;
use BackOffice\Status\TransferOrderStatus;
use BackOffice\Status\BoyOrderRequestStatus;
use BackOffice\Http\Requests\BoyOrderPendingRequest;

class BoyOrderIncompleteController
{
    public function __invoke(BoyOrderPendingRequest $request)
    {
        $summary = $this->getOrders(auth_user()->id);
        return new SuccessWithData($summary);
    }
    public  function getOrderCount(){
        $branch = GodownBoy::select('branch_id')
        ->where('id', (auth_user()->id) )
        ->first();
        $count = TransferOrder::where('fsto_source',$branch->branch_id)      
        ->whereIn('fsto_ordertype', [0,1,2])
        ->where('fsto_iscancelunpacked',0)
        ->wherein('fsto_status',[TransferOrderStatus::INCOMPLETE_ORDER])      
        ->count() ; 
         return new SuccessWithData( $count);
    }
    private function getOrders($boy_id){
        
        $branch = GodownBoy::select('branch_id')
        ->where('id', $boy_id) 
        ->first();
        $boyOrder = TransferOrder::selectraw('fsto_status,fsto_isalreadypacked,fsto_id,fsto_uid,fstr_id,fsto_ordertype,DATE_FORMAT(fsto_createdOn,"%d-%m-%y %H:%m%:%s") as time ')
        ->where('fsto_source',$branch->branch_id)      
        ->whereIn('fsto_ordertype', [0,1,2])
        ->where('fsto_isalreadypacked',1)
        ->wherein('fsto_status',[TransferOrderStatus::INCOMPLETE_ORDER])      
        ->orderBy('fsto_id', 'asc');
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
                          ->select('order_order_id')
                          ->first();
                $delinfo = OrderAddress::where('customer_order_id',$value->fstr_id)
                           ->select('order_customer_name','order_contact_no')
                           ->first();  
                $boyOrderDetails[$key]['orgno']  = $order->order_order_id; 
                $boyOrderDetails[$key]['name']  = $delinfo->order_customer_name;
                $boyOrderDetails[$key]['phone']  = $delinfo->order_contact_no;                         
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
            }
            //$boyOrderSummary->push( $boyOrdervalue);
        }
        DB::table('retaline_godown_boy_pendingcheck')->insertGetId(
            ['boy_id' => $boy_id, 'rgbp_date' => now()->format('Y-m-d') , 'rgbp_time' => now(), 'rggp_recordsreturned' => count($boyOrderDetails)   ]
        );
        return $boyOrderDetails;
    }

}
