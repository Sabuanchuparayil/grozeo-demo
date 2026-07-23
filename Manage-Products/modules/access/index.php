<?php

/*
 * Created on 01-Aug-08
 * @author : Ratheesh Kumar CK <ratheesh@saturn.in>
 *
 * Permission Settings will be handled by this module.
 *
 */

switch ($op) {
//for save permission
    case 'save':
        require(THIS_MODULE_PATH . "/save.php");
        break;
 //Get the existing capabilities for the existing user
    case 'get_existing':       
        if ($_POST['type'] == 'user') {
            //$qry			= "select c.capability as perms from admin_users u left join admin_capability c on (c.uidnr_admin=u.uidnr_admin) where u.uidnr_admin='".$_POST['id']."'";
            //$qry			= "select CONCAT(c.capability, ';', (select ar.capability from admin_role_capability ar where u.id_admin_role=ar.id_admin_role)) as perms from admin_users u left join admin_capability c on (c.uidnr_admin=u.uidnr_admin) where u.uidnr_admin='".$_POST['id']."'";
            //$qry			= "select CONCAT(COALESCE(c.capability,''), ';', COALESCE(ar.capability,'')) as perms from admin_users u left join admin_capability c on (c.uidnr_admin=u.uidnr_admin) left join admin_role_capability ar on (ar.id_admin_role=u.id_admin_role) where u.uidnr_admin='".$_POST['id']."'";
            
          $qry = "select CONCAT(COALESCE(group_concat(distinct c.SysModOpId SEPARATOR ';'),''), ';', COALESCE(group_concat(ar.SysModOpId SEPARATOR ';'),'')) as perms from " . FINASCOP_DB . "finascop_usr_master u left join usr_capability c on (c.UserId=u.UserId) left join sys_role_capability ar on (ar.RoleId=u.RoleId) where u.UserId='" . $_POST['id'] . "'";
        } elseif ($_POST['type'] == 'designation') {
            $qry = "select group_concat(distinct r.SysModOpId SEPARATOR ';') as perms from sys_role u left join sys_role_capability r on (u.RoleId=r.RoleId) where r.RoleId='" . $_POST['id'] . "'";
        }

        $rd = $db->fetch_array($db->query($qry));
        $perms = explode(PERM_SEPERATOR, $rd['perms']);
        if ($_POST['type'] == 'user') {
            $userRole = "select CONCAT(group_concat(distinct ar.SysModOpId SEPARATOR ';')) as roles from sys_role_capability ar, " . FINASCOP_DB . "finascop_usr_master u
	 	  		where u.RoleId=ar.RoleId and u.UserId='" . $_POST['id'] . "'";
            $roleRd = $db->fetch_array($db->query($userRole));
            $roleperms = explode(PERM_SEPERATOR, $roleRd['roles']);

            $blockedUserRole = "select group_concat(distinct SysModOpId SEPARATOR ';') as blockedRoles from usr_blocked_capability where UserId='" . $_POST['id'] . "'";
            $blockedRoleRd = $db->fetch_array($db->query($blockedUserRole));
            $blockedRoleperms = explode(PERM_SEPERATOR, $blockedRoleRd['blockedRoles']);
            $i = 0;
            $perms2 = array();

            foreach ($perms as $key => $value) {
                if (!in_array($value, $blockedRoleperms)) {                    
                    //$perms2[] = $value;
                    array_push($perms2,$value);
                }
                $i++;
            }
	    $perms2 =  array_values(array_unique($perms2));
            $data = array();
            $data["existing"] = $perms2;
            $data["roles"] = $roleperms;
            $perms = $data;
        } else {
            $data["existing"] = $perms;
            $data["roles"] = "";
            $perms = $data;
        }
		
        echo stripslashes(json_encode($data));

        break;

    default:
        require(THIS_MODULE_PATH . "/list.php");
        break;
}