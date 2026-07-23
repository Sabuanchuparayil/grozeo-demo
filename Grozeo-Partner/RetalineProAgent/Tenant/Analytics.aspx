<%@ Page Language="C#" AutoEventWireup="true" Title="Analytics" MasterPageFile="~/Tenant/TenantMaster.master" Async="true"  CodeBehind="Analytics.aspx.cs" Inherits="RetalineProAgent.Analytics" %>


<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
            <asp:Literal ID="ltrTitle1" runat="server" Text="Analytics"></asp:Literal>
        </h6>
        <%--<p class="mb-0">Organize and prepare orders efficiently for shipment</p>--%>
    </div>
    <style>
    table.table table, table.table table td{
        border:0px!important;
        padding: 5px;
    }      
</style>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card">
        <div class="card-body p-3">
          <iframe style="height:100vh;" runat="server" visible="false" id="ifmAnalytics" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="100%"></iframe>
        </div><!-- card-body -->
    </div><!-- card -->

</asp:Content>



