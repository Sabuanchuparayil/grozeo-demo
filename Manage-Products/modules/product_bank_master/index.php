<?php

function pdtcurlCall($url, $method)
{
    $curl = curl_init();
    curl_setopt_array(
        $curl,
        array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => 0
        )
    );
    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        return json_decode(curl_error($curl));
    }
    curl_close($curl);
    return json_decode($response);
}
switch ($op) {
    case 'getgs1Brands':
        $qry = "select id,brandName from gs1_brand_source WHERE isMapped = 1 order by brandName";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getgs1Company':
        $qry = "select id,gcp from gs1_company order by id";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getgs1Category':
        $qry = "select id,categoryName from gs1_category order by categoryName";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getgs1SubCategory':
        $category = $_POST['category'];
        if ($category > 0) {
            $qry = "select id,subCategoryName from gs1_subCategory WHERE categoryId = {$category} order by subCategoryName";
        }

        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'listImportedCategory':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'gs1_category' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;

        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $cond .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                }
            }
        }

        $countQuery = "SELECT COUNT(id) FROM gs1_category  {$cond} ";
        $count = $db->getItemFromDB($countQuery);

        $qry = "SELECT * FROM gs1_category {$cond}  ORDER BY {$sort} {$dir} LIMIT {$rec_start}, {$rec_limit}";
        $data = $db->getMultipleData($qry, true);

        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listImportedSubCategory':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'subCategoryName' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;

        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $cond .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM gs1_subCategory inner join gs1_category on gs1_subCategory.id = categoryId   {$cond} ";
        $count = $db->getItemFromDB($countQuery);

        $qry = "SELECT gs1_subCategory.id,categoryName,subCategoryName FROM gs1_subCategory inner join gs1_category on gs1_category.id = categoryId {$cond}  ORDER BY {$sort} {$dir} LIMIT {$rec_start}, {$rec_limit}";
        $data = $db->getMultipleData($qry, true);

        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listImportedCompany':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'companyName' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;

        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $cond .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM gs1_company {$cond} ";
        $count = $db->getItemFromDB($countQuery);

        $qry = "SELECT * FROM gs1_company {$cond}  ORDER BY {$sort} {$dir} LIMIT {$rec_start}, {$rec_limit}";
        $data = $db->getMultipleData($qry, true);

        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listImportedBrand':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'brandName' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $cond = "WHERE 1=1 ";

        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {

                    $cond .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM (SELECT gs1_brand_source.id,brandName,gpcCode,isEnabled,isMapped,
        CASE WHEN isEnabled = 1 THEN 'Yes' WHEN isEnabled = 0 THEN 'No' END AS enableStatus,FirstName AS assignedTO FROM gs1_brand_source 
         LEFT JOIN user_brands ON brandId = gs1_brand_source.id 
        LEFT JOIN finascop_usr_profile ON finascop_usr_profile.UserId = user_brands.UserId  ) as cnt {$cond}";
        $count = $db->getItemFromDB($countQuery);


        $qry = "SELECT * FROM (SELECT gs1_brand_source.id,brandName,gpcCode,isEnabled,isMapped,
        CASE WHEN isEnabled = 1 THEN 'Yes' WHEN isEnabled = 0 THEN 'No' END AS enableStatus,FirstName AS assignedTO FROM gs1_brand_source 
         LEFT JOIN user_brands ON brandId = gs1_brand_source.id 
        LEFT JOIN finascop_usr_profile ON finascop_usr_profile.UserId = user_brands.UserId ) AS list {$cond}  ORDER BY gs1_brand_source.id DESC LIMIT {$rec_start}, {$rec_limit}";
        $data = $db->getMultipleData($qry, true);

        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listImportedProductBank':
        $rec_limit = empty($_POST['limit']) ? 26 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $cond = "where 1=1  ";
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $cond .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                }
            }
        }
        if (($_POST['category'] > 0 && $_POST['subcategory'] > 0) || ($_POST['brand'] > 0)) {
            if ($_POST['category'] > 0 && $_POST['subcategory'] > 0)
                $cond .= " and categoryId = {$_POST['category']} and subCategoryId = {$_POST['subcategory']} ";
            if ($_POST['brand'] > 0)
                $cond .= " and brandId = {$_POST['brand']}  ";
            $countQuery = "SELECT COUNT(*) FROM gs1_products_source  {$cond} ";
            $count = $db->getItemFromDB($countQuery);

            $listQuery = "SELECT * FROM gs1_products_source {$cond}  ORDER BY {$sort} {$dir} LIMIT {$rec_start},{$rec_limit}";
            //$data = $db->getMultipleData($qry, true);
        }

        $db->printGridJson($countQuery, $listQuery);
        /*if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';*/
        break;
    case 'prdctdetailsView':
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id) {
            $data = $db->getFromDB("SELECT * FROM gs1_products_source msc  WHERE id= " . $id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'importDataFromProductBankOld':

        $importType = $_POST['importType'];
        $category = $_POST['category'];
        $brand = $_POST['brand'];
        $subCategory = $_POST['subCategory'];
        $updateExisting = $_POST['updateProducts'];

        require_once(ROOT . '/classes/gs1-data.php');
        $importData = new Gs1Data();


        switch ($importType) {
            case 'ProductNew':
                if ($category > 0 && $subCategory > 0) {
                    $dataresponse = $importData->getProductsPageDetails($category, $subCategory);

                    $result = json_decode($dataresponse);


                    $gs1pdtDetails['category'] = $category;
                    $gs1pdtDetails['subCategory'] = $subCategory;
                    $gs1pdtDetails['currentPage'] = $result->currentPage;
                    $gs1pdtDetails['totalResults'] = $result->totalResults;
                    $gs1pdtDetails['totalPage'] = $result->totalPage;
                    $gs1pdtDetails['resultsPerPage'] = $result->resultsPerPage;
                    $gs1pdtDetails['currentPageResults'] = $result->currentPageResults;

                    $unique = $db->getItemFromDB("SELECT id FROM gs1_product_insert_log_source where category = {$category} and subCategory = {$subCategory}");
                    //$url = 'http://productindia.admin.velosit.in/classes/gs1-data-api.php?catid='.$category.'&subcatid='.$subCategory.'&update='.$updateExisting.'&logid='.$logId;
                    $url = 'http://productindia.admin.velosit.in/classes/gs1-source.php?catid=' . $category . '&subcatid=' . $subCategory;
                    if ($unique > 0) {
                        $logId = $unique;
                        $status = $db->perform('gs1_product_insert_log_source', $gs1pdtDetails, 'update', " id = {$logId}");
                        $isComplete = $db->getFromDB("SELECT isComplete,insertedData,totalResults FROM gs1_product_insert_log_source where id = {$logId}", true);
                        if ($isComplete['isComplete'] == 0) {
                            $msg = "Only {$isComplete['insertedData']} data inserted from {$isComplete['totalResults']} total.";
                            pdtcurlCall($url, 'GET');
                            echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
                            //exit();
                        } else if ($isComplete['isComplete'] == 1) {
                            $msg = "Data is already imported";
                            echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
                            exit();
                        }
                    } else {
                        $gs1pdtDetails['type'] = "Product";
                        $gs1pdtDetails['startDate'] = date("Y-m-d H:i:s");
                        $status = $db->perform('gs1_product_insert_log_source', $gs1pdtDetails);
                        $logId = $db->insert_id();
                        pdtcurlCall($url, 'GET');
                    }
                } else {
                    $msg = "Choose all Filters and Proceed";
                    echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
                    exit();
                }
                break;
            case 'Product':

                if ($category > 0 && $subCategory > 0) { //&& $brand > 0
                    $db->query("TRUNCATE TABLE gs1_log");
                    $dataresponse = $importData->getProductsPageDetails($category, $subCategory);

                    $result = json_decode($dataresponse);


                    $gs1pdtDetails['category'] = $category;
                    $gs1pdtDetails['subCategory'] = $subCategory;
                    $gs1pdtDetails['currentPage'] = $result->currentPage;
                    $gs1pdtDetails['totalResults'] = $result->totalResults;
                    $gs1pdtDetails['totalPage'] = $result->totalPage;
                    $gs1pdtDetails['resultsPerPage'] = $result->resultsPerPage;
                    $gs1pdtDetails['currentPageResults'] = $result->currentPageResults;

                    $unique = $db->getItemFromDB("SELECT id FROM gs1_product_insert_log_source where category = {$category} and subCategory = {$subCategory}");
                    if ($unique > 0) {
                        $logId = $unique;
                        $status = $db->perform('gs1_product_insert_log_source', $gs1pdtDetails, 'update', " id = {$logId}");
                        $isComplete = $db->getFromDB("SELECT isComplete,insertedData,totalResults FROM gs1_product_insert_log_source where id = {$logId}", true);
                        if ($isComplete['isComplete'] == 0) {
                            $msg = "Only {$isComplete['insertedData']} data inserted from {$isComplete['totalResults']} total.";
                            $response = $importData->getProducts($db, $category, $subCategory, 0, $updateExisting, $logId);
                            echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
                            //exit();
                        } else if ($isComplete['isComplete'] == 1) {
                            $msg = "Data is already imported";
                            echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
                            exit();
                        }
                    } else {
                        $gs1pdtDetails['type'] = "Product";
                        $gs1pdtDetails['startDate'] = date("Y-m-d H:i:s");
                        $status = $db->perform('gs1_product_insert_log_source', $gs1pdtDetails);
                        $logId = $db->insert_id();
                        $response = $importData->getProducts($db, $category, $subCategory, 0, $updateExisting, $logId);
                    }
                } else {
                    $msg = "Choose all Filters and Proceed";
                    echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
                    exit();
                }
                break;
            case 'Category':
                $response = $importData->getCategories($db, $updateExisting);
                break;
            case 'Sub Category':
                $response = $importData->getSubCategories($db, $category, $updateExisting);
                break;
            case 'Company':
                $response = $importData->getCompanies($db, $updateExisting);
                break;
        }
        echo $response;

        break;
    case 'listImportedLog':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'startDate' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $cond = " WHERE 1=1 ";

        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    if ($val['field'] == 'importStatus') {
                        switch ($field['data']['value']) {
                            case 'Importing Data':
                                $cond .= " and isComplete <> 1 ";
                                break;
                            case 'Import Completed':
                                $cond .= " and isComplete = 1 ";
                                break;
                            default:
                                $cond .= "   ";
                                break;
                        }
                    } else {
                        $cond .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                    }
                }
            }
        }
        //(SELECT GROUP_CONCAT(brandname) FROM gs1_brand_company_source WHERE prefix = gcpID) as relatedBrands
        $qry = "SELECT 
    gs1_product_insert_log_source.id AS id,
    category,isReconciled,gcpID,(SELECT companyName FROM gs1_company WHERE gcp = gcpID LIMIT 1) as manufactureName,
    categoryName,'-' as relatedBrands,
    subCategory,
    subCategoryName,
    totalResults,
    totalPage,
    resultsPerPage,IF(updatedData > 0,updatedData,insertedData) AS insertedData,
isComplete,
startDate,
endDate,IF(isReconciled = 1,'Yes','No') AS isReconciledStatus ,IF(
    isComplete = 1,
    'Import Completed',
    IF(TIMESTAMPDIFF(MINUTE,(
        SELECT
            importedOn
        FROM
            gs1_products_source
        WHERE
            categoryId = gs1_product_insert_log_source.category AND subCategoryId = gs1_product_insert_log_source.subCategory
        ORDER BY
            id
        DESC
    LIMIT 1
    ),NOW()) < 5, 'Importing Data', 'Importing Data')) AS importStatus
FROM
    gs1_product_insert_log_source
left JOIN gs1_category ON gs1_category.id = gs1_product_insert_log_source.category
left JOIN gs1_subCategory ON gs1_subCategory.id = gs1_product_insert_log_source.subCategory  ";
        $countQuery = "SELECT COUNT(*) FROM ({$qry}) AS countLog {$cond} ";
        $count = $db->getItemFromDB($countQuery);

        $listqry = "SELECT * FROM ({$qry}) AS listLog {$cond}  ORDER BY {$sort} {$dir} LIMIT {$rec_start}, {$rec_limit}";
        $data = $db->getMultipleData($listqry, true);

        if (!empty($data)) {
            /*foreach($datas as $data){
				$data['insertedData'] = $db->getItemFromDB("SELECT COUNT(id) FROM gs1_products_source WHERE categoryId = {$data['category']} AND subCategoryId = {$data['subCategory']}");
				$data['importedOn'] = $db->getItemFromDB("SELECT importedOn FROM gs1_products_source WHERE categoryId = {$data['category']} AND subCategoryId = {$data['subCategory']} ORDER BY id DESC LIMIT 1");
			}*/
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'reconcileProduct':
        $category = $_POST['category'];
        $subCategory = $_POST['subCategory'];
        $gcpID = $_POST['gcpID'];

        if (!empty($gcpID)) {
            $isReconciled = $db->getItemFromDB("SELECT isReconciled FROM gs1_product_insert_log_source WHERE gcpID = '{$gcpID}'");
        } else {
            $isReconciled = $db->getItemFromDB("SELECT isReconciled FROM gs1_product_insert_log_source WHERE category = {$category} AND subCategory = {$subCategory}");
        }

        if ($isReconciled == 0) {
            $db->query('begin');
            if (!empty($gcpID)) {

                $companyName = $db->getItemFromDB("SELECT companyname FROM gs1_brand_company_source WHERE prefix = '{$gcpID}'");

                $status = $db->query("UPDATE gs1_products_source SET isValid = 0 WHERE company_detail LIKE '%{$companyName}%' AND brandId = 0 ");
                $status = $db->query("UPDATE gs1_products_source SET isValid = 0 WHERE company_detail LIKE '%{$companyName}%' AND hs_code = '' ");

                $status = $db->query("
UPDATE gs1_products_source AS b INNER JOIN gs1_product_mrp_source AS g ON b.id = g.productId SET isValid = 0 WHERE  company_detail LIKE '%{$companyName}%' AND mrp = '' ");
                $status = $db->query("
UPDATE gs1_products_source AS b INNER JOIN gs1_product_mrp_source AS g ON b.id = g.productId SET isValid = 0 WHERE  company_detail LIKE '%{$companyName}%' AND mrp = 0 ");
                $status = $db->query("UPDATE gs1_products_source SET isValid = 0  WHERE company_detail LIKE '%{$companyName}%' AND packaging_type = 'Intermediate'");
                $status = $db->query("UPDATE gs1_product_insert_log_source SET isReconciled = 1 WHERE gcpID = '{$gcpID}' ");
            } else {
                $status = $db->query("UPDATE gs1_products_source SET isValid = 0 WHERE categoryId = {$category} AND subCategoryId = {$subCategory} AND (brandId = 0 OR hs_code = 0)");
                $status = $db->query("
    UPDATE gs1_products_source AS b INNER JOIN gs1_product_mrp_source AS g ON b.id = g.productId SET isValid = 0 WHERE  categoryId = {$category} AND subCategoryId = {$subCategory} AND mrp = '' ");
                $status = $db->query("UPDATE gs1_products_source SET isValid = 0  WHERE categoryId = {$category} AND subCategoryId = {$subCategory} AND packaging_type = 'Intermediate'");
                $status = $db->query("UPDATE gs1_product_insert_log_source SET isReconciled = 1 WHERE category = {$category} AND subCategory = {$subCategory} ");
            }

            $status = $db->query('commit');
        } else {
            echo "{success: false,msg:'Already products get reconciled'}";
            exit();
        }

        if ($status == 1) {
            echo "{success: true,msg:'Product Reconciled'}";
        } else {
            echo "{success: false,msg: 'Reconciliation Successful'}";
        }

        break;
    case 'reconciledProduct':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'id' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $category = $_POST['category'];
        $subcategory = $_POST['subcategory'];
        $manufactureName = $_POST['manufactureName'];
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    if ($val['field'] == 'importStatus') {
                        switch ($field['data']['value']) {
                            case 'Importing Data':
                                $cond .= " and isComplete = 0 ";
                                break;
                            case 'Import Completed':
                                $cond .= " and isComplete = 1 ";
                                break;
                            default:
                                $cond .= "   ";
                                break;
                        }
                    } else {
                        $cond .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                    }
                }
            }
        }
        //categoryId = {$category} and subCategoryId = {$subcategory}
        $countQuery = "SELECT COUNT(*) FROM gs1_products_source where  company_detail LIKE '%{$manufactureName}%' and isValid = 0  {$cond}";
        $count = $db->getItemFromDB($countQuery);

        $qry = "SELECT * FROM gs1_products_source where company_detail LIKE '%{$manufactureName}%' and isValid = 0 {$cond}  ORDER BY {$sort} {$dir} LIMIT {$rec_start},{$rec_limit}";
        $data = $db->getMultipleData($qry, true);

        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';

        break;
    case 'checkImport':
        $lastImport = $db->getItemFromDB("SELECT importedOn FROM gs1_products_source ORDER BY id DESC LIMIT 1");
        //print_r($lastImport);
        $to_time = strtotime(date("Y-m-d H:i:s"));
        $from_time = strtotime($lastImport);
        $timeDiff =  round(abs($to_time - $from_time) / 60, 2);
        //print_r($timeDiff);
        if ($timeDiff > 3) {
            echo '{"success":true,"valid":true}';
        } else {
            echo '{"success":false,"valid":false,"msg":"Please wait for 3 minutes and then continue with the import."}';
        }
        break;
    case 'setBrandEnable':
        $id = $_POST['id'];
        $isEnabled = $_POST['isEnabled'];
        $ispending = 0;
        $from = $_POST['from'];

        $brandName = $db->getItemFromDB("SELECT brandName FROM gs1_brand_source WHERE id = '{$id}'");

        //$brandName = mysqli_real_escape_string($db->linker(), $brandName);
        if (OPERATING_COUNTRY == 'INDIA') {
            $prefixDetails = $db->getMultipleData("SELECT id,companyname,prefix FROM gs1_brand_company_source WHERE brandname = '" . mysqli_real_escape_string($db->linker(), $brandName) . "'", true);
            if ($prefixDetails[0]['id'] > 0) {
                foreach ($prefixDetails as $prefixDetail) {
                    $isCompleted = $db->getItemFromDB("SELECT isComplete FROM gs1_product_insert_log_source WHERE gcpID = '{$prefixDetail['prefix']}'");
                    $isReconciled = $db->getItemFromDB("SELECT isReconciled FROM gs1_product_insert_log_source WHERE gcpID = '{$prefixDetail['prefix']}'");
                    if ($isReconciled == 1) {
                        $ispending = 1;
                    }
                }



                $db->query('begin');
                $defaultCount = $db->getItemFromDB("SELECT COUNT(*) FROM gs1_brand_source WHERE isEnabled = 1 ");
                if ($isEnabled == 1) {
                    $status = $db->query("UPDATE gs1_brand_source SET isEnabled = 0 WHERE id = {$id}");
                } else {
                    if ($ispending == 0) {
                        $msg = "Enable brand after reconcilation.";
                        echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
                        exit();
                    } else {
                        $status = $db->query("UPDATE gs1_brand_source SET isEnabled = 1 WHERE id = {$id}");
                    }
                }

                $status = $db->query('commit');
                if ($status) {
                    $msg = "Brand updated.";
                    echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
                } else {
                    $msg = "Error Occured";
                    echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
                }
            } else {
                $msg = "This brand is not available in Activate Brands";
                echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
            }
        } else {
            $ispending = 1;
            $db->query('begin');
            $defaultCount = $db->getItemFromDB("SELECT COUNT(*) FROM gs1_brand_source WHERE isEnabled = 1 ");
            if ($isEnabled == 1) {
                $status = $db->query("UPDATE gs1_brand_source SET isEnabled = 0 WHERE id = {$id}");
            } else {
                if ($ispending == 0) {
                    $msg = "Enable brand after reconcilation.";
                    echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
                    exit();
                } else {
                    $status = $db->query("UPDATE gs1_brand_source SET isEnabled = 1 WHERE id = {$id}");
                }
            }

            $status = $db->query('commit');
            if ($status) {
                $msg = "Brand updated.";
                echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
            } else {
                $msg = "Error Occured";
                echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
            }
        }
        break;
    case 'listAvailableBrands':
        $rec_limit = empty($_POST['limit']) ? 500 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $rec_sort = empty($data['sort']) ? 'bs.id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' WHERE 1=1 and isEnabled = 1 and isMapped > 0 and ub.brandId is null';

        $data = $_POST;

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(bs.id) FROM gs1_brand_source bs left join user_brands ub ON bs.id = ub.brandId
LEFT JOIN
    gs1_products_extension ge ON bs.id = ge.brandId AND ge.retailCategory > 0  {$filter_part} GROUP BY
    bs.id,
    bs.brandName";
        $listQuery = "SELECT bs.id as id,brandName,COUNT(ge.id) AS pdtCount FROM gs1_brand_source bs  left join user_brands ub ON bs.id = ub.brandId
LEFT JOIN
    gs1_products_extension ge ON bs.id = ge.brandId AND ge.retailCategory > 0  {$filter_part}   GROUP BY
    bs.id,
    bs.brandName ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit ";
        $db->printGridJson($countQuery, $listQuery);
        break;

    case 'listUserMappedBrands':
        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = " WHERE 1=1 and isMapped > 0 and UserId = {$_POST['userId']}";

        $data = $_POST;

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(bs.id) FROM
    gs1_brand_source bs
INNER JOIN
    user_brands ub ON bs.id = ub.brandId
LEFT JOIN
    gs1_products_extension ge ON bs.id = ge.brandId AND ge.retailCategory > 0   {$filter_part} GROUP BY
    bs.id,
    bs.brandName";
        $listQuery = "SELECT bs.id as id,brandName,COUNT(ge.id) AS pdtCount
FROM
    gs1_brand_source bs
INNER JOIN
    user_brands ub ON bs.id = ub.brandId
LEFT JOIN
    gs1_products_extension ge ON bs.id = ge.brandId AND ge.retailCategory > 0  {$filter_part}  GROUP BY
    bs.id,
    bs.brandName ORDER BY $rec_sort $rec_sort_dir ";
        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'mapBrandToUser':
        $brandarr = $_POST['brandarr'];
        $userId = $_POST['userId'];
        $itemdecode = json_decode($brandarr);
        $itemcount = count($itemdecode);
        for ($i = 0; $i < $itemcount; $i++) {
            $brndMapData["brandId"] = $itemdecode[$i];
            $brndMapData["UserId"] = $userId;
            $status = $db->perform('user_brands', $brndMapData);
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Saved Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'listSkippedProductBank':
        $rec_limit = empty($_POST['limit']) ? 26 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $cond = "where 1=1  AND isArchived = 1 AND productId = 0 ";
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $cond .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                }
            }
        }
        if ($_POST['category'] > 0 && $_POST['subcategory'] > 0) {
            $cond .= " and categoryId = {$_POST['category']} and subCategoryId = {$_POST['subcategory']} ";
            $countQuery = "SELECT COUNT(*) FROM gs1_products_extension  {$cond} ";
            $count = $db->getItemFromDB($countQuery);

            $listQuery = "SELECT * FROM gs1_products_extension {$cond}  ORDER BY {$sort} {$dir} LIMIT {$rec_start},{$rec_limit}";
            //$data = $db->getMultipleData($qry, true);
        }

        $db->printGridJson($countQuery, $listQuery);
        /*if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';*/
        break;
    case 'checProductValidity':
        $category = $_POST['category'];
        $subCategory = $_POST['subCategory'];
        $gcpID = $_POST['gcpID'];
        $msg = "";
        if (!empty($gcpID)) {
            $isReconciled = $db->getItemFromDB("SELECT isReconciled FROM gs1_product_insert_log_source WHERE gcpID = '{$gcpID}'");
        } else {
            $isReconciled = $db->getItemFromDB("SELECT isReconciled FROM gs1_product_insert_log_source WHERE category = {$category} AND subCategory = {$subCategory}");
        }
        if ($isReconciled == 0) {

            if (!empty($gcpID)) {
                $companyName = $db->getItemFromDB("SELECT companyname FROM gs1_brand_company_source WHERE prefix = '{$gcpID}'");
                $missedMRPData = $db->getItemFromDB("SELECT COUNT(productId) FROM gs1_product_mrp_source INNER JOIN gs1_products_source ON gs1_products_source.id = productId WHERE company_detail LIKE '%{$companyName}%' AND mrp = ''");

                $invalidProducts = $db->getItemFromDB("SELECT COUNT(*) FROM  gs1_products_source   WHERE company_detail LIKE '%{$companyName}%' AND (brandId = 0 || hs_code = 0)");

                $intermediateData = $db->getItemFromDB("SELECT COUNT(*) FROM gs1_products_source   WHERE company_detail LIKE '%{$companyName}%' AND packaging_type = 'Intermediate'");
            } else {
                $missedMRPData = $db->getItemFromDB("SELECT COUNT(productId) FROM gs1_product_mrp_source INNER JOIN gs1_products_source ON gs1_products_source.id = productId WHERE categoryId = {$category} AND subCategoryId = {$subCategory} AND mrp = ''");

                $invalidProducts = $db->getItemFromDB("SELECT COUNT(*) FROM  gs1_products_source   WHERE categoryId = {$category} AND subCategoryId = {$subCategory} AND (brandId = 0 || hs_code = 0)");

                $intermediateData = $db->getItemFromDB("SELECT COUNT(*) FROM gs1_products_source   WHERE categoryId = {$category} AND subCategoryId = {$subCategory} AND packaging_type = 'Intermediate'");
            }


            if ($missedMRPData > 0) {
                $msg .= "MRP missing for {$missedMRPData} products.";
            }
            if ($invalidProducts > 0) {
                $msg .= "Invalid data for {$invalidProducts} products.";
            }

            if ($intermediateData > 0) {
                $msg .= "{$intermediateData} Intermediate products.";
            }

            if ($missedMRPData > 0 || $invalidProducts > 0 || $intermediateData > 0) {
                echo "{success: true,msg:'{$msg}'}";
            } else {
                $db->query('begin');
                if (!empty($gcpID)) {
                    $status = $db->query("UPDATE gs1_product_insert_log_source SET isReconciled = 1 WHERE gcpID = '{$gcpID}' ");
                } else {
                    $status = $db->query("UPDATE gs1_product_insert_log_source SET isReconciled = 1 WHERE category = {$category} AND subCategory = {$subCategory} ");
                }
                $status = $db->query('commit');
                if ($status == 1) {
                    echo "{success: false,msg:'Reconciliation Successful'}";
                }
            }
        } else {
            echo "{success: false,msg:'Already Reconciled'}";
        }

        break;
    case 'removeBrandFromUser':
        $brandId = $_POST['brandId'];
        $userId = $_POST['userId'];
        $db->query('begin');
        $logdata['userId'] = $userId;
        $logdata['brandId'] = $brandId;
        $status = $db->perform('user_brand_remove_log', $logdata);
        $delqry = "DELETE FROM user_brands WHERE brandId = {$brandId} AND UserId = {$userId}";
        $status = $db->query($delqry);
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,msg:'Brand removed from user'}";
        } else {
            echo "{success: false,msg: 'Reconciliation Successful'}";
        }
        break;
    case 'listMappedBrand':
        $rec_limit = empty($_POST['limit']) ? 22 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'brandName' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $cond = "WHERE 1=1 AND isEnabled = 1 AND isMapped = 1";

        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {

                    $cond .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM (SELECT gs1_brand_source.id,brandName,gs1_brand_source.id as companyId,companyName,isEnabled,isMapped,
        CASE WHEN isEnabled = 1 THEN 'Yes' WHEN isEnabled = 0 THEN 'No' END AS enableStatus,FirstName AS assignedTO 
        FROM gs1_brand_source LEFT JOIN user_brands ON user_brands.brandId = gs1_brand_source.id 
        INNER JOIN finascop_usr_profile ON finascop_usr_profile.UserId = user_brands.UserId 
        LEFT JOIN gs1_company c ON c.gcp LIKE CONCAT('%', gpcCode, '%') GROUP BY (gs1_brand_source.id) ) as cnt {$cond} ";
        $count = $db->getItemFromDB($countQuery);

        $qry = "SELECT * FROM (SELECT gs1_brand_source.id,brandName,gs1_brand_source.id AS companyId,companyName,isEnabled,isMapped,
        CASE WHEN isEnabled = 1 THEN 'Yes' WHEN isEnabled = 0 THEN 'No' END AS enableStatus,FirstName AS assignedTO 
        FROM gs1_brand_source LEFT JOIN user_brands ON user_brands.brandId = gs1_brand_source.id 
        INNER JOIN finascop_usr_profile ON finascop_usr_profile.UserId = user_brands.UserId 
        LEFT JOIN gs1_company c ON c.gcp LIKE CONCAT('%', gpcCode, '%') GROUP BY (gs1_brand_source.id)) AS list {$cond}  ORDER BY {$sort} {$dir} LIMIT {$rec_start}, {$rec_limit}";
        $data = $db->getMultipleData($qry, true);

        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'importDataFromProductBank':

        $importType = $_POST['importType'];
        $category = $_POST['category'];
        $brand = $_POST['brand'];
        $subCategory = $_POST['subCategory'];
        $updateExisting = $_POST['updateProducts'];

        require_once(ROOT . '/classes/gs1-data.php');
        require_once(ROOT . '/classes/gs1-source.php');
        $importMaster = new Gs1Data();
        $importData = new Gs1Source();


        switch ($importType) {

            case 'Product':

                if ($category > 0 && $subCategory > 0) { //&& $brand > 0
                    $db->query("TRUNCATE TABLE gs1_log");
                    //$dataresponse = $importData->getProductsPageDetails($category, $subCategory);

                    //$result = json_decode($dataresponse);


                    /*$gs1pdtDetails['category'] = $category;
                    $gs1pdtDetails['subCategory'] = $subCategory;
                    $gs1pdtDetails['currentPage'] = $result->currentPage;
                    $gs1pdtDetails['totalResults'] = $result->totalResults;
                    $gs1pdtDetails['totalPage'] = $result->totalPage;
                    $gs1pdtDetails['resultsPerPage'] = $result->resultsPerPage;
                    $gs1pdtDetails['currentPageResults'] = $result->currentPageResults;*/

                    $unique = $db->getItemFromDB("SELECT id FROM gs1_product_insert_log_source where category = {$category} and subCategory = {$subCategory}");
                    $response = $importData->getProducts($db, $category, $subCategory, 0);
                } else if ($brand > 0) {
                    $gcpID = (int)$brand;
                    $response = $importData->getProducts($db, 0, 0, $gcpID);
                    /*$msg = "Yet to implement";
                    echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
                    exit();*/
                } else {
                    $msg = "Choose all Filters and Proceed";
                    echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
                    exit();
                }
                break;
            case 'Category':
                $response = $importMaster->getCategories($db, $updateExisting);
                break;
            case 'Sub Category':
                $response = $importMaster->getSubCategories($db, $category, $updateExisting);
                break;
            case 'Company':
                $response = $importMaster->getCompanies($db, $updateExisting);
                break;
        }
        echo $response;

        break;
    case 'getgs1CompanyBrand':
        $qry = "select id,prefix,CONCAT(brandname,' by ',companyname) AS brand from gs1_brand_company_source where isEnabled = 1  order by id";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;

    case 'listBrandCompany':
        $rec_limit = empty($_POST['limit']) ? 26 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $cond = "where 1=1 AND status = 'Published' ";
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    if ($field['field'] == 'brandCompany') {
                        $cond .= " and (brandname LIKE '%{$field['data']['value']}%') ";
                    } else {
                        $cond .= " and ({$field['field']} LIKE '{$field['data']['value']}%') ";
                    }
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM gs1_brand_company_source  {$cond} ";
        $count = $db->getItemFromDB($countQuery);

        $listQuery = "SELECT id,CONCAT(brandname,' by ',companyname) AS brandCompany,prefix,companyname,brandname,productCount,status,isEnabled FROM gs1_brand_company_source {$cond}  ORDER BY {$sort} {$dir} LIMIT {$rec_start},{$rec_limit}";


        $db->printGridJson($countQuery, $listQuery);

        break;
    case 'bcdetailsView':
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id > 0) {
            $data = $db->getFromDB("SELECT id,prefix,companyname,brandname,productCount,status FROM gs1_brand_company_source msc  WHERE id= " . $id, true);
            $data['success'] = true;
            echo json_encode($data);
        }
        break;
    case 'listRelatedBrand':
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'name' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $prefix = $_POST['prefix'];
        $id = $_POST['id'];
        $cond = "where 1=1 AND status = 'Published' AND prefix LIKE '%{$prefix}%' AND id NOT IN ({$id})";
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {


                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $cond .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $cond .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM gs1_brand_company_source  {$cond} ";
        $count = $db->getItemFromDB($countQuery);

        $listQuery = "SELECT id,prefix,companyname,brandname,productCount,status FROM gs1_brand_company_source {$cond}  ORDER BY {$sort} {$dir} ";


        $db->printGridJson($countQuery, $listQuery);
        break;
    case 'enablerelatedBrand':

        $prefix = $_POST['prefix'];


        $db->query('begin');
        $defaultCount = $db->getItemFromDB("SELECT COUNT(*) FROM gs1_brand_company_source WHERE isEnabled = 1 AND prefix LIKE '%{$prefix}%' ");
        if ($defaultCount == 0) {
            $status = $db->query("UPDATE gs1_brand_company_source SET isEnabled = 1 WHERE prefix LIKE '%{$prefix}%' AND isEnabled = 0 ");
        } else {
            $msg = "Already activated.";
            echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
            exit();
        }

        $status = $db->query('commit');
        if ($status) {
            $msg = "Brand Activated.";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":false,"valid":false,"msg":"' . $msg . '"}';
        }
        break;
    case 'getgs1AllBrands':
        $qry = "select id,brandName from gs1_brand_source  order by brandName";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
}
