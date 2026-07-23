<?php

namespace App\Http\Repositories\Product;

use Illuminate\Http\Request;
use App\Helpers\CollectionHelper;
use Carbon\carbon;
use Elasticsearch;

class SearchESRepository
{

    public function __construct()
    {
        
    }

    public function search(Request $filter)
    {
    $sort_type = SORT_DESC;
        if($filter->sort==2) $sort_type = SORT_ASC;
        
        // $query = [
        //     'bool' => [
        //         'should' => [
        //             [ 'match' => [ 'item_name' => $filter->product_name ] ],
        //             [ 'match' => [ 'brand_name' => $filter->product_name ] ],
        //             [ 'match' => [ 'category_name' => $filter->product_name ] ]
        //         ]
        //     ]
        // ];

        $query = [
            'query_string' => [
                'query' => '*'.$filter->product_name.'*',
                'fields' => ["item_name", "brand_name", "category_name"]
            ]
        ];

        // $query = [
        //     'bool' => [
        //         'should' => [
        //             [ 
        //                 'fuzzy' => [
        //                     'item_name' => [
        //                         'value' => $filter->product_name ,
        //                         'fuzziness' => 5,
        //                         'transpositions' => true,
        //                     ]
        //                 ]
        //             ],
        //             [ 
        //                 'fuzzy' => [
        //                     'brand_name' => [
        //                         'value' => $filter->product_name,
        //                         'fuzziness' => 5,
        //                         'transpositions' => true,
        //                     ]
        //                 ]
        //             ],
        //             [ 
        //                 'fuzzy' => [
        //                     'category_name' => [
        //                         'value' => $filter->product_name,
        //                         'fuzziness' => 5,
        //                         'transpositions' => true,
        //                     ]
        //                 ]
        //             ],
        //         ]
        //     ]
        // ];

        // $query = [
        //     'bool' => [
        //         'must' => [
        //             'multi_match' => [
        //                 'query' => $filter->product_name,
        //                 'fuzziness' => '3',
        //                 'fields' => [
        //                     'item_name',
        //                     'brand_name',
        //                     'category_name'
        //                 ],
        //                 'minimum_should_match' => '75%',
        //                 'type' => 'most_fields'
        //             ]
        //         ]
        //     ]
        // ];

        $unique_search_params = [
            'index' => 'mypharm_itemmaster',
            'body'  => [
                'from' => 0, 
                'size' => 10000,
                'query' => $query,
                'collapse' => [
                    'field' => 'fsi_uid' 
                ]
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

        $inventory_search_params = [
            'index' => 'mypharm_inventory',
            'body'  => [
                'from' => 0, 
                'size' => 10000,
                'query' => [
                    'match' => [
                        'branch_id' => $filter->branch_id
                    ]
                    //'match_all' => (object)[]
                ]
            ]
        ];

        $inventory_blockeditems_params = [
            'index' => 'mypharm_blockeditems',
            'body'  => [
                'from' => 0, 
                'size' => 10000,
                'query' => [
                    'match_all' => (object)[],
                ]
            ]
        ];

        $blockeditems = Elasticsearch::search($inventory_blockeditems_params);
        $blockeditems = $blockeditems['hits']['hits'];
        $blockeditems = collect($blockeditems);
        $blockeditems = $blockeditems->pluck('_source');

        $return_data = [];
        $unique_products = Elasticsearch::search($unique_search_params);
        $itemmaster_products = Elasticsearch::search($all_search_params);
        $inventory_details = Elasticsearch::search($inventory_search_params);

        //dd($inventory_details);

        //dd($unique_products);
        //dump($inventory_details);
        //dump($inventory_details);

        foreach ($unique_products['hits']['hits'] as $each_hit) {
            $hit_data = $each_hit['_source'];
            $item_master_data = [];
            //dd($itemmaster_products['hits']['hits']);

            foreach ($itemmaster_products['hits']['hits'] as $each_item) {
                //dump('this run----');
                //print($each_item['_source']['fsi_uid']);
            //print('<br>');
                $item_data = $each_item['_source'];
                if($item_data['fsi_uid']==$hit_data['fsi_uid']){
                    $branch_data = false;
                    foreach ($inventory_details['hits']['hits'] as $each_branch) {
                        //dump($each_branch['_source']['stit_id'].'=='.$item_data["stit_ID"]);
                        //dump($each_branch['_source']['stit_id'].'=='.$item_data["stit_ID"]);
                        if($each_branch['_source']['stit_id']==$item_data["stit_ID"]){
                        //if(0==0){
                            $branch_data = $each_branch['_source'];
                        }
                    }
                    //dump($branch_data);
                    if($branch_data){
                        //dd('true---life');
                        $now = Carbon::now();
                        $actual_item_count = 0;
                        $actual_item_count_data = $blockeditems
                        ->where('branch_id', $branch_data['branch_id'])
                        ->where('expiry', '>', $now)
                        ->where('item_id', $item_data["stit_ID"])
                        ->all();

                        foreach ($actual_item_count_data as $actual_item) {
                            $actual_item_count += $actual_item['count'];
                        }

                        //dump($item_data["stit_ID"]);

                        $item_master_data[] = [
                            "stit_ID" => $item_data["stit_ID"],
                            "stit_fsiuid" => $item_data["stit_fsiuid"],
                            "quantity" => $item_data["quantity"],
                            "itemId" => $item_data['itemId'],
                            "short_description" => $item_data["short_description"],
                            "long_description" => $item_data["long_description"],
                            "selling_price" => $branch_data["selling_price"],
                            "main_image" => $item_data["main_image"],
                            "mrp" => $branch_data["mrp"],
                            "item_count" => ($branch_data["item_count"]),
                            "stock_available" => ($branch_data["item_count"]-$actual_item_count),
                            "blocked_stock" => ($actual_item_count),
                            "default_value" => 1,
                            "branch_id" => $branch_data['branch_id'],
                            "godown_itemId" => rand()
                        ];
                    }
                    else {
                        $item_master_data[] = [
                            "stit_ID" => $item_data["stit_ID"],
                            "stit_fsiuid" => $item_data["stit_fsiuid"],
                            "quantity" => $item_data["quantity"],
                            "itemId" => $item_data['itemId'],
                            "short_description" => $item_data["short_description"],
                            "long_description" => $item_data["long_description"],
                            "selling_price" => 0,
                            "main_image" => $item_data["main_image"],
                            "mrp" => 0,
                            "item_count" => 0,
                            "stock_available" => 0,
                            "blocked_stock" => 0,
                            "default_value" => 1,
                            "branch_id" => 0,
                            "godown_itemId" => rand()
                        ];
                    }
                }
            }

            //print_r('<br>--------------------------<br>');

            if($item_master_data!=[]){
                $low_price_id = 0;
                $low_price = $item_master_data[0]['selling_price'];
                foreach ($item_master_data as $key => $value) {
                    if($value['selling_price']<$low_price){
                        $low_price_id = $key;
                        $low_price = $value['selling_price'];
                    }
                }
                $item_master_data[$low_price_id]['default_value'] = 0;
            }

            array_multisort(array_column($item_master_data, 'selling_price'), $sort_type, $item_master_data);

            $return_data[] = [
                "fsi_uid" => $hit_data['fsi_uid'],
                "stit_ID" => $hit_data['stit_ID'],
                "item_name" => $hit_data['item_name'],
                "item_group_id" => $hit_data['item_group_id'],
                "brand_name" => $hit_data['brand_name'],
                "category_id" => $hit_data['category_id'],
                "category_name" => $hit_data['category_name'],
                "variant" => $hit_data['variant'],
                "item_master" => $item_master_data,
            ];
        }
        //dump('======================================================');

        //dd($return_data);

        $return_data_delete_non_branch = [];
        foreach ($return_data as $value) {
            if($value['item_master']!=[]){
            //if(0==0){
                $return_data_delete_non_branch[] = $value;
            }
        }

        $results = collect($return_data_delete_non_branch);
        $total = $results->count();
        $pageSize = 10;
        $paginated = CollectionHelper::paginate($results, $total, $pageSize)->toArray();

        $final_paginated = [
            "currentpage" => $paginated["current_page"],
            "ProductList" => $paginated["data"],
            "first_page_url" => $paginated["first_page_url"],
            "from" => $paginated["from"],
            "last_page" => $paginated["last_page"],
            "last_page_url" => $paginated["last_page_url"],
            "next_page_url" => $paginated["next_page_url"],
            "path" => $paginated["path"],
            "per_page" => $paginated["per_page"],
            "prev_page_url" => $paginated["prev_page_url"],
            "to" => $paginated["to"],
            "total" => $paginated["total"]
        ];

        //dd($paginated->toArray());
        //dd('--------------------------------');
        return $final_paginated;

        // $items = $this->uniqueItem
        //     ->select(
        //         'fsi_uid', 
        //         'fsi_uid as item_group_id',
        //         'fsi_item_name as item_name',
        //         'fsi_brand_name as brand_name',
        //         'fsi_category_id as category_id',
        //         'fsi_categry_name as category_name',
        //         'fsi_variant as variant'
        //     )
            
        //     ->where(function ($query) use ($filter) {
        //         $query->where('fsi_item_name', 'like', "%{$filter->product_name}%")
        //             ->orWhere('fsi_brand_name', 'like', "%{$filter->product_name}%")
        //             ->orWhere('fsi_categry_name', 'like', "%{$filter->product_name}%");
        //     })
        //     ->has('itemMaster')
        //     ->with(['itemMaster' => function ($query) {
        //         $query->select(
        //             'stit_ID', 
        //             'stit_fsiuid', 
        //             'stit_quantity as quantity',
        //             'stit_itemId as itemId',
        //             'stit_Description as short_description',
        //             'stit_long_description as long_description'
        //         )
        //         ->with(['mainImage' => function ($query) {
        //             $query->select('id', 'product_id', 'image_url', 'image_thumb_url')
        //                 ->where('image_type', 1);
        //         }])
        //         ->with(['additionalImage' => function ($query) {
        //             $query->select('id', 'product_id', 'image_url', 'image_thumb_url')
        //                 ->where('image_type', 1);
        //         }]);
        //     }])
        //     ->get()->toArray();
        //    if(count($items) > 0)
        //     {
        //         $items = app(ProductRepository::class)->addFields($items, $filter->branch_id);
        //         return $this->sortItems($items);
        //     }
        //     return $items;
    }
}
