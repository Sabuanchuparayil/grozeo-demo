<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProcessLock;

class ProcessLock extends Model
{
    // TODO: ProcessLock polarity (0 vs 1 meaning enabled/disabled) is inconsistent across schedulers; review and standardize as a real mutex.
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
    protected $table = 'process_lock';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'prlk_name';

    /**
     * updated at is made null.
     *
     * @var null
     */
    const UPDATED_AT = NULL;

    public static function updateColData($prlk_name, $status)
    {
        ProcessLock::where("prlk_name", $prlk_name)->update([
            'prlk_isenabled'    => $status
        ]);
    }
}
