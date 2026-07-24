<?php

define('LIB_INCLUDED', TRUE);

/**
 * Determine whether the user has privilege for the given operation.
 *
 * Created on 24-Jul-08
 * @author : Ratheesh Kumar CK <ratheesh@saturn.in>
 *
 * @param $string
 *   The module name, such as "auth, ui,etc.", being requested for.
 * @param $string
 *   The operation name, such as "login, showform,etc.", being requested for
 *   under the given Module.
 * @param $account
 *   (optional) The account to check, if not given use currently logged in user.
 *
 * @return
 *   boolean TRUE if the current user has the requested permission.
 *
 * All permission checks in the FrameWork should go through this function. This
 * way, we guarantee consistent behavior, and ensure that the superuser
 * can perform all actions.
 */
function user_access($module, $operation = DEFAULT_OPERATION, $account = NULL) {
    global $_SESSION, $db;
    static $perm = array();

    if ($operation === FALSE)
        $operation = DEFAULT_OPERATION;

    if (is_null($account)) {
        $account = $_SESSION['admin'];
    }

    // Admin_User has all privileges:
    if ($account->IsSuperUser == "Yes") {
        return TRUE;
    }

    //Get the Required Capability from Systerm_module_operation table
    $capability = "";
    if (empty($operation)) {
        $capability = $db->getItemSafe("select if(coalesce(Capability,'')='',0,OperationId) as Capability from sys_module_operation where ModuleName=?", "s", [$module]);
    } else {
        $capability = $db->getItemSafe("select if(coalesce(Capability,'')='',0,OperationId) as Capability from sys_module_operation where ModuleName=? and Operation=?", "ss", [$module, $operation]);
    }

    if ($capability === null || $capability === false) {
        return FALSE;
    }

    if ($capability == '0') {
        return FALSE;
    }
    //Check if more than one capability can have for one operation
    $capability = explode(PERM_SEPERATOR, $capability);

    if (count($capability) > 0) {
        for ($i = 0; $i < count($capability); $i++) {
            //Return whether the given account has privilege to do the operation
            if (in_array($capability[$i], $account->perms))
                return TRUE;
        }
    }else {
        //Return whether the given account has privilege to do the operation
        if (in_array($capability, $account->perms))
            return TRUE;
    }

    //If no capability is mentioned for a particular module,
    //We will consider it as a public module, everyone can access
    //if (empty($capability)) return TRUE;
    return FALSE;
}

/**
 * Fetch the list of Capabilities and Menus needs to be blocked for the logged in User
 *
 * Created on 26-Aug-08
 * @author : Ratheesh Kumar CK <ratheesh@saturn.in>
 *
 * @param $int
 *   ID of the logged in user
 *
 * @return $Array
 *   array 	A multidimensional array which holds the array of Capabilities and
 *   		Array of Menu IDs needs to be restricted
 *
 * This is only required once, when building UI.
 */
function blocked_capabilities($accountID) {
    global $db;

    $qry = "select group_concat(SysModOpId SEPARATOR ';') as Capability from usr_blocked_capability where UserId='" . $accountID . "'";
    $blocked_capabilities = $db->getItemFromDB($qry);
    $blocked_capabilities = explode(PERM_SEPERATOR, (string)($blocked_capabilities ?? ''));
    $blocked_menus = array();

    if (count($blocked_capabilities) > 0)
        $blockedList = "'" . join("','", $blocked_capabilities) . "'";
    $qry = "SELECT MenuId FROM sys_module_operation WHERE OperationId in ($blockedList) AND (capability <> NULL OR capability<>'')";
    $blocked_menus = $db->getMultipleData($qry, FALSE);

    $blocked = array(
        "menus" => $blocked_menus,
        "capabilities" => $blocked_capabilities
    );

    return $blocked;
}

/**
 * Shows Access Denied Message or Related Process
 */
function access_denied($module, $op) {
    echo '{"success":false,"error":"Access Denied","Addinfo": "' . $module . ':' . $op . '"}';
}

/**
 * Create User Object (same as what loaded into session after login)
 * based on the given UserID. If no UserID is passed as argument,
 * object from current session will be returned.
 *
 * Created on 05-Mar-09
 * @author : Ratheesh Kumar CK <ratheesh@saturn.in>
 *
 * @param $int
 *   UserID - same as primary Key of User Table.
 *
 * @return  $OBJECT
 *   StdObject contains users data like whats is stored in session
 *
 * This method is mainly used for creating user object for a particular UserID
 * than from the current session.
 * Mainly applicable in backend process: create js Cache files
 */
if (!function_exists('createUserObj')) {

    function createUserObj($userID = NULL) {
        global $_SESSION, $db;

        if (is_null($userID))
            return $_SESSION['admin'];

        $query = "select * from " . FINASCOP_DB . "finascop_usr_master a, " . FINASCOP_DB . "finascop_usr_profile b where a.UserId = b.UserId and a.UserId = " . $userID;
        $user = (object) $db->getFromDB($query, true);

        if (sizeof($user) == 0)
            return FALSE;
        else {
            $roleName = $db->getItemFromDB('select RoleName from sys_role where RoleId = ' . $user->RoleId);

            //Get the Permissions Allowed to this User and store it in a session
            $qry = "select group_concat(r.SysModOpId SEPARATOR ';') as role_perms, group_concat(c.SysModOpId SEPARATOR ';') as user_perms from " . FINASCOP_DB . "finascop_usr_master u left join sys_role_capability r on (u.RoleId=r.RoleId) left join usr_capability c on (c.UserId=u.UserId) where u.UserId='" . $user->UserId . "'";
            $rd = $db->getFromDB($qry, true);
            $rd['role_perms'] = explode(PERM_SEPERATOR, $rd['role_perms']);
            $rd['user_perms'] = explode(PERM_SEPERATOR, $rd['user_perms']);
            $user->perms = array_merge($rd['role_perms'], $rd['user_perms']);

            //Get the list of Menus needs to be blocked
            $blocked = blocked_capabilities($user->uidnr_admin);

            while (list($key, $capability) = each($user->perms)) {
                if (in_array($capability, $blocked['capabilities'])) {
                    unset($user->perms[$key]);
                    continue;
                }

                $user->perms[$key] = str_replace("'", "", $capability);
            }

            return $user;
        }
    }

}



/**
 * Returns a set of Users having given capability
 *
 * Created on 06-mar-09
 * @author : Ratheesh Kumar CK <ratheesh@saturn.in>
 *
 * @param $string
 *   Capability need to use for searching
 * @param $array
 *   Array of User IDs, if this array is given, the search will be
 *   limited to these users IDs only.
 * @param $boolean
 *   Indicate wheterwe need to include supers users in search or not
 *   Super Users will have all the permission
 *
 * @return $array
 *   Set of UserIDs, who has given permission
 *
 * Use this function to get a list of users who has given permission.
 * The system will check in User capabilities, Role Capabilities and in WO Capabilities
 * */
if (!function_exists('getUsersByCapability')) {

    function getUsersByCapability($capability, $users_in = FALSE, $include_super = FALSE) {
        global $db;
        $capable_users = array();

        if (is_array($users_in))
            $conditions = ' AND u.uidnr_admin in (' . join(',', $users_in) . ')';
        else
            $conditions = "";

        //#1: Include Super Users if Specified
        if ($include_super) {
            $query = 'SELECT u.uidnr_admin FROM admin_users u WHERE admin_super = 1 AND admin_active = 1' . $conditions;
            $capable_users = $db->getMultipleData($query, FALSE);
        }
        //End#1;
        //#2: Check in Role Capabilities Table
        $query = 'SELECT u.uidnr_admin FROM admin_role_capability rc, admin_users u WHERE u.admin_active = 1 AND u.id_admin_role=rc.id_admin_role AND rc.capability like "%\'' . $capability . '\'%"' . $conditions;
        $tmp = $db->getMultipleData($query, FALSE);
        $capable_users = array_merge($capable_users, $tmp);
        //End#2;
        //#3: Check in User Capabilities Table
        $query = 'SELECT u.uidnr_admin FROM admin_capability uc, admin_users u WHERE u.admin_active = 1 AND u.uidnr_admin=uc.uidnr_admin AND uc.capability like "%\'' . $capability . '\'%"' . $conditions;
        $tmp = $db->getMultipleData($query, FALSE);
        $capable_users = array_merge($capable_users, $tmp);
        //End#3;
        //#4: Check in Work Order Capabilities Table
        $query = 'SELECT u.uidnr_admin FROM admin_workorder_capability wc, admin_users u WHERE u.admin_active = 1 AND u.uidnr_admin=wc.uidnr_admin AND (wc.initiator_capability like "%\'' . $capability . '%\'" OR wc.approver_capability like "\'' . $capability . '\'")' . $conditions;
        $tmp = $db->getMultipleData($query, FALSE);
        $capable_users = array_merge($capable_users, $tmp);
        //End#4;
        //#5:Remove Blocked Users from the List
        $query = 'SELECT u.uidnr_admin FROM admin_blocked_capability bc, admin_users u WHERE u.admin_active = 1 AND u.uidnr_admin=bc.uidnr_admin AND bc.capability like "\'' . $capability . '\'"' . $conditions;
        $tmp = $db->getMultipleData($query, FALSE);
        $capable_users = array_merge($capable_users, $tmp);
        if ($db->num_rows($rs) > 0)
            while ($rd = $db->fetch_array($rs)) {
                $index = array_search($rd['uidnr_admin'], $capable_users);
                if ($index)
                    unset($capable_users[$index]);
            }
        //End#5;

        return array_unique($capable_users);
    }

}

/**
 * Removes Single Quotes from Array passed
 *
 * Created on 07-mar-09
 * @author : Ratheesh Kumar CK <ratheesh@saturn.in>
 *
 * @param $array
 *   Capability fetched from DB
 *
 * @return $array
 *   Capabilities with removed unwanted characters
 *
 * */
if (!function_exists('formatCapabilities')) {

    function formatCapabilities($capabilities) {
        return str_replace("'", '', $capabilities);
    }

}

class sqlDb {

    private $link;
    private $linker;
    private $dbf;
    private $last_insert_id;

    function __construct($dsn) {
        $pdsn = parse_url($dsn);
        if ($pdsn['scheme'] !== 'mysql')
            die("System is designed for MySQL only.. Please Correct the dsn");
        $mysql_db = preg_replace("@^\/@", '', $pdsn['path']);

        $this->linker = new mysqli($pdsn['host'], $pdsn['user'], $pdsn['pass'], $mysql_db);
        if ($this->linker->connect_error) {
            die("Could not connect: " . $this->linker->connect_error);
        }
        $this->link = mysqli_connect($pdsn['host'], $pdsn['user'], $pdsn['pass'], $mysql_db, ini_get("mysqli.default_port"));
        if ($this->link === false) {
            die("Could not connect: " . mysqli_connect_error());
        }
        $this->query("set @@session.sql_mode=STRICT_ALL_TABLES");
        $this->query("set SESSION group_concat_max_len = 10000");
        ;
        //mysqli_select_db($this->link,$mysql_db) or die("Could not select database");
        /* if (isset($GLOBALS['profiler'])) {
          $GLOBALS['profiler']->setLink($this->link);
          }elseif(class_exists('phpMyProfiler')){
          $GLOBALS['profiler'] = new phpMyProfiler($pdsn['host'], $pdsn['user'], $pdsn['pass']);
          } */

        $this->dbf = $mysql_db;
    }

    function linker(){
        return  $this->linker;
    }

    function __destruct() {
        if ($this->link instanceof mysqli) {
            mysqli_close($this->link);
        }
    }

    function error($query, $errno, $error) {
        //echo $error . "<br>" . $query;
        mysqli_query($this->link, "rollback");
        $return = array("success" => false, "query" => $query, "error" => $error);
        exit(json_encode($return));
        return false;
    }

    /**
     * Sends a query to the database
     *
     * @param sqlquery $query
     * @return result-resource
     */
    function query($query, $logQuery = true) {
        $result = mysqli_query($this->link, $query) or $this->error($query, mysqli_errno($this->link), mysqli_error($this->link));
        return $result;
    }

    /**
     * Perform a modification query on database
     *
     * @param string $table
     * @param object $data
     * @param string $action
     * @param string $parameters
     * @return data resource
     */
    function perform($table, $data, $action = 'insert', $parameters = '') {
        reset($data);
        if ($action == 'insert') {
            $query = 'INSERT INTO ' . $table . ' (' . join(', ', array_keys($data)) . ') VALUES (';
            reset($data);
            foreach ($data as $value) {
                if (preg_match("@^func:@i", $value)) {
                    $query .= substr($value, 5) . ', ';
                } else {
                    switch ((string) $value) {
                        case 'now()' :
                            $query .= 'NOW(), ';
                            break;
                        case 'null' :
                            $query .= 'NULL, ';
                            break;
                        default :
                            $query .= '\'' . $this->input($value) . '\', ';
                            break;
                    }
                }
            }
            $query = substr($query, 0, -2) . ')';

            return $this->query($query, false);
        } elseif ($action == 'update') {
            $query = 'UPDATE ' . $table . ' SET ';
            foreach ($data as $columns => $value) {
                if (preg_match("@^func:@i", $value)) {
                    $query .= $columns . ' = ' . substr($value, 5) . ', ';
                } else {
                    switch ((string) $value) {
                        case 'now()' :
                            $query .= $columns . ' = NOW(), ';
                            break;
                        case 'null' :
                            $query .= $columns . ' = NULL, ';
                            break;
                        case '++' :
                            $query .= $columns . ' = ' . $columns . ' + 1, ';
                            break;
                        default :
                            $query .= $columns . ' = \'' . $this->input($value) . '\', ';
                            break;
                    }
                }
            }
            $query = substr($query, 0, -2);
            if ($parameters !== '')
                $query .= ' WHERE ' . $parameters;

            return $this->query($query, true);
        }
        //echo $query;
        //return $this->query($query);
    }

    function fetch_array($result) {
        return mysqli_fetch_array($result, MYSQLI_ASSOC);
    }

    /**
     * Fetch a result row as an object
     * @author 	  Ratheesh Kumar CK
     * @created on  23-July-2008
     */
    function fetch_object($result) {
        return mysqli_fetch_object($result);
    }

    function fetch_row($result) {
        return mysqli_fetch_row($result);
    }

    function num_rows($result) {
        return mysqli_num_rows($result);
    }

    function data_seek($result, $row_number) {
        return mysqli_data_seek($result, $row_number);
    }

    function insert_id() {
        $idFromCache = $this->last_insert_id;
        $this->resetLastInsertId();
        return empty($idFromCache) ? mysqli_insert_id($this->link) : $idFromCache;
    }

    function getLastInsertId($force = false) {
        if (empty($this->last_insert_id) || $force)
            $this->last_insert_id = $this->insert_id();
        return $this->last_insert_id;
    }

    function resetLastInsertId() {
        $this->last_insert_id = null;
    }

    function affected_rows() {
        return mysqli_affected_rows($this->link);
    }

    function free_result($result) {
        return mysqli_free_result($result);
    }

    function fetch_fields($result) {
        return mysqli_fetch_field($result);
    }

    function output($string) {
        return htmlspecialchars($string);
    }

    function input($string) {
        return $this->link->real_escape_string($string);
    }

    function prepare_input($string) {
        if (is_string($string)) {
            return trim(stripslashes($string));
        } elseif (is_array($string)) {
            reset($string);
            while (list ($key, $value) = each($string)) {
                $string[$key] = $this->prepare_input($value);
            }
            return $string;
        } else {
            return $string;
        }
    }

    //Extra functions to abstract common behavior
    function getMultipleData($query, $fetch_array = false) {
        return $this->getMulipleData($query, $fetch_array);
    }

    function isCached() {
        return false;
    }

    function setCache() {
        
    }

    //Extra functions to abstract common behavior
    function getMulipleData($query, $fetch_array = false) {
        if (($retval = $this->isCached($query, $fetch_array)) == false) {
            try {
                $rs = $this->query($query);
                if ($this->num_rows($rs) == 0) {
                    $this->clearResult($rs);
                    return false;
                }
                $retval = array();
                if ($fetch_array) {
                    //while (( $row = $this->fetch_array($rs) ) !== false) {
                    while ($row = $this->fetch_array($rs)) {
                        if (count($row) > 1)
                            $retval [] = $row;
                        else
                            $retval [] = $row [0];
                    }
                } else {
                    //while (( $row = $this->fetch_row($rs) ) !== false) {
                    while ($row = $this->fetch_row($rs)) {
                        if (count($row) > 1) {
                            $retval [] = $row;
                        } else {
                            $retval [] = $row [0];
                        }
                    }
                }
                $this->clearResult($rs);
                $this->setCache($retval, $query, $fetch_array);
            } catch (Exception $e) {
                error_log('getMulipleData failed: ' . $e->getMessage() . ' Query: ' . $query);
            }
        }
        return $retval;
    }

    function getItemFromDB($query) {
        if (($retval = $this->isCached($query)) == false) {
            $rs = $this->query($query);
            if ($this->num_rows($rs) == 0) {
                $this->clearResult($rs);
                return false;
            }
            $retval = $this->fetch_row($rs);
            $this->setCache($retval, $query);
            $this->clearResult($rs);
        }
        return $retval [0];
    }

    function getFromDB($query, $fetchArray = false) {
        if (($retval = $this->isCached($query, $fetchArray)) == false) {
            $rs = $this->query($query);
            if ($this->num_rows($rs) == 0) {
                $this->clearResult($rs);
                return false;
            }
            if ($fetchArray) {
                $retval = $this->fetch_array($rs);
            } else {
                $retval = $this->fetch_row($rs);
            }
            $this->setCache($retval, $query, $fetchArray);
            $this->clearResult($rs);
        }
        return $retval;
    }

    function getArrayFromDB($query, $val = FALSE) {
        if (($retval = $this->isCached($query)) == false) {
            $rs = $this->query($query);
            if ($this->num_rows($rs) == 0) {
                $this->clearResult($rs);
                return false;
            } else {
                $retval = array();
                if ($val == true) {
                    while ($row = $this->fetch_row($rs)) {
                        $retval [] = array($row [0], $row [1]);
                    }
                } else {
                    while ($row = $this->fetch_row($rs)) {
                        $retval [$row [0]] = $row [1];
                    }
                }
                $this->clearResult($rs);
            }
            $this->setCache($retval, $query);
        }
        return $retval;
    }

    /**
     * Return resultset as a JSON
     * Note: the JSON is created by mysql.
     *
     * Example of created query:
     * SELECT
     * 	  	CONCAT('{',
     * 	  	'"totalCount":', count(*),
     * 	  	',"users":',
     * 			CONCAT('[',
     * 				GROUP_CONCAT(
     * 					CONCAT('{',''),
     * 					CONCAT('"uidnr_admin":"',u.uidnr_admin,'"'),
     * 					CONCAT(',"admin_username":"',u.admin_username,'"'),
     * 					CONCAT(',"admin_email":"',u.admin_email,'"'),
     * 					CONCAT(',"admin_active":"',u.admin_active,'"'),
     * 					CONCAT(',"admin_role_name":"',r.admin_role_name,'"'),
     * 					CONCAT('}','')
     * 				),
     * 			']'),
     * 		'}')
     * 	from
     * 		admin_users u, admin_role r
     * 	where
     * 		u.id_admin_role=r.id_admin_role
     * 	order by
     * 		$rec_sort $rec_sort_dir
     * 	limit
     * 		$rec_start,$rec_limit
     */
    function getAsJson($table, $cols, $where, $orderby, $limit, $jsCountKey, $jsDataKey) {
        $qry = <<<EOT
					SELECT
					  	CONCAT('{',
					  	'"$jsCountKey":', count(*),
					  	',"$jsDataKey":',
							CONCAT('[',
								GROUP_CONCAT(
									CONCAT('{',''),
									$cols
									CONCAT('}','')
								),
							']'),
						'}')
					from
						$table
					where
						$where
					order by
						$orderby
					limit
						$limit
EOT;

        return $this->getItemFromDB($qry);
    }

    /**
     * Return resultset as a JSON
     * Note: the JSON is created by mysql.
     *
     * Example of created query:
     * SELECT
     * 	  	CONCAT('{',
     * 	  	'"totalCount":', count(*),
     * 	  	',"users":',
     * 			CONCAT('[',
     * 				GROUP_CONCAT(
     * 					CONCAT('{',''),
     * 					CONCAT('"uidnr_admin":"',u.uidnr_admin,'"'),
     * 					CONCAT(',"admin_username":"',u.admin_username,'"'),
     * 					CONCAT(',"admin_email":"',u.admin_email,'"'),
     * 					CONCAT(',"admin_active":"',u.admin_active,'"'),
     * 					CONCAT(',"admin_role_name":"',r.admin_role_name,'"'),
     * 					CONCAT('}','')
     * 				),
     * 			']'),
     * 		'}')
     * 	from
     * 		admin_users u, admin_role r
     * 	where
     * 		u.id_admin_role=r.id_admin_role
     * 	order by
     * 		$rec_sort $rec_sort_dir
     * 	limit
     * 		$rec_start,$rec_limit
     */
    function getAsJsArray($table, $cols, $where, $orderby, $limit, $jsCountKey, $jsDataKey) {
        $qry = <<<EOT
					SELECT
					  	CONCAT('{',
					  	'"$jsCountKey":', count(*),
					  	',"$jsDataKey":',
							CONCAT('[',
								GROUP_CONCAT(
									CONCAT('{',''),
									$cols
									CONCAT('}','')
								),
							']'),
						'}')
					from
						$table
					where
						$where
					order by
						$orderby
					limit
						$limit
EOT;
        return $this->getItemFromDB($qry);
    }

    function next_result() {
        return mysqli_next_result($this->link);
    }

    function store_result() {
        return mysqli_store_result($this->link);
    }

    /**
     * Function to clear the resultset to avoid the commands out of sync error
     *
     * @param resource $result
     * @author Niju N B
     */
    function clearResult($result = null) {
        if (is_resource($result))
            $this->free_result($result);
        while ($this->next_result()) {
            $result = $this->store_result();
            if ($result) {
                $this->free_result($result);
            }
        }
    }

    function polishArray($rd) {
        if (is_array($rd))
            foreach ($rd as $key => $value) {
                $rd[$key] = html_entity_decode(stripslashes($value));
            }
        return $rd;
    }

    function printGridJson($countQuery, $listQuery) {
        $count = $this->getItemFromDB($countQuery);
        echo '{"totalCount":"', $count, '","data":[';
        $rs = $this->query($listQuery);

        if ($this->num_rows($rs)) {
            $comma = '';
            while ($rd = $this->fetch_array($rs)) {
                $rd = $this->polishArray($rd);
                echo $comma, json_encode($rd);
                $comma = ',';
            }
        }
        echo ']}';
    }

    function _loadRecordJson($sql) {
        $results = $this->getFromDB($sql, true);
        if (!$results) {
            echo '{"success":true,"data":[]}';
        } else {
            echo '{"success":true, "data":',
            json_encode($results),
            '}';
        }
    }

    // ========================================================================
    // PREPARED STATEMENT METHODS (SQL Injection Prevention)
    // Added: 2026-05-13
    // ========================================================================

    /**
     * Execute a prepared statement (INSERT, UPDATE, DELETE)
     * @param string $query   SQL with ? placeholders
     * @param string $types   Type string ("i"=int, "s"=string, "d"=double)
     * @param array  $params  Array of values to bind
     * @return mysqli_stmt|false
     */
    function executeSafe($query, $types = "", $params = []) {
        $stmt = $this->link->prepare($query);
        if (!$stmt) {
            $this->error($query, $this->link->errno, $this->link->error);
            return false;
        }
        if (!empty($params) && !empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        if ($stmt->errno) {
            $this->error($query, $stmt->errno, $stmt->error);
            return false;
        }
        return $stmt;
    }

    /**
     * Get a single value using prepared statement
     * Replaces: $db->getItemFromDB("... WHERE id = {$_POST['id']}")
     * Usage:    $db->getItemSafe("... WHERE id = ?", "i", [$_POST['id']])
     */
    function getItemSafe($query, $types = "", $params = []) {
        $stmt = $this->executeSafe($query, $types, $params);
        if (!$stmt) return false;
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            $result->free();
            $stmt->close();
            return false;
        }
        $row = $result->fetch_row();
        $retval = $row[0];
        $result->free();
        $stmt->close();
        return $retval;
    }

    /**
     * Get a single row using prepared statement
     * Replaces: $db->getFromDB("... WHERE id = {$_POST['id']}", true)
     * Usage:    $db->getFromSafe("... WHERE id = ?", "i", [$_POST['id']], true)
     */
    function getFromSafe($query, $types = "", $params = [], $fetchArray = false) {
        $stmt = $this->executeSafe($query, $types, $params);
        if (!$stmt) return false;
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            $result->free();
            $stmt->close();
            return false;
        }
        $retval = $fetchArray ? $result->fetch_assoc() : $result->fetch_row();
        $result->free();
        $stmt->close();
        return $retval;
    }

    /**
     * Get multiple rows using prepared statement
     * Replaces: $db->getMultipleData("... WHERE x = {$_POST['x']}", true)
     * Usage:    $db->getMultipleSafe("... WHERE x = ?", "s", [$_POST['x']], true)
     */
    function getMultipleSafe($query, $types = "", $params = [], $fetchArray = false) {
        $stmt = $this->executeSafe($query, $types, $params);
        if (!$stmt) return false;
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            $result->free();
            $stmt->close();
            return false;
        }
        $retval = array();
        if ($fetchArray) {
            while ($row = $result->fetch_assoc()) {
                $retval[] = (count($row) > 1) ? $row : reset($row);
            }
        } else {
            while ($row = $result->fetch_row()) {
                $retval[] = (count($row) > 1) ? $row : $row[0];
            }
        }
        $result->free();
        $stmt->close();
        return $retval;
    }

    /**
     * Sanitize integer input - use for IDs when prepared statements aren't feasible
     */
    function sanitizeInt($value) {
        return intval($value);
    }

    /**
     * Sanitize string input using real_escape_string
     * Prefer prepared statements over this method
     */
    function sanitizeString($value) {
        return $this->link->real_escape_string(trim($value));
    }

    // ========================================================================
    // END PREPARED STATEMENT METHODS
    // ========================================================================

}

class mTimer {

    var $pTime;

    function getMyTime() {
        list ($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    function getStart() {

        return date("Y-m-d G:i:s", (int) $this->pTime);

        //return floor($this->pTime);
    }

    function getStop() {
        return sprintf("%f", $this->getMyTime() - $this->pTime);
    }

    function mTimer() {
        $this->pTime = $this->getMyTime();
    }

    function commentTime($msg) {
        echo "<!-- $msg : ";
        $this->getStop();
        echo "-->\n";
    }

}

if (!function_exists('json_encode')) {

    function json_encode($var) {
        include ('json.php');
        $fn = new Services_JSON();
        $returnfn = $fn->encode($var);
        return $returnfn;
    }

}

/*
 * :TODO: 		Read the module Diresctory and return the name of the permitted modules
 *
 */

function getPermittedModules() {

    global $db;
    $permitted = array();
    $modules = $db->getMultipleData("SELECT ModuleName FROM sys_module WHERE ModuleName not in ('auth','ui')");

    // Checking the permissions of the particular module
    foreach ($modules as $dir) {
        if (user_access($dir, '') == true) {
            $permitted[] = $dir;
        }
    }
    // Return the permitted array
    return $permitted;
}

function hasCapability($cap, $account = null) {
    if (is_null($account)) {
        $account = $_SESSION['admin'];
    }

    // Admin_User has all privileges:
    if ($account->IsSuperUser == "Yes") {
        return TRUE;
    }

    return (in_array($cap, $account->perms));
}

class Activity {

    /**
     * @purpose : Activity Log
     * @param <type> $actionData
     * @param <type> $action
     * @return <type>
     * @author <Azad> azad@saturn.in
     */
    public static function Log($actionData, $action) {
		return true;
        global $db;
        //REPLACE INTO report_session_track(sessid) VALUES('123');

        $db->query("replace into session_track(sessid,userid) values ('" . addslashes(session_id()) . "', '" . intval($_SESSION['admin']->Finascop_UserId) . "')");
        if (!empty($actionData)) {
            $data = array(
                "remote_ip" => self::getIpBehindProxy(),
                "user_id" => intval($_SESSION['admin']->Finascop_UserId),
                "action_data" => "",
                "action" => $action);
            $status = $db->perform("action_log", $data);
            $logDir = LOG_REPOS . date("Y/m/d");
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }
            $logFile = $logDir . '/admin-action-' . $db->insert_id() . '.txt';
            file_put_contents($logFile, serialize($actionData));
        }
    }

    public static function getIpAddr() {
        return self::getIpBehindProxy();
    }

    private static function getIpBehindProxy() {
        global $_SERVER;
        $remote = array($_SERVER["REMOTE_ADDR"]);
        $comes_from = array("HTTP_VIA", "HTTP_X_COMING_FROM", "HTTP_X_FORWARDED_FOR", "HTTP_X_FORWARDED", "HTTP_COMING_FROM", "HTTP_FORWARDED_FOR", "HTTP_FORWARDED");
        foreach ($comes_from as $value) {
            if (isset($_SERVER[$value]) && preg_match_all("/([0-9]{1,3}\.){3,3}[0-9]{1,3}/", $_SERVER[$value], $remote_temp)) {
                $remote = array_merge($remote, $remote_temp[0]); //     Fish out IP match if ereg returns a value
            }
        }
        return join(',', $remote);
    }

}

/**
 * EXTJS GRID FILTER SANITIZER
 * ============================
 * Add this function to includes/lib.php or a separate includes/sanitize.php
 * 
 * Problem: 222 filter patterns across all modules do this:
 *   $filter = $_POST['filter'];
 *   foreach ($filter as $key => $val) {
 *       $filter_qry .= " AND " . $val['field'] . " LIKE '" . $val['data']['value'] . "%'";
 *   }
 * 
 * This allows SQL injection through both the field name and the value.
 * 
 * Solution: Sanitize filter inputs with whitelist validation for field names
 * and proper escaping for values.
 */

/**
 * Build a safe WHERE clause from ExtJS grid filter data
 * 
 * @param array  $filters       The $_POST['filter'] array from ExtJS
 * @param array  $allowedFields Whitelist of allowed column names for this grid
 * @param sqlDb  $db            Database connection for real_escape_string
 * @return string               Safe SQL WHERE clause fragment
 */
function buildSafeFilterQuery($filters, $allowedFields, $db) {
    $filter_qry = '';
    
    if (empty($filters) || !is_array($filters)) {
        return $filter_qry;
    }
    
    $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
    
    foreach ($filters as $val) {
        // Validate field name against whitelist
        $field = isset($val['field']) ? $val['field'] : '';
        if (!in_array($field, $allowedFields)) {
            continue; // Skip unknown fields - prevents SQL injection via field names
        }
        
        $type = isset($val['data']['type']) ? $val['data']['type'] : '';
        $value = isset($val['data']['value']) ? $val['data']['value'] : '';
        $comparison = isset($val['data']['comparison']) ? $val['data']['comparison'] : 'eq';
        
        switch ($type) {
            case 'string':
                // Sanitize string value
                $safeValue = $db->sanitizeString($value);
                $filter_qry .= " AND {$field} LIKE '{$safeValue}%'";
                break;
                
            case 'date':
                // Validate and format date
                $dateValue = date('Y-m-d', strtotime($value));
                if ($dateValue === '1970-01-01' && $value !== '1970-01-01') {
                    continue 2; // Invalid date, skip
                }
                $comp = isset($comparisons[$comparison]) ? $comparisons[$comparison] : '=';
                $filter_qry .= " AND DATE({$field}) {$comp} '{$dateValue}'";
                break;
                
            case 'numeric':
                // Force numeric value
                $numValue = floatval($value);
                $comp = isset($comparisons[$comparison]) ? $comparisons[$comparison] : '=';
                $filter_qry .= " AND {$field} {$comp} {$numValue}";
                break;
                
            case 'list':
                // Sanitize each item in comma-separated list
                $items = explode(',', $value);
                $safeItems = array_map(function($item) use ($db) {
                    return "'" . $db->sanitizeString(trim($item)) . "'";
                }, $items);
                $inClause = implode(',', $safeItems);
                $filter_qry .= " AND {$field} IN ({$inClause})";
                break;
                
            case 'boolean':
                $boolValue = ($value === 'true' || $value === '1') ? 1 : 0;
                $filter_qry .= " AND {$field} = {$boolValue}";
                break;
        }
    }
    
    return $filter_qry;
}

/**
 * Sanitize sort column name
 * Prevents SQL injection through ORDER BY clause
 * 
 * @param string $sort          User-provided sort column
 * @param array  $allowedFields Whitelist of sortable columns
 * @param string $default       Default sort column
 * @return string               Safe column name
 */
function sanitizeSortColumn($sort, $allowedFields, $default = 'id') {
    $sort = trim($sort);
    if (in_array($sort, $allowedFields)) {
        return $sort;
    }
    return $default;
}

/**
 * Sanitize sort direction
 * 
 * @param string $dir  User-provided direction
 * @return string      'ASC' or 'DESC'
 */
function sanitizeSortDirection($dir) {
    return (strtoupper(trim($dir)) === 'ASC') ? 'ASC' : 'DESC';
}

/**
 * Sanitize pagination parameters
 * 
 * @param mixed $limit  User-provided limit
 * @param mixed $start  User-provided offset
 * @return array        [limit, start] as integers
 */
function sanitizePagination($limit, $start) {
    $limit = is_numeric($limit) ? intval($limit) : 20;
    $start = is_numeric($start) ? intval($start) : 0;
    $limit = min(max($limit, 1), 500); // Cap at 500
    $start = max($start, 0);
    return [$limit, $start];
}

/*
 * ============================================================
 * USAGE EXAMPLE (replacing the typical ExtJS grid pattern):
 * ============================================================
 *
 * BEFORE (vulnerable):
 * 
 *   $limit = $_POST['limit'];
 *   $start = $_POST['start'];
 *   $sort = trim($_POST['sort']);
 *   $dir = trim($_POST['dir']);
 *   $filter_qry = " WHERE 1 = 1 ";
 *   if (isset($_POST['filter'])) {
 *       $filter = $_POST['filter'];
 *       foreach ($filter as $key => $val) {
 *           switch ($val['data']['type']) {
 *               case 'string':
 *                   $filter_qry .= " AND " . $val['field'] . " LIKE '" . $val['data']['value'] . "%'";
 *                   break;
 *           }
 *       }
 *   }
 *   $listQuery = "SELECT * FROM orders {$filter_qry} ORDER BY {$sort} {$dir} LIMIT {$start},{$limit}";
 *
 *
 * AFTER (safe):
 * 
 *   // Define allowed fields for THIS specific grid view
 *   $allowedFields = ['order_id', 'order_date', 'customer_name', 'order_status', 'order_total'];
 *   
 *   list($limit, $start) = sanitizePagination($_POST['limit'], $_POST['start']);
 *   $sort = sanitizeSortColumn($_POST['sort'], $allowedFields, 'order_id');
 *   $dir = sanitizeSortDirection($_POST['dir']);
 *   $filter_qry = " WHERE 1 = 1 ";
 *   
 *   if (isset($_POST['filter'])) {
 *       $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
 *   }
 *   
 *   $listQuery = "SELECT * FROM orders {$filter_qry} ORDER BY {$sort} {$dir} LIMIT {$start},{$limit}";
 */
