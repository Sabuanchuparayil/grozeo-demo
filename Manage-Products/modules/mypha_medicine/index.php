<?php

/*
 * Created on 17-Nov-19
 * @author : Nisanth < <nisanth@velosit.in>>
 *
 * Medicine details of the Application will be handled by this module.
 *
 */
writeLog(__FILE__);
require_once(EXTERNAL_LIBRARY_PATH);
require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/finascop_config/config.php');
require_once(INCLUDE_PATH . "/finascop_User.php");
$userid = $_SESSION['admin']->Finascop_UserId;
switch ($op) {
    case 'listMedicines':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'medicineMaster_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['med_id', 'med_name', 'med_composition', 'med_brand', 'med_type', 'med_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_medicineMaster "
                . "INNER JOIN  mypha_medicineType ON medicine_type_id = medicine_type"
                . " INNER JOIN  mypha_category ON category_id = medicine_category LEFT JOIN  mypha_composition ON composition_id = medicine_content "
                . "INNER JOIN  mypha_manufacture ON manufacture_id = medicine_manufacture {$search}  ORDER BY {$sort} {$dir}";

        $listQuery = "SELECT medicineMaster_id,medicineMaster_name,category_name,manufacture_name,medicine_type_name,'No' as photo_count FROM mypha_medicineMaster "
                . "INNER JOIN  mypha_medicineType ON medicine_type_id = medicine_type"
                . " INNER JOIN  mypha_category ON category_id = medicine_category LEFT JOIN  mypha_composition ON composition_id = medicine_content "
                . "INNER JOIN  mypha_manufacture ON manufacture_id = medicine_manufacture {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);


        break;
    case 'medType':
        $typeAhead = '';
        $qry = "SELECT medicine_type_id, medicine_type_name FROM mypha_medicineType WHERE status = 1   $typeAhead ORDER BY medicine_type_name ASC";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'medCategory':
        $typeAhead = '';
        $qry = "SELECT category_id, category_name FROM mypha_category WHERE status = 1   $typeAhead ORDER BY category_name ASC";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'medManufacture':
        $typeAhead = '';
        $sql = "SELECT manufacture_id, manufacture_name FROM mypha_manufacture WHERE status = 1   $typeAhead ORDER BY manufacture_name ASC";
        $data = $db->getMultipleData($sql, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
    case 'medDisease':
        $typeAhead = '';
        $qry = "SELECT disease_id, disease_name FROM mypha_disease WHERE 1 = 1   $typeAhead ORDER BY disease_name ASC";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getSearchDetails':
        $search_name = $_POST['search_name'];
        $search_id = $_POST['search_id'];
        $type = $_POST['type'];
        switch ($type) {
            case 'Use':
                $sql = "SELECT meduse_id as search_id,meduse_name as searchValue FROM mypha_meduse WHERE meduse_id = '{$search_id}'";
                break;
            case 'SF':
                $sql = "SELECT medsideffect_id as search_id,medsideffect_name as searchValue  FROM mypha_medsideffect WHERE medsideffect_id = '{$search_id}'";
                break;
            case 'Works':
                $sql = "SELECT medwork_id as search_id,medwork_name as searchValue FROM mypha_medwork WHERE medwork_id = '{$search_id}'";
                break;
            case 'Info':
                $sql = "SELECT medadinfo_id as search_id,medadinfo_name as searchValue FROM mypha_medadinfo WHERE medadinfo_id = '{$search_id}'";
                break;
            case 'Warning':
                $sql = "SELECT warning_id as search_id,warning_name as searchValue FROM mypha_warning WHERE warning_id = '{$search_id}'";
                break;
        }

        $data = $db->getFromDb($sql, true);
        if ($data['search_id'] > 0) {
            $data['success'] = true;
        } else {
            $data['success'] = false;
        }
        echo json_encode($data);
        break;
    case 'dashboardSearchGridStore':
        $search_field = $_POST['search_field'];
        $type = $_POST['type'];
        switch ($type) {
            case 'Use':
                $countQuery = "SELECT COUNT(*) FROM mypha_meduse WHERE meduse_status = 1 AND meduse_name LIKE '{$search_field}%'";
                $listQuery = "SELECT meduse_id as search_id,meduse_name as search_name FROM mypha_meduse WHERE meduse_status = 1 AND meduse_name LIKE '{$search_field}%'";
                break;
            case 'SF':
                $countQuery = "SELECT COUNT(*) FROM mypha_medsideffect WHERE medsideffect_status = 1 AND medsideffect_name LIKE '{$search_field}%'";
                $listQuery = "SELECT medsideffect_id as search_id,medsideffect_name as search_name FROM mypha_medsideffect WHERE medsideffect_status = 1 AND medsideffect_name LIKE '{$search_field}%'";
                break;
            case 'Works':
                $countQuery = "SELECT COUNT(*) FROM mypha_medwork WHERE medwork_status = 1 AND medwork_name LIKE '{$search_field}%'";
                $listQuery = "SELECT medwork_id as search_id,medwork_name as search_name FROM mypha_medwork WHERE medwork_status = 1 AND medwork_name LIKE '{$search_field}%'";
                break;
            case 'Info':
                $countQuery = "SELECT COUNT(*) FROM mypha_medadinfo WHERE medadinfo_status = 1 AND medadinfo_name LIKE '{$search_field}%'";
                $listQuery = "SELECT medadinfo_id as search_id,medadinfo_name as search_name FROM mypha_medadinfo WHERE medadinfo_status = 1 AND medadinfo_name LIKE '{$search_field}%'";
                break;
            case 'Warning':
                $countQuery = "SELECT COUNT(*) FROM mypha_warning WHERE warning_status = 1 AND warning_name LIKE '{$search_field}%'";
                $listQuery = "SELECT warning_id as search_id,warning_name as search_name FROM mypha_warning WHERE warning_status = 1 AND warning_name LIKE '{$search_field}%'";
                break;
        }
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'saveMedicineData':
        $data = $_POST;
        unset($data['apikey']);
        unset($data['tstamp']);
        unset($data['type']);
        $db->query('begin');
        if ($data['medicineMaster_id'] > 0) {
            $medUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_medicineMaster WHERE medicineMaster_name ='{$_POST['medicineMaster_name']}' "
                    . "AND medicine_type = {$_POST['medicine_type']} AND medicine_manufacture = {$_POST['medicine_manufacture']} "
                    . "AND medicineMaster_id <> {$_POST['medicineMaster_id']}");
            if ($medUnique > 0) {
                echo "{success: false, message:'Medicine already exists.'}";
                exit;
            } else {
                $data['medicine_updatedOn'] = date('Y-m-d H:i:s');
                $data['medicine_updatedBy'] = $userid;
                $data['medicine_isPrescription'] = $data['medicine_isPrescription'] == true ? 1 : 0;
                $status = $db->perform("mypha_medicineMaster", $data, 'update', 'medicineMaster_id =' . $data['medicineMaster_id']);
                $lastId = $data['medicineMaster_id'];

                $fsim['stit_itemName'] = $_POST['medicineMaster_name'];
                $status = $db->perform('finascop_stock_itemmaster', $fsim, 'update', " product_category = {$lastId} AND isMedicine = 1");
                $fsui['fsi_item_name'] = $_POST['medicineMaster_name'];
                $status = $db->perform('finascop_stock_uniqueitem', $fsui, 'update', " fsi_category_id = {$lastId} AND isMedicine = 1");
            }
        } else {
            $medUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_medicineMaster WHERE medicineMaster_name ='{$_POST['medicineMaster_name']}' "
                    . "AND medicine_type = {$_POST['medicine_type']} AND medicine_manufacture = {$_POST['medicine_manufacture']} ");
            if ($medUnique > 0) {
                echo "{success: false, message:'Medicine already exists.'}";
                exit;
            } else {
                unset($data['medicineMaster_id']);
                $data['medicine_createdOn'] = date('Y-m-d H:i:s');
                $data['medicine_createdBy'] = $userid;
                $data['medicine_isPrescription'] = $data['medicine_isPrescription'] == true ? 1 : 0;
                $status = $db->perform('mypha_medicineMaster', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT * FROM mypha_medicineMaster WHERE medicineMaster_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'loadMedicineData':
        $medId = isset($_POST['medId']) ? intval($_POST['medId']) : 0;
        if ($medId) {
            _loadRecordJson("SELECT  medicineMaster_id,medicineMaster_name,category_name,manufacture_name,medicine_type_name,medicine_manufacture,medicineContent_id,medicine_content,medicine_type,medicine_category,"
                    . "medicine_about,medicine_use,medicine_sideeffects,medicine_works,medicine_morInfo,medicine_diseases,medicine_isPrescription,medicine_status,composition_name "
                    . " FROM mypha_medicineMaster "
                    . "INNER JOIN  mypha_medicineType ON medicine_type_id = medicine_type"
                    . " INNER JOIN  mypha_category ON category_id = medicine_category LEFT JOIN  mypha_composition ON composition_id = medicine_content "
                    . "INNER JOIN  mypha_manufacture ON manufacture_id = medicine_manufacture WHERE medicineMaster_id = " . $medId);
        }
        break;
    case 'medicineDetailsView':
        $medicineMaster_id = isset($_POST['medicineMaster_id']) ? intval($_POST['medicineMaster_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($medicineMaster_id || $ID) {

            $data = $db->getFromDB("SELECT medicineMaster_id,medicineMaster_name,category_name,manufacture_name,medicine_type_name,medicine_manufacture,medicineContent_id,medicine_content,medicine_type,medicine_category,"
                    . "medicine_about,medicine_use,medicine_sideeffects,medicine_works,medicine_morInfo,medicine_diseases,IF(medicine_isPrescription = 1,'required','NA') as medicine_isPrescription,medicine_status,composition_name "
                    . "FROM mypha_medicineMaster "
                    . "INNER JOIN  mypha_medicineType ON medicine_type_id = medicine_type"
                    . " INNER JOIN  mypha_category ON category_id = medicine_category LEFT JOIN  mypha_composition ON composition_id = medicine_content "
                    . "INNER JOIN  mypha_manufacture ON manufacture_id = medicine_manufacture WHERE medicineMaster_id =" . $medicineMaster_id, true);
            if (!empty($data['medicine_diseases'])) {
                $diseases = $db->getFromDB("SELECT disease_name FROM mypha_disease WHERE disease_ID IN ({$data['medicine_diseases']})");
                $data['disease'] = $diseases;
            } else {
                $data['disease'] = 'NA';
            }

            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'medComposition':
        $qry = "SELECT composition_id, composition_name FROM mypha_composition WHERE composition_status = 1  ORDER BY composition_name ASC";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'medWarningCategory':
        $qry = "SELECT warningCategory_id, warningCategory_name FROM mypha_warningCategory WHERE warningCategory_status = 1    ORDER BY warningCategory_name ASC";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listMedicineWarnings':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'medicineWarnings_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;

        $countQuery = "SELECT COUNT(*) FROM mypha_medicineWarnings mw INNER JOIN mypha_warningCategory wc ON wc.warningCategory_id = mw.warningCategory_id WHERE medicineMaster_id = " . intval($_POST['medId']) . "";
        $listQuery = "SELECT medicineWarnings_id,medicineMaster_id,mw.warningCategory_id,medicinWarnings,warningCategory_name FROM mypha_medicineWarnings mw 
            INNER JOIN mypha_warningCategory wc ON wc.warningCategory_id = mw.warningCategory_id
        WHERE medicineMaster_id = " . intval($_POST['medId']) . " ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'addWarningsToMed':
        $medId = $_POST['medId'];
        $warningCat = $_POST['warningCat'];
        $warnMsg = $_POST['warnMsg'];
        $data['medicinWarnings'] = $warnMsg;
        $count = $db->getItemFromDB("SELECT COUNT(*) FROM mypha_medicineWarnings WHERE medicineMaster_id = {$medId} AND warningCategory_id = {$warningCat}");
        $db->query('begin');
        if ($count > 0) {
            $data['medicineWarnings_UpdatedOn'] = date('Y-m-d');
            $data['medicineWarnings_UpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('mypha_medicineWarnings', $data, 'update', " medicineMaster_id = {$medId} AND warningCategory_id = {$warningCat}");
        } else {
            $data['medicineMaster_id'] = $medId;
            $data['warningCategory_id'] = $warningCat;
            $data['medicineWarnings_CreatedOn'] = date('Y-m-d');
            $data['medicineWarnings_Createdby'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('mypha_medicineWarnings', $data);
        }
        $status = $db->query('commit');

        if ($status == 1) {
            echo '{"success":true,"msg":"Warnings added"}';
        } else {
            echo '{"success":false,"msg":" Failed to save"}';
        }

        break;
}