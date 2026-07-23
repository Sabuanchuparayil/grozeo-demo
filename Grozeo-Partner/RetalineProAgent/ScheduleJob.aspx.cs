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
    public partial class ScheduleJob: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvScheduleJob.PageIndex > 0)
                gvScheduleJob.PageIndex = gvScheduleJob.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvScheduleJob.PageIndex < gvScheduleJob.PageCount - 1)
                gvScheduleJob.PageIndex = gvScheduleJob.PageIndex + 1;
        }
        protected void SDSScheduleJob_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            ltrPageTotal.Text = e.AffectedRows.ToString();
        }

        protected void gvScheduleJob_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvScheduleJob.PageIndex * gvScheduleJob.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvScheduleJob.Rows.Count - 1;
            ltrPageCurTotal.Text = lastRowOnPage.ToString();
            ltrPageCurStart.Text = (gvScheduleJob.Rows.Count > 0 ? startRowOnPage : 0).ToString();
            var dv = (DataView)SDSScheduleJob.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSScheduleJob_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }

        protected void btnMove_Click(object sender, EventArgs e)
        {
            Button btnRevoke = (Button)sender;

            string transferorderId = Convert.ToString(btnRevoke.Attributes["transferOrderId"]);
            string statusID = Convert.ToString(btnRevoke.Attributes["statusId"]);
            string orderType = Convert.ToString(btnRevoke.Attributes["ordType"]);
            string requestId = Convert.ToString(btnRevoke.Attributes["reqId"]);

        }
        protected void gvScheduleJob_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            //  data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo"
            e.Row.Attributes.Add("data-toggle", "collapse");
            e.Row.Attributes.Add("data-target", String.Format("#collapse{0}", e.Row.RowIndex));
            e.Row.Attributes.Add("aria-expanded", "false");
            e.Row.Attributes.Add("aria-controls", String.Format("collapse{0}", e.Row.RowIndex));

        }
    }

}


