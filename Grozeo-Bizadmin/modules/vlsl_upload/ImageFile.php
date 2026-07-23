<?php 
 $bucketPath = AWSBUCKETPATH;
 $folder = AWSBUCKETFOLDER;
 $preview = SLTHUMP;
 $sql = "SELECT id,product_id,CONCAT('{$bucketPath}','/','{$folder}','','{$preview}','',image_url) as image_url,image_type FROM finascop_stock_item_images WHERE product_id = " . intval($_REQUEST['productId']) . " ORDER BY image_type DESC ";
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
        <div class="leftarea-block">
            <?php
            foreach ($pdtImages as $object) { ?>
                <div><img src="<?php echo $object['image_url']; ?>" /></div>
            <?php } ?>


        </div>
    </main>
    </body>

</html>