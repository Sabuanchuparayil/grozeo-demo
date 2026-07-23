<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Requests\Customer\RegistrationRequest;
use App\Http\Repositories\Customer\RegistrationRepository;

class RegistrationController extends Controller
{
    protected $registration;

    public function __construct(RegistrationRepository $registration)
    {

        $this->registration = $registration;

    }

    public function store(RegistrationRequest $request)
    {
        return $this->registration->store($request->validated());
    }
}
