<?php

require_once(INCLUDE_PATH . '/lib.php');
require_once(INCLUDE_PATH . '/config.php');

function getUsedVehicleDetails($apikey) {
    $nodb = new \cgoDynamiteDB();
    $arrLive = array();
    $arrLive['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey, 'oper' => '=');
    $arrLive['getAttributes'] = array('v_no', 'createddatetime', 'DriverName');
    $nors = $nodb->query('QugeoLiveVehicles', $arrLive, 'getItem');

    if (isset($nors) && count($nors) > 0) {
        return $nors;
    } else {
        return [];
    }
}
