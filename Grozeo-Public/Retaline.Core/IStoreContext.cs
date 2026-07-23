using System.Threading.Tasks;
using Retaline.Core.ViewModel.Tenant;

namespace Retaline.Core
{
    /// <summary>
    /// Store context
    /// </summary>
    public interface IStoreContext
    {
        /// <summary>
        /// Gets the current tenant
        /// </summary>
        /// <returns>A task that represents the asynchronous operation</returns>
        Task<AppTenant> GetCurrentTenantAsync();

        /// <summary>
        /// Gets the current tenant
        /// </summary>
        AppTenant GetCurrentTenant();

        /// <summary>
        /// Gets active tenant scope configuration
        /// </summary>
        /// <returns>A task that represents the asynchronous operation</returns>
        Task<int> GetActiveTenantScopeConfigurationAsync();
    }
}
