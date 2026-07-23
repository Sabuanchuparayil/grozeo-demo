<?php
/*
 * Created on 25-Aug-08
 * @author : Lakshmi Jayaram <lakshmi@saturn.in>
 *
 * It is good to write the module specific functions (used in this module only)
 */
/**
 * Retrieves the id,name from Users DB and output it as JS Array
 */
function  generateJsComboStore() {
    global $db;

    $qry	= <<<EOT
        SELECT
 					a.uidnr_admin,CONCAT(b.admin_fname," ",b.admin_lname) as name
 				FROM
 					admin_users a ,admin_profile b
 				WHERE
 					a.uidnr_admin=b.uidnr_admin AND b.admin_fname<>"" AND a.admin_super<>'1'
 				ORDER BY
 					admin_fname ASC
EOT;

    $result   = $db->getArrayFromDB($qry,FALSE);
    echo json_encode($result);
    /*$rs		= $db->query($qry);
    $i 		= 0; $num_rows 	= $db->num_rows($rs);
    echo "[";
    while($row=$db->fetch_array($rs)) {
        echo "[";
        echo "'".$row[uidnr_admin]."','".$row[name]."'";
        echo "]";
        $i++; if($i<$num_rows) echo ",";
        flush();
    }
    echo "]";*/

}


/**
 * Retrieves Enabled Company
 */
function generateCompanyComboStore() {
    global $db;

    $qry	= 'SELECT CompanyId, CompanyName FROM '.TABLE_COMPANY.' WHERE IsEnabled = "Yes" ORDER BY CompanyName ASC';
    $result   = $db->getMultipleData($qry);   
    echo json_encode($result);
    /*$rs		= $db->query($qry);
    $i 		= 0; $num_rows 	= $db->num_rows($rs);
    echo "[";
    while($row=$db->fetch_array($rs)) {
        echo "[";
        echo "'".$row[CompanyId]."','".$row[CompanyName]."'";
        echo "]";
        $i++; if($i<$num_rows) echo ",";
        flush();
    }
    echo "]";*/
}

/**
 * Checks Username or Email Duplication
 *
 * Created on 19-Jul-08
 * @author : Ratheesh Kumar CK <ratheesh@saturn.in>
 *
 * @param $string
 *   Username
 *
 * @param $string
 *   Email Address
 *
 * @param $integer
 *   USER ID - to be excluded in search - useful in Update
 *
 * @return $boolean
 *
 */
function isDuplicate ($username=false, $email=false, $userid=false) {
    global $db;

    $where  					= array();

    if ($userid)	$where[]	= " uidnr_admin<>'".$userid."'";
    if ($username && $email)
        $where[]		= " (admin_email='".$email."' or admin_username='".$username."')";
    if ($username)	$where[]	= " admin_username='".$username."'";
    if ($email)	$where[]		= " admin_email='".$email."'";

    $where 			= " where ".join(" and ", $where);


    //Check if the Same USER ID Already Exists or Not
    $qry			= "SELECT count(*) from admin_users $where";

    $duplicates	= $db->getItemFromDB($qry);
    if($duplicates>0) return TRUE;
    else				 return FALSE;
}


/**
 * Gets the modules permitted for the logged in user
 *
 * Created on 24-Nov-09
 * @author : Lakshmi Jayaram <lakshmi@saturn.in>
 *
 * @param $string
 *   UserId
 *
 *
 * @return JS Array
 *
 */
function PermittedModulesJsComboStore($RoleId,$UserId) {
    global $db;
    if(intval($RoleId == 0)) exit;
    //for super user permit all the modules..
    if(intval($UserId)>0) {
        $IsSuper    = $db->getItemFromDB('SELECT IsSuperUser FROM " . FINASCOP_DB . "finascop_usr_master WHERE UserId = '.$UserId);
        if($IsSuper == 'Yes') {
            echo "[";
            echo "['SOAdmin','Admin'],";
            echo "['project','Projects'],";
            echo "['job','Jobs'],";
            echo "['jobslite','Jobs Lite'],";
            echo "['dam','Assets'],";
            echo "['proof','Proof'],";
            echo "['delivery','Delivery'],";
            echo "['daag','Deadline'],";
            echo "['report','Reports'],";
            echo "['AdvertiserDashboard','Advertiser Dashboard']";
            echo "]";
            exit;
        }
    }

    $qry_getCapability      = 'SELECT group_concat(SysModOpId SEPARATOR ';') as Capability FROM sys_role_capability WHERE RoleId = '.$RoleId;
    $RoleCapability         = $db->getItemFromDB($qry_getCapability);
    $RoleCapability         = explode(";",$RoleCapability);
    $RoleCapability         = "'".join("','",$RoleCapability)."'";
    $qry_getModules         = 'SELECT DISTINCT ModuleName FROM sys_module_operation WHERE OperationId IN ('.$RoleCapability.') OR ((Capability="" OR Capability IS NULL) AND MenuId!=0 AND MenuId!="" AND MenuId IS NOT NULL) ';
    $rs	= $db->getArrayFromDB($qry_getModules,true);
    $modules = array(
        'SOAdmin'               =>'Admin',
        'project'               =>'Projects',
        'job'                   =>'Jobs',
        'jobslite'              =>'Jobs Lite',
        'dam'                   =>'Assets',
        'proof'                 =>'Proof',
        'delivery'              =>'Delivery',
        'daag'                  =>'Deadline',
        'report'                =>'Reports',
        'AdvertiserDashboard'   =>'Advertiser Dashboard'
    );
    foreach ($rs as $key => $value) {
        if($modules[$value[0]])
            $rs[$key] = array($value[0], $modules[$value[0]]);
        else
            array_splice($rs,$key);
    }
    echo json_encode($rs);
 }

 function stripSlashesFromValue(&$rs,$k){
     $rs[$k] = stripslashes($v);
 }
