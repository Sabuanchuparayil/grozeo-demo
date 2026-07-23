<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\ErrorWithData;
use App\Http\Requests\Customer\AddressRequest;
use App\Http\Requests\Customer\EditCustomerRequest;
use App\Http\Repositories\Customer\AddressRepository;
use App\Http\Repositories\Customer\CustomerRepository;
use App\Http\Requests\Customer\AgeVerificationRequest;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    protected $address;

    protected $customer;

    public function __construct(AddressRepository $address, CustomerRepository $customer)
    {

        $this->address = $address;
        $this->customer = $customer;
    }

    public function addAddress(AddressRequest $request)
    {

        if($request->validated()){
                $data=$request->validated();

                return new SuccessWithData(
                    $this->address->create($data)
                );
     

            
        }
    }

    public function edit(EditCustomerRequest $request)
    {
        return new SuccessWithData(
            $this->customer->edit($request)
        );
    }

    public function get()
    {

        return new SuccessWithData(
            $this->customer->get()
        );
    }

    public function delete($id)
    {
       // AddressRepository::checkPincode($data['deli_delivery_pin']);
        if(count(auth()->user()->address)==1){
            throw new Exception('You can not delete this address.');
        }
        return new SuccessWithData(
            $this->address->delete($id)
        );
    }

    public function getAddress()
    {
        return new SuccessWithData(
            $this->address->get()
        );
    }

    public function getWallet()
    {
        return new SuccessWithData(
            ["wallet_balance" => (string) $this->customer->getWallet()]
        );
    }

    public function getWalletHistory()
    {
        return new SuccessWithData(
            $this->customer->getWalletHistory()
        );
    }
    public function getWalletHistoryFiltered(Request $request)
    {
        return new SuccessWithData(
            $this->customer->getWalletHistoryFiltered($request)
        );
    }
    public function deactivateAccount()
    {
        $customerStatus = $this->customer->deactivateCustomerAccount();
        if($customerStatus['status'] == 'success')
        {
            return new SuccessWithData($customerStatus);
        }
        else
        {
            return new ErrorWithData($customerStatus['status'], $customerStatus['message']);
        }
    }

    public function ageVerification(AgeVerificationRequest $request)
    {
        try
        {
            $verifyAge = $this->customer->ageVerification($request);
            if($verifyAge)
            {
                return new SuccessResponse("Age Verified");
            }
            return new ErrorResponse("Operation failed");
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage()); 
        }
    }
}
