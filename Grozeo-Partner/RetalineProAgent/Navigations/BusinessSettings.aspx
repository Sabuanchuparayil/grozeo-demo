<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="BusinessSettings.aspx.cs" Inherits="RetalineProAgent.Navigations.BusinessSettings" %>


<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Navigations/SettingsMenu"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Business Settings</h6>
        <p class="mb-0">Versatile configuration and management</p>
    </div>
</asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

        <div class="row row-sm menucard">

         <div class="col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/ManageBusinessSettings" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-chart-scatter mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800 ">Configuration</h5>
                <p class="card-text mg-b-8 tx-11">Set up and customise business preferences and operational parameters to align the platform with requirements.</p>
              </div>
            </a>
          </div><!--col-lg-->

            <div class="col-lg-4 mb-3 mb-lg-4">
            <a href="/Tenant/Store/GST" class="card h-100 p-4">
              <div class="card-body p-0 tx-left position-relative">
                  <i class="fa-thin fa-money-check-dollar-pen mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                <h5 class="card-title tx-medium m-0 tx-14 tx-gray-800 "><%= (ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "GSTIN" : "VAT") %></h5>
                <p class="card-text mg-b-8 tx-11">Add and manage GST Numbers, including verification, compliance, and updates for tax and invoicing purposes.</p>
              </div>
            </a>
          </div><!--col-lg-->


            

            <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                {  %>
              <div class="col-lg-4 mb-3 mb-lg-4">
                  <a href="#" class="card h-100 p-4">
                      <div class="card-body p-0 tx-left position-relative">
                          <i class="fa-thin fa-money-check-dollar-pen mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                          <h5 class="card-title tx-medium m-0 tx-14 tx-gray-800 ">PAN</h5>
                          <p class="card-text mg-b-8 tx-11">Add and manage Permanent Account Numbers, including verification and compliance for taxation and legal purposes.</p>
                      </div>
                  </a>
              </div>
            <!--col-lg-->

            <div class="col-lg-4 mb-3 mb-lg-4">
                <a href="/Tenant/Store/FSSAI" class="card h-100 p-4">
                    <div class="card-body p-0 tx-left position-relative">
                        <i class="fa-thin fa-money-check-dollar-pen mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true"></i>
                        <h5 class="card-title tx-medium m-0 tx-14 tx-gray-800 ">FSSAI</h5>
                        <p class="card-text mg-b-8 tx-11">Add and manage FSSAI license details and verification status for food business compliance.</p>
                    </div>
                </a>
            </div>
            <!--col-lg-->

            <% } %>
        </div><!--row-->

</asp:Content>