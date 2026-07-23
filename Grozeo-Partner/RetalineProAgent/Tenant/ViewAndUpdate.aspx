<%@ Page Language="C#" AutoEventWireup="true" Async="true" Title="" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="ViewAndUpdate.aspx.cs" Inherits="RetalineProAgent.ViewAndUpdate" %>


<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Tenant/PendingOrders"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Tenant/PendingOrders">Order Packing</a></li>
    <li class="breadcrumb-item active" aria-current="page">View & Update Orders</li>--%>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle" runat="server" Text="View & Update Orders"></asp:Literal></h6>
    
</asp:Content>
<%--<asp:Content ContentPlaceHolderID="cntHeaderContainer" runat="server"></asp:Content>--%>

<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">

<%--<asp:PlaceHolder ID="plcActionButtonsRow" runat="server">--%>
            <%--<div class="row ">
          <div class="col-md-12 my-4">
            <p class="tx-15 m-0 alert alert-info">Order Id: <b><asp:Literal ID="ltrTitleOrderId" runat="server" Text=""></asp:Literal></b> | Total: <b><%= ConfigurationManager.AppSettings.Get("CurrencySymbol") %><asp:Literal ID="ltrTitleTotal" runat="server" Text=""></asp:Literal>
            </b> | Paymet Mode: <b><asp:Literal ID="ltrPayMode" runat="server" Text=""></asp:Literal></b> | Payment Status: <b><asp:Literal ID="ltrPayStatus" runat="server" Text=""></asp:Literal></b>
                | Status: <b><asp:Literal ID="ltrTitleStatus" runat="server" Text=""></asp:Literal></b></p>
          </div>
        </div>
    <asp:Image runat="server" ID="imgProgressing" ImageAlign="AbsMiddle" width="30" ImageUrl="https://grozeo.azurewebsites.net/images/processing.gif" Visible="false"/>--%>

<%--<div id="dvNoneSponsored" runat="server" class="row row-sm">
    <div class="col-md-3 mb-2 mb-md-0" runat="server" ID="dvAbtnAssignOrderPicker"><asp:HyperLink runat="server" ID="hlAssignOrderPicker" CssClass="btn btn-block btn-outline-primary btn-sm" Visible="false"><i class="fa fa-user"></i> Assign Order Picker</asp:HyperLink></div>
    <div class="col-md-3 mb-2 mb-md-0" runat="server" ID="dvAbtnManualPacking"><asp:HyperLink runat="server" ID="hlManualPacking" CssClass="btn btn-block btn-outline-primary btn-sm" Visible="false"><i class="fa fa-user"></i> Manual Packing</asp:HyperLink></div>
    <div class="col-md-3 mb-2 mb-md-0" runat="server" ID="dvAbtnActiveDeliveryBoys"><asp:HyperLink runat="server" ID="hlActiveDeliveryBoys" CssClass="btn btn-block btn-outline-primary btn-sm" Visible="false"><i class="fa fa-bell"></i> Assign Delivery Boy</asp:HyperLink></div>--%>
    <%--<div class="col-md-3" runat="server" ID="dvAbtnDeliveryDetails"><asp:HyperLink runat="server" ID="hlDeliveryDetails" CssClass="btn btn-block btn-outline-primary btn-xs" NavigateUrl="/ViewOrderDetails.aspx?fsto_id={0}"><i class="fa fa-table"></i> View Order Details</asp:HyperLink></div>
    <div class="col-md-3" runat="server" ID="dvAbtnPackingCompleted"><asp:HyperLink runat="server" ID="hlPackingCompleted"  CssClass="btn btn-block btn-outline-primary btn-xs"><i class="fa fa-edit"></i> Packing Completed</asp:HyperLink></div>--%>
<%--</div>--%>
<%--<div id="dvSponsored" runat="server" visible="false" class="row row-sm">
    <div class="col-md-3 mb-2 mb-md-0"><a Class="btn btn-block btn-outline-primary btn-sm">Sponsored Order</a></div>
</div>--%>

<%--</asp:PlaceHolder>--%>

    <div class="row row-sm">
          <!-- left column -->

        <div class="col-12">
            <asp:Panel runat="server" ID="pnlInvalidOrder" Visible="false" CssClass="myproduct_alertmsg_wrap" >
                <div class="myproduct_alertmsg">
                    <h4>Invalid Order</h4>
                    <div class="myproduct_alertmsg_cont">
                        <br />
                        <br />
                        <p>The order is invalid or you don't have permission to access the data.</p>
                        <br />
                        <br />
                        <div class="btn-sec text-center d-flex justify-content-center">
                            <asp:HyperLink runat="server" NavigateUrl="/Tenant/pendingorders" Text="Go to My Orders" class="btn btn-primary btn-drk-green float-none mx-2 wd-sm-auto-force p-2 px-4"></asp:HyperLink>
                        </div>

                    </div>
                </div>

            </asp:Panel>
            <asp:Panel ID="pnlValidOrder" runat="server" CssClass="card-columns card_columns_two" Style="column-gap: 1.25rem;">
                <div class="card bg-white" style="border-radius: 10px !important; overflow: hidden;">
                <div class="card-header bd-b-0-force bg-light mb-0" style="border-radius: 0px !important;">
                    <h3 class="slim-card-title text-capitalize">Order Details</h3>                    
                </div>
                      <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                      <tr>
                                        <th>Order No.</th>
                                        <td><asp:Literal ID="ltrOrdId" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>From</th>  
                                        <td><asp:Literal ID="ltrFrom" runat="server"></asp:Literal></td>     
                                      </tr>  
                                      <tr>
                                        <th>TO No.</th>
                                        <td><asp:Literal ID="ltrToNo" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Customer Name</th>
                                        <td><asp:Literal ID="ltrCustName" runat="server"></asp:Literal></td>
                                      </tr>
                                     <tr>
                                        <th>No. of Items</th>
                                        <td><asp:Literal ID="ltrNoItems" runat="server"></asp:Literal></td>
                                      </tr>
                                     <tr>
                                        <th>Value of Order</th>
                                        <td><asp:Literal ID="ltrValue" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Updated Value of Order</th>
                                        <td><asp:Literal ID="ltrUpdtedValue" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Balance To Pay</th>
                                        <td><asp:Literal ID="ltrBalToPay" runat="server"></asp:Literal></td>
                                      </tr>
                            </tbody>
                        </table>
                      </div>
                    </div>

                <div class="card bg-white" style="border-radius: 10px !important; overflow: hidden;">
                    <div class="card-header bd-b-0-force bg-light mb-0" style="border-radius: 0px !important;">
                        <h6 class="slim-card-title text-capitalize">Packing Info</h6>
                    </div>
                <div class="table-responsive">
  
                  <table class="table">
                            <tbody>
                                      <tr>
                                        <th>Order Type</th>
                                        <td><asp:Literal ID="ltrOrderType" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>To</th>  
                                        <td><asp:Literal ID="ltrTo" runat="server"></asp:Literal></td>     
                                      </tr>  
                                      <tr>
                                        <th>Order Date</th>
                                        <td><asp:Literal ID="ltrOrdDte" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Customer Number</th>
                                        <td><asp:Literal ID="ltrCustNo" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Total Quantity</th>
                                        <td><asp:Literal ID="ltrTtlQty" runat="server"></asp:Literal></td>
                                      </tr>
                                      <tr>
                                        <th>Total items to be weighed</th>
                                        <td><asp:Literal ID="ltritmweigh" runat="server"></asp:Literal></td>
                                      </tr>
                                     <tr>
                                        <th>Total items actually weighed</th>
                                        <td><asp:Literal ID="ltrActweigh" runat="server"></asp:Literal></td>
                                      </tr>
                                    <tr>
                                        <th>Orginal mode of payment</th>
                                        <td><asp:Literal ID="ltrModePay" runat="server"></asp:Literal></td>
                                      </tr>
                            </tbody>
                        </table>
                </div>
              </div><!--card-->

              

                
            </asp:Panel>
        </div>
        <div class="col-12">
            <div class="card bg-white" style="border-radius: 10px !important; overflow: hidden;">
                    <div class="card-header mb-0 bd-b-0-force bg-light mb-0card-header bd-b-0-force bg-light d-flex justify-content-between align-items-center">
                        <h6 class="slim-card-title text-capitalize">Order Item/s</h6>
                </div>
                <div class="table-responsive">
                      <asp:GridView AutoGenerateColumns="false" ID="gvItemDetails" PageSize="10" runat="server" GridLines="None" CssClass="table table-bordered gridview_table" 
                                    AllowPaging="true" PagerSettings-Visible="true" DataSourceID="SDSItemDetails">
                                    <Columns>
                                        <asp:TemplateField HeaderText = "Serial NO." ItemStyle-Width="100">
                                            <ItemTemplate>
                                                <asp:Label ID="lblRowNumber" Text='<%# Container.DataItemIndex + 1 %>' runat="server" />
                                            </ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:BoundField HeaderText="Item Name" DataField="item_name" />
                                        <asp:BoundField HeaderText="No. of Items" DataField="fsto_ItemQty" />
                                        <asp:BoundField HeaderText="Items Picked" DataField="fsto_pkdQty" />
                                        <%--<asp:BoundField HeaderText="Required Qty" DataField="stit_ConvertCalcRate" />--%>
                                        <asp:TemplateField HeaderText="Required Qty" >
                                        <ItemTemplate>
                                            <%# Eval("stit_ConvertCalcRate") + " " + Eval("packageType")%>
                                            <asp:HiddenField ID="hidId" runat="server" Value='<%# Eval("fsto_pkdQty") %>' />
                                        </ItemTemplate>
                                    </asp:TemplateField>
                                        <%--<asp:BoundField HeaderText="Picked Qty" DataField="fsto_stockValue" />--%>
                                        <asp:TemplateField HeaderText="Picked Qty" >
                                        <ItemTemplate>
                                            <%# Eval("fsto_stockValue") + " " + Eval("packageType")%>
                                        </ItemTemplate>
                                    </asp:TemplateField>
                                        <asp:BoundField HeaderText="Qty Diff" DataField="diff_conversion" />
                                        <asp:BoundField HeaderText="Value" DataField="sellingPrice" />
                                        <asp:BoundField HeaderText="New Value" DataField="fstro_ItemPackedSPincTax" />
                                        <asp:BoundField HeaderText="Difference" DataField="spValue_diff" />
                                    </Columns>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSItemDetails" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT fo.fsto_uid AS fsto_uid,fo.fsto_id AS fsto_id,fsto_ItemId,fsto_createdOn,fsto_destination,fsto_destination,fsto_source,
                                    (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_source) AS fsto_source,
                                    (SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_ID = fsto_ItemId) AS item_name,fsto_ItemQty,fsto_pkdQty,
                                    (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) AS branch,
                                    fo.fsto_id AS fsto_id,fstro_ItemMRP AS mrp,fstro_ItemSPincTax AS selPrce,fsto_stockValue,
                                    (SELECT stit_ConvertCalcRate FROM finascop_stock_itemmaster WHERE stit_ID = fsto_ItemId)  AS stit_ConvertCalcRate,
                                    (SELECT stit_ParentItemId FROM finascop_stock_itemmaster WHERE stit_ID = fsto_ItemId) AS stit_ParentItemId,
                                    (SELECT least_package_type_name FROM finascop_stock_itemmaster WHERE stit_ID = fsto_ItemId) AS packageType,
                                    (SELECT IF((stit_ParentItemId > 0),(fsto_stockValue-stit_ConvertCalcRate),(fsto_ItemQty - fsto_pkdQty)))AS diff_conversion,
                                    (fsto_ItemQty*fstro_ItemSPincTax)AS sellingPrice,
                                    fstro_ItemPackedSPincTax ,IF((fstro_ItemPackedSPincTax != 0),(fstro_ItemPackedSPincTax - (fsto_ItemQty*fstro_ItemSPincTax)),0) AS spValue_diff
                                    FROM finascop_stock_transfer_order fo 
                                    INNER JOIN finascop_stock_transfer_order_details fd ON fo.fsto_id = fd.fsto_id 
                                    AND fo.fsto_id=@fsto_id ORDER BY fo.fsto_id ASC"
                                OnSelecting="SDSItemDetails_Selecting">
                                <SelectParameters>
                                    <asp:Parameter Name="fsto_id" />
                                </SelectParameters>
                            </asp:SqlDataSource>
                        </div>

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
                        <asp:Button runat="server" ID="btnRevert" ValidationGroup="IncompleteOrders" CssClass="btn btn-primary bd-0" Text="Revert" OnClick="btnRevert_Click" OnClientClick="javascript:return confirm('You are going to change the order back to Pending Orders ??');" />&nbsp;
                        <asp:Button runat="server" ID="btnProceed" Enabled='<%# (Convert.ToInt32(Eval("fsto_pkdQty")) > 1 ? true : false) && (Convert.ToInt32(Eval("fsto_status")) > 9 ? true : false) %>' ValidationGroup="IncompleteOrders" OnClick="btnProceed_Click" CssClass="btn btn-primary bd-0 mx-2" Text="Proceed" />&nbsp;
                        <a href="#" class="btn btn-primary bd-0 mr-2" data-toggle="modal" data-target="#modalReason">Skip</a>&nbsp;
                        <asp:HyperLink runat="server" ID="hlCancelOrd" CssClass="btn btn-primary bd-0" onClick="javascript:return confirm('You are going to Cancel the Order?');"> Cancel Entire Order</asp:HyperLink>
                        <asp:Label ID="lblMessage" Font-Bold="true" runat="server" />

                    </div>
                </div>
                <!-- card-footer -->

            </div>
            <!--card-->


        </div>
        <!--col-12-->

    </div>

    <div id="modalReason" class="modal fade">
      <div class="modal-dialog modal-dialog-vertical-center" role="document">
        <div class="modal-content bd-0 tx-14">
          <div class="modal-header">
            <h6 class="tx-14 mg-b-0 tx-inverse tx-bold">Reason for skipping</h6>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body pd-20 pb-0">
              <div class="row">



              <div class="col-12">
                <div class="form-group">

                  <label>Reason:</label>
          <div>
            <div class="input-group">
              <div class="input-group-prepend">
              </div><!-- input-group-prepend -->
                   <asp:TextBox ID="txtReason" runat="server" CssClass="form-control" Height="100px" TextMode="MultiLine"/>
                    <asp:RequiredFieldValidator ValidationGroup="CreateReason" ControlToValidate="txtReason" ForeColor="Red" ErrorMessage="Input Reason" runat="server"></asp:RequiredFieldValidator>
            </div>
          </div><!-- wd-150 -->
                </div>
              </div>
              </div>
          </div>
          <div class="modal-footer">
            <asp:Button ID="btnSave" CssClass="btn btn-primary" OnClick="btnSave_Click" ValidationGroup="CreateReason" runat="server" Text="Save" formnovalidate/>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          </div>
        </div>
      </div><!-- modal-dialog -->
    </div><!-- modal -->

<style>
      .card_columns_two{
        column-count: 2;
      }

      @media (max-width: 921px) {
        .card_columns_two{
          column-count: 1;
        }
      }
    </style>

        
</asp:Content>

