using Microsoft.AspNetCore.Authentication;
using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Configuration;
using Retaline.Core.BusinessModel.API;
using Retaline.Core.BusinessModel.Captcha;
using Retaline.Core.BusinessModel.UserDetails;
using Retaline.Core.Http;
using Retaline.Core.Services.HelperServices;
using Retaline.Core.Services.ProfileManagement;
using Retaline.Core.ViewModel.Authentication;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Security.Claims;
using System.Security.Cryptography;
using System.Threading.Tasks;

namespace Retaline.Core.Services.Authentication
{
    public class CustomAuthenticationService : ICustomAuthenticationService
    {
        private readonly IHttpHelperService _httpHelperService;
        private readonly IHttpContextAccessor _httpContextAccessor;
        private readonly IConfiguration _configuration;
        private readonly IProfileService _profileService;

        private User cachedUser = null;

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

        public async Task<Dictionary<string, string>> GetOtp(string inputData, string url, int usePsw = 1, int type = 1)
        {
            var body = new Dictionary<string, object>
            {
                { (type == 2 ? "email": "mobile"), inputData }, { "use_password", usePsw }
            };
            var result = await _httpHelperService.Post<Dictionary<string, string>>(url, body);
            return result;

        }

        public async Task<UserDetailsFromApi> VerifyOtp(VerifyUserViewModel details, string url)
        {
            return await _httpHelperService.Post<UserDetailsFromApi>(url, details);
        }
        public async Task<UserDetailsFromApi> VerifyPassword(VerifyUserPswViewModel inputData)
        {
            var body = new Dictionary<string, object>
            {
                { "userid", (inputData.type == 2 ? inputData.Email : inputData.Mobile) }, { "type", inputData.type}, {"password", inputData.Password}
            };
            string url = _configuration["ApiUrls:Authentication:PasswordLogin"].ToString();
            return await _httpHelperService.Post<UserDetailsFromApi>(url, body);
        }

        public async Task<string> ConfirmLegalAge(int verify)
        {
            var body = new
            {
                status = verify
            };
            string url = _configuration["ApiUrls:Authentication:AgeVerification"].ToString();
            var result = await _httpHelperService.Post<APIModel<APISuccessModel>>(url, body);

            return "ok";
        }
        public async Task<APIModel<User>> SignUpCustomer(RegistrationViewModel details, string url)
        {
            return await _httpHelperService.Post<APIModel<User>>(url, details);
        }

        public async Task<APIModel<User>> SignUp(ViewModel.Address.AddressViewModel details, string url, string refCode)
        {
            var body = new Dictionary<string, string>
                {
                    { "city", details.DeliCity },
                    { "email", details.signupEmail },
                    { "name", details.signupName },
                    { "house_name", details.DeliHouseName },
                    { "land_mark", details.DeliLandMark },
                    { "pincode", details.DeliDeliveryPin },
                    { "post", details.DeliPost },
                    { "state", details.DeliState },
                    { "latitude", details.DeliLatitude.ToString() },
                    { "longitude", details.DeliLongitude.ToString() },
                    { "mobile", details.DeliContactNo },
                    { "deli_type", details.DeliType },
                    { "password", details.password },
                    { "refCode", refCode }
                };

            return await _httpHelperService.Post<APIModel<User>>(url, body);
        }

		public async Task<string> ImpersonateUserById(string userId)
        {
			string url = _configuration["ApiUrls:Authentication:ImpersonateToken"].ToString();
			User originalUser = GetUserFromClaims();
			//if(_httpContextAccessor.HttpContext.User.IsInRole("impersonated"))
			string strOriginalUser = (originalUser.DefaultRole == "impersonated" && !String.IsNullOrEmpty(originalUser.ImpersonatedOriginalUserData) ?
				originalUser.ImpersonatedOriginalUserData
				: Newtonsoft.Json.JsonConvert.SerializeObject(originalUser));

			// TODO: Generate OTP server-side and deliver via SMS/email; do not use a static bypass OTP in production.
			var impersonationOtp = RandomNumberGenerator.GetInt32(1000, 10000).ToString("D4");
			var data = new Dictionary<string, string>
			{
				{ "mobile", userId },
                {"email", "" },
                {"userID", userId },
				{ "otp", impersonationOtp }
			};

			var result = await _httpHelperService.Post<UserDetailsFromApi>(url, data);
			if (result == null || result.Data == null || result.Data.User == null || result.Data.User.Id <= 0)
				return "Invalid user";

			return await ImpersonateUser(result.Data.User);

		}



		public async Task<string> ImpersonateUser(string mobile)
        {
            string url = _configuration["ApiUrls:Authentication:ImpersonateToken"].ToString();
            User originalUser = GetUserFromClaims();
            //if(_httpContextAccessor.HttpContext.User.IsInRole("impersonated"))

            // TODO: Generate OTP server-side and deliver via SMS/email; do not use a static bypass OTP in production.
            var impersonationOtp = RandomNumberGenerator.GetInt32(1000, 10000).ToString("D4");
            var data = new Dictionary<string, string>
                {
                    { "mobile", mobile },
                    { "otp", impersonationOtp }
                };

            var result = await _httpHelperService.Post<UserDetailsFromApi>(url, data);
			if (result == null || result.Data == null || result.Data.User == null || result.Data.User.Id <= 0)
				return "Invalid user";

			return await ImpersonateUser(result.Data.User);

		}
		private async Task<string> ImpersonateUser(User impersonateUser) { 

            if (impersonateUser != null && impersonateUser.Id > 0)
            {
                try
                {
                    //User impersonateUser = result.Data.User;
                    if (impersonateUser.DefaultRole == "impersonate")
                        return "User selected is having admin / impersonate role hense cannot impersonate to this user";

					User originalUser = GetUserFromClaims();
					string strOriginalUser = (originalUser.DefaultRole == "impersonated" && !String.IsNullOrEmpty(originalUser.ImpersonatedOriginalUserData) ?
						originalUser.ImpersonatedOriginalUserData
						: Newtonsoft.Json.JsonConvert.SerializeObject(originalUser));

					impersonateUser.DefaultRole = "impersonated";
                    impersonateUser.ImpersonatedOriginalUserData = strOriginalUser;
                    var claims = GetUserClaims(impersonateUser);

                    await _httpContextAccessor.HttpContext.SignOutAsync();

                    var userIdentity = new ClaimsIdentity(claims, JwtBearerDefaults.AuthenticationScheme);
                    var userPrincipal = new ClaimsPrincipal(userIdentity);


                    //set value indicating whether session is persisted and the time at which the authentication was issued
                    var authenticationProperties = new AuthenticationProperties
                    {
                        IsPersistent = false,
                        IssuedUtc = DateTime.UtcNow
                    };
                    await _httpContextAccessor.HttpContext.SignInAsync(JwtBearerDefaults.AuthenticationScheme, userPrincipal, authenticationProperties);
                    cachedUser = null;

                    return "";
                }
                catch (Exception ex)
                {
                    return ex.Message;
                }
            }

            return "Invalid user";
        }

        public async Task<string> ExitImpersonation()
        {
            User _user = GetUserFromClaims();
            User originalUser = null;
            if (_user != null && !String.IsNullOrEmpty(_user.ImpersonatedOriginalUserData))
            {
                originalUser = Newtonsoft.Json.JsonConvert.DeserializeObject<User>(_user.ImpersonatedOriginalUserData);
            }

            try
            {
                await _httpContextAccessor.HttpContext.SignOutAsync();
                cachedUser = null;
                if (originalUser != null)
                    await CreateAuthenticationTicket(originalUser);
            }
            catch { }

            return "";
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
                //user.PrimaryAddress = null;
                claims.Add(new Claim(ClaimTypes.UserData, Newtonsoft.Json.JsonConvert.SerializeObject(user)));
            }
            if (user != null && !String.IsNullOrEmpty(user.DefaultRole))
                claims.Add(new Claim(ClaimTypes.Role, user.DefaultRole));

            return claims.AsEnumerable<Claim>();
        }

        public User GetUserFromClaims()
        {
            try
            {
                //if (cachedUser == null && _httpContextAccessor != null && _httpContextAccessor.HttpContext.User != null && _httpContextAccessor.HttpContext.User.Identity.IsAuthenticated)
                if (_httpContextAccessor != null && _httpContextAccessor.HttpContext.User != null && _httpContextAccessor.HttpContext.User.Identity.IsAuthenticated)
                {
                    var userData = _httpContextAccessor.HttpContext.User.Claims.Where(c => c.Type == ClaimTypes.UserData).FirstOrDefault();
                    if (userData != null && !String.IsNullOrEmpty(userData.Value))
                    {
                        var _user = Newtonsoft.Json.JsonConvert.DeserializeObject<User>(userData.Value);
                        if (_user != null && _user.PrimaryAddress == null)
                        {
                            _user.AddressCount = (Addresses == null ? (_user.PrimaryAddress == null ? 0 : 1) : Addresses.Count);
                            if (Addresses != null && Addresses.Any(a => a.IsPrimary == 1))
                                _user.PrimaryAddress = Addresses.Where(a => a.IsPrimary == 1).FirstOrDefault();
                        }
                        return _user;
                    }
                }
            }
            catch { }

            return null; //cachedUser;
        }

        public List<Address> Addresses
        {
            get
            {
                try
                {
                    var addresses = _profileService.GetAddress().Result;
                    return addresses.Data;
                }
                catch { }
                return new List<Address>();
                //if (_cachedAddress == null)
                //{
                //    try
                //    {
                //        var addresses = _profileService.GetAddress().Result;
                //        if (addresses != null && addresses.Data != null)
                //        {
                //            _cachedAddress = addresses.Data; //addresses.Data.Where(a => a.IsPrimary == 1).FirstOrDefault();
                //        }
                //        else
                //        {
                //            _cachedAddress = new List<Address>();
                //        }
                //    }
                //    catch
                //    {
                //        _cachedAddress = new List<Address>();
                //    }
                //}
                //return _cachedAddress;

            }
        }

        public int GetBranchId()
        {
            int branchId = 0;
            try
            {
                var user = GetUserFromClaims();
                if (user == null)
                    branchId = _httpHelperService.GuestUser.Branch;
                else
                    branchId = user.BranchId;
            }
            catch
            {
                branchId = 0;
            }
            if (branchId < 1)
                branchId = 0;

            return branchId;
        }

        public async Task<CaptchaResponse> VerifyToken(string token)
        {
            string url = string.Format(_configuration["Recaptcha:VerificationUrl"], _configuration["Recaptcha:Secret"], token);
            var result = await _httpHelperService.Post<CaptchaResponse>(url, null);
            return result;

        }

        /// <summary>
        /// Get authenticated customer
        /// </summary>
        /// <returns>
        /// A task that represents the asynchronous operation
        /// The task result contains the customer
        /// </returns>
        public virtual async Task<User> GetAuthenticatedCustomerAsync()
        {
            //whether there is a cached customer
            if (cachedUser != null)
                return cachedUser;

            //try to get authenticated user identity
            var authenticateResult = await _httpContextAccessor.HttpContext.AuthenticateAsync(RetalineCookieDefaults.AuthenticationScheme);
            if (!authenticateResult.Succeeded)
                return null;

            User customer = GetUserFromClaims();

            //try to get customer by email
            var emailClaim = authenticateResult.Principal.FindFirst(claim => claim.Type == ClaimTypes.Email
                && claim.Issuer.Equals(RetalineCookieDefaults.ClaimsIssuer, StringComparison.InvariantCultureIgnoreCase));
            if (emailClaim != null)
            {
                if (customer == null || customer.Email != emailClaim.Value)
                    customer = null;
                //customer = await _customerService.GetCustomerByEmailAsync(emailClaim.Value);
            }

            //whether the found customer is available
            if (customer == null)
                return null;

            //static DateTime trimMilliseconds(DateTime dt) => new(dt.Year, dt.Month, dt.Day, dt.Hour, dt.Minute, dt.Second, 0, dt.Kind);

            ////get the latest password
            //var customerPassword = await _customerService.GetCurrentPasswordAsync(customer.Id);
            ////required a customer to re-login after password changing
            //if (trimMilliseconds(customerPassword.CreatedOnUtc).CompareTo(trimMilliseconds(authenticateResult.Properties.IssuedUtc?.DateTime ?? DateTime.UtcNow)) > 0)
            //    return null;

            //cache authenticated customer
            cachedUser = customer;

            return cachedUser;
        }

        /// <summary>
        /// Get user authentication code from External authentication (Google/Facebook/Instagram)
        /// </summary>
        /// <param name="source">google, facebook, instagram</param>
        /// <param name="code">code from the source response</param>
        /// <returns>User auth info from BizAPI</returns>
        public async Task<UserDetailsFromApi> GetUserFromExternalAuth(string source, string code)
        {
            if (!string.IsNullOrEmpty(code))
            {
                string url = _configuration["ApiUrls:Authentication:GoogleAuthUrl"];
                if (String.IsNullOrEmpty(url))
                    url = "https://bizapi.dev.grozeo.in/api/signup/socials/";
                url += source;

                var postData = new Dictionary<string, string>
                {
                    { "code", code }
                };

                var result = await _httpHelperService.Post<UserDetailsFromApi>(url, postData);
                return result;
            }

            return default;
        }

    }


}
