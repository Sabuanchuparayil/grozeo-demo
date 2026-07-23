<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Title="Live Vehicles" AutoEventWireup="true" CodeBehind="LiveVehicles.aspx.cs" Inherits="RetalineProAgent.LiveVehicles" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/OrderDelivery">Order Delivery</a></li>
    <li class="breadcrumb-item active" aria-current="page">Live Vehicles</li>--%>
    <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvLiveVehicles" runat="server" CssClass="table table-bordered gridview_table" 
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" 
                                    DataSourceID="ODSLiveVehicles">
                                    <Columns>
                                        <asp:TemplateField Visible="false">
                                            <ItemTemplate><asp:HiddenField ID="hidAPIId" runat="server" Value='<%# Eval("v_id") %>' /></ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:BoundField HeaderText="V. Reg. No." DataField="v_no" SortExpression="v_no" />
                                        <asp:BoundField HeaderText="D. Name" DataField="DriverName" SortExpression="DriverName" />
                                        <asp:BoundField HeaderText="V. Type" DataField="v_typename" SortExpression="v_typename" />
                                        <asp:BoundField HeaderText="Last Updation" DataField="LocationUpdateddatetime" SortExpression="LocationUpdateddatetime" />
                                        <asp:TemplateField HeaderText = "" ItemStyle-Width="100" >
                                            <ItemTemplate>
                                                <asp:Button runat="server" ID="btnAssign" OnClick="btnDeliveryBoyAssign_Click" vehicleId='<%# Eval("v_id") %>' CssClass="btn btn-primary float-right" Text="Assign" Width="70px" />&nbsp;
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                    </Columns>
                                    <EmptyDataTemplate>
                                        <div class="text-center">
                                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                            <h6 class="mb-3">No vehicle available. Please add drivers or verify that the registered vehicle/driver is logged in and available within the delivery radius.</h6>
                                        </div>
                                              </EmptyDataTemplate>
                                </asp:GridView>

                <asp:ObjectDataSource ID="ODSLiveVehicles" runat="server" TypeName="RetalineProAgent.Core.Services.Drivers.VehicleService" SelectMethod="LoadVehicleDetailsForBinding">
                    <SelectParameters>
                        <asp:QueryStringParameter Name="br_id" QueryStringField="brId" Type="Int32" />
                        <asp:QueryStringParameter Name="longitude" QueryStringField="long" Type="Double" />
                        <asp:QueryStringParameter Name="latitude" QueryStringField="lat" Type="Double" />
                        <asp:QueryStringParameter Name="userType" QueryStringField="UserType" Type="Int32" DefaultValue="0" />
                        <asp:QueryStringParameter Name="userId" QueryStringField="UserId" Type="Int32" DefaultValue="0" />
                    </SelectParameters>
                </asp:ObjectDataSource>
               </div><!-- table-responsive -->
        </div><!-- card-body -->
    </div><!-- card -->
          <div class="container-fluid" runat="server" visible="false">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header" runat="server" visible="false">
                  <div class="float-right">

                      <div class="card-tools">
                <div class="input-group input-group-sm">
                    &nbsp;<asp:TextBox ID="txtFindLiveVehicles" runat="server" CssClass="form-control" placeholder="Search" autocomplete="nofill"></asp:TextBox> 
                    <asp:LinkButton runat="server" CssClass="input-group-append">
                        <div class="btn btn-primary">
                          <i class="fa fa-search"></i>
                        </div>
                    </asp:LinkButton>
                    &nbsp;

                    
                </div>
                  
              </div> 
                </div>
                    </div>
                
               </div>
                </div>
            </div>
          </div>
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
                window.location.href = "/Tenant/MerchantDelivery";
            });
        });
    </script>
</asp:Content>
