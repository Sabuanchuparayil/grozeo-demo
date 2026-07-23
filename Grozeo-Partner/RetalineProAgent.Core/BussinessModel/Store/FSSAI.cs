using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Store
{
    public class FSSAI
    {
        public bool valid { get; set; }
        public string message { get; set; }
        public string status { get; set; }
        public bool active { get; set; }
        public string entity { get; set; }
        public string reg { get; set; }
        public int uuid { get; set; }
        public string category { get; set; }
        public string state { get; set; }
        public string address { get; set; }
        public int zip { get; set; }
        public object[] products { get; set; }
    }
    [Serializable]
    public class FSSAINew
    {
        public int code { get; set; }

        public FSresult result { get; set; }
    }
    [Serializable]
    public class FSresult
    {
        public string patronId { get; set; }
        public FSSAIResult result { get; set; }
    }
    [Serializable]
    public class FSSAIResult
    {
        public string licenseNumber { get; set; }

        public string entityName { get; set; }

        public string status { get; set; }
         //public bool valid { get; set; }
        public object[] products { get; set; }

    }
}
