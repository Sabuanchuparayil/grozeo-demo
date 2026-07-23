<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/AgentMaster.Master" CodeBehind="DomainConfiguration.aspx.cs" Inherits="RetalineProAgent.DomainConfiguration" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/storeconfig">Settings</a></li>
    <li class="breadcrumb-item active" aria-current="page">Domain Settings</li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle">Domain Settings</h6></asp:Content>


<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

            <% if (this.CurrentUser.PackageId < 2)
                { %>

    <div class="section-wrapper mg-t-20">
          <label class="section-title">Custom Domain</label>
          <p class="mg-b-20 mg-sm-b-40">Upgrade your package for assign custom domain</p>

          <div class="form-card-wrapper">
            <div class="card wd-350 shadow-base">
              <div class="card-body pd-x-20 pd-xs-40">
                <h5 class="tx-xs-24 tx-normal tx-gray-900 mg-b-15">Upgrade package</h5>
                <p class="mg-b-30 tx-14">Your current package does not allow assign custom domain to store. Please upgrade your package for assign custom domain and many more advanced features.</p>

                <div class="form-group">
                </div><!-- form-group -->
                <a href="" data-toggle="modal" data-target="#modalupgrade" class="btn btn-primary btn-block" title="Configure custom domain">Upgrade</a>
              </div><!-- card-body -->
            </div><!-- card -->
          </div><!-- form-card-wrapper -->
        </div><!-- section-wrapper -->
    
            <% }
                else
                { %>

        <div class="section-wrapper mg-t-20">
          <label class="section-title">Custom domain</label>
          <p class="mg-b-20 mg-sm-b-40">You can map your custom domain with your store in grozeo. Please complete the prerequisite (A and TXT records) and submit a support ticket with your domain.</p>
            <h6>Prerequisites</h6>
          <div class="form-layout form-layout-6">
            <div class="row no-gutters">
              <div class="col-5 col-sm-4">
                IP (assign 'A' record):
              </div><!-- col-4 -->
              <div class="col-7 col-sm-8">
                20.192.98.160
              </div><!-- col-8 -->
            </div><!-- row -->
            <div class="row no-gutters">
              <div class="col-5 col-sm-4">
                TXT value:
              </div><!-- col-4 -->
              <div class="col-7 col-sm-8">
                55735E80C7B315247834774D416EB688D392E43290FD7A93F151AA59C5D2CB8E
              </div><!-- col-8 -->
            </div><!-- row -->
          </div><!-- form-layout -->

            <br />
            <h6>Create Ticket</h6>
          <div class="form-layout form-layout-6">
            <div class="row no-gutters">
              <div class="col-5 col-sm-4">
                Domain:
              </div><!-- col-4 -->
              <div class="col-7 col-sm-8">
                <input class="form-control" type="text" name="domain" placeholder="Enter your domain">
              </div><!-- col-8 -->
            </div><!-- row -->
            <div class="row no-gutters">
              <div class="col-5 col-sm-4">
              </div><!-- col-4 -->
              <div class="col-7 col-sm-8">
                <button class="btn btn-primary btn-block">Submit</button>
              </div><!-- col-8 -->
            </div><!-- row -->
          </div><!-- form-layout -->
        </div><!-- section-wrapper -->

                  <% } %>

</asp:Content>