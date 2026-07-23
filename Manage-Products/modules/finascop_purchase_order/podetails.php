<?php
$PoId = $_REQUEST['PoId'];
$poDetails = $db->getFromDB("SELECT branch_id,fpo_vendorName,fpo_vendorId,fpo_poNumber,DATE_FORMAT(fpo_poDate,'%d-%m-%Y') as fpo_poDate,fpo_poValue,fpo_poFinalValue,fpo_paymentTerms,fpo_poDeliveryType,DATE_FORMAT(fpo_poDeliveryDate,'%d-%m-%Y') as fpo_poDeliveryDate,"
        . "fpo_validityType,DATE_FORMAT(fpo_validDate,'%d-%m-%Y') as fpo_validDate,DATE_FORMAT(fpo_createdon,'%d-%m-%Y %H:%i:%s') as fpo_createdon,fpo_poOrderedby,fpo_shippingcharge,fpo_gdiscpercent,fpo_centralStore FROM finascop_purchase_order WHERE fpo_id = {$PoId}", true);
$vendorDetails = $db->getFromDB("SELECT stpa_Fname,stpa_Phone,stpa_Address,stpa_City,stpa_PINCODE,stpa_MobileNo,stpa_Email FROM finascop_stock_party WHERE stpa_id = {$poDetails['fpo_vendorId']}", true);
$test_sql = "SELECT fpod_itemid,(SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID=fpod_itemid) as taxRate,fpod_itemname,fpod_itemmrp,fpod_itemqty,fpod_itemoffrqty,fpod_totalqty,fpod_itemoffrrate,fpod_itemaddidisc,fpod_effectiverate,fpod_idiscountcalculus,fpod_netamount,fpod_amount,"
        . "fpod_giftname,fpod_giftqty,fpod_notes,fpod_purchasingUnit,IF(fpod_itemaddidisc > 0,(CONCAT(fpod_itemaddidisc,'',IF(fpod_idiscountcalculus = 'Amount',' Rs',' %'))),'') AS itemDisc FROM finascop_purchase_order_details WHERE fpod_fpoId = {$PoId}";
$podDetails = $db->getMultipleData($test_sql, true);
$cnt_test = count($podDetails);
$name = $db->getItemFromDB("SELECT CONCAT(FirstName,' ',LastName) FROM finascop_usr_profile WHERE UserId = {$poDetails['fpo_poOrderedby']}");
$branchSql = "SELECT br_Name,br_City,br_District,br_State,br_Address,br_pincode,br_Phone FROM finascop_branch WHERE br_ID = {$poDetails['branch_id']}";
$branchDetails = $db->getFromDB($branchSql, true);
$ctrlStreSql = "SELECT br_Name,br_City,br_District,br_State,br_Address,br_pincode,br_Phone FROM finascop_branch WHERE br_ID = {$poDetails['fpo_centralStore']}";
$csDetails = $db->getFromDB($ctrlStreSql, true);

$pojectName = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'PROJECT_NAME'");
$poCompany = $db->getItemFromDB("SELECT comp_id FROM finascop_branch_company WHERE br_Id = {$poDetails['branch_id']}");
$companyDetails = $db->getFromDB("SELECT comp_address,comp_GSTIN,comp_Ph FROM finascop_company WHERE comp_id = {$poCompany}", true);
$handlinchgGstVal = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'HANDLING_CHRG_GST'");
$pojectName = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'PROJECT_NAME'");
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
                                <th scope="col" class="txt_l valign" valign="top" colspan="2" > <div class="logo"> <a href="#"> <img src="resources/mypharmacy/admin-logo.png" alt="logo"> </a> </div></th>



                        </tr>
                        <tr>
                            <th scope="col" class="txt_l valign" valign="top" colspan="2" style="color: #01aded; text-align: center; text-transform: uppercase;"><h2>Purchase Order</h2></th>



                        </tr>

                        <tr>

                            <td >To <br>
                                <?php echo $vendorDetails['stpa_Fname']; ?>,<?php echo $vendorDetails['stpa_Address']; ?><br>Phone-<?php echo $vendorDetails['stpa_MobileNo']; ?><br>Pin-<?php echo $vendorDetails['stpa_PINCODE']; ?>

                            </td>
                            <td style="border-right: 0px solid #01aded;">PO Number: <?php echo $poDetails['fpo_poNumber']; ?><br>
                                PO Date: <?php echo $poDetails['fpo_poDate']; ?>

                            </td>

                        </tr>

                        <tr>
                            <td style="text-align: center;" >Bill To: </td>
                            <td style="text-align: center;" >Ship To: </td>
                        </tr>

                        <tr>
                            <td ><?php echo $branchDetails['br_Name'] ?><br>
                                <?php echo $branchDetails['br_Address']; ?><br>
                                Pin: <?php echo $branchDetails['br_pincode']; ?><br>
                                Phone: <?php echo $branchDetails['br_Phone']; ?><br>
                            </td>
                            <td ><?php echo $csDetails['br_Name'] ?>,<br>
                                <?php echo $csDetails['br_Address']; ?><br>
                                Pin: <?php echo $csDetails['br_pincode']; ?><br>
                                Phone: <?php echo $csDetails['br_Phone']; ?><br> </td>
                        </tr>

                        <tr>

                        </tr>

                        </thead>
                        <tbody>








                        </tbody>
                    </table>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <td >Sl</td>
                                <td >Item</td>
                                <td >Price</td>
                                <td >Rate</td>
                                <td >Discount</td>
                                <td >Tax %</td>
                                <td >Qty</td>
                                <td >PD</td>
                                <td >Amount</td>
                                <td >Total</td>

                            </tr>

                            <?php
                            for ($i = 0; $i < $cnt_test; $i++) {
                                //$taxPercent = $podDetails[$i]['fpod_itemoffrrate'] / 
                                $fpod_purchasingUnit = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$podDetails[$i]['fpod_purchasingUnit']}")
                                ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo $podDetails[$i]['fpod_itemname']; ?></td>
                                    <td><?php echo $podDetails[$i]['fpod_itemmrp']; ?></td>
                                    <td><?php echo number_format($podDetails[$i]['fpod_itemoffrrate'], 2); ?></td>
                                    <td><?php echo $podDetails[$i]['itemDisc']; ?> </td>
                                    <td><?php echo number_format($podDetails[$i]['taxRate'], 2); ?></td>
                                    <td><?php echo $podDetails[$i]['fpod_itemqty'] . ' ' . $fpod_purchasingUnit; ?></td>
                                    <td><?php echo $podDetails[$i]['fpod_itemoffrqty']; ?></td>
                                    <td><?php echo $podDetails[$i]['fpod_amount']; ?></td>
                                    <td><?php echo $podDetails[$i]['fpod_netamount']; ?></td>

                                </tr>   
                                <?php
                            }
                            ?>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>


                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <td >Term of Purchase</td>
                                <td >&nbsp;</td>
                                <td >&nbsp;</td>
                            </tr>

                            <tr>
                                <td  rowspan="3">1. Payment Terms: <?php echo $poDetails['fpo_paymentTerms']; ?><br>

                                    2. Validity Period of this PO is upto <?php echo $poDetails['fpo_validDate']; ?></td>
                                <td >Discount%</td>

                                <td ><?php echo $poDetails['fpo_gdiscpercent']; ?></td>
                            </tr> 
                            <tr>

                                <td >Shipping & Handling </td>

                                <td ><?php echo $poDetails['fpo_shippingcharge']; ?></td>
                            </tr> 
                            <tr>

                                <td >Shipping & Handling GST </td>

                                <td ><?php $handlinchgGst = $poDetails['fpo_shippingcharge'] * $handlinchgGstVal / 100;
                            echo $handlinchgGst;
                            ?></td>
                            </tr>
                            <tr>

                                <td ><strong>Total Payable</strong></td>
                                <td >&nbsp;</td>
                                <td >&#8377; <?php echo $poDetails['fpo_poFinalValue']; ?></td>
                            </tr> 


                        </thead>
                        <tbody>
                        </tbody>
                    </table>


                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <td >Purchased BY: <?php echo $name; ?><br>

                                    Time of Order creation:<?php echo $poDetails['fpo_createdon']; ?><br>
                                    Price inclusive of GST</td>
                                <td style="text-align: center" >Signature</td>

                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <td style="background:#6da5de; color: #fff;text-align: center; text-transform: uppercase;" ><h4>Comments or Special Instructions</h4></td>
                            <tr>
                                <td style="padding-left: 50px">
                                    The Supplier shall treat the order and all related deliveres as confidential.<br>
                                    <br>
                                    The Supplier shall arrange appropriate packing of premium metallic UV with brand name company logo to be embossed, at his own costs and shall be liable if the goods are damaged on transport due to faulty packing.<br>
                                    <br>
                                    GST tax invoice and delivery challan/ way bill/ e-way bill documents are to be submitted immediately after dispatch of the goods to our Centeral Stores.
                                </td>


                            </tr>

                        </thead>

                    </table>
                    <p style="text-align: center"><?php echo $pojectName; ?>,<?php echo $branchDetails['br_Name']; ?>,<?php echo $branchDetails['br_Address']; ?>,<?php echo $branchDetails['br_City']; ?> - <?php echo $branchDetails['br_pincode']; ?></p>
                </div>
            </div>
        </div>
    </body>
</html>