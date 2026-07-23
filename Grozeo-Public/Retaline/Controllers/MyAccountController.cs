using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.CodeAnalysis.CSharp;
using Retaline.Core.BusinessModel.Order;
using Retaline.Core.BusinessModel.UserDetails;
using Retaline.Core.Services.HelperServices;
using Retaline.Core.Services.Order;
using Retaline.Core.Services.Wishlist;
using Retaline.Core.ViewModel.Product;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Retaline.Core.BusinessModel.Catalog;

namespace Retaline.Web.Controllers
{
    [Authorize]
    public class MyAccountController : Controller
    {
        private readonly IOrderService _orderService;
        private readonly IWishlistService _wishlistService;
        private readonly IHttpHelperService _httpHelper;


        public MyAccountController(IOrderService orderService, IWishlistService wishlistService, IHttpHelperService httpHelper)
        {
            _orderService = orderService;
            _wishlistService = wishlistService;
            _httpHelper = httpHelper;

        }

        [Route("account")]
        public async Task<IActionResult> Index()
        {

            Models.Order.MyOrders myOrders = new();
            var successOrder = await _orderService.GetOrderHistory(true);
            myOrders.SuccessOrders = successOrder.Data;
            var failedOrders = await _orderService.GetOrderHistory(false);
            myOrders.FailedOrders = failedOrders.Data;
            return View(myOrders);
        }

        [Route("orderdetails/{orderid}")]
        public async Task<IActionResult> OrderDetails(string orderid)
        {
            var orderHistory = await _orderService.GetOrderDetails(orderid);

            return View(orderHistory.Data);

        }
        [Route("orderstatus/{orderid}")]
        public async Task<IActionResult> OrderStatus(string orderid)//(int orderid)
        {
            var orderHistory = await _orderService.GetOrderDetails(orderid);
            if (orderHistory != null && orderHistory.Data != null && orderHistory.Data.Status != null)
            {
                return Json(orderHistory.Data.Status);
            }
            return null;

        }
        [Route("mywallet")]
        public async Task<IActionResult> Wallet()
        {
            var walletResult = await _orderService.MyWalletInfo("", "");
            return View(walletResult.Data);

        }

        [HttpPost]
        [Route("mywallet")]
        public async Task<IActionResult> Wallet(Models.MyAccount.Wallet wallet = null)//(string fromDate = "", string toDate = "")//([FromBody] Retaline.Web.Models.MyAccount.Wallet wallet = null)
        {
            string fromDt = "", toDt = "";
            if (wallet != null)
            {
                if(!string.IsNullOrEmpty(wallet.fromDate))
                    try { fromDt = Convert.ToDateTime(wallet.fromDate).ToString("yyyy-MM-dd"); } catch { fromDt = ""; }
                if (!string.IsNullOrEmpty(wallet.toDate))
                    try { toDt = Convert.ToDateTime(wallet.toDate).ToString("yyyy-MM-dd"); } catch { toDt = ""; }
            }
            var walletResult = await _orderService.MyWalletInfo(fromDt, toDt);

            if(walletResult != null && walletResult.Data != null)
            {
                Core.BusinessModel.UserDetails.Wallet result = walletResult.Data;
                if (wallet != null)
                {
                    result.FromDate = wallet.fromDate; result.ToDate = wallet.toDate;
                }
                return View(result);
            }
            return View(new Core.BusinessModel.UserDetails.Wallet());

        }
        [HttpGet]
        [Route("wishlist")]
        public async Task<IActionResult> WishList()
        {
            var details = new List<Product>(); try
            {
                details = await _wishlistService.GetWishlist();

                }
            catch (Exception ex)
            {
                return Json(new { status = 0, message = ex.Message});
            }
            return View("Wishlist", details);
        }

        [Route("branch")]
        public IActionResult Branch()
        {
            return View();
        }

        [HttpPost]
        [Route("cancel-order/{orderId}")]
        public async Task<IActionResult> CancelOrder(long orderId)
        {
            var result = await _orderService.CancelOrder(orderId);
            return Json(result);
        }
        [Route("orderreturanables/{orderid}")]
        public async Task<IActionResult> OrderReturanables(string orderid)
        {
            var orderHistory = await _orderService.GetReturnables(orderid);
            if (orderHistory != null && orderHistory.Data != null)
            {
                return Json(orderHistory.Data);
            }
            return null;

        }
        [HttpPost]
        [Route("orderreturanables/return")]
        public async Task<IActionResult> ReturnItems([FromBody] List<OrderItem> orderItems)
        {
            try
            {
                if (orderItems == null || orderItems.Count <= 0)
                    return Json(new { status = 0, message = "Invalid items selected" });

                string orderId = orderItems[0].OrderId;
                if(String.IsNullOrEmpty(orderId))
                    return Json(new { status = 0, message = "Invalid order" });

                var result = await _orderService.ReturnItems(orderId, orderItems);
                if(result == null || result.Status == "error")
                    return Json(new { status = 0, message = (result != null && !String.IsNullOrEmpty(result.Message) ? result.Message : (result.Failure != null && !String.IsNullOrEmpty(result.Failure.msg)? result.Failure.msg :"Failure! There is a technical error occurred.")) });
                return Json(new { status = 1, message = result.Message, content = result.Message });
            }
            catch(Exception ex) {
                return Json(new { status = 0, message = ex.Message, content = ex.Message });
            }
        }

        [Route("order/invoice/{ordernum}")]
        public async Task<IActionResult> ViewInvoice(string ordernum)
        {
            var orderHistory = await _orderService.GetOrderDetails(ordernum);
            if (orderHistory != null && orderHistory.Data != null && orderHistory.Data.Status != null)
            {
                try
                {
                    int orderId = Convert.ToInt32(orderHistory.Data.PrimaryKey);
                    if (orderId <= 0)
                        throw new Exception("Invalid order");

                    string email = User.Identity.Name;
                    var invoiceData = await _orderService.GetInvoiceDetails(orderId, email, email);
                    //return Json(new { status = 1, message = "Success", content = invoiceData.Data });
                    return View("Invoice", invoiceData);
                }
                catch { }
            }
            return View("Invoice");
            //return Json(new { status = 0, message = "Failure", content = "Sorry! the operation failed or invalid request or you don't have access. Please contact support for more details" });

        }
        [AllowAnonymous]

        [Route("guest/update")]

        public async Task<IActionResult> SaveGuestUserCookie([FromBody] GuestData guestUser)
        {

            if (guestUser == null)
            {
                return BadRequest("Invalid guest user data.");
            }

            try
            {
                double guestLat = Convert.ToDouble(guestUser.GuestLatitude);
                double guestLng = Convert.ToDouble(guestUser.GuestLongitude);
                string guestLocality = guestUser.GuestLocality;
                _httpHelper.SetGuestLocation(guestLat, guestLng, guestLocality);

            }
            catch
            {
                
                return BadRequest("Invalid guest user data.");
            }

            return Ok(new { success = true });
        }

    }
}
