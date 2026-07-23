using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Configuration;

namespace RetalineProAgent
{
    public partial class DeliveryRules: Base.BasePartnerPage
    {
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
            gvDeliveryRules.Columns[4].HeaderText = gvDeliveryRules.Columns[4].HeaderText.Replace("Free Above Rs", "Free Above " + ConfigurationManager.AppSettings.Get("CurrencySymbol"));

        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvDeliveryRules.PageIndex > 0)
                gvDeliveryRules.PageIndex = gvDeliveryRules.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvDeliveryRules.PageIndex < gvDeliveryRules.PageCount - 1)
                gvDeliveryRules.PageIndex = gvDeliveryRules.PageIndex + 1;
        }

        protected void gvDeliveryRules_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvDeliveryRules.PageIndex * gvDeliveryRules.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvDeliveryRules.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSDeliveryRules.Select(DataSourceSelectArguments.Empty);
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

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvDeliveryRules.PageIndex = 0;
            gvDeliveryRules.DataBind();
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

        protected void SDS_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
            if (Page.User.IsInRole("BranchManager") && e.Command.Parameters.Contains("branchId"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchId"].Value = brid;
            }
        }

        protected void gvDeliveryRules_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            if (e.Row.RowType != DataControlRowType.DataRow)
                return;
            try
            {
                int is_default = Convert.ToInt32(DataBinder.Eval(e.Row.DataItem, "is_default"));
                if (is_default == 1)
                    e.Row.BackColor = System.Drawing.Color.AliceBlue;
            }
            catch (Exception ex)
            {

            }
        }

        protected void lbManageRule_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(selBranches.Text))
            {
                Common.ShowCustomAlert(this.Page, "Error", "Invalid store selection", false);
                return;
            }

            Response.Redirect("/tenant/deliveryrulesnew?type=2&brid=" + selBranches.Text);
        }
    }

}


