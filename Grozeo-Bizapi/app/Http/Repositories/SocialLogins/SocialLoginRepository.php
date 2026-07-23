<?php

namespace App\Http\Repositories\SocialLogins;

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessWithData;
use App\Http\Responses\SuccessResponse;
use App\Http\Repositories\Cart\CartRepository;

class SocialLoginRepository
{
    public function __construct() {}


    /**
     *social login details.
     *
     * @param string $requesttype
     * @param array $request
     */
    public function socialLogins($type, $request)
    {
        try
        {
            $socClass = config("socials.{$type}.sClass");
            $socLogin = new $socClass();

            $response = $socLogin->socLogin($request);
            if((@$response['error'] == '') && (@$response['email'] != ''))
            {
                $customer = $this->getCustomer($response['email']);
                $data = [
                    'is_verified'   => true,
                    'is_registered' => false,
                    'email'         => $response['email'],
                    'refCode'       => Hash::make($response['email'])
                ];
                if($customer)
                {
                    $data['user'] = $customer;
                    $data['is_registered'] = true;
                    if($customer->email_verified == 1)
                    {
                        $customer->token = createJwtToken($customer);
                        $data['user'] = $customer;
                        $data['is_verified'] = true;
                        app(CartRepository::class)->migrateGuestCart($customer->cust_id);
                    }
                    $data['token'] = '';
                }
                return new SuccessWithData($data);
            }
            return new ErrorResponse($response['error']);
        }
        catch (\Exception $e)
        {
            return new ErrorResponse($e->getMessage());
        }
    }

    /**
     * get customer details.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    private function getCustomer($email)
    {
        $customer = Customer::where('cust_email', $email)->first();
        return $customer;
    }
}