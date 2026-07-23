<?php

namespace App\Http\Repositories;

use Cache;
use BackOffice\Models\BranchGroup;

class PaymentGatewayCredentials
{
    public static function getCredentials(): array
    {
        $storeGroupID = getHeaderStoreGroup();
        if($storeGroupID > 0)
        {
            $key = 'pgCredentials_'.$storeGroupID;
            $cacheData = Cache::remember($key, 60, function () use ($storeGroupID)
            {
                $branchData = BranchGroup::find($storeGroupID);
                if(@$branchData->pg_provider)
                {
                    $paymentModal = config("paymentgateway.{$branchData->pg_provider}.modal");
                    $credentials = $paymentModal::getCompanyPaydetails(1, $storeGroupID, $branchData->pg_mode);
                    return [
                        "type"          => "store",
                        "provider"      => $branchData->pg_provider,
                        "mode"          => $branchData->pg_mode,
                        'credentials'   => $credentials
                    ];
                }
                return self::setDefault();
            });
            return $cacheData;
        }
        else
        {
            return self::setDefault();
        }
    }

    private static function setDefault()
    {
        $provider = config("paymentgateway.default");
        $paymentModal = config("paymentgateway.{$provider}.modal");
        $credentials = $paymentModal::getCompanyPaydetails(1, 0);
        return [
            "type"          => "grozeo",
            "provider"      => $provider,
            "mode"          => 1,
            "credentials"   => $credentials
        ];
    }
}