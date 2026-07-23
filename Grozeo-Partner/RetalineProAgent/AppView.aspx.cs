using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Configuration;

namespace RetalineProAgent
{
    public partial class AppView: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (ConfigurationManager.AppSettings.Get("IsDemo") != "1")
                Response.Redirect("/");
        }
    }
}