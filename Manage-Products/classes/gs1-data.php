<?php

/*define('ROOT', dirname(dirname(__FILE__)));
define('INCLUDE_PATH', ROOT . "/includes");
define('AWS_ROOT', '/home/system/awsapi');
require(ROOT . '/includes/config.php');
require(INCLUDE_PATH . '/functions.php');
require(ROOT . '/includes/lib.php');
require('TextLocal.php');
require(AWS_ROOT . '/aws-autoloader.php');*/
/*error_reporting(E_ALL);
ini_set('display_errors', '1');*/


header('Content-Type: application/json; charset=utf-8');
class Gs1Data
{
	function categories($db)
	{
		$categoryData = file_get_contents('gs1/gs1-categories.json', true);
		$categoryList = json_decode($categoryData)->data->categories;
		$values = [
			'insert' => [], 'update' => []
		];
		$values['insert'] = array_map(function($item) use ($db){
			$qry = "select * from gs1_category where id = ".$item['id'];
			$tmpsdata = $db->getMultipleData($qry, true);
			if(!$tmpsdata)
			{
				return '('.$item['id'].', "'.$item['name'].'")';
			}
		}, $categoryList);
		$values['update'] = array_map(function($item) use ($db){
			$qry = "select * from gs1_category where id = ".$item['id'];
			$tmpsdata = $db->getMultipleData($qry, true);
			if(($tmpsdata) && ($tmpsdata[0]['categoryName'] != $item['name']))
			{
				return $item;
			}
		}, $categoryList);
		$values['insert'] = array_filter($values['insert']);
		$values['update'] = array_filter($values['update']);
		if(count($values['insert']) > 0)
		{
			$insertQuery = 'INSERT INTO gs1_category (id, categoryName) VALUES '.implode(', ', $values['insert']);
			$tmpsdata = $db->query($insertQuery);
		}
		if(count($values['update']) > 0)
		{
			foreach($values['update'] as $item)
			{
				$updateQuery = 'UPDATE gs1_category SET categoryName = "'.$item['name'].'" WHERE id = '.$item['id'];
				$tmpsdata = $db->query($updateQuery);
			}
		}
		$outs = [
			'status'	=> 'success',
			'message'	=> [
				count($values['insert']).' categories inserted',
				count($values['update']).' categories updated'
			]
		];
		echo json_encode($outs, JSON_PRETTY_PRINT);
	}
	function companies($db)
	{
		$companyData = file_get_contents('gs1/GS1-Company.json', true);
		$companyList = json_decode($companyData)->items;
		$values = [
			'insert' => [], 'update' => []
		];
		$values['insert'] = array_map(function($item) use ($db){
			$qry = "select * from gs1_company where gcp = ".$item['gcp'];
			$tmpsdata = $db->getMultipleData($qry, true);
			if(!$tmpsdata)
			{
				return '("'.$item['name'].'", "'.$item['gcp'].'")';
			}
		}, $companyList);
		$values['update'] = array_map(function($item) use ($db){
			$qry = "select * from gs1_company where gcp = ".$item['gcp'];
			$tmpsdata = $db->getMultipleData($qry, true);
			if(($tmpsdata) && ($tmpsdata[0]['companyName'] != $item['name']))
			{
				return $item;
			}
		}, $companyList);
		$values['insert'] = array_filter($values['insert']);
		$values['update'] = array_filter($values['update']);
		if(count($values['insert']) > 0)
		{
			$insertQuery = 'INSERT INTO gs1_company (companyName, gcp) VALUES '.implode(', ', $values['insert']);
			$tmpsdata = $db->query($insertQuery);
		}
		if(count($values['update']) > 0)
		{
			foreach($values['update'] as $item)
			{
				$updateQuery = 'UPDATE gs1_company SET companyName = "'.$item['name'].'" WHERE gcp = '.$item['gcp'];
				$tmpsdata = $db->query($updateQuery);
			}
		}
		$outs = [
			'status'	=> 'success',
			'message'	=> [
				count($values['insert']).' companies inserted',
				count($values['update']).' companies updated',

			]
		];
		echo json_encode($outs, JSON_PRETTY_PRINT);
	}
	function products($db)
	{
		$productList = $this->combinedProducts();
		$brandInserts = $this->brands($db, $productList['brands']);
		$subCatInserts = $this->subCategories($db, $productList['subCategories']);


		$products = array_map(function($item) use ($db){
			$returns = [];
			if($item['brand']) // brand
			{
				$qry = 'select * from gs1_brand where brandName = "'.$item['brand'].'"';
				$tmpsdata = $db->getMultipleData($qry, true);
				if($tmpsdata)
				{
					$returns['brandId'] = $tmpsdata[0]['id'];
					$returns['brand'] = $item['brand'];
				}
				else
				{
					$returns['brandId'] = 0;
					$returns['brand'] = $item['brand'];
				}
			}
			else
			{
				$returns['brandId'] = 0;
				$returns['brand'] = "";
			}
			$returns['name'] = $item['name'];
			$returns['gtin'] = $item['gtin'];
			$returns['caution'] = $item['caution'];
			$returns['sku_code'] = $item['sku_code'];
			$returns['description'] = $item['description'];
			if($item['category']) // category
			{
				$qry = 'select * from gs1_category where categoryName = "'.$item['category'].'"';
				$tmpsdata = $db->getMultipleData($qry, true);
				if($tmpsdata)
				{
					$returns['categoryId'] = $tmpsdata[0]['id'];
					$returns['category'] = $item['category'];
				}
				else
				{
					$returns['categoryId'] = 0;
					$returns['category'] = $item['category'];
				}
			}
			else
			{
				$returns['categoryId'] = 0;
				$returns['category'] = "";
			}
			if($item['sub_category']) // subcategory
			{
				$qry = 'select * from gs1_subCategory where subCategoryName = "'.$item['sub_category'].'"';
				$tmpsdata = $db->getMultipleData($qry, true);
				if($tmpsdata)
				{ 
					$returns['subCategoryId'] = $tmpsdata[0]['id'];
					$returns['sub_category'] = $item['sub_category'];
				}
				else
				{
					$returns['subCategoryId'] = 0;
					$returns['sub_category'] = $item['sub_category'];
				}
			}
			else
			{
				$returns['subCategoryId'] = 0;
				$returns['sub_category'] = "";
			}
			$returns['gpc_code'] = $item['gpc_code'];
			$returns['marketing_info'] = $item['marketing_info'];
			$returns['url'] = $item['url'];
			$returns['activation_date'] = $item['activation_date'];
			$returns['deactivation_date'] = $item['deactivation_date'];
			$returns['derived_description'] = $item['derived_description'];
			$returns['country_of_origin'] = $item['country_of_origin'];
			$returns['created_date'] = $item['created_date'];
			$returns['modified_date'] = $item['modified_date'];
			$returns['type'] = $item['type'];
			$returns['packaging_type'] = $item['packaging_type'];
			$returns['primary_gtin'] = $item['primary_gtin'];
			$returns['published'] = $item['published'];
			if($item['company_detail']->name) // company details
			{
				$qry = 'select * from gs1_company where companyName = "'.$item['company_detail']->name.'"';
				$tmpsdata = $db->getMultipleData($qry, true);
				if($tmpsdata)
				{
					$returns['companyId'] = $tmpsdata[0]['id'];
					$returns['company_detail'] = $item['company_detail']->name;
				}
				else
				{
					$returns['companyId'] = 0;
					$returns['company_detail'] = $item['company_detail']->name;
				}
			}
			else
			{
				$returns['companyId'] = 0;
				$returns['company_detail'] = "";
			}
			$returns['weights_and_measures'] = json_encode($item['weights_and_measures']);
			$returns['dimensions'] = json_encode($item['dimensions']);
			$returns['case_configuration'] = json_encode($item['case_configuration']);
			$returns['hs_code'] = $item['hs_code'];
			$returns['igst'] = ($item['igst']) ? $item['igst'] : 0.0;
			$returns['cgst'] = ($item['cgst']) ? $item['cgst'] : 0.0;
			$returns['sgst'] = ($item['sgst']) ? $item['sgst'] : 0.0;
			$returns['margin'] = json_encode($item['margin']);
			$returns['attributes'] = json_encode($item['attributes']);
			$returns['additional_attributes'] = json_encode($item['additional_attributes']);
			$returns['image_front'] = $item['images']->front;
			$returns['image_back'] = $item['images']->back;
			$returns['image_top'] = $item['images']->top;
			$returns['image_bottom'] = $item['images']->bottom;
			$returns['image_left'] = $item['images']->left;
			$returns['image_right'] = $item['images']->right;
			$returns['image_top_left'] = $item['images']->top_left;
			$returns['image_top_right'] = $item['images']->top_right;
			$returns['importedOn'] = date('Y-m-d H:i:s');
			$returns['updatedOn'] = date('Y-m-d H:i:s');
			return $returns;
		}, $productList['products']);

		$mrpWithGST = array_map(function($item){
			if(count($item['mrp']) > 0)
			{
				return [
					'gst'	=> $item['gtin'],
					'mrp'	=> [
						'mrp'				=> @$item['mrp'][0]->mrp,
						'target_market'		=> @$item['mrp'][0]->target_market,
						'activation_date'	=> @$item['mrp'][0]->activation_date,
						'currency'			=> @$item['mrp'][0]->currency,
						'location'			=> @$item['mrp'][0]->location
					]
				];
			}
		}, $productList['products']);
		$gsts = array_column($mrpWithGST, 'gst');

		// $a = array_filter($products, function ($x) { return $x == 'b'; });
		$productidInserts = 0;
		$productidUpdates = 0;
		$mrpInserts = 0;
		$mrpUpdates = 0;
		if(count($products) > 0)
		{
			$x = 0;
			foreach($products as $product)
			{
				$qry = 'select * from gs1_products where gtin = "'.$product['gtin'].'"';
				$checkProduct = $db->getMultipleData($qry, true);
				$productId = 0;
				if($checkProduct)
				{
					$status = 'update';
					$productId = $checkProduct[0]['id'];

					$pUpdate = $db->perform('gs1_products', $product, 'update', 'id='.$productId);

					$qry = 'select * from gs1_product_mrp where productId = "'.$productId.'"';
					$checkMrp = $db->getMultipleData($qry, true);
					$productidUpdates++;
					if($checkMrp)
					{
						$mrpId = $checkMrp[0]['id'];
						$mrpdata = $mrpWithGST[$x]['mrp'];
						$mrpdata['productId'] = $productId;
						$pInsert = $db->perform('gs1_product_mrp', $mrpdata, 'productId='.$productId);
						$mrpUpdates++;
					}
					else
					{
						$mrpdata = $mrpWithGST[$x]['mrp'];
						$mrpdata['productId'] = $productId;
						$pInsert = $db->perform('gs1_product_mrp', $mrpdata, 'insert');
						$mrpInserts++;
					}
				}
				else
				{
					$productidInserts++;
					$pInsert = $db->perform('gs1_products', $product, 'insert');
					$productId = $db->insert_id();

					$mrpdata = $mrpWithGST[$x]['mrp'];
					$mrpdata['productId'] = $productId;
					$pInsert = $db->perform('gs1_product_mrp', $mrpdata, 'insert');
					$mrpInserts++;
				}
				if($id)
				{
				}
				$x++;
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
		echo json_encode($outs, JSON_PRETTY_PRINT);
	}
	private function combinedProducts()
	{
		$healthcare = file_get_contents('gs1/GS1-Health Care-16-1.json', true);
		$healthcare = json_decode($healthcare)->items;
		$household = file_get_contents('gs1/GS1-Household-4-1.json', true);
		$household = json_decode($household)->items;
		$stationery = file_get_contents('gs1/Stationery-40-1.json', true);
		$stationery = json_decode($stationery)->items;

		$products = array_merge($healthcare, $household, $stationery);

		$brands = array_map(function($item){
			if($item['brand'] != NULL)
			{
				return [
					'brand' 	=> $item['brand'],
					'company'	=> $item['company_detail']->name
				];
			}
		}, $products);
		$subCategories = array_map(function($item){
			if($item['brand'] != NULL)
			{
				return [
					'category' 		=> $item['category'],
					'sub_category'	=> $item['sub_category']
				];
			}
		}, $products);
		return [
			'brands'		=> array_filter(array_unique($brands, SORT_REGULAR)),
			'subCategories'	=> array_filter(array_unique($subCategories, SORT_REGULAR)),
			'products'		=> $products
		];
	}
	private function brands($db, $brands)
	{
		$values = array_map(function($item) use ($db)
		{
			$qry = 'select * from gs1_brand where brandName = "'.$item['brand'].'"';
			$tmpsdata = $db->getMultipleData($qry, true);
			if(!$tmpsdata)
			{
				$qry = 'select * from gs1_company where companyName = "'.$item['company'].'"';
				$company = $db->getMultipleData($qry, true);
				$companyId = 0;
				if($company)
				{
					$companyId = $company[0]['id'];
				}
				else
				{
					$insertQuery = 'INSERT INTO gs1_company (companyName, gcp) VALUES ("'.$item['company'].'", "0")';
					$tmpsdata = $db->query($insertQuery);
					$companyId = $db->insert_id();
				}
				return '("'.$item['brand'].'", "'.$companyId.'")';
			}
		}, $brands);
		$values = array_filter($values);
		if(count($values) > 0)
		{
			$insertQuery = 'INSERT INTO gs1_brand (brandName, companyId) VALUES '.implode(', ', $values);
			$tmpsdata = $db->query($insertQuery);
		}
		return $values;
	}
	private function subCategories($db, $subCategories)
	{
		$values = array_map(function($item) use ($db)
		{
			$qry = 'select * from gs1_subCategory where subCategoryName = "'.$item['sub_category'].'"';
			$tmpsdata = $db->getMultipleData($qry, true);
			if(!$tmpsdata)
			{
				$qry = 'select * from gs1_category where categoryName = "'.$item['category'].'"';
				$category = $db->getMultipleData($qry, true);
				$categoryId = 0;
				if($category)
				{
					$categoryId = $category[0]['id'];
				}
				else
				{
					$insertQuery = 'INSERT INTO gs1_category (categoryName) VALUES ("'.$item['category'].'")';
					$tmpsdata = $db->query($insertQuery);
					$categoryId = $db->insert_id();;
				}
				return '("'.$item['sub_category'].'", "'.$categoryId.'")';
			}
		}, $subCategories);
		$values = array_filter($values);
		if(count($values) > 0)
		{
			$insertQuery = 'INSERT INTO gs1_subCategory (subCategoryName, categoryId) VALUES '.implode(', ', $values);
			$tmpsdata = $db->query($insertQuery);
		}
		return $values;
	}


	function getCategories($db, $updateExisting)
	{
		$url = 'https://gs1datakart.org/dkapi/category';
		$headers = ['Authorization: Bearer d6060fd5ea306321570945999e075a6abf92e6ce'];
		$response = $this->getData($url, [], 'GET', $headers);
		$outs = [];
		if(@$response->status == true)
		{
			$categoryList = $response->data->categories;

			$values['insert'] = array_map(function($item) use ($db){
				$qry = "select * from gs1_category where id = ".$item->id;
				$tmpsdata = $db->getMultipleData($qry, true);
				if(!$tmpsdata)
				{
					return '('.$item->id.', "'.$item->name.'")';
				}
			}, $categoryList);
			$values['update'] = [];
			if($updateExisting == 1)
			{
				$values['update'] = array_map(function($item) use ($db){
					$qry = "select * from gs1_category where id = ".$item->id;
					$tmpsdata = $db->getMultipleData($qry, true);
					if(($tmpsdata) && ($tmpsdata[0]['categoryName'] != $item->name))
					{
						return $item;
					}
				}, $categoryList);
			}
			$values['insert'] = array_filter($values['insert']);
			$values['update'] = array_filter($values['update']);
			if(count($values['insert']) > 0)
			{
				$insertQuery = 'INSERT INTO gs1_category (id, categoryName) VALUES '.implode(', ', $values['insert']);
				$tmpsdata = $db->query($insertQuery);
			}
			if(count($values['update']) > 0)
			{
				foreach($values['update'] as $item)
				{
					$updateQuery = 'UPDATE gs1_category SET categoryName = "'.$item->name.'" WHERE id = '.$item->id;
					$tmpsdata = $db->query($updateQuery);
				}
			}
			$outs = [
				'status'	=> 'success',
				'message'	=> [
					count($values['insert']).' categories inserted',
					count($values['update']).' categories updated'
				]
			];
			echo json_encode($outs);
		}
	}
	function getSubCategories($db, $catId, $updateExisting)
	{
		$url = 'https://gs1datakart.org/dkapi/category?catid='.$catId;
		$headers = ['Authorization: Bearer d6060fd5ea306321570945999e075a6abf92e6ce'];
		$response = $this->getData($url, [], 'GET', $headers);
		$outs = [];
		if(@$response->status == true)
		{
			// die(json_encode($response->data->subcategories));
			$subCategoryList = $response->data->subcategories;

			$values['insert'] = array_map(function($item) use ($db, $catId){
				if(!is_null($item->id))
				{
					$qry = "select * from gs1_subCategory where id = ".$item->id;
					$tmpsdata = $db->getMultipleData($qry, true);
					if(!$tmpsdata)
					{
						return '('.$item->id.', "'.$item->name.'", '.$catId.')';
					}
				}

			}, $subCategoryList);
			$values['update'] = [];
			if($updateExisting == 1)
			{
				$values['update'] = array_map(function($item) use ($db){
					if(!is_null($item->id))
					{
						$qry = "select * from gs1_subCategory where id = ".$item->id;
						$tmpsdata = $db->getMultipleData($qry, true);
						if(($tmpsdata) && ($tmpsdata[0]['subCategoryName'] != $item->name))
						{
							return $item;
						}
					}
				}, $subCategoryList);
			}
			$values['insert'] = array_filter($values['insert']);
			$values['update'] = array_filter($values['update']);
			if(count($values['insert']) > 0)
			{
				$insertQuery = 'INSERT INTO gs1_subCategory (id, subCategoryName, categoryId) VALUES '.implode(', ', $values['insert']);
				$tmpsdata = $db->query($insertQuery);
			}
			if(count($values['update']) > 0)
			{
				foreach($values['update'] as $item)
				{
					$updateQuery = 'UPDATE gs1_subCategory SET subCategoryName = "'.$item->name.'" WHERE id = '.$item->id;
					$tmpsdata = $db->query($updateQuery);
				}
			}
			$outs = [
				'status'	=> 'success',
				'message'	=> [
					count($values['insert']).' subcategories inserted',
					count($values['update']).' subcategories updated'
				]
			];
			echo json_encode($outs);
		}
	}
	function getCompanies($db, $updateExisting)
	{
		$current = 1;
		$url = 'https://gs1datakart.org/dkapi/company';
		$headers = ['Authorization: Bearer d6060fd5ea306321570945999e075a6abf92e6ce'];
		$response = $this->getData($url.'?page='.$current, [], 'GET', $headers);
		$outs = [];
		if(@$response->status == true)
		{
			$last = $response->pageInfo->totalPage;
			if($current < $last)
			{
				$companyInserts = 0;
				$companyUpdates = 0;
				for($i = $current; $i <= $last; $i++)
				{
					$data = $this->getData($url.'?page='.$i, [], 'GET', $headers);

					$companies = array_map(function($item){
						return [
							'gcp'			=> $item->gcp,
							'companyName'	=> $item->name
						];
					}, $data->items);

					if(count($companies) > 0)
					{
						foreach($companies as $company)
						{
							$qry = 'select * from gs1_company where gcp = "'.$company['gcp'].'"';
							$checkCompany = $db->getMultipleData($qry, true);
							$c = 0;
							if($checkCompany)
							{
								if($updateExisting == 1)
								{
									$status = 'update';
									$companyId = $checkCompany[0]['id'];
									$updateQuery = 'UPDATE gs1_company SET companyName = "'.$company['companyName'].'" WHERE id = '.$companyId;
									$tmpsdata = $db->query($updateQuery);
									$companyUpdates++;
								}
							}
							else
							{
								$companyInserts++;
								$insertQuery = 'INSERT INTO gs1_company (companyName, gcp) VALUES ("'.$company['companyName'].'", "'.$company['gcp'].'")';
								$tmpsdata = $db->query($insertQuery);
								// $pInsert = $db->perform('gs1_company', $company, 'insert');
							}
						}
					}
				}
				$outs = [
					'status'	=> 'success',
					'message'	=> [
						$companyInserts.' companies inserted',
						$companyUpdates.' companies updated'
					]
				];
			}
			echo json_encode($outs);
		}
		else
		{
			echo json_encode($response);
		}
	}
	function getProductsPageDetails($catID, $subcatID)
	{
		$url = 'https://gs1datakart.org/dkapi/product?catid='.$catID.'&subcatid='.$subcatID;
		$headers = ['Authorization: Bearer edd4072e43efb61b88e646fe2776eb97e12620f4'];
		$response = $this->getData($url, [], 'GET', $headers);
		$outs = [];
		if(@$response->status == true)
		{
			return json_encode($response->pageInfo);
		}
		return $response;
	}
	private function insertCompany($db, $companyName)
	{
		$insertQuery = 'INSERT INTO gs1_company (companyName, gcp) VALUES ("'.$companyName.'", "0")';
		$tmpsdata = $db->query($insertQuery);
		return $tmpsdata ? $db->insert_id() : 0;
	}
	private function insertBrand($db, $companyId, $brandName)
	{
		$insertQuery = 'INSERT INTO gs1_brand (companyId, brandName) VALUES ("'.$companyId.'", "'.$brandName.'")';
		$tmpsdata = $db->query($insertQuery);
		return $tmpsdata ? $db->insert_id() : 0;
	}
	private function textFormatter($text)
	{
		/*if(strpos($text, '\"'))
		{
			return str_replace('\"', "'", $text);
		}
		if(strpos($text, '"'))
		{
			return str_replace('"', "'", $text);
		}*/
		$text = str_replace('\"', "'", $text);
		$text = str_replace('"', "'", $text);
		return $text;
	}
	function getProducts($db, $catID, $subcatID = 0, $gcp = 0, $updateExisting, $logID)
	{
		$itrations = $db->perform('gs1_log', ['content' => 'getProducts Called for Cat: '.$catID.', SubCat: '.$subcatID], 'insert');
		$current = 1;
		$url = 'https://gs1datakart.org/dkapi/product?catid='.$catID;
		$url .= ($subcatID > 0) ? '&subcatid='.$subcatID : '';
		$url .= ($gcp > 0) ? '&gcp='.$gcp : '';
		$headers = ['Authorization: Bearer edd4072e43efb61b88e646fe2776eb97e12620f4'];
		$response = $this->getData($url.'&page='.$current, [], 'GET', $headers);
		$outs = [];
		$count = 0;
		if(@$response->status == true)
		{
			$last = $response->pageInfo->totalPage;
			$itrations = $db->perform('gs1_log', ['content' => 'pageInfo Generated for Cat: '.$catID.', SubCat: '.$subcatID.', Total Pages: '.$last], 'insert');
			if($current <= $last)
			{
				$productidInserts = 0;
				$productidUpdates = 0;
				$mrpInserts = 0;
				$mrpUpdates = 0;
				for($i = $current; $i <= $last; $i++)
				{
					$itrations = $db->perform('gs1_log', ['content' => 'Cat: '.$catID.', SubCat: '.$subcatID.', Itration: '.$i], 'insert');
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
							$qry = 'select * from gs1_brand where brandName = "'.$this->textFormatter($item->brand).'"';
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
					// die(json_encode($products));
					/*$outs[] = $products;
					$count += count($products);*/

					$itrations = $db->perform('gs1_log', ['content' => 'Cat: '.$catID.', SubCat: '.$subcatID.', Itration: '.$i.', Products Got: '.count($products)], 'insert');
					$mrpWithGST = array_map(function($item)
					{
						return [
							'gst'	=> $item->gtin,
							'mrp'	=> [
								'mrp'				=> @$item->mrp[0]->mrp,
								'target_market'		=> @$item->mrp[0]->target_market,
								'activation_date'	=> @$item->mrp[0]->activation_date,
								'currency'			=> @$item->mrp[0]->currency,
								'location'			=> @$item->mrp[0]->location
							]
						];
					}, $data->items);
					$gsts = array_column($mrpWithGST, 'gst');

					$itrations = $db->perform('gs1_log', ['content' => 'Cat: '.$catID.', SubCat: '.$subcatID.', Itration: '.$i.', Products Got: '.count($products).', MRPs Got: '.count($mrpWithGST)], 'insert');

					if(count($products) > 0)
					{
						$x = 0;
						$inserted = 0;
						$updated = 0;

						$itrations = $db->perform('gs1_log', ['content' => 'Cat: '.$catID.', SubCat: '.$subcatID.', Itration: '.$i.', Insertion Started'], 'insert');
						foreach($products as $product)
						{
							$qry = 'select * from gs1_products where gtin = "'.$product['gtin'].'"';
							$checkProduct = $db->getMultipleData($qry, true);
							// die(json_encode(['count' => count($checkProduct)]));
							$productId = 0;
							if($checkProduct)
							{
								$itrations = $db->perform('gs1_log', ['content' => 'Cat: '.$catID.', SubCat: '.$subcatID.', Itration: '.$i.', Product '.$product['gtin'].' exists'], 'insert');
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
									$itrations = $db->perform('gs1_log', ['content' => 'Cat: '.$catID.', SubCat: '.$subcatID.', Itration: '.$i.', Product '.$product['gtin'].' insertion started'], 'insert');
									try{
										$itrations = $db->perform('gs1_log', ['content' => 'Cat: '.$catID.', SubCat: '.$subcatID.', Itration: '.$i.', Product '.$product['gtin'].' inside try'], 'insert');
										$productidInserts++;$inserted++;
										// $pInsert = $db->perform('gs1_products', $product, 'insert');
										
										$productString = $this->convertProduct($product);
										$insertQuery = 'INSERT INTO ('.join(', ', array_keys($product)).') VALUES ('.$productString.')';
										$pInsert = $db->query($insertQuery);
										$productId = $db->insert_id();
										if($productId)
										{
											$itrations = $db->perform('gs1_log', ['content' => 'Cat: '.$catID.', SubCat: '.$subcatID.', Itration: '.$i.', Product '.$product['gtin'].' inserted'], 'insert');
										}


										if($mrpWithGST[$x])
										{
											$mrpdata = $mrpWithGST[$x]['mrp'];
											$mrpdata['productId'] = $productId;
											$pInsert = $db->perform('gs1_product_mrp', $mrpdata, 'insert');
											$mrpInserts++;

										$itrations = $db->perform('gs1_log', ['content' => 'Cat: '.$catID.', SubCat: '.$subcatID.', Itration: '.$i.', MRP '.$product['gtin'].' insreted'], 'insert');
										}
									}
									catch(Exception $e)
									{
										$itrations = $db->perform('gs1_log', ['content' => 'Cat: '.$catID.', SubCat: '.$subcatID.', Itration: '.$i.', Exception: '.$e->getMessage()], 'insert');
									}
								}
							}
							$x++;
						}
						$qry = 'select insertedData, updatedData from gs1_product_insert_log where id = '.$logID;
						$checkPLog = $db->getMultipleData($qry, true);
						$inserted += (@$checkPLog[0]['insertedData']) ? $checkPLog[0]['insertedData'] : 0;
						$updated += (@$checkPLog[0]['updatedData']) ? $checkPLog[0]['updatedData'] : 0;
						$updateQuery = 'UPDATE gs1_product_insert_log SET insertedData = "'.$inserted.'", updatedData = "'.$updated.'" WHERE id = '.$logID;
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
		return $product['companyId'].', "'.$this->textFormatter($product['company_detail']).'", '.$product['brandId'].', "'.str_replace("'", '', $this->textFormatter($product['brand'])).'", '.$product['isValid'].', "'.str_replace("'", '', $this->textFormatter($product['name'])).'", "'.$product['gtin'].'", "'.$product['caution'].'", "'.$product['sku_code'].'", "'.str_replace("'", '', $this->textFormatter($product['description'])).'", '.$product['categoryId'].', "'.$product['category'].'", '.$product['subCategoryId'].', "'.$product['sub_category'].'", "'.$product['gpc_code'].'", "'.str_replace("'", '', $this->textFormatter($product['marketing_info'])).'", "'.str_replace("'", '', $this->textFormatter($product['url'])).'", "'.$product['activation_date'].'", "'.$product['deactivation_date'].'", "'.str_replace("'", '', $this->textFormatter($product['derived_description'])).'", "'.$product['country_of_origin'].'", "'.$product['created_date'].'", "'.$product['modified_date'].'", "'.$product['type'].'", "'.$product['packaging_type'].'", "'.$product['primary_gtin'].'", "'.$product['published'].'", "'.addslashes($product['weights_and_measures']).'", "'.addslashes($product['dimensions']).'", "'.addslashes($product['case_configuration']).'", "'.$product['hs_code'].'", "'.$product['igst'].'", "'.$product['cgst'].'", "'.$product['sgst'].'", "'.addslashes($product['margin']).'", "'.addslashes($product['attributes']).'", "'.addslashes($product['additional_attributes']).'", "'.$product['image_front'].'", "'.$product['image_back'].'", "'.$product['image_top'].'", "'.$product['image_bottom'].'", "'.$product['image_left'].'", "'.$product['image_right'].'", "'.$product['image_top_left'].'", "'.$product['image_top_right'].'", "'.$product['importedOn'].'", "'.$product['updatedOn'].'"';
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
	function testings($db)
	{
		/*$tableData = [
			'companyName'	=> 'NCOMP 1234',
			'gcp'			=> 'bb1769999'
		];
		$tmpsdata = $db->perform('gs1_company', $tableData, 'insert');
		echo $db->insert_id();*/
		// var_dump(com_create_guid());
		// phpinfo();

		$qry = 'select * from gs1_products where gtin = "'.$product['gtin'].'"';
		$checkProduct = $db->getMultipleData($qry, true);
		$productId = 0;
		if($checkProduct && ($updateExisting == 1))
		{
			die(json_encode($checkProduct));
		}
		else
		{
			die(json_encode(['status' => 'ELSE']));
		}
		echo json_encode([
			'cat'	=> $_GET['catid'],
			'page'	=> $_GET['page']
		]);
	}
}
/*$gs1 = new Gs1Data();
$db = new sqlDb(DSN);*/
// $gs1->getProductsPageDetails($db, $i, 0, 0, 0);
// $gs1->getCategories($db, 0);
/*for($i = 1; $i <= 50; $i++)
{
	$gs1->getSubCategories($db, $i, 0);
}*/
// $gs1->getCompanies($db, 0);
/*for($i = 1; $i <= 50; $i++)
{*/
	// $gs1->getProducts($db, 1, 9, 0, 0, 0);
	// $gs1->getProductsPageDetails($db, $i, 0, 0, 0);
// }