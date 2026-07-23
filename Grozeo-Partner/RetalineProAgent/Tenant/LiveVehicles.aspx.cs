using RetalineProAgent.Service;
using RetalineProAgent.Core.BussinessModel.LiveVehicles;
using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Text;

namespace RetalineProAgent
{
    public partial class LiveVehicles: Base.BasePartnerPage
    {
        
        protected void Page_Load(object sender, EventArgs e)
        {
            //string brid = Convert.ToString(Request.QueryString["quor_Deliverybr_id"]);
            //int branchid = Convert.ToInt32(brid);
            //string lat = Convert.ToString(Request.QueryString["quor_PickupLat"]);
            //double pickupLat = Convert.ToDouble(lat);
            //string Lng = Convert.ToString(Request.QueryString["quor_PickupLng"]);
            //double pickupLng = Convert.ToDouble(Lng);
            //string result = Core.Services.APIService.AssignDeliveryBoy(branchid, pickupLat, pickupLng);
            //int status = Convert.ToInt32(result);

        }

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvLiveVehicles.PageIndex > 0)
                gvLiveVehicles.PageIndex = gvLiveVehicles.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvLiveVehicles.PageIndex < gvLiveVehicles.PageCount - 1)
                gvLiveVehicles.PageIndex = gvLiveVehicles.PageIndex + 1;
        }

        //protected void gvDeliveryJobs_DataBound(object sender, EventArgs e)
        //{
        //    int startRowOnPage = (gvLiveVehicles.PageIndex * gvLiveVehicles.PageSize) + 1;
        //    int lastRowOnPage = startRowOnPage + gvLiveVehicles.Rows.Count - 1;
        //    ltrPageCurTotal.Text = lastRowOnPage.ToString();

        //    var dv = (DataView)SDSLiveVehicles.Select(DataSourceSelectArguments.Empty);
        //}

        protected void SDSLiveVehicles_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            int storegroupid = this.CurrentUser.APIStoreId;
            var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            foreach (DataRow dr in dtBranches.Rows)
            {
                string brId = dr["br_ID"].ToString();
                e.Command.Parameters["branchid"].Value = brId;
            }
        }

        protected void btnDeliveryBoyAssign_Click(object sender, EventArgs e)
        {
            Button btnAssign = (Button)sender;
            string strQuorId = Request.QueryString["quorId"];
            int quorId = Convert.ToInt32(strQuorId);

            int qugeobkNO = quorId;
            var quorIdList = new string[] {strQuorId }; 

            string brId = Request.QueryString["brId"];
            string handling_br_id = Request.QueryString["brId"];
            string drivetype = Request.QueryString["status"]; 


            int branchId = Convert.ToInt32(brId);
            int handlingBranchId = Convert.ToInt32(handling_br_id);
            string type = drivetype;
            string hdnVehicleId = Convert.ToString(btnAssign.Attributes["vehicleId"]);
            string result = Core.Services.APIService.AssignDeliveryBoy(qugeobkNO, branchId, handlingBranchId, type, hdnVehicleId, quorIdList);

            // show result as status.
            string message = result;
            
            if (message == "The driver has a live poll, please try after two minutes.")
            {
                Common.ShowToastifyMessage(this.Page, "The driver has a live poll, please try after two minutes.", "danger");
            }
            else
            {
                ShowSuccess("Assigned Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your items assigned to driver successfully!</a></h5>");
            }

            
        }

        private void ShowSuccess(string title, string content)
        {
            ltrErrorPopupTitle.Text = title;
            ltrErrorPopupText.Text = content;
            Type cstype = this.GetType();
            String csname1 = "PopupScript";
            ClientScriptManager cs = Page.ClientScript;
            ltrSuccessTitle.Text = title;
            ltrSuccessContent.Text = content;

            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> $('#modaldemo4').modal('show'); </");
            cstext1.Append("script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }
    }

}


