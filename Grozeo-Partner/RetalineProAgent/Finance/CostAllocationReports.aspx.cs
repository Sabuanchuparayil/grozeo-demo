using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class CostAllocationReports : System.Web.UI.Page
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
            if (txtFromDate.Text != "" && txtToDate.Text != "")
            {
                int costcentreid = 0;
                if (!String.IsNullOrEmpty(selcostcentre.Text))
                    try { costcentreid = Convert.ToInt32(selcostcentre.Text); } catch { costcentreid = 0; }
                List<KeyValuePair<string, object>> sqldatavId = new List<KeyValuePair<string, object>>();
                sqldatavId.Add(new KeyValuePair<string, object>("costcentreid", costcentreid));
                sqldatavId.Add(new KeyValuePair<string, object>("fromDate", txtFromDate.Text));
                sqldatavId.Add(new KeyValuePair<string, object>("toDate", txtToDate.Text));

                string costcentre = $"SELECT (SUM(COALESCE(CASE WHEN isDebtor = 1  AND CAST([tr].[createdOn] as date)  < @fromDate THEN tr.amount END,0)) -" +
                    $" SUM(COALESCE(CASE WHEN isDebtor = 0  AND CAST([tr].[createdOn] as date)  <=  @fromDate THEN tr.amount END,0))) AS OpeningBal," +
                    $"(SUM(COALESCE(CASE WHEN isDebtor = 1  AND CAST([tr].[createdOn] as date)  <= @toDate  THEN tr.amount END,0)) -" +
                    $" SUM(COALESCE(CASE WHEN isDebtor = 0  AND CAST([tr].[createdOn] as date)  <=  @toDate THEN tr.amount END,0))) AS ClosingBal," +
                   $" (SUM(COALESCE(CASE WHEN isDebtor = 1  AND CAST([tr].[createdOn] as date) >=  @fromDate AND CAST([tr].[createdOn] as date)  <= @toDate THEN tr.amount END, 0))) as dr," +
                   $"(SUM(COALESCE(CASE WHEN isDebtor = 0  AND CAST([tr].[createdOn] as date) >=  @fromDate AND CAST([tr].[createdOn] as date)  <= @toDate THEN tr.amount END, 0))) as cr" +
                    $" FROM [cost_centre_entries] tr  WHERE @costcentreid = 0 or [cost_centre_id] =@costcentreid";

                var payment = DataService.GetDataTable(costcentre, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters: sqldatavId);
                if (payment != null && payment.Rows.Count > 0)
                {
                    var dataEntry = payment.Rows[0];
                    ltrtotaldebit.Text = String.Format("{0:0.00}", dataEntry["dr"]).ToString();
                    ltrtotalcredit.Text = String.Format("{0:0.00}", dataEntry["cr"]).ToString();
                    double OpeningBal = -1;
                    if (!(dataEntry["OpeningBal"] is DBNull))
                        try { OpeningBal = (double)dataEntry["OpeningBal"]; } catch { OpeningBal = -1; }

                    double ClosingBal = -1;
                    if (!(dataEntry["ClosingBal"] is DBNull))
                        try { ClosingBal = (double)dataEntry["ClosingBal"]; } catch { ClosingBal = -1; }

                    if (OpeningBal < 0)
                    {

                        ltrPageCurStart.Text = String.Format("{0:0.00}", Math.Abs(OpeningBal)).ToString() + "  Credit ";

                    }
                    else
                    {
                        ltrPageCurStart.Text = String.Format("{0:0.00}", dataEntry["OpeningBal"]).ToString() + " Debit";
                    }

                    if (ClosingBal < 0)
                    {

                        ltrPageCurTotal.Text = String.Format("{0:0.00}", Math.Abs(ClosingBal)).ToString() + "  Credit ";

                    }
                    else
                    {

                        ltrPageCurTotal.Text = String.Format("{0:0.00}", Math.Abs(ClosingBal)).ToString() + "  Debit ";

                    }
                }

            }
        }
    }
}