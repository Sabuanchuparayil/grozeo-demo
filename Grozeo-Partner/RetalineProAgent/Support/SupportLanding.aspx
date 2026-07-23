<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="SupportLanding.aspx.cs"  Inherits ="RetalineProAgent.Support.SupportLanding" %>
<asp:PlaceHolder ID="plcContent"  runat="server">
<% if(String.IsNullOrEmpty(Request.QueryString["unitid"])) { %>
<div class="col-12 mb-4">
    <div class="swiper supportSlider">
        <div class="swiper-wrapper">
            <asp:Repeater ID="rptsuport" DataSourceID="SDSSupport" runat="server">
                 <ItemTemplate>
                     <div class="swiper-slide unit_1 <%# Eval("description") %> ">
<%--                         <asp:LinkButton runat="server" ID="btnunit" OnClick="btnunit_Click" CssClass="support_widgets d-flex flex-wrap align-items-center justify-content-center h-100 w-100"></asp:LinkButton>--%>
                    <a href="javascript:void(0)" data-href="/support/supportLanding?unitid=<%# Eval("id") +  (!string.IsNullOrEmpty(Request.QueryString["search"]) ? "&search=" + Request.QueryString["search"] : "") %>" class="support_widgets d-flex flex-wrap align-items-center justify-content-center h-100 w-100">
                        <span class="titleicon wd-50 ht-50 rounded-circle mb-2 bg-white d-flex justify-content-center align-items-center">
                            <i class="fa-thin fa-cart-shopping tx-24"></i>
                        </span>
                        <h6 class="mb-0 w-100 text-center tx-13"><%# Eval("name") %></h6>
                    </a>
                </div>
                <!--swiper-slide-->
                 </ItemTemplate>
                <footertemplate>
                    <asp:Label ID="defaultItem" CssClass="noitem" runat="server"
                        Visible='<%# rptsuport.Items.Count == 0 %>' Text="No items found" />
                </footertemplate>
            </asp:Repeater>
           <asp:SqlDataSource runat="server" ID="SDSSupport"  ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT su.id,su.name,su.description FROM support_unit su where (trim(ifnull(@sr, '')) like '' or su.name like CONCAT('%', @sr, '%'))"> 
                 <selectparameters>                     
                  <asp:QueryStringParameter QueryStringField="search" Name="sr" ConvertEmptyStringToNull="false" DbType="String" DefaultValue="" />
              </selectparameters>
            </asp:SqlDataSource>  
        </div>

        <div class="swiperButton swiper-button-next"></div>
        <div class="swiperButton swiper-button-prev"></div>
    </div>
    <!-- <div class="swiperbutton">
     <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>
    </div> -->
</div>
<!--col-12-->
<asp:PlaceHolder ID="plcunit" Visible="true" runat="server">
    <div id="loadcontet" class="col-12">
        <div class="row row-sm">
            <div class="col-12 mb-4">
                <div class="suprttitle">
                    <h3 class="mb-2 tx-dark tx-16">FAQ</h3>
                    <a class="mb-2 viewallFAQ" href="javascript:void(0)" data-href="/support/ViewallFAQ?unitid=<%# Eval("id") +  (!string.IsNullOrEmpty(Request.QueryString["search"]) ? "&search=" + Request.QueryString["search"] : "") %>">View All FAQ</a>
                </div>
                <div class="accordion" id="faqAccordion">
                    <asp:Repeater ID="rptfaq" DataSourceID="SDSFAQ" runat="server">
                        <itemtemplate>
                              <div class="accordion_card">
                        <div class="accordion_card_header" id="heading<%#Eval("id")%>" data-toggle="collapse" data-target="#collapse<%#Eval("id")%>" aria-expanded="false" aria-controls="collapse<%#Eval("id")%>">
                            <h6 class="mb-1 position-relative"><%#Eval("name")%>
                            </h6>
                            <div class="introtext">
                                <p><%#Eval("content")%></p>
                            </div>
                        </div>

                        <div id="collapse<%#Eval("id")%>" class="collapse" aria-labelledby="heading<%#Eval("id")%>" data-parent="#faqAccordion">
                            <div class="accordion_card_body">
                                <p><%#Eval("content")%></p>
                                <!-- <span class="cont_less" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">Less</span> -->
                            </div>
                        </div>
                    </div>
                    <!--accordion_card-->  
                        </itemtemplate>
                        <footertemplate>
                            <asp:Label ID="Label1" CssClass="noitem" runat="server"
                                Visible='<%# rptsuport.Items.Count == 0 %>' Text="No items found" />
                        </footertemplate>
                    </asp:Repeater>
                    <asp:SqlDataSource runat="server" ID="SDSFAQ" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ProviderName="MySql.Data.MySqlClient"
                        SelectCommand="SELECT sq.id,sq.name,sq.content FROM `support_question` sq WHERE isFeaturedQuestion=1  AND status=1 and (trim(ifnull(@sr, '')) like '' or sq.name like CONCAT('%', @sr, '%')) LIMIT 0,3">
                        <selectparameters>
                            <asp:QueryStringParameter QueryStringField="search" Name="sr" ConvertEmptyStringToNull="false" DbType="String" DefaultValue="" />
                        </selectparameters>
                    </asp:SqlDataSource>                               
                </div>   <!--accordion faqAccordion-->
            </div>
            <!--col-12-->

            <div class="col-12 mb-4">
                <div class="suprttitle">
                    <h3 class="mb-2 tx-dark tx-16">Articles</h3>
                    <a class="mb-2 viewallarticle" href="javascript:void(0)" data-href="/support/ViewAllarticle?unitid=<%# Eval("id") +  (!string.IsNullOrEmpty(Request.QueryString["search"]) ? "&search=" + Request.QueryString["search"] : "") %>">View All Articles</a>
                </div>
                <ul class="article_link m-0 p-0">
                    <asp:Repeater ID="rptArticle" DataSourceID="SDSarticle" OnItemDataBound="rptArticle_ItemDataBound" OnDataBinding="rptArticle_DataBinding" runat="server">
                        <itemtemplate>
                            <li>
                                <h6 class="mb-1 position-relative"><%#Eval("name") %></h6>
                                <div class="introtext">
                                    <p class="mb-0"><%# RetalineProAgent.Service.Common.StripHTML(Eval("content").ToString()) %></p>
                                    <a class="articlecontent" href="javascript:void(0)" data-href="/support/supportLanding?articleId=<%#Eval("id") %>">Read</a>
                                </div>
                            </li>
                        </itemtemplate>
                    </asp:Repeater>
                    <asp:SqlDataSource runat="server" ID="SDSarticle" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ProviderName="MySql.Data.MySqlClient"
                        SelectCommand="SELECT sa.id, sa.name,sa.content FROM `support_article` sa WHERE isFeaturedArticle=1  AND sa.status=1 LIMIT 0,3"></asp:SqlDataSource>
                </ul>
            </div>
        </div>

    </div>
</asp:PlaceHolder>
<script>
      var includeDiv = $("#suportcontent");
      var loader = '<div class="d-flex align-items-center justify-content-center w-100"><div class="loader"></div></div>';

      $('.support_widgets').on('click', function(e) {  
        $("#suportcontent").html(loader);
        var href = $(this).data('href');
          e.preventDefault();
          includeDiv.load(href);
          // $('#placeholderOne').css('display', 'none');
          //console.log("clicked");
          //console.log(loader);        
          //console.log(href);
          //console.log(includeDiv);        
      });
    $('.articlecontent').on('click', function (e) {
        e.preventDefault();    
        $("#suportcontent").html(loader);
        var href = $(this).data('href');
        e.preventDefault();
        includeDiv.load(href);
    });   
    $('.viewallarticle').on('click', function (e) {
        e.preventDefault();
        $("#suportcontent").html(loader);
        var href = $(this).data('href');
        e.preventDefault();
        includeDiv.load(href);
    }); 
    $('.viewallFAQ').on('click', function (e) {
        e.preventDefault();
        $("#suportcontent").html(loader);
        var href = $(this).data('href');
        e.preventDefault();
        includeDiv.load(href);
    });
      var swiper = new Swiper(".supportSlider", {
          slidesPerView: 3,
          spaceBetween: 10,
          navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
          },
          
      });

</script>

<% }
    else {
    %>
<div class="col-12 mb-4">
    <div class="title_sec d-flex align-items-center justify-content-between">
      <div id="supportunit"  runat="server" class="support_title_wrap d-flex align-items-center">
        <span class="titleicon wd-50 ht-50 rounded-circle bg-light d-flex justify-content-center align-items-center mr-3">
          <i class="fa-thin fa-cart-shopping tx-24"></i>
        </span>
        <div class="support_title">
          <h6 class="mb-1 w-100 tx-14 tx-medium "><asp:Literal runat="server" ID="ltrunitname"></asp:Literal></h6>
        </div>
      </div>
      <a href="javascript:void(0)" class="backbtn"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
    </div>
  </div><!--col-12-->

  <div class="col-12 mb-4 chapterSliderWrap">
    <div class="swiper chapterSlider border-bottom">
      <div class="swiper-wrapper"> 
           <asp:Repeater ID="rptchapters" DataSourceID="SDSchapters" OnDataBinding="rptchapters_DataBinding"  runat="server">
                 <ItemTemplate>
                     <div class="swiper-slide <%# GetActiveClass(Eval("id")) %> w-auto">
                         <span class="px-2 py-2 slidercontent"  data-href="/support/supportLanding?unitid=<%# Eval("unitId") %>&chapterid=<%# Eval("id") %>"
                             data-mainclass=".dynamic_slider_content" data-target="#chapter_<%#Eval("name") %>"><%#Eval("name") %></span>
                     </div>
                 </ItemTemplate>
           </asp:Repeater>
          <asp:SqlDataSource runat="server" ID="SDSchapters"  ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT sc.id,sc.name,sc.unitId FROM `support_chapter`sc WHERE sc.unitId=@Id">
              <selectparameters>
                  <asp:QueryStringParameter QueryStringField="unitid" Name="Id" />
              </selectparameters>
            </asp:SqlDataSource>
      </div>
    </div>
    <div class="swiperButton swiper-button-next"></div>
      <div class="swiperButton swiper-button-prev"></div>
  </div>
    <div id="loadcontet" class="col-12">

    <div class="row row-sm">
      <div class="col-12 mb-4">
        <div class="suprttitle">
          <h3 class="mb-2 tx-dark tx-16">FAQ</h3>
          <a class="mb-2 viewallFAQ" href="javascript:void(0)" data-href="/support/ViewallFAQ?unitid=<%=Request.QueryString["unitid"] %>&chapterid=<%# Eval("id") +  (!string.IsNullOrEmpty(Request.QueryString["search"]) ? "&search=" + Request.QueryString["search"] : "") %>">View All FAQ</a>
        </div>
        <div class="accordion" id="faqAccordion">
         <asp:Repeater ID="rptchapterFAQ" DataSourceID="SDSchapterFAQ" runat="server">
               <ItemTemplate>
                   <div class="accordion_card">
                       <div class="accordion_card_header" id="heading_<%#Eval("id")%>" data-toggle="collapse" data-target="#collapse_<%#Eval("id")%>" aria-expanded="false" aria-controls="collapse_<%#Eval("id")%>">
                           <h6 class="mb-1 position-relative"><%#Eval("name")%>
                           </h6>
                           <div class="introtext">
                               <p><%#Eval("content")%></p>
                           </div>
                       </div>

                       <div id="collapse_<%#Eval("id")%>" class="collapse" aria-labelledby="heading_<%#Eval("id")%>" data-parent="#faqAccordion">
                           <div class="accordion_card_body">
                               <p><%#Eval("content")%></p>
                               <!-- <span class="cont_less" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">Less</span> -->
                           </div>
                       </div>
                   </div>
                   <!--accordion_card-->  
               </ItemTemplate>
              <footertemplate>
                            <asp:Label ID="Label2" CssClass="noitem" runat="server"
                                Visible='<%# rptchapterFAQ.Items.Count == 0 %>' Text="Content will update soon..." />
                        </footertemplate>
         </asp:Repeater> 
             <asp:SqlDataSource runat="server" ID="SDSchapterFAQ"  ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ProviderName="MySql.Data.MySqlClient"
                SelectCommand="SELECT sq.id,sq.unitId, sq.name,sq.content FROM `support_question` sq WHERE sq.unitId=@Id LIMIT 0,3">
              <selectparameters>
                  <asp:QueryStringParameter QueryStringField="unitid"  Name="Id" />
              </selectparameters>
            </asp:SqlDataSource>     
        </div><!--accordion faqAccordion-->
        
      </div><!--col-12-->
        <div class="col-12 mb-4">
            <div class="suprttitle">
                <h3 class="mb-2 tx-dark tx-16">Articles</h3>
                <a class="mb-2 viewallarticle" href="javascript:void(0)" data-href="/support/ViewAllarticle?unitid=<%= Request.QueryString["unitid"] %>&chapterid=<%# Eval("id") +  (!string.IsNullOrEmpty(Request.QueryString["search"]) ? "&search=" + Request.QueryString["search"] : "") %>">View All Articles</a>
            </div>
        <ul class="article_link m-0 p-0">
            <asp:Repeater ID="rptchapterarticle" OnDataBinding="rptchapterarticle_DataBinding" OnItemDataBound="rptchapterarticle_ItemDataBound" DataSourceID="SDSchapterarticle" runat="server">                <itemtemplate>
                    <li>
                        <h6 class="mb-1 position-relative"><%#Eval("name") %></h6>
                        <div class="introtext">
                            <p class="mb-0"><%# RetalineProAgent.Service.Common.StripHTML(Eval("articleContent").ToString()) %></p>
                            <a class="articleload" href="javascript:void(0)" data-href="/support/supportLanding?unitid=<%# Eval("unitId") %>&chapterid=<%# Eval("articleChapter") %>&articleid=<%# Eval("id") %>">Read</a>
                        </div>
                    </li>
                </itemtemplate>
                 <footertemplate>
                            <asp:Label ID="Label3" CssClass="noitem" runat="server"
                                Visible='<%# rptchapterarticle.Items.Count == 0 %>' Text="Content will update soon..." />
                        </footertemplate>
            </asp:Repeater>
                <asp:SqlDataSource runat="server" ID="SDSchapterarticle" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ProviderName="MySql.Data.MySqlClient"
                    SelectCommand="SELECT sa.name as articlename,sa.id,sa.chapterId as articleChapter,sa.unitId,sa.content as articleContent,sc.name 
                    FROM `support_article` sa INNER JOIN `support_unit` sc ON sc.id=sa.unitId WHERE sa.chapterId=@Id LIMIT 0,3">
                    <selectparameters>
                        <asp:QueryStringParameter QueryStringField="chapterid" Name="Id" />
                    </selectparameters>
                </asp:SqlDataSource> 
        </ul>
      </div>
    </div>
  </div>  
  <script>
      var loader = '<div class="d-flex align-items-center justify-content-center w-100"><div class="loader"></div></div>';
      $('.backbtn').click(function (e) {
          e.preventDefault();
          $("#suportcontent").html(loader);
          $("#suportcontent").load("/Support/SupportLanding.aspx");
      });

      var ArticlesLoadDiv = $("#suportcontent");
      $('.articleload').on('click', function (e) {
          $("#suportcontent").html(loader);
          var href = $(this).data('href');
          e.preventDefault();
          ArticlesLoadDiv.load(href);
          console.log("clicked");
          console.log(loader);
          console.log(href);
          console.log(ArticlesLoadDiv);
      });

      $('.viewallarticle').on('click', function (e) {
          e.preventDefault();
          $("#suportcontent").html(loader);
          var href = $(this).data('href');
          console.log(href);
          e.preventDefault();
          includeDiv.load(href);
      });
      $('.viewallFAQ').on('click', function (e) {
          e.preventDefault();
          $("#suportcontent").html(loader);
          var href = $(this).data('href');
          e.preventDefault();
          console.log(href);
          includeDiv.load(href);
      });

      var chapterSwiper = new Swiper(".chapterSlider", {
          slidesPerView: "auto",
          loop: false,
          spaceBetween: 10,
          centeredSlides: false,
          initialSlide: 0,
          on: {
              click(event) {
                  console.log('event.target', this.clickedIndex);
                  chapterSwiper.slideTo(this.clickedIndex);
                  document.querySelectorAll('.swiper-slide').forEach((slide, index) => {
                      slide.classList.toggle('active', index === this.clickedIndex);
                  });
              },
          },
          navigation: {
              nextEl: ".swiper-button-next",
              prevEl: ".swiper-button-prev",
          },
      });
      var includeDiv = $("#suportcontent");

      $('.slidercontent').on('click', function (e) {
          $("#suportcontent").html(loader);
          var href = $(this).data('href');
          e.preventDefault();
          includeDiv.load(href);
      });


      //$(function () {
      //    $(".slidercontent").click(function () {

      //        //var target = $(this).data("target");
      //        //var mainclass = $(this).data("mainclass");
      //        //$(mainclass).addClass('hide');
      //        //$(target).removeClass('hide');



      //    });
      //});

  </script>


  <% } %>
</asp:PlaceHolder>
<asp:PlaceHolder ID="plcarticle"  runat="server">
     <%--                  --------------- article contentent load-----------------%>
                         <% if(!String.IsNullOrEmpty(Request.QueryString["articleid"])) { %>
                             <div class="col-12 mb-4">
                             <div class="title_sec d-flex align-items-center justify-content-between">
                                 <div id="articlesupport" runat="server" class="support_title_wrap d-flex align-items-center">
                                     <span class="titleicon wd-50 ht-50 rounded-circle bg-light d-flex justify-content-center align-items-center mr-3">
                                         <i class="fa-thin fa-cart-shopping tx-24"></i>
                                     </span>
                                     <div class="support_title">
                                         <h6 class="mb-1 w-100 tx-14 tx-medium "><asp:Literal runat="server" ID="ltrfrariticle"></asp:Literal></h6>
                                     </div>
                                 </div>
                                 <a href="javascript:void(0)" class="backbtn"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
                             </div>
                         </div>
                         <% } %>                         
                         <!--col-12-->

                         <div class="col-12 mb-3">
                             <h3 class="mb-3 tx-dark tx-14"><asp:Literal runat="server" ID="ltrfrarticlename"></asp:Literal></h3>
                            <asp:Literal runat="server" ID="ltrfrcontent"></asp:Literal>
                         </div>
                         <!--col-12-->

                         <script>
                             var loader = '<div class="d-flex align-items-center justify-content-center w-100"><div class="loader"></div></div>';
                             $('.backbtn').click(function (e) {
                                 e.preventDefault();
                                 $("#suportcontent").html(loader);
                                 $("#suportcontent").load("/support/supportLanding");
                             });
                         </script>

                                 <%--end of article-------%>	
    <style>
        .dynamical_support img {
          max-width: 100%;
          height: auto;
          max-height: 100%;
        }
        .noitem {
            border:none;
        }
    </style>
</asp:PlaceHolder>








  