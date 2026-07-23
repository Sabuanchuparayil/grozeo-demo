<?php
$quor_id = $_REQUEST['quor_id'];
$orderDetails = $db->getFromDb("SELECT quor_AmountCollectible,quor_RefNo as booking_no,DATE_FORMAT(quor_Date,'%d-%m-%Y') as booked_at,quor_PickupPhone,quor_PickupName,quor_DeliveryName,quor_DeliveryPhone,quor_Deliverybr_id,"
        . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",quor_PickupName,quor_DeliveryName) as customer,"
        . "quor_PickupLocation as source,"
        . "quor_DeliveryLocation as destination,"
        . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . ",'DELIVER', if(quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",'DISPATCH','PICKUP')) as type,"
        . "quor_id, "
        . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",quor_DeliveryLat,quor_PickupLat) as latitude,"
        . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",quor_DeliveryLng,quor_PickupLng) as longitude, "
        . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",' " . GMAP_DELIVERY_ICON . "','" . GMAP_PIKCUP_ICON . "') as mapicon, "
        . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . "," . FINASCOP_DB . "getBranchGodownGeoLocation(quor_Deliverybr_id,1), " . FINASCOP_DB . "getBranchGodownGeoLocation(quor_Pickupbr_id,1)) as brGodownLati,"
        . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . "," . FINASCOP_DB . "getBranchGodownGeoLocation(quor_Deliverybr_id,2), " . FINASCOP_DB . "getBranchGodownGeoLocation(quor_Pickupbr_id,2)) as brGodownLong,"
        . "(SELECT dls_DelStatus FROM qugeo_deliverystatus WHERE dls_ID = quor_Status) AS djStatus,quor_Status,"
        . "CASE WHEN quor_Type=1 THEN 'Drive' WHEN quor_Type=2 THEN 'Hired' WHEN quor_Type=3 THEN 'Customer Pickup' WHEN quor_Type=4 THEN 'Courier' WHEN quor_Type=5 THEN 'Driver Pickup' WHEN quor_Type=6 THEN 'Manual Delivery' END AS quor_TypeName,quor_Type,"
        . "if(quor_DeliveredTime <> '0000-00-00 00:00:00',DATE_FORMAT(quor_DeliveredTime,'%d-%m-%Y'),DATE_FORMAT(quor_DeliveryConfTime,'%d-%m-%Y %H:%i:%s')) as quor_DeliveredTime,"
        . "DATE_FORMAT(quor_ScheduleOpeningTime,'%d-%m-%Y %H:%i:%s') as quor_ScheduleOpeningTime,quor_TransferOrder_id,quor_PacketCount,quor_DeliveryDriverId "
        . " FROM " . FINASCOP_DB . " qugeo_order WHERE quor_id={$quor_id}", true);


$toDetails = $db->getFromDB("SELECT fsto_id,fsto_uid,fstr_id,fsto_ordertype,fsto_type,fsto_status,fsto_createdOn,fsto_source,fsto_destination FROM finascop_stock_transfer_order WHERE fsto_id = {$orderDetails['quor_TransferOrder_id']}", true);
$trDetails = $db->getFromDB("SELECT fstr_id,fstr_uid,DATE_FORMAT(fstr_createdOn,'%d-%m-%Y %H:%i:%s') as fstr_createdOn FROM finascop_stock_transfer_request WHERE fstr_id = {$toDetails['fstr_id']}", true);
$fstos_status = $db->getItemFromDB("SELECT fstos_status FROM finascop_stock_transfer_order_status WHERE fstos_id = {$toDetails['fsto_status']}");
$Driver = $db->getItemFromDB("SELECT CONCAT(d_Name,'',l_Name) FROM qugeo_driver WHERE d_ID = {$orderDetails['quor_DeliveryDriverId']}");
switch ($toDetails['fsto_ordertype']) {
    case 0:
        $fsto_ordertype = 'Branch Transfer';
        $consignee = $db->getItemFromDB("SELECT br_Name FROM finascop_branch where br_ID = {$toDetails['fsto_destination']}");
        $parentOrder = $db->getFromDB("SELECT fstr_uid AS paOrderNumber,fstr_createdOn AS paOrderDate FROM finascop_stock_transfer_request WHERE fstr_id = {$toDetails['fstr_id']}", true);
        break;
    case 1:
        $fsto_ordertype = 'B2C';
        $consignee = $db->getItemFromDB("SELECT cust_customer_name FROM retaline_customer WHERE cust_id = {$toDetails['fsto_destination']}");
        $orderDetails['booking_no'] = $db->getItemFromDB("SELECT SONumber FROM B2CSalesOrder WHERE customer_order_id = {$toDetails['fstr_id']}");
        $parentOrder = $db->getFromDB("SELECT order_order_id AS paOrderNumber,order_confirm_date AS paOrderDate,order_slot_id,order_slot_date,payment_mode,order_ondel_bankref_id FROM retaline_customer_order WHERE order_id = {$toDetails['fstr_id']}", true);
        $invoiceDetails = $db->getFromDB("SELECT invoiceNumber,invoiceDate FROM B2CInvoice WHERE bci_fstr_id = {$toDetails['fstr_id']}",true);
        break;
    case 2:
        $fsto_ordertype = 'B2B';
        $consignee = $db->getItemFromDB("SELECT b2b_Customer_Name FROM retaline_B2Bcustomer where b2b_Customer_ID =  {$toDetails['fsto_destination']}");
        $parentOrder = $db->getFromDB("SELECT bbso_SONumber AS paOrderNumber,bbso_SODate AS paOrderDate FROM retaline_B2B_SalesOrder WHERE bbso_id = {$toDetails['fstr_id']}", true);
        break;
    case 3:
        $fsto_ordertype = 'Return';
        $consignee = $db->getItemFromDB("SELECT br_Name FROM finascop_branch where br_ID = {$toDetails['fsto_destination']}");
        $parentOrder = $db->getFromDB("SELECT fstr_uid AS paOrderNumber,fstr_createdOn AS paOrderDate FROM finascop_stock_transfer_request WHERE fstr_id = {$toDetails['fstr_id']}", true);
        break;
}

$toDetailItems = $db->getMultipleData("SELECT fsto_ItemId,fsto_ItemQty,fsto_pkdQty,fsto_stockValue FROM finascop_stock_transfer_order_details WHERE fsto_id = {$orderDetails['quor_TransferOrder_id']}", true);

$arrAPI = array();
$arrAPI['PartitionKey'] = array('col' => 'quor_id', 'val' => (int) $quor_id, 'oper' => '=');
$arrAPI['IndexName'] = 'quor_id-index';
$arrAPI['queryAttributes'] = array('orderid');
$nodb = new \cgoDynamiteDB();
$rsno = $nodb->query('QugeoOrderDetails', $arrAPI, 'query');
$datas = [];
if (isset($rsno) && count($rsno) > 0) {
    $showbutton = true;
} else {
    $showbutton = false;
}
$sourcePyramid = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch where br_ID = {$toDetails['fsto_source']}");
?>

<html>
    <script>
        parent.window.Application.DeliveryJobs.Cache.vphbutton = '<?php echo $showbutton; ?>';
    </script>
    <style>
        .cesstable {
            border: 1px solid #CECECE;
            text-align: left;
        }
        table {
            font-family: arial;
            font-size: 11px;
            border-collapse: collapse;
            border-spacing: 0;
        }
        h4 {
            font-family: arial;
            font-size: 13px;
            font-weight: bold;
            padding: 3px 0;
        }
        .cesstable td {
            border-color: -moz-use-text-color #CECECE #CECECE -moz-use-text-color;
            border-style: none solid solid none;
            border-width: 0 1px 1px 0;
            height: 22px;
            padding: 0 10px 0 12px;
            vertical-align: middle;
        }
    </style>

    <?php
    if (!empty($orderDetails)) {
        
        ?>

        <h4>Details</h4>
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>


            <tr>
                    <td>
                        Order Date
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['booked_at']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Order No
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['booking_no']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Invoice No
                    </td>
                    <td>
                        <b> <?php echo $invoiceDetails['invoiceNumber']; ?> </b>
                    </td>
                </tr>

                <tr>
                    <td>
                        Cash to be Colelcted
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['quor_AmountCollectible']; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Delivery Date
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['quor_DeliveredTime']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Delivery Executive
                    </td>
                    <td>                        
                        <b> <?php echo $Driver; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Amount Received
                    </td>
                    <td>
                        <b> <?php if($orderDetails['quor_Status'] == 15) 
                        echo 0;
                        else 
                        echo $orderDetails['quor_AmountCollectible']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Transaction Refernece
                    </td>
                    <td>
                        <b> <?php echo $parentOrder['order_ondel_bankref_id']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Bank
                    </td>
                    <td>
                        <b> <?php echo $parentOrder['order_ondel_bankref_id']; ?> </b>
                    </td>
                </tr>
                
            </tbody>
        </table>
       
        
        <?php
        
    } else {
        ?>
        sorry there is no available data to display
    <?php } ?>




</html>

