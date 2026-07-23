<%@ Page Language="C#" AutoEventWireup="true" Async="true" Title="Marketing Tools" CodeBehind="MarketingTools.aspx.cs" MasterPageFile="~/Tenant/TenantMaster.master" Inherits="RetalineProAgent.Tenant.MarketingTools" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Navigations/Marketing"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Marketing Tools</h6>
        <p class="mb-0"></p>
    </div>
</asp:Content>


<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <div class="row row-sm menucard">

        <!-- Google Tag Manager Card -->
        <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <asp:LinkButton ID="lnkGTag" OnClick="lnkGoogleTag_Click" runat="server" CssClass="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                    <i class="fa fa-clipboard-list-check mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true">
                        <img src="/Content/images/google_tag_manager.png" />
                    </i>
                     <i id="I2" class="fa-light fa-circle-check active_tools tx-34 tx-success mt_tag_active wd-34 ht-34" runat="server" visible="false"></i>
                    <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Google Tag Manager</h5>
                    <p class="card-text mg-b-8 tx-11">Streamline Your Tracking and Tag Management with Google Tag Manager</p>
                </div>
            </asp:LinkButton>
        </div>

        <!-- Google Analytics Card -->
        <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
           <asp:LinkButton ID="lnkGoogleAnalytics" OnClick="lnkGoogleAnalytics_Click" runat="server" CssClass="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                    <i class="fa fa-clipboard-list-check mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true">
                        <img src="/Content/images/google_analytics.png" />
                    </i>
                    <i id="I5" class="fa-light fa-circle-check active_tools tx-34 tx-success wd-34 ht-34" runat="server" visible="false"></i>
                    <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Google Analytics</h5>
                    <p class="card-text mg-b-8 tx-11">Track, Analyze, and Optimize Your Website Performance with Google Analytics</p>
                </div>
            </asp:LinkButton>
        </div>

        <!-- Microsoft Clarity Card -->
        <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <asp:LinkButton ID="lnkMicrosoftClarity" OnClick="lnkMicrosoftClarity_Click" runat="server" CssClass="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                    <i class="fa fa-clipboard-list-check mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true">
                        <img src="/Content/images/microsoft_clarity.png" />
                    </i>
                    <i id="I1" class="fa-light fa-circle-check active_tools tx-34 tx-success wd-34 ht-34" runat="server" visible="false"></i>
                    <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Microsoft Clarity</h5>
                    <p class="card-text mg-b-8 tx-11">Gain Deeper Insights into User Behavior with Microsoft Clarity</p>
                </div>
            </asp:LinkButton>
        </div>

        <!-- SEO Tools Card -->
        <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
             <asp:LinkButton ID="lnkSEOTools" OnClick="lnkSEOTools_Click" runat="server" CssClass="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                    <i class="fa fa-clipboard-list-check mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true">
                        <img src="/Content/images/search-engine-optimisation.png" />
                    </i>
                    <i id="I3" class="fa-light fa-circle-check active_tools tx-34 tx-success wd-34 ht-34" runat="server" visible="false"></i>
                    <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">SEO Tools</h5>
                    <p class="card-text mg-b-8 tx-11">Boost Your Website's Visibility and Performance with Powerful SEO Tools</p>
                </div>
            </asp:LinkButton>
        </div>

        <!-- Meta Pixel Card -->
        <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
            <asp:LinkButton ID="lnkMetaPixal" OnClick="lnkMetaPixal_Click" runat="server" CssClass="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                    <i class="fa fa-clipboard-list-check mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true">
                        <img src="/Content/images/meta-logo.png" />
                    </i>
                    <i id="I4" class="fa-light fa-circle-check active_tools tx-34 tx-success wd-34 ht-34" runat="server" visible="false"></i>
                    <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Meta Pixel</h5>
                    <p class="card-text mg-b-8 tx-11">Optimize your ads and drive conversions with Meta Pixel</p>
                </div>
            </asp:LinkButton>
        </div>

        <!-- Tawk to Live Chat Card -->
        <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
           <asp:LinkButton ID="lnkTawkLive" OnClick="lnkTawkLive_Click" runat="server" CssClass="card h-100 p-4">
                <div class="card-body p-0 tx-left position-relative">
                    <i class="fa fa-clipboard-list-check mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true">
                        <img src="/Content/images/tawk_to_live_chat.png" />
                    </i>
                    <i id="I6" class="fa-light fa-circle-check active_tools tx-34 tx-success wd-34 ht-34" runat="server" visible="false"></i>
                    <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Tawk to Live Chat</h5>
                    <p class="card-text mg-b-8 tx-11">Get in touch with us anytime. We're here for you.</p>
                </div>
            </asp:LinkButton>
        </div>
    
        <!-- Social Media URLs -->
    <div class="col-sm-6 col-lg-4 mb-3 mb-lg-4">
       <asp:LinkButton ID="lnkSocialMedia" OnClick="lnkSocialMedia_Click" runat="server" CssClass="card h-100 p-4">
            <div class="card-body p-0 tx-left position-relative">
                <i class="fa fa-clipboard-list-check mb-3 tx-20 tx-primary wd-45 ht-45 card_icon rounded-circle d-flex justify-content-center align-items-center" aria-hidden="true">
                    <img src="/Content/images/social_media.png" />
                </i>
                <i id="I7" class="fa-light fa-circle-check active_tools tx-34 tx-success wd-34 ht-34" runat="server" visible="false"></i>
                <h5 class="card-title tx-medium mg-b-0 mg-t-3 tx-15 tx-gray-800">Social Media URL</h5>
                <p class="card-text mg-b-8 tx-11">Stay connected! Find us on our social media channels.</p>
            </div>
        </asp:LinkButton>
    </div>
    </div>

 <!-- Modal GoogleTag -->
<asp:HiddenField ID="hdGoogleTag" runat="server" />
<div id="modalGoogleTag" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg w-100" role="document">
        <div class="modal-content tx-size-sm">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h6 class="tx-14 mg-b-0 tx-inverse text-center">Google Tag Manager</h6>
            </div>
            <div class="modal-body pd-y-20 pd-x-20">
                <div class="row row-sm">
                    <div class="col-12 d-flex align-items-end">
                        <div class="input-group mr-3">
                            <label class="w-100">Enter your Google Tags: <span class="tx-danger">*</span>
                                <i class="fa-regular tx-info fa-circle-info tooltipinfo popover-trigger" tabindex="0" data-toggle="popover"
                                    title="Google Tag" data-html="true"
                                    data-content="<img src='/Content/images/GTM.png'> "></i>

                            </label>
                            <asp:TextBox ID="txtGoogleTag" runat="server" CssClass="form-control" autocomplete="nofill" />
                        </div>
                        <asp:LinkButton ID="lbtnGTSave" runat="server" Text="Save" CssClass="btn btn-primary pd-x-25 mr-2" OnClick="lbtnGTSave_Click"/>
                        <asp:LinkButton ID="lbtnGTDelete" runat="server" Text="Delete" CssClass="btn btn-primary pd-x-25 mr-2"  OnClick="lbtnGTDelete_Click" />
                    </div>
                </div>
             </div>
        </div>
    </div>
</div>

 <!-- Modal GoogleAnalytics -->
<asp:HiddenField ID="hdGoogleAnalytics" runat="server" />
<div id="modalGoogleAnalytics" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg w-100" role="document">
        <div class="modal-content tx-size-sm">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h6 class="tx-14 mg-b-0 tx-inverse text-center">Google Analytics</h6>
            </div>
            <div class="modal-body pd-y-20 pd-x-20">
                <div class="row row-sm">
                    <div class="col-12 d-flex align-items-end">
                        <div class="input-group mr-3">
                            <label class="w-100">Enter your Google Analytics Tags: <span class="tx-danger">*</span>
                                <i class="fa-regular tx-info fa-circle-info tooltipinfo popover-trigger" tabindex="0" data-toggle="popover"
                                    title="Google Analytics" data-html="true"
                                    data-content="<img src='/Content/images/GA.png'> "></i>
                            </label>
                            <asp:TextBox ID="txtGoogleAnalytics" runat="server" CssClass="form-control" autocomplete="nofill" />
                        </div>
                        <asp:LinkButton ID="lbtnGAnalyticsSave" runat="server" Text="Save" CssClass="btn btn-primary pd-x-25 mr-2" OnClick="lbtnGAnalyticsSave_Click" />
                        <asp:LinkButton ID="lbtnGAnalyticsDelete" runat="server" Text="Delete" CssClass="btn btn-primary pd-x-25 mr-2" OnClick="lbtnGAnalyticsDelete_Click" />
                    </div>
                </div>
             </div>
        </div>
    </div>
</div>

 <!-- Modal Microsoft Clarity -->
<asp:HiddenField ID="hdMicrosoftClarity" runat="server" />
<div id="modalMicrosoftClarity" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg w-100" role="document">
        <div class="modal-content tx-size-sm">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h6 class="tx-14 mg-b-0 tx-inverse text-center">Microsoft Clarity</h6>
            </div>
            <div class="modal-body pd-y-20 pd-x-20">
                <div class="row row-sm">
                    <div class="col-12 d-flex align-items-end">
                        <div class="input-group mr-3">
                            <label class="w-100">Enter your Microsoft Clarity Tags: <span class="tx-danger">*</span>
                                <i class="fa-regular tx-info fa-circle-info tooltipinfo popover-trigger" tabindex="0" data-toggle="popover"
                                    title="Microsoft Clarity" data-html="true"
                                    data-content="<img src='/Content/images/Clarity.png'> "></i>
                            </label>
                            <asp:TextBox ID="txtMSClarity" runat="server" CssClass="form-control" autocomplete="nofill" />
                        </div>
                        <asp:LinkButton ID="lbtnMSClarity" runat="server" Text="Save" CssClass="btn btn-primary pd-x-25 mr-2" OnClick="lbtnMSClarity_Click"  />
                        <asp:LinkButton ID="lbtnMSClarityDelete" runat="server" Text="Delete" CssClass="btn btn-primary pd-x-25 mr-2" OnClick="lbtnMSClarityDelete_Click" />
                    </div>
                </div>
           </div>
        </div>
    </div>
</div>

  <!-- Modal SEOTools -->

<asp:HiddenField ID="SEOMetaTag" runat="server" />
<asp:HiddenField ID="SEOMetaKey" runat="server" />
<asp:HiddenField ID="SEOMetaDesc" runat="server" />
<div id="modalSEOTools" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg w-100" role="document">
        <div class="modal-content tx-size-sm">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h6 class="tx-14 mg-b-0 tx-inverse text-center">SEO Tools</h6>
            </div>
            <div class="modal-body pd-y-20 pd-x-20">
                <div class="row row-sm">

                    <div class="col-12 col-sm-6 mb-2 mb-sm-0">
                        <div class="input-group">
                             <label class="w-100">Enter Meta Title: </label>
                             <asp:TextBox ID="txtSEOMetaTitle" runat="server" CssClass="form-control" autocomplete="nofill" />
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 mb-2">
                        <div class="input-group">
                            <label for="txtSEOMetaKeyword" class="w-100">Enter Meta Keyword:</label>
                            <asp:TextBox ID="txtSEOMetaKeyword" runat="server" CssClass="form-control" autocomplete="nofill" />
                        </div>                        
                    </div>

                    <div class="col-12 mb-2">
                        <div class="input-group">
                            <label for="txtSEOMetaDesc" class="w-100">Enter Meta Description:</label>
                            <asp:TextBox ID="txtSEOMetaDesc" runat="server" CssClass="form-control" TextMode="MultiLine" autocomplete="nofill" />
                        </div>
                    </div>

                    <div class="col-12">
                        <asp:LinkButton ID="lbtSEOSave" runat="server" Text="Save" CssClass="btn btn-primary pd-x-25 mr-2" OnClick="lbtSEOSave_Click" CausesValidation="true" />
                        <asp:LinkButton ID="lbtSEODelete" runat="server" Text="Delete" CssClass="btn btn-primary pd-x-25 mr-2" OnClick="lbtSEODelete_Click" />
                    </div>

                </div>
             </div>
        </div>
    </div>
</div>

<!-- Modal Meta Pixel Tools -->

<asp:HiddenField ID="hdMetaPixelValue" runat="server" />
<div id="modalMetaPixel" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg w-100" role="document">
        <div class="modal-content tx-size-sm">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h6 class="tx-14 mg-b-0 tx-inverse text-center">Meta Pixel</h6>
            </div>
            <div class="modal-body pd-y-20 pd-x-20">
                <div class="row row-sm">
                    <div class="col-12 d-flex align-items-end">
                        <div class="input-group mr-3">
                            <label class="w-100">Enter your Meta Pixel Tags: <span class="tx-danger">*</span>
                                <i class="fa-regular tx-info fa-circle-info tooltipinfo popover-trigger" tabindex="0" data-toggle="popover"
                                    title="Meta Pixel" data-html="true"
                                    data-content="<img src='/Content/images/MetaPixel.png'> "></i>
                            </label>
                            <asp:TextBox ID="txtMetaPixel" runat="server" CssClass="form-control" autocomplete="nofill" />
                        </div>
                        <asp:LinkButton ID="lbtnMetaPixelSave" runat="server" Text="Save" CssClass="btn btn-primary pd-x-25 mr-2" OnClick="lbtnMetaPixelSave_Click" />
                        <asp:LinkButton ID="lbtnMetaPixelDelete" runat="server" Text="Delete" CssClass="btn btn-primary pd-x-25 mr-2" OnClick="lbtnMetaPixelDelete_Click" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tawk to Live -->

    <div id="modalTwakLive" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg w-100" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h6 class="tx-14 mg-b-0 tx-inverse text-center">Tawk to Live</h6>
                </div>
                <div class="modal-body pd-y-20 pd-x-20">
                    <div class="row row-sm">
                        <div class="col-12 d-flex align-items-end">
                            <div class="input-group mr-3">
                                <label class="w-100">Enter your Tawk widget id: <span class="tx-danger">*</span>
                                    <i class="fa-regular tx-info fa-circle-info tooltipinfo popover-trigger" tabindex="0" data-toggle="popover"
                                        title="Twak To" data-html="true"
                                        data-content="<img src='/Content/images/Tawkto.png'> "></i>
                                </label>
                                <asp:TextBox ID="txtTawkWidgetId" runat="server" CssClass="form-control" autocomplete="nofill" />
                            </div>
                        </div>
                    </div>
                    <div class="row row-sm">
                        <div class="col-12 d-flex align-items-end">
                            <div class="input-group mr-3">
                                <label class="w-100">Enter your Tawk unique property id: <span class="tx-danger">*</span></label>
                                <asp:TextBox ID="txtTawkPropertyId" runat="server" CssClass="form-control" autocomplete="nofill" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12"><br />
                            <asp:LinkButton ID="lbtnTawkSave" runat="server" Text="Save" CssClass="btn btn-primary pd-x-25 mr-2" OnClick="lbtnTawkSave_Click" />
                            <asp:LinkButton ID="lbtnTawkDelete" runat="server" Text="Delete" CssClass="btn btn-primary pd-x-25 mr-2" OnClick="lbtnTawkDelete_Click" />

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

<!-- Modal Social Media -->
    <div id="modalSocialMediaURL" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg w-100" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h6 class="tx-14 mg-b-0 tx-inverse text-center">Social Media Links</h6>
                </div>
                <div class="modal-body pd-y-20 pd-x-20">
                    <div class="row row-sm">
                            <div class="col-sm-6">
                                <div class="form-group groupinput">
                                    <%--<label class="form-control-label">Facebook: </label>--%>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                          <span class="input-group-text">
                                              <img src="/Content/images/SM_facebook.png" />
                                          </span>
                                        </div>
                                        <asp:TextBox ID="txtFBUrl" TextMode="Url" runat="server" CssClass="form-control" placeholder="Facebook url" />
                                      </div><!-- input-group -->
                                </div>
                            </div>
                            <!-- col-sm-6 -->

                            <div class="col-sm-6">
                                <div class="form-group groupinput">
                                   <%-- <label class="form-control-label">X (Twitter): </label>--%>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                          <span class="input-group-text">
                                              <img src="/Content/images/SM_Twitter.png" />
                                          </span>
                                        </div>
                                        <asp:TextBox ID="txtTwitterUrl" TextMode="Url" runat="server" CssClass="form-control" placeholder="Twitter url" />
                                     </div><!-- input-group -->
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group groupinput">
                                   <%-- <label class="form-control-label">Instagram: </label>--%>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                          <span class="input-group-text">
                                              <img src="/Content/images/SM_Insta.png" />
                                          </span>
                                        </div>
                                        <asp:TextBox ID="txtInstaUrl" TextMode="Url" runat="server" CssClass="form-control" placeholder="Instagram url" />
                                     </div><!-- input-group -->
                                    
                                </div>
                            </div>
                            <!-- col-sm-6 -->
                        <div class="col-sm-6">
                            <div class="form-group groupinput">
                               <%-- <label class="form-control-label">YouTube: </label>--%>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <img src="/Content/images/SM_YouTube.png" />
                                        </span>
                                    </div>
                                    <asp:TextBox ID="txtYouTubeUrl" TextMode="Url" runat="server" CssClass="form-control" placeholder="YouTube url" />
                                </div>
                                <!-- input-group -->

                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group groupinput">
                               <%-- <label class="form-control-label">LinkedIn: </label>--%>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <img src="/Content/images/SM_LinkedIn.png" />
                                        </span>
                                    </div>
                                    <asp:TextBox ID="txtLinkedIn" TextMode="Url" runat="server" CssClass="form-control" placeholder="LinkedIn url" />
                                </div>
                                <!-- input-group -->
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group groupinput">
                               <%-- <label class="form-control-label">TikTok:</label>--%>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <img src="/Content/images/SM_TikTok.png" />
                                        </span>
                                    </div>
                                    <asp:TextBox ID="txtTikTok" TextMode="Url" runat="server" CssClass="form-control" placeholder="TikTok url" />
                                </div>
                                <!-- input-group -->

                            </div>
                        </div>
              
                        <div class="col-12 text-center">
                            <asp:LinkButton ID="lbtSMSave" runat="server" Text="Save" CssClass="btn btn-primary pd-x-25" OnClick="lbtSMSave_Click" CausesValidation="true" />
                           <%-- <asp:LinkButton ID="lbtSMCancel" runat="server" Text="Save" CssClass="btn btn-primary pd-x-25" OnClick="lbtSMCancel_Click" CausesValidation="true" />--%>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <%-- <asp:SqlDataSource ID="SDSMrktTools" runat="server" 
    ConnectionString="<%$ ConnectionStrings:localConnection %>"
    SelectCommand="select Id,Name from plugin;">
   </asp:SqlDataSource>--%>

    <style>
        .menucard i.card_icon::before {
            display: none;
        }

        .menucard i.card_icon img {
            max-width: 30px;
            filter: grayscale(1);
        }

        .menucard .active_tools {
            position: absolute;
            top: 0;
            right: 0;
        }

        /* Ensure cards are displayed properly */
        .menucard .col-sm-6 {
            display: flex;
            justify-content: center;
        }

        .menucard a.card {
            display: block;
            width: 100%;
            height: 100%;
        }

        .menucard .card-body {
            padding: 0;
        }
        .groupinput .input-group {
            border: 1px solid #7f7f7f;
            border-radius: 7px;
            overflow: hidden;
        }
        .groupinput .input-group input {
                border: 0!important;
                border-radius: 0 !important;
        }
        .groupinput .input-group-text {
            width: 32px;
            height: 25px;
            justify-content:center;
        }
        .groupinput .input-group-text img {
            max-width: 100%;
            max-height: 14px;
        }
        
    </style>
    
</asp:Content>
