using Microsoft.Extensions.Configuration;
using ODOCart.Core.BussinessModel.API;
using ODOCart.Core.BussinessModel.UserDetails;
using ODOCart.Core.Services.HelperServices;
using ODOCart.Core.ViewModel.Address;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace ODOCart.Core.Services.ProfileManagement
{
    public class ProfileService : IProfileService
    {
        private static IHttpHelperService _httpHelperService;
        private readonly IConfiguration _configuration;

        public ProfileService(IHttpHelperService httpHelperService,
             IConfiguration configuration)
        {
            _httpHelperService = httpHelperService;
            _configuration = configuration;
        }

        public async Task<ProfileRoot> GetProfile(string url)
        {
            return await _httpHelperService.Get<ProfileRoot>(url, null);

        }

        public async Task<APIModel<List<Address>>> GetAddress()
        {
            string url= _configuration["ApiUrls:ProfileManagement:AddAddresses"].ToString();
            return await _httpHelperService.Get<APIModel<List<Address>>>(url);

        }

        public async Task<APIModel<Address>> ChangePrimaryAddress(int addressId)
        {
            string url = string.Format(_configuration["ApiUrls:ProfileManagement:ChangePrimaryAddress"], addressId); //"http://odocart.api.dev.velosit.in/api/address/"+addressId.ToString()+"/primary";
            var inputParams = new Dictionary<string, int>
            {
                { "id", addressId }
            };
            return await _httpHelperService.Post<APIModel<Address>>(url, inputParams);

        }

        public async Task<object> AddAddress(AddressViewModel details)
        {
            string url = _configuration["ApiUrls:ProfileManagement:AddAddresses"].ToString();
            return await _httpHelperService.Post<object>(url, details);

        }

        public async Task<object> DeleteAddress(int id)
        {
            string url = _configuration["ApiUrls:ProfileManagement:DeleteAddresses"].ToString();
            var inputParams = new Dictionary<string, int>
            {
                { "id", id }
            };
            return await _httpHelperService.Post<object>($"{url}/{id}", inputParams);

        }

        public async Task<APIModel<List<Branch>>> GetBranches(int addressId)
        {
            string url = string.Format(_configuration["ApiUrls:ProfileManagement:Branches"], addressId);
            return await _httpHelperService.Get<APIModel<List<Branch>>>(url);

        }
        public async Task<object> SwitchBranch(int branchId, int deliveryAddressId)
        {
            string url = _configuration["ApiUrls:ProfileManagement:BranchSelect"].ToString();
            var inputParams = new Dictionary<string, int>
            {
                { "branch_id", branchId },
                {"delivery_address_id", deliveryAddressId}
            };
            return await _httpHelperService.Post<object>(url, inputParams);

        }
    }
}
