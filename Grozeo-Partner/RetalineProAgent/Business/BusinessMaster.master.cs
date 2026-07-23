using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Business
{
    public partial class BusinessMaster: Base.BasePartnerMasterPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            var _partner = this.CurrentUser;

            if ((_partner.AreaId == null || _partner.AreaId <= 0) && !(this.Page.ToString().ToLower() == "asp.business_area_aspx")) // || this.Page.ToString().ToLower() == "asp.storesettings_aspx"))
            {
                Response.Redirect("/Business/Area", true);
                return;
            }


        }
    }
}