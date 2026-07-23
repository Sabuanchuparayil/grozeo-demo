<?php
// vim: ts=4:sw=4:nu:fdc=4
if(isset($_POST["data"])) $_POST["data"] = stripslashes($_POST["data"]);
// get posted values
$cmd = isset($_POST["cmd"]) ? $_POST["cmd"] : false;
$clientArgs->id = isset($_POST["id"]) ? $_POST["id"] : 1;
$clientArgs->user = isset($_POST["user"]) ? $_POST["user"] : $_SESSION['admin']->Finascop_UserId;
$clientArgs->session = isset($_POST["session"]) ? $_POST["session"] : session_id();
$clientArgs->data = isset($_POST["data"]) ? json_decode($_POST["data"]) : array();

if(isset($_POST["ViewId"])){
    $clientArgs->viewid = trim($_POST['ViewId']);
}

if($cmd == "saveSessionState"){
    $clientArgs->viewname    = trim($_POST["ViewName"]);
    $clientArgs->description = trim($_POST["view_description"]);
    $clientArgs->ispublic    = ($_POST["is_public"]=="on") ? 'Yes':'No';
	$clientArgs->siteid		 = $_POST["current_site_id"];		// Current selected site id
}

//createTable($odb);

if(!$cmd) {
    echo '{"success":false,"error":"No command"}';
    exit;
}

// execute command
$cmd($db, $clientArgs);
exit;

// {{{
/**
 * readState: reads state
 *
 * @author    Ing. Jozef Sakáloš <jsakalos@aariadne.com>
 * @date      24. March 2008
 * @return    void
 * @param     PDO $odb
 * @param     object $clientArgs
 */
function readState($odb, $clientArgs) {

    if($clientArgs->viewid!=""){              
        $sql =
            "select Name as name, Value as value from sys_view_state b, sys_views a where "
            ." a.ViewId = b.ViewId AND a.ViewId=".$clientArgs->viewid
        ;
    }else{        
        $sql =
            "select Name as name, Value as value from tmp_state where "
            ."UserId='{$clientArgs->user}' and SessionId='{$clientArgs->session}'"
        ;
    }
    try {
        $rs = $odb->query($sql);
        $data = array();
        while($row = $odb->fetch_object($rs)) {
            $data[] = $row;
        }
    }
    catch(Exception $e) {
        echo "{\"success\":false,\"error\":\"$e\"}";
        exit;
    }

    $o = array(
        "success"=>true
        ,"data"=>$data
    );
    echo json_encode($o);
} // eo function readState
// }}}
// {{{
/**
 * saveState: saves state
 *
 * @author    Ing. Jozef Sakáloš <jsakalos@aariadne.com>
 * @date      24. March 2008
 * @return    void
 * @param     PDO $odb
 * @param     object $clientArgs
 */
function saveState($odb, $clientArgs) {    
    if(is_array($clientArgs->data))
        foreach($clientArgs->data as $row) {
            $sql =
                "replace into tmp_state (StateId,UserId,SessionId,Name,Value) values"
                ." (StateId, '{$clientArgs->user}','{$clientArgs->session}','{$row->name}','{$row->value}')"
            ;
            try {
                $odb->query($sql);
            }
            catch(Exception $e) {
                echo "{\"success\":false,\"error\":\"$e\"}";
                exit;
            }
        }
    echo '{"success":true}';
} // eo function saveState
// }}}

// {{{
/**
 * saveSessionState: Saves the Current State as a View to the User
 *
 * @author    Ratheesh Kumar CK <ratheesh@saturn.in>
 * @date      09. October 2009
 * @return    void
 * @param     MySql DB Object $odb
 * @param     object $clientArgs
 */
function saveSessionState($odb, $clientArgs) {
    try{
        if(empty($clientArgs->viewname)) throw new MyException('View Name can not be blank.');       
        //Insert Into Views Table
        $tmp = array(
           'ViewName' => $clientArgs->viewname
          ,'IsPublic' => $clientArgs->ispublic
          ,'CreatedBy' => $clientArgs->user
          ,'CreatedOn' => 'now()'
          ,'Description' => $clientArgs->description
		  ,'SiteId'=>$clientArgs->siteid					//SiteId added 
        );
        /*$sql =  "REPLACE INTO sys_views (ViewName,IsPublic,CreatedBy,CreatedOn,Description) values"
               ." ('{$clientArgs->viewname}', '{$clientArgs->ispublic}', '{$clientArgs->user}', now(), '{$clientArgs->description}')"
        ;*/
        if(empty($clientArgs->viewid)){
           $odb->perform("sys_views",$tmp);
           $viewID = $odb->insert_id();
        }else{           
            $viewID = $odb->perform("sys_views",$tmp,"update","ViewId = $clientArgs->viewid");
            $viewID = $clientArgs->viewid;
        }
        //$odb->query($sql);
        if($viewID!=""){
            //echo $qry = 'REPLACE INTO sys_view_state(ViewId, Name, Value) SELECT '.$viewID.' as ViewId, Name, Value  FROM tmp_state WHERE SessionId="'.$clientArgs->session.'" AND UserId="'.$clientArgs->user.'"';
            $odb->query('REPLACE INTO sys_view_state(ViewId, Name, Value) SELECT '.$viewID.' as ViewId, Name, Value  FROM tmp_state WHERE SessionId="'.$clientArgs->session.'" AND UserId="'.$clientArgs->user.'"');
        }
        //if is default is on,update user_preference table with the inserted view id.
        if($_POST["is_default"] == "on"){
            $odb->query("UPDATE usr_preference SET Varvalue = $viewID WHERE Varname = 'default_view' AND UserId = ".$clientArgs->user);
        }
        echo '{"success":true}';
    }catch(Exception $e) {
        echo "{\"success\":false,\"error\":\"{$e->getMessage()}\"}";
        exit;
    }
} // eo function saveSessionState
// }}}

// {{{
/**
 * getViewsComboStore: Returns List of data to be used for Combo Store
 *
 * @author    Ratheesh Kumar CK <ratheesh@saturn.in>
 * @date      09. October 2009
 * @return    void
 * @param     MySql DB Object $db
 * @param     object $clientArgs
 */
/*
 * Modified :Sreeram 7-Apr-2010
 * Reason: Views made site specific
 *         and dropdowns in 'Preferences' window and 'Save this view' window filled based on UserId
 * Changes :Added a Query to fetch siteids from map table based on userid
 */
function getViewsStore($db, $clientArgs) {
   $defaultViewId = $db->getItemFromDB('SELECT Varvalue FROM usr_preference WHERE Varname = "default_view" AND UserId =' . $_SESSION['admin']->Finascop_UserId);
    $whr = "";
    if(!empty($_POST['query'])){
        $whr = "  AND (ViewName LIKE '".$_POST['query']."%')";
    }
     $SiteId = $_POST['siteid'];
        
   $qry =<<<EOT
        SELECT
                ViewId, ViewName, IsPublic, CreatedBy, Description
        FROM
                sys_views
        WHERE
                 (IsPublic='Yes' OR CreatedBy='{$clientArgs->user}') AND SiteId=$SiteId $whr
        ORDER BY
                ViewName ASC
EOT;
								// Query Modified. Added one more condition on SiteId(Where clause)-Sreeram

	$rs = $db->query($qry);
	$i = 0;
	$num_rows = $db->num_rows($rs);
	echo "[";
        //echo "['-1','Default View'],";
	while ($row = $db->fetch_row($rs)) {
            if($row[0]==$clientArgs->user && $row[2]=='No'){
                $row[5] = 'My Private';
            }elseif($row[0]==$clientArgs->user && $row[2]=='Yes'){
                $row[5] = 'My Shared Views';
            }else{
                $row[5] = 'Public Views';
            }
            $row[1] = stripslashes($row[1]);
            if($row[0] == $defaultViewId){
                $row[1] = $row[1].' (Default)';
            }
            echo $comma;
            echo json_encode($row);
            flush();
            $comma = ',';
	}
	echo ",[0,'Default SO View','','','','']";
	echo "]";
}
// {{{
/**
 * createTable: create state table if it doesn't exist
 *
 * @author    Ing. Jozef Sakáloš <jsakalos@aariadne.com>
 * @date      24. March 2008
 * @param     PDO $odb
 * @return    void
 */
function createTable($odb) {
// check if table exists
    $ostmt = $odb->query("select name from sqlite_master where type='table' and name='state'");
    $table = $ostmt->fetchAll(PDO::FETCH_NUM);
    if(!sizeof($table)) {
    // create table
        $sql =
            "create table state"
            ."(id integer"
            .",user varchar(40)"
            .",session varchar(80)"
            .",name varchar(80)"
            .",value text"
            .")"
        ;
        $odb->exec($sql);

        // create unique index
        $sql = "create unique index idx on state(id,user,session,name)";
        $odb->exec($sql);
    }
} // eo function createTable
// }}}

/**
 * deleteTempState: Deletes temperorary state of a current session so that user will be provided with the default view
 *
 * @author    Lakshmi Jayaram <lakshmi@saturn.in>
 * @date      07 Dec 2009
 * @return    void
 *
 */
 function deleteTempState($odb,$clientArgs){
     //delete temporary view entry from tmp_state
     $qry     = "DELETE FROM tmp_state WHERE SessionId ='".$clientArgs->session."'";
     $status  = $odb->query($qry);
     if($status){
         echo "{success:true}";
     }
 }
// eof

/**
 * Select Views depending on user's default workflow.
 *
 * @author    Lakshmi Jayaram <lakshmi@saturn.in>
 * @date      01 Sep 2010
 * @return    js array
 *
 */
 function getViewsDependingOnWorkflow($odb,$clientArgs){
     $userId          = $clientArgs->user;
     $qry             = "SELECT SiteId FROM sit_user_map WHERE UserId= $userId AND `Default`='Yes'";
     $defaultWorkflow = $odb->getItemFromDB($qry);
     if(!empty($defaultWorkflow)){
         $qry =<<<EOT
        SELECT
                ViewId, ViewName, IsPublic, CreatedBy, Description
        FROM
                sys_views
        WHERE
                (IsPublic='Yes' OR CreatedBy='{$clientArgs->user}') AND SiteId=$defaultWorkflow
        ORDER BY
                ViewName ASC
EOT;
								// Query Modified. Added one more condition on SiteId(Where clause)-Sreeram

	$rs = $odb->query($qry);
	$i = 0;
	$num_rows = $odb->num_rows($rs);
	echo "[";    
	while ($row = $odb->fetch_row($rs)) {            
            $row[1] = stripslashes($row[1]);          
            echo $comma;
            echo json_encode($row);
            flush();
            $comma = ',';
	}
	echo "]";
     }

 }


?> 
