using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Models.Checkout
{
    public class CartCheckout
    {
        public bool IsPaymentView { get; set; }
        public Core.BusinessModel.Cart.Checkout.Checkout CheckoutInfo { get; set; }
        public Core.BusinessModel.Cart.Checkout.ConfirmOrder OrderInfo { get; set; }
        public bool CanCheckout { get; set; }
        public bool EnabledOnlinePayment { get; set; }
        public List<Core.BusinessModel.Cart.Checkout.CheckoutOrder> Orders { get; set; } 
        public bool PODEnabled { get; set; }
    }
}
