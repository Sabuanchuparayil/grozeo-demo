<?php

namespace App\Models;

use DOMDocument;
use Carbon\Carbon;
use Elasticsearch;
use App\Traits\ExportToES;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ESProductsearch extends Model
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
        $return = $this->select(
                        'id',
                        'stit_id',
                        'branch_id',
                        'fsbg_id',
                        'item_count',
                        'mrp',
                        'selling_price',
                        'updated_on'
                    )
                    //->where('updated_on', '>', $inventory_last_data)
                    ->get();

        dd($return);
        return $return;
    }

    public function getEsIndex()
    {
        return 'mypharm_productsearch';
    }

    public function getEsType()
    {
        return 'mypharm_productsearch';
    }

    public function getkey()
    {
        return $this->id;
    }
}
