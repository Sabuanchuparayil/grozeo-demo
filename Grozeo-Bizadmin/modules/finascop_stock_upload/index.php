<?php

switch ($op) {
    case 'listfinascopStockUpload':
        $data = $_POST;
        $rec_limit = empty($data['limit']) ? 23 : $data['limit'];
        $rec_start = empty($data['start']) ? 0 : $data['start'];
        $rec_sort = empty($data['sort']) ? 'fbiu_id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'desc' : $data['dir'];
        $search = ' WHERE 1=1';
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    $searchitem .= " and ({$field[field]} LIKE '%{$field['data']['value']}%') ";
                }
            }
        }
        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $br_ID = $_POST['branchName'];
            $searchitem .= " ";
            /*if ($_SESSION['admin']->IsSuperUser == 'Yes') {
                $searchitem .= " ";
            } else {
                $searchitem .= " AND fbiu_branch = {$br_ID} ";
            }*/
        } else {
            $br_ID = $_SESSION['admin']->finascop_current_branch_id;
            $searchitem .= " AND fbiu_branch = {$br_ID} ";
        }
        if ($rec_sort == 'fsu_date') {
            $rec_sort = 'fbiu_id';
        }


        $countQuery = "SELECT COUNT(1) from finascop_stock_branch_inventory_upload {$search} {$searchitem}";
        $count = $db->getItemFromDB($countQuery);
        //$count = $db->getItemFromDB($countQuery);
        $listQuery = " SELECT fbiu_id,fbiu_branch,if(fbiu_status=0,'Not Saved','Saved') as fbiu_status,fbiu_createdOn,CASE WHEN fbiu_uploadedbyapi = 0 THEN 'Using CSV'
                WHEN fbiu_uploadedbyapi = 1 THEN 'Using API'
                WHEN fbiu_uploadedbyapi = 2 THEN 'Using System' END as fbiu_uploadedbyapi,"
            . "DATE_FORMAT(fbiu_createdOn, '%d-%m-%Y %H:%i:%s') as fsu_date  from finascop_stock_branch_inventory_upload {$search}  {$searchitem} ORDER BY $rec_sort $rec_sort_dir LIMIT $rec_start,$rec_limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $datas[$i]['branch_name'] = $db->getItemFromDB("SELECT CONCAT(br_Name ,'-',branch_shortname) FROM finascop_branch WHERE br_ID = {$datas[$i]['fbiu_branch']} ");
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';

        // $db->printGridJson($countQuery, $listQuery);
        break;
    case 'uploadStockcsvFile':
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
        $db->query('begin');
        if ($_SESSION['admin']->current_branch_iscpd == 1) {
            $br_ID = $_POST['branch'];
        } else {
            $br_ID = $_SESSION['admin']->finascop_current_branch_id;
        }
        $fsbiu['fbiu_branch'] = $br_ID;
        $fsbiu['fbiu_createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $fsbiu['fbiu_createdOn'] = date("Y-m-d H:i:s");
        // print_r($fsbiu);
        $status = $db->perform('finascop_stock_branch_inventory_upload', $fsbiu);
        $lastId = $db->insert_id();
        $invalidErps = [];
        $updateCount = 0;
        foreach ($csvData as $key => $value) {
            if ($key == 0) {
                $col0 = 'erpId';
                $col2 = 'Qty';
                $col3 = 'MRP';
                $col4 = 'selling_price';
                $col5 = 'discount_selling_price';
                if (($value[0] != $col0) || ($value[1] != $col2) || ($value[2] != $col3) || ($value[3] != $col4) || ($value[4] != $col5)) {
                    echo '{"success":true,"valid":false,"error":"Valid column names are erpId,Qty,MRP,selling_price,discount_selling_price."}';
                    exit();
                }
            } else {
                $stit_id = $db->getItemFromDB("SELECT fsipc_stit_id FROM finascop_stock_itemmaster_product_code_stores WHERE  fsipcs_Code = '{$value[0]}' AND fsipcs_store = {$br_ID}");
                if ($stit_id == 0) {
                    $br_storeGroup = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$br_ID}");

                    $stit_id = $db->getItemFromDB("SELECT fsipc_stit_id FROM finascop_stock_itemmaster_product_codes WHERE   fsipc_code = '{$value[0]}'  AND fsipc_storeGroup = {$br_storeGroup}  AND fsipc_isIndividual =0 AND fsipc_isCompany = 0");
                    if ($stit_id == 0) {
                        $stit_id = $db->getItemFromDB("SELECT fsipc_stit_id FROM finascop_stock_itemmaster_product_codes WHERE  fsipc_code = '{$value[0]}'   AND fsipc_isCompany = 1");
                    }
                }

                if ($stit_id > 0) {
                    $fbiud['fbiu_id'] = $lastId;
                    $fbiud['stit_id'] = $stit_id;
                    $fbiud['branch_id'] = $br_ID;
                    if ((floatval($value[1]) <= 0) || (floatval($value[2]) <= 0) || (floatval($value[3]) <= 0)) {
                        $fbiud['item_count'] = 0;
                        $fbiud['mrp'] = 0;
                        $fbiud['selling_price'] = 0;
                    } else {
                        $fbiud['item_count'] = floor($value[1]);
                        $fbiud['mrp'] = floatval($value[2]);
                        $fbiud['selling_price'] = floatval($value[3]);
                        $fbiud['discount_selling_price'] = floatval($value[4]);
                    }


                    $status = $db->perform('finascop_stock_branch_inventory_upload_detail', $fbiud);
                    $updateCount++;
                } else {
                    array_push($invalidErps, $value[0]);
                    //                    echo '{"success":true,"valid":false,"error":"ERP Id ' . $value[0] . ' doesnot exist"}';
                    //                    exit();
                }
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            if (count($invalidErps) > 0) {
                $erpIds = implode(',', $invalidErps);
                $dispMsg = "Following ERP Ids does not exists - {$erpIds},  Going to save {$updateCount} Items.";
            } else {
                $dispMsg = "Going to save {$updateCount} Items";
            }

            echo '{"success":true,"valid":true,"msg":"' . $dispMsg . '","fbiu_id":' . $lastId . '}';
        } else {
            // var_dup($error);
            echo '{"success":false,"valid":false}';
        }
        break;
    case 'listStockUploadedItems':
        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'ASC' : $data['dir'];
        $filter_part = ' AND 1=1';

        if (isset($data['filter'])) {
        $allowedFields = ['id', 'name', 'status', 'created_on', 'updated_on', 'date_from', 'date_to'];
        $filter_qry .= buildSafeFilterQuery($data['filter'], $allowedFields, $db);
    }

        $countQuery = "SELECT COUNT(*) from finascop_stock_branch_inventory_upload_detail WHERE fbiu_id = '{$_POST['fbiu_id']}' {$filter_part}";
        $listQuery = "SELECT fbiu_id,stit_id,branch_id,item_count,mrp,selling_price from finascop_stock_branch_inventory_upload_detail "
            . "WHERE fbiu_id = '{$_POST['fbiu_id']}' {$filter_part}  ORDER BY CAST({$rec_sort} as char) {$rec_sort_dir},binary {$rec_sort} {$rec_sort_dir}  ";
        //$db->printGridJson($countQuery, $listQuery);
        $count = $db->getItemFromDB($countQuery);
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                $datas[$i]['stit_sku'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['stit_id']}");
                $fsipcs_Code = $db->getItemFromDB("SELECT fsipcs_Code FROM finascop_stock_itemmaster_product_code_stores WHERE  fsipc_stit_id = {$datas[$i]['stit_id']} AND fsipcs_store = {$datas[$i]['branch_id']}");
                if (empty($fsipcs_Code)) {
                    $br_storeGroup = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$datas[$i]['branch_id']}");

                    $fsipcs_Code = $db->getItemFromDB("SELECT fsipc_code  FROM finascop_stock_itemmaster_product_codes WHERE   fsipc_stit_id = {$datas[$i]['stit_id']}  AND fsipc_storeGroup = {$br_storeGroup}  AND fsipc_isIndividual =0 AND fsipc_isCompany = 0");
                    if (empty($fsipcs_Code)) {
                        $fsipcs_Code = $db->getItemFromDB("SELECT fsipc_code FROM finascop_stock_itemmaster_product_codes WHERE  fsipc_stit_id  = '{$datas[$i]['stit_id']}'   AND fsipc_isCompany = 1");
                    }
                }
                //$datas[$i]['stit_itemERPId'] = $db->getItemFromDB("SELECT stit_itemERPId FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['stit_id']}");
                if (!empty($fsipcs_Code)) {
                    $datas[$i]['stit_itemERPId'] = $fsipcs_Code;
                } else {
                    $datas[$i]['stit_itemERPId'] = 'NA';
                }
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
            //echo json_encode($qry);
        } else
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        break;
    case 'confirmStockUpload':
        $spf_factordefault = $_SESSION['admin']->DEFAULT_SPF;
        $mmf_factordefault = $_SESSION['admin']->DEFAULT_MM;
        $fbiu_id = $_POST['fbiu_id'];
        $db->query('begin');

        $fsbiu['fbiu_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
        $fsbiu['fbiu_updatedOn'] = date("Y-m-d H:i:s");
        if ($_SESSION['admin']->br_PyramidLevel == 1) {
            $br_ID = $_POST['branchName'];
        } else {
            $br_ID = $_SESSION['admin']->finascop_current_branch_id;
        }
        $db->query("UPDATE finascop_stock_branch_inventory SET item_count = 0 WHERE branch_id = {$br_ID}");
        $fsbiudetails = $db->getMulipleData("SELECT fbiu_id,stit_id,branch_id,item_count,mrp,selling_price,fsbg_id,discount_selling_price from finascop_stock_branch_inventory_upload_detail WHERE fbiu_id = {$fbiu_id}", true);
        $fsbiudetailsCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory_upload_detail WHERE fbiu_id = {$fbiu_id}");
        $bmdDetails = $db->getFromDB("SELECT * FROM retaline_margindistributions WHERE is_default = 1", true);

        if ($fsbiudetailsCount > 0) {
            foreach ($fsbiudetails as $fsbiudetail) {
                $itemDetails = $db->getFromDB("SELECT stit_SKU,pdt_brand,stit_itemId,product_category FROM finascop_stock_itemmaster WHERE stit_ID = {$fsbiudetail['stit_id']}", true);
                $spf_factorSKU = $db->getItemFromDB("SELECT spf_factor FROM selling_price_factor WHERE spf_type = 1 AND spf_detail = {$fsbiudetail['stit_id']}");
                if ($spf_factorSKU > 1) {
                    $spfFactor = $spf_factorSKU;
                } else {
                    $spf_factorBrand = $db->getItemFromDB("SELECT spf_factor FROM selling_price_factor WHERE spf_type = 2 AND spf_detail = {$itemDetails['pdt_brand']}");
                    if ($spf_factorBrand > 1) {
                        $spfFactor = $spf_factorBrand;
                    } else {
                        $spf_factorItem = $db->getItemFromDB("SELECT spf_factor FROM selling_price_factor WHERE spf_type = 3 AND spf_detail = {$itemDetails['stit_itemId']}");
                        if ($spf_factorItem > 1) {
                            $spfFactor = $spf_factorItem;
                        } else {
                            $spf_factorSC = $db->getItemFromDB("SELECT spf_factor FROM selling_price_factor WHERE spf_type = 4 AND spf_detail = {$itemDetails['product_category']}");
                            if ($spf_factorSC > 1) {
                                $spfFactor = $spf_factorSC;
                            }
                        }
                    }
                }



                if ($spfFactor > 1) {
                    $spfFactorCalc = $spfFactor;
                } else {
                    $spfFactorCalc = $spf_factordefault;
                }

                $mmf_factorSKU = $db->getItemFromDB("SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 1 AND mm_detail = {$fsbiudetail['stit_id']}");
                if ($mmf_factorSKU > 1) {
                    $mmfFactor = $mmf_factorSKU;
                } else {
                    $mmf_factorBrand = $db->getItemFromDB("SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 2 AND mm_detail = {$itemDetails['pdt_brand']}");
                    if ($mmf_factorBrand > 1) {
                        $mmfFactor = $mmf_factorBrand;
                    } else {
                        $mmf_factorItem = $db->getItemFromDB("SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 3 AND mm_detail = {$itemDetails['stit_itemId']}");
                        if ($mmf_factorItem > 1) {
                            $mmfFactor = $mmf_factorItem;
                        } else {
                            $mmf_factorSC = $db->getItemFromDB("SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 4 AND mm_detail = {$itemDetails['product_category']}");
                            if ($mmf_factorSC > 1) {
                                $mmfFactor = $mmf_factorSC;
                            }
                        }
                    }
                }

                if ($mmfFactor > 1) {
                    $mmfFactorCalc = $mmfFactor;
                } else {
                    $mmfFactorCalc = $mmf_factordefault;
                }
                //            echo "SELECT id FROM finascop_stock_branch_inventory WHERE stit_id = {$fsbiudetail['stit_id']} AND branch_id = {$fsbiudetail['branch_id']} AND fsbg_id = {$fsbiudetail['fsbg_id']}";
                $fsbiCount = $db->getItemFromDb("SELECT id FROM finascop_stock_branch_inventory WHERE stit_id = {$fsbiudetail['stit_id']} AND branch_id = {$fsbiudetail['branch_id']} AND fsbg_id = {$fsbiudetail['fsbg_id']}");

                $stit_GST = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$fsbiudetail['stit_id']}");
                $least_package_type_id = $db->getItemFromDB("SELECT least_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$fsbiudetail['stit_id']}");

                if ($fsbiudetail['discount_selling_price'] > 0) {
                    $itemLandingCost = $fsbiudetail['discount_selling_price'];
                    $itemMMG = round(($fsbiudetail['selling_price'] - $itemLandingCost), 2);
                    $desiredMargin = $fsbiudetail['selling_price'] * $mmfFactorCalc / 100; //MRP*MM %
                    if ($itemMMG >= $desiredMargin) {
                        $discount_selling_price = $fsbiudetail['discount_selling_price'];
                        //$calculatedSP = $fsbiudetail['mrp'] - ($itemMMG * $spfFactorCalc / 100); //MRP - (MARGIN*SellingPriceFactor%)
                        $calculatedSP = $fsbiudetail['selling_price'];
                        $grozeoMargin = $calculatedSP - $itemLandingCost; //(calculatedSP - landingCost)
                    } else {
                        $discount_selling_price = 0;
                        $calculatedSP = 0;
                        $grozeoMargin = 0;
                    }
                } else {
                    $discount_selling_price = 0;
                    $calculatedSP = 0;
                    $grozeoMargin = 0;
                    $itemLandingCost = $fsbiudetail['selling_price'];
                    $itemMMG = round(($fsbiudetail['mrp'] - $itemLandingCost), 2);
                }
                $fpoddata['fcpod_spHmDel'] = round($calculatedSP, 2);
                $fpoddata['fcpod_spCouDel'] = round($calculatedSP, 2);
                $fpoddata['fcpod_spPikup'] = round($calculatedSP, 2);

                $fpod_spetHmDel = ($fpoddata['fcpod_spHmDel'] * 100) / (100 + $stit_GST);
                $fpoddata['fcpod_spetHmDel'] = round($fpod_spetHmDel, 2);
                $fpod_spetCouDel = ($fpoddata['fcpod_spCouDel'] * 100) / (100 + $stit_GST);
                $fpoddata['fcpod_spetCouDel'] = round($fpod_spetCouDel, 2);
                $fpod_spetPikup = ($fpoddata['fcpod_spPikup'] * 100) / (100 + $stit_GST);
                $fpoddata['fcpod_spetPikup'] = round($fpod_spetPikup, 2);

                if ($fsbiCount > 0) {
                    $updatatLog['old_selling_price'] = $db->getItemFromDB("SELECT selling_price FROM finascop_stock_branch_inventory WHERE id = {$fsbiCount} ");
                    $fsbu['mrp'] = $fsbiudetail['mrp'];
                    $fsbu['selling_price'] = $fsbiudetail['selling_price'];
                    $fsbu['discount_selling_price'] = $discount_selling_price;
                    $fsbu['item_count'] = $fsbiudetail['item_count'];
                    $fsbu['updated_on'] = date("Y-m-d H:i:s");
                    $fsbu['fpod_poLandingCostleastSKU'] = $itemLandingCost;
                    $fsbu['fpod_poMMGleastSKU'] = $itemMMG;
                    $fsbu['fpod_leastSKUmrp'] = $fsbiudetail['mrp'];
                    $fsbu['purchasing_unit'] = $least_package_type_id;
                    $fsbu['fpod_customerRateHmDel'] = $fpoddata['fcpod_spHmDel'];
                    $fsbu['fpod_customerRateCouDel'] = $fpoddata['fcpod_spCouDel'];
                    $fsbu['fpod_customerRatePikup'] = $fpoddata['fcpod_spPikup'];
                    $fsbu['grozeo_margin'] = $grozeoMargin;
                    $status = $db->perform('finascop_stock_branch_inventory', $fsbu, 'update', " id = {$fsbiCount}");
                } else {
                    $fsbi['mrp'] = $fsbiudetail['mrp'];
                    $fsbi['selling_price'] = $fsbiudetail['selling_price'];
                    $fsbi['discount_selling_price'] = $discount_selling_price;
                    $fsbi['item_count'] = $fsbiudetail['item_count'];
                    $fsbi['stit_id'] = $fsbiudetail['stit_id'];
                    $fsbi['branch_id'] = $fsbiudetail['branch_id'];
                    $fsbi['fsbg_id'] = $fsbiudetail['fsbg_id'];
                    $fsbi['updated_on'] = date("Y-m-d H:i:s");
                    $fsbi['fpod_poLandingCostleastSKU'] = $itemLandingCost;
                    $fsbi['fpod_poMMGleastSKU'] = $itemMMG;
                    $fsbi['fpod_leastSKUmrp'] = $fsbiudetail['mrp'];
                    $fsbi['purchasing_unit'] = $least_package_type_id;
                    $fsbi['fpod_customerRateHmDel'] = $fpoddata['fcpod_spHmDel'];
                    $fsbi['fpod_customerRateCouDel'] = $fpoddata['fcpod_spCouDel'];
                    $fsbi['fpod_customerRatePikup'] = $fpoddata['fcpod_spPikup'];
                    $fsbi['grozeo_margin'] = $grozeoMargin;
                    $status = $db->perform('finascop_stock_branch_inventory', $fsbi);

                    $updatatLog['old_selling_price'] = 0;
                }

                $type = 'Stock upload';
                $updatatLog['selling_price'] = $fsbiudetail['selling_price'];
                $updatatLog['branch_id'] = $fsbiudetail['branch_id'];
                $updatatLog['stit_id'] = $fsbiudetail['stit_id'];
                $updatatLog['item_count'] = $fsbiudetail['item_count'];
                $updatatLog['fpod_skuPurchaseRange'] = NULL;
                $updatatLog['fpod_skuPurchaseQty'] = NULL;
                $updatatLog['fpod_skuAvgPurchaseRate'] = NULL;
                $updatatLog['fpod_skuLastPurchaseRate'] = NULL;
                $updatatLog['fpod_leastSKUepr'] = NULL;
                $updatatLog['fpod_effectivemargin'] = NULL;
                $updatatLog['updated_on'] = date("Y-m-d H:i:s");
                $updatatLog['updated_by'] = $_SESSION['admin']->Finascop_UserId;
                $updatatLog['type'] = $type;
                $updatatLog['action'] = 'Selling price update - ' . $type;
                $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'INVENTORYLOG'");
                $fields_string = json_encode($updatatLog);
                $opts = array(
                    CURLOPT_URL => $url,
                    CURLINFO_CONTENT_TYPE => "application/json",
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_BINARYTRANSFER => TRUE,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_POST => count($fields),
                    CURLOPT_POSTFIELDS => $fields_string,
                    CURLOPT_HTTPHEADER => array('Content-Type: application/json')
                );

                $ch = curl_init();
                curl_setopt_array($ch, $opts);
                $logrresult = curl_exec($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
                header("Content-Type: application/json");
                //$result = json_decode($datacl, true);
                if ($logrresult != true) {
                    echo '{"success":false, "msg":"Some problem in log insertion."}';
                    exit();
                }
            }
            $fbiud['fbiu_status'] = 1;
            $status = $db->perform('finascop_stock_branch_inventory_upload', $fbiud, 'update', " fbiu_id = {$fbiu_id}");
            $msg = "Uploaded stock confirmed.";
        } else {
            $status = $db->query("DELETE FROM finascop_stock_branch_inventory_upload WHERE fbiu_id = {$fbiu_id}");
            $msg = "Nothing To upload";
        }


        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            // var_dup($error);
            echo '{"success":false,"valid":false}';
        }
        break;
    case 'buildStockUploadCsv':
        if ($_SESSION['admin']->current_branch_iscpd == 1) {
            $br_ID = $_REQUEST['branchName'];
        } else {
            $br_ID = $_SESSION['admin']->finascop_current_branch_id;
        }
        $brancNam = $db->getItemFRomDB("SELECT br_Name FROM finascop_branch WHERE br_ID = {$br_ID}");
        //        $listQuery = "SELECT fsi.stit_ID as itemId,stit_SKU,item_count,mrp,selling_price,stit_itemERPId FROM finascop_stock_itemmaster fsi "
        //                . "LEFT JOIN finascop_stock_branch_inventory fsbi ON fsbi.stit_id = fsi.stit_ID WHERE branch_id = {$br_ID} AND stit_itemERPId <> ''";
        //        $items = $db->getMulipleData($listQuery, true);
        $data = "erpId" . "," . "Qty" . "," . "MRP" . "," . "selling_price" . "," . "discount_selling_price" . "\n";
        for ($j = 1; $j <= 3; $j++) {
            $itemERPId = 100 + $j;
            $item_count = 10;
            $item_mrp = 50;
            $selling_price = 45;
            $discount_selling_price = 40;
            $data .= $itemERPId . ',' . $item_count . ',' . $item_mrp . ',' . $selling_price . ',' . $discount_selling_price . "\n";
        }
        $brancNam = str_replace(' ', '_', $brancNam);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="stockupload.csv"');
        echo $data;
        exit();
        break;
    case 'getBranchName':
        $branch_id = $_SESSION['admin']->finascop_current_branch_id;
        $qry = $db->getMulipleData("SELECT br_ID,CONCAT(br_Name ,'-',branch_shortname) AS br_Name FROM finascop_branch WHERE br_status = 'Active' AND br_StoreType = 'Dealer' AND br_PyramidLevel = 4", true);
        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;

    case 'checkBranchStoreType':
        $count = $db->getItemSafe("SELECT COUNT(*) FROM finascop_branch WHERE br_status = 'Active' AND br_StoreType = 'Dealer' AND br_PyramidLevel = 4 AND br_ID = ?", "i", [$_POST['branchName']]);
        if ($count == 1) {
            echo '{"success":true,"valid":true}';
        } else {
            echo '{"success":false,"valid":false}';
        }
        break;
    case 'getRrpDetailStore':
        $type = $_POST['type'];
        switch ($type) {
            case '1':
                $qry = $db->getMulipleData("SELECT stit_ID AS rrp_detailId,stit_SKU AS rrp_detailName FROM finascop_stock_itemmaster WHERE stit_status = 1 order by stit_SKU asc", true);
                break;
            case '2':
                $qry = $db->getMulipleData("SELECT brand_id AS rrp_detailId,brand_name AS rrp_detailName FROM mypha_productbrands WHERE status = 1 order by brand_name asc", true);
                break;
            case '3':
                $qry = $db->getMulipleData("SELECT itemname_id AS rrp_detailId,item_name AS rrp_detailName FROM finascop_stock_itemmastername WHERE status = 1 order by item_name asc", true);
                break;
            case '4':
                $qry = $db->getMulipleData("SELECT sub_category_id AS rrp_detailId,sub_category AS rrp_detailName FROM mypha_productsubcategory WHERE status = 1 order by sub_category asc", true);
                break;
        }


        if (!empty($qry)) {
            echo json_encode($qry);
        } else
            echo [];
        break;
    case 'saveRRPData':
        $data['rrp_type'] = $_POST['rrp_type'];
        $data['rrp_detail'] = $_POST['rrp_detail'];
        $data['rrp_factor'] = $_POST['rrp_factor'];



        $rrpUnique = $db->getItemSafe("SELECT COUNT(*) from retaline_rrpMaster WHERE rrp_type ='?' AND rrp_detail = {$_POST['rrp_detail']} ", "s", [$_POST['rrp_type']]);
        $db->query('begin');
        if ($rrpUnique == 0) {
            $data['rrp_createdOn'] = date('Y-m-d H:i:s');
            $data['rrp_createdBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('retaline_rrpMaster', $data);
            $lastId = $db->insert_id();
        } else {
            $data['rrp_updatedOn'] = date('Y-m-d H:i:s');
            $data['rrp_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $status = $db->perform('retaline_rrpMaster', $data, 'update', " rrp_type ='{$_POST['rrp_type']}' AND rrp_detail = {$_POST['rrp_detail']}");
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success:true,valid:true,message:'Details saved ',data:" . json_encode($return_rec) . " }";
        } else {
            echo "{'success':False,'valid':false,'message': 'Error While Saving.'}";
        }
        break;
    case 'listRRPFactor':
        $limit = isset($_POST['limit']) ? $_POST['limit'] : 20;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $sort = empty($sort) ? 'rrp_id' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;
        $search = " WHERE 1=1 ";
        $filter = $_POST['filter'];
        $subCategory_name = '';
        if (isset($filter)) {
            foreach ($filter as $key => $field) {
                if ($field['data']['value'] != "") {
                    $checkComa = strstr($field['data']['value'], ',');

                    if ($field['field'] == 'rrp_typeName') {
                        $values = $field['data']['value'];
                        $valuesArr = explode(',', $values);
                        $arrCount = count($valuesArr);
                        for ($j = 0; $j < $arrCount; $j++) {
                            if ($j == 0) {
                                $comma = '';
                            } else {
                                $comma = ',';
                            }
                            switch ($valuesArr[$j]) {
                                case 'SKU':
                                    $typeId .= $comma . (int) 1;
                                    break;
                                case 'Brand':
                                    $typeId .= $comma . (int) 2;
                                    break;
                                case 'ItemMaster':
                                    $typeId .= $comma . (int) 3;
                                    break;
                                case 'Subcategory':
                                    $typeId .= $comma . (int) 4;
                                    break;
                            }
                        }
                        $search .= " and (rrp_type IN({$typeId})) ";
                    } else if ($field['field'] == 'rrp_detailName') {
                        $rrp_detailName = $field[data][value];
                        //$search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                    }

                    //$search .= " and ({$field[field]} LIKE '{$field[data][value]}%') ";
                }
            }
        }

        $countQuery = "SELECT COUNT(*) FROM retaline_rrpMaster {$search}  ";
        $count = $db->getItemFromDB($countQuery);
        $cond = '';
        $listQuery = "SELECT rrp_id,rrp_type,rrp_detail,rrp_factor FROM retaline_rrpMaster {$search}  ORDER BY {$sort} {$dir} limit $start,$limit";
        $datas = $db->getMulipleData($listQuery, true);
        $resCount = count($datas);
        if (!empty($datas)) {
            for ($i = 0; $i < $resCount; $i++) {
                switch ($datas[$i]['rrp_type']) {
                    case '1':
                        if (!empty($rrp_detailName)) {
                            $cond = " AND stit_SKU like '{$rrp_detailName}%'";
                        }
                        $datas[$i]['rrp_typeName'] = 'SKU';
                        $datas[$i]['rrp_detailName'] = $db->getItemFromDB("SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = {$datas[$i]['rrp_detail']} {$cond}");
                        break;
                    case '2':
                        if (!empty($rrp_detailName)) {
                            $cond = " AND brand_name like '{$rrp_detailName}%'";
                        }
                        $datas[$i]['rrp_typeName'] = 'Brand';
                        $datas[$i]['rrp_detailName'] = $db->getItemFromDB("SELECT brand_name FROM mypha_productbrands WHERE brand_id = {$datas[$i]['rrp_detail']} {$cond}");
                        break;
                    case '3':
                        if (!empty($rrp_detailName)) {
                            $cond = " AND item_name like '{$rrp_detailName}%'";
                        }
                        $datas[$i]['rrp_typeName'] = 'ItemMaster';
                        $datas[$i]['rrp_detailName'] = $db->getItemFromDB("SELECT item_name FROM finascop_stock_itemmastername WHERE itemname_id = {$datas[$i]['rrp_detail']} {$cond}");
                        break;
                    case '4':
                        if (!empty($rrp_detailName)) {
                            $cond = " AND sub_category like '{$rrp_detailName}%'";
                        }
                        $datas[$i]['rrp_typeName'] = 'Subcategory';
                        $datas[$i]['rrp_detailName'] = $db->getItemFromDB("SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id = {$datas[$i]['rrp_detail']} {$cond}");
                        break;
                }
            }
            echo '{"totalCount":"', $count, '","data":' . json_encode($datas) . '}';
        } else {
            echo '{"totalCount":"0","data":' . json_encode($datas) . '}';
        }
        break;
    case 'deleteRRPFactor':
        $id = $_POST['rrp_id'];
        $del_query = "DELETE FROM retaline_rrpMaster WHERE rrp_id =" . $id;
        $status = $db->query($del_query);

        $status = $db->query('commit');
        if ($status) {

            echo "{success: true,msg:'Removed Successfully'}";
        } else {
            echo "{success: false,errors: { msg: 'Error occured while saving data' }}";
        }
        break;
    case 'checkRRPData':
        $data['rrp_type'] = $_POST['rrp_type'];
        $data['rrp_detail'] = $_POST['rrp_detail'];
        $data['rrp_factor'] = $_POST['rrp_factor'];
        $rrp_factorFlag = 0;

        switch ($_POST['rrp_type']) {
            case 1:
                $rrp_factorSKU = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 1 AND rrp_detail = {$data['rrp_detail']}");
                $itemDetails = $db->getFromDB("SELECT pdt_brand,stit_itemId,product_category FROM finascop_stock_itemmaster WHERE stit_ID = {$data['rrp_detail']} ", true);
                if ($rrp_factorSKU > 1) {
                    $rrp_factorFlag = 1;
                    $rrpFactor .= "Already have factor for SKU - " . $rrp_factorSKU;
                } else {
                    $rrp_factorBrand = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 2 AND rrp_detail = {$itemDetails['pdt_brand']}");
                    if ($rrp_factorBrand > 1) {
                        $rrp_factorFlag = 1;
                        $rrpFactor .= "Already have factor for SKU Brand - " . $rrp_factorBrand;
                    } else {
                        $rrp_factorItem = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 3 AND rrp_detail = {$itemDetails['stit_itemId']}");
                        if ($rrp_factorItem > 1) {
                            $rrp_factorFlag = 1;
                            $rrpFactor .= "Already have factor for SKU Item - " . $rrp_factorItem;
                        } else {
                            $rrp_factorSC = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 4 AND rrp_detail = {$itemDetails['product_category']}");
                            if ($rrp_factorSC > 1) {
                                $rrp_factorFlag = 1;
                                $rrpFactor .= "Already have factor for SKU Subcategory - " . $rrp_factorSC;
                            }
                        }
                    }
                }
                break;
            case 2:
                $rrp_factorBrand = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 2 AND rrp_detail = {$data['rrp_detail']}");
                if ($rrp_factorBrand > 1) {
                    $rrp_factorFlag = 1;
                    $rrpFactor .= "Already have factor for this Brand - " . $rrp_factorBrand;
                }
                break;
            case 3:
                $rrp_factorItem = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 3 AND rrp_detail = {$data['rrp_detail']}");
                if ($rrp_factorItem > 1) {
                    $rrp_factorFlag = 1;
                    $rrpFactor .= "Already have factor for this Item - " . $rrp_factorItem;
                }
                break;
            case 4:
                $rrp_factorSC = $db->getItemFromDB("SELECT rrp_factor FROM retaline_rrpMaster WHERE rrp_type = 4 AND rrp_detail = {$data['rrp_detail']}");
                if ($rrp_factorSC > 1) {
                    $rrp_factorFlag = 1;
                    $rrpFactor .= "Already have factor for this Subcategory - " . $rrp_factorSC;
                }
                break;
        }

        if ($rrp_factorFlag == 1) {
            echo "{success:true,valid:false,message:'.$rrpFactor.'}";
        } else {
            echo "{'success':true,'valid':true}";
        }
        break;
    case 'missingProdctListing':
        $branchFullName = $_POST['branchFullName'];
        $branchId = $_POST['branchId'];
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'stit_SKU' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;


        $item_name = $_POST['query'];
        $item_id = $_POST['current_type'];
        $cond = " WHERE 1=1 ";
        //1:brand,2:item,3:Make
        if ($item_name != '') {
            $cond .= " AND stit_SKU LIKE '%{$item_name}%'";
        }
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {

                if ($field['field'] == 'stit_itemName') {
                    $cond .= " and (stit_itemName LIKE '{$field['data']['value']}%') ";
                } else if ($field['field'] == 'stit_brand_name') {
                    $cond .= " and (stit_brand_name LIKE '{$field['data']['value']}%') ";
                } else if ($field['field'] == 'stit_category_name') {
                    $cond .= " and (stit_category_name LIKE '{$field['data']['value']}%') ";
                } else if ($field['field'] == 'stit_SKU') {
                    $cond .= " and (stit_SKU LIKE '{$field['data']['value']}%') ";
                } else {
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
        }
        switch ($item_id) {
            case 1:


                $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} AND isMedicine=1";
                $count = $db->getItemFromDB($countQuery);

                $qry = "SELECT stit_itemName,finascop_stock_itemmaster.stit_ID AS stit_ID,stit_brand_name,stit_SKU,stit_quantity,least_package_type_name,stit_category_name,stit_product_variant  
                    FROM finascop_stock_itemmaster {$cond} AND isMedicine = 1 AND stit_status = 1  and stit_ID NOT IN (SELECT stit_id FROM finascop_stock_branch_inventory WHERE branch_id = {$branchId}) ORDER BY {$sort} {$dir}";
                $data = $db->getMultipleData($qry, true);
                break;
            case 2:

                $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} AND isMedicine=0";
                $count = $db->getItemFromDB($countQuery);

                $qry = "SELECT stit_itemName,finascop_stock_itemmaster.stit_ID AS stit_ID,stit_brand_name,stit_SKU,stit_quantity,least_package_type_name,stit_category_name,stit_product_variant  
                    FROM finascop_stock_itemmaster {$cond} AND isMedicine = 0 AND stit_status = 1  and stit_ID NOT IN (SELECT stit_id FROM finascop_stock_branch_inventory WHERE branch_id = {$branchId}) ORDER BY {$sort} {$dir}";
                $data = $db->getMultipleData($qry, true);
                break;
            case 3:

                $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} ";
                $count = $db->getItemFromDB($countQuery);

                $qry = "SELECT stit_itemName,finascop_stock_itemmaster.stit_ID AS stit_ID,stit_brand_name,stit_SKU,stit_quantity,least_package_type_name,stit_category_name,stit_product_variant  "
                    . "FROM finascop_stock_itemmaster  {$cond} ";
                $data = $db->getMultipleData($qry, true);
                break;
        }
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'listSelectedItems':
        $branchFullName = $_POST['branchFullName'];
        $branchId = $_POST['branchId'];
        $data = $_POST;
        $rec_sort = empty($data['sort']) ? 'id' : $data['sort'];
        $rec_sort_dir = empty($data['dir']) ? 'DESC' : $data['dir'];
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {

                if ($field['field'] == 'category_name') {
                    $searchitem .= " and (category_name LIKE '{$field['data']['value']}%') ";
                } else if ($field['field'] == 'parent_category') {
                    $searchitem .= " and (mypha_productparent_category.parent_category LIKE '{$field['data']['value']}%') ";
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


        if ($branchId > 0) {

            $qry = "SELECT finascop_stock_itemmaster.stit_id as stit_id,branch_id,item_count,mrp,selling_price,stit_SKU,discount_selling_price 
                   FROM finascop_stock_branch_inventory 
                   INNER JOIN finascop_stock_itemmaster ON finascop_stock_itemmaster .stit_id= finascop_stock_branch_inventory.stit_id WHERE branch_id = {$branchId}  ORDER BY $rec_sort $rec_sort_dir";

            $data = $db->getMultipleData($qry, true);

            $countQuery = "SELECT COUNT(*) FROM finascop_stock_branch_inventory INNER JOIN finascop_stock_itemmaster ON finascop_stock_itemmaster .stit_id= finascop_stock_branch_inventory.stit_id WHERE branch_id = {$branchId}";
            $count = $db->getItemFromDB($countQuery);


            if (!empty($data)) {
                echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
            } else {
                echo '{"totalCount":"0","data":[]}';
            }
        }
        break;
    case 'generateUniqueId':
        $uniqueId = '';
        while ($uniqueId == '') {
            $uniqueId = getNewFinascopApiKey();
        }

        echo '{"uid":"' . $uniqueId . '"}';
        break;
    case 'saveStockDetails':
        $spf_factordefault = $_SESSION['admin']->DEFAULT_SPF;
        $mmf_factordefault = $_SESSION['admin']->DEFAULT_MM;
        $stit_id = $_POST['stit_id'];
        $br_ID = $_POST['branchId'];

        $storeGroup = $db->getItemFromDB("SELECT br_storeGroup FROM finascop_branch WHERE br_ID = {$br_ID}");
        $store_group_grosmartMerchant = $db->getItemFromDB("SELECT store_group_grosmartMerchant FROM finascop_branch_group WHERE store_group_id = {$storeGroup}");
        if($store_group_grosmartMerchant == 0 && $_POST['stitItemDiscountSP'] > 0){
            echo '{"success":false, "msg":"Merchant is not GrosmartMerchant ."}';
                exit();
        }
        $fsbiu['fbiu_uploadedapikey'] = $_POST['uuid'];
        $fsbiu['fbiu_uploadedbyapi'] = 2;
        $fsbiu['fbiu_branch'] = $br_ID;
        $fsbiu['fbiu_createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $fsbiu['fbiu_createdOn'] = date("Y-m-d H:i:s");
        $db->query('begin');
        $isEntry = $db->getItemSafe("SELECT fbiu_id FROM finascop_stock_branch_inventory_upload WHERE fbiu_uploadedapikey = ?", "s", [$_POST['uuid']]);
        if ($isEntry > 0) {
            $lastId = $isEntry;
        } else {
            $status = $db->perform('finascop_stock_branch_inventory_upload', $fsbiu);
            $lastId = $db->insert_id();
        }


        if ($stit_id > 0) {
            $fbiud['fbiu_id'] = $lastId;
            $fbiud['stit_id'] = $stit_id;
            $fbiud['branch_id'] = $br_ID;

            $fbiud['item_count'] = $_POST['stitItemCount'];
            $fbiud['mrp'] = $_POST['stitItemMRP'];
            $fbiud['selling_price'] = $_POST['stitItemSP'];
            $fbiud['discount_selling_price'] = $_POST['stitItemDiscountSP'];

            $detailEntrycount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory_upload_detail WHERE fbiu_id = {$lastId} AND stit_id = {$stit_id} ");
            if ($detailEntrycount > 0) {
                $status = $db->perform('finascop_stock_branch_inventory_upload_detail', $fbiud, 'update', " fbiu_id = {$lastId} AND stit_id = {$stit_id}");
            } else {
                $status = $db->perform('finascop_stock_branch_inventory_upload_detail', $fbiud);
            }

            $itemDetails = $db->getFromDB("SELECT stit_SKU,pdt_brand,stit_itemId,product_category FROM finascop_stock_itemmaster WHERE stit_ID = {$stit_id}", true);
            $spf_factorSKU = $db->getItemFromDB("SELECT spf_factor FROM selling_price_factor WHERE spf_type = 1 AND spf_detail = {$stit_id}");
            if ($spf_factorSKU > 1) {
                $spfFactor = $spf_factorSKU;
            } else {
                $spf_factorBrand = $db->getItemFromDB("SELECT spf_factor FROM selling_price_factor WHERE spf_type = 2 AND spf_detail = {$itemDetails['pdt_brand']}");
                if ($spf_factorBrand > 1) {
                    $spfFactor = $spf_factorBrand;
                } else {
                    $spf_factorItem = $db->getItemFromDB("SELECT spf_factor FROM selling_price_factor WHERE spf_type = 3 AND spf_detail = {$itemDetails['stit_itemId']}");
                    if ($spf_factorItem > 1) {
                        $spfFactor = $spf_factorItem;
                    } else {
                        $spf_factorSC = $db->getItemFromDB("SELECT spf_factor FROM selling_price_factor WHERE spf_type = 4 AND spf_detail = {$itemDetails['product_category']}");
                        if ($spf_factorSC > 1) {
                            $spfFactor = $spf_factorSC;
                        }
                    }
                }
            }

            if ($spfFactor > 1) {
                $spfFactorCalc = $spfFactor;
            } else {
                $spfFactorCalc = $spf_factordefault;
            }

            $mmf_factorSKU = $db->getItemFromDB("SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 1 AND mm_detail = {$stit_id}");
            if ($mmf_factorSKU > 1) {
                $mmfFactor = $mmf_factorSKU;
            } else {
                $mmf_factorBrand = $db->getItemFromDB("SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 2 AND mm_detail = {$itemDetails['pdt_brand']}");
                if ($mmf_factorBrand > 1) {
                    $mmfFactor = $mmf_factorBrand;
                } else {
                    $mmf_factorItem = $db->getItemFromDB("SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 3 AND mm_detail = {$itemDetails['stit_itemId']}");
                    if ($mmf_factorItem > 1) {
                        $mmfFactor = $mmf_factorItem;
                    } else {
                        $mmf_factorSC = $db->getItemFromDB("SELECT mm_factor FROM minimum_margin_range WHERE mm_type = 4 AND mm_detail = {$itemDetails['product_category']}");
                        if ($mmf_factorSC > 1) {
                            $mmfFactor = $mmf_factorSC;
                        }
                    }
                }
            }

            if ($mmfFactor > 1) {
                $mmfFactorCalc = $mmfFactor;
            } else {
                $mmfFactorCalc = $mmf_factordefault;
            }

            $fsbiCount = $db->getItemFromDb("SELECT id FROM finascop_stock_branch_inventory WHERE stit_id = {$stit_id} AND branch_id = {$br_ID} ");
            $stit_GST = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$stit_id}");
            $least_package_type_id = $db->getItemFromDB("SELECT least_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$stit_id}");

            if ($fbiud['discount_selling_price'] > 0) {

                $itemLandingCost = $fbiud['discount_selling_price'];
                $itemMMG = round(($fbiud['selling_price'] - $itemLandingCost), 2); //margin
                $desiredMargin = $fbiud['selling_price'] * $mmfFactorCalc / 100; //MRP*MM%
                if ($itemMMG >= $desiredMargin) {
                    $discount_selling_price = $fbiud['discount_selling_price'];
                    //$calculatedSP = $fbiud['mrp'] - ($itemMMG * $spfFactorCalc / 100); //MRP - (MARGIN*SellingPriceFactor%)
                    $calculatedSP = $fbiud['selling_price'];
                    $grozeoMargin = $calculatedSP - $itemLandingCost; //(calculatedSP - landingCost)
                } else {
                    $discount_selling_price = 0;
                    $calculatedSP = 0;
                    $grozeoMargin = 0;
                }
            } else {
                $itemLandingCost = $fbiud['selling_price'];
                $itemMMG = round(($fsbiudetail['mrp'] - $itemLandingCost), 2);
                $discount_selling_price = 0;
                $calculatedSP = 0;
                $grozeoMargin = 0;
            }


            $fpoddata['fcpod_spHmDel'] = round($calculatedSP, 2);
            $fpoddata['fcpod_spCouDel'] = round($calculatedSP, 2);
            $fpoddata['fcpod_spPikup'] = round($calculatedSP, 2);

            //$grozeoMargin = $calculatedSP - $itemLandingCost; //(calculatedSP - landingCost)


            $fpod_spetHmDel = ($fpoddata['fcpod_spHmDel'] * 100) / (100 + $stit_GST);
            $fpoddata['fcpod_spetHmDel'] = round($fpod_spetHmDel, 2);
            $fpod_spetCouDel = ($fpoddata['fcpod_spCouDel'] * 100) / (100 + $stit_GST);
            $fpoddata['fcpod_spetCouDel'] = round($fpod_spetCouDel, 2);
            $fpod_spetPikup = ($fpoddata['fcpod_spPikup'] * 100) / (100 + $stit_GST);
            $fpoddata['fcpod_spetPikup'] = round($fpod_spetPikup, 2);

            if ($fsbiCount > 0) {
                $updatatLog['old_selling_price'] = $db->getItemFromDB("SELECT selling_price FROM finascop_stock_branch_inventory WHERE id = {$fsbiCount} ");
                $fsbu['mrp'] = $fbiud['mrp'];
                $fsbu['selling_price'] = $fbiud['selling_price'];
                $fsbu['discount_selling_price'] = $discount_selling_price;
                $fsbu['item_count'] = $fbiud['item_count'];
                $fsbu['updated_on'] = date("Y-m-d H:i:s");
                $fsbu['fpod_poLandingCostleastSKU'] = $itemLandingCost;
                $fsbu['fpod_poMMGleastSKU'] = $itemMMG;
                $fsbu['fpod_leastSKUmrp'] = $fbiud['mrp'];
                $fsbu['purchasing_unit'] = $least_package_type_id;
                $fsbu['fpod_customerRateHmDel'] = $fpoddata['fcpod_spHmDel'];
                $fsbu['fpod_customerRateCouDel'] = $fpoddata['fcpod_spCouDel'];
                $fsbu['fpod_customerRatePikup'] = $fpoddata['fcpod_spPikup'];
                $fsbu['grozeo_margin'] = $grozeoMargin;
                $status = $db->perform('finascop_stock_branch_inventory', $fsbu, 'update', " id = {$fsbiCount}");
            } else {
                $fsbi['mrp'] = $fbiud['mrp'];
                $fsbi['selling_price'] = $fbiud['selling_price'];
                $fsbi['discount_selling_price'] = $discount_selling_price;
                $fsbi['item_count'] = $fbiud['item_count'];
                $fsbi['stit_id'] = $fbiud['stit_id'];
                $fsbi['branch_id'] = $fbiud['branch_id'];
                $fsbi['updated_on'] = date("Y-m-d H:i:s");
                $fsbi['fpod_poLandingCostleastSKU'] = $itemLandingCost;
                $fsbi['fpod_poMMGleastSKU'] = $itemMMG;
                $fsbi['fpod_leastSKUmrp'] = $fbiud['mrp'];
                $fsbi['purchasing_unit'] = $least_package_type_id;
                $fsbi['fpod_customerRateHmDel'] = $fpoddata['fcpod_spHmDel'];
                $fsbi['fpod_customerRateCouDel'] = $fpoddata['fcpod_spCouDel'];
                $fsbi['fpod_customerRatePikup'] = $fpoddata['fcpod_spPikup'];
                $fsbi['grozeo_margin'] = $grozeoMargin;
                $status = $db->perform('finascop_stock_branch_inventory', $fsbi);
                $updatatLog['old_selling_price'] = 0;
            }
            $type = 'Stock Update';
            $updatatLog['selling_price'] = $fbiud['selling_price'];
            $updatatLog['discount_selling_price'] = $discount_selling_price;
            $updatatLog['branch_id'] = $fbiud['branch_id'];
            $updatatLog['stit_id'] = $fbiud['stit_id'];
            $updatatLog['item_count'] = $fbiud['item_count'];
            $updatatLog['fpod_skuPurchaseRange'] = NULL;
            $updatatLog['fpod_skuPurchaseQty'] = NULL;
            $updatatLog['fpod_skuAvgPurchaseRate'] = NULL;
            $updatatLog['fpod_skuLastPurchaseRate'] = NULL;
            $updatatLog['fpod_leastSKUepr'] = NULL;
            $updatatLog['fpod_effectivemargin'] = NULL;
            $updatatLog['grozeo_margin'] = $grozeoMargin;
            $updatatLog['updated_on'] = date("Y-m-d H:i:s");
            $updatatLog['updated_by'] = $_SESSION['admin']->Finascop_UserId;
            $updatatLog['type'] = $type;
            $updatatLog['action'] = 'Selling price update - ' . $type;
            $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'INVENTORYLOG'");
            $fields_string = json_encode($updatatLog);
            $opts = array(
                CURLOPT_URL => $url,
                CURLINFO_CONTENT_TYPE => "application/json",
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_BINARYTRANSFER => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_POST => count($fields),
                CURLOPT_POSTFIELDS => $fields_string,
                CURLOPT_HTTPHEADER => array('Content-Type: application/json')
            );

            $ch = curl_init();
            curl_setopt_array($ch, $opts);
            $logrresult = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            header("Content-Type: application/json");
            //$result = json_decode($datacl, true);
            if ($logrresult != true) {
                echo '{"success":false, "msg":"Some problem in log insertion."}';
                exit();
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"valid":true,"msg":"Stock Updated.","fbiu_id":' . $lastId . '}';
        } else {
            echo '{"success":false,"valid":false}';
        }
        break;
    case 'saveStockDetailsMargin':
        $stit_id = $_POST['stit_id'];
        $br_ID = $_POST['branchId'];
        $fsbiu['fbiu_uploadedapikey'] = $_POST['uuid'];
        $fsbiu['fbiu_uploadedbyapi'] = 2;
        $fsbiu['fbiu_branch'] = $br_ID;
        $fsbiu['fbiu_createdBy'] = $_SESSION['admin']->Finascop_UserId;
        $fsbiu['fbiu_createdOn'] = date("Y-m-d H:i:s");
        $db->query('begin');
        $isEntry = $db->getItemSafe("SELECT fbiu_id FROM finascop_stock_branch_inventory_upload WHERE fbiu_uploadedapikey = ?", "s", [$_POST['uuid']]);
        if ($isEntry > 0) {
            $lastId = $isEntry;
        } else {
            $status = $db->perform('finascop_stock_branch_inventory_upload', $fsbiu);
            $lastId = $db->insert_id();
        }

        if ($stit_id > 0) {
            $fbiud['fbiu_id'] = $lastId;
            $fbiud['stit_id'] = $stit_id;
            $fbiud['branch_id'] = $br_ID;

            $fbiud['item_count'] = $_POST['stitItemCount'];
            $fbiud['mrp'] = $_POST['stitItemMRP'];
            $fbiud['selling_price'] = $_POST['stitItemSP'];

            $detailEntrycount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_branch_inventory_upload_detail WHERE fbiu_id = {$lastId} AND stit_id = {$stit_id} ");
            if ($detailEntrycount > 0) {
                $status = $db->perform('finascop_stock_branch_inventory_upload_detail', $fbiud, 'update', " fbiu_id = {$lastId} AND stit_id = {$stit_id}");
            } else {
                $status = $db->perform('finascop_stock_branch_inventory_upload_detail', $fbiud);
            }

            $bmdDetails = $db->getFromDB("SELECT * FROM retaline_margindistributions WHERE is_default = 1", true);

            $fsbiCount = $db->getItemFromDb("SELECT id FROM finascop_stock_branch_inventory WHERE stit_id = {$stit_id} AND branch_id = {$br_ID} ");
            $issponsered = $db->getItemFromDb("SELECT issponsered FROM finascop_stock_branch_inventory WHERE stit_id = {$stit_id} AND branch_id = {$br_ID} ");
            $itemLandingCost = $fbiud['selling_price'];
            $itemMMG = round(($fbiud['mrp'] - $itemLandingCost), 2);
            $stit_GST = $db->getItemFromDB("SELECT stit_GST FROM finascop_stock_itemmaster WHERE stit_ID = {$stit_id}");
            $least_package_type_id = $db->getItemFromDB("SELECT least_package_type_id FROM finascop_stock_itemmaster WHERE stit_ID = {$stit_id}");

            $fpod_spHmDel = $itemLandingCost + (($itemMMG * $bmdDetails['bmd_company'] / 100) + ($itemMMG * $bmdDetails['bmd_incentive'] / 100) + ($itemMMG * $bmdDetails['bmd_cs'] / 100) + ($itemMMG * $bmdDetails['bmd_distributor'] / 100) + ($itemMMG * $bmdDetails['bmd_retailor'] / 100) + ($itemMMG * $bmdDetails['bmd_driver'] / 100));
            $fpoddata['fcpod_spHmDel'] = round($fpod_spHmDel, 2);
            $fpod_spCouDel = $itemLandingCost + (($itemMMG * $bmdDetails['bmd_company'] / 100) + ($itemMMG * $bmdDetails['bmd_incentive'] / 100) + ($itemMMG * $bmdDetails['bmd_cs'] / 100) + ($itemMMG * $bmdDetails['bmd_distributor'] / 100) + ($itemMMG * $bmdDetails['bmd_retailor'] / 100) + ($itemMMG * $bmdDetails['bmd_courier'] / 100));
            $fpoddata['fcpod_spCouDel'] = round($fpod_spCouDel, 2);
            $fpod_spPikup = $itemLandingCost + (($itemMMG * $bmdDetails['bmd_company'] / 100) + ($itemMMG * $bmdDetails['bmd_incentive'] / 100) + ($itemMMG * $bmdDetails['bmd_cs'] / 100) + ($itemMMG * $bmdDetails['bmd_distributor'] / 100) + ($itemMMG * $bmdDetails['bmd_retailor'] / 100));
            $fpoddata['fcpod_spPikup'] = round($fpod_spPikup, 2);

            $fpod_spetHmDel = ($fpoddata['fcpod_spHmDel'] * 100) / (100 + $stit_GST);
            $fpoddata['fcpod_spetHmDel'] = round($fpod_spetHmDel, 2);
            $fpod_spetCouDel = ($fpoddata['fcpod_spCouDel'] * 100) / (100 + $stit_GST);
            $fpoddata['fcpod_spetCouDel'] = round($fpod_spetCouDel, 2);
            $fpod_spetPikup = ($fpoddata['fcpod_spPikup'] * 100) / (100 + $stit_GST);
            $fpoddata['fcpod_spetPikup'] = round($fpod_spetPikup, 2);
            if ($issponsered == 1) {
            }
            if ($fsbiCount > 0) {
                $updatatLog['old_selling_price'] = $db->getItemFromDB("SELECT selling_price FROM finascop_stock_branch_inventory WHERE id = {$fsbiCount} ");
                $fsbu['mrp'] = $fbiud['mrp'];
                $fsbu['selling_price'] = $fbiud['selling_price'];
                $fsbu['item_count'] = $fbiud['item_count'];
                $fsbu['updated_on'] = date("Y-m-d H:i:s");
                $fsbu['fpod_poLandingCostleastSKU'] = $itemLandingCost;
                $fsbu['fpod_poMMGleastSKU'] = $itemMMG;
                $fsbu['fpod_leastSKUmrp'] = $fbiud['mrp'];
                $fsbu['purchasing_unit'] = $least_package_type_id;
                $fsbu['fpod_customerRateHmDel'] = $fpoddata['fcpod_spHmDel'];
                $fsbu['fpod_customerRateCouDel'] = $fpoddata['fcpod_spCouDel'];
                $fsbu['fpod_customerRatePikup'] = $fpoddata['fcpod_spPikup'];
                $status = $db->perform('finascop_stock_branch_inventory', $fsbu, 'update', " id = {$fsbiCount}");
            } else {
                $fsbi['mrp'] = $fbiud['mrp'];
                $fsbi['selling_price'] = $fbiud['selling_price'];
                $fsbi['item_count'] = $fbiud['item_count'];
                $fsbi['stit_id'] = $fbiud['stit_id'];
                $fsbi['branch_id'] = $fbiud['branch_id'];
                $fsbi['updated_on'] = date("Y-m-d H:i:s");
                $fsbi['fpod_poLandingCostleastSKU'] = $itemLandingCost;
                $fsbi['fpod_poMMGleastSKU'] = $itemMMG;
                $fsbi['fpod_leastSKUmrp'] = $fbiud['mrp'];
                $fsbi['purchasing_unit'] = $least_package_type_id;
                $fsbi['fpod_customerRateHmDel'] = $fpoddata['fcpod_spHmDel'];
                $fsbi['fpod_customerRateCouDel'] = $fpoddata['fcpod_spCouDel'];
                $fsbi['fpod_customerRatePikup'] = $fpoddata['fcpod_spPikup'];
                $status = $db->perform('finascop_stock_branch_inventory', $fsbi);
                $updatatLog['old_selling_price'] = 0;
            }
            $type = 'Stock Update';
            $updatatLog['selling_price'] = $fbiud['selling_price'];
            $updatatLog['branch_id'] = $fbiud['branch_id'];
            $updatatLog['stit_id'] = $fbiud['stit_id'];
            $updatatLog['item_count'] = $fbiud['item_count'];
            $updatatLog['fpod_skuPurchaseRange'] = NULL;
            $updatatLog['fpod_skuPurchaseQty'] = NULL;
            $updatatLog['fpod_skuAvgPurchaseRate'] = NULL;
            $updatatLog['fpod_skuLastPurchaseRate'] = NULL;
            $updatatLog['fpod_leastSKUepr'] = NULL;
            $updatatLog['fpod_effectivemargin'] = NULL;
            $updatatLog['updated_on'] = date("Y-m-d H:i:s");
            $updatatLog['updated_by'] = $_SESSION['admin']->Finascop_UserId;
            $updatatLog['type'] = $type;
            $updatatLog['action'] = 'Selling price update - ' . $type;
            $url = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'INVENTORYLOG'");
            $fields_string = json_encode($updatatLog);
            $opts = array(
                CURLOPT_URL => $url,
                CURLINFO_CONTENT_TYPE => "application/json",
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_BINARYTRANSFER => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_POST => count($fields),
                CURLOPT_POSTFIELDS => $fields_string,
                CURLOPT_HTTPHEADER => array('Content-Type: application/json')
            );

            $ch = curl_init();
            curl_setopt_array($ch, $opts);
            $logrresult = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            header("Content-Type: application/json");
            //$result = json_decode($datacl, true);
            if ($logrresult != true) {
                echo '{"success":false, "msg":"Some problem in log insertion."}';
                exit();
            }
        }
        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"valid":true,"msg":"Stock Updated.","fbiu_id":' . $lastId . '}';
        } else {
            echo '{"success":false,"valid":false}';
        }
        break;
    case 'cofirmStockUpdate':
        $uuid = $_POST['uuid'];
        $fbiu_id = $db->getItemSafe("SELECT fbiu_id FROM finascop_stock_branch_inventory_upload WHERE fbiu_uploadedapikey = ?", "s", [$_POST['uuid']]);
        if ($fbiu_id > 0) {
            $fbiud['fbiu_status'] = 1;
            $fbiud['fbiu_updatedBy'] = $_SESSION['admin']->Finascop_UserId;
            $fbiud['fbiu_updatedOn'] = date("Y-m-d H:i:s");
            $db->query('begin');
            $status = $db->perform('finascop_stock_branch_inventory_upload', $fbiud, 'update', " fbiu_id = {$fbiu_id}");
        }

        $status = $db->query('commit');
        if ($status == 1) {
            echo '{"success":true,"valid":true,"msg":"Stock Updated."}';
        } else {
            echo '{"success":false,"valid":false}';
        }
        break;
    case 'listSubProducts':
        //$data = $_POST;
        $rec_limit = empty($_POST['limit']) ? 23 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $searchitem = ' ';
        $psearchitem = ' ';
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    if ($field[field] == 'subItemName') {
                        $searchitem .= " and (stit_SKU LIKE '{$field['data']['value']}%') ";
                    } else if ($field[field] == 'subItemId') {
                        $searchitem .= " and (stit_itemERPId = {$field['data']['value']} ) ";
                    } else if ($field[field] == 'parentItemName') {
                        $psearchitem .= " and (stit_SKU LIKE '{$field['data']['value']}%') ";
                    } else if ($field[field] == 'parentItemId') {
                        $searchitem .= " and (stit_ParentItemId = {$field['data']['value']} ) ";
                    }
                }
            }
        }

        $query = "SELECT stit_ID,stit_SKU,stit_ConvertCalcMode,stit_ConvertCalcRate,stit_ParentItemId,stit_itemERPId FROM finascop_stock_itemmaster WHERE stit_ParentItemId > 0 {$searchitem} "
            . "order by stit_ParentItemId asc LIMIT  $rec_start,$rec_limit";
        $subProducts = $db->getMulipleData($query, true);
        if (count($subProducts) > 0) {
            for ($i = 0; $i < count($subProducts); $i++) {
                if ($subProducts[$i]['stit_ParentItemId'] > 0) {
                    $parentItem = $db->getFromDB("SELECT stit_ID,stit_SKU,stit_itemERPId FROM finascop_stock_itemmaster WHERE stit_ID = {$subProducts[$i]['stit_ParentItemId']} {$psearchitem}", true);
                }
                $data[$i]['stit_ID'] = $subProducts[$i]['stit_ID'];
                $data[$i]['stit_ParentItemId'] = $subProducts[$i]['stit_ParentItemId'];
                $data[$i]['parentItemName'] = $parentItem['stit_SKU'];
                $data[$i]['subItemName'] = $subProducts[$i]['stit_SKU'];
                $data[$i]['stit_ConvertCalcMode'] = ($subProducts[$i]['stit_ConvertCalcMode'] == 1) ? 'Price' : 'Stock';
                $data[$i]['stit_ConvertCalcRate'] = $subProducts[$i]['stit_ConvertCalcRate'];
            }
        }


        $resCount = $db->getItemFromDB("SELECT COUNT(1) FROM finascop_stock_itemmaster WHERE stit_ParentItemId > 0 order by stit_ParentItemId asc ");
        if ($resCount > 0) {
            echo '{"totalCount":"', $resCount, '","data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }

        break;
    case 'deleteSubPdt':
        $subPdtId = $_POST['subPdtId'];
        $parentPdtId = $_POST['parentPdtId'];
        if ($parentPdtId > 0) {
            $childCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster WHERE stit_ParentItemId = {$parentPdtId}");
        }

        $data['stit_ParentItemId'] = 0;
        $data['stit_ConvertCalcMode'] = 0;
        $data['stit_ConvertCalcRate'] = 0;
        $db->query('begin');
        if ($subPdtId > 0) {
            $data['stit_updatedOn'] = date("Y-m-d H:i:s");
            $status = $db->perform('finascop_stock_itemmaster', $data, 'update', " stit_ID = {$subPdtId}");
            if ($childCount == 1) {
                $parentPro['stit_HasChildItem'] = 0;
                $parentPro['stit_updatedOn'] = date("Y-m-d H:i:s");
                $status = $db->perform('finascop_stock_itemmaster', $parentPro, 'update', " stit_ID = {$parentPdtId}");
            }
            $status = $db->query('commit');
            if ($status == 1) {
                echo "{success: true,message:'Item Removed.'}";
            } else {
                echo "{success: false, message: 'Error occured while saving data'}";
            }
        }
        break;
    case 'addParentProduct':
        $parentProduct = $_POST['parentProduct'];
        $db->query('begin');
        if ($parentProduct > 0) {
            $parentPro['stit_HasChildItem'] = 1;
            $parentPro['stit_updatedOn'] = date("Y-m-d H:i:s");
            $status = $db->perform('finascop_stock_itemmaster', $parentPro, 'update', " stit_ID = {$parentProduct}");
        }

        $status = $db->query('commit');
        if ($status == 1) {
            echo "{success: true,message:'Saved Successfully'}";
        } else {
            echo "{success: false,message: 'Error occured while saving data'}";
        }
        break;
    case 'parentProdcts':
        $rec_limit = empty($_POST['limit']) ? 16 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $_allowed_sort = ['id', 'name', 'created_on', 'status'];
        $sort = in_array(trim($_POST['sort'] ?? ''), $_allowed_sort) ? trim($_POST['sort']) : 'id';
        $dir = (strtoupper(trim($_POST['dir'] ?? '')) === 'ASC') ? 'ASC' : 'DESC';
        $sort = empty($sort) ? 'stit_SKU' : $sort;
        $dir = empty($dir) ? 'DESC' : $dir;


        $item_name = $_POST['query'];
        $cond = " WHERE 1=1 ";
        if ($item_name != '') {
            $cond .= " AND stit_SKU LIKE '%{$item_name}%'";
        }
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {

                if ($field['field'] == 'stit_itemName') {
                    $cond .= " and (stit_itemName LIKE '{$field[data][value]}%') ";
                } else if ($field['field'] == 'stit_brand_name') {
                    $cond .= " and (stit_brand_name LIKE '{$field[data][value]}%') ";
                } else if ($field['field'] == 'stit_category_name') {
                    $cond .= " and (stit_category_name LIKE '{$field[data][value]}%') ";
                } else if ($field['field'] == 'stit_SKU') {
                    $cond .= " and (stit_SKU LIKE '{$field[data][value]}%') ";
                } else {
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
        }
        $countQuery = "SELECT COUNT(stit_itemId) FROM finascop_stock_itemmaster  {$cond} AND isMedicine=0 AND stit_status = 1 AND directPurchase = 1";
        $count = $db->getItemFromDB($countQuery);

        $qry = "SELECT stit_itemName,finascop_stock_itemmaster.stit_ID AS stit_ID,stit_brand_name,stit_SKU,stit_quantity,least_package_type_name,stit_category_name,stit_product_variant  
                    FROM finascop_stock_itemmaster {$cond} AND isMedicine = 0 AND stit_status = 1 AND directPurchase = 1 ORDER BY {$sort} {$dir}";
        $data = $db->getMultipleData($qry, true);
        if (!empty($data)) {
            echo '{"totalCount":' . $count . ',"data":' . json_encode($data) . '}';
        } else
            echo '{"totalCount":"0","data":[]}';
        break;
    case 'getProducts':
        $item_name = $_POST['query'];
        if ($item_name != '') {
            $cond .= " AND stit_SKU LIKE '%{$item_name}%'";
        }
        $product_category = $_POST['product_category'];
        $parentProduct = $_POST['parentProduct'];
        if ($parentProduct > 0) {
            $cond .= " and stit_HasChildItem = 0 and stit_ID not in ({$parentProduct})";
        } else {
            $cond .= " ";
        }
        $qry = "select stit_ID,stit_SKU from finascop_stock_itemmaster where stit_ParentItemId = 0 AND directPurchase = 0 AND stit_status = 1 {$cond} order by stit_SKU asc";
        $data = $db->getMultipleData($qry, true);
        echo '{"success":true,"data":' . json_encode($data) . '}';

        break;
    case 'saveSubProduct':
        if ($_POST['rsp_calcBy'] <= 0) {
            echo "{success: false,message:'Invalid value for calculation'}";
            exit();
        } else {
            $rsp_subProduct = $_POST['subProduct'];

            $data['stit_ParentItemId'] = $_POST['parentItemId'];
            if ($_POST['rsp_calcMode'] == 'Stock') {
                $data['stit_ConvertCalcMode'] = 2;
            } else {
                $data['stit_ConvertCalcMode'] = 1;
            }

            $data['stit_ConvertCalcRate'] = $_POST['rsp_calcBy'];
            $db->query('begin');
            if ($rsp_subProduct > 0) {
                $data['stit_updatedOn'] = date("Y-m-d H:i:s");
                $status = $db->perform('finascop_stock_itemmaster', $data, 'update', " stit_ID = {$rsp_subProduct}");
                $status = $db->query('commit');

                if ($status == 1) {
                    echo "{success: true,message:'Saved Successfully'}";
                } else {
                    echo "{success: false,message: 'Error occured while saving data'}";
                }
            } else {
                echo "{success: false,message:'Enter valid data.'}";
                exit();
            }
        }
        break;
    case 'listParentProducts':
        $rec_limit = empty($_POST['limit']) ? 23 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $searchitem = ' ';
        $psearchitem = ' ';
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    if ($field[field] == 'subItemName') {
                        $searchitem .= " and (stit_SKU LIKE '{$field['data']['value']}%') ";
                    } else if ($field[field] == 'subItemId') {
                        $searchitem .= " and (stit_itemERPId = {$field['data']['value']} ) ";
                    } else if ($field[field] == 'parentItemName') {
                        $psearchitem .= " and (stit_SKU LIKE '{$field['data']['value']}%') ";
                    } else if ($field[field] == 'parentItemId') {
                        $searchitem .= " and (stit_ParentItemId = {$field['data']['value']} ) ";
                    }
                }
            }
        }

        $query = "SELECT stit_ID,stit_SKU FROM finascop_stock_itemmaster WHERE stit_status = 1 AND stit_HasChildItem = 1 {$searchitem} "
            . "order by stit_ParentItemId asc LIMIT  $rec_start,$rec_limit";
        $data = $db->getMulipleData($query, true);

        $resCount = $db->getItemFromDB("SELECT COUNT(1) FROM finascop_stock_itemmaster WHERE stit_status = 1 AND  stit_HasChildItem = 1  {$searchitem} ");
        if ($resCount > 0) {
            echo '{"totalCount":"', $resCount, '","data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }
        break;
    case 'listParentSubProducts':
        $parentItemId = $_POST['parentItemId'];
        //$data = $_POST;
        $rec_limit = empty($_POST['limit']) ? 23 : $_POST['limit'];
        $rec_start = empty($_POST['start']) ? 0 : $_POST['start'];
        $searchitem = ' ';
        $psearchitem = ' ';
        $filter = $_POST['filter'];
        if (isset($filter)) {

            foreach ($filter as $key => $field) {
                $checkComa = strstr($field['data']['value'], ',');

                if ($checkComa != '') {
                    $fiterItem = $field['data']['value'];
                    $fiterItem = str_replace(',', "','", $fiterItem);
                    $searchitem .= " and ({$field['field']} IN('{$fiterItem}')) ";
                } else {
                    if ($field[field] == 'subItemName') {
                        $searchitem .= " and (stit_SKU LIKE '{$field['data']['value']}%') ";
                    } else if ($field[field] == 'subItemId') {
                        $searchitem .= " and (stit_itemERPId = {$field['data']['value']} ) ";
                    } else if ($field[field] == 'parentItemName') {
                        $psearchitem .= " and (stit_SKU LIKE '{$field['data']['value']}%') ";
                    } else if ($field[field] == 'parentItemId') {
                        $searchitem .= " and (stit_ParentItemId = {$field['data']['value']} ) ";
                    }
                }
            }
        }

        $query = "SELECT stit_ID,stit_SKU,stit_ConvertCalcMode,stit_ConvertCalcRate,stit_ParentItemId FROM finascop_stock_itemmaster WHERE stit_status = 1 AND  stit_ParentItemId = {$parentItemId} {$searchitem} "
            . "order by stit_ParentItemId asc LIMIT  $rec_start,$rec_limit";
        $subProducts = $db->getMulipleData($query, true);
        $data = $db->getMulipleData($query, true);
        if (count($subProducts) > 0) {
            for ($i = 0; $i < count($subProducts); $i++) {

                $data[$i]['stit_ConvertCalcMode'] = ($subProducts[$i]['stit_ConvertCalcMode'] == 1) ? 'Price' : 'Stock';
                $data[$i]['stit_ConvertCalcRate'] = $subProducts[$i]['stit_ConvertCalcRate'];
            }
        }


        $resCount = $db->getItemFromDB("SELECT COUNT(1) FROM finascop_stock_itemmaster WHERE stit_status = 1 AND  stit_ParentItemId = {$parentItemId} order by stit_ParentItemId asc ");
        if ($resCount > 0) {
            echo '{"totalCount":"', $resCount, '","data":' . json_encode($data) . '}';
        } else {
            echo '{"totalCount":"0","data":[]}';
        }

        break;
    case 'deleteParentPdt':
        $parentPdtId = $_POST['parentPdtId'];
        if ($parentPdtId > 0) {
            $childCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster WHERE stit_ParentItemId = {$parentPdtId}");

            $data['stit_ParentItemId'] = 0;
            $data['stit_ConvertCalcMode'] = 0;
            $data['stit_ConvertCalcRate'] = 0;
            $db->query('begin');
            $data['stit_updatedOn'] = date("Y-m-d H:i:s");
            $status = $db->perform('finascop_stock_itemmaster', $data, 'update', " stit_ParentItemId = {$parentPdtId}");

            $parentPro['stit_HasChildItem'] = 0;
            $parentPro['stit_updatedOn'] = date("Y-m-d H:i:s");
            $status = $db->perform('finascop_stock_itemmaster', $parentPro, 'update', " stit_ID = {$parentPdtId}");

            $status = $db->query('commit');
            if ($status == 1) {
                echo "{success: true,message:'Item Removed.'}";
            } else {
                echo "{success: false, message: 'Error occured while saving data'}";
            }
        }
        break;
}
