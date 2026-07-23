<?php

function _exportExcelReport($data) {

    /*
     * Created on 25-Nov-10
     * @author : Azad K G <azad@saturn.in>
     *
     * To create excel of report
     */
    global $db;
    /* Title Settings */
    require_once INCLUDE_PATH . '/simpleExcelWriter.php';

    $query = $_SESSION['Export']['Query'];



    $heads = json_decode(stripslashes($data['headers']), true);
    $fields = json_decode(stripslashes($data['dataindexes']), true);
    $excel = new simpleExcelWriter($db);
    $time = date('YmdHis');
    if (!empty($data['name'])) {
        if ($data['name'] == 'partner_daily_') {
            $lastParameters = json_decode(stripslashes(($_POST["filterData"])), true);
            $excel->exportFile = $data['name'] . $lastParameters['daily_date'] . '.xls';
        } else {
            $excel->exportFile = $data['name'] . $time . '.xls';
        }
    } else if (!empty($data['park_from_Date'])) {
        $excel->exportFile = $data['username'] . '_' . $data['park_from_Date'] . 'to' . $data['park_to_Date'] . '.xls';
    } else {
        $excel->exportFile = $_SESSION['Export']['Settings']['title'] . $time . '.xls';
    }

    $excel->totalFields = (isset($_SESSION['Export']['Settings']['totalFields'])) ? $_SESSION['Export']['Settings']['totalFields'] : false;
    $excel->export($query, $heads, $fields);
    exit();
}

function retalineExportToExcel($data, $file = false, $win = false, $title) {
    global $db;
    /* Title Settings */
    require_once INCLUDE_PATH . '/simpleExcelWriter.php';
    if ($win == 'salesreport') {
        $query = $_SESSION['salesreportqry'];
    } else if ($win == 'salesbranchreport') {
        $query = $_SESSION['salesbrncsmryreportqry'];
    } else if ($win == 'pendingorderreport') {
        $query = $_SESSION['pendingbrncsmryreportqry'];
    } else if ($win == 'salesreportmonthly') {
        $query = $_SESSION['salesreportmnthlyqry'];
    }
    try {
        //echo $query;exit();
        $heads = json_decode(stripslashes($data['headers']), true);
        $fields = json_decode(stripslashes($data['dataindexes']), true);
        $excel = new simpleExcelWriter($db);
        if ($file === false) {
            $excel->exportFile = $title . '.xls';
            $excel->export($query, $heads, $fields);
            exit();
        } else {
            echo $query;
            $excel->output($query, $heads, $fields, $file);
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
