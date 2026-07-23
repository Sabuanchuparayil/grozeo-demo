<?php
/*
 * Created on 01-Aug-08
 *
 * It is good to write the module specific functions (used in this module only)
 */

/**
 * Retrieves the id, rolename from Role DB and output it as JS Array
 */
function  generateJsComboStore()
{
	global $db;
	$whr = "";
	$primaryRoleId = $_POST['primaryRoleId'];
	if ($primaryRoleId > 0) {
		$whr = " AND RoleID <> {$primaryRoleId}";
	}

	$logged_in_user_role = $_SESSION['admin']->RoleId;
	$is_super            = $db->getItemFromDB("SELECT IsSuperUser FROM " . FINASCOP_DB . "finascop_usr_master WHERE UserId =" . $_SESSION['admin']->Finascop_UserId);

	$qry	             = 'SELECT RoleId,RoleName FROM sys_role WHERE RoleID <> 1 and  RoleName<>"" AND IsEnabled = "Yes" ' . $whr . ' ORDER BY RoleName ASC ';
	$rs		     = $db->query($qry);
	$i 		     = 0;
	$num_rows 	= $db->num_rows($rs);
	echo "[";
	while ($row = $db->fetch_array($rs)) {
		echo "[";
		echo "'" . $row[RoleId] . "','" . $row[RoleName] . "'";
		echo "]";
		$i++;
		if ($i < $num_rows) echo ",";
		flush();
	}
	echo "]";
}
