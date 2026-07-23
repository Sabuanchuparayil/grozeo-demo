<?php
$userid = $_SESSION['admin']->Finascop_UserId;
switch ($op) {
    case 'getSubCategory':
        $category = $_POST['category'];
        if ($category > 0) {
            $cond = " AND main_category = {$category} ";
        }
        $qry = "select sub_category_id,sub_category from  mypha_productsubcategory where status= 1  {$cond} order by sub_category";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'listAttributeMaster':
        $rec_limit = empty($_POST['limit']) ? 20 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'store_group_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $searchitem = '';
        if (isset($data['filter'])) {
        $allowedFields = ['attr_id', 'attr_name', 'attr_type', 'attr_status', 'attr_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        if ($_SESSION['admin']->IsSuperUser == 'Yes' || $_SESSION['admin']->UserType == 'Admin') {
            $searchitem .= " ";
        } else {
            $searchitem .= " AND createdBy = {$_SESSION['admin']->Finascop_UserId} ";
        }

          $countQuery = "SELECT COUNT(*) FROM (SELECT a.createdBy,a.id,a.name,a.displayAs,a.displayOrder,a.inProductDetail,a.inProductBrief,IF(a.status = 1, 'Active', 'Inactive') AS STATUS,GROUP_CONCAT(ps.sub_category) AS subCategory,mpc.category_name,dept.parent_category,fbt.business_type_name FROM attribute a 
LEFT JOIN attributeSubcategoryMap asm ON a.id = asm.attributeId
LEFT JOIN mypha_productsubcategory ps ON asm.subCategoryId = ps.sub_category_id
LEFT JOIN mypha_productcategory mpc ON mpc.category_id = ps.main_category 
LEFT JOIN mypha_productparent_category dept ON dept.parent_category_id = mpc.parent_category 
LEFT JOIN finascop_business_type fbt ON fbt.business_type_id = dept.parent_category_businessType
GROUP BY a.createdBy, a.id, a.name, a.displayAs, a.displayOrder, a.inProductDetail, a.inProductBrief, a.status, mpc.category_name, dept.parent_category, fbt.business_type_name) attriCount {$search} {$searchitem} ";
        $listQuery = "SELECT * FROM (SELECT a.createdBy,a.id,a.name,a.displayAs,a.displayOrder,a.inProductDetail,a.inProductBrief,IF(a.status = 1, 'Active', 'Inactive') AS STATUS,GROUP_CONCAT(ps.sub_category) AS subCategory,mpc.category_name,dept.parent_category,fbt.business_type_name FROM attribute a 
LEFT JOIN attributeSubcategoryMap asm ON a.id = asm.attributeId
LEFT JOIN mypha_productsubcategory ps ON asm.subCategoryId = ps.sub_category_id
LEFT JOIN mypha_productcategory mpc ON mpc.category_id = ps.main_category 
LEFT JOIN mypha_productparent_category dept ON dept.parent_category_id = mpc.parent_category 
LEFT JOIN finascop_business_type fbt ON fbt.business_type_id = dept.parent_category_businessType
GROUP BY a.createdBy, a.id, a.name, a.displayAs, a.displayOrder, a.inProductDetail, a.inProductBrief, a.status, mpc.category_name, dept.parent_category, fbt.business_type_name ) attriList {$search} {$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'saveAttributeMaster':
        $data = $_POST;
        $attibuteSubCategory = $data['attibuteSubCategory'];
        $attibuteSubCategories = explode(',', $attibuteSubCategory);
        unset($data['attibuteSubCategory']);
        unset($data['displayOrder']);
        unset($data['apikey']);
        unset($data['tstamp']);
        $db->query('begin');
        if ($_POST['id'] > 0) {
            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $userid;
             $isAttrubuteExists = $db->getItemSafe("SELECT COUNT(*) FROM attribute 
            INNER JOIN attributeSubcategoryMap ON attribute.id = attributeSubcategoryMap.attributeId 
            WHERE attributeSubcategoryMap.subCategoryId IN({$attibuteSubCategory}) 
            AND attribute.NAME = '{$data['name']}' AND id <> ? GROUP BY attribute.id, attribute.name", "i", [$_POST['id']]);
            if ($isAttrubuteExists > 0) {
                echo "{success: false, message:'Attribute already exists.'}";
                exit;
            } else {
            $status = $db->perform("attribute", $data, 'update', 'id =' . $data['id']);
            $status = $parentdb->perform("attribute", $data, 'update', 'id =' . $data['id']);
            $lastId = $data['id'];
            $attibuteSubCategoryExist = $db->getMulipleData("SELECT subCategoryId,attributeId FROM attributeSubcategoryMap WHERE attributeId  = {$lastId}", true);

            $subCategoryIds = array();

            foreach ($attibuteSubCategoryExist as $item) {
                $attibuteSubCategoryOld[] = $item['subCategoryId'];
            }
            //print_r($attibuteSubCategoryOld);exit();
            $toRemoveValues = array_diff($attibuteSubCategoryOld, $attibuteSubCategories);
            foreach ($toRemoveValues as $toRemove) {
                $status = $db->query("DELETE FROM attributeSubcategoryMap WHERE attributeId  = '{$lastId}' AND subCategoryId = {$toRemove}");
                $status = $parentdb->query("DELETE FROM attributeSubcategoryMap WHERE attributeId  = '{$lastId}' AND subCategoryId = {$toRemove}");
            }

            foreach ($attibuteSubCategories as $attibuteSubCategor) {
                $subcategoryExists = $db->getItemFromDB("SELECT COUNT(*) FROM attributeSubcategoryMap WHERE attributeId  = '{$lastId}' AND subCategoryId = {$attibuteSubCategor}");
                if ($subcategoryExists == 0) {
                    $atsDt['subCategoryId'] = $attibuteSubCategor;
                    $atsDt['attributeId'] = $lastId;
                    $status = $db->perform('attributeSubcategoryMap', $atsDt);
                    $status = $parentdb->perform('attributeSubcategoryMap', $atsDt);
                }
            }
            }
        } else {
            $isAttrubuteExists = $db->getItemFromDB("SELECT COUNT(*) FROM attribute 
            INNER JOIN attributeSubcategoryMap ON attribute.id = attributeSubcategoryMap.attributeId 
            WHERE attributeSubcategoryMap.subCategoryId IN({$attibuteSubCategory}) 
            AND attribute.NAME = '{$data['name']}' GROUP BY attribute.id, attribute.name ");
            if ($isAttrubuteExists > 0) {
                echo "{success: false, message:'Attribute already exists.'}";
                exit;
            } else {
            unset($data['id']);

            $data['createdOn'] = date('Y-m-d H:i:s');
            $data['createdBy'] = $userid;
            $status = $db->perform('attribute', $data);
            $lastId = $db->insert_id();
			$data['id'] = $lastId;
            $status = $parentdb->perform('attribute', $data);

            $status = $db->query("DELETE FROM attributeSubcategoryMap WHERE attributeId = {$lastId}");
            foreach ($attibuteSubCategories as $attibuteSubCategor) {
                $atsDt['subCategoryId'] = $attibuteSubCategor;
                $atsDt['attributeId'] = $lastId;
                $status = $db->perform('attributeSubcategoryMap', $atsDt);
                $status = $parentdb->perform('attributeSubcategoryMap', $atsDt);
            }
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'attributeMasterdetailsView':
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($id || $ID) {

            $data = $db->getFromDB("SELECT * FROM attribute  WHERE id =" . $id, true);
            $subCategries = $db->getItemFromDB("SELECT GROUP_CONCAT(subCategoryId) FROM attributeSubcategoryMap WHERE attributeId = {$data['id']}");
            $data['subCategory'] = $db->getItemFromDB("SELECT GROUP_CONCAT(sub_category) FROM mypha_productsubcategory WHERE sub_category_id IN ({$subCategries}) ");
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'attributeMaster_form_load':
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id) {
            /*$sql = "SELECT id,name,status AS comboAttributeMasterStatus,displayAs,displayOrder,valueMode,inProductDetail,inProductBrief,
            (SELECT GROUP_CONCAT(subCategoryId) FROM attributeSubcategoryMap WHERE attributeId = id) AS attibuteSubCategory,
            (SELECT main_category FROM mypha_productsubcategory  WHERE sub_category_id IN (SELECT GROUP_CONCAT(subCategoryId) FROM attributeSubcategoryMap WHERE attributeId = id)) AS main_category,
            (SELECT category_name FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory  WHERE sub_category_id IN (SELECT GROUP_CONCAT(subCategoryId) FROM attributeSubcategoryMap WHERE attributeId = id))) AS main_categoryName,
            (SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory  WHERE sub_category_id IN (SELECT GROUP_CONCAT(subCategoryId) FROM attributeSubcategoryMap WHERE attributeId = id))) AS parent_categorysc,
            (SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory  WHERE sub_category_id IN (SELECT GROUP_CONCAT(subCategoryId) FROM attributeSubcategoryMap WHERE attributeId = id)))) AS parent_categoryname,
            (SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory  WHERE sub_category_id IN (SELECT GROUP_CONCAT(subCategoryId) FROM attributeSubcategoryMap WHERE attributeId = id)))) as primary_businessTypesc,
            (SELECT business_type_name FROM finascop_business_type WHERE business_type_id = (SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = (SELECT main_category FROM mypha_productsubcategory  WHERE sub_category_id IN (SELECT GROUP_CONCAT(subCategoryId) FROM attributeSubcategoryMap WHERE attributeId = id))))) as btName FROM attribute WHERE id =" . $id;*/
            $sql = "SELECT 
            att.id as id,
            att.name AS name,
            att.status AS comboAttributeMasterStatus,
            att.displayAs,
            att.displayOrder,
            att.valueMode,
            att.inProductDetail,
            att.inProductBrief,
            GROUP_CONCAT(asm.subCategoryId) AS attibuteSubCategory,
            ps.main_category,
            pc.category_name AS main_categoryName,
            pc.parent_category AS parent_categorysc,
            pc2.parent_category AS parent_categoryname,
            pc2.parent_category_businessType AS primary_businessTypesc,
            bt.business_type_name AS btName 
        FROM 
            attribute AS att
        LEFT JOIN 
            attributeSubcategoryMap AS asm ON att.id = asm.attributeId
        LEFT JOIN 
            mypha_productsubcategory AS ps ON asm.subCategoryId = ps.sub_category_id
        LEFT JOIN 
            mypha_productcategory AS pc ON ps.main_category = pc.category_id
        LEFT JOIN 
            mypha_productparent_category AS pc2 ON pc.parent_category = pc2.parent_category_id
        LEFT JOIN 
            finascop_business_type AS bt ON pc2.parent_category_businessType = bt.business_type_id
        WHERE 
            att.id = {$id} 
        GROUP BY 
            att.id";
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
    case 'listAttributeValueMain':
        $rec_limit = empty($_POST['limit']) ? 20 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'a.name' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 AND valueMode = 1 AND status = 'Active' ";
        $searchitem = '';
        if (isset($data['filter'])) {
        $allowedFields = ['attr_id', 'attr_name', 'attr_type', 'attr_status', 'attr_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        if ($_SESSION['admin']->IsSuperUser == 'Yes' || $_SESSION['admin']->UserType == 'Admin') {
            $searchitem .= " ";
        } else {
            $searchitem .= " AND createdBy = {$_SESSION['admin']->Finascop_UserId} ";
        }

        /*$countQuery = "SELECT COUNT(*) FROM (SELECT a.createdBy,a.valueMode,a.id,a.name,a.displayAs,a.displayOrder,a.inProductDetail,a.inProductBrief,IF((a.status = 1), 'Active', 'Inactive') AS STATUS,
        (SELECT GROUP_CONCAT(sub_category) FROM mypha_productsubcategory WHERE sub_category_id IN 
        (SELECT subCategoryId FROM attributeSubcategoryMap WHERE attributeId = a.id)) AS subCategory,(SELECT COUNT(*) FROM attributeValue WHERE attributeId = a.id) AS valueCount 
        FROM attribute a ) attriCount  {$search} {$searchitem}";
        $listQuery = "SELECT * FROM (SELECT a.createdBy,a.valueMode,a.id,a.name,a.displayAs,a.displayOrder,a.inProductDetail,a.inProductBrief,IF((a.status = 1), 'Active', 'Inactive') AS status,
        (SELECT GROUP_CONCAT(sub_category) FROM mypha_productsubcategory WHERE sub_category_id IN 
        (SELECT subCategoryId FROM attributeSubcategoryMap WHERE attributeId = a.id)) AS subCategory,(SELECT COUNT(*) FROM attributeValue WHERE attributeId = a.id) AS valueCount 
        FROM attribute a ) attriList {$search} {$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
*/
        $countQuery = "SELECT COUNT(*) FROM (SELECT a.createdBy,a.valueMode,a.id,a.name,a.displayAs,a.displayOrder,a.inProductDetail,a.inProductBrief,IF((a.status = 1), 'Active', 'Inactive') AS STATUS, GROUP_CONCAT(DISTINCT(s.sub_category)) AS subCategory,COUNT(av.attributeId) AS valueCount,mpc.category_name,dept.parent_category,fbt.business_type_name
    FROM attribute a
    LEFT JOIN attributeSubcategoryMap asm ON a.id = asm.attributeId
    LEFT JOIN mypha_productsubcategory s ON asm.subCategoryId = s.sub_category_id
    LEFT JOIN attributeValue av ON a.id = av.attributeId 
    LEFT JOIN mypha_productcategory mpc ON mpc.category_id = s.main_category 
    LEFT JOIN mypha_productparent_category dept ON dept.parent_category_id = mpc.parent_category 
    LEFT JOIN finascop_business_type fbt ON fbt.business_type_id = dept.parent_category_businessType
    GROUP BY a.id,mpc.category_name, dept.parent_category, fbt.business_type_name) attriCount  {$search} {$searchitem}";
        $listQuery = "SELECT * FROM (SELECT a.createdBy,a.valueMode,a.id,a.name,a.displayAs,a.displayOrder,a.inProductDetail,a.inProductBrief,IF((a.status = 1), 'Active', 'Inactive') AS STATUS, GROUP_CONCAT(DISTINCT(s.sub_category)) AS subCategory,COUNT(av.attributeId) AS valueCount,mpc.category_name,dept.parent_category,fbt.business_type_name
    FROM attribute a
    LEFT JOIN attributeSubcategoryMap asm ON a.id = asm.attributeId
    LEFT JOIN mypha_productsubcategory s ON asm.subCategoryId = s.sub_category_id
    LEFT JOIN attributeValue av ON a.id = av.attributeId 
    LEFT JOIN mypha_productcategory mpc ON mpc.category_id = s.main_category 
    LEFT JOIN mypha_productparent_category dept ON dept.parent_category_id = mpc.parent_category 
    LEFT JOIN finascop_business_type fbt ON fbt.business_type_id = dept.parent_category_businessType
    GROUP BY a.id,mpc.category_name, dept.parent_category, fbt.business_type_name) attriList {$search} {$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'listAttributeValueSubGrid':
        $attributeId = $_POST['attributeId'];
        if ($_SESSION['admin']->IsSuperUser == 'Yes' || $_SESSION['admin']->UserType == 'Admin') {
            $brQuery = " ";
        } else {
            $brQuery = " AND createdBy = {$_SESSION['admin']->Finascop_UserId} ";
        }
        $countQuery = "SELECT COUNT(*) FROM attributeValue WHERE attributeId = {$attributeId} {$brQuery} ";
        $listQuery = "SELECT id,valueName,attributeId FROM attributeValue WHERE attributeId = {$attributeId} {$brQuery}";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getAttributes':
        $qry = "select id,name from  attribute where valueMode = 1 AND status = 1   order by name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'avDetailView':
        $attributeId = $_POST['attributeId'];
        $qry = "SELECT id,valueName,attributeId FROM attributeValue WHERE attributeId = {$attributeId}";
        $items = $db->getMulipleData($qry, true);
        $countDataQuery = "SELECT count(*) from attributeValue WHERE attributeId = {$attributeId}";
        $count = $db->getItemFromDB($countDataQuery);

        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'attributeValue_form_load':
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id) {
            $sql = "SELECT id as attributes,name  AS attributeName FROM attribute WHERE id =" . $id;
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
    case 'saveAttributeValue':
        $attributeId = $_POST['attributeId'];
        $valueName = $_POST['valueName'];
        if (empty($valueName)) {
            echo "{'success':false,'valid':false,'message': 'Enter a valid data.'}";
            exit();
        }
        $valueExists = $db->getItemFromDB("SELECT COUNT(*) FROM attributeValue WHERE attributeId = {$attributeId} AND valueName = '{$valueName}'");
        $db->query('begin');
        if ($valueExists == 0) {
            $data['attributeId'] = $attributeId;
            $data['valueName'] = $valueName;
            $data['createdOn'] = date('Y-m-d H:i:s');
            $data['createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('attributeValue', $data);
            $status = $parentdb->perform('attributeValue', $data);
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved '}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'getBusinessType':


        $qry = "select business_type_id,business_type_name from " . FINASCOP_DB . "finascop_business_type where status= 1  order by business_type_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        // echo '{success:true, data:'.json_encode($data).'}';
        break;
    case 'getDepartment':
        if ($_POST['primaryBt'] > 0) {
            $primaryBt = $_POST['primaryBt'];
        } else {
            $primaryBt = 0;
        }
        $qry = "select parent_category_id,parent_category FROM mypha_productparent_category where status= 1 AND  parent_category_businessType = {$primaryBt}  order by parent_category";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getCategory':
        if ($_POST['department'] > 0) {
            $primaryBt = $_POST['department'];
        } else {
            $primaryBt = 0;
        }
        $qry = "select category_id,category_name FROM mypha_productcategory where status= '1' AND  parent_category = {$primaryBt}  order by category_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'updateAttributeValue':
        $data = $_POST;
        $griddata = json_decode(stripslashes($data['data']));
        $griddata = (array) $griddata;

        $id = $griddata['id'];
        $attributeId = $griddata['attributeId'];
        $valueName = $griddata['valueName'];
        $valueExists = $db->getItemFromDB("SELECT COUNT(*) FROM attributeValue WHERE attributeId = {$attributeId} AND valueName = '{$valueName}' AND id <> {$id}");
        if ($valueExists > 0) {
            echo "{'success':false,'valid':false,'message': 'Value already exists.'}";
            exit();
        }
        $db->query('begin');

        $lpupDatat['valueName'] = $griddata['valueName'];
        $lpupDatat['updatedOn'] = date("Y-m-d H:i:s");
        $lpupDatat['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('attributeValue', $lpupDatat, 'update', "  id = {$id} ");
        $status = $parentdb->perform('attributeValue', $lpupDatat, 'update', "  id = {$id} ");
        $status = $db->query('commit');
        if ($status) {
            $msg = "Value Updated.";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'removeAttributeValue':
        $attributeValueId = $_POST['attributeValueId'];
        $valueDetail = $db->getFromDB("SELECT attributeId,valueName FROM attributeValue WHERE id = {$attributeValueId}", true);
        $db->query('begin');
        $logdata['deletedBy'] = $_SESSION['admin']->Finascop_UserId;
        $logdata['attributeId'] = $valueDetail['attributeId'];
        $logdata['attributeValue'] = $valueDetail['valueName'];
        $status = $db->perform('attibute_value_remove_log', $logdata);
        $delqry = "DELETE FROM attributeValue WHERE id = {$attributeValueId}";
        $status = $db->query($delqry);
        $status = $parentdb->query($delqry);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Attribute value removed.'}";
        } else {
            echo "{success: false,msg: 'Failed in removing attributeValue.'}";
        }
        break;
}
