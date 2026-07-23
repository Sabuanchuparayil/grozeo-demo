<?php
$productId = $_REQUEST['productId'];
$bucketPath = AWSBUCKETPATH;
$folder = AWSBUCKETFOLDER;
$preview = SLTHUMP;
$sql = "SELECT id,product_id,CONCAT('{$bucketPath}','/','{$folder}','','{$preview}','',image_url) as image_url,image_type FROM thirdparty_item_images WHERE product_id = {$productId} ORDER BY created_at DESC";
$count = $db->getItemFromDB("SELECT COUNT(*) FROM thirdparty_item_images WHERE product_id = {$productId}");
$pdtImages = $db->getMultipleData($sql, true);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title></title>
    <link href="https://fonts.googleapis.com/css?family=Lato:100,100i,300,300i,400,400i,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="resources/css/orderinvoice-style.css" media="all" />
</head>

<body>
    <header class="clearfix">

    </header>
    <main>
        <div class="left-area-block">

            <table border="1" cellspacing="0" cellpadding="0" class="ship-detail-table">
                <tbody>
                    <?php
                    if (count($pdtImages) > 0) {
                        foreach ($pdtImages as $pdtImage) { ?>
                            <tr>
                                <td><img src="<?php echo $pdtImage['image_url']; ?>" alt="Image Front" height="250" width="270" /></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td>No images to view.</td>
                        </tr>
                    <?php } ?>


                </tbody>
            </table>

        </div>
    </main>
</body>

</html>