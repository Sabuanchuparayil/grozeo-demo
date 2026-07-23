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
    public partial class DeliveryJobs: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvDeliveryJobs.PageIndex > 0)
                gvDeliveryJobs.PageIndex = gvDeliveryJobs.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvDeliveryJobs.PageIndex < gvDeliveryJobs.PageCount - 1)
                gvDeliveryJobs.PageIndex = gvDeliveryJobs.PageIndex + 1;
        }

        protected void gvDeliveryJobs_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvDeliveryJobs.PageIndex * gvDeliveryJobs.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvDeliveryJobs.Rows.Count - 1;
            ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSDeliveryJobs.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSDeliveryJobs_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }
    }

}


