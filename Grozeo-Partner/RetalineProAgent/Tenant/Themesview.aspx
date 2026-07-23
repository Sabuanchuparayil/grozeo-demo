<%@ Page Language="C#" AutoEventWireup="true" Async="true" CodeBehind="Themesview.aspx.cs" Inherits="RetalineProAgent.Tenant.Themesview" %>

<html>
<head>
    <title>Theme</title>
    <%@ Register Src="~/Controls/StoreSettings/ctrlMessagebox.ascx" TagPrefix="uc1" TagName="ctrlMessagebox" %>
    <script src="/content/lib/jquery/js/jquery.js"></script>
    <script src="/content/lib/popper.js/js/popper.js"></script>
    <script src="/content/lib/bootstrap/js/bootstrap.js"></script>
    <script src="/content/js/custom/swiper-bundle.min.js"></script>

    <link rel="stylesheet" href="/content/css/slim.css">
    <link rel="stylesheet" href="/content/css/custom/swiper-bundle.min.css" />
    <link rel="stylesheet" href="/content/css/custom/agent.css" />
    <link runat="server" id="lnkFavIco" rel="shortcut icon" href="/Content/images/favicon.ico" type="image/x-icon" />

</head>
<body>
    <form runat="server">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="theme_slider_sec newwndow">
                        <uc1:ctrlMessagebox runat="server" ID="ctrlMessagebox" />
                        <div class="slider-action">
                            <a class="applytheme <%= Request.QueryString["selectedtheme"] == "true" ? "hide" : "" %> " data-toggle="modal" href="#themealert">
                                <img alt="close popup" class="img-fluid" src="/Content/images/icon/apply_theme_icon.svg">
                                <span>Apply</span>
                            </a>                          
                            <a href="javascript:void(0)" onclick="window.close();" class="close position-relative">
                                <img alt="close popup" class="img-fluid" src="/Content/images/icon/close_popup_W_icon.svg">
                                <span>Close</span>
                            </a>
                        </div>
                        <div class="swiper themeslider">
                            <div class="swiper-wrapper">
                                <asp:Repeater runat="server" ID="rprthemeimage" DataSourceID="SDSthemeimage">
                                    <ItemTemplate>
                                        <div class="swiper-slide">
                                            <img alt="Yummy home" class="img-fluid" src='<%# Eval("image") %>'>
                                        </div>
                                    </ItemTemplate>
                                </asp:Repeater>
                                <asp:SqlDataSource runat="server" ID="SDSthemeimage" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                                    SelectCommand="SELECT * FROM `theme` t INNER JOIN  theme_image ti  ON t.id=ti.themeId WHERE t.id=@id order by type asc">
                                    <SelectParameters>
                                        <asp:QueryStringParameter QueryStringField="themeId" Name="id" />
                                    </SelectParameters>
                                </asp:SqlDataSource>
                            </div>

                        </div>
                        <div class="theme_slider_btn">
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal" id="themealert" data-backdrop="static">
            <div class="modal-dialog w-100">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="themealert_popup">
                            <span class="iconsdes"></span>
                            <h6 class="text-uppercase text-dark text-center">
                                <asp:Label runat="server" ID="lblconform"></asp:Label></h6>
                            <p class="p-0 text-dark  text-center" style="margin: 0;">
                                <asp:Label runat="server" ID="lbltext"></asp:Label></p>
                            <div class="btnsec_tem pt-3" style="text-align: center;">
                                <a href="#" data-dismiss="modal" class="btn btn-secondary mr-2">Close</a>
                                <asp:LinkButton ID="lbtTheme" runat="server" CssClass="btn btn-primary" OnClick="lbtTheme_ClickAsync">
                                    <asp:Label runat="server" ID="lbthemename"></asp:Label></asp:LinkButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function () {
                const themesliderswiper = new Swiper('.themeslider', {
                    // Optional parameters
                    autoHeight: true,
                    centeredSlides: true,
                    slidesPerView: 'auto',
                    spaceBetween: 5,
                    keyboard: {
                        enabled: true,
                        onlyInViewport: false,
                    },
                    navigation: {
                        nextEl: '.theme_slider_btn .swiper-button-next',
                        prevEl: '.theme_slider_btn .swiper-button-prev',
                    },
                });
            });
        </script>
    </form>
</body>
</html>
