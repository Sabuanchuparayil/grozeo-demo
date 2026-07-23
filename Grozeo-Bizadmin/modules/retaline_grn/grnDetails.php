<?php
$PoId = $_REQUEST['PoId'];
$poDetails = $db->getFromDB("SELECT branch_id,retgrn_vendor,retgrn_name,DATE_FORMAT(retgrn_date,'%d-%m-%Y') as retgrn_date,DATE_FORMAT(retgrn_createdon,'%d-%m-%Y %H:%i:%s') as retgrn_createdon,DATE_FORMAT(retgrn_createdon,'%H:%i %p') as retgrn_time,retgrn_createdby,retgrn_billingTo FROM retaline_grn WHERE retgrn_uniqueid = '{$PoId}'", true);
$vendorDetails = $db->getFromDB("SELECT stpa_Fname,stpa_Phone,stpa_Address,stpa_City,stpa_PINCODE,stpa_MobileNo,stpa_Email FROM finascop_stock_party WHERE stpa_id = {$poDetails['retgrn_vendor']}", true);
$test_sql = "SELECT retgrnd_itemid,(SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID=retgrnd_itemid) as taxRate,retgrnd_itemname,retgrnd_itemqty,retgrnd_itemoffrrate,retgrnd_amount,"
        . "retgrnd_purchasingUnit FROM retaline_grn_details WHERE retgrnd_uniqueid = '{$PoId}'";
$podDetails = $db->getMultipleData($test_sql, true);
$cnt_test = count($podDetails);
$name = $db->getItemFromDB("SELECT CONCAT(FirstName,' ',LastName) FROM finascop_usr_profile WHERE UserId = {$poDetails['retgrn_createdby']}");
$branchSql = "SELECT br_Name,br_City,br_District,br_State,br_Address,br_pincode,br_Phone FROM finascop_branch WHERE br_ID = {$poDetails['branch_id']}";
$branchDetails = $db->getFromDB($branchSql, true);
$ctrlStreSql = "SELECT br_Name,br_City,br_District,br_State,br_Address,br_pincode,br_Phone FROM finascop_branch WHERE br_ID = {$poDetails['retgrn_billingTo']}";
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

        th, td, p{font-family: arial, sans-serif; font-size: 16px; line-height: 22px;}
        p{font-family: arial, sans-serif; font-size: 16px; margin: 0; line-height: 22px;}
    </style>
    <body>

        <div class="container-fluid">
            <div class='panel-heading'>
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        
                        <td width="250" align="left"><img src="resources/mypharmacy/admin-logo.png"></td>
                    </tr>
                    <tr>
                        <td class="space-receipttitle" height="20" style="line-height:0; font-size: 1px;"></td>
                    </tr>
                    <tr>
                        <th colspan="3" align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 20px; line-height: 22px;">
                            Good Receipt Note
                        </th>
                    </tr>
                    <tr>
                        <td class="b-space-receipttitle" height="20" style="line-height:0; font-size: 1px;"></td>
                    </tr>
                </table>

                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="50%" valign="top" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">
                            <p style="font-family: arial, sans-serif; font-size: 16px; margin: 0px; line-height: 22px;">To<br><br><strong><?php echo $vendorDetails['stpa_Fname']; ?>,</strong><br><?php echo $vendorDetails['stpa_Address']; ?> - <?php echo $vendorDetails['stpa_PINCODE']; ?></p>
                        </td>
                        <td width="50%" valign="bottom" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">
                            <p style="margin: 0px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">PR Number : <?php echo $poDetails['retgrn_name']; ?></p>
                            <p style="margin: 0px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">PR Date & Time:<?php echo $poDetails['retgrn_date']; ?> - <?php echo $poDetails['retgrn_time']; ?></p>
                        </td>
                    </tr>
                </table>

                <table class="billtoshipto" width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="t-space-billtoshipto" height="20" style="line-height:0; font-size: 1px;"></td>
                    </tr>
                    <tr>
                        <td valign="top">
                            <table width="100%" border="1" cellspacing="0" cellpadding="0">
                                <tr>
                                    <th style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Bill To</th>
                                    <th style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Ship To</th>
                                </tr>
                                <tr>
                                    <td valign="top" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">
                                        <p style="font-family: arial, sans-serif; font-size: 16px; margin:0px; line-height: 22px;"><strong><?php echo $branchDetails['br_Name'] ?></strong><br><?php echo $branchDetails['br_Address']; ?> <?php echo $branchDetails['br_pincode']; ?></p>
                                    </td>
                                    <td valign="top" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">
                                        <p style="font-family: arial, sans-serif; font-size: 16px; margin:0px; line-height: 22px;"><strong><?php echo $csDetails['br_Name'] ?></strong><br><?php echo $csDetails['br_Address']; ?> <?php echo $csDetails['br_pincode']; ?></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="b-space-billtoshipto" height="20" style="line-height:0; font-size: 1px;"></td>
                    </tr>
                </table>

                <table width="100%" border="1" class="items_listing" cellspacing="0" cellpadding="0">
                    <tr>
                        <th width="60%"style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Item</th>
                        <th style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Qty</th>
                        <th style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Rate</th>
<!--                        <th style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Tax</th>-->
                        <th style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Amount</th>
                    </tr>
                    <?php
                    $total = 0;
                    for ($i = 0; $i < $cnt_test; $i++) {
                        //$taxPercent = $podDetails[$i]['retgrnd_itemoffrrate'] / 
                        $retgrnd_purchasingUnit = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$podDetails[$i]['retgrnd_purchasingUnit']}")
                        ?>
                        <tr>
                            <td style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $podDetails[$i]['retgrnd_itemname']; ?></td>
                            <td align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $podDetails[$i]['retgrnd_itemqty'] . ' ' . $retgrnd_purchasingUnit; ?></td>
                            <td align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo number_format($podDetails[$i]['retgrnd_itemoffrrate'], 2); ?></td>
<!--                            <td align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $podDetails[$i]['taxRate']; ?>%</td>-->
                            <td align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo number_format($podDetails[$i]['retgrnd_amount'], 2); ?></td>
                        </tr>
                        <?php
                        $total = $total + $podDetails[$i]['retgrnd_amount'];
                    }
                    ?>

                    <tr>
                        <td align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Other Charges</td>
                        <td align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"> </td>
                        <td align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"> </td>
<!--                        <td align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"> </td>-->
                        <td align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"> </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><strong>TOTAL</strong></td>
                        <td align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><strong> </strong></td>
                        <td align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><strong> </strong></td>
<!--                        <td align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><strong> </strong></td>-->
                        <td align="center" style="padding:8px; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><strong><?php echo number_format($total, 2); ?></strong></td>
                    </tr>
                </table>

                <table width="100%" border="0" class="rupeesinwords" cellspacing="0" cellpadding="0">
                    <tr>
                        <td colspan="2" height="25" style="line-height:0; font-size: 1px;"></td>
                    </tr>
                    <tr>
                        <td width="70" style="font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Rupees</td>
                        <td style="border-bottom:2px dotted black; list-style: 5px;"> <?php echo ucfirst(numberTowords($total)); ?>   only</td>
                    </tr>
                </table>

                <table width="100%" border="0" class="t-space-main_office_sign" cellspacing="0" cellpadding="0">
                    <tr>
                        <td colspan="3" height="60" style="line-height:0; font-size: 1px;"></td>
                    </tr>
                </table>

                <table width="100%" border="0" class="main_office_sign" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><strong>Weighing Assistant</strong></td>
                        <td align="center" style="font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><strong>Purchase Supervisor</strong></strong></td>
                        <td align="right" style="font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><strong>Officer in Charge</strong></td>
                    </tr>
                    <tr>
                        <td class="space" colspan="3" height="20" style="line-height:0; font-size: 1px;"></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="border-top:1px solid black; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Entered in Purchase Register Page No:</td>
                    </tr>
                    <tr>
                        <td class="space" colspan="3" height="10" style="line-height:0; font-size: 1px;"></td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right" style="font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><strong>Store Keeper</strong></td>
                    </tr>
                    <tr>
                        <td class="space" colspan="3" height="25" style="line-height:0; font-size: 1px;"></td>
                    </tr>
                </table>

                <table width="100%" border="0" class="created_by" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Created by:<?php echo $name; ?></td>
                    </tr>
                    <tr>
                        <td style="font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Date & Time :<?php echo $poDetails['retgrn_createdon']; ?></td>
                    </tr>
                </table>


<!--                <table class="table table-bordered">
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
                </table>-->
            </div><!--panel-->
        </div>
    </body>
</html>