<?php
switch ($op) {
    case 'fetchScrpData':
        $tpSiteUrl = $_POST['tpSiteUrl'];
        $gtinValue = $_POST['gtinValue'];
        if (!empty($tpSiteUrl)) {

            $isExists = $db->getItemSafe("SELECT COUNT(*) FROM product_scrap_data WHERE gsId = ?", "i", [$_POST['gsId']]);
           /* if ($isExists > 0) {
                echo "{'success':true,'valid':true,'msg': 'Already exists.'}";
                exit();
            }*/
            $cfg_Value = $db->getItemFromDB("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'SCRAP_URL'");

            $opts = array(
                CURLOPT_URL => $cfg_Value,
                CURLOPT_POSTFIELDS => $tpSiteUrl,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLINFO_CONTENT_TYPE => "application/json",
                CURLOPT_BINARYTRANSFER => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => array('Content-Type: text/plain')
            );

            $ch = curl_init();
            curl_setopt_array($ch, $opts);
            $data = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            header("Content-Type: application/json");

            $siteData = json_decode($data, TRUE);
            if ($siteData['message'] == "Internal server error") {
                echo "{'success':true,'valid':false,'msg': 'The URL you entered is not available please retry after sometime.'}";
                exit();
            } else {
                $scrapData['gsId'] = $_POST['gsId'];
                $scrapData['gtinValue'] = $_POST['gtinValue'];
                $scrapData['searchUrl'] = $tpSiteUrl;
                $scrapData['scrapData'] = $data;
                $scrapData['createdOn'] = date("Y-m-d H:i:s");
                $scrapData['createdBy'] = $_SESSION['admin']->Finascop_UserId;
                $db->query('begin');
                $isExists = $db->getItemSafe("SELECT COUNT(*) FROM product_scrap_data WHERE gsId = ?", "i", [$_POST['gsId']]);
                if ($isExists > 0) {
                    $status = $db->perform("product_scrap_data", $scrapData, 'update', "gsId = " . intval($_POST['gsId']));
                } else {
                    $status = $db->perform("product_scrap_data", $scrapData);
                }

                $status = $db->query('commit');
                if ($status == 1) {
                    echo "{success:true,valid:true,msg:'Details saved '}";
                } else {
                    echo "{'success':false,'valid':false,'msg': 'Error While Saving.'}";
                }
            }
        }
        break;
    case 'gsScrapData':

        $gsId = $_POST['gsId'];
        $scrapDatas = $db->getFromSafe("SELECT * FROM product_scrap_data WHERE gsId = ?", "i", [$_POST['gsId']], true);
        $scrappedDatas = json_decode($scrapDatas['scrapData']);
        $count = count($scrappedDatas);
        $result = [];
        for ($i = 0; $i < 21; $i++) {
            $result[$i]['id'] = $scrappedDatas[$i]->id;
            $result[$i]['Title'] = $scrappedDatas[$i]->Title;
            $result[$i]['Value'] = $scrappedDatas[$i]->Value;
        }

        echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
        break;
    case 'gsScrapMapData':
        $gsId = $_POST['gsId'];
        $gs1Data = $db->getFromDB("SELECT * FROM gs1_products_extension WHERE id = {$gsId}", true);
        $scrapMapMasters = $db->getMultipleData("SELECT * FROM product_scrap_map", true);
        $scrappedDatas = json_decode($_POST['gridProductScrapDataMapArr']);
        $count = count($scrappedDatas);

        $result = [];

        for ($i = 0; $i < 21; $i++) {
            $result[$i]['id'] = $scrapMapMasters[$i]['id'];
            $result[$i]['Title'] = $scrapMapMasters[$i]['gsTitle'];
            $gsfields = explode('/', $scrapMapMasters[$i]['gsField']);
            if (count($gsfields) > 1) {
                $gsfieldsValue = json_decode($gsfields[0]);
                $gs1DataField = $gsfieldsValue->$gsfields[1];
                $result[$i]['Value'] = $gs1Data[$gs1DataField];
            } else {
                $result[$i]['Value'] = $gs1Data[$scrapMapMasters[$i]['gsField']];
            }
            $result[$i]['isChanged'] = 0;
        }

        echo '{"totalCount":' . $count . ',"data":' . json_encode($result) . '}';
        break;
    case 'mergeScrapdataToGS1Products':
        $mappedScrapDatas = json_decode($_POST['mappedScrapData']);
        //print_r($mappedScrapDatas);
        $data['mappedData'] = $_POST['mappedScrapData'];
        $db->query('begin');
        $status = $db->perform('product_scrap_data', $data, 'update', " gsId  = {$_POST['gsId']} ");

        $scrapMapMasters = $db->getMultipleData("SELECT * FROM product_scrap_map", true);
        for ($i = 0; $i < 21; $i++) {
            if ($mappedScrapDatas[$i]->isChanged == 1) {
                if ($scrapMapMasters[$i]['productField'] == 'stit_long_description' || $scrapMapMasters[$i]['productField'] == 'stit_Description' || $scrapMapMasters[$i]['productField'] == 'stit_SKU' || $scrapMapMasters[$i]['productField'] == 'mrp' || $scrapMapMasters[$i]['productField'] == 'images') {
                    $fdata[$scrapMapMasters[$i]['productField']] = $mappedScrapDatas[$i]->Value[0];
                } else {
                    if (!empty($scrapMapMasters[$i]['productField']))
                        $fdata[$scrapMapMasters[$i]['productField']] = $mappedScrapDatas[$i]->Value;
                }

                $dataExists  = $db->getItemSafe("SELECT COUNT(*) FROM gs1_scrap_itemmaster WHERE gsId = ?", "i", [$_POST['gsId']]);
                if ($dataExists > 0) {
                    $fdata = array_filter($fdata);
                    if (count($fdata) > 0) {
                        $status = $db->perform('gs1_scrap_itemmaster', $fdata, 'update', " gsId  = {$_POST['gsId']} ");
                    }
                } else {
                    $fdata['gsId'] = $_POST['gsId'];
                    $status = $db->perform('gs1_scrap_itemmaster', $fdata);
                }
            }
        }
        $status = $db->query('commit');
        if ($status) {
            $msg = "Data Merged";
            echo '{"success":true,"valid":true,"msg":"' . $msg . '"}';
        } else {
            $msg = "Error Occured";
            echo '{"success":true,"valid":false,"msg":"' . $msg . '"}';
        }

        break;
}
