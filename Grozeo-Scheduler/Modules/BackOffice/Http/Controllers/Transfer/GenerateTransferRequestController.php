<?php

namespace BackOffice\Http\Controllers\Transfer;

use App\Http\Responses\SuccessResponse;
use BackOffice\Tasks\GenerateTransferRequests;
//use BackOffice\Http\Requests\TransferRequestRequest;
class GenerateTransferRequestController
{
    
    public function __invoke()
    {
        $generater = new GenerateTransferRequests();
        $generater();

        return new SuccessResponse('Transfer Requests generated successfully');
    }

}
