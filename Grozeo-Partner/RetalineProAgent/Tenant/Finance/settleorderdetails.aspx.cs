using Newtonsoft.Json;
using Org.BouncyCastle.Crypto.Tls;
using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Data.SqlTypes;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant.Finance
{
    public partial class settleorderdetails : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            
        }       
        protected void lvsettlement_DataBound(object sender, EventArgs e)
        {
            decimal total = 0;
            decimal totalcr=0;
                       
            foreach (ListViewItem item in lvsettlement.Items)
            {
                Label lblField2 = (Label)item.FindControl("lbdelivery");
                Label lblField = (Label)item.FindControl("lbSettlementDate");
                decimal fieldValue;
                decimal fieldValue1;
                if (decimal.TryParse(lblField2.Text, out fieldValue))  
                {
                    total += fieldValue;
                    
                }
                if (decimal.TryParse(lblField.Text, out fieldValue1))
                {
                    totalcr += fieldValue1;
                }
            }

            Literal ltrdrtotal = (Literal)lvsettlement.FindControl("ltrdeduction");
            Literal ltrcrtotal = (Literal)lvsettlement.FindControl("ltrsettleamount");
            if (ltrdrtotal != null && ltrcrtotal!= null)
            {
                ltrdrtotal.Text = total.ToString();
                ltrcrtotal.Text = totalcr.ToString();
            }



        }

        protected void SDSSettlementReport_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            //e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId.ToString();
        }

        protected void SDSsettlement_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            List<KeyValuePair<string, object>> sql = new List<KeyValuePair<string, object>>();
            sql.Add(new KeyValuePair<string, object>("Storegroup_id", this.CurrentUser.APIStoreId.ToString()));
            string getorderdetails = "SELECT rc.order_order_id AS OrderId,fb.storeRefId AS StoreRefId FROM  retaline_customer_order rc INNER JOIN finascop_branch_group fb ON rc.storegroup_id=fb.store_group_id WHERE rc.order_id NOT IN (SELECT order_id FROM merchant_settlements_order)AND status_id>9 AND rc.storegroup_id=@Storegroup_id";
            DataTable dt= DataServiceMySql.GetDataTable(getorderdetails, parmeters: sql);                 
            e.Command.Parameters["@orders"].Value = dt;
        }
    }
}