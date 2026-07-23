using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Retaline.Web.Models.Checkout
{
    public class OrderSlot
    {
        public int OrderId { get; set; }
        public int SlotId { get; set; }
        public string SlotDate { get; set; }
        public int UseWallet { get; set; }
    }
}
