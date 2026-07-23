using NPOI.Util;
using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.Query.Dynamic;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Support
{
    public partial class SupportLanding : Base.BasePartnerPage
    {
        private bool _isFirstItem;
        protected void Page_Load(object sender, EventArgs e)
        {
           
            plcContent.Visible = String.IsNullOrEmpty(Request.QueryString["articleid"]);
            plcarticle.Visible = !plcContent.Visible;
            if (!IsPostBack)
            {
                string searchValue = Request.QueryString["search"];
               
                LoadArticleContent();
                BindRepeater();
                BindArticle();
            }                      
        }
        protected void Page_PreRender(object sender, EventArgs e)
        {

        }
        protected void rptchapters_DataBinding(object sender, EventArgs e)
        {
            string unitId = (Request.QueryString["unitid"]);
            List<KeyValuePair<string, object>> sqlda = new List<KeyValuePair<string, object>>();
            sqlda.Add(new KeyValuePair<string, object>("id", unitId));
            string unitchapters = "SELECT su.name,su.description FROM `support_unit` su WHERE su.id=@id";
            var unitname = DataServiceMySql.GetDataTable(unitchapters, Service.UserService.GetAPIConnectionString(), sqlda);
            if (unitname != null && unitname.Rows.Count > 0)
            {
                ltrunitname.Text = unitname.Rows[0]["name"].ToString();
                supportunit.Attributes["class"] += " " + unitname.Rows[0]["description"].ToString(); 
            }

        }

        protected void rptchapterarticle_DataBinding(object sender, EventArgs e)
        {
            string articleid = (Request.QueryString["articleid"]);
            List<KeyValuePair<string, object>> sqlda = new List<KeyValuePair<string, object>>();
            sqlda.Add(new KeyValuePair<string, object>("id", articleid));
            string articlechapters = "SELECT sa.name,sa.id,sa.chapterId as articleChapter,sa.unitId,sa.content as articleContent,su.name FROM `support_article`sa INNER JOIN `support_unit` su ON su.id=sa.unitId WHERE sa.id=@id";
            var articlaname = DataServiceMySql.GetDataTable(articlechapters, Service.UserService.GetAPIConnectionString(), sqlda);           
        }

        protected void rptArticle_DataBinding(object sender, EventArgs e)
        {
           
             
        }

        protected void rptchapterarticle_ItemDataBound(object sender, RepeaterItemEventArgs e)
        {
            if (e.Item.ItemType == ListItemType.Item || e.Item.ItemType == ListItemType.AlternatingItem)
            {
                DataRowView dataItem = e.Item.DataItem as DataRowView;
                if (dataItem != null)
                {
                    string articleId = dataItem["id"].ToString();
                    List<KeyValuePair<string, object>> sqlda = new List<KeyValuePair<string, object>>();
                    sqlda.Add(new KeyValuePair<string, object>("id", articleId));
                    string articlechapters = "SELECT sa.name as articlename,sa.id,sa.chapterId as chapterId,sa.unitId,sa.content as articleContent,su.name,su.description FROM `support_article` sa INNER JOIN `support_unit` su ON su.id=sa.unitId WHERE sa.id=@id";
                    var articlaname = DataServiceMySql.GetDataTable(articlechapters, Service.UserService.GetAPIConnectionString(), sqlda);
                    if (articlaname != null && articlaname.Rows.Count > 0)
                    {
                        ltrfrariticle.Text = articlaname.Rows[0]["name"].ToString();
                        ltrfrarticlename.Text = articlaname.Rows[0]["articlename"].ToString();
                        ltrfrcontent.Text = articlaname.Rows[0]["articleContent"].ToString();
                        articlesupport.Attributes["class"] += " " + articlaname.Rows[0]["description"].ToString();
                    }
                    LoadArticleContent();
                }
            }
        }


        private void LoadArticleContent()
        {
            string articleId = Request.QueryString["articleid"];
            List<KeyValuePair<string, object>> sqlda = new List<KeyValuePair<string, object>>();
            sqlda.Add(new KeyValuePair<string, object>("id", articleId));
            string articlechapters = "SELECT sa.name as articleName,sa.id,sa.chapterId as chapterId,sa.unitId, sa.content as articleContent,su.name,su.description FROM `support_article` sa INNER JOIN `support_unit`su ON su.id=sa.unitId WHERE sa.id=@id";
            var articlaname = DataServiceMySql.GetDataTable(articlechapters, Service.UserService.GetAPIConnectionString(), sqlda);
            if (articlaname != null && articlaname.Rows.Count > 0)
            {
                ltrfrariticle.Text = articlaname.Rows[0]["name"].ToString();
                ltrfrarticlename.Text = articlaname.Rows[0]["articleName"].ToString();
                ltrfrcontent.Text = articlaname.Rows[0]["articleContent"].ToString();
                articlesupport.Attributes["class"] += " " + articlaname.Rows[0]["description"].ToString();
            }
        }
        private void BindRepeater()
        {   
            string unitId = Request.QueryString["unitid"];
            string chapterId = Request.QueryString["chapterid"];
            if (string.IsNullOrEmpty(unitId))
            {
                SDSchapters.SelectCommand = @"
                    SELECT sc.id, sc.name, sc.unitId 
                    FROM support_chapter sc 
                    WHERE sc.unitId = (
                        SELECT sc.unitId 
                        FROM support_chapter sc
                        ORDER BY sc.unitId 
                        LIMIT 1
                    )";
            }
            //rptchapters.DataSource = SDSchapters;
            rptchapters.DataBind();
            _isFirstItem = string.IsNullOrEmpty(chapterId);
        }
        protected string GetActiveClass(object scId)
        {
            string chapterId = Request.QueryString["chapterid"];
            if (!string.IsNullOrEmpty(chapterId) && chapterId == scId.ToString())
            {
                return "active";
            }

            if (_isFirstItem)
            {
                _isFirstItem = false;
                return "active";
            }

            return string.Empty;
        }
        private void BindArticle()
        {
            string chapterid = Request.QueryString["chapterid"];
            string unitId = Request.QueryString["unitid"];
            if (string.IsNullOrEmpty(chapterid) && !string.IsNullOrEmpty(unitId))
            {
                SDSchapterarticle.SelectCommand = SDSchapterarticle.SelectCommand = @"
                    SELECT sa.name as articleName, sa.id,sa.chapterid as articleChapter, sa.unitId,sa.content as articleContent, su.name 
                    FROM support_article sa
                    INNER JOIN support_unit su ON su.id = sa.unitId 
                    WHERE sa.chapterId = (
                        SELECT sa.chapterid as articleChapter 
                        FROM support_article sa
                        WHERE sa.unitId = @unitId 
                        ORDER BY sa.id 
                        LIMIT 1
                    )";

                SDSchapterarticle.SelectParameters.Clear();
                SDSchapterarticle.SelectParameters.Add("unitId", unitId);
            }
            rptchapterarticle.DataBind();
        }

        protected void rptArticle_ItemDataBound(object sender, RepeaterItemEventArgs e)
        {
            if (e.Item.ItemType == ListItemType.Item || e.Item.ItemType == ListItemType.AlternatingItem)
            {
                DataRowView dataItem = e.Item.DataItem as DataRowView;
                if (dataItem != null)
                {
                    string articleId = dataItem["id"].ToString();
                    List<KeyValuePair<string, object>> sqlda = new List<KeyValuePair<string, object>>();
                    sqlda.Add(new KeyValuePair<string, object>("id", articleId));
                    string articlechapters = "SELECT sa.name as articleName,sa.id,sa.chapterId as articleChapter,sa.unitId,sa.content as articleContent,su.name,su.description FROM `support_article` sa INNER JOIN `support_unit` su ON su.id=sa.unitId WHERE sa.id=@id";
                    var articlaname = DataServiceMySql.GetDataTable(articlechapters, Service.UserService.GetAPIConnectionString(), sqlda);
                    if (articlaname != null && articlaname.Rows.Count > 0)
                    {
                        ltrfrariticle.Text = articlaname.Rows[0]["name"].ToString();
                        ltrfrarticlename.Text = articlaname.Rows[0]["articleName"].ToString();
                        ltrfrcontent.Text = articlaname.Rows[0]["articleContent"].ToString();
                        articlesupport.Attributes["class"] += " " + articlaname.Rows[0]["description"].ToString();
                    }
                }
                LoadArticleContent();
            }
        }
    }
}