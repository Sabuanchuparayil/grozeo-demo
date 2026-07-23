<?php

namespace Models; {

    class ThirdPartyProducts extends ModelAbstract
    {
        public function POST_saveMerchantProducts($flag, $request)
        {
            if (!array_key_exists('productId', $request))
            {
                throw new \Exception('Missing POST parameters ');
            }
            else
            {
                try
                {
                    $db = new \sqlDb(DSN);
                    $parentDB = new \sqlDb(PARENTDSN);
                    $outs = [
                        'status'    => "false",
                        'data'      => [],
                        'msg'       => ""
                    ];
                    $query = "SELECT * FROM finascop_stock_itemmaster WHERE stit_ID={$request['productId']}";
                    $productData = $parentDB->getFromDB($query, true);
                    $outs['msg'] = "Product not found.";
                    if($productData)
                    {
                        $toInsert = $productData;
                        unset($toInsert['stit_ID']);
                        unset($toInsert['mapping_id']);
                        $toInsert['grozeo_stitId'] = $productData['stit_ID'];
                        
                        $toInsert['pdt_brand'] = 0;
                        if(@$productData['pdt_brand'])
                        {
                            $brandData = $parentDB->getFromDB("SELECT * FROM mypha_productbrands WHERE brand_id={$productData['pdt_brand']}", true);
                            if($brandData)
                            {
                                if($brandData['mapping_id'] > 0){
                                    $toInsert['pdt_brand'] = $brandData['mapping_id'];
                                }else{
                                    $toInsert['pdt_brand'] = $db->getItemFromDB("SELECT brand_id FROM mypha_productbrands WHERE brand_name = '{$brandData['brand_name']}'");
                                }
                                
                            }
                        }
                        try
                        {
                            $insertProduct = $db->query('INSERT INTO third_party_products ('.join(', ', array_keys($toInsert)).') VALUES ('.$this->insertFormat($toInsert).')');

                            $insertID = $db->getLastInsertId();

                            $query = "SELECT * FROM finascop_stock_item_images WHERE product_id={$request['productId']}";
                            $imageData  = $parentDB->getMultipleData($query, true);
                            if($imageData)
                            {
                                foreach ($imageData as $imgData)
                                {
                                    unset($imgData['id']);
                                    $imgData['product_id'] = $insertID;
                                    $imageInserts = $db->perform('thirdparty_item_images', array_filter($imgData));
                                }   
                            }
                        }
                        catch (\Exception $e)
                        {
                            return [
                                'status'    => "false",
                                'data'      => [],
                                'msg'       => $e->getMessage()
                            ]; 
                        }
                        $outs['msg'] = "Unable to insert product.";
                        if($insertProduct)
                        {
                            $brandQuery = is_null($productData['pdt_brand']) ? "AND fsi_brand_name='{$productData['stit_brand_name']}'" : "AND fsi_brand_id={$productData['pdt_brand']}";
                            $checkUnique = $db->getFromDB("SELECT * FROM third_party_products_uniqueitem WHERE fsi_item_id={$productData['stit_itemId']} {$brandQuery} AND fsi_category_id={$productData['product_category']}", true);
                            if($checkUnique)
                            {
                                $uniqueUpdate = $db->perform('third_party_products_uniqueitem', ['fsi_count' => $checkUnique['fsi_count'] + 1], 'update', "fsi_uid = {$checkUnique['fsi_uid']}");
                            }
                            else
                            {
                                $uniqueInsertData = [
                                    "fsi_item_id"           => $productData['stit_itemId'],
                                    "fsi_item_name"         => $productData['stit_itemName'],
                                    "fsi_count"             => 1,
                                    "fsi_brand_id"          => (is_null($productData['pdt_brand']) ? 0 : $productData['pdt_brand']),
                                    "fsi_brand_name"        => $productData['stit_brand_name'],
                                    "fsi_category_id"       => $productData['product_category'],
                                    "fsi_categry_name"      => $productData['stit_category_name'],
                                    "fsi_def_itemmaster_id" => $productData['stit_ID'],
                                    "isMedicine"            => $productData['isMedicine'],
                                    "fsi_displaylabel"      => $productData['stit_displaylabel']
                                ];
                                $uniqueInsert = $db->perform('third_party_products_uniqueitem', $uniqueInsertData);
                            }
                            $outs['status'] = "ok";
                            $outs['msg'] = "All details updated";
                        }
                    }
                    return $outs;
                }
                catch (\Exception $e)
                {
                    return [
                        'status'    => "false",
                        'data'      => [],
                        'msg'       => $e->getMessage()
                    ]; 
                }
            }
        }

        public function POST_saveMerchantBrands($flag, $request)
        {
            try
            {
                $productDB = new \sqlDb(DSN);
                $brandID = 0;
                $msg = "";
                $query = 'SELECT * FROM mypha_productbrands WHERE brand_name="'.$request['brand_name'].'" AND  manufacture_id="'.$request['manufacture_id'].'" AND storegroup_id="'.$request['storegroup_id'].'"';
                $brandData = $productDB->getFromDB($query, true);
                if($brandData)
                {
                    $brandID = (int)$brandData['brand_id'];
                    $toUpdate = [
                        'brand_name'        => @$request['brand_name'],
                        'manufacture_id'    => @$request['manufacture_id'],
                        'storegroup_id'     => @$request['storegroup_id']
                    ];
                    $insertProduct = $productDB->perform('mypha_productbrands', $toUpdate,'update'," brand_id = {$brandID}");                    
                    $msg = "Updated";
                }
                else
                {
                    $toInsert = [
                        'brand_name'        => @$request['brand_name'],
                        'manufacture_id'    => @$request['manufacture_id'],
                        'storegroup_id'     => @$request['storegroup_id']
                    ];
                    $insertProduct = $productDB->perform('mypha_productbrands', $toInsert);
                    $brandID = $productDB->getLastInsertId();
                    $msg = "Inserted";
                }

                return [
                    'status'    => "ok",
                    'data'      => ["brand_id"  => $brandID],
                    'msg'       => $msg
                ]; 
            }
            catch (\Exception $e)
            {
                return [
                    'status'    => "false",
                    'data'      => [],
                    'msg'       => $e->getMessage()
                ]; 
            }
        }

        private function insertFormatOld($toInsert)
        {
            return (is_null($toInsert["stit_itemId"]) ? "NULL" : $toInsert["stit_itemId"]).', "'.$toInsert["stit_itemERPId"].'", "'.$toInsert["stit_itemBarcode"].'", '.(is_null($toInsert["stit_custInitiate"]) ? "NULL" : $toInsert["stit_custInitiate"]).', '.(is_null($toInsert["stit_itemReturnTime"]) ? "NULL" : $toInsert["stit_itemReturnTime"]).', "'.$toInsert["stit_SKU"].'", "'.$toInsert["stit_HSNCode"].'", '.(is_null($toInsert["stit_GST"]) ? "NULL" : $toInsert["stit_GST"]).', '.(is_null($toInsert["stit_MRP"]) ? "NULL" : $toInsert["stit_MRP"]).', '.(is_null($toInsert["stgp_groupID"]) ? "NULL" : $toInsert["stgp_groupID"]).', '.(is_null($toInsert["stit_PeriodFrom"]) ? "NULL" : $toInsert["stit_PeriodFrom"]).', '.(is_null($toInsert["stit_PeriodTo"]) ? "NULL" : $toInsert["stit_PeriodTo"]).', '.(is_null($toInsert["stit_StockEnabled"]) ? "NULL" : $toInsert["stit_StockEnabled"]).', '.(is_null($toInsert["stit_SalesEnabled"]) ? "NULL" : $toInsert["stit_SalesEnabled"]).', '.(is_null($toInsert["stit_OpeningStock"]) ? "NULL" : $toInsert["stit_OpeningStock"]).', '.(is_null($toInsert["stit_Min_Stock"]) ? "NULL" : $toInsert["stit_Min_Stock"]).', "'.$toInsert["stit_Description"].'", '.(is_null($toInsert["stit_Convertible"]) ? "NULL" : $toInsert["stit_Convertible"]).', '.(is_null($toInsert["stit_PurchaseEnabled"]) ? "NULL" : $toInsert["stit_PurchaseEnabled"]).', '.(is_null($toInsert["stit_Tangible"]) ? "NULL" : $toInsert["stit_Tangible"]).', '.(is_null($toInsert["pdt_product_type_id"]) ? "NULL" : $toInsert["pdt_product_type_id"]).', "'.$toInsert["stit_product_variant"].'", '.(is_null($toInsert["pdt_package_type_id"]) ? "NULL" : $toInsert["pdt_package_type_id"]).', '.(is_null($toInsert["product_is_home"]) ? "NULL" : $toInsert["product_is_home"]).', "'.$toInsert["product_image_url"].'", '.(is_null($toInsert["pdt_sale_rate"]) ? "NULL" : $toInsert["pdt_sale_rate"]).', '.(is_null($toInsert["pdt_brand"]) ? "NULL" : $toInsert["pdt_brand"]).', "'.$toInsert["product_tags"].'", '.(is_null($toInsert["product_category"]) ? "NULL" : $toInsert["product_category"]).', '.(is_null($toInsert["featured"]) ? "NULL" : $toInsert["featured"]).', '.(is_null($toInsert["popular"]) ? "NULL" : $toInsert["popular"]).', '.(is_null($toInsert["prescription"]) ? "NULL" : $toInsert["prescription"]).', "'.$toInsert["product_s3_mainbucket"].'", "'.$toInsert["product_s3_addbucket"].'", "'.$toInsert["product_s3_mainfile"].'", "'.$toInsert["product_s3_addfile"].'", "'.$toInsert["stit_displaylabel"].'", "'.$toInsert["item_length"].'", "'.$toInsert["item_breadth"].'", "'.$toInsert["item_height"].'", '.(is_null($toInsert["item_weight"]) ? "NULL" : $toInsert["item_weight"]).', '.(is_null($toInsert["stit_item_volume"]) ? "NULL" : $toInsert["stit_item_volume"]).', "'.$toInsert["stit_long_description"].'", "'.$toInsert["stit_quantity"].'", "'.$toInsert["stit_itemName"].'", "'.$toInsert["stit_HSN_code"].'", "'.$toInsert["stit_package_type_namme"].'", "'.$toInsert["stit_category_name"].'", '.(is_null($toInsert["stitl1_optimumqty"]) ? "NULL" : $toInsert["stitl1_optimumqty"]).', '.(is_null($toInsert["stitl2_optimumqty"]) ? "NULL" : $toInsert["stitl2_optimumqty"]).', '.(is_null($toInsert["stitl3_optimumqty"]) ? "NULL" : $toInsert["stitl3_optimumqty"]).', '.(is_null($toInsert["stit11_minimumqty"]) ? "NULL" : $toInsert["stit11_minimumqty"]).', '.(is_null($toInsert["stit12_minimumqty"]) ? "NULL" : $toInsert["stit12_minimumqty"]).', '.(is_null($toInsert["stit13_minimumqty"]) ? "NULL" : $toInsert["stit13_minimumqty"]).', '.(is_null($toInsert["stit11_maximumqty"]) ? "NULL" : $toInsert["stit11_maximumqty"]).', '.(is_null($toInsert["stit12_maximumqty"]) ? "NULL" : $toInsert["stit12_maximumqty"]).', '.(is_null($toInsert["stit13_maximumqty"]) ? "NULL" : $toInsert["stit13_maximumqty"]).', "'.$toInsert["stit_brand_name"].'", '.(is_null($toInsert["stit_fsiuid"]) ? "NULL" : $toInsert["stit_fsiuid"]).', '.(is_null($toInsert["stii_csb"]) ? "NULL" : $toInsert["stii_csb"]).', '.(is_null($toInsert["stii_csbretail"]) ? "NULL" : $toInsert["stii_csbretail"]).', '.(is_null($toInsert["isMedicine"]) ? "NULL" : $toInsert["isMedicine"]).', '.(is_null($toInsert["cos_nos"]) ? "NULL" : $toInsert["cos_nos"]).', '.(is_null($toInsert["cos_package_type_id"]) ? "NULL" : $toInsert["cos_package_type_id"]).', "'.$toInsert["cos_package_type_name"].'", '.(is_null($toInsert["cosb_package_type_id"]) ? "NULL" : $toInsert["cosb_package_type_id"]).', "'.$toInsert["cosb_package_type_name"].'", '.(is_null($toInsert["cos_length"]) ? "NULL" : $toInsert["cos_length"]).', '.(is_null($toInsert["cos_breadth"]) ? "NULL" : $toInsert["cos_breadth"]).', '.(is_null($toInsert["cos_height"]) ? "NULL" : $toInsert["cos_height"]).', '.(is_null($toInsert["cos_weight"]) ? "NULL" : $toInsert["cos_weight"]).', '.(is_null($toInsert["cos_volume"]) ? "NULL" : $toInsert["cos_volume"]).', '.(is_null($toInsert["ccs_nos"]) ? "NULL" : $toInsert["ccs_nos"]).', '.(is_null($toInsert["ccs_package_type_id"]) ? "NULL" : $toInsert["ccs_package_type_id"]).', "'.$toInsert["ccs_package_type_name"].'", '.(is_null($toInsert["ccsb_package_type_id"]) ? "NULL" : $toInsert["ccsb_package_type_id"]).', "'.$toInsert["ccsb_package_type_name"].'", '.(is_null($toInsert["ccs_length"]) ? "NULL" : $toInsert["ccs_length"]).', '.(is_null($toInsert["ccs_breadth"]) ? "NULL" : $toInsert["ccs_breadth"]).', '.(is_null($toInsert["ccs_height"]) ? "NULL" : $toInsert["ccs_height"]).', '.(is_null($toInsert["ccs_weight"]) ? "NULL" : $toInsert["ccs_weight"]).', '.(is_null($toInsert["ccs_volume"]) ? "NULL" : $toInsert["ccs_volume"]).', '.(is_null($toInsert["rs_nos"]) ? "NULL" : $toInsert["rs_nos"]).', '.(is_null($toInsert["rs_package_type_id"]) ? "NULL" : $toInsert["rs_package_type_id"]).', "'.$toInsert["rs_package_type_name"].'", '.(is_null($toInsert["rsb_package_type_id"]) ? "NULL" : $toInsert["rsb_package_type_id"]).', "'.$toInsert["rsb_package_type_name"].'", '.(is_null($toInsert["rs_length"]) ? "NULL" : $toInsert["rs_length"]).', '.(is_null($toInsert["rs_breadth"]) ? "NULL" : $toInsert["rs_breadth"]).', '.(is_null($toInsert["rs_height"]) ? "NULL" : $toInsert["rs_height"]).', '.(is_null($toInsert["rs_weight"]) ? "NULL" : $toInsert["rs_weight"]).', '.(is_null($toInsert["rs_volume"]) ? "NULL" : $toInsert["rs_volume"]).', '.(is_null($toInsert["cs_nos"]) ? "NULL" : $toInsert["cs_nos"]).', '.(is_null($toInsert["cs_package_type_id"]) ? "NULL" : $toInsert["cs_package_type_id"]).', "'.$toInsert["cs_package_type_name"].'", '.(is_null($toInsert["csb_package_type_id"]) ? "NULL" : $toInsert["csb_package_type_id"]).', "'.$toInsert["csb_package_type_name"].'", '.(is_null($toInsert["cs_length"]) ? "NULL" : $toInsert["cs_length"]).', '.(is_null($toInsert["cs_breadth"]) ? "NULL" : $toInsert["cs_breadth"]).', '.(is_null($toInsert["cs_height"]) ? "NULL" : $toInsert["cs_height"]).', '.(is_null($toInsert["cs_weight"]) ? "NULL" : $toInsert["cs_weight"]).', '.(is_null($toInsert["cs_volume"]) ? "NULL" : $toInsert["cs_volume"]).', '.(is_null($toInsert["ds_nos"]) ? "NULL" : $toInsert["ds_nos"]).', '.(is_null($toInsert["ds_package_type_id"]) ? "NULL" : $toInsert["ds_package_type_id"]).', "'.$toInsert["ds_package_type_name"].'", '.(is_null($toInsert["dsb_package_type_id"]) ? "NULL" : $toInsert["dsb_package_type_id"]).', "'.$toInsert["dsb_package_type_name"].'", '.(is_null($toInsert["ds_length"]) ? "NULL" : $toInsert["ds_length"]).', '.(is_null($toInsert["ds_breadth"]) ? "NULL" : $toInsert["ds_breadth"]).', '.(is_null($toInsert["ds_height"]) ? "NULL" : $toInsert["ds_height"]).', '.(is_null($toInsert["ds_weight"]) ? "NULL" : $toInsert["ds_weight"]).', '.(is_null($toInsert["ds_volume"]) ? "NULL" : $toInsert["ds_volume"]).', '.(is_null($toInsert["medcompos_id"]) ? "NULL" : $toInsert["medcompos_id"]).', "'.$toInsert["medcompos_name"].'", '.(is_null($toInsert["dosform_id"]) ? "NULL" : $toInsert["dosform_id"]).', "'.$toInsert["dosform_name"].'", '.(is_null($toInsert["med_drug_groupid"]) ? "NULL" : $toInsert["med_drug_groupid"]).', "'.$toInsert["med_drug_groupname"].'", '.(is_null($toInsert["med_manufactureid"]) ? "NULL" : $toInsert["med_manufactureid"]).', "'.$toInsert["med_manufacturename"].'", '.(is_null($toInsert["least_package_type_id"]) ? "NULL" : $toInsert["least_package_type_id"]).', "'.$toInsert["least_package_type_name"].'", "'.$toInsert["createdOn"].'", '.(is_null($toInsert["createdBy"]) ? "NULL" : $toInsert["createdBy"]).', "'.$toInsert["updatedOn"].'", '.(is_null($toInsert["updatedBy"]) ? "NULL" : $toInsert["updatedBy"]).', '.(is_null($toInsert["stit_status"]) ? "NULL" : $toInsert["stit_status"]).', '.(is_null($toInsert["isEdited"]) ? "NULL" : $toInsert["isEdited"]).', '.(is_null($toInsert["stit_fixedB2BRates"]) ? "NULL" : $toInsert["stit_fixedB2BRates"]).', '.(is_null($toInsert["stit_hsnId"]) ? "NULL" : $toInsert["stit_hsnId"]).', '.(is_null($toInsert["stit_stdPacking"]) ? "NULL" : $toInsert["stit_stdPacking"]).', '.(is_null($toInsert["stit_salesUnit"]) ? "NULL" : $toInsert["stit_salesUnit"]).', '.(is_null($toInsert["stit_package_type_id"]) ? "NULL" : $toInsert["stit_package_type_id"]).', '.(is_null($toInsert["stdpckl11_package_type_id"]) ? "NULL" : $toInsert["stdpckl11_package_type_id"]).', '.(is_null($toInsert["stdpckl1_nos"]) ? "NULL" : $toInsert["stdpckl1_nos"]).', '.(is_null($toInsert["stdpckl12_package_type_id"]) ? "NULL" : $toInsert["stdpckl12_package_type_id"]).', '.(is_null($toInsert["stdpckl21_package_type_id"]) ? "NULL" : $toInsert["stdpckl21_package_type_id"]).', '.(is_null($toInsert["stdpckl2_nos"]) ? "NULL" : $toInsert["stdpckl2_nos"]).', '.(is_null($toInsert["stdpckl22_package_type_id"]) ? "NULL" : $toInsert["stdpckl22_package_type_id"]).', '.(is_null($toInsert["stdpckl31_package_type_id"]) ? "NULL" : $toInsert["stdpckl31_package_type_id"]).', '.(is_null($toInsert["stdpckl3_nos"]) ? "NULL" : $toInsert["stdpckl3_nos"]).', '.(is_null($toInsert["stdpckl32_package_type_id"]) ? "NULL" : $toInsert["stdpckl32_package_type_id"]).', '.(is_null($toInsert["stdpckl41_package_type_id"]) ? "NULL" : $toInsert["stdpckl41_package_type_id"]).', '.(is_null($toInsert["stdpckl4_nos"]) ? "NULL" : $toInsert["stdpckl4_nos"]).', '.(is_null($toInsert["stdpckl42_package_type_id"]) ? "NULL" : $toInsert["stdpckl42_package_type_id"]).', '.(is_null($toInsert["courierDelivery"]) ? "NULL" : $toInsert["courierDelivery"]).', '.(is_null($toInsert["directDelivery"]) ? "NULL" : $toInsert["directDelivery"]).', '.(is_null($toInsert["stit_foodtype"]) ? "NULL" : $toInsert["stit_foodtype"]).', '.(is_null($toInsert["stit_orgin_country"]) ? "NULL" : $toInsert["stit_orgin_country"]).', '.(is_null($toInsert["stit_unit"]) ? "NULL" : $toInsert["stit_unit"]).', '.(is_null($toInsert["stit_qty"]) ? "NULL" : $toInsert["stit_qty"]).', '.(is_null($toInsert["isVerified"]) ? "NULL" : $toInsert["isVerified"]).', "'.$toInsert["verifedOn"].'", '.(is_null($toInsert["verifedBy"]) ? "NULL" : $toInsert["verifedBy"]).', '.(is_null($toInsert["isRRPApplicable"]) ? "NULL" : $toInsert["isRRPApplicable"]).', '.(is_null($toInsert["directPurchase"]) ? "NULL" : $toInsert["directPurchase"]).', "'.$toInsert["stit_ingredients"].'", "'.$toInsert["stit_preparation_use"].'", "'.$toInsert["stit_allergens"].'", "'.$toInsert["stit_nutritionlabel"].'", '.(is_null($toInsert["stit_HasChildItem"]) ? "NULL" : $toInsert["stit_HasChildItem"]).', '.(is_null($toInsert["stit_ParentItemId"]) ? "NULL" : $toInsert["stit_ParentItemId"]).', '.(is_null($toInsert["stit_ConvertCalcMode"]) ? "NULL" : $toInsert["stit_ConvertCalcMode"]).', '.(is_null($toInsert["stit_ConvertCalcRate"]) ? "NULL" : $toInsert["stit_ConvertCalcRate"]).', "'.$toInsert["stit_updatedOn"].'", '.(is_null($toInsert["stit_StoreGroup"]) ? "NULL" : $toInsert["stit_StoreGroup"]).', '.(is_null($toInsert["stit_package_master"]) ? "NULL" : $toInsert["stit_package_master"]).', "'.$toInsert["stit_courierWt"].'", "'.$toInsert["stit_warning"].'", "'.$toInsert["stit_safety_warning"].'", "'.$toInsert["stit_storage_instruction"].'", "'.$toInsert["gtin"].'", "'.$toInsert["case_configuration"].'", '.$toInsert["taxValueId"].', "'.$toInsert["itemProcessingTime"].'",'.$toInsert["grozeo_stitId"];
        }

private function insertFormat(array $toInsert): string
{
    // Helper function to format a value: add quotes for strings, use NULL for nulls
    $formatValue = function ($val, $isString = true) {
        if (is_null($val)) {
            return "NULL";
        }
        // If numeric and isString false, just return as-is
        if (!$isString && (is_int($val) || is_float($val))) {
            return $val;
        }
        // Escape double quotes and backslashes inside string values (simple)
        $escaped = addslashes($val);
        return '"' . $escaped . '"';
    };

    // List of keys and whether to treat as string or numeric (false = numeric)
    $fields = [
        "stit_itemId" => false,
        "stit_itemERPId" => true,
        "stit_itemBarcode" => true,
        "stit_custInitiate" => false,
        "stit_itemReturnTime" => false,
        "stit_SKU" => true,
        "stit_HSNCode" => true,
        "stit_GST" => false,
        "stit_MRP" => false,
        "stgp_groupID" => false,
        "stit_PeriodFrom" => false,
        "stit_PeriodTo" => false,
        "stit_StockEnabled" => false,
        "stit_SalesEnabled" => false,
        "stit_OpeningStock" => false,
        "stit_Min_Stock" => false,
        "stit_Description" => true,
        "stit_Convertible" => false,
        "stit_PurchaseEnabled" => false,
        "stit_Tangible" => false,
        "pdt_product_type_id" => false,
        "stit_product_variant" => true,
        "pdt_package_type_id" => false,
        "product_is_home" => false,
        "product_image_url" => true,
        "pdt_sale_rate" => false,
        "pdt_brand" => false,
        "product_tags" => true,
        "product_category" => false,
        "featured" => false,
        "popular" => false,
        "prescription" => false,
        "product_s3_mainbucket" => true,
        "product_s3_addbucket" => true,
        "product_s3_mainfile" => true,
        "product_s3_addfile" => true,
        "stit_displaylabel" => true,
        "item_length" => true,
        "item_breadth" => true,
        "item_height" => true,
        "item_weight" => false,
        "stit_item_volume" => false,
        "stit_long_description" => true,
        "stit_quantity" => true,
        "stit_itemName" => true,
        "stit_HSN_code" => true,
        "stit_package_type_namme" => true,
        "stit_category_name" => true,
        "stitl1_optimumqty" => false,
        "stitl2_optimumqty" => false,
        "stitl3_optimumqty" => false,
        "stit11_minimumqty" => false,
        "stit12_minimumqty" => false,
        "stit13_minimumqty" => false,
        "stit11_maximumqty" => false,
        "stit12_maximumqty" => false,
        "stit13_maximumqty" => false,
        "stit_brand_name" => true,
        "stit_fsiuid" => false,
        "stii_csb" => false,
        "stii_csbretail" => false,
        "isMedicine" => false,
        "cos_nos" => false,
        "cos_package_type_id" => false,
        "cos_package_type_name" => true,
        "cosb_package_type_id" => false,
        "cosb_package_type_name" => true,
        "cos_length" => false,
        "cos_breadth" => false,
        "cos_height" => false,
        "cos_weight" => false,
        "cos_volume" => false,
        "ccs_nos" => false,
        "ccs_package_type_id" => false,
        "ccs_package_type_name" => true,
        "ccsb_package_type_id" => false,
        "ccsb_package_type_name" => true,
        "ccs_length" => false,
        "ccs_breadth" => false,
        "ccs_height" => false,
        "ccs_weight" => false,
        "ccs_volume" => false,
        "rs_nos" => false,
        "rs_package_type_id" => false,
        "rs_package_type_name" => true,
        "rsb_package_type_id" => false,
        "rsb_package_type_name" => true,
        "rs_length" => false,
        "rs_breadth" => false,
        "rs_height" => false,
        "rs_weight" => false,
        "rs_volume" => false,
        "cs_nos" => false,
        "cs_package_type_id" => false,
        "cs_package_type_name" => true,
        "csb_package_type_id" => false,
        "csb_package_type_name" => true,
        "cs_length" => false,
        "cs_breadth" => false,
        "cs_height" => false,
        "cs_weight" => false,
        "cs_volume" => false,
        "ds_nos" => false,
        "ds_package_type_id" => false,
        "ds_package_type_name" => true,
        "dsb_package_type_id" => false,
        "dsb_package_type_name" => true,
        "ds_length" => false,
        "ds_breadth" => false,
        "ds_height" => false,
        "ds_weight" => false,
        "ds_volume" => false,
        "medcompos_id" => false,
        "medcompos_name" => true,
        "dosform_id" => false,
        "dosform_name" => true,
        "med_drug_groupid" => false,
        "med_drug_groupname" => true,
        "med_manufactureid" => false,
        "med_manufacturename" => true,
        "least_package_type_id" => false,
        "least_package_type_name" => true,
        "createdOn" => true,
        "createdBy" => false,
        "updatedOn" => true,
        "updatedBy" => false,
        "stit_status" => false,
        "isEdited" => false,
        "stit_fixedB2BRates" => false,
        "stit_hsnId" => false,
        "stit_stdPacking" => false,
        "stit_salesUnit" => false,
        "stit_package_type_id" => false,
        "stdpckl11_package_type_id" => false,
        "stdpckl1_nos" => false,
        "stdpckl12_package_type_id" => false,
        "stdpckl21_package_type_id" => false,
        "stdpckl2_nos" => false,
        "stdpckl22_package_type_id" => false,
        "stdpckl31_package_type_id" => false,
        "stdpckl3_nos" => false,
        "stdpckl32_package_type_id" => false,
        "stdpckl41_package_type_id" => false,
        "stdpckl4_nos" => false,
        "stdpckl42_package_type_id" => false,
        "courierDelivery" => false,
        "directDelivery" => false,
        "stit_foodtype" => false,
        "stit_orgin_country" => false,
        "stit_unit" => false,
        "stit_qty" => false,
        "isVerified" => false,
        "verifedOn" => true,
        "verifedBy" => false,
        "isRRPApplicable" => false,
        "directPurchase" => false,
        "stit_ingredients" => true,
        "stit_preparation_use" => true,
        "stit_allergens" => true,
        "stit_nutritionlabel" => true,
        "stit_HasChildItem" => false,
        "stit_ParentItemId" => false,
        "stit_ConvertCalcMode" => false,
        "stit_ConvertCalcRate" => false,
        "stit_updatedOn" => true,
        "stit_StoreGroup" => false,
        "stit_package_master" => false,
        "stit_courierWt" => true,
        "stit_warning" => true,
        "stit_safety_warning" => true,
        "stit_storage_instruction" => true,
        "gtin" => true,
        "case_configuration" => true,
        "taxValueId" => false,       // You missed the null check here in your original code
        "itemProcessingTime" => true,
        "grozeo_stitId" => false
    ];

    $values = [];

    foreach ($fields as $key => $isString) {
    $val = $toInsert[$key] ?? null;

    // Handle NOT NULL fields with default values
    if ($key === 'stit_updatedOn') {
        if (is_null($val)) {
            $val = date('Y-m-d H:i:s'); // Set the current timestamp
        }
    }

    // Special handling for taxValueId: ensure numeric or NULL
    if ($key === 'taxValueId') {
        if (is_null($val)) {
            $values[] = "NULL";
            continue;
        }
        $values[] = is_numeric($val) ? $val : "NULL";
        continue;
    }

    $values[] = $formatValue($val, $isString);
}


    // Join all values with commas
    return implode(", ", $values);
}
    }
}
