<?php
$leadName = $surveyDetails[0]['crm_user_name'];
$surveyDate = date('d-m-Y', strtotime($surveyDetails[0]['responseDate']));
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title><?= SITE_TITLE ?> - Survey of - <?php echo $leadName; ?> on <?php echo $surveyDate; ?></title>
</head>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    .container {
        padding: 5px;
    }

    .logo {
        width: 150px;
    }

    .logo img {
        width: 100%;
        height: auto;
    }

    h1 {
        font-size: 30px;
    }

    ul {
        padding: 0px;
    }

    .address li {
        width: 50%;
        float: left;
        list-style: none;
        padding: 0px;
    }

    .txt_r {
        text-align: right;
        padding-right: 20px !important;
    }

    .txt_c {
        text-align: center;
    }

    .txt_l {
        text-align: left;
    }

    .pad {
        padding: 8px !important;
    }

    .valign {
        vertical-align: top !important;
    }
</style>

<body>
    <div class="container">
        <div class="innercontainer" margin="30px">
            <div class="table-responsive">
                
                <?php if (count($surveyDetails) > 0 && !empty($surveyDetails[0]['crm_user_name'])) { 
                    $q = 0;?>
                    <h3>Survey of - <?php echo $leadName; ?> on <?php echo $surveyDate; ?></h3>
                    <table width="100%" cellspacing="0" cellpadding="0">
                        <?php foreach ($surveyDetails as $surveyDetail) { ?>
                            <?php if (!empty($surveyDetail['question_text'])) { ?>
                                <thead>
                                    <tr>
                                        <td style="font-size: 16px;"><?php $q++;echo 'Q.'.$q.' '.$surveyDetail['question_text']; ?></td>
                                    </tr>
                                </thead>
                            <?php } ?>
                            <tbody>
                                <tr>
                                    <td>
                                        <?php if (!empty($surveyDetail['question_text'])) { ?><ul><?php } ?>
                                            <li><?php if ($surveyDetail['isCorrect'] == 1) { ?><strong><?php } ?><?php echo $surveyDetail['option_text']; ?><?php if ($surveyDetail['isCorrect'] == 1) { ?></strong><?php } ?></li>
                                            <?php if (!empty($surveyDetail['question_text'])) { ?>
                                            </ul><?php } ?>
                                    </td>
                                </tr>

                            </tbody>
                        <?php } ?>
                    </table>
                <?php } else { ?>
                    <p>No survey to load</p>
                <?php } ?>
            </div>
        </div>
    </div>

</body>

</html>