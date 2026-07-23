using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Retaline.Core.Services.Authentication;
using Retaline.Core.Services.Cart;
using Retaline.Core.Services.Checkout;
using System.Configuration;


namespace Retaline.Web.Views.Shared.Components.AddressModel
{
    public class GuestLocationModalViewComponent : ViewComponent
    {
        private readonly IConfiguration _configuration;

        public GuestLocationModalViewComponent(IConfiguration configuration)
        {
            _configuration = configuration;
        }
        public IViewComponentResult Invoke(int addressCount)
        {
       
                return View("GuestLocationModal");

        }
    }
}
