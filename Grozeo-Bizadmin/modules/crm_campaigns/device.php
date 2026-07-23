<?php

if(!empty($_REQUEST['refId'])){
    $refId['campaign_reference_ID'] = $_REQUEST['refId'];
    $refId['campaign_reference_clickedOn'] = date('Y-m-d H:i:s');
    $status = $db->perform(FINASCOP_DB . 'campaign_reference', $refId);
}


$iPod = stripos($_SERVER['HTTP_USER_AGENT'], "iPod");
$iPhone = stripos($_SERVER['HTTP_USER_AGENT'], "iPhone");
$iPad = stripos($_SERVER['HTTP_USER_AGENT'], "iPad");
$Android = stripos($_SERVER['HTTP_USER_AGENT'], "Android");
$webOS = stripos($_SERVER['HTTP_USER_AGENT'], "webOS");


if ($iPod || $iPhone) {
    header("Location: https://apps.apple.com/in/app/brm-pocketkart/id1483187500");
} else if ($iPad) {
    header("Location: https://apps.apple.com/in/app/brm-pocketkart/id1483187500");
} else if ($Android) {
    header("Location: https://play.google.com/store/apps/details?id=in.velosit.brm");
} else if ($webOS) {
    
}



