using ODOCart.Core.ViewModel.Tenant;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace ODOCart.Core.Services.HelperServices
{
    public interface IDBService
    {
        Task<Collection<AppTenant>> GetAllTenants();
    }
}
