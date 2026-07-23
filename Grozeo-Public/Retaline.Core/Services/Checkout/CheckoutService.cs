using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.API;
using Retaline.Core.BusinessModel.Order;
using Retaline.Core.Services.HelperServices;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Checkout
{
    public class CheckoutService : ICheckoutService
    {
        private readonly IHttpHelperService _httpHelperService;
        private readonly IConfiguration _configuration;
        private readonly Services.Authentication.ICustomAuthenticationService _customAuthenticationService;
        public CheckoutService(IHttpHelperService httpHelperService, IConfiguration configuration, Services.Authentication.ICustomAuthenticationService customAuthenticationService)
        {
            _httpHelperService = httpHelperService;
            _configuration = configuration;
            _customAuthenticationService = customAuthenticationService;
        }

        public async Task<APIModel<BusinessModel.Cart.Checkout.ConfirmOrder>> CheckoutConfirm(BusinessModel.Cart.Checkout.Customer customer, int paymentMode, int timeslotId, string strReturnUrl="", int useWallet=0, int useCoupon=0)
        {
            var user = _customAuthenticationService.GetUserFromClaims();
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                //{"portal_redirecturl", Utilities.Common.EncodeBase64String(strReturnUrl) },
                {"branch_id", user.BranchId},
                {"order_method",1},
                {"payment_mode",paymentMode ==0 ? 1 : paymentMode},
                {"nearest_retailer_branch", user.BranchId },
                {"shipping_method","2"},
                {"delivery_branch_id", user.BranchId.ToString()},
                {"prescription_id", new int[]{} },
                {"portal_redirecturl", Utilities.Common.EncodeBase64String(strReturnUrl) }

            };

            string checkoutUrlProcess = _configuration["ApiUrls:Checkout:CheckoutConfirm"].ToString();
            return await _httpHelperService.Post<APIModel<BusinessModel.Cart.Checkout.ConfirmOrder>>(checkoutUrlProcess, postData);

        }
        public async Task<APIModel<BusinessModel.Cart.Checkout.ConfirmOrder>> ConfirmOrder(string orderGroupId, int paymentMode, int timeslotId, int orderMethod = 1, string strReturnUrl = "", int useWallet = 0, int useCoupon = 0)
        {
            var user = _customAuthenticationService.GetUserFromClaims();
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                {"branch_id", user.BranchId},
                {"order_group_id", orderGroupId},
                {"order_method",orderMethod},
                {"payment_mode",paymentMode ==0 ? 1 : paymentMode},
                {"shipping_method","2"},
                {"splitorder", 1 },
                {"nearest_retailer_branch", user.BranchId },
                {"delivery_branch_id", user.BranchId },
                {"prescription_id", new int[]{} },
                {"use_wallet", useWallet },
                {"is_order_coupon", useCoupon },
                {"portal_redirecturl", Utilities.Common.EncodeBase64String(strReturnUrl) }

            };

            string checkoutUrlProcess = _configuration["ApiUrls:Checkout:ConfirmOrder"].ToString();
            return await _httpHelperService.Post<APIModel<BusinessModel.Cart.Checkout.ConfirmOrder>>(checkoutUrlProcess, postData);

        }

        public async Task<APIModel<BusinessModel.Cart.Checkout.ConfirmOrder>> GetPaymentUrl(int orderId)
        {
            var user = _customAuthenticationService.GetUserFromClaims();
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                {"orderId", orderId}
            };

            string checkoutUrlProcess = _configuration["ApiUrls:Order:GetPaymentUrl"].ToString();
            return await _httpHelperService.Post<APIModel<BusinessModel.Cart.Checkout.ConfirmOrder>>(checkoutUrlProcess, postData);

        }

        //public async Task<APIModel<List<BusinessModel.Cart.Checkout.CheckoutOrder>>> CartCheckout(BusinessModel.Cart.Checkout.Customer customer, int paymentMode, int timeslotId, string strReturnUrl = "", int useWallet = 0, int useCoupon = 0)
        public async Task<APIModel<BusinessModel.Cart.Checkout.Checkout>> CartCheckout(BusinessModel.Cart.Checkout.Customer customer, int paymentMode, int timeslotId, string strReturnUrl = "", int useWallet = 0, int useCoupon = 0)
        {
            var user = _customAuthenticationService.GetUserFromClaims();
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                //{"portal_redirecturl", Utilities.Common.EncodeBase64String(strReturnUrl) },
                {"branch_id", user.BranchId},
                {"order_method",1},
                {"splitorder", 1 },
                {"getwalletbalance", 1 },
                {"payment_mode",paymentMode ==0 ? 1 : paymentMode},
                {"nearest_retailer_branch", user.BranchId },
                {"shipping_method","2"},
                {"delivery_branch_id", user.BranchId.ToString()},
                {"prescription_id", new int[]{} },
                {"portal_redirecturl", Utilities.Common.EncodeBase64String(strReturnUrl) }

            };

            string checkoutUrlProcess = _configuration["ApiUrls:Checkout:CheckoutConfirm"].ToString();
            return await _httpHelperService.Post<APIModel<BusinessModel.Cart.Checkout.Checkout>>(checkoutUrlProcess, postData);

        }


        public async Task<APIModel<BusinessModel.Cart.Checkout.Coupon>> ApplyCoupon(int orderId, string couponCode, bool useWallet=false)
        {
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                { "order_id", orderId },
                {"coupon_code", couponCode },
                {"use_wallet", (useWallet?1:0) }
            };

            string checkoutUrlProcess = _configuration["ApiUrls:Checkout:ApplyCoupon"].ToString();
            return await _httpHelperService.Post<APIModel<BusinessModel.Cart.Checkout.Coupon>>(checkoutUrlProcess, postData);

        }
        
             public async Task<APIModel<BusinessModel.Cart.Checkout.Coupon>> RemoveCoupon(int orderId, string couponCode, bool useWallet = false)
        {
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                { "order_id", orderId },
                {"coupon_code", couponCode },
                {"use_wallet", (useWallet?1:0) }
            };

            string checkoutUrlProcess = _configuration["ApiUrls:Checkout:RemoveCoupon"].ToString();
            return await _httpHelperService.Post<APIModel<BusinessModel.Cart.Checkout.Coupon>>(checkoutUrlProcess, postData);

        }
        public async Task<APIModel<BusinessModel.Cart.Checkout.Coupon>> UseWallet(int orderId, bool useWallet = false)
        {
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                { "order_id", orderId },
                //{"coupon_code", couponCode },
                {"use_wallet", (useWallet?1:0) }
            };

            string checkoutUrlProcess = _configuration["ApiUrls:Checkout:UseWallet"].ToString();
            return await _httpHelperService.Post<APIModel<BusinessModel.Cart.Checkout.Coupon>>(checkoutUrlProcess, postData);

        }

        public async Task<APIModel<APISuccessModel>> AbortOnlinePayment(int orderId)
        {
            string abortUrl = _configuration["ApiUrls:Checkout:Abort"].ToString();
            return await _httpHelperService.Get<APIModel<APISuccessModel>>(String.Format(abortUrl, orderId));
        }

        public async Task<APIModel<APISuccessModel>> RazorPaymentComplete(string strkeyid, string strEncryptedString, string rpoid)
        {
            string paymentCompleteUrl = _configuration["ApiUrls:Checkout:PaymentComplete"] + "razorpay";
            string getUrl = $"{paymentCompleteUrl}?razorpay_payment_id={strkeyid}&razorpay_signature={strEncryptedString}&rpoid={rpoid}";
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                { "razorpay_payment_id", strkeyid },
                {"razorpay_signature", strEncryptedString }
            };
            return await _httpHelperService.Get<APIModel<APISuccessModel>>(getUrl);

        }

        public async Task<APIModel<OrderGroup>> SetOrderSlot(int orderId, int slotId, string slotDate, int UseWallet)
        {
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                { "order_id", orderId },
                {"slot_id", slotId },
                {"slot_date", slotDate },
                {"use_wallet", UseWallet }
                
            };
            string url = _configuration["ApiUrls:Checkout:SetSlot"].ToString();
            return await _httpHelperService.Post<APIModel<OrderGroup>>(url, postData);
        }

        public async Task<APIModel<APISuccessModel>> EazybuzzComplete(object obj)
        {
            string paymentCompleteUrl = _configuration["ApiUrls:Checkout:PaymentComplete"] + "easebuzz";
            //Dictionary<string, object> postData = new Dictionary<string, object>
            //{
            //    { "razorpay_payment_id", strkeyid },
            //    {"razorpay_signature", strEncryptedString }
            //};
            return await _httpHelperService.Post<APIModel<APISuccessModel>>(paymentCompleteUrl, obj);

        }

        
       public async Task<APIModel<APISuccessModel>> RemoveorderGroup(long orderId)
        {
            string url = _configuration["ApiUrls:Checkout:RemoveOrder"];
            var obj = new
            {
                order_id = orderId
            };
            return await _httpHelperService.Post<APIModel<APISuccessModel>>(url, obj);
        }
        
        public async Task<APIModel<MyOrder>> AddOrderNote(long orderId, string note)
        {
            string url = _configuration["ApiUrls:Checkout:AddOrderNote"];
            var obj = new
            {
                order_id = orderId,
                note = note
            };
            return await _httpHelperService.Post<APIModel<MyOrder>>(url, obj);
        }
    }
}
