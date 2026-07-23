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
    public partial class DeliveryAddress: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            string customerId = Request.QueryString["cust_id"];
            Service.User usr = this.CurrentUser;

            if (!String.IsNullOrEmpty(customerId))
            {
                string sql = $"SELECT  deli_name,deli_delivery_pin,deli_house_no,deli_house_name,deli_land_mark,deli_is_primary,deli_type,deli_city," +
                    $"deli_district,deli_state,deli_latitude,deli_longitude,br_Name,deli_contact_no,DATE_FORMAT(deli_created_at, '%d-%m-%Y %H:%i:%s') AS deli_created_at " +
                    $"FROM retaline_customer_delivery_info LEFT JOIN finascop_branch ON br_ID = deli_branch_id INNER JOIN retaline_customer rc " +
                    $"ON rc.cust_id = deli_customer_id WHERE rc.cust_id = '{customerId}'";

                var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString());

                if (tblItems != null && tblItems.Rows.Count > 0)
                {
                    var tr = tblItems.Rows[0];
                    ltrMobile.Text = tr["deli_contact_no"].ToString();
                    ltrAddrType.Text = tr["deli_type"].ToString();
                    ltrPin.Text = tr["deli_delivery_pin"].ToString();
                    ltrHseName.Text = tr["deli_house_name"].ToString();
                    ltrLandMrk.Text = tr["deli_land_mark"].ToString();
                    ltrDist.Text = tr["deli_district"].ToString();
                    ltrState.Text = tr["deli_state"].ToString();
                    ltrLat.Text = tr["deli_latitude"].ToString();
                    ltrLong.Text = tr["deli_longitude"].ToString();
                    ltrAssBranch.Text = tr["br_Name"].ToString();
                    ltrCrDate.Text = tr["deli_created_at"].ToString();
                }
            }

        }

        protected void SDSDeliveryAddress_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            string customerId = Request.QueryString["cust_id"];
            e.Command.Parameters["cust_id"].Value = customerId;
            }


    }
}