<?php

switch ($op) {
    case 'listadvertisement':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'adv_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['ad_name', 'ad_type', 'ad_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* RAW (disabled): if (isset($_POST['filter'])) { */

            foreach ($_POST['filter'] as $key => $val) {
                if ($val['field'] == 'adzone_name') {
                    $adzone_name = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(adv_id),0) FROM app_advertisements WHERE adzone_id IN (SELECT GROUP_CONCAT(adzone_id) FROM app_adzones WHERE adzone_name LIKE '{$val['data']['value']}%')");
                    $search .= " AND adv_id IN ({$adzone_name}) ";
                } else {
                    $search .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
                }
            }
        }
        $countQuery = "SELECT COUNT(*) FROM (SELECT adv_id,adv_title,adv_status,adzone_name,aa.adzone_id AS ad_id,
        CASE WHEN adv_status = 0 THEN 'Inactive' WHEN adv_status = 1 THEN 'Active' END AS advStatus,
        CASE WHEN adv_applicable_for = 1 THEN 'Grozeo' WHEN adv_applicable_for = 2 THEN 'Tenant' END AS advApplicable,
        CASE WHEN adv_applicable_category = 1 THEN 'Business Category' WHEN adv_applicable_for = 2 THEN 'Retail Category' END AS advApplicableCategory,
        CASE WHEN adv_applicable_category = 1 THEN (SELECT business_type_name FROM finascop_business_type WHERE business_type_id = adv_applicable_category_value) 
        WHEN adv_applicable_for = 2 THEN (SELECT business_category_name FROM retaline_business_category WHERE business_category_id = adv_applicable_category_value) END AS advApplicableCategoryName
         FROM app_advertisements aa"
            . " INNER JOIN app_adzones az ON aa.adzone_id = az.adzone_id "
            . ") as countAd ";
        $listQuery = "SELECT * FROM (SELECT adv_id,adv_title,adv_status,adzone_name,aa.adzone_id AS ad_id,
        CASE WHEN adv_status = 0 THEN 'Inactive' WHEN adv_status = 1 THEN 'Active' END AS advStatus,
        CASE WHEN adv_applicable_for = 1 THEN 'Grozeo' WHEN adv_applicable_for = 2 THEN 'Tenant' END AS advApplicable,
        CASE WHEN adv_applicable_category = 1 THEN 'Business Category' WHEN adv_applicable_for = 2 THEN 'Retail Category' END AS advApplicableCategory,
        CASE WHEN adv_applicable_category = 1 THEN  (SELECT business_category_name FROM retaline_business_category WHERE business_category_id = adv_applicable_category_value)
        WHEN adv_applicable_for = 2 THEN (SELECT business_type_name FROM finascop_business_type WHERE business_type_id = adv_applicable_category_value) END AS advApplicableCategoryName
         FROM app_advertisements aa"
            . " INNER JOIN app_adzones az ON aa.adzone_id = az.adzone_id "
            . " ) as listad  {$search} AND adv_status <> 2  ORDER BY {$sort} {$dir} limit $start,$limit";
        /*  $listQuery =  "SELECT  adv_id,adv_title,adv_status,adzone_name,aa.adzone_id AS ad_id FROM app_advertisements aa INNER JOIN app_adzones az ON aa.adzone_id = az.adzone_id  WHERE  adv_status != 2  {$search} ORDER BY {$sort} {$dir} limit $start,$limit "; */
        //advStatus,advApplicable,advApplicableCategory,advApplicableCategoryName
        $db->printGridJson($countQuery, $listQuery);
        break;


    case 'advName':
        $applicationId = $_POST['applicationId'];
        $listQuery = "SELECT adzone_id AS ad_id,adzone_name,adzone_width,adzone_height from app_adzones WHERE adzone_mode = {$applicationId} ";

        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'prtName':
        $query = $_POST['query'];
        if ($query != '') {
            $con = " where stit_SKU like '%" . $query . "%'";
        } else {
            $con = " ";
        }
        $listQuery = "SELECT stit_ID,stit_SKU from finascop_stock_itemmaster {$con} LIMIT 1,50";
        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'categoryName':

        $listQuery = "SELECT sub_category_id ,sub_category from mypha_productsubcategory where status = 1";
        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'brandName':
        getJsonKeyArray("SELECT brand_id ,brand_name from mypha_productbrands where status = 1");
        //        $listQuery = "SELECT brand_id ,brand_name from mypha_productbrands where status = 1";
        //        $data = $db->getMultipleData($listQuery, true);
        //        if (!empty($data)) {
        //            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        //        } else
        //            echo '{"totalCount":"0","data":[]}';
        break;
    case 'saveAdvertisement':

        $db->query('begin');
        $edit_status = $_POST['edit_status'];

        $data = array(
            "adv_title" => $_POST['title'],
            "adzone_id" => $_POST['adzone'],
            "adv_status" => $_POST['Status'],
            "adv_usageType" => $_POST['usageType'],
            "adv_offer" => $_POST['adv_offer'],
            "adv_theme" => $_POST['adv_theme'],
            "adv_offerType" => $_POST['adv_offerType'],
            "adv_startdate " => $_POST['adv_startdate'],
            "adv_enddate " => $_POST['adv_enddate'],
            "adv_createdOn" => date('Y-m-d H:i:s'),
            "adv_imageurl" => $_POST['adv_imageurl'],
            "adv_applicable_for" => $_POST['adv_applicable_for'],
            "adv_applicable_category" => $_POST['adv_applicable_category'],
            "adv_applicable_category_value" => $_POST['adv_applicable_category_value']
        );
        if ($_POST['adv_offerValueId'] == "") {
            unset($data['adv_offerValueId']);
        } else {
            $data['adv_offerValueId'] = $_POST['adv_offerValueId'];
        }
        if ($_POST['adv_offerpercent'] == "") {
            unset($data['adv_offerpercent']);
        } else {
            $data['adv_offerpercent'] = $_POST['adv_offerpercent'];
        }
        if ($edit_status == 1) {
            $data['adv_updatedOn'] = date('Y-m-d H:i:s');
            $adzone_name = $db->perform("app_advertisements", $data, 'update', 'adv_id = ' . intval($_POST['id']));
            $lastId = $_POST['id'];
        } else {
            unset($data['adv_id']);
            $data['adv_createdOn'] = date('Y-m-d H:i:s');
            $adzone_name = $db->perform('app_advertisements', $data);
            $lastId = $db->insert_id();
        }

        $return_rec = $db->getFromDb("SELECT adv_id,adv_title,adv_imageurl,aa.adzone_id AS ad_id,adzone_name,DATE_FORMAT(adv_startdate, '%d-%m-%Y') AS adv_startdate,DATE_FORMAT(adv_enddate, '%d-%m-%Y') AS adv_enddate,adv_link FROM app_advertisements aa INNER JOIN app_adzones az ON aa.adzone_id = az.adzone_id WHERE adv_id= {$lastId}", true);
        $adzone_name = $db->query('commit');
        if ($adzone_name) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'advertisementDetailsView':
        $adv_id = isset($_POST['adv_id']) ? intval($_POST['adv_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($adv_id || $ID) {

            $data = $db->getFromDB("SELECT CASE WHEN adv_offerType = 'Product' THEN (SELECT stit_SKU from finascop_stock_itemmaster WHERE stit_ID=adv_offerValueId) "
                . "WHEN adv_offerType = 'Category' THEN (SELECT sub_category from mypha_productsubcategory WHERE sub_category_id=adv_offerValueId) "
                . "ELSE (SELECT brand_name from mypha_productbrands WHERE brand_id=adv_offerValueId) END AS product ,"
                . "CASE WHEN adv_offer = 'Product' THEN (SELECT stit_SKU from finascop_stock_itemmaster WHERE stit_ID=adv_offerValueId) "
                . "WHEN adv_offer = 'Category' THEN (SELECT sub_category from mypha_productsubcategory WHERE sub_category_id=adv_offerValueId) "
                . "ELSE (SELECT brand_name from mypha_productbrands WHERE brand_id=adv_offerValueId) END AS product1,adv_applicable_for,adv_applicable_category,adv_applicable_category_value,"
                . "adv_id,adv_title,adv_imageurl,aa.adzone_id AS ad_id,adzone_name,DATE_FORMAT(adv_startdate, '%d-%m-%Y') AS adv_startdate,"
                . "DATE_FORMAT(adv_enddate, '%d-%m-%Y') AS adv_enddate,adv_offer,adv_offerpercent,CASE WHEN adv_status = 0 THEN 'Inactive' WHEN adv_status = 1 THEN 'Active' END AS advStatus,
                    CASE WHEN adv_applicable_for = 1 THEN 'Grozeo' WHEN adv_applicable_for = 2 THEN 'Tenant' END AS advApplicable,
                    CASE WHEN adv_applicable_category = 1 THEN 'Business Category' WHEN adv_applicable_for = 2 THEN 'Retail Category' END AS advApplicableCategory,
                    CASE WHEN adv_applicable_category = 1 THEN  (SELECT business_category_name FROM retaline_business_category WHERE business_category_id = adv_applicable_category_value)
                    WHEN adv_applicable_for = 2 THEN (SELECT business_type_name FROM finascop_business_type WHERE business_type_id = adv_applicable_category_value) END AS advApplicableCategoryName,
                    az.adzone_width as adzoneImageWidth,az.adzone_height as adzoneImageHeight FROM app_advertisements aa "
                . "INNER JOIN app_adzones az ON aa.adzone_id = az.adzone_id WHERE adv_id=" . $adv_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'advertisement_load':
        $adv_id = isset($_POST['adv_id']) ? intval($_POST['adv_id']) : 0;
        if ($adv_id) {
            $sql = "SELECT adv_id,adv_title,adv_status, adv_usageType,adv_imageurl,adzone_id AS ad_id,adv_theme,DATE_FORMAT(adv_startdate, '%Y-%m-%d') AS adv_startdate,DATE_FORMAT(adv_enddate, '%Y-%m-%d') AS adv_enddate,"
                . "CASE WHEN adv_offerType = 'Product' THEN (SELECT stit_SKU from finascop_stock_itemmaster WHERE stit_ID=adv_offerValueId) "
                . "WHEN adv_offerType = 'Category' THEN (SELECT sub_category from mypha_productsubcategory WHERE sub_category_id=adv_offerValueId) "
                . "ELSE (SELECT brand_name from mypha_productbrands WHERE brand_id=adv_offerValueId) END AS adv_offerValue_name,"
                . "CASE WHEN adv_offer = 'product' THEN (SELECT stit_SKU from finascop_stock_itemmaster WHERE stit_ID=adv_offerValueId) "
                . "WHEN adv_offer = 'category' THEN (SELECT sub_category from mypha_productsubcategory WHERE sub_category_id=adv_offerValueId) "
                . "ELSE (SELECT brand_name from mypha_productbrands WHERE brand_id=adv_offerValueId) END AS adv_offerValue_namess,adv_applicable_for,adv_applicable_category,adv_applicable_category_value,"
                . "adv_offer,adv_offerType,adv_offerValueId,adv_offerpercent FROM app_advertisements  WHERE adv_id= " . $adv_id;
            $results = $db->getFromDB($sql, true);
            //az.adzone_width as adzoneImageWidth,az.adzone_height as adzoneImageHeight
            $adzoneDetails = $db->getFromDB("SELECT adzone_width,adzone_height FROM app_adzones WHERE adzone_id = {$results['ad_id']}", true);
            $results['adzoneImageWidth'] = $adzoneDetails['adzone_width'];
            $results['adzoneImageHeight'] = $adzoneDetails['adzone_height'];
            if (!empty($results)) {
                echo '{"success":true, "data":', json_encode($results), '}';
            } else {
                echo '{"success":true,"data":[]}';
            }
        }
        break;
    case 'get_img_s3_details':
        $rid = $_POST['rid'];
        $data['albumBucketName'] = AWSBUCKETNAME;
        $data['accessKey'] = AWSS3ASSETUPLOADACCESSID;
        $data['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
        $data['bucketRegion'] = AWSS3ASSETUPLOADREGION;
        $data['oncompleteurl'] = AWSADBUCKETFOLDER;
        if ($rid) {
            $data['img_path_db'] = $db->getItemFromDB("select adv_imageurl from app_advertisements where `adv_id`= {$rid}");
        } else {
            $data['img_path_db'] = '';
        }
        echo "{success : true,'data':" . json_encode($data) . "}";
        break;


    case 'getAdImage':
        $adv_id = $_POST['adv_id'];
        $main_img = $_POST['main_img'];
        $qry = "select adv_id,adv_imageurl from app_advertisements where `adv_id`= {$adv_id}";
        $data = $db->getMultipleData($qry, true);
        // $result = $db->getMultipleData($stockregId, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            $data = '';
            echo '{"success":true,"data":' . json_encode($data) . '}';
        }

        break;

    case 'deleteAd':
        $id = $_POST['adv_id'];
        //$status = $_POST['activestatus'];
        $data = array(
            'adv_status' => 2,
            'adv_updatedOn' => date('Y-m-d H:i:s'),
            'adv_updatedBy' => $_SESSION['admin']->UserId
        );
        $qry = $db->perform(FINASCOP_DB . 'app_advertisements', $data, 'update', 'adv_id=' . $id);
        if ($qry) {
            echo "{success: true,msg:'Deleted Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'advApplcableCategory':
        $type = $_POST['type'];
        if ($type == 1) {
            $listQuery = "SELECT business_category_id as id ,business_category_name as name  from retaline_business_category where store_group_id = 0 AND status = 1";
        } else {
            $listQuery = "SELECT business_type_id as id ,business_type_name as name from finascop_business_type where status = 1";
        }

        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'appThemes':

        $data = $db->getMultipleData("SELECT id AS theme_id,title AS theme_name FROM theme WHERE status = 1", true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
}
