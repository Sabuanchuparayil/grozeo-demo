using Azure.Core;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Newtonsoft.Json;
using Retaline.Core.BusinessModel.Cart;
using Retaline.Core.BusinessModel.UserDetails;
using Retaline.Core.BusinessModel.Wishlist;
using Retaline.Core.Http;
using Retaline.Core.Services.Authentication;
using Retaline.Core.Services.Cart;
using Retaline.Core.Services.Catalog;
using Retaline.Core.Services.Common;
using Retaline.Core.Services.ProfileManagement;
using Retaline.Core.Services.Wishlist;
using Retaline.Core.ViewModel.Product;
using Retaline.Core.BusinessModel.Catalog;
using Retaline.Web.Handlers;
using Retaline.Web.Models.Cart;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net.Http;
using System.Net.Http.Json;
using System.Threading.Tasks;



namespace ODOCart.Controllers
{
    public class HomeController : Controller
    {
        private readonly ICartService _cartService;
        private readonly IHomePageHandlerService _homePageHandler;
        private readonly IWishlistService _wishlistService;
        private readonly ICatalogService _catalogService;
        private readonly IProfileService _profileService;
        private readonly IHttpContextAccessor _httpContextAccessor;
        private readonly IConfiguration _configuration;
        private readonly ICustomAuthenticationService _authenticationService;
        private readonly IGenericAttributeService _genericAttributeService;
        private readonly Retaline.Core.ViewModel.Tenant.AppTenant _tenant;

        public HomeController(
            ICartService cartService, IConfiguration configuration,
            ICatalogService catalogService, IProfileService profileService,
            IHomePageHandlerService homePageHandler, IGenericAttributeService genericAttributeService,
            IHttpContextAccessor httpContextAccessor, ICustomAuthenticationService authenticationService,
            IWishlistService wishlistService, Retaline.Core.ViewModel.Tenant.AppTenant tenant)
        {
            _cartService = cartService;
            _homePageHandler = homePageHandler;
            _wishlistService = wishlistService;
            _catalogService = catalogService;
            _profileService = profileService;
            _httpContextAccessor = httpContextAccessor;
            _configuration = configuration;
            _authenticationService = authenticationService;
            _genericAttributeService = genericAttributeService;
            _tenant = tenant;
        }

        public async Task<IActionResult> Index()
        {
            ViewBag.IsHomePage = "y";
            var details = await _homePageHandler.GetHomePageContent();
            return View(details);
        }

        /// <summary>
        /// External authentication.
        /// </summary>
        /// <param name="type">FB/Google/Whatsapp/Instagram</param>
        /// <param name="code">code from source</param>
        /// <returns></returns>
        [Route("fl/{type}")]
        public async Task<IActionResult> Index(string type = "", string code = "", string state="")
        {
            _httpContextAccessor.HttpContext.Session.Remove("SIGNUPREFCODE");
            int authResult = 2;
            if (!String.IsNullOrEmpty(code))
            {
                string source = "";
                switch (type)
                {
                    case "gm":
                        source = "google";
                        break;
                    case "fb":
                        source = "facebook";
                        break;
                    case "in":
                        source = "instagram";
                        break;
                }

                if (!String.IsNullOrEmpty(source))
                {
                    var result = await _authenticationService.GetUserFromExternalAuth(source, code);
                    if (result != null && result.Data != null && result.Data.User != null && result.Data.User.Id > 0)
                    {
                        authResult = 1;

                        await _authenticationService.CreateAuthenticationTicket(result.Data.User);
                        try
                        {
                            var adr = result?.Data?.User?.PrimaryAddress;
                            if (adr != null && adr.Latitude > 0 && adr.Longitude > 0)
                            {
                                _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLAT", adr.Latitude.ToString());
                                _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLNG", adr.Longitude.ToString());
                            }
                        }
                        catch { }
                    }

                    try
                    {
                        if (_httpContextAccessor != null && _httpContextAccessor.HttpContext != null && _httpContextAccessor.HttpContext.Session != null)
                            _httpContextAccessor.HttpContext.Session.Remove("SHOWADDRALERT");
                    }
                    catch { }
                    if (authResult != 1)
                    {

                        _httpContextAccessor.HttpContext.Session.SetString("FLRESULT", authResult.ToString());
                        _httpContextAccessor.HttpContext.Session.SetString("FLAUTHRESULT", JsonConvert.SerializeObject(result));
                        if(result != null && result.Data != null && !result.Data.IsRegisterd && !String.IsNullOrEmpty(result.Data.refCode))
                            _httpContextAccessor.HttpContext.Session.SetString("SIGNUPREFCODE", result.Data.refCode);

                        return new RedirectResult("/?fl=1");  // 1 for signup after validating email id, 2 for fl failure for some technical reason
                    }

                    return new RedirectResult("/");
                    //return Json(result);

                }
            }

            ViewBag.IsHomePage = "y";
            var details = await _homePageHandler.GetHomePageContent();
            return View(details);
        }

        [Route("testpage")]
        public IActionResult TestPage()
        {

            return View();
        }

        [Authorize]
        public IActionResult Privacy()
        {
            return View();
        }

        [ResponseCache(Duration = 0, Location = ResponseCacheLocation.None, NoStore = true)]
        public IActionResult Error()
        {
            return View("CustomError");
        }

        [Route("homecategories")]
        public async Task<IActionResult> HomeCategories()
        {
            var categories = await _catalogService.GetCatalog();
            if (categories != null && categories.Data != null)
            {
                var categoryitems = categories.Data.Where(c => !String.IsNullOrEmpty(c.ImageUrl)).Select(c => c).ToList();
                return View(categoryitems);
            }
            return View();
        }

        [Route("loadcartandwishlist")]
        public async Task<IActionResult> GetCartAndWishList()
        {
            List<ProductViewModel> cartItems = new();
            List<Product> wishlistItems = new();

            var cartRoot = await _cartService.GetCart();
            var wishList = await _wishlistService.GetWishlist();
            if (cartRoot != null && cartRoot.Data != null)
            {
                List<CartDetails> _cartItems = (cartRoot.Data.Cart == null && cartRoot.Data.CartMini != null ? _cartItems = cartRoot.Data.CartMini : cartRoot.Data.Cart);
                if (_cartItems != null && _cartItems.Count > 0)
                    cartItems = _cartItems.Select(item => new ProductViewModel() { Id = item.CartProductId, GroupId = item.CartGroupId, Quantity = item.CartOrderQty.Value }).ToList();
            }
            if (wishList != null)
            {
                wishlistItems = wishList.Select(item => new Product() { StitId = item.StitId, GroupId = item.StitFsiUId, Source=item.Source }).ToList();
            }


            return Json(new { catitems = cartItems, wishlistitems = wishlistItems });

        }

        [Route("findneareststores/{strlat?}/{strlng?}")]
        public async Task<IActionResult> FindNearestStores(string strlat = "", string strlng = "")
        {
            double lat = 0, lng = 0;
            if (String.IsNullOrEmpty(strlat) || String.IsNullOrEmpty(strlng))
                return Json(new { result = 0, stores = new { } });
            try
            {
                lat = Convert.ToDouble(strlat);
                lng = Convert.ToDouble(strlng);
            }
            catch { }
            if (lat > 0 && lng > 0)
            {
                _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLAT", lat.ToString());
                _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLNG", lng.ToString());
            }

            if (true) //()
            {
                try
                {
                    var result = await _profileService.GetNearestBranches(lat, lng);
                    if (result != null && result.Data != null)
                        return Json(new { result = 0, stores = result.Data });
                }
                catch { }
            }


            return Json(new { result = 0, stores = new { } });

        }

        //[HttpPost]
        //[Route("savecookieconsent")]
        //public IActionResult SaveCookieConsent(Retaline.Core.ViewModel.Home.CookieConsentViewModel model)
        //{
        //    // Save user's consent preferences to cookie or database
        //    // For demonstration, we'll store it in TempData
        //    TempData["CookieConsent"] = model;
        //    return Json(new { result = 1, status = "Success" });
        //    //return RedirectToAction(nameof(Index));
        //}

        [HttpPost]
        [Route("savecookieconsent")]
        public virtual async Task<IActionResult> EuCookieLawAccept()
        {
            //if (!_storeInformationSettings.DisplayEuCookieLawWarning)
            //disabled
            //    return Json(new { stored = false });

            //save setting
            int storeid = 0; try { storeid = Convert.ToInt32(_tenant?.StoreId); } catch { storeid = 0; };
            var customer = _authenticationService.GetUserFromClaims();
            if(customer != null)
                await _genericAttributeService.SaveAttributeAsync(customer.Id, "Customer", RetalineCommonDefaults.EuCookieLawAcceptedAttribute, true, storeid);
            _httpContextAccessor.HttpContext.Session.SetString($"{RetalineCookieDefaults.Prefix}{RetalineCookieDefaults.IgnoreEuCookieLawWarning}", "true");
            //TempData[$"{RetalineCookieDefaults.Prefix}{RetalineCookieDefaults.IgnoreEuCookieLawWarning}"] = true;
            return Json(new { stored = true });
        }



    }


}
