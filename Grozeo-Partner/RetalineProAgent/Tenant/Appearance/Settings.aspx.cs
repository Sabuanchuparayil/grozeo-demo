using RetalineProAgent.Core.Services;
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
    public partial class Settings: Base.BasePartnerPage
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
                if (!String.IsNullOrEmpty(Request.QueryString["tb"]))
                {
                    if (Request.QueryString["tb"] == "banner")
                        hidTab.Value = "2";
                    else if (Request.QueryString["tb"] == "info")
                        hidTab.Value = "3";
                    else if (Request.QueryString["tb"] == "theme")
                        hidTab.Value = "4";

                }
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
                    //SiteTheme = dr["Theme"].ToString();
                    //if (!String.IsNullOrEmpty(strLogo))
                    //{
                    //    imgLogo.ImageUrl = strLogo;
                    //    imgLogo.Visible = true;
                    //    chkDelImgLogo.Visible = true;
                    //}
                    //if (!String.IsNullOrEmpty(strLogoSmall))
                    //{
                    //    imgLogoWhite.ImageUrl = strLogoSmall;
                    //    imgLogoWhite.Visible = true;
                    //    chkDelImgLogoWhite.Visible = true;
                    //}

                    //string strCustomColor = dr["CustomColor"].ToString();
                    //if (!String.IsNullOrEmpty(strCustomColor))
                    //    txtColor.Text = strCustomColor;

                }

                try
                {
                    string sqlInfoContent = $"SELECT * FROM app_pages WHERE page_type IN (1, 3) AND (IFNULL(storegroup_id, 0) = 0 OR storegroup_id = {this.CurrentUser.APIStoreId}) GROUP BY page_type DESC";
                    DataTable dtInfo = DataServiceMySql.GetDataTable(sqlInfoContent, UserService.GetAPIConnectionString());
                    if (dtInfo != null && dtInfo.Rows.Count > 0)
                    {
                        var info = dtInfo.AsEnumerable().Select(item => new { pagetype = item["page_type"].ToString(), pagename = item["page_name"].ToString(), content = item["page_content"].ToString() }).ToList();
                        foreach (var item in info)
                        {
                            if (item.pagetype == "1")
                                taAboutContent.InnerHtml = item.content;
                            else if (item.pagetype == "3")
                                taTermsContent.InnerHtml = item.content;
                        }

                    }
                }
                catch (Exception ex)
                {

                }
            }
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            //int count = rptOwnbanners.Items.Count;
            //pnlUploadBanner1.Visible = count < 1;
            ////pnlUploadBanner2.Visible = count <2;
            //FileUpload1.Attributes.Remove("onchange");
            //FileUpload1.Attributes.Add("onchange", $"$('#{btnUploadBanner.ClientID}').click();");
            //FileUpload2.Attributes.Remove("onchange");
            //FileUpload2.Attributes.Add("onchange", $"$('#{btnUploadBanner.ClientID}').click();");

        }


        //    protected void SDSHomeBanners_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        //    {
        //        e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        //    }



        //    protected void ODSThemes_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        //    {
        //        e.InputParameters["path"] = Server.MapPath("~/Content/images/theme");
        //    }

        //    public bool IsActiveTheme(string themeName)
        //    {
        //        try
        //        {
        //            if (!string.IsNullOrEmpty(SiteTheme) && !String.IsNullOrEmpty(themeName) && themeName.ToLower() == "default" && SiteTheme.Equals(System.Configuration.ConfigurationManager.AppSettings.Get("ThemeDefault")))
        //                return true;

        //            return (!string.IsNullOrEmpty(themeName) && SiteTheme.Equals(themeName));
        //        }
        //        catch { }
        //        return false;
        //    }

        //    protected void lbtTheme_Click(object sender, EventArgs e)
        //    {
        //        //LinkButton lbTheme = (LinkButton)sender;
        //        //if (lbTheme != null && !String.IsNullOrEmpty(lbTheme.Attributes["themename"]) && System.IO.Directory.GetFiles(Server.MapPath("~/Content/images/theme"), $"{lbTheme.Attributes["themename"]}.png").Any())
        //        //{

        //        //    string strSql = $"UPDATE AppTenant SET Theme = @theme WHERE Id=@id";
        //        //    List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();
        //        //    parmeters.Add(new KeyValuePair<string, object>("theme", lbTheme.Attributes["themename"]));
        //        //    parmeters.Add(new KeyValuePair<string, object>("id", this.CurrentUser.StoreGroupId));
        //        //    int results = DataService.ExecuteSql(strSql, parmeters: parmeters);
        //        //    if (results > 0)
        //        //    {
        //        //        SiteTheme = lbTheme.Attributes["themename"];
        //        //        rptThemes.DataBind();
        //        //    }

        //        //}

        //    }

        protected void btnSaveInfoContent_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>();
            sqlparams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
            sqlparams.Add(new KeyValuePair<string, object>("content", taAboutContent.InnerHtml));
            string sql = @"INSERT INTO app_pages(page_name, page_content, page_status, storegroup_id, page_type)
                    VALUES('about us', @content, 1, @storegroupid, 1)
                    ON DUPLICATE KEY UPDATE
                      page_content     = VALUES(page_content),
                      page_name = VALUES(page_name)";
            int result = DataServiceMySql.ExecuteSql(sql, UserService.GetAPIConnectionString(), sqlparams);

            sqlparams = new List<KeyValuePair<string, object>>();
            sqlparams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
            sqlparams.Add(new KeyValuePair<string, object>("content", taTermsContent.InnerHtml));
            sql = @"INSERT INTO app_pages(page_name, page_content, page_status, storegroup_id, page_type)
                    VALUES('about us', @content, 1, @storegroupid, 3)
                    ON DUPLICATE KEY UPDATE
                      page_content     = VALUES(page_content),
                      page_name = VALUES(page_name)";
            result += DataServiceMySql.ExecuteSql(sql, UserService.GetAPIConnectionString(), sqlparams);

        }
    }
}
