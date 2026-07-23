<?php
$order_order_id = $_REQUEST['order_order_id'];
$order_id = $_REQUEST['order_id'];

$customerOrderDetails = $db->getFromDB("SELECT order_branch_id,order_customer_id,order_total_amount,order_delivery_charge,order_total_gst,payment_mode,DATE_FORMAT(order_confirm_date,'%a %d %M %Y') AS  order_confirm_date,"
        . "DATE_FORMAT(order_confirmed_on,'%a %d %M %Y %H:%i:%s') AS  order_confirmed_on,order_kfc_amount,order_saved_amount,total,order_discount_amount "
        . " FROM retaline_customer_order WHERE order_id = {$order_id} AND order_order_id = '{$order_order_id}'", true);
        $branchSql = "SELECT br_Name,br_City,br_District,br_State,br_Address,br_pincode,br_Phone,br_Fax FROM finascop_branch WHERE br_ID = {$customerOrderDetails['order_branch_id']}";
        $customerDetails = $db->getFromDB("SELECT cust_customer_name,cust_email,cust_mobile,cust_walletbalance FROM retaline_customer WHERE cust_id = {$customerOrderDetails['order_customer_id']}", true);
$branchDetails = $db->getFromDB($branchSql, true);
$custName = $db->getItemFromDB("SELECT cust_customer_name FROM retaline_customer WHERE cust_id = {$customerOrderDetails['order_customer_id']}");
$custAddress = $db->getFromDB("SELECT order_customer_name,order_house_no,order_house_name,order_land_mark,order_city,order_post,order_state,order_pin,order_country,order_contact_no FROM retaline_customer_order_delivery_address "
        . "WHERE customer_order_id = {$order_id}", true);
$ordeItems = $db->getMultipleData("SELECT item_id,customer_order_id,item_product_id,item_group_id,item_order_qty,item_price,item_sales_price,item_retail_price FROM retaline_customer_order_items WHERE customer_order_id = {$order_id}", true);
$noOfItems = count($ordeItems);
//if ($customerOrderDetails['order_coupon_total'] == 0) {
    $order_amt_total = $customerOrderDetails['order_total_amount'];
    $kfc = ($order_amt_total / 100);
    $amtPayable = floatval($customerOrderDetails['order_total_gst']) + floatval($customerOrderDetails['order_total_amount']) + floatval($customerOrderDetails['order_delivery_charge']) + floatval($kfc);
    $subTotal = floatval($customerOrderDetails['order_total_gst']) + floatval($customerOrderDetails['order_total_amount']);
//} else {
//    $order_amt_total = $customerOrderDetails['order_coupon_total'];
//    $kfc = ($order_amt_total / 100);
//    $amtPayable = floatval($customerOrderDetails['order_total_gst']) + floatval($customerOrderDetails['order_coupon_total']) + floatval($customerOrderDetails['order_delivery_charge']) + floatval($kfc);
//    $subTotal = floatval($customerOrderDetails['order_total_gst']) + floatval($customerOrderDetails['order_coupon_total']);
//}
$amtPayable = number_format($amtPayable, 2);
///order_coupon_percentage,is_order_coupon,order_coupon_total,

$branchSql = "SELECT br_Name,br_City,br_District,br_State,br_Address,br_pincode,br_Phone,br_Fax FROM finascop_branch WHERE br_ID = {$customerOrderDetails['order_branch_id']}";
$branchDetails = $db->getFromDB($branchSql, true);
$pojectName = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'PROJECT_NAME'");
$poCompany = $db->getItemFromDB("SELECT comp_id FROM finascop_branch_company WHERE br_Id = {$customerOrderDetails['order_branch_id']}");
$companyDetails = $db->getFromDB("SELECT comp_name,comp_address,comp_GSTIN,comp_Ph FROM finascop_company WHERE comp_id = {$poCompany}");
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
            <table border="1" cellspacing="0" cellpadding="0" class="top-area-wrap">
                <tbody>
                    <tr>
                        <td class="bo-r pad-td">
                            <div id="logo">
                                <img src="resources/images/logo.png">
                            </div>
                            <div id="company" class="">
                                <div><h3><?php echo $pojectName; ?></h3></div>
                                <div><?php echo $branchDetails['br_Name']; ?></div>
                                <!--<div>COI: NA</div>-->
                                <div><?php echo $branchDetails['br_Address']; ?></div>
                                <div><?php echo $branchDetails['br_City']; ?> - <?php echo $branchDetails['br_pincode']; ?></div>
                                <!--<div>GSTN: <?php echo $companyDetails['comp_GSTIN']; ?> </div>-->
                                <!--<div>TIN: PANAAB222384iZN</div>-->
                            </div>
                        </td>
                        <td class="bo-r">
                            <div id="" class="t-right pr-10">
                                <h1 class="head">Invoice</h1>
                                <div>Invoice No. :  <?php echo $order_order_id; ?></div>
                                <div>Order : <?php echo $order_order_id; ?></div>
                                <div>Date : <?php echo $customerOrderDetails['order_confirm_date']; ?></div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" class="cash">
                            <h3 class="col-b"><?php
if ($customerOrderDetails['payment_mode'] == 1) {
    echo 'CASH ON DELIVERY';
} else {
    echo 'ONLINE PAYMENT';
}
?> ORDER</h3>
                            Delivery Location:  <?php echo $custAddress['order_house_no']; ?>, <?php echo $custAddress['order_house_name']; ?>, <?php echo $custAddress['order_land_mark']; ?>, <?php echo $custAddress['order_city']; ?>, <?php echo $custAddress['order_state']; ?> - <?php echo $custAddress['order_pin']; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </header>
        <main>
            <div class="left-area-block">
                <table border="1" cellspacing="0" cellpadding="0" class="ship-detail-table">
                    <tbody>
                        <tr>
                            <td>
                                <div>Billed To:</div>
                            </td>
                            <td>
                                <div>Order Time: <strong><?php echo $customerOrderDetails['order_confirmed_on']; ?></strong></div>
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="3">
                                <div class="bal"><strong><?php echo $custName; ?></strong></div>
                                <div class="bal"><strong><?php echo $custAddress['order_house_no']; ?>  <?php echo $custAddress['order_house_name']; ?></strong></div>
                                <div class="bal"><strong><?php echo $custAddress['order_land_mark']; ?>, <?php echo $custAddress['order_city']; ?></strong></div>
                                <div class="bal"><strong><?php echo $custAddress['order_state']; ?> PIN: <?php echo $custAddress['order_pin']; ?></strong></div>
                            </td>
                            <td>
                                <div>Delivery Schedule: <strong>Immediate</strong></div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div>Total Amount: <strong><?php echo $customerOrderDetails['total']; ?></strong>  Amount Paid: <strong><?php
                                if ($customerOrderDetails['payment_mode'] == 1) {
                                    echo '0.00';
                                } else {
                                            echo 'Rs. ' . $customerOrderDetails['total'];
                                }
?></strong> </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="bal"><strong>Balance Payable:<?php
                                        if ($customerOrderDetails['payment_mode'] == 1) {
                                            echo 'Rs. ' . $customerOrderDetails['total'];
                                        } else {
                                            echo '0.00';
                                        }
?> </strong></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>


            <div class="left-area-block">
                <table border="1" cellspacing="0" cellpadding="0" class="ship-detail-table">
                    <tbody>
                        <tr>
                            <td>
                                <div>Contact No : <span class="bal"><strong><?php echo $custAddress['order_contact_no']; ?></strong></span></div>
                            </td>
                            <td>
                                <div>Total Number of Items : <span class="bal"><strong><?php echo $noOfItems; ?> </strong></span>&nbsp;Numbers</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <table border="1" cellspacing="0" cellpadding="0" class="ship-detail-table">
                <tbody>
                    <tr>
                        <td>
                            <div><strong>Sl</strong></div>
                        </td>
                        <td class="wid">
                            <div><strong>Item Name</strong></div>
                        </td>
                        <td>
                            <div><strong>MRP</strong></div>
                        </td>
                        <td>
                            <div><strong>Rate</strong></div>
                        </td>

                            <td>
                            <div><strong>Qty</strong></div>
                        </td>
                        <td>
                            <div><strong>Tax%</strong></div>
                        </td>
                        <td>
                            <div><strong>Amount</strong></div>
                        </td>
                    </tr>
<?php
$totalMrp = 0;
for ($i = 0; $i < $noOfItems; $i++) {
    $j = $i + 1;
    ?>
                        <tr>
                            <td>
                                <div><?php echo $j; ?></div>
                            </td>
                            <td class="wid">
                                <div><?php
                    $itemName = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$ordeItems[$i]['item_product_id']} ");
                    echo $itemName;
    ?></div>
                            </td>
                            <td>
                                <div><?php
                                    if ($ordeItems[$i]['item_retail_price'] > 0) {
                                        $itemMRP = $ordeItems[$i]['item_retail_price'];
                                    echo $itemMRP;
                                } else {
                                    echo 0;
                                }

                                $totalMrp = $totalMrp + (floatval($itemMRP) * $ordeItems[$i]['item_order_qty']);
    ?></div>
                            </td>
                            <td>
                                <div><?php echo $ordeItems[$i]['item_sales_price']; ?></div>
                            </td>
                            <?php //if ($customerOrderDetails['order_coupon_percentage'] > 0) { ?>
    <!--                                <td>
                                    <div><strong><?php echo $customerOrderDetails['order_coupon_percentage']; ?></strong></div>
                                </td>-->
                            <?php //} ?>
                            <td>
                                <div><?php echo $ordeItems[$i]['item_order_qty']; ?></div>
                            </td>
                            <td>
                                <div><?php
                        $itemTax = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$ordeItems[$i]['item_product_id']} ");
                        echo number_format($itemTax, 2);
    ?></div>
                            </td>
                            <td class="t-right">
                                <div><?php echo $ordeItems[$i]['item_price']; ?></div>
                            </td>
                        </tr>

<?php } ?>
                    <?php
                    //$discount = floatval($totalMrp) - floatval($amtPayable);
                    //$discount = floatval($totalMrp) - floatval($order_amt_total);
                    $discount = $customerOrderDetails['order_saved_amount'];
                    $discountPercent = ($discount / floatval($totalMrp)) * 100;
                    $taxDiv = floatval($customerOrderDetails['order_total_gst']) / 2;
                    $taxDiv = number_format($taxDiv,2);
                    ?>

                    <tr>
                        <td class="t-left" colspan="2" rowspan="6">
                            <div><h3>YOU SAVED</h3></div><br>
                            <div class="ten"><?php echo number_format($discountPercent, 2); ?>% = ?<?php echo number_format($discount, 2); ?></div><br>
                            <div><h3>Thank You for Shopping</h3></div>
                        </td>
                        <td class="t-right" colspan="4"><div>Total (Befor Tax)</div></td>
                        <td class="t-right"><div><?php echo $order_amt_total; ?></div></td>

                    </tr>
                    <tr>

                        <td class="t-right" colspan="4"><div>SGST</div></td>
                        <td class="t-right"><div><?php echo $taxDiv; ?></div></td>
                    </tr>
                    <tr>

                        <td class="t-right" colspan="4"><div>CGST</div></td>
                        <td class="t-right"><div><?php echo $taxDiv; ?></div></td>
                    </tr>
                    <tr>

                        <td class="t-right" colspan="4"><div>KFC</div></td>
                        <td class="t-right"><div><?php echo $customerOrderDetails['order_kfc_amount']; ?></div></td>
                    </tr>
                    <tr>

                        <td class="t-right" colspan="4"><div>Handling Charge</div></td>
                        <td class="t-right"><div><?php echo $customerOrderDetails['order_delivery_charge']; ?></div></td>
                    </tr>
                    <tr>

                        <td class="t-right" colspan="4"><div>Discount</div></td>
                        <td class="t-right"><div><?php echo $customerOrderDetails['order_discount_amount']; ?></div></td>
                    </tr>
                    <tr>

                        <td class="t-right" colspan="6"><div class="totall">Grand Total</div></td>
                        <td class="t-right"><div class="totall"><?php echo $customerOrderDetails['total']; ?></div></td>
<!--                        <td class="t-right"><div class="totall"><?php echo $amtPayable; ?></div></td>-->
                    </tr>

                    <tr>

                        <td class="t-right" colspan="7"><div class="totall-txt">Rupees <?php echo numberTowords($customerOrderDetails['total']); ?> only</div></td>
                    </tr>



                </tbody>
            </table>

        </main>
    </body>

</html>