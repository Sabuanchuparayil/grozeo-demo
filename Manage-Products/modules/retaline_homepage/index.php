<?php

switch ($op) {
    case 'changePageStatus':

        $data = $_POST;
        $brid = $data['pageID'];
        $st = ($data['pageStatus'] == 'Active') ? 0 : 1;
        $up = array('is_active' => $st);
        $type = $data['type'];
        if ($type == 'delivery') {
            $status = $db->perform(FINASCOP_DB . "retaline_homepage", $up, "update", "id={$brid}");

            $sort = empty($sort) ? 'order' : $sort;
            $dir = empty($dir) ? 'ASC' : $dir;
            $search = " WHERE 1=1 ";

            $grid_data = json_decode($data['HomePageListdata'], true);
            $count = array();
            foreach ($grid_data as $key => $val) {
                $val['is_active'] = $val['id'] == $brid ? $st : $val['is_active'];
                $key = $key == '' ? "TEMP78965" : $key;
                if ($val['is_active'] == 1) {
                    ++$count[$val['screen']];
                    $db->query("UPDATE retaline_homepage br SET br.order = {$count[$val['screen']]} WHERE br.id = {$val['id']};");
                } else {
                    $db->query("UPDATE retaline_homepage br SET br.order = 0 WHERE br.id = {$val['id']};");
                }
            }
        } else {
            $status = $db->perform(FINASCOP_DB . "retaline_homepage_collect", $up, "update", "id={$brid}");

            $sort = empty($sort) ? 'order' : $sort;
            $dir = empty($dir) ? 'ASC' : $dir;
            $search = " WHERE 1=1 ";

            $grid_data = json_decode($data['HomePageListdata'], true);
            $count = array();
            foreach ($grid_data as $key => $val) {
                $val['is_active'] = $val['id'] == $brid ? $st : $val['is_active'];
                $key = $key == '' ? "TEMP78965" : $key;
                if ($val['is_active'] == 1) {
                    ++$count[$val['screen']];
                    $db->query("UPDATE retaline_homepage_collect br SET br.order = {$count[$val['screen']]} WHERE br.id = {$val['id']};");
                } else {
                    $db->query("UPDATE retaline_homepage_collect br SET br.order = 0 WHERE br.id = {$val['id']};");
                }
            }
        }


        if ($status) {
            $result['success'] = true;
            echo json_encode($result);
        } else {
            $result['success'] = false;
            $result['reason'] = 'Error occured while saving data';
            echo json_encode($result);
        }
        break;
    case 'saveRetalineHomePageOrder':
        $data = $_POST;
        $grid_data = json_decode($data['HomePageListdata'], true);
        $count = array();
        if ($data['type'] == 'delivery') {
            foreach ($grid_data as $key => $val) {
                $key = $key == '' ? "TEMP78965" : $key;
                if ($val['is_active'] == 1) {
                    ++$count[$val['screen']];
                    $db->query("UPDATE retaline_homepage br SET br.order = {$count[$val['screen']]} WHERE br.id = {$val['id']};");
                } else {
                    $db->query("UPDATE retaline_homepage br SET br.order = 0 WHERE br.id = {$val['id']};");
                }
            }
        } else {
            foreach ($grid_data as $key => $val) {
                $key = $key == '' ? "TEMP78965" : $key;
                if ($val['is_active'] == 1) {
                    ++$count[$val['screen']];
                    $db->query("UPDATE retaline_homepage_collect br SET br.order = {$count[$val['screen']]} WHERE br.id = {$val['id']};");
                } else {
                    $db->query("UPDATE retaline_homepage_collect br SET br.order = 0 WHERE br.id = {$val['id']};");
                }
            }
        }


        return '{"success" : true}';
        break;
    case 'listRetalineHomePage':
        $sort = empty($sort) ? 'order' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search_field = $_POST['ScreenName'];
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['hp_id', 'hp_type', 'hp_status', 'hp_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        $countQuery = "SELECT COUNT(*) FROM retaline_homepage {$search} AND screen LIKE '{$search_field}%'";
        $listQuery = "SELECT id,type_id,screen,type,is_active FROM retaline_homepage bh {$search} AND screen LIKE '{$search_field}%' ORDER BY screen DESC , bh.{$sort} {$dir}"; // limit $start,$limit";   
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'listRatalineHomeCollect':
        $sort = empty($sort) ? 'order' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search_field = $_POST['ScreenName'];
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['hp_id', 'hp_type', 'hp_status', 'hp_created_on'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }
        $countQuery = "SELECT COUNT(*) FROM retaline_homepage_collect {$search} AND screen LIKE '{$search_field}%'";
        $listQuery = "SELECT id,type_id,screen,type,is_active FROM retaline_homepage_collect bh {$search} AND screen LIKE '{$search_field}%' ORDER BY screen DESC , bh.{$sort} {$dir}";
        $db->printGridJson($countQuery, $listQuery);
        break;
}