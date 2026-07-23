using Finascop.BussinessModel;
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
using RestSharp;
using System.Configuration;

namespace RetalineProAgent
{
    public partial class AssignOrdPicker: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
        }
        //protected void SDSOnlineOrders_Selected(object sender, SqlDataSourceStatusEventArgs e)
        //{
        //    e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
        //    int startRowOnPage = (gvOnlineOrders.PageIndex * gvOnlineOrders.PageSize) + 1;
        //    int lastRowOnPage = startRowOnPage + gvOnlineOrders.Rows.Count - 1;
        //    int totalRows = e.AffectedRows;

        //    ltrPageCurStart.Text = startRowOnPage.ToString();
        //    ltrPageCurTotal.Text = lastRowOnPage.ToString();
        //    ltrPageTotal.Text = totalRows.ToString();
        //}

        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvOrderPicker.PageIndex > 0)
                gvOrderPicker.PageIndex = gvOrderPicker.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvOrderPicker.PageIndex < gvOrderPicker.PageCount - 1)
                gvOrderPicker.PageIndex = gvOrderPicker.PageIndex + 1;
        }

        protected void gvOrderPicker_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvOrderPicker.PageIndex * gvOrderPicker.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvOrderPicker.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSOrderPickers.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSOrderPickers_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }
        //protected void btnOn_Click(object sender, EventArgs e)
        //{



        //}

        //protected void btnOff_Click(object sender, EventArgs e)
        //{

        //}

        //protected void btnSelect_Click(object sender, EventArgs e)
        //{
        //    Button btn = (Button)sender;
        //    //1: red   0:green
        //    if (btn.CommandName == "1")
        //    {
        //        string strSq1l = $"UPDATE retaline_godown_boy SET STATUS=1 WHERE id = '45'";
        //        DataServiceMySql.ExecuteSql(strSq1l, UserService.GetAPIConnectionString());
        //    }
        //    else
        //    {
        //        //change green to red
        //        string strSql2 = $"UPDATE retaline_godown_boy SET STATUS=0 WHERE id = '45'";
        //        DataServiceMySql.ExecuteSql(strSql2, UserService.GetAPIConnectionString());
        //    }

        //}

        protected void chkStatus_CheckedChanged(object sender, EventArgs e)
        {

            CheckBox chbtn = (CheckBox)sender;
            //int status = chbtn.Checked ? 1 : 0;
            int storegroupid = this.CurrentUser.APIStoreId;
            var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            foreach (DataRow dr in dtBranches.Rows)
            {
                string brId = dr["br_ID"].ToString();
                var strid = DataServiceMySql.GetDataTable($"SELECT id FROM retaline_godown_boy WHERE branch_id = {brId}", UserService.GetAPIConnectionString());
                foreach (DataRow da in strid.Rows)
                {
                    string id = da["id"].ToString();
                    //int id = 0;
                    if (chbtn.Checked)
                    {
                        string strSq1Updatel = $"UPDATE retaline_godown_boy SET status=1 WHERE id = '" + id + "'";
                        DataServiceMySql.ExecuteSql(strSq1Updatel, UserService.GetAPIConnectionString());
                    }
                    else
                    {
                        string strSq1Update2 = $"UPDATE retaline_godown_boy SET status=0 WHERE id = '" + id + "'";
                        DataServiceMySql.ExecuteSql(strSq1Update2, UserService.GetAPIConnectionString());
                    }
                }
            }
        }

        protected void btnAdd_Click(object sender, EventArgs e)
        {
            Button btnAssign = (Button)sender;
            if (btnAssign == null || String.IsNullOrEmpty(btnAssign.Attributes["orderpickerid"]))
            {
                // show error
                return;
            }
            int branchid = Convert.ToInt32(btnAssign.Attributes["branchid"]);
            int storegroupid = this.CurrentUser.APIStoreId;

            string orderPIckerId = Convert.ToString(btnAssign.Attributes["orderpickerid"]);
            //string orderNum = Request.QueryString["ordernum"];

            string transferOrderId = Convert.ToString(Request.QueryString["toid"]);
            //string fstr = Convert.ToString(Request.QueryString["orderid"]);
            int orderId = Convert.ToInt32(Request.QueryString["orderid"]);

            string result = Core.Services.APIService.AssignOrderPicker(transferOrderId, orderId, orderPIckerId, branchid, storegroupid);
            int statusResult = Convert.ToInt32(result);
            // show result as status.
            if (statusResult == 0)
            {
                try
                {
                    List<KeyValuePair<string, object>> orderparams = new List<KeyValuePair<string, object>>();
                    orderparams.Add(new KeyValuePair<string, object>("trforderId", orderId));
                    DataTable requestIdTbl = DataServiceMySql.GetDataTable($"SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = @trforderId", UserService.GetAPIConnectionString(), orderparams);
                    DataRow dr = requestIdTbl.Rows[0];

                    List<KeyValuePair<string, object>> reqparams = new List<KeyValuePair<string, object>>();
                    reqparams.Add(new KeyValuePair<string, object>("requestId", dr["fstr_id"].ToString()));
                    DataTable custOrdTbl = DataServiceMySql.GetDataTable($"SELECT order_id,order_order_id,order_isB2b,order_customer_id,status_id,order_branch_id,payment_mode,order_wallet_amount,total FROM retaline_customer_order WHERE order_id = @requestId", UserService.GetAPIConnectionString(), reqparams);
                    DataRow da = custOrdTbl.Rows[0];

                    List<KeyValuePair<string, object>> ordparams = new List<KeyValuePair<string, object>>();
                    ordparams.Add(new KeyValuePair<string, object>("orderId", da["order_id"].ToString()));
                    DataTable custOrderTbl = DataServiceMySql.GetDataTable($"SELECT order_customer_id,order_branch_id,payment_mode,order_wallet_amount,total, order_branch_id, (SELECT br_Name FROM finascop_branch WHERE br_ID=order_branch_id) AS branchName FROM retaline_customer_order WHERE order_id = @orderId", UserService.GetAPIConnectionString(), ordparams);
                    DataRow db = custOrderTbl.Rows[0];

                    DataTable custItemTbl = DataServiceMySql.GetDataTable($"SELECT item_product_id,item_id,item_retail_price,item_sales_price,item_order_qty FROM retaline_customer_order_items WHERE customer_order_id = @orderId", UserService.GetAPIConnectionString(), ordparams);
                    DataRow dc = custItemTbl.Rows[0];


                    string deleteSql = $"DELETE FROM finascop_stock_blocked WHERE order_id = @orderId";
                    int status = DataServiceMySql.ExecuteSql(deleteSql, UserService.GetAPIConnectionString(), ordparams);

                    double refundamt = ((Convert.ToDouble(db["payment_mode"])) == 2 || (Convert.ToDouble(db["payment_mode"])) == 5 ? (Convert.ToDouble(db["total"])) : (Convert.ToDouble(db["order_wallet_amount"])));

                    List<KeyValuePair<string, object>> custparams = new List<KeyValuePair<string, object>>();
                    custparams.Add(new KeyValuePair<string, object>("orderCustId", db["order_customer_id"].ToString()));
                    custparams.Add(new KeyValuePair<string, object>("refundAmt", refundamt));
                    //string updQry = "UPDATE retaline_customer SET cust_walletbalance = cust_walletbalance + @refundAmt WHERE cust_id=@orderCustId";
                    //DataServiceMySql.ExecuteSql(updQry, UserService.GetAPIConnectionString(), custparams);
                    string addMessage = "Order" + " " + da["order_order_id"].ToString() + " " + "from" + " " + db["branchName"].ToString() + " " + "cancelled by" + " " + this.CurrentUser.StoreGroupName + " " + "after clarification with customer due to item(s) unavailability";
                    List<KeyValuePair<string, object>> walletparams = new List<KeyValuePair<string, object>>();
                    walletparams.Add(new KeyValuePair<string, object>("customerId", db["order_customer_id"].ToString()));
                    walletparams.Add(new KeyValuePair<string, object>("orderId", da["order_id"].ToString()));
                    walletparams.Add(new KeyValuePair<string, object>("sourceType", 1));
                    walletparams.Add(new KeyValuePair<string, object>("refundAmt", refundamt));
                    walletparams.Add(new KeyValuePair<string, object>("storeName", this.CurrentUser.StoreGroupName));
                    walletparams.Add(new KeyValuePair<string, object>("createdOn", DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss")));
                    walletparams.Add(new KeyValuePair<string, object>("updatedOn", DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss")));
                    walletparams.Add(new KeyValuePair<string, object>("addInfo", addMessage));
                    walletparams.Add(new KeyValuePair<string, object>("barCode", 0));
                    //string insertQry = $"INSERT INTO retaline_customer_wallet_transaction(cust_id, refentry_id, brcw_SourceType, brcw_Amount, brcw_AddInfo, stiid_barcode, brcw_CreatedOn, brcw_Updateon) " +
                    //                    $"VALUES(@customerId, @orderId, @sourceType, @refundAmt, @addInfo, @barCode, @createdOn, @updatedOn)";
                    //var stsresult = DataServiceMySql.ExecuteScalar(insertQry, UserService.GetAPIConnectionString(), walletparams);

                    string refentryId = da["order_id"].ToString();
                    int customerId = Convert.ToInt32(db["order_customer_id"]);
                    double amount = refundamt;
                    string addInfo = addMessage;
                    string walletResult = Core.Services.APIService.WalletBalance(customerId, refentryId, amount, addInfo);


                    string custOrderId = da["order_order_id"].ToString();

                    List<KeyValuePair<string, object>> histparams = new List<KeyValuePair<string, object>>();
                    histparams.Add(new KeyValuePair<string, object>("orderId", da["order_id"].ToString()));
                    histparams.Add(new KeyValuePair<string, object>("action", "Cancelled by Admin user."));
                    histparams.Add(new KeyValuePair<string, object>("status", 19));
                    string inrtQry = $"INSERT INTO retaline_customer_order_history(order_id, order_action, order_status) " +
                                        $"VALUES(@orderId, @action, @status)";
                    var histresult = DataServiceMySql.ExecuteScalar(inrtQry, UserService.GetAPIConnectionString(), histparams);

                    List<KeyValuePair<string, object>> ordrparams = new List<KeyValuePair<string, object>>();
                    ordrparams.Add(new KeyValuePair<string, object>("status", 19));
                    ordrparams.Add(new KeyValuePair<string, object>("orderId", da["order_id"].ToString()));
                    string updtQry = "UPDATE retaline_customer_order SET status_id = @status WHERE order_id = @orderId";
                    DataServiceMySql.ExecuteSql(updtQry, UserService.GetAPIConnectionString(), ordrparams);

                    List<KeyValuePair<string, object>> dateparams = new List<KeyValuePair<string, object>>();
                    dateparams.Add(new KeyValuePair<string, object>("customerId", db["order_customer_id"].ToString()));
                    dateparams.Add(new KeyValuePair<string, object>("orderId", da["order_id"].ToString()));
                    dateparams.Add(new KeyValuePair<string, object>("reason", "From Incomplete Orders"));
                    dateparams.Add(new KeyValuePair<string, object>("cancelledByType", 3));
                    dateparams.Add(new KeyValuePair<string, object>("cancelledById", this.CurrentUser.Id));
                    string inrtQury = $"INSERT INTO retaline_customer_order_cancellationdets(customer_id, order_id, reason, cancelled_by_type, cancelled_by_id) " +
                                        $"VALUES(@customerId, @orderId, @reason, @cancelledByType, @cancelledById)";
                    var dateresult = DataServiceMySql.ExecuteScalar(inrtQury, UserService.GetAPIConnectionString(), dateparams);

                    List<KeyValuePair<string, object>> stoparams = new List<KeyValuePair<string, object>>();
                    stoparams.Add(new KeyValuePair<string, object>("status", 15));
                    stoparams.Add(new KeyValuePair<string, object>("updatedBy", 1));
                    stoparams.Add(new KeyValuePair<string, object>("transferOrdId", orderId));
                    string updtQuery = "UPDATE finascop_stock_transfer_order SET fsto_status = @status, fsto_updateby = @updatedBy WHERE fsto_id = @transferOrdId";
                    DataServiceMySql.ExecuteSql(updtQuery, UserService.GetAPIConnectionString(), stoparams);

                    List<KeyValuePair<string, object>> stparams = new List<KeyValuePair<string, object>>();
                    stparams.Add(new KeyValuePair<string, object>("status", 3));
                    stparams.Add(new KeyValuePair<string, object>("transferOrdId", orderId));
                    string updQuery = "UPDATE finascop_stock_transfer_order_details_barcodes_temp SET rpb_status = @status WHERE tmp_barcode_fstoId = @transferOrdId";
                    DataServiceMySql.ExecuteSql(updQuery, UserService.GetAPIConnectionString(), stparams);
                    ShowSuccess("Order Cancelled Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">The order {custOrderId} is cancelled successfully!</a></h5>");
                }
                catch
                {
                    Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
                }
            }
            else
            {
                Common.ShowToastifyMessage(this.Page, "Failure! Error while saving data.");
            }
            ShowSuccess("Assigned Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Order picker has been assigned successfully!</a></h5>");
        }

        protected void btnReplenish_Click(object sender, EventArgs e)
        {
            string transferOrderId = Convert.ToString(Request.QueryString["toid"]);
            string orderId = Convert.ToString(Request.QueryString["orderid"]);
            List<KeyValuePair<string, object>> toparams = new List<KeyValuePair<string, object>>();
            toparams.Add(new KeyValuePair<string, object>("orderId", orderId));
            toparams.Add(new KeyValuePair<string, object>("manualreplenuser", 0));
            string updateQry = "UPDATE finascop_stock_transfer_order SET fsto_manualreplenuser=@manualreplenuser WHERE fsto_id=@orderId";
            DataServiceMySql.ExecuteSql(updateQry, Service.UserService.GetAPIConnectionString(), toparams);
            string result = Core.Services.APIService.SubmitManualReplenish(orderId);
            if (result == "ok")
            {
                try
                {
                    List<KeyValuePair<string, object>> orderparams = new List<KeyValuePair<string, object>>();
                    orderparams.Add(new KeyValuePair<string, object>("trforderId", orderId));
                    DataTable requestIdTbl = DataServiceMySql.GetDataTable($"SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = @trforderId", UserService.GetAPIConnectionString(), orderparams);
                    DataRow dr = requestIdTbl.Rows[0];

                    List<KeyValuePair<string, object>> reqparams = new List<KeyValuePair<string, object>>();
                    reqparams.Add(new KeyValuePair<string, object>("requestId", dr["fstr_id"].ToString()));
                    DataTable custOrdTbl = DataServiceMySql.GetDataTable($"SELECT order_id,order_order_id,order_isB2b,order_customer_id,status_id,order_branch_id,payment_mode,order_wallet_amount,total FROM retaline_customer_order WHERE order_id = @requestId", UserService.GetAPIConnectionString(), reqparams);
                    DataRow da = custOrdTbl.Rows[0];

                    List<KeyValuePair<string, object>> ordparams = new List<KeyValuePair<string, object>>();
                    ordparams.Add(new KeyValuePair<string, object>("orderId", da["order_id"].ToString()));
                    DataTable custOrderTbl = DataServiceMySql.GetDataTable($"SELECT order_customer_id,order_branch_id,order_branch_id, (SELECT br_Name FROM finascop_branch WHERE br_ID=order_branch_id) AS branchName,payment_mode,order_wallet_amount,total FROM retaline_customer_order WHERE order_id = @orderId", UserService.GetAPIConnectionString(), ordparams);
                    DataRow db = custOrderTbl.Rows[0];

                    DataTable custItemTbl = DataServiceMySql.GetDataTable($"SELECT item_product_id,item_id,item_retail_price,item_sales_price,item_order_qty FROM retaline_customer_order_items WHERE customer_order_id = @orderId", UserService.GetAPIConnectionString(), ordparams);
                    DataRow dc = custItemTbl.Rows[0];


                    string deleteSql = $"DELETE FROM finascop_stock_blocked WHERE order_id = @orderId";
                    int status = DataServiceMySql.ExecuteSql(deleteSql, UserService.GetAPIConnectionString(), ordparams);

                    double refundamt = ((Convert.ToDouble(db["payment_mode"])) == 2 || (Convert.ToDouble(db["payment_mode"])) == 5 ? (Convert.ToDouble(db["total"])) : (Convert.ToDouble(db["order_wallet_amount"])));

                    List<KeyValuePair<string, object>> custparams = new List<KeyValuePair<string, object>>();
                    custparams.Add(new KeyValuePair<string, object>("orderCustId", db["order_customer_id"].ToString()));
                    custparams.Add(new KeyValuePair<string, object>("refundAmt", refundamt));
                    //string updQry = "UPDATE retaline_customer SET cust_walletbalance = cust_walletbalance + @refundAmt WHERE cust_id=@orderCustId";
                    //DataServiceMySql.ExecuteSql(updQry, UserService.GetAPIConnectionString(), custparams);
                    string addMessage = "Order" + " " + da["order_order_id"].ToString() + " " + "from" + " " + db["branchName"].ToString() + " " + "cancelled by" + " " + this.CurrentUser.StoreGroupName + " " + "after clarification with customer due to item(s) unavailability";
                    List<KeyValuePair<string, object>> walletparams = new List<KeyValuePair<string, object>>();
                    walletparams.Add(new KeyValuePair<string, object>("customerId", db["order_customer_id"].ToString()));
                    walletparams.Add(new KeyValuePair<string, object>("orderId", da["order_id"].ToString()));
                    walletparams.Add(new KeyValuePair<string, object>("sourceType", 1));
                    walletparams.Add(new KeyValuePair<string, object>("refundAmt", refundamt));
                    walletparams.Add(new KeyValuePair<string, object>("createdOn", DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss")));
                    walletparams.Add(new KeyValuePair<string, object>("updatedOn", DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss")));
                    walletparams.Add(new KeyValuePair<string, object>("addInfo", addMessage));
                    walletparams.Add(new KeyValuePair<string, object>("barCode", 0));
                    //string insertQry = $"INSERT INTO retaline_customer_wallet_transaction(cust_id, refentry_id, brcw_SourceType, brcw_Amount, brcw_AddInfo, stiid_barcode, brcw_CreatedOn, brcw_Updateon) " +
                    //                    $"VALUES(@customerId, @orderId, @sourceType, @refundAmt, @addInfo, @barCode, @createdOn, @updatedOn)";
                    //var stsresult = DataServiceMySql.ExecuteScalar(insertQry, UserService.GetAPIConnectionString(), walletparams);

                    string refentryId = da["order_id"].ToString();
                    int customerId = Convert.ToInt32(db["order_customer_id"]);
                    double amount = refundamt;
                    string addInfo = addMessage;
                    string walletResult = Core.Services.APIService.WalletBalance(customerId, refentryId, amount, addInfo);

                    string custOrderId = da["order_order_id"].ToString();

                    List<KeyValuePair<string, object>> histparams = new List<KeyValuePair<string, object>>();
                    histparams.Add(new KeyValuePair<string, object>("orderId", da["order_id"].ToString()));
                    histparams.Add(new KeyValuePair<string, object>("action", "Cancelled by Admin user."));
                    histparams.Add(new KeyValuePair<string, object>("status", 19));
                    string inrtQry = $"INSERT INTO retaline_customer_order_history(order_id, order_action, order_status) " +
                                        $"VALUES(@orderId, @action, @status)";
                    var histresult = DataServiceMySql.ExecuteScalar(inrtQry, UserService.GetAPIConnectionString(), histparams);

                    List<KeyValuePair<string, object>> ordrparams = new List<KeyValuePair<string, object>>();
                    ordrparams.Add(new KeyValuePair<string, object>("status", 19));
                    ordrparams.Add(new KeyValuePair<string, object>("orderId", da["order_id"].ToString()));
                    string updtQry = "UPDATE retaline_customer_order SET status_id = @status WHERE order_id = @orderId";
                    DataServiceMySql.ExecuteSql(updtQry, UserService.GetAPIConnectionString(), ordrparams);

                    List<KeyValuePair<string, object>> dateparams = new List<KeyValuePair<string, object>>();
                    dateparams.Add(new KeyValuePair<string, object>("customerId", db["order_customer_id"].ToString()));
                    dateparams.Add(new KeyValuePair<string, object>("orderId", da["order_id"].ToString()));
                    dateparams.Add(new KeyValuePair<string, object>("reason", "From Incomplete Orders"));
                    dateparams.Add(new KeyValuePair<string, object>("cancelledByType", 3));
                    dateparams.Add(new KeyValuePair<string, object>("cancelledById", this.CurrentUser.Id));
                    string inrtQury = $"INSERT INTO retaline_customer_order_cancellationdets(customer_id, order_id, reason, cancelled_by_type, cancelled_by_id) " +
                                        $"VALUES(@customerId, @orderId, @reason, @cancelledByType, @cancelledById)";
                    var dateresult = DataServiceMySql.ExecuteScalar(inrtQury, UserService.GetAPIConnectionString(), dateparams);

                    List<KeyValuePair<string, object>> stoparams = new List<KeyValuePair<string, object>>();
                    stoparams.Add(new KeyValuePair<string, object>("status", 15));
                    stoparams.Add(new KeyValuePair<string, object>("updatedBy", 1));
                    stoparams.Add(new KeyValuePair<string, object>("transferOrdId", orderId));
                    string updtQuery = "UPDATE finascop_stock_transfer_order SET fsto_status = @status, fsto_updateby = @updatedBy WHERE fsto_id = @transferOrdId";
                    DataServiceMySql.ExecuteSql(updtQuery, UserService.GetAPIConnectionString(), stoparams);

                    List<KeyValuePair<string, object>> stparams = new List<KeyValuePair<string, object>>();
                    stparams.Add(new KeyValuePair<string, object>("status", 3));
                    stparams.Add(new KeyValuePair<string, object>("transferOrdId", orderId));
                    string updQuery = "UPDATE finascop_stock_transfer_order_details_barcodes_temp SET rpb_status = @status WHERE tmp_barcode_fstoId = @transferOrdId";
                    DataServiceMySql.ExecuteSql(updQuery, UserService.GetAPIConnectionString(), stparams);

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
                    request.AddParameter("storegroup_id", this.CurrentUser.APIStoreId);
                    RestResponse response = client.ExecuteAsync(request).Result;

                    Console.WriteLine(response.Content);


                    ShowSuccess("Order Cancelled Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">The order {custOrderId} is cancelled successfully!</a></h5>");
                }
                catch
                {
                    Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
                }
                //ShowSuccess("Assigned Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Order picker has been assigned successfully!</a></h5>");

            }

            else
            {
                Common.ShowToastifyMessage(this.Page, "Failure! Error while saving data.");
            }
            //int status = Convert.ToInt32(result);

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


