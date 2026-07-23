<?php

switch ($op) {
    case 'list':
        $cpd_id = $_POST['current_branch_id'];
        // $cbrid = $_POST['current_branch_id'];
        if ($cpd_id != '') {
            $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
            $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
            $sort = empty($sort) ? 'order_id' : $sort;
            $dir = empty($dir) ? 'DESC' : $dir;
            $search = " WHERE 1=1 ";
            if (isset($data['filter'])) {
        $allowedFields = ['ac_id', 'order_id', 'order_generated_id', 'driver_name', 'ac_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
                }
            }

            $countQuery = "SELECT COUNT(*) FROM retaline_customer_order cpo "
                    . "INNER JOIN finascop_company cp ON cp.comp_id = cpo.order_company_id "
                    . "INNER JOIN retaline_customer_order_status st ON st.status_id=cpo.status_id "
                    . "INNER JOIN finascop_branch fb ON fb.br_ID = cpo.order_branch_id WHERE cpo.order_branch_id = {$cpd_id} AND cpo.status_id IN (5,6,7,8,9)";

            $listQuery = "SELECT order_id,order_order_id,order_customer_id,order_branch_id,st.admin_description AS status,DATE_FORMAT(order_confirm_date,'%d-%m-%Y') as order_confirm_date,br_Name AS branch_name,cp.comp_name AS company_name "
                    . "FROM retaline_customer_order cpo "
                    . "INNER JOIN finascop_company cp ON cp.comp_id = cpo.order_company_id "
                    . "INNER JOIN retaline_customer_order_status st ON st.status_id=cpo.status_id "
                    . "INNER JOIN finascop_branch fb ON fb.br_ID = cpo.order_branch_id WHERE cpo.order_branch_id = {$cpd_id} AND cpo.status_id IN (5,6,7,8,9)" . " ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
            $db->printGridJson($countQuery, $listQuery);
        }

        break;

    case 'assignExecutive':
        
        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'ASSIGNEXEC'");
        $fields = array(
            "is_cpd" => 0,
            "order_id" => $_POST['order_ID'],
            "boy_id" => $_POST['id'],
            "branch_id" => $_POST['br_ID']
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
            CURLOPT_HTTPHEADER  => array('Content-Type: application/json')
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        //print_r($data);

        //header("Content-Type: application/pdf"); 
        //header("Content-disposition: attachment; filename=axz.pdf");
        //this->data->Output('test.pdf', 'D')
        header("Content-Type: application/json");
        echo $data;
        break;

    case 'loadExecutive':
        //$branch_ID=$_POST['branch_ID'];
        $order_branch_id = $_POST['order_branch_id'];
        $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'name' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' 1=1';

        // if (isset($data['filter'])) {
        //     foreach ($data['filter'] as $key => $val) {
        //         $filter_part .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
        //     }
        // }

        $countQuery = "SELECT COUNT(*) from retaline_godown_boy where  has_open_orders = 0 AND COALESCE(fcm_id,'') <> ''  AND branch_id={$order_branch_id} AND is_offline = 0";
        $listQuery = "SELECT id,name,phone,has_open_orders from retaline_godown_boy WHERE {$filter_part} AND has_open_orders = 0 AND COALESCE(fcm_id,'') <> '' AND branch_id={$order_branch_id}  AND is_offline = 0 ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'save':
        break;
}
?>
