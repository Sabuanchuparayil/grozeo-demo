<?php ?>
<style>
    .details_view_table{ border-collapse:collapse;}
    .details_view_table td,.details_view_table th{border-bottom:1px solid #EDEDED; font-family: Source Sans Pro,Verdana,Geneva,sans-serif;
                                                  font-size: 12px;padding:3px;}
    .details_view_table th{ padding-right:21px; padding-left:14px;width:35%; vertical-align:top; font-weight: inherit; text-align: left;}
    .details_view_table td{font-weight:bold; }
    .details_view_table td span{float:left; width:95%; text-align:justify; font-weight:normal;}
    .details_view_table th[colspan="2"]{font-size:13px;  background: #f0f0f0 none repeat scroll 0 0;
                                        border-color: #d7d7d7;}
    </style>
    <div class="details-outer">
        <?php if (!empty($results)) { ?>

        <table cellspacing="2" cellpadding="2" border="0" class="details_view_table">

            <?php if ($results['textfieldContactOrganisationName'] != '') { ?>
                <tr>

                    <th width="275">Organisation Name</th>
                    <td width="675"><?php echo $results['textfieldContactOrganisationName']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['org_type'] != '') { ?>
                <tr>
                    <th width="275">Organisation Type</th>
                    <td width="675"><?php echo $results['org_type']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['textfieldContactAddress'] != '') { ?>
                <tr>
                    <th width="275">Organisation Address</th>
                    <td width="675"><?php echo $results['textfieldContactAddress']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['org_country'] != '') { ?>
                <tr>
                    <th width="275">Organisation Country</th>
                    <td width="675"><?php echo $results['org_country']; ?></td>
                </tr>
            <?php } ?>

            <?php if ($results['org_state'] != '') { ?>
                <tr>
                    <th width="275">Organisation State</th>
                    <td width="675"><?php echo $results['org_state']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['org_district'] != '') { ?>
                <tr>
                    <th width="275">Organisation City</th>
                    <td width="675"><?php echo $results['org_district']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['numberfieldContactOrganisationPincode'] != '') { ?>
                <tr>
                    <th width="275">Organisation Pincode</th>
                    <td width="675"><?php echo $results['numberfieldContactOrganisationPincode']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crmcontactsNumberfieldPrimary'] != '')  { ?>
                <tr>

                    <th width="275">Organisation Primary Mobile</th>
                    <td width="675"><?php echo $results['crmcontactsNumberfieldPrimary']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['crmcontactsNumberfieldSecondary'] != '') { ?>
                <tr>

                    <th width="275">Organisation Secondary mobile</th>
                    <td width="675"><?php echo $results['crmcontactsNumberfieldSecondary']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['numberfieldContactOrganisationContactNo'] != '') { ?>
                <tr>
                    <th width="275">Organisation Telephone No.</th>
                    <td width="675"><?php echo $results['numberfieldContactOrganisationContactNo']; ?></td>
                </tr>
            <?php } ?>

            <?php if ($results['textfieldContactOrganisationUrl'] != '') { ?>
                <tr>
                    <th width="275">Organisation Url</th>
                    <td width="675"><?php echo $results['textfieldContactOrganisationUrl']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['textfieldContactOrganisationEmail'] != '') { ?>
                <tr>
                    <th width="275">Organisation Email</th>
                    <td width="675"><a href="mailto:smiles@slanand.com"><?php echo $results['textfieldContactOrganisationEmail']; ?></a></td>
                </tr>
            <?php } ?>

            <?php if ($results['textfieldContactDetailsContactPerson'] != '') { ?>
                <tr>
                    <th width="275">Contact person</th>
                    <td width="675"><?php echo $results['textfieldContactDetailsContactPerson']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['numberfieldContactDetailsprimaryMobile'] != '') { ?>
                <tr>
                    <th width="275">Mobile</th>
                    <td width="675"><?php echo $results['numberfieldContactDetailsprimaryMobile']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['numberfieldContactDetailsSecondaryMobile'] != '') { ?>
                <tr>
                    <th width="275">Secondary Mobile</th>
                    <td width="675"><?php echo $results['numberfieldContactDetailsSecondaryMobile']; ?></td>
                </tr>
            <?php } ?>

            <?php if ($results['numberfieldContactDetailsTelephone'] != '' )  { ?>
                <tr>
                    <th width="275">Telephone No </th>
                    <td width="675"><?php echo $results['numberfieldContactDetailsTelephone']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['textfieldContactDetailsEmail'] != '')  { ?>
                <tr>
                    <th width="275">Email</th>
                    <td width="675"><a href="mailto:smiles@slanand.com"><?php echo $results['textfieldContactDetailsEmail']; ?></a></td>
                </tr>
            <?php } ?>
            <?php if ($results['textfieldIndividualAddress'] != '') { ?>
                <tr>
                    <th width="275">Address</th>
                    <td width="675"><?php echo $results['textfieldIndividualAddress']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['textfieldContactShippingAddress'] != '') { ?>
                <tr>
                    <th width="275">Shipping Address</th>
                    <td width="675"><?php echo $results['textfieldContactShippingAddress']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['ship_country'] != '') { ?>
                <tr>
                    <th width="275">Shipping Country</th>
                    <td width="675"><?php echo $results['ship_country']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['comboContactShippingState'] != '') { ?>
                <tr>
                    <th width="275">Shipping State</th>
                    <td width="675"><?php echo $results['comboContactShippingState']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['textfieldContactShippingStreet'] != '') { ?>
                <tr>
                    <th width="275">Shipping Street</th>
                    <td width="675"><?php echo $results['textfieldContactShippingStreet']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['textfieldContactshippingCity'] != '') { ?>
                <tr>
                    <th width="275">Shipping City</th>
                    <td width="675"><?php echo $results['textfieldContactshippingCity']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['numberfieldContactShippingPincode'] != '') { ?>
                <tr>
                    <th width="275">Shipping Pincode</th>
                    <td width="675"><?php echo $results['numberfieldContactShippingPincode']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['textfieldContactsshippingLocation'] != '') { ?>
                <tr>
                    <th width="275">Shipping Location</th>
                    <td width="675"><?php echo $results['textfieldContactsshippingLocation']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['textfieldContactTaxDetailsGst'] != '') { ?>
                <tr>
                    <th width="275">GST</th>
                    <td width="675"><?php echo $results['textfieldContactTaxDetailsGst']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['textfieldContactTaxDetailsPan'] != '') { ?>
                <tr>
                    <th width="275">PAN</th>
                    <td width="675"><?php echo $results['textfieldContactTaxDetailsPan']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['textfieldContactTaxDetailsTan'] != '') { ?>
                <tr>
                    <th width="275">TAN</th>
                    <td width="675"><?php echo $results['textfieldContactTaxDetailsTan']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['project_type'] != '') { ?>
                <tr>
                    <th width="275">Project Type</th>
                    <td width="675"><?php echo $results['project_type']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['datefieldContactProjectDetailsDateOfProject'] != '') { ?>
                <tr>
                    <th width="275">Project Date</th>
                    <td width="675"><?php echo $results['datefieldContactProjectDetailsDateOfProject']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['textfieldContactProjectDetailsLocation'] != '') { ?>
                <tr>
                    <th width="275">Project Location</th>
                    <td width="675"><?php echo $results['textfieldContactProjectDetailsLocation']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['textfieldContactProjectDetailsNoOfEvents'] != '') { ?>
                <tr>
                    <th width="275">No. of Events</th>
                    <td width="675"><?php echo $results['textfieldContactProjectDetailsNoOfEvents']; ?></td>
                </tr>
            <?php } ?>
            <?php if ($results['textareaContactDescription'] != '') { ?>
                <tr>
                    <th width="275">Description</th>
                    <td width="675"><?php echo $results['textareaContactDescription']; ?></td>
                </tr>
            <?php } ?>
            <?php // } elseif($results['radiogroupContactType' == 2]) {?>
        </table>
    <?php } else { ?>
    <?php } ?>
</div>