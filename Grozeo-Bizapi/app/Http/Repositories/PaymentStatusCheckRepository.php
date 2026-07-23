<?php

namespace App\Http\Repositories;

use App\Models\Order;

class PaymentStatusCheckRepository
{
     public function paymentStatusCheck($orderID = NULL, $eventType)
     {
          $outs = true;
          if($orderID)
          {
               $orderData = Order::find($orderID);
               if($orderData)
               {
                    switch ($eventType)
                    {
                         case 'success':
                              $outs = ($orderData->order_payment_status == "Success") ? false : true;
                              break;
                         case 'failed':
                              $outs = ($orderData->status_id == 2) ? false : true;
                              break;
                    }
               }
          }
          return $outs;
     }
}