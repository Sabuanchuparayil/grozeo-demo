<?php

$userid = $_SESSION['admin']->Finascop_UserId;
switch ($op) {
    case 'listVirtualCategory':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'vc_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['vc_id', 'vc_name', 'vc_type', 'vc_status', 'vc_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }


        $countQuery = "SELECT COUNT(*) FROM retaline_virtual_category  {$search}";
        $listQuery = "SELECT vc_id,vc_name,vc_parentCategoryId,vc_categoryId,mypha_productparent_category.parent_category as parent_category,category_name,IF((vc_status=0),'Inactive','Active') AS vc_status FROM retaline_virtual_category "
                . "LEFT JOIN mypha_productparent_category ON parent_category_id = vc_parentCategoryId LEFT JOIN mypha_productcategory ON vc_categoryId = category_id "
                . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'saveVirtualCategory':
        $db->query('begin');
        $data = $_POST['n'];
        $vc_id = $data['vc_id'];
        $vc_name = $data['vc_name'];
        $vc_name = addslashes($vc_name);
        if ($data['vc_isInCategory'] == true) {
            $data['vc_isInCategory'] = 1;
        } else {
            $data['vc_isInCategory'] = 0;
            $data['vc_parentCategoryId'] = 0;
            $data['vc_categoryId'] = 0;
        }
        if ($data['vc_isHome'] == true) {
            $data['vc_isHome'] = 1;
        } else {
            $data['vc_isHome'] = 0;
        }
        
        if ($data['vc_id'] > 0) {

            $data['vc_updatedOn'] = date('Y-m-d H:i:s');
            $data['vc_updatedBy'] = $userid;

            $vcUnique = $db->getItemFromDB("SELECT COUNT(*) from retaline_virtual_category WHERE vc_name ='{$vc_name}' AND vc_id <> {$vc_id} ");
            if ($vcUnique > 0) {
                echo "{success: false, message:'This Virtual Category already exists.'}";
                exit;
            } else {
                $status = $db->perform("retaline_virtual_category", $data, 'update', 'vc_id =' . $data['vc_id']);
                $lastId = $data['vc_id'];
            }
        } else {
            $vcUnique = $db->getItemFromDB("SELECT COUNT(*) from retaline_virtual_category WHERE vc_name ='{$vc_name}'  ");
            if ($vcUnique > 0) {
                echo "{success: false, message:'This Virtual Category already exists.'}";
                exit;
            } else {
                unset($data['vc_id']);
                $data['vc_createdOn'] = date('Y-m-d H:i:s');
                $data['vc_createdBy'] = $userid;
                $status = $db->perform('retaline_virtual_category', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT vc_id,vc_name,vc_parentCategoryId,vc_categoryId,mypha_productparent_category.parent_category as parent_category,category_name,IF((vc_status=0),'Inactive','Active') AS vc_status FROM retaline_virtual_category "
                . "LEFT JOIN mypha_productparent_category ON parent_category_id = vc_parentCategoryId LEFT JOIN mypha_productcategory ON vc_categoryId = category_id WHERE vc_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'vcDetailsView':
        $vc_id = isset($_POST['vc_id']) ? intval($_POST['vc_id']) : 0;
        if ($vc_id) {

            $data = $db->getFromDB("SELECT vc_id,vc_name,vc_parentCategoryId,vc_categoryId,mypha_productparent_category.parent_category as parent_category,category_name,IF((vc_status=0),'Inactive','Active') AS vc_status,"
                    . "retaline_virtual_category.image_url as image_url,IF((vc_isHome=1),'Yes','No') AS vc_isHome,IF((vc_isInCategory=1),'Yes','No') AS vc_isInCategory FROM retaline_virtual_category "
                    . "LEFT JOIN mypha_productparent_category ON parent_category_id = vc_parentCategoryId LEFT JOIN mypha_productcategory ON vc_categoryId = category_id WHERE vc_id = " . $vc_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'listitemvc':
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'stpi_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        if (isset($data['filter'])) {
        $allowedFields = ['vc_id', 'vc_name', 'vc_type', 'vc_status', 'vc_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }


        $customerId = $_POST['vc_id'];
        if (customerId != '') {

            $qry = "SELECT vc_id,stpi_id,stit_type,IF(stit_type = 1,'Medicine','Product') AS itemType,finascop_stock_itemmaster.stit_id AS itemId,stit_SKU AS itemName,stit_brand_name,stit_quantity,least_package_type_name,stit_category_name  
                    FROM retaline_vc_items INNER JOIN finascop_stock_itemmaster ON finascop_stock_itemmaster .stit_id= retaline_vc_items.stit_id WHERE vc_id='{$customerId}' ORDER BY $rec_sort $rec_sort_dir";
            //. "AND  {$filter_part} ORDER BY $rec_sort $rec_sort_dir";
//                  echo $qry;
//                   exit;

            $data = $db->getMultipleData($qry, true);

            $countQuery = "SELECT COUNT(*) FROM retaline_vc_items s  WHERE s.vc_id='{$customerId}'";
            $count = $db->getItemFromDB($countQuery);


            if (!empty($data)) {
                echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
            } else {
                echo '{"totalCount":"0","data":[]}';
            }
        }
        break;
    case 'vcitemlisting':

        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $item_name = $_POST['currentItem'];
        $item_id = $_POST['current_type'];
        $cond = " WHERE 1=1 ";
        //1:brand,2:item,3:Make
        if ($item_name != '') {
            $cond .= " AND stit_SKU LIKE '%{$item_name}%'";
        }
        if (isset($data['filter'])) {
        $allowedFields = ['vc_id', 'vc_name', 'vc_type', 'vc_status', 'vc_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        switch ($item_id) {
            case 1:


                $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} AND isMedicine=1";
                $count = $db->getItemFromDB($countQuery);

                $qry = "SELECT stit_itemName,stit_ID,stit_brand_name,stit_SKU,stit_quantity,least_package_type_name,stit_category_name  FROM finascop_stock_itemmaster {$cond} AND isMedicine=1 AND stit_status = 1 ORDER BY stit_SKU ASC";
                $data = $db->getMultipleData($qry, true);
                break;
            case 2:

                $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} AND isMedicine=0";
                $count = $db->getItemFromDB($countQuery);

                $qry = "SELECT stit_itemName,stit_ID,stit_brand_name,stit_SKU,stit_quantity,least_package_type_name,stit_category_name  FROM finascop_stock_itemmaster {$cond} AND isMedicine=0 AND stit_status = 1  ORDER BY stit_SKU ASC";
                $data = $db->getMultipleData($qry, true);
                break;
            case 3:

                $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} ";
                $count = $db->getItemFromDB($countQuery);

                $qry = "SELECT stit_itemName,stit_ID,stit_brand_name,stit_SKU,stit_quantity,least_package_type_name,stit_category_name  FROM finascop_stock_itemmaster {$cond} ";
                $data = $db->getMultipleData($qry, true);
                break;
        }
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'saveitemVC':
        $itemar = $_POST['itemarr'];
        $vc_id = $_POST['cid'];
        $itemType = $_POST['itemtype'];

        $itemdecode = json_decode($itemar);
        // print_r($itemdecode);
        $itemcount = count($itemdecode);
        //exit;
        for ($i = 0; $i < $itemcount; $i++) {


            $data = array(
                "vc_id" => $vc_id,
                "stit_id" => $itemdecode[$i],
                "stit_type" => $itemType
            );
            $itemdup = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_vc_items WHERE vc_id = {$vc_id} AND stit_id = {$itemdecode[$i]} AND stit_type = {$itemType}");
            if ($itemdup == 0) {
                $status = $db->perform(FINASCOP_DB . 'retaline_vc_items', $data);
            }
        }

        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'statusChange':
        $vc_id = $_POST['vc_id'];
        $status = $_POST['vc_status'];
        if ($status == 'Active') {
            $data['vc_status'] = 0;
        } else {
            $data['vc_status'] = 1;
        }
        $db->query('begin');
        if ($vc_id > 0) {
            $con = ' vc_id =' . intval($vc_id);
            $data['vc_updatedOn'] = date('Y-m-d H:i:s');
            $data['vc_updatedBy'] = $_SESSION['admin']->Finascop_UserId;

            $status = $db->perform(FINASCOP_DB . "retaline_virtual_category", $data, 'update', $con);
            $message = "Status Changed.";
        }

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Status Changed.'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }
        break;
    case 'get_catimg_s3_details' :

        $uploadtype = $_POST['uploadtype'];
        $rid = $_POST['rid'];
        if ($uploadtype == 'virtualCategory') {
            $data ['file_name'] = ($rid . "_1"); /* add extension in js */
            $data['albumBucketName'] = AWSBUCKETNAME;
            $data['accessKey'] = AWSS3ASSETUPLOADACCESSID;
            $data['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
            $data['bucketRegion'] = AWSS3ASSETUPLOADREGION;
            $data['oncompleteurl'] = AWSCATBUCKETFOLDER;
            $data['img_path_db'] = $db->getItemFromDB("select image_url from retaline_virtual_category where `vc_id`= {$rid}");
        }


        echo "{success : true,'data':" . json_encode($data) . "}";
        break;
    case 'saveVCategoryImage':
        $db->query('begin');
        $bucketname = $_POST['bucket'];
        $filename = $_POST['uploaded_file_name'];
        $vc_id = $_POST['vc_id'];
        $file_path = $_POST['filepath'];
        $data = array(
            "image_url" => $file_path,
        );
        $res = $db->perform('retaline_virtual_category', $data, 'update', 'vc_id=' . $vc_id);
        $status = $db->query('commit');

        if ($status) {
            echo "{success: true,msg:'Updated Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'getVCImage' :
        $vc_id = $_POST['vc_id'];
        $qry = "select vc_id,image_url from retaline_virtual_category where vc_id = {$vc_id}";
        $data = $db->getMultipleData($qry, true);
        // $result = $db->getMultipleData($stockregId, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            $data = '';
            echo '{"success":true,"data":' . json_encode($data) . '}';
        }
        break;
    case 'getVCDetails':
        $vc_id = isset($_POST['vc_id']) ? intval($_POST['vc_id']) : 0;
        if ($vc_id > 0) {
            $sql = "SELECT vc_id,vc_name,vc_parentCategoryId,vc_categoryId,mypha_productparent_category.parent_category as parent_category,category_name,IF((vc_status=0),'Inactive','Active') AS vc_status_name,"
                    . "vc_status,retaline_virtual_category.image_url as image_url,vc_isInCategory,vc_isHome FROM retaline_virtual_category "
                    . "LEFT JOIN mypha_productparent_category ON parent_category_id = vc_parentCategoryId LEFT JOIN mypha_productcategory ON vc_categoryId = category_id WHERE vc_id = " . $vc_id;
            $results = $db->getFromDB($sql, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;
    case 'deleteVCItem':
        $id = $_POST['id'];
        $del_query = "DELETE FROM retaline_vc_items WHERE stpi_id =" . $id;
        $db->query($del_query);
        $status = $db->query('commit');
        if ($status) {

            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
}

