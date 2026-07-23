<%@ Page Language="C#" AutoEventWireup="true" MasterPageFile="~/Tenant/TenantMaster.master" Async="true" CodeBehind="Themes.aspx.cs" Inherits="RetalineProAgent.Appearance.Themes" %>
    <%@ Register Src="~/Controls/StoreSettings/ctrlMessagebox.ascx" TagPrefix="uc1" TagName="ctrlMessagebox" %>
<asp:Content ContentPlaceHolderID="head" runat="server">
   <link href="/content/lib/select2/css/select2.min.css" rel="stylesheet">
    <link href="/content/lib/summernote/css/summernote-bs4.css" rel="stylesheet">
    <script src="/content/lib/summernote/js/summernote-bs4.min.js"></script>
    <script src="/content/lib/select2/js/select2.full.min.js"></script>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">  
    <a href="/Navigations/Appearance"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>
<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle">Themes</h6>
        <p class="mb-0">Aesthetic Website Themes</p>
    </div>
    </asp:Content>
<asp:Content ContentPlaceHolderID="cpMainContent" runat="server">
    <asp:HiddenField ID="hidTab" runat="server" />   
     <div class="card">
                  <div class="card-body p-3 shadow_top">                    
                    <div class="row row-sm">
                      <div class="col-12 col-sm-7 col-lg-5 col-xl-4 d-flex align-items-center form-group mb-2">
                        <label class="form-control-label text-nowrap mb-0 mr-2 tx-dark">Retail Category</label>
                        <asp:DropDownList ID="selretalicategory" runat="server" CssClass="form-control select2-show-search" DataSourceID="SDSretali" DataTextField="business_type_name" DataValueField="business_type_id" AutoPostBack="true" AppendDataBoundItems="true">
                        <asp:ListItem Text="All Business Category" Value="0"></asp:ListItem>
                        </asp:DropDownList>
                        <asp:SqlDataSource runat="server" ID="SDSretali" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ProviderName="MySql.Data.MySqlClient"
                            SelectCommand=" SELECT business_type_id,business_type_name FROM finascop_business_type WHERE STATUS = 1"></asp:SqlDataSource>
                       </div>
                        <div class="col-12 col-sm-7 col-lg-5 col-xl-4 d-flex align-items-center form-group mb-2">
                        <label class="form-control-label text-nowrap mb-0 mr-2 tx-dark">Availability <span class="tx-danger">*</span> <i class="fa-regular tx-info fa-circle-info tooltipinfo popover-trigger" tabindex="0" data-toggle="popover" title="Themes Availability" data-html="true" data-content="For instant access, choose 'Ready to Apply' themes. For 'Available on Request' themes, submit a ticket or get budget approval."></i></label>
                        <asp:DropDownList ID="selthemes" runat="server" CssClass="form-control" AutoPostBack="true" AppendDataBoundItems="true">
                        <asp:ListItem Text="All Themes" Value="0"></asp:ListItem>
                        <asp:ListItem Text="Ready to Apply" Value="1"></asp:ListItem>
                        <asp:ListItem Text="Available on Request" Value="2"></asp:ListItem>
                        </asp:DropDownList>                       
                       </div>
                    </div>  
                     <uc1:ctrlMessagebox runat="server" ID="ctrlMessagebox" />
                    <div class="row row-sm mt-3">   
                        <asp:ListView runat="server"  ID="lstthemview" DataSourceID="SDSthemes">
                            <ItemTemplate>
                             <div class="col-sm-6 col-md-4 col-lg-3 mb-3">
                        <div class="demo_wrap">                    
                          <a id="" class="btnAddBanner card-item-img demo-inner <%#(IsActiveTheme(Eval("name").ToString()) ? "selectedtheme" : "") %>"  data-id='<%# Eval("id") %>' data-images='<%# Eval("imageList") %>' onclick="setThemeId('<%# Eval("id") %>', '<%# Eval("name").ToString().Replace("'", "\\'") %>')" themename="Classic" href="javascript:void(0)" data-toggle="modal" data-target=".theamview_popup">
                            <div class="demo_image">
                              <img src='<%# Eval("homeImage").ToString()%>' alt="" >
                            </div>
                            <div class="form-check">                    
                              <label class="tx-inverse"><%# Eval("title") %></label>                    
                            </div>
                          </a>
                        </div>
                        <div class="btnsc p-2 text-center">
                          <a id="btnAddBanner" class="btn btnAddBanner btn-primary <%#(IsActiveTheme(Eval("name").ToString()) ? " " : "btn-outline-primary")%>" data-id='<%# Eval("id") %>' data-images='<%# Eval("imageList") %>' onclick="setThemeId('<%# Eval("id") %>', '<%# Eval("name").ToString().Replace("'", "\\'") %>')"  href="javascript:void(0)" data-toggle="modal" data-target=".theamview_popup"><%#(IsActiveTheme(Eval("name").ToString()) ? "Current Theme" : "Preview") %></a>
                        </div>
                      </div>
                            </ItemTemplate>
                             <EmptyDataTemplate>
                                 <div class="col-12 d-flex justify-content-center">
                                     <div class="text-center">
                                        <img style="opacity: 0.9; max-width: 150px;" src="/content/images/ban-light.svg">
                                        <h6 class="mb-3">No record available</h6>
                                    </div>
                                 </div>                                
                            </EmptyDataTemplate>
                        </asp:ListView>  
                     <asp:HiddenField ID="hfSelectedThemeId" runat="server" ClientIDMode="Static" />
                     <asp:SqlDataSource runat="server" ID="SDSthemes" ProviderName="MySql.Data.MySqlClient" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                      SelectCommand="SELECT *,GROUP_CONCAT(ti.image ORDER BY ti.type Asc) AS imageList,MAX(CASE WHEN ti.type = 1 THEN ti.image END) AS homeImage,CASE WHEN t.name = @activeThemeName THEN 0 ELSE 1 END AS IsActiveSort FROM  theme t  INNER JOIN  theme_image ti ON t.id = ti.themeId WHERE t.status=1 and (@btype = 0 OR FIND_IN_SET(@btype, t.retailCategory) > 0 ) AND ((@availability = '1' AND IFNULL(NAME, '') != '')OR(@availability = '2' AND IFNULL(NAME, '') = '') OR (@availability NOT IN ('1', '2'))) AND (StoreGroupId = 0 OR StoreGroupId = @storegroupid) GROUP BY ti.themeId ORDER BY IsActiveSort ASC, type" OnSelecting="SDSthemes_Selecting">    
                         <SelectParameters>
                             <asp:ControlParameter  ControlID="selretalicategory" Name="btype"/>
                            <asp:ControlParameter  ControlID="selthemes" Name="availability"/>
                             <asp:Parameter Name="storegroupid" />
                             <asp:Parameter Name="activeThemeName" />
                         </SelectParameters>
                        </asp:SqlDataSource>
                    </div>
                      <div class="pagenation_listview p-3">
                        <asp:DataPager ID="DataPager1" runat="server" PageSize="12"
                            PagedControlID="lstthemview">
                            <Fields>
                                <asp:NextPreviousPagerField PreviousPageText="<" FirstPageText="<<" ShowPreviousPageButton="false"
                                    ShowFirstPageButton="false" ShowNextPageButton="false" ShowLastPageButton="false"
                                    ButtonCssClass="btn btn-default" RenderNonBreakingSpacesBetweenControls="false" RenderDisabledButtonsAsLabels="false" />
                                <asp:NumericPagerField ButtonType="Link" CurrentPageLabelCssClass="btn btn-primary disabled" RenderNonBreakingSpacesBetweenControls="false"
                                    NumericButtonCssClass="btn btn-default" ButtonCount="5" NextPageText="..." NextPreviousButtonCssClass="btn btn-default" />
                                <asp:NextPreviousPagerField NextPageText=">" LastPageText=">>" ShowNextPageButton="false"
                                    ShowLastPageButton="false" ShowPreviousPageButton="false" ShowFirstPageButton="false"
                                    ButtonCssClass="btn btn-default" RenderNonBreakingSpacesBetweenControls="false" RenderDisabledButtonsAsLabels="false" />
                            </Fields>
                        </asp:DataPager>
                    </div>
                  </div>
                </div>
    <!--card-->
     <!-- MODAL thumb slider -->
          <div class="modal fade theamview_popup" tabindex="-1" role="dialog" aria-labelledby="theamview_popupLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-full-w">
              <div class="modal-content bd-0 tx-14">
                <div class="modal-header pt-0 pb-0">
                  <div class="slider-action">
                      <a class="applytheme" data-toggle="modal" href="#themealert">
                       <img alt="close popup" class="img-fluid" src="/Content/images/icon/apply_theme_icon.svg">
                        <span>Apply</span>
                      </a>
                      <a class="opentheme" target="_blank" href="/Tenant/Themesview.aspx">
                      <img alt="close popup" class="img-fluid" src="/Content/images/icon/new_window_icon.svg">
                      <span>Open</span>
                    </a>
                    <a href="javascript:void(0)" class="close position-relative" data-dismiss="modal" aria-label="Close">
                      <img alt="close popup" class="img-fluid" src="/Content/images/icon/close_popup_W_icon.svg">
                      <span>Close</span>
                    </a>
                  </div>
                </div>
                <div class="modal-body">
                  <div class="theme_slider_sec">
                    <div class="swiper themeslider">								
                      <div class="swiper-wrapper" id="modalImageContainer">                      
                                                                        
                      </div>                                          
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
                                <asp:LinkButton ID="lbtTheme" runat="server" CssClass="btn btn-primary" OnClick="lbtTheme_Click">
                                    <asp:Label runat="server" ID="lbthemename"></asp:Label></asp:LinkButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>                 
<script>
    $(function () {
        $('.txtrichtext').summernote({
            height: 165
        });
        $('.txtrichtext').on('summernote.blur', function () {
            $(this).html($(this).summernote('code'));
        });
    })
    $(document).ready(function () {       
        $('.select2-show-search').select2();
        const themesliderswiper = new Swiper('.themeslider', {
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

        var currentThemeId;
        $('.btnAddBanner').click(function () {
            var currentThemeId = $(this).data('id');
            var isSelected = $(this).hasClass('selectedtheme'); // Check if selected

            // Append selectedtheme class to the URL if the theme is selected
            var openThemeUrl = '/Tenant/Themesview.aspx?themeId=' + currentThemeId;
            if (isSelected) {
                openThemeUrl += '&selectedtheme=true';
            }

            $('.opentheme').attr('href', openThemeUrl);
            setThemeId(currentThemeId);
        });
      

    });

    function setThemeId(themeId,themename) {
       // $('.theme_slider_sec').attr('data-themeId', themeId)
        let dataid = themeId;  // Set the required data-id value here

        $(".applytheme").removeClass("hide"); // Reset first

        $(".btnAddBanner.card-item-img.demo-inner.selectedtheme").each(function () {
            if ($(this).data("id") == dataid) {
                $(".applytheme").addClass("hide");
            }
        });
        var element = document.querySelector(`[data-id="${themeId}"]`);
        document.getElementById("hfSelectedThemeId").value = themeId;
        if (!element) {
            return;
        }
        var imageList = element.getAttribute('data-images');

        if (!imageList) {
            return;
        }
        var images = imageList.split(',');
        var modalBody = document.getElementById('modalImageContainer');
        modalBody.innerHTML = '';
        images.forEach(function (image) {
            var imageSlide = document.createElement('div');
            imageSlide.classList.add('swiper-slide');
            var imgElement = document.createElement('img');
            imgElement.classList.add('img-fluid');
            imgElement.src = image.trim();
            imageSlide.appendChild(imgElement);
            modalBody.appendChild(imageSlide);
        });
        // Reset swiper to start from the first slide
        const themesliderswiper = document.querySelector('.themeslider').swiper;
        themesliderswiper.slideTo(0, 0); // Go to the first slide without animation
        $('.theamview_popup').modal('show');
        updateThemeMessage(themename);

    }
    function updateThemeMessage(themeName) {
        let lblConform = document.getElementById('<%= lblconform.ClientID %>');
        let lblText = document.getElementById('<%= lbltext.ClientID %>');
        let lbtTheme = document.getElementById('<%= lbtTheme.ClientID %>');
        if (themeName.trim() !== "") {
            lblConform.innerText = "Implementation";
            lblText.innerText = "Theme change can impact the look and feel as well as the components displayed. Are you sure you want to change the theme?";
            lbtTheme.innerText = "Ok";
        } else {
            lblConform.innerText = "Manual Implementation Required";
            lblText.innerText = "This design requires custom integration. If you like this design, we will integrate this for you. One of our design support executives will contact you and update the portal with your consent.";
            lbtTheme.innerText = "I Like the Design Contact Me";
        }
    }

</script>
     <style>
        .select2-container {
             width: 100% !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
                display: block;
        }

        .select2-container.select2-container--open {
              z-index: 1050;
            }
         .modal + .modal {
             z-index: 1051;
         }
         .modal-backdrop + .modal-backdrop {
            z-index: 1050;
         }
    </style>
</asp:Content>
