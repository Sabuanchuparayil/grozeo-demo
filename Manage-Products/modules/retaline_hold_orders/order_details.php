<?php
//User Id for the Particular variable
$order_id = $_REQUEST['order_auto_id'];

global $db;
if ($order_id > 0) {

    $data = $db->getFromDB(" SELECT order_id ,order_order_id, order_total_amount,order_delivery_charge,order_customer_id, payment_mode,
            admin_description as order_status,order_total_gst,DATE_FORMAT(order_confirm_date,'%d-%m-%Y') as order_confirm_date,total,order_app_version,order_app_os "
            . " FROM retaline_customer_order bco "
            . " inner join retaline_customer_order_status bcos ON bcos.status_id = bco.status_id"
            . " WHERE order_id =' " . $order_id . "'", true);

    $pdts = $db->getMultipleData("SELECT item_product_id,(SELECT stit_SKU 
                    FROM `finascop_stock_itemmaster` WHERE stit_ID=item_product_id) AS product_name,item_order_qty,
                    item_price,item_cgst,item_amount,
                    item_sales_price
                    FROM retaline_customer_order_items
                    WHERE customer_order_id ={$order_id}", true);
    $customerDetails = $db->getFromDB("SELECT cust_customer_name,cust_email,cust_mobile,cust_walletbalance FROM retaline_customer WHERE cust_id = {$data['order_customer_id']}", true);
    $deliveryDetails = $db->getFromDB("SELECT order_pin,order_house_no,order_house_name,order_land_mark,order_city,order_latitude,order_longitude FROM retaline_customer_order_delivery_address WHERE customer_order_id = {$order_id}", true);
    $paymentDetails = $db->getMultipleData("SELECT fees,currency,mojo_id,DATE_FORMAT(created_at,'%d-%m-%Y') as  created_at,amount,instamojo_id,status,redirect_url,payment_status,id "
            . "FROM retaline_paymentgateway_instamojo WHERE order_id = {$order_id} ORDER BY id DESC", true);
    $toDetails = $db->getFromDB("SELECT fsto_id,fstr_id FROM finascop_stock_transfer_order WHERE fsto_ordertype = 1 AND fstr_id = {$order_id}", true);
}
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
    if (!empty($customerDetails)) {
        ?>
        <h4>Customer  Details</h4>
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>



                <tr>
                    <td>
                        Name
                    </td>
                    <td>
                        <b> <?php echo $customerDetails['cust_customer_name']; ?> </b>
                    </td>
                </tr>

                <tr>
                    <td>
                        Email
                    </td>
                    <td>
                        <b> <?php echo $customerDetails['cust_email']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Mobile
                    </td>
                    <td>
                        <b> <?php echo $customerDetails['cust_mobile']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Wallet Balance
                    </td>
                    <td>
                        <b> <?php echo $customerDetails['cust_walletbalance']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Delivery Address
                    </td>
                    <td>
                        <b> <?php echo $deliveryDetails['order_house_name']; ?> </br>
                            <?php echo $deliveryDetails['order_land_mark'] . ', ' . $deliveryDetails['order_city'] . ' - ' . $deliveryDetails['order_pin']; ?>
                        </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Latitude-Longitude
                    </td>
                    <td>
                        <b> <?php echo $deliveryDetails['order_latitude'] . '-' . $deliveryDetails['order_longitude']; ?> </b>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php } ?>
    <?php
    if (!empty($data)) {
        //echo '<pre>';print_r($data);
        $t_amout = $data['total'];
        // $t_amout =  $data['order_total_amount'] + $data['order_delivery_charge'];
        ?>
        <h4>Order  Details</h4>
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>



                <tr>
                    <td>
                        Amount
                    </td>
                    <td>
                        <b> <?php echo $t_amout; ?> </b>
                    </td>
                </tr>

                <tr>
                    <td>
                        Created On
                    </td>
                    <td>
                        <b> <?php echo $data['order_confirm_date']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Order Status
                    </td>
                    <td>
                        <b> <?php echo $data['order_status']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        Payment Mode
                    </td>
                    <td>
                        <?php
                        switch ($data['payment_mode']) {
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
                        ?>
                        <b> <?php echo $payMod; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        App Version
                    </td>
                    <td>
                        <b> <?php echo $data['order_app_version']; ?> </b>
                    </td>
                </tr>
                <tr>
                    <td>
                        App OS
                    </td>
                    <td>
                        <b> <?php echo $data['order_app_os']; ?> </b>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php } else { ?>
        sorry there is no available data to display
    <?php } ?>

    <?php if (($_SESSION['admin']->IS_RETALINE_LITE != 1) && ($toDetails['fsto_id'] > 0)) { ?>
        <h4>Barcode Details</h4>
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>
                <tr>
                    <td width="30%"><strong>Barcode</strong></td>
                    <td width="20%"><strong>Item Name</strong></td>
                </tr>
                <?php
                $barcodes = $db->getMultipleData("SELECT stiid_barcode,stiid_id FROM finascop_stock_transfer_order_details_barcodes WHERE fsto_id = {$toDetails['fsto_id']}", true);
                foreach ($barcodes as $barcod) {
                    $stiid_itemmastername = $db->getItemFromDB("SELECT stiid_itemmastername FROM finascop_stock_item_inventorydetails WHERE stiid_id = {$barcod['stiid_id']}");
                    ?>
                    <tr><td width = "25%"><?php echo $barcod['stiid_barcode']; ?></td>
                        <td width="25%"><?php echo $stiid_itemmastername; ?></td>
                    </tr>
                <?php }
                ?>

            </tbody>
        </table>
    <?php } ?>
    <?php if (!empty($pdts)) { ?>
        <h4>Product Details</h4>
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody><tr>
                    <td width="30%"><strong>Name</strong></td>
                    <td width="20%"><strong>Quantity</strong></td>
                    <td width="25%"><strong>Price without tax</strong></td>
                    <td width="25%"><strong>Total with tax</strong></td>
                </tr>

                <?php
                $sum = 0;
                $price_sum = 0;
                $with_tax = 0;
                foreach ($pdts as $key2 => $value2) {
                    $sum+= $value2['item_order_qty'];
                    $price_sum+=$value2['item_amount'];
                    $with_tax+=$value2['item_price'];
                    echo "<tr>"
                    . "<td>{$value2['product_name']}</td> "
                    . "<td>{$value2['item_order_qty']}</td>"
                    . "<td>{$value2['item_amount']}</td>"
                    . " <td>{$value2['item_price']}</td>"
                    . "</tr> ";
                }
                ?>
                <tr>
                    <td width="25%"><strong>Handling Charge</strong></td>
                    <td width="25%"><strong><?php echo '-' ?></strong></td>
                    <td width="25%"><strong><?php echo '-' ?></strong></td>
                    <td width="25%"><strong><?php echo $data['order_delivery_charge']; ?></strong></td>
                </tr>
                <tr>
                    <td width="25%"><strong>Total</strong></td>
                    <td width="25%"><strong><?php echo $sum ?></strong></td>
                    <td width="25%"><strong><?php echo $price_sum ?></strong></td>
                    <td width="25%"><strong><?php echo $data['total'] ?></strong></td>
                    <!--<td width="25%"><strong><?php echo $with_tax + $data['order_delivery_charge'] ?></strong></td>-->
                </tr>
            </tbody></table>
           <!-- <h4 style="float:right">Total Product Quantity : <?php //echo $sum         ?> </h4> -->

    <?php } ?>
    <?php if (!empty($paymentDetails)) { ?>
        <h4>Payment Details</h4>
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody><tr>
                    <td width="30%"><strong>ID</strong></td>
                    <td width="15%"><strong>Amount</strong></td>
                    <td width="15%"><strong>Fees</strong></td>
                    <td width="15%"><strong>Payment status</strong></td>
                    <td width="10%"><strong>Status</strong></td>
                    <td width="15%"><strong>Create On</strong></td>
                </tr>

                <?php
                $sum = 0;
                $price_sum = 0;
                $with_tax = 0;
                for ($j = 0; $j < count($paymentDetails); $j++) {
                    if ($paymentDetails[$j]['payment_status'] == 0) {
                        $ps = 'Pending';
                    } else if ($paymentDetails[$j]['payment_status'] == 1) {
                        $ps = 'Completed';
                    } else if ($paymentDetails[$j]['payment_status'] == 2) {
                        $ps = 'Failed';
                    }
                    echo "<tr>"
                    . "<td>{$paymentDetails[$j]['mojo_id']}</td> "
                    . "<td>{$paymentDetails[$j]['amount']}</td>"
                    . "<td>{$paymentDetails[$j]['fees']}</td>"
                    . " <td>{$ps}</td>"
                    . " <td>{$paymentDetails[$j]['status']}</td>"
                    . "<td>{$paymentDetails[$j]['created_at']}</td>"
                    . "</tr> ";
                }
                ?>

            </tbody></table>

    <?php } ?>




</html>
