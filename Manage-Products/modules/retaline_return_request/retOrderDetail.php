<?php
//User Id for the Particular variable
$itemIds = json_decode(stripslashes($_REQUEST['itemIds']));
$returnOrders = array_unique(json_decode(stripslashes($_REQUEST['returnOrders'])));
$returnOrders = implode(',', $returnOrders);
global $db;
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
    if (count($itemIds) > 0) {
        foreach ($itemIds as $item) {
            $itemName = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$item}");
            ?>
            <h4>Sales Return Details of - <?php echo $itemName; ?></h4>
            <?php
            $returnOrdersItmDetails = $db->getMultipleData("SELECT rtrqo_id,rtrqod_item_id,rtrqod_return_sellable,rtrqod_return_damaged,rtrqod_return_count "
                    . "FROM finascop_stock_return_request_order_details WHERE rtrqo_id in ({$returnOrders}) AND rtrqod_item_id = {$item} AND rtrqod_return_damaged > 0 AND rtrqod_isPackOrderCreated = 0", true);
            //print_r($returnOrdersItmDetails);
            ?>

            <?php
            foreach ($returnOrdersItmDetails as $returnOrdersItmDet) {
                //print_r($returnOrdersItmDet);

                $rtrqoDetails = $db->getFromDB("SELECT order_id,rtrqo_createdOn,rtrqo_sourceBranch,rtrqo_isDirect FROM finascop_stock_return_request_order WHERE rtrqo_id = {$returnOrdersItmDet['rtrqo_id']}", true);
                if ($rtrqoDetails['order_id'] > 0) {
                    $orderDetails = $db->getFromDB("SELECT order_order_id,order_customer_id,order_branch_id,order_invoiceno,order_invoicedate FROM retaline_customer_order WHERE order_id = {$rtrqoDetails['order_id']}", true);
                } else {
                    if ($rtrqoDetails['rtrqo_isDirect'] == 1) {
                        $orderDetails['order_order_id'] = 'Direct From Branch';
                    } else {
                        $orderDetails['order_order_id'] = 'From Retailer Branch';
                    }
                }

                if ($orderDetails['order_customer_id'] > 0) {
                    $customerName = $db->getItemFromDB("SELECT cust_customer_name FROM retaline_customer WHERE cust_id = {$orderDetails['order_customer_id']}");
                }else{
                    $customerName = 'NA';
                }

                $branchName = $db->getItemFromDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$rtrqoDetails['rtrqo_sourceBranch']}");
                ?>
                <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
                    <tbody>
                        <tr><td width="30%">Created On</td><td width="70%"><b><?php echo $rtrqoDetails['rtrqo_createdOn']; ?></b></td></tr>
                        <tr><td width="30%">Return Count</td><td width="70%"><b><?php echo $returnOrdersItmDet['rtrqod_return_count']; ?></b></td></tr>
                        <tr><td width="30%">Damaged Count</td><td width="70%"><b><?php echo $returnOrdersItmDet['rtrqod_return_damaged']; ?></b></td></tr>
                        <tr><td width="30%">Sellable Count</td><td width="70%"><b><?php echo $returnOrdersItmDet['rtrqod_return_sellable']; ?></b></td></tr>
                        <tr><td width="30%">Order Number</td><td width="70%"><b><?php echo $orderDetails['order_order_id']; ?></b></td></tr>
                        <tr><td width="30%">Customer </td><td width="70%"><b><?php echo $customerName; ?></b></td></tr>
                        <tr><td width="30%">Branch</td><td width="70%"><b><?php echo $branchName; ?></b></td></tr>
                    </tbody>
                </table>
                <br/>
            <?php
        }
        ?>

            <?php
        }
    } else {
        ?>
        sorry there is no available data to display
    <?php } ?>
</html>
