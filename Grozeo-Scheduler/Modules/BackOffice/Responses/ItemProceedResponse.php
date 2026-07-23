<?php

namespace BackOffice\Responses;

use App\Http\Responses\SuccessResponse;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Log;
class ItemProceedResponse implements Responsable
{
    protected $mismatched;

    protected $packinglist;

    public function __construct(array $mismatched, array $packinglist)
    {
        $this->mismatched = $mismatched;
        $this->packinglist = $packinglist;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        if (!empty($this->mismatched)) {
            return response()->json([
                'status' => 'mismatch',
                'data' => [
                    'mismatched' => $this->mismatched,
                    'is_revoked' => false,
                ]
            ]);
        }
        return response()->json([
            'status' => 'ok',            
            'packinglist' =>  $this->packinglist
        ]);
    }
}
