<?php

namespace Models; {

    class Wallet {

        public function GET_wallet($flag, $request) {
            
        }
        public function POST_addwalletqdata($flag, $request) {
            if (!isset($request))
            {
                throw new \Exception('Missing Post parameters ');
            }
            global $db;
            $db = new \sqlDb(DSN);
            
            $query = "SELECT waqs_id,waqs_Name,waqs_Configuration FROM finascop_wallet_queue_settings WHERE waqs_id = 67";
            $wqSettings = $db->getFromDB($query, true);  
            
            $qugeoOrderQuery = "SELECT quor_RefNo, quor_ItemDetails FROM qugeo_order WHERE quor_QugeoPickupDDBOrderId = '".$request['orderid'] ."'";
            $qugeoOrderDetails = $db->getFromDB($qugeoOrderQuery, true);  
            
            $request['RefNo'] = $qugeoOrderDetails['quor_RefNo'];
            $itemDetails = json_decode($qugeoOrderDetails['quor_ItemDetails'],true);
            
//            $fields = array("tempvalue" => 'itemDetails:' . $itemDetails);
//            $db->perform('temptable',$fields);

            foreach ($itemDetails as $key => $item)
            {
                $request['amount'] += ($item['rate'] * $item['count']) ;
            }
                      
            $transctionTemplate = json_decode($wqSettings['waqs_Configuration'], true);
            
            if (!array_key_exists('RefNo', $request) || !array_key_exists('amount', $request) || floatval($request['amount']) == 0 || $request['RefNo'] == '')
            {
		//print_r($request);
                throw new \Exception('Missing financial transaction details ');
            }


            $cgodb = new \cgoSqlDB();
            $util = new Utils();
            
            $keys = $db->getFromDB("SELECT comp_ReferenceId, br_ReferenceID FROM finascop_branch b 
              INNER JOIN finascop_branch_company bc ON b.br_ID = bc.br_ID
              INNER JOIN finascop_company c ON bc.comp_id = c.comp_id 
              WHERE b.br_ID = (SELECT quor_Pickupbr_id FROM qugeo_order 
                  WHERE quor_QugeoPickupDDBOrderId  = '".$request['orderid'] ."' )", true);


            $amount = round(doubleval($request['amount']), 2);
            
            $transctionTemplate['cr']['csStock']['amt'] = $amount;
            $transctionTemplate['cr']['csStock']['br_ReferenceID'] = $keys['br_ReferenceID'];
            $transctionTemplate['dr']['csStockInTransit']['amt'] = $amount;
            $transctionTemplate['dr']['csStockInTransit']['br_ReferenceID'] = $keys['br_ReferenceID'];

            $RefNo = $request['RefNo'];
            $search = array("#ID#", "#AMT#");

            $replace = array($RefNo, $amount);
            $transctionTemplate['comments'] = str_replace($search, $replace, $transctionTemplate['comments']);

            if (strcmp($transctionTemplate['comments'], '') != 0) {
              $fields = array(
                "waqu_TransDate" => date('Y-m-d'),
                "waqu_comment" => $transctionTemplate['comments'],
                "waqu_SourceID" => intval($quor_id),
                "waqs_id" => intval($wqSettings['waqs_id']),
                "waqu_Amount" => $amount,
                "br_id" => $db->getItemFromDB("select br_id from finascop_branch where br_ReferenceID = '" .$keys['br_ReferenceID'] . "'"),
                "waqu_Data" => stripslashes(json_encode($transctionTemplate))
              );
              $status = $db->perform('finascop_wallet_queue', $fields);
            }
            if($status){
              $arrAuth = array();
              $arrAuth['success'] = true;
              $arrAuth['msg'] = 'Wallet Queue updated.';
              $arrAuth['Data'] = $fields['waqu_Data'];              
            }else{
              $arrAuth = array();
              $arrAuth['success'] = false;
              $arrAuth['msg'] = 'Wallet Queue updated.';
              $arrAuth['Data'] = array();
            }

            return $arrAuth;                    
        }
    }
}