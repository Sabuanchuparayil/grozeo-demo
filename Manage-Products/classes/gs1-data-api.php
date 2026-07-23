<?php

define('ROOT', dirname(dirname(__FILE__)));
define('INCLUDE_PATH', ROOT . "/includes");
define('AWS_ROOT', '/home/system/awsapi');
require(ROOT . '/includes/config.php');
require(INCLUDE_PATH . '/functions.php');
require(ROOT . '/includes/lib.php');
require('TextLocal.php');
require(AWS_ROOT . '/aws-autoloader.php');

// error_reporting(E_ALL);
// ini_set('display_errors', '1');


header('Content-Type: application/json; charset=utf-8');
class Gs1DataApi
{
	function getProducts($db)
	{
		$catID = $_GET['catid'];
		$subcatID = $_GET['subcatid'];
		$updateExisting = $_GET['update'];
		$logID = $_GET['logid'];
		$current = (@$_GET['page']) ? $_GET['page'] : 1;
		$url = 'https://gs1datakart.org/dkapi/product?catid='.$catID;
		$url .= ($subcatID > 0) ? '&subcatid='.$subcatID : '';
		$gcp = 0;
		$headers = ['Authorization: Bearer edd4072e43efb61b88e646fe2776eb97e12620f4'];
		$response = $this->getData($url.'&page='.$current, [], 'GET', $headers);
		$outs = [];
		$count = 0;
		if(@$response->status == true)
		{
			$last = $response->pageInfo->totalPage;
			if($logID == 0)
			{
				$logID = $this->createNewLog($db, $catID, $subcatID, $current, $last, $response->pageInfo->totalResults, $response->pageInfo->resultsPerPage, $response->pageInfo->currentPageResults);
			}
			if($current <= $last)
			{
				$productidInserts = 0;
				$productidUpdates = 0;
				$mrpInserts = 0;
				$mrpUpdates = 0;
				for($i = $current; $i <= $last; $i++)
				{
					$data = $this->getData($url.'&page='.$i, [], 'GET', $headers);
					$products = array_map(function($item) use ($db, $gcp)
					{
						$returns = [];
						if($item->company_detail->name)
						{
							$qry = ($gcp > 0) ? 'select * from gs1_company where gcp = "'.$gcp.'"' : 'select * from gs1_company where companyName = "'.$this->textFormatter($item->company_detail->name).'"';
							$tmpsdata = $db->getMultipleData($qry, true);
							if($tmpsdata)
							{
								$returns['companyId'] = $tmpsdata[0]['id'];
								$returns['company_detail'] = $item->company_detail->name;
							}
							else
							{
								$returns['companyId'] = $this->insertCompany($db, $this->textFormatter($item->company_detail->name));
								$returns['company_detail'] = $item->company_detail->name;
							}
						}
						else
						{
							$returns['companyId'] = 0;
							$returns['company_detail'] = "";
						}
						if($item->brand)
						{
							$qry = 'select * from gs1_brand where brandName = "'.str_replace('"', '', $item->brand).'"';
							$tmpsdata = $db->getMultipleData($qry, true);
							if($tmpsdata)
							{
								$returns['brandId'] = $tmpsdata[0]['id'];
								$returns['brand'] = $item->brand;
							}
							else
							{
								$returns['brandId'] = $this->insertBrand($db, $returns['companyId'], $this->textFormatter($item->brand));
								$returns['brand'] = $item->brand;
							}
						}
						else
						{
							$returns['brandId'] = 0;
							$returns['brand'] = "";
						}
						$returns['isValid'] = (($item->brand) || (@$item->hs_code) || ($item->mrp)) ? 1 : 0;
						$returns['name'] = $item->name;
						$returns['gtin'] = $item->gtin;
						$returns['caution'] = $item->caution;
						$returns['sku_code'] = $item->sku_code;
						$returns['description'] = $item->description;
						if($item->category)
						{
							$qry = 'select * from gs1_category where categoryName = "'.$this->textFormatter($item->category).'"';
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
						if($item->sub_category)
						{
							$qry = 'select * from gs1_subCategory where subCategoryName = "'.$this->textFormatter($item->sub_category).'"';
							$tmpsdata = $db->getMultipleData($qry, true);
							if($tmpsdata)
							{ 
								$returns['subCategoryId'] = $tmpsdata[0]['id'];
								$returns['sub_category'] = $this->textFormatter($item->sub_category);
							}
							else
							{
								$returns['subCategoryId'] = 0;
								$returns['sub_category'] = $this->textFormatter($item->sub_category);
							}
						}
						else
						{
							$returns['subCategoryId'] = 0;
							$returns['sub_category'] = "";
						}
						$returns['gpc_code'] = $item->gpc_code;
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
							'gst'	=> $item->gtin,
							'mrp'	=> [
								'mrp'				=> (@$item->mrp[0]->mrp) ? $item->mrp[0]->mrp : 0,
								'target_market'		=> @$item->mrp[0]->target_market,
								'activation_date'	=> @$item->mrp[0]->activation_date,
								'currency'			=> @$item->mrp[0]->currency,
								'location'			=> @$item->mrp[0]->location
							]
						];
					}, $data->items);
					$gsts = array_column($mrpWithGST, 'gst');
					if(count($products) > 0)
					{
						$x = 0;
						$inserted = 0;
						$updated = 0;
						foreach($products as $product)
						{
							$qry = 'select * from gs1_products where gtin = "'.$product['gtin'].'"';
							$checkProduct = $db->getMultipleData($qry, true);
							$productId = 0;
							if($checkProduct)
							{
								if($updateExisting == 1)
								{
									$status = 'update';
									die(json_encode(['status' => $status]));
									$productId = $checkProduct[0]['id'];

									$pUpdate = $db->perform('gs1_products', $product, 'update', 'id='.$productId);

									$qry = 'select * from gs1_product_mrp where productId = "'.$productId.'"';
									$checkMrp = $db->getMultipleData($qry, true);
									$productidUpdates++;$updated++;
									if($checkMrp)
									{
										if($mrpWithGST[$x])
										{
											$mrpId = $checkMrp[0]['id'];
											$mrpdata = $mrpWithGST[$x]['mrp'];
											$mrpdata['productId'] = $productId;
											$pInsert = $db->perform('gs1_product_mrp', $mrpdata, 'update', 'productId='.$productId);
											$mrpUpdates++;
										}
									}
									else
									{
										if($mrpWithGST[$x])
										{
											$mrpdata = $mrpWithGST[$x]['mrp'];
											$mrpdata['productId'] = $productId;
											$pInsert = $db->perform('gs1_product_mrp', $mrpdata, 'insert');
											$mrpInserts++;
										}
									}
								}
							}
							else
							{
								if($product)
								{
									$productidInserts++;$inserted++;
									$productString = $this->convertProduct($product);
									$insertQuery = 'INSERT INTO gs1_products ('.join(', ', array_keys($product)).') VALUES ('.$productString.')';
									// $pInsert = $db->perform('gs1_products', $product, 'insert');
									$pInsert = $db->query($insertQuery);
									$productId = $db->insert_id();


									if($mrpWithGST[$x])
									{
										$mrpdata = $mrpWithGST[$x]['mrp'];
										$mrpdata['productId'] = $productId;
										$pInsert = $db->perform('gs1_product_mrp', $mrpdata, 'insert');
										$mrpInserts++;
									}
								}
							}
							$x++;
						}
						$qry = 'select insertedData, updatedData from gs1_product_insert_log where id = '.$logID;
						$checkPLog = $db->getMultipleData($qry, true);
						$inserted += (@$checkPLog[0]['insertedData']) ? $checkPLog[0]['insertedData'] : 0;
						$updated += (@$checkPLog[0]['updatedData']) ? $checkPLog[0]['updatedData'] : 0;
						$updateQuery = 'UPDATE gs1_product_insert_log SET insertedData = "'.$inserted.'", updatedData = "'.$updated.'", currentPage = "'.$i.'" WHERE id = '.$logID;
						$tmpsdata = $db->query($updateQuery);
					}
				}
				$outs = [
					'status'	=> 'success',
					'message'	=> [
						$productidInserts.' products inserted',
						$productidUpdates.' products updated',
						$mrpInserts.' mrps inserted',
						$mrpUpdates.' mrps updated'

					]
				];

				$updateQuery = 'UPDATE gs1_product_insert_log SET isComplete = 1, endDate = "'.date('Y-m-d H:i:s').'" WHERE id = '.$logID;
				$tmpsdata = $db->query($updateQuery);
			}
			// echo '<pre>';var_dump($pageData);die();
			// echo json_encode(['count'=> $count, 'data' => array_values($outs)]);
			echo json_encode($outs);
		}
		else
		{
			echo json_encode($response);
		}
	}
	private function convertProduct($product)
	{
		return $product['companyId'].', "'.$this->textFormatter($product['company_detail']).'", '.$product['brandId'].', "'.str_replace("'", '', $this->textFormatter($product['brand'])).'", '.$product['isValid'].', "'.str_replace("'", '', $this->textFormatter($product['name'])).'", "'.$product['gtin'].'", "'.$product['caution'].'", "'.addslashes($product['sku_code']).'", "'.str_replace("'", '', $this->textFormatter($product['description'])).'", '.$product['categoryId'].', "'.$product['category'].'", '.$product['subCategoryId'].', "'.$product['sub_category'].'", "'.$product['gpc_code'].'", "'.str_replace("'", '', $this->textFormatter($product['marketing_info'])).'", "'.str_replace("'", '', $this->textFormatter($product['url'])).'", "'.$product['activation_date'].'", "'.$product['deactivation_date'].'", "'.str_replace("'", '', $this->textFormatter($product['derived_description'])).'", "'.$product['country_of_origin'].'", "'.$product['created_date'].'", "'.$product['modified_date'].'", "'.$product['type'].'", "'.$product['packaging_type'].'", "'.$product['primary_gtin'].'", "'.$product['published'].'", "'.addslashes($product['weights_and_measures']).'", "'.addslashes($product['dimensions']).'", "'.addslashes($product['case_configuration']).'", "'.$product['hs_code'].'", "'.$product['igst'].'", "'.$product['cgst'].'", "'.$product['sgst'].'", "'.addslashes($product['margin']).'", "'.addslashes($product['attributes']).'", "'.addslashes($product['additional_attributes']).'", "'.$product['image_front'].'", "'.$product['image_back'].'", "'.$product['image_top'].'", "'.$product['image_bottom'].'", "'.$product['image_left'].'", "'.$product['image_right'].'", "'.$product['image_top_left'].'", "'.$product['image_top_right'].'", "'.$product['importedOn'].'", "'.$product['updatedOn'].'"';
	}
	private function createNewLog($db, $catID, $subcatID, $current, $last, $totalResults, $resultsPerPage, $currentPageResults)
	{
		$logData = [
			'category'				=> $catID,
			'subCategory'			=> $subcatID,
			'currentPage'			=> $current,
			'totalResults'			=> $totalResults,
			'totalPage'				=> $last,
			'resultsPerPage'		=> $resultsPerPage,
			'currentPageResults'	=> $currentPageResults,
			'type'					=> 'Product',
			'insertedData'			=> 0,
			'updatedData'			=> 0,
			'startDate'				=> date('Y-m-d H:i:s'),
			'isComplete'			=> 0,
			'isReconciled'			=> 0
		];
		$insertNewLog = $db->perform('gs1_product_insert_log', $logData, 'insert');
		return $insertNewLog ? $db->insert_id() : 0;
	}
	private function insertCompany($db, $companyName)
	{
		$insertQuery = 'INSERT INTO gs1_company (companyName, gcp) VALUES ("'.$companyName.'", "0")';
		$tmpsdata = $db->query($insertQuery);
		return $tmpsdata ? $db->insert_id() : 0;
	}
	private function insertBrand($db, $companyId, $brandName)
	{
		$insertQuery = 'INSERT INTO gs1_brand (companyId, brandName) VALUES ("'.$companyId.'", "'.str_replace('"', '', $brandName).'")';
		$tmpsdata = $db->query($insertQuery);
		return $tmpsdata ? $db->insert_id() : 0;
	}

	private function textFormatter($text)
	{
		$text = str_replace('\"', "'", $text);
		$text = str_replace('"', "'", $text);
		return $text;
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
$gs1Api = new Gs1DataApi();
$db = new sqlDb(DSN);
$gs1Api->getProducts($db);