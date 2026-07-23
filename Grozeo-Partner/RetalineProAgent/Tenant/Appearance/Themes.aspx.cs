using RetalineProAgent.Controls.StoreSettings;
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
using static RetalineProAgent.Tenant.Themesview;

namespace RetalineProAgent.Appearance
{
    public partial class Themes : Base.BasePartnerPage
    {

        public string SiteTheme
        {
            get
            {
                return (string)ViewState["CURTHEME"];
            }
            set
            {
                ViewState["CURTHEME"] = value;
            }
        }

        protected void Page_Load(object sender, EventArgs e)
        {

            if (!IsPostBack)
            {
                int storegroupid = this.CurrentUser.StoreGroupId;

                string strSql = $"SELECT a.Id, a.Name, a.Theme, a.APIUrl, a.CanCheckout, a.CustomColor, a.FavIcoImage, " +
                    $"Stuff((SELECT ',' + (t.HostAddress) FROM Host t WHERE a.Id LIKE t.TenantId FOR Xml Path('')), 1, 1, '') as hosts, " +
                    $"a.LogoImage, a.LogoSmall, a.OnlinePaymentEnabled, a.ShowPWA, a.Status, a.StoreId as StoreGroupId, " +
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
                    SiteTheme = dr["Theme"].ToString();

                }
            }           
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {


        }


        protected void SDSHomeBanners_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }



        protected void ODSThemes_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            e.InputParameters["path"] = Server.MapPath("~/Content/images/theme");
        }

        public bool IsActiveTheme(string themeName)
        {
            try
            {
                if (!String.IsNullOrEmpty(themeName) && themeName == this.CurrentUser.Theme)
                    return true;

                return (!string.IsNullOrEmpty(themeName) && SiteTheme.Equals(themeName));
            }
            catch { }
            return false;
        }

        protected async void lbtTheme_Click(object sender, EventArgs e)
        {
            try
            {
                string selectedThemeId  = hfSelectedThemeId.Value; 
                ThemeInfo themeInfo = getname(selectedThemeId);
                string name = themeInfo.Name;
                if (!string.IsNullOrWhiteSpace(name))
                {
                    string strSql = $"UPDATE AppTenant SET Theme = @theme WHERE Id=@id";
                    List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
                    parmeters.Add(new KeyValuePair<string, object>("theme", name));
                    parmeters.Add(new KeyValuePair<string, object>("id", this.CurrentUser.StoreGroupId));
                    int results = DataService.ExecuteSql(strSql, parmeters: parmeters);
                    if (results > 0)
                    {

                        var cacheService = new RedisCacheService();
                        string cachekey = $"Retl.AppTenant.host." + this.CurrentUser.PublicSiteUrl.ToLower();
                        await cacheService.RemoveAsync(cachekey);
                        ctrlMessagebox.ShowResult("Success", "Your theme has been updated successfully!", 1, "/Tenant/Appearance/Themes");
                    }
                }
                else
                {
                    Service.User user = this.CurrentUser;
                    APIService.Support(9, user.Phone, user.Email, user.FullName, "design requires custom integration", themeInfo.Title, user.APIStoreId, 19, "", "");
                    ctrlMessagebox.ShowResult("Success", "Create Support Ticket successfully", 1, "");
                    // Common.ShowCustomAlert(this.Page, "Success", "Create Support Ticket successfully", true, "/Tenant/Appearance/Themes");
                }
            }
            catch
            {
                ctrlMessagebox.ShowResult("Failed", "Technical Error", 2, "");

            }
        }
        public ThemeInfo getname(string id)
        {
            List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
            parmeters.Add(new KeyValuePair<string, object>("themeid", id));
            var theme = DataServiceMySql.GetDataTable("SELECT id,`name`,title FROM `theme` WHERE id=@themeid", Service.UserService.GetAPIConnectionString(), parmeters);
            if (theme != null && theme.Rows.Count > 0)
            {
                ThemeInfo themeInfo = new ThemeInfo
                {
                    Name = theme.Rows[0]["name"].ToString(),
                    Title = theme.Rows[0]["title"].ToString()
                };
                return themeInfo;
            }
            return new ThemeInfo();
        }

        protected void SDSthemes_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
            e.Command.Parameters["activeThemeName"].Value = this.CurrentUser.Theme;


        }
    }
}