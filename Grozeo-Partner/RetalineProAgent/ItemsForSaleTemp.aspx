<%@ Page Language="C#" MasterPageFile="~/AgentMaster.Master" Title="Items for sale" AutoEventWireup="true" CodeBehind="ItemsForSaleTemp.aspx.cs" Inherits="RetalineProAgent.ItemsForSaleTemp" %>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div class="col-sm-6">
            <h1 style="float: left;">Current Stock at &nbsp;
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>
            </h1>
                <asp:PlaceHolder ID="plcSelectBranchModel" runat="server">
                    <asp:DropDownList ID="selBranches" OnSelectedIndexChanged="selBranches_SelectedIndexChanged" AutoPostBack="true" style="float: left; width: 50%" DataSourceID="ODSStore" AppendDataBoundItems="true" DataTextField="BranchName"   ValidationGroup="StockUpdate" DataValueField="BranchId" CssClass="form-control" runat="server"><asp:ListItem Text="Select Branch" Value=""></asp:ListItem></asp:DropDownList>
                    <asp:RequiredFieldValidator runat="server" SetFocusOnError="true" ControlToValidate="selBranches" ValidationGroup="StockUpdate" Text="*" ForeColor="Red" ErrorMessage="Select branch"></asp:RequiredFieldValidator>
                </asp:PlaceHolder>
                <asp:ObjectDataSource ID="ODSStore" runat="server" OnSelected="ODSStore_Selected" OnSelecting="ODSStore_Selecting" TypeName="RetalineProAgent.Core.Services.APIService" SelectMethod="GetStores">
                      <SelectParameters>
                          <asp:Parameter Name="storegroupid" />
                          <asp:Parameter Name="all" DefaultValue="false" Type="Boolean" />
                      </SelectParameters>
                  </asp:ObjectDataSource>

          </div>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
                                            <div class="card-header">
              <h3 class="card-title">
                  <%--<asp:Literal runat="server" ID="ltrTotalItemsSelected" Text="0"></asp:Literal> Item/s--%>
                  <asp:LinkButton runat="server" Text="Save Changes" ValidationGroup="StockUpdate" ID="btnStockSaveChanges" OnClick="btnStockSaveChanges_Click" CssClass="btn btn-info"></asp:LinkButton>&nbsp;
                  <asp:LinkButton runat="server" Text="Publish Items" ValidationGroup="StockUpdate" ID="btnStockPublishItems" OnClick="btnStockPublishItems_Click" CssClass="btn btn-info"></asp:LinkButton>
                  <asp:PlaceHolder ID="plcMultipleBranchButton" Visible="false" runat="server"><button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal-select-branch">
                  Publish Items
                </button></asp:PlaceHolder><asp:Label ID="lblResult" runat="server"></asp:Label>
              </h3>

              <div class="float-right">
                  <asp:Literal runat="server" ID="ltrPagingCurStart" Text="1"></asp:Literal>-
                  <asp:Literal runat="server" ID="ltrPagingCurTotal" Text="50"></asp:Literal>/
                  <asp:Literal runat="server" ID="ltrPagingTotal" Text="200"></asp:Literal>
                  <div class="btn-group">
                      <asp:LinkButton ID="lbtnPagerLeft" runat="server" ValidationGroup="StockUpdate" OnClick="lbtnPagerLeft_Click" CssClass="btn btn-default btn-sm">
                      <i class="fa fa-chevron-left"></i>
                      </asp:LinkButton>
                      <asp:LinkButton ID="lbtnPagerRight" runat="server" ValidationGroup="StockUpdate" OnClick="lbtnPagerRight_Click" CssClass="btn btn-default btn-sm">
                          <i class="fa fa-chevron-right"></i>
                      </asp:LinkButton>
                    <%--<button type="button" class="btn btn-default btn-sm">
                      <i class="fa fa-chevron-left"></i>
                    </button>--%>
                    <%--<button type="button" class="btn btn-default btn-sm">
                      <i class="fa fa-chevron-right"></i>
                    </button>--%>
                  </div>
                  <!-- /.btn-group -->
                </div>
              <div class="card-tools">
                <div class="input-group input-group-sm">
                    <asp:TextBox ID="txtSearchProduct" runat="server" CssClass="form-control" placeholder="Search Products"></asp:TextBox>
                  <%--<input type="text" class="form-control" placeholder="Search Products">--%>
                    <asp:LinkButton runat="server" CssClass="input-group-append">
                        <div class="btn btn-primary">
                          <i class="fa fa-search"></i>
                        </div>
                    </asp:LinkButton>
                </div>
              </div>
              <br /><br />
              <small class="text-muted">
                  <asp:Literal ID="ltrComment" runat="server" Text="Please ensure to save changes before leaving the page. The items along with changes will be listed in public site once click on the button 'Publish Items'"></asp:Literal>
                  </small>
            </div>

              <div class="card-body">

                                              <div class="table-responsive mailbox-messages">

                                <asp:GridView AutoGenerateColumns="false" ID="gvProducts" runat="server" CssClass="table table-hover table-striped" OnRowDataBound="gvProducts_RowDataBound"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="true" PagerSettings-Visible="true" DataKeyNames="Id" PageSize="10" OnDataBound="gvProducts_DataBound" DataSourceID="SDSInventory">
                                    <Columns>
                                        <asp:TemplateField>
                                            <ItemTemplate><asp:CheckBox ID="chkProductItem" AutoPostBack="true" OnCheckedChanged="chkProductItem_CheckedChanged" itemid='<%# Eval("Id") %>' Checked="true" runat="server" /></ItemTemplate>
                                        </asp:TemplateField>
                                        <asp:BoundField HeaderText="Name" DataField="Description" SortExpression="Description" />
                                        <asp:TemplateField HeaderText="MRP" SortExpression="MRP"><ItemTemplate><asp:TextBox ID="txtMRP" CssClass="mrp" TextMode="Number" SetFocusOnError="true" Width="50" Text='<%# Bind("MRP")%>' ValidationGroup="StockUpdate" min="0" runat="server"></asp:TextBox><asp:RequiredFieldValidator runat="server" ControlToValidate="txtMRP" ValidationGroup="StockUpdate" Text="*" ForeColor="Red" ErrorMessage="MRP is required"></asp:RequiredFieldValidator> </ItemTemplate></asp:TemplateField>
 						                <asp:TemplateField HeaderText="Margin ( % )" ItemStyle-HorizontalAlign="Right" SortExpression="Margin"><ItemTemplate><asp:Label ID="lblPCustomMarginVal" CssClass="lblmarginVal" runat="server"></asp:Label> &nbsp;(<asp:Label ID="lblPCustomMargine" runat="server" CssClass="lblmargin" Text='<%# Eval("Margin") %>'></asp:Label>%)</ItemTemplate></asp:TemplateField>
                                       <asp:TemplateField HeaderText="Selling Price" ItemStyle-HorizontalAlign="Right" SortExpression="SellingPrice"><ItemTemplate><asp:Label ID="lblSellingPrice" CssClass="lblsellingprice" runat="server" Text='<%# Eval("SellingPrice") %>'></asp:Label></ItemTemplate></asp:TemplateField>
                                        <asp:TemplateField HeaderText="Stock" SortExpression="Qty"><ItemTemplate><asp:TextBox ID="txtPStock" TextMode="Number" Width="50" min="1" ValidationGroup="StockUpdate" Text='<%# Bind("Qty")%>' runat="server"></asp:TextBox><asp:RequiredFieldValidator runat="server" SetFocusOnError="true" ControlToValidate="txtPStock" ValidationGroup="StockUpdate" Text="*" ForeColor="Red" ErrorMessage="Qty is required"></asp:RequiredFieldValidator></ItemTemplate></asp:TemplateField>
                                    </Columns>
                                    <EmptyDataTemplate>
                                        <small>You dont have any item selected for sale. Please go to the page <a href="/Tenant/InventoryMapping">Select items for Sale</a> to select from master data or you can upload CSV. </small>
                                              </EmptyDataTemplate>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSInventory" ConnectionString="<%$ ConnectionStrings:conn %>" OnSelecting="SDSInventory_Selecting"
         SelectCommand="SELECT i.Id, i.ErpId, i.StoreId, i.Description, c.MRP, c.Qty, isnull(isnull(c.Margin, i.Margin), 5) as Margin, c.SellingPrice FROM InventoryMapping i left join BranchCurrentStock c on c.InventoryId=i.Id and c.BranchId=@BranchId WHERE i.StoreId=@storeId and (isnull(@search, '') = '' or i.[Description] like '%'+@search+'%')" 
        OnSelected="SDSInventory_Selected">
        <SelectParameters>
            <asp:ControlParameter Name="search" ControlID="txtSearchProduct" Type="String" ConvertEmptyStringToNull="false" />
            <asp:Parameter Name="BranchId" Type="Int32" DefaultValue="-1" ConvertEmptyStringToNull="false" />
            <asp:Parameter Name="storeId" Type="Int32" DefaultValue="-1" />
            <asp:Parameter Name="user" DefaultValue="" />
        </SelectParameters>
    </asp:SqlDataSource>

                                </div>


                </div>
                </div>
              </div>
            </div>
    </div>

    <asp:PlaceHolder ID="plcSelectBranchModel123" runat="server">
         <div class="modal fade" id="modal-select-branch">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Select Branch</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                  <div class="form-group">
                  </div>                                    
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <asp:LinkButton runat="server" Text="Publish Items" ValidationGroup="StockUpdate" OnClick="btnStockPublishItems_Click" CssClass="btn btn-info"></asp:LinkButton>
              <%--<button type="button" class="btn btn-primary">Publish Inventory</button>--%>
            </div>
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
    </asp:PlaceHolder>

<script type="text/javascript">
    $('input.mrp').on('input', function (e) {
        if ($(this).val() != '' && !isNaN($(this).val())) {
            var lblmargin = $(this).closest('tr').find('td span.lblmargin');
            var lblmarginVal = $(this).closest('tr').find('td span.lblmarginVal');
            var lblsellingprice = $(this).closest('tr').find('td span.lblsellingprice');

            if (lblmargin && lblsellingprice && lblmargin.length > 0 && lblsellingprice.length > 0) {
                var mrp = 0; var margin = 0;
                margin = lblmargin[0].innerText;
                mrp = $(this).val();
                if (margin && margin > 0) {
                    var minmargin = (mrp * margin) / 100;
                    lblmarginVal[0].innerText =  minmargin;
                    lblsellingprice[0].innerText = (mrp - minmargin);
                }
            }
        }
    });
</script>
</asp:Content>