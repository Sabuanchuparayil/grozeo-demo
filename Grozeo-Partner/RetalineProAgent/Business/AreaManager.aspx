<%@ Page Language="C#" MasterPageFile="~/Business/BusinessMaster.master" Title="Area Manager" AutoEventWireup="true" CodeBehind="AreaManager.aspx.cs" Inherits="RetalineProAgent.AreaManager" %>

<asp:Content ContentPlaceHolderID="cpNhead" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Business/BusinessNavigations/Resources">Resources</a></li>
    <li class="breadcrumb-item active" aria-current="page">Area Manager</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Area Manager"></asp:Literal> 
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
                                            <asp:TextBox ID="txtSearch" runat="server" placeholder="Search by name & number" CssClass="p-1 form-control" autocomplete="off"></asp:TextBox>
                                        <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary btn-sm d-inline-block w-auto ml-2" style="height:33px; line-height: 23px;" runat="server">Search</asp:LinkButton>
                                        </div>
                                      </div>
                        

                        <div class="col-sm-8">
                            <div class="float-right mt-4"><a href="/Business/AMSettings" type="button" class="btn btn-primary pb-1 pt-1"><i class="icon ion-plus-circled mr-2"></i>Create Area Manager</a></div>
                        </div>
                    </div>
            </div>
                <div class="card-body">
                    <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvRelationshipOfficer" runat="server" CssClass="table table-bordered" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvRelationshipOfficer_DataBound" DataSourceID="SDSRelationshipOfficer">
                                    <Columns>
                                        <asp:BoundField HeaderText="Officer Name" DataField="roName" SortExpression="roName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Contact Number" DataField="roMobile" SortExpression="roMobile" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Address" DataField="roAddress" SortExpression="roAddress" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:HyperLinkField runat="server" Text="Edit" HeaderStyle-BackColor="#DEE2E6" ItemStyle-BackColor="White" NavigateUrl="~/Business/AMSettings" DataNavigateUrlFields="id" DataNavigateUrlFormatString="~/Business/AMSettings?id={0}" />
                                    </Columns>
                                    <EmptyDataTemplate>
                                        No area manager created.
                                    </EmptyDataTemplate>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSRelationshipOfficer" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT id, roName, roMobile, roAddress FROM relationship_officer WHERE type=2 AND ((@areaId > 0 and roArea = @areaId) or roBusAssociate=@baId)
                                    AND (trim(ifnull(@searchKey, '')) like '' or roName like CONCAT('%', @searchKey, '%') or roMobile like CONCAT('%', @searchKey, '%')) ORDER BY id DESC" OnSelecting="SDSRelationshipOfficer_Selecting">
                            <SelectParameters>
                                <asp:Parameter Name="baId" DefaultValue="0" />
                                <asp:Parameter Name="areaId" DefaultValue="0" />
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
