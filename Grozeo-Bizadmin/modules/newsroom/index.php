<?php
define('AWS_ROOT', '/home/system/awsapi');
require(AWS_ROOT . '/aws-autoloader.php');

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

switch ($op) {
    case 'saveNewsRoom':
        $supportdb->query('begin');

        $data = array(
            "id" => $_POST['newsRoomId'],
            "heading" => $_POST['newsRoomHeading'],
            "type" => $_POST['newsRoomType'],
            "breif" => $_POST['newsRoomBreif'],
            "mode" => $_POST['newsRoomMode'],
            "details" => (!empty($_POST['newsRoomExternalLink']) ? $_POST['newsRoomExternalLink'] : $_POST['newsRoomDetails']),
            "isGlobal" => ($_POST['isGlobal'] == 'true' ? 1 : 0),
            "countryId" => ($_POST['newsRoomCountry'] > 0 ? $_POST['newsRoomCountry'] : 0),
            "displayImaage" => $_POST['aws_file_locationtemplate'],
            "isDefault" => ($_POST['isDefault'] == true ? 1 : 0),
            "status" => $_POST['newsRoomStatus'],
        );

        $articleId = $data['id'];
        $heading = $data['heading'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $heading = addslashes($heading);
        if ($data['id'] > 0) {

            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $userid;


            $packagenameUnique = $supportdb->getItemFromDB("SELECT COUNT(*) from newsroom WHERE heading ='{$heading}' AND id !='{$articleId}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Article already exists.'}";
                exit;
            } else {
                $status = $supportdb->perform("newsroom", $data, 'update', 'id =' . $data['id']);
                $lastId = $data['id'];
            }
        } else {

            $packagenameUnique = $supportdb->getItemFromDB("SELECT COUNT(*) from newsroom WHERE heading ='{$heading}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Article already exists.'}";
                exit;
            } else {
                unset($data['id']);
                $data['createdOn'] = date('Y-m-d H:i:s');
                $data['createdBy'] = $userid;
                $status = $supportdb->perform('newsroom', $data);
                $lastId = $supportdb->insert_id();
            }
        }

        $return_rec = $supportdb->getFromDb("SELECT newsroom.id AS newsRoomId,newsroom.heading AS newsRoomHeading,IF((newsroom.status=1),'Active','Inactive') AS statusName  FROM newsroom WHERE id = {$lastId}", true);
        $status = $supportdb->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listNewsRoom':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'newsroom.id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['news_id', 'news_title', 'news_status', 'news_created_on', 'news_type'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM newsroom  {$search}";
        $listQuery = "SELECT newsroom.id AS newsRoomId,newsroom.heading AS newsRoomHeading,IF((newsroom.status=1),'Active','Inactive') AS statusName,
        CASE WHEN type = 1 THEN 'News' WHEN type = 2 THEN 'Event' WHEN type = 3 THEN 'Press Release' END AS typeName,
        CASE WHEN mode = 1 THEN 'Internal' WHEN mode = 2 THEN 'External' END AS modeName,startDate,endDate,isGlobal,isDefault,
        IF((newsroom.isGlobal=1),'Global','-') AS marketType,IF((newsroom.isDefault=1),'Main','-') AS mainArticle
          FROM newsroom {$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $supportdb->printGridJson($countQuery, $listQuery);
        break;
    case 'nrDetailsView':
        $articleId = isset($_POST['newsRoomId']) ? intval($_POST['newsRoomId']) : 0;
        if ($articleId) {
            $data = $supportdb->getFromDB("SELECT newsroom.id AS newsRoomId,newsroom.heading AS newsRoomHeading,IF((newsroom.status=1),'Active','Inactive') AS statusName,newsroom.status AS newsRoomStatus,
        CASE WHEN type = 1 THEN 'News' WHEN type = 2 THEN 'Event' WHEN type = 3 THEN 'Press Release' END AS typeName,type AS newsRoomType,mode AS newsRoomMode,
        CASE WHEN mode = 1 THEN 'Internal' WHEN mode = 2 THEN 'External' END AS modeName,startDate,endDate,isGlobal,isDefault,countryId AS newsRoomCountry,displayImaage,
        IF((newsroom.isGlobal=1),'Global','-') AS marketType,IF((newsroom.isDefault=1),'Main','-') AS mainArticle,breif AS newsRoomBreif,details AS newsRoomDetails FROM newsroom WHERE id = " . $articleId, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'nr_form_load':
        $articleId = isset($_POST['newsRoomId']) ? intval($_POST['newsRoomId']) : 0;
        if ($articleId) {
            $sql = "SELECT newsroom.id AS newsRoomId,newsroom.heading AS newsRoomHeading,IF((newsroom.status=1),'Active','Inactive') AS statusName,newsroom.status AS newsRoomStatus,
        CASE WHEN type = 1 THEN 'News' WHEN type = 2 THEN 'Event' WHEN type = 3 THEN 'Press Release' END AS typeName,type AS newsRoomType,mode AS newsRoomMode,
        CASE WHEN mode = 1 THEN 'Internal' WHEN mode = 2 THEN 'External' END AS modeName,startDate,endDate,isGlobal,isDefault,countryId AS newsRoomCountry,displayImaage AS aws_file_locationtemplate,
        IF((newsroom.isGlobal=1),'Global','-') AS marketType,IF((newsroom.isDefault=1),'Main','-') AS mainArticle,breif AS newsRoomBreif,details AS newsRoomDetails FROM newsroom WHERE id =" . $articleId;
            $results = $supportdb->getFromDB($sql, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;
    case 'get_img_s3_details':
        $rid = $_POST['rid'];
        $data['grzBucketName'] = AWSBUCKETNAME;
        $data['accessKey'] = AWSS3ASSETUPLOADACCESSID;
        $data['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
        $data['bucketRegion'] = AWSS3ASSETUPLOADREGION;
        $data['oncompleteurl'] = 'newsroom/';
        if ($rid) {
            $data['img_path_db'] = $supportdb->getItemFromDB("select displayImaage from newsroom where `id`= {$rid}");
        } else {
            $data['img_path_db'] = '';
        }
        echo "{success : true,'data':" . json_encode($data) . "}";
        break;
    case 'getApplicableCountry':

        $qry = "SELECT `country_id`,`country_name` FROM `retaline_country` WHERE `status` = '1' order by country_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getImage':
        $newsRoomId = $_POST['newsRoomId'];
        $main_img = $_POST['main_img'];
        $qry = "select id,displayImaage from newsroom where `id`= {$newsRoomId}";
        $data = $supportdb->getMultipleData($qry, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            $data = '';
            echo '{"success":true,"data":' . json_encode($data) . '}';
        }

        break;
    case 'saveHelpInfo':
        $supportdb->query('begin');

        $data = array(
            "id" => $_POST['helpInfoId'],
            "title" => $_POST['helpInfoHeading'],
            "type" => 1,
            "status" => 1,
        );

        $articleId = $data['id'];
        $heading = $data['heading'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $heading = addslashes($heading);
        if ($data['id'] > 0) {

            $data['updatedOn'] = date('Y-m-d H:i:s');
            $data['updatedBy'] = $userid;


            $packagenameUnique = $supportdb->getItemFromDB("SELECT COUNT(*) from help_info WHERE title ='{$heading}' AND id !='{$articleId}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Details already exists.'}";
                exit;
            } else {
                $status = $supportdb->perform("help_info", $data, 'update', 'id =' . $data['id']);
                $lastId = $data['id'];
            }
        } else {

            $packagenameUnique = $supportdb->getItemFromDB("SELECT COUNT(*) from help_info WHERE title ='{$heading}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Details already exists.'}";
                exit;
            } else {
                unset($data['id']);
                $data['createdOn'] = date('Y-m-d H:i:s');
                $data['createdBy'] = $userid;
                $status = $supportdb->perform('help_info', $data);
                $lastId = $supportdb->insert_id();
            }
        }
        $status = $supportdb->query('commit');

        // AWS Configuration
        $bucketName = AWSBUCKETNAME;
        $region = AWSS3ASSETUPLOADREGION; // e.g., 'us-east-1'
        $accessKey = AWSS3ASSETUPLOADACCESSID;
        $secretKey = AWSS3ASSETUPLOADSECRETKEY;

        // HTML Content and ID
        $id = $lastId; // Assume this is passed via a form
        $htmlContent = $_POST['helpInfoDetails']; // Assume this is passed via a form

        // File name to save in S3
        $fileName = "html_files/$id.html";

        try {
            // Initialize S3 client
            $s3 = new S3Client([
                'region' => $region,
                'version' => 'latest',
                'credentials' => [
                    'key' => $accessKey,
                    'secret' => $secretKey,
                ],
            ]);

            // Upload HTML file
            $result = $s3->putObject([
                'Bucket' => $bucketName,
                'Key' => $fileName,
                'Body' => $htmlContent,
                'ContentType' => 'text/html',
                'ACL' => 'public-read', // Optional: Make file publicly accessible
            ]);

            // Success message with URL
            //echo "File successfully uploaded: " . $result['ObjectURL'] . "\n";
        } catch (AwsException $e) {
            // Error handling
            echo "Error uploading file: " . $e->getMessage() . "\n";
        }


        $return_rec = $supportdb->getFromDb("SELECT help_info.id AS helpInfoId,help_info.title AS helpInfoHeading,IF((help_info.status=1),'Active','Inactive') AS statusName  FROM help_info WHERE id = {$lastId}", true);

        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listHelpInfo':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'help_info.id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['news_id', 'news_title', 'news_status', 'news_created_on', 'news_type'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM help_info  {$search}";
        $listQuery = "SELECT help_info.id AS helpInfoId,help_info.title AS helpInfoHeading,IF((help_info.status=1),'Active','Inactive') AS statusName 
              FROM help_info {$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $supportdb->printGridJson($countQuery, $listQuery);
        break;
    case 'hiDetailsView':
        $articleId = isset($_POST['helpInfoId']) ? intval($_POST['helpInfoId']) : 0;
        if ($articleId) {
            $data = $supportdb->getFromDB("SELECT help_info.id AS helpInfoId,help_info.title AS helpInfoHeading,IF((help_info.status=1),'Active','Inactive') AS statusName 
              FROM help_info WHERE id = " . $articleId, true);
            $data['displayContent'] = "https://" . AWSBUCKETNAME . ".s3.ap-southeast-1.amazonaws.com/html_files/{$articleId}.html";
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'hi_form_load':
        $articleId = isset($_POST['helpInfoId']) ? intval($_POST['helpInfoId']) : 0;
        if ($articleId) {
            $sql = "SELECT help_info.id AS helpInfoId,help_info.title AS helpInfoHeading,IF((help_info.status=1),'Active','Inactive') AS statusName 
              FROM help_info WHERE id =" . $articleId;
            $results = $supportdb->getFromDB($sql, true);

            // AWS Configuration
            $bucketName = AWSBUCKETNAME;
            $region = AWSS3ASSETUPLOADREGION; // e.g., 'us-east-1'
            $accessKey = AWSS3ASSETUPLOADACCESSID;
            $secretKey = AWSS3ASSETUPLOADSECRETKEY;

            // ID of the file to edit
            $id = $articleId; // Assume this is passed via a query parameter
            $fileName = "html_files/$id.html";

            try {
                // Initialize S3 client
                $s3 = new S3Client([
                    'region' => $region,
                    'version' => 'latest',
                    'credentials' => [
                        'key' => $accessKey,
                        'secret' => $secretKey,
                    ],
                ]);

                // Retrieve the file content
                $result = $s3->getObject([
                    'Bucket' => $bucketName,
                    'Key' => $fileName,
                ]);
                //print_r($result['Body']->getContents());
                // File content
                $htmlContent = $result['Body']->getContents();
            } catch (AwsException $e) {
                // Error handling
                echo "Error retrieving file: " . $e->getMessage();
                exit;
            }
            $results['helpInfoDetails'] = $htmlContent;
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;
}
