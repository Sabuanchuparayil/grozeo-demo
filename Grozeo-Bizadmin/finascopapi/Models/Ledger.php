<?php

namespace Models; {

    class Ledger {

        public function GET_ledger($flag, $request) {

            $cgodb = new \cgoSqlDB();
            if (($request['ledgerid'] == '') || !array_key_exists('ledgerid', $request) || !array_key_exists('ledgerid', $request) || !isset($request))
            {
                throw new \Exception('Missing Post parameters ');
            }
            $ledg = $cgodb->getItemFromDB("Select wasu_Name from finascop_wallet_sundryentity where   wasu_RefIdCRC32 = ?  and accled_ReferenceId = ? ", array('is', crc32($request['ledgerid']), $request['ledgerid']), true);
            $arrAuth = array();
            $arrAuth['success'] = true;
            $arrAuth['msg'] = 'Ledger';
            $arrAuth['Data']['name'] = $ledg;
            return $arrAuth;
        }

        public function GET_bankledgers($flag, $request) {

            $cgodb = new \cgoSqlDB();
            if (($request['branchkey'] == '') || !array_key_exists('branchkey', $request) || !isset($request))
            {
                throw new \Exception('Missing GET parameters ');
            }
			$branchid =  $cgodb->getItemFromDB("Select br_ID from finascop_branch where br_ReferenceID =? ", array('s', $request['branchkey']), true);
            $arrAuth = array();
            $is = $cgodb->getMulipleData('select ledgertypename as name,accled_ReferenceId as id from finascop_accounts_ledger  where accled_BranchId = ? and Group_ID =2 order by ledgertypename asc ', array('s', $branchid), true);
            $arrAuth['success'] = true;
            $arrAuth['msg'] = 'Bank Ledgers';
            if ($is !== false)
            {
                $arrAuth['Data']['Ledgers'] = $is;
            }
            else
            {
                $arrAuth['Data']['Ledgers'] = array();
            }
            return $arrAuth;
        }
		
        public function GET_cashledgers($flag, $request) {

            $cgodb = new \cgoSqlDB();
            if (($request['branchkey'] == '') || !array_key_exists('branchkey', $request) || !isset($request))
            {
                throw new \Exception('Missing GET parameters ');
            }
			$branchid =  $cgodb->getItemFromDB("Select br_ID from finascop_branch where br_ReferenceID =? ", array('s', $request['branchkey']), true);
            $arrAuth = array();
            $is = $cgodb->getMulipleData('select ledgertypename as name,accled_ReferenceId as id from finascop_accounts_ledger  where accled_BranchId = ? and Group_ID =1 order by ledgertypename asc ', array('s', $branchid), true);
            $arrAuth['success'] = true;
            $arrAuth['msg'] = 'Cash Ledgers';
            if ($is !== false)
            {
                $arrAuth['Data']['Ledgers'] = $is;
            }
            else
            {
                $arrAuth['Data']['Ledgers'] = array();
            }
            return $arrAuth;
        }

        public function GET_groupledgers($flag, $request) {

            $cgodb = new \cgoSqlDB();
            if ( ($request['groupkey'] == '') || !array_key_exists('groupkey', $request) || ($request['branchkey'] == '') || !array_key_exists('branchkey', $request) || !isset($request))
            {
                throw new \Exception('Missing GET parameters ');
            }
			$branchid =  $cgodb->getItemFromDB("Select br_ID from finascop_branch where br_ReferenceID =? ", array('s', $request['branchkey']), true);
			
			$groupid =  $cgodb->getItemFromDB("Select Group_ID from finascop_accounts_groups where Group_ReferenceId =? and Group_RefIdCRC32=? ", array('si', $request['branchkey'], crc32($request['ledgerid'])), true);
			
            $arrAuth = array();
            $is = $cgodb->getMulipleData('select ledgertypename as name,accled_ReferenceId as id from finascop_accounts_ledger  where accled_BranchId = ? and Group_ID ? order by ledgertypename asc ', array('si', $branchid,$groupid), true);
            $arrAuth['success'] = true;
            $arrAuth['msg'] = 'Group Ledgers';
            if ($is !== false)
            {
                $arrAuth['Data']['Ledgers'] = $is;
            }
            else
            {
                $arrAuth['Data']['Ledgers'] = array();
            }
            return $arrAuth;
        }
		
        public function GET_creditbalance($flag, $request) {
            $cgodb = new \cgoSqlDB();
            if (($request['ledgerid'] == '') || !array_key_exists('ledgerid', $request) || !isset($request))
            {
                throw new \Exception('Missing Post parameters ');
            }
            $util = new Utils();
            $ledg = $util->getLedgerBalance($request['ledgerid']);
            if (intval($ledg['accled_Ledger_Id']) > 0)
            {
                $arrAuth = array();
                $arrAuth['success'] = true;
                $arrAuth['msg'] = 'Ledger balance';
                $arrAuth['Data']['Balance'] = $ledg['Credits'] - $ledg['Debits'];
            }
            else
            {
                $arrAuth = array();
                $arrAuth['success'] = false;
                $arrAuth['msg'] = 'Ledger not found';
                $arrAuth['Data']['Balance'] = NULL;
            }
            return $arrAuth;
        }

        public function get($flag, $request) {
            $cgodb = new \cgoSqlDB();
            if (($request['ledgerid'] == '') || !array_key_exists('ledgerid', $request) || !array_key_exists('ledgerid', $request) || !isset($request))
            {
                throw new \Exception('Missing Post parameters ');
            }
            $ledg = $cgodb->getFromDB("Select wasu_Name from finascop_wallet_sundryentity where   wasu_RefIdCRC32 = ?  and accled_ReferenceId = ? ", array('is', crc32($request['ledgerid']), $request['ledgerid']), true);
            if ($ledgReferenceId != '')
            {
                $arrAuth['Data']['Ledger'] = $ledg;
                return $arrAuth;
            }
            return true;
        }

        public function POST_Debtor($flag, $request) {
            if (!isset($request))
            {
                throw new \Exception('Missing Post parameters ');
            }

            if (!array_key_exists('requestid', $request) || !array_key_exists('CreditLimit', $request) || !array_key_exists('CreditEnabled', $request) || !array_key_exists('ContactNo', $request) || trim($request['ContactNo']) == '' || !array_key_exists('Group', $request) || !array_key_exists('Name', $request) || $request['requestid'] == '' || $request['CreditLimit'] == '' || $request['CreditEnabled'] == '' || $request['Name'] == '' || $request['Group'] == '')
            {
                throw new \Exception('Missing details of ledger of POST debtor');
            }
            global $db;
            $db = new \sqlDb(DSN);
            $cgodb = new \cgoSqlDB();
            /* Setting current  date default */
            $ledg = $request;
            $arrAuth = array();
            $groupId = $cgodb->getItemFromDB("Select Group_ID from finascop_accounts_groups where Group_ReferenceId = ? and Group_RefIdCRC32 = ?  ", array('si', $ledg['Group'], crc32($ledg['Group'])), true);
            if (intval($groupId) == 0)
            {
                $arrAuth['success'] = false;
                $arrAuth['msg'] = "Invalid group ";
                $arrAuth['Data']['LedgerId'] = null;
                return $arrAuth;
            }


            $date = date_default_timezone_set('Asia/Kolkata');
            $dt = date("Y-m-d H:i:s");
            $key = sha1(microtime(true) . mt_rand(10000, 90000));
            $crc32id = crc32($key);



            $arrAuth['success'] = true;
            $arrAuth['msg'] = 'Saved Ledger';
            $ledgReferenceId = $cgodb->getItemFromDB("Select accled_ReferenceId from finascop_wallet_sundryentity where wasu_requestid = ? and wasu_reqidCRC32 = ? and wasu_IsSundryDebtor = 1 ", array('si', $ledg['requestid'], crc32($ledg['requestid'])), true);
            if ($ledgReferenceId != '')
            {
                $arrAuth['Data']['LedgerId'] = $ledgReferenceId;
                return $arrAuth;
            }

            $cgodb->begintransaction();
            $cgodb->query("INSERT INTO finascop_wallet_sundryentity(wasu_requestid,wasu_Name,wasu_GSTIN,wasu_ContactNo,wasu_Address,wasu_PAN,accled_ReferenceId,wasu_CreditLimit,wasu_CreditEnabled,wasu_RefIdCRC32,wasu_CompanyId,wasu_IsSundryDebtor,wasu_reqidCRC32) values(?,?,?,?,?,?,?,?,?,?,?,?,?)", array("sssssssssiiis", $ledg['requestid'], $ledg['Name'], $ledg['GSTIN'], $ledg['ContactNo'], $ledg['Address'], $ledg['PAN'], $key, $ledg['CreditLimit'], ($ledg['CreditEnabled'] == 'true' ? 1 : 0), $crc32id, $_SESSION["compid"], 1, crc32($ledg['requestid'])));
            //Add Ledger
            $branchdets = $cgodb->getFromDB("Select br_Name, branch_shortname from finascop_branch where br_id = ?", array('i', $_SESSION["defaultbranch"]), true);
            $groupcode = $cgodb->getItemFromDB("Select Group_ShortCode from finascop_accounts_groups where Group_ID = ?", array('i', $groupId), true);
            $ledger = array();
            $ledger['ledgertypeid'] = 0;
            $ledger['ledgertypename'] = substr($ledg['Name'], 0, 100) . "_" . $ledg['ContactNo'] . "_" . $groupcode;
            $ledger['Group_ID'] = $groupId;
            $grid_data = array();
            $grid_data[0] = array("br_ID" => $_SESSION["defaultbranch"], "br_Name" => $branchdets['br_Name'], "branch_shortname" => $branchdets['branch_shortname'], "isChecked" => "1", "accled_IsEnabled" => "1", "accled_Ledger_Id" => "0", "accled_Ledger_Id_true" => "0");
            $AccountsLedger = new \finascop\accounts\Transactions\AccountingVouchers();
            $returned = $AccountsLedger->saveLedgers($ledger, $_SESSION["compid"], $grid_data, $key);
            $ledgerentry = json_decode($returned);
            if ($ledgerentry->success == true)
            {
                $cgodb->committransaction();
                $arrAuth['Data']['LedgerId'] = $key;
            }
            else
            {
                $arrAuth['success'] = false;
                $arrAuth['msg'] = $ledgerentry->msg;
                $arrAuth['Data']['LedgerId'] = null;
            }
            return $arrAuth;
        }

        public function PUT_Debtor($flag, $request) {
            if (!isset($request))
            {
                throw new \Exception('Missing Post parameters ');
            }

            if (!array_key_exists('ReferenceId', $request) || !array_key_exists('CreditLimit', $request) || !array_key_exists('CreditEnabled', $request) || !array_key_exists('ContactNo', $request) || trim($request['ContactNo']) == '' || !array_key_exists('Group', $request) || !array_key_exists('Name', $request) || $request['ReferenceId'] == '' || $request['CreditLimit'] == '' || $request['CreditEnabled'] == '' || $request['Name'] == '' || $request['Group'] == '')
            {
                file_put_contents('php://stderr', print_r($request, TRUE));
                throw new \Exception('Missing details of ledger details of PUT debtor');
            }
            global $db;
            $db = new \sqlDb(DSN);
            $cgodb = new \cgoSqlDB();
            /* Setting current  date default */
            $date = date_default_timezone_set('Asia/Kolkata');
            $dt = date("Y-m-d H:i:s");

            $ledg = $request;

            $arrAuth = array();

            $groupId = $cgodb->getItemFromDB("Select Group_ID from finascop_accounts_groups where Group_ReferenceId = ? and Group_RefIdCRC32 = ?  ", array('si', $ledg['Group'], crc32($ledg['Group'])), true);
            if (intval($groupId) == 0)
            {
                $arrAuth['success'] = false;
                $arrAuth['msg'] = "Invalid group ";
                $arrAuth['Data']['LedgerId'] = null;
                return $arrAuth;
            }

            $arrAuth['success'] = true;
            $arrAuth['msg'] = 'Saved Ledger';
            /*$ledgReferenceId = $cgodb->getItemFromDB("Select accled_ReferenceId from finascop_wallet_sundryentity where accled_ReferenceId = ? and wasu_RefIdCRC32 = ? ", array('si', $ledg['ReferenceId'], crc32($ledg['ReferenceId'])), true);
            if ($ledgReferenceId == '')
            {
                $arrAuth['success'] = false;
                $arrAuth['msg'] = 'Invalid Reference Id';
                $arrAuth['Data']['LedgerId'] = $ledgReferenceId;
                return $arrAuth;
            }*/

            $cgodb->begintransaction();
            $cgodb->query("update finascop_wallet_sundryentity SET wasu_Name=?,wasu_GSTIN=?,wasu_ContactNo=?,wasu_Address=?,wasu_PAN=?,wasu_CreditLimit=?,wasu_CreditEnabled=?,wasu_IsSundryDebtor=? where accled_ReferenceId =? and wasu_IsSundryDebtor=1", array("ssssssiis", $ledg['Name'], $ledg['GSTIN'], $ledg['ContactNo'], $ledg['Address'], $ledg['PAN'], $ledg['CreditLimit'], ($ledg['CreditEnabled'] == 'true' ? 1 : 0), 1, $ledg['ReferenceId']));
            //Add Ledger
            $ledgertypeid = $cgodb->getItemFromDB("Select ledgertypeid from finascop_accounts_ledger where accled_ReferenceId = ? and accled_RefIdCRC32 = ? ", array('si', $ledg['ReferenceId'], crc32($ledg['ReferenceId'])), true);
            if (intval($ledgertypeid) == 0)
            {
                $arrAuth['success'] = false;
                $arrAuth['msg'] = "Error!. Wallet's ledger id not found";
                $arrAuth['Data']['LedgerId'] = $ledgertypeid;
                return $arrAuth;
            }

            $branchdets = $cgodb->getFromDB("Select br_Name, branch_shortname from finascop_branch where br_id = ?", array('i', $_SESSION["defaultbranch"]), true);
            $groupcode = $cgodb->getFromDB("Select Group_ShortCode from finascop_accounts_groups where Group_ID = ?", array('i', $groupId), true);
            $ledger = array();
            $ledger['ledgertypeid'] = $ledgertypeid;
            $ledger['ledgertypename'] = substr($ledg['Name'], 0, 100) . "_" . $ledg['ContactNo'] . "_" . $groupcode;
            $ledger['Group_ID'] = $groupId;
            $grid_data = array();
            $grid_data[0] = array("br_ID" => $_SESSION["defaultbranch"], "br_Name" => $branchdets['br_Name'], "branch_shortname" => $branchdets['branch_shortname'], "isChecked" => "1", "accled_IsEnabled" => "1", "accled_Ledger_Id" => "0", "accled_Ledger_Id_true" => "0");
            $AccountsLedger = new \finascop\accounts\Transactions\AccountingVouchers();
            $returned = $AccountsLedger->saveLedgers($ledger, $_SESSION["compid"], $grid_data, $ledgReferenceId);
            $ledgerentry = json_decode($returned);
            if ($ledgerentry->success == true)
            {
                $cgodb->committransaction();
                $arrAuth['Data']['LedgerId'] = $ledgReferenceId;
            }
            else
            {
                $arrAuth['success'] = false;
                $arrAuth['msg'] = $ledgerentry->msg;
                $arrAuth['Data']['LedgerId'] = null;
            }
            return $arrAuth;
        }

        public function POST_Creditor($flag, $request) {
            if (!isset($request))
            {
                throw new \Exception('Missing Post parameters ');
            }

            if (!array_key_exists('requestid', $request) || !array_key_exists('Group', $request) || !array_key_exists('ContactNo', $request) || trim($request['ContactNo']) == '' || !array_key_exists('Name', $request) || $request['requestid'] == '' || $request['Name'] == '' || $request['Group'] == '')
            {
                throw new \Exception('Missing details of ledger');
            }
            global $db;
            $db = new \sqlDb(DSN);
            $cgodb = new \cgoSqlDB();
            /* Setting current  date default */
            $date = date_default_timezone_set('Asia/Kolkata');
            $dt = date("Y-m-d H:i:s");
            $key = sha1(microtime(true) . mt_rand(10000, 90000));
            $crc32id = crc32($key);

            $ledg = $request;
            $arrAuth = array();
            $groupId = $cgodb->getItemFromDB("Select Group_ID from finascop_accounts_groups where Group_ReferenceId = ? and Group_RefIdCRC32 = ?  ", array('si', $ledg['Group'], crc32($ledg['Group'])), true);
            if (intval($groupId) == 0)
            {
                $arrAuth['success'] = false;
                $arrAuth['msg'] = "Invalid group ";
                $arrAuth['Data']['LedgerId'] = null;
                return $arrAuth;
            }
            $arrAuth['success'] = true;
            $arrAuth['msg'] = 'Saved Ledger';
            $ledgReferenceId = $cgodb->getItemFromDB("Select accled_ReferenceId from finascop_wallet_sundryentity where wasu_requestid = ? and wasu_reqidCRC32 = ? and wasu_IsSundryDebtor=0", array('si', $ledg['requestid'], crc32($ledg['requestid'])), true);
            if ($ledgReferenceId != '')
            {

                $arrAuth['Data']['LedgerId'] = $ledgReferenceId;
                return $arrAuth;
            }

            $cgodb->begintransaction();
            $cgodb->query("INSERT INTO finascop_wallet_sundryentity(wasu_requestid,wasu_Name,wasu_GSTIN,wasu_ContactNo,wasu_Address,wasu_PAN,accled_ReferenceId,wasu_CreditLimit,wasu_CreditEnabled,wasu_RefIdCRC32,wasu_CompanyId,wasu_IsSundryDebtor,wasu_reqidCRC32) values(?,?,?,?,?,?,?,?,?,?,?,?,?)", array("sssssssssiiis", $ledg['requestid'], $ledg['Name'], $ledg['GSTIN'], $ledg['ContactNo'], $ledg['Address'], $ledg['PAN'], $key, $ledg['CreditLimit'], ($ledg['CreditEnabled'] == 'true' ? 1 : 0), $crc32id, $_SESSION["compid"], 0, crc32($ledg['requestid'])));
            //Add Ledger
            $branchdets = $cgodb->getFromDB("Select br_Name, branch_shortname from finascop_branch where br_id = ?", array('i', $_SESSION["defaultbranch"]), true);
            $groupcode = $cgodb->getFromDB("Select Group_ShortCode from finascop_accounts_groups where Group_ID = ?", array('i', $groupId), true);
            $ledger = array();
            $ledger['ledgertypeid'] = 0;
            $ledger['ledgertypename'] = substr($ledg['Name'], 0, 100) . "_" . $ledg['ContactNo'] . "_" . $groupcode;
            $ledger['Group_ID'] = $groupId;
            $grid_data = array();
            $grid_data[0] = array("br_ID" => $_SESSION["defaultbranch"], "br_Name" => $branchdets['br_Name'], "branch_shortname" => $branchdets['branch_shortname'], "isChecked" => "1", "accled_IsEnabled" => "1", "accled_Ledger_Id" => "0", "accled_Ledger_Id_true" => "0");
            $AccountsLedger = new \finascop\accounts\Transactions\AccountingVouchers();
            $returned = $AccountsLedger->saveLedgers($ledger, $_SESSION["compid"], $grid_data, $key);
            $ledgerentry = json_decode($returned);
            if ($ledgerentry->success == true)
            {
                $cgodb->committransaction();
                $arrAuth['Data']['LedgerId'] = $key;
            }
            else
            {
                $arrAuth['success'] = false;
                $arrAuth['msg'] = $ledgerentry->msg;
                $arrAuth['Data']['LedgerId'] = null;
            }
            return $arrAuth;
        }

       public function POST_Ledger($flag, $request) {
            if (!isset($request))
            {
                throw new \Exception('Missing Post parameters ');
            }

            if (!array_key_exists('requestid', $request) || !array_key_exists('CreditLimit', $request) || !array_key_exists('CreditEnabled', $request) || !array_key_exists('ContactNo', $request) || trim($request['ContactNo']) == '' || !array_key_exists('Group', $request) || !array_key_exists('Name', $request) || $request['requestid'] == '' || $request['CreditLimit'] == '' || $request['CreditEnabled'] == '' || $request['Name'] == '' || $request['Group'] == '')
            {
                throw new \Exception('Missing details of ledger of POST Ledger');
            }
            global $db;
            $db = new \sqlDb(DSN);
            $cgodb = new \cgoSqlDB();
            /* Setting current  date default */
            $ledg = $request;
            $arrAuth = array();
            $groupId = $cgodb->getItemFromDB("Select Group_ID from finascop_accounts_groups where Group_ReferenceId = ? and Group_RefIdCRC32 = ?  ", array('si', $ledg['Group'], crc32($ledg['Group'])), true);
            if (intval($groupId) == 0)
            {
                $arrAuth['success'] = false;
                $arrAuth['msg'] = "Invalid group ";
                $arrAuth['Data']['LedgerId'] = null;
                return $arrAuth;
            }


            $date = date_default_timezone_set('Asia/Kolkata');
            $dt = date("Y-m-d H:i:s");
            $key = sha1(microtime(true) . mt_rand(10000, 90000));
            $crc32id = crc32($key);



            $arrAuth['success'] = true;
            $arrAuth['msg'] = 'Saved Ledger';
            $ledgReferenceId = $cgodb->getItemFromDB("Select accled_ReferenceId from finascop_wallet_sundryentity where wasu_requestid = ? and wasu_reqidCRC32 = ? and wasu_IsSundryDebtor = 1 ", array('si', $ledg['requestid'], crc32($ledg['requestid'])), true);
            if ($ledgReferenceId != '')
            {
                $arrAuth['Data']['LedgerId'] = $ledgReferenceId;
                return $arrAuth;
            }

            $cgodb->begintransaction();
            $cgodb->query("INSERT INTO finascop_wallet_sundryentity(wasu_requestid,wasu_Name,wasu_GSTIN,wasu_ContactNo,wasu_Address,wasu_PAN,accled_ReferenceId,wasu_CreditLimit,wasu_CreditEnabled,wasu_RefIdCRC32,wasu_CompanyId,wasu_IsSundryDebtor,wasu_reqidCRC32) values(?,?,?,?,?,?,?,?,?,?,?,?,?)", array("sssssssssiiis", $ledg['requestid'], $ledg['Name'], $ledg['GSTIN'], $ledg['ContactNo'], $ledg['Address'], $ledg['PAN'], $key, $ledg['CreditLimit'], ($ledg['CreditEnabled'] == 'true' ? 1 : 0), $crc32id, $_SESSION["compid"], 1, crc32($ledg['requestid'])));
            //Add Ledger
            $branchdets = $cgodb->getFromDB("Select br_Name, branch_shortname from finascop_branch where br_id = ?", array('i', $_SESSION["defaultbranch"]), true);
            $groupcode = $cgodb->getItemFromDB("Select Group_ShortCode from finascop_accounts_groups where Group_ID = ?", array('i', $groupId), true);
            $ledger = array();
            $ledger['ledgertypeid'] = 0;
            $ledger['ledgertypename'] = substr($ledg['Name'], 0, 100) . "_" . $ledg['ContactNo'] . "_" . $groupcode;
            $ledger['Group_ID'] = $groupId;
            $ledger['isApiCreated'] = true;
            $grid_data = array();
            $grid_data[0] = array("br_ID" => $_SESSION["defaultbranch"], "br_Name" => $branchdets['br_Name'], "branch_shortname" => $branchdets['branch_shortname'], "isChecked" => "1", "accled_IsEnabled" => "1", "accled_Ledger_Id" => "0", "accled_Ledger_Id_true" => "0");
            $AccountsLedger = new \finascop\accounts\Transactions\AccountingVouchers();
            $returned = $AccountsLedger->saveLedgers($ledger, $_SESSION["compid"], $grid_data, $key);
            $ledgerentry = json_decode($returned);
            if ($ledgerentry->success == true)
            {
                $cgodb->committransaction();
                $arrAuth['Data']['LedgerId'] = $key;
            }
            else
            {
                $arrAuth['success'] = false;
                $arrAuth['msg'] = $ledgerentry->msg;
                $arrAuth['Data']['LedgerId'] = null;
            }
            return $arrAuth;
        }
  
       public function PUT_Ledger($flag, $request) {
            if (!isset($request))
            {
                throw new \Exception('Missing Post parameters ');
            }

            if (!array_key_exists('ReferenceId', $request) || !array_key_exists('CreditLimit', $request) || !array_key_exists('CreditEnabled', $request) || !array_key_exists('ContactNo', $request) || trim($request['ContactNo']) == '' || !array_key_exists('Group', $request) || !array_key_exists('Name', $request) || $request['ReferenceId'] == '' || $request['CreditLimit'] == '' || $request['CreditEnabled'] == '' || $request['Name'] == '' || $request['Group'] == '')
            {
                file_put_contents('php://stderr', print_r($request, TRUE));
                throw new \Exception('Missing details of ledger details of PUT Ledger');
            }
            global $db;
            $db = new \sqlDb(DSN);
            $cgodb = new \cgoSqlDB();
            /* Setting current  date default */
            $date = date_default_timezone_set('Asia/Kolkata');
            $dt = date("Y-m-d H:i:s");

            $ledg = $request;

            $arrAuth = array();

            $groupId = $cgodb->getItemFromDB("Select Group_ID from finascop_accounts_groups where Group_ReferenceId = ? and Group_RefIdCRC32 = ?  ", array('si', $ledg['Group'], crc32($ledg['Group'])), true);
            if (intval($groupId) == 0)
            {
                $arrAuth['success'] = false;
                $arrAuth['msg'] = "Invalid group ";
                $arrAuth['Data']['LedgerId'] = null;
                return $arrAuth;
            }

            $arrAuth['success'] = true;
            $arrAuth['msg'] = 'Saved Ledger';
            $ledgReferenceId = $cgodb->getItemFromDB("Select accled_ReferenceId from finascop_wallet_sundryentity where accled_ReferenceId = ? and wasu_RefIdCRC32 = ? ", array('si', $ledg['ReferenceId'], crc32($ledg['ReferenceId'])), true);
            if ($ledgReferenceId == '')
            {
                $arrAuth['success'] = false;
                $arrAuth['msg'] = 'Invalid Reference Id';
                $arrAuth['Data']['LedgerId'] = $ledgReferenceId;
                return $arrAuth;
            }

            $cgodb->begintransaction();
            $cgodb->query("update finascop_wallet_sundryentity SET wasu_Name=?,wasu_GSTIN=?,wasu_ContactNo=?,wasu_Address=?,wasu_PAN=?,wasu_CreditLimit=?,wasu_CreditEnabled=?,wasu_IsSundryDebtor=? where accled_ReferenceId =? and wasu_IsSundryDebtor=1", array("ssssssiis", $ledg['Name'], $ledg['GSTIN'], $ledg['ContactNo'], $ledg['Address'], $ledg['PAN'], $ledg['CreditLimit'], ($ledg['CreditEnabled'] == 'true' ? 1 : 0), 1, $ledg['ReferenceId']));
            //Add Ledger
            $ledgertypeid = $cgodb->getItemFromDB("Select ledgertypeid from finascop_accounts_ledger where accled_ReferenceId = ? and accled_RefIdCRC32 = ? ", array('si', $ledg['ReferenceId'], crc32($ledg['ReferenceId'])), true);
            if (intval($ledgertypeid) == 0)
            {
                $arrAuth['success'] = false;
                $arrAuth['msg'] = "Error!. Wallet's ledger id not found";
                $arrAuth['Data']['LedgerId'] = $ledgertypeid;
                return $arrAuth;
            }

            $branchdets = $cgodb->getFromDB("Select br_Name, branch_shortname from finascop_branch where br_id = ?", array('i', $_SESSION["defaultbranch"]), true);
            $groupcode = $cgodb->getItemFromDB("Select Group_ShortCode from finascop_accounts_groups where Group_ID = ?", array('i', $groupId), true);
            $ledger = array();
            $ledger['ledgertypeid'] = $ledgertypeid;
            $ledger['ledgertypename'] = substr($ledg['Name'], 0, 100) . "_" . $ledg['ContactNo'] . "_" . $groupcode;
            $ledger['Group_ID'] = $groupId;
            $ledger['isApiCreated'] = true;
            $grid_data = array();
            $grid_data[0] = array("br_ID" => $_SESSION["defaultbranch"], "br_Name" => $branchdets['br_Name'], "branch_shortname" => $branchdets['branch_shortname'], "isChecked" => "1", "accled_IsEnabled" => "1", "accled_Ledger_Id" => "0", "accled_Ledger_Id_true" => "0");
            $AccountsLedger = new \finascop\accounts\Transactions\AccountingVouchers();
            $returned = $AccountsLedger->saveLedgers($ledger, $_SESSION["compid"], $grid_data, $ledgReferenceId);
            $ledgerentry = json_decode($returned);
            if ($ledgerentry->success == true)
            {
                $cgodb->committransaction();
                $arrAuth['Data']['LedgerId'] = $ledgReferenceId;
            }
            else
            {
                $arrAuth['success'] = false;
                $arrAuth['msg'] = $ledgerentry->msg;
                $arrAuth['Data']['LedgerId'] = null;
            }
            return $arrAuth;
        }

    }

}									




