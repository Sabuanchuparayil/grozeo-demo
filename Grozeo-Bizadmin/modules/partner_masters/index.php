<?php

$userid = $_SESSION['admin']->Finascop_UserId;
require_once(INCLUDE_PATH . "/finascop_wallet_client.php");
require_once(INCLUDE_PATH . "/brmClass.php");

switch ($op) {

    case 'getStoreCatRetailCat':
        $id = intval($_POST['business_category_id']);
        $edit_status = $_POST['edit_status'];
        $filter = $_POST['filter'];
        $search = " WHERE 1=1 ";
        $items = array();
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');
                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                }
            }
        }
        if ($edit_status == 1)
            $qry = "SELECT business_type_id,business_type_name,(select count(rbc_business_type) from retaline_business_category where FIND_IN_SET(business_type_id,rbc_business_type) AND business_category_id=$id)as checked FROM finascop_business_type where status = 1 {$searchitem} ";
        //$qry = "SELECT business_type_id,business_type_name,(select fam_id from sl_album_master where business_type_id in (salm_events))as checked FROM finascop_crm_event where crme_projectid = $project_id {$searchitem}";
        else
            $qry = "SELECT business_type_id,business_type_name FROM finascop_business_type where status = 1 {$searchitem}";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo json_encode([]);
        break;
    case 'listStoreCategorys':


        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'business_category_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1  AND store_group_id > 0 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Active') {
                                $fiterItem = 1;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Inactive') {
                                $fiterItem = 0;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (status = 1 or status=0) ";
                            }
                        }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM retaline_business_category  {$search}";
        $listQuery = "SELECT * FROM (SELECT store_group_id,business_category_id,business_category_name,IF((status=1),'Active','Inactive') AS status,
        IF((business_category_ingroup=1),'Yes','No')AS business_category_ingroup,rbc_business_type,
        IF(store_group_id > 0,(SELECT store_group_name FROM finascop_branch_group WHERE finascop_branch_group.store_group_id = retaline_business_category.store_group_id),'Default') as storeGroup FROM retaline_business_category ) AS listBC 
        " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'storecategorys_form_load':

        $business_category_id = isset($_POST['business_category_id']) ? intval($_POST['business_category_id']) : 0;
        if ($business_category_id) {
            $sql = "SELECT  business_category_id,business_category_name,status AS comboMasterStoreCategorysStatus,business_category_ingroup,rbc_business_type FROM retaline_business_category WHERE business_category_id =" . $business_category_id;
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
    case 'storecategorysdetailsView':

        $business_category_id = isset($_POST['business_category_id']) ? intval($_POST['business_category_id']) : 0;
        if ($business_category_id) {

            $data = $db->getFromDB("SELECT business_category_id,business_category_name,status AS status,IF((business_category_ingroup=1),'Yes','No')AS business_category_ingroup FROM retaline_business_category  WHERE business_category_id =" . $business_category_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'saveStoreCategorys':

        $db->query('begin');
        if ($_POST['business_category_ingroup'] == 'true') {
            $business_category_ingroup = 1;
        } else {
            $business_category_ingroup = 0;
        }
        $data = array(
            "business_category_id" => $_POST['id'],
            "business_category_name" => $_POST['name'],
            "status" => $_POST['status'],
            "rbc_business_type" => $_POST['rbc_retailCategory'],
            'business_category_ingroup' => $business_category_ingroup
        );
        $business_category_id = $data['business_category_id'];
        $business_category_name = $data['business_category_name'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $business_category_name = addslashes($business_category_name);




        if ($data['business_category_id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;


            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from retaline_business_category WHERE business_category_name ='{$business_category_name}' AND business_category_id!='{$business_category_id}' "); //AND store_group_id = 0
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Business category name already exists.'}";
                exit;
            } else {
                $status = $db->perform("retaline_business_category", $data, 'update', 'business_category_id =' . $data['business_category_id']);
                $lastId = $data['business_category_id'];
            }
        } else {

            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from retaline_business_category WHERE business_category_name ='{$business_category_name}'   "); //AND store_group_id = 0
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Business category already exists.'}";
                exit;
            } else {
                unset($data['business_category_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('retaline_business_category', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT business_category_id,business_category_name,status FROM retaline_business_category WHERE business_category_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
    case 'listStoreCatRetailCat':
        $id = intval($_POST['business_category_id']);
        $edit_status = $_POST['edit_status'];
        $filter = $_POST['filter'];
        $search = " WHERE 1=1 ";
        $items = array();
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');
                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                }
            }
        }
        $rbc_business_type = $db->getItemFromDB("SELECT rbc_business_type FROM retaline_business_category WHERE business_category_id = {$id}");
        $qry = "SELECT business_type_id,business_type_name FROM finascop_business_type where status = 1 AND 
                business_type_id IN ({$rbc_business_type}) {$searchitem}";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo json_encode([]);
        break;
    case 'listPrivateCategory':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 21;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'vc_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 AND rvc.store_group_id > 0 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {

                if ($field['field'] == 'category_name') {
                    $searchitem .= " and (category_name LIKE '{$field['data']['value']}%') ";
                } else if ($field['field'] == 'parent_category') {
                    $searchitem .= " and (mypha_productparent_category.parent_category LIKE '{$field['data']['value']}%') ";
                } else if ($field['field'] == 'hasImage') {
                    if ($field['data']['value'] == 'Yes') {
                        $searchitem .= " and (rvc.image_url <> '') ";
                    } else if ($field['data']['value'] == 'Yes,No') {
                        $searchitem .= "  ";
                    } else {
                        $searchitem .= " and (rvc.image_url = '') ";
                    }
                } else {
                    $checkComa = strstr($field['data']['value'], ',');

                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                    }
                }
            }
        }


        $countQuery = "SELECT COUNT(*) FROM retaline_virtual_category rvc INNER JOIN finascop_branch_group bg ON bg.store_group_id = rvc.store_group_id   {$search}";
        $listQuery = "SELECT store_group_name,vc_id,vc_name,vc_parentCategoryId,vc_categoryId,
        mypha_productparent_category.parent_category as parent_category,category_name,IF((vc_status=0),'Inactive','Active') AS vc_status,
        if(rvc.image_url = '','No','Yes') AS hasImage FROM retaline_virtual_category rvc 
        INNER JOIN finascop_branch_group bg ON bg.store_group_id = rvc.store_group_id 
        LEFT JOIN mypha_productparent_category ON parent_category_id = vc_parentCategoryId 
        LEFT JOIN mypha_productcategory ON vc_categoryId = category_id {$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'savePrivateCategory':
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

            $vcUnique = $db->getItemFromDB("SELECT COUNT(*) from retaline_virtual_category WHERE vc_name ='{$vc_name}' AND vc_id <> {$vc_id} AND store_group_id = 0  ");
            if ($vcUnique > 0) {
                echo "{success: false, message:'This Virtual Category already exists.'}";
                exit;
            } else {
                $status = $db->perform("retaline_virtual_category", $data, 'update', 'vc_id =' . $data['vc_id']);
                $lastId = $data['vc_id'];
            }
        } else {
            $vcUnique = $db->getItemFromDB("SELECT COUNT(*) from retaline_virtual_category WHERE vc_name ='{$vc_name}'  AND store_group_id = 0  ");
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
    case 'pvtCatDetailsView':
        $vc_id = isset($_POST['vc_id']) ? intval($_POST['vc_id']) : 0;
        if ($vc_id) {

            $data = $db->getFromDB("SELECT vc_id,vc_name,vc_parentCategoryId,vc_categoryId,mypha_productparent_category.parent_category as parent_category,category_name,IF((vc_status=0),'Inactive','Active') AS vc_status,"
                . "retaline_virtual_category.image_url as image_url,IF((vc_isHome=1),'Yes','No') AS vc_isHome,IF((vc_isInCategory=1),'Yes','No') AS vc_isInCategory FROM retaline_virtual_category "
                . "LEFT JOIN mypha_productparent_category ON parent_category_id = vc_parentCategoryId LEFT JOIN mypha_productcategory ON vc_categoryId = category_id WHERE vc_id = " . $vc_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'listitempvtcat':
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'stpi_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {

                if ($field['field'] == 'category_name') {
                    $searchitem .= " and (category_name LIKE '{$field['data']['value']}%') ";
                } else if ($field['field'] == 'parent_category') {
                    $searchitem .= " and (mypha_productparent_category.parent_category LIKE '{$field['data']['value']}%') ";
                } else {
                    $checkComa = strstr($field['data']['value'], ',');

                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                    }
                }
            }
        }


        $customerId = $_POST['vc_id'];
        if ($customerId != '') {

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
    case 'pvtCatitemlisting':

        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $item_name = $_POST['currentItem'];
        $item_id = $_POST['current_type'];
        $cond = " WHERE 1=1 ";
        //1:brand,2:item,3:Make
        if ($item_name != '') {
            $cond .= " AND stit_SKU LIKE '%{$item_name}%'";
        }
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {

                if ($field['field'] == 'stit_itemName') {
                    $cond .= " and (stit_itemName LIKE '{$field['data']['value']}%') ";
                } else if ($field['field'] == 'stit_brand_name') {
                    $cond .= " and (stit_brand_name LIKE '{$field['data']['value']}%') ";
                } else if ($field['field'] == 'stit_category_name') {
                    $cond .= " and (stit_category_name LIKE '{$field['data']['value']}%') ";
                } else if ($field['field'] == 'stit_SKU') {
                    $cond .= " and (stit_SKU LIKE '{$field['data']['value']}%') ";
                } else {
                    $checkComa = strstr($field['data']['value'], ',');

                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $cond .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                    }
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

                $qry = "SELECT stit_itemName,stit_ID,stit_brand_name,stit_SKU,stit_quantity,least_package_type_name,stit_category_name,"
                    . "(SELECT category_name FROM mypha_productcategory mc WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category)) as mainCategory,"
                    . "(SELECT parent_category FROM mypha_productparent_category DEP WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory mc WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category))) as department  FROM finascop_stock_itemmaster {$cond} AND isMedicine=0 AND stit_status = 1  ORDER BY stit_SKU ASC";
                $data = $db->getMultipleData($qry, true);
                break;
            case 3:

                $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} ";
                $count = $db->getItemFromDB($countQuery);

                $qry = "SELECT stit_itemName,stit_ID,stit_brand_name,stit_SKU,stit_quantity,least_package_type_name,stit_category_name,"
                    . "(SELECT category_name FROM mypha_productcategory mc WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category)) as mainCategory,"
                    . "(SELECT parent_category FROM mypha_productparent_category DEP WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory mc WHERE category_id = (SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = product_category))) as department  FROM finascop_stock_itemmaster {$cond} ";
                $data = $db->getMultipleData($qry, true);
                break;
        }
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'saveitempvtC':
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
    case 'get_catimg_s3_details':

        $uploadtype = $_POST['uploadtype'];
        $rid = $_POST['rid'];
        if ($uploadtype == 'virtualCategory') {
            $data['file_name'] = ($rid . "_1"); /* add extension in js */
            $data['albumBucketName'] = AWSBUCKETNAME;
            $data['accessKey'] = AWSS3ASSETUPLOADACCESSID;
            $data['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
            $data['bucketRegion'] = AWSS3ASSETUPLOADREGION;
            $data['oncompleteurl'] = AWSCATBUCKETFOLDER;
            $data['img_path_db'] = $db->getItemFromDB("select image_url from retaline_virtual_category where `vc_id`= {$rid}");
        }


        echo "{success : true,'data':" . json_encode($data) . "}";
        break;
    case 'savepvtCategoryImage':
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
    case 'getpvtCImage':
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
    case 'getpvtCDetails':
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
    case 'deletepvtCItem':
        $id = $_POST['id'];
        $del_query = "DELETE FROM retaline_vc_items WHERE stpi_id =" . $id;
        $db->query('begin');
        $db->query($del_query);
        $status = $db->query('commit');
        if ($status) {

            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listConfiguredMobiles':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
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

        $condition = " ";
        $qry = "select count(*) from  test_mobile {$filterCon} ";

        $totalCount = $db->getItemFromDB($qry);

        $db->query('set @cnt=0');
        $query = "select * from  test_mobile  $filterCon $condition order by mobile limit $rec_start,$rec_limit";
        $data = $db->getMultipleData($query, true);

        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;
    case 'saveConfiguredMobiles':
        $data['mobile'] = $_POST['confmobile'];
        $data['otp'] = $_POST['confotp'];
        $db->query('begin');
        $isExists = $db->getItemFromDB("SELECT COUNT(*) FROM test_mobile WHERE mobile = '{$data['mobile']}'");
        if ($isExists > 0) {
            $status = $db->perform('test_mobile', $data, 'update', " mobile = {$data['mobile']}");
        } else {
            $status = $db->perform('test_mobile', $data);
        }
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,message:'Saved Successfully'}";
        } else {
            echo "{success: false, message: 'Error occured while saving data' ";
        }
        break;
    case 'listLanguages':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
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

        $condition = " ";
        $qry = "select count(*) from  language {$filterCon} ";

        $totalCount = $db->getItemFromDB($qry);

        $db->query('set @cnt=0');
        $query = "select id,name AS languageName,code AS languageCode  from  language  $filterCon $condition order by name limit $rec_start,$rec_limit";
        $data = $db->getMultipleData($query, true);

        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;
    case 'saveLanguages':
        $langId = $_POST['langId'];
        $data['name'] = $_POST['languageName'];
        $data['code'] = $_POST['languageCode'];
        $db->query('begin');

        if ($langId > 0) {
            $isExists = $db->getItemFromDB("SELECT COUNT(*) FROM language WHERE name = '{$data['languageName']}' AND id <> {$langId}");
            if ($isExists > 0) {
                echo "{success: false, message:'Language already exists.'}";
                exit;
            } else {
                $data['updatedOn'] = date('Y-m-d H:i:s');
                $data['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('language', $data, 'update', " id = {$langId}");
            }
        } else {
            $isExists = $db->getItemFromDB("SELECT COUNT(*) FROM language WHERE name = '{$data['languageName']}'");
            if ($isExists > 0) {
                echo "{success: false, message:'Language already exists.'}";
                exit;
            } else {
                $data['createdOn'] = date('Y-m-d H:i:s');
                $data['createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('language', $data);
            }
        }
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,message:'Saved Successfully'}";
        } else {
            echo "{success: false, message: 'Error occured while saving data' ";
        }
        break;
    case 'getLanguage':
        if ($_SESSION['admin']->IsSuperUser != 'Yes') {
            $cond = " WHERE id IN (SELECT languageId FROM user_language WHERE UserId = {$userid})";
        } else {
            $cond = " WHERE 1=1 ";
        }

        $qry = "select id ,name from language {$cond} order by name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'listSourceDataLabels':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($data['sort']) ? 'Id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = " ";
        $primaryLanguage = $db->getItemFromDB("SELECT id FROM language WHERE code = 'en'");

        $data = $_POST;

        if (isset($data['filter'])) {
        $allowedFields = ['partner_id', 'partner_name', 'partner_phone', 'partner_email', 'partner_type', 'partner_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        if ($_POST['languageId'] > 0) {
            $query = "WITH ls AS(
SELECT Id, ResourceName, ResourceValue, 'Label' AS sourceType,'Static Label' AS dataType,createdOn FROM LocaleStringResource AS lsr1 WHERE LanguageId = {$primaryLanguage} 
AND NOT EXISTS (SELECT 1 FROM LocaleStringResource AS lsr2 WHERE lsr2.ResourceName = lsr1.ResourceName AND lsr2.LanguageId = {$_POST['languageId']}) ORDER BY Id 
), 
dept AS(
SELECT parent_category_id AS Id,'parent_category' AS ResourceName,parent_category AS ResourceValue,'Department' AS sourceType,'Static Label' AS dataType,created_on AS createdOn FROM mypha_productparent_category AS mpdept
WHERE  (SELECT COUNT(*) FROM ls) <= 0 AND NOT EXISTS (SELECT 1 FROM LocalizedProperty AS lp WHERE lp.LocaleKeyGroup = 'Department' AND lp.LocaleKey = mpdept.parent_category AND lp.LanguageId = {$_POST['languageId']} AND lp.EntityId = mpdept.parent_category_id)
ORDER BY parent_category_id 
),
catg AS(
SELECT category_id AS Id,'category_name' AS ResourceName,category_name AS ResourceValue,'Category' AS sourceType,'Static Label' AS dataType,created_on AS createdOn FROM mypha_productcategory AS mpcatg
WHERE  (SELECT COUNT(*) FROM dept) <= 0 AND NOT EXISTS (SELECT 1 FROM LocalizedProperty AS lp WHERE lp.LocaleKeyGroup = 'Category' AND lp.LocaleKey = mpcatg.category_name AND lp.LanguageId = {$_POST['languageId']} AND lp.EntityId = mpcatg.category_id)
ORDER BY category_id 
),
sucatg AS(
SELECT sub_category_id AS Id,'sub_category' AS ResourceName,sub_category AS ResourceValue,'Sub-Category' AS sourceType,'Static Label' AS dataType,created_on AS createdOn FROM mypha_productsubcategory AS sucatg
WHERE  (SELECT COUNT(*) FROM catg) <= 0 AND NOT EXISTS (SELECT 1 FROM LocalizedProperty AS lp WHERE lp.LocaleKeyGroup = 'Category' AND lp.LocaleKey = sucatg.sub_category AND lp.LanguageId = {$_POST['languageId']} AND lp.EntityId = sucatg.sub_category_id)
ORDER BY sub_category_id 
),
b AS(
SELECT brand_id AS Id,'brand_name' AS ResourceName,brand_name AS ResourceValue,'Brand' AS sourceType,'Static Label' AS dataType,created_on AS createdOn FROM mypha_productbrands AS mpb
WHERE  (SELECT COUNT(*) FROM sucatg) <= 0 AND NOT EXISTS (SELECT 1 FROM LocalizedProperty AS lp WHERE lp.LocaleKeyGroup = 'Brand' AND lp.LocaleKey = mpb.brand_name AND lp.LanguageId = {$_POST['languageId']} AND lp.EntityId = mpb.brand_id)
ORDER BY brand_id 
),
p_sku AS(
SELECT   stit_id AS Id, 'stit_SKU' AS ResourceName,stit_SKU AS ResourceValue,'Product' AS sourceType,'Static Label' AS dataType, createdOn FROM finascop_stock_itemmaster AS i
WHERE (SELECT COUNT(*) FROM b) <= 0 AND  NOT EXISTS (SELECT 1 FROM LocalizedProperty AS lp WHERE lp.LocaleKeyGroup = 'Product' AND lp.LocaleKey = 'stit_SKU' AND lp.LanguageId = {$_POST['languageId']} AND lp.EntityId = i.stit_id)
ORDER BY stit_id 
),
p_shortDesc AS(
SELECT stit_id AS Id,'stit_Description' AS ResourceName,stit_Description AS ResourceValue,'Product' AS sourceType,'Plain Text' AS dataType, createdOn FROM finascop_stock_itemmaster AS i
WHERE stit_Description <> '' AND (SELECT COUNT(*) FROM p_sku) <= 0 AND NOT EXISTS (SELECT 1 FROM LocalizedProperty AS lp WHERE lp.LocaleKeyGroup = 'Product' AND lp.LocaleKey = 'stit_Description' AND lp.LanguageId = {$_POST['languageId']} AND lp.EntityId = i.stit_id)
),
p_longDesc AS(
SELECT stit_id AS Id,'stit_long_description' AS ResourceName,stit_long_description AS ResourceValue,'Product' AS sourceType,'Rich Text' AS dataType, createdOn FROM finascop_stock_itemmaster AS i
WHERE stit_long_description <> '' AND (SELECT COUNT(*) FROM p_shortDesc) <= 0 AND NOT EXISTS (SELECT 1 FROM LocalizedProperty AS lp WHERE lp.LocaleKeyGroup = 'Product' AND lp.LocaleKey = 'stit_long_description' AND lp.LanguageId = {$_POST['languageId']} AND lp.EntityId = i.stit_id)
)

SELECT * FROM(
SELECT * FROM ls
UNION
SELECT * FROM dept
UNION
SELECT * FROM catg
UNION
SELECT * FROM sucatg
UNION
SELECT * FROM b
UNION
SELECT * FROM p_sku
UNION
SELECT * FROM p_shortDesc
UNION
SELECT * FROM p_longDesc
)tmp LIMIT 23";
            /* $countQuery = "SELECT COUNT(Id) from LocaleStringResource lsr1  {$filter_part} 
    AND NOT EXISTS (SELECT 1 FROM LocaleStringResource AS lsr2 WHERE lsr2.ResourceName = lsr1.ResourceName AND lsr2.LanguageId = {$_POST['languageId']}) ORDER BY $rec_sort $rec_sort_dir ";
            $listQuery = "SELECT Id,ResourceName,ResourceValue,LanguageId,'Static Label' as dataType,createdOn FROM LocaleStringResource lsr1  {$filter_part} 
    AND NOT EXISTS (SELECT 1 FROM LocaleStringResource AS lsr2 WHERE lsr2.ResourceName = lsr1.ResourceName AND lsr2.LanguageId = {$_POST['languageId']}) ORDER BY $rec_sort $rec_sort_dir  LIMIT $rec_start,$rec_limit ";*/
            $countQuery = "SELECT 22 AS Value";
            $listQuery = $query;
        }

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'listMappedLanguageConiguration':
        $rec_sort = empty($data['sort']) ? 'l1.Id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = " WHERE 1=1 and l1.LanguageId = (SELECT id FROM language WHERE code = 'en')";

        $data = $_POST;

        if (isset($data['filter'])) {
        $allowedFields = ['partner_id', 'partner_name', 'partner_phone', 'partner_email', 'partner_type', 'partner_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        if ($_POST['languageId'] > 0) {
            $cond = " AND l2.LanguageId = {$_POST['languageId']}";
        } else {
            $cond = " ";
        }
        $countQuery = "SELECT COUNT(l1.Id) FROM LocaleStringResource l1
LEFT JOIN LocaleStringResource l2 ON l1.ResourceName = l2.ResourceName  {$cond}  {$filter_part} GROUP BY l1.ResourceName ORDER BY $rec_sort $rec_sort_dir ";
        $listQuery = "SELECT l1.Id AS Id,l2.Id AS MappedId, l1.ResourceName AS ResourceName,l1.ResourceValue AS ResourceValue,l2.ResourceValue AS MappedResourceValue, 1 AS LanguageId  FROM LocaleStringResource l1
LEFT JOIN LocaleStringResource l2 ON l1.ResourceName = l2.ResourceName  {$cond} {$filter_part} GROUP BY l1.ResourceName ORDER BY $rec_sort $rec_sort_dir  ";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'updateLanguageValue':

        $data = json_decode($_POST['data']);
        $db->query('begin');

        $mapValue['ResourceName'] = $data->ResourceName;
        $mapValue['LanguageId'] = $data->LanguageId;
        $mapValue['ResourceValue'] = $data->MappedResourceValue;
        $MappedId = $data->MappedId;
        if ($MappedId > 0) {
            $status = $db->perform('LocaleStringResource', $mapValue, 'update', " Id = {$MappedId}");
        } else {
            $status = $db->perform('LocaleStringResource', $mapValue);
        }
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' ";
        }
        break;
    case 'chooseTranslationJob':
        $today = date("Y-m-d");
        $currentTime = date("Y-m-d H:i");
        $primaryLanguage = $db->getItemFromDB("SELECT id FROM language WHERE code = 'en'");
        $jobQuery = "WITH ls AS(
SELECT Id, ResourceName, ResourceValue, 'Label' AS sourceType,'Static Label' AS dataType,createdOn FROM LocaleStringResource AS lsr1 WHERE LanguageId = {$primaryLanguage} 
AND NOT EXISTS (SELECT 1 FROM LocaleStringResource AS lsr2 WHERE lsr2.ResourceName = lsr1.ResourceName AND lsr2.LanguageId = {$_POST['LanguageId']}) ORDER BY Id 
), 
dept AS(
SELECT parent_category_id AS Id,'parent_category' AS ResourceName,parent_category AS ResourceValue,'Department' AS sourceType,'Static Label' AS dataType,created_on AS createdOn FROM mypha_productparent_category AS mpdept
WHERE  (SELECT COUNT(*) FROM ls) <= 0 AND NOT EXISTS (SELECT 1 FROM LocalizedProperty AS lp WHERE lp.LocaleKeyGroup = 'Department' AND lp.LocaleKey = mpdept.parent_category AND lp.LanguageId = {$_POST['LanguageId']} AND lp.EntityId = mpdept.parent_category_id)
ORDER BY parent_category_id 
),
catg AS(
SELECT category_id AS Id,'category_name' AS ResourceName,category_name AS ResourceValue,'Category' AS sourceType,'Static Label' AS dataType,created_on AS createdOn FROM mypha_productcategory AS mpcatg
WHERE  (SELECT COUNT(*) FROM dept) <= 0 AND NOT EXISTS (SELECT 1 FROM LocalizedProperty AS lp WHERE lp.LocaleKeyGroup = 'Category' AND lp.LocaleKey = mpcatg.category_name AND lp.LanguageId = {$_POST['LanguageId']} AND lp.EntityId = mpcatg.category_id)
ORDER BY category_id 
),
sucatg AS(
SELECT sub_category_id AS Id,'sub_category' AS ResourceName,sub_category AS ResourceValue,'Sub-Category' AS sourceType,'Static Label' AS dataType,created_on AS createdOn FROM mypha_productsubcategory AS sucatg
WHERE  (SELECT COUNT(*) FROM catg) <= 0 AND NOT EXISTS (SELECT 1 FROM LocalizedProperty AS lp WHERE lp.LocaleKeyGroup = 'Category' AND lp.LocaleKey = sucatg.sub_category AND lp.LanguageId = {$_POST['LanguageId']} AND lp.EntityId = sucatg.sub_category_id)
ORDER BY sub_category_id 
),
b AS(
SELECT brand_id AS Id,'brand_name' AS ResourceName,brand_name AS ResourceValue,'Brand' AS sourceType,'Static Label' AS dataType,created_on AS createdOn FROM mypha_productbrands AS mpb
WHERE  (SELECT COUNT(*) FROM sucatg) <= 0 AND NOT EXISTS (SELECT 1 FROM LocalizedProperty AS lp WHERE lp.LocaleKeyGroup = 'Brand' AND lp.LocaleKey = mpb.brand_name AND lp.LanguageId = {$_POST['LanguageId']} AND lp.EntityId = mpb.brand_id)
ORDER BY brand_id 
),
p_sku AS(
SELECT   stit_id AS Id, 'stit_SKU' AS ResourceName,stit_SKU AS ResourceValue,'Product' AS sourceType,'Static Label' AS dataType, createdOn FROM finascop_stock_itemmaster AS i
WHERE (SELECT COUNT(*) FROM b) <= 0 AND  NOT EXISTS (SELECT 1 FROM LocalizedProperty AS lp WHERE lp.LocaleKeyGroup = 'Product' AND lp.LocaleKey = 'stit_SKU' AND lp.LanguageId = {$_POST['LanguageId']} AND lp.EntityId = i.stit_id)
ORDER BY stit_id 
),
p_shortDesc AS(
SELECT stit_id AS Id,'stit_Description' AS ResourceName,stit_Description AS ResourceValue,'Product' AS sourceType,'Plain Text' AS dataType, createdOn FROM finascop_stock_itemmaster AS i
WHERE stit_Description <> '' AND (SELECT COUNT(*) FROM p_sku) <= 0 AND NOT EXISTS (SELECT 1 FROM LocalizedProperty AS lp WHERE lp.LocaleKeyGroup = 'Product' AND lp.LocaleKey = 'stit_Description' AND lp.LanguageId = {$_POST['LanguageId']} AND lp.EntityId = i.stit_id)
),
p_longDesc AS(
SELECT stit_id AS Id,'stit_long_description' AS ResourceName,stit_long_description AS ResourceValue,'Product' AS sourceType,'Rich Text' AS dataType, createdOn FROM finascop_stock_itemmaster AS i
WHERE stit_long_description <> '' AND (SELECT COUNT(*) FROM p_shortDesc) <= 0 AND NOT EXISTS (SELECT 1 FROM LocalizedProperty AS lp WHERE lp.LocaleKeyGroup = 'Product' AND lp.LocaleKey = 'stit_long_description' AND lp.LanguageId = {$_POST['LanguageId']} AND lp.EntityId = i.stit_id)
)

SELECT * FROM(
SELECT * FROM ls
UNION
SELECT * FROM dept
UNION
SELECT * FROM catg
UNION
SELECT * FROM sucatg
UNION
SELECT * FROM b
UNION
SELECT * FROM p_sku
UNION
SELECT * FROM p_shortDesc
UNION
SELECT * FROM p_longDesc
)tmp LIMIT 1";
        $sourceData = $db->getFromDB($jobQuery, true);
        /*$sourceDatas = $db->getMultipleData("SELECT * FROM LocaleStringResource WHERE LanguageId = (SELECT id FROM language WHERE code = 'en') ORDER BY Id ASC", true);
        foreach ($sourceDatas as $sourceData) {
            $isExists = $db->getItemSafe("SELECT COUNT(*) FROM LocaleStringResource WHERE LanguageId = ? AND ResourceName = '{$sourceData['ResourceName']}'", "i", [$_POST['LanguageId']]);
            if ($isExists == 0) {
                $data['ResourceName'] = $sourceData['ResourceName'];
                $data['ResourceValue'] = $sourceData['ResourceValue'];
                $data['ResourceId'] = $sourceData['Id'];
                $data['success'] = true;
                $data['msg'] = "Assigned Job.";
                echo json_encode($data);
                exit();
            }
        }*/
        if ($sourceData['Id'] > 0) {
            $data['ResourceName'] = $sourceData['ResourceName'];
            $data['ResourceValue'] = $sourceData['ResourceValue'];
            $data['ResourceId'] = $sourceData['Id'];
            $data['sourceType'] = $sourceData['sourceType'];
            $data['dataType'] = $sourceData['dataType'];
            $data['success'] = true;
            $data['msg'] = "Assigned Job.";
            echo json_encode($data);
            exit();
        }
        break;
    case 'updateTranslation':
        $data = $_POST;
        $db->query('begin');
        $sourceType = $data['sourceType'];
        $dataType = $data['dataType'];
        if ($sourceType == 'Label') {
            $mapValue['ResourceName'] = $data['ResourceName'];
            $mapValue['LanguageId'] = $data['LanguageId'];
            $mapValue['ResourceValue'] = $data['trnslatedData'];
            $MappedId = $db->getItemFromDB("SELECT Id FROM LocaleStringResource WHERE LanguageId = {$data['LanguageId']} AND ResourceName = '{$data['ResourceName']}'");
            if ($MappedId > 0) {
                $status = $db->perform('LocaleStringResource', $mapValue, 'update', " Id = {$MappedId}");
            } else {
                $status = $db->perform('LocaleStringResource', $mapValue);
            }
        } else {
            $mapValue['LocaleKeyGroup'] = $sourceType;
            $mapValue['LocaleKey'] = $data['ResourceName'];
            $mapValue['LocaleValue'] = $data['trnslatedData'];
            $mapValue['LanguageId'] = $data['LanguageId'];
            $mapValue['EntityId'] = $data['sourceId'];
            $MappedId = $db->getItemFromDB("SELECT Id FROM LocalizedProperty WHERE LanguageId = {$data['LanguageId']} AND LocaleKey = '{$data['ResourceName']}' AND EntityId = {$data['sourceId']} ");
            if ($MappedId > 0) {
                $status = $db->perform('LocalizedProperty', $mapValue, 'update', " LanguageId = {$data['LanguageId']} AND LocaleKey = '{$data['ResourceName']}' AND EntityId = {$data['sourceId']} ");
            } else {
                $status = $db->perform('LocalizedProperty', $mapValue);
            }
        }

        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' ";
        }
        break;
    case 'generateTranslatedData':
        $languageId = $_POST['LanguageId'];
        $sourceData = $_POST['sourceData'];
        $targetln = $db->getItemFromDB("SELECT code FROM language WHERE id = {$languageId}");
        $fields['q'] = $sourceData;
        $fields['target'] = $targetln;
        $fields['source'] = 'en';

        $aiUrl = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'TRANSLATE_APIURL'");
        // Parse the URL to get the query part
        $parsedUrl = parse_url($aiUrl, PHP_URL_QUERY);
        // Parse the query string into an associative array
        parse_str($parsedUrl, $queryParams);
        // Get the value of the 'key' parameter
        $translateKey = $queryParams['key'];
        $headers = [
            "key:" . $translateKey,
            'Content-Type:application/json'
        ];


        $fields_string = json_encode($fields);
        //print_r($invoiceUrl . "/n");
        //print_r($fields_string . "/n");
        $opts = array(
            CURLOPT_URL => $aiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => count($fields),
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => $headers
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $response = json_decode($data);
        //print_r($response);
        echo '{"success":true,"data":' . $data . '}';
        break;
    case 'listAvailableLanguage':
        $rec_limit = empty($_POST['limit']) ? 500 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = " WHERE 1=1 and (user_language.UserId IS NULL OR user_language.UserId <> {$_POST['userId']})";

        $data = $_POST;

        if (isset($data['filter'])) {
        $allowedFields = ['partner_id', 'partner_name', 'partner_phone', 'partner_email', 'partner_type', 'partner_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(id) from language left join user_language on languageId = id  {$filter_part} ORDER BY $rec_sort $rec_sort_dir ";
        $listQuery = "SELECT id,name FROM language left join user_language on languageId = id  {$filter_part}  group by id ORDER BY $rec_sort $rec_sort_dir  LIMIT $rec_start,$rec_limit ";
        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'listUserMappedLanguage':

        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = " WHERE 1=1 and user_language.UserId = {$_POST['userId']}";

        $data = $_POST;

        if (isset($data['filter'])) {
        $allowedFields = ['partner_id', 'partner_name', 'partner_phone', 'partner_email', 'partner_type', 'partner_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(id) from language left join user_language on languageId = id  {$filter_part}";
        $listQuery = "SELECT id,name FROM language left join user_language on languageId = id  {$filter_part}  ORDER BY $rec_sort $rec_sort_dir ";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'mapLanguageToUser':
        $brandarr = $_POST['brandarr'];
        $userId = $_POST['userId'];
        $itemdecode = json_decode($brandarr);
        $itemcount = count($itemdecode);
        $db->query('begin');
        for ($i = 0; $i < $itemcount; $i++) {
            $entryCount = $db->getItemFromDB("SELECT COUNT(*) FROM user_language WHERE languageId = {$itemdecode[$i]} AND user_language.UserId = {$userId}");
            if ($entryCount == 0) {
                $brndMapData["languageId"] = $itemdecode[$i];
                $brndMapData["UserId"] = $userId;
                $status = $db->perform('user_language', $brndMapData);
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'saveSourceData':

        $data['ResourceName'] = $_POST['ResourceName'];
        $data['ResourceValue'] = $_POST['ResourceValue'];
        $dataExists = $db->getItemFromDB("SELECT id FROM LocaleStringResource 
        WHERE ResourceName = '{$data['ResourceName']}' AND ResourceValue = '{$data['ResourceValue']}'");
        $db->query('begin');

        if ($dataExists > 0) {
            $status = $db->perform('language', $data, 'update', " id = {$dataExists}");
        } else {
            $data['createdOn'] = date('Y-m-d H:i:s');
            $status = $db->perform('language', $data);
        }
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,message:'Saved Successfully'}";
        } else {
            echo "{success: false, message: 'Error occured while saving data' ";
        }
        break;
    case 'listTranslatedData':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($data['sort']) ? 'Id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = " ";
        $primaryLanguage = $db->getItemFromDB("SELECT id FROM language WHERE code = 'en'");

        $data = $_POST;

        if (isset($data['filter'])) {
        $allowedFields = ['partner_id', 'partner_name', 'partner_phone', 'partner_email', 'partner_type', 'partner_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        if ($_POST['languageId'] > 0) {
            $query = "WITH ls AS(
SELECT Id, ResourceName, ResourceValue, 'Label' AS sourceType,'Static Label' AS dataType,createdOn,ResourceValue AS mappedValue  FROM LocaleStringResource AS lsr1 WHERE LanguageId = " . intval($_POST['languageId']) . " ORDER BY Id 
),dept AS(
SELECT parent_category_id AS Id,'parent_category' AS ResourceName,parent_category AS ResourceValue,'Department' AS sourceType,'Static Label' AS dataType,created_on AS createdOn,lp.LocaleValue AS mappedValue FROM mypha_productparent_category AS mpdept INNER JOIN LocalizedProperty AS lp ON lp.LocaleKey = mpdept.parent_category AND lp.LanguageId = {$_POST['languageId']} AND lp.EntityId = mpdept.parent_category_id
ORDER BY parent_category_id 
),catg AS(
SELECT category_id AS Id,'category_name' AS ResourceName,category_name AS ResourceValue,'Category' AS sourceType,'Static Label' AS dataType,created_on AS createdOn,lp.LocaleValue AS mappedValue FROM mypha_productcategory AS mpcatg INNER JOIN LocalizedProperty AS lp ON lp.LocaleKey = mpcatg.category_name AND lp.LanguageId = {$_POST['languageId']} AND lp.EntityId = mpcatg.category_id ORDER BY category_id 
),
sucatg AS(
SELECT sub_category_id AS Id,'sub_category' AS ResourceName,sub_category AS ResourceValue,'Sub-Category' AS sourceType,'Static Label' AS dataType,created_on AS createdOn,lp.LocaleValue AS mappedValue FROM mypha_productsubcategory AS sucatg INNER JOIN LocalizedProperty AS lp ON lp.LocaleKey = sucatg.sub_category AND lp.LanguageId = {$_POST['languageId']} AND lp.EntityId = sucatg.sub_category_id ORDER BY sub_category_id 
),
b AS(
SELECT brand_id AS Id,'brand_name' AS ResourceName,brand_name AS ResourceValue,'Brand' AS sourceType,'Static Label' AS dataType,created_on AS createdOn,lp.LocaleValue AS mappedValue FROM mypha_productbrands AS mpb INNER JOIN LocalizedProperty AS lp ON lp.LocaleKey = mpb.brand_name AND lp.LanguageId = {$_POST['languageId']} AND lp.EntityId = mpb.brand_id ORDER BY brand_id 
),
p_sku AS(
SELECT   stit_id AS Id, 'stit_SKU' AS ResourceName,stit_SKU AS ResourceValue,'Product' AS sourceType,'Static Label' AS dataType, createdOn,lp.LocaleValue AS mappedValue FROM finascop_stock_itemmaster AS i INNER JOIN LocalizedProperty AS lp ON lp.LocaleKeyGroup = 'Product' AND lp.LocaleKey = 'stit_SKU' AND  lp.LanguageId = {$_POST['languageId']} AND lp.EntityId = i.stit_id ORDER BY stit_id 
),
p_shortDesc AS(
SELECT stit_id AS Id,'stit_Description' AS ResourceName,stit_Description AS ResourceValue,'Product' AS sourceType,'Plain Text' AS dataType, createdOn,lp.LocaleValue AS mappedValue FROM finascop_stock_itemmaster AS i INNER JOIN LocalizedProperty AS lp ON lp.LocaleKeyGroup = 'Product' AND lp.LocaleKey = 'stit_Description' AND lp.LanguageId = {$_POST['languageId']} AND lp.EntityId = i.stit_id WHERE stit_Description <> '' ),
p_longDesc AS(
SELECT stit_id AS Id,'stit_long_description' AS ResourceName,stit_long_description AS ResourceValue,'Product' AS sourceType,'Rich Text' AS dataType, createdOn,lp.LocaleValue AS mappedValue FROM finascop_stock_itemmaster AS i INNER JOIN LocalizedProperty AS lp ON lp.LocaleKeyGroup = 'Product' AND lp.LocaleKey = 'stit_long_description' AND lp.LanguageId = {$_POST['languageId']} AND lp.EntityId = i.stit_id WHERE stit_long_description <> '' 
)

SELECT * FROM(
SELECT * FROM ls
UNION
SELECT * FROM dept
UNION
SELECT * FROM catg
UNION
SELECT * FROM sucatg
UNION
SELECT * FROM b
UNION
SELECT * FROM p_sku
UNION
SELECT * FROM p_shortDesc
UNION
SELECT * FROM p_longDesc
)tmp  ";

            $countQuery = "SELECT COUNT(*) FROM ({$query}) AS dataCount ";
            $listQuery = $query . " LIMIT {$rec_start},{$rec_limit}";
        }

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'deleteMobiles':
        $mobile = $_POST['mobile'];
        $del_query = "DELETE FROM test_mobile WHERE mobile =" . $mobile;
        $db->query('begin');
        $db->query($del_query);
        $status = $db->query('commit');
        if ($status) {

            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
}
