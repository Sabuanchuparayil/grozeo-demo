using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Retaline.Core.Services.Authentication;
using Retaline.Core.Services.Cart;
using Retaline.Core.Services.Checkout;
using System.Configuration;


namespace Retaline.Web.Views.Shared.Components.AddressModel
{
    public class AddressModelViewComponent : ViewComponent
    {
        private readonly IConfiguration _configuration;

        public AddressModelViewComponent(IConfiguration configuration)
        {
            _configuration = configuration;
        }
        public IViewComponentResult Invoke(int addressCount)
        {
            string countryCode = _configuration["CountryCode"];
            if (countryCode == "UK")
                return View("AddressModelUK", addressCount);
            else
                return View("AddressModel", addressCount);

        }
    }
}
