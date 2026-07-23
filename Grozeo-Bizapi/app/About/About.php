<?php

namespace App\About;

use Illuminate\Database\Eloquent\Model;

class About extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'app_pages';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'page_id';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

     /**
     * created_at Timestamp field
     */
    const CREATED_AT = 'faq_createdOn';

    /**
     * updated_at Timestamp field
     */
    const UPDATED_AT = 'faq_updatedOn';
}
