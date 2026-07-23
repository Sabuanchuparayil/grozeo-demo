using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Finascop.BussinessModel
{
    public class CostCentreEntryStatus
    {
        public enum StatusCode
        {
            Success = 1,
            Failed = 0,
            Exception = 4,
            Undefined = 100,
        }
    }
}
