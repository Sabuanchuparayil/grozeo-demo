using System;
using System.Collections.Generic;
using System.Text;

namespace DataEntry.BusinessObject
{
    public enum Phase
    {
        Order = 1, 
        Packing = 2,
        Delivery = 3,
        EndOfDay = 4,
        Settlement = 5
    }
}
