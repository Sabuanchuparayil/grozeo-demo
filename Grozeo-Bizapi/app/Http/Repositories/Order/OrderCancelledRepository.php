<?php

namespace App\Http\Repositories\Order;
use DateTime;

use App\Models\Order;
use App\Models\Branch;
use App\Sms\SmsSender;
use App\Models\Customer;
use App\Events\OrderHistory;
use App\Models\BlockedItems;
use App\Models\OrderRefunds;
use App\Models\OrderCancelled;
use App\Exceptions\MsgException;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use BackOffice\Models\TransferOrder;
use App\Models\MarginDistributionb2c;
use App\Models\FinanceAutopostingValues;
use BackOffice\Status\CustomerOrderStatus;
use BackOffice\Status\TransferOrderStatus;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
//use App\Http\Repositories\Finascop\StoreFinascop;
use App\Http\Repositories\Finascop\OrderCancelFinascop;
use BackOffice\Http\Repositories\ReduceStock;
use Illuminate\Http\Request;
use App\Http\Repositories\PostingRepository;

class OrderCancelledRepository {

    protected $orderCancelled;
    protected $order;

    protected const ONLINE_PAYMENT = 2;

    protected const SALES_RETURN = 1;

    protected const SALES = 2;

    const COD_WITH_WALLET = 4;
    
    const ONLINE_WITH_WALLET = 5;

    public function __construct(OrderCancelled $orderCancelled, Order $order) {
        $this->orderCancelled = $orderCancelled;
        $this->order = $order;
    }


    public function create($data)
    {
        
        try
        {
            $order = $this->order->where([
                ['order_id', $data['order_id']],
                ['order_customer_id', auth_user()->cust_id],
                ['status_id', '>', 3]
            ])->whereNotIn('status_id', [
                CustomerOrderStatus::OUT_FOR_DELIVERY,
                CustomerOrderStatus::DELIVERED,
                CustomerOrderStatus::DELIVERED_NOT_CONFIRMED,
            ])->first();

            if($order)
            {
                //if (now()->format('Y-m-d') > $order->order_customer_cancel_till) {
                if(new DateTime() > new DateTime($order->order_customer_cancel_till))
                {
                    return new ErrorResponse('Order cannot be cancelled as the order delivery process has been started'); //Time expired
                }
                $data['customer_id'] = auth_user()->cust_id;
                $data['cancelled_by_type'] = 1;
                $data['cancelled_by_id'] = auth_user()->cust_id;
                $data['order_id'] = $order->order_id;
                $data['reason'] = "";

                if ($order->status_id !== CustomerOrderStatus::CANCELLED)
                {
                    DB::transaction(function () use ($order, $data)
                    {
                        $order->order_cancel_date = now();
                        $order->status_id = CustomerOrderStatus::CANCELLED;
                        $order->save();
                        $this->orderCancelled->create($data);
                        event(new OrderHistory($order->order_id, CustomerOrderStatus::CANCELLED, "Cancelled By Customer"));

                        $postReq = new Request();
                        $postReq->setMethod('POST');
                        $postReq->request->add([
                            'order_id' => $order->order_id,
                            'finascopEventRefId'     => config("event_master.cancellation"),
                            'storegroup_id' => (@$order->storegroup_id ? $order->storegroup_id : 0)
                        ]);
                        
                        (new PostingRepository)->finascopPosting($postReq);
                        

                        //order immediate cancelled sms
                        $templatedata = [
                            "order_order_id"    => $order->order_order_id
                        ];
                        app(SmsSender::class)->fetchContentSendSms($templatedata, auth_user()->cust_mobile, 27);
                        
                        if($order->payment_mode == 2 || $order->payment_mode == 5)
                        {
                            $amount = ($order->payment_mode == 2) ? $order->total : ($order->total  - $order->order_wallet_amount);
                            OrderRefunds::create([
                                "order_id"          => $order->order_id,
                                "payment_gateway"   => $order->order_payment_gateway,
                                "amount"            => abs($amount)
                            ]);
                            $order->status_id = CustomerOrderStatus::REFUND_INITIATED;
                            $order->save();
                            event(new OrderHistory($order->order_id, CustomerOrderStatus::REFUND_INITIATED, "Refund Initiated by Grozeo"));
                        }
                        if($order->payment_mode == 3 || $order->payment_mode == 4 || $order->payment_mode == 5)
                        {
                            $model = Customer::find( auth_user()->cust_id );
                            //'1 - for pay on delivery, 2 - for Online Payment, 3 - Wallet, 4 - COD with Wallet,  5 - online with Wallet, 
                            //6 - Online on Delivery, 7 - Cash on delivery'
                            $model->cust_walletbalance += $order->order_wallet_amount;
                            $model->save();

                            $openBalQuery = '(SELECT brcw_closingBalance FROM retaline_customer_wallet_transaction tx1 WHERE tx1.cust_id = '.auth_user()->cust_id.' AND tx1.brcw_id = (SELECT MAX(tx2.brcw_id) FROM retaline_customer_wallet_transaction tx2 WHERE tx2.cust_id = '.auth_user()->cust_id.'))';

                            $openBalQueryData = DB::select($openBalQuery);
                            
                            $branch = DB::table('finascop_branch')->select('br_Name', 'br_storeGroup')->where('br_ID', $order->order_branch_id)->first();
                            $branch_name = @$branch->br_Name;

                            WalletTransaction::create([
                                'cust_id' => auth_user()->cust_id,
                                'refentry_id' => $order->order_id,
                                'brcw_SourceType' => static::SALES_RETURN,
                                'brcw_Amount' => $order->order_wallet_amount,
                                'brcw_AddInfo' => "You Cancelled the Order {$order->order_order_id} from {$branch_name}",
                                'stiid_barcode' => 0,
                                'brcw_OpeningBalance' => (@$openBalQueryData[0]->brcw_closingBalance) ? $openBalQueryData[0]->brcw_closingBalance : 0
                            ]);

                            // wallet credited sms
                            $templatedata = [
                                "amount"    => $order->order_wallet_amount
                            ];
                            app(SmsSender::class)->fetchContentSendSms($templatedata, auth_user()->cust_mobile, 29);
                        }
                        ReduceStock::orderCancelled($order->order_id);
                        
                    });
                    return new SuccessResponse('Order cancelled successfully');
                }
                else
                {
                    return new ErrorResponse('Order already cancelled');
                }
            }
            return new ErrorResponse('Invalid Operation'); //order not available or invalid status error
        }
        catch (\Exception $e)
        {
            // info("OrderCancelledRepository ERROR");
            // info($e);
            return new ErrorResponse($e->getMessage()); //exception error
        }
    }

}
