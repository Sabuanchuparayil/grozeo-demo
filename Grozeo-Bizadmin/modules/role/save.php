<?php
/*
 * Created on 04-Aug-08
 * @author : Lakshmi Jayaram <lakshmi@saturn.in>
 *
 * Will Insert / Update role
 *
 */

$table_sys_role = 'sys_role';
$table_users = TABLE_USERS;

 //Check whether empty role name or not
 if(empty($_POST['admin_role_name'])){
  // Exit
 exit;
 }

 //Check if the Same Role Name Already Exists or Not
 $qry			= "SELECT count(*) from $table_sys_role where RoleId<>'".$id_admin_role."' and RoleName='".$admin_role_name."' AND IsEnabled='Yes'";
 $duplicates	= $db->getItemFromDB($qry);
 if($duplicates>0){
 	echo "{success:false,errors: { reason: '".ROLE_CHECK."' }}";
 	return;
 }

 //$TypeId   = $db->getItemFromDB("SELECT TypeId FROM sys_role WHERE RoleId =".$_SESSION['admin']->RoleId);

 //Retreive the Post data and create an array for save purpose 
 $data		=	array(
     "RoleName"	     => $_POST["admin_role_name"],
     "DefaultModule" => $_POST['default_module']
 );



 //Check whether need to do Insert or Update
 if (empty($_POST['id_admin_role'])){
   //Insert
   $status	= $db->perform($table_sys_role, $data);
   $id_role = $db->insert_id();
 }else{
   $id_role = $_POST['id_admin_role'];
   //do Update
   $status	= $db->perform($table_sys_role, $data, "update", "RoleId='".$_POST['id_admin_role']."'");
 }
/*
 //S: Regenerate the Cached JS files
	$rs		= $db->query('SELECT UserId FROM $table_users WHERE RoleId='.$id_role);
	if($db->num_rows($rs)>0)
	while($rd = $db->fetch_array($rs))
	executePHPCLICMD(array('module'=>'ui', 'op'=>'generateUserJs', 'userID'=>$rd['UserId']));
	//E: Regenerate the Cached JS files
	 *
*/

 echo "{success: true,role_id:".$id_role."}";
