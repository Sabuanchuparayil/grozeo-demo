<?php
//User Id for the Particular variable
$order_id = $_REQUEST['order_auto_id'];
$tabId = $_REQUEST['tabId'];

global $db;
if ($order_id > 0) {
    if ($tabId == 1) { //BranchDetails
        $storeBranches = $db->getMulipleData("SELECT br_Name,br_City,branch_shortname,br_Email,br_Phone,br_Incharge,br_GST FROM finascop_branch WHERE br_storeGroup = {$order_id} LIMIT 10", true);
    }
    if ($tabId == 3) { //CustomerOrders
        $custOrders = $db->getMulipleData("SELECT order_order_id,DATE_FORMAT(order_confirm_date,'%d-%m-%Y') as order_confirm_date,
        total,order_delivery_charge,admin_description as order_status,order_total_gst 
        FROM retaline_customer_order bco inner join retaline_customer_order_status bcos ON bcos.status_id = bco.status_id WHERE order_customer_id = {$order_id} ORDER BY order_id DESC LIMIT 10", true);
    }
    if ($tabId == 2) {
        $data = $db->getFromDB(" SELECT order_id ,order_order_id, order_total_amount,order_delivery_charge,
    order_customer_id, payment_mode,order_method,
        admin_description as order_status,order_total_gst,DATE_FORMAT(order_confirm_date,'%d-%m-%Y') as order_confirm_date,
        total,order_app_version,order_app_os "
            . " FROM retaline_customer_order bco "
            . " inner join retaline_customer_order_status bcos ON bcos.status_id = bco.status_id"
            . " WHERE order_id =' " . $order_id . "'", true);

        $pdts = $db->getMultipleData("SELECT item_product_id,(SELECT stit_SKU 
                FROM `finascop_stock_itemmaster` WHERE stit_ID=item_product_id) AS product_name,item_order_qty,
                item_price,item_cgst,item_amount,
                item_sales_price
                FROM retaline_customer_order_items
                WHERE customer_order_id ={$order_id}", true);
        $customerDetails = $db->getFromDB("SELECT cust_customer_name,cust_email,cust_mobile,cust_walletbalance "
            . "FROM retaline_customer WHERE cust_id = {$data['order_customer_id']}", true);
        $deliveryDetails = $db->getFromDB("SELECT order_pin,order_house_no,order_house_name,order_land_mark,order_city,order_latitude,"
            . "order_longitude FROM retaline_customer_order_delivery_address WHERE customer_order_id = {$order_id}", true);
        $paymentDetails = $db->getMultipleData("SELECT fees,currency,mojo_id,DATE_FORMAT(created_at,'%d-%m-%Y') as  created_at,"
            . "amount,instamojo_id,status,redirect_url,payment_status,id "
            . "FROM retaline_paymentgateway_instamojo WHERE order_id = {$order_id} ORDER BY id DESC", true);
        $toDetails = $db->getFromDB("SELECT fsto_id,fstr_id FROM finascop_stock_transfer_order WHERE fsto_ordertype = 1 AND fstr_id = {$order_id}", true);
        //echo "SELECT fsto_id,fstr_id FROM finascop_stock_transfer_order WHERE fsto_ordertype = 1 AND fstr_id = {$order_id}";

        if ($toDetails['fsto_id'] > 0) {
            $itemCountDetails = $db->getFromDB("SELECT SUM(fsto_ItemQty) AS fsto_ItemQty,SUM(fsto_pkdQty) AS fsto_pkdQty FROM finascop_stock_transfer_order_details WHERE fsto_id = {$toDetails['fsto_id']}", true);
            //echo "SELECT SUM(fsto_ItemQty) AS fsto_pkdQty,SUM(fsto_pkdQty) AS fsto_pkdQty FROM finascop_stock_transfer_order_details WHERE fsto_id = {$toDetails['fsto_id']}";
        }
    }
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

    .cesstablep {
        font-family: arial;
        font-size: 11px;
        color: #000;
    }
</style>

<?php
if ($tabId == 2) {
    if (!empty($data)) {
        //echo '<pre>';print_r($data);
        $t_amout = $data['total'];
        // $t_amout =  $data['order_total_amount'] + $data['order_delivery_charge'];
?>
        <h4>Order Details</h4>
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>
                <tr>
                    <td>
                        Order No
                    </td>
                    <td>
                        <b> <?php echo $data['order_order_id']; ?> </b>
                    </td>
                </tr>

                <tr>
                    <td>
                        Order Date
                    </td>
                    <td>
                        <b> <?php echo $data['order_confirm_date']; ?> </b>
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
                    <td width="30%">
                        Amount
                    </td>
                    <td>
                        <b> <?php echo $t_amout; ?> </b>
                    </td>
                </tr>

            </tbody>
        </table>
    <?php } else { ?>
        <p class="cesstablep">sorry there is no available data to display</p>
    <?php } ?>

    <?php if (!empty($pdts)) { ?>
        <h4>Product Details</h4>
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>
                <tr>
                    <td width="30%"><strong>Name</strong></td>
                    <td width="20%"><strong>Rate</strong></td>
                    <td width="20%"><strong>Quantity</strong></td>
                    <td width="25%"><strong>Amount</strong></td>
                </tr>

                <?php
                $sum = 0;
                $price_sum = 0;
                $with_tax = 0;
                foreach ($pdts as $key2 => $value2) {
                    $packageType = $db->getFromDB("SELECT cs_package_type_name,ds_package_type_name,least_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$value2['item_product_id']}", true);
                    $sum += $value2['item_order_qty'];
                    $price_sum += $value2['item_amount'];
                    $with_tax += $value2['item_price'];
                    echo "<tr>"
                        . "<td>{$value2['product_name']}</td> <td>{$value2['item_sales_price']}</td>"
                        . "<td>{$value2['item_order_qty']} {$packageType['least_package_type_name']}</td>"
                        . " <td>{$value2['item_price']}</td>"
                        . "</tr> ";
                }
                ?>

                <tr>
                    <td width="25%"><strong>Total</strong></td>
                    <td width="25%"></td>
                    <td width="25%"><strong><?php echo $sum ?></strong></td>
                    <td width="25%"><strong><?php echo $data['total'] ?></strong></td>
                </tr>
            </tbody>
        </table>
        <!-- <h4 style="float:right">Total Product Quantity : <?php //echo $sum            
                                                                ?> </h4> -->
        <h4></h4>
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>
                <tr>
                    <td>
                        Packed By
                    </td>
                    <td>
                        <b> <?php  ?> </b>
                    </td>
                    <td>
                        Time
                    </td>
                    <td>
                        <b> <?php  ?> </b>
                    </td>
                </tr>

                <tr>
                    <td>
                        Delivered By
                    </td>
                    <td>
                        <b> <?php  ?> </b>
                    </td>
                    <td>
                        Time
                    </td>
                    <td>
                        <b> <?php  ?> </b>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php } ?>
    <?php if (!empty($paymentDetails)) { ?>
        <h4>Payment Details</h4>
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>
                <tr>
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

            </tbody>
        </table>

    <?php } ?>
<?php } ?>
<?php if ($tabId == 1) { $sgname = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = {$order_id}");?>
    <h4>Branches of <?php echo $sgname;?></h4>
    <?php if (!empty($storeBranches)) { ?>
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>
                <tr>
                    <td width="30%"><strong>Code</strong></td>
                    <td width="15%"><strong>Name</strong></td>
                    <td width="15%"><strong>City</strong></td>
                    <td width="15%"><strong>Email</strong></td>
                    <td width="10%"><strong>Contact</strong></td>
                </tr>

                <?php
                for ($k = 0; $k < count($storeBranches); $k++) {

                    echo "<tr>"
                        . "<td>{$storeBranches[$k]['branch_shortname']}</td> "
                        . "<td>{$storeBranches[$k]['br_Name']}</td>"
                        . "<td>{$storeBranches[$k]['br_City']}</td>"
                        . " <td>{$storeBranches[$k]['br_Email']}</td>"
                        . " <td>{$storeBranches[$k]['br_Phone']}</td>"
                        . "</tr> ";
                }
                ?>

            </tbody>
        </table>
    <?php } else { ?>
        <p>No stores available.</p>
    <?php } ?>

<?php } ?>
<?php if ($tabId == 3) { ?>
    <h4>Customer Orders</h4>
    <?php if (!empty($custOrders)) { ?>
        <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>
                <tr>
                    <td width="30%"><strong>Order Id</strong></td>
                    <td width="15%"><strong>Order Date</strong></td>
                    <td width="15%"><strong>Total</strong></td>
                    <td width="15%"><strong>Delivery Charge</strong></td>
                    <td width="10%"><strong>GST</strong></td>
                    <td width="15%"><strong>Status</strong></td>
                </tr>

                <?php
                for ($m = 0; $m < count($custOrders); $m++) {

                    echo "<tr>"
                        . "<td>{$custOrders[$m]['order_order_id']}</td> "
                        . "<td>{$custOrders[$m]['order_confirm_date']}</td>"
                        . "<td>{$custOrders[$m]['total']}</td>"
                        . " <td>{$custOrders[$m]['order_delivery_charge']}</td>"
                        . " <td>{$custOrders[$m]['order_total_gst']}</td>"
                        . "<td>{$custOrders[$m]['order_status']}</td>"
                        . "</tr> ";
                }
                ?>

            </tbody>
        </table>
    <?php } else { ?>
        <p>No orders available.</p>
    <?php } ?>
<?php } ?>



</html>