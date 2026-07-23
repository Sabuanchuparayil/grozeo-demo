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

namespace RetalineProAgent.Finance
{
    public partial class Costpurpose : System.Web.UI.Page
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
       

        protected void btnsave_Click(object sender, EventArgs e)
        {


           
            List<KeyValuePair<string, object>> sidparams = new List<KeyValuePair<string, object>>();
            sidparams.Add(new KeyValuePair<string, object>("name", txtGroupName.Text));
            
            string cnt = null;
            DataTable groupCount = DataService.GetDataTable($"select count(1) as count from cost_purpose where [cost_purpose]= @name", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sidparams);

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
                string sub = "insert into [cost_purpose] ([cost_purpose])   values(@name)";
                int suggroup = DataService.ExecuteSql(sub, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, sidparams);
                lbgroupid.Text = "";
                Common.ShowCustomAlert(this.Page, "Success", "saved successfully!", true, "/Finance/Costpurpose");
            }
        }     
        protected void btncancel_Click(object sender, EventArgs e)
        {
            
        }

        protected void dlentrypeudate_DataBound(object sender, EventArgs e)
        {


        }

        protected void btncanel_update_Click(object sender, EventArgs e)
        {
           
        }

        protected void SDScostpurpose_Updating(object sender, SqlDataSourceCommandEventArgs e)
        {

        }
       
        protected void gvcostpurpose_DataBound(object sender, EventArgs e)
        {

        }
        
        protected void gvcostpurpose_PageIndexChanged(object sender, EventArgs e)
        {

        }

        protected void gvcostpurpose_RowUpdating(object sender, GridViewUpdateEventArgs e)
        {
           

        }

        protected void SDSCostpurpose_Updating1(object sender, SqlDataSourceCommandEventArgs e)
        {

        }
    }
}