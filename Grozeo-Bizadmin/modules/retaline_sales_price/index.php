<?php

switch ($op) {
    case 'listStockSalesPrice':
        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'stit_SKU' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $search = ' WHERE 1=1';
        if (isset($data['filter'])) {
        $allowedFields = ['sp_id', 'item_id', 'item_name', 'sp_price', 'sp_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }
        switch ($_POST['type']) {
            case 'Branch':
                $where = " AND br_Name LIKE '{$_POST['branchName']}%' ";
                break;
            case 'Vendor':
                $where = " AND br_Name LIKE '{$_POST['branchName']}%' ";
                break;
            default:
                $where = " ";
                break;
        }

        $query = "SELECT 'Branch' as type,id as spId,CONCAT(br_Name ,'-',branch_shortname) as br_Name,stit_SKU,stit_itemName,product_category,stit_category_name,stit_brand_name,med_manufacturename,category_name,mppc.parent_category as parent_category,
            stit_quantity,mrp,selling_price,item_count,fpod_leastSKUmrp,fpod_customerRateHmDel,fpod_customerRateCouDel,fpod_customerRatePikup,if(fpod_itemleastSKUptr>0,fpod_itemleastSKUptr,fpod_leastSKUb2bRetailsp) as retailPrice,discount_selling_price,
            if(fpod_itemleastSKUpts > 0,fpod_itemleastSKUpts,fpod_leastSKUb2bCSsp) as csPrice FROM finascop_stock_itemmaster fsi 
LEFT JOIN finascop_stock_branch_inventory fsb  ON fsb.stit_id=fsi.stit_ID 
INNER JOIN mypha_productsubcategory mpsc ON mpsc.sub_category_id=fsi.product_category
INNER JOIN mypha_productcategory mpc ON mpc.category_id=mpsc.main_category  
INNER JOIN mypha_productparent_category mppc ON mppc.parent_category_id=mpc.parent_category 
INNER JOIN mypha_productbrands mpb ON mpb.brand_id=fsi.pdt_brand 
INNER JOIN mypha_productmanufacture mpm ON mpm.manufacture_id=mpb.manufacture_id 
INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id WHERE fsi.isMedicine = 0
UNION
SELECT 'Vendor' as type,fcpod_id as spId,stpa_Fname AS br_Name,stit_SKU,stit_itemName,product_category,stit_category_name,stit_brand_name,med_manufacturename,category_name,mppc.parent_category as parent_category,
stit_quantity,1000 AS item_count,fcpod_itemmrp as mrp,fcpod_itemoffrrate as selling_price,fcpod_leastSKUmrp AS fpod_leastSKUmrp,fcpod_customerRateHmDel AS fpod_customerRateHmDel,fcpod_customerRateCouDel AS fpod_customerRateCouDel,0 AS fpod_customerRatePikup,0 as discount_selling_price,
0 as retailPrice,0 as csPrice FROM finascop_stock_itemmaster fsi 
LEFT JOIN finascop_contractpo_products fcp  ON fcp.fcpod_itemid=fsi.stit_ID 
INNER JOIN mypha_productsubcategory mpsc ON mpsc.sub_category_id=fsi.product_category
INNER JOIN mypha_productcategory mpc ON mpc.category_id=mpsc.main_category  
INNER JOIN mypha_productparent_category mppc ON mppc.parent_category_id=mpc.parent_category 
INNER JOIN mypha_productbrands mpb ON mpb.brand_id=fsi.pdt_brand 
INNER JOIN mypha_productmanufacture mpm ON mpm.manufacture_id=mpb.manufacture_id 
INNER JOIN finascop_stock_party br ON br.stpa_id=fcp.fcpod_vendorid WHERE fsi.isMedicine = 0";
        $countQuery = "SELECT COUNT(*) FROM ({$query}) AS countStock {$search}{$where}{$searchitem}";
        $listQuery = "SELECT * FROM ({$query}) AS listStock {$search}{$where}{$searchitem} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        //echo $listQuery;
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getBranchName':
        $type = $_POST['type'];
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        if ($type == 'Branch') {
            $qry = $db->getMulipleData("SELECT br_ID,CONCAT(br_Name ,'-',branch_shortname) as br_Name FROM finascop_branch WHERE br_status = 'Active' order by br_Name asc", true);
        } else {
            $qry = $db->getMulipleData("SELECT stpa_id as br_ID,stpa_Fname as br_Name FROM finascop_stock_party WHERE stpa_IsVendor = 1 AND deliverMode_cpr <> 3 order by stpa_Fname asc", true);
        }

        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'generateUniqueId':
        $uniqueId = '';
        while ($uniqueId == '') {
            $uniqueId = getNewFinascopApiKey();
        }

        echo '{"uid":"' . $uniqueId . '"}';
        break;
    case 'listSelectedItems':
        $type = $_POST['type'];
        $branchId = $_POST['baseStation'];
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'stit_SKU' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        if (isset($data['filter'])) {
        $allowedFields = ['sp_id', 'item_id', 'item_name', 'sp_price', 'sp_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }


        if ($branchId > 0) {

            $lastSePriDate = $db->getItemFromDB("SELECT updated_on FROM finascop_stock_branch_inventory_log WHERE branch_id = {$branchId} and type = '{$type}' ORDER BY id DESC LIMIT 1");
            if (empty($lastSePriDate)) {
                $spdate = '0000-00-00 00:00:00';
                $datespCon = " fpod_createdon >= '{$date}'";
            } else {
                $spdate = $lastSePriDate;
                $datespCon = " fpod_createdon > '{$date}'";
            }
            $isSellingPrice = $db->getItemFromDB(" SELECT COUNT(*) FROM finascop_purchase_order_details INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId  WHERE fpo_centralStore = {$branchId} AND {$datespCon} ");
            $qry = "SELECT branch_id,finascop_stock_itemmaster.stit_ID AS stitId,stit_SKU,item_count,fpod_customerRatePikup,fpod_customerRateHmDel,fpod_leastSKUepr,stit_ParentItemId,fpod_leastSKUb2bCSsp,fpod_leastSKUb2bRetailsp,"
                . "fpod_customerRateCouDel FROM finascop_stock_itemmaster  LEFT JOIN finascop_stock_branch_inventory ON finascop_stock_branch_inventory.stit_id = finascop_stock_itemmaster.stit_ID "
                . "AND (branch_id = {$branchId} || branch_id IN (SELECT br_ID FROM finascop_branch WHERE br_cpd = {$branchId})) WHERE   directPurchase = 0 AND stit_HasChildItem = 0 AND stit_status = 1 {$searchitem}    GROUP BY finascop_stock_itemmaster.stit_ID ORDER BY stit_SKU ASC ";
            //            $qry = "SELECT branch_id,finascop_stock_itemmaster.stit_ID AS stitId,stit_SKU,item_count,fpod_customerRatePikup,fpod_customerRateHmDel,fpod_leastSKUepr,fpod_leastSKUb2bCSsp,fpod_leastSKUb2bRetailsp,"
            //                    . "fpod_customerRateCouDel,stit_ParentItemId FROM finascop_stock_branch_inventory "
            //                    . "INNER JOIN finascop_stock_itemmaster ON finascop_stock_itemmaster.stit_ID =  finascop_stock_branch_inventory.stit_id AND stit_HasChildItem = 0  WHERE  branch_id = {$branchId} || "
            //                    . "branch_id IN (SELECT br_ID FROM finascop_branch WHERE br_cpd = {$branchId})   GROUP BY finascop_stock_itemmaster.stit_ID";


            switch ($type) {
                case 'pickup':
                    $field = 'fpod_customerRatePikup';
                    break;
                case 'delivery':
                    $field = 'fpod_customerRateHmDel';
                    break;
                case 'courier':
                    $field = 'fpod_customerRateCouDel';
                    break;
                case 'retail':
                    $field = 'fpod_leastSKUb2bRetailsp';
                    break;
                case 'basestation':
                    $field = 'fpod_leastSKUb2bCSsp';
                    break;
            }
            $datas = $db->getMulipleData($qry, true);
            $resCount = count($datas);

            if (!empty($datas)) {
                for ($i = 0; $i < $resCount; $i++) {
                    $currentPrice = $db->getItemFromDB("SELECT {$field} FROM finascop_stock_branch_inventory WHERE stit_id = {$datas[$i]['stitId']} AND branch_id = {$branchId}  ORDER BY updated_on DESC LIMIT 1");
                    switch ($type) {
                        case 'pickup':
                            $datas[$i]['fpod_customerRatePikup'] = $currentPrice;
                            $field_selling_price = $datas[$i]['fpod_customerRatePikup'];
                            break;
                        case 'delivery':
                            $datas[$i]['fpod_customerRateHmDel'] = $currentPrice;
                            $field_selling_price = $datas[$i]['fpod_customerRateHmDel'];

                        case 'courier':
                            $datas[$i]['fpod_customerRateCouDel'] = $currentPrice;
                            $field_selling_price = $datas[$i]['fpod_customerRateCouDel'];
                            break;
                        case 'retail':
                            $datas[$i]['fpod_leastSKUb2bRetailsp'] = $currentPrice;
                            $field_selling_price = $datas[$i]['fpod_leastSKUb2bRetailsp'];
                            break;
                        case 'basestation':
                            $datas[$i]['fpod_leastSKUb2bCSsp'] = $currentPrice;
                            $field_selling_price = $datas[$i]['fpod_leastSKUb2bCSsp'];
                            break;
                    }
                    if ($currentPrice > 0) {
                        $datas[$i]['currentPrice'] = $currentPrice;
                    } else {
                        $datas[$i]['currentPrice'] = 0;
                    }

                    $lastDate = $db->getItemFromDB("SELECT updated_on FROM finascop_stock_branch_inventory_log WHERE stit_id = {$datas[$i]['stitId']} AND branch_id = {$branchId} and type = '{$type}' ORDER BY id DESC LIMIT 1");
                    if (empty($lastDate)) {
                        $date = '0000-00-00 00:00:00';
                        $dateCon = " fpod_createdon >= '{$date}'";
                    } else {
                        $date = $lastDate;
                        $dateCon = " fpod_createdon > '{$date}'";
                    }

                    $datas[$i]['{$field}'] = $currentPrice;
                    $fpod_skuPurchaseQty = $db->getItemFromDB("SELECT SUM(fpod_leastSKUTotalqty) FROM finascop_purchase_order_details INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId  WHERE fpod_itemid = {$datas[$i]['stit_ParentItemId']} AND fpo_centralStore = {$branchId} AND {$dateCon} ");
                    if ($fpod_skuPurchaseQty > 0) {
                        $skuPurchaseRange = $db->getFromDB("SELECT MAX(fpod_itemoffrrate) as maxRate,MIN(fpod_itemoffrrate) as minRate FROM finascop_purchase_order_details "
                            . "INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId WHERE fpod_itemid = {$datas[$i]['stit_ParentItemId']} AND fpo_centralStore = {$branchId} AND {$dateCon} ORDER BY fpod_id DESC LIMIT 1", true);
                        if ($skuPurchaseRange['maxRate'] == $skuPurchaseRange['minRate']) {
                            $datas[$i]['fpod_skuPurchaseRange'] = $skuPurchaseRange['maxRate'];
                        } else {
                            $datas[$i]['fpod_skuPurchaseRange'] = $skuPurchaseRange['minRate'] . ' - ' . $skuPurchaseRange['maxRate'];
                        }

                        $totalPurchaseRate = $db->getItemFromDB(" SELECT SUM(fpod_itemoffrrate) FROM finascop_purchase_order_details INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId  WHERE fpod_itemid = {$datas[$i]['stit_ParentItemId']} AND fpo_centralStore = {$branchId} AND {$dateCon} ");

                        $fpod_skuAvgPurchaseRate = $totalPurchaseRate / 2;
                        $fpod_skuLastPurchaseRate = $db->getItemFromDB("SELECT fpod_itemoffrrate FROM finascop_purchase_order_details INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId "
                            . "WHERE fpod_itemid = {$datas[$i]['stit_ParentItemId']} AND fpo_centralStore = {$branchId}  AND {$dateCon} ORDER BY fpod_id DESC LIMIT 1");
                        $fpod_leastSKUepr = $db->getItemFromDB("SELECT fpod_effectiverate FROM finascop_purchase_order_details INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId "
                            . "WHERE fpod_itemid = {$datas[$i]['stit_ParentItemId']} AND fpo_centralStore = {$branchId}  AND {$dateCon} ORDER BY fpod_id DESC LIMIT 1");
                        $datas[$i]['fpod_skuPurchaseQty'] = $fpod_skuPurchaseQty;
                        $datas[$i]['totalPurchaseRate'] = $totalPurchaseRate;
                        $datas[$i]['fpod_skuAvgPurchaseRate'] = $fpod_skuAvgPurchaseRate;
                        $datas[$i]['fpod_skuLastPurchaseRate'] = $fpod_skuLastPurchaseRate;
                        $datas[$i]['fpod_leastSKUepr'] = $fpod_leastSKUepr;
                        if ($fpod_skuLastPurchaseRate > 0 && $field_selling_price > 0) {
                            $fpod_effectivemargin = 100 - (($fpod_skuLastPurchaseRate / $field_selling_price) * 100);
                        } else {
                            $fpod_effectivemargin = 0;
                        }

                        $datas[$i]['fpod_effectivemargin'] = round($fpod_effectivemargin, 2);
                    } else {
                        $fpod_skuLastPurchaseRate = $db->getItemFromDB("SELECT {$field} FROM finascop_purchase_order_details INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId "
                            . "WHERE fpod_itemid = {$datas[$i]['stitId']} AND fpo_centralStore = {$branchId}  AND {$dateCon} ORDER BY fpod_id DESC LIMIT 1");
                        $datas[$i]['fpod_skuPurchaseRange'] = 0;
                        $datas[$i]['fpod_skuPurchaseQty'] = 0;
                        $datas[$i]['totalPurchaseRate'] = 0;
                        $datas[$i]['fpod_skuAvgPurchaseRate'] = 0;
                        $datas[$i]['fpod_skuLastPurchaseRate'] = 0;
                        $datas[$i]['fpod_leastSKUepr'] = 0;
                        $datas[$i]['fpod_effectivemargin'] = 0;
                    }
                    $datas[$i]['selling_price'] = '';
                    $datas[$i]['lastdate'] = $spdate;
                }
                $datas = orderBy($datas, $rec_sort);
                echo '{"totalCount":"', $resCount, '","data":' . json_encode($datas) . '}';
            } else {
                echo '{"totalCount":"0","data":[]}';
            }
        }
        break;
    case 'updateSellingPrice':
        $type = $_POST['type'];
        $uuid = $_POST['uuid'];
        $search_baseStation = $_POST['search_baseStation'];
        $griddata = json_decode(stripslashes($_POST['spUpdatedItems']));
        $griddata = (array) $griddata;

        $retailors = $db->getItemFromDB("SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_cpd = {$search_baseStation} AND br_type <> 1");
        if (!empty($retailors)) {
            $branches = $search_baseStation . ',' . $retailors;
        } else {
            $branches = $search_baseStation;
        }

        $branchesArr = explode(',', $branches);
        $parentProducts = array();
        $db->query('begin');
        for ($i = 0; $i < count($griddata); $i++) {
            if ($griddata[$i]->selling_price > 0) { //&& $griddata[$i]->fpod_skuPurchaseQty > 0
                switch ($type) {
                    case 'pickup':
                        $updatat['fpod_customerRatePikup'] = $griddata[$i]->selling_price;
                        $insdata['fpod_customerRatePikup'] = $griddata[$i]->selling_price;
                        $old_selling_price = $griddata[$i]->fpod_customerRatePikup;


                        break;
                    case 'delivery':
                        $updatat['fpod_customerRateHmDel'] = $griddata[$i]->selling_price;
                        $insdata['fpod_customerRateHmDel'] = $griddata[$i]->selling_price;
                        $old_selling_price = $griddata[$i]->fpod_customerRateHmDel;

                        $updatat['mrp'] = $griddata[$i]->selling_price;
                        $updatat['selling_price'] = $griddata[$i]->selling_price;

                        $insdata['mrp'] = $griddata[$i]->selling_price;
                        $insdata['selling_price'] = $griddata[$i]->selling_price;

                    case 'courier':
                        $updatat['fpod_customerRateCouDel'] = $griddata[$i]->selling_price;
                        $insdata['fpod_customerRateCouDel'] = $griddata[$i]->selling_price;
                        $old_selling_price = $griddata[$i]->fpod_customerRateCouDel;
                        break;
                    case 'retail':
                        $updatat['fpod_leastSKUb2bRetailsp'] = $griddata[$i]->selling_price;
                        $insdata['fpod_leastSKUb2bRetailsp'] = $griddata[$i]->selling_price;
                        $old_selling_price = $griddata[$i]->fpod_leastSKUb2bRetailsp;
                        break;
                    case 'basestation':
                        $updatat['fpod_leastSKUb2bCSsp'] = $griddata[$i]->selling_price;
                        $insdata['fpod_leastSKUb2bCSsp'] = $griddata[$i]->selling_price;
                        $old_selling_price = $griddata[$i]->fpod_leastSKUb2bCSsp;
                        break;
                }



                for ($j = 0; $j < count($branchesArr); $j++) {
                    $isEntry = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory WHERE stit_id = {$griddata[$i]->stitId} and branch_id = {$branchesArr[$j]} ");
                    if ($isEntry > 0) {
                        $updatat['updated_on'] = date("Y-m-d H:i:s");
                        $status = $db->perform('finascop_stock_branch_inventory', $updatat, 'update', " stit_id = {$griddata[$i]->stitId} and branch_id = {$branchesArr[$j]} ");
                    } else {
                        $insdata['stit_id'] = $griddata[$i]->stitId;
                        $insdata['branch_id'] = $branchesArr[$j];
                        $insdata['created_at'] = date("Y-m-d H:i:s");
                        $status = $db->perform('finascop_stock_branch_inventory', $insdata);
                    }
                    if (empty($old_selling_price)) {
                        $old_selling_price = 0;
                    }
                    if (empty($griddata[$i]->item_count)) {
                        $griddata[$i]->item_count = 0;
                    }
                    $type = "updateSellingPrice";
                    $updatatLog['old_selling_price'] = $old_selling_price;
                    $updatatLog['selling_price'] = $griddata[$i]->selling_price;
                    $updatatLog['branch_id'] = $branchesArr[$j];
                    $updatatLog['stit_id'] = $griddata[$i]->stitId;
                    $updatatLog['item_count'] = $griddata[$i]->item_count;
                    $updatatLog['fpod_skuPurchaseRange'] = $griddata[$i]->fpod_skuPurchaseRange;
                    $updatatLog['fpod_skuPurchaseQty'] = $griddata[$i]->fpod_skuPurchaseQty;
                    $updatatLog['fpod_skuAvgPurchaseRate'] = $griddata[$i]->fpod_skuAvgPurchaseRate;
                    $updatatLog['fpod_skuLastPurchaseRate'] = $griddata[$i]->fpod_skuLastPurchaseRate;
                    $updatatLog['fpod_leastSKUepr'] = $griddata[$i]->fpod_leastSKUepr;
                    $updatatLog['fpod_effectivemargin'] = $griddata[$i]->fpod_effectivemargin;
                    $updatatLog['updated_on'] = date("Y-m-d H:i:s");
                    $updatatLog['updated_by'] = $_SESSION['admin']->Finascop_UserId;
                    $updatatLog['type'] = $type;
                    $updatatLog['action'] = 'Selling price update - ' . $type;
                    //$status = $db->perform('finascop_stock_branch_inventory_log', $updatatLog);


                    $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'INVENTORYLOG'");
                    $fields_string = json_encode($updatatLog);
                    $opts = array(
                        CURLOPT_URL => $url,
                        CURLINFO_CONTENT_TYPE => "application/json",
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_BINARYTRANSFER => TRUE,
                        CURLOPT_RETURNTRANSFER => TRUE,
                        CURLOPT_POST => count($fields),
                        CURLOPT_POSTFIELDS => $fields_string,
                        CURLOPT_HTTPHEADER => array('Content-Type: application/json')
                    );

                    $ch = curl_init();
                    curl_setopt_array($ch, $opts);
                    $logrresult = curl_exec($ch);
                    $info = curl_getinfo($ch);
                    curl_close($ch);
                    header("Content-Type: application/json");
                    //$result = json_decode($datacl, true);
                    if ($logrresult != true) {
                        echo '{"success":false, "msg":"Some problem in log insertion."}';
                        exit();
                    }

                    $parentItemId = $db->getItemFromDB("SELECT stit_ParentItemId FROM finascop_stock_itemmaster WHERE stit_ID = {$griddata[$i]->stitId}");
                    if ($parentItemId > 0) {
                        array_push($parentProducts, $parentItemId);
                    }
                    $UniqueParentProducts = array_unique($parentProducts);
                }
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Sellingprice updated.'";
            echo '{"success":true,"valid":true,"msg":' . $msg . '}';
            //echo '{"success":true,"valid":true,"parentItems":' . json_encode($UniqueParentProducts) . ',msg":' . $msg . '}';
        } else {
            $msg = "'Error occured while saving.'";
            echo '{"success":false,"valid":false,"msg":' . $msg . '}';
        }
        break;
    case 'getBaseStationBranch':

        $query = $_POST['query'];
        if ($query != '')
            $con = " and br_Name like '" . $query . "%'";
        else
            $con = '';

        $qry = "select br_ID,CONCAT(br_Name ,'-',branch_shortname) as br_Name from finascop_branch where br_status = 'Active' AND br_ID>0 AND br_PyramidLevel = 3 " . $con . " order by br_Name ";
        $branch = $db->getMulipleData($qry, true);
        if (!empty($branch)) {
            $branch = json_encode($branch);
            echo '{"data":' . $branch . '}';
        } else {
            echo '{"data":[]}';
        }
        break;
    case 'updateItemStock':
        $type = $_POST['type'];
        $uuid = $_POST['uuid'];
        $search_baseStation = $_POST['search_baseStation'];
        $griddata = json_decode(stripslashes($_POST['spUpdatedItems']));
        $griddata = (array) $griddata;

        $retailors = $db->getItemFromDB("SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_cpd = {$search_baseStation} AND br_type <> 1");
        $branches = $search_baseStation . ',' . $retailors;
        $branchesArr = explode(',', $branches);
        for ($i = 0; $i < count($griddata); $i++) {
            if ($griddata[$i]->selling_price > 0) {
                for ($j = 0; $j < count($branchesArr); $j++) {
                    $parentItemId = $db->getItemFromDB("SELECT stit_ParentItemId FROM finascop_stock_itemmaster WHERE stit_ID = {$griddata[$i]->stitId}");
                    if ($parentItemId > 0) {
                        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'UPDATECHILDSTOCK'");
                        if (!empty($url)) {
                            $fields = array(
                                "parentItem" => $parentItemId,
                                "branch" => $branchesArr[$j]
                            );
                            $fields_string = json_encode($fields);
                            //print_r($fields_string);
                            $opts = array(
                                CURLOPT_URL => $url,
                                CURLINFO_CONTENT_TYPE => "application/json",
                                CURLOPT_BINARYTRANSFER => TRUE,
                                CURLOPT_RETURNTRANSFER => TRUE,
                                CURLOPT_POST => count($fields),
                                CURLOPT_POSTFIELDS => $fields_string,
                                CURLOPT_HTTPHEADER => array('Content-Type: application/json')
                            );

                            $ch = curl_init();
                            curl_setopt_array($ch, $opts);
                            $data = curl_exec($ch);
                            $info = curl_getinfo($ch);
                            curl_close($ch);
                            header("Content-Type: application/json");
                        }
                    }
                }
            }
        }
        $msg = "'Stock updation processing.'";
        echo '{"success":true,"valid":true,"msg":' . $msg . '}';

        break;
    case 'listStockbankRemittance':
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];
        $filterCon = " WHERE 1=1 ";

        if (isset($_POST['filter']) && $_POST['filter'] != '') {
            foreach ($_POST['filter'] as $key => $v) {
                if (array_key_exists($v['field'], $fields))
                    $field = $fields[$v['field']];
                else {
                    $field = $v['field'];
                }
                switch ($v['data']['type']) {
                    case 'string':

                        $filterCon .= (($filterCon == "") ? " where " : " and ") . $field . " like '" . $v['data']['value'] . "%'";

                        break;
                }
            }
        }
        if ($_SESSION['admin']->br_PyramidLevel == 3) {
            $retailors = $db->getItemFromDB("SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_cpd = {$_SESSION['admin']->finascop_current_branch_id} ");
            if (!empty($retailors)) {
                $retailors = $retailors . ',' . $_SESSION['admin']->finascop_current_branch_id;
            }
            $condition = " AND rbem_createdBranch IN ({$retailors})";
        } else if ($_SESSION['admin']->br_PyramidLevel == 4) {
            $condition = " AND rbem_createdBranch = {$_SESSION['admin']->finascop_current_branch_id}";
        } else {
            $condition = " ";
        }
        $qry = "select count(*) from " . FINASCOP_DB . "retaline_bank_remittance INNER JOIN retaline_bank_master ON rbm_id = rbrem_bank INNER JOIN finascop_branch ON br_ID = rbem_createdBranch {$filterCon} ";

        $totalCount = $db->getItemFromDB($qry);
        $prefix = FINASCOP_DB;
        $prefix1 = FINASCOP_DB . 'config';
        $db->query('set @cnt=0');
        $query = "select rbrem_id,rbrem_date,rbrem_bank,rbrem_purpose,rbrem_amount,rbm_name,rbm_account,rbm_branch,rbm_ifsc,CONCAT(br_Name ,'-',branch_shortname) as br_Name from  retaline_bank_remittance INNER JOIN retaline_bank_master ON rbm_id = rbrem_bank INNER JOIN finascop_branch ON br_ID = rbem_createdBranch $filterCon $condition order by rbrem_id desc ";
        $data = $db->getMultipleData($query, true);

        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;
    case 'saveBankRemittance':
        $rpgtr['rbrem_date'] = DATE("Y-m-d", STRTOTIME($_POST['bankRemittance_date']));
        $rpgtr['rbrem_bank'] = $_POST['bankRemittance_bank'];
        $rpgtr['rbrem_purpose'] = $_POST['bankRemittance_purpose'];
        $rpgtr['rbrem_amount'] = $_POST['bankRemittance_amount'];

        $db->query('begin');
        $rpgtr['rbem_createdOn'] = date('Y-m-d H:i:s');
        $rpgtr['rbem_createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $rpgtr['rbem_createdBranch'] = $_SESSION['admin']->finascop_current_branch_id;
        $status = $db->perform('retaline_bank_remittance', $rpgtr);

        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Saved Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'getBankName':

        $qry = $db->getMulipleData("SELECT rbm_id,rbm_name,rbm_account FROM retaline_bank_master WHERE rbm_status = 1 order by rbm_name asc", true);

        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'listDailyStockExpense':
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];
        $filterCon = " WHERE 1=1 ";

        if (isset($_POST['filter']) && $_POST['filter'] != '') {
            foreach ($_POST['filter'] as $key => $v) {
                if (array_key_exists($v['field'], $fields))
                    $field = $fields[$v['field']];
                else {
                    $field = $v['field'];
                }
                switch ($v['data']['type']) {
                    case 'string':

                        $filterCon .= (($filterCon == "") ? " where " : " and ") . $field . " like '" . $v['data']['value'] . "%'";

                        break;
                }
            }
        }
        if ($_SESSION['admin']->br_PyramidLevel == 3) {
            $retailors = $db->getItemFromDB("SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_cpd = {$_SESSION['admin']->finascop_current_branch_id} ");
            if (!empty($retailors)) {
                $retailors = $retailors . ',' . $_SESSION['admin']->finascop_current_branch_id;
            }
            $condition = " AND rde_createdBranch IN ({$retailors})";
        } else if ($_SESSION['admin']->br_PyramidLevel == 4) {
            $condition = " AND rde_createdBranch = {$_SESSION['admin']->finascop_current_branch_id}";
        } else {
            $condition = " ";
        }
        $qry = "select count(*) from " . FINASCOP_DB . "retaline_daily_expense  INNER JOIN finascop_branch ON br_ID = rde_createdBranch {$filterCon} ";

        $totalCount = $db->getItemFromDB($qry);
        $prefix = FINASCOP_DB;
        $prefix1 = FINASCOP_DB . 'config';
        $db->query('set @cnt=0');
        $query = "select * from  retaline_daily_expense INNER JOIN finascop_branch ON br_ID = rde_createdBranch $filterCon $condition order by rde_id desc ";
        $data = $db->getMultipleData($query, true);

        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;
    case 'saveDailyExpense':
        $rpgtr['rde_date'] = DATE("Y-m-d", STRTOTIME($_POST['rde_date']));
        $rpgtr['rde_purpose'] = $_POST['rde_purpose'];
        $rpgtr['rde_amount'] = $_POST['rde_amount'];

        $db->query('begin');
        $rpgtr['rde_createdOn'] = date('Y-m-d H:i:s');
        $rpgtr['rde_createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $rpgtr['rde_createdBranch'] = $_SESSION['admin']->finascop_current_branch_id;
        $status = $db->perform('retaline_daily_expense', $rpgtr);

        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Saved Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listRawItems':
        $created_date = DATE("Y-m-d", STRTOTIME($_POST['created_date']));
        $type = $_POST['type'];
        $branchId = $_POST['baseStation'];
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'stit_SKU' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        if (isset($data['filter'])) {
        $allowedFields = ['sp_id', 'item_id', 'item_name', 'sp_price', 'sp_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }


        if ($branchId > 0) {

            $lastSePriDate = $db->getItemFromDB("SELECT updated_on FROM finascop_raw_branch_inventory WHERE branch_id = {$branchId} ORDER BY id DESC LIMIT 1");
            if (empty($lastSePriDate)) {
                $date = '0000-00-00 00:00:00';
                $dateCon = " fpod_createdon >= '{$date}'";
            } else {
                $date = $lastSePriDate;
                $dateCon = " fpod_createdon > '{$date}'";
            }
            $qry = "SELECT finascop_raw_branch_inventory.branch_id,finascop_stock_itemmaster.stit_ID AS stitId,stit_SKU,fishmart_price,finascop_raw_branch_inventory.selling_price,offline_price,institutional_price FROM finascop_stock_itemmaster  "
                //. "INNER JOIN finascop_stock_branch_inventory ON finascop_stock_branch_inventory.stit_id = finascop_stock_itemmaster.stit_ID "
                . "LEFT JOIN finascop_raw_branch_inventory ON finascop_raw_branch_inventory.stit_id = finascop_stock_itemmaster.stit_ID "
                . "AND finascop_raw_branch_inventory.branch_id = {$branchId} AND created_date = '{$created_date}' WHERE   directPurchase = 1 AND stit_ParentItemId = 0 AND stit_status = 1  {$searchitem}   GROUP BY finascop_stock_itemmaster.stit_ID ORDER BY {$rec_sort} {$rec_sort_dir} ";

            $datas = $db->getMulipleData($qry, true);
            $resCount = count($datas);

            if (!empty($datas)) {
                for ($i = 0; $i < $resCount; $i++) {
                    $fpod_skuPurchaseQty = $db->getItemFromDB("SELECT SUM(fpod_leastSKUTotalqty) FROM finascop_purchase_order_details INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId  WHERE fpod_itemid = {$datas[$i]['stitId']} AND fpo_centralStore = {$branchId} AND {$dateCon} ");
                    if ($fpod_skuPurchaseQty > 0) {
                        $skuPurchaseRange = $db->getFromDB("SELECT MAX(fpod_itemoffrrate) as maxRate,MIN(fpod_itemoffrrate) as minRate FROM finascop_purchase_order_details "
                            . "INNER JOIN  finascop_purchase_order ON fpo_id = fpod_fpoId WHERE fpod_itemid = {$datas[$i]['stitId']} AND fpo_centralStore = {$branchId} AND {$dateCon} ORDER BY fpod_id DESC LIMIT 1", true);
                        if ($skuPurchaseRange['maxRate'] == $skuPurchaseRange['minRate']) {
                            $datas[$i]['fpod_skuPurchaseRange'] = $skuPurchaseRange['maxRate'];
                        } else {
                            $datas[$i]['fpod_skuPurchaseRange'] = $skuPurchaseRange['minRate'] . ' - ' . $skuPurchaseRange['maxRate'];
                        }
                    } else {
                        $datas[$i]['fpod_skuPurchaseRange'] = 0;
                    }

                    $datas[$i]['lastdate'] = $date;
                }
                //lastdate,branch_id,stitId,stit_SKU,fishmart_price,selling_price
                echo '{"totalCount":"', $resCount, '","data":' . json_encode($datas) . '}';
            } else {
                echo '{"totalCount":"0","data":[]}';
            }
        }
        break;
    case 'updateRawSellingPrice':
        $type = $_POST['type'];
        $uuid = $_POST['uuid'];
        $created_date = DATE("Y-m-d", STRTOTIME($_POST['created_date']));
        $search_baseStation = $_POST['search_baseStation'];
        $griddata = json_decode(stripslashes($_POST['spUpdatedItems']));
        $griddata = (array) $griddata;


        $db->query('begin');
        for ($i = 0; $i < count($griddata); $i++) {
            if ($griddata[$i]->newfishmart_price > 0) { //&& $griddata[$i]->fpod_skuPurchaseQty > 0
                $isEntry = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_raw_branch_inventory WHERE stit_id = {$griddata[$i]->stitId} and branch_id = {$search_baseStation} AND created_date = '{$created_date}'");
                if ($isEntry > 0) {
                    $updatat['fishmart_price'] = $griddata[$i]->newfishmart_price;
                    $updatat['selling_price'] = $griddata[$i]->newselling_price;
                    $updatat['offline_price'] = $griddata[$i]->newoffline_price;
                    $updatat['institutional_price'] = $griddata[$i]->newinstitutional_price;
                    $updatat['updated_on'] = date("Y-m-d H:i:s");
                    $status = $db->perform('finascop_raw_branch_inventory', $updatat, 'update', " stit_id = {$griddata[$i]->stitId} and branch_id = {$search_baseStation} ");
                } else {
                    $insdata['fishmart_price'] = $griddata[$i]->newfishmart_price;
                    $insdata['selling_price'] = $griddata[$i]->newselling_price;
                    $insdata['offline_price'] = $griddata[$i]->newoffline_price;
                    $insdata['institutional_price'] = $griddata[$i]->newinstitutional_price;
                    $insdata['created_date'] = $created_date;
                    $insdata['stit_id'] = $griddata[$i]->stitId;
                    $insdata['branch_id'] = $search_baseStation;
                    $insdata['created_at'] = date("Y-m-d H:i:s");
                    $insdata['updated_on'] = date("Y-m-d H:i:s");
                    $status = $db->perform('finascop_raw_branch_inventory', $insdata);
                }

                $insdataLog['fishmart_price'] = $griddata[$i]->newfishmart_price;
                $insdataLog['selling_price'] = $griddata[$i]->newselling_price;
                $insdataLog['offline_price'] = $griddata[$i]->newoffline_price;
                $insdataLog['institutional_price'] = $griddata[$i]->newinstitutional_price;
                $insdataLog['created_date'] = $created_date;
                $insdataLog['stit_id'] = $griddata[$i]->stitId;
                $insdataLog['branch_id'] = $search_baseStation;
                $insdataLog['created_at'] = date("Y-m-d H:i:s");
                $status = $db->perform('finascop_raw_branch_inventory_log', $insdataLog);
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Sellingprice updated.'";
            echo '{"success":true,"valid":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error occured while saving.'";
            echo '{"success":false,"valid":false,"msg":' . $msg . '}';
        }
        break;
    case 'deleteExpenseEntry':
        $rde_id = $_POST['rde_id'];
        $db->query('begin');
        $status = $db->query("DELETE FROM retaline_daily_expense WHERE rde_id = {$rde_id} ");
        $status = $db->query('commit');
        if ($status) {
            $msg = "Entry removed";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }

        break;
    case 'deleteBankRemittanceEntry':
        $rbrem_id = $_POST['rbrem_id'];
        $db->query('begin');
        $status = $db->query("DELETE FROM retaline_bank_remittance WHERE rbrem_id = {$rbrem_id} ");
        $status = $db->query('commit');
        if ($status) {
            $msg = "Entry removed";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'listRawStockSalesPrice':
        $created_date = DATE("Y-m-d", STRTOTIME($_POST['created_date']));
        $type = $_POST['type'];
        $branchId = $_POST['baseStation'];
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'stit_SKU' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        if (isset($data['filter'])) {
        $allowedFields = ['sp_id', 'item_id', 'item_name', 'sp_price', 'sp_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }


        if ($branchId > 0) {

            $qry = "SELECT CONCAT(br_Name ,'-',branch_shortname) as br_Name,finascop_raw_branch_inventory.branch_id,finascop_stock_itemmaster.stit_ID AS stitId,stit_SKU,fishmart_price,finascop_raw_branch_inventory.selling_price,offline_price,institutional_price FROM finascop_stock_itemmaster  "
                //. "INNER JOIN finascop_stock_branch_inventory ON finascop_stock_branch_inventory.stit_id = finascop_stock_itemmaster.stit_ID "
                . "LEFT JOIN finascop_raw_branch_inventory ON finascop_raw_branch_inventory.stit_id = finascop_stock_itemmaster.stit_ID AND finascop_raw_branch_inventory.branch_id = {$branchId} AND created_date = '{$created_date}' "
                . "INNER JOIN finascop_branch ON finascop_branch.br_ID = finascop_raw_branch_inventory.branch_id "
                . " WHERE   directPurchase = 1 AND stit_ParentItemId = 0 AND stit_status = 1  {$searchitem}   GROUP BY finascop_stock_itemmaster.stit_ID ORDER BY {$rec_sort} {$rec_sort_dir} ";

            $datas = $db->getMulipleData($qry, true);
            $resCount = count($datas);

            if (!empty($datas)) {
                echo '{"totalCount":"', $resCount, '","data":' . json_encode($datas) . '}';
            } else {
                echo '{"totalCount":"0","data":[]}';
            }
        }
        break;
}
