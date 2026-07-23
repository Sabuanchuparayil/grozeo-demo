<?php
switch ($op) {
    case 'getSupportUnit':
        $qry = "select id AS suId,name AS suName from support_unit INNER JOIN support_type_unit ON support_type_unit.unitId = support_unit.id   WHERE status = 1 AND support_type_unit.typeId = {$_POST['suTypeId']} order by support_unit.name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getSupportType':
        $qry = "select typeId,typeName from support_type  WHERE typeStatus = 1 order by typeName";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'get_file_s3_details':
        $rid = $_POST['rid'];
        $data['albumBucketName'] = AWSBUCKETNAME;
        $data['accessKey'] = AWSS3ASSETUPLOADACCESSID;
        $data['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
        $data['bucketRegion'] = AWSS3ASSETUPLOADREGION;
        $data['folder'] = 'support/';
        if ($data) {
            echo "{success: true,msg:'Saved Successfully','data':" . json_encode($data) . "}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listSupportTickets':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'ticketId' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 AND ticketStage <> 6 and ticketStatus < 4 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Assigned') {
                                $fiterItem = 1;
                                $searchitem .= " and (ticketStatus = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Unassigned') {
                                $fiterItem = 0;
                                $searchitem .= " and (ticketStatus = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (ticketStatus = 1 or ticketStatus =2) ";
                            }
                        }

                        break;
                    case 'date':
                        if ($field['field'] == 'createdDate') {

                            switch ($field['data']['comparison']) {
                                case 'gt':
                                    $searchitem .= " and createdDate > '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'lt':
                                    $searchitem .= " and createdDate < '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'eq':
                                    $searchitem .= " and createdDate = '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
                                default:
                                    $searchitem .= " and createdDate = '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
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

        $countQuery = "SELECT COUNT(*) FROM(SELECT ticketId,ticketNumber,ticketTitle,ticketDescription,ticketStatus,
        (SELECT typeName FROM support_type WHERE typeId = ticketSupTypeId) AS ticketSupTypeName,ticketSupTypeId,
        ticketSuId,(SELECT support_unit.name FROM support_unit WHERE support_unit.id = ticketSuId) AS ticketSuName,CASE WHEN ticketStatus=1 THEN 'Assigned' WHEN ticketStatus =2 THEN 'Unassigned' END AS status,
        CASE WHEN createdFrom=1 THEN 'Back Office' WHEN createdFrom =2 THEN 'Partner' WHEN createdFrom =3 THEN 'BA' END AS createdFrom,createdBy,DATE_FORMAT(createdOn,'%d-%m-%Y %H:%i:%s') AS createdOn,DATE_FORMAT(createdOn,'%d-%m-%Y') AS createdDate,DATE_FORMAT(createdOn,'%H:%i:%s') AS createdTime,
        CASE WHEN createdFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) WHEN createdFrom =2 THEN (SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = createdBy) WHEN createdFrom =3 THEN (SELECT baName FROM business_associate WHERE id = createdBy) END AS ticketOwner,
        ticketAssignedTo,IF(ticketAssignedTo > 0,(SELECT FirstName FROM finascop_usr_profile WHERE UserId = ticketAssignedTo ),'NA') AS ticketAssignedToName,ticketStage,
        name as ticketStageName  FROM support_ticket INNER JOIN support_ticket_stages ON support_ticket_stages.id = ticketStage) AS count  {$search} {$searchitem}";
        $listQuery = "SELECT * FROM (SELECT ticketId,ticketNumber,ticketTitle,ticketDescription,ticketStatus,
        (SELECT typeName FROM support_type WHERE typeId = ticketSupTypeId) AS ticketSupTypeName,ticketSupTypeId,
        ticketSuId,(SELECT support_unit.name FROM support_unit WHERE support_unit.id = ticketSuId) AS ticketSuName,CASE WHEN ticketStatus=1 THEN 'Assigned' WHEN ticketStatus =2 THEN 'Unassigned' END AS status,
        CASE WHEN createdFrom=1 THEN 'Back Office' WHEN createdFrom =2 THEN 'Partner' WHEN createdFrom =3 THEN 'BA' END AS createdFrom,createdBy,DATE_FORMAT(createdOn,'%d-%m-%Y %H:%i:%s') AS createdOn,DATE_FORMAT(createdOn,'%d-%m-%Y') AS createdDate,DATE_FORMAT(createdOn,'%H:%i:%s') AS createdTime,
        CASE WHEN createdFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) WHEN createdFrom =2 THEN (SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = createdBy) WHEN createdFrom =3 THEN (SELECT baName FROM business_associate WHERE id = createdBy) END AS ticketOwner,
        ticketAssignedTo,IF(ticketAssignedTo > 0,(SELECT FirstName FROM finascop_usr_profile WHERE UserId = ticketAssignedTo ),'NA') AS ticketAssignedToName,ticketStage,
        name as ticketStageName  FROM support_ticket INNER JOIN support_ticket_stages ON support_ticket_stages.id = ticketStage) AS listTickets
        " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'supportticketsdetailsView':
        $ticketId = isset($_POST['ticketId']) ? intval($_POST['ticketId']) : 0;
        if ($ticketId) {
            $data = $db->getFromDB("SELECT ticketId,ticketNumber,ticketTitle,ticketDescription,ticketSuId,(SELECT support_unit.name FROM support_unit WHERE support_unit.id = ticketSuId) AS ticketSuName,CASE WHEN ticketStatus=1 THEN 'Assigned' WHEN ticketStatus =2 THEN 'Unassigned' END AS status,
            CASE WHEN createdFrom=1 THEN 'Back Office' WHEN createdFrom =2 THEN 'Partner' WHEN createdFrom =3 THEN 'BA' END AS createdFrom,createdBy,DATE_FORMAT(createdOn,'%d-%m-%Y %H:%i:%s') AS createdOn,DATE_FORMAT(createdOn,'%d-%m-%Y') AS createdDate,
            CASE WHEN createdFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) WHEN createdFrom =2 THEN (SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = createdBy) WHEN createdFrom =3 THEN (SELECT baName FROM business_associate WHERE id = createdBy) END AS ticketOwner,
            (SELECT filepath FROM support_ticket_log WHERE support_ticket_log.ticketId = support_ticket.ticketId AND filepath <> '') as filepath,
            (SELECT filename FROM support_ticket_log WHERE support_ticket_log.ticketId = support_ticket.ticketId AND filename <> '') as files,ticketContactNo,ticketContactName,ticketContactEmail FROM support_ticket WHERE ticketId = " . $ticketId, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'saveSupportTickets':
        //print_r($_POST);exit();
        $bucketname = $_POST['s3_albumBucketName'];
        $filepath = $_POST['s3filepath'];
        $filename = $_POST['s3_filename'];


        $data = $_POST['n'];
        $reqData['ticketTitle'] = $data['ticketTitle'];
        if ($data['ticketSuId'] > 0) {
            $reqData['ticketSuId'] = $data['ticketSuId'];
            $reqData['ticketStatus'] = 1;
            $stLog['ticketStage'] = 2;
        } else {
            $reqData['ticketSuId'] = 0;
            $reqData['ticketStatus'] = 2;
            $stLog['ticketStage'] = 1;
        }
        if (empty($data['ticketContactName'])) {
            echo "{success: false,msg:'Create ticket for a valid customer.'}";
            exit();
        }
        $reqData['ticketContactName'] = $data['ticketContactName'];
        $reqData['ticketContactNo'] = $data['ticketContactNo'];
        if (!empty($data['ticketContactEmail'])) {
            $reqData['ticketContactEmail'] = $data['ticketContactEmail'];
        }
        $reqData['ticketSupTypeId'] = $data['ticketSupTypeId'];
        $reqData['ticketDescription'] = $data['ticketDescription'];
        $reqData['createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $reqData['createdOn'] = date('Y-m-d H:i:s');

        $db->query('begin');
        $sddata['createdDate'] = date('Y-m-d H:i:s');
        $status = $db->perform('support_ticketnumbering', $sddata);
        $ticketNo = $db->insert_id();

        $reqData['ticketNumber'] = 'GRST' . date('ymd') . str_pad($ticketNo, 4, '0', STR_PAD_LEFT);


        $status = $db->perform('support_ticket', $reqData);
        $ticketId = $db->insert_id();

        if (!empty($filepath)) {
            $file = 1;
        } else {
            $file = 0;
        }
        /*if ($file == 1) {
            $filedata = array(
                'ticketId' => $ticketId,
                'bucketname' => $bucketname,
                'filepath' => $filepath,
                'filename' => $filename,
                'createdBy' => $_SESSION['admin']->Finascop_UserId,
                'createdOn' => date('Y-m-d H:i:s')
            );
            $status = $db->perform('support_communication_file', $filedata);
        }*/

        $stLog['ticketId'] = $ticketId;
        $stLog['ticketSupportUnit'] = $reqData['ticketSuId'];
        $stLog['ticketStatus'] = $reqData['ticketStatus'];
        $stLog['ticketRemarks'] = "Ticket Created";
        if ($file == 1) {
            $stLog['filename'] = $filename;
            $stLog['filepath'] = $filepath;
        }
        $stLog['createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $stLog['createdOn'] = date('Y-m-d H:i:s');
        $status = $db->perform('support_ticket_log', $stLog);
        $status = $db->query('commit');

        sendEmail::SupportCommonEmail($ticketId, 1);
        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'checkAvailablity':
        $contactNo = $_POST['contactNo'];
        $supportType = $_POST['supportType'];
        $supportTypeName = $db->getItemFromDB("SELECT typeName FROM support_type WHERE typeId = {$supportType}");
        $data['supportTypeName'] = $supportTypeName;
        switch ($supportTypeName) {
            case 'Customer':
                $data = $db->getFromDB("SELECT cust_mobile as mobile,cust_email as email,cust_customer_name as cname FROM retaline_customer WHERE cust_mobile = '{$contactNo}'", true);
                break;
            case 'Retailer':
                $fields['phone'] = $contactNo;
                $store_group = $db->getFromDB("SELECT store_group_id,store_group_name,siteUrl,
                IF(store_group_grosmartMerchant = 1,'YES','NO') AS store_group_grosmartMerchant,1 as isCustomer,br_Phone,br_Email,contactNumber 
                FROM finascop_branch_group INNER JOIN finascop_branch ON br_storeGroup = store_group_id  WHERE br_Phone LIKE '%{$contactNo}%' OR contactNumber LIKE '%{$contactNo}%' LIMIT 1",true);
                
                if ($store_group['store_group_id'] > 0) {
                    $data['cname'] = $store_group['store_group_name'];
                    $data['mobile'] =  $store_group['contactNumber'];
                    $data['email'] =  $store_group['br_Email'];
                }

                /*$url =  $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'MERCHANT_DETAILS'");
                $fields_string = json_encode($fields);
                $opts = array(
                    CURLOPT_URL => $url,
                    CURLINFO_CONTENT_TYPE => "application/json",
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_BINARYTRANSFER => TRUE,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_POST => count($fields),
                    CURLOPT_POSTFIELDS => $fields_string,
                    CURLOPT_HTTPHEADER => array('Content-Type: application/json')
                );

                $ch = curl_init();
                curl_setopt_array($ch, $opts);
                $datacl = curl_exec($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);

                header("Content-Type: application/json");
                $result = json_decode($datacl, true);
                if ($result['result'] == 1 && $result['status'] == 'Success') {
                    $data['cname'] = $result['data'][0]['FullName'];
                    $data['mobile'] =  $result['data'][0]['Mobile'];
                    $data['email'] =  $result['data'][0]['Email'];
                }*/
                break;
            case 'Associate':
                $data = $db->getFromDB("SELECT baName as cname,baPhone as mobile,baEmail as email FROM business_associate WHERE baMobileNo = '{$contactNo}'", true);
                break;
            case 'Delivery Person':
                $data = $db->getFromDB("SELECT d_Name as cname,d_Ph1 as mobile,emp_email_id as email FROM qugeo_driver WHERE d_Ph1 = '{$contactNo}'", true);
                break;
            case 'Order Picker':
                $data = $db->getFromDB("SELECT CONCAT(name,' ',lname) as cname,phone as mobile,emp_email_id as email FROM retaline_godown_boy WHERE phone = '{$contactNo}'", true);
                break;
        }

        if (!empty($data)) {
            $data['success'] = true;
        } else {
            if ($supportTypeName != 'Public') {
                $data['success'] = false;
            } else {
                $data['success'] = true;
            }
        }
        echo json_encode($data);
        break;
    case 'assignSupportUnit':
        $ticketId = $_POST['ticketId'];
        $supportUnitId = $_POST['supportUnitId'];

        $reqData['ticketSuId'] = $supportUnitId;
        $reqData['ticketStatus'] = 1;
        $reqData['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $reqData['updatedOn'] = date('Y-m-d H:i:s');

        $db->query('begin');
        $status = $db->perform('support_ticket', $reqData, 'update', " ticketId = {$ticketId}");

        $stLog['ticketId'] = $ticketId;
        $stLog['ticketSupportUnit'] = $supportUnitId;
        $stLog['ticketStatus'] = $reqData['ticketStatus'];
        $stLog['ticketRemarks'] = "Unassigned ticket get assigned";
        $stLog['createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $stLog['createdOn'] = date('Y-m-d H:i:s');
        $status = $db->perform('support_ticket_log', $stLog);
        $status = $db->query('commit');


        if ($status) {
            echo "{success: true,msg:'Ticket Assigned.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listAvailableSupportUnits':
        $rec_limit = empty($_POST['limit']) ? 500 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($data['sort']) ? 'support_unit.id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = " WHERE 1=1 and status = 1 and support_unit.id NOT IN (SELECT supportUnitId FROM user_support_unit WHERE UserId = " . intval($_POST['userId']) . ") ";

        $data = $_POST;

        if (isset($data['filter'])) {
        $allowedFields = ['ticket_id', 'ticket_subject', 'ticket_status', 'ticket_created_on', 'ticket_priority', 'customer_name'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(support_unit.id) from support_unit left join user_support_unit on supportUnitId = support_unit.id  {$filter_part} ORDER BY $rec_sort $rec_sort_dir ";
        $listQuery = "SELECT id AS suId,name AS suName FROM support_unit left join user_support_unit on supportUnitId = support_unit.id  {$filter_part}  group by support_unit.id ORDER BY $rec_sort $rec_sort_dir  LIMIT $rec_start,$rec_limit ";
        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'listUserMappedSupportUnits':
        $rec_sort = empty($data['sort']) ? 'suId' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = " WHERE 1=1 and UserId = {$_POST['userId']}";

        $data = $_POST;

        if (isset($data['filter'])) {
        $allowedFields = ['ticket_id', 'ticket_subject', 'ticket_status', 'ticket_created_on', 'ticket_priority', 'customer_name'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(support_unit.id) from support_unit inner join user_support_unit on supportUnitId = support_unit.id  {$filter_part}";
        $listQuery = "SELECT id AS suId,name AS suName FROM support_unit inner join user_support_unit on supportUnitId = support_unit.id  {$filter_part}  ORDER BY $rec_sort $rec_sort_dir ";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'mapSupportUnitsToUser':
        $brandarr = $_POST['brandarr'];
        $userId = $_POST['userId'];
        $itemdecode = json_decode($brandarr);
        $itemcount = count($itemdecode);
        for ($i = 0; $i < $itemcount; $i++) {
            $entryCount = $db->getItemFromDB("SELECT COUNT(*) FROM user_support_unit WHERE supportUnitId = {$itemdecode[$i]} AND UserId = {$userId}");
            if ($entryCount == 0) {
                $brndMapData["supportUnitId"] = $itemdecode[$i];
                $brndMapData["UserId"] = $userId;
                $status = $db->perform('user_support_unit', $brndMapData);
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'removeSupportUnitFromUser':
        $supportUnitId = $_POST['supportUnitId'];
        $userId = $_POST['userId'];
        $db->query('begin');
        $logdata['userId'] = $userId;
        $logdata['supportUnitId'] = $supportUnitId;
        $status = $db->perform('user_su_remove_log', $logdata);
        $delqry = "DELETE FROM user_support_unit WHERE supportUnitId = {$supportUnitId} AND UserId = {$userId}";
        $status = $db->query($delqry);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Support Unit removed from user'}";
        } else {
            echo "{success: false,msg: 'Reconciliation Successful'}";
        }
        break;
    case 'listAssignedTickets':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'ticketId' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1  AND isAssigned = 0 AND ticketStatus = 1 AND ticketStage <> 6 ";
        if ($_SESSION['admin']->IsSuperUser != 'Yes') {
            $escalatedTickets = $db->getItemFromDB("SELECT GROUP_CONCAT(DISTINCT(ticketId)) FROM support_ticket_log WHERE ticketStage = 7 AND createdBy = {$_SESSION['admin']->Finascop_UserId}");
            $usercond = " AND UserId = {$_SESSION['admin']->Finascop_UserId}";
            if (!empty($escalatedTickets))
                $search .= " AND ticketId NOT IN ({$escalatedTickets}) ";
        } else {
            $usercond = "";
            $search .= "";
        }

        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Assigned') {
                                $fiterItem = 1;
                                $searchitem .= " and (ticketStatus = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Unassigned') {
                                $fiterItem = 0;
                                $searchitem .= " and (ticketStatus = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (ticketStatus = 1 or ticketStatus =2) ";
                            }
                        }

                        break;
                    case 'date':
                        if ($field['field'] == 'createdDate') {

                            switch ($field['data']['comparison']) {
                                case 'gt':
                                    $searchitem .= " and createdDate > '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'lt':
                                    $searchitem .= " and createdDate < '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'eq':
                                    $searchitem .= " and createdDate = '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
                                default:
                                    $searchitem .= " and createdDate = '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
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

        $countQuery = "SELECT COUNT(*) FROM (SELECT UserId,ticketId,ticketNumber,ticketTitle,ticketDescription,ticketStatus,ticketSupTypeId,isAssigned,ticketStage,
        (SELECT typeName FROM support_type WHERE typeId = ticketSupTypeId) AS ticketSupTypeName,
        ticketSuId,(SELECT support_unit.name FROM support_unit WHERE support_unit.id = ticketSuId) AS ticketSuName,CASE WHEN ticketStatus=1 THEN 'Assigned' WHEN ticketStatus =2 THEN 'Unassigned' END AS status,
        CASE WHEN createdFrom=1 THEN 'Back Office' WHEN createdFrom =2 THEN 'BA' WHEN createdFrom =3 THEN 'RO' END AS createdFrom,createdBy,DATE_FORMAT(createdOn,'%d-%m-%Y %H:%i:%s') AS createdOn,DATE_FORMAT(createdOn,'%d-%m-%Y') AS createdDate,DATE_FORMAT(createdOn,'%H:%i:%s') AS createdTime,
        CASE WHEN createdFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) WHEN createdFrom =2 THEN (SELECT baName FROM business_associate WHERE id = createdBy) WHEN createdFrom =3 THEN (SELECT roName FROM relationship_officer WHERE id = createdBy) END AS ticketOwner,
        ticketAssignedTo,IF(ticketAssignedTo > 0,(SELECT FirstName FROM finascop_usr_profile WHERE UserId = ticketAssignedTo ),'NA') AS ticketAssignedToName,name as ticketStageName
         FROM support_ticket INNER JOIN support_ticket_stages ON support_ticket_stages.id = ticketStage
        LEFT JOIN user_support_unit on supportUnitId = ticketSuId {$usercond}) AS count  {$search} ";
        $listQuery = "SELECT * FROM (SELECT UserId,ticketId,ticketNumber,ticketTitle,ticketDescription,ticketStatus,ticketSupTypeId,isAssigned,ticketStage,
        (SELECT typeName FROM support_type WHERE typeId = ticketSupTypeId) AS ticketSupTypeName,
        ticketSuId,(SELECT support_unit.name FROM support_unit WHERE support_unit.id = ticketSuId) AS ticketSuName,CASE WHEN ticketStatus=1 THEN 'Assigned' WHEN ticketStatus =2 THEN 'Unassigned' END AS status,
        CASE WHEN createdFrom=1 THEN 'Back Office' WHEN createdFrom =2 THEN 'BA' WHEN createdFrom =3 THEN 'RO' END AS createdFrom,createdBy,DATE_FORMAT(createdOn,'%d-%m-%Y %H:%i:%s') AS createdOn,DATE_FORMAT(createdOn,'%d-%m-%Y') AS createdDate,DATE_FORMAT(createdOn,'%H:%i:%s') AS createdTime,
        CASE WHEN createdFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) WHEN createdFrom =2 THEN (SELECT baName FROM business_associate WHERE id = createdBy) WHEN createdFrom =3 THEN (SELECT roName FROM relationship_officer WHERE id = createdBy) END AS ticketOwner,
        ticketAssignedTo,IF(ticketAssignedTo > 0,(SELECT FirstName FROM finascop_usr_profile WHERE UserId = ticketAssignedTo ),'NA') AS ticketAssignedToName,name as ticketStageName
         FROM support_ticket INNER JOIN support_ticket_stages ON support_ticket_stages.id = ticketStage
        LEFT JOIN user_support_unit on supportUnitId = ticketSuId {$usercond} GROUP BY ticketId) AS listAssignTicket 
        " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'acceptSupportUnit':
        $ticketId = $_POST['ticketId'];
        $checkAlreadyAssigned = $db->getItemFromDB("SELECT COUNT(*) FROM support_ticket WHERE isAssigned = 1 and ticketId = {$ticketId}");
        if ($checkAlreadyAssigned > 0) {
            echo "{success: false,msg:'Ticket already Assigned.'}";
            exit();
        }
        $userAcceptCount = $db->getItemFromDB("SELECT COUNT(*) FROM support_ticket WHERE isAssigned = 1 AND ticketAssignedTo = {$_SESSION['admin']->Finascop_UserId} AND ticketStage = 3 ");
        if ($userAcceptCount > 0) {
            echo "{success: false,msg:'You are assigned with another ticket IN Accepted Stage.'}";
            exit();
        }
        if ($checkAlreadyAssigned == 0 && $userAcceptCount == 0) {
            $accData['isAssigned'] = 1;
            $accData['ticketAssignedTo'] = $_SESSION['admin']->Finascop_UserId;
            $accData['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $accData['updatedOn'] = date('Y-m-d H:i:s');

            $db->query('begin');
            $status = $db->perform('support_ticket', $accData, 'update', " ticketId = {$ticketId}");

            $stLog['ticketId'] = $ticketId;
            $stLog['ticketSupportUnit'] = $db->getItemFromDB("SELECT ticketSuId FROM support_ticket WHERE ticketId = {$ticketId} ");
            $stLog['ticketStatus'] = 3;
            $stLog['ticketStage'] = 3;
            $stLog['ticketRemarks'] = "Ticket Accepted";
            $stLog['createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $stLog['createdOn'] = date('Y-m-d H:i:s');
            $status = $db->perform('support_ticket_log', $stLog);
            $status = $db->query('commit');
            sendEmail::SupportCommonEmail($ticketId, 3);
            if ($status) {
                echo "{success: true,msg:'Ticket Accepted.'}";
            } else {
                echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
            }
        }
        break;
    case 'listMyAssignedTickets':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'ticketId' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1  AND isAssigned = 1 AND ticketStage <> 6 ";
        if ($_SESSION['admin']->IsSuperUser != 'Yes') {
            $search .= " AND ticketAssignedTo = {$_SESSION['admin']->Finascop_UserId}";
        } else {
            $search .= "";
        }

        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Assigned') {
                                $fiterItem = 1;
                                $searchitem .= " and (ticketStatus = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Unassigned') {
                                $fiterItem = 0;
                                $searchitem .= " and (ticketStatus = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (ticketStatus = 1 or ticketStatus =2) ";
                            }
                        }

                        break;
                    case 'date':
                        if ($field['field'] == 'createdDate') {

                            switch ($field['data']['comparison']) {
                                case 'gt':
                                    $searchitem .= " and createdDate > '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'lt':
                                    $searchitem .= " and createdDate < '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'eq':
                                    $searchitem .= " and createdDate = '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
                                default:
                                    $searchitem .= " and createdDate = '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
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

        $countQuery = "SELECT COUNT(*) FROM (SELECT ticketId,ticketNumber,ticketTitle,ticketDescription,ticketStatus,isAssigned,
        (SELECT typeName FROM support_type WHERE typeId = ticketSupTypeId) AS ticketSupTypeName,ticketSupTypeId,
        ticketSuId,(SELECT support_unit.name FROM support_unit WHERE support_unit.id = ticketSuId) AS ticketSuName,
        support_ticket_status.name AS status,support_ticket_stages.name AS ticketStageName,ticketStage,
        CASE WHEN createdFrom=1 THEN 'Back Office' WHEN createdFrom =2 THEN 'BA' WHEN createdFrom =3 THEN 'RO' END AS createdFrom,createdBy,DATE_FORMAT(createdOn,'%d-%m-%Y %H:%i:%s') AS createdOn,DATE_FORMAT(createdOn,'%d-%m-%Y') AS createdDate,DATE_FORMAT(createdOn,'%H:%i:%s') AS createdTime,
        CASE WHEN createdFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) WHEN createdFrom =2 THEN (SELECT baName FROM business_associate WHERE id = createdBy) WHEN createdFrom =3 THEN (SELECT roName FROM relationship_officer WHERE id = createdBy) END AS ticketOwner,
        ticketAssignedTo,IF(ticketAssignedTo > 0,(SELECT FirstName FROM finascop_usr_profile WHERE UserId = ticketAssignedTo ),'NA') AS ticketAssignedToName FROM support_ticket 
        LEFT JOIN user_support_unit on supportUnitId = ticketSuId  
        INNER JOIN support_ticket_status ON support_ticket_status.id = ticketStatus 
        INNER JOIN support_ticket_stages ON support_ticket_stages.id = ticketStage  GROUP BY ticketId) as ticketCount
        " . "{$search}{$searchitem}  ";
        $listQuery = "SELECT * FROM (SELECT ticketId,ticketNumber,ticketTitle,ticketDescription,ticketStatus,isAssigned,
        (SELECT typeName FROM support_type WHERE typeId = ticketSupTypeId) AS ticketSupTypeName,ticketSupTypeId,
        ticketSuId,(SELECT support_unit.name FROM support_unit WHERE support_unit.id = ticketSuId) AS ticketSuName,
        support_ticket_status.name AS status,support_ticket_stages.name AS ticketStageName,ticketStage,
        CASE WHEN createdFrom=1 THEN 'Back Office' WHEN createdFrom =2 THEN 'BA' WHEN createdFrom =3 THEN 'RO' END AS createdFrom,createdBy,DATE_FORMAT(createdOn,'%d-%m-%Y %H:%i:%s') AS createdOn,DATE_FORMAT(createdOn,'%d-%m-%Y') AS createdDate,DATE_FORMAT(createdOn,'%H:%i:%s') AS createdTime,
        CASE WHEN createdFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) WHEN createdFrom =2 THEN (SELECT baName FROM business_associate WHERE id = createdBy) WHEN createdFrom =3 THEN (SELECT roName FROM relationship_officer WHERE id = createdBy) END AS ticketOwner,
        ticketAssignedTo,IF(ticketAssignedTo > 0,(SELECT FirstName FROM finascop_usr_profile WHERE UserId = ticketAssignedTo ),'NA') AS ticketAssignedToName FROM support_ticket 
        LEFT JOIN user_support_unit on supportUnitId = ticketSuId  
        INNER JOIN support_ticket_status ON support_ticket_status.id = ticketStatus 
        INNER JOIN support_ticket_stages ON support_ticket_stages.id = ticketStage  GROUP BY ticketId) AS mytickets
        " . "{$search}{$searchitem}   ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'trasferTicket':
        $ticketId = $_POST['ticketId'];
        $ticketSupportUnit = $_POST['ticketSupportUnit'];
        $ticketRemarks = $_POST['ticketRemarks'];

        $currentticketSuId = $db->getItemFromDB("SELECT ticketSuId FROM support_ticket WHERE ticketId = {$ticketId} ");
        if ($ticketSupportUnit != $currentticketSuId) {
            $reqData['ticketSuId'] = $ticketSupportUnit;
            $reqData['ticketStage'] = 4;
            $reqData['ticketStatus'] = 1;
            $reqData['isAssigned'] = 0;
            $reqData['ticketAssignedTo'] = 0;
            $reqData['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $reqData['updatedOn'] = date('Y-m-d H:i:s');

            $db->query('begin');
            $status = $db->perform('support_ticket', $reqData, 'update', " ticketId = {$ticketId}");

            $stLog['ticketId'] = $ticketId;
            $stLog['ticketSupportUnit'] = $ticketSupportUnit;
            $stLog['ticketStage'] = $reqData['ticketStage'];
            $stLog['ticketStatus'] = $reqData['ticketStatus'];
            $stLog['ticketRemarks'] = $ticketRemarks;
            $stLog['createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $stLog['createdOn'] = date('Y-m-d H:i:s');
            $status = $db->perform('support_ticket_log', $stLog);
            $status = $db->query('commit');
            if ($status) {
                echo "{success: true,msg:'Ticket Assigned.'}";
            } else {
                echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
            }
        } else {
            echo "{success: false, msg: 'Ticket is already under the same unit' }";
        }

        break;
    case 'ticketActions':
        $ticketId = $_POST['ticketId'];
        $ticketAction = $_POST['ticketAction'];
        $ticketRemarks = $_POST['ticketRemarks'];
        $currentticketSuId = $db->getItemFromDB("SELECT ticketSuId FROM support_ticket WHERE ticketId = {$ticketId} ");
        switch ($ticketAction) {
            case 5: //Feedback Requested
                $reqData['isAssigned'] = 0;
                $reqData['ticketAssignedTo'] = 0;
                break;
            case 6: //Resolve

                break;
            case 7: //Escalate
                $reqData['isAssigned'] = 0;
                $reqData['ticketAssignedTo'] = 0;
                break;
            case 8: //Skip
                $skipCount = $db->getItemFromDB("SELECT COUNT(*) FROM support_ticket WHERE ticketId = {$ticketId} AND ticketStage = 8 AND ticketAssignedTo = {$_SESSION['admin']->Finascop_UserId} ");
                if ($skipCount == 5) {
                    echo "{success: false,msg:'You already have 5 Skipped tickets.'}";
                    exit();
                }
                break;
        }
        $ticketStageName = $db->getItemFromDB("SELECT name FROM support_ticket_stages WHERE id = {$ticketAction}");
        $reqData['ticketStage'] = $ticketAction;
        $reqData['ticketStatus'] = 1;
        $reqData['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $reqData['updatedOn'] = date('Y-m-d H:i:s');

        $db->query('begin');
        $status = $db->perform('support_ticket', $reqData, 'update', " ticketId = {$ticketId}");

        $stLog['ticketId'] = $ticketId;
        $stLog['ticketSupportUnit'] = $currentticketSuId;
        $stLog['ticketStage'] = $reqData['ticketStage'];
        $stLog['ticketStatus'] = $reqData['ticketStatus'];
        $stLog['ticketRemarks'] = $ticketRemarks;
        $stLog['createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $stLog['createdOn'] = date('Y-m-d H:i:s');
        $status = $db->perform('support_ticket_log', $stLog);
        $status = $db->query('commit');

        switch ($ticketAction) {
            case 5: //Feedback Requested
                sendEmail::SupportCommonEmail($ticketId, 5);
                break;
            case 6: //Resolve
                sendEmail::SupportCommonEmail($ticketId, 4);
                break;
        }
        if ($status) {
            $message = "Ticket stage changed as  - " . $ticketStageName;
            echo "{success: true,msg:'" . $message . "'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listTicketLogs':
        $ticketId = intval($_POST['ticketId']);
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
        $qry = "SELECT support_ticket_log.id,ticketId,ticketType,ticketStatus,ticketStage,ticketRemarks,ticketSupportUnit,createdBy,support_ticket_log.createdOn,
        support_unit.name as suName,support_ticket_status.name AS status,support_ticket_stages.name AS tiketStage,
        CASE WHEN ticketType=1 THEN 'Internal Note' WHEN ticketType =2 THEN 'External Note'  END AS ticketTypeName,
        CONCAT(FirstName,' ',LastName) AS createdByName
        FROM support_ticket_log 
        INNER JOIN support_unit ON support_unit.id = ticketSupportUnit  
        INNER JOIN support_ticket_status ON support_ticket_status.id = ticketStatus 
        INNER JOIN support_ticket_stages ON support_ticket_stages.id = ticketStage
        INNER JOIN finascop_usr_profile ON UserId = createdBy   WHERE ticketId = {$ticketId} {$searchitem} ORDER BY support_ticket_log.id DESC ";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo json_encode([]);
        break;
    case 'listResolvedTickets':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'ticketId' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1  AND ticketStage = 6 and ticketStatus < 4";
        

        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Assigned') {
                                $fiterItem = 1;
                                $searchitem .= " and (ticketStatus = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Unassigned') {
                                $fiterItem = 0;
                                $searchitem .= " and (ticketStatus = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (ticketStatus = 1 or ticketStatus =2) ";
                            }
                        }

                        break;
                    case 'date':
                        if ($field['field'] == 'createdDate') {

                            switch ($field['data']['comparison']) {
                                case 'gt':
                                    $searchitem .= " and createdDate > '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'lt':
                                    $searchitem .= " and createdDate < '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'eq':
                                    $searchitem .= " and createdDate = '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
                                default:
                                    $searchitem .= " and createdDate = '" . date('d-m-Y', strtotime($field['data']['value'])) . "'";
                                    break;
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

        $countQuery = "SELECT COUNT(*) FROM (SELECT ticketId,ticketNumber,ticketTitle,ticketDescription,ticketStatus,isAssigned,
        (SELECT typeName FROM support_type WHERE typeId = ticketSupTypeId) AS ticketSupTypeName,ticketSupTypeId,
        ticketSuId,(SELECT support_unit.name FROM support_unit WHERE support_unit.id = ticketSuId) AS ticketSuName,
        support_ticket_status.name AS status,support_ticket_stages.name AS ticketStageName,ticketStage,
        CASE WHEN createdFrom=1 THEN 'Back Office' WHEN createdFrom =2 THEN 'BA' WHEN createdFrom =3 THEN 'RO' END AS createdFrom,createdBy,DATE_FORMAT(createdOn,'%d-%m-%Y %H:%i:%s') AS createdOn,DATE_FORMAT(createdOn,'%d-%m-%Y') AS createdDate,DATE_FORMAT(createdOn,'%H:%i:%s') AS createdTime,
        CASE WHEN createdFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) WHEN createdFrom =2 THEN (SELECT baName FROM business_associate WHERE id = createdBy) WHEN createdFrom =3 THEN (SELECT roName FROM relationship_officer WHERE id = createdBy) END AS ticketOwner,
        ticketAssignedTo,IF(ticketAssignedTo > 0,(SELECT FirstName FROM finascop_usr_profile WHERE UserId = ticketAssignedTo ),'NA') AS ticketAssignedToName FROM support_ticket 
        LEFT JOIN user_support_unit on supportUnitId = ticketSuId  
        INNER JOIN support_ticket_status ON support_ticket_status.id = ticketStatus 
        INNER JOIN support_ticket_stages ON support_ticket_stages.id = ticketStage  
         GROUP BY ticketId) as ticketCount {$search}{$searchitem} ";
        $listQuery = "SELECT * FROM (SELECT ticketId,ticketNumber,ticketTitle,ticketDescription,ticketStatus,isAssigned,
        (SELECT typeName FROM support_type WHERE typeId = ticketSupTypeId) AS ticketSupTypeName,ticketSupTypeId,
        ticketSuId,(SELECT support_unit.name FROM support_unit WHERE support_unit.id = ticketSuId) AS ticketSuName,
        support_ticket_status.name AS status,support_ticket_stages.name AS ticketStageName,ticketStage,
        CASE WHEN createdFrom=1 THEN 'Back Office' WHEN createdFrom =2 THEN 'BA' WHEN createdFrom =3 THEN 'RO' END AS createdFrom,createdBy,DATE_FORMAT(createdOn,'%d-%m-%Y %H:%i:%s') AS createdOn,DATE_FORMAT(createdOn,'%d-%m-%Y') AS createdDate,DATE_FORMAT(createdOn,'%H:%i:%s') AS createdTime,
        CASE WHEN createdFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) WHEN createdFrom =2 THEN (SELECT baName FROM business_associate WHERE id = createdBy) WHEN createdFrom =3 THEN (SELECT roName FROM relationship_officer WHERE id = createdBy) END AS ticketOwner,
        ticketAssignedTo,IF(ticketAssignedTo > 0,(SELECT FirstName FROM finascop_usr_profile WHERE UserId = ticketAssignedTo ),'NA') AS ticketAssignedToName FROM support_ticket 
        LEFT JOIN user_support_unit on supportUnitId = ticketSuId  
        INNER JOIN support_ticket_status ON support_ticket_status.id = ticketStatus 
        INNER JOIN support_ticket_stages ON support_ticket_stages.id = ticketStage GROUP BY ticketId) AS resolvedTickets  
        " . "{$search}{$searchitem}   ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
}
