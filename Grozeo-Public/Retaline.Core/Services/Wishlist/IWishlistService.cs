using Retaline.Core.BusinessModel.API;
using Retaline.Core.BusinessModel.Catalog;
using Retaline.Core.BusinessModel.Wishlist;
using Retaline.Core.ViewModel.Wishlist;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Wishlist
{
    public interface IWishlistService
    {
        Task<List<Product>> GetWishlist();
        Task<object> AddToWishlist(int prodId, int groupId);
        Task<object> DeleteFromWishlist(int productId, int groupId);
        WishlistInfo WishlistIds();
        Task<object> AddToWishlist(AddToWidhlistViewModel model);
    }
}