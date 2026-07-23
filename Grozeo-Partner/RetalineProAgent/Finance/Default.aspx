<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" Title="Finance"  CodeBehind="Default.aspx.cs" Inherits="RetalineProAgent.Finance._Default" %>
<%@ Import Namespace="System.Configuration" %>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
<script src="/Content/js/custom/home.js"></script>
 

</asp:Content>
<asp:Content ContentPlaceHolderID="cpNhead" runat="server"></asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server">
            <h6 class="slim-pagetitle">Finance</h6>
    <p class="mb-0">Manage your Finance</p>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">

    <div class="Dashboard_widgets_wrap">
        <div class="row row-sm">
            <div class="col-lg-3 mb-3 mb-lg-4">
                <div class="card h-100">
                    <div class="card-body pb-3 px-4 pt-4 Dashboard_widgets">
                        <div class="d-flex align-items-center dash_title mb-4">
                            <i class="icon ion-ios-cart-outline mr-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                            <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Voucher</h5>
                        </div>
                        <div class="dash-content d-flex flex-md-nowrap align-items-center mb-3">
                          <h3 ID="ltmVoucher" class="homeloading" runat="server"></h3>
                        </div>
                        <div class="dash_btn_wrap d-flex justify-content-lg-end">
                          <a class="btn dash-btn" href="/Finance/DataEntry">Details<i class="fa-regular fa-calendar-lines ml-2"></i></a>
                        </div>
                    </div>
                </div>
            </div><!-- col-3 -->

            <div class="col-lg-3 mb-3 mb-lg-4">
                <div class="card h-100">
                    <div class="card-body pb-3 px-4 pt-4 Dashboard_widgets">
                        <div class="d-flex align-items-center dash_title mb-4">
                            <i class="icon ion-ios-pricetag-outline mr-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                            <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Transaction Logs</h5>
                        </div>
                        <div class="dash-content d-flex flex-md-nowrap align-items-center mb-3">
                          <h3 ID="lttransctionlog" class="homeloading" runat="server"></h3>
                        </div>
                        <div class="dash_btn_wrap d-flex justify-content-lg-end">
                          <a class="btn dash-btn" href="/Finance/PendingEntries">Details<i class="fa-regular fa-calendar-lines ml-2"></i></a>
                        </div>
                    </div>
                </div>
            </div><!-- col-3 -->

            <div class="col-lg-3 mb-3 mb-lg-4">
                <div class="card h-100">
                    <div class="card-body pb-3 px-4 pt-4 Dashboard_widgets">
                        <div class="d-flex align-items-center dash_title mb-4">
                            <i class="icon ion-ios-people-outline mr-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                            <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Groups</h5>
                        </div>
                        <div class="dash-content d-flex flex-md-nowrap align-items-center mb-3">
                          <h3 ID="ltmtrialbalance" class="homeloading" runat="server"></h3>
                        </div>
                        <div class="dash_btn_wrap d-flex justify-content-lg-end">
                          <a class="btn dash-btn" href="/Finance/GroupManagement">Details<i class="fa-regular fa-calendar-lines ml-2"></i></a>
                        </div>
                    </div>
                </div>
            </div><!-- col-3 -->

            <div class="col-lg-3 mb-3 mb-lg-4">
                <div class="card h-100">
                    <div class="card-body pb-3 px-4 pt-4 Dashboard_widgets">
                        <div class="d-flex align-items-center dash_title mb-4">
                            <i class="icon ion-ios-contact-outline mr-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                            <h5 class="card-title tx-medium mg-b-4 tx-14 m-0 tx-gray-600">Ledger</h5>
                        </div>
                        <div class="dash-content d-flex flex-md-nowrap align-items-center mb-3">
                          <h3 ID="ltmledger" class="homeloading" runat="server"></h3>
                        </div>
                        <div class="dash_btn_wrap d-flex justify-content-lg-end">
                          <a class="btn dash-btn" href="/Finance/Ledger">Details<i class="fa-regular fa-calendar-lines ml-2"></i></a>
                        </div>
                    </div>
                </div>
            </div><!-- col-3 -->
        </div>
    </div>               
        <!-- /.row -->
        <!-- Main row -->
        <div class="row row-sm">
          <!-- Left col -->
          <div class="col-12 connectedSortable">
            <!-- /.card -->
              <div id="accordion" class="accordion_cardsec accordion-one" role="tablist" aria-multiselectable="true">
            <div class="card">
              <div class="card-header" role="tab" id="headingOne">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne" class="tx-gray-800 transition">
                   Recent entries
                </a>
              </div><!-- card-header -->
              <div id="collapseOne" class="collapse show" role="tabpanel" aria-labelledby="headingOne">
                <div class="card-body p-0">
                 <div class="table-responsive">
                     <table class="table table-bordered mg-b-0">
                    <thead>
                    <tr>
                       <th>Created On</th>
                      <th>Voucher ID</th>
                      <th>Order Id</th>
                      
                      <th style="text-align: right;">Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                     
                    <asp:Repeater ID="Repeater1"  DataSourceID="SDSledger" runat="server">
                        <ItemTemplate>

                    <tr>
                      <td><%# Eval("createdOn") %> </td>
                      <td><%# Eval("voucherSlNoString")%>
                      </td>
                      <td><%# Eval("entity_id") %></td>
                      <td style="text-align: right;">
                        <div class="sparkbar" data-color="#00a65a" data-height="20"><%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><%# Eval("amount") %></div>
                      </td>
                    </tr>

                        </ItemTemplate>
                    </asp:Repeater>

                    </tbody>
                  </table>
                <asp:SqlDataSource runat="server" ID="SDSledger" 
        ConnectionString="<%$ ConnectionStrings:FinascopConnection %>"
         SelectCommand="select top 5  voucherSlNoString,[entity_id],createdOn,amount from  data_entry order by id desc ">       
    </asp:SqlDataSource>
                         </div>
                     
                </div>
                  <div class="card-footer clearfix">
                <%--<a href="javascript:void(0)" class="btn btn-sm btn-secondary float-right">View All Orders</a>--%>
                <a href="/Finance/Dataentry" class="btn btn-sm btn-primary float-lg-right">View All Entries</a>
              </div>
              </div>
            </div>
            <div class="card">
              <div class="card-header" role="tab" id="headingTwo">
                <a class="collapsed tx-gray-800 transition" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                   Ledger
                </a>
              </div>
              <div id="collapseTwo" class="collapse show" role="tabpanel" aria-labelledby="headingTwo">
                <div class="card-body p-0">
                     <div class="table-responsive p-0">
                <table class="table table-bordered mg-b-0">
                  <thead>
                  <tr>
                    <th>Date</th>
                    <th>Voucher Number</th>
                    <th style="text-align: center;">Perticulars</th>
                    <th>Credit</th>
                      <th>Debit</th>
                  </tr>
                  </thead>
                  <tbody>
              
       <asp:Repeater ID="Repeater4" runat="server" DataSourceID="SDSentry">
                        <ItemTemplate>
                     <tr>
                    <td>
                      <%--<img src="dist/img/default-150x150.png" alt="Product 1" class="img-circle img-size-32 mr-2">--%>
                      <%#Eval("createdOn") %>
                    </td>
                    <td><%#Eval("voucherSlNoString") %></td>
                    <td style="text-align: left;">
                      <%# Eval("particulars") %>
                    </td>
                    <td style="text-align: right;">
                       <%# (Eval("isDebtor").ToString() == "1" ? Eval("Dr","{0:n}") : "") %>
                    </td>
                          <td style="text-align: right;">
                        <%# (Eval("isDebtor").ToString() == "0" ? Eval("Cr","{0:n}") : "") %>

                    </td>
                  </tr>                    
                        </ItemTemplate>
                    </asp:Repeater>                  
                  </tbody>
                </table>
                   <asp:SqlDataSource ID="SDSentry" runat="server" 
                        SelectCommand="SET dateformat dmy;SELECT top 5  de.id,tr.ledger_id,[de].[createdOn],docSerialNo,de.voucherSlNoString, tr.particulars, isDebtor, (CASE WHEN isDebtor = 1 THEN [tr].[amount] ELSE 0  END) AS Dr, 
                        (CASE WHEN isDebtor = 0 THEN [tr].[amount] ELSE 0  END) AS Cr, de.narration from [data_entry] de
                        INNER JOIN [transactions] tr ON [de].id = [tr].data_entry_id  order by de.id desc"
                        ConnectionString="<%$ ConnectionStrings:FinascopConnection %>">                       
                   </asp:SqlDataSource>
              </div>
                </div>
              </div>
            </div>            
          </div> <!-- accordion -->
          </div>                           
        </div>
       


    

<script type="text/javascript">
    /* Chart.js Charts */
    // Sales chart
    var salesChartCanvas = document.getElementById('revenue-chart-canvas').getContext('2d')
    // $('#revenue-chart').get(0).getContext('2d');

    <asp:Literal ID="ltrChartScript" runat="server"></asp:Literal>

    

    var salesChartOptions = {
        maintainAspectRatio: false,
        responsive: true,
        legend: {
            display: false
        },
        scales: {
            xAxes: [{
                gridLines: {
                    display: false
                }
            }],
            yAxes: [{
                gridLines: {
                    display: false
                }
            }]
        }
    }

    // This will get the first returned node in the jQuery collection.
    // eslint-disable-next-line no-unused-vars
    var salesChart = new Chart(salesChartCanvas, { // lgtm[js/unused-local-variable]
        type: 'line',
        data: salesChartData,
        options: salesChartOptions
    })</script>

    <script src="/content/lib/chart.js/js/Chart.js"></script>

</asp:Content>

