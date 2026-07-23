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
    public partial class CancelledOrders: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvCancelledOrders.PageIndex > 0)
                gvCancelledOrders.PageIndex = gvCancelledOrders.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvCancelledOrders.PageIndex < gvCancelledOrders.PageCount - 1)
                gvCancelledOrders.PageIndex = gvCancelledOrders.PageIndex + 1;
        }

        protected void gvCancelledOrders_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvCancelledOrders.PageIndex * gvCancelledOrders.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvCancelledOrders.Rows.Count - 1;
            ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSCancelledOrder.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSCancelledOrder_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }
    }

}


