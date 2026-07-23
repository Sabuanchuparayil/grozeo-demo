using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class ViewAndUpdate: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            string transferOrderId = Request.QueryString["orderid"];
            Service.User usr = this.CurrentUser;
            int orderStoreGroupId = 0;
            //if (String.IsNullOrEmpty(transferOrderId))
            //{
            //    pnlInvalidOrder.Visible = true;
            //    pnlValidOrder.Visible = plcActionButtonsRow.Visible = false;
            //    return;
            //}

            List<KeyValuePair<string, object>> toparams = new List<KeyValuePair<string, object>>();
            toparams.Add(new KeyValuePair<string, object>("transOrdId", transferOrderId));
            DataTable result = DataServiceMySql.GetDataTable($"SELECT fsto_uid,fsto_createdOn,fsto_destination,(SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_source) AS sourcename," +
                $"fsto_ordertype,fstr_id,fsto_status,CASE WHEN fsto_ordertype = 0 THEN 'Branch Transfer' WHEN fsto_ordertype = 1 THEN 'B2C' " +
                $"WHEN fsto_ordertype = 2 THEN 'B2B' WHEN fsto_ordertype = 3 THEN 'Return' WHEN fsto_ordertype = 4 THEN 'Distribution' END AS fsto_ordertypeName," +
                $"(SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) AS branch, fsto_id FROM finascop_stock_transfer_order WHERE fsto_id  = @transOrdId", UserService.GetAPIConnectionString(), toparams);
            DataRow dr = result.Rows[0];

            DataTable totalItems = DataServiceMySql.GetDataTable($"SELECT COUNT(*) AS cnt FROM finascop_stock_transfer_order_details WHERE fsto_id = @transOrdId", UserService.GetAPIConnectionString(), toparams);
            DataRow da = totalItems.Rows[0];

            DataTable itemQty = DataServiceMySql.GetDataTable($"SELECT SUM(fsto_ItemQty) AS fsto_ItemQty FROM finascop_stock_transfer_order_details WHERE fsto_id = @transOrdId", UserService.GetAPIConnectionString(), toparams);
            DataRow db = itemQty.Rows[0];

            DataTable pkdQty = DataServiceMySql.GetDataTable($"SELECT ROUND(SUM(fsto_ItemQty*(SELECT stit_ConvertCalcRate FROM finascop_stock_itemmaster WHERE stit_ID = fsto_ItemId)),3) AS packedQty FROM finascop_stock_transfer_order_details WHERE fsto_id = @transOrdId", UserService.GetAPIConnectionString(), toparams);
            DataRow dc = pkdQty.Rows[0];

            DataTable conversionpkdQty = DataServiceMySql.GetDataTable($"SELECT SUM(fsto_stockValue)AS stockValue FROM finascop_stock_transfer_order_details WHERE fsto_id = @transOrdId", UserService.GetAPIConnectionString(), toparams);
            DataRow dz = conversionpkdQty.Rows[0];

            if ((Convert.ToInt32(dr["fsto_ordertype"])) == 1)
            {
                string transferReqId = dr["fstr_id"].ToString();
                List<KeyValuePair<string, object>> paparams = new List<KeyValuePair<string, object>>();
                paparams.Add(new KeyValuePair<string, object>("requestId", transferReqId));
                DataTable parentOrder = DataServiceMySql.GetDataTable($"SELECT order_id,order_order_id AS paOrderNumber,order_confirm_date AS paOrderDate,order_customer_id AS custId,total AS totalAmt," +
                    $"CASE WHEN payment_mode = 1 THEN 'Pay On Delivery' " +
                    $"WHEN payment_mode = 2 THEN 'Online Payment' " +
                    $"WHEN payment_mode = 3 THEN 'Wallet' " +
                    $"WHEN payment_mode = 4 THEN 'COD with Wallet' " +
                    $"WHEN payment_mode = 5 THEN 'Online with Wallet' " +
                    $"WHEN payment_mode = 6 THEN 'Online on Delivery' " +
                    $"WHEN payment_mode = 7 THEN 'Cash on Delivery' END AS paymentMode," +
                    $"payment_mode, order_delivery_charge, order_courier_charge FROM retaline_customer_order WHERE order_id = @requestId", UserService.GetAPIConnectionString(), paparams);
                DataRow dx = parentOrder.Rows[0];
                string custId = dx["custId"].ToString();

                List<KeyValuePair<string, object>> custparams = new List<KeyValuePair<string, object>>();
                custparams.Add(new KeyValuePair<string, object>("customerId", custId));
                DataTable customerName = DataServiceMySql.GetDataTable($"SELECT cust_customer_name,cust_mobile,cust_walletbalance FROM retaline_customer WHERE cust_id = @customerId", UserService.GetAPIConnectionString(), custparams);
                DataRow dy = customerName.Rows[0];

                

                DataTable parentOrderedItems = DataServiceMySql.GetDataTable($"SELECT item_id,item_order_id,customer_order_id,item_product_id," +
                    $"item_group_id,item_order_qty,item_order_qty_scanned,item_return_qty_sellable,item_return_qty_damaged, item_return_qty_damagedinTransit, " +
                    $"item_return_qty_requested, item_price, item_price_packed, item_retail_price,item_sales_price, item_subcategory_id, item_package_type_id, " +
                    $"item_is_taxable, item_cgst, item_sgst, item_igst, item_kfc, item_type, item_type_offer,item_coupon_code, bom_id, item_amount, item_discount, " +
                    $"item_discount_total, item_sku_id, item_status, created_at, updated_at, deleted_at,item_isMedicine, item_prescription_validated, order_branch_id, branch_type_id FROM retaline_customer_order_items WHERE customer_order_id = @requestId", UserService.GetAPIConnectionString(), paparams);
                //DataRow ds = parentOrderedItems.Rows[0];

                double currentTotal = Convert.ToDouble(dx["totalAmt"]);
                double deliveryCharge = Convert.ToDouble(dx["order_delivery_charge"]) + Convert.ToDouble(dx["order_courier_charge"]);

                double newSutotal = 0;

                foreach (DataRow ds in parentOrderedItems.Rows)
                {
                    double itemPacked = 0;
                    List<KeyValuePair<string, object>> itmparams = new List<KeyValuePair<string, object>>();
                    itmparams.Add(new KeyValuePair<string, object>("transOrdId", transferOrderId));
                    itmparams.Add(new KeyValuePair<string, object>("itemId", Convert.ToInt32(ds["item_product_id"])));
                    if (Convert.ToInt32(dr["fsto_status"]) != 9)
                    {
                        DataTable itemPackedSPincTax = DataServiceMySql.GetDataTable($"SELECT fstro_ItemPackedSPincTax FROM finascop_stock_transfer_order_details WHERE fsto_id = @transOrdId AND fsto_ItemId = @itemId", UserService.GetAPIConnectionString(), itmparams);
                        DataRow dd = itemPackedSPincTax.Rows[0];
                        itemPacked = Convert.ToDouble(dd["fstro_ItemPackedSPincTax"]);
                    }
                    else
                    {
                        DataTable itemPackedSPincTax = DataServiceMySql.GetDataTable($"SELECT (fsto_pkdQty*fstro_ItemSPincTax) AS fstro_ItemPackedSPincTax  FROM finascop_stock_transfer_order_details WHERE fsto_id = @transOrdId AND fsto_ItemId = @itemId", UserService.GetAPIConnectionString(), itmparams);
                        DataRow dm = itemPackedSPincTax.Rows[0];
                        itemPacked = Convert.ToDouble(dm["fstro_ItemPackedSPincTax"]);
                    }

                    newSutotal += itemPacked;
                }
                double newOrderTotal = 0;
                if (newSutotal > 0)
                {
                    newOrderTotal = newSutotal + deliveryCharge;
                }
                else
                {
                    newOrderTotal = newSutotal;
                }

                newOrderTotal = Math.Round((newOrderTotal), 2);
                double balanceToPay = 0;
                if (newOrderTotal > currentTotal)
                {
                    balanceToPay = newOrderTotal - currentTotal;
                }
                else
                {
                    balanceToPay = 0;
                }

                ltrOrdId.Text = dx["paOrderNumber"].ToString();
                ltrFrom.Text = dr["sourcename"].ToString();
                ltrToNo.Text = dr["fsto_uid"].ToString();
                ltrCustName.Text = dy["cust_customer_name"].ToString();
                ltrNoItems.Text = da["cnt"].ToString();
                ltrValue.Text = dx["totalAmt"].ToString();
                ltrUpdtedValue.Text = Convert.ToString(newOrderTotal);
                ltrBalToPay.Text = Convert.ToString(balanceToPay);

                ltrOrderType.Text = dr["fsto_ordertypeName"].ToString();
                ltrTo.Text = dy["cust_customer_name"].ToString();
                ltrOrdDte.Text = dx["paOrderDate"].ToString();
                ltrCustNo.Text = dy["cust_mobile"].ToString();
                ltrTtlQty.Text = db["fsto_ItemQty"].ToString();
                ltritmweigh.Text = dc["packedQty"].ToString();
                ltrActweigh.Text = dz["stockValue"].ToString();
                ltrModePay.Text = dx["paymentMode"].ToString();

                hlCancelOrd.NavigateUrl = string.Format("~/Tenant/AssignOrdPicker.aspx?orderid={0}&toid={1}&ordId={2}", dr["fsto_id"], dr["fsto_uid"], dx["order_id"]);
            }

            
        }

        protected void SDSItemDetails_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            string transferOrderId = Request.QueryString["orderid"];
            e.Command.Parameters["fsto_id"].Value = transferOrderId;
        }

        protected void btnSave_Click(object sender, EventArgs e)
        {
            string transferOrderId = Request.QueryString["orderid"];
            List<KeyValuePair<string, object>> logparams = new List<KeyValuePair<string, object>>();
            logparams.Add(new KeyValuePair<string, object>("transOrdId", transferOrderId));
            logparams.Add(new KeyValuePair<string, object>("userId", 0));
            logparams.Add(new KeyValuePair<string, object>("reason", txtReason.Text));
            logparams.Add(new KeyValuePair<string, object>("createdBy", 1));
            string insertQry = $"INSERT INTO finascop_stock_transfer_order_callskip_log(fsto_id, UserId, ftocl_remarks,ftocl_createdBy) " +
                                $"VALUES(@transOrdId,@userId,@reason,@createdBy)";
            DataServiceMySql.ExecuteSql(insertQry, Service.UserService.GetAPIConnectionString(), logparams);

            string updateQry = "UPDATE finascop_stock_transfer_order_details_barcodes_temp SET rpb_status=3 WHERE tmp_barcode_fstoId=@transOrdId";
            DataServiceMySql.ExecuteSql(updateQry, Service.UserService.GetAPIConnectionString(), logparams);
            // Activitylog
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string Source = strUrl;
            int storegroupid = this.CurrentUser.APIStoreId;
            string Users = this.CurrentUser.Email;
            string storegroup_id = (this.CurrentUser.APIStoreId).ToString();
            string transferOrdId = transferOrderId.ToString();
            string userId ="0";            
            string reason = txtReason.Text;
            string createdBy = "1";
            var items = new[]
            {
                    new { Key = "Storegroup Id", Value = storegroup_id },
                    new { Key = "Transfer Order Id", Value = transferOrdId },
                    new { Key = "User Id", Value = userId },
                    new { Key = "Reason", Value = reason },
                    new { Key = "createdBy", Value = createdBy },                  
            };
            string Description = string.Join(", ", items.Select(Item => $"{Item.Key}={Item.Value}"));
            var strresult = Activitylog.ActivitylogAsync(storegroupid, Source, Users, Description);

            Common.ShowCustomAlert(this.Page, "Data updated!", "Data updated successfully!", true, "/Tenant/PendingOrders");
        }

        protected void btnProceed_Click(object sender, EventArgs e)
        {
            string transferOrderId = Request.QueryString["orderid"];

            List<KeyValuePair<string, object>> stsparams = new List<KeyValuePair<string, object>>();
            stsparams.Add(new KeyValuePair<string, object>("transOrdId", transferOrderId));
            DataTable fstoStatusTbl = DataServiceMySql.GetDataTable($"SELECT SUM(fsto_pkdQty) AS pckSum FROM finascop_stock_transfer_order_details WHERE fsto_id = @transOrdId", UserService.GetAPIConnectionString(), stsparams);
            DataRow dk = fstoStatusTbl.Rows[0];
            if (Convert.ToInt32(dk["pckSum"]) > 0)
            {
                try
                {
                    foreach (GridViewRow gr in gvItemDetails.Rows)
                    {
                        HiddenField hidid = (HiddenField)gr.FindControl("hidId");
                        if (hidid == null || String.IsNullOrEmpty(hidid.Value))
                            continue;
                        //string itemPickedQty = Request.QueryString["pickedQty"];
                        //string itemPickedQty = Convert.ToString(btnAssign.Attributes["pickedQty"]);
                        //decimal itemQty = Convert.ToDecimal(itemPickedQty);
                        if (Convert.ToDecimal(hidid.Value) > 0)
                        {
                            List<KeyValuePair<string, object>> toparams = new List<KeyValuePair<string, object>>();
                            toparams.Add(new KeyValuePair<string, object>("transOrdId", transferOrderId));
                            toparams.Add(new KeyValuePair<string, object>("fsto_status", 6));
                            toparams.Add(new KeyValuePair<string, object>("updatedBy", 1));

                            string updateQry = "UPDATE finascop_stock_transfer_order SET fsto_status=@fsto_status,fsto_updateby=@updatedBy WHERE fsto_id=@transOrdId";
                            DataServiceMySql.ExecuteSql(updateQry, Service.UserService.GetAPIConnectionString(), toparams);

                            DataTable type = DataServiceMySql.GetDataTable($"SELECT fsto_ordertype FROM finascop_stock_transfer_order WHERE fsto_id = @transOrdId", UserService.GetAPIConnectionString(), toparams);
                            DataRow dr = type.Rows[0];

                            DataTable requestId = DataServiceMySql.GetDataTable($"SELECT fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = @transOrdId", UserService.GetAPIConnectionString(), toparams);
                            DataRow dt = requestId.Rows[0];

                            string transferReqId = dt["fstr_id"].ToString();
                            List<KeyValuePair<string, object>> reparams = new List<KeyValuePair<string, object>>();
                            reparams.Add(new KeyValuePair<string, object>("requestId", transferReqId));

                            DataTable orderTotal = DataServiceMySql.GetDataTable($"SELECT total,order_delivery_charge,payment_mode,order_customer_id,order_order_id, order_branch_id, (SELECT br_Name FROM finascop_branch WHERE br_ID=order_branch_id) AS branchName FROM retaline_customer_order WHERE order_id = @requestId", UserService.GetAPIConnectionString(), reparams);
                            DataRow ds = orderTotal.Rows[0];

                            if (Convert.ToInt32(dr["fsto_ordertype"]) == 1)
                            {
                                DataTable diffamt = DataServiceMySql.GetDataTable($"SELECT sum(fstro_ItemSPincTax*fsto_pkdQty) as amt FROM finascop_stock_transfer_order_details WHERE fsto_id = @transOrdId", UserService.GetAPIConnectionString(), toparams);
                                DataRow dx = diffamt.Rows[0];


                                double differentamt = Convert.ToDouble(dx["amt"]) + Convert.ToDouble(ds["order_delivery_charge"]);

                                if (differentamt <= 0)
                                {
                                    Common.ShowToastifyMessage(this.Page, "Unable to calculate new Order amount.", "danger");
                                }

                                List<KeyValuePair<string, object>> cutparams = new List<KeyValuePair<string, object>>();
                                cutparams.Add(new KeyValuePair<string, object>("requestId", transferReqId));
                                cutparams.Add(new KeyValuePair<string, object>("statusId", 7));
                                cutparams.Add(new KeyValuePair<string, object>("ttlAftPacking", differentamt));
                                string updateQuery = "UPDATE retaline_customer_order SET status_id=@statusId,total_afterpacking=@ttlAftPacking WHERE order_id=@requestId";
                                DataServiceMySql.ExecuteSql(updateQuery, Service.UserService.GetAPIConnectionString(), cutparams);

                                List<KeyValuePair<string, object>> hisparams = new List<KeyValuePair<string, object>>();
                                hisparams.Add(new KeyValuePair<string, object>("requestId", transferReqId));
                                hisparams.Add(new KeyValuePair<string, object>("status", 7));
                                hisparams.Add(new KeyValuePair<string, object>("action", "Proceed with aval Qty"));
                                hisparams.Add(new KeyValuePair<string, object>("createdBy", 1));
                                string insertQry = $"INSERT INTO retaline_customer_order_history(order_id, order_action, order_status) " +
                                                    $"VALUES(@requestId,@action,@status)";
                                DataServiceMySql.ExecuteSql(insertQry, Service.UserService.GetAPIConnectionString(), hisparams);

                                double refundAmt = 0;
                                if (Convert.ToInt32(ds["payment_mode"]) == 2 || Convert.ToInt32(ds["payment_mode"]) == 3 || Convert.ToInt32(ds["payment_mode"]) == 5)
                                {
                                    refundAmt = Convert.ToDouble(ds["total"]) - differentamt;
                                }

                                else if (Convert.ToInt32(ds["payment_mode"]) == 1 || Convert.ToInt32(ds["payment_mode"]) == 4 || Convert.ToInt32(ds["payment_mode"]) == 6 || Convert.ToInt32(ds["payment_mode"]) == 7)
                                {
                                    DataTable amtPayable = DataServiceMySql.GetDataTable($"SELECT order_amount_payable FROM retaline_customer_order WHERE order_id = @requestId", UserService.GetAPIConnectionString(), cutparams);
                                    DataRow dm = amtPayable.Rows[0];

                                    if ((Convert.ToDouble(ds["total"]) - differentamt) > Convert.ToInt32(dm["order_amount_payable"]))
                                    {
                                        refundAmt = (Convert.ToDouble(ds["total"]) - differentamt) - Convert.ToInt32(dm["order_amount_payable"]);
                                    }

                                    if ((Convert.ToDouble(ds["total"]) - differentamt) < Convert.ToInt32(dm["order_amount_payable"]))
                                    {
                                        List<KeyValuePair<string, object>> ordparams = new List<KeyValuePair<string, object>>();
                                        ordparams.Add(new KeyValuePair<string, object>("requestId", transferReqId));
                                        ordparams.Add(new KeyValuePair<string, object>("amtPayable", Convert.ToDouble(dm["order_amount_payable"])));
                                        string updateQuery1 = "UPDATE retaline_customer_order SET order_amount_payable=@amtPayable WHERE order_id=@requestId";
                                        DataServiceMySql.ExecuteSql(updateQuery1, Service.UserService.GetAPIConnectionString(), ordparams);
                                    }
                                }

                                if (refundAmt > 0)
                                {
                                    List<KeyValuePair<string, object>> walletparams = new List<KeyValuePair<string, object>>();
                                    walletparams.Add(new KeyValuePair<string, object>("refundAmt", refundAmt));
                                    walletparams.Add(new KeyValuePair<string, object>("customerId", Convert.ToInt32(ds["order_customer_id"])));
                                    //string uptQuery = "UPDATE retaline_customer SET cust_walletbalance=cust_walletbalance+@refundAmt WHERE cust_id=@customerId";
                                    //DataServiceMySql.ExecuteSql(uptQuery, Service.UserService.GetAPIConnectionString(), walletparams);

                                    List<KeyValuePair<string, object>> wallettransparams = new List<KeyValuePair<string, object>>();
                                    wallettransparams.Add(new KeyValuePair<string, object>("refentryId", transferReqId));
                                    wallettransparams.Add(new KeyValuePair<string, object>("customerId", Convert.ToInt32(ds["order_customer_id"])));
                                    wallettransparams.Add(new KeyValuePair<string, object>("sourceType", 1));
                                    wallettransparams.Add(new KeyValuePair<string, object>("amount", refundAmt));
                                    wallettransparams.Add(new KeyValuePair<string, object>("barcode", 0));
                                    wallettransparams.Add(new KeyValuePair<string, object>("addInfo", "Refund due insufficient items to deliver of order" + ds["order_order_id"].ToString()));
                                    //string instQry = $"INSERT INTO retaline_customer_wallet_transaction(cust_id, refentry_id, brcw_SourceType,brcw_Amount,brcw_AddInfo,stiid_barcode) " +
                                    //                $"VALUES(@customerId,@refentryId,@sourceType,@amount,@addInfo,@barcode)";
                                    //DataServiceMySql.ExecuteSql(instQry, Service.UserService.GetAPIConnectionString(), wallettransparams);


                                    string refentryId = transferReqId;
                                    int customerId = Convert.ToInt32(ds["order_customer_id"]);
                                    //int sourceType = 1;
                                    double amount = refundAmt;
                                    string addInfo = "Order" + " " + ds["order_order_id"].ToString() + " " + "from" + " " + ds["branchName"].ToString() + " " + "cancelled by" + " " + this.CurrentUser.StoreGroupName + " " + "after clarification with customer due to item(s) unavailability";
                                    string result = Core.Services.APIService.WalletBalance(customerId, refentryId, amount, addInfo);
                                }
                            }

                            else if (Convert.ToInt32(dr["fsto_ordertype"]) == 2)
                            {
                                List<KeyValuePair<string, object>> b2bparams = new List<KeyValuePair<string, object>>();
                                b2bparams.Add(new KeyValuePair<string, object>("refentryId", transferReqId));
                                b2bparams.Add(new KeyValuePair<string, object>("statusId", 7));
                                string uptQuery2 = "UPDATE retaline_B2B_SalesOrder SET status_id=@statusId WHERE bbso_id=@refentryId";
                                DataServiceMySql.ExecuteSql(uptQuery2, Service.UserService.GetAPIConnectionString(), b2bparams);
                            }

                            else if (Convert.ToInt32(dr["fsto_ordertype"]) == 3)
                            {
                                List<KeyValuePair<string, object>> rpparams = new List<KeyValuePair<string, object>>();
                                rpparams.Add(new KeyValuePair<string, object>("refentryId", transferReqId));
                                rpparams.Add(new KeyValuePair<string, object>("statusId", 1));
                                string uptQuery3 = "UPDATE finascop_stock_return_request_packing SET frrp_status=@statusId WHERE frrp_id=@refentryId";
                                DataServiceMySql.ExecuteSql(uptQuery3, Service.UserService.GetAPIConnectionString(), rpparams);
                            }
                            // Activitylog
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
                            string refentry_Id = transferReqId;
                            string customer_Id = (ds["order_customer_id"]).ToString();
                          
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
                }
                catch
                {
                    Common.ShowToastifyMessage(this.Page, "Execution failed" + "Execution failure No item matches the criteria. Please contact support for more details.", "danger");
                }
            }

            else
            {
                Common.ShowToastifyMessage(this.Page, "Sorry, No items picked for this order", "danger");
            }

        }

        protected void btnRevert_Click(object sender, EventArgs e)
        {
            //Button revertBtn = (Button)sender;
            //if (revertBtn == null)
            //    return;

            string transferOrderId = Request.QueryString["orderid"];
            List<KeyValuePair<string, object>> toparams = new List<KeyValuePair<string, object>>();
            toparams.Add(new KeyValuePair<string, object>("transOrdId", transferOrderId));
            DataTable statusTbl = DataServiceMySql.GetDataTable($"SELECT fsto_status FROM finascop_stock_transfer_order WHERE fsto_id = @transOrdId", UserService.GetAPIConnectionString(), toparams);
            DataRow dr = statusTbl.Rows[0];
            int status = Convert.ToInt32(dr["fsto_status"]);

            if (status != 9 && status != 20)
            {
                Common.ShowToastifyMessage(this.Page, "Execution failed" + "You cant revert this order, this is not an Incomplete order.", "danger");
            }

            else
            {
                List<KeyValuePair<string, object>> toparameters = new List<KeyValuePair<string, object>>();
                toparameters.Add(new KeyValuePair<string, object>("transOrdId", transferOrderId));
                toparameters.Add(new KeyValuePair<string, object>("updatedBy", 1));
                toparameters.Add(new KeyValuePair<string, object>("statusId", 6));
                toparameters.Add(new KeyValuePair<string, object>("isalreadyPacked", 0));
                string uptQuery = "UPDATE finascop_stock_transfer_order SET fsto_status=@statusId, fsto_isalreadypacked=@isalreadyPacked, fsto_updateby=@updatedBy WHERE fsto_id=@transOrdId";
                DataServiceMySql.ExecuteSql(uptQuery, Service.UserService.GetAPIConnectionString(), toparameters);

                DataTable type = DataServiceMySql.GetDataTable($"SELECT fsto_ordertype,fstr_id FROM finascop_stock_transfer_order WHERE fsto_id = @transOrdId", UserService.GetAPIConnectionString(), toparams);
                DataRow dt = type.Rows[0];
                if ((Convert.ToInt32(dt["fsto_ordertype"])) == 1)
                {
                    List<KeyValuePair<string, object>> ftoparams = new List<KeyValuePair<string, object>>();
                    ftoparams.Add(new KeyValuePair<string, object>("requestId", dt["fstr_id"].ToString()));
                    ftoparams.Add(new KeyValuePair<string, object>("statusId", 7));
                    string uptQuery4 = "UPDATE retaline_customer_order SET status_id=@statusId WHERE order_id=@requestId";
                    DataServiceMySql.ExecuteSql(uptQuery4, Service.UserService.GetAPIConnectionString(), ftoparams);

                    List<KeyValuePair<string, object>> hisparams = new List<KeyValuePair<string, object>>();
                    hisparams.Add(new KeyValuePair<string, object>("requestId", dt["fstr_id"].ToString()));
                    hisparams.Add(new KeyValuePair<string, object>("statusId", 7));
                    hisparams.Add(new KeyValuePair<string, object>("action", "Reverted"));
                    string instQry = $"INSERT INTO retaline_customer_order_history(order_id,order_action,order_status) " +
                                    $"VALUES(@requestId,@action,@statusId)";
                    DataServiceMySql.ExecuteSql(instQry, Service.UserService.GetAPIConnectionString(), hisparams);
                }

                else if ((Convert.ToInt32(dt["fsto_ordertype"])) == 2)
                {
                    List<KeyValuePair<string, object>> b2bparams = new List<KeyValuePair<string, object>>();
                    b2bparams.Add(new KeyValuePair<string, object>("requestId", dt["fstr_id"].ToString()));
                    b2bparams.Add(new KeyValuePair<string, object>("statusId", 7));
                    string uptQry = "UPDATE retaline_B2B_SalesOrder SET status_id=@statusId WHERE bbso_id=@requestId";
                    DataServiceMySql.ExecuteSql(uptQry, Service.UserService.GetAPIConnectionString(), b2bparams);
                }

                else if ((Convert.ToInt32(dt["fsto_ordertype"])) == 3)
                {
                    List<KeyValuePair<string, object>> rpparams = new List<KeyValuePair<string, object>>();
                    rpparams.Add(new KeyValuePair<string, object>("requestId", dt["fstr_id"].ToString()));
                    rpparams.Add(new KeyValuePair<string, object>("statusId", 1));
                    string uptQry = "UPDATE finascop_stock_return_request_packing SET frrp_status=@statusId WHERE frrp_id=@requestId";
                    DataServiceMySql.ExecuteSql(uptQry, Service.UserService.GetAPIConnectionString(), rpparams);
                }
                Common.ShowCustomAlert(this.Page, "Order reverted!", "Order reverted successfully!", true, "/Tenant/PendingOrders");
            }
            
        }
    }
}