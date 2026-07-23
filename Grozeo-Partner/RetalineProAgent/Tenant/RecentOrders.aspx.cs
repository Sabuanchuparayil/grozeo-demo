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

namespace RetalineProAgent
{
    public partial class RecentOrders: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            string strOrderId = Request.QueryString["ordid"];
            Service.User usr = this.CurrentUser;

            if (!String.IsNullOrEmpty(strOrderId))
            {
                string sql = $"SELECT o.order_order_id, b.br_Name, d.order_city, o.total FROM retaline_customer_order o " +
                    $"INNER JOIN finascop_branch b ON o.order_branch_id = b.br_ID INNER JOIN retaline_customer_order_delivery_address d " +
                    $"ON o.order_order_id = d.order_id  WHERE order_order_id = '{strOrderId}'";



                var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString());

                if (tblItems != null && tblItems.Rows.Count > 0)
                {
                    var tr = tblItems.Rows[0];
                    ltrOrdID.Text = tr["order_order_id"].ToString();
                    ltrFROM.Text = tr["br_Name"].ToString();
                    ltrTO.Text = tr["order_city"].ToString();
                    ltrAMOUNT.Text = tr["total"].ToString();

                }
            }
        }
    }
}