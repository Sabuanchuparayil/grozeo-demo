<?php
$fsto_id = $_REQUEST['fsto_id'];
$orderDetails = $db->getFromDb("SELECT fsto_uid,fsto_createdOn,fsto_destination,(SELECT br_Name FROM finascop_branch where br_ID = fsto_source) as sourcename,fsto_ordertype,fstr_id,fsto_source,"
        . "CASE WHEN fsto_ordertype=0 THEN 'CPD TO BR' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' END AS fsto_ordertypeName,fsto_openingtime,fsto_ismanualpacking,fsto_manualpackinguserid,fsto_assigned_boy,"
        . "CASE WHEN fsto_ordertype = 0 THEN (SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) "
        . "WHEN fsto_ordertype = 1 THEN (SELECT cust_customer_name FROM retaline_customer WHERE cust_id = fsto_destination) "
        . "WHEN fsto_ordertype = 2 THEN (SELECT b2b_Customer_Name FROM retaline_B2Bcustomer where b2b_Customer_ID = fsto_destination)"
        . "WHEN fsto_ordertype = 3 THEN (SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) END AS customer,fsto_id"
        . " FROM finascop_stock_transfer_order WHERE fsto_id={$fsto_id}", true);

if ($orderDetails['fsto_ismanualpacking'] == 1) {
    $packType = 'Manual Packing';
    $packUser = $db->getItemFromDB("SELECT CONCAT(FirstName,' ',LastName) FROM finascop_usr_profile WHERE UserId = {$orderDetails['fsto_manualpackinguserid']}");

} else {
    $packType = 'Packsure';
    if ($orderDetails['fsto_assigned_boy'] > 0)
        $packUser = $db->getItemFromDB("SELECT name FROM retaline_godown_boy WHERE id = {$orderDetails['fsto_assigned_boy']}");
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
                }
                $parentOrder = $db->getFromDB("SELECT fstr_uid AS paOrderNumber,fstr_createdOn AS paOrderDate FROM finascop_stock_transfer_request WHERE fstr_id = {$orderDetails['fstr_id']}", true);
                break;
            case '1'://b2c
                $fsto_ordertypeName = 'B2C';
                $parentOrder = $db->getFromDB("SELECT order_order_id AS paOrderNumber,order_confirm_date AS paOrderDate FROM retaline_customer_order WHERE order_id = {$orderDetails['fstr_id']}", true);

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
                    <td>
                        <b> Serial No.</b>
                    </td>
                    <td>
                        <b>Item Name</b>
                    </td>
    <!--                    <td>
                        <b>  MRP</b>
                    </td>-->
                    <td>
                        <b> Quantity</b>
                    </td>
                </tr>

                <?php
                for ($i = 0; $i < count($countDetails); $i++) {
                    if ($countDetails[$i]['fsto_ItemId'] > 0) {
                        $itemDetails = $db->getFromDb("SELECT fsto_ItemId,(SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = fsto_ItemId) as item_name,fsto_ItemQty FROM finascop_stock_transfer_order_details WHERE fsto_id = {$fsto_id} AND fsto_ItemId = {$countDetails[$i]['fsto_ItemId']}", true);
                        $itemMRP = $db->getItemFromDb("SELECT mrp FROM finascop_stock_branch_inventory WHERE stit_id={$countDetails[$i]['fsto_ItemId']} AND branch_id={$orderDetails['fsto_source']}");
                        $packageType = $db->getFromDB("SELECT cs_package_type_name,ds_package_type_name,least_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$itemDetails['fsto_ItemId']}", true);
//                        if ($sourcePyramid == 2) {
//                            $packTyp = $packageType['cs_package_type_name'];
//                        } else if ($sourcePyramid == 3) {
//                            $packTyp = $packageType['ds_package_type_name'];
//                        }else if ($sourcePyramid == 4) {
//                            $packTyp = $packageType['ds_package_type_name'];
//                        }
                        $packTyp = $packageType['least_package_type_name'];
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
                            <td>
                                <?php echo $itemDetails['fsto_ItemQty'] . ' ' . $packTyp; ?> 
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

