<?php

namespace App\Modules;

use App\Events\OtpGenerated;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Branch;

class CustomerPickupOtp
{
    
    public static function sendOtp($order_id)
    {
        return (new static)->otpsend($order_id);

    }

    public function otpsend($order_id)
    {
          $order=Order::where("order_id",$order_id)->select(['order_id','order_order_id','order_method','order_customer_id','order_branch_id'])->first();
       
        if($order->order_method==2){
           $otp=rand(0000,9999);
           $msg=$this->getMessage($order,$otp) ;
           Order::where("order_id",$order_id)->update(["order_customerpickup_otp"=>$otp]);
          $customer=Customer::where("cust_id",$order->order_customer_id)->first();
         //  $order->save();
           return event(new OtpGenerated($customer->cust_mobile, $msg));
          
       }
    }
    public function getMessage($order,$otp){
        $msg='';
        if($order->order_method==2){
             $branch= Branch::where("br_ID",$order->order_branch_id)->first();
             $msg= "You can collect the order from ".$branch->br_Name.",".$branch->br_Address." , Contact Number: ".$branch->br_Phone.". Please provide secret code $otp when the retailer ask for OTP.";
        }
      return $msg;
    }

}