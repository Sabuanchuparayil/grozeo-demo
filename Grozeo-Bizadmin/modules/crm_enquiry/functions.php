<?php

require_once(INCLUDE_PATH . '/lib.php');
require_once(INCLUDE_PATH . '/config.php');

function listSignupEnquiry()
{
    global $db;


    $nodb = new \cgoDynamiteDB();
    $attVehicles = array();
    $attVehicles['PartitionKey'] = array('col' => 'Signupstatus', 'val' => 1, 'oper' => '=');

    $attVehicles['SortKey'] = array('col' => 'isPartner', 'val' => 1, 'oper' => '=');
    $attVehicles['IndexName'] = 'Signupstatus-isPartner-index';

    $attVehicles['queryAttributes'] = array('mobile', 'tstamp', 'uuid', 'Signupstatus');


    $rsno = $nodb->query('signuplogs', $attVehicles, 'query');
    if (isset($rsno) && count($rsno) > 0) {
        $rs = array();
        foreach ($rsno as $value) {
            array_push($rs, array(
                'mobile' => $value['mobile'],
                'tstamp' => $value['tstamp'],
                'uuid' => $value['uuid'],
                'status' => $value['Signupstatus']
            ));
        }
        $count = count($rs);
        $rs = json_encode($rs);
    } else {
        $count = 0;
        $rs = '[]';
    }
    echo '{"totalCount":' . $count . ',"data":' . $rs . '}';
}

function removeEnquiry($mobile)
{
    $arrSession = array();
    $arrSession['Data'] = array();
    $arrSession['PartitionKey'] = array('col' => 'mobile', 'val' => $mobile);
    $arrSession['SortKey'] = array('col' => 'isPartner', 'val' => (int) 1);
    array_push($arrSession['Data'], array('col' => 'status', 'val' => 7));
    array_push($arrSession['Data'], array('col' => 'Signupstatus', 'val' => 7));
    $nodb = new \cgoDynamiteDB();
    $nosession = $nodb->perform('signuplogs', 'update', $arrSession, $response);
    if (!$nosession) {
        echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
    } else {
        echo "{success: true,msg:'Removed Successfully'}";
    }
}
