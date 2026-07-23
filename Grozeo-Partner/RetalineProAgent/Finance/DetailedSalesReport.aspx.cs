using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Finance
{
    public partial class DetailedSalesReport1 : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {

        }
        protected void Page_PreRender(object sender, EventArgs e)
        {
            txtDateFrom.Enabled = seldate.Text == "1";
            txtDateTo.Enabled = seldate.Text == "1";
            pnlDateRange.Enabled = seldate.Text == "1";

        }

        protected void btninvoice_Click(object sender, EventArgs e)
        {

        }
    }
}