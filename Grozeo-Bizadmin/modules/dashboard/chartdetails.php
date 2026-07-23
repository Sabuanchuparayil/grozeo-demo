<?php

//For Sales Chart
$today = date('Y-m-d');
$branch = $_SESSION['admin']->finascop_current_branch_id;
$pyramidLevel = $_SESSION['admin']->br_PyramidLevel;
$times = [10, 12, 14, 16, 18, 20, 22, 24];
$detailTimes = ['00:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00', '23.59'];
$saleCount = array();

for ($i = 1; $i < 9; $i++) {
    $fromTime = $today . ' ' . $detailTimes[$i - 1];
    $toTime = $today . ' ' . $detailTimes[$i];
    if ($pyramidLevel == 4) {
        $sales = $db->getItemFromDb("SELECT COUNT(*) FROM retaline_customer_order where order_branch_id = {$branch} and status_id  IN (4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,20,23) and DATE_FORMAT(order_confirmed_on,'%Y-%m-%d %H:%i') BETWEEN  '{$fromTime}' AND '{$toTime}'");
    } else {
        $sales = $db->getItemFromDb("SELECT COUNT(*) FROM finascop_stock_transfer_request where fstr_source = {$branch} and fstr_status = 10 and DATE_FORMAT(fstr_createdOn,'%Y-%m-%d %H:%i') BETWEEN  '{$fromTime}' AND '{$toTime}'");
    }
    array_push($saleCount, $sales);
}
//For Packing Chart

$packingCountEntrrie = array();
$toAssignCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order WHERE fsto_source = {$branch} AND fsto_status = 6 AND DATE_FORMAT(fsto_createdOn,'%Y-%m-%d') = '{$today}' ");
array_push($packingCountEntrrie, $toAssignCount);
$inProgressCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order WHERE fsto_source = {$branch} AND fsto_status NOT IN (6,10) AND DATE_FORMAT(fsto_createdOn,'%Y-%m-%d') = '{$today}' ");
array_push($packingCountEntrrie, $inProgressCount);
$completedCount = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_transfer_order WHERE fsto_source = {$branch} AND fsto_status = 10 AND DATE_FORMAT(fsto_createdOn,'%Y-%m-%d') = '{$today}' ");
array_push($packingCountEntrrie, $completedCount);
//For Delivery Chart
$deliveryCountEntrrie = array();
$pickUpCount = $db->getItemFromDB("SELECT COUNT(*) FROM qugeo_order WHERE quor_Pickupbr_id = {$branch} AND quor_Status = 22 AND DATE_FORMAT(quor_CreatedOn,'%Y-%m-%d') = '{$today}' ");
array_push($deliveryCountEntrrie, $pickUpCount);
$polledCount = $db->getItemFromDB("SELECT COUNT(*) FROM qugeo_order WHERE quor_Pickupbr_id = {$branch} AND quor_Status IN (23,32) AND DATE_FORMAT(quor_CreatedOn,'%Y-%m-%d') = '{$today}' ");
array_push($deliveryCountEntrrie, $polledCount);
$progressCount = $db->getItemFromDB("SELECT COUNT(*) FROM qugeo_order WHERE quor_Pickupbr_id = {$branch} AND quor_Status NOT IN (22,23,32,15) AND DATE_FORMAT(quor_CreatedOn,'%Y-%m-%d') = '{$today}' ");
array_push($deliveryCountEntrrie, $progressCount);
$deliveredCount = $db->getItemFromDB("SELECT COUNT(*) FROM qugeo_order WHERE quor_Pickupbr_id = {$branch} AND quor_Status = 15 AND DATE_FORMAT(quor_CreatedOn,'%Y-%m-%d') = '{$today}' ");
array_push($deliveryCountEntrrie, $deliveredCount);

$orderPickers = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_godown_boy WHERE branch_id = {$branch}");
$onlineOrderPickers = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_godown_boy WHERE branch_id = {$branch} and is_offline = 0");
$drivers = $db->getItemFromDB("SELECT COUNT(*) FROM qugeo_driver WHERE br_id = {$branch}");
$nodb = new \cgoDynamiteDB();

$arrOrder = array();
$arrOrder['PartitionKey'] = array('col' => 'ReportingBranch', 'val' => (int) $branch, 'oper' => '=');
$arrOrder['SortKey'] = array('col' => 'createddate', 'val' => (int) date('Ymd'), 'oper' => '=');
$arrOrder['IndexName'] = 'ReportingBranch-createddate-index';
$arrOrder['queryAttributes'] = array('DriverName', 'v_no', 'v_typename', 'createddatetime', 'TotalJobs', 'Home_Longitude', 'AssignedLoadedWeight', 'AssignedLoadedVolume', 'CurrentLoadedVolume', 'CurrentLoadedWeight', 'apikey', 'DriverId', 'mobno');
$arrOrder['Condition'] = array();
array_push($arrOrder['Condition'], array('col' => 'Is_Live', 'val' => 1, 'oper' => '='));

$response = array();
$rsno = $nodb->query('QugeoLiveVehicles', $arrOrder, 'query');
if (isset($rsno) && count($rsno) > 0) {
    foreach ($rsno as $vehicleapi) {
        $apikey = $vehicleapi['apikey'];
        array_push($response, array('vehno' => $vehicleapi['v_no'], 'DriverId' => $vehicleapi['DriverId'], 'drivername' => $vehicleapi['DriverName'], 'mobno' => $vehicleapi['mobno'], 'logintime' => $vehicleapi['createddatetime'], 'vtype' => $vehicleapi['v_typename'], 'assgwt' => $vehicleapi['AssignedLoadedWeight'], 'assgvol' => $vehicleapi['AssignedLoadedVolume'], 'currwt' => $vehicleapi['CurrentLoadedWeight'], 'currvol' => $vehicleapi['CurrentLoadedVolume'], 'totjobs' => $vehicleapi['TotalJobs'], 'jobscompleted' => '0', 'kmcovered' => '0', 'vehid' => $apikey));
    }
}
$onDrivers = count($response);
$availProducts = $db->getItemFromDB("SELECT COUNT(DISTINCT stit_id) FROM finascop_stock_branch_inventory WHERE branch_id  = {$branch}");
$StoreCustomers = $db->getItemFromDB("SELECT COUNT(DISTINCT order_customer_id) FROM retaline_customer_order WHERE order_branch_id  = {$branch}");
$totalpendingOrders = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order o INNER JOIN finascop_stock_transfer_order so ON so.fstr_id = o.order_id AND so.fsto_ordertype = 1 WHERE o.status_id IN(4,5,6,7,8,9,10,11,12,13,14,15,16, 20, 22, 23, 27,28, 30, 31, 32, 33, 34) AND order_branch_id  = {$branch}");
$totalOrders = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order o INNER JOIN finascop_stock_transfer_order so ON so.fstr_id = o.order_id AND so.fsto_ordertype = 1 WHERE o.status_id > 4 AND order_branch_id  = {$branch}");
$totaldeliveredOrders = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order o INNER JOIN finascop_stock_transfer_order so ON so.fstr_id = o.order_id AND so.fsto_ordertype = 1 WHERE o.status_id IN (17,18) AND order_branch_id  = {$branch}");
$totalEarnings = $db->getItemFromDB("SELECT SUM(total) FROM retaline_customer_order o WHERE o.status_id IN (4,5,6,7,8,9,10,11,12,13,14,15,16, 20, 22, 23, 27,28, 30, 31, 32, 33, 34,17,18) AND order_branch_id  = {$branch}");

$todaypendingOrders = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order o INNER JOIN finascop_stock_transfer_order so ON so.fstr_id = o.order_id AND so.fsto_ordertype = 1 WHERE o.status_id IN(4,5,6,7,8,9,10,11,12,13,14,15,16, 20, 22, 23, 27,28, 30, 31, 32, 33, 34) AND order_branch_id  = {$branch} AND DATE_FORMAT(order_confirm_date,'%Y-%m-%d') = '{$today}'");
$todayOrders = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order o INNER JOIN finascop_stock_transfer_order so ON so.fstr_id = o.order_id AND so.fsto_ordertype = 1 WHERE o.status_id > 4 AND order_branch_id  = {$branch} AND DATE_FORMAT(order_confirm_date,'%Y-%m-%d') = '{$today}'");
$todaydeliveredOrders = $db->getItemFromDB("SELECT COUNT(*) FROM retaline_customer_order o INNER JOIN finascop_stock_transfer_order so ON so.fstr_id = o.order_id AND so.fsto_ordertype = 1 WHERE o.status_id IN (17,18) AND order_branch_id  = {$branch} AND DATE_FORMAT(order_confirm_date,'%Y-%m-%d') = '{$today}'");
$todayEarnings = $db->getItemFromDB("SELECT SUM(total) FROM retaline_customer_order o WHERE o.status_id IN (4,5,6,7,8,9,10,11,12,13,14,15,16, 20, 22, 23, 27,28, 30, 31, 32, 33, 34,17,18) AND order_branch_id  = {$branch} AND DATE_FORMAT(order_confirm_date,'%Y-%m-%d') = '{$today}'");


$bsname = array();
$bsQty = array();
$bsValue = array();
$bsSalesQty = array();
$bsSalesValue = array();

$bsQtyV = $db->getItemFromDB("SELECT SUM(fpod_totalqty) FROM finascop_purchase_order_details INNER JOIN finascop_purchase_order ON fpo_id = fpod_fpoId AND fpo_centralStore = {$branch}");
$bsValueV = $db->getItemFromDB("SELECT SUM(fpod_amount) FROM finascop_purchase_order_details INNER JOIN finascop_purchase_order ON fpo_id = fpod_fpoId AND fpo_centralStore = {$branch}");

$bsSalesQtyV = $db->getItemFromDB("SELECT SUM(fsto_pkdQty) FROM finascop_stock_transfer_order_details fsd INNER JOIN finascop_stock_transfer_order fs ON fs.fsto_id = fsd.fsto_id AND fsto_status = 10 AND fsto_source = {$branch}");
$bsSalesValueV = $db->getItemFromDB("SELECT SUM(fstro_ItemSPincTax) FROM finascop_stock_transfer_order_details fsd INNER JOIN finascop_stock_transfer_order fs ON fs.fsto_id = fsd.fsto_id AND fsto_status = 10 AND fsto_source = {$branch}");
array_push($bsname, $getBaseSation['br_Name']);
array_push($bsQty, $bsQtyV);
array_push($bsValue, $bsValueV);
array_push($bsSalesQty, $bsSalesQtyV);
array_push($bsSalesValue, $bsSalesValueV);


$doiSalesQty = array();
$doiSalesValue = array();
$directSalesQty = $db->getItemFromDB("SELECT SUM(fsto_pkdQty) FROM finascop_stock_transfer_order_details INNER JOIN finascop_stock_transfer_order ON finascop_stock_transfer_order.fsto_id = finascop_stock_transfer_order_details.fsto_id INNER JOIN retaline_customer_order ON order_id = fstr_id WHERE fsto_status = 10 AND fsto_ordertype = 1 ");
array_push($doiSalesQty, $directSalesQty);
$onlineSalesQty = $db->getItemFromDB("SELECT SUM(fsto_pkdQty) FROM finascop_stock_transfer_order_details INNER JOIN finascop_stock_transfer_order ON finascop_stock_transfer_order.fsto_id = finascop_stock_transfer_order_details.fsto_id INNER JOIN retaline_customer_order ON order_id = fstr_id WHERE fsto_status = 10 AND fsto_ordertype = 1 AND payment_mode = 2 ");
array_push($doiSalesQty, $onlineSalesQty);
$istitutionalSalesQty = $db->getItemFromDB("SELECT SUM(fsto_pkdQty) FROM finascop_stock_transfer_order_details INNER JOIN finascop_stock_transfer_order ON finascop_stock_transfer_order.fsto_id = finascop_stock_transfer_order_details.fsto_id INNER JOIN retaline_customer_order ON order_id = fstr_id WHERE fsto_status = 10 AND fsto_ordertype = 1 AND payment_mode = 7 ");
array_push($doiSalesQty, $istitutionalSalesQty);

$directSalesVal = $db->getItemFromDB("SELECT SUM(fstro_ItemSPincTax) FROM finascop_stock_transfer_order_details INNER JOIN finascop_stock_transfer_order ON finascop_stock_transfer_order.fsto_id = finascop_stock_transfer_order_details.fsto_id INNER JOIN retaline_customer_order ON order_id = fstr_id WHERE fsto_status = 10 AND fsto_ordertype = 1");
array_push($doiSalesValue, $directSalesVal);
$onlineSalesVal = $db->getItemFromDB("SELECT SUM(fstro_ItemSPincTax) FROM finascop_stock_transfer_order_details INNER JOIN finascop_stock_transfer_order ON finascop_stock_transfer_order.fsto_id = finascop_stock_transfer_order_details.fsto_id INNER JOIN retaline_customer_order ON order_id = fstr_id WHERE fsto_status = 10 AND fsto_ordertype = 1 AND payment_mode = 2 ");
array_push($doiSalesValue, $onlineSalesVal);
$istitutionalSalesVal = $db->getItemFromDB("SELECT SUM(fstro_ItemSPincTax) FROM finascop_stock_transfer_order_details INNER JOIN finascop_stock_transfer_order ON finascop_stock_transfer_order.fsto_id = finascop_stock_transfer_order_details.fsto_id INNER JOIN retaline_customer_order ON order_id = fstr_id WHERE fsto_status = 10 AND fsto_ordertype = 1 AND payment_mode = 7 ");
array_push($doiSalesValue, $istitutionalSalesVal);
//print_r(json_encode($bsname));

//exit();
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <link rel="stylesheet" type="text/css" href="./resources/css/slim.css">

    <style>
        *,
        ::after,
        ::before {
            box-sizing: border-box;
        }

        .container {
            width: 100%;
            max-width: 100% !important;
            padding-right: var(--bs-gutter-x, .75rem);
            padding-left: var(--bs-gutter-x, .75rem);
            margin-right: auto;
            margin-left: auto;
        }

        .row {
            --bs-gutter-x: 1.5rem;
            --bs-gutter-y: 0;
            display: flex;
            flex-wrap: wrap;
            margin-top: calc(var(--bs-gutter-y) * -1);
            margin-right: calc(var(--bs-gutter-x) * -.5);
            margin-left: calc(var(--bs-gutter-x) * -.5);
        }

        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 1px solid #e3e6f0;
            border-radius: .35rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 .15rem 1.75rem 0 rgba(58, 59, 69, .15);
        }

        .card-header {
            padding: 1rem 1.25rem;
            margin-bottom: 0;
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }

        .card-header h6 {
            margin: 0;
            font-size: 1rem;
            line-height: 1.2;
            font-weight: 700;
            color: #4e73df;
        }

        .card-body {
            flex: 1 1 auto;
            min-height: 1px;
            padding: 1.25rem;
        }
    </style>

</head>

<body>
    <div id="site-wrapper" class="">


        <div class="chartwrap">
            <div class="container">
                <div class="row ">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Total Orders</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Total_Orders.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrNewOrders" class="homeloading"><?php echo $totalOrders; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                    <div class="col-sm-6 col-lg-3 mg-t-10 mg-sm-t-0">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Pending Orders</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Pending_Orders.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrForSale" class="homeloading"><?php echo $totalpendingOrders; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                    <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Delivered Orders</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Delivered_Orders.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrOrderPickers" class="homeloading"><?php echo $totaldeliveredOrders; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                    <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Earnings</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Earnings.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrDrivers" class="homeloading"><?php echo $totalEarnings; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                </div><!--row-->
                <div class="row ">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Today's Orders</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Total_Orders.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrNewOrders" class="homeloading"><?php echo $todayOrders; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                    <div class="col-sm-6 col-lg-3 mg-t-10 mg-sm-t-0">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Pending Orders</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Pending_Orders.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrForSale" class="homeloading"><?php echo $todaypendingOrders; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                    <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Delivered Orders</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Delivered_Orders.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrOrderPickers" class="homeloading"><?php echo $todaydeliveredOrders; ?> </h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                    <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Earnings</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Earnings.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrDrivers" class="homeloading"><?php echo $todayEarnings; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                </div><!--row-->
                <div class="row ">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Customers</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Customers.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrNewOrders" class="homeloading"><?php echo $StoreCustomers; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                    <div class="col-sm-6 col-lg-3 mg-t-10 mg-sm-t-0">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Products for Sale</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Products_sale.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrForSale" class="homeloading"><?php echo $availProducts; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                    <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Order Pickers Online</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Order_picker.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrOrderPickers" class="homeloading"><?php echo $onlineOrderPickers; ?> / <?php echo $orderPickers; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                    <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Drivers available</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Driver_available.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrDrivers" class="homeloading"><?php echo $onDrivers; ?> / <?php echo $drivers; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                </div><!--row-->


                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h6>Sales Chart</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="LineChart" style="width:100%;max-width:600px"></canvas>
                            </div>
                        </div><!--card-->
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h6>Packing Chart</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="PieCharts" style="width:100%;max-width:600px"></canvas>
                            </div>
                        </div><!--card-->
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h6>Delivery Chart</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="BarChart" style="width:100%;max-width:600px"></canvas>
                            </div>
                        </div><!--card-->
                    </div>
                </div><!--row-->                
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Sales - Quantity</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="BarChartSalesCommon" style="width:100%;max-width:600px"></canvas>
                            </div>
                        </div><!--card-->
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Sales - Value</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="BarChartSalesCommonVal" style="width:100%;max-width:600px"></canvas>
                            </div>
                        </div><!--card-->
                    </div>
                </div><!--row-->
            </div><!--container-->

        </div> <!--chartwrap-->

    </div><!--site-wrapper-->

    <script>
        // --- Line Chart ---
        var xValues = <?php echo json_encode($saleCount); ?>;
        var yValues = <?php echo json_encode($times); ?>;

        new Chart("LineChart", {
            type: "line",
            data: {
                labels: xValues,
                datasets: [{
                    fill: false,
                    lineTension: 0,
                    backgroundColor: "rgba(0,0,255,1.0)",
                    borderColor: "rgba(0,0,255,0.1)",
                    data: yValues
                }]
            },
            options: {
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            min: 10,
                            max: 23
                        }
                    }],
                }
            }
        });

        // --- Pie Charts ---
        var xValues = ["To Assign", "In Progress", "Completed"];
        var yValues = <?php echo json_encode($packingCountEntrrie); ?>;
        var barColors = [
            "#b91d47",
            "#00aba9",
            "#1e7145"
        ];

        new Chart("PieCharts", {
            type: "pie",
            data: {
                labels: xValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: yValues
                }]
            },
        });


        // --- Bar Charts ---
        var xValues = ["Pick-up", "Polled", "InProgress", "Delivered"];
        var yValues = <?php echo json_encode($deliveryCountEntrrie); ?>;
        var barColors = "#2b5797";

        new Chart("BarChart", {
            type: "bar",
            data: {
                labels: xValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: yValues
                }]
            },
            options: {
                legend: {
                    display: false
                },
            }
        });
        // --- Bar Charts purchase qty---
        var bpxValues = <?php echo json_encode($bsname); ?>;
        var bpyValues = <?php echo json_encode($bsQty); ?>;
        var barColors = "#2b5797";

        new Chart("BarChartPurchase", {
            type: "bar",
            data: {
                labels: bpxValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: bpyValues
                }]
            },
            options: {
                legend: {
                    display: false
                },
            }
        });
        // --- Bar Charts purchase val---
        var bpxvValues = <?php echo json_encode($bsname); ?>;
        var bpyvValues = <?php echo json_encode($bsValue); ?>;
        var barColors = "#1e7145";

        new Chart("BarChartPurchaseVal", {
            type: "bar",
            data: {
                labels: bpxvValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: bpyvValues
                }]
            },
            options: {
                legend: {
                    display: false
                },
            }
        });
        // --- Bar Charts sales qty---
        var bsxValues = <?php echo json_encode($bsname); ?>;
        var bsyValues = <?php echo json_encode($bsSalesQty); ?>;
        var barColors = "#2b5797";

        new Chart("BarChartSales", {
            type: "bar",
            data: {
                labels: bsxValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: bsyValues
                }]
            },
            options: {
                legend: {
                    display: false
                },
            }
        });
        // --- Bar Charts purchase val---
        var bsxvValues = <?php echo json_encode($bsname); ?>;
        var bsyvValues = <?php echo json_encode($bsSalesValue); ?>;
        var barColors = "#1e7145";

        new Chart("BarChartSalesVal", {
            type: "bar",
            data: {
                labels: bsxvValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: bsyvValues
                }]
            },
            options: {
                legend: {
                    display: false
                },
            }
        });
        // --- Bar Charts sales common qty---
        var bscxValues = ["Total", "Online", "COD"];
        var bscyValues = <?php echo json_encode($doiSalesQty); ?>;
        var barColors = "#2b5797";

        new Chart("BarChartSalesCommon", {
            type: "bar",
            data: {
                labels: bscxValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: bscyValues
                }]
            },
            options: {
                legend: {
                    display: false
                },
            }
        });
        // --- Bar Charts sales common val---
        var bscxvValues = ["Total", "Online", "COD"];
        var bscyvValues = <?php echo json_encode($doiSalesValue); ?>;
        var barColors = "#1e7145";

        new Chart("BarChartSalesCommonVal", {
            type: "bar",
            data: {
                labels: bscxvValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: bscyvValues
                }]
            },
            options: {
                legend: {
                    display: false
                },
            }
        });
    </script>


</body>


</html>