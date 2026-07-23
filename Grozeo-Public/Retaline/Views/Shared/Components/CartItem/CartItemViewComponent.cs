using Microsoft.AspNetCore.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
//using Retaline.Core.BusinessModel.Cart;

namespace Retaline.Web.Views.Shared.Components.CartItem
{
    public class CartItemViewComponent : ViewComponent
    {
        public IViewComponentResult Invoke(Retaline.Web.Models.Cart.CartItem cartItem)
        {
            return View(cartItem);
        }
    }
}
