<?php

switch ($op) {
    case 'listadvice':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'advice_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['mp_medicine', 'mp_precaution', 'mp_status'];
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
          } */
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'advice_status') {
                            if ($field['field'] == 'advice_status') {
                                $fiterItem = ($field['data']['value'] == 'Active') ? 1 : 0;
                                $search .= " and ({$field['field']} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Inactive') {
                                $fiterItem = 0;
                                $search .= " and ({$field['field']} = {$fiterItem}) ";
                            } else {
                                $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                            }
                        }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                        }
                }
            }
        }


        $countQuery = "SELECT COUNT(*) FROM mypha_safety_advice  {$search}";
        $listQuery = "SELECT advice_id,advice_name,IF((advice_status=1),'Active','Inactive') AS advice_status FROM mypha_safety_advice {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'saveAdvice':
        $db->query('begin');
        $data = $_POST['n'];

        if ($data['advice_id'] > 0) {
            $data['advice_updatedOn '] = date('Y-m-d H:i:s');
            $advice_status = $db->perform("mypha_safety_advice", $data, 'update', 'advice_id =' . $data['advice_id']);
            $lastId = $data['advice_id'];
        } else {
            unset($data['advice_id']);
            $data['advice_createdOn '] = date('Y-m-d H:i:s');
            $advice_status = $db->perform('mypha_safety_advice', $data);
            $lastId = $db->insert_id();
        }

        $return_rec = $db->getFromDb("SELECT advice_id,advice_name,advice_status FROM mypha_safety_advice WHERE advice_id  = {$lastId}", true);
        $advice_status = $db->query('commit');
        if ($advice_status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'adviceDetailsView':
        $advice_id = isset($_POST['advice_id']) ? intval($_POST['advice_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($advice_id || $ID) {

            $data = $db->getFromDB("SELECT advice_id,advice_name,advice_status FROM mypha_safety_advice WHERE advice_id  =" . $advice_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'advice_load':
        $advice_id = isset($_POST['advice_id']) ? intval($_POST['advice_id']) : 0;
        if ($advice_id) {
            $sql = "SELECT advice_id,advice_name,advice_status FROM mypha_safety_advice  WHERE advice_id= " . $advice_id;
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
    case 'listprecaution':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'precaution_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
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
          } */
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'precaution_status') {
                            if ($field['field'] == 'precaution_status') {
                                $fiterItem = ($field['data']['value'] == 'Active') ? 1 : 0;
                                $search .= " and ({$field['field']} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Inactive') {
                                $fiterItem = 0;
                                $search .= " and ({$field['field']} = {$fiterItem}) ";
                            } else {
                                $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                            }
                        }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_safety_precaution {$search}";
        $listQuery = "SELECT precaution_id,precaution_name,IF((precaution_status=1),'Active','Inactive') AS precaution_status FROM mypha_safety_precaution {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'savePrecaution':
        $db->query('begin');
        $data = $_POST['n'];

        if ($data['precaution_id'] > 0) {
            $data['precautionUpdatedOn '] = date('Y-m-d H:i:s');
            $precaution_status = $db->perform("mypha_safety_precaution", $data, 'update', 'precaution_id =' . $data['precaution_id']);
            $lastId = $data['precaution_id'];
        } else {
            unset($data['precaution_id']);
            $data['precautionCreatedOn '] = date('Y-m-d H:i:s');
            $precaution_status = $db->perform('mypha_safety_precaution', $data);
            $lastId = $db->insert_id();
        }

        $return_rec = $db->getFromDb("SELECT precaution_id,precaution_name,precaution_status FROM mypha_safety_precaution WHERE precaution_id  = {$lastId}", true);
        $precaution_status = $db->query('commit');
        if ($precaution_status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'precautionDetailsView':
        $precaution_id = isset($_POST['precaution_id']) ? intval($_POST['precaution_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($precaution_id || $ID) {

            $data = $db->getFromDB("SELECT precaution_id,precaution_name,precaution_status FROM mypha_safety_precaution WHERE precaution_id  =" . $precaution_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'precaution_load':
        $precaution_id = isset($_POST['precaution_id']) ? intval($_POST['precaution_id']) : 0;
        if ($precaution_id) {
            $sql = "SELECT precaution_id,precaution_name,precaution_status FROM mypha_safety_precaution WHERE precaution_id= " . $precaution_id;
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


    case 'listadvicePrecaution':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'preadv_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
     
        if (isset($_POST['filter'])) {

            foreach ($_POST['filter'] as $key => $val) {
               if ($val['field'] == 'advice_name') {
                    $advice_name = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(preadv_id),0) FROM mypha_precaution_advice WHERE advice_id IN (SELECT GROUP_CONCAT(advice_id) FROM mypha_safety_advice WHERE advice_name LIKE '{$val['data']['value']}%')");
                    $search .= " AND preadv_id IN({$advice_name}) ";
                } else if ($val['field'] == 'precaution_name') {
                    $precaution_name = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(preadv_id),0) FROM mypha_precaution_advice WHERE precaution_id IN (SELECT GROUP_CONCAT(precaution_id) FROM mypha_safety_precaution WHERE precaution_name LIKE '{$val['data']['value']}%')");
                    $search .= " AND preadv_id  IN({$precaution_name}) ";
                } else {
                    $search .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
                }
            }
        }
        $countQuery = "SELECT COUNT(*) FROM  mypha_precaution_advice {$search}";
    /*    $countQuery = "SELECT COUNT(*) FROM(SELECT SELECT preadv_id,msa.advice_id AS advc_id,advice_name,msp.precaution_id AS prec_id,precaution_name,preadv_content FROM mypha_precaution_advice mpa"
                . " INNER JOIN  mypha_safety_advice msa ON mpa.advice_id = msa.advice_id "
                . " INNER JOIN  mypha_safety_precaution msp ON mpa.precaution_id = msp.precaution_id) {$search}  ORDER BY {$sort} {$dir}";*/
        $listQuery = "SELECT preadv_id,msa.advice_id AS advc_id,advice_name,msp.precaution_id AS prec_id,precaution_name,preadv_content FROM mypha_precaution_advice mpa"
                . " INNER JOIN  mypha_safety_advice msa ON mpa.advice_id = msa.advice_id "
                . " INNER JOIN  mypha_safety_precaution msp ON mpa.precaution_id = msp.precaution_id"
                . " {$search} ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'adviceName':
        $listQuery = "SELECT advice_id AS advc_id,advice_name from mypha_safety_advice";

        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'precautionName':
        $listQuery = "SELECT precaution_id AS prec_id, precaution_name from mypha_safety_precaution";

        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'saveAdvicePrecaution':
        $db->query('begin');
        $data = $_POST['n'];

        if ($data['preadv_id'] > 0) {
            $data['preadv_updatedOn '] = date('Y-m-d H:i:s');
            $adzone_status = $db->perform("mypha_precaution_advice", $data, 'update', 'preadv_id =' . $data['preadv_id']);
            $lastId = $data['preadv_id'];
        } else {
            unset($data['preadv_id']);
            $data['preadv_createdOn '] = date('Y-m-d H:i:s');
            $adzone_status = $db->perform('mypha_precaution_advice', $data);
            $lastId = $db->insert_id();
        }

        $return_rec = $db->getFromDb("SELECT preadv_id,msa.advice_id AS advc_id,advice_name,msp.precaution_id AS prec_id,precaution_name,preadv_content FROM mypha_precaution_advice mpa"
                . " INNER JOIN  mypha_safety_advice msa ON mpa.advice_id = msa.advice_id "
                . " INNER JOIN  mypha_safety_precaution msp ON mpa.precaution_id = msp.precaution_id WHERE preadv_id  = {$lastId}", true);
        $preadv_content = $db->query('commit');
        if ($preadv_content) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'advice_precaution_load':

        $preadv_id = isset($_POST['preadv_id']) ? intval($_POST['preadv_id']) : 0;
        if ($preadv_id) {
            $sql = "SELECT preadv_id,msa.advice_id AS advc_id,advice_name,msp.precaution_id AS prec_id,precaution_name,preadv_content FROM mypha_precaution_advice mpa"
                    . " INNER JOIN  mypha_safety_advice msa ON mpa.advice_id = msa.advice_id "
                    . " INNER JOIN  mypha_safety_precaution msp ON mpa.precaution_id = msp.precaution_id  WHERE preadv_id= " . $preadv_id;
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
    case 'advicePrecautionDetailsView':
        $preaadv_id = isset($_POST['preadv_id']) ? intval($_POST['preadv_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($preaadv_id || $ID) {

            $data = $db->getFromDB("SELECT preadv_id,mpa.advice_id AS advc_id,advice_name,mpa.precaution_id AS prec_id,precaution_name,preadv_content FROM mypha_precaution_advice mpa "
                    . " INNER JOIN  mypha_safety_advice msa ON mpa.advice_id = msa.advice_id "
                    . " INNER JOIN  mypha_safety_precaution msp ON mpa.precaution_id = msp.precaution_id WHERE mpa.preadv_id= " . $preaadv_id, true);

            $data['success'] = true;
            echo json_encode($data);
        }
        break;
}

