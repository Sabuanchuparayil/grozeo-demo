using RetalineProAgent.Core.Services.Order;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;

namespace RetalineProAgent.Manage
{
    public partial class Default : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {
			//(new OrderService()).DelayedOrdersCountAsync()

			//var result = Task.Run(async () => await (new OrderService()).DelayedOrdersCountAsync()).GetAwaiter().GetResult();
			//ltrPendingOrder.Text = result.ToString();


		}
    }
}