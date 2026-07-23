<?php

class FinascopWalletClient {

    private $domain;
    private $waqu_id;
    private $branch_id;
    private $iscrossbranch;
    
    function __construct($domain) {

        $this->domain = $domain;
    }

    private function CURL($Fields, $url, $METHOD = 'POST') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->domain . $url);
        //echo "Domain URL " . $this->domain ."\n";
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        if ($METHOD == 'POST')
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($Fields, '', '&'));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        }
        else if ($METHOD == 'PUT')
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($Fields, '', '&'));
            //print_r($Fields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        }
        else if ($METHOD == 'GET')
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            $header = array();

            foreach ($Fields as $key => $value) {
                array_push($header, $key . ':' . $value);
            }
            $header = array_merge($header, array('Content-Type: application/x-www-form-urlencoded'));
            //print_r($header);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS | CURLPROTO_HTTP);
        //var_dump($ch);
        $data = curl_exec($ch);

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_status == 0)
        {
            $http_status = "'.$this->domain.' Host Not Found.";
        }
        if (curl_errno($ch))
        {
            $unauthIP = '';
            if($http_status == 401){
                //$ipaddress = curl_getinfo($ch, CURLINFO_PRIMARY_IP);
                //print_r(curl_getinfo($ch));
                $unauthIP = "  [Access denied.Unautorized IP Address]  ";
            }
            $ret['msg'] = "'[CURL Error No.:" . curl_errno($ch) . "]   [" . curl_error($ch) . ".]  ".$unauthIP." [HTTP Status:" . $http_status . "]'";
            $ret['success'] = false;
            return json_encode($ret);
        }
         if (!array_key_exists('success', json_decode($data, true)))
        {
            var_dump($data);
        }
        curl_close($ch);

        return $data;
    }

    public function getBankLedgers($branchApiKey,$RefIDs) {

        $companyApiKey = $RefIDs['companyApiKey'];
        $fields = array(
            "apikey" => $companyApiKey,
            "branchkey" => $branchApiKey
        );
        //print_r($fields);


        $apiresult = $this->CURL($fields, "ledger/bankledgers", 'GET');

        $result = json_decode($apiresult, true);
        //var_dump($result);exit;
        if (array_key_exists('success', $result) && $result['success'] == true)
        {
            $ret['success'] = true;
            $ret['Data']['Ledgers'] = $result['Data']['Ledgers'];
            $fields['method'] = 'GET';
            $fields['domain'] = $this->domain;            
            $this->keepLog($fields, $result, $error = null, $success = true);
            return json_encode($ret);
        }
        else
        {
            $ret['msg'] = "Failed to get bank ledgers.{$result['msg']}";
            $ret['error'] = "Error:" . $apiresult;
            $ret['Data']['Ledgers'] = $result['Data']['Ledgers'];
            $ret['success'] = false;
            $fields['method'] = 'GET';
            $fields['domain'] = $this->domain;            
            $this->keepLog($fields, $result, $result['error'], $success = false);
            return json_encode($ret);
        }
    }

    public function createLedger($requestID, $LedgerName, $contact_no, $RefIDs, $credit_limit = 0) {

        $companyApiKey = $RefIDs['companyApiKey'];
        $groupReferenceId = $RefIDs['groupReferenceId'];
        $branchApiKey = $RefIDs['branchApiKey'];
       
        $fields = array(
            "apikey" => $companyApiKey,
            "requestid" => $requestID,
            "Name" => $LedgerName,
            "CreditLimit" => $credit_limit,
            "CreditEnabled" => ($credit_limit > 0) ? 'true' : 'false',
            "ContactNo" => $contact_no,
            "Group" => $groupReferenceId,
            "branchkey" => $branchApiKey
                //"Content-Type" => "application/x-www-form-urlencoded"
        );
       // print_r($fields);


        $apiresult = $this->CURL($fields, "ledger/ledger", 'POST');

        $result = json_decode($apiresult, true);
//var_dump($result);exit;
        if (array_key_exists('success', $result) && $result['success'] == true)
        {
            $ret['msg'] = "Ledger Successfully created.{$result['msg']}";
            $ret['success'] = true;
            $ret['ledgerID'] = $result['Data']['LedgerId'];
            $fields['method'] = 'POST';
            $fields['domain'] = $this->domain;
            $this->keepLog($fields, $result, $error = null, $success = true);
            return json_encode($ret);
        }
        else
        {
            $ret['msg'] = "Failed to create ledger.{$result['msg']}";
            $ret['error'] = "Error:" . $apiresult;
            $ret['success'] = false;
            $fields['method'] = 'POST';
            $fields['domain'] = $this->domain;
            $this->keepLog($fields, $result, $result['error'], $success = false);
            return json_encode($ret);
        }
    }

    public function editLedger($requestID, $ReferenceId, $LedgerName, $contact_no, $RefIDs, $credit_limit = 0) {

        $companyApiKey = $RefIDs['companyApiKey'];
        $groupReferenceId = $RefIDs['groupReferenceId'];
        $branchApiKey = $RefIDs['branchApiKey'];

        $fields = array(
            "apikey" => $companyApiKey,
            "ReferenceId" => $ReferenceId,
            "requestid" => $requestID,
            "Name" => $LedgerName,
            "CreditLimit" => $credit_limit,
            "CreditEnabled" => ($credit_limit > 0) ? 'true' : 'false',
            "ContactNo" => $contact_no,
            "Group" => $groupReferenceId,
            "branchkey" => $branchApiKey
                //"Content-Type" => "application/x-www-form-urlencoded"
        );
       // print_r($fields);


        $apiresult = $this->CURL($fields, "ledger/ledger", 'PUT');
        //var_dump($apiresult);
        $result = json_decode($apiresult, true);
        if (array_key_exists('success', $result) && $result['success'] == true)
        {
            $ret['msg'] = "Ledger Successfully updated.{$result['msg']}";
            $ret['success'] = true;
            $ret['ledgerID'] = $result['Data']['LedgerId'];
            $fields['method'] = 'PUT';
            $fields['domain'] = $this->domain;            
            $this->keepLog($fields, $result, $error = null, $success = true);
            return json_encode($ret);
        }
        else
        {
            $ret['msg'] = "Failed to update Ledger.{$result['msg']}";
            $ret['error'] = "Error:" . $apiresult;
            $ret['success'] = false;
            $fields['method'] = 'PUT';
            $fields['domain'] = $this->domain;            
            $this->keepLog($fields, $result, $result['error'], $success = false);
            return json_encode($ret);
        }
    }

    private function keepLog($request, $response, $error, $success) {
        //global $db;
        $db = new \sqlDb(DSN);
       // $db->query("begin");
        $arrData = array("fbar_datetime" => date(("Y-m-d H:i:s"), time()),
            "fbar_transid" => $response['TransactionId'],
            "fbar_success" => ($success) ? 1 : 0,
            "fbar_request" => json_encode($request, true),
            "fbar_response" => json_encode($response, true),
            "fbar_error" => $error);
        $db->perform("finascop_branch_api_responce", $arrData);
      //  $db->query("commit");
    }
    
    private function getKeyElements($array,&$keyElm = array()) {
        
        $db = new \sqlDb(DSN);
        foreach($array as $entrykey => $entry ){ 
            if($this->iscrossbranch===true){
                $branchid= $db->getItemFromDB("select br_id from finascop_branch where br_ReferenceID = '" .$entry['br_ReferenceID'] . "'");
                if(intval($branchid) == 0){
                    //print_r($entry);
                    $branchid = 1;
                   // throw new \Exception('JSON Error - Invalid getKeyElements cross branch - branch reference  :'.$entry['br_ReferenceID']);
                }
            }else{
                $branchid=$this->branch_id;
            }
            $CRC32Val = crc32($entry['key']);
            switch ($entry['type']){
                case 'ledgerdefaulttype':
                    $query = "SELECT accled_ReferenceId FROM finascop_accounts_ledgertype_default   INNER JOIN finascop_accounts_ledgertype
                    ON finascop_accounts_ledgertype.ledgertypedefaultid = finascop_accounts_ledgertype_default.ledgertypedefaultid  INNER JOIN finascop_accounts_ledger  ON finascop_accounts_ledgertype.ledgertypeid = finascop_accounts_ledger.ledgertypeid WHERE ledt_referenceID = '{$entry['key']}' AND ledt_referenceIDCRC32 = {$CRC32Val} AND accled_BranchId = {$branchid}";
                    $accled_ReferenceId = $db->getItemFromDB($query);
                    $keyElm[$entrykey]['type'] = 'ledger';
                    $keyElm[$entrykey]['key'] = $accled_ReferenceId;

                    break;
                case 'ledgertype':
                    $query = "SELECT accled_ReferenceId FROM finascop_accounts_ledgertype falt INNER JOIN finascop_accounts_ledger fal ON falt.ledgertypeid = fal.ledgertypeid   WHERE  falt_ReferenceID = '{$entry['key']}' AND falt_ReferenceIDCRC32 = {$CRC32Val}  AND accled_BranchId = {$branchid}";
                    $accled_ReferenceId = $db->getItemFromDB($query);
                    $keyElm[$entrykey]['type'] = 'ledger';
                    $keyElm[$entrykey]['key'] = $accled_ReferenceId;

                    break;
                case 'ledger':
                    $keyElm[$entrykey]['type'] = 'ledger';
                    $keyElm[$entrykey]['key'] = $entry['key'];
                    break;                    
            }
             unset($entry);
        }
          return $keyElm; 
    }
    
    private function getKeyTypes($apikeys){
        $db = new \sqlDb(DSN);
        $groups =  array();

        foreach($apikeys as $key => $val){ 
            $CRC32Val = crc32($val['key']);
            switch ($val['type']){
                case 'ledgerdefaulttype':
                    $query = "SELECT Group_ID FROM finascop_accounts_ledgertype_default WHERE ledt_referenceID = '{$val['key']}' AND ledt_referenceIDCRC32 = {$CRC32Val}";
                    break;
                case 'ledgertype':
                     $query = "SELECT Group_ID FROM finascop_accounts_ledgertype WHERE falt_ReferenceID = '{$val['key']}' AND falt_ReferenceIDCRC32 = {$CRC32Val}";
                    break;
                case 'ledger':
                     $query = "SELECT Group_ID FROM finascop_accounts_ledger WHERE accled_ReferenceId = '{$val['key']}' AND accled_RefIdCRC32 = {$CRC32Val}";
                    break;  
                default:
                    // There can be three type - Ledger, LedgerType, LedgerTypeDefault. Anything else should be error.
                     throw new Exception('Invalid type, unable to process .waqu_id:'.$this->waqu_id);
            }
            
            $group = $db->getItemFromDB($query);
            array_push($groups, $group);
        }	
      return $groups;  
    }
    
    private function getEntryType($transDetails){
        /*
         *  1. If both side has Bank/Cash it should be contra entry
            2. If both sides don't have  Bank/Cash    it should be JV
            3. If Debit has Bank - BAnk REceipt, Cash it should brCash REceipt
            4. If Credit has Bank - Bank Payment. Cash - Cash Payment
         */

        $drKeys = $this->getKeyElements($transDetails['dr']);

        $crKeys = $this->getKeyElements($transDetails['cr']);
        
        $drKeyTypes = $this->getKeyTypes($drKeys);
        $crKeyTypes = $this->getKeyTypes($crKeys);
                
        $bankOrCashInDR = in_array(CASHINHAND_GROUP_ID, $drKeyTypes) || in_array(BANK_GROUP_ID, $drKeyTypes);
        $bankOrCashInCR = in_array(CASHINHAND_GROUP_ID, $crKeyTypes) || in_array(BANK_GROUP_ID, $crKeyTypes);

        if($bankOrCashInDR && $bankOrCashInCR ){
            $hasOtherGroupIDsInDr = (strlen(str_replace(BANK_GROUP_ID,'',str_replace(CASHINHAND_GROUP_ID,'',implode('',$drKeyTypes)))) > 0);
            $hasOtherGroupIDsInCr = (strlen(str_replace(BANK_GROUP_ID,'',str_replace(CASHINHAND_GROUP_ID,'',implode('',$crKeyTypes)))) > 0);
            if($hasOtherGroupIDsInDr || $hasOtherGroupIDsInCr){
                throw new Exception('Contra Entry Detected, but have entries other than bank/cash!!.waqu_id:'.$this->waqu_id);
            }
            return FINANCIAL_ACCOUNT_TYPE_CE;
        }
        
        if(!$bankOrCashInDR && !$bankOrCashInCR ){
            return FINANCIAL_ACCOUNT_TYPE_JV;
        }

        $bankInDR = in_array(BANK_GROUP_ID, $drKeyTypes);
        if($bankInDR ){
            return FINANCIAL_ACCOUNT_TYPE_BR;
        }       
        
        $cashInDR = in_array(CASHINHAND_GROUP_ID, $drKeyTypes);
        if($cashInDR){
            return FINANCIAL_ACCOUNT_TYPE_CR ;
        }        
        
        $bankInCR = in_array(BANK_GROUP_ID, $crKeyTypes);  
        if($bankInCR){
            return FINANCIAL_ACCOUNT_TYPE_BP;
        }
        
        $cashInCR = in_array(CASHINHAND_GROUP_ID, $crKeyTypes);           
        if($cashInCR){
            return FINANCIAL_ACCOUNT_TYPE_CP;
        }
        
    }
    
    private function getProcessedParticulars(&$particulars){
        $db = new \sqlDb(DSN);
        foreach($particulars as $entrykey => $entry ){ 
            if($this->iscrossbranch===true){
                $branchid= $db->getItemFromDB("select br_id from finascop_branch where br_ReferenceID = '" . $entry['br_ReferenceID'] . "'");
                if(intval($branchid) == 0){
                    $branchid = 1;
                    //throw new \Exception('JSON Error - Invalid cross branch getProcessedParticulars - branch reference  :' . $entry['br_ReferenceID']);
                }
            }else{
                $branchid=$this->branch_id;
            }
            
            $CRC32Val = crc32($entry['key']);
            $accled_ReferenceId = null;      
            switch ($entry['type']){
                case 'ledgerdefaulttype':
                    $query = "SELECT accled_ReferenceId FROM finascop_accounts_ledgertype_default   INNER JOIN finascop_accounts_ledgertype
                    ON finascop_accounts_ledgertype.ledgertypedefaultid = finascop_accounts_ledgertype_default.ledgertypedefaultid  INNER JOIN finascop_accounts_ledger  ON finascop_accounts_ledgertype.ledgertypeid = finascop_accounts_ledger.ledgertypeid WHERE ledt_referenceID = '{$entry['key']}' AND ledt_referenceIDCRC32 = {$CRC32Val} AND accled_BranchId = {$branchid}";
//                      $fields = array("tempvalue" => 'query' . $query);
//                      $db->perform('temptable',$fields);
                    $accled_ReferenceId = $db->getItemFromDB($query);
                    $particulars[$entrykey]['type'] = 'ledger';
                    $particulars[$entrykey]['id'] = $accled_ReferenceId;
                    $particulars[$entrykey]['amount'] = $particulars[$entrykey]['amt'];

                    break;
                case 'ledgertype':
                    $query = "SELECT accled_ReferenceId FROM finascop_accounts_ledgertype falt INNER JOIN finascop_accounts_ledger fal ON falt.ledgertypeid = fal.ledgertypeid   WHERE falt_ReferenceID = '{$entry['key']}' AND falt_ReferenceIDCRC32 = {$CRC32Val} AND accled_BranchId = {$branchid}";
                    $accled_ReferenceId = $db->getItemFromDB($query);
                    $particulars[$entrykey]['type'] = 'ledger';
                    $particulars[$entrykey]['id'] = $accled_ReferenceId;
                    $particulars[$entrykey]['amount'] = $particulars[$entrykey]['amt'];
                    break;
                case 'ledger':
                    $particulars[$entrykey]['type'] = 'ledger';
                    $accled_ReferenceId = $particulars[$entrykey]['id'] = $particulars[$entrykey]['key'];
                    $particulars[$entrykey]['amount'] = $particulars[$entrykey]['amt'];
                    break;                    
            }
            if($accled_ReferenceId == null){
                //throw new \Exception('Invalid '.$entry['type']. ' -- branch id ' . $branchid . ' -- apikey: '.$entry['key'].'. waqu_id:'.$this->waqu_id);
            }            
            unset($entry);
            unset($particulars[$entrykey]['amt']);
            unset($particulars[$entrykey]['key']);
        }
        
        foreach($particulars as $entrykey => $entry ){ 
                if( floatval($particulars[$entrykey]['amount']) ==0){
                    unset($particulars[$entrykey]);
                }
        }
      
//        exit(1);  
      return json_encode($particulars);
    }

    
    public function processTransactionQ(){
        $db = new \sqlDb(DSN);
        $query = "SELECT waqu_id,waqu_TransDate,waqu_comment,waqu_SourceID,waqs_id,waqu_Amount,
                        br_id,waqu_Data FROM finascop_wallet_queue WHERE waqu_IsProcessed <> 1 order by waqu_TransDate ";
        $transQitems = $db->getMulipleData($query, true);
        
        if (is_array($transQitems) || is_object($transQitems))
        {
        
        foreach ($transQitems as $transaction) {
  
        $this->waqu_id = $transaction['waqu_id'];
        $this->branch_id = $transaction['br_id'];
        $iscrossbranch = $db->getItemFromDB("SELECT waqs_IsCrossBranch FROM  finascop_wallet_queue_settings WHERE waqs_id = " . $transaction['waqs_id'] );

        if(intval($iscrossbranch)==1){
            $this->iscrossbranch=true;
        }else{
            $this->iscrossbranch=false;
        }
        
             if ($transaction['waqu_TransDate'] == '' || 1 <> (preg_match("/^([2][0-9][0-9][0-9])-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$transaction['waqu_TransDate']))){
                 // throw new \Exception('Invalid transaction date.waqu_id:'.$transaction['waqu_id']);
             }
             if ($transaction['waqu_comment'] == ''){
                // throw new \Exception('Transaction comment found blank.waqu_id:'.$transaction['waqu_id']);
             }             
             if ($transaction['waqu_SourceID'] == ''){
                // throw new \Exception('Transaction Source ID not given.waqu_id:'.$transaction['waqu_id']);
             } 
             if ($transaction['waqu_Amount'] == '' || intval($transaction['waqu_Amount']) == 0){
                 //throw new \Exception('Transaction Amount not given or equal to zero.waqu_id:'.$transaction['waqu_id']);
             } 
             if ($transaction['br_id'] == '' || intval($transaction['br_id']) <= 0){
                // throw new \Exception('Transaction branch_id not given.waqu_id:'.$transaction['waqu_id']);
             }
             if ($transaction['waqu_Data'] == ''){
                // throw new \Exception('Transaction data is blank.waqu_id:'.$transaction['waqu_id']);
             } 

            $transDetails = json_decode($transaction['waqu_Data'], true);
            
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    //no error - continue normal execution.
                    break;
                case JSON_ERROR_DEPTH:
                   // throw new \Exception('JSON Error - Maximum stack depth exceeded.waqu_id:'.$transaction['waqu_id']);
                case JSON_ERROR_STATE_MISMATCH:
                 //   throw new \Exception('JSON Error - Underflow or the modes mismatch.waqu_id:'.$transaction['waqu_id']);
                case JSON_ERROR_CTRL_CHAR:
                   // throw new \Exception('JSON Error - Unexpected control character found.waqu_id:'.$transaction['waqu_id']);
                case JSON_ERROR_SYNTAX:
                 //   throw new \Exception('JSON Error - Syntax error, malformed JSON.waqu_id:'.$transaction['waqu_id']);
                case JSON_ERROR_UTF8:
                 //   throw new \Exception('JSON Error - Malformed UTF-8 characters, possibly incorrectly encoded.waqu_id:'.$transaction['waqu_id']);
                default:
                 //   throw new \Exception('JSON Error.waqu_id:'.$transaction['waqu_id']);
            }

            $entry_type = $this->getEntryType($transDetails);
            /*
                Condition for selecting account and particulars
                If contra entry the creditor should be the account - FINANCIAL_ACCOUNT_TYPE_CE
                If  JV, the creditor can be account - FINANCIAL_ACCOUNT_TYPE_JV
                If  Receipt, the Debitor should be account - FINANCIAL_ACCOUNT_TYPE_CR / FINANCIAL_ACCOUNT_TYPE_BR
                If  Payment, the Creditor should be account - FINANCIAL_ACCOUNT_TYPE_CP / FINANCIAL_ACCOUNT_TYPE_BP
             */
            
            //echo "Upto here. Working OK! entry type:".$entry_type;
            $queueForRecon = 'false';
            switch($entry_type){
                case FINANCIAL_ACCOUNT_TYPE_CR:
                     $apikeys = $this->getKeyElements($transDetails['dr']);
                     $particulars = $this->getProcessedParticulars($transDetails['cr']);
                     break; 
                case FINANCIAL_ACCOUNT_TYPE_CP:
                     $apikeys = $this->getKeyElements($transDetails['cr']);
                     $particulars = $this->getProcessedParticulars($transDetails['dr']);
                     break; 
                case FINANCIAL_ACCOUNT_TYPE_BR:
                     $apikeys = $this->getKeyElements($transDetails['dr']);
                     $particulars = $this->getProcessedParticulars($transDetails['cr']);
                     //1. Always Queue for Reconciliation Bank Payment/Bank REceipt
                     $queueForRecon = 'true';
                     break; 
                case FINANCIAL_ACCOUNT_TYPE_BP:
                     $apikeys = $this->getKeyElements($transDetails['cr']);
                     $particulars = $this->getProcessedParticulars($transDetails['dr']);
                     //1. Always Queue for Reconciliation Bank Payment/Bank REceipt
                     $queueForRecon = 'true';
                     break; 
                case FINANCIAL_ACCOUNT_TYPE_JV:
                    $crApiKeys = $this->getKeyElements($transDetails['cr']);
                    $drApiKeys = $this->getKeyElements($transDetails['dr']);
                    
                    $crHasOneEntryOnly = (count($crApiKeys)== 1)?true:false;
                    $drHasOneEntryOnly = (count($drApiKeys)== 1)?true:false;
                    if($crHasOneEntryOnly){
                        $apikeys = $crApiKeys;
                        $JVSingleEntryOn = 'Creditor';
                        $particulars = $this->getProcessedParticulars($transDetails['dr']);                 
                    }else if($drHasOneEntryOnly){
                        $apikeys = $drApiKeys;
                        $JVSingleEntryOn = 'Debtor';
                        $particulars = $this->getProcessedParticulars($transDetails['cr']);                          
                    }else{
                        throw new \Exception('Invalid JV Entry.waqu_id:'.$transaction['waqu_id']);
                        
                    }
                     break; 
                case FINANCIAL_ACCOUNT_TYPE_CE:
                     $apikeys = $this->getKeyElements($transDetails['cr']);
                     $particulars = $this->getProcessedParticulars($transDetails['dr']);
                     break; 
            }
            
            print_r($crApiKeys);
            $date_input = getDate();  
            $requestID = 'WC-' . $transaction['waqu_TransDate'].'-'.$transaction['waqu_id']; 

            $apikeys = array_values($apikeys);
            //print_r($apikeys);
            $account = $apikeys[0]['key'];
            $totalAmount = $transaction['waqu_Amount'];
            $RefIDQuery = "SELECT br_ReferenceId as branchApiKey ,comp_ReferenceId as companyApiKey FROM finascop_branch fb 
                                INNER JOIN finascop_branch_company fbc ON fb.br_Id = fbc.br_Id 
                                INNER JOIN finascop_company fc ON  fbc.comp_id = fc.comp_id WHERE fb.br_id = {$transaction['br_id']}";
            $RefIDs =  $db->getFromDB($RefIDQuery, true);
            $comments = $transaction['waqu_comment'];
            $DoNotCrValidate = true;
            $enforceCreditLimit = true;            

            $fresult =  $this->financialtransaction($requestID, $account, $particulars, $totalAmount, $entry_type, $RefIDs, $queueForRecon, $comments,$transaction['waqu_TransDate'], $enforceCreditLimit, $JVSingleEntryOn, $DoNotCrValidate, ($iscrossbranch==1?true:false));

            $result = json_decode($fresult,true);
            
            if($result['success'] == 'true'){
                $fields = array(
                    "waqu_IsProcessed" => 1,
                    "waqu_ReceiptId" => $result['TransactionId'],
                    "waqu_Response" => $fresult,
                    "waqu_IsSuccess" => 1,
                    "waqu_ExecutedOn" => date('Y-m-d H:i:s'),
                    "waqu_QueueTypeId" => $entry_type
                );
             $db->perform('finascop_wallet_queue',$fields,'update',' waqu_id="'.$transaction['waqu_id'].'"');   
            }
            else{
                $fields = array(
                    "waqu_IsProcessed" => 1,
                    "waqu_ReceiptId" => '',
                    "waqu_Response" => $fresult,
                    "waqu_IsSuccess" => 0,
                    "waqu_ExecutedOn" => date('Y-m-d H:i:s'),
                    "waqu_QueueTypeId" => $entry_type
                );
            $db->perform('finascop_wallet_queue',$fields,'update',' waqu_id="'.$transaction['waqu_id'].'"'); 
            //throw new \Exception('Transaction Failed.'.$fresult .' waqu_id="'.$transaction['waqu_id'].'"');
			
            }
      
        }

    }
    }

    public function financialtransaction($requestID, $account, $particulars, $totalAmount, $entry_type, $RefIDs, $queueForRecon, $comments,$dateofTrans, $enforceCreditLimit = true, $JVSingleEntryOn = null, $DoNotCrValidate = true, $crossbranch =false) {

        $companyApiKey = $RefIDs['companyApiKey'];
        $branchApiKey = $RefIDs['branchApiKey'];
        $particulars = json_decode($particulars);
       
        foreach($particulars as &$data){
          // print_r($data);
          $data->amount = str_replace(",","",$data->amount );
        }
       $particulars = json_encode($particulars);
       
//       echo "Upto here. Working OK! entry type:".$entry_type;
//       exit(1);
        
        $fields = array(
            "apikey" => $companyApiKey,
            "branchkey" => $branchApiKey,
            "account" => $account,
            "particulars" => $particulars,
            "NoCreditValidation" => $DoNotCrValidate,
            "QueueForRecon" => $queueForRecon,
            "ledger_type" => $entry_type,
            "JVSingleEntryOn" => $JVSingleEntryOn,
            "amount" => str_replace(",", "", $totalAmount),
            "EnforceCreditLimit" => $enforceCreditLimit,
            "requestid" => $requestID,
            "Comments" => $comments,
            "receipt_date" => $dateofTrans,
            "crossbranch" => $crossbranch
                //"Content-Type" => "application/x-www-form-urlencoded"
        );
        //var_dump($fields);


        $apiresult = $this->CURL($fields, "wallet/financialtransaction", 'POST');
        //var_dump($apiresult);
        $result = json_decode($apiresult, true);
        //print_r($result);
        if ($result['success'] == true)
        {
            $ret['msg'] = "Transaction Successful.{$result['msg']}";
            $ret['TransactionId'] = $result['Data']['TransactionId'];
            $ret['success'] = 'true';
            $fields['method'] = 'POST';
            $fields['domain'] = $this->domain;            
            $this->keepLog($fields, $result, $error = null, $success = true);
            return json_encode($ret);
        }
        else
        {
            $ret['msg'] = "Transaction failed.{$result['msg']}";
            $ret['error'] = "Error:" . $apiresult;
            $ret['TransactionId'] = 'Failed';
            $ret['success'] = 'false';
            var_dump($apiresult);
            $fields['method'] = 'POST';
            $fields['domain'] = $this->domain;            
            $this->keepLog($fields, $apiresult, $result['error'], $success = false);
            return json_encode($ret);
        }
    }

}