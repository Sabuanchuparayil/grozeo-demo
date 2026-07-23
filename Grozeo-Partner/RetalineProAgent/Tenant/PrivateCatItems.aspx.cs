using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.BussinessModel.Store;
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
    public partial class PrivateCatItems: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> orderSqlprms = new List<KeyValuePair<string, object>>();
            orderSqlprms.Add(new KeyValuePair<string, object>("vcId", (Request.QueryString["id"])));
            string query = "SELECT vc_name FROM retaline_virtual_category WHERE vc_id = @vcId";
            DataTable catname = DataServiceMySql.GetDataTable(query, Service.UserService.GetAPIConnectionString(), orderSqlprms);
            if (catname.Rows.Count > 0)
            {
                ltrBranchName.Text = catname.Rows[0]["vc_name"].ToString();               
            }           
        }


        //protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        //{
        //    if (gvPrivateCatItems.PageIndex > 0)
        //        gvPrivateCatItems.PageIndex = gvPrivateCatItems.PageIndex - 1;
        //}

        //protected void lbtnPagerRight_Click(object sender, EventArgs e)
        //{
        //    if (gvPrivateCatItems.PageIndex < gvPrivateCatItems.PageCount - 1)
        //        gvPrivateCatItems.PageIndex = gvPrivateCatItems.PageIndex + 1;
        //}

        protected void gvPrivateCatItems_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvPrivateCatItems.PageIndex * gvPrivateCatItems.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvPrivateCatItems.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSPrivateCatItems.Select(DataSourceSelectArguments.Empty);
        }

        protected string GetBackLink()
        {
            string categoryType = Request.QueryString["type"];
            string backLinkUrl;

            if (categoryType == "featured")
                backLinkUrl = "/Tenant/PrivateCategory.aspx?type=featured";
            else if (categoryType == "preferred")
                backLinkUrl = "/Tenant/PrivateCategory.aspx?type=preferred";
            else
                backLinkUrl = "/Tenant/PrivateCategory"; // Default for other categories

            return backLinkUrl;
        }

    }

}


