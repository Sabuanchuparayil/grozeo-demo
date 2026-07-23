<?php

require_once(INCLUDE_PATH . "/finascop_User.php");
global $db;
switch ($op) {
    case 'getOrderPickerActivity':
        $date = $_POST['date'];
        $rec_limit = empty($_POST['limit']) ? 20 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($_POST['sort']) ? 'rgba_id' : $_POST['sort'];
        $rec_sort_dir = empty($_POST['dir']) ? 'DESC' : $_POST['dir'];
        $filter_part = ' AND 1=1';

        // SECURITY: use buildSafeFilterQuery
        $allowedFields = ['picker_name', 'pa_date', 'pa_status'];
        if (isset($_POST['filter'])) {
            $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
        }
        /* RAW (disabled): if (isset($_POST['filter'])) { */

            foreach ($_POST['filter'] as $key => $val) {
                if ($val['field'] == 'cpd_name') {
                    $cpd_name = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(id),0) FROM retaline_godown_boy WHERE branch_id IN (SELECT GROUP_CONCAT(br_ID) FROM finascop_branch WHERE br_Name LIKE '{$val['data']['value']}%')");
                    $filter_part .= " AND id  IN({$cpd_name}) ";
                } else {
                    $filter_part .= " and " . $val['field'] . " LIKE '%" . $val['data']['value'] . "%' ";
                }
            }
        }

        $user = new \finascop\User();
        $items = $user->getUserActiveBranches($_SESSION['admin']->finascop_typId, $_SESSION['admin']->Finascop_UserId, $_SESSION['admin']->finascop_current_company_id);
        $id_branch = array_column($items, '0');
        $branchids = implode(',', $id_branch);

        $countQuery = "SELECT COUNT(*) from retaline_godown_boy_activity INNER JOIN finascop_branch ON br_ID = rgba_Branchid WHERE rgba_Branchid in({$branchids}) AND rgba_date = '{$date}' {$filter_part}";
        $listQuery = "SELECT rgba_id,rgba_Users,rgba_date,rgba_FirstAccept,rgba_LastAccept,rgba_Sessions,rgba_JobsToday,rgba_JobsCompleted,rgba_JobsPreviousPending,rgba_JobsPending,rgba_Duration,"
                . "rgba_JobChecks,br_Name from retaline_godown_boy_activity INNER JOIN finascop_branch ON br_ID = rgba_Branchid WHERE rgba_Branchid in({$branchids}) AND rgba_date = '{$date}' {$filter_part}  ORDER BY  br_Name ASC LIMIT $rec_start,$rec_limit";
        $db->printGridJson($countQuery, $listQuery);

        break;
    case 'listHistoryofpickerActivity':
        $boyId = $_POST['rgba_boyid'];
        $date = $_POST['date'];
        $countQuery = "SELECT COUNT(*) from retaline_godown_boy_history WHERE id = {$boyId} AND rgbh_date = '{$date}' {$filter_part}";
        $listQuery = "SELECT rgbh_id, login_at,logout_at,CASE WHEN loggedout_by = 1 THEN 'User' WHEN loggedout_by = 2 THEN 'Forced' WHEN loggedout_by = 2 THEN 'Timeout' ELSE '-' END AS loggedout_by from retaline_godown_boy_history WHERE id = {$boyId} AND rgbh_date = '{$date}' {$filter_part}  ORDER BY  rgbh_id ASC ";
        $db->printGridJson($countQuery, $listQuery);
        break;
}