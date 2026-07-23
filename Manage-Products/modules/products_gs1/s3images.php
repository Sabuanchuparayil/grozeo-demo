<?php

use Aws\S3\S3Client;

// Specify your AWS credentials and region
$credentials = [
    'key'    => PBUPLOADACCESSID,
    'secret' => PBUPLOADSECRETKEY,
    'region' => PBUPLOADREGION
];

// Create an S3Client object
$s3Client = new S3Client([
    'version'     => 'latest',
    'credentials' => $credentials,
    'region'      => PBUPLOADREGION
]);

// Bucket name
$bucketName = 'productbankimages';

// Prefix to search for
$prefix = $_REQUEST['gtin'];

//try {
// Use the listObjectsV2 method to list objects with the specified prefix
$result = $s3Client->listObjectsV2([
    'Bucket' => $bucketName,
    'Prefix' => $prefix
]);
// Output the list of objects

/*} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}*/
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title></title>
    <link href="https://fonts.googleapis.com/css?family=Lato:100,100i,300,300i,400,400i,700&display=swap" rel="stylesheet">
</head>
<style>
    .leftarea-block {
        display: flex;
        flex-wrap: wrap;
    }

    .leftarea-block>div {
        width: calc(33.33% - 42px);
        margin: 10px;
        padding: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 1px solid black;
    }

    .leftarea-block>div img {
        max-height: 200px;
        max-width: 100%;
    }
</style>

<body>
    <header class="clearfix">

    </header>
    <main>
        <div class="leftarea-block">
            <?php
            foreach ($result['Contents'] as $object) { ?>
                <div><img src="https://productbankimages.s3.eu-west-2.amazonaws.com/<?php echo $object['Key'] . PHP_EOL; ?>" /></div>
            <?php } ?>


        </div>
    </main>
</body>

</html>