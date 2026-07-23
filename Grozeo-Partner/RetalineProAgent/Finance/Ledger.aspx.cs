using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using NPOI.SS.Formula.Functions;
using System;
using System.Collections.Generic;
using System.Data;
using System.Globalization;
using System.Linq;
using System.Security.Cryptography;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using static NPOI.HSSF.Util.HSSFColor;
using System.Web.Util;
using System.Configuration;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using System.IO;

namespace RetalineProAgent.Finance
{
    public partial class Ledger: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            txtFromDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            txtToDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            if (!IsPostBack)
            {
                txtFromDate.Text = DateTime.Now.AddDays(-30).ToString("yyyy-MM-dd");
                txtToDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            }

            getcalculation();
        }
        protected void lvLedger_DataBound(object sender, EventArgs e)
        {
            

        }

        protected void lbtnSearch_Click(object sender, EventArgs e)
        {

        }
        private void getcalculation()
        {
            try
            {
                if (txtFromDate.Text != "" && txtToDate.Text != "")
                {
                    string ledgerId = "";
                    if (!String.IsNullOrEmpty(selLedger.Text))
                        try { ledgerId = (selLedger.SelectedValue); } catch { ledgerId = ""; }
                    List<KeyValuePair<string, object>> sqldatavId = new List<KeyValuePair<string, object>>();
                    sqldatavId.Add(new KeyValuePair<string, object>("ledgerID", ledgerId));
                    sqldatavId.Add(new KeyValuePair<string, object>("fromDate", txtFromDate.Text));
                    sqldatavId.Add(new KeyValuePair<string, object>("toDate", txtToDate.Text));

                    string ledger = $"SELECT refId, (SUM(COALESCE(CASE WHEN isDebtor = 1  AND CAST([tr].[createdOn] as date)  < @fromDate THEN tr.amount END,0)) -" +
                        $" SUM(COALESCE(CASE WHEN isDebtor = 0  AND CAST([tr].[createdOn] as date)  <=  @fromDate THEN tr.amount END,0))) AS OpeningBal," +
                        $"(SUM(COALESCE(CASE WHEN isDebtor = 1  AND CAST([tr].[createdOn] as date)  <= @toDate  THEN tr.amount END,0)) -" +
                        $" SUM(COALESCE(CASE WHEN isDebtor = 0  AND CAST([tr].[createdOn] as date)  <=  @toDate THEN tr.amount END,0))) AS ClosingBal," +
                       $" (SUM(COALESCE(CASE WHEN isDebtor = 1  AND CAST([tr].[createdOn] as date) >=  @fromDate AND CAST([tr].[createdOn] as date)  <= @toDate THEN tr.amount END, 0))) as dr," +
                       $"(SUM(COALESCE(CASE WHEN isDebtor = 0  AND CAST([tr].[createdOn] as date) >=  @fromDate AND CAST([tr].[createdOn] as date)  <= @toDate THEN tr.amount END, 0))) as cr" +
                        $" FROM [transactions] tr INNER JOIN [data_entry] de on data_entry_id = de.id INNER JOIN ledger l on l.id=tr.ledger_id WHERE @ledgerID = '' or [refId] =@ledgerID group by refId ";

                    var payment = DataService.GetDataTable(ledger, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldatavId);
                    if (payment != null && payment.Rows.Count > 0)
                    {
                        var dataEntry = payment.Rows[0];
                        ltrtotaldebit.Text = String.Format("{0:0.00}", dataEntry["dr"]).ToString();
                        ltrtotalcredit.Text = String.Format("{0:0.00}", dataEntry["cr"]).ToString();
                        double OpeningBal = 0;
                        if (!(dataEntry["OpeningBal"] is DBNull))
                            try { OpeningBal = (double)dataEntry["OpeningBal"]; } catch { OpeningBal = -0; }

                        double ClosingBal = 0;
                        if (!(dataEntry["ClosingBal"] is DBNull))
                            try { ClosingBal = (double)dataEntry["ClosingBal"]; } catch { ClosingBal = -0; }

                        ltrPageCurStart.Text = string.Format("{0:0.00}{1}", Math.Abs(OpeningBal), OpeningBal == 0 ? "" : (OpeningBal < 0 ? " Cr." : " Dr."));
                        ltrPageCurTotal.Text = string.Format("{0:0.00}{1}", Math.Abs(ClosingBal), ClosingBal == 0 ? "" : (ClosingBal < 0 ? " Cr." : " Dr."));

                    }

                }
            }
            catch
            {

            }
        }

        protected void lbtnDownload_Click(object sender, EventArgs e)
        {
            DataView dv = (DataView)SDSLedger.Select(DataSourceSelectArguments.Empty);
            if (dv != null)
            {
                ExportToExcel(dv.ToTable(), "Ledger.xlsx");
            }
        }
        private void ExportToExcel(DataTable dt, string fileName)
        {
            IWorkbook workbook = new XSSFWorkbook();
            ISheet sheet = workbook.CreateSheet("Ledger");
            ICreationHelper helper = workbook.GetCreationHelper();
            ICellStyle dateStyle = workbook.CreateCellStyle();
            dateStyle.DataFormat = helper.CreateDataFormat().GetFormat("dd-MMM-yyyy");
            var rowIndex = 0;
            var fieldLabels = "createdOn,Date|voucherSlNostring,Voucher Number|opposite_ledgers_with_amounts,Particulars|debit_column,Debit|credit_column,Credit|balance_column,Balance|drcr_column,Dr/Cr";
            var fields = fieldLabels.Split('|').Select(f => new { Field = f.Split(',')[0].Trim(), Label = f.Split(',')[1].Trim() }).ToList();
            IRow fieldHeaderRow = sheet.CreateRow(rowIndex++);
            for (int i = 0; i < fields.Count; i++)
            {
                fieldHeaderRow.CreateCell(i).SetCellValue(fields[i].Label);
            }
            foreach (DataRow dr in dt.Rows)
            {
                IRow row = sheet.CreateRow(rowIndex++);
                for (int col = 0; col < fields.Count; col++)
                {
                    var field = fields[col].Field;
                    ICell cell = row.CreateCell(col);

                    if (string.IsNullOrWhiteSpace(field))
                    {
                        cell.SetCellValue("");
                        return;
                    }

                    try
                    {
                        switch (field)
                        {
                            case "debit_column":
                                if (dr["selected_ledger_isDebtor"]?.ToString() == "1" &&
                                    double.TryParse(dr["selected_ledger_amount"]?.ToString(), out double drAmt))
                                    cell.SetCellValue(drAmt);
                                else
                                    cell.SetCellValue("");
                                break;

                            case "credit_column":
                                if (dr["selected_ledger_isDebtor"]?.ToString() == "0" &&
                                    double.TryParse(dr["selected_ledger_amount"]?.ToString(), out double crAmt))
                                    cell.SetCellValue(crAmt);
                                else
                                    cell.SetCellValue("");
                                break;

                            case "balance_column":
                                if (double.TryParse(dr["selected_ledger_closingbalance"]?.ToString(), out double balance))
                                    cell.SetCellValue(Math.Abs(balance));
                                else
                                    cell.SetCellValue("");
                                break;

                            case "drcr_column":
                                if (double.TryParse(dr["selected_ledger_closingbalance"]?.ToString(), out double drcrBalance))
                                    cell.SetCellValue(drcrBalance < 0 ? "Cr" : "Dr");
                                else
                                    cell.SetCellValue("");
                                break;

                            default:
                                if (dt.Columns.Contains(field))
                                {
                                    object value = dr[field];
                                    if (field == "createdOn" && DateTime.TryParse(value?.ToString(), out DateTime dateVal))
                                    {
                                        cell.SetCellValue(dateVal);
                                        cell.CellStyle = dateStyle;
                                    }
                                    else if (double.TryParse(value?.ToString(), out double number))
                                    {
                                        cell.SetCellValue(number);
                                    }
                                    else
                                    {
                                        cell.SetCellValue(helper.CreateRichTextString(value?.ToString() ?? ""));
                                    }
                                }
                                else
                                {
                                    cell.SetCellValue("");
                                }
                                break;
                        }
                    }
                    catch
                    {
                        cell.SetCellValue("");
                    }

                }
            }

            // Export to response
            using (MemoryStream ms = new MemoryStream())
            {
                workbook.Write(ms);
                byte[] file = ms.ToArray();

                HttpContext.Current.Response.Clear();
                HttpContext.Current.Response.Buffer = true;
                HttpContext.Current.Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
                HttpContext.Current.Response.AddHeader("content-disposition", $"attachment;filename={fileName}");
                HttpContext.Current.Response.BinaryWrite(file);
                HttpContext.Current.Response.Flush();
                HttpContext.Current.Response.End();
            }
        }
    }
}
