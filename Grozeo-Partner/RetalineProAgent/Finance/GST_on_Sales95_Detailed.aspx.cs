using Amazon.DynamoDBv2.Model;
using NPOI.SS.Formula.Functions;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data.SqlClient;
using System.Data;
using System.Linq;
using System.Runtime.InteropServices.ComTypes;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Dynamic;
using RetalineProAgent.Core.Services;
using Amazon.DynamoDBv2;
using System.Text.RegularExpressions;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using System.IO;

namespace RetalineProAgent.Finance
{
    public partial class GST_on_Sales95_Detailed : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                DateTime fromDate, toDate;
                fromDate = DateTime.Today.AddDays(-30);
                toDate = DateTime.Today;
                txtDateFrom.Text = fromDate.ToString("yyyy-MM-dd");
                txtDateTo.Text = toDate.ToString("yyyy-MM-dd");
                divDateFrom.Visible = true;
                divDateTo.Visible = true;
                states_div.Visible = false;
                BindStatesToDdlStatesView();
            }
            chkDetailed.Checked = true;
                fillSelectCommandOfsdsDetailedDownload();
                gvDetailedReport.Visible = true;
        }

        private void BindStatesToDdlStatesView()
        {
            
            
            DataTable states = new DataTable();
            string query = "SELECT st_name, state_code FROM finascop_state where cnt_ID = @country_code;";

            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();

            if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
            {
                prms.Add(new KeyValuePair<string, object>("country_code",1));

            }
            else if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
            {
                prms.Add(new KeyValuePair<string, object>("country_code", 2));

            }

            states = DataServiceMySql.GetDataTable(query, ConfigurationManager.ConnectionStrings["mySqlConnection"].ConnectionString, parmeters: prms);
            ddlStates.DataSource = states;
            ddlStates.DataBind();
        }
        protected void SDSGroupBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("storegroup"))
                e.Command.Parameters["@storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        protected void btnSearch_Click(object sender, EventArgs e)
        {
            if (ddlInvoiceGroup.SelectedValue == "StateWiseInvoices")
            {
                states_div.Visible = true;
                
                    fillSelectCommandOfsdsDetailedDownload();
                    gvDetailedReport.DataBind();
                    gvDetailedReport.Visible = true;

            }
            else
            {
                    fillSelectCommandOfsdsDetailedDownload();
                    gvDetailedReport.DataBind();

                    gvDetailedReport.Visible = true;
            }
        }

        protected void ddlPeriods_SelectedIndexChanged(object sender, EventArgs e)
        {
            // Get the selected value from ddlPeriods
            string selectedValue = ddlPeriods.SelectedValue;
            divDateFrom.Visible = true;
            divDateTo.Visible = true;
            divDateFrom.Disabled = true;
            divDateTo.Disabled = true;
            txtDateFrom.Enabled = false;
            txtDateTo.Enabled = false;

            // Calculate dates based on the selected value
            DateTime fromDate, toDate;
            switch (selectedValue)
            {
                case "DateRange":
                    // Set default dates or handle as needed
                    fromDate = DateTime.Today.AddDays(-30); // Default: 30 days ago
                    toDate = DateTime.Today; // Default: Today
                    divDateFrom.Disabled = false;
                    divDateTo.Disabled = false;
                    txtDateFrom.Enabled = true;
                    txtDateTo.Enabled =true;
                    break;
                case "MonthTillDate":
                    fromDate = new DateTime(DateTime.Today.Year, DateTime.Today.Month, 1); // Beginning of the current month
                    toDate = DateTime.Today; // Today
                    break;
                case "Last Month":
                    // Calculate dates for last month
                    fromDate = new DateTime(DateTime.Today.Year, DateTime.Today.Month - 1, 1); // Beginning of last month
                    toDate = new DateTime(DateTime.Today.Year, DateTime.Today.Month, 1).AddDays(-1); // End of last month
                    break;
                case "YearTillDate":
                    fromDate = new DateTime(DateTime.Today.Year, 1, 1); // Beginning of the year
                    toDate = DateTime.Today; // Today
                    break;
                default:
                    // Handle default case or set default dates
                    fromDate = DateTime.Today.AddDays(-30);
                    toDate = DateTime.Today;
                    divDateFrom.Disabled = false;
                    divDateTo.Disabled = false;
                    txtDateFrom.Enabled = true;
                    txtDateTo.Enabled = true;
                    break;
            }

            // Set the calculated dates to the TextBox controls
            txtDateFrom.Text = fromDate.ToString("yyyy-MM-dd");
            txtDateTo.Text = toDate.ToString("yyyy-MM-dd");

            ddlInvoiceGroup.SelectedValue = "AllInvoices";
            ddlInvoiceGroup_SelectedIndexChanged(ddlInvoiceGroup, EventArgs.Empty);
        }

        private void stateSelected()
        {
            sdsGSTSalesRestaurant.SelectCommand = "WITH InvoiceCalculations AS " +
                " (SELECT DATE(inv.created_at) AS order_date, COUNT(DISTINCT(ro.customer_order_id)) AS no_of_invoices, " +
                " ROUND(SUM(item_price), 2) AS invoice_total, ROUND(SUM(order_item_basket_price_et),2) AS order_item_basket_price_et, " +
                " ROUND(SUM(order_item_igst), 2) AS igst,ROUND(SUM(order_item_cgst), 2) AS cgst," +
                " ROUND(SUM(order_item_ugst + order_item_sgst), 2) AS sgst_or_utgst, " +
                " ROUND(SUM(order_item_cess), 2) AS compensation_cess,    " +
                " ROUND(SUM(order_item_igst + order_item_cgst + order_item_ugst + order_item_sgst + order_item_cess), 2) AS total_gst_cess " +
                " FROM retaline_customer_order re INNER JOIN retaline_customer_order_items ro ON re.order_id = ro.customer_order_id " +
                " INNER JOIN finascop_stock_itemmaster fs ON ro.item_product_id = fs.stit_ID " +
                " LEFT JOIN hsn_value hs ON hs.id = fs.stit_hsnId " +
                " INNER JOIN mypha_productsubcategory mp ON fs.product_category = mp.sub_category_id" +
                " INNER JOIN finascop_branch fb ON ro.order_branch_id = fb.br_ID " +
                " INNER JOIN `invoice_number` inv ON re.order_id = inv.order_id" +
                " WHERE hasRestaurantService = 1 AND item_order_qty >= 1 AND invoice_type = 1  AND IFNULL(inv.created_at, NULL) " +
                " AND DATE(inv.created_at) >= @fromDate AND DATE(inv.created_at) <= @toDate AND partition_prefix LIKE CONCAT(@stateCode,'%') GROUP BY DATE(inv.created_at) ORDER BY inv.created_at)" +
                " SELECT order_date, no_of_invoices, (order_item_basket_price_et)as gross_sales, igst, cgst, sgst_or_utgst, compensation_cess, total_gst_cess, ROUND(invoice_total,2) AS invoice_total FROM InvoiceCalculations;";

            sdsGSTSalesRestaurant.SelectParameters.Clear();

            ControlParameter stateParameter = new ControlParameter();
            stateParameter.ControlID = "ddlStates";
            stateParameter.Name = "stateCode";
            stateParameter.PropertyName = "SelectedValue";
            sdsGSTSalesRestaurant.SelectParameters.Add(stateParameter);


            stateParameter.ConvertEmptyStringToNull = false;

            ControlParameter fromDateParameter = new ControlParameter();
            fromDateParameter.ControlID = "txtdatefrom";
            fromDateParameter.Name = "fromDate";
            fromDateParameter.PropertyName = "Text";
            sdsGSTSalesRestaurant.SelectParameters.Add(fromDateParameter);

            // Create and add ControlParameter for toDate
            ControlParameter toDateParameter = new ControlParameter();
            toDateParameter.ControlID = "txtdateto";
            toDateParameter.Name = "toDate";
            toDateParameter.PropertyName = "Text";
            sdsGSTSalesRestaurant.SelectParameters.Add(toDateParameter);

            // Optionally, you might want to set ConvertEmptyStringToNull property
            fromDateParameter.ConvertEmptyStringToNull = false;
            toDateParameter.ConvertEmptyStringToNull = false;
        }

        private void fillSelectCommandGvTaxReport()
        {
            sdsGSTSalesRestaurant.SelectCommand = "WITH InvoiceCalculations AS " +
                    " (SELECT DATE(inv.created_at) AS order_date, COUNT(DISTINCT(customer_order_id)) AS no_of_invoices, " +
                    " ROUND(SUM(item_price), 2) AS invoice_total, ROUND(SUM(order_item_basket_price_et),2) AS order_item_basket_price_et, " +
                    " ROUND(SUM(order_item_igst), 2) AS igst,ROUND(SUM(order_item_cgst), 2) AS cgst," +
                    " ROUND(SUM(order_item_ugst + order_item_sgst), 2) AS sgst_or_utgst, " +
                    " ROUND(SUM(order_item_cess), 2) AS compensation_cess,    " +
                    " ROUND(SUM(order_item_igst + order_item_cgst + order_item_ugst + order_item_sgst + order_item_cess), 2) AS total_gst_cess " +
                    " FROM retaline_customer_order re INNER JOIN retaline_customer_order_items ro ON re.order_id = ro.customer_order_id " +
                    " INNER JOIN finascop_stock_itemmaster fs ON ro.item_product_id = fs.stit_ID " +
                    " LEFT JOIN hsn_value hs ON hs.id = fs.stit_hsnId " +
                    " INNER JOIN mypha_productsubcategory mp ON fs.product_category = mp.sub_category_id" +
                    " INNER JOIN finascop_branch fb ON ro.order_branch_id = fb.br_ID " +
                    " INNER JOIN `invoice_number` inv ON re.order_id = inv.order_id" +
                    " WHERE hasRestaurantService = 1 AND item_order_qty >= 1 AND invoice_type = 1  AND IFNULL(inv.created_at, NULL) " +
                    " AND DATE(inv.created_at) >= @fromDate AND DATE(inv.created_at) <= @toDate GROUP BY DATE(inv.created_at)  ORDER BY inv.created_at)" +
                    " SELECT order_date, no_of_invoices, (order_item_basket_price_et)as gross_sales, igst, cgst, sgst_or_utgst, compensation_cess, total_gst_cess, ROUND(invoice_total ,2) AS invoice_total FROM InvoiceCalculations;";

            sdsGSTSalesRestaurant.SelectParameters.Clear();

            ControlParameter fromDateParameter = new ControlParameter();
            fromDateParameter.ControlID = "txtdatefrom";
            fromDateParameter.Name = "fromDate";
            fromDateParameter.PropertyName = "Text";
            sdsGSTSalesRestaurant.SelectParameters.Add(fromDateParameter);

            // Create and add ControlParameter for toDate
            ControlParameter toDateParameter = new ControlParameter();
            toDateParameter.ControlID = "txtdateto";
            toDateParameter.Name = "toDate";
            toDateParameter.PropertyName = "Text";
            sdsGSTSalesRestaurant.SelectParameters.Add(toDateParameter);

            // Optionally, you might want to set ConvertEmptyStringToNull property
            fromDateParameter.ConvertEmptyStringToNull = false;
            toDateParameter.ConvertEmptyStringToNull = false;
        }

        protected void ddlInvoiceGroup_SelectedIndexChanged(object sender, EventArgs e)
        {
            if (ddlInvoiceGroup.SelectedValue == "StateWiseInvoices")
            {
                states_div.Visible = true;
                ddlStates.SelectedIndex = 0;
                ddlStates_SelectedIndexChanged(ddlStates, EventArgs.Empty);
            }
            else
            {
                states_div.Visible = false;
                fillSelectCommandGvTaxReport();
            }
        }

        protected void ddlStates_SelectedIndexChanged(object sender, EventArgs e)
        {
            stateSelected();
        }

        private void fillSelectCommandOfsdsDetailedDownload()
        {
            sdsDetailedDownload.SelectCommand = "WITH InvoiceCalculations AS ( SELECT fb.br_ID,fb.br_Name,DATE(inv.created_at) AS order_date,order_customer_name,inv_number,br_GST,hasRestaurantService, re.order_order_id, partition_prefix, COUNT(DISTINCT(ro.customer_order_id)) AS no_of_invoices, IFNULL(fis.st_name,' ') AS st_name, ROUND(SUM(item_price), 2) AS invoice_total, item_sales_price, ROUND(SUM(order_item_basket_price_et),2) AS order_item_basket_price_et, ROUND(SUM(order_item_igst), 2) AS igst, ROUND(SUM(order_item_cgst), 2) AS cgst,ROUND(SUM(order_item_ugst + order_item_sgst), 2) AS sgst_or_utgst, ROUND(SUM(order_item_cess), 2) AS compensation_cess, ROUND(SUM(order_item_igst + order_item_cgst + order_item_ugst + order_item_sgst + order_item_cess), 2) AS total_gst_cess " +
                "FROM retaline_customer_order re " +
                "INNER JOIN retaline_customer_order_items ro ON re.order_id = ro.customer_order_id " +
                "INNER JOIN retaline_customer_order_delivery_address rca ON re.order_id = rca.customer_order_id " +
                "INNER JOIN finascop_stock_itemmaster fs ON ro.item_product_id = fs.stit_ID " +
                "INNER JOIN mypha_productsubcategory mp ON fs.product_category = mp.sub_category_id " +
                "INNER JOIN finascop_branch fb ON ro.order_branch_id = fb.br_ID " +
                "INNER JOIN `invoice_number` inv ON re.order_id = inv.order_id " +
                "INNER JOIN finascop_state fis  ON fis.st_ID = fb.br_State  " +
                "WHERE hasRestaurantService = 1 AND item_order_qty >= 1 AND invoice_type = 1  AND IFNULL(inv.created_at, NULL) AND DATE(inv.created_at) >= @fromDate AND DATE(inv.created_at) <= @toDate AND partition_prefix LIKE CONCAT(COALESCE(@stateCode,''),'%') GROUP BY re.order_id ORDER BY inv.created_at) " +
                "SELECT  order_date, inv_number,br_ID,br_Name,order_customer_name, br_GST, st_name,  order_order_id, partition_prefix,hasRestaurantService, no_of_invoices,  order_item_basket_price_et AS gross_sales, item_sales_price, igst,  cgst,  sgst_or_utgst,  compensation_cess,  total_gst_cess,  ROUND(invoice_total,2) AS invoice_total FROM  InvoiceCalculations;";
            
            
            sdsDetailedDownload.SelectParameters.Clear();

            if (ddlInvoiceGroup.SelectedValue == "StateWiseInvoices")
            {
                ControlParameter stateParameter = new ControlParameter();
                stateParameter.ControlID = "ddlStates";
                stateParameter.Name = "stateCode";
                stateParameter.PropertyName = "SelectedValue";
                sdsDetailedDownload.SelectParameters.Add(stateParameter);

                stateParameter.ConvertEmptyStringToNull = false;
            }
            else
            {
                Parameter stateCodeParameter = new Parameter();
                stateCodeParameter.Name = "stateCode";
                stateCodeParameter.DefaultValue = ""; // Setting the default value to an empty string
                sdsDetailedDownload.SelectParameters.Add(stateCodeParameter);

                stateCodeParameter.ConvertEmptyStringToNull = false;
            }

            ControlParameter fromDateParameter = new ControlParameter();
            fromDateParameter.ControlID = "txtdatefrom";
            fromDateParameter.Name = "fromDate";
            fromDateParameter.PropertyName = "Text";
            sdsDetailedDownload.SelectParameters.Add(fromDateParameter);

            // Create and add ControlParameter for toDate
            ControlParameter toDateParameter = new ControlParameter();
            toDateParameter.ControlID = "txtdateto";
            toDateParameter.Name = "toDate";
            toDateParameter.PropertyName = "Text";
            sdsDetailedDownload.SelectParameters.Add(toDateParameter);

            // Optionally, you might want to set ConvertEmptyStringToNull property
            fromDateParameter.ConvertEmptyStringToNull = false;
            toDateParameter.ConvertEmptyStringToNull = false;

        }

        protected void btnDownload_Click(object sender, EventArgs e)
        {

            fillSelectCommandOfsdsDetailedDownload();

            sdsDetailedDownload.DataBind();

            DataView dv = (DataView)sdsDetailedDownload.Select(DataSourceSelectArguments.Empty);
            if (dv == null)
            {
                return;
            }

            DataTable dt = dv.ToTable();
            int settlecount = dv.Count;
            IWorkbook wb = new XSSFWorkbook();
            ISheet sheet = wb.CreateSheet("GST Restaurant Report u_s 95");
            ICreationHelper cH = wb.GetCreationHelper();
            int rows = 0;
            IRow rowH = sheet.CreateRow(rows++);
            var strFieldLabels = "order_date,Invoice Date|inv_number,Invoice No|order_order_id,Order ID|br_Name,Invoiced on Behalf of|order_customer_name,Invoiced To|br_GST,GSTIN|st_name,State|gross_sales,Gross Sales|igst,IGST|cgst,CGST|sgst_or_utgst,SGST/UTGST|compensation_cess,Compensation Cess|total_gst_cess,Total GST and Cess|invoice_total,Invoice Total".Split('|');
            foreach (string dc in strFieldLabels)
            {
                ICell cell = rowH.CreateCell(rowH.Cells.Count);
                cell.SetCellValue(cH.CreateRichTextString(dc.Split(',')[1]));
            }

            foreach (DataRow dr in dt.Rows)
            {
                IRow row = sheet.CreateRow(rows++);
                int columnIndex = 0; // Track the index of the column

                foreach (string dc in strFieldLabels)
                {
                    string columnName = dc.Split(',')[0]; // Get the column name from the label
                    object cellValue = dr[columnName]; // Get the corresponding value from the DataRow

                    ICell cell = row.CreateCell(columnIndex++);

                    // Check if the cell value is not DBNull
                    if (cellValue != DBNull.Value)
                    {
                        // Depending on the type of cellValue, you might need to convert it appropriately
                        if (cellValue is DateTime)
                        {
                            
                            ICellStyle style = wb.CreateCellStyle();
                            IDataFormat dataFormatCustom = wb.CreateDataFormat();
                            style.DataFormat = dataFormatCustom.GetFormat("dd-MMM-yyyy");
                            cell.CellStyle.IsLocked = false;
                            cell.CellStyle = style;
                            cell.CellStyle.IsLocked = true;

                            cell.SetCellValue((DateTime)cellValue);
                        }
                        else if (cellValue is string)
                        {
                            cell.SetCellValue((string)cellValue);
                        }
                        else if (cellValue is double || cellValue is int || cellValue is float || cellValue is long)
                        {
                            ICellStyle style = wb.CreateCellStyle();
                            IDataFormat format = wb.CreateDataFormat();
                            string desiredFormat = "0.00";
                            style.DataFormat = format.GetFormat(desiredFormat);
                            cell.CellStyle = style;
                            cell.SetCellValue(Convert.ToDouble(cellValue));
                        }
                        // Add more conditions as needed for other data types
                    }
                    // If the cell value is DBNull, you can optionally set it to an empty string or handle it as needed
                    else
                    {
                        cell.SetCellValue("");
                    }
                }
            }
            string pmr_prefix = "GST_REP95";
            string pmr_date_format_prefix = DateTime.Now.ToString("ddMMMyyHHmmssfff").ToUpper();


            string fileNamenew = pmr_prefix + pmr_date_format_prefix + ".xlsx"; //"GMS_" + DateTime.Now.ToString("ddMMyyyy") + ".xlsx";
            string strFileUrl = "";
            string tempDir = HttpContext.Current.Server.MapPath("~/App_Data/Temp");
            // Ensure the directory exists
            if (!Directory.Exists(tempDir))
            {
                Directory.CreateDirectory(tempDir);
            }
            // Combine the directory and file name to get the full temporary file path
            string tempFilePath = Path.Combine(tempDir, fileNamenew);
            // Create a FileStream to write the Excel file content to the temporary file
            using (FileStream fs = new FileStream(tempFilePath, FileMode.Create, FileAccess.Write))
            {
                wb.Write(fs);
            }

            System.IO.File.Delete(tempFilePath);

            Response.Clear();
            Response.Buffer = true;
            Response.Charset = "";
            Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";

            Response.AddHeader("content-disposition", "attachment;filename=" + fileNamenew);
            wb.Write(Response.OutputStream);
            Response.Flush();
            Response.End();
        }

        protected void chkDetailed_CheckedChanged(object sender, EventArgs e)
        {

            if (ddlInvoiceGroup.SelectedValue == "StateWiseInvoices")
            {
                states_div.Visible = true;

                    fillSelectCommandOfsdsDetailedDownload();
                    gvDetailedReport.DataBind();
                    gvDetailedReport.Visible = true;

            }
            else
            {
                    fillSelectCommandOfsdsDetailedDownload();
                    gvDetailedReport.DataBind();
                    gvDetailedReport.Visible = true;

            }
            sdsGSTSalesRestaurant.DataBind();
        }
    }
}