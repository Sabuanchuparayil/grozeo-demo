<?php

namespace BackOffice\Http\Services;

use stdClass;
use App\Models\Order;
use App\Models\Client;
use App\Models\Customer;
use App\Models\OrderItem;
use BackOffice\Models\Branch;
use BackOffice\Models\B2bOrder;
use BackOffice\Status\QugeoType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use BackOffice\Models\ReturnPacking;
use BackOffice\Models\TransferOrder;
use BackOffice\Status\B2bOrderStatus;
use BackOffice\Models\TransferRequest;
use BackOffice\Status\DeliveryMethods;
use BackOffice\Status\CustomerOrderStatus;
use BackOffice\Models\TransferOrderDetails;
use BackOffice\Actions\Inventory\B2BQugeoPush;
use BackOffice\Actions\Inventory\B2CQugeoPush;
use BackOffice\Actions\Inventory\QugeoProcessor;
use BackOffice\Actions\Inventory\CPD2BRQugeoPush;
use BackOffice\Actions\Inventory\RETPACKQugeoPush;
use BackOffice\Actions\Inventory\DistributionQugeoPush;
use BackOffice\Models\DistributionChart;

class TransferOrderToQugeo
{
    protected const CPD_ORDER = 0;

    protected const CUSTOMER_ORDER = 1;

    protected const B2B_ORDER = 2;

    protected const STOCK_RETURN = 3;

    protected const BRANCH_DISTRIBUTION = 4;

    private function getDeliveryType($transferordertype, $possibledeliverymethods, &$nodelivery){
        $nodelivery = false;
        if($possibledeliverymethods == DeliveryMethods::CUSTOMER_PICKUP){       
            return  3;
        }elseif($possibledeliverymethods == DeliveryMethods::COURIER){
            return 0 ;
        }else{
            if($transferordertype === static::CUSTOMER_ORDER){
               return QugeoType::DRIVE ;
            }else{
                return 0;
            }
        }
    }
    public function createQugeoOrder($transferOrder,$type,$number_bags,$invoiceamt)
    {
        $qugeoDetails = new stdClass();
        $branch = new Branch;
        $qugeoProcessor = new QugeoProcessor;

        //check if transfer order exists
        $checkTransfer = $qugeoProcessor->checkTransferOrderExists($transferOrder);
        if($checkTransfer)
        {
            $qugeoDetails->quor_Refno_Source =$type;
            $qugeoDetails->quor_TransferOrder_id=$transferOrder->fsto_id;
            $qugeoDetails->quor_DeliveryMethodsAllowed=$transferOrder->fsto_DeliveryMethodsAllowedPossible;
            $qugeoDetails->quor_Type  = $this->getDeliveryType($type,$transferOrder->fsto_DeliveryMethodsAllowedPossible,$nodelivery);
          /* if($nodelivery === true){
                $packlist =[];
                $packlist[] = "No Delivery";
                return $packlist;
            }*/

            if($type === static::CPD_ORDER){                   
                $transferrequests = new TransferRequest;
                $transferrequest = $transferrequests->find($transferOrder->fstr_id); 
                $source =  $branch->find($transferOrder->fsto_source);
                $destination =  $branch->find($transferOrder->fsto_destination);
                $qugeoDetails = $this->getTransferRequestQugeoDetails($qugeoDetails,$source,$destination,$transferrequest,$qugeoProcessor,$transferOrder->fsto_id);            
           }else{
               if($type === static::CUSTOMER_ORDER){          
                $orders =new Order;
                $order = $orders->find($transferOrder->fstr_id,['order_order_id','order_packedbags_count','order_branch_id','order_id','payment_mode','order_amount_payable','order_slot_id','order_slot_date']); 
                $source =  $branch->find($transferOrder->fsto_source);
                $destination = $order->deliveryAddress; 
                $qugeoDetails = $this->getB2CQugeoDetails($qugeoDetails,$source,$destination,$order,$qugeoProcessor,$transferOrder->fsto_id,$invoiceamt);
               }elseif($type === static::B2B_ORDER){         
                $orders =new B2bOrder;           
                $order = $orders->find($transferOrder->fstr_id); 
                $source =  $branch->find($transferOrder->fsto_source);
                $b2bclient = new Client;           
                $client = $b2bclient->find($transferOrder->fsto_destination); 
                $qugeoDetails = $this->getB2BQugeoDetails($qugeoDetails,$source,$client,$order,$qugeoProcessor,$transferOrder->fsto_id,$invoiceamt);
               }elseif($type === static::STOCK_RETURN){          
                $orders =new ReturnPacking;           
                $order = $orders->find($transferOrder->fstr_id); 
                $source =  $branch->find($transferOrder->fsto_source);
                $destination =  $branch->find($transferOrder->fsto_destination);
                $qugeoDetails = $this->getReturnPackingQugeoDetails($qugeoDetails,$source,$destination,$order,$qugeoProcessor,$transferOrder->fsto_id);            
               }else if($type === static::BRANCH_DISTRIBUTION){                   
                $transferrequests = new DistributionChart;
                $transferrequest = $transferrequests->find($transferOrder->fstr_id); 
                $source =  $branch->find($transferOrder->fsto_source);
                $destination =  $branch->find($transferOrder->fsto_destination);
                $qugeoDetails = $this->getDistributionChartQugeoDetails($qugeoDetails,$source,$destination,$transferrequest,$qugeoProcessor,$transferOrder->fsto_id);            
               }       
           } 
            $qugeoDetails->quor_PacketCount = $number_bags;
            $packlist = $qugeoProcessor->save($qugeoDetails);
            return $packlist;
        }
        return [];
        
    }

    protected function getTransferRequestQugeoDetails($qugeo,$source,$destination,$transfer,$qugeoProcessor,$transferorder_id)
    {
        $cpd2brqugeopush = new CPD2BRQugeoPush();
        $qugeo->quor_TransferOrder_Type = static::CPD_ORDER;
        $openingtime = now()->addSeconds(config('app.qugeo_opening_time'));
        $qugeo->quor_RefNo = $transfer->fstr_uid; //*
        $qugeo->quor_PacketCount = 1;
        $qugeo->quor_ScheduleOpeningTime = $openingtime;  //now()->addSeconds(config('app.qugeo_opening_time'))
        $qugeo->quor_PickupToBeManual =1;
        $qugeo->quor_Pickupbr_id = $source->br_ID;
        $qugeo->quor_PickupName = $source->br_Incharge;
        $qugeo->quor_PickupAddress = $source->br_Address;
        $qugeo->quor_PickupLocation = $source->br_Name ?? '';
        $qugeo->quor_PickupPincode = $source->br_pincode;
        $qugeo->quor_PickupPhone = $source->br_Phone;
        $qugeo->quor_PickupLat = $source->br_Lat;
        $qugeo->quor_PickupLng = $source->br_Lng;
        $qugeo->quor_PickupStage1Distance = config('app.qugeo_stage1_time');
        $qugeo->quor_PickupStage2Distance = config('app.qugeo_stage2_time');
        $qugeo->quor_Deliverybr_id = $destination->br_ID;
        $qugeo->quor_DeliveryName = $destination->br_Incharge;
        $qugeo->quor_DeliveryAddress = $destination->br_Address;
        $qugeo->quor_DeliveryLocation = $destination->br_Name ?? '';
        $qugeo->quor_DeliveryPincode = $destination->br_pincode;
        $qugeo->quor_DeliveryPhone = $destination->br_Phone;
        $qugeo->quor_DeliveryLat = $destination->br_Lat;
        $qugeo->quor_DeliveryLng = $destination->br_Lng;
        $qugeo->quor_DeliverySMS = mt_rand(1000, 9999);
        $qugeo->quor_DeliveryStage1Distance = config('app.qugeo_stage1_time');
        $qugeo->quor_DeliveryStage2Distance = config('app.qugeo_stage2_time');
        $qugeo->quor_DistanceinKM = $qugeoProcessor->getDistance($source->br_Lat, $source->br_Lng, $destination->br_Lat, $destination->br_Lng);
        $qugeo->quor_StatusUpdateQry = $cpd2brqugeopush->getCPD2BRStatusUpdateSQL($transfer->fstr_id);
        $qugeo->quor_TrackingUpdateQry = $cpd2brqugeopush->getCPD2BRTrackUrlSQL($transfer->fstr_id);
        $qugeo->quor_CreatedOn = now()->format('Y-m-d H:i:s');
        $qugeo->quor_UpdateOn = now()->format('Y-m-d H:i:s');
        $qugeo->quor_Date = now()->format('Y-m-d');
        $qugeo->quor_FirstScheduleRun = $openingtime;
        $qugeo->quor_TrackingHistory = "";
        $qugeo->quor_ItemDetails = $cpd2brqugeopush->getCPD2BRItemDetails($transferorder_id);
        $qugeo->quor_ItemReturnUpdate = $cpd2brqugeopush->getCPD2BRItemReturnUpdateSQL($transfer->fstr_id);
        $qugeo->quor_AmountCollectible = $cpd2brqugeopush->getCPD2BRAmount($transferorder_id);
        $qugeo->quor_Paymode = $cpd2brqugeopush->getCPD2BRPaymentMode($transferorder_id);
        return $qugeo;
    }
    protected function getReturnPackingQugeoDetails($qugeo,$source,$destination,$transfer,$qugeoProcessor,$transferorder_id)
    {
        $retpackqugeopush = new RETPACKQugeoPush();
        $qugeo->quor_TransferOrder_Type = static::STOCK_RETURN;
        $openingtime = now()->addSeconds(config('app.qugeo_opening_time'));
        $qugeo->quor_RefNo = $transfer->frrp_uid; //*
        $qugeo->quor_PacketCount = 1;
        $qugeo->quor_ScheduleOpeningTime = $openingtime;  //now()->addSeconds(config('app.qugeo_opening_time'))
        $qugeo->quor_PickupToBeManual =1;
        $qugeo->quor_Pickupbr_id = $source->br_ID;
        $qugeo->quor_PickupName = $source->br_Incharge;
        $qugeo->quor_PickupAddress = $source->br_Address;
        $qugeo->quor_PickupLocation = $source->br_Name ?? '';
        $qugeo->quor_PickupPincode = $source->br_pincode;
        $qugeo->quor_PickupPhone = $source->br_Phone;
        $qugeo->quor_PickupLat = $source->br_Lat;
        $qugeo->quor_PickupLng = $source->br_Lng;
        $qugeo->quor_PickupStage1Distance = config('app.qugeo_stage1_time');
        $qugeo->quor_PickupStage2Distance = config('app.qugeo_stage2_time');
        $qugeo->quor_Deliverybr_id = $destination->br_ID;
        $qugeo->quor_DeliveryName = $destination->br_Incharge;
        $qugeo->quor_DeliveryAddress = $destination->br_Address;
        $qugeo->quor_DeliveryLocation = $destination->br_Name ?? '';
        $qugeo->quor_DeliveryPincode = $destination->br_pincode;
        $qugeo->quor_DeliveryPhone = $destination->br_Phone;
        $qugeo->quor_DeliveryLat = $destination->br_Lat;
        $qugeo->quor_DeliveryLng = $destination->br_Lng;
        $qugeo->quor_DeliverySMS = mt_rand(1000, 9999);
        $qugeo->quor_DeliveryStage1Distance = config('app.qugeo_stage1_time');
        $qugeo->quor_DeliveryStage2Distance = config('app.qugeo_stage2_time');
        $qugeo->quor_DistanceinKM = $qugeoProcessor->getDistance($source->br_Lat, $source->br_Lng, $destination->br_Lat, $destination->br_Lng);
        $qugeo->quor_StatusUpdateQry = $retpackqugeopush->getRETPACKStatusUpdateSQL($transfer->frrp_id);
        $qugeo->quor_TrackingUpdateQry = $retpackqugeopush->getRETPACKTrackUrlSQL($transfer->frrp_id);
        $qugeo->quor_CreatedOn = now()->format('Y-m-d H:i:s');
        $qugeo->quor_UpdateOn = now()->format('Y-m-d H:i:s');
        $qugeo->quor_Date = now()->format('Y-m-d');
        $qugeo->quor_FirstScheduleRun = $openingtime;
        $qugeo->quor_TrackingHistory = "";
        $qugeo->quor_ItemDetails = $retpackqugeopush->getRETPACKItemDetails($transferorder_id);
        $qugeo->quor_ItemReturnUpdate = $retpackqugeopush->getRETPACKItemReturnUpdateSQL($transfer->frrp_id);
        $qugeo->quor_AmountCollectible = $retpackqugeopush->getRETPACKAmount($transferorder_id);
        $qugeo->quor_Paymode = $retpackqugeopush->getRETPACKPaymentMode($transferorder_id);
        return $qugeo;
    }
    protected function getB2CQugeoDetails($qugeo,$source,$destination,$order,$qugeoProcessor,$transferorder_id,$invoiceamt)
    {
        $b2cqugeopush = new B2CQugeoPush();
        $openingtime = now()->addSeconds(config('app.qugeo_opening_time'));
        $qugeo->quor_TransferOrder_Type = static::CUSTOMER_ORDER;
        $qugeo->quor_RefNo = $order->order_order_id; //*
        $qugeo->quor_PacketCount = 1;
        $qugeo->quor_ScheduleOpeningTime = $openingtime;  //now()->addSeconds(config('app.qugeo_opening_time'))
        $qugeo->quor_PickupToBeManual =0;
        $qugeo->quor_Pickupbr_id = $source->br_ID;
        $qugeo->quor_PickupName = $source->br_Incharge;
        $qugeo->quor_PickupAddress = $source->br_Address;
        $qugeo->quor_PickupLocation = $source->br_Name ?? '';
        $qugeo->quor_PickupPincode = $source->br_pincode;
        $qugeo->quor_PickupPhone = $source->br_Phone;
        $qugeo->quor_PickupLat = $source->br_Lat;
        $qugeo->quor_PickupLng = $source->br_Lng;
        $qugeo->quor_PickupStage1Distance = config('app.qugeo_stage1_time');
        $qugeo->quor_PickupStage2Distance = config('app.qugeo_stage2_time');
        $qugeo->quor_Deliverybr_id = $order->order_branch_id;
        $qugeo->quor_DeliveryName = $destination->order_customer_name;
        $qugeo->quor_DeliveryAddress = $b2cqugeopush->getB2CDeliveryAddress($destination);
        $qugeo->quor_DeliveryLocation = $destination->order_land_mark ?? '';
        $qugeo->quor_DeliveryPincode = $destination->order_pin;
        $qugeo->quor_DeliveryPhone = $destination->order_contact_no;
        $qugeo->quor_DeliveryLat = $destination->order_latitude;
        $qugeo->quor_DeliveryLng = $destination->order_longitude;
        $qugeo->quor_DeliverySMS = mt_rand(1000, 9999);
        $qugeo->quor_DeliveryStage1Distance = config('app.qugeo_stage1_time');
        $qugeo->quor_DeliveryStage2Distance = config('app.qugeo_stage2_time');
        $qugeo->quor_DistanceinKM = $qugeoProcessor->getDistance($source->br_Lat, $source->br_Lng, $destination->order_latitude, $destination->order_longitude);
        $qugeo->quor_StatusUpdateQry = $b2cqugeopush->getB2CStatusUpdateSQL($order->order_id);
        $qugeo->quor_TrackingUpdateQry = $b2cqugeopush->getB2CTrackUrlSQL($order->order_id);
        $qugeo->quor_CreatedOn = now()->format('Y-m-d H:i:s');
        $qugeo->quor_UpdateOn = now()->format('Y-m-d H:i:s');
        $qugeo->quor_Date = now()->format('Y-m-d');
        $qugeo->quor_FirstScheduleRun = $openingtime;
        $qugeo->quor_TrackingHistory = $b2cqugeopush->getB2CTrackHistorySQL($order->order_id);
        $qugeo->quor_ItemDetails = $b2cqugeopush->getB2CItemDetails($order->order_id,$transferorder_id);
        $qugeo->quor_ItemReturnUpdate = $b2cqugeopush->getB2CItemReturnUpdateSQL($order->order_id);
        $qugeo->quor_AmountCollectible = $b2cqugeopush->getB2CAmount($order,$invoiceamt);
        $qugeo->quor_Paymode = $b2cqugeopush->getB2CPaymentMode($order);
        $qugeo->quor_slot_id = $order->order_slot_id;
        $qugeo->quor_slot_date = date('Y-m-d', strtotime($order->order_slot_date));
        $qugeo->orderID = $order->order_id;
        return $qugeo;
    }

    protected function getB2BQugeoDetails($qugeo,$source,$destination,$order,$qugeoProcessor,$transferorder_id,$invoiceamt)
    {
        $b2bqugeopush = new B2BQugeoPush();
        $qugeo->quor_TransferOrder_Type = static::B2B_ORDER ;
        $openingtime = now()->addSeconds(config('app.qugeo_opening_time'));
        $qugeo->quor_RefNo = $order->bbso_SONumber; //*
        $qugeo->quor_PacketCount = 1;
        $qugeo->quor_ScheduleOpeningTime =  $openingtime ;  //now()->addSeconds(config('app.qugeo_opening_time'))
        $qugeo->quor_PickupToBeManual =1;
        $qugeo->quor_Pickupbr_id = $source->br_ID;
        $qugeo->quor_PickupName = $source->br_Incharge;
        $qugeo->quor_PickupAddress = $source->br_Address;
        $qugeo->quor_PickupLocation = $source->br_Name ?? '';
        $qugeo->quor_PickupPincode = $source->br_pincode;
        $qugeo->quor_PickupPhone = $source->br_Phone;
        $qugeo->quor_PickupLat = $source->br_Lat;
        $qugeo->quor_PickupLng = $source->br_Lng;
        $qugeo->quor_PickupStage1Distance = config('app.qugeo_stage1_time');
        $qugeo->quor_PickupStage2Distance = config('app.qugeo_stage2_time');
        $qugeo->quor_Deliverybr_id = $order->br_ID;
        $qugeo->quor_DeliveryName = $destination->b2b_Customer_Name;
        $qugeo->quor_DeliveryAddress = $destination->b2b_Customer_Address;
        $qugeo->quor_DeliveryLocation =  '';
        $qugeo->quor_DeliveryPincode = $destination->b2b_Customer_pincode;
        $qugeo->quor_DeliveryPhone = $destination->b2b_Customer_Mobile;
        $qugeo->quor_DeliveryLat = $destination->b2b_Customer_Lat;
        $qugeo->quor_DeliveryLng = $destination->b2b_Customer_Lng;
        $qugeo->quor_DeliverySMS = mt_rand(1000, 9999);
        $qugeo->quor_DeliveryStage1Distance = config('app.qugeo_stage1_time');
        $qugeo->quor_DeliveryStage2Distance = config('app.qugeo_stage2_time');
        $qugeo->quor_DistanceinKM = $qugeoProcessor->getDistance($source->br_Lat, $source->br_Lng, $destination->b2b_Customer_Lat, $destination->b2b_Customer_Lng);
        $qugeo->quor_StatusUpdateQry = $b2bqugeopush->getB2BStatusUpdateSQL($order->bbso_id);
        $qugeo->quor_TrackingUpdateQry = $b2bqugeopush->getB2BTrackUrlSQL($order->bbso_id);
        $qugeo->quor_CreatedOn = now()->format('Y-m-d H:i:s');
        $qugeo->quor_UpdateOn = now()->format('Y-m-d H:i:s');
        $qugeo->quor_Date = now()->format('Y-m-d');
        $qugeo->quor_FirstScheduleRun = $openingtime;
        $qugeo->quor_TrackingHistory = "";
        $qugeo->quor_ItemDetails = $b2bqugeopush->getB2BItemDetails($order->bbso_id,$transferorder_id);
        $qugeo->quor_ItemReturnUpdate = $b2bqugeopush->getB2BItemReturnUpdateSQL($order->bbso_id);
        $qugeo->quor_AmountCollectible = $b2bqugeopush->getB2BAmount($order,$invoiceamt);
        $qugeo->quor_Paymode = $b2bqugeopush->getB2BPaymentMode($order);
        return $qugeo;
    }
    protected function getDistributionChartQugeoDetails($qugeo,$source,$destination,$transfer,$qugeoProcessor,$transferorder_id)
    {
        $distributionqugeopush = new DistributionQugeoPush();
        $qugeo->quor_TransferOrder_Type = static::BRANCH_DISTRIBUTION;
        $openingtime = now()->addSeconds(config('app.qugeo_opening_time'));
        $qugeo->quor_RefNo = $transfer->rdc_uid; //*
        $qugeo->quor_PacketCount = 1;
        $qugeo->quor_ScheduleOpeningTime = $openingtime;  //now()->addSeconds(config('app.qugeo_opening_time'))
        $qugeo->quor_PickupToBeManual =1;
        $qugeo->quor_Pickupbr_id = $source->br_ID;
        $qugeo->quor_PickupName = $source->br_Incharge;
        $qugeo->quor_PickupAddress = $source->br_Address;
        $qugeo->quor_PickupLocation = $source->br_Name ?? '';
        $qugeo->quor_PickupPincode = $source->br_pincode;
        $qugeo->quor_PickupPhone = $source->br_Phone;
        $qugeo->quor_PickupLat = $source->br_Lat;
        $qugeo->quor_PickupLng = $source->br_Lng;
        $qugeo->quor_PickupStage1Distance = config('app.qugeo_stage1_time');
        $qugeo->quor_PickupStage2Distance = config('app.qugeo_stage2_time');
        $qugeo->quor_Deliverybr_id = $destination->br_ID;
        $qugeo->quor_DeliveryName = $destination->br_Incharge;
        $qugeo->quor_DeliveryAddress = $destination->br_Address;
        $qugeo->quor_DeliveryLocation = $destination->br_Name ?? '';
        $qugeo->quor_DeliveryPincode = $destination->br_pincode;
        $qugeo->quor_DeliveryPhone = $destination->br_Phone;
        $qugeo->quor_DeliveryLat = $destination->br_Lat;
        $qugeo->quor_DeliveryLng = $destination->br_Lng;
        $qugeo->quor_DeliverySMS = mt_rand(1000, 9999);
        $qugeo->quor_DeliveryStage1Distance = config('app.qugeo_stage1_time');
        $qugeo->quor_DeliveryStage2Distance = config('app.qugeo_stage2_time');
        $qugeo->quor_DistanceinKM = $qugeoProcessor->getDistance($source->br_Lat, $source->br_Lng, $destination->br_Lat, $destination->br_Lng);
        $qugeo->quor_StatusUpdateQry = $distributionqugeopush->getDistributionStatusUpdateSQL($transfer->rdc_id);
        $qugeo->quor_TrackingUpdateQry = $distributionqugeopush->getDistributionTrackUrlSQL($transfer->rdc_id);
        $qugeo->quor_CreatedOn = now()->format('Y-m-d H:i:s');
        $qugeo->quor_UpdateOn = now()->format('Y-m-d H:i:s');
        $qugeo->quor_Date = now()->format('Y-m-d');
        $qugeo->quor_FirstScheduleRun = $openingtime;
        $qugeo->quor_TrackingHistory = "";
        $qugeo->quor_ItemDetails = $distributionqugeopush->getDistributionItemDetails($transferorder_id);
        $qugeo->quor_ItemReturnUpdate = $distributionqugeopush->getDistributionItemReturnUpdateSQL($transfer->rdc_id);
        $qugeo->quor_AmountCollectible = $distributionqugeopush->getDistributionAmount($transferorder_id);
        $qugeo->quor_Paymode = $distributionqugeopush->getDistributionPaymentMode($transferorder_id);
        return $qugeo;
}
}
