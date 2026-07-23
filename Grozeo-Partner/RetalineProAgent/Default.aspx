<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/AgentMaster.Master" Title="Partner" CodeBehind="Default.aspx.cs" Inherits="RetalineProAgent._Default" %>
<%@ Import Namespace="System.Configuration" %>

<asp:Content ContentPlaceHolderID="head" runat="server">
<script src="/Content/js/custom/home.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server"></asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
            <h6 class="slim-pagetitle"><%= this.CurrentUser.StoreGroupName %></h6>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">

    <div class="row row-xs">

          <div class="col-sm-6 col-lg" id="dvPendingActionsCount" style="display: none;">
            <div class="card card-status">
                <h6 class="slim-card-title tx-pink">Pending Actions</h6>
              <div class="media">
                <i class="icon ion-ios-analytics-outline tx-pink"></i>
                <div class="media-body">
                  <h1 ID="H1" class="homeloading" runat="server">0</h1>
                  <p><a href="/PendingActions">View</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->


          <div class="col-sm-6 col-lg">
            <div class="card card-status">
                <h6 class="slim-card-title">Pending Orders</h6>
              <div class="media">
                <i class="icon ion-ios-cart-outline tx-purple"></i>
                <div class="media-body">
                  <h1 ID="ltrNewOrders" class="homeloading" runat="server">0</h1>
                  <p><a href="/SaleAndReturnOrders">View Orders</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
          <div class="col-sm-6 col-lg mg-t-10 mg-sm-t-0">
            <div class="card card-status">
                <h6 class="slim-card-title">Products for Sale</h6>
              <div class="media">
                <i class="icon ion-ios-pricetag-outline tx-teal"></i>
                <div class="media-body">
                  <h1 ID="ltrForSale" runat="server" class="homeloading">0</h1>
                  <p><a href="/ItemsForSale">Add Products</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
          <div class="col-sm-6 col-lg mg-t-10 mg-lg-t-0">
            <div class="card card-status">
                <h6 class="slim-card-title">Order Pickers Online</h6>
              <div class="media">
                <i class="icon ion-ios-people-outline tx-indigo"></i>
                <div class="media-body">
                  <h1 ID="ltrOrderPickers" runat="server" class="homeloading">0</h1>
                  <p><a href="/OrderPicker">View</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
          <div class="col-sm-6 col-lg mg-t-10 mg-lg-t-0">
            <div class="card card-status">
                <h6 class="slim-card-title">Drivers available</h6>
              <div class="media">
                <i class="icon ion-ios-contact-outline tx-info"></i>
                <div class="media-body">
                  <h1 ID="ltrDrivers" runat="server" class="homeloading">0</h1>
                  <p><a href="/DeliveryBoys">View</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
        </div><!-- row --><br />

    <h6 class="slim-pagetitle">Business Associate</h6><br />
    <div class="row row-xs">
        <div class="col-sm-6 col-lg">
            <div class="card card-status">
                <h6 class="slim-card-title">Area Leads</h6>
              <div class="media">
                  <i class="fa icon fa-line-chart tx-purple" aria-hidden="true"></i>
                <div class="media-body">
                  <h1 ID="H2" class="homeloading" runat="server">0</h1>
                  <p><a href="#">View</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
          <div class="col-sm-6 col-lg mg-t-10 mg-sm-t-0">
            <div class="card card-status">
                <h6 class="slim-card-title">Non Area Lead</h6>
              <div class="media">
                <i class="icon ion-ios-pricetag-outline tx-teal"></i>
                <div class="media-body">
                  <h1 ID="H3" runat="server" class="homeloading">0</h1>
                  <p><a href="#">View</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
          <div class="col-sm-6 col-lg mg-t-10 mg-lg-t-0">
            <div class="card card-status">
                <h6 class="slim-card-title">Retailers</h6>
              <div class="media">
                <i class="icon ion-ios-people-outline tx-indigo"></i>
                <div class="media-body">
                  <h1 ID="H4" runat="server" class="homeloading">0</h1>
                  <p><a href="#">View</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
          <div class="col-sm-6 col-lg mg-t-10 mg-lg-t-0">
            <div class="card card-status">
                <h6 class="slim-card-title">Merchants</h6>
              <div class="media">
                <i class="icon ion-ios-contact-outline tx-info"></i>
                <div class="media-body">
                  <h1 ID="H5" runat="server" class="homeloading">0</h1>
                  <p><a href="#">View</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
        </div><!-- row --><br />

    <h6 class="slim-pagetitle">Fleet Managers</h6><br />
    <div class="row row-xs">
        <div class="col-sm-6 col-lg">
            <div class="card card-status">
                <h6 class="slim-card-title">Available Drivers</h6>
              <div class="media">
                <i class="icon ion-ios-cart-outline tx-purple"></i>
                <div class="media-body">
                  <h1 ID="H6" class="homeloading" runat="server">0</h1>
                  <p><a href="#">View</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
          <div class="col-sm-6 col-lg mg-t-10 mg-sm-t-0">
            <div class="card card-status">
                <h6 class="slim-card-title">Online Drivers</h6>
              <div class="media">
                <i class="icon ion-ios-pricetag-outline tx-teal"></i>
                <div class="media-body">
                  <h1 ID="H7" runat="server" class="homeloading">0</h1>
                  <p><a href="#">View</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
          <div class="col-sm-6 col-lg mg-t-10 mg-lg-t-0">
            <div class="card card-status">
                <h6 class="slim-card-title">Offline Drivers</h6>
              <div class="media">
                <i class="icon ion-ios-people-outline tx-indigo"></i>
                <div class="media-body">
                  <h1 ID="H8" runat="server" class="homeloading">0</h1>
                  <p><a href="#">View</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
        </div><!-- row --><br />

    <h6 class="slim-pagetitle">Finance</h6><br />
    <div class="row row-xs">
        <div class="col-sm-6 col-lg">
            <div class="card card-status">
                <h6 class="slim-card-title">Sales Report</h6>
              <div class="media">
                <i class="icon ion-ios-cart-outline tx-purple"></i>
                <div class="media-body">
                  <h1 ID="H9" class="homeloading" runat="server">0</h1>
                  <p><a href="#">View</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
          <div class="col-sm-6 col-lg mg-t-10 mg-sm-t-0">
            <div class="card card-status">
                <h6 class="slim-card-title">Delivery Report</h6>
              <div class="media">
                <i class="icon ion-ios-pricetag-outline tx-teal"></i>
                <div class="media-body">
                  <h1 ID="H10" runat="server" class="homeloading">0</h1>
                  <p><a href="#">View</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
          <%--<div class="col-sm-6 col-lg mg-t-10 mg-lg-t-0">
            <div class="card card-status">
                <h6 class="slim-card-title">Performance Report</h6>
              <div class="media">
                <i class="icon ion-ios-people-outline tx-indigo"></i>
                <div class="media-body">
                  <h1 ID="H11" runat="server" class="homeloading">0</h1>
                  <p><a href="#">View</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->--%>
          <div class="col-sm-6 col-lg mg-t-10 mg-lg-t-0">
            <div class="card card-status">
                <h6 class="slim-card-title">Tax Report</h6>
              <div class="media">
                <i class="icon ion-ios-contact-outline tx-info"></i>
                <div class="media-body">
                  <h1 ID="H12" runat="server" class="homeloading">0</h1>
                  <p><a href="#">View</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
        <div class="col-sm-6 col-lg mg-t-10 mg-lg-t-0">
            <div class="card card-status">
                <h6 class="slim-card-title">Transactions</h6>
              <div class="media">
                <i class="icon ion-ios-contact-outline tx-info"></i>
                <div class="media-body">
                  <h1 ID="H13" runat="server" class="homeloading">0</h1>
                  <p><a href="#">View</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
       <!-- col-3 -->
        </div><!-- row --><br />
    


    <script src="/content/lib/chart.js/js/Chart.js"></script>

<script type="text/javascript">
    home.url.getDashboard = '/api/home/DashboardValues';
    home.url.pendingOrders = '/api/home/PendingOrders';
    home.controls.orders = '<%= ltrNewOrders.ClientID %>';
    home.controls.products = '<%= ltrForSale.ClientID %>';
    home.controls.orderpickers = '<%= ltrOrderPickers.ClientID %>';
    home.controls.drivers = '<%= ltrDrivers.ClientID %>';

    home.url.pendingActions = '/api/home/0';

    /* Chart.js Charts */
    // Sales chart
    var salesChartCanvas = document.getElementById('chartBar1').getContext('2d')
    // $('#revenue-chart').get(0).getContext('2d');

    <asp:Literal ID="ltrChartScript" runat="server"></asp:Literal>

    

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

    <% if (Page.User.Identity.IsAuthenticated && !this.CurrentUser.HasVerifiedEmail)
          { %>
    $(document).ready(function () { 
        $('#modalverifyemail').modal('show');
    });

    <% } %>

</script>
    <style>
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
    </style>

</asp:Content>



 <%--<asp:Content ID="BodyContent" ContentPlaceHolderID="MainContent" runat="server"><br />
    
    <div class="row">
                       
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-building-o fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">1</div>
                                    <div>Stores!</div>
                                </div>
                            </div>
                        </div>
                        <a href="#">
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-green">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-tasks fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">0</div>
                                    <div>Inventories!</div>
                                </div>
                            </div>
                        </div>
                        <a href="/admin/Banner.aspx">
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-yellow">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-shopping-cart fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">0</div>
                                    <div>Margine!</div>
                                </div>
                            </div>
                        </div>
                        <a href="#">
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-red">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-support fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">0</div>
                                    <div>Price Update!</div>
                                </div>
                            </div>
                        </div>
                        <a href="#">
                            <div class="panel-footer">
                                <span class="pull-left">View Details</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
            
		

					</div>

</asp:Content>--%>
