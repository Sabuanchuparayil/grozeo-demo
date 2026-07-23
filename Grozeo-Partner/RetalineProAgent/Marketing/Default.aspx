<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Marketing/MarketingMaster.master" CodeBehind="Default.aspx.cs" Inherits="RetalineProAgent.Marketing.Default" %>


<asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">

    <div class="Dashboard_widgets_wrap">
            <div class="row row-sm">
              <div class="col-sm-6 col-lg-3 mb-3 mb-lg-4">
                <div class="card h-100">
                  <div class="card-body pb-3 px-4 pt-4 Dashboard_widgets widgets_Pending_orders ">
                    <div class="d-flex align-items-center dash_title mb-4">
                        <i class="fa-thin fa-list-check mr-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                      <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Delayed orders</h5>
                    </div>
                    <div class="dash-content d-flex flex-md-nowrap align-items-center justify-content-center mb-3">
                      <h3 ID="ltrNewOrders" class="homeloading" runat="server">
                          <asp:Literal ID="ltrPendingOrder" runat="server"></asp:Literal>
                      </h3>
                        <span class="dsb_card_dataerrormsg novalue"></span>
                    </div>
                    <div class="dash_btn_wrap d-flex justify-content-lg-end">
                      <a class="btn dash-btn" href="#">Details<i class="fa-regular fa-calendar-lines ml-2"></i></a>
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
                      <a class="btn dash-btn" href="#">View Insight<i class="fa-regular fa-calendar-lines ml-2"></i></a>
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
                      <a class="float-lg-right btn dash-btn" href="">Details<i class="fa-regular fa-calendar-lines ml-2"></i></a>
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
                          <p class="text-center mb-2">Out of Stock</p>
                        </div>
                        <div class="dash_btn_wrap d-flex justify-content-lg-center align-items-end w-100">
                          <a class="btn dash-btn addproductsbtn" href="/Navigations/Products">Manage Products<i class="fa-regular fa-calendar-lines ml-2"></i></a>
                        </div>
                      </div>

                    </div><!--card-->
                  </div><!--col-lg-6-->
                </div>
              </div><!--col-lg-6-->
                <div class="col-lg-6 mb-3 mb-lg-4">
                    <div class="card h-100">
                        <div class="card-body p-4 Dashboard_widgets">
                            <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Pending & In-progress Orders</h5>
                            <div class="card-activities pt-3 p-0">                                
                                <table class="table mg-b-0 tx-13">
                                    <tr class="tx-10"><td class="pd-y-5" style="width: 80px;">Orders</td><td class="pd-y-5">Status</td></tr>
                                <asp:Repeater ID="RPPendingOrders" runat="server" DataSourceID="SDSPendingOrders">
                                    <ItemTemplate><tr><td><%# Eval("orders") %> </td><td><%# Eval("status") %></td></tr></ItemTemplate>                        
                                </asp:Repeater>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--card-->
                </div>
                <!--col-lg-6-->
            </div><!--row-->
<asp:SqlDataSource ID="SDSPendingOrders" runat="server" ConnectionString ="<%$ ConnectionStrings:mySqlConnection %>" 
    SelectCommand="SELECT o.num AS `orders`, CONCAT(s.admin_description, ' (status id: ', o.status_id, ')') AS `status` FROM(
SELECT status_id, COUNT(*) AS num FROM retaline_customer_order WHERE status_id IN (6, 7, 9, 14, 15, 17, 23, 51, 54) GROUP BY status_id
)o LEFT JOIN retaline_customer_order_status s ON o.status_id=s.status_id" ProviderName="MySql.Data.MySqlClient"></asp:SqlDataSource>

          </div>

    <script src="/content/lib/chart.js/js/Chart.js"></script>

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

