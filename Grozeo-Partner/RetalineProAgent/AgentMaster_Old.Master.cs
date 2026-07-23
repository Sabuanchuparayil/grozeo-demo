using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using RetalineProAgent.Service;

namespace RetalineProAgent
{
    public partial class AgentMaster_Old: Base.BasePartnerMasterPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            lnkFavIco.Href = RetalineProAgent.Service.Common.FavIcon;
            if (Page.User.Identity.IsAuthenticated && (UserService.CachedDefaultUser == null || this.CurrentUser.Id <= 0))
            {
                FormsAuthenticationService.SignOut();
                Response.Redirect("/", true);
            }
        }

    }
}