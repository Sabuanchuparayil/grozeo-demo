<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" Title="Partner" CodeBehind="Default.aspx.cs" Inherits="RetalineProAgent.Merchant.Default" %>

<%@ Import Namespace="System.Configuration" %>

<asp:Content ContentPlaceHolderID="head" runat="server">
<script src="/Content/js/custom/home.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server"></asp:Content>
<%--<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
            <h6 class="slim-pagetitle"><%= this.CurrentUser.StoreGroupName %></h6>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">

    <div class="Dashboard_widgets_wrap">
            <div class="row row-sm">
              <div class="col-sm-6 col-lg-3 mb-3 mb-lg-4">
                <div class="card h-100">
                  <div class="card-body pb-3 px-4 pt-4 Dashboard_widgets widgets_Pending_orders ">
                    <div class="d-flex align-items-center dash_title mb-4">
                      <%--<i class="fa-thin fa-bags-shopping mr-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>--%>
                        <i class="fa-thin fa-list-check mr-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                      <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Pending orders</h5>
                    </div>
                    <div class="dash-content d-flex flex-md-nowrap align-items-center justify-content-center mb-3">
                      <h3 ID="ltrNewOrders" class="homeloading" runat="server">
                          <div class="lodingbusy"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                      </h3>
                        <span class="dsb_card_dataerrormsg novalue">0</span>
                    </div>
                    <div class="dash_btn_wrap d-flex justify-content-lg-end">
                      <a class="btn dash-btn" href="/Tenant/PendingOrders">Details<i class="fa-regular fa-calendar-lines ml-2"></i></a>
                    </div>
                  </div>
                </div><!--card-->
              </div><!--col-lg-3-->
              <div class="col-sm-6 col-lg-3 mb-3 mb-lg-4">
                <div class="card h-100">
                  <div class="card-body pb-3 px-4 pt-4 Dashboard_widgets">
                    <div class="d-flex align-items-center dash_title mb-4">
                      <i class="fa-thin fa-list-ol mr-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                      <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Total orders</h5>
                    </div>
                    <div class="dash-content d-flex flex-md-nowrap align-items-center justify-content-center mb-3">
                      <h3 ID="ltrTtlOrders" class="homeloading" runat="server">
                          <div class="lodingbusy"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                      </h3>
                        <span class="dsb_card_dataerrormsg novalue">0</span>
                    </div>
                    <div class="dash_btn_wrap d-flex justify-content-lg-end">
                      <a class="btn dash-btn" href="/Tenant/SaleAndReturnOrders">View Insight<i class="fa-regular fa-calendar-lines ml-2"></i></a>
                    </div>
                  </div>
                </div><!--card-->
              </div><!--col-lg-3-->
              <div class="col-sm-6 col-lg-3 mb-3 mb-lg-4">
                <div class="card h-100">
                  <div class="card-body pb-3 px-4 pt-4 Dashboard_widgets">
                    <div class="d-flex align-items-center dash_title mb-4">
                      <i class="fa-thin fa-square-list mr-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                      <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Total sales</h5>
                    </div>
                    <div class="dash-content d-flex flex-md-nowrap align-items-center justify-content-center mb-3">
                      <h3 ID="ltrTtlSales" class="homeloading" runat="server">
                          <div class="lodingbusy"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>  
                      </h3>
                        <span class="dsb_card_dataerrormsg novalue">0</span>
                    </div>
                    <div class="dash_btn_wrap d-flex justify-content-lg-end">
                      <a class="float-lg-right btn dash-btn" href="/Tenant/SaleAndReturnOrders">Details<i class="fa-regular fa-calendar-lines ml-2"></i></a>
                    </div>
                  </div>
                </div><!--card-->
              </div><!--col-lg-3-->
              <div class="col-sm-6 col-lg-3 mb-3 mb-lg-4">
                <div class="card h-100">
                  <div class="card-body pb-3 px-4 pt-4 Dashboard_widgets">
                    <div class="d-flex align-items-center dash_title mb-4">
                      <i class="fa-thin fa-users-line mr-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                      <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Total customers</h5>
                    </div>
                    <div class="dash-content d-flex flex-md-nowrap align-items-center justify-content-center mb-3">
                      <h3 ID="ltrTtlCustomers" class="homeloading" runat="server">
                          <div class="lodingbusy"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                      </h3>
                        <span class="dsb_card_dataerrormsg novalue">0</span>
                    </div>
                    <div class="dash_btn_wrap d-flex justify-content-lg-end">
                      <a class="float-lg-right btn dash-btn" href="/Tenant/Customers">Details<i class="fa-regular fa-calendar-lines ml-2"></i></a>
                    </div>
                  </div>
                </div><!--card-->
              </div><!--col-lg-3-->
            </div>

            <div class="row row-sm">
              <div class="col-lg-6 mb-3 mb-lg-4">
                <div class="row row-sm">
                  <div class="col-lg-6 mb-3 mb-lg-0">
                    <div class="row">
                      <div class="col-sm-6 col-lg-12 mb-3 mb-lg-4">
                        <div class="card h-100">
                          <div class="card-body  pb-3 px-4 pt-4 Dashboard_widgets">
                            <div class="d-flex align-items-center dash_title mb-4">
                              <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Order pickers</h5>
                            </div>
                            <div class="dash-content d-flex flex-md-nowrap align-items-center mb-0" ID="ltrOrderPickers" runat="server">
                                
                              <h3  class="homeloading" >
                                  <div class="lodingbusy"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                              </h3>
                            </div>
                            <div class="dash_btn_wrap d-flex justify-content-lg-end">
                              <a class="float-lg-right tx-13" href="/Tenant/OrderPicker">View all <i class="fa fa-arrow-right ml-1" aria-hidden="true"></i></a>
                            </div>
                          </div>
                        </div><!--card-->
                      </div>
                      <div class="col-sm-6 col-lg-12 mb-0">
                        <div class="card h-100">
                          <div class="card-body  pb-3 px-4 pt-4 Dashboard_widgets">
                            <div class="d-flex align-items-center dash_title mb-4">
                              <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Delivery staffs</h5>
                            </div>
                            <div class="dash-content d-flex flex-md-nowrap align-items-center mb-0" ID="ltrDrivers" runat="server">

                              <h3  class="homeloading" >
                                  <div class="lodingbusy"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                              </h3>
                            </div>
                            <div class="dash_btn_wrap d-flex justify-content-lg-end">
                              <a class="float-lg-right tx-13" href="/Tenant/DeliveryStaffs">View all <i class="fa fa-arrow-right ml-1" aria-hidden="true"></i></a>
                            </div>
                          </div>
                        </div><!--card-->
                      </div>

                        <div class="col-12 mb-0" runat="server" visible="false">
                        <div class="card h-100">
                          <div class="card-body  pb-3 px-4 pt-4 Dashboard_widgets">
                            <div class="d-flex align-items-center dash_title mb-4">
                              <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Pending Actions</h5>
                            </div>
                            <div class="dash-content d-flex flex-md-nowrap align-items-center mb-0" ID="ltrPendingActions" runat="server">

                              <h3  class="homeloading" >
                                  <div class="lodingbusy"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                              </h3>
                            </div>
                            <div class="dash_btn_wrap d-flex justify-content-lg-end">
                              <a class="float-lg-right tx-13" href="/Tenant/PendingActions">View all <i class="fa fa-arrow-right ml-1" aria-hidden="true"></i></a>
                            </div>
                          </div>
                        </div><!--card-->
                      </div>
                    </div>
                  </div><!--col-lg-6-->
                  <div class="col-lg-6 mb-0">
                    <div class="card h-100">
                        <div class="card-body p-3 Dashboard_widgets d-flex flex-wrap widgets_Products_sale">
                        <div class="d-flex flex-wrap align-items-center justify-content-center dash_title mb-2 w-100">
                          <i class="fa-thin fa-bags-shopping mb-2 tx-28 tx-primary wd-55 ht-55 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                          <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600 w-100 text-center">Products  for sale</h5>
                        </div>
                        <div class="dash-content d-flex flex-wrap align-items-center justify-content-center mb-1 w-100">
                          <h3 ID="ltrForSale" runat="server" class="homeloading w-100 text-center">
                                <div class="lodingbusy"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                          </h3>
                          <p class="text-center mb-2">Products Added</p>
                        </div>
                        <div class="dash-content d-flex flex-wrap align-items-center justify-content-center mb-1 w-100">
                          <h3 ID="ltrOutOfStock" runat="server" class="homeloading w-100 text-center">
                                <div class="lodingbusy"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
                          </h3>
                            <span class="dsb_card_dataerrormsg novalue">0</span>
                          <p class="text-center mb-2">Listed for Sale</p>
                        </div>
                        <div class="dash_btn_wrap d-flex justify-content-lg-center align-items-end w-100">
                          <a class="btn dash-btn addproductsbtn" href="/Navigations/Products">Manage Products<i class="fa-regular fa-calendar-lines ml-2"></i></a>
                          <%--<a class="btn btn-primary prodt_addnow" style="display:none" href="/Tenant/MyProducts">Add Now<i class="icon ion-plus-circled ml-2"></i></a>--%>
                        </div>
                      </div>
                      <%--<div class="card-body p-3 Dashboard_widgets d-flex flex-wrap widgets_Products_sale">
                        <div class="d-flex flex-wrap align-items-center justify-content-center dash_title mb-3 w-100">
                          <i class="fa-thin fa-bags-shopping mb-4 tx-28 tx-primary wd-55 ht-55 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                          <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600 w-100 text-center">Products for sale</h5>
                        </div>
                        <div class="dash-content d-flex flex-md-nowrap align-items-center justify-content-center mb-4 w-100">
                            <h3 ID="" runat="server" class="homeloading">
                                <div class="lodingbusy"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div></h3>
                            <p class="text-center Noproduct_errormsg" style="display:none;">No products available.</p>
                        </div>
                        <div class="dash_btn_wrap d-flex justify-content-lg-center align-items-end w-100">
                          <a class="btn dash-btn addproductsbtn" href="/Tenant/MyProducts">Add products<i class="fa-regular fa-calendar-lines ml-2"></i></a>
                          <a class="btn btn-primary prodt_addnow" style="display:none" href="/Tenant/MyProducts">Add Now<i class="icon ion-plus-circled ml-2"></i></a>
                        </div>
                      </div>--%>



                    </div><!--card-->
                  </div><!--col-lg-6-->
                </div>
              </div><!--col-lg-6-->
                <div class="col-lg-6 mb-3 mb-lg-4">
                    <div class="card h-100">
                        <div class="card-body p-4 Dashboard_widgets">
                            <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Sales analytics</h5>
                            <div class="card-activities pt-3 p-0">
                                <canvas id="chartBar1" height="200"></canvas>
                            </div>
                            <%--<div class="d-flex flex-wrap justify-content-center my-3">
                      <img alt="nodata" style="opacity: 0.9; max-width: 150px;" src="images/ban-light.svg">
                      <h6 class="mb-3 w-100 text-center">No record available</h6>

                    </div>--%>
                        </div>
                    </div>
                    <!--card-->
                </div>
                <!--col-lg-6-->
            </div><!--row-->

            <div class="row row-sm">
              <div class="col-12">
                <h6 class="slim-pagetitle mt-2 mt-lg-0 mb-4 pt-2 pb-2">Latest orders</h6>
                <div class="card">
                  <div class="card-body">
                                  <div class="table-responsive">
                <table class="table mg-b-0 tx-13" id="tblpendingorders">
                  <thead>
                    <tr class="tx-10">
                      <th class="pd-y-5">Order ID</th>
                      <th class="pd-y-5">From</th>
                      <th class="pd-y-5">To</th>
                      <th class="pd-y-5" style="text-align: right;">Amount</th>
                    </tr>
                  </thead>
                  <tbody>

<asp:SqlDataSource ID="SqlDataSource1" runat="server" OnSelecting="SDSRecentOrders_Selecting" ConnectionString ="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT o.order_id, o.order_group_id, o.order_order_id, o.total, b.br_Name, d.order_city, TIMESTAMPDIFF
                (MINUTE, o.created_at, NOW()) AS diff,so.fsto_id,so.fsto_uid FROM retaline_customer_order o 
                INNER JOIN finascop_stock_transfer_order so ON so.fstr_id = o.order_id
                LEFT JOIN finascop_branch b ON o.order_branch_id=b.br_ID
                LEFT JOIN retaline_customer_order_delivery_address d ON o.order_order_id=d.order_id 
                WHERE o.status_id IN(4,5,6,7,8,9,10,11,12,13,14,15,16, 20, 22, 23, 27,28, 30, 31, 32, 33, 34) AND storegroup_id=@storegroup
                ORDER BY o.created_at DESC LIMIT 10" ProviderName="MySql.Data.MySqlClient">
    <SelectParameters>
        <asp:Parameter Name="storegroup" />
    </SelectParameters>
</asp:SqlDataSource>

                    <asp:Repeater ID="Repeater2" runat="server">
                        <ItemTemplate>

                    <tr>
                      <td><a class="tx-inverse tx-14 tx-medium d-block" href="<%# String.Format("/Tenant/OrderDetails.aspx?orderid={0}&toid={1}&ordId={2}", Eval("fsto_id"), Eval("fsto_uid"), Eval("order_id")) %>"><%# Eval("order_order_id") %></a>
                          <%# RetalineProAgent.Service.Common.MinutesToDiff(Convert.ToInt32(Eval("diff"))) %>
                      </td>
                      <td><%# Eval("br_Name") %></td>
                      <td><%# Eval("order_city") %></td>
                      <td style="text-align: right;">
                        <div class="sparkbar" data-color="#00a65a" data-height="20"><%# String.Format("{0}{1}", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("total")) %></div>
                      </td>
                        
                    </tr>

                            
                            

                        </ItemTemplate>
                        <FooterTemplate>
                            <tr><td colspan="4"><asp:Label ID="lblEmptyData" runat="server" Visible='<%# ((Repeater)Container.NamingContainer).Items.Count == 0 %>' Text="No recent orders" /></td></tr>
    
                            <%if (Repeater2.Items.Count <= 0)
                                {  %>

                            <tr><td><div class="text-center"><img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg"><h6 class="mb-3">No record available</h6></div></td></tr>

                            <% } %>
</FooterTemplate>
                        
                    </asp:Repeater>
                     
                      <%--<tr>
                                <td>
                                    <div class="text-center">
                                        <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                        <h6 class="mb-3">No record available</h6>
                                    </div>
                                </td>
                            </tr>--%>

                    
                  </tbody>
                </table>
              </div><!-- table-responsive -->

                  </div><!--card body-->
                  <div class="card-footer d-flex flex-wrap justify-content-lg-end">
                    <div class="d-sm-flex wiz_btnsect">
                      <a href="/Tenant/SaleAndReturnOrders" id="" class="btn btn-primary ">View All Orders</a>
                    </div>
                  </div>
                </div>
              </div><!--col-12-->
            </div><!--row-->

<asp:PlaceHolder runat="server" ID="plcThankyou" Visible="false">
    <div class="Welcome_msg_poup position-fixed">
        <i class="fa-thin fa-circle-xmark close_Welcome_msg position-absolute" onclick="closeWelcomemsg()"></i>
        <h5 class="mt-2 mb-2 tx-normal">Great to see you in Grozeo</h5>
        <p class="tx-13 mb-2">Please go through the features and make ready your store. We will open up your store for business as soon as you complete the Pending Tasks. Happy Selling!</p>
      </div>
</asp:PlaceHolder>
          </div>

    <script src="/content/lib/chart.js/js/Chart.js"></script>

<script type="text/javascript">
    home.url.getDashboard = '/api/home/DashboardValues';
    home.url.pendingOrders = '/api/home/PendingOrders';
    home.controls.orders = '<%= ltrNewOrders.ClientID %>';
    home.controls.products = '<%= ltrForSale.ClientID %>';
    home.controls.orderpickers = '<%= ltrOrderPickers.ClientID %>';
    home.controls.drivers = '<%= ltrDrivers.ClientID %>';
    home.controls.totalOrder = '<%= ltrTtlOrders.ClientID %>';
    home.controls.totalSale = '<%= ltrTtlSales.ClientID %>';
    home.controls.ttlCustomers = '<%= ltrTtlCustomers.ClientID %>';
    home.controls.pendingaction = '<%= ltrPendingActions.ClientID %>';
    home.controls.outofstock = '<%= ltrOutOfStock.ClientID %>';


    home.ALGateway.controls.delayedOrders = '<%= ltrNewOrders.ClientID %>';
    home.ALGateway.controls.totalOrders = '<%= ltrTtlOrders.ClientID %>';

    home.ALGateway.urls.ALGatewayController = '/api/home/DashboardValueUpdate';

    /*home.url.pendingActions = '/api/home/PendingActions';*/
    home.url.publicsite = '//<%= this.CurrentUser.PublicSiteUrl %>';
    /* Chart.js Charts */
    // Sales chart
    var salesChartCanvas = document.getElementById('chartBar1').getContext('2d')
    // $('#revenue-chart').get(0).getContext('2d');

    <asp:Literal ID="ltrChartScript" runat="server"></asp:Literal>
 
        var salesChartData = {
        labels: [],
        datasets: [
            {
                label: 'Monthly Sales',
                backgroundColor: '#27AAC8',
                data: []
            }
        ]
    }


    window.CurrentUser = {
        APIStoreId: '<%= this.CurrentUser.APIStoreId %>'
    };


    var salesChartOptions = {
        maintainAspectRatio: false,
        legend: {
            display: false,
            labels: {
                display: false
            }
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    fontSize: 10,
                    max: 80
                }
            }],
            xAxes: [{
                ticks: {
                    beginAtZero: true,
                    fontSize: 11
                }
            }]
        }
    }

    // This will get the first returned node in the jQuery collection.
    // eslint-disable-next-line no-unused-vars
    var salesChart = new Chart(salesChartCanvas, { // lgtm[js/unused-local-variable]
        type: 'bar',
        data: salesChartData,
        options: salesChartOptions
    })

 <%--   <% if (Page.User.Identity.IsAuthenticated)
          { %>
    $(document).ready(function () { 
        $('#modalStoresetup').modal('show');
    });

    <% } %>--%>

</script>
    <script>
        $(document).ready(
            function () {
                $('.liveurl').click(function () {
                    window.open($(this).data('url'), '_blank');
                })
            }
            
        )
        function closeWelcomemsg() {
    $('.close_Welcome_msg').closest('.Welcome_msg_poup').hide();
  }
    </script>
    <style>
        .card.card-status.bg-warning * {
            color: #FFF !important;
        }
        .dashboard_tab_wrap nav{
              background: #dddee3;
            }
            .dashboard_tab_wrap nav .nav-item{
              border-bottom: 0px;
              background-color: #ecf0f3!important;
              color: #2f3032;
              border-radius: 6px 6px 0px 0px!important;
            }
            .dashboard_tab_wrap nav .nav-item.active{
              background-color: #FFF!important;
            }
            .dashboard_tab_wrap .embed-responsive{
              height:calc(100% - 57px);
            }

            @media (max-width: 991px){
              .dashboard_tab_wrap .embed-responsive{
                min-height: 600px;
              }
            }

            @media (max-width: 567px) {
                .dashboard_tab_wrap nav .nav-item{
                  font-size: 11px;
                  padding: 5px !important;
                }
                .dashboard_tab_wrap nav .nav-item .icon {
                  font-size: 17px!important;
                }
                .dashboard_tab_wrap #nav-tab{
                  padding-left: 5px!important;
                  padding-right: 5px!important;
                }
              }
    </style>

</asp:Content>

 
