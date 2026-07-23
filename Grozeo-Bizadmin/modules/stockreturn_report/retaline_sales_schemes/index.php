<?php

switch ($op){
    case 'setSchemeAsDefault':
        $qry = "UPDATE retaline_B2BScheme SET rbsch_IsGeneral = CASE WHEN rbsch_name = '{$_POST['rbsch_name']}' THEN  1 ELSE 0 END"
                . " WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}";
        $status = $db->query($qry);
        if ($status == 1) {
            $msg = "'B2B Scheme {$_POST['rbsch_name']} saved as default scheme.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error while setting  B2B Scheme. as default scheme'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;
    case 'getB2BSchemeDetails':
        $data = $_POST;
        $query = "SELECT rbsch_id, rbsch_name, rbsch_IsGeneral,rbsch_FromMrp,rbsch_ToMrp "
                . "FROM retaline_B2BScheme WHERE rbsch_id = {$data['rbsch_id']}";
        $B2BSchemeData = $db->getFromDB($query, true);
        $B2BSchemeLevels = array();
        for ($levID = 1; $levID <= 5; $levID++) {
            $listQuery = "SELECT {$levID} AS levelID, 'Level{$levID}' AS LevelName, rbsch_FromQtyL{$levID} AS fromQty,"
                    . "rbsch_ToQtyL{$levID} AS toQty,"
                    . "rbsch_CompL{$levID} AS company,rbsch_TechL{$levID} AS technology,"
                    . "rbsch_CustL{$levID} AS b2BCustomer,"
                    . "rbsch_CompL{$levID} + rbsch_TechL{$levID} + rbsch_CustL{$levID} AS b2bTotal "
                    . "FROM retaline_B2BScheme WHERE rbsch_id = {$data['rbsch_id']}";
            $B2BSchemeLevel = $db->getFromDB($listQuery, true);
            array_push($B2BSchemeLevels, $B2BSchemeLevel);
         }
        if (!empty($B2BSchemeLevels)){
            echo '{"totalCount":' . count($B2BSchemeLevels) . ',"data":' . json_encode($B2BSchemeLevels) . ',"SchemeData":' . json_encode($B2BSchemeData) . '}';
        } else{
            echo '{"totalCount":"0","data":[],"SchemeData":[]}';
        }
        break;
    case 'saveB2BScheme':
    $data = $_POST;
        $schemeLevelDetails = json_decode(stripslashes($data['schemeLevelDetails']), true);
        unset($data['schemeLevelDetails']);
        unset($data['apikey']);
        unset($data['tstamp']);
        if($data['has_rbsch_IsGeneral'] == 0){
            $data['rbsch_IsGeneral'] = 1;
        }
        unset($data['has_rbsch_IsGeneral']);

        $db->query('begin');
        if ($data['rbsch_id'] == 0){
            unset($data['rbsch_id']);
            $data['br_ID'] = $_SESSION['admin']->finascop_current_branch_id;
            $isOK = $db->perform('retaline_B2BScheme', $data);
            if ($isOK <> 1){
                $msg = "'Error while saving B2B Scheme Details.'";
                echo '{"success":false,"msg":' . $msg . '}';
                exit(1);
             }
            $data['rbsch_id'] = intval($db->insert_id());
        }
        $con = " rbsch_id = {$data['rbsch_id']}";
        $levelData = [];
        foreach ($schemeLevelDetails as $schemeLevel) {
            $levID = $schemeLevel['levelID'];
            unset($levelData);
            $levelData['rbsch_FromQtyL' . $levID] = floatval($schemeLevel['fromQty']);
            $levelData['rbsch_ToQtyL' . $levID] = floatval($schemeLevel['toQty']);
            $levelData['rbsch_CompL' . $levID] = floatval($schemeLevel['company']);
            $levelData['rbsch_TechL' . $levID] = floatval($schemeLevel['technology']);
            $levelData['rbsch_CustL' . $levID] = floatval($schemeLevel['b2BCustomer']);
            $isOK = $db->perform('retaline_B2BScheme', $levelData, 'update', $con);
            if ($isOK <> 1){
                $msg = "'Error while saving B2B Scheme Details.'";
                echo '{"success":false,"msg":' . $msg . '}';
                exit(1);
                }
            }
        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'B2B Scheme saved successfully.'";
            echo '{"success":true,"msg":' . $msg . '}';
        } else {
            $msg = "'Error while saving  B2B Scheme.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }

        break;
    case 'getSalesSchemeslist':
        $data = $_POST;

        $limit = is_numeric($data['limit']) ? $data['limit'] : 12;
        $start = is_numeric($data['start']) ? $data['start'] : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($data['sort'] ?? ''), $_allowed_sort) ? trim($data['sort']) : 'id';
        $dir = (strtoupper(trim($data['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';

        $filter_qry = " WHERE 1 = 1 AND br_ID = {$_SESSION['admin']->finascop_current_branch_id}";

        $countDataQuery = "SELECT count(1) from retaline_B2BScheme {$filter_qry} ";
        $listQuery = "SELECT rbsch_id, rbsch_name, rbsch_IsGeneral, rbsch_FromMrp, rbsch_ToMrp FROM retaline_B2BScheme {$filter_qry} GROUP BY rbsch_id ORDER BY {$sort} {$dir} LIMIT {$start},{$limit}";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
}






