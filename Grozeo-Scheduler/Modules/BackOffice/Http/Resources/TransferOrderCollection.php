<?php

namespace BackOffice\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TransferOrderCollection extends ResourceCollection
{
    protected $fsto_id;

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = TransferOrderResource::class;

    /**
     * Create a new CustomerOrder resource.
     *
     * @param mixed $resource
     * @param string $boyOrderId
     */
    public function __construct($resource, $boyOrderId, $order, $orderMethod, $restCatCheck, $orderNotes = NULL, $orderCreated, $orderConfirmed, $salesOrderNo, $salesOrderDate)
    {
        parent::__construct($resource);
        $this->boyOrderId = $boyOrderId;
        $this->order = $order;
        $this->orderMethod = $orderMethod;
        $this->restCatCheck = $restCatCheck;
        $this->orderNotes = $orderNotes;
        $this->orderCreated = $orderCreated;
        $this->orderConfirmed = $orderConfirmed;
        $this->salesOrderNo = $salesOrderNo;
        $this->salesOrderDate = $salesOrderDate;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'items'             => $this->collection,
            'boy_order_id'      => $this->boyOrderId,
            'no_item_barcode'   => config('app.no_item_barcode'),
            'status_id'         => $this->order->fsto_status,
            'key'               => md5($this->order->fsto_updateon),
            'alreadypacked'     => $this->order->fsto_isalreadypacked,
            'isCourier'         => $this->orderMethod,
            'hasRestaurant'     => ($this->restCatCheck > 0) ? 1 : 0,
            'orderNotes'        => $this->orderNotes,
            'orderCreated'      => $this->orderCreated,
            'orderConfirmed'    => $this->orderConfirmed,
            'salesOrderNo'      => $this->salesOrderNo,
            'salesOrderDate'    => $this->salesOrderDate,
        ];
    }
}
