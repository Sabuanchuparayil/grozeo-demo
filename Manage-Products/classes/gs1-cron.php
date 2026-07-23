<?php

define('ROOT', dirname(dirname(__FILE__)));
define('INCLUDE_PATH', ROOT . "/includes");
define('AWS_ROOT', '/home/system/awsapi');
require(ROOT . '/includes/config.php');
require(INCLUDE_PATH . '/functions.php');
require(ROOT . '/includes/lib.php');
require('TextLocal.php');
require(AWS_ROOT . '/aws-autoloader.php');


/*error_reporting(E_ALL);
ini_set('display_errors', '1');*/
header('Content-Type: application/json; charset=utf-8');

class Gs1Cron
{
	function getProducts($db)
	{
		$outs = [];
		$qry = 'select * from gs1_product_insert_log_source';
		$allLogs = $db->getMultipleData($qry, true);

		foreach ($allLogs as $log)
		{
			$catID = $log['category'];
			$subcatID = $log['subCategory'];
			$gcpID = $log['gcpID'];
			$logID = $log['id'];
			$pdctsCountQty = "select * from gs1_products_source where ";
			$pdctsCountQty .= ($catID > 0) ? "categoryId=".$catID : "1";
			$pdctsCountQty .= ($subcatID > 0) ? " and subCategoryId=".$subcatID : " and 1";
			$pdctsCountQty .= ($gcpID > 0) ? ' and gtin LIKE "'.$gcpID.'%"' : " and 1";
			$insertedProducts = $db->getMultipleData($pdctsCountQty, true);

			$url = 'https://gs1datakart.org/dkapi/product?';
			$url .= ($catID > 0) ? '&catid='.$catID : '';
			$url .= ($subcatID > 0) ? '&subcatid='.$subcatID : '';
			$url .= ($gcpID > 0) ? '&gcp='.$gcpID : '';
			$headers = ['Authorization: Bearer edd4072e43efb61b88e646fe2776eb97e12620f4'];
			$response = $this->getData($url, [], 'GET', $headers);

			$totalFromApi = 0;
			$inserted = 0;
			$updated = 0;
			if(@$response->status == true)
			{
				$totalFromApi = $response->pageInfo->totalResults;

				if(count($insertedProducts) <= $totalFromApi)
				{
					$starter = intval(count($insertedProducts)/1000);
					$insertProducts = $this->insertProducts($db, $url, $headers, $starter, $catID, $subcatID, $gcpID, $log['id']);
					$inserted = $insertProducts['inserted'];
					$updated = $insertProducts['updated'];
				}
			}
			$cronLogDetails = [
				'log_id'			=> $logID,
				'category'			=> $catID,
				'subcategory'		=> $subcatID,
				'gcp'				=> $gcpID,
				'inserted_count'	=> count($insertedProducts),
				'gs1_api_count'		=> $totalFromApi,
				'inserted'			=> $inserted,
				'updated'			=> $updated
			];
			$db->perform('gs1_cron_log', $cronLogDetails, 'insert');
			$outs[] = $cronLogDetails;

		}
		die(json_encode($outs));
	}


	private function convertProduct($product, $linker)
	{
		return '"'.mysqli_real_escape_string($linker, $product['company_detail']).'", '.$product['brandId'].', "'.mysqli_real_escape_string($linker, $product['brand']).'", "'.$product['unique_key'].'", '.$product['isValid'].', "'.mysqli_real_escape_string($linker, $product['name']).'", "'.$product['gtin'].'", "'.$product['caution'].'", "'.mysqli_real_escape_string($linker, $product['sku_code']).'", "'.mysqli_real_escape_string($linker, $product['description']).'", '.$product['categoryId'].', "'.$product['category'].'", '.$product['subCategoryId'].', "'.$product['sub_category'].'", "'.$product['gpc_code'].'", "'.mysqli_real_escape_string($linker, $product['marketing_info']).'", "'.mysqli_real_escape_string($linker, $product['url']).'", "'.$product['activation_date'].'", "'.$product['deactivation_date'].'", "'.mysqli_real_escape_string($linker, $product['derived_description']).'", "'.$product['country_of_origin'].'", "'.$product['created_date'].'", "'.$product['modified_date'].'", "'.$product['type'].'", "'.$product['packaging_type'].'", "'.$product['primary_gtin'].'", "'.$product['published'].'", "'.mysqli_real_escape_string($linker, $product['weights_and_measures']).'", "'.mysqli_real_escape_string($linker, $product['dimensions']).'", "'.mysqli_real_escape_string($linker, $product['case_configuration']).'", "'.$product['hs_code'].'", "'.$product['igst'].'", "'.$product['cgst'].'", "'.$product['sgst'].'", "'.mysqli_real_escape_string($linker, $product['margin']).'", "'.mysqli_real_escape_string($linker, $product['attributes']).'", "'.mysqli_real_escape_string($linker, $product['additional_attributes']).'", "'.$product['image_front'].'", "'.$product['image_back'].'", "'.$product['image_top'].'", "'.$product['image_bottom'].'", "'.$product['image_left'].'", "'.$product['image_right'].'", "'.$product['image_top_left'].'", "'.$product['image_top_right'].'", "'.$product['importedOn'].'", "'.$product['updatedOn'].'"';
	}
	private function insertBrand($db, $brandName, $gpc_code, $linker)
	{
		$gpc_code = $gpc_code ? $gpc_code : "";
		$insertQuery = 'INSERT INTO gs1_brand_source (brandName, gpcCode) VALUES ("'.mysqli_real_escape_string($linker, $brandName).'", "'.$gpc_code.'")';
		$tmpsdata = $db->query($insertQuery);
		return $tmpsdata ? $db->insert_id() : 0;
	}
	private function insertProducts($db, $url, $headers, $starter, $catID, $subcatID, $gcpID, $logID)
	{
		// MYSQLI
		$pdsn = parse_url(DSN);
        $mysql_db = preg_replace("@^\/@", '', $pdsn['path']);
        $linker = new mysqli($pdsn['host'], $pdsn['user'], $pdsn['pass'], $mysql_db) or die("Could not connect");
		// MYSQLI
		$totalInserted = 0;
		$totalUpdated = 0;
		$last = $starter + 1;
		$totalResults = 0;
		$resultsPerPage = 0;
		$currentPageResults = 0;
		for($i = $starter; $i <= $last; $i++)
		{
			$data = $this->getData($url.'&page='.$i, [], 'GET', $headers);
			$totalResults = $data->pageInfo->totalResults;
			$resultsPerPage = $data->pageInfo->resultsPerPage;
			if(count($data->items) > 0)
			{
				$currentPageResults = $data->pageInfo->currentPageResults;
				$last++;
				$products = array_map(function($item) use ($db, $linker, $catID, $subcatID, $gcpID)
				{
					$returns = [];
					$returns['company_detail'] = json_encode($item->company_detail);
					if($item->brand)
					{
						$qry = 'select * from gs1_brand_source where brandName = "'. mysqli_real_escape_string($linker, $item->brand).'"';
						$tmpsdata = $db->getMultipleData($qry, true);
						if($tmpsdata)
						{
							$returns['brandId'] = $tmpsdata[0]['id'];
							$returns['brand'] = $item->brand;
						}
						else
						{
							$returns['brandId'] = $this->insertBrand($db, $item->brand, $item->gpc_code, $linker);
							$returns['brand'] = $item->brand;
						}
					}
					else
					{
						$returns['brandId'] = 0;
						$returns['brand'] = "";
					}
					$returns['unique_key'] = md5(json_encode($item));
					$returns['isValid'] = (($item->brand) || (@$item->hs_code) || ($item->mrp)) ? 1 : 0;
					$returns['name'] = $item->name;
					$returns['gtin'] = $item->gtin;
					$returns['caution'] = $item->caution;
					$returns['sku_code'] = $item->sku_code;
					$returns['description'] = $item->description;
					
					if($catID == 0)
					{
						$returns['categoryId'] = $catID;
						$returns['category'] = $item->category;
					}
					else
					{
						if($item->category) // category
						{
							$qry = 'select * from gs1_category where categoryName = "'.$item->category.'"';
							$tmpsdata = $db->getMultipleData($qry, true);
							if($tmpsdata)
							{
								$returns['categoryId'] = $tmpsdata[0]['id'];
								$returns['category'] = $item->category;
							}
							else
							{
								$returns['categoryId'] = 0;
								$returns['category'] = $item->category;
							}
						}
						else
						{
							$returns['categoryId'] = 0;
							$returns['category'] = "";
						}
					}
					if($subcatID > 0)
					{
						$returns['subCategoryId'] = $subcatID;
						$returns['sub_category'] = $item->sub_category;
					}
					else
					{
						if($item->sub_category) // subcategory
						{
							$qry = 'select * from gs1_subCategory where subCategoryName = "'.$item->sub_category.'"';
							$tmpsdata = $db->getMultipleData($qry, true);
							if($tmpsdata)
							{ 
								$returns['subCategoryId'] = $tmpsdata[0]['id'];
								$returns['sub_category'] = $item->sub_category;
							}
							else
							{
								$returns['subCategoryId'] = 0;
								$returns['sub_category'] = $item->sub_category;
							}
						}
						else
						{
							$returns['subCategoryId'] = 0;
							$returns['sub_category'] = "";
						}
					}

					$returns['gpc_code'] = ($item->gpc_code) ? $item->gpc_code : "";
					$returns['marketing_info'] = $item->marketing_info;
					$returns['url'] = $item->url;
					$returns['activation_date'] = $item->activation_date;
					$returns['deactivation_date'] = $item->deactivation_date;
					$returns['derived_description'] = $item->derived_description;
					$returns['country_of_origin'] = $item->country_of_origin;
					$returns['created_date'] = $item->created_date;
					$returns['modified_date'] = $item->modified_date;
					$returns['type'] = $item->type;
					$returns['packaging_type'] = $item->packaging_type;
					$returns['primary_gtin'] = $item->primary_gtin;
					$returns['published'] = $item->published;
					$returns['weights_and_measures'] = json_encode($item->weights_and_measures);
					$returns['dimensions'] = json_encode($item->dimensions);
					$returns['case_configuration'] = json_encode($item->case_configuration);
					$returns['hs_code'] = $item->hs_code;
					$returns['igst'] = ($item->igst) ? $item->igst : 0.0;
					$returns['cgst'] = ($item->cgst) ? $item->cgst : 0.0;
					$returns['sgst'] = ($item->sgst) ? $item->sgst : 0.0;
					$returns['margin'] = json_encode($item->margin);
					$returns['attributes'] = json_encode($item->attributes);
					$returns['additional_attributes'] = json_encode($item->additional_attributes);
					$returns['image_front'] = $item->images->front;
					$returns['image_back'] = $item->images->back;
					$returns['image_top'] = $item->images->top;
					$returns['image_bottom'] = $item->images->bottom;
					$returns['image_left'] = $item->images->left;
					$returns['image_right'] = $item->images->right;
					$returns['image_top_left'] = $item->images->top_left;
					$returns['image_top_right'] = $item->images->top_right;
					$returns['importedOn'] = date('Y-m-d H:i:s');
					$returns['updatedOn'] = date('Y-m-d H:i:s');
					return $returns;
				}, $data->items);
				$mrpWithGST = array_map(function($item)
				{
					return [
						'unique_key'	=> md5(json_encode($item)),
						'gst'			=> $item->gtin,
						'mrp'			=> [
							'mrp'				=> (@$item->mrp[0]->mrp) ? $item->mrp[0]->mrp : 0,
							'target_market'		=> @$item->mrp[0]->target_market,
							'activation_date'	=> @$item->mrp[0]->activation_date,
							'currency'			=> @$item->mrp[0]->currency,
							'location'			=> @$item->mrp[0]->location
						]
					];
				}, $data->items);

				if(count($products) > 0)
				{
					$x = 0;
					foreach($products as $product)
					{
						$qry = 'select * from gs1_products_source where gtin = "'.$product['gtin'].'"';
						$checkProduct = $db->getMultipleData($qry, true);
						$productId = 0;
						if($checkProduct)
						{
							if($product['unique_key'] != $checkProduct[0]['unique_key'])
							{
								$status = 'update';
								$productId = $checkProduct[0]['id'];

								$pUpdate = $db->perform('gs1_products_source', $product, 'update', 'id='.$productId);

								$qry = 'select * from gs1_product_mrp_source where productId = "'.$productId.'"';
								$checkMrp = $db->getMultipleData($qry, true);
								$totalUpdated++;
								if($checkMrp)
								{
									if($mrpWithGST[$x])
									{
										$mrpId = $checkMrp[0]['id'];
										$mrpdata = $mrpWithGST[$x]['mrp'];
										$mrpdata['productId'] = $productId;
										$pInsert = $db->perform('gs1_product_mrp_source', $mrpdata, 'update', 'productId='.$productId);
									}
								}
								else
								{
									if($mrpWithGST[$x])
									{
										$mrpdata = $mrpWithGST[$x]['mrp'];
										$mrpdata['productId'] = $productId;
										$pInsert = $db->perform('gs1_product_mrp_source', $mrpdata, 'insert');
									}
								}
							}
						}
						else
						{
								$totalInserted++;
								$productString = $this->convertProduct($product, $linker);
								$insertQuery = 'INSERT INTO gs1_products_source ('.join(', ', array_keys($product)).') VALUES ('.$productString.')';
								$pInsert = $db->query($insertQuery);
								$productId = $db->insert_id();


								if($mrpWithGST[$x])
								{
									$mrpdata = $mrpWithGST[$x]['mrp'];
									$mrpdata['productId'] = $productId;
									$pInsert = $db->perform('gs1_product_mrp_source', $mrpdata, 'insert');
								}
						}
						$x++;
					}
				}
			}
			else
			{
				$last = $i-1;
			}
		}


		$updateQuery = 'UPDATE gs1_product_insert_log_source SET isComplete = 1, currentPage = '.$last.', totalResults = '.$totalResults.', totalPage = '.$last.', resultsPerPage = '.$resultsPerPage.', currentPageResults = '.$currentPageResults;
		$updateQuery .= ($totalInserted > 0) ? ', insertedData = '.$totalInserted : '';
		$updateQuery .= ($totalUpdated > 0) ? ', updatedData = '.$totalUpdated : '';
		$updateQuery .= ', endDate = "'.date('Y-m-d H:i:s').'" WHERE id = '.$logID;
		$tmpsdata = $db->query($updateQuery);
		$outs = [
			'inserted'	=> $totalInserted,
			'updated'	=> $totalUpdated
		];
		return $outs;
	}


	private function getData($url, $postData, $method, $headers)
	{
		return $this->curlCall($url, $postData, $method, $headers);
	}
	private function curlCall($url, $data, $method, $header)
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
				CURLOPT_POSTFIELDS =>$data,
				CURLOPT_HTTPHEADER => $header
			)
		);
		$response = curl_exec($curl);
		if (curl_errno($curl))
		{
			return json_decode(curl_error($curl));
		}
		curl_close($curl);
		return json_decode($response);
	}
}

$gs1Cron = new Gs1Cron();
$db = new sqlDb(DSN);
$gs1Cron->getProducts($db);