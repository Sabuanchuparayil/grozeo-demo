<?php

$userid = $_SESSION['admin']->Finascop_UserId;
switch ($op) {
    case 'getDivisionType':
        $qry = "select id,name from  divisionType  order by name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getDivision':
        if ($_POST['divTypeId'] > 0)
            $cond = " AND typeId = " . intval($_POST['divTypeId']) . " ";
        $qry = "select id,name from  division where status= 1 {$cond} order by name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getRoles':
        $qry = "select RoleId,RoleName from  sys_role  order by RoleName";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getDepartment':
        $qry = "select id,name from  org_department where status= 1 order by name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getDesignations':
        $roleId = intval($_POST['roleId']);
        $departmentId = intval($_POST['departmentId']);
        $edit_status = $_POST['edit_status'];
        $allowedFields = ['role_name', 'role_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        }
        if($departmentId > 0){
            $cond = " and departmentId = {$departmentId}";
        }else{
            $cond = " ";
        }
        if ($roleId >0)
            $qry = "SELECT org_designation.id,org_designation.name,
            CASE WHEN role_designation_mapping.designationId IS NOT NULL AND role_designation_mapping.roleId = {$roleId} THEN 1 ELSE 0 END AS checked FROM org_designation
            LEFT JOIN role_designation_mapping ON role_designation_mapping.designationId = org_designation.id where status = 1 {$cond} {$searchitem} ";
        else
            $qry = "SELECT id,name FROM org_designation where status = 1 {$cond} {$searchitem}";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        } else
            echo json_encode([]);
        break;
    case 'loadRoleData':
        $roleId = $_POST['roleId'];
        if ($roleId) {
            $sql = "SELECT RoleId AS id_admin_role,RoleName,title AS txtRole,departmentId AS roleDepartmentId,typeId AS divTypeId,divisionId AS roleDivisionId,reportingTo AS reprtingRoleId,permissionOn AS rolePermissionsTo FROM sys_role  WHERE RoleId = '{$roleId}' ";
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
    case 'save':
        require(THIS_MODULE_PATH . "/save.php");
        break;
    case 'role_combo':
        generateJsComboStore();
        break;
    case 'delete':
        require(THIS_MODULE_PATH . "/delete.php");
        break;
    default:
        require(THIS_MODULE_PATH . "/list.php");
        break;
}
