using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Retaline.Core.BusinessModel.Cart;

namespace Retaline.Web.Models.Cart
{
    public class CartItem
    {
        public CartDetails Product { get; set; }
        public CartNearestItem DeliveryProduct { get; set; }
        public int DeliveryType { get; set; }
    }
}
