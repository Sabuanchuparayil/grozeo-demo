using Retaline.Core.BusinessModel.Cart.Checkout;
using Retaline.Core.BusinessModel.Order;
using System.Collections.Generic;

namespace Retaline.Web.Models.Checkout
{
    public class PlaceOrder
    {
        public long OrderId { get; set; }
        public string OrderNum { get; set; }
        public string OrderGroupId { get; set; }
        public string PaymentMethod { get; set; }
        public string PaymentStatus { get; set; }
        public List<string> Payments { get; set; }
        public string TimeSlote { get; set; }
        public string DeliveryCharge { get; set; }
        public string Total { get; set; }
        public string SubTotal { get; set; }
        public string OrderStatus { get; set; }
        public int StatusCode { get; set; }
        public string Message { get; set; }
        public bool IsPaymentResult { get; set; }
        public string CancelTime { get; set; }
        public string CurAPITime { get; set; }
        public ConfirmOrder OrderInfo { get; set; }
        public string OrderBranchName { get; set; }
        public string OrderBranchLocation { get; set; }
        public string OrderDate { get; set; }
        public List<MyOrder> SuccessOrders { get; set; } = new List<MyOrder>();
    }
}
