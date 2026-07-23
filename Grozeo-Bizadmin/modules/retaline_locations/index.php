<?php

$userid = $_SESSION['admin']->Finascop_UserId;

switch ($op) {


    case 'saveDistrict':

        $db->query('begin');
        $data = array(
            "dst_Name" => $_POST['name'],
            "status" => $_POST['status'],
            "cnt_ID" => $_POST['country_id'],
            "st_Id" => $_POST['st_ID'],
            "dst_Id" => $_POST['id']
        );
        $state = $data['st_Id'];
        $country_id = $data['cnt_ID'];
        $dst_Id = $data['dst_Id'];
        $dst_Name = $data['dst_Name'];
        if ($state != '') {
            if (empty($_POST['id'])) {
                $hsnUnique = $db->getItemFromDB("SELECT COUNT(*) from finascop_district WHERE dst_Name ='{$dst_Name}'");
                if ($hsnUnique > 0) {
                    echo "{success: false, message:'This District Name already existing.'}";
                    exit;
                } else {
                    unset($data['dst_Id']);
                    $data['created_on'] = date('Y-m-d H:i:s');
                    $data['created_by'] = $userid;
                    $data['st_Id'] = $state;
                    $data['cnt_ID'] = $country_id;
                    $status = $db->perform('finascop_district', $data);
                    $lastId = $db->insert_id();
                }
            } else {
                if ($data['dst_Id'] > 0) {

                    $data['updated_on'] = date('Y-m-d H:i:s');
                    $data['updated_by'] = $userid;
                    $hsnUnique = $db->getItemFromDB("SELECT COUNT(*) from finascop_district WHERE dst_Id!='{$dst_Id}'  AND  dst_Name ='{$dst_Name}'");
                    if ($hsnUnique > 0) {
                        echo "{success: false, message:'This District Name with same State already existing.'}";
                        exit;
                    } else {
                        $status = $db->perform("finascop_district", $data, 'update', 'dst_Id =' . $data['dst_Id']);
                        $lastId = $data['dst_Id'];
                    }
                }
            }

            $return_rec = $db->getFromDb("SELECT dst_Id,dst_Name,status FROM finascop_district WHERE dst_Id = {$lastId}", true);
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

        $dst_Id = isset($_POST['dst_Id']) ? intval($_POST['dst_Id']) : 0;
        if ($dst_Id) {
            $sql = "SELECT dst_Id,dst_Name,status AS comboMasterDistrictStatus FROM finascop_district WHERE dst_Id =" . $dst_Id;
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

        $dst_Id = isset($_POST['dst_Id']) ? intval($_POST['dst_Id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($dst_Id || $ID) {
            $data = $db->getFromDB("SELECT dst_Id,dst_Name,status FROM finascop_district  WHERE dst_Id =" . $dst_Id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'listDistrictsAdddata':

        $data = $_POST['st_ID'];
        $country_id = $_POST['country_id'];
        //$rec_limit = empty($_POST['limit']) ? 15 : $_POST['limit'];
        //$rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'dst_Id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['loc_id', 'loc_name', 'loc_address', 'loc_pincode', 'loc_status'];
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
        if ($data > 0) {
            $countQuery = "SELECT COUNT(*) FROM finascop_district msdist INNER JOIN  finascop_state mststate  ON mststate.st_ID=msdist.st_Id WHERE mststate.st_ID=$data";
            $listQuery = "SELECT dst_Id,dst_Name,IF((msdist.status=1),'Active','Inactive') AS status
               FROM finascop_district msdist INNER JOIN  finascop_state mststate 
               ON mststate.st_ID=msdist.st_Id WHERE mststate.st_ID =$data {$searchitem}
               ORDER BY {$sort} {$dir}";

            $db->printGridJson($countQuery, $listQuery);
        }

        break;

    case 'listDistrictsdata':

        $data = $_POST['st_ID'];
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'dst_Id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['loc_id', 'loc_name', 'loc_address', 'loc_pincode', 'loc_status'];
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
        $countQuery = "SELECT COUNT(*) FROM finascop_district msdist INNER JOIN  finascop_state mststate ON mststate.st_ID=msdist.st_Id WHERE mststate.st_ID =$data ";
        $listQuery = "SELECT dst_Name,IF((mststate.status),'Active','Inactive') AS STATUS 
               FROM finascop_district msdist INNER JOIN  finascop_state mststate 
               ON mststate.st_ID=msdist.st_Id WHERE mststate.st_ID =$data {$searchitem}
               ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'listStates':

        $data = $_POST['country_id'];
        //$rec_limit = empty($_POST['limit']) ? 15 : $_POST['limit'];
        //$rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'st_ID' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['loc_id', 'loc_name', 'loc_address', 'loc_pincode', 'loc_status'];
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
        if ($data > 0) {
            $countQuery = "SELECT COUNT(*) FROM finascop_state WHERE  cnt_ID=$data";
            $listQuery = "SELECT st_ID,state_code,st_name,IF((mss.status=1),'Active','Inactive')AS status,
                    (SELECT COUNT(*) FROM finascop_district WHERE finascop_district.st_Id = mss.st_ID) AS location
                     FROM finascop_state mss INNER JOIN retaline_country msc ON mss.cnt_ID=msc.country_id AND msc.country_id =$data"
                    . "{$search}{$searchitem}  ORDER BY {$sort} {$dir}";
        }


        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'statedetailsView':

        $st_ID = isset($_POST['st_ID']) ? intval($_POST['st_ID']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($st_ID || $ID) {

            $data = $db->getFromDB("SELECT st_ID,state_code,st_name,status FROM finascop_state  WHERE st_ID =" . $st_ID, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'state_form_load':

        $st_ID = isset($_POST['st_ID']) ? intval($_POST['st_ID']) : 0;
        if ($st_ID) {
            $sql = "SELECT st_ID,state_code,st_name,status AS comboMasterStateStatus FROM finascop_state WHERE st_ID =" . $st_ID;
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
            "st_name" => $_POST['name'],
            "state_code" => $_POST['code'],
            "status" => $_POST['status'],
            "cnt_ID" => $_POST['country_id'],
            "st_ID" => $_POST['id']
        );
        $state_code = $data['state_code'];
        $st_ID = $data['st_ID'];
        $country_id = $data['cnt_ID'];
        if ($country_id != '') {


            if (empty($_POST['id'])) {
                $hsnUnique = $db->getItemFromDB("SELECT COUNT(*) from finascop_state WHERE state_code ='{$state_code}'");
                if ($hsnUnique > 0) {
                    echo "{success: false, message:'This State Code already existing.'}";
                    exit;
                } else {
                    unset($data['st_ID']);
                    $data['created_on'] = date('Y-m-d H:i:s');
                    $data['created_by'] = $userid;
                    $data['cnt_ID'] = $country_id;
                    $status = $db->perform('finascop_state', $data);
                    $lastId = $db->insert_id();
                }
            } else {
                if ($data['st_ID'] > 0) {
                    $data['updated_on'] = date('Y-m-d H:i:s');
                    $data['updated_by'] = $userid;
                    $hsnUnique = $db->getItemFromDB("SELECT COUNT(*) from finascop_state WHERE state_code ='{$state_code}' AND st_ID!='{$st_ID}' ");
                    if ($hsnUnique > 0) {
                        echo "{success: false, message:'This State Code already existing.'}";
                        exit;
                    } else {
                        $status = $db->perform("finascop_state", $data, 'update', 'st_ID =' . $data['st_ID']);
                        $lastId = $data['st_ID'];
                    }
                }
            }

            $return_rec = $db->getFromDb("SELECT st_ID,state_code,st_name,status FROM finascop_state WHERE st_ID = {$lastId}", true);
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
        $allowedFields = ['loc_id', 'loc_name', 'loc_address', 'loc_pincode', 'loc_status'];
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
        $countQuery = "SELECT COUNT(*) FROM retaline_country  {$search} AND status = '1' AND is_default = '1' ";
        $listQuery = "SELECT country_id,country_code,country_name,IF((STATUS='1'),'Active','Inactive')AS status,(SELECT COUNT(*) FROM finascop_state WHERE cnt_ID = country_id) AS location,(SELECT COUNT(*) FROM finascop_district WHERE cnt_ID = country_id) AS districts FROM retaline_country" . "{$search}{$searchitem} AND status = '1' AND is_default = '1' ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'country_form_load':

        $country_id = isset($_POST['country_id']) ? intval($_POST['country_id']) : 0;
        if ($country_id) {
            $sql = "SELECT country_id,country_name AS textfieldBrmlocationCountryName,country_code AS textfieldBrmlocationCountryCode,status AS comboBrmlocationStatus FROM retaline_country  WHERE country_id= " . $country_id;
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
            $nameUnique = $db->getItemFromDB("SELECT COUNT(*) from retaline_country WHERE (country_code ='{$code}' OR country_name='{$name}')");
            if ($nameUnique > 0) {
                echo "{success: false, message:'This Country Code or Country name already existing.'}";
                exit;
            } else {
                $status = $db->perform('retaline_country', $data);
                $lastId = $db->insert_id();
            }
        } else {
            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;
            $nameUnique = $db->getItemFromDB("SELECT COUNT(*) from retaline_country WHERE (country_code ='{$code}' OR country_name='{$name}') AND country_id!='{$country_id}' ");
            if ($nameUnique > 0) {
                echo "{success: false, message:'This Country Code or Country name already existing.'}";
                exit;
            } else {
                $status = $db->perform("retaline_country", $data, 'update', 'country_id =' . $data['country_id']);
                $lastId = $data['country_id'];
            }
        }
        $return_rec = $db->getFromDb("SELECT country_id,country_name,country_code,status FROM retaline_country WHERE country_id = {$lastId}", true);
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

            $data = $db->getFromDB("SELECT country_id,country_name,country_code,status FROM retaline_country WHERE country_id =" . $country_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'listCountryState':

        $data = $_POST['country_id'];
        $rec_limit = empty($_POST['limit']) ? 10 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'st_ID' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['loc_id', 'loc_name', 'loc_address', 'loc_pincode', 'loc_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
                }
            }
        }
        $countQuery = "SELECT COUNT(*) FROM finascop_state mss INNER JOIN retaline_country msc ON mss.cnt_ID=msc.country_id AND msc.country_id =$data";
        $listQuery = "SELECT st_ID,state_code,st_name,IF((mss.status=1),'Active','Inactive')AS status,
                    (SELECT COUNT(*) FROM finascop_district WHERE finascop_district.st_Id = st_ID) AS location
                     FROM finascop_state mss INNER JOIN retaline_country msc ON mss.cnt_ID=msc.country_id AND msc.country_id =$data"
                . "{$search}  ORDER BY {$sort} {$dir}";
        $db->printGridJson($countQuery, $listQuery);
        break;
}