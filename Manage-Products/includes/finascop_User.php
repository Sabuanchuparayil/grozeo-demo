<?php


namespace finascop;

class User {

    private function setAPIHistory(&$SESSION) {
        $nodb = new \cgoDynamiteDB();
        $arrAPI = array();
        $arrAPI['PartitionKey'] = array('col' => 'usertype', 'val' => (int) $request['usertype']);
        $arrAPI['SortKey'] = array('col' => 'id', 'val' => (string) $SESSION->Finascop_UserId);
        $arrAPI['getAttributes'] = array('apikey');
        $rsno = $nodb->query('APISession', $arrAPI, 'getItem');
        if (isset($rsno) && count($rsno) > 0) {
            $apikey = $rsno['apikey'];
            if ($apikey != '-' && isset($apikey) && trim($apikey) !== '') {
                $arrSession = array();
                $arrSession['PartitionKey'] = array('col' => 'apikey', 'val' => $apikey);
                $arrSession['Data'] = array();
                array_push($arrSession['Data'], array('col' => 'HasLoggedOut', 'val' => 1));
                array_push($arrSession['Data'], array('col' => 'LoggedOutAt', 'val' => (int) date("YmdHis")));
                array_push($arrSession['Data'], array('col' => 'IsCleanLogout', 'val' => 0));
                $nors = $nodb->perform('APIHistory', 'update', $arrSession, $response);
            }
        }

        $apikey = sha1(microtime(true) . mt_rand(10000, 90000));
        //echo 'apikey:' . $apikey;
        $arrUpdate = array();
        $arrUpdate['Data'] = array();
        $valdate = date("YmdHis");
        $SESSION->apikey = $apikey;
        $SESSION->tstamp = $valdate;
        $validityseconds = LOGIN_KEEPALIVE_TIMEOUT;

        array_push($arrUpdate['Data'], array('col' => 'usertype', 'val' => (int) $request['usertype']));
        array_push($arrUpdate['Data'], array('col' => 'id', 'val' => (string) $SESSION->Finascop_UserId));
        array_push($arrUpdate['Data'], array('col' => 'apikey', 'val' => $apikey));
        array_push($arrUpdate['Data'], array('col' => 'validtill', 'val' => (int) (time() + $validityseconds)));
        array_push($arrUpdate['Data'], array('col' => 'lastvalidation', 'val' => (int) $valdate));
        array_push($arrUpdate['Data'], array('col' => 'extrainfo', 'val' => $extrainfo));
        array_push($arrUpdate['Data'], array('col' => 'clienttype', 'val' => (string) $request['clienttype']));
        array_push($arrUpdate['Data'], array('col' => 'clientosname', 'val' => (string) $request['clientosname']));
        array_push($arrUpdate['Data'], array('col' => 'clientosver', 'val' => (string) $request['clientosver']));
        array_push($arrUpdate['Data'], array('col' => 'clientappver', 'val' => (string) $request['clientappver']));

        $nors = $nodb->perform('APISession', 'insert', $arrUpdate, $response);

        if ($nors) {
            $arrUpdate = array();
            $valdate = date("Ymd");
            $valdatetime = date("YmdHis");
            $arrUpdate = array();
            $arrUpdate['Data'] = array();
            array_push($arrUpdate['Data'], array('col' => 'id', 'val' => (string) $SESSION->Finascop_UserId));
            //array_push($arrUpdate['Data'],array('col'=>'extrainfo','val'=>(string)($request['usertype']==2?$extrainfo['v_id']:$extrainfo['c_id']) ));
            array_push($arrUpdate['Data'], array('col' => 'usertype', 'val' => (int) $request['usertype']));
            array_push($arrUpdate['Data'], array('col' => 'apikey', 'val' => $apikey));
            array_push($arrUpdate['Data'], array('col' => 'createddatetime', 'val' => (string) $valdatetime));
            array_push($arrUpdate['Data'], array('col' => 'createddate', 'val' => (int) $valdate));
            array_push($arrUpdate['Data'], array('col' => 'HasLoggedOut', 'val' => 0));
            array_push($arrUpdate['Data'], array('col' => 'IP', 'val' => $_SERVER['REMOTE_ADDR']));
            /* array_push($arrUpdate['Data'],array('col'=>'clienttype','val'=>(string)$request['clienttype'] ));
              array_push($arrUpdate['Data'],array('col'=>'clientosname','val'=>(string)$request['clientosname'] ));
              array_push($arrUpdate['Data'],array('col'=>'clientosver','val'=>(string)$request['clientosver'] ));
              array_push($arrUpdate['Data'],array('col'=>'clientappver','val'=>(string)$request['clientappver'] )); */

            $nors = $nodb->perform('APIHistory', 'insert', $arrUpdate, $response);
            if ($nors) {
                $arrAuth['success'] = true;
                /* $_SESSION["loginid"]  = ($request['usertype']==1?$extrainfo['c_id']:$rd['Id']);	
                  $_SESSION["usertype"]  = $request['usertype']; */
                $arrAuth['msg'] = 'API Key Generated';
                $arrAuth['Data']['apikey'] = $apikey;
                $arrAuth['Data']['Name'] = $rd['Name'];
                if ($request['usertype'] == 1) {
                    $arrAuth['Data']['Mobile'] = $rd['Mobile'];
                    $arrAuth['Data']['Icon'] = $rd['Icon'];
                }
            } else {
                $arrAuth['msg'] = 'API Key Generation failed for APIHistory';
                $arrAuth['Data']['APIHistory'] = '';
            }
            /* }else{
              $arrAuth['msg'] = 'API Key Generation failed  for '. $table;
              $arrAuth['Data']='';
              } */
        } else {
            $arrAuth['msg'] = 'API Key Generation failed for APISession';
            $arrAuth['Data'] = '';
        }
        global $db;
        $db->query("update " . FINASCOP_DB . "finascop_user_details set apikey = '" . $apikey . "' where UserId = " . $SESSION->Finascop_UserId . ";");
    }

    private function setAPIKey(&$SESSION) {
        global $db;
        $apikey = sha1(microtime(true) . mt_rand(10000, 90000));
        $db->query("update " . FINASCOP_DB . "finascop_user_details set apikey = '" . $apikey . "' where UserId = " . $SESSION->Finascop_UserId . ";");
    }

    private function validateSession($SESSION) {
        return ($SESSION->IsSuperUser == 'Yes' || $SESSION->finascop_typId != 0 );
    }

    private function validateCompany($user_companies) {
        global $db;
        if ((isset($user_companies) && is_array($user_companies) ? (empty($user_companies) ? false : true) : false)) {
            $count = 0;
            foreach ($user_companies as $comapny) {
                if ($comapny <= 0) {
                    return false;
                } else {
                    $qry = "SELECT count(*) from " . FINASCOP_DB . "finascop_company WHERE comp_id = {$comapny}";
                    $comapnycount = $db->getItemFromDB($qry);
                    if ($comapnycount == 0) {
                        return false;
                    }
                }
                $count = $count + 1;
            }
            if ($count == 0) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    private function validateBranch($user_branches) {
        global $db;
        if ((isset($user_branches) && (is_array($user_branches) ? (empty($user_branches) ? false : true) : false))) {
            $count = 0;
            foreach ($user_branches as $branch) {
                if ($branch <= 0) {
                    return false;
                } else {
                    $qry = "SELECT count(*) from " . FINASCOP_DB . "finascop_branch WHERE br_ID = {$branch}";
                    $branchcount = $db->getItemFromDB($qry);
                    if ($branchcount == 0) {
                        return false;
                    }
                    $qry = "SELECT count(*) from " . FINASCOP_DB . "finascop_branch_company inner join finascop_company using(comp_id) WHERE br_ID = {$branch}";
                    $branchcount = $db->getItemFromDB($qry);
                    if ($branchcount == 0) {
                        return false;
                    }
                }
                $count = $count + 1;
            }
            if ($count == 0) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    private function checkUserTypeIntegration($User_type, $user_companies, $user_branches) {

        $this->isUserTypeMapped($User_type);

        $finascop_user_type = $this->getFinascopUserType($User_type);
        //echo "finascop_user_type -- " . $finascop_user_type . "\n";
        define("ADMIN", 1, true);
        define("CORPORATE", 2, true);
        define("BRANCH", 3, true);
        define("AUDITOR", 4, true);
        switch (intval($finascop_user_type)) {
            case 1:
                break;
            case 2:
                if (!$this->validateCompany($user_companies)) {
                    echo "{success:false,errors: 'FINASCOP:Received invalid company id as parameter.' }";
                    exit();
                }

                break;
            case 3:
            case 4:
                if (!$this->validateBranch($user_branches)) {
                    echo "{success:false,errors:'FINASCOP:Received Invaild branch id as parameter.' }";
                    exit();
                }
                break;
            default:
                echo "{success:false,errors:'FINASCOP:Invalid User Type.' }";
                exit();
                break;
        }
    }

    private function getFinascopUserType($User_Type) {
        global $db;
        if (IS_FINASCOP_PROJECT != true) {
            $qry = "SELECT finascop_user_type_id from " . FINASCOP_DB . "finascop_project_user_type_mapping WHERE project_user_type_id = {$User_Type}";
            $finascopUserType = $db->getItemFromDB($qry);
        } else {
            $finascopUserType = $User_Type;
        }

        return $finascopUserType;
    }

    private function isUserTypeMapped($User_Type) {
        global $db;
        if (IS_FINASCOP_PROJECT != true) {
            $qry = "SELECT count(*) from " . FINASCOP_DB . "finascop_project_user_type_mapping WHERE project_user_type_id = {$User_Type}";
            $userTypeToFinascopMapped = $db->getItemFromDB($qry);
            if ($userTypeToFinascopMapped == 0) {
                echo "{success:false,errors: 'FINASCOP:Please map User type to finascop User Type.'}";
                exit(1);
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    private function saveDetails($profile, $master, $userID) {
        global $db;
        if (!array_key_exists("enable_autoapproval", $profile)) {
            echo "{success:false,errors: { msg: 'FINASCOP: Enable/disable auto approval not specified' }}";
            exit();
        }
        if (!array_key_exists("isanapprover", $profile)) {
            echo "{success:false,errors: { msg: 'FINASCOP: Whether user is an approver or not is not specified' }}";
            exit();
        }
        if (empty(trim($master['report_to_user'])) || !array_key_exists("report_to_user", $master)) {
             $master['report_to_user']=0;
        }
        $db->query("replace into " . FINASCOP_DB . "finascop_user_details(UserId,IsAutoApprovalEnabled,IsAnApprover,report_to_user) values "
                . "(" . $userID . "," . $profile['enable_autoapproval'] . "," . $profile['isanapprover'] . ","
                . $master['report_to_user'] . ")");
        return $status;
    }

    private function saveUserCredentials($profile, $userID, $cmp_br_relations, $selfprofile_update, $User_type) {

        $this->checkUserTypeIntegration($User_type, $cmp_br_relations['user_company'], $cmp_br_relations['user_branch']);
        $this->saveDetails($profile, $cmp_br_relations, $userID);
        if ($selfprofile_update == 0) {
            $this->configureUserComapanyAndBranch($User_type, $cmp_br_relations, $userID);
        }
    }

    public function saveUser($profile, $user, $Finascop_userdetails, $UserId, $IsAddNew, $IsFinascopIntegration = true) {
        global $db;
        if (!array_key_exists("FirstName", $profile)) {
            echo "{success:false,errors: { msg: 'FINASCOP: FirstName parameter missing' }}";
            exit();
        }
        if (!array_key_exists("LastName", $profile)) {
            echo "{success:false,errors: { msg: 'FINASCOP: LastName parameter missing' }}";
            exit();
        }
        if (!array_key_exists("user_type", $Finascop_userdetails)) {
            echo "{success:false,errors: { msg: 'FINASCOP: user_type parameter missing in Finascop_userdetails' }}";
            exit();
        }
        if (!array_key_exists("UserName", $user)) {
            echo "{success:false,errors: { msg: 'FINASCOP: UserName parameter missing' }}";
            exit();
        }
        if ($IsFinascopIntegration) {
            if (intval($UserId) == 0) {
                echo "{success:false,errors: { msg: 'FINASCOP: User Id is invalid' }}";
                exit();
            }
            $tmpuser = $user;
            $user = array();
            $user['UserId'] = $UserId;
            $user['UserName'] = $tmpuser['UserName'];
            $tmpprofile = $profile;
            $profile = array();
            $profile['UserId'] = $UserId;
            $profile['FirstName'] = $tmpprofile['FirstName'];
            $profile['LastName'] = $tmpprofile['LastName'];
            $profile['typId'] = $this->getFinascopUserType($Finascop_userdetails['user_type']);
        }
        if (intval($UserId) == 0 || ($IsAddNew && $IsFinascopIntegration)) {
            //UserID is empty, insert data into Database otherwise update
            $user_status = $db->perform(FINASCOP_DB . "finascop_usr_master", $user);
            $UserId = $db->insert_id();
            $profile['UserId'] = $UserId;
            $profile['CreatedOn'] = 'now()';
            $profile_status = $db->perform(FINASCOP_DB . "finascop_usr_profile", $profile);
        } else {
            //$UserId = $user['UserId'];
            $user_status = $db->perform(FINASCOP_DB . "finascop_usr_master", $user, 'update', "UserId='" . $UserId . "'");
            $profile_status = $db->perform(FINASCOP_DB . "finascop_usr_profile", $profile, 'update', "UserId='" . $UserId . "'");
        }
        $this->saveUserCredentials($Finascop_userdetails, $UserId, $Finascop_userdetails['cmp_br'], $Finascop_userdetails['profile_update'], $Finascop_userdetails['user_type']);
        return $UserId;
    }

    private function configureUserComapanyAndBranch($User_type, $cmp_br_relations, $UserId) {
        global $db;

        $db->query("DELETE from " . FINASCOP_DB . "`finascop_user_activebranches` WHERE `UserId` = {$UserId}");
        $db->query("DELETE from " . FINASCOP_DB . "`finascop_user_activecompanies` WHERE `UserId` = {$UserId}");
        $db->query("DELETE from " . FINASCOP_DB . "`finascop_user_auditingbranches` WHERE `UserId` = {$UserId}");

        $finascop_user_type = $this->getFinascopUserType($User_type);

        define("ADMIN", 1, true);
        define("CORPORATE", 2, true);
        define("BRANCH", 3, true);
        define("AUDITOR", 4, true);
        switch ($finascop_user_type) {
            case ADMIN:
                break;
            case BRANCH:

                $active_branch = $cmp_br_relations['user_branch'];

                if (!empty($active_branch)) {
                    //$active_branch_arr = explode(',', $active_branch);

                    foreach ($active_branch as $value) {
                        $user_activebranches['br_Id'] = $value;
                        $user_activebranches['UserId'] = $UserId;
                        $db->perform(FINASCOP_DB . 'finascop_user_activebranches', $user_activebranches);
                    }
                } else {
                    echo "{success:false,errors: { reason: 'FINASCOP:Please add user active branches ' }}";
                    exit(1);
                }
            //break; need to add company details also
            case CORPORATE:

                $active_companies = $cmp_br_relations['user_company'];
                if (!empty($active_companies)) {
                    foreach ($active_companies as $cmp_value) {
                        $user_activecompanies['comp_Id'] = $cmp_value;
                        $user_activecompanies['UserId'] = $UserId;
                        $db->perform(FINASCOP_DB . 'finascop_user_activecompanies', $user_activecompanies);
                    }
                } else {
                    echo "{success:false,errors: { reason: 'FINASCOP:Please add user companies.' }}";
                    exit(1);
                }

                break;

            case AUDITOR:

                $auditor_branch = $cmp_br_relations['user_branch'];
                if (!empty($auditor_branch)) {
                    //$auditor_branch_arr = explode(',', $auditor_branch);
                    foreach ($auditor_branch as $value) {
                        $user_auditingbranches['br_Id'] = $value;
                        $user_auditingbranches['UserId'] = $UserId;
                        $db->perform(FINASCOP_DB . 'finascop_user_auditingbranches', $user_auditingbranches);
                    }
                } else {
                    echo "{success:false,errors: { reason: 'FINASCOP:Please add auditing branches.' }}";
                    exit(1);
                }
                break;
            default:
                break;
        }


        $ur_up = array('finascop_current_branch_id' => 0, 'finascop_current_company_id' => 0);
        $db->perform(FINASCOP_DB . 'finascop_user_details', $ur_up, 'update', "UserId='" . $UserId . "'");
    }

    public function getReportingToCount($userID) {
        global $db;
        $qryCount = "select count(report_to_user) from " . FINASCOP_DB . "finascop_user_details WHERE report_to_user = {$userID} ";
        $totalCount = $db->getItemFromDB($qryCount);
        return $totalCount;
    }

    public function getDetails(&$tmp, $id) {
        global $db;
        $qry = "SELECT fud.IsAutoApprovalEnabled AS e_autoapproval,fud.IsAnApprover as e_approver "
                . "from " . FINASCOP_DB . "finascop_user_details fud WHERE fud.UserId = {$id}";
        $rs = $db->getFromDB($qry, true);
        $tmp['e_autoapproval'] = $rs['e_autoapproval'];
        $tmp['e_approver'] = $rs['e_approver'];
    }

    public function setCompanyAndBranchInSession(&$SESSION, $data) {
        global $db;
        if (!isset($data['current_company']) || !isset($data['current_branch'])) {
            echo "{success:false,errors: { msg:'FINASCOPE:Fatal Error, Please pass \$data['current_company']' & \$data['current_branch']  }}";
            exit(1);
        }
        $SESSION->finascop_current_company_id = $data['current_company'];
        $SESSION->finascop_current_branch_id = $data['current_branch'];
        /* $SESSION->finascop_current_company = $data['cmp_name']; */
        $SESSION->finascop_current_company = $db->getItemFromDB("SELECT comp_shortname "
                . "from " . FINASCOP_DB . "finascop_company WHERE comp_id = {$data['current_company']}");
        $SESSION->current_branch = $db->getItemFromDB("SELECT branch_shortname from " . FINASCOP_DB . "finascop_branch WHERE br_ID = {$data['current_branch']}");
        //pyramid Level
        $SESSION->br_PyramidLevel = $db->getItemFromDB("SELECT br_PyramidLevel from " . FINASCOP_DB . "finascop_branch WHERE br_ID = {$SESSION->finascop_current_branch_id}");
        $SESSION->finascop_current_company_isactive = $db->getItemFromDB("SELECT if(cmp_status='Active',1,0) as IsActive "
                . "from " . FINASCOP_DB . "finascop_company WHERE comp_id = {$SESSION->finascop_current_company_id}");
        $SESSION->finascop_current_branch_isactive = $db->getItemFromDB("SELECT if(br_status='Active',1,0) as IsActive from " . FINASCOP_DB . "finascop_branch WHERE br_ID = {$SESSION->finascop_current_branch_id}");

        $last = array('finascop_current_company_id' => $SESSION->finascop_current_company_id, 'finascop_current_branch_id' => $SESSION->finascop_current_branch_id);
        $userDetailsExist = $db->getItemFromDB("SELECT COUNT(UserId) FROM " . FINASCOP_DB . "finascop_user_details c WHERE c.UserId = {$SESSION->Finascop_UserId}");
        if ($userDetailsExist > 0) {
            $db->perform(FINASCOP_DB . "finascop_user_details", $last, "update", "UserId = " . $SESSION->Finascop_UserId);
        } else {
            $last['UserId'] = $SESSION->Finascop_UserId;
            $db->perform(FINASCOP_DB . "finascop_user_details", $last);
        }
    }

    public function getReportToUser($userID) {
        global $db;
        return $db->getItemFromDB("SELECT IF(report_to_user=0,'',report_to_user)AS report_to_user  from " . FINASCOP_DB . "finascop_user_details WHERE UserId = {$userID}");
    }

    public function additionalLoginActions(&$SESSION, $IsAppLogin, $user_id, $typid) {
        global $db;
        $SESSION->Finascop_UserId = $user_id;
        $SESSION->finascop_typId = $this->getFinascopUserType($typid);
        if (!$this->validateSession($SESSION)) {
            echo "{success:false,errors: { reason:'FINASCOPE:Fatal Error, Please check User type' }}";
            exit(1);
        }

        if ($IsAppLogin == true) {
            $_SESSION['admin']->IsApplicationLogin = 1;
        } else {
            $_SESSION['admin']->IsApplicationLogin = 0;
        }

        if ($IsAppLogin == true) {
            $query = "SELECT finascop_current_company_id,finascop_current_branch_id FROM " . FINASCOP_DB . "finascop_usr_master a INNER join " . FINASCOP_DB . "finascop_user_details c ON a.UserId = c.UserId WHERE  a.IsAppUser = 1  LIMIT 1";
        } else {
            $query = "SELECT finascop_current_company_id,finascop_current_branch_id,IsAutoApprovalEnabled from " . FINASCOP_DB . "finascop_user_details c WHERE c.UserId = " . $user_id;
        }
        //echo $query;
        $rs = $db->getFromDB($query, TRUE);
        //echo 'finascop_user_details' . $rs;

        if (count($rs) != 0) {
            // $rs = (object) $rs;
            foreach ($rs as $k => $v) {
                $SESSION->$k = $v;
            }
        }

        if (!empty($SESSION->finascop_current_company_id) && $SESSION->finascop_current_company_id > 0) {
            $SESSION->finascop_current_company = $db->getItemFromDB("SELECT comp_shortname "
                    . "from " . FINASCOP_DB . "finascop_company WHERE comp_id = {$SESSION->finascop_current_company_id}");
            $SESSION->finascop_current_company_isactive = $db->getItemFromDB("SELECT if(cmp_status='Active',1,0) as IsActive "
                    . "from " . FINASCOP_DB . "finascop_company WHERE comp_id = {$SESSION->finascop_current_company_id}");
        }

        if (!empty($SESSION->finascop_current_branch_id) && $SESSION->finascop_current_branch_id > 0) {
            $SESSION->current_branch = $db->getItemFromDB("SELECT branch_shortname from " . FINASCOP_DB . "finascop_branch WHERE br_ID = {$SESSION->finascop_current_branch_id}");
            $SESSION->finascop_current_branch_isactive = $db->getItemFromDB("SELECT if(br_status='Active',1,0) as IsActive from " . FINASCOP_DB . "finascop_branch WHERE br_ID = {$SESSION->finascop_current_branch_id}");
            //pyramid Level
        $SESSION->br_PyramidLevel = $db->getItemFromDB("SELECT br_PyramidLevel from " . FINASCOP_DB . "finascop_branch WHERE br_ID = {$SESSION->finascop_current_branch_id}");
        }

        /* check whether the user is auditor of current branch */
        $SESSION->is_auditor = ($SESSION->finascop_typId == 4 ? 1 : 0);

        $this->setAPIHistory($SESSION);
        //$this->setAPIKey($SESSION);

        /* have multiple company and branch mapping */
        $SESSION->Finascop_ActiveAcctsSwitch = true;
        if ($user_id > 1) {
            if ($SESSION->finascop_typId == 4) {
                //$SESSION->Finascop_ActiveAcctsSwitch = false; Seems auditor too needs switching current company!!!!
                $SESSION->Finascop_ActiveAcctsSwitch = true;
            } else {
                $SESSION->Finascop_ActiveAcctsSwitch = true;
            }
        }

        switch ($SESSION->finascop_typId) {
            case 1:
                $qry = "SELECT count(*) as cnt from " . FINASCOP_DB . "finascop_branch "
                        . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company`) ";
                $SESSION->UserType = "Admin";
                break;
            case 2:
                $qry = "SELECT count(*) as cnt from " . FINASCOP_DB . "finascop_branch"
                        . " WHERE br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE comp_id IN( SELECT comp_id  from " . FINASCOP_DB . "finascop_user_activecompanies WHERE UserId = {$user_id})) ";
                $SESSION->UserType = "Corporate";
                break;
            case 3:
                $qry = "SELECT count(*) as cnt from " . FINASCOP_DB . "finascop_company 
				WHERE  comp_id IN( SELECT comp_id  from " . FINASCOP_DB . "finascop_branch_company 
				WHERE br_Id IN(SELECT br_Id from " . FINASCOP_DB . "finascop_user_activebranches WHERE UserId = {$user_id})) ";
                $SESSION->UserType = "Branch";
                break;
            case 4:
                $qry = "SELECT COUNT(*) as cnt from " . FINASCOP_DB . "finascop_user_auditingbranches  WHERE UserId = {$user_id}  ";
                $SESSION->UserType = "Auditor";
                break;
            default:
                $qry = "SELECT 0 as cnt ";
                $SESSION->UserType = "Super";
        }
        //		echo $qry;
        //		exit;
        $SESSION->AssignedBranchCount = $db->getItemFromDB($qry);
    }

    public function getUserActiveComapnies($Finascop_typId,$Finascop_UserId) {
        global $db;
        switch ($Finascop_typId) {
            case 1:
                $qry = "SELECT comp_id AS id, comp_name AS `name` from " . FINASCOP_DB . "finascop_company 
				WHERE  comp_id IN( SELECT comp_id  from " . FINASCOP_DB . "finascop_branch_company ) ";
                break;
            case 2:
                $qry = "SELECT comp_id AS id, comp_name AS `name` from " . FINASCOP_DB . "finascop_company 
				WHERE  comp_id IN( SELECT comp_id  from " . FINASCOP_DB . "finascop_user_activecompanies WHERE UserId = {$Finascop_UserId}) ";
                break;
            case 3:
                $qry = "SELECT comp_id AS id, comp_name AS `name` from " . FINASCOP_DB . "finascop_company 
				WHERE  comp_id IN( SELECT comp_id  from " . FINASCOP_DB . "finascop_branch_company 
				WHERE br_Id IN(SELECT br_Id from " . FINASCOP_DB . "finascop_user_activebranches WHERE UserId = {$Finascop_UserId})) ";
                break;
            case 4:
                $qry = "SELECT distinct finascop_company.comp_id AS id, finascop_company.comp_name AS `name` from " . FINASCOP_DB . "finascop_company inner join " . FINASCOP_DB . "finascop_branch_company on finascop_branch_company.comp_id = finascop_company.comp_id  
				WHERE  br_id IN( SELECT br_id  from " . FINASCOP_DB . "finascop_user_auditingbranches WHERE UserId = {$Finascop_UserId}) ";
                break;
            default:
                $qry = "SELECT comp_id AS id, comp_name AS `name` from " . FINASCOP_DB . "finascop_company 
				WHERE  comp_id IN( SELECT comp_id  from " . FINASCOP_DB . "finascop_branch_company ) ";
        }
        $qry .= " ORDER BY `name` ASC";

       // finascop_getjsonkeyarray($qry);
	   return $db->getMultipleData($qry);
	   
    }

    public function getUserActiveBranches($Finascop_typId,$Finascop_UserId,$CompanyId,$allbranch=false) {
		        global $db;
		if(!$allbranch){
				$compidstr = " and comp_id= {$CompanyId} ";
		}
        switch ($Finascop_typId) {
            case 1:
                $qry = "SELECT br_ID AS id, br_Name AS `name` from " . FINASCOP_DB . "finascop_branch"
                        . " WHERE br_status = 'Active' AND br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE 1=1  {$compidstr} )";
                break;
            case 2:
                $qry = "SELECT br_ID AS id, br_Name AS `name` from " . FINASCOP_DB . "finascop_branch"
                        . " WHERE br_status = 'Active' AND br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE 1=1  {$compidstr} )";
                break;
            case 3:
                $qry = "SELECT br_ID AS id, br_Name AS `name` from " . FINASCOP_DB . "finascop_branch"
                        . " WHERE br_status = 'Active' AND br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE 1=1  {$compidstr}) AND "
                        . " br_ID IN (SELECT br_Id from " . FINASCOP_DB . "finascop_user_activebranches WHERE UserId = {$Finascop_UserId})";
                break;
            case 4:
                $qry = "SELECT distinct finascop_branch.br_ID AS id, finascop_branch.br_Name AS `name` from " . FINASCOP_DB . "finascop_branch inner join " . FINASCOP_DB . "finascop_branch_company on finascop_branch_company.br_Id = finascop_branch.br_Id "
                        . " WHERE  1=1  br_status = 'Active' AND {$compidstr} and finascop_branch.br_Id IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_user_auditingbranches` WHERE   UserId = {$Finascop_UserId} ) ";
                break;
            default:
                $qry = "SELECT br_ID AS id, br_Name AS `name` from " . FINASCOP_DB . "finascop_branch"
                        . " WHERE br_status = 'Active' AND br_ID IN (SELECT DISTINCT br_Id from " . FINASCOP_DB . "`finascop_branch_company` WHERE 1=1  {$compidstr} )";
        }
        $qry .= " ORDER BY `name` ASC";
		
        return $db->getMultipleData($qry);
    }

}
