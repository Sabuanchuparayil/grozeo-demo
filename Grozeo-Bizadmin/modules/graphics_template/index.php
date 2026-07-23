<?php

switch ($op) {
    case 'listgraphicsTemplate':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'grpTemp_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['gt_name', 'gt_type', 'gt_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* RAW (disabled): if (isset($_POST['filter'])) { */

            foreach ($_POST['filter'] as $key => $val) {
                if ($val['field'] == 'adzone_name') {
                    $adzone_name = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(grpTemp_id),0) FROM graphics_template WHERE adzone_id IN (SELECT GROUP_CONCAT(adzone_id) FROM app_adzones WHERE adzone_name LIKE '{$val['data']['value']}%')");
                    $search .= " AND grpTemp_id IN ({$adzone_name}) ";
                } else {
                    $search .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
                }
            }
        }
        if ($sort == 'createdOn') {
            $sort = 'grpTemp_id';
        }
        $countQuery = "SELECT COUNT(*) FROM (SELECT FirstName,id AS grpTemp_id,title AS grpTemp_title,STATUS AS grpTemp_status,themeId,
        application AS grpTemp_application,location AS grpTemp_location,template AS grpTemp_type,adzoneId AS grpTemp_adzones,
        CASE WHEN application = 1 THEN 'Website'  WHEN application = 2 THEN 'Mobile App' WHEN application = 3 THEN 'Facebook' 
        WHEN application = 4 THEN 'Instagram' WHEN application = 5 THEN 'WhatsApp' END AS grApplication,
        IF(themeId > 0,(SELECT title FROM theme WHERE id = themeId),
        CASE WHEN location = 1 THEN 'Home Page' WHEN location = 2 THEN 'Inner Page' WHEN location = 3 THEN 'Cover' WHEN location = 4 THEN 'Post' WHEN location = 5 THEN 'Story' END ) AS grLocation,
        CASE WHEN template = 1 THEN 'Banner' WHEN template = 2 THEN 'Invitation' 
        WHEN template = 3 THEN 'Greetings' WHEN template = 4 THEN 'Announcements' WHEN template = 5 THEN 'Offers' END AS grTemplates, 
        CASE WHEN STATUS = 0 THEN 'Inactive' WHEN STATUS = 1 THEN 'Active' END AS grStatus,
        CASE WHEN template = 1 THEN (SELECT adzone_name FROM app_adzones WHERE  adzone_id = adzoneId) ELSE '' END AS bannerPosition,graphics_template.createdOn  
        FROM graphics_template INNER JOIN finascop_usr_profile ON UserId = createdBy) as countAd ";
        $listQuery = "SELECT * FROM (SELECT FirstName,templateID,id AS grpTemp_id,title AS grpTemp_title,STATUS AS grpTemp_status,themeId,
        application AS grpTemp_application,location AS grpTemp_location,template AS grpTemp_type,adzoneId AS grpTemp_adzones,
        CASE WHEN application = 1 THEN 'Website'  WHEN application = 2 THEN 'Mobile App' WHEN application = 3 THEN 'Facebook' 
        WHEN application = 4 THEN 'Instagram' WHEN application = 5 THEN 'WhatsApp' END AS grApplication,
        IF(themeId > 0,(SELECT title FROM theme WHERE id = themeId),
        CASE WHEN location = 1 THEN 'Home Page' WHEN location = 2 THEN 'Inner Page' WHEN location = 3 THEN 'Cover' WHEN location = 4 THEN 'Post' WHEN location = 5 THEN 'Story' END ) AS grLocation,
        CASE WHEN template = 1 THEN 'Banner' WHEN template = 2 THEN 'Invitation' 
        WHEN template = 3 THEN 'Greetings' WHEN template = 4 THEN 'Announcements' WHEN template = 5 THEN 'Offers' END AS grTemplates, 
        CASE WHEN STATUS = 0 THEN 'Inactive' WHEN STATUS = 1 THEN 'Active' END AS grStatus,
        CASE WHEN template = 1 THEN (SELECT adzone_name FROM app_adzones WHERE  adzone_id = adzoneId) ELSE '' END AS bannerPosition,graphics_template.createdOn  
        FROM graphics_template INNER JOIN finascop_usr_profile ON UserId = createdBy) as listad  {$search}   ORDER BY {$sort} {$dir} limit $start,$limit";
        //templateID,grpTemp_id,grpTemp_title,grpTemp_status,grpTemp_application,grpTemp_location,grpTemp_type,grpTemp_adzones,grApplication,grLocation,grTemplates,grStatus,bannerPosition
        $db->printGridJson($countQuery, $listQuery);
        break;


    case 'advName':
        $adzone_mode = $_POST['applicationId'];
        $cond = "";
        $themeId = $_POST['themeId'];
        if ($themeId > 0) {
            $cond = " AND adzone_theme = {$themeId} ";
        }
        $listQuery = "SELECT adzone_id AS ad_id,adzone_name,adzone_width,adzone_height from app_adzones WHERE adzone_status =1 AND adzone_mode = {$adzone_mode} {$cond} ";

        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'saveGraphicsTemplate':
        $db->query('begin');
        $edit_status = $_POST['edit_status'];

        $data = array(
            "application" => $_POST['application'],
            "location" => $_POST['location'],
            "themeId" => $_POST['themeId'],
            "template" => $_POST['template'],
            "adzoneId" => $_POST['adzoneId'],
            "designUrl" => $_POST['designUrl'],
            "templateUrl " => $_POST['templateUrl'],
            "status " => $_POST['status']
        );
        $data = array_filter($data);
        if ($edit_status == 1) {
            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $_SESSION['admin']->UserId;
            $adzone_name = $db->perform("graphics_template", $data, 'update', 'id = ' . intval($_POST['id']));
            $lastId = $_POST['id'];
        } else {
            unset($data['id']);
            $data['createdOn'] = date('Y-m-d H:i:s');
            $data['createdBy'] = $_SESSION['admin']->UserId;
            $adzone_name = $db->perform('graphics_template', $data);
            $lastId = $db->insert_id();
            if ($_POST['location'] > 0)
                $middleName = $_POST['locationName'];
            else
                $middleName = $_POST['themeName'];
            $templateID['templateID'] = $_POST['applicationName'] . '_' . $middleName . '_' . $_POST['templateName'] . '_' . $lastId;
            $templateID['title'] = $templateID['templateID'];
            $adzone_name = $db->perform("graphics_template", $templateID, 'update', 'id =' . $lastId);
        }



        $return_rec = $db->getFromDb("SELECT id,title,application,location,template,adzoneId,STATUS,designUrl,templateUrl  FROM graphics_template aa  WHERE id = {$lastId}", true);
        $adzone_name = $db->query('commit');
        if ($adzone_name) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'graphicsTemplateDetailsView':
        $grpTemp_id = isset($_POST['grpTemp_id']) ? intval($_POST['grpTemp_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($grpTemp_id || $ID) {

            $data = $db->getFromDB("SELECT templateID,id AS grpTemp_id,title AS grpTemp_title,STATUS AS grpTemp_status,themeId,
            application AS grpTemp_application,location AS grpTemp_location,template AS grpTemp_type,adzoneId AS grpTemp_adzones,
            CASE WHEN application = 1 THEN 'Website'  WHEN application = 2 THEN 'Mobile App' WHEN application = 3 THEN 'Facebook' 
            WHEN application = 4 THEN 'Instagram' WHEN application = 5 THEN 'WhatsApp' END AS grApplication,
            IF(themeId > 0,(SELECT title FROM theme WHERE id = themeId),
        CASE WHEN location = 1 THEN 'Home Page' WHEN location = 2 THEN 'Inner Page' WHEN location = 3 THEN 'Cover' WHEN location = 4 THEN 'Post' WHEN location = 5 THEN 'Story' END ) AS grLocation,CASE WHEN template = 1 THEN 'Banner' WHEN template = 2 THEN 'Invitation' 
            WHEN template = 3 THEN 'Greetings' WHEN template = 4 THEN 'Announcements' WHEN template = 5 THEN 'Offers' END AS grTemplates, 
            CASE WHEN STATUS = 0 THEN 'Inactive' WHEN STATUS = 1 THEN 'Active' END AS grStatus,designUrl,templateUrl,
            CASE WHEN template = 1 THEN (SELECT adzone_name FROM app_adzones WHERE  adzone_id = adzoneId) ELSE '' END AS bannerPosition  
            FROM graphics_template WHERE ID=" . $grpTemp_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'graphicsTemplate_load':
        $grpTemp_id = isset($_POST['grpTemp_id']) ? intval($_POST['grpTemp_id']) : 0;
        if ($grpTemp_id) {
            $sql = "SELECT id AS grpTemp_id,title AS grpTemp_title,STATUS AS grpTemp_status, application AS grpTemp_application,designUrl,templateUrl,location AS grpTemp_location,
            CASE WHEN location = 1 THEN 'Home Page' WHEN location = 2 THEN 'Inner Page' WHEN location = 3 THEN 'Cover' WHEN location = 4 THEN 'Post' WHEN location = 5 THEN 'Story' END AS grLocation,
            themeId as grpTemp_theme,template AS grpTemp_type,adzoneId AS grpTemp_adzones FROM graphics_template  WHERE id = " . $grpTemp_id;
            $results = $db->getFromDB($sql, true);
            if ($results['grpTemp_type'] == 1) {
                $results['adzone_width'] = $db->getItemFromDB("SELECT adzone_width FROM app_adzones WHERE adzone_id = {$results['grpTemp_adzones']}");
                $results['adzone_height'] = $db->getItemFromDB("SELECT adzone_height FROM app_adzones WHERE adzone_id = {$results['grpTemp_adzones']}");
            } else {
                $results['adzone_width'] = $db->getItemFromDB("SELECT width FROM graphics_template_settings WHERE applicationId = {$results['grpTemp_application']} AND locationId = {$results['grpTemp_location']}");
                $results['adzone_height'] = $db->getItemFromDB("SELECT HEIGHT FROM graphics_template_settings WHERE applicationId = {$results['grpTemp_application']} AND locationId = {$results['grpTemp_location']}");
            }
            if (!empty($results)) {
                echo '{"success":true, "data":', json_encode($results), '}';
            } else {
                echo '{"success":true,"data":[]}';
            }
        }
        break;
    case 'get_img_s3_details':
        $rid = $_POST['rid'];
        $data['grzBucketName'] = AWSBUCKETNAME;
        $data['accessKey'] = AWSS3ASSETUPLOADACCESSID;
        $data['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
        $data['bucketRegion'] = AWSS3ASSETUPLOADREGION;
        $data['oncompleteurl'] = 'graphics/';
        if ($rid) {
            $data['img_path_db'] = $db->getItemFromDB("select designUrl from graphics_template where `id`= {$rid}");
        } else {
            $data['img_path_db'] = '';
        }
        echo "{success : true,'data':" . json_encode($data) . "}";
        break;


    case 'getAdImage':
        $grpTemp_id = $_POST['grpTemp_id'];
        $main_img = $_POST['main_img'];
        $qry = "select id,designUrl,templateUrl from graphics_template where `id`= {$grpTemp_id}";
        $data = $db->getMultipleData($qry, true);
        // $result = $db->getMultipleData($stockregId, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            $data = '';
            echo '{"success":true,"data":' . json_encode($data) . '}';
        }

        break;

    case 'deleteGraphTempl':
        $id = $_POST['grpTemp_id'];
        //$status = $_POST['activestatus'];
        $data = array(
            'grpTemp_status' => 2,
            'grpTemp_updatedOn' => date('Y-m-d H:i:s'),
            'grpTemp_updatedBy' => $_SESSION['admin']->UserId
        );
        $db->query('begin');
        $status = $db->perform('graphics_template', $data, 'update', 'grpTemp_id=' . $id);
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Deleted Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'appLocations':
        $applicationId = $_POST['applicationId'];

        $data = $db->getMultipleData("SELECT locationId,locationName,width,height FROM graphics_template_settings WHERE applicationId = {$applicationId}", true);
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
