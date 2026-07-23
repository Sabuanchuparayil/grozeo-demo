<?php

namespace finascop\accounts\Master {
    require_once(ROOT . '/finascop_config/lib.php');

    class Company {

        public function deleteValidIp($comp_id, $validip) {
            global $db;
            $con = " where comp_id=" . $comp_id . " and validip='" . $validip . "'";

            $qry = "DELETE from " . FINASCOP_DB . "finascop_company_ip " . $con;

            $status = $db->query($qry);
            if ($status == 1)
            {
                $msg = "'FINASCOP: IP Address deleted successfully.'";
                echo '{"success":true,"msg":' . $msg . '}';
            }
            else
            {
                $msg = "'FINASCOP:Error while deleting IP Address.'";
                echo '{"success":false,"msg":' . $msg . '}';
            }
        }

        public function getApiDomains($cmp_id) {
            global $db;
            $msg = '';
            $cond = " where comp_id = " . intval($cmp_id);

            /* $qry = "select count(comp_id) from " . FINASCOP_DB . "finascop_company_ip " . $cond;
              $totalCount = $db->getItemFromDB($qry); */

            $qry1 = "select comp_id,validip as apidomains from " . FINASCOP_DB . "finascop_company_ip " . $cond;

            $res = $db->getMultipleData($qry1, true);

            if (!empty($res))
            {
                echo '{"success":true,"data":' . json_encode($res) . '}';
            }
            else
            {
                $msg = "No data found";
                echo '{"success":true,"msg":"FINASCOP:' . $msg . '","data":[],}';
            }
        }

        public function saveApiDomains($cmp_id, $validip) {
            global $db;

            if (!empty($cmp_id))
            {
                $cond = " where comp_id = " . intval($cmp_id) . " and validip = '" . $validip . "'";
                $qry = "select count(validip) from " . FINASCOP_DB . "finascop_company_ip" . $cond;

                $isDuplicate = $db->getItemFromDB($qry);
                //check duplicates
                if ($isDuplicate)
                {
                    $msg = "FINASCOP:Api Domain already Exists";
                    echo '{"success":false,"msg":"' . $msg . '"}';
                    exit;
                }


                $data = array(
                    "comp_id" => $cmp_id,
                    "validip" => $validip
                );

                $db->perform(FINASCOP_DB . "finascop_company_ip", $data);
                $msg = "FINASCOP:Api Domain Created Successfully";
                echo '{"success":true,"msg":"' . $msg . '"}';
            }
            else
            {
                $msg = "FINASCOP:Unable to save Api Domain";
                echo '{"success":false,"msg":"' . $msg . '"}';
            }
        }

        public function checkAudit($comp_id, $auditing_company) {
            global $db;
            if (!isset($comp_id))
            {
                echo "{success: true,valid: true}";
            }
            elseif (isset($auditing_company))
            {
                echo "{success: true,valid: true}";
            }
            else
            {
                $compcount = $db->getItemFromDB("SELECT count(*) from " . FINASCOP_DB . "finascop_company WHERE auditing_company = 'Yes' and comp_id = " . $comp_id, true);
                $sel = $db->getItemFromDB("SELECT count(*) FROM " . FINASCOP_DB . "`finascop_usr_profile` uac WHERE typId = 4 ", true);
                if ($sel > 0 && $compcount > 0)
                {
                    echo "{success: true,valid: false,msg:'FINASCOP:Auditors have been created. Please select Is an Audting Company'}";
                    exit;
                }
                else
                {
                    echo "{success: true,valid: true}";
                }
            }
        }

        public function changeStatus($data) {
            global $db;
            $st = ($data['cmp_status'] == 'Active') ? 'Inactive' : 'Active';

            if ($st == 'Inactive')
            {

                $isauditcompany = $db->getItemFromDB("SELECT if(auditing_company='Yes',1,0) from " . FINASCOP_DB . "finascop_company WHERE comp_id = " . $data['comp_id'], true);
                if ($isauditcompany == 1)
                {
                    $sel = $db->getItemFromDB("SELECT count(*) FROM " . FINASCOP_DB . "`finascop_usr_profile` uac WHERE typId = 4 ", true);
                    if ($sel > 0)
                    {
                        echo "{success: false,valid: false, errors: 'FINASCOP: You cannot de-activate this company as Auditors have been created.' }";
                        exit;
                    }
                }
            }


            $up = array('cmp_status' => $st);

            $status = $db->perform(FINASCOP_DB . "finascop_company", $up, "update", "comp_id={$data['comp_id'] }");
            if ($status)
            {
                echo "{success: true,valid: true}";
            }
            else
                echo "{success: false,valid: false, errors:  'Error occured while saving data' }";
        }

        public function getDetais($id) {
            global $db;
            if (!empty($id))
            {
                $db->_loadRecordJson("SELECT * from " . FINASCOP_DB . "finascop_company WHERE comp_id = " . $id, true);
            }
        }

        public function listCompany($postData) {
            global $db;
            $rec_limit = empty($postData['limit']) ? 16 : $postData['limit'];
            $rec_start = empty($postData['start']) ? 0 : $postData['start'];
            $rec_sort = empty($postData['sort']) ? 'comp_name' : $postData['sort'];
            $rec_sort_dir = empty($postData['dir']) ? 'ASC' : $postData['dir'];

            $filter_part = ' 1=1';

            if (isset($postData['filter']))
            {

                foreach ($postData['filter'] as $key => $val)
                {
                    $filter_part .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
                }
            }

            $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "`finascop_company`";
            $listQuery = "SELECT comp_id, comp_name, comp_shortname, cmp_Typ, cmp_PAN, comp_Ph, comp_Fax, cmp_status, comp_ReferenceId, comp_gstno, comp_fssaino, comp_dlno1 "
                    . "from " . FINASCOP_DB . "`finascop_company` WHERE {$filter_part} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
            $db->printGridJson($countQuery, $listQuery);
        }

        public static function saveCompany($data, $IsAddNew, $IsFinascopIntegration = true) {
            global $db;
            unset($data['apikey']);
            unset($data['tstamp']);

            if (!array_key_exists("comp_name", $data))
            {
                echo "{success:false,errors: { msg: 'FINASCOP: Company name parameter missing' }}";
                exit();
            }

            if (!array_key_exists("comp_shortname", $data))
            {
                echo "{success:false,errors: { msg: 'FINASCOP: Company short name parameter missing' }}";
                exit();
            }
            if ($IsFinascopIntegration)
            {
                if ($data['comp_id'] == 0)
                {
                    echo "{success:false,errors: { msg: 'FINASCOP: Company Id is missing. ' }}";
                    exit();
                }
                $tmpData = $data;
                $data = array();
                $data['comp_id'] = $tmpData['comp_id'];
                $data['comp_name'] = $tmpData['comp_name'];
                $data['comp_shortname'] = $tmpData['comp_shortname'];
            }
            $company_in_db_qry = "SELECT COUNT(*) from " . FINASCOP_DB . "finascop_company "
                    . "WHERE comp_name = '{$data['comp_name']}'";
            $company_short_in_db_qry = "SELECT COUNT(*) from " . FINASCOP_DB . "finascop_company "
                    . "WHERE comp_shortname = '{$data['comp_shortname']}'";


            if (intval($data['comp_id']) > 0 && $IsAddNew == false)
            {
                $company_in_db_qry .= " AND comp_id != {$data['comp_id']}";
                $company_short_in_db_qry .= " AND comp_id != {$data['comp_id']}";
            }

            $company_in_db = $db->getItemFromDB($company_in_db_qry);
            if ($company_in_db > 0)
            {
                echo "{errors: { reason: '" . COMPANY_EXISTS . "' }}";
                exit;
            }
            else
            {
                $company_short_in_db = $db->getItemFromDB($company_short_in_db_qry);
                if ($company_short_in_db > 0)
                {
                    echo "{errors: { reason: '" . COMPANY_SHRT_NAME_EXISTS . "' }}";
                    exit;
                }
            }
            if (!isset($data['auditing_company']))
            {
                if (intval($data['comp_id']) > 0)
                {
                    $compcount = $db->getItemFromDB("SELECT count(*) from " . FINASCOP_DB . "finascop_company WHERE auditing_company = 'Yes' and comp_id = " . $data['comp_id'], true);
                }
                if ($compcount > 0)
                {
                    $sel = $db->getItemFromDB("SELECT count(*) FROM " . FINASCOP_DB . "`finascop_usr_profile` uac where typId = 4 ", true);
                    if ($sel > 0)
                    {
                        echo "{errors: { reason: 'Auditors have been created. Please select Is an Auditing Company' }}";
                        exit;
                    }
                }
                $data['auditing_company'] = 'No';
            }

            if ($data['auditing_company'] == 'Yes')
            {
                $status = $db->perform(FINASCOP_DB . "finascop_company", array("auditing_company" => "No"), "update");
            }
            if ($data['comp_id'] > 0 && $IsAddNew == false)
            {
                $current_code = $db->getItemFromDB("SELECT comp_shortname from " . FINASCOP_DB . "finascop_company WHERE comp_id = {$data['comp_id']}");

                if ($current_code != $data['comp_shortname'])
                {
                    /* branch code changed, update ledger name with the new one */
                    $ledgers = $db->getMultipleData("SELECT accled_Ledger_Id,accled_LedgerName "
                            . "from " . FINASCOP_DB . "finascop_accounts_ledger WHERE accled_CompId = {$data['comp_id']}", true);
                    if (!empty($ledgers))
                    {
                        foreach ($ledgers as $value)
                        {

                            $name_split = explode('_', $value['accled_LedgerName']);

                            $ldg_data = array(
                                'accled_LedgerName' => $name_split[0] . '_' . $data['comp_shortname'] . '_' . $name_split[2]
                            );
                            $db->perform(FINASCOP_DB . "finascop_accounts_ledger", $ldg_data, "update", "accled_Ledger_Id = " . $value['accled_Ledger_Id']);
                        }
                    }
                }
                $data['comp_ReferenceId'] = $db->getItemFromDB("SELECT comp_ReferenceId FROM finascop_company WHERE comp_id ={$data['comp_id']}");
                while ($data['comp_ReferenceId'] == '')
                {
                    $data['comp_ReferenceId'] = getNewFinascopApiKey();
                }
                $status = $db->perform(FINASCOP_DB . "finascop_company", $data, "update", "comp_id={$data['comp_id']}");

                $cmp = $data['comp_id'];
            }
            else
            {
                //unset($data['comp_id']);
                $data['comp_ReferenceId'] = '';
                while ($data['comp_ReferenceId'] == '')
                {
                    $data['comp_ReferenceId'] = getNewFinascopApiKey();
                }
                if (intval($data['comp_id']) == 0)
                {
                    $data['comp_id'] = $db->getItemFromDB("SELECT coalesce(max(comp_id),0)+1 as maxid from " . FINASCOP_DB . "finascop_company ");
                }
                $status = $db->perform(FINASCOP_DB . "finascop_company", $data);
                //$cmp = $db->insert_id();					
                $rd = $db->getMultipleData("SELECT ledgertypedefaultid,ledgertypedefaultname,Group_ID,GroupName from " . FINASCOP_DB . "`finascop_accounts_ledgertype_default` ", true);
                foreach ($rd as $k => $value)
                {
                    $maxledgerid = $db->getItemFromDB("SELECT coalesce(max(ledgertypeid),0)+1 as id  from " . FINASCOP_DB . "finascop_accounts_ledgertype; ", true);
                    $leddata = array('ledgertypeid' => $maxledgerid, 'ledgertypename' => $value['ledgertypedefaultname'], 'Group_ID' => $value['Group_ID'], 'GroupName' => $value['GroupName'], 'isCommon' => 0, 'isSystem' => 1, 'ledgercompid' => $data['comp_id'], 'ledgertypedefaultid' => $value['ledgertypedefaultid']);
                    $status = $db->perform(FINASCOP_DB . "finascop_accounts_ledgertype", $leddata);
                }
            }


            return $status;
        }

    }

    class Branch {

        public function listBranch($data) {
            global $db;
            $rec_limit = empty($data['limit']) ? 16 : $data['limit'];
            $rec_start = empty($data['start']) ? 0 : $data['start'];
            $rec_sort = empty($data['sort']) ? 'br_Name' : $data['sort'];
            $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
            $filter_part = ' 1=1';

            if (isset($data['filter']))
            {

                foreach ($data['filter'] as $key => $val)
                {
                    $filter_part .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
                }
            }

            $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . "finascop_branch";
            $listQuery = "SELECT br_ID,br_Name,branch_shortname,"
                    . "(SELECT dst_Name FROM " . FINASCOP_DB . "finascop_district WHERE dst_Id = br_District) as br_District,"
                    . "(SELECT st_name FROM " . FINASCOP_DB . "finascop_state WHERE st_ID = br_State) as br_State,"
                    . "(SELECT comp_name from " . FINASCOP_DB . "finascop_company WHERE comp_id = "
                    . "(SELECT comp_id from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id = a.br_ID)) as company,"
                    . "(SELECT comp_id from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id = a.br_ID) as comp_id,"
                    . "br_Address,br_Fax,br_Email,br_Phone,br_Incharge,br_status,br_pincode,br_Lat,br_Lng "
                    . "from " . FINASCOP_DB . "finascop_branch a WHERE {$filter_part} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
            $db->printGridJson($countQuery, $listQuery);
        }

         public function saveBranchGroup($inputdata,&$return_rec) {
                   // print_r($inputdata);
        global $db;
        $db->query('begin');
        $data = array(
            "store_group_id" => $inputdata['id'],
            "store_group_name" => $inputdata['name'],
            "status" => $inputdata['status']
        );
        $store_group_id = $data['store_group_id'];
        $store_group_name = $data['store_group_name'];
        $status = $data['status'];
        $userid = intval($_SESSION['admin']->Finascop_UserId);
        $store_group_name = addslashes($store_group_name);

        if ($data['store_group_id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;


            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from finascop_branch_group WHERE store_group_name ='{$store_group_name}' AND store_group_id!='{$store_group_id}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'This Store Group already existing.'}";
                exit;
            } else {
                $status = $db->perform("finascop_branch_group", $data, 'update', 'store_group_id =' . $data['store_group_id']);
                $lastId = $data['store_group_id'];
            }
        } else {

            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from finascop_branch_group WHERE store_group_name ='{$store_group_name}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'This Store Group already existing.'}";
                exit;
            } else {
                unset($data['store_group_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('finascop_branch_group', $data);
                $lastId = $db->insert_id();
            }
        }
        if ($lastId > 0) {
            //print_r($data);
            //print_r($data['br_businessType']);
            if (!empty($inputdata['store_group_primary_businessType']) || !empty($inputdata['store_group_additional_businessType'])) {
                $store_group_primary_businessType = $inputdata['store_group_primary_businessType'];
                $store_group_businessTypes = explode(',', $inputdata['store_group_additional_businessType']);
                $status = $db->query("DELETE FROM finascop_branch_group_business_type WHERE store_group_id = {$lastId}");
                $primbt['business_type_id'] = $store_group_primary_businessType;
                $primbt['is_primary'] = 1;
                $primbt['store_group_id'] = $lastId;
                $status = $db->perform(FINASCOP_DB . "finascop_branch_group_business_type", $primbt);
                //print_r($br_businessTypes);
                if ($store_group_businessTypes[0] > 0) {
                    for ($st = 0; $st < count($store_group_businessTypes); $st++) {
                        $strgrp['business_type_id'] = $store_group_businessTypes[$st];
                        $strgrp['store_group_id'] = $lastId;
                        $strgrp['is_primary'] = 0;
                        $status = $db->perform(FINASCOP_DB . "finascop_branch_group_business_type", $strgrp);
                    }
                }
            }
        }

        $return_rec = $db->getFromDb("SELECT store_group_id,store_group_name,status FROM finascop_branch_group WHERE store_group_id = {$lastId}", true);
        $status = $db->query('commit');
        return $status;

       
        }
        public function saveBranch($data, $IsAddNew, $IsFinascopIntegration = true, &$return_brid = 0) {
            global $db;
            unset($data['apikey']);
            unset($data['tstamp']);
            if (!array_key_exists("br_Company", $data))
            {
                echo "{success:false,errors: { msg: 'FINASCOP: Branch's company param missing. ' }}";
                exit();
            }
            if (!array_key_exists("br_defaultapibranch", $data))
            {
                echo "{success:false,errors: { msg: 'FINASCOP: Branch's default API branch param missing. ' }}";
                exit();
            }
            if (!array_key_exists("br_Name", $data))
            {
                echo "{success:false,errors: { msg: 'FINASCOP: Branch's name param missing. ' }}";
                exit();
            }
            if (!array_key_exists("branch_shortname", $data))
            {
                echo "{success:false,errors: { msg: 'FINASCOP: Branch's short name param missing. ' }}";
                exit();
            }
            if($data['br_PyramidLevel'] == 1){
                $data['br_cpd'] = 0;
            }
            if ($IsFinascopIntegration)
            {
                if (intval($data['br_ID']) == 0)
                {
                    echo "{success:false,errors: { msg: 'FINASCOP: Branch Id is missing. ' }}";
                    exit();
                }
                $tmpData = $data;
                $data = array();
                $data['br_ID'] = $tmpData['br_ID'];
                $data['br_Company'] = $tmpData['br_Company'];
                $data['br_defaultapibranch'] = $tmpData['br_defaultapibranch'];
                $data['br_Name'] = $tmpData['br_Name'];
                $data['branch_shortname'] = $tmpData['branch_shortname'];
            }
            else
            {
                if ($IsAddNew)
                {
                    unset($data['br_ID']);
                }
            }


            $company = $data['br_Company'];
            $defaultapibranch = $data['br_defaultapibranch'];
            $branch_in_db_qry = "SELECT COUNT(*) from " . FINASCOP_DB . "finascop_branch inner join " . FINASCOP_DB . "finascop_branch_company using (br_id) "
                    . "WHERE br_Name = '{$data['br_Name']}' AND comp_id ='{$company}'  ";
            $branch_shortname_in_db_qry = "SELECT COUNT(*) from " . FINASCOP_DB . "finascop_branch inner join " . FINASCOP_DB . "finascop_branch_company using (br_id) "
                    . "WHERE branch_shortname = '{$data['branch_shortname']}' AND comp_id ='{$company}' ";


            unset($data['br_Company']);
            unset($data['br_defaultapibranch']);
            unset($data['br_defaultapi']);

            if ($data['br_ID'] > 0 && $IsAddNew == false)
            {
                $branch_in_db_qry .= " AND br_ID <> {$data['br_ID']}";
                $branch_shortname_in_db_qry .= " AND br_ID <> {$data['br_ID']}";
            }

            $branch_in_db = $db->getItemFromDB($branch_in_db_qry);
            if ($branch_in_db > 0)
            {
                echo "{errors: { reason: '" . BRANCH_EXISTS . "' }}";
                exit;
            }
            else
            {
                $branch_shortname_in_db = $db->getItemFromDB($branch_shortname_in_db_qry);
                if ($branch_shortname_in_db > 0)
                {
                    echo "{errors: { reason: '" . BRANCH_SHRT_NAME_EXISTS . "' }}";
                    exit;
                }
            }


            if ($data['br_ID'] > 0 && $IsAddNew == false)
            {
                $return_brid = $data['br_ID'];
                $current_code = $db->getItemFromDB("SELECT branch_shortname from " . FINASCOP_DB . "finascop_branch WHERE br_ID = {$data['br_ID']}");

                if ($current_code != $data['branch_shortname'])
                {
                    /* branch code changed, update ledger name with the new one */
                    $ledgers = $db->getMultipleData("SELECT accled_Ledger_Id,accled_LedgerName "
                            . "from " . FINASCOP_DB . "finascop_accounts_ledger WHERE accled_BranchId = {$data['br_ID']}", true);
                    if (!empty($ledgers))
                    {
                        foreach ($ledgers as $value)
                        {
                            $name_split = explode('_', $value['accled_LedgerName']);

                            $ldg_data = array(
                                'accled_LedgerName' => $name_split[0] . '_' . $name_split[1] . '_' . $data['branch_shortname']
                            );
                            $db->perform(FINASCOP_DB . "finascop_accounts_ledger", $ldg_data, "update", "accled_Ledger_Id = " . $value['accled_Ledger_Id']);
                        }
                    }
                }
                $data['br_ReferenceID'] = $db->getItemFromDB("SELECT br_ReferenceId FROM finascop_branch WHERE br_ID = {$data['br_ID']}");
                while ($data['br_ReferenceID'] == '')
                {
                    $data['br_ReferenceID'] = getNewFinascopApiKey();
                }
                $status = $db->perform(FINASCOP_DB . "finascop_branch", $data, "update", "br_ID={$data['br_ID'] }");
                $db->query("DELETE from " . FINASCOP_DB . "`finascop_branch_company` WHERE `br_Id` = {$data['br_ID']}");
                if ($data['br_ID'] > 0)
                {
                    $cmp_br = array('br_Id' => $data['br_ID'], 'comp_id' => $company);
                    $status = $db->perform(FINASCOP_DB . "finascop_branch_company", $cmp_br);
                }
            }
            else
            {
                $brKeyCount = $db->getItemFromDB("SELECT MAX(br_key) FROM finascop_branch");
                if($brKeyCount == 0){
                    $data['br_key'] = 1000;
                }else{
                    $data['br_key'] = (int)$brKeyCount + 1;
                }
                $data['br_ReferenceID'] = '';
                while ($data['br_ReferenceID'] == '')
                {
                    $data['br_ReferenceID'] = getNewFinascopApiKey();
                }
                $status = $db->perform(FINASCOP_DB . "finascop_branch", $data);
                $data['br_ID'] = $db->insert_id();
                $return_brid = $data['br_ID'];
                /* add a ledger of type cash */


                $ldg_company = $db->getItemFromDB("SELECT comp_shortname from " . FINASCOP_DB . "finascop_company WHERE comp_id = {$company}");
                $ldg_branch = $db->getItemFromDB("select branch_shortname from " . FINASCOP_DB . "finascop_branch where br_ID = {$data['br_ID']}");
                $rd = $db->getMultipleData("SELECT ledgertypedefaultid,ledgertypedefaultname,Group_ID,GroupName from " . FINASCOP_DB . "`finascop_accounts_ledgertype_default` ", true);
                foreach ($rd as $k => $value)
                {
                    $ledgertypedefaultid = $value['ledgertypedefaultid'];
                    $accLedgerTypeId = $db->getItemFromDB("SELECT ledgertypeid from " . FINASCOP_DB . "finascop_accounts_ledgertype where ledgertypedefaultid = {$ledgertypedefaultid} and ledgercompid = {$company}");
                    if (intval($accLedgerTypeId) == 0)
                    {
                        echo "{success:false,errors: 'FINASCOP: Missing Ledger type details for the company.' }";
                        exit();
                    }
                    $accLedgerId = $db->getItemFromDB("SELECT IF(MAX(accled_Ledger_Id) IS NULL,1,MAX(accled_Ledger_Id)+1) from " . FINASCOP_DB . "finascop_accounts_ledger");
                    $WalleRefId = getNewFinascopApiKey();
                    $ledger = array(
                        'accled_Ledger_Id' => $accLedgerId,
                        'accled_LedgerName' => $value['ledgertypedefaultname'] . '_' . $ldg_company . '_' . $ldg_branch,
                        'ledgertypeid' => $accLedgerTypeId,
                        'ledgertypename' => $value['ledgertypedefaultname'],
                        'Group_ID' => $value['Group_ID'],
                        'GroupName' => $value['GroupName'],
                        'accled_system' => 1,
                        'accled_IsEnabled' => 1,
                        'accled_IsLocal' => 0,
                        'accled_ReferenceId' => $WalleRefId,
                        'accled_RefIdCRC32' => crc32($WalleRefId),
                        'accled_BranchId' => $data['br_ID'],
                        'accled_CompId' => $company,
                        'accled_IsVendor' => 0
                    );
                    $db->perform(FINASCOP_DB . "finascop_accounts_ledger", $ledger);
                }


                if ($data['br_ID'] > 0)
                {
                    $cmp_br = array('br_Id' => $data['br_ID'], 'comp_id' => $company);
                    $status = $db->perform(FINASCOP_DB . "finascop_branch_company", $cmp_br);
                }
            }

            if ($data['br_ID'] > 0 and $defaultapibranch > 0)
            {
                $cmp = array('cmp_DefaultBranch' => $data['br_ID']);
                $db->perform(FINASCOP_DB . "finascop_company", $cmp, "update", "comp_id = $company");
            }

            return $status;
        }

        public function getDetails($id) {
            global $db;
            if (!empty($id))
            {
                $db->_loadRecordJson("SELECT br_ID,br_Name, br_District, br_State, branch_shortname,              
					(SELECT comp_id from " . FINASCOP_DB . "finascop_branch_company WHERE br_Id = a.br_ID) AS br_Company,
					(SELECT if(cmp_DefaultBranch={$id},1,0) from " . FINASCOP_DB . "finascop_company inner join 
					" . FINASCOP_DB . "finascop_branch_company using(comp_id) WHERE br_Id = a.br_ID) AS br_defaultapi,
					br_Address,br_Fax,br_Email,br_Phone,br_Incharge,br_pincode,br_Lat,br_Lng
					from " . FINASCOP_DB . "finascop_branch a WHERE br_ID = " . $id, true);
            }
        }

        public function changeStatus($br_ID, $br_status, $comp_id) {
            global $db;
            $brid = $br_ID;
            $st = ($br_status == 'Active') ? 'Inactive' : 'Active';
            $up = array('br_status' => $st);
            $status = $db->perform(FINASCOP_DB . "finascop_branch", $up, "update", "br_ID={$brid}");
            if ($st == 'Inactive')
            {
                $cmpdefaultbaranch = array('cmp_DefaultBranch' => 0);
                $cond = " comp_id=" . $comp_id . " and cmp_DefaultBranch = " . $br_ID;
                $db->perform(FINASCOP_DB . "finascop_company", $cmpdefaultbaranch, "update", $cond);
            }

            if ($status)
            {
                echo "{success: true}";
            }
            else
                echo "{errors: { reason: 'Error occured while saving data' }}";
        }

        public function getComboStore($ind, $state) {
             global $db;
                $defaultCountry = $db->getItemFromDB("SELECT country_id FROM retaline_country WHERE is_default = 1");
            switch ($ind)
            {
                case 1:
                    $qry = "SELECT st_ID AS id, st_name AS `name` FROM " . FINASCOP_DB . "finascop_state WHERE cnt_ID = {$defaultCountry}";
                    break;
                case 2:
                    $qry = "SELECT dst_Id AS id, dst_Name AS `name` FROM " . FINASCOP_DB . "finascop_district WHERE st_Id = {$state}";
                    break;
                case 3:
                    $qry = "SELECT comp_id AS id, comp_name AS `name` from " . FINASCOP_DB . "finascop_company where cmp_status= 'Active' ";
                    break;
            }
            $qry .= " ORDER BY `name` ASC";

            finascop_getjsonkeyarray($qry);
        }

    }

}
