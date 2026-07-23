
using RestSharp;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.IO.Packaging;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls.Orders
{
    public partial class CtrlViewOrders : Base.BasePartnerUserControl
    {
        public string hdnfstoid_ClientId
        {
            get { return hdnfstoid.ClientID;}
        }
        public string hdnorderid_ClientId
        {
            get { return hdnorderid.ClientID; }
        }
        public string hdnordermethod_ClientId
        {
            get { return hdnordermethod.ClientID; }
        }
        public string hdnIncompleteorder_ClientId
        {
            get { return hdnIncompleteorder.ClientID; }
        }
        private string incompleteOrderValue;

        protected void Page_Load(object sender, EventArgs e)
        {
            string order_order_id=hdnorderid.Value;
            DataTable tblItems = GetOrderDetails(order_order_id);  
            if (tblItems != null && tblItems.Rows.Count > 0)
            {
                DataRow row = tblItems.Rows[0]; 
                ltrOrderId.Text = row["order_order_id"].ToString();
                ltrName.Text = row["cust_customer_name"].ToString();
                ltrMobile.Text = row["cust_mobile"].ToString();
            }
            incompleteOrderValue = hdnIncompleteorder.Value;
            plcincompleteorder.Visible = (incompleteOrderValue == "1");
            plcorderaccept.Visible = (incompleteOrderValue != "1");

            DataTable orderdata = GetAssignOrder(hdnorderid.Value);
            if (orderdata !=null && orderdata.Rows.Count>0)
            {
                DataRow getdata = orderdata.Rows[0];
                hdnFstoUid.Value = getdata?["fsto_uid"].ToString();
                hdnorder_id.Value = getdata?["order_id"].ToString();

            }
        }
        //get order and custmoer details  
        public DataTable GetOrderDetails(string orderId)
        {
            try
            {
                List<KeyValuePair<string, object>> ordrparams = new List<KeyValuePair<string, object>>();
                ordrparams.Add(new KeyValuePair<string, object>("orderId", orderId));
                string sql = $"SELECT order_id,order_order_id,status_id,order_customer_id,cust_mobile,cust_customer_name,status_id FROM retaline_customer_order INNER JOIN retaline_customer ON cust_id = order_customer_id WHERE order_order_id = @orderId";
                var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString(), ordrparams);
                return tblItems; 
            }
            catch(Exception ex)
            {
                return null;
            }
        }

        protected void btnaccept_Click(object sender, EventArgs e)
        {
            var row = GetOrderDetails(hdnorderid.Value)?.Rows[0];
            string status_id = row?["status_id"]?.ToString();
            string orderid = row?["order_id"]?.ToString();
            if (status_id == "7" || status_id=="5" || status_id == "4")
            {
                if (UpdateOrderStatus(orderid, "4"))
                { //popup Action
                    ShowPopup("modalorderaccept");                  
                }               
                else
                {
                    Common.ShowCustomAlert(this.Page, "Failed", "Invalid Order", false, "/Tenant/PendingOrders");
                    return;
                }                                   
            }           
            else
            {
                Common.ShowCustomAlert(this.Page, "Failed", "Invalid Order", false, "/Tenant/PendingOrders");
                return;
            }

        }
        //update the order status
        public bool UpdateOrderStatus(string orderId, string statusId)
        {
            try
            {
                var parameters = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("orderId", orderId),
                    new KeyValuePair<string, object>("statusId", statusId)
                };
                //update the order status
                string query = "UPDATE finascop_stock_transfer_order SET fsto_status=@statusId WHERE fstr_id=@orderId";
                var result = DataServiceMySql.ExecuteScalar(query, Service.UserService.GetAPIConnectionString(), parameters);
                // If the result is not null, return true
                return true;                
            }
            catch (Exception ex)
            {              
                return false;
            }
        }     
        // popup Action
        public void ShowPopup(string popupId)
        {
            string strAlertSCript = $"$('#{popupId}').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";

            System.Type cstype = this.GetType();
            string csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = Page.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type='text/javascript'> {strAlertSCript} </script>");

            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }       
        protected void Btnorderpacknow_Click(object sender, EventArgs e)
        {
            // Manual Packing 
            try
            {
                var row = GetAssignOrder(hdnorderid.Value)?.Rows[0];
                string fstoId = row?["fsto_id"].ToString();
                string orderId = row?["order_order_id"].ToString();
                string orderMethod = hdnordermethod.Value;
                string orderStatus = row?["StatusId"].ToString();
                string statusName = row?["order_status"].ToString();
                string redirectUrl = string.Format("~/Tenant/ManualPacking.aspx?fsto_id={0}&orderId={1}&orderMethod={2}&orderStatus={3}&statusName={4}", fstoId, orderId, orderMethod, orderStatus, statusName);
                Response.Redirect(redirectUrl);
            }
            catch
            {
                return ;
            }
            
        }
        public DataTable GetAssignOrder(string orderId)
        {
            try
            {
                List<KeyValuePair<string, object>> ordrparams = new List<KeyValuePair<string, object>>();
                ordrparams.Add(new KeyValuePair<string, object>("orderId", orderId));
                string sql = $"SELECT order_order_id,fsto_ordertype, fsto_uid,order_order_id,fsto_id,fstr_id,fsto_status,order_id,rc.status_id as StatusId,admin_description AS order_status FROM `finascop_stock_transfer_order` INNER JOIN  retaline_customer_order rc ON fstr_id=order_id LEFT JOIN retaline_customer_order_status bcos ON bcos.status_id = rc.status_id  WHERE order_order_id=@orderId";
                var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString(), ordrparams);
                return tblItems;
            }
            catch
            {
                return null;
            }
        }

        protected void btnfewitemavaliabe_Click(object sender, EventArgs e)
        {            
            DataTable tblItems = GetOrderDetails(hdnorderid.Value);
            if (tblItems != null && tblItems.Rows.Count > 0)
            {
                DataRow row = tblItems.Rows[0];
                ltrfewOrderId.Text = row["order_order_id"].ToString();
                ltrfewitenName.Text = row["cust_customer_name"].ToString();
                ltrfewitenMobile.Text = row["cust_mobile"].ToString();
            }
            ShowPopup("modalrfewitemavaliable");            
        }

        protected void btnNoitmeavalible_Click(object sender, EventArgs e)
        {

            var row = GetAssignOrder(hdnorderid.Value)?.Rows[0];
            string Transferorderid = row?["fsto_id"].ToString();
            string toid = row?["fsto_uid"].ToString();
            string orderId = row?["order_id"].ToString();
            string ordre_orderid= row?["order_order_id"].ToString();
            CancelOrder(orderId, Transferorderid);
            Common.ShowCustomAlert(this.Page, "Order Cancelled Successfully!",
                $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">The order {ordre_orderid} is cancelled successfully!</a></h5>",
                true, "/Tenant/PendingOrders");
        }

        private DataRow GetSingleRowFromQuery(string query, List<KeyValuePair<string, object>> parameters)
        {
            DataTable table = DataServiceMySql.GetDataTable(query, UserService.GetAPIConnectionString(), parameters);
            return table.Rows.Count > 0 ? table.Rows[0] : null;
        }

        private void ExecuteSql(string query, List<KeyValuePair<string, object>> parameters)
        {
            DataServiceMySql.ExecuteSql(query, UserService.GetAPIConnectionString(), parameters);
        }       
        private void UpdateWalletBalance(DataRow data)
        {
            string refentryId = data["order_id"].ToString();
            int paymentMode = data["payment_mode"] != DBNull.Value ? Convert.ToInt32(data["payment_mode"]) : 0;
            string paymentgateway= data["order_payment_gateway"] != DBNull.Value ? (data["order_payment_gateway"]).ToString() : " ";
            double walletAmount = data["order_wallet_amount"] != DBNull.Value ? Convert.ToDouble(data["order_wallet_amount"]) : 0;
            try
            { // refund initialization
                if (paymentMode == 2 || paymentMode == 5)
                {
                    decimal amount = Convert.ToInt32(data["payment_mode"]) == 2 ? Convert.ToDecimal(data["total"]) : (Convert.ToDecimal(data["total"]) - Convert.ToDecimal(data["order_wallet_amount"]));
                    var refundParams = new List<KeyValuePair<string, object>>
                    {
                        new KeyValuePair<string, object>("order_id", refentryId),
                        new KeyValuePair<string, object>("payment_gateway", paymentgateway),
                        new KeyValuePair<string, object>("amount", amount),
                         new KeyValuePair<string, object>("created_at", DateTime.Now)
                    };
                    string refundInsertQuery = "INSERT INTO customer_order_refunds(order_id, payment_gateway, amount,created_at) VALUES(@order_id, @payment_gateway, @amount,@created_at)";
                    DataServiceMySql.ExecuteSql(refundInsertQuery, UserService.GetAPIConnectionString(), refundParams);
                }
            }
            catch
            {
                LogOrderHistory(refentryId, "refund initialization", 53);
            }
            int customerId = Convert.ToInt32(data["order_customer_id"]);
            string addInfo = $"Order {data["order_order_id"]} from {data["branchName"]} cancelled by {this.CurrentUser.StoreGroupName} after clarification with customer.";
            if (paymentMode == 3 || paymentMode == 4 || paymentMode == 5)
            {
                walletupdation(customerId, refentryId, walletAmount, addInfo);
            }
        }
        private void LogOrderHistory(string orderId, string action, int status)
        {
             string query = "INSERT INTO retaline_customer_order_history(order_id, order_action, order_status) VALUES(@orderId, @action, @status)";
                ExecuteSql(query, new List<KeyValuePair<string, object>> 
                {
                    new KeyValuePair<string, object>("orderId", orderId),
                    new KeyValuePair<string, object>("action", action),
                    new KeyValuePair<string, object>("status", status)
               });
        }
        private void UpdateOrderStatusRetalinecustomerorder(string orderId, int status)
        {
            string query = "UPDATE retaline_customer_order SET status_id = @status WHERE order_id = @orderId";
            ExecuteSql(query, new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("status", status),
                new KeyValuePair<string, object>("orderId", orderId)
            });
        }

        private void InsertCancellationDetails(string customerId, string orderId, string reason)
        {
            string query = "INSERT INTO retaline_customer_order_cancellationdets(customer_id, order_id, reason, cancelled_by_type, cancelled_by_id) " +
                           "VALUES(@customerId, @orderId, @reason, @cancelledByType, @cancelledById)";
            ExecuteSql(query, new List<KeyValuePair<string, object>> 
            {
                new KeyValuePair<string, object>("customerId", customerId),
                new KeyValuePair<string, object>("orderId", orderId),
                new KeyValuePair<string, object>("reason", reason),
                new KeyValuePair<string, object>("cancelledByType", 3),
                new KeyValuePair<string, object>("cancelledById", this.CurrentUser.Id)
            });
        }

        private void UpdateStockTransferOrder(string orderId, int status, int updatedBy)
        {
            string query = "UPDATE finascop_stock_transfer_order SET fsto_status = @status, fsto_updateby = @updatedBy WHERE fsto_id = @transferOrdId";
            ExecuteSql(query, new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("status", status),
                new KeyValuePair<string, object>("updatedBy", updatedBy),
                new KeyValuePair<string, object>("transferOrdId", orderId)
            });
        }

        //private void UpdateBarcodeStatus(string orderId, int status)
        //{
        //    string query = "UPDATE finascop_stock_transfer_order_details_barcodes_temp SET rpb_status = @status WHERE tmp_barcode_fstoId = @transferOrdId";
        //    ExecuteSql(query, new List<KeyValuePair<string, object>> 
        //    {
        //        new KeyValuePair<string, object>("status", status),
        //        new KeyValuePair<string, object>("transferOrdId", orderId)
        //    });
        //}

        private void TriggerOrderCancellationAPI(string orderId)
        {
            string url = ConfigurationSettings.AppSettings.Get("api.url") ?? "http://bizapi.dev.grozeo.in";
            var client = new RestClient(new RestClientOptions(url));
            var request = new RestRequest("/api/finascop/finascopPostingService", Method.Post)
                .AddParameter("order_id", orderId)
                .AddParameter("finascopEventRefId", "078025ad-38d7-11ee-9967-065723bafb24")
                .AddParameter("storegroup_id", this.CurrentUser.APIStoreId);
            client.ExecuteAsync(request).Wait();
        }
        #region walletupdation 
        private void walletupdation(int customerId, string refentryId, double refundAmt, string addInfo)
        {
            try
            {
                var prms = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("customerId", customerId),
                    new KeyValuePair<string, object>("order_id", refentryId),
                    new KeyValuePair<string, object>("brcw_SourceType", 1),
                };
                // Get closing balance
                string balanceQuery = "SELECT brcw_closingBalance FROM retaline_customer_wallet_transaction WHERE cust_id = @customerId ORDER BY brcw_id DESC LIMIT 1";
                DataTable openBalResult = DataServiceMySql.GetDataTable(balanceQuery, UserService.GetAPIConnectionString(), prms);
                decimal openingBalance = (openBalResult.Rows.Count > 0 && openBalResult.Rows[0]["brcw_closingBalance"] != DBNull.Value) ? Convert.ToDecimal(openBalResult.Rows[0]["brcw_closingBalance"]) : 0;

                // Check if wallet transaction already exists
                string checkQuery = "SELECT 1 FROM retaline_customer_wallet_transaction WHERE cust_id = @customerId AND refentry_id = @order_id AND brcw_SourceType = @brcw_SourceType LIMIT 1";
                bool transactionExists = DataServiceMySql.GetDataTable(checkQuery, UserService.GetAPIConnectionString(), prms).Rows.Count > 0;                          
                if (!transactionExists)
                {     // Insert wallet transaction
                    string insertTxnQuery = " INSERT INTO retaline_customer_wallet_transaction(cust_id, refentry_id, brcw_SourceType, brcw_Amount, brcw_AddInfo, stiid_barcode, brcw_OpeningBalance) VALUES (@customer_id, @order_id, @source_type, @amount, @information, @barcode, @openingBalance)";
                    var additionalprms = new List<KeyValuePair<string, object>>
                    {
                         new KeyValuePair<string, object>("customer_id", customerId),
                         new KeyValuePair<string, object>("order_id", refentryId),
                         new KeyValuePair<string, object>("source_type", 1),
                         new KeyValuePair<string, object>("amount", refundAmt),
                         new KeyValuePair<string, object>("information", addInfo),
                         new KeyValuePair<string, object>("barcode", 0),
                         new KeyValuePair<string, object>("openingBalance", openingBalance),
                    };
                    int inserted = DataServiceMySql.ExecuteSql(insertTxnQuery, UserService.GetAPIConnectionString(), additionalprms);
                    if (inserted > 0)
                    {
                        // Update customer wallet balance in customer table 
                        string updateWalletQuery = "UPDATE retaline_customer SET cust_walletbalance = (cust_walletbalance + @amount)  WHERE cust_id = @customer_id";

                        var walletprms = new List<KeyValuePair<string, object>>
                        {
                             new KeyValuePair<string, object>("customer_id", customerId),
                             new KeyValuePair<string, object>("order_id", refentryId),
                             new KeyValuePair<string, object>("brcw_SourceType", 1),
                             new KeyValuePair<string, object>("amount", refundAmt),

                        };
                        DataServiceMySql.ExecuteSql(updateWalletQuery, UserService.GetAPIConnectionString(), walletprms);
                    }
                    var row = GetOrderDetails(hdnorderid.Value)?.Rows[0];
                    string mobile = row?["cust_mobile"]?.ToString();
                    Dictionary<string, string> additionalValues = new Dictionary<string, string>();
                    additionalValues.Add("amount", refundAmt.ToString());
                    Core.Services.APIService.GetOtp(mobile, storegroupid: this.CurrentUser.APIStoreId, templateid: 29, additionalParams: additionalValues);
                }
            }
            catch
            {

            }
        }
        #endregion
        protected void gvManualPacking_DataBound(object sender, EventArgs e)
        {

            if (gvManualPacking.Columns.Count > 3)
            {
                gvManualPacking.Columns[4].Visible = (incompleteOrderValue == "1");
            }
        }

        protected void btnproceed_Click(object sender, EventArgs e)
        {
            ShowPopup("PopupManualpackingDetalies");
            var row = GetOrderDetails(hdnorderid.Value)?.Rows[0];
            ltrcustomername.Text= row?["cust_customer_name"]?.ToString();
            ltrcustomerContact.Text = row?["cust_mobile"]?.ToString();
        }

        protected void btnyes_Click(object sender, EventArgs e)
        {
            var row = GetAssignOrder(hdnorderid.Value)?.Rows[0];
            string transferOrderId = row?["fsto_id"].ToString();
            string orderid = row?["order_id"].ToString();
            List<KeyValuePair<string, object>> stsparams = new List<KeyValuePair<string, object>>();
            stsparams.Add(new KeyValuePair<string, object>("transOrdId", transferOrderId));
            DataTable fstoStatusTbl = DataServiceMySql.GetDataTable($"SELECT SUM(fsto_pkdQty) AS total_packed_qty FROM finascop_stock_transfer_order_details WHERE fsto_id = @transOrdId", UserService.GetAPIConnectionString(), stsparams);
            if (fstoStatusTbl.Rows.Count > 0)
            {
                DataRow getrow = fstoStatusTbl.Rows[0];
                decimal totalPackedQty = getrow["total_packed_qty"] != DBNull.Value ? Convert.ToDecimal(getrow["total_packed_qty"]) : 0;
                if(totalPackedQty > 0)
                {
                    List<KeyValuePair<string, object>> toparams = new List<KeyValuePair<string, object>>();
                    toparams.Add(new KeyValuePair<string, object>("transOrdId", transferOrderId));
                    toparams.Add(new KeyValuePair<string, object>("fsto_status", 4));
                    toparams.Add(new KeyValuePair<string, object>("updatedBy", 1));
                    string updateQry = "UPDATE finascop_stock_transfer_order SET fsto_status=@fsto_status,fsto_updateby=@updatedBy WHERE fsto_id=@transOrdId";
                    DataServiceMySql.ExecuteSql(updateQry, Service.UserService.GetAPIConnectionString(), toparams);
                    if (Convert.ToInt32(row?["fsto_ordertype"])==1)
                    {
                        var cutparams = new List<KeyValuePair<string, object>>
                        {
                            new KeyValuePair<string, object>("requestId", orderid),
                            new KeyValuePair<string, object>("statusId", 7)
                        };
                        // Update status
                        string updateQuery = "UPDATE retaline_customer_order SET status_id=@statusId WHERE order_id=@requestId";
                        DataServiceMySql.ExecuteSql(updateQuery, Service.UserService.GetAPIConnectionString(), cutparams);
                        // Update order history
                        cutparams.Add(new KeyValuePair<string, object>("action", "Proceed with aval Qty"));
                        cutparams.Add(new KeyValuePair<string, object>("createdBy", 1));
                        string insertQry = "INSERT INTO retaline_customer_order_history(order_id, order_action, order_status) " +
                                           "VALUES(@requestId, @action, @statusId)";
                        DataServiceMySql.ExecuteSql(insertQry, Service.UserService.GetAPIConnectionString(), cutparams);

                    }
                    //Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = this.CurrentUser.APIStoreId;
                    string Users = this.CurrentUser.Email;
                    string storegroup_id = (this.CurrentUser.APIStoreId).ToString();
                    string transferOrdId = transferOrderId.ToString();
                    string userId = "0";
                    string barcode = "0";
                    string createdBy = "1";
                    string refentry_Id = transferOrderId;
                    var items = new[]
                    {
                                     new { Key = "Storegroup Id", Value = storegroup_id },
                                     new { Key = "Transfer Order Id", Value = transferOrdId },
                                     new { Key = "User Id", Value = userId },
                                     new { Key = "Barcode", Value = barcode },
                                     new { Key = "createdBy", Value = createdBy },
                                    };
                    string Description = string.Join(", ", items.Select(Item => $"{Item.Key}={Item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
                    Common.ShowCustomAlert(this.Page, "Proceed!", "Proceeded with available quantity!", true, "/Tenant/PendingOrders");
                }
            }          
            else
            {
                Common.ShowToastifyMessage(this.Page, "Sorry, No items picked for this order", "danger");
            }
        }
        #region ordercancel
        private void CancelOrder(string orderId, string transferOrderId)
        {

            try
            {
                // Step 1: Retrieve order details from the database
                DataRow customerOrderData = GetSingleRowFromQuery(
                    "SELECT order_id, order_order_id, order_customer_id, status_id, order_branch_id,order_payment_gateway, payment_mode, order_wallet_amount, total, " +
                    "(SELECT br_Name FROM finascop_branch WHERE br_ID = order_branch_id) AS branchName " +
                    "FROM retaline_customer_order WHERE order_id = @requestId",
                    new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("requestId", orderId) });              
                // Step 3: Remove stock blocking related to the cancelled order
                ExecuteSql("DELETE FROM finascop_stock_blocked WHERE order_id = @orderId",
                    new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("orderId", customerOrderData["order_id"].ToString()) });

                // Step 4: Update the customer's wallet balance with the refund amount              
                UpdateWalletBalance(customerOrderData);

                // Step 5: Log the order cancellation event
                string userId = Convert.ToString(this.CurrentUser.Id);
                string userAction = $"Cancelled by {userId}.";
                LogOrderHistory(orderId, userAction, 19);

                // Step 6: Update order status to 'Cancelled'
                UpdateOrderStatusRetalinecustomerorder(orderId, 19);

                // Step 7: Insert cancellation details for tracking
                InsertCancellationDetails(customerOrderData["order_customer_id"].ToString(), customerOrderData["order_id"].ToString(), "From Incomplete Orders");

                // Step 8: Update stock transfer order details to reflect cancellation
                UpdateStockTransferOrder(transferOrderId, 15, 1);
               

                // Step 9: Trigger API call to notify the cancellation event
                TriggerOrderCancellationAPI(orderId);
            }
            catch
            {
                // Step 15: Handle any errors that occur during the process
                Common.ShowToastifyMessage(this.Page, "Execution failed. Please contact support.", "danger");
            }           
        }
        #endregion
        protected void btnNo_Click(object sender, EventArgs e)
        {
            var row = GetAssignOrder(hdnorderid.Value)?.Rows[0];
            string Transferorderid = row?["fsto_id"].ToString();
            string toid = row?["fsto_uid"].ToString();
            string orderId = row?["order_id"].ToString();
            string ordre_orderid = row?["order_order_id"].ToString();
            CancelOrder(orderId, Transferorderid);
            Common.ShowCustomAlert(this.Page, $"Order ID : {ordre_orderid} has been Successfully cancelled!",
                "<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Please rerack your available items.!</a></h5>",
                true, "/Tenant/PendingOrders");
        }
        // few item avalible button click
        protected void btnSubmit_Click(object sender, EventArgs e)
        {
            try
            {
                string transferOrderId = hdnfstoid.Value;
                List<KeyValuePair<string, object>> toparams = new List<KeyValuePair<string, object>>();
                toparams.Add(new KeyValuePair<string, object>("transferOrdId", transferOrderId));
                toparams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));

                var dtOrderItems = DataServiceMySql.GetDataTable($"SELECT fstod.fsto_id,fstod.fsto_uid, fstod.fstod_id, fstod.fsto_ItemId, fsto.fsto_updateon,fstod.fsto_pkdQty,fstod.fsto_stockValue,fstod.fsto_ItemQty,fsto.fsto_isalreadypacked " +
                    $"FROM finascop_stock_transfer_order fsto INNER JOIN  finascop_stock_transfer_order_details fstod ON fstod.fsto_id = fsto.fsto_id " +
                    $"INNER JOIN retaline_customer_order o ON o.order_id = fsto.fstr_id INNER JOIN finascop_branch b ON b.br_ID=o.order_branch_id " +
                    $" WHERE fsto.fsto_id= @transferOrdId", UserService.GetAPIConnectionString(), toparams);

                if (dtOrderItems == null || dtOrderItems.Rows.Count <= 0)
                {
                    Common.ShowCustomAlert(this.Page, "Failure", "No item matching with the order in system. Please contact support for more details.", false, "/Tenant/PendingOrders");
                    return;
                }

                string numberofBags = "";
                string invoiceNumber = "";
                string invoiceAmt = "";
                string formattedDate = "";
                numberofBags = string.IsNullOrEmpty(numberofBags) ? "0" : numberofBags;
                int noofbags = int.TryParse(numberofBags, out int bags) ? bags : 0;
                invoiceNumber = string.IsNullOrEmpty(invoiceNumber) ? "0" : invoiceNumber;
                double invoicdeAmount = double.TryParse(invoiceAmt, out double amount) ? amount : 0;
                formattedDate = string.IsNullOrEmpty(formattedDate) ? "0" : formattedDate;
                DateTime invoiceDate;

                //int itemids = 0;
                int packQuantity = 0;
                string fsto_uid = null;
                int fsid = 0;
                string fsto_updateon = null;
                var itemsList = new List<Dictionary<string, object>>();
                decimal itemQtyy = 0, packQty = 0;
                int isAlreadyPacked = 0;
                Boolean allIsPacked = true;
                foreach (GridViewRow gr in gvfewitenavaliable.Rows)
                {
                    TextBox txtQty = (TextBox)gr.FindControl("txtUpdate");
                    TextBox txtSubPrd = (TextBox)gr.FindControl("txtSubPrd");
                    string subProduct = Convert.ToString(txtSubPrd.Text);
                    decimal txtPackSub = 0;
                    txtPackSub = string.IsNullOrEmpty(subProduct) ? 0 : Convert.ToDecimal(subProduct);
                    if (String.IsNullOrEmpty(txtQty.Attributes["prodid"]))
                        continue;
                    string strPId = txtQty.Attributes["prodid"];
                    var dr = dtOrderItems.AsEnumerable().Where(r => r["fstod_id"].ToString() == strPId).FirstOrDefault();

                    string fsidd = dr["fsto_id"].ToString();
                    fsid = Convert.ToInt32(fsidd);
                    string item_Id = dr["fsto_ItemId"].ToString();
                    string packedQuantity = txtQty.Text;
                    packQty = Convert.ToDecimal(packedQuantity);
                    fsto_uid = Convert.ToString(dr["fsto_uid"]);
                    fsto_updateon = Convert.ToString(dr["fsto_updateon"]);
                    itemQtyy = Convert.ToInt32(dr["fsto_ItemQty"]);
                    isAlreadyPacked = Convert.ToInt32(dr["fsto_isalreadypacked"]);
                    string updatequery1 = $"UPDATE finascop_stock_transfer_order SET fsto_ismanualpacking = 1 WHERE fsto_id = @transferOrdId";
                    DataServiceMySql.ExecuteSql(updatequery1, UserService.GetAPIConnectionString(), toparams);
                    itemsList.Add(new Dictionary<string, object> {
                            {"item_id", Convert.ToInt32(item_Id) },
                            {"count", Convert.ToDecimal(packedQuantity) },
                            {"fsto_stockValue", Convert.ToDecimal(txtPackSub) }
                        });

                    if (itemsList == null || itemsList.Count <= 0)
                    {
                        Common.ShowCustomAlert(this.Page, "Failure", "Execution failure No item matches the criteria. Please contact support for more details.", false, "/Tenant/PendingOrders");
                        return;
                    }
                    if (packQty < itemQtyy)
                    {
                        allIsPacked = false;
                    }
                }
                if (!allIsPacked && isAlreadyPacked == 0)
                {
                    string result = Core.Services.APIService.ForceSubmit(fsto_uid, fsid, formattedDate, invoiceNumber, invoicdeAmount, noofbags, fsto_updateon, itemsList);
                    if (result == "ok")
                    {
                        Common.ShowCustomAlert(this.Page, "Successfully moved!", "Your item/s moved to Onhold order!", true, "/Tenant/PendingOrders");
                    }
                }
                else
                {
                    Common.ShowCustomAlert(this.Page, "Failed!", "Your item(s) failed to move to the Onhold order!", true, "/Tenant/PendingOrders");
                }

            }
            catch
            {
                Common.ShowCustomAlert(this.Page, "Failed!", "Your item(s) failed to move to the Onhold order!", true, "/Tenant/PendingOrders");
            }
        }

        protected void Btnordereassign_Click(object sender, EventArgs e)
        {
            ShowPopup("modalAssignorderpicker");
        }
    }

}