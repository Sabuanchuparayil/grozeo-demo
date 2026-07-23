using Antlr.Runtime;
using RetalineProAgent.Service;
using NPOI.POIFS.NIO;
using NPOI.POIFS.Properties;
using NPOI.SS.Formula.Functions;
using System;
using System.Collections;
using System.Collections.Generic;
using System.Data;
using System.Data.SqlClient;
using System.Linq;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Xml.Linq;
using static Org.BouncyCastle.Crypto.Digests.SkeinEngine;
using RetalineProAgent.Core.Services;
using System.Configuration;
using RetalineProAgent.Core.Services.ActiveLog;

namespace RetalineProAgent.Finance
{
	public partial class CostCategory : System.Web.UI.Page
	{
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
               
            }

        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
           

        }
        protected void btncreate_Click(object sender, EventArgs e)
        {

            ShowDiv.Visible = true;
            pnldetails.Visible = false;
            editdiv.Visible = false;
            pnledit.Visible = true;






        }
        protected void lvdetails_ItemEditing(object sender, ListViewEditEventArgs e)
        {

        }

        protected void lvdetails_ItemUpdating(object sender, ListViewUpdateEventArgs e)
        {

        }

        protected void lvdetails_ItemCommand(object sender, ListViewCommandEventArgs e)
        {

        }
        private void LoadInfo()
        {
            int Id = Convert.ToInt32(hidgroupId.Value);
            if (Id > 0)
            {
                List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
                sqldaId.Add(new KeyValuePair<string, object>("id", Id));
                string gropdetais = "select cg.id,cg.name,Count(cc.cost_category_id) as countno from cost_category cg left join cost_centre cc on cc.cost_category_id=cg.id where cg.id=@id group by cost_category_id,cg.name,cg.id";
                var groupid = DataService.GetDataTable(gropdetais, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaId);
                if (groupid != null && groupid.Rows.Count > 0)
                {
                    var grou = groupid.Rows[0];
                    ltrnamegroup.Text = grou["name"].ToString();
                   
                }

            }

        }

        protected void btnsave_Click(object sender, EventArgs e)
        {


            string type = ddlentrype.SelectedItem.Text;
            List<KeyValuePair<string, object>> sidparams = new List<KeyValuePair<string, object>>();
            sidparams.Add(new KeyValuePair<string, object>("name",txtGroupName.Text));
            sidparams.Add(new KeyValuePair<string, object>("type", type));
            string cnt = null;
            DataTable groupCount = DataService.GetDataTable($"select count(1) as count from cost_category where [name]= @name", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sidparams);

            if (groupCount != null && groupCount.Rows.Count > 0)
            {
                DataRow da = groupCount.Rows[0];
                cnt = da["count"].ToString();
            }
            int count = Convert.ToInt32(cnt);
            if (count > 0)
            {
                lbgroupid.Text = "Cost Category already exists";
            }
            else
            {
                string sub = "insert into [cost_category] ([name],type)   values(@name,@type)";
                int suggroup = DataService.ExecuteSql(sub, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sidparams);
                lbgroupid.Text = "";
                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                int storegroupid = -1;
                string User = "Finance Admin";
                string name = txtGroupName.Text;
                string types = type;
                string create = "Costcategory";
               
                var items = new[]
                    {
                    new { Key = "Costcategory Name", Value = name },
                    new { Key = "Types", Value = types },
                    new { Key = "create", Value = create },                   
                };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                Common.ShowCustomAlert(this.Page, "Success", "save successfully!", true, "/Finance/Costcategory");
            }
        }

        protected void lvdatatable_DataBinding(object sender, EventArgs e)
        {

        }

        protected void lvdetails_SelectedIndexChanged(object sender, EventArgs e)
        {

        }

        protected void lvdetails_DataBound(object sender, EventArgs e)
        {



        }

        protected void lvdatatable_DataBound(object sender, EventArgs e)
        {
            if (lvdatatable.Items.Count > 0 && (String.IsNullOrEmpty(hidgroupId.Value) || hidgroupId.Value == "0"))
            {
                LinkButton lbtn = (LinkButton)lvdatatable.Items[0].FindControl("btnselect");
                if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["dataid"]))
                {
                    hidgroupId.Value = lbtn.Attributes["dataid"];
                    lvdatatable.SelectedIndex = 0;
                   
                }
                LoadInfo();
            }

        }

        protected void btnhide_Click(object sender, EventArgs e)
        {


            LinkButton lbtn = (LinkButton)sender;
            hidgroupId.Value = lbtn.Attributes["dataid"];
            int Id = Convert.ToInt32(hidgroupId.Value);
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", Id));
            string gropdetais = "select cg.id,cg.name,Count(cc.cost_category_id) as countno from cost_category cg left join cost_centre cc on cc.cost_category_id=cg.id where cg.id=@id group by cost_category_id,cg.name,cg.id";
            var groupid = DataService.GetDataTable(gropdetais, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaId);
            if (groupid != null && groupid.Rows.Count > 0)
            {
                var grou = groupid.Rows[0];
                ltrnamegroup.Text = grou["name"].ToString();
              
            }





        }

        protected void SDSGroupdetails_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {

        }

        protected void btnedit_Click(object sender, EventArgs e)
        {
            editdiv.Visible = true;
            pnledit.Visible = false;
            int Id = Convert.ToInt32(hidgroupId.Value);
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", Id));
            string cost = "select cg.id,cg.name,type,Count(cc.cost_category_id) as countno from cost_category cg left join cost_centre cc on cc.cost_category_id=cg.id where cg.id=@id group by cost_category_id,cg.name,cg.id,cg.type";
            var groupid = DataService.GetDataTable(cost, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sqldaId);
            if (groupid != null && groupid.Rows.Count > 0)
            {
                var grou = groupid.Rows[0];
                txtgroup.Text = grou["name"].ToString();
                dlentrypeudate.SelectedItem.Text = grou["type"].ToString();
            }













        }

        protected void btnupdate_Click(object sender, EventArgs e)
        {
            int Id = Convert.ToInt32(hidgroupId.Value);
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", Id));
            sqldaId.Add(new KeyValuePair<string, object>("name", txtgroup.Text));
            sqldaId.Add(new KeyValuePair<string, object>("type", dlentrypeudate.SelectedItem.Text));
            string group = "UPDATE cost_category SET name=@name,type=@type where id=@Id";
            int result = DataService.ExecuteSql(group, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sqldaId);
            lbgroupid.Text = "";
            Common.ShowCustomAlert(this.Page, "Success", "save successfully!", true, "/Finance/Costcategory");
            lbprime.Text = "";
        }

        protected void btncancel_Click(object sender, EventArgs e)
        {
            editdiv.Visible = false;
            pnledit.Visible = true;
        }

        protected void dlentrypeudate_DataBound(object sender, EventArgs e)
        {


        }

        protected void btncanel_update_Click(object sender, EventArgs e)
        {
            editdiv.Visible = false;
            pnledit.Visible = true;
        }
    }
}