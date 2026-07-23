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
    public partial class ViewOrderDetails: Base.BasePartnerPage
    {
        private int i;

        protected void Page_Load(object sender, EventArgs e)
        {

        }
        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvViewOrdDetails.PageIndex > 0)
                gvViewOrdDetails.PageIndex = gvViewOrdDetails.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvViewOrdDetails.PageIndex < gvViewOrdDetails.PageCount - 1)
                gvViewOrdDetails.PageIndex = gvViewOrdDetails.PageIndex + 1;
        }

        protected void gvViewOrdDetails_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvViewOrdDetails.PageIndex * gvViewOrdDetails.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvViewOrdDetails.Rows.Count - 1;
            ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSViewOrdDetails.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSViewOrdDetails_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            string fsto_id = Convert.ToString(Request.QueryString["fsto_id"]);
            int status = Convert.ToInt32(fsto_id);
            e.Command.Parameters["fsto_id"].Value = status;
        }
        
    }

}


