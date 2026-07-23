using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class FooterContent: Base.BasePartnerPage
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
                            //if (item.pagetype == "1")
                            //    taAboutContent.InnerHtml = item.content;
                            if (item.pagetype == "5")
                                taFooterContent.InnerHtml = item.content;
                        }

                    }
                }
                catch (Exception ex)
                {

                }

                LoadStoreInfo();
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

        private void LoadStoreInfo()
        {
            //int storeGroupId = this.CurrentUser.APIStoreId;

            //List<KeyValuePair<string, object>> pageparams = new List<KeyValuePair<string, object>>();
            //pageparams.Add(new KeyValuePair<string, object>("storegroupid", storeGroupId));
            //pageparams.Add(new KeyValuePair<string, object>("pageId", 6));
            //pageparams.Add(new KeyValuePair<string, object>("content", taFooterContent.InnerText));
            //pageparams.Add(new KeyValuePair<string, object>("pageType", 0));

            //DataTable dtPagesCnt = DataServiceMySql.GetDataTable($"SELECT COUNT(*) AS cnt FROM app_pages WHERE storegroup_id = @storeGroupId AND page_type = @pageType", UserService.GetAPIConnectionString(), pageparams);
            //DataRow dr = dtPagesCnt.Rows[0];

            //if (Convert.ToInt32(dr["cnt"]) > 0)
            //{
            //    DataTable dt = DataServiceMySql.GetDataTable($"SELECT page_id,page_name,page_content,page_status AS page_status FROM app_pages WHERE storegroup_id = @storeGroupId AND page_type = @pageType", Service.UserService.GetAPIConnectionString(), pageparams);
            //    DataRow da = dt.Rows[0];
            //    taFooterContent.InnerText = da["page_content"].ToString();
            //}
            //else
            //{
            //    DataTable dtPage = DataServiceMySql.GetDataTable($"SELECT page_id,page_name,page_content,page_status AS page_status FROM app_pages WHERE page_id = @pageId AND page_type = @pageType", Service.UserService.GetAPIConnectionString(), pageparams);
            //    DataRow db = dtPage.Rows[0];
            //    taFooterContent.InnerText = db["page_content"].ToString();
            //}

        }

        protected void btnSaveTerms_Click(object sender, EventArgs e)
        {
            int storeGroupId = this.CurrentUser.APIStoreId;

            List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>();
            sqlparams.Add(new KeyValuePair<string, object>("storegroupid", storeGroupId));
            sqlparams.Add(new KeyValuePair<string, object>("content", taFooterContent.InnerText));
            sqlparams.Add(new KeyValuePair<string, object>("pageType", 0));

            DataTable dtPagesCnt = DataServiceMySql.GetDataTable($"SELECT COUNT(*) AS cnt FROM app_pages WHERE storegroup_id = @storeGroupId AND page_type = @pageType", UserService.GetAPIConnectionString(), sqlparams);
            DataRow da = dtPagesCnt.Rows[0];

            //DataTable dtPages = DataServiceMySql.GetDataTable($"SELECT page_id FROM app_pages WHERE storegroup_id = @storeGroupId AND page_type = @pageType", UserService.GetAPIConnectionString(), sqlparams);
            //DataRow dr = dtPages.Rows[0];

            //sqlparams.Add(new KeyValuePair<string, object>("pageId", da["page_id"]));
            try
            {
                if (Convert.ToInt32(da["cnt"]) > 0)
                {
                    string updateQry = "UPDATE app_pages SET page_content=@content WHERE storegroup_id = @storeGroupId AND page_type = @pageType";
                    DataServiceMySql.ExecuteSql(updateQry, Service.UserService.GetAPIConnectionString(), sqlparams);
                    Common.ShowCustomAlert(this.Page, "Footer content updated!", "Footer content updated successfully!", true, "/Tenant/TermsOfUse");
                }
                else
                {
                    string sql = @"INSERT INTO app_pages(page_name, page_content, page_status, storegroup_id, page_type)
                    VALUES('Footer Content', @content, 1, @storegroupid, @pageType)
                    ON DUPLICATE KEY UPDATE
                      page_content     = VALUES(page_content),
                      page_name = VALUES(page_name)";
                    int result = DataServiceMySql.ExecuteSql(sql, UserService.GetAPIConnectionString(), sqlparams);
                    Common.ShowCustomAlert(this.Page, "Footer content created!", "Footer content created successfully!", true, "/Tenant/TermsOfUse");
                }
            }
            catch
            {
                Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
            }

        }
    }
}
