<?php

namespace BackOffice\Http\Controllers\Transfer;

use App\Http\Responses\SuccessResponse;
use BackOffice\Tasks\Generate3TierTransferRequests;
//use BackOffice\Http\Requests\TransferRequestRequest;
class Generate3TierTransferRequestController
{
    
    public function __invoke()
    {
        $generater = new Generate3TierTransferRequests();
        $generater();

        return new SuccessResponse('Transfer Requests generated successfully');
    }

}
