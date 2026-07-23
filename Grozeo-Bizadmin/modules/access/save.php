<?php

/*
 * Created on 13-Aug-08
 * hcs.ighr
 * Created by niju
 *
 */
	$strQry = 'SELECT count(*) FROM sys_module_operation where trim(coalesce(Title,"")) <> "" and trim(coalesce(Capability,"")) = "";';
	$nullcount  = $db->getItemFromDb($strQry);
	if($nullcount>0){
		echo "{success:false,'msg':'Illegal permission settings found'}";
		exit;
	}
$type = trim($_POST['type']);
$perms = stripslashes($_POST['perms']);
$restrict = trim(stripslashes($_POST['restrict']));
$id = trim($_POST['id']);

if ($perms != "" && !empty($id)) {

    /* Polishes the js array to string */
    $perms = substr($perms, 1, strlen($pems) - 1);
    $perms = preg_replace('/^(\,)+/', '', $perms);
    $perms = preg_replace('/(\,)+/', ',', $perms);
    $perms = preg_replace('/(\,)+$/', ',', $perms);

    /* capability string modification */
    $capability = preg_replace('/\"/', '', $perms);
    $capability = preg_replace('/\,/', PERM_SEPERATOR, $capability);
	
	$Totalcapability = array();
	$capabilityarr = explode(";",$capability);
	//Get operationid that does not have Title, but have the same capability
	foreach ($capabilityarr as $k => $v) {
		if ($v <> '' ){
			$strQry = "SELECT DISTINCT a.OperationId as OperationId FROM sys_module_operation a INNER JOIN sys_module_operation b ON a.ModuleName = b.ModuleName AND a.Capability = b.Capability WHERE b.OperationId = " . $v . ";";
			$currentCapability = $db->getMultipleData($strQry);
				$currentCapability = array_filter($currentCapability);
				if(!empty($currentCapability)) {
			$Totalcapability = array_merge($Totalcapability,$currentCapability );
			}
		}	
		}	

	 
    if ($restrict != "") {
        /* Polishes the restrict js array to string */
        $restrict = substr($restrict, 1, strlen($restrict) - 2);
        $restrict = preg_replace('/^(\,)+/', '', $restrict);
        $restrict = preg_replace('/(\,)+/', ',', $restrict);
        $restrict = preg_replace('/(\,)+$/', ',', $restrict);

        /* restrict capability string modification */
        $restrictCapability = preg_replace('/\"/', '', $restrict);
        $restrictCapability = preg_replace('/\,/', PERM_SEPERATOR, $restrictCapability);
		
		$TotalRestrictedcapability = array();
		$Restrictedcapabilityarr = explode(";",$restrictCapability);
		//Get operationid that does not have Title, but have the same capability
		foreach ($Restrictedcapabilityarr as $k => $v) {
		if ($v <> '' ){
			$strQry = "SELECT DISTINCT a.OperationId as OperationId FROM sys_module_operation a INNER JOIN sys_module_operation b ON a.ModuleName = b.ModuleName AND a.Capability = b.Capability WHERE b.OperationId = " . $v . ";";
			$RestrictedCapability = $db->getMultipleData($strQry);
					$RestrictedCapability = array_filter($RestrictedCapability);
					if(!empty($RestrictedCapability)) {
			$TotalRestrictedcapability = array_merge($TotalRestrictedcapability,$RestrictedCapability );
			}
		}
    }
		}

	if ($type == 'user') { 
		//$arrcapability = explode(PERM_SEPERATOR,$capability);
		//$arrrestrict = explode(PERM_SEPERATOR,$restrictCapability);
		$qry = "select group_concat(SysModOpId SEPARATOR ';') as Capability from usr_blocked_capability where UserId = " . $id . ";";
		$extrst = $db->getItemFromDB($qry);
		if ($extrst != ''){
			$arrextrestrict = explode(PERM_SEPERATOR,$extrst);
			$newarr = array_diff($arrextrestrict, $Totalcapability);
			$TotalRestrictedcapability =  array_values(array_unique(array_merge($newarr,$TotalRestrictedcapability)));
			//$restrictCapability = implode(PERM_SEPERATOR,$arrrestrict);
			$restrict = implode(",",$TotalRestrictedcapability);
		}		
	}


    $db->query('begin');	
	$Totalcapability =  array_values(array_unique($Totalcapability));
    if ($type == 'user') { // operator permissions

        /* Removing the existing details */
        //$db->query(sprintf("delete from admin_perms where uidnr_admin = '%d'",$id));
				$db->query("update " . FINASCOP_DB . "finascop_usr_master set JSCacheTime = '' where UserId = " . $id . ";");
        $db->query(sprintf("delete from usr_blocked_capability where UserId = '%d'", $id));		
        $db->query(sprintf("delete from usr_capability where UserId = '%d'", $id));
		//print_r($Totalcapability);
		//print_r($TotalRestrictedcapability);
		
        if (!empty($perms)):
            /* settings for inserting new data */
            //$query 	= 'insert into admin_perms select "'.$id.'" ,id_menu from system_module_operation where capability in ( '.$perms.' ) ';
            //$status = $db->query($query);
			
			foreach ($Totalcapability as $k => $v) {
				$data = array('UserId' => $id, 'SysModOpId' => $v);
				$status = $db->perform('usr_capability', $data);
			}

        endif;
		$TotalRestrictedcapability =  array_values(array_unique($TotalRestrictedcapability));
		if (!empty($restrict)) {
			foreach ($TotalRestrictedcapability as $k => $v) {
                $restricteddata = array('UserId' => $id, 'SysModOpId' => $v);
                $status = $db->perform('usr_blocked_capability', $restricteddata);
				}
        }
		$filename = ROOT . CACHE_PATH . '/' . $id . '.js';
            if (file_exists($filename)) {
               unlink($filename);
            }
        //deleteCache($id);
    } else { // role permissions

        /* Removing the existing details */
        $db->query(sprintf('delete from sys_role_capability where RoleId = "%d"', $id));
				$db->query("update finascop_usr_master set JSCacheTime = '' where RoleId = " . $id . ";");
        if (!empty($perms)):
            /* settings for inserting new data */
            //$query = 'insert into admin_role_perms select "'.$id.'",id_menu from system_module_operation where capability in ( '.$perms.' ) ';
            //$status = $db->query($query);
			//INsert
			
			foreach ($Totalcapability as $k => $v) {
				$data = array('RoleId' => $id, 'SysModOpId' => $v);				
				$status = $db->perform('sys_role_capability', $data);
			}
            //S: Regenerate the Cached JS files
				$qry = 'SELECT UserId FROM finascop_usr_master WHERE RoleId=' . $id;
            $rs = $db->getMultipleData($qry);
            
            if(!empty($rs)){
            foreach ($rs as $k => $v) {
                //delete userId.js file
                $filename = ROOT . CACHE_PATH . '/' . $v . '.js';
                if (file_exists($filename)) {
                    unlink($filename);
                }
                //executePHPCLICMD(array('module'=>'ui', 'op'=>'generateUserJs', 'userID'=>$rs['UserId']));
            }
            }
        //E: Regenerate the Cached JS files

        endif;
    }


    $db->query('commit');



    echo "{success:true}";
} else {
    echo "{success:false}";
}
		
