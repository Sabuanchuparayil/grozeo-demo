<?php

namespace finascop\accounts\Transactions;

require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/includes/config.php');
require_once(INCLUDE_PATH . "/lib.php");

//SourceOfEntry - 0-InHouse(Via Accounts Interface) 1-Wallet
class AccountingVouchers {
    /*
      Data Entry 'doc_type' numbers and what They Mean
      1 - Cash Receipt
      2 - Cash Payment
      3 - Bank Receipt
      4 - Bank Payment
      5 - Journal Voucher
      6 - Contra Entry
      Purchase 'doc_type' numbers and what They Mean
      7 - Purchase Invoice
      8 - Purchase Returnable
      9 - Purchase Return

     */
    /*
      Data Entry 'Type' text and what They Mean
      1 - Receipt
      2 - Payment
      3 - Receipt
      4 - Payment
      5 - Journal Voucher
      6 - Contra Entry
     */

    private function generateNextDocNo($doc_type, $EnteredByBranchID) {
        global $db;

        $success = $db->query("INSERT INTO " . FINASCOP_DB . "finascop_doc_number_ref"
                . "(dore_DocType,br_id) "
                . "VALUES ({$doc_type},{$EnteredByBranchID}) ON DUPLICATE KEY UPDATE dore_DocType = {$doc_type}, br_id = {$EnteredByBranchID}");

        $con = "dore_DocType = {$doc_type}  AND br_id = {$EnteredByBranchID}";
        $DocRec = $db->getFromDB("SELECT COALESCE(dore_lastDocNo,0) as dore_lastDocNo,COALESCE(dore_DocCode,dore_DocType) as dore_DocCode "
                . "FROM " . FINASCOP_DB . "finascop_doc_number_ref WHERE {$con}", true);
        $prefix = '';
        $dore_DocCodeLen = strlen($DocRec['dore_DocCode']);
        $prefix = $dore_DocCodeLen > 2 ? substr($DocRec['dore_DocCode'], -2) : $DocRec['dore_DocCode'];

        $lastDocNo = $DocRec['dore_lastDocNo'];

        $docNumber = intval($lastDocNo) + 1;

        $newDocNo = $prefix . $docNumber;

        $success = $db->query("REPLACE INTO " . FINASCOP_DB . "finascop_doc_number_ref"
                . "(dore_DocType,dore_DocCode,br_id,dore_lastDocNo) "
                . "VALUES ({$doc_type},'{$prefix}',{$EnteredByBranchID},'{$docNumber}')");

        if (!$success) {
            return '{"success" : false, "msg":"FINASCOP: Cannot update last Document Number in database."}';
            exit;
        }

        return $newDocNo;
    }

    private function getLedgerBranchId($ledid) {
        global $db;
        $brid = $db->getItemFromDB("SELECT accled_BranchId FROM " . FINASCOP_DB . "finascop_accounts_ledger WHERE accled_Ledger_Id = {$ledid}");
        return $brid;
    }

    private function getLedgerCompId($ledid) {
        global $db;
        $brid = $db->getItemFromDB("SELECT accled_CompId FROM " . FINASCOP_DB . "finascop_accounts_ledger WHERE accled_Ledger_Id = {$ledid}");
        return $brid;
    }

    private function updateLedgerBalance($acet_NO, $add_amount) {
        global $db;
        $query = "SELECT actr_amount, actr_IsDebtor, actr_IsNegative, ledg_Id FROM " . FINASCOP_DB . "finascop_accounts_transaction WHERE acet_NO = '{$acet_NO}'";
        $rd = $db->getMultipleData($query, true);
        foreach ($rd as $k => $value) {
            $amount = ($value['actr_IsNegative'] == 1) ? -$value['actr_amount'] : $value['actr_amount'];
            if ($value['actr_IsDebtor'] == 0) {
                if ($add_amount == 1) {
                    $qry = "UPDATE " . FINASCOP_DB . "finascop_accounts_ledger as al SET accled_Credits = accled_Credits + {$amount}  WHERE al.accled_Ledger_Id = {$value['ledg_Id']}";
                } elseif ($add_amount == 0) {
                    $qry = "UPDATE " . FINASCOP_DB . "finascop_accounts_ledger as al SET accled_Credits = accled_Credits - {$amount}  WHERE al.accled_Ledger_Id = {$value['ledg_Id']}";
                }
                $db->query($qry);
            } elseif ($value['actr_IsDebtor'] == 1) {
                if ($add_amount == 1) {
                    $qry = "UPDATE " . FINASCOP_DB . "finascop_accounts_ledger as al SET accled_Debits = accled_Debits + {$amount}  WHERE al.accled_Ledger_Id = {$value['ledg_Id']}";
                } elseif ($add_amount == 0) {
                    $qry = "UPDATE " . FINASCOP_DB . "finascop_accounts_ledger as al SET accled_Debits = accled_Debits - {$amount}  WHERE al.accled_Ledger_Id = {$value['ledg_Id']}";
                }
                $db->query($qry);
            }
        }

        return;
    }

    public function saveLedgers($ledger, $ldg_company_id, $grid_data, $apikey = '') {
        global $db;
        $ledger['isCommon'] = 0;


        $ldg_company = $db->getItemFromDB("SELECT comp_shortname FROM " . FINASCOP_DB . "finascop_company WHERE comp_id = {$ldg_company_id}");


        if ($ledger['ledgertypeid'] > 0) {
            $d_c = " AND ledgertypeid <> {$ledger['ledgertypeid']}";
        }

        /* check duplicate ledger name for the company */
        $qry = "SELECT COUNT(1) FROM " . FINASCOP_DB . "`finascop_accounts_ledger` "
                . "WHERE ledgertypename = '{$ledger['ledgertypename']}' AND accled_CompId = {$ldg_company_id} {$d_c} ";
        $dup = $db->getItemFromDB($qry);

        if ($dup > 0) {
            return '{"success" : false, "valid":false,"msg":"FINASCOP: Duplicate entry for ledger."}';
            exit;
        }




        if ($ledger['ledgertypeid'] == 0) {

            $ledgerId = $db->getItemFromDB("SELECT IF(MAX(ledgertypeid) IS NULL,1,MAX(ledgertypeid)+1) FROM " . FINASCOP_DB . "finascop_accounts_ledgertype");
            //$ledgerId = ($ledgerId < 1000) ? 1000 : $ledgerId;
            $ledger['ledgertypeid'] = $ledgerId;
            $ledger['GroupName'] = $db->getItemFromDB("SELECT GroupName FROM " . FINASCOP_DB . "finascop_accounts_groups WHERE Group_ID = {$ledger['Group_ID']}");
            $ledger['isSystem'] = 0;
            $ledger['isApiCreated'] = empty($ledger['isApiCreated']) ? 0 : $ledger['isApiCreated'];
            $ledger['ledgercompid'] = $ldg_company_id;

            $db->perform(FINASCOP_DB . "finascop_accounts_ledgertype", $ledger);



            foreach ($grid_data as $val) {
                $accLedgerId = $db->getItemFromDB("SELECT IF(MAX(accled_Ledger_Id) IS NULL,1,MAX(accled_Ledger_Id)+1) FROM " . FINASCOP_DB . "finascop_accounts_ledger");
                $ldg_branch = $db->getItemFromDB("select branch_shortname from " . FINASCOP_DB . "finascop_branch where br_ID = {$val['br_ID']}");
                $walleapikey ='';
                while($walleapikey==''){
                $walleapikey = ($apikey == '' ? getNewFinascopApiKey() : $apikey);
                }
                $data = array(
                    'accled_Ledger_Id' => $accLedgerId,
                    'accled_LedgerName' => $ledger['ledgertypename'] . '_' . $ldg_company . '_' . $ldg_branch,
                    'ledgertypeid' => $ledger['ledgertypeid'],
                    'ledgertypename' => $ledger['ledgertypename'],
                    'Group_ID' => $ledger['Group_ID'],
                    'GroupName' => $db->getItemFromDB("SELECT GroupName FROM " . FINASCOP_DB . "finascop_accounts_groups WHERE Group_ID = {$ledger['Group_ID']}"),
                    'accled_system' => 0,
                    'accled_IsEnabled' => 1,
                    'accled_IsLocal' => 0,
                    'accled_BranchId' => $val['br_ID'],
                    'accled_CompId' => $ldg_company_id,
                    'accled_IsVendor' => 0,
                    'accled_ReferenceId' => $walleapikey,
                    'accled_RefIdCRC32' => crc32($walleapikey),
                    'isApiCreated' => empty($ledger['isApiCreated']) ? 0 : $ledger['isApiCreated']
                );
                $apikey = '';
                $db->perform(FINASCOP_DB . "finascop_accounts_ledger", $data);
            }
        } else {

            $ledgerId = $ledger['ledgertypeid'];
            $ledger['GroupName'] = $db->getItemFromDB("SELECT GroupName FROM " . FINASCOP_DB . "finascop_accounts_groups WHERE Group_ID = {$ledger['Group_ID']}");
            $ledger['isApiCreated'] = empty($ledger['isApiCreated']) ? 0 : $ledger['isApiCreated'];
            $db->perform(FINASCOP_DB . "finascop_accounts_ledgertype", $ledger, "update", "ledgertypeid = " . $ledgerId);

            $db->perform(FINASCOP_DB . "finascop_accounts_ledger", array("ledgertypename" => $ledger['ledgertypename']), "update", "ledgertypeid = " . $ledgerId);

            $user_current_selected_branches = array();



            foreach ($grid_data as $key => $val) {

                array_push($user_current_selected_branches, $val['br_ID']);

                $accLedgerId = $db->getItemFromDB("SELECT accled_Ledger_Id FROM " . FINASCOP_DB . "finascop_accounts_ledger 
					WHERE accled_BranchId = '" . $val['br_ID'] . "' AND ledgertypeid = '" . $ledgerId . "' ");
                $ldg_branch = $db->getItemFromDB("select branch_shortname from " . FINASCOP_DB . "finascop_branch where br_ID = {$val['br_ID']}");

                if (intval($accLedgerId) > 0) {

                    $data = array(
                        'accled_LedgerName' => $ledger['ledgertypename'] . '_' . $ldg_company . '_' . $ldg_branch,
                        'ledgertypename' => $ledger['ledgertypename'],
                        'Group_ID' => $ledger['Group_ID'],
                        'GroupName' => $db->getItemFromDB("SELECT GroupName FROM " . FINASCOP_DB . "finascop_accounts_groups WHERE Group_ID = {$ledger['Group_ID']}"),
                        'accled_BranchId' => $val['br_ID'],
                        'accled_IsEnabled' => 1,
                        'accled_CompId' => $ldg_company_id,
                        'isApiCreated' => empty($ledger['isApiCreated']) ? 0 : $ledger['isApiCreated']        
                    );
                    $db->perform(FINASCOP_DB . "finascop_accounts_ledger", $data, "update", "accled_Ledger_Id = " . $accLedgerId);
                } else {
                    $accLedgerId = $accLedgerId = $db->getItemFromDB("SELECT IF(MAX(accled_Ledger_Id) IS NULL,1,MAX(accled_Ledger_Id)+1) FROM " . FINASCOP_DB . "finascop_accounts_ledger");
                    $walleapikey = getNewFinascopApiKey();
                    $data = array(
                        'accled_Ledger_Id' => $accLedgerId,
                        'accled_LedgerName' => $ledger['ledgertypename'] . '_' . $ldg_company . '_' . $ldg_branch,
                        'ledgertypeid' => $ledger['ledgertypeid'],
                        'ledgertypename' => $ledger['ledgertypename'],
                        'Group_ID' => $ledger['Group_ID'],
                        'GroupName' => $db->getItemFromDB("SELECT GroupName FROM " . FINASCOP_DB . "finascop_accounts_groups WHERE Group_ID = {$ledger['Group_ID']}"),
                        'accled_system' => 0,
                        'accled_IsEnabled' => 1,
                        'accled_IsLocal' => 0,
                        'accled_BranchId' => $val['br_ID'],
                        'accled_CompId' => $ldg_company_id,
                        'accled_IsVendor' => 0,
                        'accled_ReferenceId' => $walleapikey,
                        'accled_RefIdCRC32' => crc32($walleapikey),
                        'isApiCreated' => empty($ledger['isApiCreated']) ? 0 : $ledger['isApiCreated']        
                    );
                    $db->perform(FINASCOP_DB . "finascop_accounts_ledger", $data);
                }
            }
            if (!empty($user_current_selected_branches)) {
                $qry = "SELECT group_concat(br_ID) FROM  " . FINASCOP_DB . "finascop_branch"
                        . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id= {$ldg_company_id}) ";
                $not_admin = " AND accled_BranchId IN (" . $db->getItemFromDB($qry) . ")";

                $br = implode(',', $user_current_selected_branches);

                //$disable_all = array('accled_IsEnabled' => 0,
                //'GroupName' => $db->getItemFromDB("SELECT GroupName FROM accounts_groups WHERE Group_ID = {$ledger['Group_ID']}"));
                $GroupName = $db->getItemFromDB("SELECT GroupName FROM  " . FINASCOP_DB . "finascop_accounts_groups WHERE Group_ID = {$ledger['Group_ID']}");
                //$db->perform("accounts_ledger", $disable_all, "update", "ledgertypeid = "	. $ledgerId . " AND accled_BranchId NOT IN ({$br})  {$not_admin}");
                $query = "update  " . FINASCOP_DB . "finascop_accounts_ledger as a inner join " . FINASCOP_DB . "finascop_branch as b on  a.accled_BranchId = b.br_id  set a.accled_IsEnabled =0, a.GroupName = '" . $GroupName . "', a.accled_LedgerName = concat('" . $ledger['ledgertypename'] . "','_','" . $ldg_company . "','_', b.branch_shortname) where accled_BranchId NOT IN ({$br}) and ledgertypeid= $ledgerId ";
                $db->query($query);
            }
        }


        return '{"success" : true, "valid": true}';
    }

    public function saveParticularData($data, $sessiondets, $editable = 0, $autoapprove = 0, $QueueForRecon = false, $hascrossbranch = false) {
        global $db;


        if (strtotime(($data['receipt_date'] == '' ? date('Y-m-d') : $data['receipt_date'])) > strtotime(date('Y-m-d'))) {
            echo '{"success":false,"msg":"FINASCOP: Please check the date, you cannot save a post dated entry "}';
            exit();
        }

        $acc_ref = $data['acet_NO'];

        if (!empty($acc_ref)) {

            $updated_on = $data['updated_on'];
            $currentStatus = $db->getFromDB("SELECT updated_on,acet_SourceOfEntry FROM " . FINASCOP_DB . "finascop_accounts_entry WHERE acet_NO = '{$acc_ref}'", true);
            if ($updated_on != $currentStatus['updated_on']) {
                echo '{"success":false,"msg":"FINASCOP: Please refresh the entries. This entry has been updated."}';
                exit();
            } elseif ($currentStatus['acet_SourceOfEntry'] != 0) {
                echo '{"success":false,"msg":"FINASCOP: Automated entries cannot be edited"}';
                exit();
            }
        } else {

            $status = $db->getItemFromDB('select cmp_status from ' . FINASCOP_DB . 'finascop_company where comp_id = ' . $sessiondets->company_id);
            if ($status == 'Inactive') {
                echo '{"success":false,"msg":"FINASCOP: You can not add entries for this company ' . $sessiondets->company . '  has been deactivated "}';
                exit();
            }
        }
        $EntryOfBranch = $this->getLedgerBranchId($data['receipt_account']);
        $EntryOfCompany = $this->getLedgerCompId($data['receipt_account']);

        if ($sessiondets->branch_id != $EntryOfBranch && $hascrossbranch === false) {
            echo '{"success":false,"msg":"FINASCOP: Session branch and account branch does not match"}';
            exit();
        }
        if ($sessiondets->company_id != $EntryOfCompany) {
            echo '{"success":false,"msg":"FINASCOP: Session company and account company does not match"}';
            exit();
        }
        if (intval($EntryOfBranch) == 0) {
            echo '{"success":false,"msg":"FINASCOP: Invalid/blank account id specified "}';
            exit();
        }
        $grid_data = json_decode($data['particular_data'], true);
        $uniqtypeids = array($data['receipt_account']);
        $total = 0;
        if (intval($data['ledger_type']) == 0) {
            echo "{success : false, msg : 'FINASCOP: Invalid ledger type'}";
            exit;
        }

        foreach ($grid_data as $key => $val) {
            if (in_array($val['particular_id'], $uniqtypeids)) {
                file_put_contents('php://stderr', print_r("&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& \n", TRUE));
                file_put_contents('php://stderr', print_r($grid_data, TRUE));
                
//           $fields = array("tempvalue" => $grid_data);
//           $db->perform('temptable',$fields);
           
                echo "{success : false, msg : 'FINASCOP: " . $val['particular_name'] . " has multiple entries, please remove duplicate entry'}";
                exit;
            } else {
                array_push($uniqtypeids, $val['particular_id']);
            }
            $total = $total + $val['amount'];
            if ($this->getLedgerBranchId($val['particular_id']) != $EntryOfBranch && $hascrossbranch === false) {
                echo "{success : false, msg : 'FINASCOP: Unrelated branch/zone ledger specified. All ledgers should be of the same branch '}";
                exit;
            }

            if ($this->getLedgerCompId($val['particular_id']) != $EntryOfCompany) {
                echo '{"success":false,"msg":"FINASCOP: Unrelated company ledger specified. All ledgers should be of the same company and branch"}';
                exit();
            }
        }
        if (bccomp($data['total_amount'], $total, 2) != 0) {
            echo "{success : false, msg : 'FINASCOP: Critical error!. Sum of all Particular entries is not same as  Grand total[" . $data['total_amount'] . " <> " . $total . "]'}";
            exit;
        }


        $acc_dbtr = ($data['type'] == 'Receipt' ? 1 : 0);
        $acc_tr_dbtr = ($data['type'] == 'Receipt' ? 0 : 1);

        if ($data['type'] == 'Journal Voucher') {
            if ($data['ctr_dtr_type'] == 'Debtor') {
                $acc_dbtr = 1;
                $acc_tr_dbtr = 0;
            } else {
                $acc_dbtr = 0;
                $acc_tr_dbtr = 1;
            }
        }

        if ($data['type'] == 'Contra Entry') {
            $acc_dbtr = 1;
            $acc_tr_dbtr = 0;
        }

        $account_entry = array('acet_NO' => $acc_ref,
            'acet_Date' => date('Y-m-d', strtotime($data['receipt_date'])),
            'acet_Narration' => $data['narration'],
            'acet_Amount' => $data['total_amount'],
            'acet_InWords' => finascop_numberToWords($data['total_amount']),
            'acet_TypeId' => $data['ledger_type']);


        if (empty($data['acet_NO'])) {
            $acc_ref = getRandomRef();
            $acc_DocNO = $acc_ref;
            if (defined('FINASCOP_SERIALY_NUMBERED_INVOICE')) {
                if (FINASCOP_SERIALY_NUMBERED_INVOICE === true) {
                    $acc_DocNO = $this->generateNextDocNo($data['ledger_type'], $EntryOfBranch);
                }
            }
        } else {
            $acc_ref = $data['acet_NO'];
            $acc_DocNO = $data['acet_DocNO'];
            $old_data = $db->getFromDB("select comp_id,branch_id,acet_EntryBy,acet_AssignedTo,acet_Status,acet_SourceOfEntry,acet_QueueForRecon,acet_UTRRefno from " . FINASCOP_DB . "finascop_accounts_entry where `acet_NO` = '{$acc_ref}'", true);
            $db->query("DELETE FROM " . FINASCOP_DB . "`finascop_accounts_entry` WHERE `acet_NO` = '{$acc_ref}'");
            $db->query("DELETE FROM " . FINASCOP_DB . "`finascop_accounts_transaction` WHERE `acet_NO` = '{$acc_ref}'");
        }

        $updateon = sha1(microtime(true) . mt_rand(10000, 90000));
        $account_entry['updated_on'] = $updateon;
        $account_entry['updated_by'] = $sessiondets->Finascop_UserId;
        $account_entry['acet_NO'] = $acc_ref;
        $account_entry['acet_DocNO'] = $acc_DocNO;
        $account_entry['acet_DocNOCRC32'] = crc32($acc_DocNO);
        $account_entry['comp_id'] = (empty($old_data['comp_id'])) ? $sessiondets->company_id : $old_data['comp_id'];
        $account_entry['branch_id'] = (empty($old_data['branch_id'])) ? $sessiondets->branch_id : $old_data['branch_id'];
        $account_entry['acet_EntryBy'] = (empty($old_data['acet_EntryBy'])) ? $sessiondets->Finascop_UserId : $old_data['acet_EntryBy'];
        $account_entry['acet_AssignedTo'] = (empty($old_data['acet_AssignedTo']) ? 0 : $old_data['acet_AssignedTo']);
        $account_entry['acet_Status'] = (empty($old_data['acet_Status']) ? 0 : $old_data['acet_Status']);
        $account_entry['acet_SourceOfEntry'] = (empty($old_data['acet_SourceOfEntry']) ? $editable : $old_data['acet_SourceOfEntry']);
        $account_entry['acet_ledg_Id'] = $data['receipt_account'];
        $account_entry['acet_QueueForRecon'] = (empty($old_data['acet_QueueForRecon']) ? ($QueueForRecon == 'true' ? 1 : 0) : $old_data['acet_QueueForRecon']);
        $account_entry['acet_UTRRefno'] = (empty($old_data['acet_UTRRefno']) ? $data['acet_UTRRefno'] : $old_data['acet_UTRRefno']);

        if (!empty($data['location']) && !empty($data['bucket'])) {
            $account_entry['acet_ImageURL'] = $data['location'];
            $account_entry['acet_AWSBucket'] = $data['bucket'];
        }

        $db->perform(FINASCOP_DB . "finascop_accounts_entry", $account_entry);

        /* for account */

        $account_transaction_entry = array(
            'acet_NO' => $acc_ref,
            'acet_TypeId' => $data['ledger_type'],
            'ledg_Id' => $data['receipt_account'],
            'actr_amount' => $data['total_amount'],
            'actr_IsDebtor' => $acc_dbtr,
            'actr_Date' => date('Y-m-d', strtotime($data['receipt_date'])),
            'comp_Id' => (empty($old_data['comp_id'])) ? $sessiondets->company_id : $old_data['comp_id'],
            'actr_IsApproved' => 0,
            'br_ID' => (empty($old_data['branch_id'])) ? $sessiondets->branch_id : $old_data['branch_id'],
            'entry_type' => 'Account');
        $db->perform(FINASCOP_DB . "finascop_accounts_transaction", $account_transaction_entry);

        foreach ($grid_data as $key => $val) {
            $account_transaction_entry = array(
                'acet_NO' => $acc_ref,
                'acet_TypeId' => $data['ledger_type'],
                'ledg_Id' => $val['particular_id'],
                'actr_amount' => abs($val['amount']),
                'actr_IsDebtor' => ($val['amount'] > 0 ? $acc_tr_dbtr : ($acc_tr_dbtr == 1 ? 0 : 1)),
                'actr_IsNegative' => ($val['amount'] > 0 ? 0 : 1),
                'actr_Date' => date('Y-m-d', strtotime($data ['receipt_date'])),
                'comp_Id' => (empty($old_data['comp_id'])) ? $sessiondets->company_id : $old_data['comp_id'],
                'actr_IsApproved' => 0,
                'br_ID' => (empty($old_data['branch_id'])) ? $sessiondets->branch_id : $old_data['branch_id'],
                'entry_type' => 'Particular');
            $db->perform(FINASCOP_DB . "finascop_accounts_transaction", $account_transaction_entry);
        }
        $msg = "";
        if ($autoapprove == 1) {
            $post_data['acet_NO'] = $acc_ref;
            $post_data['updated_on'] = $updateon;
            $post_data['type'] = 3;
            $dataenter = $this->updateStatus($post_data, $sessiondets, 1);
            $dataapprove = json_decode($dataenter);
            $msg = $dataapprove->msg;
        }
        if (empty($data['acet_NO'])) {

            $nodb = new \cgoDynamiteDB();
            $arrSession = array();
            $arrSession['PartitionKey'] = array('col' => 'apikey', 'val' => (string) $data['apikey']);
            $arrSession['SortKey'] = array('col' => 'tstamp', 'val' => (int) $data['tstamp']);
            $arrSession['Data'] = array();
            array_push($arrSession['Data'], array('col' => 'id', 'val' => $acc_ref));
            array_push($arrSession['Data'], array('col' => 'autoapprove', 'val' => (int) $autoapprove));
            $nors = $nodb->perform('ActionLogs', 'update', $arrSession, $response);

            $acet_NO = $acc_ref;
            $acet_type = $data['type'];
            $qry = "SELECT (SELECT comp_name FROM " . FINASCOP_DB . "finascop_company fc WHERE fc.comp_id=fae.comp_id) AS company_name,
				(SELECT br_Name FROM " . FINASCOP_DB . "finascop_branch fb WHERE fb.br_ID=fae.branch_id) AS branch_Name,
				(SELECT br_Address FROM " . FINASCOP_DB . "finascop_branch fb WHERE fb.br_ID=fae.branch_id) AS branch_address,
				acet_DocNO AS receipt_no, acet_Date AS receipt_date,acet_Amount, acet_InWords,acet_Narration, acet_ledg_Id  FROM " . FINASCOP_DB . "finascop_accounts_entry fae WHERE acet_NO= '$acet_NO' ";

            $data = $db->getFromDB($qry, true);
            $query = "SELECT fat.ledg_Id, fat.actr_amount AS amount, fat.actr_IsDebtor, fat.actr_IsNegative,
				(SELECT fal.ledgertypename
				FROM " . FINASCOP_DB . "finascop_accounts_ledger fal
				WHERE fal.accled_Ledger_Id = fat.ledg_Id
				) AS NAME FROM " . FINASCOP_DB . "finascop_accounts_transaction fat WHERE acet_NO = '$acet_NO'";
            $particular = $db->getMultipleData($query, true);
            $returned = SavePDFtoS3($data['company_name'], $data['branch_Name'], $data['branch_address'], $data['receipt_no'], $data['receipt_date'], $acet_type, $data['acet_Amount'], $data['acet_InWords'], $data['acet_Narration'], $data['acet_ledg_Id'], $particular);
            return '{"success" : true,"msg":"' . $msg . '", "RefNo" : "' . $acc_DocNO . '","UpdateOn" : "' . $updateon . '","ObjectUrl" : "' . $returned . '"}';
        } else 
        {

            $acet_NO = $data['acet_NO'];
            $acet_type = $data['type'];
            $qry = "SELECT (SELECT comp_name FROM " . FINASCOP_DB . "finascop_company fc WHERE fc.comp_id=fae.comp_id) AS company_name,
				(SELECT br_Name FROM " . FINASCOP_DB . "finascop_branch fb WHERE fb.br_ID=fae.branch_id) AS branch_Name,
				(SELECT br_Address FROM " . FINASCOP_DB . "finascop_branch fb WHERE fb.br_ID=fae.branch_id) AS branch_address,
				acet_DocNO AS receipt_no, acet_Date AS receipt_date,acet_Amount, acet_InWords,acet_Narration, acet_ledg_Id  FROM " . FINASCOP_DB . "finascop_accounts_entry fae WHERE acet_NO= '$acet_NO' ";

            $data = $db->getFromDB($qry, true);
            $query = "SELECT fat.ledg_Id, fat.actr_amount AS amount, fat.actr_IsDebtor, fat.actr_IsNegative,
				(SELECT fal.ledgertypename
				FROM " . FINASCOP_DB . "finascop_accounts_ledger fal
				WHERE fal.accled_Ledger_Id = fat.ledg_Id
				) AS NAME FROM " . FINASCOP_DB . "finascop_accounts_transaction fat WHERE acet_NO = '$acet_NO'";
            $particular = $db->getMultipleData($query, true);
            $returned = SavePDFtoS3($data['company_name'], $data['branch_Name'], $data['branch_address'], $data['receipt_no'], $data['receipt_date'], $acet_type, $data['acet_Amount'], $data['acet_InWords'], $data['acet_Narration'], $data['acet_ledg_Id'], $particular);
            return '{"success" : true,"msg":"' . $msg . '", "RefNo" : "' . $acc_DocNO . '","UpdateOn" : "' . $updateon . '","ObjectUrl" : "' . $returned . '"}';
        }
    }

    public function saveFileDetails($data, $sessiondets) {
        global $db;

        if (strtotime(($data['receipt_date'] == '' ? date('Y-m-d') : $data['receipt_date'])) > strtotime(date('Y-m-d'))) {
            echo '{"success":false,"msg":"FINASCOP: Please check the date, you cannot save a post dated entry "}';
            exit();
        }
        if (!empty($data['acet_NO'])) {
            $updated_on = $data['updated_on'];
            $currentUpdatedOnDate = $db->getItemFromDB("SELECT updated_on FROM " . FINASCOP_DB . "finascop_accounts_entry WHERE acet_NO = '{$data['acet_NO']}'");
            if ($updated_on != $currentUpdatedOnDate) {
                echo '{"success":false,"msg":"FINASCOP: Please refresh the entries. This entry has been updated."}';
                exit();
            }
        } else {
            $status = $db->getItemFromDB('select cmp_status from ' . FINASCOP_DB . 'finascop_company where comp_id = ' . $sessiondets->company_id);
            if ($status == 'Inactive') {
                echo '{"success":false,"msg":"FINASCOP: You can not add entries for this company ' . $sessiondets->company . '  has been deactivated "}';
                exit();
            }
        }

        if ($data['type'] == 'Receipt')
            $acet_TypeId = 1;
        elseif ($data['type'] == 'Payment')
            $acet_TypeId = 2;
        elseif ($data['type'] == 'Journal Voucher')
            $acet_TypeId = 5;
        else if ($data['type'] == 'Contra Entry')
            $acet_TypeId = 6;

        if (empty($data['acet_NO'])) {
            $acc_ref = getRandomRef();
            $acc_DocNO = $acc_ref;
            $EntryOfBranch = $sessiondets->branch_id;
            if (defined('FINASCOP_SERIALY_NUMBERED_INVOICE')) {
                if (FINASCOP_SERIALY_NUMBERED_INVOICE === true) {
                    $acc_DocNO = $this->generateNextDocNo($acet_TypeId, $EntryOfBranch);
                }
            }
        } else {
            $acc_ref = $data['acet_NO'];
            $acc_DocNO = $data['acet_DocNO'];
            $old_data = $db->getFromDB("select comp_id,branch_id,acet_EntryBy,acet_AssignedTo,acet_Status from " . FINASCOP_DB . "finascop_accounts_entry where `acet_NO` = '{$acc_ref}'", true);
            $db->query("DELETE FROM " . FINASCOP_DB . "`finascop_accounts_entry` WHERE `acet_NO` = '{$acc_ref}'");
            $db->query("DELETE FROM " . FINASCOP_DB . "`finascop_accounts_transaction` WHERE `acet_NO` = '{$acc_ref}'");
        }



        $account_entry = array(
            'acet_NO' => $acc_ref,
            'acet_DocNO' => $acc_DocNO,
            'acet_EntryBy' => $sessiondets->Finascop_UserId,
            'comp_id' => $sessiondets->company_id,
            'acet_TypeId' => $acet_TypeId,
            'acet_ImageURL' => $data['Location'],
            'acet_AWSBucket' => $data['Bucket'],
            'acet_Date' => date('Y-m-d', strtotime(($data['receipt_date'] == '' ? date('Y-m-d') : $data['receipt_date']))),
            'acet_Narration' => $data['narration']);

        $account_entry['updated_on'] = sha1(microtime(true) . mt_rand(10000, 90000));
        $account_entry['updated_by'] = $sessiondets->Finascop_UserId;
        $account_entry['comp_id'] = (empty($old_data['comp_id'])) ? $sessiondets->company_id : $old_data['comp_id'];
        $account_entry['branch_id'] = (empty($old_data['branch_id'])) ? $sessiondets->branch_id : $old_data['branch_id'];
        $account_entry['acet_EntryBy'] = (empty($old_data['acet_EntryBy'])) ? $sessiondets->Finascop_UserId : $old_data['acet_EntryBy'];
        $account_entry['acet_AssignedTo'] = (empty($old_data['acet_AssignedTo']) ? 0 : $old_data['acet_AssignedTo']);
        $account_entry['acet_Status'] = (empty($old_data['acet_Status']) ? 0 : $old_data['acet_Status']);

        $status = $db->perform(FINASCOP_DB . "finascop_accounts_entry", $account_entry);

        if (empty($data['acet_NO'])) {
            $nodb = new \cgoDynamiteDB();
            $arrSession = array();
            $arrSession['PartitionKey'] = array('col' => 'apikey', 'val' => (string) $data['apikey']);
            $arrSession['SortKey'] = array('col' => 'tstamp', 'val' => (int) $data['tstamp']);
            $arrSession['Data'] = array();
            array_push($arrSession['Data'], array('col' => 'id', 'val' => $acc_ref));
            $nors = $nodb->perform('ActionLogs', 'update', $arrSession, $response);
        }
        return '{"success" : true, "RefNo" : "' . $acc_ref . '"}';
    }

    public function updateStatus($post_data, $sessiondets, $autoapproval = 0) {
        global $db;
        $acet_NO = $post_data['acet_NO'];
        $updated_on = $post_data['updated_on'];
        $comments = $post_data['reason'];
        $currentUpdatedOnDate = $db->getFromDB("SELECT updated_on,acet_SourceOfEntry,acet_Status,acet_QueueForRecon "
                . "FROM " . FINASCOP_DB . "finascop_accounts_entry WHERE acet_NO = '{$acet_NO}'", true);
        if ($updated_on != $currentUpdatedOnDate['updated_on']) {
            echo '{"success":false,"msg":"FINASCOP: Please refresh the entries. This entry has been updated."}';
            exit();
        } elseif ($currentUpdatedOnDate['acet_SourceOfEntry'] != 0 && $autoapproval == 0 && $currentUpdatedOnDate['acet_QueueForRecon'] == 0) {
            echo '{"success":false,"msg":"FINASCOP: Automated entries cannot be edited"}';
            exit();
        }
        $current_acet_status = $currentUpdatedOnDate['acet_Status'];
        $type = $post_data['type'];
        $check_audit_permission = 0;

        $updated_on = sha1(microtime(true) . mt_rand(10000, 90000));
        $updated_by = $sessiondets->Finascop_UserId;
        if ($type == 1) {/* taken over window */
            $data = array('acet_AssignedTo' => $sessiondets->Finascop_UserId, 'acet_Status' => 1, 'updated_on' => $updated_on, 'updated_by' => $updated_by);
            $status = $db->perform(FINASCOP_DB . "finascop_accounts_entry", $data, "update", "acet_NO = '{$acet_NO}'");

            return '{"success" : true,"takenover":true,"updated_on":"' . $updated_on . '"}';
            exit;
        } elseif ($type == 2) {/* escalate */

            $report_to = $db->getItemFromDB("SELECT report_to_user FROM  " . FINASCOP_DB . "finascop_user_details WHERE UserId = {$sessiondets->Finascop_UserId}");
            if (empty($report_to) || $report_to == 0) {
                echo"{'success':false,'msg':'FINASCOP: Cannot Escalate. Your user settings have no \'reporting to\' athority specified.'}";
                exit(1);
            }
            $report_to_name = $db->getItemFromDB("SELECT CONCAT(FirstName,' ',LastName) FROM " . FINASCOP_DB . "`finascop_usr_profile` WHERE UserId = {$report_to}");

            $data = array('acet_EscalatedBy' => $sessiondets->Finascop_UserId, 'acet_Status' => 2, 'acet_AssignedTo' => $report_to, 'updated_on' => $updated_on, 'updated_by' => $updated_by, 'acet_EscalateComments' => $comments);
            $status = $db->perform(FINASCOP_DB . "finascop_accounts_entry", $data, "update", "acet_NO = '{$acet_NO}'");
            $msg = "Audit entry escalated to {$report_to_name}";
            if (empty($data['acet_NO'])) {
                $nodb = new \cgoDynamiteDB();
                $arrSession = array();
                $arrSession['PartitionKey'] = array('col' => 'apikey', 'val' => (string) $post_data['apikey']);
                $arrSession['SortKey'] = array('col' => 'tstamp', 'val' => (int) $data['tstamp']);
                $arrSession['Data'] = array();
                array_push($arrSession['Data'], array('col' => 'escalatedto', 'val' => $report_to_name));
                $nors = $nodb->perform('ActionLogs', 'update', $arrSession, $response);
            }
        } elseif ($type == 3) {/* approved */
            if ($current_acet_status == '3') {
                echo "{success : false, msg : 'FINASCOP: This entry has already been approved'}";
                exit;
            }
            $listQuery = "SELECT ledg_Id AS particular_id,IF(actr_IsNegative = 1,-actr_amount,actr_amount) AS amount,
				IF(b.GroupName<>'',CONCAT(accled_LedgerName,' (',GroupName,')'),accled_LedgerName) AS particular_name, actr_IsDebtor as IsDebtor, entry_type
				FROM " . FINASCOP_DB . "finascop_accounts_transaction a
				INNER JOIN " . FINASCOP_DB . "finascop_accounts_ledger b
				ON a.ledg_Id = b.accled_Ledger_Id 
				WHERE acet_NO = '{$acet_NO}' ";
 
            $data = $db->getMultipleData($listQuery, true);
            if (empty($data)) {
                echo "{success : false, msg : 'FINASCOP: The entries are incomplete please verify.'}";
                exit;
            }
            $uniqtypeids = array();
            $AccountTotal = 0;
            $total = 0;
            foreach ($data as $key => $val) {
                if (in_array($val['particular_id'], $uniqtypeids)) {
                   file_put_contents('php://stderr', print_r("&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&& \n", TRUE));
                file_put_contents('php://stderr', print_r($grid_data, TRUE));
                
//           $fields = array("tempvalue" => $grid_data);
//           $db->perform('temptable',$fields);          
           
                    echo "{success : false, msg : 'FINASCOP: " . $val['particular_name'] . " has multiple entries, please remove duplicate entry'}";
                    exit;
                } else {
                    array_push($uniqtypeids, $val['particular_id']);
                }
                if ($val['entry_type'] == 'Account') {
                    $AccountTotal = $val['amount'];
                } else {
                    $total = $total + $val['amount'];
                }
            }
            if (bccomp($AccountTotal, $total, 2) != 0) {
                echo "{success : false, msg : 'FINASCOP: Critical error!. Sum of all Particular entries is not same as  Grand total[" . $AccountTotal . " <> " . $total . "]'}";
                exit;
            }
            $data = array('acet_ApprovedBy' => $sessiondets->Finascop_UserId, 'acet_Status' => 3, 'updated_on' => $updated_on, 'updated_by' => $updated_by);
            $status = $db->perform(FINASCOP_DB . "finascop_accounts_entry", $data, "update", "acet_NO = '{$acet_NO}'");

            $accounts_transaction_data = array('actr_IsApproved' => 1);
            $db->perform(FINASCOP_DB . "finascop_accounts_transaction", $accounts_transaction_data, "update", "acet_NO = '{$acet_NO}'");
            $add_amount = 1;
            $this->updateLedgerBalance($acet_NO, $add_amount);
            $msg = "Audit entry approved.";
        } elseif ($type == 4) {/* cancelled */
            $data = array('acet_Status' => 4, 'updated_on' => $updated_on, 'updated_by' => $updated_by);
            $status = $db->perform(FINASCOP_DB . "finascop_accounts_entry", $data, "update", "acet_NO = '{$acet_NO}'");
            $accounts_transaction_data = array('actr_IsApproved' => -1); //transaction entry cancelled
            $db->perform(FINASCOP_DB . "finascop_accounts_transaction", $accounts_transaction_data, "update", "acet_NO = '{$acet_NO}'");
            $msg = "Audit entry cancelled.";
        } elseif ($type == 5) {/* rebut */
            $data = array('acet_AssignedTo' => 0, 'acet_Status' => 5, 'updated_on' => $updated_on, 'updated_by' => $updated_by, 'acet_RebutComments' => $comments);
            $status = $db->perform(FINASCOP_DB . "finascop_accounts_entry", $data, "update", "acet_NO = '{$acet_NO}'");
            $msg = "Audit entry rebutted.";
        } elseif ($type == 6) {/* rollback */
            $accounts_transaction_data = array('actr_IsApproved' => 0); //transaction entry cancelled
            $db->perform(FINASCOP_DB . "finascop_accounts_transaction", $accounts_transaction_data, "update", "acet_NO = '{$acet_NO}'");
            $data = array('acet_AssignedTo' => $sessiondets->Finascop_UserId, 'acet_Status' => 1, 'updated_on' => $updated_on, 'updated_by' => $updated_by);
            $status = $db->perform(FINASCOP_DB . "finascop_accounts_entry", $data, "update", "acet_NO = '{$acet_NO}'");
            $add_amount = 0;
            $this->updateLedgerBalance($acet_NO, $add_amount);
            $msg = "Audit entry rollbacked.";
        }
        if ($status)
            return '{"success" : true,"msg":"' . $msg . '","updated_on":"' . $updated_on . '"}';
    }

    public function updatedate($data, $sessiondets) {
        global $db;
        if (strtotime(($data['receipt_date'] == '' ? date('Y-m-d') : $data['receipt_date'])) > strtotime(date('Y-m-d'))) {
            echo '{"success":false,"msg":"FINASCOP: Please check the date, you cannot save a post dated entry "}';
            exit();
        }
        $acc_ref = $data['acet_NO'];
        $receipt_date = ($data['receipt_date'] == '' ? date('Y-m-d') : $data['receipt_date']);
        $updated_on = $data['updated_on'];
        $currentStatus = $db->getFromDB("SELECT updated_on FROM " . FINASCOP_DB . "finascop_accounts_entry WHERE acet_NO = '{$acc_ref}'", true);
        if ($updated_on != $currentStatus['updated_on']) {
            echo '{"success":false,"msg":"FINASCOP: Please refresh the entries. This entry has been updated."}';
            exit();
        }
        //Save Entry
        $updated_on = sha1(microtime(true) . mt_rand(10000, 90000));
        $updated_by = $sessiondets->Finascop_UserId;
        $entryData = array('acet_ApprovedBy' => $sessiondets->Finascop_UserId, 'acet_Date' => $receipt_date, 'updated_on' => $updated_on, 'updated_by' => $updated_by);
        $status = $db->perform(FINASCOP_DB . "finascop_accounts_entry", $entryData, "update", "acet_NO = '{$acc_ref}'");
        //Save  Transaction
        $accounts_transaction_data = array('actr_Date' => $receipt_date);
        $db->perform(FINASCOP_DB . "finascop_accounts_transaction", $accounts_transaction_data, "update", "acet_NO = '{$acc_ref}'");
        $msg = "Updated date";
        return '{"success" : true,"msg":"' . $msg . '","updated_on":"' . $updated_on . '"}';
    }

}



