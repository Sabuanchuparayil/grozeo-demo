<?php global $db;?>
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
<div class="details-outer">
    <?php if (!empty($results)) { ?>

        <table cellspacing="2" cellpadding="2" border="0" class="details_view_table">

            <?php if ($results['crco_orgName'] != '') { ?>
                <tr>

                    <th width="275">Store Name</th>
                    <td width="675"><?php echo $results['crco_orgName']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crco_type'] > 0) {
                ?>
                <tr>
                    <th width="275">Contact Type</th>
                    <td width="675"><?php echo $crco_type; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crco_location'] != '') { ?>
                <tr>
                    <th width="275">Location</th>
                    <td width="675"><?php echo $results['crco_location']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crco_orgPincode'] != '') { ?>
                <tr>
                    <th width="275">Postal Code</th>
                    <td width="675"><?php echo $results['crco_orgPincode']; ?></td>
                </tr>
            <?php } ?>

            <?php if ($results['crco_orgAddress'] != '') { ?>
                <tr>
                    <th width="275">Address</th>
                    <td width="675"><?php echo $results['crco_orgAddress']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crco_indMobile'] != '') { ?>
                <tr>
                    <th width="275">Contact No</th>
                    <td width="675"><?php echo $results['crco_indMobile']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crco_orgContactNo'] != '') { ?>
                <tr>
                    <th width="275">Telephone No</th>
                    <td width="675"><?php echo $results['crco_orgContactNo']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['retailCategory'] > 0) {
                ?>
                <tr>

                    <th width="275">Business Category</th>
                    <td width="675"><?php echo $retailCategory.' '.$businessCategory; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crco_orgEmail'] != '') { ?>
                <tr>

                    <th width="275">Email</th>
                    <td width="675"><?php echo $results['crco_orgEmail']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crco_orgCountry'] != '') { ?>
                <tr>
                    <th width="275">Country</th>
                    <td width="675"><?php echo $results['crco_orgCountry']; ?></td>
                </tr>
            <?php } ?>

            <?php if ($results['crco_glocality'] != '') { ?>
                <tr>
                    <th width="275">Place</th>
                    <td width="675"><?php echo $results['crco_glocality']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crco_gplace'] != '') { ?>
                <tr>
                    <th width="275">State/Province</th>
                    <td width="675"><?php echo $results['crco_gplace']; ?></td>
                </tr>
            <?php } ?>

            <?php if ($results['crco_mode'] > 0) {
                 ?>
                <tr>
                    <th width="275">Mode</th>
                    <td width="675"><?php echo $crco_mode; ?></td>
                </tr>
            <?php } ?>

            <?php if ($results['crco_image'] != '') {
                 ?>
                <tr>
                    <th width="275">Image</th>
                    <td width="675"><img style="width: auto; height: auto; max-width: 100%; max-height: 100%;" src="<?php echo $results['crco_image']; ?>"></img></td>
                </tr>
            <?php } ?>
            
        </table>
    <?php } else { ?>
    <?php } ?>
</div>