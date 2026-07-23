<?php
require_once(INCLUDE_PATH . "/finascop_common_functions.php");
require_once(ROOT . '/finascop_config/lib.php');
define('AWS_ROOT', '/home/system/awsapi');
require(AWS_ROOT . '/aws-autoloader.php');
switch ($op) {

    case 'postS3detailsvlsl':
        $s3data['awsucketName'] = AWSBUCKETNAME;
        $s3data['uploadbucket'] = AWSBUCKETUPLOADS;
        $s3data['accessKey'] = AWSS3ASSETUPLOADACCESSID;
        $s3data['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
        $s3data['bucketRegion'] = AWSS3ASSETUPLOADREGION;
        $s3data['oncompleteurl'] = AWSBUCKETFOLDER;
        $data = json_encode($s3data);
        echo '{"success":true,"data":' . $data . '}';
        break;

    case 'saveImagesvlsl':
        $data['product_id'] = $_POST['itemId'];
        $data['image_url'] = $_POST['image_url'];
        $data['image_folder'] = $_POST['image_folder'];
        $data['image_type'] = 0;
        $data['created_at'] = date("Y-m-d H:i:s");
        $db->query('begin');
        $status = $db->perform(FINASCOP_DB . 'finascop_stock_item_images', $data);
        $status = $db->query('commit');
        if ($status) {
            echo '{"success":true}';
        }
        break;
    case 'deleteImagesvlsl':
        $db->query('begin');
        $status = $db->executeSafe("DELETE FROM finascop_stock_item_images WHERE id = ?", "i", [$_POST['id']]);
        $status = $db->query('commit');
        if ($status) {
            echo '{"success":true}';
        }
        break;
    case 'listImagesvlsl':
        $start = ($_POST['start']) ? $_POST['start'] : 0;
        $limit = ($_POST['limit']) ? $_POST['limit'] : 10;
        $bucketPath = AWSBUCKETPATH;
        $folder = AWSBUCKETFOLDER;
        $preview = SLTHUMP;
        $sql = "SELECT id,product_id,CONCAT('{$bucketPath}','/','{$folder}','','{$preview}','',image_url) as image_url,image_type FROM finascop_stock_item_images WHERE product_id = " . intval($_POST['itemId']) . " ORDER BY created_at DESC LIMIT {$start},{$limit}";
        $count = $db->getItemSafe("SELECT COUNT(*) FROM finascop_stock_item_images WHERE product_id = ?", "i", [$_POST['itemId']]);
        $pdtImages = $db->getMultipleData($sql, true);
        if ($pdtImages != false) {
            $data = json_encode($pdtImages);
            echo '{"success":true,"count":' . $count . ',"data":' . $data . '}';
        } else {
            echo '{"success":false,"data":"No Data Found"}';
        }
        break;
    case 'setMainImagesvlsl':
        $product_id = $db->getItemSafe("SELECT product_id FROM finascop_stock_item_images WHERE id = ?", "i", [$_POST['id']]);
        $data['image_type'] = 1;
        $data['updated_at'] = date("Y-m-d H:i:s");
        $db->query('begin');
        $db->query("UPDATE finascop_stock_item_images SET image_type = 0,updated_at = NOW() WHERE product_id = {$product_id}");
        $status = $db->perform(FINASCOP_DB . 'finascop_stock_item_images', $data, 'update', "id = " . intval($_POST['id']));
        $status = $db->query('commit');
        if ($status) {
            echo '{"success":true}';
        }
        break;
    case 'getImageUrls':
        $itemId = isset($_POST['itemId']) ? intval($_POST['itemId']) : 0;
        if ($itemId > 0) {
            $bucketPath = AWSBUCKETPATH;
            $folder = AWSBUCKETFOLDER;
            $preview = SLTHUMP;
            $sql = "SELECT id,product_id,CONCAT('{$bucketPath}','/','{$folder}','','{$preview}','',image_url) as image_url,image_type FROM finascop_stock_item_images WHERE product_id = {$itemId} ORDER BY image_type ASC";
            $pdtImages = $db->getMultipleData($sql, true);
            if ($pdtImages != false) {
                $imgdata = [];
                foreach ($pdtImages as $key => $image) {
                    $imgdata["imgurl" . ($key + 1)] = $image['image_url'];
                }
                $data = json_encode($imgdata);
                echo '{"success":true,"data":' . $data . '}';
            } else {
                echo '{"success":false,"data":[]}';
            }
        }
        break;
    case 'importImages':
        $itemIdFrPt = $_POST['itemId'];
        $images = [];

        // Collect image URLs from the POST data
        for ($i = 1; $i <= 5; $i++) {
            $postKey = 'imgurl' . $i;
            if (!empty($_POST[$postKey])) {
                $imageUrl = strtok($_POST[$postKey], '?');

                // Exclude URLs containing AWSBUCKETUPLOADS
                if (strpos($imageUrl, AWSBUCKETUPLOADS) === false) {
                    $images[] = $imageUrl;
                }
            }
        }

        if (!empty($images)) {
            $s3upload = new cgoS3FileHandler();
            $cloudFrontPath = 'products/';
            $documentRoot = $_SERVER["DOCUMENT_ROOT"];
            $tmpDir = $documentRoot . '/tmp/importimages/';

            // Ensure temporary directory exists
            if (!is_dir($tmpDir)) {
                if (!mkdir($tmpDir, 0777, true)) {
                    error_log("Failed to create temporary directory: " . $tmpDir);
                    return;
                }
            }

            foreach ($images as $key => $imageUrl) {
                if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                    error_log("Invalid image URL detected: " . $imageUrl);
                    continue;
                }

                // Generate unique file name
                $fileExtension = pathinfo(basename($imageUrl), PATHINFO_EXTENSION);
                $fileuploadname = md5(uniqid(rand(), true)) . "." . $fileExtension;
                $destinationPath = $tmpDir . $fileuploadname;

                if (@copy($imageUrl, $destinationPath)) {
                    $isFileUploaded = $s3upload->putFileToS3($cloudFrontPath, AWSBUCKETUPLOADS, $destinationPath, $fileuploadname);

                    if ($isFileUploaded) {
                        // Determine image type (1 for first image, 0 for others)
                        $imageType = ($key === 0) ? 1 : 0;

                        $imdata = [
                            "product_id" => $itemIdFrPt,
                            "image_url" => $fileuploadname,
                            "image_thumb_url" => '', // Assuming this is handled elsewhere or not used
                            "image_type" => $imageType,
                            "bucket_name" => AWSBUCKETUPLOADS,
                            "createdBy" => $_SESSION['admin']->Finascop_UserId,
                            "created_at" => date("Y-m-d H:i:s"),
                            "image_folder" => 'products/'
                        ];

                        try {
                            $status = $db->perform('finascop_stock_item_images', $imdata);
                            if (!$status) {
                                error_log("Failed to insert image data into DB for product_id: {$itemIdFrPt}, filename: {$fileuploadname}");
                            }
                        } catch (Exception $e) {
                            error_log("Database error inserting image data for product_id {$itemIdFrPt}: " . $e->getMessage());
                        }
                    } else {
                        error_log("S3 upload failed for image: {$imageUrl} to {$cloudFrontPath}{$fileuploadname}");
                    }

                    // Clean up the local file
                    if (file_exists($destinationPath)) {
                        unlink($destinationPath);
                    }
                } else {
                    error_log("Failed to copy image from URL: {$imageUrl} to local path: {$destinationPath}");
                }
            }
        }

        // Commit the transaction
        $status = $db->query('commit');
        if ($status) {
            echo '{"success":true}';
        } else {
            echo '{"success":false}';
        }

        break;
}
