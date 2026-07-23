<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" AutoEventWireup="true" Title="Marketing" CodeBehind="Marketing.aspx.cs" Inherits="RetalineProAgent.Navigations.Marketing" %>


<%--<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Accounts & MIS</li>
</asp:Content>--%>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Marketing</h6>
        <p class="mb-0"></p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <div class="row row-sm menucard">

        <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Appearance/Graphics" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                    <i class="fa-thin fa-pen-nib mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                    <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Graphics</h5>
                    <p class="card-text mg-b-8 tx-11">Boost your online presence with customized creatives in just a few clicks.</p>
                </div>
            </a>
        </div>
        <!--col-lg-->

        <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/MarketingTools" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                    <i class="fa-thin fa-chart-mixed-up-circle-dollar mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                    <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Marketing Tools</h5>
                    <p class="card-text mg-b-8 tx-11">Manage promotional features for campaigns and ads with insights on performance and engagement.</p>
                </div>
            </a>
        </div>
        <!--col-lg-->

        <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Analytics" class="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                    <i class="fa-thin fa-chart-pie mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                    <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Analytics</h5>
                    <p class="card-text mg-b-8 tx-11">Access customer visit insights and performance metrics including live and historical visits.</p>
                </div>
            </a>
        </div>
        <!--col-lg-->
    </div>
    <!--row-->

</asp:Content>
