<?php

/**
 * @author Kavitha K<kavitha@saturn.in>
 * 
 */
require_once(EXTERNAL_LIBRARY_PATH);
require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/finascop_config/config.php');
switch ($op) {

    /** utilData- Contains data for Utilities/Find in Files * */
    case 'utilData':
        $fileType = array(IN_REPOS, OUT_REPOS, MAS_REPOS);
        $fType = $_POST['fileType'];

        $date = $_POST['searchdate'];
        $file = $fileType[$fType] . $date;
        $filePattern = $_POST['filePattern'];
        $searchPattern = $_POST['searchPattern'];
        $searchFiles = array();
        $curTimeStamp = strtotime("now");
        $tmpfile = DOC_ROOT . '/tmp/' . $curTimeStamp . '.tmp';
        $tmplog = DOC_ROOT . '/tmp/' . $curTimeStamp . '.log';
        $cmd = '/usr/bin/find  "' . $file . '" -type f -name ' . $filePattern . ' -print0 | xargs -0 zgrep -n "' . $searchPattern . '"';
        exec($cmd, $searchFiles);

        for ($i = 0; $i < count($searchFiles); $i++) {
            list($fileName, $lineNo, $line) = explode(":", $searchFiles[$i], 3);
            $data[$i]['Filename'] = str_replace(DOC_ROOT, '', $fileName);
            $data[$i]['Line'] = $lineNo;
            $data[$i]['Content'] = $line;
        }
        $branch = $_POST['branch'];
        echo '{"data":' . json_encode($data) . '}';
        break;

    case 'saveFindfiles':
        echo '{"success":"true"}';
        break;
//getting data for locationstore    
    case 'getLocation':
        echo'{"data":[{"name":"In","value":"0"},{"name":"Out","value":"1"},{"name":"Master","value":"2"}]}';
        break;
//getting data for branchstore    
    case 'getBranch':
        $qry = "select br_Name,br_ID from " . FINASCOP_DB . "finascop_branch";
        $data = $db->getMulipleData($qry, true);
        echo '{"data":' . json_encode($data) . '}';

        break;
    /**
     * checkData- check validation of file pattern values
     */
    case 'checkData':
        $valid = $_POST['value'];
        $arr = array('null');
        if (!in_array($valid, $arr)) {
            echo "{success:true, valid:true}";
        } else {
            $reason = "Invalid File Pattern";
            echo'{"success":true,"valid":false,"reason":"' . $reason . '"}';
        }
        break;

    /** getBranch- Contains the data for the State combobox in Create Branch 
     * */
    case 'getAllBranch':
        $qry = "select br_Name,br_ID from " . FINASCOP_DB . "finascop_branch";
        $rs = $db->getMulipleData($qry, true);
        if (!empty($rs)) {
            if (count($rs) > 0) {
                echo "[";
                echo '["0","All"],';
                foreach ($rs as $k => $v) {
                    echo "[";
                    echo "'" . $v['br_ID'] . "','" . addslashes($v['br_Name']) . "'";
                    echo "]";
                    $i++;
                    if ($i < count($rs))
                        echo ",";
                    flush();
                }
                echo "]";
            }
        }
        break;

    /** restoreData- Contains data for Utilities/Live Restore 
     * */
    case 'restoreData':
        echo'{"count":"5","data":[{"code":"1","name":"Branch","city":"Pattathanam",
            "district":"Kollam","authorization":"12345","livrestore":"12-12-2011"}, 
            {"code":"2","name":"Logs","city":"Pala","district":"Kottayam",
            "authorization":"30854","livrestore":"02-12-2009"}, {"code":"3",
            "name":"Application","city":"KP Nagar","district":"Trivandrum",
            "authorization":"75412","livrestore":"24-02-2010"}, {"code":"4",
            "name":"Patch","city":"Aluva","district":"Ernakulam",
            "authorization":"87430","livrestore":"10-09-2000"}, {"code":"5",
            "name":"Preset","city":"Kuttipuram","district":"Malapuram",
            "authorization":"20185","livrestore":"25-05-2006"}]} ';
        break;
    /* For geting Server Cashe      */
    case 'getsvrcache':
        $qry = "SELECT FROM_UNIXTIME(JSCacheTime) FROM " . FINASCOP_DB . "finascop_usr_master  WHERE UserId = " . $_SESSION['admin']->Finascop_UserId;

        $time = $db->getItemFromDB($qry);

        echo '{"success":true,"data":{"time":"' . $time . '"}}';
        break;

    /* For Clear Server Cache      */

    case 'resetsvrcache':
        $qry1 = "UPDATE " . FINASCOP_DB . "finascop_usr_master SET `JSCacheTime`=''";
        $db->query($qry1);
        $msg = "Cache is cleared";
        echo '{"success":true,"msg":"' . $msg . '"}';
        //echo "{success : true}";
        break;
    case 'startRestore':
        echo '{"success":"true"}';

        break;
    case 'getauthKey':
        $from_time = strtotime("now");
        $to_time = strtotime("1960-01-01 05:30:00");
        $key = strtoupper(dechex(abs($to_time - $from_time) - 3600));
        echo '{"success":true,"data":{"key":"' . $key . '"}}';
        break;

    /* for Initiate live restore  */

    case 'initiate':
        $branchid = $_POST['branchid'];
// $frstuploaddt = date("Y-m-d G:i:s");;
        //$comments = $_POST['lrscomments'];
        //$billable=intval($_POST['billable']);
        $curDate = date("Y-m-d G:i:s");
        $qry = "SELECT entry FROM process_queue  WHERE  bid='" . intval($branchid) . "' ORDER BY qid LIMIT 1";
        $entry = $db->getItemFromDB($qry);
        if ($entry != '') {
            //echo '{"success":true,"msg":"Live restore is available for first time installation branches only. " }';
            //exit;
            $billable = '1';
            $comments = 'Initial Upload - ' . $entry;
        } else {
            $billable = '0';
            $comments = 'First Run';
        }

        $data = array(
            'branchid' => $branchid,
            'addon' => $curDate,
            'addedby' => $_SESSION['admin']->Finascop_UserId,
            'billable' => $billable,
            'comments' => $comments,
        );
        $db->perform('liverestore_inithistory', $data);
        $execatmin = rand(210, 240);
        $date = date('Y-m-d H:i:s');
        $currentDate = strtotime($date);
        $futureDate = $currentDate + (60 * $execatmin);
        $formatDate = date("Y-m-d H:i:s", $futureDate);
        $to = 'bills.carego@saturn.in';
        //$to = 'vishnu@saturn.in';
        $subject = "Live Restore - " . $_SERVER['HTTP_HOST'] . " - " . $_POST['brname'] . " - " . ($billable == 1 ? "Billable" : "First Run");
        $content = "Live Restore - " . $_SERVER['HTTP_HOST'] . " - " . $_POST['brname'] . " - " . ($billable == 1 ? "Billable" : "First Run")
                . " <br> " . "Comments: " . $comments . " <br > Initiated By <b> <i>" . $_SESSION['admin']->UserName . " </i> </b>. Restore will be ready by <b> " . $formatDate . " </b> ";
        $status = sendMail($to, $subject, $content, true);
        save_email_log($to, $mail_event = 0, $content, $subject, $status);
        $execatmin = $execatmin - 10;
        $command = sprintf('echo "cp ' . LIVERESTORE_REPOS . 'init_' . $branchid . '.dmp ' . DBDUMP_REPOS . '" | at now + %d min >> /tmp/liverestorerqst.txt 2>&1', $execatmin);
        exec($command);
        echo '{"success":true,"msg":"Live restore Initiated. Restore will be ready by - ' . $formatDate . ' " }';

        break;

    /* for getting entry */
    case 'getentry':
        if (!empty($_POST['bid'])) {
            $qry = "SELECT entry FROM process_queue  WHERE  bid='" . intval($_POST['bid']) . "' ORDER BY qid LIMIT 1";

            $entry = $db->getItemFromDB($qry);

            echo '{"success":true,"data":{"entry":"' . $entry . '"}}';
        }
        break;

    case 'getBranches':
        /* For getting branches  */

        if ($_POST['query'] != "")
            $filter_query = "And br_Name like '" . $_POST['query'] . "%'";
        //Filter of branches for Zonal user
        $UserTypeID = intval($_SESSION['admin']->finascop_typId);
        if ($UserTypeID == 2)
            $ZoneID = intval($_SESSION['admin']->typdetsid);
        else
            $ZoneID = 0;
        $db->query('set @ZONEUSERID = ' . $ZoneID);
        $query = "Select br_ID, br_Name from " . FINASCOP_DB . "finascop_branch 
                    where br_id >1 and br_zone =(if(@ZONEUSERID=0,br_zone,@ZONEUSERID))   and  br_Name <>  '' $filter_query Order By br_Name ASC";
        $rs = $db->query($query);
        $i = 0;
        $num_rows = $db->num_rows($rs);
        echo "[";
        while ($row = $db->fetch_array($rs)) {

            echo "[";
            echo '"' . $row['br_ID'] . '","' . addslashes($row['br_Name']) . '"';
            echo "]";
            $i++;
            if ($i < $num_rows)
                echo ",";
            flush();
        }
        echo "]";
        break;

    case 'getHistory':
        /**
         * For Listing  History
         */
        $prefix = '';
        $branchid = $_POST['bid'];
        if (!empty($_POST['bid'])) {
            $qry = "select count(lrihid) from $prefix.liverestore_inithistory ";
            $totalCount = $db->getItemFromDB($qry);
            $qry1 = "select lrihid,date_format(addon,'%Y-%m-%d %H:%i:%s') as addon,addedby,UserName as added_by
          from $prefix.liverestore_inithistory inner join 
         " . FINASCOP_DB . "finascop_usr_master on liverestore_inithistory.addedby = " . FINASCOP_DB . "finascop_usr_master.UserId 
       	  where branchid= '" . $branchid . "'order by addon DESC limit 10";

            $res = $db->getMultipleData($qry1, true);
        } else {
            $qry = "select count(lrihid) from $prefix.liverestore_inithistory ";
            $totalCount = $db->getItemFromDB($qry);
            $qry1 = "select lrihid,date_format(addon,'%Y-%m-%d %H:%i:%s') as addon,addedby,UserName as added_by
          from $prefix.liverestore_inithistory inner join " . FINASCOP_DB . "finascop_usr_master on liverestore_inithistory.addedby = " . FINASCOP_DB . "finascop_usr_master.UserId 
       	  order by addon DESC limit 10";

            $res = $db->getMultipleData($qry1, true);
        }
        if (!empty($res))
            echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($res) . '}';
        else {
            echo '{"success":true,"data":[]}';
        }
        break;

    case 'getMaxRSBId':
        $prefix = '';
        $qry = "select max(report_id+1) as new_report_id from $prefix.sys_reports ";
        $new_report_id = $db->getItemFromDB($qry);
        if ($new_report_id > 0)
            echo '{"success":true,"data":' . $new_report_id . '}';
        else
            echo '{"success":false,"data":""}';
        break;
    case 'loadRPTList':
        $prefix = '';
        $qry = "SELECT rptname, birtrptname,headers,proc_name,report_id FROM $prefix.sys_reports ";
        $rpts = $db->getMulipleData($qry, true);
        if (!empty($rpts)) {
            $rpts = json_encode($rpts);
            echo '{"data":' . $rpts . '}';
        } else {
            echo '{"data":[]}';
        }
        break;
    case 'getEditDetails':
        $prefix = '';
        $report_id = $_POST['report_id'];
        $qry = "SELECT * FROM $prefix.sys_reports WHERE report_id = " . $report_id;
        $reportresult = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($reportresult) . '}';
        break;
    case 'getexcludeFieldNames':

        $excludeFieldName = json_decode($_REQUEST['excludeFieldNames']);
        //print_r($excludeFieldName);
        //$excludeFieldNames = explode(',',$excludeFieldName);
        $i = 0;
        $FieldName = array();
        foreach ($excludeFieldName as $FieldNames) {
            $FieldName[$i]['columnField'] = $FieldNames;
            $i++;
        }
        echo '{"success":true,"data":' . json_encode($FieldName) . '}';
        break;
    case 'executeSP':
        $SPName = $_REQUEST['SPName'];
        $pdsn = parse_url(DSN);
        $mysql_db = preg_replace("@^\/@", '', $pdsn['path']);

        $conn = new mysqli($pdsn['host'], $pdsn['user'], $pdsn['pass'], $mysql_db); //, ini_get("mysqli.default_port")) or die("Could not connect " . mysql_error());

        $selectDB = /* mysql_select_db removed - handled by mysqli constructor */;


        $columns = $_POST['columns'];
        $columnsArray = json_decode($columns);
        $call = '\t';
        $columnResult = array();
        foreach ($columnsArray as $key => $value) {
            $columnResult[] = $value->columnField;
        }
        $i = 0;
        foreach ($columnResult as $column) {

            if ($i <> 0)
                $call.= '\t';

            $columnArray = explode('_', $column);

            $type = str_replace(range(0, 9), '', $columnArray[0]);

            switch ($type) {
                case 'dtPick':
                    $append = date('Y-m-d');
                    break;
                default:
                    $append = '0';
                    break;
            }

            $call.= $append;
            $i++;
        }
        $call.= '\t';
        $outputColumns = '';

        $qr = mysqli_query($conn, "CALL " . $SPName . "('" . $call . "','')", $conn);
        while ($r = mysqli_fetch_field($qr)) {
            $outputColumns.= $r->name . '|';
        }

        $outputColumns = substr($outputColumns, 0, -1);
        echo '{"success":true,"data":' . json_encode($outputColumns) . '}';
        break;
    case 'saveRPTSettings':
        $prefix = '';
        $report_id = $_POST['hdnIdnewRpt'];
        $rptname = $_POST['id_RPTName'];
        $IsEdit = $_POST['IsEdit'];
        $json_options = '{"Panel":{"title":"' . $rptname . '"}}';
        $formfile = 'report' . $report_id . '.js';
        $proc_name = $_POST['id_SPName'];
        $colname = $_POST['id_txt_ColumnNames'];
        $birtrptname = $_POST['id_BIRTName'];
        $NeedNoDecimals = 0;
        $rptQry = $_POST['hdnIdFieldTotal'];
        $headers = $rptname;
        $IsGeneral = 0;
        $field_orders = '';
        $field_order = json_decode($_POST['hdnIdFieldOrderStore']);
        for ($i = 0; $i < count($field_order); $i++)
            $field_orders.=$field_order[$i]->columnField . '|';

        $field_orders = substr($field_orders, 0, -1);

        /*
         * Check whether a report is exist with this report ID

         */
        if ($IsEdit == 1) {
            $count = $db->getItemFromDB("SELECT COUNT(*) FROM $prefix.sys_reports WHERE rptname ='" . $rptname . "' and report_id !=" . $report_id);
            $birt_count = $db->getItemFromDB("SELECT COUNT(*) FROM $prefix.sys_reports WHERE birtrptname ='" . $birtrptname . "' and report_id !=" . $report_id);
        } else {
            $count = $db->getItemFromDB("SELECT COUNT(*) FROM $prefix.sys_reports WHERE rptname ='" . $rptname . "' or report_id =" . $report_id);
            $birt_count = $db->getItemFromDB("SELECT COUNT(*) FROM $prefix.sys_reports WHERE birtrptname ='" . $birtrptname . "'");
        }
        if ($count > 0) {
            echo "{success: false, mesg: 'Duplicate in Report.'}";
        } else if ($birt_count > 0) {
            echo "{success: false, mesg: 'Duplicate in BIRT Report Name.'}";
        } else {

            $saveArray = array(
                "report_id" => $report_id,
                "json_options" => $json_options,
                "formfile" => $formfile,
                "proc_name" => $proc_name,
                "colname" => $colname,
                "field_order" => $field_orders,
                "colalign" => '',
                "birtrptname" => $birtrptname,
                "NeedNoDecimals" => $NeedNoDecimals,
                "rptQry" => $rptQry,
                "rptname" => $rptname,
                "headers" => $headers,
                "IsGeneral" => $IsGeneral
            );
            $con = 'report_id =' . $report_id;
            if ($IsEdit == 0)
                $db->perform('sys_reports', $saveArray);
            else
                $db->perform('sys_reports', $saveArray, "update", $con);

            echo "{success: true, mesg: 'Saved Successfully.'}";
        }

        break;
}