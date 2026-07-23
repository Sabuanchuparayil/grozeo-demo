using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.Cart;
using Retaline.Core.Services.Cart;
using Retaline.Core.Services.Checkout;
using Retaline.Core.ViewModel.Cart;
using System;
using System.Linq;
using System.Threading.Tasks;
using Retaline.Core.Services.Authentication;
using System.Collections.Generic;
using SaasKit.Multitenancy;
using System.Security.Policy;

namespace Retaline.Web.Controllers
{
    //[Authorize("HavingPrimaryAddress")]
    public class CartController : Controller
    {
        private readonly ICartService _cartService;
        private readonly ICheckoutService _checkoutService;
        private readonly IConfiguration _configuration;
        private readonly Core.ViewModel.Tenant.AppTenant _tenant;
        private readonly ICustomAuthenticationService _authenticationService;
        public CartController(ICartService cartService, ICheckoutService checkoutService, IConfiguration configuration
            , SaasKit.Multitenancy.ITenant<Core.ViewModel.Tenant.AppTenant> tenant, ICustomAuthenticationService authenticationService)
        {
            _cartService = cartService;
            _checkoutService = checkoutService;
            _configuration = configuration;
            this._tenant = tenant?.Value;
            _authenticationService = authenticationService;
        }


        [Route("cart")]
        public async Task<IActionResult> Index()
        {
            CartRoot cartDetails = await _cartService.GetCartDetails(); //GetCart();
            if (cartDetails == null)
                return Redirect("/");

            if (cartDetails.Data != null && cartDetails.Data.Cart != null && cartDetails.Data.Cart.Count > 0)
            {
                var prescriptions = await _cartService.GetPriscriptions();
                if (prescriptions != null && prescriptions.Data != null)
                {
                    foreach (var cdata in prescriptions.Data)
                    {
                        var citem = cartDetails.Data.Cart.FirstOrDefault(c => c.Id == cdata.Id);
                        if (citem != null)
                            citem.PrescriptionRequired = true;
                    }
                }
                cartDetails.Data.PriceLabels.ForEach(item =>
                {
                    try
                    {
                        var labels = item.Value.Split(" ");
                        if (labels.Length > 1)
                        {
                            var price = Convert.ToDecimal(labels[1]).ToString("0.00");
                            item.Value = $"{labels[0]} {price}";
                        }

                    }
                    catch { }
                });

            }
            return View(cartDetails.Data);
        }

        [Route("checkout")]
        [Authorize]
        public IActionResult Checkout()
        {
            return new RedirectResult("/cart");
        }



        [Route("paymenturl")]
        [Authorize]
        [HttpPost]
        public async Task<IActionResult> PaymentUrl([FromBody] Models.Checkout.Checkout checkout)
        {
            bool canCheckout = _tenant?.CanCheckout ?? false; //_configuration["CanCheckout"];
            if (!canCheckout)
                return null;

            //var checkoutInfo = await _cartService.CheckoutInfo();
            Models.Checkout.CartCheckout cartCheckout = new Models.Checkout.CartCheckout();
            cartCheckout.IsPaymentView = false;
            //cartCheckout.CheckoutInfo = checkoutInfo.Data;

            if (checkout != null && ((Int32)checkout.PaymentMethod) == 2)
            {
                int paymentMethod = (Int32)checkout.PaymentMethod;
                int timeSlot = (Int32)checkout.TimeSlotMode;
                cartCheckout.IsPaymentView = true;
                //string strReturnUrl = $"{Request.Scheme}://{Request.Host}/confirm-order/{checkout.OrderNum}/{checkout.OrderId}";
                //string strReturnUrl = $"{Request.Scheme}://{Request.Host}/gatewayresult/{checkout.OrderNum}/{checkout.OrderId}";
                string strReturnUrl = "";// $"{Request.Scheme}://{Request.Host}/gatewayresult/";
                //var result = await _checkoutService.CheckoutConfirm(new Core.BusinessModel.Cart.Checkout.Customer { CustomerId = checkout.CustomerId, Id = checkout.OrderId, OrderId = checkout.OrderNum, TotalAmount = checkout.NetAmount }, paymentMethod, timeSlot, strReturnUrl, (checkout.UseWallet?1:0), (String.IsNullOrEmpty( checkout.CouponCode) ?0:1));
                if (checkout.IsPodToOnline == 1)
                {
                    var result = await _checkoutService.GetPaymentUrl(checkout.OrderId);
                    cartCheckout.OrderInfo = result.Data;
                }
                else
                {
                    var result = await _checkoutService.ConfirmOrder(checkout.OrderGroupId, paymentMethod, timeSlot, 1, strReturnUrl, (checkout.UseWallet ? 1 : 0), (String.IsNullOrEmpty(checkout.CouponCode) ? 0 : 1));
                    cartCheckout.OrderInfo = result.Data;
                }

                if (cartCheckout != null && cartCheckout.OrderInfo != null && cartCheckout.OrderInfo.PaymentDetails != null)
                {
                    if (cartCheckout.OrderInfo.PaymentGateway.Contains("atom") && String.IsNullOrEmpty(cartCheckout.OrderInfo.PaymentDetails.LongUrl))
                        return null;

                    if (cartCheckout.OrderInfo.PaymentGateway == "razorpay" && cartCheckout.OrderInfo.PaymentDetails != null && this._tenant != null)
                    {
                        cartCheckout.OrderInfo.PaymentDetails.Name = this._tenant.Name;
                        cartCheckout.OrderInfo.PaymentDetails.Theme.Color = this._tenant.CustomColor;
                        if (!String.IsNullOrEmpty(this._tenant.LogoImage))
                            cartCheckout.OrderInfo.PaymentDetails.Image = this._tenant.LogoImage;
                    }

                    Core.BusinessModel.Cart.Checkout.PaymentInfo payment = cartCheckout.OrderInfo.PaymentDetails;
                    if(!String.IsNullOrEmpty(checkout.OrderGroupId))
                        payment.LongUrl= (this._tenant?.APIUrl ?? _configuration["ApiUrls:ApiDomain"]).TrimEnd(new char[]{ '/'}) +"/api/payment/redirect/" + checkout.OrderGroupId;

					string countryCode = _configuration["CountryCode"];
					string currencyCode = _configuration["CurrencySymbol"];
                    if (countryCode == "UK")
                    {
                        countryCode = "GB";
                        currencyCode = "gbp";
                    }
					else if (countryCode == "IN")
					{
						currencyCode = "inr";
					}
					else if (countryCode == "AE" || countryCode == "UAE")
					{
						countryCode = "AE";
						currencyCode = "aed";
					}

					return Json(new { paymentUrl = cartCheckout.OrderInfo.PaymentDetails.LongUrl, gateway = cartCheckout.OrderInfo.PaymentGateway, paymentType = cartCheckout.OrderInfo.PaymentDetails.Id, keyid= cartCheckout.OrderInfo.KeyId, paymentInfo = payment, countryCode = countryCode, currencySymbol = currencyCode });// new RedirectResult(paymentResult.Data.PaymentDetails.LongUrl);
                }
            }

            return null;
        }

        [HttpPost]
        public async Task<IActionResult> AddToCart([FromBody] AddToCartViewModel cartInfo)
        {
            var user = _authenticationService.GetUserFromClaims();
            cartInfo.BranchId = cartInfo.BranchId ?? user.BranchId;
            cartInfo.BranchType = cartInfo.BranchType ?? 1;
            cartInfo.OrderMethod = 1;
            cartInfo.OrderType = 2;
            var cartDetails = await _cartService.AddToCart(cartInfo);
            var cartCount = 0;
            if (cartDetails.Status == "ok")
            {
                var updatedCartDetails = await _cartService.GetCart();
                try
                {
                    cartCount = (updatedCartDetails.Data.Cart ?? updatedCartDetails.Data.CartMini).Count;
                }
                catch { cartCount = 0; }
            }

            //return Json(cartCount);
            return Json(new { cartCount = cartCount, itemid = cartInfo.CartProductId, grp = cartInfo.CartGroupId });
        }

        [HttpPost]
        public async Task<IActionResult> ReplaceItem([FromBody] AddToCartViewModel cartInfo)
        {
            cartInfo.OrderMethod = 1;
            cartInfo.OrderType = 2;
            await _cartService.ReplaceItem(cartInfo);
            return Json(new { status = 1 });
        }

        [HttpPost]
        public async Task<IActionResult> UpdateCart([FromBody] CartProductIdDetails cartItem)
        {
            cartItem.OrderMethod = "1";
            var cartDetails = await _cartService.UpdateCart(cartItem);
            var cartCount = -1;
            var cartItems = new List<CartDetails>();
            if (cartDetails.Status == "ok")
            {
                try
                {
                    var updatedCartDetails = await _cartService.GetCart();
                    cartItems = updatedCartDetails.Data.Cart ?? updatedCartDetails.Data.CartMini;
                    cartCount = (cartItems != null ? cartItems.Count : 0); // updatedCartDetails.Data.Cart.Count;
                }
                catch { }
            }
            return Json(new { itemid = cartItem.CartProductId, qty = cartItem.CartOrderQty, cartCount, cartItems });
        }

        [HttpPost]
        public async Task<IActionResult> DeleteCartItem([FromBody] AddToCartViewModel details)
        {
            var cartDetails = await _cartService.DeleteCartItem(details.CartProductId);
            return Json(cartDetails);
        }
        [HttpPost]
        [Authorize]
        public async Task<IActionResult> ClearCart()
        {
            var cartDetails = await _cartService.ClearCart();
            return Json(cartDetails);
        }

        [Route("cartcount/{minify?}")]
        [AllowAnonymous]
        public async Task<IActionResult> CartCount(int minify = 1)
        {
            CartViewModel cart = new();
            if(true) //(User.Identity.IsAuthenticated)
            {
                try
                {
                    CartRoot details = new CartRoot();
                    List<CartDetails> cartDetails = new List<CartDetails>();

                    if (minify > 0)
                    {
                        details = await _cartService.GetCartCount(minify);
                        if (details != null && details.Data != null && details.Data.CartMini != null)
                            cartDetails = details.Data.CartMini;
                    }
                    else
                    {
                        details = await _cartService.GetCartDetails();
                        if (details != null && details.Data != null && details.Data.Cart != null)
                            cartDetails = details.Data.Cart;
                    }

                    if (cartDetails != null && cartDetails.Count > 0)
                    {
                        cart.TotalItems = cartDetails.Count;
                        cart.CartId = cartDetails[0].Id;
                        cart.ActualPrice = Math.Round(cartDetails.Where(item => item.CartRetailPrice.HasValue).Sum(item => item.CartRetailPrice.Value * item.CartOrderQty.Value), 2);
                        cart.SalesPrice = Math.Round(cartDetails.Where(item => item.CartSalesPrice.HasValue).Sum(item => item.CartSalesPrice.Value * item.CartOrderQty.Value), 2);
                        cart.TotalDiscount = Math.Round(cart.ActualPrice - cart.SalesPrice, 2);
                        if (minify == 0)
                        {
                            var firstTwoLineItems = cartDetails.Take(2).ToList();
                            firstTwoLineItems.ForEach(item =>
                            {
                                CartItemMaster cmItem = item.Item.ItemMaster.Where(i => i.StitID == item.CartProductId).FirstOrDefault();
                                CartItemViewModel cartItem = new()
                                {
                                    ProductId = item.CartProductId,
                                    GroupId = item.CartGroupId,
                                    ProductName = (cmItem != null ? cmItem.SKU : item.Item.ItemName),
                                    CartOrderQty = item.CartOrderQty.ToString()
                                };
                                if(cmItem != null && cmItem.MainImage != null && cmItem.MainImage.Count > 0)
                                    cartItem.ProductImage = cmItem.MainImage[0].ImageUrl;

                                cart.CartItems.Add(cartItem);

                            });

                            if (cartDetails.Count > 2)
                            {
                                cart.RemainingItems = cartDetails.Count - 2;
                            }
                        }
                    }

                }
                catch (Exception ex)
                {
                    cart = new CartViewModel();
                }
            }
            return Json(cart);
        }

        public async Task<IActionResult> CartSummary()
        {
            try
            {
                CartRoot cartDetails = await _cartService.GetCartDetails();
                if (cartDetails != null && cartDetails.Data != null && cartDetails.Data.PriceLabels != null)
                {
                    string countryCode = _configuration["CountryCode"];
                    if (countryCode !="IN")
                    {
                        string currencySymbol = _configuration["CurrencySymbol"];
                        cartDetails.Data.PriceLabels.ForEach(item =>
                        {
                            item.Value =item.Value.Replace("₹", currencySymbol);
                        });
                    }

                    return Json(new { cartDetails.Data.Cart, cartDetails.Data.PriceLabels });
                }

            }
            catch
            {
                //cartCount = -1;
            }
            return Json(null);
        }

    }
}
