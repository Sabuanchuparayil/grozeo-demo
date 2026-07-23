using RetalineProAgent.Core.Services.Drivers;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Threading.Tasks;
using static System.Net.Mime.MediaTypeNames;

namespace RetalineProAgent.Core.Services.Drivers
{
    #region Models
    public class VehicleDetail
    {
        public string apikey { get; set; }
        public string v_id { get; set; }
        public string v_no { get; set; }
        public double Latitude { get; set; }
        public double Longitude { get; set; }
        public DateTime? LocationUpdateddatetime { get; set; }
        public string DriverName { get; set; }
        public string v_typename { get; set; }
        public decimal v_capacity { get; set; }
        public decimal CurrentLoadedWeight { get; set; }
        public string v_MapIcon { get; set; }
    }

    public class VehicleResponse
    {
        public bool Success { get; set; }
        public string Msg { get; set; }
        public List<VehicleDetail> Vehicles { get; set; } = new List<VehicleDetail>();
    }

    public class PartitionKeyModel
    {
        public string Col { get; set; }
        public object Val { get; set; }
        public string Oper { get; set; } = "=";
    }

    public class SortKeyModel
    {
        public string Col { get; set; }
        public object Val1 { get; set; }
        public object Val2 { get; set; }
        public string Oper { get; set; } = "=";
        public bool Between { get; set; } = false;
    }

    public class ConditionModel
    {
        public string Col { get; set; }
        public object Val { get; set; }
        public object Val1 { get; set; }
        public object Val2 { get; set; }
        public string Oper { get; set; }
        public bool Between { get; set; } = false;
    }
    #endregion

    #region Geo Utility
    public class GeoUtilities
    {
        public (double lon1, double lon2, double lat1, double lat2) GetDegreeMatrix(double mylon, double mylat, double dist)
        {
            double kmtomile = dist * 0.623;

            double lon1 = mylon - kmtomile / Math.Abs(Math.Cos(ToRadians(mylat)) * 69);
            double lon2 = mylon + kmtomile / Math.Abs(Math.Cos(ToRadians(mylat)) * 69);
            double lat1 = mylat - (kmtomile / 69);
            double lat2 = mylat + (kmtomile / 69);

            return (lon1, lon2, lat1, lat2);
        }

        private double ToRadians(double angle) => (Math.PI / 180) * angle;
    }
    #endregion

    #region Vehicle Service

    public class VehicleService
    {
        public VehicleResponse ListLiveVehicles(int br_id, int storeGroupId)
        {
            var vehicles = new List<VehicleDetail>();
            int dateInt = int.Parse(DateTime.Now.ToString("yyyyMMdd"));

            string tablePrefix = System.Configuration.ConfigurationManager.AppSettings.Get("AWS_Prefix");
            string tableName = tablePrefix + "QugeoLiveVehicles";

            try
            {
                // Define which attributes to fetch
                var queryAttributes = new List<string>
                    {
                        "apikey", "v_id", "v_no", "Latitude", "Longitude",
                        "LocationUpdateddatetime", "DriverName", "v_typename",
                        "v_capacity", "CurrentLoadedWeight", "v_MapIcon"
                    };

                // Determine branch IDs to query
                List<int> branchIds = new List<int>();
                if (br_id > 0)
                {
                    branchIds.Add(br_id);
                }
                else if (storeGroupId > 0)
                {
                    branchIds = GetBranchesByStoreGroup(storeGroupId);
                }

                // Call DynamoService synchronously
                //var vehicleItems = DynamoService.ReadFromDynamoDB(
                //    tableName: tableName,
                //    br_id: br_id,
                //    createdDate: dateInt,
                //    onlyLive: true,
                //    queryAttributes: queryAttributes
                //);

                foreach (var branch in branchIds)
                {
                    var vehicleItems = DynamoService.ReadFromDynamoDB(
                        tableName: tableName,
                        br_id: branch,
                        createdDate: dateInt,
                        onlyLive: true,
                        queryAttributes: queryAttributes
                    );

                    // Map DynamoDB items to VehicleDetail objects
                    foreach (var item in vehicleItems)
                    {
                        var vehicle = new VehicleDetail
                        {
                            apikey = item.ContainsKey("apikey") ? item["apikey"].S : string.Empty,
                            v_id = item.ContainsKey("v_id") ? item["v_id"].N : string.Empty,
                            v_no = item.ContainsKey("v_no") ? item["v_no"].N : string.Empty,
                            Latitude = item.ContainsKey("Latitude") ? double.Parse(item["Latitude"].N) : 0,
                            Longitude = item.ContainsKey("Longitude") ? double.Parse(item["Longitude"].N) : 0,
                            LocationUpdateddatetime = item.ContainsKey("LocationUpdateddatetime")
                                ? ParseDynamoDate(item["LocationUpdateddatetime"].N)
                                : (DateTime?)null,
                            DriverName = item.ContainsKey("DriverName") ? item["DriverName"].S : string.Empty,
                            v_typename = item.ContainsKey("v_typename") ? item["v_typename"].S : string.Empty,
                            v_capacity = item.ContainsKey("v_capacity") ? decimal.Parse(item["v_capacity"].N) : 0,
                            CurrentLoadedWeight = item.ContainsKey("CurrentLoadedWeight") ? decimal.Parse(item["CurrentLoadedWeight"].N) : 0,
                            v_MapIcon = item.ContainsKey("v_MapIcon") ? item["v_MapIcon"].S : string.Empty
                        };

                        vehicles.Add(vehicle);
                    }
                }
                return new VehicleResponse { Vehicles = vehicles };
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Error fetching live vehicles: {ex}");
                return new VehicleResponse { Vehicles = new List<VehicleDetail>() };
            }
        }

        private DateTime? ParseDynamoDate(string numericValue)
        {
            if (string.IsNullOrWhiteSpace(numericValue))
                return null;

            if (long.TryParse(numericValue, out long dtLong))
            {
                string str = dtLong.ToString();
                if (DateTime.TryParseExact(str, "yyyyMMddHHmmss", null, System.Globalization.DateTimeStyles.None, out DateTime result))
                    return result;
                if (DateTime.TryParseExact(str, "yyyyMMdd", null, System.Globalization.DateTimeStyles.None, out result))
                    return result;
                if (dtLong > 1000000000 && dtLong < 9999999999)
                    return DateTimeOffset.FromUnixTimeSeconds(dtLong).DateTime;
                if (dtLong > 1000000000000 && dtLong < 9999999999999)
                    return DateTimeOffset.FromUnixTimeMilliseconds(dtLong).DateTime;
            }
            return null;
        }

        private List<int> GetBranchesByStoreGroup(int storeGroupId)
        {
            var branchIds = new List<int>();
            var dtBranches = DataServiceMySql.GetDataTable(
                $"SELECT br_ID FROM finascop_branch WHERE br_storeGroup = {storeGroupId} AND br_status = 'Active'"
            );

            foreach (DataRow row in dtBranches.Rows)
            {
                branchIds.Add(Convert.ToInt32(row["br_ID"]));
            }

            return branchIds;
        }

        #endregion


        #region Vehicle Facade
        public static VehicleResponse LoadVehicleDetails(int br_id, double longitude, double latitude, int userType, int userId)
        {
            //if (br_id <= 0)
            //    throw new ArgumentException("Invalid branch id");
            //if (longitude == 0)
            //    throw new ArgumentException("Invalid longitude");
            //if (latitude == 0)
            //    throw new ArgumentException("Invalid latitude");

            var sql = DataServiceMySql.GetDataTable($"SELECT br_storeGroup, br_Name FROM finascop_branch WHERE br_ID = {br_id}");
            if (sql == null || sql.Rows.Count == 0)
                return new VehicleResponse { Success = false, Msg = "Branch not found", Vehicles = new List<VehicleDetail>() };

            string storeGroupId = sql.Rows[0]["br_storeGroup"].ToString();

            // Get pickup circle distance
            string sqlDist = "SELECT cfg_Value FROM sys_configuration WHERE cfg_Name = 'QC_VEHICLE_PICKUP_CIRCLE_DIST'";
            var distTable = DataServiceMySql.GetDataTable(sqlDist);
            if (distTable == null || distTable.Rows.Count == 0)
                throw new Exception("Pickup circle distance not configured");

            double pickupCircleDist = Convert.ToDouble(distTable.Rows[0]["cfg_Value"]);

            // Calculate degree matrix
            var geo = new GeoUtilities();
            var (lon1, lon2, lat1, lat2) = geo.GetDegreeMatrix(longitude, latitude, pickupCircleDist);

            // DynamoDB attributes
            var queryAttributes = new List<string>
            {
                "apikey", "v_id", "v_no", "Latitude", "Longitude",
                "LocationUpdateddatetime", "DriverName", "v_typename",
                "v_capacity", "CurrentLoadedWeight", "v_MapIcon"
            };

            var attVehicles = new Dictionary<string, object>
            {
                ["PartitionKey"] = new PartitionKeyModel { Col = "Is_Live", Val = 1 },
                ["SortKey"] = new SortKeyModel { Col = "Latitude", Val1 = lat1, Val2 = lat2, Between = true },
                ["IndexName"] = "Is_Live-Latitude-index",
                ["queryAttributes"] = queryAttributes,
                ["Condition"] = new List<ConditionModel>
            {
                new ConditionModel { Col = "Longitude", Val1 = lon1, Val2 = lon2, Between = true }
            }
            };

            var conditions = (List<ConditionModel>)attVehicles["Condition"];
            if (userType > 0 && userId > 0)
            {
                conditions.Add(new ConditionModel { Col = "createdBy", Val = userType, Oper = "=" });
                conditions.Add(new ConditionModel { Col = "sourceId", Val = userId, Oper = "=" });
            }
            else
            {
                conditions.Add(new ConditionModel { Col = "createdBy", Val = 1, Oper = "=" });
                conditions.Add(new ConditionModel { Col = "sourceId", Val = storeGroupId, Oper = "=" });
            }

            // DynamoDB table
            string tablePrefix = System.Configuration.ConfigurationManager.AppSettings["AWS_Prefix"];
            string tableName = tablePrefix + "QugeoLiveVehicles";

            // Read from DynamoDB
            var vehicleItems = DynamoService.ReadFromDynamoDB(
                tableName: tableName,
                br_id: br_id,
                createdDate: int.Parse(DateTime.Now.ToString("yyyyMMdd")),
                onlyLive: true,
                queryAttributes: queryAttributes
            );

            var vehicles = new List<VehicleDetail>();
            if (vehicleItems != null && vehicleItems.Count > 0)
            {
                foreach (var item in vehicleItems)
                {
                    vehicles.Add(new VehicleDetail
                    {
                        apikey = item.ContainsKey("apikey") ? item["apikey"].S : string.Empty,
                        v_id = item.ContainsKey("v_id") ? item["v_id"].N : string.Empty,
                        v_no = item.ContainsKey("v_no") ? item["v_no"].S : string.Empty,
                        Latitude = item.ContainsKey("Latitude") ? double.Parse(item["Latitude"].N) : 0,
                        Longitude = item.ContainsKey("Longitude") ? double.Parse(item["Longitude"].N) : 0,
                        LocationUpdateddatetime = item.ContainsKey("LocationUpdateddatetime") ? ParseDynamoDateTime(item["LocationUpdateddatetime"].N) : (DateTime?)null,
                        DriverName = item.ContainsKey("DriverName") ? item["DriverName"].S : string.Empty,
                        v_typename = item.ContainsKey("v_typename") ? item["v_typename"].S : string.Empty,
                        v_capacity = item.ContainsKey("v_capacity") ? decimal.Parse(item["v_capacity"].N) : 0,
                        CurrentLoadedWeight = item.ContainsKey("CurrentLoadedWeight") ? decimal.Parse(item["CurrentLoadedWeight"].N) : 0,
                        v_MapIcon = item.ContainsKey("v_MapIcon") ? item["v_MapIcon"].S : string.Empty
                    });
                }

                return new VehicleResponse
                {
                    Success = true,
                    Msg = "Vehicle details fetched successfully",
                    Vehicles = vehicles
                };
            }

            return new VehicleResponse
            {
                Success = false,
                Msg = "No vehicles found",
                Vehicles = new List<VehicleDetail>()
            };
        }

        private static DateTime? ParseDynamoDateTime(string numericValue)
        {
            if (string.IsNullOrWhiteSpace(numericValue))
                return null;

            if (long.TryParse(numericValue, out long dtLong))
            {
                string str = dtLong.ToString();
                if (DateTime.TryParseExact(str, "yyyyMMddHHmmss", null, System.Globalization.DateTimeStyles.None, out DateTime result))
                    return result;
                if (DateTime.TryParseExact(str, "yyyyMMdd", null, System.Globalization.DateTimeStyles.None, out result))
                    return result;
                if (dtLong > 1000000000 && dtLong < 9999999999)
                    return DateTimeOffset.FromUnixTimeSeconds(dtLong).DateTime;
                if (dtLong > 1000000000000 && dtLong < 9999999999999)
                    return DateTimeOffset.FromUnixTimeMilliseconds(dtLong).DateTime;
            }
            return null;
        }

        public static List<VehicleDetail> LoadVehicleDetailsForBinding(int br_id, double longitude, double latitude, int userType, int userId)
        {
            var response = LoadVehicleDetails(br_id, longitude, latitude, userType, userId);
            return response != null && response.Success ? response.Vehicles : new List<VehicleDetail>();
        }
    }
    #endregion
}


