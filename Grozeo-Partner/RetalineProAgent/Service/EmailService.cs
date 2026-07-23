
using System.Collections.Generic;
using System.Configuration;
using System.Web.Hosting;


namespace RetalineProAgent.Service
{
    public enum EmailType
    {
        VerifyEmail = 1,
        GSTVerification = 2,
        DeliveryConfirmation = 3,
        OrderConfirmation = 4,
        PaymentSettlementConfirmation = 5,
        RequestProductReview = 6,
        ShippingConfirmation = 7,
        ShoppingCardReminder = 8,
        StoreCreated = 9,
        WelcomeNewCustomer = 10,
        WalletDebitNotification = 11,
        WalletCreditNotification = 12,
        WalletBalanceNotification = 13,
        ChangestoYourAccount = 14,
        NewStoreUserCreated = 15,
        ProspectInvite = 16,
        BusinessAssociatewelcome = 17,
        ResetPassword = 18,
        Ticketcreation = 19,
        TicketcreationbyCustomer = 20,
        SupportTicketConfirmation = 21,
        SupportTicketResolved = 22,
        QueryAskedDepartment = 23,
        invoice = 24,
        invoiceresturant=30,
        itemrepeat = 25,
        orderslip=26,
        Productdetalis=27,
        PackingSlip=28,
        otpverification=29,
        invoiceservice=31,
        itemdeliverycharge=32,

    }
  
    public class EmailService
    {
        public static string GetTemplateFile(EmailType emailType)
        {
            string filename = "";

            switch (emailType)
            {
                case EmailType.VerifyEmail:
                    filename = "email_verification.html";
                    break;
                case EmailType.GSTVerification:
                    filename = "Gst_otp_Notification.html";
                    break;
                case EmailType.DeliveryConfirmation:
                    filename = "delivery_confirmation.html";
                    break;
                case EmailType.OrderConfirmation:
                    filename = "order_confirmation.html";
                    break;
                case EmailType.PaymentSettlementConfirmation:
                    filename = "payment_settlement_confirmation.html";
                    break;
                case EmailType.RequestProductReview:
                    filename = "req_product_review.html";
                    break;
                case EmailType.ShippingConfirmation:
                    filename = "shipping_confirmation.html";
                    break;
                case EmailType.ShoppingCardReminder:
                    filename = "shopping_cart_reminder.html";
                    break;
                case EmailType.StoreCreated:
                    filename = "store_created.html";
                    break;
                case EmailType.WelcomeNewCustomer:
                    filename = "welcome_new_customer.html";
                    break;
                case EmailType.WalletBalanceNotification:
                    filename = "wallet_balance_notification.html";
                    break;
                case EmailType.WalletCreditNotification:
                    filename = "wallet_credit_notification.html";
                    break;
                case EmailType.WalletDebitNotification:
                    filename = "wallet_debit_notification.html";
                    break;
                case EmailType.ChangestoYourAccount:
                    filename = "changes_to_your_account.html";
                    break;
                case EmailType.NewStoreUserCreated:
                    filename = "store_user_created.html";
                    break;
                case EmailType.ProspectInvite:
                    filename = "prospect_invite.html";
                    break;
                case EmailType.BusinessAssociatewelcome:
                    filename = "Business_Associate_welcome.html";
                    break;
                case EmailType.ResetPassword:
                    filename = "ResetPassword.html";
                    break;
                case EmailType.Ticketcreation:
                    filename = "Ticket_Creation.html";
                    break;
                case EmailType.TicketcreationbyCustomer:
                    filename = "Ticket_Created_by_Customer.html";
                    break;
                case EmailType.SupportTicketConfirmation:
                    filename = "SupportTicketConfirmation.html";
                    break;
                case EmailType.SupportTicketResolved:
                    filename = "SupportTicketResolved.html";
                    break;
                case EmailType.QueryAskedDepartment:
                    filename = "QueryAskedDepartment.html";
                    break;
                case EmailType.invoice:
                    filename = $"Invoice_{ConfigurationManager.AppSettings.Get("CountryCode")}.html";
                    break;
                case EmailType.invoiceresturant:
                    filename = "Invoice_RestureantIN.html";
                    break;
                case EmailType.itemrepeat:
                    filename = "itemrepeat.html";
                    break;
                case EmailType.orderslip:
                    filename = "OrderSlip.html";
                    break;
                case EmailType.Productdetalis:
                    filename = "productdetalis.html";
                    break;
                case EmailType.PackingSlip:
                    filename = "packingslip.html";
                    break;
                case EmailType.otpverification:
                    filename = "OtpVerification.html";
                    break;
                case EmailType.invoiceservice:
                    filename = $"Invoice_{ConfigurationManager.AppSettings.Get("CountryCode")}_service.html";
                    break;
                case EmailType.itemdeliverycharge:
                    filename = "itemdeliverycharge.html";
                    break;
            }

            return filename;
        }
        public static string CreateEmailbody(EmailType emailType, List<KeyValuePair<string, string>> lstReplacements)
        {

            string filename = GetTemplateFile(emailType);
            string body = string.Empty;
            try
            {
                using (System.IO.StreamReader reader = new System.IO.StreamReader(HostingEnvironment.MapPath("/Content/template/" + filename)))
                {
                    body = reader.ReadToEnd();
                }

                if (lstReplacements != null && lstReplacements.Count > 0)
                {
                    foreach (KeyValuePair<string, string> val in lstReplacements)
                        if (!string.IsNullOrEmpty(val.Key))
                            body = body.Replace(val.Key, val.Value);
                }
            }
            catch { }

            return body;
        }
              
    }

}
