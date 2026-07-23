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

namespace RetalineProAgent.Tenant.Finance
{
    public partial class SettlementReportDownload : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            txtFromDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            txtToDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            if (!IsPostBack)
            {
                txtFromDate.Text = DateTime.Now.AddDays(-15).ToString("yyyy-MM-dd");
                txtToDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            }

        }

        protected void btnDownload_Click(object sender, EventArgs e)
        {
            DownloadDataToExcel();

        }

        protected void btnSearch_Click(object sender, EventArgs e)
        {



        }

        protected void DownloadDataToExcel()
        {
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>() {
                new KeyValuePair<string, object>("fromDate", txtFromDate.Text), new KeyValuePair<string, object>("toDate", txtToDate.Text)
            };

            DataTable dataTable = DataServiceMySql.GetDataTable("getSettlementData", "", prms, true);

            //using (MySqlConnection connection = new MySqlConnection(ConfigurationManager.ConnectionStrings["mySqlConnection"].ConnectionString))
            //{
            //    using (MySqlCommand command = new MySqlCommand(SDSSettlementDownload.SelectCommand, connection))
            //    {
            //        // Add parameters
            //        command.Parameters.AddWithValue("@fromDate", txtFromDate.Text);
            //        command.Parameters.AddWithValue("@toDate", txtToDate.Text);

            //        using (MySqlDataAdapter adapter = new MySqlDataAdapter(command))
            //        {
            //            adapter.Fill(dataTable);
            //        }
            //    }
            //}

            SettlementReportDownload.ExportDataSetToExcel(dataTable, "SettlementData.xlsx");
        }


        public static void ExportDataSetToExcel(DataTable dataTable, string fileName)
        {
            XSSFWorkbook workbook = new XSSFWorkbook();
            ISheet sheet = workbook.CreateSheet("Sheet1");

            // Create header row
            IRow headerRow = sheet.CreateRow(0);
            for (int i = 0; i < dataTable.Columns.Count; i++)
            {
                headerRow.CreateCell(i).SetCellValue(dataTable.Columns[i].ColumnName);
            }

            // Populate data rows
            for (int i = 0; i < dataTable.Rows.Count; i++)
            {
                IRow dataRow = sheet.CreateRow(i + 1);
                for (int j = 0; j < dataTable.Columns.Count; j++)
                {
                    dataRow.CreateCell(j).SetCellValue(dataTable.Rows[i][j].ToString());
                }
            }

            // Write workbook to memory stream
            using (MemoryStream stream = new MemoryStream())
            {
                workbook.Write(stream);
                byte[] byteArray = stream.ToArray();

                // Offer the file as a download
                HttpContext.Current.Response.Clear();
                HttpContext.Current.Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
                HttpContext.Current.Response.AddHeader("content-disposition", $"attachment;filename={fileName}");
                HttpContext.Current.Response.BinaryWrite(byteArray);
                HttpContext.Current.Response.End();
            }
        }

        protected void SDSSettlementDownload_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {

        }
    }
}

