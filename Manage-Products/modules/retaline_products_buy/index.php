<?php

$userid = $_SESSION['admin']->Finascop_UserId;
switch ($op) {
    case 'listItemMasterData':
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'finascop_stock_itemmaster.stit_ID' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $search = " WHERE 1=1 and rup_status = 1 ";
        if (isset($filter)) {
            $filter = $_POST['filter'];
            foreach ($filter as $key => $val) {
                $type = $val['data']['type'];
                $value = $val['data']['value'];
                $field = $val['field'];
                switch ($val['data']['type']) {
                    case 'string':
                        if ($val['field'] == 'hsn_code') {
                            $hsn_code = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(hsn_id),0) FROM finascop_hsn WHERE hsn_code LIKE '{$val['data']['value']}%' ");
                            $search .= " AND stit_HSNCode IN({$hsn_code}) ";
                        } else if ($val['field'] == 'statusName') {
                            if ($val['data']['value'] == 'Active') {
                                $search .= " and stit_status = 1 ";
                            } else if ($val['data']['value'] == 'Inactive') {
                                $search .= " and stit_status = 0 ";
                            } else {
                                $search .= " and stit_status IN(1,0) ";
                            }
                        } else if ($val['field'] == 'isVerified') {
                            if ($val[data][value] == 'Yes') {
                                $search .= " and (isVerified = 1) ";
                            } else if ($val[data][value] == 'No') {
                                $search .= " and (isVerified = 0) ";
                            } else {
                                $search .= " and (isVerified IN(1,0)) ";
                            }
                        } else {
                            $search .= " and ({$val['field']} LIKE '{$val['data']['value']}%') ";
                        }
                        break;
                    case 'numeric' :
                        if ($val['field'] == 'tax') {
                            $search .= " AND stit_GST " . $comparisons[$val['data']['comparison']] . " " . $val['data']['value'];
                        } else {
                            $search .= " AND " . $val['field'] . " " . $comparisons[$val['data']['comparison']] . " " . $val['data']['value'];
                        }
                        break;
                }
            }
        }
        $countQuery = "SELECT count(*) from retaline_user_products inner join finascop_stock_itemmaster on finascop_stock_itemmaster.stit_ID = retaline_user_products.stit_id {$search}  order by {$sort} {$dir}";

        $qry = "SELECT * FROM retaline_user_products inner join finascop_stock_itemmaster on finascop_stock_itemmaster.stit_ID = retaline_user_products.stit_id {$search}  order by {$sort} {$dir}  ";
        $count = $db->getItemFromDB($countQuery);
        $listQuery = "{$qry} limit {$rec_start},{$rec_limit}";
        $db->printGridJson($countQuery, $listQuery);

        break;
    case 'saveUserProducts':
        $itemar = $_POST['itemarr'];
        $itemType = $_POST['itemtype'];

        $itemdecode = json_decode($itemar);
        $itemcount = count($itemdecode);
        $db->query('begin');
        for ($i = 0; $i < $itemcount; $i++) {
            $data = array(
                "UserId" => $userid,
                "stit_id" => $itemdecode[$i],
                "stit_type" => $itemType,
                "rup_status" => 0,
            );
            $itemdup = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_user_products WHERE UserId = {$userid} AND stit_id = {$itemdecode[$i]} AND stit_type = {$itemType}");
            if ($itemdup == 0) {
                $status = $db->perform(FINASCOP_DB . 'retaline_user_products', $data);
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listSelectedItems':
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'rup_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }


        $customerId = $userid;
        if ($userid > 0) {

            $qry = "SELECT UserId,rup_id,stit_type,IF(stit_type = 1,'Medicine','Product') AS itemType,finascop_stock_itemmaster.stit_id AS itemId,stit_SKU AS itemName,stit_brand_name,stit_quantity,least_package_type_name,
                stit_category_name,stit_product_variant,stit_itemName
                    FROM retaline_user_products INNER JOIN finascop_stock_itemmaster ON finascop_stock_itemmaster .stit_id= retaline_user_products.stit_id WHERE UserId='{$customerId}' AND rup_status = 0 ORDER BY $rec_sort $rec_sort_dir";

            $data = $db->getMultipleData($qry, true);

            $countQuery = "SELECT COUNT(*) FROM retaline_user_products s  WHERE s.UserId='{$customerId}'  AND rup_status = 0 ";
            $count = $db->getItemFromDB($countQuery);


            if (!empty($data)) {
                echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
            } else {
                echo '{"totalCount":"0","data":[]}';
            }
        }
        break;
    case 'retProdctListing':

        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'stit_SKU' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $item_name = $_POST['currentItem'];
        $item_id = $_POST['current_type'];
        $cond = " WHERE 1=1 ";
        //1:brand,2:item,3:Make
        if ($item_name != '') {
            $cond .= " AND stit_SKU LIKE '%{$item_name}%'";
        }
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        switch ($item_id) {
            case 1:


                $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} AND isMedicine=1";
                $count = $db->getItemFromDB($countQuery);

                $qry = "SELECT stit_itemName,finascop_stock_itemmaster.stit_ID AS stit_ID,stit_brand_name,stit_SKU,stit_quantity,least_package_type_name,stit_category_name,stit_product_variant  
                    FROM finascop_stock_itemmaster {$cond} AND isMedicine = 1 AND stit_status = 1  and stit_ID NOT IN (SELECT stit_id FROM retaline_user_products "
                    . "INNER JOIN finascop_usr_profile ON finascop_usr_profile.UserId = retaline_user_products.UserId  WHERE CompanyId = {$_SESSION['admin']->CompanyId}) ORDER BY {$sort} {$dir}";
                $data = $db->getMultipleData($qry, true);
                break;
            case 2:

                $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} AND isMedicine=0";
                $count = $db->getItemFromDB($countQuery);

                $qry = "SELECT stit_itemName,finascop_stock_itemmaster.stit_ID AS stit_ID,stit_brand_name,stit_SKU,stit_quantity,least_package_type_name,stit_category_name,stit_product_variant  
                    FROM finascop_stock_itemmaster {$cond} AND isMedicine = 0 AND stit_status = 1  and stit_ID NOT IN (SELECT stit_id FROM retaline_user_products "
                    . "INNER JOIN finascop_usr_profile ON finascop_usr_profile.UserId = retaline_user_products.UserId  WHERE CompanyId = {$_SESSION['admin']->CompanyId}) ORDER BY {$sort} {$dir}";
                $data = $db->getMultipleData($qry, true);
                break;
            case 3:

                $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} ";
                $count = $db->getItemFromDB($countQuery);

                $qry = "SELECT stit_itemName,finascop_stock_itemmaster.stit_ID AS stit_ID,stit_brand_name,stit_SKU,stit_quantity,least_package_type_name,stit_category_name,stit_product_variant  "
                        . "FROM finascop_stock_itemmaster {$cond} ";
                $data = $db->getMultipleData($qry, true);
                break;
        }
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'confirmMappedPrdcts':

        $userItemstoMap = $db->getMultipleData("SELECT * FROM retaline_user_products WHERE UserId = {$userid} AND rup_status = 0", true);
        $db->query('begin');
        foreach ($userItemstoMap as $userItems) {
            $data = array(
                "UserId" => $userid,
                "stit_id" => $userItems['stit_id'],
                "stit_type" => $userItems['stit_type'],
                "ruph_createdOn" => date('Y-m-d H:i:s'),
                "ruph_createdBy" => $userid
            );
            $status = $db->perform('retaline_user_products_log', $data);

            $rupdata['rup_status'] = 1;
            $status = $db->perform('retaline_user_products', $rupdata, 'update', " UserId = {$userid} AND rup_status = 0");
        }

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'deleteSelectedUserItems':
        $id = $_POST['id'];
        $del_query = "DELETE FROM retaline_user_products WHERE rup_id =" . $id;
        $db->query($del_query);
        $status = $db->query('commit');
        if ($status) {

            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listPprdctPurchaseLog':
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'ruph_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $search = " WHERE 1=1 ";
        $IsSuperUser = $_SESSION['admin']->IsSuperUser;
        if ($IsSuperUser == 'Yes') {
            $search .= " ";
        } else {
            $search .= " and UserId = {$userid} ";
        }
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }
        $countQuery = "SELECT count(*) from retaline_user_products_log  {$search} GROUP BY DATE_FORMAT(ruph_createdOn,'%d-%m-%Y') order by {$sort} {$dir}";

        $qry = "SELECT * FROM retaline_user_products_log {$search} GROUP BY DATE_FORMAT(ruph_createdOn,'%d-%m-%Y') order by {$sort} {$dir}  ";
        $count = $db->getItemFromDB($countQuery);
        $listQuery = "{$qry} limit {$rec_start},{$rec_limit}";
        $datas = $db->getMultipleData($listQuery, true);
        //$db->printGridJson($countQuery, $listQuery);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $datas[$i]['ruph_createdOn'] = date('Y-m-d', strtotime($datas[$i]['ruph_createdOn']));
                $itemCount = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_user_products_log {$search} AND DATE_FORMAT(ruph_createdOn, '%Y-%m-%d') = '{$datas[$i]['ruph_createdOn']}'");
                $datas[$i]['itemCount'] = 1 * $itemCount;
                $productAmt = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name ='PRODUCT_PRICE'");
                $datas[$i]['itemPrice'] = $datas[$i]['itemCount'] * $productAmt;
            }
            echo '{"totalCount":' . $resCount . ',"data":' . json_encode($datas) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'usrPrdtsDetailsView':
        $stit_ID = isset($_POST['stit_ID']) ? intval($_POST['stit_ID']) : 0;
        $bucketPath = AWSBUCKETPATH;
        $folder = AWSBUCKETFOLDER;
        $preview = SLTHUMP;
        if ($stit_ID) {

            $data = $db->getFromDB("SELECT * from  finascop_stock_itemmaster WHERE stit_ID = " . $stit_ID, true);
            $image_urlend = $db->getItemFromDB("SELECT image_url FROM finascop_stock_item_images WHERE product_id = {$stit_ID} AND image_type = 1");
            $data['image_urlend'] = $image_urlend;
            if(!empty($image_urlend)){
                $data['image_url'] = "{$bucketPath}/{$folder}{$preview}{$image_urlend}";
            }else{
                $data['image_url'] = "-";
            }
            
            $data['stit_orgin_countryname'] = $db->getItemFromDB("SELECT country_name FROM finascop_country WHERE country_id = {$data['stit_orgin_country']}");
            $productCountry = $db->getItemFromDB("SELECT GROUP_CONCAT(stitc_country) FROM finascop_stock_itemmaster_country WHERE stitc_stit_id  = {$stit_ID} ");
            if(!empty($productCountry)){
                $data['productCountry'] = $db->getItemFromDB("SELECT GROUP_CONCAT(country_name) FROM finascop_country WHERE country_id IN ({$productCountry})");
            }else{
                $data['productCountry'] = '-';
            }
            
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
}

