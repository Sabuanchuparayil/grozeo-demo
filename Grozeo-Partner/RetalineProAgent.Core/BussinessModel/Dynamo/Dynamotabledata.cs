using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Dynamo
{
    public class SettlementData
    {
        public string order_kfc_amount { get; set; }
        public string order_tcs_utgst { get; set; }
        public string order_total_sgst { get; set; }
        public string order_tds { get; set; }
        public string order_grosstotal { get; set; }
        public string order_tdr { get; set; }
        public string order_total_gst { get; set; }
        public string deliveryConfTime { get; set; }
        public string order_tcs_sgst { get; set; }
        public string ms_id { get; set; }
        public string order_payment_gateway_tax { get; set; }
        public string order_tcs_cgst { get; set; }
        public string order_tcs_igst { get; set; }
        public string order_tdr_sgst { get; set; }
        public string order_tcs { get; set; }
        public string order_total_utgst { get; set; }
        public string order_total_cgst { get; set; }
        public string order_payment_gateway_fees { get; set; }
        public string order_tdr_igst { get; set; }
        public string order_tdr_utgst { get; set; }
        public string order_total_igst { get; set; }
        public string tstamp { get; set; }
        public string order_tdr_cgst { get; set; }
        public string uuid { get; set; }
        public string order_id { get; set; }

    }
}
