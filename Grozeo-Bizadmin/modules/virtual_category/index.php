<?php

$userid = $_SESSION['admin']->Finascop_UserId;
switch ($op) {
    case 'listVirtualCategory':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'vc_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 AND store_group_id = 0 ";
        if (isset($data['filter'])) {
        $allowedFields = ['vc_id', 'vc_name', 'vc_type', 'vc_status', 'vc_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
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


        $countQuery = "SELECT COUNT(*) FROM retaline_virtual_category  {$search}";
        $listQuery = "SELECT vc_id,vc_name,vc_parentCategoryId,vc_categoryId,mypha_productparent_category.parent_category as parent_category,category_name,IF((vc_status=0),'Inactive','Active') AS vc_status,if(rvc.image_url = '','No','Yes') AS hasImage FROM retaline_virtual_category rvc "
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
    case 'getVCImage':
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
    case 'listtolerance':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'rtm_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 AND type = 0 ";
        if (isset($data['filter'])) {
        $allowedFields = ['vc_id', 'vc_name', 'vc_type', 'vc_status', 'vc_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM retaline_tolerance_master  {$search}";
        $listQuery = "SELECT * FROM retaline_tolerance_master {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'toleranceDetailsView':
        $rtm_id = isset($_POST['rtm_id']) ? intval($_POST['rtm_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($rtm_id || $ID) {

            $data = $db->getFromDB("SELECT * FROM retaline_tolerance_master WHERE rtm_id  =" . $rtm_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'saveTolerance':
        $rpgtr['rtm_value'] = $_POST['rtm_value'];
        $rpgtr['rtm_percentage'] = $_POST['rtm_percentage'];
        $count = $db->getItemSafe("SELECT COUNT(*) FROM retaline_tolerance_master WHERE rtm_value = ? and  rtm_percentage = {$rpgtr['rtm_percentage']}", "s", [$_POST['rtm_value']]);
        $db->query('begin');
        if ($count == 0) {
            $rpgtr['rtm_createdOn'] = date('Y-m-d H:i:s');
            $rpgtr['rtm_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('retaline_tolerance_master', $rpgtr);
        } else {
            echo "{success: false,msg:'Tolerance Value already added'}";
            exit();
        }
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Saved Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'setDefault':
        $rtm_id = $_POST['rtm_id'];
        $db->query('begin');
        $db->query("UPDATE retaline_tolerance_master SET rtm_default = 0 WHERE type = 0");
        $data['rtm_default'] = 1;
        $data['rtm_updatedOn'] = date("Y-m-d H:i:s");
        $data['rtm_updatedBy'] = $userid;
        $status = $db->perform('retaline_tolerance_master', $data, 'update', " rtm_id = {$rtm_id}");
        $status = $db->query('commit');
        if ($status) {
            $msg = "Saved as Default";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'listPackageMaster':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'rpckm_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['vc_id', 'vc_name', 'vc_type', 'vc_status', 'vc_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM retaline_package_master LEFT JOIN finascop_branch_group ON finascop_branch_group.store_group_id = retaline_package_master.store_group_id 
        LEFT JOIN finascop_branch ON br_ID = branchId  {$search}";
        $listQuery = "SELECT rpckm_id,rpckm_name,rpckm_type,rpckm_length,rpckm_breadth,rpckm_height,
        rpckm_status,CASE WHEN rpckm_type = 1 THEN 'Quick' WHEN rpckm_type = 2 THEN 'Courier' END AS rpckm_typeName,br_Name,store_group_name 
        FROM retaline_package_master LEFT JOIN finascop_branch_group ON finascop_branch_group.store_group_id = retaline_package_master.store_group_id 
        LEFT JOIN finascop_branch ON br_ID = branchId   {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'savePackageMaster':
        $rpgtr['rpckm_name'] = $_POST['rpckm_name'];
        $rpgtr['rpckm_type'] = $_POST['rpckm_type'];
        $rpgtr['rpckm_length'] = $_POST['rpckm_length'];
        $rpgtr['rpckm_height'] = $_POST['rpckm_height'];
        $rpgtr['rpckm_breadth'] = $_POST['rpckm_breadth'];
        $count = $db->getItemSafe("SELECT COUNT(*) FROM retaline_package_master WHERE rpckm_type = ? and  rpckm_length = '{$rpgtr['rpckm_length']}' and  rpckm_breadth = '{$rpgtr['rpckm_breadth']}' and  rpckm_height = '{$rpgtr['rpckm_height']}'", "s", [$_POST['rpckm_type']]);
        $db->query('begin');
        if ($count == 0) {
            $rpgtr['created_on'] = date('Y-m-d H:i:s');
            $rpgtr['created_by'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('retaline_package_master', $rpgtr);
        } else {
            echo "{success: false,msg:'Package Master already added'}";
            exit();
        }
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Saved Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'getWholesalerDetails':
        $rtlr_code = $_POST['rtlr_code'];
        $results = $db->getFromDB("SELECT br_ID,br_Name,br_Address FROM finascop_branch WHERE branch_shortname = '{$rtlr_code}' AND br_PyramidLevel = 4", true);

        if (!$results) {
            echo '{"success":true,"data":[]}';
        } else {
            echo '{"success":true, "data":',
            json_encode($results),
            '}';
        }
        break;
    case 'saveWholesaler':
        $rtlr_code = $_POST['rtlr_code'];
        $brDeta = $db->getFromDB("SELECT br_ID,br_Name FROM finascop_branch WHERE branch_shortname = '{$rtlr_code}' AND br_PyramidLevel = 4", true);
        $db->query('begin');
        if ($brDeta['br_ID'] > 0) {
            $data['br_isWholesaler'] = 1;
        }
        $status = $db->perform('finascop_branch', $data, 'update', " br_ID = {$brDeta['br_ID']} ");
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Saved Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listwholesaler':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'br_ID' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 AND br_isWholesaler = 1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['vc_id', 'vc_name', 'vc_type', 'vc_status', 'vc_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM finascop_branch  {$search}";
        $listQuery = "SELECT * FROM finascop_branch {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'listSponseredProducts':
        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'stit_SKU' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $search = ' WHERE 1=1 ';
        if (isset($data['filter'])) {
        $allowedFields = ['vc_id', 'vc_name', 'vc_type', 'vc_status', 'vc_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }
        switch ($_POST['type']) {
            case 'Branch':
                $where = " AND br_Name LIKE '{$_POST['branchName']}%' ";
                break;
            case 'Vendor':
                $where = " AND br_Name LIKE '{$_POST['branchName']}%' ";
                break;
            default:
                $where = " ";
                break;
        }
        if ($_POST['prouctsWithLM'] === 'true') {
            $searchitem .= " and issponsered = 2 ";
        } else {
            $searchitem .= " ";
        }
        $query = "SELECT 'Branch' AS TYPE,id AS spId,CONCAT(br_Name,'-',branch_shortname) AS br_Name,stit_SKU,stit_itemName,product_category,stit_category_name,stit_brand_name,med_manufacturename,category_name,mppc.parent_category AS parent_category,
            mrp,selling_price,ROUND((100-((selling_price*100)/mrp)),2) AS margin,sponsered_margin,stit_GST,stit_quantity,item_count,fpod_leastSKUmrp,ROUND((fpod_customerRateHmDel+(fpod_customerRateHmDel*stit_GST/100)),2) AS fpod_customerRateHmDel,
            ROUND((fpod_customerRateCouDel+(fpod_customerRateCouDel*stit_GST/100)),2) AS fpod_customerRateCouDel,ROUND((fpod_customerRatePikup+(fpod_customerRatePikup*stit_GST/100)),2) AS fpod_customerRatePikup,
            IF(fpod_itemleastSKUptr>0,fpod_itemleastSKUptr,fpod_leastSKUb2bRetailsp) AS retailPrice,IF(fpod_itemleastSKUpts > 0,fpod_itemleastSKUpts,fpod_leastSKUb2bCSsp) AS csPrice,IF(issponsered = 1,'Yes','No') AS sponsered, 
            issponsered,CASE WHEN issponsered = 0 THEN 'Products Available' WHEN issponsered = 1 THEN 'Sponsered' WHEN issponsered = 2 THEN 'Margin Lowered' WHEN issponsered = 3 THEN 'Product Deselected' END AS spStatus,
            (sponsered_margin - current_margin) as margin_difference,discount_selling_price
 FROM finascop_stock_itemmaster fsi 
LEFT JOIN finascop_stock_branch_inventory fsb  ON fsb.stit_id=fsi.stit_ID 
INNER JOIN mypha_productsubcategory mpsc ON mpsc.sub_category_id=fsi.product_category
INNER JOIN mypha_productcategory mpc ON mpc.category_id=mpsc.main_category  
INNER JOIN mypha_productparent_category mppc ON mppc.parent_category_id=mpc.parent_category 
INNER JOIN mypha_productbrands mpb ON mpb.brand_id=fsi.pdt_brand 
INNER JOIN mypha_productmanufacture mpm ON mpm.manufacture_id=mpb.manufacture_id 
INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id WHERE fsi.isMedicine = 0 AND br_ID IN (SELECT br_ID FROM finascop_branch WHERE br_isWholesaler = 1 )";
        $countQuery = "SELECT COUNT(*) FROM ({$query}) AS countStock {$search}{$where}{$searchitem}";
        $listQuery = "SELECT * FROM ({$query}) AS listStock {$search}{$where}{$searchitem} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        //echo $listQuery;
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'addItemasSponsered':
        $id = $_POST['id'];
        $issponsered = $_POST['issponsered'];

        if ($issponsered == 1) {
            $sponsered_margin = $db->getItemFromDB("SELECT sponsered_margin FROM finascop_stock_branch_inventory WHERE id = {$id}");
            if ($sponsered_margin > 0) {
                $data['issponsered'] = 3;
            } else {
                $data['issponsered'] = 0;
            }

            //$data['sponsered_margin'] = 0;
        } else {
            $data['issponsered'] = 1;
            $fsbiData = $db->getFromDB("SELECT mrp,selling_price FROM finascop_stock_branch_inventory WHERE id = {$id}", true);
            $sponsered_margin = (100 - (($fsbiData['selling_price'] * 100) / $fsbiData['mrp'])); //100-((retailers' selling price x 100)/MRP))
            $sponsered_margin = round($sponsered_margin, 2);
            $data['sponsered_margin'] = $sponsered_margin;
        }
        $data['updated_on'] = date('Y-m-d H:i:s');
        $status = $db->perform('finascop_stock_branch_inventory', $data, 'update', " id = {$id} ");
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Saved Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'getItemName':
        $mrpbrand_id = $_POST['mrpbrand_id'];
        if ($mrpbrand_id > 0) {
            if ($_POST['query'] != '') {
                $que = addslashes($_POST['query']);
                $searchQuery = " AND stit_SKU LIKE '%{$que}%'";
            } else {
                $searchQuery = '';
            }
            if ($mrpbrand_id > 0) {
                $searchQuery .= " AND pdt_brand = {$mrpbrand_id} ";
            }
            $qry = $db->getMulipleData("SELECT stit_ID,stit_itemName,stit_SKU,cs_package_type_name,least_package_type_name as packageName FROM finascop_stock_itemmaster  where 1=1 {$searchQuery}", true);
        }

        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'brandName':
        getJsonKeyArray("SELECT brand_id ,brand_name from mypha_productbrands where status = 1");
        break;
    case 'itemDetailsView':
        $itemId = isset($_POST['itemId']) ? intval($_POST['itemId']) : 0;
        if ($itemId) {

            $data = $db->getFromDB("select stit_ID ,stit_itemId ,stit_SKU,pdt_brand,product_category,stit_product_variant,stit_itemReturnTime,stit_custInitiate,stit_itemName,stit_brand_name,stit_category_name,"
                . "stit_package_type_namme ,pdt_package_type_id,product_category,pdt_brand,featured,courierDelivery,directDelivery,directPurchase,stit_foodtype,stit_orgin_country,stit_quantity,"
                . "(SELECT country_name FROM finascop_country WHERE country_id = stit_orgin_country) as orgCountryName,isRRPApplicable,stit_HSN_code,stit_brand_name,stit_category_name,"
                . "stit_itemName,least_package_type_name "
                . " from  finascop_stock_itemmaster  where stit_ID = {$itemId}", true);
            $data['categoryId'] = $db->getItemFromDB("SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = {$data['product_category']}");
            $data['categoryName'] = $db->getItemFromDB("SELECT category_name FROM mypha_productcategory WHERE category_id = {$data['product_category']}");
            $data['deptId'] = $db->getItemFromDB("SELECT parent_category FROM mypha_productcategory WHERE category_id = {$data['categoryId']}");
            $data['deptName'] = $db->getItemFromDB("SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = {$data['deptId']}");

            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'addItemsMrps':
        $event = $_POST['event'];
        $itemId = $_POST['mrpItemId'];
        $mrp = $_POST['itemMrp'];
        $id = $_POST['id'];
        $location = $_POST['location'];
        switch ($event) {
            case 'add':
                $count = $db->getItemFromDB("SELECT COUNT(*) FROM item_mrp WHERE itemMrp = {$mrp} AND stit_Id = {$itemId}");
                if ($count > 0) {
                    echo "{'success':false,'valid':false,'msg': 'MRP already added.'}";
                    exit();
                }
                $data['itemMrp'] = $mrp;
                $data['stit_Id'] = $itemId;
                $data['location'] = $location;
                $data['createdOn'] = date('Y-m-d H:i:s');
                $db->query('begin');
                $data['createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('item_mrp', $data);
                $status = $db->query('commit');
                break;
            case 'edit':
                $data['itemMrp'] = $mrp;
                $data['location'] = $location;
                $data['createdOn'] = date('Y-m-d H:i:s');
                $db->query('begin');
                $data['createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform('item_mrp', $data, 'update', " id = {$id}");
                $status = $db->query('commit');
                break;
        }

        if ($status == 1) {
            echo "{success:true,valid:true,msg:'MRP saved '}";
        } else {
            echo "{'success':false,'valid':false,'msg': 'Error While Saving.'}";
        }

        break;
    case 'listItemMrp':
        $data = $_POST;
        $gridselecter = $_POST['gridselecter'];
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'stit_SKU' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $search = ' WHERE 1=1 AND `stit_status` = 1 AND isMedicine = 0 ';
        if (isset($data['filter'])) {
        $allowedFields = ['vc_id', 'vc_name', 'vc_type', 'vc_status', 'vc_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }
        switch ($gridselecter) {
            case '1':
                $conQuery = " INNER JOIN  finascop_stock_itemmaster fsi ON im.stit_Id = fsi.stit_ID";

                break;
            case '2':
                $conQuery = " RIGHT JOIN  finascop_stock_itemmaster fsi ON im.stit_Id = fsi.stit_ID";
                $search .= ' and id IS NULL ';
                break;
            case '3':
                $conQuery = " RIGHT JOIN  finascop_stock_itemmaster fsi ON im.stit_Id = fsi.stit_ID";
                break;
            case '4':
                $conQuery = " INNER JOIN  finascop_stock_itemmaster fsi ON im.stit_Id = fsi.stit_ID";
                $search .= ' AND mrpVreify = 1 ';
                break;
            case '5':
                $conQuery = " INNER JOIN  finascop_stock_itemmaster fsi ON im.stit_Id = fsi.stit_ID";
                $search .= ' AND mrpVreify = 0 ';
                break;
        }
        $query = "SELECT id,fsi.stit_ID,stit_SKU,stit_itemName,product_category,stit_category_name,stit_brand_name,med_manufacturename,category_name,mppc.parent_category AS parent_category,itemMrp,stit_quantity,
            stit_product_variant,stit_status,fsi.isMedicine,im.isVerified AS mrpVreify,location
             FROM item_mrp im
            {$conQuery}
INNER JOIN mypha_productsubcategory mpsc ON mpsc.sub_category_id=fsi.product_category
INNER JOIN mypha_productcategory mpc ON mpc.category_id=mpsc.main_category  
INNER JOIN mypha_productparent_category mppc ON mppc.parent_category_id=mpc.parent_category 
INNER JOIN mypha_productbrands mpb ON mpb.brand_id=fsi.pdt_brand 
INNER JOIN mypha_productmanufacture mpm ON mpm.manufacture_id=mpb.manufacture_id ";
        $countQuery = "SELECT COUNT(*) FROM ({$query}) AS countStock {$search}{$where}{$searchitem}";
        $listQuery = "SELECT * FROM ({$query}) AS listStock {$search}{$where}{$searchitem} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        //echo $listQuery;
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'listPrivateCategory':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'vc_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 AND store_group_id > 0 ";
        if (isset($data['filter'])) {
        $allowedFields = ['vc_id', 'vc_name', 'vc_type', 'vc_status', 'vc_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
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


        $countQuery = "SELECT COUNT(*) FROM retaline_virtual_category  {$search}";
        $listQuery = "SELECT vc_id,vc_name,vc_parentCategoryId,vc_categoryId,mypha_productparent_category.parent_category as parent_category,category_name,
        IF((vc_status=0),'Inactive','Active') AS vc_status,if(rvc.image_url = '','No','Yes') AS hasImage,
        IF(store_group_id > 0,(SELECT store_group_name FROM finascop_branch_group WHERE finascop_branch_group.store_group_id = rvc.store_group_id),'Default') as storeGroup
        FROM retaline_virtual_category rvc "
            . "LEFT JOIN mypha_productparent_category ON parent_category_id = vc_parentCategoryId LEFT JOIN mypha_productcategory ON vc_categoryId = category_id "
            . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'listItemMrptoVerify':
        $data = $_POST;
        $gridselecter = $_POST['gridselecter'];
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'stit_SKU' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $search = ' WHERE 1=1 AND `stit_status` = 1 AND isMedicine = 0 ';
        if (isset($data['filter'])) {
        $allowedFields = ['vc_id', 'vc_name', 'vc_type', 'vc_status', 'vc_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }
        switch ($gridselecter) {
            case '1':
                $search .= ' AND mrpVreify = 1 ';

                break;
            case '0':
                $search .= ' AND mrpVreify = 0 ';
                break;
        }
        $query = "SELECT id,fsi.stit_ID,stit_SKU,stit_itemName,product_category,stit_category_name,stit_brand_name,med_manufacturename,category_name,mppc.parent_category AS parent_category,itemMrp,stit_quantity,
                stit_product_variant,stit_status,fsi.isMedicine,im.isVerified AS mrpVreify,location 
                 FROM item_mrp im  INNER JOIN  finascop_stock_itemmaster fsi ON im.stit_Id = fsi.stit_ID
    INNER JOIN mypha_productsubcategory mpsc ON mpsc.sub_category_id=fsi.product_category
    INNER JOIN mypha_productcategory mpc ON mpc.category_id=mpsc.main_category  
    INNER JOIN mypha_productparent_category mppc ON mppc.parent_category_id=mpc.parent_category 
    INNER JOIN mypha_productbrands mpb ON mpb.brand_id=fsi.pdt_brand 
    INNER JOIN mypha_productmanufacture mpm ON mpm.manufacture_id=mpb.manufacture_id ";
        $countQuery = "SELECT COUNT(*) FROM ({$query}) AS countStock {$search}{$where}{$searchitem}";
        $listQuery = "SELECT * FROM ({$query}) AS listStock {$search}{$where}{$searchitem} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        //echo $listQuery;
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'verifyMrp':
        $id = $_POST['id'];
        $data['isVerified'] = 1;
        $data['verifiedBy'] = $_SESSION['admin']->UserId;
        $data['verifiedOn'] = date('Y-m-d H:i:s');
        $db->query('begin');
        $status = $db->perform("item_mrp", $data, 'update', 'id =' . $id);
        $status = $db->query('commit');
        if ($status == 1) {

            echo "{success:true,valid:true,msg:'MRP Verified.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'mrpLocations':
        $defaultCountry = $db->getItemFromDB("SELECT country_id FROM retaline_country WHERE is_default = 1");

        $listQuery = "SELECT st_ID AS id, st_name AS `name` FROM  finascop_state WHERE cnt_ID = {$defaultCountry}";
        $data = $db->getMultipleData($listQuery, true);
        $count = count($data);
        if (!empty($data)) {
            $data[$count]['id'] = "0";
            $data[$count]['name'] = "Pan India";
            usort($data, function($a, $b) {
                return $a['id'] - $b['id'];
            });
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
}
