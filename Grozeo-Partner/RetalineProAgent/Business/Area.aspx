<%@ Page Language="C#" MasterPageFile="~/Business/BusinessMaster.master" Title="Area" AutoEventWireup="true" CodeBehind="Area.aspx.cs" Inherits="RetalineProAgent.Area" %>

<asp:Content ContentPlaceHolderID="cpNhead" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Area</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Area"></asp:Literal> 
                <%--<asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>--%> 
            </h6>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpNMainContent">
        <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                                    <div class="col-lg-4">
                                          <label class="form-control-label w-100 mb-1">Search: </label>
                                        <input type="text" style="display:none" />
                                        <input type="password" style="display:none" />
                                        <div class="d-flex">
                                            <asp:TextBox ID="txtSearch" runat="server" placeholder="Search by name & location" CssClass="p-1 form-control" autocomplete="off"></asp:TextBox>
                                        <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary btn-sm d-inline-block w-auto ml-2" style="height:33px; line-height: 23px;" runat="server">Search</asp:LinkButton>
                                        </div>
                                      </div>
                        

                        <%--<div class="col-sm-8">
                            <div class="float-right mt-4"><a href="#" type="button" class="btn btn-info pb-1 pt-1"><i class="icon ion-plus-circled mr-2"></i>Create Relationship Officer</a></div>
                        </div>--%>
                    </div>
            </div>
                <div class="card-body">
                    <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvArea" runat="server" CssClass="table table-bordered" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnRowDataBound="gvArea_RowDataBound" DataSourceID="SDSArea">
                                    <Columns>
                                        <asp:BoundField HeaderText="Name" DataField="areaName" SortExpression="areaName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Location" DataField="areaLocation" SortExpression="areaLocation" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Span" DataField="areaSpan" SortExpression="areaSpan" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Latitude" DataField="areaLatitude" SortExpression="areaLatitude" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Longitude" DataField="areaLongitude" SortExpression="areaLongitude" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Business Associate" DataField="baName" SortExpression="baName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <%--<asp:HyperLinkField runat="server" Text="Edit" HeaderStyle-BackColor="#DEE2E6" ItemStyle-BackColor="White" NavigateUrl="~/Business/ContactSettings" DataNavigateUrlFields="id" DataNavigateUrlFormatString="~/Business/ContactSettings?id={0}" />--%>
                                        <asp:TemplateField HeaderStyle-BackColor="#DEE2E6"><ItemTemplate><asp:LinkButton ID="lbSelectArea" runat="server" Text="Select" OnClick="lbSelectArea_Click" areaid='<%# Eval("areaid") %>'></asp:LinkButton></ItemTemplate></asp:TemplateField>
                                    </Columns>
                                    <EmptyDataTemplate>
                                        No area linked to your account.
                                    </EmptyDataTemplate>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSArea" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT area_entries.id as areaid, ba.id,baName,areaName,areaLocation,areaSpan,areaLatitude,areaLongitude,areaBusinessAssociate FROM area_entries
                                    INNER JOIN business_associate ba ON ba.id = areaBusinessAssociate WHERE (@isAdmin = 1 or ba.id=@baId) AND (trim(ifnull(@searchKey, '')) like '' or areaName like CONCAT('%', @searchKey, '%') or areaLocation like CONCAT('%', @searchKey, '%')) ORDER BY id DESC" 
                                    OnSelecting="SDSArea_Selecting">
                            <SelectParameters>
                                <asp:Parameter Name="baId" DefaultValue="0" />
                                <asp:Parameter Name="isAdmin" DefaultValue="0" />
                                <asp:ControlParameter Name="searchKey" ControlID="txtSearch" ConvertEmptyStringToNull="false" />
                            </SelectParameters>
                        </asp:SqlDataSource>
                    </div>
                </div>
              </div>
            </div>
          </div>
    <script type="text/javascript">
        

        $("input[data-bootstrap-switch], tb[data-bootstrap-switch] input[type=checkbox]").each(function () {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('tb[data-bootstrap-switch] input[type=checkbox]').on('switchChange.bootstrapSwitch', function (e, state) {
            $(this).prop('checked', !state);
            $(this).trigger('click');
        });

    </script>
</asp:Content>
