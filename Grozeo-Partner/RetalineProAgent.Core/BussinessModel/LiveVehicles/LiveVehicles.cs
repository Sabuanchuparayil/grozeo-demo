using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.Json.Serialization;
using System.Threading.Tasks;

namespace RetalineProAgent.Core.BussinessModel.LiveVehicles
{
    public class vehicleDetails
    {
        [JsonPropertyName("v_ID")]
        public string VId { get; set; }

        [JsonPropertyName("v_No")]  
        public string VRegNo { get; set; }
        [JsonPropertyName("Latitude")]
        public object Latitude { get; set; }

        [JsonPropertyName("Longitude")]
        public object Longitude { get; set; }
        [JsonPropertyName("LastLocationDtTm")]
        public object LastUpdation { get; set; }
        [JsonPropertyName("DriverName")]
        public string DName { get; set; }
        [JsonPropertyName("Vehicletypename")]
        public string VType { get; set; }

        [JsonPropertyName("MaxLoad")]
        public int? MaxLoad { get; set; }

        [JsonPropertyName("CurrentLoad")]
        public int? CurrentLoad { get; set; }

        [JsonPropertyName("v_MapIcon")]
        public string Map { get; set; }
    }

    public class LiveVehicle
    {
        [JsonPropertyName("msg")]
        public string Message { get; set; }

        [JsonPropertyName("Data")]
        public List<vehicleDetails> VehicleDetails { get; set; }

    }
}
