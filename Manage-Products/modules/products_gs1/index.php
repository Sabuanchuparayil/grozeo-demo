<?php
require_once(ROOT . '/finascop_config/lib.php');
define('AWS_ROOT', '/home/system/awsapi');
require(AWS_ROOT . '/aws-autoloader.php');

use Aws\S3\S3Client;

switch ($op) {
    case 'getgs1Brands':
        $IsSuperUser = $_SESSION['admin']->IsSuperUser;
        $UserType = $_SESSION['admin']->UserType;
        if ($IsSuperUser == 'Yes' || $UserType == 'Admin') {
            $search .= " ";
        } else {
            $search .= " INNER JOIN user_brands ON user_brands.brandId = gs1_brand_source.id AND UserId = {$_SESSION['admin']->Finascop_UserId} ";
        }
        $qry = "select gs1_brand_source.id as id,brandName from gs1_brand_source  
        INNER JOIN gs1_products_extension ON gs1_brand_source.id = gs1_products_extension.brandId AND productId = 0 AND isArchived = 0 {$search} WHERE isMapped >= 1  GROUP BY (gs1_brand_source.id) order by brandName";

        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getgs1AllBrands':
        $category = $_POST['category'];
        $subcategory = $_POST['subcategory'];
        $filter_part = "WHERE 1=1 ";
        if ($category > 0) {
            $filter_part .= " AND categoryId = {$category}";
        }
        if ($subcategory > 0) {
            $filter_part .= " AND subCategoryId = {$subcategory}";
        }
        if ($category > 0 && $subcategory > 0) {
            $qry = "SELECT DISTINCT(brandId) as id,brand as brandName FROM gs1_products_source  {$filter_part}  ORDER BY brand ";
        } else {
            $qry = "select id,brandName from gs1_brand_source order by brandName";
        }
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getgs1Category':
        $oldqry = "select id,categoryName from gs1_category INNER JOIN gs1_subCategory ON categoryId = gs1_category.id order by categoryName";
        $qry = "SELECT gs1_category.id,categoryName FROM gs1_category INNER JOIN gs1_subCategory ON categoryId = gs1_category.id ORDER BY categoryName";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getgs1SubCategory':
        $category = $_POST['category'];
        if ($category > 0) {
            $qry = "select id,subCategoryName from gs1_subCategory WHERE categoryId = {$category} order by subCategoryName";
        }

        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'gs1ProdctListing':

        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $brand = $_POST['brand'];
        $category = $_POST['category'];
        $subcategory = $_POST['subcategory'];
        $archive = $_POST['archive'];
        $merchantId = $_POST['merchantId'];
        $cond = " WHERE `isMastersMapped` = 0 AND `isValid`=1  AND u.sourceProductId IS NULL ";
        //AND `brandId` > 0  AND `hs_code` <> ''AND `isValid`=1 AND `brandId` > 0  AND `hs_code` <> '' 
        //1:brand,2:item,3:Make
        if ($merchantId > 0) {
            $uploadedFrom = $db->getItemFromDB("SELECT GROUP_CONCAT(id) FROM product_source_upload_master WHERE storeGroupId = {$merchantId}");
            $cond .= " AND uploadedFrom IN ({$uploadedFrom}) ";
        }
        if (!empty($archive)) {
            $cond .= " AND isArchived = {$archive}";
        } else {
            $cond .= " AND isArchived = 0 ";
        }
        if ($brand > 0) {

            $cond .= " AND brandId = {$brand}";
        }
        if ($category > 0) {
            $cond .= " AND categoryId = {$category}";
        }
        if ($subcategory > 0) {
            $cond .= " AND subCategoryId = {$subcategory}";
        }
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $cond .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                }
            }
        }
        if (($category > 0 && $subcategory > 0) || ($brand > 0) || ($merchantId > 0)) {
            $countQuery = "SELECT COUNT(g.id) FROM gs1_products_source AS g LEFT JOIN upload_product_map AS u ON g.id = u.sourceProductId  {$cond} LIMIT 0,1000 ";
            $count = $db->getItemFromDB($countQuery);

            $qry = "SELECT g.id as id,brand,name,gtin,description,category,sub_category,gpc_code,hs_code,igst,image_front,
            image_back,image_top,image_bottom,image_left,image_right,image_top_left,image_top_right FROM gs1_products_source AS g LEFT JOIN upload_product_map AS u ON g.id = u.sourceProductId  {$cond}  ORDER BY {$sort} {$dir} LIMIT 0,1000";
            $data = $db->getMultipleData($qry, true);
        }
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getBusinessType':
        $qry = "select business_type_id,business_type_name from finascop_business_type where status= 1  order by business_type_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getDepartment':
        $primaryBt = $_POST['primaryBt'];
        if ($primaryBt > 0) {
            $cond = " AND parent_category_businessType = {$primaryBt} ";
        } else {
            $cond = " ";
        }
        $qry = "select parent_category_id,parent_category FROM mypha_productparent_category where status= 1 {$cond}  order by parent_category";
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
    case 'getSubCategory':
        if ($_POST['category'] > 0) {
            $category = $_POST['category'];
        } else {
            $category = 0;
        }
        $qry = "select sub_category_id,sub_category FROM mypha_productsubcategory where status= '1' AND  main_category = {$category}  order by sub_category";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'mapMastertoProducts':
        $itemar = $_POST['itemarr'];
        $retailCategory = $_POST['retailCategory'];
        $department = $_POST['department'];
        $category = $_POST['category'];
        $subCategory = $_POST['subCategory'];

        $itemdecode = json_decode($itemar);
        $itemcount = count($itemdecode);
        $db->query('begin');
        for ($i = 0; $i < $itemcount; $i++) {
            $gs1Data = $db->getFromDB("SELECT id,brandId,brand,name,gtin,caution,sku_code,description,categoryId,category,subCategoryId,sub_category,gpc_code,marketing_info,url,activation_date,deactivation_date,derived_description,country_of_origin,created_date,modified_date,type,packaging_type,published,company_detail,weights_and_measures,dimensions,case_configuration,hs_code,igst,cgst,sgst,margin,attributes,additional_attributes,image_front,image_back,image_top,image_bottom,image_left,image_right,image_top_left,image_top_right,importedOn,updatedOn,storeGroupId FROM gs1_products_source WHERE id = {$itemdecode[$i]}", true);
            if (OPERATING_COUNTRY != 'INDIA') {
                $companyDeta = $gs1Data['company_detail'];
                $companyId = $db->getItemFromDB('SELECT id FROM gs1_company WHERE companyName = "' . mysqli_real_escape_string($db->linker(), $companyDeta) . '"');
            } else {
                $companyDeta = json_decode($gs1Data['company_detail']);
                $companyId = $db->getItemFromDB('SELECT id FROM gs1_company WHERE companyName = "' . mysqli_real_escape_string($db->linker(), $companyDeta->name) . '"');
            }

            $gs1DupData['storeGroupId'] = $gs1Data['storeGroupId'];
            $gs1DupData["brandId"] = $gs1Data['brandId'];
            $gs1DupData["brand"] = $gs1Data['brand'];
            $gs1DupData["name"] = $gs1Data['name'];
            $gs1DupData["gtin"] = $gs1Data['gtin'];
            $gs1DupData["caution"] = $gs1Data['caution'];
            $gs1DupData["sku_code"] = $gs1Data['sku_code'];
            $gs1DupData["description"] = $gs1Data['description'];
            $gs1DupData["categoryId"] = $gs1Data['categoryId'];
            $gs1DupData["category"] = $gs1Data['category'];
            $gs1DupData["subCategoryId"] = $gs1Data['subCategoryId'];
            $gs1DupData["sub_category"] = $gs1Data['sub_category'];
            $gs1DupData["gpc_code"] = $gs1Data['gpc_code'];
            $gs1DupData["marketing_info"] = $gs1Data['marketing_info'];
            $gs1DupData["url"] = $gs1Data['url'];
            $gs1DupData["activation_date"] = $gs1Data['activation_date'];
            $gs1DupData["deactivation_date"] = $gs1Data['deactivation_date'];
            $gs1DupData["derived_description"] = $gs1Data['derived_description'];
            $gs1DupData["country_of_origin"] = $gs1Data['country_of_origin'];
            $gs1DupData["created_date"] = $gs1Data['created_date'];
            $gs1DupData["modified_date"] = $gs1Data['modified_date'];
            $gs1DupData["type"] = $gs1Data['type'];
            $gs1DupData["packaging_type"] = $gs1Data['packaging_type'];
            $gs1DupData["published"] = $gs1Data['published'];
            $gs1DupData["companyId"] = $companyId;
            $gs1DupData["company_detail"] = $gs1Data['company_detail'];
            $gs1DupData["weights_and_measures"] = $gs1Data['weights_and_measures'];
            $gs1DupData["dimensions"] = $gs1Data['dimensions'];
            $gs1DupData["case_configuration"] = $gs1Data['case_configuration'];
            $gs1DupData["hs_code"] = $gs1Data['hs_code'];
            $gs1DupData["igst"] = $gs1Data['igst'];
            $gs1DupData["cgst"] = $gs1Data['cgst'];
            $gs1DupData["sgst"] = $gs1Data['sgst'];
            $gs1DupData["margin"] = $gs1Data['margin'];
            $gs1DupData["attributes"] = $gs1Data['attributes'];
            $gs1DupData["additional_attributes"] = $gs1Data['additional_attributes'];
            $gs1DupData["image_front"] = $gs1Data['image_front'];
            $gs1DupData["image_back"] = $gs1Data['image_back'];
            $gs1DupData["image_top"] = $gs1Data['image_top'];
            $gs1DupData["image_bottom"] = $gs1Data['image_bottom'];
            $gs1DupData["image_left"] = $gs1Data['image_left'];
            $gs1DupData["image_right"] = $gs1Data['image_right'];
            $gs1DupData["image_top_left"] = $gs1Data['image_top_left'];
            $gs1DupData["image_top_right"] = $gs1Data['image_top_right'];
            $gs1DupData["importedOn"] = $gs1Data['importedOn'];

            $gs1DupData["retailCategory"] = $retailCategory;
            $gs1DupData["department"] = $department;
            $gs1DupData["categoryGr"] = $category;
            $gs1DupData["subCategoryGr"] = $subCategory;
            $gs1DupData["isMastersMapped"] = 1;
            $gs1DupData["id"] = $gs1Data['id'];
            //if (OPERATING_COUNTRY != 'INDIA') {
            $gs1DupData = array_filter($gs1DupData);
            //}
            $status = $db->perform('gs1_products_extension', $gs1DupData);

            $data = array(
                "isMastersMapped" => 1,
                "updatedOn" => date("Y-m-d H:i:s")
            );
            $status = $db->perform('gs1_products_source', $data, 'update', " id = {$itemdecode[$i]}");

            $status = $db->query("UPDATE gs1_brand_source SET isMapped = 1 where id = {$gs1Data['brandId']}");
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listProcessedProducts':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'gs1_products_extension.name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $brand = $_POST['brand_id'];
        $category = $_POST['category'];
        $subcategory = $_POST['subcategory'];
        $merchantId = $_POST['merchantId'];
        if ($_POST['ind'] == "skip") {
            $cond = " WHERE 1=1 AND gs1_products_extension.isArchived > 1 AND gs1_products_extension.productId = 0";
        } else {
            $cond = " WHERE 1=1 AND gs1_products_extension.isMastersMapped = 1 AND gs1_products_extension.productId = 0";
            if ($_POST['showArchived'] == 1) {
                $cond .= " AND gs1_products_extension.isArchived = 1 ";
            } else {
                $cond .= " AND gs1_products_extension.isArchived = 0 ";
            }
        }

        if ($_POST['showConverted'] == 1) {
            $cond .= " AND gs1_products_extension.isBrandTrimmed = 1 AND gs1_products_extension.hasMappedItemMaster = 1  ";
            if ($brand > 0) {
                $cond .= " AND gs1_products_extension.brandId = {$brand}";
            }
        } else {
            if ($brand > 0) {
                $cond .= " AND gs1_products_extension.brandId = {$brand}";
            }
        }


        //1:brand,2:item,3:Make
        if ($brand > 0) {
            $cond .= " AND gs1_products_extension.brandId = {$brand}";
        }
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');
                if ($field['field'] == 'itemName') {
                    $cond .= " and (gs1_products_extension.name LIKE '%{$field['data']['value']}%') ";
                } else {
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
        $cond .= " AND u.sourceProductId IS NULL";
        if ($merchantId > 0) {
            $uploadedFrom = $db->getItemFromDB("SELECT GROUP_CONCAT(id) FROM product_source_upload_master WHERE storeGroupId = {$merchantId}");
            $cond .= " AND uploadedFrom IN ({$uploadedFrom}) ";
        }
        if ($merchantId > 0) {
            $countQuery = "SELECT COUNT(gs1_products_extension.id) FROM gs1_products_extension INNER JOIN gs1_products_source ON gs1_products_source.id = gs1_products_extension.id LEFT JOIN upload_product_map AS u ON gs1_products_extension.id = u.sourceProductId {$cond} ";
            $count = $db->getItemFromDB($countQuery);
            $qry = "SELECT  gs1_products_extension.id,gs1_products_extension.brand,gs1_products_extension.name,gs1_products_extension.gtin,
            gs1_products_extension.description,gs1_products_extension.category,gs1_products_extension.sub_category,
            gs1_products_extension.gpc_code,gs1_products_extension.productMaster,if(gs1_products_extension.isBrandTrimmed =1,gs1_products_extension.brandTrimmedName,gs1_products_extension.name) as itemName,gs1_products_extension.isBrandTrimmed,gs1_products_extension.brandTrimmedName,gs1_products_extension.hasMappedItemMaster,gs1_products_extension.isProMasterGenerated 
            FROM gs1_products_extension 
            INNER JOIN gs1_products_source ON gs1_products_source.id = gs1_products_extension.id 
            LEFT JOIN upload_product_map AS u ON gs1_products_extension.id = u.sourceProductId {$cond}  ORDER BY {$sort} {$dir}";
            $data = $db->getMultipleData($qry, true);
        } else {
            $countQuery = "SELECT COUNT(id) FROM gs1_products_extension LEFT JOIN upload_product_map AS u ON gs1_products_extension.id = u.sourceProductId {$cond} ";
            $count = $db->getItemFromDB($countQuery);
            $qry = "SELECT  id,brand,name,gtin,description,category,sub_category,gpc_code,productMaster,if(isBrandTrimmed =1,brandTrimmedName,name) as itemName,isBrandTrimmed,brandTrimmedName,hasMappedItemMaster,isProMasterGenerated,gtin 
            FROM gs1_products_extension LEFT JOIN upload_product_map AS u ON gs1_products_extension.id = u.sourceProductId {$cond}  ORDER BY {$sort} {$dir}";
            $data = $db->getMultipleData($qry, true);
        }

        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getGs1Product_EditData':
        $id = $_POST['id'];
        $qry = "select * FROM gs1_products_extension  where id = {$id} ";
        $data = $db->getFromDB($qry, true);
        $weights_and_measures = json_decode($data['weights_and_measures']);
        $dimensions = json_decode($data['dimensions']);
        $case_configuration = json_decode($data['case_configuration']);
        $netWeight = $weights_and_measures->net_content;
        if ($data['isArchived'] > 0) {
            $skipReasonOnform = $db->getItemFromDB("SELECT name FROM gs1_skip_status WHERE id = {$data['isArchived']}");
            $data['skipReasonOnform'] = $skipReasonOnform;
        } else {
            $data['skipReasonOnform'] == '';
        }
        $data['gtinName'] = $data['name'];
        $data['packaging_type'] = $data['packaging_type'];
        $data['netWeight'] = $netWeight;
        $data['netUnit'] = $weights_and_measures->measurement_unit;
        $data['packageUnit'] = $dimensions->measurement_unit;
        $data['packageHeight'] = $dimensions->height;
        $data['packageWidth'] = $dimensions->width;
        $data['packageDepth'] = $dimensions->depth;
        $data['packageWtUnt'] = $dimensions->measurement_unit;
        $data['packageWt'] = $case_configuration->weight;
        $data['grCategory'] = $db->getItemFromDB("SELECT category_name FROM mypha_productcategory WHERE category_id = {$data['categoryGr']}");
        $data['grSubCategory'] = $db->getItemFromDB("SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id = {$data['subCategoryGr']}");
        $data['retailCategoryName'] = $db->getItemFromDB("SELECT business_type_name FROM finascop_business_type WHERE business_type_id = {$data['retailCategory']}");
        $data['deptName'] = $db->getItemFromDB("SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = {$data['department']}");
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'trimProductBrands':
        $itemar = $_POST['itemarr'];
        $type = $_POST['type'];

        $itemdecode = json_decode($itemar);
        $itemcount = count($itemdecode);

        for ($i = 0; $i < $itemcount; $i++) {


            $brand = $db->getItemFromDB("SELECT brand FROM gs1_products_extension WHERE id  = {$itemdecode[$i]} ");
            $prdctName = $db->getItemFromDB("SELECT name FROM gs1_products_extension WHERE id  = {$itemdecode[$i]} ");
            $itammasterName = str_ireplace($brand, " ", $prdctName);
            $itammasterName = trim($itammasterName);
            // preg_replace('/^' . preg_quote($brand, '/') . '/', '', $prdctName);
            $data = array(
                "brandTrimmedName" => $itammasterName,
                "isBrandTrimmed" => 1
            );

            $status = $db->perform('gs1_products_extension', $data, 'update', " id  = {$itemdecode[$i]}");
            $gs1data = array(
                "isMastersMapped" => 2,
                "updatedOn" => date("Y-m-d H:i:s")
            );
            $status = $db->perform('gs1_products_source', $gs1data, 'update', " id  = {$itemdecode[$i]}");
        }

        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listProductsToMapItemMaster':
        $itemarr = json_decode(stripslashes($_POST['itemarr']), true);
        $itemIds = implode(',', $itemarr);

        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;

        $cond = " WHERE 1=1 AND isMastersMapped = 1 AND productId = 0 AND id IN ({$itemIds}) ";
        //AND isBrandTrimmed = 1 

        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');
                $checkComa = strstr($field['data']['value'], ',');
                if ($field['field'] == 'itemName') {
                    $cond .= " and (name LIKE '%{$field[data][value]}%') ";
                } else {
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $cond .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                    }
                }
            }
        }
        $countQuery = "SELECT COUNT(id) FROM gs1_products_extension  {$cond} ";
        $count = $db->getItemFromDB($countQuery);

        $qry = "SELECT id,brand,name,gtin,description,category,sub_category,gpc_code,productMaster,if(isBrandTrimmed =1,brandTrimmedName,name) as itemName,isBrandTrimmed,brandTrimmedName,hasMappedItemMaster,isProMasterGenerated FROM gs1_products_extension {$cond}  ORDER BY {$sort} {$dir}";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'mapItemMastertoProducts':
        $itemar = $_POST['itemarr'];
        $productMasterName = $_POST['itemMasterName'];

        $itemdecode = json_decode($itemar);
        $itemcount = count($itemdecode);

        $itemMaterId = $db->getItemFromDB("SELECT itemname_id FROM finascop_stock_itemmastername WHERE item_name = '{$productMasterName}'");
        if ($itemMaterId > 0) {
            $productMasterId = $itemMaterId;
        } else {
            $itemData = array(
                "item_name" => $productMasterName,
                "status" => 1,
                "created_on" => date("Y-m-d H:i:s")
            );
            $status = $db->perform("finascop_stock_itemmastername", $itemData);
            $productMasterId = $db->insert_id();
        }
        for ($i = 0; $i < $itemcount; $i++) {

            $data = array(
                "productMaster" => $productMasterName,
                "productMasterId" => $productMasterId,
                "hasMappedItemMaster" => 1
            );

            $status = $db->perform('gs1_products_extension', $data, 'update', " id  = {$itemdecode[$i]}");
            $gs1data = array(
                "isMastersMapped" => 4,
                "updatedOn" => date("Y-m-d H:i:s")
            );
            $status = $db->perform('gs1_products_source', $gs1data, 'update', " id  = {$itemdecode[$i]}");
        }

        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'saveProductMainMasters':
        $id = $_POST['id'];
        $productDetails = $db->getFromDB("SELECT * FROM gs1_products_extension WHERE id = {$id}", true);

        $scrapProductDetails = $db->getFromDB("SELECT * FROM gs1_scrap_itemmaster WHERE gsId = {$id}", true);

        $gsId = $_POST['gsId'];
        $scrapDatas = $db->getFromDB("SELECT * FROM product_scrap_data WHERE gsId = {$id}", true);
        $scrappedDatas = json_decode($scrapDatas['scrapData']);


        //brand,manufacture,hsncode,gst,orgin country,units
        $company_detail = json_decode($productDetails['company_detail']);
        if (!empty($scrapProductDetails['med_manufacturename'])) {
            $manufactureName = $scrapProductDetails['med_manufacturename'];
        } else {
            $manufactureName = $company_detail->name;
        }
        $isManufactureAvailable  = $db->getItemFromDB('SELECT manufacture_id FROM mypha_productmanufacture WHERE manufacture_name = "' . $manufactureName . '"');
        $db->query('begin');
        if ($isManufactureAvailable > 0) {
            $manufactureId = $isManufactureAvailable;
        } else {
            $manuData = array(
                "manufacture_name" => $manufactureName,
                "created_on" => date("Y-m-d H:i:s"),
                "status" => 1
            );
            $status = $db->perform("mypha_productmanufacture", $manuData);
            $manufactureId = $db->insert_id();
        }
        $productData['med_manufactureid'] = $manufactureId;
        $productData['med_manufacturename'] = $manufactureName;

        $isBrandAvailable  = $db->getItemFromDB('SELECT brand_id FROM mypha_productbrands WHERE brand_name = "' . mysqli_real_escape_string($db->linker(), $productDetails['brand']) . '" and manufacture_id = ' . $manufactureId);
        if ($isBrandAvailable > 0) {
            $brandId = $isBrandAvailable;
        } else {
            $brandData = array(
                "brand_name" => $productDetails['brand'],
                "manufacture_id" => $manufactureId,
                "created_on" => date("Y-m-d H:i:s"),
                "status" => 1
            );
            $status = $db->perform("mypha_productbrands", $brandData);
            $brandId = $db->insert_id();
        }

        $productData['pdt_brand'] = $brandId;
        $productData['stit_brand_name'] = $productDetails['brand'];

        $isHsnAvailable  = $db->getItemFromDB("SELECT hsn_id FROM finascop_hsn WHERE hsn_code = '{$productDetails['hs_code']}'");
        if ($isHsnAvailable > 0) {
            $hsnupData = array(
                "gst_percent" => $productDetails['igst']
            );
            $status = $db->perform("finascop_hsn", $hsnupData, 'update', " hsn_id = {$isHsnAvailable}");
            $hsnId = $isHsnAvailable;
        } else {
            $hsnData = array(
                "hsn_code" => $productDetails['hs_code'],
                "gst_percent" => $productDetails['igst'],
                "created_on" => date("Y-m-d H:i:s"),
                "status" => 1
            );
            $status = $db->perform("finascop_hsn", $hsnData);
            $hsnId = $db->insert_id();
        }

        $isGstAvailable  = $db->getItemFromDB("SELECT id FROM hsn_value WHERE hsnId = {$hsnId} AND hsnGst = '{$productDetails['igst']}'");
        if ($isGstAvailable > 0) {
            $gstId = $isGstAvailable;
        } else {
            $gstData = array(
                "hsnId" => $hsnId,
                "hsnGst" => $productDetails['igst'],
            );
            $status = $db->perform("hsn_value", $gstData);
            $gstId = $db->insert_id();
        }
        $productData['stit_HSNCode'] = $hsnId;
        $productData['stit_HSN_code'] = $productDetails['hs_code'];
        $productData['stit_hsnId'] = $hsnId;
        $productData['stit_GST'] = $gstId;
        $productData['GST'] = $productDetails['igst'];
        $productData['HSN'] = $productDetails['hs_code'];

        $orgCountryAvaialable = $db->getItemFromDB("SELECT country_id FROM finascop_country WHERE country_name = '{$productDetails['country_of_origin']}'");
        if ($orgCountryAvaialable > 0) {
            $countryId = $orgCountryAvaialable;
        } else {
            $countryData = array(
                "country_name" => $productDetails['country_of_origin'],
                "created_on" => date("Y-m-d H:i:s"),
                "status" => 1
            );
            $status = $db->perform("finascop_country", $countryData);
            $countryId = $db->insert_id();
        }

        $productData['stit_orgin_country'] = $countryId;
        $productData['orgCountryName'] = $productDetails['country_of_origin'];

        $weights_and_measures = json_decode($productDetails['weights_and_measures']);
        $dimensions = json_decode($productDetails['dimensions']);
        if (!empty($weights_and_measures->measurement_unit)) {
            $unitAvaialable = $db->getItemFromDB("SELECT unit_id FROM mypha_unit WHERE unit_name = '{$weights_and_measures->measurement_unit}'");
            if ($unitAvaialable > 0) {
                $unitId = $unitAvaialable;
            } else {
                $unitData = array(
                    "unit_name" => $weights_and_measures->measurement_unit,
                    "created_on" => date("Y-m-d H:i:s"),
                    "status" => 1
                );
                $status = $db->perform("mypha_unit", $unitData);
                $unitId = $db->insert_id();
            }

            $productData['stit_unit'] = $unitId;
            $productData['stit_unitName'] = $weights_and_measures->measurement_unit;
        }

        if (!empty($scrapProductDetails['stit_Description'])) {
            $productData['stit_Description'] = $scrapProductDetails['stit_Description'];
        } else {
            $productData['stit_Description'] = $productDetails['description'];
        }

        if (!empty($scrapProductDetails['stit_long_description'])) {
            $productData['stit_long_description'] = $scrapProductDetails['stit_long_description'];
        } else {
            $productData['stit_long_description'] = $productDetails['derived_description'];
        }

        $productData['product_category'] = $productDetails['subCategoryGr'];
        $productData['stit_category_name'] = $db->getItemFromDB("SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id = {$productDetails['subCategoryGr']} ");
        $productData['stit_itemId'] = $productDetails['productMasterId'];
        $productData['stit_itemName'] = $db->getItemFromDB("SELECT item_name FROM finascop_stock_itemmastername WHERE itemname_id = {$productDetails['productMasterId']} ");
        $weights_and_measures = json_decode($productDetails['weights_and_measures']);

        if (!empty($scrapProductDetails['stit_qty'])) {
            $productData['stit_qty'] = $scrapProductDetails['stit_qty'];
        } else {
            $productData['stit_qty'] = $weights_and_measures->net_content;
        }
        $productData['stit_courierWt'] = $weights_and_measures->gross_weight;
        $productData['stit_quantity'] = $productData['stit_qty'] . ' ' . $weights_and_measures->measurement_unit;
        if (!empty($scrapProductDetails['dimensions'])) {
            $amzdimensions = explode('x', $scrapProductDetails['dimensions']);
            $productData['item_breadth'] = $amzdimensions[1];
            $productData['item_height'] = $amzdimensions[2];
            $productData['item_length'] = $amzdimensions[0];
        } else {
            $productData['item_breadth'] = $dimensions->depth;
            $productData['item_height'] = $dimensions->height;
            $productData['item_length'] = $dimensions->width;
        }

        $attributes = json_decode($productDetails['attributes']);
        $productData['stit_ingredients'] = $attributes->ingredients;
        $productData['stit_allergens'] = $attributes->allergen_information;
        $productData['stit_nutritionlabel'] = $attributes->nutritional_information;
        if (!empty($scrapProductDetails['stit_product_variant'])) {
            $productData['stit_product_variant'] = $scrapProductDetails['stit_product_variant'];
        } else {
            $productData['stit_product_variant'] = $productDetails['variant'];
        }
        if (!empty($scrapProductDetails['stit_foodtype'])) {
            $productData['stit_foodtype'] = $scrapProductDetails['stit_foodtype'];
        } else {
            $productData['stit_foodtype'] = $productDetails['edibility'];
        }

        $productData['stii_csb'] = 1;
        $productData['stii_csbretail'] = 1;
        $productData['stitl1_optimumqty'] = 1;
        $productData['stit11_minimumqty'] = 1;
        $productData['stit11_maximumqty'] = 1;
        $productData['stitl2_optimumqty'] = 1;
        $productData['stit12_minimumqty'] = 1;
        $productData['stit12_maximumqty'] = 1;
        $productData['stitl3_optimumqty'] = 1;
        $productData['stit13_minimumqty'] = 1;
        $productData['stit13_maximumqty'] = 1;

        $skuBrandName = $productData['stit_brand_name'];
        $skuItemName = $productData['stit_itemName'];
        if ($skuBrandName == 'Generic') {
            $skuBrandName = '';
        }
        //$SKU = $productData['stit_brand_name'] . " " . $productData['stit_itemName']. " " . $productData['stit_product_variant'] . " " . $productData['stit_quantity']. " " . $_POST['least_package_type_name'];
        if ($skuItemName == 'Private Product') {
            $stit_SKU = $productDetails['name'];
        } else {
            $stit_SKU = $skuBrandName . " " . $skuItemName . " " . $productData['stit_product_variant'] . " " . $productData['stit_quantity'];
        }
        if (!empty($scrapProductDetails['stit_SKU'])) {
            $productData['stit_SKU'] = $scrapProductDetails['stit_SKU'];
        } else {
            $productData['stit_SKU'] = $stit_SKU;
        }

        $productData['gtin'] = $productDetails['gtin'];

        $db->query('commit');
        echo '{"success":true,"data":' . json_encode($productData) . '}';
        break;
    case 'updateItemMasterName':
        $data = $_POST;
        $griddata = json_decode(stripslashes($data['data']));
        $griddata = (array) $griddata;

        $hasMappedItemMaster = $db->getItemFromDB("SELECT hasMappedItemMaster FROM gs1_products_extension WHERE id  = {$griddata['id']}");
        if ($hasMappedItemMaster == 1) {
            $msg = "Item Already mapped";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
            exit();
        }
        $productMasterName = $griddata['productMaster'];
        $itemMaterId = $db->getItemFromDB("SELECT itemname_id FROM finascop_stock_itemmastername WHERE item_name = '{$productMasterName}'");
        if ($itemMaterId > 0) {
            $productMasterId = $itemMaterId;
        } else {
            $itemData = array(
                "item_name" => $productMasterName,
                "status" => 1,
                "created_on" => date("Y-m-d H:i:s")
            );
            $status = $db->perform("finascop_stock_itemmastername", $itemData);
            $productMasterId = $db->insert_id();
        }


        $db->query('begin');
        $data = array(
            "productMaster" => $productMasterName,
            "productMasterId" => $productMasterId,
            "hasMappedItemMaster" => 1
        );

        $status = $db->perform('gs1_products_extension', $data, 'update', " id  = {$griddata['id']} AND hasMappedItemMaster = 0 ");
        $gs1data = array(
            "isMastersMapped" => 4,
            "updatedOn" => date("Y-m-d H:i:s")
        );
        $status = $db->perform('gs1_products_source', $gs1data, 'update', " id  = {$griddata['id']}");
        $status = $db->query('commit');
        if ($status) {
            $msg = "Saved Successfully";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'updateGS1fromProduct':
        $gs1Id = $_POST['gs1Id'];
        $stit_ID = $_POST['stit_ID'];
        $db->query('begin');
        $prdctdata = array(
            "productId" => $stit_ID,
            "updatedOn" => date("Y-m-d H:i:s")
        );

        $status = $db->perform('gs1_products_extension', $prdctdata, 'update', " id  = {$gs1Id} AND productId = 0 ");
        $gs1data = array(
            "isMastersMapped" => 5,
            "updatedOn" => date("Y-m-d H:i:s")
        );
        $status = $db->perform('gs1_products_source', $gs1data, 'update', " id  = {$gs1Id}");
        $gs1Data = $db->getFromDB("SELECT gtin,name,case_configuration,image_front,image_back,image_top,image_bottom,image_left,image_right,image_top_left,image_top_right FROM gs1_products_extension WHERE id  = {$gs1Id}", true);
        $fsidata['prdctBankItemName'] = $gs1Data['name'];
        $fsidata['gtin'] = $gs1Data['gtin'];
        $fsidata['case_configuration'] = $gs1Data['case_configuration'];
        $status = $db->perform('finascop_stock_itemmaster', $fsidata, 'update', " stit_ID  = {$stit_ID} ");

        /*$productMrps = $db->getMultipleData("SELECT * FROM gs1_product_mrp_source WHERE productId = {$gs1Id}", true);
        foreach ($productMrps as $productMrp) {
            $mrpdata = array(
                "stit_Id" => $stit_ID,
                "itemMrp" => $productMrp['mrp'],
                "location" => $productMrp['location'],
                "createdOn" => date("Y-m-d H:i:s")
            );
            if ($productMrp['mrp'] > 0) {
                $status = $db->perform('item_mrp', $mrpdata);
            }
        }*/

        $fstrd['fsipc_isCompany'] = 1;
        $fstrd['fsipc_isIndividual'] = 1;
        $fstrd['fsipc_storeGroup'] = 0;
        $fstrd['fsipc_store'] = 0;
        $fstrd['fsipc_stit_id'] = $stit_ID;
        $fstrd['fsipc_code'] = $gs1Data['gtin'];
        $fstrd['fsipc_codeType'] = 'Company Barcode';
        $isGlobal = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = '{$fstrd['fsipc_stit_id']}' AND fsipc_isCompany = 1");
        if ($isGlobal == 0) {
            $fstrd['fsipc_createdOn'] = date("Y-m-d H:i");
            $fstrd['fsipc_createdBy'] = $_SESSION['admin']->Finascop_UserId;

            $status = $db->perform('finascop_stock_itemmaster_product_codes', $fstrd);
            $fsipcs['fsipc_id'] = $db->insert_id();

            $fsipcs['fsipc_stit_id'] = $stit_ID;
            $fsipcs['fsipcs_store'] = $fstrd['fsipc_store'];
            $fsipcs['fsipcs_Code'] = $fstrd['fsipc_code'];
            $status = $db->perform('finascop_stock_itemmaster_product_code_stores', $fsipcs);
        }


        $images = array();
        if (!empty($gs1Data['image_front']))
            array_push($images, strtok($gs1Data['image_front'], '?'));
        if (!empty($gs1Data['image_back']))
            array_push($images, strtok($gs1Data['image_back'], '?'));
        if (!empty($gs1Data['image_top']))
            array_push($images, strtok($gs1Data['image_top'], '?'));
        if (!empty($gs1Data['image_bottom']))
            array_push($images, strtok($gs1Data['image_bottom'], '?'));
        if (!empty($gs1Data['image_left']))
            array_push($images, strtok($gs1Data['image_left'], '?'));
        if (!empty($gs1Data['image_right']))
            array_push($images, strtok($gs1Data['image_right'], '?'));
        if (!empty($gs1Data['image_top_left']))
            array_push($images, strtok($gs1Data['image_top_left'], '?'));
        if (!empty($gs1Data['image_top_right']))
            array_push($images, strtok($gs1Data['image_top_right'], '?'));
        $imageCount = count($images);
        //echo $imageCount;
        //global $s3upload;
        if ($imageCount > 0) {
            foreach ($images as $imagePath) {
                $fileuploadname = trim(str_replace('.', '', uniqid("", true))) . "." . pathinfo(basename($imagePath), PATHINFO_EXTENSION);
                $destinationPath = $_SERVER["DOCUMENT_ROOT"] . '/tmp/importimages/' . $fileuploadname;
                if (copy($imagePath, $destinationPath)) {

                    $s3upload = new cgoS3FileHandler();

                    $cloudFrontPath = 'products/';

                    //$isFileUploaded =
                    $s3upload->putFileToS3($cloudFrontPath, AWSBUCKETUPLOADS, $destinationPath, $fileuploadname);
                    //echo 'isFileUploaded'.$isFileUploaded;
                    //if ($isFileUploaded == 1)
                    if (file_exists($destinationPath)) {
                        $data = array(
                            "product_id" => $stit_ID,
                            "image_url" => $fileuploadname,
                            "image_thumb_url" => '',
                            "image_type" => 1,
                            "bucket_name" => '',
                            "createdBy" => $_SESSION['admin']->Finascop_UserId,
                            "created_at" => date("Y-m-d H:i:s"),
                            "image_folder" => 'products/'
                        );
                        $status = $db->perform('finascop_stock_item_images', $data);
                    }
                }
            }
        }

        $scrapProductDetails = $db->getFromDB("SELECT mrps,images FROM gs1_scrap_itemmaster WHERE gsId = {$gs1Id}", true);
        if (!empty($scrapProductDetails['images'])) {
            $scrapImages = explode(',', $scrapProductDetails['images']);
            foreach ($scrapImages as $scrapImage) {

                $fileuploadname = trim(str_replace('.', '', uniqid("", true))) . "." . pathinfo(basename($scrapImage), PATHINFO_EXTENSION);
                $destinationPath = $_SERVER["DOCUMENT_ROOT"] . '/tmp/importimages/' . $fileuploadname;
                if (copy($scrapImage, $destinationPath)) {

                    $s3upload = new cgoS3FileHandler();

                    $cloudFrontPath = 'products/';

                    //$isFileUploaded =
                    $s3upload->putFileToS3($cloudFrontPath, AWSBUCKETUPLOADS, $destinationPath, $fileuploadname);
                    //echo 'isFileUploaded'.$isFileUploaded;
                    //if ($isFileUploaded == 1)
                    if (file_exists($destinationPath)) {
                        $data = array(
                            "product_id" => $stit_ID,
                            "image_url" => $fileuploadname,
                            "image_thumb_url" => '',
                            "image_type" => 1,
                            "bucket_name" => '',
                            "image_folder" => 'products/'
                        );
                        $status = $db->perform('finascop_stock_item_images', $data);
                    }
                }
            }
        }
        /*if (!empty($scrapProductDetails['mrps'])) {
            $scrapMrps = explode(',', $scrapProductDetails['mrps']);
            foreach ($scrapMrps as $scrapMrp) {
                $scrapMrp = preg_replace('/[^0-9]/s', "", $scrapMrp);
                $scrapMrp = str_replace('kg', "", $scrapMrp);
                $scrapMrp = stripslashes($scrapMrp);
                $scrapMrp = trim($scrapMrp);
                $mrpExists = $db->getItemFromDB("SELECT COUNT(*) FROM item_mrp WHERE stit_Id = {$stit_ID} AND itemMrp = {$scrapMrp}");
                if ($mrpExists == 0) {
                    $mrpdata = array(
                        "stit_Id" => $stit_ID,
                        "itemMrp" => $scrapMrp,
                        "createdOn" => date("Y-m-d H:i:s")
                    );
                    if ($scrapMrp > 0) {
                        $status = $db->perform('item_mrp', $mrpdata);
                    }
                }
            }
        }*/


        $status = $db->query('commit');
        if ($status) {
            $msg = "Saved Successfully";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'generateGS1ItemMaster':
        $itemar = $_POST['itemarr'];
        $productMasterName = $_POST['itemMasterName'];

        $itemdecode = json_decode($itemar);
        $itemcount = count($itemdecode);

        for ($i = 0; $i < $itemcount; $i++) {
            $items[$itemdecode[$i]] = $db->getItemFromDB("SELECT brandTrimmedName FROM gs1_products_extension WHERE id  = {$itemdecode[$i]}");
        }

        foreach ($items as $key => $item) {

            // Use explode() to split the item into words
            $words = explode(' ', $item);

            // Get the first two words and combine them into a category
            $category = $words[0] . ' ' . $words[1];

            // Add the category to the $itemsProcessed array
            $result[$key] = $category;
        }

        /*$words = array_map(function ($value) {
            return explode(' ', $value)[0];
        }, $items);

        $commonWords = array_unique($words);

        $result = array_map(function ($value) use ($items, $commonWords) {
            $commonWordsInValue = array_values(array_intersect($commonWords, explode(" ", $value)));
            return $commonWordsInValue[0] . ' ' . $commonWordsInValue[1];
        }, $items);

        $result = array_combine(array_keys($items), $result);*/

        $db->query('begin');
        foreach ($result as $reKey => $reVal) {
            $data = array(
                "productMaster" => $reVal,
                "isProMasterGenerated" => 1
            );

            $status = $db->perform('gs1_products_extension', $data, 'update', " id  = {$reKey} AND hasMappedItemMaster = 0");
            $gs1data = array(
                "isMastersMapped" => 3,
                "updatedOn" => date("Y-m-d H:i:s")
            );
            $status = $db->perform('gs1_products_source', $gs1data, 'update', " id  = {$reKey}");
        }
        $status = $db->query('commit');
        if ($status) {
            $msg = "Product Master Generated";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'updateGS1Products':
        $data['variant'] = $_POST['variant'];
        $data['edibility'] = $_POST['edibility'];
        if (empty($_POST['barcodeUnVerified']))
            $data['barcodeUnVerified'] = 0;
        else
            $data['barcodeUnVerified'] = $_POST['barcodeUnVerified'];

        $db->query('begin');
        $status = $db->perform('gs1_products_extension', $data, 'update', " id  = {$_POST['id']} ");
        $status = $db->query('commit');
        if ($status) {
            $msg = "Product Master Generated";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }

        break;
    case 'confirmGeneratedMaster':
        $itemar = $_POST['itemarr'];
        $itemdecode = json_decode($itemar);
        $itemcount = count($itemdecode);

        for ($i = 0; $i < $itemcount; $i++) {
            $productMasterName = $db->getItemFromDB("SELECT productMaster FROM gs1_products_extension WHERE ID = {$itemdecode[$i]}");
            $itemMaterId = $db->getItemFromDB("SELECT itemname_id FROM finascop_stock_itemmastername WHERE item_name = '{$productMasterName}'");
            if ($itemMaterId > 0) {
                $productMasterId = $itemMaterId;
            } else {
                $itemData = array(
                    "item_name" => $productMasterName,
                    "status" => 1,
                    "created_on" => date("Y-m-d H:i:s")
                );
                $status = $db->perform("finascop_stock_itemmastername", $itemData);
                $productMasterId = $db->insert_id();
            }


            $db->query('begin');
            $data = array(
                "productMaster" => $productMasterName,
                "productMasterId" => $productMasterId,
                "hasMappedItemMaster" => 1
            );

            $status = $db->perform('gs1_products_extension', $data, 'update', " id  = {$itemdecode[$i]} AND hasMappedItemMaster = 0 ");
            $gs1data = array(
                "isMastersMapped" => 4,
                "updatedOn" => date("Y-m-d H:i:s")
            );
            $status = $db->perform('gs1_products_source', $gs1data, 'update', " id  = {$itemdecode[$i]}");
        }
        $status = $db->query('commit');
        if ($status) {
            $msg = "Saved Successfully";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'getMasterBrands':
        $qry = "select brand_id,brand_name from mypha_productbrands WHERE status = 1 order by brand_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'ProdctListing':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'stit_SKU' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $brand = $_POST['brand'];

        $cond = " WHERE isMedicine = 0  ";
        //1:brand,2:item,3:Make
        if ($brand > 0) {
            $cond .= " AND pdt_brand = {$brand}";
        }

        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $cond .= " and ({$field[field]} LIKE '%{$field[data][value]}%') ";
                }
            }
        }

        //$countQuery = "SELECT COUNT(*) FROM finascop_stock_itemmaster  {$cond} ";
        //$count = $db->getItemFromDB($countQuery);

        $qry = "SELECT stit_ID ,stit_itemName ,stit_SKU,IF(stit_status = 1,'Active','Inactive') AS statusName,stit_category_name,stit_brand_name,gtin,isMedicine,
        IF((isVerified = 1),'Yes','No') AS isVerified,pdt_brand,       
        IF(mrp.mrps> 0,'Yes','No') AS hasMrp,IF(exportProduct.exportedPrdcts> 0,'Yes','No') AS isExported
        FROM finascop_stock_itemmaster LEFT JOIN finascop_usr_profile ON UserId = createdBy 
        LEFT JOIN (SELECT stit_id AS stitid, COUNT(*) AS mrps FROM item_mrp GROUP BY stit_id ) mrp ON mrp.stitid = finascop_stock_itemmaster.stit_ID 
        LEFT JOIN (SELECT product_stitId AS stitid, COUNT(*) AS exportedPrdcts FROM product_grozeo_map GROUP BY product_stitId ) exportProduct ON exportProduct.stitid = finascop_stock_itemmaster.stit_ID
        ";
        $count = $db->getItemFromDB("SELECT COUNT(*) FROM ({$qry}) AS countItem {$cond}  ORDER BY {$sort} {$dir} ");
        $listQuery = "SELECT * FROM ({$qry}) AS listItem  {$cond}  ORDER BY {$sort} {$dir}";
        $data = $db->getMultipleData($listQuery, true);

        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'showImages':
        ob_start();
        switch (OPERATING_COUNTRY) {
            case 'INDIA':
                include('ImageFile.php');
                break;
            case 'UK':
                include('s3images.php');
                break;
            default:
                include('ImageFile.php');
                break;
        }


        $rehtml = ob_get_clean();
        echo $rehtml;
        exit;

        break;
    case 'listMrpsofGs1Products':

        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(*) from gs1_product_mrp_source WHERE mrp > 0 and productId = {$_POST['gs1Id']} {$filter_part}";
        $listQuery = "SELECT * from gs1_product_mrp_source WHERE mrp > 0 and productId = {$_POST['gs1Id']} {$filter_part}  ORDER BY $rec_sort $rec_sort_dir ";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'listDistinctBrands':
        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' WHERE 1=1';
        $category = $_POST['category'];
        $subcategory = $_POST['subcategory'];
        $data = $_POST;

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        if ($category > 0) {
            $filter_part .= " AND categoryId = {$category}";
        }
        if ($subcategory > 0) {
            $filter_part .= " AND subCategoryId = {$subcategory}";
        }
        $countQuery = "SELECT COUNT(DISTINCT(brandId)) from gs1_products_source  {$filter_part}";
        $listQuery = "SELECT DISTINCT(brandId),brand FROM gs1_products_source  {$filter_part}  ORDER BY $rec_sort $rec_sort_dir ";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getMasterItems':
        $qry = "select itemname_id,item_name from finascop_stock_itemmastername WHERE status = 1 order by item_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'archiveProduct':
        $itemar = $_POST['itemarr'];
        $type = $_POST['type'];
        $ind = $_POST['ind'];
        $skipReason = $_POST['skipReason'];

        $itemdecode = json_decode($itemar);
        $itemcount = count($itemdecode);
        $db->query('begin');
        for ($i = 0; $i < $itemcount; $i++) {

            $gs1data = array(
                "isArchived" => $skipReason,
                "updatedOn" => date("Y-m-d H:i:s")
            );
            /*if($ind == "skip"){
			$gs1data = array(                
                "isArchived" => 1,
				"updatedOn" => date("Y-m-d H:i:s")
            );
			}else{
				$gs1data = array(                
                "isArchived" => 2,
				"updatedOn" => date("Y-m-d H:i:s")
            );
			}*/
            $status = $db->perform('gs1_products_extension', $gs1data, 'update', " id  = {$itemdecode[$i]}");
        }
        $status = $db->query('commit');

        if ($status == 1) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'archiveBaseProducts':
        $itemar = $_POST['itemarr'];

        $itemdecode = json_decode($itemar);
        $itemcount = count($itemdecode);

        for ($i = 0; $i < $itemcount; $i++) {
            $gs1data = array(
                "isArchived" => 1,
                "updatedOn" => date("Y-m-d H:i:s")
            );
            $status = $db->perform('gs1_products_source', $gs1data, 'update', " id  = {$itemdecode[$i]}");
        }

        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listEnabledBrands':

        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' WHERE isEnabled = 1 ';

        $data = $_POST;

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(id) from gs1_brand_source  {$filter_part}";
        $listQuery = "SELECT id,brandName from gs1_brand_source  {$filter_part}  ORDER BY $rec_sort $rec_sort_dir ";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'getEnabledBrands':

        $search .= " ";
        //$qry = "select id,brandName from gs1_brand_source {$search} WHERE isEnabled = 1 order by brandName";
        $qry = "SELECT gs1_brand_source.id,brandName FROM gs1_brand_source  
        LEFT JOIN gs1_products_source ON gs1_brand_source.id = brandId  WHERE isEnabled = 1 AND isMastersMapped = 0 AND isValid =  1 AND isArchived = 0 GROUP BY (gs1_brand_source.id) ORDER BY brandName";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getSkipStatus':
        $search .= " ";
        $qry = "select id,name from gs1_skip_status {$search} order by sort asc";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'checkReconcile':
        $status = 1;
        $brand = $_POST['$brand'];
        $brandName = $db->getItemFromDB("SELECT brandName FROM gs1_brand_source WHERE id = '{$brand}'");
        $prefixDetails = $db->getMultipleData("SELECT id,companyname,prefix FROM gs1_brand_company_source WHERE brandname = '{$brandName}'", true);
        foreach ($prefixDetails as $prefixDetail) {
            $isReconciled = $db->getItemFromDB("SELECT isReconciled FROM gs1_product_insert_log_source WHERE gcpID = '{$prefixDetail['prefix']}'");
            if ($isReconciled == 0) {
                $status = 0;
            }
        }

        if ($status == 1) {
            echo '{"success":true,"valid":true,"prefix":{$prefix} }';
        } else {
            echo '{"success":false,"valid":false,"prefix":{$prefix} }';
        }
        break;
    case 'mapConfirmGS1ItemMaster':
        $itemar = $_POST['itemarr'];
        $productMasterName = 'Private Product';

        $itemdecode = json_decode($itemar);
        $itemcount = count($itemdecode);

        $itemMaterId = $db->getItemFromDB("SELECT itemname_id FROM finascop_stock_itemmastername WHERE item_name = 'Private Product'");

        $productMasterId = $itemMaterId;

        for ($i = 0; $i < $itemcount; $i++) {

            $data = array(
                "productMaster" => $productMasterName,
                "productMasterId" => $productMasterId,
                "hasMappedItemMaster" => 1
            );

            $status = $db->perform('gs1_products_extension', $data, 'update', " id  = {$itemdecode[$i]}");
            $gs1data = array(
                "isMastersMapped" => 4,
                "updatedOn" => date("Y-m-d H:i:s")
            );
            $status = $db->perform('gs1_products_source', $gs1data, 'update', " id  = {$itemdecode[$i]}");
        }

        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'duplicateImages':
        $ItemId = $_POST['id'];
        $stitId = $_POST['stit_ID'];

        $db->query('begin');
        $itemImages = $db->getMultipleData("SELECT * FROM finascop_stock_item_images WHERE product_id = {$ItemId}", true);
        foreach ($itemImages as $itemImage) {
            if (!empty($itemImage['image_url'])) {
                $imgdata = array(
                    "product_id" => $stitId,
                    "image_url" => $itemImage['image_url'],
                    "createdBy" => $_SESSION['admin']->Finascop_UserId,
                    "created_at" => date("Y-m-d H:i:s"),
                    "image_thumb_url" => '',
                    "image_type" => $itemImage['image_type'],
                    "bucket_name" => '',
                    "image_folder" => 'products/'
                );
                $status = $db->perform('finascop_stock_item_images', $imgdata);
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved '}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'getMrpsofGs1Products':

        if ($_POST['gs1Id'] > 0) {
            $gs1Id = $_POST['gs1Id'];
        } else {
            $gs1Id = 0;
        }
        $qry = "select id,concat(mrp,' - ',location) as mrp FROM gs1_product_mrp_source WHERE mrp > 0 and productId = {$gs1Id} ";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';

        break;
    case 'listProductimages':
        $rec_limit = empty($_POST['limit']) ? 28 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";

        // Specify your AWS credentials and region
        $credentials = [
            'key'    => PBUPLOADACCESSID,
            'secret' => PBUPLOADSECRETKEY,
            'region' => PBUPLOADREGION
        ];

        // Create an S3Client object
        $s3Client = new S3Client([
            'version'     => 'latest',
            'credentials' => $credentials,
            'region'      => PBUPLOADREGION
        ]);

        // Bucket name
        $bucketName = 'productbankimages';

        // Prefix to search for
        $prefix = $_POST['gtin'];

        //try {
        // Use the listObjectsV2 method to list objects with the specified prefix
        $result = $s3Client->listObjectsV2([
            'Bucket' => $bucketName,
            'Prefix' => $prefix
        ]);
        $data = [];
        $count = count($result);
        foreach ($result['Contents'] as $key => $object) {
            $data[$key]['id'] = $key + 1;
            $data[$key]['imagetype'] = 'Third Party';
            $data[$key]['imagename'] = $object['Key'];
            $data[$key]['thumpimg_path'] = 'https://productbankimages.s3.eu-west-2.amazonaws.com/' . $object['Key'];
        }


        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'confirmSelectedImages':
        $itemar = $_POST['itemarr'];

        $itemdecode = json_decode($itemMasterItemsArr);
        $itemcount = count($itemdecode);
        $itemImages = json_decode($itemMasterImagesArr);

        $gs1DupData["image_front"] = $itemImages[0];
        $gs1DupData["image_back"] = $itemImages[1];
        $gs1DupData["image_top"] = $itemImages[2];
        $gs1DupData["image_bottom"] = $itemImages[3];
        $gs1DupData["image_left"] = $itemImages[4];
        /*$gs1DupData["image_right"] = $itemImages[5];
        $gs1DupData["image_top_left"] = $itemImages[6];
        $gs1DupData["image_top_right"] = $itemImages[7];*/
        $status = $db->perform('gs1_products_extension', $gs1DupData, 'update', "id = " . intval($_POST['gsId']));

        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'mapHSNtoProducts':
        $itemar = $_POST['itemarr'];
        $hsnId = $_POST['hsnId'];
        $hsnValue = $_POST['hsnValue'];
        $taxValue = $_POST['taxValue'];
        $taxValueId = $_POST['taxValueId'];
        $brandId = $_POST['brandId'];

        $itemdecode = json_decode($itemar);
        $itemcount = count($itemdecode);
        $db->query('begin');
        for ($i = 0; $i < $itemcount; $i++) {
            //if (OPERATING_COUNTRY != 'INDIA') {
            $gs1DupData['hs_code'] = $hsnValue;
            $gs1DupData['igst'] = $taxValue;
            $status = $db->perform('gs1_products_extension', $gs1DupData, 'update', " id = {$itemdecode[$i]}");
            //}
        }
        $status = $db->query("UPDATE gs1_brand_source SET isMapped = 2 where id = {$brandId}");
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'hsnProdctListing':

        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $brand = $_POST['brand'];
        $category = $_POST['category'];
        $subcategory = $_POST['subcategory'];
        $archive = $_POST['archive'];
        $merchantId = $_POST['merchantId'];
        $joinCond = " ";
        $cond = " WHERE g.productId = 0  AND g.hs_code = '' AND u.sourceProductId IS NULL ";
        //AND `brandId` > 0  AND `hs_code` <> ''AND `isValid`=1 AND `brandId` > 0  AND `hs_code` <> '' 
        //1:brand,2:item,3:Make
        if ($merchantId > 0) {
            $uploadedFrom = $db->getItemFromDB("SELECT GROUP_CONCAT(id) FROM product_source_upload_master WHERE storeGroupId = {$merchantId}");
            $cond .= " AND uploadedFrom IN ({$uploadedFrom}) ";
            $joinCond .= " INNER JOIN gs1_products_source ON gs1_products_source.id = g.id ";
        }
        if (!empty($archive)) {
            $cond .= " AND g.isArchived = {$archive}";
        } else {
            $cond .= " AND g.isArchived = 0 ";
        }
        if ($brand > 0) {

            $cond .= " AND g.brandId = {$brand}";
        }
        if ($category > 0) {
            $cond .= " AND g.categoryId = {$category}";
        }
        if ($subcategory > 0) {
            $cond .= " AND g.subCategoryId = {$subcategory}";
        }
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $cond .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                }
            }
        }
        if (($category > 0 && $subcategory > 0) || ($brand > 0) || ($merchantId > 0)) {
            $countQuery = "SELECT COUNT(g.id) FROM gs1_products_extension AS g {$joinCond} LEFT JOIN upload_product_map AS u ON g.id = u.sourceProductId {$cond} LIMIT 0,1000 ";
            $count = $db->getItemFromDB($countQuery);

            $qry = "SELECT g.id as id,g.brand,g.name,g.gtin,g.description,g.category,g.sub_category,g.gpc_code,g.hs_code,g.igst,g.image_front,
                g.image_back,g.image_top,g.image_bottom,g.image_left,g.image_right,g.image_top_left,g.image_top_right,
                g.retailCategory,(SELECT business_type_name FROM finascop_business_type WHERE business_type_id = g.retailCategory) AS retailCategoryName,
                g.department,(SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = g.department) AS departmentName,
                g.categoryGr,(SELECT category_name FROM mypha_productcategory WHERE category_id = g.categoryGr) AS categoryName,
                g.subCategoryGr,(SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id = g.subCategoryGr) AS subCategName FROM gs1_products_extension AS g {$joinCond} LEFT JOIN upload_product_map AS u ON g.id = u.sourceProductId  {$cond}  ORDER BY {$sort} {$dir} LIMIT 0,1000";
            $data = $db->getMultipleData($qry, true);
        }
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getExistingHsns':
        $qry = "SELECT id AS taxValueId,hsn_id,hsnGst,hsn_code,CONCAT(hsn_code,'-',hsnGst,'%') AS codeGst FROM hsn_value INNER JOIN finascop_hsn ON hsn_id = hsnId ORDER BY hsn_code";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getEnabledBrandsforHsn':

        $search .= " ";
        //$qry = "select id,brandName from gs1_brand_source {$search} WHERE isEnabled = 1 order by brandName";
        $qry = "SELECT gs1_brand_source.id,brandName FROM gs1_brand_source  
            LEFT JOIN gs1_products_source ON gs1_brand_source.id = brandId  WHERE gs1_brand_source.isMapped IN(1) AND isEnabled = 1 AND  isValid =  1 AND isArchived = 0 GROUP BY (gs1_brand_source.id) ORDER BY brandName";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'updateGS1forMerchantProduct':
        $gs1Id = $_POST['gs1Id'];
        $stit_ID = $_POST['stit_ID'];
        $db->query('begin');
        $prdctdata = array(
            "productId" => $stit_ID,
            "updatedOn" => date("Y-m-d H:i:s")
        );

        $status = $db->perform('gs1_products_extension', $prdctdata, 'update', " id  = {$gs1Id} AND productId = 0 ");
        $gs1data = array(
            "isMastersMapped" => 5,
            "updatedOn" => date("Y-m-d H:i:s")
        );
        $status = $db->perform('gs1_products_source', $gs1data, 'update', " id  = {$gs1Id}");


        $status = $db->query('commit');
        if ($status) {
            $msg = "Saved Successfully";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'listMatchedCatgeorys':
        $searchCategory = $_POST['searchCategory'];
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'sub_category_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;

        $cond = " WHERE 1=1 ";
        if ($brand > 0) {
            $cond .= " AND pdt_brand = {$brand}";
        }

        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $cond .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                }
            }
        }


        $qry = "SELECT bt.business_type_id,
    bt.business_type_name AS retailCategory,
    ppc.parent_category_id,
    ppc.parent_category AS department,
    pc.category_id,sc.sub_category_id,sc.main_category,
    pc.category_name AS category,
    sc.sub_category AS subCategory
FROM 
    mypha_productsubcategory sc
INNER JOIN 
    mypha_productcategory pc ON pc.category_id = sc.main_category
INNER JOIN 
    mypha_productparent_category ppc ON ppc.parent_category_id = pc.parent_category
INNER JOIN 
    finascop_business_type bt ON bt.business_type_id = ppc.parent_category_businessType
WHERE 
    bt.business_type_name LIKE '%{$searchCategory}%'
    OR ppc.parent_category LIKE '%{$searchCategory}%'
    OR pc.category_name LIKE '%{$searchCategory}%'
    OR sc.sub_category LIKE '%{$searchCategory}%' ";
        $count = $db->getItemFromDB("SELECT COUNT(*) FROM ({$qry}) AS countItem {$cond}  ORDER BY {$sort} {$dir} ");
        $listQuery = "SELECT * FROM ({$qry}) AS listItem  {$cond}  ORDER BY {$sort} {$dir}";
        $data = $db->getMultipleData($listQuery, true);

        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
}
