using RetalineProAgent.Core.Services.HelperServices;
using RetalineProAgent.Service;
using System;
using System.Collections.Generic;
using System.Data;
using System.Configuration;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Security.Cryptography;
using System.Web.UI;
using RetalineProAgent.Core.Services;
using Newtonsoft.Json;
using RetalineProAgent.Core.BussinessModel.Store;
using RetalineProAgent.Core.BussinessModel.Finance;
using log4net.Util.TypeConverters;

namespace RetalineProAgent.Services
{
    public enum GSTStatus
    {
        Verified = 1,
        VerificationSkipped = 2,
        NoGST = 3
    }

    public static class StoreService
    {

        public static int CreateStoreGroup(string storegroupname, int primaryBusinessType, List<int> secondaryBusinessTypes, string storeRefId, string siteUrl, string invitationcode = "")
        {
            string sql = $"INSERT INTO finascop_branch_group(store_group_name, storeRefId, siteUrl, prospect_Id) VALUES(@storegroupname, @refid, @siteUrl, (SELECT id FROM finascop_crm_prospect WHERE invitationCode LIKE @invcode LIMIT 1)); select LAST_INSERT_ID()";
            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
            input.Add(new KeyValuePair<string, object>("storegroupname", storegroupname));
            input.Add(new KeyValuePair<string, object>("refid", storeRefId));
            input.Add(new KeyValuePair<string, object>("siteUrl", siteUrl));
            input.Add(new KeyValuePair<string, object>("invcode", invitationcode));

            var result = DataServiceMySql.ExecuteScalar(sql, Service.UserService.GetAPIConnectionString(), input);

            int storegroupid = Convert.ToInt32(result);

            if (storegroupid > 0)
            {
                input.Clear();
                sql = "INSERT INTO finascop_branch_group_business_type(store_group_id, business_type_id, is_primary) VALUES(@groupid, @business_type_id, 1); ";
                input.Add(new KeyValuePair<string, object>("groupid", storegroupid));
                input.Add(new KeyValuePair<string, object>("business_type_id", primaryBusinessType));

                int count = 0;
                foreach (int secondarybId in secondaryBusinessTypes.Distinct())
                {
                    if (secondarybId == primaryBusinessType)
                        continue;

                    sql += "INSERT INTO finascop_branch_group_business_type(store_group_id, business_type_id, is_primary) VALUES(@groupid, @business_type_id" + count + ", 0); ";
                    input.Add(new KeyValuePair<string, object>("business_type_id" + count, secondarybId));
                    count++;
                }

                int rowsAffected = DataServiceMySql.ExecuteSql(sql, Service.UserService.GetAPIConnectionString(), input);

                if (rowsAffected < 1)
                {
                    throw new Exception("Add business types failed");
                }
            }

            return storegroupid;

            //var storeInfo = APIService.StoreGroupCreate(name,mobile);
        }

        public static int AddBusinessTypes(int storegroupid, int primaryBusinessType, List<int> secondaryBusinessTypes)
        {
            int result = 0;
            try {
                if (storegroupid > 0 && (primaryBusinessType >0 || secondaryBusinessTypes.Count > 0) )
                {
                    if (primaryBusinessType <= 0 && secondaryBusinessTypes.Count > 0)
                        primaryBusinessType = secondaryBusinessTypes[0];
                    if (primaryBusinessType <= 0)
                        return 0;
 
                    List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
                    string sql = "DELETE from finascop_branch_group_business_type WHERE store_group_id = @groupid; INSERT INTO finascop_branch_group_business_type(store_group_id, business_type_id, is_primary) VALUES(@groupid, @business_type_id, 1); ";
                    input.Add(new KeyValuePair<string, object>("groupid", storegroupid));
                    input.Add(new KeyValuePair<string, object>("business_type_id", primaryBusinessType));

                    int count = 0;
                    foreach (int secondarybId in secondaryBusinessTypes.Distinct())
                    {
                        if (secondarybId == primaryBusinessType)
                            continue;

                        sql += $"INSERT INTO finascop_branch_group_business_type(store_group_id, business_type_id, is_primary) VALUES(@groupid, @business_type_id{count}, 0); ";
                        input.Add(new KeyValuePair<string, object>("business_type_id" + count, secondarybId));
                        count++;
                    }

                    int rowsAffected = DataServiceMySql.ExecuteSql(sql, Service.UserService.GetAPIConnectionString(), input);

                    if (rowsAffected < 1)
                    {
                        throw new Exception("Add business types failed");
                    }
                }

            }
            catch {
                result = 0;
            }

            return result;
        }

        public static int AppendBusinessTypes(int storegroupid, List<int> secondaryBusinessTypes)
        {
            int result = 0;
            try
            {
                if (storegroupid > 0 && secondaryBusinessTypes.Count > 0)
                {
                    List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
                    string sql = "";
                    input.Add(new KeyValuePair<string, object>("groupid", storegroupid));
                    int count = 0;
                    foreach (int secondarybId in secondaryBusinessTypes.Distinct())
                    {
                        sql += $"INSERT INTO finascop_branch_group_business_type(store_group_id, business_type_id, is_primary) VALUES(@groupid, @business_type_id{count}, 0); ";
                        input.Add(new KeyValuePair<string, object>("business_type_id" + count, secondarybId));
                        count++;
                    }

                    int rowsAffected = DataServiceMySql.ExecuteSql(sql, Service.UserService.GetAPIConnectionString(), input);

                    if (rowsAffected < 1)
                    {
                        throw new Exception("Add business types failed");
                    }
                }

            }
            catch
            {
                result = 0;
            }

            return result;
        }

        /// <summary>
        /// Create Store / Branch
        /// </summary>
        /// <param name="storename">Store name</param>
        /// <param name="shortname"></param>
        /// <param name="storegroupid"></param>
        /// <param name="address"></param>
        /// <param name="city"></param>
        /// <param name="state"></param>
        /// <param name="district"></param>
        /// <param name="postalCode"></param>
        /// <param name="email"></param>
        /// <param name="mobile"></param>
        /// <param name="lat"></param>
        /// <param name="lng"></param>
        /// <param name="contactperson"></param>
        /// <param name="gst"></param>
        /// <param name="isDefault"></param>
        /// <param name="tradeRestriction"></param>
        /// <returns></returns>
        public static int CreateStore(string storename, string shortname, int storegroupid, string address, string address2, string address3, string city, int state,
            int district, string postalCode, string email, string mobile, string lat, string lng, string contactperson, string gst, 
            int isDefault = 0, int tradeRestriction=0, int taxType=0, int? directDelivery=1 ,int? courierDelivery = 1)
        {
            string storeType = "Dealer";
            //int stockLevel = 3;
            int stockLevel = 0;
            //int cpd = -1;
            int cpd = 0;
            int expressDelivery = 0;
            int ranking = 2;
            int pyramidLevel = 4;            
            int idSlotted = 0;
            int idCourier = 0;
            string strSqlMaxKey = $"SELECT IFNULL((SELECT COALESCE (MAX(br_key),1000) FROM finascop_branch), 0) AS maxkey, IFNULL((SELECT pgChargeId FROM pgcharge_master WHERE pgChargeIsDefault = 1 AND pgChargeStatus = 1 LIMIT 1), 0) AS pgcharge, IFNULL((SELECT sdId FROM settlementDays_master WHERE sdIsDefault = 1 AND sdStatus = 1 LIMIT 1), 0) AS settlementid";
            //var tblKey = DataServiceMySql.GetDataTable($"SELECT COALESCE (MAX(br_key),1000) FROM finascop_branch", UserService.GetAPIConnectionString());
            var tblKey = DataServiceMySql.GetDataTable(strSqlMaxKey, UserService.GetAPIConnectionString());
            string key = tblKey.Rows[0]["maxkey"].ToString();
            int pgchargeid = 0; try { pgchargeid = Convert.ToInt32(tblKey.Rows[0]["pgcharge"]); } catch { pgchargeid = 0; } 
            int settlementid = 0; try { settlementid = Convert.ToInt32(tblKey.Rows[0]["settlementid"]); } catch { settlementid = 0; }
           
            int lastkey = Convert.ToInt32(key);
            int nextkey = lastkey + 1;
            string branchKey = Convert.ToString(nextkey);
            Random rnd = new Random();
            string value = branchKey + (Convert.ToString(rnd.Next(10, 200)));
            //var tblbrReferenceID = DataServiceMySql.GetDataTable($"SELECT br_ReferenceID FROM finascop_branch", UserService.GetAPIConnectionString());
            //string refID = tblbrReferenceID.Rows[0][0].ToString();
            //string refKey = null;
            int br_SalesOnline = 1;
            //if (refID != null)
            //{
                //refKey = CreateMD5((DateTime.Now.ToString(branchKey)) + value);
            //}
            string brRefID = CreateMD5((DateTime.Now.ToString(branchKey)) + value); //refKey;
            
            string sql = $"INSERT INTO finascop_branch(br_Name,br_City,br_District,br_State,br_Address,br_Fax,br_Email,br_Phone,br_Incharge," +
                $"branch_shortname,br_key,br_ReferenceID,br_Lat,br_Lng,br_pincode,br_stockLevel,br_cpd,br_StoreType,br_storeGroup," +
                $"br_rdrIdExpress,br_ranking,br_PyramidLevel,br_directDelivery,br_courierDelivery,br_rdrIdSlotted,br_rdrIdCourier, br_isdefaultstore, " +
                $"br_SalesOnline, br_GST, br_pgchargeId, br_sdId, tradeRestriction, taxType, br_Address2, br_Address3, areaId) " +
                $"VALUES(@br_Name,@br_City,@br_District,@br_State,@br_Address,@br_Phone,@br_Email,@br_Phone,@br_Incharge,@branch_shortname,@br_key,@br_ReferenceID,@br_Lat," +
                $"@br_Lng,@br_pincode,@br_stockLevel,@br_cpd,@br_StoreType,@br_storeGroup,@br_rdrIdExpress,@br_ranking," +
                $"@br_PyramidLevel,(case when (totalDriversAvailable(@br_Lat, @br_Lng, 10.0, @br_storeGroup) > 0) then 1 else 0 end),@br_courierDelivery,@br_rdrIdSlotted,@br_rdrIdCourier,@br_isdefaultstore," +
                $" @br_SalesOnline, @gst, @pgchargeId, @sdId, @tradeRestriction, @taxType, @address2, @address3, (ifnull(findNearestAreaId(@br_Lat, @br_Lng), 0))); select LAST_INSERT_ID()";
            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
            input.Add(new KeyValuePair<string, object>("br_Name", storename));
            input.Add(new KeyValuePair<string, object>("br_City", city));
            input.Add(new KeyValuePair<string, object>("br_District", district));
            input.Add(new KeyValuePair<string, object>("br_State", state));
            input.Add(new KeyValuePair<string, object>("br_Address", address));
            input.Add(new KeyValuePair<string, object>("br_Email", email));
            input.Add(new KeyValuePair<string, object>("br_Phone", mobile));
            input.Add(new KeyValuePair<string, object>("br_Incharge", contactperson));
            input.Add(new KeyValuePair<string, object>("branch_shortname", (shortname.Length > 5 ? shortname.Substring(0, 5) : shortname)));
            input.Add(new KeyValuePair<string, object>("br_key", branchKey));
            input.Add(new KeyValuePair<string, object>("br_ReferenceID", brRefID));
            input.Add(new KeyValuePair<string, object>("br_Lat", lat));
            input.Add(new KeyValuePair<string, object>("br_Lng", lng));
            input.Add(new KeyValuePair<string, object>("br_pincode", postalCode));
            input.Add(new KeyValuePair<string, object>("br_stockLevel", stockLevel));
            input.Add(new KeyValuePair<string, object>("br_cpd", cpd));
            input.Add(new KeyValuePair<string, object>("br_StoreType", storeType));
            input.Add(new KeyValuePair<string, object>("br_storeGroup", storegroupid));
            input.Add(new KeyValuePair<string, object>("br_rdrIdExpress", expressDelivery));
            input.Add(new KeyValuePair<string, object>("br_ranking", ranking));
            input.Add(new KeyValuePair<string, object>("br_PyramidLevel", pyramidLevel));
            input.Add(new KeyValuePair<string, object>("br_directDelivery", directDelivery));
            input.Add(new KeyValuePair<string, object>("br_courierDelivery", courierDelivery));
            input.Add(new KeyValuePair<string, object>("br_rdrIdSlotted", idSlotted));
            input.Add(new KeyValuePair<string, object>("br_rdrIdCourier", idCourier));
            input.Add(new KeyValuePair<string, object>("br_isdefaultstore", isDefault));
            input.Add(new KeyValuePair<string, object>("br_SalesOnline", br_SalesOnline));
            input.Add(new KeyValuePair<string, object>("gst", gst));
            input.Add(new KeyValuePair<string, object>("pgchargeId", pgchargeid));
            input.Add(new KeyValuePair<string, object>("sdId", settlementid));
            input.Add(new KeyValuePair<string, object>("tradeRestriction", tradeRestriction));
            input.Add(new KeyValuePair<string, object>("taxType", taxType));
            input.Add(new KeyValuePair<string, object>("address2", address2));
            input.Add(new KeyValuePair<string, object>("address3", address3));
            
            var result = DataServiceMySql.ExecuteScalar(sql, Service.UserService.GetAPIConnectionString(), input);

            int storeid = Convert.ToInt32(result);

            string strSqlGetCompanyId = "SELECT comp_id FROM finascop_company LIMIT 1";
            var objCompanyId = DataServiceMySql.ExecuteScalar(strSqlGetCompanyId, Service.UserService.GetAPIConnectionString());

            int companyId = Convert.ToInt32(objCompanyId);
            CreateCompany(storeid, companyId);



            return storeid;
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


        public static int CreateCompany(int branchId, int companyId)
        {
            string sql = $"INSERT INTO finascop_branch_company(br_Id,comp_id) VALUES(@br_Id,@comp_id);";
            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
            input.Add(new KeyValuePair<string, object>("br_Id", branchId));
            input.Add(new KeyValuePair<string, object>("comp_id", companyId));

            var result = DataServiceMySql.ExecuteSql(sql, Service.UserService.GetAPIConnectionString(), input);
            int companyid = Convert.ToInt32(result);
            return companyid;
        }

        public static int UpdateStore(int branchId, string storename, int storegroupid, string address, string address2, string address3, string city, int state,
            int district, string postalCode, string email, string mobile, string contactperson, string lat, string lng, int tradeRestriction = 0, int taxType = 0, int directDelivery=0, int courierDelivery=0)
        {
            string storeType = "Dealer";
            int stockLevel = 3;
            int cpd = -1;
            int expressDelivery = 1;
            int ranking = 2;
            int pyramidLevel = 4;           
            int idSlotted = 0;
            int idCourier = 0;

            string sql = $"UPDATE finascop_branch SET br_Name=@br_Name,br_City=@br_City,br_District=@br_District,br_State=@br_State," +
                $"br_Address=@br_Address,br_Email=@br_Email,br_Phone=@br_Phone,br_Incharge=@br_Incharge,br_Lat=@br_Lat,br_Lng=@br_Lng," +
                $"br_pincode=@br_pincode, tradeRestriction = @tradeRestriction, taxType = @taxType,br_directDelivery=@br_directDelivery," +
                $"br_courierDelivery=@br_courierDelivery, br_Address2=@address2, br_Address3=@address3 WHERE br_ID=@br_ID and br_storeGroup=@br_storeGroup";
            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
            input.Add(new KeyValuePair<string, object>("br_ID", branchId));
            input.Add(new KeyValuePair<string, object>("br_Name", storename));
            input.Add(new KeyValuePair<string, object>("br_City", city));
            input.Add(new KeyValuePair<string, object>("br_District", district));
            input.Add(new KeyValuePair<string, object>("br_State", state));
            input.Add(new KeyValuePair<string, object>("br_Address", address));
            input.Add(new KeyValuePair<string, object>("br_Email", email));
            input.Add(new KeyValuePair<string, object>("br_Phone", mobile));
            input.Add(new KeyValuePair<string, object>("br_Incharge", contactperson));
            //input.Add(new KeyValuePair<string, object>("branch_shortname", shortname));
            input.Add(new KeyValuePair<string, object>("br_Lat", lat));
            input.Add(new KeyValuePair<string, object>("br_Lng", lng));
            input.Add(new KeyValuePair<string, object>("br_pincode", postalCode));
            //input.Add(new KeyValuePair<string, object>("br_stockLevel", stockLevel));
            //input.Add(new KeyValuePair<string, object>("br_cpd", cpd));
            //input.Add(new KeyValuePair<string, object>("br_StoreType", storeType));
            input.Add(new KeyValuePair<string, object>("br_storeGroup", storegroupid));
            //input.Add(new KeyValuePair<string, object>("br_rdrIdExpress", expressDelivery));
            //input.Add(new KeyValuePair<string, object>("br_ranking", ranking));
            //input.Add(new KeyValuePair<string, object>("br_PyramidLevel", pyramidLevel));
            input.Add(new KeyValuePair<string, object>("br_directDelivery", directDelivery));
            input.Add(new KeyValuePair<string, object>("br_courierDelivery", courierDelivery));
            //input.Add(new KeyValuePair<string, object>("br_rdrIdSlotted", idSlotted));
            //input.Add(new KeyValuePair<string, object>("br_rdrIdCourier", idCourier));
            input.Add(new KeyValuePair<string, object>("tradeRestriction", tradeRestriction));
            input.Add(new KeyValuePair<string, object>("taxType", taxType));
            input.Add(new KeyValuePair<string, object>("address2", address2));
            input.Add(new KeyValuePair<string, object>("address3", address3));
            
            var result = DataServiceMySql.ExecuteScalar(sql, Service.UserService.GetAPIConnectionString(), input);
            if(result != null && result is int)
                return Convert.ToInt32(result);
            return -1;
        }

        public static List<Core.BussinessModel.Store.Store> GetStores(int storegroupid, int apistoregroupid, bool all = true)
        {
            List<Core.BussinessModel.Store.Store> stores = new List<Core.BussinessModel.Store.Store>(); //Core.Services.APIService.GetStores(storegroupid, all);

            List<KeyValuePair<string, object>> storeParams = new List<KeyValuePair<string, object>>();
            storeParams.Add(new KeyValuePair<string, object>("storegroupId", storegroupid));
            string sql = "select br.*, ba.BankName as storebankName, ba.Id as bankid, ba.AccountNumber, ba.Branch, ba.AccountName, gst.id as gstid, gst.gstin as gstnum, CONCAT_WS(' - ', ba.AccountNumber, NULLIF(ba.BankName, ''), NULLIF(ba.Branch, '')) AS combinedBankName, f.FSSAIName as storefssaiName, f.Id as fssaiid, f.AccountNumber, f.AccountName, f.AccountNumber + ' - ' + f.FSSAIName as combinedFssaiName from StoreBranch br left join gst on br.GSTId=gst.id left join BankAccount ba on br.BankId=ba.id left join FSSAI f on br.FSSAI_Id = f.Id where br.StoreId = @storegroupId";
            var dtStore = DataService.GetDataTable(sql, parmeters: storeParams);
            if (dtStore == null || dtStore.Rows.Count <= 0)
                return null;

            var storeGroupId = Core.Services.APIService.GetStores(apistoregroupid, all);

            foreach (var store in storeGroupId)
            {
                var drs = dtStore.Select("APIBranchId=" + store.BranchId);
                if (drs.Length > 0)
                {
                    if(drs[0]["gstid"] != DBNull.Value)
                        store.GSTId = (int?)drs[0]["gstid"];
                    if(drs[0]["bankid"] != DBNull.Value)
                        store.BankId = (int?)drs[0]["bankid"];
                    store.GSTIN = drs[0]["gstnum"].ToString();
                    store.Bank = drs[0]["combinedBankName"].ToString();
                    store.DBBranchid = (int)drs[0]["Id"];

                    if (drs[0]["fssaiid"] != DBNull.Value)
                    {
                        store.FSSAI_Id = (int?)drs[0]["fssaiid"];
                        store.FSSAI = drs[0]["combinedFssaiName"].ToString();
                    }

                    stores.Add(store);
                }
            }

            return stores;
        }

        public static string MatomoCreateSite(string name, string siteurl)
        {
            string matomoid = "";
            try
            {
                if (String.IsNullOrEmpty(siteurl))
                    return "";

                string url = ConfigurationManager.AppSettings.Get("MatomoUrl");
                string key = ConfigurationManager.AppSettings.Get("MatomoToken");
                if (String.IsNullOrEmpty(url) || String.IsNullOrEmpty(key))
                    return "";

               url += "index.php?module=API&method=SitesManager.addSite&format=json";
                List<KeyValuePair<string, string>> prms = new List<KeyValuePair<string, string>>();
                prms.Add(new KeyValuePair<string, string>("token_auth", key));
                prms.Add(new KeyValuePair<string, string>("siteName", name));
                prms.Add(new KeyValuePair<string, string>("urls", siteurl));

                var result = HttpHelperService.Post<object>(url, prms, -1);

                if (result != null)
                {
                    var dynamicObject = JsonConvert.DeserializeAnonymousType(result.ToString(), new { value = 0 });//JsonConvert.DeserializeAnonymousType(JsonConvert.SerializeObject(result), new { value = 0 });
                    matomoid = ""+ dynamicObject.value;

                    MatomoSetViewAccess(matomoid, siteurl);
                }
            }
            catch (Exception ex)
            {
            
            }

            return matomoid;
        }
        public static void MatomoSetViewAccess(string siteId, string siteurl)
        {
            try
            {
                if (String.IsNullOrEmpty(siteurl))
                    return;

                string url = ConfigurationManager.AppSettings.Get("MatomoUrl");
                string key = ConfigurationManager.AppSettings.Get("MatomoToken");
                string viewUser = ConfigurationManager.AppSettings.Get("MatomoViewUser");

                if (String.IsNullOrEmpty(url) || String.IsNullOrEmpty(key))
                    return;

                url += "index.php?module=API&method=UsersManager.setUserAccess&format=json";
                List<KeyValuePair<string, string>> prms = new List<KeyValuePair<string, string>>();
                prms.Add(new KeyValuePair<string, string>("token_auth", key));
                prms.Add(new KeyValuePair<string, string>("userLogin", viewUser));
                prms.Add(new KeyValuePair<string, string>("access", "view"));
                prms.Add(new KeyValuePair<string, string>("idSites", siteId));

                var result = HttpHelperService.Post<object>(url, prms, -1);

            }
            catch (Exception ex)
            {

            }
        }

        public static MerchantData MerchantPendingActions(int pendingOnly, int streogroupId)
        {
            string strPendingAtMySql = @"SELECT * FROM(
SELECT bg.store_group_id, bg.store_group_name
,(SELECT COUNT(DISTINCT emp_id) FROM qugeo_driver dr INNER JOIN finascop_branch b ON b.br_ID= dr.br_id WHERE br_status= 'Active' AND b.br_storeGroup= bg.store_group_id AND dr.d_Active=1 ) AS drivers
,(SELECT COUNT(DISTINCT stit_id) AS val FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE br_status= 'Active' AND b.br_storeGroup=bg.store_group_id ) AS products
,(SELECT COUNT(*) FROM finascop_branch WHERE br_storeGroup= bg.store_group_id AND br_SalesOnline = 1) AS storesOnline
,(SELECT COUNT(*) FROM finascop_branch WHERE br_storeGroup= bg.store_group_id) AS totalStores
,(SELECT COUNT(DISTINCT phone) FROM retaline_godown_boy gb INNER JOIN finascop_branch b ON b.br_ID=gb.branch_id WHERE br_status= 'Active' AND b.br_storeGroup= bg.store_group_id AND gb.status=1 ) AS orderpickers
,(SELECT COUNT(DISTINCT phone) FROM retaline_godown_boy gb INNER JOIN finascop_branch b ON b.br_ID=gb.branch_id WHERE br_status= 'Active' AND b.br_storeGroup= bg.store_group_id AND gb.status=1 AND is_offline=0 ) AS orderpickersOnline
,(SELECT COUNT(DISTINCT stit_id) AS val FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE br_status= 'Active' AND b.br_storeGroup=bg.store_group_id AND bi.item_count > 0 AND bi.mrp > 0 AND bi.selling_price > 0 ) AS productsWithStockAndPrice
,(SELECT COUNT(*) FROM `retaline_delivery_rules` WHERE rdr_storeGroupId=bg.store_group_id) AS DeliveryRules
,(SELECT COUNT(*) FROM finascop_stock_branch_inventory bi INNER JOIN  finascop_branch b ON b.br_ID=bi.branch_id 
INNER JOIN finascop_stock_itemmaster i ON bi.stit_id=i.stit_id INNER JOIN mypha_productsubcategory pc ON i.product_category = pc.sub_category_id AND pc.hasRestaurantService = 1
WHERE br_status= 'Active' AND b.br_storeGroup = bg.store_group_id AND b.platform_tax_enabled=1) restaurantProducts 
,(SELECT COUNT(*) FROM store_paymentgateway_connect WHERE storeGroupId=@soregroupid) AS subaccount
FROM finascop_branch_group bg WHERE (@soregroupid=0 OR bg.store_group_id = @soregroupid)
) AS tbl WHERE (@hasPendingJobsOnly = 0 OR (drivers <= 0 OR products <= 0 OR totalStores <= 0 OR orderpickers <= 0))
";

            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
            input.Add(new KeyValuePair<string, object>("soregroupid", streogroupId));
            input.Add(new KeyValuePair<string, object>("hasPendingJobsOnly", pendingOnly));
            var tblMysqlItems = DataServiceMySql.GetDataTable(strPendingAtMySql, Service.UserService.GetAPIConnectionString(), input);

            string strPendingSqlServer = @"select * from(
select Id, [Name], StoreId, isnull(CanCheckout,0) as CanCheckout, isnull(OnlinePaymentEnabled,0) as OnlinePaymentEnabled, isnull(PODEnabled,0) as PODEnabled, isnull(ShowPWA, 0) as ShowPWA 
    ,(SELECT COUNT(*) as storesWithBank FROM StoreBranch WHERE StoreId= a.Id AND bankid >0) as storesWithBank 
    ,(SELECT COUNT(*) as bankAccounts FROM BankAccount WHERE TenantId= a.Id) as bankAccounts
    ,(SELECT COUNT(*) as bankLinkedToStore FROM BankAccount WHERE TenantId= a.Id AND Id in (select bankid FROM StoreBranch WHERE StoreId= a.Id)) as bankLinkedToStore
    ,(SELECT COUNT(*) as gsts FROM GST WHERE TenantId= a.Id ) as gsts
    ,(SELECT COUNT(*) as gstNotLinkedToStore FROM GST WHERE tenantid= a.Id AND id not in(select gstid FROM StoreBranch WHERE StoreId= a.Id) ) as gstnotLinkedToStore
    ,(SELECT COUNT(*) as gsts FROM GST WHERE TenantId= a.Id AND isverified = 0) as gstNotVerified
	,(SELECT COUNT(*) as storesWithoutBank FROM StoreBranch WHERE StoreId=a.Id and ( BankId IS NULL OR BankId NOT IN(SELECT id from BankAccount where TenantId=a.Id))) as storesWithoutBank
	,(SELECT COUNT(*) as fssai FROM FSSAI WHERE TenantId= a.Id) as fssais
	,(SELECT COUNT(*) as fssaiNotLinkedToStore FROM FSSAI WHERE TenantId= a.Id AND Id not in(select FSSAI_Id FROM StoreBranch WHERE StoreId= a.Id)) as fssaiNotLinkedToStore 
	,(select count(*) from [User] u inner join User_UserRole_Mapping rm on rm.UserId=u.Id inner join UserRole r on rm.RoleId=r.Id where r.RoleType=2 and u.StoreGroupId = a.id ) as storeUsers

	,(select count(*) from [User] u inner join User_UserRole_Mapping rm on rm.UserId=u.Id inner join UserRole r on rm.RoleId=r.Id where r.RoleType=2 and u.StoreGroupId = a.id and u.hasVerifiedEmail > 0) as emailVerified
	,(select count(*) from [User] u inner join User_UserRole_Mapping rm on rm.UserId=u.Id inner join UserRole r on rm.RoleId=r.Id where r.RoleType=2 and u.StoreGroupId = a.id  and u.hasVerifiedMobile > 0) as mobileVerified
    , TenantType
	from AppTenant a WHERE a.StoreId > 0 and (@soregroupid=0 OR a.StoreId = @soregroupid)
) t WHERE @hasPendingJobsOnly = 0 OR isnull(CanCheckout, 0) <= 0 or storesWithBank <= 0 or bankAccounts <= 0 or gsts <= 0 or gstnotLinkedToStore <= 0 or storesWithoutBank > 0
";
            DataTable dtSqlResult = DataService.GetDataTable(strPendingSqlServer, parmeters: input);
            MerchantData merchantData = new MerchantData();
            if(dtSqlResult.Rows.Count > 0)
            {
                DataRow r = dtSqlResult.Rows[0];
                merchantData.StoregroupId = Convert.ToInt32(r["Id"]);
                merchantData.MerchantName = r["name"].ToString();
                merchantData.CanCheckout = (bool)r["CanCheckout"];
                merchantData.PayOnline = (bool)r["OnlinePaymentEnabled"];
                merchantData.PodEnabled = (bool)r["PODEnabled"];
                merchantData.HasPWA = (bool)r["ShowPWA"];
                merchantData.StoresWithBank = (int)r["storesWithBank"];
                merchantData.BankAccounts = (int)r["bankAccounts"];
                merchantData.BankAccountLinkedToStores = (int)r["bankLinkedToStore"];
                merchantData.GSTs = (int)r["gsts"];
                merchantData.GSTsNotLinkedToStore = (int)r["gstnotLinkedToStore"];
                merchantData.GSTNotVerified = (int)r["gstNotVerified"];
                merchantData.StoresWithoutBank = (int)r["storesWithoutBank"];
                merchantData.FSSAIs = (int)r["fssais"];
                merchantData.FSSAIsNotLinkedToStore = (int)r["fssaiNotLinkedToStore"];
                merchantData.StoreUsers = (int)r["storeUsers"];
                merchantData.EmailVerified = (int)r["emailVerified"];
                merchantData.MobileVerified = (int)r["mobileVerified"];
                merchantData.APIStoregroupId = Convert.ToInt32(r["StoreId"]);
                merchantData.TenantType = Convert.ToInt32(r["TenantType"]);
            }

            if(tblMysqlItems.Rows.Count > 0)
            {
                DataRow r = tblMysqlItems.Rows[0];
                merchantData.APIStoregroupId = Convert.ToInt32(r["store_group_id"]);
                merchantData.MerchantName = r["store_group_name"].ToString();
                merchantData.Drivers = Convert.ToInt32(r["drivers"]);
                merchantData.Products = Convert.ToInt32(r["products"]);
                merchantData.StoresOnline = Convert.ToInt32(r["storesOnline"]);
                merchantData.TotalStores = Convert.ToInt32(r["totalStores"]);
                merchantData.OrderPickers = Convert.ToInt32(r["orderpickers"]);
                merchantData.OrderPickersOnline = Convert.ToInt32(r["orderpickersOnline"]);
                merchantData.ProductsWithStockAndPrice = Convert.ToInt32(r["productsWithStockAndPrice"]);
                merchantData.DeliveryRules = Convert.ToInt32(r["DeliveryRules"]);
                merchantData.RestaurantProducts = Convert.ToInt32(r["restaurantProducts"]);
                merchantData.Subaccount= Convert.ToInt32(r["subaccount"]);
            }

            if (streogroupId > 0)
            {
                DataTable checkLanguageSql = DataServiceMySql.GetDataTable("SELECT COUNT(*) AS languagecount FROM language_mapping WHERE type=2 AND typeId = @soregroupid",
                    UserService.GetAPIConnectionString(), input);
                if (checkLanguageSql != null && checkLanguageSql.Rows.Count > 0)
                {
                    DataRow dr = checkLanguageSql.Rows[0];
                    merchantData.MerchantLanguage = Convert.ToInt32(dr["languagecount"]);
                }
                
            }

                SetPendingActivity(merchantData);

            return merchantData;
        }

        public static List<MerchantData> MerchantsWithPendingActions(int pendingOnly, int streogroupId)
        {
            string strPendingAtMySql = @"SELECT * FROM(
SELECT bg.store_group_id, bg.store_group_name
,(SELECT COUNT(DISTINCT emp_id) FROM qugeo_driver dr INNER JOIN finascop_branch b ON b.br_ID= dr.br_id WHERE br_status= 'Active' AND b.br_storeGroup= bg.store_group_id AND dr.d_Active=1 ) AS drivers
,(SELECT COUNT(DISTINCT stit_id) AS val FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE br_status= 'Active' AND b.br_storeGroup=bg.store_group_id ) AS products
,(SELECT COUNT(*) FROM finascop_branch WHERE br_storeGroup= bg.store_group_id AND br_SalesOnline = 1) AS storesOnline
,(SELECT COUNT(*) FROM finascop_branch WHERE br_storeGroup= bg.store_group_id) AS totalStores
,(SELECT COUNT(DISTINCT phone) FROM retaline_godown_boy gb INNER JOIN finascop_branch b ON b.br_ID=gb.branch_id WHERE br_status= 'Active' AND b.br_storeGroup= bg.store_group_id AND gb.status=1 ) AS orderpickers
,(SELECT COUNT(DISTINCT phone) FROM retaline_godown_boy gb INNER JOIN finascop_branch b ON b.br_ID=gb.branch_id WHERE br_status= 'Active' AND b.br_storeGroup= bg.store_group_id AND gb.status=1 AND is_offline=0 ) AS orderpickersOnline
,(SELECT COUNT(DISTINCT stit_id) AS val FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE br_status= 'Active' AND b.br_storeGroup=bg.store_group_id AND bi.item_count > 0 AND bi.mrp > 0 AND bi.selling_price > 0 ) AS productsWithStockAndPrice
,(SELECT COUNT(*) FROM `retaline_delivery_rules` WHERE rdr_storeGroupId=bg.store_group_id) AS DeliveryRules
,(SELECT COUNT(*) FROM finascop_stock_branch_inventory bi INNER JOIN  finascop_branch b ON b.br_ID=bi.branch_id 
INNER JOIN finascop_stock_itemmaster i ON bi.stit_id=i.stit_id INNER JOIN mypha_productsubcategory pc ON i.product_category = pc.sub_category_id AND pc.hasRestaurantService = 1
WHERE br_status= 'Active' AND b.br_storeGroup = bg.store_group_id AND b.platform_tax_enabled=1) restaurantProducts 
FROM finascop_branch_group bg WHERE (@soregroupid=0 OR bg.store_group_id = @soregroupid)
) AS tbl WHERE (@hasPendingJobsOnly = 0 OR (drivers <= 0 OR products <= 0 OR totalStores <= 0 OR orderpickers <= 0))
";

            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
            input.Add(new KeyValuePair<string, object>("soregroupid", streogroupId));
            input.Add(new KeyValuePair<string, object>("hasPendingJobsOnly", 0));
            var tblMysqlItems = DataServiceMySql.GetDataTable(strPendingAtMySql, Service.UserService.GetAPIConnectionString(), input);

            string strPendingSqlServer = @"select * from(
select Id, [Name], StoreId, isnull(CanCheckout,0) as CanCheckout, isnull(OnlinePaymentEnabled,0) as OnlinePaymentEnabled, isnull(PODEnabled,0) as PODEnabled, isnull(ShowPWA, 0) as ShowPWA 
,LogoImage, CreatedOn
    ,(SELECT COUNT(*) as storesWithBank FROM StoreBranch WHERE StoreId= a.Id AND bankid >0) as storesWithBank 
    ,(SELECT COUNT(*) as bankAccounts FROM BankAccount WHERE TenantId= a.Id) as bankAccounts
    ,(SELECT COUNT(*) as bankLinkedToStore FROM BankAccount WHERE TenantId= a.Id AND Id in (select bankid FROM StoreBranch WHERE StoreId= a.Id)) as bankLinkedToStore
    ,(SELECT COUNT(*) as gsts FROM GST WHERE TenantId= a.Id ) as gsts
    ,(SELECT COUNT(*) as gstNotLinkedToStore FROM GST WHERE tenantid= a.Id AND id not in(select gstid FROM StoreBranch WHERE StoreId= a.Id) ) as gstnotLinkedToStore
    ,(SELECT COUNT(*) as gsts FROM GST WHERE TenantId= a.Id AND isverified = 0) as gstNotVerified
	,(SELECT COUNT(*) as storesWithoutBank FROM StoreBranch WHERE StoreId=a.Id and ( BankId IS NULL OR BankId NOT IN(SELECT id from BankAccount where TenantId=a.Id))) as storesWithoutBank
	,(SELECT COUNT(*) as fssai FROM FSSAI WHERE TenantId= a.Id) as fssais
	,(SELECT COUNT(*) as fssaiNotLinkedToStore FROM FSSAI WHERE TenantId= a.Id AND Id not in(select FSSAI_Id FROM StoreBranch WHERE StoreId= a.Id)) as fssaiNotLinkedToStore 
	,(select count(*) from [User] u inner join User_UserRole_Mapping rm on rm.UserId=u.Id inner join UserRole r on rm.RoleId=r.Id where r.RoleType=2 and u.StoreGroupId = a.id ) as storeUsers

	,(select count(*) from [User] u inner join User_UserRole_Mapping rm on rm.UserId=u.Id inner join UserRole r on rm.RoleId=r.Id where r.RoleType=2 and u.StoreGroupId = a.id and u.hasVerifiedEmail > 0) as emailVerified
	,(select count(*) from [User] u inner join User_UserRole_Mapping rm on rm.UserId=u.Id inner join UserRole r on rm.RoleId=r.Id where r.RoleType=2 and u.StoreGroupId = a.id  and u.hasVerifiedMobile > 0) as mobileVerified
    , TenantType
	from AppTenant a WHERE a.StoreId > 0 and (@soregroupid=0 OR a.StoreId = @soregroupid)
) t WHERE @hasPendingJobsOnly = 0 OR isnull(CanCheckout, 0) <= 0 or storesWithBank <= 0 or bankAccounts <= 0 or gsts <= 0 or gstnotLinkedToStore <= 0 or storesWithoutBank > 0
";
            DataTable dtSqlResult = DataService.GetDataTable(strPendingSqlServer, parmeters: input);
            List<MerchantData> merchantDataList = new List<MerchantData>();

            if (dtSqlResult.Rows.Count > 0)
            {
                foreach (DataRow r in dtSqlResult.Rows)
                {
                    bool isNew = true;
                    int storeGroupId = Convert.ToInt32(r["Id"]);
                    MerchantData merchantData = new MerchantData();
                    if (merchantDataList.Any(m => m.StoregroupId == storeGroupId))
                    {
                        merchantData = merchantDataList.Where(m => m.StoregroupId == storeGroupId).FirstOrDefault();
                        isNew = false;
                    }

                    merchantData.StoregroupId = Convert.ToInt32(r["Id"]);
                    merchantData.MerchantName = r["name"].ToString();
                    merchantData.CanCheckout = (bool)r["CanCheckout"];
                    merchantData.PayOnline = (bool)r["OnlinePaymentEnabled"];
                    merchantData.PodEnabled = (bool)r["PODEnabled"];
                    merchantData.HasPWA = (bool)r["ShowPWA"];
                    merchantData.StoresWithBank = (int)r["storesWithBank"];
                    merchantData.BankAccounts = (int)r["bankAccounts"];
                    merchantData.BankAccountLinkedToStores = (int)r["bankLinkedToStore"];
                    merchantData.GSTs = (int)r["gsts"];
                    merchantData.GSTsNotLinkedToStore = (int)r["gstnotLinkedToStore"];
                    merchantData.GSTNotVerified = (int)r["gstNotVerified"];
                    merchantData.StoresWithoutBank = (int)r["storesWithoutBank"];
                    merchantData.FSSAIs = (int)r["fssais"];
                    merchantData.FSSAIsNotLinkedToStore = (int)r["fssaiNotLinkedToStore"];
                    merchantData.StoreUsers = (int)r["storeUsers"];
                    merchantData.EmailVerified = (int)r["emailVerified"];
                    merchantData.MobileVerified = (int)r["mobileVerified"];
                    merchantData.APIStoregroupId = Convert.ToInt32(r["StoreId"]);
                    merchantData.TenantType = Convert.ToInt32(r["TenantType"]);
                    merchantData.LogoImage = r["LogoImage"].ToString();
                    merchantData.CreatedOn = (DateTime)r["CreatedOn"];
                    if (isNew)
                        merchantDataList.Add(merchantData);
                }
            }

            if (tblMysqlItems.Rows.Count > 0)
            {
                foreach (DataRow r in tblMysqlItems.Rows)
                {
                    int apiStoreGroupId = Convert.ToInt32(r["store_group_id"]);
                    var merchantData = merchantDataList.Where(m => m.APIStoregroupId == apiStoreGroupId).FirstOrDefault();

                    if (merchantData == null)
                        continue;

                    merchantData.APIStoregroupId = Convert.ToInt32(r["store_group_id"]);
                    merchantData.MerchantName = r["store_group_name"].ToString();
                    merchantData.Drivers = Convert.ToInt32(r["drivers"]);
                    merchantData.Products = Convert.ToInt32(r["products"]);
                    merchantData.StoresOnline = Convert.ToInt32(r["storesOnline"]);
                    merchantData.TotalStores = Convert.ToInt32(r["totalStores"]);
                    merchantData.OrderPickers = Convert.ToInt32(r["orderpickers"]);
                    merchantData.OrderPickersOnline = Convert.ToInt32(r["orderpickersOnline"]);
                    merchantData.ProductsWithStockAndPrice = Convert.ToInt32(r["productsWithStockAndPrice"]);
                    merchantData.DeliveryRules = Convert.ToInt32(r["DeliveryRules"]);
                    merchantData.RestaurantProducts = Convert.ToInt32(r["restaurantProducts"]);
                }
            }
            if (streogroupId > 0)
            {
                DataTable checkLanguageSql = DataServiceMySql.GetDataTable("SELECT COUNT(*) AS languagecount FROM language_mapping WHERE type=2 AND typeId = @soregroupid",
                    UserService.GetAPIConnectionString(), input);
                if (checkLanguageSql != null && checkLanguageSql.Rows.Count > 0)
                {
                    foreach (DataRow r in checkLanguageSql.Rows)
                    {
                        bool isNew = true;
                        int storeGroupId = Convert.ToInt32(r["Id"]);
                        MerchantData merchantData = new MerchantData();
                        if (merchantDataList.Any(m => m.StoregroupId == storeGroupId))
                        {
                            merchantData = merchantDataList.Where(m => m.StoregroupId == storeGroupId).FirstOrDefault();
                            isNew = false;
                        }
                        DataRow dr = checkLanguageSql.Rows[0];
                        merchantData.MerchantLanguage = Convert.ToInt32(dr["languagecount"]);
                    }
                }

            }

            foreach (var merchantData in merchantDataList)
                SetPendingActivity(merchantData);
            if(pendingOnly == 1)
                return merchantDataList.Where(m=> m.PendingActions.Count() >0).ToList();

            return merchantDataList;
        }


        private static void SetPendingActivity(MerchantData merchantData)
        {
            int pendingBankAccountLinkedToStore = (merchantData.BankAccounts - merchantData.BankAccountLinkedToStores);

            if (merchantData != null) {
                // Pending Actions
                if (ConfigurationManager.AppSettings.Get("PaymentGatewaySubAccount") == "0" && merchantData.BankAccounts <= 0 )
                    merchantData.PendingActions.Add(new PendingActvity {Name= "bankAccounts", Type= PendingActvityType.Action, Count= merchantData.BankAccounts, Description="No bank account added." });
                if (ConfigurationManager.AppSettings.Get("PaymentGatewaySubAccount") == "0" && merchantData.StoresWithoutBank > 0)
                    merchantData.PendingActions.Add(new PendingActvity {Name= "storesWithoutBank", Type= PendingActvityType.Action, Count= merchantData.StoresWithoutBank, Description="Store/s without bank account." });
                // Moved to Pending Jobs.
                //if (merchantData.GSTs <= 0)
                //    merchantData.PendingActions.Add(new PendingActvity {Name= "gstscount", Type= PendingActvityType.Action, Count= merchantData.GSTs, Description= ConfigurationManager.AppSettings.Get("CountryCode") == "UK" ? "No VAT added." : "No GSTIN added." });

                //if (merchantData.GSTsNotLinkedToStore < 0)
                //    merchantData.PendingActions.Add(new PendingActvity {Name= "gstNotLinkedToStore", Type= PendingActvityType.Action, Count= merchantData.GSTsNotLinkedToStore, Description="GSTIN not linked to store." });
                if (merchantData.OrderPickers <= 0)
                    merchantData.PendingActions.Add(new PendingActvity {Name= "orderPickers", Type= PendingActvityType.Action, Count= merchantData.OrderPickers, Description="No order picker created." });
                if (merchantData.ProductsWithStockAndPrice <= 0)
                    merchantData.PendingActions.Add(new PendingActvity {Name= "products", Type= PendingActvityType.Action, Count= merchantData.ProductsWithStockAndPrice, Description="No product listed with stock and price." });
                if (merchantData.TotalStores <= 0)
                    merchantData.PendingActions.Add(new PendingActvity {Name= "branches", Type= PendingActvityType.Action, Count= merchantData.TotalStores, Description="No store is created or active." });
                //if (merchantData.DeliveryRules <= 0)
                //    merchantData.PendingActions.Add(new PendingActvity { Name = "deliveryRules", Type = PendingActvityType.Action, Count = merchantData.DeliveryRules, Description = "No delivery rule created." });
                if (merchantData.RestaurantProducts > 0 && merchantData.FSSAIs <= 0)
                    merchantData.PendingActions.Add(new PendingActvity { Name = "fssaiCount", Type = PendingActvityType.Action, Count = merchantData.DeliveryRules, Description = "No FSSAI added." });
                if (merchantData.RestaurantProducts > 0 && merchantData.FSSAIsNotLinkedToStore > 0)
                    merchantData.PendingActions.Add(new PendingActvity {Name= "fssaiNotLinked", Type= PendingActvityType.Action, Count= merchantData.FSSAIsNotLinkedToStore, Description="FSSAI link to store is pending ." });
                if (merchantData.MerchantLanguage <= 0 && (ConfigurationManager.AppSettings.Get("CountryCode") != "UK"))
                    merchantData.PendingActions.Add(new PendingActvity { Name = "merchantLanguagePreference", Type = PendingActvityType.Action, Count = merchantData.MerchantLanguage, Description = "Set Language preference." });
                

                    // Pending Jobs
                    if (merchantData.GSTNotVerified > 0)
                    merchantData.PendingJobs.Add(new PendingActvity {Name= "gstnNotVerified", Type= PendingActvityType.Job, Count= merchantData.GSTNotVerified, Description="GST pending for verification" });
                if (merchantData.StoresOnline <= 0)
                    merchantData.PendingJobs.Add(new PendingActvity {Name= "totalStores", Type= PendingActvityType.Job, Count= merchantData.StoresOnline, Description="No store is online" });
                if (ConfigurationManager.AppSettings.Get("PaymentGatewaySubAccount") == "0" && merchantData.BankAccountLinkedToStores <= 0)
                    merchantData.PendingJobs.Add(new PendingActvity {Name= "bankLinkedToStore", Type= PendingActvityType.Job, Count= merchantData.BankAccountLinkedToStores, Description="Bank account is not linked to store" });
                //if (merchantData.BankAccountLinkedToStores > 0 && pendingBankAccountLinkedToStore > 0)
                //    merchantData.PendingJobs.Add(new PendingActvity { Name = "bankNoLinkedToStore", Type = PendingActvityType.Job, Count = pendingBankAccountLinkedToStore, Description = "One or more bank account not linked to store" });
                if (merchantData.OrderPickersOnline <= 0)
                    merchantData.PendingJobs.Add(new PendingActvity { Name = "orderPickersOnline", Type = PendingActvityType.Job, Count = merchantData.OrderPickersOnline, Description = "No order picker is online for processing." });
                if (merchantData.Drivers <= 0)
                    merchantData.PendingJobs.Add(new PendingActvity { Name = "drivers", Type = PendingActvityType.Job, Count = merchantData.Drivers, Description = "No driver created." });
                // Email and mobile verification are not part of the restriction. So these can be removed from the list, as per confirmed by BR.
                //if (merchantData.MobileVerified <= 0)
                //    merchantData.PendingJobs.Add(new PendingActvity { Name = "mobileVerified", Type = PendingActvityType.Job, Count = merchantData.MobileVerified, Description = "Mobile verification is pending." });
                //if (merchantData.EmailVerified <= 0)
                //    merchantData.PendingJobs.Add(new PendingActvity { Name = "emailverified", Type = PendingActvityType.Action, Count = merchantData.EmailVerified, Description = "Email verification is pending." });
                if (merchantData.TenantType == 1 && merchantData.GSTs <= 0)
                    merchantData.PendingJobs.Add(new PendingActvity { Name = "gstscount", Type = PendingActvityType.Job, Count = merchantData.GSTs, Description = ConfigurationManager.AppSettings.Get("CountryCode") == "IN" ? "No GSTIN added." : "No VAT added." });
                if (merchantData.Subaccount <= 0 && ConfigurationManager.AppSettings.Get("PaymentGatewaySubAccount") == "1")
                    merchantData.PendingJobs.Add(new PendingActvity { Name = "subaccount", Type = PendingActvityType.Job, Count = merchantData.Subaccount, Description = "Payment Gateway Account not added." });

            }

            //return merchantData;
        }


        public static List<MerchantData> MerchantsReport(int period, int areaId, string storeNamePref="")
        {
            string strPendingAtMySql = @"SELECT * FROM(
SELECT bg.store_group_id, bg.store_group_name, isFeatured, ifnull(bo.num, 0) num, ifnull(bo.total, 0) total, crpr_orgName, crpr_mode, roName, baName, areaName, areaLocation, areaId
,(SELECT COUNT(DISTINCT emp_id) FROM qugeo_driver dr INNER JOIN finascop_branch b ON b.br_ID= dr.br_id WHERE br_status= 'Active' AND b.br_storeGroup= bg.store_group_id AND dr.d_Active=1 ) AS drivers
,(SELECT COUNT(DISTINCT stit_id) AS val FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE br_status= 'Active' AND b.br_storeGroup=bg.store_group_id ) AS products
,gb.storesOnline, gb.brNames, gb.br_areaNames, gb.br_areaIds, gb.totalStores

,(SELECT COUNT(DISTINCT phone) FROM retaline_godown_boy gb INNER JOIN finascop_branch b ON b.br_ID=gb.branch_id WHERE br_status= 'Active' AND b.br_storeGroup= bg.store_group_id AND gb.status=1 ) AS orderpickers
,(SELECT COUNT(DISTINCT phone) FROM retaline_godown_boy gb INNER JOIN finascop_branch b ON b.br_ID=gb.branch_id WHERE br_status= 'Active' AND b.br_storeGroup= bg.store_group_id AND gb.status=1 AND is_offline=0 ) AS orderpickersOnline
,(SELECT COUNT(DISTINCT stit_id) AS val FROM finascop_stock_branch_inventory bi INNER JOIN finascop_branch b ON b.br_ID=bi.branch_id WHERE br_status= 'Active' AND b.br_storeGroup=bg.store_group_id AND bi.item_count > 0 AND bi.mrp > 0 AND bi.selling_price > 0 ) AS productsWithStockAndPrice
,(SELECT COUNT(*) FROM `retaline_delivery_rules` WHERE rdr_storeGroupId=bg.store_group_id) AS DeliveryRules
FROM finascop_branch_group bg left join (SELECT b.br_StoreGroup, COUNT(*) AS num, SUM(total) AS total FROM `retaline_customer_order` o INNER JOIN finascop_branch b ON o.order_branch_id=b.br_ID WHERE o.status_id > 3 GROUP BY b.br_StoreGroup) bo on bo.br_StoreGroup=bg.store_group_id
left join (SELECT br_storeGroup, GROUP_CONCAT(br_Name) brNames, GROUP_CONCAT(areaName) br_areaNames, GROUP_CONCAT(b.areaId) br_areaIds, COUNT(*) AS totalStores, COUNT(DISTINCT CASE WHEN br_SalesOnline = 1 THEN br_ID END) AS storesOnline FROM finascop_branch b LEFT JOIN area_entries a ON b.areaId=a.id GROUP BY br_storeGroup) gb on gb.br_StoreGroup=bg.store_group_id

left join(SELECT pr.id, pr.crpr_orgName, pr.crpr_mode, pr.areaId, ro.roName, ba.baName, a.areaName, a.areaLocation FROM finascop_crm_prospect pr 
LEFT JOIN relationship_officer ro ON pr.assignedRO=ro.id LEFT JOIN business_associate ba ON ba.id=pr.baId LEFT JOIN area_entries a ON pr.areaId=a.id) ro on bg.prospect_Id=ro.id 
WHERE (ifnull(@areaId, 0) <= 0 or find_in_set(@areaId, gb.br_areaIds) ) and (ifnull(@period, 0)=0 OR 1=1) 
) AS tbl WHERE (@hasPendingJobsOnly = 0 OR (drivers <= 0 OR products <= 0 OR totalStores <= 0 OR orderpickers <= 0))
";

            List<KeyValuePair<string, object>> input = new List<KeyValuePair<string, object>>();
            input.Add(new KeyValuePair<string, object>("hasPendingJobsOnly", 0));

            input.Add(new KeyValuePair<string, object>("period", period));
            input.Add(new KeyValuePair<string, object>("areaId", areaId));
            input.Add(new KeyValuePair<string, object>("storeNamePref", storeNamePref));


            var tblMysqlItems = DataServiceMySql.GetDataTable(strPendingAtMySql, Service.UserService.GetAPIConnectionString(), input);

            if (tblMysqlItems == null || tblMysqlItems.Rows.Count <= 0)
                return default;

            string strPendingSqlServer = @"select * from (
select Id, [Name], StoreId, isnull(CanCheckout,0) as CanCheckout, isnull(OnlinePaymentEnabled,0) as OnlinePaymentEnabled, isnull(PODEnabled,0) as PODEnabled, isnull(ShowPWA, 0) as ShowPWA 
,LogoImage, CreatedOn, isnull(m.PlanName, 'Growth') as PlanName
    ,(SELECT COUNT(*) as storesWithBank FROM StoreBranch WHERE StoreId= a.Id AND bankid >0) as storesWithBank 
    ,(SELECT COUNT(*) as bankAccounts FROM BankAccount WHERE TenantId= a.Id) as bankAccounts
    ,(SELECT COUNT(*) as bankLinkedToStore FROM BankAccount WHERE TenantId= a.Id AND Id in (select bankid FROM StoreBranch WHERE StoreId= a.Id)) as bankLinkedToStore
    ,(SELECT COUNT(*) as gsts FROM GST WHERE TenantId= a.Id ) as gsts
    ,(SELECT COUNT(*) as gstNotLinkedToStore FROM GST WHERE tenantid= a.Id AND id not in(select gstid FROM StoreBranch WHERE StoreId= a.Id) ) as gstnotLinkedToStore
    ,(SELECT COUNT(*) as gsts FROM GST WHERE TenantId= a.Id AND isverified = 0) as gstNotVerified
	,(SELECT COUNT(*) as storesWithoutBank FROM StoreBranch WHERE StoreId=a.Id and ( BankId IS NULL OR BankId NOT IN(SELECT id from BankAccount where TenantId=a.Id))) as storesWithoutBank
	,(SELECT COUNT(*) as fssai FROM FSSAI WHERE TenantId= a.Id) as fssais
	,(SELECT COUNT(*) as fssaiNotLinkedToStore FROM FSSAI WHERE TenantId= a.Id AND Id not in(select FSSAI_Id FROM StoreBranch WHERE StoreId= a.Id)) as fssaiNotLinkedToStore 
	,(select count(*) from [User] u inner join User_UserRole_Mapping rm on rm.UserId=u.Id inner join UserRole r on rm.RoleId=r.Id where r.RoleType=2 and u.StoreGroupId = a.id ) as storeUsers
	,(select count(*) from [User] u inner join User_UserRole_Mapping rm on rm.UserId=u.Id inner join UserRole r on rm.RoleId=r.Id where r.RoleType=2 and u.StoreGroupId = a.id and u.hasVerifiedEmail > 0) as emailVerified
	,(select count(*) from [User] u inner join User_UserRole_Mapping rm on rm.UserId=u.Id inner join UserRole r on rm.RoleId=r.Id where r.RoleType=2 and u.StoreGroupId = a.id  and u.hasVerifiedMobile > 0) as mobileVerified
    , TenantType from AppTenant a left join (select isnull(s.PlanName, 'Growth') as PlanName, m.MerchantID from S_SubscriptionPlans s left join S_MerchantSubscriptions m on s.Id = m.PlanID where s.[Type]=0 ) m on a.Id= m.MerchantID

WHERE a.StoreId > 0 and (isnull(@storeNamePref, '') = '' or (concat('%', [Name], '%') like @storeNamePref) and 
(isnull(@period, 0)=0 OR (@period = 1 and month(CreatedOn) = month(getdate() ))  OR (@period = 2 and month(CreatedOn) = month(DATEADD(month, -1, getdate())) ) ) )
) t WHERE @hasPendingJobsOnly = 0 OR isnull(CanCheckout, 0) <= 0 or storesWithBank <= 0 or bankAccounts <= 0 or gsts <= 0 or gstnotLinkedToStore <= 0 or storesWithoutBank > 0
";
            DataTable dtSqlResult = DataService.GetDataTable(strPendingSqlServer, parmeters: input);
            if (dtSqlResult == null || dtSqlResult.Rows.Count <= 0)
                return default;

            List<MerchantData> merchantDataList = new List<MerchantData>();

            if (dtSqlResult.Rows.Count > 0)
            {
                foreach (DataRow r in dtSqlResult.Rows)
                {
                    bool isNew = true;
                    int storeGroupId = Convert.ToInt32(r["Id"]);
                    int apiStoreId = Convert.ToInt32(r["StoreId"]);
                    if (!tblMysqlItems.Select($"store_group_id = {apiStoreId}").Any())
                        continue;

                    MerchantData merchantData = new MerchantData();
                    if (merchantDataList.Any(m => m.StoregroupId == storeGroupId))
                    {
                        merchantData = merchantDataList.Where(m => m.StoregroupId == storeGroupId).FirstOrDefault();
                        isNew = false;
                    }

                    merchantData.StoregroupId = Convert.ToInt32(r["Id"]);
                    merchantData.MerchantName = r["name"].ToString();
                    merchantData.CanCheckout = (bool)r["CanCheckout"];
                    merchantData.PayOnline = (bool)r["OnlinePaymentEnabled"];
                    merchantData.PodEnabled = (bool)r["PODEnabled"];
                    merchantData.HasPWA = (bool)r["ShowPWA"];
                    merchantData.StoresWithBank = (int)r["storesWithBank"];
                    merchantData.BankAccounts = (int)r["bankAccounts"];
                    merchantData.BankAccountLinkedToStores = (int)r["bankLinkedToStore"];
                    merchantData.GSTs = (int)r["gsts"];
                    merchantData.GSTsNotLinkedToStore = (int)r["gstnotLinkedToStore"];
                    merchantData.GSTNotVerified = (int)r["gstNotVerified"];
                    merchantData.StoresWithoutBank = (int)r["storesWithoutBank"];
                    merchantData.FSSAIs = (int)r["fssais"];
                    merchantData.FSSAIsNotLinkedToStore = (int)r["fssaiNotLinkedToStore"];
                    merchantData.StoreUsers = (int)r["storeUsers"];
                    merchantData.EmailVerified = (int)r["emailVerified"];
                    merchantData.MobileVerified = (int)r["mobileVerified"];
                    merchantData.APIStoregroupId = apiStoreId; // Convert.ToInt32(r["StoreId"]);
                    merchantData.TenantType = Convert.ToInt32(r["TenantType"]);
                    merchantData.LogoImage = r["LogoImage"].ToString();
                    merchantData.CreatedOn = (DateTime)r["CreatedOn"];
                    merchantData.PlanName = r["PlanName"].ToString();
                    if (isNew)
                        merchantDataList.Add(merchantData);
                }
            }

            if (tblMysqlItems.Rows.Count > 0)
            {
                foreach (DataRow r in tblMysqlItems.Rows)
                {
                    int apiStoreGroupId = Convert.ToInt32(r["store_group_id"]);
                    var merchantData = merchantDataList.Where(m => m.APIStoregroupId == apiStoreGroupId).FirstOrDefault();

                    if (merchantData == null)
                        continue;

                    merchantData.APIStoregroupId = Convert.ToInt32(r["store_group_id"]);
                    merchantData.MerchantName = r["store_group_name"].ToString();
                    merchantData.Drivers = Convert.ToInt32(r["drivers"]);
                    merchantData.Products = Convert.ToInt32(r["products"]);
                    merchantData.StoresOnline = Convert.ToInt32(r["storesOnline"]);
                    merchantData.TotalStores = Convert.ToInt32(r["totalStores"]);
                    merchantData.OrderPickers = Convert.ToInt32(r["orderpickers"]);
                    merchantData.OrderPickersOnline = Convert.ToInt32(r["orderpickersOnline"]);
                    merchantData.ProductsWithStockAndPrice = Convert.ToInt32(r["productsWithStockAndPrice"]);
                    merchantData.DeliveryRules = Convert.ToInt32(r["DeliveryRules"]);
                    //merchantData.RestaurantProducts = Convert.ToInt32(r["restaurantProducts"]);
                    merchantData.Orders = Convert.ToInt32(r["num"]);
                    merchantData.OrderValue = Convert.ToDouble(r["total"]);
                    merchantData.CpOrganizationName = r["crpr_orgName"].ToString();
                    if(r["crpr_mode"] != DBNull.Value)
                        merchantData.CpMode = Convert.ToInt32(r["crpr_mode"]);
                    merchantData.RoName = r["roName"].ToString();
                    merchantData.BAName = r["baName"].ToString();
                    merchantData.Areaname = r["areaName"].ToString();
                    merchantData.AreaLocation = r["areaLocation"].ToString();
                    merchantData.IsFeatured = Convert.ToBoolean(r["isFeatured"]);
                    merchantData.BranchNames= r["brNames"].ToString();
                    merchantData.BranchAreaName = r["br_areaNames"].ToString();
                }
            }

            foreach (var merchantData in merchantDataList)
                SetPendingActivity(merchantData);

            return merchantDataList;
        }


    }
}
