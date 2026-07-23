<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\Customer\AddressRepository;
class AddressController extends Controller
{
    protected $address;

    public function __construct(AddressRepository $address)
    {
        $this->address = $address;
    }

    public function setPrimary($id)
    {

        return new SuccessWithData(
            $this->address->setPrimary($id)
        );
    }
}
