using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent
{
    public partial class DailySalesReport: Base.BasePartnerPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            txtDateFrom.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
            txtDateTo.Attributes.Add("max", DateTime.Now.ToString("yyyy-MM-dd"));
        }
    }
}