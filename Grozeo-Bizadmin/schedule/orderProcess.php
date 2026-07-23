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

    public function getPromotedProducts() {
        $db = new sqlDb(DSN);
        $today = date("Y-m-d");
        $db->query('begin');
        $db->query("TRUNCATE TABLE promotedProducts");

        /* $getRCPromotedProducts = $db->getMultipleData("SELECT fcpod_id,fcpo_validDate,fcpod_vendorid,cpo.fcpod_itemid,fcpod_itemmrp,fcpod_itemoffrrate,fcpod_itemoffrrateet,fcpod_customerRateHmDel,fcpod_customerRateCouDel FROM finascop_contractpo_products cpo INNER JOIN (
          SELECT fcpod_itemid, MIN(fcpod_customerRateHmDel) MinPoint
          FROM finascop_contractpo_products
          GROUP BY fcpod_itemid  ) tbl1 ON tbl1.fcpod_itemid = cpo.fcpod_itemid AND cpo.fcpod_customerRateHmDel = tbl1.MinPoint  WHERE EXISTS (SELECT stpa_id FROM finascop_stock_party WHERE stpa_id = cpo.fcpod_vendorid AND stpa_IsVendor = 1 AND deliverMode_cpr <> 3 )  AND fcpo_validDate >= '{$today}' ORDER BY fcpod_customerRateHmDel ASC", true);

          foreach ($getRCPromotedProducts as $getRCPromotedItem) {
          $proItemdata['branchtypeid'] = 2;
          $proItemdata['rcvendorid'] = $getRCPromotedItem['fcpod_vendorid'];
          $proItemdata['itemid'] = $getRCPromotedItem['fcpod_itemid'];

          $status = $db->perform('promotedProducts', $proItemdata);
          } */

        $getBIpromotedProducts = $db->getMultipleData("SELECT fsbi.id,fsbi.stit_id as rcitemid,fsbi.branch_id as branchId,fpod_customerRateHmDel,mrp FROM finascop_stock_branch_inventory fsbi INNER JOIN (
    SELECT stit_id, MIN(fpod_customerRateHmDel) MinPoint
    FROM finascop_stock_branch_inventory
    GROUP BY stit_id  ) tbl1 ON tbl1.stit_id = fsbi.stit_id AND fsbi.fpod_customerRateHmDel = tbl1.MinPoint  WHERE EXISTS (SELECT br_ID FROM finascop_branch WHERE br_ID = fsbi.branch_id AND br_storeGroup = -1)  ORDER BY fsbi.id ASC ", true);

        foreach ($getBIpromotedProducts as $getBIpromotedItem) {
            $itemBusinessType = $db->getItemFromDB("SELECT business_type_id FROM promotedProducts 
INNER JOIN finascop_stock_itemmaster ON stit_ID = itemid 
INNER JOIN  mypha_productsubcategory ON sub_category_id = product_category 
INNER JOIN mypha_productcategory ON category_id = main_category 
INNER JOIN mypha_productparent_category ON parent_category_id = mypha_productcategory.parent_category 
INNER JOIN finascop_business_type ON business_type_id = parent_category_businessType WHERE stit_ID = {$getBIpromotedItem['rcitemid']}");
            if ($itemBusinessType > 0) {
                $proItemdatabi['branchtypeid'] = 1;
                $proItemdatabi['branchId'] = $getBIpromotedItem['branchId'];
                $proItemdatabi['itemid'] = $getBIpromotedItem['rcitemid'];
                $proItemdatabi['itemBusinessType'] = $itemBusinessType;

                $status = $db->perform('promotedProducts', $proItemdatabi);
            }
        }
        $status = $db->query('commit');
    }

}
