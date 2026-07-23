using System.Collections.ObjectModel;
using Retaline.Core.ViewModel.Tenant;

namespace Retaline.Core.ViewModel.Tenant
{
    public class MultitenancyOptions
    {
        public Collection<AppTenant> Tenants { get; set; }
    }
}
