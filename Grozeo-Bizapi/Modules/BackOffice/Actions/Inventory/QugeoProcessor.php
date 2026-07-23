<?php

namespace BackOffice\Actions\Inventory;

use App\Models\Order;
use GuzzleHttp\Client;
use App\Models\OrderItem;
use BackOffice\Models\Branch;
use App\Models\StockItemMaster;
use App\Models\OrderItemBarcodes;
use BackOffice\Models\QugeoOrder;
use BackOffice\Models\TransferOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class QugeoProcessor
{
    protected $qugeo;

    protected $branch;

    protected $client;

    const CASH_ON_DELIVERY = 1;

    const ONLINE = 2;

    public function __construct()
    {
        $this->qugeo = new QugeoOrder();
         $this->client = new Client();
    }

    public function save($order)
    {
        if(!empty($order->quor_slot_id)){
			$slotId = $order->quor_slot_id;
            $slotDate = $order->quor_slot_date;
            $quor_Type = 1;
        }else{
            $quor_Type = $order->quor_Type;
			$slotId = 0;
            $slotDate = '0000-00-00';
        }
        if($order->quor_TransferOrder_Type == 4){
            $quor_Type = 1;
        }
        $qugeoOrder = $this->qugeo->create([
            'quor_TransferOrder_Type' => $order->quor_TransferOrder_Type,
            'quor_DeliveryMethodsAllowed' =>  $order->quor_DeliveryMethodsAllowed,
            'quor_Type' =>  $quor_Type,
            'quor_Refno_Source' => $order->quor_Refno_Source,
            'quor_TransferOrder_id' => $order->quor_TransferOrder_id,
            'quor_RefNo' => $order->quor_RefNo,
            'quor_PacketCount' => $order->quor_PacketCount,
            'quor_ScheduleOpeningTime' => $order->quor_ScheduleOpeningTime,
            'quor_PickupToBeManual'  => $order->quor_PickupToBeManual,
            'quor_Pickupbr_id' => $order->quor_Pickupbr_id,
            'quor_PickupName' => $order->quor_PickupName,
            'quor_PickupAddress' => $order->quor_PickupAddress,
            'quor_PickupLocation' => $order->quor_PickupLocation,
            'quor_PickupPincode' => $order->quor_PickupPincode,
            'quor_PickupPhone' => $order->quor_PickupPhone,
            'quor_PickupLat' => $order->quor_PickupLat,
            'quor_PickupLng' => $order->quor_PickupLng,
            'quor_PickupStage1Distance' => $order->quor_PickupStage1Distance,
            'quor_PickupStage2Distance' => $order->quor_PickupStage2Distance,
            'quor_Deliverybr_id' => $order->quor_Deliverybr_id,
            'quor_DeliveryName' => $order->quor_DeliveryName,
            'quor_DeliveryAddress' => $order->quor_DeliveryAddress,
            'quor_DeliveryLocation' => $order->quor_DeliveryLocation,
            'quor_DeliveryPincode' => $order->quor_DeliveryPincode,
            'quor_DeliveryPhone' => $order->quor_DeliveryPhone,
            'quor_DeliveryLat' => $order->quor_DeliveryLat,
            'quor_DeliveryLng' => $order->quor_DeliveryLng,
            'quor_DeliverySMS' => $order->quor_DeliverySMS,
            'quor_DeliveryStage1Distance' => $order->quor_DeliveryStage1Distance,
            'quor_DeliveryStage2Distance' => $order->quor_DeliveryStage2Distance,
            'quor_DistanceinKM' => $order->quor_DistanceinKM,
            'quor_StatusUpdateQry' => $order->quor_StatusUpdateQry,
            'quor_TrackingUpdateQry' => $order->quor_TrackingUpdateQry,
            'quor_CreatedOn' => $order->quor_CreatedOn,
            'quor_UpdateOn' => $order->quor_UpdateOn,
            'quor_Date' => now()->format('Y-m-d'),
            'quor_FirstScheduleRun' => now()->format('Y-m-d'),
            'quor_TrackingHistory' => $order->quor_TrackingHistory,
            'quor_ItemDetails' => $order->quor_ItemDetails,
            'quor_ItemReturnUpdate' => $order->quor_ItemReturnUpdate,
            'quor_ItemReturned' => '',
            'quor_AmountCollectible' => $order->quor_AmountCollectible,
            'quor_Paymode' => $order->quor_Paymode,
            'quor_slot_id' => $slotId,
            'quor_slot_date' => $slotDate
        ]);

        $qugeoDetails = [];
        $packinglist = [
            'packingNumber' => [],
            'BoxDetails'    => []
        ];
        
        for ($i=1; $i <= $order->quor_PacketCount; $i++) { 
            // $packid = "{$order->quor_RefNo}/{$order->quor_PacketCount}/{$i}";

            DB::statement("SET @package_id = 0");
            DB::statement("CALL getOrderPackageId({$order->orderID}, {$order->quor_Pickupbr_id}, {$i}, @package_id)");
            $packageID = DB::select("SELECT @package_id AS package_id");
            $packageID = reset($packageID);
            $packid = @$packageID->package_id ?? "{$order->quor_RefNo}/{$order->quor_PacketCount}/{$i}";
            $packinglist['packingNumber'][] = $packid;
            $qugeoDetails[] = [
                'quor_RefNo' =>  $packid,
                'quor_IsBarcode' => 1,
            ];
            if($i == $order->quor_PacketCount){
                $branchData = Branch::where('br_ID', $order->quor_Pickupbr_id)->first();
                $boxDetails = DB::table('retaline_package_master')
                ->select('rpckm_id', 'rpckm_name', 'rpckm_length', 'rpckm_breadth', 'rpckm_height')
                ->where('rpckm_status', 1)
                ->whereRaw(DB::raw("store_group_id = {$branchData->br_storeGroup} OR (FIND_IN_SET({$branchData->br_ID}, branchId))"));
                if($order->quor_DeliveryMethodsAllowed == 8){
                    $boxDetails->where('rpckm_type', 2);
                }else{
                    $boxDetails->where('rpckm_type', 1);
                }                
                $boxDetails = $boxDetails->get();
                foreach ($boxDetails as $boxDetail) {
                    $packinglist['BoxDetails'][] = [
                            'rpckm_id'      => $boxDetail->rpckm_id,
                            'rpckm_name'    => $boxDetail->rpckm_name,
                            'rpckm_length'  => $boxDetail->rpckm_length,
                            'rpckm_breadth' => $boxDetail->rpckm_breadth,
                            'rpckm_height'  => $boxDetail->rpckm_height
                       ];
        }
                $packinglist['fstoId'] = $order->quor_TransferOrder_id;
                $packinglist['fstoOrderType'] = $order->quor_TransferOrder_Type;
            }
        }
        // $packinglist['products'] = $this->productListForBoxing($order->quor_TransferOrder_id);
        $qugeoOrder->details()->createMany($qugeoDetails);
        return $packinglist;
    }

    public function checkTransferOrderExists($transferOrder)
    {
        if($transferOrder)
        {
            $transferOrderId = @$transferOrder->fsto_id;
            if($transferOrderId)
            {
                $checkQugeoOrder = QugeoOrder::where('quor_TransferOrder_id', $transferOrderId)->first();
                if($checkQugeoOrder)
                {
                    return false;
                }
                else
                {
                    return true;
                }
            }
        }
        return false;
    }

    public function getDistance($oriLat, $oriLng, $destLat, $destLng)
    {
        $result = $this->client->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'query' => [
                'units' => 'metric',
                'origins' => "{$oriLat},{$oriLng}",
                'destinations' => "{$destLat},{$destLng}",
                'key' => config('app.google_api_key'),
            ],
        ]);

        $data = $result->getBody()->getContents();
        $data = json_decode($data, true);
        if(isset($data['rows'][0]['elements'][0]['distance']['value'])){
            return (int) ($data['rows'][0]['elements'][0]['distance']['value'] / 1000);
        }else{
            return 0;
        }
    }
    
    public function getDistancebyLek($lat1, $lon1, $lat2, $lon2, $unit)
    {
        
        $theta = $lon1 - $lon2;
      $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
      $dist = acos($dist);
      $dist = rad2deg($dist);
      $miles = $dist * 60 * 1.1515;
      $unit = strtoupper($unit);
      
      return ($miles * 1.609344);

        //  if ($unit == "K") {
        //      return ($miles * 1.609344);
        //  } else if ($unit == "N") {
        //      return ($miles * 0.8684);
        //  } else {
        //      return $miles;
        //  }
    }

    private function productListForBoxing($fsto_id)
    {
        $transferOrder = TransferOrder::where('fsto_id', $fsto_id)->first();
        $products = $transferOrder->packedtransferorderDetails()->with('item:stit_ID,stit_SKU,stit_ConvertCalcMode,stit_ConvertCalcRate')->get();
        $outs = [];
        foreach($products as $pdct)
        {
            $outs[] = [
                'item_id'       => $pdct->item->stit_ID,
                'item_sku'      => $pdct->item->stit_SKU,
                'item_count'    => $pdct->fsto_pkdQty
            ];
        }
        return $outs;
    }

}
