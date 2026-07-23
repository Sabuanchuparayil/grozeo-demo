using Retaline.Core.BusinessModel.Catalog;
using Retaline.Core.BusinessModel.Home.Advertisement;
using Retaline.Core.ViewModel.Tenant;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Security.Cryptography;
using System.Text.RegularExpressions;
using System.Threading.Tasks;
using System.Web;

namespace Retaline.Web.Service
{
    public static class Common
    {
        public static string GetPaymentModeString(Retaline.Web.Models.Checkout.PaymentMode paymentMode)
        {
            string strPaymentMode = "";
            switch (paymentMode)
            {
                case Models.Checkout.PaymentMode.COD:
                    strPaymentMode = "Cash on Delivery";
                    break;
                case Models.Checkout.PaymentMode.CODWithWallet:
                    strPaymentMode = "Wallet and Cash on Delivery";
                    break;
                case Models.Checkout.PaymentMode.Online:
                    strPaymentMode = "Online Payment";
                    break;
                case Models.Checkout.PaymentMode.OnlineOnDelivery:
                    strPaymentMode = "Online Payment on Delivery";
                    break;
                case Models.Checkout.PaymentMode.OnlineWithWallet:
                    strPaymentMode = "Wallet and Online Payment";
                    break;
                case Models.Checkout.PaymentMode.POD:
                    strPaymentMode = "Pay on Delivery";
                    break;
                case Models.Checkout.PaymentMode.Wallet:
                    strPaymentMode = "Wallet";
                    break;
            }

            return strPaymentMode;
        }
        public static string ShrinkText(string content, int maxSize, bool addDots = true)
        {
            if (!String.IsNullOrEmpty(content) && content.Length > maxSize)
                return content.Substring(0, maxSize) + (addDots ? ".." : "");
            return content;
        }

        public static string GetStatusText(int statusId)
        {
            string strStatus = "";
            switch (statusId)
            {
                case 0: 
                    strStatus = "Order Checked Out"; 
                    break;
                case 1: 
                    strStatus = "Online Payment Initiated"; 
                    break;
                case 2: 
                    strStatus = "Online Payment Failed"; 
                    break;
                case 3: 
                    strStatus = "Payment Received"; 
                    break;
                case 4: 
                case 5: 
                case 7: 
                    strStatus = "Order Confirmed"; 
                    break;
                case 6: 
                case 8: 
                    strStatus = "Processing the Order"; 
                    break;
                case 9: 
                case 10: 
                case 11: 
                case 12: 
                case 13: 
                case 14: 
                    strStatus = "Order Waiting for Pickup"; 
                    break;
                case 15: 
                case 20: 
                    strStatus = "Order on the Way for Delivery"; 
                    break;
                case 16: 
                    strStatus = "Delivery Failed"; 
                    break;
                case 17: 
                case 18: 
                    strStatus = "Delivery Completed"; 
                    break;
                case 21: 
                    strStatus = "Payment Not Received"; 
                    break;
                case 22: 
                    strStatus = "Waiting for Payment Approval"; 
                    break;
                 case 23: 
                    strStatus = "Some Items Not Available"; 
                    break;
                case 19: 
                case 24: strStatus = "Order Cancelled"; 
                    break;
           }

            return strStatus;
        }

        public static int OrderPostion(int statusId)
        {
            int pos = -1;
            if ((new int[] { 3, 4, 5, 7 }).Contains(statusId))
                pos = 0; // Order Confirmed
            else if ((new int[] { 6, 8, 23 }).Contains(statusId))
                pos = 1; // Processing Order
            else if ((new int[] { 9, 10, 11, 12, 13, 14, 16 }).Contains(statusId))
                pos = 2; // Ready to Pickup
            else if ((new int[] { 20, 15 }).Contains(statusId))
                pos = 3; // En Route to Deliver
            else if ((new int[] { 17, 18 }).Contains(statusId))
                pos = 5; // Delivery Completed

            return pos;
        }

        public static string GenerateCategoryLink(CategoryData category)
        {
            if (category == null)
                return "";
            switch (category.Categorylevel)
            {
                // virtual category
                case 4:
                    return String.Format("/vc/{0}/{1}", category.Id, category.CategoryName);
                    break;
                case 3:
                    return String.Format("/pc/{0}/{1}/{2}/{3}", category.ParentCategoryId, category.CategoryId, category.Id, category.CategoryName);
                    break;
                case 2:
                    return String.Format("/pc/{0}/{1}/{2}", category.ParentCategoryId, category.Id, category.CategoryName);
                    break;
                case 1:
                    return String.Format("/pc/{0}/{1}", category.Id, category.CategoryName);
                    break;
            }
            return "";
        }
        public static string OfferUrl(AdZoneInfo adZoneInfo, int page=1)
        {
            if (adZoneInfo == null || adZoneInfo.AdvId == -1)
                return "";

            string offerUrl = "/offers";
            if (!String.IsNullOrEmpty(adZoneInfo.AdvOffer) && adZoneInfo.AdvOfferValue > 0)
            {
                if(adZoneInfo.AdvOffer.ToLower() == "brand")
                    offerUrl = String.Format("/brand/{0}/{1}", adZoneInfo.AdvOfferValue, adZoneInfo.AdvTitle??"offers");
                else if (adZoneInfo.AdvOffer.ToLower() == "product")
                    offerUrl = String.Format("/pd/{0}/-1/0/{1}", adZoneInfo.AdvOfferValue
                        //, (adZoneInfo.AdvAdditionalInfo != null && adZoneInfo.AdvAdditionalInfo.AdvZoneGroup > 0 ? adZoneInfo.AdvAdditionalInfo.AdvZoneGroup : adZoneInfo.AdvOfferValue)
                        , adZoneInfo.AdvTitle ?? "offers");
                else if (adZoneInfo.AdvOffer.ToLower() == "category")
                    offerUrl = String.Format("/pc/0/0/{0}/{1}", adZoneInfo.AdvOfferValue, adZoneInfo.AdvTitle ?? "offers");
                else if(!String.IsNullOrEmpty(adZoneInfo.OfferType))
                    offerUrl = String.Format("/offers/-1/-1/{0}/{1}/{2}", adZoneInfo.OfferType, adZoneInfo.AdvOfferValue, page);
            }
            return offerUrl;
        }

        public static string StripHTML(string input)
        {
            return Regex.Replace(input, "<.*?>", String.Empty);
        }

        public static string FormatContentWithPlaceholder(string input, AppTenant tenant, bool stipHtml = false)
        {
            if(String.IsNullOrEmpty(input))
                return input;

            if(stipHtml)
                return StripHTML(input.Replace("[[Store Name]]", tenant?.Name));

            return input.Replace("[[Store Name]]", tenant?.Name);
        }

        public static string EncodeUrl(string url)
        {
            return Uri.EscapeDataString(Regex.Replace(url, "[^a-zA-Z0-9 ]+", "", RegexOptions.Compiled).Replace(" ", "-").Replace("--","-").Trim('-'));
        }

        public static string FormatCurrency(string strInput, string currencySymbol)
        {
            if (!String.IsNullOrEmpty(strInput))
            {
                strInput = strInput.Replace("₹", currencySymbol).Replace(currencySymbol + " ", currencySymbol);
            }
            return strInput;
        }
    }
}
