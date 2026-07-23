<?php

namespace App\Http\Controllers;

use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\StateRepository;

class StateController extends Controller
{
    private $stateRepo;

    public function __construct(StateRepository $stateRepo)
    {
        $this->stateRepo = $stateRepo;
    }

    public function getStates($country = 1)
    {
        try
        {
            return new SuccessWithData($this->stateRepo->get($country));
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
}
