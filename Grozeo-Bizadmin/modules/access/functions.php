<?php
function appendNonMenuedItemsRole($elements,$role_id,&$db){
  $merged = array();
  foreach ($elements as $element) {
    $query = "SELECT id,MenuId,MenuText AS text,MenuText,Title,ModuleName,ParentMenuId,OperationId,SysModOpId  "
            . "FROM  (SELECT CONCAT('op',OperationId) AS id,MenuId,Title AS MenuText,Title,OperationId,ModuleName, "
            . "@parentMenu AS ParentMenuId,  src.SysModOpId   FROM sys_module_operation smo "
            . "LEFT JOIN (SELECT * FROM sys_role_capability WHERE RoleId = {$role_id}) src ON smo.OperationId = src.SysModOpId ) menu_sorted,  "
            . "(SELECT @parentMenu := {$element['ParentMenuId']}) initialisation  "
            . "WHERE Title IS NOT NULL AND ModuleName = '{$element['ModuleName']}' AND MenuId = 0";
    $MenuChildTree = $db->getMultipleData($query, true);
    if (!empty($MenuChildTree)) {
      $merged = array_merge($merged, $MenuChildTree); 
    }
    
  }
 return $merged;
}


function appendNonMenuedItems($elements,$user_id,$role_id,&$db){
  $merged = array();
  foreach ($elements as $element) {
    $query = "SELECT id,MenuId,MenuText AS text,MenuText,Title,ModuleName,ParentMenuId,OperationId,SysModOpId,RoleModOpId , SysBlkOpId 
FROM 
(SELECT CONCAT('op',OperationId) AS id,MenuId,Title AS MenuText,Title,OperationId,ModuleName, @parentMenu AS	ParentMenuId,
uc.SysModOpId,src.SysModOpId AS RoleModOpId,sbc.SysModOpId AS SysBlkOpId 
FROM sys_module_operation smo 
LEFT JOIN (SELECT * FROM usr_capability WHERE UserId = {$user_id}) uc ON smo.OperationId = uc.SysModOpId 
LEFT JOIN (SELECT * FROM sys_role_capability WHERE RoleId = {$role_id}) src ON smo.OperationId = src.SysModOpId 
LEFT JOIN (SELECT * FROM usr_blocked_capability WHERE UserId = {$user_id}) sbc ON smo.OperationId = sbc.SysModOpId ) menu_sorted,
(SELECT @parentMenu := {$element['ParentMenuId']}) initialisation 
WHERE Title IS NOT NULL AND ModuleName = '{$element['ModuleName']}' AND MenuId = 0";

    $MenuChildTree = $db->getMultipleData($query, true);
    if (!empty($MenuChildTree)) {
      $merged = array_merge($merged, $MenuChildTree); 
    }
    
  }
  return $merged;
}

function buildTree(array &$elements, $parentId = 0) {
    $branch = array();
    foreach ($elements as $element) {
      $element['children'] = array();
        if ($element['ParentMenuId'] == $parentId) {
            $children = buildTree($elements, $element['MenuId']);
            if ($children) {
                $element['children'] = $children;
            }else{
              $element['leaf'] = true;
              $element['cls'] = 'nature_group';
            if($element['OperationId'] != NULL){
                if($element['OperationId'] == $element['RoleModOpId'] ){
                  $element['checked'] = true;
                }else{
                  $element['checked'] = false;
                }
              }
              else{
                $element['hidden'] = true;
              }
            }
            $branch[] = $element;
        }
    }

    return $branch;
}
function buildUserTree(array &$elements, $parentId = 0) {
    $branch = array();
    foreach ($elements as $element) {
      $element['children'] = array();
        if ($element['ParentMenuId'] == $parentId) {
            $children = buildUserTree($elements, $element['MenuId']);
            if ($children) {
                $element['children'] = $children;
            }else{
              $element['leaf'] = true;
              $element['cls'] = 'nature_group';
            if($element['OperationId'] != NULL){
                 if((($element['OperationId'] == $element['SysModOpId']) || ($element['OperationId'] == $element['RoleModOpId'])) &&  $element['SysBlkOpId'] == NULL ){
                  $element['checked'] = true;
                }else{
                  $element['checked'] = false;
                }
              }
              else{
                $element['hidden'] = true;
              }
            }
            $branch[] = $element;
        }
    }
    return $branch;
}


