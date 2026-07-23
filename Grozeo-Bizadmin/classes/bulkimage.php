<?php

define('ROOT', dirname(dirname(__FILE__)));
define('INCLUDE_PATH', ROOT . "/includes");
require(ROOT . '/includes/config.php');
define('AWS_ROOT', '/home/system/awsapi');
//include(INCLUDE_PATH . "/config.php");
require(INCLUDE_PATH . '/functions.php');
require(ROOT . '/includes/lib.php');
require(ROOT . '/finascop_config/lib.php');
require(ROOT . '/finascop_config/config.php');
require('TextLocal.php');
require(AWS_ROOT . '/aws-autoloader.php');

class ImageUpload {

    private static $tmpsdata;

    public static function imageBulkUpload() {
        if (isset(self::$tmpsdata)) {
            echo 'Skip call due to another call in processing';
            return null;
        }


        try {
            $db = new sqlDb(DSN);
            $qry = "SELECT * FROM tmp_master_images INNER JOIN tmp_master_pro ON tmp_master_pro.sno = tmp_master_images.sno WHERE masterid > 0 AND isImageupload = 0  ";
            $tmpsdata = $db->getMultipleData($qry, true);

            if (count($tmpsdata) > 0) {
                foreach ($tmpsdata as $tmpdata) {

                    $db->query('begin');
                    //File download
                    //$url = str_replace('?dl=0', '?dl=1', $tmpdata['Images']);
                    $url = '/var/www/sites/product.admin.velosit.in/public/tmp/grozeo-images/' . $tmpdata['Images'];

                    // Use basename() function to return the base name of file 
                    $fileuploadname = trim(str_replace('.', '', uniqid("", true))) . ".jpg";
                    $file_name = $fileuploadname;

                    // Use file_get_contents() function to get the file
                    // from url and use file_put_contents() function to
                    // save the file by using base name
                    //Image upload

                    $sourcePath = file_get_contents($url);
                    $destinationPath = '/var/www/sites/product.admin.velosit.in/public/tmp/importimages/' . $file_name;
                    if (file_put_contents($destinationPath, $sourcePath)) {
                        $imgdownloaded = true;
                    } else {
                        $imgdownloaded = false;
                        echo "File downloading failed. - " . "\t" . $tmpdata['sno'] . "\t" . $SKU . "\r\n";
                    }
                    if ($imgdownloaded == 1) {
                        $s3upload = new cgoS3FileHandler();

                        $cloudFrontPath = 'products/';
                        $isFileUploaded = $s3upload->putFileToS3($cloudFrontPath, AWSBUCKETUPLOADS, $destinationPath, $file_name);
                        if ($isFileUploaded == 1) {
                            $data = array(
                                "product_id" => $tmpdata['masterid'],
                                "image_url" => $fileuploadname,
                                "image_thumb_url" => '',
                                "image_type" => 1,
                                "bucket_name" => '',
                                "image_folder" => 'products/'
                            );
                            $status = $db->perform('finascop_stock_item_images', $data);
                            $db->query("update tmp_master_pro set isImageupload = 1 WHERE tmp_id = {$tmpdata['tmp_id']} and masterid = {$tmpdata['masterid']}");
                            try {
                                unlink($destinationPath);
                            } catch (Exception $ex) {
                                
                            }
                        }
                    }
                    $message = 'Saved Successfully';
                    $db->query('commit');
                }
            } else {
                echo 'No data to import.';
            }
        } catch (Exception $ex) {
            
        } finally {
            $tmpsdata = null;
        }
        //return self::$tmpsdata;
    }

    public static function imageLinkUpload() {
        if (isset(self::$tmpsdata)) {
            echo 'Skip call due to another call in processing';
            return null;
        }
        try {
            $db = new sqlDb(DSN);
            $qry = "SELECT * FROM tmp_master_images INNER JOIN tmp_master_pro ON tmp_master_pro.sno = tmp_master_images.sno WHERE masterid > 0 AND isImageupload = 0 ";
            $tmpsdata = $db->getMultipleData($qry, true);

            if (count($tmpsdata) > 0) {
                foreach ($tmpsdata as $tmpdata) {

                    $db->query('begin');
                    $url = $tmpdata['Images'];
                    // echo "Image Path. - " . "\t" . $tmpdata['Images'] . "\r\n";

                    $fileuploadname = trim(str_replace('.', '', uniqid("", true))) . ".jpg";
                    $file_name = $fileuploadname;
                    if (file_put_contents('/tmp/' . $file_name, file_get_contents($url))) {
                        $imgdownloaded = true;
                    } else {
                        $imgdownloaded = false;
                        echo "File downloading failed. - " . "\t" . $tmpdata['sno'] . "\t" . $tmpdata['Images'] . "\r\n";
                    }

                    if ($imgdownloaded === true) {
                        //echo 'imgdownloaded'.$imgdownloaded;continue;
                        $s3upload = new cgoS3FileHandler();
                        $cloudFrontPath = 'products/'; //. $fileuploadname
                        $s3upload->putFileToS3($cloudFrontPath, AWSBUCKETUPLOADS, '/tmp/' . $file_name, $file_name);
                        $cloudFrontPath = AWSBUCKETPATH . "/" . $cloudFrontPath;
                        $data = array(
                            "product_id" => $tmpdata['masterid'],
                            "image_url" => $fileuploadname,
                            "image_thumb_url" => '',
                            "image_type" => 1,
                            "bucket_name" => '',
                            "image_folder" => 'products/'
                        );
                        //print_r($data);
                        $status = $db->perform('finascop_stock_item_images', $data);
                        $db->query("update tmp_master_pro set isImageupload = 1 WHERE tmp_id = {$tmpdata['tmp_id']} and masterid = {$tmpdata['masterid']}");
                    }
                    //unlink('/tmp/' . $file_name); 
                    $db->query('commit');
                }
            } else {
                echo 'No data to import.';
            }
        } catch (Exception $ex) {
            
        } finally {
            $tmpsdata = null;
        }
        //return self::$tmpsdata;
    }

}

//ImageUpload::imageBulkUpload();
ImageUpload::imageLinkUpload();
?>