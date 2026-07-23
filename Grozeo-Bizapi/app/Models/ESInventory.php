<?php

namespace App\Models;

use DOMDocument;
use Carbon\Carbon;
use Elasticsearch;
use App\Traits\ExportToES;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ESInventory extends Model
{
    use ExportToES;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_stock_branch_inventory';

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
        $inventory_last_data_query = [
            'index' => 'mypharm_inventory',
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
        $inventory_last_data = Elasticsearch::search($inventory_last_data_query)['hits']['hits'];
        if($inventory_last_data==[]) $inventory_last_data = Carbon::parse('first day of December 2008');
        else $inventory_last_data = Elasticsearch::search($inventory_last_data_query)['hits']['hits'][0]['_source']['updated_on'];

        $return = $this->select(
                        'id',
                        'stit_id',
                        'branch_id',
                        'fsbg_id',
                        'item_count',
                        'mrp',
                        'selling_price',
                        'updated_on'
                    )->where('updated_on', '>', $inventory_last_data)->get();

        return $return;
    }

    public function getEsIndex()
    {
        return 'mypharm_inventory';
    }

    public function getEsType()
    {
        return 'mypharm_inventory';
    }

    public function getkey()
    {
        return $this->id;
    }
}
