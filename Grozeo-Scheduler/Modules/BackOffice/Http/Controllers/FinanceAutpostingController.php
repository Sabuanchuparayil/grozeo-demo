<?php
namespace BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use App\Http\Repositories\PostingRepository;
use App\Models\{
    Order,
    FinanceAutopostingValues
};

class FinanceAutpostingController
{
    public function __construct() {}

    public function updateAutopostings(Request $request)
    {
        try
        {
            $order = Order::where('order_id', $request->order_id)->first();
            if($order)
            {
                $updateFields = null;
                if(!empty($updateFields))
                {
                    $autoPosting = FinanceAutopostingValues::where('order_id', $request->order_id)->update($updateFields);

                    $postReq = new Request();
                    $postReq->setMethod('POST');
                    $postReq->request->add([
                        'order_id'              => $order->order_id,
                        'finascopEventRefId'    => $request->reference_id,
                        'storegroup_id'         => ($order->storegroup_id ? $order->storegroup_id : 0)
                    ]);

                    (new PostingRepository)->finascopPosting($postReq);
                    return new SuccessResponse("Data Updated");
                }
                else
                {
                    return new ErrorResponse("Some Errod Occured"); 
                }
            }
            else
            {
                return new ErrorResponse("Order not found"); 
            }
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
    private function getOrderData($order, $type)
    {
        $outs = [];
        switch ($type)
        {
            case 'pickup':
            break;
            
            case 'delivery':
            break;
        }
        return $outs;
    }
}
