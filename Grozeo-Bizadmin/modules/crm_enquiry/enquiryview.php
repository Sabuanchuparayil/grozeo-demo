<html>
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
            <?php if($results['enquiryName'] != '') { ?>
                <tr>
                    <th width="275">Name</th>
                    <td width="675"><?php echo $results['enquiryName']; ?></td>
                </tr>
            <?php } if($results['enquiryMobile'] != '') {?>
                <tr>
                    <th width="275">Mobile</th>
                    <td width="675"><?php echo $results['enquiryMobile']; ?></td>
                </tr>
            <?php } if($results['enquiryTelephone'] != '') { ?>
                <tr>
                    <th width="275">Telephone</th>
                    <td width="675"><?php echo $results['enquiryTelephone']; ?></td>
                </tr>
            <?php } if($results['enquiryEmail'] != '') { ?>
                <tr>
                    <th width="275">Email</th>
                    <td width="675"><?php echo $results['enquiryEmail']; ?></td>
                </tr>
            <?php } if($results['enquiryAddress'] != '') { ?>
                <tr>
                    <th width="275">Address</th>
                    <td width="675"><?php echo $results['enquiryAddress']; ?></td>
                </tr>
            <?php } if($results['enquiryProjectType'] != '') { ?>
                <tr>
                    <th width="275">Project Type</th>
                    <td width="675"><?php echo $results['enquiryProjectType']; ?></td>
                </tr>
                <?php } if($results['crms_id'] > 0) { 
                    $enqSource = $db->getItemFromDB("SELECT crms_name FROM finascop_crm_source WHERE crms_id = {$results['crms_id']}");?>
                <tr>
                    <th width="275">Source</th>
                    <td width="675"><?php echo $enqSource; ?></td>
                </tr>
            <?php }  if($results['crme_description'] != '') {?>
                 <tr>
                    <th width="275">Description</th>
                    <td width="675"><?php echo $results['crme_description']; ?></td>
                </tr>
                <?php }  if($results['country_code'] != '') {?>
                 <tr>
                    <th width="275">Country Code</th>
                    <td width="675"><?php echo $results['country_code']; ?></td>
                </tr>
                <?php } if($results['crmm_location'] != '') {?>
                 <tr>
                    <th width="275">Location</th>
                    <td width="675"><?php echo $results['crmm_location']; ?></td>
                </tr>
                <?php } ?>
                
            </tbody>
        </table>
    <?php } else { ?>
    <?php } ?>
</html>