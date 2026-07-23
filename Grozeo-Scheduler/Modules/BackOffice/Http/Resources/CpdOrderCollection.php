<?php

namespace BackOffice\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CpdOrderCollection extends ResourceCollection
{
    protected $boyOrderId;

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = CpdOrderResource::class;

    /**
     * Create a new CustomerOrder resource.
     *
     * @param mixed $resource
     * @param string $boyOrderId
     */
    public function __construct($resource, $boyOrderId)
    {
        parent::__construct($resource);
        $this->boyOrderId = $boyOrderId;
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
            'items' => $this->collection,
            'boy_order_id' => $this->boyOrderId,
            'no_item_barcode' => config('app.no_item_barcode')
        ];
    }
}
