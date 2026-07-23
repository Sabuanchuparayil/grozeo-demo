<?php

function jsonMenuGenerator($list, $showText = TRUE, $showIcon = TRUE, $showQTip = TRUE) {

    $ret = '{items:[';
    foreach ($list as $key => $value) {

        if ($value['data'][1] == "-") {
            $ret .= "'-',";
        } else {
            $ret .= '{';
            //$ret .= 'id : "uimenu_'. $value['data'][0].'"';
            $ret .= 'MenuId: "' . $value['data'][0] . '"';

            $comma = '';
            $comma = ',';
            if ($showText) {
                $ret .= $comma . 'text : "' . $value['data'][1] . '"';
                $comma = ',';
            }

            /* if ($showQTip) {
              $ret .= $comma . 'tooltip : "' . $value['data'][1] . '"';
              $comma = ',';
              } */
            if (!empty($value['data'][1])) {
                $ret .= $comma . 'tooltip : "' . $value['data'][1] . '"';
            }
            /* Check for handler */
            if (!empty($value['data'][2])) {
                $ret .= $comma . 'handler:' . $value['data'][2] . '';
                $comma = ',';
            }

            /* Check for iconCls */
            if (!empty($value['data'][5]) && $showIcon) {
                if ($showText) {
                    $ret .= $comma . 'iconCls:"' . $value['data'][5] . '"';
                    $comma = ',';
                } else {
                    $ret .= $comma . 'cls:"' . $value['data'][5] . '"';
                    $comma = ',';
                }
            }

            /* Check for children */
            if (isset($value['children']))
                $ret .= $comma . 'menu:' . jsonMenuGenerator($value['children'], $showText);

            /* For toplevel menu add separator */
            if ($value['data'][3] == 0)
                $ret .= '},"-",';
            else
                $ret .= '},';
        }
    }
    /* Polishes the string */
    $ret = preg_replace('/},+$/', '}', $ret);
    $ret = preg_replace('/},"-",+$/', '}', $ret);

    $ret .= ']}';
    return $ret;
}

/**
 * Returns all MenuID of the All the Menus permitted for the logged in User
 * 1. This includes, all the Public Menus (where capability = NULL) and all the menus and its parent menus permitted for the user
 * 2. This needs to be find out using the capbaility list stored in Session for the logged in user.
 * @return	void
 * */
function getPermittedMenus() {
    global $_SESSION, $db;

    $permitted = array();
    $perm_parents = array(0);
    $capabilities = "''";
    if (is_array($_SESSION['admin']->perms))
        $capabilities = "'" . join("','", $_SESSION['admin']->perms) . "'";
    /* check the super user */
    if ($_SESSION['admin']->IsSuperUser == 'Yes') {
        /* For super user populate all the menus */
        $query = 'SELECT MenuId FROM sys_menu where IsEnabled="Yes" ';
    } else {
        /* For other users construct the menu based on their role and permisions */
        $query = 'SELECT MenuId FROM sys_module_operation WHERE MenuId != "" AND (OperationId IN (' . $capabilities . ') OR Capability="" OR Capability=NULL)';
    }

    $permitted = $db->getMulipleData($query);


    //??????????????????????????????????????????????????????????
    //++++++++++++++++++ NOT WORKING ++++++++++++++++++++++++++
    //NEED TO REWRITE THE FUNCTION TO GET THE BLOCKED LILST FROMCABILITIES INSTEAD OF FROM PERMS
    //??????????????????????????????????????????????????????????
    //Get the list of Menus needs to be blocked
    $blocked = blocked_capabilities($userID);
    if (count($blocked['menus']) > 0)
        if (is_array($blocked['menus']))
            $blocked_menus = join(",", $blocked['menus']);
    //??????????????????????????????????????????????????????????
    //??????????????????????????????????????????????????????????

    /* check whether the user have permitted menu or not */

    if ($permitted) {
        /* add all the parent menu of the currently selected menu to the the permitted */
        while (true) {
            $whr = "";
            if ($parentMenuId)
                $whr = " AND ParentMenuId IN ($parentMenuId)";

            $query = 'SELECT distinct(ParentMenuId) FROM sys_menu WHERE IsEnabled="Yes" and MenuId in (' .
                    join(',', $permitted) . ') AND ParentMenuId NOT IN (0,' . join(',', $permitted) . ')' .
                    $whr;

            $result = $db->query($query);

            /* Exit from the loop when parent is not found */
            if ($db->num_rows($result) == 0)
                break;

            /* Add parent to the permitted menu */
            while ($row = $db->fetch_row($result))
                $permitted[] = $row[0];
        }

        return $permitted;
    }else {
        return false;
    }
}

function buildMenuTree($result) {
    global $db;

    /* Setting the common values */
    $refs = array();
    $list = array();

    while ($row = $db->fetch_row($result)) {
        /* 	Add item to reference list	 */
        $thisref = & $refs[$row[0]];
        $thisref['data'] = $row;
        /*  If item has no parent add item reference to top level of build list. */
        if ($row[3] == 0) {
            $list[$row[0]] = & $thisref;
        } else {
            /* If item has a parent, add the item reference to it’s parents list of children. */
            $refs[$row[3]]['children'][$row[0]] = & $thisref;
        }
    }
    //print_r($list);
    return $list;
}

function parseJSONMenu($list) {
    if (count($list) > 0) {
        //$jsonMenu = jsonMenuGenerator($list);
        $reg_exp = preg_replace("/('-',)+/", "'-',", jsonMenuGenerator($list, $_REQUEST['showText'] == 'false' ? false : true ));
        $jsonMenu = preg_replace("/('-',])+/", ']', $reg_exp);
        $jsonMenu = str_replace("['-',{", '[{', $jsonMenu);
        //$jsonMenu = polishJsonMenu($jsonMenu);
    } else {
        $jsonMenu = '{}';
    }

    return $jsonMenu;
}

function getChildren($parentId = 0) {
    global $db;
    $subMenus = array();

    $children = $db->getMultipleData("SELECT MenuId FROM sys_menu WHERE IsEnabled='Yes' and ParentMenuId =  $parentId");
    if (is_array($children))
        foreach ($children as $sub) {
            $subMenus[] = $sub;
            $tmp = getChildren($sub);
            $subMenus = array_merge($subMenus, $tmp);
        }

    return $subMenus;
}

/**
 * @param 	{Integer} MenuId of the Parent Menu. If ParentMenuId provided, only the submenus will be listed.
 *
 */
function getTopLevelButtons($parentMenuId = 0) {
    /* Setting the common values */
    global $db;
    $refs = array();
    $list = array();

    $permitted = getPermittedMenus();

    if (is_array($permitted))
        $permittedMenus = join('","', $permitted);
    /* check the top level */
    $appendWHR = (strlen($blocked_menus) > 0) ? "and MenuId NOT IN (" . $blocked_menus . ")" : "";
    if ($_SESSION['admin']->current_branch_iscpd == 1) {
        $cpdMenu = " AND MenuBranchStatus <> 2 ";
    } else {
        $cpdMenu = " AND MenuBranchStatus <> 1 ";
    }
    
    $parents = array(intval($parentMenuId));
    if ($parentMenuId > 0) {
        $tmp = getChildren($parentMenuId);
        $parents = array_merge($parents, $tmp);
    }

    $query = "SELECT MenuId, MenuText, MenuLink, ParentMenuId, ShortKey, IconCls, SortOrder FROM sys_menu " .
            "WHERE  IsEnabled='Yes' and MenuId IN (\"" . $permittedMenus . "\") AND ParentMenuId IN (" . implode(',', $parents) . ") " .
            $appendWHR;
    //" ORDER BY SortOrder, ParentMenuId, MenuId";
    //commented by lakshmi AND ParentMenuId = '".$parentMenuId."' 

    if ($parentMenuId > 0)
        $query = "SELECT * FROM (" . $query . " UNION SELECT MenuId, MenuText, MenuLink, ParentMenuId, ShortKey, IconCls, SortOrder FROM sys_menu  WHERE IsEnabled='Yes' and   MenuId IN (\"" . $parentMenuId . "\") ) AS MenuTbl";
    $query .= " ORDER BY SortOrder, ParentMenuId, MenuId";


    $result = $db->query($query);

    $list = buildMenuTree($result);
    $jsonMenu = parseJSONMenu($list);

    return $jsonMenu;
}

?>
