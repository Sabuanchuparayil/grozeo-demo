using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.ContactArea
{
    public class AreaDetails
    {
        public int id { get; set; }
        public string areaName { get; set; }
        public int areaSpan { get; set; }
        public string areaLatitude { get; set; }
        public string areaLongitude { get; set; }
        public int areaBusinessAssociate { get; set; }
        public double distance { get; set; }
        public string areaLocation { get; set; }
        public string areaCreatedOn { get; set; }
        public int areaCreatedBy { get; set; }

    }

    //"{\"status\":\"ok\",
    //\"default_currency\":null,
    //\"data\":{\"status\":\"success\",\"data\":null,\"message\":\"\"}}"
}
