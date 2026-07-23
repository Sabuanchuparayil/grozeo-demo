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
    public partial class GST_on_Sales95 : Base.BasePartnerPage
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

            fillSelectCommandGvTaxReport();
            gvTaxReport.Visible = true;



        }

        private void BindStatesToDdlStatesView()
        {


            DataTable states = new DataTable();
            string query = "SELECT st_name, state_code FROM finascop_state where cnt_ID = @country_code;";

            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();

            if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
            {
                prms.Add(new KeyValuePair<string, object>("country_code", 1));

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


                stateSelected();
                gvTaxReport.Visible = true;

            }
            else
            {
                fillSelectCommandGvTaxReport();
                gvTaxReport.Visible = true;

            }
            sdsGSTSalesRestaurant.DataBind();
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

    }
}