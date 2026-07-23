<?php

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
    case 'showImages':
        ob_start();
        include('ImageFile.php');

        $rehtml = ob_get_clean();
        echo $rehtml;
        exit;

        break;
}
