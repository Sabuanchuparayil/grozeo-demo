<?php

namespace App\Models;

use DOMDocument;
use Carbon\Carbon;
use Elasticsearch;
use DB;
use App\Traits\ExportToES;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ESItemmaster extends Model
{
    use ExportToES;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_stock_itemmaster';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'created_by', 'updated_at', 'updated_by', 'pro_nutri_info'
    ];

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created_on';
    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'updated_on';

    public function getRecords()
    {
        $itemmaster_last_data_query = [
            'index' => 'mypharm_itemmaster',
            'body'  => [
                'query' => [
                    'match_all' => (object)[],
                ],
                'size' => 1,
                'sort' => [
                    'updated_on' => [
                        'order' => 'desc'
                    ]
                ]
            ]
        ];
        $itemmaster_last_data = Elasticsearch::search($itemmaster_last_data_query)['hits']['hits'];
        if($itemmaster_last_data==[]) $itemmaster_last_data = Carbon::parse('first day of December 2008');
        else $itemmaster_last_data = Elasticsearch::search($itemmaster_last_data_query)['hits']['hits'][0]['_source']['updated_on'];

        $domain = "https://" . config('filesystems.disks.s3.bucket') . "." . config('filesystems.disks.s3.driver') . ".".env("AWS_DEFAULT_REGION", "ap-southeast-1").".amazonaws.com/products/";
        $domain = (config('app.cdn_url') != "") ? (config('app.cdn_url')."/products/") : $domain;
        $return = $this->with(['mainImage' => function ($qry) use ($domain) {
                            $qry->where('image_type', 1)
                                ->select('id', 'product_id', DB::raw('CONCAT("' . $domain . 'preview-",image_url) as image_url'), DB::raw('CONCAT("' . $domain . 'thumbnail-",image_url) as image_thumb_url'));
                        }])->select(
                'finascop_stock_uniqueitem.fsi_uid AS item_group_id',
                'finascop_stock_itemmaster.stit_itemId AS stit_itemId',
                'finascop_stock_itemmaster.stit_SKU AS stit_SKU',
                'finascop_stock_uniqueitem.fsi_uid AS fsi_uid',
                'finascop_stock_uniqueitem.fsi_item_name AS item_name',
                'finascop_stock_itemmaster.stit_ID AS stit_ID',
                //'stgp_groupID AS item_group_id',
                'finascop_stock_uniqueitem.fsi_uid AS item_group_id',
                'stit_fsiuid',
                'finascop_stock_itemmaster.isMedicine AS isMedicine',
                'stit_quantity AS quantity',
                'stit_itemId AS itemId',
                'stit_Description AS short_description',
                'stit_long_description AS long_description',
                'stit_product_variant AS product_variant',
                // 'finascop_stock_branch_inventory.item_count AS stock_available',
                // 'finascop_stock_branch_inventory.branch_id AS brand_id',
                // 'finascop_stock_branch_inventory.mrp AS mrp',
                // 'finascop_stock_branch_inventory.selling_price AS selling_price',
                'finascop_stock_uniqueitem.fsi_brand_name AS brand_name',
                'finascop_stock_uniqueitem.fsi_variant AS variant',
                'finascop_stock_uniqueitem.fsi_category_id AS category_id',
                'finascop_stock_uniqueitem.fsi_categry_name AS category_name',
                'finascop_stock_itemmaster.updated_on AS updated_on'
            )
            // ->leftJoin('finascop_stock_branch_inventory','finascop_stock_branch_inventory.stit_id', '=', 'finascop_stock_itemmaster.stit_ID')
            ->leftJoin('finascop_stock_uniqueitem','finascop_stock_uniqueitem.fsi_uid', '=', 'finascop_stock_itemmaster.stit_fsiuid')
            ->where('finascop_stock_itemmaster.updated_on', '>', $itemmaster_last_data)
            //->limit(10)
            ->get();

        dd($return->toArray());

        return $return;
    }

    public function mainImage()
    {
       return $this->hasMany('App\Models\StockItemImage','product_id','stit_ID');
    }

    public function getEsIndex()
    {
        return 'mypharm_itemmaster';
    }

    public function getEsType()
    {
        return 'mypharm_itemmaster';
    }

    public function getkey()
    {
        return $this->id;
    }
}
