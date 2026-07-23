using RetalineProAgent.Core.BussinessModel.Catalog;
using RetalineProAgent.Core.Services.HelperServices;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Configuration;
using RetalineProAgent.Core.BussinessModel.Home;
using RetalineProAgent.Core.BussinessModel.Store;
using System.Security.Cryptography;
using RetalineProAgent.Core.BussinessModel.UserDetails;

using SendGrid;
using SendGrid.Helpers.Mail;
using RestSharp;
using System.Text.Json;
using RetalineProAgent.Core.Services.Finance;
using System.Data.SqlTypes;
using RetalineProAgent.Core.BussinessModel.Adhar;
using Amazon.DynamoDBv2.Model;
using RetalineProAgent.Core.Services.ActiveLog;
using RetalineProAgent.Core.BussinessModel.API;
using Newtonsoft.Json;
using Amazon.Runtime.Internal.Transform;
using System.Xml.Linq;
using System.Net.Http;

namespace RetalineProAgent.Core.Services
{
    public static class APIService
    {
        private static List<CategoryData> _cachedCategories = null;
        private static List<HomeValue> _cachedBrands = null;
        private static CategoryProducts _brandProducts = null;
        private static List<BussinessModel.Inventory.Brand> _cachedBrandsMaster = null;
        public static void ClearCachedData()
        {
            _cachedCategories = null;
            _cachedBrands = null;
            _brandProducts = null;
        }


        public static List<CategoryData> Categories(int storeid)
        {
            if (_cachedCategories != null)
                return _cachedCategories;

            string url = "/api/home/category";
            var data = HttpHelperService.Get<CatalogRoot>(url, storeid, null).Result;
            if (data != null && data.Data != null)
                _cachedCategories = data.Data;

            return _cachedCategories;
        }

        public static BussinessModel.Inventory.Products GetBrandProducts(int storeid, int brandid, int catid, int catLevelId, int count = 20, int pageid = 1)
        {
            //if (_brandProducts != null)
            //    return _brandProducts;

            //if (brandid < 1)
            //    return null;

            var requestParams = new Dictionary<string, object>
            {
                {"store", storeid },
                { "brand", brandid},
                { "catlevel", catLevelId},
                { "category", catid},
                {"count", count }
            };
            //string url = $"{ ConfigurationSettings.AppSettings.Get("api.brands")}{pageid}";
            string url = $"{ConfigurationSettings.AppSettings.Get("api.BackOffice.Products")}?page={pageid}";
            // ?store=5&count=1000&catlevel=0&brand=0&category=0
            //var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<CategoryProducts>>(url, requestParams, storeid);
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<BussinessModel.API.APIItems<BussinessModel.Inventory.Products>>>(url, requestParams, storeid);
            if (aPIData != null && aPIData.Data != null)
                return aPIData.Data.Data;//_brandProducts = aPIData.Data;
            return null; //_brandProducts;
        }

        public static CategoryProducts GetProductsByCategoy(int catid, int storeid, int pageid, int categoryLevel = 3)
        {

            var requestParams = new Dictionary<string, object>
            {
                { "requested_id", catid},
                { "virtualcategoryid", 0},
                {"category_level",  1},
                {"branch_id", 0 },
                {"order_method", 1 },
                {"sort", new Dictionary<string, object>{
                    {"price", "" }
                } },
                {"filter", new Dictionary<string, object>{
                    {"category", new object[]{ } },
                    {"brands", new object[]{ } },
                    {"price_range", new object[]{ } },

                } }
            };


            // {"requested_id":"1", "category_level": 1, "branch_id":"0","order_method":"1","sort":{"price":""},"filter":{"category":[],"brands":[],"price_range":[]}}

            BussinessModel.API.APIModel<CategoryProducts> aPIData = null;

            string url = String.Format(ConfigurationSettings.AppSettings.Get("api.ProductByParentCategoryId"), pageid);
            aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<CategoryProducts>>(url, requestParams, storeid);

            //if (categoryLevel == 1)
            //{
            //    string url = String.Format(ConfigurationSettings.AppSettings.Get("api.ProductByParentCategoryId"), pageid);
            //    aPIData = HttpHelperService.Get<BussinessModel.API.APIModel<CategoryProducts>>(url, storeid).Result;
            //}
            //else if (categoryLevel == 2)
            //{
            //    string url = String.Format(ConfigurationSettings.AppSettings.Get("api.ProductByCategoryId"), pageid);
            //    aPIData = HttpHelperService.Get<BussinessModel.API.APIModel<CategoryProducts>>(url, storeid).Result;
            //}
            //else
            //{
            //    string url = $"{ConfigurationSettings.AppSettings.Get("api.ProductBySubCategory")}{pageid}";
            //    aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<CategoryProducts>>(url, requestParams, storeid);
            //}
            if (aPIData != null)
                return aPIData.Data;
            return null;
        }

        public static List<HomeValue> GetHomeBrands(int storeid)
        {
            if (_cachedBrands != null)
                return _cachedBrands;

            string homeContentUrl = ConfigurationSettings.AppSettings.Get("api.GetContent");
            var _catchedHomeRoot = HttpHelperService.Get<HomeRoot>(homeContentUrl, storeid, null).Result;
            _cachedBrands = FormatData(_catchedHomeRoot, "brand");
            return _cachedBrands;
        }

        public static List<BussinessModel.Inventory.Brand> GetMasterBrands(int storeid = -1)
        {
            if (_cachedBrandsMaster != null)
                return _cachedBrandsMaster;

            string homeContentUrl = ConfigurationSettings.AppSettings.Get("api.BrandsMaster");
            var apiData = HttpHelperService.Get<BussinessModel.API.APIModel<List<BussinessModel.Inventory.Brand>>>(homeContentUrl, storeid, null).Result;
            if (apiData != null)
                _cachedBrandsMaster = apiData.Data;
            return _cachedBrandsMaster;

        }

        private static List<HomeValue> FormatData(HomeRoot dataFromAPI, string type)
        {
            if (dataFromAPI != null && dataFromAPI.Data != null)
            {
                return dataFromAPI.Data.Home.Where(item => item.Type.ToLower() == type).SelectMany(item => item.Value).ToList();
            }
            return null;
        }

        public static List<Store> GetStores(int storegroupid, bool all = true)
        {
            string storesUrl = ConfigurationSettings.AppSettings.Get("api.Stores");
            var stores = HttpHelperService.Get<BussinessModel.API.APIModel<List<Store>>>(storesUrl + storegroupid, storegroupid, null).Result;
            if (stores != null && stores.Data != null)
                return stores.Data.Where(b => all || b.Status == 1).OrderBy(s => s.BranchName).ToList();

            return null;
        }

        public static List<BusinessType> GetBusinessTypes()
        {
            string strUrl = ConfigurationSettings.AppSettings.Get("api.BackOffice.BusinessTypes");
            var stores = HttpHelperService.Get<BussinessModel.API.APIModel<List<BusinessType>>>(strUrl, 0, null).Result;
            return stores.Data;

        }

        public static int CreateStoreGroup(string storegroupname, int primarybusinesstype, string otherbusinesstypes)
        {
            var requestParams = new Dictionary<string, object>
            {
                {"name", storegroupname },
                { "primarybusinesstype", primarybusinesstype},
                { "additionalbusinessType", otherbusinesstypes}
            };
            string url = ConfigurationSettings.AppSettings.Get("api.BackOffice.AddStoreGroup");
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<BussinessModel.API.StoregroupResult>>(url, requestParams, -1);
            if (aPIData != null && aPIData.Data != null)
                return Convert.ToInt32(aPIData.Data.StoreGroupId);
            return -1;
        }
        public static void UpdateStoreGroup(int storegroupid, string storegroupname, int primarybusinesstype, string otherbusinesstypes)
        {
            var requestParams = new Dictionary<string, object>
            {
                {"id", storegroupid },
                {"name", storegroupname },
                { "primarybusinesstype", primarybusinesstype},
                { "additionalbusinessType", otherbusinesstypes}
            };
            string url = ConfigurationSettings.AppSettings.Get("api.BackOffice.UpdateStoreGroup");
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<BussinessModel.API.APISuccessModel>>(url, requestParams, -1);
            //if (aPIData != null && aPIData.Data != null)
            //    return Convert.ToInt32(aPIData.Data.StoreGroupId);
            //return -1;
        }


        public static List<State> GetStates()
        {
            string url = ConfigurationSettings.AppSettings.Get("api.BackOffice.States");
            var apiData = HttpHelperService.Get<BussinessModel.API.APIModel<List<State>>>(url, -1).Result;
            if (apiData != null)
                return apiData.Data;
            return null;
        }
        public static List<District> GetDistricts(int stateId)
        {
            string url = ConfigurationSettings.AppSettings.Get("api.BackOffice.Districts");
            var apiData = HttpHelperService.Get<BussinessModel.API.APIModel<List<District>>>($"{url}{stateId}", -1).Result;
            if (apiData != null)
                return apiData.Data;
            return null;
        }

        public static Store CreateStore(string brname, string braddress, string brdistrict, string brstate, string brcity, int brpin,
            string brincharge, string brphone, string bremail, string brfax, int brstocklevel, int brdefaultapibranch,
            int brstoregroup, double brlat, double brlng)
        {
            var requestParams = new Dictionary<string, object>
            {
                {"brname" , brname},
                {"braddress" , braddress},
                {"brdistrict" , brdistrict},
                {"brstate" , brstate},
                {"brcity" , brcity},
                {"brpincode" , brpin},
                {"brincharge" , brincharge},
                {"brphone" , brphone},
                {"bremail" , bremail},
                {"brfax" , brfax},
                {"brstocklevel" , brstocklevel},
                {"brdefaultapibranch" , 0},
                {"brstoregroup" , brstoregroup},
                {"brlat" , brlat},
                {"brlng" , brlng}
            };

            string url = ConfigurationSettings.AppSettings.Get("api.BackOffice.AddStore");
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<Store>>(url, requestParams, -1);
            if (aPIData != null)
                return aPIData.Data;

            return null;
        }
        public static Store UpdateStore(int branchId, string brname, string braddress, string brdistrict, string brstate, string brcity, int brpin,
            string brincharge, string brphone, string bremail, string brfax, int brstocklevel, int brdefaultapibranch,
            int brstoregroup, double brlat, double brlng)
        {
            var requestParams = new Dictionary<string, object>
            {
                {"brid", branchId },
                {"brname" , brname},
                {"braddress" , braddress},
                {"brdistrict" , brdistrict},
                {"brstate" , brstate},
                {"brcity" , brcity},
                {"brpincode" , brpin},
                {"brincharge" , brincharge},
                {"brphone" , brphone},
                {"bremail" , bremail},
                {"brfax" , brfax},
                {"brstocklevel" , brstocklevel},
                {"brdefaultapibranch" , brdefaultapibranch},
                {"brstoregroup" , brstoregroup},
                {"brlat" , brlat},
                {"brlng" , brlng}
            };

            string url = ConfigurationSettings.AppSettings.Get("api.BackOffice.UpdateStore");
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<Store>>(url, requestParams, -1);
            if (aPIData != null)
                return aPIData.Data;

            return null;
        }



        public static void UploadInventory(string branchKey, List<InventoryAPI> inventories)
        {
            var requestParams = new Dictionary<string, object>
            {
                {"data" , inventories}
            };

            string url = ConfigurationSettings.AppSettings.Get("api.BackOffice.AddInventory");
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<int>>(url, requestParams, -1, branchKey);

        }

        public static BankInfo BankInfoFromIFSC(string ifsc)
        {
            string url = ConfigurationSettings.AppSettings.Get("api.BankInfoFromIFSC");
            var apiData = HttpHelperService.Get<BankInfo>(url + ifsc, -1).Result;
            return apiData;
        }
        public static BankAccount VerifyBankAccount(string accountNo, string ifsc)
        {
            string url = "https://api.attestr.com/api/v1/public/finanx/acc";
            List<KeyValuePair<string, object>> data = new List<KeyValuePair<string, object>>();
            data.Add(new KeyValuePair<string, object>("acc", accountNo));
            data.Add(new KeyValuePair<string, object>("ifsc", ifsc));
            data.Add(new KeyValuePair<string, object>("fetchIfsc", true));

            //List<KeyValuePair<string, string>> headers = new List<KeyValuePair<string, string>>();
            //headers.Add(new KeyValuePair<string, string>("Content-Type", "application/json"));
            //headers.Add(new KeyValuePair<string, string>("Authorization", "Basic T1gwUUExbEJyNHg1LVZNbDZ6LjBlMjJiMWE5MzBkMjFkZDlkZjdiYTc1NmZiYTkwMjgzOmU4N2Y4ZDllYWZhZWVhMzE5ZmIzYzU3NTk2YWQxNzU4MzA2NjUwOGVlZjE2MmRhMg=="));
            //string authKey = "";//"Basic T1gwUUExbEJyNHg1LVZNbDZ6LjBlMjJiMWE5MzBkMjFkZDlkZjdiYTc1NmZiYTkwMjgzOmU4N2Y4ZDllYWZhZWVhMzE5ZmIzYzU3NTk2YWQxNzU4MzA2NjUwOGVlZjE2MmRhMg==";
            //var APIData = HttpHelperService.Post<BankAccount>(url, data, 0, authKey, headers);
            //return APIData;

            string content = $"\"acc\": \"{accountNo}\", \"ifsc\": \"{ifsc}\", \"fetchIfsc\": true";

            var client = new RestClient(url);
            var request = new RestRequest();// (Method.Post);
            request.Method = Method.Post;
            request.AddHeader("content-type", "application/json");
            request.AddHeader("Authorization", "Basic T1gwUUExbEJyNHg1LVZNbDZ6LjBlMjJiMWE5MzBkMjFkZDlkZjdiYTc1NmZiYTkwMjgzOmU4N2Y4ZDllYWZhZWVhMzE5ZmIzYzU3NTk2YWQxNzU4MzA2NjUwOGVlZjE2MmRhMg==");

            request.AddBody("{"+ content + "}", "application/json");
            var response = client.Execute<BankAccount>(request);

            return response.Data;


        }

        public static FSSAINew VerifyFSSAI(string accountNo)
        {

            // string url = "https://api.attestr.com/api/v1/public/checkx/fssai";
            string url= ConfigurationSettings.AppSettings.Get("FSSAIAPIUrl");
            string apiSecret = ConfigurationManager.AppSettings.Get("FSSAIAPIKey");
            string clientId = ConfigurationManager.AppSettings.Get("FSSAIAPIclientId");
            string content = $"\"licenseNumber\": \"{accountNo}\"";
            var client = new RestClient(url);
            var request = new RestRequest();
            request.Method = Method.Post;
            request.AddHeader("content-type", "application/json");
            request.AddHeader("secretKey", apiSecret);
            request.AddHeader("clientId", clientId);

            request.AddBody("{" + content + "}", "application/json");
            var response = client.Execute<FSSAINew>(request);

            return response.Data;


        }

        public static BankAccountPostCoder VerifyBankAccountUK(string accountNo, string sortcode, string identifier="apicall")
        {
            string apikey = ConfigurationManager.AppSettings.Get("POSTCODEKeyUK");
            if(string.IsNullOrEmpty(apikey))
                apikey = "PCW6M-J2546-2FZCK-KF9DQ";

            string url = $"https://ws.postcoder.com/pcw/{apikey}/bank?identifier={identifier}";
            //List<KeyValuePair<string, object>> data = new List<KeyValuePair<string, object>>();
            //data.Add(new KeyValuePair<string, object>("acc", accountNo));
            //data.Add(new KeyValuePair<string, object>("ifsc", sortcode));
            //data.Add(new KeyValuePair<string, object>("fetchIfsc", true));

            //List<KeyValuePair<string, string>> headers = new List<KeyValuePair<string, string>>();
            //headers.Add(new KeyValuePair<string, string>("Content-Type", "application/json"));
            //headers.Add(new KeyValuePair<string, string>("Authorization", "Basic T1gwUUExbEJyNHg1LVZNbDZ6LjBlMjJiMWE5MzBkMjFkZDlkZjdiYTc1NmZiYTkwMjgzOmU4N2Y4ZDllYWZhZWVhMzE5ZmIzYzU3NTk2YWQxNzU4MzA2NjUwOGVlZjE2MmRhMg=="));
            //string authKey = "";//"Basic T1gwUUExbEJyNHg1LVZNbDZ6LjBlMjJiMWE5MzBkMjFkZDlkZjdiYTc1NmZiYTkwMjgzOmU4N2Y4ZDllYWZhZWVhMzE5ZmIzYzU3NTk2YWQxNzU4MzA2NjUwOGVlZjE2MmRhMg==";
            //var APIData = HttpHelperService.Post<BankAccount>(url, data, 0, authKey, headers);
            //return APIData;

            string content = $"\"accountnumber\": \"{accountNo}\", \"sortcode\": \"{sortcode}\"";

            var client = new RestClient(url);
            var request = new RestRequest();// (Method.Post);
            request.Method = Method.Post;
            //request.AddHeader("content-type", "application/json");
            //request.AddHeader("Authorization", "Basic T1gwUUExbEJyNHg1LVZNbDZ6LjBlMjJiMWE5MzBkMjFkZDlkZjdiYTc1NmZiYTkwMjgzOmU4N2Y4ZDllYWZhZWVhMzE5ZmIzYzU3NTk2YWQxNzU4MzA2NjUwOGVlZjE2MmRhMg==");

            request.AddBody("{" + content + "}", "application/json");
            var response = client.Execute<BankAccountPostCoder>(request);
            if(response != null && response.Data != null)
                return response.Data;

            return null;
        }

        //public static BussinessModel.Finance.StoreGroup StoreGroupCreate(string storeName, string mobile)
        //{
        //    string url = "https://finascopdataentry.azurewebsites.net/api/CreateTenantLedger";
        //    List<KeyValuePair<string, object>> data = new List<KeyValuePair<string, object>>();
        //    data.Add(new KeyValuePair<string, object>("name", storeName));
        //    data.Add(new KeyValuePair<string, object>("mobile", mobile));


        //    string content = $"\"name\": \"{storeName}\", \"mobile\": \"{mobile}\"";

        //    var client = new RestClient(url);
        //    var request = new RestRequest();// (Method.Post);
        //    request.Method = Method.Post;
        //    //request.AddHeader("content-type", "application/json");
        //    request.AddHeader("x-functions-key", "P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==");

        //    request.AddBody("{" + content + "}", "application/json");
        //    var response = client.Execute<BussinessModel.Finance.StoreGroup>(request);

        //    return response.Data;
        //}

        public static void SetDefaultStore(int storeId)
        {
            var requestParams = new Dictionary<string, object>
            {
                {"brid" , storeId}
            };

            string url = ConfigurationSettings.AppSettings.Get("api.SetDefaultStore");
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<int>>(url, requestParams, -1);

        }

        public static void ChangeBranchStatus(int storeId, bool status)
        {
            var requestParams = new Dictionary<string, object>
            {
                {"brid" , storeId},
                {"enable",  (status ? 1: 0)}
            };

            string url = ConfigurationSettings.AppSettings.Get("api.SetStoreStatus");
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<int>>(url, requestParams, -1);

        }

        public static string AssignOrderPicker(string transferorderId, int orderId, string orderPickerId, int branchId, int storegroupid)
        {
            var requestParams = new Dictionary<string, object>
            {
                {"is_cpd", 0 },
                {"order_id", transferorderId },
                {"boy_id", orderPickerId },
                {"branch_id", branchId },
                { "order_pk_id", orderId }
            };

            string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/back-office/transfer-order-assign";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<BussinessModel.API.APISuccessModel>>(url, requestParams, storegroupid);
            if (aPIData != null && aPIData.Data != null)
                return aPIData.Data.Status;
            return null;
        }

        public static BussinessModel.OrderPacking.ManualPacking SubmitManualPacking(string fsto_uid, int fsid, string formattedDate, string invoiceNumber, double invoicdeAmount, int noofbags, string fsto_updateon, 
            List<Dictionary<string, object>> itemList)
        {
            var requestParams = new Dictionary<string, object>
            {

                //{"fsto_uid", fsto_uid },
                //{"fsto_id", fsid},
                {"type", 1 },
                {"ismanual", 1},
                {"ispartner", 1},
                //{"key", CreateMD5(fsto_updateon)},
                {"boy_order_id", "-10" },
                {"invoicedate", formattedDate },
                {"invoiceno", invoiceNumber },
                {"invoiceamt", invoicdeAmount },
                {"number_bags", noofbags },
                //{"fsto_stockValue", 0 },
                //{"item_id", itemid },
                {"items", itemList.ToArray() }
            };


            string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/back-office/manualtransfers/{fsto_uid}/proceednobarcode";
            var aPIData = HttpHelperService.Post<BussinessModel.OrderPacking.ManualPacking>(url, requestParams, 0);
            if (aPIData != null)
                return aPIData;// .Status;
            return null;
        }

        public static string ShippingURL(string transferOrderId)
        {
            var requestParams = new Dictionary<string, object>
            {
                {"fstoId", transferOrderId }
            };


            string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/back-office/manualtransfers/generateshipment";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<BussinessModel.API.APISuccessModel>>(url, requestParams, 0);
            if (aPIData != null)
                return aPIData.Status;
            return null;
        }
        public static string ForceSubmit(string fsto_uid, int fsid, string formattedDate, string invoiceNumber, double invoicdeAmount, int noofbags, string fsto_updateon,
            List<Dictionary<string, object>> itemList)
        {
            var requestParams = new Dictionary<string, object>
            {

                //{"fsto_uid", fsto_uid },
                //{"fsto_id", fsid},
                {"type", 1 },
                {"ismanual", 1},
                //{"key", CreateMD5(fsto_updateon)},
                {"boy_order_id", "-10" },
                {"invoicedate", 0 },
                {"invoiceno", 0 },
                {"invoiceamt", 0 },
                {"number_bags", noofbags },
                {"is_incomplete", true },
                {"items", itemList.ToArray() }
            };


            string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/back-office/manualtransfers/{fsto_uid}/proceednobarcode";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<BussinessModel.API.APISuccessModel>>(url, requestParams, 0);
            if (aPIData != null)
                return aPIData.Status;
            return null;
        }

        private static object md5(string fsto_updateon)
        {
            //string UserName = fsto_updateon;

            //create the MD5CryptoServiceProvider object we will use to encrypt the password
            MD5CryptoServiceProvider md5Hasher = new MD5CryptoServiceProvider();
            //create an array of bytes we will use to store the encrypted password
            Byte[] hashedBytes;
            //Create a UTF8Encoding object we will use to convert our password string to a byte array
            UTF8Encoding encoder = new UTF8Encoding();

            //encrypt the password and store it in the hashedBytes byte array
            hashedBytes = md5Hasher.ComputeHash(encoder.GetBytes(fsto_updateon));

            return hashedBytes;
            //throw new NotImplementedException();

        }

        public static string CreateMD5(string input)
        {
            string result;
            using (MD5 hash = MD5.Create())
            {
                result = String.Join
                (
                    "",
                    from ba in hash.ComputeHash
                    (
                        Encoding.UTF8.GetBytes(input)
                    )
                    select ba.ToString("x2")
                );
            }
            return result;
        }

        public static string Revoke(int ordboyId, int orderPIckerId)
        {
            var requestParams = new Dictionary<string, object>
            {
                {"boy_id",  ordboyId},
                { "order_pk_id", orderPIckerId }
            };

            string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/back-office/transfer-order-revoke";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<BussinessModel.API.APISuccessModel>>(url, requestParams, 0);
            if (aPIData != null)
                return aPIData.Status;
            return null;
        }

        public static List<BussinessModel.LiveVehicles.vehicleDetails> LoadVehicle(int branchid, double pickupLat, double pickupLng , int?UserType=0,int?UserId=0)
        {
            //var requestParams = new Dictionary<string, object>
            //{
            //    {"br_id", branchid },
            //    {"latitude", pickupLat },
            //    {"longitude", pickupLng }
            //};

            List<KeyValuePair<string, string>> prms = new List<KeyValuePair<string, string>>();
            prms.Add(new KeyValuePair<string, string>("br_id", branchid.ToString()));
            prms.Add(new KeyValuePair<string, string>("latitude", pickupLat.ToString()));
            prms.Add(new KeyValuePair<string, string>("longitude", pickupLng.ToString()));
            prms.Add(new KeyValuePair<string, string>("userType", UserType.ToString()));
            prms.Add(new KeyValuePair<string, string>("userId", UserId.ToString()));


            string url = $"{ConfigurationSettings.AppSettings.Get("admin.url")}qugeoapi/partner/loadVehicleDetails";
            var aPIData = HttpHelperService.Post<BussinessModel.LiveVehicles.LiveVehicle>(url, prms, branchid);
            if (aPIData != null && aPIData.VehicleDetails != null)
                return aPIData.VehicleDetails;
            return null;
        }

        public static List<BussinessModel.LiveVehicles.vehicleDetails> VehiclesOnline(int storegroupid)
        {
            List<KeyValuePair<string, string>> prms = new List<KeyValuePair<string, string>>();
            prms.Add(new KeyValuePair<string, string>("storeGroupId", storegroupid.ToString()));
            string url = $"{ConfigurationSettings.AppSettings.Get("admin.url")}qugeoapi/partner/listLiveVehicles";
            var aPIData = HttpHelperService.Post<BussinessModel.LiveVehicles.LiveVehicle>(url, prms, storegroupid);
            if (aPIData != null && aPIData.VehicleDetails != null)
                return aPIData.VehicleDetails;
            return null;
        }

        public static string AssignDeliveryBoy(int qugeobkNO, int branchId, int handlingBranchId, string type, string hdnVehicleId, string[] quorIdList)
        {
            List<KeyValuePair<string, string>> prms = new List<KeyValuePair<string, string>>();
            prms.Add(new KeyValuePair<string, string>("isScheduled", "0"));
            prms.Add(new KeyValuePair<string, string>("qugeobk_NO", qugeobkNO.ToString()));
            prms.Add(new KeyValuePair<string, string>("br_id", branchId.ToString()));
            prms.Add(new KeyValuePair<string, string>("handling_br_id", handlingBranchId.ToString()));
            prms.Add(new KeyValuePair<string, string>("hdnVehicleId", hdnVehicleId.ToString()));
            prms.Add(new KeyValuePair<string, string>("type", type.ToString()));
            prms.Add(new KeyValuePair<string, string>("quorIds[]", quorIdList[0]));

            string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/drivers/scheduleABookingJobs";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APISuccessModel>(url, prms, branchId);
              if (aPIData != null && aPIData.Message != null)
                return aPIData.Message;
            return null;
        }

        public static string AssignDeliveryStaff(int qugeobkNO, int branchId, int handlingBranchId, string type, string hdnVehicleId, string[] quorIdArray)
        {
            List<KeyValuePair<string, string>> prms = new List<KeyValuePair<string, string>>();
            prms.Add(new KeyValuePair<string, string>("isScheduled", "1"));
            prms.Add(new KeyValuePair<string, string>("qugeobk_NO", qugeobkNO.ToString()));
            prms.Add(new KeyValuePair<string, string>("br_id", branchId.ToString()));
            prms.Add(new KeyValuePair<string, string>("handling_br_id", handlingBranchId.ToString()));
            prms.Add(new KeyValuePair<string, string>("hdnVehicleId", hdnVehicleId.ToString()));
            prms.Add(new KeyValuePair<string, string>("type", type.ToString()));
            prms.Add(new KeyValuePair<string, string>("quorIds", System.Text.Json.JsonSerializer.Serialize(quorIdArray)));

            string url = $"{ConfigurationSettings.AppSettings.Get("admin.url")}qugeoapi/partner/scheduleABookingJobs";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APISuccessModel>(url, prms, branchId);
            if (aPIData != null && aPIData.Message != null)
                return aPIData.Message;
            return null;
        }

        public static BussinessModel.PAN.PANInfo GetPANDetails(string pan)
        {
            string emptraAPIClientID = ConfigurationSettings.AppSettings.Get("emptra.clientid");
            string emptraAPISecret = ConfigurationSettings.AppSettings.Get("emptra.secret");
            string emptraAPIUrl = "https://api.emptra.com/fetchPanDetails";

            List<KeyValuePair<string, string>> data = new List<KeyValuePair<string, string>>();
            data.Add(new KeyValuePair<string, string>("pan", pan));

            List<KeyValuePair<string, string>> headers = new List<KeyValuePair<string, string>>();
            headers.Add(new KeyValuePair<string, string>("clientId", emptraAPIClientID)); //"bdd9215673be7ff7740ae648a7cfdcb4:574ab7f003510e494abe4da72a66fef8"));
            headers.Add(new KeyValuePair<string, string>("secretKey", emptraAPISecret)); //"8STGIWr7xIXyeCuRJ09ez8yOtXj9LKuNsi5Kxu5ppf2poKjgDwyNX05ASBYRURmTh"));

            var APIData = HttpHelperService.Post<BussinessModel.PAN.PANInfo>(emptraAPIUrl, data, 0, "", headers);
            return APIData;

        }

        public static bool VerifyBank()
        {
            var client = new RestClient("https://api.emptra.com/bankAccount/verify");
            var request = new RestRequest();// (Method.Post);
            request.Method = Method.Post;
            request.AddHeader("content-type", "application/json");
            var response = client.Execute(request);

            return response.IsSuccessful;
        }

        public static Dictionary<string, string> GetOtp(string mobile, string gst ="0", int storegroupid = 0, int templateid=0, Dictionary<string, string> additionalParams= null)
        {
            string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/signup/mobile";

            var body = new Dictionary<string, string>
            {
                { "mobile", mobile },
                { "gst", gst }
            };
            if(templateid > 0)
                body.Add("template_type", templateid.ToString());

            if (additionalParams != null)
                foreach (var data in additionalParams)
                    body.Add(data.Key, data.Value);

            var result = HttpHelperService.Post<Dictionary<string, string>>(url, body, storegroupid);
            return result;

        }

        public static UserDetailsFromApi VerifyOtp(string mobile, string otp, int storegroupid = 0)
        {
            string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/signup/verify";
            var body = new Dictionary<string, string>
            {
                { "mobile", mobile },
                {"otp", otp },
                {"branch_group", storegroupid.ToString()},
                {"isPartner", "1" }
            };
           
            return HttpHelperService.Post<UserDetailsFromApi>(url, body, storegroupid);
        }

		public static Dictionary<string, string> GetEmailOtp(string email, string gst = "0", int storegroupid = 0, int templateid = 0, Dictionary<string, string> additionalParams = null)
		{
			string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/signup/email";

			var body = new Dictionary<string, object>
			{
				{ "mobile", "" },
                { "email", email },
                {"identifier", "customer" },
				{ "use_password", 0 }
			};
			if (templateid > 0)
				body.Add("template_type", templateid.ToString());

			if (additionalParams != null)
				foreach (var data in additionalParams)
					body.Add(data.Key, data.Value);

			var result = HttpHelperService.Post<Dictionary<string, string>>(url, body, storegroupid);
			return result;

		}


		public static UserDetailsFromApi VerifyEmailOtp(string email, string otp, int storegroupid = 0)
		{
			string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/signup/verify/email";
			var body = new Dictionary<string, string>
			{
				{ "email", email },
				{"otp", otp },
			};

			return HttpHelperService.Post<UserDetailsFromApi>(url, body, storegroupid);
		}


		public static async Task<bool> SendEmail(string toEmail, string subject, string body, string toName = "", bool isHtml = false, string fromEmail = "", string fromName = "")
        {
            if (String.IsNullOrEmpty(fromEmail))
                fromEmail = ConfigurationSettings.AppSettings.Get("FromEmail");
            if (String.IsNullOrEmpty(fromName))
                fromName = ConfigurationSettings.AppSettings.Get("FromEmailName");

            var emailSender = ConfigurationSettings.AppSettings.Get("EmailSender");

            // If AWS SES then insert the email data in the Dynamo DB to trigger lambda and send using AWS SES.
            if (emailSender == "AWS_SES")
            {
                string tablename = ConfigurationManager.AppSettings.Get("DynamoEmailTbl");               
                DateTime currentDateTime = DateTime.Now;
                TimeZoneInfo timeZone = TimeZoneInfo.FindSystemTimeZoneById("UTC");
                DateTime currentTimeInDesiredTimeZone = TimeZoneInfo.ConvertTime(currentDateTime, timeZone);
                string formattedDateTime = currentTimeInDesiredTimeZone.ToString("yyyy-MM-dd HH:mm:ss");
                Guid uuid = Guid.NewGuid();
                string uuidAsString = uuid.ToString();
                var itemToWrite = new Dictionary<string, AttributeValue>
                    {
                        { "uuid", new AttributeValue { S =  uuidAsString} },
                        { "tstamp", new AttributeValue { S = formattedDateTime } },
                        { "eqEmailFrom", new AttributeValue { S = fromEmail } },
                        { "eqEmailTo", new AttributeValue { S = toEmail } },
                        { "eqMessage", new AttributeValue { S = body } },
                        { "eqSubject", new AttributeValue { S = subject } },
                        { "eqStatus", new AttributeValue { S = "0" } },
                        {"eqEmailToName",new AttributeValue { S = toName } },
                        {"eqEmailFromName",new AttributeValue { S = fromName } },
                };

                DynamoService.SaveToDynamoDb(tablename, itemToWrite);
                return true;
            }
            var apiKey = ConfigurationSettings.AppSettings.Get("SendGridAPIKey");  // "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"; // Environment.GetEnvironmentVariable("SENDGRID_API_KEY"); 
            var client = new SendGridClient(apiKey);
            var msg = new SendGridMessage()
            {
                From = new EmailAddress(fromEmail, fromName),
                Subject = subject,
                PlainTextContent = (isHtml ? "" : body),
                HtmlContent = (isHtml ? body : "")
            };
            msg.AddTo(new EmailAddress(toEmail, toName));
            var response = await client.SendEmailAsync(msg).ConfigureAwait(false);

            return response.IsSuccessStatusCode;
        }

        

        public static BussinessModel.Captcha.CaptchaResponse VerifyToken(string token)
        {
            if(string.IsNullOrEmpty(token)) 
                return new BussinessModel.Captcha.CaptchaResponse();

            string url = string.Format(ConfigurationSettings.AppSettings.Get("Recaptcha.VerificationUrl"), ConfigurationSettings.AppSettings.Get("Recaptcha.Secret"), token);
            var result = HttpHelperService.Post<BussinessModel.Captcha.CaptchaResponse>(url, null, 0);
            return result;
        }

        public static BussinessModel.VAT.VATData ValidateVAT(string vat)
        {
            string apiSecret = ConfigurationManager.AppSettings.Get("VATSTACKApiSecret");
            if (string.IsNullOrEmpty(apiSecret))
                apiSecret = "sk_live_968249bc9800f9e45e09769c2f935a9c";
            string content = $"\"query\": \"{vat}\"";
            // {"query":"198283458"}
            var client = new RestClient("https://api.vatstack.com/v1/validations");
            var request = new RestRequest();// (Method.Post);
            request.Method = Method.Post;
            request.AddHeader("content-type", "application/json");
            request.AddHeader("X-API-KEY", apiSecret);

            request.AddBody("{" + content + "}", "application/json");
            var response = client.Execute<BussinessModel.VAT.VATData>(request);
            if(request != null && response.Data != null)
                return response.Data;

            return default;
        }
        public static BussinessModel.VAT.TRNData ValidateTRN(string trn)
        {
            string apiToken = ConfigurationManager.AppSettings.Get("SurepassAPIToken");
            string content = $"\"trn_number\": \"{trn}\"";
            // {"query":"198283458"}
            var client = new RestClient(String.Format("{0}/uae-trn/verification", ConfigurationManager.AppSettings.Get("SurepassAPIUrl")));
            var request = new RestRequest();// (Method.Post);
            request.Method = Method.Post;
            request.AddHeader("content-type", "application/json");
            request.AddHeader("Authorization", $"Bearer {apiToken}");

            request.AddBody("{" + content + "}", "application/json");
            var response = client.Execute<APIModel<BussinessModel.VAT.TRNData>>(request);
            if (request != null && response.Data != null)
                return response.Data.Data;

            return default;
        }

        public static async Task<string> SubmitForm(string url, object data)
        {
            if (String.IsNullOrEmpty(url))
                return "Invalid URL";
            var response = HttpHelperService.Post<BussinessModel.API.APIModel<BussinessModel.API.APIItems<string>>>(url, data, 0);
            if (response != null && response.Data != null && response.Data.Data != null)
                return response.Data.Data;

            //var content = JsonSerializer.Serialize(data);
            //var client = new RestClient(url);
            //var request = new RestRequest();
            //request.Method = Method.Post;
            //request.AddBody("{" + content + "}", "application/json");
            //var response = await client.ExecuteAsync<string>(request);
            //if (request != null && response.Data != null)
            //    return response.Data;

            return "";

        }

        public static string SubmitManualReplenish(string orderId)
            
        {
            var requestParams = new Dictionary<string, object>
            {
                {"action", 0 },
                {"order_pk_id", orderId},
                {"order_request_id", "-10" }
            };


            string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/back-office/manualtransfers/{orderId}/replenish";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<BussinessModel.API.APISuccessModel>>(url, requestParams, 0);
            if (aPIData != null)
                return aPIData.Status;
            return null;
        }

        public static string DeliverCODJobs(int storegroupid, int[] quorIdList, DateTime collectionDate)
        {
            List<KeyValuePair<string, string>> prms = new List<KeyValuePair<string, string>>();
            prms.Add(new KeyValuePair<string, string>("storeGroupId", storegroupid.ToString()));
            prms.Add(new KeyValuePair<string, string>("collectionDate", collectionDate.ToString()));
            //prms.Add(new KeyValuePair<string, string>("quorIds[]", "[\"" + quorIdList + "\"]"));
            prms.Add(new KeyValuePair<string, string>("quorIds[]", "[" + String.Join(",", quorIdList) + "]"));
            string url = $"{ConfigurationSettings.AppSettings.Get("admin.url")}qugeoapi/partner/saveDeliverCODJobs";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APISuccessModel>(url, prms, storegroupid);
            if (aPIData != null && aPIData.Message != null)
                return aPIData.Message;
            return null;
        }

        public static string InventoryLog(int itemId, int branchID, double selPrice, double itemCnt, string type, string action)
        {
            //var requestParams = new Dictionary<string, object>
            //{
            //    {"stit_id", itemId },
            //    {"branch_id", "NULL" },
            //    {"selling_price", sellingPrice },
            //    {"old_item_count", "NULL" },
            //    {"item_count", itemCnt },
            //    {"old_selling_price", "NULL" },
            //    {"fpod_skuPurchaseRange", "NULL" },
            //    {"fpod_skuPurchaseQty", "NULL" },
            //    {"fpod_skuAvgPurchaseRate", "NULL" },
            //    {"fpod_skuLastPurchaseRate", "NULL" },
            //    {"fpod_leastSKUepr", "NULL" },
            //    {"fpod_effectivemargin", "NULL" },
            //    {"updated_on", "NULL" },
            //    {"updated_by", "NULL" },
            //    {"type", "NULL" },
            //    {"action", "NULL" }
            //};

            List<KeyValuePair<string, string>> prms = new List<KeyValuePair<string, string>>();
            prms.Add(new KeyValuePair<string, string>("stit_id", itemId.ToString()));
            prms.Add(new KeyValuePair<string, string>("branch_id", branchID.ToString()));
            prms.Add(new KeyValuePair<string, string>("selling_price", selPrice.ToString()));
            prms.Add(new KeyValuePair<string, string>("old_item_count", "NULL"));
            prms.Add(new KeyValuePair<string, string>("item_count", itemCnt.ToString()));
            prms.Add(new KeyValuePair<string, string>("old_selling_price", "NULL"));
            prms.Add(new KeyValuePair<string, string>("fpod_skuPurchaseRange", "NULL"));
            prms.Add(new KeyValuePair<string, string>("fpod_skuPurchaseQty", "NULL"));
            prms.Add(new KeyValuePair<string, string>("fpod_skuAvgPurchaseRate", "NULL"));
            prms.Add(new KeyValuePair<string, string>("fpod_skuLastPurchaseRate", "NULL"));
            prms.Add(new KeyValuePair<string, string>("fpod_leastSKUepr", "NULL"));
            prms.Add(new KeyValuePair<string, string>("fpod_effectivemargin", "NULL"));
            prms.Add(new KeyValuePair<string, string>("updated_on", "NULL"));
            prms.Add(new KeyValuePair<string, string>("updated_by", "NULL"));
            prms.Add(new KeyValuePair<string, string>("type", type));
            prms.Add(new KeyValuePair<string, string>("action", action));


            string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/back-office/inventory-log/save";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<BussinessModel.API.APISuccessModel>>(url, prms, 0);
            if (aPIData != null && aPIData.Data != null)
                return aPIData.Data.Status;
            return null;
        }

        public static async Task<T> ExecuteAPI<T>(string url, List<KeyValuePair<string, string>> header, object content, string contenttype= "application/json")
        {

            //string key = ConfigurationManager.AppSettings.Get("FinascopAPIKey");
            //if (String.IsNullOrEmpty(key))
            //    key = "P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==";

            //string content = (string)JObject.Parse(JsonConvert.SerializeObject(voucher)); //$"\"acc\": \"{accountNo}\", \"ifsc\": \"{ifsc}\", \"fetchIfsc\": true";

            var client = new RestClient(url);
            var request = new RestRequest();//api/FinascopDataEntry (Method.Post);
            request.Method = Method.Post;
            foreach(var _header in header)
                request.AddHeader(_header.Key, _header.Value);

            //request.AddHeader("content-type", "application/json");
            //request.AddHeader("x-functions-key", key); //'"P_5JtNckvvxLTUM6cF9py_7ZYIA5QM9ofmNaDvh__HoqAzFuAbEyZQ==");

            //request.AddBody("{" + content + "}", "application/json");
            request.AddBody(content, contenttype);
            var response = await client.ExecuteAsync<T>(request);
            return response.Data;

        }

        public static BussinessModel.ContactArea.AreaDetails getAreaForLead(string latitude, string longitude)
        {
            List<KeyValuePair<string, string>> prms = new List<KeyValuePair<string, string>>();
            prms.Add(new KeyValuePair<string, string>("latitude", latitude));
            prms.Add(new KeyValuePair<string, string>("longitude", longitude));

            string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/back-office/nearest-baarea";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel< BussinessModel.API.APIModel<BussinessModel.ContactArea.AreaDetails>>>(url, prms, 0);
            if (aPIData != null && aPIData.Data != null)
                return aPIData.Data.Data;// .Status;
            return null;

        }

        //public static List<BussinessModel.BusinessFAQList.businessDetail> BusinessFAQ(int branchid, double pickupLat, double pickupLng)
        //{
        //    List<KeyValuePair<string, string>> prms = new List<KeyValuePair<string, string>>();
        //    prms.Add(new KeyValuePair<string, string>("br_id", branchid.ToString()));
        //    prms.Add(new KeyValuePair<string, string>("latitude", pickupLat.ToString()));
        //    prms.Add(new KeyValuePair<string, string>("longitude", pickupLng.ToString()));


        //    string url = $"{ConfigurationSettings.AppSettings.Get("business.url")}api/conversations";
        //    var aPIData = HttpHelperService.Post<BussinessModel.BusinessFAQList.BusinessQuestions>(url, prms, branchid);
        //    if (aPIData != null && aPIData.BusinessDetails != null)
        //        return aPIData.BusinessDetails;
        //    return null;
        //}

        public static List<BussinessModel.MessageCenterList.messageDetail> MessageCenter(int branchid, double pickupLat, double pickupLng)
        {
            List<KeyValuePair<string, string>> prms = new List<KeyValuePair<string, string>>();
            prms.Add(new KeyValuePair<string, string>("embed", "threads"));
            prms.Add(new KeyValuePair<string, string>("mailboxId", "123"));
            prms.Add(new KeyValuePair<string, string>("folderId", "57"));
            prms.Add(new KeyValuePair<string, string>("status", "active"));
            prms.Add(new KeyValuePair<string, string>("state", "deleted"));
            prms.Add(new KeyValuePair<string, string>("type", "email"));
            prms.Add(new KeyValuePair<string, string>("assignedTo", "35"));
            prms.Add(new KeyValuePair<string, string>("customerEmail", "john@example.org"));
            prms.Add(new KeyValuePair<string, string>("customerPhone", "777-777-777"));
            prms.Add(new KeyValuePair<string, string>("customerId", "17"));
            prms.Add(new KeyValuePair<string, string>("number", "359"));
            prms.Add(new KeyValuePair<string, string>("subject", "test"));
            prms.Add(new KeyValuePair<string, string>("tag", "overdue"));
            prms.Add(new KeyValuePair<string, string>("createdSince", "2021-01-07T12:00:03Z"));
            prms.Add(new KeyValuePair<string, string>("updatedSince", "2021-01-07T12:00:03Z"));
            prms.Add(new KeyValuePair<string, string>("sortField", "updatedAt"));
            prms.Add(new KeyValuePair<string, string>("sortOrder", "asc"));
            prms.Add(new KeyValuePair<string, string>("page", "1"));
            prms.Add(new KeyValuePair<string, string>("pageSize", "100"));


            string url = $"{ConfigurationSettings.AppSettings.Get("business.url")}api/conversations";
            var aPIData = HttpHelperService.Post<BussinessModel.MessageCenterList.MessageCenter>(url, prms, branchid);
            if (aPIData != null && aPIData.MessageDetails != null)
                return aPIData.MessageDetails;
            return null;
        }

        public static string OrdDelivConfEmail(string name, string email, string storename, string ordNum)

        {
            var requestParams = new Dictionary<string, object>
            {
                {"fullname", name },
                {"email", email},
                {"storename", storename },
                {"ordernum", ordNum}
            };


            string url = $"{ConfigurationSettings.AppSettings.Get("partner.url")}api/APIService/DeliveryCompletionSendEmail";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<BussinessModel.API.APISuccessModel>>(url, requestParams, 0);
            if (aPIData != null)
                return aPIData.Status;
            return null;
        }

        public static string ShippingConfEmail(string name, string email, string ordNum)
        {
            var requestParams = new Dictionary<string, object>
            {
                {"email", email},
                //{"storename", storename },
                {"order_order_id", ordNum},
                {"Customersname", name },
            };
            string url = $"{ConfigurationSettings.AppSettings.Get("partner.url")}api/APIService/ShippingConfirmation";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<BussinessModel.API.APISuccessModel>>(url, requestParams, 0);
            if (aPIData != null)
                return aPIData.Status;
            return null;
        }

        public static string WalletBalance(int customerId, string refentryId, double amount, string addInfo)

        {
            var requestParams = new Dictionary<string, object>
            {
                {"customer_id", customerId },
                {"order_id", refentryId},
                {"source_type", 1 },
                {"amount", amount },
                {"information", addInfo },
                {"barcode", 0 }
            };


            string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/back-office/wallet/create";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<BussinessModel.API.APISuccessModel>>(url, requestParams, 0);
            if (aPIData != null)
                return aPIData.Status;
            return null;
        }

        public static string ProductIndia(int productId)
        {
            List<KeyValuePair<string, string>> prms = new List<KeyValuePair<string, string>>();
            prms.Add(new KeyValuePair<string, string>("productId", productId.ToString()));


            string url = $"{ConfigurationSettings.AppSettings.Get("productIndia.url")}partnerapi/thirdpartyproducts/saveMerchantProducts";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APISuccessModel>(url, prms, 0);
            if (aPIData != null)
                return aPIData.Status;
            return null;
        }

        public static BussinessModel.BrandData.ProductBrand ProductBrand(string brandName, int manufacturerId, int storegroupId)
        {
            List<KeyValuePair<string, string>> prms = new List<KeyValuePair<string, string>>();
            prms.Add(new KeyValuePair<string, string>("brand_name", brandName.ToString()));
            prms.Add(new KeyValuePair<string, string>("manufacture_id", manufacturerId.ToString()));
            prms.Add(new KeyValuePair<string, string>("storegroup_id", storegroupId.ToString()));


            string url = $"{ConfigurationSettings.AppSettings.Get("productIndia.url")}partnerapi/thirdpartyproducts/saveMerchantBrands";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<BussinessModel.BrandData.ProductBrand>>(url, prms, 0);
            if (aPIData != null && aPIData.Data != null)
                return aPIData.Data;
            return default;

        }

        public static AdharVerificationResult<AdharVerificationData> GetAdharDetails(string adhar)
        {
            string emptraAPIClientID = ConfigurationSettings.AppSettings.Get("emptra.clientid");
            string emptraAPISecret = ConfigurationSettings.AppSettings.Get("emptra.secret");
            string emptraAPIUrl = "https://api.emptra.com/aadhaarVerification/requestOtp";

            List<KeyValuePair<string, string>> data = new List<KeyValuePair<string, string>>();
            data.Add(new KeyValuePair<string, string>("aadhaarNumber", adhar));

            List<KeyValuePair<string, string>> headers = new List<KeyValuePair<string, string>>();
            headers.Add(new KeyValuePair<string, string>("clientId", emptraAPIClientID));
            headers.Add(new KeyValuePair<string, string>("secretKey", emptraAPISecret));
            // AdharAPIModel
            var APIData = HttpHelperService.Post<AdharAPIModel<AdharVerificationData>>(emptraAPIUrl, data, 0, "", headers);
            if (APIData != null)
                return APIData.result;

            return default;
        }

        public static AdharVerificationResult<AdharInfo> VerifyAdhar(string client_id, string otp)
        {
            string emptraAPIClientID = ConfigurationSettings.AppSettings.Get("emptra.clientid");
            string emptraAPISecret = ConfigurationSettings.AppSettings.Get("emptra.secret");
            string emptraAPIUrl = "https://api.emptra.com/aadhaarVerification/submitOtp";

            List<KeyValuePair<string, string>> data = new List<KeyValuePair<string, string>>();
            data.Add(new KeyValuePair<string, string>("client_id", client_id));
            data.Add(new KeyValuePair<string, string>("otp", otp));

            List<KeyValuePair<string, string>> headers = new List<KeyValuePair<string, string>>();
            headers.Add(new KeyValuePair<string, string>("clientId", emptraAPIClientID));
            headers.Add(new KeyValuePair<string, string>("secretKey", emptraAPISecret));

            var APIData = HttpHelperService.Post<AdharAPIModel<AdharInfo>>(emptraAPIUrl, data, 0, "", headers);
            if (APIData != null)
                return APIData.result;

            return default;
        }

        public static BussinessModel.API.APISuccessModel Support(int supportType, string phone, string email, string name, string title, string description, int storeId, int supportUnit, string generatedFileName, string filePath)
        {
            var prms = new Dictionary<string, object>
            {
                {"support_type",  supportType},
                { "phone", phone },
                { "email", email },
                { "name", name },
                { "title", title },
                { "description", description },
                { "created_from", 2 },
                { "created_by",  storeId},
                { "support_unit",  supportUnit},
                { "file_name",  generatedFileName},
                { "file_url",  filePath}
            };


            string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/support-ticket/create";
            var aPIData = HttpHelperService.Post<BussinessModel.API.APISuccessModel>(url, prms, 0);
            if (aPIData != null && aPIData.Message != null)
                return aPIData;
            //return null;
            //var aPIData = HttpHelperService.Post<BussinessModel.API.APIModel<BussinessModel.API.APISuccessModel>>(url, prms, 0);
            //if (aPIData != null)
            //    return aPIData.Data;
            return null;
        }
        public static string GenerateProductDescription(string name, string brandname,string category, string description)
        {
            string googleurl = ConfigurationManager.AppSettings.Get("googleDescriptionurl");
            string key = ConfigurationManager.AppSettings.Get("googleDescriptionKey");
            if (String.IsNullOrEmpty(googleurl) || String.IsNullOrEmpty(key))
                return "";
            string url = $"{googleurl}?key={key}";
            var fields = new Dictionary<string, object>
            {
                { "contents", new Dictionary<string, object>
                    {
                        { "parts", new Dictionary<string, object>
                            {
                                { "text", "Create an expanded product description for  our ecom portal with few hash tags and seo capable keywords on   " + name + "of brand" + brandname +" under the category" + category + "with short description" + description +  ". Please make sure that the content is complete and concise, while does not contain any links or external references."}

                            }
                        }
                    }
                }
            };

            var contents = JsonConvert.SerializeObject(fields);
            var client = new RestClient(url);
            var request = new RestRequest();
            request.Method = Method.Post;
            request.AddHeader("Content-Type", "application/json");

            // Serialize and add the body using AddJsonBody
            request.AddJsonBody(contents);

            // Execute the request and get the response
            var response = client.Execute(request);
            var jsonObject = JsonConvert.DeserializeObject<dynamic>(response.Content);

            // Navigate to the text part
            var candidates = jsonObject?.candidates;

            if (candidates != null && candidates.Count > 0)
            {
                var content = candidates[0]?.content;
                var parts = content?.parts;

                if (parts != null && parts.Count > 0)
                {
                    return parts[0]?.text;
                }
            }
            return string.Empty;
        }

        public static APIModel<dynamic> Subscription_Stripe(string payment_method_id, int storegroupid, string priceId, string subscriptionId="")
        {
            if (string.IsNullOrEmpty(payment_method_id) || storegroupid < 1)
                return null;

			var prms = new Dictionary<string, object>
			{
				{"token",  payment_method_id},
                {"priceid", priceId },
                {"subscriptionid", subscriptionId }
			};

			string url = $"{ConfigurationSettings.AppSettings.Get("api.url")}api/partner/subscriptions/stripe/create";
			var aPIData = HttpHelperService.Post<APIModel<dynamic>>(url, prms, storegroupid);
			if (aPIData != null && aPIData.Status != null)
				return aPIData;

			return null;

		}

        public static string ClickToCallAPI_VoxBay(string Phone)
        {
            string url = "https://x.voxbay.com:81/api/click_to_call";
            var prms = new Dictionary<string, string>
    {
        { "uid", ConfigurationSettings.AppSettings.Get("VoxbayUID") },
        { "upin", ConfigurationSettings.AppSettings.Get("VoxbayPIN") },
        { "ext", ConfigurationSettings.AppSettings.Get("VoxbayExtension") },
        { "destination", Phone }
    };
            string queryString = string.Join("&", prms.Select(kvp => $"{kvp.Key}={Uri.EscapeDataString(kvp.Value)}"));
            string callUrl = $"{url}?{queryString}";

            using (HttpClient client = new HttpClient())
            {
                try
                {
                    HttpResponseMessage response = client.GetAsync(callUrl).Result;
                    if (response.IsSuccessStatusCode)
                    {
                        return response.Content.ReadAsStringAsync().Result;
                    }
                }
                catch (Exception ex)
                {
                    Console.WriteLine($"API call failed: {ex.Message}");
                }
            }

            return null;
        }

    }
}

