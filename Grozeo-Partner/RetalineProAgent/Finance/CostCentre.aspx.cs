using RetalineProAgent.Service;
using NPOI.POIFS.Properties;
using Org.BouncyCastle.Asn1.Ocsp;
using System;
using System.Collections.Generic;
using System.ComponentModel.DataAnnotations;
using System.Data;
using System.Data.SqlTypes;
using System.Diagnostics;
using System.Linq;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Xml.Linq;
using RetalineProAgent.Core.Services;
using System.Configuration;
using NPOI.OpenXmlFormats.Dml.Diagram;
using RetalineProAgent.Core.BussinessModel.Finance;
using System.Web.Optimization;
using RetalineProAgent.Core.Services.ActiveLog;

namespace RetalineProAgent.Finance
{
    public partial class CostCentre : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void btncreatenew_Click(object sender, EventArgs e)
        {
            pnlnewledgers.Visible = true;
            pnlledgerdetailes.Visible = false;
            pnlledger_updetes.Visible = false;
        }


       private void Loadinfo()
        {

            int Id = Convert.ToInt32(hidledger.Value);
            if (Id>0)
            {
            
                int LedId = Convert.ToInt32(hidledger.Value);
                List<KeyValuePair<string, object>> sqlId = new List<KeyValuePair<string, object>>();
                sqlId.Add(new KeyValuePair<string, object>("id", LedId));
                string costcentre = "select name,cost_category_id,(select name from cost_category cg where cc.cost_category_id=cg.id) as costcategoryname from cost_centre cc where cc.id=@id";
                var ledgerid = DataService.GetDataTable(costcentre, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqlId);

                if (ledgerid != null && ledgerid.Rows.Count > 0)
                {
                    var ledgername = ledgerid.Rows[0];
                    ltrnameledger.Text = ledgername["name"].ToString();
                    ltrgroup.Text = ledgername["costcategoryname"].ToString();
                }

            }

        }

        protected void btnselect_Click(object sender, EventArgs e)
        {

            LinkButton lbtn = (LinkButton)sender;
            if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["dataid"]))
            {
                btnedit.Enabled = true;
                hidledger.Value = lbtn.Attributes["dataid"];
                int LedId = Convert.ToInt32(hidledger.Value);
                List<KeyValuePair<string, object>> sqlId = new List<KeyValuePair<string, object>>();
                sqlId.Add(new KeyValuePair<string, object>("id", LedId));
                string costcentre = "select name,cost_category_id,(select name from cost_category cg where cc.cost_category_id=cg.id) as costcategoryname from cost_centre cc where cc.id=@id";
                var ledgerid = DataService.GetDataTable(costcentre, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqlId);

                if (ledgerid != null && ledgerid.Rows.Count > 0)
                {
                    var ledgername = ledgerid.Rows[0];
                    ltrnameledger.Text = ledgername["name"].ToString();
                    ltrgroup.Text = ledgername["costcategoryname"].ToString();
                  
            }   }   
        }

        protected void btnedit_Click(object sender, EventArgs e)
        {

            pnlledger_updetes.Visible = true;
            pnlledgerdetailes.Visible = false;
            int Id = Convert.ToInt32(hidledger.Value);
            List<KeyValuePair<string, object>> sqldaId = new List<KeyValuePair<string, object>>();
            sqldaId.Add(new KeyValuePair<string, object>("id", Id));
            string cost = "select name,cost_category_id,(select name from cost_category cg where cc.cost_category_id=cg.id) as costcategoryname from cost_centre cc where cc.id=@id";
            var ledgername = DataService.GetDataTable(cost, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaId);
            if (ledgername != null && ledgername.Rows.Count > 0)
            {
                var led = ledgername.Rows[0];
                txtnameupdate.Text = led["name"].ToString();
                if (selGroup.Items.Count <= 1)
                    selGroup.DataBind();
                if (selGroup.Items.FindByValue(led["cost_category_id"].ToString()) != null)
                {
                    selGroup.SelectedIndex = selGroup.Items.IndexOf(selGroup.Items.FindByValue(led["cost_category_id"].ToString()));

                }
               

            }
           



        }

        protected void btnsave_Click(object sender, EventArgs e)
        {


            int costcategoryid = 0;
            if (!String.IsNullOrEmpty(ddlgroup.Text))
                costcategoryid = Convert.ToInt32(ddlgroup.Text);
            List<KeyValuePair<string, object>> sidparams = new List<KeyValuePair<string, object>>();
            sidparams.Add(new KeyValuePair<string, object>("name", txtName.Text));
            sidparams.Add(new KeyValuePair<string, object>("costcategoryid", costcategoryid));
            string cnt = null;
            DataTable ledgercount = DataService.GetDataTable($"select count(1) as count from cost_centre where [name]=@name", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: new List<KeyValuePair<string, object>>() { new KeyValuePair<string, object>("name", txtName.Text) });
            if (ledgercount != null && ledgercount.Rows.Count > 0)
            {
                DataRow da = ledgercount.Rows[0];
                cnt = da["count"].ToString();
            }
            int count = Convert.ToInt32(cnt);
            if (count > 0)
            {
                lbledgermgmt.Text = "Cost Centyre is already exist";

            }
            else
            {


                string sub = "insert into [cost_centre] ([name],cost_category_id,IsAuto)   values(@name,@costcategoryid,0)";
                //string led = "insert into ledger ([name],groups_id,isSystem,isEnabled,groups_refId,company_id,company_refId,branch_id,branch_refId,isApiCreated) values(@name, @groupid,0,1,(select  refId from groups where id = @groupid),10,(select TOP 1   refId  from company ),1,(select TOP 1   refId  from company branch where isActive=1),0)";
                int ledger = DataService.ExecuteSql(sub, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sidparams);
                lbledgermgmt.Text = "";
                // Activitylog
                String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                string Source = strUrl;
                int storegroupid = -1;
                string User = "Finance Admin";
                string name = txtName.Text;
                string costcategory_id =Convert.ToString(costcategoryid);
                string create = "CostCentre";

                var items = new[]
                    {
                    new { Key = "CostCentre Name", Value = name },
                    new { Key = "CostCategoryId", Value = costcategory_id },
                    new { Key = "create", Value = create },
                };
                string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, User, Description);
                Common.ShowCustomAlert(this.Page, "Success", "save successfully!", true, "/Finance/CostCentre");

            }
        }



        protected void ddlgroup_SelectedIndexChanged(object sender, EventArgs e)
        {
          
        }

        protected void selGroup_SelectedIndexChanged(object sender, EventArgs e)
        {
        }

        protected void btnupdate_Click(object sender, EventArgs e)
        {


            int Id = Convert.ToInt32(hidledger.Value);
            int groupid = Convert.ToInt32(selGroup.SelectedItem.Value);
           
            
            //int nature = Convert.ToInt32(ddlnaturetupeupdate.SelectedItem.Value);
            List<KeyValuePair<string, object>> sidparams = new List<KeyValuePair<string, object>>();
            sidparams.Add(new KeyValuePair<string, object>("id", Id));
            sidparams.Add(new KeyValuePair<string, object>("ccid", groupid));
            sidparams.Add(new KeyValuePair<string, object>("name", txtnameupdate.Text));


            string cost = "UPDATE cost_centre  SET name=@name,cost_category_id=@ccid where id=@Id";

            int centre = DataService.ExecuteSql(cost, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sidparams);
            Common.ShowCustomAlert(this.Page, "Success", "save successfully!", true, "/Finance/CostCentre");

        }

        protected void btncanel_update_Click(object sender, EventArgs e)
        {
            pnlnewledgers.Visible = false;
            pnlledger_updetes.Visible = false;
            pnlledgerdetailes.Visible = true;
        }

        protected void btncancel_Click(object sender, EventArgs e)
        {
            pnlledger_updetes.Visible = false;
            pnlledgerdetailes.Visible = true;
        }

        protected void lvledger_DataBound(object sender, EventArgs e)
        {
            if (lvledger.Items.Count > 0 && (String.IsNullOrEmpty(hidledger.Value) || hidledger.Value == "0"))
            {
                LinkButton lbtn = (LinkButton)lvledger.Items[0].FindControl("btnselect");
                if (lbtn != null && !String.IsNullOrEmpty(lbtn.Attributes["dataid"]))
                {
                    hidledger.Value = lbtn.Attributes["dataid"];
                    lvledger.SelectedIndex = 0;

                }
                Loadinfo();
            }
        }
    }
}