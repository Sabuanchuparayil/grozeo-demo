<?php

namespace App\Traits;

use Carbon\carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Client;
use App\Models\OrderAddress;
use App\Models\Branch;
use App\Models\B2bOrder;
use Illuminate\Support\Facades\Log;
use App\CloudFcmNotification;
use App\Models\ReturnPacking;
use App\Models\TransferOrder;
use App\Models\TransferRequest;
use App\Models\SalesOrder;

trait CanSendNotificationsToBoy
{
    /**
     * Send Order request notification to boy
     *
     * @param string $orderId
     * @param string $fcmId
     * @param int $type 0 - Cpd Order, 1 - Customer order
     * @param string $boyOrderId
     * @param bool $isRevoked
     * @return void
     */
    private function getAddinfo($id){
        try
        {
            $value=TransferOrder::selectraw('fsto_id,fsto_uid,fstr_id,fsto_ordertype,fsto_isalreadypacked,fsto_pickingNumber')      
            ->where('fsto_id',$id)      
            ->first();
            $boyOrdervalue =array();
            $boyOrdervalue['type']  =$value->fsto_ordertype;  
            $boyOrdervalue['alreadypacked']  =$value->fsto_isalreadypacked;
            $boyOrdervalue['pickingNumber']  =$value->fsto_pickingNumber;      
            if($value->fsto_ordertype == '0'){
                $transReq = TransferRequest::select('fstr_destination','fstr_uid')->where('fstr_id', $value->fstr_id)->first();
                $boyOrdervalue['orgno']  = $transReq->fstr_uid;
                $branchdets = Branch::select('br_name','br_Phone')
                ->where('br_id', $transReq->fstr_destination)
                ->first();
                $boyOrdervalue['payment']  = ''  ; 
                $boyOrdervalue['delcharge']  = 0  ; 
                $boyOrdervalue['name']  = $branchdets->br_name;
                $boyOrdervalue['phone']  = $branchdets->br_Phone;
                $boyOrdervalue['amount']  = 0;   
                $boyOrdervalue['isinvoice']  = 0;
                $boyOrdervalue['SONumber']  ='';
            }elseif($value->fsto_ordertype == '1'){           
                $order = Order::where('order_id', $value->fstr_id)
                          ->select('order_order_id','payment_mode','order_delivery_charge','total','order_branch_id')
                          ->first();
                $salesOrder = SalesOrder::where('customer_order_id', $value->fstr_id)
                          ->select('SONumber','id')
                          ->first();
                $delinfo = OrderAddress::where('customer_order_id',$value->fstr_id)
                           ->select('order_customer_name','order_contact_no')
                           ->first();  
                $branchdets = Branch::select('br_name','br_Phone','br_ownInvoice')
                ->where('br_id', $order->order_branch_id)
                ->first();
                $orderItems = OrderItem::where('customer_order_id', $value->fstr_id)->pluck('is_restaurant')->toArray();
                $itemRestCount = array_count_values($orderItems);
                $restCount = (@$itemRestCount[1]) ? $itemRestCount[1] : 0;
                $hasInvoice = (count($orderItems) == $restCount) ? "0" : "1";
                $PaidStatus = "";
                if($order->payment_mode>0){                       
                    $PaidStatus = (($order->payment_mode==1 || $order->payment_mode==4)? 'UNPAID' : 'PAID');              
                }         
                $boyOrdervalue['payment']  = $PaidStatus ; 
                $boyOrdervalue['delcharge']  = $order->order_delivery_charge   ; 
                $boyOrdervalue['orgno']  = $order->order_order_id  ; 
                $boyOrdervalue['name']  = $delinfo->order_customer_name  ;
                $boyOrdervalue['phone']  = $delinfo->order_contact_no;    
                $boyOrdervalue['amount']  = $order->total;                     
                $boyOrdervalue['isinvoice']  = $hasInvoice;
                $boyOrdervalue['SONumber']  = $salesOrder->SONumber;
            }elseif($value->fsto_ordertype == '2'){
                $order = B2bOrder::where('bbso_id', $value->fstr_id)
                          ->select('b2b_Customer_ID','bbso_SONumber','bbso_HandlingCharges','bbso_InvValAtax')
                          ->first();
                $delinfo = Client::where('b2b_Customer_ID',$order->b2b_Customer_ID)
                           ->select('b2b_Customer_Name','b2b_Customer_Phone')
                           ->first();  
                $boyOrdervalue['payment']  =  'Credit'   ; 
                $boyOrdervalue['delcharge']  = $order->bbso_HandlingCharges   ; 
                $boyOrdervalue['orgno']  = $order->bbso_SONumber; 
                $boyOrdervalue['name']  = $delinfo->b2b_Customer_Name;
                $boyOrdervalue['phone']  = $delinfo->b2b_Customer_Phone; 
                $boyOrdervalue['amount']  = $order->bbso_InvValAtax;   
                $boyOrdervalue['isinvoice']  = 0;
                $boyOrdervalue['SONumber']  ='';
            }elseif($value->fsto_ordertype == '3'){
                $order = ReturnPacking::where('frrp_id', $value->fstr_id)
                          ->select('frrp_source','frrp_uid')
                          ->first();
                $branchdets = Branch::select('br_name','br_Phone')
                          ->where('br_id', $order->frrp_source)
                          ->first();
                $boyOrdervalue['payment']  =  ' '   ; 
                $boyOrdervalue['delcharge']  = 0   ; 
                $boyOrdervalue['orgno']  = $order->frrp_uid; 
                $boyOrdervalue['name']  = $branchdets->br_name;
                $boyOrdervalue['phone']  = $branchdets->br_Phone; 
                $boyOrdervalue['amount']  = 0;   
                $boyOrdervalue['isinvoice']  = 0;
                $boyOrdervalue['SONumber']  ='';
            }
            return $boyOrdervalue;
        }catch (\Exception $e)
        {
            info("CanSendNotificationsToBoy ERROR => ".$e->getMessage());
        }
        
    }
    public function sendNotificationToBoy($orderId, $fcmId, int $orderReqId = -1, $boyOrderId = -1, $isRevoked = false,$order_pk_id, $forcelogout=0,$isReplenish = false)
    {
        if (empty($fcmId)) {
            return;
        }

        $message = $isRevoked 
            ? 'Order Revoked'
            : 'New order request received';
        $message = $isReplenish?'Replenish Order':$message;
        if($forcelogout != 1){
            $addinfo = $this->getAddinfo($order_pk_id,$orderId);
        }else{
             $addinfo['orgno'] = "";
             $addinfo['name'] = "";
             $addinfo['phone']  = "";
             $addinfo['type'] = "";
             $addinfo['alreadypacked'] = "";
             $addinfo['amount'] = 0;
             $addinfo['payment'] = "";
             $addinfo['delcharge'] = "";
        }
       
        $newNotification = new CloudFcmNotification();
        $response = $newNotification
            ->setTimeToLive(60)
            ->setAnalyticalLabel($orderId)
            ->setBody($message)
            ->setTitle(config('siteinfo.app_client_project_name') . " Order Picker")
            ->setSound('notfctn2.caf')
            ->setData([
                'message' => $message,
                'orderId' => $orderId,             
                'boyOrderId' => $boyOrderId,
                'orderReqId' => $orderReqId,
                'isRevoked' => $isRevoked ? 'true' : 'false',
                'isReplenish' => $isReplenish ? 'true' : 'false',
                'order_pk_id' => $order_pk_id,
                'isLogout' => $forcelogout,
                'no_item_barcode' =>(env('NO_ITEM_BARCODE',false)?'true':'false'),
                'is_package' =>(env('IS_PACKAGE',false)?'true':'false'),
                'orgno'  => $addinfo['orgno'],
                'name'  => $addinfo['name'],
                'phone'  => $addinfo['phone'] ,
                'type'  => $addinfo['type'] ,
                'amount' =>  $addinfo['amount'] ,
                'payment' =>  $addinfo['payment'] ,
                'delcharge' =>  $addinfo['delcharge'] ,                
                'is_invoice' =>  $addinfo['isinvoice'] ,
                'SONumber' =>  $addinfo['SONumber'] ,
                'pickingNo' =>  $addinfo['pickingNumber'] ,
            ])
            ->to($fcmId)
            ->send();

    }
    public function sendNotificationToDriver($time, $phone, $body, $title, $data, $fcmID)
    {
        if (empty($fcmID))
        {
            return;
        }
        $newNotification = new CloudFcmNotification();
        $response = $newNotification
            ->setTimeToLive($time)
            ->setAnalyticalLabel($phone)
            ->setBody($body)
            ->setTitle($title)
            ->setSound('notfctn2.caf')
            ->setData($data)
            ->to($fcmID)
            ->sendDrive();
        return $response;

    }
}
