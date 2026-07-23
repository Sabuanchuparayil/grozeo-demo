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
    public partial class SpotReturn: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvSpotReturn.PageIndex > 0)
                gvSpotReturn.PageIndex = gvSpotReturn.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvSpotReturn.PageIndex < gvSpotReturn.PageCount - 1)
                gvSpotReturn.PageIndex = gvSpotReturn.PageIndex + 1;
        }

        protected void gvSpotReturn_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvSpotReturn.PageIndex * gvSpotReturn.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvSpotReturn.Rows.Count - 1;
            ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSSpotReturn.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSSpotReturn_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }

        protected void SDSSpotReturn_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            ltrPageTotal.Text = e.AffectedRows.ToString();
            if(e.AffectedRows <= 0)
            {
                ltrPageCurStart.Text = ltrPageCurTotal.Text = "0";
            }
        }
    }

}


