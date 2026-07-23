using Retaline.Core.BusinessModel.Common;
using Retaline.Core.BusinessModel.Home;
using Retaline.Core.ViewModel.Tenant;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Retaline.Core.Services.HelperServices
{
    public interface IDBService
    {
        Task<Collection<AppTenant>> GetAllTenants();
        Task<IList<GenericAttribute>> GetGenericAttributeFromDB(int entityId, string keyGroup);
        Task<int> SaveGenericAttributeInDB(GenericAttribute attribute);
        Task<IList<RetalinePlugin>> GetTenantPlugins(int tenantId);

	}
}
