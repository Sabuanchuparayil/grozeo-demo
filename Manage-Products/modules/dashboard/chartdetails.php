<?php

//For Sales Chart
$today = date('Y-m-d');
$totalRetailCategory = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_business_type ");
$activeRetailCategory = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_business_type WHERE status = 1");
$totalDepartment = $db->getItemFromDB("SELECT COUNT(*) FROM mypha_productparent_category ");
$activeDepartment = $db->getItemFromDB("SELECT COUNT(*) FROM mypha_productparent_category WHERE status = 1");
$totalCategory = $db->getItemFromDB("SELECT COUNT(*) FROM mypha_productcategory ");
$activeCategory = $db->getItemFromDB("SELECT COUNT(*) FROM mypha_productcategory WHERE status = 1");
$totalSubCategory = $db->getItemFromDB("SELECT COUNT(*) FROM mypha_productsubcategory ");
$activeSubCategory = $db->getItemFromDB("SELECT COUNT(*) FROM mypha_productsubcategory WHERE status = 1");
$totalBrands = $db->getItemFromDB("SELECT COUNT(*) FROM mypha_productbrands ");
$activeBrands = $db->getItemFromDB("SELECT COUNT(*) FROM mypha_productbrands WHERE status = 1");
$totalProductMasters = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmastername ");
$activeProductMasters = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmastername WHERE status = 1");
$totalProducts = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster ");
$activeProducts = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_stock_itemmaster WHERE stit_status = 1");
$totalUsers = $db->getItemFromDB("SELECT COUNT(*) FROM finascop_usr_master ");
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
                            <h6 class="slim-card-title">Retail Category</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Total_Orders.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrNewOrders" class="homeloading"><?php echo $activeRetailCategory; ?> / <?php echo $totalRetailCategory; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                    <div class="col-sm-6 col-lg-3 mg-t-10 mg-sm-t-0">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Department</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Pending_Orders.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrForSale" class="homeloading"><?php echo $activeDepartment; ?> / <?php echo $totalDepartment; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                    <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Category</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Delivered_Orders.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrOrderPickers" class="homeloading"><?php echo $activeCategory; ?> / <?php echo $totalCategory; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                    <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Subcategory</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Earnings.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrDrivers" class="homeloading"><?php echo $activeSubCategory; ?> / <?php echo $totalSubCategory; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                </div><!--row-->
                <div class="row ">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Brands</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Total_Orders.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrNewOrders" class="homeloading"><?php echo $activeBrands; ?> / <?php echo $totalBrands; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                    <div class="col-sm-6 col-lg-3 mg-t-10 mg-sm-t-0">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Product Masters</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Pending_Orders.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrForSale" class="homeloading"><?php echo $activeProductMasters; ?> / <?php echo $totalProductMasters; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                    <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Products</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Delivered_Orders.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrOrderPickers" class="homeloading"><?php echo $activeProducts; ?> / <?php echo $totalProducts; ?> </h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                    <div class="col-sm-6 col-lg-3 mg-t-10 mg-lg-t-0">
                        <div class="card card-status">
                            <h6 class="slim-card-title">Users</h6>
                            <div class="media">
                                <i class="icon">
                                    <img src="resources/dimages/Customers.png">
                                </i>
                                <div class="media-body">
                                    <h1 id="cpMainContent_ltrDrivers" class="homeloading"><?php echo $totalUsers; ?></h1>
                                    <p></p>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div><!-- card -->
                    </div><!-- col-3 -->
                </div><!--row-->          


                
            </div><!--container-->

        </div> <!--chartwrap-->

    </div><!--site-wrapper-->

    


</body>


</html>