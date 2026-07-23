<?php

switch ($op) {
    case 'generateUniqueId':
        $uniqueId = '';
        while ($uniqueId == '') {
            $uniqueId = getNewFinascopApiKey();
        }

        echo '{"uid":"' . $uniqueId . '"}';
        break;
    case 'saveItemPOPreqDetails':
        $adhocCount = $db->getItemSafe("SELECT COUNT(*) FROM finascop_purchase_order_poprereq WHERE prereq_uniqueid = ? AND prereq_updatedon = '{$_POST['poprereq_updatedon']}'", "i", [$_POST['fpopredet_uniqueid']]);
//        if ($_POST['poprereq_updatedon'] != '' && $adhocCount > 0) {
//            echo '{"success":false,"msg":"Reload data updation is going on."}';
//            exit();
//        }
        $db->query('begin');
        if ($adhocCount > 0) {
            $updatedDate = $db->getItemSafe("SELECT prereq_updatedon FROM finascop_purchase_order_poprereq WHERE prereq_uniqueid = ?", "s", [$_POST['fpopredet_uniqueid']]);
            if ($updatedDate == $_POST['poprereq_updatedon']) {
                $adhocData['prereq_updatedon'] = date("Y-m-d H:i");
                $adhocData['prereq_updatedby'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('finascop_purchase_order_poprereq', $adhocData, 'update', " prereq_uniqueid = '{$_POST['fpopredet_uniqueid']}'");
            } else {
                echo '{"success":false,"msg":"Reload data updation is going on."}';
                exit();
            }
        } else {
            $adhocData['prereq_uniqueid'] = $_POST['fpopredet_uniqueid'];
            $adhocData['prereq_vendor'] = $_POST['fpopredet_vendorid'];
            $adhocData['prereq_createdon'] = date("Y-m-d H:i");
            $adhocData['prereq_createdby'] = $_SESSION['admin']->Finascop_UserId;
            $adhocData['prereq_updatedon'] = date("Y-m-d H:i");
            $adhocData['prereq_updatedby'] = $_SESSION['admin']->Finascop_UserId;
            $adhocData['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;

            $status = $db->perform('finascop_purchase_order_poprereq', $adhocData);
        }

        $data['branch_id'] = $_SESSION['admin']->finascop_current_branch_id;
        $data['fpopredet_uniqueid'] = $_POST['fpopredet_uniqueid'];
        $data['fpopredet_vendorid'] = $_POST['fpopredet_vendorid'];
        $data['fpopredet_itemid'] = $_POST['fpopredet_itemid'];
        $data['fpopredet_itemname'] = $_POST['fpopredet_itemname'];
        $data['fpopredet_itemqty'] = $_POST['fpopredet_itemqty'];
        $data['fpopredet_createdon'] = date("Y-m-d H:i:s");
        $data['fpopredet_createdby'] = $_SESSION['admin']->Finascop_UserId;
        $data['fpopredet_purchasingUnit'] = $db->getItemFromDB("SELECT csb_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$data['fpopredet_itemid']}");

        $db->query('begin');
        $dup = $db->getItemFromDB("SELECT COUNT(1) FROM finascop_poprereq_details WHERE fpopredet_uniqueid = '{$data['fpopredet_uniqueid']}' and fpopredet_itemid = {$data['fpopredet_itemid']}");

        if ($dup > 0) {
            $con = "fpopredet_uniqueid = '{$data['fpopredet_uniqueid']}' and fpopredet_itemid = {$data['fpopredet_itemid']}";
            $status = $db->perform('finascop_poprereq_details', $data, 'update', $con);
        } else {
            $status = $db->perform('finascop_poprereq_details', $data);
        }
        $newupdatedDate = $db->getItemSafe("SELECT prereq_updatedon FROM finascop_purchase_order_poprereq WHERE prereq_uniqueid = ?", "s", [$_POST['fpopredet_uniqueid']]);
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Item PO Prerequisite details saved successfully.'";
            echo '{"success":true,"date":"' . $newupdatedDate . '","msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving invoice.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'listPoPrereqdetailsStore':
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }
        $fpopredet_uniqueid = $_POST['fpopredet_uniqueid'];
        $countDataQuery = "SELECT count(*) from finascop_poprereq_details where fpopredet_uniqueid = '{$fpopredet_uniqueid}' {$filter_qry} ";
        $listQuery = "SELECT  fpopredet_vendorid,fpopredet_itemid,fpopredet_itemname,fpopredet_itemmrp,fpopredet_itemqty,fpopredet_itemoffrqty,if(fpopredet_itemoffrqty > 0,CONCAT(fpopredet_itemqty,'+',fpopredet_itemoffrqty),fpopredet_totalqty) as fpopredet_totalqty,fpopredet_balanceqty,fpopredet_itemoffrrate,fpopredet_itemoffrrateet,fpopredet_itemaddidisc,fpopredet_effectiverate,"
                . "fpopredet_idiscountcalculs,fpopredet_netamount,fpopredet_amount,fpopredet_initialnetamount,fpopredet_gendiscount,fpopredet_shippingcharge,"
                . "IF(fpopredet_itemaddidisc > 0,(CONCAT(fpopredet_itemaddidisc,'',IF(fpopredet_idiscountcalculs = 'Amount',' Rs',' %'))),'') AS itemDisc from finascop_poprereq_details where fpopredet_uniqueid = '{$fpopredet_uniqueid}' ORDER BY fpopredet_createdon ASC ";
        $db->printGridJson($countDataQuery, $listQuery);

        break;
    case 'prerqSaveClose':
        $fpopredet_uniqueid = $_POST['fpopredet_uniqueid'];
        $adhocpo['prereq_billingTo'] = $_POST['prereq_billingTo'];

        if ($_POST['isConfirm'] == 1) {
            $adhocpo['prereq_name'] = 'PR' . time();
            $adhocpo['prereq_status'] = 1;
            $prereqpode['fpopredet_prereqname'] = $adhocpo['prereq_name'];
        } else {
            $adhocpo['prereq_status'] = 0;
        }


        $adhocpo = array_filter($adhocpo);
        if (!empty($fpopredet_uniqueid) && (count($adhocpo) > 0)) {
            $db->query('begin');
            $adcon = " prereq_uniqueid = '{$fpopredet_uniqueid}'";
            $status = $db->perform('finascop_purchase_order_poprereq', $adhocpo, 'update', $adcon);

            $prereqpode['fpopredet_prereqname'] = $adhocpo['prereq_name'];
            $status = $db->perform('finascop_poprereq_details', $prereqpode, 'update', " fpopredet_uniqueid = '{$fpopredet_uniqueid}'");
            $status = $db->query('commit');
        }


        if ($status == 1) {
            $msg = "'Prerequisite PO saved.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving data.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'getPurchaseOrderPrereqisite':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 12;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'fpot_createdon' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        $filter_qry = " WHERE 1 = 1  ";
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }
//        else {
//            $filter_qry .= " and (fpo_Active = 1) ";
//        }
if ($_SESSION['admin']->br_PyramidLevel == 1) {
    $filter_qry .= " ";
    /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
        $filter_qry .= " ";
    } else {
        $filter_qry .= " AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} " ;
    }*/
} else {
    $filter_qry .= " AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} " ;
}
        $date = date('Y-m-d');
        $countDataQuery = "SELECT count(*) from finascop_purchase_order_poprereq fp  {$filter_qry}  ORDER BY prereq_createdon DESC";
        $listQuery = "SELECT  prereq_uniqueid ,prereq_name,prereq_createdby,(SELECT stpa_Fname FROM finascop_stock_party WHERE stpa_id = prereq_vendor) as vendorName,prereq_status,prereq_createdon
 FROM finascop_purchase_order_poprereq fp  {$filter_qry} ORDER BY prereq_createdon DESC LIMIT $start,$limit ";
        $db->printGridJson($countDataQuery, $listQuery);

        break;
    case 'deletePrereqDetails':
        $db->query('begin');
        $del_query = "DELETE FROM finascop_purchase_order_poprereq WHERE prereq_uniqueid='{$_POST['prereq_uniqueid']}'";
        $temp = $db->query($del_query);
        if ($temp) {
            $del_query = "DELETE FROM finascop_poprereq_details WHERE fpopredet_uniqueid='{$_POST['prereq_uniqueid']}'";
            $db->query($del_query);
        }
        $status = $db->query('commit');
        if (status) {
            echo "{success:true,valid:true,message:'Deleted Succesfully ' }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Deleteing.'}";
        }
        break;
    case 'loadPrereqPO':

        $podata = $db->getFromSafe("SELECT  prereq_uniqueid ,prereq_name ,DATE_FORMAT(prereq_createdon,'%d-%m-%Y %H:%i:%s') as prereq_createdon,prereq_createdby,prereq_updatedon,
            prereq_poValue,prereq_paymentTerms,prereq_paymentValue,prereq_validityType,prereq_shippingcharge,prereq_gdiscount,prereq_gdiscounttype,
            (SELECT stpa_Fname FROM finascop_stock_party WHERE stpa_id = prereq_vendor) as vendorName,prereq_vendor,prereq_billingTo
 FROM finascop_purchase_order_poprereq fp  {$filter_qry} where prereq_uniqueid = ?", "s", [$_POST['uniqueid']], true);
        if (!empty($podata)) {
            echo json_encode($podata);
        }

        break;
    case 'printPOPreDetails':
        ob_start();
        include('podetails.php');
        $resHtml = ob_get_contents();
        ob_end_clean();
        echo $resHtml;
        exit();
        break;
    case 'deleteVendorItemFromPOPrereq':
        $itemid = $_POST['itemid'];
        $uid = $_POST['uid'];
        $db->query('begin');
        $delquery = "DELETE FROM finascop_poprereq_details  WHERE fpopredet_uniqueid = '{$uid}' AND fpopredet_itemid = {$itemid}";
        $status = $db->query($delquery);

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Deleted Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while deleting data' }}";
        }
        break;
}