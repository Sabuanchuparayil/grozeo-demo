<?php
$order_order_id = $_REQUEST['order_generated_id'];
$order_id = $_REQUEST['order_id'];
$branchSql = "SELECT br_Name,br_City,br_District,br_State,br_Address,br_pincode,br_Phone,br_Fax FROM finascop_branch WHERE br_ID = {$_SESSION['admin']->finascop_current_branch_id}";
$branchDetails = $db->getFromDB($branchSql, true);
$customerOrderDetails = $db->getFromDB("SELECT order_customer_id,order_total_amount,order_delivery_charge,order_total_gst,payment_mode,DATE_FORMAT(order_confirm_date,'%a %d %M %Y') AS  order_confirm_date,"
        . "DATE_FORMAT(order_confirmed_on,'%a %d %M %Y %H:%i:%s') AS  order_confirmed_on,order_kfc_amount,order_saved_amount,total,order_packedbags_count "
        . " FROM retaline_customer_order WHERE order_id = {$order_id} AND order_order_id = '{$order_order_id}'", true);
$custName = $db->getItemFromDB("SELECT cust_customer_name FROM retaline_customer WHERE cust_id = {$customerOrderDetails['order_customer_id']}");
$custAddress = $db->getFromDB("SELECT order_customer_name,order_house_no,order_house_name,order_land_mark,order_city,order_post,order_state,order_pin,order_country,order_contact_no FROM retaline_customer_order_delivery_address "
        . "WHERE customer_order_id = {$order_id}", true);
$ordeItems = $db->getMultipleData("SELECT item_id,customer_order_id,item_product_id,item_group_id,item_order_qty,item_price,item_sales_price FROM retaline_customer_order_items WHERE customer_order_id = {$order_id}", true);
$noOfItems = count($ordeItems);
//if ($customerOrderDetails['order_coupon_total'] == 0) {
$order_amt_total = $customerOrderDetails['order_total_amount'];
$kfc = ($order_amt_total / 100);
$amtPayable = floatval($customerOrderDetails['order_total_gst']) + floatval($customerOrderDetails['order_total_amount']) + floatval($customerOrderDetails['order_delivery_charge']) + floatval($kfc);
$subTotal = floatval($customerOrderDetails['order_total_gst']) + floatval($customerOrderDetails['order_total_amount']);
if (!empty($_REQUEST['printCount'])) {
    $printCount = $_REQUEST['printCount'];
} else {
    $printCount = $customerOrderDetails['order_packedbags_count'];
    }
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <title></title>
        <link href="https://fonts.googleapis.com/css?family=Lato:100,100i,300,300i,400,400i,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="resources/css/orderinvoice-style.css" media="all" />
    </head>

    <body>
        <header class="clearfix">

        </header>
        <main>
            <div class="left-area-block">
                <?php for ($p = 1; $p <= $printCount; $p++) { ?>
                    <table border="1" cellspacing="0" cellpadding="0" class="ship-detail-table">
                        <tbody>

                        <tr>
                                <td rowspan="5">
                                    <div>Order No : <span class="bal"><strong><?php echo $order_order_id; ?></strong></span> / Packet: <span class="bal"><strong><?php echo $p ; ?></strong></span></div>
                                </td>
                                <td rowspan="5">
                                    <div class="bal"><strong><?php echo $custAddress['order_customer_name']; ?></strong></div>
                                    <div class="bal"><strong><?php echo $custAddress['order_house_no']; ?> <?php echo $custAddress['order_house_name']; ?></strong></div>
                                    <div class="bal"><strong><?php echo $custAddress['order_land_mark']; ?> <?php echo $custAddress['order_city']; ?></strong></div>
                                    <div class="bal"><strong><?php echo $custAddress['order_state']; ?> PIN: <?php echo $custAddress['order_pin']; ?></strong></div>
                                    <div>Contact No : <span class="bal"><strong><?php echo $custAddress['order_contact_no']; ?></strong></span></div>
                                </td>
                                <td>Barcodes :
                        <?php
                                    $barcodeType = "code128";
                                    $barcodeDisplay = "horizontal";
                                    $barcodeSize = "20";
                                    $printText = "true";
                                    $barcodeText = $db->getMultipleData("SELECT stiid_barcode FROM retaline_customer_order_items_barcodes where customer_order_id = {$order_id}");
                                    $quantity = count($barcodeText);
                                    if ($barcodeText != '') {
                                        for ($i = 0; $i < $quantity; $i++) {
                                            echo '<div class="bcimg"><img class="barcode" alt="' . $barcodeText[$i] . '" src="../barcode.php?text=' . $barcodeText[$i] . '&codetype=' . $barcodeType . '&orientation=' . $barcodeDisplay . '&size=' . $barcodeSize . '&print=' . $printText . '"/></div>';
                            }
                            }
                            ?>
                                </td>
                            </tr>
                        </tbody>
                        </table>
                <?php } ?>
            </div>
        </main>
    </body>

</html>