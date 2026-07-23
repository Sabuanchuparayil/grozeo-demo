<?php

include_once(ROOT . '/finascop_libs/dompdf/autoload.inc.php');

use Dompdf\Dompdf;

function getIPAddress() {
    //whether ip is from the share internet  
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    //whether ip is from the proxy  
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
//whether ip is from the remote address  
    else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function CreateInvoicePDF($bbso_InvNumber, $bbso_id) {
    global $db;
    /* instantiate and use the dompdf class */
    $pojectName = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'PROJECT_NAME'");
    $query = "SELECT bbso_id, bbso_InvNumber,bbso_SONumber,bbso_SODate,bbso_InvByUserName, bbso_InvIPAddress,bbso_InvDate ,b2b_Customer_ID,bbso_SOValue,bbso_HandlingCharges,bbso_gdiscpercent,"
            . "(bbso_SOValue - bbso_HandlingCharges) AS bbso_InvSubTotal,bbso_InvValBtax,bbso_CGSTVal,bbso_SGSTVal,bbso_totInFig,bbso_totInWords,br_ID,"
            . "(SELECT br_Name FROM finascop_branch fb WHERE fb.br_ID = rbs.br_ID) AS bbso_Branch"
            . " FROM retaline_B2B_SalesOrder rbs WHERE bbso_id = {$bbso_id}";
    $B2BInvData = $db->getFromDB($query, true);

    $toDetails = $db->getFromDB("SELECT fsto_id,fstr_id FROM finascop_stock_transfer_order WHERE fsto_ordertype = 2 AND fstr_id = {$bbso_id}", true);

    $branchSql = "SELECT br_Name,br_City,br_District,br_State,br_Address,br_pincode,br_Phone,br_Fax FROM finascop_branch WHERE br_ID = {$B2BInvData['br_ID']}";
    $branchDetails = $db->getFromDB($branchSql, true);
    $poCompany = $db->getItemFromDB("SELECT comp_id FROM finascop_branch_company WHERE br_Id = {$B2BInvData['br_ID']}");
    $companyDetails = $db->getFromDB("SELECT comp_name,comp_address,comp_GSTIN,comp_Ph,comp_dlno1,comp_dlno2,comp_fssaino,comp_gstno FROM finascop_company WHERE comp_id = {$poCompany}", true);

    $bbso_grandTotal = $B2BInvData['bbso_SOValue'];
    $bbso_grandTotalRounded = round($bbso_grandTotal);
    $bbso_grandTotalInWords = getIndianCurrency($bbso_grandTotalRounded);


    $bbso_InvDate = $B2BInvData['bbso_InvDate'];
    $bbso_InvNo = $B2BInvData['bbso_InvNumber'];
    $bbso_InvBranch = $B2BInvData['bbso_Branch'];

    $bbso_InvSubTotal = $B2BInvData['bbso_InvSubTotal'];
    $bbso_HandlingCharges = $B2BInvData['bbso_HandlingCharges'];
    $bbso_InvGrandTotal = $B2BInvData['bbso_SOValue'];
    $db->query("SET @slNo = 0;");
    $listQuery = "SELECT @slNo := @slNo + 1 as slNo,  b2bso_itemname,b2bso_HSN, b2bso_itemqty, b2bso_itemrate,b2bso_gst,b2bso_cgst_percent,b2bso_itemoffrqty,b2bso_itemrateet,b2bso_netamountet,b2bso_itemcess,"
            . "b2bso_cgst_value, b2bso_sgst_percent, b2bso_sgst_value, b2bso_amount_btax,b2bso_discountamt, b2bso_netamount,b2bso_itemid,b2bso_itemmrp,b2bso_itemPkg,b2bso_itemaddidisc,b2bso_idiscountcalculs,"
            . "b2bso_gendiscount FROM retaline_B2B_SalesOrderDetails WHERE bbso_id = {$bbso_id}";
    $B2BInvItemDetArr = $db->getMultipleData($listQuery, true);

    $bbso_InvItemDetails = '';
    $totalGST = 0;
    foreach ($B2BInvItemDetArr as $itemDetails) {
        $totalGST = $totalGST + $itemDetails['b2bso_cgst_value'] + $itemDetails['b2bso_sgst_value'];
        if ($toDetails['fsto_id'] > 0) {
            $batch = $db->getItemFromDB("SELECT GROUP_CONCAT(DISTINCT(stiid_batchno)) FROM finascop_stock_transfer_order_details_barcodes fstodb "
                    . "INNER JOIN finascop_stock_item_inventorydetails fsii ON fsii.stiid_id = fstodb.stiid_id WHERE fsto_id = {$toDetails['fsto_id']} AND stiid_itemmasterid = {$itemDetails['b2bso_itemid']}");
            $expiryDates = $db->getItemFromDB("SELECT GROUP_CONCAT(DISTINCT(stiid_expirydate)) FROM finascop_stock_transfer_order_details_barcodes fstodb "
                    . "INNER JOIN finascop_stock_item_inventorydetails fsii ON fsii.stiid_id = fstodb.stiid_id WHERE fsto_id = {$toDetails['fsto_id']} AND stiid_itemmasterid = {$itemDetails['b2bso_itemid']}");
        }
        $itemManuf = $db->getItemFromDB("SELECT med_manufacturename FROM finascop_stock_itemmaster WHERE stit_ID = {$itemDetails['b2bso_itemid']} ");
        $hsn_code = $db->getItemFromDB("SELECT hsn_code FROM finascop_hsn WHERE hsn_id = {$itemDetails['b2bso_HSN']} ");
        $itemRate = explode('.', $itemDetails['b2bso_itemrate']);
        $netamount = explode('.', $itemDetails['b2bso_netamount']);
        if ($itemDetails['b2bso_idiscountcalculs'] == 'Percentage') {
            $type = '%';
        } else {
            $type = 'Rs';
        }
        $curItemDet = '';
        $curItemDet = '<tr>
         	<td class="pad">' . $itemDetails['slNo'] . '</td>
         	<td class="pad">' . $itemDetails['b2bso_itemname'] . '</td>
         	<td class="pad">' . $itemManuf . '</td>
         	<td class="pad">' . $hsn_code . '</td>
         	<td class="pad">' . $batch . '</td>
         	<td class="pad">' . $expiryDates . '</td>
         	<td class="pad">' . $itemDetails['b2bso_itemqty'] . '</td>
                     <td class="pad">' . $itemDetails['b2bso_itemoffrqty'] . '</td>
         	<td class="pad">' . $itemDetails['b2bso_itemPkg'] . '</td>
         	<td class="pad">' . $itemDetails['b2bso_itemmrp'] . '</td>
         	<td class="pad">' . $itemDetails['b2bso_itemrate'] . '</td>
         	<td class="pad">' . $itemDetails['b2bso_itemaddidisc'] . ' ' . $type . '</td>
         	<td class="pad">' . $itemDetails['b2bso_gendiscount'] . ' Rs</td>
         	<td class="pad" >' . $itemDetails['b2bso_netamountet'] . '</td>
         	<td class="pad" >' . $itemDetails['b2bso_cgst_percent'] . '</td>
         	<td class="pad">' . $itemDetails['b2bso_cgst_value'] . '</td>
         	<td class="pad">' . $itemDetails['b2bso_sgst_percent'] . '</td>
         	<td class="pad">' . $itemDetails['b2bso_sgst_value'] . '</td>
         	<td class="pad">' . $itemDetails['b2bso_itemcess'] . '</td>
         	<td class="pad">' . $itemDetails['b2bso_netamount'] . '</td>
         	</tr>';

        $bbso_InvItemDetails .= $curItemDet;
    }

    $b2bSOCustomer = $B2BInvData['b2b_Customer_ID'];
    $b2bCustDet = $db->getFromDB("SELECT b2b_Customer_Name,b2b_Customer_Address,b2b_Customer_pincode,b2b_Customer_Phone,"
            . "b2b_Customer_Email,b2b_Customer_Mobile,b2b_Customer_gst,b2b_Customer_dlno1,b2b_Customer_dlno2,b2b_Customer_fssaino "
            . "  FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = {$b2bSOCustomer}", true);

    $bbso_CustomerAddress = "{$b2bCustDet['b2b_Customer_Name']},<br>"
            . "{$b2bCustDet['b2b_Customer_Address']} PIN:{$b2bCustDet['b2b_Customer_pincode']} <br>"
            . "Phone:{$b2bCustDet['b2b_Customer_Phone']} <br>Email: {$b2bCustDet['b2b_Customer_Email']} <br> "
            . "Mob: {$b2bCustDet['b2b_Customer_Mobile']} <br>GST: {$b2bCustDet['b2b_Customer_gst']}";


    $dompdf = new Dompdf();
    // $dompdf_options = array( "default_paper_size" => 'a4', "enable_unicode" => DOMPDF_UNICODE_ENABLED, "enable_php" => DOMPDF_ENABLE_PHP, "enable_remote" => true, "enable_css_float" => true, "enable_javascript" => true, "enable_html5_parser" => DOMPDF_ENABLE_HTML5PARSER, "enable_font_subsetting" => DOMPDF_ENABLE_FONTSUBSETTING);
    // $dompdf->set_options($dompdf_options);


    $html = '<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>GOGOMEDS</title>
</head>
 <!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script> 

<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap" rel="stylesheet">

<link rel="stylesheet" href="../../resources/css/invoiceb2b.css">
<body>

<div class="container">
<div class="row">
<div class="table-responsive">
<table class="table table-bordered">
  <thead>
    <tr>
      <th scope="col" class="txt_l valign" valign="top"> <div class="logo"> <a href="#"> <img src="resources/po/img/logo.png" alt="logo"> </a> </div></th>
      <th scope="col">
		<h1>' . $pojectName . '</h1>
<p>' . $branchDetails['br_Name'] . '<br>
                            ' . $branchDetails['br_Address'] . ' <br>
                            ' . $branchDetails['br_City'] . '  ' . $branchDetails['br_pincode'] . '<br>Phone-' . $branchDetails['br_Phone'] . '</p>	
     </th>
      <th colspan="2" scope="col" valign="top" class="valign">
<table>
	<tr>
		<td class="txt_r">DL No 1:  </td>
		<td colspan="3"> ' . $companyDetails['comp_dlno1'] . '</td>
	</tr>
	<tr>
		<td class="txt_r">DL No 2:  </td>
		<td colspan="3"> ' . $companyDetails['comp_dlno2'] . '</td>
	</tr>
		<tr>
		<td class="txt_r">FSSAI No :    </td>
		<td colspan="3">' . $companyDetails['comp_fssaino'] . '</td>
	</tr>
			<tr>
		<td class="txt_r">GST No :    </td>
		<td colspan="3">' . $companyDetails['comp_gstno'] . '</td>
	</tr>
		<tr>
		<td class="txt_r">LR No : </td>
		<td width="50px"> </td>
		<td>LR Date : </td>
		<td  width="50px"> </td>
	</tr>

</table>
     
     </th>
  
    </tr>
  </thead>
  <tbody>
    <tr>
      
      <td colspan="2">Sales Order Number: ' . $B2BInvData['bbso_SONumber'] . '<br>
      	Sales Order Date: ' . $B2BInvData['bbso_SODate'] . '
	 </td>
      <td colspan="2"><h2>TAX INVOICE</h2></td>
      
    </tr>
    <tr>
      <td colspan="4" class="txt_c" >FORM GST INVOICE-1</td>
      
     
    </tr>
    <tr>
      <td>Invoice Date</td>
      <td>' . $bbso_InvDate . '</td>
      <td colspan="2">Transportation Details:</td>
      </tr>
      
    <tr>
      <td>Invoice No</td>
      <td>' . $bbso_InvNo . '</td>
      <td colspan="2"> </td>
      </tr> 
       
           <tr>
    
      <td colspan="4">&nbsp; </td>
      </tr> 
      
          <tr>
      <td colspan="2">DETAILS OF THE RECEIVER (BILLED TO) </td>
      <td colspan="2">DETAILS OF THE CONSIGNEE (SHIPPED TO)</td>
		 </tr> 
      <tr>
      <td colspan="2">' . $b2bCustDet['b2b_Customer_Name'] . ' </td>
      <td colspan="2">' . $b2bCustDet['b2b_Customer_Name'] . ' </td>
	 </tr>  
       <tr>
      <td colspan="2">' . $b2bCustDet['b2b_Customer_Address'] . '  </td>
      <td colspan="2" >' . $b2bCustDet['b2b_Customer_Address'] . '  </td>
	 </tr>   
         <tr>
      <td colspan="2">Mobile:' . $b2bCustDet['b2b_Customer_Mobile'] . '<br>	
		Ph: ' . $b2bCustDet['b2b_Customer_Phone'] . '  </td>
      <td colspan="2">Mobile:' . $b2bCustDet['b2b_Customer_Mobile'] . '<br>	
Ph: ' . $b2bCustDet['b2b_Customer_Phone'] . ' </td>
	 </tr> 
       
         <tr>
         	<td>GST No. : ' . $b2bCustDet['b2b_Customer_gst'] . '</td>
         	<td>FSSAI No. : ' . $b2bCustDet['b2b_Customer_fssaino'] . '</td>
         	<td>GST No. : ' . $b2bCustDet['b2b_Customer_gst'] . '</td>
         	<td>FSSAI No. : ' . $b2bCustDet['b2b_Customer_fssaino'] . '</td>
         </tr>  
         <tr>
         	<td>DL NO 1 :  ' . $b2bCustDet['b2b_Customer_dlno1'] . '</td>
         	<td>DL NO 2 :  ' . $b2bCustDet['b2b_Customer_dlno2'] . '</td>
         	<td>DL NO 2 :  ' . $b2bCustDet['b2b_Customer_dlno3'] . '</td>
         	<td>DL NO 2 :  ' . $b2bCustDet['b2b_Customer_dlno4'] . '</td>
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
        <th class="pad" rowspan="2">Pdt. Disc.</th>
        <th class="pad" rowspan="2">Unit</th>
         <th class="pad" rowspan="2">MRP</th>
        <th class="pad" rowspan="2">Rate</th>
        <th class="pad" rowspan="2">Sch Discount % / Amt</th>
         <th class="pad" rowspan="2">Cash Discount % / Amt</th>
        <th class="pad" rowspan="2">Taxable Value</th>
        <th class="pad" colspan="2">CGST / IGST</th>
        <th class="pad" colspan="2">SGST</th>
        <th class="pad" colspan="2">Cess</th>
        <th class="pad" rowspan="2">Total</th>
    </tr>
    <tr>
        <th class="pad">Rate %.</th>
        <th class="pad">Amt</th>
        <th class="pad">Rate %.</th>
        <th class="pad">Amt</th>
         <th class="pad">Rate 1%</th>
    </tr>
    ' . $bbso_InvItemDetails . '
         	
         		<tr>
         	<td class="pad" colspan="11">&nbsp;</td>
         	<td class="pad" colspan="11">&nbsp;</td>
        
         	</tr>
         	 	<tr>
         	<td class="pad" colspan="11">Freight / Handling Charges</td>
         	<td class="pad" colspan="11" style="text-align: right">' . $B2BInvData['bbso_HandlingCharges'] . '</td>
         	</tr>
         	
         	<tr>
         	<td class="pad" colspan="11">Adl. Discount</td>
         	<td class="pad" colspan="11" style="text-align: right">' . $B2BInvData['bbso_gdiscpercent'] . '</td>$totalGST
         	</tr>
         	<tr>
         	<td class="pad" colspan="11">GST Total </td>
         	<td class="pad" colspan="11" style="text-align: right">' . $totalGST . '</td>
         	</tr>
           	<tr>
         	<td class="pad" colspan="11">Cr/Dr Note  </td>
         	<td class="pad" colspan="11" style="text-align: right">&nbsp;</td>
         	</tr>       	
         	<tr>
         	<td class="pad" colspan="11">TCS</td>
         	<td class="pad" colspan="11" style="text-align: right">&nbsp;</td>
         	</tr> 
				<tr>
         	<td class="pad" colspan="11">Total</td>
         	<td class="pad" colspan="11" style="text-align: right">&nbsp;</td>
         	</tr>      	
    		<tr>
         	<td class="pad" colspan="11">Round Off</td>
         	<td class="pad" colspan="11" style="text-align: right">&nbsp;</td>
         	</tr> 
         	<tr>
         	<td class="pad" colspan="11">Grand Total</td>
         	<td class="pad" colspan="11" style="text-align: right">' . $B2BInvData['bbso_SOValue'] . '</td>
         	</tr> 	
    		 <tr>
         	<td class="pad" colspan="11">Grand Total   in Words</td>
         	<td class="pad" colspan="11" style="text-align: right">' . ucwords(numberTowords($B2BInvData['bbso_SOValue'])) . ' only</td>
         	</tr> 
         
</table>
         </td>
         </tr>
         <tr>
         <td colspan="4" >&nbsp; </td>
         </tr>
         <tr>
         <td colspan="2" >Declaration: Certified that all the particulars shown in the above Tax Invoice are true and correct and that my/ our Registration under the CGST/ SGST/ IGST Acts 2017 is valid as on the date of this invoice </td>
         <td >Seal</td>
         <td >Signature </td>
         </tr>
        
  </tbody>
</table>
</div>
</div>
</div>
</body>
</html>
    ';
    $dompdf->set_option('isFontSubsettingEnabled', true);
    $dompdf->set_option('isHtml5ParserEnabled', true);

//$dompdf->loadHtml(mb_convert_encoding($html, 'windows-1252'), 'windows-1252');
    $dompdf->loadHtml($html, 'UTF-8');
//$dompdf->loadHtml("<h1>test pdf generation</h1>");

    $dompdf->setPaper('legal', 'landscape');

    $dompdf->set_option('defaultMediaType', 'all');
    $dompdf->set_option('isFontSubsettingEnabled', true);
    /* Render the HTML as PDF */
    $dompdf->render();


    //$file_to_save = '/home/web/jatayu-booking/html/uploads/file.pdf';
    ob_clean();
    header("Content-type: application/octet-stream");
    header("Content-disposition: attachment;filename=SO{$bbso_InvNumber}.pdf");

//readfile($file_to_save);
    echo $dompdf->output();

// echo $data;
}
