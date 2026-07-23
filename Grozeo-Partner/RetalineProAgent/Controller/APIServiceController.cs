using System;
using System.Collections;
using System.Collections.Generic;
using System.ComponentModel;
using System.Configuration;
using System.Data;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Web;
using System.Web.Http;
using Amazon.DynamoDBv2.DocumentModel;
using Antlr.Runtime;
using Finascop.Services;
using Newtonsoft.Json;
using NPOI.OpenXmlFormats.Wordprocessing;
using NPOI.POIFS.Properties;
using Org.BouncyCastle.Asn1.X509.Qualified;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Finance;
using RetalineProAgent.Service;
using StackExchange.Redis;


namespace RetalineProAgent.Controller
{
    [FilterIP(
         ConfigurationKeyAllowedSingleIPs = "AllowedAdminSingleIPs"
    )]
    public class APIServiceController : ApiController
    {
        /// <summary>
        /// Send welcome email to the public customer sign up
        /// </summary>
        /// <param name="user">User (fullname, email, storename)</param>
        /// <returns></returns>
        [HttpPost]
        public IHttpActionResult SendWelcomeCustomerEmail([FromBody] object user)
        {

            var dynamicObject = JsonConvert.DeserializeAnonymousType(JsonConvert.SerializeObject(user), new { fullname = string.Empty, email = string.Empty, storename= string.Empty, storegroupid=0 });
            if (dynamicObject == null || String.IsNullOrEmpty(dynamicObject.email) || String.IsNullOrEmpty(dynamicObject.fullname))
            {
                return Json(new { result = 0, status = "Error", message = "Invalid or missing data provided" });
            }

            // Send email
            try
            {
                List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
                replacements.Add(new KeyValuePair<string, string>("[URLPART]", ConfigurationManager.AppSettings["partner.url"]));
                replacements.Add(new KeyValuePair<string, string>("[STORENAME]", dynamicObject.storename));
                replacements.Add(new KeyValuePair<string, string>("[FULLNAME]", dynamicObject.fullname));
                string strBody = Service.EmailService.CreateEmailbody(Service.EmailType.WelcomeNewCustomer, replacements);
                // Send invitation email.
                Core.Services.APIService.SendEmail(dynamicObject.email, "Welcome to Grozeo Store", strBody, dynamicObject.fullname, true);

            }
            catch (Exception ex)
            {
                return Json(new { result = 0, status = "Error", message = "Error occurred: " + ex.Message });
            }

            return Json(new { result = 1, status = "Success", message = "Invitation send successfully!" });

        }

        /// <summary>
        /// Send email notification on order completion
        /// </summary>
        /// <param name="user">User (fullname, email, storename, sku, ordernum, orderdate, orderquantity, deliverydate, total)</param>
        /// <returns></returns>
        [HttpPost]
        public IHttpActionResult OrderCompletionSendEmail([FromBody] object user)
        {

            var dynamicObject = JsonConvert.DeserializeAnonymousType(JsonConvert.SerializeObject(user), new { fullname = string.Empty, email = string.Empty, sku=string.Empty, 
                storename = string.Empty, ordernum = string.Empty, orderdate=string.Empty, orderquantity=0, deliverydate=string.Empty, total=double.MinValue });
            if (dynamicObject == null || String.IsNullOrEmpty(dynamicObject.email) || String.IsNullOrEmpty(dynamicObject.fullname))
            {
                return Json(new { result = 0, status = "Error", message = "Invalid or missing data provided" });
            }

            // Send email
            try
            {
                
                List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
                replacements.Add(new KeyValuePair<string, string>("[URLPART]", ConfigurationManager.AppSettings["partner.url"]));
                replacements.Add(new KeyValuePair<string, string>("[STORENAME]", dynamicObject.storename));
                replacements.Add(new KeyValuePair<string, string>("[FULLNAME]", dynamicObject.fullname));
                replacements.Add(new KeyValuePair<string, string>("[ORDERNUM]", dynamicObject.ordernum));
                replacements.Add(new KeyValuePair<string, string>("[ORDERDATE]", dynamicObject.orderdate));
                replacements.Add(new KeyValuePair<string, string>("[ORDERQTY]", dynamicObject.orderquantity.ToString()));
                replacements.Add(new KeyValuePair<string, string>("[ORDERTOTAL]", dynamicObject.total.ToString()));
                replacements.Add(new KeyValuePair<string, string>("[ORDERDELIVERYDATE]", dynamicObject.deliverydate));
                replacements.Add(new KeyValuePair<string, string>("[CURRENCYSYMBOL]", ConfigurationManager.AppSettings["CurrencySymbol"]));

                string strBody = Service.EmailService.CreateEmailbody(Service.EmailType.OrderConfirmation, replacements);
                // Send invitation email.
                Core.Services.APIService.SendEmail(dynamicObject.email, String.Format("{0} - Order placed successfully - {1}", dynamicObject.storename, dynamicObject.sku), strBody, dynamicObject.fullname, true);

            }
            catch (Exception ex)
            {
                return Json(new { result = 0, status = "Error", message = "Error occurred: " + ex.Message });
            }

            return Json(new { result = 1, status = "Success", message = "Notification send successfully!" });

        }
        /// <summary>
        /// Send email notification on delivery completion
        /// </summary>
        /// <param name="user">User (fullname, email, storename, ordernum)</param>
        /// <returns></returns>
        [HttpPost]
        public IHttpActionResult DeliveryCompletionSendEmail([FromBody] object user)
        {

            var dynamicObject = JsonConvert.DeserializeAnonymousType(JsonConvert.SerializeObject(user), new { fullname = string.Empty, email = string.Empty, 
                storename = string.Empty, storegroupid = 0, ordernum = string.Empty
            });
            if (dynamicObject == null || String.IsNullOrEmpty(dynamicObject.email) || String.IsNullOrEmpty(dynamicObject.fullname))
            {
                return Json(new { result = 0, status = "Error", message = "Invalid or missing data provided" });
            }

            // Send email
            try
            {
                
                List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
                replacements.Add(new KeyValuePair<string, string>("[URLPART]", ConfigurationManager.AppSettings["partner.url"]));
                replacements.Add(new KeyValuePair<string, string>("[STORENAME]", dynamicObject.storename));
                replacements.Add(new KeyValuePair<string, string>("[FULLNAME]", dynamicObject.fullname));
                replacements.Add(new KeyValuePair<string, string>("[ORDERNUM]", dynamicObject.ordernum));
                string strBody = Service.EmailService.CreateEmailbody(Service.EmailType.DeliveryConfirmation, replacements);
                // Send invitation email.
                Core.Services.APIService.SendEmail(dynamicObject.email, "Delivery Confirmation", strBody, dynamicObject.fullname, true);

            }
            catch (Exception ex)
            {
                return Json(new { result = 0, status = "Error", message = "Error occurred: " + ex.Message });
            }

            return Json(new { result = 1, status = "Success", message = "Notification send successfully!" });

        }

        /// <summary>
        /// Send email notification on Support Ticket Created
        /// </summary>
        /// <param name="customer">Customer (Customer's name, email, Ticket ID, Issue Description,Date Created)</param>
        /// customer.EmailType -  1 = Ticketcreation, 2 =TicketcreationbyCustomer ,3=SupportTicketConfirmation,4=SupportTicketResolved
        /// <returns></returns>
        [HttpPost]
        public IHttpActionResult Createsupportmail([FromBody] object customer)
        {
            var dynamicObject = JsonConvert.DeserializeAnonymousType(JsonConvert.SerializeObject(customer), new { Customersname = string.Empty, email = string.Empty,
                TicketID = string.Empty,IssueDescription = string.Empty,  createddate= string.Empty, EmailType = 0, Query= string.Empty
            });

            if (dynamicObject == null || String.IsNullOrEmpty(dynamicObject.email) || String.IsNullOrEmpty(dynamicObject.Customersname))
            {
                return Json(new { result = 0, status = "Error", message = "Invalid or missing data provided" });
            }

            // Send email
            try
            {
                 List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
                replacements.Add(new KeyValuePair<string, string>("[URLPART]", ConfigurationManager.AppSettings["partner.url"]));
                replacements.Add(new KeyValuePair<string, string>("[CUSTOMERSNAME]", dynamicObject.Customersname));
                replacements.Add(new KeyValuePair<string, string>("[TICKETID]", dynamicObject.TicketID));
                replacements.Add(new KeyValuePair<string, string>("[BRIEFDESRIPTIONOFTHEISSUE]", dynamicObject.IssueDescription));
                

                if (dynamicObject.EmailType ==1) 
                {
                    replacements.Add(new KeyValuePair<string, string>("[DATEANDTIME]", dynamicObject.createddate));
                    string strBody = Service.EmailService.CreateEmailbody(Service.EmailType.Ticketcreation, replacements);
                    // Send invitation email.
                    Core.Services.APIService.SendEmail(dynamicObject.email, "Ticket Creation", strBody, dynamicObject.Customersname, true);

                }
                if (dynamicObject.EmailType == 2)
                {
                    replacements.Add(new KeyValuePair<string, string>("[DATEANDTIME]", dynamicObject.createddate));
                    string strBody = Service.EmailService.CreateEmailbody(Service.EmailType.TicketcreationbyCustomer, replacements);
                    // Send invitation email.
                    Core.Services.APIService.SendEmail(dynamicObject.email, "Ticket Creation By Customer", strBody, dynamicObject.Customersname, true);

                }
                if(dynamicObject.EmailType == 3)
                {
                    replacements.Add(new KeyValuePair<string, string>("[DATEANDTIME]", dynamicObject.createddate));
                    string strBody = Service.EmailService.CreateEmailbody(Service.EmailType.SupportTicketConfirmation, replacements);
                    // Send invitation email.
                    Core.Services.APIService.SendEmail(dynamicObject.email, "Support Ticket Confirmation", strBody, dynamicObject.Customersname, true);

                }
                if (dynamicObject.EmailType == 4)
                {
                    replacements.Add(new KeyValuePair<string, string>("[DATEANDTIME]", dynamicObject.createddate));
                    string strBody = Service.EmailService.CreateEmailbody(Service.EmailType.SupportTicketResolved, replacements);
                    // Send invitation email.
                    Core.Services.APIService.SendEmail(dynamicObject.email, "Support Ticket Resolved", strBody, dynamicObject.Customersname, true);

                }
                if (dynamicObject.EmailType == 5)
                {                    
                    replacements.Add(new KeyValuePair<string, string>("[SPECIFYTHEQUERYORCLARIFICATION]", dynamicObject.Query));
                    string strBody = Service.EmailService.CreateEmailbody(Service.EmailType.QueryAskedDepartment, replacements);
                    // Send invitation email.
                    Core.Services.APIService.SendEmail(dynamicObject.email, "Support Ticket Resolved", strBody, dynamicObject.Customersname, true);

                }
            }
            catch (Exception ex)
            {
                return Json(new { result = 0, status = "Error", message = "Error occurred: " + ex.Message });
            }

            return Json(new { result = 1, status = "Success", message = "Notification send successfully!" });

        }
        /// <summary>
        /// Send email notification on Email Verification
        /// </summary>
        /// <param name="user">Customer ,email</param>
        /// 
        /// <returns></returns>
        [HttpPost]
        public IHttpActionResult Emailverification([FromBody] object user)
        {
            try
            {
                var dynamicObject = JsonConvert.DeserializeAnonymousType(JsonConvert.SerializeObject(user), new
                {
                    email = string.Empty,
                    Otp = string.Empty,                    
                });
                if (dynamicObject == null || String.IsNullOrEmpty(dynamicObject.email))
                {
                    return Json(new { result = 0, status = "Error", message = "Invalid or missing data provided" });
                }
                List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
                replacements.Add(new KeyValuePair<string, string>("[URLPART]", ConfigurationManager.AppSettings["partner.url"]));
                replacements.Add(new KeyValuePair<string, string>("[OTPCode]", dynamicObject.Otp));
                string strBody = Service.EmailService.CreateEmailbody(Service.EmailType.otpverification, replacements);

                Core.Services.APIService.SendEmail(dynamicObject.email, "Grozeo OTP for your Email Verification", strBody, "", true);
            }
            catch (Exception ex)
            {
                return Json(new { result = 0, status = "Error", message = "Error occurred: " + ex.Message });

            }
            return Json(new { result = 1, status = "Success", message = "Notification send successfully!" });
        }

        /// <summary>
        /// Send email notification on Shipping Confirmation
        /// </summary>
        /// <param name="customer">Customer ,order_order_id</param>
        /// 
        /// <returns></returns>
        [HttpPost]

        public IHttpActionResult ShippingConfirmation([FromBody] object orderid)
        {
            try
            {
                var dynamicObject = JsonConvert.DeserializeAnonymousType(JsonConvert.SerializeObject(orderid), new
                {
                    email = string.Empty,
                    order_order_id = string.Empty,
                    Customersname = string.Empty
                });
                if (dynamicObject == null || String.IsNullOrEmpty(dynamicObject.email) || String.IsNullOrEmpty(dynamicObject.Customersname))
                {
                    return Json(new { result = 0, status = "Error", message = "Invalid or missing data provided" });
                }
                List<KeyValuePair<string, object>> sql = new List<KeyValuePair<string, object>>();
                sql.Add(new KeyValuePair<string, object>("order_order_id", dynamicObject.order_order_id));
                string shippingconformation = "SELECT rc.order_order_id, rc.order_trackURL,rc.order_trackID,sc.pickupdate FROM `retaline_customer_order` rc INNER JOIN `qugeo_order` qo ON quor_RefNo=order_order_id AND quor_TransferOrder_Type=1 INNER JOIN `shipping_consignment` sc ON sc.order_id= rc.order_order_id where order_order_id=@order_order_id";
                var dtitems = DataServiceMySql.GetDataTable(shippingconformation, Service.UserService.GetAPIConnectionString(), sql);
                List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
                replacements.Add(new KeyValuePair<string, string>("[CUSTOMERSNAME]", dynamicObject.Customersname));
                replacements.Add(new KeyValuePair<string, string>("[ORDERNUMBER]", dynamicObject.order_order_id));
                if (dtitems != null && dtitems.Rows.Count > 0)
                {
                    DateTime qocDate = (DateTime)dtitems.Rows[0]["pickupdate"];
                    string formattedDate = qocDate.ToString("dd/MMM/yyyy");
                    replacements.Add(new KeyValuePair<string, string>("[SHIPPINGDATE]", formattedDate));
                    replacements.Add(new KeyValuePair<string, string>("[TRACKINGNUMBER]", dtitems.Rows[0]["order_trackID"].ToString()));
                    replacements.Add(new KeyValuePair<string, string>("[CARRIERTRACKINGWEGISTELINK]", dtitems.Rows[0]["order_trackURL"].ToString()));
                    string strBody = Service.EmailService.CreateEmailbody(Service.EmailType.ShippingConfirmation, replacements);
                    Core.Services.APIService.SendEmail(dynamicObject.email, "Shipping Confirmation", strBody, dynamicObject.Customersname, true);
                }
               
            }
            catch (Exception ex)
            {
                return Json(new { result = 0, status = "Error", message = "Error occurred: " + ex.Message });
            }
            return Json(new { result = 1, status = "Success", message = "Notification send successfully!" });


        }



        /// <summary>
        /// Send email notification on invoice Created
        /// </summary>
        /// <param name="customer">Customer ,orderid</param>
        /// 
        /// <returns></returns>
        [HttpPost]
        public IHttpActionResult SentInvoice([FromBody] object orderid)
        {
            try
            {
                var dynamicObject = JsonConvert.DeserializeAnonymousType(JsonConvert.SerializeObject(orderid), new
                {                    
                    email = string.Empty,
                    order_id = 0,
                    Customersname = string.Empty
                });
                if (dynamicObject == null || String.IsNullOrEmpty(dynamicObject.email)|| String.IsNullOrEmpty(dynamicObject.Customersname))
                {
                    return Json(new { result = 0, status = "Error", message = "Invalid or missing data provided" });
                }
                string strBody = Service.InvoiceService.Generateinvoicetemplate( dynamicObject.order_id);
                Core.Services.APIService.SendEmail(dynamicObject.email, "Invoice", strBody, dynamicObject.Customersname, true);

            }
            catch (Exception ex)
            {
                return Json(new { result = 0, status = "Error", message = "Error occurred: " + ex.Message });
            }

            return Json(new { result = 1, status = "Success", message = "Notification send successfully!" });
        }

        /// <summary>
        /// Get invoice
        /// </summary>
        //// <param name="orderid"></param>
        //// <returns></returns>
        [HttpPost]
        public IHttpActionResult GetInvoiceContent([FromBody] object orderid)
        {
            string invoiceContent = String.Empty;
            try
            {
                var dynamicObject = JsonConvert.DeserializeAnonymousType(JsonConvert.SerializeObject(orderid), new
                {
                    email = string.Empty,
                    order_id = 0,
                    Customersname = string.Empty,
                    TypeId = 0,
                    
                });
                if (dynamicObject == null || String.IsNullOrEmpty(dynamicObject.email) || String.IsNullOrEmpty(dynamicObject.Customersname))
                {
                    return Json(new { result = 0, status = "Error", message = "Invalid or missing data provided" });
                }              
                invoiceContent = Service.InvoiceService.Generateinvoicetemplate(dynamicObject.order_id);                
               
            }
            catch (Exception ex)
            {
                return Json(new { result = 0, status = "Error", message = "Error occurred: " + ex.Message });
            }

            return Json(new { result = 1, status = "Success", message = "", data = invoiceContent });

        }
        /// <summary>
        /// settlement amount
        /// </summary>
        /// <param name="orderid"></param>
        /// <param name="StoreRefId"></param>
        /// <returns> settlement data table</returns>
        [HttpPost]
        public IHttpActionResult GetSettlementValues([FromBody] object orders)
        {
            // Read input into list of objects.
            var dynamicObject = JsonConvert.DeserializeAnonymousType(JsonConvert.SerializeObject(orders), new List<object> {new
            {
                OrderId = string.Empty,
                BranchId = new int(),
                StoreRefId = string.Empty,
            }});

            DataTable settlement = new DataTable();

            try
            {
                // Sql input parameter (dynamic table)
                DataTable dt = new DataTable();
                dt.Columns.Add("OrderId", typeof(string));
                dt.Columns.Add("StoreRefId", typeof(string));                
                foreach(var items in dynamicObject)
                {
                    dynamic dynamicOrder = items;
                    DataRow dr = dt.NewRow();
                    dr["OrderId"] =dynamicOrder.OrderId;
                    dr["StoreRefId"] = dynamicOrder.StoreRefId;
                    dt.Rows.Add(dr);
                }
                if (dt.Rows.Count > 0)
                {
                    List<KeyValuePair<String, Object>> parmeters = new List<KeyValuePair<string, object>>();                   
                    parmeters.Add(new KeyValuePair<string, object>("orders", dt));
                    // get the settlement amount
                    settlement = DataService.GetDataTable("GetSettlementAmount", ConfigurationManager.ConnectionStrings["FinascopConnection"].ConnectionString, parmeters, isSP: true);
                }

            }
            catch (Exception ex)
            {
                return Json(new { result = 0, status = "Error", message = "Error occurred: " + ex.Message });
            }
            settlement.Columns.Add("BankId", typeof(int));
            settlement.Columns.Add("BankAccountNo", typeof(string));
            settlement.Columns.Add("IFSC", typeof(string));
            settlement.Columns.Add("BranchId", typeof(string));
            settlement.Columns.Add("AccountName", typeof(string));
            settlement.Columns.Add("StoreEmail", typeof(string));
            settlement.Columns.Add("PgType", typeof(int));
            settlement.Columns.Add("PgAccountId", typeof(string));
            settlement = ConfigurationManager.AppSettings.Get("PaymentGatewaySubAccount") == "1" ? GetBankconnectDetails(settlement, dynamicObject):GetBankDetails(settlement,dynamicObject);           
            return Json(new { result = 1, status = "Success", message = "", data = settlement });           
        }

        [HttpPost]
        public IHttpActionResult GetMerchant([FromBody] object filter)
        {

            var dynamicObject = JsonConvert.DeserializeAnonymousType(JsonConvert.SerializeObject(filter), new
            {
                email = string.Empty,
                phone = string.Empty,
                storegroupid = 0
            });
            if (dynamicObject == null || (String.IsNullOrEmpty(dynamicObject.email) && String.IsNullOrEmpty(dynamicObject.phone)))
            {
                return Json(new { result = 0, status = "Error", message = "Invalid or missing data provided" });
            }

            // Send email
            try
            {
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("mobile", dynamicObject.phone??""));
                prms.Add(new KeyValuePair<string, object>("email", dynamicObject.email??""));
                prms.Add(new KeyValuePair<string, object>("storegroup", dynamicObject.storegroupid));

                string sql = $" select a.Id, a.Theme, a.CanCheckout, a.OnlinePaymentEnabled, a.StoreId, a.Status, a.ShowPWA, a.LogoImage, a.LogoSmall, a.FavIcoImage, " +
                    $"a.OwnBannerOnly, a.Stage, a.EnableAnalytics, a.TenantType, a.PackageId, u.Email, u.Mobile, u.FullName, u.[Address], u.City, u.[State], " +
                    $"u.hasVerifiedEmail, u.hasVerifiedMobile, u.hasVerifiedVAT, " +
                    $"(SELECT STRING_AGG ( concat(replace(BankName, ', ', ','), ', ', replace(AccountNumber, ', ', ','), ', ', replace(AccountName, ', ', ','), ', ', replace(Branch, ', ', ',')), '|,|') AS csv FROM BankAccount b where TenantId=a.Id) as bankaccounts, " +
                    $"(select STRING_AGG (concat(replace(gstin, ', ', ','), ', ', replace(organization, ', ', ',')), '|,|') as gstinfo from GST where tenantid=a.Id) as gst, " +
                    $"(select STRING_AGG ( concat(replace(b.[Location], '|| ', ','), '|| ', replace(b.Addr, '|| ', ','), '|| ', replace(b.District, '|| ', ','), '|| ', " +
                        $"replace(b.[State], '|| ', ','), '|| ', replace(b.Lat, '|| ', ','), '|| ', replace(b.Lang, '|| ', ','), '|| ', replace(b.[Status], '|| ', ','), '|| ', " +
                        $"replace(ba.BankName, '|| ', ','), '|| ', replace(ba.Branch, '|| ', ','), '|| ', replace(g.gstin, '|| ', ',')), '|,|') as branches from StoreBranch b left join BankAccount ba on ba.Id=b.BankId left join GST g on b.GSTId = g.id where b.StoreId=a.Id) as branches " +
                    $" from AppTenant a inner join [User] u on a.id = u.StoreGroupId " +
                    $" where (isnull(@mobile, '') <> '' and u.Mobile like '%'+@mobile) or (isnull(@email, '') <> '' and u.Email like @email) or (isnull(@storegroup, 0) > 0 and u.StoreGroupId = @storegroup)";
                // Send invitation email.

                var tblResult = RetalineProAgent.Core.Services.DataService.GetDataTable(sql, parmeters: prms);
                if (tblResult == null || tblResult.Rows.Count < 1)
                    throw new Exception("No record found or there is a technical error happened!");

                List<Object> data= new List<Object>();

                foreach (System.Data.DataRow dr in tblResult.Rows)
                {
                    try
                    {
                        data.Add(new
                        {
                            Id = dr["Id"].ToString(),
                            Theme = dr["Theme"].ToString(),
                            CanCheckout = dr["CanCheckout"].ToString(),
                            OnlinePaymentEnabled = dr["OnlinePaymentEnabled"].ToString(),
                            StoreGroupId = dr["StoreId"].ToString(),
                            Status = dr["Status"].ToString(),
                            ShowPWA = dr["ShowPWA"].ToString(),
                            LogoImage = dr["LogoImage"].ToString(),
                            LogoSmall = dr["LogoSmall"].ToString(),
                            FavIcon = dr["FavIcoImage"].ToString(),
                            OwnBannerOnly = dr["OwnBannerOnly"].ToString(),
                            Stage = dr["Stage"].ToString(),
                            EnableAnalytics = dr["EnableAnalytics"].ToString(),
                            TenantType = dr["TenantType"].ToString(),
                            PackageId = dr["PackageId"].ToString(),
                            Email = dr["Email"].ToString(),
                            Mobile = dr["Mobile"].ToString(),
                            FullName = dr["FullName"].ToString(),
                            Address = dr["Address"].ToString(),
                            City = dr["City"].ToString(),
                            State = dr["State"].ToString(),
                            EmailVerified = dr["hasVerifiedEmail"].ToString(),
                            MobileVerified = dr["hasVerifiedMobile"].ToString(),
                            VATVerified = dr["hasVerifiedVAT"].ToString(),
                            Bankaccounts = dr["bankaccounts"].ToString().Split(new string[] { "|,|" }, StringSplitOptions.RemoveEmptyEntries).Select(b=> new { Bank= b.Split(new string[] { ", " }, StringSplitOptions.None)[0], AccountNumber= b.Split(new string[] { ", " }, StringSplitOptions.None)[1], AccountName = b.Split(new string[] { ", " }, StringSplitOptions.None)[2], Branch= b.Split(new string[] { ", " }, StringSplitOptions.None)[3] }).ToList(),
                            GST = dr["gst"].ToString().Split(new string[] { "|,|" }, StringSplitOptions.RemoveEmptyEntries).Select(g=> new {
                                Gstin= g.Split(new string[] { ", " }, StringSplitOptions.None)[0], Organization= g.Split(new string[] { ", " }, 
                                StringSplitOptions.None)[1] }).ToList(), 
                            Branches = dr["branches"].ToString().Split(new string[] { "|,|" }, StringSplitOptions.RemoveEmptyEntries).Select(b=> new {
                                Location= b.Split(new string[] { "|| " }, StringSplitOptions.None)[0], Address= b.Split(new string[] { "|| " }, StringSplitOptions.None)[1], 
                                District= b.Split(new string[] { "|| " }, StringSplitOptions.None)[2], State= b.Split(new string[] { "|| " }, StringSplitOptions.None)[3], 
                                    Lat= b.Split(new string[] { "|| " }, StringSplitOptions.None)[4], Lng= b.Split(new string[] { "|| " }, StringSplitOptions.None)[5], 
                                    Status= b.Split(new string[] { "|| " }, StringSplitOptions.None)[6], Bank= b.Split(new string[] { "|| " }, 
                                    StringSplitOptions.None)[7], Branch= b.Split(new string[] { "|| " }, StringSplitOptions.None)[8], 
                                    GSTIN= b.Split(new string[] { "|| " }, StringSplitOptions.None)[9] }).ToList()
                        });
                    }
                    catch(Exception ex1) 
                    { 
                    
                    }
                }

                return Json(new { result = 1, status = "Success", data = data });
            }
            catch (Exception ex)
            {
                return Json(new { result = 0, status = "Error", message = "Error occurred: " + ex.Message });
            }

            return Json(new { result = 0, status = "Failure", message = "There is a technical error happened!" });

        }

        [HttpPost]
        public IHttpActionResult GetMerchantsWithPendingAction(int pendingOnly, int streogroupId = 0)
        {
            try
            {
                var combinedData = Services.StoreService.MerchantsWithPendingActions(pendingOnly, streogroupId);
                return Json(new { result = 1, status = "Success", data = combinedData });

            }
            catch (Exception ex)
            {
                return Json(new { result = 0, status = "Error", message = ex.Message });
            }
        }

        private static DataTable GetBankDetails(DataTable settlement,List<dynamic> dynamicObject)
        {
            if (dynamicObject == null || dynamicObject.Count == 0)
                return null;
            try
            {
                // Step 1: Prepare DataTable of BranchIds
                DataTable branchTable = new DataTable();
                branchTable.Columns.Add("BranchId", typeof(int));
                foreach (var item in dynamicObject)
                {
                    DataRow row = branchTable.NewRow();
                    row["BranchId"] = item.BranchId;
                    branchTable.Rows.Add(row);
                }
                if (branchTable.Rows.Count == 0)
                    return null;
                // Step 2: Get branch bank account info
                var prms = new List<KeyValuePair<string, object>>
                {
                    new KeyValuePair<string, object>("branches", branchTable)
                };
                DataTable bankAccounts = DataService.GetDataTable("GetBranchBankAccounts", parmeters: prms, isSP: true);
                if (bankAccounts == null || bankAccounts.Rows.Count == 0)
                    return null;
                // Step 3: Update settlement with matching bank info
                foreach (DataRow row in settlement.Rows)
                {

                    dynamic obj = dynamicObject.FirstOrDefault(o => o.OrderId == Convert.ToInt64(row["entity_id"]));
                    if (obj == null)
                        continue;
                    DataRow[] matched = bankAccounts.Select($"BranchId = '{obj.BranchId}'");
                    if (matched.Length == 0)
                        continue;

                    DataRow bankData = matched[0];
                    row["BankId"] = bankData["BankId"];
                    row["BankAccountNo"] = bankData["BankAccountNo"];
                    row["IFSC"] = bankData["IFSC"];
                    row["BranchId"] = bankData["BranchId"];
                    row["AccountName"] = bankData["AccountName"];
                    row["StoreEmail"] = bankData["StoreEmail"];
                }
            }
            catch
            {
                return null;
            }

            return settlement;
        }
        private static DataTable GetBankconnectDetails(DataTable settlement, List<dynamic> dynamicObject)
        {
            if (dynamicObject == null || dynamicObject.Count == 0)
                return settlement;

            try
            {
                var bankConnectResults = new List<DataRow>();
                string branchIds = string.Join(",", dynamicObject.Select(o => ((dynamic)o).BranchId));
                var prms = new List<KeyValuePair<string, object>>
                {
                        new KeyValuePair<string, object>("BranchId", branchIds)
                };
                string getdata = "SELECT bc.*,b.br_ID, b.br_storegroup, (SELECT accountId FROM store_paymentgateway_connect WHERE storeGroupId =b.br_storegroup ORDER BY id LIMIT 1) AS defaultAccountId,(SELECT pgType FROM store_paymentgateway_connect WHERE storeGroupId =b.br_storegroup ORDER BY id LIMIT 1) AS Defaultpgtype FROM finascop_branch b LEFT JOIN store_paymentgateway_connect bc ON bc.branchId=b.br_ID WHERE b.br_ID IN (@BranchId)";
                DataTable storeconnect= DataServiceMySql.GetDataTable(getdata, Service.UserService.GetAPIConnectionString(), prms);

                // Now map results back to settlement
                foreach (DataRow row in settlement.Rows)
                {
                    dynamic obj = dynamicObject.FirstOrDefault(o => o.OrderId == Convert.ToInt64(row["entity_id"]));
                    if (obj == null)
                        continue;
                    var connectaccount = storeconnect.Select($"br_ID = '{obj.BranchId}'");
                    if (connectaccount == null || connectaccount.Length == 0)
                        continue;
                    DataRow connectaccountData = connectaccount[0];
                    row["PgType"] = connectaccountData.IsNull("PgType") ? connectaccountData["Defaultpgtype"] : connectaccountData["PgType"];
                    row["PgAccountId"] = connectaccountData.IsNull("accountId") ? connectaccountData["defaultAccountId"] : connectaccountData["accountId"];
                    row["BranchId"] = connectaccountData["br_ID"];
                }

                return settlement;
            }
            catch (Exception ex)
            {
                return null; 
            }
        }
    }

}