using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Retaline.Core.Services.Wishlist;
using Retaline.Core.ViewModel.Wishlist;
using System.Threading.Tasks;

namespace Retaline.Web.Controllers
{
    [Authorize]
    public class WishlistController : Controller
    {
        private readonly IWishlistService _wishlistService;

        public WishlistController(IWishlistService wishlistService)
        {
            _wishlistService = wishlistService;
        }


        [HttpPost]
        [Route("addtowishlist")]
        public async Task<IActionResult> AddToWishlist([FromBody] AddToWidhlistViewModel details)
        {
            if (details.OrderMethod == null)
                details.OrderMethod = 1;

            if (details.Type == null)
                details.Type = 1;

            var result = await _wishlistService.AddToWishlist(details);
            return Json(result);
        }

        [HttpPost]
        [Route("deletefromwishlist")]
        public async Task<IActionResult> DeleteFromWishlist([FromBody] AddToWidhlistViewModel details)
        {
            var result = await _wishlistService.DeleteFromWishlist(details.ProductId, details.GroupId);
            return Json(result);
        }
    }
}
