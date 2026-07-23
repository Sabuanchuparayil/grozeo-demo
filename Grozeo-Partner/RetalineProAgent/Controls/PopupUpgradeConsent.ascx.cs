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
    public partial class PopupUpgradeConsent: Base.BasePartnerUserControl
    {

        public string TitleContent { set { ltrTitle.Text = value; } }
        public string BodyContent3 { set { ltrBodyContent3.Text = value; } }
        public string BodyContent1 { set { ltrBodyContent1.Text = value; } }
        public string BodyContent2 { set { ltrBodyContent2.Text = value; } }

        public delegate void ParentCustomHandler(int type);
        public event ParentCustomHandler ParentButtonBinding;
        //public string UpgradeName {
        //    get {
        //        return (string)ViewState["UPGRADENAME"];
        //    }

        //    set {
        //        ViewState["UPGRADENAME"] = value;
        //    }
        //}
        protected void Page_Load(object sender, EventArgs e)
        {
            //lblResult.Text = "";
        }

        protected void btnVerifyConsentOTP_Click(object sender, EventArgs e)
        {
            //var result = Core.Services.APIService.VerifyOtp(this.CurrentUser.Phone, txtConsentOTP.Text);
            //if (result != null && result.Data != null && result.Data.IsVerified)
            //{
            //    List<KeyValuePair<string, object>> sqlparams = new List<KeyValuePair<string, object>>();
            //    sqlparams.Add(new KeyValuePair<string, object>("id", this.CurrentUser.StoreGroupId));
            //    sqlparams.Add(new KeyValuePair<string, object>("username", this.CurrentUser.FullName));
            //    sqlparams.Add(new KeyValuePair<string, object>("userid", this.CurrentUser.Id));

            //    string sql = "INSERT INTO UpgradeHistory(TenantId, PackageId, [Name], CreatedBy, CreatedUserId) VALUES(@id, 2, "+String.Format("'{0}'", UpgradeName??"Add service") +", @username, @userid); UPDATE AppTenant SET PackageId = 2 WHERE Id=@id";
            //    var sqlresult = DataService.ExecuteSql(sql, "", sqlparams);


            //    ParentButtonBinding(1);
            //}
            //else
            //{
            //    Type cstype = this.GetType();
            //    ClientScriptManager cs = Page.ClientScript;
            //    StringBuilder cstext1 = new StringBuilder();
            //    cstext1.Append("<script type=text/javascript> failureotp('Invalid OTP or verification failed!!'); </");
            //    cstext1.Append("script>");
            //    cs.RegisterStartupScript(cstype, "UpgradeConsent", cstext1.ToString());

            //    return;

            //    //Common.ShowToastifyMessage(this.Page, "Invalid OTP or verification failed!!", "danger");
            //}

        }
    }
}