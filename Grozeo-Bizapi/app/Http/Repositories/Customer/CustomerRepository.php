<?php

namespace App\Http\Repositories\Customer;
use stdClass;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

use App\Models\Customer;
use App\Models\DeliveryInfo;
use App\Models\WalletTransaction;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;

class CustomerRepository
{
    protected $customer;
    protected $deliverinfo;

    public function __construct(Customer $customer,DeliveryInfo $deliverinfo)
    {
        $this->customer = $customer;
        $this->deliverinfo=$deliverinfo;
    }

    public function edit($request)
    {

        $data = $request->validated();

        $this->customer::where('cust_id', '=',auth()->user()->cust_id)
        ->update($this->prepareData($data));

        $this->deliverinfo::where('deli_is_primary',1)
        ->where('deli_customer_id',auth()->user()->cust_id)->update(
            $this->deliveryData($data)
        );

        $std=new stdClass();
        $std->cust_email=$request['email'];

        $std->cust_customer_name=$request['name'];
        $std->deliverInfo=array(
            "deli_delivery_pin"=>isset($request['pincode'])?$request['pincode']:"",
            "deli_post" =>isset($request['post'])?$request['post']:"",
            "deli_state" =>$request['state'],
            "deli_latitude" =>$request['latitude'],
            "deli_longitude" =>$request['longitude'],
            "deli_contact_no" =>$request['mobile'],
            "deli_house_name" =>$request['house_name'],
            "deli_house_no" =>isset($request['house_no'])?$request['house_no']:"",
            "deli_land_mark" =>$request['land_mark'],
            "deli_is_primary" =>1,
          );





        return $std;

    }

    public function get()
    {
        $data=auth()->user();
        if($data)
        {
            $data['address']=$this->deliverinfo::where('deli_customer_id', '=',auth()->user()->cust_id)->where('deli_is_primary',1)->first();
        }
        return  $data;
    }

    public function prepareData($data)
    {
        return [


            'cust_mobile'   => $data['mobile'],
            'cust_email' => $data['email'],
            'cust_customer_name' => $data['name'],

        ];
    }
    public function deliveryData($data)
    {
        return [


            'deli_name' => $data['name'],
            'deli_contact_no' => $data['mobile'],
            'deli_customer_id' => auth()->user()->cust_id,
            'deli_delivery_pin'   => isset($data['pincode'])?$data['pincode']:"",
            'deli_branch_id' => 1,
            'deli_house_no' => isset($data['house_no'])?$data['house_no']:"",
            'deli_house_name' => $data['house_name'],
            'deli_land_mark' => $data['land_mark'],
            'deli_post' =>isset($data['post'])?$data['post']:"",
            'deli_city' =>isset($data['city'])?$data['city']:"",
            'deli_state' =>isset($data['state'])?$data['state']:"",
            'deli_status' => 'active',
            'deli_is_primary' => 1,
            'deli_latitude'=>isset($data['latitude'])?$data['latitude']:NULL,
            'deli_longitude'=>isset($data['longitude'])?$data['longitude']:NULL,
        ];
    }
    private function walletDetails($type)
    {
        return WalletTransaction::where('cust_id', auth_user()->cust_id)
                                ->where('brcw_SourceType', $type)
                                ->select('refentry_id', 'brcw_AddInfo', 'brcw_CreatedOn','brcw_Amount')
                                ->get()
                                ->toArray();
       
    }
    private function getOrder($orderIds)
    {
        return Order::whereIn('order_id', $orderIds)
                    ->select('order_id','order_order_id', 'order_branch_id')
                    ->get();
    }
    public function getWallet()
    {
        $rs = auth_user()->cust_walletbalance ?? 0;
        $defCurrency = config('app.def_currency_symbol');
        return "{$defCurrency} {$rs}";
    }

    public function getWalletHistory()
    {
        $spent_det = $this->walletDetails(2);
        $recieve_det = $this->walletDetails(1);
        return [
            "wallet_balance" => auth_user()->cust_walletbalance,
            "spent_details" => count($spent_det) > 0 ? $this->findSpentDetails($spent_det) : [],
            "recieve_details" => count($recieve_det) > 0 ? $this->findRecieveDetails($recieve_det) : [],
        ];

    }
    private function findSpentDetails(array $spent_det)
    {
        $order = array_column($spent_det, 'refentry_id');
        $orders = $this->getOrder($order)->toArray();
        $order_ids = array_column($orders, 'order_order_id', 'order_id');
        

        return array_map(function($item) use ($order_ids) {
                        $order_id = array_key_exists($item['refentry_id'], $order_ids) ? $order_ids[$item['refentry_id']] : "";
                        $defCurrency = config('app.def_currency_symbol');
                        return [
                        "order_id" => $order_id,
                        "amount_added" => $amt = abs(round($item['brcw_Amount'], 2)), //, '.', ''),
                        "order_date" => Date("d/m/Y", strtotime($item['brcw_CreatedOn'])),
                        "reason" => "You spent {$defCurrency} {$amt}"
                        ];
                            }, $spent_det);
    }

    private function findRecieveDetails(array $receive)
    {
        //$msg = array_column($receive, 'brcw_AddInfo','refentry_id');
        $date = array_column($receive, 'brcw_CreatedOn','refentry_id');
        $order = array_keys($date);
        $orders = $this->getOrder($order)->toArray();
        $order_ids = array_column($orders, 'order_order_id', 'order_id');

        $order_items = WalletTransaction::select(DB::raw('sum(brcw_Amount) as amount,refentry_id, count(refentry_id) as count'))
                                            ->groupBy('refentry_id')
                                            ->get()
                                            ->toArray();
        $rs = array_column($order_items, 'amount','refentry_id');
        $count = array_column($order_items, 'count','refentry_id');

        return array_map(function($order_id) use ($date, $count, $order_ids, $rs) {
            $orderId = array_key_exists($order_id, $order_ids) ? $order_ids[$order_id] : "";
            $amount = array_key_exists($order_id, $rs) ? $rs[$order_id]: 0;
            $dt = array_key_exists($order_id, $date) ? $date[$order_id]: "";
            $count_val = array_key_exists($order_id, $count) ? $count[$order_id]: 0;
            $defCurrency = config('app.def_currency_symbol');
            //$message = array_key_exists($order_id, $msg) ? $msg[$order_id]: "";
            return [
            "order_id" => $orderId,
            //"amount_added" => $amt = round($amount, 2, '.', ''),
		    "amount_added" => $amt = round($amount, 2), // '.', ''),
            "order_date" => $dt ? Date("d/m/Y", strtotime($dt)) : "",
            "reason" => "You cancelled {$count_val} order and received {$defCurrency} {$amt}"
            ];
                }, $order);
    }


    public function getWalletHistoryFiltered($request)
    {
        $walletDetails = WalletTransaction::where('cust_id', auth_user()->cust_id)
            ->select('refentry_id', 'brcw_AddInfo', 'brcw_SourceType', 'brcw_OpeningBalance', 'brcw_CreatedOn','brcw_Amount', 'brcw_closingBalance')
            ->orderBy('brcw_CreatedOn', 'DESC');
        if(!empty($request))
        {
            if(@$request['from_date'])
            {
                $walletDetails = $walletDetails->whereDate('brcw_CreatedOn', '>=', date('Y-m-d', strtotime($request['from_date'])));
            }
            if(@$request['to_date'])
            {
                $walletDetails = $walletDetails->whereDate('brcw_CreatedOn', '<=', date('Y-m-d', strtotime($request['to_date'])));
            }
        }
        $walletDetails = $walletDetails->get()->toArray();
        return $this->walletResponse($walletDetails);
    }
    private function walletResponse($walletDetails)
    {
        $order = array_column($walletDetails, 'refentry_id');
        $orders = $this->getOrder($order)->toArray();
        $order_ids = array_column($orders, 'order_order_id', 'order_id');
        $branch_ids = array_column($orders, 'order_branch_id', 'order_id');

        $returnData = array_map(function($item) use ($order_ids, $branch_ids)
        {
            $storegroupid = getHeaderStoreGroup();
            $order_id = array_key_exists($item['refentry_id'], $order_ids) ? $order_ids[$item['refentry_id']] : "";
            $branch_id = array_key_exists($item['refentry_id'], $branch_ids) ? $branch_ids[$item['refentry_id']] : "";
            $branch_name = '';
            if($branch_id != '')
            {
                $branch = DB::table('finascop_branch')->select('br_Name', 'br_storeGroup')->where('br_ID', $branch_id)->first();

                $branch_name = @$branch->br_Name;
                if($branch)
                {
                    if(($storegroupid > 0) && ($storegroupid != $branch->br_storeGroup))
                    {
                        $branch_name = 'Other branch';
                    }
                }
            }
            $reason = ($item['brcw_SourceType'] == 1) ? 'Cancellation of Order by you' : 'Purchase of Order by you';
            if(($item['brcw_SourceType'] == 3))
            {
                $reason = '';
            }
            return [
                "order_id"          => $order_id,
                "order_branch_id"   => $branch_id,
                "branch_name"       => $branch_name,
                "amount_added"      => round($item['brcw_Amount'], 2),
                "order_date"        => Date("d/m/Y H:i", strtotime($item['brcw_CreatedOn'])),
                "reason"            => $item['brcw_AddInfo'],
                'opening_balance'   => $item['brcw_OpeningBalance'],
                'closing_balance'   => $item['brcw_closingBalance']
            ];
        }, $walletDetails);

        return [
            "wallet_balance" => auth_user()->cust_walletbalance,
            "wallet_details" => count($returnData) > 0 ? $returnData : [],
        ];
    }
    public function deactivateCustomerAccount()
    {
        $outs = [
            'status'    => 'failed',
            'message'   => 'User Not Logged In'
        ];
        if(@auth()->user()->cust_id != '')
        {
            $outs['message'] = 'Mobile number not found';
            if(@auth()->user()->cust_mobile != '')
            {
                $userUpdate = [
                    'cust_mobile'  => (auth()->user()->cust_mobile.'_da')
                ];
                $updates = $this->customer::where('cust_id', '=',auth()->user()->cust_id)->update($userUpdate);
                $outs['message'] = 'Unable to deactivate account';
                if($updates > 0)
                {
                    $outs['status'] = 'success';
                    $outs['message'] = 'Account Deactivated.';
                    auth()->logout();
                }
            }
        }
        return $outs;
    }

    public function ageVerification($request)
    {
        $verify = $this->customer::where('cust_id', auth()->user()->cust_id)->update([
            'age_verified'  => $request['status']
        ]);
        return $verify;
    }
}
