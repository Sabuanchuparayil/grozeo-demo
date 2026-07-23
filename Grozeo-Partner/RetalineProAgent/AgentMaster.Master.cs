using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

using RetalineProAgent.Service;

namespace RetalineProAgent
{
    public partial class AgentMaster : Base.BasePartnerMasterPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
			if (!String.IsNullOrEmpty(Request.QueryString["withpostcode"]) && Request.QueryString["withpostcode"] == "1")
			{
				Session.Add("SHOWPOSTCODER", 1);
			}

			User _user = (Page.User.Identity.IsAuthenticated ? this.CurrentUser : null);
            lnkFavIco.Href = RetalineProAgent.Service.Common.FavIcon;
            if (Page.User.Identity.IsAuthenticated && (_user == null || _user.Id <= 0))
            {
                FormsAuthenticationService.SignOut();
                Response.Redirect("/", true);
                return;
            }




        }
        protected void Page_PreRender(object sender, EventArgs e) {
            if (Session["SHOWPOSTCODER"] != null && (int)Session["SHOWPOSTCODER"] == 1)
                plsHeaderPostcoder.Visible = true;

        }


    }
}