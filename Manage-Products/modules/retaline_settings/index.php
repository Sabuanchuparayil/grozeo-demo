<?php

$userid = $_SESSION['admin']->Finascop_UserId;
require_once(INCLUDE_PATH . "/finascop_wallet_client.php");
require_once(INCLUDE_PATH . "/brmClass.php");

switch ($op) {


    case 'listFaq':


        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'faq_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['rs_id', 'rs_key', 'rs_value', 'rs_status'];
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


        $countQuery = "SELECT COUNT(*) FROM app_faqs  {$search}";
        $listQuery = "SELECT faq_id,faq_title,faq_description,IF((faq_status=1),'Active','Inactive') AS faq_status FROM app_faqs 
        " . "{$search} {$searchitem} ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'FaqdetailsView':
        $faq_id = isset($_POST['faq_id']) ? intval($_POST['faq_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($faq_id || $ID) {

            $data = $db->getFromDB("SELECT faq_id,faq_title,faq_description,faq_status AS faq_status FROM app_faqs  WHERE faq_id =" . $faq_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'faq_form_load':

        $faq_id = isset($_POST['faq_id']) ? intval($_POST['faq_id']) : 0;
        if ($faq_id) {
            $sql = "SELECT  faq_id,faq_title,faq_description,faq_status AS faq_status FROM app_faqs WHERE faq_id =" . $faq_id;
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


    case 'saveFaq':

        $db->query('begin');


        $data = array(
            "faq_id" => $_POST['faq_id'],
            "faq_title" => $_POST['faq_title'],
            "faq_description" => $_POST['faq_description'],
            "faq_status" => $_POST['faq_status']
        );
        $faq_id = $data['faq_id'];
        $faq_title = $data['faq_title'];
        $faq_description = $data['faq_description'];
        $faq_status = $data['faq_status'];

        $faq_title = addslashes($faq_title);



        if ($data['faq_id'] > 0) {
            $data['faq_updatedOn'] = date('Y-m-d H:i:s');
            $data['faq_updatedBy'] = $userid;
            $CourierUnique = $db->getItemFromDB("SELECT COUNT(*) from app_faqs WHERE faq_title ='{$faq_title}' AND faq_id!='{$faq_id}' ");
            if ($CourierUnique > 0) {
                echo "{success: false, message:'This  Courier name already existing.'}";
                exit;
            } else {


                $faq_status = $db->perform("app_faqs", $data, 'update', 'faq_id =' . $data['faq_id']);
                $lastId = $data['faq_id'];
            }
        } else {
            $CourierUnique = $db->getItemFromDB("SELECT COUNT(*) from app_faqs WHERE faq_title ='{$faq_title}' AND faq_id!='{$faq_id}' ");
            if ($CourierUnique > 0) {
                echo "{success: false, message:'This  Courier name already existing.'}";
                exit;
            } else {
                unset($data['faq_id']);
                $data['faq_createdOn'] = date('Y-m-d H:i:s');
                $data['faq_createdBy'] = $userid;
                $faq_status = $db->perform('app_faqs', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT  faq_id,faq_title,faq_description,faq_status FROM app_faqs WHERE faq_id = {$lastId}", true);
        $faq_status = $db->query('commit');
        if ($faq_status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;



    case 'listPage':


        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'page_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['rs_id', 'rs_key', 'rs_value', 'rs_status'];
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


        $countQuery = "SELECT COUNT(*) FROM app_pages  {$search}";
        $listQuery = "SELECT page_id,page_name,page_content,IF((page_status=1),'Active','Inactive') AS page_status FROM app_pages 
        " . "{$search} {$searchitem} ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'pagedetailsView':
        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($page_id || $ID) {

            $data = $db->getFromDB("SELECT page_id,page_name,page_content,page_status AS page_status FROM app_pages  WHERE page_id =" . $page_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'page_form_load':

        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        if ($page_id) {
            $sql = "SELECT  page_id,page_name,page_content,page_status AS page_status FROM app_pages WHERE page_id =" . $page_id;
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


    case 'savePage':

        $db->query('begin');


        $data = array(
            "page_id" => $_POST['page_id'],
            "page_name" => $_POST['page_name'],
            "page_content" => $_POST['page_content'],
            "page_status" => $_POST['page_status']
        );
        $page_id = $data['page_id'];
        $page_name = $data['page_name'];
        $page_content = $data['page_content'];
        $page_status = $data['page_status'];

        $page_name = addslashes($page_name);



        if ($data['page_id'] > 0) {
            $data['page_updatedOn'] = date('Y-m-d H:i:s');
            $data['page_updatedBy'] = $userid;
            $PageUnique = $db->getItemFromDB("SELECT COUNT(*) from app_pages WHERE page_name ='{$page_name}' AND page_id!='{$page_id}' ");
            if ($PageUnique > 0) {
                echo "{success: false, message:'This name already existing.'}";
                exit;
            } else {
                $page_status = $db->perform("app_pages", $data, 'update', 'page_id =' . $data['page_id']);
                $lastId = $data['page_id'];
            }
        } else {
            $PageUnique = $db->getItemFromDB("SELECT COUNT(*) from app_pages WHERE page_name ='{$page_name}' AND page_id!='{$page_id}' ");
            if ($PageUnique > 0) {
                echo "{success: false, message:'This name already existing.'}";
                exit;
            } else {
                unset($data['page_id']);
                $data['page_createdOn'] = date('Y-m-d H:i:s');
                $data['page_createdBy'] = $userid;
                $page_status = $db->perform('app_pages', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT  page_id,page_name,page_content,page_status FROM app_pages WHERE page_id = {$lastId}", true);
        $page_status = $db->query('commit');
        if ($page_status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'listfeedback':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'fb_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['rs_id', 'rs_key', 'rs_value', 'rs_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM app_feedback  {$search}";
        $listQuery = "SELECT fb_id,fb_mobile,fb_email,fb_comments FROM app_feedback {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'feedbackDetailsView':
        $fb_id = isset($_POST['fb_id']) ? intval($_POST['fb_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($fb_id || $ID) {

            $data = $db->getFromDB("SELECT fb_id,fb_mobile,fb_email,fb_comments FROM app_feedback WHERE fb_id  =" . $fb_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'listnotification':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'notification_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['rs_id', 'rs_key', 'rs_value', 'rs_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


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

        $countQuery = "SELECT COUNT(*) FROM app_notification  {$search}";
        $listQuery = "SELECT notification_id,notification_content,IF((notification_status=1),'Active','Inactive') AS notification_status FROM app_notification {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'saveNotification':
        $db->query('begin');
        $data = $_POST['n'];

        if ($data['notification_id'] > 0) {
            $data['notification_updatedOn'] = date('Y-m-d H:i:s');
            $notification_status = $db->perform("app_notification", $data, 'update', 'notification_id =' . $data['notification_id']);
            if ($data['notification_id'] == 2) {
                $number['tnumber'] = $data['notification_content'];
                $notification_status = $db->perform("app_callcenter", $number, 'update', 'tid = 1');
                $datnumber['notification_status'] = 0;
                $notification_status = $db->perform("app_notification", $datnumber, 'update', 'notification_id =' . $data['notification_id']);
            }
            $lastId = $data['notification_id'];
        } else {
            unset($data['notification_id']);
            $data['notification_updatedBy'] = date('Y-m-d H:i:s');
            $notification_status = $db->perform('app_notification', $data);
            $lastId = $db->insert_id();
        }

        $return_rec = $db->getFromDb("SELECT notification_id,notification_content,notification_status FROM app_notification WHERE notification_id  = {$lastId}", true);
        $notification_status = $db->query('commit');
        if ($notification_status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'notificationDetailsView':
        $notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($notification_id || $ID) {

            $data = $db->getFromDB("SELECT notification_id,notification_content,notification_status FROM app_notification WHERE notification_id  =" . $notification_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'notification_load':
        $notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
        if ($notification_id) {
            $sql = "SELECT notification_id,notification_content,notification_status FROM app_notification  WHERE notification_id= " . $notification_id;
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
    case 'listpaymentTerms':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'ptc_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['rs_id', 'rs_key', 'rs_value', 'rs_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


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

        $countQuery = "SELECT COUNT(*) FROM retaline_paymtTermscfg  {$search}";
        $listQuery = "SELECT ptc_id,ptc_name,ptc_days,IF((ptc_status=1),'Active','Inactive') AS ptc_status FROM retaline_paymtTermscfg {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'paymentTermsDetailsView':
        $ptc_id = isset($_POST['ptc_id']) ? intval($_POST['ptc_id']) : 0;
        if ($ptc_id) {

            $data = $db->getFromDB("SELECT  ptc_id,ptc_name,ptc_days,ptc_status FROM retaline_paymtTermscfg WHERE ptc_id  =" . $ptc_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'pt_form_load':
        $ptc_id = isset($_POST['ptc_id']) ? intval($_POST['ptc_id']) : 0;
        if ($ptc_id) {
            $sql = "SELECT ptc_id,ptc_name,ptc_days,ptc_status FROM retaline_paymtTermscfg WHERE ptc_id = " . $ptc_id;
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
    case 'savePaymentTerms':
        $db->query('begin');
        $data = array(
            "ptc_id" => $_POST['ptc_id'],
            "ptc_name" => $_POST['ptc_name'],
            "ptc_days" => $_POST['ptc_days'],
            "ptc_status" => $_POST['ptc_status']
        );
        $ptc_id = $data['ptc_id'];
        $ptc_name = $data['ptc_name'];

        $ptc_name = addslashes($ptc_name);



        if ($data['ptc_id'] > 0) {
            $data['ptc_updatedOn'] = date('Y-m-d H:i:s');
            $data['ptc_updatedBy'] = $userid;
            $CourierUnique = $db->getItemFromDB("SELECT COUNT(*) from retaline_paymtTermscfg WHERE ptc_name ='{$ptc_name}' AND ptc_id!='{$ptc_id}' ");
            if ($CourierUnique > 0) {
                echo "{success: false, message:'This terms already existing.'}";
                exit;
            } else {


                $status = $db->perform("retaline_paymtTermscfg", $data, 'update', 'ptc_id =' . $ptc_id);
                $lastId = $ptc_id;
            }
        } else {
            $CourierUnique = $db->getItemFromDB("SELECT COUNT(*) from retaline_paymtTermscfg WHERE ptc_name ='{$ptc_name}' AND ptc_id!='{$ptc_id}' ");
            if ($CourierUnique > 0) {
                echo "{success: false, message:'This  terms already existing.'}";
                exit;
            } else {
                unset($data['ptc_id']);
                $data['ptc_createdOn'] = date('Y-m-d H:i:s');
                $data['ptc_createdBy'] = $userid;
                $status = $db->perform('retaline_paymtTermscfg', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT  ptc_id,ptc_name,ptc_days,ptc_status FROM retaline_paymtTermscfg WHERE ptc_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'listCourier':


        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'mst_courier_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['rs_id', 'rs_key', 'rs_value', 'rs_status'];
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


        $countQuery = "SELECT COUNT(*) FROM mst_courier  {$search}";
        $listQuery = "SELECT mst_courier_id,mst_courier_name,mst_courier_url,IF((status=1),'Active','Inactive') AS status FROM mst_courier 
        " . "{$search} {$searchitem} ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'courierdetailsView':
        $mst_courier_id = isset($_POST['mst_courier_id']) ? intval($_POST['mst_courier_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($mst_courier_id || $ID) {

            $data = $db->getFromDB("SELECT mst_courier_id,mst_courier_name,mst_courier_url,status AS status FROM mst_courier  WHERE mst_courier_id =" . $mst_courier_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'courier_form_load':

        $mst_courier_id = isset($_POST['mst_courier_id']) ? intval($_POST['mst_courier_id']) : 0;
        if ($mst_courier_id) {
            $sql = "SELECT  mst_courier_id,mst_courier_name,mst_courier_url,status AS comboMasterCourierStatus,mst_courier_phone FROM mst_courier WHERE mst_courier_id =" . $mst_courier_id;
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


    case 'saveCourier':

        $db->query('begin');


        $data = array(
            "mst_courier_id" => $_POST['id'],
            "mst_courier_name" => $_POST['name'],
            "mst_courier_url" => $_POST['url'],
            "mst_courier_phone" => $_POST['phone'],
            "status" => $_POST['status']
        );
        $mst_courier_id = $data['mst_courier_id'];
        $mst_courier_name = $data['mst_courier_name'];
        $mst_courier_url = $data['mst_courier_url'];
        $status = $data['status'];

        $mst_courier_name = addslashes($mst_courier_name);



        if ($data['mst_courier_id'] > 0) {
            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;
            $CourierUnique = $db->getItemFromDB("SELECT COUNT(*) from mst_courier WHERE mst_courier_name ='{$mst_courier_name}' AND mst_courier_id!='{$mst_courier_id}' ");
            if ($CourierUnique > 0) {
                echo "{success: false, message:'This  Courier name already existing.'}";
                exit;
            } else {

                $FinascopWC = new FinascopWalletClient(FINASCOPAPIDOMAIN);
                $RefIDs['companyApiKey'] = $db->getItemFromDB("SELECT comp_ReferenceId FROM finascop_company WHERE comp_id ={$_SESSION['admin']->finascop_current_company_id}");
                $RefIDs['groupReferenceId'] = SUNDRYCREDITORGRP;
                $RefIDs['branchApiKey'] = $db->getItemFromDB("SELECT br_ReferenceId FROM finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");


                $ReferenceId = $db->getItemFromDB("SELECT accled_ReferenceId FROM mst_courier WHERE mst_courier_id = {$data['mst_courier_id']}");
                $returned = $FinascopWC->editLedger(time(), $ReferenceId, $data['mst_courier_name'], $data['mst_courier_phone'], $RefIDs, $credit_limit = 0);

                $result = json_decode($returned, true);
                if (array_key_exists('success', $result) && $result['success'] == true) {
                    $data['accled_ReferenceId'] = $result['ledgerID'];
                } else {
                    echo "{'success':'false','msg':'Failed to create ledger.{$result['error']}'}";
                    exit(1);
                }
                $status = $db->perform("mst_courier", $data, 'update', 'mst_courier_id =' . $data['mst_courier_id']);
                $lastId = $data['mst_courier_id'];
            }
        } else {
            $CourierUnique = $db->getItemFromDB("SELECT COUNT(*) from mst_courier WHERE mst_courier_name ='{$mst_courier_name}' AND mst_courier_id!='{$mst_courier_id}' ");
            if ($CourierUnique > 0) {
                echo "{success: false, message:'This  Courier name already existing.'}";
                exit;
            } else {


                $FinascopWC = new FinascopWalletClient(FINASCOPAPIDOMAIN);
                $RefIDs['companyApiKey'] = $db->getItemFromDB("SELECT comp_ReferenceId FROM finascop_company WHERE comp_id ={$_SESSION['admin']->finascop_current_company_id}");
                $RefIDs['groupReferenceId'] = SUNDRYCREDITORGRP;
                $RefIDs['branchApiKey'] = $db->getItemFromDB("SELECT br_ReferenceId FROM finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}");

                $returned = $FinascopWC->createLedger(time(), $data['mst_courier_name'], $data['mst_courier_phone'], $RefIDs, $credit_limit = 0);

                $result = json_decode($returned, true);
                if (array_key_exists('success', $result) && $result['success'] == true) {
                    $data['accled_ReferenceId'] = $result['ledgerID'];
                } else {
                    echo "{'success':'false','msg':'Failed to create ledger.{$result['error']}'}";
                    exit(1);
                }

                unset($data['mst_courier_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mst_courier', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT  mst_courier_id,mst_courier_name,mst_courier_url,status FROM mst_courier WHERE mst_courier_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'getBusinessType':

        if ($_POST['primaryBt'] > 0) {
            $primaryBt = $_POST['primaryBt'];
        } else {
            $primaryBt = 0;
        }
        $qry = "select business_type_id,business_type_name from " . FINASCOP_DB . "finascop_business_type where status= 1 AND  business_type_id <> {$primaryBt}  order by business_type_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        // echo '{success:true, data:'.json_encode($data).'}';
        break;
    case 'listStoreGroups':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'store_group_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $searchitem = '';
        //if (isset($data['filter'])) {
        $allowedFields = ['rs_id', 'rs_key', 'rs_value', 'rs_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }
            
            /*foreach ($filter as $key => $field) {
                switch ($field['data']['type']) {
                    case 'list':
                        if ($field['field'] == 'status') {
                            if ($field['data']['value'] == 'Active') {
                                $fiterItem = 1;
                                $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'Inactive') {
                                $fiterItem = 0;
                                $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (status = 1 or status=0) ";
                            }
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
            }*/
        }
        $countQuery = "SELECT COUNT(*) FROM finascop_branch_group  {$search}";
        $listQuery = "SELECT a.store_group_id,store_group_name,IF((status=1),'Active','Inactive') AS status,
            (SELECT COUNT(*) FROM finascop_branch WHERE br_storeGroup = store_group_id) AS sg_store_count,
            (SELECT business_type_name FROM finascop_business_type WHERE business_type_id = (SELECT business_type_id FROM finascop_branch_group_business_type fb WHERE fb.store_group_id = a.store_group_id AND is_primary = 1)) AS sg_primary_bt,
            (SELECT GROUP_CONCAT(business_type_name) FROM finascop_business_type INNER JOIN finascop_branch_group_business_type  ON finascop_business_type.business_type_id = finascop_branch_group_business_type.business_type_id WHERE finascop_branch_group_business_type.store_group_id = a.store_group_id AND is_primary = 0) AS sg_additional_bt,
            (SELECT br_Name FROM finascop_branch a WHERE br_isdefaultstore = 1 AND br_storeGroup = store_group_id) as defStrGrp FROM finascop_branch_group a 
        " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'storegroupsdetailsView':

        $store_group_id = isset($_POST['store_group_id']) ? intval($_POST['store_group_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($store_group_id || $ID) {

            $data = $db->getFromDB("SELECT store_group_id,store_group_name,status AS status,(SELECT COUNT(*) FROM finascop_branch WHERE br_storeGroup = store_group_id) AS sg_store_count,
            (SELECT business_type_name FROM finascop_business_type WHERE business_type_id = (SELECT business_type_id FROM finascop_branch_group_business_type fb WHERE fb.store_group_id = a.store_group_id AND is_primary = 1) ) AS sg_primary_bt,
            (SELECT GROUP_CONCAT(business_type_name) FROM finascop_business_type INNER JOIN finascop_branch_group_business_type  ON finascop_business_type.business_type_id = finascop_branch_group_business_type.business_type_id WHERE finascop_branch_group_business_type.store_group_id = a.store_group_id AND is_primary = 0) AS sg_additional_bt,
            (SELECT br_Name FROM finascop_branch a WHERE br_isdefaultstore = 1 AND br_storeGroup = store_group_id) as defStrGrp FROM finascop_branch_group as a  WHERE store_group_id =" . $store_group_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'storegroups_form_load':

        $store_group_id = isset($_POST['store_group_id']) ? intval($_POST['store_group_id']) : 0;
        if ($store_group_id) {
            $sql = "SELECT  a.store_group_id,store_group_name,status AS comboMasterStoreGroupsStatus,(SELECT business_type_id FROM finascop_branch_group_business_type fb WHERE fb.store_group_id = a.store_group_id AND is_primary = 1) AS store_group_primary_businessType,"
                    . "(SELECT GROUP_CONCAT(business_type_id) FROM finascop_branch_group_business_type fb WHERE fb.store_group_id = a.store_group_id AND is_primary = 0) AS store_group_additional_businessType FROM finascop_branch_group a "
                    . "WHERE a.store_group_id =" . $store_group_id;
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


    case 'saveStoreGroups':
        global $db;

        $data = $_POST;
        //echo $dat;
        $branch = new \finascop\accounts\Master\brmBranch();
        $status = $branch->saveBranchgroup($data, $return_rec);
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }


        break;
}