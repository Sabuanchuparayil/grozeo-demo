using RetalineProAgent.Core.Services;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class StoreCompletion: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            frmPublicStore.Src = $"//{this.CurrentUser.PublicSiteUrl}";
            lbtnConfirmCompletion.OnClientClick = $"window.open('https://{this.CurrentUser.PublicSiteUrl }', '_blank', 'toolbar=0,location=0,menubar=0');";
        }

        protected void lbtnConfirmCompletion_Click(object sender, EventArgs e)
        {
            List<KeyValuePair<String, Object>> tenantParmeters = new List<KeyValuePair<string, object>>();
            tenantParmeters.Add(new KeyValuePair<string, object>("tenantId", this.CurrentUser.StoreGroupId));
            DataService.ExecuteSql("UPDATE AppTenant SET Stage = 1 WHERE Stage = 9 AND Id=@tenantId", parmeters: tenantParmeters);
            Service.UserService.CachedDefaultUser = null;

            Response.Redirect("/");

        }
    }
}