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

            <?php if ($results['aaName'] != '') { ?>
                <tr>

                    <th width="275">Name</th>
                    <td width="675"><?php echo $results['aaName']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['aaName'] != '') {
                ?>
                <tr>
                    <th width="275">Mobile</th>
                    <td width="675"><?php echo $results['aaMobile']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['aaEmail'] != '') { ?>
                <tr>
                    <th width="275">Email</th>
                    <td width="675"><?php echo $results['aaEmail']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['professionId'] > 0) { ?>
                <tr>
                    <th width="275">Profession</th>
                    <td width="675"><?php echo $results['professionName']; ?></td>
                </tr>
            <?php } ?>

            <?php if ($results['businessVertical'] != '') { ?>
                <tr>
                    <th width="275">Business Vertical</th>
                    <td width="675"><?php echo $results['businessVertical']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['organisationName'] != '') { ?>
                <tr>
                    <th width="275">Organisation Name</th>
                    <td width="675"><?php echo $results['organisationName']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['organisationType'] != '') { ?>
                <tr>
                    <th width="275">Organisation Type</th>
                    <td width="675"><?php echo $results['organisationType']; ?></td>
                </tr>
            <?php } ?>
            
            <?php if ($results['gstNumber'] != '') { ?>
                <tr>

                    <th width="275"><?php echo GST;?> Number</th>
                    <td width="675"><?php echo $results['gstNumber']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['businessLocation'] != '') { ?>
                <tr>
                    <th width="275">Location</th>
                    <td width="675"><?php echo $results['businessLocation']; ?></td>
                </tr>
            <?php } ?>           
            <?php if ($results['country_code'] != '') { ?>
                <tr>
                    <th width="275">Country Code</th>
                    <td width="675"><?php echo $results['country_code']; ?></td>
                </tr>
            <?php } ?> 
        </table>
    <?php } else { ?>
    <?php } ?>
</div>