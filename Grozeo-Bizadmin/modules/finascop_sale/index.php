<?php

require_once(INCLUDE_PATH . "/finascop_common_functions.php");
switch ($op) {
    case 'getInvoiceItems':
        $qry = "SELECT (SELECT stit_itemName FROM " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID = saii_itemID) AS item, saii_itemID AS item_id, "
                . "(SELECT stit_MRP FROM " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID = saii_itemID) AS mrp, "
                . "saii_Rate AS rate, saii_itemQty AS qty, saii_IGST AS igst, saii_CGST AS cgst, saii_SGST AS sgst, "
                . "(SELECT stit_HSNCode FROM " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID = saii_itemID) AS hsncode,"
                . "saii_Rate * saii_itemQty as amt_bf_tax,saii_Rate * saii_itemQty + saii_IGST + saii_CGST + saii_SGST as amt_af_tax "
                . "FROM " . FINASCOP_DB . "finascop_sales_invoice_items fsii INNER JOIN " . FINASCOP_DB . "finascop_sales_invoice fsi ON fsii.saen_Id = fsi.saen_Id "
                . "WHERE fsi.saen_Id='{$_POST['invID']}'";

        $items = $db->getMulipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . count($items) . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'saveEditedData':
        $invoice_date = explode('/', $_POST['invoice_date']);
        date_default_timezone_set('Asia/Kolkata');
        $invoice_date = date("Y-m-d", mktime(0, 0, 0, $invoice_date[1], $invoice_date[0], $invoice_date[2]));
        if ($_POST['purchase_orderdate'] != '') {
            $purchase_orderdate = explode('/', $_POST['purchase_orderdate']);
            date_default_timezone_set('Asia/Kolkata');
            $purchase_orderdate = date("Y-m-d", mktime(0, 0, 0, $purchase_orderdate[1], $purchase_orderdate[0], $purchase_orderdate[2]));
        } else {
            $purchase_orderdate = '0000-00-00';
        }
        $data = array(
            "saen_InvoiceDate" => $invoice_date,
            "saen_CustomerPurchaseOrder" => $_POST['purchase_orderno'],
            "saen_CustomerPurchaseOrderDate" => $purchase_orderdate,
            "saen_RefNo" => $_POST['referenceno']
        );


        $items = array(
            "InvoiceNo" => $_POST['invoiceno'],
            "InvoiceDate" => $invoice_date,
            "Party" => $_POST['party'],
            "ClientPO" => $_POST['purchase_orderno'],
            "ClientPODate" => $purchase_orderdate,
            "RefNo" => $_POST['referenceno']
        );
        $con = "saen_Id='" . $_POST['saen_Id'] . "'";
        $status = $db->perform(FINASCOP_DB . "finascop_sales_invoice", $data, 'update', $con);
        if ($status) {
            echo "{success: true,data:" . json_encode($items) . ",msg:'Updated Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }

        break;


    case 'saveInvoice':
        $db->query('begin');
        $cDate = explode('/', $_POST['inv_date']);
        date_default_timezone_set('Asia/Kolkata');
        $cDate = date("Y-m-d", mktime(0, 0, 0, $cDate[1], $cDate[0], $cDate[2]));
        if (!empty($_POST['purchase_order_date'])) {
            $poDate = explode('/', $_POST['purchase_order_date']);
            $poDate = date("Y-m-d", mktime(0, 0, 0, $poDate[1], $poDate[0], $poDate[2]));
        } else {
            $poDate = '0000-00-00';
        }
        $isParty = $_POST['cb1'];
        $br_id = intval($_SESSION['admin']->finascop_current_branch_id);
        if (empty($data['saen_Id'])) {
            $saen_Id = getRandomRef();
        }

        $data = array(
            'saen_Id' => $saen_Id,
            'saen_InvoiceDate' => $cDate,
            'saen_IsParty' => (isset($_POST['cb1']) ? 1 : 0),
            "stpa_id" => (isset($_POST['cb1']) ? $_POST['party_id_no'] : 0),
            'saen_Customername' => $_POST['party_name'],
            'saen_CustomerPurchaseOrder' => $_POST['purchcase_order_no'],
            'saen_CustomerPurchaseOrderDate' => $poDate,
            'saen_RefNo' => $_POST['ref_no'],
            'saen_Bank' => $_POST['paymentmode'],
            'saen_Discount' => $_POST['discount'],
            'saen_GrossAmt' => $_POST['grandtotal'],
            'saen_Tax' => $_POST['tax'],
            'saen_NetAmt' => $_POST['netamount'],
            'saen_terms_id' => $_POST['terms'],
            'saen_signature' => $_POST['signature'],
            'saen_TotalItems' => $_POST['totalitems'],
            'saen_TotalItemQty' => $_POST['totalquantity'],
            'br_id' => $br_id,
            'saen_updated_on' => sha1(microtime(true) . mt_rand(10000, 90000))
        );
        $status = $db->perform(FINASCOP_DB . 'finascop_sales_invoice', $data);

        $invoice_id = $saen_Id;

        $status = insertInvoiceNo($invoice_id);

        $status = saveItems($invoice_id, $_POST['itemData'], $br_id);


        $status = $db->query('commit');
        if ($status == 1) {
            $msg = "'Invoice saved successfully.'";
            echo "{success: true,msg:$msg}";
        } else {
            $msg = "'Error while saving invoice.'";
            echo '{"success":false,"msg":' . $msg . '}';
        }
        break;

    case 'getTermsData':
        $qry = "SELECT inte_terms AS terms, inte_termsDetails AS term_details from " . FINASCOP_DB . "finascop_inventory_terms WHERE inte_id = '{$_POST['id']}' ";
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';

        break;

    case 'listTermsdata':
        $search_hint = $_POST['query'];
        $qry = "SELECT inte_id AS id,inte_terms as terms,inte_termsDetails AS details from " . FINASCOP_DB . "finascop_inventory_terms WHERE inte_terms LIKE '{$search_hint}%'";
        $terms = $db->getMulipleData($qry, true);
        if (!empty($terms)) {
            echo '{"totalCount":' . count($terms) . ',"data":' . json_encode($terms) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'saveTerms':
        if (empty($_POST['id'])) {


            $termsName = $_POST['terms'];
            $IsUnique = $db->getItemFromDB("SELECT COUNT(inte_terms) from " . FINASCOP_DB . "finascop_inventory_terms WHERE inte_terms = '{$termsName}'");
            if ($IsUnique > 0) {
                echo "{success: false,msg:'Terms name already existing.'}";
                exit;
            }
            $data = array(
                "inte_terms" => $_POST['terms'],
                "inte_termsDetails" => $_POST['term_details']
            );
            $status = $db->perform(FINASCOP_DB . "finascop_inventory_terms", $data);
        } else {
            $data = array(
                "inte_terms" => $_POST['terms'],
                "inte_termsDetails" => $_POST['term_details']
            );
            $status = $db->perform(FINASCOP_DB . "finascop_inventory_terms", $data, 'update', "inte_id = " . intval($_POST['id']));
        }
        if ($status) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;

    case 'getDefaultUnit':
        $item_id = $_POST['item_id'];
        $qry = "SELECT sait_DefaultUnit AS item_unit from " . FINASCOP_DB . "finascop_sales_itemmaster WHERE sait_ID = {$item_id}";
        //$items = $db->getFromDB($qry,true);
        $items = $db->getItemFromDB($qry);
        if (!empty($items)) {
            echo '{"totalCount":' . count($items) . ',"data":[{"item_unit": ' . json_encode($items) . '}]}';
            //echo '{"totalCount":' . count($items) . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'getItems':
        $itemfilter = $_POST['query'];
        if (!empty($_REQUEST['excludeIds'])) {
            $excludeIds = json_decode(stripslashes($_REQUEST['excludeIds']), true);

            if (!empty($excludeIds)) {
                $excludeIds = implode(',', $excludeIds);
            } else {
                $excludeIds = "''";
            }
        } else {
            $excludeIds = "''";
        }
        if (!empty($itemfilter)) {
            $qry = "SELECT stit_ID AS item_id, stit_itemName AS item_name from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_SalesEnabled=1 "
                    . "AND stit_ID NOT IN (" . $excludeIds . ") AND  stit_itemName LIKE '{$itemfilter}%' "
                    . " UNION "
                    . "SELECT stit_ID AS item_id, stit_itemName AS item_name from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_SalesEnabled=1 "
                    . "AND stit_ID NOT IN (" . $excludeIds . ") AND  stit_itemName LIKE '%{$itemfilter}%' AND stit_itemName NOT LIKE '{$itemfilter}%'";
        } else {
            $qry = "SELECT stit_ID AS item_id, stit_itemName AS item_name from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_SalesEnabled=1 "
                    . "AND stit_ID NOT IN (" . $excludeIds . ") ";
        }
        $items = $db->getMultipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . count($items) . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;

    case 'getItemsData':
        $qry = "SELECT sait_ID AS item_id, sait_Name AS item, IF(sait_IsClientSpecific = 1,'on','') AS clinetspecific,"
                . "(SELECT ledgertypename from " . FINASCOP_DB . "finascop_accounts_ledger WHERE accled_Ledger_Id = sait_Ledger_Id) AS 'client', "
                . " sait_Ledger_Id as id,"
                . "sait_DefaultUnit AS default_unit from " . FINASCOP_DB . "finascop_sales_itemmaster WHERE sait_ID = '{$_POST['id']}' ";
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';

        break;

    case 'listItems':
        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        $currentCompanyID = $_SESSION['admin']->finascop_current_company_id;
        $qry = "SELECT sait_ID AS ItemId,sait_Name AS ItemName,ledgertypename  AS ClientName "
                . "from " . FINASCOP_DB . "finascop_sales_itemmaster LEFT join " . FINASCOP_DB . "finascop_accounts_ledger ON sait_Ledger_Id = accled_Ledger_Id "
                . " WHERE (accled_BranchId = {$currentBranch} OR accled_BranchId IS NULL) AND (accled_CompId = {$currentCompanyID} OR accled_CompId IS NULL)";
        $items = $db->getMultipleData($qry, true);
        if (!empty($items)) {
            echo '{"totalCount":' . count($items) . ',"data":' . json_encode($items) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;

    case 'saveItem':
        if (intval($_POST['id']) == 0) {
            $itemName = $_POST['item'];
            $IsUnique = $db->getItemFromDB("SELECT COUNT(sait_Name) from " . FINASCOP_DB . "finascop_sales_itemmaster WHERE sait_Name = '{$itemName}'");
            if ($IsUnique > 0) {
                echo "{success: false,msg:'Item already existing.'}";
                exit;
            }
        }

        if (!empty($_POST['clinetspecific'])) {
            $data = array(
                "sait_Name" => $_POST['item'],
                "sait_IsClientSpecific" => 1,
                "sait_Ledger_Id" => $_POST['client'],
                "sait_DefaultUnit" => $_POST['default_unit']
            );
        } else {
            $data = array(
                "sait_Name" => $_POST['item'],
                "sait_IsClientSpecific" => 0,
                //"sait_Ledger_Id" => '',
                "sait_DefaultUnit" => $_POST['default_unit']
            );
        }
        if (intval($_POST['id']) == 0) {
            $status = $db->perform(FINASCOP_DB . "finascop_sales_itemmaster", $data);
            if ($status) {
                echo "{success: true,msg:'Saved Successfully'}";
            } else {
                echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
            }
        } else {
            $con = 'sait_ID = ' . intval($_POST['id']);
            $status = $db->perform(FINASCOP_DB . "finascop_sales_itemmaster", $data, 'update', $con);
            if ($status) {
                echo "{success: true,msg:'Updated Successfully'}";
            } else {
                echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
            }
        }
        break;

    case 'listParty':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];

        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        // $currentCompanyID = $_SESSION['admin']->finascop_current_company_id;
        $search_hint = $_POST['query'];
        if ($_POST['isParty'] == 'false') {

            echo '{"success":true,"data":[]}';
            exit();
        } else {
            $qry = "SELECT fsp.stpa_id AS id,CONCAT(fsp.stpa_Fname,' ',fsp.stpa_Lname) AS party,"
                    . "(SELECT st_Id from " . FINASCOP_DB . "finascop_district fd WHERE fd.dst_Id=fsp.dst_Id) AS party_state_id, fsp.br_id  "
                    . "from " . FINASCOP_DB . "finascop_stock_party fsp WHERE fsp.stpa_Fname LIKE '{$search_hint}%' AND fsp.br_id = {$currentBranch} ";
            $party = $db->getMultipleData($qry, true);
            echo '{"success":true,"data":' . json_encode($party) . '}';
        }
        break;

    case 'listInvoices':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($_POST['sort']) ? 'saen_InvoiceDate' : $_POST['sort'];
        $rec_sort_dir = empty($_POST['dir']) ? 'DESC' : $_POST['dir'];
        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        $total = "select SUM(saen_NetAmt) AS total_amount,SUM(saen_Tax) as total_tax,SUM(saen_TotalItems) AS itemstotal, SUM(saen_TotalItemQty) AS totalitemqty  "
                . " from " . FINASCOP_DB . "finascop_sales_invoice WHERE {$currentBranch} = br_id";
        $coltotal = $db->getFromDB($total, true);
        $qry = "SELECT saen_Id AS InvID, saen_InvoiceNo AS InvoiceNo, saen_InvoiceDate AS InvoiceDate, saen_Customername AS Party, "
                . "saen_NetAmt AS amount, saen_Tax AS tax, saen_TotalItems AS totalitems, saen_TotalItemQty AS itemquantity, "
                . "saen_CustomerPurchaseOrder AS ClientPO, saen_CustomerPurchaseOrderDate AS ClientPODate, saen_RefNo AS RefNo, "
                . "saen_Bank AS Bank from " . FINASCOP_DB . "finascop_sales_invoice WHERE {$currentBranch} = br_id "
                . "ORDER BY {$rec_sort} {$rec_sort_dir} LIMIT {$rec_start},{$rec_limit}";
        $invoices = $db->getMulipleData($qry, true);

        $countQuery = "SELECT COUNT(*) FROM " . FINASCOP_DB . "finascop_sales_invoice a WHERE a.br_id= '{$currentBranch}'";
        $count = $db->getItemFromDB($countQuery);

        $result = [];
        foreach ($invoices as $key => $value) {

            foreach ($coltotal as $k => $v) {

                $value[$k] = $v;
                $result[$key] = $value;
            }
        }

        if (!empty($result)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;

    case 'saveBankMasterData':
        if (!empty($_POST['id']))
        // $cond = " and inba_Id <> " . intval($_POST['id']);
            $IsUnique = $db->getItemFromDB("SELECT COUNT(inba_AccountNo) from " . FINASCOP_DB . "finascop_inventory_bankdetails WHERE inba_AccountNo = '{$_POST['id_accno']}' AND inba_BankName = '{$_POST['id_bankname']}'AND inba_Id <> '{$_POST['id']}' ");
        if ($IsUnique > 0) {
            echo "{success: false,msg:'Acc.No already existing.'}";
            exit;
        }
        $IsUnique = $db->getItemFromDB("SELECT COUNT(inba_BankName) from " . FINASCOP_DB . "finascop_inventory_bankdetails WHERE inba_BankName = '{$_POST['id_bankname']}' AND inba_Branch = '{$_POST['id_branch']}'AND inba_Id <> '{$_POST['id']}'");
        if ($IsUnique > 0) {
            echo "{success: false,msg:'Bank Name already existing.'}";
            exit;
        }

        $data = array(
            "inba_AccountNo" => $_POST['id_accno'],
            "inba_BankName" => $_POST['id_bankname'],
            "inba_Branch" => $_POST['id_branch'],
            "inba_IFSC" => $_POST['id_ifc']
        );
        if (empty($_POST['id'])) {
            $status = $db->perform(FINASCOP_DB . "finascop_inventory_bankdetails", $data);
            if ($status) {
                echo "{success: true,msg:'Saved Successfully'}";
            } else {
                echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
            }
        } else {
            $con = 'inba_Id=' . intval($_POST['id']);
            $status = $db->perform(FINASCOP_DB . "finascop_inventory_bankdetails", $data, 'update', $con);
            if ($status) {
                echo "{success: true,msg:'Updated Successfully'}";
            } else {
                echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
            }
        }
        break;


    case 'listBankdata':
        global $db;
        $qry = "SELECT inba_Id AS id,inba_AccountNo AS accountno,inba_BankName AS  bank,inba_Branch AS branch,inba_IFSC As ifc  from " . FINASCOP_DB . "finascop_inventory_bankdetails";

        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        //echo' {"totalCount":"2","data":[{"customer":"Hari","client":"Alleppey Parcel Service","bank":"SBI","amount":"1000","utr":"123"},
        //{"customer":"Manu","client":"ABC","bank":"HDFC","amount":"5000","utr":"1234"}]}';

        break;

    case 'getBankMasterData':

        $qry = "select inba_Id AS id, inba_AccountNo AS id_accno,inba_BankName As id_bankname,inba_Branch As id_branch,inba_IFSC AS id_ifc
        from " . FINASCOP_DB . "finascop_inventory_bankdetails WHERE inba_Id = '{$_POST['id']}' ";
        $data = $db->getFromDB($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;

    case 'getCommonRate':
        $item_id = $_POST['item_id'];
        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        if ($item_id != '') {
            $qry = "select stbr_CommonRate from " . FINASCOP_DB . "finascop_stock_branch WHERE stit_ID = '{$item_id}' AND br_id = '{$currentBranch}' ";
        } else {
            $qry = "select stbr_CommonRate AS 0 from " . FINASCOP_DB . "finascop_stock_branch";
        }
        $data = $db->getFromDB($qry, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            echo '{"success":false,"msg":"Rate Not Found"}';
        }

        break;

    case 'getPartyRate':
        $item_id = $_POST['item_id'];
        $party_id = $_POST['party_id'];
        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        $qry = "select stbp_Rate from " . FINASCOP_DB . "finascop_stock_branch_rate WHERE stit_id = '{$item_id}' AND stbr_id = '{$currentBranch}' AND stpa_id = '{$party_id}'";
        $data = $db->getFromDB($qry, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            echo '{"success":false,"msg":"Rate Not Found"}';
        }
        break;

    case 'getBranchStateId':
        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        $qry = "select br_State from " . FINASCOP_DB . "finascop_branch WHERE br_ID = '{$currentBranch}' ";
        $data = $db->getFromDB($qry, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            echo '{"success":false,"msg":"No data found"}';
        }
        break;
    case 'getInvoiceItemdetails':
        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        $item_id = $_POST['item_id'];
        $party_state_id = $_POST['party_state_id'];
        $branch_state_id = $_POST['branch_state_id'];
        if ($branch_state_id == $party_state_id) {
            $qry = "select stit_MRP,stit_HSNCode,stit_GST, 0 AS IGST, ((stit_GST/100)*0.5) AS CGST, ((stit_GST/100)*0.5) AS SGST from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID= '{$item_id}' ";
        } else {
            $qry = "select stit_MRP,stit_HSNCode,stit_GST, (stit_GST/100) AS IGST, 0 AS CGST, 0 AS SGST  from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID= '{$item_id}' ";
        }


        $data = $db->getFromDB($qry, true);
        if ($data) {
            echo "{success:true,data:" . json_encode($data) . "}";
        } else {
            echo '{"success":false,"msg":"Data not inserted"}';
        }

        break;

    case 'getTermDetails':
        $term = $_POST['term'];
        $qry = "select inte_termsDetails from " . FINASCOP_DB . "finascop_inventory_terms WHERE inte_id = '{$term}'";
        $data = $db->getFromDB($qry, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            echo '{"success":false,"msg":"No terms Found"}';
        }
        break;

    case 'getSalesReturnslist':

        $rec_limit = empty($_POST['limit']) ? 15 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($_POST['sort']) ? 'sare_InvoiceDate' : $_POST['sort'];
        $rec_sort_dir = empty($_POST['dir']) ? 'DESC' : $_POST['dir'];
        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        $total = "select SUM(sare_TotalItems) AS returned_item_total,SUM(sare_TotalItemsQty) as total_items_qty, SUM(sare_GrossAmt) as totalamount "
                . " from " . FINASCOP_DB . "finascop_sales_return WHERE br_id = {$currentBranch} AND sare_IsCancelled = 0";
        $coltotal = $db->getFromDB($total, true);
        $qry = "SELECT sare_id,sare_InvoiceNo, sare_InvoiceDate, sare_TotalItems, "
                . "sare_TotalItemsQty, sare_GrossAmt, (SELECT saen_updated_on FROM " . FINASCOP_DB . "finascop_sales_invoice fsi WHERE fsi.saen_Id = fsr.saen_Id ) AS previous_key,(SELECT concat(FirstName,' ',LastName) "
                . "from " . FINASCOP_DB . "finascop_usr_profile WHERE sare_EntryBy = UserId) AS Entryby, "
                . "saen_Id, "
                . "(SELECT COUNT(pure_id) FROM " . FINASCOP_DB . "finascop_purchase_returnable fpr WHERE 
                    fpr.prab_RefId = fsr.saen_Id AND fpr.pure_id <> '') AS showCancelBtn, 
                    'Yes' AS has_returns, saen_Id AS InvID, sare_updated_on as updated_on "
                . "from " . FINASCOP_DB . "finascop_sales_return fsr "
                . "WHERE br_id = {$currentBranch} AND sare_IsCancelled = 0 "
                . "ORDER BY {$rec_sort} {$rec_sort_dir} LIMIT $rec_start,$rec_limit";
        $invoices = $db->getMulipleData($qry, true);

        $count_qry = "SELECT COUNT(*) FROM " . FINASCOP_DB . "finascop_sales_return WHERE br_id = {$currentBranch} AND sare_IsCancelled = 0 ";
        $count = $db->getItemFromDB($count_qry);
                $result = [];
        foreach ($invoices as $key => $value) {

            foreach ($coltotal as $k => $v) {

                $value[$k] = $v;
                $result[$key] = $value;
            }
        }
        if (!empty($result)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;

    case 'listsearchsalesreturn':

        $rec_limit = intval($_POST['limit']) == 0 ? 15 : $_POST['limit'];
        $rec_start = intval($_POST['start']) == 0 ? 0 : $_POST['start'];
        $rec_sort = empty($_POST['sort']) ? 'saen_InvoiceDate' : $_POST['sort'];
        $rec_sort_dir = empty($_POST['dir']) ? 'DESC' : $_POST['dir'];
        $fromdate = explode('/', $_POST['item_search_from']);
        $fromDate = date("Y-m-d", mktime(0, 0, 0, $fromdate[1], $fromdate[0], $fromdate[2]));

        $todate = explode('/', $_POST['item_search_to']);
        $toDate = date("Y-m-d", mktime(0, 0, 0, $todate[1], $todate[0], $todate[2]));


        $invoice_no = $_POST['invoice_no'];

        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        $invoice_condition = "";
        if ($invoice_no) {
            $invoice_condition = " AND saen_InvoiceNo LIKE '%{$invoice_no}%' ";
        }

        $qry = "SELECT saen_Id AS InvID, saen_InvoiceNo AS InvoiceNo, "
                . "saen_InvoiceDate AS InvoiceDate, saen_TotalItems AS totalitems, "
                . "saen_TotalItemQty AS totalitemsqty, IF(saen_HasSalesReturns = 1,'Yes','No') AS has_returns, "
                . "saen_Discount as discount, saen_GrossAmt AS gross_amount,saen_updated_on AS previous_key,saen_Tax as tax,"
                . "saen_NetAmt as net_amount,"
                . "saen_StockApproval as stock_approval, saen_StockApproved as stock_approved,"
                . " saen_StockRejected as stock_rejected,"
                . "br_id as br_id  "
                . "from " . FINASCOP_DB . "finascop_sales_invoice  "
                . "WHERE {$currentBranch} = br_id AND  "
                . "saen_InvoiceDate BETWEEN '$fromDate' AND '$toDate' $invoice_condition  ORDER BY {$rec_sort} {$rec_sort_dir} LIMIT $rec_start,$rec_limit";

        $invoices = $db->getMulipleData($qry, true);

        $count_qry = "SELECT COUNT(*) FROM " . FINASCOP_DB . "finascop_sales_invoice "
                . "WHERE br_id = {$currentBranch} AND  saen_InvoiceDate "
                . "BETWEEN '$fromDate' AND '$toDate' AND saen_InvoiceNo LIKE '%{$invoice_no}%' ";
        $count = $db->getItemFromDB($count_qry);

        if (!empty($invoices)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($invoices) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;


    case 'listsalesgridstore':
        $invoice_no = $_POST['invoice_no'];
        $InvID = $_POST['InvID'];
        $returnqty = $_POST['return_qty'];
        $qry = "SELECT (SELECT stit_itemName from " . FINASCOP_DB . "finascop_stock_itemmaster"
                . " WHERE stit_ID=saii_itemID) AS itemname,"
                . "(SELECT stit_StockEnabled from " . FINASCOP_DB . "finascop_stock_itemmaster "
                . "WHERE stit_ID=saii_itemID) AS stock_enabled, "
                . "saii_itemID AS itemid, "
                . "(SELECT fsi.stpa_id FROM " . FINASCOP_DB . "finascop_sales_invoice fsi "
                . "WHERE fsi.saen_Id = fsii.saen_Id) AS partyId,"
                . "(SELECT fd.st_Id FROM " . FINASCOP_DB . "finascop_district fd WHERE fd.dst_Id = "
                . "(SELECT dst_Id FROM " . FINASCOP_DB . "finascop_stock_party fsp "
                . "WHERE fsp.stpa_id=fsi.stpa_id)) AS state_id, "
                . "saii_itemQty AS sold_qty,saii_returnedQty AS return_qty,saii_resaleableQty as return_resaleable, "
                . "saii_Rate AS rate from " . FINASCOP_DB . "finascop_sales_invoice_items fsii INNER JOIN " . FINASCOP_DB . "finascop_sales_invoice fsi ON fsii.saen_Id = fsi.saen_Id WHERE '{$InvID}' =fsii.saen_Id";
        $data = $db->getMulipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;

    case 'listsalesreturnviewstore':
        $invoice_no = $_POST['invoice_no'];
        $InvID = $_POST['InvID'];
        $returnqty = $_POST['return_qty'];
        $qry = "SELECT (SELECT stit_itemName from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID=saii_itemID) AS itemname,"
                . "(SELECT stit_StockEnabled from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID=saii_itemID) AS stock_enabled, "
                . "saii_itemID AS itemid, "
                . "(SELECT fsi.stpa_id FROM " . FINASCOP_DB . "finascop_sales_invoice fsi WHERE fsi.saen_Id = fsii.saen_Id) AS partyId,"
                . "(SELECT fd.st_Id FROM " . FINASCOP_DB . "finascop_district fd WHERE fd.dst_Id = "
                . "(SELECT dst_Id FROM " . FINASCOP_DB . "finascop_stock_party fsp WHERE fsp.stpa_id=fsi.stpa_id)) AS state_id, "
                . "saii_itemQty AS sold_qty,saii_returnedQty AS return_qty,saii_resaleableQty as return_resaleable, saii_Rate AS rate, "
                . "sard_itemPurchaseReturnableQty AS purchase_return_qty, sard_itemScrapQty AS scrap_qty, sard_IGST AS igst, sard_CGST AS cgst,"
                . " sard_SGST AS sgst, saii_Rate*saii_returnedQty+sard_IGST+sard_CGST+sard_SGST+sard_CGST AS amount from " . FINASCOP_DB . "finascop_sales_invoice_items fsii INNER JOIN " . FINASCOP_DB . "finascop_sales_invoice fsi "
                . "ON fsii.saen_Id = fsi.saen_Id INNER JOIN " . FINASCOP_DB . " finascop_sales_return fsr ON fsr.saen_Id = fsi.saen_Id "
                . "INNER JOIN " . FINASCOP_DB . "finascop_sales_return_items fsri ON fsr.sare_id = fsri.sare_Id AND fsri.sard_ItemID = fsii.saii_itemID "
                . "AND sare_IsCancelled=0 "
                . "WHERE '{$InvID}' =fsii.saen_Id";
        $data = $db->getMulipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;

    case 'getSalesReturnItemdetails':
        $currentBranch = $_SESSION['admin']->finascop_current_branch_id;
        $branch_qry = "select br_State from " . FINASCOP_DB . "finascop_branch WHERE br_ID = '{$currentBranch}' ";
        $branch_state_id = $db->getFromDB($branch_qry, true);
        $item_id = $_POST['item_id'];
        $party_stateid = $_POST['party_state_id'];
        $party_state_id = ($party_stateid == "" ? $_POST['branch_state_id'] : $_POST['party_state_id']);
        if ($branch_state_id == $party_state_id) {
            $qry = "select stit_GST, 0 AS IGST, ((stit_GST/100)*0.5) AS CGST,((stit_GST/100)*0.5) AS SGST from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID= '{$item_id}' ";
        } else {
            $qry = "select stit_MRP,stit_HSNCode,stit_GST, (stit_GST/100) AS IGST, 0 AS CGST, 0 AS SGST  from " . FINASCOP_DB . "finascop_stock_itemmaster WHERE stit_ID= '{$item_id}' ";
        }
        $data = $db->getFromDB($qry, true);
        if ($data) {

            echo "{success:true,data:" . json_encode($data) . "}";
        } else {
            echo '{"success":false,"msg":"Data not inserted"}';
        }

        break;


    case 'removeSalesReturn':
        $can_id = $_POST['remove_SRid'];
        $saen_Id = $_POST['saen_Id'];
        $prev_key = $_POST['previous_key'];
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        if (!salesReturnTableDataIntegrityIsOK($prev_key, $can_id, $branch_id)) {
            echo '{"success":false,"msg":"Current sales return invoice has been updated by another user, since you access the data."}';
            exit();
        }

        $stockregId = "SELECT stre_id,stre_ApprovedBy FROM " . FINASCOP_DB . "finascop_stock_register WHERE stre_RefInvId = '{$can_id}' AND stre_ApprovedBy > 0";
        $result = $db->getMultipleData($stockregId, true);

        if ($result == true) {
            $val_StockUpdation = "SELECT sard_itemID, sard_itemReturnedQty FROM " . FINASCOP_DB . "finascop_sales_return_items "
                    . "WHERE sare_Id = '{$can_id}'";
            $resultForStockUpdation = $db->getMultipleData($val_StockUpdation, true);
            foreach ($result as $key => $value) {
                if ($value['stre_ApprovedBy'] > 0) {

                    if ($resultForStockUpdation == true) {
                        foreach ($resultForStockUpdation as $key => $val) {
                            $itemid = intval($val['sard_itemID']);
                            $return_qty = intval($val['sard_itemReturnedQty']);
                            $integrity_key = sha1(microtime(true) . mt_rand(10000, 90000));
                            $qry = "UPDATE  " . FINASCOP_DB . "finascop_stock_branch SET stbr_CurrentStock = stbr_CurrentStock - {$return_qty},"
                                    . "stbr_updated_on = '{$integrity_key}' "
                                    . "WHERE stit_ID = '{$itemid}' AND br_Id = '{$branch_id}'";
                            $status = $db->query($qry);
                        }
                    }
                }
            }
        } else {
            $query = "DELETE FROM  " . FINASCOP_DB . "finascop_stock_register WHERE stre_RefInvId = '$can_id' AND br_id = '$branch_id'";
            $status = $db->query($query);
        }
        $integrity_key = sha1(microtime(true) . mt_rand(10000, 90000));
        $qry = "UPDATE  " . FINASCOP_DB . "finascop_sales_return SET sare_IsCancelled = 1, sare_updated_on = '{$integrity_key}' WHERE sare_id = '$can_id' AND br_id = '$branch_id' ";
        $status = $db->query($qry);

        $query = "UPDATE  " . FINASCOP_DB . "finascop_sales_invoice SET saen_HasSalesReturns = 0 "
                . "WHERE saen_Id = '$saen_Id'";
        $status = $db->query($query);

        $query = "UPDATE  " . FINASCOP_DB . "finascop_sales_invoice_items SET saii_returnedQty = 0 , saii_resaleableQty = 0 "
                . "WHERE saen_Id = '$saen_Id'";
        $status = $db->query($query);

        if ($status == true) {
            echo '{"success":true,"msg":"Data Removed Successfully"}';
        } else {
            echo '{"success":fasle,"msg":"Error occured while removing this data"}';
        }

        break;


    case 'saveSalesReturn':

        $db->query('begin');
        date_default_timezone_set('Asia/Kolkata');
        $date = date("Y-m-d");
        $br_id = intval($_SESSION['admin']->finascop_current_branch_id);
        $UserId = $_SESSION['admin']->Finascop_UserId;
        $prev_key = $_POST['previous_key'];
        $integrity_key = sha1(microtime(true) . mt_rand(10000, 90000));
        $saen_Id = $_POST['saen_id'];
        if (!salesTableDataIntegrityIsOK($prev_key, $saen_Id, $br_id)) {
            echo '{"success":false,"msg":"Current Invoice has been updated by another user, since you access the data."}';
            exit;
        }
        if (empty($data['sare_id'])) {
            $sare_id = getRandomRef();
        }
        $sales_return_gridData = json_decode(stripslashes($_POST['sales_return_gridData']), true);
        $sales_return_total_gridData = json_decode(stripslashes($_POST['sales_return_total_gridData']), true);
        $itemcount = 0;
        $tax = $sales_return_total_gridData[0]['igst'] + $sales_return_total_gridData[0]['cgst'] + $sales_return_total_gridData[0]['sgst'];

        foreach ($sales_return_gridData as $key => $val) {

            if ($val['return_qty'] != 0) {
                $salesreturnGriddata = array(
                    "sare_Id" => $sare_id,
                    "sard_itemID" => $val['itemid'],
                    "sard_itemReturnedQty" => $val['return_qty'],
                    "sard_itemPurchaseReturnableQty" => $val['purchase_return_qty'],
                    "sard_itemResalableQty" => $val['return_resaleable'],
                    "sard_itemScrapQty" => $val['scrap_qty'],
                    "sard_Rate" => $val['rate'],
                    "sard_IGST" => $val['igst'],
                    "sard_CGST" => $val['cgst'],
                    "sard_SGST" => $val['sgst']
                );
                $status = $db->perform(FINASCOP_DB . 'finascop_sales_return_items', $salesreturnGriddata);

                $itemcount = $itemcount + 1;
                $return_quantity = intval($val['return_qty']);
                $resaleable_qty = intval($val['return_resaleable']);
                $qry = "UPDATE  " . FINASCOP_DB . "finascop_sales_invoice_items SET saii_returnedQty = {$return_quantity},saii_resaleableQty = {$resaleable_qty}  WHERE saen_Id = '{$_POST['saen_id']}' AND saii_itemID = '{$val['itemid']}'";
                $status = $db->query($qry);
            }
        }

        $query = "UPDATE  " . FINASCOP_DB . "finascop_sales_invoice SET saen_HasSalesReturns = 1,saen_updated_on = '{$integrity_key}' WHERE saen_InvoiceNo = '{$_POST['invoice_no']}'";
        $status = $db->query($query);
        $salesReturndata = array(
            "sare_id" => $sare_id,
            "sare_InvoiceDate" => $date,
            "sare_TotalItems" => $itemcount,
            "sare_TotalItemsQty" => $sales_return_total_gridData[0]['return_qty'],
            "saen_Id" => $_POST['saen_id'],
            "sare_GrossAmt" => $_POST['gross_amount'],
            "sare_Tax" => $tax,
            "sare_NetAmt" => $_POST['gross_amount'],
            "br_id" => $_POST['br_id'],
            "sare_EntryBy" => $UserId,
            "sare_updated_on" => sha1(microtime(true) . mt_rand(10000, 90000))
        );
        $status = $db->perform(FINASCOP_DB . 'finascop_sales_return', $salesReturndata);
        $invoice_id = $sare_id;
        $salesreturninvoiceno = insertSalesReturnInvoiceNo($invoice_id);
        $stockReturnInvNo = generateNextDocNo('21', $br_id);

        foreach ($sales_return_gridData as $key => $value) {
            if (($value['return_qty'] != 0) && ($value['stock_enabled'] == 1)) {
                $stockRegisterReturndata = array(
                    "stit_ID" => $value['itemid'],
                    "stre_Qty" => $value['return_qty'],
                    "stre_isPurchase" => 0,
                    "stre_InvNo" => $stockReturnInvNo,
                    "br_id" => $br_id,
                    "stre_RefInvId" => $sare_id,
                    "stre_Date" => $date
                );
                $status = $db->perform(FINASCOP_DB . 'finascop_stock_register', $stockRegisterReturndata);
            } else {
                $itemid = intval($value['itemid']);
                $return_qty = intval($value['return_qty']);
                $integrity_key = sha1(microtime(true) . mt_rand(10000, 90000));
                $qry = "UPDATE  " . FINASCOP_DB . "finascop_stock_branch SET stbr_CurrentStock = stbr_CurrentStock + {$return_qty},"
                        . "stbr_updated_on = '{$integrity_key}'"
                        . " WHERE stit_ID = '{$itemid}' AND br_Id = '{$br_id}'";
                $status = $db->query($qry);
            }
        }

        foreach ($sales_return_gridData as $key => $v) {
            if (empty($data['prab_Id'])) {
                $prab_Id = getRandomRef();
            }
            if (($v['purchase_return_qty'] != 0)) {
                $purchase_returnable_data = array(
                    "prab_Id" => $prab_Id,
                    "prab_RefId" => $_POST['saen_id'],
                    "prab_RefIsPurchaseReturn" => 0,
                    "br_id" => $br_id,
                    "prab_RecordDate" => $date,
                    "stit_ID" => $v['itemid'],
                    "prab_Qty" => $v['purchase_return_qty'],
                    "prab_EntryBy" => $UserId,
                    "prab_IsActive" => 1
                );
                $status = $db->perform(FINASCOP_DB . 'finascop_purchase_returnable', $purchase_returnable_data);
            }
        }

        $status = $db->query('commit');


        if ($status) {
            echo "{success: true,msg:'Data Saved'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;


    case 'getSalesReturn_InvoiceNo':
        $invID = $_POST['invID'];
        $item_id = $_POST['item_id'];
        $qry = "SELECT sare_InvoiceNo as sare_invNo  FROM  " . FINASCOP_DB . "finascop_sales_return "
                . "WHERE sare_IsCancelled = 0 AND saen_Id = '{$invID}'";

        $sare_InvoiceNo = $db->getFromDB($qry, true);

        if ($sare_InvoiceNo) {
            echo '{"success":true,"data":' . json_encode($sare_InvoiceNo) . ',"msg":"success"}';
        } else {
            echo '{"success":false,"msg":"Data fetching is failed"}';
        }

        break;
}

