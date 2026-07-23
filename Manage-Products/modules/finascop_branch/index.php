<?php

require_once(INCLUDE_PATH . "/finascop_accounts_Master.php");
require_once(INCLUDE_PATH . "/brmClass.php");
require_once(INCLUDE_PATH . "/finascop_common_functions.php");



switch ($op) {
    case 'getBranchStructure':

        $Basic_Nature = $db->getMultipleData("SELECT finascop_branch.br_ID as br_id,concat(br_Name,'[',branch_shortname,']') AS br_name  
		from " . FINASCOP_DB . "finascop_branch inner join finascop_branch_company using(br_id)  where br_PyramidLevel =1  and comp_id = {$_SESSION['admin']->finascop_current_company_id}                                            
		ORDER BY br_name", true);

        if (!empty($Basic_Nature)) {
            $Basic_Nature_node = array();
            foreach ($Basic_Nature as $idx => $val) {
                $Basic_Nature_node[$idx] = array();
                $Basic_Nature_node[$idx]['id'] = 'L1_' . $val['br_id'];
                $Basic_Nature_node[$idx]['text'] = $val['br_name'];
                $Basic_Nature_node[$idx]['draggable'] = false;
                $Basic_Nature_node[$idx]['children'] = '';
                $Basic_Nature_node[$idx]['cls'] = 'finascop_basic_nature';


                $NatGroupName = $db->getMultipleData("SELECT finascop_branch.br_ID as br_id,concat(br_Name,'[',branch_shortname,']') AS br_name  
				from " . FINASCOP_DB . "finascop_branch                                    
				WHERE  br_status = 'Active' and  br_cpd = '{$val['br_id']}'
				ORDER BY br_name", true);


                if (!empty($NatGroupName)) {
                    $Basic_Nature_node[$idx]['leaf'] = false;
                    $NatGroupName_node = array();
                    foreach ($NatGroupName as $idp => $value) {
                        $NatGroupName_node[$idp] = array();
                        $NatGroupName_node[$idp]['id'] = 'L2_' . $value['br_id'];
                        $NatGroupName_node[$idp]['text'] = $value['br_name'];
                        $NatGroupName_node[$idp]['draggable'] = false;
                        $NatGroupName_node[$idp]['children'] = '';
                        $NatGroupName_node[$idp]['cls'] = 'finascop_nature_group';

                        $GroupName = $db->getMultipleData("SELECT finascop_branch.br_ID as br_id,concat(br_Name,'[',branch_shortname,']') AS br_name  
						from " . FINASCOP_DB . "finascop_branch                                    
						WHERE  br_status = 'Active' and  br_cpd = '{$value['br_id']}'
						ORDER BY br_name", true);

                        if (!empty($GroupName)) {
                            $NatGroupName_node[$idp]['leaf'] = false;
                            $BranchLedgerName_node = array();
                            foreach ($GroupName as $idl => $values) {
                                $BranchLedgerName_node[$idl] = array();
                                $BranchLedgerName_node[$idl]['id'] = 'L3_' . $values['br_id'];
                                $BranchLedgerName_node[$idl]['text'] = $values['br_name'];
                                //  $BranchLedgerName_node[$idl]['leaf'] = false;
                                $BranchLedgerName_node[$idl]['draggable'] = true;
                                $BranchLedgerName_node[$idl]['children'] = '';
                                $BranchLedgerName_node[$idl]['cls'] = 'finascop_group';

                                $Ledgertypename = $db->getMultipleData("SELECT finascop_branch.br_ID as br_id,concat(br_Name,'[',branch_shortname,']') AS br_name  
								from " . FINASCOP_DB . "finascop_branch                                    
								WHERE br_status = 'Active' and br_cpd = '{$values['br_id']}'
								ORDER BY br_name", true);

                                if (!empty($Ledgertypename)) {
                                    $BranchLedgerName_node[$idl]['leaf'] = false;
                                    $Ledgertypename_node = array();
                                    foreach ($Ledgertypename as $ld => $Ledgertypename_values) {
                                        $Ledgertypename_node[$ld] = array();
                                        $Ledgertypename_node[$ld]['id'] = 'L4_' . $Ledgertypename_values['br_id'];
                                        $Ledgertypename_node[$ld]['text'] = $Ledgertypename_values['br_name'];
                                        $Ledgertypename_node[$ld]['leaf'] = true;
                                        $Ledgertypename_node[$ld]['draggable'] = true;
                                        $Ledgertypename_node[$ld]['children'] = '';
                                        $Ledgertypename_node[$ld]['cls'] = 'finascop_ledger_type';
                                    }

                                    /*   if ($values['Group_ID'] == 23) {
                                      print_r($Ledgertypename_node);
                                      exit;
                                      } */

                                    $BranchLedgerName_node[$idl]['children'] = $Ledgertypename_node;
                                    $Ledgertypename_node = array();
                                    $ledger_name_node = array();
                                } else {
                                    $BranchLedgerName_node[$idl]['leaf'] = true;
                                    $BranchLedgerName_node[$idl]['children'] = array();
                                }
                            }
                            $NatGroupName_node[$idp]['children'] = $BranchLedgerName_node;
                            $BranchLedgerName_node = array();
                        } else {
                            $NatGroupName_node[$idp]['leaf'] = true;
                            $NatGroupName_node[$idp]['children'] = array();
                        }
                    }
                    $Basic_Nature_node[$idx]['children'] = $NatGroupName_node;
                    $NatGroupName_node = array();
                } else {
                    $Basic_Nature_node[$idx]['leaf'] = true;
                    $Basic_Nature_node[$idx]['children'] = array();
                }
            }
        }
        //print_r($Basic_Nature_node);
        echo json_encode($Basic_Nature_node);
        break;
    case 'listCPDBranch':
        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'br_Name' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['br_id', 'br_name', 'br_code', 'br_type', 'br_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }

        $countQuery = "SELECT COUNT(1) FROM "
                . FINASCOP_DB . "finascop_branch a INNER JOIN " . FINASCOP_DB . "finascop_branch_company fbc "
                . "ON a.br_ID = fbc.br_ID  WHERE br_PyramidLevel= 1 AND comp_id = {$_SESSION['admin']->finascop_current_company_id}";
         $listQuery = "SELECT a.br_ID,br_Name,branch_shortname,br_csdefault,"
                . "(SELECT dst_Name FROM " . FINASCOP_DB . "finascop_district WHERE dst_Id = br_District) as br_District,"
                . "(SELECT st_name FROM " . FINASCOP_DB . "finascop_state WHERE st_ID = br_State) as br_State,"
                . "(SELECT comp_name from " . FINASCOP_DB . "finascop_company WHERE comp_id = "
                . "(SELECT comp_id from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id = a.br_ID)) as company,"
                . "(SELECT comp_id from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id = a.br_ID) as comp_id,"
                . "br_Address,br_Fax,br_Email,br_Phone,br_Incharge,br_status,br_pincode,br_Lat,br_Lng,if(br_PyramidLevel <> 1,(SELECT br_Name FROM finascop_branch WHERE br_ID = a.br_cpd),' ') AS  branchCpd "
                . "from " . FINASCOP_DB . "finascop_branch a INNER JOIN " . FINASCOP_DB . "finascop_branch_company fbc ON a.br_ID = fbc.br_ID "
                . "WHERE br_PyramidLevel= 1 AND comp_id = {$_SESSION['admin']->finascop_current_company_id} "
                . "AND  {$filter_part} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        $db->printGridJson($countQuery, $listQuery);

        break;

    case 'listBranch':

        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'br_Name' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['br_id', 'br_name', 'br_code', 'br_type', 'br_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }

        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "finascop_branch WHERE br_PyramidLevel= 3 AND  {$filter_part} ";
        $listQuery = "SELECT br_ID,br_Name,branch_shortname,br_csdefault,br_storeGroup,"
                . "(SELECT dst_Name FROM " . FINASCOP_DB . "finascop_district WHERE dst_Id = br_District) as br_District,"
                . "(SELECT st_name FROM " . FINASCOP_DB . "finascop_state WHERE st_ID = br_State) as br_State,"
                . "(SELECT comp_name from " . FINASCOP_DB . "finascop_company WHERE comp_id = "
                . "(SELECT comp_id from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id = a.br_ID)) as company,"
                . "(SELECT comp_id from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id = a.br_ID) as comp_id,"
                . "br_Address,br_Fax,br_Email,br_Phone,br_Incharge,br_status,br_pincode,br_Lat,br_Lng,if(br_PyramidLevel <> 1,(SELECT br_Name FROM finascop_branch WHERE br_ID = a.br_cpd),' ') AS  branchCpd "
                . "from " . FINASCOP_DB . "finascop_branch a WHERE br_PyramidLevel= 3 AND  {$filter_part} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
//                echo $listQuery;
//               exit;
        // $db->printGridJson($countQuery, $listQuery);

        $datas = $db->getMulipleData($listQuery, true);
        // print_r($datas);
        $resCount = count($datas);
        $count = $db->getItemFromDB($countQuery);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $br_defRetailor = $db->getFromDB("SELECT br_ID,br_Name FROM finascop_branch WHERE br_cpd = {$datas[$i]['br_ID']} AND br_csdefault = 1 AND br_PyramidLevel = 4", true);
                $datas[$i]['br_defRetailor'] = ($br_defRetailor['br_ID'] > 0 ? $br_defRetailor['br_Name'] : '-');
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }

        break;

    case 'saveBranch':
        global $db;
        $db->query('begin');
        $data = $_POST;
        // print_r($data);exit();
        if ($data['br_ID'] > 0) {
            $br_cpd = $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$data['br_ID']}");
            if ($br_cpd != $data['br_cpd']) {
                echo "{success: false, errors:  'You are not allowed to change Central Store' }";
                exit();
            }
        }

        //echo $dat;
        $branch = new \finascop\accounts\Master\brmBranch();
        $status = $branch->saveBranch($data, ($data['br_ID'] > 0 ? false : true), false);
        if ($status) {
            echo "{success: true}";
            $db->query('commit');
        } else {
            echo "{success: false, errors:  'FINASCOP: Error occured while saving data' }";
        }

        break;


    case 'getDetails':
        $branch = new \finascop\accounts\Master\brmBranch();
        $branch->getDetails($_POST['id']);
        break;

    case 'changeStatus':

        $branch = new \finascop\accounts\Master\brmBranch();
        $branch->changeStatus($_POST['br_ID'], $_POST['br_status'], $_POST['comp_id']);

        break;

    case 'getComboStore':

        $branch = new \finascop\accounts\Master\brmBranch();
        //print_r($_GET);
        $branch->getComboStore($_GET['ind'], $_POST['state']);

        break;
    case 'centralStoreslist':

        $listQuery = "SELECT br_ID , br_Name from finascop_branch where br_PyramidLevel= 2";

        $data = $db->getMultipleData($listQuery, true);
        echo json_encode($data);

        break;
}






