<%@ Control Language="C#" AutoEventWireup="true" CodeBehind="CtrlViewOrders.ascx.cs" Inherits="RetalineProAgent.Controls.Orders.CtrlViewOrders" %>
<div id="modalvieworder" class="modal fade">
    <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
        <div class="modal-content bd-0 tx-14">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="card">
                <div class="card-header shadow_top">
                    <div class="d-flex flex-wrap flex-lg-nowrap align-items-center justify-content-between">
                        <asp:PlaceHolder ID="plcHead" runat="server">
                            <div class="ordr-info d-flex align-items-center flex-wrap flex-md-nowrap">
                                <div class="col-12 col-md-auto p-0 pr-md-4 d-inline-block tx-15 manl_pk_orId lh-normal">
                                    <strong>Order Id:</strong>
                                    <strong class="tx-dark"><asp:Literal ID="ltrOrderId"  runat="server" Text=""></asp:Literal></strong>                            
                                </div>
                                <div class="col-12 col-md-auto p-0 tx-15 d-flex align-items-center">
                                    <i class="icon ion-android-person mr-2 tx-22" style="position: relative; top: -3px;"></i>
                                    <strong class="tx-dark"><asp:Literal ID="ltrName" runat="server" Text=""></asp:Literal></strong>                                   
                                </div>
                                <div class="col-12 col-md-auto p-0 d-flex align-items-center pl-0 pl-md-3">
                                    <i class="icon ion-ios-telephone mr-2 tx-22" style="position: relative; top: -2px;"></i>
                                    <strong class="tx-dark"><asp:Literal ID="ltrMobile" runat="server" Text=""></asp:Literal></strong>                                   
                                </div>
                            </div>
                        </asp:PlaceHolder>
                        <div>
                            <asp:LinkButton CssClass="btn btn-outline-primary" Visible="false" runat="server" ID="btnprint"><i class="fa-light fa-print mr-2"></i>Print</asp:LinkButton>
                        </div>
                    </div>
                </div>
                <!-- card-header -->
                <div class="card-body p-3 pt-0">
                    <div class="table-responsive">
                        <asp:GridView AutoGenerateColumns="false" ID="gvManualPacking" OnDataBound="gvManualPacking_DataBound" runat="server" CssClass="table table-bordered gridview_table" GridLines="None"
                            AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" DataSourceID="SDSManualPacking">
                            <Columns>
                                <asp:TemplateField HeaderText="Sl No" ItemStyle-Width="80">
                                    <ItemTemplate>
                                        <asp:Label ID="lblRowNumber" Text='<%# Container.DataItemIndex + 1 %>' runat="server" />
                                    </ItemTemplate>
                                </asp:TemplateField>
                                <asp:BoundField HeaderText="Item" DataField="item_name" SortExpression="item_name" />
                                <asp:TemplateField>
                                    <HeaderTemplate>
                                        <%# (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "MRP" : "RRP") %>
                                    </HeaderTemplate>
                                    <ItemTemplate>
                                        <itemtemplate>
                                            <asp:Label ID="lblMRP" runat="server" Text='<%# Eval("mrp", "{0:0.00}") %>' CssClass="text-right"></asp:Label>
                                        </itemtemplate>
                                        <itemstyle width="120" horizontalalign="Right" />
                                    </ItemTemplate>
                                </asp:TemplateField>
                                <asp:BoundField HeaderText="Order Qty" DataField="fsto_ItemQty" ItemStyle-Width="120" SortExpression="fsto_ItemQty" DataFormatString="{0:0.00}" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" />
                                <asp:TemplateField HeaderText="Packed Qty" Visible="false" ItemStyle-Width="100" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" HeaderStyle-HorizontalAlign="Left">
                                    <ItemTemplate>
                                        <input type="text" style="display: none" />
                                        <input type="password" style="display: none" />
                                        <asp:TextBox runat="server" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" MaxLength="3" orderqty='<%# Eval("fsto_ItemQty") %>' ID="txtUpdate" Enabled="false" onfocus="this.select()" Width="120px" Text='<%#Eval("fsto_pkdQty")%>' CssClass="form-control packedqty text-right" prodid='<%# Eval("fstod_id") %>' autocomplete="off"></asp:TextBox>
                                    </ItemTemplate>
                                </asp:TemplateField>
                            </Columns>
                            <EmptyDataTemplate>
                                <div class="text-center">
                                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                    <h6 class="mb-3">No order record available.</h6>
                                </div>
                            </EmptyDataTemplate>
                        </asp:GridView>
                        <asp:HiddenField runat="server" ID="hdnIncompleteorder" />
                        <asp:HiddenField runat="server" ID="hdnorderid" />
                        <asp:HiddenField runat="server" ID="hdnfstoid" />
                        <asp:HiddenField runat="server" ID="hdnordermethod" />
                        <asp:HiddenField runat="server" ID="hdnorderstatus" />
                        <asp:HiddenField runat="server" ID="hdnorder_id" />
                        <asp:HiddenField runat="server" ID="hdnFstoUid" />
                        <asp:SqlDataSource runat="server" ID="SDSManualPacking" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT fo.fsto_uid AS fsto_uid,fo.fsto_isalreadypacked,fo.fsto_id AS fsto_id,fd.fsto_ItemId,fd.fstod_id,fsto_createdOn,fsto_destination,fsto_destination,fsto_source,
                                    (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_source) AS fsto_source,
                                    (SELECT br_parentPacking FROM finascop_branch WHERE br_ID = fsto_source) AS br_parentPacking,
                                    (SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = fsto_ItemId) AS item_name,
                                    (SELECT stit_ParentItemId FROM finascop_stock_itemmaster WHERE stit_ID = fsto_ItemId) AS stit_ParentItemId,
                                    fsto_ItemQty,fsto_pkdQty, (SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'IS_INVOICE') as ownInvoice,
                                    (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) AS branch,
                                    fo.fsto_id AS fsto_id,fstro_ItemMRP AS mrp
                                    FROM finascop_stock_transfer_order fo INNER JOIN finascop_stock_transfer_order_details fd ON fo.fsto_id = fd.fsto_id  
                                    AND fo.fsto_id=@fstoid">
                            <SelectParameters>
                                <asp:ControlParameter ControlID="hdnfstoid" Name="fstoid" DefaultValue="0" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                    </div>


                </div>
                <!-- card-body -->
                <div class="card-footer d-flex flex-wrap justify-content-center">
                    <asp:PlaceHolder runat="server" Visible="true" ID="plcorderaccept">
                    <asp:Button runat="server" Text="Accept Order" ID="btnaccept" OnClick="btnaccept_Click" CssClass="btn btn-primary mr-2"></asp:Button>
                    <asp:LinkButton runat="server" ID="btnreject" OnClientClick="showOrderreject(); return false;" Text="Out of Stock" CssClass="btn btn-danger"></asp:LinkButton>
                    </asp:PlaceHolder> 
                    <asp:PlaceHolder runat="server" Visible="false" ID="plcincompleteorder">
                     <asp:Button runat="server" Text="Proceed" ID="btnproceed" OnClick="btnproceed_Click" CssClass="btn btn-primary mr-2"></asp:Button>
                    <asp:LinkButton runat="server" ID="btnclose"  Text="Close" CssClass="btn btn-danger"></asp:LinkButton>
                    </asp:PlaceHolder>
                    <!--row-->
                </div>
                <!-- card-footer -->
            </div>
        </div>
    </div>
</div>
<div id="modalorderaccept" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm w-100">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">                
              <span aria-hidden="true">&times;</span>
            </button>
              <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold p-1">Order Accepted</h6>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="ltrSuccessContent" Text="Do you prefer to process the order now?" runat="server"></asp:Literal></p>
            <a href="/Tenant/PendingOrders" class="btn btn-outline-primary">Do It Later</a>
             <asp:Button runat="server" ID="btnOrderAccept"  OnClientClick="showOrderProcessModal(); return false;" CssClass="btn btn-primary" Text="Yes"></asp:Button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div>
<div id="modalorderProcess" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">                
              <span aria-hidden="true">&times;</span>
            </button>
              <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold p-1">Great!</h6>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="Literal1" Text="Please select an option below to pack the order now." runat="server"></asp:Literal></p>
             <asp:Button runat="server" ID="Btnordereassign" CssClass="btn btn-primary btnAssignOrderPicker" OnClick="Btnordereassign_Click" Text="Assign Order picker" ></asp:Button>
             <asp:Button runat="server" ID="Btnorderpacknow" OnClick="Btnorderpacknow_Click" CssClass="btn btn-primary" Text="Pack it now"></asp:Button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div>
<div id="modalorderreject" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content tx-size-sm">
          <div class="modal-body tx-center pd-y-20 pd-x-20">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">                
              <span aria-hidden="true">&times;</span>
            </button>
              <h6 class="tx-14 mg-b-0 tx-uppercase tx-inverse tx-bold p-1">Items Unavailable</h6>
            <p class="mg-b-20 mg-x-20"><asp:Literal ID="Literal2" Text="Select whether all items or some items are unavailable" runat="server"></asp:Literal></p>
             <asp:Button runat="server" ID="btnfewitemavaliabe"  OnClick="btnfewitemavaliabe_Click"  CssClass="btn btn-primary" Text="Few Items Available"></asp:Button>
             <asp:Button runat="server" ID="btnNoitmeavalible" OnClientClick="javascript:return confirm('Do you want to cancel this order?');" OnClick="btnNoitmeavalible_Click"  CssClass="btn btn-secondary" Text="No Items Available "></asp:Button>
          </div><!-- modal-body -->
        </div><!-- modal-content -->
      </div><!-- modal-dialog -->
    </div>
<div class="modal" id="PopupManualpackingDetalies">
        <div class="modal-dialog">
            <div class="modal-content">               
                <div class="modal-body">                 
                    <div class="row row-sm">
                        <div class="col-12 mt-2">
                            <div class="packet_number my-2 text-center tx-dark">
                                <asp:Label runat="server" Text="Customer Name"></asp:Label> :
                                <asp:Label CssClass="tx-semibold" runat="server"  ID="ltrcustomername"></asp:Label>
                            </div>
                            <div class="packet_number my-2 text-center tx-dark">
                                <asp:Label runat="server" Text="Customer Contact"></asp:Label> :
                                <asp:Label runat="server" CssClass="tx-semibold"  ID="ltrcustomerContact"></asp:Label>
                            </div>
                            <div class="packet_number my-2 text-center tx-dark">
                              <asp:Label runat="server" Text="Customer Approved for proceeding with the available items?" CssClass="tx-bold-"></asp:Label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <asp:LinkButton CssClass="btn btn-primary px-3 objdiv" OnClick="btnyes_Click" runat="server" ID="btnyes">Yes</asp:LinkButton>
                    <asp:LinkButton runat="server" CssClass="btn btn-primary btn-outline-primary px-3 objdiv" ID="btnNo" OnClientClick="javascript:return confirm('Do you want to cancel this order?');" OnClick="btnNo_Click" Text="No"></asp:LinkButton>
                    <a href="/Tenant/PendingOrders" class="btn btn-outline-secondary px-3">Close</a>
                </div>
            </div>
        </div>
    </div>
<div id="modalrfewitemavaliable" class="modal fade">
    <div class="modal-dialog modal-dialog-vertical-center w-100" role="document">
        <div class="modal-content bd-0 tx-14">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="card">
                <div class="card-header shadow_top">
                    <div class="d-flex flex-wrap flex-lg-nowrap align-items-center justify-content-between">
                        <asp:PlaceHolder ID="PlaceHolder1" runat="server">
                            <div class="ordr-info d-flex align-items-center flex-wrap flex-md-nowrap">
                                <div class="col-12 col-md-auto p-0 pr-md-4 d-inline-block tx-15 manl_pk_orId lh-normal">
                                    <strong>Order Id:</strong>                                    
                                    <strong class="tx-dark">
                                        <asp:Literal ID="ltrfewOrderId" runat="server" Text=""></asp:Literal></strong>
                                </div>
                                <div class="col-12 col-md-auto p-0 tx-15 d-flex align-items-center">
                                    <i class="icon ion-android-person mr-2 tx-22" style="position: relative; top: -3px;"></i>
                                    <strong class="tx-dark">
                                        <asp:Literal ID="ltrfewitenName" runat="server" Text=""></asp:Literal></strong>
                                </div>
                                <div class="col-12 col-md-auto p-0 d-flex align-items-center pl-0 pl-md-3">
                                    <i class="icon ion-ios-telephone mr-2 tx-22" style="position: relative; top: -2px;"></i>
                                    <strong class="tx-dark">
                                        <asp:Literal ID="ltrfewitenMobile" runat="server" Text=""></asp:Literal></strong>
                                </div>
                            </div>
                        </asp:PlaceHolder>
                    </div>
                </div>
                <!-- card-header -->
                <div class="card-body p-3 pt-0">
                    <div class="table-responsive">
                        <asp:GridView AutoGenerateColumns="false" ID="gvfewitenavaliable" runat="server" CssClass="table table-bordered gridview_table" GridLines="None"
                            AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" DataSourceID="SDSfewitemavalible">
                            <Columns>
                                <asp:TemplateField HeaderText="Sl No" ItemStyle-Width="80">
                                    <ItemTemplate>
                                        <asp:Label ID="lblRowNumber" Text='<%# Container.DataItemIndex + 1 %>' runat="server" />
                                    </ItemTemplate>
                                </asp:TemplateField>
                                <asp:BoundField HeaderText="Item Name" DataField="item_name" SortExpression="item_name" />
                                <asp:BoundField HeaderText="Ordered Qty" DataField="fsto_ItemQty" ItemStyle-Width="120" SortExpression="fsto_ItemQty" DataFormatString="{0:0.00}" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" />
                                <asp:TemplateField HeaderText="Available Qty" ItemStyle-Width="100" ItemStyle-HorizontalAlign="Right" HeaderStyle-CssClass="left_align" HeaderStyle-HorizontalAlign="Left">
                                    <ItemTemplate>
                                        <input type="text" style="display: none" />
                                        <input type="password" style="display: none" />
                                        <asp:TextBox runat="server" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" MaxLength="3" orderqty='<%# Eval("fsto_ItemQty") %>' ID="txtUpdate" onfocus="this.select()" ValidationGroup="ManualPacking" Width="120px" Text='<%#Eval("fsto_pkdQty")%>' CssClass="form-control packedqty text-right" prodid='<%# Eval("fstod_id") %>' Enabled='<%# (Convert.ToInt32(Eval("fsto_isalreadypacked"))  == 1 ? false : true) %>' autocomplete="off"></asp:TextBox>
                                    </ItemTemplate>
                                </asp:TemplateField>
                                <asp:TemplateField HeaderText="Product picked" ItemStyle-Width="100" ItemStyle-HorizontalAlign="Right" Visible="false" HeaderStyle-CssClass="left_align">
                                    <ItemTemplate>
                                        <input type="text" style="display: none" />
                                        <input type="password" style="display: none" />
                                        <asp:TextBox runat="server" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" MaxLength="3" orderqty='<%# Eval("fsto_ItemQty") %>' ID="txtSubPrd" onfocus="this.select()" ValidationGroup="ManualPacking" Width="120px" Enabled='<%# (Convert.ToInt32(Eval("stit_ParentItemId")) > 0  && (Convert.ToInt32(Eval("br_parentPacking")) == 0 ? true : false)) %>' CssClass="form-control text-right" autocomplete="off" prodid='<%# Eval("fstod_id") %>'></asp:TextBox>
                                    </ItemTemplate>
                                </asp:TemplateField>
                            </Columns>
                            <EmptyDataTemplate>
                                <div class="text-center">
                                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                    <h6 class="mb-3">No order record available.</h6>
                                </div>
                            </EmptyDataTemplate>
                        </asp:GridView>
                        <asp:SqlDataSource runat="server" ID="SDSfewitemavalible" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                            SelectCommand="SELECT fo.fsto_uid AS fsto_uid,fo.fsto_isalreadypacked,fo.fsto_id AS fsto_id,fd.fsto_ItemId,fd.fstod_id,fsto_createdOn,fsto_destination,fsto_destination,fsto_source,
                                    (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_source) AS fsto_source,
                                    (SELECT br_parentPacking FROM finascop_branch WHERE br_ID = fsto_source) AS br_parentPacking,
                                    (SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = fsto_ItemId) AS item_name,
                                    (SELECT stit_ParentItemId FROM finascop_stock_itemmaster WHERE stit_ID = fsto_ItemId) AS stit_ParentItemId,
                                    fsto_ItemQty,fsto_pkdQty, (SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'IS_INVOICE') as ownInvoice,
                                    (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) AS branch,
                                    fo.fsto_id AS fsto_id,fstro_ItemMRP AS mrp
                                    FROM finascop_stock_transfer_order fo INNER JOIN finascop_stock_transfer_order_details fd ON fo.fsto_id = fd.fsto_id  
                                    AND fo.fsto_id=@fstoid">
                            <SelectParameters>
                                <asp:ControlParameter ControlID="hdnfstoid" Name="fstoid" DefaultValue="0" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                    </div>
                </div>
                <!-- card-body -->
                <div class="card-footer d-flex flex-wrap justify-content-center">
                    <asp:Button runat="server" Text="Submit" ID="btnSubmit" OnClick="btnSubmit_Click" OnClientClick="if(!validateqty()) return confirm('Not all products in your order have been picked. Would you like to proceed with an incomplete order?');" CssClass="btn btn-primary mr-2"></asp:Button>
                    <asp:LinkButton runat="server" ID="btncancel" Text="Cancel" CssClass="btn btn-danger"></asp:LinkButton>
                </div>
                <!-- card-footer -->
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">     
        $(function () {          
            $('.objdiv').click(function () {
                $(this).closest('div').addClass('processing_loader');
                setTimeout(function () {
                    $('.objdiv').removeClass('processing_loader');
                }, 7000);
            });


        });
    function showOrderProcessModal() {
        $('#modalorderaccept').modal('hide');
        $('#modalorderProcess').modal('show');       
      }
    function showOrderreject() {        
        $('#modalvieworder').modal('hide');
        $('#modalorderreject').modal('show');
    }

    $(document).ready(function () {
        $('#<%= Btnordereassign.ClientID %>').on("click", function (e) {
            var btn = this;            
            var fsto_id = $('#<%= hdnfstoid.ClientID %>').val();
            var order_orderId = $('#<%=hdnorderid.ClientID %>').val();
            var fsto_uid = $('#<%= hdnFstoUid.ClientID %>').val();
            var orderid = $('#<%= hdnorder_id.ClientID %>').val();
            $(btn).attr("data-fsto_id", fsto_id);
            $(btn).attr("data-order_orderId", order_orderId);
            $(btn).attr("data-fsto_uid", fsto_uid);
            $(btn).attr("data-orderid", orderid);           
            assignOrderPicker(btn);
        });
    });
</script>
 <style>
 @media (max-width: 991px) {
             .pg-ntion {
                 flex: auto;
                 max-width: none;
                 width: auto;
             }
         }
          @media (min-width: 992px) {
            #modalvieworder .modal-dialog{
                max-width: 900px;
            }
          }
          @media (min-width: 992px) {
            #modalrfewitemavaliable .modal-dialog{
                max-width: 900px;
            }
          }           
           #modalorderaccept {
            z-index: 1051;
           }
                   
 </style>


        

