<?php
function buildTree(array &$elements, $parentId = 0)
{
  $branch = array();
  foreach ($elements as $element) {
    $element['children'] = array();
    if ($element['ParentMenuId'] == $parentId) {
      $children = buildTree($elements, $element['MenuId']);
      if ($children) {
        $element['children'] = $children;
      } else {
        $element['leaf'] = true;
        $element['cls'] = 'nature_group';
        if ($element['OperationId'] != NULL) {
          if ($element['OperationId'] == $element['RoleModOpId']) {
            $element['checked'] = true;
          } else {
            $element['checked'] = false;
          }
        } else {
          $element['hidden'] = true;
        }
      }
      $branch[] = $element;
    }
  }

  return $branch;
}
function buildTrainingTree(array &$elements, $parentId = 0)
{
  $branch = array();
  foreach ($elements as $element) {
    $element['children'] = array();
    if ($element['MenuId'] == $parentId) {
      $element['leaf'] = true;
      $element['cls'] = 'nature_group';
      if ($element['trainingId'] != NULL) {
        if ($element['trainingId'] == $element['tmtId']) {
          $element['checked'] = true;
        } else {
          $element['checked'] = false;
        }
      }
      $branch[] = $element;
    }
  }
  return $branch;
}
