<?php
/*
 * Created on 04-Aug-08
 *
 * Will Delete Users
 *
 */

  $userIDs	=	json_decode(stripslashes($_POST["uidnr_admin"]));
  $failedVals = array();
  $db->query("begin");
  foreach($userIDs as $k => $v)
  {
	$userId = intval($v);
	$status = $db->executeSafe("delete from " . FINASCOP_DB . "finascop_usr_master where UserId=?", "i", [$userId]);
        $status = $db->executeSafe("delete from " . FINASCOP_DB . "finascop_usr_profile where UserId=?", "i", [$userId]);
        $status = $db->executeSafe("delete from " . FINASCOP_DB . "finascop_user_details where UserId=?", "i", [$userId]);
        /*$updateCount = $db->getItemFromDB("SELECT ROW_COUNT()");
        if($updateCount > 0){
            array_push($failedVals, $v);
        }*/
  }
  /*if(count($failedVals) > 0){
      $failedItems = explode(',',$failedVals);
      echo "{success:false,errors:{reason:'Failed to update:'".$failedItems."}}";
      exit;
  }*/
  $db->query("commit");
  //Return Status
  if ($status){
   echo "{success:true}";
  }
