<?php

namespace App\Modules;

use App\Models\Branch;

class DetermineStates
{
    private $branch;

    const IN_STATE = 1;

    const OUT_STATE = 0;

    public function __construct()
    {
        $this->branch = new Branch;
    }

    public static function find(int $branch_id, string $state = "")
    {
        return (new static)->state($branch_id, $state);
    }

    private function state($branch_id, $state)
    {
        $branch = $this->getBranch($branch_id);
        if(empty($state))
        {
            $primaryAddress = auth()->user()->primaryAddress;
            $state = $primaryAddress->deli_state ?? "";
        }
        $branch_state = $branch->state->st_name ?? "nil";
        if(strtolower($branch_state) === strtolower($state))
        {
            return static::IN_STATE;
        }
            return static::OUT_STATE;
    }

    private function getBranch($branch_id)
    {
        return $this->branch
                    ->with('state')
                    ->where('br_ID', $branch_id)
                    ->first();
    }

}