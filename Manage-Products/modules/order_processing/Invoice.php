<?php
$order_order_id = $_REQUEST['order_order_id'];
$order_id = $_REQUEST['order_id'];
$toDetails = $db->getFromDB("SELECT fsto_id,fstr_id FROM finascop_stock_transfer_order WHERE fsto_ordertype = 1 AND fstr_id = {$order_id}", true);
//$barcodes = $db->getMultipleData("SELECT stiid_barcode,stiid_id FROM finascop_stock_transfer_order_details_barcodes fstodb INNER JOIN finascop_stock_item_inventorydetails fsii ON fsii.stiid_id = fstodb.stiid_id WHERE fsto_id = {$toDetails['fsto_id']}", true);

$customerOrderDetails = $db->getFromDB("SELECT order_branch_id,order_customer_id,order_total_amount,order_delivery_charge,order_total_gst,payment_mode,DATE_FORMAT(order_confirm_date,'%a %d %M %Y') AS  order_confirm_date,"
        . "DATE_FORMAT(order_confirmed_on,'%a %d %M %Y %H:%i:%s') AS  order_confirmed_on,order_kfc_amount,order_saved_amount,total,order_discount_amount,order_roundoff "
        . " FROM retaline_customer_order WHERE order_id = {$order_id} AND order_order_id = '{$order_order_id}'", true);
$branchSql = "SELECT br_Name,br_City,br_District,br_State,br_Address,br_pincode,br_Phone,br_Fax FROM finascop_branch WHERE br_ID = {$customerOrderDetails['order_branch_id']}";
$customerDetails = $db->getFromDB("SELECT cust_customer_name,cust_email,cust_mobile,cust_walletbalance FROM retaline_customer WHERE cust_id = {$customerOrderDetails['order_customer_id']}", true);
$branchDetails = $db->getFromDB($branchSql, true);
$custName = $db->getItemFromDB("SELECT cust_customer_name FROM retaline_customer WHERE cust_id = {$customerOrderDetails['order_customer_id']}");
$custAddress = $db->getFromDB("SELECT order_customer_name,order_house_no,order_house_name,order_land_mark,order_city,order_post,order_state,order_pin,order_country,order_contact_no FROM retaline_customer_order_delivery_address "
        . "WHERE customer_order_id = {$order_id}", true);
$ordeItems = $db->getMultipleData("SELECT item_id,customer_order_id,item_product_id,item_group_id,item_order_qty,item_price,item_sales_price,item_retail_price,item_cgst,item_sgst,item_igst,item_kfc,item_amount,"
        . "item_discount,item_discount_total FROM retaline_customer_order_items WHERE customer_order_id = {$order_id}", true);
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
$companyDetails = $db->getFromDB("SELECT comp_name,comp_address,comp_GSTIN,comp_Ph,comp_GSTIN,comp_dlno1,comp_dlno2,comp_fssaino FROM finascop_company WHERE comp_id = {$poCompany}", true);
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?= SITE_TITLE ?></title>
    </head>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script> 

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap" rel="stylesheet">
    <style>
        body{
            font-family: 'Poppins', sans-serif;
        }
        .container {padding: 25px;}
        .logo{width: 150px;}
        .logo img {width: 100%; height: auto;}
        h1 {font-size: 30px;}
        ul {padding: 0px;}
        .address li {width: 50%; float: left; list-style: none; padding: 0px;}
        .txt_r {text-align: right; padding-right: 20px!important;}
        .txt_c {text-align: center;}
        .txt_l {text-align: left;}
        .pad {padding: 8px!important;}
        .valign {vertical-align: top!important;}
    </style>
    <body>

        <div class="container">
            <div class="row">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th scope="col" class="txt_l valign" valign="top"> <div class="logo"> <a href="#"> <img src="resources/mypharmacy/admin-logo.png" alt="logo"> </a> </div></th>
                        <th scope="col">
                        <h1><?php echo $pojectName; ?></h1>
                        <p><?php echo $branchDetails['br_Name']; ?><br>
                            <?php echo $branchDetails['br_Address']; ?>, <br>
                            <?php echo $branchDetails['br_City']; ?> Pin - <?php echo $branchDetails['br_pincode']; ?><br>Phone - <?php echo $branchDetails['br_Phone']; ?></p>	
                        </th>
                        <th colspan="2" scope="col" valign="top" class="valign">
                        <table>
                            <?php if($_SESSION['admin']->IS_MEDICINE_REQUIRED == 1){ ?>
                            <tr>
                                <td class="txt_r">DL No 1:  </td>
                                <td colspan="3"> <?php $companyDetails['comp_dlno1']; ?></td>
                            </tr>
                            <tr>
                                <td class="txt_r">DL No 2:  </td>
                                <td colspan="3"> <?php $companyDetails['comp_dlno2']; ?></td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td class="txt_r">FSSAI No :    </td>
                                <td colspan="3"><?php $companyDetails['comp_fssaino']; ?></td>
                            </tr>
                            <tr>
                                <td class="txt_r">GST No :    </td>
                                <td colspan="3"><?php $companyDetails['comp_GSTIN']; ?></td>
                            </tr>


                        </table>
                        <?php
                        switch ($customerOrderDetails['payment_mode']) {
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
                        </th>

                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="txt_c"><h2><?php echo $payMod; ?> ORDER</h2></td>
                            </tr>
                            <tr>

                                <td colspan="2">Order Number: <?php echo $order_order_id; ?><br>
                                    Order Date & Time: <?php echo $customerOrderDetails['order_confirm_date']; ?> <br>
                                    Delivery Schedule:   Immediate<br>
                                    <table style="width: 100%;">
                                        <tr>
                                            <td  width="25%">Total Number of Items  : </td>
                                            <td width="25%"><?php echo $noOfItems; ?>    </td>
                                            <td  width="25%">Weight (in Kg) :  </td>
                                            <td  width="25%">&nbsp;</td>
                                        </tr>
                                    </table>
                                </td>

                                <td colspan="2" width="40%">
                                    Total Amount :    INR <?php echo $customerOrderDetails['total']; ?><br>
                                    Amount Paid : INR <?php
                                    if ($customerOrderDetails['payment_mode'] == 1) {
                                        echo '0.00';
                                    } else {
                                        echo 'Rs. ' . $customerOrderDetails['total'];
                                    }
                                    ?>
                                    <h4>AMOUNT TO BE COLLECTED    : <?php
                                        if ($customerOrderDetails['payment_mode'] == 1) {
                                            echo 'Rs. ' . $customerOrderDetails['total'];
                                        } else {
                                            echo '0.00';
                                        }
                                        ?></h4></td>

                            </tr>
                            <tr>
                                <td colspan="4" class="txt_c">&nbsp;</td>


                            </tr>
                            <tr>
                                <td>Invoice Date</td>
                                <td><?php echo $customerOrderDetails['order_confirm_date']; ?></td>
                                <td colspan="2"><?php if($_SESSION['admin']->IS_MEDICINE_REQUIRED == 1){ ?> Name of the Prescriber  : Dr. <?php } ?> </td>
                            </tr>

                            <tr>
                                <td>Invoice No</td>
                                <td><?php echo $order_order_id; ?></td>
                                <td colspan="2"> Reg No.   </td>
                            </tr> 

                            <tr>

                                <td colspan="4">&nbsp; </td>
                            </tr> 

                            <tr>
                                <td colspan="2">BILLED TO  </td>
                                <td colspan="2">DELIVER TO</td>
                            </tr> 
                            <tr>
                                <td colspan="2"><?php echo $custName; ?>  </td>
                                <td colspan="2"><?php echo $custName; ?> </td>

                            </tr>  
                            <tr>
                                <td colspan="2"><?php echo $custAddress['order_house_no']; ?> <?php echo $custAddress['order_house_name']; ?>, <?php echo $custAddress['order_land_mark']; ?>, <?php echo $custAddress['order_city']; ?>, <?php echo $custAddress['order_state']; ?> - <?php echo $custAddress['order_pin']; ?></td>
                                <td colspan="2"><?php echo $custAddress['order_house_no']; ?> <?php echo $custAddress['order_house_name']; ?>, <?php echo $custAddress['order_land_mark']; ?>, <?php echo $custAddress['order_city']; ?>, <?php echo $custAddress['order_state']; ?> - <?php echo $custAddress['order_pin']; ?> </td>

                            </tr> 
                            <tr>

                            <tr>
                                <td colspan="2">Mobile:<?php echo $custAddress['order_contact_no']; ?> <br>	
                                </td>
                                <td colspan="2">Mobile: <?php echo $custAddress['order_contact_no']; ?> <br>	
                                </td>
                            </tr> 

                            <tr>
                                <td colspan="4">&nbsp;</td>
                            </tr>  

                            <tr>
                                <td colspan="4" style="padding: 0px">


                                    <table style="width: 100%;" class="table-bordered"  >
                                        <tr align="center" >
                                            <th  class="pad" rowspan="2">Sl No</th>
                                            <th class="pad" rowspan="2">Description of Goods</th>
                                            <th class="pad" rowspan="2">MFR</th>
                                            <th class="pad" rowspan="2">HSN Code</th>
                                            <th class="pad" rowspan="2">Batch No</th>
                                            <th class="pad" rowspan="2">Exp Date</th>
                                            <th class="pad" rowspan="2">Qty</th>
                                            <th class="pad" rowspan="2">Unit</th>
                                            <th class="pad" rowspan="2">MRP</th>
                                            <th class="pad" rowspan="2">Rate</th>
                                            <th class="pad" rowspan="2">Discount</th>
                                            <th class="pad" rowspan="2">Taxable Value</th>
                                            <th class="pad" colspan="2">CGST / IGST</th>
                                            <th class="pad" colspan="2">SGST</th>
                                            <th class="pad" rowspan="2">Cess<br>Rate 1%</th>
                                            <th class="pad" rowspan="2">Total</th>
                                        </tr>
                                        <tr>
                                            <th class="pad">Rate %</th>
                                            <th class="pad">Amount</th>
                                            <th class="pad">Rate %</th>
                                            <th class="pad">Amount</th>
                                        </tr>

                                        <?php
                                        $totalMrp = 0;
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
                                            ?>
                                            <tr>
                                                <td class="pad"><?php echo $j; ?></td>
                                                <td class="pad"><?php
                                        $itemName = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$ordeItems[$i]['item_product_id']} ");
                                        echo $itemName;
                                            ?></td>
                                                <td class="pad"><?php
                                                $itemManuf = $db->getItemFromDB("SELECT med_manufacturename FROM finascop_stock_itemmaster WHERE stit_ID = {$ordeItems[$i]['item_product_id']} ");
                                                echo $itemManuf;
                                            ?></td>
                                                <td class="pad"><?php
                                                $itemHSN = $db->getItemFromDB("SELECT stit_HSNCode FROM finascop_stock_itemmaster WHERE stit_ID = {$ordeItems[$i]['item_product_id']} ");
                                                $hsn_code = $db->getItemFromDB("SELECT hsn_code FROM finascop_hsn WHERE hsn_id = {$itemHSN} ");
                                                $itemUnit = $db->getItemFromDB("SELECT cosb_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$ordeItems[$i]['item_product_id']} ");
                                                echo $hsn_code;
                                            ?></td>
                                                <td class="pad"><?php echo $batch; ?></td>
                                                <td class="pad"><?php echo $expiryDates; ?></td>
                                                <td class="pad"><?php echo $ordeItems[$i]['item_order_qty']; ?></td>
                                                <td class="pad"><?php echo $itemUnit;?></td>
                                                <td class="pad"><?php
                                                if ($ordeItems[$i]['item_retail_price'] > 0) {
                                                    $itemMRP = $ordeItems[$i]['item_retail_price'];
                                                    echo $itemMRP;
                                                } else {
                                                    echo 0;
                                                }

                                                $totalMrp = $totalMrp + (floatval($itemMRP) * $ordeItems[$i]['item_order_qty']);

                                                $taxableValue = $ordeItems[$i]['item_sales_price'] * 100 / (100 + $itemGST);
                                                $taxableValue = round($taxableValue, 2);
                                                $itemcgst = $taxableValue * $itemCGSTpercent / 100;
                                                $itemcgst = round($itemcgst, 2);

                                                $itemsgst = $taxableValue * $itemSGSTpercent / 100;
                                                $itemsgst = round($itemsgst, 2);
                                            ?></td>
                                                <td class="pad"><?php echo $ordeItems[$i]['item_sales_price']; ?></td>
                                                <td class="pad"><?php echo $ordeItems[$i]['item_discount']; ?></td>
                                                <td class="pad"><?php echo $taxableValue; ?></td>
                                                <td class="pad" ><?php echo $itemCGSTpercent; ?></td>
                                                <td class="pad"><?php echo $itemcgst; ?></td>
                                                <td class="pad"><?php echo $itemSGSTpercent; ?></td>
                                                <td class="pad"><?php echo $itemcgst; ?></td>
                                                <td class="pad"><?php echo $ordeItems[$i]['item_kfc']; ?></td>
                                                <td class="pad"><?php echo $ordeItems[$i]['item_price']; ?></td>
                                            </tr>
                                        <?php } ?>
                                        <?php
                                        //$discount = floatval($totalMrp) - floatval($amtPayable);
                                        //$discount = floatval($totalMrp) - floatval($order_amt_total);
                                        $discount = $customerOrderDetails['order_saved_amount'];
                                        $discountPercent = ($discount / floatval($totalMrp)) * 100;
                                        $taxDiv = floatval($customerOrderDetails['order_total_gst']) / 2;
                                        $taxDiv = number_format($taxDiv, 2);
                                        ?>

                                        <tr>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad" >&nbsp;</td>
                                            <td class="pad" >&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>		
                                        </tr>
                                        <tr>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad" >&nbsp;</td>
                                            <td class="pad" >&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>	
                                        </tr>
<!--                                        <tr>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad txt_r" colspan="3">Grand Total</td>
                                            <td class="pad">&nbsp;</td>



                                            <td class="pad">&nbsp;</td>
                                            <td class="pad" >&nbsp;</td>
                                            <td class="pad" >&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad"><?php echo $order_amt_total; ?></td>
                                        </tr>-->
                                        <tr>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>

                                            <td class="pad txt_r" colspan="4">Handling Charges</td>
                                            <td class="pad txt_r" colspan="8"><?php echo $customerOrderDetails['order_delivery_charge']; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>

                                            <td class="pad txt_r" colspan="4">KFC</td>
                                            <td class="pad txt_r" colspan="8"><?php echo $customerOrderDetails['order_kfc_amount']; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>

                                            <td class="pad txt_r" colspan="4">Round Off</td>
                                            <td class="pad txt_r" colspan="8"><?php echo $customerOrderDetails['order_roundoff']; ?></td>
                                        </tr>
                                        
                                        <tr>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>

                                            <td class="pad txt_r" colspan="4">Total Values in Figures</td>
                                            <td class="pad txt_r" colspan="8"><?php echo $customerOrderDetails['total']; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>
                                            <td class="pad">&nbsp;</td>

                                            <td class="pad txt_r" colspan="4">Total Values in Words</td>
                                            <td class="pad txt_l" colspan="8">Rupees <?php echo numberTowords($customerOrderDetails['total']); ?>   only</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" >&nbsp; </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="txt_c">This is a Computer Generated Document, hence does not require Signature. </td>

                            </tr>
                            <tr>
                                <td colspan="4" >&nbsp; </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="txt_c">Declaration: Certified that all the particulars shown in the above Tax Invoice are true and correct and that my/ our Registration under the CGST/ SGST/ IGST Acts 2017 is valid as on the date of this invoice </td>

                            </tr>
                            <tr>
                                <td colspan="4" >&nbsp; </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="txt_c">Return Address : <?php echo $pojectName; ?>,<?php echo $branchDetails['br_Name']; ?>,<?php echo $branchDetails['br_Address']; ?>,<?php echo $branchDetails['br_City']; ?> - <?php echo $branchDetails['br_pincode']; ?> </td>

                            </tr>
                            <tr>
                                <td colspan="4" >&nbsp; </td>
                            </tr>

                            <tr>
                                <td colspan="4" class="txt_c"><h3>You saved <?php echo number_format($discountPercent, 2); ?>% = &#x20B9;<?php echo number_format($discount, 2); ?>   :      Thank you for Shopping</h3> </td>

                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>