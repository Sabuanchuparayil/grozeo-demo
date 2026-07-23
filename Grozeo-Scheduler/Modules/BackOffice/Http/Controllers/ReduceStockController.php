<?php

namespace BackOffice\Http\Controllers;

use App\Exceptions\MsgException;
use Illuminate\Support\Facades\Log;
use App\Http\Responses\SuccessResponse;
use BackOffice\Http\Requests\ReduceStockRequest;
use BackOffice\Http\Repositories\ReduceStock;


class ReduceStockController
{

    public function __invoke(ReduceStockRequest $request)
    {

        ReduceStock::ResetChildItemsStock($request->parentItem, $request->branch);
        //$strmsg = "Stock updated";
        //return new SuccessResponse($strmsg);

    }

}
