using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;
using Retaline.Core.BusinessModel.Logging;
using Retaline.Core.BusinessModel.UserDetails;
using Retaline.Core.Services.Authentication;
using Retaline.Core.Services.ProfileManagement;
using Retaline.Core.ViewModel.Address;
using Retaline.Web.Models;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Controllers
{
    [Authorize]
    public class AddressController : Controller
    {
        private readonly ILogger<AddressController> _logger;
        private readonly IConfiguration _configuration;
        private readonly IHttpContextAccessor _httpContextAccessor;
        private readonly IProfileService _profileService;
        private readonly ICustomAuthenticationService _authenticationService;
        private readonly Core.ViewModel.Tenant.AppTenant tenant;

        public AddressController(ILogger<AddressController> logger,
            IConfiguration configuration,
            IHttpContextAccessor httpContextAccessor,
            IProfileService profileService, ICustomAuthenticationService authenticationService,
            SaasKit.Multitenancy.ITenant<Core.ViewModel.Tenant.AppTenant> tenant
         )
        {
            _logger = logger;
            _configuration = configuration;
            _httpContextAccessor = httpContextAccessor;
            _profileService = profileService;
            _authenticationService = authenticationService;
            this.tenant = tenant?.Value;
        }
        public async Task<IActionResult> Index()
        {
            //string profileUrl = _configuration["ApiUrls:ProfileManagement:GetAddresses"].ToString();
            //var userDataJson = _httpContextAccessor.HttpContext.User.Claims.FirstOrDefault(item => item.Type == ClaimTypes.UserData).Value;
            //var user = JsonConvert.DeserializeObject<User>(userDataJson);
            var details = await _profileService.GetAddress();
            //return View(details.Data.OrderByDescending(a => a.IsPrimary).ToList());

            if (details != null && details.Data != null)
                return View(details.Data.OrderByDescending(a => a.IsPrimary).ToList());

            return View((new List< Core.BusinessModel.UserDetails.Address>()));

        }

        [AllowAnonymous]
        [HttpPost]
        public async Task<IActionResult> AddAddress([FromBody] AddressViewModel details)
        {
            if (string.IsNullOrEmpty(details.DeliDeliveryPin))
                details.DeliDeliveryPin = "0";

            if (_httpContextAccessor.HttpContext.User.Identity.IsAuthenticated)
            {
                var user = _authenticationService.GetUserFromClaims();
                try
                {
                    if(string.IsNullOrEmpty(details.DeliName))
                        details.DeliName = user.Name;
                    if(string.IsNullOrEmpty(details.DeliHouseNumber))
                        details.DeliContactNo = user.Mobile;
                    if (string.IsNullOrEmpty(details.DeliType))
                        details.DeliType = "Home";
                }
                catch { }
                //try
                //{
                //    if (tenant != null && !string.IsNullOrEmpty(tenant.StoreId))
                //        details.BranchGroup = int.Parse(tenant.StoreId);
                //}
                //catch { }
                var result = await _profileService.AddAddress(details);
                if (user != null)
                {
                    try
                    {
                        var addresses = _profileService.GetAddress().Result;
                        user.AddressCount = addresses.Data.Count;
                        user.PrimaryAddress = addresses.Data.Where(a => a.IsPrimary == 1).FirstOrDefault();
                        if (user.PrimaryAddress != null && user.PrimaryAddress.Latitude > 0 && user.PrimaryAddress.Longitude > 0)
                        {
                            _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLAT", user.PrimaryAddress.Latitude.ToString());
                            _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLNG", user.PrimaryAddress.Longitude.ToString());
                        }

                        await _authenticationService.CreateAuthenticationTicket(user);
                    }
                    catch { }
                }
                return Json(result);
            }
            else
            {
                string refCode = _httpContextAccessor.HttpContext.Session.GetString("SIGNUPREFCODE");
                var result = await _authenticationService.SignUp(details, _configuration["ApiUrls:Authentication:SignUp"], refCode);
                var addressCount = -1;
                string _status = "";
                if (result != null && !string.IsNullOrEmpty(result.Status))
                {
                    _status = result.Status;
                    await _authenticationService.CreateAuthenticationTicket(result.Data);
                    //string profileUrl = _configuration["ApiUrls:ProfileManagement:GetAddresses"].ToString();
                    try
                    {
                        var addressess = await _profileService.GetAddress();
                        addressCount = addressess.Data.Count;

                        var adr= addressess?.Data?.Where(a=> a.IsPrimary > 0).FirstOrDefault();
                        if (adr != null && adr.Latitude > 0 && adr.Longitude > 0)
                        {
                            _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLAT", adr.Latitude.ToString());
                            _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLNG", adr.Longitude.ToString());
                        }

                    }
                    catch { }
                }
                return Json(new { status = _status, addressCount = addressCount });
            }
        }

        [HttpPost]
        public async Task<IActionResult> ChangeAddress([FromBody] Models.Address addressId)
        {

            //string profileUrl = _configuration["ApiUrls:ProfileManagement:GetAddresses"].ToString();
            var adr = await _profileService.ChangePrimaryAddress(addressId.AddressId);

            if (adr != null && adr.Id== addressId.AddressId)
            {
                var user = _authenticationService.GetUserFromClaims();
                user.BranchId = adr.BranchId ?? default;
                user.PrimaryAddress = adr;
                if(adr != null && adr.Latitude > 0 && adr.Longitude > 0)
                {
                    _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLAT", adr.Latitude.ToString());
                    _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLNG", adr.Longitude.ToString());
                }
                await _authenticationService.CreateAuthenticationTicket(user);
            }

            //var details = await _profileService.GetAddress();
            //if(details != null && details.Data != null)
            //    return View("Index", details.Data.OrderByDescending(a=> a.IsPrimary).ToList());
            return Json(new { result = "Success" });
            //return View("Index", new List<Address>());
        }


        [HttpPost]
        public async Task<IActionResult> DeleteAddress([FromBody] Models.Address addressId)
        {
            var result = await _profileService.DeleteAddress(addressId.AddressId);
            return Json(new { result = "Success" });
        }

        [Route("branches")]
        public async Task<IActionResult> Branches()
        {
            var user = _authenticationService.GetUserFromClaims();
            //var result = await _profileService.GetBranches(user.PrimaryAddress.Id);
            var result = await _profileService.GetNearestRetailers(user.PrimaryAddress.Latitude, user.PrimaryAddress.Longitude);
            return Json(result.Data);
        }

        [Route("switchbranch")]
        [HttpPost]
        public async Task<IActionResult> SwitchBranch([FromBody] Branch branch)
        {
            var user = _authenticationService.GetUserFromClaims();
            var result = await _profileService.SwitchBranch(branch.Id, user.PrimaryAddress.Id);
            return Json(1);
        }
        [AllowAnonymous]
        [Route("getAddrStates")]
        public async Task<IActionResult> getAddrStates([FromBody] string countryId)
        {

            var result = await _profileService.getAddrStates(countryId);
             return Json(result);
        }
        [AllowAnonymous]
        [Route("getDistricts")]
        public async Task<IActionResult> getDistricts([FromBody] string selectedStateId)
        {
            var result = await _profileService.getDistrictsWithStateId(selectedStateId);
            return Json(result);
      
        }

    }


}
