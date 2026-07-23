<?php
namespace App\Factories;

use App\Partners\{
    Courier\Shipyaari\Shipyaari,
    Courier\WorldOptions\WorldOptions,
    Express\Uber\Uber,
    Express\Porter\Porter
};

class PartnerFactory
{
    public static function make(string $type, string $region = 'india')
    {
        $name = config("shipping.default.$region.$type");
        $key = strtolower("{$type}:{$name}");
        
        switch ($key)
        {
            case 'courier:shipyaari':
                return new Shipyaari();
            case 'courier:worldoptions':
                return new WorldOptions();
            case 'express:porter':
                return new Porter();
            case 'express:uber':
                return new Uber();
            default:
                throw new \Exception("Unknown partner type: {$type} / name: {$name}");
        }
    }
}
