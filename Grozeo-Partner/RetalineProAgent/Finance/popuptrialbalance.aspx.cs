using RetalineProAgent.Controls;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class popuptrialbalance: Base.BasePartnerPage
    {
    
        protected void Page_Load(object sender, EventArgs e)
        {
            int parentid = Convert.ToInt32(Request.QueryString["prid"]);
            string fromdate = Request.QueryString["dtfrom"];
            string todate = Request.QueryString["dtto"];

            ctlNestedGroup1.ParentId = parentid;
            ctlNestedGroup1.FromDate = fromdate;
            ctlNestedGroup1.ToDate = todate;

        }
    }
}