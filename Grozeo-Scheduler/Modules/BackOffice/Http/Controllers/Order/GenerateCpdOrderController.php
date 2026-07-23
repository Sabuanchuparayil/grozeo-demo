<?php

namespace BackOffice\Http\Controllers\Order;

use App\Http\Responses\SuccessResponse;
use BackOffice\Tasks\GenerateCpdOrders;

class GenerateCpdOrderController
{

    public function __invoke()
    {
        $generater = new GenerateCpdOrders();
        $generater();

        return new SuccessResponse('Cpd orders generated successfully');
    }

}
