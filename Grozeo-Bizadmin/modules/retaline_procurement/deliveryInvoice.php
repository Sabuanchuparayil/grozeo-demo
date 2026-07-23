<?php
$quor_id = $_REQUEST['quor_id'];
$orderDetails = $db->getFromDb("SELECT quor_RefNo as booking_no,quor_CreatedOn,DATE_FORMAT(quor_Date,'%d-%m-%Y') as booked_at,quor_PickupPhone,quor_PickupName,quor_DeliveryName,quor_DeliveryPhone,quor_Deliverybr_id,"
        . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",quor_PickupName,quor_DeliveryName) as customer,"
        . "quor_PickupLocation as source,"
        . "quor_DeliveryLocation as destination,"
        . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . ",'DELIVER', if(quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",'DISPATCH','PICKUP')) as type,"
        . "quor_id, "
        . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",quor_DeliveryLat,quor_PickupLat) as latitude,"
        . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",quor_DeliveryLng,quor_PickupLng) as longitude, "
        . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . " or quor_Status=" . ORDER_PICKUP_WAITING_FOR_DISPATCH_DLS_ID . ",' " . GMAP_DELIVERY_ICON . "','" . GMAP_PIKCUP_ICON . "') as mapicon, "
        . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . "," . FINASCOP_DB . "getBranchGodownGeoLocation(quor_Deliverybr_id,1), " . FINASCOP_DB . "getBranchGodownGeoLocation(quor_Pickupbr_id,1)) as brGodownLati,"
        . "if(quor_Status=" . ORDER_DELIVERY_ROUTE_ASSIGNED_DLS_ID . "," . FINASCOP_DB . "getBranchGodownGeoLocation(quor_Deliverybr_id,2), " . FINASCOP_DB . "getBranchGodownGeoLocation(quor_Pickupbr_id,2)) as brGodownLong,"
        . "(SELECT dls_DelStatus FROM qugeo_deliverystatus WHERE dls_ID = quor_Status) AS djStatus,quor_Status,"
        . "CASE WHEN quor_Type=1 THEN 'Drive' WHEN quor_Type=2 THEN 'Hired' WHEN quor_Type=3 THEN 'Customer Pickup' WHEN quor_Type=4 THEN 'Courier' WHEN quor_Type=5 THEN 'Driver Pickup' WHEN quor_Type=6 THEN 'Manual Delivery' END AS quor_TypeName,quor_Type,"
        . "if(quor_DeliveredTime <> '0000-00-00 00:00:00',DATE_FORMAT(quor_DeliveredTime,'%d-%m-%Y'),DATE_FORMAT(quor_DeliveryConfTime,'%d-%m-%Y %H:%i:%s')) as quor_DeliveredTime,"
        . "DATE_FORMAT(quor_ScheduleOpeningTime,'%d-%m-%Y %H:%i:%s') as quor_ScheduleOpeningTime,quor_TransferOrder_id,quor_PacketCount,quor_QugeoPickupDDBDriverId,quor_QugeoDeliveryDDBDriverId "
        . " FROM " . FINASCOP_DB . " qugeo_order WHERE quor_id={$quor_id}", true);

$toDetails = $db->getFromDB("SELECT * FROM finascop_stock_transfer_order WHERE fsto_id = {$orderDetails['quor_TransferOrder_id']}", true);
$trDetails = $db->getFromDB("SELECT fstr_id,fstr_uid,DATE_FORMAT(fstr_createdOn,'%d-%m-%Y %H:%i:%s') as fstr_createdOn FROM finascop_stock_transfer_request WHERE fstr_id = {$toDetails['fstr_id']}", true);
$fstos_status = $db->getItemFromDB("SELECT fstos_status FROM finascop_stock_transfer_order_status WHERE fstos_id = {$toDetails['fsto_status']}");
$consigner = $db->getItemFromDB("SELECT br_Name FROM finascop_branch where br_ID = {$toDetails['fsto_source']}");
$consignerAddress = $db->getItemFromDB("SELECT br_Address FROM finascop_branch where br_ID = {$toDetails['fsto_source']}");
$consignerEmailContact = $db->getFromDB("SELECT br_Email,br_Phone FROM finascop_branch where br_ID = {$toDetails['fsto_source']}", true);
$toDetailItems = $db->getMultipleData("SELECT fsto_ItemId,fsto_ItemQty FROM finascop_stock_transfer_order_details WHERE fsto_id = {$orderDetails['quor_TransferOrder_id']}", true);

$invoiceData = $db->getFromDB("SELECT * FROM TransferInvoice WHERE ti_fsto_id = {$orderDetails['quor_TransferOrder_id']}",true);
$invoiceDetailsData = $db->getMultipleData("SELECT * FROM TransferInvoiceDetails WHERE ti_id = {$invoiceData['id']}",true);
switch ($toDetails['fsto_ordertype']) {
    case 0:
        $fsto_ordertype = 'Branch Transfer';
        $consignee = $db->getItemFromDB("SELECT br_Name FROM finascop_branch where br_ID = {$toDetails['fsto_destination']}");
        $consigneeAddress = $db->getItemFromDB("SELECT br_Address FROM finascop_branch where br_ID = {$toDetails['fsto_destination']}");
        break;
    case 1:
        $fsto_ordertype = 'B2C';
        $consignee = $db->getItemFromDB("SELECT cust_customer_name FROM retaline_customer WHERE cust_id = {$toDetails['fsto_destination']}");
        $consigneeAddress = $db->getItemFromDB("SELECT CONCAT(deli_house_name,', ',deli_land_mark,', ',deli_city,', 'deli_post) FROM retaline_customer_delivery_info WHERE deli_customer_id = {$toDetails['fsto_destination']} AND deli_is_primary = 1");
        break;
    case 2:
        $fsto_ordertype = 'B2B';
        $consignee = $db->getItemFromDB("SELECT b2b_Customer_Name FROM retaline_B2Bcustomer where b2b_Customer_ID =  {$toDetails['fsto_destination']}");
        $consigneeAddress = $db->getItemFromDB("SELECT b2b_Customer_Address FROM finascop_branch where br_ID = {$toDetails['fsto_destination']}");
        break;
    case 3:
        $fsto_ordertype = 'Return';
        $consignee = $db->getItemFromDB("SELECT br_Name FROM finascop_branch where br_ID = {$toDetails['fsto_destination']}");
        $consigneeAddress = $db->getItemFromDB("SELECT br_Address FROM finascop_branch where br_ID = {$toDetails['fsto_destination']}");
        break;
    case 4:
        $fsto_ordertype = 'Branch Distribution';
        $consignee = $db->getItemFromDB("SELECT br_Name FROM finascop_branch where br_ID = {$toDetails['fsto_destination']}");
        $consigneeAddress = $db->getItemFromDB("SELECT br_Address FROM finascop_branch where br_ID = {$toDetails['fsto_destination']}");
        break;
}
switch ($orderDetails['quor_Type']) {
    
    case 1:
        $quor_TypeName = 'Drive';
        $activedriveid = ($orderDetails['quor_QugeoPickupDDBDriverId'] == '' ? $orderDetails['quor_QugeoDeliveryDDBDriverId'] : $orderDetails['quor_QugeoPickupDDBDriverId']);
        if ($activedriveid != '') {
            $vehicleapi = getUsedVehicleDetails($activedriveid);
            if (isset($vehicleapi['v_no'])) {
                $deliveredBy = $vehicleapi['v_no'];
                $deliveredByTime = date("Y-m-d H:i:s", strtotime($vehicleapi['createddatetime']));
            }
        }
        break;
    case 2:
        $quor_TypeName = 'Hired';
        $deliveryDetails = $db->getFromDB("SELECT bcd_driver,bcd_vehicleNo,bcd_dispatchDate FROM qugeo_order_dispatch WHERE quor_id = {$quor_id}", true);
        $deliveredBy = $deliveryDetails['bcd_driver'];
        break;
    case 3:
        $quor_TypeName = 'Customer Pickup';
        $deliveryDetails = $db->getFromDB("SELECT qcp_pickupBy,qcp_pickupDate,qcp_pickupTime FROM qugeo_customer_pickup WHERE quor_id = {$quor_id}", true);
        $deliveredBy = $deliveryDetails['qcp_pickupBy'];
        break;
    case 4:
        $quor_TypeName = 'Courier';
        $deliveryDetails = $db->getFromDB("SELECT qoc_qcn,qoc_date,qoc_time FROM qugeo_order_courier WHERE quor_id = {$quor_id}", true);
        $deliveredBy = $deliveryDetails['qoc_qcn'];
        break;
    case 5:
        $quor_TypeName = 'Driver Pick up';
        $deliveryDetails = $db->getFromDB("SELECT qcp_pickupBy,qcp_pickupDate,qcp_pickupTime FROM qugeo_customer_pickup WHERE quor_id = {$quor_id}", true);
        $deliveredBy = $deliveryDetails['qcp_pickupBy'];
        
        break;
    case 6:
        $quor_TypeName = 'Manual Delivery';
        $deliveryDetails = $db->getFromDB("SELECT qmd_deliveredBy,qmd_Date,qmd_Time,qmd_remarks FROM qugeo_manual_deliver WHERE quor_id = {$quor_id}", true);
        $deliveredBy = $deliveryDetails['qmd_deliveredBy'];
        break;
}
?>
<html>
    <style>

        th, td, p{font-family: arial, sans-serif; font-size: 8px; line-height: 12px;}
        p{font-family: arial, sans-serif; font-size: 8px; margin: 0; line-height: 12px;}


    </style>



    <div class="container-fluid">
        <div class='panel'>
            <table width="100%" border="1" cellspacing="0" cellpadding="0">

                <tr>
                    <td>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td width="50%" valign="middle" align="center" style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">
                                    <img  src="resources/mypharmacy/logo-invoice.png">
                                </td>
                                <td>
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td colspan="2" align="center" style="padding:8px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-weight: bold; font-size: 24px; line-height: 26px;">INVOICE</td>
                                        </tr>
                                        <tr>
                                            <td width="50%" align="right" style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Invoice No.</td>
                                            <td width="50%" style="padding:8px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo 'INV/' . $orderDetails['booking_no']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%" align="right" style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Invoice Dt.</td>
                                            <td width="50%" style="padding:8px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $orderDetails['booked_at']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%" align="right" style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Order No.</td>
                                            <td width="50%" style="padding:8px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $orderDetails['booking_no']; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%" align="right" style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Delivery Mode</td>
                                            <td width="50%" style="padding:8px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $quor_TypeName; ?></td>
                                        </tr>
                                        <tr>
                                            <td width="50%" align="right" style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Delivered By</td>
                                            <td width="50%" style="padding:8px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $deliveredBy; ?></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            
                            <tr>
                                <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; font-weight: bold; line-height: 22px;">From :</td>
                                <td style="padding:8px; border-bottom: 1px solid #4c4c4c;  font-family: arial, sans-serif; font-size: 16px; font-weight: bold; line-height: 22px;">To : </td>
                            </tr>
                            <tr>
                                <td valign="top" height="100" style="padding:8px; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><b><?php echo $consigner; ?></b><br><?php echo $consignerAddress; ?></td>
                                <td valign="top" height="100" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"> <b><?php echo $consignee; ?></b><br><?php echo $consigneeAddress; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">S/n</td>
                                <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Item</td>
                                <td width="9%" style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">HSN Code</td>
                                <td width="9%" style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Qty</td>
                                <td width="9%" style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Return</td>
                                <td width="9%" style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">UoM</td>
                                <td width="9%" style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Unit Rate</td>
                                <td width="9%" style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Tax%</td>
                                <td width="9%" style="padding:8px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Amount</td>
                            </tr>
                            <?php
                            $subTotal = 0;
                            $taxableValueTotal = 0;
                            $itemsgstTotal = 0;
                            $itemcgstTotal = 0;
                            $j = 0;
                            for ($i = 0; $i < count($invoiceDetailsData); $i++) {

                                if ($invoiceDetailsData[$i]['tiItemStockQty'] > 0) {

                                    $j = $j + 1;
                                    
                                    $fsto_ItemName = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$invoiceDetailsData[$i]['tiItemId']}");
                                    $packageType = $db->getFromDB("SELECT cs_package_type_name,ds_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$invoiceDetailsData[$i]['tiItemId']}", true);
                                    $itemHSN = $db->getItemFromDB("SELECT stit_HSNCode FROM finascop_stock_itemmaster WHERE stit_ID = {$invoiceDetailsData[$i]['tiItemId']} ");
                                    $hsn_code = $db->getItemFromDB("SELECT hsn_code FROM finascop_hsn WHERE hsn_id = {$itemHSN} ");
                                    $itemUnit = $db->getItemFromDB("SELECT cosb_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$invoiceDetailsData[$i]['tiItemId']} ");
                                    $itemGST = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$invoiceDetailsData[$i]['tiItemId']} ");


                                    ?>
                                    <tr>
                                        <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $j; ?></td>
                                        <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $fsto_ItemName; ?></td>
                                        <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $hsn_code; ?></td>
                                        <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $invoiceDetailsData[$i]['tiItemStockQty']; ?></td>
                                        <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $invoiceDetailsData[$i]['tiItemReturnQty']; ?></td>
                                        <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $itemUnit; ?></td>
                                        <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $invoiceDetailsData[$i]['tiItemUnitRate']; ?></td>
                                        <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $invoiceDetailsData[$i]['tiItemGST']; ?>%</td>
                                        <td style="padding:8px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $invoiceDetailsData[$i]['tiItemAmount']; ?></td>
                                    </tr>
                                <?php
                                }
                            }
                            ?> 


                            <tr>
                                <td colspan="6" valign="bottom" style="padding:8px; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Amount in words :Rupees <?php
                                    echo ucfirst
                                            (numberTowords($invoiceData['tiTotal']));
                                    ?>   only</td>
                                <td colspan="3">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; font-weight: bold; line-height: 22px;">Sub Total</td>
                                            <td width="33%" style="padding:8px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo number_format((float) $invoiceData['tiSubTotal'], 2, '.', ''); ?></td>
                                        </tr>
                                        
                                        <tr>
                                            <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Handling Charge</td>
                                            <td style="padding:8px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $invoiceData['tiHandlingCharge']; ?></td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; font-weight: bold; line-height: 22px;">Payable Amount</td>
                                            <td style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo number_format((float) $invoiceData['tiTotal'], 2, '.', ''); ?></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td height="20" style="line-height:0; font-size: 1px;"></td>
                </tr>

                <tr>
                    <td>
                        <table width="100%" border="0" class="" cellspacing="0" cellpadding="0">
                            <tr>
                                <td>
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td style="padding:8px; border-bottom: 1px solid; font-family: arial, sans-serif; font-size: 14px; line-height: 22px;">For any queries, contact : </td>
                                        </tr>
                                        <tr>
                                            <td height="80" style="padding:8px; font-family: arial, sans-serif; font-size: 14px; line-height: 22px; font-weight: bold;"><?php echo $consignerAddress; ?><br> <?php echo $consignerEmailContact['br_Email']; ?>, <?php echo $consignerEmailContact['br_Phone']; ?></td>
                                        </tr>
                                    </table>
                                </td>
                                <td width="37.5%" height="100" valign="bottom" align="center" style="padding:8px; border-left: 1px solid; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Authorised Sign.</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td>
                        <table width="100%" border="0" class="" cellspacing="0" cellpadding="0">
                            <tr>
                                <td align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">This is a software generated invoice</td>
                            </tr>
                        </table>
                    </td>
                </tr>

            </table>
        </div><!--panel-->
    </div>
</html>