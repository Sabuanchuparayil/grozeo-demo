<?php

$userid = $_SESSION['admin']->Finascop_UserId;
switch ($op) {

    case 'listDisease':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'disease_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_disease  {$search}";

        $listQuery = "SELECT disease_id,disease_name,disease_description FROM mypha_disease  {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'saveDisease':
        $db->query('begin');
        $data = $_POST['n'];
        //$data['dept_name'] = $db->getItemFromDB("SELECT dept_name FROM idrl_mst_department WHERE mst_dept_id = {$data['dept_id']}");
        if ($data['disease_id'] > 0) {
            $data['disease_updated_on'] = date('Y-m-d H:i:s');
            $status = $db->perform("mypha_disease", $data, 'update', 'disease_id =' . $data['disease_id']);
            $lastId = $data['disease_id'];
        } else {
            unset($data['disease_id']);
            $data['disease_created_on'] = date('Y-m-d H:i:s');
            $status = $db->perform('mypha_disease', $data);
            $lastId = $db->insert_id();
        }

        $return_rec = $db->getFromDb("SELECT * FROM mypha_disease WHERE disease_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'loadDisease':

        $disease_id = isset($_POST['disease_id']) ? intval($_POST['disease_id']) : 0;
        if ($disease_id) {
            $sql = "SELECT disease_id,disease_name,disease_description FROM mypha_disease  WHERE disease_id= " . $disease_id;
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



        $diseaseId = isset($_POST['diseaseId']) ? intval($_POST['diseaseId']) : 0;
        if ($diseaseId) {
            _loadRecordJson("SELECT disease_id,disease_name,dept_id,dept_name FROM mypha_disease  WHERE disease_id = " . $diseaseId);
        }
        break;
    case 'saveManufacture':

        $db->query('begin');
        $data = $_POST['n'];
        $manufacture_id = $data['manufacture_id'];
        $manufacture_name = $data['manufacture_name'];
        $manufacture_name = addslashes($manufacture_name);


        if ($data['manufacture_id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;

            $manufactureUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_manufacture WHERE manufacture_name ='{$manufacture_name}' AND manufacture_id <> {$manufacture_id} ");
            if ($manufactureUnique > 0) {
                echo "{success: false, message:'This Manufacture already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_manufacture", $data, 'update', 'manufacture_id =' . $data['manufacture_id']);
                $lastId = $data['manufacture_id'];

                $fsim['med_manufacturename'] = $data['manufacture_name'];
                $status = $db->perform('finascop_stock_itemmaster', $fsim_pdt, 'update', " med_manufactureid = {$lastId} AND isMedicine = 1");
                //                $fsui['fsi_brand_name'] = $data['composition_name'];
                //                $status = $db->perform('finascop_stock_uniqueitem', $fsui, 'update', " fsi_brand_id = {$lastId} AND isMedicine = 1");
            }
        } else {
            $manufactureUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_manufacture WHERE manufacture_name ='{$manufacture_name}'  ");
            if ($manufactureUnique > 0) {
                echo "{success: false, message:'This Manufacture already exists.'}";
                exit;
            } else {
                unset($data['manufacture_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mypha_manufacture', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT manufacture_id,manufacture_name,status FROM mypha_manufacture WHERE manufacture_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listManufacture':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'manufacture_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
                }
            }
        }



        $countQuery = "SELECT COUNT(*) FROM mypha_manufacture   {$search}";

        $listQuery = "SELECT manufacture_id,manufacture_name,IF((status=1),'Active','Inactive')AS status FROM mypha_manufacture
        " . "{$search}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'ManufacturedetailsView':
        $manufacture_id = isset($_POST['manufacture_id']) ? intval($_POST['manufacture_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($manufacture_id || $ID) {

            $data = $db->getFromDB("SELECT manufacture_id,manufacture_name,status FROM mypha_manufacture WHERE manufacture_id =" . $manufacture_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'manufacture_form_load':
        $manufacture_id = isset($_POST['manufacture_id']) ? intval($_POST['manufacture_id']) : 0;
        if ($manufacture_id) {
            $sql = "SELECT manufacture_id as textfieldMasterManufactureId,manufacture_name as textfieldMasterManufacture,status as  manufacturStatus FROM mypha_manufacture  WHERE manufacture_id= " . $manufacture_id;
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
    case 'medicinetype_form_load':
        $medicineTypeId = isset($_POST['medicine_type_id']) ? intval($_POST['medicine_type_id']) : 0;
        if ($medicineTypeId) {
            $sql = "SELECT medicine_type_id ,medicine_type_name ,status  as comboMasterMedicineTypesStatus FROM mypha_medicineType  WHERE medicine_type_id= " . $medicineTypeId;
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
    case 'medicinetypesdetailsView':
        $medicineTypeId = isset($_POST['medicine_type_id']) ? intval($_POST['medicine_type_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($medicineTypeId || $ID) {

            $data = $db->getFromDB("SELECT medicine_type_id ,medicine_type_name ,status FROM mypha_medicineType WHERE medicine_type_id= " . $medicineTypeId, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'listMedicineTypes':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'medicine_type_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
                }
            }
        }



        $countQuery = "SELECT COUNT(*) FROM mypha_medicineType   {$search}";

        $listQuery = "SELECT medicine_type_id ,medicine_type_name,IF((status=1),'Active','Inactive')AS status FROM mypha_medicineType
        " . "{$search}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'saveMedicineTypes':
        $db->query('begin');
        $data = $_POST['n'];
        $mediTyp_id = $data['medicine_type_id'];
        $mediTyp_name = $data['medicine_type_name'];
        $mediTyp_name = addslashes($mediTyp_name);


        if ($data['medicine_type_id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;

            $medTypUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_medicineType WHERE medicine_type_name ='{$mediTyp_name}' AND medicine_type_id <> {$mediTyp_id} ");
            if ($medTypUnique > 0) {
                echo "{success: false, message:'This Medicine Type already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_medicineType", $data, 'update', 'medicine_type_id =' . $data['medicine_type_id']);
                $lastId = $data['medicine_type_id'];


                $fsim['dosform_name'] = $data['medicine_type_name'];
                $status = $db->perform('finascop_stock_itemmaster', $fsim, 'update', " dosform_id = {$lastId} AND isMedicine = 1");
                $fsim_cos['cos_package_type_name'] = $data['medicine_type_name'];
                $status = $db->perform('finascop_stock_itemmaster', $fsim_cos, 'update', " cos_package_type_id = {$lastId} AND isMedicine = 1");
                $fsim_ccs['ccs_package_type_name'] = $data['medicine_type_name'];
                $status = $db->perform('finascop_stock_itemmaster', $fsim_ccs, 'update', " ccs_package_type_id = {$lastId} AND isMedicine = 1");
                $fsim_rs['rs_package_type_name'] = $data['medicine_type_name'];
                $status = $db->perform('finascop_stock_itemmaster', $fsim_rs, 'update', " rs_package_type_id = {$lastId} AND isMedicine = 1");
            }
        } else {
            $manufactureUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_medicineType WHERE medicine_type_name ='{$mediTyp_name}'  ");
            if ($manufactureUnique > 0) {
                echo "{success: false, message:'This Medicine Type already exists.'}";
                exit;
            } else {
                unset($data['medicine_type_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mypha_medicineType', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT medicine_type_id ,medicine_type_name,status as comboMasterMedicineTypesStatus FROM mypha_medicineType WHERE medicine_type_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'category_form_load':
        $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        if ($categoryId) {
            $sql = "SELECT category_id ,category_name ,status  as comboMasterCategorysStatus FROM mypha_category  WHERE category_id= " . $categoryId;
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
    case 'categorysdetailsView':
        $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($categoryId || $ID) {

            $data = $db->getFromDB("SELECT category_id ,category_name ,status FROM mypha_category WHERE category_id= " . $categoryId, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'listCategorys':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'category_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
                }
            }
        }



        $countQuery = "SELECT COUNT(*) FROM mypha_category   {$search}";

        $listQuery = "SELECT category_id ,category_name,IF((status=1),'Active','Inactive')AS status FROM mypha_category
        " . "{$search}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'saveCategorys':
        $db->query('begin');
        $data = $_POST['n'];
        $mediCat_id = $data['category_id'];
        $mediCat_name = $data['category_name'];
        $mediCat_name = addslashes($mediCat_name);


        if ($data['category_id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;

            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_category WHERE category_name ='{$mediCat_name}' AND category_id <> {$mediCat_id} ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This Category already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_category", $data, 'update', 'category_id =' . $data['category_id']);
                $lastId = $data['category_id'];
            }
        } else {
            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_category WHERE category_name ='{$mediCat_name}'  ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This Category already exists.'}";
                exit;
            } else {
                unset($data['category_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mypha_category', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT category_id ,category_name,status as comboMasterCategorysStatus FROM mypha_category WHERE category_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listMedicineContents':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'medicineContent_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 AND status = 1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
                }
            }
        }



        $countQuery = "SELECT COUNT(*) FROM mypha_medicineContent   {$search}";

        $listQuery = "SELECT medicineContent_id ,medicineContent_name,medicineContent_uses,medicineContent_works,medicineContent_side_effects,medicineContent_advice,IF((status=1),'Active','Inactive')AS status 
            FROM mypha_medicineContent
        " . "{$search}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'saveMedicineContents':
        $db->query('begin');
        $data = $_POST['n'];
        $mediContent_id = $data['medicineContent_id'];
        $mediContent_name = $data['medicineContent_name'];
        $mediContent_name = addslashes($mediContent_name);


        if ($data['medicineContent_id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;

            $medConntUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_medicineContent WHERE medicineContent_name ='{$mediContent_name}' AND medicineContent_id <> {$mediContent_id} ");
            if ($medConntUnique > 0) {
                echo "{success: false, message:'This Medicine Content already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_medicineContent", $data, 'update', 'medicineContent_id =' . $data['medicineContent_id']);
                $lastId = $data['medicineContent_id'];
            }
        } else {
            $medConntUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_medicineContent WHERE medicineContent_name ='{$mediContent_name}'  ");
            if ($medConntUnique > 0) {
                echo "{success: false, message:'This Medicine Content already exists.'}";
                exit;
            } else {
                unset($data['medicineContent_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mypha_medicineContent', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT medicineContent_id ,medicineContent_name,status as comboMasterMedicineContentsStatus FROM mypha_medicineContent WHERE medicineContent_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'medicineContentsdetailsView':
        $medicineContentId = isset($_POST['medicineContent_id']) ? intval($_POST['medicineContent_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($medicineContentId || $ID) {

            $data = $db->getFromDB("SELECT medicineContent_id ,medicineContent_name,medicineContent_uses,medicineContent_works,medicineContent_side_effects,medicineContent_advice ,status FROM mypha_medicineContent WHERE medicineContent_id= " . $medicineContentId, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'medicineContent_form_load':
        $medicineContentId = isset($_POST['medicineContent_id']) ? intval($_POST['medicineContent_id']) : 0;
        if ($medicineContentId) {
            $sql = "SELECT medicineContent_id ,medicineContent_name,medicineContent_uses,medicineContent_works,medicineContent_side_effects,medicineContent_advice,status  as comboMasterMedicineContentsStatus FROM mypha_medicineContent  WHERE medicineContent_id= " . $medicineContentId;
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
    case 'listwarningCategorys':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'category_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
                }
            }
        }



        $countQuery = "SELECT COUNT(*) FROM mypha_warningCategory   {$search}";

        $listQuery = "SELECT warningCategory_id ,warningCategory_name,IF((warningCategory_status=1),'Active','Inactive')AS status FROM mypha_warningCategory
        " . "{$search}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'warning_category_form_load':
        $warningcategoryId = isset($_POST['warningCategory_id']) ? intval($_POST['warningCategory_id']) : 0;
        if ($warningcategoryId) {
            $sql = "SELECT warningCategory_id ,warningCategory_name ,warningCategory_status  AS comboMasterWarningCategorysStatus FROM mypha_warningCategory  WHERE warningCategory_id='{$warningcategoryId}' ";
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
    case 'warning_form_load':
        $warning_id = isset($_POST['warning_id']) ? intval($_POST['warning_id']) : 0;
        if ($warning_id) {
            $sql = "SELECT warning_id,warning_name ,warning_status  AS comboMasterWarningStatus FROM mypha_warning  WHERE warning_id='{$warning_id}' ";
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

    case 'composition_form_load':
        $composition_id = isset($_POST['composition_id']) ? intval($_POST['composition_id']) : 0;
        if ($composition_id) {
            $sql = "SELECT composition_id,composition_name ,subCategory_id,composition_status  AS comboMasterCompositionStatus FROM mypha_composition  WHERE composition_id='{$composition_id}' ";
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


    case 'use_form_load':
        $meduse_id = isset($_POST['meduse_id']) ? intval($_POST['meduse_id']) : 0;
        if ($meduse_id) {
            $sql = "SELECT meduse_id,meduse_name,meduse_status  AS comboMasterUseStatus FROM mypha_meduse  WHERE meduse_id='{$meduse_id}' ";
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

    case 'unit_form_load':
        $unit_id = isset($_POST['unit_id']) ? intval($_POST['unit_id']) : 0;
        if ($unit_id) {
            $sql = "SELECT unit_id,unit_name,status  AS comboMasterUnitStatus FROM mypha_unit  WHERE unit_id='{$unit_id}' ";
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


    case 'warningcategorysdetailsView':
        $warningcategoryId = isset($_POST['warningCategory_id']) ? intval($_POST['warningCategory_id']) : 0;
        // $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($warningcategoryId) {

            $data = $db->getFromDB("SELECT warningCategory_id ,warningCategory_name ,warningCategory_status  as status FROM mypha_warningCategory WHERE warningCategory_id= " . $warningcategoryId, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'worksdetailsView':
        $medwork_id = isset($_POST['medwork_id']) ? intval($_POST['medwork_id']) : 0;
        // $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($medwork_id) {

            $data = $db->getFromDB("SELECT medwork_id,medwork_name,medwork_status  FROM mypha_medwork WHERE medwork_id= " . $medwork_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;


    case 'usedetailsView':
        $meduse_id = isset($_POST['meduse_id']) ? intval($_POST['meduse_id']) : 0;
        // $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($meduse_id) {

            $data = $db->getFromDB("SELECT meduse_id ,meduse_name,meduse_status  FROM mypha_meduse WHERE meduse_id= " . $meduse_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'unitdetailsView':
        $unit_id = isset($_POST['unit_id']) ? intval($_POST['unit_id']) : 0;
        // $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($unit_id) {

            $data = $db->getFromDB("SELECT unit_id ,unit_name,status  FROM mypha_unit WHERE unit_id= " . $unit_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'sideeffectdetailsView':
        $medsideffect_id = isset($_POST['medsideffect_id']) ? intval($_POST['medsideffect_id']) : 0;
        // $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($medsideffect_id) {

            $data = $db->getFromDB("SELECT medsideffect_id ,medsideffect_name,medsideffect_status  FROM mypha_medsideffect WHERE medsideffect_id= " . $medsideffect_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;



    case 'warningdetailsView':
        $warning_id = isset($_POST['warning_id']) ? intval($_POST['warning_id']) : 0;
        // $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($warning_id) {

            $data = $db->getFromDB("SELECT warning_id ,warning_name ,warning_status  as status FROM mypha_warning WHERE warning_id= " . $warning_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'compositiondetailsView':
        $composition_id = isset($_POST['composition_id']) ? intval($_POST['composition_id']) : 0;
        // $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($composition_id) {

            $data = $db->getFromDB("SELECT composition_id ,composition_name ,composition_status,subCategory_id,"
                . "(SELECT subCategory_name FROM mypha_subCategory msc WHERE msc.subCategory_id = myco.subCategory_id) as subCategory,contraindications,special_precautions,interactions,adverse_drug_reactions  "
                . "FROM mypha_composition myco WHERE composition_id= " . $composition_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'informationdetailsView':
        $medadinfo_id = isset($_POST['medadinfo_id']) ? intval($_POST['medadinfo_id']) : 0;
        // $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($medadinfo_id) {

            $data = $db->getFromDB("SELECT medadinfo_id,medadinfo_name,medadinfo_status  FROM mypha_medadinfo WHERE medadinfo_id= " . $medadinfo_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'diseasedetailsView':
        $disease_id = isset($_POST['disease_id']) ? intval($_POST['disease_id']) : 0;
        // $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($disease_id) {

            $data = $db->getFromDB("SELECT disease_id,disease_name,disease_description  FROM mypha_disease WHERE disease_id= " . $disease_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;


    case 'saveWarningCategorys':
        $db->query('begin');
        $data = $_POST['n'];
        $mediCat_id = $data['warningCategory_id'];
        $mediCat_name = $data['warningCategory_name'];
        $mediCat_name = addslashes($mediCat_name);


        if ($data['warningCategory_id'] > 0) {

            $data['warningCategory_updatedOn'] = date('Y-m-d H:i:s');
            $data['warningCategory_updatedBy'] = $userid;

            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_warningCategory WHERE warningCategory_name ='{$mediCat_name}' AND warningCategory_id <> {$mediCat_id} ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This warning Category already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_warningCategory", $data, 'update', 'warningCategory_id =' . $data['warningCategory_id']);
                $lastId = $data['warningCategory_id'];
            }
        } else {
            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_warningCategory WHERE warningCategory_name ='{$mediCat_name}'  ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This warning Category already exists.'}";
                exit;
            } else {
                unset($data['warningCategory_id']);
                $data['warningCategory_createdOn'] = date('Y-m-d H:i:s');
                $data['warningCategory_createdBy'] = $userid;
                $status = $db->perform('mypha_warningCategory', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT warningCategory_id ,warningCategory_name ,warningCategory_status as comboMasterCategorysStatus FROM mypha_warningCategory WHERE warningCategory_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listwarnings':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'warning_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_warning  {$search}";

        $listQuery = "SELECT warning_id,warning_name,IF((warning_status=1),'Active','Inactive') AS status FROM mypha_warning  {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'saveUses':
        $db->query('begin');
        $data = $_POST['n'];
        $mediCat_id = $data['meduse_id'];
        $mediCat_name = $data['meduse_name'];
        $mediCat_name = addslashes($mediCat_name);


        if ($data['meduse_id'] > 0) {

            $data['meduse_updatedOn'] = date('Y-m-d H:i:s');
            $data['meduse_updatedBy'] = $userid;

            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_meduse WHERE meduse_name ='{$mediCat_name}' AND meduse_id <> {$mediCat_id} ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This Usage already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_meduse", $data, 'update', 'meduse_id =' . $data['meduse_id']);
                $lastId = $data['meduse_id'];
            }
        } else {
            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_meduse WHERE meduse_name ='{$mediCat_name}'  ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This Usage already exists.'}";
                exit;
            } else {
                unset($data['meduse_id']);
                $data['meduse_createdOn'] = date('Y-m-d H:i:s');
                $data['meduse_createdBy'] = $userid;
                $status = $db->perform('mypha_meduse', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT meduse_id ,meduse_name ,meduse_status  FROM mypha_meduse WHERE meduse_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;











    case 'saveWarning':
        $db->query('begin');
        $data = $_POST['n'];
        $mediCat_id = $data['warning_id'];
        $mediCat_name = $data['warning_name'];
        $mediCat_name = addslashes($mediCat_name);


        if ($data['warning_id'] > 0) {

            $data['warning_updatedOn'] = date('Y-m-d H:i:s');
            $data['warning_updatedBy'] = $userid;

            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_warning WHERE warning_name ='{$mediCat_name}' AND warning_id <> {$mediCat_id} ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This warning already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_warning", $data, 'update', 'warning_id =' . $data['warning_id']);
                $lastId = $data['warning_id'];
            }
        } else {
            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_warning WHERE warning_name ='{$mediCat_name}'  ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This warning  already exists.'}";
                exit;
            } else {
                unset($data['warning_id']);
                $data['warning_createdOn'] = date('Y-m-d H:i:s');
                $data['warning_createdBy'] = $userid;
                $status = $db->perform('mypha_warning', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT warning_id ,warning_name ,warning_status as comboMasterWarningStatus FROM mypha_warning WHERE warning_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'listComposition':
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

        $countQuery = "SELECT COUNT(*) FROM(SELECT composition_id,composition_name,subCategory_name,IF((composition_status=1),'Active','Inactive') AS composition_status,"
            . "contraindications,special_precautions,interactions,adverse_drug_reactions,IF((adverse_drug_reactions <> ''),'Yes','No') AS adrstatus,IF((interactions <> ''),'Yes','No') AS isstatus,"
            . "IF((special_precautions <> ''),'Yes','No') AS spstatus,IF((contraindications <> ''),'Yes','No') AS csstatus FROM mypha_composition mc INNER JOIN mypha_subCategory msc ON msc.subCategory_id = mc.subCategory_id WHERE composition_type = 0) AS compCount {$search}";

        $listQuery = "SELECT * FROM(SELECT composition_id,composition_name,subCategory_name,IF((composition_status=1),'Active','Inactive') AS composition_status,"
            . "contraindications,special_precautions,interactions,adverse_drug_reactions,IF((adverse_drug_reactions <> ''),'Yes','No') AS adrstatus,IF((interactions <> ''),'Yes','No') AS isstatus,"
            . "IF((special_precautions <> ''),'Yes','No') AS spstatus,IF((contraindications <> ''),'Yes','No') AS csstatus FROM mypha_composition mc INNER JOIN mypha_subCategory msc ON msc.subCategory_id = mc.subCategory_id  WHERE composition_type = 0) AS listApi {$search} ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;


    case 'saveComposition':
        $db->query('begin');
        $data = $_POST['n'];
        $mediCat_id = $data['composition_id'];
        $mediCat_name = $data['composition_name'];
        $mediCat_name = addslashes($mediCat_name);


        if ($data['composition_id'] > 0) {

            $data['composition_updatedOn'] = date('Y-m-d H:i:s');
            $data['composition_updatedBy'] = $userid;

            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_composition WHERE composition_name ='{$mediCat_name}' AND subCategory_id = {$data['subCategory_id']} AND composition_id <> {$mediCat_id} ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This  single api drug already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_composition", $data, 'update', 'composition_id =' . $data['composition_id']);
                $mapdata['subCategory_id '] = $data['subCategory_id'];
                $mapdata['composition_updatedOn '] = $data['composition_updatedOn'];
                $mapdata['composition_updatedBy '] = $userid;
                $status = $db->perform("mypha_composition", $mapdata, 'update', 'composition_map =' . $data['composition_id']);
                $lastId = $data['composition_id'];


                $fsim['medcompos_name'] = $data['composition_name'];
                $fsim_pdt['stit_brand_name'] = $data['composition_name'];
                $status = $db->perform('finascop_stock_itemmaster', $fsim, 'update', " medcompos_id = {$lastId} AND isMedicine = 1");
                $status = $db->perform('finascop_stock_itemmaster', $fsim_pdt, 'update', " pdt_brand = {$lastId} AND isMedicine = 1");
                //                $fsui['fsi_brand_name'] = $data['composition_name'];
                //                $status = $db->perform('finascop_stock_uniqueitem', $fsui, 'update', " fsi_brand_id = {$lastId} AND isMedicine = 1");
            }
        } else {
            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_composition WHERE composition_name ='{$mediCat_name}' AND subCategory_id = {$data['subCategory_id']} ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This single api drug  already exists.'}";
                exit;
            } else {
                unset($data['composition_id']);
                $data['composition_createdOn'] = date('Y-m-d H:i:s');
                $data['composition_createdBy'] = $userid;
                $status = $db->perform('mypha_composition', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT composition_id,composition_name,subCategory_name,IF((composition_status=1),'Active','Inactive') AS composition_status,"
            . "contraindications,special_precautions,interactions,adverse_drug_reactions,IF((adverse_drug_reactions <> ''),'Yes','No') AS adrstatus,IF((interactions <> ''),'Yes','No') AS isstatus,"
            . "IF((special_precautions <> ''),'Yes','No') AS spstatus,IF((contraindications <> ''),'Yes','No') AS csstatus FROM mypha_composition mc INNER JOIN mypha_subCategory msc ON msc.subCategory_id = mc.subCategory_id  WHERE composition_type = 0 AND composition_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'listUses':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'meduse_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_meduse  {$search}";

        $listQuery = "SELECT meduse_id,meduse_name,IF((meduse_status=1),'Active','Inactive') AS meduse_status  FROM mypha_meduse  {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;


    case 'listSideeffect':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'medsideffect_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_medsideffect  {$search}";

        $listQuery = "SELECT medsideffect_id,medsideffect_name,IF((medsideffect_status=1),'Active','Inactive') AS meduse_status  FROM mypha_medsideffect  {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'saveSideEffect':
        $db->query('begin');
        $data = $_POST['n'];
        $mediCat_id = $data['medsideffect_id'];
        $mediCat_name = $data['medsideffect_name'];
        $mediCat_name = addslashes($mediCat_name);


        if ($data['medsideffect_id'] > 0) {

            $data['medsideffect_updatedOn'] = date('Y-m-d H:i:s');
            $data['medsideffect_updatedBy'] = $userid;

            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_medsideffect WHERE medsideffect_name ='{$mediCat_name}' AND medsideffect_id <> {$mediCat_id} ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This Side effect already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_medsideffect", $data, 'update', 'medsideffect_id =' . $data['medsideffect_id']);
                $lastId = $data['medsideffect_id'];
            }
        } else {
            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_medsideffect WHERE medsideffect_name ='{$mediCat_name}'  ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This Side effect  already exists.'}";
                exit;
            } else {
                unset($data['medsideffect_id']);
                $data['medsideffect_createdOn'] = date('Y-m-d H:i:s');
                $data['medsideffect_createdBy'] = $userid;
                $status = $db->perform('mypha_medsideffect', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT medsideffect_id ,medsideffect_name,medsideffect_status FROM mypha_medsideffect WHERE medsideffect_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'sideeffect_form_load':
        $medsideffect_id = isset($_POST['medsideffect_id']) ? intval($_POST['medsideffect_id']) : 0;
        if ($medsideffect_id) {
            $sql = "SELECT medsideffect_id,medsideffect_name,medsideffect_status  as comboMasterSideeffectStatus FROM mypha_medsideffect  WHERE medsideffect_id= " . $medsideffect_id;
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
    case 'info_form_load':
        $medadinfo_id = isset($_POST['medadinfo_id']) ? intval($_POST['medadinfo_id']) : 0;
        if ($medadinfo_id) {
            $sql = "SELECT medadinfo_id,medadinfo_name,medadinfo_status  AS comboMasterInformationStatus FROM mypha_medadinfo  WHERE medadinfo_id= " . $medadinfo_id;
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



    case 'listWorks':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'medwork_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_medwork  {$search}";

        $listQuery = "SELECT medwork_id,medwork_name,IF((medwork_status=1),'Active','Inactive') AS medwork_status  FROM mypha_medwork  {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'work_form_load':
        $medwork_id = isset($_POST['medwork_id']) ? intval($_POST['medwork_id']) : 0;
        if ($medwork_id) {
            $sql = "SELECT medwork_id,medwork_name ,medwork_status  as comboMasterWorksStatus FROM mypha_medwork  WHERE medwork_id= " . $medwork_id;
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


    case 'saveWork':
        $db->query('begin');
        $data = $_POST['n'];
        $mediCat_id = $data['medwork_id'];
        $mediCat_name = $data['medwork_name'];
        $mediCat_name = addslashes($mediCat_name);


        if ($data['medwork_id'] > 0) {

            $data['medwork_updatedOn'] = date('Y-m-d H:i:s');
            $data['medwork_updatedBy'] = $userid;

            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_medwork WHERE medwork_name ='{$mediCat_name}' AND medwork_id <> {$mediCat_id} ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This Medicine working already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_medwork", $data, 'update', 'medwork_id =' . $data['medwork_id']);
                $lastId = $data['medwork_id'];
            }
        } else {
            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_medwork WHERE medwork_name ='{$mediCat_name}'  ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This Medicine working  already exists.'}";
                exit;
            } else {
                unset($data['medwork_id']);
                $data['medwork_createdOn'] = date('Y-m-d H:i:s');
                $data['medwork_createdBy'] = $userid;
                $status = $db->perform('mypha_medwork', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT medwork_id ,medwork_name,medwork_status FROM mypha_medwork WHERE medwork_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'listInfo':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'medadinfo_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_medadinfo  {$search}";

        $listQuery = "SELECT medadinfo_id,medadinfo_name,IF((medadinfo_status=1),'Active','Inactive') AS medadinfo_status FROM mypha_medadinfo  {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;


    case 'saveInfo':
        $db->query('begin');
        $data = $_POST['n'];
        $mediCat_id = $data['medadinfo_id'];
        $mediCat_name = $data['medadinfo_name'];
        $mediCat_name = addslashes($mediCat_name);


        if ($data['medadinfo_id'] > 0) {

            $data['medadinfo_updatedOn'] = date('Y-m-d H:i:s');
            $data['medadinfo_updatedBy'] = $userid;

            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_medadinfo WHERE medadinfo_name ='{$mediCat_name}' AND medadinfo_id <> {$mediCat_id} ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This Medicine working already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_medadinfo", $data, 'update', 'medadinfo_id =' . $data['medadinfo_id']);
                $lastId = $data['medadinfo_id'];
            }
        } else {
            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_medadinfo WHERE medadinfo_name ='{$mediCat_name}'  ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This Medicine working  already exists.'}";
                exit;
            } else {
                unset($data['medadinfo_id']);
                $data['medadinfo_createdOn'] = date('Y-m-d H:i:s');
                $data['medadinfo_createdBy'] = $userid;
                $status = $db->perform('mypha_medadinfo', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT medadinfo_id ,medadinfo_name,medadinfo_status FROM mypha_medadinfo WHERE medadinfo_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'saveUnit':
        $db->query('begin');
        $data = $_POST['n'];
        $mediCat_id = $data['unit_id'];
        $mediCat_name = $data['unit_name'];
        $mediCat_name = addslashes($mediCat_name);


        if ($data['unit_id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;

            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_unit WHERE unit_name ='{$mediCat_name}' AND unit_id <> {$mediCat_id} ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This Unit already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_unit", $data, 'update', 'unit_id =' . $data['unit_id']);
                $lastId = $data['unit_id'];
            }
        } else {
            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_unit WHERE unit_name ='{$mediCat_name}'  ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This Unit already exists.'}";
                exit;
            } else {
                unset($data['unit_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mypha_unit', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT unit_id ,unit_name ,status  FROM mypha_unit WHERE unit_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'listUnit':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'unit_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_unit  {$search}";

        $listQuery = "SELECT unit_id,unit_name,IF((status=1),'Active','Inactive') AS status FROM mypha_unit  {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
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
    case 'listSubCategorys':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'subCategory_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
                    }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_subCategory  {$search}";

        $listQuery = "SELECT subCategory_id,subCategory_name,(SELECT category_name FROM mypha_category mc WHERE mc.category_id =  msc.category_id) as category_name,IF((status=1),'Active','Inactive') AS status "
            . "FROM mypha_subCategory msc {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'saveSubCategory':
        $db->query('begin');
        $data = $_POST['n'];
        $mediCat_id = $data['subCategory_id'];
        $mediCat_name = $data['subCategory_name'];
        $mediCat_name = addslashes($mediCat_name);


        if ($data['subCategory_id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;

            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_subCategory WHERE subCategory_name ='{$mediCat_name}' AND subCategory_id <> {$mediCat_id} ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This subcategory already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_subCategory", $data, 'update', 'subCategory_id =' . $data['subCategory_id']);
                $lastId = $data['subCategory_id'];

                $fsim['med_drug_groupname'] = $data['subCategory_name'];
                $fsim_cat['stit_category_name'] = $data['subCategory_name'];
                $status = $db->perform('finascop_stock_itemmaster', $fsim, 'update', " med_drug_groupid = {$lastId} AND isMedicine = 1");
                $status = $db->perform('finascop_stock_itemmaster', $fsim_cat, 'update', " product_category = {$lastId} AND isMedicine = 1");
                $fsui['fsi_categry_name'] = $data['subCategory_name'];
                $status = $db->perform('finascop_stock_uniqueitem', $fsui, 'update', " fsi_category_id = {$lastId} AND isMedicine = 1");
            }
        } else {
            $medCatUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_subCategory WHERE subCategory_name ='{$mediCat_name}'  ");
            if ($medCatUnique > 0) {
                echo "{success: false, message:'This subcategory already exists.'}";
                exit;
            } else {
                unset($data['subCategory_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mypha_subCategory', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT subCategory_id ,subCategory_name ,status  FROM mypha_subCategory WHERE subCategory_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'loadSubcategory':
        $subCategory_id = isset($_POST['subCategory_id']) ? intval($_POST['subCategory_id']) : 0;
        if ($subCategory_id) {
            $sql = "SELECT subCategory_id ,subCategory_name ,category_id,status as comboMasterSubcategoryStatus FROM mypha_subCategory WHERE subCategory_id= " . $subCategory_id;
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
    case 'SubcategorydetailsView':
        $subCategory_id = isset($_POST['subCategory_id']) ? intval($_POST['subCategory_id']) : 0;
        if ($subCategory_id) {
            $data = $db->getFromDB("SELECT subCategory_id ,subCategory_name ,category_id,status ,(SELECT category_name FROM mypha_category mc WHERE mc.category_id =  msc.category_id) as category_name "
                . "FROM mypha_subCategory msc  WHERE subCategory_id= " . $subCategory_id, true);
            $data['success'] = true;
            echo json_encode($data);
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
    case 'listRoas':


        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'roa_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_roa  {$search}";
        $listQuery = "SELECT roa_id,roa_name,IF((status=1),'Active','Inactive') AS status FROM mypha_roa 
        " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'roasdetailsView':

        $roa_id = isset($_POST['roa_id']) ? intval($_POST['roa_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($roa_id || $ID) {

            $data = $db->getFromDB("SELECT roa_id,roa_name,status AS status FROM mypha_roa  WHERE roa_id =" . $roa_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'roas_form_load':

        $roa_id = isset($_POST['roa_id']) ? intval($_POST['roa_id']) : 0;
        if ($roa_id) {
            $sql = "SELECT  roa_id,roa_name,status AS comboMasterRoasStatus FROM mypha_roa WHERE roa_id =" . $roa_id;
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


    case 'saveRoas':

        $db->query('begin');
        $data = array(
            "roa_id" => $_POST['id'],
            "roa_name" => $_POST['name'],
            "status" => $_POST['status']
        );
        $roa_id = $data['roa_id'];
        $roa_name = $data['roa_name'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $roa_name = addslashes($roa_name);

        if ($data['roa_id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;


            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_roa WHERE roa_name ='{$roa_name}' AND roa_id!='{$roa_id}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'This Roa already existing.'}";
                exit;
            } else {
                $status = $db->perform("mypha_roa", $data, 'update', 'roa_id =' . $data['roa_id']);
                $lastId = $data['roa_id'];
            }
        } else {

            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_roa WHERE roa_name ='{$roa_name}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'This Roa Name already existing.'}";
                exit;
            } else {
                unset($data['roa_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mypha_roa', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT roa_id,roa_name,status FROM mypha_roa WHERE roa_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listUnitDoseMaster':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'unitdose_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'unitdose_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }


        $countQuery = "SELECT COUNT(*) FROM mypha_unitdose  {$search}";
        $listQuery = "SELECT unitdose_id,(ms.roa_name) as roaname,unitdose_name,(mc.status) AS status,IF(mc.image_url IS NOT NULL,'present','nil') AS image
     FROM mypha_unitdose mc INNER JOIN mypha_roa ms ON ms.roa_id=mc.unitdose_roa "
            . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'unitDose_form_load':
        $unitdose_id = isset($_POST['unitdose_id']) ? intval($_POST['unitdose_id']) : 0;
        if ($unitdose_id) {
            $sql = "SELECT unitdose_id,unitdose_name,unitdose_roa,status as statuscat FROM mypha_unitdose  WHERE unitdose_id = " . $unitdose_id;
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
    case 'UnitDosedetailsView':
        $unitdose_id = isset($_POST['unitdose_id']) ? intval($_POST['unitdose_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($unitdose_id || $ID) {

            $data = $db->getFromDB("SELECT unitdose_id,unitdose_name,unitdose_roa,status,IF(image_url IS NOT NULL,'present','nil') AS image,"
                . "(SELECT roa_name FROM mypha_roa WHERE roa_id = mc.unitdose_roa) as unitdose_roa_name FROM mypha_unitdose mc WHERE unitdose_id =" . $unitdose_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'saveUnitDose':


        $db->query('begin');

        $data = array(
            "unitdose_id" => $_POST['id'],
            "unitdose_name" => $_POST['name'],
            "unitdose_roa" => $_POST['unitdose_roa'],
            "status" => $_POST['status']
        );

        $unitdose_id = $data['unitdose_id'];
        $unitdose_name = $data['unitdose_name'];
        $roa_name = $data['unitdose_roa'];
        $status = $data['status'];
        $unitdose_name = addslashes($unitdose_name);

        if (empty($_POST['id'])) {


            $CategoryUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_unitdose WHERE unitdose_name ='{$unitdose_name}'  ");
            if ($CategoryUnique > 0) {
                echo "{success: false, message:'This  Unit Dose already existing.'}";
                exit;
            } else {
                unset($data['unitdose_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mypha_unitdose', $data);
                $lastId = $db->insert_id();
            }
        } else {


            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;
            $CategoryUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_unitdose WHERE unitdose_name ='{$unitdose_name}' AND unitdose_id!='{$unitdose_id}' ");
            if ($CategoryUnique > 0) {
                echo "{success: false, message:'This  Category already existing.'}";
                exit;
            } else {
                $status = $db->perform("mypha_unitdose", $data, 'update', 'unitdose_id =' . $data['unitdose_id']);
                $lastId = $data['unitdose_id'];
            }
        }

        $return_rec = $db->getFromDb("SELECT unitdose_id,unitdose_name,unitdose_roa,status,IF(image_url IS NOT NULL,'present','nil') AS image,(SELECT roa_name FROM mypha_roa WHERE roa_id = mc.unitdose_roa) as unitdose_roa_name FROM mypha_unitdose mc WHERE unitdose_id = {$lastId}", true);

        $status = $db->query('commit');

        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'loadProductCombo':

        $isMedicine = $_POST['isMedicine'];
        $qry = "SELECT stit_ID, stit_SKU FROM finascop_stock_itemmaster WHERE isMedicine = {$isMedicine}";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'listmappedDiseaseinMedi':
        $disease_id = $_POST['disease_id'];
        if ($disease_id) {
            $countQuery = "SELECT COUNT(*) FROM mypha_mapDiseaseMedicine im "
                . " INNER JOIN finascop_stock_itemmaster fsi ON im.stit_ID = fsi.stit_ID  WHERE im.disease_id={$disease_id} ";

            $listQuery = "SELECT medDise_id,im.stit_ID,stit_SKU,disease_id,is_medicine FROM mypha_mapDiseaseMedicine im  "
                . " INNER JOIN finascop_stock_itemmaster fsi ON im.stit_ID = fsi.stit_ID  WHERE im.disease_id={$disease_id} ";
            $db->printGridJson($countQuery, $listQuery);
        }
        break;

    case 'mapMedicinetoDisease':
        $db->query('begin');

        $isType = $_POST['rb-auto'];
        if ($isType == 'Medicine') {
            $isMedicine = 1;
        } else {
            $isMedicine = 0;
        }

        $medDise_id = $_POST['medDise_id'];
        $disease_id = $_POST['disease_id'];
        $data = array(
            'is_medicine' => $isMedicine,
            'stit_ID' => $_POST['stit_ID'],
            'disease_id' => $_POST['disease_id'],
            'medDise_createdOn' => date('Y-m-d H:i:s'),
            'medDise_createdBy' => $userid
        );
        /* Creating a data array */
        $ingCount = $db->getItemSafe("SELECT COUNT(*) FROM mypha_mapDiseaseMedicine WHERE stit_ID= ?  AND disease_id = {$disease_id}", "i", [$_POST['stit_ID']]);
        if ($ingCount > 0) {
            $medDise_id = $db->getItemSafe("SELECT medDise_id FROM mypha_mapDiseaseMedicine WHERE stit_ID= ? AND disease_id = {$disease_id}", "i", [$_POST['stit_ID']]);
            $status = $db->perform("mypha_mapDiseaseMedicine", $data, 'update', 'medDise_id =' . $medDise_id);
        } else {
            $data['medDise_updatedOn'] = date('Y-m-d H:i:s');
            $data['medDise_updatedBy'] = $userid;
            $status = $db->perform("mypha_mapDiseaseMedicine", $data);
        }

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message: 'Medicine mapped .'}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;

    case 'deleteMedicine':
        $db->query('begin');
        $medDise_id = $_POST['medDise_id'];
        //        $is_medicine = $_POST['is_medicine'];
        $disease_id = $_POST['disease_id'];
        $stit_ID = $_POST['stit_ID'];
        $data['medDise_updatedOn'];
        $del_query = "DELETE FROM mypha_mapDiseaseMedicine WHERE stit_ID= " . intval($_POST['stit_ID']) . "  AND disease_id = {$disease_id}";
        $db->query($del_query);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Medicine deleted successfully '}";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'getDiseaseImage':
        $disease_id = $_POST['disease_id'];
        $qry = "select disease_id,disease_image from mypha_disease where `disease_id`= {$disease_id}";
        $data = $db->getMultipleData($qry, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            $data = '';
            echo '{"success":true,"data":' . json_encode($data) . '}';
        }
        break;

    case 'saveDiseaseImage':
        $db->query('begin');
        $bucketname = $_POST['bucket'];
        $filename = $_POST['uploaded_file_name'];
        $disease_id = $_POST['disease_id'];
        $file_path = $_POST['filepath'];
        $data = array(
            "disease_image" => $file_path,
        );
        $res = $db->perform('mypha_disease', $data, 'update', 'disease_id=' . $disease_id);
        $status = $db->query('commit');

        if ($status) {
            echo "{success: true,msg:'Updated Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;

    case 'get_diseaseimg_s3_details':

        $uploadtype = $_POST['uploadtype'];
        $rid = $_POST['rid'];
        if ($uploadtype == 'disease') {
            $data['di_file_name'] = ($rid . "_1"); /* add extension in js */
            $data['di_albumBucketName'] = AWSBUCKETNAME;
            $data['di_accessKey'] = AWSS3ASSETUPLOADACCESSID;
            $data['di_secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
            $data['di_bucketRegion'] = AWSS3ASSETUPLOADREGION;
            $data['di_oncompleteurl'] = AWSDISEASEDBUCKETFOLDER;
            $data['di_img_path_db'] = $db->getItemFromDB("select disease_image from mypha_disease where `disease_id`= {$rid}");
        }
        echo "{success : true,'data':" . json_encode($data) . "}";
        break;
    case 'listUnitValues':
        $id = intval($_POST['unitId']);
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
        $qry = "SELECT id,value FROM unit_value where unitId = {$id} {$searchitem}";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo json_encode([]);
        break;
    case 'saveUnitValue':
        $unitId = $_POST['unitId'];
        $data['value'] = $_POST['unitValue'];
        $data['unitId'] = $_POST['unitId'];

        $valueId = $db->getItemSafe("SELECT id from unit_value WHERE unitId ='?' AND value = '{$_POST['unitValue']}' ", "i", [$_POST['unitId']]);
        $db->query('begin');
        if ($valueId == 0) {
            $status = $db->perform('unit_value', $data);
            $lastId = $db->insert_id();
        } else {
            $status = $db->perform('unit_value', $data, 'update', " unitId ='{$_POST['unitId']}' AND value = '{$_POST['unitValue']}' ");
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
}
