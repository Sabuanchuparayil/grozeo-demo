using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Retaline.Core.Services.Checkout;
using Retaline.Core.Services.Order;
using Retaline.Web.Models.Checkout;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Linq.Expressions;
using System.Threading.Tasks;

namespace Retaline.Web.Controllers
{
    [Authorize]
    public class CheckoutController : Controller
    {
        private readonly ICheckoutService _checkoutService;
        private readonly IOrderService _orderService;
        private readonly Core.ViewModel.Tenant.AppTenant _tenant;
        private readonly IConfiguration _configuration;
        public CheckoutController(
            ICheckoutService checkoutService,
            IOrderService orderService, IConfiguration configuration,
            SaasKit.Multitenancy.ITenant<Core.ViewModel.Tenant.AppTenant> tenant)
        {
            _checkoutService = checkoutService;
            _orderService = orderService;
            _tenant = tenant?.Value;
            _configuration = configuration;
        }
        public IActionResult Index()
        {
            return View();
        }

        [Route("checkout/clearmemcache")]
        public IActionResult Test()
        {
            Core.Utilities.Common.ClearCache();
            return new JsonResult("success");
        }

        public IActionResult Payment(string url)
        {
            return View("Payment", url);
        }


        [HttpPost]
        public async Task<IActionResult> SubmitOrder(Checkout checkout)
        {
            //if (checkout != null && !String.IsNullOrEmpty(checkout.razorpay_payment_id) && !String.IsNullOrEmpty(checkout.razorpay_signature))
            //{
            //    await _checkoutService.RazorPaymentComplete(checkout.razorpay_payment_id, checkout.razorpay_signature, checkout.rpoid);
            //}

            bool canCheckout = _tenant?.CanCheckout ?? false; // _configuration["CanCheckout"].ToString();
            if (canCheckout && !_tenant.OnlinePaymentEnabled && !_tenant.PODEnabled)
                canCheckout = false;

            if (!canCheckout)
                return null;

            int paymentMethod = (Int32)checkout.PaymentMethod;
            // TODO: PaymentMethod and NetAmount must be verified server-side against the order; do not trust client-submitted values for flow control.

            checkout.TimeSlotMode = DeliveryMode.Soon;
            //int timeSlot = 1
            if (checkout.TimeSlote != "soon")
                checkout.TimeSlotMode = DeliveryMode.Selected;

            int timeSlot = (Int32)checkout.TimeSlotMode;

            //if(result.Data.PaymentMode== "Online" && !String.IsNullOrEmpty(result.Data.PaymentDetails.LongUrl))
            //{
            //    return Redirect(result.Data.PaymentDetails.LongUrl);
            //}

            PlaceOrder placeOrder = new PlaceOrder();
            placeOrder.OrderInfo = new Core.BusinessModel.Cart.Checkout.ConfirmOrder();
            //if (!checkout.IsPaymentResult)
            //{
            if (paymentMethod == 1 || checkout.NetAmount == 0)
            {
                //var paymentResult = await _checkoutService.CheckoutConfirm(new Core.BusinessModel.Cart.Checkout.Customer { CustomerId=checkout.CustomerId, Id=checkout.OrderId, OrderId=checkout.OrderNum, TotalAmount=checkout.NetAmount } , paymentMethod, timeSlot, "", checkout.UseWallet?1:0, String.IsNullOrEmpty(checkout.CouponCode)?0:1);
                Core.BusinessModel.API.APIModel<Core.BusinessModel.Cart.Checkout.ConfirmOrder> paymentResult = await _checkoutService.ConfirmOrder(checkout.OrderGroupId, paymentMethod, timeSlot, 1, "", checkout.UseWallet ? 1 : 0, String.IsNullOrEmpty(checkout.CouponCode) ? 0 : 1);
                if (paymentResult != null && paymentResult.Data != null && paymentResult.Data.PaymentMode == "Online" && !String.IsNullOrEmpty(paymentResult.Data.PaymentDetails.LongUrl))
                {
                    placeOrder.OrderInfo = paymentResult.Data;
                    try { placeOrder.Message = paymentResult.Data.Message; } catch { placeOrder.Message = ""; }

                    //if (!String.IsNullOrEmpty(placeOrder.OrderInfo.PaymentDetails.LongUrl))
                    //{
                    //    return new RedirectResult(placeOrder.OrderInfo.PaymentDetails.LongUrl);
                    //}
                }
            }
            //var checkoutInfo = await _cartService.CheckoutInfo();

            //}
            //else
            //{
            int orderId = checkout.OrderId; //checkoutInfo.customer.Id;
            string orderNum = checkout.OrderNum; //checkoutInfo.customer.OrderId;
            string orderGroupId = "";
            if (orderId < 1)
                orderId = checkout.OrderId;
            if (String.IsNullOrEmpty(orderNum))
                orderNum = checkout.OrderNum;

            Core.BusinessModel.API.APIModel<Core.BusinessModel.Order.Order> orderResult = await _orderService.GetOrderDetails(orderNum);//(orderId);
            Core.BusinessModel.Order.Order orderData = (orderResult == null || orderResult.Data == null ? null : orderResult.Data);
            if (orderData == null)
            {
                return View("ThankYou", placeOrder);
            }

            //try { placeOrder.OrderStatus = orderResult.Data.Status.Status; } catch { }
            //try { orderGroupId = orderResult.Data.OrderGroupId; } catch { }
            //try { placeOrder.StatusCode = orderResult.Data.Status.StatusId ?? -1; } catch { }
            //try { placeOrder.CurAPITime = orderResult.Data.CurAPITime; } catch { }
            //try { placeOrder.CancelTime = orderResult.Data.CancelTime; } catch { }
            //try
            //{
            //    placeOrder.OrderBranchName = orderResult.Data.BranchName;
            //    placeOrder.OrderBranchLocation = orderResult.Data.BranchLocation;
            //    placeOrder.OrderDate = orderResult.Data.OrderDateTime;
            //}
            //catch { }
            //var paymentInfo = await _orderService.PaymentStatus(orderId, orderNum);
            //if (paymentInfo != null && paymentInfo.Data != null)
            //    placeOrder.PaymentStatus = paymentInfo.Data.Status;

            ////}

            //placeOrder.OrderId = orderId;
            //placeOrder.OrderNum = orderNum;
            //switch (orderResult.Data.PaymentModeVal)
            //{
            //    case 1:
            //        placeOrder.PaymentMethod = "Pay On Delivery";
            //        break;
            //    case 2:
            //        placeOrder.PaymentMethod = "Online";
            //        break;
            //    case 3:
            //        placeOrder.PaymentMethod = "Wallet";
            //        break;
            //    case 4:
            //        placeOrder.PaymentMethod = "Wallet and Pay On Delivery";
            //        break;
            //    case 5:
            //        placeOrder.PaymentMethod = "Wallet and Online";
            //        break;
            //    default:
            //        placeOrder.PaymentMethod = (paymentMethod == 1 ? "Pay On Delivery" : "Online");
            //        break;
            //}
            //try { placeOrder.SubTotal = orderResult.Data.SubTotal; } catch { }
            //try { placeOrder.Total = orderResult.Data.OrderTotal; } catch { }
            //try { placeOrder.DeliveryCharge = orderResult.Data.ShippingCharge; } catch { }
            Core.BusinessModel.API.APIModel<List<Core.BusinessModel.Order.MyOrder>> successOrder = await _orderService.GetGroupOrderDetails(checkout.OrderGroupId);
            placeOrder.SuccessOrders=successOrder.Data==null ? new List<Retaline.Core.BusinessModel.Order.MyOrder>() : successOrder.Data;
            placeOrder = BindOrderInfo(orderData, placeOrder, paymentMethod);
            
            return View("ThankYou", placeOrder);//Json(new { result= 1 });
        }

        [HttpPost]
        public async Task<IActionResult> SubmitCoupon([FromBody] Checkout checkout)//int orderId, string couponCode, int useWallet=0)
        {
            var result = await _checkoutService.ApplyCoupon(checkout.OrderId, checkout.CouponCode, checkout.UseWallet);
            if (result != null && result.Data != null && result.Data.Labels != null && result.Data.Labels.Count > 0)
            {
                result.Data.Labels = result.Data.Labels.OrderByDescending(l => l.Order).ToList();
                return Json(new { result.Data });
            }
            return Json(new { result });
        }
        [HttpPost]
        public async Task<IActionResult> RemoveCoupon([FromBody] Checkout checkout)//int orderId, string couponCode, int useWallet=0)
        {
            var result = await _checkoutService.RemoveCoupon(checkout.OrderId, checkout.CouponCode, checkout.UseWallet);
            if (result != null && result.Data != null && result.Data.Labels != null && result.Data.Labels.Count > 0)
            {
                result.Data.Labels = result.Data.Labels.OrderByDescending(l => l.Order).ToList();
                return Json(new { result.Data });
            }
            return Json(new { result });
        }

        [HttpPost]
        public async Task<IActionResult> UseWallet([FromBody] Checkout checkout)
        {
            var result = await _checkoutService.UseWallet(checkout.OrderId, checkout.UseWallet);
            if (result != null && result.Data != null && result.Data.Labels != null && result.Data.Labels.Count > 0)
            {
                result.Data.Labels = result.Data.Labels.OrderByDescending(l => l.Order).ToList();
                return Json(new { result.Data });
            }
            return Json(new { result });
        }

        [Route("confirm-order/{orderNum}/{orderId}")]
        [Route("confirm-order/{orderNum}")]
        //[HttpPost]
        public async Task<IActionResult> ConfirmOrder(string orderNum, int orderId)
        {

            bool canCheckout = _tenant?.CanCheckout ?? false;
            if (!canCheckout)
                return null;

            PlaceOrder placeOrder = new();

            Core.BusinessModel.API.APIModel<Core.BusinessModel.Order.Order> orderResult = await _orderService.GetOrderDetails(orderNum);
            Core.BusinessModel.Order.Order orderData = (orderResult == null || orderResult.Data == null ? null : orderResult.Data);
            if(orderData == null)
            {
                return View("ThankYou", placeOrder);
            }
            //try { placeOrder.OrderStatus = orderResult.Data.Status.Status; } catch { }
            //try { placeOrder.StatusCode = orderResult.Data.Status.StatusId ?? -1; } catch { }
            //try { placeOrder.CurAPITime = orderResult.Data.CurAPITime; } catch { }
            //try { placeOrder.CancelTime = orderResult.Data.CancelTime; } catch { }
            //try
            //{
            //    placeOrder.OrderBranchName = orderResult.Data.BranchName;
            //    placeOrder.OrderBranchLocation = orderResult.Data.BranchLocation;
            //    placeOrder.OrderDate = orderResult.Data.OrderDateTime;
            //}
            //catch { }

            //var paymentInfo = await _orderService.PaymentStatus(orderId, orderNum);
            //if (paymentInfo != null && paymentInfo.Data != null)
            //{
            //    placeOrder.PaymentStatus = paymentInfo.Data.Status;
            //    if (paymentInfo.Data.Payments != null && paymentInfo.Data.Payments.Length > 0)
            //        placeOrder.Payments = paymentInfo.Data.Payments.Select(p => p.Status).ToList();
            //} 
            //placeOrder.OrderId = orderId;
            //placeOrder.OrderNum = orderNum;
            //try { placeOrder.SubTotal = orderResult.Data.SubTotal; } catch { }
            //try { placeOrder.Total = orderResult.Data.OrderTotal; } catch { }
            //try { placeOrder.DeliveryCharge = orderResult.Data.ShippingCharge; } catch { }
            var successOrder = await _orderService.GetGroupOrderDetails(orderData.OrderGroupId);
            placeOrder.SuccessOrders=successOrder.Data==null ? new List<Core.BusinessModel.Order.MyOrder>() : successOrder.Data;
            placeOrder = BindOrderInfo(orderData, placeOrder, 1);
            return View("ThankYou", placeOrder);
        }

        private PlaceOrder BindOrderInfo(Core.BusinessModel.Order.Order order, PlaceOrder orderModel, int paymentMethod)
        {
            if (order == null && (orderModel.SuccessOrders == null || orderModel.SuccessOrders.Count <= 0))
                return orderModel;
            if(order == null)
            {
                order = orderModel.SuccessOrders.Select(o => new Core.BusinessModel.Order.Order() { ShippingCharge=o.DeliveryCharge.ToString(),
                 Status = new Core.BusinessModel.Order.OrderStatus() { Status = o.Status, StatusId=o.StatusId }, CurAPITime = o.CurAPITime, CancelTime = o.CancelTime,
                 BranchName = o.BranchName, BranchLocation = o.BranchLocation, OrderDateTime = o.CreatedOn, PrimaryKey=o.OrderId, OrderId=o.OrderKey,
                 Id = o.OrderId, PaymentModeVal = o.PaymentMode, SubTotal=o.SubTotal.ToString(), OrderTotal=o.OrderTotal.ToString()
                }).FirstOrDefault();
            }

            try { orderModel.OrderStatus = order.Status.Status; } catch { }
            try { orderModel.OrderGroupId = order.OrderGroupId; } catch { }
            try { orderModel.StatusCode = order.Status.StatusId ?? -1; } catch { }
            try { orderModel.CurAPITime = order.CurAPITime; } catch { }
            try { orderModel.CancelTime = order.CancelTime; } catch { }
            try
            {
                orderModel.OrderBranchName = order.BranchName;
                orderModel.OrderBranchLocation = order.BranchLocation;
                orderModel.OrderDate = order.OrderDateTime;
            }
            catch { }
            try {orderModel.PaymentStatus = order.PaymentStatus; } catch { }
            try
            {
                var paymentInfo = _orderService.PaymentStatus(order.PrimaryKey.Value, order.OrderId).Result;
                if (paymentInfo != null && paymentInfo.Data != null)
                    orderModel.PaymentStatus = paymentInfo.Data.Status;
            }
            catch
            {
            }
            //}

            orderModel.OrderId = (order.Id <= 0 && order.PrimaryKey.Value > 0 ? order.PrimaryKey.Value : order.Id);
            orderModel.OrderNum = (String.IsNullOrEmpty(order.OrderNum) ? order.OrderId : order.OrderNum);
            switch (order.PaymentModeVal)
            {
                case 1:
                    orderModel.PaymentMethod = "Pay On Delivery";
                    break;
                case 2:
                    orderModel.PaymentMethod = "Online";
                    break;
                case 3:
                    orderModel.PaymentMethod = "Wallet";
                    break;
                case 4:
                    orderModel.PaymentMethod = "Wallet and Pay On Delivery";
                    break;
                case 5:
                    orderModel.PaymentMethod = "Wallet and Online";
                    break;
                default:
                    orderModel.PaymentMethod = (paymentMethod == 1 ? "Pay On Delivery" : "Online");
                    break;
            }
            if (orderModel.SuccessOrders != null && orderModel.SuccessOrders.Count > 0)
            {
                try { orderModel.SubTotal = orderModel.SuccessOrders.Sum(o=> o.SubTotal).ToString(); } catch { }
                try { orderModel.Total = orderModel.SuccessOrders.Sum(o => o.OrderTotal).ToString(); } catch { }
                try { orderModel.DeliveryCharge = orderModel.SuccessOrders.Sum(o => o.DeliveryCharge).ToString(); } catch { }
            }
            else
            {
                try { orderModel.SubTotal = order.SubTotal; } catch { }
                try { orderModel.Total = order.OrderTotal; } catch { }
                try { orderModel.DeliveryCharge = order.ShippingCharge; } catch { }
            }

            return orderModel;
        }


        [Route("gatewayresult/{orderNum}/{orderId}")]
        [Route("gatewayresult/{orderNum}")]
        public IActionResult GatewayResult(string orderNum, int orderId)
        {
            //return View("GatewayResult", $"/confirm-order/{orderNum}/{orderId}");
            return View("GatewayResult", $"/confirm-order/{orderNum}");
        }

        [Route("checkout/abortonlinepayment/{orderId}")]
        public async Task<IActionResult> AbortPayment(int orderId)
        {
            var obj = await _checkoutService.AbortOnlinePayment(orderId);
            return new JsonResult(obj.Data);
        }

        public IActionResult TestPage()
        {
            return View();
        }

        [HttpPost]
        [Route("checkout/rsubmit")]
        public async Task<IActionResult> RazorpaySubmit([FromBody] Models.Common.RazorpayResult objRazorpay)//(string rpayid, string rpaysig, string rpoid)
        {
            string result = "fail";
            if (objRazorpay != null && !String.IsNullOrEmpty(objRazorpay.razorpay_payment_id) && !String.IsNullOrEmpty(objRazorpay.razorpay_signature))
            {
                try
                {
                    await _checkoutService.RazorPaymentComplete(objRazorpay.razorpay_payment_id, objRazorpay.razorpay_signature, objRazorpay.razorpay_order_id);
                    result = "success";
                }
                catch (Exception ex)
                {
                    result = "fail - " + ex.Message;
                }
            }

            //var result = await _checkoutService.UseWallet(checkout.OrderId, checkout.UseWallet);
            //if (result != null && result.Data != null && result.Data.Labels != null && result.Data.Labels.Count > 0)
            //{
            //    result.Data.Labels = result.Data.Labels.OrderByDescending(l => l.Order).ToList();
            //    return Json(new { result.Data });
            //}
            //return Json(new { result });
            return Json(new { result });
            //return Json("success");
        }

        [HttpPost]
        [Route("checkout/easybuzzsubmit")]
        public async Task<IActionResult> EasebuzzSubmit([FromBody] object obj)
        {
            string result = "fail";
            if (obj != null)
            {
                try
                {
                    await _checkoutService.EazybuzzComplete(obj);
                    result = "success";
                }
                catch (Exception ex)
                {
                    result = "fail - " + ex.Message;
                }
            }

            //var result = await _checkoutService.UseWallet(checkout.OrderId, checkout.UseWallet);
            //if (result != null && result.Data != null && result.Data.Labels != null && result.Data.Labels.Count > 0)
            //{
            //    result.Data.Labels = result.Data.Labels.OrderByDescending(l => l.Order).ToList();
            //    return Json(new { result.Data });
            //}
            //return Json(new { result });
            return Json(new { result });
            //return Json("success");
        }


        [HttpPost]
        [Route("checkout/setslot")]
        public async Task<IActionResult> SetSlot([FromBody] Models.Checkout.OrderSlot objSlot)
        {
            var result = await _checkoutService.SetOrderSlot(objSlot.OrderId, objSlot.SlotId, objSlot.SlotDate, objSlot.UseWallet);
            return Json(new { result });
            //return Json(new { result });
            //return Json("success");
        }

        [Route("checkout")]
        [HttpPost]
        public async Task<IActionResult> Checkout(Checkout checkout = null)
        {
            string paymentGateway = _configuration["PaymentGateway"];
            string strReturnUrl = "";
            if (paymentGateway.ToLower() == "ccavenue")
                strReturnUrl = $"{Request.Scheme}://{Request.Host}/confirm-order/";// {checkout.OrderNum}/{checkout.OrderId}";

            // Splited orders in array
            var checkoutInfo = await _checkoutService.CartCheckout(new Core.BusinessModel.Cart.Checkout.Customer { CustomerId = checkout.CustomerId, Id = checkout.OrderId, OrderId = checkout.OrderNum, TotalAmount = checkout.NetAmount }, 1, 1, strReturnUrl, checkout.UseWallet ? 1 : 0, String.IsNullOrEmpty(checkout.CouponCode) ? 0 : 1);
            var orders = checkoutInfo.Data.Orders;
            //checkoutInfo.Data.shippingAddress
            CartCheckout cartCheckout = new()
            {
                Orders = orders,//.Data;
                PODEnabled = _tenant?.PODEnabled ?? false,
                CanCheckout = _tenant?.CanCheckout ?? false, //(strCanCheckout == "true");
                EnabledOnlinePayment = _tenant?.OnlinePaymentEnabled ?? false, //(strEnabledOnlinePayment == "true");

                IsPaymentView = false
            };

            if (!cartCheckout.PODEnabled && !cartCheckout.EnabledOnlinePayment)
                cartCheckout.CanCheckout = false;

            var checkoutcustomer = new Core.BusinessModel.Cart.Checkout.Customer
            {
                DeliveryCharge = (orders.Sum(o => o.DeliveryCharge) +
                 orders.Sum(o => o.CourierCharge)),
                KFCAmount = orders.Sum(o => o.KFC),
                RoundOff = orders.Sum(o => o.RoundOff),
                SubTotal = orders.Sum(o => o.SubTotal),
                Total = orders.Where(o => o.DeliveryStatus == 1).Sum(o => o.Total),
                TotalAmount = orders.Sum(o => o.OrderTotal),
                MRP = orders.Sum(o => o.MRP),
                TotalCGST = orders.Sum(o => o.CGST),
                TotalGST = orders.Sum(o => o.TotalGST),
                TotalSGST = orders.Sum(o => o.SGST),
            };

            cartCheckout.CheckoutInfo = new Core.BusinessModel.Cart.Checkout.Checkout
            {
                customer = checkoutcustomer,
                WalletBalance = checkoutInfo.Data.WalletBalance,
                StockAvailable = checkoutInfo.Data.StockAvailable,
                SufficientAvailable = checkoutInfo.Data.SufficientAvailable,
                Message = checkoutInfo.Data.Message, style= checkoutInfo.Data.style, PriceLabels=checkoutInfo.Data.PriceLabels,
                shippingAddress = checkoutInfo.Data.shippingAddress
            };  //checkoutInfo;

            return View(cartCheckout);//(checkoutInfo.Data);
        }

        [HttpPost]
        [Route("checkout/removeorderGroup")]
        public async Task<IActionResult> RemoveorderGroup([FromBody] RemoveOrder orderObj)
        {
          
            if (orderObj == null)
            {
                return BadRequest("OrderSlot object is null");
            }

            var result = await _checkoutService.RemoveorderGroup(orderObj.OrderId);
            return Json(new { result });
        
        }
        [HttpPost]
        [Route("checkout/addOrderNote")]
        public async Task<IActionResult> AddOrderNote([FromBody] DeliveryNote apiData)
        {
            //API can be used to add and update notes in orders

            if (apiData == null)
            {
                return BadRequest("apiData object is null");
            }

            var result = await _checkoutService.AddOrderNote(apiData.OrderId, apiData.Note);
            return Json(new { result });

        }
    }
}
