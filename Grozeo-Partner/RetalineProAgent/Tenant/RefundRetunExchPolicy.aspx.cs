using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class RefundRetunExchPolicy : Base.BasePartnerPage
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
                try
                {
                    User _curUser = this.CurrentUser;
                    string sqlInfoContent = $"SELECT * FROM app_pages WHERE page_type IN (1, 3) AND (IFNULL(storegroup_id, 0) = 0 OR storegroup_id = {_curUser.APIStoreId}) GROUP BY page_type DESC";
                    sqlInfoContent = $@"SELECT t.* FROM app_pages t JOIN (
                        SELECT page_type, MAX(page_id) Maxpage_id FROM app_pages WHERE  page_type = 6 AND (IFNULL(storegroup_id, 0) = 0 OR storegroup_id = {_curUser.APIStoreId})
                        GROUP BY page_type
                    ) r ON t.page_type = r.page_type AND t.page_id = r.Maxpage_id
                    WHERE  t.page_type = 6 AND (IFNULL(storegroup_id, 0) = 0 OR storegroup_id = {_curUser.APIStoreId}) ORDER BY t.page_id DESC";

                    DataTable dtInfo = DataServiceMySql.GetDataTable(sqlInfoContent, UserService.GetAPIConnectionString());
                    if (dtInfo != null && dtInfo.Rows.Count > 0)
                    {
                        var info = dtInfo.AsEnumerable().Select(item => new { pagetype = item["page_type"].ToString(), pagename = item["page_name"].ToString(), content = item["page_content"].ToString() }).FirstOrDefault();
                        if (info.pagetype == "6")
                            taAboutContent.InnerHtml = info.content;
                    }
                }
                catch (Exception ex)
                {
                    Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
                }
            }
        }

        
        protected void btnSaveAbout_Click(object sender, EventArgs e)
        {
            int storeGroupId = this.CurrentUser.APIStoreId;

            List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>();
            sqlparams.Add(new KeyValuePair<string, object>("storegroupid", storeGroupId));
            sqlparams.Add(new KeyValuePair<string, object>("content", taAboutContent.InnerText));
            sqlparams.Add(new KeyValuePair<string, object>("pageType", 6));

            DataTable dtPagesCnt = DataServiceMySql.GetDataTable($"SELECT COUNT(*) AS cnt FROM app_pages WHERE storegroup_id = @storeGroupId AND page_type = @pageType", UserService.GetAPIConnectionString(), sqlparams);
            DataRow da = dtPagesCnt.Rows[0];
            try
            {
                if (Convert.ToInt32(da["cnt"]) > 0)
                {
                    string updateQry = "UPDATE app_pages SET page_content=@content WHERE storegroup_id = @storeGroupId AND page_type = @pageType";
                    DataServiceMySql.ExecuteSql(updateQry, Service.UserService.GetAPIConnectionString(), sqlparams);
                    Common.ShowCustomAlert(this.Page, "Refund, Return, and Exchange Policy updated!", "Refund, Return, and Exchange Policy updated successfully!", true, "/Tenant/RefundRetunExchPolicy");
                }
                else
                {
                    string sql = @"INSERT INTO app_pages(page_name, page_content, page_status, storegroup_id, page_type)
                    VALUES('Refund, Return, and Exchange Policy', @content, 1, @storegroupid, @pageType)
                    ON DUPLICATE KEY UPDATE
                      page_content     = VALUES(page_content),
                      page_name = VALUES(page_name)";
                    int result = DataServiceMySql.ExecuteSql(sql, UserService.GetAPIConnectionString(), sqlparams);
                    Common.ShowCustomAlert(this.Page, "Refund, Return, and Exchange Policy created!", "Refund, Return, and Exchange Policy created successfully!", true, "/Tenant/RefundRetunExchPolicy");
                }
            }
            catch
            {
                Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
            }
        }

        private void ShowSuccess(string title, string content)
        {
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#modaldemo4').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }
    }
}
