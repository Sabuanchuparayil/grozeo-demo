using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Store
{
    public class PendingActvity
    {
        public string Name { get; set; }
        public PendingActvityType Type { get; set; }
        public int Count { get; set; }
        public string Description { get; set; }
    }
    public enum PendingActvityType
    {
        Action = 1,
        Job = 2
    }
}
