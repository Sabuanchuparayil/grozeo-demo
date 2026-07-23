using System;
using System.Collections.Generic;
using System.Text;

namespace DataEntry
{
    public class DataEntryStatus
    {
        public enum StatusCode
        {
            Success = 1,
            AccountOrParticularsIsNULL = 2,
            AccountAndParticularsSumsNotBalanced = 3,
            AccountsOrParticularsSumIsZero = 4,
            Failed_UnknownException = 5
        }
    }
}
