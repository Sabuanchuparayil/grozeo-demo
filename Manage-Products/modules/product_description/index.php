<?php
global $db, $parentdb;
switch ($op) {
    case 'getRelatedData':
        $type = $_POST['type'];
        switch ($type) {
            case 1:
                $qry = "select sub_category_id as id,sub_category as name from mypha_productsubcategory WHERE status = 1 order by sub_category";
                break;
            case 2:
                $qry = "select brand_id as id,brand_name as name from mypha_productbrands WHERE status = 1 order by brand_name";
                break;
        }

        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';
        break;
    case 'getProductDetails':
        $selectedValue = $_POST['selectedValue'];
        $selectType = $_POST['selectType'];
        switch ($selectType) {
            case 'Sub Category':
                $cond = " AND product_category  = {$selectedValue}";
                break;
            case 'Brand':
                $cond = " AND pdt_brand = {$selectedValue}";
                break;
        }
        $qry = "SELECT stit_ID,stit_SKU,stit_long_description,pdt_brand,item_name,brand_name,product_category,sub_category,stit_itemId,stit_product_variant,stit_quantity 
        FROM finascop_stock_itemmaster 
        INNER JOIN mypha_productbrands ON brand_id = pdt_brand 
        INNER JOIN mypha_productsubcategory ON sub_category_id = product_category 
        INNER JOIN finascop_stock_itemmastername ON itemname_id = stit_itemId WHERE stit_status = 1 AND stit_long_description = '' {$cond} order by stit_ID ASC LIMIT 1";
        $data = $db->getFromDB($qry, true);
        $stit_ID = $data['stit_ID'];
        $details = "SKU: {$data['stit_SKU']},Brand: {$data['brand_name']},Product Master: {$data['item_name']},Variant: {$data['stit_product_variant']},Unit: {$data['stit_quantity']} of Category: {$data['sub_category']}";
        if ($data['stit_ID'] > 0) {
            echo '{"success":true,"data":' . json_encode($data) . '}';
        } else {
            echo '{"success":true,"data":[]}';
        }

        break;
    case 'fetchDescription':
        $prdctId = $_POST['prdctId'];
        $prdctDetails = $_POST['prdctDetails'];
        $prdctSKU = $_POST['prdctSKU'];
        $productDetail = $db->getFromDB("SELECT stit_itemName,stit_brand_name,stit_category_name,stit_product_variant,stit_quantity,stit_Description FROM finascop_stock_itemmaster WHERE stit_ID = {$prdctId}", true);
        // Product information
        $productInfo = [
            'title' => $prdctSKU,
            'brand' => $productDetail['stit_brand_name'],
            'category' => $productDetail['stit_category_name'],
            'variant' => $productDetail['stit_product_variant'],
            'quantity' => $productDetail['stit_quantity'],
            'description' => $productDetail['stit_Description'],
            'targetAudience' => 'E-Commerce Customers'
        ];
        //$prompt = "Generate product description for: " . $productInfo['title'] . " of brand " . $productInfo['brand'] . "  under the category " . $productInfo['category'] . ". The target audience is " . $productInfo['targetAudience'] . ".";
        //$prompt = "Create an expanded product description. The content should be complete and concise. The content should not contain any links or external references: " . $productInfo['title'] . " of brand " . $productInfo['brand'] . "  under the category " . $productInfo['category'] . " with short description " .$productInfo['description'];
        $prompt = "Create an expanded product description for  our ecom portal with few hash tags and seo capable keywords on  " . '"' . $productInfo['title'] . '"' . " of brand " . $productInfo['brand'] . "  under the category " . $productInfo['category'] . " with short description " . $productInfo['description'] . ". Please make sure that the content is complete and concise, while does not contain any links or external references.";
        $fields['contents']['parts']['text'] = $prompt;

        $key = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'GOOGLE_KEY'");
        $aiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $key;
        $fields_string = json_encode($fields);
        //print_r($invoiceUrl . "/n");
        //print_r($fields_string . "/n");
        $opts = array(
            CURLOPT_URL => $aiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => count($fields),
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        );

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $response = json_decode($data);
        //print_r($response);
        echo '{"success":true,"data":' . $data . '}';
        break;
    case 'mapDescriptionToProducts':
        $prdctId = $_POST['prdctId'];
        $data['stit_long_description'] = $_POST['prdct_long_description'];
        $con = 'stit_ID=' . $prdctId;
        $data['updatedOn'] = date('Y-m-d H:i:s');
        $data['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $db->query('begin');
        $status = $db->perform("finascop_stock_itemmaster", $data, 'update', $con);
        $status = $db->query('commit');
        if ($status) {
            $message = 'Saved Successfully';
            echo "{success: true,stit_ID:{$prdctId},msg:'{$message}'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }
        break;
    case 'listProductsToMapDescription':
        $rec_limit = empty($_POST['limit']) ? 21 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'finascop_stock_itemmaster.stit_ID' : $sort;
        $dir = empty($dir) ? 'ASC' : $dir;
        $merchantId = $_POST['merchantId'];

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }
        }
        if ($merchantId > 0) {
            $countQuery = "SELECT COUNT(*) FROM (SELECT finascop_stock_itemmaster.stit_ID ,stit_itemName ,stit_SKU,stit_brand_name,IF(stit_StoreGroup>0,'Private','Brand') AS prdctType FROM finascop_stock_itemmaster 
        INNER JOIN finascop_stock_branch_inventory ON finascop_stock_branch_inventory.stit_id = finascop_stock_itemmaster.stit_ID 
        INNER JOIN finascop_branch ON br_ID = branch_id WHERE br_storeGroup = {$merchantId} AND stit_status = 1 AND (stit_long_description = '' OR stit_long_description IS NULL) {$cond} GROUP BY finascop_stock_itemmaster.stit_ID ) as groupedresult ";
            $listQuery = "SELECT finascop_stock_itemmaster.stit_ID ,stit_itemName ,stit_SKU,stit_brand_name,IF(stit_StoreGroup>0,'Private','Brand') AS prdctType FROM finascop_stock_itemmaster 
        INNER JOIN finascop_stock_branch_inventory ON finascop_stock_branch_inventory.stit_id = finascop_stock_itemmaster.stit_ID 
        INNER JOIN finascop_branch ON br_ID = branch_id WHERE br_storeGroup = {$merchantId} AND stit_status = 1 AND (stit_long_description = '' OR stit_long_description IS NULL) {$cond} GROUP BY finascop_stock_itemmaster.stit_ID ORDER BY {$sort} {$dir} limit $rec_start,$rec_limit";
        }

        $parentdb->printGridJson($countQuery, $listQuery);
        break;

    case 'generateDescription':
        $merchantId = $_POST['merchantId'];
        $enteredLimit = $_POST['enteredLimit'];
        if ($enteredLimit > 0)
            $limit = " LIMIT 0,{$enteredLimit}";
        else
            $limit = "";
        $prdctDetails = $parentdb->getMulipleData("SELECT finascop_stock_itemmaster.stit_ID as prdctId,stit_SKU,
        stit_itemName,stit_brand_name,stit_category_name,stit_product_variant,stit_quantity,stit_Description
        stit_StoreGroup,IF(stit_StoreGroup>0,'Private','Brand') AS prdctType FROM finascop_stock_itemmaster 
        INNER JOIN finascop_stock_branch_inventory ON finascop_stock_branch_inventory.stit_id = finascop_stock_itemmaster.stit_ID 
        INNER JOIN finascop_branch ON br_ID = branch_id WHERE br_storeGroup = {$merchantId} AND stit_status = 1 AND (stit_long_description = '' OR stit_long_description IS NULL) GROUP BY finascop_stock_itemmaster.stit_ID ORDER BY finascop_stock_itemmaster.stit_ID ASC {$limit}", true);
        if (empty($prdctDetails[0]['prdctId'])) {
            echo "{success: false,msg: 'No records available' }";
            exit();
        }

        foreach ($prdctDetails as $productDetail) {
            // Product information
            if ($productDetail['prdctId'] > 0) {
                $productInfo = [
                    'title' => $productDetail['stit_SKU'],
                    'brand' => $productDetail['stit_brand_name'],
                    'category' => $productDetail['stit_category_name'],
                    'variant' => $productDetail['stit_product_variant'],
                    'quantity' => $productDetail['stit_quantity'],
                    'description' => $productDetail['stit_Description'],
                    'targetAudience' => 'E-Commerce Customers'
                ];
                $prompt = "Create an expanded product description for  our ecom portal with few hash tags and seo capable keywords on  " . '"' . $productInfo['title'] . '"' . " of brand " . $productInfo['brand'] . "  under the category " . $productInfo['category'] . " with short description " . $productInfo['description'] . ". Please make sure that the content is complete and concise, while does not contain any links or external references.";
                $fields['contents']['parts']['text'] = $prompt;

                $key = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'GOOGLE_KEY'");
                $aiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $key;
                $fields_string = json_encode($fields);
                $opts = array(
                    CURLOPT_URL => $aiUrl,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POST => count($fields),
                    CURLOPT_POSTFIELDS => $fields_string,
                    CURLOPT_HTTPHEADER => array('Content-Type: application/json')
                );

                $ch = curl_init();
                curl_setopt_array($ch, $opts);
                $data = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
                $response = json_decode($data);

                $aiDescription = $response->candidates[0]->content->parts[0]->text;

                // Process the string using PHP's preg_replace and str_replace functions
                $aiDescription = preg_replace([
                    // Replace **...** with <strong>...</strong>
                    '/\*\*(.*?)\*\*/',
                    // Handle "* **...**: ..." as <li><strong>...</strong>: ...</li>
                    '/(?:^|\n)\* \*\*(.*?)\*\*: (.*?)(?=\n|\*|$)/',
                    // Handle "* ..." as <li>...</li> for plain items
                    '/(?:^|\n)\* (.*?)(?=\n|\*|$)/',
                    // Wrap all consecutive <li> elements into a <ul>
                    '/(<li>.*?<\/li>)+/s',
                    // Handle headings starting with ##
                    '/^##\s*(.+)$/m'
                ], [
                    '<strong>$1</strong>', // Replacement for **...**
                    '<li><strong>$1:</strong> $2</li>', // Replacement for "* **...**: ..."
                    '<li>$1</li>', // Replacement for "* ..."
                    '<ul>$0</ul>', // Wrap <li> elements in <ul>
                    '<strong>$1</strong>' // Replacement for headings starting with ##
                ], $aiDescription);

                // Replace double newlines with paragraph tags
                $aiDescription = str_replace("\n\n", '</p><p>', $aiDescription);

                // Replace single newlines with <br> for line breaks
                $aiDescription = str_replace("\n", '<br>', $aiDescription);

                // Trim the resulting string
                $aiDescription = trim($aiDescription);

                $pdata['stit_long_description'] = $aiDescription;
                $pdata['updatedOn'] = date('Y-m-d H:i:s');
                $pdata['updatedBy'] = $_SESSION['admin']->Finascop_UserId;
                $status = $parentdb->perform("finascop_stock_itemmaster", $pdata, 'update', " stit_ID= {$productDetail['prdctId']}");
                if ($productDetail['stit_StoreGroup'] == 0) {
                    $brandGalleryId = $db->getItemFromDB("SELECT product_stitId FROM product_grozeo_map WHERE grozeo_stitId = {$productDetail['prdctId']}");
                    if ($brandGalleryId > 0)
                        $status = $db->perform("finascop_stock_itemmaster", $pdata, 'update', " stit_ID= {$brandGalleryId}");
                }
            }
        }
        if ($status) {
            $message = 'Saved Successfully';
            echo "{success: true,msg:'{$message}'}";
        } else {
            echo "{success: false,msg: 'Error occured while saving data' }";
        }
        break;
}
