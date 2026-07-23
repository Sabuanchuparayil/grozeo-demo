<?php

//makemenu.php
$_SESSION['menu_data'] = array();
$permitted = array();
$width = 0;
$mnu_level = 0;
$admin_uidnr = $_SESSION['admin']['uidnr_admin'];
$admin_role = $_SESSION['admin']['id_admin_role'];
$query = sprintf("select id_menu from admin_perms where uidnr_admin = %d union select id_menu from admin_menu where menu_default_permission = 1 union select id_menu from admin_role_perms where id_admin_role = %d",$admin_uidnr,$admin_role);
//$query = sprintf("select id_menu from admin_role_perms where id_admin_role = %d",$admin_role);
$result = $db->query($query);
while($row = $db->fetch_row($result)) $permitted[] = $row[0];
while(true){
	$query='select distinct(menu_parent) from admin_menu  where id_menu in ('.join(',',$permitted).') and menu_parent not in(0,'.join(',',$permitted).')';
	$result = $db->query($query);
	if ($db->num_rows($result) == 0) break;
	while($row = $db->fetch_row($result)) $permitted[] = $row[0];
}

/* lock down the menu builder to the second level */
$perm_parents = array(0);
$menuRs = $db->query('select id_menu from admin_menu where menu_parent = 0');
while($menuRd = $db->fetch_row($menuRs))
	$perm_parents[] = $menuRd[0];

$cmenu = array();
$menu = array();

 $query = "select id_menu,menu_text,menu_link,menu_parent,menu_key_char from admin_menu where id_menu in (".join(',',$permitted).") and menu_status=1 order by placement,menu_parent,id_menu";
$result = $db->query($query);
while($row = $db->fetch_row($result)){
	if(!in_array($row[3],$perm_parents)) print_r($row); 
	$menu[$row[3]][] = $row;
	$cmenu[] = $row;
}

$mMenu = $menu[0];
$txtMenu = array();

foreach($cmenu as $menuItem){

	if($menuItem[3] == 0) continue;

	if(array_key_exists($menuItem[0],$menu)){
		$txtMenu[$menuItem[3]][] = '	<a class="menuItem" href="" onclick="return false;" ' . "\n"
				.'		 onmouseover="menuItemMouseover(event, \'menuId'. $menuItem[0] . '\');" onmouseout="mouseMoveOut();">' . "\n"
				.'		<span class="menuItemText">'.$menuItem[1].'</span>'  . "\n"
				.'		<span class="menuItemArrow">&#9654;</span></a>' . "\n";
	}else{

		$txtMenu[$menuItem[3]][] = '<li><a class="menuItem" ' . "\n"
				.'		 href="?module='.$menuItem[2].'&stage=1">'.$menuItem[1].'</a></li>' . "\n";
	}
}

//process the menubar
echo '<div id="menuBar">' . "\n";

foreach($mMenu as $mainMenuItems){
	if($mainMenuItems[1]== "Main") continue;

	echo '<div class="menuTitleBar" onclick="return dToggle('.$mainMenuItems[0].');">' 
		.$mainMenuItems[1] . '<div class="showhide" id="sh_'.$mainMenuItems[0].'"><img src="/images/spAcEr.gif" class="hideImg"></div></div>' 
		."\n";

		$menuPanes = $txtMenu[$mainMenuItems[0]];
		echo '<div id="menuId'.$mainMenuItems[0].'"><ul class="menu">' . "\n";
		if(is_array($menuPanes)) echo join('',$menuPanes) . "\n";
		else echo $menuPanes;
		echo '</ul></div><div class="menusep"></div>' . "\n\n";
		
	}
//include("myaccountmenu.php");
echo '</div>' . "\n";


