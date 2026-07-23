
using Microsoft.Azure.Management.WebSites.Models;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using NPOI.SS.Formula.Eval;
using NPOI.SS.Formula.Functions;
using RetalineProAgent.Core.Services;
using RetalineProAgent.Core.Services.Cache;
using RetalineProAgent.Core.Services.Drivers;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Data;
using System.Dynamic;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Net.Http.Headers;
using System.Security.Cryptography.X509Certificates;
using System.Text;
using System.Threading.Tasks;
using System.Web.Http;
using System.Web.Script.Serialization;

namespace RetalineProAgent.Controller
{
    public class HomeController : ApiController
    {
        // GET api/<controller>
        public IEnumerable<string> Get()
        {
            return new string[] { "value1", "value2" };
        }

        // GET api/<controller>/5
        //public IHttpActionResult Get(int id)
        //{
        //    try
        //    {
        //        if (id > 0)
        //            return Json(new { result = 1, status = "Success", data = PendingActions(true) });
        //        else
        //            return Json(new { result = 1, status = "Success", data = PendingActions() });
        //    }
        //    catch
        //    {
        //        return Json(new { result = 0, status = "Error", message = "Failure", data = -1 });
        //    }
        //    //return "value";
        //}

        // POST api/<controller>
        //public void Post([FromBody] string value)
        //{
        //}

        // PUT api/<controller>/5
        public void Put(int id, [FromBody] string value)
        {
        }

        // DELETE api/<controller>/5
        public void Delete(int id)
        {
        }

        public class DashboardDataFromAWSLambda
        {
            public int totalorders { get; set; }
            public int neworders { get; set; }
        }

        public enum Result
        {
            Success = 1,
            Failed = 2,
            Error = 3,
            Exception = 4,
            NoData = 5,
            Undefined = 100,
        }

        public class DashboardResponseAWSLambda
        {
            public Result result { get; set; }
            public string status { get; set; }
            public string message { get; set; }
            public DashboardDataFromAWSLambda data { get; set; }
        }
        public class DashboardData
        {
            public int orderpickers { get; set; }
            public int neworders { get; set; }
            public int forsale { get; set; }
            public int drivers { get; set; }
            public int onlineOrderPickers { get; set; }
            public int onlineVehicles { get; set; }
            public int totalorders { get; set; }
            public int totalsales { get; set; }
            public int totalcustomers { get; set; }
            public int outOfStock { get; set; }
        }

        public class DashboardResponse
        {
            public int result { get; set; }
            public string status { get; set; }
            public DashboardData data { get; set; }
        }

        public class StoreGroupParams
        {
            public int storeGroupID { get; set; }
            public List<int> BranchIDs { get; set; }
        }

        [HttpPost]
        public async Task<IHttpActionResult> DashboardValueUpdate([FromBody] StoreGroupParams storeGroupParams)
        {
            int branchId = 0;
            if (User.IsInRole("BranchManager"))
            {
                Service.User usr = Service.UserService.CachedDefaultUser;
                branchId = usr.APIRoleBranchId;
                if(branchId > 0)
                    storeGroupParams.BranchIDs = new List<int> { branchId };
            }

            // Create an instance of HttpClient
            using (var client = new HttpClient())
            {
                // Define the API Gateway URL
                string apiUrl = ConfigurationManager.AppSettings.Get("ALGOrderTotalUrl");

                // Create an HttpClient instance
                using (var httpClient = new HttpClient())
                {
                    // Prepare the input JSON object for the API call
                    var apiInput = new
                    {
                        storeGroupID = storeGroupParams.storeGroupID,
                        branchIDs = storeGroupParams.BranchIDs?.ToArray() ?? new int[] { }
                    };

                    client.DefaultRequestHeaders.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));

                    // Serialize the C# object to a JSON string
                    var jsonInput = JsonConvert.SerializeObject(apiInput);
                    var httpContent = new StringContent(jsonInput, Encoding.UTF8, "application/json");

                    var jsonResponse = await httpClient.PostAsync(apiUrl, httpContent);
                    var jsonString = await jsonResponse.Content.ReadAsStringAsync();

                    //dynamic data = System.Text.Json.JsonSerializer.Deserialize<DynamicObject>(jsonString);

                    var dynamicObject = JsonConvert.DeserializeAnonymousType(jsonString, 
                        new {result = 0, status = string.Empty, message = string.Empty, data = string.Empty
                        } 
                    );

                    if (dynamicObject.result == 1)
                    {                        // 
                        var dynamicResult = JsonConvert.DeserializeAnonymousType(dynamicObject.data, new { totalOrders = 0, delayedOrders = 0 });
                        return Json(new
                        {
                            result = 1,
                            status = "Success",
                            data = new
                            {
                                totalOrders = dynamicResult.totalOrders,
                                delayedOrders = dynamicResult.delayedOrders
                            }
                        });
                    }

                    return Json(new { result = 0, status = "Failure" });
                }

            }
        }

        [HttpPost]
        public async Task<IHttpActionResult> DashboardValues([FromBody] HomeAPIParams homeAPIParams)
        {
            if (homeAPIParams.isPendingOrders == 1)
            {
                try
                {
                    return Json(new { result = 1, status = "Success", data = GetPendingOrders() });
                }
                catch
                {
                    return Json(new { result = 0, status = "Error", message = "Failure", data = new { } });
                }
            }

            Service.User usr = Service.UserService.CachedDefaultUser;
            var cacheService = new RedisCacheService();
            string cacheKey = $"Retl.AppTenant.pendingtasks.count.{usr.APIStoreId}";

            int branchId = 0;
            if (User.IsInRole("BranchManager"))
            {
                branchId = usr.APIRoleBranchId;
                //branchId = UserService.UserRoleBranchId;
            }

            var result = await cacheService.GetAsync<DashboardResponse>(cacheKey, async () =>
            {
                string strDashboardVals = @"
                    SELECT COUNT(*) FROM retaline_godown_boy WHERE is_offline <> 1 AND `status`=1 AND (@branchid <= 0 OR branch_id = @branchid) AND branch_id IN (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storeGroup) UNION ALL 
                    SELECT COUNT(*) FROM retaline_customer_order INNER JOIN finascop_branch ON br_ID = order_branch_id AND (@branchid <= 0 OR br_ID = @branchid) AND finascop_branch.br_storeGroup=@storeGroup WHERE status_id IN (5,6,7,9) AND status_id > 0 UNION ALL 
                    SELECT COUNT(DISTINCT stit_id) FROM finascop_stock_branch_inventory WHERE (@branchid <= 0 OR branch_id = @branchid) AND branch_id IN(SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storeGroup) UNION ALL 
                    SELECT COUNT(*) FROM qugeo_driver WHERE (@branchid <= 0 OR br_id = @branchid) AND br_id IN (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storeGroup) UNION ALL 
                    SELECT COUNT(*) FROM retaline_godown_boy WHERE (@branchid <= 0 OR branch_id = @branchid) AND branch_id IN (SELECT br_ID FROM finascop_branch WHERE br_storeGroup = @storeGroup) UNION ALL 
                    SELECT COUNT(*) FROM retaline_customer_order co INNER JOIN finascop_stock_transfer_order sto ON sto.fstr_id = order_id WHERE (@branchid <= 0 OR co.order_branch_id = @branchid) AND co.storegroup_id = @storeGroup AND status_id NOT IN(0,1,2,21,19,24,34) UNION ALL
                    SELECT COUNT(*) FROM retaline_customer_order WHERE (@branchid <= 0 OR order_branch_id = @branchid) AND order_delivered_date IS NOT NULL   AND order_delivered_date > '1000-01-01 00:00:00' AND storegroup_id = @storeGroup UNION ALL
                    SELECT COUNT(*) FROM retaline_customer WHERE storegroup_id=@storeGroup OR cust_id IN( SELECT o.order_customer_id FROM retaline_customer_order o INNER JOIN finascop_branch b ON o.order_branch_id=b.br_ID WHERE (@branchid <= 0 OR b.br_ID = @branchid) AND b.br_storegroup=@storeGroup ) UNION ALL 
                    SELECT COUNT(DISTINCT stit_id) FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id AND (@branchid <= 0 OR b.br_ID = @branchid) LEFT JOIN (SELECT item_id, SUM(`count`) AS blockedNum FROM finascop_stock_blocked GROUP BY item_id) blocked ON blocked.item_id = bi.stit_id WHERE br_status= 'Active' AND b.br_storeGroup= @storeGroup AND (IFNULL(bi.item_count, 0) - IFNULL(blockedNum, 0)) > 0  AND selling_price > 0
                ";

            var input = new List<KeyValuePair<string, object>>
            {
                new KeyValuePair<string, object>("storeGroup", usr.APIStoreId),
                new KeyValuePair<string, object>("branchid", branchId)
            };

                var tblItems = DataServiceMySql.GetDataTable(strDashboardVals, Service.UserService.GetAPIConnectionString(), input);

                int onlineVehicles = 0;

                try
                {
                    var vehicleService = new VehicleService();
                    var liveVehiclesResponse = vehicleService.ListLiveVehicles(0, usr.APIStoreId); 
                    if (liveVehiclesResponse?.Vehicles != null)
                        onlineVehicles = liveVehiclesResponse.Vehicles.Count;
                }
                catch (Exception ex)
                {
                    string strmsg = ex.Message;
                }

                int[] values = new int[9];
                for (int i = 0; i < 9; i++)
                {
                    if (tblItems?.Rows.Count > i)
                    {
                        try { values[i] = Convert.ToInt32(tblItems.Rows[i][0]); } catch { values[i] = 0; }
                    }
                }

                int onlineOrderPickers = values[0];
                int totalOrderPickers = values[4];
                int totalDrivers = values[3];

                var data = new DashboardData
                {
                    orderpickers = values[4],
                    neworders = values[1],
                    forsale = values[2],
                    drivers = values[3],
                    onlineOrderPickers = Math.Min(values[0], values[4]),
                    onlineVehicles = Math.Min(onlineVehicles, values[3]),
                    totalorders = values[5],
                    totalsales = values[6],
                    totalcustomers = values[7],
                    outOfStock = values[8]
                };

                return new DashboardResponse
                {
                    result = 1,
                    status = "Success",
                    data = data
                };
            }, TimeSpan.FromMinutes(10));

            return Json(result);
        }

        public IHttpActionResult OrderStatus([FromBody] JObject requestBody)
        {
            int status = 0;
            string order_orderID = requestBody["orderID"]?.ToString();
            List<KeyValuePair<string, object>> associateparams = new List<KeyValuePair<string, object>>();
            associateparams.Add(new KeyValuePair<string, object>("order_id", order_orderID));
            DataTable OrdStatus = DataServiceMySql.GetDataTable($"SELECT rc.status_id,admin_description FROM retaline_customer_order rc INNER JOIN retaline_customer_order_status rs ON rc.status_id=rs.status_id WHERE order_order_id = @order_id", UserService.GetAPIConnectionString(), associateparams);
            try
            {
                if (OrdStatus != null && OrdStatus.Rows.Count > 0)
                {
                    DataRow dr = OrdStatus.Rows[0];
                    status = Convert.ToInt32(dr["status_id"]);
                    if (new int[] { 15,17,18,19 }.Contains(status))
                    {
                        return Json(new { result = 1, status = "Delayed", data = status });
                    }
                    else
                        return Json(new { result = 1, status = "Success", data = 1 });
                }
            }
            catch
            {
                return Json(new { result = 0, status = "Error", message = "Failure", data = -1 });
            }
            return Json(new { result = 0, status = "Error", message = "Failure" });

        }

        public IHttpActionResult AssociateDashboardValues([FromBody] AssociateAPIParams associateAPIParams)
        {
            if (associateAPIParams.isPendingOrders == 1)
            {
                try
                {
                    return Json(new { result = 1, status = "Success", data = GetLeads() });
                }
                catch
                {
                    return Json(new { result = 0, status = "Error", message = "Failure", data = new { } });
                }

            }
            var user = Service.UserService.CachedDefaultUser; 
            List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>();
            baparams.Add(new KeyValuePair<string, object>("email", user.Email));
            DataTable result = DataServiceMySql.GetDataTable($"SELECT ba.id  FROM business_associate ba WHERE baEmail = @email", UserService.GetAPIConnectionString(), baparams);
            string baId = "";
            if (result != null && result.Rows.Count > 0)
            {
                DataRow dr = result.Rows[0];
                baId = dr["id"].ToString();
            }
            List<KeyValuePair<string, object>> associateparams = new List<KeyValuePair<string, object>>();
            associateparams.Add(new KeyValuePair<string, object>("baId", baId));
            DataTable roTbl = DataServiceMySql.GetDataTable($"SELECT id FROM relationship_officer WHERE roBusAssociate = @baId", UserService.GetAPIConnectionString(), associateparams);
            string roId = "";
            if (roTbl != null && roTbl.Rows.Count > 0)
            {
                DataRow da = roTbl.Rows[0];
                roId = da["id"].ToString();
            }
            associateparams.Add(new KeyValuePair<string, object>("roId", roId));
            string strDashboardVals = @"SELECT COUNT(*) FROM finascop_crm_contact fcc INNER JOIN crm_contact_type cct ON cct.id = crco_type WHERE (fcc.id IN(SELECT contactId FROM finascop_crm_lead WHERE areaId IN (SELECT GROUP_CONCAT(id) FROM area_entries WHERE areaBusinessAssociate  = @baId)) OR (crco_CreatedFrom = 2 AND crco_CreatedBy = @baId) OR (crco_CreatedFrom = 3 AND crco_CreatedBy IN(SELECT GROUP_CONCAT(id) FROM relationship_officer WHERE roBusAssociate = @baId)))
            UNION ALL SELECT COUNT(*) FROM  finascop_crm_lead cl LEFT JOIN finascop_crm_prospect ON leadId=cl.id INNER JOIN business_associate ba ON ba.id=cl.baId WHERE cl.crmuId NOT IN (3,7) AND ((cl.areaId IN (SELECT GROUP_CONCAT(id) FROM area_entries WHERE areaBusinessAssociate  = @baId)) OR cl.baId=@baId) AND crle_type IN (1,3) 
            UNION ALL SELECT COUNT(*) FROM  finascop_crm_prospect cp INNER JOIN business_associate ba ON ba.id=cp.baId WHERE (areaId IN (SELECT GROUP_CONCAT(id) FROM area_entries WHERE areaBusinessAssociate  = @baId) OR baId=@baId) AND IFNULL(storeGroupId, 0) <= 0 UNION ALL SELECT COUNT(*) FROM finascop_branch b INNER JOIN finascop_branch_group bg ON b.br_storeGroup=bg.store_group_id WHERE ((b.areaid IN (SELECT GROUP_CONCAT(id) FROM area_entries WHERE areaBusinessAssociate  = @baId)) OR (bg.store_group_id IN(SELECT storeGroupId FROM  finascop_crm_prospect WHERE areaId IN (SELECT GROUP_CONCAT(id) FROM area_entries WHERE areaBusinessAssociate  = @baId)))) AND store_group_grosmartMerchant IN (0, 1)";
            var tblItems = DataServiceMySql.GetDataTable(strDashboardVals, Service.UserService.GetAPIConnectionString(), associateparams);
            if (tblItems != null)
            {
                int _contacts = 0, _leads = 0, _prospects = 0, _retailers = 0;
                if (tblItems.Rows.Count > 0)
                    try { _contacts = Convert.ToInt32(tblItems.Rows[0][0]); } catch { }
                if (tblItems.Rows.Count > 1)
                    try { _leads = Convert.ToInt32(tblItems.Rows[1][0]); } catch { }
                if (tblItems.Rows.Count > 2)
                    try { _prospects = Convert.ToInt32(tblItems.Rows[2][0]); } catch { }
                if (tblItems.Rows.Count > 3)
                    try { _retailers = Convert.ToInt32(tblItems.Rows[3][0]); } catch { }

                return Json(new { result = 1, status = "Success", data = new { contacts = _contacts, leads = _leads, prospects = _prospects, retailers = _retailers } });
            }

            return Json(new { result = 0, status = "Error", message = "Failure" });
        }

        public IHttpActionResult GetPendingTasks(int id = 0)
        {
            try
            {
                if (id > 0)
                {
                    //return Json(new { result = 1, status = "Success", data = PendingActions(true) });
                    return Json(new { result = 1, status = "Success", data = "" });
                }
                else
                {
                    var _user = Service.UserService.CachedDefaultUser;
                    int storegroupid = _user.APIStoreId;
                    var combinedData = Services.StoreService.MerchantPendingActions(0, storegroupid);
                    return Json(new { result = 1, status = "Success", data = (combinedData != null ? combinedData.PendingActions.Count + combinedData.PendingJobs.Count : 0) });
                }
            }
            catch
            {
                return Json(new { result = 0, status = "Error", message = "Failure", data = -1 });
            }

        }

        public object GetLeads()
        {
            var user = Service.UserService.CachedDefaultUser;
            List<KeyValuePair<string, object>> baparams = new List<KeyValuePair<string, object>>();
            baparams.Add(new KeyValuePair<string, object>("email", user.Email));
            DataTable result = DataServiceMySql.GetDataTable($"SELECT ba.id  FROM business_associate ba WHERE baEmail = @email", UserService.GetAPIConnectionString(), baparams);
            string baId = "";
            if (result != null && result.Rows.Count > 0)
            {
                DataRow dr = result.Rows[0];
                baId = dr["id"].ToString();
            }
            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
            input.Add(new KeyValuePair<string, object>("baId", baId));
            string sql = @"SELECT fcc.id, crco_orgName,crco_type, NAME AS contactType, CASE WHEN crco_mode=1 THEN 'Enquiries from the Site or SM campaigns' WHEN crco_mode=2 THEN 'Contacts created through CRM web form' WHEN crco_type=3 THEN 'Contacts creation through CRM mobile app with current location and photo' WHEN crco_type=4 THEN 'Contacts created through CRM mobile app with Google address API' END AS contactMode, crco_indContactperson, crco_indMobile, crco_orgEmail,crmu_id,crco_isActive,crco_CreatedBy, (SELECT baName FROM   business_associate WHERE id=crco_CreatedBy) AS baName,
                CASE WHEN crco_CreatedFrom=1 THEN 'Admin'
                WHEN crco_CreatedFrom=2 THEN 'Partner' WHEN crco_CreatedFrom=3 THEN 'App' END AS created_From, 
                crco_CreatedFrom FROM finascop_crm_contact fcc 
                INNER JOIN crm_contact_type cct ON cct.id = crco_type WHERE 1=1 AND crco_CreatedFrom IN (1,2) AND crco_CreatedBy IN(@baId,(SELECT GROUP_CONCAT(id) FROM relationship_officer WHERE roBusAssociate = @baId)) ORDER BY fcc.id DESC LIMIT 5";
            
            var tblItems = DataServiceMySql.GetDataTable(sql, Service.UserService.GetAPIConnectionString(), input);

            var data = tblItems.AsEnumerable().Select(item => new {
                storeName = item["crco_orgName"],
                contactNumber = item["crco_indMobile"],
                contactType = item["contactType"]
            }).ToArray();

            return data;
        }

        public object GetPendingOrders()
        {
            Service.User _user = Service.UserService.CachedDefaultUser;
            string sql = @"SELECT o.order_id, o.order_group_id, o.order_order_id, o.total, b.br_Name, d.order_city, TIMESTAMPDIFF
                (MINUTE, o.created_at, NOW()) AS diff,so.fsto_id,so.fsto_uid FROM retaline_customer_order o 
                INNER JOIN finascop_stock_transfer_order so ON so.fstr_id = o.order_id
                LEFT JOIN finascop_branch b ON o.order_branch_id=b.br_ID 
                LEFT JOIN retaline_customer_order_delivery_address d ON o.order_order_id=d.order_id 
                 WHERE (@branchid <= 0 OR branch_id = @branchid) AND o.status_id IN(4,5,6,7,8,9,10,11,12,13,14,15,16, 20, 22, 23, 27,28, 30, 31, 32, 33, 34) AND storegroup_id=@storegroup
                  ORDER BY o.created_at DESC LIMIT 10
                ";

            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>()
            {
                new KeyValuePair<string, object>("storegroup", _user.APIStoreId), 
                new KeyValuePair<string, object>("branchid", (User.IsInRole("BranchManager")? _user.APIRoleBranchId: 0))
            };

            //input.Add(new KeyValuePair<string, object>("storegroup", _user.APIStoreId));
            //input.Add(new KeyValuePair<string, object>("storegroup_id", this.CurrentUser.StoreGroupId));
            var tblItems = DataServiceMySql.GetDataTable(sql, Service.UserService.GetAPIConnectionString(), input);

            var data= tblItems.AsEnumerable().Select(item => new { orderid= item["order_id"], fstoid=item["fsto_id"], uid=item["fsto_uid"], orderNum=item["order_order_id"], 
                branchName=item["br_Name"], city=item["order_city"], total= String.Format("{0}{1}", ConfigurationManager.AppSettings.Get("CurrencySymbol"), item["total"]),
                diff= RetalineProAgent.Service.Common.MinutesToDiff(Convert.ToInt32(item["diff"])) }).ToArray();

            return data;
        }

        private object PendingActions(bool isDetailView=false)
        {
            var _user = Service.UserService.CachedDefaultUser;
            int storegroupid = _user.StoreGroupId, apistoregroupid = _user.APIStoreId;
            string sqlBranch = $"SELECT TOP 1 * FROM StoreBranch WHERE Storeid={storegroupid} ORDER BY IsDefault DESC";
            DataTable dtBranch = DataService.GetDataTable(sqlBranch);
            int branches = (dtBranch !=null && dtBranch.Rows.Count > 0 ? 1 : 0);
            int bankAccounts = 0, storesWithoutBank = 0, bankLinkedToStore = 0, gstscount = 0, gstNotLinkedToStore = 0, totalStores = 0, gstnNotVerified = 0;

            DataTable tblStoreSummary = DataService.GetDataTable("StoreSummary", parmeters: new List<KeyValuePair<string, object>> { new KeyValuePair<string, object>("tenantid", storegroupid) }, isSP: true);
            if (tblStoreSummary != null && tblStoreSummary.Rows.Count > 0)
            {
                bankAccounts =  (tblStoreSummary.Rows.Count > 1 ? Convert.ToInt32(tblStoreSummary.Rows[1][0]) : 0);                
                bankLinkedToStore = (tblStoreSummary.Rows.Count > 2 ? Convert.ToInt32(tblStoreSummary.Rows[2][0]) : 0);
                gstscount = (tblStoreSummary.Rows.Count > 3 ? Convert.ToInt32(tblStoreSummary.Rows[3][0]) : 0);
                gstNotLinkedToStore = (tblStoreSummary.Rows.Count > 4 ? Convert.ToInt32(tblStoreSummary.Rows[4][0]) : 0);
                gstnNotVerified = (tblStoreSummary.Rows.Count > 5 ? Convert.ToInt32(tblStoreSummary.Rows[5][0]) : 0);
                //storesWithoutBank = (tblStoreSummary.Rows.Count > 0 ? Convert.ToInt32(tblStoreSummary.Rows[6][0]) : 0);
                //if (storesWithoutBank > 0)
                //    storesWithoutBank = 1;
                //else
                //    storesWithoutBank = 0;
            }

            int orderPickers=0, orderPickersOnline=0, drivers=0, products=0, outOfStock=0, onlineStores =0, emailverified=_user.HasVerifiedEmail?1:0;

            string sqlAdditionalInfo = $"SELECT COUNT(DISTINCT phone) FROM retaline_godown_boy gb INNER JOIN finascop_branch b ON b.br_ID=gb.branch_id WHERE br_status= 'Active' AND b.br_storeGroup= {apistoregroupid} AND gb.status=1 " +
        $"UNION ALL SELECT COUNT(DISTINCT phone) FROM retaline_godown_boy gb INNER JOIN finascop_branch b ON b.br_ID=gb.branch_id WHERE br_status= 'Active' AND b.br_storeGroup= {apistoregroupid} AND gb.status=1 AND is_offline=0 " +
        $"UNION ALL SELECT COUNT(DISTINCT emp_id) FROM qugeo_driver dr INNER JOIN finascop_branch b ON b.br_ID= dr.br_id WHERE br_status= 'Active' AND b.br_storeGroup= {apistoregroupid} AND dr.d_Active=1  " +
        $"UNION ALL SELECT COUNT(DISTINCT stit_id) AS val FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE br_status= 'Active' AND b.br_storeGroup={apistoregroupid} " +
        $"UNION ALL SELECT COUNT(DISTINCT stit_id) FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id LEFT JOIN (SELECT item_id, SUM(`count`) AS blockedNum FROM finascop_stock_blocked GROUP BY item_id) blocked ON blocked.item_id = bi.stit_id " +
        $"WHERE br_status= 'Active' AND b.br_storeGroup= {apistoregroupid} AND ((IFNULL(bi.item_count, 0) - IFNULL(blockedNum, 0)) <= 0  or mrp <= 0 or selling_price <= 0) " +
        $"UNION ALL SELECT COUNT(*) FROM finascop_branch WHERE br_storeGroup= {apistoregroupid} AND br_SalesOnline = 1 " +
        $"UNION ALL SELECT COUNT(*) FROM finascop_branch WHERE br_storeGroup= {apistoregroupid} ";

            DataTable dtAdditionalInfo = DataServiceMySql.GetDataTable(sqlAdditionalInfo, UserService.GetAPIConnectionString());
            if (dtAdditionalInfo != null && dtAdditionalInfo.Rows.Count > 0)
            {
                try { orderPickers = Convert.ToInt32(dtAdditionalInfo.Rows[0][0]); } catch { orderPickers = 0; }
                if (dtAdditionalInfo.Rows.Count > 1)
                    try { orderPickersOnline = Convert.ToInt32( dtAdditionalInfo.Rows[1][0]); } catch { orderPickersOnline=0; }

                if (dtAdditionalInfo.Rows.Count > 2)
                    try { drivers = Convert.ToInt32(dtAdditionalInfo.Rows[2][0]); } catch { drivers=0; }
                //int onlineVehicles = 0;
                //try { onlineVehicles = Core.Services.APIService.VehiclesOnline(this.CurrentUser.APIStoreId).Count(); } catch { onlineVehicles = 0; }

                if (dtAdditionalInfo.Rows.Count > 3)
                    try { products = Convert.ToInt32(dtAdditionalInfo.Rows[3][0]); } catch { products=0; }
                //if (dtAdditionalInfo.Rows.Count > 4)
                //    try { outOfStock = Convert.ToInt32(dtAdditionalInfo.Rows[4][0]); } catch { outOfStock=0; }
                if (dtAdditionalInfo.Rows.Count > 5)
                    try { onlineStores = Convert.ToInt32(dtAdditionalInfo.Rows[5][0]);} catch { onlineStores = 0; }
                if (dtAdditionalInfo.Rows.Count > 6)
                    try { totalStores = Convert.ToInt32(dtAdditionalInfo.Rows[6][0]); } catch { totalStores=0; }
            }

            int pendingActions = 0;

            pendingActions += (branches > 0 ? 0 : 1) + (bankAccounts > 0 ? 0 : 1) + (bankLinkedToStore > 0 ? 0 : 1) + 
                (gstscount > 0 ? 0 : 1) + (gstNotLinkedToStore <= 0 ? 0 : 1) + (totalStores > 0 ? 0 : 1) + (gstnNotVerified <= 0 ? 0 : 1) + (orderPickers > 0 ? 0 : 1) + 
                (orderPickersOnline > 0 ? 0 : 1) + (drivers > 0 ? 0 : 1) + (products > 0 ? 0 : 1) + (products >0 && products == outOfStock ? 1 : 0) + (onlineStores > 0 ? 0 : 1)+ (emailverified > 0 ? 0 : 1);
            if(!isDetailView)
                return pendingActions;
            else
                return new { result = 1, status = "Success", data = new {
                    branches, bankAccounts, bankLinkedToStore, gstscount, gstNotLinkedToStore, totalStores, gstnNotVerified,
                    orderPickers, orderPickersOnline, drivers, products, outOfStock, onlineStores, emailverified
                } };

        }

        public IHttpActionResult OrderCompletionSendEmail([FromBody] object margineData)
        {
            var dynamicObject = JsonConvert.DeserializeAnonymousType(JsonConvert.SerializeObject(margineData), new
            {
                pid = int.MinValue,
                amt = double.MinValue,
                brId = double.MinValue
            });
            if (dynamicObject == null || dynamicObject.pid <= 0 || dynamicObject.amt <= 0 || dynamicObject.brId <= 0)
            {
                return Json(new { result = 0, status = "Error", message = "Invalid or missing data provided" });
            }


            return Json(new { result = 1, status = "Success", message = "Success" });

        }

        public object ValidateMargin([FromBody] object marginData)
        {
            var dynamicObject = JsonConvert.DeserializeAnonymousType(JsonConvert.SerializeObject(marginData), new
            {
                pid = int.MinValue,
                amt = double.MinValue,
                brId = int.MinValue
            });
            if (dynamicObject == null || dynamicObject.pid <= 0 || dynamicObject.amt <= 0 || dynamicObject.brId <= 0)
            {
                return Json(new { result = 0, status = "Error", message = "Invalid or missing data provided" });
            }
            

            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
            input.Add(new KeyValuePair<string, object>("stitId", dynamicObject.pid));
            input.Add(new KeyValuePair<string, object>("branchId", dynamicObject.brId));
            string sql = @"SELECT stit_MRP, branch_id, selling_price, discount_selling_price FROM finascop_stock_itemmaster im
            INNER JOIN finascop_stock_branch_inventory bi ON im.stit_ID=bi.stit_id
            WHERE im.stit_ID = @stitId AND branch_id=@branchId";
            var tblItems = DataServiceMySql.GetDataTable(sql, Service.UserService.GetAPIConnectionString(), input);
            string mrp = "";
            string sellingPrice = "";
            string branchId = "";
            if (tblItems != null && tblItems.Rows.Count > 0)
            {
                mrp = tblItems.Rows[0]["stit_MRP"].ToString();
                sellingPrice = tblItems.Rows[0]["selling_price"].ToString();
                branchId = tblItems.Rows[0]["branch_id"].ToString();
            }
            if (dynamicObject.amt > Convert.ToDouble(sellingPrice) && dynamicObject.amt > Convert.ToDouble(mrp))
            {
                try
                {
                    return Json(new { result = 0, status = "Error", message = "Failure", data = new { } });
                    
                }
                catch
                {
                    return Json(new { result = 1, status = "Success", message = "Success" });
                }
            }
            return Json(new { result = 1, status = "Success", message = "Success" });
        }

        public IHttpActionResult GetGraphicsObjects() { 
            return Json(new { storename= "ABC Stores", address= "test address, test city, test state", email= "test@email.com", 
                websiteurl= "mywebsite.com", phone = "9898989898", 
                Images = new []{ 
                    new{
                        name= "Template",
                        type=1,
                        url="https://partner.dev.grozeo.in/Content/canvas/images/Homebanner.jpg"
                    },
                    new{
                        name= "QRCode",
                        type=2,
                        url="https://partner.dev.grozeo.in/Content/canvas/images/qrcode.jpg"
                    },
                    new{
                        name= "Big-Logo",
                        type=7,
                        url="https://partner.dev.grozeo.in/Content/canvas/images/big-logo.jpg"
                    },
                    new{
                        name= "Small-logo",
                        type=7,
                        url="https://partner.dev.grozeo.in/Content/canvas/images/small-logo.jpg"
                    },
                }

            }); 
        
        }
        /// <summary>
        /// send otp for razor pay 
        /// </summary>
        /// <param name="requestBody"></param>
        /// <returns></returns>
        public IHttpActionResult Sendotp([FromBody] JObject requestBody)
        {
            try
            {
                string mobile = requestBody["input"]?.ToString();
                if (mobile == null)
                    return BadRequest ("Failed to send OTP: ");
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("mobil", mobile));
                var dt = DataService.GetDataTable("select  * from [user] where Mobile like @mobil ", parmeters: prms);
                if(dt == null || dt.Rows.Count <= 0)
                {
                    var sucess = Core.Services.APIService.GetOtp(mobile);
                    if (sucess != null)
                        return Json(new { result = 1, status = "Success", data = sucess });
                    else
                        return Json(new { result = 1, status = "error", data = "error" });
                }
                else
                {
                    return Json(new { result = 1, status = "Verified", data = "Verified" });
                }               
            }
            catch (Exception ex)
            {
                return BadRequest("Failed to send OTP: " + ex.Message);
            }
        }
        /// <summary>
        /// Verify otp for razor pay
        /// </summary>
        /// <param name="requestBody"></param>
        /// <returns></returns>
        public IHttpActionResult Verifyotp([FromBody] JObject requestBody)
        {
            try
            {
                string mobile = requestBody["mobile"]?.ToString();
                string otp = requestBody["otp"]?.ToString();
                if (mobile == null|| otp==null)
                    return BadRequest("Failed to send OTP: ");
                var verifyotp = Core.Services.APIService.VerifyOtp(mobile, otp);
                if (verifyotp != null)
                {
                    return Json(new { result = 1, status = "Success", data = "Success" });
                }
                else
                {
                    return Json(new { result = 0, status = "error", data = "error" });

                }                                              
            }
            catch (Exception ex)
            {
                return BadRequest("Failed to send OTP: " + ex.Message);
            }
           

        }
        /// <summary>
        /// verify Email otp
        /// </summary>
        /// <param name="requestBody"></param>
        /// <returns></returns>
        public IHttpActionResult sendemailotp([FromBody] JObject requestBody)
        {
            try
            {
                string email = requestBody["inputmail"]?.ToString();
                if (email == null)
                    return BadRequest("Failed to send OTP: ");
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("email", email));
                var dt = DataService.GetDataTable("select  * from [user] where Email like @email ", parmeters: prms);
                if (dt == null || dt.Rows.Count <= 0)
                {
                    var sucess = Core.Services.APIService.GetEmailOtp(email);
                    if(sucess!=null)
                    return Json(new { result = 1, status = "Success", data = sucess });
                    else
                        return Json(new { result = 1, status = "error", data = "error" });

                }
                else
                {
                    return Json(new { result = 1, status = "Verified", data = "Verified" });
                }
            }
            catch (Exception ex)
            {
                return BadRequest("Failed to send OTP: " + ex.Message);
            }
        }

        /// <summary>
        /// Verify Email otp for razor pay
        /// </summary>
        /// <param name="requestBody"></param>
        /// <returns></returns>
        public IHttpActionResult Emailotpverify([FromBody] JObject requestBody)
        {
            try
            {
                string email = requestBody["email"]?.ToString();
                string otp = requestBody["emailotp"]?.ToString();
                if(email==null|| otp==null)
                    return BadRequest("Failed to send OTP: ");
                var verifyotp = Core.Services.APIService.VerifyOtp(email, otp);
                if (verifyotp != null)
                {
                    return Json(new { result = 1, status = "Success", data = "Success" });
                }
                else
                {
                    return Json(new { result = 0, status = "error", data = "" });

                }
            }
            catch (Exception ex)
            {
                return BadRequest("Failed to send OTP: " + ex.Message);
            }

        }

        public IHttpActionResult Bankdetails([FromBody] JObject requestBody)
        {
            try
            {
                string bankaccountnumber = requestBody["bankaccountnumber"]?.ToString();
                string Ifsc = requestBody["Ifsc"]?.ToString();
                if(bankaccountnumber==null|| Ifsc==null)
                    return BadRequest("Failed to get  Details:");
                List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
                prms.Add(new KeyValuePair<string, object>("Ifsc", Ifsc));
                prms.Add(new KeyValuePair<string, object>("bankaccountnumber", bankaccountnumber));
                var dt = DataServiceMySql.GetDataTable("SELECT * FROM MerchantSubaccount WHERE Bankaccountnumber=@bankaccountnumber AND IFSC=@Ifsc AND `status`=4 ", parmeters: prms);
                if (dt == null || dt.Rows.Count <= 0)
                {
                    var bankInfo = APIService.VerifyBankAccount(bankaccountnumber, Ifsc);
                    if (bankInfo == null)
                        return Json(new { result = 0, status = "error", data = string.Empty });
                    var data = new
                    {
                        Name = bankInfo?.name ?? string.Empty,
                        Bank = bankInfo?.ifsc?.bank ?? string.Empty,
                        Branch = bankInfo?.ifsc?.branch ?? string.Empty
                    };

                    return Json(new { result = 1, status = "Success", data = data });
                }
                else
                {
                    return Json(new { result = 1, status = "Failed", data = "Failed" });
                }

            }
            catch (Exception ex)
            {
                return BadRequest("Failed to send OTP: " + ex.Message);

            }

        }


        public IHttpActionResult ProductCategory([FromBody] JObject requestBody)
        {
            string getinput = requestBody["input"]?.ToString();
            List<KeyValuePair<string, object>> prms = new List<KeyValuePair<string, object>>();
            prms.Add(new KeyValuePair<string, object>("Getinput", getinput));
            string getCategory = "SELECT  pc.parent_category, c.category_name, sc.sub_category,(SELECT business_type_name FROM finascop_business_type bt WHERE bt.business_type_id=gbt.business_type_id) AS business_type_name, parent_category_businessType, parent_category_id, category_id, sub_category_id FROM mypha_productsubcategory sc INNER JOIN  mypha_productcategory c  ON c.category_id = sc.main_category INNER JOIN  mypha_productparent_category pc  ON pc.parent_category_id = c.parent_category INNER JOIN finascop_branch_group_business_type gbt ON gbt.business_type_id = parent_category_businessType WHERE sc.status=1 AND c.status=1 AND pc.status=1 AND (sc.sub_category_id IN (SELECT product_category FROM `finascop_stock_itemmaster` WHERE `stit_SKU` LIKE CONCAT('%', @Getinput, '%')) OR  pc.parent_category LIKE CONCAT('%', @Getinput, '%') OR  c.category_name LIKE CONCAT('%', @Getinput, '%') OR  sc.sub_category LIKE CONCAT('%', @Getinput, '%') OR  parent_category_businessType LIKE CONCAT('%', @Getinput, '%') OR (SELECT business_type_name  FROM finascop_business_type bt  WHERE bt.business_type_id = gbt.business_type_id) LIKE CONCAT('%', @Getinput, '%')) GROUP BY sc.sub_category_id";
            DataTable GetDetails = DataServiceMySql.GetDataTable(getCategory, UserService.GetAPIConnectionString(), prms);
            try
            {
                List<object> results = new List<object>();
                if (GetDetails != null && GetDetails.Rows.Count > 0)
                {
                    foreach (DataRow row in GetDetails.Rows)
                    {
                        results.Add(new
                        {
                            ParentCategory = new
                            {
                                Name = row["parent_category"].ToString(),
                                Id = row["parent_category_id"].ToString()
                            },
                            Category = new
                            {
                                Name = row["category_name"].ToString(),
                                Id = row["category_id"].ToString()
                            },
                            SubCategory = new
                            {
                                Name = row["sub_category"].ToString(),
                                Id = row["sub_category_id"].ToString()
                            },
                            BusinessType = new
                            {
                                Name = row["business_type_name"].ToString(),
                                Id = row["parent_category_businessType"].ToString()
                            }
                        });
                    }
                }
                return Json(new { result = 1, status = "Success", data = results });
            }
            catch (Exception ex)
            {
                return Json(new { result = 0, status = "Error", message = "Invalid or missing data provided" });

            }
        }

       

    }


    public class HomeAPIParams
    {
        public int isPendingOrders { get; set; }
    }

    public class AssociateAPIParams
    {
        public int isPendingOrders { get; set; }
    }

}