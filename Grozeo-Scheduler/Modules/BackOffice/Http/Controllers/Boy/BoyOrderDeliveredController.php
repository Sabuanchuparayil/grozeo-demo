<?php

namespace BackOffice\Http\Controllers\Boy;
use Carbon\Carbon;
use App\Models\Order;

use App\Models\Client;
use App\Models\DeliveryInfo;
use App\Models\OrderAddress;
use Illuminate\Http\Request;
use BackOffice\Models\Branch;
use BackOffice\Models\B2bOrder;
use BackOffice\Models\BoyOrder;
use BackOffice\Models\GodownBoy;
use BackOffice\Models\QugeoOrder;
use BackOffice\Status\QugeoStatus;
use Illuminate\Support\Facades\DB;
use BackOffice\Models\TransferOrder;
use App\Http\Responses\ErrorResponse;
use App\Models\MarginDistributionb2c;
use BackOffice\Models\BoyOrderRequest;
use BackOffice\Models\TransferRequest;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use BackOffice\Models\InventoryHistory;
use BackOffice\Status\TransferOrderStatus;
use BackOffice\Status\BoyOrderRequestStatus;
use BackOffice\Status\QugeoSourceOrderStatus;
use App\Http\Repositories\Finascop\StoreFinascop;
use BackOffice\Http\Requests\BoyOrderDeliveredRequest;
use Illuminate\Support\Facades\Log;
use App\Status\DelayedOrderActions;
use App\Events\DelayedOrderActions as DelayedOrderEvent;

class BoyOrderDeliveredController
{
    protected const CASH_ON_DELIVERY = 1;

    public function __invoke(BoyOrderDeliveredRequest $request)
    {
        $summary = $this->getOrders(auth_user()->id);
        return new SuccessWithData($summary);
    }

    private function getOrders($boy_id){
        
        $branch = GodownBoy::select('branch_id')
        ->where('id', $boy_id) 
        ->first();
        $boyOrder = QugeoOrder::selectraw('quor_id,quor_TransferOrder_Type,quor_ItemDetails,quor_RefNo,quor_AmountCollectible,DATE_FORMAT(quor_CreatedOn,"%d-%m-%y %H:%m%:%s") as time ')
        ->where('quor_Deliverybr_id',$branch->branch_id)      
        ->where('quor_DeliveryMethodsAllowed', 4)
       ->wherein('quor_Refno_Source',[1,2]) 
        ->where('quor_Type',3)
        ->where('quor_Status',22)
        ->orderBy('quor_TransferOrder_id', 'asc');
        $boyOrderDetails =  $boyOrder->paginate(10);
       
        foreach($boyOrderDetails as $key => $value ){
            $boyOrderDetails[$key]['quor_ItemDetails']  = json_decode($boyOrderDetails[$key]['quor_ItemDetails']);
            
            $boyOrderDetails[$key]['order_pk_id']  = $value->quor_id;
            $boyOrderDetails[$key]['order_id']  = $value->quor_RefNo;
            $boyOrderDetails[$key]['time'] = $value->time;  
            $boyOrderDetails[$key]['payment']  =($value->quor_AmountCollectible>0)?"Collect":"Paid";
            $boyOrderDetails[$key]['branch']  =$branch->branch_id;

            if($value->quor_TransferOrder_Type == '0'){
                $transReq = TransferRequest::select('fstr_destination','fstr_uid')->where('fstr_uid', $value->quor_RefNo)->first();
                $boyOrderDetails[$key]['orgno']  = $transReq->fstr_uid;
                $branchdets = Branch::select('br_name','br_Phone')
                ->where('br_id', $transReq->fstr_destination)
                ->first();
                $boyOrderDetails[$key]['name']  = $branchdets->br_name;
                $boyOrderDetails[$key]['phone']  = $branchdets->br_Phone;
            }elseif($value->quor_TransferOrder_Type == '1'){
                $order = Order::where('order_order_id', $value->quor_RefNo)
                          ->select('order_order_id','order_id')
                          ->first();
                $delinfo = OrderAddress::where('customer_order_id',$order->order_id)
                           ->select('order_customer_name','order_contact_no')
                           ->first();  
                $boyOrderDetails[$key]['orgno']  = $order->order_order_id; 
                $boyOrderDetails[$key]['name']  = $delinfo->order_customer_name;
                $boyOrderDetails[$key]['phone']  = $delinfo->order_contact_no;                         
            }elseif($value->quor_TransferOrder_Type == '2'){
                $order = B2bOrder::where('bbso_SONumber', $value->quor_RefNo)
                          ->select('b2b_Customer_ID')
                          ->first();
                $delinfo = Client::where('b2b_Customer_ID',$order->b2b_Customer_ID)
                           ->select('b2b_Customer_Name','b2b_Customer_Phone')
                           ->first();  
                $boyOrderDetails[$key]['orgno']  = $order->bbso_SONumber; 
                $boyOrderDetails[$key]['name']  = $delinfo->b2b_Customer_Name;
                $boyOrderDetails[$key]['phone']  = $delinfo->b2b_Customer_Phone; 
            }
                    
            
            //$boyOrderSummary->push( $boyOrdervalue);
        }
       
        return $boyOrderDetails;
    }

    public function verifyotp( request $request){
       
        $validatedData = $request->validate([
            'order_id' => 'required',
            'otp' => 'required',
            'quor_AmountCollectible'=>'nullable',
            'ondel_payment_mode'=>'nullable'

        ]);
        $resp = DB::transaction(function () use ( $request){
        $order=Order::where("order_order_id",$request->order_id)->where("order_customerpickup_otp",$request->otp)->first();
        if($order){
            $boyOrder = QugeoOrder::selectraw('quor_Status')
            ->where('quor_RefNo',$request->order_id)->first();      
            if($boyOrder->quor_Status != 22){
                return new SuccessResponse('Reload the delivery orders, this order has been updated.');
            }
            if($order->payment_mode==static::CASH_ON_DELIVERY){
                if(isset($request->quor_AmountCollectible) && ($request->quor_AmountCollectible >=$order->order_amount_payable )){
                    $this->updatequgeoStatus($request->order_id,$request->ondel_payment_mode);
                    StoreFinascop::paidcustomercollectbooking('16-SalesB2COnlineCustomerPickupPayonDelivery',$order->order_customer_id, $order->order_id); 
                   // $datas = StoreFinascop::getmargindistrinution($order->order_id, $order->order_customer_id);
                   // StoreFinascop::margindistribution('16a-MarginDistributionProcessQueue', $datas['order'], $datas['ho'], $datas['company'], $datas['cs'], $datas['distributor'], $datas['retailor'], $datas['incentive'], $datas['deliverycharge']);
                    $data  = new SuccessResponse('Delivery confirmed.');
                    return  $data;
                }else{
                    return new ErrorResponse('Please collect the total amount as order is pay on delivery');
                }
            }else{
                $this->updatequgeoStatus($request->order_id,$request->ondel_payment_mode);
            }
             return new SuccessResponse('OTP is verified.');
        }else{
            return new ErrorResponse('Otp is not verified');
        }
    });
     return $resp;
    }    

    private function getmargindistrinution($order_id,$customer_id) {
        //order - order_id, order_order_id, total
        //HOpayable - amt, referenceid 10
        //Company - amt, referenceid (total *10/100)
        //$cs  - amt, referenceid
        //$distri - amt, referenceid
        //$retail - amt, referenceid
        //$incen  - amt, referenceid
        //$delchrg - amt, referenceid
        $order = Order::where('order_id', $order_id)
                ->where('order_customer_id', $customer_id)
                ->first();
        $amount = DB::table('retaline_customer_order_items')
                ->selectraw(' SUM((item_retail_price-item_sales_price)*item_order_qty) as total')                       
                ->where('customer_order_id', $order->order_id)
                ->first() ;                
        $marginDistributions = MarginDistributionb2c::where('is_default', 1)
                ->first();
        $retailer = Branch::where('br_ID', $order->order_branch_id)
                ->first();
        $distributor = Branch::where('br_ID', $retailer->br_cpd)
                ->first();
        $centralStore = Branch::where('br_ID', $distributor->br_cpd)
                ->first();
        $cpd = Branch::where('br_ID', $centralStore->br_cpd)
                ->first();

        $data['ho'] = new \stdClass();
        $data['company'] = new \stdClass();
        $data['order'] = new \stdClass();
        $data['cs'] = new \stdClass();
        $data['distributor'] = new \stdClass();
        $data['retailor'] = new \stdClass();
        $data['incentive'] = new \stdClass();
        $data['deliverycharge'] = new \stdClass();

        //$data['ho']->amt = $amount->total;
        $data['ho']->br_ReferenceID = $cpd->br_ReferenceID;
        $data['company']->amt = round(($amount->total * $marginDistributions->bmd_company) / 100,2);
        $data['company']->br_ReferenceID = $cpd->br_ReferenceID;
        $data['order']->order_id = $order->order_id;
        $data['order']->order_order_id = $order->order_order_id;
        $data['order']->total = $amount->total;
        $data['order']->order_confirm_date = $order->order_confirm_date;
        $data['order']->order_branch_id = $order->order_branch_id;
        $data['cs']->amt = round(($amount->total * $marginDistributions->bmd_hub) / 100,2);
        $data['cs']->br_ReferenceID = $centralStore->br_ReferenceID;
        $data['distributor']->amt = round(($amount->total * $marginDistributions->bmd_distributor) / 100,2);
        $data['distributor']->br_ReferenceID = $distributor->br_ReferenceID;
        $data['retailor']->amt = round(($amount->total * $marginDistributions->bmd_retailor) / 100,2);
        $data['retailor']->br_ReferenceID = $retailer->br_ReferenceID;
        $data['incentive']->amt = round(($amount->total * $marginDistributions->bmd_incentive) / 100,2);
        $data['incentive']->br_ReferenceID = $retailer->br_ReferenceID;
        $data['deliverycharge']->amt = round(($amount->total * $marginDistributions->bmd_driver) / 100,2);
        $data['deliverycharge']->br_ReferenceID = $retailer->br_ReferenceID;

        $data['ho']->amt = $data['deliverycharge']->amt + $data['incentive']->amt + $data['retailor']->amt + $data['distributor']->amt + $data['cs']->amt + $data['company']->amt;
        $data['ho']->amt = round($data['ho']->amt,2);

        return $data;
        // return array('order' => array('order_id' => $order->order_id, 'order_order_id' => $order->order_id, 'total' => $amount->total), 'company' => array('amt' => 0, 'br_ReferenceID' => 'sdfsd'));
    }

    public function  updatequgeoStatus($order_id,$ondel_payment_mode){
        $boyOrder = QugeoOrder::selectraw('quor_id,quor_RefNo,quor_StatusUpdateQry,quor_TrackingHistory,quor_TrackingUpdateQry,quor_TransferOrder_id')
        ->where('quor_RefNo',$order_id)->first();      
        QugeoOrder::where('quor_id',$boyOrder->quor_id)->update(
            [
                'quor_Status'=>QugeoStatus::ORDER_DELIVERY_COMPLETED_DLS_ID,
                "quor_DeliveredTime"=>  now(),

            ]
            );

        $updateurl=$boyOrder->quor_StatusUpdateQry;
        //$updateurl = $db->getItemFromDb("select quor_StatusUpdateQry from qugeo_order where quor_id = " . $nors['quor_id'],true);	
        $updateurl = QugeoOrder::getQugeoParentStatusUpdated($updateurl,QugeoStatus::ORDER_DELIVERY_COMPLETED_DLS_ID);
        $updateurl = str_replace("###2","",$updateurl);
        $updateurl = str_replace("###6",(intval($ondel_payment_mode)==1?7:6),$updateurl);
        $updateurl = str_replace("###7",(intval($ondel_payment_mode)==1?"":$ondel_payment_mode),$updateurl);
        $execQry = explode(";",$updateurl);
      //  DB::statement($sql);


        if(trim($execQry[0]) != "" ){
            DB::statement(trim($execQry[0]));
        }
        if(trim($execQry[1]) != "" ){
            DB::statement(trim($execQry[1]));
        }
        $orderno = Order::where('order_order_id',$order_id)
        ->select('order_id')    
        ->first();
        event(new DelayedOrderEvent($orderno->order_id, 7));
        //lDB::statement("DELETE FROM finascop_stock_blocked WHERE order_id = {$orderno->order_id}");


            
            $is_retalineLite = DB::table('sys_configuration')
            ->selectraw('cfg_Value')                       
            ->where('cfg_Name', 'IS_RETALINE_LITE')
            ->first() ;  
            if ($is_retalineLite->cfg_Value != 1) {
                $barcodes = DB::table('finascop_stock_transfer_order_details_barcodes')
                ->selectraw('stiid_barcode')                       
                ->where('fsto_id', $boyOrder->quor_TransferOrder_id)
                ->get() ;  

           

            foreach ($barcodes as $barcode) {

                    //Update the status of the barcode in the finascop_stock_item_inventorydetails
                    DB::statement('update finascop_stock_item_inventorydetails set stiid_status = 6 where stiid_barcode = ' . $barcode->stiid_barcode);
                    $stiiid = DB::table('finascop_stock_item_inventorydetails')
                    ->select('stii_id','stiid_itemmasterid')                       
                    ->where('stiid_barcode', $barcode->stiid_barcode)
                    ->first() ; 
                    InventoryHistory::create([
                        'stiid_id' => $stiiid->stii_id,
                        'stiidm_itemmasterid' => $stiiid->stiid_itemmasterid,
                        'stiidm_barcode' => $barcode->stiid_barcode,
                        'created_at' => now(),
                        'stiidm_details' =>  'Delivered item in the Delivery order ' . $order_id
                    ]);
                            
            }
            }

            //UPdate Return
			//$qry = "select coalesce(quor_ItemReturned,'') as ss   from qugeo_order where quor_id = " . $quor_id;
            //$return_items = $db->getItemFromDB($qry, true);
			
           // $updateurl = $db->getItemFromDb("select quor_ItemReturnUpdate from qugeo_order where quor_id = " . $quor_id, true);
           // $updateurl = str_replace("##13", $return_items, $updateurl);
            //$db->query($updateurl);
            // $db->query('update retaline_customer_order set status_id = ' . QUGEO_TO_CUSTOMER_ORDER_STATUS_DELIVERD_CONFIRM . ' where order_id = ' . $orderid);
            //$db->query("INSERT INTO retaline_customer_order_history(order_id, order_status, created_at, updated_at) VALUES(" . $orderid . ", " . QUGEO_TO_CUSTOMER_ORDER_STATUS_DELIVERD_CONFIRM . ", NOW(), NOW())");
        

    }

}
