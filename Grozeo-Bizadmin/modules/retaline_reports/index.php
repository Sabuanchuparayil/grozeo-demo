<?php

require_once(INCLUDE_PATH . "/finascop_User.php");
global $db;
switch ($op) {
    case 'getsalessmryGridData':
        $salesrep_from_Date = $_POST['salesrep_from_Date'];
        $salesrep_to_Date = $_POST['salesrep_to_Date'];
        $branchName = $_POST['branchName'];
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 23;
        $start = is_numeric($start) ? $start : 0;

        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'order_id' : $sort;
        //$sort = ($sort=='order_created_on') ? 'DATE(order_created_on)' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $userID = $_SESSION['admin']->UserId;
        $filter_qry = "WHERE 1=1 ";

        $order = '';
        if (isset($_POST['filter'])) {
            $allowedFields = ['date_from', 'date_to', 'br_id', 'report_type', 'order_status'];
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        $user = new \finascop\User();
        $items = $user->getUserActiveBranches($_SESSION['admin']->finascop_typId, $_SESSION['admin']->Finascop_UserId, $_SESSION['admin']->finascop_current_company_id);
        $id_branch = array_column($items, '0');
        $branchids = implode(',', $id_branch);

//        if ($_POST['current_branch_id'] > 0) {
//            $current_branch_id = $_POST['current_branch_id'];
//        } else {
//            $current_branch_id = $db->getItemSafe("SELECT br_ID FROM finascop_branch WHERE br_Name = ?", "s", [$_POST['br_Name']]);
//        }

        if ($branchName > 0) {
            $cond = " AND order_branch_id IN({$branchName})";
        } else {
            $cond = " ";
        }
        if ($sort == 'order_created_on') {
            $sort = 'order_id';
        }
        if ($_POST['br_Name'] != '') {
            $brNameFilter = $db->sanitizeString($_POST['br_Name']);
            $filter_qry .= " AND br_Name  LIKE  '{$brNameFilter}%'";
        }


        $query = " SELECT order_id,order_order_id,order_packedbags_count,bco.order_customer_id,order_branch_id,br_Name,bco.status_id as status,DATE_FORMAT(created_at,'%d-%m-%Y') AS order_created_on,
             TIME_FORMAT(cast(created_at as time),'%r') as ordertime,admin_description AS order_status,admin_description,order_payment_gateway_refid,order_payment_gateway_refid_crc32,(order_total_cgst + order_total_sgst+order_kfc_amount) AS totalGST,
            order_customer_name AS delivery_to,(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS cust_mobile,order_delivery_charge,order_invoiceamt,DATE_FORMAT(order_delivered_date,'%d-%m-%Y') AS order_delivered_date,
            order_HasReturn,order_ItemsReturned,order_ReturnVerified,created_at,order_amounts,order_amount_payable,payment_mode,total,order_total_amount,order_total_gst,comp_name,
           (order_total_amount+order_total_cgst + order_total_sgst+order_kfc_amount) as totalAmount,order_invoiceno,order_roundoff,
            CASE WHEN payment_mode = 1 THEN 'Pay On Delivery' 
            WHEN payment_mode = 2 THEN 'Online Payment' 
            WHEN payment_mode = 3 THEN 'Wallet' 
            WHEN payment_mode = 4 THEN 'COD with Wallet'
            WHEN payment_mode = 5 THEN 'Online with Wallet'
            WHEN payment_mode = 6 THEN 'Online on Delivery'
            WHEN payment_mode = 7 THEN 'Cash on Delivery' END AS order_paymode
            FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id INNER JOIN finascop_company ON comp_id = order_company_id  WHERE 1 = 1 and bco.status_id IN(17,18) {$cond} AND  DATE_FORMAT(created_at,'%Y-%m-%d') "
                . "BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}'";

        $_SESSION['salesreportqry'] = $query;
        $countQuery = $db->getItemFromDB(" SELECT COUNT(*) FROM ({$query}) AS orerCount {$filter_qry} ORDER BY  {$sort} {$dir} ");
        $listQuery = "SELECT * FROM({$query}) as orderList  {$filter_qry}  ORDER BY  {$sort} {$dir} limit " . $start . "," . $limit;
//CAST({$sort} as char) {$dir},binary {$sort} {$dir}
        //$db->printGridJson($countQuery, $listQuery);
//INNER JOIN retaline_customer_order_delivery_address bcoda ON bcoda.order_id = bco.order_id
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $orderLoc = $db->getFromDB("SELECT order_latitude,order_longitude FROM retaline_customer_order_delivery_address WHERE customer_order_id = {$datas[$i]['order_id']}", true);
                $datas[$i]['order_latitude'] = $orderLoc['order_latitude'];
                $datas[$i]['order_longitude'] = $orderLoc['order_longitude'];
                // $datas[$i]['order_paymode'] = $payMod;
            }
            //print_r($datats);
        }
        echo '{"totalCount":"', $countQuery, '","data":' . json_encode($datas) . '}';
        break;
    case 'getsalesexport':
        require(THIS_MODULE_PATH . "/function.php");
//
//        $lastParameters = json_decode(stripslashes(($_POST["filterData"])), true);
//        $_POST['name'] = 'order_salesSmry_';
//        for ($i = 0; $i <= $i; $i++) {
//            if (array_key_exists('filter[' . $i . '][field]', $lastParameters)) {
//                $filterParams[$i]['field'] = $lastParameters['filter[' . $i . '][field]'];
//                unset($lastParameters['filter[' . $i . '][field]']);
//                $filterParams[$i]['data']['type'] = $lastParameters['filter[' . $i . '][data][type]'];
//                unset($lastParameters['filter[' . $i . '][data][type]']);
//                $filterParams[$i]['data']['value'] = $lastParameters['filter[' . $i . '][data][value]'];
//                unset($lastParameters['filter[' . $i . '][data][value]']);
//                $filterParams[$i]['data']['comparison'] = $lastParameters['filter[' . $i . '][data][comparison]'];
//                unset($lastParameters['filter[' . $i . '][data][comparison]']);
//            } else {
//                break;
//            }
//        }
//        $_POST['filter'] = $filterParams;
//
//        //$filterdata = json_decode($_POST['filterData'],true);
//        //array_push($_POST,$filterdata) ;
//        foreach ($lastParameters as $keys => $values) {
//            $_POST[$keys] = $values;
//        }
//        $_POST['start'] = 0;
//        $_POST['limit'] = 100000;
//        $qry = $this->_getsalessmryGridData($_POST);
//        //print_r($qry['listQuery']);
//        $_SESSION['Export']['Query'] = $_SESSION['tasktabexcel'];
//        $_SESSION['Export']['Settings']['title'] = time() . "_";
//        _exportExcelReport($_POST);

        $data = $_POST; //json_decode(stripslashes($_POST['e']), true);
        $win = $data['wisize'];
        $title = $data['activeTabtitle'];
        retalineExportToExcel($data, false, $win, $title);
        break;
    case 'getBranchName':
        //$branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $qry = $db->getMulipleData("SELECT br_ID,br_Name FROM finascop_branch  inner join finascop_branch_company on finascop_branch_company.br_Id = finascop_branch.br_ID WHERE br_status = 'Active' and comp_id = {$_SESSION['admin']->CompanyId}", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'getsalesbrnchsmryGridData':
        $salesrep_from_Date = $_POST['salesrep_from_Date'];
        $salesrep_to_Date = $_POST['salesrep_to_Date'];
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;

        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'order_branch_id' : $sort;
        //$sort = ($sort=='order_created_on') ? 'DATE(order_created_on)' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $userID = $_SESSION['admin']->UserId;
        $filter_qry = "WHERE 1=1 ";

        $order = '';
        if (isset($_POST['filter'])) {
            $allowedFields = ['date_from', 'date_to', 'br_id', 'report_type', 'order_status'];
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        $user = new \finascop\User();
        $items = $user->getUserActiveBranches($_SESSION['admin']->finascop_typId, $_SESSION['admin']->Finascop_UserId, $_SESSION['admin']->finascop_current_company_id);
        $id_branch = array_column($items, '0');
        $branchids = implode(',', $id_branch);



        if ($sort == 'order_created_on') {
            $sort = 'order_id';
        }
        if ($_POST['br_Name'] != '') {
            $brNameFilter = $db->sanitizeString($_POST['br_Name']);
            $filter_qry .= " AND br_Name  LIKE  '{$brNameFilter}%'";
        }
//'br_Name','orderCOunt','PoDCount','PaidCount','totalAmount','totalGST','totalAmt','order_confirmed_on','order_delivered_date','created_at','order_method','updated_at'

        $query = " SELECT order_branch_id,br_Name,COUNT(*) AS orderCOunt,SUM(IF(payment_mode=1 OR payment_mode=4 OR payment_mode=6 OR payment_mode=7,1,0)) AS `PoDCount`,"
                . "SUM(IF(payment_mode=1 OR payment_mode=4 OR payment_mode=6 OR payment_mode=7,0,1)) AS `PaidCount`,SUM(order_delivery_charge) AS deliveryCharge,SUM(order_total_amount) AS orderValue,SUM(order_roundoff) AS orderRoudoff,"
                . "SUM(order_total_amount+order_total_cgst + order_total_sgst+order_kfc_amount) AS totalAmount,SUM(order_total_cgst + order_total_sgst+order_kfc_amount) AS totalGST,SUM(total) AS totalAmt,"
                . "order_confirmed_on,order_delivered_date,created_at,order_method,updated_at FROM retaline_customer_order bco INNER JOIN finascop_branch ON br_ID = order_branch_id "
                . "WHERE 1 = 1 AND bco.status_id IN(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,23,24)  {$cond} AND  DATE_FORMAT(created_at,'%Y-%m-%d') "
                . "BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}' GROUP BY order_branch_id";

        $_SESSION['salesbrncsmryreportqry'] = $query;
        $countQuery = $db->getItemFromDB(" SELECT COUNT(*) FROM ({$query}) AS orerCount {$filter_qry} ORDER BY  {$sort} {$dir} ");
        $listQuery = "SELECT * FROM({$query}) as orderList  {$filter_qry}  ORDER BY  {$sort} {$dir} limit " . $start . "," . $limit;
        $datas = $db->getMulipleData($listQuery, true);
        echo '{"totalCount":"', $countQuery, '","data":' . json_encode($datas) . '}';
        break;
    case 'getsalesbrnchexport':
        require(THIS_MODULE_PATH . "/function.php");
        $data = $_POST; //json_decode(stripslashes($_POST['e']), true);
        $win = $data['wisize'];
        $title = $data['activeTabtitle'];
        retalineExportToExcel($data, false, $win, $title);
        break;
    case 'getPendingOrderGridData':
        $salesrep_from_Date = $_POST['salesrep_from_Date'];
        $salesrep_to_Date = $_POST['salesrep_to_Date'];
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 24;
        $start = is_numeric($start) ? $start : 0;

        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'br_id' : $sort;
        //$sort = ($sort=='order_created_on') ? 'DATE(order_created_on)' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $userID = $_SESSION['admin']->UserId;
        $filter_qry = "WHERE 1=1 ";

        $order = '';
        if (isset($_POST['filter'])) {
            $allowedFields = ['date_from', 'date_to', 'br_id', 'report_type', 'order_status'];
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        $user = new \finascop\User();
        $items = $user->getUserActiveBranches($_SESSION['admin']->finascop_typId, $_SESSION['admin']->Finascop_UserId, $_SESSION['admin']->finascop_current_company_id);
        $id_branch = array_column($items, '0');
        $branchids = implode(',', $id_branch);



        if ($sort == 'order_created_on') {
            $sort = 'order_id';
        }
        if ($_POST['br_Name'] != '') {
            $brNameFilter = $db->sanitizeString($_POST['br_Name']);
            $filter_qry .= " AND br_Name  LIKE  '{$brNameFilter}%'";
        }

        /* $query = "SELECT br_id,br_Name,
          (SELECT COUNT(*) FROM retaline_customer_order  WHERE  order_branch_id = finascop_branch.br_id AND status_id IN(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,20,23)  AND DATE_FORMAT(created_at,'%Y-%m-%d') BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}' ) AS orderCOunt,
          (SELECT COUNT(*) FROM retaline_customer_order  WHERE  order_branch_id = finascop_branch.br_id AND status_id IN(4,5,6,7,8,23)  AND DATE_FORMAT(created_at,'%Y-%m-%d') BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}' ) AS noPackorderCOunt,
          (SELECT SUM(order_total_amount+order_total_cgst + order_total_sgst+order_kfc_amount) FROM retaline_customer_order  WHERE  order_branch_id = finascop_branch.br_id AND status_id > 4 AND status_id <> 19 AND status_id <> 24 AND DATE_FORMAT(created_at,'%Y-%m-%d') BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}') AS totalAmount,
          (SELECT COUNT(*) FROM retaline_customer_order  WHERE  order_branch_id = finascop_branch.br_id AND status_id IN (10,11,12,13,14,15,16,20,17,18) AND DATE_FORMAT(created_at,'%Y-%m-%d') BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}') AS pkdOrders,
          (SELECT COUNT(*) FROM retaline_customer_order  WHERE  order_branch_id = finascop_branch.br_id AND (status_id=18 OR status_id=17)   AND DATE_FORMAT(created_at,'%Y-%m-%d') BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}') AS deliveredOrders,
          (SELECT COUNT(*) FROM retaline_customer_order  WHERE  order_branch_id = finascop_branch.br_id AND status_id=9 AND DATE_FORMAT(created_at,'%Y-%m-%d') BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}') AS pendngOrders,
          (SELECT SUM(order_total_amount+order_total_cgst + order_total_sgst+order_kfc_amount) FROM retaline_customer_order  WHERE  order_branch_id = finascop_branch.br_id AND status_id=9 AND DATE_FORMAT(created_at,'%Y-%m-%d') BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}') AS pendngOrderValue
          FROM  finascop_branch ORDER BY br_Name ASC "; */
        //br_id,br_Name,orderCOunt,packedOrders,packingpending,orderrecived,deliveredOrders,deliverypending,totalpending,pendngOrderValue
        $query = "SELECT br_id,br_Name,(SELECT COUNT(*) FROM retaline_customer_order  WHERE  order_branch_id = finascop_branch.br_id AND status_id IN(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,20,23)  AND DATE_FORMAT(created_at,'%Y-%m-%d') BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}' ) AS orderCOunt,"
                . "@packedOrders:=(SELECT COUNT(*) FROM retaline_customer_order  WHERE  order_branch_id = finascop_branch.br_id AND status_id IN (9,10,11,12,13,14,15,16,20,17,18) AND DATE_FORMAT(created_at,'%Y-%m-%d') BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}') AS packedOrders,
@packingpending:=(SELECT COUNT(*) FROM retaline_customer_order  WHERE  order_branch_id = finascop_branch.br_id AND status_id IN(4,5,6,7,8)  AND DATE_FORMAT(created_at,'%Y-%m-%d') BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}' ) AS packingpending,
(@packedOrders+@packingpending) AS orderrecived,
@deliveredOrders:=(SELECT COUNT(*) FROM retaline_customer_order  WHERE  order_branch_id = finascop_branch.br_id AND (status_id=18 OR status_id=17)   AND DATE_FORMAT(created_at,'%Y-%m-%d') BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}') AS deliveredOrders,
@deliverypending:=(SELECT COUNT(*) FROM retaline_customer_order  WHERE  order_branch_id = finascop_branch.br_id AND status_id IN (9,10,11,12,13,14,15,16) AND DATE_FORMAT(created_at,'%Y-%m-%d') BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}') AS deliverypending,
@packingpending+@deliverypending AS totalpending,
 (SELECT SUM(total) FROM retaline_customer_order  WHERE  order_branch_id = finascop_branch.br_id AND status_id IN (4,5,6,7,8,9,10,11,12,13,14,15,16) AND DATE_FORMAT(created_at,'%Y-%m-%d') BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}') AS pendngOrderValue
  FROM  finascop_branch ORDER BY br_Name ASC ";


        $_SESSION['pendingbrncsmryreportqry'] = $query;
        $countQuery = $db->getItemFromDB(" SELECT COUNT(*) FROM ({$query}) AS orerCount {$filter_qry} ORDER BY  {$sort} {$dir} ");
        $listQuery = "SELECT * FROM({$query}) as orderList  {$filter_qry}  ORDER BY  {$sort} {$dir} limit " . $start . "," . $limit;
        $datas = $db->getMulipleData($listQuery, true);
        echo '{"totalCount":"', $countQuery, '","data":' . json_encode($datas) . '}';
        break;
    case 'getPendingOrderexport':
        require(THIS_MODULE_PATH . "/function.php");
        $data = $_POST; //json_decode(stripslashes($_POST['e']), true);
        $win = $data['wisize'];
        $title = $data['activeTabtitle'];
        retalineExportToExcel($data, false, $win, $title);
        break;
    case 'getsalessmryGridDatamnthly':
        $salesrep_from_Date = $_POST['salesrep_from_Date'];
        $salesrep_to_Date = $_POST['salesrep_to_Date'];
        $branchName = $_POST['branchName'];
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 23;
        $start = is_numeric($start) ? $start : 0;

        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'order_id' : $sort;
        //$sort = ($sort=='order_created_on') ? 'DATE(order_created_on)' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $userID = $_SESSION['admin']->UserId;
        $filter_qry = "WHERE 1=1 ";

        $order = '';
        if (isset($_POST['filter'])) {
            $allowedFields = ['date_from', 'date_to', 'br_id', 'report_type', 'order_status'];
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        $user = new \finascop\User();
        $items = $user->getUserActiveBranches($_SESSION['admin']->finascop_typId, $_SESSION['admin']->Finascop_UserId, $_SESSION['admin']->finascop_current_company_id);
        $id_branch = array_column($items, '0');
        $branchids = implode(',', $id_branch);
        if ($branchName > 0) {
            $cond = " AND order_branch_id IN({$branchName})";
        } else {
            $cond = " ";
        }
        if ($sort == 'order_created_on') {
            $sort = 'order_id';
        }
        if ($_POST['br_Name'] != '') {
            $brNameFilter = $db->sanitizeString($_POST['br_Name']);
            $filter_qry .= " AND br_Name  LIKE  '{$brNameFilter}%'";
        }

        $query = "SELECT order_id,order_order_id,br_Name,DATE_FORMAT(created_at,'%d-%m-%Y') AS order_created_on,
             TIME_FORMAT(CAST(created_at AS TIME),'%r') AS ordertime,admin_description AS order_status,
            order_customer_name AS delivery_to,(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS cust_mobile,order_delivery_charge,order_amount_payable,total,order_total_amount,order_total_gst,
           (order_total_amount+order_total_cgst + order_total_sgst+order_kfc_amount) AS totalAmount,order_roundoff,bco.status_id AS STATUS,
            CASE WHEN payment_mode = 1 THEN 'Pay On Delivery' 
            WHEN payment_mode = 2 THEN 'Online Payment' 
            WHEN payment_mode = 3 THEN 'Wallet' 
            WHEN payment_mode = 4 THEN 'COD with Wallet'
            WHEN payment_mode = 5 THEN 'Online with Wallet'
            WHEN payment_mode = 6 THEN 'Online on Delivery'
            WHEN payment_mode = 7 THEN 'Cash on Delivery' END AS order_paymode
            FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id  WHERE 1 = 1 AND bco.status_id > 0 {$cond} AND  DATE_FORMAT(created_at,'%Y-%m-%d') BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}'";

        $query = " SELECT order_id,order_order_id,order_packedbags_count,bco.order_customer_id,order_branch_id,br_Name,bco.status_id as status,DATE_FORMAT(created_at,'%d-%m-%Y') AS order_created_on,
             TIME_FORMAT(cast(created_at as time),'%r') as ordertime,admin_description AS order_status,admin_description,order_payment_gateway_refid,order_payment_gateway_refid_crc32,(order_total_cgst + order_total_sgst+order_kfc_amount) AS totalGST,
            order_customer_name AS delivery_to,(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS cust_mobile,order_delivery_charge,order_invoiceamt,DATE_FORMAT(order_delivered_date,'%d-%m-%Y') AS order_delivered_date,
            order_HasReturn,order_ItemsReturned,order_ReturnVerified,created_at,order_amounts,order_amount_payable,payment_mode,total,order_total_amount,order_total_gst,comp_name,
           (order_total_amount+order_total_cgst + order_total_sgst+order_kfc_amount) as totalAmount,order_invoiceno,order_roundoff,
            CASE WHEN payment_mode = 1 THEN 'Pay On Delivery' 
            WHEN payment_mode = 2 THEN 'Online Payment' 
            WHEN payment_mode = 3 THEN 'Wallet' 
            WHEN payment_mode = 4 THEN 'COD with Wallet'
            WHEN payment_mode = 5 THEN 'Online with Wallet'
            WHEN payment_mode = 6 THEN 'Online on Delivery'
            WHEN payment_mode = 7 THEN 'Cash on Delivery' END AS order_paymode
            FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id INNER JOIN finascop_company ON comp_id = order_company_id  WHERE 1 = 1 and bco.status_id IN(17,18) {$cond} AND  DATE_FORMAT(created_at,'%Y-%m-%d') "
                . "BETWEEN '{$salesrep_from_Date}' AND '{$salesrep_to_Date}'";

        $_SESSION['salesreportmnthlyqry'] = $query;
        $countQuery = $db->getItemFromDB(" SELECT COUNT(*) FROM ({$query}) AS orerCount {$filter_qry} ORDER BY  {$sort} {$dir} ");
        $listQuery = "SELECT * FROM({$query}) as orderList  {$filter_qry}  ORDER BY  {$sort} {$dir} limit " . $start . "," . $limit;
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        
        echo '{"totalCount":"', $countQuery, '","data":' . json_encode($datas) . '}';
        break;
    case 'getsalesexportmnthly':
        require(THIS_MODULE_PATH . "/function.php");
        $data = $_POST; //json_decode(stripslashes($_POST['e']), true);
        $win = $data['wisize'];
        $title = $data['activeTabtitle'];
        retalineExportToExcel($data, false, $win, $title);
        break;
}
