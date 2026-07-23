<?php

namespace BackOffice\Http\Repositories\RelationOfficer;

use BackOffice\Models\RelationOfficer\AreaEntries;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Responses\SuccessWithData;

class AreaFinderCheck
{
    public function callareaCheckLatLong(Request $request)
    {
        $result = $this->areaCheckLatLong($request->latitude, $request->longitude);
        $data = [
            'status'    => 'success',
            'data'      => $result,
            'message'   => ''
        ];
        return new SuccessWithData($data);
    }
    public function areaCheckLatLong($lat, $long)
    {
        $selectors = DB::raw('*, calcDistance('.$lat.', '.$long.', areaLatitude, areaLongitude) AS distance');
        $queryData = AreaEntries::select($selectors)
            ->orderBy('distance', 'ASC')
            ->havingRaw('distance <= areaSpan')
            ->with('businessAssociate:id,baName')
            ->first();
        return $queryData;
    }
}
