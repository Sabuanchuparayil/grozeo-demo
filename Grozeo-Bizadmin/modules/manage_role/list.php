<?php
/*
 * Created on 30-Jul-08
 * @author : Lakshmi Jayaram <lakshmi@saturn.in>
 *
 * Retrieve the List of designation and send it as JSON
 *
 */

//Retrieve Parameters
$rec_limit		= $_POST['limit'];
$rec_start		= $_POST['start'];
$rec_sort		= $_POST['sort'];
$rec_sort_dir	= $_POST['dir'];
//--



//Query to Get Data
if (isset($_POST['filter'])) {
    $arr = array('admin_role_name' => "RoleName");
    foreach ($_POST['filter'] as $key => $val) {
        $filter_part .= " and " . $arr[$val['field']] . " LIKE '%" . $val['data']['value'] . "%' ";
    }
}

$whr = "";
$logged_in_user_role = $_SESSION['admin']->RoleId;
$is_super            = $db->getItemFromDB("SELECT IsSuperUser FROM " . FINASCOP_DB . "finascop_usr_master WHERE UserId =". $_SESSION['admin']->Finascop_UserId);
if($is_super == 'No') {
    //$whr             = 'AND TypeId = (SELECT TypeId FROM sys_role WHERE RoleId = '.$logged_in_user_role.')';
}

$totalCount = $db->getItemFromDB("select count(RoleId) from sys_role r where r.RoleId <> '' and r.IsEnabled= 'Yes' $whr $filter_part");
$db->query('set @cnt=0');
$qry		= <<<EOT
	 select * from (select @cnt:=@cnt+1 as rownum,sel.* from
 ( select
	  		RoleId as id_admin_role, RoleName,title as admin_role_name, DefaultModule as default_module,
            departmentId AS roleDepartmentId,typeId AS divTypeId,divisionId AS roleDivisionId,reportingTo AS reprtingRoleId,permissionOn AS rolePermissionsTo,
            (SELECT name FROM org_department WHERE id = departmentId) AS deptName,
            (SELECT name FROM divisionType WHERE id = typeId) AS typeName,
            (SELECT title FROM sys_role rep WHERE rep.RoleId = r.reportingTo) AS repRole
	  from
    sys_role r
	  where r.RoleId <> 1 and r.RoleId <> '' and r.IsEnabled='Yes'
          $whr $filter_part
	  order by $rec_sort $rec_sort_dir ) as sel) as sel2 limit $rec_start,$rec_limit
EOT;

$rs		= $db->query($qry);
$i 		= 0; $num_rows 	= $db->num_rows($rs);
echo '{"totalCount":'.$totalCount.',"roles":';
echo "[";
while($row=$db->fetch_array($rs)) {
    echo json_encode($row);
    $i++; if($i<$num_rows) echo ",";
    flush();
}
echo "]}";
 exit;