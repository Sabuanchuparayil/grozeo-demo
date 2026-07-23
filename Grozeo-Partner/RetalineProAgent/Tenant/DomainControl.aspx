<%@ Page Language="C#" MasterPageFile="~/Tenant/TenantMaster.master" Async="true" Title="Domain Control" AutoEventWireup="true" CodeBehind="DomainControl.aspx.cs" Inherits="RetalineProAgent.DomainControl" %>

<asp:Content ContentPlaceHolderID="head" runat="server">

    <script src="/Content/custom/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
      <link rel="stylesheet" href="/Content/custom/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link href="/Content/lib/jquery-toggles/css/toggles-full.css" rel="stylesheet">
    <script src="/Content/lib/jquery-toggles/js/toggles.min.js"></script>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <h6 class="slim-pagetitle"><asp:Literal ID="ltrTitle1" runat="server" Text="Domain Controls"></asp:Literal> 
                <asp:Literal ID="ltrBranchName" runat="server"></asp:Literal>
            </h6>
    <p class="mb-0">Total Domain Flexibility</p>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Navigations/Delivery"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">
    
<%--        <div class="row" >
            <div class="col-12">

                <div class="card">
                    <div class="card-body">
                      <div class="d-inline-block w-100 tx-center py-4">
                          <asp:Image runat="server" ID="imgId" CssClass="img-fluid" style="opacity: 0.9; max-width:450px; width: 100%;" ImageUrl="/content/images/nodata.png"/>
                      </div>
                    </div><!--card body-->
              </div>

            </div>
          
          </div>--%>

                <% if (this.CurrentUser.PackageId < 2)
                { %>

    <div class="section-wrapper mg-t-20">
          <label class="section-title">Custom Domain</label>
          <p class="mg-b-20 mg-sm-b-40">Upgrade your package for assigning custom domain</p>

          <div class="form-card-wrapper">
            <div class="card wd-350 shadow-base">
              <div class="card-body pd-x-20 pd-xs-40">
                <h5 class="tx-xs-24 tx-normal tx-gray-900 mg-b-15">Upgrade package</h5>
                <p class="mg-b-30 tx-14">Your current package does not allow you to assign a custom domain to the store. Please upgrade your package for assigning a custom domain and many more advanced features.</p>

                <div class="form-group">
                </div><!-- form-group -->
                <a href="" data-toggle="modal" data-target="#modalupgrade" class="btn btn-primary btn-block" title="Configure custom domain">Upgrade</a>
              </div><!-- card-body -->
            </div><!-- card -->
          </div><!-- form-card-wrapper -->
        </div><!-- section-wrapper -->

    <% }
                else
                {
    %>
    <asp:PlaceHolder ID="plcValidateDomain" runat="server">
        <div class="card">
            <div class="card-body shadow_top p-3">
                <p class="mg-b-20 mg-sm-b-40">You can map your custom domain with your store in grozeo. Please complete the prerequisite (A and TXT records) and submit a support ticket with your domain.</p>
                <h6>Add custom domain</h6>
                <div class="form-layout form-layout-6">
                    <div class="row no-gutters">
                        <div class="col-5 col-sm-4">
                            Domain:
                        </div>
                        <!-- col-4 -->
                        <div class="col-7 col-sm-8">
                            <asp:TextBox ID="txtDomain" CssClass="form-control" runat="server" placeholder="Enter your domain" ValidationGroup="CreateDomain"></asp:TextBox>
                            <asp:RequiredFieldValidator runat="server" ControlToValidate="txtDomain" ErrorMessage="Domain name is required" Text="*" ForeColor="Red" ValidationGroup="CreateDomain" Display="Dynamic"></asp:RequiredFieldValidator>
                            <asp:RegularExpressionValidator runat="server" ControlToValidate="txtDomain" ErrorMessage="Invalid domain" ValidationExpression="^((?!-)[A-Za-z0-9-]{1,63}(?<!-)\.)+[A-Za-z]{2,6}$" ForeColor="Red" ValidationGroup="CreateDomain" Display="Dynamic"></asp:RegularExpressionValidator>
                            <asp:CustomValidator runat="server" ControlToValidate="txtDomain" ErrorMessage="Invalid domain name" OnServerValidate="Unnamed_ServerValidate" ValidationGroup="CreateDomain" Text="*" ForeColor="Red" Display="Dynamic"></asp:CustomValidator>
                            <%--<input class="form-control" type="text" name="domain" placeholder="Enter your domain">--%>
                        </div>
                        <!-- col-8 -->
                    </div>
                    <!-- row -->
                    <div class="row no-gutters">
                        <div class="col-5 col-sm-4">
                        </div>
                        <!-- col-4 -->
                        <div class="col-7 col-sm-8">
                            <div class="col-3 col-sm-3">
                                <asp:Button ID="btnAdd" runat="server" CssClass="btn btn-primary btn-block" OnClick="btnAdd_Click" ValidationGroup="CreateDomain" Text="Submit" />
                            </div>
                            <%--<button class="btn btn-primary btn-block">Submit</button>--%>
                        </div>
                        <!-- col-8 -->
                    </div>
                    <!-- row -->
                </div>
                <!-- form-layout -->
            </div>
        </div>
    </asp:PlaceHolder>
    <asp:PlaceHolder ID="plcDomainProgress" runat="server">
        <asp:Label ID="lblInprogressMsg" runat="server" Text=""></asp:Label>
        <div class="card">
            <div class="card-body shadow_top p-3">
                <div class="row row-sm">
                    <div class="col-12 mb-3">
                        <div class="domain_dtls border d-flex row row-sm m-0">
                            <div class="col-12 col-md-4 p-3 domain_dtls_name">
                                <span class="w-100 d-inline-block tx-dark">Domain name</span>
                                <span id="domainDisplay" runat="server"><strong class="tx-dark">
                                    <asp:Literal ID="ltrDomainName" runat="server"></asp:Literal></strong></span>
                                    <asp:TextBox ID="txtDomainEdit" CssClass="form-control" runat="server" Visible="false"></asp:TextBox>
                            </div>
                            <div class="col-12 col-md-4 p-3 domain_dtls_status">
                                <span class="w-100 d-inline-block tx-dark">Status</span>
                                <span><strong class="tx-dark">
                                    <asp:Literal ID="ltrDomainStatus" runat="server"></asp:Literal></strong></span>
                            </div>
                            <asp:PlaceHolder ID="plcProgressCount" runat="server">
                                <asp:HiddenField ID="hidExpiryDate" runat="server" />
                                <asp:HiddenField ID="hidCurDate" runat="server" />
                                <div class="col-12 col-md-4 p-3 domain_dtls_expires">
                                    <span class="w-100 d-inline-block tx-dark">Expires in</span>
                                    <span><strong class="tx-warning">
                                        <label id="lblCountdown" class="text-warning"></label>
                                    </strong></span>
                                </div>
                            </asp:PlaceHolder>
                        </div>
                    </div>
                    <asp:PlaceHolder ID="plcDomainSettings" runat="server">
                        <div class="col-12 mb-2">
                            <div class="dnsupdte w-100 d-flex flex-wrap align-items-center mb-3">
                                <h6 class="tx-dark mb-2 mb-sm-0 mr-3 ">Prerequisites (DNS updates required)</h6>
                                <div class="d-flex align-items-center dnsupdte_btn">
                                    <asp:LinkButton ID="lbtnDownload" runat="server" OnClick="lbtnDownload_Click" CssClass="btn btn-outline-primary">Download <i class="fa fa-download mb-0" aria-hidden="true"></i></asp:LinkButton>
                                    <a href="javascript:void(0)" data-toggle="modal" data-target="#popupsendmail" class="btn btn-outline-secondary">Email <i class="fa fa-envelope mb-0" aria-hidden="true"></i></a>
                                </div>
                            </div>
                            <p>Please make sure to update your domain DNS with the following records (A and TXT records) for verification. You may have to get support from your tech guy who manages the domain unless it is by your self. You can contact the support team for more information on how to assign the DNS records in your domain, if it is requires.</p>
                        </div>

                        <div class="col-12">
                            <p class="mb-1">
                                <label id="lblVerify" runat="server">
                                    <br />
                                    <br />
                                    Please click on the verify button once the update at your DNS is completed. This page will be expiring in 48 hourse. You have to create the domain setting again unless the mapping completed before expiry</label></p>
                            <div class="table-responsive">
                                <table class="table table-bordered mg-b-0 gridview_table">
                                    <tr>
                                        <th width="100px">Type</th>
                                        <th width="10%">Host</th>
                                        <th>Value</th>
                                    </tr>
                                    <tr>
                                        <td>A</td>
                                        <td>@</td>
                                        <td>
                                            <asp:Literal ID="ltrIP" runat="server" Text=""></asp:Literal></td>
                                    </tr>
                                    <tr>
                                        <td>TXT value</td>
                                        <td>asuid</td>
                                        <td>
                                            <p class="m-0" style="word-break: break-all;">
                                                <asp:Literal ID="ltrTXTRecord" runat="server" Text=""></asp:Literal></p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center my-2 w-100">
                            <label id="lblMappingProgress" runat="server" class="tx-waring" visible="false">Domain mapping is inprogress. This may take upto 48 hours to complete.</label>
                        </div>
                        </asp:PlaceHolder>     
                        <div class="col-12 text-center mt-3">
                            <asp:Button ID="btnValidate" runat="server" CssClass="btn btn-primary px-3 mr-2" OnClientClick="return confirm('Are you sure you have added the DNS (IP and TXT) entries at your domain manager?')" OnClick="btnValidate_Click" Text="Verify" />
                           <asp:LinkButton ID="btndomainedit" runat="server" CssClass="btn btn-primary mr-2" Text="Change Domain" OnClick="btndomainedit_Click"></asp:LinkButton>
                          <asp:LinkButton ID="btndomainsave" runat="server" CssClass="btn btn-primary" Text="Save" OnClick="btndomainsave_Click" Visible="false"></asp:LinkButton>
                        </div>                                 
                </div>
                <div class="form-layout form-layout-6">
                    <asp:PlaceHolder ID="plcSuccess" Visible="false" runat="server">
                        <div class="row no-gutters">
                            <div class="col-5 col-sm-4">
                                Status:
                            </div>
                            <!-- col-4 -->
                            <asp:Panel ID="pnlCompleted" runat="server" CssClass="col-7 col-sm-8">Domain Successfully connected.</asp:Panel>
                            <asp:Panel ID="pnlSSLPending" runat="server" CssClass="col-7 col-sm-8">Partially Completed. SSL is pending</asp:Panel>
                            <div class="col-12 text-center mt-3">
                                <asp:LinkButton ID="btnnewdomain" runat="server" CssClass="btn btn-primary mr-2" Text="Change Domain" OnClick="btndomainedit_Click"></asp:LinkButton>
                                <asp:LinkButton ID="btnsavenewdomain" runat="server" CssClass="btn btn-primary" Text="Save" OnClick="btndomainsave_Click" Visible="false"></asp:LinkButton>
                            </div>
                        </div>
                        <!-- row -->
                    </asp:PlaceHolder>
                </div>     <!-- form-layout -->
            </div>
        </div>       
    </asp:PlaceHolder>
    <asp:Label ID="lblStatus" runat="server" CssClass="mg-b-20 mg-sm-b-40"></asp:Label>
    <br />
    <div id="popupsendmail" class="modal fade">
        <div class="modal-dialog modal-dialog-vertical-center" role="document">
            <div class="modal-content bd-0 tx-14">
                <div class="modal-body">
                    <div class="section-wrapper p-0 border-0">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <div class="row row-sm">
                            <div class="col-12">
                                <h6 class="mb-2 tx-dark">Send DNS records to email</h6>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="form-control-label">Email:</label>
                                    <asp:TextBox ID="txtEmail" TextMode="Email" runat="server" CssClass="form-control" placeholder="Destination email"></asp:TextBox>
                                </div>
                            </div>
                        </div>
                        <!--row-->
                    </div>
                    <!--section-wrapper-->
                </div>
                <!--modal-body-->
                <div class="modal-footer">
                    <span class="error_msg_wrap" id="sendEmailError">
                        <asp:Literal ID="ltrSendEmailResult" runat="server"></asp:Literal>
                        <asp:RequiredFieldValidator runat="server" SetFocusOnError="true" ErrorMessage="Please input email" ControlToValidate="txtEmail" Display="Dynamic" ValidationGroup="SendEmail"></asp:RequiredFieldValidator>
                    </span>
                    <asp:LinkButton runat="server" Text="Save" OnClick="lbtnEmail_Click" CssClass="btn btn-primary btn-drk-green" ValidationGroup="SendEmail"></asp:LinkButton>
                    <%--<asp:Button runat="server" Text="Save" ID="btnAddBrand" OnClick="btnAddBrand_Click" CssClass="btn btn-primary btn-drk-green" />--%>
                    <%--<button type="button" class="btn btn-primary btn-drk-green">Save</button>--%>
                    <button type="button" class="btn btn-secondary btn-drk-green" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
        <!-- modal-dialog -->
    </div>
    <!-- modal -->


    <%if (plcProgressCount.Visible)
        { %>
    <script type="text/javascript">
        function startCountdown(expiryDateUTC, customNow) {
            // Convert UTC date to local date
            var expiryDate = new Date(expiryDateUTC);

            // Check if the date is valid
            if (isNaN(expiryDate.getTime())) {
                $('#countdown').html("Invalid Date");
                return;
            }

            var countdownElement = $('#lblCountdown');

            var interval = setInterval(function () {
                // Use customNow if provided, otherwise use the actual current date
                var now = customNow ? new Date(customNow).getTime() : new Date().getTime();

                // Increment customNow if it is used
                if (customNow) {
                    customNow += 1000; // Increment by 1 second
                }

                var distance = expiryDate - now;

                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                var dayText = days === 1 ? "day" : "days";
                var hourText = hours === 1 ? "hour" : "hours";
                var minuteText = minutes === 1 ? "minute" : "minutes";
                var secondText = seconds === 1 ? "second" : "seconds";

                countdownElement.html(days + " " + dayText + " " +
                    hours + " " + hourText + " " +
                    minutes + " " + minuteText + " " +
                    seconds + " " + secondText);

                if (distance < 0) {
                    clearInterval(interval);
                    countdownElement.html("EXPIRED");
                    window.location.reload();
                }
            }, 1000);
        }

        $(document).ready(function () {
            var expiryDateUTC = $('#<%= hidExpiryDate.ClientID %>').val();
            var customNow = $('#<%= hidCurDate.ClientID %>').val(); // Get custom now value if any
            startCountdown(expiryDateUTC, customNow ? new Date(customNow).getTime() : null);
        });
    </script>


    <%} %>


                  <% } %>


      <style>
            .domain_dtls {
              background: #f4f4f4;
              border-radius: 8px ;
            }
            .domain_dtls > div{
              border-right: 1px solid #dee2e6;
            }
            .domain_dtls > div:last-child{
              border:0;
            }
            .dnsupdte_btn {
              gap:20px
            }
            @media (max-width: 767px) {
              .domain_dtls > div{
                border-bottom: 1px solid #dee2e6;
              }
            }
          </style>
</asp:Content>
