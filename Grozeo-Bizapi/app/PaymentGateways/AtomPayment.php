<?php

namespace App\PaymentGateways;

use App\Models\Order;
use App\Domains\Atom\Atom;
use App\Models\CompanyAtom;
use App\Domains\Atom\AtomAES;
use App\Models\CompanyBranch;
use App\Exceptions\MsgException;
use App\Models\Payment\AtomModel;
use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use App\Domains\EasyPay\AesForJava;
use App\Models\Payment\InstamojoModel;
use App\Domains\Atom\AtomTransactionRequest;
use App\Domains\Atom\AtomTransactionResponse;
use App\PaymentGateways\InterfacePaymentGateway;

class AtomPayment implements InterfacePaymentGateway
{
    protected const ONLINE_PAYMENT = 2;

    const PAYMENT_SUCCESS = 1;

    const PAYMENT_FAILED = 2;
    private function getCallBackURL($compid){
        $userAgentType=getUserAgentType(); 
        $compdets = DB::table('finascop_branch_company')
        ->select('finascop_branch_company.comp_id as comp_id','fb.comp_paymentgateway as comp_paymentgateway')
        ->join('finascop_company as fb', 'fb.comp_id', 'finascop_branch_company.comp_id')                    
        ->where('finascop_branch_company.br_Id', auth_user()->deli_branch_id)
        ->first() ;
        $comp_paymentgateway =  $compdets->comp_paymentgateway ?? config('paymentgateway.default');
        $redirect_url=($userAgentType=="web")? config('paymentgateway.web_redirect_url'):route('payment.result',['paymentgateway' => $comp_paymentgateway . '-' . $compid]);
        $redirect_url=route('payment.result',['paymentgateway' => $comp_paymentgateway . '-' . $compid]);
        return $redirect_url;    
    }
    private function getToken($companyatom){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $companyatom['tokenurl']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);  
        $resp = json_decode($output);
        
        return $resp->token;
    }
    public function processPayment($request)
    {
       // try {
            //createAtomRequest($cid = '', $rid = '', $crn = '', $amt = '', $ver = '', $typ = '', $cny = '', $rtu = '', $ppi = '', $re1 = 'MN', $re2 = '', $re3 = '', $re4 = '', $re5 = '')
            $order = $this->getOrder($request['order_id']);
            $company_id = CompanyBranch::where('br_Id', $order->order_branch_id)->first();
            $compid =  $company_id->comp_id ?? 0;
            $companyatom = CompanyAtom::getCompanyPaydetails($compid);

            //$token = $this->getToken($companyatom);
            $atomParams = [];
            $atomParams['login'] = $companyatom['login'];
            $atomParams['pass'] = $companyatom['pass'];
            $atomParams['ttype'] = $companyatom['ttype'];
            $atomParams['prodid'] = $companyatom['prodid'];
            $atomParams['txncurr'] = $companyatom['txncurr'];
            $atomParams['clientcode'] = $companyatom['clientcode'];
            $atomParams['custacc'] = $companyatom['custacc'];
            $atomParams['reqhashkey'] = $companyatom['reqhashkey'];
            $atomParams['resphashkey'] = $companyatom['resphashkey'];
            $atomParams['aesreqhashkey'] = $companyatom['aesreqhashkey'];
            $atomParams['aesreqhashkeysalt'] = $companyatom['aesreqhashkeysalt'];
            $atomParams['aesresphashkey'] = $companyatom['aesresphashkey'];
            $atomParams['aesresphashkeysalt'] = $companyatom['aesresphashkeysalt'];
            $atomParams['paymenturl'] = $companyatom['paymenturl'];
            $atomParams['enquiryurl'] = $companyatom['enquiryurl'];
            $atomParams['mode'] = $companyatom['mode'];

            $atomParams["txnid"] = microtime(1)*10000;
            $atomParams["txnscamt"] = $order->total;
            $atomParams["amt"] = $order->total;
            $atomParams["date"] = date("d/m/Y H:i:s");
            $atomParams["ru"] = $this->getCallBackURL($compid);
            //$atomParams["ru"] = "http://retaline.api.dev.velosit.in/payment/result/redirect/atom-1";
            $atomParams["bankid"] = 2001;

            $atomParams["udf1"] = auth_user()->cust_customer_name;
            $atomParams["udf2"] = auth_user()->cust_email;
            $atomParams["udf3"] = auth_user()->cust_mobile;
            $atomParams["udf9"] = $request['order_order_id'];


            $atom = new AtomTransactionRequest();
            
            $atom->setRequestEncypritonKey($atomParams['aesreqhashkey']);
            $atom->setResponseEncypritonKey($atomParams['aesresphashkey']);
            $atom->setSalt($atomParams['aesreqhashkeysalt']);
            $atom->setReqHashKey($atomParams['reqhashkey']);
            $atom->setRespHashKey( $atomParams['resphashkey']);
            $atom->setLogin($atomParams['login']);
            $atom->setPassword($atomParams['pass']);
            $atom->setTransactionType($atomParams['ttype']);
            $atom->setProductId($atomParams['prodid'] );
            $atom->setAmount($atomParams["amt"]);
            $atom->setTransactionCurrency($atomParams['txncurr']);
            $atom->setTransactionAmount($atomParams["txnscamt"]);
            $atom->setTransactionId($atomParams["txnid"]);
            $atom->setTransactionDate($atomParams["date"]);
            $atom->setCustomerAccount($atomParams['custacc']);
            $atom->setCustomerName(auth_user()->cust_customer_name);
            $atom->setCustomerEmailId(auth_user()->cust_email);
            $atom->setCustomerMobile(auth_user()->cust_mobile);
            $atom->setCustomerBillingAddress("NA");
            $atom->setReturnUrl($atomParams["ru"]);
            $atom->setMode($atomParams['mode']);
            //$atom->setTransactionUrl($atomParams['paymenturl']);
            $atom->setUrl($atomParams['paymenturl']) ;
            $atom->setClientCode($atomParams['clientcode']) ;
            $atom->setUdf9($atomParams["udf9"]) ;

            $url = $atom->getPGUrl();
            $atomParams['longurl'] = $url;
            $this->addAtom($atomParams, $request);
            return [
                'id' => $request['order_order_id'],                
                'longurl'  =>  $url,
            ];
       /* } catch (\Exception $e) {
            throw new ErrorException($e->getMessage());
        }*/
    }

    public function paymentComplete($request,$compid){
        //decrypt($data = null, $key = null, $blockSize = null, $mode = null)
        $companyatom = CompanyAtom::getCompanyPaydetails($compid);
        $transactionResponse = new AtomTransactionResponse();
        $transactionResponse->setRespHashKey($companyatom['resphashkey']);
        $transactionResponse->setResponseEncypritonKey($companyatom['aesresphashkey']);
        $transactionResponse->setSalt($companyatom['aesresphashkeysalt']);

        $response = $transactionResponse->decryptResponseIntoArray($_POST['encdata']);

     
       
       // $paymentId = $request->input('payment_request_id');
        $order = $this->findOrder($response['udf9']);
        //Verify and respond
       // $responsestr = $this->verify($response['udf9'],$order,$companyatom);
        //$response=[];
       // parse_str($responsestr,$response);

        $status = $response['f_code'] ?? '';
        $ref_id = $response['mmp_txn'] ?? '';
        $amount = $response['amt'] ?? '';
        $trn = $response['bank_txn'] ?? '';
        if(strtolower($status) === 'ok'){
            $status = 'success';
            $this->updateAtom($response, $status, static::PAYMENT_SUCCESS, $order->order_id,$trn);
        }else{
            $status = 'failed' ;
            $this->updateAtom($response, 'Failed', static::PAYMENT_FAILED, $order->order_id,$trn);
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

    private function addAtom($response, $request)
    {
       return AtomModel::create([
            "customer_id" => auth_user()->cust_id,
            "order_id" => $request['order_id'],
            "order_order_id" => $request['order_order_id'],
            "atomreq_id" => $response['txnid'],
            "atomreq_id_crc32" => crc32($response['txnid']),
            "phone" => $response['udf3'],
            "email" => $response['udf2'],
            "name" => $response['udf1'],
            "amount" => $response['txnscamt'],
            "purpose" => '',
            "payment_status" => 0,
            "long_url" => $response['longurl'],
            "inst_created_at" => $response['date'],
            "inst_modified_at" => $response['date'],
            "redirect_url" => $response['ru'],
        ]);
    }
    private function updateAtom($response, $status, $payment_status, $order_id, $trn)
    {
        $instamojo = AtomModel::where('order_id', $order_id)
                        ->firstOrFail();
        $data = [
            'payment_status' => $payment_status,
            'status' => $status,
            'response' => json_encode($response),
        ];

        $data['atom_id'] =  $response['mmp_txn'] ?? '';
        $data['atom_id_crc32'] = crc32($data['atom_id']);
        $data['bank_trn_id'] = $trn;
       /* if($payment_status === 'success')
            {
            $data['currency'] =  $response['CNY'] ?? '';
            //$data['fees'] =  $response['BRN'] ?? '';
        }*/
        return $instamojo->update($data);
    }
    public function verify($paymentreqid,$order,$companyatom)
    {    
       
       try {
            if($order->order_payment_response_received !=0)
            {
                return ["status"=>($order['status_id']=='4'?'Completed':'Failed'),"payments"=>[["status"=>($order['status_id']=='4'?'Completed':'Failed')]]];               
            }
            $atomParams = [];
            $atomParams["cid"] = $companyatom['cid'];
            $atomParams["typ"] = $companyatom['typ'];
            $atomParams["ver"] = $companyatom['ver'];
            $atomParams["rid"] = $paymentreqid;
            $atomParams["crn"] = $paymentreqid;

            //callAtomEnquiry($cid = '', $rid = '', $crn = '', $ver = '', $typ = '') 
            $atom = new Atom($companyatom['enquiryurl'],$companyatom['checksumkey'],$companyatom['encryptionkey']);
            //echo 'Below is easy pay';
            $response = $atom->callAtomEnquiry($atomParams["cid"], $atomParams["rid"], $atomParams["crn"], $atomParams["ver"], $atomParams["typ"]);    
           // $atomenquiryresponse = $atom->callEasyPayEnquiry('6123','16104509498397','16104509498397','1.0','Test'); 
            //$response = (new AesForJava)->decrypt($atomenquiryresponse,$companyatom['encryptionkey']);
            return $response;
        }
        catch (\Exception $e) {
            throw new ErrorException($e->getMessage()."--Comapnyid->". $compid."--".$companyinstamojo['api_key']."--".$companyinstamojo['auth_token']."--".$companyinstamojo['url']);
        }
    
    }  
    private function getOrder($order_id)
    {
        return Order::where('order_id', $order_id)    
                    ->select('order_order_id','order_id','order_payment_response_received','status_id','order_branch_id','total')
                    ->first();
    }   
}
