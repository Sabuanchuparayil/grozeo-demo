using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Navigations;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant.Finance
{
    public partial class deliveryReport: Base.BasePartnerPage
    {
        //List<Store> _myBranches = null;
        //List<Store> MyBranches
        //{
        //    get
        //    {

        //        if (_myBranches == null)
        //        {
        //            _myBranches = Core.Services.APIService.GetStores(this.CurrentUser.APIStoreId, false);
        //        }
        //        return _myBranches;
        //    }
        //    set { _myBranches = value; }
        //}
        protected void Page_Load(object sender, EventArgs e)
        {
            int storegroupid = this.CurrentUser.APIStoreId;
            var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID,br_name FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            if (dtBranches == null)
                return;
            DataRow dr = dtBranches.Rows[0];
            string branchName = dr["br_name"].ToString();

            var btStoreGrp = DataServiceMySql.GetDataTable($"SELECT COUNT(br_storeGroup) AS cnt FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            DataRow dc = btStoreGrp.Rows[0];
            string storeGroup = dc["cnt"].ToString();
            if (Convert.ToInt32(storeGroup) == 1)
            {
                branchname.Visible = true;
                branchname.Value = dr["br_name"].ToString();
            }
            else
            {
                branchname.Visible = false;
            }
            //if (!IsPostBack && String.IsNullOrEmpty(hidFilterType.Value))
            //{
            //    FilterType = 0; hidFilterType.Value = "0";

            //}

            //lblResult.Text = "";

        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            

        }
        
        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvdelivery.PageIndex = 0;
            gvdelivery.DataBind();
            //ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");
        }

        protected void selBranches_DataBound(object sender, EventArgs e)
        {

            //if (selBranches.Items.Count < 1)
            //{
            //    selBranches.DataBind();
            //}
            plcSelectBranchModel.Visible = selBranches.Items.Count > 1;
            //ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");

        }
        protected void SDSBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
           

        }
        protected void SDSdelivery_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            e.Command.Parameters["branchId"].Value = selBranches.Text;
        }
        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvdelivery.PageIndex > 0)
                gvdelivery.PageIndex = gvdelivery.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvdelivery.PageIndex < gvdelivery.PageCount - 1)
                gvdelivery.PageIndex = gvdelivery.PageIndex + 1;
        }


        protected void gvdelivery_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvdelivery.PageIndex * gvdelivery.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvdelivery.Rows.Count - 1;

            var dv = (DataView)SDSdelivery.Select(DataSourceSelectArguments.Empty);
        }
    }
}