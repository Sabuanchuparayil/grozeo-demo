using RetalineProAgent.Core.BussinessModel.Finance;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant
{
    public partial class Salesreturns : Base.BasePartnerPage
    {
        public enum ReturnStatus
        {
            Unknown=0,
            ReturnCompleted = 1,     
            ReturnPending = 2,      
            ReturnResolved = 3,      
            DisputeInTransit = 4,    
            ReturnRejected = 5      
        }
        public enum DefectReason
        {
            Unknown = 0,
            ManufacturingDefect = 1,
            DamagedInTransit = 2,
            PackingDefect = 3,
            FaultedByCustomer = 4
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            int storegroupid = this.CurrentUser.APIStoreId;
            var dtBranches = DataServiceMySql.GetDataTable($@"SELECT br_ID, br_name,(SELECT COUNT(*) FROM finascop_branch WHERE br_storeGroup = {storegroupid}) AS storeGroupCount FROM finascop_branch WHERE br_storeGroup = {storegroupid}  LIMIT 1", UserService.GetAPIConnectionString());
            if (dtBranches?.Rows.Count > 0)
            {
                DataRow dr = dtBranches.Rows[0];
                hdnbranchid.Value = dr["br_ID"].ToString();
                branchname.Visible = Convert.ToInt32(dr["storeGroupCount"]) == 1;
                branchname.Value = branchname.Visible ? dr["br_name"].ToString() : string.Empty;
            }

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


        protected void SDSPendingOrders_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;            
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchid"].Value = brid;
            }
        }
        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvPendingOrders.PageIndex = 0;
            gvPendingOrders.DataBind();
            int branchId = Convert.ToInt32(selBranch.SelectedValue);
            Session["SelectedBranchId"] = branchId;
        }

        protected void selBranches_DataBound(object sender, EventArgs e)
        {           
            if (selBranch.Items.Count > 1)
            {
                plcSelectBranchModel.Visible = selBranch.Items.Count > 2;
            }
        }

        protected void btnviewitems_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            string orderId = btn.Attributes["data-orderid"];
            hdordermethod.Value=btn.Attributes["data-ordermethod"];
            hdnorderid.Value = orderId;
            string strAlertSCript = "$('#modalViewreturnorder').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }
        public ReturnStatus GetReturnStatus(DefectReason defectReason, string resolution)
        {
            if ((defectReason == DefectReason.ManufacturingDefect || defectReason == DefectReason.PackingDefect) &&
                (resolution == "Replaced" || resolution == "Refunded"))
                return ReturnStatus.ReturnCompleted;

            if (defectReason == DefectReason.ManufacturingDefect)
            {
                if (resolution == "Sent to Warranter") return ReturnStatus.ReturnPending;
                if (resolution == "Advised Customer for Warranty") return ReturnStatus.ReturnResolved;
            }

            if (defectReason == DefectReason.DamagedInTransit &&
                (resolution == "Replaced" || resolution == "Refunded" || resolution == "Dispute on Delivery"))
                return ReturnStatus.DisputeInTransit;

            if (defectReason == DefectReason.FaultedByCustomer && resolution == "Return Request Rejected")
                return ReturnStatus.ReturnRejected;
            if (defectReason == DefectReason.Unknown && (resolution == "Replaced" || resolution == "Refunded"))
            return ReturnStatus.ReturnCompleted;

            return ReturnStatus.ReturnResolved;
        }

        protected void btnsubmiresolution_Click(object sender, EventArgs e)
        {
            try
            {
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                Guid newGuid = Guid.NewGuid();
                prms.Add(new KeyValuePair<string, object>("Guid", newGuid));
                prms.Add(new KeyValuePair<string, object>("orderid", hdnorderid.Value));
                prms.Add(new KeyValuePair<string, object>("branchid", hdnbranchid.Value));
                prms.Add(new KeyValuePair<string, object>("sourceid", 3));
                prms.Add(new KeyValuePair<string, object>("type", 1));
                prms.Add(new KeyValuePair<string, object>("createddate", DateTime.Now.ToString("yyyy-MM-dd")));
                prms.Add(new KeyValuePair<string, object>("createdOn", DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss")));
                prms.Add(new KeyValuePair<string, object>("rtrqo_createdBy", this.CurrentUser.APIStoreId));
                prms.Add(new KeyValuePair<string, object>("isdirect", hdordermethod.Value == "1" ? 1 : 0));
                prms.Add(new KeyValuePair<string, object>("status", "1"));
                var requestreturn = "insert into finascop_stock_return_request_order (rtrqo_uuid,rtrqo_date,rtrqo_createdOn,rtrqo_type,rtrqo_sourceType,order_id,rtrqo_sourceBranch,rtrqo_isDirect,rtrqo_isBanned,rtrqo_status) value(@Guid,@createddate,@createdOn,@type,@sourceid,@orderid,@branchid,@isdirect,0,@status);SELECT LAST_INSERT_ID();";
                var dtreturnrequest = DataServiceMySql.ExecuteScalar(requestreturn, Service.UserService.GetAPIConnectionString(), prms);
                var itemdetails = "SELECT item_order_qty, item_return_qty_sellable,item_product_id,item_return_qty_damaged, item_return_qty_damagedinTransit, item_return_qty_requested,item_price, item_retail_price FROM `retaline_customer_order_items`ro INNER JOIN `retaline_customer_order` rc ON  ro.customer_order_id = rc.order_id WHERE order_id = @orderid and item_return_qty_requested > 0";
                DataTable dtBranches = DataServiceMySql.GetDataTable(itemdetails, UserService.GetAPIConnectionString(), prms);
                if (dtBranches.Rows.Count > 0)
                {
                    prms.Clear();
                    foreach (DataRow dr in dtBranches.Rows)
                    {
                        prms.Add(new KeyValuePair<string, object>("item_id", dr["item_product_id"].ToString()));
                        prms.Add(new KeyValuePair<string, object>("rtrqod_return_count", dr["item_return_qty_requested"].ToString()));
                        prms.Add(new KeyValuePair<string, object>("returrequestid", dtreturnrequest));
                        prms.Add(new KeyValuePair<string, object>("isReceivedOnStore", rbReceivedYes.Checked ? 1 : 0));
                        prms.Add(new KeyValuePair<string, object>("remark", rbReceivedNo.Checked == true ? txtRemark.Text : txtaditinalconditions.Text));
                        prms.Add(new KeyValuePair<string, object>("receviedon", rbReceivedNo.Checked == true ? (object)DBNull.Value : txtReceivedItemOn.Text));
                        prms.Add(new KeyValuePair<string, object>("broughtby", rbReceivedNo.Checked == true ? "0" : ddlItemBroughtBy.SelectedValue));
                        prms.Add(new KeyValuePair<string, object>("broughtname", rbReceivedNo.Checked == true ? "" : txtstaffname.Text));
                        prms.Add(new KeyValuePair<string, object>("sellablecondition", rbReceivedNo.Checked == true ? "0" : ddlItemCondition.SelectedValue));
                        prms.Add(new KeyValuePair<string, object>("itemnosellablereason", rbReceivedNo.Checked == true ? "0" : ddlitennosellablereason.SelectedValue == "2" ? ddlitennosellablereason.SelectedValue : "0"));
                        prms.Add(new KeyValuePair<string, object>("resolution", rbReceivedNo.Checked == true ? ddlResolution.SelectedValue : ddlresolutions.SelectedValue));
                        if (ddlItemCondition.SelectedValue == "1")
                        {
                            prms.Add(new KeyValuePair<string, object>("branchid", hdnbranchid.Value));
                            string updatestock = "UPDATE finascop_stock_branch_inventory SET item_count = item_count + @rtrqod_return_count WHERE stit_id = @item_id and branch_id=@branchid";
                            DataServiceMySql.ExecuteSql(updatestock, UserService.GetAPIConnectionString(), prms);
                        }
                        string selectedValue = ddlitennosellablereason.SelectedValue?.Trim();
                        DefectReason defectReason = DefectReason.Unknown;
                        switch (selectedValue)
                        {
                            case "1":
                                defectReason = DefectReason.ManufacturingDefect;
                                break;
                            case "2":
                                defectReason = DefectReason.DamagedInTransit;
                                break;
                            case "3":
                                defectReason = DefectReason.PackingDefect;
                                break;
                            case "4":
                                defectReason = DefectReason.FaultedByCustomer;
                                break;                            
                        }
                        ReturnStatus status = GetReturnStatus(defectReason, rbReceivedYes.Checked? ddlresolutions.SelectedItem.Text : ddlResolution.SelectedItem.Text);
                        prms.Add(new KeyValuePair<string, object>("status", status));
                        string insertreturn = "insert into finascop_stock_return_request_order_details (rtrqo_id,rtrqod_item_id,rtrqod_return_count,rtrqod_return_sellable,rtrqo_type,isReceivedOnStore,remark,defectReason,resolution,receivedOn,returnSource,returnSourceName,returnStatus)" +
                            " values(@returrequestid,@item_id,@rtrqod_return_count,@sellablecondition,0,@isReceivedOnStore,@remark,@itemnosellablereason,@resolution,@receviedon,@broughtby,@broughtname,@status)";
                        int result = DataServiceMySql.ExecuteSql(insertreturn, UserService.GetAPIConnectionString(), prms);
                        string returnupdate = "update retaline_customer_order Set order_itemReturnRequestCount=@rtrqod_return_count where order_id=@orderid";
                        prms.Add(new KeyValuePair<string, object>("orderid", hdnorderid.Value));
                        DataServiceMySql.ExecuteSql(returnupdate, UserService.GetAPIConnectionString(), prms);
                        Common.ShowCustomAlert(this.Page, "Success", "Return  processed successfully.", true, "/Tenant/Salesreturns");
                    }
                    
                }
                
            }
            catch
            {
                Common.ShowCustomAlert(this.Page, "Error", "Technical error occured", false, "/Tenant/Salesreturns");

            }
        }

        protected void btnResolveManually_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            string orderId = btn.Attributes["data-orderid"];
            hdordermethod.Value = btn.Attributes["data-ordermethod"];
            hdnorderid.Value = orderId;                   
            string strAlertSCript = "$('#modalmanualreturnorder').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void rbReceivedYes_CheckedChanged(object sender, EventArgs e)
        {
            if (rbReceivedYes.Checked)
            {
                ToggleReceivedDetails(true);
            }
        }
        protected void rbReceivedNo_CheckedChanged(object sender, EventArgs e)
        {
            if (rbReceivedNo.Checked)
            {
                ToggleReceivedDetails(false);
            }
        }     
        private void ToggleReceivedDetails(bool isReceived)
        {
            plcnotrecived.Visible = !isReceived;
            plcreceiveddetails.Visible = isReceived;

            string strAlertSCript = "$('#modalmanualreturnorder').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }       

    }
}