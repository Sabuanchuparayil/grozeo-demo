<%@ Page Language="C#" AutoEventWireup="true" Title="Manage Invoice and Packing Details" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="MerchantControls.aspx.cs" Inherits="RetalineProAgent.Tenant.MerchantControls" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">
            <asp:Literal ID="ltrTitle" runat="server" Text="Manage Invoice and Packing Details"></asp:Literal></h6>
        <p class="mb-0">Manage Invoice and Packing Details for Express Orders</p>
    </div>
    <%--<script type="text/javascript">
        window.onload = function () {
            document.getElementById('<%= txtOrderId.ClientID %>').setAttribute('autocomplete', 'off');
        };
    </script>--%>
</asp:Content>


<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <asp:GridView ID="gvBranches" runat="server" AutoGenerateColumns="False" DataSourceID="SDSMerchantControl" 
                    CssClass="table table-bordered gridview_table" DataKeyNames="br_ID" AllowPaging="True" AllowSorting="True" PageSize="10">
                    <Columns>
                        <asp:BoundField DataField="br_Id" Visible="false" />
                        <asp:TemplateField HeaderText="Store Name">
                            <ItemTemplate>
                                <%# Eval("br_Name") %><br />
                                <small>
                                    <i runat="server" title="Courier Delivery" visible='<%# (Convert.ToString(Eval("br_courierDelivery"))  == "1" ? true : false) %>' class="fa fa-truck" aria-hidden="true"></i>
                                    <i runat="server" title="Express Delivery" visible='<%# (Convert.ToString(Eval("br_directDelivery"))  == "1" ? true : false) %>' class="fa fa-motorcycle" aria-hidden="true"></i>
                                     <br /><small>GST: <%# Eval("br_GST") %></small>
                               </small>
                            </ItemTemplate>
                        </asp:TemplateField>
                        <asp:TemplateField HeaderText="Store Address" ItemStyle-Width="200px" HeaderStyle-Width="200px">
                            <ItemTemplate>
                                <i class="fa fa-map-marker"></i>
                                </a>&nbsp;<%# Eval("br_Address") %>
                                <br />
                                <i class="ion-ios-timer tx-purple"></i>&nbsp;<small></small>
                                                              
                                <asp:Repeater ID="rptTiming" runat="server">
                                        <ItemTemplate><%# Eval("OnTime") %> - <%# Eval("OffTime") %></ItemTemplate>
                                        <SeparatorTemplate>, </SeparatorTemplate>
                                    </asp:Repeater>
                                    <asp:Literal ID="ltrNoTiming" Visible="false" runat="server" Text="All time"></asp:Literal>
                                </small>
                            </ItemTemplate>
                        </asp:TemplateField>

                        <asp:TemplateField HeaderText="Collect Invoice Details" ControlStyle-CssClass="collect_invoice_details">
                            <ItemTemplate>
                                <asp:CheckBox ID="chkInvoiceDetails" runat="server" Text="Collect Invoice Details of Express Deliverable Orders"
                                    Checked="true" OnCheckedChanged="chkInvoiceDetails_CheckedChanged" AutoPostBack="true" />
                            </ItemTemplate>
                        </asp:TemplateField>

                        <asp:TemplateField HeaderText="Collect Package Details" ControlStyle-CssClass="collect_package">
                            <ItemTemplate>
                                <asp:CheckBox ID="chkPackageDetails" runat="server" Text="Collect Package Details of Express Deliverable Orders"
                                    Checked="true" OnCheckedChanged="chkPackageDetails_CheckedChanged" AutoPostBack="true" />
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
                    <PagerSettings Mode="NumericFirstLast" PageButtonCount="5" />
                </asp:GridView>

                <asp:SqlDataSource ID="SDSMerchantControl" OnSelecting="SDSMerchantControl_Selecting" runat="server" ProviderName="MySql.Data.MySqlClient"
                    ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                    SelectCommand="SELECT br_ID,br_Name,br_City,br_Address,br_directDelivery,br_courierDelivery,br_GST,br_open_time,br_close_time FROM finascop_branch fb
                                   INNER JOIN finascop_branch_group fbg ON fb.br_storeGroup=fbg.store_group_id WHERE store_group_id=@storegroupId ORDER BY br_Name">
                    <SelectParameters>
                        <asp:Parameter Name="storegroupId"/>
                    </SelectParameters>
                </asp:SqlDataSource>

                
                
            </div>
        </div>
    </div>

    <style>
        .collect_invoice_details, .collect_package {
            display: flex;
            align-items: start;
        }
        .collect_invoice_details input, .collect_package input{
            margin-top:4px;
            margin-right:5px;
        }
        .collect_invoice_details label, .collect_package label{
            margin:0px;
        }
    </style>
</asp:Content>
