using Retaline.Core.BusinessModel.API;
using Retaline.Core.BusinessModel.Store;
using Retaline.Core.BusinessModel.UserDetails;
using Retaline.Core.ViewModel.Address;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace Retaline.Core.Services.ProfileManagement
{
    public interface IProfileService
    {
        Task<ProfileRoot> GetProfile(string url);
        Task<APIModel<List<Address>>> GetAddress();
        Task<Address> ChangePrimaryAddress(int addressId);
        Task<object> AddAddress(AddressViewModel details);
        Task<object> DeleteAddress(int id);
        Task<APIModel<List<Branch>>> GetBranches(int addressId);
        Task<object> SwitchBranch(int branchId, int deliveryAddressId);
        Task<APIModel<List<Store>>> GetNearestRetailers(double lat, double lng);
        Task<APIModel<PagedResult<List<StoreGroup>>>> GetNearestStores(double lat, double lng, int rcId = 0, int page = 1);
        Task<APIModel<PagedResult<List<Store>>>> GetNearestBranches(double lat, double lng, int page = 1, int defaultBranchId = -1);
        Task<object> getAddrStates(string countryId);
        Task<object> getDistrictsWithStateId(string selectedStateId);
        

    }
}