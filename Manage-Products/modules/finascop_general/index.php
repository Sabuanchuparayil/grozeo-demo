<?php

require_once(ROOT . '/../includes/config.php');
require_once(INCLUDE_PATH . "/finascop_common_functions.php");
switch ($op) {
    case 'saveUserMapping':
        $data = $_POST['data'];
        $userTypeMapping = json_decode($data, true);
        $success = true;
        $db->query("begin");
         $db->query("DELETE FROM " . FINASCOP_DB . "finascop_project_user_type_mapping");
         $db->query("commit");
         foreach ($userTypeMapping as $key => $value) {
           
            $success = $db->query("REPLACE INTO " . FINASCOP_DB . "finascop_project_user_type_mapping(project_user_type_id,finascop_user_type_id) VALUES ({$value['project_user_type_id']},{$value['finascop_user_type_id']})");
            if (!$success) {
                break;
            }
        }
       
        if ($success) {
            echo "{success:true}";
        } else {
            echo "{success:false}";
        }

        break;
    case 'getUserTypeMapping':
        $qry = "SELECT utm.user_type_id as project_user_type_id,"
                . "utm.user_type_text as project_user_type,"
                . "fput.finascop_user_type_id as finascop_user_type_id,"
                . "(SELECT finascop_user_type_text FROM " . FINASCOP_DB . "finascop_user_type fut WHERE fut.finascop_user_type_id = fput.finascop_user_type_id ) as finascop_user_type "
                . "FROM usr_type_master utm LEFT JOIN " . FINASCOP_DB . "finascop_project_user_type_mapping fput "
                . "ON  utm.user_type_id = fput.project_user_type_id ORDER BY utm.user_type_id";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';


        break;
    case 'getFinascopUserTypes':
        $qry = "SELECT fut.finascop_user_type_id AS finascop_user_type_id,fut.finascop_user_type_text AS finascop_user_type "
                . "FROM " . FINASCOP_DB . "finascop_user_type fut";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
}
