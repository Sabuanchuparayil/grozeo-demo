<?php
require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/includes/config.php');
require_once(INCLUDE_PATH . '/config.php');
require_once(EXTERNAL_LIBRARY_PATH);
require_once(INCLUDE_PATH . '/CloudFcmNotification.php');
require_once(QUGEO_API_ROOT . '/Models/QugeoScheduler.php');
require_once(QUGEO_API_ROOT . '/Models/QugeoOrderHandler.php');
require_once(QUGEO_API_ROOT . '/Models/QugeoOrderPoller.php');
require_once(QUGEO_API_ROOT . '/Models/Utils.php');

require_once(INCLUDE_PATH . "/finascop_common_functions.php");

function display_pie_chart_data($qry, $msg, $title) {
    global $db;
    $data = $db->getMultipleData($qry);
    if (is_array($data)) {
        $comma = '';
        $color = array('Completed' => '"#408000"', 'Pending' => '"#DC3812"');
        foreach ($data as $key => $val) {
            if ($val[1] == 0) {
                $val[1] = '';
            }
            $values .= $comma . $val[1];
            $colors .= $comma . $color[$val[0]];
            if ($val[1] > 0) {
                $displays .= $comma . '"' . $val[0] . ' - ' . $val[1] . ' (%%.%%)"';
                $delmsg = '"' . $val[0] . ' - ' . $val[1] . '"';
            }
            $comma = ',';
        }
        if (!empty($displays)) {
            if (strlen($values) > 1) {
                $s = substr($values, 0, 1);
                if ($s == ',') {
                    $arr1 = array('%title%', '%message%');
                    $arr2 = array($title, $delmsg);
                    echo str_replace($arr1, $arr2, file_get_contents(dirname(__FILE__) . '/js/delay.html'));
                } else {
                    $arr1 = array('%title%', '%values%', '%displays%', '%colors%');
                    $arr2 = array($title, $values, $displays, $colors);
                    echo str_replace($arr1, $arr2, file_get_contents(dirname(__FILE__) . '/js/piechart.html'));
                }
            }
        } else {
            $arr1 = array('%title%', '%message%');
            $arr2 = array($title, $msg);
            echo str_replace($arr1, $arr2, file_get_contents(dirname(__FILE__) . '/js/nograph.html'));
        }
    }
}
/*
 * {
  "type": "service_account",
  "project_id": "apt-footing-286705",
  "private_key_id": "c99d8ee25af748a3831ce0fe00b767c1e8b8c723",
  "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQCo1kkSKpsWWKjQ\n3LgK1XMPNh0Iwp+z5iZz5dDWIwFrv44l0LoznhxBnTEKrKt74xh26jB+NRkxu6WP\nV/D0AGuKF27ePheUQRnn6u9sj/xPsCA0bN1/w+C/3wV0sIUoQ54THMFaXt/0VlDt\n7cq5Y2U1s4wqgYb9c5exbVvZmcKgGRqtwOOgZ2vg2qgdUhTW8buQwHJBPbEBdzTD\nYyIjXS22v73ljN843T/GBoCuerEia24XD/+kuuimRVDdKwulV45nZ90TEgK05yMQ\nQULCLSMkvBlRFVaORsWK4UhfMQl/jmxe76ABTSQYqQttYrFFj3vnVECoN3J7pKnQ\ndqCzNqKVAgMBAAECggEAGRySDrEmkvrJ1RLXDslzjRIj3F8yKKw1EOzgFFFtjyyV\nBFvYrf+4vSuXlTpcYRdEVr97SfI01phNxhWy4V/EnuvgUg7TK/pI0502UiPSR7nu\n+E0u3qdwIKmXoqT0kSoJc1dGDN5qmelSrSi+i7dUYNndIZ9VSBIuwDV/QUykzXHg\nlS5W3YWOsVVgKiW4mWLBzqdT+c/mrboqqLDSrHRnA8q/ZdXxFDYnYR7SJUJVJ+yl\nOYmE46kopWY14i+HMdu9NQ5N7UYdFRVfV8YSi4qIIFWTlXLfQN4igvZaoLxkEcN6\n253Z4rj4I35I+shZCzDVWzBJPyWP+wUnMTJ1BhRhGwKBgQDQ/Qm4BQ6w/SQlt7Aq\n36KgbC/aHh6pPabo0u1Iu1KvJtnYBfFIzqtvVvoVvsb5h65IqZ7l5H9eiGnFJIq1\nXbQ5HlMGY31c52Ed5YERhZYw9oQL71NKT1xDwPl7P+HTPaLYVEYE/mE7A3CX8orq\n0PwT8Bxspu+daLYIRoi2ZTlziwKBgQDO0Q+Ese9X8itDIORGQH66quJVvYMop/c1\nXq/vCjofludFkv0/0jrcBZIJoRjVIW9CSTE3qO+XcZM8ZWaWZ384N7HkhkSFyGqh\nMUeEl8lQd//7jGxzcMQ0ko82mxmqoByYYrsBOcNFm+MEjkGAh1twKMJ/zp++rrVf\nLP5I1n6GXwKBgQDF0I1vepICY0ngzbLZkh7r/Bt7OQFKrombXXOuYWkNUxfmCxHt\nGXpaBBjplk/eh1gnfS5jalpJT5PWQBVvQIfDfPmXVXqnAngBhWga/rhAFka8yUas\nUtwBYdqDl98YRQIsA/DW7zV3V3UuzixBwZipAqDVE1pBQ4jx4lmrU7sCqwKBgQCZ\nu/XZIZtUuINIJw8I2bBaeeQ8796rPhAY8AW/ns6N5NKNeTIfWtq96rYfykx3QZ8+\nmsGnKkDPRnG4F3gmnVCILX5i7RvKhqwcnlEXUu0mj80M49lKBq0Sl081vB1cJCCd\ndeakhpeNCs+59zThobxqpyHNd35vc9cGpJ9w7WCn6QKBgQCvcSscq8u77KSe8u2b\nsKMikHX70fEDYXegg0I56Nv8GFpvJzdvba28K+DOWTbc7rZpG/W8MLhpveL3OJq6\nNwdjI1VyvCcYbA2ncdLjM2Xd+Sk/fAO1IG2DzSuOq1y/T97Otm6hXmV669befV18\nO2dIDMw2vPs0d0vezA8ylwtrFw==\n-----END PRIVATE KEY-----\n",
  "client_email": "dhanya-velosit@apt-footing-286705.iam.gserviceaccount.com",
  "client_id": "102969462092613169713",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/dhanya-velosit%40apt-footing-286705.iam.gserviceaccount.com"
  }
  function generateToken(event, context, callback) {
  var {google} = require("googleapis");
  console.log('generate token from google api');
  var googleServiceAccountKey = {
  "type": "service_account",
  "project_id": "asianet-in",
  "private_key_id": "0d5ae9652888e337fa9fa752eb3574b4dc4584d2",
  "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCoE2/Vo6ZY5fXh\npum4VG5x+iCjVlFyhZtrZG3/jkrHIUqalGZkbm1uEldZgemJMMnIDhWOOIq5giXo\nBBZRGWPowrmbFMJbFx7Hj/eq84Xg4XJgcIFJEMlWZ3CL2AY3EB+h/RoJFznBZPy1\nCpMoj1jWV16298O9rJMIoSFjR4O3aeEiE4rnVt+aqyiQ4W5SdM/y2scoH+lJIUpZ\nbVuxyjwjWfOHRQsS3sresvgHDAq3MVxyk3aBHNYzinyFeV3osFhtT6A/R4SOSond\n6heYs//0d7piu8FRyymZ500tkGyBi1YdQPBjF+kdeEw8IeBy1Vxvq3LfvDx90jsC\nlPYV6hD5AgMBAAECggEAGSBAu3YZv9fu3FM0xODgWusa1ngouF9XVuReWlImBSNJ\nsRamwPROKSKWStP0OtVfkOfkGo0C1g0qCAeWY3zRdgIoN5IQpQftisVPr890b+Qc\nEmU3OppHEwLnLQy4DyK6q993tSyy7mGfspvYWHunevF4QA+FhFUobOWTpXETNIei\nQlAfcT01TO8yCd9bTkYk38KkSSm6SMeKiV1hi6atjPZoO0iVvJkUcAF7VlddNeO0\nFVe2kqKCJ6Hc2UY0e52n5c3kP0LiiadpClmCRbphacxQaISs6le77uNXsrCTb8Hr\nO9rcdSvtBbS3q/GOArFbOoX3y+mJrpip5lytsTbJNQKBgQDQvlDN1d2zBzRqF+sV\nVXphjsOLayMlbAKrWPdTPexUv0ShYww5dEI+9OrHCrlcxkngIEVzg6xwXRT6Ze3+\nphV4+Fb5wb1Oq6DllZiZxG5MGljGQoQ9d4eJSAzw2oF9QeJvhceTXCcSOs9JUeE/\nie605JpujBx3swcIvwXvKSm2pQKBgQDOID6S8AX12ZpSCDpkNSA8tSvKLUDtDqIy\nJch34dkGgFZToeeKpyDJ1quQUXfXV/SwO8/zZP0/1ba/kgLUrCi81gQqbBTs+cuf\n/YvyerphovR7FYILorH9wbNpddl57HxlOqOWZOm9dtX/O6q8e7ifVBxDkgUSSHZh\nEXWWmis0xQKBgBENmzhVonj/u653KcNiak8SBLOdGw/xlP4+lGX+hxIdVhQBLXx8\nHPVbuNpt69rCcEKZIFNhjHLZh980+I53LwXk8+YPh9Gnf8uBvyfAvmoFNP9ta7RB\n0ZbLhhMfJrj+6urFeRp2ytJYb5rDz60LLa8lheBGHgVBYO+7+1YgfMHpAoGAHRIp\nIRofDRR6klU8vwNLH6TBn0sQnB5zO7Ved6HvtN4GztbHzCNUGYNgQQNsbn+mL/DP\nnFlC6mze0FfsdEgvmqpoff8uWFnGoTLmOPWcMccEyhM3eyKgDdTy083eNTboOHKz\nvbNPz/vhpJSquNiOKlJ3hgQjGPgFUh1fQzVNIK0CgYBrCVDbo9IaiLn+BNMnqTjh\nDlThlPSavjRJ1ps38iTxNioheUFmWLqjrG7Pl2cGTbhxWO2aGbGOuAPX4aNv2h9w\ng9/XeensQ7DSkmx88yp/VdpWtem8WeS9tX9vkaauGgkCOqvYHSJblpOw22m5dJeQ\niURW5ZkBQXFoPY9QJUB7fA==\n-----END PRIVATE KEY-----\n",
  "client_email": "dashboardviewer@asianet-in.iam.gserviceaccount.com",
  "client_id": "103358449921332152714",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/dashboardviewer%40asianet-in.iam.gserviceaccount.com"
  };

  const googleJWTClient = new google.auth.JWT(
  googleServiceAccountKey.client_email,
  null,
  googleServiceAccountKey.private_key,
  ['https://www.googleapis.com/auth/analytics.readonly'], null);

  googleJWTClient.authorize((error, access_token) => {

  if (error) {
  console.log(error);
  done(null, error, callback);
  }
  console.log(access_token);
  done(null, access_token, callback);
  // ... access_token ready to use to fetch data and return to client

  // even serve access_token back to client for use in `gapi.analytics.auth.authorize`

  });

  }
 */

switch ($op) {
    case 'schedulerGridStore':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'prlk_name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                    }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM process_lock {$search} AND prlk_isenabled = 1  AND status  = 1 ORDER BY {$sort} {$dir}";

        $listQuery = "SELECT prlk_name,prlk_status,prlk_updtime,prlk_isenabled,prlk_email,prlk_Description,prlk_interval,TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP,prlk_updtime))/60  as minuteDiff FROM process_lock {$search} AND prlk_isenabled = 1 AND status  = 1  ORDER BY {$sort} {$dir} ";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'changeStatus':
        $prlk_name = $_POST['prlk_name'];
        $prlk['prlk_updtime'] = date('Y-m-d H:i:s');
        $prlk['prlk_status'] = 0;
        $db->query('begin');
        $status = $db->perform('process_lock', $prlk, 'update', " prlk_name = '{$prlk_name}'");
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,msg:'Converted '}";
        } else {
            echo "{'success':false,'valid':false,'msg': 'Error While Converting.'}";
        }
        break;
    case 'chartCombination':
        ob_start();
        include('chartdetails.php');
        $resHtml = ob_get_contents();
        ob_end_clean();
        echo $resHtml;
        exit();
        break;
    case 'dlistCurrentStock':
        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'stit_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $search = ' WHERE 1=1 AND stit_status = 1 ';
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $searchitem .= " and ({$field[field]} LIKE '{$field['data']['value']}%') ";
                }
            }
        }
        $br_ID = $_SESSION['admin']->finascop_current_branch_id;
        $where = " AND branch_id =" . $br_ID;

        $br_PyramidLevel = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch WHERE br_ID = {$br_ID}");
        if ($br_PyramidLevel == 2) {
            $where .= " AND (directPurchase = 1  || stit_ParentItemId = 0) ";
            $rackStatus = " AND stiid_status = 1";
            $dispatchStatus = " AND stiid_status = 2";
            $receiveStatus = " AND stiid_status = -1";
            $deliverStatus = " AND stiid_status = -1";
        } else if ($br_PyramidLevel == 3) {
            $where .= " AND (directPurchase = 1  || stit_ParentItemId = 0)";
            $rackStatus = " AND stiid_status = 1";
            $dispatchStatus = " AND stiid_status = 2";
            $receiveStatus = " AND stiid_status = 3";
            $deliverStatus = " AND stiid_status = -1";
        } else if ($br_PyramidLevel == 4) {
            $where .= "  ";
            $rackStatus = " AND stiid_status = 4";
            $dispatchStatus = " AND stiid_status = -1";
            $receiveStatus = " AND stiid_status = 3";
            $deliverStatus = " AND stiid_status = 5";
        }

        $countQuery = "SELECT COUNT(1) from " . FINASCOP_DB . " finascop_stock_branch_inventory fsb "
            . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
            . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$where}{$searchitem}";
        $count = $db->getItemFromDB($countQuery);
        $listQuery = "SELECT fsb.stit_id as stit_id,br.br_Name as br_Name,stit_SKU,fsbg_id,purchasing_unit,stit_ParentItemId,"
            . "fsb.item_count as item_count,fsb.mrp as mrp,fsb.selling_price as selling_price,fsb.branch_id as branchId,"
            . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stitl1_optimumqty "
            . "WHEN br.br_stockLevel= 2 THEN fsi.stitl2_optimumqty "
            . "ELSE stitl3_optimumqty END AS optimumqty,"
            . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stit11_minimumqty "
            . "WHEN br.br_stockLevel= 2 THEN fsi.stit12_minimumqty "
            . "ELSE stit13_minimumqty END AS minimumqty,"
            . "CASE WHEN br.br_stockLevel= 1 THEN fsi.stit11_maximumqty "
            . "WHEN br.br_stockLevel= 2 THEN fsi.stit13_maximumqty "
            . "ELSE stit13_maximumqty END AS maximumqty,csb_package_type_name,cs_nos,cs_package_type_name,ds_nos,ds_package_type_name,cos_nos,cos_package_type_name,br_PyramidLevel,least_package_type_id "
            . "FROM finascop_stock_branch_inventory fsb "
            . "INNER JOIN finascop_stock_itemmaster fsi ON fsb.stit_id=fsi.stit_ID "
            . "INNER JOIN finascop_branch br ON br.br_ID=fsb.branch_id {$search}{$where}{$searchitem} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                //foreach ($datas as $data) {
                $datas[$i]['rack_count'] = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stiid_itemmasterid = {$datas[$i]['stit_id']} AND cpd_branch_id = {$datas[$i]['branchId']} AND fsbg_id = {$datas[$i]['fsbg_id']} {$rackStatus}");
                //$datas[$i]['dispatch_count'] = ($selBranchCpd == 1) ? ($db->getItemFromDB("SELECT COUNT(*) FROM  finascop_stock_item_inventorydetails WHERE stiid_itemmasterid = {$datas[$i]['stit_id']} AND cpd_branch_id = {$datas[$i]['branchId']} {$dispatchStatus}")) : '-';
                $datas[$i]['dispatch_count'] = ($selBranchCpd == 1) ? dispatchCount($datas[$i]['branchId'], $datas[$i]['stit_id']) : '-';
                $datas[$i]['receive_count'] = ($selBranchCpd == 1) ? '-' : ($db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stiid_itemmasterid = {$datas[$i]['stit_id']} AND cpd_branch_id = {$datas[$i]['branchId']} AND fsbg_id = {$datas[$i]['fsbg_id']} {$receiveStatus}"));
                $datas[$i]['deliver_count'] = ($selBranchCpd == 1) ? '-' : ($db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_item_inventorydetails WHERE stiid_itemmasterid = {$datas[$i]['stit_id']} AND cpd_branch_id = {$datas[$i]['branchId']} AND fsbg_id = {$datas[$i]['fsbg_id']} {$deliverStatus}"));
                $datas[$i]['fsbg_name'] = $db->getItemFromDB("SELECT fsbg_name FROM finascop_stock_item_batch_group WHERE fsbg_id = {$datas[$i]['fsbg_id']}");
                $datas[$i]['cart_count'] = $db->getItemFromDB("SELECT sum(count) FROM finascop_stock_blocked  WHERE markedfordelivery =1 and item_id ={$datas[$i]['stit_id']} and branch_id = {$datas[$i]['branchId']}");
                $datas[$i]['blocked_count'] = $db->getItemFromDB("SELECT sum(count) FROM finascop_stock_blocked  WHERE markedfordelivery = 0 and item_id ={$datas[$i]['stit_id']} and branch_id = {$datas[$i]['branchId']}");
                if ($datas[$i]['stit_ParentItemId'] == 0) {
                    $datas[$i]['least_package_type_name'] = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type  WHERE package_type_id = {$datas[$i]['least_package_type_id']}");
                } else {
                    $datas[$i]['least_package_type_name'] = 'Nos';
                }

                switch ($datas[$i]['br_PyramidLevel']) {
                    case 2:
                        $datas[$i]['purchasing_unitname'] = $datas[$i]['cs_package_type_name'];
                        break;
                    case 3:
                        $datas[$i]['purchasing_unitname'] = $datas[$i]['ds_package_type_name'];
                        break;
                    case 4:
                        $datas[$i]['purchasing_unitname'] = $datas[$i]['ds_package_type_name'];
                        break;
                }
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
            //echo json_encode($qry);
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        break;
    case 'dgetPurchaseOrderData':
        $limit = max(1, min(200, intval($_POST['limit'] ?? 23)));
        $start = max(0, intval($_POST['start'] ?? 0));
        $limit = is_numeric($limit) && $limit > 0 ? $limit : 12;
        $start = is_numeric($start) && $start > 0 ? $start : 0;
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'fpo_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $today = date('Y-m-d');
        $filter_qry = " WHERE 1 = 1 AND branch_id = {$_SESSION['admin']->finascop_current_branch_id} AND fpo_poDate = '{$today}'";
        if (isset($_POST['filter'])) {
        $allowedFields = ['br_id', 'date_from', 'date_to', 'order_status', 'order_type'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }

                        break;
                }
            }
        }
        if ($sort == 'fpo_poDate') {
            $sort = 'fpo_id';
        }

        $date = date('dd-mm-YYYY');
        $countDataQuery = "SELECT count(*) from finascop_purchase_order fp INNER JOIN finascop_usr_master um ON fp.fpo_poOrderedby = um.UserId {$filter_qry} ";
        $listQuery = "SELECT  fpo_id,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') as fpo_poDate,fpo_paymentTerms,fpo_poDeliveryDate,fpo_poDeliveryType,fpo_paymentValue ,UserName,fpo_vendorName,
                DATE_FORMAT(fpo_validDate,'%d-%m-%Y') as fpo_validDate,IF((fpo_Active=1),'Active','Inactive') AS fpo_Active,IF((fpo_potype=1),'Manual','Initiated') AS fpo_potype   
     FROM finascop_purchase_order fp INNER JOIN finascop_usr_master um ON fp.fpo_poOrderedby = um.UserId {$filter_qry} ORDER BY {$sort} {$dir} LIMIT $start,$limit ";
        $db->printGridJson($countDataQuery, $listQuery);
        break;
    case 'dlistRetalineTransferRequest':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 15;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fsto_id' : ($sort == 'fsto_createdOn' ? 'fsto_id' : $sort);
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 AND fsto_status <> 10 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status_name') {
                            if ($field['data']['value'] == 'Transfer Requested') {
                                //    $field['data']['value'] = 2;
                                $fiterItem = 1;
                                $search .= " and (fsto_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Partially Attended') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 5;
                                $search .= " and (fsto_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Completely Attended') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 10;
                                $search .= " and (fsto_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Invoke Expired') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 15;
                                $search .= " and (fsto_status = {$fiterItem}) ";
                            } else {
                                //    $field['data']['value'] = 0;
                                $search .= " and (fsto_status = 1 or fsto_status = 5 or fsto_status = 10 ) ";
                            }
                        }

                        break;
                    case 'string':
                        foreach ($filter as $key => $field) {
                            if ($field['data']['value'] != "") {
                                $checkComa = strstr($field['data']['value'], ',');
                                if ($checkComa != '') {
                                    $fiterItem = $field['data']['value'];
                                    $fiterItem = str_replace(',', "','", $fiterItem);
                                    $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                                } else {
                                    $search .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                                }
                            }
                        }
                }
            }
        }
        $TODAY = date('Y-m-d');
        $where = " AND fsto_source = {$_SESSION['admin']->finascop_current_branch_id} AND DATE_FORMAT(fsto_createdOn,'%Y-%m-%d') = '{$TODAY}'";
        $countQuery = "SELECT COUNT(*) FROM finascop_stock_transfer_order  INNER JOIN finascop_stock_transfer_order_status ON fstos_id = fsto_status {$search} {$where}";

        $listQuery = "SELECT fsto_id,fsto_uid,fsto_source,fsto_destination,fsto_status,"
            . "CASE WHEN fsto_type=1 THEN 'Transfer Invoked' WHEN fsto_type=2 THEN 'Stock Requested' WHEN fsto_type=3 THEN 'Sales Order'  ELSE 'Transfer Requested' END AS fsto_type,"
            . "fstos_status AS status_name,"
            . "DATE_FORMAT(fsto_createdOn,'%d %M %Y') as fsto_createdOn,"
            . "fsto_initiatedBy,(SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_initiatedBy) as initiatedBranch,"
            . "(SELECT br_Name FROM finascop_branch where br_ID = fsto_source) as sourcename,"
            . "(SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) as branch  FROM finascop_stock_transfer_order INNER JOIN finascop_stock_transfer_order_status ON fstos_id = fsto_status   {$search} {$where} ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'dlistRetalineTransferReceivals':
        $limit = isset($_POST['limit']) && $_POST['limit'] > 0 ? $_POST['limit'] : 15;
        $start = isset($_POST['start']) && $_POST['start'] > 0  ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fsto_id' : ($sort == 'fsto_createdOn' ? 'fsto_id' : $sort);
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status_name') {
                            if ($field['data']['value'] == 'Transfer Requested') {
                                //    $field['data']['value'] = 2;
                                $fiterItem = 1;
                                $search .= " and (fsto_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Partially Attended') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 5;
                                $search .= " and (fsto_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Completely Attended') {
                                //    $field['data']['value'] = 1;
                                $fiterItem = 10;
                                $search .= " and (fsto_status = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Invoke Expired') {
                                //    $field['data']['value'] = 3;
                                $fiterItem = 15;
                                $search .= " and (fsto_status = {$fiterItem}) ";
                            } else {
                                //    $field['data']['value'] = 0;
                                $search .= " and (fsto_status = 1 or fsto_status = 5 or fsto_status = 10 ) ";
                            }
                        }

                        break;
                    case 'string':
                        foreach ($filter as $key => $field) {
                            if ($field['data']['value'] != "") {
                                $checkComa = strstr($field['data']['value'], ',');
                                if ($checkComa != '') {
                                    $fiterItem = $field['data']['value'];
                                    $fiterItem = str_replace(',', "','", $fiterItem);
                                    $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                                } else {
                                    $search .= " and ({$field['field']} LIKE '%{$field['data']['value']}%') ";
                                }
                            }
                        }
                }
            }
        }
        $TODAY = date('Y-m-d');
        $where = " AND fsto_destination = {$_SESSION['admin']->finascop_current_branch_id} AND DATE_FORMAT(fsto_createdOn,'%Y-%m-%d') = '{$TODAY}'";
        $countQuery = "SELECT COUNT(*) FROM finascop_stock_transfer_order  INNER JOIN finascop_stock_transfer_order_status ON fstos_id = fsto_status {$search} {$where}";

        $listQuery = "SELECT fsto_id,fsto_uid,fsto_source,fsto_destination,fsto_status,"
            . "CASE WHEN fsto_type=1 THEN 'Transfer Invoked' WHEN fsto_type=2 THEN 'Stock Requested' WHEN fsto_type=3 THEN 'Sales Order'  ELSE 'Transfer Requested' END AS fsto_type,"
            . "fstos_status AS status_name,"
            . "DATE_FORMAT(fsto_createdOn,'%d %M %Y') as fsto_createdOn,"
            . "fsto_initiatedBy,(SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_initiatedBy) as initiatedBranch,"
            . "(SELECT br_Name FROM finascop_branch where br_ID = fsto_source) as sourcename,"
            . "(SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) as branch  FROM finascop_stock_transfer_order INNER JOIN finascop_stock_transfer_order_status ON fstos_id = fsto_status   {$search} {$where} ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
}
