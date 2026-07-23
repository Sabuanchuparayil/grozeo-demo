using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class ReceiveCash : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                int storegroupid = this.CurrentUser.APIStoreId;

                // Fetch branches
                var dtBranches = DataServiceMySql.GetDataTable(
                    $"SELECT br_ID, br_name FROM finascop_branch WHERE br_storeGroup = {storegroupid}",
                    UserService.GetAPIConnectionString());

                if (dtBranches.Rows.Count > 0)
                {
                    DataRow dr = dtBranches.Rows[0];
                    string branchName = dr["br_name"].ToString();

                    // If store group has only 1 branch — show it
                    if (dtBranches.Rows.Count == 1)
                    {
                        branchname.Visible = true;
                        branchname.Value = branchName;
                    }
                    else
                    {
                        branchname.Visible = false;
                    }
                }

                // Set delivery date field max and default value
                string today = DateTime.Now.ToString("yyyy-MM-dd");
                txtDeliDate.Text = today;
                txtDeliDate.Attributes["max"] = today;
            }

        }

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

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvDeliverBoy.PageIndex > 0)
                gvDeliverBoy.PageIndex = gvDeliverBoy.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvDeliverBoy.PageIndex < gvDeliverBoy.PageCount - 1)
                gvDeliverBoy.PageIndex = gvDeliverBoy.PageIndex + 1;
        }

        protected void gvDeliveryBoy_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvDeliverBoy.PageIndex * gvDeliverBoy.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvDeliverBoy.Rows.Count - 1;
        }
        protected void SDSDeliveryBoy_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            if (selBranches.Items.Count < 1)
                selBranches.DataBind();

            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchId"].Value = brid;
            }
            else
            {
                e.Command.Parameters["branchId"].Value = selBranches.Text;
            }
            var selectedDriver = ddlDrivers.SelectedValue;
            e.Command.Parameters["DriverId"].Value = string.IsNullOrEmpty(selectedDriver) ? 0 : Convert.ToInt32(selectedDriver);
            hfDriverId.Value = selectedDriver;

        }
        protected void ODSStore_Selecting(object sender, ObjectDataSourceSelectingEventArgs e)
        {
            e.InputParameters["storegroupid"] = this.CurrentUser.APIStoreId;
        }

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvDeliverBoy.PageIndex = 0;
            gvDeliverBoy.DataBind();

        }

        protected void selBranches_DataBound(object sender, EventArgs e)
        {

            plcSelectBranchModel.Visible = selBranches.Items.Count > 1;

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

        protected void lbtnReset_Click(object sender, EventArgs e)
        {
            txtFindCustomer.Text = string.Empty;
        }
        protected void btnViewJobs_Click(object sender, EventArgs e)
        {

        }
                
        protected void ddlDrivers_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvDeliverBoy.DataBind();
        }

        protected void gvDeliverBoy_RowDataBound(object sender, GridViewRowEventArgs e)
        {
           
        }

        protected void btnDeliverOrders_Click(object sender, EventArgs e)
        {
            List<int> lstQrIds = new List<int>();

            DateTime collectionDate;
            try
            {
                collectionDate = Convert.ToDateTime(txtDeliDate.Text);
            }
            catch
            {
               return;
            }

            var storegroupid = this.CurrentUser.APIStoreId;
            bool hasJobs = false;

            foreach (GridViewRow gr in gvDeliverBoy.Rows)
            {
                CheckBox chk = (CheckBox)gr.FindControl("ckCCJobs");
                if (chk != null && chk.Checked)
                {
                    int index = gr.RowIndex;
                    object orderIdObj = gvDeliverBoy.DataKeys[index].Value;

                    if (orderIdObj != null)
                    {
                        int orderId = Convert.ToInt32(orderIdObj);
                        if (orderId > 0)
                        {
                            lstQrIds.Add(orderId);
                            hasJobs = true;
                        }
                    }
                }
            }

            if (!hasJobs)
            {
               return;
            }

            string result = Core.Services.APIService.DeliverCODJobs(storegroupid, lstQrIds.ToArray(), collectionDate);

            string message = result;
            if (result == "Delivered")
            {
                Common.ShowCustomAlert(this.Page, "Completed Successfully!", "Your job(s) are completed successfully", true, "/Tenant/ReceiveCash");
            }
            else
            {
                if (hasJobs == false)
                {
                    Common.ShowToastifyMessage(this.Page, "Please select job(s) and continue.", "danger");
                }
                else
                {
                    Common.ShowCustomAlert(this.Page, "Failure", "Execution failure . Please contact support for more details.", false, "/Tenant/ReceiveCash");
                }

                return;
            }
        }
        private void calculateAmount()
        {
            double total = 0;
            string selectedDriverId = ""; 

            foreach (GridViewRow gr in gvDeliverBoy.Rows)
            {
                CheckBox chk = (CheckBox)gr.FindControl("ckCCJobs");
                Literal ltrAmount = (Literal)gr.FindControl("ltrAmount");
                Literal ltrDriverId = (Literal)gr.FindControl("ltrDriverId");

                if (chk != null && ltrAmount != null && chk.Checked && !string.IsNullOrEmpty(ltrAmount.Text))
                {
                    double amt = Convert.ToDouble(ltrAmount.Text);
                    total += amt;

                    if (ltrDriverId != null)
                    {
                        selectedDriverId = ltrDriverId.Text;
                    }
                }
            }

            txtCashInHand.Text = total.ToString();
            lblTotalAmount.Text = total.ToString();

            string strDriverName = "";
            if (!string.IsNullOrEmpty(selectedDriverId))
            {
                string sql = $"SELECT d_Name FROM qugeo_driver WHERE d_ID = '{selectedDriverId}'";
                var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString());
                if (tblItems != null && tblItems.Rows.Count > 0)
                {
                    var ta = tblItems.Rows[0];
                    strDriverName = ta["d_Name"].ToString();
                }
            }

            lblDriverName.Text = strDriverName;
        }
        protected void ckCCJobs_CheckedChanged(object sender, EventArgs e)
        {
            calculateAmount();

        }

    }
}