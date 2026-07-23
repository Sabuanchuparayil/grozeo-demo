<?php

/*
 * Created on 01-Aug-08
 * @author : Ratheesh Kumar CK <ratheesh@saturn.in>
 *
 * Permission Settings will be handled by this module.
 *
 */
switch ($op) {
   case 'saveRolePermission':
      $data = $_POST;
      $db->query('begin');
      if($data['node_checked'] == 'true'){
        $qry = "DELETE FROM " . FINASCOP_DB . "sys_role_capability WHERE RoleId = {$data['role_id']} AND SysModOpId = {$data['perm_op']}";
        $db->query($qry);
        
        $perm_data = array('RoleId' => $data['role_id'], 'SysModOpId' => $data['perm_op']);
        $status = $db->perform('sys_role_capability', $perm_data);
      }else{
        $qry = "DELETE FROM " . FINASCOP_DB . "sys_role_capability WHERE RoleId = {$data['role_id']} AND SysModOpId = {$data['perm_op']}";
        $db->query($qry);
      } 
      $success = $db->query('commit');
      if($success){
        echo "{success:true}";
      } else {
        echo "{success:false}";
      }
    break;  
   case 'getPermissionRole':
    
       $Basic_Menu = $db->getMultipleData("SELECT MenuId,MenuText FROM sys_menu  WHERE ParentMenuId = '0' AND IsEnabled = 'Yes' ORDER BY SortOrder", true);

        if (!empty($Basic_Menu)) {
          $Basic_Tbar_Menu = array();
            foreach ($Basic_Menu as $index => $val) {
                $Basic_Tbar_Menu[$index] = array();
                $Basic_Tbar_Menu[$index]['id'] = 'BA_' . $val['MenuId'];
                $Basic_Tbar_Menu[$index]['text'] = $val['MenuText'];
                $Basic_Tbar_Menu[$index]['draggable'] = false;
                $Basic_Tbar_Menu[$index]['children'] = array();
                $Basic_Tbar_Menu[$index]['cls'] = 'myphasetting-btn';
                
                $query = "SELECT CONCAT(MenuId,RAND(10)) AS id,MenuId,IF(cnt > 1,Title,MenuText) AS text,ParentMenuId,ModuleName,
                  OperationId,SysModOpId as RoleModOpId
                  FROM 
                  (SELECT MenuId,MenuText,
                  (SELECT COUNT(MenuId) FROM sys_module_operation  WHERE MenuId = sm.MenuId GROUP BY MenuId HAVING COUNT(MenuId) > 1) AS cnt,
                   ParentMenuId,ModuleName,Title,OperationId,src.SysModOpId 
                    FROM sys_menu sm 
                    LEFT JOIN sys_module_operation smo USING(MenuId) 
                    LEFT JOIN (SELECT * FROM sys_role_capability WHERE RoleId = " . intval($_GET['role_id']) . ") src ON smo.OperationId = src.SysModOpId 
                    WHERE sm.IsEnabled = 'Yes'
                    ORDER BY ParentMenuId,SortOrder ) menu_sorted,
                    (SELECT @iv := CONCAT({$val['MenuId']},',',(SELECT GROUP_CONCAT(MenuId) "
                    . "FROM sys_menu WHERE ParentMenuId = {$val['MenuId']}))) initialisation 
                    WHERE FIND_IN_SET(ParentMenuId, @iv) AND @iv := CONCAT(@iv, ',', MenuId)";

                $MenuChildTree = $db->getMultipleData($query, true);
 
                if (!empty($MenuChildTree)) {
                  $temp = array();
                  //$temp = appendNonMenuedItemsRole($MenuChildTree, $_GET['role_id'],$db);
                  if (!empty($temp)) {
                    $MenuChildTree = array_merge($temp, $MenuChildTree); 
                  }
                  
                }
               
                if (!empty($MenuChildTree)) {
                    $MenuChildTree_node = buildTree($MenuChildTree,$val['MenuId']);
                    $Basic_Tbar_Menu[$index]['leaf'] = false;
                    $Basic_Tbar_Menu[$index]['children'] = $MenuChildTree_node;
                } 
            }
        }
        echo json_encode($Basic_Tbar_Menu);
        break;
 
  case 'savePermission':
    
    $data = $_POST;
    $db->query('begin');
    if($data['node_checked'] == 'true'){

      $qry = "DELETE FROM " . FINASCOP_DB . "usr_capability WHERE UserId = {$data['user_id']} AND SysModOpId = {$data['perm_op']}";

      $db->query($qry);
      $db->query("DELETE FROM " . FINASCOP_DB . "usr_blocked_capability WHERE UserId = {$data['user_id']} AND SysModOpId = {$data['perm_op']}");
      $perm_data = array('UserId' => $data['user_id'], 'SysModOpId' => $data['perm_op']);
      $status = $db->perform('usr_capability', $perm_data);
    }else{

      $qry = "DELETE FROM " . FINASCOP_DB . "usr_capability WHERE UserId = {$data['user_id']} AND SysModOpId = {$data['perm_op']}";
      $db->query("DELETE FROM " . FINASCOP_DB . "usr_blocked_capability WHERE UserId = {$data['user_id']} AND SysModOpId = {$data['perm_op']}");

      $db->query($qry);
      $perm_data = array('UserId' => $data['user_id'], 'SysModOpId' => $data['perm_op']);
      $status = $db->perform('usr_blocked_capability', $perm_data);
    } 
    $success = $db->query('commit');
    if($success){
      echo "{success:true}";
    } else {
      echo "{success:false}";
    }
  break;
  case 'getPermissionUser':
    
    $RoleId = $db->getItemFromDB('select RoleId from finascop_usr_master where UserId = ' . $_GET['user_id']);

        $Basic_Menu = $db->getMultipleData("SELECT MenuId,MenuText FROM sys_menu  WHERE ParentMenuId = '0' AND IsEnabled = 'Yes' ORDER BY SortOrder", true);

        if (!empty($Basic_Menu)) {
          $Basic_Tbar_Menu = array();
            foreach ($Basic_Menu as $index => $val) {
                $Basic_Tbar_Menu[$index] = array();
                $Basic_Tbar_Menu[$index]['id'] = 'BA_' . $val['MenuId'];
                $Basic_Tbar_Menu[$index]['text'] = $val['MenuText'];
                $Basic_Tbar_Menu[$index]['draggable'] = false;
                $Basic_Tbar_Menu[$index]['children'] = array();
                $Basic_Tbar_Menu[$index]['cls'] = 'myphasetting-btn';

                $query = "SELECT CONCAT(MenuId,RAND(10)) AS id,MenuId,IF(cnt > 1,Title,MenuText)AS text, MenuText,Title,ModuleName,
                  ParentMenuId,OperationId,
                  SysModOpId,RoleModOpId,SysBlkOpId FROM 
                  (SELECT MenuId,MenuText,
                  (SELECT COUNT(MenuId) FROM sys_module_operation  WHERE MenuId = sm.MenuId GROUP BY MenuId HAVING COUNT(MenuId) > 1) AS cnt, 
                  Title,ParentMenuId,ModuleName, OperationId,
                  uc.SysModOpId,src.SysModOpId AS RoleModOpId,sbc.SysModOpId AS SysBlkOpId 
                  FROM sys_menu sm "
                . "LEFT JOIN sys_module_operation smo USING(MenuId) "
                . "LEFT JOIN (SELECT * FROM usr_capability WHERE UserId = {$_GET['user_id']}) uc ON smo.OperationId = uc.SysModOpId "
                . "LEFT JOIN (SELECT * FROM sys_role_capability WHERE RoleId = {$RoleId}) src ON smo.OperationId = src.SysModOpId "
                . "LEFT JOIN (SELECT * FROM usr_blocked_capability WHERE UserId = {$_GET['user_id']}) sbc ON smo.OperationId = sbc.SysModOpId "
                . "WHERE sm.IsEnabled = 'Yes' ORDER BY ParentMenuId,SortOrder ) menu_sorted,"
                . "(SELECT "
                . "@iv := CONCAT({$val['MenuId']},',',(SELECT GROUP_CONCAT(MenuId) FROM sys_menu WHERE ParentMenuId = {$val['MenuId']}))) initialisation"
                . " WHERE FIND_IN_SET(ParentMenuId, @iv) AND @iv := CONCAT(@iv, ',', MenuId)";

                $MenuChildTree = $db->getMultipleData($query, true);
                
                if (!empty($MenuChildTree)) {
                  $temp = array();
                  //$temp = appendNonMenuedItems($MenuChildTree, $_GET['user_id'],$RoleId,$db);
                  if (!empty($temp)) {
                    $MenuChildTree = array_merge($temp, $MenuChildTree); 
                  }
                 }
                if (!empty($MenuChildTree)) {
                    $MenuChildTree_node = buildUserTree($MenuChildTree,$val['MenuId']);
                    $Basic_Tbar_Menu[$index]['leaf'] = false;
                    $Basic_Tbar_Menu[$index]['children'] = $MenuChildTree_node;
                } 


            }
        }
        echo json_encode($Basic_Tbar_Menu);
        break;

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