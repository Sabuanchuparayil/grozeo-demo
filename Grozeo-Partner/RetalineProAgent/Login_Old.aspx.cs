using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.IO;
using System.Linq;
using System.Net.PeerToPeer;
using System.Net;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.Script.Serialization;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.UI.Login
{
    public partial class Login_Old : System.Web.UI.Page
    {
        private string CurUID
        {
            get
            {
                return (string)ViewState["CURUID"];
            }
            set
            {
                ViewState["CURUID"] = value;
            }
        }
        protected void Page_Load(object sender, EventArgs e)
        {
            if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
            {
                //txtMobile.Attributes.Add("oninput", @"this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');");
                txtMobile.Attributes.Add("maxlength", "10");
            }
            else if (ConfigurationManager.AppSettings.Get("CountryCode") == "UK")
            {
                //txtMobile.Attributes.Add("oninput", @"this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');");
                txtMobile.Attributes.Add("maxlength", "12");
            }

            ltrResult.Text = ltrSendPasswordMessage.Text = ltrLoginError.Text = lblResult.Text = ltrInvalidMobile.Text = ""; countdown.Visible = false;
            string logoUrl = ConfigurationManager.AppSettings.Get("logoUrl");
            //if(!String.IsNullOrEmpty(logoUrl))
            //imgLogo.Src = logoUrl;

            if (User.Identity.IsAuthenticated && !String.IsNullOrEmpty(Request.QueryString["ReturnUrl"]) && Request.QueryString["ReturnUrl"].ToLower() != "/Navigations/StoreSettings")
                if ((Page.User.IsInRole("StoreAdmin") || Page.User.IsInRole("Agent") || Page.User.IsInRole("StoreManager") || Page.User.IsInRole("BranchManager")))
                    Response.Redirect("/Tenant/");
            //Response.Redirect("/Tenant/Store/StoreSettings");

            if (Session["LoginWith"] != null)
            {
                switch (Session["LoginWith"].ToString())
                {
                    case "google":
                        FetchUserSocialDetail("google");
                        break;
                    case "facebook":
                        FetchUserSocialDetail("facebook");
                        break;
                }
            }

        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            plcLoginWithPsw.Visible = (String.IsNullOrEmpty(hidLoginType.Value) || hidLoginType.Value == "1");
            plcLoginWithOTP.Visible = (hidLoginType.Value == "2");
            plcLoginPendingVerification.Visible = (hidLoginType.Value == "3");
            plcForgotPassword.Visible = (hidLoginType.Value == "4");
            plcVerificationFailedEmail.Visible = (hidLoginType.Value == "5");
            plcVerificationFailedMobile.Visible = (hidLoginType.Value == "6");

            //lbtnViewEmailLogin.Visible = plcLoginWithOTP.Visible || plcForgotPassword.Visible || plcVerificationFailedEmail.Visible || plcVerificationFailedMobile.Visible;
            //lbtnViewMobileLogin.Visible = plcLoginWithPsw.Visible || plcForgotPassword.Visible || plcVerificationFailedEmail.Visible || plcVerificationFailedMobile.Visible;
            plsHeaderPostcoder.Visible = false;
            if (Session["SHOWPOSTCODER"] != null && (int)Session["SHOWPOSTCODER"] == 1)
                plsHeaderPostcoder.Visible = true;

        }

        protected void lbtnViewMobileLogin_Click(object sender, EventArgs e)
        {
            hidLoginType.Value = "2";
            pnlInputMobile.Visible = true;
            pnlInputOTP.Visible = false;
            txtMobile.Value = "";
            ltrAutoFocusObj.Text = txtMobile.ClientID;
        }

        protected void lbtnViewEmailLogin_Click(object sender, EventArgs e)
        {
            hidLoginType.Value = "1";
            UserName.Text = "";
            ltrAutoFocusObj.Text = UserName.ClientID;
        }

        protected void btnSendOTP_Click(object sender, EventArgs e)
        {
            if (ConfigurationManager.AppSettings.Get("CountryCode") == "IN")
            {
                Regex reg = new Regex(@"^[0-9]{10}$");
                if (String.IsNullOrEmpty(txtMobile.Value) || !reg.Match(txtMobile.Value).Success)
                {
                    ltrInvalidMobile.Text = "Invalid mobile number. Please enter your 10 digit mobile number (without country code) used to register your store";
                    return;
                }
            }

            string sql = $"select top 1 * from [User] where mobile = @mobile";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("mobile", txtMobile.Value));
            var tblUser = DataService.GetDataTable(sql, parmeters: prms);
            if (tblUser == null || tblUser.Rows.Count < 1)
            {
                //hidLoginType.Value = "6";
                ltrInvalidMobile.Text = "Number provided is not registered. Please enter the phone number that was used to register or sign up afresh.";
                return;
            }

            Core.Services.APIService.GetOtp(txtMobile.Value);
            pnlInputMobile.Visible = false;
            pnlInputOTP.Visible = true;

            ltrCurMobileNum.Text = txtMobile.Value;
            countdown.Visible = true;
            txtLoginOTP.Value = "";
            //first.Value = second.Value = third.Value = fourth.Value = "";
            ltrAutoFocusObj.Text = txtLoginOTP.ClientID; //first.ClientID;
        }

        protected void btnVerifyOTP_Click(object sender, EventArgs e)
        {
            string strOtp = txtLoginOTP.Value; //$"{first.Value}{second.Value}{third.Value}{fourth.Value}";
            var userResult = Core.Services.APIService.VerifyOtp(txtMobile.Value, strOtp);

            if (userResult != null && userResult.Data != null && userResult.Data.IsVerified)
            {

                User user = null;
                var loginResult = UserService.ValidateCustomer(txtMobile.Value, "", out user, isSuccessOTPLogin: true);
                switch (loginResult)
                {
                    case CustomerLoginResults.Successful:
                    case CustomerLoginResults.PendingEmailVerification:
                        {
                            //e.Authenticated = true;
                            FormsAuthenticationService.SignIn(user, true);//RememberMe.Checked);
                            Service.UserService.CachedDefaultUser = user;
                            Response.Redirect("/");
                            break;
                        }
                    case CustomerLoginResults.CustomerNotExist:
                        ltrResult.Text = "Verification Failed. Your account is not existing or expired. Please contact support for more details";
                        break;
                    case CustomerLoginResults.Deleted:
                        //ModelState.AddModelError("", _localizationService.GetResource("Account.Login.WrongCredentials.Deleted"));
                        ltrResult.Text = "Verification Failed. User account does not exists";
                        break;
                    case CustomerLoginResults.NotActive:
                        ltrResult.Text = "Verification Failed. Your account is not active. Please contact support for more details";
                        break;
                    case CustomerLoginResults.NotRegistered:
                        ltrResult.Text = "Verification Failed. User is not registered or active";
                        break;
                    //case CustomerLoginResults.PendingEmailVerification:
                    //    CurUID = txtMobile.Value;
                    //    lbtnResendEmail.Attributes.Add("authtype", "2");
                    //    hidLoginType.Value = "3";
                    //    //ltrResult.Text = "Verification Failed. Your account is waiting for email verification. Please complete the verification using the link send to your email inbox";
                    //    break;
                    default:
                        ltrResult.Text = "Verification Failed. Your account is not active or pending for action. Please contact support for more details";
                        break;
                }

                //var user = Service.UserService.GetCustomerByMobile(userResult.Data.AppUser.Mobile);
                //if (user != null && user.Active)
                //{
                //    FormsAuthenticationService.SignIn(user, true);
                //    this.CurrentUser = user;
                //    Response.Redirect("/");
                //}
                //else
                //{
                //    ltrResult.Text = "User account is not active. Please contact administrator.";
                //}


            }
            else
            {
                ltrResult.Text = "Invalid OTP or verification failed!!";
            }

        }


        protected void LoginButton_Click(object sender, EventArgs e)
        {
            User user = null;
            var loginResult = UserService.ValidateCustomer(UserName.Text, Password.Text, out user);
            switch (loginResult)
            {
                case CustomerLoginResults.Successful:
                    {
                        //e.Authenticated = true;
                        FormsAuthenticationService.SignIn(user, true);
                        Service.UserService.CachedDefaultUser = user;
                        Response.Redirect("/");
                        break;
                    }
                case CustomerLoginResults.CustomerNotExist:
                case CustomerLoginResults.Deleted:
                case CustomerLoginResults.NotRegistered:
                    //hidLoginType.Value = "5";
                    //ltrEmailLoginFailure.Text = "We do not have your email within our system. Please select your email id used for registration or register your store now..";
                    ltrLoginError.Text = "Email provided is not registered. Please provide the email that used to register or sign up fresh.";
                    break;
                case CustomerLoginResults.NotActive:
                    ltrLoginError.Text = "User is not active. Please contact support for more details to activate the account";
                    break;
                case CustomerLoginResults.PendingEmailVerification:
                    CurUID = UserName.Text;
                    lbtnResendEmail.Attributes.Add("authtype", "1");
                    hidLoginType.Value = "3";
                    //ltrResult.Text = "Verification Failed. Your account is waiting for email verification. Please complete the verification using the link send to your email inbox";
                    break;
                case CustomerLoginResults.WrongPassword:
                default:
                    ltrLoginError.Text = "Invalid credentials. Please try again or signup";
                    break;
            }
        }

        protected void GoogleBtnClick(object sender, EventArgs e)
        {
            GetSocialCredentials("google");
        }
        protected void FacebookBtnClick(object sender, EventArgs e)
        {
            GetSocialCredentials("facebook");
        }
        private void GetSocialCredentials(String provider)
        {
            if (provider == "google")
            {
                string Googleurl = String.Format(ConfigurationManager.AppSettings["Googleurl"], ConfigurationManager.AppSettings["google_redirect_url"], ConfigurationManager.AppSettings["google_client_id"]);
                Session["LoginWith"] = provider;
                Response.Redirect(Googleurl);
            }
            else if (provider == "facebook")
            {
                string Facebookurl = string.Format(ConfigurationManager.AppSettings["Facebook_url"], ConfigurationManager.AppSettings["Facebook_AppId"], ConfigurationManager.AppSettings["Facebook_RedirectUrl"], ConfigurationManager.AppSettings["Facebook_scope"]);
                Session["LoginWith"] = provider;
                Response.Redirect(Facebookurl);
            }
        }

        private void FetchUserSocialDetail(String provider)
        {
            User user = null;
            try
            {
                if (provider == "google")
                {
                    var url = Request.Url.Query;
                    string code = Request.QueryString["code"];

                    if (!string.IsNullOrEmpty(code)) //(url))
                    {
                        //string queryString = url.ToString();
                        //string[] words = queryString.Split('=');
                        //string code = words[1];
                        if (!string.IsNullOrEmpty(code))
                        {
                            //string Parameters =  "code=" + code + "&client_id=" + ConfigurationManager.AppSettings["google_client_id"] + "&client_secret=" + ConfigurationManager.AppSettings["google_client_secret"] + "&redirect_uri=" + ConfigurationManager.AppSettings["google_redirect_url"] + "&grant_type=authorization_code";
                            string parameters = string.Format("code={0}&client_id={1}&client_secret={2}&redirect_uri={3}&grant_type=authorization_code",
                                code,
                                ConfigurationManager.AppSettings["google_client_id"],
                                ConfigurationManager.AppSettings["google_client_secret"],
                                ConfigurationManager.AppSettings["google_redirect_url"]);
                            string response = MakeWebRequest(ConfigurationManager.AppSettings["googleoAuthUrl"], "POST", "application/x-www-form-urlencoded", parameters);
                            GoogleToken tokenInfo = new JavaScriptSerializer().Deserialize<GoogleToken>(response);

                            if (tokenInfo != null)
                            {
                                if (!string.IsNullOrEmpty(tokenInfo.access_token))
                                {
                                    var googleInfo = MakeWebRequest(ConfigurationManager.AppSettings["googleoAccessUrl"] + tokenInfo.access_token, "GET");
                                    GoogleInfo profile = new JavaScriptSerializer().Deserialize<GoogleInfo>(googleInfo);
                                    var loginResult = UserService.ValidateCustomer(profile.email, "", out user, true);
                                    if (loginResult == CustomerLoginResults.Successful)
                                    {
                                        //e.Authenticated = true;
                                        FormsAuthenticationService.SignIn(user, true);
                                        Service.UserService.CachedDefaultUser = user;
                                        Response.Redirect("/");
                                    }
                                    else if (loginResult == CustomerLoginResults.PendingEmailVerification)
                                    {
                                        CurUID = profile.email;
                                        lbtnResendEmail.Attributes.Add("authtype", "3");
                                        hidLoginType.Value = "3";
                                    }
                                    else
                                    {
                                        hidLoginType.Value = "5";
                                        ltrEmailLoginFailure.Text = $"We do not have your email {profile.email} within our system. Please select your email id used for registration or register your store now..";
                                        //ltrResult.Text = $"The email id: {profile.email} is not existing or not active. You may Please signup using the register link or contact support if you have registered alterady with this email id but cannot login.";
                                    }
                                    //txtResponse.Text = googleInfo;
                                }
                            }
                        }
                    }
                    Session.Remove("LoginWith");
                }
                else if (provider == "facebook")
                {
                    if (Request["code"] != null)
                    {
                        string url = string.Format(ConfigurationManager.AppSettings["FacebookOAuthurl"],
                            ConfigurationManager.AppSettings["Facebook_AppId"],
                            ConfigurationManager.AppSettings["Facebook_RedirectUrl"],
                            ConfigurationManager.AppSettings["Facebook_scope"],
                            Request["code"].ToString(),
                            ConfigurationManager.AppSettings["Facebook_AppSecret"]);

                        string tokenResponse = MakeWebRequest(url, "GET");
                        var tokenInfo = new JavaScriptSerializer().Deserialize<FacebookToken>(tokenResponse);
                        var facebookInfoJson = MakeWebRequest(ConfigurationManager.AppSettings["FacebookAccessUrl"] + tokenInfo.access_token, "GET");
                        FacebookInfo objUser = new JavaScriptSerializer().Deserialize<FacebookInfo>(facebookInfoJson);
                        //txtResponse.Text = facebookInfoJson;
                        var loginResult = UserService.ValidateCustomer(objUser.email, "", out user, true);
                        if (loginResult == CustomerLoginResults.Successful)
                        {
                            //e.Authenticated = true;
                            FormsAuthenticationService.SignIn(user, true);
                            Service.UserService.CachedDefaultUser = user;
                            Response.Redirect("/");
                        }
                        else if (loginResult == CustomerLoginResults.PendingEmailVerification)
                        {
                            CurUID = objUser.email;
                            lbtnResendEmail.Attributes.Add("authtype", "4");
                            hidLoginType.Value = "3";
                        }
                        else
                        {
                            hidLoginType.Value = "5";
                            ltrEmailLoginFailure.Text = $"We do not have your email {objUser.email} within our system. Please select your email id used for registration or register your store now..";
                            //ltrResult.Text = $"The email id: {objUser.email} is not existing or not active. You may Please signup using the register link or contact support if you have registered alterady with this email id but cannot login.";
                        }

                    }
                }
                Session.Remove("LoginWith");
            }
            catch (Exception ex)
            {
                ltrResult.Text = "Failure! <br> " + ex.Message;
                //Response.Redirect("error.aspx");
            }
        }

        /// <summary>
        /// Calling 3rd party web apis. 
        /// </summary>
        /// <param name="destinationUrl"></param>
        /// <param name="methodName"></param>
        /// <param name="requestJSON"></param>
        /// <returns></returns>
        public string MakeWebRequest(string destinationUrl, string methodName, string contentType = "", string requestJSON = "")
        {
            try
            {
                HttpWebRequest request = (HttpWebRequest)WebRequest.Create(destinationUrl);
                request.Method = methodName;
                if (methodName == "POST")
                {
                    byte[] bytes = System.Text.Encoding.ASCII.GetBytes(requestJSON);
                    request.ContentType = contentType;
                    request.ContentLength = bytes.Length;
                    using (Stream requestStream = request.GetRequestStream())
                    {
                        requestStream.Write(bytes, 0, bytes.Length);
                    }
                }
                using (HttpWebResponse response = (HttpWebResponse)request.GetResponse())
                {
                    if (response.StatusCode == HttpStatusCode.OK)
                    {
                        using (StreamReader reader = new StreamReader(response.GetResponseStream()))
                        {
                            return reader.ReadToEnd();
                        }
                    }
                }

                return null;
            }
            catch (WebException webEx)
            {
                return webEx.Message;
            }
        }

        protected void lbtnResendEmail_Click(object sender, EventArgs e)
        {
            try
            {
                string authType = lbtnResendEmail.Attributes["authtype"];
                var user = (authType == "2" ? UserService.GetCustomerByMobile(CurUID) : UserService.GetCustomerByEmail(CurUID));

                string sql = $"select top 1 * from VerifyLog where verify_type=2 and mobile = @mobile and email like @email order by id desc";
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("mobile", user.Phone));
                prms.Add(new KeyValuePair<string, object>("email", user.Email));
                var tblUserLog = DataService.GetDataTable(sql, parmeters: prms);
                if (tblUserLog == null || tblUserLog.Rows.Count < 1)
                {
                    ltrResult.Text = "The user account was not validated. Please login with your mobile using OTP login and verify your email account.";
                    return;
                }

                prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("id", (int)tblUserLog.Rows[0]["id"]));
                DataService.ExecuteSql("UPDATE VerifyLog SET expire_time = DATEADD(HOUR, 1, GETUTCDATE()) WHERE id= @id", parmeters: prms);

                // Send activation email.
                var result = Core.Services.APIService.SendEmail(user.Email, "New Store Creation", (string)tblUserLog.Rows[0]["data"], user.FullName, true);
                ltrResult.Text = $"Verification link has been send to your email id: {user.Email}. Please check your inbox for the activation link.";

            }
            catch (Exception ex)
            {
                ltrResult.Text = "There is a technical error happend or your account is not validated. Please login with your mobile number and activate your email id.";
            }

        }

        protected void btnFogotPassword_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(txtForgotPswEmail.Text) || !IsValidEmail(txtForgotPswEmail.Text))
            {
                ltrResult.Text = "Invalid Email";
                return;
            }

            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("email", txtForgotPswEmail.Text));
            string sql = "select * from [User] where email = @email";
            var tblUser = DataService.GetDataTable(sql, parmeters: prms);
            if (tblUser == null || tblUser.Rows.Count < 1)
            {
                ltrResult.Text = "The account is not existing or not activated.";
                return;
            }

            bool hasVerifiedEmail = (bool)tblUser.Rows[0]["hasVerifiedEmail"];
            if (!hasVerifiedEmail)
            {
                ltrResult.Text = "Sorry, your account is not activated yet. You can login with mobile using OTP and verify/activate your email id from profile page.";
                return;
            }


            string guid = Guid.NewGuid().ToString();
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            string strActivationUrl = $"{strUrl}signup?verify={guid}";

            prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("verify_key", guid));
            prms.Add(new KeyValuePair<string, object>("verify_code", ""));
            prms.Add(new KeyValuePair<string, object>("verify_type", 1));
            prms.Add(new KeyValuePair<string, object>("verify_status", "pending"));
            prms.Add(new KeyValuePair<string, object>("details", strActivationUrl));
            prms.Add(new KeyValuePair<string, object>("mobil", (string)tblUser.Rows[0]["mobile"]));
            prms.Add(new KeyValuePair<string, object>("email", (string)tblUser.Rows[0]["email"]));
            //string strBody = "<p style='color: green'><strong>RESET ACCESS.</strong></p>" +
            //    "<p>Please click on the link or use the url provide below to reset your password. <br/>Grozeo help you phygitise your store and will support you compete with your fellow online merchants. We are just a call/ click away. Our AI enabled state-of-the-art support system will extend maximum support to you and your customers without bothering you. " +
            //    $"<br><br>{strActivationUrl}<br><br>Enjoy your new freedom to a skeumorphic retail ecosystem - Grozeo</p>";
            string strBody = "";
            List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
            replacements.Add(new KeyValuePair<string, string>("[URLPART]", strUrl.TrimEnd(new char[] { '/', '\\' })));
            replacements.Add(new KeyValuePair<string, string>("[User]", (string)tblUser.Rows[0]["FullName"]));
            replacements.Add(new KeyValuePair<string, string>("[Password Reset Link]", strActivationUrl));
            strBody = EmailService.CreateEmailbody(EmailType.ResetPassword, replacements);
            prms.Add(new KeyValuePair<string, object>("data", strBody));
            // Save log data
            DataService.ExecuteSql("INSERT INTO VerifyLog(verify_key, verify_code, verify_type, expire_time, verify_status, details, mobile, email, [data]) VALUES(@verify_key, @verify_code, @verify_type, DATEADD(HOUR, 1, GETUTCDATE()), @verify_status, @details, @mobil, @email, @data)", parmeters: prms);

            // Send activation email.
            Core.Services.APIService.SendEmail(txtForgotPswEmail.Text, "Reset Password", strBody, (string)tblUser.Rows[0]["FullName"], true);

            lblResult.Text = $"Password reset link has been send to your email id. The link will be expired after 1 hour. Please check your inbox for the reset password link.";

        }

        protected void btnshowFogotPassword_Click(object sender, EventArgs e)
        {
            hidLoginType.Value = "4";
        }

        public bool IsValidEmail(string emailaddress)
        {
            try
            {
                System.Net.Mail.MailAddress m = new System.Net.Mail.MailAddress(emailaddress);

                return true;
            }
            catch (FormatException)
            {
                return false;
            }
        }


    }

    public class GoogleToken
    {
        public string access_token { get; set; }
        public string token_type { get; set; }
        public int expires_in { get; set; }
        public string id_token { get; set; }
        public string refresh_token { get; set; }
    }
    public class GoogleInfo
    {
        public string id { get; set; }
        public string email { get; set; }
        public bool verified_email { get; set; }
        public string name { get; set; }
        public string given_name { get; set; }
        public string family_name { get; set; }
        public string picture { get; set; }
        public string locale { get; set; }
        public string gender { get; set; }
    }
    public class FacebookInfo
    {
        public string first_name { get; set; }
        public string last_name { get; set; }
        public string gender { get; set; }
        public string locale { get; set; }
        public string link { get; set; }
        public string id { get; set; }
        public string email { get; set; }
    }

    public class FacebookToken
    {
        public string access_token { get; set; }
        public string token_type { get; set; }
        public int expires_in { get; set; }
    }


}