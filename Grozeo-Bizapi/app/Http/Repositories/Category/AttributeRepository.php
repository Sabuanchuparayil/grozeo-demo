<?php
namespace App\Http\Repositories\Category;

use App\Http\Responses\{
    ErrorResponse,
    SuccessWithData
};
use Illuminate\Support\Facades\DB;

class AttributeRepository
{
    public function __construct()
    {
    }

    /**
     * get attributes by category repo
     * call SP AttributeSubCategoryMapping()
     * request categoryID
     * returns array of attributes
    */
    public function getCategoryAttributes($id)
    {
        $getAttributes = DB::select("CALL AttributeSubCategoryMapping(?)", [$id]);

        // convert single array in the query response to grouped array
        $response = $this->formatAttributeList($getAttributes);
        
        return new SuccessWithData($response);
    }

    /**
     * get attributes by category repo
     * call SP AttributeSubCategoryMapping()
     * request categoryID
     * returns array of attributes
    */
    public function getProductAttributes($id)
    {
        $getAttributes = DB::select("CALL AttributeProductMapping(?)", [$id]);

        // convert single array in the query response to grouped array
        $response = $this->formatAttributeList($getAttributes);
        
        return new SuccessWithData($response);
    }

    private function formatAttributeList($attributes)
    {
        $response = collect($attributes);
        $response = $response->groupBy('id')->map(function ($items) 
        {
            $first = $items->first();
            return [
                'id'        => $first->id,
                'name'      => $first->name,
                'values'    => $items->filter(function ($i)
                {
                    return  @$i->valueID !== null;
                })
                ->map(function ($i)
                {
                    return [
                        'id'    => $i->valueID,
                        'value' => $i->valueName
                    ];
                })
                ->values()->all() ?: null
            ];
        })->values();

        return $response;
    }
}
