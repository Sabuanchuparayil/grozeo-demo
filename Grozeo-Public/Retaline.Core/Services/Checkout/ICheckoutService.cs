using Retaline.Core.BusinessModel.API;
using Retaline.Core.BusinessModel.Order;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Checkout
{
    public interface ICheckoutService
    {
        Task<APIModel<BusinessModel.Cart.Checkout.ConfirmOrder>> CheckoutConfirm(BusinessModel.Cart.Checkout.Customer customer, int paymentMode, int timeslotId, string strReturnUrl = "", int useWallet = 0, int useCoupon = 0);
        Task<APIModel<BusinessModel.Cart.Checkout.Coupon>> ApplyCoupon(int orderId, string couponCode, bool useWallet = false);
        Task<APIModel<BusinessModel.Cart.Checkout.Coupon>> RemoveCoupon(int orderId, string couponCode, bool useWallet = false);
        
        Task<APIModel<BusinessModel.Cart.Checkout.Coupon>> UseWallet(int orderId, bool useWallet = false);
        Task<APIModel<APISuccessModel>> AbortOnlinePayment(int orderId);
        //Task<APIModel<List<BusinessModel.Cart.Checkout.CheckoutOrder>>> CartCheckout(BusinessModel.Cart.Checkout.Customer customer, int paymentMode, int timeslotId, string strReturnUrl = "", int useWallet = 0, int useCoupon = 0);
        Task<APIModel<BusinessModel.Cart.Checkout.Checkout>> CartCheckout(BusinessModel.Cart.Checkout.Customer customer, int paymentMode, int timeslotId, string strReturnUrl = "", int useWallet = 0, int useCoupon = 0);
        Task<APIModel<BusinessModel.Cart.Checkout.ConfirmOrder>> ConfirmOrder(string orderGroupId, int paymentMode, int timeslotId, int orderMethod = 1, string strReturnUrl = "", int useWallet = 0, int useCoupon = 0);
        Task<APIModel<APISuccessModel>> RazorPaymentComplete(string strkeyid, string strEncryptedString, string rpoi);
        Task<APIModel<OrderGroup>> SetOrderSlot(int orderId, int slotId, string slotDate, int UseWallet);
        Task<APIModel<BusinessModel.Cart.Checkout.ConfirmOrder>> GetPaymentUrl(int orderId);
        Task<APIModel<APISuccessModel>> EazybuzzComplete(object obj);
        Task<APIModel<APISuccessModel>> RemoveorderGroup(long orderId);
        Task<APIModel<MyOrder>> AddOrderNote(long orderId, string note);
        
    }
}
