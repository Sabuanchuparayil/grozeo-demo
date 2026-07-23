<?php

namespace Models; {

    class Auth extends ModelAbstract {

        public function GET_auth($flag, $request) {
            
        }

        public function GET_verifyKey($flag, $request) {
          
          $response = null;

            if (verifyKey($request['orderid'],  null, $response))
            {
                $arrAuth['success'] = true;
                $arrAuth['msg'] = 'Order ID Key Valid';
                $arrAuth['Data'] = array('orderid' => 'Valid');
            }
            else
            {
                $arrAuth['msg'] = $response;
                $arrAuth['Data'] = array('orderid' => 'InValid');
            }
        }

        public function verifyKey($orderid, $origin, &$response) {
          $db = new \cgoSqlDB();
          $sqldb = new \sqlDb(DSN);
          $keys = $sqldb->getFromDB("SELECT comp_ReferenceId, br_ReferenceID FROM finascop_branch b 
            INNER JOIN finascop_branch_company bc ON b.br_ID = bc.br_ID
            INNER JOIN finascop_company c ON bc.comp_id = c.comp_id 
            WHERE b.br_ID = (SELECT quor_Pickupbr_id FROM qugeo_order 
            WHERE quor_QugeoPickupDDBOrderId  = '".$orderid ."' )", true);
          
          $apiKey = $keys['comp_ReferenceId'];
          $request['branchkey'] = $branchkey = $keys['br_ReferenceID'];
          

            $comp = $db->getFromDB('select  finascop_company.comp_id as comp_id, cmp_DefaultBranch,comp_name from finascop_company inner join finascop_company_ip using(comp_id) where cmp_status = "Active" and comp_ReferenceId = ? and validip =?  ', array('ss', $apiKey, $_SERVER['REMOTE_ADDR']), true);

            if (intval($comp['comp_id']) > 0)
            {
                $branch = $db->getFromDB('select  br_ID,br_Name from finascop_branch inner join finascop_branch_company using(br_Id) where  br_ReferenceID = ? and comp_id =? ', array('ss', $branchkey, $comp['comp_id']), true);
                if (intval($branch['br_ID']) > 0)
                {
                    $_SESSION["compid"] = $comp['comp_id'];
                    $_SESSION["company"] = $comp['comp_name'];
                    $_SESSION["defaultbranch"] = $branch['br_ID'];
                    return true;
                }
                else
                {
                    $response = 'Branch is missing';
                    return false;
                }
            }
            else
            {
                $response = 'Invalid Company '.$apiKey.' or your domain is not authorised for access - ' . $_SERVER['REMOTE_ADDR'];
                return false;
            }
        }

        public function GET_testapi($flag, $request) {
            $arrAuth = array();
            $arrAuth['success'] = true;
            $arrAuth['msg'] = 'Testing OK';
            $arrAuth['Data'] = array();
            return $arrAuth;
        }

    }

}
	

