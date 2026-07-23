<%@ Page Language="C#" AutoEventWireup="true" Title="Graphics" EnableViewState="true" MasterPageFile="~/Tenant/TenantMaster.master" Async="true"  CodeBehind="Graphics.aspx.cs" Inherits="RetalineProAgent.Graphics" %>


<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <a href="/Navigations/Marketing"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server">
    <div>
        <h6 class="slim-pagetitle">
            <asp:Literal ID="ltrTitle1" runat="server" Text="Graphics for Social Media Marketing"></asp:Literal>
        </h6>
    </div>
    
</asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <div class="card">
        <div class="card-header shadow_top">
            <div class="row row-sm">
                <div class="col-12 col-md-4 col-lg-3 mb-2 mb-md-0">
                    <label for="SelectPost" class="tx-dark mb-1 w-100">Select SM Application</label>
                    <asp:DropDownList ID="selectapplication" runat="server" AutoPostBack="True" CssClass="form-control select2" OnSelectedIndexChanged="SelectApplicationChanged" EnableViewState="true">
                        <asp:ListItem Value="">Select SM Application</asp:ListItem>
                        <%--<asp:ListItem Value="1">Web</asp:ListItem>
                        <asp:ListItem Value="2">App</asp:ListItem>--%>
                        <asp:ListItem Value="3">Facebook</asp:ListItem>
                        <asp:ListItem Value="4">Instagram</asp:ListItem>
                        <asp:ListItem Value="5">WhatsApp</asp:ListItem>
                    </asp:DropDownList>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="selectapplication" ForeColor="Red" CssClass="errormsg" ErrorMessage="Please select application" Display="Dynamic" ValidationGroup="AddGraphics"></asp:RequiredFieldValidator>
                </div>
                
                <div class="col-12 col-sm-6  col-md-4 col-lg-3 mb-2 mb-md-0">
                    <label for="SelectPost" class="tx-dark mb-1 w-100">Select Post Type</label>
                    <asp:DropDownList ID="selPostType" runat="server" CssClass="form-control select2" AutoPostBack="true" OnSelectedIndexChanged="SelPostType_SelectedIndexChanged"></asp:DropDownList>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="selPostType" ForeColor="Red" CssClass="errormsg" ErrorMessage="Please select post type" Display="Dynamic" ValidationGroup="AddGraphics"></asp:RequiredFieldValidator>
                </div>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2 mb-md-0">
                    <label for="SelectPost" class="tx-dark mb-1 w-100">Select Post Theme</label>
                    <asp:DropDownList ID="selectposttheme" runat="server" CssClass="form-control select2"></asp:DropDownList>
                    <asp:RequiredFieldValidator runat="server" ControlToValidate="selectposttheme" ForeColor="Red" CssClass="errormsg" ErrorMessage="Please select post theme" Display="Dynamic" ValidationGroup="AddGraphics"></asp:RequiredFieldValidator>
                </div>
                <%--<div class="col-lg-2 d-flex flex-wrap">
                    <label class="mb-4 d-none d-lg-inline-block w-100"></label>
                    <asp:LinkButton ID="lbtnAdd" runat="server" CssClass="btn btn-primary w-lg-100 mt-2 mt-lg-0" Text="Go" ValidationGroup="AddSlot" />
                </div>--%>
                <%--<div class="col-12 col-md-3 align-items-end d-flex justify-content-md-end">
                  <a id="" class="btn  btn-primary" typeid="1" href="/Tenant/Appearance/CustomisedGraphics">Customised Graphics</a>
                </div>--%>
                <div class="col-12 col-lg-3 d-flex flex-wrap align-items-end justify-content-lg-between">
                    <asp:LinkButton ID="lbtnGo" OnClick="lbtnGo_Click" runat="server" CssClass="btn btn-primary mt-2 mt-lg-0" Text="Go" ValidationGroup="AddGraphics" />
                    <a id="" class="btn btn-primary mt-2 mt-lg-0 ml-2" typeid="1" href="/Tenant/Appearance/CustomisedGraphics">Customised Graphics</a>
                </div>
            </div>
            <asp:Label ID="errorMessageLabel" runat="server" ForeColor="Red" Visible="false"></asp:Label>
        </div><!-- card-header -->
        <div class="card-body p-3">
            <div class="row row-cols-1 row-cols-md-2">
                <!-- Use row-cols-2 to display 2 images side by side -->
                <div class="col-12" runat="server" visible="false" id="titleName">
                    <h5 class="tx-16 tx-dark">Graphics for
        <span style="display: inline-block;">
            <asp:Literal ID="ltrGraphics" runat="server"></asp:Literal>
        </span>
                    </h5>
                </div>

                <div class="col-12 d-flex flex-wrap grphlist_list_wrap facbpost_creatives">
                    <asp:Repeater ID="rptOwnGraphics" DataSourceID="SDSGraphicTemplates" runat="server" OnItemDataBound="rptOwnGraphics_ItemDataBound" Visible="false">
                        <ItemTemplate>
                            <div class="grphlist_list p-2">
                                <div class="grphlist_wrap d-flex flex-column border rounded p-1">
                                    <div class="grph_img d-flex justify-content-center">
                                        <asp:Image runat="server" onerror="this.src='/content/images/image_on_error.svg'" data-toggle="modal" data-target='<%# "#creativepostpopup" + Container.ItemIndex %>' CssClass="mw-100" ImageUrl='<%# Eval("designUrl") %>' />
                                    </div>
                                    <div class="grph_btn w-100 d-flex justify-content-center align-items-center mt-2 flex-wrap">
                                        <asp:HyperLink runat="server" CssClass="btn btn-primary m-1 p-1 tx-9 d-flex align-items-center justify-content-center" NavigateUrl='<%# string.Format("~/Tenant/Appearance/CustomBanner?id={0}&tempid={1}&designid={2}", Eval("id"), Eval("templateID"), Eval("designUrl")) %>' Text="Customise"></asp:HyperLink>
                                        <a href="#" class="btn btn-outline-primary m-1 p-1 tx-9 d-flex align-items-center justify-content-center">Order</a>
                                    </div>
                                </div>
                            </div>
                        </ItemTemplate>
                    </asp:Repeater>
                </div>

                <!-- Message for no records available -->
                <div  class="col-12 text-center" runat="server" id="noRecordsMessage" visible="false">
                    <img class="mt-3" style="opacity: 0.9; max-width: 150px;" src="/Content/images/NoGraphicsIMG.svg">
                    <h6 class="mb-2 mt-4">Select a template to proceed</h6>
                </div>
            </div>
        </div>
            <asp:Repeater ID="rptImages" runat="server" DataSourceID="SDSGraphicTemplates">
                <ItemTemplate>
                    <div class="modal fade" id='<%# "creativepostpopup" + Container.ItemIndex %>' tabindex="-1" role="dialog" aria-labelledby='<%# "creativepostpopuptitle" + Container.ItemIndex %>' aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-body p-0">
                                    <button type="button" class="close bg-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <div class="d-flex justify-content-center align-items-center">
                                        <asp:Image runat="server" Style="max-height: 450px;" CssClass="mw-100" ImageUrl='<%# string.Format("{0}?id={1}", Eval("designUrl"), Eval("id")) %>' />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </ItemTemplate>
            </asp:Repeater>
        </div>
        <!-- card -->
    
    <asp:SqlDataSource ID="SDSGraphicTemplates" runat="server" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
        SelectCommand="SELECT gt.id, templateID, designUrl, templateUrl, width, height, locationId FROM graphics_template gt
                       INNER JOIN graphics_template_settings ON applicationId=gt.application AND locationId = gt.location 
                       WHERE STATUS=1 AND application=@applicationId AND location=@postId AND gt.template=@themeId"
        ProviderName="MySql.Data.MySqlClient">
        <SelectParameters>
            <asp:ControlParameter Name="applicationId" ControlID="selectapplication" DefaultValue="0" />
            <asp:ControlParameter Name="postId" ControlID="selPostType" DefaultValue="0" />
            <asp:ControlParameter Name="themeId" ControlID="selectposttheme" DefaultValue="0" />
        </SelectParameters>
    </asp:SqlDataSource>


    
</asp:Content>



