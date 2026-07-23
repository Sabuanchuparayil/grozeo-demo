<?php

namespace Models; {

    class Wallet {

        public function GET_wallet($flag, $request) {
            
        }

        public function POST_addfund($flag, $request) {
            if (!isset($request))
            {
                throw new \Exception('Missing Post parameters ');
            }

            if (!array_key_exists('ledgerid', $request) || !array_key_exists('requestid', $request) || !array_key_exists('bankid', $request) || !array_key_exists('amount', $request) || !array_key_exists('UTRRefno', $request) || $request['UTRRefno'] == '' || floatval($request['amount']) == 0 || $request['requestid'] == '' || $request['bankid'] == '')
            {
                throw new \Exception('Missing add fund details ');
            }
            $cgodb = new \cgoSqlDB();
            /* Setting current  date default */
            $date = date_default_timezone_set('Asia/Kolkata');
            $dt = date("Y-m-d H:i:s");
            $key = sha1(microtime(true) . mt_rand(10000, 90000));
            $crc32id = crc32($key);

            $ledg = $request;
            $arrAuth = array();


            $ReferenceId = $cgodb->getItemFromDB("Select wafr_ReferenceId from finascop_wallet_addfundrequest where wafr_requestid = ? and wafr_reqidCRC32 = ? ", array('si', $ledg['requestid'], crc32($ledg['requestid'])), true);
            if ($ReferenceId != '')
            {
                $arrAuth['success'] = true;
                $arrAuth['msg'] = 'Add fund saved';
                $arrAuth['Data']['TransactionId'] = $ReferenceId;
                return $arrAuth;
            }

            $ledgReferenceId = $cgodb->getItemFromDB("Select accled_Ledger_Id from  finascop_accounts_ledger where   accled_RefIdCRC32 = ?  and accled_ReferenceId = ? ", array('is', crc32($ledg['ledgerid']), $ledg['ledgerid']), true);
            if (intval($ledgReferenceId) == 0)
            {
                $arrAuth['msg'] = 'Invalid ledger';
                $arrAuth['Data']['TransactionId'] = null;
                return $arrAuth;
            }
            //Is the bank account mapped 
            $BankLedgerId = $cgodb->getItemFromDB("Select walc_OriginalId from finascop_wallet_ledgerconfiguration where walc_type = 1 and comp_id=? and walc_subtype = ?  ", array('is', $_SESSION["compid"], $ledg['bankid']), true);
            if (intval($BankLedgerId) == 0)
            {
                $arrAuth['msg'] = 'Bank mapping not set';
                $arrAuth['Data']['TransactionId'] = null;
                return $arrAuth;
            }
            $cgodb->begintransaction();
            //Add fund
            $cgodb->query("INSERT INTO finascop_wallet_addfundrequest(wafr_compid,wafr_ledgid,wafr_bankid,wafr_amount,wafr_requestid,wafr_reqidCRC32,wafr_UTRRefno,wafr_ReqTime,wafr_ReferenceId,wafr_RefIdCRC32,wafr_ClientComments,wafr_AddedOn) values(?,?,?,?,?,?,?,?,?,?,?,?)", array("iisssssssiss", $_SESSION["compid"], $ledgReferenceId, $ledg['bankid'], $ledg['amount'], $ledg['requestid'], crc32($ledg['requestid']), $ledg['UTRRefno'], $dt, $key, $crc32id, $ledg['comments'], ($ledg['addedon'] == '' ? '0000-00-00' : $ledg['addedon'])));
            $cgodb->committransaction();
            $arrAuth['success'] = true;
            $arrAuth['msg'] = 'Add fund saved';
            $arrAuth['Data']['TransactionId'] = $key;

            return $arrAuth;
        }

        public function POST_financialtransaction($flag, $request) {
            if (!isset($request))
            {
                throw new \Exception('Missing Post parameters ');
            }

            if (!array_key_exists('crossbranch', $request) || !array_key_exists('Comments', $request) || !array_key_exists('particulars', $request) || !array_key_exists('requestid', $request) || !array_key_exists('amount', $request) || !array_key_exists('account', $request) || !array_key_exists('NoCreditValidation', $request) || !array_key_exists('EnforceCreditLimit', $request) || $request['account'] == '' || intval($request['ledger_type']) == 0 || floatval($request['amount']) == 0 || $request['Comments'] == '' || !array_key_exists('QueueForRecon', $request) || $request['receipt_date'] == '' || $request['receipt_date'] == '' || $request['crossbranch'] == '' || $request['QueueForRecon'] == '' || $request['requestid'] == '' || $request['ledger_type'] == '')
            {
				print_r($request);
                throw new \Exception('Missing financial transaction details ');
            }

            $request['particulars_arr'] = json_decode($request['particulars']);
            

            
            foreach ($request['particulars_arr'] as $crleg)
            {
                if (!array_key_exists('id', $crleg) || !array_key_exists('amount', $crleg))
                {
                    throw new \Exception('Missing Keys in Credit Ledger details ');
                }
                if ($crleg->id == '' || floatval($crleg->amount) == 0)
                {
                    throw new \Exception('Invalid Credit Ledger details ' . $crleg->id . ' -- ' . $crleg->amount );
                }
            }
            if (intval($request['ledger_type']) < 1 || intval($request['ledger_type']) > 6)
            {
                throw new \Exception('Invalid Ledger Type');
            }
			if (intval($request['ledger_type'])==5){
				if(!array_key_exists('JVSingleEntryOn', $request) || $request['JVSingleEntryOn'] == '' || ($request['JVSingleEntryOn'] != 'Debtor' && $request['JVSingleEntryOn'] != 'Creditor')){
					throw new \Exception('Invalid JVSingleEntryOn Type');
				}
			}
            global $db;
            $db = new \sqlDb(DSN);
            $cgodb = new \cgoSqlDB();
            $util = new Utils();
            
            if ($request['NoCreditValidation'] == 'false')
            {
                /* $HasCredit = $cgodb->getItemFromDB("Select wasu_CreditEnabled from finascop_wallet_sundryentity where  accled_ReferenceId = ?   and wasu_RefIdCRC32 = ? ",array('si', $request['account'],crc32($request['account'])),true);
                  if($HasCredit ==0){
                  $arrAuth['msg'] = "Credit has not been enabled";
                  $arrAuth['Data']['TransactionId'] = null;
                  return	$arrAuth;
                  } */
                if ($request['EnforceCreditLimit'] == 'true')
                {
                    $ledgdets = $util->getLedgerBalance($request['account']);
                    $CreditLimit = $cgodb->getItemFromDB("Select wasu_CreditLimit from finascop_wallet_sundryentity where accled_ReferenceId = ?  and wasu_RefIdCRC32 = ? ", array('si', $request['account'], crc32($request['account'])), true);
                    if ((($ledgdets['Credits'] + $CreditLimit) - ($ledgdets['Debits'] + $request['amount'])) <= 0)
                    {
                        $arrAuth['msg'] = "You don't have enough credit to complete the entry";
                        $arrAuth['Data']['TransactionId'] = null;
                        return $arrAuth;
                    }
                }
                else
                {
                    $ledgdets = $util->getLedgerBalance($request['account']);
                    if (($ledgdets['Credits'] - ($ledgdets['Debits'] + $request['amount'])) <= 0)
                    {
                        $arrAuth['msg'] = "You don't have enough credit to complete the entry";
                        $arrAuth['Data']['TransactionId'] = null;
                        return $arrAuth;
                    }
                }
            }

            if ($request['QueueForRecon'] != 'true' && $request['QueueForRecon'] != 'false')
            {
                $arrAuth['msg'] = "Invalid flag for reconcillation status " . $request['QueueForRecon'];
                $arrAuth['Data']['TransactionId'] = null;
                return $arrAuth;
            }
            /* Setting current  date default */
            $date = date_default_timezone_set('Asia/Kolkata');
            $dt = date("Y-m-d H:i:s");
            $key = sha1(microtime(true) . mt_rand(10000, 90000));
            $crc32id = crc32($key);

            $ledg = $request;
            $arrAuth = array();


            $ReferenceId = $cgodb->getItemFromDB("Select acet_NO from finascop_wallet_transaction where wadt_requestid = ? and wadt_reqidCRC32 = ? ", array('si', $ledg['requestid'], crc32($ledg['requestid'])), true);
            if ($ReferenceId != '')
            {
                $arrAuth['success'] = true;
                $arrAuth['msg'] = 'Transaction Saved';
                $arrAuth['Data']['TransactionId'] = $ReferenceId;
                return $arrAuth;
            }
            $grid_data = array();

            foreach ($request['particulars_arr'] as $crleg)
            {
                $ledgReference = $cgodb->getFromDB("Select accled_Ledger_Id,accled_LedgerName from  finascop_accounts_ledger where   accled_RefIdCRC32 = ?  and accled_ReferenceId = ? ", array('is', crc32($crleg->id), $crleg->id), true);
                if (intval($ledgReference['accled_Ledger_Id']) == 0)
                {
                    $arrAuth['msg'] = 'Invalid ledger';
                    $arrAuth['Data']['TransactionId'] = null;
                    return $arrAuth;
                }
                array_push($grid_data, array("particular_id" => $ledgReference['accled_Ledger_Id'], "particular_name" => $ledgReference['accled_LedgerName'], "amount" => $crleg->amount));
            }

            $debitReferenceId = $cgodb->getItemFromDB("Select accled_Ledger_Id from  finascop_accounts_ledger where   accled_RefIdCRC32 = ?  and accled_ReferenceId = ? ", array('is', crc32($ledg['account']), $ledg['account']), true);
            if (intval($debitReferenceId) == 0)
            {
                $arrAuth['msg'] = 'Invalid ledger';
                $arrAuth['Data']['TransactionId'] = null;
                return $arrAuth;
            }
            $db->query("begin");
            $transdata = array();
            $transdata['receipt_date'] = $ledg['receipt_date'] ;
            $transdata['receipt_account'] = $debitReferenceId;
            $transdata['ledger_type'] = intval($request['ledger_type']);
            switch (intval($request['ledger_type']))
            {
                case 1:
                    $transdata['type'] = 'Receipt';
					$transdata['ctr_dtr_type'] = 'Debtor';
                    break;
                case 2:
                    $transdata['type'] = 'Payment';
					$transdata['ctr_dtr_type'] = 'Creditor';
                    break;
                case 3:
                    $transdata['type'] = 'Receipt';
					$transdata['ctr_dtr_type'] = 'Debtor';
                    break;
                case 4:
                    $transdata['type'] = 'Payment';
					$transdata['ctr_dtr_type'] = 'Creditor';
                    break;
                case 5:
                    $transdata['type'] = 'Journal Voucher';
					$transdata['ctr_dtr_type'] = $request['JVSingleEntryOn'];
                    break;
                case 6:
                    $transdata['type'] = 'Contra Entry';
					$transdata['ctr_dtr_type'] = 'Creditor';
                    break;
                default:
                    throw new \Exception('Invalid  Ledger Type Id');
                    break;
            }

            $transdata['total_amount'] = $ledg['amount'];
            $transdata['narration'] = $ledg['Comments'];
            
            $transdata['apikey'] = $key;
            $transdata['tstamp'] = date("YmdHis");
            $transdata['acet_UTRRefno'] = $request['UTRRefno'];

            $transdata['particular_data'] = json_encode($grid_data);
            $sessiondets = new \stdClass;
            $sessiondets->company_id = $_SESSION["compid"];
            $sessiondets->Finascop_UserId = 0;
            $sessiondets->company = $_SESSION["company"];
            $sessiondets->UserId = 0;
            $sessiondets->branch_id = $_SESSION["defaultbranch"];
            $AccountsLedger = new \finascop\accounts\Transactions\AccountingVouchers();
            $dataenter = $AccountsLedger->saveParticularData($transdata, $sessiondets, 1, ($request['QueueForRecon'] == 'true' ? 0 : 1), $request['QueueForRecon'],($request['crossbranch']==='true' || $request['crossbranch']==='1' || $request['crossbranch']===true ?true:false));

            $dataentry = json_decode($dataenter);

            if ($dataentry->success != true)
            {
                $arrAuth['msg'] = 'Error, unable to create an voucher.';
                $arrAuth['Data']['TransactionId'] = null;
                return $arrAuth;
            }


            //Add fund				
            //$cgodb->query("INSERT INTO finascop_wallet_transaction(wadt_requestid,wadt_reqidCRC32,wadt_compid,wadt_ledgid,wadt_amount,wadt_ReqTime,wadt_IsDebit,acet_NO,wadt_Comments) values(?,?,?,?,?,?,?,?,?)",array("siiississ",$ledg['requestid'],crc32($ledg['requestid']),$_SESSION["compid"],$ledgReferenceId,$ledg['amount'],$dt,1,$dataentry->RefNo,$ledg['Comments']));	
            $arrData = array("wadt_requestid" => $ledg['requestid'],
                "wadt_reqidCRC32" => crc32($ledg['requestid']),
                "wadt_compid" => $_SESSION["compid"],
                "wadt_debitledgid" => $debitReferenceId,
                "wadt_creditledgid" => $request['particulars'],
                "wadt_amount" => $ledg['amount'],
                "wadt_ReqTime" => $dt,
                "wadt_IsDebit" => 1,
                "acet_NO" => $dataentry->RefNo,
                "wadt_Comments" => $ledg['Comments']);
            $db->perform("finascop_wallet_transaction", $arrData);
            $db->query("commit");
            $arrAuth['success'] = true;
            $arrAuth['msg'] = 'Transaction Saved';
            $arrAuth['Data']['TransactionId'] = $dataentry->RefNo;

            return $arrAuth;
        }

    }

}													



