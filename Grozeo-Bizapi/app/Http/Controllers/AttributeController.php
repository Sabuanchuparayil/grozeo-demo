<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\ErrorResponse;
use App\Http\Repositories\Category\AttributeRepository;

class AttributeController extends Controller
{
    protected $attrRepo;
    public function __construct(AttributeRepository $attrRepo)
    {
        $this->attrRepo = $attrRepo;
    }

    /**
     * Attributes Listing API
     * request
     *  type (product/category)
     *  id (product id/category id)
     * returns attribute array/ error response
     * try catch for handling exceptions
    */
    public function getAttributes($type, $id)
    {
        try
        {
            switch ($type)
            {
                case 'category':
                    return $this->attrRepo->getCategoryAttributes($id);
                    break;
                case 'product':
                    return $this->attrRepo->getProductAttributes($id);
                    break;
                
                default:
                    return new ErrorResponse("Operation failed");
                    break;
            }
        }
        catch(\Exception $e)
        {
            return new ErrorResponse("Operation failed");
        }
    }
}
