using Newtonsoft.Json;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RestSharp;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Service;
using RetalineProAgent.Services;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Globalization;
using System.IO;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant
{
    public partial class MerchantDelivery : Base.BasePartnerPage
    {
        public enum DeliveryType
        {
            Manual = 6,
            Courier = 4,
            CustomerPickup = 3
        }

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

        public class StatusRule
        {
            public int FilterType { get; set; }
            public Func<int, int, int, int, GridViewRow, bool> Match { get; set; }
            public string CssClass { get; set; }
        }

        protected void btnFilterType_Click(object sender, EventArgs e)
        {
            LinkButton btn = sender as LinkButton;
            if (btn == null || string.IsNullOrEmpty(btn.Attributes["typeid"]))
            {
                Common.ShowToastifyMessage(this.Page, "Invalid Selection", "danger");
                return;
            }
            dvSlider.Visible = false;

            FilterType = Convert.ToInt32(btn.Attributes["typeid"]);
            hidFilterType.Value = FilterType.ToString();
            if (FilterType == 3 || FilterType == 7 || FilterType == 8 || FilterType == 9 || FilterType == 10 || FilterType == 11 || FilterType == 12)
            {
                dvSlider.Visible = true;
                plcInTransit.Visible = true;
                plcDelivered.Visible = false;
            }
            if (FilterType == 4 || FilterType == 13)
            {
                dvSlider.Visible = true;
                plcDelivered.Visible = true;
                plcInTransit.Visible = false;
            }
        }

        private void SetGridColumnVisibility(int filterType)
        {
            var columnVisibilityMap = new Dictionary<int, bool[]>
            {
                { 0, new[] { false, true, true, true, false, true, true, false, true } },
                { 1, new[] { false, true, true, true, false, true, true, false, true } },
                { 2, new[] { false, true, true, true, false, true, true, false, true } },
                { 3, new[] { false, true, true, true, false, true, true, false, true } },
                { 4, new[] { false, true, true, true, false, true, true, false, true } },
                { 5, new[] { false, true, true, true, false, true, true, false, true } },
                { 6, new[] { false, true, true, true, false, true, true, false, true } },
                { 7, new[] { false, true, true, true, false, true, true, false, true } },
                { 8, new[] { false, true, true, true, false, true, true, false, true } },
                { 10, new[] { false, true, true, true, false, true, true, false, true } },
                { 11, new[] { false, true, true, true, false, true, true, false, true } },
                { 12, new[] { false, true, true, true, false, true, true, false, true } },
                { 13, new[] { false, true, true, true, false, true, true, false, true } },
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
            bool isScheduleVisible = (filterType == 9);
            scheduleDeliv.Visible = isScheduleVisible;
            dvscheduleDeliv.Visible = isScheduleVisible;
            slotContainer.Visible = isScheduleVisible;

            gvPendingOrders.AllowPaging = !isScheduleVisible;
        }

        protected void Page_Load(object sender, EventArgs e)
        {
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
                        ltrBranchName.Text = branchname.Value;
                    }
                    else
                    {
                        branchname.Visible = false;
                        selBranches.Visible = true;

                        // Default to All Branches
                        ltrBranchName.Text = "All Branches";
                        Session["SelectedBranchId"] = 0;
                    }
                }
            }

            if (!IsPostBack)
            {
                if (String.IsNullOrEmpty(hidFilterType.Value))
                {
                    FilterType = 0;
                    hidFilterType.Value = "0";
                }
                else
                {
                    FilterType = Convert.ToInt32(hidFilterType.Value);
                }
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
            //lbtnAll.CssClass = String.Format("btn btn-block btn-outline-primary btn-sm {0}", (FilterType == 12 ? "active" : ""));
            //lbnpending.CssClass = String.Format("btn btn-block btn-outline-primary btn-sm {0}", (FilterType == 0 ? "active" : ""));
            //lbtndeliveryonhold.CssClass = String.Format("btn btn-inline-block btn-outline-primary mr-2 {0} ", (FilterType == 11 ? " active" : ""));
            //lbnPickUp.CssClass = String.Format("btn btn-block btn-outline-primary btn-sm {0}", (FilterType == 20 ? "active" : ""));
            ////lbtnDelivery.CssClass = String.Format("btn btn-block btn-outline-primary btn-sm {0}", (FilterType == 1 ? "active" : ""));
            ////lbtScheduleDelivery.CssClass = String.Format("btn btn-block btn-outline-primary btn-sm {0}", (FilterType == 2 ? "active" : ""));
            //lbtnDelivered.CssClass = String.Format("btn btn-block btn-outline-primary btn-sm {0}", (FilterType == 4 ? "active" : ""));
            //lbtnInTransit.CssClass = String.Format("btn btn-block btn-outline-primary btn-sm {0}", (FilterType == 5 ? "active" : ""));
            SetGridColumnVisibility(FilterType);
            SetAdditionalVisibility(FilterType);
        }

        private void TriggerFilter(string typeId)
        {
            if (!string.IsNullOrEmpty(typeId))
            {
                LinkButton dummyBtn = new LinkButton();
                dummyBtn.Attributes["typeid"] = typeId;
                btnFilterType_Click(dummyBtn, EventArgs.Empty);
            }
        }

        protected void ddlOrderStatus_SelectedIndexChanged(object sender, EventArgs e)
        {
            TriggerFilter(ddlOrderStatus.SelectedValue);
        }

        protected void ddlDelivered_SelectedIndexChanged(object sender, EventArgs e)
        {
            TriggerFilter(ddlDelivered.SelectedValue);
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
            //gvPendingOrders.PageIndex = 0;
            //gvPendingOrders.DataBind();
            //ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");
            //gvPendingOrders.PageIndex = 0;
            //gvPendingOrders.DataBind();
            //int branchId = Convert.ToInt32(selBranches.SelectedValue);
            //Session["SelectedBranchId"] = branchId;
            int branchId = 0;
            string branchName = "All Branches";

            if (selBranches.SelectedIndex > 0 &&
                !string.IsNullOrEmpty(selBranches.SelectedValue) &&
                int.TryParse(selBranches.SelectedValue, out int selected))
            {
                branchId = selected;
                branchName = selBranches.SelectedItem.Text;
            }

            // Store in session
            Session["SelectedBranchId"] = branchId;

            // Update branch label
            ltrBranchName.Text = branchName;

            // Refresh Grid
            gvPendingOrders.PageIndex = 0;
            gvPendingOrders.DataBind();
        }

        protected void selBranches_DataBound(object sender, EventArgs e)
        {

            //if (selBranches.Items.Count < 1)
            //{
            //    selBranches.DataBind();
            //}
            //plcSelectBranchModel.Visible = selBranches.Items.Count > 1;
            //ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");
            if (selBranches.Items.Count > 1)
            {
                plcSelectBranchModel.Visible = selBranches.Items.Count > 2;

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

        private static readonly List<StatusRule> Rules = new List<StatusRule>
        {
            // All Orders - Pending
            new StatusRule
            {
                FilterType = 0, // Delivered
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) => statusid == 18,
                CssClass = "delivered_items-row"
            },
            new StatusRule
            {
                FilterType = 0, // Delivery on Hold
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) => statusid == 17,
                CssClass = "delivered_items-row"
            },
            new StatusRule
            {
                FilterType = 0, // Failed
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) => statusid == 16,
                CssClass = "failed_items-row"
            },
            new StatusRule
            {
                FilterType = 0, // On Hold
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) => statusid == 55,
                CssClass = "hold_items-row"
            },
            new StatusRule
            {
                FilterType = 0, // Pending
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) =>
                {
                    if (quorStatus == null || quorStatus.Equals(DBNull.Value))
                        return false;

                    int qStatus = Convert.ToInt32(quorStatus);
                    return qStatus != 15 && qStatus != 38 && qStatus != 40;
                },
                CssClass = "pending_items-row"
            },
            new StatusRule
            {
                FilterType = 0, // Transit Modes (catch-all)
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) =>
                    (statusid >= 9 && statusid <= 15) &&
                    (deliveryMode >= 1 && deliveryMode <= 6),
                CssClass = "trasit_items-row"
            },
            new StatusRule
            {
                FilterType = 1, // Pending
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) =>
                {
                    if (quorStatus == null || quorStatus.Equals(DBNull.Value))
                        return false;

                    int qStatus = Convert.ToInt32(quorStatus);
                    return qStatus != 15 && qStatus != 38 && qStatus != 40;
                },
                CssClass = "pending_items-row"
            },
            new StatusRule
            {
                FilterType = 2, // Pickup Awaiting
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) => true,
                CssClass = "packing_items-row"
            },
            new StatusRule
            {
                FilterType = 3, // Transit Modes dropdown
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) => true,
                CssClass = "trasit_items-row"
            },
            new StatusRule
            {
                FilterType = 7, // Hyperlocal Transfer
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) => true,
                CssClass = "trasit_items-row"
            },
            new StatusRule
            {
                FilterType = 8, // Local Express Transfer
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) => true,
                CssClass = "trasit_items-row"
            },
            new StatusRule
            {
                FilterType = 9, // Scheduled Transfer
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) => true,
                CssClass = "trasit_items-row"
            },
            new StatusRule
            {
                FilterType = 10, // Courier Transfer
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) => true,
                CssClass = "trasit_items-row"
            },
            new StatusRule
            {
                FilterType = 11, // Parcel Transfer
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) => true,
                CssClass = "trasit_items-row"
            },
            new StatusRule
            {
                FilterType = 12, // Cargo Transfer
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) => true,
                CssClass = "trasit_items-row"
            },
            new StatusRule
            {
                FilterType = 4, // Delivered
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) =>
                    statusid == 18,
                CssClass = "delivered_items-row"
            },
            new StatusRule
            {
                FilterType = 13, // Delivered but not confirmed
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) =>
                    statusid == 17,
                CssClass = "delivered_items-row"
            },
            new StatusRule
            {
                FilterType = 5, // Failed
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) => true,
                CssClass = "failed_items-row"
            },
            new StatusRule
            {
                FilterType = 6, // On Hold
                //Match = (statusid, quorStatus, fstoStatus, row) =>
                //    statusid == 55,
                Match = (statusid, quorStatus, fstoStatus, deliveryMode, row) => true,
                CssClass = "hold_items-row"
            }
        };

        protected void gvPendingOrders_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            try
            {
                if (e.Row.RowType != DataControlRowType.DataRow)
                    return;

                e.Row.Attributes.Add("data-toggle", "collapse");
                e.Row.Attributes.Add("data-target", "#collapse" + e.Row.DataItemIndex);
                e.Row.Attributes.Add("aria-expanded", "false");
                e.Row.Attributes.Add("aria-controls", "collapse" + e.Row.DataItemIndex);

                int statusid = 0, quorStatus = 0, fstoStatus = 0, deliveryMode = 0;
                int.TryParse(Convert.ToString(DataBinder.Eval(e.Row.DataItem, "status_id")), out statusid);
                int.TryParse(Convert.ToString(DataBinder.Eval(e.Row.DataItem, "quor_Status")), out quorStatus);
                int.TryParse(Convert.ToString(DataBinder.Eval(e.Row.DataItem, "fsto_status")), out fstoStatus);
                int.TryParse(Convert.ToString(DataBinder.Eval(e.Row.DataItem, "rdr_deliveryMode")), out deliveryMode);

                StatusRule rule = null;

                if (FilterType == 0)
                {
                    // Pick the first matching rule only for All Orders
                    rule = Rules.FirstOrDefault(r => r.FilterType == 0 && r.Match(statusid, quorStatus, fstoStatus, deliveryMode, e.Row));
                }
                else
                {
                    // Pick the first matching rule for the selected filter
                    rule = Rules.FirstOrDefault(r => r.FilterType == FilterType && r.Match(statusid, quorStatus, fstoStatus, deliveryMode, e.Row));
                }

                if (rule != null && !string.IsNullOrEmpty(rule.CssClass))
                {
                    e.Row.CssClass = rule.CssClass; 
                }
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "An error occurred: " + ex.Message, "danger");
            }
        }

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
                        Common.ShowCustomAlert(this.Page, "Failure", "Invalid order or the process cannot execute at this time.", false, "/Tenant/MerchantDelivery");
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

                    bool manualdelivery = ManualDelivery(order_id, quor_id, txtDelivBoy.Text, txtDate.Text);
                    if (manualdelivery)
                    {
                        UpdateOrderAndHistory(order_id, quor_id, DeliveryType.Manual, 15, 18, quorDeliveryConfTime, quorDeliveryConfTime, quorDeliveryConfTime, txtDate.Text);
                    }
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
                        Common.ShowCustomAlert(this.Page, "Failure", "Invalid order or the process cannot execute at this time.", false, "/Tenant/MerchantDelivery");
                        return;
                    }
                    string name = custTbl.Rows[0]["cust_customer_name"].ToString();
                    string storeName = custTbl.Rows[0]["store_group_name"].ToString();
                    string email = custTbl.Rows[0]["cust_email"].ToString();
                    var emailresult = Core.Services.APIService.OrdDelivConfEmail(name, email, storeName, ordNum);

                    try
                    {
                        TriggerFinascopApi(order_id, "078024a3-38d7-11ee-9967-065723bafb24");
                        TriggerFinascopApi(order_id, "07802530-38d7-11ee-9967-065723bafb24");
                    }
                    catch (Exception ex)
                    {
                        string strError = ex.Message;
                    }
                }
            }
            Common.ShowCustomAlert(this.Page, "Delivered Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your items delivered successfully!</a></h5>", true, "/Tenant/MerchantDelivery");
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

            }
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
        protected void SDSSlot_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            e.Command.Parameters["storegroupid"].Value = this.CurrentUser.APIStoreId;
        }

        protected void selSlot_SelectedIndexChanged(object sender, EventArgs e)
        {

        }
        public string GetShippingLabelUrl(string orderId)
        {
            var orderparams = new List<KeyValuePair<string, object>>
            {
               new KeyValuePair<string, object>("orderId", orderId)
            };

            var orderTbl = DataServiceMySql.GetDataTable(
                "SELECT shipment_label FROM shipping_consignment WHERE order_id = @orderId",
                UserService.GetAPIConnectionString(), orderparams);

            string shippingUrl = orderTbl?.Rows.Count > 0
                ? orderTbl.Rows[0]["shipment_label"].ToString()
                : generateshippingurl(orderId);

            return string.IsNullOrEmpty(shippingUrl) ? string.Empty : shippingUrl;
        }
        public string generateshippingurl(string orderId)
        {
            string body = string.Empty;
            var result = GenerateorderDetalies(orderId.ToString());
            List<KeyValuePair<string, object>> Sqlprms = new List<KeyValuePair<string, object>>();
            Sqlprms.Add(new KeyValuePair<string, object>("orderid", orderId));
            string sqlOrder = "SELECT rc.order_order_id,rc.order_confirm_date,rc.total,rc.created_at,br_Name FROM retaline_customer_order rc INNER JOIN finascop_branch fb  ON  fb.br_ID =rc.order_branch_id where order_order_id=@orderid";
            DataTable drOrderInfo = DataServiceMySql.GetDataTable(sqlOrder, Service.UserService.GetAPIConnectionString(), Sqlprms);
            List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
            replacements.Add(new KeyValuePair<string, string>("[StoreName]", drOrderInfo.Rows[0]["br_name"].ToString()));
            replacements.Add(new KeyValuePair<string, string>("[Ordernumber]", drOrderInfo.Rows[0]["order_order_id"].ToString()));
            DateTime orderDate = DateTime.Parse(drOrderInfo.Rows[0]["created_at"].ToString());
            string formattedordereDate = orderDate.ToString("dd MMM yyyy");
            replacements.Add(new KeyValuePair<string, string>("[date]", formattedordereDate));
            replacements.Add(new KeyValuePair<string, string>("[saleorderamount]", drOrderInfo.Rows[0]["total"].ToString()));
            DateTime orderConfirmDate = DateTime.Parse(drOrderInfo.Rows[0]["order_confirm_date"].ToString());
            string formattedDate = orderConfirmDate.ToString("dd MMM yyyy");
            replacements.Add(new KeyValuePair<string, string>("[saleconfirmed]", formattedDate));
            replacements.Add(new KeyValuePair<string, string>("[totalitem]", result.itemQty.ToString()));
            replacements.Add(new KeyValuePair<string, string>("[itemcount]", result.itemCount.ToString()));
            replacements.Add(new KeyValuePair<string, string>("[OrderDetalis]", result.strItemBody));
            body = EmailService.CreateEmailbody(EmailType.orderslip, replacements);
            string orderBody = body.Replace("'", "\\'").Replace("\n", "").Replace("\r", "");
            string fileName = $"shippinglabel-{orderId}.html";
            string filePath = HttpContext.Current.Server.MapPath($"~/ShippingLabels/{fileName}");
            string directoryPath = Path.GetDirectoryName(filePath);
            if (!Directory.Exists(directoryPath))
                Directory.CreateDirectory(directoryPath);
            File.WriteAllText(filePath, orderBody);
            string baseUrl = HttpContext.Current.Request.Url.GetLeftPart(UriPartial.Authority);
            return $"{baseUrl}/ShippingLabels/{fileName}";
        }
        public (string strItemBody, int itemCount, int itemQty) GenerateorderDetalies(string order_id)
        {
            int serialNumber = 1;
            List<KeyValuePair<string, object>> orderSqlprms = new List<KeyValuePair<string, object>>();
            orderSqlprms.Add(new KeyValuePair<string, object>("orderid", order_id));
            string itemdetails = $"SELECT customer_order_id, hasRestaurantService,item_sales_price ,order_item_basket_price_et,IFNULL((SELECT fsi.fsipc_code FROM finascop_stock_itemmaster_product_codes fsi" +
                $" WHERE fsi.fsipc_stit_id = fs.stit_ID  AND(fsi.fsipc_store = fb.br_ID OR fsipc_isCompany = 1) ORDER BY fsipc_store DESC LIMIT 1),'Not Applicable') " +
                $"AS itemcode, order_item_mrp_et, stit_SKU, item_sales_price, order_item_mrp, IFNULL(item_order_qty, 0) AS item_order_qty, " +
                $" item_price  FROM retaline_customer_order re INNER JOIN retaline_customer_order_items ro ON re.order_id = ro.customer_order_id" +
                $" INNER JOIN finascop_stock_itemmaster fs ON ro.item_product_id = fs.stit_ID INNER JOIN mypha_productsubcategory mp ON fs.product_category = mp.sub_category_id" +
                $" INNER JOIN finascop_branch fb ON ro.order_branch_id = fb.br_ID WHERE order_order_id=@orderid";
            DataTable dtitems = DataServiceMySql.GetDataTable(itemdetails, Service.UserService.GetAPIConnectionString(), orderSqlprms);
            if (dtitems == null || dtitems.Rows.Count <= 0)
                return ("", 0, 0);
            // Calculate item quantity and count
            int itemQty = dtitems.AsEnumerable().Sum(r => Convert.ToInt32(r["item_order_qty"]));
            int itemcount = dtitems.Rows.Count;
            StringBuilder sbItems = new StringBuilder();

            foreach (DataRow dritem in dtitems.Rows)
            {
                decimal orderItemMRP = Convert.ToDecimal(dritem["order_item_mrp"]);
                decimal itemSalesPrice = Convert.ToDecimal(dritem["item_sales_price"]);
                decimal discount = orderItemMRP - itemSalesPrice;
                string Discount = discount.ToString("0.00");

                // List of replacements for template placeholders
                List<KeyValuePair<string, string>> childItemReplacements = new List<KeyValuePair<string, string>>
                    {
                        new KeyValuePair<string, string>("[ItemName]", dritem["stit_SKU"].ToString()),
                        new KeyValuePair<string, string>("[SellingPrice]", itemSalesPrice.ToString("0.00")),
                        new KeyValuePair<string, string>("[Barcode]", dritem["itemcode"].ToString()),
                        new KeyValuePair<string, string>("[MRP]", orderItemMRP.ToString("0.00")),
                        new KeyValuePair<string, string>("[Quatity]", dritem["item_order_qty"].ToString()),
                         new KeyValuePair<string, string>("[NO]", serialNumber.ToString()),
                    };

                // Generate the item body using the replacements
                string strItemBody = EmailService.CreateEmailbody(EmailType.Productdetalis, childItemReplacements);
                sbItems.Append(strItemBody);
                serialNumber++;
            }

            return (sbItems.ToString(), itemcount, itemQty);
        }

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

            Common.ShowCustomAlert(this.Page, "Manually Scheduled!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Your order is manually scheduled!</a></h5>", true, "/Tenant/MerchantDelivery");
        }

        protected void btnconformdelivery_Click(object sender, EventArgs e)
        {
            string orderId = hdndelieveryorderid.Value;
            string quorId = hdndeliveryquorid.Value;

            if (string.IsNullOrWhiteSpace(orderId) || string.IsNullOrWhiteSpace(quorId))
            {
                ShowError("Invalid order or not accessible");
                return;
            }

            bool isDispatched = false;
            // Handle delivery method
            if (rdbyperson.Checked)
                isDispatched = ManualDelivery(orderId, quorId, txtpersonsname.Text, txtdelievrydate.Text, false);
            else if (rdbycourier.Checked)
                isDispatched = CourierDelivery(orderId, quorId, txtpersonsname.Text, txtdelievrydate.Text, txttrackingnumber.Text, txtdeliverytrackingurl.Text, false);
            else if (rdcustomerpickup.Checked)
            {
                if (CustomerPickup(orderId, quorId))
                {
                    sendmail(orderId);
                    TriggerFinascopApi(orderId, "078024a3-38d7-11ee-9967-065723bafb24");
                    TriggerFinascopApi(orderId, "07802530-38d7-11ee-9967-065723bafb24");
                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                    prms.Add(new KeyValuePair<string, object>("p_order_id", orderId));
                    prms.Add(new KeyValuePair<string, object>("p_quor_id", quorId));
                    prms.Add(new KeyValuePair<string, object>("current_datetime", txtdevtime.Text));
                    DataServiceMySql.ExecuteSP("UpdateDeliveryStatus", UserService.GetAPIConnectionString(), prms);
                    ShowSuccess("Delivery Successful!", "The order has been delivered successfully.", "/Tenant/MerchantDelivery");
                }
                else ShowError("Could not complete customer pickup.");
                return;
            }
            else
            {
                ShowError("Please select a delivery option");
                return;
            }

            if (isDispatched)
            {
                TriggerFinascopApi(orderId, "078024a3-38d7-11ee-9967-065723bafb24");
                ShowSuccess("Order Dispatched!", "Your order is dispatched!", "/Tenant/MerchantDelivery");
            }
            else
            {
                ShowError("Could not dispatch the order.");
            }

        }

        private void ShowSuccess(string title, string message, string redirectUrl)
        {
            string html = $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">{message}</a></h5>";
            Common.ShowCustomAlert(this.Page, title, html, true, redirectUrl);
        }

        private void ShowError(string message)
        {
            Common.ShowCustomAlert(this.Page, "Error", message, false, "/Tenant/MerchantDelivery");
        }

        /// <summary>
        /// Triggers the Finascop API with given order and event reference.
        /// </summary>
        /// <param name="orderId">Order ID to dispatch</param>
        /// <param name="eventRefId">Finascop Event Reference ID</param>
        private void TriggerFinascopApi(string orderId, string eventRefId)
        {
            // Get API URL from config or fallback to default
            string url = ConfigurationSettings.AppSettings["api.url"];
            if (string.IsNullOrWhiteSpace(url))
            {
                url = "http://bizapi.dev.grozeo.in";
            }

            // Prepare RestClient and request
            var client = new RestClient(new RestClientOptions(url));
            var request = new RestRequest("/api/finascop/finascopPostingService", Method.Post)
            {
                AlwaysMultipartFormData = true
            };

            // Add required parameters
            request.AddParameter("order_id", orderId);
            request.AddParameter("finascopEventRefId", eventRefId);
            request.AddParameter("storegroup_id", this.CurrentUser.APIStoreId);

            // Execute request synchronously
            RestResponse response = client.ExecuteAsync(request).Result;
        }
        private void UpdateOrderAndHistory(string customerOrderId, string quorid, DeliveryType type, int status, int cstatus, string dispatchDate, string dispatchTime, string now, string date)
        {
            var orderParams = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("quorDeliveryConfTime", date),
                new KeyValuePair<string, object>("quorUpdateOn", dispatchDate),
                new KeyValuePair<string, object>("quorid", quorid),
                new KeyValuePair<string, object>("status", status),
                new KeyValuePair<string, object>("type", (int)type)
            };

            // 1. Update qugeo_order table
            string updateOrder = "UPDATE qugeo_order SET quor_Type = @type, quor_DeliveryConfTime = @quorDeliveryConfTime, quor_Status = @status, quor_UpdateOn = @quorUpdateOn WHERE quor_id = @quorid";
            DataServiceMySql.ExecuteSql(updateOrder, UserService.GetAPIConnectionString(), orderParams);
            var customerParams = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("updateat", now),
                new KeyValuePair<string, object>("orderid", customerOrderId),
                new KeyValuePair<string, object>("cstatus", cstatus)
            };
            // 2. Update retaline_customer_order
            string updateCustomer = "UPDATE retaline_customer_order SET status_id = @cstatus, order_status_addinfo = '###2', payment_mode = IF(payment_mode = 1, '1', payment_mode), order_ondel_bankref_id = '###7', updated_at = @updateat WHERE order_id = @orderid";
            DataServiceMySql.ExecuteSql(updateCustomer, UserService.GetAPIConnectionString(), customerParams);

            // 3. Insert retaline_customer_order_history
            string insertHistory = "INSERT INTO retaline_customer_order_history (order_id, order_status, created_at, updated_at) VALUES(@orderid,@cstatus, @updateat, @updateat)";
            DataServiceMySql.ExecuteSql(insertHistory, UserService.GetAPIConnectionString(), customerParams);
        }

        /// <summary>
        /// Handles courier delivery process.
        /// </summary>
        protected bool CourierDelivery(string customerOrderId, string quorid, string name, string date, string trackingNumber, string trackingURL, bool isUpdate = false)
        {
            if (!DateTime.TryParse(date, out DateTime dispatchDateTime) || string.IsNullOrWhiteSpace(trackingNumber) || string.IsNullOrWhiteSpace(trackingURL))
            {
                return false;
            }
            string dispatchDate = dispatchDateTime.ToString("yyyy-MM-dd");
            string dispatchTime = dispatchDateTime.ToString("HH:mm:ss", CultureInfo.InvariantCulture);
            string now = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
            try
            {
                var param = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("courier",name ),
                    new KeyValuePair<string, object>("trackingNumber", trackingNumber),
                    new KeyValuePair<string, object>("dispatchDate", dispatchDate),
                    new KeyValuePair<string, object>("dispatchTime", dispatchTime),
                    new KeyValuePair<string, object>("orderId", quorid),
                    new KeyValuePair<string, object>("trackingURL", trackingURL)
                };
                // Update existing courier record
                string sqlCourier = isUpdate ? @"UPDATE qugeo_order_courier SET qoc_qcn = @trackingNumber, qoc_date = @dispatchDate, qoc_time = @dispatchTime,qoc_trackingUrl = @trackingURL,provider = @courier WHERE quor_id = @orderId"
                                               : @"INSERT INTO qugeo_order_courier  (provider, qoc_qcn, qoc_date, qoc_time, quor_id, qoc_trackingUrl) VALUES (@courier, @trackingNumber, @dispatchDate, @dispatchTime, @orderId, @trackingURL)";
                DataServiceMySql.ExecuteSql(sqlCourier, UserService.GetAPIConnectionString(), param);
                int orderStatus = isUpdate ? 15 : 9;
                int orderHistoryStatus = isUpdate ? 18 : 15;
                UpdateOrderAndHistory(customerOrderId, quorid, DeliveryType.Courier, orderStatus, orderHistoryStatus, dispatchDate, dispatchTime, now, date);
                return true;

            }
            catch (Exception ex)
            {
                return false;
            }
        }
        /// <summary>
        /// Handles manual delivery process.
        /// </summary>
        protected bool ManualDelivery(string customerOrderId, string quorid, string name, string deliverydate, bool isUpdate = false)
        {
            const int CreatedBy = 1;
            if (!DateTime.TryParse(deliverydate, out DateTime dispatchDateTime) || string.IsNullOrWhiteSpace(name) || string.IsNullOrWhiteSpace(deliverydate))
            {
                return false;
            }
            string dispatchDate = dispatchDateTime.ToString("yyyy-MM-dd");
            string dispatchTime = dispatchDateTime.ToString("HH:mm:ss", CultureInfo.InvariantCulture);
            string now = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
            // 1. Insert manual delivery record
            try
            {
                var deliveryParams = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("qmddeliveredBy",name),
                    new KeyValuePair<string, object>("qmdDate", dispatchDate),
                    new KeyValuePair<string, object>("qmdTime", dispatchTime),
                    new KeyValuePair<string, object>("quorid", quorid),
                    new KeyValuePair<string, object>("qmdcreatedOn", now),
                    new KeyValuePair<string, object>("CreatedBy", CreatedBy)
                };
                string insertDelivery = isUpdate ? @"UPDATE qugeo_manual_deliver SET qmd_deliveredBy = @qmddeliveredBy,qmd_Date = @qmdDate, qmd_Time = @qmdTime,qmd_updatedOn = @qmdcreatedOn,qmd_updatedBy = @CreatedBy WHERE quor_id = @quorid"
                                                 : @"INSERT INTO qugeo_manual_deliver (qmd_deliveredBy, qmd_Date, qmd_Time, quor_id, qmd_createdOn, qmd_createdBy) VALUES(@qmddeliveredBy, @qmdDate, @qmdTime, @quorid, @qmdcreatedOn, @CreatedBy)";

                DataServiceMySql.ExecuteSql(insertDelivery, UserService.GetAPIConnectionString(), deliveryParams);
                int orderStatus = isUpdate ? 15 : 9;
                int orderHistoryStatus = isUpdate ? 18 : 15;
                // 2. Update order status/type,Update customer order and Insert order history
                UpdateOrderAndHistory(customerOrderId, quorid, DeliveryType.Manual, orderStatus, orderHistoryStatus, dispatchDate, dispatchTime, now, deliverydate);
                return true;
            }
            catch
            {
                return false;
            }
        }
        /// <summary>
        /// Handles customer pickup delivery process.
        /// </summary>
        protected bool CustomerPickup(string customerOrderId, string quorid)
        {
            const int CreatedBy = 1;
            if (!DateTime.TryParse(txtdelievrydate.Text, out DateTime dispatchDateTime) || string.IsNullOrWhiteSpace(txtpersonsname.Text) || string.IsNullOrWhiteSpace(txtdelievrydate.Text))
            {
                return false;
            }
            string dispatchDate = dispatchDateTime.ToString("yyyy-MM-dd");
            string dispatchTime = dispatchDateTime.ToString("HH:mm:ss", CultureInfo.InvariantCulture);
            string now = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss");
            try
            {
                // 1. Insert customer pickup record
                var pickupParams = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("qcp_pickupBy", txtpersonsname.Text),
                    new KeyValuePair<string, object>("qcp_pickupDate", dispatchDate),
                    new KeyValuePair<string, object>("qcp_pickupTime", dispatchTime),
                    new KeyValuePair<string, object>("qcp_createdOn", now),
                    new KeyValuePair<string, object>("qcp_createdBy", CreatedBy)
                };
                string pickupSql = "INSERT INTO qugeo_customer_pickup (qcp_pickupBy, qcp_pickupDate, qcp_pickupTime, qcp_createdOn, qcp_createdBy) VALUES(@qcp_pickupBy,@qcp_pickupDate,@qcp_pickupTime,@qcp_createdOn,@qcp_createdBy)";
                DataServiceMySql.ExecuteSql(pickupSql, UserService.GetAPIConnectionString(), pickupParams);
                // 2. Update order status/type,Update customer order and Insert order history
                UpdateOrderAndHistory(customerOrderId, quorid, DeliveryType.CustomerPickup, 15, 18, dispatchDate, dispatchTime, now, txtdelievrydate.Text);
                return true;
            }
            catch
            {
                return false;
            }
        }

        protected void btndeliveryupdate_Click1(object sender, EventArgs e)
        {
            Button lbtn = (Button)sender;
            int order_id = Convert.ToInt32(lbtn.Attributes["orderId"]);
            hdndelieveryorderid.Value = order_id.ToString();
            int quorId = Convert.ToInt32(lbtn.Attributes["quorId"]);
            hdndeliveryquorid.Value = quorId.ToString();
            var toparams = new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("quorId", quorId) };
            DataTable deliveryinfo = DataServiceMySql.GetDataTable(@" SELECT qmd_deliveredBy, qmd_Date, qmd_Time, qo.quor_id, qc.qoc_date, qc.provider, qc.qoc_trackingUrl, qc.qoc_qcn, qc.qoc_time, qo.quor_Type FROM qugeo_order qo LEFT JOIN qugeo_manual_deliver qm ON qm.quor_id = qo.quor_id LEFT JOIN qugeo_order_courier qc ON qc.quor_id = qo.quor_id WHERE qo.quor_id = @quorId", UserService.GetAPIConnectionString(), toparams);
            if (deliveryinfo == null || deliveryinfo.Rows.Count == 0)
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Invalid order or the process cannot execute at this time.", false, "/Tenant/MerchantDelivery");
                return;
            }
            DataRow row = deliveryinfo.Rows[0];
            int deliveryType = row["quor_Type"] != DBNull.Value ? Convert.ToInt32(row["quor_Type"]) : 0;
            // Shared date and time formatter
            string FormatDate(object dateObj) => dateObj != DBNull.Value ? Convert.ToDateTime(dateObj).ToString("yyyy-MM-dd") : "";
            string FormatString(object val) => val?.ToString() ?? "";
            if (deliveryType == 6) // Manual
            {
                rddevbyperson.Checked = true;
                txtdevname.Text = FormatString(row["qmd_deliveredBy"]);
                txtdevtime.Text = $"{FormatDate(row["qmd_Date"])} {FormatString(row["qmd_Time"])}".Trim();
            }
            else if (deliveryType == 4) // Courier
            {
                rddevbycourier.Checked = true;
                txtdevname.Text = FormatString(row["provider"]);
                txtdevtime.Text = $"{FormatDate(row["qoc_date"])} {FormatString(row["qoc_time"])}".Trim();
                txtdevtrackingurl.Text = FormatString(row["qoc_trackingUrl"]);
                txtdevtrackingno.Text = FormatString(row["qoc_qcn"]);
            }

            string strAlertSCript = "$('#modalmerchantdelivery').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void btnmerchantdeliveryconfirm_Click(object sender, EventArgs e)
        {
            string orderid = hdndelieveryorderid.Value;
            string quorid = hdndeliveryquorid.Value;
            bool isdelivered = false;
            // Handle delivery method
            if (rddevbyperson.Checked)
            {
                isdelivered = ManualDelivery(orderid, quorid, txtdevname.Text, txtdevtime.Text, true);
                TriggerFinascopApi(orderid, "07802530-38d7-11ee-9967-065723bafb24");
            }
            else if (rddevbycourier.Checked)
            {
                isdelivered = CourierDelivery(orderid, quorid, txtdevname.Text, txtdevtime.Text, txtdevtrackingno.Text, txtdevtrackingurl.Text, true);
                TriggerFinascopApi(orderid, "07802530-38d7-11ee-9967-065723bafb24");
            }
            if (isdelivered)
            {
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("p_order_id", orderid));
                prms.Add(new KeyValuePair<string, object>("p_quor_id", quorid));
                prms.Add(new KeyValuePair<string, object>("current_datetime", txtdevtime.Text));
                DataServiceMySql.ExecuteSP("UpdateDeliveryStatus", UserService.GetAPIConnectionString(), prms);
                sendmail(orderid);
                ShowSuccess("Delivery Successful!", "The order has been delivered successfully.", "/Tenant/MerchantDelivery");
            }
            else
                ShowError("Could not delivered the order.");
        }

        private void sendmail(string orederid)
        {
            List<KeyValuePair<string, object>> custparams = new List<KeyValuePair<string, object>>();
            custparams.Add(new KeyValuePair<string, object>("orderId", orederid));
            custparams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));
            var custTbl = DataServiceMySql.GetDataTable($"SELECT order_order_id, order_customer_id, cust_email, co.storegroup_id, store_group_name, cust_mobile, cust_customer_name FROM retaline_customer_order co INNER JOIN finascop_branch_group ON store_group_id = co.storegroup_id INNER JOIN retaline_customer ON cust_id = order_customer_id WHERE order_id = @orderId",
                    UserService.GetAPIConnectionString(), custparams);
            if (custTbl == null || custTbl.Rows.Count <= 0)
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Invalid order or the process cannot execute at this time.", false, "/Tenant/MerchantDelivery");
                return;
            }
            string name = custTbl.Rows[0]["cust_customer_name"].ToString();
            string storeName = custTbl.Rows[0]["store_group_name"].ToString();
            string email = custTbl.Rows[0]["cust_email"].ToString();
            string orderid = custTbl.Rows[0]["order_order_id"].ToString();
            var emailresult = Core.Services.APIService.OrdDelivConfEmail(name, email, storeName, orderid);
        }

        protected void gvPendingOrders_RowCommand(object sender, GridViewCommandEventArgs e)
        {
            if (e.CommandName == "GenerateLabel")
            {
                string orderId = e.CommandArgument.ToString();
                string labelUrl = GetShippingLabelUrl(orderId);
                ScriptManager.RegisterStartupScript(this, this.GetType(), "openLabel", $"window.open('{labelUrl}', '_blank');", true);
            }
        }

        protected void btnadmincancel_Click(object sender, EventArgs e)
        {
            LinkButton row = (LinkButton)sender;
            string Transferorderid = Convert.ToString(row.Attributes["fsto_id"]);
            string toid = Convert.ToString(row.Attributes["fsto_uid"]);
            string orderId = Convert.ToString(row.Attributes["order_id"]);
            string ordre_orderid = Convert.ToString(row.Attributes["order_order_id"]);
            CancelOrder(orderId, Transferorderid);
            Common.ShowCustomAlert(this.Page, "Order Cancelled Successfully!",
                $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">The order {ordre_orderid} is cancelled successfully!</a></h5>",
                true, "/Tenant/MerchantDelivery");
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

                var customerOrderDatas = new List<KeyValuePair<string, object>>
                {
                     new KeyValuePair<string, object>("orderId", customerOrderData["order_id"].ToString()),
                };
                DataServiceMySql.ExecuteSql("DELETE FROM finascop_stock_blocked WHERE order_id = @orderId", UserService.GetAPIConnectionString(), customerOrderDatas);

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
                //step 9 ;update qugodelivery table
                Updatedeliverystatus(transferOrderId, 40);
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

        private DataRow GetSingleRowFromQuery(string query, List<KeyValuePair<string, object>> parameters)
        {
            DataTable table = DataServiceMySql.GetDataTable(query, UserService.GetAPIConnectionString(), parameters);
            return table.Rows.Count > 0 ? table.Rows[0] : null;
        }
        private void UpdateWalletBalance(DataRow data)
        {
            string refentryId = data["order_id"].ToString();
            int paymentMode = data["payment_mode"] != DBNull.Value ? Convert.ToInt32(data["payment_mode"]) : 0;
            string paymentgateway = data["order_payment_gateway"] != DBNull.Value ? (data["order_payment_gateway"]).ToString() : " ";
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

            var OrderDatas = new List<KeyValuePair<string, object>>
            {
                  new KeyValuePair<string, object>("orderId", orderId),
                    new KeyValuePair<string, object>("action", action),
                    new KeyValuePair<string, object>("status", status)
            };
            DataServiceMySql.ExecuteSql(query, UserService.GetAPIConnectionString(), OrderDatas);
        }
        private void UpdateOrderStatusRetalinecustomerorder(string orderId, int status)
        {
            string query = "UPDATE retaline_customer_order SET status_id = @status WHERE order_id = @orderId";
            var OrderDatas = new List<KeyValuePair<string, object>>
            {
                 new KeyValuePair<string, object>("status", status),
                new KeyValuePair<string, object>("orderId", orderId)
            };
            DataServiceMySql.ExecuteSql(query, UserService.GetAPIConnectionString(), OrderDatas);

        }

        private void InsertCancellationDetails(string customerId, string orderId, string reason)
        {
            string query = "INSERT INTO retaline_customer_order_cancellationdets(customer_id, order_id, reason, cancelled_by_type, cancelled_by_id) " +
                           "VALUES(@customerId, @orderId, @reason, @cancelledByType, @cancelledById)";
            var OrderDatas = new List<KeyValuePair<string, object>>
            {
                 new KeyValuePair<string, object>("customerId", customerId),
                new KeyValuePair<string, object>("orderId", orderId),
                new KeyValuePair<string, object>("reason", reason),
                new KeyValuePair<string, object>("cancelledByType", 3),
                new KeyValuePair<string, object>("cancelledById", this.CurrentUser.Id)
            };
            DataServiceMySql.ExecuteSql(query, UserService.GetAPIConnectionString(), OrderDatas);

        }

        private void UpdateStockTransferOrder(string orderId, int status, int updatedBy)
        {
            string query = "UPDATE finascop_stock_transfer_order SET fsto_status = @status, fsto_updateby = @updatedBy WHERE fsto_id = @transferOrdId";
            var OrderDatas = new List<KeyValuePair<string, object>>
            {
                 new KeyValuePair<string, object>("status", status),
                new KeyValuePair<string, object>("updatedBy", updatedBy),
                new KeyValuePair<string, object>("transferOrdId", orderId)
            };
            DataServiceMySql.ExecuteSql(query, UserService.GetAPIConnectionString(), OrderDatas);
        }

        private void Updatedeliverystatus(string tranferorder, int status)
        {
            string query = "Update `qugeo_order` SET quor_Status=@status where quor_TransferOrder_id=@tranferorder";
            var OrderDatas = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("status", status),
                new KeyValuePair<string, object>("tranferorder", tranferorder)
            };
            DataServiceMySql.ExecuteSql(query, UserService.GetAPIConnectionString(), OrderDatas);
        }

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
                }
            }
            catch
            {

            }
        }
        #endregion
    }
}