using MySqlX.XDevAPI.Relational;
using NPOI.SS.Formula.Functions;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using StackExchange.Redis;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Data.SqlClient;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Reflection;
using System.Text;
using System.Web;
using System.Web.DynamicData;
using System.Web.UI;
using System.Web.UI.WebControls;
using static RetalineProAgent.DataEntry;

namespace RetalineProAgent
{
    public partial class Passbook : Base.BasePartnerPage
    {
        private DataRowView previousRowView;
        private List<GridViewRow> allRows = new List<GridViewRow>();

        private decimal prevPageBalance
        {
            get
            {
                if (ViewState["PrevPageBalance"] != null)
                    return (decimal)ViewState["PrevPageBalance"];
                return 0;
            }
            set
            {
                ViewState["PrevPageBalance"] = value;
            }
        }

        private decimal OpeningBalance
        {
            get
            {
                if (ViewState["OpeningBalance"] != null)
                    return (decimal)ViewState["OpeningBalance"];
                return 0;
            }
            set
            {
                ViewState["OpeningBalance"] = value;
            }
        }

        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                // Calculate one month from today
                DateTime fromDate = DateTime.Today.AddMonths(-1);
                DateTime toDate = DateTime.Today;
                // Set the default values for the TextBoxes
                txtDateFrom.Text = fromDate.ToString("yyyy-MM-dd");
                txtDateTo.Text = toDate.ToString("yyyy-MM-dd");
            }
            getcalculation();
        }

        protected void gvPassbook_DataBound(object sender, EventArgs e)
        {
            int currentPageIndex = gvPassbook.PageIndex;
            if (currentPageIndex == 0)
            {
                foreach (TableCell cell in gvPassbook.Rows[0].Cells)
                {
                    cell.Font.Bold = true;
                }
            }
            if (currentPageIndex == gvPassbook.PageCount - 1)
            {
                int maxRow = gvPassbook.Rows.Count;
                foreach (TableCell cell in gvPassbook.Rows[maxRow - 1].Cells)
                {
                    cell.Font.Bold = true;
                }
            }
        }

        protected void gvPassbook_RowDataBound(object sender, GridViewRowEventArgs e)
        {

            
        }



        protected void SDSGroupBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("storegroup"))
                e.Command.Parameters["@storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        protected void gvPassbook_PageIndexChanged(object sender, EventArgs e)
        {
            OpeningBalance = Math.Round(OpeningBalance, 2);
            decimal previousBalance = OpeningBalance;
            int currentPage = 0;


            int currentPageIndex = gvPassbook.PageIndex;
            int pageSize = gvPassbook.PageSize;


            DataSourceSelectArguments args = new DataSourceSelectArguments();
            DataView view = (DataView)SDSPassbookEntries.Select(args);
            DataTable dt = view.ToTable();

            foreach (DataRow row in dt.Rows)
            {
                int index = dt.Rows.IndexOf(row);

                string drValue = row["dr"].ToString().Trim();
                decimal debit;
                if (!decimal.TryParse(drValue, out debit))
                {
                    debit = 0;
                }
                else
                {
                    debit = Math.Round(debit, 2);
                }
                string crValue = row["cr"].ToString().Trim();
                decimal credit;
                if (!decimal.TryParse(crValue, out credit))
                {
                    credit = 0;
                }
                else
                {
                    credit = Math.Round(credit, 2);
                }
                previousBalance =  Math.Round(previousBalance, 2);
                decimal currentBalance = Math.Round(previousBalance - debit + credit, 2);
                previousBalance = currentBalance;
                currentPage = (index + 1) / gvPassbook.PageSize;
                if ((index + 1) % gvPassbook.PageSize == 0 && currentPage == gvPassbook.PageIndex)
                {
                    prevPageBalance = previousBalance;
                    //return;

                }
            }

        }

        protected void SDSPassbookEntries_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {

        }

        protected void lbtnSearch_Click(object sender, EventArgs e)
        {
            gvPassbook.SetPageIndex(0);
        }

        protected void lbtnaction_Click(object sender, EventArgs e)
        {

            LinkButton lbtn = (LinkButton)sender;
            hidValueHeadOrderId.Value = (lbtn.Attributes["orderid"]);
            hidValueHeadStorRef.Value = (lbtn.Attributes["storeref"]);
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

        protected void lvsettlement_DataBound(object sender, EventArgs e)
        {
            List<KeyValuePair<string, object>> sqldaorderRefId = new List<KeyValuePair<string, object>>();
            sqldaorderRefId.Add(new KeyValuePair<string, object>("order_id", hidValueHeadOrderId.Value));
            sqldaorderRefId.Add(new KeyValuePair<string, object>("Refid", hidValueHeadStorRef.Value));
            string postingdetails = "with x as(SELECT de.entity_id,l.id,data_entry_id FROM transactions tr INNER JOIN  data_entry de ON tr.data_entry_id =de.id inner join [ledger] l on l.id=tr.ledger_id where de.[entity_id] = @order_id and refId =@Refid) select ROUND((SUM(CASE WHEN (tr.isDebtor = 1) THEN tr.amount END )),2) AS dr_amount,ROUND((SUM(CASE WHEN (tr.isDebtor = 0) THEN tr.amount END )),2) AS cr_amount from transactions tr INNER JOIN  data_entry de ON tr.data_entry_id =de.id inner join x on x.data_entry_id  = tr.data_entry_id inner join [ledger] l on l.id=tr.ledger_id  where x.id!=tr.ledger_id ";
            var amount = DataService.GetDataTable(postingdetails, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldaorderRefId);
            Literal ltrdrtotal = (Literal)lvsettlement.FindControl("ltrdeduction");
            Literal ltrcrtotal = (Literal)lvsettlement.FindControl("ltrsettleamount");
            if (ltrdrtotal != null && ltrcrtotal != null)
            {
                var total = amount.Rows[0];
                ltrdrtotal.Text = String.Format("{0:0.00}", total["dr_amount"]).ToString();
                ltrcrtotal.Text = String.Format("{0:0.00}", total["cr_amount"]).ToString();
            }
        }

        protected void btndownload_Click(object sender, EventArgs e)
        {
            DataView dv = (DataView)SDSPassbookEntries.Select(DataSourceSelectArguments.Empty);
            if (dv != null)
            {
                ExportToExcel(dv.ToTable(), "Merchantpassbook.xlsx");
            }

        }
        private void ExportToExcel(DataTable dt, string fileName)
        {
            IWorkbook workbook = new XSSFWorkbook();
            ISheet sheet = workbook.CreateSheet("Merchantpassbook");
            ICreationHelper helper = workbook.GetCreationHelper();
            ICellStyle dateStyle = workbook.CreateCellStyle();
            dateStyle.DataFormat = helper.CreateDataFormat().GetFormat("dd-MMM-yyyy");
            var rowIndex = 0;
            var fieldLabels = "createdOn,Date|particulars,Particulars|refernce,Reference|dr_amount,Debit|cr_amount,Credit";
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
        // opening,closing total debit and credit calculation 
        private void getcalculation()
        {
            try
            {
                if (txtDateFrom.Text != "" && txtDateTo.Text != "")
                {
                    string ledgerId = "";
                    if (ddlStoreGroupBranch.SelectedValue != null && !string.IsNullOrWhiteSpace(ddlStoreGroupBranch.SelectedValue))
                    {
                        ledgerId = ddlStoreGroupBranch.SelectedValue;
                    }
                    else
                    {
                        string storeref = "SELECT storeRefId FROM finascop_branch_group WHERE store_group_id = @storegroupid";
                        List<KeyValuePair<string, object>> ledparms = new List<KeyValuePair<string, object>>
                        {
                            new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId)
                        };
                        var storeRefId = DataServiceMySql.GetDataTable(storeref, UserService.GetAPIConnectionString(), parmeters: ledparms)
                                                         .AsEnumerable()
                                                         .Select(row => row.Field<string>("storeRefId"))
                                                         .FirstOrDefault();
                        ledgerId = storeRefId ?? ""; 
                    }
                    List<KeyValuePair<string, object>> sqldatavId = new List<KeyValuePair<string, object>>();
                    sqldatavId.Add(new KeyValuePair<string, object>("ledgerID", ledgerId));
                    sqldatavId.Add(new KeyValuePair<string, object>("fromDate", txtDateFrom.Text));
                    sqldatavId.Add(new KeyValuePair<string, object>("toDate", txtDateTo.Text));
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
                            try { OpeningBal = (double)dataEntry["OpeningBal"]; } catch { OpeningBal = 0; }

                        double ClosingBal = 0;
                        if (!(dataEntry["ClosingBal"] is DBNull))
                            try { ClosingBal = (double)dataEntry["ClosingBal"]; } catch { ClosingBal = 0; }
                        ltrPageCurStart.Text = string.Format("{0:0.00}{1}", Math.Abs(OpeningBal), OpeningBal == 0 ? "" : (OpeningBal < 0 ? " Dr." : " Cr.")); 
                        ltrPageCurTotal.Text = string.Format("{0:0.00}{1}", Math.Abs(ClosingBal),ClosingBal == 0 ? "" : (ClosingBal < 0 ? " Dr." : " Cr.")); 
                    }

                }
            }
            catch
            {

            }
        }
    }
}