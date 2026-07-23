
<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Business/BusinessMaster.master" Title="Profile" CodeBehind="BusinessProfile.aspx.cs" Inherits="RetalineProAgent.BusinessProfile" %>
<%@ Register Src="~/Controls/ctrlLanguages.ascx" TagPrefix="uc1" TagName="ctrlLanguages" %>
<asp:Content ContentPlaceHolderID="cpNhead" runat="server">
    <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpNMainContent" runat="server">
    <%@ Register Src="~/Controls/ctrlSetPassword.ascx" TagPrefix="uc1" TagName="ctrlSetPassword" %>

    <div class="row row-sm">
        <div class="col-lg-8">
            <div class="card card-profile">
                <div class="card-body">
                    <div class="media">
                        <img src="https://odocartstorage.blob.core.windows.net/odo-files/MerchantLogo/1ec9b806-cfd8-4c96-93d2-940e4e580ff7_logo_white.png" alt="">
                        <div class="media-body">
                            <h3 class="card-profile-name">
                                <asp:Literal runat="server" ID="ltrFullName"></asp:Literal>
                            </h3>
                            <p class="card-profile-position">
                                <asp:Literal ID="ltrRole" runat="server"></asp:Literal> 
                                <a href="/"><%= this.CurrentUser.StoreGroupName %></a>
                            </p>
                            <p class="mg-b-0">
                                <asp:Literal ID="ltrAddr" runat="server"></asp:Literal>
                            </p>
                            <p>
                                <asp:Literal runat="server" ID="ltrCity"></asp:Literal>&nbsp;
                                <asp:Literal runat="server" ID="ltrState"></asp:Literal>&nbsp;
                                <asp:Literal runat="server" ID="ltrCountry"></asp:Literal><br />
                            </p>
                            <uc1:ctrlSetPassword runat="server" ID="ctrlSetPassword" />
                        </div><!-- media-body -->
                    </div><!-- media -->
                </div><!-- card-body -->
            </div><!-- card -->
        </div><!-- col-lg-8 -->

        <div class="col-lg-4 mg-t-20 mg-lg-t-0">
            <div class="card pd-25">
                <div class="slim-card-title">Contact &amp;Personal Info</div>

                <div class="media-list mg-t-25">
                    <div class="media mg-t-25">
                        <div><i class="icon ion-ios-telephone-outline tx-24 lh-0"></i></div>
                        <div class="media-body mg-l-15 mg-t-4">
                            <h6 class="tx-14 tx-gray-700">Phone Number</h6>
                            <span class="d-block"><%= this.CurrentUser.Phone %></span>
                        </div><!-- media-body -->
                    </div><!-- media -->
                    <div class="media mg-t-25">
                        <div><i class="icon ion-ios-email-outline tx-24 lh-0"></i></div>
                        <div class="media-body mg-l-15 mg-t-4">
                            <h6 class="tx-14 tx-gray-700">Email Address</h6>
                            <span class="d-block"><%= this.CurrentUser.Email %></span>
                        </div><!-- media-body -->
                    </div><!-- media -->
                    <div class="media mg-t-25">
                        <div><i class="ion-android-checkbox-outline tx-18 lh-0"></i></div>
                        <div class="media-body mg-l-15 mg-t-2">
                            <h6 class="tx-14 tx-gray-700">Email Verification Status</h6>
                            <a href="#" class="d-block"><%= this.CurrentUser.HasVerifiedEmail %></a>
                        </div><!-- media-body -->
                    </div><!-- media -->

                    <% if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK") { %>
                        <div class="media mg-t-25" style="display: none;">
                    <% } else { %>
                        <div class="media mg-t-25">
                    <% } %>
                    <uc1:ctrlLanguages runat="server" ID="ctrlLanguages1" />
                    <div><i class="fa-regular fa-language tx-14 lh-0"></i></div>
                    <div class="media-body mg-l-15 mg-t-2">
                        <h6 class="tx-14 tx-gray-700">Language preference</h6>
                        <a href="javascript:void(0)" onclick="$('#modallanguage').modal('show');" class="d-block">Set Language</a>
                    </div><!-- media-body -->
                    <%--<br /><br /><br /><br /><br /><br /><br /><br />--%>
                    </div><!-- media -->

                    <div class="media mg-t-25">
                        <div><i class="ion-ios-eye tx-22 lh-0"></i></div>
                        <div class="media-body mg-l-15 mg-t-2">
                            <h6 class="tx-14 tx-gray-700">Change Password</h6>
                            <a href="javascript:void(0)" onclick="$('#modalsetpsw').modal('show');" class="d-block">Change</a>
                        </div><!-- media-body -->
                        <br /><br /><br /><br /><br /><br /><br /><br />
                    </div><!-- media -->
                </div><!-- media-list -->
            </div><!-- card -->
        </div><!-- col-lg-4 -->
    </div><!-- row -->

</asp:Content>
