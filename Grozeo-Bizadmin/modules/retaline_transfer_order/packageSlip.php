<?php
$fsto_id = $_REQUEST['fsto_id'];
$orderDetails = $db->getFromDB("SELECT fsto_uid,fsto_createdOn,fsto_destination,fsto_status,fsto_updateon,
(SELECT br_Name FROM finascop_branch where br_ID = fsto_source) as sourcename,fsto_ordertype,fstr_id,fsto_source,
CASE WHEN fsto_ordertype=0 THEN 'Branch Transfer' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' END AS fsto_ordertypeName,fsto_openingtime,fsto_ismanualpacking,fsto_manualpackinguserid,fsto_assigned_boy,
CASE WHEN fsto_ordertype = 0 THEN (SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) 
WHEN fsto_ordertype = 1 THEN (SELECT cust_customer_name FROM retaline_customer WHERE cust_id = fsto_destination) 
WHEN fsto_ordertype = 2 THEN (SELECT b2b_Customer_Name FROM retaline_B2Bcustomer where b2b_Customer_ID = fsto_destination) 
WHEN fsto_ordertype = 3 THEN (SELECT br_Name FROM finascop_branch where br_ID = fsto_destination) END AS customer,fsto_id 
FROM finascop_stock_transfer_order WHERE fsto_id={$fsto_id}", true);

if ($orderDetails['fsto_ismanualpacking'] == 1) {
    $packType = 'Manual Packing';
    $packUser = $db->getItemFromDB("SELECT CONCAT(FirstName,' ',LastName) FROM finascop_usr_profile WHERE UserId = {$orderDetails['fsto_manualpackinguserid']}");
} else {

    if ($orderDetails['fsto_assigned_boy'] > 0) {
        $packType = 'Packsure';
        $packUser = $db->getItemFromDB("SELECT name FROM retaline_godown_boy WHERE id = {$orderDetails['fsto_assigned_boy']}");
    }
}


?>

<!-- index.html -->
<!DOCTYPE html>
<html lang="en">
<?php if (!empty($orderDetails)) {
    $itemDetails = $db->getMultipleData("SELECT fsto_id,fsto_ItemId,fsto_ItemQty,fsto_pkdQty,stit_SKU FROM finascop_stock_transfer_order_details 
    INNER JOIN  finascop_stock_itemmaster ON stit_ID = fsto_ItemId  WHERE fsto_id={$orderDetails['fsto_id']}", true);
    switch ($orderDetails['fsto_ordertype']) {
        case 1:
            $mainOrderDetails = $db->getFromDB("SELECT order_id,order_order_id,total,total_afterpacking,order_confirm_date,created_at,updated_at,order_invoiceno,order_invoicedate,order_invoiceamt FROM retaline_customer_order WHERE order_id = {$orderDetails['fstr_id']}", true);
            $itemCount = $db->getItemFromDB("SELECT COUNT(1) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$orderDetails['fsto_id']}");
            $totalItems = $db->getItemFromDB("SELECT SUM(fsto_ItemQty) FROM finascop_stock_transfer_order_details WHERE fsto_id = {$orderDetails['fsto_id']}");
            if ($mainOrderDetails['total_afterpacking'] > 0)
                $orderValue = $mainOrderDetails['total_afterpacking'];
            else
                $orderValue = $mainOrderDetails['total'];
                $salesOrderDetails = $db->getFromDB("SELECT SONumber,SODate FROM B2CSalesOrder WHERE customer_order_id = {$orderDetails['fstr_id']}",true);
            break;
            
    }
?>

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Slip</title>
    </head>

    <style>


    </style>

    <body>

        <div class="container">
            <?php if ($orderDetails['fsto_status'] < 10) { ?>
                <table border="0" cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="padding: 6px;">
                            <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                <tr>
                                    <td align="center" style="font-size:14px; font-family: Arial, Helvetica, sans-serif; font-weight: bold;"><?php echo $sourcename; ?></td>
                                </tr>
                                <tr>
                                    <td height="2" style="line-height:0; font-size: 1px;"></td>
                                </tr>
                                <tr>
                                    <td align="center" style="font-size:11px; font-family: Arial, Helvetica, sans-serif;">
                                        Order No.<?php echo $mainOrderDetails['order_order_id']; ?> Date: <?php echo $mainOrderDetails['created_at']; ?> Sale Order Amount: <?php echo $mainOrderDetails['total']; ?> Total Items:<?php echo $totalItems; ?> Items Count:<?php echo $itemCount; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr><!--tr-->
                    <tr>
                        <td>
                            <table border="0" cellspacing="0" cellpadding="0" width="100%" style="border:1px solid #000;">
                                <tr>
                                    <th align="center" width="40px" style="padding: 6px; font-size:12px; font-family: Arial, Helvetica, sans-serif;">No.</th>
                                    <th align="center" style="padding: 6px; font-size:12px; font-family: Arial, Helvetica, sans-serif; border-right:1px solid #000; border-left:1px solid #000;">Item Name <br> Barcode/ ERP ID - Price/S.Price</th>
                                    <th align="center" width="40px" style="padding: 6px; font-size:12px; font-family: Arial, Helvetica, sans-serif;">Qty</th>
                                </tr>
                                <?php
                                $sino = 0;
                                foreach ($itemDetails as $itemDetail) {
                                    $sino = $sino + 1;
                                    $itemPrice = $db->getFromDb("SELECT mrp,selling_price FROM finascop_stock_branch_inventory WHERE stit_id={$itemDetail['fsto_ItemId']} AND branch_id={$orderDetails['fsto_source']}", true);
                                    $productCode  = $db->getItemFromDB("SELECT fsipc_code FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = {$itemDetail['fsto_ItemId']}");

                                ?>
                                    <tr>
                                        <td align="center" style="padding: 6px; font-size:13px; font-family: Arial, Helvetica, sans-serif; border-top:1px solid #000;"><?php echo $sino; ?></td>
                                        <td align="left" style="padding: 6px; font-size:13px; font-family: Arial, Helvetica, sans-serif; border-top:1px solid #000; border-right:1px solid #000; border-left:1px solid #000;">
                                            <p style="font-size:13px; font-family: Arial, Helvetica, sans-serif; margin: 0; margin-bottom:4px;"><?php echo $itemDetail['stit_SKU']; ?></p>
                                            <p style="font-size:13px; font-family: Arial, Helvetica, sans-serif; margin: 0;"><?php echo $productCode; ?> - <?php echo $itemPrice['mrp']; ?> / <?php echo $itemPrice['selling_price']; ?></p>
                                        </td>
                                        <td align="center" style="padding: 6px; font-size:13px; font-family: Arial, Helvetica, sans-serif; border-top:1px solid #000;"><?php echo $itemDetail['fsto_ItemQty']; ?></td>
                                    </tr>

                                <?php } ?>


                            </table>
                        </td>
                    </tr><!--tr-->
                    <tr>
                        <td align="center" style="font-size:11px; font-family: Arial, Helvetica, sans-serif; padding: 6px;">
                            Sales Confirmed: <?php echo $mainOrderDetails['order_confirm_date']; ?> / Accepted: <?php echo $mainOrderDetails['updated_at']; ?> Accepted by: <?php echo $packUser; ?>
                        </td>
                    </tr>
                </table>
            <?php } else if ($orderDetails['fsto_status'] == 10 || $orderDetails['fsto_status'] == 12) { ?>
                <table border="0" cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                        <td style="padding: 6px;">
                            <table border="0" cellspacing="0" cellpadding="0" width="100%">
                                <tr>
                                    <td align="center" style="font-size:14px; font-family: Arial, Helvetica, sans-serif; font-weight: bold;">Store Name</td>
                                </tr>
                                <tr>
                                    <td height="2" style="line-height:0; font-size: 1px;"></td>
                                </tr>
                                <tr>
                                    <td align="center" style="font-size:11px; font-family: Arial, Helvetica, sans-serif;">
                                        Order No.<?php echo $mainOrderDetails['order_order_id']; ?> Date: <?php echo $mainOrderDetails['created_at']; ?> Sale Order Amount: <?php echo $mainOrderDetails['total']; ?> Total Items:<?php echo $totalItems; ?> Items Count:<?php echo $itemCount; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr><!--tr-->
                    <tr>
                        <td>
                            <table border="0" cellspacing="0" cellpadding="0" width="100%" style="border:1px solid #000;">
                                <tr>
                                    <th align="center" width="40px" style="padding: 6px; font-size:12px; font-family: Arial, Helvetica, sans-serif;">No.</th>
                                    <th align="center" style="padding: 6px; font-size:12px; font-family: Arial, Helvetica, sans-serif; border-right:1px solid #000; border-left:1px solid #000;">Item Name - Amount <br>Barcode/ ERP ID - Price/S.Price</th>
                                    <th align="center" width="40px" style="padding: 6px; font-size:12px; font-family: Arial, Helvetica, sans-serif;">Qty</th>
                                </tr>
                                <?php
                                $sino = 0;
                                foreach ($itemDetails as $itemDetail) {
                                    $sino = $sino + 1;
                                    $itemPrice = $db->getFromDb("SELECT mrp,selling_price FROM finascop_stock_branch_inventory WHERE stit_id={$itemDetail['fsto_ItemId']} AND branch_id={$orderDetails['fsto_source']}", true);
                                    $productCode  = $db->getItemFromDB("SELECT fsipc_code FROM finascop_stock_itemmaster_product_codes WHERE fsipc_stit_id = {$itemDetail['fsto_ItemId']}");

                                ?>
                                    <tr>
                                        <td align="center" style="padding: 6px; font-size:13px; font-family: Arial, Helvetica, sans-serif; border-top:1px solid #000;"><?php echo $sino; ?></td>
                                        <td align="left" style="padding: 6px; font-size:13px; font-family: Arial, Helvetica, sans-serif; border-top:1px solid #000; border-right:1px solid #000; border-left:1px solid #000;">
                                            <p style="font-size:13px; font-family: Arial, Helvetica, sans-serif; margin: 0; margin-bottom:4px;"><?php echo $itemDetail['stit_SKU']; ?></p>
                                            <p style="font-size:13px; font-family: Arial, Helvetica, sans-serif; margin: 0;"><?php echo $productCode; ?> - <?php echo $itemPrice['mrp']; ?> / <?php echo $itemPrice['selling_price']; ?></p>
                                        </td>
                                        <td align="center" style="padding: 6px; font-size:13px; font-family: Arial, Helvetica, sans-serif; border-top:1px solid #000;"><?php echo $itemDetail['fsto_pkdQty']; ?></td>
                                    </tr>

                                <?php } ?>

                            </table>
                        </td>
                    </tr><!--tr-->
                    <tr>
                        <td>
                            <table border="0" cellspacing="0" cellpadding="0" width="100%" style="border:1px solid #000; border-top:0px;"">
						<tr>
							<td width=" 50%" align="center" style="padding: 6px; font-size:13px; font-family: Arial, Helvetica, sans-serif; border-right:1px solid #000;"">TOTAL VALUE</td>
							<td width=" 50%" align="left" style="padding: 6px; font-size:13px; font-family: Arial, Helvetica, sans-serif; font-weight: bold;"><?php echo $orderValue; ?>
                        </td>
                    </tr>
                </table>
                </td>
                </tr>
                <tr>
                    <td align="center" style="font-size:11px; font-family: Arial, Helvetica, sans-serif; padding: 6px;">
                        Sales Confirmed: <?php echo $mainOrderDetails['order_confirm_date']; ?> / Packed: <?php echo $orderDetails['fsto_updateon']; ?> Sales Order No.<?php echo $salesOrderDetails['SONumber']; ?> Dated: <?php echo $salesOrderDetails['SODate']; ?>
                    </td>
                </tr>
                </table>

            <?php } else { ?>
                Packing slip not available
            <?php } ?>
        </div>

    </body>
<?php } else {
?>
    sorry there is no available data to display
<?php } ?>

</html>