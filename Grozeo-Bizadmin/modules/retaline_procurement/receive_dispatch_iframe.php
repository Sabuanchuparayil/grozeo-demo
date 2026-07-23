
<?php
$fsto_id = $quor_TransferOrder_id;
$quorder = $db->getFromDB("SELECT quor_Type,quor_QugeoDeliveryDDBOrderId,quor_QugeoDeliveryDDBDriverId FROM `qugeo_order` WHERE quor_id = {$quor_id} ", true);
$orderDetails = $db->getFromDb("SELECT fsto_uid,fsto_createdOn,fsto_destination,(SELECT br_Name FROM finascop_branch where br_ID = fsto_source) as sourcename,fsto_ordertype,fstr_id,fsto_source,"
        . "CASE WHEN fsto_ordertype=0 THEN 'Branch Transfer' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' WHEN fsto_ordertype=3 THEN 'Return' WHEN fsto_ordertype=4 THEN 'Distribution' END AS fsto_ordertypeName,"
        . "(SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) as branch,fsto_id"
        . " FROM finascop_stock_transfer_order WHERE fsto_id={$fsto_id}", true);

$sourcePyramid = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch where br_ID = {$orderDetails['fsto_source']}");


switch ($quorder['quor_Type']) {
    case '1':

        $nodb = new \cgoDynamiteDB();
        $arrOrder = array();
        $arrAPI = array();
        $arrAPI['PartitionKey'] = array('col' => 'apikey', 'val' => $quorder['quor_QugeoDeliveryDDBDriverId'], 'oper' => '=');
        $arrAPI['getAttributes'] = array('extrainfo', 'id');
        $rsno = $nodb->query('APIHistory', $arrAPI, 'getItem');
        if (isset($rsno) && count($rsno) > 0) {
            $vehno = $rsno['extrainfo'];
            $driverId = $rsno['id'];
            $driverDetails = $db->getFromDB("SELECT d_Name,d_Ph1 FROM qugeo_driver WHERE d_ID = {$driverId}", TRUE);
            $driveerDetails['vehno'] = $vehno['v_no'];
            $driveerDetails['drivername'] = $driverDetails['d_Name'];
            $driveerDetails['driverphone'] = $driverDetails['d_Ph1'];
        }
        // print_r($value);
        break;
    case '2':
        $disdata = $db->getFromDb("SELECT bcd_vehicleNo,bcd_driver,bcd_driverContact,bcd_driverLrgcn,bcd_dispatchDate,bcd_dispatchTime FROM qugeo_order_dispatch WHERE quor_id = {$quor_id} order by bcd_id desc limit 1", true);
        break;
    case '4':
        $quorierDetails = $db->getFromDb("SELECT qoc_courier,qoc_qcn,qoc_date,qoc_time FROM qugeo_order_courier WHERE quor_id = {$quor_id} order by qoc_id desc limit 1", true);
        break;
}

//print_r($orderDetails);
?><html>

    <style>
        .cesstable {
            border: 1px solid #909090;
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
            border-color: -moz-use-text-color #909090 #909090  -moz-use-text-color;
            border-style: solid solid solid solid;
            border-width: 1px 1px 1px 1px;
            height: 22px;
            padding: 0 10px 0 12px;
            vertical-align: middle;
        }
        .cesstable th {
            border-color: -moz-use-text-color #909090 #909090  -moz-use-text-color;
            border-style: solid solid solid solid;
            border-width: 1px 1px 1px 1px;
            height: 22px;
            padding: 0 10px 0 12px;
            vertical-align: middle;
        }
    </style>
    <?php if (!empty($driveerDetails)) { ?>
        <h4>Delivery Details</h4>  
        <table cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>
                <?php if ($driveerDetails['vehno'] != '') { ?>
                    <tr>
                        <td width="275">Vehicle No</td>
                        <td width="675"><?php echo $driveerDetails['vehno']; ?></td>
                    </tr>
                <?php } ?>
                <?php if ($driveerDetails['drivername'] != '') { ?>
                    <tr>
                        <td width="275">Delivery Resource </td>
                        <td width="675"><?php echo $driveerDetails['drivername']; ?></td>
                    </tr>
                <?php } ?>
                <?php if ($driveerDetails['driverphone'] != '') { ?>
                    <tr>
                        <td width="275">Contact No.</td>
                        <td width="675"><?php echo $driveerDetails['driverphone']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <br>
    <?php } ?>
    <?php if (!empty($disdata)) { ?>
        <h4>Dispatch Details</h4>  
        <table cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>
                <?php if ($disdata['bcd_vehicleNo'] != '') { ?>
                    <tr>
                        <td width="275">Vehicle No</td>
                        <td width="675"><?php echo $disdata['bcd_vehicleNo']; ?></td>
                    </tr>
                <?php } ?>
                <?php if ($disdata['bcd_driver'] != '') { ?>
                    <tr>
                        <td width="275">Driver</td>
                        <td width="675"><?php echo $disdata['bcd_driver']; ?></td>
                    </tr>
                <?php } ?>
                <?php if ($disdata['bcd_dispatchTime'] != '') { ?>
                    <tr>
                        <td width="275">Dispatch At</td>
                        <td width="675"><?php echo $disdata['bcd_dispatchTime']; ?></td>
                    </tr>
                <?php } ?>
                <?php if ($disdata['bcd_dispatchDate'] != '') { ?>
                    <tr>
                        <td width="275">Dispatch Date</td>
                        <td width="675"><?php echo $disdata['bcd_dispatchDate']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <br>
    <?php } ?>

    <?php if (!empty($quorierDetails)) { ?>
        <h4>Courier Details</h4>  
        <table cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>
                <?php
                if ($quorierDetails['qoc_courier'] > 0) {
                    $courName = $db->getItemFromDB("SELECT mst_courier_name  FROM mst_courier WHERE mst_courier_id = {$quorierDetails['qoc_courier']}");
                    ?>
                    <tr>
                        <td width="275">Courier</td>
                        <td width="675"><?php echo $courName; ?></td>
                    </tr>
                <?php } ?>
                <?php if ($quorierDetails['qoc_qcn'] != '') { ?>
                    <tr>
                        <td width="275">Driver</td>
                        <td width="675"><?php echo $quorierDetails['qoc_qcn']; ?></td>
                    </tr>
                <?php } ?>
                <?php if ($quorierDetails['qoc_date'] != '') { ?>
                    <tr>
                        <td width="275">Date</td>
                        <td width="675"><?php echo $disdata['qoc_date']; ?></td>
                    </tr>
                <?php } ?>
                <?php if ($quorierDetails['qoc_time'] != '') { ?>
                    <tr>
                        <td width="275">Time</td>
                        <td width="675"><?php echo $quorierDetails['qoc_time']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <br>
    <?php } ?>

    <?php
    if (!empty($orderDetails)) {

        switch ($orderDetails['fsto_ordertype']) {
            case '0'://cpd to branch
                $parentOrder = $db->getFromDB("SELECT fstr_uid AS paOrderNumber,fstr_createdOn AS paOrderDate FROM finascop_stock_transfer_request WHERE fstr_id = {$orderDetails['fstr_id']}", true);
                break;
            case '1'://b2c
                $parentOrder = $db->getFromDB("SELECT order_order_id AS paOrderNumber,order_confirm_date AS paOrderDate FROM retaline_customer_order WHERE order_id = {$orderDetails['fstr_id']}", true);

                break;
            case '2'://b2b
                $parentOrder = $db->getFromDB("SELECT bbso_SONumber AS paOrderNumber,bbso_SODate AS paOrderDate FROM retaline_B2B_SalesOrder WHERE bbso_id = {$orderDetails['fstr_id']}", true);
                break;
            case '3'://b2c
                $parentOrder = $db->getFromDB("SELECT frrp_uid AS paOrderNumber,frrp_createdOn AS paOrderDate FROM finascop_stock_return_request_packing WHERE frrp_id = {$orderDetails['fstr_id']}", true);

                break;
            case '4'://distribution
                $parentOrder = $db->getFromDB("SELECT rdc_uid AS paOrderNumber,rdc_date AS paOrderDate FROM retaline_distribution_chart WHERE rdc_id = {$orderDetails['fstr_id']}", true);
                break;
        }
        //echo '<pre>';print_r($data);
        // $t_amout =  $data['order_total_amount'] + $data['order_delivery_charge'];
        $countDetails = $db->getMultipleData("SELECT fsto_id,fsto_ItemId,fstro_receivedOn,fstro_receivedTime,fsto_ItemQtyL3Received FROM finascop_stock_transfer_order_details WHERE fsto_id={$orderDetails['fsto_id']}", true);

        $fstoAdditionalItems = $db->getMultipleData("SELECT * FROM retaline_distribution_chart_additional_items WHERE fsto_id = {$quor_TransferOrder_id} AND rdcai_status = 6", true);
//        print_r($countDetails);
//        echo count($countDetails);
        ?>


        <h4>Packing Order Details</h4>  
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>



                <tr>
                    <td>
                        TO No
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['fsto_uid']; ?> </b>
                    </td>
                </tr>

                <tr>
                    <td>
                        From
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['sourcename']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        To
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['branch']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Order Created At
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['fsto_createdOn']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Order Type
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['fsto_ordertypeName']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Order Number
                    </td>
                    <td>
                        <b> <?php echo $parentOrder['paOrderNumber']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Order Date
                    </td>
                    <td>
                        <b> <?php echo $parentOrder['paOrderDate']; ?> </b>
                    </td>
                </tr>

            </tbody>
        </table>
        <h4>Item  Details</h4>  
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>
                <tr>
                    <td>
                        <b> Serial No.</b>
                    </td>
                    <td>
                        <b>Item Name</b>
                    </td>
    <!--                    <td>
                        <b>  MRP</b>
                    </td>-->
                    <td style='text-align: right;padding-right: 10px;'>
                        <b>Ordered Quantity</b>
                    </td>
                    <td style='text-align: right;padding-right: 10px;'>
                        <b>Received Quantity</b>
                    </td>
                    <td>
                        <b> Received Date</b>
                    </td>
                    <td>
                        <b> Received Time</b>
                    </td>
                </tr>

                <?php
                for ($i = 0; $i < count($countDetails); $i++) {
                    if ($countDetails[$i]['fsto_ItemId'] > 0) {
                        $itemDetails = $db->getFromDb("SELECT fsto_ItemId,(SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = fsto_ItemId) as item_name,fsto_ItemQty,fstro_receivedOn,fstro_receivedTime,fsto_ItemQtyL3Received FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id} AND fsto_ItemId = {$countDetails[$i]['fsto_ItemId']}", true);
                        $itemMRP = $db->getItemFromDb("SELECT mrp FROM finascop_stock_branch_inventory WHERE stit_id={$countDetails[$i]['fsto_ItemId']} AND branch_id={$orderDetails['fsto_source']}");
//                        $packageType = $db->getFromDB("SELECT cs_package_type_name,ds_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$countDetails[$i]['fsto_ItemId']}", true);
//                        if ($sourcePyramid == 2) {
//                            $packTyp = $packageType['cs_package_type_name'];
//                        } else if ($sourcePyramid == 3) {
//                            $packTyp = $packageType['ds_package_type_name'];
//                        } else if ($sourcePyramid == 4) {
//                            $packTyp = $packageType['ds_package_type_name'];
//                        }
                        $packTyp = $db->getItemFromDB("SELECT least_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$countDetails[$i]['fsto_ItemId']}");
                        ?>
                        <tr>
                            <td>
                                <?php echo $i + 1; ?> 
                            </td>
                            <td>
                                <?php echo $itemDetails['item_name']; ?> 
                            </td>
            <!--                            <td>
                                <b> <?php echo $itemMRP; ?> 
                            </td>-->
                            <td style='text-align: right;padding-right: 10px;'>
                                <?php echo $itemDetails['fsto_ItemQty'] . ' ' . $packTyp; ?> 
                            </td>
                            <td style='text-align: right;padding-right: 10px;'>
                                <?php echo $itemDetails['fsto_ItemQtyL3Received'] . ' ' . $packTyp; ?> 
                            </td>
                            <td>
                                <?php echo $itemDetails['fstro_receivedOn']; ?> 
                            </td>
                            <td>
                                <?php echo $itemDetails['fstro_receivedTime']; ?> 
                            </td>
                        </tr>


                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
        <?php if (count($fstoAdditionalItems) > 0) { ?>
            <h4>Additional Item  Details</h4>  
            <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
                <tbody>
                    <tr>
                        <td>
                            <b> Serial No.</b>
                        </td>
                        <td>
                            <b>Item Name</b>
                        </td>
                        <td style='text-align: right;padding-right: 10px;'>
                            <b> Quantity</b>
                        </td>
                        <td>
                            <b>  Date</b>
                        </td>
                    </tr>

                    <?php
                    for ($i = 0; $i < count($fstoAdditionalItems); $i++) {
                        if ($fstoAdditionalItems[$i]['rdcai_ItemId'] > 0) {
                            $itemDetails = $db->getFromDb("SELECT rdcai_ItemId,(SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = rdcai_ItemId) as item_name,rdcai_ReceivedItemQty,rdcai_createdOn FROM retaline_distribution_chart_additional_items WHERE fsto_id = {$fsto_id} AND rdcai_ItemId = {$fstoAdditionalItems[$i]['rdcai_ItemId']}", true);
                            $itemMRP = $db->getItemFromDb("SELECT mrp FROM finascop_stock_branch_inventory WHERE stit_id={$fstoAdditionalItems[$i]['rdcai_ItemId']} AND branch_id={$orderDetails['fsto_source']}");
                            if ($sourcePyramid != 4) {
                                $packTyp = $db->getItemFromDB("SELECT least_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$countDetails[$i]['fsto_ItemId']}");
                            } else {
                                $packTyp = '';
                            }
                            ?>
                            <tr>
                                <td>
                                    <?php echo $i + 1; ?> 
                                </td>
                                <td>
                                    <?php echo $itemDetails['item_name']; ?> 
                                </td>
                                <td style='text-align: right;padding-right: 10px;'>
                                    <?php echo $itemDetails['rdcai_ReceivedItemQty'] . ' ' . $packTyp; ?> 
                                </td>
                                <td>
                                    <?php echo $itemDetails['rdcai_createdOn']; ?> 
                                </td>
                            </tr>


                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
            <?php
        }
    } else {
        ?>
        sorry there is no available data to display
    <?php } ?>
</html>