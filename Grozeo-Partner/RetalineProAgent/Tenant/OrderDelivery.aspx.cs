using Finascop.Services;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RestSharp;
using RetalineProAgent.Core.BussinessModel.Order;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
//using RetalineProAgent.Core.BussinessModel.OnlineOrders;
using RetalineProAgent.Service;
using StackExchange.Redis;
using System;
using System.Collections.Concurrent;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Globalization;
using System.IO;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.Services.Description;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class OrderDelivery : Base.BasePartnerPage
    {
        public int FilterType
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

        protected void btnFilterType_Click(object sender, EventArgs e)
        {
            LinkButton btn = sender as LinkButton;
            if (btn == null || string.IsNullOrEmpty(btn.Attributes["typeid"]))
            {
                Common.ShowToastifyMessage(this.Page, "Invalid Selection", "danger");
                return;
            }

            FilterType = Convert.ToInt32(btn.Attributes["typeid"]);
            hidFilterType.Value = FilterType.ToString();
        }

        private void SetGridColumnVisibility(int filterType)
        {
            var columnVisibilityMap = new Dictionary<int, bool[]>
            {
                { 0, new[] { false, true, true, true, false, true, true, false, true } },
                { 1, new[] { false, true, true, true, false, true, true, false, true } },
                { 2, new[] { true, true, true, true, true, true, true, false, true } },
                { 4, new[] { false, true, true, true, false, true, true, false, true } },
                { 5, new[] { false, true, true, true, false, true, true, false, true } },
                { 11, new[] { false, true, true, true, false, true, true, false, true } },
            };

            bool[] columnVisibility;
            if (!columnVisibilityMap.TryGetValue(filterType, out columnVisibility))
            {
                columnVisibility = new[] { false, false, false, false, false, false, false, false, false };
            }

            for (int i = 0; i < columnVisibility.Length && i < gvPendingOrders.Columns.Count; i++)
            {
                gvPendingOrders.Columns[i].Visible = columnVisibility[i];
            }

            if (!columnVisibilityMap.ContainsKey(filterType) && gvPendingOrders.Columns.Count > 7)
                gvPendingOrders.Columns[7].Visible = false;
        }

        private void SetAdditionalVisibility(int filterType)
        {
            bool isScheduleVisible = (filterType == 2);
            scheduleDeliv.Visible = isScheduleVisible;
            slotContainer.Visible = isScheduleVisible;

            gvPendingOrders.AllowPaging = !isScheduleVisible;
        }

        protected void Page_Load(object sender, EventArgs e)
        {
            txtDispatchDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            int storegroupid = this.CurrentUser.APIStoreId;
            var dtBranches = DataServiceMySql.GetDataTable($"SELECT br_ID,br_name FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
            if (dtBranches != null && dtBranches.Rows.Count > 0)
            {
                DataRow dr = dtBranches.Rows[0];
                string branchName = dr["br_name"].ToString();

                var btStoreGrp = DataServiceMySql.GetDataTable($"SELECT COUNT(br_storeGroup) AS cnt FROM finascop_branch WHERE br_storeGroup = {storegroupid}", UserService.GetAPIConnectionString());
                if (btStoreGrp != null && btStoreGrp.Rows.Count > 0)
                {
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

            }

            if (!IsPostBack && String.IsNullOrEmpty(hidFilterType.Value))
            {

                FilterType = 0; hidFilterType.Value = "0";

                //ltrTitle.Text = "Delivery";
                txtDateFrom.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
                txtDateTo.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            }


            if (gvPendingOrders.HeaderRow != null)
                gvPendingOrders.HeaderRow.TableSection = TableRowSection.TableHeader;

            //txtDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            //txtTime.Text = DateTime.Now.ToString("HH:mm:ss");
            LoadStoreInfo();
        }

        private void LoadStoreInfo()
        {
            //txtDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
            //txtTime.Text = DateTime.Now.ToString("HH:mm:ss");
            if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
            {
                var IN = TimeZoneInfo.ConvertTimeBySystemTimeZoneId(DateTimeOffset.UtcNow, "India Standard Time");
                txtDate.Text = IN.ToString("yyyy-MM-dd");
                txtTime.Text = IN.ToString("HH:mm:ss");
            }
            else if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
            {
                var UK = TimeZoneInfo.ConvertTimeBySystemTimeZoneId(DateTimeOffset.UtcNow, "GMT Standard Time");
                txtDate.Text = UK.ToString("yyyy-MM-dd");
                txtTime.Text = UK.ToString("HH:mm:ss");
            }
        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            lbnpending.CssClass = String.Format("btn btn-block btn-outline-primary btn-sm {0}", (FilterType == 0 ? "active" : ""));
            lbtnDelivery.CssClass = String.Format("btn btn-block btn-outline-primary btn-sm {0}", (FilterType == 1 ? "active" : ""));
            lbtScheduleDelivery.CssClass = String.Format("btn btn-block btn-outline-primary btn-sm {0}", (FilterType == 2 ? "active" : ""));
            lbtnDelivered.CssClass = String.Format("btn btn-block btn-outline-primary btn-sm {0}", (FilterType == 4 ? "active" : ""));
            lbtnInTransit.CssClass = String.Format("btn btn-block btn-outline-primary btn-sm {0}", (FilterType == 5 ? "active" : ""));
            lbtndeliveryonhold.CssClass = String.Format("btn btn-inline-block btn-outline-primary mr-2 {0} ", (FilterType == 11 ? " active" : ""));
            SetGridColumnVisibility(FilterType);
            SetAdditionalVisibility(FilterType);
        }


        protected void lbtnPagerLeft_Click(object sender, EventArgs e)
        {
            if (gvPendingOrders.PageIndex > 0)
                gvPendingOrders.PageIndex = gvPendingOrders.PageIndex - 1;
        }

        protected void lbtnPagerRight_Click(object sender, EventArgs e)
        {
            if (gvPendingOrders.PageIndex < gvPendingOrders.PageCount - 1)
                gvPendingOrders.PageIndex = gvPendingOrders.PageIndex + 1;
        }

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            gvPendingOrders.PageIndex = 0;
            gvPendingOrders.DataBind();
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
        

        protected void gvPendingOrders_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvPendingOrders.PageIndex * gvPendingOrders.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvPendingOrders.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();
            //ltrPageCurStart.Text = (gvPendingOrders.Rows.Count > 0 ? startRowOnPage : 0).ToString();
            var dv = (DataView)SDSPendingOrders.Select(DataSourceSelectArguments.Empty);
        }

        protected void SDSPendingOrders_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroup"].Value = this.CurrentUser.APIStoreId;
            hidFilterType.Value = FilterType.ToString();
            e.Command.Parameters["filterType"].Value = FilterType;
            if (Page.User.IsInRole("BranchManager"))
            {
                int brid = UserService.UserRoleBranchId;
                e.Command.Parameters["branchId"].Value = brid;
            }
        }

        protected void SDSPendingOrders_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            //ltrPageTotal.Text = e.AffectedRows.ToString();
        }

        private void ExportGridToExcel()
        {
            DataView dv = (DataView)SDSPendingOrders.Select(DataSourceSelectArguments.Empty);
            DataTable dt = dv.ToTable();
            IWorkbook wb = new XSSFWorkbook();
            ISheet sheet = wb.CreateSheet("Data1");
            ICreationHelper cH = wb.GetCreationHelper();
            int rows = 0;
            IRow rowH = sheet.CreateRow(rows++);

            foreach (DataControlField dc in gvForExportOnly.Columns)
            {
                ICell cell = rowH.CreateCell(rowH.Cells.Count);
                cell.SetCellValue(cH.CreateRichTextString(dc.HeaderText));
            }

            foreach (DataRow dr in dt.Rows)
            {
                IRow row = sheet.CreateRow(rows++);
                for (int j = 0; j < gvForExportOnly.Columns.Count; j++)
                {
                    ICell cell = row.CreateCell(j);
                    cell.SetCellValue(cH.CreateRichTextString(dr[gvForExportOnly.Columns[j].SortExpression].ToString()));
                }
            }

            Response.Clear();
            Response.Buffer = true;
            Response.Charset = "";
            Response.ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
            Response.AddHeader("content-disposition", "attachment;filename=PackedOrders.xlsx");
            wb.Write(Response.OutputStream);

            Response.Flush();
            Response.End();

        }

        protected void lbtnDownloadExcel_Click(object sender, EventArgs e)
        {
            ExportGridToExcel();
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

        protected void gvPendingOrders_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            //  data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo"
            //if(FilterType == 1 || FilterType == 4 || FilterType == 5)
            //{
            e.Row.Attributes.Add("data-toggle", "collapse");
            e.Row.Attributes.Add("data-target", String.Format("#collapse{0}", e.Row.DataItemIndex));
            e.Row.Attributes.Add("aria-expanded", "false");
            e.Row.Attributes.Add("aria-controls", String.Format("collapse{0}", e.Row.DataItemIndex));
            //}
        }

        //protected void btnMoveToPacking_Click(object sender, EventArgs e)
        //{
        //    Button btnAssign = (Button)sender;
        //    string transferOrderId = Convert.ToString(btnAssign.Attributes["transferOrderId"]);
        //    string status = Convert.ToString(btnAssign.Attributes["statusId"]);
        //    string type = Convert.ToString(btnAssign.Attributes["orderType"]);
        //    int orderType = Convert.ToInt32(type);
        //    string requestId = Convert.ToString(btnAssign.Attributes["request"]);
        //    int statusId = Convert.ToInt32(status);

        //    if ((statusId == 11) && (orderType == 1))
        //    {
        //        List<KeyValuePair<string, object>> orderparams = new List<KeyValuePair<string, object>>();
        //        orderparams.Add(new KeyValuePair<string, object>("transOrdId", transferOrderId));
        //        orderparams.Add(new KeyValuePair<string, object>("status", 6));
        //        orderparams.Add(new KeyValuePair<string, object>("updatedBy", 1));
        //        string updateQry = "UPDATE finascop_stock_transfer_order SET fsto_status=@status, fsto_updateby=@updatedBy WHERE fsto_id=@transOrdId";
        //        DataServiceMySql.ExecuteSql(updateQry, Service.UserService.GetAPIConnectionString(), orderparams);

        //        List<KeyValuePair<string, object>> custorderparams = new List<KeyValuePair<string, object>>();
        //        custorderparams.Add(new KeyValuePair<string, object>("orderId", requestId));
        //        custorderparams.Add(new KeyValuePair<string, object>("status", 7));
        //        string updtQry = "UPDATE retaline_customer_order SET status_id=@status WHERE order_id=@orderId";
        //        DataServiceMySql.ExecuteSql(updtQry, Service.UserService.GetAPIConnectionString(), custorderparams);

        //        Common.ShowCustomAlert(this.Page, "Data updated!", "Data updated successfully!", true, "/Tenant/PendingOrders");
        //    }
        //    else
        //    {
        //        Common.ShowToastifyMessage(this.Page, "Error occured while saving data.", "danger");
        //    }

        //}

        protected void SDSPendingOrders_Selecting1(object sender, SqlDataSourceSelectingEventArgs e)
        {

        }

        protected void chkAll_CheckedChanged(object sender, EventArgs e)
        {
            CheckBox chbtn = (CheckBox)sender;
            if (chbtn.Checked == true)
            {
                foreach (GridViewRow gr in gvPendingOrders.Rows)
                {
                    CheckBox checkdeliv = (CheckBox)gr.FindControl("chkDelivery");
                    checkdeliv.Checked = true;
                }
            }
            else if (chbtn.Checked == false)
            {
                foreach (GridViewRow gr in gvPendingOrders.Rows)
                {
                    CheckBox checkdeliv = (CheckBox)gr.FindControl("chkDelivery");
                    checkdeliv.Checked = false;
                }
            }

        }

        protected void chkDelivery_CheckedChanged(object sender, EventArgs e)
        {
            CheckBox chbtn = (CheckBox)sender;
            if (chbtn.Checked == true)
            {
                lbnDelivStaff.Enabled = true;
                lbnManualDeliv.Enabled = true;
            }
            else
            {
                lbnDelivStaff.Enabled = false;
                lbnManualDeliv.Enabled = false;
            }
        }

        protected async void btnManualDeliverySubmit_Click(object sender, EventArgs e)
        {
            string fsto_id = "";
            string ordNum = "";
            string orderNumber = "";
            foreach (GridViewRow gr in gvPendingOrders.Rows)
            {
                CheckBox chk = (CheckBox)gr.FindControl("chkDelivery");
                if (chk != null && chk.Checked)
                {
                    fsto_id = Convert.ToString(chk.Attributes["fstoId"]);

                    List<KeyValuePair<string, object>> toparams = new List<KeyValuePair<string, object>>();
                    toparams.Add(new KeyValuePair<string, object>("fstoid", fsto_id));
                    toparams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
                    var tblOrdInfo = DataServiceMySql.GetDataTable($"SELECT qo.quor_AmountCollectible,co.order_id,co.order_order_id, qo.quor_id, qo.quor_TransferOrder_id  FROM retaline_customer_order co " +
                            $"INNER JOIN qugeo_order qo ON qo.quor_RefNo = co.order_order_id INNER JOIN finascop_stock_transfer_order fo ON co.order_id=fo.fstr_id " +
                            $" WHERE fo.fsto_id = @fstoid",
                            UserService.GetAPIConnectionString(), toparams);
                    if (tblOrdInfo == null || tblOrdInfo.Rows.Count <= 0)
                    {
                        Common.ShowCustomAlert(this.Page, "Failure", "Invalid order or the process cannot execute at this time.", false, "/Tenant/OrderDelivery");
                        return;
                    }

                    string order_id = tblOrdInfo.Rows[0]["order_id"].ToString();
                    string quor_id = tblOrdInfo.Rows[0]["quor_id"].ToString();
                    double quor_AmountCollectible = Convert.ToDouble(tblOrdInfo.Rows[0]["quor_AmountCollectible"]);
                    orderNumber = tblOrdInfo.Rows[0]["order_order_id"].ToString();

                    int qugeoId = Convert.ToInt32(quor_id);
                    int orderID = Convert.ToInt32(order_id);
                    txtDate.Text = DateTime.Now.ToString("yyyy-MM-dd");
                    txtTime.Text = DateTime.Now.ToString("HH:mm:ss");
                    string quor_DeliveryConfTime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                    string quorDeliveryConfTime = DateTime.Now.ToString("yyyy-MM-dd 00:00:00");
                    var data = new List<Dictionary<string, object>>();
                    data.Add(new Dictionary<string, object> {
                            {"qmd_deliveredBy", txtDelivBoy.Text },
                            {"qmd_Date", txtDate.Text },
                            {"qmd_Time", txtTime.Text },
                            {"quor_id", qugeoId },
                            {"qmd_createdOn", quor_DeliveryConfTime },
                            {"qmd_createdBy", 1 }
                        });

                    toparams = new List<KeyValuePair<string, object>>();
                    toparams.Add(new KeyValuePair<string, object>("qmddeliveredBy", txtDelivBoy.Text));
                    toparams.Add(new KeyValuePair<string, object>("qmdDate", txtDate.Text));
                    toparams.Add(new KeyValuePair<string, object>("qmdTime", txtTime.Text));
                    toparams.Add(new KeyValuePair<string, object>("quorid", qugeoId));
                    toparams.Add(new KeyValuePair<string, object>("qmdcreatedOn", quor_DeliveryConfTime));
                    string strSql = $"INSERT INTO qugeo_manual_deliver(qmd_deliveredBy, qmd_Date, qmd_Time, quor_id, qmd_createdOn, qmd_createdBy) " +
                            $"VALUES(@qmddeliveredBy, @qmdDate, @qmdTime, @quorid, @qmdcreatedOn, 1)";
                    DataServiceMySql.ExecuteSql(strSql, UserService.GetAPIConnectionString(), toparams);

                    toparams = new List<KeyValuePair<string, object>>();
                    toparams.Add(new KeyValuePair<string, object>("quorDeliveryConfTime", quorDeliveryConfTime));
                    toparams.Add(new KeyValuePair<string, object>("quorUpdateOn", quor_DeliveryConfTime));
                    toparams.Add(new KeyValuePair<string, object>("quorid", qugeoId));
                    string fstostatus = $"UPDATE qugeo_order SET quor_Type = 6, quor_DeliveryConfTime = @quorDeliveryConfTime,quor_Status = 15, quor_UpdateOn = @quorUpdateOn WHERE quor_id = @quorid";
                    DataServiceMySql.ExecuteSql(fstostatus, UserService.GetAPIConnectionString(), toparams);

                    toparams = new List<KeyValuePair<string, object>>();
                    toparams.Add(new KeyValuePair<string, object>("updateat", quor_DeliveryConfTime));
                    toparams.Add(new KeyValuePair<string, object>("orderid", orderID));
                    string updateQry = $"UPDATE retaline_customer_order SET status_id = 18, order_status_addinfo = '###2', payment_mode = IF(payment_mode=1,'1',payment_mode), order_ondel_bankref_id = '###7', " +
                        $"updated_at = @updateat WHERE order_id = @orderid ";
                    DataServiceMySql.ExecuteSql(updateQry, UserService.GetAPIConnectionString(), toparams);
                    //update ordere history
                    string action = "Action By" + this.CurrentUser.APIStoreId.ToString();
                    Core.Services.Order.OrderService.AddOrderHistoryData(orderID, 18, action);//Delivered=18
                    // Activitylog
                    String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                    String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                    string Source = strUrl;
                    int storegroupid = this.CurrentUser.APIStoreId;

                    string Users = this.CurrentUser.Email;
                    string storegroup = (this.CurrentUser.APIStoreId).ToString();
                    string deliveredBy = txtDelivBoy.Text;
                    string Date = txtDate.Text;
                    string Time = txtTime.Text;
                    int quorid = qugeoId;
                    string DeliveryConfTime = quorDeliveryConfTime;
                    string UpdateOn = quor_DeliveryConfTime;
                    int orderid = orderID;

                    var items = new[]
                        {
                    new { Key = "Store Group", Value = storegroup },
                    new { Key = " Delivered By", Value = deliveredBy },
                    new { Key = "Date", Value = Date },
                    new { Key = "Time", Value = Time },
                    new { Key = "DeliveryConfTime", Value = DeliveryConfTime },
                    new { Key = "UpdateOn", Value = UpdateOn },
                    new { Key = "orderid", Value =Convert.ToString(orderid) },
                    new { Key = "quorid", Value =Convert.ToString(quorid) },

                    };
                    string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
                    var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
                    ordNum = tblOrdInfo.Rows[0]["order_order_id"].ToString();
                    List<KeyValuePair<string, object>> custparams = new List<KeyValuePair<string, object>>();
                    custparams.Add(new KeyValuePair<string, object>("orderId", ordNum));
                    custparams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
                    var custTbl = DataServiceMySql.GetDataTable($"SELECT order_order_id, order_customer_id, cust_email, co.storegroup_id, store_group_name, cust_mobile, cust_customer_name FROM retaline_customer_order co INNER JOIN finascop_branch_group ON store_group_id = co.storegroup_id INNER JOIN retaline_customer ON cust_id = order_customer_id WHERE order_order_id = @orderId",
                            UserService.GetAPIConnectionString(), custparams);
                    if (custTbl == null || custTbl.Rows.Count <= 0)
                    {
                        Common.ShowCustomAlert(this.Page, "Failure", "Invalid order or the process cannot execute at this time.", false, "/Tenant/OrderDelivery");
                        return;
                    }
                    string name = custTbl.Rows[0]["cust_customer_name"].ToString();
                    string storeName = custTbl.Rows[0]["store_group_name"].ToString();
                    string email = custTbl.Rows[0]["cust_email"].ToString();
                    var emailresult = Core.Services.APIService.OrdDelivConfEmail(name, email, storeName, ordNum);

                    try
                    {
                        //var  result = DeliveryService.DeliveryVoucher(fsto_id, UserService.GetAPIConnectionString(), this.CurrentUser.APIStoreId).ConfigureAwait(false);
                        //    await result;

                        string url = ConfigurationSettings.AppSettings.Get("api.url");
                        if (String.IsNullOrEmpty(url))
                        {
                            url = "http://bizapi.dev.grozeo.in";
                        }

                        var options = new RestClientOptions(url);

                        var client = new RestClient(options);

                        var request = new RestRequest("/api/finascop/finascopPostingService", Method.Post);
                        request.AlwaysMultipartFormData = true;
                        request.AddParameter("order_id", order_id);
                        request.AddParameter("finascopEventRefId", "078024a3-38d7-11ee-9967-065723bafb24");
                        request.AddParameter("storegroup_id", this.CurrentUser.APIStoreId);
                        RestResponse response = client.ExecuteAsync(request).Result;

                        var DeliveryConfirmationRequest = new RestRequest("/api/finascop/finascopPostingService", Method.Post);
                        DeliveryConfirmationRequest.AlwaysMultipartFormData = true;
                        DeliveryConfirmationRequest.AddParameter("order_id", order_id);
                        DeliveryConfirmationRequest.AddParameter("finascopEventRefId", "07802530-38d7-11ee-9967-065723bafb24");
                        DeliveryConfirmationRequest.AddParameter("storegroup_id", this.CurrentUser.APIStoreId);
                        RestResponse DeliveryConfirmationResponse = client.ExecuteAsync(DeliveryConfirmationRequest).Result;

                        if (quor_AmountCollectible > 0)
                        {
                            //var result3 = PayOnDelivery.PODVoucher(fsto_id, UserService.GetAPIConnectionString(), this.CurrentUser.APIStoreId).ConfigureAwait(false);
                            //await result3;
                            //var PayOnDeliveryRequest = new RestRequest($"/api/finascop/finascopPostingService/{order_id}/", Method.Get);
                            //RestResponse PayOnDeliveryResponse = client.ExecuteAsync(PayOnDeliveryRequest).Result;
                            //Console.WriteLine(PayOnDeliveryResponse.Content);
                        }


                    }
                    catch (Exception ex)
                    {
                        string strError = ex.Message;
                    }
                }
            }
            Common.ShowCustomAlert(this.Page, "Delivered Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your items delivered successfully!</a></h5>", true, "/Tenant/OrderDelivery");
        }

        protected void btnDeliveryBoyAssign_Click(object sender, EventArgs e)
        {
            string orderId = "", maplong = "", maplat = "", status = "", quorId = "", orderBranchId = "";
            List<string> qrids = new List<string>();
            foreach (GridViewRow gr in gvPendingOrders.Rows)
            {
                CheckBox chk = (CheckBox)gr.FindControl("chkDelivery");
                if (chk != null && chk.Checked)
                {
                    orderId = Convert.ToString(chk.Attributes["orderId"]);
                    maplong = Convert.ToString(chk.Attributes["mapLong"]);
                    maplat = Convert.ToString(chk.Attributes["mapLat"]);
                    status = Convert.ToString(chk.Attributes["orderType"]);
                    quorId = Convert.ToString(chk.Attributes["quorId"]);
                    qrids.Add(chk.Attributes["quorId"]);
                    orderBranchId = Convert.ToString(chk.Attributes["orderBranchId"]);

                }
            }
            string brId = orderBranchId;
            string handling_br_id = orderBranchId;
            string drivetype = status;


            LinkButton btnAssign = (LinkButton)sender;
            string strQuorId = Request.QueryString["quorId"];
            int quorIds = Convert.ToInt32(quorId);
            int branchId = Convert.ToInt32(brId);
            int handlingBranchId = Convert.ToInt32(handling_br_id);
            string type = drivetype;
            string hdnVehicleId = Convert.ToString(btnAssign.Attributes["vehicleId"]);
            int qugeobkNO = quorIds;
            var quorIdArray = qrids.ToArray();
            string result = APIService.AssignDeliveryStaff(qugeobkNO, branchId, handlingBranchId, type, hdnVehicleId, quorIdArray);
            string message = result;

            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = this.CurrentUser.APIStoreId;
            string Users = this.CurrentUser.Email;

            int BookingNumber = quorIds;
            int BranchId = branchId;
            string drive_type = drivetype;
            string VehicleId = Convert.ToString(hdnVehicleId);
            var Ids = quorIdArray;
            string orderid = result;
            string APIname = "AssignDeliveryStaff";

            var items = new[]
                {
                    new { Key = "Booking Number", Value =Convert.ToString(BookingNumber) },
                    new { Key = " Branch Id", Value =Convert.ToString (BranchId) },
                    new { Key = "Drive Type", Value = drivetype },
                    new { Key = "Vehicle Id", Value = VehicleId },
                    new { Key = "orderid", Value =Convert.ToString(orderid) },
                    new { Key = "APIname", Value =APIname },

                    };
            string Description = string.Join(", ", items.Select(item => $"{item.Key}={item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);
            if (message == "The driver has a live poll, please try after two minutes.")
            {
                Common.ShowToastifyMessage(this.Page, "The driver has a live poll, please try after two minutes.", "danger");
            }
            else
            {
                ShowSuccess("Assigned Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your items assigned to driver successfully!</a></h5>");
            }
        }

        protected void lbnDelivStaff_Click(object sender, EventArgs e)
        {
            string orderId = "", maplong = "", maplat = "", status = "", quorId = "", orderBranchId = "";
            List<string> qrids = new List<string>();
            foreach (GridViewRow gvrow in gvPendingOrders.Rows)
            {
                CheckBox chk = (CheckBox)gvrow.FindControl("chkDelivery");
                if (chk != null & chk.Checked)
                {
                    orderId = Convert.ToString(chk.Attributes["orderId"]);
                    maplong = Convert.ToString(chk.Attributes["mapLong"]);
                    maplat = Convert.ToString(chk.Attributes["mapLat"]);
                    status = Convert.ToString(chk.Attributes["orderType"]);
                    quorId = Convert.ToString(chk.Attributes["quorId"]);
                    qrids.Add(chk.Attributes["quorId"]);
                    orderBranchId = Convert.ToString(chk.Attributes["orderBranchId"]);
                    break;
                }
                //string liveVehicle = orderId + ',' + maplong + ',' + maplat + ',' + status + ',' + quorId;
                //ClientScript.RegisterStartupScript(GetType(), "Show('" + liveVehicle + "')", "<script> $('#modalDeliveryStaff').modal('toggle');</script>");

                //ScriptManager.RegisterStartupScript(this, typeof(string), "<script> $('#modalDeliveryStaff').modal('toggle');</script>", "#modalDeliveryStaff('" + liveVehicle + "');", true);
            }

            //string result = APIService.AssignDeliveryBoy(qugeobkNO, branchId, handlingBranchId, type, hdnVehicleId, qrids.ToArray());


            ODSLiveVehicles.SelectParameters["branchid"].DefaultValue = orderBranchId;
            ODSLiveVehicles.SelectParameters["pickupLat"].DefaultValue = maplat;
            ODSLiveVehicles.SelectParameters["pickupLng"].DefaultValue = maplong;
            ODSLiveVehicles.Select();
            gvLiveVehicles.DataBind();

            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalDeliveryStaff').modal('toggle');</script>");
        }


        protected void lbnManualDeliv_Click(object sender, EventArgs e)
        {
            string strfstoId = Request.QueryString["fstoId"];
            int fstoId = Convert.ToInt32(strfstoId);

            int fstoNO = fstoId;
            List<string> fstoidList = new List<string>();
            //string fstoId = "";
            foreach (GridViewRow gr in gvPendingOrders.Rows)
            {
                CheckBox chk = (CheckBox)gr.FindControl("chkDelivery");
                if (chk != null && chk.Checked)
                {
                    string orderNum = chk.Attributes["fstoId"];
                    if (!String.IsNullOrEmpty(orderNum))
                        fstoidList.Add(orderNum);
                }
            }

            ClientScript.RegisterStartupScript(GetType(), "Show", "<script> $('#modalmanualDelivery').modal('toggle');</script>");
        }


        //protected void btnFailed_Click(object sender, EventArgs e)
        //{

        //}
        //protected void btnDelivered_Click(object sender, EventArgs e)
        //{

        //}
        public void OnConfirmManualSch(object sender, EventArgs e)
        {
            string confirmValue = Request.Form["confirm_value"];
            if (confirmValue == "Yes")
            {
                this.Page.ClientScript.RegisterStartupScript(this.GetType(), "alert", "alert('You clicked YES!')", true);
            }
            else
            {
                this.Page.ClientScript.RegisterStartupScript(this.GetType(), "alert", "alert('You clicked NO!')", true);
            }
        }
        //public void OnConfirmFail(object sender, EventArgs e)
        //{
        //    string confirmValue = Request.Form["confirm_value"];
        //    if (confirmValue == "Yes")
        //    {
        //        this.Page.ClientScript.RegisterStartupScript(this.GetType(), "alert", "alert('You clicked YES!')", true);
        //    }
        //    else
        //    {
        //        this.Page.ClientScript.RegisterStartupScript(this.GetType(), "alert", "alert('You clicked NO!')", true);
        //    }
        //}

        //public void OnConfirmDeliver(object sender, EventArgs e)
        //{
        //    string confirmValue = Request.Form["confirm_value"];
        //    if (confirmValue == "Yes")
        //    {
        //        this.Page.ClientScript.RegisterStartupScript(this.GetType(), "alert", "alert('You clicked YES!')", true);
        //    }
        //    else
        //    {
        //        this.Page.ClientScript.RegisterStartupScript(this.GetType(), "alert", "alert('You clicked NO!')", true);
        //    }
        //}

        protected void btnmanualSchedule_Click(object sender, EventArgs e)
        {
            Button btnManualSchedule = (Button)sender;

            string quorid = Convert.ToString(btnManualSchedule.Attributes["quorId"]);
            string orderId = Convert.ToString(btnManualSchedule.Attributes["orderId"]);
            if (btnManualSchedule == null || String.IsNullOrEmpty(btnManualSchedule.Attributes["quorId"]))
            {
                // show error
                Common.ShowToastifyMessage(this.Page, "Invalid order for manual schedule.", "danger");
                return;
            }
            List<KeyValuePair<string, object>> msparams = new List<KeyValuePair<string, object>>();
            msparams.Add(new KeyValuePair<string, object>("quorid", quorid));
            //msparams.Add(new KeyValuePair<string, object>("fstoid", "Manual Schedule"));
            //msparams.Add(new KeyValuePair<string, object>("fstoid", Request.QueryString["statusDes"]));
            //msparams.Add(new KeyValuePair<string, object>("quor_Status", Request.QueryString["qugeoStatus"]));
            //msparams.Add(new KeyValuePair<string, object>("quor_Status", Request.QueryString["qugeoStatus"]));
            msparams.Add(new KeyValuePair<string, object>("quorType", "0"));
            msparams.Add(new KeyValuePair<string, object>("quorSchedulePickupTime", null));
            msparams.Add(new KeyValuePair<string, object>("quorPickedupTime", null));
            msparams.Add(new KeyValuePair<string, object>("quorQugeoPickupDDBOrderId", ""));
            msparams.Add(new KeyValuePair<string, object>("quorPickupToBeManual", "1"));
            msparams.Add(new KeyValuePair<string, object>("quorPickupConfTime", null));
            msparams.Add(new KeyValuePair<string, object>("quorUpdateOn", DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss")));
            msparams.Add(new KeyValuePair<string, object>("quorStatus", "22"));

            string updateQry1 = $"UPDATE qugeo_order SET quor_Type=@quorType, quor_QugeoPickupDDBOrderId=@quorQugeoPickupDDBOrderId, quor_PickupToBeManual=@quorPickupToBeManual, quor_PickupConfTime=@quorPickupConfTime, quor_SchedulePickupTime=@quorSchedulePickupTime, quor_PickedupTime=@quorPickedupTime, quor_Status=@quorStatus, quor_UpdateOn=@quorUpdateOn WHERE quor_id=@quorid ";
            DataServiceMySql.ExecuteSql(updateQry1, UserService.GetAPIConnectionString(), msparams);

            //string orderId = Request.QueryString["orderId"];

            List<KeyValuePair<string, object>> coparams = new List<KeyValuePair<string, object>>();
            coparams.Add(new KeyValuePair<string, object>("orderId", orderId));
            coparams.Add(new KeyValuePair<string, object>("statusId", 9));
            coparams.Add(new KeyValuePair<string, object>("addInfo", "###2"));
            coparams.Add(new KeyValuePair<string, object>("bankrefId", "###7"));
            coparams.Add(new KeyValuePair<string, object>("updatedAt", DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss")));

            string updateQry2 = $"UPDATE retaline_customer_order SET status_id=@statusId, order_status_addinfo=@addInfo, payment_mode = if(payment_mode=1,'1',payment_mode), order_ondel_bankref_id=@bankrefId, updated_at=@updatedAt WHERE order_id=@orderId ";
            DataServiceMySql.ExecuteSql(updateQry2, UserService.GetAPIConnectionString(), coparams);


            string action = "Action By" + this.CurrentUser.APIStoreId.ToString();
            Core.Services.Order.OrderService.AddOrderHistoryData(Convert.ToInt32(orderId), 9, action);//Packed And Ready for delivery=9

            Common.ShowCustomAlert(this.Page, "Manually Scheduled!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your order is manually scheduled!</a></h5>", true, "/Tenant/OrderDelivery");
        }

        protected void lbtnOrderDetails_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            string quorId = lbtn.Attributes["quorId"];
            hdnQuorId.Value = quorId;
            string order_id = lbtn.Attributes["order_id"];
            hdnOrderId.Value = order_id;
            string strAlertSCript = "$('#modalDeliveryDetails').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void btnAdd_Click(object sender, EventArgs e)
        {
            try
            {
                Button btnDeliveryDetails = (Button)sender;
                string quorid = hdnQuorId.Value;
                string order_id = hdnOrderId.Value;

                DateTime dispatchDateTime = DateTime.Parse(txtDispatchDate.Text);
                string dispatchDateTimeStr = dispatchDateTime.ToString("yyyy-MM-dd HH:mm:ss");
                string dispatchDate = dispatchDateTime.ToString("yyyy-MM-dd");
                string dispatchTime = String.Format("{0}:{1}:{2}", dispatchDateTime.ToString("HH"), dispatchDateTime.ToString("mm"), dispatchDateTime.ToString("ss"));
                List<KeyValuePair<string, object>> qugeoparams = new List<KeyValuePair<string, object>>();
                if (selCourier.SelectedItem.Value != "-1")
                {
                    qugeoparams.Add(new KeyValuePair<string, object>("courier", selCourier.SelectedItem.Value));
                }
                else
                {
                    qugeoparams.Add(new KeyValuePair<string, object>("courier", "-1"));
                }
                qugeoparams.Add(new KeyValuePair<string, object>("trackingNumber", txtTrackingNo.Text));
                qugeoparams.Add(new KeyValuePair<string, object>("dispatchDate", dispatchDate));
                qugeoparams.Add(new KeyValuePair<string, object>("dispatchTime", dispatchTime));
                qugeoparams.Add(new KeyValuePair<string, object>("orderId", (quorid)));
                qugeoparams.Add(new KeyValuePair<string, object>("trackingURL", txtTrackingURL.Text));
                qugeoparams.Add(new KeyValuePair<string, object>("statusId", 9));
                qugeoparams.Add(new KeyValuePair<string, object>("type", 4));

                string strSqlcourier = $"INSERT INTO qugeo_order_courier(qoc_courier, qoc_qcn, qoc_date, qoc_time, quor_id, qoc_trackingUrl) " +
                        $"VALUES(@courier, @trackingNumber, @dispatchDate, @dispatchTime, @orderId, @trackingURL)";
                DataServiceMySql.ExecuteSql(strSqlcourier, UserService.GetAPIConnectionString(), qugeoparams);

                string strSqlOrder = $"UPDATE qugeo_order SET quor_Type=@type, quor_Status=@statusId WHERE quor_id=@orderId ";
                DataServiceMySql.ExecuteSql(strSqlOrder, UserService.GetAPIConnectionString(), qugeoparams);

                string quor_DeliveryConfTime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                qugeoparams.Add(new KeyValuePair<string, object>("custstatus_id", 15));
                qugeoparams.Add(new KeyValuePair<string, object>("updateat", quor_DeliveryConfTime));
                qugeoparams.Add(new KeyValuePair<string, object>("corderId", order_id));
                string updateQry = $"UPDATE retaline_customer_order SET status_id = @custstatus_id, updated_at = @updateat, order_trackURL = @trackingURL WHERE order_id = @corderId ";
                DataServiceMySql.ExecuteSql(updateQry, UserService.GetAPIConnectionString(), qugeoparams);
                                string url = ConfigurationSettings.AppSettings.Get("api.url");
                if (String.IsNullOrEmpty(url))
                {
                    url = "http://bizapi.dev.grozeo.in";
                }

                var options = new RestClientOptions(url);

                var client = new RestClient(options);

                var request = new RestRequest("/api/finascop/finascopPostingService", Method.Post);
                request.AlwaysMultipartFormData = true;
                request.AddParameter("order_id", order_id);
                request.AddParameter("finascopEventRefId", "07802425-38d7-11ee-9967-065723bafb24");
                request.AddParameter("storegroup_id", this.CurrentUser.APIStoreId);
                RestResponse response = client.ExecuteAsync(request).Result;

                Common.ShowCustomAlert(this.Page, "Order Dispatched!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your order is dispatched!</a></h5>", true, "/Tenant/OrderDelivery");
            }
            catch
            {
                Common.ShowToastifyMessage(this.Page, "Invalid order.", "danger");
                return;
            }

        }

        protected void lbtnDeliveryUpdate_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            string quorId = lbtn.Attributes["quorId"];
            hdnQuorId.Value = quorId;
            string paymentMode = lbtn.Attributes["paymentMode"];
            hdnPayementMode.Value = paymentMode;
            string order_id = lbtn.Attributes["orderId"];
            hdnOrderId.Value = order_id;
            if (paymentMode == "1" || paymentMode == "7")
            {
                dvPaymentMode.Visible = true;
            }
            else
            {
                dvPaymentMode.Visible = false;
            }
            string strAlertSCript = "$('#modalDeliveryUpdate').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void btnDeliveryUpdate_Click(object sender, EventArgs e)
        {
            try
            {
                Button btnDeliveryDetails = (Button)sender;
                string quorid = hdnQuorId.Value;
                string paymentMode = hdnPayementMode.Value;
                string orderId = hdnOrderId.Value;
                string dispatchDateTime = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
                string bankTransactionId = "";
                int mode = 0;
                dvPaymentMode.Visible = (paymentMode == "1" || paymentMode == "7");
                mode = (dvPaymentMode.Visible && rbBank.Checked) ? 6 : 7;
                bankTransactionId = (mode == 6) ? txtTransactionId.Text : "";
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
                string updateQry = $"UPDATE retaline_customer_order SET status_id = 18, order_status_addinfo = '###2', payment_mode = CASE WHEN @payementMode > 0 THEN @payementMode ELSE payment_mode  END , order_ondel_bankref_id = @banktransactionId,updated_at = @current_datetime WHERE order_id = @p_order_id;";
                DataServiceMySql.ExecuteSql(updateQry, UserService.GetAPIConnectionString(), qugeoparams);


                //update ordere history
                string action = "Action By" + this.CurrentUser.APIStoreId.ToString();
                Core.Services.Order.OrderService.AddOrderHistoryData(Convert.ToInt32(quorid), 18, action);//Delivered=18
                try
                {
                    DataTable dtResult = DataServiceMySql.GetDataTable("UpdateDeliveryStatus", UserService.GetAPIConnectionString(), qugeoparams, true);
                }
                catch
                {
                    Common.ShowToastifyMessage(this.Page, "Invalid order.", "danger");
                    return;
                }


                qugeoparams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
                var custTbl = DataServiceMySql.GetDataTable($"SELECT order_order_id, order_customer_id, cust_email, co.storegroup_id, store_group_name, cust_mobile, cust_customer_name FROM retaline_customer_order co INNER JOIN finascop_branch_group ON store_group_id = co.storegroup_id INNER JOIN retaline_customer ON cust_id = order_customer_id WHERE order_id = @p_order_id",
                        UserService.GetAPIConnectionString(), qugeoparams);
                if (custTbl == null || custTbl.Rows.Count <= 0)
                {
                    Common.ShowCustomAlert(this.Page, "Failure", "Invalid order or the process cannot execute at this time.", false, "/Tenant/OrderDelivery");
                    return;
                }

                string DelVouherItemsQry = $"SELECT fsto_id,order_delivery_charge,storegroup_id,order_courier_charge,order_delivery_charge_gst," +
                   $"order_order_id AS orders, order_total_amount AS amount_before_tax, order_roundoff, order_id, order_branch_id,order_tcs_utgst," +
                   $"order_tds,order_tcs,order_tcs_cgst,order_tcs_sgst,order_tcs_igst,order_total_utgst,order_delivery_charge_utgst,order_method," +
                   $"payment_mode,order_total_sgst AS sgst, order_total_cgst AS cgst, order_delivery_charge_igst,order_total_igst, " +
                   $"order_delivery_charge_cgst,order_delivery_charge_sgst, quor_AmountCollectible,quor_Paymode,quor_DeliveryDriverId  " +
                   $"FROM retaline_customer_order rco " +
                   $"INNER JOIN finascop_stock_transfer_order fsto ON rco.order_id = fsto.fstr_id  AND fsto.fsto_ordertype = 1 " +
                   $"INNER JOIN qugeo_order ON quor_TransferOrder_id = fsto_id AND quor_TransferOrder_Type = 1 WHERE order_id = @order_id";


                List<KeyValuePair<string, object>> dviParams = new List<KeyValuePair<string, object>>();
                dviParams.Add(new KeyValuePair<string, object>("order_id", orderId));

                DataTable dviResult = DataServiceMySql.GetDataTable(DelVouherItemsQry, UserService.GetAPIConnectionString(), dviParams);

                DataRow dviData = dviResult.Rows[0];

                if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
                {
                    if (int.Parse(dviData["quor_AmountCollectible"].ToString()) > 0)
                    {
                        switch (int.Parse(dviData["order_method"].ToString()))
                        {
                            case 1:
                                if (int.Parse(dviData["quor_DeliveryDriverId"].ToString()) > 0)
                                {
                                    int driverID = int.Parse(dviData["quor_DeliveryDriverId"].ToString());

                                    string driverDetailsQry = $"SELECT createdBy FROM qugeo_driver WHERE d_ID ={driverID}";
                                    int createdBy = (int)DataServiceMySql.ExecuteScalar(driverDetailsQry, UserService.GetAPIConnectionString());
                                    if (createdBy == 1)
                                    {
                                        List<KeyValuePair<string, object>> fav_params = new List<KeyValuePair<string, object>>();

                                        string str_FPVSqlOrder = $"UPDATE finance_autoposting_values " +
                                            $" SET DeliveryAgent_PODCashinHand = @quor_AmountCollectible," +
                                            $" TSOPOD_CashPayment = @quor_AmountCollectible, TenantCollection_COD = @quor_AmountCollectible," +
                                            $" CourierCollection_COD = 0, GrozeoLogisticsPartnerCollection_COD = 0, " +
                                            $" POD_CashSettledbyDA = @quor_AmountCollectible WHERE order_id = @order_id ";

                                        fav_params.Add(new KeyValuePair<string, object>("order_id", orderId));
                                        fav_params.Add(new KeyValuePair<string, object>("quor_AmountCollectible", dviData["quor_AmountCollectible"]));

                                        DataServiceMySql.ExecuteSql(str_FPVSqlOrder, UserService.GetAPIConnectionString(), fav_params);

                                    }
                                    else
                                    {
                                        List<KeyValuePair<string, object>> fav_params = new List<KeyValuePair<string, object>>();

                                        string str_FPVSqlOrder = $"UPDATE finance_autoposting_values " +
                                            $" SET DeliveryAgent_PODCashinHand = @quor_AmountCollectible," +
                                            $" TSOPOD_CashPayment = @quor_AmountCollectible, TenantCollection_COD = 0, CourierCollection_COD = 0," +
                                            $" GrozeoLogisticsPartnerCollection_COD = @quor_AmountCollectible, " +
                                            $" POD_CashSettledbyDA = @quor_AmountCollectible WHERE order_id = @order_id ";

                                        fav_params.Add(new KeyValuePair<string, object>("order_id", orderId));
                                        fav_params.Add(new KeyValuePair<string, object>("quor_AmountCollectible", dviData["quor_AmountCollectible"]));

                                        DataServiceMySql.ExecuteSql(str_FPVSqlOrder, UserService.GetAPIConnectionString(), fav_params);

                                    }
                                }
                                break;
                            case 3:

                                List<KeyValuePair<string, object>> favparams = new List<KeyValuePair<string, object>>();

                                string strFPVSqlOrder = $"UPDATE finance_autoposting_values SET TSOPOD_CashPayment = @quor_AmountCollectible, TenantCollection_COD = 0, CourierCollection_COD = @quor_AmountCollectible, GrozeoLogisticsPartnerCollection_COD = 0, POD_CashSettledbyLSP = @quor_AmountCollectible WHERE order_id = @order_id ";

                                favparams.Add(new KeyValuePair<string, object>("order_id", orderId));
                                favparams.Add(new KeyValuePair<string, object>("quor_AmountCollectible", dviData["quor_AmountCollectible"]));

                                DataServiceMySql.ExecuteSql(strFPVSqlOrder, UserService.GetAPIConnectionString(), favparams);

                                break;
                        }
                    }
                }

                else if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                {
                    if (int.Parse(dviData["quor_AmountCollectible"].ToString()) > 0)
                    {
                        switch (int.Parse(dviData["order_method"].ToString()))
                        {
                            case 1:
                                if (int.Parse(dviData["quor_DeliveryDriverId"].ToString()) > 0)
                                {
                                    int driverID = int.Parse(dviData["quor_DeliveryDriverId"].ToString());

                                    string driverDetailsQry = $"SELECT createdBy FROM qugeo_driver WHERE d_ID ={driverID}";
                                    int createdBy = (int)DataServiceMySql.ExecuteScalar(driverDetailsQry, UserService.GetAPIConnectionString());
                                    if (createdBy == 1)
                                    {

                                        List<KeyValuePair<string, object>> fav_params = new List<KeyValuePair<string, object>>();

                                        string str_FPVSqlOrder = $"UPDATE finance_autoposting_values SET TSOPOD_CashPayment = @quor_AmountCollectible," +
                                            $" TenantCollection_COD = @quor_AmountCollectible, CourierCollection_COD = 0, " +
                                            $" GrozeoLogisticsPartnerCollection_COD = 0  WHERE order_id = @order_id ";

                                        fav_params.Add(new KeyValuePair<string, object>("order_id", orderId));
                                        fav_params.Add(new KeyValuePair<string, object>("quor_AmountCollectible", dviData["quor_AmountCollectible"]));

                                        DataServiceMySql.ExecuteSql(str_FPVSqlOrder, UserService.GetAPIConnectionString(), fav_params);

                                    }
                                    else
                                    {
                                        List<KeyValuePair<string, object>> fav_params = new List<KeyValuePair<string, object>>();

                                        string str_FPVSqlOrder = $"UPDATE finance_autoposting_values SET TSOPOD_CashPayment = @quor_AmountCollectible," +
                                            $" TenantCollection_COD = 0, CourierCollection_COD = 0, " +
                                            $" GrozeoLogisticsPartnerCollection_COD = @quor_AmountCollectible  WHERE order_id = @order_id ";

                                        fav_params.Add(new KeyValuePair<string, object>("order_id", orderId));
                                        fav_params.Add(new KeyValuePair<string, object>("quor_AmountCollectible", dviData["quor_AmountCollectible"]));

                                        DataServiceMySql.ExecuteSql(str_FPVSqlOrder, UserService.GetAPIConnectionString(), fav_params);
                                    }
                                }
                                break;
                            case 3:

                                List<KeyValuePair<string, object>> favparams = new List<KeyValuePair<string, object>>();

                                string strFPVSqlOrder = $"UPDATE finance_autoposting_values SET TSOPOD_CashPayment = @quor_AmountCollectible," +
                                    $" TenantCollection_COD = 0, CourierCollection_COD = @quor_AmountCollectible, GrozeoLogisticsPartnerCollection_COD = 0 " +
                                    $" WHERE order_id = @order_id ";

                                favparams.Add(new KeyValuePair<string, object>("order_id", orderId));
                                favparams.Add(new KeyValuePair<string, object>("quor_AmountCollectible", dviData["quor_AmountCollectible"]));

                                DataServiceMySql.ExecuteSql(strFPVSqlOrder, UserService.GetAPIConnectionString(), favparams);
                                break;
                        }
                    }

                }

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
                request.AddParameter("finascopEventRefId", "078024a3-38d7-11ee-9967-065723bafb24");
                request.AddParameter("storegroup_id", this.CurrentUser.APIStoreId);
                RestResponse response = client.ExecuteAsync(request).Result;

                var DeliveryConfirmationRequest = new RestRequest("/api/finascop/finascopPostingService", Method.Post);
                DeliveryConfirmationRequest.AlwaysMultipartFormData = true;
                DeliveryConfirmationRequest.AddParameter("order_id", orderId);
                DeliveryConfirmationRequest.AddParameter("finascopEventRefId", "07802530-38d7-11ee-9967-065723bafb24");
                DeliveryConfirmationRequest.AddParameter("storegroup_id", this.CurrentUser.APIStoreId);
                RestResponse DeliveryConfirmationResponse = client.ExecuteAsync(DeliveryConfirmationRequest).Result;


                string name = custTbl.Rows[0]["cust_customer_name"].ToString();
                string storeName = custTbl.Rows[0]["store_group_name"].ToString();
                string email = custTbl.Rows[0]["cust_email"].ToString();
                string ordNum = custTbl.Rows[0]["order_order_id"].ToString();


                var emailresult = Core.Services.APIService.OrdDelivConfEmail(name, email, storeName, ordNum);
                ShowSuccess("Delivery Completed!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your item/s has been delivered successfully!</a></h5>");
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "Invalid order.", "danger");
                return;
            }
        }
        protected void btnshowpopup_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            //toid
            int toid = Convert.ToInt32(lbtn.Attributes["toid"]);
            int order_id = Convert.ToInt32(lbtn.Attributes["order_id"]);
            int quorId = Convert.ToInt32(lbtn.Attributes["quorId"]);
            hdnQuorId.Value = Convert.ToString(quorId);
            hdnOrderId.Value = Convert.ToString(order_id);
            if (chkcancel.Checked)
            {
                canellupdate(order_id);
            }
            else if (chkmannual.Checked)
            {
                string strAlertSCript = "$('#modalDeliveryDetails').modal('show');";
                strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
                System.Type cstype = this.GetType();
                String csname1 = "ShowConfirmPopup";
                ClientScriptManager cs = this.ClientScript;
                StringBuilder cstext1 = new StringBuilder();
                cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
                cstext1.Append("script>");
                cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
            }
            else
            {
                updateshipment(order_id);
            }
        }
        protected async void lbtmanagedelivery_Click(object sender, EventArgs e)
        {
            LinkButton lbtn = (LinkButton)sender;
            string quorId = lbtn.Attributes["quorId"];
            btnshowpopup.Attributes.Add("quorId", quorId);
            string order_id = lbtn.Attributes["orderId"];
            btnshowpopup.Attributes.Add("order_id", order_id);
            string strAlertSCript = "$('#modaldeliverycharge').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("orderid", order_id));
            string orderdetails = "SELECT order_delivery_charge,order_order_id FROM `retaline_customer_order` where order_id=@orderid";
            var dtorderdetails = DataServiceMySql.GetDataTable(orderdetails, UserService.GetAPIConnectionString(), parmeters: prms);
            if (dtorderdetails != null && dtorderdetails.Rows.Count > 0)
            {
                var trorderdetalis = dtorderdetails.Rows[0];
                string ordere_id = trorderdetalis["order_order_id"].ToString();
                string deliverychange = trorderdetalis["order_delivery_charge"].ToString();
                string tableprefix = ConfigurationManager.AppSettings.Get("AWS_Prefix");
                string table = "shipping_consignment_log";
                string tableName = String.Concat(tableprefix, table);
                var data = new Dictionary<string, object>
                {
                    ["PartitionKey"] = new Dictionary<string, string> { ["col"] = "orderID", ["val"] = ordere_id },
                    ["SortKey"] = new Dictionary<string, string> { ["col"] = "orderMethod", ["val"] = "3" },
                    ["IndexName"] = "orderID-orderMethod-index",
                    ["queryAttributes"] = new List<string> { "orderID", "orderMethod", "request", "response", "type" },
                    ["Condition"] = new List<Dictionary<string, string>>
                    {
                      new Dictionary<string, string> { ["col"] = "type", ["val"] = "1", ["oper"] = "=" }
                    }
                };

                var items = await DynamoService.ReadDynamoDBAsync(tableName, data);
                string shippmentresult = items
                    .Where(item => item.TryGetValue("orderID", out var orderIdValue) && orderIdValue.S == ordere_id)
                    .Select(item =>
                    {
                        // Check if "request" equals "Partner List"
                        if (item.TryGetValue("request", out var requestValue) && requestValue.S == "Partner List")
                        {
                            // Return the response value if "request" is "Partner List"
                            return item.TryGetValue("response", out var responseValue) ? responseValue.S : null;
                        }
                        return null;
                    })
                  .FirstOrDefault(response => response != null);

                if (shippmentresult != null)
                {
                    var jsonObject = JsonConvert.DeserializeAnonymousType(shippmentresult, new[] { new { partner_id = string.Empty, delivery_charge = 0.00 } });
                    var minCharge = jsonObject.OrderBy(d => d.delivery_charge).FirstOrDefault();
                    var minChargeValue = minCharge?.delivery_charge;
                    double minChargeValueStr = ((double)minChargeValue);
                    double deficientDelivery = (minChargeValueStr - Convert.ToDouble(deliverychange));
                    ltrdelivery.Text = $"Based on the submitted size and weight, the delivery charges have increased to Rs. {minChargeValue}, while the customer is charged Rs. {deliverychange}. To complete the delivery, you will need to cover the deficiency of Rs. {deficientDelivery}.";

                }

            }
            ltrdeliverycost.Text = "Proceed with the additional delivery cost";
            ltrcanel.Text = "Cancel the order as we can’t spend more for delivery";
            ltrmannual.Text = "Manual Delivery";
        }
        public void canellupdate(int orderId)
        {   // cancel the order
            try
            {
                List<KeyValuePair<string, object>> orderParams = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("orderId", orderId),
                    new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId)
                };
                string orderdetalis = "SELECT order_id,quor_id,quor_DeliveryMethodsAllowed,order_customer_id,br_Name,order_branch_id,order_order_id,br_Name,payment_mode,fsto_id,total,order_total_gst,order_wallet_amount,order_kfc_amount,order_total_cgst,order_total_sgst,status_id FROM qugeo_order  INNER JOIN qugeo_deliverystatus ON dls_ID = quor_Status INNER JOIN finascop_stock_transfer_order ON fsto_id = quor_TransferOrder_id INNER JOIN retaline_customer_order ON order_id = fstr_id  INNER JOIN finascop_branch ON br_ID = order_branch_id  INNER JOIN finascop_branch_group ON store_group_id = br_storeGroup WHERE order_id =@orderId and br_storeGroup=@storegroupid ";
                DataTable custItemTbl = DataServiceMySql.GetDataTable(orderdetalis, UserService.GetAPIConnectionString(), orderParams);
                DataRow dc = custItemTbl.Rows[0];


                string deleteSql = $"DELETE FROM finascop_stock_blocked WHERE order_id = @orderId";
                int status = DataServiceMySql.ExecuteSql(deleteSql, UserService.GetAPIConnectionString(), orderParams);
                double refundamt = ((Convert.ToDouble(dc["payment_mode"])) == 2 || (Convert.ToDouble(dc["payment_mode"])) == 5 ? (Convert.ToDouble(dc["total"])) : (Convert.ToDouble(dc["order_wallet_amount"])));
                string logMessage = $"Order {dc["order_order_id"]} from {dc["br_Name"]} cancelled by {this.CurrentUser.StoreGroupName} after clarification with customer due to item(s) unavailability.";
                string walletResult = Core.Services.APIService.WalletBalance(Convert.ToInt32(dc["order_customer_id"]), dc["order_id"].ToString(), refundamt, logMessage);
                string custOrderId = dc["order_order_id"].ToString();
                string inrtQry = "";
                List<KeyValuePair<string, object>> historyParams = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("orderId", dc["order_id"]),
                    new KeyValuePair<string, object>("customerId", dc["order_customer_id"]),
                     new KeyValuePair<string, object>("cancelled_by_id",this.CurrentUser.Id ),
                };
                string action = "Action By" + this.CurrentUser.APIStoreId.ToString();
                Core.Services.Order.OrderService.AddOrderHistoryData(Convert.ToInt32(orderId), 19, action);//Cancelled=19
                inrtQry += $" INSERT INTO retaline_customer_order_cancellationdets(customer_id, order_id, reason, cancelled_by_type, cancelled_by_id) VALUES(@customerId, @orderId, 'From Incomplete Orders', 3, @cancelled_by_id)";
                var histresult = DataServiceMySql.ExecuteScalar(inrtQry, UserService.GetAPIConnectionString(), historyParams);
                // Update order and related tables
                var updateParams = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("fstostatus", 15),
                    new KeyValuePair<string, object>("updatedBy", this.CurrentUser.APIStoreId),
                    new KeyValuePair<string, object>("rpbstatus", 3),
                    new KeyValuePair<string, object>("quorstatus", 40),
                    new KeyValuePair<string, object>("quor_id", dc["quor_id"]),
                    new KeyValuePair<string, object>("date", DateTime.Now),
                    new KeyValuePair<string, object>("transferOrdId", orderId)
                };
                Core.Services.Order.OrderService.UpdateOrderStatus(Convert.ToInt32(orderId), 19);//Cancelled=19
                string updtQry = "UPDATE finascop_stock_transfer_order SET fsto_status = @fstostatus, fsto_updateby = @updatedBy WHERE fsto_id = @transferOrdId; ";
                updtQry += "UPDATE finascop_stock_transfer_order_details_barcodes_temp SET rpb_status = @rpbstatus WHERE tmp_barcode_fstoId = @transferOrdId ; ";
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
                request.AddParameter("storegroup_id", this.CurrentUser.APIStoreId);
                RestResponse response = client.ExecuteAsync(request).Result;

                Console.WriteLine(response.Content);


                ShowSuccess("Order Cancelled Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">The order {custOrderId} is cancelled successfully!</a></h5>");
            }
            catch
            {
                Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
            }
        }

        public void updateshipment(int orderId)
        {
            // Order history entry
            string action = "Action By" + this.CurrentUser.APIStoreId.ToString();
            Core.Services.Order.OrderService.AddOrderHistoryData(Convert.ToInt32(orderId), 9, action);//Packed And Ready for delivery=9           
            // Stock Transfer Order update & // Qugeo Order update &
            List<KeyValuePair<string, object>> rms = new List<KeyValuePair<string, object>>();
            rms.Add(new KeyValuePair<string, object>("order_id", orderId));
            rms.Add(new KeyValuePair<string, object>("fsto_status", 22)); // 22 = Dificiency approved
            rms.Add(new KeyValuePair<string, object>("fsto_updateon", DateTime.Now));
            rms.Add(new KeyValuePair<string, object>("fsto_updateby", this.CurrentUser.APIStoreId));
            rms.Add(new KeyValuePair<string, object>("fsto_hasShipmentCreated", 3));
            //order status update            
            Core.Services.Order.OrderService.UpdateOrderStatus(Convert.ToInt32(orderId), 9);//Packed And Ready for delivery=9    
            string updatestocktranfer = "UPDATE finascop_stock_transfer_order  set fsto_status=@fsto_status,fsto_updateon=@fsto_updateon,fsto_hasShipmentCreated=@fsto_hasShipmentCreated WHERE fstr_id=@order_id ; ";
            DataServiceMySql.ExecuteScalar(updatestocktranfer, UserService.GetAPIConnectionString(), rms);
            string getid = "SELECT quor_id, fsto_id,quor_TransferOrder_id FROM `finascop_stock_transfer_order` INNER JOIN qugeo_order ON fsto_id = quor_TransferOrder_id WHERE fstr_id=@order_id";
            DataTable dt = DataServiceMySql.GetDataTable(getid, UserService.GetAPIConnectionString(), rms);
            DataRow dtid = dt.Rows[0];
            string quor_id = dtid["quor_id"].ToString();
            //update qugeo_order status
            Core.Services.Order.OrderService.UpdateQueGeoStatus(Convert.ToInt32(quor_id), 22);//Pick at Origin=22    
            ShowSuccess("Shipment is on processing.", "");
        }

        protected void SDSSlot_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }

        protected void selSlot_SelectedIndexChanged(object sender, EventArgs e)
        {
            //lblDebug.Text = "Selected Slot: " + selSlot.SelectedValue;
        }

        //protected void selCourier_SelectedIndexChanged(object sender, EventArgs e)
        //{
        //    string courierId = selCourier.SelectedValue; // Assuming the courier ID is stored as the dropdown's value
        //    if (courierId != "-1")
        //    {
        //        byte[] trackingURLData = GetTrackingURL(courierId);
        //        if (trackingURLData != null)
        //        {
        //            string trackingURL = System.Text.Encoding.UTF8.GetString(trackingURLData);
        //            txtTrackingURL.Text = trackingURL;
        //        }
        //        else
        //        {
        //            txtTrackingURL.Text = ""; // Clear the textbox if no tracking URL is available
        //        }
        //        txtTrackingURL.Enabled = false; // Make the textbox readonly
        //    }
        //    else
        //    {
        //        txtTrackingURL.Text = ""; // Clear the textbox if "Others" is selected
        //        txtTrackingURL.Enabled = true; // Enable the textbox for input
        //    }
        //}

        //public byte[] GetTrackingURL(string courierId)
        //{
        //    byte[] trackingURLData = null;
        //    if (!string.IsNullOrEmpty(courierId))
        //    {
        //        List<KeyValuePair<string, object>> courierParams = new List<KeyValuePair<string, object>>();
        //        courierParams.Add(new KeyValuePair<string, object>("courierId", courierId));
        //        DataTable dtCourierUrl = DataServiceMySql.GetDataTable($"SELECT mst_courier_url FROM mst_courier WHERE mst_courier_id = @courierId",
        //            Service.UserService.GetAPIConnectionString(), courierParams);
        //        if (dtCourierUrl != null && dtCourierUrl.Rows.Count > 0)
        //        {
        //            trackingURLData = (byte[])dtCourierUrl.Rows[0]["mst_courier_url"];
        //        }
        //    }

        //    return trackingURLData;
        //}
    }
}