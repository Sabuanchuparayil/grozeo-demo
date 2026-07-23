<?php
    // vim: ts=4:sw=4:nu:fdc=4
    $user = isset($_COOKIE["user"]) ? $_COOKIE["user"] : (string) rand();
    @setcookie("user", $user, time() + 365 * 24 * 3600);

    $clientArgs = new stdClass();
    // get posted values
    $clientArgs->id = isset($_POST["id"]) ? $_POST["id"] : 1;
    $clientArgs->user = isset($_POST["user"]) ? $_POST["user"] : $_SESSION['admin']->Finascop_UserId;
    $clientArgs->session = isset($_POST["session"]) ? $_POST["session"] : session_id();
    //
    //MODIFIED BY LAKSHMI J
    //check whether any preference is set for the default view.
    //if so, load details from sys_view_state table rather than tmp_state
    /*if(!empty($_SESSION['admin']->ViewId)){
        //$sql =   "SELECT Name as name, Value as value FROM sys_view_state WHERE ViewId=".$_SESSION['admin']->ViewId;
        //check whther the view is set for default site
        $defSite = $db->query("select SiteId from sit_user_map where UserId=$clientArgs->user and `Default`='Yes'");
        if(!empty($defSite)){
            $sql =   "SELECT Name as name, Value as value FROM sys_view_state WHERE ViewId=".$_SESSION['admin']->ViewId;
        }else{
             $sql =   "SELECT Name as name, Value as value FROM tmp_state WHERE "
            ."UserId='{$clientArgs->user}' and SessionId='{$clientArgs->session}'";
        }
    }*/
    
    $DefViewId = $db->getItemFromDB("SELECT Varvalue from usr_preference WHERE UserId=".$_SESSION['admin']->Finascop_UserId." AND Varname='default_view'");
    if(!empty($DefViewId)){   
            $sql =   "SELECT Name as name, Value as value FROM sys_view_state WHERE ViewId=$DefViewId";      
            $defaultViewName = $db->getItemFromDB("SELECT ViewName FROM sys_views WHERE ViewId=$DefViewId");          
    }
    
    else{
    $sql =   "SELECT Name as name, Value as value FROM tmp_state WHERE "
            ."UserId='{$clientArgs->user}' and SessionId='{$clientArgs->session}'"
    ;
    $defaultViewName = "Default SO View";
    }    
    $ostmt = $db->query($sql);
    $state = array();
    if($db->num_rows($ostmt)>0)
    while($row = $db->fetch_object($ostmt)){
      $state[] = $row;
    }
    $state = json_encode($state);
?>
<script type="text/javascript">
Ext.state.Manager.setProvider(new Ext.ux.state.HttpProvider({
	 url:'?module=ui&op=ui-state'//'state-sqlite.php?#state'
	,user:'<?=$_SESSION['admin']->Finascop_UserId?>'
	,session:'<?=session_id()?>'
	,id:'<?=$clientArgs->id?>'
	,readBaseParams:{cmd:'readState'}
	,saveBaseParams:{cmd:'saveState'}
	,autoRead:false
	,logFailure:false
	,logSuccess:false
    ,delay:10
}));
Ext.state.Manager.getProvider().initState(<?=$state;?>);
_SESSION.DefaultView = "<?=$defaultViewName;?>";
</script>
<?php
// eof
?>
