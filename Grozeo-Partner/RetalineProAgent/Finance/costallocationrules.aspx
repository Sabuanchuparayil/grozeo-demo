<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" CodeBehind="costallocationrules.aspx.cs" Inherits="RetalineProAgent.Finance.costallocationrules" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
<a href="/Finance/Navigations/ChartofAccounts"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Cost Allocation Rules</h6>
    <p class="mb-0">You can see Cost Allocation Rules here</p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <section class="content">
        <div class="row row-sm">
            <div class="col-12 pb-3">
                <div class="card m-0 h-100">
                    <div class="card-header shadow_top">
                        <div class="row row-sm">
                            <div class="col-12 col-lg-5">
                                <div class="d-inline-block mb-2 mb-lg-0 mr-lg-3">
                                    <a href="/Finance/CostAllocation" class="btn btn-primary py-1 AddVoucherBTN">Create New</a>
                                </div>
                            </div>
                            <div class="col-12 col-lg-7 d-flex align-items-end">
                                <div class="input-group input_search_box">
                                    <input type="text" style="display: none" />
                                    <input type="password" style="display: none" />
                                    <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="off"></asp:TextBox>
                                    <asp:LinkButton runat="server" CssClass="input-group-append">
                                        <div class="btn bd bd-l-0 tx-gray-600">
                                          <i class="fa fa-search"></i>
                              </div>
                                    </asp:LinkButton>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="table-responsive">
                            <asp:GridView ID="gvcostpurpose" runat="server" AutoGenerateColumns="false" CssClass="table table-bordered gridview_table" BorderStyle="Solid"
                                DataSourceID="SDSCostpurpose" AllowPaging="true" PageSize="5">
                                <Columns>
                                    <asp:BoundField HeaderText="Rule name" ReadOnly="true" DataField="rulename" SortExpression="rulename" ItemStyle-Width="25%" HeaderStyle-CssClass="py-1 " />
                                     <asp:BoundField HeaderText="Finance Function" ReadOnly="true" DataField="NAME" SortExpression="NAME" ItemStyle-Width="15%" HeaderStyle-CssClass="py-1 " />
                                     <asp:BoundField HeaderText="Sales Type" ReadOnly="true" DataField="saletype" SortExpression="saletype" ItemStyle-Width="15%" HeaderStyle-CssClass="py-1 " />
                                     <asp:BoundField HeaderText="Payment Type" ReadOnly="true" DataField="paymenttype" SortExpression="paymenttype" ItemStyle-Width="15%" HeaderStyle-CssClass="py-1 " />
                                     <asp:BoundField HeaderText="Delivery Type" ReadOnly="true" DataField="DeliveryType" SortExpression="DeliveryType" ItemStyle-Width="15%" HeaderStyle-CssClass="py-1 " />
                                     <asp:BoundField HeaderText="Area Type" ReadOnly="true" DataField="areaname" SortExpression="areaname" ItemStyle-Width="10%" HeaderStyle-CssClass="py-1 " />
                                    <asp:TemplateField ItemStyle-Width="150">
                                        <ItemTemplate>
                                            <asp:LinkButton ID="btn_Edit" runat="server" OnClick="btn_Edit_Click" Text="Edit" recid='<%# Eval("id") %>' CausesValidation="false" />
                                        </ItemTemplate>
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
                            <asp:SqlDataSource runat="server" ID="SDSCostpurpose" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                SelectCommand="SELECT cd.id ,rulename,em.NAME,fa.name as areaname, CASE WHEN sale_type_id=0 THEN 'Grozeo' ELSE 'Tenant' END AS saletype, 
                                CASE WHEN payment_type_id=0 THEN 'Online' ELSE 'Pay On Delivery' END AS paymenttype,
                                CASE  WHEN delivery_type_id = 0 THEN 'Courier' ELSE 'Local' END AS DeliveryType
                                FROM  cost_distribution_function cd 
                                INNER JOIN finance_event_master em ON  em.id = event_master_id
                                INNER JOIN finance_area_type fa ON cd.area_type_id=fa.id WHERE (TRIM(@search) LIKE '' OR rulename LIKE CONCAT('%', @search, '%')) ORDER BY id DESC">
                                <SelectParameters>
                                    <asp:ControlParameter Name="search" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                                </SelectParameters>
                            </asp:SqlDataSource>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</asp:Content>
