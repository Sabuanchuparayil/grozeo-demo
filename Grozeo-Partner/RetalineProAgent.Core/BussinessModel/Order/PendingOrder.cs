using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Order
{
    public class PendingOrder
    {
        public string orderID { get; set; }
        public string OrderOrderID { get; set; }
        public string DeliveryMode { get; set; }
        public string CustomerDetails { get; set; }
        public string Address { get; set; }
        public string OrderDate { get; set; }
        public string MerchantName { get; set; }
        public string MerchantDetails { get; set; }
        public string OrderTotal { get; set; }
        public string OrderSubTotal { get; set; }
        public string BranchID { get; set; }
        public string Mode { get; set; }
        public string PaymentMode { get; set; }
        public string StoreID { get; set; }
        public string StoreName { get; set; }

        public string Type {  get; set; }
        public string UUID { get; set; }
        public string Timestamp { get; set; }
        public string Action { get; set; }
        public string ModeMethod { get; set; }

    }
}
