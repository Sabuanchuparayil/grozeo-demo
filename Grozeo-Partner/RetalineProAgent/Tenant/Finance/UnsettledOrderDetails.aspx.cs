using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant.Finance
{
    public partial class UnsettledOrderDetails : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }
        protected void lvsettlement_DataBound(object sender, EventArgs e)
        {
            decimal total = 0;
            decimal totalcr = 0;

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
            if (ltrdrtotal != null && ltrcrtotal != null)
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
            string getorderdetails = "SELECT rc.order_order_id AS OrderId,fb.storeRefId AS StoreRefId FROM merchant_settlements_order so INNER JOIN merchant_settlements ms ON so.ms_ref_id=ms.ref_id INNER JOIN finance_transaction_log ft ON ft.ms_id=ms.id INNER JOIN finance_transaction t ON t.id=ft.ft_id INNER JOIN finascop_branch_group fb ON t.storegroup_id=fb.store_group_id INNER JOIN `retaline_customer_order` rc ON rc.order_id=so.order_id  WHERE t.status_id=3 AND t.storegroup_id=@Storegroup_id";
            DataTable dt = DataServiceMySql.GetDataTable(getorderdetails, parmeters: sql);
            e.Command.Parameters["@orders"].Value = dt;
        }

    }
}