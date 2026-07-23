<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" CodeBehind="orderCalculationHeads.aspx.cs" Inherits="RetalineProAgent.Finance.orderCalculationHeads" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
      <a href="/Finance/Navigations/Costallocationandautoposting"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle">Order Calculation Heads</h6>
     <p class="mb-0">You can see Order Calculation Heads here</p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <section class="content">
        <div class="row row-sm">
            <div class="col-12 pb-3">
                <div class="card m-0 h-100">
                    <div class="card-header shadow_top">
                        <div class="row row-sm">                            
                            <div class="col-12 col-lg-7 d-flex align-items-end">
                                <div class="input-group input_search_box">
                                    <input type="text" style="display: none" />
                                    <input type="password" style="display: none" />
                                    <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" autocomplete="off"></asp:TextBox>
                                    <asp:LinkButton runat="server" CssClass="input-group-append">
                                        <div class="btn bd bd-l-0 tx-gray-600" >
                                          <i class="fa fa-search"></i>
                              </div>
                                    </asp:LinkButton>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="table-responsive" >
                            <asp:GridView ID="gvcostpurpose" runat="server" AutoGenerateColumns="false" CssClass="table table-bordered gridview_table" BorderStyle="Solid"
                                DataSourceID="SDSCostpurpose" AllowPaging="true" PageSize="10">
                                <Columns>
                                    <asp:TemplateField HeaderText="No" ItemStyle-Width="2%">
                                        <ItemTemplate>
                                            <%# Container.DataItemIndex + 1 %>
                                        </ItemTemplate>
                                    </asp:TemplateField>
                                    <asp:BoundField HeaderText="Name" ReadOnly="true" DataField="NAME" SortExpression="NAME" ItemStyle-Width="15%" HeaderStyle-CssClass="py-1 " />
                                </Columns>
                                 <EmptyDataTemplate>
                                <div class="text-center">
                                    <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                    <h6 class="mb-3">No record available</h6>
                                </div>
                            </EmptyDataTemplate>
                            <PagerStyle HorizontalAlign="Center" CssClass="cssPager" />
                              <PagerSettings Mode="NumericFirstLast" PageButtonCount="5"/>                            </asp:GridView>
                            <asp:SqlDataSource runat="server" ID="SDSCostpurpose" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                SelectCommand="SELECT id,NAME FROM finance_calculation_heads   WHERE (TRIM(@search) LIKE '' OR NAME LIKE CONCAT('%', @search, '%')) ORDER BY name">
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