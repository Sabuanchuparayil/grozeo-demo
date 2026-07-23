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
    public partial class LatestOrders: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvLatestOrders.PageIndex > 0)
                gvLatestOrders.PageIndex = gvLatestOrders.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvLatestOrders.PageIndex < gvLatestOrders.PageCount - 1)
                gvLatestOrders.PageIndex = gvLatestOrders.PageIndex + 1;
        }

        protected void gvLatestOrders_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvLatestOrders.PageIndex * gvLatestOrders.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvLatestOrders.Rows.Count - 1;
            ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSLatestOrders.Select(DataSourceSelectArguments.Empty);
        }


        protected void SDSLatestOrders_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }
    }
}