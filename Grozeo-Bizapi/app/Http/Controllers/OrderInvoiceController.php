<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Helpers\HttpCurlCalls;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\ErrorResponse;
use Illuminate\Support\Facades\DB;

class OrderInvoiceController extends Controller
{
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getInvoiceByOrder(Request $request)
    {
        try
        {
            $storegroupid = getHeaderStoreGroup();

            $order = $this->order->where([
                ['storegroup_id', $storegroupid],
                ['order_id', $request->orderId],
                ['order_customer_id', auth_user()->cust_id],
            ])->first();
            if($order)
            {
                $customer = Customer::find($order->order_customer_id);
                $body = [
                    'email'         => $customer->cust_email,
                    'order_id'      => $order->order_id,
                    'Customersname' => $customer->cust_customer_name
                ];
                $invoiceLink = DB::select("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name= 'ORDER_GET_INVOICE'");
                $url = $invoiceLink[0]->cfg_Value;

                $response = (new HttpCurlCalls)->curlCall($url, json_encode($body), 'POST', ['Content-Type: application/json']);

                return new SuccessWithData($response);
            }
            return new ErrorResponse("Invalid Order");
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage());
        }
    }
}