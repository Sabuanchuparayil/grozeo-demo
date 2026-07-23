<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceAutopostingValues extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finance_autoposting_values';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    public $timestamps = false;
}
