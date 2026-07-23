<?php
$fsto_id = $_REQUEST['fsto_id'];
$orderDetails = $db->getFromDB("SELECT fsto_uid,fsto_createdOn,fsto_destination,(SELECT br_Name FROM finascop_branch where br_ID = fsto_source) as sourcename,fsto_ordertype,fstr_id,fsto_source,"
        . "CASE WHEN fsto_ordertype=0 THEN 'Branch Transfer' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' END AS fsto_ordertypeName,fsto_openingtime,fsto_ismanualpacking,fsto_manualpackinguserid,fsto_assigned_boy,"
        . "CASE WHEN fsto_ordertype = 0 THEN (SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) "
        . "WHEN fsto_ordertype = 1 THEN (SELECT cust_customer_name FROM retaline_customer WHERE cust_id = fsto_destination) "
        . "WHEN fsto_ordertype = 2 THEN (SELECT b2b_Customer_Name FROM retaline_B2Bcustomer where b2b_Customer_ID = fsto_destination)"
        . "WHEN fsto_ordertype = 3 THEN (SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) END AS customer,fsto_id"
        . " FROM finascop_stock_transfer_order WHERE fsto_id={$fsto_id}", true);

if ($orderDetails['fsto_ismanualpacking'] == 1) {
    $packType = 'Manual Packing';
    $packUser = $db->getItemFromDB("SELECT CONCAT(FirstName,' ',LastName) FROM finascop_usr_profile WHERE UserId = {$orderDetails['fsto_manualpackinguserid']}");
} else {

    if ($orderDetails['fsto_assigned_boy'] > 0) {
    $packType = 'Packsure';
        $packUser = $db->getItemFromDB("SELECT name FROM retaline_godown_boy WHERE id = {$orderDetails['fsto_assigned_boy']}");
}
}

//        print_r($orderDetails);
?>

<html>
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
        $sourcePyramid = $db->getItemFromDB("SELECT br_PyramidLevel FROM finascop_branch where br_ID = {$orderDetails['fsto_source']}");
        switch ($orderDetails['fsto_ordertype']) {
            case '0'://cpd to branch

                if ($sourcePyramid == 2) {
                    $fsto_ordertypeName = 'CS to Distributor';
                } else if ($sourcePyramid == 3) {
                    $fsto_ordertypeName = 'Distributor to Retailor';
                } else {
                    $fsto_ordertypeName = 'Branch Transfer';
                }
                $parentOrder = $db->getFromDB("SELECT fstr_uid AS paOrderNumber,fstr_createdOn AS paOrderDate FROM finascop_stock_transfer_request WHERE fstr_id = {$orderDetails['fstr_id']}", true);
                break;
            case '1'://b2c
                $fsto_ordertypeName = 'B2C';
                $parentOrder = $db->getFromDB("SELECT order_order_id AS paOrderNumber,order_confirm_date AS paOrderDate,order_slot_id,order_slot_date,payment_mode FROM retaline_customer_order WHERE order_id = {$orderDetails['fstr_id']}", true);
                switch ($parentOrder['payment_mode']) {
                    case 1:
                        $payMod = 'Pay On Delivery';
                break;
                    case 2:
                        $payMod = 'Online Payment';
                        break;
                    case 3:
                        $payMod = 'Wallet';
                        break;
                    case 4:
                        $payMod = 'COD with Wallet';
                        break;
                    case 5:
                        $payMod = 'Online with Wallet';
                        break;
                    case 6:
                        $payMod = 'Online on Delivery';
                        break;
                    case 7:
                        $payMod = 'Cash on Delivery';
                        break;
                }
                break;
            case '2'://b2b
                $fsto_ordertypeName = 'B2B';
                $parentOrder = $db->getFromDB("SELECT bbso_SONumber AS paOrderNumber,bbso_SODate AS paOrderDate FROM retaline_B2B_SalesOrder WHERE bbso_id = {$orderDetails['fstr_id']}", true);
                break;            
            case '3'://branch to cpd

                if ($sourcePyramid == 3) {
                    $fsto_ordertypeName = 'Distributor to CS';
                } else if ($sourcePyramid == 4) {
                    $fsto_ordertypeName = 'Retailor to Distributor';
                }
                $parentOrder = $db->getFromDB("SELECT fstr_uid AS paOrderNumber,fstr_createdOn AS paOrderDate FROM finascop_stock_transfer_request WHERE fstr_id = {$orderDetails['fstr_id']}", true);
                break;
            case '4'://cpd to branch
            
                $fsto_ordertypeName = 'Distribution';
                $parentOrder = $db->getFromDB("SELECT rdc_uid AS paOrderNumber,rdc_createdOn AS paOrderDate FROM retaline_distribution_chart WHERE rdc_id = {$orderDetails['fstr_id']}", true);
                break;
        }
        //echo '<pre>';print_r($data);
        // $t_amout =  $data['order_total_amount'] + $data['order_delivery_charge'];
        $countDetails = $db->getMultipleData("SELECT fsto_id,fsto_ItemId FROM finascop_stock_transfer_order_details WHERE fsto_id={$orderDetails['fsto_id']}", true);
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
                        Consigner
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['sourcename']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Consignee
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['customer']; ?> </b>
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
                        <b> <?php echo $fsto_ordertypeName; ?> </b>
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
                <?php if ($orderDetails['fsto_ordertype'] == 1) { ?>
                <tr>
                    <td>
                            Payment Mode
                        </td>
                        <td>
                            <b> <?php echo $payMod; ?> </b>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td>
                        Scheduler opening Time
                    </td>
                    <td>
                        <b> <?php echo $orderDetails['fsto_openingtime']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Pack Type
                    </td>
                    <td>
                        <b> <?php echo $packType; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Packing User
                    </td>
                    <td>
                        <b> <?php echo $packUser; ?> </b>
                    </td>
                </tr>
                <?php if (($orderDetails['fsto_ordertype'] == 1) && ($parentOrder['order_slot_id'] > 0)) { ?>
                <tr>
                    <td>
                        Slot Date
                    </td>
                    <td>
                        <b> <?php echo $parentOrder['order_slot_date']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Slot Time
                    </td>
                    <td>
                            <b> <?php
                                $slotTime = $db->getItemFromDB("SELECT CONCAT(DATE_FORMAT(rbds_time_from,'%h:%i %p'),'-',DATE_FORMAT(rbds_time_to,'%h:%i %p')) FROM retaline_branch_delivery_slot WHERE rbds_id = {$parentOrder['order_slot_id']}");
                                echo $slotTime;
                                ?> </b>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
<!--
        <h4>Request Details</h4>  
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>
                <tr>
                    <td>
                        Order Type
                    </td>
                    <td>
                        <b> <?php echo $fsto_ordertypeName; ?> </b>
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
        -->
        <h4>Item  Details</h4>  
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>
                <tr>
    <!--                    <td>
                        <b> Serial No.</b>
                    </td>-->
                    <td>
                        <b>Item Name</b>
                    </td>
    <!--                    <td>
                        <b>  MRP</b>
                    </td>-->
                    <td>
                        <b> No. of Items</b>
                    </td>
                    <td>
                        <b>Picked Items</b>
                    </td>
                    <td>
                        <b> Required Qty</b>
                    </td>
                    <td>
                        <b> Picked Qty</b>
                    </td>
                </tr>

                <?php
                for ($i = 0; $i < count($countDetails); $i++) {
                    if ($countDetails[$i]['fsto_ItemId'] > 0) {
                        $itemDetails = $db->getFromDB("SELECT fsto_ItemId,(SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = fsto_ItemId) as item_name,fsto_ItemQty,fsto_stockValue,fsto_pkdQty FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id} AND fsto_ItemId = {$countDetails[$i]['fsto_ItemId']}", true);
                        $itemMRP = $db->getItemFromDb("SELECT mrp FROM finascop_stock_branch_inventory WHERE stit_id={$countDetails[$i]['fsto_ItemId']} AND branch_id={$orderDetails['fsto_source']}");
                        $packageType = $db->getFromDB("SELECT cs_package_type_name,ds_package_type_name,least_package_type_name,stit_ParentItemId,stit_ConvertCalcRate FROM finascop_stock_itemmaster WHERE stit_ID = {$itemDetails['fsto_ItemId']}", true);

                        if ($packageType['stit_ParentItemId'] > 0) {
                            $orderQty = $packageType['stit_ConvertCalcRate'] * $itemDetails['fsto_ItemQty'];
                            $packQty = $itemDetails['fsto_stockValue'];
                        $packTyp = $packageType['least_package_type_name'];
                        } else {
                            $orderQty = 0;
                            $packQty = 0;
                                $packTyp = '';
                            }
                        ?>
                        <tr>
            <!--                            <td>
                            <?php //echo $i + 1; ?> 
                            </td>-->
                            <td>
                                <?php echo $itemDetails['item_name']; ?> 
                            </td>
                            <td style='text-align: right;padding-right: 10px;'>
                                <?php echo floatval($itemDetails['fsto_ItemQty']); ?> 
                            </td>
                            <td style='text-align: right;padding-right: 10px;'>
                                <?php echo floatval($itemDetails['fsto_pkdQty']); ?> 
                            </td>
            <!--                            <td>
                                <b> <?php echo $itemMRP; ?> 
                            </td>-->
                            <td style='text-align: right;padding-right: 10px;'>
                                <?php // echo $itemDetails['fsto_ItemQty'] . ' ' . $packTyp; ?> 
                                <?php echo $orderQty . ' ' . $packTyp; ?> 
                            </td>
                            <td style='text-align: right;padding-right: 10px;'>
                                <?php // echo $itemDetails['fsto_pkdQty'] . ' ' . $packTyp; ?> 
                                <?php echo $packQty . ' ' . $packTyp; ?> 
                            </td>
                        </tr>


                        <?php
                    }
                }
                ?>
            </tbody>
        </table>

        <?php
        $orderPackDetails = $db->getMultipleData("SELECT rtopd_id,rtopd_orderType,rtopd_packets,rtopd_packaging,rtopd_packetweigh FROM retaline_transfer_order_pack_details WHERE rtopd_fstoId = {$fsto_id}", true);
        if ($orderPackDetails[0]['rtopd_id'] > 0) {
            ?>
            <h4>Packing  Details</h4>  
            <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
                <tbody>
                    <tr>
                            <td>
                            <b> Serial No.</b>
                            </td>
                        <td>
                            <b>Packet No</b>
                        </td>
                        <td>
                            <b>  Package</b>
                        </td>
                        <td>
                            <b> Weight</b>
                        </td>
                        </tr>

                    <?php
                    for ($p = 0; $p < count($orderPackDetails); $p++) {
                        $package = $db->getItemFromDB("SELECT rpckm_name FROM retaline_package_master WHERE rpckm_id = {$orderPackDetails[$p]['rtopd_packaging']} ")
                        ?>
                        <tr>
                            <td>
                                <?php echo $p + 1; ?> 
                            </td>
                            <td>
                                <?php echo $orderPackDetails[$p]['rtopd_packets']; ?> 
                            </td>
                            <td>
                                <b> <?php echo $package; ?> 
                            </td>
                            <td>
                                <?php echo $orderPackDetails[$p]['rtopd_packetweigh']; ?> 
                            </td>
                        </tr>


                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
        <?php
    } else {
        ?>
        sorry there is no available data to display
    <?php } ?>



</html>

