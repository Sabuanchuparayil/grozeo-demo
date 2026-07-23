using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Finascop.BussinessModel.Finascop
{
    public class SalesReport
    {

        public DateTime createdOn { get; set; }
        public int? numberOfOrders { get; set; }
        public double? sales { get; set; }
        public double? delCharges { get; set; }
        public double? taxes { get; set; }
        public double? bankCharges { get; set; }
        public double? delCharges1 { get; set; }
        public double? tcs { get; set; }
        public double? tds { get; set; }
        public double? orderRefund { get; set; }

    }
}
