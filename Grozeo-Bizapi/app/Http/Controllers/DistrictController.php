<?php

namespace App\Http\Controllers;

use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\DistrictRepository;

class DistrictController extends Controller
{
    private $districtRepo;

    public function __construct(DistrictRepository $districtRepo)
    {
        $this->districtRepo = $districtRepo;
    }

    public function getDistricts($state = 1)
    {
        try
        {
            return new SuccessWithData($this->districtRepo->get($state));
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
}
