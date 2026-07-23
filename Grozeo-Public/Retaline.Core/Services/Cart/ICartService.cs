using Retaline.Core.BusinessModel.API;
using Retaline.Core.BusinessModel.Cart;
using Retaline.Core.ViewModel.Cart;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Cart
{
    public interface ICartService
    {
        Task<CartRoot> GetCart();
        Task<APIModel<AddToCartData>> AddToCart(AddToCartViewModel details);
        Task<APIModel<AddToCartData>> ReplaceItem(AddToCartViewModel cartData);
        Task<APISuccessModel> UpdateCart(CartProductIdDetails details);
        Task<object> DeleteCartItem(int productId);
        Task<APISuccessModel> ClearCart();
        Task<BusinessModel.Cart.Checkout.Checkout> CheckoutInfo(int orderMethod = 1, int shippingMethod = 1, int branchId = 14, int delBranchId = 14);
        Task<List<CartDetails>> CartItems();
        Task<CartRoot> GetCartDetails(int orderType = 1);
        Task<APIModel<DeliveryMethod>> CartDeliverymethod(int ordermethod = 1, int branchid = 14);
        Task<APIModel<List<CartDetails>>> GetPriscriptions(int ordermethod = 1, int branchid = 14);
        Task<CartRoot> GetCartCount(int orderType = 1);
    }
}