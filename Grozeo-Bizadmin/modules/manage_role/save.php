<?php

$table_sys_role = 'sys_role';
$table_users = TABLE_USERS;

//Check whether empty role name or not
if (empty($_POST['admin_role_name']) || empty($_POST['roleDepartmentId']) || empty($_POST['admin_role_name'])) {
  // Exit
  exit;
}
$roleName = $_POST["admin_role_name"] . ' ' . $_POST["roleDepartmentName"];
$cleaned = preg_replace('/[^a-zA-Z0-9 ]/', ' ', $roleName);
$words = explode(' ', $cleaned);
$uniqueWords = array_unique(array_filter(array_map('ucfirst', $words)));
$uniqRoleName = implode('', $uniqueWords);

$id_admin_role = $_POST['id'];
//Check if the Same Role Name Already Exists or Not
$qry  = "SELECT count(*) from $table_sys_role where RoleId<>'" . $id_admin_role . "' and RoleName='" . $uniqRoleName . "' AND IsEnabled='Yes' AND departmentId='{$_POST['roleDepartmentId']}'";
$duplicates  = $db->getItemFromDB($qry);
if ($duplicates > 0) {
  echo "{success:false,errors: { reason: '" . ROLE_CHECK . "' }}";
  return;
}


//Retreive the Post data and create an array for save purpose 
$data    =  array(
  "RoleName" => $uniqRoleName,
  "title"       => $_POST["admin_role_name"],
  "DefaultModule" => $_POST['default_module'],
  "departmentId" => $_POST['roleDepartmentId'],
  "typeId"       => $_POST["divTypeId"],
  "reportingTo" => $_POST['reprtingRoleId'],
  "permissionOn" => $_POST['rolePermissionsTo']
);



//Check whether need to do Insert or Update
if (empty($_POST['id'])) {
  //Insert
  $status  = $db->perform($table_sys_role, $data);
  $id_role = $db->insert_id();
} else {
  $id_role = $_POST['id'];
  //do Update
  $status  = $db->perform($table_sys_role, $data, "update", "RoleId='" . $_POST['id'] . "'");
}


echo "{success: true,role_id:" . $id_role . "}";
