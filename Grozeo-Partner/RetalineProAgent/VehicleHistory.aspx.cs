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
    public partial class VehicleHistory: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvVehicleHistory.PageIndex > 0)
                gvVehicleHistory.PageIndex = gvVehicleHistory.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvVehicleHistory.PageIndex < gvVehicleHistory.PageCount - 1)
                gvVehicleHistory.PageIndex = gvVehicleHistory.PageIndex + 1;
        }

        //protected void gvDeliveryJobs_DataBound(object sender, EventArgs e)
        //{
        //    int startRowOnPage = (gvLiveVehicles.PageIndex * gvLiveVehicles.PageSize) + 1;
        //    int lastRowOnPage = startRowOnPage + gvLiveVehicles.Rows.Count - 1;
        //    ltrPageCurTotal.Text = lastRowOnPage.ToString();

        //    var dv = (DataView)SDSLiveVehicles.Select(DataSourceSelectArguments.Empty);
        //}

        //protected void SDSLiveVehicles_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
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


