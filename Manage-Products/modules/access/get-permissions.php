<?php
/*
 * Created on 24-Aug-08
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
$adminid		= $_POST['id'];
$qry		= <<<EOT
	  SELECT
	 	  	CONCAT('{',
	 	  	'"existing":',
	 	  		CONCAT('"',group_concat(c.SysModOpId SEPARATOR ';'),';',
	 	  			(SELECT ar.Capability FROM sys_role_capability ar WHERE u.RoleId=ar.RoleId)),'"',
	 	  	',"roles":','"',
	 	  	(SELECT group_concat(ar.SysModOpId SEPARATOR ';') AS perms FROM sys_role_capability ar, " . FINASCOP_DB . "finascop_usr_master u
	 	  		WHERE u.RoleId=ar.RoleId AND u.UserId=$adminid),'"','}')
	 	  			AS perms FROM " . FINASCOP_DB . "finascop_usr_master u LEFT JOIN usr_capability c ON (c.UserId=u.UserId) WHERE u.UserId=$adminid;
                                            

EOT;
