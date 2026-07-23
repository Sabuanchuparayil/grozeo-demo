<?php

class OrderProcessorSch {

    public function prescriptionAvaialbale() {
        $db = new sqlDb(DSN);

        $prlk_updtime = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'ORDERCOVERTIONTIME'");
        // $countApprovedPrescriptions = $db->getItemFromDB("SELECT COUNT(*) FROM upload_prescription WHERE updated_at > '{$prlk_updtime}'");
        $countApprovedPrescriptions = $db->getItemFromDB("SELECT COUNT(*) FROM upload_prescription WHERE isScheduleProcessed = 0");
        $prescriptionIds = "";
        if ($countApprovedPrescriptions > 0) {
            $db->query("set group_concat_max_len=95000;");
            $prescriptionIds = $db->getItemFromDB("SELECT GROUP_CONCAT(id) FROM upload_prescription WHERE isScheduleProcessed = 0");
            //$customers = $db->getMultipleData("SELECT distinct cust_id,1 FROM upload_prescription WHERE updated_at > '{$prlk_updtime}'", true);
            $customers = $db->getMultipleData("SELECT distinct cust_id,1 FROM upload_prescription WHERE isScheduleProcessed = 0", true);
            foreach ($customers as $customer) {
                $prescriptionOrderCount = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order WHERE order_customer_id = {$customer['cust_id']} AND status_id = 22");
                if ($prescriptionOrderCount > 0) {
                    $prescriptionOrders = $db->getMultipleData("SELECT order_id,order_order_id,updated_at FROM retaline_customer_order WHERE order_customer_id = {$customer['cust_id']} AND status_id = 22", true);
                    foreach ($prescriptionOrders as $prescriptionOrder) {
                        $prescriptionOrderMedines = $db->getItemFromDB("SELECT GROUP_CONCAT(item_product_id) FROM retaline_customer_order_items WHERE customer_order_id = {$prescriptionOrder['order_id']} AND item_isMedicine = 1");
                        $prescriptionOrderMedinesArr = explode(',', $prescriptionOrderMedines);
                        $date = date('Y-m-d');
                        $customerMedicines = $db->getItemFromDB("SELECT GROUP_CONCAT(stit_Id) FROM mypha_prescriptiom_medicine_map where cust_id = {$customer['cust_id']} AND pmm_expirydate  >= '{$date}'");
                        $prescriptionOrderMedinesArrcustomer = explode(',', $customerMedicines);
//                        echo 'orderId'.$prescriptionOrder['order_id']."\n";
//echo '$prescriptionOrderMedinesArr';
//print_r($prescriptionOrderMedinesArr);
//echo '$prescriptionOrderMedinesArrcustomer';
//print_r($prescriptionOrderMedinesArrcustomer);

                        $prescriptionOrderMedinesCount = count($prescriptionOrderMedinesArr);
                        $searchCount = count(array_intersect($prescriptionOrderMedinesArr, $prescriptionOrderMedinesArrcustomer));
//                        echo '$prescriptionOrderMedinesCount'.$prescriptionOrderMedinesCount."\n";;
//                        echo '$searchCount'.$searchCount."\n";;
//                        echo  "------------------------------------- -\n";
                        //exit();
                        if ($searchCount == $prescriptionOrderMedinesCount) {
                            $cfg_Value = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'ORDERSUCCESS'");
                            $url = str_replace('{orderId}', $prescriptionOrder['order_id'], $cfg_Value);

                            //$fields_string = json_encode($fields);
                            //echo '$url'.$url;
                            $opts = array(
                                CURLOPT_URL => $url,
                                CURLINFO_CONTENT_TYPE => "application/json",
                                CURLOPT_BINARYTRANSFER => TRUE,
                                CURLOPT_RETURNTRANSFER => TRUE,
                                CURLOPT_HTTPHEADER => array('Content-Type: application/json')
                            );

                            $ch = curl_init();
                            curl_setopt_array($ch, $opts);
                            $data = curl_exec($ch);
                            $info = curl_getinfo($ch);
                            curl_close($ch);
//echo 'curl call';
                            //print_r($data);
                        }
                    }
                }
            }
        }
        $db->query('begin');
        $cdata['cfg_Value'] = date('Y-m-d H:i:s');
        $uploPre['isScheduleProcessed'] = 1;
        $uploPre['updated_at'] = date('Y-m-d H:i:s');
        if ($prescriptionIds != '') {
            $status = $db->perform('upload_prescription', $uploPre, 'update', " id IN ({$prescriptionIds})");
        }

        $status = $db->perform('sys_configuration', $cdata, 'update', " cfg_Name = 'ORDERCOVERTIONTIME'");
        $status = $db->query('commit');
        return 1;
    }

}
