<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Categories.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Navigations.Categories" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/Navigations/Products"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a></li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Categories</h6>
        <p class="mb-0"></p>
    </div>
    </asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm menucard">
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                <a href="/Tenant/StoreCategory" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-list-ol mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Store Categories</h5>
                        <p class="card-text mg-b-8 tx-11">Streamline product categorization for improved customer navigation.</p>
                    </div>

                </a>
            </div>
            <!--col-lg-->
            <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
                <a href="/Tenant/PrivateCategory" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-list-radio mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Private Categories</h5>
                        <p class="card-text mg-b-8 tx-11">Create private categories for personalized customer experience.</p>
                    </div>
                </a>
            </div>
            <!--col-lg-->
        </div><!--row-->
</asp:Content>