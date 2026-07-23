using RetalineProAgent.Core.BussinessModel.Store;
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
    public partial class TestPagination : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            SDSSalesOrders.ConnectionString = Service.DataService.APIConnectionString(UserService.GetAPIConnectionString());
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
    }
}