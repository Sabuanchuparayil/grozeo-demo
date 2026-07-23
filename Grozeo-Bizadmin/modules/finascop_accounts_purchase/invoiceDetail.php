<?php
$peIds = json_decode(stripslashes($_GET['fpeIds']));
$vendorId = $_POST['vendorId'];
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
    $stckInvVal = 0;
    $fpe_netAmtPaid = 0;
    $fpe_netAmount = 0;
    if (count($peIds) > 0) {
        $purEntry = implode(', ', $peIds);
        $PurorderIds = $db->getMultipleData("SELECT DISTINCT(fpe_fpoId) FROM finascop_purchase_entry WHERE fpe_id IN ({$purEntry})");
        foreach ($PurorderIds as $PurorderId) {
            $amtReceived = 0;
            $poDetails = $db->getFromDB("SELECT fpo_paymentTerms,fpo_paymentValue,fpo_poValue,fpo_poNumber,fpo_poDate,fpo_poFinalValue FROM finascop_purchase_order WHERE fpo_id = {$PurorderId}", true);
            $lastStockDate = $db->getItemFromDB("SELECT MAX(stii_createdon) FROM finascop_stock_item_inventory WHERE stii_fpoid = {$PurorderId}");
            $creditTermsValue = $db->getItemFromDB("SELECT ptc_days FROM retaline_paymtTermscfg WHERE ptc_name = '{$poDetails['fpo_paymentTerms']}'");
            if ($poDetails['fpo_paymentTerms'] != 'Custom') {
                $ptDate = date('d-m-Y', strtotime($lastStockDate . " + {$creditTermsValue} days"));
            } else {
                $ptDate = date('d-m-Y', strtotime($lastStockDate . "+ {$poDetails['fpo_paymentValue']} days"));
            }
            $datOfInst[] = $ptDate;
            ?>
    <h4>PO details of - <?php echo $poDetails['fpo_poNumber']; ?> - <span><a href="javascript:parent.window.Application.Finascop_Purchase_Order.viewPODetails(<?php echo $PurorderId; ?>);">View</a></span></h4>
            <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
                <tbody>
                    <tr><td width="30%">PO Date </td><td width="70%"><b><?php echo date('d-m-Y', strtotime($poDetails['fpo_poDate'])); ?></b></td></tr>
                    <tr><td width="30%">PO Value</td><td width="70%"><b><?php echo $poDetails['fpo_poFinalValue']; ?></b></td></tr>
                    <?php
                    $fpodIds = $db->getMultipleData("SELECT fpod_id,fpod_fpoId FROM finascop_purchase_order_details WHERE fpod_fpoId = {$PurorderId}", true);
                    foreach ($fpodIds AS $fpodId) {
                        $fpodIdReceivedQty = $db->getItemFromDB("SELECT fpod_receivedqty FROM finascop_purchase_order_details WHERE fpod_id = {$fpodId['fpod_id']}");
                        $fpodIdEPR = $db->getItemFromDB("SELECT fpod_poLandingCost FROM finascop_purchase_order_details WHERE fpod_id = {$fpodId['fpod_id']}");
                        //echo '$fpodIdEPR'.$fpodIdEPR;
                        //echo '$fpodIdReceivedQty'.$fpodIdReceivedQty;
                        $fpodReceivedValue = floatval($fpodIdEPR * $fpodIdReceivedQty);
                        $amtReceived = $amtReceived + $fpodReceivedValue;
                    }
                    ?>
                    <tr><td width="30%">Stock received</td><td width="70%"><b><?php echo number_format($amtReceived, 2); ?> </b></td></tr>

                    <tr><td width="30%">Stock received date</td><td width="70%"><b><?php echo date('d-m-Y', strtotime($lastStockDate)); ?></b></td></tr>
                    <tr><td width="30%">Credit Terms</td><td width="70%"><b><?php echo $poDetails['fpo_paymentTerms']; ?> - (<?php echo $ptDate; ?>) </b></td></tr>

                </tbody>
            </table>
            <?php
            $PoPes = $db->getMultipleData("SELECT fpe_id FROM finascop_purchase_entry WHERE fpe_fpoId = {$PurorderId}");
            $purEntries = array_intersect($PoPes, $peIds);

            foreach ($purEntries as $peId) {
                $purchaseEntryDetails = $db->getFromDB("SELECT fpe_vendor_id,fpe_vendorName,fpe_fpoId,fpe_fpoPoNumber,fpe_fpoPODate,fpe_invoiceNumber,fpe_invoiceDate,fpe_grossAmt,fpe_discount,fpe_netQty,fpe_netItems,fpe_netTax,"
                        . "fpe_netAmount,fpe_netIgst,fpe_netCgst,fpe_netSgst,fpe_createdOn,fpe_created_by,fpe_netAmtPaid FROM finascop_purchase_entry WHERE fpe_id = {$peId}", true);
                ?>
                <h4>Details of Invoice No:  <?php echo $purchaseEntryDetails['fpe_invoiceNumber']; ?></h4>
                <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
                    <tbody>
                        <tr><td width="30%">Invoice No: / Invoice Date </td><td width="70%"><b> <?php echo $purchaseEntryDetails['fpe_invoiceNumber'] . ' / ' . date('d-m-Y', strtotime($purchaseEntryDetails['fpe_invoiceDate'])); ?></b></td></tr>
                        <tr><td width="30%">Invoice Value</td><td width="70%"><b><?php echo $purchaseEntryDetails['fpe_netAmount']; ?></b></td></tr>
                        <tr><td width="30%">Bill Value in Purchase Entry</td><td width="70%"><b><?php echo $purchaseEntryDetails['fpe_netAmount']; ?> </b></td></tr>
                        <tr><td width="30%">Amount paid on PO</td><td width="70%"><b><?php echo number_format($purchaseEntryDetails['fpe_netAmtPaid'], 2); ?> </b></td></tr>

                    </tbody>
                </table>
                <?php
                $fpe_netAmtPaid = $fpe_netAmtPaid + $purchaseEntryDetails['fpe_netAmtPaid'];
                $fpe_netAmount = $fpe_netAmount + $purchaseEntryDetails['fpe_netAmount'];
                $requestID .= '_' . $purchaseEntryDetails['fpe_invoiceNumber'];
                $invoices .= ' Inv : ' . $purchaseEntryDetails['fpe_invoiceNumber'] . ' Date:' . date('d-m-Y', strtotime($purchaseEntryDetails['fpe_invoiceDate'])) . ' Amt:' . $purchaseEntryDetails['fpe_netAmount'];
            }
            $stckInvVal += $amtReceived;
        }

        $maxdatOfInst = max(array_map('strtotime', $datOfInst));
        $amtPayableinStockInv = $stckInvVal - $fpe_netAmtPaid;
        $amtPayableinPurchaseEntry = $fpe_netAmount - $fpe_netAmtPaid;
        if (($amtPayableinStockInv > $amtPayableinPurchaseEntry) && ($amtPayableinPurchaseEntry > 0)) {
            $amtPayable = $amtPayableinPurchaseEntry;
        } else {
            $amtPayable = $amtPayableinStockInv;
        }
        ?>
        <div class="invoicesummary">
            <h4>Payment Details: </h4>
            <table width="100%" cellspacing="2" cellpadding="2" border="0" class="cesstable">
                <tbody>
<!--                    <tr><td width="30%">amtPayableinStockInv</td><td width="70%"><b><?php echo number_format(round($amtPayableinStockInv), 2); ?> </b></td></tr>
                    <tr><td width="30%">amtPayableinPurchaseEntry</td><td width="70%"><b><?php echo number_format(round($amtPayableinPurchaseEntry), 2); ?> </b></td></tr>
                    <tr><td width="30%">Amount Paid</td><td width="70%"><b><?php echo number_format(round($fpe_netAmtPaid), 2); ?> </b></td></tr>-->
                    <tr><td width="30%">Amount Payable</td><td width="70%"><b><?php echo number_format(round($amtPayable), 2); ?> </b></td></tr>
                    <tr><td width="30%">Date of Instrument</td><td width="70%"><b><?php echo date('d-m-Y', $maxdatOfInst); ?> </b></td></tr>
                </tbody>
            </table>
        </div>
        <span class="nhidefields">
            <input type="hidden" id="fpe_netAmtPaid" name="fpe_netAmtPaid" value="<?php echo $fpe_netAmtPaid; ?>">
            <input type="hidden" id="fpe_netAmount" name="fpe_netAmount" value="<?php echo $fpe_netAmtPaid; ?>">
            <input type="hidden" id="stckInvVal" name="stckInvVal" value="<?php echo $stckInvVal; ?>">
            <input type="hidden" id="amtPayableinStockInv" name="amtPayableinStockInv" value="<?php echo $amtPayableinStockInv; ?>">
            <input type="hidden" id="amtPayableinPurchaseEntry" name="amtPayableinPurchaseEntry" value="<?php echo $amtPayableinPurchaseEntry; ?>">
            <input type="hidden" id="amtPayable" name="amtPayable" value="<?php echo $amtPayable; ?>">
            
            <input type="hidden" id="netamt" name="netamt" value="<?php echo round($amtPayable); ?>">
            <input type="hidden" id="reqID" name="invoices" value="<?php echo $requestID; ?>">
            <input type="hidden" id="invoices" name="invoices" value="<?php echo $invoices; ?>">
            <!--<input type="hidden" id="netamt" name="netamt" value="<?php echo round($amttoPayable); ?>">-->
            <input type="hidden" id="datofinstrument" name="datofinstrument" value="<?php echo date('d-m-y', $maxdatOfInst); ?>">
        </span>
    <?php } else { ?>
        sorry there is no available data to display
    <?php } ?>
</html>