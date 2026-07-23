<?php
require_once(dirname(__DIR__) . '/includes/config.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['admin'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$asin = $_REQUEST['asin'] ?? '';
$apikey = getenv('SCRAPERAPI_KEY') ?: (defined('SCRAPERAPI_KEY') ? SCRAPERAPI_KEY : '');
if (empty($apikey)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'SCRAPERAPI_KEY not configured']);
    exit;
}

$scrapapiUrl = "https://api.scraperapi.com/structured/amazon/product?api_key={$apikey}&asin={$asin}&country_code=in&tld=in";

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => $scrapapiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
));

$response = curl_exec($curl);
curl_close($curl);


// Decode the JSON response
$data = json_decode($response, true);
unset($data['reviews']);
echo '<pre>';
print_r($data);
echo '</pre>';
?>
