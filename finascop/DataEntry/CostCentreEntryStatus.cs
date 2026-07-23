using System;
using System.Collections.Generic;
using System.Text;

namespace DataEntry
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
