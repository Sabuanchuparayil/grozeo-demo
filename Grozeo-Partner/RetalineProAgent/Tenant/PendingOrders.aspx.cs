using MySql.Data.MySqlClient;
using NPOI.SS.UserModel;
using NPOI.XSSF.UserModel;
using RetalineProAgent.Core.Services;
//using RetalineProAgent.Core.BussinessModel.OnlineOrders;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Drawing;
using System.IO;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class PendingOrders : Base.BasePartnerPage
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

        public class StatusRule
        {
            public int FilterType { get; set; }
            public Func<int, int, GridViewRow, bool> Match { get; set; }
            public string CssClass { get; set; }
        }

        protected void btnFilterType_Click(object sender, EventArgs e)
        {
            try
            {
                if (!(sender is LinkButton btn) || string.IsNullOrEmpty(btn.Attributes["typeid"]))
                    return;

                int filterType = Convert.ToInt32(btn.Attributes["typeid"]);
                FilterType = filterType;
                hidFilterType.Value = filterType.ToString();

                // Reset visibility defaults
                dvSlider.Visible = false;
                plcDelivered.Visible = false;
                plcInTransit.Visible = false;

                // Handle visibility based on filter type
                switch (filterType)
                {
                    // All Orders
                    case 0:
                    case 10:
                    case 11:
                        dvSlider.Visible = true;
                        plcDelivered.Visible = true;
                        break;

                    // Processing
                    case 3:
                    case 7:
                    case 8:
                    case 9:
                        dvSlider.Visible = true;
                        plcInTransit.Visible = true;
                        break;
                }

                // Handle GridView column visibility
                bool hideColumn = filterType == 1 ||
                                  filterType == 2 ||
                                  filterType == 3 ||
                                  filterType == 4 ||
                                  filterType == 5 ||
                                  filterType == 11;

                gvPendingOrders.Columns[4].Visible = !hideColumn ? true : false;
            }
            catch (Exception ex)
            {
                Common.ShowToastifyMessage(this.Page, "Error: " + ex.Message, "danger");
            }
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

            if (!IsPostBack && String.IsNullOrEmpty(hidFilterType.Value))
            {

                FilterType = 10; hidFilterType.Value = "10";
                dvSlider.Visible = true;
                plcDelivered.Visible = true;
                //ltrTitle.Text = "Pending Orders";
                txtDateFrom.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
                txtDateTo.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            }

            if (gvPendingOrders.HeaderRow != null)
                gvPendingOrders.HeaderRow.TableSection = TableRowSection.TableHeader;
        }

        protected void selBranches_SelectedIndexChanged(object sender, EventArgs e)
        {
            //gvPendingOrders.PageIndex = 0;
            //gvPendingOrders.DataBind();
            //ltrBranchName.Text = (selBranches.SelectedIndex >= 0 ? selBranches.SelectedItem.Text : "");
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

        protected void Page_PreRender(object sender, EventArgs e)
        {
            ////lbtnAll.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType == 8 ? "active" : ""));
            //lbtnnewjob.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType == 1 ? "active" : ""));
            ////lbtAccepted.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType == 10 ? "active" : ""));
            //lbtnPendingorder.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType == 2 ? "active" : ""));
            ////lbtnProcessing.CssClass = String.Format("nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 {0}", (FilterType == 3 ? "active" : ""));
            ////lbtTobepacked.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType == 4 ? "active" : ""));
            //lbtOnhold.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType == 5 ? "active" : ""));
            //lbtnPacked.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType == 6 ? "active" : ""));
            ////lbtnCancel.CssClass = String.Format("nav-link btn btn-block btn-outline-primary btn-sm p-1 px-2 {0}", (FilterType == 7 ? "active" : ""));
            //lbrerack.CssClass = String.Format("btn btn-block btn-outline-primary {0}", (FilterType == 9 ? "active" : ""));
        }

        protected void ddlOrderStatus_SelectedIndexChanged(object sender, EventArgs e)
        {
            string typeId = ddlOrderStatus.SelectedValue;

            if (!string.IsNullOrEmpty(typeId))
            {
                // Create a fake LinkButton to reuse btnFilterType_Click
                LinkButton dummyBtn = new LinkButton();
                dummyBtn.Attributes["typeid"] = typeId;

                // Call your existing handler
                btnFilterType_Click(dummyBtn, EventArgs.Empty);
            }
        }

        protected void ddlSuccessfulOrders_SelectedIndexChanged(object sender, EventArgs e)
        {
            string typeId = ddlSuccessfulOrders.SelectedValue;

            if (!string.IsNullOrEmpty(typeId))
            {
                // Create a fake LinkButton to reuse btnFilterType_Click
                LinkButton dummyBtn = new LinkButton();
                dummyBtn.Attributes["typeid"] = typeId;

                // Call your existing handler
                btnFilterType_Click(dummyBtn, EventArgs.Empty);
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
            Response.AddHeader("content-disposition", "attachment;filename=AllOrders.xlsx");
            wb.Write(Response.OutputStream);

            Response.Flush();
            Response.End();

        }

        protected void lbtnDownloadExcel_Click(object sender, EventArgs e)
        {
            ExportGridToExcel();
        }

        protected void btnRevoke_Click(object sender, EventArgs e)
        {
            Button btnRevoke = (Button)sender;

            string orderId = Convert.ToString(btnRevoke.Attributes["transferOrderId"]);
            List<KeyValuePair<string, object>> sqlId = new List<KeyValuePair<string, object>>();
            sqlId.Add(new KeyValuePair<string, object>("toId", orderId));
            var boyId = "SELECT id,boy_id,order_pk_id FROM retaline_godown_boy_orders_request WHERE order_pk_id=@toId";
            DataTable orderBoyId = DataServiceMySql.GetDataTable(boyId, Service.UserService.GetAPIConnectionString(), sqlId);
            if (btnRevoke == null || String.IsNullOrEmpty(btnRevoke.Attributes["transferOrderId"]))
            {
                // show error
                Common.ShowToastifyMessage(this.Page, "No boy polled", "danger");
                return;
            }
            string boy = null;
            string boyPkId = null;
            if (orderBoyId.Rows.Count > 0)
            {

                boy = orderBoyId.Rows[0]["boy_id"].ToString();
                boyPkId = orderBoyId.Rows[0]["order_pk_id"].ToString();

            }
            int ordboyId = Convert.ToInt32(boy);
            int orderPIckerId = Convert.ToInt32(boyPkId);
            int storegroupid = this.CurrentUser.APIStoreId;
            string result = Core.Services.APIService.Revoke(ordboyId, orderPIckerId);

            // show result as status.
            string status = result;
            if (status == "ok")
            {
                Common.ShowCustomAlert(this.Page, "Reverted Successfully!", $"<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Order has been reverted successfully!</a></h5>", true, "/Tenant/PendingOrders");
            }
        }

        private static readonly List<StatusRule> Rules = new List<StatusRule>
        {
            //// All Orders
            //new StatusRule { FilterType = 0, Match = (fsto, order, row) => order >= 0, CssClass = "pending_items-row" },

            // New Orders
            new StatusRule { FilterType = 1, Match = (fsto, order, row) => fsto < 4 && order == 4, CssClass = "pending_items-row" },

            // Pending
            new StatusRule { FilterType = 2, Match = (fsto, order, row) => fsto == 4, CssClass = "packing_items-row" },

            // Processing
            new StatusRule { FilterType = 3, Match = (fsto, order, row) => new[] { 2, 3, 6, 7, 8 }.Contains(fsto), CssClass = "trasit_items-row" },

            // Completed
            //new StatusRule { FilterType = 4, Match = (fsto, order, row) => fsto == 10 || order == 9, CssClass = "row-processing" },

            // On Hold
            new StatusRule { FilterType = 5, Match = (fsto, order, row) => new[] { 9, 11, 20 }.Contains(fsto), CssClass = "failed_items-row" },

            // Re-rack
            new StatusRule
            {
                FilterType = 6,
                Match = (fsto, order, row) =>
                {
                    int isPacked = DataBinder.Eval(row.DataItem, "fsto_isalreadypacked") != DBNull.Value ? Convert.ToInt32(DataBinder.Eval(row.DataItem, "fsto_isalreadypacked")) : 0;
                    int isReplenished = DataBinder.Eval(row.DataItem, "is_replenished") != DBNull.Value ? Convert.ToInt32(DataBinder.Eval(row.DataItem, "is_replenished")) : 1;
                    return fsto == 15 && isPacked == 1 && isReplenished == 0;
                },
                CssClass = "hold_items-row"
            },

            // Order Picking
            new StatusRule { FilterType = 7, Match = (fsto, order, row) => new[] { 4, 8 }.Contains(fsto), CssClass = "trasit_items-row" },

            // Ready to Invoice
            new StatusRule { FilterType = 8, Match = (fsto, order, row) => fsto == 10 || order == 9, CssClass = "trasit_items-row" },

            // Ready to Pack
            new StatusRule { FilterType = 9, Match = (fsto, order, row) => new[] { 6, 8 }.Contains(fsto), CssClass = "trasit_items-row" },

            // All Orders
            new StatusRule { FilterType = 0, Match = (fsto, order, row) => fsto < 4 || order == 4, CssClass = "pending_items-row" },
            new StatusRule { FilterType = 0, Match = (fsto, order, row) => fsto == 4, CssClass = "packing_items-row" },
            new StatusRule { FilterType = 0, Match = (fsto, order, row) => new[] { 2, 3, 6, 7, 8 }.Contains(fsto), CssClass = "trasit_items-row" },
            new StatusRule { FilterType = 0, Match = (fsto, order, row) => order == 19, CssClass = "cancel_items-row" },
            new StatusRule { FilterType = 0, Match = (fsto, order, row) => new[] { 9, 11, 20 }.Contains(fsto), CssClass = "failed_items-row" },
            new StatusRule
            {
                FilterType = 0,
                Match = (fsto, order, row) =>
                {
                    int isPacked = DataBinder.Eval(row.DataItem, "fsto_isalreadypacked") != DBNull.Value
                                   ? Convert.ToInt32(DataBinder.Eval(row.DataItem, "fsto_isalreadypacked")) : 0;

                    int isReplenished = DataBinder.Eval(row.DataItem, "is_replenished") != DBNull.Value
                                        ? Convert.ToInt32(DataBinder.Eval(row.DataItem, "is_replenished")) : 1;
                    return fsto == 15 && isPacked == 1 && isReplenished == 0;
                },
                CssClass = "failed_items-row"
            },
             new StatusRule { FilterType = 0, Match = (fsto, order, row) => new[] { 4, 8 }.Contains(fsto), CssClass = "trasit_items-row" },
             new StatusRule { FilterType = 0, Match = (fsto, order, row) => fsto == 10 || order == 9, CssClass = "trasit_items-row" },
             new StatusRule { FilterType = 0, Match = (fsto, order, row) => new[] { 6, 8 }.Contains(fsto), CssClass = "trasit_items-row" },

            // Successful Orders

            new StatusRule { FilterType = 10, Match = (fsto, order, row) => fsto < 4 || order == 4, CssClass = "pending_items-row" },
            new StatusRule { FilterType = 10, Match = (fsto, order, row) => fsto == 4, CssClass = "packing_items-row" },
            new StatusRule { FilterType = 10, Match = (fsto, order, row) => new[] { 2, 3, 6, 7, 8 }.Contains(fsto), CssClass = "trasit_items-row" },
            new StatusRule { FilterType = 10, Match = (fsto, order, row) => order == 19, CssClass = "cancel_items-row" },
            new StatusRule { FilterType = 10, Match = (fsto, order, row) => new[] { 9, 11, 20 }.Contains(fsto), CssClass = "failed_items-row" },
            new StatusRule
            {
                FilterType = 10,
                Match = (fsto, order, row) =>
                {
                    int isPacked = DataBinder.Eval(row.DataItem, "fsto_isalreadypacked") != DBNull.Value
                                   ? Convert.ToInt32(DataBinder.Eval(row.DataItem, "fsto_isalreadypacked")) : 0;

                    int isReplenished = DataBinder.Eval(row.DataItem, "is_replenished") != DBNull.Value
                                        ? Convert.ToInt32(DataBinder.Eval(row.DataItem, "is_replenished")) : 1;
                    return fsto == 15 && isPacked == 1 && isReplenished == 0;
                },
                CssClass = "failed_items-row"
            },
             new StatusRule { FilterType = 10, Match = (fsto, order, row) => new[] { 4, 8 }.Contains(fsto), CssClass = "trasit_items-row" },
             new StatusRule { FilterType = 10, Match = (fsto, order, row) => fsto == 10 || order == 9, CssClass = "trasit_items-row" },
             new StatusRule { FilterType = 10, Match = (fsto, order, row) => new[] { 6, 8 }.Contains(fsto), CssClass = "trasit_items-row" },

            // All Orders 
            new StatusRule { FilterType = 11, Match = (fsto, order, row) => order == 19, CssClass = "cancel_items-row" }
        };

        protected void gvPendingOrders_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            if (e.Row.RowType != DataControlRowType.DataRow)
                return;

            // Add collapse attributes
            e.Row.Attributes.Add("data-toggle", "collapse");
            e.Row.Attributes.Add("data-target", $"#collapse{e.Row.DataItemIndex}");
            e.Row.Attributes.Add("aria-expanded", "false");
            e.Row.Attributes.Add("aria-controls", $"collapse{e.Row.DataItemIndex}");

            try
            {
                // Parse status values
                int fstoStatus = Convert.ToInt32(DataBinder.Eval(e.Row.DataItem, "fsto_status") ?? 0);
                int orderStatus = Convert.ToInt32(DataBinder.Eval(e.Row.DataItem, "status_id") ?? 0);

                // Find matching rule for the current FilterType
                var rule = Rules.FirstOrDefault(r => r.FilterType == FilterType && r.Match(fstoStatus, orderStatus, e.Row));

                // Apply CSS class
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

        protected void gvPendingOrders_PageIndexChanging(object sender, GridViewPageEventArgs e)
        {
            gvPendingOrders.PageIndex = e.NewPageIndex;
            gvPendingOrders.DataBind();
        }

        protected void btnprint_Click(object sender, EventArgs e)
        {
            try
            {
                LinkButton btnprint = (LinkButton)sender;
                string orderId = Convert.ToString(btnprint.Attributes["order_id"]);
                string body = string.Empty;
                var result = GenerateorderDetalies((orderId));
                List<KeyValuePair<string, object>> Sqlprms = new List<KeyValuePair<string, object>>();
                Sqlprms.Add(new KeyValuePair<string, object>("orderid", orderId));
                string sqlOrder = "SELECT rc.order_order_id,rc.order_confirm_date,rc.total,invoice_type,fsto_updateon, rc.created_at,br_Name,order_invoiceno,order_invoicedate,inv_number FROM retaline_customer_order rc  INNER JOIN finascop_branch fb  ON  fb.br_ID =rc.order_branch_id INNER JOIN`finascop_stock_transfer_order` fsto ON rc.order_id = fsto.fstr_id AND fsto.fsto_ordertype = 1 left JOIN `invoice_number` vn ON vn.order_id=rc.order_id AND invoice_type=1 WHERE rc.order_order_id=@orderid";
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
                DateTime orderpackedDate = DateTime.Parse(drOrderInfo.Rows[0]["fsto_updateon"].ToString());
                string formattedorderepackedDate = orderDate.ToString("dd MMM yyyy");
                replacements.Add(new KeyValuePair<string, string>("[Packeddate]", formattedorderepackedDate));
                replacements.Add(new KeyValuePair<string, string>("[Invoiceno]", drOrderInfo.Rows[0]["order_invoiceno"].ToString()));
                DateTime orderinvoiceDate = DateTime.Parse(drOrderInfo.Rows[0]["order_invoicedate"].ToString());
                string formattedordereinvoiceDate = orderDate.ToString("dd MMM yyyy");
                replacements.Add(new KeyValuePair<string, string>("[Invoicedate]", formattedordereinvoiceDate));
                body = EmailService.CreateEmailbody(EmailType.PackingSlip, replacements);
                string orderBody = body.Replace("'", "\\'").Replace("\n", "").Replace("\r", "");
                string script = $@"var printWindow = window.open('', '_blank'); printWindow.document.open();printWindow.document.write('<html><head><title>Print Preview</title><style>body {{ font-family: Arial, sans-serif; }}</style></head><body>');printWindow.document.write('{orderBody}');printWindow.document.write('</body></html>'); printWindow.document.close();";
                ScriptManager.RegisterStartupScript(this, GetType(), "PrintWindow", script, true); ;
            }
            catch
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Packing slip is not Avaliable. Please verify the order selected or the order is expired.", false);
                return;
            }



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

        public static string GetPaymentStatusName(int paymentMode, int statusId, string orderPaymentStatus = "")
        {
            switch (paymentMode)
            {
                case 1:
                    return statusId == 19 ? "Cancelled" : "To be collected";
                case 2:
                    return string.IsNullOrEmpty(orderPaymentStatus) ? "Paid Online" : orderPaymentStatus;
                case 3:
                    return "Wallet";
                case 4:
                    return "COD with Wallet";
                case 5:
                    return string.IsNullOrEmpty(orderPaymentStatus) ? "Paid Online with Wallet" : orderPaymentStatus;
                case 6:
                    return "Online on Delivery";
                case 7:
                    return statusId == 19 ? "Cancelled" : "To be collected";
                default:
                    return string.Empty;
            }
        }
        public static string GetPaymentModeName(int paymentMode)
        {
            switch (paymentMode)
            {
                case 1:
                    return "Pay On Delivery";
                case 2:
                    return "Online";
                case 3:
                    return "Wallet";
                case 4:
                    return "COD with Wallet";
                case 5:
                    return "Online with Wallet";
                case 6:
                    return "Online on Delivery";
                case 7:
                    return "Cash on Delivery";
                default:
                    return string.Empty;
            }
        }

        protected void btnvieworder_Click(object sender, EventArgs e)
        {
            Button btn = sender as Button;

            // Access button attributes
            string status = btn.Attributes["data-orderStatus"];
            string fstoId = btn.Attributes["data-fsto_id"];
            string orderId = btn.Attributes["data-orderId"];
            string orderMethod = btn.Attributes["data-orderMethod"];
            string statusName = btn.Attributes["data-status"];
            if (status == "7" || status == "5"|| status == "4" || status == "23")
            {
                // Show popup
                string strAlertSCript = "$('#modalvieworder').modal('show');";
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
                Common.ShowCustomAlert(this.Page, "Failed", "Invalid Order", false, "/Tenant/PendingOrders");
            }
          
        }

        public static int GetPackedQuantitySum(string transferOrderId)
        {
            try
            {
                List<KeyValuePair<string, object>> stsparams = new List<KeyValuePair<string, object>>();
                stsparams.Add(new KeyValuePair<string, object>("transOrdId", transferOrderId));
                // Execute the query to get the packed quantity sum
                DataTable fstoStatusTbl = DataServiceMySql.GetDataTable("SELECT SUM(fsto_pkdQty) AS pckSum FROM finascop_stock_transfer_order_details WHERE fsto_id = @transOrdId", UserService.GetAPIConnectionString(), stsparams);
                if (fstoStatusTbl.Rows.Count > 0 && fstoStatusTbl.Rows[0]["pckSum"] != DBNull.Value)
                {
                    return Convert.ToInt32(fstoStatusTbl.Rows[0]["pckSum"]);
                }
                // Return 0 if no rows were returned or if the sum is null                
            }
            catch (Exception ex)
            {
                return 0;
            }
            return 0;
        }

        protected bool IsButtonVisible(string fstoStatus, string orderId)
        {

            if (Convert.ToInt32(fstoStatus) < 4)
            {
                int orderIdValue = Convert.ToInt32(orderId);

                List<KeyValuePair<string, object>> stsparams = new List<KeyValuePair<string, object>>();
                stsparams.Add(new KeyValuePair<string, object>("orderid", orderId));
                DataTable fstotimediffTbl = DataServiceMySql.GetDataTable("SELECT fsto_createdOn,TIMESTAMPDIFF(MINUTE,  NOW(),fsto_createdOn) AS TimeDifferenceInMinutes FROM finascop_stock_transfer_order WHERE fstr_id=@orderid", UserService.GetAPIConnectionString(), stsparams);
                if (fstotimediffTbl.Rows.Count > 0 && fstotimediffTbl.Rows[0]["TimeDifferenceInMinutes"] != DBNull.Value)
                {
                    string timedifference = (fstotimediffTbl.Rows[0]["TimeDifferenceInMinutes"].ToString());
                    return double.TryParse(timedifference, out double timeDifferenceInMinutes) && Math.Abs(timeDifferenceInMinutes) > 3;
                }
            }

            return false;
        }

        protected void btnAssignOrderPicker_Click(object sender, EventArgs e)
        {
            // Show popup
            string strAlertSCript = "$('#modalAssignorderpicker').modal('show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = this.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void btnrerack_Click(object sender, EventArgs e)
        {
            Button btn = (Button)sender;
            if (!String.IsNullOrEmpty(btn.Attributes["tranferorderid"]))
            {
                string tranferorderid = btn.Attributes["tranferorderid"];
                hdnfstoid.Value = tranferorderid;

                List<KeyValuePair<string, object>> ordrparams = new List<KeyValuePair<string, object>>();
                ordrparams.Add(new KeyValuePair<string, object>("orderId", tranferorderid));
                string sql = $"SELECT order_order_id,fsto_id,fstr_id,fsto_pickingNumber FROM `finascop_stock_transfer_order` INNER JOIN  retaline_customer_order rc ON fstr_id=order_id  WHERE fsto_id=@orderId";
                var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString(), ordrparams);
                ltrorderid.Text = tblItems?.Rows.Count > 0 ? tblItems.Rows[0]["order_order_id"]?.ToString() : string.Empty;
                ltrbasketno.Text = tblItems?.Rows.Count > 0 ? tblItems.Rows[0]["fsto_pickingNumber"]?.ToString() : string.Empty;
                string strAlertSCript = "$('#modalrerack').modal('show');";
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

        protected void btnrerackorder_Click(object sender, EventArgs e)
        {
            // Step 1: Update the stock transfer order to mark manual replenishment
            List<KeyValuePair<string, object>> toparams = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("TranferorderId",  hdnfstoid.Value),
                new KeyValuePair<string, object>("manualreplenuser", 0)
            };
            string updateQry = "UPDATE finascop_stock_transfer_order SET fsto_manualreplenuser=@manualreplenuser WHERE fsto_id=@TranferorderId";
            DataServiceMySql.ExecuteSql(updateQry, Service.UserService.GetAPIConnectionString(), toparams);
            // Step 2: Submit the manual replenishment process via API
            string result = Core.Services.APIService.SubmitManualReplenish(hdnfstoid.Value);
            if (result == "ok")
            {
                Common.ShowCustomAlert(this.Page, "Rerack successfully", "<h5 class=\"lh-3 mg-b-20\"><a class=\"tx-inverse hover-primary\">Order has been rerack successfully!</a></h5>", true, "/Tenant/PendingOrders");
            }
            else
            {
                Common.ShowCustomAlert(this.Page, "Rerack failed", "Rerack failed", false, "/Tenant/PendingOrders");
            }
        }

        protected string GetDeliveryMode(string orderMethod, object orderSlotId)
        {
            try
            {
                if (orderSlotId != DBNull.Value && Convert.ToInt32(orderSlotId) > 0)
                    return "Scheduled Delivery";

                switch (orderMethod)
                {
                    case "1":
                        return "Express Delivery";
                    case "3":
                        return "Courier Delivery";
                    default:
                        return "Scheduled Delivery";
                }
            }
            catch
            {
                return "Unknown";
            }
        }


    }

}