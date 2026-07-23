using Amazon.DynamoDBv2.Model;
using Amazon.DynamoDBv2;
using RestSharp;
using RetalineProAgent.Core.BussinessModel.Order;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Operation
{
    public partial class DeliveryDelay : Base.BasePartnerPage
    {
        private int Filter_by_StoreId
        {
            get
            {
                if (ViewState["STOREFILTER"] == null)
                    return 0;
                return (int)ViewState["STOREFILTER"];
            }
            set
            {
                ViewState["STOREFILTER"] = value;
            }
        }


        string VId = "";
        protected void btnFilterType_Click(object sender, EventArgs e)
        {
            LinkButton clickedButton = sender as LinkButton;
            int deliveryMode = int.Parse(clickedButton.CommandArgument);
            hfFilterType.Value = deliveryMode.ToString();

            // Save the active button ID in ViewState
            ViewState["ActiveButtonID"] = clickedButton.ID;

            BindGrid();
            //LinkButton clickedButton = sender as LinkButton;
            //int deliveryMode = int.Parse(clickedButton.CommandArgument);
            //hfFilterType.Value = deliveryMode.ToString();
            //BindGrid();
        }

        private void SetActiveButton()
        {
            string activeId = ViewState["ActiveButtonID"]?.ToString();

            var buttons = new List<LinkButton>
            {
               lbtnPendingJobs,
               lbtnAPIBookingFailed,
               lbtnHyperlocalPending,
               lbtnLocalExpressPending,
               lbtnCourierPickupDelayed,
               lbtnParcelBooking,
               lbtnCargoBooking
            };

            foreach (var btn in buttons)
            {
                btn.CssClass = "btn btn-block btn-outline-primary" + (btn.ID == activeId ? " active" : "");
            }
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                hfFilterType.Value = "0";
                ViewState["ActiveButtonID"] = lbtnPendingJobs.ID;
            }
            //int branchid = -1;
            //ODSFailedOrders.SelectParameters["branchid"].DefaultValue = branchid.ToString();
        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            BindGrid();
            SetActiveButton();
            //BindGrid();
        }
        private void BindGrid()
        {
            List<PendingOrder> data = (List<PendingOrder>)ODSFailedOrders.Select();

            int FilterType = 0;
            if (!string.IsNullOrEmpty(hfFilterType.Value))
            {
                FilterType = int.Parse(hfFilterType.Value);
            }

            if (!IsPostBack)
            {
                selBranches.DataSource = data.Select(o => new { StoreId = o.BranchID, StoreName = o.MerchantName }).Distinct();
                selBranches.DataBind();
            }

            if (Filter_by_StoreId > 0)
            {
                data = data.Where(o => o.BranchID == Filter_by_StoreId.ToString()).ToList();
            }

            if (!String.IsNullOrEmpty(txtOrderId.Text))
            {
                data = data.Where(o => o.OrderOrderID == txtOrderId.Text).ToList();
            }

            Dictionary<int, Func<List<PendingOrder>, List<PendingOrder>>> filters = new Dictionary<int, Func<List<PendingOrder>, List<PendingOrder>>>
            {
               { 6, (d) => d.Where(o => int.Parse(o.DeliveryMode) == 2 && int.Parse(o.Mode)==2).ToList() },
               { 2, (d) => d.Where(o => int.Parse(o.DeliveryMode) == 2 && int.Parse(o.Mode)!=2).ToList() },
               { 1, (d) => d.Where(o => int.Parse(o.DeliveryMode) == 1).ToList() },
               { 3, (d) => d.Where(o => int.Parse(o.DeliveryMode) == 3).ToList() },
               { 4, (d) => d.Where(o => int.Parse(o.DeliveryMode) == 4).ToList() },
               { 5, (d) => d.Where(o => int.Parse(o.DeliveryMode) == 5).ToList() }
            };

            if (filters.ContainsKey(FilterType))
            {
                data = filters[FilterType](data);
            }

            gvFailedOrders.DataSource = data;
            gvFailedOrders.DataBind();

        }
        protected void gvFailedOrders_PageIndexChanging(object sender, GridViewPageEventArgs e)
        {
            gvFailedOrders.PageIndex = e.NewPageIndex;
            BindGrid();
        }
        protected void SDSBranches_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            int baId;
            int Id = Convert.ToInt32(this.CurrentUser.AreaId);
            var dtArea = DataServiceMySql.GetDataTable($"SELECT areaBusinessAssociate from area_entries where id={Id}", UserService.GetAPIConnectionString());
            if (dtArea != null && dtArea.Rows.Count > 0)
            {
                DataRow dr = dtArea.Rows[0];
                baId = Convert.ToInt32(dr["areaBusinessAssociate"]);
                e.Command.Parameters["baId"].Value = baId;
            }
        }
        protected void gvFailedOrders_RowDataBound(object sender, GridViewRowEventArgs e)
        {

        }

        protected void gvFailedOrders_DataBound(object sender, EventArgs e)
        {

        }
        protected void SDSFailedOrders_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {

        }
        protected void btnCancelBooking_Click(object sender, EventArgs e)
        {

        }
        protected void btnAssignRider_Click(object sender, EventArgs e)
        {
            string orderId = "";
            string pickLat = "";
            string pickLng = "";
            int usertype = 2;// (createdBy column)
            int userId = 0;

            LinkButton btn = (LinkButton)sender;
            orderId = (btn.CommandArgument).ToString();

            GridViewRow row = (GridViewRow)btn.NamingContainer;

            LinkButton uuidLinkButton = (LinkButton)row.FindControl("uuidLinkButton");
            string UUID = uuidLinkButton.CommandArgument;

            LinkButton tstampLinkButton = (LinkButton)row.FindControl("tstampLinkButton");
            string tstamp = tstampLinkButton.CommandArgument;

            hiddenOrderId.Value = orderId;
            hiddenUUID.Value = UUID;
            hiddentstamp.Value = tstamp;

            var dtArea = DataServiceMySql.GetDataTable($"SELECT areaName FROM retaline_customer_order co INNER JOIN finascop_branch fb ON fb.br_ID=co.order_branch_id INNER JOIN area_entries ae ON fb.areaId= ae.id WHERE order_order_id={orderId}", UserService.GetAPIConnectionString());
            if (dtArea != null && dtArea.Rows.Count > 0)
            {
                DataRow dr = dtArea.Rows[0];
                string AName = dr["areaName"].ToString();
                lblArea.Text = AName;
                var dtUserId = DataServiceMySql.GetDataTable($"SELECT areaBusinessAssociate FROM area_entries WHERE id={CurrentUser.AreaId}", UserService.GetAPIConnectionString());
                if (dtUserId != null && dtUserId.Rows.Count > 0)
                {
                    DataRow drUser = dtUserId.Rows[0];
                    userId = Convert.ToInt32(drUser["areaBusinessAssociate"]);
                }
            }

            var dtLatLong = DataServiceMySql.GetDataTable($"SELECT quor_PickupLat,quor_PickupLng FROM qugeo_order WHERE quor_RefNo={orderId}", UserService.GetAPIConnectionString());
            if (dtLatLong != null && dtLatLong.Rows.Count > 0)
            {
                DataRow dr = dtLatLong.Rows[0];
                pickLat = dr["quor_PickupLat"].ToString();
                pickLng = dr["quor_PickupLng"].ToString();
            }

            //ODSLiveVehicles.SelectParameters["branchid"].DefaultValue = "";
            ODSLiveVehicles.SelectParameters["pickupLat"].DefaultValue = pickLat;
            ODSLiveVehicles.SelectParameters["pickupLng"].DefaultValue = pickLng;
            ODSLiveVehicles.SelectParameters["UserType"].DefaultValue = usertype.ToString();
            ODSLiveVehicles.SelectParameters["UserId"].DefaultValue = userId.ToString();

            var result = Core.Services.APIService.LoadVehicle(0, Convert.ToDouble(pickLat), Convert.ToDouble(pickLng), usertype, userId);

            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalAssignRider').modal('toggle');</script>");

        }

        protected async void btnAssignAgent_Click(object sender, EventArgs e)
        {
            int branchId = 0;
            int QuorId = 0;
            string orderId = hiddenOrderId.Value;
            int handlingBranchId = 0;
            string drivetype = "";
            string uuid = hiddenUUID.Value;
            string tstamp = hiddentstamp.Value;

            if (ddlLiveDrivers.SelectedItem == null || ddlLiveDrivers.SelectedItem.Text == "Select drivers")
            {
                Common.ShowToastifyMessage(this.Page, "No driver was assigned.", "warning");
                return;
            }

            var dtRider = DataServiceMySql.GetDataTable($"SELECT quor_id,quor_Pickupbr_id,IF(quor_Status = 22, 'PICKUP', NULL) AS pickStatus FROM qugeo_order WHERE quor_RefNo={orderId}", UserService.GetAPIConnectionString());
            if (dtRider != null && dtRider.Rows.Count > 0)
            {
                DataRow dr = dtRider.Rows[0];
                QuorId = Convert.ToInt32(dr["quor_id"]);
                branchId = Convert.ToInt32(dr["quor_Pickupbr_id"]);
                handlingBranchId = Convert.ToInt32(branchId);
                drivetype = dr["pickStatus"].ToString();

            }

            var quorIdArray = new string[] { QuorId.ToString() };

            string result = APIService.AssignDeliveryBoy(QuorId, branchId, handlingBranchId, drivetype, VId, quorIdArray);

            if (string.IsNullOrEmpty(result))
            {
                Common.ShowToastifyMessage(this.Page, "Error in handling the order", "danger");
            }
            else if (result == "The driver has a live poll, please try after two minutes.")
            {
                Common.ShowToastifyMessage(this.Page, "The driver has a live poll, please try after two minutes.", "danger");
            }
            else
            {
                try
                {
                    await UpdateDynamoDbOrder(uuid, tstamp, 1);
                    ShowSuccess("Assigned Successfully!", "<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your items have been assigned to the driver successfully!</a></h5>");
                    ddlLiveDrivers.SelectedIndex = -1;
                }
                catch (Exception ex)
                {
                    Common.ShowToastifyMessage(this.Page, "Error updating order: " + ex.Message, "danger");
                }
            }
        }


        public static async Task UpdateDynamoDbOrder(string UID, string Tstamp, int action)
        {
            try
            {
                // Assuming you get the table prefix and table name from configuration
                string tableprefix = ConfigurationManager.AppSettings.Get("AWS_Prefix");
                string table = "delayed_orders";  // Assuming "orders" is your table name
                string tableName = String.Concat(tableprefix, table);

                // Define the key to locate the order (use orderId as the partition key)
                var key = new Dictionary<string, AttributeValue>
                {
                  { "uuid", new AttributeValue { S = UID } },
                  { "tstamp", new AttributeValue { S = Tstamp } }
                };

                // Define the attribute updates dictionary for skipDate and action
                var attributeUpdates = new Dictionary<string, AttributeValueUpdate>
            {
                { "skipDate", new AttributeValueUpdate
                    {
                        Action = AttributeAction.PUT,
                        Value = new AttributeValue { S = DateTime.Now.AddHours(24).ToString("yyyy-MM-dd HH:mm:ss") } // Set skipDate to today's date, or pass your desired value
                    }
                },
                { "action", new AttributeValueUpdate
                    {
                        Action = AttributeAction.PUT,
                        Value = new AttributeValue { N = action.ToString() } // Set action to the passed integer value
                    }
                }
            };

                // Call the DynamoDB update method to apply the updates
                await DynamoService.UpdateToDynamoDb(tableName, key, attributeUpdates);
            }
            catch (Exception ex)
            {
                // Log or rethrow the exception as needed
                throw new Exception("Error updating order in DynamoDB", ex);
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

        protected async void btnDispatch_Click(object sender, EventArgs e)
        {
            int OrderId = Convert.ToInt32(hdnOrderId.Value);
            int QuorId = 0;
            hdnstatusId.Value = 9.ToString();
            string uuid = hduuid.Value;
            string tstamp = hdtstamp.Value;

            var dtDispatch = DataServiceMySql.GetDataTable($"SELECT quor_id FROM finascop_stock_transfer_order INNER JOIN qugeo_order ON quor_TransferOrder_id = fsto_id WHERE fstr_id = {OrderId} AND fsto_ordertype = 1", UserService.GetAPIConnectionString());

            if (dtDispatch != null && dtDispatch.Rows.Count > 0)
            {
                DataRow dr = dtDispatch.Rows[0];
                QuorId = Convert.ToInt32(dr["quor_id"]);
                hdnUQuorId.Value = QuorId.ToString();
            }

            try
            {
                // Check if quor_id already exists in qugeo_order_courier
                var checkExistence = DataServiceMySql.GetDataTable($"SELECT COUNT(*) FROM qugeo_order_courier WHERE quor_id = {QuorId}", UserService.GetAPIConnectionString());
                if (checkExistence != null && checkExistence.Rows.Count > 0 && Convert.ToInt32(checkExistence.Rows[0][0]) > 0)
                {
                    // If quor_id exists, show a message and return
                    Common.ShowToastifyMessage(this.Page, "Order has already been dispatched.", "warning");
                    return;
                }
                string dispatchTime;
                // string dispatchDateTime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                string dispatchDate = txtDispatchDate.Text;
                DateTime parseddispatchDate = DateTime.ParseExact(dispatchDate, "yyyy-MM-dd", null);

                // Compare the parsed DateTime with DateTime.Now
                if (parseddispatchDate > DateTime.Now)
                {
                    Common.ShowToastifyMessage(this.Page, "Please select a valid date. The date cannot be greater than today's date", "warning");
                }
                else
                {
                    DateTime now = DateTime.Now;
                    dispatchTime = String.Format("{0}:{1}:{2}", now.ToString("HH"), now.ToString("mm"), now.ToString("ss"));


                    List<KeyValuePair<string, object>> qugeoparams = new List<KeyValuePair<string, object>>();
                    qugeoparams.Add(new KeyValuePair<string, object>("courier", selCourier.SelectedItem.Value != "-1" ? selCourier.SelectedItem.Value : "-1"));
                    qugeoparams.Add(new KeyValuePair<string, object>("trackingNumber", txtTrackingNo.Text));
                    qugeoparams.Add(new KeyValuePair<string, object>("dispatchDate", dispatchDate));
                    qugeoparams.Add(new KeyValuePair<string, object>("dispatchTime", dispatchTime));
                    qugeoparams.Add(new KeyValuePair<string, object>("orderId", QuorId));
                    qugeoparams.Add(new KeyValuePair<string, object>("trackingURL", txtTrackingURL.Text));
                    qugeoparams.Add(new KeyValuePair<string, object>("statusId", hdnstatusId.Value));
                    qugeoparams.Add(new KeyValuePair<string, object>("type", 4));

                    string strSqlcourier = $"INSERT INTO qugeo_order_courier(qoc_courier, qoc_qcn, qoc_date, qoc_time, quor_id, qoc_trackingUrl) " +
                                            $"VALUES(@courier, @trackingNumber, @dispatchDate, @dispatchTime, @orderId, @trackingURL)";
                    DataServiceMySql.ExecuteSql(strSqlcourier, UserService.GetAPIConnectionString(), qugeoparams);

                    string strSqlOrder = $"UPDATE qugeo_order SET quor_Type=@type, quor_Status=@statusId WHERE quor_id=@orderId";
                    DataServiceMySql.ExecuteSql(strSqlOrder, UserService.GetAPIConnectionString(), qugeoparams);

                    string quor_DeliveryConfTime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                    qugeoparams.Add(new KeyValuePair<string, object>("custstatus_id", 15));
                    qugeoparams.Add(new KeyValuePair<string, object>("updateat", quor_DeliveryConfTime));
                    qugeoparams.Add(new KeyValuePair<string, object>("corderId", OrderId));
                    string updateQry = $"UPDATE retaline_customer_order SET status_id = @custstatus_id, updated_at = @updateat, order_trackURL = @trackingURL WHERE order_id = @corderId ";
                    DataServiceMySql.ExecuteSql(updateQry, UserService.GetAPIConnectionString(), qugeoparams);

                    string url = ConfigurationSettings.AppSettings.Get("api.url") ?? "http://bizapi.dev.grozeo.in";

                    var options = new RestClientOptions(url);
                    var client = new RestClient(options);
                    var request = new RestRequest("/api/finascop/finascopPostingService", Method.Post);
                    request.AlwaysMultipartFormData = true;
                    request.AddParameter("order_id", OrderId);
                    request.AddParameter("finascopEventRefId", "07802425-38d7-11ee-9967-065723bafb24");
                    request.AddParameter("storegroup_id", this.CurrentUser.APIStoreId);
                    RestResponse response = client.ExecuteAsync(request).Result;

                    await UpdateDynamoDbOrder(uuid, tstamp, 6);
                    Common.ShowCustomAlert(this.Page, "Order Dispatched!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your order is dispatched!</a></h5>", true, "/Business/DeliveryBookingFailed");
                }
            }
            catch
            {
                Common.ShowToastifyMessage(this.Page, "Invalid order.", "danger");
                return;
            }
        }
        protected void lbtnDeliveryUpdate_Click(object sender, EventArgs e)
        {
            string orderId = "";
            string paymentMode = "";
            int QuorId = 0;

            LinkButton lbtn = (LinkButton)sender;
            string commandArgument = lbtn.CommandArgument;

            string[] args = commandArgument.Split(',');

            if (args.Length == 2)
            {
                orderId = args[0];
                paymentMode = args[1];
            }

            hdnUOrderId.Value = orderId;
            hdnPayementMode.Value = paymentMode;

            var dtDel = DataServiceMySql.GetDataTable($"SELECT quor_id FROM finascop_stock_transfer_order INNER JOIN qugeo_order ON quor_TransferOrder_id = fsto_id WHERE fstr_id = {orderId} AND fsto_ordertype = 1", UserService.GetAPIConnectionString());

            if (dtDel != null && dtDel.Rows.Count > 0)
            {
                DataRow dr = dtDel.Rows[0];
                QuorId = Convert.ToInt32(dr["quor_id"]);
            }
            hdnUQuorId.Value = QuorId.ToString();

            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalDeliveryUpdate').modal('toggle');</script>");

        }

        protected void lbtnBookManual_Click(object sender, EventArgs e)
        {
            string orderId = "";
            LinkButton btn = (LinkButton)sender;
            orderId = (btn.CommandArgument).ToString();

            GridViewRow row = (GridViewRow)btn.NamingContainer;

            LinkButton uuidLinkButton = (LinkButton)row.FindControl("uuidLinkButton");
            string UUID = uuidLinkButton.CommandArgument;

            LinkButton tstampLinkButton = (LinkButton)row.FindControl("tstampLinkButton");
            string tstamp = tstampLinkButton.CommandArgument;

            hdnOrderId.Value = orderId;
            hduuid.Value = UUID;
            hdtstamp.Value = tstamp;

            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalDeliveryDetails').modal('toggle');</script>");

        }
        protected async void lbtnSkip_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            int orderID = int.Parse(btn.CommandArgument);

            GridViewRow row = (GridViewRow)btn.NamingContainer;

            LinkButton uuidLinkButton = (LinkButton)row.FindControl("uuidLinkButton");
            string UUID = uuidLinkButton.CommandArgument;

            LinkButton tstampLinkButton = (LinkButton)row.FindControl("tstampLinkButton");
            string tstamp = tstampLinkButton.CommandArgument;

            try
            {
                await UpdateDynamoDbOrder(UUID, tstamp, 7);
                Common.ShowCustomAlert(this.Page, "Success!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Success</a></h5>", true, "/Business/DeliveryBookingFailed");
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Failed!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Failed</a></h5>", true, "/Business/DeliveryBookingFailed");
                return;

            }

        }
        protected void ddlLiveDrivers_SelectedIndexChanged(object sender, EventArgs e)
        {
            if (ddlLiveDrivers.SelectedValue != "-1")
            {
                DropDownList ddlLiveDrivers = (DropDownList)sender;
                VId = ddlLiveDrivers.SelectedValue;
            }
        }
        protected void btnCancelOrder_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            string OrderID = btn.CommandArgument;

            GridViewRow row = (GridViewRow)btn.NamingContainer;

            LinkButton uuidLinkButton = (LinkButton)row.FindControl("uuidLinkButton");
            string UUID = uuidLinkButton.CommandArgument;

            LinkButton tstampLinkButton = (LinkButton)row.FindControl("tstampLinkButton");
            string tstamp = tstampLinkButton.CommandArgument;

            hidOrderId.Value = OrderID;
            hidUuid.Value = UUID;
            hidtstamp.Value = tstamp;

            SDSCancelOrder.SelectParameters["orderId"].DefaultValue = OrderID;

            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalCancelOrder').modal('toggle');</script>");

        }

        protected void InitiateCancellation_Click(object sender, EventArgs e)
        {
            int orderId = int.Parse(hidOrderId.Value);
            CancelOrder(orderId);
        }

        public async void CancelOrder(int orderId)
        {   // cancel the order
            int StoreId = 0;
            string StoreName = "";

            string uuid = hidUuid.Value;
            string tstamp = hidtstamp.Value;

            try
            {
                var dtsgID = DataServiceMySql.GetDataTable($"SELECT br_StoreGroup,store_group_name FROM retaline_customer_order INNER JOIN finascop_branch ON br_ID=order_branch_id INNER JOIN finascop_branch_group ON store_group_id=br_storeGroup WHERE order_id={orderId}", UserService.GetAPIConnectionString());
                if (dtsgID != null && dtsgID.Rows.Count > 0)
                {
                    DataRow dr = dtsgID.Rows[0];
                    StoreId = Convert.ToInt32(dr["br_StoreGroup"]);
                    StoreName = dr["store_group_name"].ToString();

                }

                List<KeyValuePair<string, object>> orderParams = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("orderId", orderId),
                    new KeyValuePair<string, object>("storegroupid", StoreId)
                };
                string orderdetalis = "SELECT order_id,quor_id,quor_DeliveryMethodsAllowed,order_customer_id,br_Name,order_branch_id,order_order_id,br_Name,payment_mode,fsto_id,total,order_total_gst,order_wallet_amount,order_kfc_amount,order_total_cgst,order_total_sgst,status_id FROM qugeo_order  INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status INNER JOIN finascop_stock_transfer_order ON fsto_id = quor_TransferOrder_id INNER JOIN retaline_customer_order ON order_id = fstr_id  INNER JOIN finascop_branch ON br_ID = order_branch_id  INNER JOIN finascop_branch_group ON store_group_id = br_storeGroup WHERE order_id =@orderId and br_storeGroup=@storegroupid ";
                DataTable custItemTbl = DataServiceMySql.GetDataTable(orderdetalis, UserService.GetAPIConnectionString(), orderParams);
                DataRow dc = custItemTbl.Rows[0];

                string deleteSql = $"DELETE FROM finascop_stock_blocked WHERE order_id = @orderId";
                int status = DataServiceMySql.ExecuteSql(deleteSql, UserService.GetAPIConnectionString(), orderParams);
                double refundamt = ((Convert.ToDouble(dc["payment_mode"])) == 2 || (Convert.ToDouble(dc["payment_mode"])) == 5 ? (Convert.ToDouble(dc["total"])) : 0);
                string logMessage = $"Order {dc["order_order_id"]} from {dc["br_Name"]} cancelled by {StoreName} after clarification with customer due to item(s) unavailability.";
                string walletResult = Core.Services.APIService.WalletBalance(Convert.ToInt32(dc["order_customer_id"]), dc["order_id"].ToString(), refundamt, logMessage);
                string custOrderId = dc["order_order_id"].ToString();
                string inrtQry = "";
                List<KeyValuePair<string, object>> historyParams = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("orderId", dc["order_id"]),
                    new KeyValuePair<string, object>("customerId", dc["order_customer_id"]),
                    new KeyValuePair<string, object>("reason",hidCancelReason.Value),
                    new KeyValuePair<string, object>("cancelledId",this.CurrentUser.Id)

                };

                string action = "Action By" + StoreId.ToString();
                Core.Services.Order.OrderService.AddOrderHistoryData(Convert.ToInt32(orderId), 19, action);//Cancelled=19
                inrtQry += $" INSERT INTO retaline_customer_order_cancellationdets(customer_id, order_id, reason, cancelled_by_type, cancelled_by_id) VALUES(@customerId,@orderId,@reason,6,@cancelledId)";
                var histresult = DataServiceMySql.ExecuteScalar(inrtQry, UserService.GetAPIConnectionString(), historyParams);
                // Update order and related tables
                var updateParams = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("fstostatus", 15),
                    new KeyValuePair<string, object>("updatedBy", StoreId),
                    new KeyValuePair<string, object>("rpbstatus", 3),
                    new KeyValuePair<string, object>("quorstatus", 40),
                    new KeyValuePair<string, object>("quor_id", dc["quor_id"]),
                    new KeyValuePair<string, object>("date", DateTime.Now),
                    new KeyValuePair<string, object>("fstrId", orderId)
                };
                Core.Services.Order.OrderService.UpdateOrderStatus(Convert.ToInt32(orderId), 19);//Cancelled=19
                string updtQry = "UPDATE finascop_stock_transfer_order SET fsto_status = @fstostatus, fsto_updateby = @updatedBy WHERE fstr_id = @fstrId; ";
                //updtQry += "UPDATE finascop_stock_transfer_order_details_barcodes_temp SET rpb_status = @rpbstatus WHERE tmp_barcode_fstoId = @transferOrdId ; ";
                DataServiceMySql.ExecuteSql(updtQry, UserService.GetAPIConnectionString(), updateParams);
                //update qugeo_order status
                Core.Services.Order.OrderService.UpdateQueGeoStatus(Convert.ToInt32(dc["quor_id"]), 40);//Cancelled=40   

                //order cancellation call
                string url = ConfigurationSettings.AppSettings.Get("api.url");
                if (String.IsNullOrEmpty(url))
                {
                    url = "http://bizapi.dev.grozeo.in";
                }

                var options = new RestClientOptions(url);

                var client = new RestClient(options);

                var request = new RestRequest("/api/finascop/finascopPostingService", Method.Post);
                request.AlwaysMultipartFormData = true;
                request.AddParameter("order_id", orderId);
                request.AddParameter("finascopEventRefId", "078025ad-38d7-11ee-9967-065723bafb24");
                request.AddParameter("storegroup_id", StoreId);
                RestResponse response = client.ExecuteAsync(request).Result;

                //Console.WriteLine(response.Content);

                await UpdateDynamoDbOrder(uuid, tstamp, 2);
                ShowSuccess("Order Cancelled Successfully!", $"<h6 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">The order {custOrderId} is cancelled successfully!</a></h6>");
            }
            catch
            {
                Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
            }
        }

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            int brId = 0;
            //ODSFailedOrders.SelectParameters["branchid"].DefaultValue = selBranches.SelectedValue;
            try
            {
                if (!string.IsNullOrEmpty(selBranches.SelectedValue))
                    brId = Convert.ToInt32(selBranches.SelectedValue);
            }
            catch { brId = 0; }

            Filter_by_StoreId = brId;
            BindGrid();

        }

        protected void lbtnSearch_Click(object sender, EventArgs e)
        {
            string orderID = txtOrderId.Text.Trim();
            ODSFailedOrders.SelectParameters["orderID"].DefaultValue = orderID;

        }

        protected void btnDeliveryUpdate_Click(object sender, EventArgs e)
        {
            int StoreId = 0;
            string StoreName = "";
            string orderId = hdnUOrderId.Value;
            string paymentMode = hdnPayementMode.Value;
            string quorid = hdnUQuorId.Value;

            try
            {

                string dispatchDateTime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                string bankTransactionId = "";
                int mode = 0;
                if (paymentMode == "1" || paymentMode == "7")
                {
                    dvPaymentMode.Visible = true;
                    if (rbBank.Checked == true)
                    {
                        mode = 6;
                        bankTransactionId = txtTransactionId.Text;
                    }
                    else
                    {
                        mode = 7;
                        bankTransactionId = "";
                    }
                }
                else
                {
                    dvPaymentMode.Visible = false;
                }

                List<KeyValuePair<string, object>> qugeoparams = new List<KeyValuePair<string, object>>();
                qugeoparams.Add(new KeyValuePair<string, object>("p_quor_id", quorid));
                qugeoparams.Add(new KeyValuePair<string, object>("banktransactionId", bankTransactionId));
                qugeoparams.Add(new KeyValuePair<string, object>("remarks", txtDeliveryRemarks.Text));
                qugeoparams.Add(new KeyValuePair<string, object>("date", dispatchDateTime));
                qugeoparams.Add(new KeyValuePair<string, object>("statusId", 15));
                qugeoparams.Add(new KeyValuePair<string, object>("current_datetime", txtDeliveryDate.Text));
                qugeoparams.Add(new KeyValuePair<string, object>("payementMode", mode));
                string strSqlOrder = $"UPDATE qugeo_order SET quor_Status=@statusId, quor_UpdateOn=@current_datetime, quor_DeliveredTime=@current_datetime WHERE quor_id=@p_quor_id ";
                DataServiceMySql.ExecuteSql(strSqlOrder, UserService.GetAPIConnectionString(), qugeoparams);


                qugeoparams.Add(new KeyValuePair<string, object>("p_order_id", orderId));
                string updateQry = $"UPDATE retaline_customer_order SET status_id = 18, order_status_addinfo = '###2', payment_mode = @payementMode, order_ondel_bankref_id = @banktransactionId, " +
                    $"updated_at = @current_datetime WHERE order_id = @p_order_id ";
                DataServiceMySql.ExecuteSql(updateQry, UserService.GetAPIConnectionString(), qugeoparams);

                var dtsgID = DataServiceMySql.GetDataTable($"SELECT br_StoreGroup, store_group_name FROM retaline_customer_order LEFT JOIN finascop_branch ON br_ID = order_branch_id LEFT JOIN finascop_branch_group ON store_group_id = br_ID WHERE order_id = {orderId}", UserService.GetAPIConnectionString());

                if (dtsgID != null && dtsgID.Rows.Count > 0)
                {
                    DataRow dr = dtsgID.Rows[0];
                    StoreId = Convert.ToInt32(dr["br_StoreGroup"]);
                    StoreName = dr["store_group_name"].ToString();

                }

                //update ordere history
                string action = "Action By" + StoreId.ToString();
                Core.Services.Order.OrderService.AddOrderHistoryData(Convert.ToInt32(quorid), 18, action);//Delivered=18
                try
                {
                    DataTable dtResult = DataServiceMySql.GetDataTable("UpdateDeliveryStatus", UserService.GetAPIConnectionString(), qugeoparams, true);
                    ShowSuccess("Delivery Completed!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your item/s has been delivered successfully!</a></h5>");
                }
                catch
                {
                    Common.ShowToastifyMessage(this.Page, "Invalid order.", "danger");
                    return;
                }

            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "Invalid order.", "danger");
                return;
            }

        }

        // API Booking Functions
        protected async void lbtnRetryAPIBooking_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            int orderID = int.Parse(btn.CommandArgument);

            GridViewRow row = (GridViewRow)btn.NamingContainer;

            LinkButton uuidLinkButton = (LinkButton)row.FindControl("uuidLinkButton");
            string UUID = uuidLinkButton.CommandArgument;

            LinkButton tstampLinkButton = (LinkButton)row.FindControl("tstampLinkButton");
            string tstamp = tstampLinkButton.CommandArgument;

            List<KeyValuePair<string, object>> APIRetry_Params = new List<KeyValuePair<string, object>>();

            string APIRetry = $"UPDATE finascop_stock_transfer_order SET fsto_status=10, fsto_hasShipmentCreated=0 WHERE fstr_id=@orderID";

            APIRetry_Params.Add(new KeyValuePair<string, object>("orderID", orderID));
            try
            {
                DataServiceMySql.ExecuteSql(APIRetry, UserService.GetAPIConnectionString(), APIRetry_Params);
                await UpdateDynamoDbOrder(UUID, tstamp, 8);
                Common.ShowCustomAlert(this.Page, "Success!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your item(s) have been successfully submitted for API booking!</a></h5>", true, "/Business/DeliveryBookingFailed");
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Failed!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">API Booking Failed.Manual Booking suggested</a></h5>", true, "/Business/DeliveryBookingFailed");
                return;

            }
        }

        protected void lbtnAPIBookManually_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            int orderID = int.Parse(btn.CommandArgument);

            GridViewRow row = (GridViewRow)btn.NamingContainer;

            LinkButton uuidLinkButton = (LinkButton)row.FindControl("uuidLinkButton");
            string UUID = uuidLinkButton.CommandArgument;

            LinkButton tstampLinkButton = (LinkButton)row.FindControl("tstampLinkButton");
            string tstamp = tstampLinkButton.CommandArgument;

            hdnCanOrderId.Value = orderID.ToString();
            hdnUUID.Value = UUID;
            hdntStamp.Value = tstamp;

            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalmanualDelivery').modal('toggle');</script>");
        }
        protected async void btnManualDeliverySubmit_Click(object sender, EventArgs e)
        {
            try
            {
                int OrderId = Convert.ToInt32(hdnCanOrderId.Value);
                int QuorId = 0;
                int fstoId = 0;
                string UUID = hdnUUID.Value;
                string tstamp = hdntStamp.Value;

                var dtDispatch = DataServiceMySql.GetDataTable($"SELECT quor_id,fsto_id FROM finascop_stock_transfer_order INNER JOIN qugeo_order ON quor_TransferOrder_id = fsto_id WHERE fstr_id = {OrderId} AND fsto_ordertype = 1", UserService.GetAPIConnectionString());

                if (dtDispatch != null && dtDispatch.Rows.Count > 0)
                {
                    DataRow dr = dtDispatch.Rows[0];
                    QuorId = Convert.ToInt32(dr["quor_id"]);
                    fstoId = Convert.ToInt32(dr["fsto_id"]);
                }

                txtDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
                txtTime.Text = DateTime.Now.ToString("HH:mm:ss");
                string quor_DeliveryConfTime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                string quorDeliveryConfTime = DateTime.Now.ToString("yyyy-MM-dd 00:00:00");
                var data = new List<Dictionary<string, object>>();
                data.Add(new Dictionary<string, object> {
                            {"qmd_deliveredBy", txtDelivBoy.Text },
                            {"qmd_Date", txtDate.Text },
                            {"qmd_Time", txtTime.Text },
                            {"quor_id", OrderId },
                            {"qmd_createdOn", quor_DeliveryConfTime },
                            {"qmd_createdBy", 3 }
                        });
                List<KeyValuePair<string, object>> APIparams = new List<KeyValuePair<string, object>>();
                APIparams = new List<KeyValuePair<string, object>>();
                APIparams.Add(new KeyValuePair<string, object>("qmddeliveredBy", txtDelivBoy.Text));
                APIparams.Add(new KeyValuePair<string, object>("qmdDate", txtDate.Text));
                APIparams.Add(new KeyValuePair<string, object>("qmdTime", txtTime.Text));
                APIparams.Add(new KeyValuePair<string, object>("quorid", QuorId));
                APIparams.Add(new KeyValuePair<string, object>("qmdcreatedOn", quor_DeliveryConfTime));
                string strSql = $"INSERT INTO qugeo_manual_deliver(qmd_deliveredBy, qmd_Date, qmd_Time, quor_id, qmd_createdOn, qmd_createdBy) " +
                        $"VALUES(@qmddeliveredBy, @qmdDate, @qmdTime, @quorid, @qmdcreatedOn, 3)";
                DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), APIparams);

                APIparams = new List<KeyValuePair<string, object>>();
                APIparams.Add(new KeyValuePair<string, object>("quorDeliveryConfTime", quorDeliveryConfTime));
                APIparams.Add(new KeyValuePair<string, object>("quorUpdateOn", quor_DeliveryConfTime));
                APIparams.Add(new KeyValuePair<string, object>("quorid", OrderId));
                string fstostatus = $"UPDATE qugeo_order SET quor_Type = 6, quor_DeliveryConfTime = @quorDeliveryConfTime,quor_Status = 15, quor_UpdateOn = @quorUpdateOn WHERE quor_TransferOrder_id = @fstoId";
                DataServiceMySql.ExecuteSql(fstostatus, UserService.GetAPIConnectionString(), APIparams);

                APIparams = new List<KeyValuePair<string, object>>();
                APIparams.Add(new KeyValuePair<string, object>("updateat", quor_DeliveryConfTime));
                APIparams.Add(new KeyValuePair<string, object>("orderid", OrderId));
                string updateQry = $"UPDATE retaline_customer_order SET status_id = 18, order_status_addinfo = '###2', payment_mode = IF(payment_mode=1,'1',payment_mode), order_ondel_bankref_id = '###7', " +
                    $"updated_at = @updateat WHERE order_id = @orderid ";
                DataServiceMySql.ExecuteSql(updateQry, UserService.GetAPIConnectionString(), APIparams);

                APIparams = new List<KeyValuePair<string, object>>();
                APIparams.Add(new KeyValuePair<string, object>("orderID", OrderId));
                string query = $"UPDATE finascop_stock_transfer_order SET fsto_status=10, fsto_hasShipmentCreated=2 WHERE fstr_id=@orderID";
                DataServiceMySql.ExecuteSql(query, UserService.GetAPIConnectionString(), APIparams);

                string action = "Action By" + this.CurrentUser.APIStoreId.ToString();
                Core.Services.Order.OrderService.AddOrderHistoryData(OrderId, 18, action);

                await UpdateDynamoDbOrder(UUID, tstamp, 9);
                Common.ShowCustomAlert(this.Page, "Delivered Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your items are delivered successfully!</a></h5>", true, "/Business/DeliveryBookingFailed");
            }

            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Failed!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Invalid Order</a></h5>", true, "/Business/DeliveryBookingFailed");
                return;

            }
        }
        protected async void lbtnAPICancelOrder_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            int orderID = int.Parse(btn.CommandArgument);

            GridViewRow row = (GridViewRow)btn.NamingContainer;

            LinkButton uuidLinkButton = (LinkButton)row.FindControl("uuidLinkButton");
            string UUID = uuidLinkButton.CommandArgument;

            LinkButton tstampLinkButton = (LinkButton)row.FindControl("tstampLinkButton");
            string tstamp = tstampLinkButton.CommandArgument;

            int custId = 0;

            List<KeyValuePair<string, object>> CustParams = new List<KeyValuePair<string, object>>
            {
                  new KeyValuePair<string, object>("customerId",custId),
                  new KeyValuePair<string,object>("orderId",orderID)
            };

            string custdetalis = "SELECT order_customer_id FROM retaline_customer_order  WHERE order_id=@orderID";
            DataTable custItemTbl = DataServiceMySql.GetDataTable(custdetalis, UserService.GetAPIConnectionString(), CustParams);
            DataRow dc = custItemTbl.Rows[0];
            custId = Convert.ToInt32(dc["order_customer_id"]);

            List<KeyValuePair<string, object>> InsertParams = new List<KeyValuePair<string, object>>
            {
                    new KeyValuePair<string, object>("orderId", orderID),
                    new KeyValuePair<string, object>("customerId",custId),
                    new KeyValuePair<string, object>("reason","API cancellation requested"),
                    new KeyValuePair<string, object>("cancelledId",this.CurrentUser.Id),
                    new KeyValuePair<string, object>("created_at",DateTime.Now),
            };
            try
            {
                string inrtQry = $" INSERT INTO retaline_customer_order_cancellationdets(customer_id, order_id, reason, cancelled_by_type,cancelled_by_id,created_at) VALUES(@customerId,@orderId,@reason,6,@cancelledId,@created_at)";
                var result = DataServiceMySql.ExecuteScalar(inrtQry, UserService.GetAPIConnectionString(), InsertParams);

                await UpdateDynamoDbOrder(UUID, tstamp, 10);
                Common.ShowCustomAlert(this.Page, "Success!", $"<h6 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your API Booking has been successfully submitted for cancellation</a></h6>", true, "/Business/DeliveryBookingFailed");
            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Failed!", $"<h6 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">API Booking cancellation failed. Please retry again later</a></h6>", true, "/Business/DeliveryBookingFailed");
                return;

            }
        }
        protected void lbtnCancelBooking_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            int orderID = int.Parse(btn.CommandArgument);

            GridViewRow row = (GridViewRow)btn.NamingContainer;

            LinkButton uuidLinkButton = (LinkButton)row.FindControl("uuidLinkButton");
            string UUID = uuidLinkButton.CommandArgument;

            LinkButton tstampLinkButton = (LinkButton)row.FindControl("tstampLinkButton");
            string tstamp = tstampLinkButton.CommandArgument;

            hdOID.Value = orderID.ToString();
            hdUID.Value = UUID.ToString();
            hdttstmp.Value = tstamp.ToString();

            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalCancelCourierOrder').modal('toggle');</script>");

        }

        protected async void btnYes_Click(object sender, EventArgs e)
        {
            string UUID = hdUID.Value;
            string tstamp = hdttstmp.Value;
            int orderId = Convert.ToInt32(hdOID.Value);
            int custId = 0;

            List<KeyValuePair<string, object>> CustParams = new List<KeyValuePair<string, object>>
            {
                  new KeyValuePair<string, object>("customerId",custId),
                  new KeyValuePair<string,object>("orderId",orderId)
            };

            string custdetalis = "SELECT order_customer_id FROM retaline_customer_order  WHERE order_id=@orderId";
            DataTable custItemTbl = DataServiceMySql.GetDataTable(custdetalis, UserService.GetAPIConnectionString(), CustParams);
            DataRow dc = custItemTbl.Rows[0];
            custId = Convert.ToInt32(dc["order_customer_id"]);

            List<KeyValuePair<string, object>> InsertParams = new List<KeyValuePair<string, object>>
            {
                    new KeyValuePair<string, object>("orderId", orderId),
                    new KeyValuePair<string, object>("customerId",custId),
                    new KeyValuePair<string, object>("reason", ddlCourierCancelReason.SelectedItem),
                    new KeyValuePair<string, object>("cancelledId",this.CurrentUser.Id),
                    new KeyValuePair<string, object>("created_at",DateTime.Now),
            };
            try
            {
                string inrtQry = $" INSERT INTO retaline_customer_order_cancellationdets(customer_id, order_id, reason, cancelled_by_type,cancelled_by_id,created_at) VALUES(@customerId,@orderId,@reason,6,@cancelledId,@created_at)";
                var result = DataServiceMySql.ExecuteScalar(inrtQry, UserService.GetAPIConnectionString(), InsertParams);
                await UpdateDynamoDbOrder(UUID, tstamp, 5);
                Common.ShowCustomAlert(this.Page, "Success!", "Your booking has been successfully submitted for cancellation", true, "/Business/DeliveryBookingFailed");

            }
            catch (Exception ex)
            {
                Common.ShowCustomAlert(this.Page, "Failed!", "Booking cancellation failed. Please retry again later", true, "/Business/DeliveryBookingFailed");
                return;
            }
        }
        protected async void btnHandledOrderOK_Click(object sender, EventArgs e)
        {
            string orderID = hfOrderID.Value;
            string uuid = hfUUID.Value;
            string timestamp = hftstamp.Value;

            try
            {
                string tableprefix = ConfigurationManager.AppSettings.Get("AWS_Prefix");
                string table = "delayed_orders";
                string tableName = String.Concat(tableprefix, table);


                var key = new Dictionary<string, AttributeValue>
                {
                  { "uuid", new AttributeValue { S = uuid  } },
                  { "tstamp", new AttributeValue { S = timestamp } }
                };

                var attributeUpdates = new Dictionary<string, AttributeValueUpdate>
                {
                { "type", new AttributeValueUpdate
                    {
                    Action = AttributeAction.PUT,
                    Value = new AttributeValue { N = 5.ToString() }
                    }
                }
            };

                await DynamoService.UpdateToDynamoDb(tableName, key, attributeUpdates);
            }
            catch (Exception ex)
            {
                throw new Exception("Error updating order in DynamoDB", ex);
            }
        }

        protected void ddlCancelReason_SelectedIndexChanged(object sender, EventArgs e)
        {
            DropDownList ddlCancelReason = sender as DropDownList;
            if (ddlCancelReason != null)
            {
                RepeaterItem item = (RepeaterItem)ddlCancelReason.NamingContainer;
                if (item != null)
                {
                    string cancelReason = ddlCancelReason.SelectedItem.ToString();
                    hidCancelReason.Value = cancelReason;

                }
            }

        }

        protected void lbtnViewDetails_Click(object sender, EventArgs e)
        {
            LinkButton btn = (LinkButton)sender;
            int orderID = int.Parse(btn.CommandArgument);

            List<KeyValuePair<string, object>> CustParams = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string,object>("orderId",orderID)
            };

            string ordDets = @"SELECT 
    co.order_order_id, 
    co.total, 
    co.order_cess, 
    co.order_total_amount, 
    co.order_delivery_charge_et, 
    (co.order_total_gst + co.order_delivery_charge_gst) AS totalGst, 
    co.order_roundoff,
    CONCAT(
        fb.br_Name, ', ', 
        fb.br_Address, ', ', 
        fb.br_City, ', ', 
        fd.dst_Name, ', ', 
        fs.st_name, ', ', 
        fb.br_pincode
    ) AS 'FromAddress',
    fb.br_Phone,
    fb.br_Email,
    CONCAT_WS(
        ', ',
        NULLIF(da.order_customer_name, ''),
        NULLIF(da.order_address, ''),
        NULLIF(da.order_address2, ''),
        NULLIF(da.order_house_no, ''),
        NULLIF(da.order_house_name, ''),
        NULLIF(da.order_land_mark, ''),
        NULLIF(da.order_state, ''),
        NULLIF(da.order_country, ''),
        NULLIF(da.order_pin, '')
    ) AS 'ToAddress',
    da.order_contact_no,
    da.order_customer_email,
    co.order_packedbags_count,
    tod.rtopd_packetweigh,
    tod.rtpod_length,
    tod.rtpod_breadth,
    tod.rtpod_height
FROM 
    retaline_customer_order co
INNER JOIN 
    finascop_stock_transfer_order sto ON sto.fstr_id = co.order_id
INNER JOIN 
    finascop_branch fb ON co.order_branch_id = fb.br_ID
INNER JOIN 
    finascop_district fd ON fb.br_district = fd.dst_Id
left JOIN 
    finascop_state fs ON fb.br_State = fs.st_ID
left JOIN 
    retaline_customer_order_delivery_address da ON da.order_id = co.order_order_id
left JOIN 
    retaline_transfer_order_pack_details tod ON tod.rtopd_fstoId = sto.fsto_id
WHERE 
    co.order_id =@orderID;";


            DataTable custItemTbl = DataServiceMySql.GetDataTable(ordDets, UserService.GetAPIConnectionString(), CustParams);
            DataRow dc = custItemTbl.Rows[0];
            if (custItemTbl != null && custItemTbl.Rows.Count > 0)
            {
                string Order_Id = dc["order_order_Id"].ToString();
                string FromAddress = dc["FromAddress"].ToString();
                string ToAddress = dc["ToAddress"].ToString();
                ltrOrder.Text = Order_Id;
                ltrFromAddress.Text = FromAddress;
                ltrfPhone.Text = "Phone: " + dc["br_Phone"].ToString();
                ltrfEmail.Text = "Email: " + dc["br_Email"].ToString();
                ltrToAddress.Text = ToAddress;
                ltrTPhone.Text = "Phone: " + dc["order_contact_no"].ToString();
                ltrTEmail.Text = "Email: " + dc["order_customer_email"].ToString();

                ltrOrdAmt.Text = dc["total"].ToString();
                ltrCess.Text = dc["order_cess"].ToString();
                ltrSubTotal.Text = dc["order_total_amount"].ToString();
                ltrDeliveryCharge.Text = dc["order_delivery_charge_et"].ToString();
                ltrGST.Text = dc["totalGst"].ToString();
                ltrRoundOff.Text = dc["order_roundoff"].ToString();

                StringBuilder packetHtml = new StringBuilder();
                int packetIndex = 1;

                foreach (DataRow row in custItemTbl.Rows)
                {
                    string GetSafeString(object value) => value != DBNull.Value ? value.ToString() : string.Empty;

                    string packetPacketweigh = GetSafeString(row["rtopd_packetweigh"]);
                    string packetLength = GetSafeString(row["rtpod_length"]);
                    string packetBreadth = GetSafeString(row["rtpod_breadth"]);
                    string packetHeight = GetSafeString(row["rtpod_height"]);

                    packetHtml.Append($"<span style='width: 65%; text-align: left;'></span><span style='color: black; text-align: left; width: 100%;'><i class='fa-light fa-cube mr-2 tx-20'></i>  L : {packetLength} cm * B : {packetBreadth} cm * H : {packetHeight} cm </span>");

                    packetIndex++;
                }
                ltrNoPacket.Text = dc["order_packedbags_count"].ToString();
                ltrPacket.Text = packetHtml.ToString();
                ltrPacketWeight.Text = dc["rtopd_packetweigh"].ToString() + " kg";
            }
            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalviewCourierDetials').modal('toggle');</script>");
        }

        protected void btnLECancelOrder_Click(object sender, EventArgs e)
        {

        }

        protected void btnLEAssignRider_Click(object sender, EventArgs e)
        {

        }
    }
}


