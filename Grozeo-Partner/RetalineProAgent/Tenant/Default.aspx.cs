using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Merchant
{
    public partial class Default: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            //            if (!IsPostBack)
            //            {
            //                if (Request.RawUrl.EndsWith("?upgrade"))
            //                    Service.UserService.CachedDefaultUser = null;

            //                Service.User usr = this.CurrentUser;

            //                SDSRecentOrders.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
            //                SDSProduct.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());

            //                string strDashboardVals = @"SELECT COUNT(*) FROM retaline_godown_boy WHERE is_offline <> 1 AND branch_id IN (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storeGroup) UNION ALL 
            //SELECT COUNT(*) FROM retaline_customer_order o INNER JOIN finascop_stock_transfer_order so ON so.fstr_id = o.order_id WHERE o.status_id IN(4,5,6,7,8,9,10,11,12,13,14,15,16, 20, 22, 23, 25, 27, 28, 30, 31, 32, 33, 34, 40) AND o.storegroup_id = @storeGroup UNION ALL 
            //SELECT COUNT(DISTINCT stit_id) FROM finascop_stock_branch_inventory WHERE branch_id IN(SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storeGroup) UNION ALL 
            //SELECT COUNT(*) FROM qugeo_driver WHERE br_id IN (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storeGroup) UNION ALL 
            //SELECT COUNT(*) FROM retaline_godown_boy WHERE branch_id IN (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storeGroup)";

            //                List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
            //                input.Add(new KeyValuePair<string, object>("storeGroup", this.CurrentUser.APIStoreId));
            //                //input.Add(new KeyValuePair<string, object>("storegroup_id", this.CurrentUser.StoreGroupId));
            //                var tblItems = DataServiceMySql.GetDataTable(strDashboardVals, Service.UserService.GetAPIConnectionString(), input);
            //                int onlineVehicles = 0, drivers = 0, orderPickersOnline = 0, orderPickers = 0;
            //                try { onlineVehicles = Core.Services.APIService.VehiclesOnline(this.CurrentUser.APIStoreId).Count(); } catch { onlineVehicles = 0; }

            //                if (tblItems != null)
            //                {
            //                    if (tblItems.Rows.Count > 0)
            //                        try { orderPickersOnline = Convert.ToInt32(tblItems.Rows[0][0]); } catch { orderPickersOnline = 0; }
            //                    if (tblItems.Rows.Count > 1)
            //                        ltrNewOrders.InnerText = tblItems.Rows[1][0].ToString();
            //                    if (tblItems.Rows.Count > 2)
            //                        ltrForSale.InnerText = tblItems.Rows[2][0].ToString();
            //                    if (tblItems.Rows.Count > 3)
            //                        try { drivers = Convert.ToInt32(tblItems.Rows[3][0]); } catch { drivers = 0; }
            //                    if (tblItems.Rows.Count > 4)
            //                        try { orderPickers = Convert.ToInt32(tblItems.Rows[4][0]); } catch { orderPickers = 0; }
            //                }

            //                ltrDrivers.InnerText = String.Format("{0} / {1}", onlineVehicles > drivers ? drivers : onlineVehicles, drivers);
            //                ltrOrderPickers.InnerText = String.Format("{0} / {1}", orderPickersOnline > orderPickers ? orderPickers : orderPickersOnline, orderPickers);

            //                string strSql = $"SELECT ordermonth, COUNT(*) AS `totalsales` FROM(SELECT  DATE_FORMAT(`created_at`, '%Y-%M') AS `ordermonth` FROM `retaline_customer_order` WHERE storegroup_id = {this.CurrentUser.APIStoreId} )tmp GROUP BY ordermonth ORDER BY `ordermonth`";
            //                var tblSalesChart = DataServiceMySql.GetDataTable(strSql, Service.UserService.GetAPIConnectionString());

            //                ltrChartScript.Text = "var salesChartData = {};";
            //                if (tblSalesChart != null && tblSalesChart.Rows.Count > 0)
            //                {

            //                    string strLabels = String.Join("','", tblSalesChart.AsEnumerable().Select(r => r.Field<string>("ordermonth")).ToArray());
            //                    if (!String.IsNullOrEmpty(strLabels))
            //                        strLabels = String.Format("'{0}'", strLabels);
            //                    string strLabelsVals = String.Join("','", tblSalesChart.AsEnumerable().Select(r => (r.Field<Int64>("totalsales")).ToString()).ToArray());
            //                    if (!String.IsNullOrEmpty(strLabelsVals))
            //                        strLabelsVals = String.Format("'{0}'", strLabelsVals);

            //                    ltrChartScript.Text = @"var salesChartData = {
            //        labels: [" + strLabels + @"],
            //        datasets: [
            //            {
            //                label: 'Monthly Sales',
            //                backgroundColor: '#27AAC8',
            //                data: [" + strLabelsVals + @"]
            //            }
            //        ]
            //    }";

            //                }

            //            }

            if (!IsPostBack)
            {
                // Check if the session variable is set to true
                bool showThankYouMessage = Session["ShowThankYouMessage"] as bool? ?? false;
                if (showThankYouMessage)
                {
                    // Display the "Thank you" message
                    plcThankyou.Visible = true;
                }

                // Once the message is displayed, clear the session variable
                Session["ShowThankYouMessage"] = false;
            }
        }

        protected void SDSRecentOrders_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

    }
}