<?php
define('AWS_ROOT', '/home/system/awsapi');
require(AWS_ROOT . '/aws-autoloader.php');

use Aws\S3\S3Client;

switch ($op) {
    case 'listadManagement':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'adzone_id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        /*    if (isset($filter)) {
        /*    if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $search .= " AND ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $search .= " AND ({$field[field]} LIKE '{$field['data']['value']}%') ";
                    }
                }
            }
        }*/
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');
                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $search .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        //    if ($field['field'] == 'status') {
                        if ($field['field'] == 'adzone_status') {
                            $fiterItem = ($field['data']['value'] == 'Active') ? 1 : 0;
                            $search .= " and ({$field['field']} = {$fiterItem}) ";
                        } else if ($field['data']['value'] == 'Inactive') {
                            $fiterItem = 0;
                            $search .= " and ({$field['field']} = {$fiterItem}) ";
                        } else {
                            $search .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                        //}
                    }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM app_adzones  {$search}";
        $listQuery = "SELECT adzone_id,adzone_name AS adzone_name,adzone_screen,adzone_mode,adzone_theme,adzone_width,adzone_height,IF((adzone_status=1),'Active','Inactive') AS adzone_status,adzone_type FROM app_adzones {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'saveAdManagement':
        $db->query('begin');
        $data = $_POST['n'];
        if ($data['adzone_mode'] == 'Web')
            $data['adzone_mode'] = 1;
        else
            $data['adzone_mode'] = 2;
        if (!empty($_POST['aws_file_locationtemplate']))
            $data['previewImage'] = $_POST['aws_file_locationtemplate'];
        $data['adzone_theme'] = $db->getItemFromDB("SELECT id FROM theme WHERE title = '{$data['adzone_theme']}'");
        if ($data['adzone_id'] > 0) {
            $data['adzone_updatedOn '] = date('Y-m-d H:i:s');
            $data['adzone_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $adzone_status = $db->perform("app_adzones", $data, 'update', 'adzone_id =' . $data['adzone_id']);
            $lastId = $data['adzone_id'];
        } else {
            unset($data['adzone_id']);
            $data['adzone_cretedOn '] = date('Y-m-d H:i:s');
            $data['adzone_cretedBy'] = $_SESSION['admin']->Finascop_UserId;
            $adzone_status = $db->perform('app_adzones', $data);
            $lastId = $db->insert_id();
        }

        $return_rec = $db->getFromDb("SELECT adzone_id,adzone_name AS adzone_name,adzone_screen,adzone_status,adzone_type,adzone_theme,adzone_width,adzone_height FROM app_adzones WHERE adzone_id  = {$lastId}", true);
        $adzone_status = $db->query('commit');
        if ($adzone_status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'adManagementDetailsView':
        $adzone_id = isset($_POST['adzone_id']) ? intval($_POST['adzone_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($adzone_id || $ID) {

            $data = $db->getFromDB("SELECT adzone_id,adzone_name AS adzone_name,adzone_screen,adzone_status,adzone_type,adzone_theme,adzone_width,adzone_height,adzone_mode,previewImage,
            (SELECT title FROM theme WHERE id = adzone_theme) AS adzone_themeName FROM app_adzones WHERE adzone_id  =" . $adzone_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'admanagement_load':
        $adzone_id = isset($_POST['adzone_id']) ? intval($_POST['adzone_id']) : 0;
        if ($adzone_id) {
            $sql = "SELECT adzone_id,adzone_name AS adzone_name,adzone_screen,adzone_status,adzone_type,adzone_mode,adzone_theme,adzone_width,adzone_height,previewImage FROM app_adzones  WHERE adzone_id= " . $adzone_id;
            $results = $db->getFromDB($sql, true);

            $results['grzBucketName'] = AWSBUCKETNAME;
            $results['accessKey'] = AWSS3ASSETUPLOADACCESSID;
            $results['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
            $results['bucketRegion'] = AWSS3ASSETUPLOADREGION;
            $results['oncompleteurl'] = 'adzone/';
            $results['img_path_db'] = $results['previewImage'];
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;
    case 'screenName':

        $listQuery = "SELECT screen_id,screen_name from app_screens";

        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'layoutName':

        $listQuery = "SELECT layout_type_id,layout_type_name,type_id from app_layouttype";

        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listthemeManagement':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 22;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    } else if ($field['field'] == 'theme_name') {
                            $fiterItem = 0;
                            $search .= " and (title LIKE '%{$field['data']['value']}%') ";
                        } else {
                            $search .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                        }
                        //}
                    }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM theme t LEFT JOIN finascop_branch_group ON store_group_id = StoreGroupId {$search}";
        $listQuery = "SELECT id as theme_id,title AS theme_name,description AS theme_description,
        IF((t.status=1),'Active','Inactive') AS theme_status,CASE WHEN name = title THEN 'Available' ELSE 'Not Available' END AS themeAvailable,store_group_name
         FROM theme t LEFT JOIN finascop_branch_group ON store_group_id = StoreGroupId {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'saveThemeManagement':
        $db->query('begin');

        $data         = $_POST;
        $themeDesigns = json_decode($_POST['themeDesigns']);
        $zipFileName  = basename(urldecode($data['themeFile_Location']));
        $themeName    = pathinfo($zipFileName, PATHINFO_FILENAME);

        // Build base theme data
        $tdata = [
            'title'          => $data['theme_name'],
            'description'    => $data['theme_description'],
            'retailCategory' => $data['retailCategorys'],
            'status'         => $data['theme_status']
        ];
        if (!empty($themeName)) {
            $tdata['name'] = $themeName;
        }

        // Create / Update Theme
        if ($data['theme_id'] > 0) {
            $themeUnique = $db->getItemFromDB("SELECT COUNT(*) FROM theme WHERE title = '{$data['theme_name']}' AND id <> {$data['theme_id']}");
            if ($themeUnique > 0) {
                echo "{success:false, message:'Theme already exists.'}";
                exit;
            }
            $tdata['updatedOn '] = date('Y-m-d H:i:s');
            $tdata['updatedBy']  = $_SESSION['admin']->Finascop_UserId;
            $db->perform("theme", $tdata, 'update', 'id=' . $data['theme_id']);
            $lastId = $data['theme_id'];
        } else {
            $themeUnique = $db->getItemFromDB("SELECT COUNT(*) FROM theme WHERE title = '{$data['theme_name']}'");
            if ($themeUnique > 0) {
                echo "{success:false, message:'Theme already exists.'}";
                exit;
            }
            $tdata['createdOn '] = date('Y-m-d H:i:s');
            $tdata['createdBy']  = $_SESSION['admin']->Finascop_UserId;
            $db->perform('theme', $tdata);
            $lastId = $db->insert_id();
        }

        // Fetch default theme once
        $defaultThemeId = $db->getItemFromDB("SELECT id FROM theme WHERE title LIKE '%Grozeo%' LIMIT 1");

        // Single loop for 1–10
        for ($type = 1; $type <= 10; $type++) {
            $designValue = isset($themeDesigns[$type - 1]) ? $themeDesigns[$type - 1] : '';

            // Does this type already exist in current theme?
            $designExist = $db->getItemFromDB("SELECT id FROM theme_image WHERE type = {$type} AND themeId = {$lastId}");

            if (!empty($designValue)) {
                // Insert/Update with uploaded image
                $themImage = [
                    'themeId' => $lastId,
                    'type'    => $type,
                    'image'   => $designValue
                ];
                if ($designExist > 0) {
                    $themImage['updatedOn '] = date('Y-m-d H:i:s');
                    $themImage['updatedBy']  = $_SESSION['admin']->Finascop_UserId;
                    $db->perform('theme_image', $themImage, 'update', "id={$designExist}");
                } else {
                    $themImage['createdOn '] = date('Y-m-d H:i:s');
                    $themImage['createdBy']  = $_SESSION['admin']->Finascop_UserId;
                    $db->perform('theme_image', $themImage);
                }
            } elseif ($type >= 2 && $type <= 10 && empty($designExist)) {
                // No upload → try to copy from default theme
                $defaultImage = $db->getItemFromDB("SELECT image FROM theme_image WHERE type = {$type} AND themeId = {$defaultThemeId}");
                if (!empty($defaultImage)) {
                    $dthemImage = [
                        'themeId'   => $lastId,
                        'type'      => $type,
                        'image'     => $defaultImage,
                        'createdOn' => date('Y-m-d H:i:s'),
                        'createdBy' => $_SESSION['admin']->Finascop_UserId,
                    ];
                    $db->perform('theme_image', $dthemImage);
                }
            }
        }

        // Fetch updated theme
        $return_rec = $db->getFromDb(
            "SELECT id AS theme_id, title AS theme_name, description AS theme_description, status AS theme_status 
     FROM theme WHERE id = {$lastId}",
            true
        );

        $theme_status = $db->query('commit');

        if ($theme_status == 1) {
            echo "{success:true,valid:true,message:'Details saved',data:" . json_encode($return_rec) . "}";
        } else {
            echo "{success:false,valid:false,message:'Error while saving.'}";
        }

        break;

    case 'themeManagementDetailsView':
        $theme_id = isset($_POST['theme_id']) ? intval($_POST['theme_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($theme_id || $ID) {

            $data = $db->getFromDB("SELECT name,id AS theme_id,title AS theme_name,description AS theme_description,status AS theme_status FROM theme WHERE id  =" . $theme_id, true);


            $bucket = AWSBUCKETNAME;
            $region = AWSS3ASSETUPLOADREGION; // e.g., ap-south-1
            $themeName = $data['name'] ?? ''; // from URL: ?theme=darkmode
            $zipContent = "No Files uploaded";
            if (!empty($themeName)) {
                $zipContent = "";
                // AWS SDK setup
                $s3 = new S3Client([
                    'region'  => $region,
                    'version' => 'latest',
                    'credentials' => [
                        'key'    => AWSS3ASSETUPLOADACCESSID,
                        'secret' => AWSS3ASSETUPLOADSECRETKEY,
                    ]
                ]);

                $prefix = "themes/{$themeName}/"; // Folder prefix
                $objects = $s3->listObjectsV2([
                    'Bucket' => $bucket,
                    'Prefix' => $prefix
                ]);

                $zipContent .= "<h2>Files in <code>{$themeName}</code></h2>";
                $zipContent .= "<ul>";

                if (isset($objects['Contents'])) {
                    foreach ($objects['Contents'] as $object) {
                        $key = $object['Key'];
                        if ($key === $prefix) continue; // skip the folder itself

                        $url = $s3->getObjectUrl($bucket, $key);
                        $zipContent .= "<li><a href='{$url}' target='_blank'>" . basename($key) . "</a></li>";
                    }
                } else {
                    $zipContent .= "<li>No files found in this theme.</li>";
                }

                $zipContent .= "</ul>";
            }



            $data['zipContent'] = $zipContent;
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'themeManagement_load':
        $theme_id = isset($_POST['theme_id']) ? intval($_POST['theme_id']) : 0;
        if ($theme_id) {
            $sql = "SELECT id AS theme_id,title AS theme_name,description AS theme_description,status AS theme_status FROM theme WHERE id = " . $theme_id;
            $results = $db->getFromDB($sql, true);
            if (!$results) {
                echo '{"success":true,"data":[]}';
            } else {
                echo '{"success":true, "data":',
                json_encode($results),
                '}';
            }
        }
        break;
    case 'themeName':

        $listQuery = "SELECT id AS theme_id,title AS theme_name from theme where status = 1";

        $data = $db->getMultipleData($listQuery, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'themeRetailCategories':
        $typeAhead = '';
        $qry = "SELECT business_type_id, business_type_name FROM finascop_business_type WHERE status = 1   $typeAhead ORDER BY business_type_name ASC";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'get_img_s3_details':
        $isCommonPage = ($_POST['isCommonPage'] > 0 ? $_POST['isCommonPage'] : 0);
        $themeId = $_POST['themeId'];
        if ($isCommonPage == 1)
            $themeId = $db->getItemFromDB("SELECT id FROM theme WHERE title LIKE '%{$themeId}%' LIMIT 1");

        $sql = "SELECT id AS theme_id,title AS theme_name,description AS theme_description,status AS theme_status,retailCategory AS retailCategoryIds FROM theme WHERE id = {$themeId}";
        $data = $db->getFromDB($sql, true);
        if (!empty($data['retailCategoryIds']))
            $data['retailCategorys'] = $db->getItemFromDB("SELECT GROUP_CONCAT(business_type_name) FROM finascop_business_type WHERE business_type_id IN ({$data['retailCategoryIds']});");
        $data['homePage_awsLocation'] = $db->getItemFromDB("SELECT image FROM theme_image where `themeId`= {$themeId} AND type = 1");
        $data['searchPage_awsLocation'] = $db->getItemFromDB("SELECT image FROM theme_image where `themeId`= {$themeId} AND type = 2");
        $data['prdctPage_awsLocation'] = $db->getItemFromDB("SELECT image FROM theme_image where `themeId`= {$themeId} AND type = 3");
        $data['cartPage_awsLocation'] = $db->getItemFromDB("SELECT image FROM theme_image where `themeId`= {$themeId} AND type = 4");
        $data['checkOutPage_awsLocation'] = $db->getItemFromDB("SELECT image FROM theme_image where `themeId`= {$themeId} AND type = 5");
        $data['itemView_awsLocation'] = $db->getItemFromDB("SELECT image FROM theme_image where `themeId`= {$themeId} AND type = 6");
        $data['payment_awsLocation'] = $db->getItemFromDB("SELECT image FROM theme_image where `themeId`= {$themeId} AND type = 7");
        $data['walletPage_awsLocation'] = $db->getItemFromDB("SELECT image FROM theme_image where `themeId`= {$themeId} AND type = 8");
        $data['orderPage_awsLocation'] = $db->getItemFromDB("SELECT image FROM theme_image where `themeId`= {$themeId} AND type = 9");
        $data['orderDetails_awsLocation'] = $db->getItemFromDB("SELECT image FROM theme_image where `themeId`= {$themeId} AND type = 10");
        $data['grzBucketName'] = AWSBUCKETNAME;
        $data['accessKey'] = AWSS3ASSETUPLOADACCESSID;
        $data['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
        $data['bucketRegion'] = AWSS3ASSETUPLOADREGION;
        $data['oncompleteurl'] = 'themes/';

        echo "{success : true,'data':" . json_encode($data) . "}";
        break;
    case 'getDesignImages':
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            $data = '';
            echo '{"success":true,"data":' . json_encode($data) . '}';
        }

        break;
    case 'saveCommonThemeDesigns':
        $db->query('begin');
        $data = $_POST;
        $themeName = $_POST['themeName'];
        $themeDesigns = json_decode($_POST['themeDesigns']);
        $themeId = $db->getItemFromDB("SELECT id FROM theme WHERE title LIKE '%{$themeName}%' LIMIT 1");

        foreach ($themeDesigns as $designKey => $designValue) {
            $themImage['themeId'] = $themeId;
            $themImage['type'] = $designKey + 1;
            $themImage['image'] = $designValue;
            if (!empty($designValue)) {
                $designExist = $db->getItemFromDB("SELECT id FROM theme_image WHERE type = {$themImage['type']} AND themeId = {$themImage['themeId']}");
                if ($designExist > 0) {
                    $themImage['updatedOn '] = date('Y-m-d H:i:s');
                    $themImage['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
                    $theme_status = $db->perform('theme_image', $themImage, 'update', " id = {$designExist}");
                } else {
                    $themImage['createdOn '] = date('Y-m-d H:i:s');
                    $themImage['createdBy'] = $_SESSION['admin']->Finascop_UserId;
                    $theme_status = $db->perform('theme_image', $themImage);
                }
            }
        }
        $return_rec = $db->getFromDb("SELECT id AS theme_id,title AS theme_name,description AS theme_description,status AS theme_status FROM theme WHERE id  = {$themeId}", true);
        $theme_status = $db->query('commit');
        if ($theme_status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
}
