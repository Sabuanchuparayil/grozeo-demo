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
    <?php if (!empty($results)) { ?>
        <table cellspacing="2" cellpadding="2" border="0" class="details_view_table">
        <tbody>
            <?php if ($results['textfieldMarketingLeadOrgName'] != '') { ?>
                <tr>
                    <th width="275">Organisation Name</th>
                    <td width="675"><?php echo $results['textfieldMarketingLeadOrgName']; ?></td>
                </tr>
            <?php } if ($results['org_type'] != '') { ?>
                <tr>
                    <th width="275">Organisation Type</th>
                    <td width="675"><?php echo $results['org_type']; ?></td>
                </tr>
            <?php } if ($results['textareaMarketingLeadOrganisationAddress'] != '') { ?>
                <tr>
                    <th width="275">Organisation Address</th>
                    <td width="675"><?php echo $results['textareaMarketingLeadOrganisationAddress']; ?></td>
                </tr>
            <?php } if ($results['org_country'] != '') { ?>
                <tr>
                    <th width="275">Organisation Country</th>
                    <td width="675"><?php echo $results['org_country']; ?></td>
                </tr>
            <?php } if ($results['numberfieldMarketingLeadOrganisationPrimarymobile'] != '') { ?>
                <tr>
                    <th width="275">Organisation Primary Mobile</th>
                    <td width="675"><?php echo $results['numberfieldMarketingLeadOrganisationPrimarymobile']; ?></td>
                </tr>
            <?php } if ($results['numberfieldMarketingLeadOrganisationSecondarymobile'] != '') { ?>
                <tr>
                    <th width="275">Organisation Secondary Mobile</th>
                    <td width="675"><?php echo $results['numberfieldMarketingLeadOrganisationSecondarymobile']; ?></td>
                </tr>
            <?php } if ($results['numberfieldMarketingLeadOrganisationContactNo'] != '') { ?>
                <tr>
                    <th width="275">Organisation Telephone</th>
                    <td width="675"><?php echo $results['numberfieldMarketingLeadOrganisationContactNo']; ?></td>
                </tr>
            <?php } if ($results['textfieldMarketingLeadOrganisationUrl'] != '') { ?>
                <tr>
                    <th width="275">Organisation Url</th>
                    <td width="675"><?php echo $results['textfieldMarketingLeadOrganisationUrl']; ?></td>
                </tr>
            <?php } if ($results['textfieldMarketingLeadOrganisationEmail'] != '') { ?>
                <tr>
                    <th width="275">Organisation Email</th>
                    <td width="675"><a href="mailto:smiles@slanand.com"><?php echo $results['textfieldMarketingLeadOrganisationEmail']; ?></a></td>
                </tr>
            <?php } if ($results['textfieldMarketingLeadIndividualContactPerson'] != '') { ?>
                <tr>
                    <th width="275">Contact person</th>
                    <td width="675"><?php echo $results['textfieldMarketingLeadIndividualContactPerson']; ?></td>
                </tr>
            <?php } if ($results['textfieldMarketingLeadIndividualDesignation'] != '') { ?>
                <tr>
                    <th width="275">Designation</th>
                    <td width="675"><?php echo $results['textfieldMarketingLeadIndividualDesignation']; ?></td>
                </tr>
            <?php } if ($results['numberfieldMarketingLeadindividualPrimarymobile'] != '') { ?>
                <tr>
                    <th width="275">Mobile</th>
                    <td width="675"><?php echo $results['numberfieldMarketingLeadindividualPrimarymobile']; ?></td>
                </tr>
            <?php } if ($results['numberfieldMarketingLeadindividualSecondarymobile'] != '') { ?>
                <tr>
                    <th width="275">Secondary Mobile</th>
                    <td width="675"><?php echo $results['numberfieldMarketingLeadindividualSecondarymobile']; ?></td>
                </tr>
            <?php } if ($results['numberfieldMarketingLeadIndividualTelephone'] != '')  { ?>
                <tr>
                    <th width="275">Telephone</th>
                    <td width="675"><?php echo $results['numberfieldMarketingLeadIndividualTelephone']; ?></td>
                </tr>
            <?php } if ($results['textfieldMarketingLeadIndividualEmail'] != '') { ?>
                <tr>
                    <th width="275">Email</th>
                    <td width="675"><a href="mailto:smiles@slanand.com"><?php echo $results['textfieldMarketingLeadIndividualEmail']; ?></a></td>
                </tr>
            <?php } if ($results['textareaMarketingLeadIndividualAddress'] != '') { ?>
                <tr>
                    <th width="275">Address</th>
                    <td width="675"><?php echo $results['textareaMarketingLeadIndividualAddress']; ?></td>
                </tr>
            <?php } if ($results['textfieldMarketingLeadShippingAddress'] != '') { ?>
                <tr>
                    <th width="275">Shipping Address</th>
                    <td width="675"><?php echo $results['textfieldMarketingLeadShippingAddress']; ?></td>
                </tr>
            <?php } if ($results['ship_country'] != '') { ?>
                <tr>
                    <th width="275">Shipping Country</th>
                    <td width="675"><?php echo $results['ship_country']; ?></td>
                </tr>
            <?php } if ($results['textfieldMarketingLeadShippingState'] != '') { ?>
                <tr>
                    <th width="275">Shipping State</th>
                    <td width="675"><?php echo $results['textfieldMarketingLeadShippingState']; ?></td>
                </tr>
            <?php } if ($results['textfieldMarketingLeadShippingCity'] != '') { ?>
                <tr>
                    <th width="275">Shipping City</th>
                    <td width="675"><?php echo $results['textfieldMarketingLeadShippingCity']; ?></td>
                </tr>
            <?php } if ($results['textfieldMarketingLeadshippingStreet'] != '') { ?>
                <tr>
                    <th width="275">Shipping Street</th>
                    <td width="675"><?php echo $results['textfieldMarketingLeadshippingStreet']; ?></td>
                </tr>
            <?php } if ($results['numberfieldMarketingLeadShippingPincode'] != '') { ?>
                <tr>
                    <th width="275">Shipping Pincode</th>
                    <td width="675"><?php echo $results['numberfieldMarketingLeadShippingPincode']; ?></td>
                </tr>
            <?php } if ($results['textfieldMarketingLeadshippingLocation'] != '') { ?>
                <tr>
                    <th width="275">Shipping Location</th>
                    <td width="675"><?php echo $results['textfieldMarketingLeadshippingLocation']; ?></td>
                </tr>
            <?php } if ($results['textfieldMarketingLeadTaxGst'] != '') { ?>
                <tr>
                    <th width="275">GST</th>
                    <td width="675"><?php echo $results['textfieldMarketingLeadTaxGst']; ?></td>
                </tr>
            <?php } if ($results['textfieldMarketingLeadTaxPan'] != '') { ?>
                <tr>
                    <th width="275">PAN</th>
                    <td width="675"><?php echo $results['textfieldMarketingLeadTaxPan']; ?></td>
                </tr>
            <?php } if ($results['textfieldMarketingLeadTaxTan'] != '') { ?>
                <tr>
                    <th width="275">TAN</th>
                    <td width="675"><?php echo $results['textfieldMarketingLeadTaxTan']; ?></td>
                </tr>
            <?php } if ($results['project_type'] != '') { ?>
                <tr>
                    <th width="275">Project Type</th>
                    <td width="675"><?php echo $results['project_type']; ?></td>
                </tr>
            <?php } if ($results['datefieldMarketingLeadProjectProjectDate'] != '') { ?>
                <tr>
                    <th width="275">Project Date</th>
                    <td width="675"><?php echo $results['datefieldMarketingLeadProjectProjectDate']; ?></td>
                </tr>
            <?php } if ($results['textfieldMarketingLeadProjectLocation'] != '') { ?>
                <tr>
                    <th width="275">Project Location</th>
                    <td width="675"><?php echo $results['textfieldMarketingLeadProjectLocation']; ?></td>
                </tr>
            <?php } if ($results['textfieldMarketingLeadProjectNoOfEvents'] != '') { ?>
                <tr>
                    <th width="275">No. of Events</th>
                    <td width="675"><?php echo $results['textfieldMarketingLeadProjectNoOfEvents']; ?></td>
                </tr>
            <?php } if ($results['textareaMarketingDescriptioninformationDescription'] != '') { ?>
                <tr>
                    <th width="275">Description</th>
                    <td width="675"><?php echo $results['textareaMarketingDescriptioninformationDescription']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } else { ?>
<?php } ?>