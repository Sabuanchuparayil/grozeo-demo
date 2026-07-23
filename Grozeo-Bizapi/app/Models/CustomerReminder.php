<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReminder extends Model
{
    //

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_reminder';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    public $timestamps = false;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    public function items()
    {
        return $this->hasMany("App\Models\ProductMedicineIitemReminder", "item_id", "id");
    }
}
