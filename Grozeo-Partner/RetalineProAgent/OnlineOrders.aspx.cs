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
    public partial class OnlineOrders: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }
        //protected void SDSOnlineOrders_Selected(object sender, SqlDataSourceStatusEventArgs e)
        //{
        //    e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        //    int startRowOnPage = (gvOnlineOrders.PageIndex * gvOnlineOrders.PageSize) + 1;
        //    int lastRowOnPage = startRowOnPage + gvOnlineOrders.Rows.Count - 1;
        //    int totalRows = e.AffectedRows;

        //    ltrPageCurStart.Text = startRowOnPage.ToString();
        //    ltrPageCurTotal.Text = lastRowOnPage.ToString();
        //    ltrPageTotal.Text = totalRows.ToString();
        //}

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvOnlineOrders.PageIndex > 0)
                gvOnlineOrders.PageIndex = gvOnlineOrders.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvOnlineOrders.PageIndex < gvOnlineOrders.PageCount - 1)
                gvOnlineOrders.PageIndex = gvOnlineOrders.PageIndex + 1;
        }

        protected void gvOnlineOrders_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvOnlineOrders.PageIndex * gvOnlineOrders.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvOnlineOrders.Rows.Count - 1;
            ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSOnlineOrders.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSOnlineOrders_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }
    }
}