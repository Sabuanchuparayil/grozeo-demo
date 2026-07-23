<%@ Page Language="C#" AutoEventWireup="true" Async="true" MasterPageFile="~/Tenant/TenantMaster.master" CodeBehind="Banner.aspx.cs" Inherits="RetalineProAgent.Appearance.Banner" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <%--<li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/storeconfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/appearance">Appearance</a></li>
    <li class="breadcrumb-item active" aria-current="page">Banner</li>--%>
    <a href="/Navigations/Appearance"><i class="fa fa-reply mr-2" aria-hidden="true"></i>Back</a>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle">
    <div>
        <h6 class="slim-pagetitle m-0">Banner</h6>
        <p class="mb-0">Engaging Visuals</p>
    </div>
</asp:Content>
 
<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <div class="card">
        <div class="card-body p-3 shadow_top">
          <div class="d-flex justify-content-lg-end">
              <a id="cpMainContent_btnAddBanner" class="btn btn-primary" href="/Tenant/Appearance/BannerSettings">Create New Banner<i class="ion-android-add-circle ml-1"></i></a>
            </div>
        
          <!-- <p class="mg-b-20 mg-sm-b-40">Add banner advertisements to the homepage. If no banner is present, the default banner will load.</p> -->

          <div class="banner_list_wrap mt-3 pt-2">
            <div class="row row-sm">
                <asp:Repeater ID="rptOwnbanners" DataSourceID="SDSOwnBanners" runat="server">
                    <ItemTemplate>
              <div class="col-12 col-sm-6 mb-3 pb-2">
                <div class="banner_list border p-3 position-relative rounded">
                    <asp:LinkButton runat="server" CssClass="clear_banner" OnClientClick="return confirm('Are you sure you want to delete this banner?');" OnClick="lbtnDelBanner_Click" ID="lbtnDelBanner" itemid='<%# Eval("adv_id") %>' ForeColor="#dc3545" imgurl='<%# Eval("adv_imageurl") %>'>
                        <i class="fa fa-times-circle tx-24-force" aria-hidden="true"></i></asp:LinkButton>
                  
                    <%--<div class="slim-pageheader">
                    <h6 class="title text-dark pr-3"><%# Eval("adv_title") %></h6>--%>
                    <%--<a href="" class="messages-compose mb-2 text-danger"><i class="icon ion-compose mr-1"></i>Edit</a>--%>
                        <%--<asp:HyperLink runat="server" CssClass="messages-compose mb-2 text-danger"  NavigateUrl='<%# string.Format("~/Appearance/BannerSettings.aspx?advId={0}", Eval("adv_id")) %>' Text="Edit Date"><i class="icon ion-compose mr-1"></i>Edit</asp:HyperLink>
                  </div>--%>

                    
                    <%--<asp:HyperLink runat="server" Text="Edit Date" HeaderStyle-BackColor="#DEE2E6" ItemStyle-BackColor="White" NavigateUrl="~/Appearance/BannerSettings" DataNavigateUrlFields="adv_id" DataNavigateUrlFormatString="~/BannerSettings?id={0}" />--%>
                  
                    <div class="banner_img text-center">
                    <img src="<%# Eval("adv_imageurl") %>"">
                  </div>
                    <div class="banner_disc mt-2">
                        <p class="mb-0">
                            Date: <b><%# Eval("startDate") %></b> to: <b><%# Eval("endDate") %></b><br />
                            Banner Location: <b><%# Eval("adzone_name") %></b><br />
                            Show In: <b><%# Eval("categoryType") %></b> - <b><%# Eval("businessCatName") %></b><br />
                            <%--Banner Link: <b><%# Eval("adv_offer") %></b> - <%# Eval("offerValue") %> - <%# Eval("itemName") %><br />--%>
                            <%--Banner Link: <b><%# Eval("adv_offer") %></b> - <%# Eval("offerValue") %> - <%# (String.IsNullOrEmpty(Eval("offerValue").ToString()) ? "" : Eval("itemName")) %><br />--%>
                            <%# 
                                !String.IsNullOrEmpty(Eval("offerDisplayName")?.ToString()) 
                                ? $"Banner Link: <b>{Eval("offerDisplayName")}</b> - {Eval("offerValue")}<br />" 
                                : "" 
                            %>
                    </div>
                </div><!--banner_list-->
                
              </div><!--col-lg-6-->

                    </ItemTemplate>
                </asp:Repeater>

              <div class="col-12 col-sm-6 mb-3 pb-2">
                <div class="banner_list border d-flex rounded">
                  
                  <a class="img_upload p-3 w-100 d-flex justify-content-center align-content-center flex-wrap" href="/Tenant/Appearance/BannerSettings">
                    <div class="btn_upload"></div>
                  </a>
                </div><!--banner_list-->
                
              </div><!--col-lg-6-->



            </div><!--row-->

            

            
          
          </div><!-- table-responsive -->
        </div><!-- card-body -->
    </div>

        
                <asp:SqlDataSource ID="SDSOwnBanners" runat="server"  ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT a.adv_id, a.adv_imageurl,a.adv_offer, CASE WHEN a.adv_offer = 'Product' THEN 'SKU' ELSE a.adv_offer END AS offerDisplayName, a.adv_offerType,adv_offerValueId, (SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_id=adv_offerValueId) AS itemName, 
                    CASE WHEN adv_offer='Product' THEN (SELECT stit_SKU FROM finascop_stock_itemmaster WHERE stit_id=adv_offerValueId)
                    WHEN adv_offer='Sub Category' THEN (SELECT sub_category FROM mypha_productsubcategory WHERE sub_category_id=adv_offerValueId) 
                    WHEN adv_offer='Brand' THEN (SELECT brand_name FROM mypha_productbrands WHERE brand_id=adv_offerValueId)
                    WHEN adv_offer='Offer' THEN CONCAT(adv_offerpercent,' ','%',' ','for',' ',adv_offerType)
                    WHEN adv_offer='Category' THEN (SELECT category_name FROM mypha_productcategory WHERE category_id=adv_offerValueId) 
                    WHEN adv_offer='Department' THEN (SELECT parent_category FROM mypha_productparent_category WHERE parent_category_id=adv_offerValueId) 
                    END AS offerValue, a.adv_startdate,DATE_FORMAT(adv_startdate,'%d-%m-%Y') AS startDate, a.adv_enddate,DATE_FORMAT(adv_enddate,'%d-%m-%Y') AS endDate, z.adzone_name, a.adv_title, a.adv_offerType, 
                    a.adv_applicable_category, CASE WHEN adv_applicable_category=1 THEN 'Business Category' WHEN adv_applicable_category=2 THEN 'Retail Category' END AS categoryType,
                    a.adv_applicable_category_value,IF(adv_applicable_category=1,(SELECT business_category_name FROM retaline_business_category 
                    WHERE business_category_id=adv_applicable_category_value), (SELECT business_type_name FROM finascop_business_type WHERE business_type_id=adv_applicable_category_value)) AS businessCatName
                    FROM app_advertisements a INNER JOIN app_adzones z ON a.adzone_id=z.adzone_id WHERE z.adzone_screen LIKE 'Home' AND z.adzone_type LIKE 'advertisement' AND a.storegroup_id=@storegroupid"
                ProviderName="MySql.Data.MySqlClient" OnSelecting="SDSHomeBanners_Selecting" >
                    <SelectParameters>
                        <asp:Parameter Name="storegroupid" />
                    </SelectParameters></asp:SqlDataSource>

<style>
          .banner_list.border .banner_disc{
            position: absolute;
            background:rgba(0, 0, 0, 0.6);
            overflow: hidden;
            width: 100%;
            height: 0;
            opacity: 0;
            transition: .5s ease;
            bottom: 0;
            left: 50%;
            -webkit-transform: translate(-50%, 0%);
            -ms-transform: translate(-50%, 0%);
            transform: translate(-50%, 0%); 
          }
          .banner_list.border .banner_disc p{
            color: white;
            padding: 10px;
          }
          .banner_list.border:hover .banner_disc {
            height:60%;
            opacity:1;
          }
          .banner_list.border {
            min-height: 200px;
          }
          .banner_list.border .btn_upload {
              background-position-y: 20px;
          }

          .banner_list .slim-pageheader{
            flex-direction: unset;
            padding: 0px!important;
          }
          .banner_list .slim-pageheader h6{
            max-width: 90%;
          }
          .banner_list .slim-pageheader .messages-compose{
            font-size: 0.875rem;
          }
        </style>
</asp:Content>
