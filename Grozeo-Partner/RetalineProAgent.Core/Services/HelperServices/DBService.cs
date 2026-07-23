using LazyCache;
using Microsoft.Extensions.Configuration;
using ODOCart.Core.ViewModel.Tenant;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Data;
using System.Data.SqlClient;
using System.Linq;
using System.Text;
using System.Threading;
using System.Threading.Tasks;

namespace ODOCart.Core.Services.HelperServices
{
    public class DBService: IDBService
    {
        private readonly IConfiguration _configuration;
        public DBService(IConfiguration configuration)
        {
            _configuration = configuration;
        }

        /// <summary>
        /// GetAllTenants from DB
        /// </summary>
        /// <returns>All active Tenants list</returns>
        public async Task<Collection<AppTenant>> GetAllTenants()
        {
            Collection<AppTenant> appTenants = new Collection<AppTenant>();
            //IAppCache cache = new CachingService();

            appTenants = await Utilities.Common.Cache.GetOrAddAsync(Utilities.Common.TenantsCacheKey, cacheEntry =>
            {
                //cacheEntry.SlidingExpiration = TimeSpan.FromSeconds(60*30);
                return GetAllTenantsFromDB();

            });

            return appTenants;
        }

        private async Task<Collection<AppTenant>> GetAllTenantsFromDB()
        {
            Collection<AppTenant> tenants = new Collection<AppTenant>();
            try
            {
                using (var connection = new SqlConnection(_configuration["ConnectionStrings:DefaultConnection"]))
                {
                    await connection.OpenAsync();
                    string strSql = "SELECT a.*, Stuff((SELECT ',' + (t.HostAddress) FROM Host t WHERE a.[Status] =1 and a.Id LIKE t.TenantId FOR Xml Path('')), 1, 1, '') as hosts FROM AppTenant a";
                    using (var command = new SqlCommand(strSql, connection)) // "SELECT * FROM dbo.AppTenant Where [Status] = 1"
                    {
                        var reader = await command.ExecuteReaderAsync();
                        //var lst = reader.AutoMap<ViewModel.Tenant.AppTenant>().ToList();
                        //if (reader.Read())
                        //{
                        //    result = reader[0].ToString();
                        //}
                        while (reader.Read())
                        {
                            try
                            {
                                AppTenant tenant = new AppTenant();
                                tenant.Id = int.Parse(reader["Id"].ToString());
                                tenant.Name = reader["Name"].ToString();
                                tenant.Theme = reader["Theme"].ToString();
                                tenant.APIUrl = reader["APIUrl"].ToString();

                                tenant.StoreId = reader["StoreId"].ToString();

                                string srHostnames = reader["hosts"].ToString();
                                if (!String.IsNullOrEmpty(srHostnames))
                                {
                                    tenant.Hostnames = srHostnames.Split(',').Select(h => h.Trim()).ToArray();
                                }
                                tenant.CanCheckout = (bool)reader["CanCheckout"];
                                tenant.OnlinePaymentEnabled = (bool)reader["OnlinePaymentEnabled"];

                                tenant.Status = bool.Parse(reader["Status"].ToString());
                                tenant.ShowPWA = bool.Parse(reader["ShowPWA"].ToString());
                                tenant.LogoImage = reader["LogoImage"].ToString();
                                tenant.CustomColor = reader["CustomColor"].ToString();
                                tenant.LogoSmall = reader["LogoSmall"].ToString();
                                tenant.FavIcoImage = reader["FavIcoImage"].ToString();

                                tenants.Add(tenant);
                            }
                            catch(Exception ex)
                            {
                                
                            }
                        }

                        if (!reader.IsClosed) await reader.CloseAsync();
                        if (connection.State != ConnectionState.Closed) await connection.CloseAsync();

                        if(!tenants.Any(t => t.Name == "ODO Cart"))
                        {
                            var objc = _configuration.GetValue<MultitenancyOptions>("Multitenancy");
                            if (objc != null && objc.Tenants != null && objc.Tenants.Any(t=> t.Name == "ODO Cart"))
                            {
                                tenants.Add(objc.Tenants.Where(t => t.Name == "ODO Cart").FirstOrDefault());
                            }
                        }
                        

                        return tenants;//FormatJsonData(result);
                    }
                }
            }
            catch (Exception ex)
            {
                return null;
            }

        }
        //private string FormatJsonData(string json)
        //{
        //    dynamic data = Newtonsoft.Json.JsonConvert.DeserializeObject(json);
        //    return Newtonsoft.Json.JsonConvert.SerializeObject(data, Newtonsoft.Json.Formatting.Indented);
        //}

        //public async void BulkInsert(DataTable dt)
        //{
           
        //    try
        //    {
        //        using (var connection = new SqlConnection(_configuration["ConnectionStrings:DefaultConnection"]))
        //        {
        //            await connection.OpenAsync();
        //            string strSql = "SELECT a.*, Stuff((SELECT ',' + (t.HostAddress) FROM Host t WHERE a.[Status] =1 and a.Id LIKE t.TenantId FOR Xml Path('')), 1, 1, '') as hosts FROM AppTenant a";
        //            using (var command = new SqlCommand(strSql, connection)) // "SELECT * FROM dbo.AppTenant Where [Status] = 1"
        //            {
        //                var reader = await command.ExecuteReaderAsync();
        //                //var lst = reader.AutoMap<ViewModel.Tenant.AppTenant>().ToList();
        //                //if (reader.Read())
        //                //{
        //                //    result = reader[0].ToString();
        //                //}
        //                while (reader.Read())
        //                {
        //                    try
        //                    {
        //                        AppTenant tenant = new AppTenant();
        //                        tenant.Id = int.Parse(reader["Id"].ToString());
        //                        tenant.Name = reader["Name"].ToString();
        //                        tenant.Theme = reader["Theme"].ToString();
        //                        tenant.APIUrl = reader["APIUrl"].ToString();

        //                        tenant.StoreId = reader["StoreId"].ToString();

        //                        string srHostnames = reader["hosts"].ToString();
        //                        if (!String.IsNullOrEmpty(srHostnames))
        //                        {
        //                            tenant.Hostnames = srHostnames.Split(',').Select(h => h.Trim()).ToArray();
        //                        }
        //                        tenant.CanCheckout = (bool)reader["CanCheckout"];
        //                        tenant.OnlinePaymentEnabled = (bool)reader["OnlinePaymentEnabled"];

        //                        tenant.Status = bool.Parse(reader["Status"].ToString());
        //                        tenant.ShowPWA = bool.Parse(reader["ShowPWA"].ToString());
        //                        tenant.LogoImage = reader["LogoImage"].ToString();
        //                        tenant.CustomColor = reader["CustomColor"].ToString();
        //                        tenant.LogoSmall = reader["LogoSmall"].ToString();
        //                        tenant.FavIcoImage = reader["FavIcoImage"].ToString();

        //                        tenants.Add(tenant);
        //                    }
        //                    catch (Exception ex)
        //                    {

        //                    }
        //                }

        //                if (!reader.IsClosed) await reader.CloseAsync();
        //                if (connection.State != ConnectionState.Closed) await connection.CloseAsync();

        //                if (!tenants.Any(t => t.Name == "ODO Cart"))
        //                {
        //                    var objc = _configuration.GetValue<MultitenancyOptions>("Multitenancy");
        //                    if (objc != null && objc.Tenants != null && objc.Tenants.Any(t => t.Name == "ODO Cart"))
        //                    {
        //                        tenants.Add(objc.Tenants.Where(t => t.Name == "ODO Cart").FirstOrDefault());
        //                    }
        //                }


        //                return tenants;//FormatJsonData(result);
        //            }
        //        }
        //    }
        //    catch (Exception ex)
        //    {
        //        return null;
        //    }


        //}

    }
}
