<?php

namespace App\Http\Repositories\Pincode;

use App\Models\Pincode;
use App\Models\BrmPincode;
use App\Exceptions\PincodeNotFoundException;

class PincodeRepository
{
    protected $pincode;

    public function __construct(Pincode $pincode)
    {
        $this->pincode = $pincode;
    }

    public function get($data)
    {
        if($this->checkPincode($data))
        {
            if($this->checkActive($data))
            {

                return $this->getPostOffice($data);
            }
            else
            {
                $this->throwException("Pincode is not Active.");
            }
        }
        else{
            $this->throwException("Pincode is not Available.");
        }

    }

    public function throwException($msg)
    {
        throw new PincodeNotFoundException($msg);
    }

    private function getPostOffice(array $data)
    {

        // return $this->pincode->with('districtAndState')->get();
        return $this->pincode
                    ->with('districtAndState')
                    ->where('pincode', $data['pincode'])
                    ->first() ?? $this->throwException("Invalid Pincode");
    }

    private function checkPincode(array $data)
    {
        return BrmPincode::where('pincode', $data['pincode'])
        ->exists();
    }

    private function checkActive(array $data)
    {
        return BrmPincode::where('pincode', $data['pincode'])
                            ->where('isActive', 1)
                            ->exists();
    }

}
