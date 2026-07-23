using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Tenant
{
    public partial class invoice : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            int orderId= Convert.ToInt32(Request.QueryString["ordId"]);
            string body = Service.InvoiceService.Generateinvoicetemplate(orderId);
            ltrinvoice.Text = body;
            
        }
    }
}