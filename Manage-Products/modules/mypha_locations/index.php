<?php

$userid = $_SESSION['admin']->Finascop_UserId;

switch ($op) {


    case 'saveDistrict':

        $db->query('begin');
        $data = array(
            "district_name" => $_POST['name'],
            "status" => $_POST['status'],
            "mst_district_country_id" => $_POST['country_id'],
            "mst_district_state_id" => $_POST['state_id'],
            "district_id" => $_POST['id']
        );
        $state = $data['mst_district_state_id'];
        $country_id = $data['mst_district_country_id'];
        $district_id = $data['district_id'];
        $district_name = $data['district_name'];
        if ($state != '') {
            if (empty($_POST['id'])) {
                $hsnUnique = $db->getItemFromDB("SELECT COUNT(*) from mst_district WHERE district_name ='{$district_name}'");
                if ($hsnUnique > 0) {
                    echo "{success: false, message:'This District Name already existing.'}";
                    exit;
                } else {
                    unset($data['district_id']);
                    $data['created_on'] = date('Y-m-d H:i:s');
                    $data['created_by'] = $userid;
                    $data['mst_district_state_id'] = $state;
                    $data['mst_district_country_id'] = $country_id;
                    $status = $db->perform('mst_district', $data);
                    $lastId = $db->insert_id();
                }
            } else {
                if ($data['district_id'] > 0) {

                    $data['updated_on'] = date('Y-m-d H:i:s');
                    $data['updated_by'] = $userid;
                    $hsnUnique = $db->getItemFromDB("SELECT COUNT(*) from mst_district WHERE district_id!='{$district_id}'  AND  district_name ='{$district_name}'");
                    if ($hsnUnique > 0) {
                        echo "{success: false, message:'This District Name with same State already existing.'}";
                        exit;
                    } else {
                        $status = $db->perform("mst_district", $data, 'update', 'district_id =' . $data['district_id']);
                        $lastId = $data['district_id'];
                    }
                }
            }

            $return_rec = $db->getFromDb("SELECT district_id,district_name,status FROM mst_district WHERE district_id = {$lastId}", true);
            $status = $db->query('commit');

            if ($status == 1) {
                echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
            } else {
                echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
            }
        } else {
            echo "{success: false, message:'State is not selected'}";
        }
        break;

    case 'district_form_load':

        $district_id = isset($_POST['district_id']) ? intval($_POST['district_id']) : 0;
        if ($district_id) {
            $sql = "SELECT district_id,district_name,status AS comboMasterDistrictStatus FROM mst_district WHERE district_id =" . $district_id;
            $results = $db->getFromDB($sql, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;

    case 'districtdetailsView':

        $district_id = isset($_POST['district_id']) ? intval($_POST['district_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($district_id || $ID) {
            $data = $db->getFromDB("SELECT district_id,district_name,status FROM mst_district  WHERE district_id =" . $district_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'listDistrictsAdddata':

        $data = $_POST['state_id'];
        $country_id = $_POST['country_id'];
        $rec_limit = empty($_POST['limit']) ? 15 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'district_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                        }
                }
            }
        }
        $countQuery = "SELECT COUNT(*) FROM mst_district msdist INNER JOIN  mst_state mststate  ON mststate.state_id=msdist.mst_district_state_id WHERE mststate.state_id=$data";
        $listQuery = "SELECT district_id,district_name,IF((msdist.status=1),'Active','Inactive') AS status
               FROM mst_district msdist INNER JOIN  mst_state mststate 
               ON mststate.state_id=msdist.mst_district_state_id WHERE mststate.state_id =$data {$searchitem}
               ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'listDistrictsdata':

        $data = $_POST['state_id'];
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'district_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                        }
                }
            }
        }
        $countQuery = "SELECT COUNT(*) FROM mst_district msdist INNER JOIN  mst_state mststate ON mststate.state_id=msdist.mst_district_state_id WHERE mststate.state_id =$data ";
        $listQuery = "SELECT district_name,IF((mststate.status),'Active','Inactive') AS STATUS 
               FROM mst_district msdist INNER JOIN  mst_state mststate 
               ON mststate.state_id=msdist.mst_district_state_id WHERE mststate.state_id =$data {$searchitem}
               ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'listStates':

        $data = $_POST['country_id'];
        $rec_limit = empty($_POST['limit']) ? 15 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'state_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                        }
                }
            }
        }
        $countQuery = "SELECT COUNT(*) FROM mst_state WHERE  mst_states_country_id=$data";
        $listQuery = "SELECT state_id,state_code,state_name,IF((mss.status=1),'Active','Inactive')AS status,
                    (SELECT COUNT(*) FROM mst_district WHERE mst_district_state_id = state_id) AS location
                     FROM mst_state mss INNER JOIN finascop_country msc ON mss.mst_states_country_id=msc.country_id AND msc.country_id =$data"
                . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'statedetailsView':

        $state_id = isset($_POST['state_id']) ? intval($_POST['state_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($state_id || $ID) {

            $data = $db->getFromDB("SELECT state_id,state_code,state_name,status FROM mst_state  WHERE state_id =" . $state_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'state_form_load':

        $state_id = isset($_POST['state_id']) ? intval($_POST['state_id']) : 0;
        if ($state_id) {
            $sql = "SELECT state_id,state_code,state_name,status AS comboMasterStateStatus FROM mst_state WHERE state_id =" . $state_id;
            $results = $db->getFromDB($sql, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;

    case 'saveStates':

        $db->query('begin');
        $data = array(
            "state_name" => $_POST['name'],
            "state_code" => $_POST['code'],
            "status" => $_POST['status'],
            "mst_states_country_id" => $_POST['country_id'],
            "state_id" => $_POST['id']
        );
        $state_code = $data['state_code'];
        $state_id = $data['state_id'];
        $country_id = $data['mst_states_country_id'];
        if ($country_id != '') {


            if (empty($_POST['id'])) {
                $hsnUnique = $db->getItemFromDB("SELECT COUNT(*) from mst_state WHERE state_code ='{$state_code}'");
                if ($hsnUnique > 0) {
                    echo "{success: false, message:'This State Code already existing.'}";
                    exit;
                } else {
                    unset($data['state_id']);
                    $data['created_on'] = date('Y-m-d H:i:s');
                    $data['created_by'] = $userid;
                    $data['mst_states_country_id'] = $country_id;
                    $status = $db->perform('mst_state', $data);
                    $lastId = $db->insert_id();
                }
            } else {
                if ($data['state_id'] > 0) {
                    $data['updated_on'] = date('Y-m-d H:i:s');
                    $data['updated_by'] = $userid;
                    $hsnUnique = $db->getItemFromDB("SELECT COUNT(*) from mst_state WHERE state_code ='{$state_code}' AND state_id!='{$state_id}' ");
                    if ($hsnUnique > 0) {
                        echo "{success: false, message:'This State Code already existing.'}";
                        exit;
                    } else {
                        $status = $db->perform("mst_state", $data, 'update', 'state_id =' . $data['state_id']);
                        $lastId = $data['state_id'];
                    }
                }
            }

            $return_rec = $db->getFromDb("SELECT state_id,state_code,state_name,status FROM mst_state WHERE state_id = {$lastId}", true);
            $status = $db->query('commit');

            if ($status == 1) {
                echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
            } else {
                echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
            }
        } else {
            echo "{success: false, message:'Country is not Selected'}";
        }
        break;

    case 'listCountry':

        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'country_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                        }
                }
            }
        }
        $countQuery = "SELECT COUNT(*) FROM finascop_country  {$search}";
        $listQuery = "SELECT country_id,country_code,country_name,IF((STATUS=1),'Active','Inactive')AS status FROM finascop_country" . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'country_form_load':

        $country_id = isset($_POST['country_id']) ? intval($_POST['country_id']) : 0;
        if ($country_id) {
            $sql = "SELECT country_id,country_name AS textfieldOrginCountryName,country_code AS textfieldOrginCountryCode,status AS comboCntryOrginStatus FROM finascop_country  WHERE country_id= " . $country_id;
            $results = $db->getFromDB($sql, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;

    case 'saveCountryDetails':

        $db->query('begin');
        $data = array(
            "country_name" => $_POST['name'],
            "country_code" => $_POST['code'],
            "status" => $_POST['status'],
            "country_id" => $_POST['id']
        );
        $country_id = $data['country_id'];
        $code = $data['country_code'];
        $name = $data['country_name'];

        if (empty($_POST['id'])) {

            unset($data['country_id']);
            $data['created_on'] = date('Y-m-d H:i:s');
            $data['created_by'] = $userid;
            $nameUnique = $db->getItemFromDB("SELECT COUNT(*) from finascop_country WHERE (country_code ='{$code}' OR country_name='{$name}')");
            if ($nameUnique > 0) {
                echo "{success: false, message:'This Country Code or Country name already existing.'}";
                exit;
            } else {
                $status = $db->perform('finascop_country', $data);
                $lastId = $db->insert_id();

                $data['country_id'] = $lastId;
                $status = $parentdb->perform('finascop_country', $data);
            }
        } else {
            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;
            $nameUnique = $db->getItemFromDB("SELECT COUNT(*) from finascop_country WHERE (country_code ='{$code}' OR country_name='{$name}') AND country_id!='{$country_id}' ");
            if ($nameUnique > 0) {
                echo "{success: false, message:'This Country Code or Country name already existing.'}";
                exit;
            } else {
                $status = $db->perform("finascop_country", $data, 'update', 'country_id =' . $data['country_id']);
                $lastId = $data['country_id'];
                $status = $parentdb->perform("finascop_country", $data, 'update', 'country_id =' . $data['country_id']);
            }
        }
        $return_rec = $db->getFromDb("SELECT country_id,country_name,country_code,status FROM finascop_country WHERE country_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'CountrydetailsView':

        $country_id = isset($_POST['country_id']) ? intval($_POST['country_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($country_id || $ID) {

            $data = $db->getFromDB("SELECT country_id,country_name,country_code,status FROM finascop_country WHERE country_id =" . $country_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'listCountryState':

        $data = $_POST['country_id'];
        $rec_limit = empty($_POST['limit']) ? 10 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'state_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
                }
            }
        }
        $countQuery = "SELECT COUNT(*) FROM mst_state mss INNER JOIN finascop_country msc ON mss.mst_states_country_id=msc.country_id AND msc.country_id =$data";
        $listQuery = "SELECT state_id,state_code,state_name,IF((mss.status=1),'Active','Inactive')AS status,
                    (SELECT COUNT(*) FROM mst_district WHERE mst_district_state_id = state_id) AS location
                     FROM mst_state mss INNER JOIN finascop_country msc ON mss.mst_states_country_id=msc.country_id AND msc.country_id =$data"
                . "{$search}  ORDER BY {$sort} {$dir}";
        $db->printGridJson($countQuery, $listQuery);
        break;
}