using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.VAT
{
    [Serializable]
    public class VATData
    {
        public bool active { get; set; }
        public string company_address { get; set; }
        public string company_name { get; set; }
        public string company_type { get; set; }
        public string consultation_number { get; set; }
        public string country_code { get; set; }
        public string created { get; set; }
        public string external_id { get; set; }
        public string id { get; set; }
        public string query { get; set; }
        public string requested { get; set; }
        public string type { get; set; }
        public string updated { get; set; }
        public bool valid { get; set; }
        public bool valid_format { get; set; }
        public string vat_number { get; set; }

    }

    [Serializable]
    public class TRNData
    {
        public string client_id { get; set; }
        public string trn_number { get; set; }
        public string legal_name { get; set; }
        public string[] legal_name_list { get; set; }
        public bool? trn_status { get; set; }

    }

}
