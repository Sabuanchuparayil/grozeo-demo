<?php
/*
 * Construction of Top menu
 * Created on 08-Aug-08
 * Created by NIJU N B
 *
 */

/*
 * :TODO:		This Function construct  the json encoded string for menu
 * :ARGUMENT:	An array of permitted menu list
 * :TRICKY: 	Recursive calling
 * :AUTHOR:		Niju N B
 * */

function jsonMenuGenerator($list, $showText = TRUE, $showIcon = TRUE){

	$ret = '{items:[';

	foreach($list as $key =>$value){
		if ($value['data'][1]=="-"){
			$ret .="'-',";
		}else{
		$ret .= '{text : "'.$value['data'][1].'"';

		/* Check for handler */
		if(!empty($value['data'][2]))
			$ret .= ',handler:'.$value['data'][2].'';

		/* Check for children*/
		if(isset($value['children']))
			$ret .= ',menu:'.jsonMenuGenerator($value['children']);

		}
		$ret .= ',cls:"xtramnu"';
	}
	/* Polishes the string */
	$ret = preg_replace('/},+$/','}',$ret);
	$ret = preg_replace('/},"-",+$/','}',$ret);

	$ret .= ']}';
	return $ret;
}

/* Setting the common values */
$permitted		= array();
$perm_parents 	= array(0);
$refs			= array();
$list 			= array();

/* check the super user */
if($_SESSION['admin']->IsSuperUser):
	/* For super user populate all the menus */
	$query	=	'SELECT MenuId FROM sys_menu where IsEnabled="Yes" ';
else:
	/* For other users construct the menu based on their role and permisions*/
	$admin_uidnr=	$userID;
 	$admin_role	=	$db->getItemFromDB("SELECT RoleId FROM " . FINASCOP_DB . "finascop_usr_master WHERE UserId=".$userID);
	$query		=	sprintf(
					"select id_menu from admin_perms where uidnr_admin = %d " .
					"union select id_menu from admin_menu where menu_default_permission = 1 " .
					"union select id_menu from admin_role_perms where id_admin_role = %d",
					$admin_uidnr,$admin_role);

endif;

$permitted	=	$db->getMulipleData($query);


//Get the list of Menus needs to be blocked
$blocked		= blocked_capabilities($userID);
if(count($blocked['menus'])>0 && is_array($blocked['menus'])){
	$blocked_menus	= implode(",", $blocked['menus']);
}

/* check whether the user have permitted menu or not */
if($permitted) :

	/* add all the parent menu of the currently selected menu to the the permitted */
	while(true){
		$query	=	'SELECT distinct(ParentMenuId) FROM sys_menu WHERE IsEnabled="Yes" and  MenuId in ('.
					join(',',$permitted).') AND ParentMenuId NOT IN (0,'.join(',',$permitted).')';
		$result = 	$db->query($query);

		/* Exit from the loop when parent is not found*/
		if ($db->num_rows($result) == 0)
			break;

		/*Add parent to the permitted menu*/
		while($row = $db->fetch_row($result))
			$permitted[] = $row[0];
	}

	/* check the top level */
        $permMenus = strlen(join(',',$permitted)) > 0 ? join(',',$permitted) : '';
        $blocMenus = strlen($blocked_menus) > 0 ? $blocked_menus : '';

	$appendWHR = (strlen($blocked_menus)>0)?"and MenuId NOT IN (".$blocMenus.")":"";
	$query 	= 	"SELECT MenuId, MenuText, MenuLink, ParentMenuId FROM sys_menu " .
				"WHERE IsEnabled='Yes' and MenuId IN (".$permMenus.") " .
				$appendWHR.
				" ORDER BY SortOrder, ParentMenuId, MenuId";
	$result = 	$db->query($query);


	while($row = $db->fetch_row($result)){

		/*	Add item to reference list	 */
		$thisref		 	=	 &$refs[$row[0]];
		$thisref['data']	=	$row;
		/*  If item has no parent add item reference to top level of build list. */
	    if ($row[3] == 0) {
	        $list[$row[0]] = &$thisref;
	    } else {
	    	/* If item has a parent, add the item reference to it’s parents list of children. */
	        $refs[$row[3]]['children'][$row[0]] = &$thisref;
	    }

	}

	//$jsonMenu = jsonMenuGenerator($list);
	$reg_exp = preg_replace("/('-',)+/","'-',",jsonMenuGenerator($list));
	$jsonMenu = preg_replace("/('-',])+/",']',$reg_exp);
	$jsonMenu = str_replace("['-',{",'[{',$jsonMenu);
	//$jsonMenu = polishJsonMenu($jsonMenu);
else:
	$jsonMenu = '{}';
endif;
	$jsonMenu = '{}';
 /* Generate the script variable*/
 echo 'var jsonMenu = '.$jsonMenu.';';
