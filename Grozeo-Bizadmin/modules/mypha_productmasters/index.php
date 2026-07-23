<?php

$userid = $_SESSION['admin']->Finascop_UserId;

switch ($op) {
    case 'listCategory':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'category_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'category_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['pm_id', 'pm_name', 'pm_code', 'pm_type', 'pm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }else if ($field['field'] == 'parent_category') {
                    $searchitem .= " and (ms.parent_category LIKE '{$field[data][value]}%') ";
                } else if ($field['field'] == 'cat_name') {
                    $searchitem .= " and (category_name LIKE '{$field[data][value]}%') ";
                } else if ($field['field'] == 'isHome') {
                    if ($field[data][value] == 'Yes') {
                        $searchitem .= " and (mc.isHome = 1) ";
                    } else if ($field[data][value] == 'Yes,No') {
                        $searchitem .= " and (mc.isHome IN(1,0)) ";
                    } else {
                        $searchitem .= " and (mc.isHome = 0) ";
                    }
                } else if ($field['field'] == 'isInCategory') {
                    if ($field[data][value] == 'Yes') {
                        $searchitem .= " and (mc.isInCategory = 1) ";
                    } else if ($field[data][value] == 'Yes,No') {
                        $searchitem .= " and (mc.isInCategory IN(1,0)) ";
                    } else {
                        $searchitem .= " and (mc.isInCategory = 0) ";
                    }
                } else if ($field['field'] == 'hasImage') {
                    if ($field[data][value] == 'Yes') {
                        $searchitem .= " and (mc.image_url <> '') ";
                    } else if ($field[data][value] == 'Yes,No') {
                        $searchitem .= "  ";
                    } else {
                        $searchitem .= " and (mc.image_url = '') ";
                    }
                } else {
                    $checkComa = strstr($field['data']['value'], ',');

                    if ($checkComa != '') {
                        $fiterItem = $field['data']['value'];
                        $fiterItem = str_replace(',', "','", $fiterItem);
                        $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                    } else {
                        $searchitem .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                    }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_productcategory mc 
        INNER JOIN mypha_productparent_category ms ON ms.parent_category_id=mc.parent_category 
        INNER JOIN finascop_business_type ON business_type_id = parent_category_businessType  {$search} {$searchitem}";
        $listQuery = "SELECT business_type_name,category_id,(ms.parent_category),category_name AS cat_name,(mc.status) AS status,IF(mc.image_url IS NOT NULL,'present','nil') AS image,IF((mc.isHome=1),'Yes','No') AS isHome,
            IF((mc.isInCategory=1),'Yes','No') AS isInCategory, mc.image_url AS image_url,if((mc.image_url = '' || mc.image_url IS NULL),'No','Yes') AS hasImage,
            IF((mc.status='1'),'Active','Inactive') AS status  
     FROM mypha_productcategory mc 
     INNER JOIN mypha_productparent_category ms ON ms.parent_category_id=mc.parent_category 
     INNER JOIN finascop_business_type ON business_type_id = parent_category_businessType "
            . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'cate_form_load':
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        if ($category_id) {
            $sql = "SELECT category_id,category_name AS cat_name,isHome as mc_isHome,isInCategory as mc_isInCategory,parent_category,status as statuscat FROM mypha_productcategory  WHERE category_id = " . $category_id;
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
    case 'CatdetailsView':
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($category_id || $ID) {

            $data = $db->getFromDB("SELECT category_id,category_name AS cat_name,IF((isHome=1),'Yes','No') AS mc_isHome,IF((isInCategory=1),'Yes','No') AS mc_isInCategory,parent_category,status,IF(image_url IS NOT NULL,'present','nil') AS image,"
                . "if((mc.image_url = '' || mc.image_url IS NULL),'No','Yes') AS hasImage,(SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = mc.parent_category) as parent_category_name,image_url FROM mypha_productcategory mc WHERE category_id =" . $category_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'saveCategory':


        $db->query('begin');
        if ($_POST['mc_isHome'] == 'true') {
            $isHome = 1;
        } else {
            $isHome = 0;
        }
        if ($_POST['mc_isInCategory'] == 'true') {
            $isInCategory = 1;
        } else {
            $isInCategory = 0;
        }
        $data = array(
            "category_id" => $_POST['id'],
            "category_name" => $_POST['name'],
            "parent_category" => $_POST['parent_category'],
            "status" => $_POST['status'],
            "isHome" => $isHome,
            "isInCategory" => $isInCategory
        );

        $category_id = $data['category_id'];
        $category_name = $data['category_name'];
        $parent_category = $data['parent_category'];
        $status = $data['status'];
        $category_name = addslashes($category_name);

        if (empty($_POST['id'])) {


            $CategoryUnique = $db->getItemSafe("SELECT COUNT(*) from mypha_productcategory WHERE category_name ='{$category_name}' AND parent_category = ?", "s", [$_POST['parent_category']]);
            if ($CategoryUnique > 0) {
                echo "{success: false, message:'This  Category already existing.'}";
                exit;
            } else {
                unset($data['category_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mypha_productcategory', $data);
                $lastId = $db->insert_id();
            }
        } else {


            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;
            $CategoryUnique = $db->getItemSafe("SELECT COUNT(*) from mypha_productcategory WHERE category_name ='{$category_name}' AND parent_category = ? AND category_id!='{$category_id}' ", "s", [$_POST['parent_category']]);
            if ($CategoryUnique > 0) {
                echo "{success: false, message:'This  Category already existing.'}";
                exit;
            } else {
                $status = $db->perform("mypha_productcategory", $data, 'update', 'category_id =' . $data['category_id']);
                $lastId = $data['category_id'];
            }
        }

        $return_rec = $db->getFromDb("SELECT category_id,category_name AS cat_name,parent_category,status,IF(image_url IS NOT NULL,'present','nil') AS image,(SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = mc.parent_category) as parent_category_name FROM mypha_productcategory mc WHERE category_id = {$lastId}", true);

        $status = $db->query('commit');

        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;

    case 'listParentCategory':

        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'parent_category_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['pm_id', 'pm_name', 'pm_code', 'pm_type', 'pm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    } else if ($field['field'] == 'isHome') {
                            if ($field[data][value] == 'Yes') {
                                $searchitem .= " and (isHome = 1) ";
                            } else if ($field[data][value] == 'Yes,No') {
                                $searchitem .= " and (isHome IN(1,0)) ";
                            } else {
                                $searchitem .= " and (isHome = 0) ";
                            }
                        } else if ($field['field'] == 'isInCategory') {
                            if ($field[data][value] == 'Yes') {
                                $searchitem .= " and (isInCategory = 1) ";
                            } else if ($field[data][value] == 'Yes,No') {
                                $searchitem .= " and (isInCategory IN(1,0)) ";
                            } else {
                                $searchitem .= " and (isInCategory = 0) ";
                            }
                        } else if ($field['field'] == 'hasImage') {
                            if ($field[data][value] == 'Yes') {
                                $searchitem .= " and (image_url <> '') ";
                            } else if ($field[data][value] == 'Yes,No') {
                                $searchitem .= "  ";
                            } else {
                                $searchitem .= " and (image_url = '') ";
                            }
                        }

                        break;
                    default:
                        if ($field['field'] == 'parent_category_businessType') {
                            $fiterItem = $db->getItemFromDB("SELECT COALESCE(GROUP_CONCAT(business_type_id),0) FROM finascop_business_type WHERE business_type_name LIKE '{$field['data']['value']}%'");
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {

                            $checkComa = strstr($field['data']['value'], ',');

                            if ($checkComa != '') {
                                $fiterItem = $field['data']['value'];
                                $fiterItem = str_replace(',', "','", $fiterItem);
                                $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                            } else {
                                $searchitem .= " and ({$field['field']} LIKE '{$field[data][value]}%') ";
                            }
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_productparent_category   {$search} {$searchitem}";

        $listQuery = "SELECT parent_category_id,parent_category,IF((status=1),'Active','Inactive')AS status,IF((isHome=1),'Yes','No') AS isHome,IF((isInCategory=1),'Yes','No') AS isInCategory,
          (SELECT business_type_name FROM finascop_business_type WHERE business_type_id = parent_category_businessType) AS  parent_category_businessType,if(image_url = '','No','Yes') AS hasImage  FROM mypha_productparent_category
        " . "{$search} {$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        //        echo $listQuery;
        //        exit();

        $db->printGridJson($countQuery, $listQuery);
        // exit;
        break;

    case 'parent_category_form_load':
        $parent_category_id = isset($_POST['parent_category_id']) ? intval($_POST['parent_category_id']) : 0;
        if ($parent_category_id) {
            $sql = "SELECT parent_category_id AS textfieldMasterParentCategoryId,isHome as pc_isHome,isInCategory as pc_isInCategory,parent_category AS textfieldMasterParentCategory,status AS comboMasterParentCategorystatus,(SELECT business_type_name FROM finascop_business_type WHERE business_type_id = parent_category_businessType) as parent_category_busType,parent_category_businessType  FROM mypha_productparent_category  WHERE parent_category_id= " . $parent_category_id;
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

    case 'ParentCategorydetailsView':
        $parent_category_id = isset($_POST['parent_category_id']) ? intval($_POST['parent_category_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($parent_category_id || $ID) {

            $data = $db->getFromDB("SELECT parent_category_id,IF((isHome=1),'Yes','No') AS pc_isHome,IF((isInCategory=1),'Yes','No') AS pc_isInCategory,parent_category,status,parent_category_businessType,(SELECT business_type_name FROM finascop_business_type WHERE business_type_id = parent_category_businessType) as business_type_name,image_url FROM mypha_productparent_category WHERE parent_category_id =" . $parent_category_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'saveParentCategory':
        $db->query('begin');

        if ($_POST['pc_isHome'] == 'true') {
            $isHome = 1;
        } else {
            $isHome = 0;
        }
        if ($_POST['pc_isInCategory'] == 'true') {
            $isInCategory = 1;
        } else {
            $isInCategory = 0;
        }

        $data = array(
            "parent_category_id" => $_POST['id'],
            "parent_category" => $_POST['name'],
            "parent_category_businessType" => $_POST['parent_category_businessType'],
            "status" => $_POST['status'],
            "isHome" => $isHome,
            "isInCategory" => $isInCategory
        );

        $parent_category = rtrim(ltrim($data['parent_category'], " "), " ");
        $status = $data['status'];
        $parent_category_id = $data['parent_category_id'];
        $parent_category = addslashes($parent_category);
        if (empty($_POST['id'])) {

            $parentCategoryUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_productparent_category WHERE parent_category ='{$parent_category}'  ");
            if ($parentCategoryUnique > 0) {
                echo "{success: false, message:'This Parent Category already existing.'}";
                exit;
            } else {
                unset($data['parent_category_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mypha_productparent_category', $data);
                $lastId = $db->insert_id();
            }
        } else {


            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;

            $parentCategoryUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_productparent_category WHERE parent_category ='{$parent_category}' AND parent_category_id!='{$parent_category_id}' ");
            if ($parentCategoryUnique > 0) {
                echo "{success: false, message:'Parent Category already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_productparent_category", $data, 'update', 'parent_category_id =' . $data['parent_category_id']);
                $lastId = $data['parent_category_id'];
            }
        }
        $return_rec = $db->getFromDb("SELECT parent_category_id,parent_category,status FROM mypha_productparent_category WHERE parent_category_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listSubcategory':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 12;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'sub_category_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['pm_id', 'pm_name', 'pm_code', 'pm_type', 'pm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    } else if ($field['field'] == 'isInCategory') {
                    if ($field[data][value] == 'Yes') {
                        $searchitem .= " and (sub.isInCategory = 1) ";
                    } else if ($field[data][value] == 'Yes,No') {
                        $searchitem .= " and (sub.isInCategory IN(1,0)) ";
                    } else {
                        $searchitem .= " and (sub.isInCategory = 0) ";
                    }
                } else if ($field['field'] == 'hasImage') {
                    if ($field[data][value] == 'Yes') {
                        $searchitem .= " and (sub_category_image <> '') ";
                    } else if ($field[data][value] == 'Yes,No') {
                        $searchitem .= "  ";
                    } else {
                        $searchitem .= " and (sub_category_image = '') ";
                    }
                } else if ($field['field'] == 'sbc_isCompositionDelator') {
                    if ($field[data][value] == 'Yes') {
                        $searchitem .= " and (isCompositionDelator = 1) ";
                    } else if ($field[data][value] == 'Yes,No') {
                        $searchitem .= "  and (isCompositionDelator IN (1,0)) ";
                    } else {
                        $searchitem .= " and (isCompositionDelator = 0) ";
                    }
                }else if ($field['field'] == 'sbc_isNonGstRetailer') {
                    if ($field[data][value] == 'Yes') {
                        $searchitem .= " and (isNonGstRetailer = 1) ";
                    } else if ($field[data][value] == 'Yes,No') {
                        $searchitem .= "  and (isNonGstRetailer IN (1,0)) ";
                    } else {
                        $searchitem .= " and (isNonGstRetailer = 0) ";
                    }
                }else if ($field['field'] == 'sbc_isPerishable') {
                    if ($field[data][value] == 'Yes') {
                        $searchitem .= " and (isPerishable = 1) ";
                    } else if ($field[data][value] == 'Yes,No') {
                        $searchitem .= "  and (isPerishable IN (1,0)) ";
                    } else {
                        $searchitem .= " and (isPerishable = 0) ";
                    }
                }else if ($field['field'] == 'sbc_hasRestaurantService') {
                    if ($field[data][value] == 'Yes') {
                        $searchitem .= " and (hasRestaurantService = 1) ";
                    } else if ($field[data][value] == 'Yes,No') {
                        $searchitem .= "  and (hasRestaurantService IN (1,0)) ";
                    } else {
                        $searchitem .= " and (hasRestaurantService = 0) ";
                    }
                }else if ($field['field'] == 'sbc_isProductDetail') {
                    if ($field[data][value] == 'Yes') {
                        $searchitem .= " and (isProductDetail = 1) ";
                    } else if ($field[data][value] == 'Yes,No') {
                        $searchitem .= "  and (isProductDetail IN (1,0)) ";
                    } else {
                        $searchitem .= " and (isProductDetail = 0) ";
                    }
                }else if ($field['field'] == 'substatus') {
                    if ($field[data][value] == 'Active') {
                        $searchitem .= " and (sub.status = 1) ";
                    } else if ($field[data][value] == 'Inactive') {
                        $searchitem .= " and (sub.status = 0) ";
                    } else {
                        $searchitem .= "  ";
                    }
                } else {
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


        $countQuery = "SELECT COUNT(*) FROM mypha_productsubcategory sub 
            INNER JOIN mypha_productcategory mc ON mc.category_id = sub.main_category 
            INNER JOIN mypha_productparent_category ON mypha_productparent_category.parent_category_id = mc.parent_category 
            INNER JOIN finascop_business_type ON business_type_id = parent_category_businessType {$search} {$searchitem}";
        $listQuery = "SELECT business_type_name,mypha_productparent_category.parent_category AS parent_category,category_name,sub_category_id,sub_category AS sub_cat,(sub.status),category_name,IF((sub.isHome=1),'Yes','No') AS isHome,IF((sub.isInCategory=1),'Yes','No') AS isInCategory,
        IF((isCompositionDelator=1),'Yes','No')AS sbc_isCompositionDelator,IF((isNonGstRetailer=1),'Yes','No') AS sbc_isNonGstRetailer,IF((isPerishable=1),'Yes','No') AS sbc_isPerishable,IF((hasRestaurantService=1),'Yes','No') AS sbc_hasRestaurantService,
        IF((isProductDetail=1),'Yes','No') AS sbc_isProductDetail,"
            . "if(sub_category_image = '','No','Yes') AS hasImage,if(sub.status = 1,'Active','Inactive') AS substatus,processingTime   FROM mypha_productsubcategory sub 
            INNER JOIN mypha_productcategory mc ON mc.category_id = sub.main_category 
            INNER JOIN mypha_productparent_category ON mypha_productparent_category.parent_category_id = mc.parent_category 
            INNER JOIN finascop_business_type ON business_type_id = parent_category_businessType"
            . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $start,$limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'subCatdetailsView':
        $sub_category_id = isset($_POST['sub_category_id']) ? intval($_POST['sub_category_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($sub_category_id || $ID) {

            $data = $db->getFromDB("SELECT sub_category_id,sub_category AS sub_cat,main_category,status,IF((isHome=1),'Yes','No')AS sbc_isHome,IF((isInCategory=1),'Yes','No')AS sbc_isInCategory,
            IF((isCompositionDelator=1),'Yes','No')AS sbc_isCompositionDelator,IF((isNonGstRetailer=1),'Yes','No')AS sbc_isNonGstRetailer,IF((isPerishable=1),'Yes','No') AS sbc_isPerishable,IF((hasRestaurantService=1),'Yes','No') AS sbc_hasRestaurantService,IF((isProductDetail=1),'Yes','No') AS sbc_isProductDetail,
            IF((hasAgeVerification=1),'Yes','No') AS sbc_hasAgeVerification,"
                . "(SELECT category_name FROM mypha_productcategory WHERE category_id = main_category) AS main_categoryName,(SELECT parent_category FROM mypha_productcategory WHERE category_id = main_category) AS parent_categorysc,
                (SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = main_category)) AS parent_categoryname,
                (SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = main_category)) AS primary_businessTypesc,
                (SELECT business_type_name FROM finascop_business_type WHERE business_type_id = (SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = main_category))) AS btName,sub_category_image,processingTime,
                packingMode,CASE WHEN packingMode = 0 THEN 'Group Packing' WHEN packingMode = 1 THEN 'Pack the items independently' WHEN packingMode = 2 THEN 'Pack same items together'  END AS  packingModeName FROM mypha_productsubcategory WHERE sub_category_id =" . $sub_category_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'subcate_form_load':
        $sub_category_id = isset($_POST['sub_category_id']) ? intval($_POST['sub_category_id']) : 0;
        if ($sub_category_id) {
            $sql = "SELECT sub_category_id,sub_category AS sub_cat,isHome AS sbc_isHome,isInCategory AS sbc_isInCategory,isCompositionDelator as sbc_isCompositionDelator,isNonGstRetailer as sbc_isNonGstRetailer,isPerishable AS sbc_isPerishable,hasRestaurantService AS sbc_hasRestaurantService,isProductDetail AS sbc_isProductDetail,
            hasAgeVerification AS sbc_hasAgeVerification,main_category,STATUS,(SELECT category_name FROM mypha_productcategory WHERE category_id = main_category) AS main_categoryName,packingMode AS radiobuttonPackingMode,
            (SELECT parent_category FROM mypha_productcategory WHERE category_id = main_category) AS parent_categorysc,
            (SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = main_category)) AS parent_categoryname,
            (SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = main_category)) AS primary_businessTypesc,(SELECT business_type_name FROM finascop_business_type WHERE business_type_id = (SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = (SELECT parent_category FROM mypha_productcategory WHERE category_id = main_category))) AS btName,
            processingTime as sbc_processingTime FROM mypha_productsubcategory  WHERE sub_category_id = " . $sub_category_id;
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

    case 'saveSubcategory':

        $db->query('begin');

        if ($_POST['sbc_isProductDetail'] == 'true') {
            $isProductDetail = 1;
        } else {
            $isProductDetail = 0;
        }
        if ($_POST['sbc_isHome'] == 'true') {
            $isHome = 1;
        } else {
            $isHome = 0;
        }
        if ($_POST['sbc_isInCategory'] == 'true') {
            $isInCategory = 1;
        } else {
            $isInCategory = 0;
        }
        if ($_POST['sbc_isCompositionDelator'] == 'true') {
            $isCompositionDelator = 1;
        } else {
            $isCompositionDelator = 0;
        }
        if ($_POST['sbc_isNonGstRetailer'] == 'true') {
            $isNonGstRetailer = 1;
        } else {
            $isNonGstRetailer = 0;
        }
        if ($_POST['sbc_isPerishable'] == 'true') {
            $isPerishable = 1;
        } else {
            $isPerishable = 0;
        }
        if ($_POST['sbc_hasRestaurantService'] == 'true') {
            $hasRestaurantService = 1;
        } else {
            $hasRestaurantService = 0;
        }
        if ($_POST['sbc_hasAgeVerification'] == 'true') {
            $hasAgeVerification = 1;
        } else {
            $hasAgeVerification = 0;
        }
        $data = array(
            "sub_category_id" => $_POST['id'],
            "sub_category" => $_POST['name'],
            "main_category" => $_POST['main_category'],
            "processingTime" => $_POST['sbc_processingTime'],
            "status" => $_POST['status'],
            "isHome" => $isHome,
            "isInCategory" => $isInCategory,
            "isCompositionDelator" => $isCompositionDelator,
            "isNonGstRetailer" => $isNonGstRetailer,
            "isPerishable" => $isPerishable,
            "hasRestaurantService" => $hasRestaurantService,
            "isProductDetail" => $isProductDetail,
            "packingMode" => $_POST['packingMode'],
            "hasAgeVerification" => $hasAgeVerification
        );

        $sub_category_id = $data['sub_category_id'];
        $sub_category = $data['sub_category'];
        $main_category = $data['main_category'];
        $status = $data['status'];
        //$userid = $_SESSION['admin']->Finascop_UserId;
        if ($data['sub_category_id'] > 0) {
            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;
            $SubCategoryUnique = $db->getItemSafe("SELECT COUNT(*) from mypha_productsubcategory WHERE sub_category ='{$sub_category}' and main_category = ? AND sub_category_id!='{$sub_category_id}' ", "s", [$_POST['main_category']]);
            if ($SubCategoryUnique > 0) {
                echo "{success: false, message:'This  Sub category already existing.'}";
                exit;
            } else {
                $status = $db->perform("mypha_productsubcategory", $data, 'update', 'sub_category_id =' . $data['sub_category_id']);
                $lastId = $data['sub_category_id'];

                $fsim['stit_category_name'] = $data['sub_category'];
                $status = $db->perform('finascop_stock_itemmaster', $fsim, 'update', " product_category = {$lastId} AND isMedicine = 0");
                $fsui['fsi_categry_name'] = $data['sub_category'];
                $status = $db->perform('finascop_stock_uniqueitem', $fsui, 'update', " fsi_category_id = {$lastId} AND isMedicine = 0");
            }
        } else {

            $SubCategoryUnique = $db->getItemSafe("SELECT COUNT(*) from mypha_productsubcategory WHERE sub_category ='{$sub_category}' and main_category = ? AND sub_category_id!='{$sub_category_id}' ", "s", [$_POST['main_category']]);
            if ($SubCategoryUnique > 0) {
                echo "{success: false, message:'This  Sub category already existing.'}";
                exit;
            } else {
                unset($data['sub_category_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mypha_productsubcategory', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT sub_category_id,sub_category,main_category,status,(SELECT category_name FROM mypha_productcategory mc WHERE category_id = main_category) as main_category_name FROM mypha_productsubcategory WHERE sub_category_id = {$lastId}", true);

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listBrands':

        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'brand_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['pm_id', 'pm_name', 'pm_code', 'pm_type', 'pm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

                        if ($field['field'] == 'top_brand') {
                            if ($field['data']['value'] == 'Y') {
                                $searchitem .= " AND {$field['field']} = 1 ";
                            } else if ($field['data']['value'] == 'N') {
                                $searchitem .= " AND {$field['field']} = 0 ";
                            } else {
                                $searchitem .= " AND (top_brand = 1 OR top_brand=0) ";
                            }
                        }

                        break;
                    default:
                        $checkComa = strstr($field['data']['value'], ',');
                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " AND ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " AND ({$field['field']} LIKE '{$field[data][value]}%') ";
                        }
                }
            }
        }


        $countQuery = "SELECT COUNT(*) FROM mypha_productbrands mpb INNER JOIN mypha_productmanufacture mpm ON mpb.manufacture_id = mpm.manufacture_id {$search} {$searchitem}";

        $listQuery = "SELECT brand_id,brand_name,IF((mpb.status=0),'Inactive','Active')AS status,img_name,img_url,mpb.manufacture_id,
            IF((top_brand=1),'Y','N')AS top_brand, manufacture_name FROM mypha_productbrands mpb INNER JOIN mypha_productmanufacture mpm ON mpb.manufacture_id = mpm.manufacture_id " . "{$search} {$searchitem} ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;


    case 'brands_form_load':
        $brand_id = isset($_POST['brand_id']) ? intval($_POST['brand_id']) : 0;
        if ($brand_id) {
            $sql = "SELECT brand_id ,brand_name ,top_brand ,status AS comboMasterBrandsstatus,manufacture_id as promanufacture_id FROM mypha_productbrands  WHERE brand_id= " . $brand_id;
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

    case 'BranddetailsView':
        $brand_id = isset($_POST['brand_id']) ? intval($_POST['brand_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($brand_id || $ID) {

            $data = $db->getFromDB("SELECT brand_id,brand_name,IF((top_brand=1),'Yes','No')AS top_brand,img_url,img_name,status,mpb.manufacture_id,"
                . "IF(mpb.manufacture_id > 0,(SELECT manufacture_name FROM mypha_productmanufacture mpm WHERE mpm.manufacture_id = mpb.manufacture_id),'-') AS manufacture  FROM mypha_productbrands mpb WHERE brand_id =" . $brand_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'saveBrands':
        $db->query('begin');

        $data = array(
            "brand_id" => $_POST['id'],
            "brand_name" => $_POST['name'],
            "manufacture_id" => $_POST['manufacture'],
            "status" => $_POST['status']
        );
        $brand_name = $data['brand_name'];
        $status = $data['status'];
        $brand_id = $data['brand_id'];


        if ($_POST['topbrand'] == 'true') {
            $data['top_brand'] = 1;
        } else {
            $data['top_brand'] = 0;
        }

        $brand_name = addslashes($brand_name);

        if (empty($_POST['id'])) {

            $brandnameUnique = $db->getItemSafe("SELECT COUNT(*) from mypha_productbrands WHERE brand_name ='{$brand_name}' and manufacture_id = ?", "s", [$_POST['manufacture']]);
            if ($brandnameUnique > 0) {
                echo "{success: false, message:'This Brand Name already existing.'}";
                exit;
            } else {
                unset($data['brand_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mypha_productbrands', $data);
                $lastId = $db->insert_id();
            }
        } else {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;

            $brandnameUnique = $db->getItemSafe("SELECT COUNT(*) from mypha_productbrands WHERE brand_name ='{$brand_name}' and manufacture_id = ? AND brand_id!='{$brand_id}'", "s", [$_POST['manufacture']]);
            if ($brandnameUnique > 0) {
                echo "{success: false, message:'This Brand Name already existing.'}";
                exit;
            } else {

                $status = $db->perform("mypha_productbrands", $data, 'update', ' brand_id =' . $data['brand_id']);
                $lastId = $data['brand_id'];


                $fsim['stit_brand_name'] = $data['brand_name'];
                $fsim['med_manufactureid'] = $data['manufacture_id'];
                $fsim['med_manufacturename'] = $db->getItemFromDB("SELECT manufacture_name FROM mypha_productmanufacture WHERE manufacture_id = {$data['manufacture_id']}");
                $status = $db->perform(
                    'finascop_stock_itemmaster',
                    $fsim,
                    'update',
                    ' pdt_brand =' . $data['brand_id'] . ' AND isMedicine = 0'
                );
                $fsui['fsi_brand_name'] = $data['brand_name'];

                $status = $db->perform(
                    'finascop_stock_uniqueitem',
                    $fsui,
                    'update',
                    ' fsi_brand_id =' . $data['brand_id'] . ' AND isMedicine = 0'
                );
            }
        }

        $return_rec = $db->getFromDb("SELECT brand_id,brand_name,img_url,img_name,top_brand,created_on,status FROM mypha_productbrands WHERE brand_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listPackageTypes':


        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'package_type_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['pm_id', 'pm_name', 'pm_code', 'pm_type', 'pm_status'];
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
                            $searchitem .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_productpackage_type  {$search}";
        $listQuery = "SELECT package_type_id,package_type_name,IF((status=1),'Active','Inactive') AS status FROM mypha_productpackage_type 
        " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'packagetypesdetailsView':

        $package_type_id = isset($_POST['package_type_id']) ? intval($_POST['package_type_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($package_type_id || $ID) {

            $data = $db->getFromDB("SELECT package_type_id,package_type_name,status AS status FROM mypha_productpackage_type  WHERE package_type_id =" . $package_type_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'packagetypes_form_load':

        $package_type_id = isset($_POST['package_type_id']) ? intval($_POST['package_type_id']) : 0;
        if ($package_type_id) {
            $sql = "SELECT  package_type_id,package_type_name,status AS comboMasterPackageTypesStatus FROM mypha_productpackage_type WHERE package_type_id =" . $package_type_id;
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


    case 'savePackageTypes':

        $db->query('begin');
        $data = array(
            "package_type_id" => $_POST['id'],
            "package_type_name" => $_POST['name'],
            "status" => $_POST['status']
        );
        $package_type_id = $data['package_type_id'];
        $package_type_name = $data['package_type_name'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $package_type_name = addslashes($package_type_name);

        if ($data['package_type_id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;


            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_productpackage_type WHERE package_type_name ='{$package_type_name}' AND package_type_id!='{$package_type_id}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'This Package Name already existing.'}";
                exit;
            } else {
                $status = $db->perform("mypha_productpackage_type", $data, 'update', 'package_type_id =' . $data['package_type_id']);
                $lastId = $data['package_type_id'];
                $fsim_cos['cos_package_type_name'] = $package_type_name;
                $fsim_cosb['cosb_package_type_name'] = $package_type_name;
                $fsim_ccs['ccs_package_type_name'] = $package_type_name;
                $fsim_ccsb['ccsb_package_type_name'] = $package_type_name;
                $fsim_rs['rs_package_type_name'] = $package_type_name;
                $fsim_rsb['rsb_package_type_name'] = $package_type_name;
                $fsim_cs['cs_package_type_name'] = $package_type_name;
                $fsim_csb['csb_package_type_name'] = $package_type_name;
                $fsim_ds['ds_package_type_name'] = $package_type_name;
                $fsim_dsb['dsb_package_type_name'] = $package_type_name;
                $fsim_lpt['least_package_type_name'] = $package_type_name;
                $status1 = $db->perform('finascop_stock_itemmaster', $fsim_cos, 'update', " cos_package_type_id = {$lastId}  AND isMedicine = 0");
                $status1 = $db->perform('finascop_stock_itemmaster', $fsim_cosb, 'update', " cosb_package_type_id = {$lastId} ");
                $status1 = $db->perform('finascop_stock_itemmaster', $fsim_ccs, 'update', " ccs_package_type_id = {$lastId} AND isMedicine = 0");
                $status1 = $db->perform('finascop_stock_itemmaster', $fsim_ccsb, 'update', " ccsb_package_type_id = {$lastId} ");
                $status1 = $db->perform('finascop_stock_itemmaster', $fsim_rs, 'update', " rs_package_type_id = {$lastId} AND isMedicine = 0");
                $status1 = $db->perform('finascop_stock_itemmaster', $fsim_rsb, 'update', " rsb_package_type_id = {$lastId} ");
                $status1 = $db->perform('finascop_stock_itemmaster', $fsim_cs, 'update', " cs_package_type_id = {$lastId} ");
                $status1 = $db->perform('finascop_stock_itemmaster', $fsim_csb, 'update', " csb_package_type_id = {$lastId} ");
                $status1 = $db->perform('finascop_stock_itemmaster', $fsim_ds, 'update', " ds_package_type_id = {$lastId} ");
                $status1 = $db->perform('finascop_stock_itemmaster', $fsim_dsb, 'update', " dsb_package_type_id = {$lastId} ");
                $status1 = $db->perform('finascop_stock_itemmaster', $fsim_lpt, 'update', " least_package_type_id = {$lastId} ");
            }
        } else {

            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_productpackage_type WHERE package_type_name ='{$package_type_name}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'This Package Name already existing.'}";
                exit;
            } else {
                unset($data['package_type_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mypha_productpackage_type', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT package_type_id,package_type_name,status FROM mypha_productpackage_type WHERE package_type_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listTagsTypes':


        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'tag_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['pm_id', 'pm_name', 'pm_code', 'pm_type', 'pm_status'];
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
                            $searchitem .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM mypha_producttag  {$search}";
        $listQuery = "SELECT tag_id,tag_name,IF((status=1),'Active','Inactive') AS status FROM mypha_producttag 
        " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'tagsdetailsView':

        $tag_id = isset($_POST['tag_id']) ? intval($_POST['tag_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($tag_id || $ID) {

            $data = $db->getFromDB("SELECT tag_id,tag_name,status AS status FROM mypha_producttag  WHERE tag_id =" . $tag_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'tags_form_load':

        $tag_id = isset($_POST['tag_id']) ? intval($_POST['tag_id']) : 0;
        if ($tag_id) {
            $sql = "SELECT  tag_id,tag_name,status AS comboMasterTagsStatus FROM mypha_producttag WHERE tag_id =" . $tag_id;
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


    case 'saveTags':

        $db->query('begin');
        $data = array(
            "tag_id" => $_POST['id'],
            "tag_name" => $_POST['name'],
            "status" => $_POST['status']
        );
        $tag_id = $data['tag_id'];
        $tag_name = $data['tag_name'];
        $status = $data['status'];

        $tag_name = addslashes($tag_name);

        $userid = $_SESSION['admin']->Finascop_UserId;



        if ($data['tag_id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;

            $tagnameUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_producttag WHERE tag_name ='{$tag_name}' AND tag_id!='{$tag_id}' ");
            if ($tagnameUnique > 0) {
                echo "{success: false, message:'This Tag Name already existing.'}";
                exit;
            } else {
                $status = $db->perform("mypha_producttag", $data, 'update', 'tag_id =' . $data['tag_id']);
                $lastId = $data['tag_id'];
            }
        } else {
            $tagnameUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_producttag WHERE tag_name ='{$tag_name}' AND tag_id!='{$tag_id}' ");
            if ($tagnameUnique > 0) {
                echo "{success: false, message:'This Tag Name already existing.'}";
                exit;
            } else {
                unset($data['tag_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mypha_producttag', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT tag_id,tag_name,status FROM mypha_producttag WHERE tag_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listHSN':


        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['pm_id', 'pm_name', 'pm_code', 'pm_type', 'pm_status'];
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


        $countQuery = "SELECT COUNT(*) FROM hsn_value INNER JOIN finascop_hsn ON hsnId = hsn_id  {$search}";
        $listQuery = "SELECT id as hsn_id_pk,hsn_id,hsn_code,hsnGst,hsnCess,hsn_description,IF((STATUS=1),'Active','Inactive')AS status  FROM hsn_value 
        INNER JOIN finascop_hsn ON hsnId = hsn_id {$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'hsndetailsView':
        $hsn_id = isset($_POST['hsn_id']) ? intval($_POST['hsn_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($hsn_id || $ID) {

            $data = $db->getFromDB("SELECT hsn_id,hsn_code,hsnGst,hsnCess,hsn_description,status FROM hsn_value 
            INNER JOIN finascop_hsn ON hsnId = hsn_id  WHERE id =" . $hsn_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'hns_form_load':

        $hsn_id = isset($_POST['hsn_id']) ? intval($_POST['hsn_id']) : 0;
        if ($hsn_id) {
            $sql = "SELECT id as hsn_id_pk,hsn_id,hsn_code,hsnGst,hsnCess,hsn_description,status AS comboMasterHSNStatus FROM hsn_value 
            INNER JOIN finascop_hsn ON hsnId = hsn_id WHERE id =" . $hsn_id;
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


    case 'saveHSN':

        $db->query('begin');
        $description = $_POST['description'];
        if ($_POST['cess'] > 0) {
            $cess = $_POST['cess'];
        } else {
            $cess = 0;
        }
        $id = $_POST['id'];
        $data = array(
            "hsnGst" => $_POST['gst'],
            "hsnCess" => $_POST['cess'],
            "hsnDescription" => $description
        );
        $data = array_filter($data);
        $hsn_code = $_POST['code'];
        $hsnGst = $data['hsnGst'];
        $status = $data['status'];
        $mdata['hsn_description'] = $description;

        if ($id > 0) {
            $hsn_id = $db->getItemFromDB("SELECT hsn_id FROM finascop_hsn WHERE hsn_code ='{$hsn_code}'");
            $hsnUnique = $db->getItemSafe("SELECT COUNT(*) from hsn_value WHERE hsnGst = ? AND  hsnId = '{$hsn_id}' AND id !='{$id}' ", "s", [$_POST['gst']]);
            if ($hsnUnique > 0) {
                echo "{success: false, message:'GST already existing for this code.'}";
                exit;
            } else {

                $mdata['updated_on'] = date('Y-m-d H:i:s');
                $mdata['updated_by'] = $userid;

                $status = $db->perform('finascop_hsn', $mdata, 'update', " hsn_id = {$hsn_id}");

                $status = $db->perform("hsn_value", $data, 'update', 'id =' . $id);
            }
            $lastId = $id;
        } else {
            $hsn_id = $db->getItemFromDB("SELECT hsn_id FROM finascop_hsn WHERE hsn_code = '{$hsn_code}'");
            if ($hsn_id > 0) {
                $mdata['updated_on'] = date('Y-m-d H:i:s');
                $mdata['updated_by'] = $userid;

                $status = $db->perform('finascop_hsn', $mdata, 'update', " hsn_id = {$hsn_id}");

                $data['hsnId'] = $hsn_id;
                $status = $db->perform("hsn_value", $data);
                $lastId = $db->insert_id();
            } else {

                $mdata['created_on'] = date('Y-m-d H:i:s');
                $mdata['created_by'] = $userid;
                $mdata['hsn_code'] =  $_POST['code'];
                $mdata['status'] =  $_POST['status'];
                $status = $db->perform('finascop_hsn', $mdata);
                $hsn_id = $db->insert_id();

                $data['hsnId'] = $hsn_id;
                $status = $db->perform("hsn_value", $data);
                $lastId = $db->insert_id();
            }
        }

        $itemCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster WHERE stit_hsnId = {$hsn_id} AND taxValueId = {$lastId}");
        if ($itemCount > 0) {
            $fsim['stit_HSN_code'] = $_POST['code'];
            $fsim['stit_HSNCode'] = $_POST['code'];
            $fsim['stit_GST'] = $hsnGst;
            $status = $db->perform('finascop_stock_itemmaster', $fsim, 'update', " stit_hsnId = {$hsn_id} ");
        }
        $return_rec = $db->getFromDb("SELECT id as hsn_id_pk,hsn_id,hsn_code,hsnGst,hsnCess,hsn_description,status AS comboMasterHSNStatus FROM hsn_value 
        INNER JOIN finascop_hsn ON hsnId = hsn_id WHERE id = {$lastId}", true);

        $status = $db->query('commit');

        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listItemName':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'itemname_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['pm_id', 'pm_name', 'pm_code', 'pm_type', 'pm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    } else if ($field['field'] == 'isVerified') {
                            if ($field['data']['value'] == 'Yes') {
                                $fiterItem = 1;
                                $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'No') {
                                $fiterItem = 0;
                                $searchitem .= " and ({$field[field]} = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (isVerified = 1 or isVerified=0) ";
                            }
                        } else if ($field['field'] == 'itemDisplayName') {
                            if ($field['data']['value'] == 'Yes') {
                                $fiterItem = 1;
                                $searchitem .= " and (isItemGroup = {$fiterItem}) ";
                            } else if ($field['data']['value'] == 'No') {
                                $fiterItem = 0;
                                $searchitem .= " and (isItemGroup = {$fiterItem}) ";
                            } else {
                                $searchitem .= " and (isItemGroup = 1 or isItemGroup=0) ";
                            }
                        }

                        break;
                    default:


                        $checkComa = strstr($field['data']['value'], ',');

                        if ($checkComa != '') {
                            $fiterItem = $field['data']['value'];
                            $fiterItem = str_replace(',', "','", $fiterItem);
                            $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                        } else {
                            $searchitem .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                        }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM finascop_stock_itemmastername  {$search}";
        $listQuery = "SELECT itemname_id,item_name,isItemGroup,IF((isItemGroup=1),'Yes','No') AS itemDisplayName,IF((STATUS=1),'Active','Inactive')AS status,IF((isVerified=1),'Yes','No')AS isVerified FROM finascop_stock_itemmastername 
        " . "{$search} {$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'itemnamedetailsView':

        $itemname_id = isset($_POST['itemname_id']) ? intval($_POST['itemname_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($itemname_id || $ID) {

            $data = $db->getFromDB("SELECT itemname_id,item_name,status,itemDisplayName,iteamGroupImage FROM finascop_stock_itemmastername  WHERE itemname_id =" . $itemname_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'itemname_form_load':

        $itemname_id = isset($_POST['itemname_id']) ? intval($_POST['itemname_id']) : 0;
        if ($itemname_id) {
            $sql = "SELECT itemname_id,item_name,isItemGroup,itemDisplayName,iteamGroupImage,status AS comboMasterItemNameStatus FROM finascop_stock_itemmastername WHERE itemname_id =" . $itemname_id;
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

    case 'saveItemName':

        $db->query('begin');
        $data = array(
            "itemname_id" => $_POST['id'],
            "item_name" => $_POST['name'],
            "itemDisplayName" => $_POST['itemDisplayName'],
            "isItemGroup" => $_POST['isItemGroup'],
            "status" => $_POST['status']
        );
        $itemname_id = $data['itemname_id'];
        $status = $data['status'];
        $itemDisplayName = $data['itemDisplayName'];
        $isItemGroup = $data['isItemGroup'];
        $item_name = $data['item_name'];

        $item_name = addslashes($item_name);
        //$userid = $_SESSION['admin']->Finascop_UserId;
        if ($data['itemname_id'] > 0) {

            $data['updated_by'] = $userid;
            $data['updated_on'] = date('Y-m-d H:i:s');


            $itemnameUnique = $db->getItemFromDB("SELECT COUNT(*) from finascop_stock_itemmastername WHERE item_name ='{$item_name}' AND itemname_id!='{$itemname_id}' ");
            if ($itemnameUnique > 0) {
                echo "{success: false, message:'This Item Name already existing.'}";
                exit;
            } else {
                $status = $db->perform("finascop_stock_itemmastername", $data, 'update', 'itemname_id =' . $data['itemname_id']);
                $lastId = $data['itemname_id'];

                $fsim['stit_itemName'] = $data['item_name'];
                $status = $db->perform('finascop_stock_itemmaster', $fsim, 'update', " stit_itemId = {$data['itemname_id']} AND isMedicine = 0");
                $fsui['fsi_item_name'] = $data['item_name'];
                $status = $db->perform('finascop_stock_uniqueitem', $fsui, 'update', " fsi_item_id = {$data['itemname_id']} AND isMedicine = 0");
            }
        } else {


            $itemnameUnique = $db->getItemFromDB("SELECT COUNT(*) from finascop_stock_itemmastername WHERE item_name ='{$item_name}' ");
            if ($itemnameUnique > 0) {
                echo "{success: false, message:'This Item Name already existing.'}";
                exit;
            } else {
                unset($data['itemname_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('finascop_stock_itemmastername', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT itemname_id,item_name,status,itemDisplayName,isItemGroup FROM finascop_stock_itemmastername WHERE itemname_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;


    case 'getBrandImage':
        $brand_id = $_POST['brand_id'];
        $qry = "select brand_id,img_url from mypha_productbrands where `brand_id`= {$brand_id}";
        $data = $db->getMultipleData($qry, true);
        // $result = $db->getMultipleData($stockregId, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            $data = '';
            echo '{"success":true,"data":' . json_encode($data) . '}';
        }
        break;

    case 'getparentcategoryImage':
        $parentcat_id = $_POST['parentcat_id'];
        $qry = "select parent_category_id,image_url from mypha_productparent_category where `parent_category_id`= {$parentcat_id}";
        $data = $db->getMultipleData($qry, true);
        // $result = $db->getMultipleData($stockregId, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            $data = '';
            echo '{"success":true,"data":' . json_encode($data) . '}';
        }
        break;
    case 'get_catimg_s3_details':

        $uploadtype = $_POST['uploadtype'];
        $rid = $_POST['rid'];
        if ($uploadtype == 'category') {
            $data['file_name'] = ($rid . "_1"); /* add extension in js */
            $data['albumBucketName'] = AWSBUCKETNAME;
            $data['accessKey'] = AWSS3ASSETUPLOADACCESSID;
            $data['secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
            $data['bucketRegion'] = AWSS3ASSETUPLOADREGION;
            $data['oncompleteurl'] = AWSCATBUCKETFOLDER;
            $data['img_path_db'] = $db->getItemFromDB("select image_url from mypha_productcategory where `category_id`= {$rid}");
        } else if ($uploadtype == 'parentcategory') {
            $data['pc_file_name'] = ($rid . "_1"); /* add extension in js */
            $data['pc_albumBucketName'] = AWSBUCKETNAME;
            $data['pc_accessKey'] = AWSS3ASSETUPLOADACCESSID;
            $data['pc_secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
            $data['pc_bucketRegion'] = AWSS3ASSETUPLOADREGION;
            $data['pc_oncompleteurl'] = AWSCATBUCKETFOLDER;
            $data['pc_img_path_db'] = $db->getItemFromDB("select image_url from mypha_productparent_category where `parent_category_id`= {$rid}");
        } else if ($uploadtype == 'subcategory') {
            $data['sc_file_name'] = ($rid . "_1"); /* add extension in js */
            $data['sc_albumBucketName'] = AWSBUCKETNAME;
            $data['sc_accessKey'] = AWSS3ASSETUPLOADACCESSID;
            $data['sc_secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
            $data['sc_bucketRegion'] = AWSS3ASSETUPLOADREGION;
            $data['sc_oncompleteurl'] = AWSCATBUCKETFOLDER;
            $data['sc_img_path_db'] = $db->getItemFromDB("select sub_category_image from mypha_productsubcategory where `sub_category_id`= {$rid}");
        }


        echo "{success : true,'data':" . json_encode($data) . "}";
        break;
    case 'get_brandimg_s3_details':

        $uploadtype = $_POST['uploadtype'];
        $rid = $_POST['rid'];
        if ($uploadtype == 'brand') {
            $data['br_file_name'] = ($rid . "_1"); /* add extension in js */
            $data['br_albumBucketName'] = AWSBUCKETNAME;
            $data['br_accessKey'] = AWSS3ASSETUPLOADACCESSID;
            $data['br_secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
            $data['br_bucketRegion'] = AWSS3ASSETUPLOADREGION;
            $data['br_oncompleteurl'] = AWSBRANDBUCKETFOLDER;
            $data['br_img_path_db'] = $db->getItemFromDB("select img_url from mypha_productbrands where `brand_id`= {$rid}");
        }


        echo "{success : true,'data':" . json_encode($data) . "}";
        break;

    case 'saveParentCategoryImage':
        $db->query('begin');
        $bucketname = $_POST['bucket'];
        $filename = $_POST['uploaded_file_name'];
        $parent_category_id = $_POST['parent_category_id'];
        $file_path = $_POST['filepath'];
        $data = array(
            "image_url" => $file_path,
        );
        $res = $db->perform('mypha_productparent_category', $data, 'update', 'parent_category_id=' . $parent_category_id);
        $status = $db->query('commit');

        if ($status) {
            echo "{success: true,msg:'Updated Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;

    case 'getcategoryImage':
        $category_id = $_POST['cat_id'];
        $qry = "select category_id,image_url from mypha_productcategory where `category_id`= {$category_id}";
        $data = $db->getMultipleData($qry, true);
        // $result = $db->getMultipleData($stockregId, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            $data = '';
            echo '{"success":true,"data":' . json_encode($data) . '}';
        }

        break;
    case 'getsubcategoryImage':
        $subcategory_id = $_POST['subcat_id'];
        $qry = "select sub_category_id,sub_category_image from mypha_productsubcategory where `sub_category_id`= {$subcategory_id}";
        $data = $db->getMultipleData($qry, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            $data = '';
            echo '{"success":true,"data":' . json_encode($data) . '}';
        }

        break;
    case 'getprdtMasterImage':
        $itemname_id = $_POST['itemname_id'];
        $qry = "SELECT itemname_id,iteamGroupImage FROM finascop_stock_itemmastername where `itemname_id`= {$itemname_id}";
        $data = $db->getMultipleData($qry, true);
        if ($data) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            $data = '';
            echo '{"success":true,"data":' . json_encode($data) . '}';
        }

        break;
    case 'get_prtmaster_s3_details':

        $uploadtype = $_POST['uploadtype'];
        $rid = $_POST['rid'];
        if ($uploadtype == 'productMaster') {
            $data['pm_file_name'] = ($rid . "_1"); /* add extension in js */
            $data['pm_albumBucketName'] = AWSBUCKETNAME;
            $data['pm_accessKey'] = AWSS3ASSETUPLOADACCESSID;
            $data['pm_secretKey'] = AWSS3ASSETUPLOADSECRETKEY;
            $data['pm_bucketRegion'] = AWSS3ASSETUPLOADREGION;
            $data['pm_oncompleteurl'] = AWSCATBUCKETFOLDER;
            $data['pm_img_path_db'] = $db->getItemFromDB("SELECT iteamGroupImage FROM finascop_stock_itemmastername WHERE `itemname_id`= {$rid}");
        }
        echo "{success : true,'data':" . json_encode($data) . "}";
        break;
    case 'savePrdtMasterImage':
        $db->query('begin');
        $bucketname = $_POST['bucket'];
        $filename = $_POST['uploadedprt_file_name'];
        $itemname_id = $_POST['itemname_id'];
        $file_path = $_POST['filepath'];
        $data = array(
            "iteamGroupImage" => $file_path,
        );
        $res = $db->perform('finascop_stock_itemmastername', $data, 'update', 'itemname_id=' . $itemname_id);
        $status = $db->query('commit');

        if ($status) {
            echo "{success: true,msg:'Updated Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'saveSubCategoryImage':
        $db->query('begin');
        $bucketname = $_POST['bucket'];
        $filename = $_POST['uploaded_file_name'];
        $subcategory_id = $_POST['subcategory_id'];
        $file_path = $_POST['filepath'];
        $data = array(
            "sub_category_image" => $file_path,
        );
        $res = $db->perform('mypha_productsubcategory', $data, 'update', 'sub_category_id=' . $subcategory_id);
        $status = $db->query('commit');

        if ($status) {
            echo "{success: true,msg:'Updated Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'saveBrandImage':
        $db->query('begin');
        $bucketname = $_POST['bucket'];
        $filename = $_POST['uploaded_file_name'];
        $brand_id = $_POST['brand_id'];
        $file_path = $_POST['filepath'];
        $data = array(
            "img_url" => $file_path,
        );
        $res = $db->perform('mypha_productbrands', $data, 'update', 'brand_id=' . $brand_id);
        $status = $db->query('commit');

        if ($status) {
            echo "{success: true,msg:'Updated Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;

    case 'saveMainCategoryImage':
        $db->query('begin');
        $bucketname = $_POST['bucket'];
        $filename = $_POST['uploaded_file_name'];
        $category_id = $_POST['category_id'];
        $file_path = $_POST['filepath'];
        $data = array(
            "image_url" => $file_path,
        );
        $res = $db->perform('mypha_productcategory', $data, 'update', 'category_id=' . $category_id);
        $status = $db->query('commit');

        if ($status) {
            echo "{success: true,msg:'Updated Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'saveProductManufacture':

        $db->query('begin');
        $data = $_POST['n'];
        $manufacture_id = $data['manufacture_id'];
        $manufacture_name = $data['manufacture_name'];
        $manufacture_name = addslashes($manufacture_name);


        if ($data['manufacture_id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;

            $manufactureUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_productmanufacture WHERE manufacture_name ='{$manufacture_name}' AND manufacture_id <> {$manufacture_id} ");
            if ($manufactureUnique > 0) {
                echo "{success: false, message:'This Manufacture already exists.'}";
                exit;
            } else {
                $status = $db->perform("mypha_productmanufacture", $data, 'update', 'manufacture_id =' . $data['manufacture_id']);
                $lastId = $data['manufacture_id'];

                $fsim['med_manufacturename'] = $data['manufacture_name'];
                $status = $db->perform('finascop_stock_itemmaster', $fsim, 'update', " med_manufactureid = {$lastId} AND isMedicine = 0");
                //                $fsui['fsi_brand_name'] = $data['composition_name'];
                //                $status = $db->perform('finascop_stock_uniqueitem', $fsui, 'update', " fsi_brand_id = {$lastId} AND isMedicine = 1");
            }
        } else {
            $manufactureUnique = $db->getItemFromDB("SELECT COUNT(*) from mypha_productmanufacture WHERE manufacture_name ='{$manufacture_name}'  ");
            if ($manufactureUnique > 0) {
                echo "{success: false, message:'This Manufacture already exists.'}";
                exit;
            } else {
                unset($data['manufacture_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('mypha_productmanufacture', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT manufacture_id,manufacture_name,status FROM mypha_productmanufacture WHERE manufacture_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listProductManufacture':
        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'manufacture_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['pm_id', 'pm_name', 'pm_code', 'pm_type', 'pm_status'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
                }
            }
        }



        $countQuery = "SELECT COUNT(*) FROM mypha_productmanufacture   {$search}";

        $listQuery = "SELECT manufacture_id,manufacture_name,IF((status=1),'Active','Inactive')AS status FROM mypha_productmanufacture
        " . "{$search}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'ManufactureProductdetailsView':
        $manufacture_id = isset($_POST['manufacture_id']) ? intval($_POST['manufacture_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($manufacture_id || $ID) {

            $data = $db->getFromDB("SELECT manufacture_id,manufacture_name,status FROM mypha_productmanufacture WHERE manufacture_id =" . $manufacture_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'manufactureprod_form_load':
        $manufacture_id = isset($_POST['manufacture_id']) ? intval($_POST['manufacture_id']) : 0;
        if ($manufacture_id) {
            $sql = "SELECT manufacture_id as textfieldProductMasterManufactureId,manufacture_name as textfieldProductMasterManufacture,status as  manufacturProductStatus FROM mypha_productmanufacture  WHERE manufacture_id= " . $manufacture_id;
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
    case 'loadProductManufactureCombo':
        $qry = "SELECT manufacture_id, manufacture_name FROM mypha_productmanufacture WHERE status = 1";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . count($data) . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listBusinessTypes':


        $rec_limit = empty($_POST['limit']) ? 12 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $sort = empty($sort) ? 'business_type_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        if (isset($data['filter'])) {
        $allowedFields = ['pm_id', 'pm_name', 'pm_code', 'pm_type', 'pm_status'];
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

        $countQuery = "SELECT COUNT(*) FROM finascop_business_type  {$search}";
        $listQuery = "SELECT business_type_id,business_type_name,IF((status=1),'Active','Inactive') AS status FROM finascop_business_type 
        " . "{$search}{$searchitem}  ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";

        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'businesstypesdetailsView':

        $business_type_id = isset($_POST['business_type_id']) ? intval($_POST['business_type_id']) : 0;
        $ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        if ($business_type_id || $ID) {

            $data = $db->getFromDB("SELECT business_type_id,business_type_name,status AS status FROM finascop_business_type  WHERE business_type_id =" . $business_type_id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;

    case 'businesstypes_form_load':

        $business_type_id = isset($_POST['business_type_id']) ? intval($_POST['business_type_id']) : 0;
        if ($business_type_id) {
            $sql = "SELECT  business_type_id,business_type_name,status AS comboMasterBusinessTypesStatus FROM finascop_business_type WHERE business_type_id =" . $business_type_id;
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


    case 'saveBusinessTypes':

        $db->query('begin');
        $data = array(
            "business_type_id" => $_POST['id'],
            "business_type_name" => $_POST['name'],
            "status" => $_POST['status']
        );
        $business_type_id = $data['business_type_id'];
        $business_type_name = $data['business_type_name'];
        $status = $data['status'];
        $userid = $_SESSION['admin']->Finascop_UserId;
        $business_type_name = addslashes($business_type_name);




        if ($data['business_type_id'] > 0) {

            $data['updated_on'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $userid;


            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from finascop_business_type WHERE business_type_name ='{$business_type_name}' AND business_type_id!='{$business_type_id}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Business type name already exists.'}";
                exit;
            } else {
                $status = $db->perform("finascop_business_type", $data, 'update', 'business_type_id =' . $data['business_type_id']);
                $lastId = $data['business_type_id'];
                $fsim['stit_business_type_namme'] = $business_type_name;
                //$status1 = $db->perform('finascop_stock_itemmaster', $fsim, 'update', " pdt_business_type_id = {$lastId} ");
            }
        } else {

            $packagenameUnique = $db->getItemFromDB("SELECT COUNT(*) from finascop_business_type WHERE business_type_name ='{$business_type_name}' ");
            if ($packagenameUnique > 0) {
                echo "{success: false, message:'Business type name already exists.'}";
                exit;
            } else {
                unset($data['business_type_id']);
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['created_by'] = $userid;
                $status = $db->perform('finascop_business_type', $data);
                $lastId = $db->insert_id();
            }
        }

        $return_rec = $db->getFromDb("SELECT business_type_id,business_type_name,status FROM finascop_business_type WHERE business_type_id = {$lastId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }

        break;
    case 'verifyProdctMaster':
        $medId = $_POST['itemid'];
        $db->query('begin');

        $data = array(
            "isVerified" => 1,
            "verifedOn" => date('Y-m-d H:i:s'),
            "verifedBy" => $_SESSION['admin']->Finascop_UserId
        );
        $isVerified = $db->getItemFromDb("SELECT isVerified FROM finascop_stock_itemmastername WHERE itemname_id = {$medId}");

        if ($medId > 0 && $isVerified == 0) {
            $status = $db->perform("finascop_stock_itemmastername", $data, 'update', 'itemname_id =' . $medId);
        } else {
            echo "{'success':true,'valid':false,'message': 'Data is already verified..'}";
            exit();
        }

        $return_rec = $db->getFromDb("SELECT itemname_id,item_name,status FROM finascop_stock_itemmastername WHERE itemname_id = {$medId}", true);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'getItemCategory':
        $main_category = $_POST['category_id'];
        //$main_category = $db->getItemFromDB("SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = {$sub_category_id}");
        $parent_category = $db->getItemFromDB("SELECT parent_category FROM mypha_productcategory WHERE category_id = {$main_category}");

        $itemHistory['iteParentCategory'] = $db->getItemFromDB("SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = {$parent_category}");
        $parent_category_businessType = $db->getItemFromDB("SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = {$parent_category}");
        $itemHistory['iteMidCategory'] = $db->getItemFromDB("SELECT category_name FROM mypha_productcategory WHERE category_id = {$main_category}");
        $parent_category_businessTypeName = $db->getItemFromDB("SELECT business_type_name FROM finascop_business_type WHERE business_type_id = {$parent_category_businessType}");
        $itemHistory['categoryCombination'] = $parent_category_businessTypeName . ' > ' . $itemHistory['iteParentCategory'] . ' > ' . $itemHistory['iteMidCategory'];
        if (!empty($itemHistory)) {
            echo json_encode($itemHistory);
        }
        break;
    case 'getCategoryMap':
        //$main_category = $_POST['parent_category_id'];
        //$main_category = $db->getItemFromDB("SELECT main_category FROM mypha_productsubcategory WHERE sub_category_id = {$sub_category_id}");
        $parent_category = $_POST['parent_category_id'];

        $itemHistory['iteParentCategory'] = $db->getItemFromDB("SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id = {$parent_category}");
        $parent_category_businessType = $db->getItemFromDB("SELECT parent_category_businessType FROM mypha_productparent_category WHERE parent_category_id = {$parent_category}");
        //$itemHistory['iteMidCategory'] = $db->getItemFromDB("SELECT category_name FROM mypha_productcategory WHERE category_id = {$main_category}");
        $parent_category_businessTypeName = $db->getItemFromDB("SELECT business_type_name FROM finascop_business_type WHERE business_type_id = {$parent_category_businessType}");
        $itemHistory['categoryCombination'] = $parent_category_businessTypeName . ' > ' . $itemHistory['iteParentCategory'];
        if (!empty($itemHistory)) {
            echo json_encode($itemHistory);
        }
        break;
    case 'getBusinessType':


        $qry = "select business_type_id,business_type_name from " . FINASCOP_DB . "finascop_business_type where status= 1  order by business_type_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        // echo '{success:true, data:'.json_encode($data).'}';
        break;
    case 'getDepartment':
        if ($_POST['primaryBt'] > 0) {
            $primaryBt = $_POST['primaryBt'];
        } else {
            $primaryBt = 0;
        }
        $qry = "select parent_category_id,parent_category FROM mypha_productparent_category where status= 1 AND  parent_category_businessType = {$primaryBt}  order by parent_category";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getCategory':
        if ($_POST['department'] > 0) {
            $primaryBt = $_POST['department'];
        } else {
            $primaryBt = 0;
        }
        $qry = "select category_id,category_name FROM mypha_productcategory where status= '1' AND  parent_category = {$primaryBt}  order by category_name";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
}
