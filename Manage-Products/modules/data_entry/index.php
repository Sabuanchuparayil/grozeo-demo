<?php

switch ($op) {

    case 'checkMobile':
        $count = $db->getItemFromDB("select (select count(brand_ambassador_id) from brand_ambassador where brand_ambassador_mobile='{$_POST['mobile']}' ) +"
                . " (select count(customer_id) from customers where customer_mobile='{$_POST['mobile']}') as sumcount");
        /* $count = $db->getItemSafe("select count(*) from customers where customer_mobile = ?", "s", [$_POST['mobile']]); */
        if ($count > 0) {
            echo '{"success":true,"valid":false, "msg": "This mobile number is already occupied. Please enter another one."}';
        } else {
            echo '{"success":true,"valid":true}';
        }
        break;


    case 'saveDataEntry':

        $data = $_POST;

        unset($data['customer_id']);

        $dt = $db->getItemSafe("SELECT dst_id FROM `postoffice` WHERE pincode = ?", "s", [$_POST['customer_pin']]);

        if (!empty($dt)) {
            $st = $db->getItemFromDB("SELECT mst_district_state_id FROM mst_district WHERE district_id = '{$dt}'");
        }

        $data['customer_state'] = $st;
        $data['customer_district'] = $dt;
        $data['created_on'] = 'now()';
        $data['created_by'] = $_SESSION['admin']->UserId;

        $status = $db->perform('customers', $data);
        echo '{"success":true,"valid":true}';
        break;

    case 'listCustomers':

        $limit = (isset($_POST['limit']) && is_numeric($_POST['limit'])) ? $_POST['limit'] : 20;
        $start = (isset($_POST['start']) && is_numeric($_POST['start'])) ? $_POST['start'] : 0;

        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'cust_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');



        $filter_qry = " WHERE 1 = 1 ";
        if (isset($_POST['filter'])) {
        $allowedFields = ['de_id', 'de_voucher_no', 'de_date', 'de_type', 'de_amount', 'de_status'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }

        $countQuery = "SELECT COUNT(*) "
                . " FROM retaline_customer {$filter_qry}";


        $listQuery = "SELECT cust_id,cust_customer_id,cust_customer_name,cust_mobile,cust_email,cust_walletbalance,cust_prom_reward_point,cust_status,cust_ref_code,cust_branch_id/*,cust_status*/ from retaline_customer {$filter_qry}"
                . "ORDER BY {$sort} {$dir} LIMIT {$start},{$limit} ";

        $db->printGridJson($countQuery, $listQuery);


        break;

    case 'pickData':
        $dt = $db->getItemSafe("SELECT dst_id FROM `postoffice` WHERE pincode = ?", "s", [$_POST['pin']]);

        if (!empty($dt)) {
            $dist_st = $db->getFromDB("SELECT district_name,mst_district_state_id FROM mst_district WHERE district_id = '{$dt}'", true);

            $st = $db->getItemFromDB("SELECT state_name FROM mst_state WHERE state_id = '{$dist_st['mst_district_state_id']}'", true);
            if (!empty($st))
                echo '{"success":true,"valid":true,"district":"' . $dist_st['district_name'] . '","state":"' . $st . '"}';
            else
                echo '{"success":true,"valid":false, "msg": "This PIN code is not found in the system."}';
        } else {
            echo '{"success":true,"valid":false, "msg": "This PIN code is not found in the system."}';
        }
        break;

    case 'changeStatus';

        $data['status'] = ($_POST['status'] == 'Active') ? 'Inactive' : 'Active';

        $status = $db->perform('customers', $data, 'update', "customer_id = " . intval($_POST['cust_id']));
        echo '{"success":true,"valid":true}';
        break;
    case 'listDeliveryAddress':
        $customerId = $_POST['customerId'];
        $rec_sort = empty($data['sort']) ? 'created_at' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['de_id', 'de_voucher_no', 'de_date', 'de_type', 'de_amount', 'de_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        $countQuery = "SELECT COUNT(*) FROM retaline_customer_delivery_info WHERE deli_customer_id = {$customerId}";
        $listQuery = "SELECT  deli_name,deli_delivery_pin,deli_house_no,deli_house_name,deli_land_mark,deli_is_primary FROM retaline_customer_delivery_info WHERE deli_customer_id = {$customerId}";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'listWalletHistory':
        $customerId = $_POST['customerId'];
        $rec_sort = empty($data['sort']) ? 'created_at' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['de_id', 'de_voucher_no', 'de_date', 'de_type', 'de_amount', 'de_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        $countQuery = "SELECT COUNT(*) FROM retaline_customer_wallet_transaction  WHERE cust_id = {$customerId}";
        $listQuery = "SELECT (select order_order_id from retaline_customer_order where order_id=refentry_id) as  orderno, brcw_Amount as  orderamount, brcw_AddInfo as  orderinfo, brcw_CreatedOn as date FROM retaline_customer_wallet_transaction  WHERE cust_id = {$customerId} order by brcw_id";
        $db->printGridJson($countQuery, $listQuery);
        break;
}
