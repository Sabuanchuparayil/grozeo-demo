
<?php
$rdc_id = $_REQUEST['rdc_id'];
$rdc_status = $_REQUEST['rdc_status'];
$branchDesi = $_REQUEST['branchDesi'];

$listQry = "SELECT rdc_id,rdcd_id,rdc_ItemId,rdc_ApprovedItemQty as rdc_TransferedItemQty,rdc_ApprovedItemQty,rdc_RequiredItemQty,rdcd_status,rdc_ItemUnits,rdc_leastSKUCount,least_package_type_id,"
        . "CASE WHEN rdcd_status=1 THEN 'Requested' WHEN rdcd_status=2 THEN 'Ordered' WHEN rdcd_status=3 THEN 'Deleted' WHEN rdcd_status=4 THEN 'Packed' "
        . "WHEN rdcd_status=5 THEN 'Partially Received' WHEN rdcd_status=6 THEN 'Received' ELSE 'Not Saved' END AS status_name FROM retaline_distribution_chart_details WHERE 1=1 AND rdc_id ={$rdc_id} ORDER BY rdcd_id ";
$datas = $db->getMulipleData($listQry, true);
$rdc_destination = $db->getFromDB("SELECT DATE_FORMAT(rdc_date,'%d-%m-%Y') as rdc_date,rdc_destination from retaline_distribution_chart where rdc_id = {$rdc_id}", true);
$brName = $db->getItemFromDB("SELECT br_Name FROM finascop_branch where br_ID = {$rdc_destination['rdc_destination']}");
$resCount = count($datas);
?>

<!DOCTYPE html>
<html lang="en">
    <style>

        th, td, p{font-family: arial, sans-serif; font-size: 16px; line-height: 22px;}
        p{font-family: arial, sans-serif; font-size: 16px; margin: 0; line-height: 22px;}


    </style>

    <body>
        <div class="container-fluid">
            <div class='panel'>
                <table width="100%" border="1" cellspacing="0" cellpadding="0">

                    <tr>

                        <td>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" colspan="2" style="padding:8px; border-bottom: 1px solid #4c4c4c; font-family: arial, sans-serif; font-weight: bold; font-size: 24px; line-height: 26px;">PACKING SLIP</td>
                                </tr>
    <!--                            <tr>
                                    <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; font-weight: bold; line-height: 22px;">No : <?php echo $orderDetails['booking_no']; ?></td>
                                    <td style="padding:8px; border-bottom: 1px solid #4c4c4c;  font-family: arial, sans-serif; font-size: 16px; font-weight: bold; line-height: 22px;"></td>
                                </tr>-->
                                <tr>
                                    <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; font-weight: bold; line-height: 22px;">To : <?php echo $brName; ?></td>
                                    <td style="width:211px;padding:8px; border-bottom: 1px solid #4c4c4c;  font-family: arial, sans-serif; font-size: 16px; font-weight: bold; line-height: 22px;">Date : <?php echo $rdc_destination['rdc_date']; ?></td>
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
                                    <td width="12.5%" style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">Qty</td>
                                    <td width="12.5%" style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;">UoM</td>
                                </tr>
                                <?php
                                $j = 0;
                                for ($i = 0; $i < $resCount; $i++) {
                                    if ($datas[$i]['rdc_ApprovedItemQty'] > 0) {
                                        $j = $i + 1;
                                        $datas[$i]['rdc_ItemName'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster where stit_ID = {$datas[$i]['rdc_ItemId']}");
                                        $datas[$i]['packageType'] = $db->getItemFromDB("(select package_type_name from mypha_productpackage_type WHERE package_type_id = {$datas[$i]['rdc_ItemUnits']})");
                                        $datas[$i]['least_package_type'] = $db->getItemFromDB("(select package_type_name from mypha_productpackage_type WHERE package_type_id = {$datas[$i]['least_package_type_id']})");
                                        ?>
                                        <tr>
                                            <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $j; ?></td>
                                            <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $datas[$i]['rdc_ItemName']; ?></td>
                                            <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $datas[$i]['rdc_ApprovedItemQty']; ?></td>
                                            <td style="padding:8px; border-bottom: 1px solid #4c4c4c; border-right: 1px solid #4c4c4c; font-family: arial, sans-serif; font-size: 16px; line-height: 22px;"><?php echo $datas[$i]['packageType']; ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?> 
                            </table>
                        </td>
                    </tr>


                </table>
            </div><!--panel-->
        </div>

    </body>

</html>
