using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.API;
using Retaline.Core.BusinessModel.Cart;
using Retaline.Core.Services.HelperServices;
using Retaline.Core.ViewModel.Cart;
using System.Collections.Generic;
using System.Threading.Tasks;
using System.Linq;

namespace Retaline.Core.Services.Cart
{
    public class CartService : ICartService
    {
        private readonly IHttpHelperService _httpHelperService;
        private readonly IConfiguration _configuration;
        private readonly Services.Authentication.ICustomAuthenticationService _customAuthenticationService;

        private List<CartDetails> _cachedCart;

        public CartService(IHttpHelperService httpHelperService, IConfiguration configuration, Services.Authentication.ICustomAuthenticationService customAuthenticationService)
        {
            _httpHelperService = httpHelperService;
            _configuration = configuration;
            _customAuthenticationService = customAuthenticationService;
        }

        public async Task<CartRoot> GetCart()
        {
            string cartUrl = $"{_configuration["ApiUrls:ProfileManagement:GetCart"].ToString()}/1";
            var cartRoot = await _httpHelperService.Get<CartRoot>(cartUrl, null);
            if (cartRoot != null && cartRoot.Data != null)
            {
                if (cartRoot.Data.Cart == null && cartRoot.Data.CartMini != null)
                    _cachedCart = cartRoot.Data.CartMini;
                else
                    _cachedCart = cartRoot.Data.Cart;//.Select(c => c.Item).ToList();
            }
            return cartRoot; //await _httpHelperService.Get<CartRoot>(cartUrl, null);

        }

        public async Task<CartRoot> GetCartDetails(int orderType = 1)
        {
            string cartUrl = $"{_configuration["ApiUrls:Cart:CartDetails"].ToString()}/{orderType}";
            //cartUrl = $"http://grozeo.api.dev.velosit.in/api/cart/cartdetails/{orderType}";
            var cartRoot = await _httpHelperService.Get<CartRoot>(cartUrl, null);
            if (cartRoot != null && cartRoot.Data != null)
                _cachedCart = cartRoot.Data.Cart;//.Select(c => c.Item).ToList();

            return cartRoot; //await _httpHelperService.Get<CartRoot>(cartUrl, null);
        }

        public async Task<CartRoot> GetCartCount(int orderType = 1)
        {
            string cartUrl = $"{_configuration["ApiUrls:Cart:CartCount"].ToString()}/{orderType}";
            var cartRoot = await _httpHelperService.Get<CartRoot>(cartUrl, null);
            if (cartRoot != null && cartRoot.Data != null)
                _cachedCart = cartRoot.Data.CartMini;
            return cartRoot;
        }

        public async Task<APIModel<AddToCartData>> AddToCart(AddToCartViewModel details)
        {
            string cartUrl = _configuration["ApiUrls:Cart:AddToCart"].ToString();
            return await _httpHelperService.Post<APIModel<AddToCartData>>(cartUrl, details);
        }

        public async Task<APIModel<AddToCartData>> ReplaceItem(AddToCartViewModel cartData)
        {
            string cartUrl = _configuration["ApiUrls:Cart:ReplaceItem"].ToString();
            return await _httpHelperService.Post<APIModel<AddToCartData>>(cartUrl, cartData);
        }
        public async Task<APISuccessModel> UpdateCart(CartProductIdDetails details)
        {
            string cartUrl = _configuration["ApiUrls:Cart:UpdateCart"].ToString();
            return await _httpHelperService.Put<APISuccessModel>(cartUrl, details);
        }

        public async Task<object> DeleteCartItem(int productId)
        {
            string cartUrl = _configuration["ApiUrls:Cart:DeleteCart"].ToString();
            return await _httpHelperService.Delete<object>($"{cartUrl}{productId}", null);
        }

        public async Task<APISuccessModel> ClearCart()
        {
            string cartUrl = _configuration["ApiUrls:Cart:ClearCart"].ToString();
            return await _httpHelperService.Delete<APISuccessModel>(cartUrl+"1", null);
        }

        public async Task<BusinessModel.Cart.Checkout.Checkout> CheckoutInfo(int orderMethod=1, int shippingMethod=1, int branchId=14, int delBranchId=14)
        {
            var user = _customAuthenticationService.GetUserFromClaims();
            string checkoutUrl = _configuration["ApiUrls:Cart:Checkout"].ToString();
            Dictionary<string, object> postData = new Dictionary<string, object>
            {

                { "order_method", 1 },
                {"branch_id", user.BranchId },
                {"shipping_method", shippingMethod },
                {"delivery_branch_id", delBranchId },
                {"prescription_id", new int[]{ } }
            };

            var apidata= await _httpHelperService.Post<APIModel<List<BusinessModel.Cart.Checkout.Checkout>>>(checkoutUrl, postData);
            if (apidata != null)
                return apidata.Data[0];

            return default;

        }

        public async Task<APIModel<DeliveryMethod>> CartDeliverymethod(int ordermethod=1, int branchid=14)
        {
            //var user = _customAuthenticationService.GetUserFromClaims();
            string checkoutUrl = _configuration["ApiUrls:Cart:DeliveryMethod"].ToString();
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                { "order_method", ordermethod },
                {"branch_id", -1 } // user.BranchId 
            };
            return await _httpHelperService.Post<APIModel<DeliveryMethod>>(checkoutUrl, postData);

        }

        // List<CartDetails>
        public async Task<APIModel<List<CartDetails>>> GetPriscriptions(int ordermethod = 1, int branchid = 10)
        {
            string checkoutUrl = _configuration["ApiUrls:Cart:Prescription"].ToString();
            Dictionary<string, object> postData = new Dictionary<string, object>
            {
                { "order_method", ordermethod },
                {"branch_id", branchid  }
            };
            return await _httpHelperService.Post<APIModel<List<CartDetails>>>(checkoutUrl, postData);
        }

        public async Task<List<CartDetails>> CartItems()
        {
            if (_cachedCart == null)
                await GetCart();

            return _cachedCart;

            //var str = string.Join(",", _cachedCart.Select(c => string.Join(",", c.ItemMaster.Select(i => string.Format("{ \"id\":{0}, \"grp\":{1}, \"qty\":{2} }", i.StitID, i.StitFsiuid, i.Quantity)).ToArray())).ToArray()); //Any(c=> c.ItemMaster.Any(i=> i.StitID == 0 && i.StitFsiuid==0))
        }

    }
}
