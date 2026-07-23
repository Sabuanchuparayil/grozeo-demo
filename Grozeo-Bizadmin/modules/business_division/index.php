<?php

$userid = $_SESSION['admin']->Finascop_UserId;

switch ($op) {
    case 'listZone':


        $rec_limit = empty($_POST['limit']) ? 21 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE typeId = 2 ";
        $searchitem = " WHERE 1=1 ";
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
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }

        $subquery = "SELECT id as zone_id,name as zone_name,IF((status=1),'Active','Inactive') AS status FROM division {$search} ";
        $countQuery = "SELECT COUNT(*) FROM ({$subquery}) as zoneCount  {$searchitem}";
        $listQuery = "SELECT * FROM ({$subquery}) AS zoneList {$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'zonesdetailsView':

        $zone_id = isset($_POST['zone_id']) ? intval($_POST['zone_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($zone_id || $ID) {

            $data = $db->getFromDB("SELECT id as zone_id,name as zone_name,status AS status FROM division  WHERE id =" . $zone_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'zones_form_load':

        $zone_id = isset($_POST['zone_id']) ? intval($_POST['zone_id']) : 0;
        if ($zone_id) {
            $sql = "SELECT  id as zone_id,name as zone_name,status AS comboZoneStatus FROM division WHERE id =" . $zone_id;
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


    case 'saveZones':

        $db->query('begin');
        $data = array(
            "id" => $_POST['id'],
            "name" => $_POST['name'],
            "typeId" => 2,
            "parentId" => 1,
            "status" => $_POST['status']
        );
        $zone_id = $data['id'];
        $zone_name = $data['name'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $zone_name = addslashes($zone_name);

        if ($data['id'] > 0) {

            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $userid;


            $zoneUnique = $db->getItemFromDB("SELECT COUNT(*) from division WHERE name ='{$zone_name}' AND id != '{$zone_id}' ");
            if ($zoneUnique > 0) {
                echo "{success: false, message:'Zone name already exists.'}";
                exit;
            } else {
                $status = $db->perform("division", $data, 'update', 'id =' . $data['id']);
                $lastId = $data['id'];
            }
        } else {

            $zoneUnique = $db->getItemFromDB("SELECT COUNT(*) from division WHERE name ='{$zone_name}' ");
            if ($zoneUnique > 0) {
                echo "{success: false, message:'Zone name already exists.'}";
                exit;
            } else {
                unset($data['id']);
                $data['createdOn'] = date('Y-m-d H:i:s');
                $data['createdBy'] = $userid;
                $status = $db->perform('division', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT id as zone_id,name as zone_name,status FROM division WHERE id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
    case 'getZones':
        $qry = "select id,name from  division where status= 1 and typeId = 2  order by name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'listRegion':


        $rec_limit = empty($_POST['limit']) ? 21 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE d1.typeId = 3 ";
        $searchitem = " WHERE 1=1 ";
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
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }
        $subquery = "SELECT d1.id AS region_id,d1.`name` AS region_name,d1.parentId,d2.`name` AS zone_name,d1.typeId as typeId,IF((d1.status=1),'Active','Inactive') AS status FROM division d1 
LEFT JOIN division d2 ON d1.parentId = d2.id {$search} ";
        $countQuery = "SELECT COUNT(*) FROM ({$subquery}) AS regionCount {$searchitem}";
        $listQuery = "SELECT * FROM ({$subquery}) AS regionList {$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'regionsdetailsView':

        $region_id = isset($_POST['region_id']) ? intval($_POST['region_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($region_id || $ID) {

            $data = $db->getFromDB("SELECT d1.id AS region_id,d1.`name` AS region_name,d1.parentId,d2.`name` AS zone_name,d1.status AS status FROM division d1 
LEFT JOIN division d2 ON d1.parentId = d2.id WHERE d1.id =" . $region_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'regions_form_load':

        $region_id = isset($_POST['region_id']) ? intval($_POST['region_id']) : 0;
        if ($region_id) {
            $sql = "SELECT d1.id AS region_id,d1.`name` AS region_name,d1.parentId as regionZoneId,d2.`name` AS zone_name,d1.`status` AS comboRegionStatus FROM division d1 
LEFT JOIN division d2 ON d1.parentId = d2.id WHERE d1.id = " . $region_id;
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
    case 'saveRegions':

        $db->query('begin');
        $data = array(
            "id" => $_POST['id'],
            "name" => $_POST['name'],
            "typeId" => 3,
            "parentId" => $_POST['parentId'],
            "status" => $_POST['status']
        );
        $region_id = $data['id'];
        $region_name = $data['name'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $region_name = addslashes($region_name);

        if ($data['id'] > 0) {

            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $userid;


            $regionUnique = $db->getItemSafe("SELECT COUNT(*) from division WHERE name ='{$region_name}' AND parentId = ? AND id != '{$region_id}' ", "i", [$_POST['parentId']]);
            if ($regionUnique > 0) {
                echo "{success: false, message:'Region name already exists.'}";
                exit;
            } else {
                $status = $db->perform("division", $data, 'update', 'id =' . $data['id']);
                $lastId = $data['id'];
            }
        } else {

            $regionUnique = $db->getItemSafe("SELECT COUNT(*) from division WHERE name ='{$zone_name}' AND parentId = ? ", "i", [$_POST['parentId']]);
            if ($regionUnique > 0) {
                echo "{success: false, message:'Region name already exists.'}";
                exit;
            } else {
                unset($data['id']);
                $data['createdOn'] = date('Y-m-d H:i:s');
                $data['createdBy'] = $userid;
                $status = $db->perform('division', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT  id as region_id,name as region_name,parentId as regionZoneId,(SELECT name FROM division WHERE id = parentId) as zone_name,status AS comboRegionStatus FROM division WHERE id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
    case 'getRegion':
        if ($_POST['zoneId'] > 0) {
            $zoneId = $_POST['zoneId'];
        } else {
            $zoneId = 0;
        }
        $qry = "select id,name FROM division where status= 1 AND typeId = 3 AND parentId = {$zoneId}  order by name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'listTerritory':
        $rec_limit = empty($_POST['limit']) ? 21 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE d1.typeId = 4 ";
        $searchitem = " WHERE 1=1 ";
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
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }
        $subquery = "SELECT 
    d1.id AS territory_id,d1.typeId as typeId,
    d1.name AS territory_name,
    d1.parentId AS territoryRegionId,
    d2.NAME AS region_name,
    d2.parentId AS territoryZoneId,
    d3.NAME AS zone_name,
    d1.status AS comboTerritoryStatus,IF((d1.status=1),'Active','Inactive') AS status
FROM 
    division d1
LEFT JOIN 
    division d2 ON d1.parentId = d2.id
LEFT JOIN 
    division d3 ON d2.parentId = d3.id {$search} ";
        $countQuery = "SELECT COUNT(*) FROM ({$subquery}) as territoryCount {$searchitem}";
        $listQuery = "SELECT * FROM ({$subquery}) AS territoryList {$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'territorysdetailsView':

        $territory_id = isset($_POST['territory_id']) ? intval($_POST['territory_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($territory_id || $ID) {

            $data = $db->getFromDB("SELECT 
    d1.id AS territory_id,
    d1.NAME AS territory_name,
    d1.parentId AS territoryRegionId,
    d2.NAME AS region_name,
    d2.parentId AS territoryZoneId,
    d3.NAME AS zone_name,
    d1.STATUS AS comboTerritoryStatus
FROM 
    division d1
LEFT JOIN 
    division d2 ON d1.parentId = d2.id
LEFT JOIN 
    division d3 ON d2.parentId = d3.id
WHERE 
    d1.id =" . $territory_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'territorys_form_load':

        $territory_id = isset($_POST['territory_id']) ? intval($_POST['territory_id']) : 0;
        if ($territory_id) {
            $sql = "SELECT 
    d1.id AS territory_id,
    d1.NAME AS territory_name,
    d1.parentId AS territoryRegionId,
    d2.NAME AS region_name,
    d2.parentId AS territoryZoneId,
    d3.NAME AS zone_name,
    d1.STATUS AS comboTerritoryStatus
FROM 
    division d1
LEFT JOIN 
    division d2 ON d1.parentId = d2.id
LEFT JOIN 
    division d3 ON d2.parentId = d3.id
WHERE 
    d1.id =" . $territory_id;
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
    case 'saveTerritorys':

        $db->query('begin');
        $data = array(
            "id" => $_POST['id'],
            "name" => $_POST['name'],
            "typeId" => 4,
            "parentId" => $_POST['parentId'],
            "status" => $_POST['status']
        );
        $region_id = $data['id'];
        $region_name = $data['name'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $region_name = addslashes($region_name);

        if ($data['id'] > 0) {

            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $userid;


            $regionUnique = $db->getItemSafe("SELECT COUNT(*) from division WHERE name ='{$region_name}' AND parentId = ? AND id != '{$region_id}' ", "i", [$_POST['parentId']]);
            if ($regionUnique > 0) {
                echo "{success: false, message:'Territory name already exists.'}";
                exit;
            } else {
                $status = $db->perform("division", $data, 'update', 'id =' . $data['id']);
                $lastId = $data['id'];
            }
        } else {

            $regionUnique = $db->getItemSafe("SELECT COUNT(*) from division WHERE name ='{$region_name}' AND parentId = ? ", "i", [$_POST['parentId']]);
            if ($regionUnique > 0) {
                echo "{success: false, message:'Territory name already exists.'}";
                exit;
            } else {
                unset($data['id']);
                $data['createdOn'] = date('Y-m-d H:i:s');
                $data['createdBy'] = $userid;
                $status = $db->perform('division', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT  id as region_id,name as region_name,parentId as regionZoneId,(SELECT name FROM division WHERE id = parentId) as zone_name,status AS comboRegionStatus FROM division WHERE id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
    case 'listDepartment':


        $rec_limit = empty($_POST['limit']) ? 21 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'id' : $sort;
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
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM org_department  {$search}";
        $listQuery = "SELECT id as department_id,name as department_name,IF((status=1),'Active','Inactive') AS status,parentId AS isParent,parentId as parentDepartment,IF(parentId>0,(SELECT NAME FROM org_department WHERE id = parentId),'-') AS parentDepartmentName FROM org_department 
            " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'departmentsdetailsView':

        $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($department_id || $ID) {

            $data = $db->getFromDB("SELECT id as department_id,name as department_name,status AS status,IF(parentId>0,0,1) AS isParent,parentId as parentDepartment,IF(parentId > 0,(SELECT `name` FROM org_department pd WHERE pd.id = od.parentId),'-') AS parentDepartmentName FROM org_department od  WHERE id =" . $department_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'departments_form_load':

        $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
        if ($department_id) {
            $sql = "SELECT  id as department_id,name as department_name,status AS comboDepartmentStatus,IF(parentId>0,0,1) AS isParent,parentId as parentDepartment,IF(parentId > 0,(SELECT `name` FROM org_department pd WHERE pd.id = od.parentId),'-') AS parentDepartmentName FROM org_department od WHERE id =" . $department_id;
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


    case 'saveDepartments':

        $db->query('begin');
        $data = array(
            "id" => $_POST['id'],
            "name" => $_POST['name'],
            "parentId" => ($_POST['parentId'] > 0 ? $_POST['parentId'] : 0),
            "status" => $_POST['status']
        );
        $department_id = $data['id'];
        $department_name = $data['name'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $department_name = addslashes($department_name);

        if ($data['id'] > 0) {

            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $userid;


            $departmentUnique = $db->getItemFromDB("SELECT COUNT(*) from org_department WHERE name ='{$department_name}' AND id != '{$department_id}' ");
            if ($departmentUnique > 0) {
                echo "{success: false, message:'Department name already exists.'}";
                exit;
            } else {
                $status = $db->perform("org_department", $data, 'update', 'id =' . $data['id']);
                $lastId = $data['id'];
            }
        } else {

            $departmentUnique = $db->getItemFromDB("SELECT COUNT(*) from org_department WHERE name ='{$department_name}' ");
            if ($departmentUnique > 0) {
                echo "{success: false, message:'Department name already exists.'}";
                exit;
            } else {
                unset($data['id']);
                $data['createdOn'] = date('Y-m-d H:i:s');
                $data['createdBy'] = $userid;
                $status = $db->perform('org_department', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT id as department_id,name as department_name,status FROM org_department WHERE id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
    case 'getParentDepartment':

        $qry = "select id,name from org_department WHERE parentId = 0 order by name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'listDesignation':


        $rec_limit = empty($_POST['limit']) ? 21 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE  1=1 ";
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
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM org_designation  {$search}";
        $listQuery = "SELECT id as designation_id,name as designation_name,(SELECT title FROM sys_role WHERE RoleId = d.roleId) as role_name,IF((status=1),'Active','Inactive') AS status FROM org_designation d 
                    " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'designationsdetailsView':

        $designation_id = isset($_POST['designation_id']) ? intval($_POST['designation_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($designation_id || $ID) {

            $data = $db->getFromDB("SELECT id as designation_id,name as designation_name,roleId,(SELECT title FROM sys_role WHERE RoleId = d.roleId) as role_name,status AS status FROM org_designation d  WHERE id =" . $designation_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'designations_form_load':

        $designation_id = isset($_POST['designation_id']) ? intval($_POST['designation_id']) : 0;
        if ($designation_id) {
            $sql = "SELECT  id as designation_id,name as designation_name,roleId as designationRoleId,(SELECT title FROM sys_role WHERE RoleId = d.roleId) as role_name,status AS comboDesignationStatus FROM org_designation d WHERE id =" . $designation_id;
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
    case 'saveDesignations':

        $db->query('begin');
        $data = array(
            "id" => $_POST['id'],
            "name" => $_POST['name'],
            "roleId" => $_POST['parentId'],
            "status" => $_POST['status']
        );
        $designation_id = $data['id'];
        $designation_name = $data['name'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $designation_name = addslashes($designation_name);

        if ($data['id'] > 0) {

            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $userid;


            $designationUnique = $db->getItemSafe("SELECT COUNT(*) from org_designation WHERE name ='{$designation_name}' AND roleId = ? AND id != '{$designation_id}' ", "i", [$_POST['parentId']]);
            if ($designationUnique > 0) {
                echo "{success: false, message:'Designation name already exists.'}";
                exit;
            } else {
                $status = $db->perform("org_designation", $data, 'update', 'id =' . $data['id']);
                $lastId = $data['id'];
            }
        } else {

            $designationUnique = $db->getItemSafe("SELECT COUNT(*) from org_designation WHERE name ='{$designation_name}' AND roleId = ? ", "i", [$_POST['parentId']]);
            if ($designationUnique > 0) {
                echo "{success: false, message:'Designation name already exists.'}";
                exit;
            } else {
                unset($data['id']);
                $data['createdOn'] = date('Y-m-d H:i:s');
                $data['createdBy'] = $userid;
                $status = $db->perform('org_designation', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT  id as designation_id,name as designation_name,roleId as designationRoleId,(SELECT RoleName FROM sys_role WHERE RoleId = d.roleId) as role_name,status AS comboDesignationStatus FROM org_designation d WHERE id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
    case 'getRoles':
        $qry = "select RoleId as id,title as name from  sys_role order by RoleName";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'listAreaDivision':
        $rec_limit = empty($_POST['limit']) ? 21 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'division.id' : $sort;
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
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM  (SELECT area_entries.id AS areaId,d1.id AS areaDivision_id,
        IF(d1.name IS NULL,areaName,d1.name) AS areaDivision_name,d1.parentId AS areaDivisionTerritoryId,
        d2.name AS territory_name,d2.parentId AS areaDivisionRegionId,d3.name AS region_name,
        d3.parentId AS areaDivisionZoneId,d4.name AS zone_name,IF((d1.status=1),'Active','Inactive') AS status 
        FROM area_entries LEFT JOIN division d1 ON d1.id = area_entries.divisionId AND d1.typeId = 5 
        LEFT JOIN division d2 ON d1.parentId = d2.id LEFT JOIN division d3 ON d2.parentId = d3.id LEFT JOIN division d4 ON d3.parentId = d4.id) AS areaDivisionCount {$search}{$searchitem}";
        $listQuery = "SELECT * FROM (SELECT area_entries.id AS areaId,d1.id AS areaDivision_id,
        IF(d1.name IS NULL,areaName,d1.name) AS areaDivision_name,d1.parentId AS areaDivisionTerritoryId,
        d2.name AS territory_name,d2.parentId AS areaDivisionRegionId,d3.name AS region_name,
        d3.parentId AS areaDivisionZoneId,d4.name AS zone_name,IF((d1.status=1),'Active','Inactive') AS status 
        FROM area_entries LEFT JOIN division d1 ON d1.id = area_entries.divisionId AND d1.typeId = 5 
        LEFT JOIN division d2 ON d1.parentId = d2.id LEFT JOIN division d3 ON d2.parentId = d3.id LEFT JOIN division d4 ON d3.parentId = d4.id) AS areaDivisionList {$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'areaDivisionsdetailsView':

        $areaDivision_id = isset($_POST['areaDivision_id']) ? intval($_POST['areaDivision_id']) : 0;
        $areaId = isset($_POST['areaId']) ? intval($_POST['areaId']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($areaDivision_id || $areaId) {

            $data = $db->getFromDB("SELECT 
        d1.id AS areaDivision_id,
        IF(d1.name IS NULL,areaName,d1.name) AS areaDivision_name,
        d1.parentId AS areaDivisionTerritoryId,
        d2.NAME AS territory_name,
        d2.parentId AS areaDivisionRegionId,
        d3.NAME AS region_name,
        d3.parentId AS areaDivisionZoneId,
        d4.NAME AS zone_name,
        d1.STATUS AS comboAreaDivisionStatus
    FROM area_entries ar
         LEFT JOIN division d1  ON d1.id = ar.divisionId AND d1.typeId = 5 
    LEFT JOIN 
        division d2 ON d1.parentId = d2.id
    LEFT JOIN 
        division d3 ON d2.parentId = d3.id 
    LEFT JOIN 
        division d4 ON d3.parentId = d4.id
    WHERE 
        d1.id = {$areaDivision_id} or ar.id = {$areaId}", true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'areaDivisions_form_load':

        $areaDivision_id = isset($_POST['areaDivision_id']) ? intval($_POST['areaDivision_id']) : 0;
        $areaId = isset($_POST['areaId']) ? intval($_POST['areaId']) : 0;
        if ($areaDivision_id || $areaId) {
            $sql = "SELECT 
        d1.id AS areaDivision_id,
        IF(d1.name IS NULL,areaName,d1.name) AS areaDivision_name,
        d1.parentId AS areaDivisionTerritoryId,
        d2.NAME AS territory_name,
        d2.parentId AS areaDivisionRegionId,
        d3.NAME AS region_name,
        d3.parentId AS areaDivisionZoneId,
        d4.NAME AS zone_name,
        d1.STATUS AS comboAreaDivisionStatus
    FROM area_entries ar
         LEFT JOIN division d1  ON d1.id = ar.divisionId AND d1.typeId = 5 
    LEFT JOIN 
        division d2 ON d1.parentId = d2.id
    LEFT JOIN 
        division d3 ON d2.parentId = d3.id 
    LEFT JOIN 
        division d4 ON d3.parentId = d4.id
    WHERE 
        d1.id = {$areaDivision_id} or ar.id = {$areaId}";
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
    case 'saveAreaDivisions':

        $db->query('begin');
        $data = array(
            "id" => $_POST['id'],
            "name" => $_POST['name'],
            "typeId" => 5,
            "parentId" => $_POST['parentId'],
            "status" => $_POST['status']
        );
        $area_id = $data['id'];
        $area_name = $data['name'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $area_name = addslashes($area_name);

        if ($data['id'] > 0) {

            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $userid;

            $areaUnique = $db->getItemSafe("SELECT COUNT(*) from division WHERE name ='{$area_name}' AND parentId = ? AND id != '{$area_id}' ", "i", [$_POST['parentId']]);
            if ($areaUnique > 0) {
                echo "{success: false, message:'Area name already exists.'}";
                exit;
            } else {
                $status = $db->perform("division", $data, 'update', 'id =' . $data['id']);
                $lastId = $data['id'];
            }
        } else {

            $areaUnique = $db->getItemSafe("SELECT COUNT(*) from division WHERE name ='{$area_name}' AND parentId = ? ", "i", [$_POST['parentId']]);
            if ($areaUnique > 0) {
                echo "{success: false, message:'Area name already exists.'}";
                exit;
            } else {
                unset($data['id']);
                $data['createdOn'] = date('Y-m-d H:i:s');
                $data['createdBy'] = $userid;
                $status = $db->perform('division', $data);
                $lastId = $db->insert_id();
            }
        }

        $areaEntriesId = $db->getItemFromDB("SELECT id FROM area_entries WHERE divisionId = {$lastId}", true);
        if ($areaEntriesId > 0) {
            $areaEntryUnique = $db->getItemFromDB("SELECT COUNT(*) from area_entries WHERE areaName ='{$area_name}' AND divisionId = {$lastId} AND id != '{$areaEntriesId}' ");
            if ($areaEntryUnique > 0) {
                echo "{success: false, message:'Area Entry already exists.'}";
                exit;
            } else {
                $areadata['areaName'] = $_POST['name'];
                $areadata['areaUpdatedOn'] = date('Y-m-d H:i:s');
                $areadata['areaUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform("area_entries", $areadata, 'update', 'id =' . $areaEntriesId);
            }
        } else {
            $areaEntryUnique = $db->getItemFromDB("SELECT id from area_entries WHERE areaName ='{$area_name}' ");
            if ($areaEntryUnique > 0) {
                $areadata['divisionId'] = $lastId;
                $areadata['areaName'] = $_POST['name'];
                $areadata['areaUpdatedOn'] = date('Y-m-d H:i:s');
                $areadata['areaUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $db->perform("area_entries", $areadata, 'update', 'id =' . $areaEntryUnique);
            } else {
                $areadata['areaCreatedOn'] = date('Y-m-d H:i:s');
                $areadata['areaCreatedBy'] = $_SESSION['admin']->Finascop_UserId;
                $areadata['areaName'] = $_POST['name'];
                $areadata['divisionId'] = $lastId;
                $status = $db->perform("area_entries", $areadata);
            }
        }
        $return_rec = $db->getFromDb("SELECT  id as areaDivision_id,name as areaDivision_name,parentId as areaDivisionTerritoryId,(SELECT name FROM division WHERE id = parentId) as territory_name,status AS comboRegionStatus FROM division WHERE id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
    case 'getTerritory':
        if ($_POST['regionId'] > 0) {
            $regionId = $_POST['regionId'];
        } else {
            $regionId = 0;
        }
        $qry = "select id,name FROM division where status= 1 AND typeId = 4 AND parentId = {$regionId}  order by name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
}
