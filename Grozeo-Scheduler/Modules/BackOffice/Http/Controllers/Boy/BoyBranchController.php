<?php
namespace BackOffice\Http\Controllers\Boy;

use Carbon\Carbon;
use App\Http\Responses\{
    ErrorResponse,
    SuccessWithData
};
use BackOffice\Http\Requests\{
    BoyBranchOrderRequest
};
use Illuminate\Support\Facades\DB;
use BackOffice\Models\TransferOrder;

class BoyBranchController
{
    public function getBranchOrders(BoyBranchOrderRequest $request)
    {
        try
        {
            $from = @$request->date['from'];
            $to = @$request->date['to'];
            $boy = auth_user();
            if(@$boy->allowStoreClose == 0)
            {
                return new ErrorResponse("Invalid permissions");
            }
            $boyOrder = TransferOrder::select($this->selectFields())
            ->with(['fstosStatus','boy:id,name,lname'])
            ->with(['order' => function($q) {
                $q->select('order_id', 'order_order_id', 'total', 'order_customer_id')
                ->with('customer:cust_id,cust_customer_name,cust_email,cust_mobile')
                ->withCount([
                    'orderItems as total_items',
                    'orderItems as packed_items' => function($qu) {
                        $qu->where('order_item_status', 1);
                    }
                ]);
            }])
            ->where([
                ['fsto_source', $boy->branch_id],
                ['fsto_iscancelunpacked', 0]
            ])
            ->when($from, function ($q) use ($from){
                $q->whereDate("fsto_createdOn", ">=", $from);
            })
            ->when($to, function ($q) use ($to){
                $q->whereDate("fsto_createdOn", "<=", $to);
            })
            ->whereIn('fsto_ordertype', [0, 1, 2])
            ->orderBy('fsto_createdOn', 'DESC')
            ->paginate(10);

            return new SuccessWithData($boyOrder);
        }
        catch (\Exception $e)
        {
            return new ErrorResponse("Operation failed"); 
        }
    }
    private function selectFields()
    {
        return [
            "fsto_id",
            "fsto_uid",
            "fstr_id",
            "fsto_source",
            "fsto_status",
            "fsto_destination",
            "fsto_assigned_boy",
            "fsto_ordertype",
            "fsto_createdOn"
        ];
    }
}