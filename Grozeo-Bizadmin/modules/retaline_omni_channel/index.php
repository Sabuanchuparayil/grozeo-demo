<?php

require_once(ROOT . '/finascop_config/lib.php');
require_once(ROOT . '/includes/config.php');
require_once(EXTERNAL_LIBRARY_PATH);
require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(ROOT . '/classes/TextLocal.php');
global $db, $supportdb;
$ICONPATH = DEFICNPATH . '/crmc/';
switch ($op) {
    case 'checkCustomerExist':
        $customerPhone = $_POST['customerPhone'];
        if (!empty($customerPhone)) {
            $customerDetails = $db->getFromDB("SELECT cust_id,cust_mobile,cust_customer_name,defaultRole FROM retaline_customer WHERE  cust_mobile = '{$customerPhone}'", true); //defaultRole = 'user' and
            $result = $customerDetails;
        }
        if ($result['cust_id'] > 0) {
            $impersonateurl = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'IMPERSONATEURL'");
            $result['path'] = $impersonateurl . $customerPhone;
            //$result['path'] = $impersonateurl;
            $result['success'] = true;
        } else {
            $result['success'] = false;
        }
        if (!empty($result)) {
            echo json_encode($result);
        }
        break;

    case 'custDetailsView':
        $cust_id = isset($_POST['cust_id']) ? intval($_POST['cust_id']) : 0;
        if ($cust_id > 0) {
            $data = $db->getFromDB("SELECT cust_id,cust_mobile,cust_customer_name,defaultRole,cust_email,cust_walletbalance,cust_alt_phone,cust_alternate_email FROM retaline_customer  WHERE cust_id =" . $cust_id, true);
            $data['isCustomer'] = 1;
            $data['success'] = true;
        } else {
            $data['isCustomer'] = 0;
            $data['success'] = false;
        }

        echo json_encode($data);
        break;
    case 'listorders':
        $cust_id = $_POST['cust_id'];

        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'order_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $userID = $_SESSION['admin']->UserId;
        $filter_qry = "WHERE 1=1 ";

        $order = '';
        if (isset($_POST['filter'])) {
        $allowedFields = ['order_generated_id', 'member_phone', 'order_created_on', 'channel', 'order_status'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
            }
        }


        if ($sort == 'order_created_on') {
            $sort = 'order_id';
        }

        if ($cust_id > 0) {
            $filter_qry .= " AND order_customer_id = {$cust_id}";
        } else {
            $filter_qry .= " AND order_customer_id = 0";
        }

        $query = "SELECT bco.order_id,bco.order_order_id,order_packedbags_count,
bco.order_customer_id,order_branch_id,br_Name,total,
 bco.status_id AS STATUS,DATE_FORMAT(bco.created_at,'%d-%m-%Y') AS order_created_on,
 TIME_FORMAT(CAST(bco.created_at AS TIME),'%r') AS ordertime,admin_description AS order_status,
 admin_description,order_payment_gateway_refid,order_payment_gateway_refid_crc32,
CASE
    WHEN order_method = 1 THEN 'Drive Delivery'
    WHEN order_method = 2 THEN 'Customer Collect'
    WHEN order_method = 3 THEN 'Courier Delivery'
END AS order_method,
(SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS delivery_to,
(SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS cust_mobile,
            order_HasReturn,order_ItemsReturned,order_ReturnVerified,bco.created_at,
            order_latitude,order_longitude
            FROM retaline_customer_order bco
                        INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                        INNER JOIN retaline_customer_order_delivery_address bcoda ON bcoda.customer_order_id = bco.order_id
                        INNER JOIN finascop_branch ON br_ID = order_branch_id 
                        WHERE 1 = 1 AND bco.status_id > 0 ";
        $countQuery = " SELECT COUNT(*) FROM ({$query}) AS orderCount {$filter_qry} ORDER BY  {$sort} {$dir} ";
        $listQuery = "SELECT * FROM({$query}) as orderList  {$filter_qry}  ORDER BY  {$sort} {$dir} LIMIT 12";
        //CAST({$sort} as char) {$dir},binary {$sort} {$dir}



        $db->printGridJson($countQuery, $listQuery);
        break;
    case "order_details_view":
        require(THIS_MODULE_PATH . "/order_details.php");
        break;
    case 'customerAction':
        $qry = "SELECT crma_id,crma_name FROM finascop_crm_action";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        }
        break;

    case 'customerModeofAction':
        $qry = "SELECT crmm_id,crmm_name FROM finascop_crm_action_mode";
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        }
        break;
    case 'get_file_s3_details':
        $rid = $_POST['rid'];
        $data['albumBucketName'] = AWSBUCKETNAME;
        $data['accessKey'] = AWSS3ASSETUPLOADACCESSID;
        $data['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
        $data['bucketRegion'] = AWSS3ASSETUPLOADREGION;
        if ($data) {
            echo "{success: true,msg:'Saved Successfully','data':" . json_encode($data) . "}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'insertCommunicationDetails':
        $supportdb->query('begin');
        $type = $_POST['type'];
        $crc_id = $_POST['crmc_id'];
        $combo_action = $_POST['_comboSelectActionCustomers'];
        $combo_action_mode = $_POST['_comboModeOfContactCustomers'];
        $remarks = $_POST['textareaRemarksCustomers'];
        $scdate = explode('/', $_POST['datefieldSelectDateCustomers']);
        $scdate = date('Y-m-d H:i:s');
        $scremark = $_POST['textareaMarketingCustomerRemarks'];
        $bucketname = $_POST['s3_albumBucketName'];
        $filepath = $_POST['s3filepath'];
        $filename = $_POST['s3_filename'];
        $mobile = $_POST['mobile'];

        if (!empty($filepath)) {
            $file = 1;
        } else {
            $file = 0;
        }
        $communication = array(
            'entryFrom' => 1,
            'userType' => $type,
            'userId' => $crc_id,
            'entryAction' => $db->getItemFromDB("SELECT crma_name FROM finascop_crm_action WHERE crma_id = {$combo_action}"),
            'entryMode' => $db->getItemFromDB("SELECT crmm_name FROM finascop_crm_action_mode WHERE crmm_id = {$combo_action_mode}"),
            'callRemarks' => $remarks,
            'callRecords' => ($file == 1 ? $filepath : ''),
            'followupDate' => $scdate,
            'createdOn' => date('Y-m-d H:i:s'),
            'contactNumber' => $mobile,
            'jobId' => ($_POST['jobId'] > 0 ? $_POST['jobId'] : 0),
            'createdBy' => $_SESSION['admin']->Finascop_UserId
        );

        $status = $supportdb->perform('call_logs', $communication);
        $status = $supportdb->query('commit');
        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'getCommunication':

        $crcu_id = $_POST['crcu_id'];
        $rec_limit = empty($_POST['limit']) ? 8 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

        $inward_call = $ICONPATH . 'inward_call.png';
        $outward_call = $ICONPATH . 'outward_call.png';
        $inward_email = $ICONPATH . 'inward_email.png';
        $outward_email = $ICONPATH . 'outward_email.png';
        $communication_user = $ICONPATH . 'communication_user.png';
        $calender_icon = $ICONPATH . 'calender.png';

        $lead_id = $db->getItemFromDB("SELECT crle_id FROM finascop_crm_customer WHERE crcu_id={$crcu_id}");

        //$contact_id = $db->getItemFromDB("SELECT crco_id FROM finascop_crm_customer WHERE crcu_id={$crcu_id}");
        if ($lead_id > 0) {
            $contact_id = $db->getItemFromDB("SELECT crco_id FROM finascop_crm_lead WHERE crle_id ={$lead_id}");
        } else {
            $contact_id = $db->getItemFromDB("SELECT crco_id FROM finascop_crm_customer WHERE crcu_id={$crcu_id}");
        }
        if ($lead_id == 0) {
            $lead_id = 00.1;
        }
        if ($contact_id == 0) {
            $contact_id = 00.1;
        }

        $qry = "SELECT fcc.crmc_id AS id,crma_name,DATE_FORMAT(crmc_Communication_Time, '%e %M %Y %I.%i %p') AS date_and_time,crmc_remark AS remark,crmu_name AS response,
    (SELECT CONCAT(FirstName,' ',LastName) FROM finascop_usr_profile WHERE UserId=fcc.UserId) AS resource,crmm_name,fcc.crmc_id AS crmc_id,'{$communication_user}' AS communication_user, '{$calender_icon}' AS calender,
    (SELECT IF((fcc.crmu_id != 4 && fcc.crmu_id != 3),CONCAT('on',' ', DATE_FORMAT(fcs.crsc_ScheduleDate, '%e %M %Y %I.%i %p')),'')
    FROM finascop_crm_schedule fcs INNER JOIN finascop_crm_communication fcc ON fcs.crmc_id = fcc.crmc_id WHERE fcc.crmc_id = id) AS crsc_ScheduleDate
    FROM finascop_crm_action_mode fcam INNER JOIN finascop_crm_communication fcc ON  fcam.crmm_id = fcc.crcm_id 
    INNER JOIN finascop_crm_action fca ON  crma_id = crca_id 
    LEFT JOIN finascop_crm_status status ON status.crmu_id=fcc.crmu_id
    INNER JOIN finascop_crm_schedule fcs ON fcs.crmc_id=fcc.crmc_id
    WHERE fcc.crcu_id={$crcu_id} order by crmc_Communication_Time DESC";

        $countDataQuery = "SELECT count(*) from finascop_crm_communication where crcu_id = {$crcu_id}";
        $count = $db->getItemFromDB($countDataQuery);

        $items = $db->getMulipleData($qry, true);


        if (!empty($items)) {

            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getDocumentGridData':

        $crcu_id = intval($_POST['crcu_id']);

        $qry = "SELECT DATE_FORMAT(crmc_Communication_Time, '%e %M %Y %I.%i %p') AS date_and_time,
        CONCAT(FirstName,' ',LastName) AS resource,crmf_id,
        crma_name,crmf_filepath,crmf_filename,crmm_name,crmc_remark,
        RIGHT(crmf_filepath,3) AS fileextension
        FROM finascop_crm_communication_file fccf INNER JOIN finascop_crm_communication fcc ON fcc.crmc_id = fccf.crmc_id
        INNER JOIN finascop_usr_profile fup ON fup.UserId = fcc.UserId
        INNER JOIN finascop_crm_action fca ON fca.crma_id = fcc.crca_id
        INNER JOIN finascop_crm_action_mode fca ON  crmm_id = crcm_id 
        WHERE fcc.crcu_id={$crcu_id} ORDER BY crmf_id DESC LIMIT 12";

        $countDataQuery = "SELECT count(*) from finascop_crm_communication where crcu_id={$crcu_id}";
        $count = $db->getItemFromDB($countDataQuery);
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
    case 'saveretalineCustomers':
        $veri_id = $_POST['veri_id'];
        $phone = $_POST['cust_mobile'];
        $cust_customer_id = $_POST['cust_customer_id'];
        if ($veri_id > 0)
            $veri_status = $db->getItemFromDB("SELECT veri_status FROM retaline_customer_signup_verifiLog WHERE veri_id = {$veri_id}");
        if ($veri_status != 'verified') {
            echo "{success:true,valid:false,message:'Mobile not verified.'}";
            exit();
        }
        //$isPhoneDuplicated = $db->getItemFromDB("SELECT COUNT(1) FROM retaline_customer WHERE cust_mobile = " . $phone);


        /*if ($isPhoneDuplicated >= 1) {
            echo "{success:true,valid:false,message:'Mobile Phone number already exists.'}";
            exit();
        }*/
        $locs = new cgoGeoUtilities();
        $locdata = $locs->getNearestAerialRetailers($_POST['deli_longitude'], $_POST['deli_latitude'], 15);
        //$lastcust_customer_id = $db->getItemFromDB("SELECT MAX(cust_customer_id) FROM retaline_customer");
        //$cust_customer_id = $lastcust_customer_id + 1;
        $data = array(
            "cust_customer_name" => $_POST['cust_customer_name'],
            "cust_email" => $_POST['cust_email'],
            "cust_mobile" => $_POST['cust_mobile'],
            "cust_alternate_email" => $_POST['cust_alternate_email'],
            "cust_alt_phone" => $_POST['cust_alt_phone'],
            "defaultRole" => 'user',
            "cust_branch_id" => $locdata[0]['br_ID'],
            //"cust_customer_id" => $cust_customer_id,            
        );
        $db->query('begin');
        $status = $db->perform('retaline_customer', $data, 'update', " cust_customer_id = {$cust_customer_id}");
        $cust_id = $cust_customer_id;

        $deli_type = (!empty($_POST['deli_type_name']) ? $_POST['deli_type_name'] : $_POST['deli_type']);
        $deliveryInfo = array(
            "deli_customer_id" => $cust_id,
            "deli_type" => $deli_type,
            "deli_district" => $_POST['deli_district'],
            "deli_post" => $_POST['deli_post'],
            "deli_city" => $_POST['deli_district'],
            "deli_state" => $_POST['deli_state'],
            "deli_latitude" => $_POST['deli_latitude'],
            "deli_longitude" => $_POST['deli_longitude'],
            "deli_house_name" => $_POST['deli_house_name'],
            "deli_land_mark" => $_POST['deli_land_mark'],
            "deli_name" => $_POST['cust_customer_name'],
            "deli_contact_no" => $_POST['cust_mobile'],
            "deli_branch_id" => $locdata[0]['br_ID'],
            "deli_is_primary" => 1,
            "deli_created_at" => date('Y-m-d H:i:s'),
            "deli_updated_at" => date('Y-m-d H:i:s'),
            "deli_delivery_pin" => $_POST['deli_post'],
            "deli_address" => $_POST['deli_address'],
            "deli_address2" => $_POST['deli_address2'],
            "deli_status" => 'active',
            "deli_email" => $_POST['cust_email']
        );
        $status = $db->perform('retaline_customer_delivery_info', $deliveryInfo);

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ' }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
    case 'getCategoryStructure':
        $BtGroupName = $db->getMultipleData("SELECT business_type_id,business_type_name FROM finascop_business_type WHERE status = 1 ORDER BY business_type_name ASC", true);
        if (!empty($BtGroupName)) {
            $BtGroupName_node = array();
            foreach ($BtGroupName as $idBT => $valueBT) {
                $BtGroupName_node[$idBT] = array();
                $BtGroupName_node[$idBT]['id'] = 'L1_' . $valueBT['business_type_id'];
                $BtGroupName_node[$idBT]['text'] = $valueBT['business_type_name'];
                $BtGroupName_node[$idBT]['draggable'] = false;
                $BtGroupName_node[$idBT]['children'] = '';
                $BtGroupName_node[$idBT]['cls'] = 'bt_nature_group';

                $NatGroupName = $db->getMultipleData("SELECT parent_category_id,parent_category FROM mypha_productparent_category WHERE status = 1 AND parent_category_businessType = {$valueBT['business_type_id']} ORDER BY parent_category ASC", true);
                if (!empty($NatGroupName)) {
                    $BtGroupName_node[$idBT]['leaf'] = false;
                    $NatGroupName_node = array();
                    foreach ($NatGroupName as $idp => $value) {
                        $NatGroupName_node[$idp] = array();
                        $NatGroupName_node[$idp]['id'] = 'L2_' . $value['parent_category_id'];
                        $NatGroupName_node[$idp]['text'] = $value['parent_category'];
                        $NatGroupName_node[$idp]['draggable'] = false;
                        $NatGroupName_node[$idp]['children'] = '';
                        $NatGroupName_node[$idp]['cls'] = 'pc_nature_group';

                        $GroupName = $db->getMultipleData("SELECT category_id,category_name FROM mypha_productcategory WHERE status = '1' AND  parent_category = '{$value['parent_category_id']}'
				ORDER BY category_name", true);

                        if (!empty($GroupName)) {
                            $NatGroupName_node[$idp]['leaf'] = false;
                            $BranchLedgerName_node = array();
                            foreach ($GroupName as $idl => $values) {
                                $BranchLedgerName_node[$idl] = array();
                                $BranchLedgerName_node[$idl]['id'] = 'L3_' . $values['category_id'];
                                $BranchLedgerName_node[$idl]['text'] = $values['category_name'];
                                //  $BranchLedgerName_node[$idl]['leaf'] = false;
                                $BranchLedgerName_node[$idl]['draggable'] = true;
                                $BranchLedgerName_node[$idl]['children'] = '';
                                $BranchLedgerName_node[$idl]['cls'] = 'cat_group';

                                $Ledgertypename = $db->getMultipleData("SELECT sub_category_id,sub_category FROM mypha_productsubcategory WHERE status = 1 AND  main_category = '{$values['category_id']}'
						ORDER BY sub_category", true);

                                if (!empty($Ledgertypename)) {
                                    $BranchLedgerName_node[$idl]['leaf'] = false;
                                    $Ledgertypename_node = array();
                                    foreach ($Ledgertypename as $ld => $Ledgertypename_values) {
                                        $Ledgertypename_node[$ld] = array();
                                        $Ledgertypename_node[$ld]['id'] = 'L4_' . $Ledgertypename_values['sub_category_id'];
                                        $Ledgertypename_node[$ld]['text'] = $Ledgertypename_values['sub_category'];
                                        $Ledgertypename_node[$ld]['leaf'] = true;
                                        $Ledgertypename_node[$ld]['draggable'] = true;
                                        $Ledgertypename_node[$ld]['children'] = '';
                                        $Ledgertypename_node[$ld]['cls'] = 'subcat_type';
                                    }

                                    /*   if ($values['Group_ID'] == 23) {
                                      print_r($Ledgertypename_node);
                                      exit;
                                      } */

                                    $BranchLedgerName_node[$idl]['children'] = $Ledgertypename_node;
                                    $Ledgertypename_node = array();
                                    $ledger_name_node = array();
                                } else {
                                    $BranchLedgerName_node[$idl]['leaf'] = true;
                                    $BranchLedgerName_node[$idl]['children'] = array();
                                }
                            }
                            $NatGroupName_node[$idp]['children'] = $BranchLedgerName_node;
                            $BranchLedgerName_node = array();
                        } else {
                            $NatGroupName_node[$idp]['leaf'] = true;
                            $NatGroupName_node[$idp]['children'] = array();
                        }
                    }
                    $BtGroupName_node[$idBT]['children'] = $NatGroupName_node;
                    $NatGroupName_node = array();
                } else {
                    $BtGroupName_node[$idBT]['leaf'] = true;
                    $BtGroupName_node[$idBT]['children'] = array();
                }
            }
        }

        echo json_encode($BtGroupName_node);
        break;
    case 'listItemsofSubcat':
        $rec_limit = empty($_POST['limit']) ? 18 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $search = " WHERE 1=1 ";

        $search .= " AND product_category = " . intval($_POST['subcategoryId']) . "";
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
        if ($_POST['subcategoryId'] > 0) {
            $countQuery = "SELECT count(*) from  finascop_stock_itemmaster {$search} order by stit_ID";
            $count = $db->getItemFromDB($countQuery);

            $qry = "SELECT  stit_ID,stit_SKU,stit_itemName,stit_brand_name,sub_category,category_name,mypha_productparent_category.parent_category AS deptName,business_type_name FROM finascop_stock_itemmaster "
                . "INNER JOIN mypha_productsubcategory ON sub_category_id = product_category INNER JOIN mypha_productcategory ON category_id = main_category "
                . "INNER JOIN mypha_productparent_category ON parent_category_id = mypha_productcategory.parent_category "
                . "INNER JOIN finascop_business_type ON  business_type_id = parent_category_businessType {$search} order by stit_ID desc  ";
            $data = $db->getMultipleData($qry, true);
        } else {
            $count = 0;
            $data = [];
        }


        echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        break;
    case 'kaleyeraClicktoCall':
        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'KALYERAURL'");
        $kalayeraApi = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'KALYERAAPIKEY'");
        $kalayeraNum = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'KALYERANUMBER'");
        $kalayeraSid = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'KAYERASID'");
        $callurl = $url . 'v1/' . $kalayeraSid . '/voice/click-to-call';
        $fields = array(
            "from" => $kalayeraNum, //$kalayeraNum,
            "to" => $_POST['phone'],
            "bridge" => $kalayeraNum,
            "prefix" => 91,
        );
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'api-key:' . $kalayeraApi
        );
        $fields_string = http_build_query($fields, '', '&');
        $opts = array(
            CURLOPT_URL => $callurl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => $headers
        );
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        curl_close($ch);
        echo $data;

        break;
    case 'kaleyeraOutCall':
        $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'KALYERAURL'");
        $kalayeraApi = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'KALYERAAPIKEY'");
        $kalayeraNum = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'KALYERANUMBER'");
        $kalayeraSid = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'KAYERASID'");
        $callurl = $url . 'v1/' . $kalayeraSid . '/voice/outbound';
        $target[]["message"] = array(
            "language" => "en-US",
            "speed" => "medium",
            "text" => "Welcome , its from Retaline Support"
        );
        $target = json_encode($target);
        $fields = array(
            "to" => $_POST['phone'],
            "bridge" => $kalayeraNum,
            "retry" => 1,
            "prefix" => 91,
            "target" => $target
        );
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'api-key:' . $kalayeraApi
        );
        $fields_string = http_build_query($fields, '', '&');
        $opts = array(
            CURLOPT_URL => $callurl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => $headers
        );
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        curl_close($ch);
        echo $data;

        break;
    case 'checkMerchantExist':
        $customerPhone = $_POST['customerPhone'];
        $fields['phone'] = $customerPhone;
        $store_group = $db->getFromDB("SELECT store_group_id,store_group_name,siteUrl,
            IF(store_group_grosmartMerchant = 1,'YES','NO') AS store_group_grosmartMerchant,1 as isCustomer,br_Phone,br_Email,contactNumber 
            FROM finascop_branch_group INNER JOIN finascop_branch ON br_storeGroup = store_group_id  WHERE br_Phone LIKE '%{$customerPhone}%' LIMIT 1", true);

        if ($store_group['store_group_id'] > 0) {
            $impersonateurl = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'IMPERSONATEURL_MERCHANT'");
            $data['cust_customer_name'] =  $store_group['store_group_name'];
            $data['cust_mobile'] =  $store_group['contactNumber'] . '-' . $store_group['br_Phone'];
            $data['cust_id'] = $store_group['store_group_id'];
            $data['partnerId'] = $store_group['store_group_id'];
            $data['path'] = $impersonateurl . $store_group['store_group_id'];
            $data['success'] = true;
        } else {
            $data['success'] = false;
        }
        if (!empty($data)) {
            echo json_encode($data);
        }


        /*$url =  $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'MERCHANT_DETAILS'");
        $fields_string = json_encode($fields);
        $opts = array(
            CURLOPT_URL => $url,
            //CURLINFO_CONTENT_TYPE => "application/json",
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_BINARYTRANSFER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => count($fields),
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $datacl = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        header("Content-Type: application/json");
        $result = json_decode($datacl, true);
        if ($result['result'] == 1 && $result['status'] == 'Success') {
            $data['cust_customer_name'] =  $result['data'][0]['FullName'];
            $data['cust_mobile'] =  $result['data'][0]['Mobile'];
            $data['cust_id'] = $result['data'][0]['StoreGroupId'];
            $data['partnerId'] = $result['data'][0]['Id'];
            $impersonateurl = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'IMPERSONATEURL_MERCHANT'");
            // $data['path'] = $impersonateurl . $customerPhone;
            $data['path'] = $impersonateurl . $data['partnerId'];
            $data['success'] = true;
        } else {
            $data['success'] = false;
        }
        if (!empty($data)) {
            echo json_encode($data);
        }*/
        break;
    case 'merchantDetailsView':
        $cust_id = isset($_POST['cust_id']) ? intval($_POST['cust_id']) : 0;
        $partnerId = isset($_POST['partnerId']) ? intval($_POST['partnerId']) : 0;
        if ($cust_id > 0) {
            $data = $db->getFromDB("SELECT store_group_id,store_group_name,siteUrl,
            IF(store_group_grosmartMerchant = 1,'YES','NO') AS store_group_grosmartMerchant,1 as isCustomer,br_Phone,br_Email
            FROM finascop_branch_group INNER JOIN finascop_branch ON br_storeGroup = store_group_id  WHERE store_group_id = {$cust_id} GROUP BY br_storeGroup", true);
            $data['partnerId'] = $partnerId;
            $data['isCustomer'] = 1;
            $data['success'] = true;
        } else {
            $data['isCustomer'] = 0;
            $data['success'] = false;
        }

        echo json_encode($data);
        break;
    case 'listMerchantorders':
        $merchantId = $_POST['merchantId'];

        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'order_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $comparisons = array('gt' => '>', 'lt' => '<', 'eq' => '=');
        $userID = $_SESSION['admin']->UserId;
        $filter_qry = "WHERE 1=1 ";

        $order = '';
        if (isset($_POST['filter'])) {
        $allowedFields = ['order_generated_id', 'member_phone', 'order_created_on', 'channel', 'order_status'];
        $filter_qry .= buildSafeFilterQuery($_POST['filter'], $allowedFields, $db);
    }
            }
        }


        if ($sort == 'order_created_on') {
            $sort = 'order_id';
        }

        if ($merchantId > 0) {
            $filter_qry .= " AND storegroup_id = {$merchantId} OR order_branch_id  IN (SELECT GROUP_CONCAT(br_ID) FROM finascop_branch where br_storeGroup = {$merchantId})";
        } else {
            $filter_qry .= " AND storegroup_id = -1";
        }

        $query = "SELECT bco.order_id,bco.order_order_id,order_packedbags_count,bco.storegroup_id,
    bco.order_customer_id,order_branch_id,br_Name,total,
     bco.status_id AS STATUS,DATE_FORMAT(bco.created_at,'%d-%m-%Y') AS order_created_on,
     TIME_FORMAT(CAST(bco.created_at AS TIME),'%r') AS ordertime,admin_description AS order_status,
     admin_description,order_payment_gateway_refid,order_payment_gateway_refid_crc32,
    CASE
        WHEN order_method = 1 THEN 'Drive Delivery'
        WHEN order_method = 2 THEN 'Customer Collect'
        WHEN order_method = 3 THEN 'Courier Delivery'
    END AS order_method,
    (SELECT cust_customer_name FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS delivery_to,
    (SELECT cust_mobile FROM `retaline_customer` WHERE cust_id = bco.order_customer_id) AS cust_mobile,
                order_HasReturn,order_ItemsReturned,order_ReturnVerified,bco.created_at,
                order_latitude,order_longitude
                FROM retaline_customer_order bco
                            INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id  
                            INNER JOIN retaline_customer_order_delivery_address bcoda ON bcoda.customer_order_id = bco.order_id
                            INNER JOIN finascop_branch ON br_ID = order_branch_id 
                            WHERE 1 = 1 AND bco.status_id > 0 ";
        $countQuery = " SELECT COUNT(*) FROM ({$query}) AS orderCount {$filter_qry} ORDER BY  {$sort} {$dir} LIMIT 12";
        $listQuery = "SELECT * FROM({$query}) as orderList  {$filter_qry}  ORDER BY  {$sort} {$dir} ";
        //CAST({$sort} as char) {$dir},binary {$sort} {$dir}



        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'outboundCall':
        $jobId = $_POST['jobId'];
        $phone = $_POST['phone'];
        if ($jobId > 0)
            $jobDetails = $supportdb->getFromDB("SELECT eventId,jobTitle,eventName,callType,orderRefrenceId FROM outbound_jobs oj INNER JOIN support_user_events sue on sue.id = oj.eventId where oj.id = {$jobId}", true);

        switch ($jobDetails['callType']) {
            case 3:
                $order_branch_id = $db->getItemFromDb("SELECT order_branch_id FROM retaline_customer_order WHERE order_order_id = '{$jobDetails['orderRefrenceId']}'");
                $phone = $db->getItemFromDB("SELECT br_Phone FROM finascop_branch WHERE br_ID = {$order_branch_id}");
                break;
            default:
                $phone = $phone;
                break;
        }
        $agentID = $db->getItemFromDB("SELECT agentID FROM finascop_user_details WHERE UserId = {$_SESSION['admin']->UserId} ");
        if (empty($agentID)) {
            echo "{success: false,msg:'Check your agent id to proceed the call.'}";
            exit();
        }
        $uniqueId = getNewFinascopApiKey();
        switch (CALLCENTER) {
            case 'OZONTEL':
                $response = ozontelCallcenter($agentID, $phone, $uniqueId);
                if ($response['success'] == true) {
                    if (!empty($response['ucid'])) {
                        $jrecData['jobId'] = ($jobId > 0 ? $jobId : 0);
                        $jrecData['uui'] = $uniqueId;
                        $jrecData['ucid'] = $response['ucid'];
                        $status = $supportdb->perform('call_recordings', $jrecData);
                    }
                }
                $outputString = str_replace('<status>', '', $response);
                $outputString = str_replace('</status>', '', $outputString);
                $outputString = trim($outputString) . 'Calling -' . $phone;
                echo "{success: true,msg:'{$outputString}'}";
                break;
            case 'VOXBAY':
                $response = voxbayCallcenter($phone, $agentID);
                //$response = json_decode($response, true);
                if ($response == '{"Success"}') {
                    $outputString = 'Calling -' . $phone;
                    $jrecData['jobId'] = ($jobId > 0 ? $jobId : 0);
                    $jrecData['uui'] = $uniqueId;
                    //$jrecData['ucid'] = $response['ucid'];
                    $status = $supportdb->perform('call_recordings', $jrecData);
                    echo "{success: true,msg:'" . $outputString . "'}";
                } else {
                    echo "{success: false,msg:'" . $response . "'}";
                }

                break;
        }


        break;
    case 'callAction':
        $qry = "SELECT id,name FROM call_actions";
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            echo json_encode($items);
        }
        break;
    case 'insertCallLogDetails':
        $supportdb->query('begin');
        $jobId = $_POST['jobId'];
        $type = $_POST['type'];
        $followupDateTime = date('Y-m-d', strtotime($_POST['followupDate'])) . ' ' . date('H:i', strtotime($_POST['followupTime']));
        //$followupDateTime = $_POST['followupDate'].' '.$_POST['followupTime'];
        $jobData = $supportdb->getFromDB("SELECT eventId,calleeName,calleeMobile,calleeType,status FROM outbound_jobs WHERE id = {$jobId}", true);
        $callAction = $supportdb->getItemFromDB("SELECT id FROM call_actions WHERE name = '{$type}'");
        $userType = $supportdb->getItemFromDB("SELECT id FROM support_applicable_users WHERE name = '{$_POST['userType']}'");
        $data['callAction'] = $callAction;
        $data['userId'] = $_POST['userId'];
        $data['userType'] = $userType;
        $data['callRemarks'] = $_POST['callRemarks'];
        $data['jobId'] = $jobId;
        $data['entryFrom'] = 2;
        if (!empty($_POST['followupDate'])) {
            $data['followupDate'] = date('Y-m-d H:i', strtotime($followupDateTime));
            $eventdata['followupDate'] = date('Y-m-d H:i', strtotime($followupDateTime));
        }
        $data['createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $supportdb->perform('call_logs', $data);
        $callLogId = $supportdb->insert_id();
        switch ($callAction) {
            case 1:
                if ($jobData['eventId'] == 1) {
                    //$streogroupId = $_POST['partnerId'];
                    $streogroupId = $_POST['userId'];
                    $url =  $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'ONBOARDING_JOBS'");
                    $url = $url . "?pendingOnly=0&streogroupId=" . $streogroupId;
                    $fields_string = json_encode($fields);
                    $opts = array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                    );

                    $ch = curl_init();
                    curl_setopt_array($ch, $opts);
                    $datacl = curl_exec($ch);
                    $info = curl_getinfo($ch);
                    curl_close($ch);

                    header("Content-Type: application/json");
                    $result = json_decode($datacl, true);
                    $sgPending = $result['data'][0];
                    if ($sgPending['Products'] == 0 || $sgPending['BankAccounts'] == 0 || $sgPending['BankAccountLinkedToStores'] == 0) {
                        $eventdata['status'] = 1;
                    } else {
                        switch ($sgPending['TenantType']) {
                            case 1:
                                if ($sgPending['GSTNotVerified'] == 0) {
                                    $eventdata['status'] = 3;
                                    $eventdata['completedOn'] = date('Y-m-d H:i:s');
                                } else {
                                    $eventdata['status'] = 1;
                                }
                                break;
                            case 2:
                                $eventdata['status'] = 3;
                                $eventdata['completedOn'] = date('Y-m-d H:i:s');
                                break;
                        }
                    }
                } else {
                    $eventdata['status'] = 3;
                    $eventdata['completedOn'] = date('Y-m-d H:i:s');
                }
                break;
            case 2:
                $eventdata['status'] = 1;
                break;
            case 3:
                $eventdata['status'] = 1;
                break;
        }
        if (count($eventdata) > 0) {
            $eventdata['assignedTo'] = 0;
            $eventdata['callerName'] = '';
            $status = $supportdb->perform('outbound_jobs', $eventdata, 'update', " id = {$jobId}");

            $joblog['actionBy'] = $_SESSION['admin']->Finascop_UserId;
            $joblog['actionOn'] = date('Y-m-d H:i:s');
            $joblog['actionRemark'] = 'Call ' . $type;
            $joblog['jobId'] = $jobId;
            $joblog['callLogId'] = $callLogId;
            $status = $supportdb->perform('outbound_jobs_log', $joblog);
        }
        $status = $supportdb->query('commit');
        if ($callAction == 1 && !empty($_POST['alternateNo'])) {
            $conactdet['userType'] = $userType;
            $caleeId = $supportdb->getItemFromDB("SELECT calleeId FROM outbound_jobs WHERE id = {$jobId}");
            $conactdet['userId'] = $caleeId;
            $conactdet['contactNo'] = $_POST['alternateNo'];
            $conactdet['createdOn'] = date('Y-m-d H:i:s');
            $conactdet['createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $isExists = $db->getItemFromDB("SELECT COUNT(*) FROM contact_preference 
            WHERE userType = {$userType} AND  userId = {$caleeId} AND contactNo = '{$conactdet['contactNo']}'");
            if ($isExists == 0)
                $status = $db->perform('contact_preference', $conactdet);
            if ($eventdata['status'] != 3) {
                $altnodata['calleeMobile'] = $_POST['alternateNo'];
                $status = $supportdb->perform('outbound_jobs', $altnodata, 'update', " id = {$jobId}");
            }
        }


        if ($status) {
            echo "{success: true,msg:'Call status updated and details saved successfully.'}";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
    case 'getCallLogGridData':
        $userId = intval($_POST['userId']);
        //$userType = $supportdb->getItemFromDB("SELECT id FROM support_applicable_users WHERE name = '{$_POST['userType']}'");
        $userType = $_POST['userType'];

        $qry = "SELECT fccf.id,userId,userType,followupDate,callRemarks,callRecords,createdOn,createdBy,
        fcc.name AS callActionName FROM call_logs fccf 
        INNER JOIN call_actions fcc ON fcc.id = fccf.callAction 
        INNER JOIN support_applicable_users fup ON fup.id = fccf.userType 
        WHERE userId= {$userId} AND userType = {$userType} order by fccf.id DESC LIMIT 12";

        $countDataQuery = "SELECT count(*) from call_logs WHERE userId= {$userId} AND userType = {$userType} ";
        $count = $supportdb->getItemFromDB($countDataQuery);
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            for ($i = 0; $i < count($items); $i++) {
                $items[$i]['callInitiatedBy'] = $db->getItemFromDB("SELECT CONCAT(FirstName,'',LastName) FROM finascop_usr_profile WHERE UserId = {$items[$i]['createdBy']}");
            }
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
    case 'getTicketGridData':
        $phone = intval($_POST['phone']);
        $userType = $_POST['userType'];


        $qry = "SELECT ticketId,ticketNumber,ticketDescription,createdOn,name  as ticketStatusName,ticketStatus,CASE WHEN createdFrom=1 THEN 'Back Office' WHEN createdFrom =2 THEN 'BA' WHEN createdFrom =3 THEN 'RO' END AS createdFrom,createdBy,
        DATE_FORMAT(createdOn,'%d-%m-%Y %H:%i:%s') AS createdOn,
        CASE WHEN createdFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) WHEN createdFrom =2 THEN (SELECT baName FROM business_associate WHERE id = createdBy) WHEN createdFrom =3 THEN (SELECT roName FROM relationship_officer WHERE id = createdBy) END AS ticketOwner
        FROM support_ticket fccf INNER JOIN support_ticket_status fcc ON fcc.id = fccf.ticketStatus
        WHERE ticketContactNo = {$phone}  ORDER BY ticketId DESC LIMIT 12";

        $countDataQuery = "SELECT count(*) from support_ticket fccf INNER JOIN support_ticket_status fcc ON fcc.id = fccf.ticketStatus
        WHERE ticketContactNo = {$phone} ";
        $count = $db->getItemFromDB($countDataQuery);
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getRecordingGridDataNosql':
        $mobile = $_POST['phone'];
        if (!empty($mobile)) {
            $nodb = new \cgoDynamiteDB();
            $today = date("Ymd");
            $arrOrder = array();
            $arrOrder['PartitionKey'] = array('col' => 'CallerID', 'val' => (string) $mobile, 'oper' => '=');
            $arrOrder['IndexName'] = 'CallerID-index';
            $arrOrder['queryAttributes'] = array('uuid', 'StartTime', 'Duration', 'DialStatus', 'CustomerStatus', 'AudioFile', 'AgentName', 'createddatetime');

            $response = array();
            $rsno = $nodb->query('SupportCallLog', $arrOrder, 'query');
            if (isset($rsno) && count($rsno) > 0) {
                foreach ($rsno as $callLog) {
                    array_push($response, array(
                        'StartTime' => $callLog['StartTime'],
                        'Duration' => $callLog['Duration'],
                        'uuid' => $callLog['uuid'],
                        'DialStatus' => $callLog['DialStatus'],
                        'CustomerStatus' => $callLog['CustomerStatus'],
                        'createddatetime' => $callLog['createddatetime'],
                        'AudioFile' => $callLog['AudioFile'],
                        'AgentName' => $callLog['AgentName'],
                    ));
                }
                $response = orderBy($response, 'createddatetime');
            }
        }
        if (count($response) > 0) {
            echo '{"totalCount":' . count($response) . ',"data":' . json_encode($response) . '}';
        } else {
            echo '{"totalCount":0,"data":[]}';
        }

        break;
    case 'getRecordingGridData':
        $mobile = $_POST['phone'];
        if (!empty($mobile)) {
            $qry = "SELECT id,StartTime, Duration, DialStatus, CustomerStatus, AudioFile, AgentName FROM call_communications
        WHERE CallerID = '{$mobile}' ";

            $countDataQuery = "SELECT count(*) from call_communications  WHERE CallerID = '{$mobile}' ";
            $count = $db->getItemFromDB($countDataQuery);
            $items = $db->getMulipleData($qry, true);
            if (!empty($items)) {
                echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
            } else
                echo '{"totalCount":"0","data":[]}';
        }

        break;
    case 'getCustomerGridData':
        $merchantId = intval($_POST['merchantId']);
        $qry = "SELECT cust_id,cust_mobile,cust_email,cust_customer_name FROM retaline_customer  
        INNER JOIN retaline_customer_order ON order_customer_id = cust_id 
        INNER JOIN finascop_branch fb ON order_branch_id=fb.br_ID
        WHERE retaline_customer_order.status_id > 0 AND retaline_customer_order.storegroup_id = {$merchantId} LIMIT 12";

        $countDataQuery = "SELECT count(*) from retaline_customer  
        INNER JOIN retaline_customer_order ON order_customer_id = cust_id 
        INNER JOIN finascop_branch fb ON order_branch_id=fb.br_ID
        WHERE retaline_customer_order.status_id > 0 AND retaline_customer_order.storegroup_id = {$merchantId} ";
        $count = $db->getItemFromDB($countDataQuery);
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
    case 'merchantTabDetailsView':
        $cust_id = isset($_POST['cust_id']) ? intval($_POST['cust_id']) : 0;
        $pkid = isset($_POST['pkid']) ? intval($_POST['pkid']) : 0;
        $tabType = $_POST['tabType'];
        if ($cust_id > 0) {
            switch ($tabType) {
                case 'calllog':
                    if ($pkid > 0) {
                        $data = $supportdb->getFromDB("SELECT fccf.id,userId,userType,DATE_FORMAT(followupDate, '%d-%m-%y %H:%i %p') AS followupDate,callRemarks,
                        callRecords,createdOn,createdBy,fcc.name AS callActionName FROM call_logs fccf 
                        INNER JOIN call_actions fcc ON fcc.id = fccf.callAction 
                        INNER JOIN support_applicable_users fup ON fup.id = fccf.userType 
                        WHERE fccf.id = {$pkid}", true);
                        $data['callInitiatedBy'] = $db->getItemFromDB("SELECT CONCAT(FirstName,'',LastName) FROM finascop_usr_profile WHERE UserId = {$data['createdBy']}");
                    }
                    $data['tabId'] = 1;
                    $data['success'] = true;
                    break;
                case 'ticket':
                    if ($pkid > 0) {
                        $data = $db->getFromDB("SELECT ticketTitle,ticketNumber,ticketDescription,name  as ticketStatusName,ticketContactNo,ticketStatus,
                        (SELECT typeName FROM support_type WHERE typeId = ticketSupTypeId) AS ticketSupTypeName,ticketSupTypeId,
                        ticketSuId,(SELECT suName FROM support_unit WHERE suId = ticketSuId) AS ticketSuName,
                        CASE WHEN createdFrom=1 THEN 'Back Office' WHEN createdFrom =2 THEN 'BA' WHEN createdFrom =3 THEN 'RO' END AS createdFrom,createdBy,
                        createdOn,CASE WHEN createdFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) 
                        WHEN createdFrom =2 THEN (SELECT baName FROM business_associate WHERE id = createdBy) 
                        WHEN createdFrom =3 THEN (SELECT roName FROM relationship_officer WHERE id = createdBy) END AS ticketOwner,
                        (SELECT filepath FROM support_ticket_log WHERE support_ticket_log.ticketId = fccf.ticketId AND filepath <> '') as filepath,
                        (SELECT filename FROM support_ticket_log WHERE support_ticket_log.ticketId = fccf.ticketId AND filename <> '') as filename
                    FROM support_ticket fccf INNER JOIN support_ticket_status fcc ON fcc.id = fccf.ticketStatus
                    WHERE ticketId = {$pkid}", true);
                    }

                    $data['tabId'] = 2;
                    $data['success'] = true;
                    break;
                case 'communicationOld':
                    if ($pkid > 0) {
                        $data = $db->getFromDB("SELECT fcc.crmc_id AS id,crma_name,DATE_FORMAT(crmc_Communication_Time, '%e %M %Y %I.%i %p') AS date_and_time,crmc_remark AS remark,crmu_name AS response,
                        (SELECT CONCAT(FirstName,' ',LastName) FROM finascop_usr_profile WHERE UserId=fcc.UserId) AS resource,crmm_name,fcc.crmc_id AS crmc_id,'{$communication_user}' AS communication_user, '{$calender_icon}' AS calender,
                        (SELECT IF((fcc.crmu_id != 4 && fcc.crmu_id != 3),CONCAT('on',' ', DATE_FORMAT(fcs.crsc_ScheduleDate, '%e %M %Y %I.%i %p')),'')
                        FROM finascop_crm_schedule fcs INNER JOIN finascop_crm_communication fcc ON fcs.crmc_id = fcc.crmc_id WHERE fcc.crmc_id = id) AS crsc_ScheduleDate
                        FROM finascop_crm_action_mode fcam INNER JOIN finascop_crm_communication fcc ON  fcam.crmm_id = fcc.crcm_id 
                        INNER JOIN finascop_crm_action fca ON  crma_id = crca_id 
                        LEFT JOIN finascop_crm_status status ON status.crmu_id=fcc.crmu_id
                        INNER JOIN finascop_crm_schedule fcs ON fcs.crmc_id=fcc.crmc_id
                        WHERE fcc.crmc_id = {$pkid}", true);
                    }

                    $data['tabId'] = 3;
                    $data['success'] = true;
                    break;
                case 'communication':
                    if ($pkid > 0) {
                        $data = $supportdb->getFromDB("SELECT fccf.id,userId,userType,followupDate,callRemarks,callRecords,createdOn,createdBy,
                        fcc.name AS callActionName,entryFrom,entryAction,entryMode,contactNumber,
                        CASE WHEN entryFrom = 1 THEN 'Communication' 
                            WHEN entryFrom = 2 THEN 'Call Log' 
                            WHEN entryFrom = 3 THEN 'Call Recording' END AS entryFromName 
                            FROM call_logs fccf 
                        LEFT JOIN call_actions fcc ON fcc.id = fccf.callAction 
                        INNER JOIN support_applicable_users fup ON fup.id = fccf.userType 
                        WHERE fccf.id = {$pkid}", true);
                        $data['filename'] = basename($data['callRecords']);
                        $data['callInitiatedBy'] = $db->getItemFromDB("SELECT CONCAT(FirstName,'',LastName) FROM finascop_usr_profile WHERE UserId = {$data['createdBy']}");
                    }

                    $data['tabId'] = 3;
                    $data['success'] = true;
                    break;
                case 'document':
                    if ($pkid > 0) {
                        $data = $db->getFromDB("SELECT DATE_FORMAT(crmc_Communication_Time, '%e %M %Y %I.%i %p') AS date_and_time,
                        CONCAT(FirstName,' ',LastName) AS resource,crmf_id,
                        crma_name,crmf_filepath,crmf_filename,crmm_name,
                        RIGHT(crmf_filepath,3) AS fileextension
                        FROM finascop_crm_communication_file fccf INNER JOIN finascop_crm_communication fcc ON fcc.crmc_id = fccf.crmc_id
                        INNER JOIN finascop_usr_profile fup ON fup.UserId = fcc.UserId
                        INNER JOIN finascop_crm_action fca ON fca.crma_id = fcc.crca_id
                        INNER JOIN finascop_crm_action_mode fca ON  crmm_id = crcm_id
                    WHERE crmf_id = {$pkid}", true);
                    }

                    $data['tabId'] = 4;
                    $data['success'] = true;
                    break;
                case 'recordings':
                    if ($pkid > 0) {
                    }
                    $data['tabId'] = 5;
                    $data['success'] = true;
                    break;
            }
        } else {
            $data['tabId'] = 0;
            $data['success'] = false;
        }

        echo json_encode($data);
        break;
    case 'loadOrdersite':
        $order_id = $_POST['order_id'];
        $oderDetail = $db->getFromDB("SELECT order_branch_id,storegroup_id,order_customer_id FROM retaline_customer_order WHERE order_id = {$order_id}", true);
        if ($oderDetail['storegroup_id'] > 0) {
            $sgurl = $db->getItemFromDB("SELECT siteUrl FROM finascop_branch_group WHERE store_group_id = {$oderDetail['storegroup_id']}");
        } else {
            $sgurl = $db->getItemFromDB("SELECT siteUrl FROM finascop_branch_group WHERE store_group_id = (SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$oderDetail['order_branch_id']})");
        }
        if ($sgurl) {
            $sgurl = 'https://' . $sgurl . '/impersonate/' . $oderDetail['order_customer_id'] . '/1';
            echo "{success: true,'data':'" . $sgurl . "'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data'}";
        }
        break;
    case 'getStoreSearchGridData':
        $searchName = $_POST['searchName'];
        $qry = "SELECT store_group_id,store_group_name,siteUrl,br_Phone,br_Email,br_State,
        (SELECT st_name FROM finascop_state WHERE st_ID = br_State) as stateName
        FROM finascop_branch_group INNER JOIN finascop_branch ON br_storeGroup = store_group_id  WHERE store_group_name LIKE '%{$searchName}%' GROUP BY br_storeGroup";

        $countDataQuery = "SELECT count(*) FROM finascop_branch_group INNER JOIN finascop_branch ON br_storeGroup = store_group_id  WHERE store_group_name LIKE '%{$searchName}%' GROUP BY br_storeGroup";
        $count = $db->getItemFromDB($countDataQuery);
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
    case 'getCustomerSearchGridData':
        $searchName = $_POST['searchName'];
        $qry = "SELECT cust_id,cust_mobile,cust_customer_name,cust_walletbalance FROM retaline_customer WHERE cust_customer_name LIKE '%{$searchName}%' ";

        $countDataQuery = "SELECT count(*) FROM retaline_customer WHERE cust_customer_name LIKE '%{$searchName}%' ";
        $count = $db->getItemFromDB($countDataQuery);
        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
    case 'getAllCommunications':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = 'fccf.id';
        $dir = empty($dir) ? 'ASC' : $dir;
        $userId = intval($_POST['userId']);
        //$userType = $supportdb->getItemFromDB("SELECT id FROM support_applicable_users WHERE name = '{$_POST['userType']}'");
        $userType = $_POST['userType'];

        $qry = "SELECT fccf.id,userId,userType,followupDate,callRemarks,callRecords,createdOn,createdBy,
            fcc.name AS callActionName,entryFrom,entryAction,entryMode,contactNumber,
            CASE WHEN entryFrom = 1 THEN 'Communication' 
                            WHEN entryFrom = 2 THEN 'Call Log' 
                            WHEN entryFrom = 3 THEN 'Call Recording' END AS entryFromName FROM call_logs fccf 
            LEFT JOIN call_actions fcc ON fcc.id = fccf.callAction 
            INNER JOIN support_applicable_users fup ON fup.id = fccf.userType 
            WHERE userId= {$userId} AND userType = {$userType} order by fccf.id DESC LIMIT {$rec_start},{$rec_limit}";

        $countDataQuery = "SELECT count(*) from call_logs WHERE userId= {$userId} AND userType = {$userType} ";
        $count = $supportdb->getItemFromDB($countDataQuery);
        $items = $supportdb->getMulipleData($qry, true);
        if (!empty($items)) {
            for ($i = 0; $i < count($items); $i++) {
                $items[$i]['callInitiatedBy'] = $db->getItemFromDB("SELECT CONCAT(FirstName,'',LastName) FROM finascop_usr_profile WHERE UserId = {$items[$i]['createdBy']}");
            }
            echo '{"totalCount":' . $count . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
    case 'customerTabDetailsView':
        $cust_id = isset($_POST['cust_id']) ? intval($_POST['cust_id']) : 0;
        $pkid = isset($_POST['pkid']) ? intval($_POST['pkid']) : 0;
        $tabType = $_POST['tabType'];
        if ($cust_id > 0) {
            switch ($tabType) {
                case 'ticket':
                    if ($pkid > 0) {
                        $data = $db->getFromDB("SELECT ticketTitle,ticketNumber,ticketDescription,name  as ticketStatusName,ticketContactNo,ticketStatus,
                            (SELECT typeName FROM support_type WHERE typeId = ticketSupTypeId) AS ticketSupTypeName,ticketSupTypeId,
                            ticketSuId,(SELECT suName FROM support_unit WHERE suId = ticketSuId) AS ticketSuName,
                            CASE WHEN createdFrom=1 THEN 'Back Office' WHEN createdFrom =2 THEN 'BA' WHEN createdFrom =3 THEN 'RO' END AS createdFrom,createdBy,
                            createdOn,CASE WHEN createdFrom=1 THEN (SELECT FirstName FROM finascop_usr_profile WHERE UserId = createdBy) 
                            WHEN createdFrom =2 THEN (SELECT baName FROM business_associate WHERE id = createdBy) 
                            WHEN createdFrom =3 THEN (SELECT roName FROM relationship_officer WHERE id = createdBy) END AS ticketOwner,
                            (SELECT filepath FROM support_ticket_log WHERE support_ticket_log.ticketId = fccf.ticketId AND filepath <> '') as filepath,
                            (SELECT filename FROM support_ticket_log WHERE support_ticket_log.ticketId = fccf.ticketId AND filename <> '') as filename
                        FROM support_ticket fccf INNER JOIN support_ticket_status fcc ON fcc.id = fccf.ticketStatus
                        WHERE ticketId = {$pkid}", true);
                    }

                    $data['tabId'] = 2;
                    $data['success'] = true;
                    break;
                case 'communication':
                    if ($pkid > 0) {
                        $data = $supportdb->getFromDB("SELECT fccf.id,userId,userType,followupDate,callRemarks,callRecords,createdOn,createdBy,
                            fcc.name AS callActionName,entryFrom,entryAction,entryMode,contactNumber,
                            CASE WHEN entryFrom = 1 THEN 'Communication' 
                                WHEN entryFrom = 2 THEN 'Call Log' 
                                WHEN entryFrom = 3 THEN 'Call Recording' END AS entryFromName 
                                FROM call_logs fccf 
                            LEFT JOIN call_actions fcc ON fcc.id = fccf.callAction 
                            INNER JOIN support_applicable_users fup ON fup.id = fccf.userType 
                            WHERE fccf.id = {$pkid}", true);
                        $data['filename'] = basename($data['callRecords']);
                        $data['callInitiatedBy'] = $db->getItemFromDB("SELECT CONCAT(FirstName,'',LastName) FROM finascop_usr_profile WHERE UserId = {$data['createdBy']}");
                    }

                    $data['tabId'] = 3;
                    $data['success'] = true;
                    break;
            }
        } else {
            $data['tabId'] = 0;
            $data['success'] = false;
        }

        echo json_encode($data);
        break;
    case 'insertCallLogForCustomer':
        $supportdb->query('begin');
        $jobId = $_POST['jobId'];
        $type = $_POST['type'];
        $followupDateTime = date('Y-m-d', strtotime($_POST['followupDate'])) . ' ' . date('H:i', strtotime($_POST['followupTime']));
        //$followupDateTime = $_POST['followupDate'].' '.$_POST['followupTime'];
        $callAction = $supportdb->getItemFromDB("SELECT id FROM call_actions WHERE name = '{$type}'");
        $userType = $supportdb->getItemFromDB("SELECT id FROM support_applicable_users WHERE name = '{$_POST['userType']}'");
        $data['callAction'] = $callAction;
        $data['userId'] = $_POST['userId'];
        $data['userType'] = $userType;
        $data['callRemarks'] = $_POST['callRemarks'];
        $data['jobId'] = $jobId;
        $data['entryFrom'] = 2;
        if (!empty($_POST['followupDate'])) {
            $data['followupDate'] = date('Y-m-d H:i', strtotime($followupDateTime));
            $eventdata['followupDate'] = date('Y-m-d H:i', strtotime($followupDateTime));
        }
        $data['createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $status = $supportdb->perform('call_logs', $data);
        $callLogId = $supportdb->insert_id();
        switch ($callAction) {
            case 1:
                $eventdata['status'] = 3;
                break;
            case 2:
                $eventdata['status'] = 1;
                break;
            case 3:
                $eventdata['status'] = 1;
                break;
        }
        if (count($eventdata) > 0) {
            $eventdata['assignedTo'] = 0;
            $eventdata['callerName'] = '';
            $status = $supportdb->perform('outbound_jobs', $eventdata, 'update', " id = {$jobId}");

            $joblog['actionBy'] = $_SESSION['admin']->Finascop_UserId;
            $joblog['actionOn'] = date('Y-m-d H:i:s');
            $joblog['actionRemark'] = 'Call ' . $type;
            $joblog['jobId'] = $jobId;
            $joblog['callLogId'] = $callLogId;
            $status = $supportdb->perform('outbound_jobs_log', $joblog);
        }

        $status = $supportdb->query('commit');


        if ($status) {
            echo "{success: true,msg:'Call status updated and details saved successfully.'}";
        } else {
            echo "{success: false, msg: 'Error occured while saving data' }";
        }
        break;
    case 'getSiteUrls':
        $sgId = $_POST['sgId'];
        $cond = "";
        $customerPhone = $_POST['customerPhone'];
        $fields['phone'] = $customerPhone;
        if ($sgId > 0) {
            $cond  .= " AND store_group_id = {$sgId}";
        }
        $store_group = $db->getFromDB("SELECT store_group_id,store_group_name,siteUrl,
            IF(store_group_grosmartMerchant = 1,'YES','NO') AS store_group_grosmartMerchant,1 as isCustomer,br_Phone,br_Email,contactNumber 
            FROM finascop_branch_group INNER JOIN finascop_branch ON br_storeGroup = store_group_id  WHERE br_Phone LIKE '%{$customerPhone}%' {$cond} LIMIT 1", true);

        /*$url =  $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'MERCHANT_DETAILS'");
        $fields_string = json_encode($fields);
        $opts = array(
            CURLOPT_URL => $url,
            //CURLINFO_CONTENT_TYPE => "application/json",
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_BINARYTRANSFER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => count($fields),
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $datacl = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        header("Content-Type: application/json");
        $result = json_decode($datacl, true);*/



        if ($store_group['store_group_id'] > 0) {
            $data['cust_customer_name'] =  $store_group['store_group_name'];
            $data['cust_mobile'] =  $store_group['contactNumber'] . '-' . $store_group['br_Phone'];
            $data['cust_id'] = $store_group['store_group_id'];
            $data['StoreGroupId'] = $store_group['store_group_id'];
            $impersonateurl = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'IMPERSONATEURL_MERCHANT'");
            $data['partnerSite'] = $impersonateurl . $data['StoreGroupId'];
            $data['publicSite'] = 'https://' . $db->getItemFromDB("SELECT siteUrl FROM finascop_branch_group WHERE store_group_id = {$data['StoreGroupId']}");

            $data['success'] = true;
        } else {
            $data['success'] = false;
        }
        if (!empty($data)) {
            echo json_encode($data);
        }
        break;
    case 'sendOtpToCustomer':
        $phone = $_POST['phone'];
        $sdata = array();
        $ver = $db->getFromDB("select COUNT(1) AS ismobile,mobile,otp FROM test_mobile  WHERE mobile = {$phone}", true);
        $otp =  mt_rand(1000, 9999);
        if (intval($ver['ismobile']) > 0) {
            $otp = $ver['otp'];
        }

        $templatedata['otp'] = $otp;
        $sendsms = sms::fetchContentSendSms($templatedata, $phone, 15);
        $sendsms = (!empty($sendsms) ? 1 : 0);

        $lastcust_customer_id = $db->getItemFromDB("SELECT MAX(veri_customer_id) FROM retaline_customer_signup_verifiLog");
        $cust_customer_id = $lastcust_customer_id + 1;


        $sdata['veri_company_id'] = 1;
        $sdata['veri_mobile'] = $phone;
        $sdata['veri_sms_code'] = $otp;
        $sdata['veri_smsgen_dt'] = date('Y-m-d H:i:s');
        $sdata['veri_smsexp_dt'] = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $sdata['veri_sms_status'] = 'sent';
        $sdata['veri_sms_count'] = 1;
        $sdata['veri_status'] = 'sms sent';
        $sdata['veri_issend_sms'] = $sendsms;
        $sdata['veri_customer_id'] = $cust_customer_id;

        $db->query('begin');
        $status = $db->perform('retaline_customer_signup_verifiLog', $sdata);
        $veri_id = $db->insert_id();



        $status = $db->query('commit');
        if ($status) {
            echo "{success: true,veri_id: {$veri_id},cust_customer_id: {$cust_customer_id},msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'verifyCustomer':
        $phone = $_POST['phone'];
        $otp = $_POST['otp'];
        $veri_id = $_POST['veri_id'];
        $cust_customer_id = $_POST['cust_customer_id'];
        $signupOtp = $db->getItemFromDB("SELECT veri_sms_code FROM retaline_customer_signup_verifiLog WHERE veri_id = {$veri_id}");
        if ($signupOtp != $otp) {
            echo "{success: false, msg: 'Entered otp is not valid' }";
            exit();
        }

        $sdata['veri_status'] = 'verified';
        $db->query('begin');
        $status = $db->perform('retaline_customer_signup_verifiLog', $sdata, 'update', " veri_id = {$veri_id} ");

        $cusDeta['cust_customer_id'] = $cust_customer_id;
        $cusDeta['cust_mobile'] = $phone;
        $cusDeta['cust_branch_id'] = 0;
        $cusDeta['cust_email'] = 'email';
        $cusDeta['cust_customer_name'] = $phone;
        $cusDeta['cust_ref_code'] = GenerateReferralCode::generate();
        $cusDeta['cust_created_at'] = date('Y-m-d H:i:s');
        $cusDeta['cust_updated_at'] = date('Y-m-d H:i:s');
        $cusDeta['cust_status'] = 'admin-registered';
        $status = $db->perform('retaline_customer', $cusDeta);
        $cust_customer_id = $db->insert_id();
        $status = $db->query('commit');

        if ($status) {
            echo "{success: true,msg:'Verified Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
}
