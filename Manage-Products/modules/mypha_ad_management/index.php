<?php

switch ($op) {
    case 'listadManagement':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'adzone_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['ad_name', 'ad_type', 'ad_start', 'ad_end', 'ad_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* RAW (disabled): $filter = $_POST['filter']; */
    /*    if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $search .= " AND ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $search .= " AND ({$field[field]} LIKE '{$field['data']['value']}%') ";
                    }
                }
            }
        }*/
                if (isset($filter)) {

            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                    //    if ($field['field'] == 'status') {
                            if ($field['field'] == 'adzone_status') {
                                $fiterItem = ($field['data']['value'] == 'Active') ? 1 : 0;
                                $search .= " and ({$field['field']} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Inactive') {
                               $fiterItem = 0;
                               $search .= " and ({$field['field']} = {$fiterItem}) ";
                               } else {
                                $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                            }
                        //}
                    }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM app_adzones  {$search}";
        $listQuery = "SELECT adzone_id,adzone_name AS adzone_name,adzone_screen,IF((adzone_status=1),'Active','Inactive') AS adzone_status,adzone_type FROM app_adzones {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'saveAdManagement':
        $db->query('begin');
        $data = $_POST['n'];

        if ($data['adzone_id'] > 0) {
            $data['adzone_updatedOn '] = date('Y-m-d H:i:s');
            $data['adzone_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $adzone_status = $db->perform("app_adzones", $data, 'update', 'adzone_id =' . $data['adzone_id']);
            $lastId = $data['adzone_id'];
        } else {
            unset($data['adzone_id']);
            $data['adzone_cretedOn '] = date('Y-m-d H:i:s');
            $data['adzone_cretedBy'] = $_SESSION['admin']->Finascop_UserId;
            $adzone_status = $db->perform('app_adzones', $data);
            $lastId = $db->insert_id();
        }

        $return_rec = $db->getFromDb("SELECT adzone_id,adzone_name AS adzone_name,adzone_screen,adzone_status,adzone_type FROM app_adzones WHERE adzone_id  = {$lastId}", true);
        $adzone_status = $db->query('commit');
        if ($adzone_status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'adManagementDetailsView':
        $adzone_id = isset($_POST['adzone_id']) ? intval($_POST['adzone_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($adzone_id || $ID) {

            $data = $db->getFromDB("SELECT adzone_id,adzone_name AS adzone_name,adzone_screen,adzone_status,adzone_type FROM app_adzones WHERE adzone_id  =" . $adzone_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'admanagement_load':
        $adzone_id = isset($_POST['adzone_id']) ? intval($_POST['adzone_id']) : 0;
        if ($adzone_id) {
            $sql = "SELECT adzone_id,adzone_name AS adzone_name,adzone_screen,adzone_status,adzone_type FROM app_adzones  WHERE adzone_id= " . $adzone_id;
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
    case 'screenName':

        $listQuery = "SELECT screen_id,screen_name from app_screens";

        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'layoutName':

        $listQuery = "SELECT layout_type_id,layout_type_name,type_id from app_layouttype";

        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
}


