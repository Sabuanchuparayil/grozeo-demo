using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.API;
using Retaline.Core.BusinessModel.Store;
using Retaline.Core.BusinessModel.UserDetails;
using Retaline.Core.Services.HelperServices;
using Retaline.Core.ViewModel.Address;
using System.Collections.Generic;
using System.Threading.Tasks;

namespace Retaline.Core.Services.ProfileManagement
{
    public class ProfileService : IProfileService
    {
        private readonly IHttpHelperService _httpHelperService;
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

        public async Task<Address> ChangePrimaryAddress(int addressId)
        {
            string url = string.Format(_configuration["ApiUrls:ProfileManagement:ChangePrimaryAddress"], addressId); //"http://odocart.api.dev.velosit.in/api/address/"+addressId.ToString()+"/primary";
            var inputParams = new Dictionary<string, int>
            {
                { "id", addressId }
            };

            var adrList = await _httpHelperService.Put<APIModel<List<Address>>>(url, inputParams);
            return adrList.Data.Find(a => a.IsPrimary == 1 || a.Id == addressId); //await _httpHelperService.Put<APIModel<Address>>(url, inputParams);

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
            return await _httpHelperService.Delete<object>($"{url}/{id}", inputParams);

        }

        public async Task<APIModel<List<Branch>>> GetBranches(int addressId)
        {
            string url = string.Format(_configuration["ApiUrls:ProfileManagement:Branches"], addressId);
            return await _httpHelperService.Get<APIModel<List<Branch>>>(url);

        }

        public async Task<APIModel<List<Store>>> GetNearestRetailers(double lat, double lng)
        {
            string url = _configuration["ApiUrls:ProfileManagement:NearestRetailer"].ToString();
            var inputParams = new Dictionary<string, double>
            {
                { "latitude", lat },
                { "longitude", lng }
            };
            return await _httpHelperService.Post<APIModel<List<Store>>>(url, inputParams);
        }

        public async Task<APIModel<PagedResult<List<StoreGroup>>>> GetNearestStores(double lat, double lng, int rcId = 0, int page = 1)
        {
            string url = _configuration["ApiUrls:Home:NearStores"].ToString();
            var inputParams = new Dictionary<string, double>
            {
                { "latitude", lat },
                { "longitude", lng },
                { "page", page },
                { "retail_category" ,rcId }
            };
            return await _httpHelperService.Post<APIModel<PagedResult<List<StoreGroup>>>>(url, inputParams);
        }
        public async Task<APIModel<PagedResult<List<Store>>>> GetNearestBranches(double lat, double lng, int page = 1, int defaultBranchId=-1)
        {
            string url = _configuration["ApiUrls:Home:NearBranches"].ToString();
            var inputParams = new Dictionary<string, double>
            {
                { "latitude", lat },
                { "longitude", lng },
                {"page", page }
            };
            return await _httpHelperService.Post<APIModel<PagedResult<List<Store>>>>(url, inputParams, customBranchId: defaultBranchId);
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

        public async Task<object> getAddrStates(string countryCode) //1 India
        {
            string url = _configuration["ApiUrls:ProfileManagement:getAddrStates"].ToString() + countryCode;
            return await _httpHelperService.Get<APIModel<List<object>>>(url);
           

        }
        public async Task<object> getDistrictsWithStateId(string selectedStateId) 
        {
            string url = _configuration["ApiUrls:ProfileManagement:getDistricts"].ToString() + selectedStateId;
            return await _httpHelperService.Get<APIModel<List<object>>>(url);


        }
    }
}
