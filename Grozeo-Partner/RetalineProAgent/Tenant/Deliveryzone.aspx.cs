using System;
using System.Collections;
using System.Collections.Generic;
using System.Data;
using System.Data.SqlClient;
using System.Linq;
using System.Security.Cryptography.X509Certificates;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Windows.Controls;
using Amazon.DynamoDBv2;
using MySql.Data.MySqlClient;
using NPOI.SS.Formula.Functions;
using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using static Pipelines.Sockets.Unofficial.SocketConnection;

namespace RetalineProAgent.Tenant
{
    public partial class Deliveryzone : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
        }

        protected void Page_PreRender(object sender, EventArgs e)
        {
            selDistrictCity.Enabled = rvDistrict.Visible = rbtnDistrict.Checked;
            selStateProvince.Enabled = rvState.Visible = rbtnState.Checked || rbtnDistrict.Checked;
        }

        /// <summary>
        /// Create new zone
        /// </summary>
        /// <param name="sender"></param>
        /// <param name="e"></param>
        protected void btnAdd_Click(object sender, EventArgs e)
        {
            string Name = "";

            if (rbtnCountry.Checked)
                Name = selCountry.SelectedItem.Text + "-" + "Country";
            else if (rbtnState.Checked)
                Name = selStateProvince.SelectedItem.Text + "-" + "State";
            else if (rbtnDistrict.Checked)
                Name = selDistrictCity.SelectedItem.Text + "-" + "District";

            int countryId = -1; try { if(!String.IsNullOrEmpty(selCountry.Text)) countryId= Convert.ToInt32(selCountry.Text); } catch { countryId = -1; }
            int stateId = -1; try { if((rbtnState.Checked || rbtnDistrict.Checked) && !String.IsNullOrEmpty(selStateProvince.Text)) stateId = Convert.ToInt32(selStateProvince.Text); } catch { stateId = -1; }
            int districtId = -1; try { if(rbtnDistrict.Checked && !String.IsNullOrEmpty(selDistrictCity.Text)) districtId = Convert.ToInt32(selDistrictCity.SelectedValue); } catch { districtId = -1; }
            int storegroupId = this.CurrentUser.APIStoreId;
            int branchId = 0;
            if (!String.IsNullOrEmpty(selBranches.Text))
                try { branchId = Convert.ToInt32(selBranches.Text); } catch { branchId = 0; }

            string query = $"INSERT INTO delivery_zone (name, countryId, stateId, districtId, branchId, storegroupId) VALUES (@Name, @countryId, @stateId, @districtId, @branchId, @storegroupId)";
            List<KeyValuePair<string, object>> parameters = new List<KeyValuePair<string, object>>
               {
                 new KeyValuePair<string, object>("@Name", Name),
                 new KeyValuePair<string, object>("@countryId", countryId),
                 new KeyValuePair<string, object>("@stateId", stateId),
                 new KeyValuePair<string, object>("@districtId", districtId),
                 new KeyValuePair<string, object>("@branchId", branchId),
                 new KeyValuePair<string, object>("@storegroupId", storegroupId)
               };

            // Check if duplicate
            DataTable dtZone = DataServiceMySql.GetDataTable("select * from delivery_zone where countryId=@countryId and stateId = @stateId and districtId = @districtId and branchId=@branchId and storegroupId = @storegroupId", UserService.GetAPIConnectionString(), parameters);
            if(dtZone != null && dtZone.Rows.Count > 0)
            {
                Common.ShowCustomAlert(this.Page, "Duplicate data", "The zone is existing already. Cannot create duplicate zone", false);
                return;
            }

            int result = DataServiceMySql.ExecuteSql(query, UserService.GetAPIConnectionString(), parameters);
            gvDeliveryZone.DataBind();

            Common.ShowToastifyMessage(this.Page, "Zone created successfully!!");
            //Common.ShowCustomAlert(this.Page, "Success", "Zone created successfully", true, Request.RawUrl);

        }

        protected void SDSDeliveryZone_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("storegroupid"))
                e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;

        }

        protected void selCurState_DataBound(object sender, EventArgs e)
        {
            DropDownList dlState = (DropDownList)sender;
            dlState.Items.Insert(0, new ListItem("All State/Province", "-1"));
            if (dlState.NamingContainer is GridViewRow)
            {
                GridViewRow gvr = (GridViewRow)dlState.NamingContainer;
                if (gvr != null && gvr.DataItem != null)
                {
                    string selectedStateId = DataBinder.Eval(gvr.DataItem, "stateId").ToString();
                    if (dlState.Items.FindByValue(selectedStateId) != null)
                        dlState.Text = selectedStateId;
                }
            }
        }

        protected void selCurDistrict_DataBound(object sender, EventArgs e)
        {
            DropDownList dlDst = (DropDownList)sender;
            dlDst.Items.Insert(0, new ListItem("All District/City", "-1"));
            if (dlDst.NamingContainer is GridViewRow)
            {
                GridViewRow gvr = (GridViewRow)dlDst.NamingContainer;
                if (gvr != null && gvr.DataItem != null)
                {
                    string selectedDstId = DataBinder.Eval(gvr.DataItem, "districtId").ToString();
                    if (dlDst.Items.FindByValue(selectedDstId) != null)
                        dlDst.Text = selectedDstId;
                }
            }
        }

        protected void SDSDeliveryZone_Updating(object sender, SqlDataSourceCommandEventArgs e)
        {
            GridViewRow editRow = gvDeliveryZone.Rows[gvDeliveryZone.EditIndex];
            DropDownList dlCountry = (DropDownList)editRow.FindControl("selCurCountry");
            DropDownList dlState = (DropDownList)editRow.FindControl("selCurState");
            DropDownList dlDistrict = (DropDownList)editRow.FindControl("selCurDistrict");

            if(dlCountry != null && dlState != null && dlDistrict != null)
            {
                e.Command.Parameters["countryId"].Value = dlCountry.Text;
                e.Command.Parameters["stateId"].Value = dlState.Text;
                e.Command.Parameters["districtId"].Value = dlDistrict.Text;
            }

        }

        protected void SDSBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
            if (Page.User.IsInRole("BranchManager") && e.Command.Parameters.Contains("branchid"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchid"].Value = brid;
            }
        }

        protected void selBranches_DataBound(object sender, EventArgs e)
        {
            plcSelectBranchModel.Visible = selBranches.Items.Count > 1;
            branchname.Visible = !plcSelectBranchModel.Visible;

            if(selBranches.Items.Count > 1 && selBranches.Items.FindByValue("") == null)
                selBranches.Items.Insert(0, new ListItem("Select Branch", ""));

            if (branchname.Visible)
                try { branchname.Value = selBranches.Items[1].Text; } catch { }

        }

        protected void selCountry_DataBound(object sender, EventArgs e)
        {
            DropDownList dlCountry = (DropDownList)sender;
            if (rbtnCountry.Checked)
            {
                if(dlCountry.Items.FindByValue("") == null)
                    dlCountry.Items.Insert(0, new ListItem("Select Country", ""));
            }
            else
            {
                if(dlCountry.Items.FindByValue("-1") == null)
                    dlCountry.Items.Insert(0, new ListItem("Select Country", "-1"));
            }

            if (dlCountry.NamingContainer is GridViewRow)
            {
                GridViewRow gvr = (GridViewRow)dlCountry.NamingContainer;
                if (gvr != null && gvr.DataItem != null)
                {
                    string selectedCountryId = DataBinder.Eval(gvr.DataItem, "countryId").ToString();
                    if (dlCountry.Items.FindByValue(selectedCountryId) != null)
                        dlCountry.Text = selectedCountryId;
                }
            }

        }

        protected void selDistrictCity_DataBound(object sender, EventArgs e)
        {

            DropDownList dlDst = (DropDownList)sender;
            if (rbtnDistrict.Checked)
            {
                if (dlDst.Items.FindByValue("-1") != null)
                    dlDst.Items.Remove(dlDst.Items.FindByValue("-1"));

                if(dlDst.Items.FindByValue("") == null)
                    dlDst.Items.Insert(0, new ListItem("Select District/City", ""));
            }
            else
            {
                if (dlDst.Items.FindByValue("") != null)
                    dlDst.Items.Remove(dlDst.Items.FindByValue(""));

                if (dlDst.Items.FindByValue("-1") == null)
                    dlDst.Items.Insert(0, new ListItem("All District/City", "-1"));
            }
        }

        protected void selStateProvince_DataBound(object sender, EventArgs e)
        {
            DropDownList dlState = (DropDownList)sender;
            if (rbtnState.Checked || rbtnDistrict.Checked)
            {
                if (dlState.Items.FindByValue("-1") != null)
                    dlState.Items.Remove(dlState.Items.FindByValue("-1"));

                if (dlState.Items.FindByValue("") == null)
                    dlState.Items.Insert(0, new ListItem("Select State/Province", ""));
            }
            else
            {
                if (dlState.Items.FindByValue("") != null)
                    dlState.Items.Remove(dlState.Items.FindByValue(""));
                if (dlState.Items.FindByValue("-1") == null)
                    dlState.Items.Insert(0, new ListItem("All State/Province", "-1"));
            }
        }
    }
}

 
