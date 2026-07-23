using RetalineProAgent.Core.BussinessModel.OrderPacking;
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
    public partial class OrderPacking: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }
        //protected void SDSOrderPacking_Selected(object sender, SqlDataSourceStatusEventArgs e)
        //{
        //    int startRowOnPage = (gvOrderPacking.PageIndex * gvOrderPacking.PageSize) + 1;
        //    int lastRowOnPage = startRowOnPage + gvOrderPacking.Rows.Count - 1;
        //    int totalRows = e.AffectedRows;

        //    ltrPageCurStart.Text = startRowOnPage.ToString();
        //    ltrPageCurTotal.Text = lastRowOnPage.ToString();
        //    ltrPageTotal.Text = totalRows.ToString();
        //}

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvOrderPacking.PageIndex > 0)
                gvOrderPacking.PageIndex = gvOrderPacking.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvOrderPacking.PageIndex < gvOrderPacking.PageCount - 1)
                gvOrderPacking.PageIndex = gvOrderPacking.PageIndex + 1;
        }

        protected void gvOrderPacking_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvOrderPacking.PageIndex * gvOrderPacking.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvOrderPacking.Rows.Count - 1;
            ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSOrderPacking.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSOrderPacking_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }

    }

}


