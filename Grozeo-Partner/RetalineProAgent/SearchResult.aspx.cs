using NPOI.SS.Formula.Functions;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.Drivers;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class SearchResult: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                if (Request.RawUrl.EndsWith("?upgrade"))
                    Service.UserService.CachedDefaultUser = null;

                //int storeid = this.CurrentUser.StoreGroupId;
                Service.User usr = this.CurrentUser;


                SDSRecentOrders.ConnectionString = DataService.APIConnectionString(Service.UserService.GetAPIConnectionString());
                //var dt = DataService.GetDataTable($"SELECT * FROM AppTenant WHERE Id={usr.StoreGroupId}");
                //if (dt != null && dt.Rows.Count > 0)
                //{
                //    string strStoregroupid = dt.Rows[0]["StoreId"].ToString();
                //    if (!String.IsNullOrEmpty(strStoregroupid))
                //    {

                //    }
                //}

                string strDashboardVals = @"SELECT COUNT(*) FROM retaline_godown_boy WHERE is_offline <> 1 AND branch_id IN (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storeGroup) UNION ALL 
SELECT COUNT(*) FROM retaline_customer_order o INNER JOIN finascop_stock_transfer_order so ON so.fstr_id = o.order_id WHERE o.status_id IN(4,5,6,7,8,9,10,11,12,13,14,15,16, 20, 22, 23, 27,28, 30, 31, 32, 33, 34) AND o.storegroup_id = @storeGroup UNION ALL 
SELECT COUNT(DISTINCT stit_id) FROM finascop_stock_branch_inventory WHERE branch_id IN(SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storeGroup) UNION ALL 
SELECT COUNT(*) FROM qugeo_driver WHERE br_id IN (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storeGroup) UNION ALL 
SELECT COUNT(*) FROM retaline_godown_boy WHERE branch_id IN (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storeGroup)";

                //  WHERE cust_created_at >= CURDATE()
                List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
                input.Add(new KeyValuePair<string, object>("storeGroup", this.CurrentUser.APIStoreId));
                //input.Add(new KeyValuePair<string, object>("storegroup_id", this.CurrentUser.StoreGroupId));
                var tblItems = DataServiceMySql.GetDataTable(strDashboardVals, Service.UserService.GetAPIConnectionString(), input);
                int onlineVehicles = 0, drivers = 0, orderPickersOnline = 0, orderPickers = 0;
                try
                {
                    var vehicleService = new VehicleService();
                    var liveVehiclesResponse = vehicleService.ListLiveVehicles(0, this.CurrentUser.APIStoreId);
                    if (liveVehiclesResponse?.Vehicles != null)
                        onlineVehicles = liveVehiclesResponse.Vehicles.Count;
                }
                catch { onlineVehicles = 0; }

                if (tblItems != null)
                {
                    if (tblItems.Rows.Count > 0)
                        try { orderPickersOnline = Convert.ToInt32(tblItems.Rows[0][0]); } catch { orderPickersOnline = 0; }
                    //ltrOrderPickers.InnerText = tblItems.Rows[0][0].ToString();
                    if (tblItems.Rows.Count > 1)                    
                    if (tblItems.Rows.Count > 3)
                        try { drivers = Convert.ToInt32(tblItems.Rows[3][0]); } catch { drivers = 0; }
                    if (tblItems.Rows.Count > 4)
                        try { orderPickers = Convert.ToInt32(tblItems.Rows[4][0]); } catch { orderPickers = 0; }
                    //ltrDrivers.InnerText = tblItems.Rows[3][0].ToString();
                }            
                string strSql = $"SELECT  DATE_FORMAT(`created_at`, '%Y-%M') AS `ordermonth`, COUNT(*) AS `totalsales`FROM `retaline_customer_order` WHERE storegroup_id = {this.CurrentUser.APIStoreId} GROUP BY MONTH(`created_at`) ORDER BY `created_at`";
                var tblSalesChart = DataServiceMySql.GetDataTable(strSql, Service.UserService.GetAPIConnectionString());
              
                if (tblSalesChart != null && tblSalesChart.Rows.Count > 0)
                {

                   
                    //ltrChartScript.Text = String.Format(ltrChartScript.Text, strLabels, strLabelsVals);
                    //tblSalesChart.AsEnumerable().Select(r => r.Field<string>("ordermonth")).ToArray();
                }

                //var stores = Core.Services.APIService.GetStores(usr.APIStoreId);
                //if (stores != null)
                //    ltrBranches.Text = stores.Count.ToString();
                //var dt = DataService.GetDataTable($"SELECT COUNT(*) FROM InventoryMapping WHERE StoreId={usr.StoreGroupId}");
                //if (dt != null)
                //    ltrForSale.Text = dt.Rows[0][0].ToString();
            }
        }

        protected void SDSRecentOrders_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

        //protected void btnConnect_Click(object sender, EventArgs e)
        //{
        //    if (!String.IsNullOrEmpty(txtSql.Text))
        //    {
        //        sdsConnect.SelectCommand = txtSql.Text;
        //        var dsource = sdsConnect.Select(DataSourceSelectArguments.Empty);
        //        gvTables.DataSource = dsource;
        //        gvTables.DataBind();
        //    }
        //}
    }
}