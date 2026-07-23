using Microsoft.AspNetCore.Authentication;
using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Configuration;
using Microsoft.IdentityModel.Tokens;
using ODOCart.Core.BussinessModel.API;
using ODOCart.Core.BussinessModel.UserDetails;
using ODOCart.Core.Services.HelperServices;
using ODOCart.Core.Services.ProfileManagement;
using ODOCart.Core.ViewModel.Authentication;
using System;
using System.Collections.Generic;
using System.IdentityModel.Tokens.Jwt;
using System.Linq;
using System.Security.Claims;
using System.Text;
using System.Threading.Tasks;

namespace ODOCart.Core.Services.Authentication
{
    public class CustomAuthenticationService : ICustomAuthenticationService
    {
        private static IHttpHelperService _httpHelperService;
        private readonly IHttpContextAccessor _httpContextAccessor;
        private readonly IConfiguration _configuration;
        private readonly IProfileService _profileService;

        private List<Address> _cachedAddress;

        private User cachedUser = null;
        //private GuestData _guestData;

        public CustomAuthenticationService(IHttpHelperService httpHelperService, IConfiguration configuration,
                     IHttpContextAccessor httpContextAccessor, IProfileService profileService
            )
        {
            _httpHelperService = httpHelperService;
            _httpContextAccessor = httpContextAccessor;
            _profileService = profileService;
            _configuration = configuration;
        }

        //public async Task<GuestData> GetGuestUser()
        //{
        //    if (_guestData == null)
        //    {
        //        string url = _configuration["ApiUrls:Authentication:GetGuest"];
        //        var guestRoot = await _httpHelperService.Get<GuestRoot>(url);
        //        _guestData = guestRoot.Data;
        //    }
        //    return _guestData;
        //}

        public async Task<Dictionary<string, string>> GetOtp(string mobile, string url)
        {
            var body = new Dictionary<string, string>
            {
                { "mobile", mobile }
            };
            var result = await _httpHelperService.Post<Dictionary<string, string>>(url, body);
            return result;

        }

        public async Task<UserDetailsFromApi> VerifyOtp(VerifyUserViewModel details, string url)
        {
            return await _httpHelperService.Post<UserDetailsFromApi>(url, details);
        }

        public async Task<APIModel<User>> SignUp(RegistrationViewModel details, string url)
        {
            return await _httpHelperService.Post<APIModel<User>>(url, details);
        }


        public async Task CreateAuthenticationTicket(User user)
        {
            var claims = GetUserClaims(user);
            //create principal for the current authentication scheme
            var userIdentity = new ClaimsIdentity(claims, JwtBearerDefaults.AuthenticationScheme);
            var userPrincipal = new ClaimsPrincipal(userIdentity);


            //set value indicating whether session is persisted and the time at which the authentication was issued
            var authenticationProperties = new AuthenticationProperties
            {
                IsPersistent = true,
                IssuedUtc = DateTime.UtcNow
            };
            await _httpContextAccessor.HttpContext.SignInAsync(JwtBearerDefaults.AuthenticationScheme, userPrincipal, authenticationProperties);
        }


        private IEnumerable<Claim> GetUserClaims(User user)
        {
            List<Claim> claims = new List<Claim>();
            if (!string.IsNullOrEmpty(user.Mobile))
                claims.Add(new Claim(ClaimTypes.Name, user.Mobile, ClaimValueTypes.String, ""));

            if (!string.IsNullOrEmpty(user.Email))
                claims.Add(new Claim(ClaimTypes.Email, user.Email, ClaimValueTypes.Email, ""));
            if (!string.IsNullOrEmpty(user.Token))
            {
                user.PrimaryAddress = null;
                claims.Add(new Claim(ClaimTypes.UserData, Newtonsoft.Json.JsonConvert.SerializeObject(user)));
            }

            return claims.AsEnumerable<Claim>();
        }

        public User GetUserFromClaims()
        {
            try
            {
                if (cachedUser == null && _httpContextAccessor != null && _httpContextAccessor.HttpContext.User != null && _httpContextAccessor.HttpContext.User.Identity.IsAuthenticated)
                {
                    var userData = _httpContextAccessor.HttpContext.User.Claims.Where(c => c.Type == ClaimTypes.UserData).FirstOrDefault();
                    if (userData != null && !String.IsNullOrEmpty(userData.Value))
                    {
                        cachedUser = Newtonsoft.Json.JsonConvert.DeserializeObject<User>(userData.Value);
                        cachedUser.AddressCount = (Addresses == null ? 0 : Addresses.Count);
                        if(Addresses != null &&  Addresses.Any(a => a.IsPrimary == 1))
                            cachedUser.PrimaryAddress = Addresses.Where(a => a.IsPrimary == 1).FirstOrDefault();
                    }
                }
            }
            catch { }

            return cachedUser;
        }

        public List<Address> Addresses
        {
            get
            {
                if (_cachedAddress == null)
                {
                    try
                    {
                        var addresses = _profileService.GetAddress().Result;
                        if (addresses != null && addresses.Data != null)
                        {
                            _cachedAddress = addresses.Data; //addresses.Data.Where(a => a.IsPrimary == 1).FirstOrDefault();
                        }
                        else
                        {
                            _cachedAddress = new List<Address>();
                        }
                    }
                    catch {
                        _cachedAddress = new List<Address>();
                    }
                }
                return _cachedAddress;

            }
        }

    }
}
