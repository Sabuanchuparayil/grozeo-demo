<?php

/**
 * @author Kavitha K<kavitha@saturn.in>
 * @crated on 14-02-2012
 * test
 */
require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/finascop_config/config.php');

//validating dates
function dateValidation($postDate, $typeDate = '') {
    $pDate = explode('/', $postDate);
    $pDate = date("Y-m-d", mktime(0, 0, 0, $pDate[1], $pDate[0], $pDate[2]));
    $ddate = strtotime($pDate);
    if ($typeDate == '') {
        $date = time();
        return $diff = doubleval(($date - $ddate) / (60 * 60 * 24));
    } else {
        $mDate = explode('/', $typeDate);
        $mDate = date("Y-m-d", mktime(0, 0, 0, $mDate[1], $mDate[0], $mDate[2]));
        $datetyp = strtotime($mDate);

        return $diff = doubleval(($ddate - $datetyp ) / (60 * 60 * 24));
    }
}

switch ($op) {
    /**
     * To save the Vehicle details
     */
    case 'saveVehicle':
        $msg = '';
        $vNo = trim($_POST['v_No']);
        /**
         * field Validation.validating required fields
         */
        $reqFields = array("Vehicle Reg No" => $vNo,
            "Vehicle Type" => $_POST['v_Type'],
            "Manufacturer" => $_POST['v_manufacturor'],
            "Date Of Purchase" => $_POST['dt_purchase'],
            "Date Of Manufacturing" => $_POST['dt_manufacture'],
            "Valid Permit Date" => $_POST['dt_permit'],
            "Fitness Certificate Date" => $_POST['dt_fitness'],
            "Pollution Certificate Expiry Date" => $_POST['dt_pollution'],
            "Insurance expiry date" => $_POST['dt_insurance'],
            "Rate/KM" => $_POST['v_cost'],
            "Branch" => intval($_POST['br_id']),
            ($_POST['v_isCmp'] == 1) ? "Company" : "HVO" => ($_POST['v_isCmp'] == 1) ? intval($_POST['comp_ID']) : intval($_POST['vhow_ID']));


        $msg = reqFieldValidation($reqFields);
        $comboFields = array("Branch" => array("cond" => "br_id=" . intval($_POST['br_id']), "table" => "finascop_branch", $_POST['br_id']),
            "Company" => array("cond" => "comp_id=" . intval($_POST['comp_ID']), "table" => "finascop_company", $_POST['comp_ID']),
            "HVO" => array("cond" => "vhow_ID=" . intval($_POST['vhow_ID']), "table" => "qugeo_vehicle_owners", $_POST['vhow_ID']));
        $cMsg = checkValidationCombo($comboFields);
        if (!empty($cMsg)) {
            echo '{"success":false,"msg":"' . $cMsg . '"}';
            exit;
        }
        if (!empty($msg)) {
            echo '{"success":false,"msg":"' . $msg . '"}';
            exit;
        }
//validating Name,Special characters can not be allowed more than once at a time.allowed space only 
        $msg = '';
        $vNo = trim($_POST['v_No']);
        if (strpos($vNo, ' ') !== false) {
            echo '{"success":false,"msg":"Space can not be allowed in Vehicle Reg No "}';
            exit;
        }
        $bID = intval($_POST['br_id']);
        //checking branch name is empty or not

        if (empty($bID)) {
            $msg = "Please select branch before save";
            echo '{"success":true,"msg":"' . $msg . ' "}';
            exit;
        } else {
            //$ts = microtime(true);
            //$fileName = $ts . '.0.spl';

            // create an instance of diskwriter
            //$dw = new diskWriter($fileName, 0, true);
            /**
             * To check duplicate values
             */
            if (!empty($_POST['v_ID']))
                $cond = " and v_id <> " . intval($_POST['v_ID']);
            $qry = "select count(*) from  qugeo_vehicle where v_No = 
                '" . $vNo . "'" . $cond;
            $isDuplicate = $db->getItemFromDB($qry);
            //checking duplicates
            if ($isDuplicate) {
                $msg = "Vehicle already Exists";
                echo '{"success":false,"msg":"' . $msg . '"}';
                exit;
            } else {

                $qry = "SELECT coalesce(max( v_id ),0)+1 FROM  qugeo_vehicle ";
                $dID = $db->getItemFromDB($qry);

                foreach ($_POST as $k => $value) {
                    switch ($k) {
                        //validating date of Purchase
                        case 'dt_purchase':
                            if (!empty($_POST['dt_purchase'])) {
                                $diff = dateValidation($_POST['dt_purchase']);
                                $diffwithMan = dateValidation($_POST['dt_purchase'], $_POST['dt_manufacture']);
                                if ($diff < 0) {
                                    $msg = " Please check Date of Purchase";
                                    echo '{"success":false,"msg":"' . $msg . '"}';
                                    exit;
                                }
                                if ($diffwithMan < 1) {
                                    $msg = "Date of Purchasing should be greater than Manufacturing Date";
                                    echo '{"success":false,"msg":"' . $msg . '"}';
                                    exit;
                                } else {
                                    $pDate = explode('/', $_POST['dt_purchase']);
                                    $pDate = date("Y-m-d", mktime(0, 0, 0, $pDate[1], $pDate[0], $pDate[2]));
                                }
                            } else
                                $pDate = "0000-00-00";
                            break;
                        //validating manufacturing date   
                        case 'dt_manufacture':
                            if (!empty($_POST['dt_manufacture'])) {
                                $diff = dateValidation($_POST['dt_manufacture']);
                                if ($diff < 1) {
                                    $msg = " Please check Date of Manufacturing";
                                    echo '{"success":false,"msg":"' . $msg . '"}';
                                    exit;
                                } else {
                                    $mDate = explode('/', $_POST['dt_manufacture']);
                                    $mDate = date("Y-m-d", mktime(0, 0, 0, $mDate[1], $mDate[0], $mDate[2]));
                                }
                            } else
                                $mDate = "0000-00-00";
                            break;
                        //validating permit  
                        case 'dt_permit':
                            if (!empty($_POST['dt_permit'])) {
                                $diffwithMan = dateValidation($_POST['dt_permit'], $_POST['dt_manufacture']);
                                $diffwithPurchase = dateValidation($_POST['dt_permit'], $_POST['dt_purchase']);
                                $diff = dateValidation($_POST['dt_permit']);
                                if ($diff > 0) {
                                    $msg = " Please check Date of Permit";
                                    echo '{"success":false,"msg":"' . $msg . '"}';
                                    exit;
                                }
                                if ($diffwithMan < 1) {
                                    $msg = "Date of Permit should be greater than Manufacturing Date";
                                    echo '{"success":false,"msg":"' . $msg . '"}';
                                    exit;
                                }
                                if ($diffwithPurchase < 1) {
                                    $msg = "Date of Permit should be greater than Purchasing Date";
                                    echo '{"success":false,"msg":"' . $msg . '"}';
                                    exit;
                                } else {
                                    $prDate = explode('/', $_POST['dt_permit']);
                                    $prDate = date("Y-m-d", mktime(0, 0, 0, $prDate[1], $prDate[0], $prDate[2]));
                                }
                            } else
                                $prDate = "0000-00-00";
                            break;
                        //validating fitness  date 
                        case 'dt_fitness':
                            if (!empty($_POST['dt_fitness'])) {
                                $diffwithMan = dateValidation($_POST['dt_fitness'], $_POST['dt_manufacture']);
                                $diffwithPurchase = dateValidation($_POST['dt_fitness'], $_POST['dt_purchase']);
                                $diff = dateValidation($_POST['dt_fitness']);
                                if ($diff > 0) {
                                    $msg = " Please check Date of Fitness";
                                    echo '{"success":false,"msg":"' . $msg . '"}';
                                    exit;
                                }

                                if ($diffwithMan < 1) {
                                    $msg = "Date of Fitness should be greater than Manufacturing Date";
                                    echo '{"success":false,"msg":"' . $msg . '"}';
                                    exit;
                                }
                                if ($diffwithPurchase < 1) {
                                    $msg = "Date of Fitness should be greater than Purchasing Date";
                                    echo '{"success":false,"msg":"' . $msg . '"}';
                                    exit;
                                } else {
                                    $fDate = explode('/', $_POST['dt_fitness']);
                                    $fDate = date("Y-m-d", mktime(0, 0, 0, $fDate[1], $fDate[0], $fDate[2]));
                                }
                            } else
                                $fDate = "0000-00-00";
                            break;
                        //validating polution date   
                        case 'dt_pollution':
                            if (!empty($_POST['dt_pollution'])) {
                                $diffwithMan = dateValidation($_POST['dt_pollution'], $_POST['dt_manufacture']);
                                $diffwithPurchase = dateValidation($_POST['dt_pollution'], $_POST['dt_purchase']);
                                $diff = dateValidation($_POST['dt_pollution']);
                                if ($diff > 0) {
                                    $msg = " Please check Date of Pollution";
                                    echo '{"success":false,"msg":"' . $msg . '"}';
                                    exit;
                                }


                                if ($diffwithMan < 1) {
                                    $msg = "Date of Pollution should be greater than Manufacturing Date";
                                    echo '{"success":false,"msg":"' . $msg . '"}';
                                    exit;
                                }
                                if ($diffwithPurchase < 1) {
                                    $msg = "Date of Pollution should be greater than Purchasing Date";
                                    echo '{"success":false,"msg":"' . $msg . '"}';
                                    exit;
                                } else {
                                    $plDate = explode('/', $_POST['dt_pollution']);
                                    $plDate = date("Y-m-d", mktime(0, 0, 0, $plDate[1], $plDate[0], $plDate[2]));
                                }
                            } else
                                $plDate = "0000-00-00";
                            break;
                        //validating insurence date   
                        case 'dt_insurance':
                            if (!empty($_POST['dt_insurance'])) {
                                $diffwithMan = dateValidation($_POST['dt_insurance'], $_POST['dt_manufacture']);
                                $diffwithPurchase = dateValidation($_POST['dt_insurance'], $_POST['dt_purchase']);
                                $diff = dateValidation($_POST['dt_insurance']);
                                if ($diff > 0) {
                                    $msg = " Please check Date of Insurance";
                                    echo '{"success":false,"msg":"' . $msg . '"}';
                                    exit;
                                }


                                if ($diffwithMan < 1) {
                                    $msg = "Date of Insurance should be greater than Manufacturing Date";
                                    echo '{"success":false,"msg":"' . $msg . '"}';
                                    exit;
                                }
                                if ($diffwithPurchase < 1) {
                                    $msg = "Date of Insurance should be greater than Purchasing Date";
                                    echo '{"success":false,"msg":"' . $msg . '"}';
                                    exit;
                                } else {
                                    $inDate = explode('/', $_POST['dt_insurance']);
                                    $inDate = date("Y-m-d", mktime(0, 0, 0, $inDate[1], $inDate[0], $inDate[2]));
                                }
                            } else
                                $inDate = "0000-00-00";
                            break;
                    }
                }

                $data = array("v_No" => $vNo,
                    "v_Type" => intval($_POST['v_Type']),
                    "v_manufacturor" => $_POST['v_manufacturor'],
                    "dt_purchase" => $pDate,
                    "dt_manufacture" => $mDate,
                    "dt_permit" => $prDate,
                    "dt_fitness" => $fDate,
                    "dt_pollution" => $plDate,
                    "dt_insurance" => $inDate,
                    "hr_inst" => intval($_POST['hr_inst']),
                    "hr_ir" => intval($_POST['hr_ir']),
                    "v_cost" => intval($_POST['v_cost']),
                    "cmpId" => ($_POST['v_isCmp'] == 1) ? intval($_POST['comp_ID']) : intval($_POST['vhow_ID']),
                    "br_id" => $_POST['br_id'],
                    "hpa_Status" => $_POST['hpa_id'],
                    "v_isCmp" => $_POST['v_isCmp'],
                    "v_chasisno" => $_POST['v_chasisno'],
                    "v_permit" => $_POST['v_permit'],
                    "v_Engineno" => $_POST['v_Engineno'],
                    "v_Model" => $_POST['v_Model'],
                    "v_Make" => $_POST['v_Make'],
                    "v_color" => $_POST['v_color']);
                $db->query('begin');
                //insert
                if (empty($_POST['v_ID'])) {
                    $data['v_ID'] = $dID;
                    $db->perform('qugeo_vehicle', $data);
                    $msg = "Vehicle Created Successfully";
                    
                    //update
                } else {
                    $con = 'v_id=' . intval($_POST['v_ID']);
                    $db->perform('qugeo_vehicle', $data, 'update', $con);
                    $msg = "Vehicle Updated Successfully";

                    
                }

                $db->query('commit');
            }
            echo '{"success":true,"msg":"' . $msg . '"}';
        }

        break;
    case 'getBranch':
        /* get branch for combo
         */
        $query = $_POST['query'];
        if ($query != '')
            $con = " where br_Name like '" . $query . "%'";
        else
            $con = '';


        $qry = "select br_ID,br_Name from  finascop_branch 
                " . $con . " order by br_Name ";
        $branch = $db->getMulipleData($qry, true);
        if (!empty($branch)) {
            $branch = json_encode($branch);
            echo '{"data":' . $branch . '}';
        } else {
            echo '{"data":[]}';
        }

        break;
    case 'getVehicleType':
        /* get vehicle type  for combo
         */
        /* $query = $_POST['query'];
          if ($query != '')
          $con = " where v_Name like '" . $query . "%'";
          else
          $con = '';
         */

        $qry = "select vhty_id,vhty_name from  qugeo_vehicletype 
                order by vhty_name ";
        $vehicletype = $db->getMulipleData($qry, true);
        if (!empty($vehicletype)) {
            $vehicletype = json_encode($vehicletype);
            echo '{"data":' . $vehicletype . '}';
        } else {
            echo '{"data":[]}';
        }

        break;
    case 'getCompany':
        /* get Company for combo
         */
        $query = $_POST['query'];
        if ($query != '')
            $con = " where comp_name like '" . $query . "%'";
        else
            $con = '';


        $qry = "select comp_id,comp_name from  finascop_company 
                " . $con . " order by comp_name ";
        $company = $db->getMulipleData($qry, true);
        if (!empty($company)) {
            $company = json_encode($company);
            echo '{"data":' . $company . '}';
        } else {
            echo '{"data":[]}';
        }

        break;
    case 'getHireOwner':
        /* get Company for combo
         */
        $query = $_POST['query'];
        if ($query != '')
            $con = " and vhow_Name like '" . $query . "%'";
        else
            $con = '';

        if (empty($_POST['id']))
            $activeCon = " and vhow_Active =1";
        else
            $activeCon = " and vhow_Active =1 or vhow_ID=(select cmpId from 
                qugeo_vehicle where v_id=" . intval($_POST['id']) . ")";

        $qry = "select vhow_ID,vhow_Name from  qugeo_vehicle_owners where 1=1 
             " . $activeCon . $con . " order by vhow_Name ";
        $vowner = $db->getMulipleData($qry, true);
        if (!empty($vowner)) {
            $vowner = json_encode($vowner);
            echo '{"data":' . $vowner . '}';
        } else {
            echo '{"data":[]}';
        }

        break;
    case 'gethpastatus':
        /* get Company for combo
         */
        $query = $_POST['query'];
        if ($query != '')
            $con = " and hpa_status like '" . $query . "%'";
        else
            $con = '';

        if ($_POST['type'] == 'hire') {
            $qry = "select hpa_id,hpa_status from  qugeo_hpa_status 
                where hpa_id in(1,2)" . $con . "  order by hpa_status ";
        } else {
            $qry = "select hpa_id,hpa_status from  qugeo_hpa_status 
                where hpa_id in(3,4)" . $con . "  order by hpa_status ";
        }

        $hpa = $db->getMulipleData($qry, true);
        if (!empty($hpa)) {
            $hpa = json_encode($hpa);
            echo '{"data":' . $hpa . '}';
        } else {
            echo '{"data":[]}';
        }

        break;
    //get volume based on selecting vehicle type      
    case 'getVolume':
        $qry = "select concat_ws('x',vhty_Lgth,vhty_Brth,vhty_Hgt) as volume,
            vhty_MaxCapacity as tonnage from  qugeo_vehicletype where vhty_Id=" .
                $_POST['v_Type'];
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    //for loading vehicle type combo
    case 'getVehicle':
        $id = intval($_POST['v_ID']);
        $qry = "select v_ID,v_No,v_Type,v_manufacturor,dt_purchase,
              dt_manufacture,dt_permit,dt_fitness,dt_pollution,dt_insurance,
              hr_inst,hr_ir,v_cost,cmpId,br_id,v_isCmp ,hpa_Status as hpa_id,(select vhty_MaxCapacity 
              from  qugeo_vehicletype where vhty_Id = qugeo_vehicle.v_Type) as tonnage,
              (select concat_ws('x',vhty_Lgth,vhty_Brth,vhty_Hgt) from  qugeo_vehicletype 
              where vhty_Id = qugeo_vehicle.v_Type) as volume,v_chasisno,
              v_permit,v_Engineno,v_Model,v_Make,v_color 
        from  qugeo_vehicle where v_id=" . $id;
        $data = $db->getFromDB($qry, true);

        if ($data['v_isCmp'] == 2) {
            $data['vhow_ID'] = $data['cmpId'];
            $qry = "select vhow_Name from 
               qugeo_vehicle_owners where vhow_Id = " . $data['cmpId'];
            $data['vhow_Name'] = $db->getItemFromDB($qry);
        }
        if ($data['v_isCmp'] == 1) {
            $data['comp_ID'] = $data['cmpId'];
            $qry = "select comp_name from 
               finascop_company where comp_id = " . $data['cmpId'];
            $data['comp_name'] = $db->getItemFromDB($qry);
        }
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;

    case 'list':
        /**
         * for listing the Vehicle details in grid
         */
        //Retrieve Parameters
        $recLimit = intval($_POST['limit']);
        $recStart = intval($_POST['start']);
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];
//--
        $fields = array(
            //'v_EntryBy' =>  'getEntryByRangeBranch(v_id)',
            'v_Active' => 'IF(v_active = 1,"Y","N")',
            'v_No' => 'v_No',
            'v_Hired' => 'IF(v_isCmp = 2,"Y","N")',
            'vhty_name' => 'vhty_name',
            'v_manufacturor' => 'v_manufacturor'
        //    'brName' => 'br_Name'
        );



        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['vehicle_no', 'vehicle_type', 'vehicle_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* RAW (disabled): if (isset($_POST['filter']) && $_POST['filter'] != '') { */
            foreach ($_POST['filter'] as $key => $v) {
                $field = $fields[$v['field']];
                //print_r($field);
                switch ($v['field']) {
                    case 'v_EntryBy':
                     
                            $condval = " < 10000000 ";
                       
                        $filterCon .= " and v_ID  " . $condval . " ";
                        break;
                    
                    case 'brName':
                        if ($v['field'] = 'br_ID') {
                            $qry = "SELECT br_ID from finascop_branch WHERE br_Name "
                                    . " LIKE'" . $v['data']['value'] . "%' ";
                            $filterCon .= " AND " . $v['field'] . " IN(" . $qry . ") ";
                        } else
                            $filterCon .= " AND " . $v['field'] . " LIKE '%" . $v['data']['value'] . "%'";
                        break;
                        
                    case 'v_Hired':
                        if (strtoupper($v['data']['value']) == 'Y') {
                            $filterCon .= " and v_isCmp = 2 ";
                        } else if (strtoupper($v['data']['value']) == 'N') {
                            $filterCon .= " and v_isCmp = 1 ";
                        }
                        break;
                    case 'v_Active':
                        if (strtoupper($v['data']['value']) == 'Y') {
                            $filterCon .= " and v_active = 1 ";
                        } else if (strtoupper($v['data']['value']) == 'N'){
                            $filterCon .= " and v_active = 0 ";
                        }
                        break;

                    default:
                        switch ($v['data']['type']) {
                            case 'string':
                                $filterCon .= " and " . $field . " like '" . $v['data']['value'] . "%'";
                                break;
                        }
                        break;
                }
            }
        }

        /*     if (isset($_POST['filter']) && $_POST['filter'] != '') {
          foreach ($_POST['filter'] as $key => $v) {
          switch ($v['data']['type']) {
          case 'string':

          $filterCon .= " and " . $v['field'] . " like '" . $v['data']['value'] . "%'";
          break;
          }
          }
          } */
        $qry = "select count(v_id) from  qugeo_vehicle inner join 
             qugeo_vehicletype on v_Type=vhty_id  where 1=1 " . $filterCon;
        $totalCount = $db->getItemFromDB($qry);
        /*SELECT v_ID, v_No,vhty_name,v_manufacturor, IF(v_active=1,'Y','N')  AS v_Active,IF(v_isCmp=2,'Y','N') AS v_Hired      FROM  qugeo_vehicle INNER JOIN  qugeo_vehicletype ON v_Type=vhty_id */
        $db->query('set @cnt=0');
        $qry = "select * from (select @cnt:=@cnt+1 as rownum,sel.* from
        (select v_ID, v_No,vhty_name,v_manufacturor, IF(v_active=1,'Y','N')  AS v_Active,IF(v_isCmp=2,'Y','N') AS v_Hired,br_id,(SELECT br_Name FROM finascop_branch WHERE finascop_branch.br_ID = qugeo_vehicle.br_id) AS brName 
         from  qugeo_vehicle inner join  qugeo_vehicletype on v_Type=vhty_id
           where 1=1  " . $filterCon . " order by " . $recSort . " " . $recSortDir . ") as sel) as sel2 limit " .
                $recStart . ", " . $recLimit;
        $data = $db->getMultipleData($qry, true);
        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;
    //for disable vehicle settings 
    case 'disableSettings':


        $data = array('v_Active' => 0);
        $db->query("begin");
        $db->perform('qugeo_vehicle', $data, 'update', 'v_id = ' . intval($_POST['VehicleId']));


        $db->query("commit");
        echo "{success: true, error: ''}";

        break;
    //for enable vehicle settings
    case 'enableSettings':


        $data = array('v_Active' => 1);
        $db->query("begin");
        $db->perform('qugeo_vehicle', $data, 'update', 'v_id = ' . intval($_POST['VehicleId']));

        $db->query("commit");
        echo "{success: true, error: ''}";

        break;
}