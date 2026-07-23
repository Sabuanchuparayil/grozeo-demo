<?php
$order_auto_id = $_REQUEST['order_id'];

$notMentioned = "-  N A  -";

global $db;
if ($order_auto_id > 0) {

    $data = $db->getFromDB(" SELECT order_id ,order_order_id, cust_customer_name,cust_mobile,order_total_amount,order_delivery_charge, 
            admin_description as order_status,order_total_gst,DATE_FORMAT(order_confirm_date,'%d-%m-%Y') as order_confirm_date "
            . " FROM retaline_customer_order bco "
            . " inner join retaline_customer_order_status bcos ON bcos.status_id = bco.status_id"
            . " inner join retaline_customer bc ON bc.cust_id=bco.order_customer_id"
            . " WHERE order_id =' " . $order_auto_id . "'", true);
    $pdts = $db->getMultipleData("SELECT item_product_id,(SELECT stit_SKU 
                    FROM `finascop_stock_itemmaster` WHERE stit_ID=item_product_id) AS product_name,item_order_qty,
                    item_price,item_cgst,
                    item_sales_price
                    FROM retaline_customer_order_items
                    WHERE customer_order_id ={$order_auto_id}", true);
    $invoice = $db->getFromDB("SELECT invoice_number FROM invoice_table WHERE invoice_order_id = {$order_auto_id}",true);
    $invoice = $invoice['invoice_number'];

}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title></title>

    </head>
    <style type="text/css">
        body{ padding:10px; margin:10px; font-family:Arial, Helvetica, sans-serif;font-size:12px;}
        ul{ list-style:none; margin:0; padding:0;}
        .wrap{ position:relative;}
        .order{width:100%; background:#E9EEF2
        }
        /*.order li{ padding:5px 15px; clear:both; overflow:hidden;}*/
        .order li {
            padding: 5px 15px;
            clear: both;
            overflow: hidden;
            font-size: 16px;
            font-weight: bold;
        }
        .title{font-size:25px; text-align:left; padding:0 0 25px;}
        .address{ font-size:12px;}
        .subti{ text-align:center; color:#555; padding:25px 0; font-size:18px;  background: rgb(242,246,248); /* Old browsers */
                background: -moz-linear-gradient(top,  rgba(242,246,248,1) 0%, rgba(216,225,231,1) 50%, rgba(181,198,208,1) 51%, rgba(224,239,249,1) 100%); /* FF3.6+ */
                background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(242,246,248,1)), color-stop(50%,rgba(216,225,231,1)), color-stop(51%,rgba(181,198,208,1)), color-stop(100%,rgba(224,239,249,1))); /* Chrome,Safari4+ */
                background: -webkit-linear-gradient(top,  rgba(242,246,248,1) 0%,rgba(216,225,231,1) 50%,rgba(181,198,208,1) 51%,rgba(224,239,249,1) 100%); /* Chrome10+,Safari5.1+ */
                background: -o-linear-gradient(top,  rgba(242,246,248,1) 0%,rgba(216,225,231,1) 50%,rgba(181,198,208,1) 51%,rgba(224,239,249,1) 100%); /* Opera 11.10+ */
                background: -ms-linear-gradient(top,  rgba(242,246,248,1) 0%,rgba(216,225,231,1) 50%,rgba(181,198,208,1) 51%,rgba(224,239,249,1) 100%); /* IE10+ */
                background: linear-gradient(to bottom,  rgba(242,246,248,1) 0%,rgba(216,225,231,1) 50%,rgba(181,198,208,1) 51%,rgba(224,239,249,1) 100%); /* W3C */
                filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f2f6f8', endColorstr='#e0eff9',GradientType=0 ); /* IE6-9 */


                margin: 5px 0;}
        th{background: rgb(246,248,249); /* Old browsers */
           background: -moz-linear-gradient(top,  rgba(246,248,249,1) 0%, rgba(229,235,238,1) 50%, rgba(215,222,227,1) 51%, rgba(245,247,249,1) 100%); /* FF3.6+ */
           background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(246,248,249,1)), color-stop(50%,rgba(229,235,238,1)), color-stop(51%,rgba(215,222,227,1)), color-stop(100%,rgba(245,247,249,1))); /* Chrome,Safari4+ */
           background: -webkit-linear-gradient(top,  rgba(246,248,249,1) 0%,rgba(229,235,238,1) 50%,rgba(215,222,227,1) 51%,rgba(245,247,249,1) 100%); /* Chrome10+,Safari5.1+ */
           background: -o-linear-gradient(top,  rgba(246,248,249,1) 0%,rgba(229,235,238,1) 50%,rgba(215,222,227,1) 51%,rgba(245,247,249,1) 100%); /* Opera 11.10+ */
           background: -ms-linear-gradient(top,  rgba(246,248,249,1) 0%,rgba(229,235,238,1) 50%,rgba(215,222,227,1) 51%,rgba(245,247,249,1) 100%); /* IE10+ */
           background: linear-gradient(to bottom,  rgba(246,248,249,1) 0%,rgba(229,235,238,1) 50%,rgba(215,222,227,1) 51%,rgba(245,247,249,1) 100%); /* W3C */
           filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f6f8f9', endColorstr='#f5f7f9',GradientType=0 ); /* IE6-9 */
        }
        .subti em{ font-size:14px;}.order li.sign{ text-align:right; padding:15px 0}
        .place{width:35%; float:left; text-align:left;}
        .copy{ text-align:center}
        .order li label{width:25%; float:left;}
        .order li span.inputdata{width:70%; float:left; padding:0 0 0 15px;}
        .order li.text-right span.inputdata{ float:right; text-align:right;}
        .data{padding:25px 0;}
        .seal{width:25%; float:left;}
        .order li.sign-office{width:70%; float:right; clear:none; text-align:right;}
        .order li.foroffice{padding:25px 0 15px 0;}
        th,td{ text-align:left; padding:3px; border-bottom:1px solid #D7DEE3;border-right:1px solid #D7DEE3; vertical-align:top;}
        table{border:1px solid #D7DEE3; background:#fff}
        .no-bor{ border:none;}
        th{ color:#444;}
        .rupeeicons{ font-size:13px;}
        .photo-group{ position:absolute; top:60px; right:25px;}
    </style>

    <body><div class="wrap">

            <ul class="order">
                <li class="subti">
                    <strong>GoGomeds Shopping</strong>
                </li>
                <li>
                    <label>Invoice Number
                    </label><span class="inputdata">  <?php
                        if (!empty($invoice)) {
                            echo $invoice;
                        } else {
                            echo $notMentioned;
                        }
                        ?></span></li>
                <li>
                <li>
                    <label>Order ID
                    </label><span class="inputdata">  <?php
                        if (!empty($data['order_order_id'])) {
                            echo $data['order_order_id'];
                        } else {
                            echo $notMentioned;
                        }
                        ?></span></li>
                <li>
                    <label>Order By
                    </label><span class="inputdata">  <?php
                        if (!empty($data['cust_customer_name'])) {
                            echo $data['cust_customer_name'];?><br>
                           <?php echo  $username['address'];
                           
                        } else {
                            echo $notMentioned;
                        }
                        ?></span></li>
                
                 <li>
                    <label>Mobile No
                    </label><span class="inputdata">  <?php
                        if (!empty($data['cust_mobile'])) {
                            echo $data['cust_mobile'];
                        } else {
                            echo $notMentioned;
                        }
                        ?></span></li>

              



            </ul></div></body>
</html>