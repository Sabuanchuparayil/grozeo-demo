<%@ Page Language="C#" Async="true" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="GST.aspx.cs" Inherits="RetalineProAgent.GST" %>


<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/storeconfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/BusinessSettings">Business Settings</a></li>
    <li class="breadcrumb-item active" aria-current="page"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %></li>--%>
  <%--  <a href="javascript:void(0)" onClick="history.go(-1); return false;"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>--%>
 <a href="/Navigations/BusinessSettings"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle"> <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GSTIN" : "VAT") %></h6>
        <p class="mb-0">Manage your <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> settings</p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-body">
            <div class="p-3 shadow_top">
            <div class="row row-sm">
                <div class="col-12 col-lg-9">
                <label class="col-12 p-0 m-0"><%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> List</label>
                    <p class="mg-b-0">List of <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT numbers") %>s added. The <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> account can be linked with store in the manage store page to enable online transaction</p>
                </div>
                <div class="col-lg-3 mt-3 mt-lg-0 d-flex align-items-start justify-content-lg-end">
                    <a href="/Tenant/Store/GST-Add" class="btn px-4 d-block d-md-inline-block btn-primary">Add <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GSTIN" : "VAT") %><i class="icon ion-plus-circled ml-2"></i></a>
                </div>
            </div>
        </div>
            <div class="table-responsive">
              <asp:GridView ID="gvGST" runat="server" GridLines="None" DataSourceID="SDSGST" AutoGenerateColumns="false" CssClass="table table-bordered gridview_table" AllowSorting="true" ShowFooter="false" AllowPaging="true" PageSize="10" OnRowDataBound="gvGST_RowDataBound">
                  <Columns>
                      <asp:BoundField DataField="gstin" />
                      <asp:BoundField HeaderText="Organization" DataField="organization" />
                      <asp:BoundField HeaderText="Address" DataField="address" />
                      <asp:BoundField HeaderText="Branch" DataField="storeid" />
                      <asp:TemplateField HeaderStyle-Width="50" HeaderText="Status">
                          <ItemTemplate>
                              <asp:HyperLink runat="server" CssClass="tx-warning" Visible='<%# (((bool)Eval("isverified")) ? false: true) %>' NavigateUrl='<%# String.Format("/Tenant/store/gst-add?action=verify&id={0}", Eval("id")) %>' Text="Verify"></asp:HyperLink>
                              <asp:Label runat="server" CssClass="tx-success" Visible='<%# (((bool)Eval("isverified")) ? true: false) %>' Text="Verified"></asp:Label>
                          </ItemTemplate>
                      </asp:TemplateField>
                  </Columns>
                  <EmptyDataTemplate>No <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> account added</EmptyDataTemplate>
                  <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                  <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
              </asp:GridView>
              <asp:SqlDataSource ID="SDSGST" OnSelecting="SDSGST_Selecting" runat="server" ConnectionString="<%$ ConnectionStrings:localConnection %>"
        SelectCommand="Select * from GST where TenantId=@storeId">
        <SelectParameters>
            <asp:Parameter Name="storeId" />
        </SelectParameters>
    </asp:SqlDataSource>
          </div><!-- table-responsive -->
        </div><!-- card-body -->

        <div class="card-body mt-3">
            <div class="p-3 shadow_top">
                <div class="row row-sm">
                    <div class="col-12">
                        <h6 class="tx-dark mb-1">Stores Connected with <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %></h6>
                        <p class="mb-0">Stores with <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %>. Select <%= (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %> for the stores.</p>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <asp:GridView ID="gvStores" runat="server" OnRowCommand="gvStores_RowCommand" GridLines="None" DataSourceID="ODSStore" AllowSorting="true" ShowFooter="false" AllowPaging="true" PageSize="10" AutoGenerateColumns="false" CssClass="table table-bordered gridview_table">
                    <Columns>
                        <asp:BoundField HeaderText="Store" DataField="BranchName" ReadOnly="true" />
                        <asp:TemplateField>
                            <HeaderTemplate>
                                <%# (ConfigurationManager.AppSettings.Get("VATType") == "2" ? "GST" : "VAT") %>
                            </HeaderTemplate>
                            <ItemTemplate>
                                <asp:Literal ID="ltrGst" runat="server" Text='<%# Eval("GSTIN") %>' Visible='<%# String.IsNullOrEmpty(Eval("GSTIN").ToString())? false : true %>'></asp:Literal>
                                <asp:DropDownList ID="selGstin" runat="server" AutoPostBack="true" CssClass="form-control" storeid='<%# Eval("DBBranchid") %>' DataSourceID="SDSGST" Visible='<%# String.IsNullOrEmpty(Eval("GSTIN").ToString())? true : false %>' DataTextField="gstin" DataValueField="id" OnSelectedIndexChanged="selGstin_SelectedIndexChanged" AppendDataBoundItems="true">
                                    <asp:ListItem Text="Select"></asp:ListItem>
                                </asp:DropDownList>
                            </ItemTemplate>
                            <EditItemTemplate>
                                <asp:DropDownList ID="selGstin" runat="server" AutoPostBack="true" CssClass="form-control" storeid='<%# Eval("DBBranchid") %>' DataSourceID="SDSGST" DataTextField="gstin" DataValueField="id" OnSelectedIndexChanged="selGstin_SelectedIndexChanged" AppendDataBoundItems="true">
                                    <asp:ListItem Text="Select"></asp:ListItem>
                                </asp:DropDownList>
                                <asp:RequiredFieldValidator runat="server" ControlToValidate="selGstin" ErrorMessage="Please select gstin" ForeColor="Red" ValidationGroup="ChangeGst"></asp:RequiredFieldValidator>
                            </EditItemTemplate>
                        </asp:TemplateField>
                        <asp:TemplateField HeaderText="">
                            <ItemTemplate>
                                <asp:LinkButton runat="server" CommandName="Edit" Text="Change"></asp:LinkButton>
                            </ItemTemplate>
                            <EditItemTemplate>
                                <asp:LinkButton runat="server" CommandName="Cancel" Text="Cancel" ValidationGroup="ChangeGst"></asp:LinkButton>
                            </EditItemTemplate>
                        </asp:TemplateField>
                    </Columns>
                    <EmptyDataTemplate>
                        <div class="text-center">
                            <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                            <h6 class="mb-3">No record available</h6>
                        </div>
                    </EmptyDataTemplate>
                    <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                    <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>
                </asp:GridView>
                <asp:ObjectDataSource ID="ODSStore" runat="server" OnSelecting="ODSStore_Selecting" TypeName="RetalineProAgent.Services.StoreService" SelectMethod="GetStores">
                    <SelectParameters>
                        <asp:Parameter Name="storegroupid" />
                        <asp:Parameter Name="apistoregroupid" />
                        <asp:Parameter Name="all" DefaultValue="true" Type="Boolean" />
                    </SelectParameters>
                </asp:ObjectDataSource>
            </div> <!-- table-responsive -->
            
        </div>
    </div>
    <!-- card -->
</asp:Content>

