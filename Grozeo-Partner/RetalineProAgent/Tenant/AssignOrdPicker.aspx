<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Assign Order Picker" AutoEventWireup="true" CodeBehind="AssignOrdPicker.aspx.cs" Inherits="RetalineProAgent.AssignOrdPicker" %>

<asp:Content ContentPlaceHolderID="head" runat="server">
    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/PendingOrders">Packing & Delivery</a></li>
    <li class="breadcrumb-item active" aria-current="page">Assign Order Picker</li>--%>
    <a href="/Tenant/ViewAndUpdate"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvOrderPicker" runat="server" CssClass="table table-bordered gridview_table" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvOrderPicker_DataBound" DataSourceID="SDSOrderPickers">
                                    <Columns>
                                        <asp:BoundField HeaderText="Name" DataField="name" SortExpression="name"/>
                                        <asp:BoundField HeaderText="Phone" DataField="phone" SortExpression="phone"/>
                                        <asp:BoundField HeaderText="Status" DataField="liveStatus" SortExpression="liveStatus"/>
                                        <asp:TemplateField>
                                            <ItemTemplate>
                                                <asp:Button runat="server" ID="btnAdd" Enabled='<%# (Convert.ToInt32(Eval("is_offline")) == 1 ? false : true) %>' orderpickerid='<%# Eval("id") %>'  branchid='<%# Eval("branch_id") %>' OnClick="btnAdd_Click" CssClass="btn btn-success float-right" Text="Assign"/>&nbsp;
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                    </Columns>
                                    <EmptyDataTemplate>
                                        <div class="text-center">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                            <h6 class="mb-3">No order picker available. Please add order picker for your store</h6>
                                        </div>
                                    </EmptyDataTemplate>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSOrderPickers" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT boy.id,boy.name, boy.has_open_orders,boy.phone,boy.is_offline,IF(boy.is_offline = 1,'Offline','Online') AS liveStatus, 
                    boy.branch_id FROM retaline_godown_boy boy INNER JOIN finascop_branch b ON b.br_ID=boy.branch_id
                    WHERE b.br_storeGroup = @storegroupid AND boy.status=1 OR branch_id = 
                    (SELECT order_branch_id FROM retaline_customer_order WHERE order_id = @orderid LIMIT 1) ORDER BY is_offline=1"
        OnSelecting="SDSOrderPickers_Selecting">
        <SelectParameters>
            <asp:QueryStringParameter QueryStringField="ordId" Name="orderid" />
            <asp:Parameter Name="storegroupid" />
        </SelectParameters>
    </asp:SqlDataSource>
               </div>
        </div><!-- card-body -->
        <div class="card-footer d-flex flex-wrap justify-content-lg-between">
                <div class="pagination-wrapper col-12 col-lg-6 p-0 pr-md-2 d-flex justify-content-center justify-content-lg-start">
                    <%--<ul class="pagination pagination-circle mg-b-0 mb-2 mb-lg-0">
                      <li class="page-item hidden-xs-down">
                        <a class="page-link" href="#" aria-label="First"><i class="fa fa-angle-double-left"></i></a>
                      </li>
                      <li class="page-item">
                        <a class="page-link" href="#" aria-label="Previous"><i class="fa fa-angle-left"></i></a>
                      </li>
                      <li class="page-item active"><a class="page-link" href="#">1</a></li>
                      <li class="page-item"><a class="page-link" href="#">2</a></li>
                      <li class="page-item hidden-xs-down"><a class="page-link" href="#">3</a></li>
                      <li class="page-item hidden-xs-down"><a class="page-link" href="#">4</a></li>
                      <li class="page-item disabled"><span class="page-link">...</span></li>
                      <li class="page-item"><a class="page-link" href="#">10</a></li>
                      <li class="page-item">
                        <a class="page-link" href="#" aria-label="Next"><i class="fa fa-angle-right"></i></a>
                      </li>
                      <li class="page-item hidden-xs-down">
                        <a class="page-link" href="#" aria-label="Last"><i class="fa fa-angle-double-right"></i></a>
                      </li>
                    </ul>--%>
                </div>
            <div class="col-12 col-lg-6 p-0 d-flex justify-content-center justify-content-lg-end align-items-end">
                    <asp:Button runat="server" ID="btnProceed" CssClass="btn btn-primary mr-2" Text="Replenish Manually" OnClick="btnReplenish_Click"/>
                    <a href="/Tenant/ViewAndUpdate<%=(String.IsNullOrEmpty(Request.QueryString["orderid"])? "" : String.Format("?orderid={0}", Request.QueryString["orderid"])) %>" class="btn btn-secondary">Cancel</a>
                </div>
            </div><!-- card-footer -->
    </div><!-- card -->
        

    <div id="modaldemo5" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <i class="icon icon ion-ios-close-outline tx-100 tx-danger lh-1 mg-t-20 d-inline-block"></i>
            <h4 class="tx-danger mg-b-20"><asp:Literal ID="ltrErrorPopupTitle" runat="server"></asp:Literal></h4>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrErrorPopupText" runat="server"></asp:Literal></p>
            <button type="button" class="btn btn-danger pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

    <!-- MODAL ALERT MESSAGE -->
    <div id="modaldemo4" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <i class="icon ion-ios-checkmark-outline tx-100 tx-success lh-1 mg-t-20 d-inline-block"></i>
            <h4 class="tx-success tx-semibold mg-b-20"><asp:Literal ID="ltrSuccessTitle" runat="server"></asp:Literal></h4>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrSuccessContent" runat="server"></asp:Literal></p>

            <button type="button" class="btn btn-success pd-x-25" data-dismiss="modal" aria-label="Close">Continue</button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div><!-- modal -->

   <script type="text/javascript">
       $(function () {

           // hide modal with effect
           $('#modaldemo4').on('hidden.bs.modal', function (e) {
               window.location.href = "/Tenant/PendingOrders";
           });
       });
   </script>
</asp:Content>
