using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.Dynamo
{
    public class GraphicsData
    {
        public string uuid { get; set; }
        public int storeid { get; set; }
        public string templateid { get; set; }
        public string graphicsURL { get; set; }
        public int createddate { get; set; }
        public string createdtime { get; set; }

    }
}
