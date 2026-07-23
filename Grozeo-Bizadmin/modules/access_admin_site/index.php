<?php

switch ($op) {
    case 'saveRolePermission':
        $data = $_POST;
        $db->query('begin');
        if ($data['node_checked'] == 'true') {
            $qry = "DELETE FROM sys_role_capability WHERE RoleId = {$data['role_id']} AND SysModOpId = {$data['perm_op']}";
            $db->query($qry);

            $perm_data = array('RoleId' => $data['role_id'], 'SysModOpId' => $data['perm_op']);
            $status = $db->perform('sys_role_capability', $perm_data);
        } else {
            $qry = "DELETE FROM sys_role_capability WHERE RoleId = {$data['role_id']} AND SysModOpId = {$data['perm_op']}";
            $db->query($qry);
        }
        $success = $db->query('commit');
        if ($success) {
            echo "{success:true}";
        } else {
            echo "{success:false}";
        }
        break;
    case 'getPermissionRole':

        $Basic_Menu = $db->getMultipleData("SELECT MenuId,MenuText FROM partner_menu  WHERE ParentMenuId = '0' AND IsEnabled = 'Yes' ORDER BY SortOrder", true);

        if (!empty($Basic_Menu)) {
            $Basic_Tbar_Menu = array();
            foreach ($Basic_Menu as $index => $val) {
                $Basic_Tbar_Menu[$index] = array();
                $Basic_Tbar_Menu[$index]['id'] = 'BA_' . $val['MenuId'];
                $Basic_Tbar_Menu[$index]['text'] = $val['MenuText'];
                $Basic_Tbar_Menu[$index]['draggable'] = false;
                $Basic_Tbar_Menu[$index]['children'] = array();
                $Basic_Tbar_Menu[$index]['cls'] = 'myphasetting-btn';

                $query = "SELECT CONCAT(MenuId,RAND(10)) AS id,MenuId,MenuText AS text,ParentMenuId,ModuleName,
                   OperationId,SysModOpId as RoleModOpId
                   FROM 
                   (SELECT MenuId,MenuText,
                   (SELECT COUNT(MenuId) FROM sys_module_operation  WHERE MenuId = sm.MenuId GROUP BY MenuId HAVING COUNT(MenuId) > 1) AS cnt,
                    ParentMenuId,ModuleName,Title,OperationId,src.SysModOpId 
                     FROM partner_menu sm 
                     LEFT JOIN sys_module_operation smo USING(MenuId) 
                     LEFT JOIN (SELECT * FROM sys_role_capability WHERE RoleId = " . intval($_GET['role_id']) . ") src ON smo.OperationId = src.SysModOpId 
                     WHERE sm.IsEnabled = 'Yes'
                     ORDER BY ParentMenuId,SortOrder ) menu_sorted,
                     (SELECT @iv := CONCAT({$val['MenuId']},',',(SELECT GROUP_CONCAT(MenuId) "
                    . "FROM partner_menu WHERE ParentMenuId = {$val['MenuId']}))) initialisation 
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
                    $MenuChildTree_node = buildTree($MenuChildTree, $val['MenuId']);
                    $Basic_Tbar_Menu[$index]['leaf'] = false;
                    $Basic_Tbar_Menu[$index]['children'] = $MenuChildTree_node;
                }
            }
        }
        echo json_encode($Basic_Tbar_Menu);
        break;
}
