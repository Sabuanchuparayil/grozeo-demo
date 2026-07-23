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
    public partial class DeliveryResourceReport: Base.BasePartnerPage
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
            if (gvDeliveryResourceReport.PageIndex > 0)
                gvDeliveryResourceReport.PageIndex = gvDeliveryResourceReport.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvDeliveryResourceReport.PageIndex < gvDeliveryResourceReport.PageCount - 1)
                gvDeliveryResourceReport.PageIndex = gvDeliveryResourceReport.PageIndex + 1;
        }

        protected void gvDeliveryResourceReport_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvDeliveryResourceReport.PageIndex * gvDeliveryResourceReport.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvDeliveryResourceReport.Rows.Count - 1;
            ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSDeliveryResourceReport.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSDeliveryResourceReport_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }
    }
}