using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Models.Order
{
    public class MyOrders
    {
        public List<Core.BusinessModel.Order.MyOrder> SuccessOrders { get; set; }
        public List<Core.BusinessModel.Order.MyOrder> FailedOrders { get; set; }

    }
}
