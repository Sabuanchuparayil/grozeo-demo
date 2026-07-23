using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Controls
{
    public partial class SideMenu: Base.BasePartnerUserControl
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            plcUsers.Visible = (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent")
                || Page.User.IsInRole("StoreAdmin") || Page.User.IsInRole("Agent"));
            //plcUserToStore.Visible= (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent")
            //    || Page.User.IsInRole("Agent"));

            plcManager.Visible = (Page.User.IsInRole("SuperAdmin") || Page.User.IsInRole("RetalineProAgent")
                || Page.User.IsInRole("Agent"));
        }
    }
}