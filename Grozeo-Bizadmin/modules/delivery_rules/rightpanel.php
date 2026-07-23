<?php
//User Id for the Particular variable
$rdr_id = $_REQUEST['rdr_id'];

global $db;
if ($rdr_id > 0) {

    $data = $db->getFromDB("SELECT rdr_id,rdr_ruleName,rdr_deliveryMode,rdr_calculationMode,rdr_ruleFor,"
        . "CASE WHEN rdr_deliveryMode = 1 THEN 'Courier Delivery'
        WHEN rdr_deliveryMode = 2 THEN 'Hyperlocal Delivery'
        WHEN rdr_deliveryMode = 3 THEN 'Scheduled Local Delivery' 
        WHEN rdr_deliveryMode = 4 THEN 'Local Last Mile Delivery'
        WHEN rdr_deliveryMode = 5 THEN 'Parcel Delivery'
        WHEN rdr_deliveryMode = 3 THEN 'Cargo Delivery'
        WHEN rdr_deliveryMode = 3 THEN 'Manual Delivery'
    END AS rdr_deliveryModeName,CASE WHEN rdr_calculationMode = 1 THEN 'Distance'
        WHEN rdr_calculationMode = 2 THEN 'Fixed' WHEN rdr_calculationMode = 3 THEN 'Grozeo' WHEN rdr_calculationMode = 4 THEN 'Weight' WHEN rdr_calculationMode = 5 THEN 'Zone' 
    END AS rdr_calculationModeName, CASE WHEN rdr_ruleFor = 1 THEN 'Common Rule'
        WHEN rdr_ruleFor = 2 THEN 'Store Group' WHEN rdr_ruleFor = 3 THEN 'Store' WHEN rdr_ruleFor = 4 THEN 'Area'
    END AS rdr_ruleForName,rdr_fixedRateperkm as fixedRate,
    rdr_fixedRateMin,rdr_fixedRateMax,rdr_fromkm1,rdr_tokm1,rdr_amt1,rdr_amt1,rdr_fromkm2,rdr_tokm2,rdr_amt2,rdr_fromkm3,rdr_tokm3,rdr_amt3,rdr_isfreeDelivery,
    rdr_isfreeDeliveryAmt,IF(rdr_isfreeDeliveryAmt > 0,rdr_isfreeDeliveryAmt,0) AS freeDelivery, rdr_ruleFor,rdr_ruleForId,is_default FROM retaline_delivery_rules WHERE rdr_id =' " . $rdr_id . "'", true);
} ?>

<html>
<style>
    .cesstable {
        border-collapse: collapse;
    }

    .cesstable td,
    .cesstable th {
        border-bottom: 1px solid #EDEDED;
        font-family: Source Sans Pro, Verdana, Geneva, sans-serif;
        font-size: 12px;
        padding: 3px;
    }

    .cesstable th {
        padding-right: 21px;
        padding-left: 14px;
        width: 35%;
        vertical-align: top;
        font-weight: inherit;
        text-align: left;
    }

    .cesstable td {
        font-weight: bold;
    }

    .cesstable td span {
        float: left;
        width: 95%;
        text-align: justify;
        font-weight: normal;
    }

    .cesstable th[colspan="2"] {
        font-size: 13px;
        background: #f0f0f0 none repeat scroll 0 0;
        border-color: #d7d7d7;
    }
</style>
<?php if (!empty($data)) { ?>
    <!-- <h6>Delivery Rule</h6>
    <table border="0" width="99%" class="cesstable">
        <tbody>
            <tr>
                <th width="40%" text-align="left">
                    Name
                </th>
                <td>
                    <?php echo $data['rdr_ruleName']; ?>
                </td>
            </tr>
            <tr>
                <th width="40%" text-align="left">
                    Delivery Mode
                </th>
                <td>
                    <?php echo $data['rdr_deliveryModeName']; ?>
                </td>
            </tr>
            <tr>
                <th width="40%" text-align="left">
                    Calculation Mode
                </th>
                <td>
                    <?php echo $data['rdr_calculationModeName']; ?>
                </td>
            </tr>
            <?php if ($data['rdr_calculationMode'] == 2) { ?>
                <tr>
                    <th width="40%" text-align="left">
                        Rate /Km
                    </th>
                    <td>
                        <?php echo $data['fixedRate']; ?>
                    </td>
                </tr>
                <tr>
                    <th width="40%" text-align="left">
                        Min Rate
                    </th>
                    <td>
                        <?php echo $data['rdr_fixedRateMin']; ?>
                    </td>
                </tr>
                <tr>
                    <th width="40%" text-align="left">
                        Max Rate
                    </th>
                    <td>
                        <?php echo $data['rdr_fixedRateMax']; ?>
                    </td>
                </tr>
            <?php } else { ?>
                <tr>
                    <th width="40%" text-align="left">
                        From km
                    </th>
                    <td>
                        <?php echo $data['rdr_fromkm1']; ?> to <?php echo $data['rdr_tokm1']; ?> amount is <?php echo $data['rdr_amt1']; ?>
                    </td>
                </tr>
                <?php if ($data['rdr_amt2'] > 0) { ?>
                    <tr>
                        <th width="40%" text-align="left">
                            From km
                        </th>
                        <td>
                            <?php echo $data['rdr_fromkm2']; ?> to <?php echo $data['rdr_tokm2']; ?> amount is <?php echo $data['rdr_amt2']; ?>
                        </td>
                    </tr>
                <?php } ?>
                <?php if ($data['rdr_amt3'] > 0) { ?>
                    <tr>
                        <th width="40%" text-align="left">
                            From km
                        </th>
                        <td>
                            <?php echo $data['rdr_fromkm3']; ?> to <?php echo $data['rdr_tokm3']; ?> amount is <?php echo $data['rdr_amt3']; ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
            <tr>
                <th width="40%" text-align="left">
                    Rule For
                </th>
                <td>
                    <?php echo $data['rdr_ruleForName']; ?>
                </td>
            </tr>
            <?php
            if ($data['rdr_ruleFor'] == 2) {
                $storeGroup = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = {$data['rdr_ruleForId']}");
            ?>
                <tr>
                    <th width="40%" text-align="left">
                        Store Group
                    </th>
                    <td>
                        <?php echo $storeGroup; ?>
                    </td>
                </tr>
            <?php } ?>
            <?php
            if ($data['rdr_ruleFor'] == 3) {
                $store = $db->getItemFromDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$data['rdr_ruleForId']}");
            ?>
                <tr>
                    <th width="40%" text-align="left">
                        Store
                    </th>
                    <td>
                        <?php echo $store; ?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th width="40%" text-align="left">
                    Free Above Rs.
                </th>
                <td>
                    <?php echo $data['freeDelivery']; ?>
                </td>
            </tr>
        </tbody>
    </table> -->

    <h6>Delivery Rule</h6>
    <table border="0" width="99%" class="cesstable">
        <tbody>
            <tr>
                <th width="40%" text-align="left">
                    Name
                </th>
                <td>
                    <?php echo $data['rdr_ruleName']; ?>
                </td>
            </tr>
            <tr>
                <th width="40%" text-align="left">
                    Delivery Mode
                </th>
                <td>
                    <?php echo $data['rdr_deliveryModeName']; ?>
                </td>
            </tr>
            <tr>
                <th width="40%" text-align="left">
                    Calculation Mode
                </th>
                <td>
                    <?php echo $data['rdr_calculationModeName']; ?>
                </td>
            </tr>
            <?php if ($data['rdr_calculationMode'] == 2) { ?>
                <tr>
                    <th width="40%" text-align="left">
                        Fixed Charge
                    </th>
                    <td>
                    Rate per <?php echo DISTANCE.' '.$data['fixedRate']; ?> Min Amount <?php echo CURRENCY.' '.$data['rdr_fixedRateMin']; ?> Max Amount <?php echo CURRENCY.' '.$data['rdr_fixedRateMax']; ?> 
                    <?php if($data['rdr_isfreeDeliveryAmt'] > 0) { ?>Free Above <?php echo CURRENCY.' '.$data['rdr_isfreeDeliveryAmt']; } ?>
                    </td>
                </tr>

            <?php } else { ?>
                <tr>
                    <th width="40%" text-align="left">
                        Dynamic Charge
                    </th>
                    <td>
                        Min Charge <?php echo $data['rdr_fixedRateMin']; ?> Max Charge <?php echo $data['rdr_amt2']; ?>
                    </td>
                </tr>

            <?php } ?>
            <tr>
                <th width="40%" text-align="left">
                    Rule For
                </th>
                <td>
                    <?php echo $data['rdr_ruleForName']; ?>
                </td>
            </tr>
            <?php
            if ($data['rdr_ruleFor'] == 2) {
                $storeGroup = $db->getItemFromDB("SELECT store_group_name FROM finascop_branch_group WHERE store_group_id = {$data['rdr_ruleForId']}");
            ?>
                <tr>
                    <th width="40%" text-align="left">
                        Store Group
                    </th>
                    <td>
                        <?php echo $storeGroup; ?>
                    </td>
                </tr>
            <?php } ?>
            <?php
            if ($data['rdr_ruleFor'] == 3) {
                $store = $db->getItemFromDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$data['rdr_ruleForId']}");
            ?>
                <tr>
                    <th width="40%" text-align="left">
                        Store
                    </th>
                    <td>
                        <?php echo $store; ?>
                    </td>
                </tr>
            <?php }
            if ($data['rdr_ruleFor'] == 4) {
                $area = $db->getItemFromDB("SELECT areaName FROM area_entries WHERE id = {$data['rdr_ruleForId']}");
            ?>
                <tr>
                    <th width="40%" text-align="left">
                        Area
                    </th>
                    <td>
                        <?php echo $area; ?>
                    </td>
                </tr>
            <?php }
            if ($data['rdr_isfreeDelivery'] == 1) { ?>
                <tr>
                    <th width="40%" text-align="left">
                        Free Above <?php echo CURRENCY;?>.
                    </th>
                    <td>
                        <?php echo $data['freeDelivery']; ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } else { ?>
    sorry there is no available data to display
<?php } ?>

</html>