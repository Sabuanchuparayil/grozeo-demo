<?php

namespace App\Http\Repositories\Finascop;

use App\Models\Order;
use App\Models\Finascop\FinascopQueue;
use App\Models\Finascop\FinascopSettings;

class StoreFinascop {

    const PAYTM = "paytm";

    const INSTAMOJO = "instamojo";

    const EASYPAY = "easypay";

    const RAZORPAY = "razorpay";

    const STRIPE = "stripe";

    public static function margindistribution($configid, $order ,$HOpayable, $company, $cs, $distri, $retail, $incen, $delchrg)
    {
        //$setting = '10paytm-SalesB2COnlineHmeDelPaidOnline';
        $data = FinascopSettings::where('waqs_Name', $configid)->first();
        if($data)
        {
            $decode = json_decode($data['waqs_Configuration']);
        }else{
            throw new Exception('Missing Finascop wallet queue settings ');
        }

        $customer_name = auth_user()->cust_customer_name ?? '';
        $mobile = auth_user()->cust_mobile ?? '';
       // $comment = "Type:B2C On Online Sales({$text}), TRANS ID:{$order->order_id}, NO:{$order->order_order_id}, Name:{$customer_name}, Amt:{$order->total}, Phone:{$mobile}";
       //Type:Marin Distribution Process Queue, TRANS ID:#ID#, NO:#NO#,  Amt:#AMT#
        $comment = str_replace('#ID#',$order->order_id,$decode->comments);
        $comment = str_replace('#NO#',$order->order_order_id,$comment);
        $comment = str_replace('#AMT#',$HOpayable->amt,$comment);
        isset($decode->dr->companyMargin->amt) ? $decode->dr->companyMargin->amt = $company->amt : 0;
        isset($decode->dr->companyMargin->br_ReferenceID) ? $decode->dr->companyMargin->br_ReferenceID= $company->br_ReferenceID : -1;

        isset($decode->dr->csMargin->amt) ? $decode->dr->csMargin->amt = $cs->amt : 0;
        isset($decode->dr->csMargin->br_ReferenceID) ? $decode->dr->csMargin->br_ReferenceID= $cs->br_ReferenceID : -1;

        isset($decode->dr->distributorMargin->amt) ? $decode->dr->distributorMargin->amt = $distri->amt : 0;
        isset($decode->dr->distributorMargin->br_ReferenceID) ? $decode->dr->distributorMargin->br_ReferenceID= $distri->br_ReferenceID : -1;

        isset($decode->dr->retailerMargin->amt) ? $decode->dr->retailerMargin->amt = $retail->amt : 0;
        isset($decode->dr->retailerMargin->br_ReferenceID) ? $decode->dr->retailerMargin->br_ReferenceID= $retail->br_ReferenceID : -1;

        isset($decode->dr->revenueIncentive->amt) ? $decode->dr->revenueIncentive->amt = $incen->amt : 0;
        isset($decode->dr->revenueIncentive->br_ReferenceID) ? $decode->dr->revenueIncentive->br_ReferenceID= $incen->br_ReferenceID : -1;

        isset($decode->dr->retailorDeliveryCharge->amt) ? $decode->dr->retailorDeliveryCharge->amt = $delchrg->amt : 0;
        isset($decode->dr->retailorDeliveryCharge->br_ReferenceID) ? $decode->dr->retailorDeliveryCharge->br_ReferenceID= $delchrg->br_ReferenceID : -1;
        //$gst = round($order->order_total_gst / 2, 2);
        isset($decode->cr->HOmarginPayable->amt) ? $decode->cr->HOmarginPayable->amt = $HOpayable->amt : 0;
        isset($decode->cr->HOmarginPayable->br_ReferenceID) ? $decode->cr->HOmarginPayable->br_ReferenceID = $HOpayable->br_ReferenceID : -1;
        isset($decode->comments) ? $decode->comments = $comment : '';
        return FinascopQueue::create([
            "waqu_TransDate" => $order->order_confirm_date,
            "waqu_comment" => $comment,
            "waqu_SourceID" => $order->order_id,
            "waqs_id" => $data['waqs_id'],
            "waqu_Amount" => $HOpayable->amt,
            "br_id" => $order->order_branch_id,
            "waqu_Data" => json_encode($decode),
        ]);

    }

    public static function reversemargindistribution($configid, $order ,$HOpayable, $company, $cs, $distri, $retail, $incen, $delchrg)
    {
        //$setting = '10paytm-SalesB2COnlineHmeDelPaidOnline';
        $data = FinascopSettings::where('waqs_Name', $configid)->first();
        if($data)
        {
            $decode = json_decode($data['waqs_Configuration']);
        }else{
            throw new Exception('Missing Finascop wallet queue settings ');
        }

        $customer_name = auth_user()->cust_customer_name ?? '';
        $mobile = auth_user()->cust_mobile ?? '';
       // $comment = "Type:B2C On Online Sales({$text}), TRANS ID:{$order->order_id}, NO:{$order->order_order_id}, Name:{$customer_name}, Amt:{$order->total}, Phone:{$mobile}";
       //Type:Marin Distribution Process Queue, TRANS ID:#ID#, NO:#NO#,  Amt:#AMT#
        $comment = str_replace('#ID#',$order->order_id,$decode->comments);
        $comment = str_replace('#NO#',$order->order_order_id,$comment);
        $comment = str_replace('#AMT#',$order->total,$comment);
        isset($decode->cr->companyMargin->amt) ? $decode->cr->companyMargin->amt = $company->amt : 0;
        isset($decode->cr->companyMargin->br_ReferenceID) ? $decode->cr->companyMargin->br_ReferenceID= $company->br_ReferenceID : -1;

        isset($decode->cr->csMargin->amt) ? $decode->cr->csMargin->amt = $cs->amt : 0;
        isset($decode->cr->csMargin->br_ReferenceID) ? $decode->cr->csMargin->br_ReferenceID= $cs->br_ReferenceID : -1;

        isset($decode->cr->distributorMargin->amt) ? $decode->cr->distributorMargin->amt = $distri->amt : 0;
        isset($decode->cr->distributorMargin->br_ReferenceID) ? $decode->cr->distributorMargin->br_ReferenceID= $distri->br_ReferenceID : -1;

        isset($decode->cr->retailerMargin->amt) ? $decode->cr->retailerMargin->amt = $retail->amt : 0;
        isset($decode->cr->retailerMargin->br_ReferenceID) ? $decode->cr->retailerMargin->br_ReferenceID= $retail->br_ReferenceID : -1;

        isset($decode->cr->revenueIncentive->amt) ? $decode->cr->revenueIncentive->amt = $incen->amt : 0;
        isset($decode->cr->revenueIncentive->br_ReferenceID) ? $decode->cr->revenueIncentive->br_ReferenceID= $incen->br_ReferenceID : -1;

        isset($decode->cr->retailorDeliveryCharge->amt) ? $decode->cr->retailorDeliveryCharge->amt = $delchrg->amt : 0;
        isset($decode->cr->retailorDeliveryCharge->br_ReferenceID) ? $decode->cr->retailorDeliveryCharge->br_ReferenceID= $delchrg->br_ReferenceID : -1;
        //$gst = round($order->order_total_gst / 2, 2);
        isset($decode->dr->HOmarginPayable->amt) ? $decode->dr->HOmarginPayable->amt = $HOpayable->amt : 0;
        isset($decode->dr->HOmarginPayable->br_ReferenceID) ? $decode->dr->HOmarginPayable->br_ReferenceID = $HOpayable->br_ReferenceID : -1;
        isset($decode->comments) ? $decode->comments = $comment : '';
        return FinascopQueue::create([
            "waqu_TransDate" => $order->order_confirm_date,
            "waqu_comment" => $comment,
            "waqu_SourceID" => $order->order_id,
            "waqs_id" => $data['waqs_id'],
            "waqu_Amount" => $HOpayable->amt,
            "br_id" => $order->order_branch_id,
            "waqu_Data" => json_encode($decode),
        ]);

    }

    public static function cancelonlinebooking($configid, $customer_id, $order_id)
    {
        $data = FinascopSettings::where('waqs_Name', $configid)->first();
        if($data)
        {
            $decode = json_decode($data['waqs_Configuration']);
            $order = Order::where('order_id', $order_id)
            ->where('order_customer_id', $customer_id)
            ->first();
        }else{

                throw new Exception('Missing Finascop wallet queue settings ');

        }
        $customer_name = auth_user()->cust_customer_name ?? '';
        $mobile = auth_user()->cust_mobile ?? '';
        $comment = str_replace('#ID#',$order->order_id,$decode->comments);
        $comment = str_replace('#NO#',$order->order_order_id,$comment);
        $comment = str_replace('#AMT#',$order->total,$comment);
        isset($decode->cr->customerWallet->amt) ? $decode->cr->customerWallet->amt = $order->total : 0;
        isset($decode->dr->retailerSales->amt) ? $decode->dr->retailerSales->amt = $order->order_total_amount : 0;
        //$gst = round($order->order_total_gst / 2, 2);
        isset($decode->dr->cgst->amt) ? $decode->dr->cgst->amt = $order->order_total_cgst : 0;
        isset($decode->dr->sgst->amt) ? $decode->dr->sgst->amt = $order->order_total_sgst : 0;
        isset($decode->dr->kfc->amt) ? $decode->dr->kfc->amt = $order->order_kfc_amount : 0;
        isset($decode->dr->discount->amt) ? $decode->dr->discount->amt = ($order->order_discount_amount > 0 ? "-".$order->order_discount_amount : 0) : 0;
        isset($decode->dr->roundoff->amt) ? $decode->dr->roundoff->amt = $order->order_roundoff : 0;
        isset($decode->comments) ? $decode->comments = $comment : 0;

        return FinascopQueue::create([
            "waqu_TransDate" => $order->order_confirm_date,
            "waqu_comment" => $comment,
            "waqu_SourceID" => $order->order_id,
            "waqs_id" => $data['waqs_id'],
            "waqu_Amount" => $order->total,
            "br_id" => $order->order_branch_id,
            "waqu_Data" => json_encode($decode),
        ]);

    }

    public static function topayondeliverybooking($configid, $customer_id, $order_id)
    {
        $data = FinascopSettings::where('waqs_Name', $configid)->first();
        if($data)
        {
            $decode = json_decode($data['waqs_Configuration']);
            $order = Order::where('order_id', $order_id)
            ->where('order_customer_id', $customer_id)
            ->first();
        }else{

                throw new Exception('Missing Finascop wallet queue settings ');

        }
        $customer_name = auth_user()->cust_customer_name ?? '';
        $mobile = auth_user()->cust_mobile ?? '';
        $comment = str_replace('#ID#',$order->order_id,$decode->comments);
        $comment = str_replace('#NO#',$order->order_order_id,$comment);
        $comment = str_replace('#AMT#',$order->total,$comment);
        $comment = str_replace('#NAME#',$customer_name,$comment);
        isset($decode->dr->cashCollectibleatRetail->amt) ? $decode->dr->cashCollectibleatRetail->amt = ($order->total-$order->order_wallet_amount) : 0;
        //isset($decode->dr->customerWallet->amt) ? $decode->dr->customerWallet->amt = $order->order_wallet_amount : 0;
        isset($decode->cr->retailerSales->amt) ? $decode->cr->retailerSales->amt = $order->order_total_amount : 0;
        //$gst = round($order->order_total_gst / 2, 2);
        isset($decode->cr->cgst->amt) ? $decode->cr->cgst->amt = $order->order_total_cgst : 0;
        isset($decode->cr->sgst->amt) ? $decode->cr->sgst->amt = $order->order_total_sgst : 0;
        isset($decode->cr->kfc->amt) ? $decode->cr->kfc->amt = $order->order_kfc_amount : 0;
        isset($decode->cr->discount->amt) ? $decode->cr->discount->amt = ($order->order_discount_amount > 0 ? "-".$order->order_discount_amount : 0) : 0;
        isset($decode->cr->roundoff->amt) ? $decode->cr->roundoff->amt = $order->order_roundoff : 0;
        isset($decode->comments) ? $decode->comments = $comment : 0;

        return FinascopQueue::create([
            "waqu_TransDate" => $order->order_confirm_date,
            "waqu_comment" => $comment,
            "waqu_SourceID" => $order->order_id,
            "waqs_id" => $data['waqs_id'],
            "waqu_Amount" => $order->total,
            "br_id" => $order->order_branch_id,
            "waqu_Data" => json_encode($decode),
        ]);

    }

    public static function canceltopayondeliverybooking($configid, $customer_id, $order_id)
    {
        $data = FinascopSettings::where('waqs_Name', $configid)->first();
        if($data)
        {
            $decode = json_decode($data['waqs_Configuration']);
            $order = Order::where('order_id', $order_id)
            ->where('order_customer_id', $customer_id)
            ->first();
        }else{

                throw new Exception('Missing Finascop wallet queue settings ');

        }
        $customer_name = auth_user()->cust_customer_name ?? '';
        $mobile = auth_user()->cust_mobile ?? '';
                $comment = str_replace('#ID#',$order->order_id,$decode->comments);
        $comment = str_replace('#NO#',$order->order_order_id,$comment);
        $comment = str_replace('#AMT#',$order->total,$comment);
        isset($decode->cr->cashCollectibleatRetilor->amt) ? $decode->cr->cashCollectibleatRetilor->amt = ($order->total-$order->order_wallet_amount) : 0;
        //isset($decode->cr->customerWallet->amt) ? $decode->cr->customerWallet->amt = $order->order_wallet_amount : 0;
        isset($decode->dr->retailerSales->amt) ? $decode->dr->retailerSales->amt = $order->order_total_amount : 0;
        //$gst = round($order->order_total_gst / 2, 2);
        isset($decode->dr->cgst->amt) ? $decode->dr->cgst->amt = $order->order_total_cgst : 0;
        isset($decode->dr->sgst->amt) ? $decode->dr->sgst->amt = $order->order_total_sgst : 0;
        isset($decode->dr->kfc->amt) ? $decode->dr->kfc->amt = $order->order_kfc_amount : 0;
        isset($decode->dr->discount->amt) ? $decode->dr->discount->amt = ($order->order_discount_amount > 0 ? "-".$order->order_discount_amount : 0) : 0;
        isset($decode->dr->roundoff->amt) ? $decode->dr->roundoff->amt = $order->order_roundoff : 0;
        isset($decode->comments) ? $decode->comments = $comment : 0;

        return FinascopQueue::create([
            "waqu_TransDate" => $order->order_confirm_date,
            "waqu_comment" => $comment,
            "waqu_SourceID" => $order->order_id,
            "waqs_id" => $data['waqs_id'],
            "waqu_Amount" => $order->total,
            "br_id" => $order->order_branch_id,
            "waqu_Data" => json_encode($decode),
        ]);

    }

    public static function paidcustomercollectbooking($configid, $customer_id, $order_id)
    {
        $data = FinascopSettings::where('waqs_Name', $configid)->first();
        if($data)
        {
            $decode = json_decode($data['waqs_Configuration']);
            $order = Order::where('order_id', $order_id)
            ->where('order_customer_id', $customer_id)
            ->first();
        }else{
                throw new Exception('Missing Finascop wallet queue settings ');
        }
        $customer_name = auth_user()->cust_customer_name ?? '';
        $mobile = auth_user()->cust_mobile ?? '';
        $comment = str_replace('#ID#',$order->order_id,$decode->comments);
        $comment = str_replace('#NO#',$order->order_order_id,$comment);
        $comment = str_replace('#AMT#',$order->total,$comment);
        $comment = str_replace('#NAME#',$customer_name,$comment);
        isset($decode->dr->cashinhand->amt) ? $decode->dr->cashinhand->amt = $order->total : 0;
        isset($decode->cr->cashcollectibile->amt) ? $decode->cr->cashcollectibile->amt = $order->total : 0;
        //$gst = round($order->order_total_gst / 2, 2);

        return FinascopQueue::create([
            "waqu_TransDate" => $order->order_confirm_date,
            "waqu_comment" => $comment,
            "waqu_SourceID" => $order->order_id,
            "waqs_id" => $data['waqs_id'],
            "waqu_Amount" => $order->total,
            "br_id" => $order->order_branch_id,
            "waqu_Data" => json_encode($decode),
        ]);

    }
    public static function store($payment_type, $customer_id, $order_id)
    {
        return (new static)->storeFinascop($payment_type, $customer_id, $order_id);
    }

    private function storeFinascop($payment_type, $customer_id, $order_id)
    {
        if(static::PAYTM === $payment_type)
        {
            $setting = '10-SalesB2COnlineHmeDelPaidOnlinePaytm';
            $text = "Paytm";
        }
        elseif(static::INSTAMOJO === $payment_type)
        {
            $setting = '10-SalesB2COnlineHmeDelPaidOnlineInstaMojo';
            $text = "Instamojo";
        }
        elseif(static::EASYPAY === $payment_type)
        {
            $setting = '10-SalesB2COnlineHmeDelPaidOnlineEasypay';
            $text = "Easypay";
        }
        elseif(static::RAZORPAY === $payment_type){
            $setting = '10-SalesB2COnlineHmeDelPaidOnlineRazorpay';
            $text = "Razorpay";
        }
        elseif(static::STRIPE == $payment_type){
            $setting = '10-SalesB2COnlineHmeDelPaidOnlineStripe';
            $text = "Stripe";
        }
        $data = FinascopSettings::where('waqs_Name', $setting)->first();
        if($data)
        {
            $decode = json_decode($data['waqs_Configuration']);
            $order = $this->getOrderDetails($customer_id, $order_id);
            return $this->addData($order, $decode, $text, $data['waqs_id']);
        }else{
            throw new Exception('Missing Finascop wallet queue settings ');
        }
        return false;
    }

    private function getOrderDetails($customer_id, $order_id)
    {
        return Order::where('order_id', $order_id)
                        ->where('order_customer_id', $customer_id)
                        ->first();
    }

    private function addData($order, $decode, $text, $waqs_id)
    {
        $customer_name = auth_user()->cust_customer_name ?? '';
        $mobile = auth_user()->cust_mobile ?? '';
        $comment = "Type:B2C On Online Sales({$text}), TRANS ID:{$order->order_id}, NO:{$order->order_order_id}, Name:{$customer_name}, Amt:{$order->total}, Phone:{$mobile}";
        isset($decode->dr->paymentgateway->amt) ? $decode->dr->paymentgateway->amt = ($order->total-$order->order_wallet_amount) : 0;
        //isset($decode->dr->customerWallet->amt) ? $decode->dr->customerWallet->amt = $order->order_wallet_amount : 0;
        isset($decode->cr->collect->amt) ? $decode->cr->collect->amt = $order->order_total_amount : 0;
        //$gst = round($order->order_total_gst / 2, 2);
        isset($decode->cr->cgst->amt) ? $decode->cr->cgst->amt = $order->order_total_cgst : 0;
        isset($decode->cr->sgst->amt) ? $decode->cr->sgst->amt = $order->order_total_sgst : 0;
        isset($decode->cr->kfc->amt) ? $decode->cr->kfc->amt = $order->order_kfc_amount : 0;
        isset($decode->cr->discount->amt) ? $decode->cr->discount->amt = ($order->order_discount_amount > 0 ? "-".$order->order_discount_amount : 0) : 0;
        isset($decode->cr->roundoff->amt) ? $decode->cr->roundoff->amt = $order->order_roundoff : 0;
        isset($decode->comments) ? $decode->comments = $comment : 0;
        return FinascopQueue::create([
            "waqu_TransDate" => $order->order_confirm_date,
            "waqu_comment" => $comment,
            "waqu_SourceID" => $order->order_id,
            "waqs_id" => $waqs_id,
            "waqu_Amount" => $order->total,
            "br_id" => $order->order_branch_id,
            "waqu_Data" => json_encode($decode),
        ]);

    }
    
    public static function getSalesOrder_entryRefId($order_id = 0){
       return Order::select('entry_RefId')->where('order_id',(int) $order_id)->first();
    }

}
