using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class Taxreport: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            txtDateFrom.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            txtDateTo.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            txtDateFrom.Visible = seldate.Text == "2";
            txtDateTo.Visible = seldate.Text == "2";
            pnlDateRange.Visible = seldate.Text == "2";
            plcSelectmonth.Visible = seldate.Text == "1";
        }
        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvtaxreport.PageIndex > 0)
                gvtaxreport.PageIndex = gvtaxreport.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvtaxreport.PageIndex < gvtaxreport.PageCount - 1)
                gvtaxreport.PageIndex = gvtaxreport.PageIndex + 1;
        }

        protected void gvtaxreport_RowDataBound(object sender, GridViewRowEventArgs e)
        {
         
        }

        protected void ODStaxreport_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }
        private void ExportGridToExcel(int month)
        {
            if (month <= 0)
                return;

            SDSreport.SelectParameters["month"].DefaultValue = month.ToString();
            DataView dv = (DataView)SDSreport.Select(DataSourceSelectArguments.Empty);
            DataTable dt = dv.ToTable();
            IWorkbook wb = new XSSFWorkbook();
            ISheet sheet = wb.CreateSheet("Tax Report");
            ICreationHelper cH = wb.GetCreationHelper();
            int rows = 0;
            IRow rowH = sheet.CreateRow(rows++);

            var strFieldLabels = "br_gst,Seller Gstin|invoiceNumber,Invoice Number|invoiceDate,Invoice Date|payment_mode,Transaction Type|order_order_id,Order Id|order_confirm_date,Order Date|item_order_qty,Quantity|productname,Item Description| ,Gpin| ,Hsn/sac| ,Sku |br_City,Bill From Store|billstate,Bill From State|br_pincode,Bill From Postal Code|br_City,Ship From Store|br_State,Ship From State|br_pincode,Ship From Postal Code|order_city,Ship To City |order_state,Ship To State |order_pin,Ship To Postal Code|invoiceValue,Invoice Amount|order_item_mrp_et,Tax Exclusive Gross|order_item_gst,Total Tax Amount|order_item_cgst,Cgst Rate|order_item_sgst,Sgst Rate|order_item_ugst,Utgst Rate|order_item_igst,Igst Rate|item_kfc,Compensatory Cess Rate|item_sales_price,Product Amount Total|item_price, Product Value|order_item_tcs_cgst,Cgst Tax|order_item_tcs_sgst,Sgst Tax|order_item_tcs_igst,Igst Tax| order_item_tcs_utgst,Utgst Tax |  ,Compensatory Cess Tax|order_delivery_charge,Shipping Amount Total|order_delivery_charge_gst,Shipping Amount|order_delivery_charge_cgst,Shipping Cgst Tax|order_delivery_charge_sgst,Shipping Sgst Tax|order_delivery_charge_utgst,Shipping Utgst Tax|order_delivery_charge_igst,Shipping Igst Tax|0,Shipping Cess Tax Amount|order_tcs_cgst,Tcs Cgst Amount|CFG,Tcs Sgst Rate|order_tcs_sgst,Tcs Sgst Amount|CFG,Tcs Utgst Rate|order_tcs_utgst,Tcs Utgst Amount|CFG,Tcs Igst Rate|order_tcs_igst,Tcs Igst Amount|paymentcode,Payment Method Code|  ,Credit Note No| ,Credit Note Date".Split('|');
            foreach (string dc in strFieldLabels)
            {
                ICell cell = rowH.CreateCell(rowH.Cells.Count);
                cell.SetCellValue(cH.CreateRichTextString(dc.Split(',')[1]));
            }

            foreach (DataRow dr in dt.Rows)
            {
                IRow row = sheet.CreateRow(rows++);
                for (int j = 0; j < strFieldLabels.Length; j++)
                {
                    ICell cell = row.CreateCell(j);
                    string strField = strFieldLabels[j].Split(',')[0];
                    string cellVal = "";
                    if (!String.IsNullOrEmpty(strField.Trim()) && dr.Table.Columns.Contains(strField))
                        try { cellVal = dr[strField].ToString(); } catch { }
                    cell.SetCellValue(cH.CreateRichTextString(cellVal));
                }
            }

            Response.Clear();
            Response.Buffer = true;
            Response.Charset = "";
            Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
            Response.AddHeader("content-disposition", "attachment;filename=TaxReport.xlsx");
            wb.Write(Response.OutputStream);

            Response.Flush();
            Response.End();

        }


        protected void btndownload_Click(object sender, EventArgs e)
        {
            Button btn = (Button)sender;
            int month = -1;
            if(btn!=null && !String.IsNullOrEmpty(btn.Attributes["month"]))
                try { month = Convert.ToInt32(btn.Attributes["month"]); } catch { }
            ExportGridToExcel(month);
        }


        protected void gvtaxreport_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvtaxreport.PageIndex * gvtaxreport.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvtaxreport.Rows.Count - 1;
        }
    }
}