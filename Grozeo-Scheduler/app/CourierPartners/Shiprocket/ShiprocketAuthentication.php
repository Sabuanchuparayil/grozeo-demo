<?php
namespace App\CourierPartners\Shiprocket;

use App\CourierPartners\Shiprocket\ShiprocketApiFunctions;

class ShiprocketAuthentication
{
    protected $functions;
    function __construct()
    {
        $this->functions = new ShiprocketApiFunctions;
    }
       
    public function authentication()
    {
        return $this->functions->createAuthToken();
    }
}