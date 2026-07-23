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
            <?php if($results['invitationCode'] != '') { ?>
                <tr>
                    <th width="275">Code</th>
                    <td width="675"><?php echo $results['invitationCode']; ?></td>
                </tr>
            <?php } if($results['codeTypeName'] != '') {?>
                <tr>
                    <th width="275">Code Type</th>
                    <td width="675"><?php echo $results['codeTypeName']; ?></td>
                </tr>
            <?php } if($results['enquiryTelephone'] != '') { ?>
                <tr>
                    <th width="275">Referrer Type</th>
                    <td width="675"><?php echo $results['referrerTypeName']; ?></td>
                </tr>
            <?php } if($results['validityName'] != '') { ?>
                <tr>
                    <th width="275">Validity</th>
                    <td width="675"><?php echo $results['validityName']; ?></td>
                </tr>
            <?php } if($results['referrerName'] != '') { ?>
                <tr>
                    <th width="275">Referrer</th>
                    <td width="675"><?php echo $results['referrerName']; ?></td>
                </tr>
            <?php } if($results['crpr_ExpiredOn'] != '') { ?>
                <tr>
                    <th width="275">Expired On</th>
                    <td width="675"><?php echo $results['crpr_ExpiredOn']; ?></td>
                </tr>
                <?php }   if($results['crpr_CreatedOn'] != '') {?>
                 <tr>
                    <th width="275">Created On</th>
                    <td width="675"><?php echo $results['crpr_CreatedOn']; ?></td>
                </tr>                
                <?php }   if($results['blockMerchant'] == 1) {?>
                 <tr>
                    <th width="275">Block Merchant admin access after store creation</th>
                    <?php if($results['blockMerchant'] == 1) {?>
                        <td width="675">Show ‘Plan Upgrade UI’ after Store Creation</td>
                    <?php } else {?>
                        <td width="675">Show ‘Pending Actions UI’ with Plan Upgrade</td>
                    <?php } ?>
                    
                </tr>                
                <?php } if($results['invitationLink'] != '') { ?>
                <tr>
                    <th width="275">Invitation Link</th>
                    <td width="675"><?php echo $results['invitationLink']; ?></td>
                </tr>
            <?php }?>
                
            </tbody>
        </table>
    <?php } else { ?>
    <?php } ?>
</html>