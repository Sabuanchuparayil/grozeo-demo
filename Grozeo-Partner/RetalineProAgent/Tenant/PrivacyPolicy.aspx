<%@ Page Language="C#" AutoEventWireup="true" Async="true" MasterPageFile="~/Tenant/TenantMaster.master" ValidateRequest="false" CodeBehind="PrivacyPolicy.aspx.cs" Inherits="RetalineProAgent.PrivacyPolicy" %>
<asp:Content ContentPlaceHolderID="head" runat="server">
  <link href="/content/lib/summernote/css/summernote-bs4.css" rel="stylesheet">
<script src="/content/lib/summernote/js/summernote-bs4.min.js"></script>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/StoreConfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/Appearance">Appearance</a></li>
    <li class="breadcrumb-item"><a href="/navigations/ContentsPages">Content Pages</a></li>
    <li class="breadcrumb-item active" aria-current="page">Privacy Policy</li>--%>
    <a href="/Navigations/ContentsPages"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Privacy Policy</h6>
        <p class="mb-0">Privacy policy</p>
    </div>
</asp:Content>


<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">

    <asp:HiddenField ID="hidTab" runat="server" />

    <div class="card">

        <div class="card-body p-3 shadow_top">
            <%--<label class="section-title"><i class="fa fa-edit"></i> INFO SETTINGS</label>--%>
            <p class="mb-2">Changes in the display settings can impact the look and feel as well as components used.</p>


            <div class="row">

                <div class="col-md-12 mb-4">

                    <p class="lead mb-0">Add/edit privacy policy</p>
                    <small>The info pages will load when the corresponding link in the footer is clicked. If the specified content is missing, default content will be loaded.</small>

                    <h6 class="card-title mb-2 mt-4">About</h6>
                    <textarea runat="server" id="taPrivacyPolicy" class="txtrichtext"></textarea>


                </div>
                <%--<div class="col-md-12 mb-4">
                    <h6 class="card-title mb-2">Terms & Conditions</h6>
                    <textarea runat="server" id="taTermsContent" class="txtrichtext"></textarea>
                </div>--%>
                <div class="col-12">
                    <div class="d-inline-block">
                        <div class="input-group">
                            <asp:Button runat="server" ID="btnSavePrivacyPolicy" OnClientClick="loadSummernoteContent()" OnClick="btnSavePrivacyPolicy_Click" CssClass="btn btn-primary " Text="Save Content" />
                            <asp:Label ID="Label2" Font-Bold="true" runat="server" />
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
    



              <%--<div class="col-5 col-sm-2">
                <div class="nav flex-column nav-tabs h-100" id="vert-tabs-tab" role="tablist" aria-orientation="vertical">
                  <a class="nav-link <%= (hidTab.Value != "4" ?"active" : "") %>" onclick="$('#<%= hidTab.ClientID %>').val('3')" id="vert-tabs-info-tab" data-toggle="pill" href="#vert-tabs-info" role="tab" aria-controls="vert-tabs-info" aria-selected="<%= (hidTab.Value != "4" ?"true" : "false") %>">Info</a>
                  <a class="nav-link <%= (hidTab.Value == "4" ?"active" : "") %>" onclick="$('#<%= hidTab.ClientID %>').val('4')" id="vert-tabs-theme-tab" data-toggle="pill" href="#vert-tabs-theme" role="tab" aria-controls="vert-tabs-theme" aria-selected="<%= (hidTab.Value == "4" ?"true" : "false") %>">Theme</a>
                </div>
              </div>--%>
              <%--<div class="col-7 col-sm-10">
                <div class="tab-content" id="vert-tabs-tabContent">--%>
                  <%--<div class="tab-pane text-left fade <%= (String.IsNullOrEmpty(hidTab.Value) || hidTab.Value == "1"?"show active":"") %>" id="vert-tabs-logo" role="tabpanel" aria-labelledby="vert-tabs-logo-tab">
                      <p class="lead mb-0">Add/Edit logo to be displayed</p>
                        <small>If added, the logo will be displayed in the header at the top left of the public website. In the absence of a logo, the name specified in the store's settings will be displayed.</small>
                      <br /><br />
                  <div class="row">
                                <div class="col-md-6 form-group">
                                            <label>Logo (for website)</label>
											<asp:FileUpload ID="uploadLogo" CssClass="form-control" runat="server" />
                                    <asp:Image ID="imgLogo" runat="server" style="max-width: 40px; max-height: 40px; width: auto; margin-top: 5px; height: auto;border: solid 1px lightgray;" Visible="false" />
                                    <asp:CheckBox ID="chkDelImgLogo" OnCheckedChanged="chkDelImgLogo_CheckedChanged" OnClick="if(!confirm('Are you sure you want to delete the logo?')){return false;};" AutoPostBack="true" runat="server" Visible="false" Text="Delete?" />
                                        </div>

                                 <div class="col-md-6 form-group">
                                            <label>Small Logo (Preferably monogram, for mobile view)</label>
											<asp:FileUpload ID="uploadLogoWhite" CssClass="form-control" runat="server" />
                                    <asp:Image ID="imgLogoWhite" runat="server" style="max-width: 40px; max-height: 40px; width: auto; margin-top: 5px; height: auto;border: solid 1px lightgray;" Visible="false" />
                                    <asp:CheckBox ID="chkDelImgLogoWhite" OnCheckedChanged="chkDelImgLogoWhite_CheckedChanged" OnClick="if(!confirm('Are you sure you want to delete the logo?')){return false;};" AutoPostBack="true" runat="server" Visible="false" Text="Delete?" />
                                        </div>
</div>
                                

                  <div class="row">
                    
                    <div class="col-md-6 form-group">

<div class="form-group">
                  <label>&nbsp;</label>
                  <div class="input-group">
            <asp:Button runat="server" ID="btnSaveLogo" OnClick="btnSaveLogo_Click" CssClass="btn btn-success float-right" Text="Save changes"/>&nbsp;
            <br /><asp:Label ID="lblLogoMessage" Font-Bold="true" runat="server"/>

</div></div>


                    </div>

                  </div>


                  


                  </div>--%>
                  
                  <%--<div class="tab-pane fade <%= (hidTab.Value != "4" ?"show active" : "") %>" id="vert-tabs-info" role="tabpanel" aria-labelledby="vert-tabs-info-tab">

                      <p class="lead mb-0">Add/edit information page content</p>
                    <small>The info pages will load when the corresponding link in the footer is clicked. If the specified content is missing, default content will be loaded.</small>
                      <br /><br />


<div class="row">
        <div class="col-md-12">
              <h3 class="card-title">About</h3>
                    <textarea runat="server" id="taAboutContent" class="txtrichtext"></textarea>
        </div>
        <!-- /.col-->
      </div>

<br />--%>
<%--<div class="row">
        <div class="col-md-12">
              <h3 class="card-title">Terms & Conditions</h3>
                <textarea runat="server" id="taTermsContent" class="txtrichtext"></textarea>
        </div>
        <!-- /.col-->
      </div>--%>

<%--<div class="row">
                        <div class="col-md-6 form-group">

<div class="form-group">
                  <label>&nbsp;</label>
                  <div class="input-group">
            <asp:Button runat="server" ID="btnSaveInfoContent" OnClientClick="loadSummernoteContent()" OnClick="btnSaveInfoContent_Click" CssClass="btn btn-success float-right" Text="Save Content"/>&nbsp;
            <br /><asp:Label ID="Label2" Font-Bold="true" runat="server"/>

</div></div>


                    </div>

    </div--%>

                  <%--</div>--%>
                <%--  <div class="tab-pane fade <%= (hidTab.Value == "4" ?"show active" : "") %>" id="vert-tabs-theme" role="tabpanel" aria-labelledby="vert-tabs-theme-tab">

                      <div class="row">
                          <asp:Repeater runat="server" ID="rptThemes" DataSourceID="ODSThemes">
                              <ItemTemplate> 
                                  <div class="col-md-3">
                                      <asp:LinkButton ID="lbtTheme" OnClientClick="return confirm('Theme change can impact the look and feel as well as the components displayed. Are you sure you want to change the theme?')" runat="server" OnClick="lbtTheme_Click" themename='<%# System.IO.Path.GetFileNameWithoutExtension((string)Container.DataItem) %>' CssClass='<%# (IsActiveTheme(System.IO.Path.GetFileNameWithoutExtension((string)Container.DataItem)) ? "bg-mantle" : "card-item-img" ) %>'>
                                      <img src="<%# String.Format("/Content/images/theme/{0}", System.IO.Path.GetFileName((string)Container.DataItem)) %>" style="max-width: 100%; height: auto; max-height: 200;" /><br />
                                      <div class="form-check" style="margin-top:10px;">
                                     <i class="ion-ios-checkmark-outline" style="margin-right: 5px;"></i>
                                    <label class="tx-inverse">
                                              <i class="<%# (IsActiveTheme(System.IO.Path.GetFileNameWithoutExtension((string)Container.DataItem)) ? "ion-checkmark-circled" : "ion-android-radio-button-off" ) %>" style="margin-right: 5px;"></i>
                                              <span><%# System.IO.Path.GetFileNameWithoutExtension((string)Container.DataItem) %></span></label>
                                       <asp:RadioButton ID="rbTheme" ClientIDMode="Static" GroupName="Theme" runat="server" Text='<%# System.IO.Path.GetFileNameWithoutExtension((string)Container.DataItem) %>' OnCheckedChanged="rbTheme_CheckedChanged" Checked='<%# IsActiveTheme(System.IO.Path.GetFileNameWithoutExtension((string)Container.DataItem)) %>' />--%>
                               <%--  </div>
                                          </asp:LinkButton>
                                  </div>
                              </ItemTemplate>
                          </asp:Repeater>
                          <asp:ObjectDataSource ID="ODSThemes" OnSelecting="ODSThemes_Selecting" runat="server" TypeName="System.IO.Directory" SelectMethod="GetFiles"><SelectParameters><asp:Parameter Name="path" DefaultValue="Content/images/theme" /></SelectParameters></asp:ObjectDataSource>--%>
                        <%--  <div class="col-md-3">
                              <img src="Content/images/theme/theme1.png" style="max-width: 100%; height: auto; max-height: 200;" /><br />
                              <div class="form-check">
                          <input class="form-check-input" type="radio" name="radio1" checked>
                          <label class="form-check-label">Default Theme</label>
                        </div>
                     </div>
                  </div>

                  </div>--%>
              
<%--</div>--%>

<script>
    $(function () {
        $('.txtrichtext').summernote({
            height: 165
        });
        $('.txtrichtext').on('summernote.blur', function () {
            $(this).html($(this).summernote('code'));
        });
    })

</script>

</asp:Content>