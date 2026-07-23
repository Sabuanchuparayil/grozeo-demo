using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.IO.Packaging;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant
{
    public partial class ManualPacking : Base.BasePartnerPage
    {
        public enum ViewMode
        {
            Boxdetalis = 1,
            Packing = 2,           
        }
        public ViewMode ViewType
        {
            get
            {
                if (ViewState["VIEWMODE"] != null)
                    return (ViewMode)ViewState["VIEWMODE"];

                return ViewMode.Packing;
            }
            set
            {
                ViewState["VIEWMODE"] = value;
            }
        }
        private List<string> Packages
        {
            get
            {
                if (ViewState["NUMOFPACKS"] != null)
                    try
                    {
                        return (List<string>)ViewState["NUMOFPACKS"];
                    }
                    catch { }
                return default;
            }
            set
            {
                ViewState["NUMOFPACKS"] = value;
            }

        }
        public string PackagesCode(int index)
        {
            if (Packages.Count > index)
                return Packages[index];
            return "";
        }
        protected int PackCount { get; set; }
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                //txtInvDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
                string transferOrdId = Request.QueryString["fsto_id"] ?? string.Empty;
                string orderStatus = Request.QueryString["orderStatus"] ?? string.Empty;
                string statusName = Request.QueryString["statusName"] ?? string.Empty;

                if (transferOrdId != string.Empty && orderStatus == "51" && statusName == "Packed but not boxed")
                {
                    BoxDetails(transferOrdId);
                    ViewType = ViewMode.Boxdetalis;
                    if (ViewType == ViewMode.Boxdetalis)
                    {
                        showpopup();
                    }
                }
               
            }


            if (!ValidateQueryString("fsto_id", out string transferOrderId))
            {
                ShowFailureAlert("Invalid item selected. Please verify the order selected or the order is expired.", "/Tenant/PendingOrders");
                return;
            }
            // Retrieve transfer order items
            var transferOrderItems = GetTransferOrderItems(Convert.ToInt32(transferOrderId));
            if (transferOrderItems.Rows.Count > 0)
            {
                DataRow dr = transferOrderItems.Rows[0];
                UpdateGridViewPacking(dr);
            }

            if (!ValidateQueryString("orderId", out string orderId))
            {
                ShowFailureAlert("Invalid item selected. Please verify the order selected or the order is expired.", "/Tenant/PendingOrders");
                return;
            }
            // Retrieve and display order details
            var tblItems = GetOrderDetails(orderId.ToString());
            if (tblItems.Rows.Count > 0)
            {
                PopulateOrderDetails(tblItems.Rows[0]);
            }
        }

        protected void btnSubmit_Click(object sender, EventArgs e)
        {
            try
            {
                string transferOrderId = Convert.ToString(Request.QueryString["fsto_id"]);
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
                string updatequery1 = $"UPDATE finascop_stock_transfer_order SET fsto_ismanualpacking = 1 WHERE fsto_id = @transferOrdId";
                DataServiceMySql.ExecuteSql(updatequery1, UserService.GetAPIConnectionString(), toparams);
                string numberofBags = string.IsNullOrWhiteSpace(txtNumBags.Text) ? "0" : txtNumBags.Text;
                int noofbags = Convert.ToInt32(numberofBags);
                string invoiceNumber = string.IsNullOrWhiteSpace(txtInvNum.Text) ? "0" : txtInvNum.Text;
                double invoiceAmount = string.IsNullOrWhiteSpace(txtAmt.Text) ? 0 : Convert.ToDouble(txtAmt.Text);
                DateTime invoiceDate;
                string formattedDate = DateTime.TryParse(txtInvDate.Text, out invoiceDate) ? invoiceDate.ToString("yyyy-MM-dd") : null;
                var itemsList = new List<Dictionary<string, object>>();
                bool allIsPacked = true;
                int fsid = 0;
                string fsto_uid = null;
                string fsto_updateon = null;
                decimal itemQty = 0;
                int isAlreadyPacked = 0;
                decimal packedQuantity = 0;
                decimal stockValue = 0;
                foreach (GridViewRow gr in gvManualPacking.Rows)
                {
                    TextBox txtQty = (TextBox)gr.FindControl("txtUpdate");
                    TextBox txtSubPrd = (TextBox)gr.FindControl("txtSubPrd");

                    if (string.IsNullOrEmpty(txtQty.Attributes["prodid"])) continue;
                    string strPId = txtQty.Attributes["prodid"];
                    var dr = dtOrderItems.AsEnumerable().FirstOrDefault(r => r["fstod_id"].ToString() == strPId);
                    if (dr == null) continue;

                    fsid = Convert.ToInt32(dr["fsto_id"]);
                    fsto_uid = dr["fsto_uid"].ToString();
                    fsto_updateon = dr["fsto_updateon"].ToString();
                    itemQty = Convert.ToDecimal(dr["fsto_ItemQty"]);
                    isAlreadyPacked = Convert.ToInt32(dr["fsto_isalreadypacked"]);
                    packedQuantity = Convert.ToDecimal(txtQty.Text);
                    stockValue = string.IsNullOrWhiteSpace(txtSubPrd.Text) ? 0 : Convert.ToDecimal(txtSubPrd.Text);
                    itemsList.Add(new Dictionary<string, object>
                {
                    {"item_id", Convert.ToInt32(dr["fsto_ItemId"])},
                    {"count", packedQuantity},
                    {"fsto_stockValue", stockValue}
                });
                    allIsPacked = allIsPacked && packedQuantity >= itemQty;
                    DataServiceMySql.ExecuteSql("UPDATE finascop_stock_transfer_order SET fsto_ismanualpacking = 1 WHERE fsto_id = @transferOrdId", UserService.GetAPIConnectionString(), toparams);
                }
                if (!itemsList.Any())
                {
                    Common.ShowCustomAlert(this.Page, "Failure", "No item matches the criteria. Please contact support.", false, "/Tenant/PendingOrders");
                    return;
                }
                string result;
                if (!allIsPacked && isAlreadyPacked == 0)
                {
                    result = Core.Services.APIService.ForceSubmit(fsto_uid, fsid, formattedDate, invoiceNumber, invoiceAmount, noofbags, fsto_updateon, itemsList);
                    if (result == "ok")
                    {
                        Common.ShowCustomAlert(this.Page, "Success", "Moved to on hold order!", true, "/Tenant/PendingOrders");
                    }
                }
                else if (allIsPacked && isAlreadyPacked <= 1 && noofbags >= 1)
                {
                    var packingResult = Core.Services.APIService.SubmitManualPacking(fsto_uid, fsid, formattedDate, invoiceNumber, invoiceAmount, noofbags, fsto_updateon, itemsList);
                    if (packingResult != null && packingResult.status.ToLower() == "ok")
                    {
                        var dtCustOrder = DataServiceMySql.GetDataTable("SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='COURIER_PACKING'", UserService.GetAPIConnectionString());
                        if (dtCustOrder?.Rows.Count > 0 && Convert.ToInt32(dtCustOrder.Rows[0]["cfg_Value"]) == 0)
                        {
                            string msgContent = $"<p>Thank you for packing. This order has <strong>{packingResult.packinglist.packingNumber.Length}</strong> box(es).</p>";
                            msgContent += string.Join("", packingResult.packinglist.packingNumber.Select((p, i) =>
                                $"<div class=\"row\"><div class=\"col-4 border\">Packet {i + 1}</div><div class=\"col-8 border\">{p}</div></div>"));
                            Common.ShowCustomAlert(this.Page, "Packed Successfully!", msgContent, true, "/Tenant/PendingOrders");
                        }
                        else
                        {
                            Packages = packingResult.packinglist.packingNumber.ToList();
                            rptPackageType.DataSource = Packages;
                            rptPackageType.DataBind();
                            ViewType = ViewMode.Boxdetalis;
                            if (ViewType == ViewMode.Boxdetalis)
                            {
                                showpopup();
                            }
                        }
                    }
                    
                }
            }
            catch(Exception ex)
            {
                ShowFailureAlert("Invalid item selected. Please verify the order selected or the order is expired.", "/Tenant/PendingOrders");
            }


        }
        protected void SDSPackageType_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            int orderMethod = Convert.ToInt32(Request.QueryString["orderMethod"] ?? "0");
            e.Command.Parameters["packtype"].Value = (orderMethod == 3) ? 2 : 1;
        }
        protected void selPackageType_SelectedIndexChanged(object sender, EventArgs e)
        {
            DropDownList selPackageType = (DropDownList)sender;
            string selectedValue = selPackageType.SelectedValue;
            foreach (RepeaterItem item in rptPackageType.Items)
            {
                if (item.ItemType == ListItemType.Item || item.ItemType == ListItemType.AlternatingItem)
                {
                    TextBox txtLength = item.FindControl("txtLength") as TextBox;
                    TextBox txtBreadth = item.FindControl("txtbreadth") as TextBox;
                    TextBox txtHeight = item.FindControl("txtHeight") as TextBox;

                    if (item.FindControl("selPackageType") == selPackageType)
                    {
                        bool isCustom = selectedValue == "-1";
                        SetTextBoxState(txtLength, isCustom);
                        SetTextBoxState(txtBreadth, isCustom);
                        SetTextBoxState(txtHeight, isCustom);

                        if (!isCustom)
                        {
                            PopulatePackageDimensions(selectedValue, txtLength, txtBreadth, txtHeight);
                        }
                    }
                }
            }
            ViewType = ViewMode.Boxdetalis;
            if (ViewType == ViewMode.Boxdetalis)
            {
                showpopup();
            }
        }

        private void SetTextBoxState(TextBox textBox, bool enabled)
        {
            if (textBox != null) textBox.Enabled = enabled;
        }
        // Dimensions of packets 
        private void PopulatePackageDimensions(string packageType, TextBox txtLength, TextBox txtBreadth, TextBox txtHeight)
        {
            DataTable dtTemplate = GetPackageDimensions(packageType);

            if (dtTemplate.Rows.Count > 0)
            {
                DataRow row = dtTemplate.Rows[0];
                txtLength.Text = row["rpckm_length"].ToString();
                txtBreadth.Text = row["rpckm_breadth"].ToString();
                txtHeight.Text = row["rpckm_height"].ToString();
            }
        }

        private DataTable GetPackageDimensions(string packageType)
        {
            string sql = "SELECT rpckm_id, rpckm_name, rpckm_length, rpckm_breadth, rpckm_height FROM retaline_package_master WHERE rpckm_id = @packageMasterId";
            List<KeyValuePair<string, object>> templateParams = new List<KeyValuePair<string, object>>();
            templateParams.Add(new KeyValuePair<string, object>("packageMasterId", packageType));
            return DataServiceMySql.GetDataTable(sql, Service.UserService.GetAPIConnectionString(), templateParams);
        }

        public void showpopup()
        {
            string strAlertSCript = "$('#PopupManualpacking').modal({backdrop: 'static', keyboard: false}, 'show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = Page.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }
        private void UpdateGridViewPacking(DataRow dr)
        {
            bool isAlreadyPacked = Convert.ToInt32(dr["fsto_isalreadypacked"]) == 0;
            int packedQty = Convert.ToInt32(dr["fsto_pkdQty"]);
            int itemQty = Convert.ToInt32(dr["fsto_ItemQty"]);

            foreach (GridViewRow gr in gvManualPacking.Rows)
            {
                TextBox txtQty = (TextBox)gr.FindControl("txtUpdate");
                txtQty.Enabled = isAlreadyPacked && packedQty < itemQty;
            }
        }

        private void PopulateOrderDetails(DataRow orderRow)
        {
            ltrOrderId.Text = orderRow["order_order_id"].ToString();
            ltrName.Text = orderRow["cust_customer_name"].ToString();
            ltrMobile.Text = orderRow["cust_mobile"].ToString();
        }
        private bool ValidateQueryString(string key, out string result)
        {
            result = "0";
            string queryValue = Request.QueryString[key];
            if (string.IsNullOrWhiteSpace(queryValue))
                return false;
            if (!string.IsNullOrEmpty(queryValue))
            {
                result = queryValue;
                return true;
            }

            return false;
        }
        private void ShowFailureAlert(string message, string redirectUrl)
        {
            Common.ShowCustomAlert(this.Page, "Failure", message, false, redirectUrl);
        }
        private DataTable GetTransferOrderItems(int transferOrderId)
        {
            var orderParams = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("transferOrdId", transferOrderId),
                new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId)
            };
            var transferOrderItems = DataServiceMySql.GetDataTable($"SELECT fsto_isalreadypacked,fsto_pkdQty,fsto_ItemQty,(SELECT br_type FROM finascop_branch WHERE br_ID = fsto_source) AS branchType," +
            $"CASE WHEN fsto_ordertype = 0 THEN 'Branch Transfer' WHEN fsto_ordertype = 1 THEN 'B2C' WHEN fsto_ordertype = 2 THEN 'B2B' " +
            $"WHEN fsto_ordertype = 3 THEN 'Return' WHEN fsto_ordertype = 4 THEN 'Distribution' END AS fsto_ordertype FROM finascop_stock_transfer_order fto INNER JOIN finascop_stock_transfer_order_details tod ON fto.fsto_id = tod.fsto_id" +
            $" WHERE fto.fsto_id= @transferOrdId", UserService.GetAPIConnectionString(), orderParams);
             return transferOrderItems;
        }
        private DataTable GetOrderDetails(string orderId)
        {
            List<KeyValuePair<string, object>> ordrparams = new List<KeyValuePair<string, object>>();
            ordrparams.Add(new KeyValuePair<string, object>("orderId", orderId));
            string sql = $"SELECT order_id,order_order_id,order_customer_id,cust_mobile,cust_customer_name,status_id FROM retaline_customer_order INNER JOIN retaline_customer ON cust_id = order_customer_id WHERE order_order_id = @orderId";
            var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString(), ordrparams);
            return tblItems;
        }
        // Box detalis after packing
        protected void BoxDetails(string transferOrderId)
        {
            string sql = "SELECT quor_PacketCount, quor_RefNo FROM qugeo_order WHERE quor_TransferOrder_id = @transferOrderId";
            List<KeyValuePair<string, object>> ordrparams = new List<KeyValuePair<string, object>>();
            ordrparams.Add(new KeyValuePair<string, object>("transferOrderId", transferOrderId));
            DataTable tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString(), ordrparams);

            if (tblItems?.Rows.Count > 0)
            {
                DataRow row = tblItems.Rows[0];
                int packCount = PackCount= Convert.ToInt32(row["quor_PacketCount"]);
                if (packCount > 0)
                {
                    string referenceNo = row["quor_RefNo"].ToString();
                    Packages = Enumerable.Range(1, packCount)
                                         .Select(i => $"{referenceNo}/{packCount}/{i}")
                                         .ToList();

                    rptPackageType.DataSource = Packages;
                    rptPackageType.DataBind();
                }
            }
        }

        protected void SDSPackageType_Selecting1(object sender, SqlDataSourceSelectingEventArgs e)
        {
            int orderMethod = int.TryParse(Request.QueryString["orderMethod"], out int method) ? method : 0;
            e.Command.Parameters["packtype"].Value = (orderMethod == 3) ? 2 : 1;
            ViewType = ViewMode.Boxdetalis;
            if (ViewType == ViewMode.Boxdetalis)
            {
                showpopup();
            }
        }

        protected void btnPackageSubmit_Click(object sender, EventArgs e)
        {
            ProcessPackageDetails();
            string orderId = ""; try { orderId = Request.QueryString["orderId"]; } catch { orderId = ""; }
            lblordernumber.Text = orderId;
            lblpacketcount.Text = Packages.Count.ToString();
            lblinvoicecount.Text = "1";
            rptPackageTypedetails.DataSource = Packages;
            rptPackageTypedetails.DataBind();
            string strAlertSCript = "$('#PopupManualpackingDetalies').modal({backdrop: 'static', keyboard: false}, 'show');";
            strAlertSCript = "$(document).ready(function () { " + strAlertSCript + " });";
            System.Type cstype = this.GetType();
            String csname1 = "ShowConfirmPopup";
            ClientScriptManager cs = Page.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strAlertSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());
        }

        protected void ProcessPackageDetails()
        {
            try
            {
                string transferOrderId = Convert.ToString(Request.QueryString["fsto_id"]);
                string orderMethod = Convert.ToString(Request.QueryString["orderMethod"]);
                string orderId = ""; try { orderId = Request.QueryString["orderId"]; } catch { orderId = ""; }
                foreach (RepeaterItem item in rptPackageType.Items)
                {
                    if (item.ItemType == ListItemType.Item || item.ItemType == ListItemType.AlternatingItem)
                    {
                        TextBox txtPackNum = (TextBox)item.FindControl("txtPackNumber");
                        DropDownList packageType = (DropDownList)item.FindControl("selPackageType");
                        TextBox length = (TextBox)item.FindControl("txtLength");
                        TextBox breadth = (TextBox)item.FindControl("txtbreadth");
                        TextBox height = (TextBox)item.FindControl("txtHeight");
                        TextBox weight = (TextBox)item.FindControl("txtWeight");
                        List<KeyValuePair<string, object>> packparams = new List<KeyValuePair<string, object>>();
                        packparams.Add(new KeyValuePair<string, object>("transferOrdId", transferOrderId));
                        packparams.Add(new KeyValuePair<string, object>("packets", txtPackNum.Text));
                        packparams.Add(new KeyValuePair<string, object>("orderType", "B2C"));
                        packparams.Add(new KeyValuePair<string, object>("packaging", packageType.Text));
                        packparams.Add(new KeyValuePair<string, object>("length", length.Text));
                        packparams.Add(new KeyValuePair<string, object>("breadth", breadth.Text));
                        packparams.Add(new KeyValuePair<string, object>("height", height.Text));
                        packparams.Add(new KeyValuePair<string, object>("invoicedate", txtInvDate.Text));
                        packparams.Add(new KeyValuePair<string, object>("invoicenumber", txtInvNum.Text));
                        packparams.Add(new KeyValuePair<string, object>("invoiceamount", txtAmt.Text));
                        packparams.Add(new KeyValuePair<string, object>("packetWeight", weight.Text));
                        packparams.Add(new KeyValuePair<string, object>("orderId", orderId));
                        packparams.Add(new KeyValuePair<string, object>("orderStatus", 9));
                        packparams.Add(new KeyValuePair<string, object>("tranorderStatus", 10));
                        string insertQry = $"INSERT INTO retaline_transfer_order_pack_details(rtopd_fstoId, rtopd_orderType, rtopd_packets, rtopd_packaging, rtpod_length, rtpod_breadth, rtpod_height, rtopd_packetweigh) " +
                                            $"VALUES(@transferOrdId,@orderType,@packets,@packaging,@length,@breadth,@height,@packetWeight)";
                        DataServiceMySql.ExecuteSql(insertQry, Service.UserService.GetAPIConnectionString(), packparams);

                        string updateOrderTblQry = $"UPDATE retaline_customer_order SET status_id=@orderStatus,order_invoiceno=@invoicenumber,order_invoicedate=@invoicedate,order_invoiceamt=@invoiceamount WHERE order_order_id = @orderId";
                        DataServiceMySql.ExecuteSql(updateOrderTblQry, Service.UserService.GetAPIConnectionString(), packparams);

                        string updateTransOrdTblQry = $"UPDATE finascop_stock_transfer_order SET fsto_status=@tranorderStatus WHERE fsto_id = @transferOrdId";
                        DataServiceMySql.ExecuteSql(updateTransOrdTblQry, Service.UserService.GetAPIConnectionString(), packparams);

                        // Activitylog
                        String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
                        String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
                        string Source = strUrl;
                        int storegroupid = this.CurrentUser.APIStoreId;
                        string Users = this.CurrentUser.Email;
                        string storegroup_id = (this.CurrentUser.APIStoreId).ToString();
                        string transferOrdId = transferOrderId.ToString();
                        string packets = txtPackNum.Text;
                        string orderType = "B2C";
                        string packaging = packageType.Text;
                        string packetWeight = weight.Text;
                        var items = new[]
                         {
                    new { Key = "Storegroup Id", Value = storegroup_id },
                    new { Key = "Transfer Order Id", Value = transferOrdId },
                    new { Key = "Packets", Value = packets },
                    new { Key = "Order Type", Value = orderType },
                    new { Key = "Packaging", Value = packaging },
                    new { Key = "Packet Weight", Value = packetWeight },
                    };
                        string Description = string.Join(", ", items.Select(Item => $"{Item.Key}={Item.Value}"));
                        var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);

                    }
                }
            }
            catch(Exception ex)
            {
                ShowFailureAlert("Invalid item selected. Please verify the order selected or the order is expired.", "/Tenant/PendingOrders");
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

        protected void btnprint_Click(object sender, EventArgs e)
        {
            string orderId = ""; try { orderId = Request.QueryString["orderId"]; } catch { orderId = ""; }
            try
            {
                string body = string.Empty;
                var result = GenerateorderDetalies((orderId));
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
                string script = $@"var printWindow = window.open('', '_blank'); printWindow.document.open();printWindow.document.write('<html><head><title>Print Preview</title><style>body {{ font-family: Arial, sans-serif; }}</style></head><body>');printWindow.document.write('{orderBody}');printWindow.document.write('</body></html>'); printWindow.document.close();";
                ScriptManager.RegisterStartupScript(this, GetType(), "PrintWindow", script, true);
                Common.ShowCustomAlert(this.Page, "Packed Successfully!", "Packed Successfully!", true, "/Tenant/PendingOrders");
            }
            catch
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Invalid item selected. Please verify the order selected or the order is expired.", false);
                return;
            }

        }
    }
}