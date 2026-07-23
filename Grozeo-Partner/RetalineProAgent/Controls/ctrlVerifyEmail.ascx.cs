using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls
{
    public partial class ctrlVerifyEmail: Base.BasePartnerUserControl
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            lblResult.Text = "";
        }

        protected void btnVerifyEmail_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(txtVerifyCode.Text))
            {
                lblResult.Text = "Invalid verification code.";
                ShowPopup();
                return;
            }

            //if(CurVerificationKey.Key > 0 && !String.IsNullOrEmpty(CurVerificationKey.Value))
            //{
            //    if(CurVerificationKey.Key.ToString() != txtVerifyCode.Text)
            //    {
            //        lblResult.Text = "Invalid verification code.";
            //        ShowPopup();
            //        return;
            //    }
            //    int rowsUpdated = DataService.ExecuteSql("if not exists(select * from [User] where email like @email) begin UPDATE [User] SET Email = @email, hasVerifiedEmail=1 WHERE Id= @id end");
            //    if(rowsUpdated <= 0)
            //    {
            //        Common.ShowToastifyMessage(this.Page, "Failure. The data is not in a valid format or the email id is duplicating!", "danger");
            //        ShowPopup();
            //        return;
            //    }
            //    Service.UserService.CachedDefaultUser = null;
            //    var usr = this.CurrentUser;
            //    if(usr != null)
            //    {
            //        usr.Email = CurVerificationKey.Value;
            //        this.CurrentUser = usr;
            //        Common.ShowCustomAlert(this.Page, "Success", "Email has been changed to "+ CurVerificationKey.Value, true, "/");
            //        CurVerificationKey = default;
            //        return;
            //    }
            //    Common.ShowToastifyMessage(this.Page, "Submitted for execution", "info");
            //    return;
            //}

            var user = this.CurrentUser;
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("verify_code", txtVerifyCode.Text));
            prms.Add(new KeyValuePair<string, object>("verify_type", 2));
            prms.Add(new KeyValuePair<string, object>("verify_status", "pending"));
            prms.Add(new KeyValuePair<string, object>("mobil", user.Phone));
            prms.Add(new KeyValuePair<string, object>("email", user.Email));

            var dt = DataService.GetDataTable("select * from VerifyLog where email like @email and mobile like @mobil and verify_code = @verify_code and expire_time > getutcdate();", parmeters: prms);
            if(dt == null || dt.Rows.Count <= 0)
            {
                //Common.ShowCustomAlert()
                lblResult.Text = "Invalid verification code or the code was expired.";
                ShowPopup();
                return;
            }

            string sql = "UPDATE [User] SET hasVerifiedEmail=1 WHERE Id="+ this.CurrentUser.Id;
            DataService.ExecuteSql(sql);

            Service.UserService.CachedDefaultUser = null;
            this.CurrentUser.HasVerifiedEmail = true;
            user = this.CurrentUser;
            user.HasVerifiedEmail = true;
            Service.UserService.CachedDefaultUser = user;
            Common.ShowToastifyMessage(this.Page, "Email verified successfully!!");

            ShowPopup("$('#modalsetpsw').modal('show'); ", true);
            //Common.ShowCustomAlert(this.Page, "Verification completed", "Email verified successfully!!", true, "/");

        }

        protected void lbtnResendCode_Click(object sender, EventArgs e)
        {
            string guid = Guid.NewGuid().ToString();
            int rnd = new Random().Next(1001, 9999);
            var user = this.CurrentUser;
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("verify_key", guid));
            prms.Add(new KeyValuePair<string, object>("verify_code", rnd));
            prms.Add(new KeyValuePair<string, object>("verify_type", 2));
            prms.Add(new KeyValuePair<string, object>("verify_status", "pending"));
            prms.Add(new KeyValuePair<string, object>("details", "")); //strActivationUrl));
            prms.Add(new KeyValuePair<string, object>("mobil", user.Phone));
            prms.Add(new KeyValuePair<string, object>("email", user.Email));
            //string strBody = $"<p style='color: green'><strong>GROZEO - Activate account.</strong></p>" +
            //    "<p><strong>Please use the verification code below to complete your email verification.</strong></p><br/>" +
            //    $"<p>Email: <b>{user.Email}</b><br/><br/>" +
            //    $"<p>Activation code: <b>{rnd}</b><br/><br/>" +
            //    "<p>The verification can be triggered from profile page by login using mobile number and OTP. Once verified, you can reset password and use your credentials to login next time, besides other options like mobile login, gmail or facebook login." +
            //    $"<br><br>Enjoy your new freedom to a skeumorphic retail ecosystem - Grozeo</p>";
            string strBody = "";
            prms.Add(new KeyValuePair<string, object>("data", strBody));
            // Save log data
            DataService.ExecuteSql("if exists(select * from VerifyLog where email like @email and mobile like @mobil and expire_time >= getutcdate()) begin UPDATE VerifyLog SET verify_code=@verify_code, expire_time = DATEADD(HOUR, 1, GETUTCDATE()) WHERE email like @email and mobile like @mobil and expire_time >= getutcdate(); end else begin INSERT INTO VerifyLog(verify_key, verify_code, verify_type, expire_time, verify_status, details, mobile, email, [data]) VALUES(@verify_key, @verify_code, @verify_type, DATEADD(HOUR, 1, GETUTCDATE()), @verify_status, @details, @mobil, @email, @data); end", parmeters: prms);

            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
            replacements.Add(new KeyValuePair<string, string>("[URLPART]", strUrl.TrimEnd(new char[] { '/', '\\' })));
            replacements.Add(new KeyValuePair<string, string>("[OTP CONTENT]", rnd.ToString()));
            replacements.Add(new KeyValuePair<string, string>("[user fullName]", user.FullName));
            strBody = EmailService.CreateEmailbody(EmailType.VerifyEmail, replacements);
            // Send activation email.
            Core.Services.APIService.SendEmail(user.Email, "Your Authorization Code for Grozeo Merchant Account", strBody, user.FullName, true);
            Common.ShowToastifyMessage(this.Page, "Verification code has been send to your email id.");
            ShowPopup();

        }

        private void ShowPopup(string additionalScript="", bool replace=false)
        {
            string strSCript = (replace ? additionalScript : "$('#modalverifyemail').modal('show'); " + additionalScript); //@"$('#modalverifyemail').modal('show'); "+ additionalScript;
            Type cstype = this.Page.GetType();
            String csname1 = "ShowEmailVerificationBox";
            ClientScriptManager cs = this.Page.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append($"<script type=text/javascript> {strSCript} </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, csname1, cstext1.ToString());

        }

    }
}