using MySql.Data.MySqlClient;
using Newtonsoft.Json;
using NPOI.OpenXmlFormats.Spreadsheet;
using NPOI.POIFS.Properties;
using NPOI.SS.UserModel;
using NPOI.SS.Util.CellWalk;
using NPOI.Util;
using NPOI.XSSF.UserModel;
using Org.BouncyCastle.Asn1.Ocsp;
using RestSharp;
using RetalineProAgent.Core.BussinessModel;
using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Service;
using StackExchange.Redis;
using System;
using System.Data;
using System.IO;
using System.Collections.Generic;
using System.Configuration;
using System.Data.SqlTypes;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using RetalineProAgent.Tenant.Finance;

namespace RetalineProAgent.Tenant
{
    public partial class CustomerWishlist : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
           
            
        }
        protected void lbtnDownloadExcel_Click(object sender,EventArgs e)
        {
            ExportGridToExcel();
        }
        protected void selStatus_SelectedIndexChanged(object sender,EventArgs e)
        {

        }
        protected void DeleteItem_Click(object sender, EventArgs e) 
        {

        }

        private void ExportGridToExcel()
        {
            DataView dv = (DataView)SSDSCustomerWishlist.Select(DataSourceSelectArguments.Empty);
            DataTable dt = dv.ToTable();
            IWorkbook wb = new XSSFWorkbook();
            ISheet sheet = wb.CreateSheet("Data1");
            ICreationHelper cH = wb.GetCreationHelper();
            int rows = 0;
            IRow rowH = sheet.CreateRow(rows++);

            foreach (DataControlField dc in gvCustomerWishlist.Columns)
            {
                ICell cell = rowH.CreateCell(rowH.Cells.Count);
                cell.SetCellValue(cH.CreateRichTextString(dc.HeaderText));
            }

            foreach (DataRow dr in dt.Rows)
            {
                IRow row = sheet.CreateRow(rows++);
                for (int j = 0; j < gvCustomerWishlist.Columns.Count; j++)
                {
                    ICell cell = row.CreateCell(j);
                    cell.SetCellValue(cH.CreateRichTextString(dr[gvCustomerWishlist.Columns[j].SortExpression].ToString()));
                }
            }

            Response.Clear();
            Response.Buffer = true;
            Response.Charset = "";
            Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
            Response.AddHeader("content-disposition", "attachment;filename=CustomerWishlist.xlsx");
            wb.Write(Response.OutputStream);

            Response.Flush();
            Response.End();

        }

        protected void btnreset_Click(object sender, EventArgs e)
        {
            int BranchID = Convert.ToInt32(Request.QueryString["BranchId"]);
            Response.Redirect($"~/Tenant/CustomerWishlist.aspx?BranchId={BranchID}");
        }
    }
}