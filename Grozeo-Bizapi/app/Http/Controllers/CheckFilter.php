<?php

namespace App\Http\Controllers;

class CheckFilter
{
    public function filterCheck($home, $request)
    {

        if (((count($request['filter']['category']) !== 0) || (count($request['filter']['brands']) !== 0) || (count($request['filter']['price_range']) !== 0)) || (!empty($request['sort']['price']))) {

            return true;
        } else {

            return false;
        }
    }
}
