<?php

namespace App\Http\Repositories;

use App\Models\District;

class DistrictRepository
{
    protected $district;

    public function __construct(District $district)
    {
        $this->district = $district;
    }

    public function get($state)
    {
        return $this->district->select('dst_Id', 'dst_Name')->where('st_Id', $state)->orderBy('dst_Name')->get();
    }
}
