using Microsoft.AspNetCore.Authentication;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Http;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;
using Retaline.Core.Services.Authentication;
using Retaline.Core.Services.ProfileManagement;
using Retaline.Core.ViewModel.Authentication;
using System;
using System.Drawing.Drawing2D;
using System.Linq;
using System.Text.RegularExpressions;
using System.Threading.Tasks;

namespace Retaline.Web.Controllers
{
    public class AuthenticationController : Controller
    {
        private readonly ILogger<AuthenticationController> _logger;
        private readonly IConfiguration _configuration;
        private readonly ICustomAuthenticationService _authenticationService;
        private readonly IProfileService _profileService;
        private readonly IHttpContextAccessor _httpContextAccessor;
        private readonly Core.ViewModel.Tenant.AppTenant tenant;

        public AuthenticationController(ILogger<AuthenticationController> logger,
            IConfiguration configuration,
            IHttpContextAccessor httpContextAccessor,
          ICustomAuthenticationService authenticationService,
            IProfileService profileService, SaasKit.Multitenancy.ITenant<Core.ViewModel.Tenant.AppTenant> tenant)
        {
            _logger = logger;
            _configuration = configuration;
            _authenticationService = authenticationService;
            _httpContextAccessor = httpContextAccessor;
            _profileService = profileService;
            this.tenant = tenant?.Value;
        }

        [HttpPost]
        [AllowAnonymous]
        public async Task<IActionResult> GetOtp([FromBody] VerifyUserViewModel authData, string token)
        {
            bool isCaptchaEnabled = true; try { isCaptchaEnabled = Convert.ToBoolean(_configuration["isCaptchaEnabled"]); } catch { isCaptchaEnabled = true; }

            Core.BusinessModel.Captcha.CaptchaResponse captchaResponse = new Core.BusinessModel.Captcha.CaptchaResponse();
            if (isCaptchaEnabled)
                captchaResponse = await _authenticationService.VerifyToken(token);

            if (!isCaptchaEnabled || captchaResponse.Success)
            {
                var result = await _authenticationService.GetOtp(authData.Mobile, _configuration["ApiUrls:Authentication:GetOtp"].ToString(), authData.usePsw);
                return Json(result);
            }
            else
            {
                return Json(null);
            }
        }

        [HttpPost]
        [AllowAnonymous]
        public async Task<IActionResult> VerifyOtp([FromBody] VerifyUserViewModel details)
        {
            try
            {
                if (tenant != null && !string.IsNullOrEmpty(tenant.StoreId) && System.Convert.ToInt32(tenant.StoreId) > 0)
                    details.GroupId = System.Convert.ToInt32(tenant.StoreId);
            }
            catch { }
            string url = _configuration["ApiUrls:Authentication:VerifyOtp"];
            if (String.IsNullOrEmpty(details.Mobile) && !String.IsNullOrEmpty(details.Email))
                url = url + "/email";

            var result = await _authenticationService.VerifyOtp(details, url);

            if (result != null && result.Data != null && result.Data.User != null && result.Data.User.Id > 0)
            {
                await _authenticationService.CreateAuthenticationTicket(result.Data.User);
                try
                {
                    var adr = result?.Data?.User?.PrimaryAddress;
                    if (adr != null && adr.Latitude > 0 && adr.Longitude > 0)
                    {
                        _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLAT", adr.Latitude.ToString());
                        _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLNG", adr.Longitude.ToString());
                    }
                }
                catch { }
            }

            try
            {
                if (_httpContextAccessor != null && _httpContextAccessor.HttpContext != null && _httpContextAccessor.HttpContext.Session != null)
                {
                    _httpContextAccessor.HttpContext.Session.Remove("SHOWADDRALERT");
                    if(!String.IsNullOrEmpty(result.Data.refCode))
                        _httpContextAccessor.HttpContext.Session.SetString("SIGNUPREFCODE", result.Data.refCode);
                }
            }
            catch { }

            return Json(result);
        }

        [HttpPost]
        [AllowAnonymous]
        [ValidateAntiForgeryToken]
        public async Task<IActionResult> VerifyPsw([FromBody] VerifyUserPswViewModel pswData, string token)
        {
            var captchaResponse = await _authenticationService.VerifyToken(token);
            if (captchaResponse.Success)
            {
                var result = await _authenticationService.VerifyPassword(pswData);

                if (result != null && result.Data != null && result.Data.User != null && result.Data.User.Id > 0)
                {
                    await _authenticationService.CreateAuthenticationTicket(result.Data.User);
                    try
                    {
                        var adr = result?.Data?.User?.PrimaryAddress;
                        if (adr != null && adr.Latitude > 0 && adr.Longitude > 0)
                        {
                            _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLAT", adr.Latitude.ToString());
                            _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLNG", adr.Longitude.ToString());
                        }
                    }
                    catch { }
                }

                try
                {
                    if (_httpContextAccessor != null && _httpContextAccessor.HttpContext != null && _httpContextAccessor.HttpContext.Session != null)
                        _httpContextAccessor.HttpContext.Session.Remove("SHOWADDRALERT");
                }
                catch { }

                return Json(result);

            }
            else
            {
                return Json(new { result = "false", message = "Captcha validation failed" });
            }


        }


        [Route("Impersonate")]
		[Route("Impersonate/{id}/{type}")]
		[Authorize(Roles = "impersonate, impersonated")]
        public async Task<IActionResult> ImpersonateUser(string id, int confirmImpersonate = 0, int type=0)
        {
            Models.Authentication.Impersonate _impersonate = new Models.Authentication.Impersonate();
            _impersonate.ImpersonateNumber = id;
            if (string.IsNullOrEmpty(id) || (User.IsInRole("impersonated") && confirmImpersonate == 0))
                return View("Impersonate", _impersonate);
            string result = "";
            if (type == 1)
                result = await _authenticationService.ImpersonateUserById(id);
            else
                result = await _authenticationService.ImpersonateUser(id);

            _impersonate.Message = result;

            if (string.IsNullOrEmpty(result))
                return RedirectToAction("Index", "Home");

            return View("Impersonate", _impersonate);
        }

        [Route("endimpersonation")]
        //[Authorize(Roles = "impersonated")]
        public async Task<IActionResult> StopImpersonation()
        {
            await _authenticationService.ExitImpersonation();
            return RedirectToAction("Index", "Home");
        }

        [HttpPost]
        [AllowAnonymous]
        public async Task<IActionResult> SignUp([FromBody] RegistrationViewModel details)
        {
            string refCode = _httpContextAccessor.HttpContext.Session.GetString("SIGNUPREFCODE");
            details.refCode = refCode;
            var result = await _authenticationService.SignUpCustomer(details, _configuration["ApiUrls:Authentication:SignUp"]);
            var addressCount = -1;
            if (!string.IsNullOrEmpty(result.Status) && result.Status != "error")
            {
                await _authenticationService.CreateAuthenticationTicket(result.Data);
                //string profileUrl = _configuration["ApiUrls:ProfileManagement:GetAddresses"].ToString();
                var addressess = await _profileService.GetAddress();
                addressCount = addressess.Data.Count;
                try
                {
                    var adr = addressess.Data.Where(a => a.IsPrimary > 0).FirstOrDefault();
                    if (adr != null && adr.Latitude > 0 && adr.Longitude > 0)
                    {
                        _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLAT", adr.Latitude.ToString());
                        _httpContextAccessor.HttpContext.Session.SetString("CURSEARCHLNG", adr.Longitude.ToString());
                    }
                }
                catch { }
                return Json(new { status = result.Status, addressCount = addressCount });

            }
            return Json(new { status = result.Status, message = result.Failure.msg });

        }

        [Route("logout")]
        public async Task<IActionResult> LogOut()
        {
            await _httpContextAccessor.HttpContext.SignOutAsync(Microsoft.AspNetCore.Authentication.JwtBearer.JwtBearerDefaults.AuthenticationScheme);
            return Redirect("/"); //Json("OK");


        }

        [Route("account/accessdenied")]
        public IActionResult AccessDenied(string ReturnUrl)
        {
            //await _httpContextAccessor.HttpContext.SignOutAsync(Microsoft.AspNetCore.Authentication.JwtBearer.JwtBearerDefaults.AuthenticationScheme);
            return Redirect("/?ReturnUrl=" + ReturnUrl); //Json("OK");


        }


        [HttpPost]
        [AllowAnonymous]
        public void ActiveGuest()
        {
            //var result = await _authenticationService.VerifyOtp(details, _configuration["ApiUrls:Authentication:VerifyOtp"].ToString());
            //if (result.Data.User.Id > 0)
            //{
            //    await _authenticationService.CreateAuthenticationTicket(result.Data.User);
            //}
            //return Json(result);
        }

        [AllowAnonymous]
        [HttpPost]
        [Route("login/validateemail")]
        public async Task<IActionResult> GetEmailOtp([FromBody] VerifyUserViewModel details, string token)
        {
            if (details == null || string.IsNullOrEmpty(details.Email))
            {
                return Json(new { status = "false", message = "Email is a required field" });
            }

            string pattern = @"^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$";

            // Create Regex object
            Regex regex = new Regex(pattern);

            // Check if the email matches the pattern
            if (!regex.IsMatch(details.Email))
            {
                return Json(new { status = "false", message = "Invalid email format" });
            }


            var captchaResponse = await _authenticationService.VerifyToken(token);
            if (captchaResponse.Success)
            {
                var result = await _authenticationService.GetOtp(details.Email, _configuration["ApiUrls:Authentication:GetEmailOtp"], details.usePsw, 2);
                return Json(result);
                //return Json(new { status = "ok", message = "OTP send successfully", msg = "verified successfully for password" });
            }
            else
            {
                return Json(new { result = "false", message = "Captcha validation failed" });
            }
        }

        [HttpPost]
        [Route("UpdateVerifiedStatus")]
        public IActionResult UpdateVerifiedStatus(string verified)
        {
            // TODO: EmailVerified/PhoneVerified must only be set by verified backend processes (e.g. OTP/email verification API), not client self-service.
            return StatusCode(StatusCodes.Status403Forbidden, new { success = false, message = "Self-service verification is disabled." });

            //var user = _authenticationService.GetUserFromClaims();
            //if(verified == "email")
            //{
            //    user.EmailVerified = 1;
            //}
            //else
            //{
            //    user.PhoneVerified = 1;
            //}
            //await _authenticationService.CreateAuthenticationTicket(user);
            //return Json(new { success = true });
        }

        [HttpPost]
        [Route("confirmLegalAge")]

        public async Task<IActionResult> confirmAge([FromBody] int verify)
        {

            try
            {
                await _authenticationService.ConfirmLegalAge(verify);
                return Json(new { success = true }); // Return a success response

            }
            catch
            {
                return Json(new { success = false }); // Return a failure response}
            }
        }
        [HttpPost]
        [Route("UpdateAgeVerifiedStatus")]
        public IActionResult UpdateAgeVerifiedStatus(bool value)
        {
            // TODO: AgeVerified must only be set by verified backend processes (e.g. age verification API), not client self-service.
            return StatusCode(StatusCodes.Status403Forbidden, new { success = false, message = "Self-service age verification is disabled." });

            //var user = _authenticationService.GetUserFromClaims();
            //if (value)
            //{
            //    user.AgeVerified = 1;
            //}
            //else
            //{
            //    user.AgeVerified = 0;
            //}
            //await _authenticationService.CreateAuthenticationTicket(user);
            //return Json(new { success = true });
        }
        

    }
}
