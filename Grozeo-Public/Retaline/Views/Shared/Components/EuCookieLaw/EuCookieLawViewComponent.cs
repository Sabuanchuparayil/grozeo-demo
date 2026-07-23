using System;
using System.Configuration;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Retaline.Core;
using Retaline.Core.Http;
using Retaline.Core.Services.Common;

namespace Retaline.Web.Views.Shared.Components
{
    public partial class EuCookieLawViewComponent : ViewComponent
    {
        private readonly IGenericAttributeService _genericAttributeService;
        //private readonly IStoreContext _storeContext;
        private readonly IWorkContext _workContext;
        private readonly Core.ViewModel.Tenant.AppTenant _tenant;
        private readonly Core.Services.Authentication.ICustomAuthenticationService _customAuthService;
        private readonly IHttpContextAccessor _httpContextAccessor;
        private readonly IConfiguration _configuration;

        public EuCookieLawViewComponent(IGenericAttributeService genericAttributeService, Core.ViewModel.Tenant.AppTenant tenant, IConfiguration configuration,
            Core.Services.Authentication.ICustomAuthenticationService customAuthService, IHttpContextAccessor httpContextAccessor)
        {
            _genericAttributeService = genericAttributeService;
            _tenant = tenant;
            _customAuthService = customAuthService;
            _httpContextAccessor = httpContextAccessor;
            _configuration = configuration;
        }

        public async Task<IViewComponentResult> InvokeAsync()
        {
            if(_configuration["DisplayEuCookieLawWarning"] != "true")
            //if (!_storeInformationSettings.DisplayEuCookieLawWarning)
            //disabled
                return Content("");

            //ignore search engines because some pages could be indexed with the EU cookie as description
            var customer = _customAuthService.GetUserFromClaims();

            //if (customer.IsSearchEngineAccount())
            //    return Content("");
            int storeid = 0; try { storeid = Convert.ToInt32(_tenant?.StoreId); } catch { storeid = 0; };
            //var store = await _storeContext.GetCurrentTenantAsync();

            if (customer != null && await _genericAttributeService.GetAttributeAsync<bool>(customer.Id, "Customer", RetalineCommonDefaults.EuCookieLawAcceptedAttribute, storeid))
            {
                _httpContextAccessor.HttpContext.Session.SetString($"{RetalineCookieDefaults.Prefix}{RetalineCookieDefaults.IgnoreEuCookieLawWarning}", "true");
                //TempData[$"{RetalineCookieDefaults.Prefix}{RetalineCookieDefaults.IgnoreEuCookieLawWarning}"] = true;
                //already accepted
                return Content("");
            }

            //ignore notification?
            //right now it's used during logout so popup window is not displayed twice
            //if (TempData[$"{RetalineCookieDefaults.Prefix}{RetalineCookieDefaults.IgnoreEuCookieLawWarning}"] != null && Convert.ToBoolean(TempData[$"{RetalineCookieDefaults.Prefix}{RetalineCookieDefaults.IgnoreEuCookieLawWarning}"]))
            if (!String.IsNullOrEmpty(_httpContextAccessor.HttpContext.Session.GetString($"{RetalineCookieDefaults.Prefix}{RetalineCookieDefaults.IgnoreEuCookieLawWarning}")))
                return Content("");

            return View();
        }
    }
}