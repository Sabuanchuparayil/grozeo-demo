using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.Drivers;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Threading.Tasks;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class DeliveryStaffs : Base.BasePartnerPage
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
            int storegroupid = this.CurrentUser.APIStoreId;
            var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID,br_name FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            if(dtBranches != null && dtBranches.Rows.Count > 0)
            {
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
            else
            {
                Common.ShowCustomAlert(this.Page, "Failure!", "No branch available", false, "/Tenant/DeliveryStaffs");
            }
            
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
            var dv = (DataView)SDSDeliveryBoy.Select(DataSourceSelectArguments.Empty);

            int storeGroupId = 0;
            int branchId = 0;
            if (selBranches.SelectedValue != null)
                branchId = int.Parse(selBranches.SelectedValue);
            var vehicleService = new VehicleService();

            VehicleResponse liveVehicles = vehicleService.ListLiveVehicles(branchId, storeGroupId);

            var liveDriverNames = liveVehicles?.Vehicles?
                .Where(v => !string.IsNullOrWhiteSpace(v.DriverName))
                .Select(v => v.DriverName.Trim().ToLower())
                .ToHashSet()
                ?? new HashSet<string>();

            foreach (GridViewRow row in gvDeliverBoy.Rows)
            {
                if (row.RowType == DataControlRowType.DataRow)
                {
                    string driverName = row.Cells[0].Text.Trim().ToLower();
                    Label lblLiveStatus = row.FindControl("lblLiveStatus") as Label;

                    if (lblLiveStatus != null)
                    {
                        bool isOnline = liveDriverNames.Contains(driverName);
                        lblLiveStatus.Text = isOnline ? "Online" : "Offline";
                        lblLiveStatus.ForeColor = isOnline ? System.Drawing.Color.Green : System.Drawing.Color.Red;
                    }
                }
            }
        }

        
        protected void SDSDeliveryBoy_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            if (Page.User.IsInRole("BranchManager") && e.Command.Parameters.Contains("branchId"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchId"].Value = brid;
            }

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
            gvDeliverBoy.PageIndex = 0;
            gvDeliverBoy.DataBind();
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


