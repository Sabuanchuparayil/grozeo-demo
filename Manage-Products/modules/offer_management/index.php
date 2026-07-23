<?php

switch ($op) {
    case 'listOfferMgmt':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 12;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'stit_ID' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        //$filter_qry = " AND 1 = 1 ";
        $filter_qry = '';
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'] ?? null;
        if (isset($filter)) {
            foreach ($filter as $key => $val) {
                switch ($val['data']['type']) {
                    case 'string':
                        $filter_qry .= " AND " . $val['field'] . "  LIKE  '" . $val['data']['value'] . "%'";
                        break;
                }
            }
        }
        if ($sort == 'stit_ID') {
            $sort = 'bom_startdate';
        }
        $date = date('dd-mm-YYYY');
        $qry = "SELECT bom_id,bom_bmdCompany,bom_bmdHub,bom_bmdIncentive,bom_bmdTechnology,bom_bmdCustomer,bom_offrPlacement,bom_offrPromotion,bom_offrSupplier,stiid_fpoid,stiid_itemmasterid,bom_status,bom_locked,"
                . "DATE_FORMAT(bom_startdate,'%d-%m-%Y') as bom_startdate,DATE_FORMAT(bom_enddate,'%d-%m-%Y') as bom_enddate"
                . " FROM retaline_offer_management WHERE 1=1 AND bom_type = 0 {$filter_qry}";
        $data = $db->getMultipleData($qry, true);

        $countQuery = "SELECT COUNT(*) FROM retaline_offer_management WHERE 1=1 AND bom_type = 0 {$filter_qry}";
        $count = $db->getItemFromDB($countQuery);
        for ($i = 0; $i < $count; $i++) {
            if ($data[$i]['bom_status'] == 1) {
                $status = 'Active';
            } else {
                $status = 'Inactive';
            }
            $detailSql = $db->getFromDB("SELECT stii_itemmastername,stii_mrp,stii_selpri,stii_epraft FROM finascop_stock_item_inventory WHERE stii_fpoid = {$data[$i]['stiid_fpoid']} AND stii_itemmasterid = {$data[$i]['stiid_itemmasterid']} LIMIT 1", true);
            $data[$i]['bmo_mrp'] = $detailSql['stii_mrp'];
            $data[$i]['bmo_epr'] = $detailSql['stii_epraft'];
            $data[$i]['bmo_itemName'] = $detailSql['stii_itemmastername'];
            $data[$i]['itemCount'] = '';
            $data[$i]['bmo_offrrate'] = $db->getItemFromDB("SELECT fpod_itemoffrrate FROM finascop_purchase_order_details WHERE fpod_fpoId = {$data[$i]['stiid_fpoid']} AND fpod_itemid = {$data[$i]['stiid_itemmasterid']} ");
            $data[$i]['bom_status'] = $status;
        }

        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'searchOfferMgmt':
        $stiid_itemmasterid = $_POST['itemname'];
        $itemname = $db->getItemSafe("SELECT stit_itemName FROM finascop_stock_itemmaster where stit_ID = ?", "i", [$_POST['itemname']]);
        $itemname = addslashes($_POST['itemname']);
        if ($itemname != '') {
            $cond = " and stii_itemmastername = '{$itemname}'";
        } else {
            $cond = " ";
        }
        //$sql = " SELECT COUNT(*) as iItemsRemaining,stiid_fpoid,stiid_itemmasterid,stiid_itemmastername FROM finascop_stock_item_inventorydetails WHERE stiid_status < 5 and stiid_itemmastername LIKE '%{$itemname}%' GROUP BY stiid_fpoid";
        $sql = " SELECT COUNT(*) as iItemsRemaining,stiid_fpoid,stiid_itemmasterid,stiid_itemmastername FROM finascop_stock_item_inventorydetails WHERE stiid_status < 5 and stiid_itemmasterid = {$stiid_itemmasterid} GROUP BY stiid_fpoid";
        $datas = $db->getMulipleData($sql, true);
//        $countQuery = "SELECT COUNT(*) FROM finascop_stock_item_inventory 
//INNER JOIN finascop_purchase_order ON fpo_id = stii_fpoid AND fpo_Active = 1 WHERE 1=1 {$cond}  ORDER BY stii_itemmastername ASC";
//        $listQuery = "SELECT stii_id,stii_fpoid,stii_fpodid,stii_itemmasterid,stii_itemmastername,fpo_poNumber,stii_fpodid,
//(SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stiid_status < 5 AND stiid_itemmasterid = stii_itemmasterid) AS iItemsRemaining
//FROM finascop_stock_item_inventory 
//INNER JOIN finascop_purchase_order ON fpo_id = stii_fpoid AND fpo_Active = 1 WHERE 1=1 {$cond}  ORDER BY stii_itemmastername ASC";
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $datas[$i]['fpo_poNumber'] = $db->getItemFromDB("SELECT fpo_poNumber FROM finascop_purchase_order WHERE fpo_id = {$datas[$i]['stiid_fpoid']} ");
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
            //echo json_encode($qry);
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        //$db->printGridJson($countQuery, $listQuery);
        break;
    case 'offerFormLoad':
        $fpo_id = $_POST['fpo_id'];
        $item_id = $_POST['item_id'];
        $bmd_percent = 100;
        $sql = "SELECT fpo_poNumber,stii_fpoid,stii_fpodid,stii_itemmasterid,stii_itemmastername,stii_mrp,stii_selpri,stii_epraft,{$bmd_percent} as bmd_percent,bmd_company as companypd_old,bmd_hub as hubpd_old,finascop_stock_item_inventory.bmdd_id,"
                . "bmd_incentive as incentivepd_old,bmd_technology as technologypd_old,bmd_customer as customerpd_old,"
                . "(SELECT fpod_itemoffrrate from finascop_purchase_order_details where fpod_id = stii_fpodid) as offerRate FROM finascop_stock_item_inventory "
                . " INNER JOIN finascop_purchase_order ON fpo_id = stii_fpoid "
                . "INNER JOIN retaline_margindistributions ON retaline_margindistributions.bmd_id = finascop_stock_item_inventory.bmdd_id "
                . "WHERE stii_fpoid ={$fpo_id} AND stii_itemmasterid = {$item_id} Limit 1";
        $results = $db->getFromDB($sql, true);
        if (!$results) {
            echo '{"success":true,"data":[]}';
        } else {
            echo '{"success":true, "data":',
            json_encode($results),
            '}';
        }
        break;
    case 'saveOfferPercentage':
        $data['stiid_fpoid'] = $_POST['PoId'];
        $data['stiid_itemmasterid'] = $_POST['itemId'];
        $data['bom_bmdCompany'] = $_POST['companypd_new'];
        $data['bom_bmdHub'] = $_POST['hubpd_new'];
        $data['bom_bmdIncentive'] = $_POST['incentivepd_new'];
        $data['bom_bmdTechnology'] = $_POST['technologypd_new'];
        $data['bom_bmdCustomer'] = $_POST['customerpd_new'];
        $data['bom_offrPlacement'] = $_POST['offerlacement'];
        $data['bom_offrDiffer'] = $_POST['difference'];
        $data['bom_offrPromotion'] = $_POST['promotion'];
        $data['bom_offrSupplier'] = $_POST['supplier'];
        $data['bom_narration'] = $_POST['narration'];
        $data['bom_startdate'] = date('Y-m-d', strtotime($_POST['offer_start']));
        $data['bom_offfrvalidtype'] = $_POST['offer_type'];
        if ($_POST['offer_type'] == 'Till stock lasts') {
            $data['bom_enddate'] = date('Y-m-d', strtotime('+10 years'));
        } else {
        $data['bom_enddate'] = date('Y-m-d', strtotime($_POST['offer_end']));
        }

        $data['bom_createdOn'] = date("Y-m-d H:i:s");
        $data['bom_createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $data['bom_itemsRemaining'] = $_POST['itemsRemaining'];
        $count = $db->getItemSafe("SELECT COUNT(1) FROM retaline_offer_management WHERE stiid_fpoid = ? AND stiid_itemmasterid = {$_POST['itemId']} AND bom_status = 1 AND bom_type = 0", "i", [$_POST['PoId']]);
        if ($count > 0) {
            echo "{'success':true,'valid':false,'msg': 'Offer already created..'}";
            exit();
        }
        $db->query('begin');
        $status = $db->perform('retaline_offer_management', $data);
        $lastId = $db->insert_id();
        $return_rec = $db->getFromDb("SELECT * FROM retaline_offer_management WHERE bom_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'msg': 'Error While Saving.'}";
        }
        break;
    case 'ofperDetailsView':
        $bom_id = isset($_POST['bom_id']) ? intval($_POST['bom_id']) : 0;
        if ($bom_id > 0) {
            $result = $db->getFromDB("SELECT bom_bmdCompany,bom_bmdHub,bom_bmdIncentive,bom_bmdTechnology,bom_bmdCustomer,bom_offrPlacement,bom_offrDiffer,bom_offrPromotion,bom_offrSupplier,bom_narration,stiid_fpoid,stiid_itemmasterid,"
                    . "DATE_FORMAT(bom_startdate,'%d-%m-%Y') as bom_startdate,DATE_FORMAT(bom_enddate,'%d-%m-%Y') as bom_enddate,bom_offfrvalidtype FROM retaline_offer_management WHERE bom_id = " . $bom_id, true);
            $result['bmo_offrrate'] = $db->getItemFromDB("SELECT fpod_itemoffrrate FROM finascop_purchase_order_details WHERE fpod_fpoId = {$result['stiid_fpoid']} AND fpod_itemid = {$result['stiid_itemmasterid']} ");
            $result['success'] = true;
            echo json_encode($result);
        }
        break;
    case 'getItemName':
        if ($_POST['query'] != '') {
            $que = addslashes($_POST['query']);
            $searchQuery = " AND stit_SKU LIKE '%{$que}%'";
        } else {
            $searchQuery = '';
        }

        //finascop_getjsonkeyarray("SELECT stit_ID,stit_itemName,stit_SKU FROM finascop_stock_itemmaster where 1=1 {$searchQuery} ");
        $qry = $db->getMulipleData("SELECT stit_ID,stit_itemName,stit_SKU FROM finascop_stock_itemmaster where 1=1 {$searchQuery}", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'listCouponOfferMgmt':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 12;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'bom_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        $filter_qry = "";
        if (isset($_POST['filter'])) {
            $allowedFields = ['offer_id', 'offer_name', 'offer_type', 'offer_start', 'offer_end', 'offer_status'];
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* if (isset($filter)) {

          foreach ($filter as $key => $field) {
          if ($field['data']['value'] != "") {
          $checkComa = strstr($field['data']['value'], ',');
          if ($checkComa != '') {
          $filter_qry = $field['data']['value'];
          $filter_qry= str_replace(',', "','", $filter_qry);
          $search .= " and ({$field['field']} IN('{$filter_qry}')) ";
            } else {
          if ($field['field'] == 'bom_status') {
          $filter_qry = ($filter[0][data][value] == 'Active') ? 1 : 0;
          $search .= " and ({$field[field]} = {$filter_qry}) ";
          } else {
          $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
            }
            $data[$i]['bom_typeName'] = $typeName;
            $data[$i]['bmo_Name'] = $bomName;
            $data[$i]['bom_status'] = $status;
        }
          }
          }
          } */
        if ($sort == 'bom_id') {
            $sort = 'bom_startdate';
            $sort = 'bom_enddate';
        }
        $date = date('dd-mm-YYYY');

        $qry = "SELECT bom_id,
CASE
WHEN bom_offerType = 1 THEN 'Offer'
WHEN bom_offerType = 2 THEN 'Category'
WHEN bom_offerType = 3 THEN 'Item'
END AS bom_typeName,
CASE
WHEN bom_offerType = 1 THEN bom_offerCode
WHEN bom_offerType = 2 THEN (SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id = bom_subCategoryId)
WHEN bom_offerType = 3 THEN (SELECT stit_SKU FROM finascop_stock_itemmaster WHERE  stit_ID = stiid_itemmasterid)
END AS bmo_Name,
CASE
WHEN bom_status = 1 THEN 'Active'
WHEN bom_status = 0 THEN 'Inactive'
END AS bom_status,
bom_bmdCompany,bom_bmdHub,bom_bmdIncentive,bom_bmdTechnology,bom_bmdCustomer,bom_offrPlacement,
bom_offrPromotion,bom_offrSupplier,stiid_fpoid,stiid_itemmasterid,bom_type,
DATE_FORMAT(bom_startdate,'%d-%m-%Y') AS bom_startdate,DATE_FORMAT(bom_enddate,'%d-%m-%Y') AS bom_enddate,
bom_offerCode,bom_subCategoryId,bom_offerType,bom_locked
FROM retaline_offer_management WHERE 1=1 AND bom_type = 1 {$filter_qry} ORDER BY $sort $dir limit {$start},{$limit}";
        $countQuery = "SELECT COUNT(*) FROM retaline_offer_management WHERE 1=1 AND bom_type = 1 {$filter_qry}";
        $count = $db->getItemFromDB($countQuery);
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'saveCouponOffer':
        $offerDetails['bom_type'] = 1;
        $offerDetails['bom_offerType'] = $_POST['bom_offerType'];
        $offerDetails['bom_use'] = $_POST['bom_use'];
        if ($_POST['offerlacement'] > 0) {
            $offerDetails['bom_offrPlacement'] = $_POST['offerlacement'];
        }
        if ($_POST['bom_offerCode'] != '') {
            $offerDetails['bom_offerCode'] = $_POST['bom_offerCode'];
        }
        if ($_POST['couponItems'] > 0) {
            $offerDetails['stiid_itemmasterid'] = $_POST['couponItems'];
        }

        if ($_POST['product_category'] > 0) {
            $offerDetails['bom_subCategoryId'] = $_POST['product_category'];
        }

        $offerDetails['bom_bmdCompany'] = $_POST['bom_bmdCompany'];
        $offerDetails['bom_bmdHub'] = $_POST['bom_bmdHub'];
        $offerDetails['bom_bmdIncentive'] = $_POST['bom_bmdIncentive'];
        $offerDetails['bom_bmdTechnology'] = $_POST['bom_bmdTechnology'];
        $offerDetails['bom_offrSupplier'] = $_POST['bom_offrSupplier'];
        $offerDetails['bom_offrPromotion'] = $_POST['bom_offrPromotion'];
        $offerDetails['bom_offrSupplier'] = $_POST['bom_offrSupplier'];
        $offerDetails['bom_narration'] = $_POST['narration'];
        $offerDetails['bom_startdate'] = date('Y-m-d', strtotime($_POST['offer_start']));
        $offerDetails['bom_offfrvalidtype'] = $_POST['offer_type'];
        $offerDetails['bom_enddate'] = date('Y-m-d', strtotime($_POST['offer_end']));
        $db->query('begin');
        if ($_POST['bom_id'] > 0) {
            
        } else {
            $offerDetails['bom_status'] = 1;
            $offerDetails['bom_createdOn'] = date("Y-m-d H:i:s");
            $offerDetails['bom_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('retaline_offer_management', $offerDetails);
        }
        $lastId = $db->insert_id();
        $return_rec = $db->getFromDb("SELECT * FROM retaline_offer_management WHERE bom_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'msg': 'Error While Saving.'}";
        }
        break;
    case 'couponcategoryStore':
        $qry = $db->getMulipleData("SELECT sub_category_id,sub_category FROM mypha_productsubcategory WHERE status = 1", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'ofperDetailsViewCoupon':
        $bom_id = isset($_POST['bom_id']) ? intval($_POST['bom_id']) : 0;
        if ($bom_id > 0) {
            $result = $db->getFromDB("SELECT bom_bmdCompany,bom_bmdHub,bom_bmdIncentive,bom_bmdTechnology,bom_bmdCustomer,bom_offrPlacement,bom_offrDiffer,bom_offrPromotion,bom_offrSupplier,bom_narration,bom_subCategoryId,stiid_itemmasterid,"
                    . "DATE_FORMAT(bom_startdate,'%d-%m-%Y') as bom_startdate,DATE_FORMAT(bom_enddate,'%d-%m-%Y') as bom_enddate,bom_offfrvalidtype,bom_offerCode,bom_offerType,bom_status FROM retaline_offer_management WHERE bom_id = " . $bom_id, true);
            if ($result['bom_offerType'] == 1) {
                $typeName = 'Offer';
                $bomName = $result['bom_offerCode'];
            } else if ($result['bom_offerType'] == 2) {
                $typeName = 'Category';
                $bomName = $db->getItemFromDB("SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id = {$result['bom_subCategoryId']}");
            } else if ($result['bom_offerType'] == 3) {
                $typeName = 'Item';
                $bomName = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE  stit_ID = {$result['stiid_itemmasterid']}");
            }
            if ($result['bom_status'] == 1) {
                $status = 'Active';
            } else {
                $status = 'Inactive';
            }
            $result['bom_typeName'] = $typeName;
            $result['bmo_Name'] = $bomName;
            $result['bom_status'] = $status;
            $result['success'] = true;
            echo json_encode($result);
        }
        break;
    case 'offerStatus':
        $bom_id = isset($_POST['bom_id']) ? intval($_POST['bom_id']) : 0;
        $db->query('begin');
        if ($_POST['bom_id'] > 0) {
            $st['bom_status'] = 0;
            $st['bom_updatedOn'] = date("Y-m-d H:i:s");
            $st['bom_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('retaline_offer_management', $st, 'update', " bom_id = {$bom_id}");
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Details saved ' }";
        } else {
            echo "{'success':False,'valid':false,'msg': 'Error While Saving.'}";
        }
        break;
}