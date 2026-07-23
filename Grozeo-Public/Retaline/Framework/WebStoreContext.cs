using System;
using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Http;
using Microsoft.Net.Http.Headers;
using Retaline.Core;
using Retaline.Core.ViewModel.Tenant;

namespace Retaline.Web.Framework
{
    /// <summary>
    /// Store context for web application
    /// </summary>
    public partial class WebStoreContext : IStoreContext
    {
        #region Fields

        private readonly IHttpContextAccessor _httpContextAccessor;
        private readonly Core.ViewModel.Tenant.AppTenant _tenant;

        private AppTenant _cachedTenant;
        private int? _cachedActiveStoreScopeConfiguration;

        #endregion

        #region Ctor

        /// <summary>
        /// Ctor
        /// </summary>
        /// <param name="genericAttributeService">Generic attribute service</param>
        /// <param name="httpContextAccessor">HTTP context accessor</param>
        /// <param name="storeRepository">Store repository</param>
        /// <param name="storeService">Store service</param>
        public WebStoreContext(IHttpContextAccessor httpContextAccessor,
            SaasKit.Multitenancy.ITenant<Core.ViewModel.Tenant.AppTenant> tenant)
        {
            _httpContextAccessor = httpContextAccessor;
            this._tenant = tenant?.Value;
        }

        #endregion

        #region Properties

        /// <summary>
        /// Gets the current store
        /// </summary>
        /// <returns>A task that represents the asynchronous operation</returns>
        public virtual async Task<AppTenant> GetCurrentTenantAsync()
        {
            if (_cachedTenant != null)
                return _cachedTenant;

            _cachedTenant = _tenant ?? throw new Exception("No tenant could be loaded");

            return _cachedTenant;
        }

        /// <summary>
        /// Gets the current store
        /// </summary>
        public virtual AppTenant GetCurrentTenant()
        {
            if (_cachedTenant != null)
                return _cachedTenant;

            _cachedTenant = _tenant ?? throw new Exception("No store could be loaded");

            return _cachedTenant;
        }

        /// <summary>
        /// Gets active tenant scope configuration
        /// </summary>
        /// <returns>A task that represents the asynchronous operation</returns>
        public virtual async Task<int> GetActiveTenantScopeConfigurationAsync()
        {
            if (_cachedActiveStoreScopeConfiguration.HasValue)
                return _cachedActiveStoreScopeConfiguration.Value;

            //do not inject IWorkContext via constructor because it'll cause circular references
            //var currentCustomer = await Core.Infra.EngineContext.Current.Resolve<IWorkContext>().GetCurrentCustomerAsync();


            return 0;
        }

        #endregion
    }
}