<?php
namespace App\Http\Controllers\Driver\Order;

use App\Http\Controllers\Controller;
use App\Http\Responses\ErrorResponse;
use App\Http\Repositories\Driver\OrderListRepository;

class OrderListController extends Controller
{
    protected $orderRepo;

    public function __construct(OrderListRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }
    
    /**
     * Fetch Pending & In Progress orders
     *
     */
    public function orders($type)
    {
        switch ($type)
        {
            case 'pending':
                return $this->orderRepo->pendingOrders();
                break;
            case 'in-progress':
                return $this->orderRepo->inProgressOrders();
                break;
            case 'delivered':
                return $this->orderRepo->deliveredOrders();
                break;
            default:
                return new ErrorResponse("Operation failed");
                break;
        }
    }
}
