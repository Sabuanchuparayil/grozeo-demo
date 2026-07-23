<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="PerformanceReports.aspx.cs" Inherits="RetalineProAgent.Tenant.Finance.PerformanceReports" %>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/accounts">Accounts & MIS</a></li>
    <li class="breadcrumb-item active" aria-current="page">Performance Reports</li>--%>
    <a href="/Navigations/Accounts"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
        <asp:Literal ID="ltrTitle1" runat="server" Text="Daily Sales Report">Performance Reports</asp:Literal>
        <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>
    </h6>
        <p class="mb-0">Measure Business Performance</p>
    </div>
    
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="col-12">

                <div class="card">
                    <div class="card-body">
                      <div class="d-inline-block w-100 tx-center py-4">
                          <asp:Image runat="server" ID="imgId" CssClass="img-fluid" style="opacity: 0.9; max-width:450px; width: 100%;" ImageUrl="/content/images/nodata.png"/>
                      </div>
                    </div><!--card body-->
              </div>

            </div>
</asp:Content>
