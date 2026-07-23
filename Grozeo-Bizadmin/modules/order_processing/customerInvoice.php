<?php
$order_order_id = $_REQUEST['order_order_id'];
$order_id = $_REQUEST['order_id'];

$orderData['order_id'] = $order_id;
$orderData['email'] = 'invoice@velosit.in';
$orderData['Customersname'] = 'Back Office';
$invoiceUrl = $db->getItemFromDB("SELECT cfg_Value from sys_configuration where cfg_Name = 'DISPLAY_INVOICE'");
$fields_string = json_encode($orderData);
//print_r($invoiceUrl . "/n");
//print_r($fields_string . "/n");
$opts = array(
    CURLOPT_URL => $invoiceUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POST => count($orderData),
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
echo $response->data;