<?php ?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?= SITE_TITLE ?> - Stock alert of - <?php echo $branchName; ?> on <?php echo $sa_Displaydate; ?></title>
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
        body{
            font-family: 'Poppins', sans-serif;
        }
        .container {padding: 5px;}
        .logo{width: 150px;}
        .logo img {width: 100%; height: auto;}
        h1 {font-size: 30px;}
        ul {padding: 0px;}
        .address li {width: 50%; float: left; list-style: none; padding: 0px;}
        .txt_r {text-align: right; padding-right: 20px!important;}
        .txt_c {text-align: center;}
        .txt_l {text-align: left;}
        .pad {padding: 8px!important;}
        .valign {vertical-align: top!important;}
    </style>
    <body>
        <?php if ($branchId > 0) { ?>
            <div class="container">
                <div class="innercontainer" margin="30px">
                    <div class="table-responsive">
                        <h4>Stock alert of - <?php echo $branchName; ?> on <?php echo $sa_Displaydate; ?></h4>
                        <?php if ($details[0]['stitId'] > 0) { ?>
                            <table class="table table-bordered alertdetails" id="alertdetailsid" width="100%" height="100%">
                                <thead>
                                    <tr>
                                        <td >SI No.</td>
                                        <td >Item Name</td>
                                        <td >Projected Stock</td>
                                        <td >In Stock</td>
                                        <td >Total</td>
                                        <td >Unit</td>

                                    </tr>

                                    <?php
                                    $i = 1;
                                    foreach ($details as $detail) {
                                        $projectedStock = $db->getItemFromDB("SELECT SUM(rpd_quantity) FROM retaline_procurement_details WHERE rpd_stitId = {$detail['stitId']}  AND rpd_date = '{$sa_date}' AND rpd_branch  = {$branchId} AND rpd_status = 0 ");
                                        if (empty($detail['item_count'])) {
                                            $item_count = 0;
                                        } else {
                                            $item_count = round($detail['item_count'], 3);
                                        }
                                        if (empty($projectedStock)) {
                                            $rpd_quantity = 0;
                                        } else {
                                            $rpd_quantity = round($projectedStock, 3);
                                        }
                                        $total = $rpd_quantity + $item_count;
                                        $least_package_type_name = $db->getItemFromDB("SELECT least_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = {$detail['stitId']}");
                                        //$taxPercent = $podDetails[$i]['retgrnd_itemoffrrate'] / 
                                        //$retgrnd_purchasingUnit = $db->getItemFromDB("SELECT package_type_name FROM mypha_productpackage_type WHERE package_type_id = {$detail['retgrnd_purchasingUnit']}")
                                        ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?php echo $detail['stit_SKU']; ?></td>
                                            <td style='text-align: right;padding-right: 10px;'><?php echo $rpd_quantity; ?></td>
                                            <td style='text-align: right;padding-right: 10px;'><?php echo $item_count; ?></td>
                                            <td style='text-align: right;padding-right: 10px;'><?php echo round($total, 3); ?></td>
                                            <td><?php echo $least_package_type_name; ?></td>

                                        </tr>   
                                        <?php
                                    }
                                    ?>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <button style="display: none;" onclick="myFunction()">COPY DATA</button>
                        <?php } else { ?>
                            <p>No Stock for this day</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
        <script>
            function myFunction() {
                /* Get the text field */
                var copyText = document.getElementsByClassName("alertdetails")[0];

                /* Select the text field */
                copyText.select();
                document.execCommand("copy");

                //copyText.setSelectionRange(0, 99999); /* For mobile devices */

                /* Copy the text inside the text field */
                //navigator.clipboard.writeText(copyText.value);

                /* Alert the copied text */
                alert("Copied the text: " + copyText.value);
            }
        </script>
    </body>
</html>