<?php
/*
 * Created on 04-Aug-08
 * @author : Lakshmi Jayaram <lakshmi@saturn.in>
 *
 * Will Delete Role
 *
 */

 //$table_sys_role = TABLE_ROLES;
 $roleIDs	=	join(",", json_decode(stripslashes($_POST["id_admin_role"])));

 // Delete data from admin_Role
 $status	= $db->query("update sys_role set IsEnabled='No' where RoleId in (".$roleIDs.")");

 //Return Status
 if ($status){
   echo "{success:true}";
 }