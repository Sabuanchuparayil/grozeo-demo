<%@ Page Language="C#" AutoEventWireup="true" Title="Fleet Portal" MasterPageFile="~/Fleet/FleetMaster.master" CodeBehind="Default.aspx.cs" Inherits="RetalineProAgent.Fleet.Default" %>

<%@ Import Namespace="System.Configuration" %>

<asp:Content ContentPlaceHolderID="cpNhead" runat="server">
<%--<script src="/Content/js/custom/home.js"></script>--%>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server"></asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
            <h6 class="slim-pagetitle">Fleet Manage</h6>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">

    <div class="row row-xs">

          <div class="col-sm-6 col-lg">
            <div class="card card-status">
                <h6 class="slim-card-title">Pending Deliveries</h6>
              <div class="media">
                <i class="icon ion-ios-cart-outline tx-purple"></i>
                <div class="media-body">
                  <h1 ID="ltrNewOrders" class="homeloading" runat="server"><div class="lodingbusy"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div></h1>
                  <p><a href="#">View All</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
          <div class="col-sm-6 col-lg mg-t-10 mg-sm-t-0">
            <div class="card card-status">
                <h6 class="slim-card-title">Orders</h6>
              <div class="media">
                <i class="icon ion-ios-pricetag-outline tx-teal"></i>
                <div class="media-body">
                  <h1 ID="ltrForSale" runat="server" class="homeloading"><div class="lodingbusy"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div></h1>
                  <p><a href="#">View All</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
          <div class="col-sm-6 col-lg mg-t-10 mg-lg-t-0">
            <div class="card card-status">
                <h6 class="slim-card-title">Drivers</h6>
              <div class="media">
                <i class="icon ion-ios-people-outline tx-indigo"></i>
                <div class="media-body">
                  <h1 ID="ltrOrderPickers" runat="server" class="homeloading"><div class="lodingbusy"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div></h1>
                  <p><a href="#">View All</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
          <div class="col-sm-6 col-lg mg-t-10 mg-lg-t-0">
            <div class="card card-status">
                <h6 class="slim-card-title">Order Returns</h6>
              <div class="media">
                <i class="icon ion-ios-contact-outline tx-info"></i>
                <div class="media-body">
                  <h1 ID="ltrDrivers" runat="server" class="homeloading"><div class="lodingbusy"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div></h1>
                  <p><a href="#">View All</a></p>
                </div><!-- media-body -->
              </div><!-- media -->
            </div><!-- card -->
          </div><!-- col-3 -->
        </div><!-- row -->


<div class="row row-xs mg-t-10">
          <div class="col-lg-6">
            <div class="card card-table mg-b-10">
              <div class="card-header">
                <h6 class="slim-card-title">My Deliveries</h6>
              </div><!-- card-header -->
              <div class="table-responsive">
                <table class="table mg-b-0 tx-13" id="tblpendingorders">
                  <thead>
                    <tr class="tx-10">
                      <th class="pd-y-5">Date</th>
                      <th class="pd-y-5">Location</th>
                      <th class="pd-y-5">Store</th>
                      <th class="pd-y-5" style="text-align: right;">Status</th>
                    </tr>
                  </thead>
                  <tbody>

<%--<asp:SqlDataSource ID="SDSRecentOrders" runat="server" OnSelecting="SDSRecentOrders_Selecting"
                SelectCommand="SELECT o.order_id, o.order_group_id, o.order_order_id, o.total, b.br_Name, d.order_city, TIMESTAMPDIFF
(MINUTE, o.created_at, NOW()) AS diff,so.fsto_id,so.fsto_uid FROM retaline_customer_order o 
    INNER JOIN finascop_stock_transfer_order so ON so.fstr_id = o.order_id
LEFT JOIN finascop_branch b ON o.order_branch_id=b.br_ID
LEFT JOIN retaline_customer_order_delivery_address d ON o.order_order_id=d.order_id 
 WHERE o.status_id IN(4,5,6,7,8,9,10,11,12,13,14,15,16, 20, 22, 23, 27,28, 30, 31, 32, 33, 34) AND storegroup_id=@storegroup
  ORDER BY o.created_at DESC LIMIT 10
"
                
                ProviderName="MySql.Data.MySqlClient"
                >
    <SelectParameters>
        <asp:Parameter Name="storegroup" />
    </SelectParameters>

</asp:SqlDataSource>--%>

                    <asp:Repeater ID="rptOrders" runat="server">
                        <ItemTemplate>

                    <tr>
                      <td><a class="tx-inverse tx-14 tx-medium d-block" href="<%# String.Format("/OrderDetails.aspx?orderid={0}&toid={1}&ordId={2}", Eval("fsto_id"), Eval("fsto_uid"), Eval("order_id")) %>"><%# Eval("order_order_id") %></a>
                          &nbsp; <%# RetalineProAgent.Service.Common.MinutesToDiff(Convert.ToInt32(Eval("diff"))) %>
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
    
</FooterTemplate>
                    </asp:Repeater>


                    
                  </tbody>
                </table>
              </div><!-- table-responsive -->
              <div class="card-footer tx-12 pd-y-15 bg-transparent">
                <a href="#"><i class="fa fa-angle-down mg-r-5"></i>View All</a>
              </div><!-- card-footer -->
            </div><!-- card -->

            <div class="card card-table mg-b-10">
              <div class="card-header">
                <h6 class="slim-card-title">Spot Returns</h6>
              </div><!-- card-header -->
              <div class="table-responsive">
                <table class="table mg-b-0 tx-13">
                  <thead>
                    <tr class="tx-10">
                      <th>Order</th>
                      <th class="pd-y-5" style="text-align: center;">Date</th>
                    </tr>
                  </thead>
                  <tbody>
           <%-- <asp:SqlDataSource ID="SDSProduct" runat="server" OnSelecting="SDSRecentOrders_Selecting"
                SelectCommand="SELECT i.stit_ID, stit_itemId, stit_SKU, (SELECT COUNT(*) FROM finascop_stock_branch_inventory bi
inner join finascop_branch b on b.br_ID=bi.branch_id where b.br_storeGroup=@storegroup AND stit_id=i.stit_ID) AS cnt, 
(SELECT IFNULL(SUM(item_sales_price), 0) FROM retaline_customer_order_items oi INNER JOIN finascop_branch b ON oi.order_branch_id=b.br_ID WHERE b.br_storeGroup=@storegroup AND  item_product_id =i.stit_ID) AS total
FROM finascop_stock_itemmaster i where i.stit_ID in (Select stit_id from finascop_stock_branch_inventory bi
inner join finascop_branch b on b.br_ID=bi.branch_id where b.br_storeGroup=@storegroup) ORDER BY stit_ID DESC LIMIT 10
"               
                ProviderName="MySql.Data.MySqlClient">
                <SelectParameters>
                    <asp:FormParameter Name="storegroup" />
                </SelectParameters>
            </asp:SqlDataSource>--%>
<asp:Repeater ID="Repeater1" runat="server">
                        <ItemTemplate>
<tr>
                    <td>
                      <%# Eval("stit_SKU") %>
                        <%# ( Convert.ToInt32(Eval("cnt")) > 1 ? String.Format("&nbsp; <small>{0} branches</small>", Eval("cnt")) : "" ) %>
                    </td>
                    <td class="valign-middle tx-right">
                      <%# String.Format("{0}{1:0.00}", ConfigurationManager.AppSettings.Get("CurrencySymbol"), Eval("total")) %>
                    </td>
                  </tr>
                    

                        </ItemTemplate>
    <FooterTemplate>
                            <tr><td colspan="2"><asp:Label ID="Label1" runat="server" Visible='<%# ((Repeater)Container.NamingContainer).Items.Count == 0 %>' Text="No product available for sale" /></td></tr>
    
</FooterTemplate>
                    </asp:Repeater>

                  </tbody>
                </table>
              </div><!-- table-responsive -->
              <div class="card-footer tx-12 pd-y-15 bg-transparent">
                <a href="#"><i class="fa fa-angle-down mg-r-5"></i>View All</a>
              </div><!-- card-footer -->
            </div><!-- card -->


               


          </div><!-- col-6 -->
          <div class="col-lg-6 mg-t-10 mg-lg-t-0">
          

            <%--<div class="card h-100">
                <div class="dashboard_tab_wrap h-100">
                    <nav>

                      <div class="nav nav-tabs px-2 pt-2" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link lh-normal d-flex align-items-center p-2 ml-1 mr-1 rounded active" id="Website-Preview-tab" data-toggle="tab" href="#Website-Preview" role="tab" aria-controls="Website-Preview" aria-selected="true"><i class="icon ion-ios-world tx-26 mr-2 lh-normal"></i>Website Preview</a>
<a class="nav-item nav-link lh-normal d-flex align-items-center p-2 rounded ml-1 mr-1" id="Dashboard-Widgets-tab" data-toggle="tab-" href="#Dashboard-Widgets" role="tab" aria-controls="Dashboard-Widgets" aria-selected="false"><i class="icon ion-ios-speedometer tx-26 lh-normal mr-2"></i>Dashboard Widgets</a>
                      </div>

                    </nav>
                    <div class="tab-content h-100" id="nav-tabContent">
                      <div class="tab-pane h-100 fade show active" id="Website-Preview" role="tabpanel" aria-labelledby="Website-Preview-tab">
                        <div class="embed-responsive embed-responsive-16by9">
                          <iframe class="embed-responsive-item" id="ifpublicsite" allowfullscreen></iframe>
                        </div>
                      </div>
                      <div class="tab-pane fade" id="Dashboard-Widgets" role="tabpanel" aria-labelledby="Dashboard-Widgets-tab">
                        Dashboard-Widgets
                      </div>
                    </div>
                  </div>
            </div>--%>


<div class="card card-table">
               <div class="card-header">
                <h6 class="slim-card-title">Monthly Deliveries</h6>
              </div>
              <div class="card-activities pd-20">
  
              <canvas id="chartBar1" height="280"></canvas>
           </div></div>

 <%--<div class="card card-dash-headline">
              <h4>Download apps</h4>
              <p>Every order picker and driver should install the respective app in their mobile device to get polled on the auto scheduler</p>


<div class="card card-dash-one mg-t-20">
          <div class="row no-gutters">
            <div class="col-lg-6">
              <a href="<%= ConfigurationManager.AppSettings.Get("PacksureLinkAndroid") %>" target="_blank"><i class="icon ion-ios-cloud-download-outline tx-purple"></i></a>
              <div class="dash-content">
                <label class="tx-success">Packing App</label>
                  <span class="info-box-number"><a class="btn-outline-secondary btn-xs" href="javascript:void(0)" onclick="prompt('Press Ctrl + C, then Enter to copy to clipboard', '<%= ConfigurationManager.AppSettings.Get("PacksureLinkAndroid") %>')">
                  <i class="fa fa-share-alt"></i></a>
                    
&nbsp;&nbsp;<a target="_blank" class="btn-outline-secondary btn-xs" href="<%= String.Format("{0}{1}", (HttpContext.Current.Request.Browser.IsMobileDevice?"whatsapp://send?text=":"https://web.whatsapp.com/send?l=en&amp;text="), ConfigurationManager.AppSettings.Get("PacksureLinkAndroid")) %>"  data-action="share/whatsapp/share">
                  <i class="ion-social-whatsapp"></i></a>
&nbsp;&nbsp;<a class="btn-outline-secondary btn-xs" href="mailto:?subject=I wanted you to install this packing app in your mobile&amp;body=<%= ConfigurationManager.AppSettings.Get("PacksureLinkAndroid") %>">
                  <i class="fa fa-envelope"></i></a>
    
</span>

              </div><!-- dash-content -->
            </div><!-- col-3 -->
            <div class="col-lg-6">
              <a href="<%= ConfigurationManager.AppSettings.Get("DriveLinkAndroid") %>" target="_blank"><i class="icon ion-ios-cloud-download-outline tx-purple"></i></a>
              <div class="dash-content">
                <label class="tx-success">Delivery App</label>
                  <span class="info-box-number"><a class="btn-outline-secondary btn-xs" href="javascript:void(0)" onclick="prompt('Press Ctrl + C, then Enter to copy to clipboard', '<%= ConfigurationManager.AppSettings.Get("DriveLinkAndroid") %>')">
                  <i class="fa fa-share-alt"></i></a>
&nbsp;&nbsp;<a target="_blank" class="btn-outline-secondary btn-xs" href="<%= String.Format("{0}{1}", (HttpContext.Current.Request.Browser.IsMobileDevice?"whatsapp://send?text=":"https://web.whatsapp.com/send?l=en&amp;text="), ConfigurationManager.AppSettings.Get("DriveLinkAndroid")) %>"  data-action="share/whatsapp/share">
                  <i class="ion-social-whatsapp"></i></a>
&nbsp;&nbsp;<a class="btn-outline-secondary btn-xs" href="mailto:?subject=I wanted you to install this packing app in your mobile&amp;body=Click the link to install the app <%= ConfigurationManager.AppSettings.Get("DriveLinkAndroid") %>">
                  <i class="fa fa-envelope"></i></a>    
</span>              </div><!-- dash-content -->
            </div><!-- col-3 -->
            <!-- col-3 -->
            <!-- col-3 -->
          </div><!-- row -->
        </div>







            </div>--%>

          </div><!-- col-6 -->




        </div><!-- row -->


    <script src="/content/lib/chart.js/js/Chart.js"></script>

<%--<script type="text/javascript">
    home.url.getDashboard = '/api/home/DashboardValues';
    home.url.pendingOrders = '/api/home/PendingOrders';
    home.controls.orders = '<%= ltrNewOrders.ClientID %>';
    home.controls.products = '<%= ltrForSale.ClientID %>';
    home.controls.orderpickers = '<%= ltrOrderPickers.ClientID %>';
    home.controls.drivers = '<%= ltrDrivers.ClientID %>';

    home.url.pendingActions = '/api/home/0';
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

</script>--%>

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
