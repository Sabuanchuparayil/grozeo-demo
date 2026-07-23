<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(INCLUDE_PATH . "/finascop_wallet_client.php");
switch ($op) {
    case 'listBarCodesOfItem':
        $data = $_POST;
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 12;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'stiid_barcode' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        $bbsd_id = $data['bbsd_id'];

        $db->query("SET @slNo = {$start};");
        $listQuery = "SELECT @slNo := @slNo + 1 as slNo, stiid_barcode FROM retaline_B2B_SalesOrderDetails_barcodes
         WHERE bbsd_id = {$bbsd_id} ORDER BY {$sort} {$dir} LIMIT {$start},{$limit}";
        $countDataQuery = "SELECT count(1) from retaline_B2B_SalesOrderDetails_barcodes WHERE bbsd_id = {$bbsd_id} ";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
    case 'updateHandlingCharges':
        $data = $_POST;
        $db->query("UPDATE retaline_B2B_SalesOrder SET bbso_SOValue = bbso_SOValue - bbso_HandlingCharges + {$data['bbso_HandlingCharges']},
        bbso_InvValAtax = bbso_InvValAtax - bbso_HandlingCharges + {$data['bbso_HandlingCharges']} WHERE bbso_id = {$data['bbso_id']}");

        $status = $db->query("UPDATE retaline_B2B_SalesOrder SET bbso_HandlingCharges = {$data['bbso_HandlingCharges']} WHERE bbso_id = {$data['bbso_id']}");

        $con = "bbso_id = {$data['bbso_id']}";
        $TotAmount = $db->getItemFromDB("SELECT bbso_InvValAtax FROM retaline_B2B_SalesOrder WHERE {$con}");
        $paise = round($TotAmount - ($Ruppes = floor($TotAmount)), 2) * 100;
        $B2BSalesInvData['bbso_totInFig'] = "Rupees " . $Ruppes . " and " . $paise . " Paise";
        $B2BSalesInvData['bbso_totInWords'] = getIndianCurrency(number_format((float) $TotAmount, 2, '.', ''));

        $data['bbso_updatedon'] = date("Y-m-d H:i:s");
        $data['bbso_updatedby'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('retaline_B2B_SalesOrder', $B2BSalesInvData, 'update', $con);

        if ($status == 1) {
            echo '{"success":true}';
        } else {
            echo '{"success":false}';
        }
        break;
    case 'printInvoice':
        $data = $_GET;
        CreateInvoicePDF($data['bbso_InvNumber'], $data['bbso_id']);
        break;
    case 'saveB2BSalesInvoice':
        $bbso_id = $_POST['bbso_id'];
        $db->query('begin');
        if (!empty($bbso_id)) {
            $neIinvDetails = $db->getFromDB("SELECT COALESCE(MAX(bbso_invid) + 1,0) as bbso_invid, CONCAT('INV',DATE_FORMAT(CURDATE(),'%Y'),LPAD(COALESCE(MAX(bbso_invid) + 1,0), 6, '0')) AS bbso_InvNumber FROM retaline_B2B_SalesOrder", true);
            $data['bbso_InvNumber'] = $neIinvDetails['bbso_InvNumber'];
            $data['bbso_invid'] = $neIinvDetails['bbso_invid'];
            $data['bbso_InvDate'] = date("Y-m-d H:i:s");
            $data['status_id'] = 9;
            $data['bbso_InvoiceStatus'] = 2;

            $data['bbso_InvByUserName'] = $db->getItemFromDB("SELECT UserName FROM finascop_usr_master WHERE UserId = " . $_SESSION['admin']->Finascop_UserId);
            $data['bbso_InvIPAddress'] = getIPAddress();

            $con = "bbso_id = {$bbso_id}";
            $status = $db->perform('retaline_B2B_SalesOrder', $data, 'update', $con);
        } else {
            $status = 0;
        }


        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'B2B Sales Invoice {$data['bbso_InvNumber']} is ready.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error while creating B2B Sales Invoice.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'b2bSOCustomerDetails':

        $b2bSOCustomer = $_POST['b2bSOCustomer'];
        $result = $db->getFromDB("SELECT b2b_Customer_Incharge,b2b_Customer_pincode,b2b_Customer_Mobile,b2b_Customer_gst,rbsch_name"
                . "  FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = {$b2bSOCustomer}", true);
        if (!empty($result)) {
            echo json_encode($result);
        } else {
            echo '{"data":[]}';
        }
        break;
    case 'getB2BBrCustomers':
        $result = $db->getMulipleData("SELECT b2b_Customer_ID,b2b_Customer_Name FROM retaline_B2Bcustomer WHERE b2b_Customer_status='Active' AND br_ID = {$_SESSION['admin']->finascop_current_branch_id}", true);
        if (!empty($result)) {
            echo '{"totalCount":' . count($result) . ',"data":' . json_encode($result) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'getB2BSODetails':
        $data = $_POST;
        $query = "SELECT bbso_id, bbso_SONumber, bbso_SODate,b2b_Customer_ID,b2b_Customer_Name,bbso_SOValue,"
                . "bbso_HandlingCharges ,(SELECT status from retaline_B2B_Status rbbs WHERE rbbs.status_id = rbs.status_id)AS bbso_Active "
                . "FROM retaline_B2B_SalesOrder rbs WHERE bbso_id = {$data['bbso_id']}";
        $B2BSalesOrderData = $db->getFromDB($query, true);
        $listQuery = "SELECT bbso_id,bbsd_id,b2bso_itemid, b2bso_itemname, b2bso_itemmrp, b2bso_itemqty, b2bso_itemrate,b2bso_gst,b2bso_itemoffrqty,"
                . "b2bso_itemPkg,b2bso_amount,b2bso_discountpercent,b2bso_discountamt, b2bso_netamount "
                . "FROM retaline_B2B_SalesOrderDetails WHERE bbso_id = {$data['bbso_id']}";
        $B2BSOItemDetails = $db->getMultipleData($listQuery, true);

        if (!empty($B2BSOItemDetails)) {
            echo '{"totalCount":' . count($B2BSOItemDetails) . ',"data":' . json_encode($B2BSOItemDetails) . ',"SOdata":' . json_encode($B2BSalesOrderData) . '}';
        } else {
            echo '{"totalCount":"0","data":[],"SOdata":[]}';
        }

        break;
    case 'generateUniqueID':
        $uniqueId = '';
        while ($uniqueId == '') {
            $uniqueId = getNewFinascopApiKey();
        }
        echo '{"uid":"' . $uniqueId . '"}';
        break;

    case 'listB2BSalesInvoces':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 23;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'bbso_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;

        $filter_qry = " WHERE bbso_InvoiceStatus IN (1,2) ";
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    } else if ($val['field'] == 'bbso_InvoiceStatusName') {
                            if ($val['data']['value'] == 'Invoiced') {
                                $fiterItem = 2;
                                $filter_qry .= " and (bbso_InvoiceStatus= {$fiterItem}) ";
                            } else {
                                $fiterItem = 1;
                                $filter_qry .= " and (bbso_InvoiceStatus = {$fiterItem}) ";
                            }
                        }
                        break;
                }
            }
        }

        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $filter_qry .= " ";
           /* if ($_SESSION['admin']->IsSuperUser == 'Yes') {
                $filter_qry .= " ";
            } else {
                $filter_qry .= " AND  br_ID = {$_SESSION['admin']->finascop_current_branch_id} ";
            }*/
        } else {
            $filter_qry .= " AND  br_ID = {$_SESSION['admin']->finascop_current_branch_id} ";
        }

        $countDataQuery = "SELECT count(1) from retaline_B2B_SalesOrder {$filter_qry} ";
        $listQuery = "SELECT bbso_id, bbso_InvNumber, bbso_InvDate,b2b_Customer_ID,b2b_Customer_Name,bbso_SOValue,bbso_InvoiceStatus,"
                . "CASE WHEN bbso_InvoiceStatus = 0 THEN 'Not Ready for Invoice' WHEN bbso_InvoiceStatus = 1 THEN 'Ready for Invoice' WHEN bbso_InvoiceStatus = 2 THEN 'Invoiced' END AS bbso_InvoiceStatusName,"
                . "bbso_HandlingCharges ,(SELECT status from retaline_B2B_Status rbbs WHERE rbbs.status_id = rbs.status_id) AS bbso_Active,status_id "
                . "FROM retaline_B2B_SalesOrder rbs {$filter_qry} ORDER BY {$sort} {$dir} LIMIT {$start},{$limit}";

        $db->printGridJson($countDataQuery, $listQuery);
        break;
    case 'getSMSReportGridData':
        $postvar = $_POST;
        $limit = $postvar['limit'];
        $start = $postvar['start'];
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'smsemaillog_id' : $sort;
//        $sort = ($sort=='order_created_on') ? 'DATE(order_created_on)' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $userID = $_SESSION['admin']->UserId;
        $filter_qry = "WHERE 1=1 AND smsemail_id <> '' AND  smsemail_text NOT LIKE  '%Invalid address%' ";
        $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];


        $order = '';
        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }


        if ($sort == 'smsemail_datetime') {
            $sort = 'smsemaillog_id';
        }

        if (!empty($_POST['smssearch_stock_from_date'])) {
            $filter_qry .= " and DATE_FORMAT(smsemail_datetime, '%Y-%m-%d') = '" . date('Y-m-d', strtotime($_POST['smssearch_stock_from_date'])) . "'";
        }


        $query = " SELECT smsemaillog_id,smsemail_id,smsemail_datetime,smsemail_text,issms,sms_responseid,'SMS' AS typeof FROM sms_email_logs   
 UNION
 SELECT veri_id AS smsemaillog_id,veri_mobile AS smsemail_id,veri_smsgen_dt AS smsemail_datetime,veri_sms_code AS smsemail_text,veri_sms_status AS issms,veri_issend_sms AS sms_responseid,'Email' AS typeof FROM retaline_customer_signup_verifiLog "; //AND order_branch_id = {$current_branch_id}
        $countQuery = " SELECT COUNT(*) FROM ({$query}) AS orerCount {$filter_qry} ORDER BY  {$sort} {$dir} ";
        $listQuery = "SELECT * FROM({$query}) as orderList  {$filter_qry}  ORDER BY  {$sort} {$dir} limit " . $start . "," . $limit;

        $data = $db->getMultipleData($listQuery, true);

        $count = $db->getItemFromDB($countQuery);


        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
}