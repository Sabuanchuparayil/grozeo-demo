<?php

$userid = $_SESSION['admin']->Finascop_UserId;
switch ($op) {
    case 'listmedComposition':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'composition_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
            $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
            $search .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
        }

        $countQuery = "SELECT COUNT(*) FROM(SELECT composition_id,composition_name,subCategory_name,IF((composition_status=1),'Active','Inactive') AS composition_status,"
                . "contraindications,special_precautions,interactions,adverse_drug_reactions,IF((adverse_drug_reactions <> ''),'Yes','No') AS adrstatus,IF((interactions <> ''),'Yes','No') AS isstatus,"
                . "IF((special_precautions <> ''),'Yes','No') AS spstatus,IF((contraindications <> ''),'Yes','No') AS csstatus FROM mypha_composition mc INNER JOIN mypha_subCategory msc ON msc.subCategory_id = mc.subCategory_id WHERE composition_type = 1) AS compCount {$search} ";

        $listQuery = "SELECT * FROM(SELECT composition_id,composition_name,subCategory_name,IF((composition_status=1),'Active','Inactive') AS composition_status,"
                . "contraindications,special_precautions,interactions,adverse_drug_reactions,IF((adverse_drug_reactions <> ''),'Yes','No') AS adrstatus,IF((interactions <> ''),'Yes','No') AS isstatus,"
                . "IF((special_precautions <> ''),'Yes','No') AS spstatus,IF((contraindications <> ''),'Yes','No') AS csstatus FROM mypha_composition mc INNER JOIN mypha_subCategory msc ON msc.subCategory_id = mc.subCategory_id  WHERE composition_type = 1) AS listApi {$search} ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'medCompositiondetailsView':
        $composition_id = isset($_POST['composition_id']) ? intval($_POST['composition_id']) : 0;
        // $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($composition_id) {

            $data = $db->getFromDB("SELECT composition_id ,composition_name ,composition_status,subCategory_id,(SELECT subCategory_name FROM mypha_subCategory msc WHERE msc.subCategory_id = myco.subCategory_id) as subCategory,"
                    . "contraindications,special_precautions,interactions,adverse_drug_reactions  "
                    . "FROM mypha_composition myco WHERE composition_id= " . $composition_id, true);
            $contentIds = $db->getMultipleData("SELECT content_id FROM mypha_medContentComposition WHERE composition_id= " . $composition_id);
            if ($contentIds[0] > 0) {
                $contentIds = implode(',', $contentIds);
                $contentNames = $db->getMultipleData("SELECT composition_name FROM mypha_composition WHERE composition_id IN({$contentIds})");
                $data['content'] = implode(' + ', $contentNames);
            }
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'loadmedComposition':
        $composition_id = isset($_POST['composition_id']) ? intval($_POST['composition_id']) : 0;
        if ($composition_id) {
            $sql = "SELECT composition_id as medComposition_id,composition_name as medCompositionForm_name,subCategory_id as subCategory,composition_status  AS comboMastermedCompositionStatus FROM mypha_composition  WHERE composition_id='{$composition_id}' ";
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
    case 'savemedComposition':
        $db->query('begin');
        $data = $_POST['n'];
        $mediCat_id = $data['composition_id'];
        $mediCat_name = $data['composition_name'];
        $mediCat_name = addslashes($mediCat_name);


        if ($data['composition_id'] > 0) {

            $data['composition_updatedOn'] = date('Y-m-d H:i:s');
            $data['composition_updatedBy'] = $userid;

            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_composition WHERE composition_name ='{$mediCat_name}' AND subCategory_id = {$data['subCategory_id']}  AND composition_id <> {$mediCat_id} ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This multiple api drug already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_composition", $data, 'update', 'composition_id =' . $data['composition_id']);
                //$status = $db->perform("mypha_composition", $data, 'update', 'composition_map =' . $data['composition_id']);
                $lastId = $data['composition_id'];

                $fsim['medcompos_name'] = $data['composition_name'];
                $fsim_pdt['stit_brand_name'] = $data['composition_name'];
                $status = $db->perform('finascop_stock_itemmaster', $fsim, 'update', " medcompos_id = {$lastId} AND isMedicine = 1");
                $status = $db->perform('finascop_stock_itemmaster', $fsim_pdt, 'update', " pdt_brand = {$lastId} AND isMedicine = 1");
                $fsui['fsi_brand_name'] = $data['composition_name'];
                $status = $db->perform('finascop_stock_uniqueitem', $fsui, 'update', " fsi_brand_id = {$lastId} AND isMedicine = 1");
            }
        } else {
            $uuid = $_POST['uuid'];
            $data['composition_type'] = 1;
            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_composition WHERE composition_name ='{$mediCat_name}' AND subCategory_id = {$data['subCategory_id']} ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This  multiple api drug  already exists.'}";
                exit;
            } else {
                unset($data['composition_id']);
                $data['composition_createdOn'] = date('Y-m-d H:i:s');
                $data['composition_createdBy'] = $userid;
                $status = $db->perform('mypha_composition', $data);
                $lastId = $db->insert_id();
                $contents = $db->getMultipleData("SELECT * FROM tmpmypha_medContentComposition WHERE composition_id = '{$uuid}' ", true);
                $c = sizeof($contents[0]);
                if ($c > 0) {
                    foreach ($contents as $content) {
                        $codata['content_id'] = $content['content_id'];
                        $codata['composition_id'] = $lastId;
                        $status = $db->perform('mypha_medContentComposition', $codata);
                    }
                    $status = $db->query("DELETE FROM tmpmypha_medContentComposition WHERE composition_id = '{$uuid}'");
                }
            }
        }

        $return_rec = $db->getFromDb("SELECT composition_id,composition_name,subCategory_name,IF((composition_status=1),'Active','Inactive') AS composition_status,"
                . "contraindications,special_precautions,interactions,adverse_drug_reactions,IF((adverse_drug_reactions <> ''),'Yes','No') AS adrstatus,IF((interactions <> ''),'Yes','No') AS isstatus,"
                . "IF((special_precautions <> ''),'Yes','No') AS spstatus,IF((contraindications <> ''),'Yes','No') AS csstatus FROM mypha_composition mc INNER JOIN mypha_subCategory msc ON msc.subCategory_id = mc.subCategory_id  WHERE composition_type = 1 AND composition_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'medSubCategory':
        $typeAhead = '';
        $qry = "SELECT subCategory_id, subCategory_name FROM mypha_subCategory WHERE status = 1   $typeAhead ORDER BY subCategory_name ASC";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getContentsofComposition':
        if ($_POST['composition_id'] > 0) {
            $qry = "SELECT MEDCOM.composition_id ,content_id,composition_name AS content_name FROM mypha_medContentComposition MEDCOM 
INNER JOIN mypha_composition MYCOMP ON MYCOMP.composition_id = MEDCOM.content_id 
 WHERE MEDCOM.composition_id = {$_POST['composition_id']}";
            $items = $db->getMultipleData($qry, true);
        } else {
            $qry = "SELECT MEDCOM.composition_id ,content_id,composition_name AS content_name FROM tmpmypha_medContentComposition MEDCOM 
INNER JOIN mypha_composition MYCOMP ON MYCOMP.composition_id = MEDCOM.content_id 
 WHERE MEDCOM.composition_id =  '{$_POST['uuid']}'";
            $items = $db->getMultipleData($qry, true);
        }

        if (!empty($items)) {
            echo '{"totalCount":' . count($items) . ',"data":' . json_encode($items) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'medContents':
        $typeAhead = '';
        $qry = "SELECT composition_id ,composition_name FROM mypha_composition WHERE composition_status = 1 and composition_type in(0,2)  $typeAhead ORDER BY composition_name ASC";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'mapContenttoComposition':
        $db->query('begin');
        if ($_POST['composition_id'] > 0) {
            $count = $db->getItemSafe("SELECT COUNT(*) FROM mypha_medContentComposition WHERE composition_id = ? AND content_id = {$_POST['content_id']}", "i", [$_POST['composition_id']]);
            if ($count > 0) {
                $status = $db->perform('mypha_medContentComposition', $data, 'update', "composition_id = " . intval($_POST['composition_id']));
            } else {
                $data['composition_id'] = $_POST['composition_id'];
                $data['content_id'] = $_POST['content_id'];
                $status = $db->perform('mypha_medContentComposition', $data);
                $countConten = $db->getItemSafe("SELECT COUNT(*) FROM mypha_composition WHERE composition_map = ?", "i", [$_POST['composition_id']]);
                if ($countConten > 0) {
                    $contents = $db->getMultipleData("SELECT composition_id,composition_name FROM mypha_composition WHERE composition_map = {$_POST['composition_id']}", true);
                    $c = sizeof($contents[0]);
                    if ($c > 0) {
                        foreach ($contents as $content) {
                            $codata['composition_id'] = $content['composition_id'];
                            $codata['content_id'] = $_POST['content_id'];
                            $status = $db->perform('mypha_medContentComposition', $codata);
                        }
                    }
                }
            }
        } else {
            $data['content_id'] = $_POST['content_id'];
            $count = $db->getItemSafe("SELECT COUNT(*) FROM tmpmypha_medContentComposition WHERE composition_id = ? AND content_id = {$_POST['content_id']}", "i", [$_POST['uuid']]);
            if ($count > 0) {
                $status = $db->perform('tmpmypha_medContentComposition', $data, 'update', " composition_id = '{$_POST['uuid']}'");
            } else {
                $data['composition_id'] = $_POST['uuid'];
                $status = $db->perform('tmpmypha_medContentComposition', $data);
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved '}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'deleteContent':
        $db->query('begin');
        if ($_POST['composition_id'] > 0) {
            $status = $db->executeSafe("DELETE FROM mypha_medContentComposition WHERE composition_id = ? AND content_id = {$_POST['content_id']} ", "i", [$_POST['composition_id']]);
            $count = $db->getItemSafe("SELECT COUNT(*) FROM mypha_composition WHERE composition_map = ?", "i", [$_POST['composition_id']]);
            if ($count > 0) {
                $contents = $db->getMultipleData("SELECT composition_id,composition_name FROM mypha_composition WHERE composition_map = {$_POST['composition_id']}", true);
                $c = sizeof($contents[0]);
                if ($c > 0) {
                    foreach ($contents as $content) {
                        $status = $db->executeSafe("DELETE FROM mypha_medContentComposition WHERE composition_id = '{$content['composition_id']}' AND content_id = ? ", "i", [$_POST['content_id']]);
                    }
                }
            }
        } else {
            $status = $db->executeSafe("DELETE FROM tmpmypha_medContentComposition WHERE composition_id = ? AND content_id = {$_POST['content_id']} ", "s", [$_POST['uuid']]);
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Content deleted'}";
        } else {
            echo "{'success':false,'valid':false,'message': 'Error While deleting.'}";
        }
        break;
    case 'uploadcsvFile':
        $table = $_POST['csv_table'];
        $file = $_FILES['excel_file']['tmp_name'];
        $newPath = str_replace('tmp', 'dev/shm', $file);
        copy($file, $newPath);
        $row = 0;
        $csvData = array();
        if (($handle = fopen($newPath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                $csvData[$row] = $data;

                $num = count($data);
                $row++;
            }
            fclose($handle);
        }
        $db->query('begin');
        switch ($table) {
            case 'Category':
                foreach ($csvData as $key => $value) {
                    if ($key > 0) {
                        $value[1] = str_replace("'", "\'", $value[1]);
                        $catagory['category_name'] = $value[1];
                        $catagory['created_on'] = date("Y-m-d H:i:s");
                        $catagory['created_by'] = $userid;
                        $dupCategory = $db->getItemFromDB("SELECT COUNT(*) FROM mypha_category WHERE category_name = '{$value[1]}'");
                        if ($dupCategory < 1) {
                            $status = $db->perform("mypha_category", $catagory);
                        }
                    }
                }
                break;
            case 'Manufacture':
                foreach ($csvData as $key => $value) {
                    if ($key > 0) {
                        $value[1] = str_replace("'", "\'", $value[1]);
                        $manufacture['manufacture_name'] = $value[1];
                        $manufacture['created_on'] = date("Y-m-d H:i:s");
                        $manufacture['created_by'] = $userid;
                        $dupManufa = $db->getItemFromDB("SELECT COUNT(*) FROM mypha_manufacture WHERE manufacture_name = '{$value[1]}'");
                        if ($dupManufa < 1) {
                            $status = $db->perform("mypha_manufacture", $manufacture);
                        }
                    }
                }
                break;
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"valid":true}';
        } else {
            // var_dup($error);
            echo '{"success":false,"valid":false}';
        }

        break;
    case 'loadSAPCDetailsData':
        $composition_id = isset($_POST['composition_id']) ? intval($_POST['composition_id']) : 0;
        if ($composition_id) {
            $sql = "SELECT contraindications,special_precautions,interactions,adverse_drug_reactions FROM mypha_composition  WHERE composition_id='{$composition_id}' ";
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
    case 'saveAPIContentDetailsData':
        $db->query('begin');
        $data['contraindications'] = $_POST['contraindications'];
        $data['interactions'] = $_POST['interactions'];
        $data['special_precautions'] = $_POST['special_precautions'];
        $data['adverse_drug_reactions'] = $_POST['adverse_drug_reactions'];

        if ($_POST['composition_id'] > 0) {
            $data['composition_updatedOn'] = date('Y-m-d H:i:s');
            $data['composition_updatedBy'] = $userid;
            $status = $db->perform("mypha_composition", $data, 'update', 'composition_id = ' . intval($_POST['composition_id']));
            $lastId = $_POST['composition_id'];
        }
        $return_rec = $db->getFromDb("SELECT composition_id ,composition_name ,composition_status as comboMasterCompositionStatus FROM mypha_composition WHERE composition_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'mapStrengthtoApi':
        $type = $_POST['type'];
        $sapiId = $_POST['sapiId'];
        $SAPI_name = $db->getFromDB("SELECT composition_name,subCategory_id,contraindications,special_precautions,interactions,adverse_drug_reactions FROM mypha_composition WHERE composition_id = {$sapiId}", true);
        $composition_name = $SAPI_name['composition_name'] . ' (' . $_POST['medStrength'] . ')';
        $data['composition_type'] = 2;
        $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_composition WHERE composition_name ='{$composition_name}' AND subCategory_id = {$SAPI_name['subCategory_id']} AND composition_status = 1");
        $db->query('begin');
        if ($medCatUnique > 0) {
            echo "{success: false, message:'This  API drug  already exists.'}";
            exit;
        } else {
            unset($data['composition_id']);
            $data['composition_name'] = $composition_name;
            $data['subCategory_id'] = $SAPI_name['subCategory_id'];
            $data['composition_strength'] = $_POST['medStrength'];
            $data['contraindications'] = $SAPI_name['contraindications'];
            $data['special_precautions'] = $SAPI_name['special_precautions'];
            $data['interactions'] = $SAPI_name['interactions'];
            $data['adverse_drug_reactions'] = $SAPI_name['adverse_drug_reactions'];
            $data['composition_map'] = $sapiId;
            $data['composition_createdOn'] = date('Y-m-d H:i:s');
            $data['composition_createdBy'] = $userid;
            $status = $db->perform('mypha_composition', $data);
            $lastId = $db->insert_id();
            if ($type == 'multiple') {
                $contents = $db->getMultipleData("SELECT composition_id,content_id FROM mypha_medContentComposition WHERE composition_id = {$sapiId}", true);
                $c = sizeof($contents[0]);
                if ($c > 0) {
                    foreach ($contents as $content) {
                        $codata['content_id'] = $content['content_id'];
                        $codata['composition_id'] = $lastId;
                        $status = $db->perform('mypha_medContentComposition', $codata);
                    }
                }
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ' }";
        } else {
            echo "{'success':false,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listApistrengths':
        $composition_map = $_POST['composition_map'];
        if ($composition_map) {
            $countQuery = "SELECT COUNT(*) FROM mypha_composition WHERE composition_map = {$composition_map} AND composition_type = 2";
            $listQuery = "SELECT composition_id,composition_name,composition_strength,composition_map,composition_status,IF(composition_status =1,'Active','Inactive') as statuscomp FROM  mypha_composition WHERE composition_map = {$composition_map} AND composition_type = 2";
            $db->printGridJson($countQuery, $listQuery);
        }
        break;
    case 'compstatusChange':
        $composition_id = $_POST['composition_id'];
        $status = $_POST['composition_status'];
        if ($status == 1) {
            $data['composition_status'] = 0;
        } else {
            $data['composition_status'] = 1;
        }
        $db->query('begin');
        if ($composition_id > 0) {
            $con = ' composition_id =' . intval($composition_id);
            $data['composition_updatedOn'] = date('Y-m-d H:i:s');
            $data['composition_updatedBy'] = $_SESSION['admin']->Finascop_UserId;

            $status = $db->perform(FINASCOP_DB . "mypha_composition", $data, 'update', $con);
            $message = "Status Changed.";
        }

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Status Changed.'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }
        break;
}