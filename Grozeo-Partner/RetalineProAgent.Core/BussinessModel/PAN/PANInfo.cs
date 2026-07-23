using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.PAN
{
    [Serializable]
    public class PANInfo
    {
        public Result result { get; set; }
    }
    [Serializable]
    public class Result
    {
        public Essentials essentials { get; set; }
        public string id { get; set; }
        public string patronId { get; set; }
        public string task { get; set; }
        public PANResult result { get; set; }
    }

    [Serializable]
    public class Essentials
    {
            public string number { get; set; }
    }

    [Serializable]
    public class PANResult
    {
        public string name { get; set; }
        public string number { get; set; }
        public string typeOfHolder { get; set; }
        public bool isIndividual { get; set; }
        public bool isValid { get; set; }
        public string firstName { get; set; }
        public string middleName { get; set; }
        public string lastName { get; set; }
        public string title { get; set; }
        public string panStatus { get; set; }
        public string panStatusCode { get; set; }
        public string aadhaarSeedingStatus { get; set; }
        public string aadhaarSeedingStatusCode { get; set; }
        public string lastUpdatedOn { get; set; }

    }
}
