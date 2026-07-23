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
    public partial class Advertisement: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvAdvertisement.PageIndex > 0)
                gvAdvertisement.PageIndex = gvAdvertisement.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvAdvertisement.PageIndex < gvAdvertisement.PageCount - 1)
                gvAdvertisement.PageIndex = gvAdvertisement.PageIndex + 1;
        }

        protected void gvAdvertisement_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvAdvertisement.PageIndex * gvAdvertisement.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvAdvertisement.Rows.Count - 1;
            ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSAdvertisement.Select(DataSourceSelectArguments.Empty);
        }

        //protected void SDSDeliveryBoy_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        //{
        //    int storegroupid = this.CurrentUser.APIStoreId;
        //    var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
        //    foreach (DataRow dr in dtBranches.Rows)
        //    {
        //        string brId = dr["br_ID"].ToString();
        //        e.Command.Parameters["branchid"].Value = brId;
        //    }
        //}
    }

}


