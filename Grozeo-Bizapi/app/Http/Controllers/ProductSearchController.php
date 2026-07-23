<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\SuccessWithData;
use App\Http\Repositories\Product\MedicineESRepository;
use App\Http\Repositories\Product\SearchESRepository;

class ProductSearchController extends Controller
{
    public function __construct()
    {

    }

    public function newESSearch(Request $request)
    {
        return new SuccessWithData(
            (new MedicineESRepository())->search($request)
        );
    }

    
    public function newESSortFiletrSearch(Request $request)
    {
        return new SuccessWithData(
            (new SearchESRepository())->search($request)
        );
    }
}
