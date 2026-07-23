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

namespace RetalineProAgent.Finance
{
    public partial class _Default : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            string count = "select (select count(*) from data_entry) as vouchers,(select count(*) from finascop_log) as transactionLog,(select  count(*) from [ledger]) as ledgers,(select count(*) from groups) as groups";
            var dtcount = DataService.GetDataTable(count, ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString);
            if (dtcount != null && dtcount.Rows.Count > 0)
            {
                var getcount = dtcount.Rows[0];
                ltmVoucher.InnerHtml= getcount["vouchers"].ToString();
                lttransctionlog.InnerHtml= getcount["transactionLog"].ToString();
                ltmtrialbalance.InnerHtml= getcount["groups"].ToString();
                ltmledger.InnerHtml= getcount["ledgers"].ToString();
            }

        }

        protected void SDSRecentOrders_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            //e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }

    }
}