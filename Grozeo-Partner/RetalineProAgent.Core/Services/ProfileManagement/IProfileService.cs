using ODOCart.Core.BussinessModel.API;
using ODOCart.Core.BussinessModel.UserDetails;
using ODOCart.Core.ViewModel.Address;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace ODOCart.Core.Services.ProfileManagement
{
    public interface IProfileService
    {
        Task<ProfileRoot> GetProfile(string url);
        Task<APIModel<List<Address>>> GetAddress();
        Task<APIModel<Address>> ChangePrimaryAddress(int addressId);
        Task<object> AddAddress(AddressViewModel details);
        Task<object> DeleteAddress(int id);
        Task<APIModel<List<Branch>>> GetBranches(int addressId);
        Task<object> SwitchBranch(int branchId, int deliveryAddressId);
    }
}