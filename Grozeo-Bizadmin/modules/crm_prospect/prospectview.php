<?php ?>
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
            <?php if ($results['crpr_orgName'] != '') { ?>
                <tr>

                    <th width="275">Store Name</th>
                    <td width="675"><?php echo $results['crpr_orgName']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crpr_type'] > 0) {
            ?>
                <tr>
                    <th width="275">Contact Type</th>
                    <td width="675"><?php echo $crpr_type; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crpr_location'] != '') { ?>
                <tr>
                    <th width="275">Location</th>
                    <td width="675"><?php echo $results['crpr_location']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crpr_orgPincode'] != '') { ?>
                <tr>
                    <th width="275">Postal Code</th>
                    <td width="675"><?php echo $results['crpr_orgPincode']; ?></td>
                </tr>
            <?php } ?>

            <?php if ($results['crpr_orgAddress'] != '') { ?>
                <tr>
                    <th width="275">Address</th>
                    <td width="675"><?php echo $results['crpr_orgAddress']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crpr_indMobile'] != '') { ?>
                <tr>
                    <th width="275">Contact No</th>
                    <td width="675"><?php echo $results['crpr_indMobile']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crpr_orgContactNo'] != '') { ?>
                <tr>
                    <th width="275">Telephone No</th>
                    <td width="675"><?php echo $results['crpr_orgContactNo']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['retailCategory'] > 0) {
            ?>
                <tr>

                    <th width="275">Retail Category</th>
                    <td width="675"><?php echo $retailCategory; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crpr_orgEmail'] != '') { ?>
                <tr>

                    <th width="275">Email</th>
                    <td width="675"><?php echo $results['crpr_orgEmail']; ?></td>
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

                    <th width="275">Email</th>
                    <td width="675"><?php echo $results['areaName']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crpr_image'] != '') {
                 ?>
                <tr>
                    <th width="275">Image</th>
                    <td width="675"><img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="<?php echo $results['crpr_image']; ?>"></img></td>
                </tr>
            <?php } ?>
            <?php if ($results['invitationSent']  == 1) { ?>
                <tr>

                    <th width="275">Code</th>
                    <td width="675"><?php echo $results['invitationCode']; ?></td>
                </tr>
            <?php } else { ?>
                <tr>

                    <th width="275">Code</th>
                    <td width="675">NA</td>
                </tr>
            <?php } ?>

        </tbody>
    </table>
<?php } else { ?>
<?php } ?>