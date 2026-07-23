<?php

namespace App\PaymentGateways;

use App\Models\Order;
use App\Models\CompanyBranch;
use App\Models\CompanyEasypay;
use App\Domains\Easypay\EasyPay;
use App\Exceptions\MsgException;
use App\Exceptions\ErrorException;
use App\Domains\Easypay\AesForJava;
use App\Models\Payment\EasypayModel;
use App\Models\Payment\InstamojoModel;
use App\PaymentGateways\InterfacePaymentGateway;

class EasypayPayment implements InterfacePaymentGateway
{
    protected const ONLINE_PAYMENT = 2;

    const PAYMENT_SUCCESS = 1;

    const PAYMENT_FAILED = 2;
    private function getCallBackURL($compid){
        $userAgentType=getUserAgentType(); 
        $redirect_url=($userAgentType=="web")? config('paymentgateway.web_redirect_url'):route('payment.result',['paymentgateway' => config('paymentgateway.default') . '-' . $compid]);
        $redirect_url=route('payment.result',['paymentgateway' => config('paymentgateway.default'). '-' . $compid]);
        return $redirect_url;    
    }
    private function getToken($companyeasypay){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $companyeasypay['tokenurl']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);  
        $resp = json_decode($output);
        
        return $resp->token;
    }
    public function processPayment($request)
    {
       // try {
            //createEasypayRequest($cid = '', $rid = '', $crn = '', $amt = '', $ver = '', $typ = '', $cny = '', $rtu = '', $ppi = '', $re1 = 'MN', $re2 = '', $re3 = '', $re4 = '', $re5 = '')
            $company_id = CompanyBranch::where('br_Id', auth_user()->deli_branch_id)->first();
            $compid =  $company_id->comp_id ?? 0;
            $companyeasypay = CompanyEasypay::getCompanyPaydetails($compid);

            $token = $this->getToken($companyeasypay);
            $easypayParams = [];
            $easypayParams["cid"] = $companyeasypay['cid'];
            $easypayParams["typ"] = $companyeasypay['typ'];
            $easypayParams["ver"] = $companyeasypay['ver'];
            $easypayParams["rid"] = microtime(1)*10000;
            $easypayParams["amt"] = $request['total_amount'];
            $easypayParams["cny"] = $companyeasypay['cny'];
            $easypayParams["crn"] = $easypayParams["rid"];
            $easypayParams["rtu"] = $this->getCallBackURL($compid);
            $easypayParams["re1"] = $companyeasypay['re1'];
            $easypayParams["ppi"] = $request['order_order_id'].'|'. auth_user()->cust_email . '|' . auth_user()->cust_mobile . '|' . auth_user()->cust_customer_name . '|' . $easypayParams["rid"].'|'. $request['total_amount'];

            $easypay = new EasyPay('Dummy',$companyeasypay['checksumkey'],$companyeasypay['encryptionkey']);
            $easypayChecksum = $easypay->createEasypayRequest($easypayParams["cid"], $easypayParams["rid"], $easypayParams["rid"],  $easypayParams["amt"], $easypayParams["ver"], $easypayParams["typ"], $easypayParams["cny"], $easypayParams["rtu"], $easypayParams["ppi"],$easypayParams["re1"],  '',  '',  '',  '');       

            $savedets = ['id'=>$easypayParams["rid"],'phone'=>auth_user()->cust_mobile ,'email'=>auth_user()->cust_email,'buyer_name'=>auth_user()->cust_customer_name,'amount' => $request['total_amount'], 'purpose' => $request['order_order_id'], 'longurl' =>  $companyeasypay['paymenturl']."?i=". $easypayChecksum . "&j=" . $token, 'created_at' => now(), 'modified_at' => now(), 'redirect_url' => $easypayParams["rtu"] ];

            $this->addEasypay($savedets, $request);
            return [
                'id' => $easypayParams["rid"],                
                'longurl'  => $companyeasypay['paymenturl']."?i=". $easypayChecksum . "&j=" . $token,
            ];
       /* } catch (\Exception $e) {
            throw new ErrorException($e->getMessage());
        }*/
    }

    public function paymentComplete($request,$compid){
        //decrypt($data = null, $key = null, $blockSize = null, $mode = null)
        $companyeasypay = CompanyEasypay::getCompanyPaydetails($compid);
        $responsestr = (new AesForJava)->decrypt($request->i,$companyeasypay['encryptionkey']);

        parse_str($responsestr,$response);

        $paymentId = $request->input('payment_request_id');
        $order = $this->findOrder($response['RID']);
        //$response = $this->verify($response['RID'],$order,$companyeasypay);

        $status = $response['RMK'] ?? '';
        $ref_id = $response['BRN'] ?? '';
        $amount = $response['AMT'] ?? '';
        $trn = $response['TRN'] ?? '';
        if($status === 'success'){
            $status = 'success';
            $this->updateEasypay($response, $status, static::PAYMENT_SUCCESS, $order->order_id,$trn);
        }else{
            $status = 'failed' ;
            $this->updateEasypay($response, 'Failed', static::PAYMENT_FAILED, $order->order_id,$trn);
        }
        $responsestring =  json_encode($response);
        return ["status"=>$status,"amount"=> $amount, 'reponseid' => $ref_id, 'responsestring' => $responsestring, 'order' => $order  ];
        
    }

    private function findOrder($paymentId)
    {
        $crcId = crc32($paymentId);

        return Order::where('order_payment_gateway_req_refid_crc32', $crcId)
            ->where('order_payment_gateway_req_refid', $paymentId)
            ->latest()->firstOrFail();
    }

    private function addEasypay($response, $request)
    {
       return EasypayModel::create([
            "customer_id" => auth_user()->cust_id,
            "order_id" => $request['order_id'],
            "order_order_id" => $request['order_order_id'],
            "easpayreq_id" => $response['id'],
            "easpayreq_id_crc32" => crc32($response['id']),
            "phone" => $response['phone'],
            "email" => $response['email'],
            "name" => $response['buyer_name'],
            "amount" => $response['amount'],
            "purpose" => $response['purpose'],
            "payment_status" => 0,
            "long_url" => $response['longurl'],
            "inst_created_at" => $response['created_at'],
            "inst_modified_at" => $response['modified_at'],
            "redirect_url" => $response['redirect_url'],
        ]);
    }
    private function updateEasypay($response, $status, $payment_status, $order_id, $trn)
    {
        $instamojo = EasypayModel::where('order_id', $order_id)
                        ->firstOrFail();
        $data = [
            'payment_status' => $payment_status,
            'status' => $status,
            'response' => json_encode($response),
        ];

        $data['easy_id'] =  $response['BRN'] ?? '';
        $data['easy_id_crc32'] = crc32($data['easy_id']);
        $data['mojo_trn_id'] = $trn;
        if($payment_status === 'success')
            {
            $data['currency'] =  $response['CNY'] ?? '';
            //$data['fees'] =  $response['BRN'] ?? '';
        }
        return $instamojo->update($data);
    }
    public function verify($paymentreqid,$order,$companyeasypay)
    {       
       
       try {
            if($order->order_payment_response_received !=0)
            {
                return ["status"=>($order['status_id']=='4'?'Completed':'Failed'),"payments"=>[["status"=>($order['status_id']=='4'?'Completed':'Failed')]]];               
            }
            $easypayParams = [];
            $easypayParams["cid"] = $companyeasypay['cid'];
            $easypayParams["typ"] = $companyeasypay['typ'];
            $easypayParams["ver"] = $companyeasypay['ver'];
            $easypayParams["rid"] = $paymentreqid;
            $easypayParams["crn"] = $paymentreqid;

            //callEasyPayEnquiry($cid = '', $rid = '', $crn = '', $ver = '', $typ = '') 
            $easypay = new EasyPay($companyeasypay['enquiryurl'],$companyeasypay['checksumkey'],$companyeasypay['encryptionkey']);
            //echo 'Below is easy pay';
            $easypayenquiryresponse = $easypay->callEasyPayEnquiry($easypayParams["cid"], $easypayParams["rid"], $easypayParams["crn"], $easypayParams["ver"], $easypayParams["typ"]);    
            $response = (new AesForJava)->decrypt($easypayenquiryresponse,$companyeasypay['encryptionkey']);
            return $response;
        }
        catch (\Exception $e) {
            throw new ErrorException($e->getMessage()."--Comapnyid->". $compid."--".$companyinstamojo['api_key']."--".$companyinstamojo['auth_token']."--".$companyinstamojo['url']);
        }
    
    }    
}
