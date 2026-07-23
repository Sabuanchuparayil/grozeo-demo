<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Finance/FinanceMaster.master" CodeBehind="Autopostingsettings.aspx.cs" Inherits="RetalineProAgent.Finance.Autopostingsettings" %>

<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
   <a href="/Finance/Navigations/ChartofAccounts"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"> Auto Posting Rules</h6>
     <p class="mb-0">You can see Auto Posting Rules here</p>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
    <section class="content">
        <div class="row row-sm">
            <div class="col-12 pb-3">
                <div class="card m-0 h-100">
                    <div class="card-header">
                        <div class="row row-sm">
                            <div class="col-12 col-lg-5 d-flex align-items-center">
                                <div class="d-inline-block mb-2 mb-lg-0 mr-3">
                                    <a  href="/Finance/AutoPostingRules"  class="btn btn-primary py-1 AddVoucherBTN">Create New</a>
                                </div>
                                <div class="d-flex align-items-center showrols">
                                    <asp:CheckBox ID="cbShowDisabledRules" Text="Show Disabled Rules" runat="server" AutoPostBack="true"></asp:CheckBox>
                                </div>
                            </div>
                            <div class="col-12 col-lg-7 d-flex align-items-end">
                                <div class="input-group input_search_box">
                                    <input type="text" style="display: none" />
                                    <input type="password" style="display: none" />
                                    <asp:TextBox ID="txtSearch" runat="server" CssClass="form-control" placeholder="Search" Style="height: 31px;" autocomplete="off"></asp:TextBox>
                                   
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
                                DataSourceID="SDSCostpurpose" OnRowDataBound="gvcostpurpose_RowDataBound" AllowPaging="true" PageSize="12">
                               
                                <Columns>
                                    <asp:BoundField HeaderText="Rule ID" ReadOnly="true" DataField="id" SortExpression="id" ItemStyle-Width="8%" HeaderStyle-CssClass="py-1" />
                                    <asp:BoundField HeaderText="Status" ReadOnly="true" DataField="status" visible="false" />
                                    <asp:BoundField HeaderText="Rule name" ReadOnly="true" DataField="rulename" SortExpression="rulename" ItemStyle-Width="30%" HeaderStyle-CssClass="py-1" />
                                    <asp:BoundField HeaderText="Finance Function" ReadOnly="true" DataField="eventmatername" SortExpression="eventmatername" ItemStyle-Width="20%" HeaderStyle-CssClass="py-1" />
                                    <asp:BoundField HeaderText="Voucher Type" ReadOnly="true" DataField="vouchername" SortExpression="vouchername" ItemStyle-Width="15%" HeaderStyle-CssClass="py-1" />
                                    <asp:TemplateField ItemStyle-Width="10%">
                                        <ItemTemplate>
                                            <asp:LinkButton ID="btn_View" runat="server" CssClass="mr-2" OnClick="btn_View_Click" Text="View" recid='<%# Eval("id") %>' CausesValidation="false"/>
                                            <asp:LinkButton ID="btn_Edit" runat="server" OnClick="btn_Edit_Click" Text="Edit" recid='<%# Eval("id") %>' CausesValidation="false" Visible='<%# Eval("status").ToString() == "1" %>' />
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
                                SelectCommand="SELECT fr.id,fr.Status,rulename,em.name AS eventmatername,fv.name AS vouchername FROM finance_autoposting_rule fr INNER JOIN finance_event_master em 
                                ON em.id = event_master_id INNER JOIN finance_voucher_type fv ON fv.id = voucher_id  
                                                    WHERE ((@disabled = 1 AND (fr.status = 0)) OR (@disabled = 0 AND fr.status = 1) ) 
                                                        AND (TRIM(@search) LIKE ''OR rulename LIKE CONCAT('%', @search, '%')) ORDER BY rulename ASC;">
                                <SelectParameters>
                                    <asp:ControlParameter Name="search" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                                    <asp:ControlParameter Name="disabled" ControlID="cbShowDisabledRules" ConvertEmptyStringToNull="false" DefaultValue="0" Type="Int32" />
                                </SelectParameters>
                            </asp:SqlDataSource>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </section>  

    <style>
    .showrols label{
margin-bottom:0px;
}
    .showrols input{
margin-right:5px;
}


    </style>
</asp:Content>
