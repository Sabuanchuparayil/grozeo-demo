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
    public partial class PopupUpgradeStore: Base.BasePartnerUserControl
    {
        public string TitleContent { set { ltrTitleContent.Text = value; } }
        public string HeadContent { set { ltrHeadContent.Text = value; } }
        public string BodyContent1 { set { ltrBodyContent1.Text = value; } }
        public string BodyContent2 { set { ltrBodyContent2.Text = value; } }

        public delegate void ParentCustomHandler(int type);
        public event ParentCustomHandler ParentButtonBinding;
        public string UpgradeName
        {
            get
            {
                return (string)ViewState["UPGRADESTORENAME"];
            }

            set
            {
                ViewState["UPGRADESTORENAME"] = value;
            }
        }
        public bool showServerButton
        {
            get
            {
                if (ViewState["SHOWSERVERBUTTON"] == null)
                    return false;

                try { return (bool)ViewState["SHOWSERVERBUTTON"]; } catch { }
                return false;
            }

            set
            {
                ViewState["SHOWSERVERBUTTON"] = value;
            }

        }
        protected void Page_Load(object sender, EventArgs e)
        {
            //plcUpgradeOTPClientButton.Visible = !showServerButton;
            //btnVerifyConsentOTP.Visible = !plcUpgradeOTPClientButton.Visible;
            //lblResult.Text = "";
        }

        protected void btnVerifyConsentOTP_Click(object sender, EventArgs e)
        {
            if(!Page.User.Identity.IsAuthenticated || String.IsNullOrEmpty(this.CurrentUser.Phone))
            {
                Common.ShowCustomAlert(this.Page, "Failure", "Invalid user or the session was expired.", false, "/");
                return;
            }
            //if (String.IsNullOrEmpty(txtUpgradeTOP.Text))
            //{
            //    ShowResult("Invalid OTP or verification failed!!");
            //    return;
            //}
            //var result = Core.Services.APIService.VerifyOtp(this.CurrentUser.Phone, txtUpgradeTOP.Text);
            //if (result != null && result.Data != null && result.Data.IsVerified)
            //{
            //    List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>();
            //    sqlparams.Add(new KeyValuePair<string, object>("id", this.CurrentUser.StoreGroupId));
            //    sqlparams.Add(new KeyValuePair<string, object>("username", this.CurrentUser.FullName));
            //    sqlparams.Add(new KeyValuePair<string, object>("userid", this.CurrentUser.Id));

            //    string sql = "INSERT INTO UpgradeHistory(TenantId, PackageId, [Name], CreatedBy, CreatedUserId) VALUES(@id, 2, 'Scale', @username, @userid); UPDATE AppTenant SET PackageId = 2 WHERE Id=@id";
            //    var sqlresult = DataService.ExecuteSql(sql, "", sqlparams);
            //    Service.UserService.CachedDefaultUser = null;

            //    ParentButtonBinding(1);
            //}
            //else
            //{
            //    ShowResult("Invalid OTP or verification failed!!");
            //    return;
            //    //Common.ShowToastifyMessage(this.Page, "Invalid OTP or verification failed!!", "danger");
            //}

        }

        private void ShowResult(string msg)
        {
            Type cstype = this.GetType();
            ClientScriptManager cs = Page.ClientScript;
            StringBuilder cstext1 = new StringBuilder();
            cstext1.Append("<script type=text/javascript> failureotp('"+msg.Replace("'", "")+"'); </");
            cstext1.Append("script>");
            cs.RegisterStartupScript(cstype, "UpgradeStoreScript", cstext1.ToString());

        }
    }
}