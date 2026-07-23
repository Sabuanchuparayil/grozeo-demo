<?php
global $db;

$order_auto_id = $_REQUEST['order_auto_id'];
$order_generated_id = $_REQUEST['order_generated_id'];
$UserLog1 = "SELECT * FROM sales_activity_log";
$details = "SELECT bco.order_id ,order_order_id, cust_customer_name,order_total_amount,order_delivery_charge, order_total_gst,order_action,
DATE_FORMAT(bcoh.created_at,'%d-%m-%Y %H:%i:%s') AS order_confirm_date,
(SELECT admin_description FROM retaline_customer_order_status WHERE status_id = order_status) AS new_status,
(SELECT admin_description FROM retaline_customer_order_status WHERE status_id =
(SELECT order_status FROM retaline_customer_order_history pl  WHERE pl.id < bcoh.id AND pl.order_id=bco.order_id ORDER BY pl.id DESC LIMIT 1)) AS old_status
 FROM  retaline_customer_order_history bcoh
 INNER JOIN retaline_customer_order bco ON bco.order_id = bcoh.order_id
INNER JOIN retaline_customer bc ON bc.cust_id=bco.order_customer_id 
WHERE bco.order_id = {$order_auto_id} ORDER BY bcoh.id DESC";
// $details = $db->getFromDB("SELECT order_id ,order_order_id, cust_customer_name,order_total_amount,order_delivery_charge, 
//          admin_description as order_status,order_total_gst,DATE_FORMAT(order_confirm_date,'%d-%m-%Y') as order_confirm_date "
//          . " FROM retaline_customer_order bco "
//          . " inner join retaline_customer_order_status bcos ON bcos.status_id = bco.status_id"
//          . " inner join retaline_customer bc ON bc.cust_id=bco.order_customer_id"
//          . " WHERE order_id =' " . $order_auto_id . "'", true);
// $details =" select order_id , "
//         . "  message ,action_at,"
//         . " CASE "
//         . " WHEN  current_status IN(1,2,3,8)  Then if(order_user_type<>'BA',(select customer_name from customers where customer_id =order_log.action_by ),(select brand_ambassador_name from brand_ambassador where brand_ambassador_id =order_log.action_by))"
//         . " ELSE (SELECT CONCAT( finascop_usr_profile.FirstName, ' ', finascop_usr_profile.LastName )  from finascop_usr_profile  where finascop_usr_profile.UserId= order_log.action_by )"
//         . " END as user,"
//         . " if(old_status>0,(select order_status_config_name from order_status_config where order_status_config_id = old_status),'On Process') as old_status,"
//         . " if(current_status>0,(select order_status_config_name from order_status_config where order_status_config_id = current_status),'On Process') as current_status"
//         . " from order_log "
//         . " inner join order_table on order_table.order_auto_id = order_log.order_id"
//         . " where order_id 	 ='{$order_auto_id}' order by action_at desc";

$UserLogDetails = $db->getMultipleData($details, True);
//print_r($UserLogDetails);
?>

<html>
    <head>
        <title>Order Log</title>
    </head>
    <style type="text/css">
        .history{ font-family:Arial; font-size:15px;margin-bottom: 15px;}
        .cmtdept {
            color: #4b4b4b;
            float: left;
            font-size: 11px;
            padding: 5px;
        }
        .cmtuser {
            color: #0D0D0D;
            float: left;
            font-size: 11px;
            font-family: arial;
            padding: 5px 5px 5px 0;
            text-align: left;
        }
        .cmtdate {
            float: left;
            font-weight: bold;
            font-size: 11px;
            padding: 5px 5px 5px 0;
        }
        .cmtlabel {
            color: #0D0D0D;
            float: left;
            font-size: 11px;
            font-family: arial;
            padding: 5px 5px 5px 0;
            text-align: left;
        }
        .cmtlabelApprovers {
            color: #858585;
            float: left;
            font-size: 11px;
            padding: 0 1px 5px 5px;
        }
        .cmtlabelApprovers b {
            color: #333;
        }
        .cmtlevel {
            float: left;
            font-size: 11px;
            font-style: italic;
            font-weight: bold;
            padding: 5px 5px 5px 0;
        }
        .cmtcomments {
            color: #858585;
            float: left;
            font-size: 11px;
            padding: 5px 5px 5px 0;
        }
        .cmtcmts {
            float: left;
            font-size: 11px;
            font-style: italic;
            text-align: justify;
            font-weight: bold;
            width: 129px;
            padding: 5px 5px 5px 0;
        }
        .cmtsummary {
            color: #ff0000;
            float: left;
            font-size: 11px;
            font-weight: bold;
            padding: 5px 5px 5px 0;
        }
        .cmthr {
            background: #BFBFBF none repeat scroll 0 0;
            border: 0 none;
            clear: both;
            color: #BFBFBF;
            height: 1px;
        }
        *{margin: 0;
          padding: 0;
        }

        ul li{
            list-style: none;
            padding-bottom: 10px;
            float: left;
            width: 100%;
            font-family: arial;
        }
        ul li p{
            display: none;
            padding: 5px 6px;
            margin: 5px 0;
        }
        p {
            font-size: 11px;
            border:1px solid #99BBE8;
            background-color: #DFE8F6;
        }	
        h4 {
            width:100%;
        }

        h4 a{
            font-size: 12px;
            font-weight:bold;
            font-family: Arial;
            text-decoration: none;
            float: left;
            color: #416AA3;
            outline: none;
            background: url("./resources/images/toggle_button.png") no-repeat;
            padding:0 0 0 20px;

            overflow: hidden;
        }
        ul li h4 span{
            text-align: right;
            font-weight: bold;
            font-size: 11px;

        }
        .clicked{
            background: url("./resources/images/toggle_button.png") no-repeat scroll 0 -16px transparent !important;

            overflow: hidden;
        }

    </style>
    <body>
        <div class="cdetails-outer no-border" style="width:95%; margin:0 auto;">
            <ul class="anexure">
<?php
if (!empty($UserLogDetails)) {

    foreach ($UserLogDetails as $key => $value) {
        //$date = date('d-m-Y H:i:s ', strtotime($value['action_at']));
        ?>
                        <li>
                            <table width = "100%" border = "0" cellspacing = "0" cellpadding = "0" class = "fullbordered">
                                <tr>
                                    <td align="left" width="50%">
                                        <div class="cmtuser">Order |</div>
                                             <div class="cmtlevel"><?php echo $value['cust_customer_name']; ?></div>
                                        <div class="cmtdate"> | <?php echo $value['order_confirm_date']; ?>                                                             </div></td>
                                    <td align="left" ><div class="cmtlabel">Current Status : </div>
                                        <div class="cmtlevel"><?php echo $value['new_status']; ?></div></td>
                                </tr><tr>
                                    <td align="left" width="50%">
                                        <!--<div class="cmtlabel">Action</div>
                                        <div class="cmtsummary"><?php //echo 'NA'; ?></div>-->
                                    </td>
                                    <td>
                                        <div class="cmtlabel">Previous Status : </div>
                                        <div class="cmtlevel"><?php if($value['old_status'] != '') {
                                            echo $value['old_status'];
                                        }else{
                                            echo  'NA'; 
                                        }?></div>
                                    </td>
                                </tr>

                            </table>
                            <hr class="cmthr"></li>
        <?php }
    ?>
                </ul>
                    <?php } else { ?>
                <li>Integration is Going on..</li>
            <?php }
            ?>
        </div>
    </body>
</html>