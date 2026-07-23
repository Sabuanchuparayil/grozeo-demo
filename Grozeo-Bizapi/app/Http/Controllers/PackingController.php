<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\DB;

class PackingController
{
    public function __construct() {}

    public function packingCallback(Request $request, $type = "")
    {
        $packingClass = config("packingpartners.{$type}.sClass");
        $packer = new $packingClass();

        $webHook = $packer->webhook($request);
    }
}