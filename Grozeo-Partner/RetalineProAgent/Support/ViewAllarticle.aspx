<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="ViewAllarticle.aspx.cs" Inherits="RetalineProAgent.Support.ViewAllarticle" %>

<% if(String.IsNullOrEmpty(Request.QueryString["unitid"])) { %>
<!--col-12-->
<asp:PlaceHolder ID="plcunit" Visible="true" runat="server">
    <div id="loadcontet" class="col-12">       
        <div class="row row-sm">
            <div class="col-12 mb-4">
                <a href="javascript:void(0)" class="backbtn"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
                <div class="suprttitle">
                    <h3 class="mb-2 tx-dark tx-16">Articles</h3>
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
      <a href="javascript:void(0)" class="backbtn"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
    </div>
  </div><!--col-12-->
  
    <div id="loadcontet" class="col-12">
    <div class="row row-sm">     
        <div class="col-12 mb-4">
            <div class="suprttitle">
                <h3 class="mb-2 tx-dark tx-16">Articles</h3>
                <a class="mb-2" href="">View All Articles</a>
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
                    FROM `support_article` sa INNER JOIN `support_unit` sc ON sc.id=sa.unitId WHERE sa.chapterId=@Id">
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