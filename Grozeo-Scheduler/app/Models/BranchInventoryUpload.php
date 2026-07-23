<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\BranchInventoryUploadDetails;

class BranchInventoryUpload extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finascop_stock_branch_inventory_upload';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'fbiu_id';
    /**
     * Updated at field.
     * @var string
     */
    const UPDATED_AT = "fbiu_updatedOn";
     const CREATED_AT = "fbiu_createdOn";

    public function details()
    {
        return $this->hasMany(BranchInventoryUploadDetails::class, 'fbiu_id');
    }
}
