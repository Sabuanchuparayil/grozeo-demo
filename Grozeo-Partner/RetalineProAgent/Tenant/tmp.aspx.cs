using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant
{
    public partial class tmp : Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }

        protected void SDSSubscriptions_Selecting(object sender, SqlDataSourceSelectingEventArgs e)
        {
            if (e.Command.Parameters.Contains("@storegroupid"))
                e.Command.Parameters["@storegroupid"].Value = this.CurrentUser.StoreGroupId;

        }

        public string LogoImage(string key)
        {
            string strLogoPath = "/content/images/p-no-image.png";
            switch (key)
            {
                case "pwa":
                    strLogoPath = "/content/images/logo/pwa_logo.png";
                    break;
                case "andriodapp":
                    strLogoPath = "/content/images/logo/Android_logo.svg";
                    break;
                case "iosapp":
                    strLogoPath = "/content/images/logo/ios-logo.jpg";
                    break;
                default:
                    strLogoPath = "/content/images/p-no-image.png";
                    break;
            }
            return strLogoPath;
        }
    }
}