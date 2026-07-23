using Microsoft.AspNetCore.Mvc;
using Retaline.Core.BusinessModel.UserDetails;
using Retaline.Core.ViewModel.Home;
using System.Collections.Generic;

namespace Retaline.Web.Views.Shared.Components.Header
{
    public class HeaderViewComponent : ViewComponent
    {
        //private readonly Core.Services.Cart.ICartService _cartService;
        //public HeaderViewComponent(Core.Services.Cart.ICartService cartService)
        //{
        //    _cartService = cartService;
        //}

        public IViewComponentResult Invoke() //(HomeDetailsViewModel details)
        {
            int CartCount = 0;
            //if (User.Identity.IsAuthenticated)
            //{
            //    try
            //    {
            //        var cartDetails = _cartService.GetCart().Result;
            //        if (cartDetails != null && cartDetails.Data != null && cartDetails.Data.Cart != null)
            //        {
            //            CartCount = cartDetails.Data.Cart.Count;
            //        }
            //    }
            //    catch
            //    {
            //        CartCount = 0;
            //    }
            //}
            return View("Header", new HeaderAndFooterViewModel() {  CartCount = CartCount });
            //return View("Header", details);
        }
    }
}
