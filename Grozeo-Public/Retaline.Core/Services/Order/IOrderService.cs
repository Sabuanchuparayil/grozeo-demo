using Retaline.Core.BusinessModel.API;
using Retaline.Core.BusinessModel.Cart.Checkout;
using Retaline.Core.BusinessModel.Order;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Order
{
    public interface IOrderService
    {
        Task<APIModel<List<MyOrder>>> GetOrderHistory();
        Task<APIModel<BusinessModel.Order.Order>> GetOrderDetails(int orderid);
        Task<APIModel<BusinessModel.Order.Order>> GetOrderDetails(string ordernum);
        Task<APIModel<PaymentStatus>> PaymentStatus(int orderId, string orderNum);
        Task<APIModel<BusinessModel.UserDetails.Wallet>> MyWalletInfo();
        Task<APIModel<BusinessModel.UserDetails.Wallet>> MyWalletInfo(string fromDate, string toDate);
        Task<APISuccessModel> CancelOrder(long orderId, string reason = "");
        Task<PagedResult<List<MyOrder>>> GetOrderHistory(bool active);
        Task<APIModel<List<MyOrder>>> GetGroupOrderDetails(string orderGroupId);
        Task<APIModel<List<OrderItem>>> GetReturnables(string orderNum);
        Task<APISuccessModel> ReturnItems(string orderId, List<OrderItem> orderItems);
        Task<APIModel<string>> GetInvoiceDetails(int orderId, string email, string customerName);
    }
}
