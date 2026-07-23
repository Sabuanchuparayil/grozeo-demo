<?php

define('ROOT', dirname(dirname(__FILE__)));
define('INCLUDE_PATH', ROOT . "/includes");
define('AWS_ROOT', '/home/system/awsapi');
require(ROOT . '/includes/config.php');
require(INCLUDE_PATH . '/functions.php');
require(ROOT . '/includes/lib.php');
require('TextLocal.php');
require(AWS_ROOT . '/aws-autoloader.php');

error_reporting(E_ALL);
ini_set('display_errors', '1');


header('Content-Type: application/json; charset=utf-8');
class Gs1JSON
{
	function getProducts($db)
	{
		$catID = $_GET['catid'];
		$subcatID = $_GET['subcatid'];
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
			if($current <= $last)
			{
				$x = 0;
				if (!file_exists("gs1-json/".$catID."/".$subcatID))
				{
					mkdir("gs1-json/".$catID."/".$subcatID, 0777, true);
				}
				for($i = $current; $i <= $last; $i++)
				{
					$filename = "gs1-json/".$catID."/".$subcatID."/".$i.".json";
					$data = $this->getData($url.'&page='.$i, [], 'GET', $headers);
					file_put_contents($filename, json_encode($data->items, JSON_PRETTY_PRINT));
					$x++;
				}
				$outs = [
					'status'	=> 'success',
					'message'	=> 'Category: '.$catID.' | SubCategory: '.$subcatID.' | Total: '.$last.' | Saved: '.$x
				];
			}
			echo json_encode($outs);
		}
		else
		{
			echo json_encode($response);
		}
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
$gs1Api = new Gs1JSON();
$db = new sqlDb(DSN);
$gs1Api->getProducts($db);