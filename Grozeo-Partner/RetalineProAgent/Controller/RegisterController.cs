using RetalineProAgent.Core.Services;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Web.Http;
using Newtonsoft.Json;
using NPOI.OpenXmlFormats.Wordprocessing;
using RetalineProAgent.Service;

namespace RetalineProAgent.Controller
{
    [FilterIP(
         ConfigurationKeyAllowedSingleIPs = "AllowedAdminSingleIPs"
    )]
    public class RegisterController : ApiController
    {
        //// GET api/<controller>
        //public IEnumerable<string> Get()
        //{
        //    return new string[] { "value1", "value2" };
        //}

        //// GET api/<controller>/5
        //public string Get(int id)
        //{
        //    return "value";
        //}

        // POST api/<controller>
        //public void Post([FromBody] Service.User value)
        [HttpPost]
        public IHttpActionResult RegisterUser([FromBody] Service.User user)
        {
            if(user == null)
                return Json(new { result = 0, status = "Error", message = "Invalid object" });
            if (String.IsNullOrEmpty(user.Phone))
                return Json(new { result = 0, status = "Error", message = "Missing user mobile" });
            if (String.IsNullOrEmpty(user.Email))
                return Json(new { result = 0, status = "Error", message = "Missing user email" });
            if (String.IsNullOrEmpty(user.Password))
                user.Password = Guid.NewGuid().ToString().Split('-').LastOrDefault();
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("email", user.Email));
            prms.Add(new KeyValuePair<string, object>("mobile", user.Phone.TrimStart(new char[] { '0' })));
            prms.Add(new KeyValuePair<string, object>("password", user.Password));
            prms.Add(new KeyValuePair<string, object>("passwordType", 1));
            prms.Add(new KeyValuePair<string, object>("fullName", user.FullName));
            prms.Add(new KeyValuePair<string, object>("address", user.Address));
            prms.Add(new KeyValuePair<string, object>("city", user.City));
            prms.Add(new KeyValuePair<string, object>("state", user.State));
            prms.Add(new KeyValuePair<string, object>("country", user.Country));
            prms.Add(new KeyValuePair<string, object>("storegroupid", 1));
            prms.Add(new KeyValuePair<string, object>("storegroupname", ""));
            prms.Add(new KeyValuePair<string, object>("createdby", "Admin API"));
            prms.Add(new KeyValuePair<string, object>("roleId", 7));
            prms.Add(new KeyValuePair<string, object>("usertype", 0));
            prms.Add(new KeyValuePair<string, object>("hasVerifiedEmail", 1));
            prms.Add(new KeyValuePair<string, object>("hasVerifiedMobile", 1));

            DataTable dt = DataService.GetDataTable("CreateUser", parmeters: prms, isSP: true);
            if (dt == null || dt.Rows.Count < 1)
                return Json(new { result = 0, status = "Error", message = "Failure! There is a technical error happened while executing." });
            int userid = 0;
            int result = (int)dt.Rows[0][0];
            userid=(int)dt.Rows[0][1];
            if (userid > 0 && user.roleId.Length > 0)
            {
                // To prevent the modification or update of merchant roles.
                string userrole = $"DELETE FROM User_UserRole_Mapping WHERE UserId = @userid and RoleId NOT IN (select  id from UserRole where RoleType=2); " +
                    $"INSERT INTO User_UserRole_Mapping (UserId, RoleId, StoreGroupId) " +
                    $"SELECT @userid,Id,@storegroupid from UserRole where RoleType <> 2 and Id in({string.Join(",", user.roleId)});";
                    prms.Add(new KeyValuePair<string, object>("userid", userid));
                    DataService.ExecuteSql(userrole, parmeters: prms);               
            }
            if (result == 1)
            {               
                prms.Add(new KeyValuePair<string, object>("areaId", (user.AreaId != null? user.AreaId : -1)));
                prms.Add(new KeyValuePair<string, object>("fleetId", (user.FleetId != null ? user.FleetId : -1) ));
                string sql = "UPDATE [User] SET hasVerifiedEmail=1, AreaId=@areaId, FleetId=@fleetId WHERE Email=@email AND Mobile=@mobile";
                DataService.ExecuteSql(sql, parmeters: prms);

                String strUrl = Request.RequestUri.AbsoluteUri.Replace(Request.RequestUri.PathAndQuery, "/");
                //string strBody = $"<p style='color: green'><strong>WELCOME TO GROZEO FAMILY.</strong></p>" +
                //"<p>Please click on the link or use the url provide below." +
                //$"<br><br>Login link: {strUrl}<br>" +
                //$"User name: {user.Email}<br>Temporary password: {user.Password}<br><br>Enjoy your new freedom to a skeuomorphic retail ecosystem - Grozeo</p>";
                string strBody = "";
                List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
                replacements.Add(new KeyValuePair<string, string>("[URLPART]", strUrl.TrimEnd(new char[] { '/', '\\' })));
                replacements.Add(new KeyValuePair<string, string>("[Business Associate's Name]", user.FullName));
                replacements.Add(new KeyValuePair<string, string>("[Login Username]", user.Email));
                replacements.Add(new KeyValuePair<string, string>("[Login Password]", user.Password));
                replacements.Add(new KeyValuePair<string, string>("[Login Link]", strUrl));            
                strBody = EmailService.CreateEmailbody(EmailType.BusinessAssociatewelcome, replacements);
                // Send activation email.
                var result2 = Core.Services.APIService.SendEmail(user.Email, "Grozeo Associate - Verification", strBody, user.FullName, true).Result;


                return Json(new { result = 1, status = "Success", message = "User created successfully!", refId = userid });
            }
            string message = result == 2 ? "User linked to store successfully!"
                : result == -1 ? "Failed, the email or mobile conflicted with an existing record. Please try with another email and mobile!"
                : result == -2 ? "Failed, lack of permission!"
                : "Invalid operation";
            return Json(new {result = result > 0 ? result : 0, status = result > 0 ? "Success" : "Error", message, refId = userid});

        }

        [HttpPost]
        public IHttpActionResult ProspectSendInvite([FromBody] object code)
        {
            var dynamicObject = JsonConvert.DeserializeAnonymousType(JsonConvert.SerializeObject(code), new { code = string.Empty, fullname = string.Empty, email= string.Empty });
            if (dynamicObject == null || String.IsNullOrEmpty(dynamicObject.code) || String.IsNullOrEmpty(dynamicObject.email) || String.IsNullOrEmpty(dynamicObject.fullname))
            {
                return Json(new { result = 0, status = "Error", message = "Invalid or missing data provided" });
            }
            String strUrl = Request.RequestUri.AbsoluteUri.Replace(Request.RequestUri.PathAndQuery, "/").TrimEnd(new char[] { '/', '\\', ' ' });
            string signupUrl = $"{strUrl}/Login?refcode={dynamicObject.code}";

            // Send email
            try
            {
                List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
                replacements.Add(new KeyValuePair<string, string>("[URLPART]", strUrl));
                replacements.Add(new KeyValuePair<string, string>("[USER]", dynamicObject.fullname));
                replacements.Add(new KeyValuePair<string, string>("[SIGNUPURL]", signupUrl));

                string strBody = Service.EmailService.CreateEmailbody(Service.EmailType.ProspectInvite, replacements);
                // Send activation email.
                Core.Services.APIService.SendEmail(dynamicObject.email, "Welcome to Grozeo Store", strBody, dynamicObject.fullname, true);

            }
            catch (Exception ex) {
                return Json(new { result = 0, status = "Error", message = "Error occurred: "+ ex.Message });
            }

            return Json(new { result = 1, status = "Success", message = "Invitation send successfully!", url= signupUrl });
        }

        //// PUT api/<controller>/5
        //public void Put(int id, [FromBody] string value)
        //{
        //}

        //// DELETE api/<controller>/5
        //public void Delete(int id)
        //{
        //}
    }
}