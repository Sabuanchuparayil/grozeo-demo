<?php
$scheduledMeetings  = $db->getMultipleData("SELECT id,meetingDate,meetingTime,meetingRemarks FROM crm_meetings WHERE crmUserType = 'lead' AND crmUserId = {$crle_id} ORDER BY id DESC", true); ?>
<style>
    .details_view_table {
        border-collapse: collapse;
    }

    .details_view_table td,
    .details_view_table th {
        border-bottom: 1px solid #EDEDED;
        font-family: Source Sans Pro, Verdana, Geneva, sans-serif;
        font-size: 12px;
        padding: 3px;
    }

    .details_view_table th {
        padding-right: 21px;
        padding-left: 14px;
        width: 35%;
        vertical-align: top;
        font-weight: inherit;
        text-align: left;
    }

    .details_view_table td {
        font-weight: bold;
    }

    .details_view_table td span {
        float: left;
        width: 95%;
        text-align: justify;
        font-weight: normal;
    }

    .details_view_table th[colspan="2"] {
        font-size: 13px;
        background: #f0f0f0 none repeat scroll 0 0;
        border-color: #d7d7d7;
    }
</style>
<?php if (!empty($results)) { ?>
    <table cellspacing="2" cellpadding="2" border="0" class="details_view_table">
        <tbody>
            <?php if ($results['crle_orgName'] != '') { ?>
                <tr>

                    <th width="275">Store Name</th>
                    <td width="675"><?php echo $results['crle_orgName']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crle_type'] > 0) {
            ?>
                <tr>
                    <th width="275">Contact Type</th>
                    <td width="675"><?php echo $crle_type; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crle_location'] != '') { ?>
                <tr>
                    <th width="275">Location</th>
                    <td width="675"><?php echo $results['crle_location']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crle_orgPincode'] != '') { ?>
                <tr>
                    <th width="275">Postal Code</th>
                    <td width="675"><?php echo $results['crle_orgPincode']; ?></td>
                </tr>
            <?php } ?>

            <?php if ($results['crle_orgAddress'] != '') { ?>
                <tr>
                    <th width="275">Address</th>
                    <td width="675"><?php echo $results['crle_orgAddress']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crle_indMobile'] != '') { ?>
                <tr>
                    <th width="275">Contact No</th>
                    <td width="675"><?php echo $results['crle_indMobile']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crle_orgContactNo'] != '') { ?>
                <tr>
                    <th width="275">Telephone No</th>
                    <td width="675"><?php echo $results['crle_orgContactNo']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['retailCategory'] > 0) {
            ?>
                <tr>

                    <th width="275">Retail Category</th>
                    <td width="675"><?php echo $retailCategory; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crle_orgEmail'] != '') { ?>
                <tr>

                    <th width="275">Email</th>
                    <td width="675"><?php echo $results['crle_orgEmail']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['baId'] > 0) { ?>
                <tr>

                    <th width="275">Business Associate</th>
                    <td width="675"><?php echo $results['baName']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['areaId'] > 0) { ?>
                <tr>

                    <th width="275">Area</th>
                    <td width="675"><?php echo $results['areaName']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crle_image'] != '') {
                 ?>
                <tr>
                    <th width="275">Image</th>
                    <td width="675"><img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="<?php echo $results['crle_image']; ?>"></img></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php if ($scheduledMeetings[0]['id'] > 0) { ?>
        <h5>Scheduled Meetings</h5>
        <table border="0" class="details_view_table">
            <tbody>
                <tr>
                    <th>Date</th><th>Time</th><th>Remarks</th>
                </tr>
                <tr>
                    <?php foreach($scheduledMeetings as $scheduledMeeting){?>
                    <td><?php echo $scheduledMeeting['meetingDate']; ?></td>
                    <td><?php echo $scheduledMeeting['meetingTime']; ?></td>
                    <td><?php echo $scheduledMeeting['meetingRemarks']; ?></td>
                    <?php }?>
                </tr>
            </tbody>
        </table>
    <?php } ?>
<?php } else { ?>
<?php } ?>