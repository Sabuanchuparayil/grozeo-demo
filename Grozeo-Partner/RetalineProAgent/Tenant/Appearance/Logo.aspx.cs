using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.Cache;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Appearance
{
    public partial class Logo: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                LoadView();
            }

        }

        private void LoadView()
        {
            ImgPreview1.CssClass = "preview_img";
            ImgPreview1.ImageUrl = "";
            lbtnDelImg1.CssClass = "btn_rmv_remove";
            spnImgUpload1.Attributes["class"] = "btn_upload";
            ImgPreview2.CssClass = "preview_img";
            ImgPreview2.ImageUrl = "";
            lbtnDelImg2.CssClass = "btn_rmv_remove";
            spnImgUpload2.Attributes["class"] = "btn_upload";
            ImgPreview3.CssClass = "preview_img";
            ImgPreview3.ImageUrl = "";
            lbtnDelImg3.CssClass = "btn_rmv_remove";
            spnImgUpload3.Attributes["class"] = "btn_upload";

            int storegroupid = this.CurrentUser.StoreGroupId;
            string strSql = $"SELECT a.Id, a.Name, a.Theme, a.APIUrl, a.CanCheckout, a.CustomColor, a.FavIcoImage, " +
                $"Stuff((SELECT ',' + (t.HostAddress) FROM Host t WHERE a.Id LIKE t.TenantId FOR Xml Path('')), 1, 1, '') as hosts, " +
                $"a.LogoImage, a.LogoSmall, a.FavIcoImage, a.OnlinePaymentEnabled, a.ShowPWA, a.Status, a.StoreId as StoreGroupId, " +
                $"s.APICode, s.BusinessType, s.SecondaryBusinessTypes, s.DisplayName, s.CreatedBy, s.CreatedOn, s.DBConnectionString, s.GroupId, s.Id as StoreId, " +
                $"s.InventoryFile, s.MinMargin, s.Name as StoreName, s.Package, s.SelectSql, s.UpdatedOn, s.UpdatedBy, s.InventoryMapType, " +
                $" sb.Location, sb.Addr, sb.District, sb.[State], sb.Pin, sb.Lat, sb.Lang, sb.GST, sb.PAN, sb.APIBranchId, sb.BankBranch, sb.BankAddr, sb.BankIFSC, sb.BankName, sb.BankNo, sb.MapLocation " +
                $" FROM AppTenant a inner join Store s on a.Id=s.Tenantid left join StoreBranch sb on sb.StoreId=a.Id and sb.IsDefault = 1 WHERE a.Id={storegroupid}";//SDSStores.SelectCommand + " where a.Id = " + strEditId;

            if (!(Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent")))
                strSql += " AND a.Id in (SELECT m.StoreGroupId FROM User_UserRole_Mapping m INNER JOIN [User] u on u.Id=m.UserId WHERE u.Email like @user)";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("user", Page.User.Identity.Name));
            DataTable dt = DataService.GetDataTable(strSql, parmeters: prms);

            if (dt.Rows.Count > 0)
            {
                DataRow dr = dt.Rows[0];
                string strLogo = dr["LogoImage"].ToString();
                string strLogoSmall = dr["LogoSmall"].ToString();
                string strLogoFavIcon = dr["FavIcoImage"].ToString();
                //SiteTheme = dr["Theme"].ToString();
                if (!String.IsNullOrEmpty(strLogo))
                {
                    ImgPreview1.ImageUrl = strLogo;
                    //ImgPreview1.Attributes.Add("originalimg", strLogo);
                    ImgPreview1.CssClass = "preview_img it";
                    lbtnDelImg1.CssClass = "btn_rmv_remove rmv";
                    spnImgUpload1.Attributes["class"] = "btn_upload rmvbg";
                    //lblImgPreview1.Attributes.Add("onclick", "if(confirm('Are you sure you want to remove the image?')){$('#"+hidDelImg1.ClientID+"').val('1');}else{return false;}");
                    //lblImgPreview1.Attributes.Add("hiddenfld", hidDelImg1.ClientID);                        
                    //imgLogo.Visible = true;
                    //chkDelImgLogo.Visible = true;
                }
                if (!String.IsNullOrEmpty(strLogoSmall))
                {
                    ImgPreview2.ImageUrl = strLogoSmall;
                    //ImgPreview2.Attributes.Add("originalimg", strLogoSmall);
                    ImgPreview2.CssClass = "preview_img it";
                    lbtnDelImg2.CssClass = "btn_rmv_remove rmv";
                    spnImgUpload2.Attributes["class"] = "btn_upload rmvbg";
                    //lblImgPreview2.Attributes.Add("onclick", "if(confirm('Are you sure you want to remove the image?')){$('#" + hidDelImg2.ClientID + "').val('1');}else{return false;}");
                    //lblImgPreview2.Attributes.Add("hiddenfld", hidDelImg2.ClientID);
                    //imgLogoWhite.Visible = true;
                    //chkDelImgLogoWhite.Visible = true;
                }
                if (!String.IsNullOrEmpty(strLogoFavIcon))
                {
                    ImgPreview3.ImageUrl = strLogoFavIcon;
                    //ImgPreview2.Attributes.Add("originalimg", strLogoSmall);
                    ImgPreview3.CssClass = "preview_img it";
                    lbtnDelImg3.CssClass = "btn_rmv_remove rmv";
                    spnImgUpload3.Attributes["class"] = "btn_upload rmvbg";
                    //lblImgPreview2.Attributes.Add("onclick", "if(confirm('Are you sure you want to remove the image?')){$('#" + hidDelImg2.ClientID + "').val('1');}else{return false;}");
                    //lblImgPreview2.Attributes.Add("hiddenfld", hidDelImg2.ClientID);
                    //imgLogoWhite.Visible = true;
                    //chkDelImgLogoWhite.Visible = true;
                }

                //string strCustomColor = dr["CustomColor"].ToString();
                //if (!String.IsNullOrEmpty(strCustomColor))
                //    txtColor.Text = strCustomColor;

            }

        }
        protected async void btnUpload_Click(object sender, EventArgs e)
        {
            //Service.User user = UserService.GetCustomerByUsername(Page.User.Identity.Name);
            List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
            string strLogo = Guid.NewGuid().ToString();
            string LogoUrl = "", LogoUrlWhite = "";
            string strSqlLogo = "", strSqlLogo1="", strSqlLogo2 = "", strSqlLogo3 = "";
            //if (hidDelImg1.Value == "1")
            //{
            //    strSqlLogo1 = ", LogoImage=''";
            //    if (!String.IsNullOrEmpty(ImgPreview1.Attributes["originalimg"]))
            //        await Common.DeleteBlob(ImgPreview1.Attributes["originalimg"]);
            //}
            //if (hidDelImg2.Value == "1")
            //{
            //    strSqlLogo2 = ", LogoSmall=''";
            //    if (!String.IsNullOrEmpty(ImgPreview2.Attributes["originalimg"]))
            //        await Common.DeleteBlob(ImgPreview2.Attributes["originalimg"]);
            //}

            if (Imgupload1.HasFile)
            {
                string strExtention = System.IO.Path.GetExtension(Imgupload1.PostedFile.FileName);
                //string resultLogo = await Service.Common.CreateBlob(Imgupload1.PostedFile.InputStream, strLogo + $"_logo{strExtention}");
                string resultLogo = FileService.UploadMerchantImage(Imgupload1.PostedFile.InputStream, strLogo + $"_logo{strExtention}", "MerchantLogo");
                if (!string.IsNullOrEmpty(resultLogo))
                {
                    parmeters.Add(new KeyValuePair<string, object>("LogoImage", resultLogo));
                    strSqlLogo1 = ", LogoImage=@LogoImage";

                    ImgPreview1.ImageUrl = resultLogo;
                    //ImgPreview1.Visible = true;
                    //chkDelImgLogo.Visible = true;

                }
            }

            if (Imgupload2.HasFile)
            {
                string strExtention = System.IO.Path.GetExtension(Imgupload2.PostedFile.FileName);
                //string resultLogo = await Service.Common.CreateBlob(Imgupload2.PostedFile.InputStream, strLogo + $"_logo_white{strExtention}");
                string resultLogo = FileService.UploadMerchantImage(Imgupload2.PostedFile.InputStream, strLogo + $"_logo_white{strExtention}", "MerchantLogo");
                if (!string.IsNullOrEmpty(resultLogo))
                {
                    parmeters.Add(new KeyValuePair<string, object>("LogoSmall", resultLogo));
                    strSqlLogo2 = ", LogoSmall = @LogoSmall";

                    ImgPreview2.ImageUrl = resultLogo;
                    //ImgPreview2.Visible = true;
                    //chkDelImgLogoWhite.Visible = true;

                }
            }

            if (Imgupload3.HasFile)
            {
                string strExtention = System.IO.Path.GetExtension(Imgupload3.PostedFile.FileName);
                //string resultLogo = await Service.Common.CreateBlob(Imgupload3.PostedFile.InputStream, strLogo + $"_logo_white{strExtention}");
                string resultLogo = FileService.UploadMerchantImage(Imgupload3.PostedFile.InputStream, strLogo + $"_logo_white{strExtention}", "MerchantLogo");
                if (!string.IsNullOrEmpty(resultLogo))
                {
                    parmeters.Add(new KeyValuePair<string, object>("FavIcoImage", resultLogo));
                    strSqlLogo3 = ", FavIcoImage = @FavIcoImage";

                    ImgPreview3.ImageUrl = resultLogo;
                    //ImgPreview2.Visible = true;
                    //chkDelImgLogoWhite.Visible = true;

                }
            }

            //parmeters.Add(new KeyValuePair<string, object>("CustomColor", txtColor.Text));
            parmeters.Add(new KeyValuePair<string, object>("StoreGroupId", this.CurrentUser.StoreGroupId));
            string sql = $"UPDATE AppTenant SET CustomColor = CustomColor {strSqlLogo1 + strSqlLogo2 + strSqlLogo3} WHERE Id=@StoreGroupId; ";

            //sql += "UPDATE Store SET BusinessType=@BusinessType, Displayname=@Displayname, SecondaryBusinessTypes=@SecondaryBusinessTypes WHERE TenantId=@StoreGroupId;";
            int strresult = DataService.ExecuteSql(sql, parmeters: parmeters);
            Service.UserService.CachedDefaultUser = null;
            LoadView();

            var cacheService = new RedisCacheService();
            string cachekey = $"Retl.AppTenant.host." + this.CurrentUser.PublicSiteUrl.ToLower();
            await cacheService.RemoveAsync(cachekey);

        }


        protected async void lbtnDelImg_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
            string strSqlLogo1 = "", strSqlLogo2 = "", strSqlLogo3 = "";
            LinkButton lbt = (LinkButton)sender;

            if (lbt != null)
            {
                if (lbt.ID == "lbtnDelImg1")
                {
                    strSqlLogo1 = ", LogoImage=''";
                    if (!String.IsNullOrEmpty(ImgPreview1.Attributes["originalimg"]))
                        await FileService.DeleteS3ImageAsync(ImgPreview1.Attributes["originalimg"]);
                }
                else if (lbt.ID == "lbtnDelImg2")
                {
                    strSqlLogo2 = ", LogoSmall=''";
                    if (!String.IsNullOrEmpty(ImgPreview2.Attributes["originalimg"]))
                        await FileService.DeleteS3ImageAsync(ImgPreview2.Attributes["originalimg"]);
                }
                else if (lbt.ID == "lbtnDelImg3")
                {
                    strSqlLogo3 = ", FavIcoImage=''";
                    if (!String.IsNullOrEmpty(ImgPreview3.Attributes["originalimg"]))
                        await FileService.DeleteS3ImageAsync(ImgPreview3.Attributes["originalimg"]);
                }

                parmeters.Add(new KeyValuePair<string, object>("StoreGroupId", this.CurrentUser.StoreGroupId));
                string sql = $"UPDATE AppTenant SET CustomColor = CustomColor {strSqlLogo1 + strSqlLogo2 + strSqlLogo3} WHERE Id=@StoreGroupId; ";
                int strresult = DataService.ExecuteSql(sql, parmeters: parmeters);
                Service.UserService.CachedDefaultUser = null;
                LoadView();

                var cacheService = new RedisCacheService();
                string cachekey = $"Retl.AppTenant.host." + this.CurrentUser.PublicSiteUrl.ToLower();
                await cacheService.RemoveAsync(cachekey);
            }
        }
    }
}