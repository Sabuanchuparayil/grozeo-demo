<%@ Page Language="C#" MasterPageFile="~/Business/BusinessMaster.master" Title="Retailer Leads" AutoEventWireup="true" CodeBehind="RetailerLeads.aspx.cs" Inherits="RetalineProAgent.RetailerLeads" %>

<asp:Content ContentPlaceHolderID="cpNhead" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/Business/BusinessNavigations/BusinessCRM">CRM</a></li>
    <li class="breadcrumb-item active" aria-current="page">Retailer Leads</li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpNTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Retailer Leads"></asp:Literal> 
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
                                            <asp:TextBox ID="txtSearch" runat="server" placeholder="Search by name, number & state" CssClass="p-1 form-control" autocomplete="off"></asp:TextBox>
                                        <asp:LinkButton ID="lbtnSearch" CssClass="btn btn-block btn-primary btn-sm d-inline-block w-auto ml-2" style="height:33px; line-height: 23px;" runat="server">Search</asp:LinkButton>
                                        </div>
                                      </div>
                        

                        <div class="col-sm-8">
                            <div class="float-right mt-4"><a href="/Business/RLeadSettings" type="button" class="btn btn-info pb-1 pt-1"><i class="icon ion-plus-circled mr-2"></i>Create Retailer Leads</a></div>
                        </div>
                    </div>
            </div>
                <div class="card-body">
                    <div class="table-responsive">
                                <asp:GridView AutoGenerateColumns="false" ID="gvRetailerLead" runat="server" CssClass="table table-bordered" GridLines="None" BorderColor="#ECECEC"
                                    AllowPaging="true" AllowSorting="true" ShowFooter="false" PagerSettings-Visible="true" PageSize="10" OnDataBound="gvRetailerLead_DataBound" DataSourceID="SDSRetailerLeads">
                                    <Columns>
                                        <asp:BoundField HeaderText="Store Name" DataField="crle_orgName" SortExpression="crle_orgName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Contact Name" DataField="crle_indContactperson" SortExpression="crle_indContactperson" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Contact Number" DataField="crle_indMobile" SortExpression="crle_indMobile" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Location" DataField="crle_location" SortExpression="crle_location" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Created By" DataField="baName" SortExpression="baName" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <asp:BoundField HeaderText="Status" DataField="leadStatus" SortExpression="leadStatus" HeaderStyle-BackColor="#DEE2E6" HeaderStyle-ForeColor="Black" ItemStyle-BackColor="White"/>
                                        <%--<asp:HyperLinkField runat="server" Text="Edit" HeaderStyle-BackColor="#DEE2E6" ItemStyle-BackColor="White" NavigateUrl="~/Business/RLeadSettings" DataNavigateUrlFields="id" DataNavigateUrlFormatString="~/Business/RLeadSettings?id={0}" />--%>
                                    </Columns>
                                    <EmptyDataTemplate>
                                        No retailer leads.
                                    </EmptyDataTemplate>
                                </asp:GridView>

                                <asp:SqlDataSource runat="server" ID="SDSRetailerLeads" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                 SelectCommand = "SELECT cl.id, ba.baName, crle_orgName,crle_location, crle_indContactperson, crle_orgContactNo, crle_CreatedBy,
                                    cl.crmuId,IF((invitationSent = 0),(SELECT crmu_name FROM finascop_crm_status cs WHERE cs.crmu_id=cl.crmuId),'Invitation Sent') AS leadStatus, crle_gplace, crle_indMobile 
                                    FROM  finascop_crm_lead cl
                                    LEFT JOIN finascop_crm_prospect ON leadId=cl.id
                                    INNER JOIN business_associate ba ON ba.id=cl.baId
                                    WHERE cl.crmuId NOT IN (3,7) AND crle_type=1 AND ((@areaId > 0 and cl.areaId = @areaId) or cl.baId=@baId)
                                    AND (trim(ifnull(@searchKey, '')) like '' or crle_orgName like CONCAT('%', @searchKey, '%') or crle_gplace like CONCAT('%', @searchKey, '%') or crle_indMobile like CONCAT('%', @searchKey, '%')) ORDER BY id DESC" OnSelecting="SDSRetailerLeads_Selecting">
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
