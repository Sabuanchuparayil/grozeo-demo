<?php
/*
 * Created on 07-Aug-08
 * @author : Ratheesh Kumar CK <lakshmi@saturn.in>
 *
 * Retrieve the List of Operations available for setting permissions
 * grouped by Module Name
 *
 */
/*
 $db->query("set group_concat_max_len=24000;");
 $db->query("set max_allowed_packet=32000;");
 $qry		= <<<EOT
	SELECT CONCAT('[',GROUP_CONCAT(jRow),']') FROM (SELECT CONCAT('{"module":"',module,'","module_title":"',module_title,'","description" : "',descr,'",
"operations" :[',GROUP_CONCAT(CONCAT('{"op":"',operation,'",'), CONCAT('"op_title":"',op_title,'",'),
CONCAT('"status":"',IsEnabled,'",'), CONCAT('"capability":"',capability,'"}')),']}') AS jRow FROM (SELECT a.ModuleName AS module, a.ModuleTitle AS module_title, COALESCE(a.Description,'') AS descr, b.Operation AS operation, b.Title AS op_title, COALESCE(b.Capability,'') AS capability, b.IsEnabled FROM sys_module a, sys_module_operation b WHERE a.ModuleName = b.ModuleName AND b.Title<>'')
jsonTble
GROUP BY module,descr) AS jTable
EOT;

 echo $db->getItemFromDB($qry);
*/
/*$query = "SELECT DISTINCT(a.ModuleName) AS module, a.ModuleTitle AS module_title, COALESCE(a.Description,'') AS description FROM sys_module a, sys_module_operation b WHERE a.ModuleName=b.ModuleName AND b.Title<>''";
$modules = $db->getMultipleData($query, true);
foreach ($modules as $key => $val) {

    $query = "SELECT Operation AS op, `Title` AS op_title, IsEnabled AS `status`, COALESCE(Capability,'') AS capability FROM sys_module_operation WHERE Title<>'' AND ModuleName = '".$val['module']."' ";
    $operations = $db->getMultipleData($query, true);
    $modules[$key]['operations'] = json_encode($operations);
}
foreach ($modules as $key => $val) {

    if($modules[$key]['operations']=='')
        unset($modules[$key]);
}

$modules = stripslashes(json_encode($modules));
$modules = str_replace('"operations":"[', '"operations":[', $modules);
$modules = str_replace('}]"}', '}]}', $modules);
echo $modules;*/
 $db->query("set group_concat_max_len=1000000;");
// $db->query("set max_allowed_packet=32000;");

if($_SESSION['admin']->IsSuperUser == 'Yes'):
 $qry		= <<<EOT
	select concat('[',group_concat(jRow),']') from (select  concat('{"module":"',module,'","module_title":"',module_title,'","description" : "',descr,'","operations" :[',group_concat(concat('{"op":"',operation,'",'), concat('"op_title":"',op_title,'",'), concat('"status":"',enabled,'",'), concat('"capability":"',capability,'"}')),']}') as jRow from (select a.ModuleName as module, a.ModuleTitle as module_title, coalesce(a.description,'') as descr, b.operation as operation, b.title as op_title, CAST(COALESCE(b.OperationId,'') AS CHAR) as capability, b.IsEnabled AS enabled from sys_module a, sys_module_operation b where a.ModuleName = b.ModuleName and b.title<>'') jsonTble group by module order by module) as jTable;
EOT;
else:

$qry		= <<<EOT
	select concat('[',group_concat(jRow),']') from (select concat('{"module":"',module,'","module_title":"',module_title,'","description" : "',descr,'","operations" :[',group_concat(concat('{"op":"',operation,'",'), concat('"op_title":"',op_title,'",'), concat('"status":"',enabled,'",'), concat('"capability":"',capability,'"}')),']}') as jRow from ( select a.ModuleName as module, a.ModuleTitle as module_title, coalesce(a.description,'') as descr, b.operation as operation, b.title as op_title, CAST(COALESCE(b.OperationId,'') AS CHAR) as capability, b.IsEnabled AS enabled from sys_module a, sys_module_operation b where a.ModuleName = b.ModuleName and b.title<>'' and  b.OperationId in (SELECT SysModOpId FROM usr_capability as acap WHERE acap.UserId = {$_SESSION['admin']->Finascop_UserId}  UNION  SELECT  SysModOpId FROM sys_role_capability rcap WHERE rcap.RoleId = {$_SESSION['admin']->RoleId}) AND    b.OperationId not in (select SysModOpId FROM usr_blocked_capability where UserId={$_SESSION['admin']->Finascop_UserId})) jsonTble group by module order by module) as jTable
EOT;
endif;
//print_r($_SESSION['admin']);
//echo $qry;
//exit;
echo $db->getItemFromDB($qry);