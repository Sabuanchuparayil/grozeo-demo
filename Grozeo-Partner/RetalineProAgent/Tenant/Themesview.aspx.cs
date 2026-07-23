using Google.Protobuf.WellKnownTypes;
using Microsoft.Ajax.Utilities;
using NPOI.OpenXmlFormats.Wordprocessing;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.Cache;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using static QRCoder.PayloadGenerator;

namespace RetalineProAgent.Tenant
{
    public partial class Themesview : Base.BasePartnerPage
    {
        public class ThemeInfo
        {
            public string Name { get; set; }
            public string Title { get; set; }
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            string id = Request.QueryString["themeId"];
            ThemeInfo themeInfo = getname(id);
            string name = themeInfo.Name;
            if (!string.IsNullOrWhiteSpace(name))
            {
                lblconform.Text = "Implementation";
                lbltext.Text = "Theme change can impact the look and feel as well as the components displayed. Are you sure you want to change the theme?";
                lbtTheme.Text = "Ok";
            }
            else
            {
                lblconform.Text = "Manual Implementation Required";
                lbltext.Text = "This design requires custom integration. If you like this design, we will integrate this for you. One of our design support executive will contact you and update the portal with your consent.";
                lbtTheme.Text = "I Like the Design Contact me";
            }

        }
        protected async void lbtTheme_ClickAsync(object sender, EventArgs e)
        {
            try
            {
                string id = Request.QueryString["themeId"];
                ThemeInfo themeInfo = getname(id);
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
    }

}