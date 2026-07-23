using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Threading.Tasks;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls.StoreSettings
{
    public partial class ctrlOrderPackingDetails: Base.BasePartnerUserControl
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            string strOrderId = Request.QueryString["id"];
            Service.User usr = this.CurrentUser;

            if (!String.IsNullOrEmpty(strOrderId))
            {
                string sql = $"SELECT fsto_uid,(SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_source) AS fsto_sourceName,CASE WHEN fsto_ordertype=0 THEN 'CPD TO BR' WHEN fsto_ordertype=1 THEN 'B2C' WHEN fsto_ordertype=2 THEN 'B2B' WHEN fsto_ordertype=3 THEN 'BR TO CPD' END AS fsto_ordertype," +
                    $"CASE WHEN fsto_ordertype = 0 THEN (SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) WHEN fsto_ordertype = 1 THEN(SELECT cust_customer_name FROM retaline_customer WHERE cust_id = fsto_destination) WHEN fsto_ordertype = 2 THEN(SELECT b2b_Customer_Name FROM retaline_B2Bcustomer WHERE b2b_Customer_ID = fsto_destination) WHEN fsto_ordertype = 3 THEN(SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) END AS fsto_destinationName,DATE_FORMAT(fsto_createdOn,'%d-%m-%Y') AS fstoCreatedOn,(SELECT fstos_status FROM finascop_stock_transfer_order_status WHERE fstos_id = fsto_status) AS fsto_statusName FROM finascop_stock_transfer_order WHERE fsto_uid = '{strOrderId}'";



                var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString());

                if(tblItems != null && tblItems.Rows.Count > 0)
                {
                    var tr = tblItems.Rows[0];
                    ltrOrderId.Text = tr["fsto_uid"].ToString();
                    ltrStore.Text = tr["fsto_sourceName"].ToString();
                    ltrCustomer.Text = tr["fsto_destinationName"].ToString();
                    ltrCreateDate.Text = tr["fstoCreatedOn"].ToString();
                    ltrType.Text = tr["fsto_ordertype"].ToString();
                    //ltrOrdNumber.Text = tr["paOrderNumber"].ToString();
                    //ltrDate.Text = tr[""].ToString();
                    //ltrScheduleTime.Text = tr[""].ToString();
                    //ltrPackType.Text = tr[""].ToString();
                    ltrStatus.Text = tr["fsto_statusName"].ToString();
                }
            }
        }
    }
}