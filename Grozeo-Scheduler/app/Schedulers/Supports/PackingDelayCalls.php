<?php

namespace App\Schedulers\Supports;

use Illuminate\Support\Facades\DB;
use App\Models\Supports\{
    OrderOutboundJobs,
    OrderPackingDelayCallsLog
};
use App\Models\ProcessLock;
use App\Helpers\HttpCurlCalls;


class PackingDelayCalls
{
    public function __invoke()
    {
        $defaultIVR = config('ivrcalls.default');
        if ($defaultIVR != "") {
            try {
                $orderData = DB::select('SELECT * FROM(
                    SELECT o.order_id,o.order_order_id, bg.store_group_name, br.br_storeGroup, br.br_Phone, fsto.fsto_createdOn, fsto.fsto_status,
                    (
                        SELECT IFNULL(MAX(processingTime), 10) FROM(
                            SELECT IFNULL(i.itemProcessingTime, s.processingTime) AS processingTime FROM finascop_stock_transfer_order_details od
                            INNER JOIN finascop_stock_itemmaster i ON i.stit_ID =  od.fsto_ItemId
                            INNER JOIN mypha_productsubcategory s ON s.sub_category_id = i.product_category
                            WHERE od.fsto_id = fsto.fsto_id
                        ) tmp
                    ) AS processingTime
                    FROM finascop_stock_transfer_order fsto
                    INNER JOIN retaline_customer_order o ON o.order_id = fsto.fstr_id AND fsto.fsto_ordertype = 1 
                    INNER JOIN finascop_branch br ON o.order_branch_id = br.br_ID
                    INNER JOIN finascop_branch_group bg ON bg.store_group_id = br.br_storeGroup 
                    WHERE fsto_status < 9
                ) pendingOrders WHERE DATE_ADD(fsto_createdOn, INTERVAL processingTime MINUTE) BETWEEN (SELECT DATE_ADD(NOW(), INTERVAL -3 HOUR)) AND (SELECT DATE_ADD(NOW(), INTERVAL 30 MINUTE)) AND order_id NOT IN (SELECT order_id FROM order_packing_delay_calls_log)');

                foreach ($orderData as $od) {
                    $ojCreate = OrderOutboundJobs::create([
                        'eventId'       => 4,
                        'orderRefrenceId' => $od->order_order_id,
                        'jobTitle'      => "Packing Delay Alert Call-{$od->store_group_name} for {$od->order_order_id}",
                        'calleeId'      => $od->br_storeGroup,
                        'calleeName'    => $od->store_group_name,
                        'calleeMobile'  => $od->br_Phone,
                        'calleeType'    => 2,
                        'eventRank'     => 1,
                        'status'        => 1
                    ]);
                    if ($ojCreate) {

                        $callClass = config("ivrcalls.{$defaultIVR}.class");
                        $callObj = new $callClass();
                        $response = $callObj->triggerIvrCall($od->br_Phone, $od->store_group_name);

                        /*$fields = array(
                            "api_key" => config("ivrcalls.{$defaultIVR}.key"),
                            "campaign_name" => config("ivrcalls.{$defaultIVR}.campaign"),
                            "format" => 'json',
                            "PhoneNumber" => $od->br_Phone . 'Name=' . $od->store_group_name,
                            "action" => 'start'
                        );
                        $fields_string = http_build_query($fields, '', '&');
                        $apiURL = config("ivrcalls.{$defaultIVR}.url") . $fields_string;
                        $response = (new HttpCurlCalls)->curlCall($apiURL, [], 'GET', []);*/
                        if ($response) {
                            $ojCreate->status = 2;
                            $ojCreate->save();
                            OrderPackingDelayCallsLog::create([
                                'order_id'  => $od->order_id
                            ]);
                        }
                    }
                }

                ProcessLock::updateColData("BizAPI_PackingDelayCalls", 0);
            } catch (\Exception $e) {
                info("PackingDelayCalls ERROR => " . $e->getMessage());
                ProcessLock::updateColData("BizAPI_PackingDelayCalls", 1);
            }
        }
    }
}
