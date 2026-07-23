<?php

namespace App\Models;

use DOMDocument;
use Carbon\Carbon;
use Elasticsearch;
use App\Traits\ExportToES;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ESBlockedItems extends Model
{
    use ExportToES;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_stock_blocked';

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
        $blockeditems_last_data_query = [
            'index' => 'mypharm_blockeditems',
            'body'  => [
                'query' => [
                    'match_all' => (object)[],
                ],
                'size' => 1,
                'sort' => [
                    'updated_at' => [
                        'order' => 'desc'
                    ]
                ]
            ]
        ];
        $blockeditems_last_data = Elasticsearch::search($blockeditems_last_data_query)['hits']['hits'];
        if($blockeditems_last_data==[]) $blockeditems_last_data = Carbon::parse('first day of December 2008');
        else $blockeditems_last_data = Elasticsearch::search($blockeditems_last_data_query)['hits']['hits'][0]['_source']['updated_at'];

        $return = $this->select(
                        'id',
                        'item_id',
                        'branch_id',
                        'count',
                        'expiry',
                        'updated_at'
                    )->where('updated_at', '>', $blockeditems_last_data)->get();

        return $return;
    }

    public function getEsIndex()
    {
        return 'mypharm_blockeditems';
    }

    public function getEsType()
    {
        return 'mypharm_blockeditems';
    }

    public function getkey()
    {
        return $this->id;
    }
}
