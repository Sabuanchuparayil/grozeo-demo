<?php
$orderDetails = $db->getFromDb("SELECT quor_RefNo,DATE_FORMAT(quor_Date,'%d-%m-%Y') as booked_at,"
        . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",quor_PickupName,quor_DeliveryName) as customer, "
        . "quor_PickupLocation as source,quor_DeliveryLocation as destination FROM " . FINASCOP_DB . " qugeo_order WHERE quor_id={$quor_id}", true);
//print_r($toDetails);

$branchSql = "SELECT br_Name,br_City,br_District,br_State,br_Address,br_pincode,br_Phone,br_Fax FROM finascop_branch WHERE br_ID = {$toDetails['fsto_source']}";
$consigner = $db->getItemFromDB("SELECT br_Name FROM finascop_branch where br_ID = {$toDetails['fsto_source']}");
switch ($toDetails['fsto_ordertype']) {
    case 0:
        $fsto_ordertype = 'Branch Transfer';
        $consignee = $db->getItemFromDB("SELECT br_Name FROM finascop_branch where br_ID = {$toDetails['fsto_destination']}");
        break;
    case 1:
        $fsto_ordertype = 'B2C';
        $consignee = $db->getItemFromDB("SELECT cust_customer_name FROM retaline_customer WHERE cust_id = {$toDetails['fsto_destination']}");
        break;
    case 2:
        $fsto_ordertype = 'B2B';
        $consignee = $db->getItemFromDB("SELECT b2b_Customer_Name FROM retaline_B2Bcustomer where b2b_Customer_ID =  {$toDetails['fsto_destination']}");
        break;
}
$branchDetails = $db->getFromDB($branchSql, true);
       // print_r($toDetailItems);
foreach ($toDetailItems as $itemDetails) {
    $itemDetails['itemname'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$itemDetails['fsto_ItemId']} ");
    $itemDetails['itemHSN'] = $db->getItemFromDB("SELECT stit_HSNCode FROM finascop_stock_itemmaster WHERE stit_ID = {$itemDetails['fsto_ItemId']} ");
    $curItemDet = '';
    $curItemDet = '<tr>
            <td class="slno">' . $itemDetails['slNo'] . '</td>
            <td class="desc">' . $itemDetails['itemname'] . '</td>
            <td class="hsn">' . $itemDetails['itemHSN'] . '</td>
            <td class="qty">' . $itemDetails['fsto_ItemQty'] . '</td>
            <td class="rate">' . $itemDetails['fstro_ItemSPincTax'] . '</td>
            <td class="taxval">' . $itemDetails['fstro_totamtbeforetax'] . '</td>
            <td class="cgstrate">' . $itemDetails['fstro_cgst_percent'] . '%</td>
            <td class="cgstamt">' . $itemDetails['fstro_cgst_value'] . '</td>
            <td class="sgstrate">' . $itemDetails['fstro_sgst_percent'] . '%</td>
                <td class="sgstamt">' . $itemDetails['fstro_sgst_value'] . '</td>
                <td class="kfcrate">' . $itemDetails['fstro_kfc_percent'] . '%</td>
                     <td class="kfcamt">' . $itemDetails['fstro_kfc_value'] . '</td>            
            <td class="tot">' . $itemDetails['fstro_totamtaftertax'] . '</td>
            </tr>';
    $totalBfTax = $totalBfTax + $itemDetails['fstro_totamtbeforetax'];
    $totalCgst = $totalCgst + $itemDetails['fstro_cgst_value'];
    $totalSgst = $totalCgst + $itemDetails['fstro_sgst_value'];
    $totalAfTax = $totalAfTax + $itemDetails['fstro_totamtaftertax'];
    $invItemDetails .= $curItemDet;
}
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
                        <img style="width: 19mm; height: 15mm;" alt="logo" src="./resources/images/logo.png">
                    </th>
                    <th class="tg" colspan="3">
            <table class="ti">
                <tbody>
                    <tr>
                        <th class="ti" style="font-size: 85%;font-weight: bold;">
                            <?php echo SITE_TITLE; ?>
                        </th>
                    </tr>
                    <tr>
                        <td class="ti" style="font-size: 70%;">
                            <?php echo $branchDetails['br_Name']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="ti" style="font-size: 70%;">
                            <?php echo $branchDetails['br_Address']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="ti" style="font-size: 70%;">
                            <?php echo $branchDetails['br_City']; ?> - <?php echo $branchDetails['br_pincode']; ?>
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
                                        Order Number: <?php echo $toDetails['fsto_uid']; ?>
                                    </th>
                                </tr>
                                <tr>
                                    <th class="ti" style="text-align: left; font-size: 80%;">
                                        Order Date: <?php echo $toDetails['fsto_createdOn']; ?>
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
                        Challan No: <?php echo $orderDetails['quor_RefNo']; ?>
                    </th>
                </tr>
                <tr>
                    <th class="tg" style="text-align: left; font-size: 75%;">
                        <?php echo $consignee; ?>
                    </th>
                    <th class="tg" style="text-align: left; font-size: 75%;">
                        Challan Date: <?php echo $orderDetails['booked_at']; ?>
                    </th>
                </tr>
                <tr>
                    <td rowspan = '2'; class="tg" style="text-align: left; font-size: 75%;">
                        <?php echo $orderDetails['destination']; ?>
                    </td>
                    <td class="tg" style="text-align: left; font-size: 75%;">
                        Place of Supply: <?php echo $orderDetails['destination']; ?>
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
                        GST No. : 
                    </td>
                    <td class="tg" style="text-align: left; font-size: 75%;">
                        Vehicle No.: <?php echo $disdata['bcd_vehicleNo']; ?>
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
                    <th class="kfc" colspan="2">
                        KFC
                        <br>
                        Rate | Amount
                    </th>
                    <th class="tot">
                        Total
                    </th>
                </tr>
                <?php echo $invItemDetails; ?>
                <tr>
                    <td class="tg" colspan="5" style="text-align: right; font-size: 75%;"> Grand Total: </td>
                    <td class="tg" style="text-align: right; font-size: 65%;"><?php echo $toDetails['fsto_amtbeforetax']; ?></td>
                    <td class="tg"></td>
                    <td class="tg" style="text-align: right; font-size: 65%;"><?php echo $toDetails['fsto_cgstval']; ?></td>
                    <td class="tg"></td>
                    <td class="tg" style="text-align: right; font-size: 65%;"><?php echo $toDetails['fsto_sgstval']; ?></td>
                    <td class="tg"></td>
                    <td class="tg" style="text-align: right; font-size: 65%;"><?php echo $toDetails['fsto_kfcval']; ?></td>
                    <td class="tg" style="text-align: right; font-size: 65%;"><?php echo $toDetails['fsto_amtaftertax']; ?></td>
                </tr>
                <tr>
                    <td class="tg" colspan="5" style="text-align: right; font-size: 75%;">Handling Charges</td>
                    <td class="tg" colspan="8" style="text-align: right; font-size: 65%;"><?php echo $toDetails['fsto_handlingcharge']; ?></td>
                </tr> 
                <tr>
                    <td class="tg" colspan="5" style="text-align: right; font-size: 75%;">Discount</td>
                    <td class="tg" colspan="8" style="text-align: right; font-size: 65%;">-<?php echo $toDetails['fsto_discount']; ?></td>
                </tr> 
                <tr>
                    <td class="tg" colspan="5" style="text-align: right; font-size: 75%;">Net Amount</td>
                    <td class="tg" colspan="8" style="text-align: right; font-size: 65%;"><?php echo $toDetails['fsto_netamount']; ?></td>
                </tr>
                <tr>
                    <td class="tg" colspan="5" style="text-align: right; font-size: 75%;">Total Values in Words</td>
                    <td class="tg" colspan="8" style="text-align: right; font-size: 65%;"><?php
                        $totInWords = getIndianCurrency(number_format((float) $toDetails['fsto_netamount'], 2, '.', ''));
                        echo $totInWords;
                        ?></td>
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