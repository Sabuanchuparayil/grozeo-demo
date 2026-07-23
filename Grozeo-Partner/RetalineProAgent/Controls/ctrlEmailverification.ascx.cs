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
    public partial class Emailverification : Base.BasePartnerUserControl
    {

        public KeyValuePair<int, string> CurVerificationKey
        {
            get
            {
                if (ViewState["CURVIEWVALIDATIONKEY"] == null)
                    return default;

                return (KeyValuePair<int, string>)ViewState["CURVIEWVALIDATIONKEY"];
            }
            set
            {
                ViewState["CURVIEWVALIDATIONKEY"] = value;
            }

        }
        protected void Page_Load(object sender, EventArgs e)
        {
            lblResult.Text = "";

        }

        protected void btnverify_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(txtLoginOTP.Text))
            {
                lblResult.Text = "Invalid verification code.";
                return;
            }
            var user = this.CurrentUser;
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("verify_code", txtLoginOTP.Text));
            prms.Add(new KeyValuePair<string, object>("verify_type", 2));
            prms.Add(new KeyValuePair<string, object>("verify_status", "pending"));
            prms.Add(new KeyValuePair<string, object>("mobil", user.Phone));
            prms.Add(new KeyValuePair<string, object>("email", user.Email));

            var dt = DataService.GetDataTable("select * from VerifyLog where email like @email and mobile like @mobil and verify_code = @verify_code and expire_time > getutcdate();", parmeters: prms);
            if (dt == null || dt.Rows.Count <= 0)
            {
                lblResult.Text = "Invalid verification code or the code was expired.";
                string clearScript = @"$('.Emailverificationpopup').addClass('active');";
                ScriptManager.RegisterStartupScript(this, GetType(), "ClearFieldsScript", clearScript, true);
                return;
            }
            string sql = "UPDATE [User] SET hasVerifiedEmail=1 WHERE Id=" + this.CurrentUser.Id;
            DataService.ExecuteSql(sql);
            Service.UserService.CachedDefaultUser = null;
            this.CurrentUser.HasVerifiedEmail = true;
            user = this.CurrentUser;
            user.HasVerifiedEmail = true;
            Service.UserService.CachedDefaultUser = user;
            Common.ShowToastifyMessage(this.Page, "Email verified successfully!!");

        }

        protected void btnresent_Click(object sender, EventArgs e)
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
            string clearScript = @"$('.Emailverificationpopup').addClass('active');";
            ScriptManager.RegisterStartupScript(this, GetType(), "ClearFieldsScript", clearScript, true);

        }

        protected void btnsubmitt_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(txtSetPassword1.Text))
            {
                lblResult.Text = "Invalid password 1";
                return;
            }
            else if (String.IsNullOrEmpty(txtSetPassword2.Text))
            {
                lblResult.Text = "Invalid Password 2";
                return;
            }
            else if (txtSetPassword1.Text != txtSetPassword2.Text)
            {
                lblResult.Text = "Password 1 and Password 2 are not matching";
                return;
            }



            string saltKey = EncryptionService.CreateSaltKey(5);
            string strEncPsw = EncryptionService.CreatePasswordHash(txtSetPassword1.Text, saltKey);

            string sql = "UPDATE [User] SET [Password] = @psw, [PasswordSalt] = @saltKey, [PasswordType] = 2  WHERE id= @id; ";
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("psw", strEncPsw));
            prms.Add(new KeyValuePair<string, object>("saltKey", saltKey));
            prms.Add(new KeyValuePair<string, object>("id", this.CurrentUser.Id));
            int rowsupdated = DataService.ExecuteSql(sql, parmeters: prms);
            if (rowsupdated > 0)
                Common.ShowCustomAlert(this.Page, "success", "Password set successfully",true, "/Tenant/Default");
            else
                Common.ShowToastifyMessage(this.Page, "Submitted for execution.", "info");

        }

        protected void btnchangemail_Click(object sender, EventArgs e)
        {
            plcemailverify.Visible = false;
            plcnewemailadd.Visible = true;
            plcgetotp.Visible = false;
            string clearScript = @"$('.Emailverificationpopup').addClass('active');";
            ScriptManager.RegisterStartupScript(this, GetType(), "ClearFieldsScript", clearScript, true);

        }

        protected void btnsentotp_Click(object sender, EventArgs e)
        {
            if (!Common.IsValidEmail(txtNewEmail.Text))
            {
                lblResult.Text = "Invalid email format";
                Common.ShowToastifyMessage(this.Page, "Invalid email format", "danger");
                return;
            }

            var user = this.CurrentUser;
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("email", txtNewEmail.Text));

            var dt = DataService.GetDataTable("select * from [User] where email like @email", parmeters: prms);
            if (dt != null && dt.Rows.Count > 0)
            {
                lblResult.Text = "Email is already using. Please try with a different email id.";
                Common.ShowToastifyMessage(this.Page, "Email is already using. Please try with a different email id.", "danger");
                return;
            }

            int rnd = new Random().Next(1001, 9999);
            CurVerificationKey = new KeyValuePair<int, string>(rnd, txtNewEmail.Text);
            string strBody = "";
            String strPathAndQuery = HttpContext.Current.Request.Url.PathAndQuery;
            String strUrl = HttpContext.Current.Request.Url.AbsoluteUri.Replace(strPathAndQuery, "/");
            List<KeyValuePair<string, string>> replacements = new List<KeyValuePair<string, string>>();
            replacements.Add(new KeyValuePair<string, string>("[URLPART]", strUrl.TrimEnd(new char[] { '/', '\\' })));
            replacements.Add(new KeyValuePair<string, string>("[OTP CONTENT]", rnd.ToString()));
            replacements.Add(new KeyValuePair<string, string>("[user fullName]", user.FullName));
            strBody = EmailService.CreateEmailbody(EmailType.VerifyEmail, replacements);

            // Send activation email.
            Core.Services.APIService.SendEmail(txtNewEmail.Text, "Your Authorization Code for Grozeo Merchant Account", strBody, user.FullName, true);
            Common.ShowToastifyMessage(this.Page, "Verification code has been send to your email id.");
            plcemailverify.Visible = false;
            plcnewemailadd.Visible = false;
            plcgetotp.Visible = true;
            string clearScript = @"$('.Emailverificationpopup').addClass('active');";
            ScriptManager.RegisterStartupScript(this, GetType(), "ClearFieldsScript", clearScript, true);

        }

        protected void btnVerifyEmail_Click(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(txtChangeEmailVerifyCode.Text))
            {
                lblResult.Text = "Invalid verification code.";
                return;
            }

            if (CurVerificationKey.Key > 0 && !String.IsNullOrEmpty(CurVerificationKey.Value))
            {
                if (CurVerificationKey.Key.ToString() != txtChangeEmailVerifyCode.Text)
                {
                    lblResult.Text = "Invalid verification code.";
                    return;
                }
                if (!Common.IsValidEmail(CurVerificationKey.Value))
                {
                    Common.ShowToastifyMessage(this.Page, "Invalid email format or operation failure..", "danger");
                    return;
                }

                string sql = "if not exists(select * from [User] where email like @email) begin UPDATE [User] SET Email = @email, hasVerifiedEmail=1 WHERE Id= @id end";
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("email", CurVerificationKey.Value));
                prms.Add(new KeyValuePair<string, object>("id", this.CurrentUser.Id));

                int rowsUpdated = DataService.ExecuteSql(sql, parmeters: prms);
                if (rowsUpdated <= 0)
                {
                    Common.ShowToastifyMessage(this.Page, "Failure. The data is not in a valid format or the email id is duplicating!", "danger");
                    return;
                }
                Service.UserService.CachedDefaultUser = null;
                var usr = this.CurrentUser;
                if (usr != null)
                {
                    usr.Email = CurVerificationKey.Value;
                    Service.UserService.CachedDefaultUser = usr;
                    Common.ShowCustomAlert(this.Page, "Success", "Email has been changed to " + CurVerificationKey.Value, true, "/Tenant/Default");
                    CurVerificationKey = default;
                    return;
                }
                Common.ShowToastifyMessage(this.Page, "Submitted for execution", "info");
                return;
            }
            Common.ShowToastifyMessage(this.Page, "Invalid operation", "danger");
        }

        protected void btnresentbutton_Click(object sender, EventArgs e)
        {
            CurVerificationKey = new KeyValuePair<int, string>();
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
            string strBody = $"<p style='color: green'><strong>GROZEO - Activate account.</strong></p>" +
                "<p><strong>Please use the verification code below to complete your email verification.</strong></p><br/>" +
                $"<p>Email: <b>{user.Email}</b><br/><br/>" +
                $"<p>Activation code: <b>{rnd}</b><br/><br/>" +
                "<p>The verification can be triggered from profile page by login using mobile number and OTP. Once verified, you can reset password and use your credentials to login next time, besides other options like mobile login, gmail or facebook login." +
                $"<br><br>Enjoy your new freedom to a skeumorphic retail ecosystem - Grozeo</p>";
            prms.Add(new KeyValuePair<string, object>("data", strBody));
            // Save log data
            DataService.ExecuteSql("if exists(select * from VerifyLog where email like @email and mobile like @mobil) begin UPDATE VerifyLog SET verify_code=@verify_code, expire_time = DATEADD(HOUR, 1, GETUTCDATE()) WHERE email like @email and mobile like @mobil; end else begin INSERT INTO VerifyLog(verify_key, verify_code, verify_type, expire_time, verify_status, details, mobile, email, [data]) VALUES(@verify_key, @verify_code, @verify_type, DATEADD(HOUR, 1, GETUTCDATE()), @verify_status, @details, @mobil, @email, @data); end", parmeters: prms);

            // Send activation email.
            Core.Services.APIService.SendEmail(user.Email, "Grozeo New Store - Verification", strBody, user.FullName, true);
            Common.ShowToastifyMessage(this.Page, "Verification code has been send to your email id.");

        }
    }
}