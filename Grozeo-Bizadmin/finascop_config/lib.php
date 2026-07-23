<?php

// classess specific for the rpc service
if (extension_loaded('zlib')) {
    if (!function_exists('gzopen') && function_exists('gzopen64')) {

        function gzopen($filename, $mode, $use_include_path = 0) {
            return gzopen64($filename, $mode, $use_include_path);
        }

    }
}

function checkSysLock() {
    if (!file_exists(SYSTEM_LOCK)) {
        $etag = time() . getmypid();
        file_put_contents(SYSTEM_LOCK, $etag);
        return $etag;
    }
    clearstatcache();
    usleep(250);
    return checkSysLock();
}

if (!function_exists('gzdecode')) {

    function gzdecode($data) {
        $flags = ord(substr($data, 3, 1));
        $headerlen = 10;
        $extralen = 0;
        $filenamelen = 0;
        if ($flags & 4) {
            $extralen = unpack('v', substr($data, 10, 2));
            $extralen = $extralen[1];
            $headerlen += 2 + $extralen;
        }
        if ($flags & 8) // Filename
            $headerlen = strpos($data, chr(0), $headerlen) + 1;
        if ($flags & 16) // Comment
            $headerlen = strpos($data, chr(0), $headerlen) + 1;
        if ($flags & 2) // CRC at end of file
            $headerlen += 2;
        $unpacked = gzinflate(substr($data, $headerlen));
        if ($unpacked === FALSE)
            $unpacked = $data;
        return $unpacked;
    }

}

function finascop_aasort(&$array, $key) {
    $sorter = array();
    $ret = array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii] = $va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        array_push($ret, $array[$ii]);
    }
    $array = $ret;
}

function finascop_arsort(&$array, $key) {
    $sorter = array();
    $ret = array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii] = $va[$key];
    }
    arsort($sorter);
    foreach ($sorter as $ii => $va) {
        array_push($ret, $array[$ii]);
    }
    $array = $ret;
}

function getSourceOrderGrandtotal($sourcetype, $refno) {
    $db = new \SqlDB(DSN);
    switch ($sourcetype) {
        case '0': // Branch Tramsfer
            $fsto_netamount = $db->getItemFromDB("SELECT fsto_netamount FROM  finascop_stock_transfer_order WHERE fstr_id = (SELECT fstr_id FROM finascop_stock_transfer_request WHERE fstr_uid = '{$refno}')");
//            $fields = array("tempvalue" => "SELECT fsto_netamount FROM  finascop_stock_transfer_order WHERE fstr_id = (SELECT fstr_id FROM finascop_stock_transfer_request WHERE fstr_uid = '{$refno}')");
//            $db->perform('temptable',$fields);	

            break;
        case '1': // B2C
            $fsto_netamount = $db->getItemFromDB("SELECT total FROM  retaline_customer_order WHERE order_order_id = '{$refno}'");
            break;
        case '2': // B2B
            $fsto_netamount = $db->getItemFromDB("SELECT bbso_SOValue FROM retaline_B2B_SalesOrder WHERE bbso_SONumber = '{$refno}'");

            break;
        case '3': // Return
            $fstr_id = $db->getItemFromDB("SELECT frrp_id FROM finascop_stock_return_request_packing WHERE frrp_uid = '{$refno}'");
            $fsto_netamount = $db->getItemFromDB("SELECT fsto_netamount FROM  finascop_stock_transfer_order WHERE fstr_id = {$fstr_id}");
            break;
    }
//    echo "fsto_netamount:".$fsto_netamount;
//    exit();
    return $fsto_netamount;
}

function getQugeoParentStatusUpdated($updateurl, $status) {
    $pos = strpos($updateurl, '##31');
    if ($pos === false) {
        $pos = strpos($updateurl, '###1');
        if ($pos === false) {
            $pos = strpos($updateurl, '##21');
            if ($pos === false) {
                $pos = strpos($updateurl, '##61');
                if ($pos === false) {
                    file_put_contents('php://stderr', "Could not find a valid status from drive status to update source" . "\n");
                    file_put_contents('php://stderr', $updateurl . "\n");
                    throw new Exception("Could not find a valid status from drive status to update source");
                } else {
                    $type = 3;
                }
            } else {
                $type = 2;
            }
        } else {
            $type = 1;
        }
    } else {
        $type = 0;
    }
    switch ($status) {
        case 22:
            if ($type == 0) {
                $strReturn = str_replace("##31", QUGEO_TO_CPD2BR_ORDER_STATUS_READY_FOR_DELIVERY, $updateurl);
            } elseif ($type == 1) {
                $strReturn = str_replace("###1", QUGEO_TO_B2C_ORDER_STATUS_READY_FOR_DELIVERY, $updateurl);
            } elseif ($type == 2) {
                $strReturn = str_replace("##21", QUGEO_TO_B2B_ORDER_STATUS_READY_FOR_DELIVERY, $updateurl);
            } else {
                $strReturn = str_replace("##61", 4, $updateurl);
            }
            //Ready for delivery
            break;
        case 23:
        case 32:
            if ($type == 0) {
                $strReturn = str_replace("##31", QUGEO_TO_CPD2BR_ORDER_STATUS_BOY_POLLED, $updateurl);
            } elseif ($type == 1) {
                $strReturn = str_replace("###1", QUGEO_TO_B2C_ORDER_STATUS_BOY_POLLED, $updateurl);
            } elseif ($type == 2) {
                $strReturn = str_replace("##21", QUGEO_TO_B2B_ORDER_STATUS_BOY_POLLED, $updateurl);
            } else {
                $strReturn = str_replace("##61", 4, $updateurl);
            }
            //Polled
            break;
        case 24:
        case 33:
            if ($type == 0) {
                $strReturn = str_replace("##31", QUGEO_TO_CPD2BR_ORDER_STATUS_POLL_REJECT_NORESPONSE, $updateurl);
            } elseif ($type == 1) {
                $strReturn = str_replace("###1", QUGEO_TO_B2C_ORDER_STATUS_POLL_REJECT_NORESPONSE, $updateurl);
            } elseif ($type == 2) {
                $strReturn = str_replace("##21", QUGEO_TO_B2B_ORDER_STATUS_POLL_REJECT_NORESPONSE, $updateurl);
            } else {
                $strReturn = str_replace("##61", 4, $updateurl);
            }
            //Rejected
            break;
        case 25:
        case 34:
            if ($type == 0) {
                $strReturn = str_replace("##31", QUGEO_TO_CPD2BR_ORDER_STATUS_POLL_REJECT_NORESPONSE, $updateurl);
            } elseif ($type == 1) {
                $strReturn = str_replace("###1", QUGEO_TO_B2C_ORDER_STATUS_POLL_REJECT_NORESPONSE, $updateurl);
            } elseif ($type == 2) {
                $strReturn = str_replace("##21", QUGEO_TO_B2B_ORDER_STATUS_POLL_REJECT_NORESPONSE, $updateurl);
            } else {
                $strReturn = str_replace("##61", 4, $updateurl);
            }
            //No response
            break;
        case 26:
        case 27:
            if ($type == 0) {
                $strReturn = str_replace("##31", QUGEO_TO_CPD2BR_ORDER_STATUS_BOY_ASSIGNED, $updateurl);
            } elseif ($type == 1) {
                $strReturn = str_replace("###1", QUGEO_TO_B2C_ORDER_STATUS_BOY_ASSIGNED, $updateurl);
            } elseif ($type == 2) {
                $strReturn = str_replace("##21", QUGEO_TO_B2B_ORDER_STATUS_BOY_ASSIGNED, $updateurl);
            } else {
                $strReturn = str_replace("##61", 4, $updateurl);
            }
            // Assigned				
            break;
        case 28:
        case 29:
            if ($type == 0) {
                $strReturn = str_replace("##31", QUGEO_TO_CPD2BR_ORDER_STATUS_PICKED_UP, $updateurl);
            } elseif ($type == 1) {
                $strReturn = str_replace("###1", QUGEO_TO_B2C_ORDER_STATUS_PICKED_UP, $updateurl);
            } elseif ($type == 2) {
                $strReturn = str_replace("##21", QUGEO_TO_B2B_ORDER_STATUS_PICKED_UP, $updateurl);
            } else {
                $strReturn = str_replace("##61", 4, $updateurl);
            }
            // PIcked UP ****
            break;
        case 9: //Out For Delivery
            if ($type == 0) {
                $strReturn = str_replace("##31", QUGEO_TO_CPD2BR_ORDER_STATUS_OUT_DELIVERY, $updateurl);
            } elseif ($type == 1) {
                $strReturn = str_replace("###1", QUGEO_TO_B2C_ORDER_STATUS_OUT_FOR_DELIVERY, $updateurl);
            } elseif ($type == 2) {
                $strReturn = str_replace("##21", QUGEO_TO_B2B_ORDER_STATUS_OUT_DELIVERY, $updateurl);
            } else {
                $strReturn = str_replace("##61", 4, $updateurl);
            }
            break;
        case 31:
            if ($type == 0) {
                $strReturn = str_replace("##31", QUGEO_TO_CPD2BR_ORDER_STATUS_MANUAL_SCHEDULE, $updateurl);
            } elseif ($type == 1) {
                $strReturn = str_replace("###1", QUGEO_TO_B2C_ORDER_STATUS_MANUAL_SCHEDULE, $updateurl);
            } elseif ($type == 2) {
                $strReturn = str_replace("##21", QUGEO_TO_B2B_ORDER_STATUS_MANUAL_SCHEDULE, $updateurl);
            } else {
                $strReturn = str_replace("##61", 4, $updateurl);
            }
            //Delivery schedule
            break;
        case 35:
        case 36:
        case 37:
            if ($type == 0) {
                $strReturn = str_replace("##31", QUGEO_TO_CPD2BR_ORDER_STATUS_PICKUP_FAILED, $updateurl);
            } elseif ($type == 1) {
                $strReturn = str_replace("###1", QUGEO_TO_B2C_ORDER_STATUS_PICKUP_FAILED, $updateurl);
            } elseif ($type == 2) {
                $strReturn = str_replace("##21", QUGEO_TO_B2B_ORDER_STATUS_PICKUP_FAILED, $updateurl);
            } else {
                $strReturn = str_replace("##61", 4, $updateurl);
            }
            //PIckup failed
            break;
        case 10:
        case 11:
        case 12:
        case 13:
        case 14:
            if ($type == 0) {
                $strReturn = str_replace("##31", QUGEO_TO_CPD2BR_ORDER_STATUS_DELIVERY_FAILED, $updateurl);
            } elseif ($type == 1) {
                $strReturn = str_replace("###1", QUGEO_TO_B2C_ORDER_STATUS_DELIVERY_FAILED, $updateurl);
            } elseif ($type == 2) {
                $strReturn = str_replace("##21", QUGEO_TO_B2B_ORDER_STATUS_DELIVERY_FAILED, $updateurl);
            } else {
                $strReturn = str_replace("##61", 4, $updateurl);
            }
            // delivery Failed
            break;
        case 38:
            if ($type == 0) {
                $strReturn = str_replace("##31", QUGEO_TO_CPD2BR_ORDER_STATUS_DELIVERED_NOT_CONFIRMED, $updateurl);
            } elseif ($type == 1) {
                $strReturn = str_replace("###1", QUGEO_TO_B2C_ORDER_STATUS_DELIVERED_NOT_CONFIRMED, $updateurl);
            } elseif ($type == 2) {
                $strReturn = str_replace("##21", QUGEO_TO_B2B_ORDER_STATUS_DELIVERED_NOT_CONFIRMED, $updateurl);
            } else {
                $strReturn = str_replace("##61", 5, $updateurl);
            }
            //Delivered but not confirmed
            break;
        case 15:
            if ($type == 0) {
                $strReturn = str_replace("##31", QUGEO_TO_CPD2BR_ORDER_STATUS_DELIVERY_CONFIRMED, $updateurl);
            } elseif ($type == 1) {
                $strReturn = str_replace("###1", QUGEO_TO_B2C_ORDER_STATUS_DELIVERY_CONFIRMED, $updateurl);
            } elseif ($type == 2) {
                $strReturn = str_replace("##21", QUGEO_TO_B2B_ORDER_STATUS_DELIVERY_CONFIRMED, $updateurl);
            } else {
                $strReturn = str_replace("##61", 5, $updateurl);
            }
            //Deliverey confirmed
            break;
        default:
            file_put_contents('php://stderr', "getQugeoParentStatusUpdated -- Default " . $updateurl . " -- " . $status . "\n");
    }
    file_put_contents('php://stderr', "getQugeoParentStatusUpdated -- Whats " . $updateurl . " -- " . $status . " -- " . $type . "\n");
    file_put_contents('php://stderr', "getQugeoParentStatusUpdated -- " . $strReturn . "\n");
    return $strReturn;
}


function dispatchOrders($qrystring, $quor_Status, $trId, $lastId, $quor_TransferOrder_Type, $quor_TransferOrder_id, $quor_id) {
    global $db;
    $updateQueries = getQugeoParentStatusUpdated($qrystring, $quor_Status);
    $updateQueries = str_replace("###6", "1", $updateQueries);
    $updateQuerys = explode(';', $updateQueries);
    foreach ($updateQuerys as $updateQuery) {
        $updateQuery = trim($updateQuery);

        if ($updateQuery != '') {
            // echo 'qww '.$updateQuery."\n";
            $status = $db->query("{$updateQuery}");
        }
    }
    //echo 'outside';
    $is_retalineLite = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'IS_RETALINE_LITE'");

//    echo '$is_retalineLite'.$is_retalineLite;
//    print_r('TransferOrder_Type:'.$quor_TransferOrder_Type);
    if ($is_retalineLite != 1) {
        switch ($quor_TransferOrder_Type) {
            case '0':
                $fsiid['stiid_status'] = 3;
                $cond = " cpd_order_id = " . (int) $trId;
                break;
            case '1':
                $fsiid['stiid_status'] = 6;
                $cond = " cust_order_id = " . (int) $trId;
                break;
            case '2':
                $fsiid['stiid_status'] = 6;
                $cond = " b2b_order_id = " . (int) $trId;
                break;
            case '3':
                //$fsiid['stiid_status'] = 3;
                $cond = " ret_packing_id = " . (int) $trId;
                break;
        }

        $inventoryDetails = $db->getMultipleData("SELECT * FROM finascop_stock_item_inventorydetails WHERE {$cond}", true);


        $fsiid['stiid_updatedon'] = date('Y-m-d H:i:s');
        $fsiid['stiid_updatedby'] = $_SESSION['admin']->Finascop_UserId;
        $fsiidstatus = $db->perform("finascop_stock_item_inventorydetails", $fsiid, 'update', $cond);

        foreach ($inventoryDetails as $inventoryDetail) {
            $fsiidmData['stiid_id'] = $inventoryDetail['stiid_id'];
            $fsiidmData['stiidm_itemmasterid'] = $inventoryDetail['stiid_itemmasterid'];
            $fsiidmData['stiidm_barcode'] = $inventoryDetail['stiid_barcode'];
            $fsiidmData['created_at'] = date('Y-m-d H:i:s');
            $fsiidmData['stiidm_details'] = 'Dispatched this item in the delivery order ' . $lastId;
            $fsiidmstatus = $db->perform('finascop_stock_item_inventorydetails_movement', $fsiidmData);
        }
    }
}

//function dispatchOrders end

class cgoSqlDB {

    private $dbConnection;
    public $quiet;
    private $mc;
    private $ttl;
    private $dbf;
    private $tz;
    public $mcEnabled = false;
    private $relations;
    public $private;
    private $doNotProcessQry;
    private $lastResult;
    public $default_db;

    function begintransaction() {
        try {
            //mysqli_autocommit($this->dbConnection, FALSE)
            mysqli_query($this->dbConnection, "START TRANSACTION");
        } catch (Exception $e) {
            print_r($e);
            exit;
        }
    }

    function committransaction() {
        try {
            mysqli_query($this->dbConnection, "COMMIT");
        } catch (Exception $e) {
            print_r($e);
            exit;
        }
    }

    function __construct() {
        $pdsn = parse_url(DSN);
        if ($pdsn['scheme'] !== 'mysql')
            die("System is designed for MySQL only.. Please Correct the dsn");
        $mysql_db = preg_replace("@^\/@", '', $pdsn['path']);

        $this->dbConnection = mysqli_connect($pdsn['host'], $pdsn['user'], $pdsn['pass'], $mysql_db, ini_get("mysqli.default_port"))
                or die("Could not connect!<br>" . mysqli_error());

        $this->dbf = $mysql_db;
        $this->default_db = $mysql_db;

        $this->quiet = false;
        $this->relations = false;
        $this->ttl = 2 * 60 * 60;
        if ($this->mcEnabled == true) {
            $this->connectMC();
        }
        $this->private = '';
        $this->lastResult = false;
        $this->doNotProcessQry = false;
    }

    private function cactchExeption() {
        echo $dsn;
        print_r(debug_backtrace());
        exit();
    }

    public function setRelations($r) {
        $this->relations = $r;
    }

    private function connectMC() {
        $this->mc = new Memcache ( );
        if (strpos($_SERVER['SERVER_ADDR'], '192.168.0.') !== false) {
            $this->mc->addserver('192.168.0.15', 11211);
        } else {
            for ($i = 34; $i < 38; $i++) {
                $this->mc->addserver('192.168.33.' . $i, 11211);
            }
        }
    }

    function error($query, $errno, $error) {
        if (!$this->quiet) {
            echo $error . "<br>" . $query;
            if (defined('TRACE_DEBUG'))
                print_r(debug_backtrace());
        }
        exit;
        //return false;
    }

    function refValues($arr) {
        if (strnatcmp(phpversion(), '5.3') >= 0) { //Reference is required for PHP 5.3+ 
            $refs = array();
            foreach ($arr as $key => $value)
                $refs[$key] = &$arr[$key];
            return $refs;
        }
        return $arr;
    }

    /**
     * Sends a query to the database
     *
     * @param sqlquery $query
     * @return result-resource
     */
    function query($query, $params = array()) {
        if ($this->lastResult !== false) {
            $this->clearResult($this->lastResult);
        }
        if ($this->mcEnabled == true)
            $this->checkQuery($query);
        try {
            $stmt = $this->dbConnection->prepare($query);
            if ($stmt === false) {
                echo("SQL prepare error " . $this->dbConnection->error . " Query " . $query );
                file_put_contents('php://stderr', "SQL prepare error " . $this->dbConnection->error . " Query " . $query . "\n");
                exit();
            }

            if (count($params) > 1) {
                $binparam = call_user_func_array(array($stmt, "bind_param"), $this->refValues($params));
                if ($binparam === false) {
                    echo("SQL bind_param error " . $stmt->error);
                    file_put_contents('php://stderr', "SQL bind_param error " . $stmt->error . " Query " . $query . "\n");
                    exit();
                }
            }

            if ($stmt->execute() === false) {
                echo("SQL execute error " . $stmt->error);
                file_put_contents('php://stderr', "SQL execute error " . $stmt->error . " Query " . $query . "\n");
                exit();
            }

            $this->lastResult = $stmt->get_result();
            $stmt->close();
            //$this->lastResult = mysqli_query($this->dbconnection, $query) or $this->error($query, mysqli_errno($this->dbconnection), mysqli_error($this->dbconnection));
            return $this->lastResult;
        } catch (Exception $e) {
            file_put_contents('php://stderr', print_r($e, TRUE));
            file_put_contents('php://stderr', print_r($query, TRUE));
            print_r($query);
            print_r($e);
            exit;
        }
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
    function perform($table, $action, $data, $condition, $paramtypes) {
        reset($data);
        $arrDataCount = count($data);
        $arrConditionCount = 0;
        //If insert
        if ($action == 'insert') {
            $query = 'INSERT INTO ' . $table . ' (' . join(', ', array_keys($data)) . ') VALUES (';

            foreach ($data as &$value) {
                if (strpos($value, 'func:') !== false) {
                    $query .= '?' . ', ';
                    $value = substr($value, 5);
                } else {
                    switch ((string) $value) {
                        case 'now()' :
                            $query .= '?, ';
                            $value = 'NOW()';
                            break;
                        case 'null' :
                            $query .= '?, ';
                            $value = 'NULL';
                            break;
                        default :
                            $query .= '?, ';
                            break;
                    }
                }
            }
            $query = substr($query, 0, -2) . ')';
        } elseif ($action == 'update') {
            //If update
            $query = 'UPDATE ' . $table . ' SET ';
            //Get update columns and it values	
            foreach ($data as $columns => &$value) {
                if (strpos($value, 'func:') !== false) {
                    $query .= $columns . '=?, ';
                    $value = substr($value, 5);
                } else {
                    switch ((string) $value) {
                        case 'now()' :
                            $query .= $columns . ' = ?, ';
                            $value = 'NOW()';
                            break;
                        case 'null' :
                            $query .= $columns . ' = ?, ';
                            $value = 'NULL';
                            break;
                        case '++' :
                            $query .= $columns . ' = ' . $columns . ' + 1, ';
                            $arrDataCount = $arrDataCount - 1;
                            break;
                        default :
                            $query .= $columns . ' = ?, ';
                            break;
                    }
                }
            }
            $query = substr($query, 0, -2);
            //Get update condition and it values	
            $parameters = '';
            if (count($condition) > 0 && !empty($condition)) {
                $arrDataCount = $arrDataCount + count($condition);
                foreach ($condition as $columns => &$value) {
                    if (strpos($value, 'func:') !== false) {
                        $parameters .= $columns . '=?, ';
                        $value = substr($value, 5);
                    } else {
                        switch ((string) $value) {
                            case 'now()' :
                                $parameters .= $columns . ' = ?, ';
                                $value = 'NOW()';
                                break;
                            case 'null' :
                                $parameters .= $columns . ' = ?, ';
                                $value = 'NULL';
                                break;
                            case '++' :
                                $parameters .= $columns . ' = ' . $columns . ' + 1, ';
                                $arrDataCount = $arrDataCount - 1;
                                break;
                            default :
                                $parameters .= $columns . ' = ?, ';
                                break;
                        }
                    }
                }
                $query .= ' WHERE ' . substr($parameters, 0, -2);
            }
        }
        if (strlen($paramtypes) != $arrDataCount) {
            throw new \Exception('Parameter count and each parameter\'s type\'s count does not match, check parametertype variable');
        }
        $data = array_values($data);
        $condition = array_values($condition);
        array_unshift($data, $paramtypes);
        $data = array_merge($data, $condition);

        $res = $this->query($query, $data);

        if ($this->mcEnabled == true && $this->affected_rows() > 0)
            $this->checkAndInvalidate($table);

        return $res;
    }

    function fetch_array($result) {
        return mysqli_fetch_array($result, MYSQLI_ASSOC);
    }

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
        return mysqli_insert_id($this->dbConnection);
    }

    function affected_rows() {
        return mysqli_affected_rows($this->dbConnection);
    }

    function free_result($result) {
        mysqli_free_result($result);
    }

    function fetch_fields($result) {
        return mysqli_fetch_field($result);
    }

    function output($string) {
        return htmlspecialchars($string);
    }

    function input($string) {
        return addslashes($string);
    }

    function next_result() {
        return mysqli_next_result($this->dbConnection);
    }

    function store_result() {
        return mysqli_store_result($this->dbConnection);
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

    /* private function checkQuery($query) {
      //first tokenize to identify the query type
      if ($this->mcEnabled == true && $this->doNotProcessQry === false) {
      list($type, $operation) = explode(' ', trim($query));
      if (strtolower($type) == 'delete') {
      if (preg_match('/\bfrom\b\s*(\w+)\s*(.*)/i', $query, $t)) {
      $this->checkAndInvalidate($t[1]);
      }
      return;
      }
      if (strtolower($type) == 'truncate') {
      $table = str_ireplace('table ', '', trim($operation));
      $this->checkAndInvalidate($table);
      return;
      }
      if (strtolower($type) == 'insert') {
      if (preg_match("@into (.+?) @i", $operation, $t)) {
      $this->checkAndInvalidate($t[1]);
      }
      return;
      }
      if (strtolower($type) == 'update') {
      if (preg_match('/\bUPDATE\b\s*(\w+)\s/i', $query, $t)) {
      $this->checkAndInvalidate($t[1]);
      }
      }
      if (strtolower($type) == 'set') {
      if (stripos($query, 'session.time_zone') !== false) {
      $this->tz = trim(substr($query, strpos($query, '=')), "'");
      }
      }
      }
      } */

    private function updateValues($key, $data) {
        if ($this->mcEnabled == true) {
            $done = $this->mc->add($key, $data, MEMCACHE_COMPRESSED, $this->ttl);
            if ($done == false) {
                $this->mc->replace($key, $data, MEMCACHE_COMPRESSED, $this->ttl);
            }
        }
    }

    private function checkAndInvalidate($table) {
        if ($this->mcEnabled == true) {
            $dbkey = md5($_SERVER ['HTTP_HOST'] . $this->dbf . '-' . trim($table));
            $this->updateValues($dbkey, microtime(1));
            $this->checkRelations($table);
        }
    }

    private function checkRelations($table) {
        if ($this->mcEnabled == true) {
            if ($this->relations == false or ! isset($this->relations['Trigger'][$table])) {
                return;
            }
            foreach ($this->relations['Trigger'][$table] as $cTable) {
                $this->checkAndInvalidate($cTable);
            }
        }
    }

    private function tzTag($query) {
        if ($this->mcEnabled == true) {
            if ($this->relations == false or ! isset($this->relations['TZ'])) {
                return $query;
            }
            $cTest = '@( ' . join(' | ', $this->relations['TZ']) . ' )@';
            if (preg_match($cTest, str_replace(',', ' , ', $query))) {
                return $this->tz . ':' . $query;
            }
        }
        return $query;
    }

    private function isCached($query, $fa = 'X') {

        if ($this->mcEnabled == false) {
            return false;
        }
        if (!stripos($query, ' from ')) {
            return false;
        }
        $key = md5($this->dbf . $this->tzTag($query) . '-' . intval($fa));
        $value = $this->mc->get($key);
        if (!$value) {
            return false;
        }
        return $this->validateCached($value, $query);
    }

    private function isCacheableQuery($query) {
        if ($this->mcEnabled == false) {
            return false;
        }
        if (!stripos($query, ' from ')) {
            return false;
        }
        $rv = true;
        $cc = array(' call', ' rand');
        return $rv && (!preg_match('@' . join('|', $cc) . '@', $query));
    }

    private function setCache($data, $query, $fa = 'X') {
        if (!$this->isCacheableQuery($query))
            return;
        $key = md5($this->dbf . $this->tzTag($query) . '-' . intval($fa));
        $this->updateValues($key, array('ts' => microtime(1), 'data' => $data));
    }

    private function validateCached($value, $query) {
        $rv = $value['data'];
        $tableReg = $this->getTables();
        $query = str_replace(array("\n", ',', ',  '), array(" ", ', ', ', '), $query) . ' ';
        preg_match_all($tableReg, $query, $m);
        foreach ($m[1] as $table) {
            $k = md5($_SERVER ['HTTP_HOST'] . $this->dbf . '-' . trim($table));
            $g = $this->mc->get($k);
            if (!$g or $g > $value['ts']) {
                $rv = false;
                break;
            }
        }
        return $rv;
    }

    private function getTables() {
        $key = $this->dbf . '-tableRegEx';
        if (($tableRegex = $this->mc->get($key)) == false) {
            $tables = sprintf("select table_name from information_schema.tables where table_schema = '%s'", $this->dbf);
            $this->doNotProcessQry = true;
            $rs = $this->query($tables, $params);
            $this->doNotProcessQry = false;
            $tableList = array();
            while (($rd = $this->fetch_row($rs)) !== NULL) {
                $tableList[] = $rd[0];
            }
            $tableRegex = '@( ' . join(' | ', $tableList) . ' )@isU';
            $this->updateValues($key, $tableRegex);
        }
        return $tableRegex;
    }

    /*
      @Functions added from functions in admin folder by niju
     */

    function getMulipleData($query, $params, $fetch_array = false) {
        if (($retval = $this->isCached($query, $fetch_array)) == false) {
            try {
                $rs = $this->query($query, $params);
                if ($this->num_rows($rs) == 0)
                    return false;
                $retval = array();
                if ($fetch_array) {
                    while ($row = $this->fetch_array($rs)) {
                        if (count($row) > 1)
                            $retval [] = $row;
                        else
                            $retval [] = $row [0];
                    }
                } else {
                    while ($row = $this->fetch_row($rs)) {
                        if (count($row) > 1) {
                            $retval [] = $row;
                        } else {
                            $retval [] = $row [0];
                        }
                    }
                }
                if ($this->mcEnabled == true)
                    $this->setCache($retval, $query, $fetch_array);
                $this->clearResult($rs);
            } catch (Exception $e) {
                file_put_contents('php://stderr', print_r($e, TRUE));
                file_put_contents('php://stderr', print_r($query, TRUE));
                throw new \Exception($e);
            }
        }
        return $retval;
    }

    function getItemFromDB($query, $params) {
        if (($retval = $this->isCached($query)) == false) {
            $rs = $this->query($query, $params);
            if ($this->num_rows($rs) == 0)
                return false;
            $retval = $this->fetch_row($rs);
            if ($this->mcEnabled == true)
                $this->setCache($retval, $query);
            $this->clearResult($rs);
        }
        return $retval [0];
    }

    function getFromDB($query, $params, $fetchArray = false) {
        if (($retval = $this->isCached($query, $fetchArray)) == false) {
            $rs = $this->query($query, $params);
            if ($this->num_rows($rs) == 0)
                return false;
            if ($fetchArray) {
                $retval = $this->fetch_array($rs);
            } else {
                $retval = $this->fetch_row($rs);
            }
            if ($this->mcEnabled == true)
                $this->setCache($retval, $query, $fetchArray);
            $this->clearResult($rs);
        }
        return $retval;
    }

    function getArrayFromDB($query, $params) {
        if (($retval = $this->isCached($query)) == false) {
            $rs = $this->query($query, $params);
            if ($this->num_rows($rs) == 0)
                return false;
            else {
                $retval = array();
                while ($row = $this->fetch_row($rs)) {
                    $retval [$row [0]] = $row [1];
                }
            }
            if ($this->mcEnabled == true)
                $this->setCache($retval, $query);
            $this->clearResult($rs);
        }
        return $retval;
    }

    /**
     * Function to clear the resultset to avoid the commands out of sync error
     *
     * @param resource $result
     * @author Niju N B
     */
    function clearResult($result) {
        if (is_resource($result)) {
            $this->free_result($result);
        }
        while ($this->next_result()) {
            $result = $this->store_result();
            if ($result) {
                $this->free_result($result);
            }
        }
        $this->lastResult = false;
    }

    function select_db($db) {

        $rs = mysqli_select_db($this->dbConnection, $db); // or die("Could not select database");
        if ($rs == false && php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
            print_r(debug_backtrace());
        }
    }

}

class cgoDynamiteDB {

    private $AWS_ACCESS_KEY_ID;
    private $AWS_SECRET_ACCESS_KEY;
    private $DynamoDBclient;
    private $AddTablePrefix;
    private $DynamoDBMarshaler;

    public function __construct() {

        $AWS_ACCESS_KEY_ID = AWSDYNAMODBACCESSKEY;
        $AWS_SECRET_ACCESS_KEY = AWSDYNAMODBPASSWORDKEY;
        $AWS_REGION = AWSDYNAMODBDEFAULTREGION;
        $this->DynamoDBclient = \Aws\DynamoDb\DynamoDbClient::factory(array(
                    'version' => '2012-08-10',
                    'credentials' => array(
                        'key' => $AWS_ACCESS_KEY_ID,
                        'secret' => $AWS_SECRET_ACCESS_KEY,
                    ),
                    'region' => $AWS_REGION
        ));
        $this->DynamoDBMarshaler = new Aws\DynamoDb\Marshaler();
        $this->AddTablePrefix = true;
    }

    public function AddTablePrefix($AddPrefix) {
        $this->AddTablePrefix = $AddPrefix;
    }

    protected function SanitiseGeneralArray($data) {
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            } elseif (is_array($value)) {
                $data[$key] = $this->SanitiseGeneralArray($value);
            }
        }
        return $data;
    }

    protected function SanitiseArray($data) {
        foreach ($data as $key => $value) {
            if ($value['val'] === '') {
                $data[$key]['val'] = null;
            } elseif (is_array($value['val'])) {
                $data[$key]['val'] = $this->SanitiseGeneralArray($value['val']);
            }
        }
        return $data;
    }

    protected function CompressArray($data) {
        $newdata = array();
        foreach ($data as $value) {
            $newdata[$value['col']] = $value['val'];
        }
        return $newdata;
    }

    protected function boxValue($type, $val) {
        //WTF!!!
        return (string) $val;
        /* 	switch ($type) {
          case 'S':
          return (string) $val;
          break;
          case 'N':
          if (strpos($mystring, ".") !== false)
          return (float) $val;
          else
          return (int) $val;
          break;
          case 'BOOL':
          return filter_var($val, FILTER_VALIDATE_BOOLEAN);
          break;
          default:
          throw new \Exception('Boxer not implemented');
          } */
    }

    protected function SplitMapKeys($col, &$ExpressionAttributeNames, &$count, &$filtercolumn) {
        $mapcols = explode(".", $col);
        $filtercolumn = '';
        foreach ($mapcols as $mapcol) {
            $ExpressionAttributeNames['#p' . $count] = $mapcol;
            $filtercolumn = ($filtercolumn == '' ? '#p' . $count : $filtercolumn . '.#p' . $count);
            $count = $count + 1;
        }

        /* {
          "TableName": "MyTable",
          "FilterExpression": "#k_Compatible.#k_RAM = :v_Compatible_RAM",
          "ExpressionAttributeNames": {
          "#k_Compatible": "Compatible",
          "#k_RAM": "RAM"
          },
          "ExpressionAttributeValues": {
          ":v_Compatible_RAM": "RAM1"
          }
          } */
    }

    public function query($table, $data, $type, $ConsistentRead = true) {
        if ($this->AddTablePrefix) {
            $table = AWSDYNAMODBTABLEPREFIX . $table;
        }
        if ($type == 'getItem') {

            if (!array_key_exists('PartitionKey', $data)) {
                file_put_contents('php://stderr', print_r('Missing Partition Key in getItem DynaDB for table ' . $table, TRUE));
                file_put_contents('php://stderr', print_r($data, TRUE));
                throw new \Exception('Missing Partition Key in getItem DynaDB');
            } elseif (!array_key_exists('getAttributes', $data)) {
                file_put_contents('php://stderr', print_r('Missing getAttributes in getItem DynaDB for table ' . $table, TRUE));
                file_put_contents('php://stderr', print_r($data, TRUE));
                throw new \Exception('Missing getAttributes in getItem DynaDB');
            }

            if (array_key_exists('SortKey', $data)) {
                $Key = array(
                    $data['PartitionKey']['col'] => $data['PartitionKey']['val'],
                    $data['SortKey']['col'] => $data['SortKey']['val']);
            } else {
                $Key = array($data['PartitionKey']['col'] => $data['PartitionKey']['val']);
            }

            $ExpressionAttributeNames = array();
            $ProjectionExpression = '';
            $colcnt = 1;

            foreach ($data['getAttributes'] as $value) {
                $ExpressionAttributeNames['#p' . $colcnt] = $value;
                $ProjectionExpression = ($ProjectionExpression == '' ? '#p' . $colcnt : $ProjectionExpression . ', #p' . $colcnt );
                $colcnt++;
            }

            $Doagain = true;
            while ($Doagain) {
                $Doagain = false;
                try {

                    $getItem = array(
                        'TableName' => $table,
                        'ConsistentRead' => $ConsistentRead,
                        'Key' => $this->DynamoDBMarshaler->marshalItem($Key),
                        'ExpressionAttributeNames' => $ExpressionAttributeNames,
                        'ProjectionExpression' => $ProjectionExpression,
                        'ReturnConsumedCapacity' => 'TOTAL'
                    );

                    $resp = $this->DynamoDBclient->getItem($getItem);
                    if (isset($resp['Item'])) {
                        $response = $this->DynamoDBMarshaler->unmarshalItem($resp['Item']);
                    } else {
                        $response = array();
                    }
                } catch (\Aws\Exception\AwsException $e) {
                    file_put_contents('php://stderr', print_r($data, TRUE));
                    file_put_contents('php://stderr', print_r('Table ' . $table . ' Caught exception: ' . $e->getMessage() . ' HTTP Status Code -- ' . $e->getStatusCode() . "\n", TRUE));
                    if ($e->getStatusCode() != '503') {
                        print_r($data);
                        throw new Exception('Table ' . $table . '---' . $e->getMessage());
                    } else {
                        $Doagain = true;
                    }
                }
            }
            //$response['ConsumedCapacity'] = $resp['ConsumedCapacity']['CapacityUnits'];				
        } elseif ($type == 'batchGetItem') {
            
        } elseif ($type == 'query') {
            $response = null;
            if (!array_key_exists('PartitionKey', $data)) {
                file_put_contents('php://stderr', print_r('Missing Partition Key in query DynaDB for table ' . $table, TRUE));
                file_put_contents('php://stderr', print_r($data, TRUE));
                throw new \Exception('Missing Partition Key in query DynaDB');
            } elseif (!array_key_exists('oper', $data['PartitionKey'])) {
                file_put_contents('php://stderr', print_r('Missing operator for Partition Key in query DynaDB for table ' . $table, TRUE));
                file_put_contents('php://stderr', print_r($data, TRUE));
                throw new \Exception('Missing operator for Partition Key in query DynaDB');
            } elseif (!array_key_exists('queryAttributes', $data)) {
                file_put_contents('php://stderr', print_r('Missing queryAttributes in query DynaDB for table ' . $table, TRUE));
                file_put_contents('php://stderr', print_r($data, TRUE));
                throw new \Exception('Missing queryAttributes in query DynaDB');
            }
            $request = array('TableName' => $table);

            if (array_key_exists('IndexName', $data)) {
                if ($data['IndexName'] != '')
                    $request['IndexName'] = $data['IndexName'];
            }else {
                $request['ConsistentRead'] = $ConsistentRead;
            }
            $colcnt = 1;
            $KeyConditionExpression = '';
            $ExpressionAttributeValues = array();
            $ExpressionAttributeNames = array();
            if (array_key_exists('SortKey', $data)) {
                if (array_key_exists('SortKeyBetween', $data['SortKey']) && $data['SortKey']['SortKeyBetween'] == true) {
                    if (!array_key_exists('val1', $data['SortKey']) || !array_key_exists('val2', $data['SortKey'])) {
                        throw new \Exception('Missing operator for Sort Keys between values missing in query DynaDB ' . $table);
                    }
                    $Key = array($data['PartitionKey']['col'] => $data['PartitionKey']['val'], $data['SortKey']['col'] => $data['SortKey']['val1']);
                    $KeyConditionExpression = $data['PartitionKey']['col'] . $data['PartitionKey']['oper'] . ' :v1 and ' . $data['SortKey']['col'] . ' between  :v2 and :v3 ';
                    $ExpressionAttributeValues = array(':v1' => $data['PartitionKey']['val'], ':v2' => $data['SortKey']['val1'], ':v3' => $data['SortKey']['val2']);
                    $colcnt = $colcnt + 3;
                } else {
                    if (!array_key_exists('oper', $data['SortKey'])) {
                        throw new \Exception('Missing operator for Sort Key in query DynaDB ' . $table);
                    }
                    $Key = array($data['PartitionKey']['col'] => $data['PartitionKey']['val'], $data['SortKey']['col'] => $data['SortKey']['val']);
                    $KeyConditionExpression = $data['PartitionKey']['col'] . $data['PartitionKey']['oper'] . ' :v1 and ' . $data['SortKey']['col'] . $data['SortKey']['oper'] . ' :v2 ';
                    $ExpressionAttributeValues = array(':v1' => $data['PartitionKey']['val'], ':v2' => $data['SortKey']['val']);
                    $colcnt = $colcnt + 2;
                }
            } else {
                $Key = array($data['PartitionKey']['col'] => $data['PartitionKey']['val']);
                $KeyConditionExpression = $data['PartitionKey']['col'] . $data['PartitionKey']['oper'] . ' :v1 ';
                $ExpressionAttributeValues = array(':v1' => $data['PartitionKey']['val']);
                $colcnt = $colcnt + 1;
            }
            $request['KeyConditionExpression'] = $KeyConditionExpression;

            $ProjectionExpression = '';
            foreach ($data['queryAttributes'] as $value) {
                $ExpressionAttributeNames['#p' . $colcnt] = $value;
                $ProjectionExpression = ($ProjectionExpression == '' ? '#p' . $colcnt : $ProjectionExpression . ', #p' . $colcnt );
                $colcnt ++;
            }

            if (array_key_exists('Condition', $data)) {
                $FilterExpression = '';
                foreach ($data['Condition'] as $value) {
                    if (!array_key_exists('ConditionJoin', $value)) {
                        $value['ConditionJoin'] = ' and ';
                    }
                    if (!array_key_exists('ConditionOpeningBrace', $value)) {
                        $value['ConditionOpeningBrace'] = '';
                    }
                    if (!array_key_exists('ConditionClosingBrace', $value)) {
                        $value['ConditionClosingBrace'] = '';
                    }
                    if (strpos($value['col'], ".") === false) {
                        $ExpressionAttributeNames['#p' . $colcnt] = $value['col'];
                        $filtercolumn = '#p' . $colcnt;
                    } else {
                        $this->SplitMapKeys($value['col'], $ExpressionAttributeNames, $colcnt, $filtercolumn);
                    }
                    if (array_key_exists('ConditionBetween', $value) && $value['ConditionBetween'] == true) {
                        if (!array_key_exists('val1', $value) || !array_key_exists('val2', $value)) {
                            throw new \Exception('Missing operator for Sort Keys between values missing in query DynaDB ' . $table);
                        }
                        $ExpressionAttributeValues[':v' . $colcnt] = $value['val1'];
                        $ExpressionAttributeValues[':v' . ($colcnt + 1)] = $value['val2'];
                        $FilterExpression = ($FilterExpression == '' ? $value['ConditionOpeningBrace'] . ' ' . $filtercolumn . ' between :v' . $colcnt . ' and :v' . ($colcnt + 1) . $value['ConditionClosingBrace'] : $FilterExpression . $value['ConditionOpeningBrace'] . $value['ConditionJoin'] . ' ' . $filtercolumn . ' between :v' . $colcnt . ' and :v' . ($colcnt + 1) . $value['ConditionClosingBrace']);
                        $colcnt = $colcnt + 2;
                    } else {

                        $ExpressionAttributeValues[':v' . $colcnt] = $value['val'];
                        $FilterExpression = ($FilterExpression == '' ? $value['ConditionOpeningBrace'] . ' ' . $filtercolumn . $value['oper'] . ':v' . $colcnt . $value['ConditionClosingBrace'] : $FilterExpression . $value['ConditionJoin'] . $value['ConditionOpeningBrace'] . ' ' . $filtercolumn . $value['oper'] . ':v' . $colcnt . $value['ConditionClosingBrace'] );
                        $colcnt++;
                    }
                }
                //$FilterExpression = "{" . $FilterExpression ."}";
                $request['FilterExpression'] = $FilterExpression;
            }

            if (array_key_exists('ExclusiveStartKey', $data)) {
                $request['ExclusiveStartKey'] = $this->DynamoDBMarshaler->marshalItem(array($data['ExclusiveStartKey']['col'] => $data['ExclusiveStartKey']['val']));
            }
            try {
                $request['ExpressionAttributeValues'] = $this->DynamoDBMarshaler->marshalItem($ExpressionAttributeValues);
            } catch (\Aws\Exception\AwsException $e) {
                print_r($data);
                file_put_contents('php://stderr', print_r($data, TRUE));
                file_put_contents('php://stderr', print_r('Table ' . $table . ' Caught exception: ' . $e->getMessage() . ' HTTP Status Code -- ' . $e->getStatusCode() . "\n", TRUE));
                throw new Exception('Table ' . $table . '---' . $e->getMessage());
            }
            $request['ExpressionAttributeNames'] = $ExpressionAttributeNames;
            $request['ProjectionExpression'] = $ProjectionExpression;
            if (array_key_exists('Limit', $data)) {
                $request['Limit'] = $data['Limit'];
            } else {
                $request['Limit'] = 1;
            }
            $request['ReturnConsumedCapacity'] = 'TOTAL';
            $response = array();
            $loopedonce = false;
            do {
                if (isset($resp) && isset($resp['LastEvaluatedKey'])) {
                    $request['ExclusiveStartKey'] = $resp['LastEvaluatedKey'];
                }
                $Doagain = true;
                while ($Doagain) {
                    $Doagain = false;
                    try {
                        $resp = $this->DynamoDBclient->query($request);
                        if ($resp['Count'] == 0 && $loopedonce == false && array_key_exists('IndexName', $data)) {
                            //file_put_contents('php://stderr', print_r("Dynamodb query got zero records trying again Table - " . $table . " INdex " . $data['IndexName'] ."\n" , TRUE));
                            sleep(1);
                            $resp = $this->DynamoDBclient->query($request);
                            //file_put_contents('php://stderr', print_r("Dynamodb query tired again count - " . $resp['Count'] . " Table - " . $table . " INdex " . $data['IndexName'] ."\n", TRUE));
                        }
                        $loopedonce = true;
                        if ($resp['Count'] > 0) {
                            foreach ($resp['Items'] as $DynaItem) {
                                array_push($response, $this->DynamoDBMarshaler->unmarshalItem($DynaItem));
                            }
                            //$response['ConsumedCapacity'] =  $resp['ConsumedCapacity']['CapacityUnits'];
                        }
                    } catch (\Aws\Exception\AwsException $e) {

                        file_put_contents('php://stderr', print_r($data, TRUE));
                        file_put_contents('php://stderr', print_r($request, TRUE));
                        file_put_contents('php://stderr', print_r('Table ' . $table . ' Caught exception: ' . $e->getMessage() . ' HTTP Status Code -- ' . $e->getStatusCode() . "\n", TRUE));
                        if ($e->getStatusCode() != '503') {
                            print_r($data);
                            throw new Exception('Table ' . $table . '---' . $e->getMessage());
                        } else {
                            $Doagain = true;
                        }
                    }
                }
            } while (isset($resp['LastEvaluatedKey']));
        } elseif ($type == 'scan') {
            if (!array_key_exists('Data', $data)) {
                throw new \Exception('Missing Data in scan DynaDB for table ' . $table);
            }
            $request = array('TableName' => $table);
            if (array_key_exists('IndexName', $data)) {
                if ($data['IndexName'] != '')
                    $request['IndexName'] = $data['IndexName'];
            }else {
                $request['ConsistentRead'] = $ConsistentRead;
            }
            $ProjectionExpression = '';
            $colcnt = 1;
            foreach ($data['Data'] as $value) {
                $ExpressionAttributeNames['#p' . $colcnt] = $value['col'];
                $ProjectionExpression = ($ProjectionExpression == '' ? '#p' . $colcnt : $ProjectionExpression . ', #p' . $colcnt );
                $colcnt++;
            }
            $request['ProjectionExpression'] = $ProjectionExpression;

            if (array_key_exists('Condition', $data)) {
                if (!array_key_exists('ConditionJoin', $data)) {
                    $data['ConditionJoin'] = ' and ';
                }
                $FilterExpression = '';
                foreach ($data['Condition'] as $value) {
                    if (array_key_exists('ConditionBetween', $value) && $value['ConditionBetween'] == true) {
                        if (!array_key_exists('val1', $value) || !array_key_exists('val2', $value)) {
                            throw new \Exception('Missing operator for Sort Keys between values missing in query DynaDB ' . $table);
                        }
                        $ExpressionAttributeNames['#p' . $colcnt] = $value['col'];
                        $ExpressionAttributeValues[':v' . $colcnt] = array($value['type'] => (string) $value['val1']);
                        $ExpressionAttributeValues[':v' . ($colcnt + 1)] = array($value['type'] => (string) $value['val2']);
                        $FilterExpression = ($FilterExpression == '' ? '#p' . $colcnt . ' between :v' . $colcnt . ' and :v' . $colcnt + 1 : $FilterExpression . $data['ConditionJoin'] . ' #p' . $colcnt . ' between :v' . $colcnt . ' and :v' . $colcnt + 1);
                        $colcnt = $colcnt + 2;
                    } else {
                        $ExpressionAttributeNames['#p' . $colcnt] = $value['col'];
                        $ExpressionAttributeValues[':v' . $colcnt] = array($value['type'] => (string) $value['val']);
                        $FilterExpression = ($FilterExpression == '' ? '#p' . $colcnt . $value['oper'] . ':v' . $colcnt : $FilterExpression . $data['ConditionJoin'] . ' #p' . $colcnt . $value['oper'] . ':v' . $colcnt );
                        $colcnt++;
                    }
                }
                $request['FilterExpression'] = $FilterExpression;
                //$request['ExpressionAttributeValues']=$ExpressionAttributeValues;		
                try {
                    $request['ExpressionAttributeValues'] = $this->DynamoDBMarshaler->marshalItem($ExpressionAttributeValues);
                } catch (\Aws\Exception\AwsException $e) {
                    print_r($data);
                    file_put_contents('php://stderr', print_r($data, TRUE));
                    file_put_contents('php://stderr', print_r('Table ' . $table . ' Caught exception: ' . $e->getMessage() . ' HTTP Status Code -- ' . $e->getStatusCode() . "\n", TRUE));
                    throw new Exception('Table ' . $table . '---' . $e->getMessage());
                }
            }
            if (array_key_exists('ExclusiveStartKey', $data)) {
                $request['ExclusiveStartKey'] = $this->DynamoDBMarshaler->marshalItem(array($data['ExclusiveStartKey']['col'] => $data['ExclusiveStartKey']['val']));
            }
            $limit = 0;
            if (array_key_exists('Limit', $data)) {
                $request['Limit'] = $data['Limit'];
                $limit = $data['Limit'];
            }
            $request['ExpressionAttributeNames'] = $ExpressionAttributeNames;
            $request['ProjectionExpression'] = $ProjectionExpression;

            $cntr = 1;
            $response = array();
            $loopedonce = false;
            do {
                if (isset($resp) && isset($resp['LastEvaluatedKey'])) {
                    $request['ExclusiveStartKey'] = $resp['LastEvaluatedKey'];
                }
                $Doagain = true;
                while ($Doagain) {
                    $Doagain = false;
                    try {
                        $resp = $this->DynamoDBclient->scan($request);
                        if ($resp['Count'] == 0 && $loopedonce == false && array_key_exists('IndexName', $data)) {
                            file_put_contents('php://stderr', print_r("Dynamodb query got zero records trying again Table - " . $table . " INdex " . $data['IndexName'] . "\n", TRUE));
                            sleep(1);
                            $resp = $this->DynamoDBclient->query($request);
                            file_put_contents('php://stderr', print_r("Dynamodb query tired again count - " . $resp['Count'] . " Table - " . $table . " INdex " . $data['IndexName'] . "\n", TRUE));
                        }
                        $loopedonce = true;
                        if ($resp['Count'] > 0) {
                            foreach ($resp['Items'] as $DynaItem) {
                                array_push($response, $this->DynamoDBMarshaler->unmarshalItem($DynaItem));
                            }
                            //$response['ConsumedCapacity'] =  $resp['ConsumedCapacity']['CapacityUnits'];
                        }
                    } catch (\Aws\Exception\AwsException $e) {

                        file_put_contents('php://stderr', print_r($data, TRUE));
                        file_put_contents('php://stderr', print_r($request, TRUE));
                        file_put_contents('php://stderr', print_r('Table ' . $table . ' Caught exception: ' . $e->getMessage() . ' HTTP Status Code -- ' . $e->getStatusCode() . "\n", TRUE));
                        if ($e->getStatusCode() != '503') {
                            print_r($data);
                            throw new Exception('Table ' . $table . '---' . $e->getMessage());
                        } else {
                            $Doagain = true;
                        }
                    }
                }

                if ($cntr >= $limit && $limit > 0) {
                    break;
                }
                $cntr++;
            } while (isset($resp['LastEvaluatedKey']));
        }
        return $response;
    }

    public function perform($table, $action, $data, &$response) {
        $response = array();
        if ($this->AddTablePrefix) {
            $table = AWSDYNAMODBTABLEPREFIX . $table;
        }
        if ($action == 'insert') {
            if (!array_key_exists('Data', $data)) {
                file_put_contents('php://stderr', print_r('Missing Data in Insert DynaDB for table ' . $table, TRUE));
                file_put_contents('php://stderr', print_r($data, TRUE));
                throw new \Exception('Missing Data in Insert DynaDB');
            }
            $insertdata = array();
            $ExpressionAttributeNames = array();
            $ExpressionAttributeValues = array();
            $data['Data'] = $this->SanitiseArray($data['Data']);
            $data['Data'] = $this->CompressArray($data['Data']);
            $colcnt = 1;

            try {
                $request = array(
                    'TableName' => $table,
                    'Item' => $this->DynamoDBMarshaler->marshalItem($data['Data']),
                    'ReturnConsumedCapacity' => 'TOTAL',
                    'ReturnValues' => 'ALL_OLD',
                    'ReturnItemCollectionMetrics' => 'SIZE'
                );
            } catch (\Aws\Exception\AwsException $e) {
                $response['msg'] = 'Table ' . $table . '---' . $e->getMessage();
                $response['data'] = $data;
                file_put_contents('php://stderr', print_r($data, TRUE));
                file_put_contents('php://stderr', print_r('Table ' . $table . ' Caught exception: ' . $e->getMessage() . ' HTTP Status Code -- ' . $e->getStatusCode() . "\n", TRUE));
                throw $e;
            }

            if (array_key_exists('ConditionExpression', $data)) {
                $request['ConditionExpression'] = $data['ConditionExpression'];
            }
            $Doagain = true;
            while ($Doagain) {
                $Doagain = false;
                try {
                    $response = $this->DynamoDBclient->putItem($request);
                } catch (\Aws\Exception\AwsException $e) {
                    // The PutItem operation failed.
                    if ($e->getAwsErrorCode() == 'ConditionalCheckFailedException' && array_key_exists('ConditionExpression', $data)) {
                        return false;
                    } else {
                        file_put_contents('php://stderr', print_r($data, TRUE));
                        file_put_contents('php://stderr', print_r('Table ' . $table . ' Caught exception: ' . $e->getMessage() . ' HTTP Status Code -- ' . $e->getStatusCode() . "\n", TRUE));
                        if ($e->getStatusCode() != '503') {
                            $response['msg'] = 'Table ' . $table . '---' . $e->getMessage();
                            $response['data'] = $data;
                            throw $e;
                        } else {
                            $Doagain = true;
                        }
                    }
                }
            }
        } elseif ($action == 'update') {
            if (!array_key_exists('PartitionKey', $data)) {
                file_put_contents('php://stderr', print_r('Missing Partition Key in Update DynaDB for table ' . $table, TRUE));
                file_put_contents('php://stderr', print_r($data, TRUE));
                throw new \Exception('Missing Partition Key in Update DynaDB');
            } elseif (!array_key_exists('Data', $data)) {
                file_put_contents('php://stderr', print_r('Missing Data in Update DynaDB for table ' . $table, TRUE));
                file_put_contents('php://stderr', print_r($data, TRUE));
                throw new \Exception('Missing Data in Update DynaDB');
            }
            if (array_key_exists('createifnotexists', $data)) {
                if ($data['createifnotexists'] == true) {
                    $createifnotexists = true;
                } else {
                    $createifnotexists = false;
                }
            } else {
                $createifnotexists = false;
            }
            $UpdateAttributeConidtion = '';
            if (array_key_exists('SortKey', $data)) {
                try {
                    $Key = $this->DynamoDBMarshaler->marshalItem(array($data['PartitionKey']['col'] => $data['PartitionKey']['val'], $data['SortKey']['col'] => $data['SortKey']['val']));
                } catch (\Aws\Exception\AwsException $e) {
                    // The PutItem operation failed.
                    if ($e->getAwsErrorCode() == 'ConditionalCheckFailedException' && array_key_exists('ConditionExpression', $data)) {
                        
                    } else {
                        $response['msg'] = 'Table ' . $table . '---' . $e->getMessage();
                        $response['data'] = $data;
                        file_put_contents('php://stderr', print_r($data, TRUE));
                        file_put_contents('php://stderr', print_r('Table ' . $table . ' Caught exception: ' . $e->getMessage() . ' HTTP Status Code -- ' . $e->getStatusCode() . "\n", TRUE));
                        throw $e;
                    }
                }
                if ($createifnotexists == false) {
                    $UpdateAttributeConidtion = " attribute_exists(" . $data['PartitionKey']['col'] . ")  and attribute_exists(" . $data['SortKey']['col'] . ")";
                }
            } else {
                try {
                    $Key = $this->DynamoDBMarshaler->marshalItem(array($data['PartitionKey']['col'] => $data['PartitionKey']['val']));
                } catch (\Aws\Exception\AwsException $e) {
                    // The PutItem operation failed.
                    if ($e->getAwsErrorCode() == 'ConditionalCheckFailedException' && array_key_exists('ConditionExpression', $data)) {
                        
                    } else {
                        $response['msg'] = 'Table ' . $table . '---' . $e->getMessage();
                        $response['data'] = $data;
                        file_put_contents('php://stderr', print_r($data, TRUE));
                        file_put_contents('php://stderr', print_r('Table ' . $table . ' Caught exception: ' . $e->getMessage() . ' HTTP Status Code -- ' . $e->getStatusCode() . "\n", TRUE));
                        throw $e;
                    }
                }
                if ($createifnotexists == false) {
                    $UpdateAttributeConidtion = " attribute_exists(" . $data['PartitionKey']['col'] . ")";
                }
            }
            $data['Data'] = $this->SanitiseArray($data['Data']);
            $colcnt = 1;
            $ExpressionAttributeNames = array();
            $ExpressionAttributeValues = array();
            $UpdateExpression = '';
            foreach ($data['Data'] as $value) {
                if (array_key_exists('oper', $value)) {
                    $oper = $value['oper'];
                } else {
                    $oper = '=';
                }
                $ExpressionAttributeNames['#p' . $colcnt] = $value['col'];
                $ExpressionAttributeValues[':v' . $colcnt] = $value['val'];
                if (trim($oper) == '=') {
                    $UpdateExpression = ($UpdateExpression == '' ? 'set ' . '#p' . $colcnt . ' = ' . ':v' . $colcnt : $UpdateExpression . ', #p' . $colcnt . ' = ' . ':v' . $colcnt);
                } else {
                    $UpdateExpression = ($UpdateExpression == '' ? 'set ' . '#p' . $colcnt . ' = ' . '#p' . $colcnt . ' ' . $oper . ' :v' . $colcnt : $UpdateExpression . ', #p' . $colcnt . ' = ' . '#p' . $colcnt . ' ' . $oper . ' :v' . $colcnt);
                }
                $colcnt++;
            }

            $ConditionExpression = '';
            try {
                if (array_key_exists('Condition', $data)) {
                    foreach ($data['Condition'] as $value) {
                        if (array_key_exists('ConditionBetween', $value) && $value['ConditionBetween'] == true) {
                            if (!array_key_exists('val1', $value) || !array_key_exists('val2', $value)) {
                                throw new \Exception('Missing operator for Sort Keys between values missing in query DynaDB ' . $table);
                            }
                            $ExpressionAttributeNames['#p' . $colcnt] = $value['col'];
                            $ExpressionAttributeValues[':v' . $colcnt] = $value['val1'];
                            $ExpressionAttributeValues[':v' . ($colcnt + 1)] = $value['val2'];
                            $ConditionExpression = ($ConditionExpression == '' ? '#p' . $colcnt . ' between :v' . $colcnt . ' and :v' . $colcnt + 1 : $ConditionExpression . ' and #p' . $colcnt . ' between :v' . $colcnt . ' and :v' . $colcnt + 1);
                            $colcnt = $colcnt + 2;
                        } else {
                            $ExpressionAttributeNames['#p' . $colcnt] = $value['col'];
                            $ExpressionAttributeValues[':v' . $colcnt] = $value['val'];
                            $ConditionExpression = ($ConditionExpression == '' ? '#p' . $colcnt . $value['oper'] . ':v' . $colcnt : $ConditionExpression . ' and #p' . $colcnt . $value['oper'] . ':v' . $colcnt );
                            $colcnt++;
                        }
                    }
                }
            } catch (\Exception $e) {
                file_put_contents('php://stderr', print_r($data, TRUE));
                file_put_contents('php://stderr', print_r('Table ' . $table . ' Caught exception: ' . $e->getMessage() . ' HTTP Status Code -- ' . $e->getStatusCode() . "\n", TRUE));
                throw $e;
                return false;
            }

            try {
                $request = array(
                    'TableName' => $table,
                    'Key' => $Key,
                    'ExpressionAttributeNames' => $ExpressionAttributeNames,
                    'ExpressionAttributeValues' => $this->DynamoDBMarshaler->marshalItem($ExpressionAttributeValues),
                    'UpdateExpression' => $UpdateExpression,
                    //'ConditionExpression' => '#P = :val2',
                    'ReturnValues' => 'ALL_NEW',
                    'ReturnConsumedCapacity' => 'TOTAL'
                );
            } catch (\Aws\Exception\AwsException $e) {
                // The PutItem operation failed.

                if ($e->getAwsErrorCode() == 'ConditionalCheckFailedException' && array_key_exists('ConditionExpression', $data)) {
                    return false;
                } else {
                    $response['msg'] = 'Table ' . $table . '---' . $e->getMessage();
                    $response['data'] = $data;
                    file_put_contents('php://stderr', print_r($data, TRUE));
                    file_put_contents('php://stderr', print_r('Table ' . $table . ' Caught exception: ' . $e->getMessage() . ' HTTP Status Code -- ' . $e->getStatusCode() . "\n", TRUE));
                    throw $e;
                }
            }
            if ($createifnotexists == false) {
                $ConditionExpression = ($ConditionExpression == '' ? $UpdateAttributeConidtion : $ConditionExpression . 'and ' . $UpdateAttributeConidtion);
            }
            if ($ConditionExpression != '') {
                $request['ConditionExpression'] = $ConditionExpression;
            }
            $Doagain = true;
            while ($Doagain) {
                $Doagain = false;
                try {
                    $response = $this->DynamoDBclient->updateItem($request);
                } catch (\Aws\Exception\AwsException $e) {
                    if ($e->getAwsErrorCode() == 'ConditionalCheckFailedException') {
                        
                    } else {
                        file_put_contents('php://stderr', print_r($data, TRUE));
                        file_put_contents('php://stderr', print_r('Table ' . $table . ' Caught exception: ' . $e->getMessage() . ' HTTP Status Code -- ' . $e->getStatusCode() . "\n", TRUE));
                        if ($e->getStatusCode() != '503') {
                            print_r($data);
                            echo 'Table ' . $table . ' Caught exception: ' . $e->getMessage() . "\n";
                            throw $e;
                        } else {
                            $Doagain = true;
                        }
                    }
                }
            }
        } elseif ($action == 'delete') {
            if (!array_key_exists('PartitionKey', $data)) {
                file_put_contents('php://stderr', print_r('Missing Partition Key in Delete DynaDB', TRUE));
                file_put_contents('php://stderr', print_r($data, TRUE));
                throw new \Exception('Missing Partition Key in Delete DynaDB');
            }
            if (array_key_exists('SortKey', $data)) {
                $Key = $this->DynamoDBMarshaler->marshalItem(array($data['PartitionKey']['col'] => $data['PartitionKey']['val'], $data['SortKey']['col'] => $data['SortKey']['val']));
            } else {
                $Key = $this->DynamoDBMarshaler->marshalItem(array($data['PartitionKey']['col'] => $data['PartitionKey']['val']));
            }
            $Doagain = true;
            while ($Doagain) {
                $Doagain = false;
                try {
                    $response = $this->DynamoDBclient->deleteItem(array(
                        'TableName' => $table,
                        'Key' => $Key,
                        'ReturnValues' => 'ALL_OLD',
                        'ReturnConsumedCapacity' => 'TOTAL'
                    ));
                } catch (\Aws\Exception\AwsException $e) {

                    file_put_contents('php://stderr', print_r($Key, TRUE));
                    file_put_contents('php://stderr', print_r('Table ' . $table . ' Caught exception: ' . $e->getMessage() . ' HTTP Status Code -- ' . $e->getStatusCode() . "\n", TRUE));

                    if ($e->getStatusCode() != '503') {
                        $response['msg'] = 'Table ' . $table . '--- Action: Delete ---' . $e->getMessage();
                        $response['data'] = $data;
                        print_r($data);
                        echo 'Table ' . $table . ' Caught exception: ' . $e->getMessage() . "\n";
                        throw $e;
                    } else {
                        $Doagain = true;
                    }
                }
            }
        } elseif ($action == 'bulkinsert') {
            if (!array_key_exists('Data', $data)) {
                file_put_contents('php://stderr', print_r('Missing Data in Insert DynaDB for table ' . $table, TRUE));
                file_put_contents('php://stderr', print_r($data, TRUE));
                throw new \Exception('Missing Data in Insert DynaDB');
            }
            $insertdata = array();
            $ExpressionAttributeNames = array();
            $ExpressionAttributeValues = array();
            $colcnt = 1;
            $ItemData = array();
            foreach ($data['Data'] as $putval) {
                $putval = $this->SanitiseArray($putval);
                $putval = $this->CompressArray($putval);
                array_push($ItemData, array('PutRequest' => array('Item' => $this->DynamoDBMarshaler->marshalItem($putval))));
            }
            try {
                $request = array(
                    'RequestItems' => array($table => $ItemData),
                    'ReturnConsumedCapacity' => 'TOTAL',
                    'ReturnItemCollectionMetrics' => 'SIZE'
                );
            } catch (\Aws\Exception\AwsException $e) {
                $response['msg'] = 'Table ' . $table . '---' . $e->getMessage();
                $response['data'] = $data;
                file_put_contents('php://stderr', print_r($data, TRUE));
                file_put_contents('php://stderr', print_r('Table ' . $table . ' Caught exception: ' . $e->getMessage() . ' HTTP Status Code -- ' . $e->getStatusCode() . "\n", TRUE));
                throw $e;
            }

            /* if(array_key_exists('ConditionExpression',$data)){
              $request['ConditionExpression'] = $data['ConditionExpression'];
              } */
            $Doagain = true;
            while ($Doagain) {
                $Doagain = false;
                try {
                    $response = $this->DynamoDBclient->batchWriteItem($request);
                } catch (\Aws\Exception\AwsException $e) {
                    // The PutItem operation failed.
                    if ($e->getAwsErrorCode() == 'ConditionalCheckFailedException' && array_key_exists('ConditionExpression', $data)) {
                        
                    } else {

                        file_put_contents('php://stderr', print_r($data, TRUE));
                        file_put_contents('php://stderr', print_r('Table ' . $table . ' Caught exception: ' . $e->getMessage() . ' HTTP Status Code -- ' . $e->getStatusCode() . "\n", TRUE));

                        if ($e->getStatusCode() != '503') {
                            $response['msg'] = 'Table ' . $table . '--- Action: Delete ---' . $e->getMessage();
                            $response['data'] = $data;
                            print_r($data);
                            echo 'Table ' . $table . ' Caught exception: ' . $e->getMessage() . "\n";
                            throw $e;
                        } else {
                            $Doagain = true;
                        }
                    }
                    return false;
                }
            }
        } else {
            throw new \Exception('Invalid action in perform of DynaDB');
        }
        return true;
    }

    public function DeleteTable($table) {
        $response = array();
        /* if ($this->AddTablePrefix) {
          $table = AWSDYNAMODBTABLEPREFIX . $table;
          } */

        try {
            $request = array("TableName" => $table);
            $myfile = fopen("/tmp/dropddb.txt", "a") or die("Unable to open file!");
            $response = $this->DynamoDBclient->deleteTable($request);
            $this->DynamoDBclient->waitUntil('TableNotExists', array(
                'TableName' => $table
            ));

            fwrite($myfile, "\n");
            fwrite($myfile, "\n");
            fwrite($myfile, $response);
            fclose($myfile);
            return $response;
        } catch (DynamoDbException $e) {
            echo "Unable to delete table:\n";
            echo $e->getMessage() . "\n";
        }
    }

    public function DescribeTable($table) {
        $response = array();
        /* if ($this->AddTablePrefix) {
          $table = AWSDYNAMODBTABLEPREFIX . $table;
          } */

        try {
            $request = array("TableName" => $table);
            $myfile = fopen("/tmp/describeddb.txt", "a") or die("Unable to open file!");
            $response = $this->DynamoDBclient->describeTable($request);
            fwrite($myfile, "\n");
            fwrite($myfile, "\n");
            fwrite($myfile, $response);
            fclose($myfile);
            return $response;
        } catch (DynamoDbException $e) {
            echo "Unable to describe table:\n";
            echo $e->getMessage() . "\n";
        }
    }

    public function CreateTable($tableobject) {
        $response = array();

        try {
            //$request = array("TableName"=>$table);
            $myfile = fopen("/tmp/createddb.txt", "a") or die("Unable to open file!");
            $response = $this->DynamoDBclient->createTable($tableobject);
            fwrite($myfile, "\n");
            fwrite($myfile, "\n");
            fwrite($myfile, $response);
            fclose($myfile);
            return $response;
        } catch (DynamoDbException $e) {
            echo "Unable to create table:\n";
            echo $e->getMessage() . "\n";
        }
    }

}

class firebasemessage {

    public function sendmessage($ttl, $labeltext, $bodytext, $title, $data, $fcmid) {
        $newNotification = new CloudFcmNotification();
        $response = $newNotification
                ->setTimeToLive($ttl)
                ->setAnalyticalLabel($labeltext)
                ->setBody($bodytext)
                ->setTitle($title)
                ->setSound('default')
                ->setData($data)
                ->to($fcmid)
                ->send();
    }

}

class cgoAWSSES {

    public function sendmail($to, $data) {
        $key = AWS_SES_ACCESSKEY;
        $secret = AWS_SES_PASSWORDKEY;
        $message = "SendEmail";
        $versionInBytes = chr(2);
        $signatureInBytes = hash_hmac('sha256', $message, $secret, true);
        $signatureAndVer = $versionInBytes . $signatureInBytes;
        $smtpPassword = base64_encode($signatureAndVer);

        $SESclient = \Aws\Ses\SesClient::factory(array(
                    'version' => '2010-12-01',
                    'credentials' => array(
                        'key' => AWS_SES_ACCESSKEY,
                        'secret' => AWS_SES_PASSWORDKEY,
                    ),
                    'region' => AWS_SES_DEFAULT_REGION
        ));
        print_r(array(
            'version' => '2010-12-01',
            'credentials' => array(
                'key' => AWS_SES_ACCESSKEY,
                'secret' => smtpPassword,
            ),
            'region' => AWS_SES_DEFAULT_REGION
        ));
        $msg = array();
        $msg['Source'] = AWS_SES_AUTH_SENDER;
        //ToAddresses must be an array
        $msg['Destination']['ToAddresses'][] = $to;

        $msg['Message']['Subject']['Data'] = "Text only subject";
        $msg['Message']['Subject']['Charset'] = "UTF-8";

        $msg['Message']['Body']['Text']['Data'] = $data;
        $msg['Message']['Body']['Text']['Charset'] = "UTF-8";
        $msg['Message']['Body']['Html']['Data'] = $data;
        $msg['Message']['Body']['Html']['Charset'] = "UTF-8";

        try {
            $result = $SESclient->sendEmail($msg);
            $msg_id = $result->get('MessageId');
        } catch (Exception $e) {
            //An error happened and the email did not get sent
            echo($e->getMessage());
        }
    }

    public function send_mail($to, $data) {
        require_once('class.phpmailer.php');
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "tls";
        $mail->Host = "email-smtp.us-east-1.amazonaws.com";
        $mail->Username = AWS_SES_ACCESSKEY;
        $mail->Password = AWS_SES_PASSWORDKEY;
        //
        $message = '
			<html>
			<head>
			<title>ES Html Report</title>
			</head>
			<body>
			<table>
			<tr>
			<th>Project Name</th>
			<th>TODo</th>
			<th>Priority</th>
			<th>Due on</th>
			<th>Assignee</th>
			<th>Created</th>
			<th>Updated</th>
			<th>Completed</th>
			<th>Assignee Status</th>
			<th>Status</th>
			</tr>
			
			</table>
			</body>
			</html>
			';
        $mail->setFrom(AWS_SES_AUTH_SENDER, 'First Last');

        $mail->addAddress($to, 'John Doe');

        $body = preg_replace('/\[\]/', '', $message);
        $mail->IsHTML(true);
        $mail->Body = $body;
        var_dump($body);

        $mail->AltBody = 'This is a plain-text message body';

        //send the message, check for errors
        if (!$mail->send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
            echo "Message sent!";
        }
    }

}

class cgoAWSSNS {

    private $AWS_ACCESS_KEY_ID;
    private $AWS_SECRET_ACCESS_KEY;
    private $SNSclient;

    public function __construct() {

        $this->SNSclient = \Aws\Sns\SnsClient::factory(array(
                    'version' => '2010-03-31',
                    'credentials' => array(
                        'key' => AWS_SNS_ACCESSKEY,
                        'secret' => AWS_SNS_PASSWORDKEY,
                    ),
                    'region' => AWS_SNS_DEFAULT_REGION
        ));
    }

    public function createEndPoint($token, $custdata) {
        $result = $this->SNSclient->createPlatformEndpoint(array(
            'PlatformApplicationArn' => AWS_SNS_APPLICATION_ARN,
            'Token' => $token,
            'CustomUserData' => $custdata
        ));
        return $result->get('EndpointArn');
    }

    public function deleteEndPoint($arn) {
        $result = $this->SNSclient->deleteEndpoint(array(
            'EndpointArn' => $arn,
        ));
        return $result;
    }

    public function publishToEndPoint($arn, $message, $ttl) {
        $result = $this->SNSclient->publish(array('Message' => $message,
            'TargetArn' => $arn,
            'MessageAttributes' => array(
                'AWS.SNS.MOBILE.GCM.TTL' => array(
                    'DataType' => 'Number',
                    'StringValue' => $ttl
                )
            )
        ));
        return $result->get('MessageId');
    }

    public function getEndPointDetails($arn, $attrib) {
        $result = $this->SNSclient->getEndpointAttributes(array('EndpointArn' => $arn));
        file_put_contents('php://stderr', print_r($result, TRUE));
        $resultAttrib = $result->get('Attributes');
        return $resultAttrib[$attrib];
    }

}

class cgoGeoUtilities {

    function cgoGeoUtilities() {
        
    }

    public function getDegreeMatrix($mylon, $mylat, $dist) {
        $kmtomile = $dist * 0.623;
        $lon1 = $mylon - $kmtomile / abs(cos(deg2rad($mylat)) * 69);
        $lon2 = $mylon + $kmtomile / abs(cos(deg2rad($mylat)) * 69);
        $lat1 = $mylat - ($kmtomile / 69);
        $lat2 = $mylat + ($kmtomile / 69);
        return array("kmtomile" => $kmtomile, "lon1" => $lon1, "lon2" => $lon2, "lat1" => $lat1, "lat2" => $lat2);
    }

    public function GetDrivingDistance($lat1, $lat2, $long1, $long2) {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?key=" . GMAP_DIST_API_KEY . "&origins=" . $lat1 . "," . $long1 . "&destinations=" . $lat2 . "," . $long2;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response, true);
        //file_put_contents('php://stderr', print_r($lat1 . "," . $lat2 . "," . $long1 . "," . $long2, TRUE));
        //file_put_contents('php://stderr', print_r($response_a, TRUE));
        $dist = $response_a['rows'][0]['elements'][0]['distance']['value'];
        if ((float) $dist == 0) {
            file_put_contents('php://stderr', print_r("ERRORRRRRRR in DISTCANCE " . GMAP_DIST_API_KEY, TRUE));
            file_put_contents('php://stderr', print_r($response_a, TRUE));
        }
        $db = new \cgoSqlDB();
        //$db->query("INSERT INTO  googleapiscount(dt,typ,useby,`count`) VALUES (?,?,?,?)  ON DUPLICATE KEY UPDATE `count`=`count`+1",array('ssss', date('Ymd'),'distancematrix','backend-lib.php-GetDrivingDistance',1));
        return round($dist / 1000, 2);
    }

    public function getNearestAerialLocations($mylon, $mylat, $dist, $onlyactive = false) {
        global $db;
        $degMat = $this->getDegreeMatrix($mylon, $mylat, $dist);
        $kmtomile = $degMat['kmtomile'];
        $lon1 = $degMat['lon1'];
        $lon2 = $degMat['lon2'];
        $lat1 = $degMat['lat1'];
        $lat2 = $degMat['lat2'];

        $qry = "SELECT   branch_location.*,
			(3956 * 2 * ASIN(SQRT( POWER(SIN((ABS($mylat) - ABS(brlo_Lati))
			* PI()/180 / 2), 2) +
			COS(ABS($mylat) * PI()/180) * COS(ABS(brlo_Lati) * PI()/180) *
			POWER(SIN(($mylon -brlo_Long) * PI()/180 / 2), 2) )))/0.623 AS
			distance FROM   branch_location
			WHERE brlo_Long BETWEEN $lon1 AND $lon2
			AND brlo_Lati BETWEEN $lat1 AND $lat2 " . ($onlyactive ?
                        " AND brlo_Active = 1 " : "") .
                " ORDER BY Distance ;";
        $result = $db->getMulipleData($qry, true);
        return $result;
    }

    public function getNearestAerialBranches($mylon, $mylat, $dist) {
        global $db;
        $degMat = $this->getDegreeMatrix($mylon, $mylat, $dist);
        $kmtomile = $degMat['kmtomile'];
        $lon1 = $degMat['lon1'];
        $lon2 = $degMat['lon2'];
        $lat1 = $degMat['lat1'];
        $lat2 = $degMat['lat2'];
        $qry = "SELECT   finascop_branch.*,
			(3956 * 2 * ASIN(SQRT( POWER(SIN((ABS($mylat) - ABS(br_Lat))
			* PI()/180 / 2), 2) +
			COS(ABS($mylat) * PI()/180) * COS(ABS(br_Lat) * PI()/180) *
			POWER(SIN(($mylon -br_Lng) * PI()/180 / 2), 2) )))/0.623 AS
			distance FROM   finascop_branch
			WHERE br_Lng BETWEEN $lon1 AND $lon2
			AND br_Lat BETWEEN $lat1 AND $lat2
			AND br_status = 'Active'
			ORDER BY distance ;";
        $result = $db->getMulipleData($qry, true);
        return $result;
    }

    public function getNearestAerialRetailers($mylon, $mylat, $dist) {
        global $db;
        $degMat = $this->getDegreeMatrix($mylon, $mylat, $dist);
        $kmtomile = $degMat['kmtomile'];
        $lon1 = $degMat['lon1'];
        $lon2 = $degMat['lon2'];
        $lat1 = $degMat['lat1'];
        $lat2 = $degMat['lat2'];
        $qry = "SELECT   finascop_branch.*,
			(3956 * 2 * ASIN(SQRT( POWER(SIN((ABS($mylat) - ABS(br_Lat))
			* PI()/180 / 2), 2) +
			COS(ABS($mylat) * PI()/180) * COS(ABS(br_Lat) * PI()/180) *
			POWER(SIN(($mylon -br_Lng) * PI()/180 / 2), 2) )))/0.623 AS
			distance FROM   finascop_branch
			WHERE br_Lng BETWEEN $lon1 AND $lon2
			AND br_Lat BETWEEN $lat1 AND $lat2
			AND br_status = 'Active' AND br_PyramidLevel = 4
			ORDER BY distance ;";
        $result = $db->getMulipleData($qry, true);
        return $result;
    }

    public function getDrivingRoute($Srclat, $Dstlat, $Srclong, $Dstlong, $waypoints) {
        $url = "https://maps.googleapis.com/maps/api/directions/json?key=" . GMAP_DIST_API_KEY . "&origin=" . $Srclat . "," . $Srclong . "&destination=" . $Dstlat . "," . $Dstlong . "&waypoints=" . $waypoints;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        //file_put_contents('php://stderr', print_r($url, TRUE));
        //file_put_contents('php://stderr', print_r($response, TRUE));
        $response_a = json_decode($response, true);
        $order = $response_a['routes'][0]['waypoint_order'];
        $db = new \cgoSqlDB();
        //$db->query("INSERT INTO  googleapiscount(dt,typ,useby,`count`) VALUES (?,?,?,?)  ON DUPLICATE KEY UPDATE `count`=`count`+1",array('ssss', date('Ymd'),'directions','backend-lib.php-getDrivingRoute',1));
        return $order;
    }

    public function getSnapToRoad($geocoords) {
        $url = "https://roads.googleapis.com/v1/snapToRoads?&interpolate=true&key=" . GMAP_DIST_API_KEY . "&path=" . $geocoords;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response, true);
        //file_put_contents('php://stderr', print_r($geocoords, TRUE));
        $order = $response_a['snappedPoints'];
        //file_put_contents('php://stderr', print_r($response_a, TRUE));
        $db = new \cgoSqlDB();
        //$db->query("INSERT INTO " . DB_PREFIX . "1.googleapiscount(dt,typ,useby,`count`) VALUES (?,?,?,?)  ON DUPLICATE KEY UPDATE `count`=`count`+1",array('ssss', date('Ymd'),'snapToRoads','backend-lib.php-getSnapToRoad',1));
        return $order;
    }

}

class GenerateReferralCode {

    /**
     * Static function to generate a unique refferal code
     */
    public static function generate() {
        return (new static)->generateCode();
    }

    /**
     * Generate a unique refferal code.
     *
     * @return string
     */
    public function generateCode() {
        return $this->getMonthCode() .
                $this->getDate() .
                'RF' . $this->addPaddingZeros(
                        $this->getLastNumber()
        );
    }

    /**
     * Get the last inserted number from db.
     *
     * @return int
     */
    public function getLastNumber() {
        $db = new \SqlDB(DSN);
        $latest = $db->getItemFromDB("SELECT cust_ref_code FROM retaline_customer ORDER BY cust_id DESC LIMIT 1");
        return $latest ?
                ((int) substr($latest, 5)) + 1 :
                1;
    }

    /**
     * Add padding zeros.
     *
     * @param string $refCode
     * @return string
     */
    public function addPaddingZeros($refCode) {
        return str_pad($refCode, 5, '0', STR_PAD_LEFT);
    }

    public function getDate() {
        return date('d');
    }

    public function getMonthCode() {
        $monthCodes = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
            'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',
            'U', 'V', 'W', 'X', 'Y', 'Z'
        ];
        $month = date('m');
        return $monthCodes[$month - 1];
    }

}

class cgoS3FileHandler {

    function getFileFromS3($S3Object, $S3Bucket, $TmpPath = '', $tries = 3) {
        
    }

    function putFileToS3($S3Object, $S3Bucket, $FilePath, $file_name,$fileType = 'jpg',$mediaType = 'image') {
		//function putFileToS3($S3Object, $S3Bucket, $FilePath, $storage = 'STANDARD', $tries = 2, $file_name) {


        $client = \Aws\S3\S3Client::factory(array(
                    'version' => 'latest',
                    'credentials' => array(
                        'key' => AWSS3ASSETUPLOADACCESSID,
                        'secret' => AWSS3ASSETUPLOADSECRETKEY,
                    ),
                    'region' => AWSS3ASSETUPLOADREGION
        ));

        // Detect MIME type automatically
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $FilePath);
        finfo_close($finfo);

        $result = $client->putObject(array(
            'Bucket' => $S3Bucket,
            'Key' => $file_name,
            'SourceFile' => $FilePath,
            'ACL' => 'public-read',
            'ContentType' => $mimeType, 
            'Metadata' => array(
                'filepath' => $S3Object,
                'bucket' => AWSBUCKETNAME,
                'fileType' => $fileType,
                'mediaType' => $mediaType
            )
        ));

        // We can poll the object until it is accessible
        //$client->waitUntil('ObjectExists', array(
        //    'Bucket' => $S3Bucket,
        //    'Key'    => $S3Object
        //));
        return true;
    }

    function isS3FileExist($S3Bucket, $S3Object, $returnInfo = false) {
        require_once('s3.php');
        $S3Obj = new S3(AWSACCESSKEY, AWSPASSWORDKEY);
        return $S3Obj->getObjectInfo($S3Bucket, $S3Object, $returnInfo);
    }

}

class supportSqlDB {

    private $dbConnection;
    public $quiet;
    private $mc;
    private $ttl;
    private $dbf;
    private $tz;
    public $mcEnabled = false;
    private $relations;
    public $private;
    private $doNotProcessQry;
    private $lastResult;
    public $default_db;

    function begintransaction() {
        try {
            //mysqli_autocommit($this->dbConnection, FALSE)
            mysqli_query($this->dbConnection, "START TRANSACTION");
        } catch (Exception $e) {
            print_r($e);
            exit;
        }
    }

    function committransaction() {
        try {
            mysqli_query($this->dbConnection, "COMMIT");
        } catch (Exception $e) {
            print_r($e);
            exit;
        }
    }

    function __construct() {
        $pdsn = parse_url(SUPPORTDSN);
        if ($pdsn['scheme'] !== 'mysql')
            die("System is designed for MySQL only.. Please Correct the dsn");
        $mysql_db = preg_replace("@^\/@", '', $pdsn['path']);

        $this->dbConnection = mysqli_connect($pdsn['host'], $pdsn['user'], $pdsn['pass'], $mysql_db, ini_get("mysqli.default_port"))
                or die("Could not connect!<br>" . mysqli_error());

        $this->dbf = $mysql_db;
        $this->default_db = $mysql_db;

        $this->quiet = false;
        $this->relations = false;
        $this->ttl = 2 * 60 * 60;
        if ($this->mcEnabled == true) {
            $this->connectMC();
        }
        $this->private = '';
        $this->lastResult = false;
        $this->doNotProcessQry = false;
    }

    private function cactchExeption() {
        echo $dsn;
        print_r(debug_backtrace());
        exit();
    }

    public function setRelations($r) {
        $this->relations = $r;
    }

    private function connectMC() {
        $this->mc = new Memcache ( );
        if (strpos($_SERVER['SERVER_ADDR'], '192.168.0.') !== false) {
            $this->mc->addserver('192.168.0.15', 11211);
        } else {
            for ($i = 34; $i < 38; $i++) {
                $this->mc->addserver('192.168.33.' . $i, 11211);
            }
        }
    }

    function error($query, $errno, $error) {
        if (!$this->quiet) {
            echo $error . "<br>" . $query;
            if (defined('TRACE_DEBUG'))
                print_r(debug_backtrace());
        }
        exit;
        //return false;
    }

    function refValues($arr) {
        if (strnatcmp(phpversion(), '5.3') >= 0) { //Reference is required for PHP 5.3+ 
            $refs = array();
            foreach ($arr as $key => $value)
                $refs[$key] = &$arr[$key];
            return $refs;
        }
        return $arr;
    }

    /**
     * Sends a query to the database
     *
     * @param sqlquery $query
     * @return result-resource
     */
    function query($query, $params = array()) {
        if ($this->lastResult !== false) {
            $this->clearResult($this->lastResult);
        }
        if ($this->mcEnabled == true)
            $this->checkQuery($query);
        try {
            $stmt = $this->dbConnection->prepare($query);
            if ($stmt === false) {
                echo("SQL prepare error " . $this->dbConnection->error . " Query " . $query );
                file_put_contents('php://stderr', "SQL prepare error " . $this->dbConnection->error . " Query " . $query . "\n");
                exit();
            }

            if (count($params) > 1) {
                $binparam = call_user_func_array(array($stmt, "bind_param"), $this->refValues($params));
                if ($binparam === false) {
                    echo("SQL bind_param error " . $stmt->error);
                    file_put_contents('php://stderr', "SQL bind_param error " . $stmt->error . " Query " . $query . "\n");
                    exit();
                }
            }

            if ($stmt->execute() === false) {
                echo("SQL execute error " . $stmt->error);
                file_put_contents('php://stderr', "SQL execute error " . $stmt->error . " Query " . $query . "\n");
                exit();
            }

            $this->lastResult = $stmt->get_result();
            $stmt->close();
            //$this->lastResult = mysqli_query($this->dbconnection, $query) or $this->error($query, mysqli_errno($this->dbconnection), mysqli_error($this->dbconnection));
            return $this->lastResult;
        } catch (Exception $e) {
            file_put_contents('php://stderr', print_r($e, TRUE));
            file_put_contents('php://stderr', print_r($query, TRUE));
            print_r($query);
            print_r($e);
            exit;
        }
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
    function perform($table, $action, $data, $condition, $paramtypes) {
        reset($data);
        $arrDataCount = count($data);
        $arrConditionCount = 0;
        //If insert
        if ($action == 'insert') {
            $query = 'INSERT INTO ' . $table . ' (' . join(', ', array_keys($data)) . ') VALUES (';

            foreach ($data as &$value) {
                if (strpos($value, 'func:') !== false) {
                    $query .= '?' . ', ';
                    $value = substr($value, 5);
                } else {
                    switch ((string) $value) {
                        case 'now()' :
                            $query .= '?, ';
                            $value = 'NOW()';
                            break;
                        case 'null' :
                            $query .= '?, ';
                            $value = 'NULL';
                            break;
                        default :
                            $query .= '?, ';
                            break;
                    }
                }
            }
            $query = substr($query, 0, -2) . ')';
        } elseif ($action == 'update') {
            //If update
            $query = 'UPDATE ' . $table . ' SET ';
            //Get update columns and it values	
            foreach ($data as $columns => &$value) {
                if (strpos($value, 'func:') !== false) {
                    $query .= $columns . '=?, ';
                    $value = substr($value, 5);
                } else {
                    switch ((string) $value) {
                        case 'now()' :
                            $query .= $columns . ' = ?, ';
                            $value = 'NOW()';
                            break;
                        case 'null' :
                            $query .= $columns . ' = ?, ';
                            $value = 'NULL';
                            break;
                        case '++' :
                            $query .= $columns . ' = ' . $columns . ' + 1, ';
                            $arrDataCount = $arrDataCount - 1;
                            break;
                        default :
                            $query .= $columns . ' = ?, ';
                            break;
                    }
                }
            }
            $query = substr($query, 0, -2);
            //Get update condition and it values	
            $parameters = '';
            if (count($condition) > 0 && !empty($condition)) {
                $arrDataCount = $arrDataCount + count($condition);
                foreach ($condition as $columns => &$value) {
                    if (strpos($value, 'func:') !== false) {
                        $parameters .= $columns . '=?, ';
                        $value = substr($value, 5);
                    } else {
                        switch ((string) $value) {
                            case 'now()' :
                                $parameters .= $columns . ' = ?, ';
                                $value = 'NOW()';
                                break;
                            case 'null' :
                                $parameters .= $columns . ' = ?, ';
                                $value = 'NULL';
                                break;
                            case '++' :
                                $parameters .= $columns . ' = ' . $columns . ' + 1, ';
                                $arrDataCount = $arrDataCount - 1;
                                break;
                            default :
                                $parameters .= $columns . ' = ?, ';
                                break;
                        }
                    }
                }
                $query .= ' WHERE ' . substr($parameters, 0, -2);
            }
        }
        if (strlen($paramtypes) != $arrDataCount) {
            throw new \Exception('Parameter count and each parameter\'s type\'s count does not match, check parametertype variable');
        }
        $data = array_values($data);
        $condition = array_values($condition);
        array_unshift($data, $paramtypes);
        $data = array_merge($data, $condition);

        $res = $this->query($query, $data);

        if ($this->mcEnabled == true && $this->affected_rows() > 0)
            $this->checkAndInvalidate($table);

        return $res;
    }

    function fetch_array($result) {
        return mysqli_fetch_array($result, MYSQLI_ASSOC);
    }

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
        return mysqli_insert_id($this->dbConnection);
    }

    function affected_rows() {
        return mysqli_affected_rows($this->dbConnection);
    }

    function free_result($result) {
        mysqli_free_result($result);
    }

    function fetch_fields($result) {
        return mysqli_fetch_field($result);
    }

    function output($string) {
        return htmlspecialchars($string);
    }

    function input($string) {
        return addslashes($string);
    }

    function next_result() {
        return mysqli_next_result($this->dbConnection);
    }

    function store_result() {
        return mysqli_store_result($this->dbConnection);
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

    /* private function checkQuery($query) {
      //first tokenize to identify the query type
      if ($this->mcEnabled == true && $this->doNotProcessQry === false) {
      list($type, $operation) = explode(' ', trim($query));
      if (strtolower($type) == 'delete') {
      if (preg_match('/\bfrom\b\s*(\w+)\s*(.*)/i', $query, $t)) {
      $this->checkAndInvalidate($t[1]);
      }
      return;
      }
      if (strtolower($type) == 'truncate') {
      $table = str_ireplace('table ', '', trim($operation));
      $this->checkAndInvalidate($table);
      return;
      }
      if (strtolower($type) == 'insert') {
      if (preg_match("@into (.+?) @i", $operation, $t)) {
      $this->checkAndInvalidate($t[1]);
      }
      return;
      }
      if (strtolower($type) == 'update') {
      if (preg_match('/\bUPDATE\b\s*(\w+)\s/i', $query, $t)) {
      $this->checkAndInvalidate($t[1]);
      }
      }
      if (strtolower($type) == 'set') {
      if (stripos($query, 'session.time_zone') !== false) {
      $this->tz = trim(substr($query, strpos($query, '=')), "'");
      }
      }
      }
      } */

    private function updateValues($key, $data) {
        if ($this->mcEnabled == true) {
            $done = $this->mc->add($key, $data, MEMCACHE_COMPRESSED, $this->ttl);
            if ($done == false) {
                $this->mc->replace($key, $data, MEMCACHE_COMPRESSED, $this->ttl);
            }
        }
    }

    private function checkAndInvalidate($table) {
        if ($this->mcEnabled == true) {
            $dbkey = md5($_SERVER ['HTTP_HOST'] . $this->dbf . '-' . trim($table));
            $this->updateValues($dbkey, microtime(1));
            $this->checkRelations($table);
        }
    }

    private function checkRelations($table) {
        if ($this->mcEnabled == true) {
            if ($this->relations == false or ! isset($this->relations['Trigger'][$table])) {
                return;
            }
            foreach ($this->relations['Trigger'][$table] as $cTable) {
                $this->checkAndInvalidate($cTable);
            }
        }
    }

    private function tzTag($query) {
        if ($this->mcEnabled == true) {
            if ($this->relations == false or ! isset($this->relations['TZ'])) {
                return $query;
            }
            $cTest = '@( ' . join(' | ', $this->relations['TZ']) . ' )@';
            if (preg_match($cTest, str_replace(',', ' , ', $query))) {
                return $this->tz . ':' . $query;
            }
        }
        return $query;
    }

    private function isCached($query, $fa = 'X') {

        if ($this->mcEnabled == false) {
            return false;
        }
        if (!stripos($query, ' from ')) {
            return false;
        }
        $key = md5($this->dbf . $this->tzTag($query) . '-' . intval($fa));
        $value = $this->mc->get($key);
        if (!$value) {
            return false;
        }
        return $this->validateCached($value, $query);
    }

    private function isCacheableQuery($query) {
        if ($this->mcEnabled == false) {
            return false;
        }
        if (!stripos($query, ' from ')) {
            return false;
        }
        $rv = true;
        $cc = array(' call', ' rand');
        return $rv && (!preg_match('@' . join('|', $cc) . '@', $query));
    }

    private function setCache($data, $query, $fa = 'X') {
        if (!$this->isCacheableQuery($query))
            return;
        $key = md5($this->dbf . $this->tzTag($query) . '-' . intval($fa));
        $this->updateValues($key, array('ts' => microtime(1), 'data' => $data));
    }

    private function validateCached($value, $query) {
        $rv = $value['data'];
        $tableReg = $this->getTables();
        $query = str_replace(array("\n", ',', ',  '), array(" ", ', ', ', '), $query) . ' ';
        preg_match_all($tableReg, $query, $m);
        foreach ($m[1] as $table) {
            $k = md5($_SERVER ['HTTP_HOST'] . $this->dbf . '-' . trim($table));
            $g = $this->mc->get($k);
            if (!$g or $g > $value['ts']) {
                $rv = false;
                break;
            }
        }
        return $rv;
    }

    private function getTables() {
        $key = $this->dbf . '-tableRegEx';
        if (($tableRegex = $this->mc->get($key)) == false) {
            $tables = sprintf("select table_name from information_schema.tables where table_schema = '%s'", $this->dbf);
            $this->doNotProcessQry = true;
            $rs = $this->query($tables, $params);
            $this->doNotProcessQry = false;
            $tableList = array();
            while (($rd = $this->fetch_row($rs)) !== NULL) {
                $tableList[] = $rd[0];
            }
            $tableRegex = '@( ' . join(' | ', $tableList) . ' )@isU';
            $this->updateValues($key, $tableRegex);
        }
        return $tableRegex;
    }

    /*
      @Functions added from functions in admin folder by niju
     */

    function getMulipleData($query, $params, $fetch_array = false) {
        if (($retval = $this->isCached($query, $fetch_array)) == false) {
            try {
                $rs = $this->query($query, $params);
                if ($this->num_rows($rs) == 0)
                    return false;
                $retval = array();
                if ($fetch_array) {
                    while ($row = $this->fetch_array($rs)) {
                        if (count($row) > 1)
                            $retval [] = $row;
                        else
                            $retval [] = $row [0];
                    }
                } else {
                    while ($row = $this->fetch_row($rs)) {
                        if (count($row) > 1) {
                            $retval [] = $row;
                        } else {
                            $retval [] = $row [0];
                        }
                    }
                }
                if ($this->mcEnabled == true)
                    $this->setCache($retval, $query, $fetch_array);
                $this->clearResult($rs);
            } catch (Exception $e) {
                file_put_contents('php://stderr', print_r($e, TRUE));
                file_put_contents('php://stderr', print_r($query, TRUE));
                throw new \Exception($e);
            }
        }
        return $retval;
    }

    function getItemFromDB($query, $params) {
        if (($retval = $this->isCached($query)) == false) {
            $rs = $this->query($query, $params);
            if ($this->num_rows($rs) == 0)
                return false;
            $retval = $this->fetch_row($rs);
            if ($this->mcEnabled == true)
                $this->setCache($retval, $query);
            $this->clearResult($rs);
        }
        return $retval [0];
    }

    function getFromDB($query, $params, $fetchArray = false) {
        if (($retval = $this->isCached($query, $fetchArray)) == false) {
            $rs = $this->query($query, $params);
            if ($this->num_rows($rs) == 0)
                return false;
            if ($fetchArray) {
                $retval = $this->fetch_array($rs);
            } else {
                $retval = $this->fetch_row($rs);
            }
            if ($this->mcEnabled == true)
                $this->setCache($retval, $query, $fetchArray);
            $this->clearResult($rs);
        }
        return $retval;
    }

    function getArrayFromDB($query, $params) {
        if (($retval = $this->isCached($query)) == false) {
            $rs = $this->query($query, $params);
            if ($this->num_rows($rs) == 0)
                return false;
            else {
                $retval = array();
                while ($row = $this->fetch_row($rs)) {
                    $retval [$row [0]] = $row [1];
                }
            }
            if ($this->mcEnabled == true)
                $this->setCache($retval, $query);
            $this->clearResult($rs);
        }
        return $retval;
    }

    /**
     * Function to clear the resultset to avoid the commands out of sync error
     *
     * @param resource $result
     * @author Niju N B
     */
    function clearResult($result) {
        if (is_resource($result)) {
            $this->free_result($result);
        }
        while ($this->next_result()) {
            $result = $this->store_result();
            if ($result) {
                $this->free_result($result);
            }
        }
        $this->lastResult = false;
    }

    function select_db($db) {

        $rs = mysqli_select_db($this->dbConnection, $db); // or die("Could not select database");
        if ($rs == false && php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
            print_r(debug_backtrace());
        }
    }

}





