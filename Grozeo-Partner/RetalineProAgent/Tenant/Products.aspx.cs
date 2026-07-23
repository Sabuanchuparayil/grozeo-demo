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
    public partial class Products: Base.BasePartnerPage
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
            //if (this.CurrentUser.TenantType == 2 && System.Configuration.ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1")
            //{
            //    Response.Redirect("/Tenant/SponsoredProducts");
            //    return;
            //}
            //if (!IsPostBack && String.IsNullOrEmpty(hidFilterType.Value))
            //{
            //    FilterType = 0; hidFilterType.Value = "0";

            //}

            //lblResult.Text = "";
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            //if (gvProducts.HeaderRow != null)
            //    gvProducts.HeaderRow.TableSection = TableRowSection.TableHeader;
            //if (selBranches.Items.Count > 1)
            //{
            //    selBranches.Items.Insert(0, new ListItem("Select Branch", "-1"));
            //}

            //ltrBranchName.Visible = selBranches.Items.Count <= 2;

            //if (ltrBranchName.Visible && selBranches.Items.Count > 1)
            //    ltrBranchName.Text = selBranches.Items[1].Text;



        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvMainProducts.PageIndex > 0)
                gvMainProducts.PageIndex = gvMainProducts.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvMainProducts.PageIndex < gvMainProducts.PageCount - 1)
                gvMainProducts.PageIndex = gvMainProducts.PageIndex + 1;
        }

        protected void gvMainProducts_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvMainProducts.PageIndex * gvMainProducts.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvMainProducts.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSMainProducts.Select(DataSourceSelectArguments.Empty);
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
            gvMainProducts.PageIndex = 0;
            gvMainProducts.DataBind();
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

        protected void SDS_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            //if (selBranches.Items.Count < 1)
            //    selBranches.DataBind();

            //e.Command.Parameters["branchId"].Value = selBranches.Text;

        }

        protected void SDSBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;

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

        protected void SDSMainProducts_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            //ltrPageTotal.Text = e.AffectedRows.ToString();
        }
    }

}


