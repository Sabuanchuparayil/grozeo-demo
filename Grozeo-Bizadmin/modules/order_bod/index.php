<?php


require_once(INCLUDE_PATH . '/lib.php');
require_once(INCLUDE_PATH . '/config.php');
require_once(INCLUDE_PATH . "/finascop_common_functions.php");

switch ($op) {
    case 'listbodCollectionJobs':
        loadBODJobs();
        break;
    case 'order_details_viewbod':
        require(THIS_MODULE_PATH . "/detailView.php");
        break;
    case 'getBranch':

        //****** Previous Code ******//

        $query = $_POST['query'];
        if ($query != '')
            $con = " and br_Name like '" . $query . "%'";
        else
            $con = '';


        $qry = "select br_ID,CONCAT(br_Name ,'-',branch_shortname) as br_Name from finascop_branch where br_status = 'Active' AND br_ID>0 " . $con . " order by br_Name ";
        $branch = $db->getMulipleData($qry, true);
        if (!empty($branch)) {
            $branch = json_encode($branch);
            echo '{"data":' . $branch . '}';
        } else {
            echo '{"data":[]}';
        }


        break;
    case 'acceptAmount':
        $quor_id = intval($_POST['quor_id']);
        $qry = "select quor_UpdateOn,quor_id,quor_RefNo,quor_TransferOrder_id,quor_Status from qugeo_order where quor_id =  " . $quor_id;
        $bookingtimedetails = $db->getFromDB($qry, true);
        $acceptableStatus = [9, 38];
        if (in_array($bookingtimedetails["quor_Status"], $acceptableStatus)) {
            $bkdt = date('Y-m-d H:i:s');
            $dlsid = ORDER_DELIVERY_COMPLETED_DLS_ID;
            $trsid = 7;
            if ($current_dlsid <> 38) {
                $data["quor_ScheduleDeliveryTime"] = $bkdt;
            }
            $data["quor_DeliveryConfTime"] = $bkdt;

            $data["quor_UpdateOn"] = $bkdt;
            $data["quor_Status"] = $dlsid;


            $db->query('begin');
            $con = ' quor_id=' . $quor_id;
            $db->perform('qugeo_order', $data, 'update', $con);

            $qrystring = $db->getItemFromDB("SELECT quor_StatusUpdateQry FROM qugeo_order WHERE quor_id = {$quor_id}");
            $updateQueries = getQugeoParentStatusUpdated($qrystring, $dlsid);
            $updateQueries = str_replace("###6", "1", $updateQueries);
            $parentOrder = $db->getFromDB("SELECT fstr_id,fsto_ordertype FROM finascop_stock_transfer_order WHERE fsto_id = {$bookingtimedetails['quor_TransferOrder_id']}", true);
            if ($parentOrder['fsto_ordertype'] == 1) {
                $ondel_refer_id = $db->getItemFromDB("SELECT order_ondel_bankref_id FROM retaline_customer_order where order_id = {$parentOrder['fstr_id']}");
            }

            $updateQueries = str_replace("###7", $ondel_refer_id, $updateQueries);
            $updateQuerys = explode(';', $updateQueries);
            foreach ($updateQuerys as $updateQuery) {
                $updateQuery = trim($updateQuery);
                if ($updateQuery != '') {
                    $status = $db->query("{$updateQuery}");
                }
            }

            //Get id from retaline_customer_order		
            $qry = "select order_id from retaline_customer_order where order_order_id = '" . $bookingtimedetails['quor_RefNo'] . "'";
            $orderid = $db->getItemFromDB($qry, true);

            //Get Retuned items
            $qry = "select quor_ItemReturned from qugeo_order where quor_id = " . $quor_id;
            $returneditems = $db->getItemFromDB($qry, true);
            $returnbarcodes = json_decode($returneditems);

            $quor_TransferOrder_id = $db->getItemFromDB("SELECT quor_TransferOrder_id FROM qugeo_order WHERE quor_id = {$quor_id}");

            //UPdate Return
            $qry = "select coalesce(quor_ItemReturned,'') as ss   from qugeo_order where quor_id = " . $quor_id;
            $return_items = $db->getItemFromDB($qry, true);

            $updateurl = $db->getItemFromDb("select quor_ItemReturnUpdate from qugeo_order where quor_id = " . $quor_id, true);
            $updateurl = str_replace("##13", $return_items, $updateurl);

            $quor_AmountCollectible = $db->getItemFromDb("select quor_AmountCollectible from qugeo_order where quor_id = " . $quor_id);
            if ($quor_AmountCollectible > 0) {
               // PayOnDelivery::PODVoucher($bookingtimedetails["quor_TransferOrder_id"]);
            }
            if ($parentOrder['fsto_ordertype'] == 1) {
                $quor_id = $quor_id;
                $custOrderId = $parentOrder['fstr_id'];
                $delQry = "CALL UpdateDeliveryStatus($quor_id,$custOrderId,'".$bkdt."')";
                $status = $db->query($delQry);
            }
            DeliveryConfirmation::DeliveryConfirmationVoucher($quor_TransferOrder_id);
            DeliveryConfirmation::DeliveryEmail($quor_TransferOrder_id);

            $db->query('commit');
            echo '{"success":true,"msg":"Updated the Job"}';
        } else {
            echo '{"success":false,"msg":"The Order has been edited, please re-load the Jobs and edit the details again."}';
            exit;
        }


        break;
    case 'deletePGChargeEntry':
        $pgChargeId = $_POST['pgChargeId'];
        $db->query('begin');
        $epdata['pgChargeStatus'] = 0;
        $epdata['pgChargeUpdatedOn'] = date('Y-m-d H:i:s');
        $epdata['pgChargeUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('pgcharge_master', $epdata, 'update', 'pgChargeId =' . $pgChargeId);
        $status = $db->query('commit');
        if ($status) {
            $msg = "Entry removed";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'savePGCharges':
        $rpgtr['pgChargeName'] = $_POST['pgChargeName'];
        $rpgtr['pgChargePercentage'] = $_POST['pgChargePercentage'];
        $rpgtr['pgChargeStatus'] = 1;

        $db->query('begin');
        $pgChargeId = $db->getItemFromDB("SELECT pgChargeId from pgcharge_master WHERE pgChargeName ='{$rpgtr['pgChargeName']}'");
        if ($pgChargeId > 0) {
            $rpgtr['pgChargeUpdatedOn'] = date('Y-m-d H:i:s');
            $rpgtr['pgChargeUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform("pgcharge_master", $rpgtr, 'update', 'pgChargeId =' . $pgChargeId);
        } else {
            $rpgtr['pgChargeCreatedOn'] = date('Y-m-d H:i:s');
            $rpgtr['pgChargeCreatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('pgcharge_master', $rpgtr);
        }

        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Saved Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listPGCharges':
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];
        $filterCon = " WHERE 1=1 AND pgChargeStatus = 1 ";

        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['order_generated_id', 'bod_date', 'bod_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* RAW (disabled): if (isset($_POST['filter']) && $_POST['filter'] != '') { */
            foreach ($_POST['filter'] as $key => $v) {
                if (array_key_exists($v['field'], $fields))
                    $field = $fields[$v['field']];
                else {
                    $field = $v['field'];
                }
                switch ($v['data']['type']) {
                    case 'string':

                        $filterCon .= (($filterCon == "") ? " where " : " and ") . $field . " like '" . $v['data']['value'] . "%'";

                        break;
                }
            }
        }

        $condition = " ";
        $qry = "select count(*) from  pgcharge_master {$filterCon} ";

        $totalCount = $db->getItemFromDB($qry);

        $db->query('set @cnt=0');
        $query = "select * from  pgcharge_master  $filterCon $condition order by pgChargeId desc ";
        $data = $db->getMultipleData($query, true);

        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;
    case 'setPGChargeDefault':
        $pgChargeId = $_POST['pgChargeId'];
        $currentpgChargeIsDefault = $_POST['pgChargeIsDefault'];
        if ($currentpgChargeIsDefault == 0) {
            $pgchg['pgChargeIsDefault'] = 1;
            $pgchg['pgChargeUpdatedOn'] = date('Y-m-d H:i:s');
            $pgchg['pgChargeUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;

            $db->query('begin');
            $defaultCount = $db->getItemFromDB("SELECT COUNT(*) FROM pgcharge_master WHERE pgChargeIsDefault = 1");
            if ($defaultCount > 0) {
                $status = $db->query("UPDATE pgcharge_master SET pgChargeIsDefault = 0 ");
            }
            $status = $db->perform("pgcharge_master", $pgchg, 'update', 'pgChargeId =' . $pgChargeId);
            $status = $db->query('commit');
        }

        if ($status) {
            echo "{success: true,msg:'Updated Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'deleteSettlementDaysEntry':
        $sdId = $_POST['sdId'];
        $db->query('begin');
        $epdata['sdStatus'] = 0;
        $epdata['sdUpdatedOn'] = date('Y-m-d H:i:s');
        $epdata['sdUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $db->perform('settlementDays_master', $epdata, 'update', 'sdId =' . $sdId);
        $status = $db->query('commit');
        if ($status) {
            $msg = "Entry removed";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'saveSettlementDays':
        $rpgtr['sdName'] = $_POST['sdName'];
        $rpgtr['sdDays'] = $_POST['sdDays'];
        $rpgtr['sdStatus'] = 1;

        $db->query('begin');
        $sdId = $db->getItemFromDB("SELECT sdId from settlementDays_master WHERE sdName ='{$rpgtr['sdName']}'");
        if ($sdId > 0) {
            $rpgtr['sdUpdatedOn'] = date('Y-m-d H:i:s');
            $rpgtr['sdUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform("settlementDays_master", $rpgtr, 'update', 'sdId =' . $sdId);
        } else {
            $rpgtr['sdCreatedOn'] = date('Y-m-d H:i:s');
            $rpgtr['sdCreatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('settlementDays_master', $rpgtr);
        }

        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Saved Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listSettlementDays':
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];
        $filterCon = " WHERE 1=1 AND sdStatus = 1 ";

        if (isset($_POST['filter']) && $_POST['filter'] != '') {
            foreach ($_POST['filter'] as $key => $v) {
                if (array_key_exists($v['field'], $fields))
                    $field = $fields[$v['field']];
                else {
                    $field = $v['field'];
                }
                switch ($v['data']['type']) {
                    case 'string':

                        $filterCon .= (($filterCon == "") ? " where " : " and ") . $field . " like '" . $v['data']['value'] . "%'";

                        break;
                }
            }
        }

        $condition = " ";
        $qry = "select count(*) from  settlementDays_master {$filterCon} ";

        $totalCount = $db->getItemFromDB($qry);

        $db->query('set @cnt=0');
        $query = "select * from  settlementDays_master  $filterCon $condition order by sdId desc ";
        $data = $db->getMultipleData($query, true);

        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;
    case 'setSettlementDayseDefault':
        $sdId = $_POST['sdId'];
        $currentsdIsDefault = $_POST['sdIsDefault'];
        if ($currentsdIsDefault == 0) {
            $pgchg['sdIsDefault'] = 1;
            $pgchg['sdUpdatedOn'] = date('Y-m-d H:i:s');
            $pgchg['sdUpdatedBy'] = $_SESSION['admin']->Finascop_UserId;

            $db->query('begin');
            $defaultCount = $db->getItemFromDB("SELECT COUNT(*) FROM settlementDays_master WHERE sdIsDefault = 1");
            if ($defaultCount > 0) {
                $status = $db->query("UPDATE settlementDays_master SET sdIsDefault = 0 ");
            }
            $status = $db->perform("settlementDays_master", $pgchg, 'update', 'sdId =' . $sdId);
            $status = $db->query('commit');
        }

        if ($status) {
            echo "{success: true,msg:'Updated Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
}
