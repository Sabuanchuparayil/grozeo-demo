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
    public partial class Feedback: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvFeedback.PageIndex > 0)
                gvFeedback.PageIndex = gvFeedback.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvFeedback.PageIndex < gvFeedback.PageCount - 1)
                gvFeedback.PageIndex = gvFeedback.PageIndex + 1;
        }

        protected void gvFeedback_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvFeedback.PageIndex * gvFeedback.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvFeedback.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSFeedback.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSFeedback_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }
    }

}


