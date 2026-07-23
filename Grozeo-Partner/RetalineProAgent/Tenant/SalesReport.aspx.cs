//using RetalineProAgent.Core.BussinessModel.OnlineOrders;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
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
    public partial class SalesReport: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            int storegroupid = this.CurrentUser.APIStoreId;
            var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID,br_name FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            DataRow dr = dtBranches.Rows[0];
            string branchName = dr["br_name"].ToString();

            var btStoreGrp = DataServiceMySql.GetDataTable($"SELECT COUNT(br_storeGroup) AS cnt FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            DataRow dc = btStoreGrp.Rows[0];
            string storeGroup = dc["cnt"].ToString();
            if(Convert.ToInt32(storeGroup) == 1)
            {
                branchname.Visible = true;
                branchname.Value = dr["br_name"].ToString();
            }
            else
            {
                branchname.Visible = false;
            }
            txtDateFrom.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            txtDateTo.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));

        }
        //protected void SDSOnlineOrders_Selected(object sender, SqlDataSourceStatusEventArgs e)
        //{
        //    e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        //    int startRowOnPage = (gvOnlineOrders.PageIndex * gvOnlineOrders.PageSize) + 1;
        //    int lastRowOnPage = startRowOnPage + gvOnlineOrders.Rows.Count - 1;
        //    int totalRows = e.AffectedRows;

        //    ltrPageCurStart.Text = startRowOnPage.ToString();
        //    ltrPageCurTotal.Text = lastRowOnPage.ToString();
        //    ltrPageTotal.Text = totalRows.ToString();
        //}

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvSalesReport.PageIndex > 0)
                gvSalesReport.PageIndex = gvSalesReport.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvSalesReport.PageIndex < gvSalesReport.PageCount - 1)
                gvSalesReport.PageIndex = gvSalesReport.PageIndex + 1;
        }

        protected void gvSalesReport_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvSalesReport.PageIndex * gvSalesReport.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvSalesReport.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSSalesReport.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSOnlineOrders_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            if (selBranches.Items.Count < 1)
                selBranches.DataBind();
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchId"].Value = brid;
            }
            else
            {
                e.Command.Parameters["branchId"].Value = selBranches.Text;
            }
        }

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvSalesReport.PageIndex = 0;
            gvSalesReport.DataBind();
            ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");
        }

        protected void selBranches_DataBound(object sender, EventArgs e)
        {

            //if (selBranches.Items.Count < 1)
            //{
            //    selBranches.DataBind();
            //}
            plcSelectBranchModel.Visible = selBranches.Items.Count > 1;
            ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");

        }

        protected void SDSBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchid"].Value = brid;
            }

        }

        protected void SDSSalesReport_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            //ltrPageTotal.Text = e.AffectedRows.ToString();
        }

        private void ExportGridToExcel()
        {
            DataView dv = (DataView)SDSSalesReport.Select(DataSourceSelectArguments.Empty);
            DataTable dt = dv.ToTable();
            IWorkbook wb = new XSSFWorkbook();
            ISheet sheet = wb.CreateSheet("Data1");
            ICreationHelper cH = wb.GetCreationHelper();
            int rows = 0;
            IRow rowH = sheet.CreateRow(rows++);

            foreach (DataControlField dc in gvForExportOnly.Columns)
            {
                ICell cell = rowH.CreateCell(rowH.Cells.Count);
                cell.SetCellValue(cH.CreateRichTextString(dc.HeaderText));
            }

            foreach (DataRow dr in dt.Rows)
            {
                IRow row = sheet.CreateRow(rows++);
                for (int j = 0; j < gvForExportOnly.Columns.Count; j++)
                {
                    ICell cell = row.CreateCell(j);
                    cell.SetCellValue(cH.CreateRichTextString(dr[gvForExportOnly.Columns[j].SortExpression].ToString()));
                }
            }

            Response.Clear();
            Response.Buffer = true;
            Response.Charset = "";
            Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
            Response.AddHeader("content-disposition", "attachment;filename=PackedOrders.xlsx");
            wb.Write(Response.OutputStream);

            Response.Flush();
            Response.End();

        }

        protected void lbtnDownloadExcel_Click(object sender, EventArgs e)
        {
            if (gvSalesReport.Rows.Count == 0)
            {
                Common.ShowToastifyMessage(this.Page, "No data available to download.", "danger");
                return;
            }
            ExportGridToExcel();
        }
    }
}