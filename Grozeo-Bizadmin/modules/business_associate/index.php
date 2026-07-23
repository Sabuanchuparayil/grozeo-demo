<?php

require_once(ROOT . '/finascop_config/lib.php');
require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(INCLUDE_PATH . "/finascop_wallet_client.php");

function randomPassword()
{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}
switch ($op) {
    case 'EditbusinessAssociateDetails':
        $id = $_POST['baId'];


        $data['location'] = $_POST['baLocation'];
        $data['baName'] = $_POST['baName'];
        $data['baCity'] = $_POST['baCity'];
        $data['baAddress'] = $_POST['baAddress'];
        $data['st_id'] = $_POST['state'];
        $data['dst_Id'] = $_POST['c_district'];
        $data['baPincode'] = $_POST['baPincode'];
        $data['baGSTIN'] = $_POST['baGSTIN'];
        $data['baPanNo'] = $_POST['baPanNo'];
        $data['baContactPerson'] = $_POST['baContactPerson'];
        $data['baMobileNo'] = $_POST['baMobileNo'];
        $data['baEmail'] = $_POST['baEmail'];
        $data['balatitude'] = $_POST['balatitude'];
        $data['balongitude'] = $_POST['balongitude'];
        $data['baType'] = $_POST['baType'];
        $data['baMode'] = $_POST['baMode'];
        $data['baIsPartner'] = (!empty($_POST['baIsPartner']) ? $_POST['baIsPartner'] : 0);
        $data['bpId'] = ($_POST['bptnrId'] > 0 ? $_POST['bptnrId'] : 0);
        $data['networkType'] = ($_POST['bptnrId'] > 0 ? $_POST['areaTypeId'] : -1);
        $data['type'] = $_POST['businessType'];
        $data['status'] = ($_POST['baStatus'] = 'Active' ? 1 : 0);

        $IsUnique = $db->getItemSafe("SELECT COUNT(*) from business_associate WHERE baName = ? AND id <> '{$_POST['baId']}'", "s", [$_POST['baName']]);
        if ($IsUnique > 0) {
            echo "{success: false,msg:'Name already existing.'}";
            exit;
        }
        if ($id > 0) {
            $data['baUpdatedOn'] = date('Y-m-d H:i:s');
            $data['baUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform("business_associate", $data, 'update', " id = {$id}");

            if ($status == 1) {
                echo "{success: true,msg:'Saved Successfully'}";
            } else {
                echo "{success: false,msg: 'Error occured while saving data'}";
            }
        } else {
            $temporary_password = randomPassword();
            $city = $_POST['baCity'];
            $state = $db->getItemSafe("SELECT st_name FROM finascop_state WHERE st_ID = ?", "s", [$_POST['state']]);
            $country = $db->getItemFromDB("SELECT country_name FROM retaline_country WHERE is_default = 1");

            $refid = $db->getItemFromDB("SELECT UUID() AS uuid");
            $data['refid'] = $refid;
            $data['temporary_password'] = $temporary_password;
            $data['baCreatedOn'] = date('Y-m-d H:i:s');;
            $data['baCreatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform("business_associate", $data);
            if ($status == 1) {
                echo "{success: true,msg:'Saved Successfully'}";
            } else {
                echo "{success: false,msg: 'Error occured while saving data'}";
            }
        }

        break;
    case 'listBusinessAssociate':
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $filter_query = ' 1=1 AND userType  = 3 ';
        $br_id = $_SESSION['admin']->finascop_current_branch_id;

        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }


        $qry = "select id as baId, userType,baName,baPhone,baAddress,baCity,baPincode,baGSTIN,br_id,baContactPerson,baMobileNo,baEmail,baPanNo,balatitude,balongitude,"
            . "(select st_name from finascop_state b inner join  finascop_district d on b.st_ID = d.st_Id where d.dst_Id = a.dst_Id)as st_name,"
            . "(select b.st_ID from finascop_state b inner join  finascop_district d on b.st_ID = d.st_Id where d.dst_Id = a.dst_Id)as st_id,"
            . "(select c.dst_Id from  finascop_district c where c.dst_Id = a.dst_Id )as dst_Id,"
            . "(select dst_Name from  finascop_district c where c.dst_Id = a.dst_Id )as dst_Name,baMode,baType,
            CASE WHEN baType = 1 THEN 'Area' WHEN baType = 2 THEN 'Market' END AS baTypeName,
            CASE WHEN baMode = 1 THEN 'Direct' WHEN baMode = 2 THEN 'Network' END AS baModeName,
            bpId,(SELECT bpName FROM  business_partner bp WHERE bp.id = a.bpId ) AS bpName,location,
            type,CASE WHEN type = 1 THEN 'Company' WHEN type = 2 THEN 'LLP' WHEN type = 3 THEN 'Firm' WHEN type = 4 THEN 'Trust' WHEN type = 5 THEN 'Proprietorship' END AS businessType,
            CASE WHEN status = 1 THEN 'Active' WHEN baType = 0 THEN 'Inactive' END AS baStatus,status   from   business_associate a  ";

        $listQry = "SELECT * FROM ({$qry}) AS listba where  {$filter_query} order by baId asc limit {$rec_start},{$rec_limit}";
        $data = $db->getMultipleData($listQry, true);

        $countQuery = "SELECT COUNT(*) FROM  ({$qry}) as countba where {$filter_query} ";
        $count = $db->getItemFromDB($countQuery);


        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }

        break;
    case 'getStates':
        $defaultCountry = $db->getItemFromDB("SELECT country_id FROM retaline_country WHERE is_default = 1");

        $qry = "select st_ID,st_name from finascop_state WHERE cnt_ID = {$defaultCountry} order by st_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        // echo '{success:true, data:'.json_encode($data).'}';
        break;
    case 'getDistrict':
        $state = $_POST['st_Id'];
        $qry = "select dst_ID,dst_Name from finascop_district where st_Id = '$state'  order by dst_Name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        // echo '{success:true, data:'.json_encode($data).'}';
        break;
    case 'AreadetailsView':

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id) {
            $data = $db->getFromDB("SELECT  id,areaName,areaLocation,areaSpan,areaLatitude,areaLongitude,
            areaBusinessAssociate,areaState,areaDistrict,areaLockedTill,areaLockedFor,divisionId,
            (SELECT  parentId FROM division WHERE id = divisionId) as areaTerritory,
            (SELECT  name FROM division WHERE id = (SELECT  parentId FROM division WHERE id = divisionId)) as areaTerritoryName,
            (SELECT st_name FROM finascop_state WHERE st_ID = areaState) AS st_name,
            (SELECT dst_Name FROM  finascop_district WHERE dst_Id = areaDistrict) AS dst_Name,
            (SELECT baName FROM business_associate WHERE business_associate.id = areaBusinessAssociate) as areaBusinessAssociateName 
            FROM area_entries WHERE id= {$id}", true);

            require(THIS_MODULE_PATH . "/baView.php");
        }
        break;
    case 'saveArea':
        $id = $_POST['id'];
        $db->query('begin');
        $data['areaName'] = $_POST['areaName'];
        $data['areaLocation'] = $_POST['areaLocation'];
        $data['areaSpan'] = $_POST['areaSpan'];
        $data['areaLatitude'] = $_POST['areaLatitude'];
        $data['areaLongitude'] = $_POST['areaLongitude'];
        $data['areaState'] = $_POST['areaState'];
        $data['areaDistrict'] = $_POST['areaDistrict'];
        $data['divisionId'] = $_POST['divisionId'];

        $divisionData['parentId'] = $_POST['areaTerritory'];
        $divisionData['typeId'] = 5;
        $divisionData['name'] = $_POST['areaName'];
        //$data['areaBusinessAssociate'] = $_POST['areaBusinessAssociate'];
        if ($_POST['areaBusinessAssociate'] > 0) {
            $areaAssociateType = $db->getItemSafe("SELECT baType from business_associate WHERE business_associate.id = = ?", "s", [$_POST['areaBusinessAssociate']]);

            $areaAssociateCount = 1;
            $marketAssociateCount = 3;

            $hasBaMapped = $db->getItemSafe("SELECT COUNT(*) from area_entries WHERE areaBusinessAssociate = ? AND id <> '{$_POST['id']}'", "s", [$_POST['areaBusinessAssociate']]);
            switch ($areaAssociateType) {
                case 1:
                    if ($hasBaMapped > 0) {
                        echo "{success: false,msg:'Bussiness associate already mapped to {$areaAssociateCount} area.'}";
                        exit;
                    }
                    break;
                case 2:
                    if ($hasBaMapped > 2) {
                        echo "{success: false,msg:'Bussiness associate already mapped to {$marketAssociateCount} areas.'}";
                        exit;
                    }
                    break;
            }
        }

        if ($data['divisionId'] > 0) {
            $areaDivUnique = $db->getItemFromDB("SELECT COUNT(*) from division WHERE name ='{$divisionData['name']}' AND parentId = {$divisionData['parentId']} AND  id  <> {$data['divisionId']}");
            if ($areaUnique > 0) {
                echo "{success: false,msg:'Area already existing.'}";
                exit;
            }
            $divisionData['updatedOn'] = date('Y-m-d H:i:s');
            $divisionData['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform("division", $divisionData, 'update', " id = {$data['divisionId']}");
            $divisionId = $data['divisionId'];
        } else {
            if ($divisionData['parentId'] > 0) {
                $areaDivUnique = $db->getItemFromDB("SELECT COUNT(*) from division WHERE name ='{$divisionData['name']}' AND parentId = {$divisionData['parentId']} ");
                if ($areaUnique > 0) {
                    echo "{success: false,msg:'Area already existing.'}";
                    exit;
                }
                $divisionData['createdOn'] = date('Y-m-d H:i:s');
                $divisionData['createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform("division", $divisionData);
                $divisionId = $db->insert_id();
            } else {
                $divisionId = 0;
            }
        }
        $data['divisionId'] = $divisionId;
        if ($id > 0) {
            $areaUnique = $db->getItemFromDB("SELECT COUNT(*) from area_entries WHERE areaName = '{$data['areaName']}' AND  id  <> {$id}");
            if ($areaUnique > 0) {
                echo "{success: false,msg:'Name already existing.'}";
                exit;
            }
            $data['areaUpdatedOn'] = date('Y-m-d H:i:s');
            $data['areaUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform("area_entries", $data, 'update', " id = {$id}");
        } else {
            $areaUnique = $db->getItemFromDB("SELECT COUNT(*) from area_entries WHERE areaName = '{$data['areaName']}' ");
            if ($areaUnique > 0) {
                echo "{success: false,msg:'Name already existing.'}";
                exit;
            }
            $data['areaCreatedOn'] = date('Y-m-d H:i:s');
            $data['areaCreatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform("area_entries", $data);
            $id = $db->insert_id();
        }

        $areaLat = $data['areaLatitude'];
        $areaLng = $data['areaLongitude'];
        $radiusKm = $data['areaSpan']; // 10-km area
        $newAreaId = $id;

        $earthRadius = 6371; // km
        $latDelta = $radiusKm / $earthRadius;
        $lngDelta = $radiusKm / ($earthRadius * cos(deg2rad($areaLat)));

        $minLat = $areaLat - rad2deg($latDelta);
        $maxLat = $areaLat + rad2deg($latDelta);
        $minLng = $areaLng - rad2deg($lngDelta);
        $maxLng = $areaLng + rad2deg($lngDelta);

        $upareasql = "UPDATE finascop_branch
SET areaId = {$newAreaId}
WHERE (areaId IS NULL OR areaId = 0)
  -- ① Bounding Box Filter (fast index-based)
  AND br_Lat BETWEEN {$minLat} AND {$maxLat}
  AND br_Lng BETWEEN {$minLng} AND {$maxLng}
  -- ② Exact Circular Distance Filter
  AND (
    6371 * ACOS(
      COS(RADIANS({$areaLat})) * COS(RADIANS(br_Lat)) *
      COS(RADIANS(br_Lng) - RADIANS({$areaLng})) +
      SIN(RADIANS({$areaLat})) * SIN(RADIANS(br_Lat))
    )
  ) <= {$radiusKm}";
        $status = $db->query($upareasql);
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,valid: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data'}";
        }
        break;
    case 'getbusinessAssociate':
        $qry = "SELECT id,baName FROM business_associate";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo [];
        break;
    case 'editFormDataLoad':
        $cust_id = $_POST['baId'];
        if ($cust_id != 0) {

            $qry = "SELECT id AS baId, location AS baLocation,baName,baPhone,baAddress,baCity,baPincode,baGSTIN,dst_Id,br_id,baContactPerson,baMobileNo,baEmail,baPanNo,balatitude,balongitude,
        (SELECT st_name FROM finascop_state b INNER JOIN  finascop_district d ON b.st_ID = d.st_Id WHERE d.dst_Id = a.dst_Id)AS st_name,
        (SELECT b.st_ID FROM finascop_state b INNER JOIN finascop_district d ON b.st_ID = d.st_Id WHERE d.dst_Id = a.dst_Id)AS st_id,
        (SELECT c.dst_Id FROM  finascop_district c WHERE c.dst_Id = a.dst_Id )AS dst_Id,
        (SELECT dst_Name FROM  finascop_district c WHERE c.dst_Id = a.dst_Id )AS dst_Name,baMode,baType,
            CASE WHEN baType = 1 THEN 'Area' WHEN baType = 1 THEN 'Market' END AS baTypeName,CASE WHEN baMode = 1 THEN 'Direct' WHEN baMode = 1 THEN 'Network' END AS baModeName,
            bpId as bptnrId,CASE WHEN networkType = 0 THEN (SELECT bpName FROM  business_partner bp WHERE bp.id = a.bpId) WHEN networkType = 1 THEN (SELECT baName FROM  business_associate ba WHERE ba.id = a.bpId) END AS bpName,baIsPartner,networkType,
            type,CASE WHEN type = 1 THEN 'Company' WHEN type = 2 THEN 'LLP' WHEN type = 3 THEN 'Firm' WHEN type = 4 THEN 'Trust' WHEN type = 5 THEN 'Proprietorship'END AS businessType,
            CASE WHEN status = 1 THEN 'Active' WHEN baType = 0 THEN 'Inactive' END AS baStatus,status
        FROM  business_associate a WHERE id = {$cust_id}";
            $results = $db->getFromDB($qry, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;
    case 'area_form_load':
        $cust_id = $_POST['id'];
        if ($cust_id != 0) {

            $qry = "SELECT id,areaName,areaLocation,areaSpan,areaLatitude,areaLongitude,
            areaBusinessAssociate,areaState,areaDistrict,divisionId,
            (SELECT  parentId FROM division WHERE id = divisionId) as areaTerritory,
            (SELECT  name FROM division WHERE id = (SELECT  parentId FROM division WHERE id = divisionId)) as areaTerritoryName,
            (SELECT st_name FROM finascop_state WHERE st_ID = areaState) AS st_name,
            (SELECT dst_Name FROM  finascop_district WHERE dst_Id = areaDistrict) AS dst_Name,
            (SELECT baName FROM business_associate WHERE business_associate.id = areaBusinessAssociate) as areaBusinessAssociateName FROM  area_entries a WHERE id = {$cust_id}";
            $results = $db->getFromDB($qry, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;
    case 'listAreas':


        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $searchitem = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        $countQuery = "SELECT COUNT(*) FROM area_entries ae
LEFT JOIN business_associate ba ON ba.id = ae.areaBusinessAssociate
LEFT JOIN division d ON d.id = ae.divisionId
LEFT JOIN division d2 ON d2.id = d.parentId
LEFT JOIN finascop_state fs ON fs.st_ID = ae.areaState
LEFT JOIN finascop_district fd ON fd.dst_Id = ae.areaDistrict {$searchitem}";
        $listQuery = "SELECT 
    ae.id,
    ae.areaName,
    ae.areaLocation,
    ae.areaSpan,
    ae.areaLatitude,
    ae.areaLongitude,
    ae.areaBusinessAssociate,
    ba.baName AS areaBusinessAssociateName,
    ae.divisionId,
    d.parentId AS areaTerritory,
    d2.name AS areaTerritoryName,
    fs.st_name,
    fd.dst_Name
FROM area_entries ae
LEFT JOIN business_associate ba ON ba.id = ae.areaBusinessAssociate
LEFT JOIN division d ON d.id = ae.divisionId
LEFT JOIN division d2 ON d2.id = d.parentId
LEFT JOIN finascop_state fs ON fs.st_ID = ae.areaState
LEFT JOIN finascop_district fd ON fd.dst_Id = ae.areaDistrict {$searchitem} ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'EditBusinessPartnerDetails':

        $id = $_POST['customer_id'];

        $data['bpName'] = $_POST['bpName'];
        $data['bpCity'] = $_POST['bpCity'];
        $data['bpAddress'] = $_POST['bpAddress'];
        $data['st_id'] = $_POST['state'];
        $data['dst_Id'] = $_POST['c_district'];
        $data['bpPincode'] = $_POST['bpPincode'];
        $data['bpGSTIN'] = $_POST['bpGSTIN'];
        $data['bpPanNo'] = $_POST['bpPanNo'];
        $data['bpContactPerson'] = $_POST['bpContactPerson'];
        $data['bpMobileNo'] = $_POST['bpMobileNo'];
        $data['bpEmail'] = $_POST['bpEmail'];
        $data['bplatitude'] = $_POST['bplatitude'];
        $data['bplongitude'] = $_POST['bplongitude'];
        $data['bpType'] = $_POST['bpType'];

        $IsUnique = $db->getItemSafe("SELECT COUNT(*) from business_partner WHERE bpName = ? AND id <> '{$_POST['customer_id']}'", "s", [$_POST['bpName']]);
        if ($IsUnique > 0) {
            echo "{success: false,msg:'Name already existing.'}";
            exit;
        }
        if ($id > 0) {
            $data['bpUpdatedOn'] = date('Y-m-d H:i:s');
            $data['bpUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform("business_partner", $data, 'update', " id = {$id}");
            if ($status) {
                echo "{success: true,msg:'Saved Successfully'}";
            } else {
                echo "{success: false,msg: 'Error occured while saving data'}";
            }
        } else {
            $refid = $db->getItemFromDB("SELECT UUID() AS uuid");
            $data['refid'] = $refid;
            $data['bpCreatedOn'] = date('Y-m-d H:i:s');;
            $data['bpCreatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform("business_partner", $data);

            //Create ledger in Finascop

            $group_id = '199';
            $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'CreateLedger'");
            $finields = array(
                "name" => $_POST['bpName'],
                "mobile" =>  $_POST['bpMobileNo'],
                "refid" => $refid,
                "group_id" => $group_id
            );
            //print_r($finields);exit();
            $fields_stringfin = json_encode($finields);
            $cURLConnection = curl_init();
            $headers = [
                "x-functions-key:" . DATAENTRY_KEY,
                'Content-Type:application/json'
            ];

            curl_setopt($cURLConnection, CURLOPT_URL, $url);
            curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cURLConnection, CURLOPT_ENCODING, '');
            curl_setopt($cURLConnection, CURLOPT_MAXREDIRS, 10);
            curl_setopt($cURLConnection, CURLOPT_TIMEOUT,  0);
            curl_setopt($cURLConnection, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($cURLConnection, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            //curl_setopt($cURLConnection, CURLOPT_POST, 1);
            curl_setopt($cURLConnection, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $fields_stringfin);
            curl_setopt($cURLConnection, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS | CURLPROTO_HTTP);

            $response = curl_exec($cURLConnection);
            curl_close($cURLConnection);
            $resultFin = json_decode($response, true);
            //print_r($resultFin);
            //if ($status) {
            if ($resultFin['statusId'] == 1) {
                echo "{success: true,msg:'Saved Successfully'}";
            } else {
                $message = $resultFin['message'];
                echo "{success: false,msg: '{$message}'}";
            }
        }

        break;
    case 'listBusinessPartner':
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $filter_query = ' 1=1';
        $br_id = $_SESSION['admin']->finascop_current_branch_id;

        if (isset($_POST['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
        }


        $qry = "select id as bpId, bpName,bpAddress,bpCity,bpPincode,bpGSTIN,dst_Id,br_id,bpContactPerson,bpMobileNo,bpEmail,bpPanNo,bplatitude,bplongitude,"
            . "(select st_name from finascop_state b inner join  finascop_district d on b.st_ID = d.st_Id where d.dst_Id = a.dst_Id)as st_name,"
            . "(select b.st_ID from finascop_state b inner join  finascop_district d on b.st_ID = d.st_Id where d.dst_Id = a.dst_Id)as st_id,"
            . "(select c.dst_Id from  finascop_district c where c.dst_Id = a.dst_Id )as dst_Id,"
            . "(select dst_Name from  finascop_district c where c.dst_Id = a.dst_Id )as dst_Name,bpType,
            CASE WHEN bpType = 1 THEN 'Sole Proprietorship' WHEN bpType = 2 THEN 'Partnership' WHEN bpType = 3 THEN 'LLP' 
            WHEN bpType = 4 THEN 'Private limited company' WHEN bpType = 5 THEN 'Society' WHEN bpType = 6 THEN 'Trust'  END as bpTypeName "
            . "  from   business_partner a where  {$filter_query} order by id asc limit {$rec_start},{$rec_limit} ";

        $data = $db->getMultipleData($qry, true);

        $countQuery = "SELECT COUNT(*) FROM  business_partner a where {$filter_query} ";
        $count = $db->getItemFromDB($countQuery);


        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }

        break;
    case 'editFormDataLoadPartner':
        $cust_id = $_POST['bpId'];
        if ($cust_id != 0) {

            $qry = "SELECT id AS bpId, bpName,bpAddress,bpCity,bpPincode,bpGSTIN,dst_Id,br_id,bpContactPerson,bpMobileNo,bpEmail,bpPanNo,bplatitude,bplongitude,
            (SELECT st_name FROM finascop_state b INNER JOIN  finascop_district d ON b.st_ID = d.st_Id WHERE d.dst_Id = a.dst_Id)AS st_name,
            (SELECT b.st_ID FROM finascop_state b INNER JOIN finascop_district d ON b.st_ID = d.st_Id WHERE d.dst_Id = a.dst_Id)AS st_id,
            (SELECT c.dst_Id FROM  finascop_district c WHERE c.dst_Id = a.dst_Id )AS dst_Id,
            (SELECT dst_Name FROM  finascop_district c WHERE c.dst_Id = a.dst_Id )AS dst_Name ,bpType,
            CASE WHEN bpType = 1 THEN 'Sole Proprietorship' WHEN bpType = 2 THEN 'Partnership' WHEN bpType = 3 THEN 'LLP' 
            WHEN bpType = 4 THEN 'Private limited company' WHEN bpType = 5 THEN 'Society' WHEN bpType = 6 THEN 'Trust'  END as bpTypeName
            FROM  business_partner a WHERE id = {$cust_id}";
            $results = $db->getFromDB($qry, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;
    case 'getBusinessPartners':
        $type = $_POST['type'];
        $mode = $_POST['mode'];
        /*if ($type == 1 && $mode == 2) {
            $qry = "SELECT *, CONCAT(id, '_', networkType) AS unique_key FROM ((SELECT id,bpName,0 AS networkType FROM business_partner) UNION (SELECT id,baName AS bpName,1 AS networkType FROM business_associate WHERE baType = 1 AND baMode = 1)) AS bps ORDER BY networkType";
        } else {
            $qry = "select id,bpName,0 AS networkType from business_partner order by bpName";
        }*/
        $qry = "SELECT id,baName AS bpName,1 AS networkType FROM business_associate WHERE baType = 1 AND baMode = 1 order by baName";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        // echo '{success:true, data:'.json_encode($data).'}';
        break;
    case 'ListBatoMap':
        $filter_query = ' 1=1';
        if ($_POST['searchState'] > 0) {
            $filter_query .= " AND st_id = {$_POST['searchState']} ";
        }
        if ($_POST['searchDistrict'] > 0) {
            $filter_query .= " AND dst_Id = {$_POST['searchDistrict']} ";
        }
        $qry = "select id ,baName,baPhone,baAddress,baCity,baPincode,baMobileNo from   business_associate 
        where  {$filter_query} order by id asc ";

        $data = $db->getMultipleData($qry, true);

        $countQuery = "SELECT COUNT(*) FROM  business_associate where {$filter_query} ";
        $count = $db->getItemFromDB($countQuery);


        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'assignBa':
        $id = $_POST['executive_id'];
        $areaId = $_POST['areaId'];
        $areaAssociateType = $db->getItemFromDB("SELECT baType from business_associate WHERE business_associate.id = '{$id}'");

        $areaAssociateCount = 1;
        $marketAssociateCount = 3;

        $hasBaMapped = $db->getItemFromDB("SELECT COUNT(*) from area_entries WHERE areaBusinessAssociate = '{$id}' AND id <> '{$areaId}'");
        switch ($areaAssociateType) {
            case '1':
                if ($hasBaMapped > 0) {
                    echo "{success: false,msg:'Bussiness associate already mapped to {$areaAssociateCount} area.'}";
                    exit;
                }
                break;
            case '2':
                if ($hasBaMapped > 2) {
                    echo "{success: false,msg:'Bussiness associate already mapped to {$marketAssociateCount} areas.'}";
                    exit;
                }
                break;
        }
        $db->query('begin');
        $data['areaBusinessAssociate'] = $id;
        $data['areaUpdatedOn'] = date('Y-m-d H:i:s');
        $data['areaUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;

        $baDetails = $db->getFromDB("SELECT * FROM business_associate WHERE id = {$id}", true);
        if ($baDetails['st_id'] > 0)
            $state = $db->getItemFromDB("SELECT st_name FROM finascop_state WHERE st_ID = {$baDetails['st_id']}");
        $country = $db->getItemFromDB("SELECT country_name FROM retaline_country WHERE is_default = 1");

        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'BAURL'");
        $fields = array(
            "FullName" => $baDetails['baName'],
            "Address" => $baDetails['baAddress'],
            "Phone" =>  $baDetails['baMobileNo'],
            "Email" => $baDetails['baEmail'],
            "Password" => $baDetails['temporary_password'],
            "City" => $baDetails['baCity'],
            "State" => $state,
            "Country" => $country,
            "UserType" => 3,
            "roleId" => array(6),
            "areaId" => $areaId
        );
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
        //print_r($result);
        if ($result['refId'] > 0) {

            //Create ledger in Finascop
            if ($areaAssociateType == 1) {
                $group_id = '197';
            } else {
                $group_id = '198';
            }
            $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'CreateLedger'");
            $finields = array(
                "name" => $_POST['baName'],
                "mobile" =>  $_POST['baMobileNo'],
                "refid" => $result['refId'],
                "group_id" => $group_id
            );

            $fields_stringfin = json_encode($finields);
            //echo $url;
            //print_r($fields_stringfin);
            $cURLConnection = curl_init();
            $headers = [
                "x-functions-key:" . DATAENTRY_KEY,
                'Content-Type:application/json'
            ];

            curl_setopt($cURLConnection, CURLOPT_URL, $url);
            curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cURLConnection, CURLOPT_ENCODING, '');
            curl_setopt($cURLConnection, CURLOPT_MAXREDIRS, 10);
            curl_setopt($cURLConnection, CURLOPT_TIMEOUT,  0);
            curl_setopt($cURLConnection, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($cURLConnection, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            //curl_setopt($cURLConnection, CURLOPT_POST, 1);
            curl_setopt($cURLConnection, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $fields_stringfin);
            curl_setopt($cURLConnection, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS | CURLPROTO_HTTP);

            $response = curl_exec($cURLConnection);
            curl_close($cURLConnection);
            $resultFin = json_decode($response, true);
        } else {
            $apiResponse = "API conflicted. Please try again!" . $result['message'];
            echo "{success: false, msg: '" . $apiResponse . "' }";
            exit();
        }
        $status = $db->perform("area_entries", $data, 'update', " id = {$areaId}");
        $status = $db->query('commit');
        if ($status == 1) {
            $message = " Details Saved Successfully." . $apiResponse;
            echo "{success: true,msg:'{$message}'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data'}";
        }
        break;
    case 'listRelationshipOfficer':
        $baId = $_POST['baId'];
        $filter_query = " 1=1 AND roBusAssociate = {$baId} ";

        $qry = "select id,roName,roMobile,roAddress,roPincode,rost_id,rodst_Id,roQualification,roExperience,
        roContactPerson,roContactMobile,roUsername,roPassword,roBloodGroup,roLicenceNo,roPanNo,roAadhaar,roBankAccount,roUPI,roBusAssociate,
        roArea from   relationship_officer where  {$filter_query} order by id asc ";

        $data = $db->getMultipleData($qry, true);

        $countQuery = "SELECT COUNT(*) FROM  relationship_officer where {$filter_query} ";
        $count = $db->getItemFromDB($countQuery);


        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'roDetailsView':
        $deli_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($deli_id) {

            $data = $db->getFromDB("SELECT  id,roName,roMobile,roAddress,roPincode,rost_id,rodst_Id,roQualification,roExperience,
                roContactPerson,roContactMobile,roUsername,roPassword,roBloodGroup,roLicenceNo,roPanNo,roAadhaar,roBankAccount,roUPI,roBusAssociate,
                roArea from   relationship_officer WHERE id = {$deli_id}", true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'updateRoStatus':
        $roId = $_POST['roId'];
        $leadUpdate['roStatus'] = $_POST['roStatus'];

        $crmCmmn['roId'] = $roId;
        $crmCmmn['roStatus'] = $_POST['roStatus'];
        $crmCmmn['roRemarks'] = $_POST['roRemarks'];

        $db->query('begin');
        $status = $db->perform('relational_officer_log', $crmCmmn);
        $status = $db->perform('relationship_officer', $leadUpdate, 'update', " id = {$roId}");

        switch ($_POST['roStatus']) {
            case 1:
                $message = "RO is Created.";
                break;
            case 2:
                $message = "RO is Approved.";
                break;
            case 3:
                $message = "RO is on hold";
                break;
            case 4:
                $message = "RO is Rejected";
                break;
        }

        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg: '" . $message . "' }";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
    case 'getTerritory':
        $qry = "select id,name FROM division where status= 1 AND typeId = 4  order by name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getArea':
        $state = $_POST['st_Id'];
        $district = $_POST['dst_ID'];
        $qry = "select id,areaName from area_entries WHERE areaState = {$state} AND areaDistrict = {$district} order by areaName";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'loadGeography':
        $state = $_POST['stateId'];
        $district = $_POST['districtId'];
        $areaId = $_POST['areaId'];
        $cond = " ";
        if ($state > 0)
            $cond .= " AND areaState = {$state} ";
        if ($district > 0)
            $cond .= " AND areaDistrict = {$district} ";
        if ($areaId > 0)
            $cond .= " AND id = {$areaId} ";
        $qry = "select id,areaName,areaLocation,areaSpan,areaLatitude,areaLongitude,
            areaBusinessAssociate,areaState,areaDistrict,
            (SELECT baName FROM business_associate WHERE business_associate.id = areaBusinessAssociate) as areaBusinessAssociateName, 
            (SELECT CASE WHEN baType = 1 THEN 'Area' WHEN baType = 2 THEN 'Market' END AS baTypeName FROM business_associate WHERE business_associate.id = areaBusinessAssociate) AS licenseeType 
            FROM area_entries WHERE 1=1 {$cond} order by areaName";
        $areaDetails = $db->getMultipleData($qry, true);
        $data = [];

        foreach ($areaDetails as $area) {
            $areaName = $area['areaName'];
            $data[$areaName] = [
                'center' => [
                    'lat' => floatval($area['areaLatitude']),
                    'lng' => floatval($area['areaLongitude']),
                ],
                'span' => intval($area['areaSpan']), // Using span as areaSpan
                'licensee' => $area['areaBusinessAssociateName'],
                'type' => $area['licenseeType']
            ];
        }
        echo '{success:true, data:' . json_encode($data) . '}';
        break;
}
