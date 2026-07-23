using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.IO;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using MySql.Data.MySqlClient;
using Newtonsoft.Json;
using NPOI.OpenXmlFormats.Spreadsheet;
using NPOI.POIFS.Properties;
using NPOI.SS.Util.CellWalk;
using NPOI.Util;
using Org.BouncyCastle.Asn1.Ocsp;
using RestSharp;
using RetalineProAgent.Tenant.Finance;
using System.Configuration;

namespace RetalineProAgent
{
    public partial class Customers: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            ltrBranchName.Text = "All Branch";
            int storegroupid = this.CurrentUser.APIStoreId;
            var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID,br_name FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            DataRow dr = dtBranches.Rows[0];
            string branchName = dr["br_name"].ToString();

            var btStoreGrp = DataServiceMySql.GetDataTable($"SELECT COUNT(br_storeGroup) AS cnt FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            DataRow dc = btStoreGrp.Rows[0];
            string storeGroup = dc["cnt"].ToString();
            if (Convert.ToInt32(storeGroup) == 1)
            {
                branchname.Visible = true;
                branchname.Value = dr["br_name"].ToString();
            }
            else
            {
                branchname.Visible = false;
            }

        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvCustomers.PageIndex > 0)
                gvCustomers.PageIndex = gvCustomers.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvCustomers.PageIndex < gvCustomers.PageCount - 1)
                gvCustomers.PageIndex = gvCustomers.PageIndex + 1;
        }

        protected void gvCustomers_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvCustomers.PageIndex * gvCustomers.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvCustomers.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSCustomers.Select(DataSourceSelectArguments.Empty);
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            if (selBranch.Items.Count < 1)
            {
                selBranch.DataBind();
            }
        }

        protected void SDSCustomers_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            //if (selBranches.Items.Count < 1)
            //    selBranches.DataBind();
            //if (Page.User.IsInRole("BranchManager"))
            //{
            //    int brid = UserService.UserRoleBranchId;
            //    e.Command.Parameters["branchId"].Value = brid;
            //}
            //else
            //{
            //    e.Command.Parameters["branchId"].Value = selBranches.Text;
            //}
        }

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvCustomers.PageIndex = 0;
            gvCustomers.DataBind();
            ltrBranchName.Text = (selBranch.SelectedIndex >= 0 ? selBranch.SelectedItem.Text : "");
        }

        protected void selBranches_DataBound(object sender, EventArgs e)
        {
            //MyBranches = (List<Store>)e.ReturnValue;
            //plcSelectBranchModel.Visible = selBranch.Items.Count > 2;
            if (selBranch.Items.Count > 1)
            {
                plcSelectBranchModel.Visible = selBranch.Items.Count > 2;

            }
        }

        protected void SDSBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
            if (Page.User.IsInRole("BranchManager") && e.Command.Parameters.Contains("branchid"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchid"].Value = brid;
            }

        }

        protected void btnview_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            hidValueHeadOrderId.Value = (lbtn.Attributes["recid"]);
            string Id = hidValueHeadOrderId.Value;
            hidestoreid.Value= (this.CurrentUser.APIStoreId).ToString();
            //popup Action
            string strAlertSCript = "$('#Pupaction').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void lbtnDownload_Click(object sender , EventArgs e)
        {
            DownloadDataToExcel();
            
        }

        protected void DownloadDataToExcel()
        {
            // Get data from the SqlDataSource
            DataView dv = (DataView)SDSCustomers.Select(DataSourceSelectArguments.Empty);
            DataTable dt = dv.ToTable();

            // Create a new DataTable with only the specified columns
            DataTable dtExport = new DataTable();
            dtExport.Columns.Add("Mobile");
            dtExport.Columns.Add("Name");
            dtExport.Columns.Add("Email");
            dtExport.Columns.Add("Registered On", typeof(DateTime));
            dtExport.Columns.Add("Orders", typeof(int));

            // Populate the new DataTable with data from the original DataTable
            foreach (DataRow row in dt.Rows)
            {
                dtExport.Rows.Add(row["cust_mobile"], row["cust_customer_name"], row["cust_email"],
                                  DateTime.Parse(row["cust_created_at"].ToString()), row["cust_orders"]);
            }

            // Create a new Excel workbook
            string attachment = "attachment; filename=CustomersData.xlsx";
            Response.ClearContent();
            Response.AddHeader("content-disposition", attachment);
            Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";

            // Write the data to the Excel file
            StringWriter sw = new StringWriter();
            HtmlTextWriter htw = new HtmlTextWriter(sw);
            GridView gv = new GridView();
            gv.DataSource = dtExport;
            gv.DataBind();
            gv.RenderControl(htw);
            Response.Write(sw.ToString());
            Response.End();
        }

    }

    

}



