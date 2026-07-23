<?php
$fstr_id = $_REQUEST['fstr_id'];
$orderDetails = $db->getFromDB("SELECT order_order_id,order_method,delivery_rule_type FROM retaline_customer_order WHERE order_id = {$fstr_id}", true);
$orderID = $orderDetails['order_order_id'];
$orderMethod = $orderDetails['order_method'];
$nodb = new \cgoDynamiteDB();
$today = date("Ymd");
$arrOrder = array();
$arrOrder['PartitionKey'] = array('col' => 'orderID', 'val' => (string) $orderID, 'oper' => '=');
$arrOrder['SortKey'] = array('col' => 'orderMethod', 'val' => (string)$orderMethod, 'oper' => '=');
$arrOrder['IndexName'] = 'orderID-orderMethod-index';
$arrOrder['queryAttributes'] = array('APIName', 'response', 'APIURL', 'request', 'APIHeaders', 'tstamp','type');

$response = array();
$rsno = $nodb->query('shipping_consignment_log', $arrOrder, 'query');
if (isset($rsno) && count($rsno) > 0) {
    foreach ($rsno as $callLog) {
        array_push($response, array(
            'APIName' => $callLog['APIName'],
            'response' => $callLog['response'],
            'APIURL' => $callLog['APIURL'],
            'request' => $callLog['request'],
            'APIHeaders' => $callLog['APIHeaders'],
            'tstamp' => $callLog['tstamp'],
            'type' => $callLog['type'],
        ));
    }
    $response = orderBy($response, 'tstamp');
}
$count = count($response);
$filtered = array_values(array_filter($response, function ($log) {
    return $log['APIName'] !== 'checkDeliveryAvailable';
}));
$response = $filtered;

function renderJsonAsTable($data)
{
    if (!is_array($data)) return '';

    $html = '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; margin-bottom: 10px;">';
    foreach ($data as $key => $value) {
        $html .= '<tr>';
        $html .= '<td><strong>' . htmlspecialchars($key) . '</strong></td>';
        if (is_array($value)) {
            $html .= '<td>' . renderJsonAsTable($value) . '</td>';
        } else {
            $html .= '<td>' . htmlspecialchars($value) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</table>';
    return $html;
}


if ($count > 0) {
?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>API Call Logs</title>
        <style>
            .log-container {
                font-family: Arial, sans-serif;
                margin: 20px 0;
            }

            .card {
                border: 1px solid #ccc;
                border-radius: 5px;
                padding: 15px 20px;
                margin-bottom: 20px;
                background-color: #f9f9f9;
            }

            .card .row {
                margin-bottom: 10px;
            }

            .card .key {
                font-weight: bold;
                color: #333;
            }

            .card ul {
                padding-left: 20px;
                margin-top: 5px;
            }

            .card li {
                list-style: disc;
                color: #444;
            }
        </style>
    </head>

    <body>

        <h2>API Logs</h2>

        <div class="log-container">
            <?php foreach ($response as $log) {
                switch ($log['type']) {
                    case '1':
                        $providerName = 'Shipyari';
                        break;
                    case '2':
                        $providerName = 'World Options';
                        break;
                    case '4':
                        $providerName = 'Uber';
                        break;
                    case '6':
                        $providerName = 'clickpost(courier)';
                        break;
                    case '7':
                        $providerName = 'clickpost(Express)';
                        break;
                    case '8':
                        $providerName = 'Porter';
                        break;
                        
                }
            ?>
                <div class="card">
                    <div><strong>API Name:</strong> <?= htmlspecialchars($log['APIName']) ?></div>
                    <div><strong>Timestamp:</strong> <?= htmlspecialchars($log['tstamp']) ?></div>
                    <div><strong>Provider:</strong> <?php echo $providerName; ?></div>
                    <div><strong>API URL:</strong> <?= htmlspecialchars($log['APIURL']) ?></div>

                    <div><strong>Request:</strong>
                        <?php
                        $requestJson = $log['request'];
                        $requestData = json_decode($log['request'], true);
                        if ($requestData) {
                            echo renderJsonAsTable($requestData);
                        ?>
                            <button onclick="copyToClipboard('request-json-<?= $index ?>')">Copy Request</button>
                            <pre id="request-json-<?= $index ?>" style="display:none;"><?= htmlspecialchars($requestJson) ?></pre>
                        <?php
                        } else {
                            echo '<em>Invalid Request</em>';
                        }
                        ?>
                    </div>

                    <div><strong>Response:</strong>
                        <?php
                        $responseJson = $log['response'];
                        $responseData = json_decode($log['response'], true);
                        if ($responseData) {
                            echo renderJsonAsTable($responseData);
                        ?>
                            <button onclick="copyToClipboard('response-json-<?= $index ?>')">Copy Response</button>
                            <pre id="response-json-<?= $index ?>" style="display:none;"><?= htmlspecialchars($responseJson) ?></pre>
                        <?php
                        } else {
                            echo '<em>Invalid Response</em>';
                        }
                        ?>
                    </div>

                </div>

        <?php }
        } ?>
        </div>

    </body>
    <script>
        function copyToClipboard(elementId) {
            const el = document.getElementById(elementId);
            const text = el.innerText || el.textContent;

            navigator.clipboard.writeText(text).then(() => {}).catch(err => {});
        }
    </script>

    </html>