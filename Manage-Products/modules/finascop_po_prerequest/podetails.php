<?php
$PoId = $_REQUEST['PoId'];
$poDetails = $db->getFromDB("SELECT prereq_billingTo,prereq_vendor,prereq_name,DATE_FORMAT(prereq_createdon,'%d-%m-%Y') as prereq_createdon,prereq_createdby FROM finascop_purchase_order_poprereq WHERE prereq_name = '{$PoId}'", true);
$vendorDetails = $db->getFromDB("SELECT stpa_Fname,stpa_Phone,stpa_Address,stpa_City,stpa_PINCODE,stpa_MobileNo,stpa_Email FROM finascop_stock_party WHERE stpa_id = {$poDetails['prereq_vendor']}", true);
$test_sql = "SELECT fpopredet_itemid,(SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID=fpopredet_itemid) as taxRate,fpopredet_itemname,fpopredet_itemmrp,fpopredet_itemqty,fpopredet_itemoffrqty,fpopredet_totalqty,fpopredet_itemoffrrate,"
        . "fpopredet_itemaddidisc,fpopredet_effectiverate,fpopredet_idiscountcalculs,fpopredet_netamount,fpopredet_amount,fpopredet_purchasingUnit FROM finascop_poprereq_details WHERE fpopredet_prereqname = '{$PoId}'";
$podDetails = $db->getMultipleData($test_sql, true);
$cnt_test = count($podDetails);
$name = $db->getItemFromDB("SELECT CONCAT(FirstName,' ',LastName) FROM finascop_usr_profile WHERE UserId = {$poDetails['prereq_createdby']}");
$br_cpd =  $db->getItemFromDB("SELECT br_cpd FROM finascop_branch WHERE br_ID = {$poDetails['prereq_billingTo']}");
$branchSql = "SELECT br_Name,br_City,br_District,br_State,br_Address FROM finascop_branch WHERE br_ID = {$br_cpd}";
$branchDetails = $db->getFromDB($branchSql, true);
$ctrlStreSql = "SELECT br_Name,br_City,br_District,br_State,br_Address FROM finascop_branch WHERE br_ID = {$poDetails['prereq_billingTo']}";
$csDetails = $db->getFromDB($ctrlStreSql, true);

$pojectName = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'PROJECT_NAME'");
$poCompany = $db->getItemFromDB("SELECT comp_id FROM finascop_branch_company WHERE br_Id = {$poDetails['prereq_billingTo']}");
$companyDetails = $db->getFromDB("SELECT comp_address,comp_GSTIN,comp_Ph FROM finascop_company WHERE comp_id = {$poCompany}");
$vendorDetails = $db->getFromDB("SELECT stpa_Fname,stpa_Address,stpa_MobileNo FROM finascop_stock_party WHERE stpa_id = {$poDetails['prereq_vendor']}", true);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>PO Details of <?php echo $poDetails['prereq_name']; ?></title>
        <link href="https://fonts.googleapis.com/css?family=Lato:100,100i,300,300i,400,400i,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="resources/po/css/style.css" media="all" />
    </head>
    <body>
        <header class="clearfix">
            <div id="logo">
                <img src="resources/po/img/logo.png">
            </div>
            <div id="company">
                <h2 class="name"></h2>
                <div></div>        
                <div></div>
            </div>
        </div>
    </header>
    <main>
        <div id="details" class="clearfix">
            <div id="client">
                <div class="address">PR Number: <?php echo $poDetails['prereq_name']; ?></div>
                <div class="address">PR Date: <?php echo $poDetails['prereq_createdon']; ?></div>
                <div class="address">To<br>
                    <?php echo $vendorDetails['stpa_Fname']; ?>,<?php echo $vendorDetails['stpa_Address']; ?><br>Phone-<?php echo $vendorDetails['stpa_MobileNo']; ?><br>Pin <?php echo $vendorDetails['stpa_PINCODE']; ?></div>
            </div>
            <div id="invoice">
                <h1>PREREQUISITE</h1>
                <div class="date"></div>
                <!--                <div class="date">COI: NA</div>-->
            </div>
        </div>
        <table border="1" cellspacing="0" cellpadding="0" class="ship-detail">       
            <thead>
                <tr>
                    <th>Bill To:</th>
                    <th>Ship To:</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><div><?php echo $branchDetails['br_Name'] ?>,</div>
                        <div>  <?php echo $branchDetails['br_Address']; ?></div>
                        <div> <?php echo $branchDetails['br_City']; ?></div>
                        <div>GST No. NA</div></td>
                    <td><div><?php echo $csDetails['br_Name'] ?>,</div>
                        <div>  <?php echo $csDetails['br_Address']; ?></div>
                        <div> <?php echo $csDetails['br_City']; ?></div>
                        <div>GST No. NA</div></td>
                </tr>
            </tbody>
        </table>
        <table border="0" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th class="no">Sl</th>
                    <th class="no">Item</th>
                    <th class="no">Price</th>
                    <th class="no">Rate</th>
                    <th class="no">Discount</th>
                    <th class="no">Tax%</th>
                    <th class="no">Qty</th>
                    <th class="no">PD</th>
                    <th class="no">Amount</th>
                    <th class="no">Total</th>

                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $i < $cnt_test; $i++) {
                    //$taxPercent = $podDetails[$i]['fpopredet_itemoffrrate'] / 
                    $fpopredet_purchasingUnit = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$podDetails[$i]['fpopredet_purchasingUnit']}")
                    ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo $podDetails[$i]['fpopredet_itemname']; ?></td>
                        <td><?php echo $podDetails[$i]['fpopredet_itemmrp']; ?></td>
                        <td><?php echo $podDetails[$i]['fpopredet_itemoffrrate']; ?></td>
                        <td><?php echo $podDetails[$i]['fpopredet_itemaddidisc']; ?></td>
                        <td><?php echo number_format($podDetails[$i]['taxRate'], 2); ?></td>
                        <td><?php echo $podDetails[$i]['fpopredet_itemqty'] . ' ' . $fpopredet_purchasingUnit; ?></td>
                        <td><?php echo $podDetails[$i]['fpopredet_itemoffrqty']; ?></td>
                        <td><?php echo $podDetails[$i]['fpopredet_amount']; ?></td>
                        <td><?php echo $podDetails[$i]['fpopredet_netamount']; ?></td>

                    </tr>

                    <?php
                }
                ?>

            </tbody>
            <table class="ship-detail">
                <tbody>
                    <tr class="c16">
                        <td class="c19" colspan="1" rowspan="1">
                            <p class="c1"><span class="c45">Term of Purchase</span></p>
                        </td>
                        <td class="c18" colspan="1" rowspan="1">
                            <p class="c8"><span class="c45"></span></p>
                        </td>
                        <td class="c32" colspan="1" rowspan="1">
                            <p class="c8"><span class="c45"></span></p>
                        </td>
   <!--                        <td class="c18" colspan="1" rowspan="1">
                            <p class="c8"><span class="c45">Total</span></p>
                        </td>
                     <td class="c32" colspan="1" rowspan="1">
                            <p class="c8"><span class="c45">Rs.<?php echo $poDetails['fpo_poValue']; ?></span></p>
                        </td>-->
                    </tr>
                    <tr class="c24">
                        <td class="c19" colspan="1" rowspan="4">
                            <ol class="c42 lst-kix_qasqkzy6hbq7-0 start" start="1">
                                <li class="c1 c13">
                                    <span class="c15">Payment Terms: <?php echo $poDetails['fpo_paymentTerms']; ?></span>
                                </li>
                                <li class="c1 c13">
                                    <span class="c15">Validity Period of this PO is upto <?php echo $poDetails['fpo_validDate']; ?></span>
                                </li>

                            </ol></td>
                        <td class="c18" colspan="1" rowspan="1">
                            <p class="c8"><span class="c15">Discount%</span></p></td><td class="c32" colspan="1" rowspan="1"><p class="c8"><span class="c15"><?php echo $poDetails['fpo_gdiscpercent']; ?></span></p></td></tr>
                            <!--<tr class="c24"><td class="c18" colspan="1" rowspan="1"><p class="c8"><span class="c15">GST</span></p></td><td class="c32" colspan="1" rowspan="1"><p class="c8"><span class="c15">6,000.00</span></p></td></tr>-->
                    <tr class="c24"><td class="c18" colspan="1" rowspan="1"><p class="c8"><span class="c15">Shipping &amp; Handling</span></p></td><td class="c32" colspan="1" rowspan="1"><p class="c8"><span class="c15"><?php echo $poDetails['fpo_shippingcharge']; ?></span></p></td></tr><tr class="c24"><td class="c18" colspan="1" rowspan="1"><p class="c8"><span class="c36">Total Payable</span></p></td><td class="c32" colspan="1" rowspan="1"><p class="c31"><span class="c36">₹ <?php echo $poDetails['fpo_poValue']; ?></span></p></td></tr></tbody></table>
        </table>
        <table border="1" cellspacing="0" cellpadding="0" class="ship-detail">       
            <tbody>
                <tr>
                    <td>          
                        <div>Purchased By: <?php echo $name; ?></div>

                        <div>Time of Order Creation: <?php echo $poDetails['fpo_createdon']; ?></div>
                        <div>Price inclusive of GST</div>
                    </td>
                    <td class="sign"><div>Signature</div>
                    </td>
                </tr>
            </tbody>
        </table>

    </main>

</body>
</html>