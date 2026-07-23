
<!--
<html>
     <style>
        .cesstable {
            border: 1px solid #909090;
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
            border-color: -moz-use-text-color #909090 #909090  -moz-use-text-color;
            border-style: solid solid solid solid;
            border-width: 1px 1px 1px 1px;
            height: 22px;
            padding: 0 10px 0 12px;
            vertical-align: middle;
        }
        .cesstable th {
            border-color: -moz-use-text-color #909090 #909090  -moz-use-text-color;
            border-style: solid solid solid solid;
            border-width: 1px 1px 1px 1px;
            height: 22px;
            padding: 0 10px 0 12px;
            vertical-align: middle;
        }
    </style>
    <span style="align: center;"><strong>Delivery Challan</strong></span>
    <?php if (!empty($disdata)) { ?>
        <table cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>
                <?php if ($disdata['bcd_vehicleNo'] != '') { ?>
                    <tr>
                        <td width="275">Vehicle No</td>
                        <td width="675"><?php echo $disdata['bcd_vehicleNo']; ?></td>
                    </tr>
                <?php } ?>
                <?php if ($disdata['bcd_driver'] != '') { ?>
                    <tr>
                        <td width="275">Driver</td>
                        <td width="675"><?php echo $disdata['bcd_driver']; ?></td>
                    </tr>
                <?php } ?>
                <?php if ($disdata['bcd_dispatchTime'] != '') { ?>
                    <tr>
                        <td width="275">Dispatch At</td>
                        <td width="675"><?php echo $disdata['bcd_dispatchTime']; ?></td>
                    </tr>
                <?php } ?>
                <?php if ($disdata['bcd_dispatchDate'] != '') { ?>
                    <tr>
                        <td width="275">Dispatch Date</td>
                        <td width="675"><?php echo $disdata['bcd_dispatchDate']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <br>
        <?php if (!empty($result)) { ?>
            <table cellspacing="2" cellpadding="2" border="0" class="cesstable">
                <tbody>
                    <tr>
                        <th width="275">Dispatch No.</td>
                        <th width="275">Branch Name</td>
                        <th width="275">CPD Name</td>
                        <th width="275">Order Status</td>
                    </tr>

                <?php for($i=0;$i<count($result);$i++){?>
                        <tr>
                        <td width="275"><?php echo $result[$i]['order_no']; ?></td>
                        <td width="275"><?php echo $result[$i]['branch_name']; ?></td>
                        <td width="275"><?php echo $result[$i]['cpd_Name']; ?></td>
                        <td width="275"><?php echo $result[$i]['order_status']; ?></td>

                        </tr>
                <?php 
                $order_id = $result[0]['order_id'];
                } 
                
                    $orderDetails = $db->getMultipleData("SELECT retaline_branch_outward_order.order_no AS OrderNO,
                    (SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID= retaline_branch_outward_order_items.stit_ID ) AS SKU,
                retaline_branch_outward_order_items.bcod_scannedcount AS QTY,(SELECT MIN(stiid_barcode)FROM retaline_branch_outward_order_items_barcodes 
                WHERE  retaline_branch_outward_order_items.bcod_id =retaline_branch_outward_order_items_barcodes.bcod_id  ) AS MinBarcode,
                (SELECT MAX(stiid_barcode) FROM retaline_branch_outward_order_items_barcodes WHERE  retaline_branch_outward_order_items.bcod_id =retaline_branch_outward_order_items_barcodes.bcod_id  ) AS MaxBarcode  
                FROM  retaline_branch_outward_order INNER JOIN retaline_branch_outward_order_items  ON bcor_id = order_id WHERE order_id = {$order_id} AND bcod_scannedcount >0", true);
                    
                ?>
                </tbody>
            </table>
        <br>
        <?php } else { ?>
            sorry there is no orders to display
        <?php } ?>
             <?php if (!empty($orderDetails)) { ?>
        <table cellspacing="2" cellpadding="2" border="0" class="cesstable">
            <tbody>
                <tr>
                    <th width="275">SI.</td>
                    <th width="275">SKU</td>
                    <th width="275">QTY</td>
                    <th width="275">Min barcode</td>
                    <th width="275">Max barcode</td>
                </tr>

                <?php for ($j = 0; $j < count($orderDetails); $j++) { ?>
                    <tr>
                        <td width="275"><?php echo $j+1; ?></td>
                        <td width="275"><?php echo $orderDetails[$j]['SKU']; ?></td>
                        <td width="275"><?php echo $orderDetails[$j]['QTY']; ?></td>
                        <td width="275"><?php echo $orderDetails[$j]['MinBarcode']; ?></td>
                        <td width="275"><?php echo $orderDetails[$j]['MaxBarcode']; ?></td>

                    </tr>
                <?php } ?>
            </tbody>
        </table>
             <?php } else { ?>
            Nothing to display
        <?php } ?>
        <br>
    <?php } else { ?>
        sorry there is no available data to display
    <?php } ?>
</html>
    -->


<?php
$bbso_InvNumber = SO2020000073;
$bbso_id = 91;
        $query = "SELECT bbso_id, bbso_InvNumber,bbso_SONumber,bbso_SODate,bbso_InvDate ,b2b_Customer_ID,bbso_SOValue,bbso_HandlingCharges,"
        . "(bbso_SOValue - bbso_HandlingCharges) AS bbso_InvSubTotal,bbso_InvValBtax,bbso_CGSTVal,bbso_SGSTVal,bbso_totInFig,bbso_totInWords,"
        . "(SELECT br_Name FROM finascop_branch fb WHERE fb.br_ID = rbs.br_ID) AS bbso_Branch"
        . " FROM retaline_B2B_SalesOrder rbs WHERE bbso_id = {$bbso_id}";
        $B2BInvData = $db->getFromDB($query, true);

        $bbso_InvDate = $B2BInvData['bbso_InvDate'];
        $bbso_InvNo = $B2BInvData['bbso_InvNumber'];
        $bbso_InvBranch = $B2BInvData['bbso_Branch'];

        $bbso_InvSubTotal = $B2BInvData['bbso_InvSubTotal'];
        $bbso_HandlingCharges = $B2BInvData['bbso_HandlingCharges'];
        $bbso_InvGrandTotal = $B2BInvData['bbso_SOValue']; 
        $db->query("SET @slNo = 0;"); 
        $listQuery = "SELECT @slNo := @slNo + 1 as slNo,  b2bso_itemname,b2bso_HSN, b2bso_itemqty, b2bso_itemrate,b2bso_gst,b2bso_cgst_percent, "
        ." b2bso_cgst_value, b2bso_sgst_percent, b2bso_sgst_value, b2bso_amount_btax,b2bso_discountamt, b2bso_netamount "
        . "FROM retaline_B2B_SalesOrderDetails WHERE bbso_id = {$bbso_id}";
        $B2BInvItemDetArr = $db->getMultipleData($listQuery, true);

        $bbso_InvItemDetails = '';
        foreach ($B2BInvItemDetArr as $itemDetails) {
            $curItemDet = '';
            $curItemDet = '<tr>
            <td class="slno">' . $itemDetails['slNo'] . '</td>
            <td class="desc">' . $itemDetails['b2bso_itemname'] . '</td>
            <td class="hsn">' . $itemDetails['b2bso_HSN'] . '</td>
            <td class="qty">' . $itemDetails['b2bso_itemqty'] . '</td>
            <td class="rate">' . $itemDetails['b2bso_itemrate'] . '</td>
            <td class="taxval">' . $itemDetails['b2bso_amount_btax'] . '</td>
            <td class="cgstrate">' . $itemDetails['b2bso_cgst_percent'] . '%</td>
            <td class="cgstamt">' . $itemDetails['b2bso_cgst_value'] . '</td>
            <td class="sgstrate">' . $itemDetails['b2bso_sgst_percent'] . '%</td>
            <td class="sgstamt">' . $itemDetails['b2bso_sgst_value'] . '</td>
            <td class="tot">' . $itemDetails['b2bso_netamount'] . '</td>
            </tr>';
        $bbso_InvItemDetails .= $curItemDet;

        }

        $b2bSOCustomer = $B2BInvData['b2b_Customer_ID'];
        $b2bCustDet = $db->getFromDB("SELECT b2b_Customer_Name,b2b_Customer_Address,b2b_Customer_pincode,b2b_Customer_Phone,"
        . "b2b_Customer_Email,b2b_Customer_Mobile,b2b_Customer_gst "
        . "  FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = {$b2bSOCustomer}", true);

        $bbso_CustomerAddress = "{$b2bCustDet['b2b_Customer_Name']},<br>"
        . "{$b2bCustDet['b2b_Customer_Address']} PIN:{$b2bCustDet['b2b_Customer_pincode']} <br>"
        . "Phone:{$b2bCustDet['b2b_Customer_Phone']} <br>Email: {$b2bCustDet['b2b_Customer_Email']} <br> "
        . "Mob: {$b2bCustDet['b2b_Customer_Mobile']} <br>GST: {$b2bCustDet['b2b_Customer_gst']}";

?>

<div style="width: 100%;">
    <head>
        <link rel="stylesheet" href="../../resources/css/invoicestyle.css" media="all" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style>
            @font-face {
                font-family: Belleza;
                font-weight: normal; 
                font-style: normal;
                src: url("../../includes/dompdf/lib/fonts/Belleza.ttf") format("truetype");
            } 

            @media print {
            body * {
                visibility: hidden;
            }
            #section-to-print, #section-to-print * {
                visibility: visible;
            }
            #section-to-print {
                position: absolute;
                left: 0;
                top: 0;
            }
            }

        </style>
    </head>
    <div id = 'section-to-print' style="width: 100%;">




  
        <table class="tg" style="width: 99.80%; border:none;">
                <thead>
                    <tr>
                        <th class="tg" style="width: 4%;">
                            <img style="width: 19mm; height: 15mm;" alt="brm logo" src="./resources/images/logo.jpg">
                        </th>
                        <th class="tg" colspan="3">
                            <table class="ti">
                                <tbody>
                                    <tr>
                                        <th class="ti" style="font-size: 85%;font-weight: bold;">
                                            Retaline
                                        </th>
                                    </tr>
                                    <tr>
                                        <td class="ti" style="font-size: 70%;">
                                           Kazhakuttom
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ti" style="font-size: 70%;">
                                            Thiruvananthapuram,Kerala PIN Code:695512
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ti" style="font-size: 70%;">
                                            Tel:0471-00000000 Mob:0000000000 Email:sales@retaline.in
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </th>
                        <th class="tg" colspan="2" style="width: 30%; text-align: right; margin: auto 0 auto auto;">
                            <table class="ti">
                                <tbody>
                                    <tr>
                                        <th class="ti" style="text-align: right; font-size: 65%; font-weight: bold;"> GST:</th>
                                    </tr>
                                    <tr>
                                        <td class="ti" style="text-align: right; font-size: 65%;"> COI:</td>
                                    </tr>
                                </tbody>
                            </table>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr style=" height: 43px;">
                        <td class="tg" colspan="3" style="height: 40px;">
                            <table class="ti">
                                <tbody>
                                    <tr>
                                        <th class="ti" style="text-align: left; font-size: 80%;">
                                            Sales Order Number: <?php echo $B2BInvData['bbso_SONumber']; ?>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="ti" style="text-align: left; font-size: 80%;">
                                            Sales Order Date: <?php echo   $B2BInvData['bbso_SODate']; ?>
                                        </th>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td class="tg" colspan="3" style=" width: 70%; height: 40px; text-align: center; font-weight: bold; font-size: 200%;">DELIVERY CHALLAN</td>
                    </tr>
                </tbody>
            </table>

            <table style="width: 99.80%; border:none;" class="tg">
                <thead>
                    <tr>
                        <th class="tg" colspan="2" style="border: medium none ; font-size: 80%;">ORIGINAL FOR RECIPIENT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th class="tg" style="width: 50%; text-align: left; font-size: 80%;">
                            Details of the Receiver (Consignee)
                        </th>
                        <th class="tg" style="width: 50%; text-align: left; font-size: 80%;">
                            Challan No: 
                        </th>
                    </tr>
                    <tr>
                        <th class="tg" style="text-align: left; font-size: 75%;">
                            <?php echo $b2bCustDet['b2b_Customer_Name']; ?>
                        </th>
                        <th class="tg" style="text-align: left; font-size: 75%;">
                            Challan Date: 
                        </th>
                    </tr>
                    <tr>
                        <td rowspan = '2'; class="tg" style="text-align: left; font-size: 75%;">
                            <?php echo $b2bCustDet['b2b_Customer_Address']; ?>
                        </td>
                        <td class="tg" style="text-align: left; font-size: 75%;">
                            Place of Supply: 
                        </td>
                    </tr>
                    <tr>
                        <td class="tg" style="text-align: left; font-size: 75%;">
                            State & Code: 
                        </td>
                    </tr>
                    <tr>
                        <td class="tg" style="text-align: left; font-size: 75%;"> 
                        </td>
                        <td class="tg" style="text-align: left; font-size: 75%;"> 
                        Transportation Mode: 
                        </td>
                    </tr>
                    <tr>
                        <td class="tg" style="text-align: left; font-size: 75%;">
                            GST No. : <?php echo $b2bCustDet['b2b_Customer_gst']; ?>
                        </td>
                        <td class="tg" style="text-align: left; font-size: 75%;">
                            Vehicle No.: 
                        </td>
                    </tr>

                </tbody>
            </table>
            <table style="width: 99.80%; border:none;" class="tg">
                <thead>
                    <tr>
                        <th class="tg" colspan="11" style="border: medium none ; font-size: 80%; width: 100%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th class="slno">
                            Sl
                        </th>
                        <th class="desc">
                            Description of Goods
                        </th>
                        <th class="hsn">
                            HSN
                            <br>
                            Code
                        </th>
                        <th class="qty">
                            Qty
                        </th>
                        <th class="rate">
                            Rate
                        </th>
                        <th class="taxval">
                            Taxable
                            <br>
                            Value
                        </th>
                        <th class="cgst" colspan="2">
                            CGST
                            <br>
                            Rate | Amount
                        </th>
                        <th class="sgst" colspan="2">
                            SGST
                            <br>
                            Rate | Amount
                        </th>
                        <th class="tot">
                            Total
                        </th>
                    </tr>
                    <?php echo $bbso_InvItemDetails; ?>
                    <tr>
                        <td class="tg" colspan="5" style="text-align: right; font-size: 75%;"> Grand Total: </td>
                        <td class="tg" style="text-align: right; font-size: 65%;"><?php echo $B2BInvData['bbso_InvValBtax']; ?></td>
                        <td class="tg"></td>
                        <td class="tg" style="text-align: right; font-size: 65%;"><?php echo $B2BInvData['bbso_CGSTVal']; ?></td>
                        <td class="tg"></td>
                        <td class="tg" style="text-align: right; font-size: 65%;"><?php echo $B2BInvData['bbso_SGSTVal']; ?></td>
                        <td class="tg" style="text-align: right; font-size: 65%;"><?php echo $B2BInvData['bbso_InvSubTotal']; ?></td>
                    </tr>
                    <tr>
                        <td class="tg" colspan="5" style="text-align: right; font-size: 75%;">Handling Charges</td>
                        <td class="tg" colspan="6" style="text-align: left; font-size: 65%;"><?php echo $B2BInvData['bbso_HandlingCharges']; ?></td>
                    </tr>                        
                    <tr>
                        <td class="tg" colspan="5" style="text-align: right; font-size: 75%;">Total Values in Figures</td>
                        <td class="tg" colspan="6" style="text-align: left; font-size: 65%;"><?php echo $B2BInvData['bbso_totInFig']; ?></td>
                    </tr>
                    <tr>
                        <td class="tg" colspan="5" style="text-align: right; font-size: 75%;">Total Values in Words</td>
                        <td class="tg" colspan="6" style="text-align: left; font-size: 65%;"><?php echo $B2BInvData['bbso_totInWords']; ?></td>
                    </tr>

                </tbody>
            </table>
   




            <table style="width: 99.80%; border:none;" class="tg">
                <thead>
                    
                    <tr style="border: medium none;">
                        <th class="tg" colspan="3" style="border: medium none; text-align: center; font-size: 80%;"> &nbsp;</th>
                    </tr>
                    <tr>
                        <td style="width: 55%; text-align: left; font-size: 55%; font-weight: bold;" class="tg" >
                            Declaration: Certified that all the particulars shown in the above Tax Invoice are true and correct and that my/ our Registration under the CGST/ SGST/ IGST Acts 2017 is valid as on the date of this invoice
                        </td>
                        <td style="width: 20%;text-align: left; font-size: 75%; font-weight: normal;" class="tg"> 
                            &nbsp;<br>
                            &nbsp;<br>
                            &nbsp;<br>
                            &nbsp;<br>
                            &nbsp;<br>
                            &nbsp;<br>
                            Seal
                        </td>
                        <td style="width: 25%;text-align: left; font-size: 75%; font-weight: bold;" class="tg"> 
                            &nbsp;<br>
                            &nbsp;<br>
                            &nbsp;<br>
                            &nbsp;<br>
                            &nbsp;<br>
                            &nbsp;<br>
                            Signature
                        </td>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table> 
          
                
        </div>

</div>