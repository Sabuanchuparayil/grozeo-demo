<?php
function _getCallRecReportGridData($postvar)
{

    global $supportdb;
    $limit = $postvar['limit'];
    $start = $postvar['start'];
    $limit = is_numeric($limit) ? $limit : 22;
    $start = is_numeric($start) ? $start : 0;
    $sort = 'id';
    $dir = 'DESC';
    $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
    $userID = $_SESSION['admin']->UserId;
    $searchitem = "";

    $search = " WHERE 1=1  ";
    $filter = $_POST['filter'];
    if (isset($filter)) {
        foreach ($filter as $key => $field) {
            switch ($field['data']['type']) {
                case 'list':
                    if ($field['field'] == 'status') {
                        if ($field['data']['value'] == 'Active') {
                            $fiterItem = 1;
                            $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                        } else if ($field['data']['value'] == 'Inactive') {
                            $fiterItem = 0;
                            $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                        } else {
                            $searchitem .= " and (status = 1 or status=0) ";
                        }
                    }
                    break;
                case 'date':
                    if ($field['field'] == 'calledOn') {

                        switch ($field['data']['comparison']) {
                            case 'gt':
                                $searchitem .= " and DATE_FORMAT(StartTime,'%Y-%m-%d') > '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            case 'lt':
                                $searchitem .= " and DATE_FORMAT(StartTime,'%Y-%m-%d') < '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            case 'eq':
                                $searchitem .= " and DATE_FORMAT(StartTime,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            default:
                                $searchitem .= " and DATE_FORMAT(StartTime,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                        }
                    }
                    break;
                default:
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                    }
            }
        }
    }
    if ($_POST['caller_Name'] != '') {
        $searchitem .= " AND AgentName  LIKE  '" . $_POST['caller_Name'] . "%'";
    }
    if (!empty($_POST['search_cr_from_date']) && !empty($_POST['search_cr_to_date'])) {
        $searchitem .= " and DATE_FORMAT(createdOn, '%Y-%m-%d') BETWEEN '" . date('Y-m-d', strtotime($_POST['search_cr_from_date'])) . "' AND '" . date('Y-m-d', strtotime($_POST['search_cr_to_date'])) . "'";
    }
    $query = "SELECT  id,AudioFile,AudioPath,CASE WHEN AudioPath LIKE 'http%' THEN AudioPath ELSE AudioFile END AS AudioURL,HoldDuration,WrapUpDuration,ConfDuration,CustomerStatus,AgentStatus,
        DialStatus,Comments,Status,CallerID,StartTime,EndTime,TimeToAnswer,CallDuration,Duration,AgentName,
        Disposition,HangupBy,DATE_FORMAT(StartTime,'%d-%m-%Y') AS calledOn,createdOn FROM call_communications ";

    $countQuery = "SELECT count(*) from call_communications {$search} {$searchitem} ";
    $listQuery = "SELECT * FROM({$query}) as orderList  {$search} {$searchitem} ORDER BY  {$sort} {$dir} limit " . $start . "," . $limit;
    return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
}
function JobLogReportGridData($postvar)
{
    global $supportdb;
    $limit = $postvar['limit'];
    $start = $postvar['start'];
    $limit = is_numeric($limit) ? $limit : 22;
    $start = is_numeric($start) ? $start : 0;
    $sort = 'ojl.id';
    $dir = 'DESC';
    $searchitem = "";
    $search = " WHERE 1=1  ";
    $filter = $_POST['filter'];
    if (isset($filter)) {

        foreach ($filter as $key => $field) {
            switch ($field['data']['type']) {
                case 'list':
                    if ($field['field'] == 'status') {
                        if ($field['data']['value'] == 'Active') {
                            $fiterItem = 1;
                            $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                        } else if ($field['data']['value'] == 'Inactive') {
                            $fiterItem = 0;
                            $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                        } else {
                            $searchitem .= " and (status = 1 or status=0) ";
                        }
                    }

                    break;
                case 'date':
                    if ($field['field'] == 'calledOn') {

                        switch ($field['data']['comparison']) {
                            case 'gt':
                                $searchitem .= " and DATE_FORMAT(ojl.actionOn,'%Y-%m-%d') > '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            case 'lt':
                                $searchitem .= " and DATE_FORMAT(ojl.actionOn,'%Y-%m-%d') < '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            case 'eq':
                                $searchitem .= " and DATE_FORMAT(ojl.actionOn,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                            default:
                                $searchitem .= " and DATE_FORMAT(ojl.actionOn,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                break;
                        }
                    }
                    break;
                default:


                    $checkComa = strstr($field['data']['value'], ',');

                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                    }
            }
        }
    }

    if ($_POST['caller_Name'] != '') {
        $searchitem .= " AND ojl.actionBy  =  " . $_POST['caller_Name'];
    }
    if (!empty($_POST['search_cl_from_date']) && !empty($_POST['search_cl_to_date'])) {
        $searchitem .= " and DATE_FORMAT(ojl.actionOn, '%Y-%m-%d') BETWEEN '" . date('Y-m-d', strtotime($_POST['search_cl_from_date'])) . "' AND '" . date('Y-m-d', strtotime($_POST['search_cl_to_date'])) . "'";
    }

    $countQuery = "SELECT COUNT(*) FROM outbound_jobs_log ojl LEFT JOIN outbound_jobs oj ON oj.id = jobId  
    LEFT JOIN support_user_events sue ON sue.id = eventId {$search} {$searchitem} ";
    $listQuery = "SELECT ojl.id,actionBy,actionOn,actionRemark,jobTitle,calleeName,
    calleeMobile,eventName,ojl.actionOn AS createdOn,DATE_FORMAT(ojl.actionOn,'%d-%m-%Y') AS calledOn,isManual,ojl.actionBy AS createdBy 
    FROM outbound_jobs_log ojl LEFT JOIN outbound_jobs oj ON oj.id = jobId  
    LEFT JOIN support_user_events sue ON sue.id = eventId {$search} {$searchitem}
    ORDER BY {$sort} {$dir} limit $start,$limit";
    return array('countQuery' => $countQuery, 'listQuery' => $listQuery);
}
function _exportExcelReport($data)
{


    global $db;
    require_once INCLUDE_PATH . '/simpleExcelWriter.php';

    $query = $_SESSION['Export']['Query'];

    $heads = json_decode(stripslashes($data['headers']), true);
    $fields = json_decode(stripslashes($data['dataindexes']), true);
    $excel = new simpleExcelWriter($db);
    $time = date('YmdHis');
    if (!empty($data['caller_Name'])) {
        $excel->exportFile = $data['caller_Name'] . $time . '.xls';
    } else {
        $excel->exportFile = $_SESSION['Export']['Settings']['title'] . $time . '.xls';
    }

    $excel->totalFields = (isset($_SESSION['Export']['Settings']['totalFields'])) ? $_SESSION['Export']['Settings']['totalFields'] : false;
    $excel->exportSupport($query, $heads, $fields);
    exit();
}
switch ($op) {
    case 'startJob':
        $status = OutboundJobs::createEventBasedJobs($db, $supportdb);

        if ($status == true) {
            echo "{success: true,msg:'Job Created'}";
        } else {
            echo "{success: false,msg: 'No jobs to create.' }";
        }
        break;
    case 'listOutboundCalls':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = 'oj.id';
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1  ";
        $filter = $_POST['filter'];
        $today = date("Y-m-d");
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Active') {
                                $fiterItem = 1;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Inactive') {
                                $fiterItem = 0;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (status = 1 or status=0) ";
                            }
                        }

                        break;
                    case 'date':
                        if ($field['field'] == 'createdDate') {

                            switch ($field['data']['comparison']) {
                                case 'gt':
                                    $searchitem .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') > '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'lt':
                                    $searchitem .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') < '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'eq':
                                    $searchitem .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                default:
                                    $searchitem .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                            }
                        } elseif ($field['field'] == 'followupOn') {

                            switch ($field['data']['comparison']) {
                                case 'gt':
                                    $searchitem .= " and DATE_FORMAT(followupDate,'%Y-%m-%d') > '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'lt':
                                    $searchitem .= " and DATE_FORMAT(followupDate,'%Y-%m-%d') < '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'eq':
                                    $searchitem .= " and DATE_FORMAT(followupDate,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                default:
                                    $searchitem .= " and DATE_FORMAT(followupDate,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                            }
                        }
                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            if ($field['field'] == 'calleeTypeName') {
                                $searchitem .= " and (au.name LIKE '{$field['data']['value']}%') ";
                            } else if ($field['field'] == 'statusName') {
                                $searchitem .= " and (es.name LIKE '{$field['data']['value']}%') ";
                            } else {
                                $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                            }
                        }
                }
            }
        }

        $userAcceptCount = $supportdb->getItemFromDB("SELECT COUNT(*) FROM outbound_jobs WHERE assignedTo = {$_SESSION['admin']->Finascop_UserId} AND status = 2");
        if ($userAcceptCount > 0) {
            $searchitem .= " AND assignedTo = {$_SESSION['admin']->Finascop_UserId} AND status = 2";
        } else {
            $searchitem .= " AND status IN (1,2) OR (followupDate LIKE '{$today}%' AND status = 1)";
        }

        $countQuery = "SELECT COUNT(*) FROM outbound_jobs oj 
        INNER JOIN support_applicable_users au ON au.id = calleeType 
        INNER JOIN support_event_status es ON es.id = `status` 
        INNER JOIN support_user_events ue ON ue.id = eventId {$search} {$searchitem} ";
        $listQuery = "SELECT oj.id,eventId,if(eventId > 0,eventName,jobTitle) as eventName,calleeId,calleeName,calleeMobile,calleeType,au.name as calleeTypeName,eventRank,STATUS,
        es.name AS statusName,DATE_FORMAT(createdOn,'%d-%m-%Y') AS createdDate,
        DATE_FORMAT(createdOn,'%H:%i:%s') AS createdTime,assignedTo,assignedOn,callerName,DATE_FORMAT(followupDate,'%d-%m-%Y %H:%i %p') AS followupDate,DATE_FORMAT(followupDate,'%d-%m-%Y %H:%i %p') AS followupOn  FROM outbound_jobs oj 
        INNER JOIN support_applicable_users au ON au.id = calleeType 
        INNER JOIN support_event_status es ON es.id = `status` 
        LEFT JOIN support_user_events ue ON ue.id = eventId {$search} {$searchitem}
        ORDER BY CASE
        WHEN followupDate = '{$today}' THEN 1 
        WHEN followupDate IS NOT NULL THEN 3 
        WHEN followupDate IS NULL THEN 2      
        ELSE 4                                
    END,
    followupDate DESC,eventRank ASC limit $rec_start,$rec_limit";

        $supportdb->printGridJson($countQuery, $listQuery);
        break;

    case 'acceptJob':
        $jobId = $_POST['jobId'];
        $sgData = $supportdb->getFromDB("SELECT assignedTo FROM outbound_jobs WHERE id = {$jobId}");
        if ($sgData['assignedTo'] != 0 && $sgData['assignedTo'] != $_SESSION['admin']->Finascop_UserId) {
            echo "{success: false,msg:'Job already Assigned.'}";
            exit();
        }
        $userAcceptCount = $supportdb->getItemFromDB("SELECT COUNT(*) FROM outbound_jobs WHERE assignedTo = {$_SESSION['admin']->Finascop_UserId} and id <> {$jobId}");
        if ($userAcceptCount > 0) {
            echo "{success: false,msg:'You are assigned with another job.'}";
            exit();
        }
        if ($sgData['assignedTo'] == $_SESSION['admin']->Finascop_UserId) {
            echo "{success: true,msg:'Manage Jobs.'}";
            exit();
        }
        if ($sgData['assignedTo'] == 0  && $userAcceptCount == 0) {

            $accData['assignedTo'] = $_SESSION['admin']->Finascop_UserId;
            $accData['assignedOn'] = date('Y-m-d H:i:s');
            $accData['callerName'] = $_SESSION['admin']->UserName;
            $accData['status'] = 2;

            $jobDetails = $supportdb->getFromDB("SELECT jobTitle,calleeType FROM outbound_jobs WHERE id = {$jobId}", true);
            $jobtitle = $jobDetails['jobTitle'];
            $calleeType = $jobDetails['calleeType'];
            $supportdb->query('begin');
            $status = $supportdb->perform('outbound_jobs', $accData, 'update', " id = {$jobId} AND assignedTo <> {$_SESSION['admin']->Finascop_UserId}");
            $status = $supportdb->query('commit');
            if ($status == 1) {
                echo "{success: true,msg:'Job Accepted.',calleeType:'" . $calleeType . "',jobTitle:'" . $jobtitle . "',jobId:'" . $jobId . "'}";
            } else {
                echo "{success: false,msg: 'Error occured while saving data' }";
            }
        }
        break;
    case 'listOutboundEvents':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'sue.id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1  ";
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Active') {
                                $fiterItem = 1;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Inactive') {
                                $fiterItem = 0;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (status = 1 or status=0) ";
                            }
                        }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }



        $countQuery = "SELECT COUNT(*) FROM support_user_events sue
        INNER JOIN support_applicable_users au ON au.id = UserId {$search} {$searchitem} ";
        $listQuery = "SELECT sue.id,au.name AS userName,eventName,`rank` FROM support_user_events sue  
        INNER JOIN support_applicable_users au ON au.id = UserId {$search} {$searchitem}
        ORDER BY `{$sort}` {$dir} limit $rec_start,$rec_limit";

        $supportdb->printGridJson($countQuery, $listQuery);
        break;
    case 'chooseJob':
        $today = date("Y-m-d");
        $currentTime = date("Y-m-d H:i");

        $intjoblog['actionBy'] = $_SESSION['admin']->Finascop_UserId;
        $intjoblog['actionOn'] = date('Y-m-d H:i:s');
        $intjoblog['actionRemark'] = 'Clicks Job Assign button';
        $intjoblog['jobId'] = 0;
        $status = $supportdb->perform('outbound_jobs_log', $intjoblog);

        $userAcceptCount = $supportdb->getFromDB("SELECT * FROM outbound_jobs WHERE assignedTo = {$_SESSION['admin']->Finascop_UserId}", true);
        if ($userAcceptCount['id'] > 0) {
            $result['success'] = true;
            $result['msg'] = "Loading current job.";
            $result['jobId'] = $userAcceptCount['id'];
            $result['jobTitle'] = $userAcceptCount['jobTitle'];
            $result['calleeType'] = $userAcceptCount['calleeType'];
            $result['phone'] = $userAcceptCount['calleeMobile'];
        } else {
            $recentJobs = $supportdb->getItemFromDB("SELECT GROUP_CONCAT(DISTINCT(jobId)) FROM outbound_jobs_log WHERE actionOn LIKE '{$today}%' ");
            //actionBy = {$_SESSION['admin']->Finascop_UserId} AND
            /*$jobToAssign = $supportdb->getFromDB("SELECT * FROM outbound_jobs 
            WHERE assignedTo = 0 AND status = 1  and followupDate LIKE '{$currentTime}%'  
            ORDER BY followupDate DESC", true);*/
            $jobToAssign = $supportdb->getFromDB("SELECT * 
            FROM outbound_jobs 
            WHERE assignedTo = 0 AND eventId <> 13 
                AND status = 1 
                AND followupDate BETWEEN 
                    DATE_SUB(CONCAT(CURDATE(), ' ', TIME_FORMAT(CURRENT_TIME(), '%H:%i')), INTERVAL 10 MINUTE) 
                    AND 
                    DATE_ADD(CONCAT(CURDATE(), ' ', TIME_FORMAT(CURRENT_TIME(), '%H:%i')), INTERVAL 10 MINUTE)
            ORDER BY followupDate DESC", true);
            //
            if ($jobToAssign['id'] == 0) {
                if (!empty($recentJobs))
                    //$cond = " AND id NOT IN ({$recentJobs})";
                    $cond = " AND id NOT IN (SELECT jobId FROM outbound_jobs_log WHERE actionOn LIKE '{$today}%')";
                else
                    $cond = " ";
                $jobToAssign = $supportdb->getFromDB("SELECT * FROM outbound_jobs 
                WHERE assignedTo = 0 AND status = 1 AND eventId <> 13 and followupDate IS NULL 
                  {$cond} 
                ORDER BY eventRank ASC,
                CASE WHEN assignedOn IS NULL THEN 1 ELSE 2                                
    END, assignedOn ASC ", true);
                //AND calleeMobile <> '9847915865'
                //AND DATE_FORMAT(followupDate,'%Y-%m-%d') <> '{$today}'
                $jobAssigned = $jobToAssign['id'];
            } else {
                $jobAssigned = $jobToAssign['id'];
            }


            $accData['assignedTo'] = $_SESSION['admin']->Finascop_UserId;

            $accData['assignedOn'] = date('Y-m-d H:i:s');
            $accData['callerName'] = $_SESSION['admin']->UserName;
            $accData['status'] = 2;


            if ($jobAssigned > 0) {
                $result['jobTitle'] = $jobToAssign['jobTitle'];
                $supportdb->query('begin');
                $status = $supportdb->perform('outbound_jobs', $accData, 'update', " id = {$jobAssigned} AND assignedTo = 0");

                $joblog['actionBy'] = $_SESSION['admin']->Finascop_UserId;
                $joblog['actionOn'] = date('Y-m-d H:i:s');
                $joblog['actionRemark'] = 'Job Started';
                $joblog['jobId'] = $jobAssigned;
                $status = $supportdb->perform('outbound_jobs_log', $joblog);
                $status = $supportdb->query('commit');

                $result['success'] = true;
                $result['msg'] = "Assigned Job.";
                $result['jobId'] = $jobAssigned;
                $result['calleeType'] = $jobToAssign['calleeType'];
                $result['phone'] = $jobToAssign['calleeMobile'];
            } else {
                $result['success'] = false;
                $result['msg'] = "No Jobs to assign.";
            }
        }
        echo json_encode($result);
        break;
    case 'listAvailableCallEvents':
        $rec_limit = empty($_POST['limit']) ? 500 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = " WHERE 1=1 and (user_call_events.UserId IS NULL OR user_call_events.UserId <> {$_POST['userId']})";

        $data = $_POST;

        if (isset($data['filter'])) {
        $allowedFields = ['call_id', 'call_date', 'call_status', 'customer_name', 'customer_phone', 'agent_name'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(id) from support_user_events left join user_call_events on eventId = id  {$filter_part} ORDER BY $rec_sort $rec_sort_dir ";
        $listQuery = "SELECT id,eventName FROM support_user_events left join user_call_events on eventId = id  {$filter_part}  group by id ORDER BY $rec_sort $rec_sort_dir  LIMIT $rec_start,$rec_limit ";
        $supportdb->printGridJson($countQuery, $listQuery);
        break;

    case 'listUserMappedCallEvents':
        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = " WHERE 1=1 and user_call_events.UserId = {$_POST['userId']}";

        $data = $_POST;

        if (isset($data['filter'])) {
        $allowedFields = ['call_id', 'call_date', 'call_status', 'customer_name', 'customer_phone', 'agent_name'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(id) from support_user_events left join user_call_events on eventId = id  {$filter_part}";
        $listQuery = "SELECT id,eventName FROM support_user_events left join user_call_events on eventId = id  {$filter_part}  ORDER BY $rec_sort $rec_sort_dir ";
        $supportdb->printGridJson($countQuery, $listQuery);
        break;
    case 'mapCallEventsToUser':
        $brandarr = $_POST['brandarr'];
        $userId = $_POST['userId'];
        $itemdecode = json_decode($brandarr);
        $itemcount = count($itemdecode);
        for ($i = 0; $i < $itemcount; $i++) {
            $entryCount = $supportdb->getItemFromDB("SELECT COUNT(*) FROM user_call_events WHERE eventId = {$itemdecode[$i]} AND user_call_events.UserId = {$userId}");
            if ($entryCount == 0) {
                $brndMapData["eventId"] = $itemdecode[$i];
                $brndMapData["UserId"] = $userId;
                $status = $supportdb->perform('user_call_events', $brndMapData);
            }
        }
        $status = $supportdb->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'removeCallEventFromUser':
        $eventId = $_POST['eventId'];
        $userId = $_POST['userId'];
        $supportdb->query('begin');
        $logdata['userId'] = $userId;
        $logdata['eventId'] = $eventId;
        $status = $supportdb->perform('user_event_remove_log', $logdata);
        $delqry = "DELETE FROM user_call_events WHERE eventId = {$eventId} AND user_call_events.UserId = {$userId}";
        $status = $supportdb->query($delqry);
        $status = $supportdb->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Event removed from user'}";
        } else {
            echo "{success: false,msg: 'Reconciliation Successful'}";
        }
        break;
    case 'exitJobFromUser':
        $supportdb->query('begin');

        $jobId = $supportdb->getItemFromDB("SELECT ID FROM outbound_jobs WHERE assignedTo = {$_SESSION['admin']->Finascop_UserId}");
        $joblog['actionBy'] = $_SESSION['admin']->Finascop_UserId;
        $joblog['actionOn'] = date('Y-m-d H:i:s');
        $joblog['actionRemark'] = 'Exited from Job';
        $joblog['jobId'] = $jobId;
        $status = $supportdb->perform('outbound_jobs_log', $joblog);

        $eventdata['assignedTo'] = 0;
        $eventdata['status'] = 1;
        $eventdata['callerName'] = '';
        $status = $supportdb->perform('outbound_jobs', $eventdata, 'update', " id = {$jobId}");

        $status = $supportdb->query('commit');
        if ($status) {
            echo "{success: true,msg:'User exited from job'}";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
    case 'listCallLogs':

        $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

        $qry = JobLogReportGridData($_POST);

        $data = $supportdb->getMultipleData($qry['listQuery'], true);

        $count = $supportdb->getItemFromDB($qry['countQuery']);


        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['createdOn'] = date("d-m-Y H:i:s", strtotime($data[$i]['createdOn']));
                $data[$i]['caller'] = $db->getItemFromDB("SELECT CONCAT(FirstName,'',LastName) FROM finascop_usr_profile WHERE UserId = {$data[$i]['actionBy']}");
                if ($data[$i]['isManual'] == 1)
                    $data[$i]['createdBy'] = $db->getItemFromDB("SELECT CONCAT(FirstName,'',LastName) FROM finascop_usr_profile WHERE UserId = {$data[$i]['createdBy']}");
                else
                    $items[$i]['createdBy'] = "System";
            }
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'callLogDetailsView':
        $jobId = isset($_POST['jobId']) ? intval($_POST['jobId']) : 0;
        if ($jobId > 0) {
            $data = $supportdb->getFromDB("SELECT ojl.id,actionBy,actionOn,actionRemark,jobTitle,calleeName,
            calleeMobile,eventName,ojl.actionOn AS createdOn,isManual,ojl.actionBy AS createdBy 
            FROM outbound_jobs_log ojl INNER JOIN outbound_jobs oj ON oj.id = jobId  
            INNER JOIN support_user_events sue ON sue.id = eventId WHERE ojl.id =" . $jobId, true);
            $data['createdOn'] = date("d-m-Y H:i:s", strtotime($data['createdOn']));
            $data['caller'] = $db->getItemFromDB("SELECT CONCAT(FirstName,'',LastName) FROM finascop_usr_profile WHERE UserId = {$data['actionBy']}");
            if ($data['isManual'] == 1)
                $data['createdBy'] = $db->getItemFromDB("SELECT CONCAT(FirstName,'',LastName) FROM finascop_usr_profile WHERE UserId = {$data['createdBy']}");
            else
                $data['createdBy'] = "System";
            $data['success'] = true;
        } else {
            $data['success'] = false;
        }

        echo json_encode($data);
        break;
    case 'getUserType':
        $qry = "select id,name from support_applicable_users order by name";
        $data = $supportdb->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getEvents':
        $qry = "select id,eventName,`rank` as eventRank from support_user_events order by `rank`";
        $data = $supportdb->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'saveOutboundJobs':

        $reqData = $_POST['n'];

        $supportdb->query('begin');

        $reqData['createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $reqData['createdOn'] = date('Y-m-d H:i:s');
        $reqData['isManual'] = 1;
        $reqData = array_filter($reqData);
        $status = $supportdb->perform('outbound_jobs', $reqData);

        $status = $supportdb->query('commit');

        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listRecordings':
        $rec_limit = empty($_POST['limit']) ? 25 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

        $qry = _getCallRecReportGridData($_POST);

        $data = $supportdb->getMultipleData($qry['listQuery'], true);

        $count = $supportdb->getItemFromDB($qry['countQuery']);


        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'listRecordingsOld':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = 'id';
        $dir = 'DESC';
        $search = " WHERE 1=1  ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Active') {
                                $fiterItem = 1;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Inactive') {
                                $fiterItem = 0;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (status = 1 or status=0) ";
                            }
                        }
                        break;
                    case 'date':
                        if ($field['field'] == 'calledOn') {

                            switch ($field['data']['comparison']) {
                                case 'gt':
                                    $searchitem .= " and DATE_FORMAT(StartTime,'%Y-%m-%d') > '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'lt':
                                    $searchitem .= " and DATE_FORMAT(StartTime,'%Y-%m-%d') < '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'eq':
                                    $searchitem .= " and DATE_FORMAT(StartTime,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                default:
                                    $searchitem .= " and DATE_FORMAT(StartTime,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                            }
                        }
                        break;
                    default:
                        $checkComa = strstr($field['data']['value'], ',');
                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }
        $qry = "SELECT  AudioFile,HoldDuration,WrapUpDuration,ConfDuration,CustomerStatus,AgentStatus,
        DialStatus,Comments,Status,CallerID,StartTime,EndTime,TimeToAnswer,CallDuration,Duration,AgentName,
        Disposition,HangupBy,DATE_FORMAT(StartTime,'%d-%m-%Y') AS calledOn FROM call_communications {$search} {$searchitem}
        ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $countDataQuery = "SELECT count(*) from call_communications {$search} {$searchitem} ";
        $count = $supportdb->getItemFromDB($countDataQuery);
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }

        break;
    case 'listFollowupCalls':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = 'oj.id';
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1  AND DATE(followupDate) = CURDATE() ";
        $filter = $_POST['filter'];
        $today = date("Y-m-d");
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Active') {
                                $fiterItem = 1;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Inactive') {
                                $fiterItem = 0;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (status = 1 or status=0) ";
                            }
                        }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            if ($field['field'] == 'calleeTypeName') {
                                $searchitem .= " and (au.name LIKE '{$field['data']['value']}%') ";
                            } else {
                                $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                            }
                        }
                }
            }
        }

        $userAcceptCount = $supportdb->getItemFromDB("SELECT COUNT(*) FROM outbound_jobs WHERE assignedTo = {$_SESSION['admin']->Finascop_UserId} AND status = 2");
        if ($userAcceptCount > 0) {
            $searchitem .= " AND assignedTo = {$_SESSION['admin']->Finascop_UserId} AND status = 2";
        } else {
            $searchitem .= " AND assignedTo = 0 AND status = 1 ";
        }

        $countQuery = "SELECT COUNT(*) FROM outbound_jobs oj 
            INNER JOIN support_applicable_users au ON au.id = calleeType 
            INNER JOIN support_event_status es ON es.id = `status` 
            INNER JOIN support_user_events ue ON ue.id = eventId {$search} {$searchitem} ";
        $listQuery = "SELECT oj.id,eventId,if(eventId > 0,eventName,jobTitle) as eventName,calleeId,calleeName,calleeMobile,calleeType,au.name as calleeTypeName,eventRank,STATUS,
            es.name AS statusName,DATE_FORMAT(createdOn,'%d-%m-%Y') AS createdDate,
            DATE_FORMAT(createdOn,'%H:%i:%s') AS createdTime,assignedTo,assignedOn,callerName,DATE_FORMAT(followupDate,'%d-%m-%Y %H:%i %p') AS followupDate  FROM outbound_jobs oj 
            INNER JOIN support_applicable_users au ON au.id = calleeType 
            INNER JOIN support_event_status es ON es.id = `status` 
            LEFT JOIN support_user_events ue ON ue.id = eventId {$search} {$searchitem}
            ORDER BY CASE
            WHEN followupDate = '{$today}' THEN 1 
            WHEN followupDate IS NOT NULL THEN 3 
            WHEN followupDate IS NULL THEN 2      
            ELSE 4                                
        END,
        followupDate DESC,
        oj.id,eventRank ASC limit $rec_start,$rec_limit";

        $supportdb->printGridJson($countQuery, $listQuery);
        break;
    case 'listcallCommunications':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = 'ojl.id';
        $dir = 'DESC';
        $search = " WHERE 1=1  ";
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Active') {
                                $fiterItem = 1;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Inactive') {
                                $fiterItem = 0;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (status = 1 or status=0) ";
                            }
                        }

                        break;
                    case 'date':
                        if ($field['field'] == 'calledOn') {

                            switch ($field['data']['comparison']) {
                                case 'gt':
                                    $searchitem .= " and DATE_FORMAT(ojl.createdOn,'%Y-%m-%d') > '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'lt':
                                    $searchitem .= " and DATE_FORMAT(ojl.createdOn,'%Y-%m-%d') < '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'eq':
                                    $searchitem .= " and DATE_FORMAT(ojl.createdOn,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                default:
                                    $searchitem .= " and DATE_FORMAT(ojl.createdOn,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                            }
                        }
                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }



        $countQuery = "SELECT COUNT(*) FROM call_logs ojl INNER JOIN outbound_jobs oj ON oj.id = jobId  
        INNER JOIN support_user_events sue ON sue.id = eventId {$search} {$searchitem} ";
        $listQuery = "SELECT ojl.id,callRemarks as actionRemark,jobTitle,calleeName,
        calleeMobile,eventName,ojl.createdOn AS createdOn,DATE_FORMAT(ojl.createdOn,'%d-%m-%Y') AS calledOn,isManual,ojl.createdBy AS createdBy 
        FROM call_logs ojl INNER JOIN outbound_jobs oj ON oj.id = jobId  
        INNER JOIN support_user_events sue ON sue.id = eventId {$search} {$searchitem}
        ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
        $count = $supportdb->getItemFromDB($countQuery);
        $items = $supportdb->getMultipleData($listQuery, true);
        if (!empty($items)) {
            for ($i = 0; $i < count($items); $i++) {
                $items[$i]['createdOn'] = date("d-m-Y H:i:s", strtotime($items[$i]['createdOn']));
                $items[$i]['caller'] = $db->getItemFromDB("SELECT CONCAT(FirstName,'',LastName) FROM finascop_usr_profile WHERE UserId = {$items[$i]['createdBy']}");
                if ($items[$i]['isManual'] == 1)
                    $items[$i]['createdBy'] = $db->getItemFromDB("SELECT CONCAT(FirstName,'',LastName) FROM finascop_usr_profile WHERE UserId = {$items[$i]['createdBy']}");
                else
                    $items[$i]['createdBy'] = "System";
            }
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'callCommunicationDetailsView':
        $jobId = isset($_POST['jobId']) ? intval($_POST['jobId']) : 0;
        if ($jobId > 0) {
            $data = $supportdb->getFromDB("SELECT ojl.id,callRemarks as actionRemark,jobTitle,calleeName,
            calleeMobile,eventName,ojl.createdOn AS createdOn,isManual,ojl.createdBy AS createdBy  
                FROM call_logs ojl INNER JOIN outbound_jobs oj ON oj.id = jobId  
                INNER JOIN support_user_events sue ON sue.id = eventId WHERE ojl.id =" . $jobId, true);
            $data['createdOn'] = date("d-m-Y H:i:s", strtotime($data['createdOn']));
            $data['caller'] = $db->getItemFromDB("SELECT CONCAT(FirstName,'',LastName) FROM finascop_usr_profile WHERE UserId = {$data['createdBy']}");
            if ($data['isManual'] == 1)
                $data['createdBy'] = $db->getItemFromDB("SELECT CONCAT(FirstName,'',LastName) FROM finascop_usr_profile WHERE UserId = {$data['createdBy']}");
            else
                $data['createdBy'] = "System";
            $data['success'] = true;
        } else {
            $data['success'] = false;
        }

        echo json_encode($data);
        break;
    case 'listAlljobs':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = 'oj.id';
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1  AND `status` = 3 ";
        $filter = $_POST['filter'];
        $today = date("Y-m-d");
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Active') {
                                $fiterItem = 1;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Inactive') {
                                $fiterItem = 0;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (status = 1 or status=0) ";
                            }
                        }

                        break;
                    case 'date':
                        if ($field['field'] == 'createdDate') {

                            switch ($field['data']['comparison']) {
                                case 'gt':
                                    $searchitem .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') > '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'lt':
                                    $searchitem .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') < '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'eq':
                                    $searchitem .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                default:
                                    $searchitem .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                            }
                        } elseif ($field['field'] == 'followupOn') {

                            switch ($field['data']['comparison']) {
                                case 'gt':
                                    $searchitem .= " and DATE_FORMAT(followupDate,'%Y-%m-%d') > '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'lt':
                                    $searchitem .= " and DATE_FORMAT(followupDate,'%Y-%m-%d') < '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'eq':
                                    $searchitem .= " and DATE_FORMAT(followupDate,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                default:
                                    $searchitem .= " and DATE_FORMAT(followupDate,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                            }
                        }
                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            if ($field['field'] == 'calleeTypeName') {
                                $searchitem .= " and (au.name LIKE '{$field['data']['value']}%') ";
                            } else if ($field['field'] == 'statusName') {
                                $searchitem .= " and (es.name LIKE '{$field['data']['value']}%') ";
                            } else {
                                $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                            }
                        }
                }
            }
        }


        $countQuery = "SELECT COUNT(*) FROM outbound_jobs oj 
            INNER JOIN support_applicable_users au ON au.id = calleeType 
            INNER JOIN support_event_status es ON es.id = `status` 
            INNER JOIN support_user_events ue ON ue.id = eventId {$search} {$searchitem} ";
        $listQuery = "SELECT oj.id,eventId,if(eventId > 0,eventName,jobTitle) as eventName,calleeId,calleeName,calleeMobile,calleeType,au.name as calleeTypeName,eventRank,STATUS,completedOn,
            es.name AS statusName,DATE_FORMAT(createdOn,'%d-%m-%Y') AS createdDate,
            DATE_FORMAT(createdOn,'%H:%i:%s') AS createdTime,assignedTo,assignedOn,callerName,DATE_FORMAT(followupDate,'%d-%m-%Y %H:%i %p') AS followupDate,DATE_FORMAT(followupDate,'%d-%m-%Y %H:%i %p') AS followupOn  FROM outbound_jobs oj 
            INNER JOIN support_applicable_users au ON au.id = calleeType 
            INNER JOIN support_event_status es ON es.id = `status` 
            LEFT JOIN support_user_events ue ON ue.id = eventId {$search} {$searchitem}
            ORDER BY id ASC limit $rec_start,$rec_limit";

        $supportdb->printGridJson($countQuery, $listQuery);
        break;
    case 'getUserName':
        $qry = $db->getMulipleData("SELECT up.UserId as UserId,CONCAT(FirstName,' ',LastName) AS UserName FROM finascop_usr_profile up
        INNER JOIN finascop_usr_master um ON um.UserId = up.UserId INNER JOIN sys_role sr ON sr.RoleId = um.RoleId  where RoleName = 'Support'", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'callRecordReportsexportexcel':
        $lastParameters = json_decode(stripslashes(($_POST["filterData"])), true);

        for ($i = 0; $i <= $i; $i++) {
            if (array_key_exists('filter[' . $i . '][field]', $lastParameters)) {
                $filterParams[$i]['field'] = $lastParameters['filter[' . $i . '][field]'];
                unset($lastParameters['filter[' . $i . '][field]']);
                $filterParams[$i]['data']['type'] = $lastParameters['filter[' . $i . '][data][type]'];
                unset($lastParameters['filter[' . $i . '][data][type]']);
                $filterParams[$i]['data']['value'] = $lastParameters['filter[' . $i . '][data][value]'];
                unset($lastParameters['filter[' . $i . '][data][value]']);
                $filterParams[$i]['data']['comparison'] = $lastParameters['filter[' . $i . '][data][comparison]'];
                unset($lastParameters['filter[' . $i . '][data][comparison]']);
            } else {
                break;
            }
        }
        $_POST['filter'] = $filterParams;

        //$filterdata = json_decode($_POST['filterData'],true);
        //array_push($_POST,$filterdata) ;
        foreach ($lastParameters as $keys => $values) {
            $_POST[$keys] = $values;
        }
        $_POST['start'] = 0;
        $_POST['limit'] = 100000;
        $qry = _getCallRecReportGridData($_POST);
        //print_r($qry['listQuery']);
        $_SESSION['Export']['Query'] = $qry['listQuery'];
        $_SESSION['Export']['Settings']['title'] = "callrecordings_";
        _exportExcelReport($_POST);
        break;
    case 'callLogReportsexportexcel':
        $lastParameters = json_decode(stripslashes(($_POST["filterData"])), true);

        for ($i = 0; $i <= $i; $i++) {
            if (array_key_exists('filter[' . $i . '][field]', $lastParameters)) {
                $filterParams[$i]['field'] = $lastParameters['filter[' . $i . '][field]'];
                unset($lastParameters['filter[' . $i . '][field]']);
                $filterParams[$i]['data']['type'] = $lastParameters['filter[' . $i . '][data][type]'];
                unset($lastParameters['filter[' . $i . '][data][type]']);
                $filterParams[$i]['data']['value'] = $lastParameters['filter[' . $i . '][data][value]'];
                unset($lastParameters['filter[' . $i . '][data][value]']);
                $filterParams[$i]['data']['comparison'] = $lastParameters['filter[' . $i . '][data][comparison]'];
                unset($lastParameters['filter[' . $i . '][data][comparison]']);
            } else {
                break;
            }
        }
        $_POST['filter'] = $filterParams;

        //$filterdata = json_decode($_POST['filterData'],true);
        //array_push($_POST,$filterdata) ;
        foreach ($lastParameters as $keys => $values) {
            $_POST[$keys] = $values;
        }
        $_POST['start'] = 0;
        $_POST['limit'] = 100000;
        $qry = JobLogReportGridData($_POST);
        //print_r($qry['listQuery']);
        $_SESSION['Export']['Query'] = $qry['listQuery'];
        $_SESSION['Export']['Settings']['title'] = "joblogs_";
        _exportExcelReport($_POST);
        break;
    case 'listClosedCalls':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = 'oj.id';
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1  ";
        $filter = $_POST['filter'];
        $today = date("Y-m-d");
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Active') {
                                $fiterItem = 1;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Inactive') {
                                $fiterItem = 0;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (status = 1 or status=0) ";
                            }
                        }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            if ($field['field'] == 'calleeTypeName') {
                                $searchitem .= " and (au.name LIKE '{$field['data']['value']}%') ";
                            } else {
                                $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                            }
                        }
                }
            }
        }

        $searchitem .= " AND  status = 3 ";

        $countQuery = "SELECT COUNT(*) FROM outbound_jobs oj 
            INNER JOIN support_applicable_users au ON au.id = calleeType 
            INNER JOIN support_event_status es ON es.id = `status` 
            INNER JOIN support_user_events ue ON ue.id = eventId {$search} {$searchitem} ";
        $listQuery = "SELECT oj.id,eventId,if(eventId > 0,eventName,jobTitle) as eventName,calleeId,calleeName,calleeMobile,calleeType,au.name as calleeTypeName,eventRank,STATUS,
            es.name AS statusName,DATE_FORMAT(createdOn,'%d-%m-%Y') AS createdDate,
            DATE_FORMAT(createdOn,'%H:%i:%s') AS createdTime,assignedTo,assignedOn,callerName,DATE_FORMAT(completedOn,'%d-%m-%Y %H:%i %p') AS completedOn  FROM outbound_jobs oj 
            INNER JOIN support_applicable_users au ON au.id = calleeType 
            INNER JOIN support_event_status es ON es.id = `status` 
            LEFT JOIN support_user_events ue ON ue.id = eventId {$search} {$searchitem}
            ORDER BY 
        oj.id,eventRank ASC limit $rec_start,$rec_limit";

        $supportdb->printGridJson($countQuery, $listQuery);
        break;
    case 'listObJobLogs':
        $jobId = $_POST['jobId'];
        $searchitem = "";
        $search = " WHERE 1=1  ";
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Active') {
                                $fiterItem = 1;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Inactive') {
                                $fiterItem = 0;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (status = 1 or status=0) ";
                            }
                        }

                        break;
                    case 'date':
                        if ($field['field'] == 'createdOn') {

                            switch ($field['data']['comparison']) {
                                case 'gt':
                                    $searchitem .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') > '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'lt':
                                    $searchitem .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') < '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                case 'eq':
                                    $searchitem .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                                default:
                                    $searchitem .= " and DATE_FORMAT(createdOn,'%Y-%m-%d') = '" . date('Y-m-d', strtotime($field['data']['value'])) . "'";
                                    break;
                            }
                        }
                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }


        $query = "SELECT `callAction`, `callRemarks`, `createdOn`, `createdBy`,entryFrom,
CASE WHEN entryFrom = 1 THEN 'Communication' WHEN entryFrom = 2 THEN 'Call Log' WHEN entryFrom = 3 THEN 'Call Recording' END AS entryFromName,
(SELECT `name` FROM call_actions WHERE id = callAction) AS callActionName
FROM `call_logs` WHERE jobId = {$jobId} 
UNION ALL
SELECT  NULL AS `callAction`, `actionRemark` AS `callRemarks`, `actionOn` AS `createdOn`, `actionBy` AS `createdBy`,4 AS entryFrom,'Job Log' AS entryFromName,
'Start/Exit' AS callActionName
FROM `outbound_jobs_log` WHERE jobId = {$jobId}
ORDER BY `createdOn` ASC";
        $countQuery = "SELECT COUNT(*) FROM ({$query}) as listCount";
        $listQuery = "SELECT  * FROM ({$query}) logList";

        $data = $supportdb->getMultipleData($listQuery, true);
        $count = $supportdb->getItemFromDB($countQuery);

        if (!empty($data)) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['createdDate'] = date("d-m-Y", strtotime($data[$i]['createdOn']));
                $data[$i]['createdByName'] = $db->getItemFromDB("SELECT CONCAT(FirstName,'',LastName) FROM finascop_usr_profile WHERE UserId = {$data[$i]['createdBy']}");
            }
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listMissedCalls':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = 'oj.id';
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1  ";
        $filter = $_POST['filter'];
        $today = date("Y-m-d");
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Active') {
                                $fiterItem = 1;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Inactive') {
                                $fiterItem = 0;
                                $searchitem .= " and ({$field['field']} = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (status = 1 or status=0) ";
                            }
                        }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            if ($field['field'] == 'calleeTypeName') {
                                $searchitem .= " and (au.name LIKE '{$field['data']['value']}%') ";
                            } else {
                                $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                            }
                        }
                }
            }
        }

        $searchitem .= " AND  eventId IN (16,17) ";

        $countQuery = "SELECT COUNT(*) FROM outbound_jobs oj 
            INNER JOIN support_applicable_users au ON au.id = calleeType 
            INNER JOIN support_event_status es ON es.id = `status` 
            INNER JOIN support_user_events ue ON ue.id = eventId {$search} {$searchitem} ";
        $listQuery = "SELECT oj.id,eventId,if(eventId > 0,eventName,jobTitle) as eventName,calleeId,calleeName,calleeMobile,calleeType,au.name as calleeTypeName,eventRank,STATUS,
            es.name AS statusName,DATE_FORMAT(createdOn,'%d-%m-%Y') AS createdDate,
            DATE_FORMAT(createdOn,'%H:%i:%s') AS createdTime,assignedTo,assignedOn,callerName,DATE_FORMAT(completedOn,'%d-%m-%Y %H:%i %p') AS completedOn  FROM outbound_jobs oj 
            INNER JOIN support_applicable_users au ON au.id = calleeType 
            INNER JOIN support_event_status es ON es.id = `status` 
            LEFT JOIN support_user_events ue ON ue.id = eventId {$search} {$searchitem}
            ORDER BY 
        oj.id,eventRank ASC limit $rec_start,$rec_limit";

        $supportdb->printGridJson($countQuery, $listQuery);
        break;
}
