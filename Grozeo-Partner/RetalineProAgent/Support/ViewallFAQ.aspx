<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="ViewallFAQ.aspx.cs" Inherits="RetalineProAgent.Support.ViewallFAQ" %>

<% if(String.IsNullOrEmpty(Request.QueryString["unitid"])) { %>
<!--col-12-->
<asp:PlaceHolder ID="plcunit" Visible="true" runat="server">
    <div id="loadcontet" class="col-12">
        <div class="row row-sm">           
            <div class="col-12 mb-4">
                 
                <div class="suprttitle justify-content-between"> 
                    <h3 class="mb-2 tx-dark tx-16">FAQ</h3>
                    <a href="javascript:void(0)" class="backbtn mb-2"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
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
                                Visible='<%# rptfaq.Items.Count == 0 %>' Text="No items found" />
                        </footertemplate>
                    </asp:Repeater>
                    <asp:SqlDataSource runat="server" ID="SDSFAQ" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>" ProviderName="MySql.Data.MySqlClient"
                        SelectCommand="SELECT sq.id,sq.name,sq.content FROM `support_question` sq WHERE isFeaturedQuestion=1  AND status=1 and (trim(ifnull(@sr, '')) like '' or sq.name like CONCAT('%', @sr, '%')) LIMIT 0,3">
                        <selectparameters>
                            <asp:QueryStringParameter QueryStringField="search" Name="sr" ConvertEmptyStringToNull="false" DbType="String" DefaultValue="" />
                        </selectparameters>
                    </asp:SqlDataSource>
                </div>
                <!--accordion faqAccordion-->
            </div>
            <!--col-12-->
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
    </div>
  </div><!--col-12-->


    <div id="loadcontet" class="col-12">
    <div class="row row-sm">
      <div class="col-12 mb-4">
        <div class="suprttitle justify-content-between">
          <h3 class="mb-2 tx-dark tx-16">FAQ</h3>
           <a href="javascript:void(0)" class="backbtn"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
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
                SelectCommand="SELECT sq.id,sq.unitId, sq.name,sq.content FROM `support_question` sq WHERE sq.unitId=@Id">
              <selectparameters>
                  <asp:QueryStringParameter QueryStringField="unitid"  Name="Id" />
              </selectparameters>
            </asp:SqlDataSource>     
        </div><!--accordion faqAccordion-->
        
      </div><!--col-12-->       
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
