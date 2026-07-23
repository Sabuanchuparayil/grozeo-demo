using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Web.Http;
using System.Web.Http.Results;

namespace RetalineProAgent
{
    public class AuthController : ApiController
    {
        // GET api/<controller>
        public IEnumerable<string> Get()
        {
            return new string[] { "value1", "value2" };
        }

        // GET api/<controller>/5
        public string Get(int id)
        {
            return "value";
        }

        // POST api/<controller>
        //public void Post([FromBody] string value)
        //{
        //}

        // PUT api/<controller>/5
        //public void Put(int id, [FromBody] string value)
        //{
        //}

        // DELETE api/<controller>/5
        public void Delete(int id)
        {
        }
        //[AllowAnonymous]
        [HttpPost]
        public IHttpActionResult GetOTP([FromBody] AuthModel content)
        {
			return Json(new { result = 0, status = "Error", message = "Invalid operation. Please use the <a href='/login'>Account</a> window to sign up" });

			if (content == null || String.IsNullOrEmpty(content.token))
                return Json(new { result = 0, status = "Error", message = "Invalid token" });

            //if (String.IsNullOrEmpty(content.mobile) || content.mobile.Length != 10)
            //    return Json(new { result = 0, status = "Error", message = "Invalid mobile" });

            var captchaResult = Core.Services.APIService.VerifyToken(content.token);
            if(!captchaResult.Success)
                return Json(new { result = 0, status = "Error", message = "Invalid captcha" });

            var user = Service.UserService.GetCustomerByMobile(content.mobile.TrimStart(new char[] { '0' }));
            if (user != null)
                return Json(new { result = 0, status = "Error", message = "The mobile number already exists. Please use the <a href='/login'>login</a> window to sign in" });

            int restrictsignup = 1;
            if (!String.IsNullOrEmpty(System.Configuration.ConfigurationManager.AppSettings.Get("SignupRestrictByInvite")))
            {
                try { restrictsignup = Convert.ToInt32(System.Configuration.ConfigurationManager.AppSettings.Get("SignupRestrictByInvite")); } catch { }
                if (restrictsignup > 0)
                {
                    if (String.IsNullOrEmpty(content.invitationcode))
                        return Json(new { result = -2, status = "Error", message = "Missing invitation code" });

                    List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                    prms.Add(new KeyValuePair<string, object>("code", content.invitationcode));
                    var dtResult = RetalineProAgent.Core.Services.DataServiceMySql.GetDataTable("SELECT * FROM finascop_crm_prospect WHERE (crpr_mode = 5 OR IFNULL(storeGroupId, 0) < 1) and invitationCode=@code AND DATE_ADD(NOW(), INTERVAL -30 MINUTE) < TIMESTAMP(crpr_CreatedOn)", parmeters: prms);
                    if (dtResult == null || dtResult.Rows.Count <= 0)
                        return Json(new { result = -2, status = "Error", message = "Invalid or expired invitation code" });
                }
            }


            var result = Core.Services.APIService.GetOtp(content.mobile.TrimStart(new char[] { '0' }), templateid: 21);
            if(result != null)
                return Json(new { result = 1, status = "Success", message = "OTP send successfully" });
            //return "{result: 1, status: \"Success\", message: \"OTP send successfully\" }";

            return Json(new { result = 0, status = "Error", message = "Failure" });
        }
        //[AllowAnonymous]
        //[HttpPost]
        //public string VerifyCaptchaToken(string token)
        //{
        //    var result = Core.Services.APIService.VerifyToken(token);
        //    return System.Text.Json.JsonSerializer.Serialize(result);
        //}

    }

    public class AuthModel
    {
        public string mobile { get; set; }
        public string otp { get; set; }
        public string token { get; set; }
        public int? type { get; set; } = 1;
        public string invitationcode { get; set; }
    }
}