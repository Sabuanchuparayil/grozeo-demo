<?php

global $db;
global $label;

function Configfunction($arr, $parent) {
    
}

switch ($op) {
    case 'uploadcsvFile':
        //print_r($_REQUEST);
        $table = $_POST['csv_table'];
        $file = $_FILES['excel_file']['tmp_name'];
        $newPath = str_replace('tmp', 'dev/shm', $file);
        copy($file, $newPath);
        $row = 0;
        $csvData = array();
        if (($handle = fopen($newPath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                $csvData[$row] = $data;

                $num = count($data);
                $row++;
            }
            fclose($handle);
        }
        //print_r($csvData);
        switch ($table) {
            case 'country':
                foreach ($csvData as $key => $value) {
                    $country_name = trim($value[0]);
                    if ($key > 0) {
                        if (!empty($country_name)) {
                            $dupcheck = $db->getItemFromDB("select count(country_id) from finascop_country where country_name='{$country_name}'");
                            if ($dupcheck < 1) {
                                $countryData['country_name'] = $country_name;
                                $countryData['status'] = 1;
                                $countryData['created_on'] = date("Y-m-d H:i:s");
                                $countryData['created_by'] = $_SESSION['admin']->Finascop_UserId;

                                $status = $db->perform("finascop_country", $countryData);
                            }
                        }
                    }
                }
                break;
            case 'itemname':
                foreach ($csvData as $key => $value) {
                    $itemName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', addslashes(trim($value[0])));
                    if ($key > 0) {
                        if (!empty($itemName)) {
                            $dupcheck = $db->getItemFromDB("select count(itemname_id) from finascop_stock_itemmastername where item_name='{$itemName}'");
                            if ($dupcheck < 1) {
                                $itemnameData['item_name'] = $itemName;
                                $itemnameData['status'] = 1;
                                $itemnameData['created_on'] = date("Y-m-d H:i:s");
                                $itemnameData['created_by'] = $_SESSION['admin']->Finascop_UserId;
                                ;
                                $status = $db->perform("finascop_stock_itemmastername", $itemnameData);
                            }
                        }
                    }
                }
                break;
            case 'manufacture':
                foreach ($csvData as $key => $value) {

                    $manuName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', addslashes(trim($value[0])));
                    if ($key > 0) {
                        if (!empty($manuName)) {
                            $dupcheck = $db->getItemFromDB("select count(manufacture_id) from mypha_productmanufacture where manufacture_name='{$manuName}'");
                            if ($dupcheck < 1) {
                                $manufData['manufacture_name'] = $manuName;
                                $manufData['status'] = 1;
                                $manufData['created_on'] = date("Y-m-d H:i:s");
                                $manufData['created_by'] = $_SESSION['admin']->Finascop_UserId;
                                ;
                                $status = $db->perform("mypha_productmanufacture", $manufData);
                            }
                        }
                    }
                }
                break;
            case 'unit':
                foreach ($csvData as $key => $value) {
                    $unitName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', addslashes(trim($value[0])));
                    if ($key > 0) {
                        if (!empty($unitName)) {
                            $dupcheck = $db->getItemFromDB("select count(unit_id) from mypha_unit where unit_name='{$unitName}'");
                            if ($dupcheck < 1) {
                                $unitData['unit_name'] = $unitName;
                                $unitData['status'] = 1;
                                $unitData['created_on'] = date("Y-m-d H:i:s");
                                $unitData['created_by'] = $_SESSION['admin']->Finascop_UserId;
                                ;
                                $status = $db->perform("mypha_unit", $unitData);
                            }
                        }
                    }
                }
                break;
            case 'hsn':
                foreach ($csvData as $key => $value) {
                    $hsnName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', addslashes(trim($value[0])));
                    $gst = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', addslashes(trim($value[1])));
                    if ($key > 0) {
                        if (!empty($hsnName)) {
                            $dupcheck = $db->getItemFromDB("select count(hsn_id) from finascop_hsn where hsn_code = '{$hsnName}'");
                            if ($dupcheck < 1) {
                                $hsnData['hsn_code'] = $hsnName;
                                $hsnData['gst_percent'] = $gst;
                                $hsnData['status'] = 1;
                                $hsnData['created_on'] = date("Y-m-d H:i:s");
                                $hsnData['created_by'] = $_SESSION['admin']->Finascop_UserId;
                                ;
                                $status = $db->perform("finascop_hsn", $hsnData);
                            }
                        }
                    }
                }
                break;
            case 'package':
                foreach ($csvData as $key => $value) {
                    $packName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', addslashes(trim($value[0])));
                    if ($key > 0) {
                        if (!empty($packName)) {
                            $dupcheck = $db->getItemFromDB("select count(package_type_id) from mypha_productpackage_type where package_type_name='{$packName}'");
                            if ($dupcheck < 1) {
                                $packData['package_type_name'] = $packName;
                                $packData['status'] = 1;
                                $packData['created_on'] = date("Y-m-d H:i:s");
                                $packData['created_by'] = $_SESSION['admin']->Finascop_UserId;
                                $status = $db->perform("mypha_productpackage_type", $packData);
                            }
                        }
                    }
                }
                break;
            case'brand':
                foreach ($csvData as $key => $value) {
                    $brandName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', addslashes(trim($value[0])));
                    $manufName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', addslashes(trim($value[1])));
                    if ($key > 0) {
                        if (!empty($brandName)) {
                            $dupcheck = $db->getItemFromDB("select count(brand_id) from mypha_productbrands where brand_name='{$brandName}'");
                            if ($dupcheck < 1) {

                                $brandData['manufacture_id'] = $db->getItemFromDB("select manufacture_id from mypha_productmanufacture where manufacture_name='{$manufName}' ");
                                if (empty($brandData['manufacture_id'])) {
                                    $brandData['manufacture_id'] = 0;
                                }
                                $brandData['brand_name'] = $brandName;
                                $brandData['status'] = 1;
                                $brandData['created_on'] = date("Y-m-d H:i:s");
                                $brandData['created_by'] = $_SESSION['admin']->Finascop_UserId;
                                $status = $db->perform("mypha_productbrands", $brandData);
                            }
                        }
                    }
                }
                break;
            case 'businesstype':
                foreach ($csvData as $key => $value) {
                    $btName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', addslashes(trim($value[0])));

                    if ($key > 0) {
                        if (!empty($btName)) {
                            $dupcheck = $db->getItemFromDB("select count(business_type_id) from finascop_business_type where business_type_name='{$btName}'");
                            if ($dupcheck < 1) {
                                $btData['business_type_name'] = $btName;
                                $btData['status'] = 1;
                                $btData['created_on'] = date("Y-m-d H:i:s");
                                $btData['created_by'] = $_SESSION['admin']->Finascop_UserId;
                                $status = $db->perform("finascop_business_type", $btData);
                            }
                        }
                    }
                }
                break;
            case 'parentcategory':
                foreach ($csvData as $key => $value) {
                    $btName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', addslashes(trim($value[0])));

                    $pcName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', addslashes(trim($value[1])));

                    if ($key > 0) {
                        if (!empty($pcName)) {
                            $parent_category_businessType = $db->getItemFromDB("select business_type_id from finascop_business_type where business_type_name='{$btName}' ");
                            $dupcheck = $db->getItemFromDB("select count(parent_category_id) from mypha_productparent_category where parent_category='{$pcName}' AND parent_category_businessType = {$parent_category_businessType}");
                            if ($dupcheck < 1) {
                                $pcData['parent_category_businessType'] = $db->getItemFromDB("select business_type_id from finascop_business_type where business_type_name='{$btName}' ");
                                if (empty($pcData['parent_category_businessType'])) {
                                    $pcData['parent_category_businessType'] = 0;
                                }
                                $pcData['parent_category'] = $pcName;
                                $pcData['isMedicine'] = 0;
                                $pcData['status'] = 1;
                                $pcData['created_on'] = date("Y-m-d H:i:s");
                                $pcData['created_by'] = $_SESSION['admin']->Finascop_UserId;
                                $status = $db->perform("mypha_productparent_category", $pcData);
                            }
                        }
                    }
                }
                break;
            case'category':
                foreach ($csvData as $key => $value) {
                    $categName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', addslashes(trim($value[1])));
                    $pcName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', addslashes(trim($value[0])));
                    if ($key > 0) {
                        if (!empty($categName)) {
                            $parent_category = $db->getItemFromDB("select parent_category_id from mypha_productparent_category where parent_category='{$pcName}' ");
                            $dupcheck = $db->getItemFromDB("select count(category_id) from mypha_productcategory where category_name='{$categName}' and parent_category = {$parent_category}");
                            if ($dupcheck == 0) {

                                $catData['parent_category'] = $db->getItemFromDB("select parent_category_id from mypha_productparent_category where parent_category='{$pcName}' ");
                                if (empty($catData['parent_category'])) {
                                    $catData['parent_category'] = 0;
                                }
                                $catData['category_name'] = $categName;
                                $catData['status'] = 1;
                                $catData['created_on'] = date("Y-m-d H:i:s");
                                $catData['created_by'] = $_SESSION['admin']->Finascop_UserId;
                                $status = $db->perform("mypha_productcategory", $catData);
                            }
                        }
                    }
                }
                break;
            case'subcategory':
                foreach ($csvData as $key => $value) {
                    $subcategName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', addslashes(trim($value[1])));
                    $catName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', addslashes(trim($value[0])));
                    if ($key > 0) {
                        if (!empty($subcategName)) {
                            $main_category = $db->getItemFromDB("select category_id from mypha_productcategory where category_name='{$catName}' ");
                            $dupcheck = $db->getItemFromDB("select count(sub_category_id) from mypha_productsubcategory where sub_category='{$subcategName}' AND main_category = {$main_category}");
                            if ($dupcheck == 0) {

                                $subcatData['main_category'] = $db->getItemFromDB("select category_id from mypha_productcategory where category_name='{$catName}' ");
                                if (empty($subcatData['main_category'])) {
                                    $subcatData['main_category'] = 0;
                                }
                                $subcatData['sub_category'] = $subcategName;
                                $subcatData['status'] = 1;
                                $subcatData['created_on'] = date("Y-m-d H:i:s");
                                $subcatData['created_by'] = $_SESSION['admin']->Finascop_UserId;
                                $status = $db->perform("mypha_productsubcategory", $subcatData);
                            }
                        }
                    }
                }
                break;
        }
        if ($status) {
            echo '{"success":true,"valid":true}';
        } else {
            // var_dup($error);
            echo '{"success":false,"valid":false}';
        }

        break;
}
?>
