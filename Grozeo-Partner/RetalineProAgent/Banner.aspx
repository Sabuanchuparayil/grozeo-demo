<%@ Page Language="C#" AutoEventWireup="true" Async="true" MasterPageFile="~/AgentMaster.Master" CodeBehind="Banner.aspx.cs" Inherits="RetalineProAgent.Banner" %>

<asp:Content ContentPlaceHolderID="cpBreadcrumb" runat="server">
    <li class="breadcrumb-item"><a href="/">Home</a></li>
    <li class="breadcrumb-item"><a href="/navigations/storeconfig">Settings</a></li>
    <li class="breadcrumb-item"><a href="/navigations/appearance">Appearance</a></li>
    <li class="breadcrumb-item active" aria-current="page">Banner</li>
</asp:Content>

<asp:Content ContentPlaceHolderID="cpTitle" runat="server" ID="cTitle"><h6 class="slim-pagetitle">Banner</h6></asp:Content>

<asp:Content runat="server" ContentPlaceHolderID="cpMainContent">

    <asp:PlaceHolder ID="plcBannerList" runat="server">
    <div class="section-wrapper mg-t-20">
          <label class="section-title">Store Banners</label>
        <div class="float-right"><asp:LinkButton runat="server" ID="btnAddBanner" OnClick="btnAddStore_Click" CssClass="btn btn-block btn-info btn-sm"><i class="ion-home"></i> Add Banner</asp:LinkButton></div>
          <p class="mg-b-20 mg-sm-b-40"><small class="mg-b-20 mg-sm-b-40">Add banner advertisements to the homepage. If no banner is present, the default banner will load.</small></p>

          <div class="table-responsive">
    <asp:Repeater ID="rptOwnbanners" DataSourceID="SDSOwnBanners" runat="server" OnItemDataBound="rptOwnbanners_ItemDataBound">
        <HeaderTemplate><div class="row"></HeaderTemplate>
        <ItemTemplate>
            <div class="col-md-1"><asp:LinkButton runat="server" OnClientClick="return confirm('Are you sure you want to delete this banner?');" OnClick="lbtnDelBanner_Click" ID="lbtnDelBanner" itemid='<%# Eval("adv_id") %>' ForeColor="#dc3545" imgurl='<%# Eval("adv_imageurl") %>'><i class="fa fa-window-close"></i></asp:LinkButton></div>
            <div class="col-md-11"><img style="width: 100%; max-width: 100%; solid 1px gray" src="<%# Eval("adv_imageurl") %>" /></div>
        </ItemTemplate>
        <SeparatorTemplate><hr /></SeparatorTemplate>
        <FooterTemplate></div></FooterTemplate>
    </asp:Repeater>
                  <asp:PlaceHolder ID="plcNoBanner" runat="server" Visible="false"><p class="mg-b-20 mg-sm-b-40">No banner added yet to your store</p></asp:PlaceHolder>
              
            <asp:SqlDataSource ID="SDSOwnBanners" runat="server"  ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                SelectCommand="SELECT a.adv_id, a.adv_imageurl FROM app_advertisements a INNER JOIN app_adzones z ON a.adzone_id=z.adzone_id WHERE z.adzone_screen LIKE 'Home' AND z.adzone_type LIKE 'advertisement' AND a.storegroup_id=@storegroupid"
                ProviderName="MySql.Data.MySqlClient" OnSelecting="SDSHomeBanners_Selecting" ><SelectParameters><asp:Parameter Name="storegroupid" /></SelectParameters></asp:SqlDataSource>

          </div><!-- table-responsive -->
        </div><!-- section-wrapper -->

<div class="row" runat="server" visible="false">
    <div class="col-md-12 form-group">
        <div class="form-group">
            <h4>Default Banners</h4>
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <asp:CheckBox ID="chkOwnBannerOnly" runat="server"   />
                    <asp:Button ID="lbtnOwnBannerChange" runat="server" style="display:none;" OnClick="ownBannerOnly_CheckedChanged"></asp:Button>
                    <%--<input type="checkbox" class="custom-control-input" id="customSwitch1">--%>
                    <label class="custom-control-label" onclick="$('#<%= lbtnOwnBannerChange.ClientID %>').click();" for="<%= chkOwnBannerOnly.ClientID %>">Disable default banners</label><br />
                    <small>If enabled, the default banners will be displayed after the user-added banners. In the absence of a custom banner, the default banners will be displayed regardless of the option selected.</small>
                    <br />
                </div>
            </div>

        </div>
    </div>
</div>


    </asp:PlaceHolder>

    <asp:PlaceHolder ID="plcBannerSettings" Visible="false" runat="server">

        <div class="card card-body">

          <div class="form-layout">

              <label class="slim-card-title"><asp:Literal runat="server" ID="ltrAddTitle" Text="Add new store"></asp:Literal></label>
          <div><small class="mg-b-20 mg-sm-b-40">Please ensure the branch location selected in map using the button 'Load Map'.</small></div>

                    <asp:Panel ID="pnlUploadBanner1" runat="server" CssClass="row">
                          <asp:Panel ID="pnlBanner1FileUpload" runat="server" CssClass="col-md-3"><label>Banner * </label>
                              <asp:FileUpload ID="FileUpload1" CssClass="form-control form-control-sm" runat="server" /></asp:Panel>
                          <asp:Panel ID="pnlUploadBanner1BType" runat="server" CssClass="col-md-3"><label>Business Type</label>
                              <asp:DropDownList ID="selBanner1BusinessType" OnDataBound="selBanner1BusinessType_DataBound" DataSourceID="SDSBusinessType" AppendDataBoundItems="true" DataTextField="business_type_name" DataValueField="business_type_id" AutoPostBack="true" runat="server" CssClass="form-control form-control-sm"><asp:ListItem Text="All Business Types" Value="0"></asp:ListItem></asp:DropDownList></asp:Panel>
                          <asp:Panel ID="pnlUploadBanner1RType" runat="server" CssClass="col-md-3"><label>Retail Type</label>
                              <asp:DropDownList ID="selBanner1RetailType" AppendDataBoundItems="true" DataTextField="business_type_name" DataValueField="business_type_id" runat="server" CssClass="form-control form-control-sm"><asp:ListItem Text="All Retail Types" Value="0"></asp:ListItem></asp:DropDownList></asp:Panel>
                          <div class="col-md-2"><label>&nbsp;</label><asp:LinkButton runat="server" Text="Upload" OnClick="btnUploadBanner_Click" ID="btnUploadBanner1" CssClass="btn btn-block btn-success btn-sm"></asp:LinkButton></div>
                          <div class="col-md-1"><label>&nbsp;</label><a href="/Tenant/banner" class="btn btn-block btn-secondary btn-sm">Cancel</a></div>

                      </asp:Panel>

<asp:SqlDataSource ID="SDSBusinessType" runat="server" SelectCommand="SELECT bt.business_type_name, bt.business_type_id FROM finascop_business_type bt INNER JOIN finascop_branch_group_business_type  gbt ON gbt.business_type_id = bt.business_type_id
    WHERE gbt.store_group_id =@storegroupid" ConnectionString="<%$ ConnectionStrings:mySqlConnection %>"
                ProviderName="MySql.Data.MySqlClient" OnSelecting="SDSHomeBanners_Selecting" ><SelectParameters><asp:Parameter Name="storegroupid" /></SelectParameters></asp:SqlDataSource>


            <asp:Label ID="lblMessage" Font-Bold="true" runat="server"/>

          </div><!-- form-layout -->
        </div>


<br />


    </asp:PlaceHolder>



</asp:Content>
