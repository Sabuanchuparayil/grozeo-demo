using NPOI.HSSF.Record;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class SalesReport: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {

                txtDateFrom.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
                txtDateTo.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
                lbtnSearch.Enabled = true;
                txtDateFrom.Enabled = true;
                txtDateTo.Enabled = true;
                DateTime today = DateTime.Now; // Current date
                DateTime oneMonthAgo = today.AddMonths(-1).AddDays(1); // Same day, previous month

                txtDateFrom.Text = oneMonthAgo.ToString("yyyy-MM-dd");
                txtDateTo.Text = DateTime.Now.ToString("yyyy-MM-dd");
            }
            //gvdailySalesReport.DataSource = SDSSalesReports;
            gvdailySalesReport.DataBind();
        }
        protected void Page_PreRender(object sender, EventArgs e)
        {

           
        }
        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            //if (gvdailySalesReport.PageIndex > 0)
            //    gvdailySalesReport.PageIndex = gvdailySalesReport.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            //if (gvdailySalesReport.PageIndex < gvdailySalesReport.PageCount - 1)
            //    gvdailySalesReport.PageIndex = gvdailySalesReport.PageIndex + 1;
        }



        protected void SDSSalesReport_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            //e.InputParameters["storegroupid"] = this.CurrentUser.APIStoreId;
        }

        protected void SDSSalesReports_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }

        protected void gvdailySalesReport_RowDataBound1(object sender, GridViewRowEventArgs e)
        {
            Literal itra = (Literal)e.Row.FindControl("ltrAmountdue");
            if (itra == null)
                return;

            double sales = 0; try { sales = Convert.ToDouble(DataBinder.Eval(e.Row.DataItem, "totalamount")); } catch { }
            double taxes = 0; try { taxes = Convert.ToDouble(DataBinder.Eval(e.Row.DataItem, "tax")); } catch { }
            double bankCharges = 0; try { bankCharges = Convert.ToDouble(DataBinder.Eval(e.Row.DataItem, "bankcharges")); } catch { }
            double delCharges = 0; try { delCharges = Convert.ToDouble(DataBinder.Eval(e.Row.DataItem, "deliverycharge")); } catch { }
            double tcs = 0; try { tcs = Convert.ToDouble(DataBinder.Eval(e.Row.DataItem, "tcs")); } catch { }
            double tds = 0; try { tds = Convert.ToDouble(DataBinder.Eval(e.Row.DataItem, "tds")); } catch { }
            double orderRefund = 0; try { orderRefund = Convert.ToDouble(DataBinder.Eval(e.Row.DataItem, "orderRefund")); } catch { }
            double delCharges1 = 0; try { delCharges1 = Convert.ToDouble(DataBinder.Eval(e.Row.DataItem, "DeliveryExpenses")); } catch { }
            double pgcharges = 0; try { pgcharges = Convert.ToDouble(DataBinder.Eval(e.Row.DataItem, "pgcharges")); } catch { }
            double totalpr = 0; try { totalpr = Convert.ToDouble(DataBinder.Eval(e.Row.DataItem, "totalpr")); } catch { }
            double total = sales + delCharges + taxes;
            double amountdue = totalpr - pgcharges - delCharges1 - tcs - tds - orderRefund;
            itra.Text = String.Format("{0:0.00}", amountdue.ToString());

            //int startRowOnPage = (gvdailySalesReport.PageIndex * gvdailySalesReport.PageSize) + 1;
            //  int lastRowOnPage = startRowOnPage + gvdailySalesReport.Rows.Count - 1;
            e.Row.Attributes.Add("data-toggle", "collapse");
          e.Row.Attributes.Add("data-target", String.Format("#collapse{0}", e.Row.DataItemIndex));
          e.Row.Attributes.Add("aria-expanded", "false");
          e.Row.Attributes.Add("aria-controls", String.Format("collapse{0}", e.Row.DataItemIndex));
        }

        protected void gvdailySalesReport_DataBound(object sender, EventArgs e)
        {

        }

        protected void ddlPeriods_SelectedIndexChanged(object sender, EventArgs e)
        {
            string selectedValue = ddlPeriods.SelectedValue;
            switch (selectedValue)
            {
                case "DateRange":
                    lbtnSearch.Enabled = true;
                    txtDateFrom.Enabled = true;
                    txtDateTo.Enabled = true;
                    DateTime today = DateTime.Now; // Current date
                    DateTime oneMonthAgo = today.AddMonths(-1).AddDays(1); // Same day, previous month

                    txtDateFrom.Text = oneMonthAgo.ToString("yyyy-MM-dd");
                    txtDateTo.Text = DateTime.Now.ToString("yyyy-MM-dd");

                    break;
                case "MonthTillDate":
                    lbtnSearch.Enabled = true;
                    txtDateFrom.Enabled = false;
                    txtDateTo.Enabled = false;
                    today = DateTime.Now;
                    DateTime startOfMonth = new DateTime(today.Year, today.Month, 1);

                    txtDateFrom.Text = startOfMonth.ToString("yyyy-MM-dd");
                    txtDateTo.Text = today.ToString("yyyy-MM-dd");
                    break;

                case "LastMonth":
                    lbtnSearch.Enabled = true;
                    txtDateFrom.Enabled = false;
                    txtDateTo.Enabled = false;
                    today = DateTime.Now;
                    DateTime startOfPreviousMonth = new DateTime(today.Year, today.Month, 1).AddMonths(-1);
                    DateTime endOfPreviousMonth = new DateTime(today.Year, today.Month, 1).AddDays(-1);

                    txtDateFrom.Text = startOfPreviousMonth.ToString("yyyy-MM-dd");
                    txtDateTo.Text = endOfPreviousMonth.ToString("yyyy-MM-dd");
                    break;

            }

        }
    }
}