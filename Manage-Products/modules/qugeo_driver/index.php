<?php

/**
  /* @author Kavitha K<kavitha@saturn.in>
 * @crated on 14-02-2012
 */
require_once(INCLUDE_PATH . '/lib.php');
require_once(INCLUDE_PATH . '/config.php');
require_once(INCLUDE_PATH . "/finascop_common_functions.php");

switch ($op) {
    /**
     * To save the Driver details
     */
    case 'saveDriver':
        $msg = '';
        $dName = trim($_POST['d_Name']);
        $lName = trim($_POST['l_Name']);
        /**
         * field Validation.validating required fields.
         */
        $reqFields = array("Driver" => $dName, //first Name
            "DriverLastName" => $lName,
            "Phone" => $_POST['d_Ph1'],
            "Branch" => $_POST['br_id']);


        $msg = reqFieldValidation($reqFields);
        $comboFields = array("Branch" => array("cond" => "br_id=" . intval($_POST['br_id']), "table" => "finascop_branch", $_POST['br_id']));
        $cMsg = checkValidationCombo($comboFields);
        if (!empty($cMsg)) {
            echo '{"success":false,"msg":"' . $cMsg . '"}';
            exit;
        }
        if (!empty($msg)) {
            echo '{"success":false,"msg":"' . $msg . '"}';
            exit;
        }
        //validating Name,Special characters can not be allowed more than once at a time.allowed dot only       
        $msg = '';
        $dName = trim($_POST['d_Name']);
        if (strpos($dName, '..') !== false || strpos($dName, '  ') !== false) {
            echo '{"success":false,"msg":"Special characters can not be used more than once at a time "}';
            exit;
        }
        $bID = intval($_POST['br_id']);

        if (empty($bID)) {
            $msg = "Please select branch before save";
            echo '{"success":true,"msg":"' . $msg . ' "}';
            exit;
        } else {
            //for get microtime which returns a number
            $ts = microtime(true);
            $fileName = $ts . '.0.spl';

            // create an instance of diskwriter
            //$dw = new diskWriter($fileName, 0, true);


            $qry = "SELECT coalesce(max( d_id ),0)+1 FROM qugeo_driver ";
            $dID = $db->getItemFromDB($qry);
            if (!empty($_POST['d_dob'])) {
                $dobDate = explode('/', $_POST['d_dob']);
                $dbDate = date("Y-m-d", mktime(0, 0, 0, $dobDate[1], $dobDate[0], $dobDate[2]));
                $ddate = strtotime($dbDate);
                $date = time();
                $diff = intval(($date - $ddate) / (60 * 60 * 24));
                $diff = intval(($diff / 30) / 12);
                //validating date of birth
                if ($diff < 18) {
                    $msg = " Please check Date of Birth";
                    echo '{"success":false,"msg":"' . $msg . '"}';
                    exit;
                }
            } else
                $dbDate = "0000-00-00";
            //checking Licence Expiry date
            if (!empty($_POST['d_licenceexpairy'])) {
                $LExpDate = explode('/', $_POST['d_licenceexpairy']);
                $today = date("Y-m-d");
                $LExpDate = date("Y-m-d", mktime(0, 0, 0, $LExpDate[1], $LExpDate[0], $LExpDate[2]));
                if ($LExpDate <= $today) {
                    $today = date("d-m-Y");
                    $msg = "Licence Expiry should be greater than  " . $today;
                    echo '{"success":false,"msg":"' . $msg . '"}';
                    exit;
                }
            } else
            $appDate = "0000-00-00";
            
            $qry = "select br_Lat,br_Lng	from finascop_branch where br_id=" . $_POST['br_id'];
            $geocords = $db->getFromDB($qry, true);
            
            $data = array(
                "d_Name" => $dName,
                "l_Name"  => $lName,
                "d_Add1" => $_POST['d_Add1'],
                "d_Add2" => $_POST['d_Add2'],
                "d_Add3" => $_POST['d_Add3'],
                "d_Ph1" => $_POST['d_Ph1'],
                'employee_type'=>$_POST['employee_type'],
                'emp_id'=>$_POST['emp_id'],
                'emp_ni_number'=>$_POST['emp_ni_number'],
                'emp_email_id' =>$_POST['emp_email_id'],  
                "d_dob" => $dbDate,
                "d_licence" => $_POST['d_licence'],
                "d_licenceexpairy" => $LExpDate,
                "br_id" => $_POST['br_id'],
                "d_HomeLati" => $geocords['br_Lat'],
                "d_HomeLong" => $geocords['br_Lng'],
                "d_DeliveryRange" => $_POST['d_DeliveryRange'],
                "d_isallowManualSchedule" => empty($_POST['d_isallowManualSchedule']) ? 0 : 1,
                "d_isallowAutoSchedule" => empty($_POST['d_isallowAutoSchedule']) ? 0 : 1);
            $db->query('begin');
            //insert
            if (empty($_POST['d_ID'])) {
                $data['d_ID'] = $dID;
                $db->perform('qugeo_driver', $data);
                $msg = "Driver Created Successfully";
                //actionDW('qugeo_driver', $data, $dw, 'insert');
                //do update
            } else {
                $con = 'd_id=' . intval($_POST['d_ID']);
                $db->perform('qugeo_driver', $data, 'update', $con);
                $msg = "Driver Updated Successfully";
                //actionDW('qugeo_driver', $data, $dw, 'update', $con);
            }
            // to pack the file to a process file
            //$inFilePath = $dw->mkS3GzipFile(false, true, AWSDATAUPLOADBUCKET, AWSDATAUPLOADCONTENTTYPE);
            /* if ($inFilePath == false) {

              echo '{"success":false,"error":"Unexpected Runtime error!"}';
              exit;
              } */


// to register the file into process queue
            //$proc = array('S3Bucket' => AWSDATAUPLOADBUCKET, 'bid' => 0, 'filename' => $inFilePath, 'entry' => date("Y-m-d G:i:s", $ts));
// save the entry into process queue
            // $db->perform('process_queue', $proc);
            $db->query('commit');
            // }
            echo '{"success":true,"msg":"' . $msg . '"}';
        }

        break;

    case 'getBranch':
        /* get branch for combo
         */

        $query = $_POST['query'];
        if ($query != '')
            $con = " AND br_Name like '" . $query . "%'";
        else
            $con = '';


        $qry = "select br_ID,br_Name from finascop_branch WHERE br_status = 'Active' 
                " . $con . " order by br_Name ";
        $branch = $db->getMulipleData($qry, true);
        if (!empty($branch)) {
            $branch = json_encode($branch);
            echo '{"data":' . $branch . '}';
        } else {
            echo '{"data":[]}';
        }

        break;
//To fetch the details of a selected Driver       
    case 'getDriver':
        
        $id = intval($_POST['d_ID']);
        $qry = "select d_ID,d_Name,l_Name,d_Ph1,d_Add1,d_Add2,d_Add3,employee_type,emp_id,emp_ni_number,emp_email_id,d_DeliveryRange,
              d_licenceexpairy,d_licence,d_dob,br_id,d_isallowAutoSchedule,d_isallowManualSchedule 
        from qugeo_driver where d_id=" . $id;
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'list':
        /**
         * for listing the Driver Details in  grid
         */
        //Retrieve Parameters
        $recLimit = intval($_POST['limit']);
        $recStart = intval($_POST['start']);
        $recSort = ($_POST['sort'] == "" ? 'd_Name' : $_POST['sort']);
        $recSortDir = ($_POST['dir'] == "" ? 'asc' : $_POST['dir']);
        /* $fields = array(

          'd_isallowAutoSchedule' => 'IF(d_isallowAutoSchedule = 1,"Yes","No")',
          'd_isallowManualSchedule' => 'IF(d_isallowManualSchedule = 1,"Yes","No")'
          ); */
//--
        $search = " WHERE 1=1 ";
        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['driver_name', 'driver_phone', 'driver_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* RAW (disabled): if (isset($_POST['filter']) && $_POST['filter'] != '') { */
            foreach ($_POST['filter'] as $key => $v) {
                switch ($v['data']['type']) {
                    case 'string':
                        if ($v['field'] == 'branch') {
                            $v['field'] = 'br_id';
                            $qry = "select br_ID from finascop_branch where br_Name "
                                    . " like'" . $v['data']['value'] . "%' and br_id=qugeo_driver.br_id";
                            $filterCon .= " and qugeo_driver.br_id in(" . $qry . ") ";
                            //$search .= " and ({$v['field']} = {$fiterCon}) ";
                        } else if ($v['field'] == 'address') {
                            $filterCon .= " and d_Add1 like '%" . $v['data']['value'] . "%'";
                            $search .= " and (d_Add1 LIKE '{$field['data']['value']}%') ";
                        } else {
                            $filterCon .= " and " . $v['field'] . " like '%" . $v['data']['value'] . "%'";
                            $search .= " and ({$v['field']} LIKE '{$field['data']['value']}%') ";
                        }
                        break;
                    case 'list':
                        if ($v['field'] == 'd_isallowAutoSchedule') {
                            if ($v['field'] == 'd_isallowAutoSchedule') {
                                $fiterCon = ($v['data']['value'] == 'Yes') ? 1 : 0;
                                $search .= " and ({$v['field']} = {$fiterCon}) ";
                            } else if ($field['data']['value'] == 'No') {
                                $fiterCon = 0;
                                $search .= " and ({$v['field']} = {$fiterCon}) ";
                            } else {
                                // $search .= " and ({$v['field']} LIKE '{$field['data']['value']}%') ";
                                $search .= " and (d_isallowAutoSchedule = 1 or d_isallowAutoSchedule=0) ";
                            }
                        }

                        if ($v['field'] == 'd_isallowManualSchedule') {
                            if ($v['field'] == 'd_isallowManualSchedule') {
                                $fiterCon = ($v['data']['value'] == 'Yes') ? 1 : 0;
                                $search .= " and ({$v['field']} = {$fiterCon}) ";
                            } else if ($field['data']['value'] == 'No') {
                                $fiterCon = 0;
                                $search .= " and ({$v['field']} = {$fiterCon}) ";
                            } else {
                                //    $search .= " and ({$v['field']} LIKE '{$field['data']['value']}%') ";
                                $search .= " and (d_isallowAutoSchedule = 1 or d_isallowAutoSchedule=0) ";
                            }
                        }

                        break;
                }
            }
        }

        $qry = "select count(d_id) from qugeo_driver {$search} " . $filterCon;
        $totalCount = $db->getItemFromDB($qry);
        $prefix = DB_PREFIX . '1';
        $db->query('set @cnt=0');
        $qry = <<<EOT
		select d_ID, d_Name,concat_ws(',',d_Add1,d_Add2,d_Add3) as address,
        d_Ph1,(select br_Name from finascop_branch where br_id=qugeo_driver.br_id) 
        as branch,IF((d_isallowAutoSchedule=1),'Yes','No')  as d_isallowAutoSchedule,  IF((d_isallowManualSchedule=1),'Yes','No')  as d_isallowManualSchedule from qugeo_driver {$search} $filterCon
	 order by $recSort $recSortDir
        limit $recStart,$recLimit
EOT;

        $data = $db->getMultipleData($qry, true);
        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;



    case 'load_activity':
        $driver = $_POST['activity_driver'];
        $activity_date_from = $_POST['activity_date_from'];
        $activity_date_to = $_POST['activity_date_to'];
        $aryRange = array();

        $iDateFrom = mktime(1, 0, 0, substr($activity_date_from, 5, 2), substr($activity_date_from, 8, 2), substr($activity_date_from, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($activity_date_to, 5, 2), substr($activity_date_to, 8, 2), substr($activity_date_to, 0, 4));

        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date('Ymd', $iDateFrom)); // first entry
            while ($iDateFrom < $iDateTo) {
                $iDateFrom+=86400; // add 24 hours
                array_push($aryRange, date('Ymd', $iDateFrom));
            }
        }
        $datas = getdata($aryRange, $driver);
        echo '{"data":' . json_encode($datas) . '}';
        break;

    case 'getBranchDriver':

        if ($_POST['Branch'] > 0) {

            $data = $db->getMultipleData("SELECT d_ID as driver_id,d_Name as driver_name FROM  qugeo_driver WHERE br_id = {$_POST['Branch']} ORDER BY d_Name ", true);
            if (!empty($data)) {
                $data = json_encode($data);
                echo '{"data":' . $data . '}';
            } else {
                echo '{"data":[]}';
            }
        }
        break;
}