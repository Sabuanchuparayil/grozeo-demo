using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.API;
using Retaline.Core.BusinessModel.Wishlist;
using Retaline.Core.Services.HelperServices;
using Retaline.Core.ViewModel.Wishlist;
using System.Collections.Generic;
using System.Threading.Tasks;
using System.Linq;
using Retaline.Core.BusinessModel.Catalog;

namespace Retaline.Core.Services.Wishlist
{
    public class WishlistService : IWishlistService
    {
        private readonly IHttpHelperService _httpHelperService;
        private readonly IConfiguration _configuration;
        private WishlistInfo _cachedWishlistIds;

        public WishlistService(IHttpHelperService httpHelperService, IConfiguration configuration)
        {
            _httpHelperService = httpHelperService;
            _configuration = configuration;
        }

        public async Task<List<Product>> GetWishlist()
        {
            string url = _configuration["ApiUrls:Wishlist:Get"].ToString();
            var result = await _httpHelperService.Get<APIModel<List<Product>>>($"{url}/1", null);
            if(result != null && result.Data != null)
                return result.Data;
            return default;
        }


        public async Task<object> AddToWishlist(int prodId, int groupId)
        {
            string url = _configuration["ApiUrls:Wishlist:Add"].ToString();
            var requestParams = new Dictionary<string, object>
            {
                { "product_id", prodId}, //818
                { "group_id", groupId} // 850
            };


            return await _httpHelperService.Post<object>(url, requestParams);
        }
        public async Task<object> AddToWishlist(AddToWidhlistViewModel model)
        {
            string url = _configuration["ApiUrls:Wishlist:Add"].ToString();
            return await _httpHelperService.Post<object>(url, model);
        }
        public async Task<object> DeleteFromWishlist(int productId, int groupId)
        {
            string url = _configuration["ApiUrls:Wishlist:Delete"].ToString();
            url = $"{url}/{groupId}/{productId}/1";
            return await _httpHelperService.Delete<object>(url, null);
        }

        public WishlistInfo WishlistIds()
        {
            if (_cachedWishlistIds == null)
            {
                //_cachedWishlistIds = new WishlistInfo();
                //try
                //{
                //    var response = GetWishlist().Result;
                //    if (response != null && response.Data != null)
                //        _cachedWishlistIds = response.Data;
                //}
                //catch
                //{
                //    _cachedWishlistIds = new WishlistInfo();
                //}
            }

            return _cachedWishlistIds;
        }

    }
}
