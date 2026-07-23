<?php

namespace App\PackingPartners\Petpooja;

use App\PackingPartners\Petpooja\{
    PetpoojaRequest,
    PetpoojaApiOperations
};
use App\Models\Packing\OrderPacking;
use App\Http\Repositories\{
    PackingLogRepository,
    PackingUpdateRepository
};

class Petpooja
{
    protected $requests, $operations;
    public function __construct()
    {
		$this->requests = new PetpoojaRequest;
		$this->operations = new PetpoojaApiOperations;
    }

    public function orderPacking($orderID)
    {
        try
        {
            $requestData = $this->requests->createPackingRequest($orderID);
            if($requestData['status'] == 'success')
            {
                $packing = $this->operations->createPacking($requestData['data']);
                if($packing['status'] == 'success')
                {
                    OrderPacking::create([
                        'order_id'          => $orderID,
                        'packing_type'      => 'petpooja',
                        'packing_id'        => $packing['data']->clientOrderID,
                        'packing_status'    => 0,
                        'packing_request'   => json_encode($packing['request']),
                        'packing_response'  => json_encode($packing['data']),

                    ]);
                }
                return $packing;
            }
            return $requestData;
        }
        catch (\Exception $e)
        {
            // info("PETPOOJA orderPacking ERROR");info($e);
        }
    }

    public function webhook($request)
    {
        try
        {
            (new PackingLogRepository)->createLog([
                'order_id'      => @$request->orderID,
                'type'          => 'petpooja',
                'APIName'       => "Webhook",
                "APIURL"        => "",
                "APIHeaders"    => "",
                'request'       => json_encode($request->all()),
                "response"      => ""
            ]);
            $setTracking = $this->updatePackingDetails($request);
        }
        catch (\Exception $e)
        {
            // info("PETPOOJA WEBHOOK ERROR");info($e);
        }
    }
    private function updatePackingDetails($request)
    {
        $orderID = $request->orderID;
        $status = 0;
        switch ($request->status)
        {
            case "1":
                $status = 1;
                (new PackingUpdateRepository)->accepted($orderID, "petpooja");
                break;
            case "2":
                $status = 1;
                (new PackingUpdateRepository)->accepted($orderID, "petpooja");
                break;
            case "3":
                $status = 1;
                (new PackingUpdateRepository)->accepted($orderID, "petpooja");
                break;
            case "4":
                break;
            case "5":
                $status = 3;
                (new PackingUpdateRepository)->packed($orderID, "petpooja");
                break;
            case "10":
                break;
            case "-1":
                $status = 4;
                (new PackingUpdateRepository)->cancelled($orderID, "petpooja");
                break;
        }
        OrderPacking::where('packing_id', $orderID)->update([
            'packing_status'    => $status

        ]);
    }
}