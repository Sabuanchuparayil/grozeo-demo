<?php

$userid = $_SESSION['admin']->Finascop_UserId;

switch ($op) {
    case 'listMedicineMasters':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'medicineMaster_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        $subCategory_name = '';
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {

                        if ($field['field'] == 'composition_name') {
                            $composition_ids = $db->getItemFromDB("SELECT GROUP_CONCAT(composition_id) FROM mypha_composition WHERE composition_name LIKE '{$field[data][value]}%'");
                            $search .= " and (medicine_composition IN({$composition_ids})) ";
                        } else if ($field['field'] == 'subCategory_name') {
                            $subCategory_ids = $db->getItemFromDB("SELECT GROUP_CONCAT(composition_id) FROM mypha_composition mc INNER JOIN mypha_subCategory ms ON  ms.subCategory_id = mc.subCategory_id AND subCategory_name LIKE '{$field[data][value]}%'");
                            $search .= " and (medicine_composition IN({$subCategory_ids})) ";
                        } else if ($field['field'] == 'manufacture_name') {
                            $manufacture_ids = $db->getItemFromDB("SELECT GROUP_CONCAT(manufacture_id) FROM mypha_manufacture WHERE manufacture_name LIKE '{$field[data][value]}%'");
                            $search .= " and (medicine_manufacture IN({$manufacture_ids})) ";
                        } else if ($field['field'] == 'medicine_type_name') {
                            $medicine_type_id = $db->getItemFromDB("SELECT GROUP_CONCAT(medicine_type_id) FROM mypha_medicineType WHERE medicine_type_name LIKE '{$field[data][value]}%'");
                            $search .= " and (medicine_type IN({$medicine_type_id})) ";
                        } else if ($field['field'] == 'isVerified') {
                            if ($field[data][value] == 'Yes') {
                                $search .= " and (isVerified = 1) ";
                            } else if ($field[data][value] == 'No') {
                                $search .= " and (isVerified = 0) ";
                            } else {
                                $search .= " and (isVerified IN(1,0)) ";
                            }
                        } else if ($field['field'] == 'mustatus') {
                            if ($field[data][value] == 'Yes') {
                                $search .= " and (medicine_use <> '') ";
                            } else if ($field[data][value] == 'No') {
                                $search .= " and (medicine_use = '') ";
                            } else {
                                $search .= " and (medicine_use IN(<> '','')) ";
                            }
                        } else if ($field['field'] == 'sfstatus') {
                            if ($field[data][value] == 'Yes') {
                                $search .= " and (medicine_sideeffects <> '') ";
                            } else if ($field[data][value] == 'No') {
                                $search .= " and (medicine_sideeffects = '') ";
                            } else {
                                $search .= " and (medicine_sideeffects IN(<> '','')) ";
                            }
                        } else if ($field['field'] == 'mwstatus') {
                            if ($field[data][value] == 'Yes') {
                                $search .= " and (medicine_works <> '') ";
                            } else if ($field[data][value] == 'No') {
                                $search .= " and (medicine_works = '') ";
                            } else {
                                $search .= " and (medicine_works IN(<> '','')) ";
                            }
                        } else if ($field['field'] == 'mistatus') {
                            if ($field[data][value] == 'Yes') {
                                $search .= " and (medicine_morInfo <> '') ";
                            } else if ($field[data][value] == 'No') {
                                $search .= " and (medicine_morInfo = '') ";
                            } else {
                                $search .= " and (medicine_morInfo IN(<> '','')) ";
                            }
                        } else {
                            $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                        }
                    }
                }
            }
        }

//        $countQuery = "SELECT COUNT(*) FROM(SELECT medicineMaster_id,medicineMaster_name,manufacture_name,medicine_type_name,composition_name,subCategory_name,medicine_use,medicine_sideeffects,medicine_works,medicine_morInfo,IF(medicine_use <> '','Yes','No') AS mustatus,IF((medicine_sideeffects <>  ''),'Yes','No') AS sfstatus,
//IF((medicine_works <>  ''),'Yes','No') AS mwstatus,IF((medicine_morInfo <>  ''),'Yes','No') AS mistatus FROM mypha_medicineMaster "
//                . "INNER JOIN  mypha_medicineType ON medicine_type_id = medicine_type "
//                . "LEFT JOIN  mypha_composition mc ON composition_id = medicine_composition "
//                . "INNER JOIN  mypha_manufacture ON manufacture_id = medicine_manufacture "
//                . "LEFT JOIN mypha_subCategory ms on ms.subCategory_id = mc.subCategory_id) as brcount {$search}  ORDER BY {$sort} {$dir}";
        $countQuery = "SELECT COUNT(*) FROM mypha_medicineMaster {$search}  ";
        $count = $db->getItemFromDB($countQuery);
//        $listQuery = "SELECT * FROM(SELECT medicineMaster_id,medicineMaster_name,manufacture_name,medicine_type_name,composition_name,subCategory_name,medicine_use,medicine_sideeffects,medicine_works,medicine_morInfo,IF(medicine_use <> '','Yes','No') AS mustatus,IF((medicine_sideeffects <>  ''),'Yes','No') AS sfstatus,
//IF((medicine_works <>  ''),'Yes','No') AS mwstatus,IF((medicine_morInfo <>  ''),'Yes','No') AS mistatus FROM mypha_medicineMaster "
//                . "INNER JOIN  mypha_medicineType ON medicine_type_id = medicine_type "
//                . "LEFT JOIN  mypha_composition mc ON composition_id = medicine_composition "
//                . "INNER JOIN  mypha_manufacture ON manufacture_id = medicine_manufacture "
//                . "LEFT JOIN mypha_subCategory ms on ms.subCategory_id = mc.subCategory_id) AS listBrand {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $listQuery = "SELECT medicineMaster_id,medicineMaster_name,medicine_use, IF(medicine_use <> '','Yes','No') AS mustatus,"
                . "IF((medicine_sideeffects <>  ''),'Yes','No') AS sfstatus,medicine_sideeffects,IF((medicine_works <>  ''),'Yes','No') AS mwstatus,medicine_works,medicine_morInfo,IF((medicine_morInfo <>  ''),'Yes','No') AS mistatus,"
                . "medicine_manufacture,medicine_type,medicine_composition,IF((isVerified = 1),'Yes','No') AS isVerified  FROM mypha_medicineMaster {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";
        $datas = $db->getMulipleData($listQuery, true);
        //echo  $listQuery ;
        $resCount = count($datas);
        //print_r($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $combo[$i] = $db->getFromDb("SELECT composition_name,subCategory_name FROM mypha_composition mc LEFT JOIN mypha_subCategory ms ON  ms.subCategory_id = mc.subCategory_id {$subCategory_name}"
                        . "WHERE composition_id={$datas[$i]['medicine_composition']}  ", true);
                $datas[$i]['composition_name'] = $combo[$i]['composition_name'];
                $datas[$i]['subCategory_name'] = $combo[$i]['subCategory_name'];
                $datas[$i]['manufacture_name'] = $db->getItemFromDb("SELECT manufacture_name FROM mypha_manufacture WHERE manufacture_id={$datas[$i]['medicine_manufacture']}");
                $datas[$i]['medicine_type_name'] = $db->getItemFromDb("SELECT medicine_type_name FROM mypha_medicineType WHERE medicine_type_id={$datas[$i]['medicine_type']}");
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }

//        $db->printGridJson($countQuery, $listQuery);

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
    case 'medManufacture':
        $typeAhead = '';
        $sql = "SELECT manufacture_id, manufacture_name FROM mypha_manufacture WHERE status = 1   $typeAhead ORDER BY manufacture_name ASC";
        $data = $db->getMultipleData($sql, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
    case 'medComposition':
        $qry = "SELECT composition_id, CONCAT(composition_name,'-',(SELECT subCategory_name FROM mypha_subCategory ms WHERE ms.subCategory_id = mc.subCategory_id)) as composition_name FROM mypha_composition mc WHERE composition_status = 1  ORDER BY composition_name ASC";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'saveMedicineData':
        $data = $_POST;
        unset($data['apikey']);
        unset($data['tstamp']);
        unset($data['type']);
        $db->query('begin');
        if ($data['medicineMaster_id'] > 0) {
            $medUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_medicineMaster WHERE medicineMaster_name ='{$_POST['medicineMaster_name']}' "
                    . "AND medicine_type = {$_POST['medicine_type']} "
                    . "AND medicineMaster_id <> {$_POST['medicineMaster_id']}");
            if ($medUnique > 0) {
                echo "{success: false, message:'Medicine already exists.'}";
                exit;
            } else {
                $data['medicine_updatedOn'] = date('Y-m-d H:i:s');
                $data['medicine_updatedBy'] = $userid;
                $status = $db->perform("mypha_medicineMaster", $data, 'update', 'medicineMaster_id =' . $data['medicineMaster_id']);
                $lastId = $data['medicineMaster_id'];


                $fsim['stit_itemName'] = $_POST['medicineMaster_name'];
                $status = $db->perform('finascop_stock_itemmaster', $fsim, 'update', " stit_itemId = {$lastId} AND isMedicine = 1");
                $fsui['fsi_item_name'] = $_POST['medicineMaster_name'];
                $status = $db->perform('finascop_stock_uniqueitem', $fsui, 'update', " fsi_item_id = {$lastId} AND isMedicine = 1");
            }
        } else {
            $medUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_medicineMaster WHERE medicineMaster_name ='{$_POST['medicineMaster_name']}' "
                    . "AND medicine_type = {$_POST['medicine_type']} ");
            if ($medUnique > 0) {
                echo "{success: false, message:'Medicine already exists.'}";
                exit;
            } else {
                unset($data['medicineMaster_id']);
                $data['medicine_createdOn'] = date('Y-m-d H:i:s');
                $data['medicine_createdBy'] = $userid;
                $status = $db->perform('mypha_medicineMaster', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT medicineMaster_id,medicineMaster_name,manufacture_name,medicine_type_name,composition_name,subCategory_name,medicine_use,medicine_sideeffects,medicine_works,medicine_morInfo,IF((medicine_use <> ''),'Yes','No') AS mustatus,IF((medicine_sideeffects <>  ''),'Yes','No') AS sfstatus,
IF((medicine_works <>  ''),'Yes','No') AS mwstatus,IF((medicine_morInfo <>  ''),'Yes','No') AS mistatus FROM mypha_medicineMaster "
                . "INNER JOIN  mypha_medicineType ON medicine_type_id = medicine_type "
                . "LEFT JOIN  mypha_composition mc ON composition_id = medicine_composition "
                . "INNER JOIN  mypha_manufacture ON manufacture_id = medicine_manufacture "
                . "LEFT JOIN mypha_subCategory ms on ms.subCategory_id = mc.subCategory_id WHERE medicineMaster_id = {$lastId}", true);
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
            _loadRecordJson("SELECT  medicineMaster_id,medicineMaster_name,manufacture_name,medicine_type_name,medicine_manufacture,medicine_type,composition_name,medicine_composition "
                    . " FROM mypha_medicineMaster "
                    . "INNER JOIN  mypha_medicineType ON medicine_type_id = medicine_type "
                    . "LEFT JOIN  mypha_composition ON composition_id = medicine_composition "
                    . "INNER JOIN  mypha_manufacture ON manufacture_id = medicine_manufacture WHERE medicineMaster_id = " . $medId);
        }
        break;
    case 'listmedComposition':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'composition_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_composition  MC "
                . "INNER JOIN mypha_subCategory MS ON MS.subCategory_id = MC.subCategory_id INNER JOIN mypha_category mcat ON mcat.category_id = MS.category_id {$search} AND composition_type = 1 ";

        $listQuery = "SELECT composition_id,composition_name,MC.subCategory_id,subCategory_name as subCategory,category_name,IF((composition_status=1),'Active','Inactive') AS composition_status "
                . "FROM mypha_composition MC "
                . "INNER JOIN mypha_subCategory MS ON MS.subCategory_id = MC.subCategory_id "
                . "INNER JOIN mypha_category mcat ON mcat.category_id = MS.category_id {$search} AND composition_type = 1 ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'saveIngradients':
        $db->query('begin');
        $data = $_POST['n'];
        $mediCat_id = $data['ingradient_id'];
        $mediCat_name = $data['ingradient_name'];
        $mediCat_name = addslashes($mediCat_name);


        if ($data['ingradient_id'] > 0) {

            $data['ingradient_updatedOn'] = date('Y-m-d H:i:s');
            $data['ingradient_updatedBy'] = $userid;

            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_ingradient WHERE ingradient_name ='{$mediCat_name}' AND ingradient_id <> {$mediCat_id} ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This ingradient already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_ingradient", $data, 'update', 'ingradient_id =' . $data['ingradient_id']);
                $lastId = $data['ingradient_id'];
            }
        } else {
            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_ingradient WHERE ingradient_name ='{$mediCat_name}'  ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This Excipients already exists.'}";
                exit;
            } else {
                unset($data['ingradient_id']);
                $data['ingradient_createdOn'] = date('Y-m-d H:i:s');
                $data['ingradient_createdBy'] = $userid;
                $status = $db->perform('mypha_ingradient', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT ingradient_id ,ingradient_name ,ingradient_status  FROM mypha_ingradient WHERE ingradient_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'IngradientsdetailsView':
        $ingradient_id = isset($_POST['ingradient_id']) ? intval($_POST['ingradient_id']) : 0;
        if ($ingradient_id) {
            $sql = "SELECT ingradient_id ,ingradient_name ,ingradient_shortname,ingradient_status FROM mypha_ingradient WHERE ingradient_id= " . $ingradient_id;
            $data = $db->getFromDB($sql, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'loadIngradients':
        $ingradient_id = isset($_POST['ingradient_id']) ? intval($_POST['ingradient_id']) : 0;
        if ($ingradient_id) {
            $sql = "SELECT ingradient_id ,ingradient_name ,ingradient_shortname,ingradient_status FROM mypha_ingradient WHERE ingradient_id= " . $ingradient_id;
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
    case 'listIngradients':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'ingradient_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_ingradient  {$search}";

        $listQuery = "SELECT ingradient_id,ingradient_name ,ingradient_shortname,IF((ingradient_status=1),'Active','Inactive') AS ingradient_status  "
                . "FROM mypha_ingradient msc {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'loadIngridientsCombo':
        $qry = "SELECT ingradient_id, ingradient_name FROM mypha_ingradient WHERE ingradient_status = 1  ORDER BY ingradient_name ASC";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'loadUnitCombo':
        $qry = "SELECT unit_id, unit_name FROM mypha_unit WHERE status = 1  ORDER BY unit_name ASC";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'loadMedicineIngradients':
        $medIngrad_id = isset($_POST['medIngrad_id']) ? intval($_POST['medIngrad_id']) : 0;
        if ($medIngrad_id) {
            $sql = "SELECT medIngrad_id,medicineMaster_id,ingradient_id,medIngrad_qty,unit_id FROM mypha_mapMedicineIngradients ip WHERE medIngrad_id= " . $medIngrad_id;
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
    case 'loadAdvicePrecaution':
        $medadv_id = isset($_POST['medadv_id']) ? intval($_POST['medadv_id']) : 0;
        if ($medadv_id) {
            $sql = "SELECT medadv_id,advice_id AS adi_id,precaution_id AS pret_id,medadv_content FROM mypha_medicine_advice WHERE medadv_id= " . $medadv_id;
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
    case 'loadAdviceCombo':
        $listQuery = "SELECT advice_id AS adi_id,advice_name from mypha_safety_advice";

        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'loadPrecautionCombo':
        $listQuery = "SELECT precaution_id AS pret_id,precaution_name from mypha_safety_precaution";

        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listmappedIngradientinMedi':
        $medicineMaster_id = $_POST['medicineMaster_id'];
        if ($medicineMaster_id) {
            $countQuery = "SELECT COUNT(*) FROM mypha_mapMedicineIngradients it "
                    . "INNER JOIN mypha_ingradient ipt ON it.ingradient_id=ipt.ingradient_id  WHERE it.medicineMaster_id={$medicineMaster_id} ";
            $listQuery = "SELECT medIngrad_id,medicineMaster_id,it.ingradient_id,medIngrad_qty,it.unit_id,ingradient_name FROM mypha_mapMedicineIngradients it "
                    . " INNER JOIN mypha_ingradient ipt ON it.ingradient_id=ipt.ingradient_id  WHERE it.medicineMaster_id={$medicineMaster_id} ";
            $db->printGridJson($countQuery, $listQuery);
        }
        break;

    case 'mapIngradientstoMedicine':
        $db->query('begin');
        $medIngrad_id = $_POST['medIngrad_id'];
        $medicineMaster_id = $_POST['medicineMaster_id'];
        $data = array(
            'ingradient_id' => $_POST['ingradient_id'],
            'medIngrad_qty' => $_POST['medIngrad_qty'],
            //'unit_id' => $_POST['unit_id'],
            'medicineMaster_id' => $_POST['medicineMaster_id']
        );
        /* Creating a data array */
        $ingCount = $db->getItemSafe("SELECT COUNT(*) FROM mypha_mapMedicineIngradients WHERE ingradient_id = ? AND medicineMaster_id = {$medicineMaster_id} ", "i", [$_POST['ingradient_id']]);
        if ($ingCount > 0) {
            $medIngrad_id = $db->getItemSafe("SELECT medIngrad_id FROM mypha_mapMedicineIngradients WHERE ingradient_id = ? AND medicineMaster_id = {$medicineMaster_id}", "i", [$_POST['ingradient_id']]);
            $status = $db->perform("mypha_mapMedicineIngradients", $data, 'update', 'medIngrad_id =' . $medIngrad_id);
        } else {
            $status = $db->perform("mypha_mapMedicineIngradients", $data);
        }

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message: 'Ingradients mapped .'}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;

    case 'listadPrecaution':
        $medicineMaster_id = $_POST['medicineMaster_id'];
        if ($medicineMaster_id) {
            $countQuery = "SELECT COUNT(*) FROM mypha_medicine_advice ma "
                    . " INNER JOIN  mypha_safety_advice msa ON msa.advice_id = ma.advice_id  "
                    . " INNER JOIN  mypha_safety_precaution msp ON msp.precaution_id = ma.precaution_id  WHERE ma.medicineMaster_id={$medicineMaster_id} ";

            $listQuery = "SELECT medadv_id,medicineMaster_id,ma.advice_id AS adi_id,ma.precaution_id AS pret_id,ma.medadv_content,advice_name,precaution_name FROM mypha_medicine_advice ma "
                    . " INNER JOIN  mypha_safety_advice msa ON msa.advice_id = ma.advice_id  "
                    . " INNER JOIN  mypha_safety_precaution msp ON msp.precaution_id = ma.precaution_id  WHERE ma.medicineMaster_id={$medicineMaster_id} ";
            $db->printGridJson($countQuery, $listQuery);
        }
        break;

    case 'mapAdPrecautionstoMedicine':
        $db->query('begin');
        $medadv_id = $_POST['medadv_id'];
        $medicineMaster_id = $_POST['medicineMaster_id'];
        $precaution_id = $_POST['precaution_id'];
        $data = array(
            'advice_id' => $_POST['advice_id'],
            'precaution_id' => $_POST['precaution_id'],
            'medadv_content' => $_POST['medadv_content'],
            'medicineMaster_id' => $_POST['medicineMaster_id']
        );
        /* Creating a data array */
        $preCount = $db->getItemSafe("SELECT COUNT(*) FROM mypha_medicine_advice WHERE advice_id = ? AND precaution_id = {$precaution_id} and  medicineMaster_id = {$medicineMaster_id} ", "i", [$_POST['advice_id']]);
        if ($preCount > 0) {
            $medadv_id = $db->getItemSafe("SELECT medadv_id FROM mypha_medicine_advice WHERE advice_id = ? AND precaution_id = {$precaution_id} AND medicineMaster_id = {$medicineMaster_id}", "i", [$_POST['advice_id']]);
            $status = $db->perform("mypha_medicine_advice", $data, 'update', 'medadv_id =' . $medadv_id);
        } else {
            $status = $db->perform("mypha_medicine_advice", $data);
        }
        $medadv_content = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message: 'Details mapped .'}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'adprecaution_load':
        $preadv_id = isset($_POST['preadv_id']) ? intval($_POST['preadv_id']) : 0;
        if ($preadv_id) {
            $sql = "SELECT preadv_id,preadv_content FROM mypha_precaution_advice  WHERE preadv_id= " . $preadv_id;
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
    case 'saveMedicineDetailsData':
        $medicineId = $_POST['MedId'];
        if ($medicineId > 0) {
            $data['medicine_use'] = $_POST['medicine_use'];
            $data['medicine_works'] = $_POST['medicine_works'];
            $data['medicine_sideeffects'] = $_POST['medicine_sideeffects'];
            $data['medicine_morInfo'] = $_POST['medicine_morInfo'];
            $data['medicine_updatedOn'] = date('Y-m-d H:i:s');
            $data['medicine_updatedBy'] = $userid;
            $db->query('begin');
            $status = $db->perform("mypha_medicineMaster", $data, 'update', 'medicineMaster_id =' . $medicineId);
            $status = $db->query('commit');
        }

        $return_rec = $db->getFromDb("SELECT * FROM mypha_medicineMaster WHERE medicineMaster_id = {$medicineId}", true);

        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
    case 'loadMedicineDetailsData':
        $medId = isset($_POST['medicineMaster_id']) ? intval($_POST['medicineMaster_id']) : 0;
        if ($medId) {
            _loadRecordJson("SELECT  medicineMaster_id,medicine_use,medicine_works,medicine_sideeffects,medicine_morInfo  FROM mypha_medicineMaster WHERE medicineMaster_id = " . $medId);
        }
        break;
    case 'loadPrecAdvice':
        $precValue = isset($_POST['precValue']) ? intval($_POST['precValue']) : 0;
        $advValue = isset($_POST['advValue']) ? intval($_POST['advValue']) : 0;
        if ($precValue > 0 && $advValue > 0) {
            $sql = "SELECT preadv_content,advice_id AS adi_id,precaution_id AS pret_id FROM mypha_precaution_advice WHERE advice_id = {$advValue} AND precaution_id = {$precValue}";
            $data = $db->getFromDB($sql, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'verifyDrug':
        $medId = $_POST['medId'];
        $db->query('begin');

        $data = array(
            "isVerified" => 1,
            "verifedOn" => date('Y-m-d H:i:s'),
            "verifedBy" => $userid
        );
        $isVerified = $db->getItemFromDb("SELECT isVerified FROM mypha_medicineMaster WHERE medicineMaster_id = {$medId}");

        if ($medId > 0 && $isVerified == 0) {
            $status = $db->perform("mypha_medicineMaster", $data, 'update', 'medicineMaster_id =' . $medId);
        } else {
            echo "{'success':true,'valid':false,'message': 'Data is already verified.'}";
            exit();
        }
        $return_rec = $db->getFromDb("SELECT medicineMaster_id,medicineMaster_name,manufacture_name,medicine_type_name,composition_name,subCategory_name,medicine_use,medicine_sideeffects,medicine_works,medicine_morInfo,IF((medicine_use <> ''),'Yes','No') AS mustatus,IF((medicine_sideeffects <>  ''),'Yes','No') AS sfstatus,
IF((medicine_works <>  ''),'Yes','No') AS mwstatus,IF((medicine_morInfo <>  ''),'Yes','No') AS mistatus,IF((isVerified = 1),'Yes','No') AS isVerified FROM mypha_medicineMaster "
                . "INNER JOIN  mypha_medicineType ON medicine_type_id = medicine_type "
                . "LEFT JOIN  mypha_composition mc ON composition_id = medicine_composition "
                . "INNER JOIN  mypha_manufacture ON manufacture_id = medicine_manufacture "
                . "LEFT JOIN mypha_subCategory ms on ms.subCategory_id = mc.subCategory_id WHERE medicineMaster_id = {$medId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
}