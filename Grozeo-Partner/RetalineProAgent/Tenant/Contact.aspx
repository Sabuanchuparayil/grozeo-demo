<%@ Page Title="Contact" Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" AutoEventWireup="true" CodeBehind="Contact.aspx.cs" Inherits="RetalineProAgent.Contact" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Support">Support</a></li>
    <li class="breadcrumb-item active" aria-current="page">Contact Center</li>--%>
    <a href="/Navigations/Support"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle m-0">Customer Center</h6>
        <p class="mb-0">Empower Customer Relationships</p>
    </div>
</asp:Content>
<asp:Content ID="BodyContent" ContentPlaceHolderID="cpMainContent" runat="server">

          <!-- Default box -->
      <div class="card">
          <div class="row no-gutters">
              <div class="col-lg-6 bg-primary">
                <div class="pd-40">
                  <h1 class="tx-white mg-b-20">Connect with us 
                      <%--<%= System.Configuration.ConfigurationManager.AppSettings.Get("ThemeDefault") %>--%>
                  </h1>
                  <p class="tx-white op-7 mg-b-30">We work with clients large and small across a variety of industries, and we use all types of media to get your name out there in the most effective way for you. We believe that analysing your company and your customers is critical to efficiently reacting to your promotional demands, and we will work with you to fully understand your business in order to get the most amount of publicity possible so that you can see a return on your investment.</p>
                  <p class="tx-white">
                    <span class="tx-uppercase tx-medium d-block mg-b-15">Our Address:</span>
                    <span class="op-7">
                        <asp:Literal ID="ltrAddress" runat="server" Text="Grozeo International Private Limited, The Atomic Near Technopark Phase 1, Kazhakootam, Trivandrum, Kerala-695581"></asp:Literal>
                         <br />
                        <asp:Literal ID="ltrPhone" runat="server" Text="Mob: +917012455015, Tel: +914713551729, "></asp:Literal>
                        <asp:Literal ID="ltrEmail" runat="server" Text=""></asp:Literal>
<%--Email: <%= System.Configuration.ConfigurationManager.AppSettings.Get("FromEmail") %>--%>
                        <br /><br />
                        <asp:Literal ID="ltrUKAddr" runat="server" Text="69-71 CHARING CROSS ROAD LONDON ENGLAND WC2H 0NE"></asp:Literal>

                    </span>
                  </p>
                </div>
              </div><!-- col-6 -->
              <div class="col-lg-6 bg-white">
                <div class="pd-y-30 pd-xl-x-30">
                  <%--<button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>--%>
                  <div class="pd-x-30 pd-y-10">
                    <h3 class="tx-gray-800 tx-normal mg-b-5">Message Us!</h3>
                    <p>Send your message / comment to us</p>
                    <br>
            <div class="form-group">
              <label for="inputName">Name <span class="tx-danger">*</span></label>
                <input type="text" style="display:none" />
                <input type="password" style="display:none" />
              <input type="text" id="inputName" runat="server" class="form-control" required autocomplete="off" />
            </div>
            <div class="form-group">
              <label for="inputEmail">E-Mail <span class="tx-danger">*</span></label>
                <input type="text" style="display:none" />
                <input type="password" style="display:none" />
              <input type="email" runat="server" id="inputEmail" class="form-control" required autocomplete="off"  />
            </div>
            <div class="form-group">
              <label for="inputSubject">Phone <span class="tx-danger">*</span></label>
                <input type="text" style="display:none" />
                <input type="password" style="display:none" />
              <input type="text" id="inputPhone" runat="server" class="form-control" autocomplete="off"  required oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" />
            </div>
            <div class="form-group">
              <label for="inputMessage">Message <span class="tx-danger">*</span></label>
                <input type="text" style="display:none" />
                <input type="password" style="display:none" />
              <textarea id="inputMessage" class="form-control" runat="server" rows="4" required autocomplete="off" ></textarea>
            </div>
            <asp:Button ID="btnSubmit" CssClass="btn btn-primary btn-block" runat="server" Text="Submit" OnClick="btnSubmit_Click" />

                  </div>
                </div><!-- pd-20 -->
              </div><!-- col-6 -->
            </div><!-- row -->
        
      </div>


</asp:Content>
