using Finascop.Services;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class ManualPacking: Base.BasePartnerPage
    {
        private string CurViewType
        {
            get
            {
                return (string)ViewState["CURVIEWTYPE"];
            }
            set
            {
                ViewState["CURVIEWTYPE"] = value;
            }
        }
        private List<string> Packages
        {
            get
            {
                if(ViewState["NUMOFPACKS"] != null)
                    try {
                        return(List<string>)ViewState["NUMOFPACKS"];
                    } catch { }
                return default;
            }
            set
            {
                ViewState["NUMOFPACKS"] = value;
            }

        }
        public string PackagesCode(int index)
        {
            if(Packages.Count > index)
                return Packages[index];
            return "";
        }

        
        protected void Page_Load(object sender, EventArgs e)
        {
            plcPackageType.Visible = false;
            if (!IsPostBack)
            {
                txtInvDate.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
                string transferOrdId = Request.QueryString["fsto_id"];
                string orderStatus = ""; try { orderStatus = Request.QueryString["orderStatus"]; } catch { orderStatus = ""; }
                string statusName = ""; try { statusName = Request.QueryString["statusName"]; } catch { statusName = ""; }
                if (!string.IsNullOrEmpty(transferOrdId) && orderStatus == "51" && statusName == "Packed but not boxed")
                {
                    BoxDetails(transferOrdId);
                    plcManualPacking.Visible = false;
                    plcHead.Visible = true;
                    plcPackageType.Visible = true;
                    btnprint.Visible = plcPackageType.Visible;
                    string printVisibility = Request.QueryString["orderMethod"];
                    btnprint.Visible = !string.IsNullOrEmpty(printVisibility) && printVisibility == "1";
                }
                string fewitem= Request.QueryString["fewItemsFlag"];
                if (fewitem == "1")
                {
                    txtInvNum.Visible= txtInvDate.Visible= txtAmt.Visible= txtNumBags.Visible= false;
                }
            }
            if (String.IsNullOrEmpty(Request.QueryString["fsto_id"]))
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Invalid item selected. Please verify the order selected or the order is expired.", false, "/Tenant/PendingOrders");
                return;
            }

            int transferOrderId = 0; try { transferOrderId = Convert.ToInt32(Request.QueryString["fsto_id"]); } catch { transferOrderId = 0; }
            if (transferOrderId <= 0)
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Invalid item selected. Please verify the order selected or the order is expired.", false, "/Tenant/PendingOrders");
                return;
            }

            List<KeyValuePair<string, object>> orderparams = new List<KeyValuePair<string, object>>();
            orderparams.Add(new KeyValuePair<string, object>("transferOrdId", transferOrderId));
            orderparams.Add(new KeyValuePair<string, object>("storegroupid", this.CurrentUser.APIStoreId));

            var transferOrderItems = DataServiceMySql.GetDataTable($"SELECT fsto_isalreadypacked,fsto_pkdQty,fsto_ItemQty,(SELECT br_type FROM finascop_branch WHERE br_ID = fsto_source) AS branchType," +
                $"CASE WHEN fsto_ordertype = 0 THEN 'Branch Transfer' WHEN fsto_ordertype = 1 THEN 'B2C' WHEN fsto_ordertype = 2 THEN 'B2B' " +
                $"WHEN fsto_ordertype = 3 THEN 'Return' WHEN fsto_ordertype = 4 THEN 'Distribution' END AS fsto_ordertype FROM finascop_stock_transfer_order fto INNER JOIN finascop_stock_transfer_order_details tod ON fto.fsto_id = tod.fsto_id" +
                $" WHERE fto.fsto_id= @transferOrdId", UserService.GetAPIConnectionString(), orderparams);
            if (transferOrderItems != null || transferOrderItems.Rows.Count > 0)
            {
                DataRow dr = transferOrderItems.Rows[0];

                foreach (GridViewRow gr in gvManualPacking.Rows)
                {
                    TextBox txtQty = (TextBox)gr.FindControl("txtUpdate");
                    if ((Convert.ToInt32(dr["fsto_pkdQty"]) < (Convert.ToInt32(dr["fsto_ItemQty"])) && (Convert.ToInt32(dr["fsto_isalreadypacked"])) == 0))
                    {

                        txtQty.Enabled = true;
                    }
                    else if((Convert.ToInt32(dr["fsto_isalreadypacked"])) == 1)
                    {
                        txtQty.Enabled = false;
                    }
                }
            }
            string orderId = ""; try { orderId = Request.QueryString["orderId"]; } catch { orderId = ""; }
            if (orderId == null)
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Invalid item selected. Please verify the order selected or the order is expired.", false, "/Tenant/PendingOrders");
                return;
            }

            List<KeyValuePair<string, object>> ordrparams = new List<KeyValuePair<string, object>>();
            ordrparams.Add(new KeyValuePair<string, object>("orderId", orderId));
            string sql = $"SELECT order_id,order_order_id,order_customer_id,cust_mobile,cust_customer_name,status_id FROM retaline_customer_order INNER JOIN retaline_customer ON cust_id = order_customer_id WHERE order_order_id = @orderId";
            var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString(), ordrparams);
            
            string ordId = "", customerName = "", custPhone = "", statusId="";
            
                if (tblItems != null && tblItems.Rows.Count > 0)
                {
                    var ta = tblItems.Rows[0];
                    ordId = ta["order_order_id"].ToString();
                    customerName = ta["cust_customer_name"].ToString();
                    custPhone = ta["cust_mobile"].ToString();
                    statusId = ta["status_id"].ToString();
            }
            ltrOrderId.Text = ordId;
            ltrName.Text = customerName;
            ltrMobile.Text = custPhone;

            
        }
        // Box detalis after packing
        protected void BoxDetails(string transferOrderId)
        {
            List<KeyValuePair<string, object>> ordrparams = new List<KeyValuePair<string, object>>();
            ordrparams.Add(new KeyValuePair<string, object>("transferOrderId", transferOrderId));
            string sql = "SELECT quor_PacketCount, quor_RefNo FROM qugeo_order WHERE quor_TransferOrder_id = @transferOrderId";
            DataTable tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString(), ordrparams);

            if (tblItems != null && tblItems.Rows.Count > 0)
            {
                DataRow row = tblItems.Rows[0];
                int packCount = Convert.ToInt32(row["quor_PacketCount"]);
                string referenceNo = row["quor_RefNo"].ToString();

                if (packCount > 0)
                {
                    List<string> _packages = new List<string>();
                    for (int i = 1; i <= packCount; i++)
                    {
                        string packid = $"{referenceNo}/{packCount}/{i}";
                        _packages.Add(packid);
                    }

                    Packages = _packages;
                    rptPackageType.DataSource = _packages;

                    rptPackageType.DataBind();
                }
            }
        }


        protected void gvManualPacking_DataBound(object sender, EventArgs e)
        {
            int startRowOnPage = (gvManualPacking.PageIndex * gvManualPacking.PageSize) + 1;
            int lastRowOnPage = startRowOnPage + gvManualPacking.Rows.Count - 1;
            //ltrPageCurTotal.Text = lastRowOnPage.ToString();

            var dv = (DataView)SDSManualPacking.Select(DataSourceSelectArguments.Empty);
        }

        protected void btnManualPackingSubmit_Click(object sender, EventArgs e)
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

            string numberofBags = txtNumBags.Text, invoiceNumber = txtInvNum.Text, formattedDate = txtInvDate.Text, invoiceAmt = txtAmt.Text;
            double invoicdeAmount = 0;


            if (numberofBags == "")
            {
                numberofBags = "0";
            }
            else
            {
                numberofBags = txtNumBags.Text;
            }
            int noofbags = Convert.ToInt32(numberofBags);
            
            if (invoiceNumber == "")
            {
                rfInvDate.Visible = false;
                invoiceNumber = "0";
            }
            else
            {
                invoiceNumber = txtInvNum.Text;
            }
            
            if (invoiceAmt == "")
            {
                rfInvNum.Visible = false;
                invoicdeAmount = 0;
            }
            else
            {
                invoicdeAmount = Convert.ToDouble(txtAmt.Text);
            }
            DateTime invoiceDate;
            
            if (formattedDate == "")
            {
                rfAmount.Visible = false;
                formattedDate = "0";
            }
            else if (DateTime.TryParse(txtInvDate.Text, out invoiceDate))
            {
                // The date was successfully parsed, and you can use 'invoiceDate' here.
                 formattedDate = invoiceDate.ToString("yyyy-MM-dd");
            }

            //int itemids = 0;
            int packQuantity = 0;
            string fsto_uid = null;
            int fsid = 0;
            string fsto_updateon = null;
            var itemsList = new List<Dictionary<string, object>>();
            //dtOrderItems.AsEnumerable().Select(r=> r[""])
            decimal itemQtyy = 0, packQty = 0;
            int isAlreadyPacked = 0;
            Boolean allIsPacked = true;
            foreach (GridViewRow gr in gvManualPacking.Rows)
            {
                TextBox txtQty = (TextBox)gr.FindControl("txtUpdate");
                TextBox txtSubPrd = (TextBox)gr.FindControl("txtSubPrd");
                string subProduct = Convert.ToString(txtSubPrd.Text);
                decimal txtPackSub = 0;
                if(subProduct == "")
                {
                    txtPackSub = 0;
                }
                else
                {
                    txtPackSub = Convert.ToDecimal(subProduct);
                }

                
                if (String.IsNullOrEmpty(txtQty.Attributes["prodid"]))
                    continue;

                string strPId = txtQty.Attributes["prodid"];
                var dr = dtOrderItems.AsEnumerable().Where(r => r["fstod_id"].ToString() == strPId).FirstOrDefault();

                string fsidd = dr["fsto_id"].ToString();
                fsid = Convert.ToInt32(fsidd);
                string item_Id = dr["fsto_ItemId"].ToString();
                //string packedQuantity = da["fsto_pkdQty"].ToString();
                //string stockValue = dr["fsto_stockValue"].ToString();
                
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
                        Common.ShowCustomAlert(this.Page, "Successfully moved!", "Your item/s moved to incomplete order!", true, "/Tenant/PendingOrders");
                    }
                }

                else if ((((itemQtyy == packQty && isAlreadyPacked == 0) || isAlreadyPacked == 1)) && Convert.ToInt32(noofbags) >= 1)
                {
                   var result = Core.Services.APIService.SubmitManualPacking(fsto_uid, fsid, formattedDate, invoiceNumber, invoicdeAmount, noofbags, fsto_updateon, itemsList);
                    if (result != null && result.status.ToLower() == "ok" && result.packinglist.packingNumber.Length > 0)
                    {
                    
                    var dtCustOrder = DataServiceMySql.GetDataTable($"SELECT cfg_Value FROM sys_configuration WHERE cfg_Name='COURIER_PACKING' ", UserService.GetAPIConnectionString());
                    if(dtCustOrder != null && dtCustOrder.Rows.Count >= 0)
                    {
                        DataRow db = dtCustOrder.Rows[0];
                        if (Convert.ToInt32(db["cfg_Value"]) == 0)
                        {
                            plcPackageType.Visible = false;
                            btnprint.Visible = false;
                            string msgContent = $"<p class=\"lh-3 mg-b-20\">Thank you for packing the order. As per the input you gave, this order has <a class=\"tx-inverse hover-primary\">{result.packinglist.packingNumber.Length}</a> box. Please write the following reference on packets to help the delivery team.</p>";
                            for (int i = 0; i < result.packinglist.packingNumber.Length; i++)
                            {
                                msgContent += $"<div class=\"row\"><div class=\"col-4 border text-left\">Packet {i + 1}</div><div class=\"col-8 border text-left\">{result.packinglist.packingNumber[i]}</div></div>";
                            }
                            Common.ShowCustomAlert(this.Page, "Packed Successfully!", msgContent, true, "/Tenant/PendingOrders");
                        }
                        else
                        {
                            //NumberOfPackages = result.packinglist.packingNumber.Length;
                            List<string> _packages = new List<string>();
                            for (int i = 0; i < result.packinglist.packingNumber.Length; i++)
                            {
                                _packages.Add(result.packinglist.packingNumber[i]);                               
                            }
                            Packages = _packages;

                            rptPackageType.DataSource = _packages;
                            rptPackageType.DataBind();
                            plcManualPacking.Visible = false;
                            plcHead.Visible = true;
                            plcPackageType.Visible = true;
                            btnprint.Visible = plcPackageType.Visible;


                        }
                    }
                }

             }
        }
        protected void btnPackageSubmit_Click(object sender, EventArgs e)
        {
            ProcessPackageDetails();
        }

        protected void ProcessPackageDetails()
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
                    packparams.Add(new KeyValuePair<string, object>("packetWeight", weight.Text));
                    packparams.Add(new KeyValuePair<string, object>("orderId", orderId));
                    packparams.Add(new KeyValuePair<string, object>("orderStatus", 9));
                    packparams.Add(new KeyValuePair<string, object>("tranorderStatus", 10));
                    string insertQry = $"INSERT INTO retaline_transfer_order_pack_details(rtopd_fstoId, rtopd_orderType, rtopd_packets, rtopd_packaging, rtpod_length, rtpod_breadth, rtpod_height, rtopd_packetweigh) " +
                                        $"VALUES(@transferOrdId,@orderType,@packets,@packaging,@length,@breadth,@height,@packetWeight)";
                    DataServiceMySql.ExecuteSql(insertQry, Service.UserService.GetAPIConnectionString(), packparams);

                    string updateOrderTblQry = $"UPDATE retaline_customer_order SET status_id=@orderStatus WHERE order_order_id = @orderId";
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
                        Common.ShowCustomAlert(this.Page, "Success", "Your item has been packed successfully!", true, "/Tenant/PendingOrders");
                    
                }
            }
        }


        protected void SDSManualPacking_Selected(object sender, SqlDataSourceStatusEventArgs e)
        {
            //ltrPageTotal.Text = e.AffectedRows.ToString();
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

        protected void SDSPackageType_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            int orderMethod = 0; try { orderMethod = Convert.ToInt32(Request.QueryString["orderMethod"]); } catch { orderMethod = 0; }
            if(orderMethod == 3)
            {
                e.Command.Parameters["packtype"].Value = 2;
            }
            else 
            {
                e.Command.Parameters["packtype"].Value = 1;
            }
        }

        protected void gvManualPacking_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            try
            {
                if (e.Row.RowType == DataControlRowType.Header)
                {
                    BoundField myBoundField = (BoundField)((DataControlFieldCell)e.Row.Cells[2]).ContainingField;

                    if (myBoundField != null)
                    {
                        if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
                        {
                            myBoundField.HeaderText = "MRP";
                        }
                        else
                        {
                            myBoundField.HeaderText = "RRP";
                        }
                    }
                }
            }
            catch
            {

            }
        }

        

        protected void selPackageType_SelectedIndexChanged(object sender, EventArgs e)
        {
            plcPackageType.Visible = true;
            btnprint.Visible = plcPackageType.Visible;
            DropDownList selPackageType = (DropDownList)sender;
            string selectedValue = selPackageType.SelectedValue;

            foreach (RepeaterItem item in rptPackageType.Items)
            {
                if (item.ItemType == ListItemType.Item || item.ItemType == ListItemType.AlternatingItem)
                {
                    TextBox txtLength = (TextBox)item.FindControl("txtLength");
                    TextBox txtbreadth = (TextBox)item.FindControl("txtbreadth");
                    TextBox txtHeight = (TextBox)item.FindControl("txtHeight");

                    if (item.FindControl("selPackageType") == selPackageType)
                    {
                        if (selectedValue == "-1")
                        {
                            txtLength.Enabled = true;
                            txtbreadth.Enabled = true;
                            txtHeight.Enabled = true;
                        }
                        else
                        {
                            txtLength.Enabled = false;
                            txtbreadth.Enabled = false;
                            txtHeight.Enabled = false;

                            PopulatePackageDimensions(selectedValue, txtLength, txtbreadth, txtHeight);
                        }
                    }
                }
            }
        }

        private void PopulatePackageDimensions(string packageType, TextBox txtLength, TextBox txtbreadth, TextBox txtHeight)
        {
            DataTable dtTemplate = GetDataFromDatabase(packageType);

            if (dtTemplate.Rows.Count > 0)
            {
                txtLength.Text = dtTemplate.Rows[0]["rpckm_length"].ToString();
                txtbreadth.Text = dtTemplate.Rows[0]["rpckm_breadth"].ToString();
                txtHeight.Text = dtTemplate.Rows[0]["rpckm_height"].ToString();
            }
        }

        private DataTable GetDataFromDatabase(string packageType)
        {
            string packageId = packageType;

            List<KeyValuePair<string, object>> templateParams = new List<KeyValuePair<string, object>>();
            templateParams.Add(new KeyValuePair<string, object>("packageMasterId", packageId));
            DataTable dtTemplate = DataServiceMySql.GetDataTable(
                $"SELECT rpckm_id,rpckm_name,rpckm_length,rpckm_breadth,rpckm_height FROM retaline_package_master WHERE rpckm_id=@packageMasterId", Service.UserService.GetAPIConnectionString(), templateParams);

            return dtTemplate;
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
                return ("",0,0);                       
            // Calculate item quantity and count
            int itemQty =  dtitems.AsEnumerable().Sum(r => Convert.ToInt32(r["item_order_qty"]));
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
            plcPackageType.Visible = true;
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
                replacements.Add(new KeyValuePair<string, string>("[saleconfirmed]",formattedDate));
                replacements.Add(new KeyValuePair<string, string>("[totalitem]", result.itemQty.ToString()));
                replacements.Add(new KeyValuePair<string, string>("[itemcount]", result.itemCount.ToString()));
                replacements.Add(new KeyValuePair<string, string>("[OrderDetalis]", result.strItemBody));
                body = EmailService.CreateEmailbody(EmailType.orderslip, replacements);
                string orderBody = body.Replace("'", "\\'").Replace("\n", "").Replace("\r", "");
                string script = $@"var printWindow = window.open('', '_blank'); printWindow.document.open();printWindow.document.write('<html><head><title>Print Preview</title><style>body {{ font-family: Arial, sans-serif; }}</style></head><body>');printWindow.document.write('{orderBody}');printWindow.document.write('</body></html>'); printWindow.document.close();";
                ScriptManager.RegisterStartupScript(this, GetType(), "PrintWindow", script, true); ;
            }
            catch
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Invalid item selected. Please verify the order selected or the order is expired.", false);
                return;
            }

        }

        
    }

}


