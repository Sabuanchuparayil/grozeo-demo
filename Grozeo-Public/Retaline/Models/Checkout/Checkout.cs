using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Models.Checkout
{
    public class Checkout
    {
        public int OrderId { get; set; }
        public string OrderNum { get; set; }
        public string OrderGroupId { get; set; }
        public int CustomerId { get; set; }
        public double Total { get; set; }
        public PaymentMode PaymentMethod { get; set; }
        public string TimeSlote { get; set; }
        public int DeliveryAddressId { get; set; }
        public DeliveryMode TimeSlotMode { get; set; }
        public bool IsPaymentResult { get; set; }
        public string CouponCode { get; set; }
        public bool UseWallet { get; set; }
        public double NetAmount { get; set; }
        public DeliveryOption DeliveryType { get; set; }
        public int IsPodToOnline { get; set; }
    }
    public enum PaymentMode
    {
        POD=1,
        Online=2,
        Wallet=3,
        CODWithWallet=4,
        OnlineWithWallet=5,
        OnlineOnDelivery=6,
        COD=7
    }
    public enum DeliveryMode
    {
        Soon=1,
        Selected=2
    }

    public enum DeliveryOption
    {
        Home=1,
        Courier=2
    }
}
