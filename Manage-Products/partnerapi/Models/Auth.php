<?php

namespace Models; {

    class Auth extends ModelAbstract {

        public function GET_auth($flag, $request) {
            
        }

        public function GET_verifyKey($flag, $request) {
            if (verifyKey($request['apikey'], $request['branchkey'], null, $response))
            {
                $arrAuth['success'] = true;
                $arrAuth['msg'] = 'API Key Valid';
                $arrAuth['Data'] = array('apikey' => 'Valid');
            }
            else
            {
                $arrAuth['msg'] = $response;
                $arrAuth['Data'] = array('apikey' => 'InValid');
            }
        }

        public function verifyKey($apiKey, $branchkey, $origin, &$response) {

            $db = new \cgoSqlDB();
            $comp = $db->getFromDB('select  finascop_company.comp_id as comp_id, cmp_DefaultBranch,comp_name    from finascop_company inner join finascop_company_ip using(comp_id) where cmp_status = "Active" and comp_ReferenceId = ? and validip =?  ', array('ss', $apiKey, $_SERVER['REMOTE_ADDR']), true);

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
                $response = 'Invalid Company or your domain is not authorised for access - ' . $_SERVER['REMOTE_ADDR'];
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
	

