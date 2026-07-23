using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class OrderDetailsNew : Base.BasePartnerPage
    {
        protected string SourcePage => Request.QueryString["page"];
        protected void Page_Load(object sender, EventArgs e)
        {
            string transferOrderId = Request.QueryString["ordId"];
            string orderId = Request.QueryString["ordorderId"];
            
            if (String.IsNullOrEmpty(transferOrderId))
            {
                pnlInvalidOrder.Visible = true;
                pnlValidOrder.Visible = plcActionButtonsRow.Visible = false;
                return;
            }
            if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
            {
                tsccess.Visible = false;
            }

            dvNoneSponsoredPack.Visible = false;
            dvNoneSponsoredDelivery.Visible = false;
            OrderDetailsView(transferOrderId);
        }

        private void OrderDetailsView(string transferOrderId)
        {
            if (!string.IsNullOrEmpty(SourcePage))
            {
                Service.User usr = this.CurrentUser;
                int orderStoreGroupId = 0;
                string sql = $"SELECT order_id,order_order_id,order_packedbags_count,order_customer_id,fstr_id,fsto_ordertype,br_storeGroup,order_branch_id, CONCAT(br_Name, '-', branch_shortname) AS br_Name, bco.status_id AS STATUS,DATE_FORMAT(created_at, '%d-%m-%Y') AS order_created_on, TIME_FORMAT(CAST(created_at AS TIME), '%r') AS ordertime, admin_description AS order_status, (SELECT cust_customer_name FROM retaline_customer WHERE cust_id = order_customer_id) AS delivery_to, (SELECT cust_mobile FROM retaline_customer WHERE cust_id = order_customer_id) AS cust_mobile, order_HasReturn, order_ItemsReturned, order_ReturnVerified, CASE WHEN fsto_ordertype = 0 THEN 'CPD TO BR' WHEN fsto_ordertype = 1 THEN 'B2C' WHEN fsto_ordertype = 2 THEN 'B2B' END AS fsto_ordertypeName,fsto_openingtime, fsto_ismanualpacking, CASE WHEN admin_description = 'Queued for manual assignment' THEN '' WHEN so.fsto_ismanualpacking = 1 AND so.fsto_manualpackinguserid > 0 THEN (SELECT CONCAT(FirstName, ' ', LastName) FROM finascop_usr_profile WHERE UserId = so.fsto_manualpackinguserid) WHEN so.fsto_ismanualpacking = 1 AND so.fsto_manualpackinguserid = 0 THEN 'Unknown' WHEN so.fsto_ismanualpacking = 0 AND so.fsto_assigned_boy > 0 THEN (SELECT NAME FROM retaline_godown_boy WHERE id = so.fsto_assigned_boy)ELSE 'Unknown' END AS packingUserId,CASE WHEN admin_description = 'Queued for manual assignment' THEN '' WHEN fsto_ismanualpacking = 0 THEN 'Packsure App' WHEN fsto_ismanualpacking = 1 THEN 'Manual Packing' END AS packingType,fsto_uid,br_Name AS sourcename,fsto_destination,CASE WHEN fsto_ordertype = 0 THEN(SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) WHEN fsto_ordertype = 1 THEN(SELECT cust_customer_name FROM retaline_customer WHERE cust_id = fsto_destination) WHEN fsto_ordertype = 2 THEN(SELECT b2b_Customer_Name FROM retaline_B2Bcustomer WHERE  b2b_Customer_ID = fsto_destination) WHEN fsto_ordertype = 3 THEN(SELECT br_Name FROM finascop_branch WHERE br_ID = fsto_destination) END AS customer FROM retaline_customer_order bco INNER JOIN retaline_customer_order_status bcos ON bcos.status_id = bco.status_id INNER JOIN finascop_branch ON br_ID = order_branch_id LEFT JOIN finascop_stock_transfer_order so ON bco.order_id = so.fstr_id WHERE bco.status_id > 0 AND order_id = '{transferOrderId}'";
                var tblItems = DataServiceMySql.GetDataTable(sql, UserService.GetAPIConnectionString());

                if (tblItems == null || tblItems.Rows.Count <= 0)
                {
                    pnlInvalidOrder.Visible = true;
                    pnlValidOrder.Visible = plcActionButtonsRow.Visible = false;
                    return;
                }
                var trTransferOrder = tblItems.Rows[0];
                string strLtrOrdNo = "", strltrOrdDate = "", strltrSchOpnTime = "", orderSlotId = "";
                string fsid = trTransferOrder["fstr_id"].ToString();
                if (sql != null)
                {
                    string sqlorder = $"SELECT order_order_id, order_confirm_date, order_slot_id, order_confirmed_on,DATE_FORMAT(order_slot_date,'%d-%m-%Y') AS slotDate," +
                        $"(SELECT CONCAT(DATE_FORMAT(rbds_time_from, '%H:%i'), '-', DATE_FORMAT(rbds_time_to, '%H:%i')) " +
                        $"FROM retaline_branch_delivery_slot WHERE rbds_id = order_slot_id) AS slotTime, order_delivery_start_at, " +
                        $"storegroup_id FROM retaline_customer_order WHERE order_id = {fsid}";
                    var tblorder = DataServiceMySql.GetDataTable(sqlorder, UserService.GetAPIConnectionString());

                    if (tblorder != null && tblorder.Rows.Count > 0)
                    {
                        var ta = tblorder.Rows[0];
                        strLtrOrdNo = ta["order_order_id"].ToString();
                        strltrOrdDate = ta["order_confirmed_on"].ToString();
                        strltrSchOpnTime = ta["slotDate"].ToString() + ' ' + ta["slotTime"].ToString();
                        orderSlotId = ta["order_slot_id"].ToString();

                        try { orderStoreGroupId = Convert.ToInt32(ta["storegroup_id"]); } catch { }
                    }
                }
                int orderPlacedStoreGroupId = 0, ordSlotId = 0;
                if (orderSlotId == "")
                {
                    ordSlotId = 0;
                }
                else
                {
                    ordSlotId = Convert.ToInt32(orderSlotId);
                }
                try { orderPlacedStoreGroupId = Convert.ToInt32(trTransferOrder["br_storeGroup"]); } catch { }

                string packingUserId = trTransferOrder["packingUserId"].ToString();
                string packingType = trTransferOrder["packingType"].ToString();
                // Check if either value is empty or NULL
                if (!string.IsNullOrEmpty(packingUserId) && !string.IsNullOrEmpty(packingType))
                {
                    ltrPackedBy.Text = packingUserId + " / " + packingType;
                }
                else if (!string.IsNullOrEmpty(packingUserId))
                {
                    ltrPackedBy.Text = packingUserId;
                }
                else if (!string.IsNullOrEmpty(packingType))
                {
                    ltrPackedBy.Text = packingType;
                }
                else
                {
                    ltrPackedBy.Text = "Not Yet Packed"; // Default to empty if both are missing
                }
                ltrToNo.Text = trTransferOrder["fsto_uid"].ToString();
                ltrConsigne.Text = trTransferOrder["customer"].ToString();

                string orddetails = $"SELECT co.order_id,co.order_method,co.order_order_id, co.order_group_id,co.order_notes,co.total,co.order_cess,co.status_id, cod.admin_description, co.order_payment_status, so.fsto_id, so.fsto_uid,co.status_id AS StatusId,quor_PickupLat,quor_PickupLng,quor_id,quor_Deliverybr_id,quor_Type,quor_Status,drivetype, " +
                    $"CASE WHEN payment_mode = 1 THEN 'Pay On Delivery' " +
                    $"WHEN payment_mode = 2 THEN 'Online Payment' " +
                    $"WHEN payment_mode = 3 THEN 'Wallet' " +
                    $"WHEN payment_mode = 4 THEN 'COD With Wallet' " +
                    $"WHEN payment_mode = 5 THEN 'Online With Wallet' " +
                    $"WHEN payment_mode = 6 THEN 'Online On Delivery' " +
                    $"WHEN payment_mode = 7 THEN 'Cash On Delivery' END AS payment_mode," +
                    $"CASE WHEN co.payment_mode = 1 THEN 'Yet to be collected' " +
                    $"WHEN co.payment_mode = 2 THEN(COALESCE(order_payment_status, 'Payment Failed', co.order_payment_status)) " +
                    $"WHEN co.payment_mode = 3 THEN 'Success' WHEN co.payment_mode = 4 THEN 'COD with Wallet' " +
                    $"WHEN co.payment_mode = 5 THEN(COALESCE(order_payment_status, 'Payment Failed', co.order_payment_status)) " +
                    $"WHEN co.payment_mode = 6 THEN 'Online on Delivery' WHEN co.payment_mode = 7 THEN 'Yet to be collected' ELSE '' END AS payStatus, co.order_confirm_date,co.order_confirmed_on,od.order_customer_name, " +
                    $"co.subtotal, co.order_total_amount, co.order_delivery_charge, co.order_total_gst, co.order_delivery_charge_et, cod.customer_description, co.order_packedbags_count,(co.order_total_gst + co.order_delivery_charge_gst) AS totalGst, co.order_delivery_charge_gst, co.order_discount_add_total, co.order_roundoff, " +
                    $"order_contact_no, CONCAT(od.order_house_no, ' ', od.order_house_name, ' ', od.order_address, ' ', od.order_land_mark, ' ', od.order_city, ' ', od.order_state, ' ', od.order_country, ' ', od.order_post) AS address " +
                    $"FROM retaline_customer_order co INNER JOIN retaline_customer_order_delivery_address od ON od.order_id = co.order_order_id " +
                    $"LEFT JOIN finascop_stock_transfer_order so ON so.fstr_id = co.order_id " +
                    $"LEFT JOIN retaline_customer_order_status cod ON cod.status_id = co.status_id " +
                    $"LEFT JOIN (SELECT quor_RefNo,quor_PickupLat,quor_PickupLng,quor_id,quor_Deliverybr_id,quor_Type,quor_Status,IF(quor_Status = 22, 'PICKUP', IF(quor_Status = 31, 'DELIVERY', '')) AS drivetype FROM qugeo_order GROUP BY quor_RefNo) qo ON qo.quor_RefNo = co.order_order_id WHERE co.order_id = '{transferOrderId}'";
                var tblItems2 = DataServiceMySql.GetDataTable(orddetails, UserService.GetAPIConnectionString());
                string transferOrdId = "";
                if (tblItems2 != null && tblItems2.Rows.Count > 0)
                {
                    var trOrder = tblItems2.Rows[0];
                    ltrTitleOrderId.Text = trOrder["order_order_id"].ToString();
                    ltrTitleTotal.Text = trOrder["total"].ToString();
                    ltrPayMode.Text = trOrder["payment_mode"].ToString();
                    ltrPayStatus.Text = trOrder["payStatus"].ToString();
                    ltrTitleStatus.Text = trOrder["admin_description"].ToString();
                    ltrOrder.Text = trOrder["order_order_id"].ToString();
                    ltrOrdId.Text = trOrder["order_group_id"].ToString();
                    ltrCustName.Text = trOrder["order_customer_name"].ToString();
                    ltrOrdDte.Text = trOrder["order_confirmed_on"].ToString();
                    ltrOrdAmt.Text = trOrder["total"].ToString();
                    ltrCess.Text = trOrder["order_cess"].ToString();
                    ltrCntNo.Text = trOrder["order_contact_no"].ToString();
                    string orderMethod = trOrder["order_method"].ToString();
                    ltrSubTotal.Text = trOrder["order_total_amount"].ToString();
                    ltrDeliveryCharge.Text = trOrder["order_delivery_charge_et"].ToString();
                    ltrGST.Text = trOrder["totalGst"].ToString();
                    ltrRoundOff.Text = trOrder["order_roundoff"].ToString();
                    ltrNoPacket.Text = trOrder["order_packedbags_count"].ToString() + " " + "Packets";                    

                    if (SourcePage == "Packing")
                    {
                        if ((new int[] { 4, 6, 7 }).Contains(Convert.ToInt32(trOrder["StatusId"])) && (new int[] { 4, 5, 6, 7, 23 }).Contains(Convert.ToInt32(trOrder["StatusId"])))
                        {
                            hlAssignOrderPicker.Visible = true;
                            hlAssignOrderPicker.NavigateUrl = string.Format("~/Tenant/AssignOrderPicker.aspx?orderid={0}&toid={1}&ordId={2}", trOrder["fsto_id"], trOrder["fsto_uid"], trOrder["order_id"]);
                            hlManualPacking.Visible = true;
                            hlManualPacking.NavigateUrl = string.Format("~/Tenant/ManualPacking.aspx?fsto_id={0}&orderId={1}", trOrder["fsto_id"], trOrder["order_order_id"]);
                        }

                        else if (ltrTitleStatus.Text == "Queued for manual assignment")
                        {
                            hlAssignOrderPicker.Visible = true;
                        }
                        dvNoneSponsoredPack.Visible = (orderPlacedStoreGroupId == orderStoreGroupId);
                        dvSponsored.Visible = !dvNoneSponsoredPack.Visible;
                        dvNoneSponsoredPack.Visible = true;
                        txtCustomerNotes.Text = trOrder["order_notes"].ToString();
                        txtCustomerNotes.Enabled = false;
                        dvNoneSponsoredDelivery.Visible = (orderPlacedStoreGroupId == orderStoreGroupId);
                        dvSponsored.Visible = !dvNoneSponsoredDelivery.Visible;
                    }

                    if (SourcePage == "Delivery")
                    {
                        if (((new int[] { 9, 12, 13, 22, 27, 28, 29, 30, 31, 32, 33, 34 }).Contains(Convert.ToInt32(trOrder["StatusId"]))) && ordSlotId < 0)
                        {
                            hlManualDelivery.Visible = true;
                            hlManualDelivery.NavigateUrl = string.Format("~/Tenant/ManualDelivery.aspx?fsto_id={0}", trOrder["fsto_id"]);

                        }

                        if (((new int[] { 9, 12, 13, 22, 27, 28, 29, 30, 31, 32, 33, 34 }).Contains(Convert.ToInt32(trOrder["StatusId"])))
                            && (orderMethod != "3") && ordSlotId < 0)
                        {
                            hlActiveDeliveryBoys.Visible = true;
                            hlActiveDeliveryBoys.NavigateUrl = string.Format("/Tenant/LiveVehicles.aspx?orderid={0}&lat={1}&long={2}&brId={3}&status={4}&quorId={5}", trOrder["order_order_id"], trOrder["quor_PickupLat"], trOrder["quor_PickupLng"], trOrder["quor_Deliverybr_id"], trOrder["drivetype"], trOrder["quor_id"]);
                        }
                    }
                    //}
                    transferOrdId = trOrder["fsto_id"].ToString();
                }

                var customerAddress = DataServiceMySql.GetDataTable($"SELECT CONCAT_WS(', ',NULLIF(CONCAT_WS(' ', order_house_no, order_house_name), ''),NULLIF(order_address, ''),NULLIF(order_land_mark, '')) AS address_part1,CONCAT_WS(', ',NULLIF(order_city, ''),NULLIF(order_state, ''),NULLIF(order_pin, ''),NULLIF(order_country, '')) AS address_part2, order_contact_no,order_customer_email FROM retaline_customer_order_delivery_address oda WHERE oda.customer_order_id = {fsid}", UserService.GetAPIConnectionString());

                if (customerAddress != null && customerAddress.Rows.Count > 0)
                {
                    var custOrder = customerAddress.Rows[0];
                    ltrAdd1.Text = custOrder["address_part1"].ToString();
                    ltrAdd2.Text = custOrder["address_part2"].ToString();
                    ltrDelivNumber.Text = custOrder["order_contact_no"].ToString();
                    ltrCntEmail.Text = custOrder["order_customer_email"].ToString();
                }

                int transOrderId = Convert.ToInt32(transferOrdId);              
                var id = DataServiceMySql.GetDataTable($"SELECT quor_id,quor_RefNo,quor_Pickupbr_id FROM qugeo_order qo INNER JOIN finascop_stock_transfer_order sto " +
                    $"ON fsto_id = quor_TransferOrder_id WHERE sto.fstr_id = {transferOrderId}", UserService.GetAPIConnectionString());
                string quor_id = null;
                string quor_RefNo = null;
                foreach (DataRow da in id.Rows)
                {
                    quor_id = da["quor_id"].ToString();
                    quor_RefNo = da["quor_RefNo"].ToString();

                    string qugeoOrderId = quor_id;
                    string qugeoRefNo = quor_RefNo;
                    if (qugeoRefNo == strLtrOrdNo)
                    {
                        string deliveryDetails = $"SELECT quor_RefNo AS booking_no,quor_DeliveryDriverId,(select d_Name from qugeo_driver where d_ID=quor_DeliveryDriverId) as driverName,quor_PickedupTime,DATE_FORMAT(quor_Date,'%d-%m-%Y') AS booked_at,quor_PickupPhone,quor_PickupName, quor_UpdateOn," +
                        $"quor_DeliveryName,quor_DeliveryPhone,quor_Deliverybr_id,quor_PickupLocation AS source,quor_DeliveryLocation AS destination," +
                        $"(SELECT dls_DelStatus FROM qugeo_deliverystatus WHERE dls_ID = quor_Status) AS djStatus, quor_Status,CASE WHEN quor_Type = 1 THEN 'Drive' " +
                        $"WHEN quor_Type = 2 THEN 'Hired' WHEN quor_Type = 3 THEN 'Customer Pickup' WHEN quor_Type = 4 THEN 'Courier' WHEN quor_Type=5 THEN 'Driver Pickup' WHEN quor_Type=6 THEN 'Manual Delivery' END AS quor_TypeName,quor_Type," +
                        $"IF(quor_DeliveredTime IS NULL, DATE_FORMAT(quor_DeliveredTime, '%d-%m-%Y %H:%i:%s'), DATE_FORMAT(quor_DeliveryConfTime, '%d-%m-%Y %H:%i:%s')) AS deliveredTime, quor_DeliveredTime," +
                        $"DATE_FORMAT(quor_ScheduleOpeningTime, '%d-%m-%Y %H:%i:%s') AS quor_ScheduleOpeningTime, quor_TransferOrder_id, quor_PacketCount FROM qugeo_order WHERE quor_id = '{qugeoOrderId}'";
                        var tblItems3 = DataServiceMySql.GetDataTable(deliveryDetails, UserService.GetAPIConnectionString());
                        if (tblItems3 != null && tblItems3.Rows.Count > 0)
                        {
                            var tz = tblItems3.Rows[0];
                            if (tz["quor_TypeName"].ToString() != "")
                            {
                                ltrType.Text = tz["quor_TypeName"].ToString();
                            }
                            else
                            {
                                ltrType.Text = "Not Updated";
                                ltrCourierParCom.Text = "Not Updated";
                                ltrConsNum.Text = "Not Updated";
                            }

                            string trackingDetails = $"SELECT order_id, order_order_id, order_trackID, order_trackURL FROM retaline_customer_order WHERE order_id= '{transferOrderId}'";
                            var tblItems4 = DataServiceMySql.GetDataTable(trackingDetails, UserService.GetAPIConnectionString());
                            if (tblItems4 != null && tblItems4.Rows.Count > 0)
                            {
                                var tr = tblItems4.Rows[0];
                                string trackingurl = tr["order_trackURL"].ToString();

                                if (!string.IsNullOrEmpty(trackingurl))
                                {
                                    hlTrackingUrl.NavigateUrl = trackingurl;
                                    hlTrackingUrl.Visible = true;
                                }
                                else
                                {
                                    hlTrackingUrl.Visible = false;
                                }

                                ltrManifestUpte.Text = $"{tz["djStatus"]} - ";
                            }
                            else
                            {
                                hlTrackingUrl.Visible = false;
                                ltrManifestUpte.Text = "No tracking information available";
                            }
                            ltrManifestUpteTime.Text = tz["quor_UpdateOn"].ToString();
                            string deliveryDate = tz["quor_DeliveredTime"].ToString();
                            string driverName = tz["driverName"].ToString();
                            string typeName = tz["quor_TypeName"].ToString();
                            if (tz["deliveredTime"].ToString() == "")
                            {
                                ltrDevDate.Text = "Not Yet Delivered";
                            }
                            else
                            {
                                ltrDevDate.Text = tz["deliveredTime"].ToString();
                            }

                            if (tz["quor_PickedupTime"].ToString() == "")
                            {
                                ltrPackedUpdte.Text = "Not Updated";
                            }
                            else
                            {
                                ltrPackedUpdte.Text = tz["quor_PickedupTime"].ToString();
                            }
                            if (typeName == "Courier")
                            {
                                var courierPartnerTbl = DataServiceMySql.GetDataTable($"SELECT mst_courier_id,mst_courier_name FROM mst_courier", UserService.GetAPIConnectionString());
                                if (courierPartnerTbl != null && courierPartnerTbl.Rows.Count > 0)
                                {
                                    var tr = courierPartnerTbl.Rows[0];
                                    ltrCourierParCom.Text = tr["mst_courier_name"].ToString();
                                }
                                else
                                {
                                    ltrCourierParCom.Text = "Courier / Parcel Company is not available";
                                }

                                var courierTbl = DataServiceMySql.GetDataTable($"SELECT qoc_id,qoc_courier,qoc_qcn,qoc_trackingUrl FROM qugeo_order_courier WHERE quor_id = {qugeoOrderId}", UserService.GetAPIConnectionString());
                                if (courierTbl != null && courierTbl.Rows.Count > 0)
                                {
                                    var ty = courierTbl.Rows[0];
                                    ltrConsNum.Text = ty["qoc_qcn"].ToString();
                                }
                            }
                            else if (typeName != "")
                            {
                                ltrCourierParCom.Text = "This order deliver is through drive";
                                ltrConsNum.Text = "This order deliver is through drive";
                            }
                            if (!string.IsNullOrEmpty(deliveryDate) && !string.IsNullOrEmpty(driverName))
                            {
                                ltrDelivConDate.Text = tz["quor_DeliveredTime"].ToString();
                                ltrDelivConBy.Text = tz["driverName"].ToString();
                            }
                            else
                            {
                                ltrDelivConDate.Text = "Not Yet Delivered";
                                ltrDelivConBy.Text = "Not Yet Delivered";
                            }
                            ltrExpectDeliv.Text = "Not Updated";
                        }
                    }
                }
            }
        }
        protected void SDSItemDetails_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            string transferOrderId = Request.QueryString["ordId"];
            e.Command.Parameters["orderId"].Value = transferOrderId;
        }
        protected void SDSTaxDetails_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            string transferOrderId = Request.QueryString["ordId"];
            e.Command.Parameters["custOrderId"].Value = transferOrderId;
        }

        protected string GetBackLink()
        {
            switch (SourcePage?.Trim())
            {
                case "Packing":
                    return "/Tenant/PendingOrders";

                case "Delivery":
                    return "/Tenant/MerchantDelivery";

                case "Orders":
                default:
                    return "/Tenant/PendingOrders";
            }
        }
    }
}