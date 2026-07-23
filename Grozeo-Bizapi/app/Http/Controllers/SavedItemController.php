<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Requests\Wishlist\SaveItemRequest;
use App\Http\Repositories\Wishlist\SavedItemRepository;

class SavedItemController extends Controller
{
    protected $savedItem;

    public function __construct(SavedItemRepository $savedItem)
    {
        $this->savedItem = $savedItem;
    }

    public function create(SaveItemRequest $request)
    {
        $this->savedItem->create($request->validated());
        return new SuccessResponse('Item successfully saved');
    }

    public function get($order_method)
    {
        return new SuccessWithData(
            $this->savedItem->get($order_method)
        );
    }

    public function delete($groupId, $productId,$order_method)
    {
        return new SuccessWithData([
            'product_id' => $this->savedItem->delete($groupId, $productId,$order_method)
        ]);
    }
}
