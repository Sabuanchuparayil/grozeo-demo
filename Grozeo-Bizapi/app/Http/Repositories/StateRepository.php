<?php

namespace App\Http\Repositories;

use App\Models\State;

class StateRepository
{
    protected $state;

    public function __construct(State $state)
    {
        $this->state = $state;
    }

    public function get($country)
    {
        return $this->state->select('st_ID', 'st_name', 'state_code')->where('cnt_ID', $country)->orderBy('st_name')->get();
    }
}
