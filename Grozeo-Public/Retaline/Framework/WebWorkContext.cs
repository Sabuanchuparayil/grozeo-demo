using System;
using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Localization;
using Retaline.Core;
using Retaline.Core.BusinessModel.UserDetails;
using Retaline.Core.Http;
using Retaline.Core.Infra;
using Retaline.Core.Services.Authentication;
using Retaline.Core.Services.ScheduleTasks;

namespace Retaline.Web.Framework
{
    /// <summary>
    /// Represents work context for web application
    /// </summary>
    public partial class WebWorkContext : IWorkContext
    {
        #region Fields

        private readonly ICustomAuthenticationService _authenticationService;
        //private readonly ICustomerService _customerService;
        private readonly IHttpContextAccessor _httpContextAccessor;
        private readonly IStoreContext _storeContext;
        private readonly IWebHelper _webHelper;

        private User _cachedCustomer;
        private User _originalCustomerIfImpersonated;

        #endregion

        #region Ctor

        public WebWorkContext(
            ICustomAuthenticationService authenticationService,
            IHttpContextAccessor httpContextAccessor,
            IStoreContext storeContext,
            IWebHelper webHelper)
        {
            _authenticationService = authenticationService;
            _httpContextAccessor = httpContextAccessor;
            _storeContext = storeContext;

            _webHelper = webHelper;
        }

        #endregion

        #region Utilities

        /// <summary>
        /// Get customer cookie
        /// </summary>
        /// <returns>String value of cookie</returns>
        protected virtual string GetCustomerCookie()
        {
            var cookieName = $"{RetalineCookieDefaults.Prefix}{RetalineCookieDefaults.CustomerCookie}";
            return _httpContextAccessor.HttpContext?.Request?.Cookies[cookieName];
        }

        /// <summary>
        /// Set customer cookie
        /// </summary>
        /// <param name="customerGuid">Guid of the customer</param>
        protected virtual void SetCustomerCookie(int customerId)//(Guid customerGuid)
        {
            if (_httpContextAccessor.HttpContext?.Response?.HasStarted ?? true)
                return;

            //delete current cookie value
            var cookieName = $"{RetalineCookieDefaults.Prefix}{RetalineCookieDefaults.CustomerCookie}";
            _httpContextAccessor.HttpContext.Response.Cookies.Delete(cookieName);

            //get date of cookie expiration
            var cookieExpires = RetalineCookieDefaults.CustomerCookieExpires;
            var cookieExpiresDate = DateTime.Now.AddHours(cookieExpires);

            //if passed guid is empty set cookie as expired
            //if (customerGuid == Guid.Empty)
            if(customerId <= 0)
                cookieExpiresDate = DateTime.Now.AddMonths(-1);

            //set new cookie value
            var options = new CookieOptions
            {
                HttpOnly = true,
                Expires = cookieExpiresDate,
                Secure = _webHelper.IsCurrentConnectionSecured()
            };
            _httpContextAccessor.HttpContext.Response.Cookies.Append(cookieName, customerId.ToString(), options);
        }


        #endregion

        #region Properties

        /// <summary>
        /// Gets the current customer
        /// </summary>
        /// <returns>A task that represents the asynchronous operation</returns>
        public virtual async Task<User> GetCurrentCustomerAsync()
        {
            //whether there is a cached value
            if (_cachedCustomer != null)
                return _cachedCustomer;

            await SetCurrentCustomerAsync();

            return _cachedCustomer;
        }

        /// <summary>
        /// Sets the current customer
        /// </summary>
        /// <param name="customer">Current customer</param>
        /// <returns>A task that represents the asynchronous operation</returns>
        public virtual async Task SetCurrentCustomerAsync(User customer = null)
        {
            if (customer == null)
            {
                //check whether request is made by a background (schedule) task
                //if (_httpContextAccessor.HttpContext?.Request
                //    ?.Path.Equals(new PathString($"/{RetalineTaskDefaults.ScheduleTaskPath}"), StringComparison.InvariantCultureIgnoreCase)
                //    ?? true)
                //{
                //    //in this case return built-in customer record for background task
                //    customer = await _customerService.GetOrCreateBackgroundTaskUserAsync();
                //}

                //if (customer == null)
                //{
                //    //check whether request is made by a search engine, in this case return built-in customer record for search engines
                //    if (_userAgentHelper.IsSearchEngine())
                //        customer = await _customerService.GetOrCreateSearchEngineUserAsync();
                //}

                if (customer == null)
                {
                    //try to get registered user
                    customer = await _authenticationService.GetAuthenticatedCustomerAsync();
                }

                if (customer != null)
                {                    
                    //get impersonate user if required
                    if (!String.IsNullOrEmpty(customer.ImpersonatedOriginalUserData))
                    {
                        var impersonatedCustomer = Newtonsoft.Json.JsonConvert.DeserializeObject<User>(customer.ImpersonatedOriginalUserData);
                        if (impersonatedCustomer != null)
                        {
                            //set impersonated customer
                            _originalCustomerIfImpersonated = impersonatedCustomer;// customer;
                            //customer = impersonatedCustomer;
                        }
                    }
                }

                //if (customer == null)
                //{
                //    //get guest customer
                //    var customerCookie = GetCustomerCookie();
                //    if (Guid.TryParse(customerCookie, out var customerGuid))
                //    {
                //        //get customer from cookie (should not be registered)
                //        var customerByCookie = await _customerService.GetCustomerByGuidAsync(customerGuid);
                //        if (customerByCookie != null && !await _customerService.IsRegisteredAsync(customerByCookie))
                //            customer = customerByCookie;
                //    }
                //}

                //if (customer == null)
                //{
                //    //create guest if not exists
                //    customer = await _customerService.InsertGuestCustomerAsync();
                //}
            }

            if (customer != null)
            {
                //set customer cookie
                SetCustomerCookie(customer.Id);//.CustomerGuid);

                //cache the found customer
                _cachedCustomer = customer;
            }
        }

        /// <summary>
        /// Gets the original customer (in case the current one is impersonated)
        /// </summary>
        public virtual User OriginalCustomerIfImpersonated => _originalCustomerIfImpersonated;

        /// <summary>
        /// Gets or sets value indicating whether we're in admin area
        /// </summary>
        public virtual bool IsAdmin { get; set; }

        #endregion
    }
}