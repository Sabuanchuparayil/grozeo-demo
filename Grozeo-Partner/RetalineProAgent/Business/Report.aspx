<%@ Page Language="C#" MasterPageFile="~/Business/BusinessMaster.master" Title="Marketing Reports" AutoEventWireup="true" CodeBehind="Report.aspx.cs" Inherits="RetalineProAgent.Report" %>
<%@ Import Namespace="RetalineProAgent.Core.BussinessModel.Store" %>

<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Marketing Report</h6>
        <p class="mb-0">Stores and settings</p>
    </div>
</asp:Content>


<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">

        <div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
            <div class="row row-sm mt-2">

                <div class="col-12 col-sm-4 col-lg-4 form-group mb-2 mb-lg-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtSearchStore" runat="server">Search by:</label>
                    <asp:TextBox ID="txtSearchStore" runat="server" autocomplete="off" CssClass="form-control" placeholder="Store name"></asp:TextBox>
                </div>
                <div class="col-12 col-sm-3 col-lg-3 form-group mb-2 mb-lg-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" for="txtDateFrom" runat="server">Period:</label>
                    <asp:DropDownList ID="selPeriod" runat="server" CssClass="form-control select2" >
                        <asp:ListItem Text="This Month" Value="1"></asp:ListItem>
                        <asp:ListItem Text="Previous Month" Value="2"></asp:ListItem>
                        <asp:ListItem Text="Last 3 Month" Value="3"></asp:ListItem>
                        <asp:ListItem Text="Last 6 Month" Value="4"></asp:ListItem>
                        <asp:ListItem Text="This Year" Value="5"></asp:ListItem>
                        <asp:ListItem Text="Last Year" Value="6"></asp:ListItem>
                        <asp:ListItem Text="All" Value="7"></asp:ListItem>
                    </asp:DropDownList>
                </div>
                <div class="col-12 col-sm-3 col-lg-3 form-group mb-0">
                    <label class="form-control-label mb-1 w-100 tx-dark" runat="server">Area:</label>
                    <asp:DropDownList ID="selArea" runat="server" CssClass="form-control select2" DataSourceID="SDSArea" DataTextField="areaName" DataValueField="id" AppendDataBoundItems="true">
                        <asp:ListItem Text="All Area" Value="-1"></asp:ListItem>
                    </asp:DropDownList>
                    <asp:SqlDataSource ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" runat="server" ID="SDSArea" ProviderName="MySql.Data.MySqlClient"
                        SelectCommand="SELECT * FROM `area_entries`"></asp:SqlDataSource>
                </div>
                <div class="col-4 col-sm-6 col-lg-2 d-flex align-items-end">
                    <label class="mb-0">&nbsp;</label>
                    <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary" runat="server">Search</asp:LinkButton> &nbsp;
                    <asp:Button runat="server" ID="btnDownload" CssClass="btn btn-primary" Text="Download" OnClick="btnDownload_Click" />
                    </div>

                

            </div>


            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <asp:ObjectDataSource ID="ODSReport" runat="server" TypeName="RetalineProAgent.Report" SelectMethod="GetMerchantReport" EnablePaging="true"
     StartRowIndexParameterName="startIndex" MaximumRowsParameterName="pageSize" SortParameterName="sortBy" SelectCountMethod="Count">
                        <SelectParameters>
                            <asp:ControlParameter Name="period" ControlID="selPeriod" DefaultValue="1" Type="Int32" />
                            <asp:ControlParameter Name="areaId" ControlID="selArea" DefaultValue="1" Type="Int32" />
                            <asp:ControlParameter Name="storeNamePref" ControlID="txtSearchStore" DefaultValue="" ConvertEmptyStringToNull="false" Type="String" />
                        </SelectParameters>
                    </asp:ObjectDataSource>

                    <asp:GridView AutoGenerateColumns="false" AllowCustomPaging="false" EnableSortingAndPagingCallbacks="false"  ID="gvContacts" runat="server" PageSize="50" AllowPaging="true" DataSourceID="ODSReport" AllowSorting="true"  CssClass="table table-bordered" GridLines="None" BorderColor="#ECECEC">
                        <Columns>
                            <asp:TemplateField HeaderText="Retailer" SortExpression="MerchantName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                <ItemTemplate><%# Eval("MerchantName") %> <asp:PlaceHolder runat="server" Visible='<%# Convert.ToInt32(Eval("TotalStores")) > 1 %>'><br /><small>( Stores: <%# Eval("BranchNames") %></small> )</asp:PlaceHolder> </ItemTemplate>
                            </asp:TemplateField>
                            <asp:BoundField HeaderText="Created On" DataField="CreatedOn" SortExpression="CreatedOn" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                            <asp:BoundField HeaderText="Stores" DataField="TotalStores" SortExpression="TotalStores" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                            <asp:BoundField HeaderText="Store Areas" DataField="BranchAreaName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />

                            <asp:BoundField HeaderText="Plan" DataField="PlanName" SortExpression="PlanName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                            <asp:BoundField HeaderText="Listed" DataField="IsFeatured" SortExpression="IsFeatured" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />

                            <asp:BoundField HeaderText="Checkout Enabled" DataField="CanCheckout" SortExpression="CanCheckout" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                            <asp:TemplateField HeaderText="Bank Accounts" SortExpression="BankAccounts" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                <ItemTemplate><%# Eval("BankAccounts") %></ItemTemplate>
                            </asp:TemplateField>
                            <asp:TemplateField HeaderText="Products" SortExpression="Products" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                <ItemTemplate><%# Eval("Products") %></ItemTemplate>
                            </asp:TemplateField>
                            <asp:BoundField HeaderText="Order Pickers" DataField="OrderPickers" SortExpression="OrderPickers" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                            <asp:TemplateField HeaderText="Pending Actions" SortExpression="PendingActions" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                <ItemTemplate><%# ((List<PendingActvity>)Eval("PendingActions")).Count() %></ItemTemplate>
                            </asp:TemplateField>
                            <asp:TemplateField HeaderText="Pending Jobs" SortExpression="PendingJobs" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                <ItemTemplate><%# ((List<PendingActvity>)Eval("PendingJobs")).Count() %></ItemTemplate>
                            </asp:TemplateField>
                            <asp:BoundField HeaderText="Orders" DataField="Orders" SortExpression="Orders" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White" />
                            <asp:TemplateField HeaderText="Order Value" SortExpression="OrderValue" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                <ItemTemplate><%# ConfigurationManager.AppSettings.Get("CurrencySymbol") %><%# Eval("OrderValue") %></ItemTemplate>
                            </asp:TemplateField>

                            <asp:TemplateField HeaderText="RO" SortExpression="RoName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                <ItemTemplate><asp:PlaceHolder runat="server" Visible='<%# !String.IsNullOrEmpty(Eval("RoName").ToString()) %>'><%# Eval("RoName") %><br /><small>( <%# Eval("Areaname") %>, <%# GetProspectMode(Eval("CpMode")) %> )</small></asp:PlaceHolder></ItemTemplate>
                            </asp:TemplateField>
                            <%--<asp:TemplateField HeaderStyle-Width="150px" ItemStyle-Width="150px" HeaderStyle-BackColor="#DEE2E6" HeaderText="View Image" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White">
                                <ItemTemplate>
                                    <asp:Image ID="imgDisplay" runat="server" Visible="false" />
                                    <asp:Label ID="lblMessage" runat="server" ForeColor="Red"></asp:Label>
                                    
                                </ItemTemplate>
                            </asp:TemplateField>--%>
                        </Columns>
                        <EmptyDataTemplate>
                            No reports.
                        </EmptyDataTemplate>
                    </asp:GridView>
                </div>
            </div>
        </div>
    </div>
</div>

    
</asp:Content>