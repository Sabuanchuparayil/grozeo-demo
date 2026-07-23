<?php
$quor_id = $_REQUEST['quor_id'];
$orderDetails = $db->getFromDb("SELECT quor_RefNo as booking_no,DATE_FORMAT(quor_Date,'%d-%m-%Y') as booked_at,quor_PickupPhone,quor_PickupName,quor_DeliveryName,quor_DeliveryPhone,quor_Deliverybr_id,"
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
        . "CASE WHEN quor_Type=1 THEN 'Drive' WHEN quor_Type=2 THEN 'Hired' WHEN quor_Type=3 THEN 'Customer Pickup' WHEN quor_Type=4 THEN 'Courier' END AS quor_TypeName,quor_Type,"
        . "if(quor_DeliveredTime <> '0000-00-00 00:00:00',DATE_FORMAT(quor_DeliveredTime,'%d-%m-%Y'),DATE_FORMAT(quor_DeliveryConfTime,'%d-%m-%Y %H:%i:%s')) as quor_DeliveredTime,"
        . "DATE_FORMAT(quor_ScheduleOpeningTime,'%d-%m-%Y %H:%i:%s') as quor_ScheduleOpeningTime,quor_TransferOrder_id,quor_PacketCount "
        . " FROM " . FINASCOP_DB . " qugeo_order WHERE quor_id={$quor_id}", true);

$toDetails = $db->getFromDB("SELECT fsto_id,fsto_uid,fstr_id,fsto_ordertype,fsto_type,fsto_status,fsto_createdOn,fsto_source,fsto_destination FROM finascop_stock_transfer_order WHERE fsto_id = {$orderDetails['quor_TransferOrder_id']}", true);
$trDetails = $db->getFromDB("SELECT fstr_id,fstr_uid,DATE_FORMAT(fstr_createdOn,'%d-%m-%Y %H:%i:%s') as fstr_createdOn FROM finascop_stock_transfer_request WHERE fstr_id = {$toDetails['fstr_id']}", true);
$fstos_status = $db->getItemFromDB("SELECT fstos_status FROM finascop_stock_transfer_order_status WHERE fstos_id = {$toDetails['fsto_status']}");
$consigner = $db->getItemFromDB("SELECT br_Name FROM finascop_branch where br_ID = {$toDetails['fsto_source']}");
switch ($toDetails['fsto_ordertype']) {
    case 0:
        $fsto_ordertype = 'CPD TO BR';
        $consignee = $db->getItemFromDB("SELECT br_Name FROM finascop_branch where br_ID = {$toDetails['fsto_destination']}");
        break;
    case 1:
        $fsto_ordertype = 'B2C';
        $consignee = $db->getItemFromDB("SELECT cust_customer_name FROM retaline_customer WHERE cust_id = {$toDetails['fsto_destination']}");
        break;
    case 2:
        $fsto_ordertype = 'B2B';
        $consignee = $db->getItemFromDB("SELECT b2b_Customer_Name FROM retaline_B2Bcustomer where b2b_Customer_ID =  {$toDetails['fsto_destination']}");
        break;
    case 3:
        $fsto_ordertype = 'BR TO CPD';
        $consignee = $db->getItemFromDB("SELECT br_Name FROM finascop_branch where br_ID = {$toDetails['fsto_destination']}");
        break;
}

$toDetailItems = $db->getMultipleData("SELECT fsto_ItemId,fsto_ItemQty FROM finascop_stock_transfer_order_details WHERE fsto_id = {$orderDetails['quor_TransferOrder_id']}", true);

//        print_r($orderDetails);
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
        //echo '<pre>';print_r($data);
        // $t_amout =  $data['order_total_amount'] + $data['order_delivery_charge'];
        //$countDetails = $db->getMultipleData("SELECT fsto_id,fsto_ItemId FROM finascop_stock_transfer_order_details WHERE fsto_id={$orderDetails['fsto_id']}", true);
//        print_r($countDetails);
//        echo count($countDetails);
        ?>

        <h4>Drive Details</h4>
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>



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
                        Consignee
                    </td>
                    <td>
                        <!--<b> <?php echo $orderDetails['customer']; ?> </b>-->
                        <b> <?php echo $consignee; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Consigner
                    </td>
                    <td>
                        <!--<b> <?php echo $orderDetails['source']; ?>,  <?php echo $orderDetails['quor_DeliveryPhone']; ?>, <?php echo $orderDetails['destination']; ?></b>-->
                        <b> <?php echo $consigner; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        Type
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['quor_TypeName']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Status
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['djStatus']; ?> </b>
                    </td>
                </tr>
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
                        Delivery Date
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['quor_DeliveredTime']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Packet Count
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['quor_PacketCount']; ?> </b>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php if ($trDetails['fstr_id'] > 0) { ?> 
            <h4>Stock Request Details</h4>
            <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
                <tbody>
                    <tr>
                        <td>
                            Stock Request No
                        </td>
                        <td>
                            <b> <?php echo $trDetails['fstr_uid']; ?> </b>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Created On
                        </td>
                        <td>
                            <b> <?php echo $trDetails['fstr_createdOn']; ?> </b>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php } ?>
        <?php if ($orderDetails['quor_TransferOrder_id'] > 0) { ?> 
            <h4>Packing Order Details</h4>
            <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
                <tbody>



                    <tr>
                        <td>
                            Packing Order No
                        </td>
                        <td>
                            <b> <?php echo $toDetails['fsto_uid']; ?> </b>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Date
                        </td>
                        <td>
                            <b> <?php echo $toDetails['fsto_createdOn']; ?> </b>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Type
                        </td>
                        <td>
                            <b> <?php echo $fsto_ordertype; ?> </b>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Status
                        </td>
                        <td>
                            <b> <?php echo $fstos_status; ?> </b>
                        </td>
                    </tr>

                </tbody>
            </table>
        <?php } ?>
        <?php
        if ($toDetailItems[0]['fsto_ItemId'] > 0) {
            ?> 
            <h4>Transfer Order Items</h4>
            <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
                <tbody>

                    <tr><td><b>Item Name</b></td><td><b>Quantity</b></td></tr>
                    <?php
                    for ($i = 0; $i < count($toDetailItems); $i++) {
                        $packageType = $db->getFromDB("SELECT cs_package_type_name,ds_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$toDetailItems[$i]['fsto_ItemId']}", true);
                        if ($sourcePyramid == 2) {
                            $packTyp = $packageType['cs_package_type_name'];
                        } else if ($sourcePyramid == 3) {
                            $packTyp = $packageType['ds_package_type_name'];
                        } else if ($sourcePyramid == 4) {
                            $packTyp = $packageType['ds_package_type_name'];
                        }
                        ?>
                        <tr><td><?php
                                if ($toDetailItems[$i]['fsto_ItemId'])
                                    $fsto_ItemName = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$toDetailItems[$i]['fsto_ItemId']}");
                                echo $fsto_ItemName;
                                ?></td>
                            <td><?php echo $toDetailItems[$i]['fsto_ItemQty'] . ' ' . $packTyp; ?></td>
                        </tr>
                    <?php } ?> 

                </tbody>
            </table>
            <?php
        }
    } else {
        ?>
        sorry there is no available data to display
    <?php } ?>




</html>

