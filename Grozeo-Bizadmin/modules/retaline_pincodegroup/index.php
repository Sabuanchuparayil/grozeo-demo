<?php

switch ($op) {
    case 'savePincodeGroups':
        $rpg_id = $_POST['rpg_id'];
        $rpg['rpg_pincode'] = $_POST['rpg_pincode'];
        $rpg['rpg_name'] = $_POST['rpg_name'];
        if ($rpg_id > 0) {
            $dupName = $db->getItemSafe("SELECT COUNT(*) FROM retaline_pincode_group WHERE rpg_name = ? AND rpg_id <> $rpg_id", "s", [$_POST['rpg_name']]);
            if ($dupName > 0) {
                echo '{"success":false,"valid":false,"msg":"Group already exist."}';
            } else {
                $status = $db->perform('retaline_pincode_group', $rpg, 'update', " rpg_id = {$rpg_id}");
                $lastId = $rpg_id;
            }
        } else {
            $dupName = $db->getItemSafe("SELECT COUNT(*) FROM retaline_pincode_group WHERE rpg_name = ? ", "s", [$_POST['rpg_name']]);
            if ($dupName > 0) {
                echo '{"success":false,"valid":false,"msg":"Group already exist."}';
            } else {
                $status = $db->perform('retaline_pincode_group', $rpg);
                $lastId = $db->insert_id();
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"valid":true,"rpg_id":' . $lastId . '}';
        } else {
            // var_dup($error);
            echo '{"success":false,"valid":false}';
        }
        break;
    case 'listpincodegroup':
        $recLimit = intval($_POST['limit']);
        $recStart = intval($_POST['start']);
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];
//--
        $recLimit = intval($_POST['limit']);
        $recStart = intval($_POST['start']);
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];

        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['pg_name', 'pg_pincode', 'pg_status'];
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
                    case 'list':
                        if ($field == 'is_Active') {
                            $filterCon .= (($filterCon == "") ? " where " : " and ") . 'p.isActive' . " = '" . ($v['data']['value'] == 'Y' ? '1' : '0') . "'";
                        } else {
                            $filterCon .= (($filterCon == "") ? " where " : " and ") . $field . " like '" . $v['data']['value'] . "%'";
                        }
                        break;
                    case 'string':

                        $filterCon .= (($filterCon == "") ? " where " : " and ") . $field . " like '" . $v['data']['value'] . "%'";

                        break;
                }
            }
        }

        $qry = "select count(*) from " . FINASCOP_DB . "retaline_pincode_group {$filterCon}";

        $totalCount = $db->getItemFromDB($qry);
        $prefix = FINASCOP_DB;
        $prefix1 = FINASCOP_DB . 'config';
        $db->query('set @cnt=0');
        $query = "select rpg_id,rpg_name,rpg_pincode,rpg_createdOn from  retaline_pincode_group $filterCon order by $recSort $recSortDir   limit $recStart,$recLimit";
        $data = $db->getMultipleData($query, true);

        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;
    case 'getPincodeGroup':
        $id = intval($_POST['rpg_id']);
        $qry = "select rpg_id,rpg_name,rpg_pincode,rpg_createdOn from  retaline_pincode_group where rpg_id =" . $id;
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'listPincodeGroupTimeSlotStore':
        $rpg_id = $_POST['rpg_id'];
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];
        $filterCon = " WHERE 1=1 ";

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

        $qry = "select count(*) from " . FINASCOP_DB . "retaline_pincode_group_time_range {$filterCon} AND rpg_id = {$rpg_id}";

        $totalCount = $db->getItemFromDB($qry);
        $prefix = FINASCOP_DB;
        $prefix1 = FINASCOP_DB . 'config';
        $db->query('set @cnt=0');
        $query = "select rpgtr.rpg_id,(SELECT rpg_name from retaline_pincode_group rpg where rpg.rpg_id = rpgtr.rpg_id)  as rpg_name,rpgtr_time_from,rpgtr_time_to,rpgtr_time_maxslot "
                . "from  retaline_pincode_group_time_range rpgtr $filterCon AND rpgtr.rpg_id = {$rpg_id} order by $recSort $recSortDir ";
        $data = $db->getMultipleData($query, true);

        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;
    case 'savePincodeGroupsTimeSlots':
        $rpgtr['rpg_id'] = $_POST['rpg_id'];
        $rpgtr['rpgtr_time_from'] = DATE("H:i", STRTOTIME($_POST['rpgtr_time_from']));
        $rpgtr['rpgtr_time_to'] = DATE("H:i", STRTOTIME($_POST['rpgtr_time_to']));
        $rpgtr['rpgtr_time_maxslot'] = $_POST['rpgtr_time_maxslot'];
        $count = $db->getItemSafe("SELECT COUNT(*) FROM retaline_pincode_group_time_range WHERE rpg_id = ? and  rpgtr_time_from = '{$rpgtr['rpgtr_time_from']}'", "i", [$_POST['rpg_id']]);
        $db->query('begin');
        if ($count > 0) {
            $data['rpgtr_updatedOn'] = date('Y-m-d H:i:s');
            $data['rpgtr_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('retaline_pincode_group_time_range', $data, 'update', "bsd_date = '{$data['bsd_date']}'");
        } else {
            $data['rpgtr_createdOn'] = date('Y-m-d H:i:s');
            $data['rpgtr_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('retaline_pincode_group_time_range', $rpgtr);
        }
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Added Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listBranchTimeSlotStore':
        $branch_id = $_POST['branch_id'];
        $recSort = $_POST['sort'];
        $recSortDir = $_POST['dir'];
        $filterCon = " WHERE 1=1 ";

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

        $qry = "select count(*) from " . FINASCOP_DB . "retaline_branch_delivery_slot {$filterCon} AND branch_id = {$branch_id}";

        $totalCount = $db->getItemFromDB($qry);
        $prefix = FINASCOP_DB;
        $prefix1 = FINASCOP_DB . 'config';
        $db->query('set @cnt=0');
        $query = "select rbds_id,branch_id,rbds_time_from,rbds_time_to,rbds_time_maxslot from  retaline_branch_delivery_slot rpgtr $filterCon AND branch_id = {$branch_id} order by $recSort $recSortDir ";
        $data = $db->getMultipleData($query, true);

        echo '{"totalCount":' . $totalCount . ',"data":' . json_encode($data) . '}';
        break;
    case 'saveBranchTimeSlots':
        $rpgtr['branch_id'] = $_POST['branch_id'];
        $rpgtr['rbds_time_from'] = DATE("H:i", STRTOTIME($_POST['rbds_time_from']));
        $rpgtr['rbds_time_to'] = DATE("H:i", STRTOTIME($_POST['rbds_time_to']));
        $rpgtr['rbds_time_maxslot'] = $_POST['rbds_time_maxslot'];
        $count = $db->getItemSafe("SELECT COUNT(*) FROM retaline_branch_delivery_slot WHERE branch_id = ? and  rbds_time_from = '{$rpgtr['rbds_time_from']}'", "i", [$_POST['branch_id']]);
        $db->query('begin');
        if ($count == 0) {
            $rpgtr['rbds_createdOn'] = date('Y-m-d H:i:s');
            $rpgtr['rbds_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('retaline_branch_delivery_slot', $rpgtr);
        } else {
            echo "{success: false,msg:'Time slot already added'}";
            exit();
        }
        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,msg:'Saved Successfully.'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'deleteBranchTimeSlot':
        $db->query('begin');
        $count = $db->getItemSafe("SELECT COUNT() FROM retaline_customer_order WHERE order_slot_id = ?", "i", [$_POST['rbds_id']]);
        if($count > 0){
            echo "{success: false,msg:'Not possible to delete time slot as its mapped to order'}";
            exit();
        }
        $del_query = "DELETE FROM retaline_branch_delivery_slot WHERE rbds_id ='{$_POST['rbds_id']}'";
        $temp = $db->query($del_query);
        $status = $db->query('commit');
        if ($status) {
            echo "{success:true,valid:true,message:'Deleted Succesfully ' }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Deleteing.'}";
        }
        break;
    case 'updateSchedulePacking':
        $br_ID = isset($_POST['branch_id']) ? intval($_POST['branch_id']) : 0;
        $data['br_schedulePackiing'] = $_POST['br_schedulePackiing'];
        $status = $db->perform("finascop_branch", $data, "update", "br_ID={$br_ID}");
        if ($status) {
            echo "{success: true,msg:'Packing hour config updated. '}";
        } else {
            echo "{success: false, errors:  'FINASCOP: Error occured while updating' }";
        }
        break;
}
    