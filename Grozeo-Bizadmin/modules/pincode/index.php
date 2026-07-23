<?php

/**

 * @crated on 17-02-2016
 */
require_once(ROOT . '/finascop_config/lib.php');
require_once(INCLUDE_PATH . '/config.php');

switch ($op) {





    case 'list':

        /**
         * for listing all Holiday in grid
         */
        $recLimit = intval($_POST['limit']);
        $recStart = intval($_POST['start']);
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];
//--
        $recLimit = intval($_POST['limit']);
        $recStart = intval($_POST['start']);
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];
        //--
        $fields = array(
            'st_id' => 's.st_name',
            'dt_id' => 'd.dst_Name',
            'brName' => 'brName'
        );
        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['pincode', 'area_name', 'city', 'state'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* RAW (disabled): if (isset($_POST['filter']) && $_POST['filter'] != '') { */
            foreach ($_POST['filter'] as $key => $v) {
                if (array_key_exists($v['field'], $fields))
                    $field = $fields[$v['field']];
                else {
                    $field = $v['field'];
                }
                switch ($v['data']['type']) {
                    case 'list':
                        if ($field == 'is_Active') {
                            $filterCon .= (($filterCon == "") ? " where " : " and ") . 'p.isActive' . " = '" . ($v['data']['value'] == 'Y' ? '1' : '0') . "'";
                        } else {
                            $filterCon .= (($filterCon == "") ? " where " : " and ") . $field . " like '" . $v['data']['value'] . "%'";
                        }
                        break;
                        
                    case 'string':
                        if ($field == 'brName') {
                    $brName = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(branch_id),0) FROM retaline_pincode WHERE branch_id IN (SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_Name LIKE '{$v['data']['value']}%' )");
                    $filterCon .= " AND b.br_ID IN({$brName}) ";
                        } else {
                            $filterCon .= (($filterCon == "") ? " where " : " and ") . $field . " like '" . $v['data']['value'] . "%'";
                        }
                        break;
                }
            }
        }
        $qry = "select count(pincode) from " . FINASCOP_DB . "retaline_pincode p inner join " . FINASCOP_DB . "finascop_district d on p.dst_id = d.dst_Id inner join " . FINASCOP_DB . "finascop_state s on d.st_ID= s.st_ID  inner join " . FINASCOP_DB . "finascop_branch b on p.branch_id=b.br_ID" . $filterCon;

        $totalCount = $db->getItemFromDB($qry);
        $prefix = FINASCOP_DB;
        $prefix1 = FINASCOP_DB . 'config';
        $db->query('set @cnt=0');
        $query = "select * from (select @cnt:=@cnt+1 as rownum,sel.* from
      	(select p.pincode, p.dst_id,s.st_name as st_id,b.br_Name as brName,IF(p.isActive=1,'Y','N')  AS is_Active,d.dst_Name as dt_id ,if(p.Has_Pickup=1,'Y','N') as pickup, if(p.Has_Delivery=1,'Y','N') as delivery from retaline_pincode p inner join 
        finascop_district d on p.dst_id = d.dst_Id inner join finascop_state s on d.st_ID= s.st_ID inner join finascop_branch b on p.branch_id= b.br_ID $filterCon order by $recSort $recSortDir)as sel )as sel2 
        limit $recStart,$recLimit";
        $data = $db->getMultipleData($query, true);

        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;

    case 'getPincode':
        /**
         * To fetch the details of a selected collection boy
         */
        $id = intval($_POST['pin_ID']);
        $qry = "select p.pincode,b.br_Name as brName,p.branch_id as br_ID,p.Has_Pickup as is_pickups,p.Has_Delivery as is_deliverys,s.st_name as st_name,s.st_ID,d.dst_Name as dst_Name,d.dst_Id 
        from " . FINASCOP_DB . "retaline_pincode p inner join " . FINASCOP_DB . "finascop_district d on p.dst_id = d.dst_Id inner join " . FINASCOP_DB . "finascop_state s on d.st_ID= s.st_ID inner join " . FINASCOP_DB . "finascop_branch b on p.branch_id= b.br_ID where pincode=" . $id;
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;

    /* for saving pincode    */

    case 'savePincode':
        $msg = '';
        $state = $_POST['c_State'];
        $district = $_POST['c_District'];
        $branch = $_POST['br_id'];

        $msg = '';

        $ts = microtime(true);
        $fileName = $ts . '.0.spl';

        // create an instance of diskwriter


        $data = array("pincode" => $_POST['pincode'],
            "dst_id" => $district,
            "branch_id" => $branch,
            " Has_Pickup" => ($_POST['is_pickups'] == 'on') ? 1 : 0,
            " Has_Delivery" => ($_POST['is_deliverys'] == 'on') ? 1 : 0
        );
        if ($_POST['is_pickups'] != 'on' && $_POST['is_deliverys'] != 'on') {
            $data['isActive'] = 0;
        }
        $db->query('begin');
        //do insert
        if (empty($_POST['pin_id'])) {
            $db->perform(FINASCOP_DB . 'retaline_pincode', $data);
            $msg = "Pin Code Created Successfully";
            //do update
        } else {
            $con = 'pincode=' . intval($_POST['pin_id']);
            $db->perform(FINASCOP_DB . 'retaline_pincode', $data, 'update', $con);
            $msg = "Pincode Updated Successfully";
        }
        $db->query('commit');
        //}
        echo '{"success":true,"msg":"' . $msg . '"}';

        break;
    case 'getmaps':
        require dirname(__FILE__) . '/maps.php';
        getMap();
        break;


    case 'getState':
        /* get state for load  state combo
         */
        $defaultCountry = $db->getItemFromDB("SELECT country_id FROM retaline_country WHERE is_default = 1");
        $query = $_POST['query'];
        if ($query != '')
            $con = " and st_name like '" . $query . "%'";
        else
            $con = '';


        $qry = "select st_ID,st_name from " . FINASCOP_DB . "finascop_state WHERE cnt_ID = {$defaultCountry}
                " . $con . " order by st_name ";
        $state = $db->getMulipleData($qry, true);
        if (!empty($state)) {
            $state = json_encode($state);
            echo '{"data":' . $state . '}';
        } else {
            echo '{"data":[]}';
        }


        break;

    case 'getDistrict':
        /* get district for load district combo
         */
        $query = $_POST['query'];
        if ($query != '')
            $con = "  dst_Name like '" . $query . "%'";
        else
            $con = '';

        if (!empty($_POST['st_ID'])) {
            $qry = "select dst_Id,dst_Name from " . FINASCOP_DB . "finascop_district 
                where st_ID =" . $_POST['st_ID'] .
                    $con . " order by dst_Name ";
            $dist = $db->getMulipleData($qry, true);
            if (!empty($dist)) {
                $dist = json_encode($dist);
                echo '{"data":' . $dist . '}';
            } else {
                echo '{"data":[]}';
            }
        } else {
            echo '{"success":false,"msg":"Please select State "}';
            exit;
        }

        break;
    case 'disableSettings':

        $data = array('isActive' => 0);
        $db->query("begin");
        $db->perform(FINASCOP_DB . 'retaline_pincode', $data, 'update', 'pincode = ' . intval($_POST['pin_ID']));

        $db->query("commit");
        echo "{success: true, error: ''}";

        break;

    /* for enable pincode settings */
    case 'enableSettings':
        $qry = "SELECT pincode,Has_Pickup,Has_Delivery FROM " . FINASCOP_DB . "retaline_pincode WHERE pincode = " . intval($_POST['pin_ID']);
        $brlo = $db->getFromDB($qry, true);

        $data = array('isActive' => 1);
        $db->query("begin");
        $db->perform(FINASCOP_DB . 'retaline_pincode', $data, 'update', 'pincode = ' . intval($_POST['pin_ID']));


        $db->query("commit");
        echo "{success: true, error: ''}";

        break;
    case 'getBranch':
        //$state = $_POST['st_Id'];
        $qry = "SELECT br_ID,br_Name FROM " . FINASCOP_DB . "finascop_branch where br_status='Active' AND br_PyramidLevel = 2 ";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        // echo '{success:true, data:'.json_encode($data).'}';
        break;
}