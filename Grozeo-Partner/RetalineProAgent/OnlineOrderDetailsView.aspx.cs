//using RetalineProAgent.Core.BussinessModel.OnlineOrders;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class OnlineOrderDetailsView: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            string strOrderId = Request.QueryString["id"];
            Service.User usr = this.CurrentUser;

            if (!String.IsNullOrEmpty(strOrderId))
            {
                string sql = $"SELECT bco.order_id ,order_order_id, cust_customer_name,order_total_amount,order_delivery_charge, order_total_gst," +
                    $"order_action,DATE_FORMAT(bcoh.created_at, '%d-%m-%Y %H:%i:%s') AS order_confirm_date," +
                    $"(SELECT admin_description FROM retaline_customer_order_status WHERE status_id = order_status) AS new_status," +
                    $"(SELECT admin_description FROM retaline_customer_order_status WHERE status_id = " +
                    $"(SELECT order_status FROM retaline_customer_order_history pl  WHERE pl.id < bcoh.id AND pl.order_id = bco.order_id " +
                    $"ORDER BY pl.id DESC LIMIT 1)) AS old_status FROM retaline_customer_order_history bcoh INNER JOIN retaline_customer_order bco " +
                    $"ON bco.order_id = bcoh.order_id INNER JOIN retaline_customer bc ON bc.cust_id = bco.order_customer_id WHERE order_order_id = '{strOrderId}'";



                var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString());

                if (tblItems != null && tblItems.Rows.Count > 0)
                {
                    var tr = tblItems.Rows[0];
                    ltrOrder.Text = tr["cust_customer_name"].ToString();
                    ltrDate.Text = tr["order_confirm_date"].ToString();
                    ltrCurrentStatus.Text = tr["new_status"].ToString();
                    ltrPreviousStatus.Text = tr["old_status"].ToString();
                }
            }
        }
    }
}