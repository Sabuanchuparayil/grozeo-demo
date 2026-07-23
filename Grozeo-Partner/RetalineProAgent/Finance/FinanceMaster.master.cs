using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.HtmlControls;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class FinanceMaster: Base.BasePartnerMasterPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            //if (!IsPostBack)
            //{
            //    HtmlGenericControl body = (HtmlGenericControl)Master.FindControl("agentBody");
            //    body.Attributes.Add("class", "ribbon_version slim-full-width slim-sticky-header slim-sticky-sidebar hide-sidebar");
            //}
        }
    }
}