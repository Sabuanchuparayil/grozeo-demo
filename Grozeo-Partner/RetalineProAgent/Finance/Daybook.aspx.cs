using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.IO;
using System.Linq;
using System.Web;
using System.Web.DynamicData;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class WebForm1: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            txtFromDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            txtToDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            //txtFromDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            //txtToDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            if (!IsPostBack)
            {
                txtFromDate.Text = DateTime.Now.AddDays(-30).ToString("yyyy-MM-dd");
                txtToDate.Text = DateTime.Now.ToString("yyyy-MM-dd");               

            }

        }       
        protected void lbSelectData_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;            
            lvdatatable.DataBind();
        }

        protected void lvdatatable_SelectedIndexChanged(object sender, EventArgs e)
        {

        }

        protected void lnkExport1_Click(object sender, EventArgs e)
        {
            DataView dv = (DataView)SDSDataEntry.Select(DataSourceSelectArguments.Empty);
            if (dv != null)
            {
                ExportToExcel(dv.ToTable(), "DayBook.xlsx", isXeroFormat: false);
            }
        }

        protected void lnbtndownload_Click(object sender, EventArgs e)
        {
            DataView dv = (DataView)SDSDataEntry.Select(DataSourceSelectArguments.Empty);
            if (dv != null)
            {
                ExportToExcel(dv.ToTable(), "XeroImportFormat.xlsx", isXeroFormat: true);
            }
        }

        private void ExportToExcel(DataTable dt, string fileName, bool isXeroFormat)
        {
            IWorkbook workbook = new XSSFWorkbook();
            ISheet sheet = workbook.CreateSheet(isXeroFormat ? "Xero Import Format" : "Day Book");
            ICreationHelper helper = workbook.GetCreationHelper();
            ICellStyle dateStyle = workbook.CreateCellStyle();
            dateStyle.DataFormat = helper.CreateDataFormat().GetFormat("dd-MMM-yyyy");

            var rowIndex = 0;

            var fieldLabels = isXeroFormat
                ? "voucherSlNoString,Narration|dateforshow,Date|reference,Description|ledger_id,AccountCode|particulars,Account Name| ,TaxRate|amount,Amount| ,TrackingName1| ,TrackingOption1| ,TrackingName2| ,TrackingOption2"
                : "dateforshow,Date|transid,Trans ID|voucherSlNoString,Voucher No.|Voucher,Voucher Type|ledger_id,Led. ID|particulars,Ledger Name|reference,Reference|dr_amount,Debit|cr_amount,Credit";

            var fields = fieldLabels
                .Split('|')
                .Select(f => new { Field = f.Split(',')[0].Trim(), Label = f.Split(',')[1].Trim() })
                .ToList();

            // Header rows
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

                    if (!string.IsNullOrWhiteSpace(field) && dt.Columns.Contains(field))
                    {
                        object value = dr[field];
                        try
                        {
                            if (field == "createdOn" && DateTime.TryParse(value?.ToString(), out DateTime dateVal))
                            {
                                cell.SetCellValue(dateVal);
                                cell.CellStyle = dateStyle;
                            }
                            else if (field == "amount" && isXeroFormat && dt.Columns.Contains("isDebtor"))
                            {
                                bool isDebit = dr["isDebtor"].ToString() == "1";
                                double.TryParse(value?.ToString(), out double num);
                                cell.SetCellValue(isDebit ? num : -num);
                            }
                            else if ((field == "dr_amount" || field == "cr_amount") && double.TryParse(value?.ToString(), out double number))
                            {
                                cell.SetCellValue(number);
                            }
                            else
                            {
                                cell.SetCellValue(helper.CreateRichTextString(value?.ToString() ?? ""));
                            }
                        }
                        catch
                        {
                            cell.SetCellValue("");
                        }
                    }
                    else if (fields[col].Label == "TaxRate")
                    {
                        cell.SetCellValue("No VAT");
                    }
                    else
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