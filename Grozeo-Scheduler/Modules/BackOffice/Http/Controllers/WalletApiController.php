<?php

namespace BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use BackOffice\Http\Requests\WalletRequest;
use BackOffice\Http\Repositories\WalletTransactionRepository;

class WalletApiController
{
    public function __construct() {}

    public function createWalletEntry(WalletRequest $request)
    {
        $response = (new WalletTransactionRepository)->createWalletEntry($request);
        return $response;
    }
}
