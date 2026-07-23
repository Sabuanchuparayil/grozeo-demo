using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Navigations
{
    public partial class Products: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            
            //if (this.CurrentUser.TenantType == 2 && System.Configuration.ConfigurationManager.AppSettings.Get("StoreDisableNoneVAT") == "1")
            //{
            //    Response.Redirect("/Tenant/SponsoredProducts");
            //    return;
            //}

        }
    }
}