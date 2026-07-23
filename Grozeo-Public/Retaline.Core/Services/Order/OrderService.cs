using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.API;
using Retaline.Core.BusinessModel.Cart.Checkout;
using Retaline.Core.BusinessModel.Order;
using Retaline.Core.Services.HelperServices;
using StackExchange.Redis;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Order
{
    public class OrderService : IOrderService
    {
        private readonly IHttpHelperService _httpHelperService;
        private readonly IConfiguration _configuration;
        public OrderService(IHttpHelperService httpHelperService, IConfiguration configuration)
        {
            _httpHelperService = httpHelperService;
            _configuration = configuration;
        }
        public async Task<APIModel<List<MyOrder>>> GetOrderHistory()
        {
            string orderHistoryUrl = _configuration["ApiUrls:Order:OrderHistory"].ToString();
            return await _httpHelperService.Get<APIModel<List<MyOrder>>>(orderHistoryUrl, null);
        }

        public async Task<PagedResult<List<MyOrder>>> GetOrderHistory(bool active)
        {
            string orderHistoryUrl = "";
            if (active)
                orderHistoryUrl = _configuration["ApiUrls:Order:ActiveOrders"].ToString();
            else
                orderHistoryUrl = _configuration["ApiUrls:Order:FailedOrders"].ToString();
            var result= await _httpHelperService.Get<APIModel<PagedResult<List<MyOrder>>>>(orderHistoryUrl, null);
            if(result != null && result.Data != null)
                return result.Data;

            return default;
        }

        public async Task<APIModel<BusinessModel.Order.Order>> GetOrderDetails(int orderid)
        {
            string orderUrl = _configuration["ApiUrls:Order:OrderDetails"].ToString();
            return await _httpHelperService.Get<APIModel<BusinessModel.Order.Order>>(String.Format(orderUrl, orderid), null);

        }
        public async Task<APIModel<BusinessModel.Order.Order>> GetOrderDetails(string ordernum)
        {
            string orderUrl = _configuration["ApiUrls:Order:OrderDetails"].ToString();
            return await _httpHelperService.Get<APIModel<BusinessModel.Order.Order>>(String.Format(orderUrl, ordernum), null);

            //string orderUrl = "/api/orders/complete"; //_configuration["ApiUrls:Order:OrderDetailsComplete"].ToString();
            //Dictionary<string, object> postData = new Dictionary<string, object>
            //{
            //    { "orderId", ordernum }
            //};
            //return await _httpHelperService.Post<APIModel<BusinessModel.Order.Order>>(orderUrl, postData);
        }
        public async Task<APIModel<BusinessModel.UserDetails.Wallet>> MyWalletInfo(string fromDate, string toDate)
        {
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                { "from_date", fromDate },
                {"to_date", toDate  }
            };
            string walletUrl = _configuration["ApiUrls:ProfileManagement:MyWallet"].ToString();
            return await _httpHelperService.Post<APIModel<BusinessModel.UserDetails.Wallet>>(walletUrl, postData);

        }

        public async Task<APIModel<BusinessModel.UserDetails.Wallet>> MyWalletInfo()
        {
            string walletUrl = _configuration["ApiUrls:ProfileManagement:MyWallet"].ToString();
            return await _httpHelperService.Get<APIModel<BusinessModel.UserDetails.Wallet>>(walletUrl);

        }

        public async Task<APIModel<PaymentStatus>> PaymentStatus(int orderId, string orderNum)
        {
            string paymentStatusUrl = _configuration["ApiUrls:Order:PaymentStatus"].ToString();
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                { "id", orderNum },
                {"order_id", orderId  }
            };
            return await _httpHelperService.Post<APIModel<PaymentStatus>>(paymentStatusUrl, postData);
        }

        public async Task<APISuccessModel> CancelOrder(long orderId, string reason = "")
        {
            string paymentStatusUrl = _configuration["ApiUrls:Order:cancelOrder"].ToString();
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                { "order_id", orderId },
                {"reason", reason }
            };
            return await _httpHelperService.Post<APISuccessModel>(paymentStatusUrl, postData);
        }

        public async Task<APIModel<List<MyOrder>>> GetGroupOrderDetails(string orderGroupId)
        {
            string orderUrl = _configuration["ApiUrls:Order:GroupOrders"].ToString();
            return await _httpHelperService.Get<APIModel<List<MyOrder>>>(string.Format(orderUrl, orderGroupId), null);
        }
        /// <summary>
        /// List Returnables
        /// </summary>
        /// <param name="orderNum"></param>
        /// <returns></returns>
        public async Task<APIModel<List<OrderItem>>> GetReturnables(string orderNum)
        {
            string orderUrl = _configuration["ApiUrls:Order:Returnables"].ToString();
            return await _httpHelperService.Get<APIModel<List<OrderItem>>>(string.Format(orderUrl, orderNum), null);
        }
        public async Task<APISuccessModel> ReturnItems(string orderId, List<OrderItem> orderItems)
        {
            var items = orderItems.Select(i => new { item_id = i.Id, qty = i.OrderQty }).ToArray();
            string returnItemsUrl = _configuration["ApiUrls:Order:ReturnItems"].ToString();
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                { "order_id", orderId },
                {"items", items }
            };
            return await _httpHelperService.Post<APISuccessModel>(returnItemsUrl, postData);
        }

        public async Task<APIModel<string>> GetInvoiceDetails(int orderId, string email, string customerName)
        {
            string invoiceUrl = _configuration["ApiUrls:Order:InvoiceContent"].ToString();
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                { "orderId", orderId }
            };

            var result = await _httpHelperService.Post<APIModel<APIModel<string>>>(invoiceUrl, postData);
            return result.Data;
        }


    }
}
