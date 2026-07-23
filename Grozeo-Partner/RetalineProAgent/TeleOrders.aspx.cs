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
    public partial class TeleOrders: Base.BasePartnerPage
    {

        protected void Page_Load(object sender, EventArgs e)
        {
        }

        

        //protected void SDSItemDetails_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        //{
        //    string orderId = Request.QueryString["orderId"];
        //    e.Command.Parameters["orderId"].Value = orderId;
        //}

        protected void btnSearch_Click(object sender, EventArgs e)
        {
            string mobile = txtMobile.Text;
            string customerPhn = mobile;
            var sql1 = DataServiceMySql.GetDataTable($"SELECT cust_id, cust_mobile, defaultRole FROM retaline_customer WHERE cust_mobile = {mobile} AND defaultRole='user'", UserService.GetAPIConnectionString());
            int custId = 0;
            string defaultRole = null; 
            if (sql1 != null && sql1.Rows.Count > 0)
            {
                custId = (int)sql1.Rows[0]["cust_id"];
                defaultRole = sql1.Rows[0]["defaultRole"].ToString();
            }
            int customerId = custId;
            string dfaultRole = defaultRole;
            if (customerId > 0 && dfaultRole == "user")
            {
                impersonateIframe.Src = $"https://demo-sites.retaline.net/impersonate?customerPhone={txtMobile.Text}";
                string sql = $"SELECT cust_id,cust_mobile,cust_customer_name,defaultRole,cust_email,cust_walletbalance,cust_alt_phone,cust_alternate_email FROM retaline_customer  WHERE cust_id = {customerId} AND defaultRole = 'user'";

                var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString());

                if (tblItems != null && tblItems.Rows.Count > 0)
                {
                    var tr = tblItems.Rows[0];
                    ltrName.Text = tr["cust_customer_name"].ToString();
                    ltrMobile.Text = tr["cust_mobile"].ToString();
                    ltrEmail.Text = tr["cust_email"].ToString();
                    ltrAltPhone.Text = tr["cust_alt_phone"].ToString();
                    ltrAltEmail.Text = tr["cust_alternate_email"].ToString();
                    ltrWalletBallence.Text = tr["cust_walletbalance"].ToString();
                }
            }

            //string orderId = Request["order_auto_id"];

            //string orddetails = $"SELECT co.order_id,co.order_order_id, co.order_group_id,co.total,co.status_id, cod.admin_description, co.order_payment_status, " +
            //    $"CASE WHEN payment_mode = 1 THEN 'Pay On Delivery' " +
            //    $"WHEN payment_mode = 2 THEN 'Online Payment' " +
            //    $"WHEN payment_mode = 3 THEN 'Wallet' " +
            //    $"WHEN payment_mode = 4 THEN 'COD With Wallet' " +
            //    $"WHEN payment_mode = 5 THEN 'Online With Wallet' " +
            //    $"WHEN payment_mode = 6 THEN 'Online On Delivery' " +
            //    $"WHEN payment_mode = 7 THEN 'Cash On Delivery' END AS payment_mode, co.order_confirm_date,od.order_customer_name, " +
            //    $"co.subtotal, co.order_total_amount, co.order_delivery_charge, co.order_total_gst, co.order_discount_add_total, co.order_roundoff, " +
            //    $"order_contact_no, CONCAT(od.order_house_no, ' ', od.order_house_name, ' ', od.order_address, ' ', od.order_land_mark, ' ', od.order_city, ' ', od.order_state, ' ', od.order_country, ' ', od.order_post) AS address " +
            //    $"FROM retaline_customer_order co INNER JOIN retaline_customer_order_delivery_address od ON od.order_id = co.order_order_id " +
            //    $"INNER JOIN finascop_stock_transfer_order so ON so.fstr_id = co.order_id " +
            //    $"LEFT JOIN retaline_customer_order_status cod ON cod.status_id = co.status_id WHERE co.order_customer_id = '{customerId}'";
            //var tblItems2 = DataServiceMySql.GetDataTable(orddetails, UserService.GetAPIConnectionString());
            //if (tblItems2 != null && tblItems2.Rows.Count > 0)
            //{
            //    var tr = tblItems2.Rows[0];
            //    ltrOrderNo.Text = tr["order_order_id"].ToString();
            //    ltrOrderDate.Text = tr["order_confirm_date"].ToString();
            //    ltrPaymentMode.Text = tr["payment_mode"].ToString();
            //    ltrAmount.Text = tr["total"].ToString();
            //}


            //SDSOrders.ConnectionString = DataService.APIConnectionString(UserService.GetAPIConnectionString());
            //SDSDocuments.ConnectionString = DataService.APIConnectionString(UserService.GetAPIConnectionString());
            //SDSItemDetails.ConnectionString = DataService.APIConnectionString(UserService.GetAPIConnectionString());
            //string result = Core.Services.APIService.ImpersonateURL(customerPhn);

            
            // show result as status.
            //string status = result;
        }

        //protected void SDSOrders_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        //{
        //    string mobile = txtMobile.Text;
        //    var sql1 = DataServiceMySql.GetDataTable($"SELECT cust_id, cust_mobile, defaultRole FROM retaline_customer WHERE cust_mobile = '"+ mobile + "' AND defaultRole='user'", UserService.GetAPIConnectionString());
        //    int custId = 0;
        //    if (sql1 != null && sql1.Rows.Count > 0)
        //    {
        //        custId = (int)sql1.Rows[0]["cust_id"];
        //    }
        //    int customerId = custId;
        //    e.Command.Parameters["customerId"].Value = customerId;

        //}

        protected void Tab1_Click(object sender, EventArgs e)
        {
            Tab1.CssClass = "Clicked";
            Tab2.CssClass = "Initial";
            MainView.ActiveViewIndex = 0;
        }

        protected void Tab2_Click(object sender, EventArgs e)
        {
            Tab1.CssClass = "Initial";
            Tab2.CssClass = "Clicked";
            MainView.ActiveViewIndex = 1;
        }

        

    }
}