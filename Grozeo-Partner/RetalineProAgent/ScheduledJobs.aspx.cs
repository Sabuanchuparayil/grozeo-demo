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
    public partial class ScheduledJobs: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvScheduledJobs.PageIndex > 0)
                gvScheduledJobs.PageIndex = gvScheduledJobs.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvScheduledJobs.PageIndex < gvScheduledJobs.PageCount - 1)
                gvScheduledJobs.PageIndex = gvScheduledJobs.PageIndex + 1;
        }

        protected void gvScheduledJobs_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvScheduledJobs.PageIndex * gvScheduledJobs.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvScheduledJobs.Rows.Count - 1;
            ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSScheduledJobs.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSScheduledJobs_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            int storegroupid = this.CurrentUser.APIStoreId;
            var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            foreach (DataRow dr in dtBranches.Rows)
            {
                string brId = dr["br_ID"].ToString();
                e.Command.Parameters["branchid"].Value = brId;
            }
        }
    }

}


