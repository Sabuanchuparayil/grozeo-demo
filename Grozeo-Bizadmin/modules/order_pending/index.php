<?php

require_once(INCLUDE_PATH . "/finascop_User.php");
global $db;

function updateOrderLog($log_entry) {
    global $db;
    $log_entry['action_by'] = $_SESSION['admin']->UserId;
    $log_entry['action_at'] = 'now()';
    $db->perform('order_log', $log_entry);
}

switch ($op) {
    case 'getPendingOrders':

        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) ? $limit : 20;
        $start = is_numeric($start) ? $start : 0;

        $_allowed_sort = ['order_id', 'order_created_on', 'order_total_amount'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'order_id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'order_id' : $sort;
        //$sort = ($sort=='order_created_on') ? 'DATE(order_created_on)' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $userID = $_SESSION['admin']->UserId;
        $filter_qry = "WHERE 1=1 ";

        $order = '';
        if (isset($_POST['filter'])) {
        $allowedFields = ['order_generated_id', 'member_phone', 'order_created_on', 'order_status', 'order_user_type', 'order_total_amount'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
                }
            }
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


        if ($sort == 'order_created_on') {
            $sort = 'order_id';
        }
        if ($_POST['br_Name'] != '') {
            $filter_qry .= " AND br_Name  LIKE  '" . $_POST['br_Name'] . "%'";
        }


        $query = " SELECT order_id,order_order_id,order_packedbags_count,bco.order_customer_id,order_branch_id,br_Name,bco.status_id as status,DATE_FORMAT(created_at,'%d-%m-%Y') AS order_created_on,
             TIME_FORMAT(cast(created_at as time),'%r') as ordertime,admin_description AS order_status,admin_description,order_payment_gateway_refid,order_payment_gateway_refid_crc32,
            (SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS delivery_to,(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS cust_mobile,
            order_HasReturn,order_ItemsReturned,order_ReturnVerified,created_at,order_total_amount,order_convertedBy,order_isConverted,
            (SELECT FirstName FROM finascop_usr_profile WHERE UserId = order_convertedBy) as order_convertedByName,CASE WHEN order_isConverted = 1 THEN 'Converted' WHEN order_isConverted = 0 THEN 'Not Converted' END AS order_isConvertedType
            FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN finascop_branch ON br_ID = order_branch_id  WHERE 1 = 1 and bco.status_id = 21 AND order_branch_id IN ({$branchids})";
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
            }
            echo '{"totalCount":"', $countQuery, '","data":' . json_encode($datas) . '}';
        }
        break;


    case 'detailsView':
        $order_auto_id = isset($_POST['order_auto_id']) ? intval($_POST['order_auto_id']) : 0;

        if ($order_auto_id > 0) {
            $data = $db->getFromDB(" SELECT order_auto_id ,order_generated_id, order_user_type, order_user_mobile,order_total_amount, 
            order_status_config_name as order_status,order_tax,DATE_FORMAT(order_created_on,'%d-%m-%Y') as order_created_on, request_generated_id"
                    . " FROM order_table "
                    . " inner join order_status_config on order_status_config_id = order_status"
                    . " WHERE order_auto_id =' " . $order_auto_id . "'", true);

            $data['success'] = true;
            echo json_encode($data);
        }

        break;
    case "order_details":
        require(THIS_MODULE_PATH . "/order_details.php");
        break;
    case 'oreder_userlog_dtlsview':
        ob_start();
        include('order_log.php');
        $resHtml = ob_get_clean();
        echo $resHtml;
        break;

    case 'convertOrderToSuccess':
        $orderId = $_POST['orderId'];
        if ($orderId > 0) {
            $cfg_Value = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'ORDERSUCCESS'");
            $url = str_replace('{orderId}', $orderId, $cfg_Value);

            $data['order_payment_gateway_refid'] = $_POST['orderTransactionNumber'];
            $data['order_payment_gateway_refid_crc32'] = crc32($_POST['orderTransactionNumber']);
            $data['order_isConverted'] = 1;
            $data['order_convertedBy'] = $_SESSION['admin']->UserId;
            $data['order_convertedOn'] = date('Y-m-d H:i:s');
            $db->query('begin');
            $status = $db->perform('retaline_customer_order', $data, 'update', " order_id = {$orderId}");

            //$fields_string = json_encode($fields);
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
            $status = $db->query('commit');
            header("Content-Type: application/json");
            echo $data;
            }
        break;
}
