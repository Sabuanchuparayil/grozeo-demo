using RetalineProAgent.Core.BussinessModel.Store;
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
    public partial class MessageCenter : Base.BasePartnerPage
    {
        List<Store> _myBranches = null;
        List<Store> MyBranches
        {
            get
            {

                if (_myBranches == null)
                {
                    _myBranches = Core.Services.APIService.GetStores(this.CurrentUser.APIStoreId, false);
                }
                return _myBranches;
            }
            set { _myBranches = value; }
        }
        public int FilterType
        {
            get
            {
                if (ViewState["ORDFILTERTYPE"] == null)
                    return 0;
                else
                    return (int)ViewState["ORDFILTERTYPE"];
            }
            set
            {
                ViewState["ORDFILTERTYPE"] = value;
            }
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            int storegroupid = this.CurrentUser.APIStoreId;
            var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID,br_name FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
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

        }

        protected void selBranches_DataBound(object sender, EventArgs e)
        {
            //pnlSelectBranchModel.Visible = selBranches.Items.Count > 1;
            plcSelectBranchModel.Visible = selBranches.Items.Count > 1;
            ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");
            //pnlDummyContainer.Visible = !pnlSelectBranchModel.Visible;
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {

        }

        protected void SDSBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchid"].Value = brid;
            }

        }

        protected void btnFilterType_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            if (btn != null && !String.IsNullOrEmpty(btn.Attributes["typeid"]))
            {
                int btypeid = Convert.ToInt32(btn.Attributes["typeid"]);
                FilterType = btypeid;
                //hidFilterType.Value = btypeid.ToString();
            }
        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {

        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {

        }

        protected void gvBusinessFAQ_DataBound(object sender, EventArgs e)
        {

        }

        protected void OBJ_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            if (e.InputParameters.Contains("storeId"))
                e.InputParameters["storeId"] = this.CurrentUser.APIStoreId;//.StoreGroupId;
        }

        protected void SDSCustCommunication_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            //hidFilterType.Value = FilterType.ToString();
            e.Command.Parameters["filterType"].Value = FilterType;
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        }
        

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");
        }

        //protected void gvMessageCenter_DataBound(object sender, EventArgs e)
        //{

        //}

        //protected void gvMessageCenter_RowDataBound(object sender, GridViewRowEventArgs e)
        //{

        //}
    }

}


