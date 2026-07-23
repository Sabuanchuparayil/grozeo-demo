<?php
define('ROOT', dirname(dirname(__FILE__)));
define('INCLUDE_PATH', ROOT . "/includes");
define('AWS_ROOT', '/home/system/awsapi');
require(ROOT . '/includes/config.php');
require(INCLUDE_PATH . '/functions.php');
require(ROOT . '/includes/lib.php');
require('TextLocal.php');
require(AWS_ROOT . '/aws-autoloader.php');

header('Content-Type: application/json; charset=utf-8');

class Gs1Insertions
{

	function getProducts($catID, $subcatID)
	{
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
			$pcount = 0;
			$mrpcount = 0;
			$total = 0;
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
						if(($item->brand) && (@$item->hs_code) && (count($item->mrp) > 0))
						{	
							return ['gtin' => $item->gtin];
						}
					}, $data->items);
					$p = $products;
					$pcount += count(array_filter($p));
					$total += count($data->items);
					$mrpWithGST = array_map(function($item)
					{
						if(($item->brand) && (@$item->hs_code) && (count($item->mrp) > 0))
						{
							return [
								'gst'	=> $item->gtin
							];
						}
					}, $data->items);
					$m = $mrpWithGST;
					$mrpcount += count(array_filter($m));
					$gsts = array_column($mrpWithGST, 'gst');
				}
			}
			echo json_encode([
				'products'	=> $pcount,
				'mrps'		=> $mrpcount,
				'total'		=> $total
			]);
		}
		else
		{
			echo json_encode($response);
		}
	}

	function insertions()
	{
		$pageData = [];
		$current = 0;
		$url = 'https://api.instantwebtools.net/v1/passenger?page='.$current.'&size=10';
		$response = $this->curlCall($url, [], 'GET', []);
		$last = 10;//$response->totalPages;
		if($current < $last)
		{
			for($i = $current; $i < $last; $i++)
			{
				$data = $this->getData($i);
				array_push($pageData, $data->data);
			}
		}
		$toSave['items'] = array_merge(...$pageData);

		file_put_contents("gs1/dummy-data.json", json_encode($toSave, JSON_PRETTY_PRINT));
		$fromFile = json_decode(file_get_contents("gs1/dummy-data.json", true));

		echo json_encode($fromFile->items);
	}
	// private function getData($page)
	private function getData($url, $postData, $method, $headers)
	{
		/*$url = 'https://api.instantwebtools.net/v1/passenger?page='.$page.'&size=10';
		return $this->curlCall($url, [], 'GET', []);*/
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
$gs1 = new Gs1Insertions();
$gs1->getProducts(1, 60);