using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
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
    public partial class Leads: Base.BasePartnerPage
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
        protected void Page_Load(object sender, EventArgs e)
        {
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {

        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvLeads.PageIndex > 0)
                gvLeads.PageIndex = gvLeads.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvLeads.PageIndex < gvLeads.PageCount - 1)
                gvLeads.PageIndex = gvLeads.PageIndex + 1;
        }

        protected void gvLead_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvLeads.PageIndex * gvLeads.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvLeads.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSLead.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSLead_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            //if (selBranches.Items.Count < 1)
            //    selBranches.DataBind();

            //e.Command.Parameters["branchId"].Value = selBranches.Text;
        }

        protected void ODSStore_Selected(object sender, ObjectDataSourceStatusEventArgs e)
        {
            //MyBranches = (List<Store>)e.ReturnValue;
            //if (MyBranches != null)
            //{
            //    ltrBranchName.Visible = MyBranches.Count == 1;
            //    ltrBranchName.Text = MyBranches[0].BranchName;
            //    plcSelectBranchModel.Visible = MyBranches.Count > 1;

            //}
        }

        protected void ODSStore_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            e.InputParameters["storegroupid"] = this.CurrentUser.APIStoreId;
        }

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");
        }

        protected void selBranches_DataBound(object sender, EventArgs e)
        {

            //if (selBranches.Items.Count < 1)
            //{
            //    selBranches.DataBind();
            //}
            plcSelectBranchModel.Visible = selBranches.Items.Count > 1;
            ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");

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

        private int FilterType
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
    }

}


