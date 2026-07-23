<?php

namespace BackOffice\Models;

use Illuminate\Database\Eloquent\Model;

class EmailsmsQueue extends Model
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
    protected $table = 'retaline_emailsms_queue';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    const CREATED_AT = 'created_on';

    const UPDATED_AT = 'updated_on';

}
