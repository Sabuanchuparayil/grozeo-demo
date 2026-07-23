<?php

/**
 * @author Kavitha K<kavitha@saturn.in>
 * @crated on 27-02-2012
 */
//require_once(ROOT . '/../rpc/lib.php');
//require_once(ROOT . '/../rpc/config.php');

switch ($op) {
    /**
     * To save the Vehicleowner Details 
     */
    case 'saveVehicleowner':
        $msg = '';
        $voName = trim($_POST['vhow_Name']);
        /**
         * field Validation.Validating required fields
         */
        $reqFields = array("Name" => $voName,
            "Address1" => $_POST['vhow_Add1'],
            "Address2" => $_POST['vhow_Add2'],
            "Address3" => $_POST['vhow_Add3'],
            "Phone" => $_POST['vhow_Ph1']
        );
   //validating Name,Special characters can not be allowed more than once at a time.allowed dot only    
  $msg = '';
$voName = trim($_POST['vhow_Name']);
if (strpos($voName,'..')!==false || strpos($voName, '  ')!==false) {
    echo '{"success":false,"msg":"Special characters can not be used more than once at a time "}';
            exit;
}

        $msg = reqFieldValidation($reqFields);
        if (!empty($msg)) {
            echo '{"success":false,"msg":"' . $msg . '"}';
            exit;
        } else {
            /**
             * To save vehicle owner
             */
            $ts = microtime(true);
            $fileName = $ts . '.0.spl';

            // create an instance of diskwriter
            //$dw = new diskWriter($fileName, 0, true);
            /**
             * To check duplicates
             */
            if (!empty($_POST['vhow_ID']))
                $cond = " and vhow_id <> " . intval($_POST['vhow_ID']);
            $qry = "select count(*) from qugeo_vehicle_owners where vhow_name = 
                '" . $voName . "'" . $cond;
            $isDuplicate = $db->getItemFromDB($qry);
            if ($isDuplicate) {
                $msg = "Vehicle Owner already Exists";
                echo '{"success":false,"msg":"' . $msg . '"}';
                exit;
            } else {
                /**
                 * To save vehicle owner
                 */
//                $qry = "SELECT br_startIndex FROM finascop_branch WHERE br_id =1 ";
//                $hoStartIndex = $db->getItemFromDB($qry);
//                $qry = "SELECT coalesce(max( vhow_id ),0)+1 FROM qugeo_vehicle_owners 
//                    WHERE vhow_id < " . intval($hoStartIndex);
                $qry = "SELECT coalesce(max( vhow_id ),0)+1 FROM qugeo_vehicle_owners";
                $voID = $db->getItemFromDB($qry);

                $data = array("vhow_Name" => $voName,
                    "vhow_Add1" => $_POST['vhow_Add1'],
                    "vhow_Add2" => $_POST['vhow_Add2'],
                    "vhow_Add3" => $_POST['vhow_Add3'],
                    "vhow_Ph1" => $_POST['vhow_Ph1'],
                    "vhow_Active" => intval($_POST['vhow_Active']));
                $db->query('begin');
                //insert
                if (empty($_POST['vhow_ID'])) {
                    $data['vhow_ID'] = $voID;
                    $db->perform('qugeo_vehicle_owners', $data);
                    $msg = "Vehicle Owner Created Successfully";
                    //actionDW('qugeo_vehicle_owners', $data, $dw, 'insert');
                    //do update
                } else {
                    $con = 'vhow_id=' . intval($_POST['vhow_ID']);
                    $db->perform('qugeo_vehicle_owners', $data, 'update', $con);
                    $msg = "Vehicle Owner Updated Successfully";
                    //actionDW('qugeo_vehicle_owners', $data, $dw, 'update', $con);
                }
                // to pack the file to a process file
                //$inFilePath = $dw->mkS3GzipFile(false,true,AWSDATAUPLOADBUCKET,AWSDATAUPLOADCONTENTTYPE); 
//        if ($inFilePath == false) {
//            
//            echo '{"success":false,"error":"Unexpected Runtime error!"}';
//            exit;
//        }


// to register the file into process queue
                //$proc = array('S3Bucket' => AWSDATAUPLOADBUCKET, 'bid' => 0, 'filename' => $inFilePath, 'entry' => date("Y-m-d G:i:s", $ts));

// save the entry into process queue
                //$db->perform('process_queue', $proc);
                $db->query('commit');
            }
            echo '{"success":true,"msg":"' . $msg . '"}';
        }

        break;

//To fetch the details of a selected Vehicle Owner
        
    case 'getVehicleowner':
        $id = intval($_POST['vhow_ID']);
        $qry = "select vhow_ID,vhow_Name,vhow_Ph1,vhow_Add1,vhow_Add2,vhow_Add3,
        vhow_Active from qugeo_vehicle_owners where vhow_id=" . $id;
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    
    case 'list':
        /**
         * for listing the Vehicleowner details in grid
         */
        //Retrieve Parameters
        $recLimit = intval($_POST['limit']);
        $recStart = intval($_POST['start']);
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];
//--
        $search = " WHERE 1=1 ";
        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['owner_name', 'owner_phone', 'owner_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* RAW (disabled): if (isset($_POST['filter']) && $_POST['filter'] != '') { */
            foreach ($_POST['filter'] as $key => $v) {
                switch ($v['data']['type']) {
                    case 'string':
                        if ($v['field'] == 'address') {
                            $filterCon .= " and vhow_Add1 like '%" . $v['data']['value'] . "%'";
                            $search .= " and (vhow_Add1 LIKE '{$field['data']['value']}%') ";
                        } else{
                        $filterCon .= " and " . $v['field'] . " like '" . $v['data']['value'] . "%'";
                        $search .= " and ({$v['field']} LIKE '{$field['data']['value']}%') ";
                    }
                        break;
                    case 'list':
                        if ($v['field'] == 'vhow_Active') {
                                $fiterCon = ($v['data']['value'] == 'Yes') ? 1 : 0;
                                $search .= " and ({$v['field']} = {$fiterCon}) ";
                            } else if ($field['data']['value'] == 'No') {
                                $fiterCon = 0;
                                $search .= " and ({$v['field']} = {$fiterCon}) ";
                            } else {
                            //    $search .= " and ({$v['field']} LIKE '{$field['data']['value']}%') ";
                                $search .= " and (vhow_Active = 1 or vhow_Active=0) ";
                            }

                        break;
                }
            }
        }
        $qry = "select count(vhow_id) from qugeo_vehicle_owners {$search} " . $filterCon;
        $totalCount = $db->getItemFromDB($qry);
        $db->query('set @cnt=0');
        $qry = <<<EOT
	 select * from (select @cnt:=@cnt+1 as rownum,sel.* from
        (select vhow_ID, vhow_Name,concat_ws(',',vhow_Add1,vhow_Add2,vhow_Add3)
        as address, vhow_Ph1,IF((vhow_Active=1),'Yes','No')  as vhow_Active from qugeo_vehicle_owners where 1=1 $filterCon 
	 order by $recSort $recSortDir ) as sel) as sel2 
        limit $recStart,$recLimit
EOT;

        $data = $db->getMultipleData($qry, true);

        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $data[$k]['vhow_Active'] = ($data[$k]['vhow_Active'] == 1) ? 'Yes' : 'No';
            }
        }
        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;
}