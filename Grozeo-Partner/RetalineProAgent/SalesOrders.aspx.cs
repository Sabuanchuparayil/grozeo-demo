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
    public partial class SalesOrders: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }
        protected void SDSSalesOrders_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            int startRowOnPage = (gvSalesOrders.PageIndex * gvSalesOrders.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvSalesOrders.Rows.Count - 1;
            int totalRows = e.AffectedRows;

            ltrPageCurStart.Text = startRowOnPage.ToString();
            ltrPageCurTotal.Text = lastRowOnPage.ToString();
            ltrPageTotal.Text = totalRows.ToString();
        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvSalesOrders.PageIndex > 0)
                gvSalesOrders.PageIndex = gvSalesOrders.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvSalesOrders.PageIndex < gvSalesOrders.PageCount - 1)
                gvSalesOrders.PageIndex = gvSalesOrders.PageIndex + 1;
        }

        protected void gvSalesOrders_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvSalesOrders.PageIndex * gvSalesOrders.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvSalesOrders.Rows.Count - 1;
            ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSSalesOrders.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSSalesOrders_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }
    }
}