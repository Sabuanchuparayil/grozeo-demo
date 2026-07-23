using Retaline.Core.BusinessModel.Cart.Checkout;
using Retaline.Core.BusinessModel.Order;
using System.Collections.Generic;

namespace Retaline.Web.Models.Checkout
{
    public class DeliveryNote
    {
        public long OrderId { get; set; }
        public string Note { get; set; }

    }
}
