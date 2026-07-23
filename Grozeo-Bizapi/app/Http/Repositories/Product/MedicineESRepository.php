<?php

namespace App\Http\Repositories\Product;

use Illuminate\Http\Request;
use App\Helpers\CollectionHelper;
use Carbon\carbon;
use Elasticsearch;

class MedicineESRepository
{

    public function __construct()
    {
        
    }

    public function search(Request $filter)
    {
    //    $query = [
    //         'bool' => [
    //             'should' => [
    //                 [
    //                     'fuzzy' => [
    //                         'stit_SKU' => [
    //                             'value' => $filter->param,
    //                             'fuzziness' => 5
    //                         ]
    //                     ]
    //                 ]
    //             ]
    //         ]
    //     ];

        $query = [
            'query_string' => [
                'query' => '*'.$filter->param.'*',
                'fields' => ["stit_SKU"]
            ]
        ];

        $all_search_params = [
            'index' => 'mypharm_itemmaster',
            'body'  => [
                'from' => 0, 
                'size' => 10000,
                'query' => $query
            ]
        ];

        $itemmaster_products = Elasticsearch::search($all_search_params);
        $result_medicines = [];

        foreach ($itemmaster_products['hits']['hits'] as $each_medicine) {
            $each_medicine = $each_medicine['_source'];
            $result_medicines[] = [
                "stit_ID" => $each_medicine["stit_ID"],
                "stit_SKU" => $each_medicine["stit_SKU"],
                "isMedicine" => $each_medicine["isMedicine"]
            ];
        }

        return $result_medicines;
    }
}
