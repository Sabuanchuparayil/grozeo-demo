using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Store
{
    public class BankAccount
    {
        public bool valid { get; set; }
        public string name { get; set; }
        public IFSC ifsc { get; set; }
        public string status { get; set; }
    }
    public class IFSC
    {
        public bool valid { get; set; }
        public string bank { get; set; }
        public string code { get; set; }
        public string ifsc { get; set; }
        public string micr { get; set; }
        public string branch { get; set; }
        public string city { get; set; }
        public string district { get; set; }
        public string state { get; set; }
        public string address { get; set; }
        public bool neft { get; set; }
        public bool imps { get; set; }
        public bool rtgs { get; set; }
    }

    public class BankAccountPostCoder
    {
        public bool valid { get; set; }
        public int stateid { get; set; }
        public string sortcode { get; set; }
        public string accountnumber { get; set; }
        public bool directdebits { get; set; }
        public bool fasterpayments { get; set; }
        public bool chaps { get; set; }
        public bool bacs { get; set; }
        public string bankbic { get; set; }
        public string branchbic { get; set; }
        public string bankname { get; set; }
        public string branchname { get; set; }
        public string addressline1 { get; set; }
        public string addressline2 { get; set; }
        public string addressline3 { get; set; }
        public string addressline4 { get; set; }
        public string posttown { get; set; }
        public string postcode { get; set; }
        public string phone1 { get; set; }
        public string phone2 { get; set; }

    }
}
