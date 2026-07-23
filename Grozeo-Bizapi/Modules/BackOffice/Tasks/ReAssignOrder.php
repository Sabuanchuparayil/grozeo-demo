<?php

namespace BackOffice\Tasks;


use App\Models\Order;
use Illuminate\Support\Facades\DB;
use BackOffice\Tasks\OrderAssigner;
use Illuminate\Support\Facades\Log;
use BackOffice\Models\TransferOrder;
use BackOffice\Models\BoyOrderRequest;
use BackOffice\Status\TransferOrderStatus;
use BackOffice\Status\BoyOrderRequestStatus;

class ReAssignOrder extends OrderAssigner
{
    /**
     * Timeout frequency in minutes.
     * 
     * @var int
     */
    protected const TIME_OUT_FREQUENCY = 3;

    /**
     * List of pending open order ids.
     *
     * @var array
     */
    protected $pendingOpenOrderIds;
    
    /**
     * List of pending open order int ids.
     *
     * @var array
     */
    protected $pendingOpenOrderIntIds;
     
    public function __invoke()
    {

        try{
        DB::transaction(function ()  {
        $this->clearGodownBoys();
        $pendingOpenOrders = $this->getPendingOpenOrders();
        $this->pendingOpenOrderIds = $pendingOpenOrders->pluck('order_pk_id');
        $this->pendingOpenOrderIntIds = $pendingOpenOrders->pluck('id');
        $this->makePendingOrdersTimedOut();
        $this->changeOrderStatus();
        $this->getPollNoResponseOrders();
        $this->openOrders = $this->getOpenOrders();
        $this->godownBoys = $this->getGodownBoys();
        $this->tempAssignedOrders = $this->getTempAssignedOrders($this->pendingOpenOrderIds);

        $this->processOrders();
        });
    }catch (\Exception $e)
    {
        info("ReAssignOrder ERROR => ".$e->getMessage());
    }
    }

    /**
     * {@inheritDoc}
     */
    protected function getOpenOrders()
    {
       //DB::enableQueryLog(); 
        $data =  TransferOrder::select('fsto_id', 'fsto_uid', 'fsto_source', 'fsto_sourcetype', 'fsto_openingtime')
        ->whereIn('fsto_id', $this->pendingOpenOrderIds)
        ->whereIn('fsto_ordertype', [0,1,2,3])
        ->where('fsto_openingtime', '<=', now()->format('Y-m-d H:i:s'))
        ->where(function ($query) {
            $query->where('fsto_status', TransferOrderStatus::TO_MANUALLY_ASSIGN)
            ->orWhere('fsto_status', TransferOrderStatus::POLL_NO_RESPONSE)
                ->orWhere('fsto_status', TransferOrderStatus::POLL_REJECTED);
        })       
        ->get();
        return $data;
    }

    /**
     * Get the open pending orders.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getPendingOpenOrders()
    {
        return BoyOrderRequest::select('id', 'boy_id', 'branch_id', 'order_id','order_pk_id')
            ->where(function ($query) {
                $query->where('status', BoyOrderRequestStatus::REQUEST_SENT);
            })
            ->where('created_at', '<', now()->subMinutes(static::TIME_OUT_FREQUENCY))
            ->get();
    }
    
    /**
     * Get the open pending orders.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getPollNoResponseOrders()
    {
        //DB::enableQueryLog(); 
        $items =  TransferOrder::select('fsto_polled_request_id')
        ->where('fsto_status', TransferOrderStatus::POLL_NO_RESPONSE)    
        ->whereIn('fsto_ordertype', [0,1,2,3])  
        ->whereNotIn('fsto_id', $this->pendingOpenOrderIds)   
        ->get();
        
        $norepsonseitems =  BoyOrderRequest::select('order_pk_id')
        ->whereIn('id',  $items->pluck('fsto_polled_request_id'))
        ->get();

        $norepsonseitems =  $norepsonseitems->pluck('order_pk_id');
        $this->pendingOpenOrderIds = $this->pendingOpenOrderIds->merge($norepsonseitems);
        
    }

    /**
     * Change the order status to timed out.
     *
     * @return void
     */
    public function makePendingOrdersTimedOut()
    {
        BoyOrderRequest::whereIn('id', $this->pendingOpenOrderIntIds)
            ->update(['status' => BoyOrderRequestStatus::TIMED_OUT]);
    }

    /**
     * Change order status to no response.
     *
     * @return void
     */
    public function changeOrderStatus()
    {
        TransferOrder::whereIn('fsto_id', $this->pendingOpenOrderIds)
            ->where('fsto_status', TransferOrderStatus::GODOWN_BOY_POLLED)
            ->update(['fsto_status' => TransferOrderStatus::POLL_NO_RESPONSE]);
        foreach($this->pendingOpenOrderIds as $pendingOpenOrderId){
            TransferOrder::reverseStatusUpdate($pendingOpenOrderId,TransferOrderStatus::POLL_NO_RESPONSE);
            $order =  TransferOrder::where('fsto_id', $pendingOpenOrderId)
                        ->select('fsto_ordertype','fstr_id')
                        ->first() ; 
            if($order->fsto_ordertype == 1){
                Order::where('order_id', $order->fstr_id)                       
                    ->update(['status_id' => 7]);
            }
        }
    }

}
