<?php

namespace App\PaymentGateways;

use Instamojo;
use App\Models\Order;
use App\Models\CompanyBranch;
use App\Exceptions\MsgException;
use App\Models\CompanyInstamojo;
use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\Log;
use App\Models\Payment\InstamojoModel;
use App\Models\PaymentTransactionDetails;
use App\PaymentGateways\InterfacePaymentGateway;
use Illuminate\Support\Facades\DB;
Use Exception;
class InstamojoPayment implements InterfacePaymentGateway
{
    protected const ONLINE_PAYMENT = 2;

    const PAYMENT_SUCCESS = 1;

    const PAYMENT_FAILED = 2;
    public function paymentComplete($request,$compid){
        $paymentId = $request->input('payment_request_id');
        $order = $this->findOrder($paymentId);     
        $response = $this->verify([
            'id' => $paymentId,
            'order_id' => $order->order_id,
        ]);
        $status = $response['payments'][0]['status'] ?? '';
        $ref_id = $response['payments'][0]['payment_id'] ?? '';
        $amount = $response['amount'] ?? '';
        if($status === 'Credit'){
            $status = 'success';
            $this->updateInstamojo($response, $status, static::PAYMENT_SUCCESS, $order->order_id);
        }else{
            $status = 'failed' ;
            $this->updateInstamojo($response, 'Failed', static::PAYMENT_FAILED, $order->order_id);
        }
        $responsestring =  json_encode($response);
        return ["status"=>$status,"amount"=> $amount, 'reponseid' => $ref_id, 'responsestring' => $responsestring, 'paymentid' => $paymentId, 'order' => $order   ];
        
    }

    public function processPayment($request)
    {
        //try {
            $company_id = CompanyBranch::where('br_Id', auth_user()->deli_branch_id)->first();
            $compid =  $company_id->comp_id ?? 0;
            $companyinstamojo = CompanyInstamojo::getCompanyPaydetails($compid);
            $api = new Instamojo\Instamojo(
                $companyinstamojo['api_key'],
                $companyinstamojo['auth_token'],
                $companyinstamojo['url']
            );
            $date = \gmdate("Y-m-d H:i:s", strtotime('+595 seconds'));
            $userAgentType=getUserAgentType(); 

            $redirect_url=($userAgentType=="web")? config('paymentgateway.web_redirect_url'):route('payment.result',['paymentgateway' => config('paymentgateway.default') . '-' .  $compid]);
             $redirect_url=route('payment.result',['paymentgateway' => config('paymentgateway.default') . '-' . $compid]);

           // $date = \gmdate("Y-m-d H:i:s", strtotime('+10 minutes'));
            $requestArray=array(
                "purpose" => config('siteinfo.app_client_project_name'),
               // "amount" => $request['total_amount'],
                "amount" => $request->total,
                "send_email" => false,
                "email" => auth_user()->cust_email,
                "redirect_url" => $redirect_url,
                "phone" => auth_user()->cust_mobile,
                "send_sms" => false,
                "buyer_name" => auth_user()->cust_customer_name,
                "expires_at" => $date,
                "webhook" => $url_webhook =  route('payment.webhook',['paymentgateway' => config('paymentgateway.default')]),
            );
            $response = $api->paymentRequestCreate($requestArray);

            $orderCount = DB::table('retaline_customer_onlinepayment_details')            
            ->where('order_id', $request["order_id"])           
            ->count() ; 
            if($orderCount == 0){
                PaymentTransactionDetails::create(
                  array('order_id' => $request["order_id"],"roop_requeststring"=>json_encode($requestArray),"roop_requestid"=>$response["id"])
                );

            }    

           $this->addInstamojo($response, $request);
           $result =[];
           $result['id'] = $response['id'];
           $result['longurl'] = $response['longurl'];
            return $result;
       /* } catch (\Exception $e) {
            throw new ErrorException($e->getMessage());
        }*/
    }

    private function findOrder($paymentId)
    {
        $crcId = crc32($paymentId);

        return Order::where('order_payment_gateway_req_refid_crc32', $crcId)
            ->where('order_payment_gateway_req_refid', $paymentId)
            ->latest()->firstOrFail();
    }
    
    private function addInstamojo($response, $request)
    {
       return InstamojoModel::create([
            "customer_id" => auth_user()->cust_id,
            "order_id" => $request['order_id'],
            "order_order_id" => $request['order_order_id'],
            "instamojo_id" => $response['id'],
            "instamojo_id_crc32" => crc32($response['id']),
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

    private function updateInstamojo($response, $status, $payment_status, $order_id)
    {
        $instamojo = InstamojoModel::where('order_id', $order_id)
                        ->firstOrFail();
        $data = [
            'payment_status' => $payment_status,
            'status' => $status,
            'response' => json_encode($response),
        ];

        $data['mojo_id'] = $response['payments'][0]['payment_id'] ?? '';
        $data['mojo_id_crc32'] = crc32($data['mojo_id']);
        if($payment_status === 'success')
            {
            $data['currency'] = $response['payments'][0]['currency'] ?? '';
            $data['fees'] = $response['payments'][0]['fees'] ?? '';
        }
        return $instamojo->update($data);
    }

    public function verify($request,$isredirect=true)
    {
        if ($isredirect==true){
        $order = $this->getOrder($request['order_id']);
       try {
            if($order->order_payment_response_received !=0)
            {
                return ["status"=>($order['status_id']=='4'?'Completed':'Failed'),"payments"=>[["status"=>($order['status_id']=='4'?'Completed':'Failed')]]];               
            }
            $company_id = CompanyBranch::where('br_Id', $order->order_branch_id)->first();
            $compid =  $company_id->comp_id ?? 0;
            $companyinstamojo = CompanyInstamojo::getCompanyPaydetails($compid);
            $api = new Instamojo\Instamojo(
                            $companyinstamojo['api_key'],
                            $companyinstamojo['auth_token'],
                            $companyinstamojo['url']
                        );
            $response = $api->paymentRequestStatus($request['id']);                
            return $response;
        }
        catch (\Exception $e) {
            throw new ErrorException($e->getMessage()."--Comapnyid->". $compid."--".$companyinstamojo['api_key']."--".$companyinstamojo['auth_token']."--".$companyinstamojo['url']);
        }
    }else{
        $order = Order::where('order_id', $order_id);
        return ["status"=>($order['status_id']=='4'?'Completed':'Failed'),"payments"=>[["status"=>($order['status_id']=='4'?'Completed':'Failed')]]];
    }
    }

    private function getOrder($order_id)
    {
        return Order::where('order_id', $order_id)    
                    ->select('order_order_id','order_id','order_payment_response_received','status_id','order_branch_id')
                    ->first();
    }
}
