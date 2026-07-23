<?php
$order_order_id = $_REQUEST['order_order_id'];
$order_id = $_REQUEST['order_id'];
$invoiceGenerated = $db->getItemSafe("SELECT COUNT(*) FROM B2CInvoice WHERE bci_fstr_id = ?", "i", [$_REQUEST['order_id']]);

if ($invoiceGenerated == 1) {
    $invoiceDetails = $db->getFromSafe("SELECT id,invoiceNumber,DATE_FORMAT(invoiceDate,'%d %M %Y') AS  invoiceDate FROM B2CInvoice WHERE bci_fstr_id = ?", "i", [$_REQUEST['order_id']], true);
    $invoiceItemDetails = $db->getMultipleData("SELECT * FROM B2CInvoiceDetails WHERE bci_id = {$invoiceDetails['id']}", true);

    $toDetails = $db->getFromDB("SELECT fsto_id,fstr_id FROM finascop_stock_transfer_order WHERE fsto_ordertype = 1 AND fstr_id = {$order_id}", true);
    $customerOrderDetails = $db->getFromDB("SELECT order_branch_id,order_customer_id,order_total_amount,order_delivery_charge,order_total_gst,payment_mode,DATE_FORMAT(order_confirm_date,'%a %d %M %Y') AS  order_confirm_date,"
            . "DATE_FORMAT(order_confirmed_on,'%a %d %M %Y %H:%i:%s') AS  order_confirmed_on,order_kfc_amount,order_saved_amount,total,order_discount_amount,order_roundoff,order_amount_payable,total_afterpacking "
            . " FROM retaline_customer_order WHERE order_id = {$order_id} AND order_order_id = '{$order_order_id}'", true);
    $branchSql = "SELECT br_Name,br_City,br_District,br_State,br_Address,br_pincode,br_Phone,br_Fax FROM finascop_branch WHERE br_ID = {$customerOrderDetails['order_branch_id']}";
    $customerDetails = $db->getFromDB("SELECT cust_customer_name,cust_email,cust_mobile,cust_walletbalance FROM retaline_customer WHERE cust_id = {$customerOrderDetails['order_customer_id']}", true);
    $branchDetails = $db->getFromDB($branchSql, true);
    $custName = $db->getItemFromDB("SELECT cust_customer_name FROM retaline_customer WHERE cust_id = {$customerOrderDetails['order_customer_id']}");
    $custAddress = $db->getFromDB("SELECT order_customer_name,order_house_no,order_house_name,order_land_mark,order_city,order_post,order_state,order_pin,order_country,order_contact_no FROM retaline_customer_order_delivery_address "
            . "WHERE customer_order_id = {$order_id}", true);
    $ordeItems = $db->getMultipleData("SELECT item_id,customer_order_id,item_product_id,item_group_id,item_order_qty,item_price,item_sales_price,item_retail_price,item_cgst,item_sgst,item_igst,item_kfc,item_amount,"
            . "item_discount,item_discount_total,item_order_qty_scanned FROM retaline_customer_order_items WHERE customer_order_id = {$order_id}", true);
    $noOfItems = count($ordeItems);
    $order_amt_total = $customerOrderDetails['order_total_amount'];
    $kfc = ($order_amt_total / 100);
    $amtPayable = floatval($customerOrderDetails['order_total_gst']) + floatval($customerOrderDetails['order_total_amount']) + floatval($customerOrderDetails['order_delivery_charge']) + floatval($kfc);
    $subTotal = floatval($customerOrderDetails['order_total_gst']) + floatval($customerOrderDetails['order_total_amount']);

    $amtPayable = number_format($amtPayable, 2);

    $branchSql = "SELECT br_Name,br_City,br_District,br_State,br_Address,br_pincode,br_Phone,br_Fax FROM finascop_branch WHERE br_ID = {$customerOrderDetails['order_branch_id']}";
    $branchDetails = $db->getFromDB($branchSql, true);
    $pojectName = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'PROJECT_NAME'");
    $poCompany = $db->getItemFromDB("SELECT comp_id FROM finascop_branch_company WHERE br_Id = {$customerOrderDetails['order_branch_id']}");
    $companyDetails = $db->getFromDB("SELECT comp_name,comp_address,comp_GSTIN,comp_Ph,comp_dlno1,comp_dlno2,comp_fssaino FROM finascop_company WHERE comp_id = {$poCompany}", true);

    if (!empty($customerOrderDetails['order_amount_payable'])) {
        $order_amount_payable = $customerOrderDetails['order_amount_payable'];
    } else {
        $order_amount_payable = 0.00;
    }
    switch ($customerOrderDetails['payment_mode']) {
        case 1:
            $payMod = 'Pay On Delivery';
            break;
        case 2:
            $payMod = 'Online Payment';
            $paymentDetails = $db->getFromDB("SELECT * FROM retaline_paymentgateway_razorpay WHERE order_id = {$order_id}", true);
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
}
?>
<!doctype html>
<html>

    <head>
        <meta charset="utf-8">
        <title><?= SITE_TITLE ?></title>
    </head>
    <style>
th, td, p{font-family: arial, sans-serif; font-size: 8px; line-height: 12px;}
p{font-family: arial, sans-serif; font-size: 8px; margin: 0; line-height: 12px;}
</style>
    <body>
        <div class="container-fluid">
            <div class='panel'>
                <?php if ($invoiceGenerated == 1) { ?>
                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                        <tr>
                            <td colspan="2">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="padding:5px; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;" width="110" valign="middle" align="center">
                                            <img src="resources/mypharmacy/freshmeenmatsyafed_logo.png" width="75" height="43">
                                        </td>
                                        <td align="left" valign="top" width="" style="padding:5px; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">
                                            <strong style="font-size: 9px; font-weight: bold;"><?php echo $companyDetails['comp_name']; ?></strong><br>
                                            <?php echo $companyDetails['comp_address']; ?>, 
                                            PIN <?php 'NA' ?><br>
                                            GSTN: <?php echo $companyDetails['comp_GSTIN']; ?>
                                        </td>
                                        <td valign="top" width="35%" style="padding:5px; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">
                                            Invoice No:<br>
                                            <strong><?php echo $invoiceDetails['invoiceNumber']; ?></strong><br>
                                            Invoice Date:<br>
                                            <strong><?php echo $invoiceDetails['invoiceDate']; ?></strong>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr><!--header part-->

                        <tr>
                            <td colspan="2">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td valign="top" width="65%" style="padding:5px; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">
                                            Order No: <strong><?php echo $order_order_id; ?></strong> Date: <strong><?php echo $customerOrderDetails['order_confirm_date']; ?></strong>
                                        </td>
                                        <td align="center" bgcolor="#999999" width="35%" style="background:#999999; padding:5px; font-family: arial, sans-serif; font-size: 8px; line-height: 12px; font-weight: bold; color: #FFFFFF;">CUSTOMER COPY</td>
                                    </tr>
                                </table>
                            </td>
                        </tr><!--order_no customer_copy-->

                        <tr>
                            <td colspan="2">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="50%" style="padding:5px; padding-right:15px; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;" valign="top" >
                                            Bill To:<br>
                                            <strong><?php echo $custName; ?></strong><br>
                                            <?php echo $custAddress['order_house_no']; ?> <?php echo $custAddress['order_house_name']; ?>,<br>
                                            <?php echo $custAddress['order_land_mark']; ?>, <?php echo $custAddress['order_city']; ?>, <?php echo $custAddress['order_state']; ?> - <?php echo $custAddress['order_pin']; ?>
                                        </td>
                                        <td align="left" width="50%" style="padding:5px; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;" valign="top" >
                                            Ship To:<br>
                                            <strong><?php echo $custName; ?></strong><br>
                                            <?php echo $custAddress['order_house_no']; ?> <?php echo $custAddress['order_house_name']; ?>,<br>
                                            <?php echo $custAddress['order_land_mark']; ?>, <?php echo $custAddress['order_city']; ?>, <?php echo $custAddress['order_state']; ?> - <?php echo $custAddress['order_pin']; ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr><!--bill and shipping address-->

                        <tr>
                            <td colspan="2">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td align="center" width="4%" style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px; font-weight: bold;">S/n</td>
                                        <td align="center" style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">Item</td>
                                        <td align="center" width="11.6%" style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px; font-weight: bold;">Rate</td>
                                        <td align="center" width="11.6%" style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px; font-weight: bold;">Qty</td>
                                        <td align="center" width="11.6%" style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px; font-weight: bold;">Tax</td>
                                        <td align="center" width="11.6%" style="padding:5px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">Amount</td>
                                    </tr>

                                    <?php
                                    $totalMrp = 0;
                                    $subTotalNew = 0;
                                    for ($i = 0; $i < $noOfItems; $i++) {
                                        $itemGST = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$ordeItems[$i]['item_product_id']} ");
                                        if ($toDetails['fsto_id'] > 0) {
                                            $batch = $db->getItemFromDB("SELECT GROUP_CONCAT(DISTINCT(stiid_batchno)) FROM finascop_stock_transfer_order_details_barcodes fstodb "
                                                    . "INNER JOIN finascop_stock_item_inventorydetails fsii ON fsii.stiid_id = fstodb.stiid_id WHERE fsto_id = {$toDetails['fsto_id']} AND stiid_itemmasterid = {$ordeItems[$i]['item_product_id']}");
                                            $expiryDates = $db->getItemFromDB("SELECT GROUP_CONCAT(DISTINCT(stiid_expirydate)) FROM finascop_stock_transfer_order_details_barcodes fstodb "
                                                    . "INNER JOIN finascop_stock_item_inventorydetails fsii ON fsii.stiid_id = fstodb.stiid_id WHERE fsto_id = {$toDetails['fsto_id']} AND stiid_itemmasterid = {$ordeItems[$i]['item_product_id']}");
                                        }
                                        if ($custAddress['order_state'] == 'Kerala') {
                                            $itemCGSTpercent = $itemGST / 2;
                                            $itemSGSTpercent = $itemGST / 2;
                                        } else {
                                            $itemCGSTpercent = $itemGST;
                                            $itemSGSTpercent = 0;
                                        }
                                        $j = $i + 1; //med_manufacturename
                                        $itemName = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$ordeItems[$i]['item_product_id']} ");
                                        $itemHSN = $db->getItemFromDB("SELECT stit_HSNCode FROM finascop_stock_itemmaster WHERE stit_ID = {$ordeItems[$i]['item_product_id']} ");
                                        $hsn_code = $db->getItemFromDB("SELECT hsn_code FROM finascop_hsn WHERE hsn_id = {$itemHSN} ");
                                        $itemUnit = $db->getItemFromDB("SELECT least_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$ordeItems[$i]['item_product_id']} ");
                                        $pickedQty = $db->getItemFromDB("SELECT fsto_pkdQty FROM finascop_stock_transfer_order_details WHERE fsto_id = {$toDetails['fsto_id']} AND fsto_ItemId = {$ordeItems[$i]['item_product_id']}");
                                        ?>
                                        <tr>
                                            <td valign="top" width="4%" style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;"><?php echo $j; ?></td>
                                            <td valign="top" style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;"><?php echo $itemName; ?> Ordered <?php echo $ordeItems[$i]['item_order_qty']; ?> No; Processed <?php echo $ordeItems[$i]['itemConversionValue'] . ' ' . $itemUnit; ?>Kg </td>
                                            <td valign="top" align="right" style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;"><?php echo $ordeItems[$i]['item_sales_price']; ?></td>
                                            <td valign="top" align="right" style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;"><?php echo $pickedQty . ' ' . $itemUnit; ?></td>
                                            <td valign="top" align="right" style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;"><?php echo $itemGST; ?>%</td>
                                            <td valign="top" align="right" style="padding:5px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">
                                                <?php
                                                if ($ordeItems[$i]['item_order_qty_scanned'] > 0) {
                                                    $item_price = $ordeItems[$i]['item_order_qty_scanned'] * $ordeItems[$i]['item_sales_price'];
                                                    $item_price = round($item_price, 2);
                                                } else {
                                                    $item_price = $ordeItems[$i]['item_price'];
                                                }

                                                $subTotalNew += $item_price;
                                                //echo $ordeItems[$i]['item_price'];
                                                echo number_format($item_price, 2);
                                                ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?> 
                                    <tr>
                                        <td colspan="3" valign="top" style="padding:8px; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">
                                            <p>Mode of Payment: <?php echo $payMod; ?><br>
                                                <?php if ($customerOrderDetails['payment_mode'] == 2) { ?>
                                                    Bank Reference: <?php echo $paymentDetails['razorpay_id']; ?><br>
                                                    Paid vide Receipt No.<?php echo $paymentDetails['receipt']; ?> through Net Banking<br>
                                                    <!--                                            Customer IP: 198.168.202.222</p><br>-->
                                                <?php } ?>
                                                <?php if (!empty($customerOrderDetails['total_afterpacking'])) { ?>
                                                <p><i>Important Update:</i><br>
                                                    To process the order, some of the items weighed more hence the sale order value of <?php echo CURRENCY.' '.$customerOrderDetails['total']; ?> increased to <?php echo CURRENCY.' '.$customerOrderDetails['total_afterpacking']; ?>. Balance to be paid <?php echo CURRENCY.' '.$order_amount_payable; ?></p><br>

                                            <?php } ?>
                                            <p><i>Tax Info:</i>
                                                SGST: <?php echo $itemsgstTotal; ?><br>
                                                CGST: <?php echo $itemcgstTotal; ?></p>
                                        </td>
                                        <td colspan="3">
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <td style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">Total</td>
                                                    <td align="right" width="33.3%" style="padding:5px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;"><?php echo number_format($subTotalNew, 2); ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">Taxable Amt</td>
                                                    <td align="right" width="33.3%" style="padding:5px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;"><?php echo $taxableValueTotal; ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">Tax</td>
                                                    <td align="right" width="33.3%" style="padding:5px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">0.00</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">Delivery</td>
                                                    <td align="right" width="33.3%" style="padding:5px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;"><?php echo $customerOrderDetails['order_delivery_charge']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">Round Off</td>
                                                    <td align="right" width="33.3%" style="padding:5px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;"><?php echo $customerOrderDetails['order_roundoff']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px;  font-weight: bold; line-height: 12px;">Grand Total</td>
                                                    <td align="right" width="33.3%" style="padding:5px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;"><?php
                                                        if (!empty($customerOrderDetails['total_afterpacking'])) {
                                                            $orderAmount = $customerOrderDetails['total_afterpacking'];
                                                        } else {
                                                            $orderAmount = $customerOrderDetails['total'];
                                                        }
                                                        echo $orderAmount;
                                                        ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:5px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">Amount Paid</td>
                                                    <td align="right" width="33.3%" style="padding:5px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;"><?php
                                                        if (!empty($customerOrderDetails['total_afterpacking'])) {
                                                            $paidamount = $customerOrderDetails['total_afterpacking'] - $customerOrderDetails['order_amount_payable'];
                                                        } else {
                                                            $paidamount = $customerOrderDetails['total'] - $customerOrderDetails['order_amount_payable'];
                                                        }


                                                        echo number_format($paidamount, 2);
                                                        ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:5px; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">Balance to Pay</td>
                                                    <td align="right" width="33.3%" style="padding:5px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;"><?php
                                                        echo $order_amount_payable;
                                                        ?></td>
                                                </tr>

                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr><!--list of items-->

                        <tr>
                            <td colspan="2" align="right" style="padding:5px; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">
                                Total  <strong style="font-size:9px;"><?php
                                    if (!empty($customerOrderDetails['total_afterpacking'])) {
                                        $orderAmount = $customerOrderDetails['total_afterpacking'];
                                    } else {
                                        $orderAmount = $customerOrderDetails['total'];
                                    }
                                    echo CURRENCY.' '.ucfirst
                                            (numberTowords($orderAmount));
                                    ?></strong> only
                            </td>
                        </tr><!--amount in words-->

                        <tr>
                            <td width="50%" valign="top" style="padding:5px; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">
                                For Enquiries<br>
                                <strong><?php echo $branchDetails['br_Name']; ?></strong><br>
                                <p><?php echo $branchDetails['br_Address']; ?>  <?php echo $branchDetails['br_City']; ?>, Pin - <?php echo $branchDetails['br_pincode']; ?><br>
                                    Call: <?php echo $branchDetails['br_Phone']; ?>/ <?php echo $branchDetails['br_Fax']; ?><br>
                                    Email: <?php echo $branchDetails['br_Email']; ?></p>
                            </td>
                            <td width="50%" valign="top" style="border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td valign="top" style="padding:5px; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;">Authorized Signatory</td>
                                    </tr>
                                    <tr>
                                        <td height="15" valign="top" style="padding:5px; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;"></td>
                                    </tr>
                                    <tr>
                                        <td style="padding:5px; font-family: arial, sans-serif; font-size: 8px; line-height: 12px;"><i>This is a computer generated invoice hence not required a signature</i></td>
                                    </tr>
                                </table>
                            </td>
                        </tr><!--amount in words-->

                    </table><!--table of content-->
                <?php } else { ?>
                    <div>Invoice not generated</div>
                <?php } ?>
            </div><!--panel-->
        </div>
    </body>
</html>