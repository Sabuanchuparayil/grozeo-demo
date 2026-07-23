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
    public partial class ActiveDeliveryBoys: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvDeliverBoy.PageIndex > 0)
                gvDeliverBoy.PageIndex = gvDeliverBoy.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvDeliverBoy.PageIndex < gvDeliverBoy.PageCount - 1)
                gvDeliverBoy.PageIndex = gvDeliverBoy.PageIndex + 1;
        }

        protected void gvDeliveryBoy_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvDeliverBoy.PageIndex * gvDeliverBoy.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvDeliverBoy.Rows.Count - 1;
            ltrPageCurTotal.Text = lastRowOnPage.ToString();

            //var dv = (DataView)SDSDeliveryBoy.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSDeliveryBoy_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }

        protected void btnAssign_Click(object sender, EventArgs e)
        {

        }
    }

}


